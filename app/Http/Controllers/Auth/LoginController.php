<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLog;
use App\Models\Concept;
use App\Providers\RouteServiceProvider;
use App\Services\CmpApi;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Subscription;
use View;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::USER;

    protected $currentDomain;
    protected $concept;
    protected $cmp;
    protected $template;
    protected $locale;
    protected $api_url;

    public function __construct()
    {
        $this->currentDomain = parse_url(url()->current(), PHP_URL_HOST);
        $this->concept = Concept::where('domain_name', $this->currentDomain)->first();

        if (!$this->concept) {
            $this->concept = Concept::where('template_name', config('app.default_concept'))->first();
        }

        $this->template = $this->concept->template;
        $this->locale = app()->getLocale();
        $this->cmp = new CmpApi($this->concept);
        $this->api_url = $this->concept->crm->api_url;

        $this->middleware('guest')->except('logout');
        View::share('concept', $this->concept);
    }

    public function showLoginForm()
    {
        $themeDir = 'templates.' . $this->template;
        return view($themeDir . '.auth.login', compact('themeDir'));
    }

    public function login(Request $request)
    {
        $username = $request->email;
        $password = $request->password;
        $remember = $request->has('remember');

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // First Attempt: Local Auth
        if (Auth::attempt(['email' => $username, 'password' => $password, 'domain_name' => $this->currentDomain], $remember)) {
            return $this->handleSubscriptionCheck(Auth::user());
        }

        // Second Attempt: Remote Auth
        $authResponse = $this->cmp->authenticateUser($request, $username, $password);
        dd($authResponse); 

        if ($authResponse->status === 'success') {
            $userData = $authResponse->data;
            $this->createOrUpdateWebUser($userData, $username, $password);

            if (Auth::attempt(['email' => $username, 'password' => $password, 'domain_name' => $this->currentDomain], $remember)) {
                return redirect()->route('user.dashboard.index');
            }
        }

        return redirect()->back()->withErrors(['error' => 'Email address or password is incorrect.']);
    }

    protected function handleSubscriptionCheck($user)
    {
        $customerId = $user->customer_id;
        $subData = $this->cmp->getSubscriptionDetailByCustomerId($customerId);
        // dd($subData);

        if ($subData->status !== 'success') {
            $name = $user->name;
            $email = $user->email;
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'error' => "Dear {$name}, Your subscription associated with the email address {$email} has ended."
            ]);
        }

        return redirect()->route('user.dashboard.index');
    }

   protected function createOrUpdateWebUser($data, $username, $password)
{
    $existingUser = User::where('customer_id', $data->customer_id)
        ->where('domain_name', $this->currentDomain)
        ->first();

    $userInfo = $this->cmp->getCustomer($data->customer_id)->data;
    $subData = $this->cmp->getSubscriptionDetailByCustomerId($data->customer_id)->data[0] ?? null;
    // dd($subData);
    $encryptedPassword = Hash::make($password);
    $fullName = "{$userInfo->first_name} {$userInfo->last_name}";
    $usernameSlug = Str::slug($fullName);


    if (!$existingUser) {
        $user = User::create([
            'concept_id'       => $this->concept->id,
            'email'            => $username,
            'name'             => $fullName, 
            'level'             => 'SUBSCRIBER', 
            'firstname'        => $userInfo->first_name,
            'lastname'         => $userInfo->last_name,
            'username'         => $usernameSlug,
            'plain_password'   => $password,
            'role'             => 'ROLE_SUBSCRIBER',
            'enabled'          => 1,
            'password'         => $encryptedPassword,
            'subscription_id'  => $subData->id ?? null,
            'customer_id'      => $data->customer_id,
            'web_user_id'      => $data->id,
            'domain_name'      => $this->currentDomain,
        ]);

        $user->subscription()->create([
            'plan_id' => $subData->plan_id ?? 3,
            'generated_images' => 0,
            'status' => $subData->status ?? 'active' === 'active' ? 1 : 1,
            'expiry_at' => $subData->billing_period_end_date ,
            'about_to_expire_reminder' => 0,
            'expired_reminder' => 0,
            'is_viewed' => 1,
        ]);

    } else {
        $existingUser->update([
            'name'             => $fullName,
            'username'         => $usernameSlug,
            'username_canonical' => strtolower($usernameSlug),
            'plain_password'   => $password,
            'role'             => 'ROLE_SUBSCRIBER',
            'enabled'          => 1,
            'password'         => $encryptedPassword,
            'subscription_id'  => $subData->id,
            'customer_id'      => $data->customer_id,
            'domain_name'      => $this->currentDomain,
        ]);

        Subscription::updateOrCreate(
            ['user_id' => $existingUser->id],
            [
                'plan_id' => $subData->plan_id ?? 3,
                'generated_images' => 0,
                'status' => $subData->status === 'active' ? 1 : 1,
                'expiry_at' => $subData->ends_at ?? now()->addMonth(),
                'about_to_expire_reminder' => 0,
                'expired_reminder' => 0,
                'is_viewed' => 0,
            ]
        );
    }
}



    public function logout(Request $request)
    {
        $sessionKey = $this->guard()->user()->name;

        $this->guard()->logout();
        $request->session()->forget($sessionKey);

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? response()->json([], 204)
            : redirect()->route('login');
    }
}

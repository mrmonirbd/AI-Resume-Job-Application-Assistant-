<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Concept;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CmpApi;
use App\Services\CmpApiFactory;
use App\Services\ConceptResolver;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, ConceptResolver $conceptResolver, CmpApiFactory $cmpFactory): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $concept = $conceptResolver->forRequest($request);
        $cmp = $cmpFactory->isConfigured($concept) ? $cmpFactory->make($concept) : null;
        $host = strtolower($request->getHost());
        $localUser = User::where('email', $credentials['email'])->first();
        $matchesDomain = $localUser && (blank($localUser->domain_name) || strtolower($localUser->domain_name) === $host);

        if ($matchesDomain && Auth::attempt($credentials, $request->boolean('remember'))) {
            if (filled($localUser->crm_customer_id)) {
                $this->handleSubscriptionCheck($request, $localUser, $concept, $cmp);
            }

            return $this->redirectForAuthenticatedUser($request);
        }

        if ($cmp === null) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        try {
            $remoteAuthentication = $cmp->authenticateUser($request, $credentials['email'], $credentials['password']);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'email' => __('We could not reach the account service. Please try again.'),
            ]);
        }

        if (! $this->isSuccessful($remoteAuthentication)) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $remoteAccount = $this->responseData($remoteAuthentication);
        $customerId = data_get($remoteAccount, 'customer_id');

        if (! filled($customerId)) {
            throw ValidationException::withMessages([
                'email' => __('The account service did not return customer data.'),
            ]);
        }

        try {
            $customerResponse = $cmp->getCustomer((string) $customerId);
            $subscriptionResponse = $cmp->getSubscriptionDetailByCustomerId((string) $customerId);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'email' => __('We could not load the subscription for this account. Please try again.'),
            ]);
        }

        if (! $this->isSuccessful($customerResponse) || ! $this->isSuccessful($subscriptionResponse)) {
            throw ValidationException::withMessages([
                'email' => __('This account does not have an active subscription.'),
            ]);
        }

        $customer = $this->responseData($customerResponse);
        $subscription = $this->firstSubscription($this->responseData($subscriptionResponse));

        if (! is_array($customer) || ! is_array($subscription) || ! is_array($remoteAccount)) {
            throw ValidationException::withMessages([
                'email' => __('The account service returned incomplete account data.'),
            ]);
        }

        $user = DB::transaction(function () use ($concept, $customer, $subscription, $remoteAccount, $credentials, $request): User {
            return $this->syncRemoteUser(
                $concept,
                $customer,
                $subscription,
                is_array($remoteAccount) ? $remoteAccount : [],
                $credentials['email'],
                $credentials['password'],
                $request->getHost(),
            );
        });

        Auth::login($user, $request->boolean('remember'));

        return $this->redirectForAuthenticatedUser($request);
    }

    private function handleSubscriptionCheck(Request $request, User $user, ?Concept $concept, ?CmpApi $cmp): void
    {
        $message = 'We could not verify your subscription. Please try again.';
        $hasActiveSubscription = false;

        if ($cmp !== null && (! $user->concept_id || $user->concept_id === $concept->id)) {
            try {
                $response = $cmp->getSubscriptionDetailByCustomerId($user->crm_customer_id);
                $subscription = $this->firstSubscription($this->responseData($response));
                $hasActiveSubscription = $this->isSuccessful($response) && $subscription !== null;
                $message = 'This account does not have an active subscription.';
            } catch (ConnectionException) {
                $hasActiveSubscription = false;
            }
        }

        if (! $hasActiveSubscription) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages(['email' => __($message)]);
        }
    }

    private function isSuccessful(mixed $response): bool
    {
        return is_object($response) && in_array(data_get($response, 'status'), ['success', 'active'], true);
    }

    private function responseData(mixed $response): mixed
    {
        if (! is_object($response)) {
            return null;
        }

        return json_decode(json_encode(data_get($response, 'data'), JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    private function redirectForAuthenticatedUser(Request $request): RedirectResponse
    {
        $request->session()->regenerate();

        if (! $request->user()->hasVerifiedEmail()) {
            return to_route('verification.notice');
        }

        if (! $request->user()->onboarding_completed_at) {
            return to_route('onboarding.show');
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>  $subscription
     * @param  array<string, mixed>  $remoteAccount
     */
    private function syncRemoteUser(Concept $concept, array $customer, array $subscription, array $remoteAccount, string $email, string $password, string $host): User
    {
        $firstName = trim((string) data_get($customer, 'first_name'));
        $lastName = trim((string) data_get($customer, 'last_name'));
        $name = trim("{$firstName} {$lastName}") ?: $email;
        $user = User::firstOrNew(['email' => $email]);

        if ($user->exists && ((filled($user->domain_name) && strtolower($user->domain_name) !== strtolower($host))
            || (filled($user->concept_id) && $user->concept_id !== $concept->id))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $user->fill([
            'name' => $name,
            'password' => Hash::make($password),
            'concept_id' => $concept->id,
            'crm_customer_id' => (string) data_get($customer, 'id', data_get($remoteAccount, 'customer_id')),
            'crm_subscription_id' => (string) data_get($subscription, 'id'),
            'crm_web_user_id' => (string) data_get($remoteAccount, 'id'),
            'domain_name' => strtolower($host),
            'crm_profile' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => (string) data_get($customer, 'phone'),
                'address' => (string) data_get($customer, 'address', data_get($customer, 'address_1')),
                'city' => (string) data_get($customer, 'city'),
                'postcode' => (string) data_get($customer, 'postcode', data_get($customer, 'zip', data_get($customer, 'postal_code'))),
            ],
            'onboarding_completed_at' => $user->onboarding_completed_at ?? now(),
        ]);
        $user->email_verified_at = now();
        $user->save();

        $localSubscription = $user->subscriptions()->latest()->first() ?? new Subscription(['user_id' => $user->id]);
        $localSubscription->fill([
            'status' => 'active',
            'renews_at' => $this->dateOrNull(data_get($subscription, 'billing_period_end_date')),
            'ends_at' => null,
            'metadata' => [
                'remote_id' => (string) data_get($subscription, 'id'),
                'remote_plan_id' => data_get($subscription, 'plan_id'),
                'remote_status' => data_get($subscription, 'status', 'active'),
            ],
        ]);
        $localSubscription->save();

        return $user;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function firstSubscription(mixed $subscriptions): ?array
    {
        if (! is_array($subscriptions)) {
            return null;
        }

        $subscription = array_is_list($subscriptions) ? ($subscriptions[0] ?? null) : $subscriptions;

        if (! is_array($subscription) || ! filled($subscription['id'] ?? null)) {
            return null;
        }

        if (isset($subscription['status']) && ! in_array($subscription['status'], ['active', 'trialing'], true)) {
            return null;
        }

        return $subscription;
    }

    private function dateOrNull(mixed $date): ?Carbon
    {
        return filled($date) ? Carbon::parse((string) $date) : null;
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }
}

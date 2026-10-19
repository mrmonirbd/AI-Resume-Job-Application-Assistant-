<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Concept;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ConceptResolver;
use App\Services\Crm\CrmApiClient;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, ConceptResolver $conceptResolver, CrmApiClient $crm): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            return $this->redirectForAuthenticatedUser($request);
        }

        $concept = $conceptResolver->forRequest($request);

        if (! $crm->isConfigured($concept)) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        try {
            $remoteAuthentication = $crm->authenticate($concept, $credentials['email'], $credentials['password']);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'email' => __('We could not reach the account service. Please try again.'),
            ]);
        }

        if (! $crm->isSuccessful($remoteAuthentication)) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        $remoteAccount = $crm->data($remoteAuthentication);
        $customerId = data_get($remoteAccount, 'customer_id');

        if (! filled($customerId)) {
            throw ValidationException::withMessages([
                'email' => __('The account service did not return customer data.'),
            ]);
        }

        try {
            $customerResponse = $crm->customer($concept, (string) $customerId);
            $subscriptionResponse = $crm->activeSubscription($concept, (string) $customerId);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'email' => __('We could not load the subscription for this account. Please try again.'),
            ]);
        }

        if (! $crm->isSuccessful($customerResponse) || ! $crm->isSuccessful($subscriptionResponse)) {
            throw ValidationException::withMessages([
                'email' => __('This account does not have an active subscription.'),
            ]);
        }

        $customer = $crm->data($customerResponse);
        $subscription = $this->firstSubscription($crm->data($subscriptionResponse));

        if (! is_array($customer) || ! is_array($subscription)) {
            throw ValidationException::withMessages([
                'email' => __('The account service returned incomplete account data.'),
            ]);
        }

        $user = $this->syncRemoteUser(
            $concept,
            $customer,
            $subscription,
            is_array($remoteAccount) ? $remoteAccount : [],
            $credentials['email'],
            $credentials['password'],
            $request->getHost(),
        );

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectForAuthenticatedUser($request);
    }

    private function redirectForAuthenticatedUser(Request $request): RedirectResponse
    {
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

        return is_array($subscription) ? $subscription : null;
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

        return to_route('landing');
    }
}

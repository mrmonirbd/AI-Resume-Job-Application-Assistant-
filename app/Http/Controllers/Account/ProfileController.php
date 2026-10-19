<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ConceptResolver;
use App\Services\Crm\CrmApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request, ConceptResolver $conceptResolver, CrmApiClient $crm): View
    {
        $user = $request->user();
        $concept = $user->concept ?? $conceptResolver->forRequest($request);
        $crmCustomer = null;

        if ($crm->isConfigured($concept) && filled($user->crm_customer_id)) {
            try {
                $response = $crm->customer($concept, $user->crm_customer_id);
                $crmCustomer = $crm->isSuccessful($response) ? $crm->data($response) : null;
            } catch (ConnectionException) {
                $crmCustomer = null;
            }
        }

        $profile = $this->profileData($user->crm_profile, $crmCustomer);

        return view('account.profile', compact('user', 'concept', 'crmCustomer', 'profile'));
    }

    public function update(ProfileUpdateRequest $request, ConceptResolver $conceptResolver, CrmApiClient $crm): RedirectResponse
    {
        $user = $request->user();
        $concept = $user->concept ?? $conceptResolver->forRequest($request);
        $validated = $request->validated();

        if ($crm->isConfigured($concept) && filled($user->crm_customer_id)) {
            try {
                $response = $crm->updateCustomer($concept, $user->crm_customer_id, [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'city' => $validated['city'],
                    'postcode' => $validated['postcode'],
                    'current_email' => $user->email,
                    'customer_id' => $user->crm_customer_id,
                    'subscription_id' => $user->crm_subscription_id,
                ]);
            } catch (ConnectionException) {
                return back()->withErrors(['profile' => 'We could not update your CRM account. Please try again.']);
            }

            if (! $crm->isSuccessful($response)) {
                return back()->withErrors(['profile' => 'The CRM could not update your profile. Please try again.']);
            }
        }

        $user->fill(Arr::only($validated, ['professional_title', 'location', 'career_goal']));
        $user->name = trim($validated['first_name'].' '.$validated['last_name']);
        $user->crm_profile = array_merge($user->crm_profile ?? [], Arr::only($validated, [
            'first_name', 'last_name', 'phone', 'address', 'city', 'postcode',
        ]));

        $user->save();

        return back()->with('status', 'Profile updated.');
    }

    public function security(Request $request): View
    {
        return view('account.security', ['user' => $request->user()]);
    }

    public function password(Request $request, ConceptResolver $conceptResolver, CrmApiClient $crm): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        $concept = $user->concept ?? $conceptResolver->forRequest($request);

        if ($crm->isConfigured($concept) && filled($user->crm_web_user_id)) {
            try {
                $response = $crm->updatePassword($concept, $user->crm_web_user_id, $validated['password']);
            } catch (ConnectionException) {
                return back()->withErrors(['password' => 'We could not update your CRM password. Please try again.']);
            }

            if (! $crm->isSuccessful($response)) {
                return back()->withErrors(['password' => 'The CRM could not update your password. Please try again.']);
            }
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return back()->with('status', 'Password changed.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('landing');
    }

    /**
     * @param  array<string, mixed>|null  $storedProfile
     * @return array<string, string>
     */
    private function profileData(?array $storedProfile, mixed $crmCustomer): array
    {
        $profile = $storedProfile ?? [];

        if (! is_array($crmCustomer)) {
            return $profile;
        }

        foreach ([
            'first_name' => ['first_name'],
            'last_name' => ['last_name'],
            'phone' => ['phone'],
            'address' => ['address', 'address_1'],
            'city' => ['city'],
            'postcode' => ['postcode', 'zip', 'postal_code'],
        ] as $field => $keys) {
            foreach ($keys as $key) {
                $value = data_get($crmCustomer, $key);

                if (filled($value)) {
                    $profile[$field] = (string) $value;
                    break;
                }
            }
        }

        return $profile;
    }
}

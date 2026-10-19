<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\ConceptResolver;
use App\Services\Crm\CrmApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function show(Request $request, ConceptResolver $conceptResolver, CrmApiClient $crm): View
    {
        $user = $request->user();
        $concept = $user->concept ?? $conceptResolver->forRequest($request);
        $subscription = $user->subscriptions()->with('plan')->latest()->first();
        $remoteSubscription = null;
        $crmUnavailable = false;

        if ($crm->isConfigured($concept) && filled($user->crm_customer_id)) {
            try {
                $subscriptionResponse = filled($user->crm_subscription_id)
                    ? $crm->subscription($concept, $user->crm_subscription_id)
                    : $crm->activeSubscription($concept, $user->crm_customer_id);
                $remoteSubscription = $crm->isSuccessful($subscriptionResponse) ? $this->firstItem($crm->data($subscriptionResponse)) : null;
            } catch (ConnectionException) {
                $crmUnavailable = true;
            }
        }

        return view('account.subscription', compact('user', 'concept', 'subscription', 'remoteSubscription', 'crmUnavailable'));
    }

    public function paymentHistory(Request $request, ConceptResolver $conceptResolver, CrmApiClient $crm): View
    {
        $user = $request->user();
        $concept = $user->concept ?? $conceptResolver->forRequest($request);
        $invoices = [];
        $crmUnavailable = false;

        if ($crm->isConfigured($concept) && filled($user->crm_customer_id)) {
            try {
                $response = $crm->invoices($concept, $user->crm_customer_id);
                $invoices = $crm->isSuccessful($response) && is_array($crm->data($response)) ? $crm->data($response) : [];
            } catch (ConnectionException) {
                $crmUnavailable = true;
            }
        }

        return view('account.payment-history', compact('user', 'concept', 'invoices', 'crmUnavailable'));
    }

    public function cancel(Request $request, ConceptResolver $conceptResolver, CrmApiClient $crm): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:cancel'],
        ]);

        $user = $request->user();
        $concept = $user->concept ?? $conceptResolver->forRequest($request);

        if (! $crm->isConfigured($concept) || ! filled($user->crm_subscription_id)) {
            return back()->withErrors(['subscription' => 'No CRM subscription is linked to this account.']);
        }

        try {
            $response = $crm->cancelSubscription($concept, $user->crm_subscription_id, $request);
        } catch (ConnectionException) {
            return back()->withErrors(['subscription' => 'We could not reach the CRM. Please try again.']);
        }

        if (! $crm->isSuccessful($response)) {
            return back()->withErrors(['subscription' => 'The CRM could not cancel this subscription. Please contact support.']);
        }

        $user->subscriptions()->latest()->first()?->update([
            'status' => 'cancelled',
            'ends_at' => now(),
        ]);

        return back()->with('status', 'Your subscription has been cancelled.');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function firstItem(mixed $data): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        $item = array_is_list($data) ? ($data[0] ?? null) : $data;

        return is_array($item) ? $item : null;
    }
}

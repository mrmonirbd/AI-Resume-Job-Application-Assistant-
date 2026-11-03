<?php

namespace App\Services;

use App\Models\Concept;
use App\Models\User;
use App\Services\Crm\CrmApiClient;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use stdClass;

class LegacyCmpApi extends CmpApi
{
    private array $customerCache = [];

    private array $subscriptionCache = [];

    public function __construct(private readonly Concept $legacyConcept, private readonly CrmApiClient $transport)
    {
        parent::__construct($legacyConcept);
    }

    public function authenticateUser(mixed $request, mixed $username, mixed $password): stdClass
    {
        if (! $request instanceof Request || ! $this->transport->isConfigured($this->legacyConcept)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        $response = $this->successfulResponse($this->transport->authenticate($this->legacyConcept, $username, $password));
        if (! is_object($response->data ?? null) || ! filled($response->data->customer_id ?? null)) {
            throw ValidationException::withMessages(['email' => 'The account service did not return customer data.']);
        }

        $existingUser = User::where('email', $username)->first();
        if ($existingUser) {
            $this->getCustomer($response->data->customer_id);
            $this->getSubscriptionDetailByCustomerId($response->data->customer_id);
            $existingUser->customer_id = $response->data->customer_id;
            $existingUser->domain_name = strtolower($request->getHost());
            $existingUser->save();
        }

        return $response;
    }

    public function getCustomer(mixed $customerId): stdClass
    {
        $key = (string) $customerId;
        if (isset($this->customerCache[$key])) {
            return $this->customerCache[$key];
        }

        $response = $this->successfulResponse($this->transport->customer($this->legacyConcept, $key));
        if (! is_object($response->data ?? null)) {
            throw ValidationException::withMessages(['email' => 'The account service returned incomplete customer data.']);
        }
        $response->data->first_name ??= '';
        $response->data->last_name ??= '';
        request()->attributes->set('legacy_cmp_customer', (array) $response->data);

        return $this->customerCache[$key] = $response;
    }

    public function getSubscriptionDetailByCustomerId(mixed $customerId): stdClass
    {
        if (blank($customerId) && Auth::check() && blank(Auth::user()->crm_customer_id)) {
            return (object) ['status' => 'success', 'data' => []];
        }

        $key = (string) $customerId;
        if (isset($this->subscriptionCache[$key])) {
            return $this->subscriptionCache[$key];
        }

        $response = $this->successfulResponse($this->transport->activeSubscription($this->legacyConcept, $key));
        $subscriptions = is_array($response->data ?? null) ? $response->data : [$response->data ?? null];
        $subscription = collect($subscriptions)->first(fn (mixed $item): bool => is_object($item)
            && filled($item->id ?? null)
            && in_array($item->status ?? 'active', ['active', 'trialing'], true));
        if (! $subscription) {
            throw ValidationException::withMessages(['email' => 'This account does not have an active subscription.']);
        }
        $subscription->billing_period_end_date ??= null;
        $subscription->ends_at ??= $subscription->billing_period_end_date;
        $response->data = [$subscription];
        request()->attributes->set('legacy_cmp_subscription', (array) $subscription);

        return $this->subscriptionCache[$key] = $response;
    }

    private function successfulResponse(Response $response): stdClass
    {
        $payload = $response->object();
        if (! $response->successful() || ! $payload instanceof stdClass || ! in_array($payload->status ?? null, ['success', 'active'], true)) {
            throw ValidationException::withMessages(['email' => 'The account service could not verify your account or subscription.']);
        }

        return $payload;
    }
}

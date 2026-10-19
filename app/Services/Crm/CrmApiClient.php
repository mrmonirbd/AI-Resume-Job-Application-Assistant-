<?php

namespace App\Services\Crm;

use App\Models\Concept;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CrmApiClient
{
    public function isConfigured(?Concept $concept): bool
    {
        return $concept?->is_enabled === true
            && filled($concept->crm_api_url)
            && filled($concept->crm_api_key);
    }

    public function authenticate(Concept $concept, string $email, string $password): Response
    {
        return $this->request($concept)->post('api/s/v3/users/authenticates.json', [
            'username' => $email,
            'password' => $password,
            '_locale' => app()->getLocale(),
        ]);
    }

    public function customer(Concept $concept, string $customerId): Response
    {
        return $this->request($concept)->get("api/s/v3/customers/{$customerId}.json");
    }

    public function activeSubscription(Concept $concept, string $customerId): Response
    {
        return $this->request($concept)->get("api/s/v3/customers/{$customerId}/subscriptions/active.json");
    }

    public function subscription(Concept $concept, string $subscriptionId): Response
    {
        return $this->request($concept)->get("api/s/v3/subscriptions/{$subscriptionId}.json");
    }

    public function invoices(Concept $concept, string $customerId): Response
    {
        return $this->request($concept)->get("api/s/v3/customers/{$customerId}/invoices.json");
    }

    public function updateCustomer(Concept $concept, string $customerId, array $customer): Response
    {
        return $this->request($concept)->patch("api/s/v3/customers/{$customerId}.json", $customer);
    }

    public function updatePassword(Concept $concept, string $webUserId, string $password): Response
    {
        return $this->request($concept)->patch("api/s/v3/users/{$webUserId}.json", [
            'password' => $password,
        ]);
    }

    public function cancelSubscription(Concept $concept, string $subscriptionId, Request $request): Response
    {
        return $this->request($concept)->post("api/s/v3/subscriptions/{$subscriptionId}/cancel.json", [
            'customer_ip' => $request->ip(),
            'customer_user_agent' => $request->userAgent(),
            'customer_accept_language' => $request->header('Accept-Language'),
            '_locale' => app()->getLocale(),
        ]);
    }

    public function isSuccessful(Response $response): bool
    {
        return $response->successful() && in_array(data_get($response->json(), 'status'), ['success', 'active'], true);
    }

    public function data(Response $response): mixed
    {
        return data_get($response->json(), 'data');
    }

    private function request(Concept $concept): PendingRequest
    {
        return Http::baseUrl(rtrim((string) $concept->crm_api_url, '/'))
            ->acceptJson()
            ->asJson()
            ->withHeader('x-api-key', (string) $concept->crm_api_key)
            ->timeout((int) config('services.crm.timeout'))
            ->connectTimeout((int) config('services.crm.timeout'))
            ->retry((int) config('services.crm.retries'), (int) config('services.crm.retry_sleep_ms'));
    }
}

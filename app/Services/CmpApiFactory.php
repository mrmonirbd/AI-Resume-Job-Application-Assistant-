<?php

namespace App\Services;

use App\Models\Concept;
use App\Services\Crm\CrmApiClient;

class CmpApiFactory
{
    public function __construct(private readonly CrmApiClient $client) {}

    public function isConfigured(?Concept $concept): bool
    {
        return $this->client->isConfigured($concept);
    }

    public function make(Concept $concept): CmpApi
    {
        return new LegacyCmpApi($concept, $this->client);
    }
}

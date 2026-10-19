<?php

namespace Database\Seeders;

use App\Models\Concept;
use Illuminate\Database\Seeder;

class ConceptSeeder extends Seeder
{
    public function run(): void
    {
        $domain = config('services.crm.default_domain') ?: parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $apiUrl = config('services.crm.api_url');
        $apiKey = config('services.crm.api_key');

        Concept::updateOrCreate(
            ['domain_name' => strtolower($domain)],
            [
                'name' => config('services.crm.default_name'),
                'crm_api_url' => $apiUrl,
                'crm_api_key' => $apiKey,
                'crm_service_id' => config('services.crm.service_id'),
                'product_id' => config('services.crm.product_id'),
                'company_name' => config('services.crm.default_name'),
                'support_email' => config('services.crm.support_email'),
                'support_phone' => config('services.crm.support_phone'),
                'is_default' => true,
                'is_enabled' => filled($apiUrl) && filled($apiKey),
            ],
        );
    }
}

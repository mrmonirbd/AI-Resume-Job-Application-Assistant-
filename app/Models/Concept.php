<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Concept extends Model
{
    protected $fillable = [
        'name',
        'domain_name',
        'crm_api_url',
        'crm_api_key',
        'crm_service_id',
        'product_id',
        'company_name',
        'support_email',
        'support_phone',
        'template',
        'is_default',
        'is_enabled',
    ];

    protected $hidden = [
        'crm_api_key',
    ];

    protected function casts(): array
    {
        return [
            'crm_api_key' => 'encrypted',
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }
}

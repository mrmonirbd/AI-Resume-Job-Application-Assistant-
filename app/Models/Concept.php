<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Concept extends Model
{
    protected $fillable = [
        'name',
        'crm_id',
        'domain_name',
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
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }

    public function crm(): BelongsTo
    {
        return $this->belongsTo(Crm::class);
    }
}

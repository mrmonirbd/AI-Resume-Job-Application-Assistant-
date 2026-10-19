<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crm extends Model
{
    protected $fillable = [
        'name',
        'api_url',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function concepts(): HasMany
    {
        return $this->hasMany(Concept::class);
    }
}

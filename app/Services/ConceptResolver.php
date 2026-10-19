<?php

namespace App\Services;

use App\Models\Concept;
use Illuminate\Http\Request;

class ConceptResolver
{
    public function forRequest(Request $request): ?Concept
    {
        return $this->forHost($request->getHost());
    }

    public function forHost(?string $host): ?Concept
    {
        $host = strtolower(trim((string) $host));

        return Concept::query()
            ->when($host !== '', fn ($query) => $query->where('domain_name', $host))
            ->first()
            ?? Concept::query()->where('is_default', true)->first();
    }
}

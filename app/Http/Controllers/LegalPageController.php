<?php

namespace App\Http\Controllers;

use App\Services\ConceptResolver;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function show(Request $request, ConceptResolver $conceptResolver, string $page): View
    {
        $pages = [
            'terms' => 'terms',
            'privacy' => 'privacy',
            'subscription-policy' => 'subscription',
            'refund-policy' => 'refund',
        ];

        abort_unless(array_key_exists($page, $pages), 404);

        return view('legal.show', [
            'concept' => $conceptResolver->forRequest($request),
            'page' => $pages[$page],
        ]);
    }
}

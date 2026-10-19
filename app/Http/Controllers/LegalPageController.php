<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Services\ConceptResolver;
use App\Services\Crm\CrmApiClient;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class LegalPageController extends Controller
{
    public function show(
        Request $request,
        ConceptResolver $conceptResolver,
        CrmApiClient $crm,
        string $page,
    ): View {
        $pages = [
            'terms' => [
                'key' => 'terms',
                'title' => 'Terms of service',
                'type' => 'terms_of_service',
            ],
            'privacy' => [
                'key' => 'privacy',
                'title' => 'Privacy policy',
                'type' => 'privacy_policy',
            ],
            'subscription-policy' => [
                'key' => 'subscription',
                'title' => 'Subscription policy',
                'type' => 'subscription_policy',
            ],
            'refund-policy' => [
                'key' => 'refund',
                'title' => 'Refund policy',
                'type' => 'subscription_policy',
            ],
        ];

        abort_unless(array_key_exists($page, $pages), 404);

        $concept = $conceptResolver->forRequest($request);
        $policy = $pages[$page];
        $policy['html'] = $this->renderedPolicy($concept, $policy['type'], $crm);

        return view('legal.show', [
            'concept' => $concept,
            'page' => $policy['key'],
            'policy' => $policy,
        ]);
    }

    private function renderedPolicy(?Concept $concept, string $policyType, CrmApiClient $crm): ?string
    {
        if (! $crm->isConfigured($concept)) {
            return null;
        }

        try {
            $response = $crm->renderedPolicy($concept, $policyType);

            if (! $crm->isSuccessful($response)) {
                return null;
            }

            $data = $crm->data($response);
            $html = is_string($data) ? $data : data_get($data, 'html', data_get($data, 'content'));

            return is_string($html) && filled($html) ? $html : null;
        } catch (Throwable) {
            return null;
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Concept;
use App\Services\ConceptResolver;
use App\Services\Crm\CrmApiClient;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
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

            if (! $response->successful()) {
                return null;
            }

            $data = $crm->data($response);
            $html = is_string($data) ? $data : data_get($data, 'html', data_get($data, 'content'));

            return is_string($html) && filled($html) ? $this->cleanPolicyHtml($html) : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function cleanPolicyHtml(string $html): string
    {
        $document = new DOMDocument;
        $previousErrors = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<div id="crm-policy-content">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $root = $document->getElementById('crm-policy-content');

        if (! $root instanceof DOMElement) {
            return '';
        }

        $xpath = new DOMXPath($document);
        $removableNodes = $xpath->query('.//script | .//style | .//iframe | .//object | .//embed | .//svg | .//math | .//form | .//input | .//button | .//link | .//meta | .//base', $root);

        foreach (iterator_to_array($removableNodes ?: []) as $node) {
            $node->parentNode?->removeChild($node);
        }

        $allowedTags = ['p', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'strong', 'b', 'em', 'i', 'u', 'br', 'hr', 'ul', 'ol', 'li', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td'];

        foreach (iterator_to_array($xpath->query('.//*', $root) ?: []) as $element) {
            if (! $element instanceof DOMElement) {
                continue;
            }

            if (! in_array(strtolower($element->tagName), $allowedTags, true)) {
                $element->parentNode?->removeChild($element);

                continue;
            }

            foreach (iterator_to_array($element->attributes) as $attribute) {
                $name = strtolower($attribute->nodeName);
                $value = preg_replace('/[\x00-\x20\x7f]+/', '', $attribute->nodeValue) ?? '';
                $isSafeLink = strtolower($element->tagName) === 'a'
                    && $name === 'href'
                    && (str_starts_with($value, '#') || preg_match('~^https?://~i', $value) || preg_match('~^(?:mailto|tel):~i', $value));

                if (! $isSafeLink) {
                    $element->removeAttribute($attribute->nodeName);
                }
            }
        }

        return collect(iterator_to_array($root->childNodes))
            ->map(fn (DOMNode $node): string => $document->saveHTML($node))
            ->implode('');
    }
}

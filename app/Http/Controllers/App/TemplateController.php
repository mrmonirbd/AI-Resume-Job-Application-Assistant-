<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ResumeTemplate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __invoke(Request $request): View
    {
        $sortOptions = [
            'default' => 'Default',
            'name' => 'Name A-Z',
            'layout' => 'Layout Type',
            'category' => 'Category',
        ];
        $sort = (string) $request->query('sort', 'default');

        if (! array_key_exists($sort, $sortOptions)) {
            $sort = 'default';
        }

        $templates = ResumeTemplate::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (ResumeTemplate $template): string => sprintf(
                '%04d-%s',
                (int) data_get($template->settings, 'sort', 999),
                $template->name
            ))
            ->values();

        $groupedTemplates = match ($sort) {
            'name' => $templates
                ->sortBy(fn (ResumeTemplate $template): string => $template->name)
                ->values()
                ->groupBy(fn (): string => 'all'),
            'layout' => $templates
                ->sortBy(fn (ResumeTemplate $template): string => $template->layout.'-'.$template->name)
                ->values()
                ->groupBy(fn (): string => 'all'),
            'category' => $templates
                ->groupBy(fn (ResumeTemplate $template): string => $template->category ?: 'Other')
                ->sortKeys(),
            default => $templates->groupBy(fn (): string => 'all'),
        };

        return view('app.templates', [
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'templateCount' => $templates->count(),
            'templates' => $groupedTemplates,
        ]);
    }
}

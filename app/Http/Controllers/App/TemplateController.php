<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ResumeTemplate;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __invoke(): View
    {
        $templates = ResumeTemplate::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (ResumeTemplate $template): int => (int) data_get($template->settings, 'sort', 999))
            ->groupBy('category');

        return view('app.templates', [
            'templates' => $templates,
        ]);
    }
}

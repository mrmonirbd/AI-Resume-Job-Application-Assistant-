<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ResumeTemplate;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __invoke(): View
    {
        return view('app.templates', [
            'templates' => ResumeTemplate::where('is_active', true)->orderBy('category')->get()->groupBy('category'),
        ]);
    }
}

<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\ResumeTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(Request $request): View
    {
        return view('app.onboarding', [
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_method' => ['required', 'in:ai,manual,import'],
            'name' => ['required', 'string', 'max:120'],
            'professional_title' => ['nullable', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:160'],
            'career_goal' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:4000'],
            'experience' => ['nullable', 'string', 'max:6000'],
            'education' => ['nullable', 'string', 'max:4000'],
            'skills' => ['nullable', 'string', 'max:2000'],
            'projects' => ['nullable', 'string', 'max:4000'],
            'certifications' => ['nullable', 'string', 'max:2000'],
            'languages' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $user = $request->user();
            $user->update([
                'name' => $validated['name'],
                'professional_title' => $validated['professional_title'] ?? null,
                'location' => $validated['location'] ?? null,
                'career_goal' => $validated['career_goal'] ?? null,
                'onboarding_completed_at' => now(),
            ]);

            $template = ResumeTemplate::where('slug', 'classic-clarity')->first();
            $resume = $user->masterResume()->firstOrCreate(
                ['is_master' => true],
                [
                    'template_id' => $template?->id,
                    'title' => 'Master Resume',
                    'type' => 'master',
                    'professional_title' => $validated['professional_title'] ?? null,
                    'professional_summary' => $validated['summary'] ?? null,
                    'last_edited_at' => now(),
                ]
            );

            $resume->update([
                'professional_title' => $validated['professional_title'] ?? $resume->professional_title,
                'professional_summary' => $validated['summary'] ?? $resume->professional_summary,
                'last_edited_at' => now(),
            ]);

            collect([
                'personal' => 'Personal Information',
                'summary' => 'Professional Summary',
                'experience' => 'Work Experience',
                'education' => 'Education',
                'skills' => 'Skills',
                'projects' => 'Projects',
                'certifications' => 'Certifications',
                'languages' => 'Languages',
            ])->each(function (string $title, string $type) use ($resume, $validated) {
                $resume->sections()->updateOrCreate(
                    ['type' => $type],
                    [
                        'title' => $title,
                        'content' => $validated[$type] ?? null,
                        'sort_order' => array_search($type, ['personal', 'summary', 'experience', 'education', 'skills', 'projects', 'certifications', 'languages'], true),
                        'is_visible' => true,
                    ]
                );
            });

            collect(explode(',', $validated['skills'] ?? ''))
                ->map(fn ($skill) => trim($skill))
                ->filter()
                ->unique()
                ->values()
                ->each(fn ($skill, $index) => $resume->skills()->firstOrCreate(
                    ['name' => $skill],
                    ['sort_order' => $index, 'category' => 'Core']
                ));
        });

        return to_route('dashboard')->with('status', 'Your master resume workspace is ready.');
    }
}

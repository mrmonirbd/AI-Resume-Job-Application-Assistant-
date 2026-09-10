<?php

namespace App\Http\Controllers\App;

use App\Actions\AI\CheckAtsResume;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AtsCheckerController extends Controller
{
    public function create(Request $request): View
    {
        return view('app.ats.show', [
            'resumes' => $request->user()->resumes()->where('status', 'active')->latest('last_edited_at')->get(),
            'result' => null,
        ]);
    }

    public function store(Request $request, CheckAtsResume $checker): View
    {
        $validated = $request->validate([
            'resume_id' => ['required', 'exists:resumes,id'],
            'job_description' => ['nullable', 'string', 'max:12000'],
        ]);

        $resume = $request->user()->resumes()->whereKey($validated['resume_id'])->firstOrFail();

        return view('app.ats.show', [
            'resumes' => $request->user()->resumes()->where('status', 'active')->latest('last_edited_at')->get(),
            'selectedResume' => $resume,
            'result' => $checker->handle($resume, $validated['job_description'] ?? null),
        ]);
    }
}

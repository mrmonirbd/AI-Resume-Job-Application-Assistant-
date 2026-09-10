<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = $request->user()->applications()
            ->with(['job.analysis', 'job.matches'])
            ->latest()
            ->get()
            ->groupBy('status');

        return view('app.applications.index', [
            'applicationsByStage' => $applications,
            'stages' => JobApplication::STAGES,
        ]);
    }

    public function stage(Request $request, JobApplication $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', JobApplication::STAGES)],
        ]);

        $timeline = $application->activity_timeline ?? [];
        $timeline[] = ['label' => 'Moved to '.str($validated['status'])->headline(), 'at' => now()->toDateTimeString()];

        $application->update([
            'status' => $validated['status'],
            'applied_at' => $validated['status'] === 'applied' && ! $application->applied_at ? now() : $application->applied_at,
            'activity_timeline' => $timeline,
        ]);
        $application->job()->update(['stage' => $validated['status'], 'status' => $validated['status']]);

        return back()->with('status', 'Application moved.');
    }
}

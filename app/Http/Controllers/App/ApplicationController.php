<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $applications = $request->user()->applications()
            ->has('job')
            ->with(['job.analysis', 'job.matches'])
            ->latest()
            ->get();

        return view('app.applications.index', [
            'applications' => $applications->map(fn (JobApplication $application): array => [
                'id' => $application->id,
                'status' => $application->status,
                'company' => $application->job->company,
                'title' => $application->job->title,
                'location' => $application->job->location ?: 'Location open',
                'score' => $application->job->matches->max('overall_score'),
                'url' => route('jobs.show', $application->job),
                'moveUrl' => route('applications.stage', $application),
            ])->values(),
            'stages' => JobApplication::STAGES,
        ]);
    }

    public function stage(Request $request, JobApplication $application): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', JobApplication::STAGES)],
        ]);

        DB::transaction(function () use ($application, $validated): void {
            $timeline = $application->activity_timeline ?? [];
            if ($application->status !== $validated['status']) {
                $timeline[] = ['label' => 'Moved to '.str($validated['status'])->headline(), 'at' => now()->toDateTimeString()];
            }

            $application->update([
                'status' => $validated['status'],
                'applied_at' => $validated['status'] === 'applied' && ! $application->applied_at ? now() : $application->applied_at,
                'activity_timeline' => $timeline,
            ]);
            $application->job()->update(['stage' => $validated['status'], 'status' => $validated['status']]);
        });

        if ($request->expectsJson()) {
            return response()->json(['id' => $application->id, 'status' => $application->status]);
        }

        return back()->with('status', 'Application moved.');
    }
}

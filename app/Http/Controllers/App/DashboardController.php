<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\ResumeJobMatch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $applicationQuery = $user->applications()->has('job');
        $applications = (clone $applicationQuery)
            ->with('job')
            ->latest()
            ->limit(6)
            ->get();
        $resumes = $user->resumes()->with('template')->latest('last_edited_at')->limit(5)->get();

        return view('app.dashboard', [
            'masterResume' => $user->masterResume()->first(),
            'resumes' => $resumes,
            'applications' => $applications,
            'stats' => [
                'active_applications' => (clone $applicationQuery)->whereNotIn('status', ['rejected', 'archived'])->count(),
                'interviews' => (clone $applicationQuery)->where('status', 'interview')->count(),
                'resumes_created' => $user->resumes()->count(),
                'average_match_score' => (int) round(ResumeJobMatch::whereHas('job', fn ($query) => $query->where('user_id', $user->id))->avg('overall_score') ?: 0),
                'ai_requests' => AiGeneration::where('user_id', $user->id)->count(),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $applications = $user->applications()->with(['job', 'job.matches'])->latest()->get();
        $resumes = $user->resumes()->latest('last_edited_at')->take(5)->get();

        return view('app.dashboard', [
            'masterResume' => $user->masterResume()->first(),
            'resumes' => $resumes,
            'applications' => $applications->take(6),
            'stats' => [
                'active_applications' => $applications->whereNotIn('status', ['rejected', 'archived'])->count(),
                'interviews' => $applications->where('status', 'interview')->count(),
                'resumes_created' => $user->resumes()->count(),
                'average_match_score' => (int) round($user->jobs()->with('matches')->get()->flatMap->matches->avg('overall_score') ?: 0),
                'ai_requests' => AiGeneration::where('user_id', $user->id)->count(),
            ],
        ]);
    }
}

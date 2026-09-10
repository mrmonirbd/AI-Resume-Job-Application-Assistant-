<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGeneration;
use App\Models\JobApplication;
use App\Models\Plan;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::whereNotNull('email_verified_at')->count(),
                'resumes_created' => Resume::count(),
                'ai_requests' => AiGeneration::count(),
                'applications_created' => JobApplication::count(),
                'estimated_ai_cost' => AiGeneration::sum('cost_estimate'),
                'templates' => ResumeTemplate::count(),
                'plans' => Plan::count(),
            ],
            'recentGenerations' => AiGeneration::latest()->take(8)->get(),
        ]);
    }
}

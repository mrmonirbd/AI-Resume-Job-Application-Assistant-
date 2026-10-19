<?php

namespace Tests\Feature;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_application_tracker_ignore_applications_with_deleted_jobs(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        $job = JobPosting::create([
            'user_id' => $user->id,
            'company' => 'Acme Digital',
            'title' => 'Backend Developer',
            'description' => 'Build and maintain Laravel applications.',
        ]);
        JobApplication::create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'status' => 'applied',
        ]);

        $job->delete();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Backend Developer');

        $this->actingAs($user)
            ->get(route('applications.index'))
            ->assertOk()
            ->assertDontSee('Backend Developer');
    }
}

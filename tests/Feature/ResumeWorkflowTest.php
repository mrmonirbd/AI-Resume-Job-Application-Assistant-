<?php

namespace Tests\Feature;

use App\Models\JobAnalysis;
use App\Models\JobApplication;
use App\Models\Resume;
use App\Models\ResumeJobMatch;
use App\Models\ResumeTemplate;
use App\Models\TailoredResume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_creates_master_resume_with_verified_skills(): void
    {
        ResumeTemplate::create([
            'name' => 'Classic Clarity',
            'slug' => 'classic-clarity',
            'category' => 'Professional',
            'layout' => 'single-column',
        ]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->post(route('onboarding.store'), [
                'start_method' => 'manual',
                'name' => 'Jane Smith',
                'professional_title' => 'Laravel Developer',
                'location' => 'Austin, TX',
                'career_goal' => 'Backend engineering',
                'summary' => 'Builds production Laravel applications.',
                'skills' => 'Laravel, PHP, MySQL',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('resumes', [
            'user_id' => $user->id,
            'title' => 'Master Resume',
            'is_master' => true,
        ]);
        $this->assertDatabaseHas('skills', ['name' => 'Laravel']);
    }

    public function test_job_workflow_analyzes_matches_and_creates_tailored_resume(): void
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'onboarding_completed_at' => now()]);
        $resume = Resume::create([
            'user_id' => $user->id,
            'title' => 'Master Resume',
            'type' => 'master',
            'is_master' => true,
            'professional_title' => 'Backend Developer',
            'professional_summary' => 'Builds Laravel APIs with MySQL.',
        ]);
        $resume->skills()->createMany([
            ['name' => 'Laravel', 'sort_order' => 0],
            ['name' => 'PHP', 'sort_order' => 1],
            ['name' => 'MySQL', 'sort_order' => 2],
        ]);
        $resume->experiences()->create([
            'company' => 'Acme',
            'position' => 'Backend Developer',
            'description' => 'Built REST API services with Laravel and MySQL.',
        ]);

        $response = $this->actingAs($user)->post(route('jobs.store'), [
            'company' => 'BrightApps',
            'title' => 'Senior Laravel Developer',
            'location' => 'Remote',
            'description' => 'We need a senior developer with Laravel, PHP, MySQL, Docker, REST API, testing, and communication experience.',
        ]);

        $job = $user->jobs()->firstOrFail();
        $response->assertRedirect(route('jobs.show', $job));
        $this->assertDatabaseCount('job_analyses', 1);
        $this->assertDatabaseCount('applications', 1);

        $this->actingAs($user)
            ->post(route('jobs.match', $job), ['resume_id' => $resume->id])
            ->assertRedirect();

        $this->assertGreaterThan(0, ResumeJobMatch::firstOrFail()->overall_score);

        $this->actingAs($user)
            ->post(route('jobs.tailor', $job), ['resume_id' => $resume->id])
            ->assertRedirect();

        $this->assertDatabaseCount('tailored_resumes', 1);
        $this->assertTrue(TailoredResume::first()->resume->source_resume_id === $resume->id);
        $this->assertEquals('preparing', JobApplication::first()->status);
        $this->assertEquals('Senior Laravel Developer', JobAnalysis::first()->extracted_title);
    }
}

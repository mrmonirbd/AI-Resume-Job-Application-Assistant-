<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\User;
use App\Services\DeepSeekService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtsCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_nested_ai_category_scores_without_server_error(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        $resume = Resume::create([
            'user_id' => $user->id,
            'title' => 'Master Resume',
            'type' => 'master',
            'is_master' => true,
            'professional_title' => 'Backend Developer',
            'professional_summary' => 'Builds Laravel APIs.',
        ]);
        $resume->skills()->create(['name' => 'Laravel']);
        $resume->experiences()->create([
            'company' => 'Acme',
            'position' => 'Developer',
            'description' => 'Built Laravel APIs.',
        ]);

        $this->mock(DeepSeekService::class, function ($mock): void {
            $mock->shouldReceive('structured')
                ->once()
                ->andReturn([
                    'readiness_score' => ['score' => '84/100'],
                    'categories' => [
                        ['name' => 'Content', 'score' => ['value' => 88]],
                        ['category' => 'Structure', 'percentage' => '92%'],
                    ],
                    'recommendations' => [
                        ['text' => 'Keep section headings simple.'],
                    ],
                    'disclaimer' => ['text' => 'Internal estimate only.'],
                ]);
        });

        $this->actingAs($user)
            ->post(route('ats.store'), [
                'resume_id' => $resume->id,
                'job_description' => 'We need Laravel API experience.',
            ])
            ->assertOk()
            ->assertSee('Content')
            ->assertSee('88/100')
            ->assertSee('Structure')
            ->assertSee('92/100')
            ->assertSee('Keep section headings simple.')
            ->assertSee('Internal estimate only.');
    }
}

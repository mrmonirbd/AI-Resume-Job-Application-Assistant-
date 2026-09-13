<?php

namespace Tests\Feature;

use App\Models\AiTool;
use App\Models\Resume;
use App\Models\User;
use App\Services\DeepSeekService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cover_letter_tool_runs_with_typed_job_text_and_default_resume(): void
    {
        AiTool::create([
            'name' => 'Cover Letter Generator',
            'slug' => 'cover-letter-generator',
            'category' => 'application',
            'is_active' => true,
        ]);
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
            'status' => 'active',
            'last_edited_at' => now(),
        ]);

        $this->mock(DeepSeekService::class, function ($mock): void {
            $mock->shouldReceive('structured')
                ->once()
                ->andReturn([
                    'title' => 'Draft Role Cover Letter',
                    'content' => 'Generated cover letter content.',
                    'tone' => 'professional',
                    'length' => 'standard',
                ]);
        });

        $this->actingAs($user)
            ->post(route('ai-tools.run'), [
                'tool' => 'cover-letter-generator',
                'text' => 'We need a Laravel developer with API experience.',
            ])
            ->assertOk()
            ->assertSee('Draft Role Cover Letter')
            ->assertSee('Generated cover letter content.');

        $this->assertDatabaseHas('cover_letters', [
            'user_id' => $user->id,
            'resume_id' => $resume->id,
            'job_id' => null,
            'title' => 'Draft Role Cover Letter',
        ]);
    }

    public function test_cover_letter_tool_redirects_with_validation_when_job_context_is_empty(): void
    {
        AiTool::create([
            'name' => 'Cover Letter Generator',
            'slug' => 'cover-letter-generator',
            'category' => 'application',
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        Resume::create([
            'user_id' => $user->id,
            'title' => 'Master Resume',
            'type' => 'master',
            'is_master' => true,
            'professional_title' => 'Backend Developer',
            'professional_summary' => 'Builds Laravel APIs.',
            'status' => 'active',
            'last_edited_at' => now(),
        ]);

        $this->actingAs($user)
            ->from(route('ai-tools.index'))
            ->post(route('ai-tools.run'), [
                'tool' => 'cover-letter-generator',
            ])
            ->assertRedirect(route('ai-tools.index'))
            ->assertSessionHasErrors('text');
    }
}

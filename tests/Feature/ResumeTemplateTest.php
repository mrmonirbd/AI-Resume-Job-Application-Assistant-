<?php

namespace Tests\Feature;

use App\Livewire\ResumeBuilder;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResumeTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_gallery_renders_polished_template_library(): void
    {
        $this->seed();
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('templates.index'))
            ->assertSee('polished resume layouts')
            ->assertSee('Classic Clarity')
            ->assertSee('Black Timeline')
            ->assertSee('Technical Grid');

        $this->assertGreaterThanOrEqual(24, ResumeTemplate::where('is_active', true)->count());
    }

    public function test_created_resume_uses_selected_template_defaults(): void
    {
        $template = ResumeTemplate::create([
            'name' => 'Navy Profile',
            'slug' => 'navy-profile',
            'category' => 'Executive',
            'layout' => 'left-sidebar-navy',
            'font_family' => 'Inter',
            'accent_color' => '#0f2742',
            'settings' => ['style' => 'sidebar', 'sort' => 30],
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('resumes.store'), [
                'title' => 'Banking Analyst Resume',
                'professional_title' => 'Credit Analyst',
                'professional_summary' => 'Analyzes portfolio risk and financial statements.',
                'template_id' => $template->id,
                'spacing' => 'comfortable',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('resumes', [
            'user_id' => $user->id,
            'title' => 'Banking Analyst Resume',
            'template_id' => $template->id,
            'accent_color' => '#0f2742',
            'font_family' => 'Inter',
        ]);
    }

    public function test_resume_builder_updates_template_for_existing_resume(): void
    {
        $classic = ResumeTemplate::create([
            'name' => 'Classic Clarity',
            'slug' => 'classic-clarity',
            'category' => 'Professional',
            'layout' => 'single-column',
            'font_family' => 'Inter',
            'accent_color' => '#1f4e79',
            'settings' => ['style' => 'classic', 'sort' => 10],
            'is_active' => true,
        ]);
        $template = ResumeTemplate::create([
            'name' => 'Copper Header',
            'slug' => 'copper-header',
            'category' => 'Creative',
            'layout' => 'top-band-photo',
            'font_family' => 'Georgia',
            'accent_color' => '#9a6b55',
            'settings' => ['style' => 'top-band', 'sort' => 60],
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        $resume = Resume::create([
            'user_id' => $user->id,
            'template_id' => $classic->id,
            'title' => 'Master Resume',
            'type' => 'master',
            'is_master' => true,
            'professional_title' => 'Analyst',
            'professional_summary' => 'Builds reliable reports.',
            'accent_color' => '#1f4e79',
            'font_family' => 'Inter',
            'spacing' => 'comfortable',
            'status' => 'active',
            'last_edited_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.template_id', $template->id)
            ->call('saveOverview')
            ->assertSet('notice', 'Resume saved.');

        $this->assertDatabaseHas('resumes', [
            'id' => $resume->id,
            'template_id' => $template->id,
            'accent_color' => '#9a6b55',
            'font_family' => 'Georgia',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\ResumeBuilder;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
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
            ->assertSee('Sort')
            ->assertSee('Name A-Z')
            ->assertSee('Category')
            ->assertSee('onchange="this.form.submit()"', false)
            ->assertSee('Preview')
            ->assertSee('Use this template')
            ->assertSee('Configuration')
            ->assertSee('Next to editor')
            ->assertSee('name="template_slug"', false)
            ->assertSee('aspect-[210/297]', false)
            ->assertSee('max-w-[30rem]', false)
            ->assertDontSee('Uncategorized')
            ->assertDontSee('Apply')
            ->assertSee('images/demo-profile.svg')
            ->assertSee('Nusrat Jahan')
            ->assertSee('Credit Analyst')
            ->assertSee('Classic Clarity')
            ->assertSee('Black Timeline')
            ->assertSee('Technical Grid')
            ->assertSee('data-template-style="timeline"', false)
            ->assertSee('data-template-style="technical"', false)
            ->assertSee('data-template-style="soft-sidebar"', false);

        $this->assertGreaterThanOrEqual(24, ResumeTemplate::where('is_active', true)->count());
    }

    public function test_template_gallery_can_be_grouped_by_category(): void
    {
        $this->seed();
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('templates.index', ['sort' => 'category']))
            ->assertSee('Category')
            ->assertSee('Professional')
            ->assertSee('Executive')
            ->assertSee('Modern');
    }

    public function test_sidebar_template_thumbnail_uses_demo_profile_image(): void
    {
        $template = new ResumeTemplate([
            'name' => 'Executive Ledger',
            'slug' => 'executive-ledger',
            'category' => 'Executive',
            'layout' => 'left-sidebar-dark',
            'font_family' => 'Georgia',
            'accent_color' => '#334155',
            'settings' => [
                'style' => 'sidebar',
                'surface' => '#ffffff',
                'soft' => '#e2e8f0',
                'dark' => '#1f2937',
            ],
        ]);

        $html = Blade::render('<x-resume-template-thumbnail :template="$template" />', [
            'template' => $template,
        ]);

        $this->assertStringContainsString('data-template-layout="left-sidebar-dark"', $html);
        $this->assertStringContainsString('images/demo-profile.svg', $html);
        $this->assertStringNotContainsString('h-8 w-16 rounded-sm bg-white/90', $html);
    }

    public function test_template_configuration_form_creates_resume_and_redirects_to_editor(): void
    {
        $template = ResumeTemplate::create([
            'name' => 'Modern Signal',
            'slug' => 'modern-signal',
            'category' => 'Modern',
            'layout' => 'split-header',
            'font_family' => 'Inter',
            'accent_color' => '#0f766e',
            'settings' => ['style' => 'split', 'sort' => 40],
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post(route('resumes.store'), [
                'title' => 'Modern Signal Resume',
                'professional_title' => 'Credit Analyst',
                'professional_summary' => 'Prepared verified reports for business teams.',
                'template_id' => $template->id,
                'template_slug' => $template->slug,
                'spacing' => 'comfortable',
                'accent_color' => '#0f766e',
                'font_family' => 'Inter',
            ]);
        $resume = Resume::query()->firstOrFail();

        $response->assertRedirect(route('resumes.edit', $resume));
        $this->assertDatabaseHas('resumes', [
            'id' => $resume->id,
            'user_id' => $user->id,
            'title' => 'Modern Signal Resume',
            'professional_title' => 'Credit Analyst',
            'template_id' => $template->id,
            'accent_color' => '#0f766e',
            'font_family' => 'Inter',
        ]);
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

    public function test_resume_export_generates_single_page_a4_pdf(): void
    {
        $template = ResumeTemplate::create([
            'name' => 'Navy Profile',
            'slug' => 'navy-profile',
            'category' => 'Executive',
            'layout' => 'left-sidebar-navy',
            'font_family' => 'Inter',
            'accent_color' => '#0f2742',
            'settings' => [
                'style' => 'sidebar',
                'sidebar' => 'left',
                'dark' => '#0f2742',
                'soft' => '#dbeafe',
                'surface' => '#ffffff',
                'photo' => true,
            ],
            'is_active' => true,
        ]);
        $user = User::factory()->create([
            'name' => 'Demo User',
            'location' => 'Remote',
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        $resume = Resume::create([
            'user_id' => $user->id,
            'template_id' => $template->id,
            'title' => 'Navy Profile Resume',
            'type' => 'general',
            'professional_title' => 'Finance Manager',
            'professional_summary' => 'Finance manager with reporting and credit analysis experience.',
            'accent_color' => '#0f2742',
            'font_family' => 'Inter',
            'spacing' => 'compact',
            'last_edited_at' => now(),
        ]);
        $resume->skills()->createMany([
            ['name' => 'Financial Reporting', 'sort_order' => 0],
            ['name' => 'Credit Risk', 'sort_order' => 1],
        ]);
        $resume->experiences()->create([
            'company' => 'Acme Finance',
            'position' => 'Finance Manager',
            'description' => 'Prepared portfolio reports and reviewed credit risk.',
            'start_date' => '2021',
            'sort_order' => 0,
        ]);
        $resume->educations()->create([
            'school' => 'Dhaka University',
            'degree' => 'BBA',
            'field' => 'Finance',
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('resumes.export', $resume));
        $pdf = $response->baseResponse->getContent();

        $response->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertStringContainsString('/Count 1', $pdf);
        $this->assertStringContainsString('/MediaBox [0.000 0.000 595.280 841.890]', $pdf);
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

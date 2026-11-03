<?php

namespace Tests\Feature;

use App\Livewire\ResumeBuilder;
use App\Models\AiGeneration;
use App\Models\Concept;
use App\Models\Crm;
use App\Models\User;
use App\Services\AI\AIManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_workspace_pages_require_authentication(): void
    {
        foreach (['dashboard', 'resumes.index', 'resumes.create', 'jobs.index', 'jobs.create', 'applications.index', 'ats.create', 'ai-tools.index', 'cover-letters.index', 'templates.index', 'profile.edit', 'subscription.show', 'admin.dashboard'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_users_cannot_read_export_modify_or_duplicate_another_users_resume(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $resume = $owner->resumes()->create(['title' => 'Private resume']);

        $this->actingAs($otherUser);
        foreach (['resumes.edit', 'resumes.export', 'resumes.export-word', 'resumes.export-image'] as $route) {
            $this->get(route($route, $resume))->assertForbidden();
        }
        $this->post(route('resumes.duplicate', $resume))->assertForbidden();
        $this->patch(route('resumes.update', $resume), ['title' => 'Changed'])->assertForbidden();
        $this->delete(route('resumes.destroy', $resume))->assertForbidden();

        $this->assertDatabaseCount('resumes', 1);
        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'title' => 'Private resume', 'deleted_at' => null]);
    }

    public function test_users_cannot_analyze_or_remove_another_users_job(): void
    {
        $owner = User::factory()->create();
        $job = $owner->jobs()->create(['title' => 'Private job', 'company' => 'Acme', 'description' => 'Private description']);
        $this->actingAs(User::factory()->create());

        $this->get(route('jobs.show', $job))->assertForbidden();
        foreach (['jobs.analyze', 'jobs.match', 'jobs.tailor', 'jobs.cover-letter'] as $route) {
            $this->post(route($route, $job))->assertForbidden();
        }
        $this->delete(route('jobs.destroy', $job))->assertForbidden();

        $this->assertDatabaseHas('jobs', ['id' => $job->id, 'deleted_at' => null]);
        $this->assertDatabaseCount('ai_generations', 0);
    }

    public function test_regular_users_cannot_access_admin(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_resume_builder_rejects_empty_section_titles_without_writing_other_changes(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume']);
        $section = $resume->sections()->create(['type' => 'summary', 'title' => 'Summary', 'sort_order' => 0]);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set("sections.{$section->id}.title", '')
            ->set("sections.{$section->id}.content", 'Unsaved changes')
            ->call('saveSections')
            ->assertHasErrors(["sections.{$section->id}.title" => 'required'])
            ->assertSee('Please check the following fields:');

        $this->assertDatabaseHas('resume_sections', ['id' => $section->id, 'title' => 'Summary', 'content' => null]);
    }

    public function test_resume_builder_displays_skill_validation_and_saves_valid_skills(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume']);
        $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume]);

        $component->call('addSkill')->assertHasErrors(['newSkill.name' => 'required']);
        $this->assertDatabaseCount('skills', 0);

        $component->set('newSkill.name', 'Laravel')->call('addSkill')->assertHasNoErrors();
        $this->assertDatabaseHas('skills', ['resume_id' => $resume->id, 'name' => 'Laravel']);
    }

    public function test_resume_builder_cannot_remove_another_resumes_skill(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume']);
        $otherResume = User::factory()->create()->resumes()->create(['title' => 'Private resume']);
        $skill = $otherResume->skills()->create(['name' => 'Private skill']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])->call('removeSkill', $skill->id);

        $this->assertModelExists($skill);
    }

    public function test_resume_rejects_css_in_color_and_font_settings(): void
    {
        $this->actingAs(User::factory()->create())->post(route('resumes.store'), [
            'title' => 'My resume',
            'accent_color' => 'red; display:none',
            'font_family' => 'Inter; background:url(https://example.test)',
        ])->assertSessionHasErrors(['accent_color', 'font_family']);

        $this->assertDatabaseCount('resumes', 0);
    }

    public function test_builder_uses_field_paths_for_invalid_overview_settings(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.title', '')
            ->set('overview.accent_color', 'invalid')
            ->call('saveOverview')
            ->assertHasErrors(['overview.title' => 'required', 'overview.accent_color' => 'hex_color']);

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'title' => 'My resume']);
    }

    public function test_builder_limits_ai_summary_requests_before_calling_provider(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume', 'professional_summary' => 'Builds Laravel applications.']);
        $key = 'resume-summary:'.$user->id;
        RateLimiter::clear($key);
        foreach (range(1, 12) as $attempt) {
            RateLimiter::hit($key, 60);
        }

        try {
            Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
                ->call('improveSummary')
                ->assertHasErrors('overview.professional_summary')
                ->assertSee('Too many AI requests.');

            $this->assertDatabaseCount('ai_generations', 0);
        } finally {
            RateLimiter::clear($key);
        }
    }

    public function test_ai_audit_records_do_not_store_resume_content(): void
    {
        config(['services.deepseek.key' => null]);
        $user = User::factory()->create();

        $result = app(AIManager::class)->json('privacy-test', 'Rewrite', 'Private prompt', fn (): array => ['ai_rewrite' => 'Private career history'], $user);

        $this->assertSame('Private career history', $result['ai_rewrite']);
        $this->assertStringNotContainsString('Private career history', AiGeneration::firstOrFail()->response_summary);
        $this->assertDatabaseHas('ai_usage', ['user_id' => $user->id, 'feature' => 'privacy-test', 'requests' => 1]);
    }

    public function test_crm_rejected_credentials_return_a_validation_error(): void
    {
        $this->crmConcept();
        Http::preventStrayRequests();
        Http::fake(['https://crm.example.test/api/s/v3/users/authenticates.json' => Http::response(['status' => 'error'], 401)]);

        $this->post(route('login'), ['email' => 'invalid@example.com', 'password' => 'incorrect'])
            ->assertSessionHasErrors('error');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        Http::assertSentCount(1);
    }

    public function test_crm_login_allows_missing_subscription_without_creating_a_subscription(): void
    {
        $this->crmConcept();
        Http::preventStrayRequests();
        Http::fake([
            'https://crm.example.test/api/s/v3/users/authenticates.json' => Http::response(['status' => 'success', 'data' => ['id' => 'web-1', 'customer_id' => 'customer-1']]),
            'https://crm.example.test/api/s/v3/customers/customer-1.json' => Http::response(['status' => 'success', 'data' => ['id' => 'customer-1', 'first_name' => 'John', 'last_name' => 'Doe']]),
            'https://crm.example.test/api/s/v3/customers/customer-1/subscriptions/active.json' => Http::response(['status' => 'success', 'data' => []]),
        ]);

        $this->post(route('login'), ['email' => 'missing@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('subscriptions', 0);

        $this->post(route('logout'));
        $this->post(route('login'), ['email' => 'missing@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        Http::assertSentCount(4);
    }

    public function test_legal_policy_removes_active_content_and_obfuscated_javascript_links(): void
    {
        $this->crmConcept();
        Http::preventStrayRequests();
        Http::fake(['https://crm.example.test/api/s/v3/website/tos/rendered.json*' => Http::response(['data' => ['html' => '<h2>Safe policy</h2><a href="java&#x09;script:alert(1)" onclick="alert(2)">Unsafe link</a><svg><a href="javascript:alert(3)">SVG attack</a></svg><form action="https://evil.test"><input name="password"></form><a href="https://example.test/terms">Safe link</a>']])]);

        $this->get(route('legal.show', 'privacy'))
            ->assertSee('Safe policy')->assertSee('https://example.test/terms')
            ->assertDontSee('script:alert', false)->assertDontSee('onclick', false)
            ->assertDontSee('SVG attack')->assertDontSee('https://evil.test');

        Http::assertSentCount(1);
    }

    public function test_jobs_reject_non_web_urls_and_unbounded_descriptions(): void
    {
        $this->actingAs(User::factory()->create())->post(route('jobs.store'), [
            'title' => 'Developer', 'company' => 'Acme',
            'job_url' => 'ftp://example.test/job', 'description' => str_repeat('x', 30001),
        ])->assertSessionHasErrors(['job_url', 'description']);

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_main_workspace_pages_render_for_a_verified_user(): void
    {
        $this->seed();
        $user = User::where('email', 'demo@example.com')->firstOrFail();
        $this->actingAs($user);

        foreach (['dashboard', 'resumes.index', 'resumes.create', 'jobs.index', 'jobs.create', 'applications.index', 'ats.create', 'ai-tools.index', 'cover-letters.index', 'templates.index', 'profile.edit', 'profile.security', 'subscription.show', 'subscription.payment-history'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_dashboard_counts_all_applications_while_showing_only_recent_records(): void
    {
        $user = User::factory()->create();
        $job = $user->jobs()->create(['title' => 'Developer', 'company' => 'Acme', 'description' => 'Build applications']);
        foreach (range(1, 8) as $index) {
            $user->applications()->create(['job_id' => $job->id, 'status' => $index === 1 ? 'interview' : 'applied']);
        }

        $this->actingAs($user)->get(route('dashboard'))
            ->assertViewHas('applications', fn ($applications): bool => $applications->count() === 6)
            ->assertViewHas('stats', fn (array $stats): bool => $stats['active_applications'] === 8 && $stats['interviews'] === 1);
    }

    public function test_password_recovery_is_rate_limited(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.33']);
        foreach (range(1, 6) as $attempt) {
            $this->post(route('password.email'), ['email' => 'not-an-email'])->assertSessionHasErrors('email');
        }

        $this->post(route('password.email'), ['email' => 'not-an-email'])->assertTooManyRequests();
    }

    public function test_job_creation_is_rate_limited_before_ai_work(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        foreach (range(1, 12) as $attempt) {
            $this->post(route('jobs.store'), [])->assertSessionHasErrors('description');
        }

        $this->post(route('jobs.store'), [])->assertTooManyRequests();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('ai_generations', 0);
    }

    public function test_builder_can_generate_and_review_a_summary_suggestion(): void
    {
        config(['services.deepseek.key' => null]);
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume', 'professional_summary' => 'Builds Laravel applications.']);
        $key = 'resume-summary:'.$user->id;
        RateLimiter::clear($key);

        try {
            Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
                ->call('improveSummary')
                ->assertHasNoErrors()
                ->assertSee('AI suggestion ready');

            $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_summary' => 'Builds Laravel applications.']);
            $this->assertDatabaseCount('ai_generations', 1);
        } finally {
            RateLimiter::clear($key);
        }
    }

    private function crmConcept(): void
    {
        $crm = Crm::create(['name' => 'Test CRM', 'api_url' => 'https://crm.example.test', 'is_enabled' => true]);
        Concept::create(['name' => 'Test concept', 'domain_name' => 'localhost', 'crm_id' => $crm->id, 'crm_api_key' => 'test-key', 'is_default' => true, 'is_enabled' => true]);
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\ResumeBuilder;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Process;
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
            ->assertSee('John Doe')
            ->assertSee('Berlin, Germany')
            ->assertSee('New York, NY')
            ->assertSee('Search templates')
            ->assertDontSee('Dhaka')
            ->assertDontSee('+880')
            ->assertSee('Business Analyst')
            ->assertSee('Classic Clarity')
            ->assertSee('Black Timeline')
            ->assertSee('Technical Grid')
            ->assertSee('data-template-style="timeline"', false)
            ->assertSee('data-template-style="technical"', false)
            ->assertSee('data-template-style="soft-sidebar"', false)
            ->assertSee('data-template-variant="executive-ledger"', false)
            ->assertSee('data-template-variant="navy-profile"', false)
            ->assertSee('data-template-variant="slate-manager"', false)
            ->assertSee('data-template-variant="marketing-sidebar"', false)
            ->assertSee('data-template-variant="copper-header"', false)
            ->assertSee('data-template-variant="rose-designer"', false)
            ->assertSee('data-template-variant="graduate-focus"', false)
            ->assertSee('data-template-variant="academic-plain"', false);

        $this->assertGreaterThanOrEqual(24, ResumeTemplate::where('is_active', true)->count());
    }

    public function test_repeated_template_families_render_distinct_thumbnail_variants(): void
    {
        $this->seed();
        $renderedVariants = [];

        ResumeTemplate::where('is_active', true)->get()->each(function (ResumeTemplate $template) use (&$renderedVariants): void {
            $html = Blade::render('<x-resume-template-thumbnail :template="$template" />', [
                'template' => $template,
            ]);

            $this->assertStringContainsString('data-template-variant="'.$template->slug.'"', $html);
            $renderedVariants[] = $template->slug;
        });

        $this->assertSame($renderedVariants, array_values(array_unique($renderedVariants)));
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

    public function test_resume_can_be_exported_as_a_word_document(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        $resume = Resume::create([
            'user_id' => $user->id,
            'title' => 'Finance Manager Resume',
            'type' => 'general',
            'professional_title' => 'Finance Manager',
            'professional_summary' => 'Finance manager with reporting experience.',
            'last_edited_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('resumes.export-word', $resume));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->assertHeader('content-disposition', 'attachment; filename="finance-manager-resume.docx"');

        $document = $response->getContent();
        $temporaryDocumentPath = tempnam(sys_get_temp_dir(), 'resume-word-test-');

        file_put_contents($temporaryDocumentPath, $document);
        $archive = new \ZipArchive;

        try {
            $this->assertTrue($archive->open($temporaryDocumentPath) === true);
            $this->assertNotFalse($archive->locateName('word/document.xml'));
            $this->assertStringContainsString('Finance Manager', (string) $archive->getFromName('word/document.xml'));
        } finally {
            $archive->close();
            @unlink($temporaryDocumentPath);
        }
    }

    public function test_resume_can_be_exported_as_a_png_image(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        $resume = Resume::create([
            'user_id' => $user->id,
            'title' => 'Finance Manager Resume',
            'type' => 'general',
            'professional_title' => 'Finance Manager',
            'professional_summary' => 'Finance manager with reporting experience.',
            'last_edited_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('resumes.export-image', $resume));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'image/png')
            ->assertHeader('content-disposition', 'attachment; filename="finance-manager-resume.png"');

        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $response->getContent());
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

    public function test_selecting_a_template_updates_preview_without_saving_the_resume(): void
    {
        $user = User::factory()->create();
        $classic = ResumeTemplate::create([
            'name' => 'Classic preview', 'slug' => 'classic-preview', 'category' => 'Professional',
            'layout' => 'single-column', 'accent_color' => '#123456', 'font_family' => 'Inter',
            'settings' => ['style' => 'classic'], 'is_active' => true,
        ]);
        $sidebar = ResumeTemplate::create([
            'name' => 'Sidebar preview', 'slug' => 'sidebar-preview', 'category' => 'Executive',
            'layout' => 'left-sidebar-dark', 'accent_color' => '#654321', 'font_family' => 'Georgia',
            'settings' => ['style' => 'sidebar', 'dark' => '#112233'], 'is_active' => true,
        ]);
        $resume = $user->resumes()->create([
            'title' => 'My resume', 'template_id' => $classic->id,
            'accent_color' => '#123456', 'font_family' => 'Inter',
        ]);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.template_id', $sidebar->id)
            ->assertSet('overview.accent_color', '#654321')
            ->assertSet('overview.font_family', 'Georgia')
            ->assertSee('background: #112233; color: #ffffff;', false)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('resumes', [
            'id' => $resume->id, 'template_id' => $classic->id,
            'accent_color' => '#123456', 'font_family' => 'Inter',
        ]);
    }

    public function test_every_seeded_template_updates_the_live_preview_without_persisting_selection(): void
    {
        $this->seed();
        $user = User::where('email', 'demo@example.com')->firstOrFail();
        $resume = $user->masterResume()->firstOrFail();
        ResumeTemplate::create([
            'name' => 'Operator Compact', 'slug' => 'operator-compact', 'category' => 'Professional',
            'layout' => 'compact', 'accent_color' => '#334155', 'font_family' => 'Inter',
            'settings' => ['style' => 'classic', 'soft' => '#e2e8f0'], 'is_active' => true,
        ]);
        $originalTemplateId = $resume->template_id;
        $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume]);

        foreach (ResumeTemplate::where('is_active', true)->get() as $template) {
            $component->set('overview.template_id', $template->id)->assertHasNoErrors();
            $document = new \DOMDocument;
            $previousErrors = libxml_use_internal_errors(true);
            $document->loadHTML($component->html());
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
            $xpath = new \DOMXPath($document);
            $root = $xpath->query('//*[@data-preview-template="'.$template->slug.'"]')->item(0);

            $this->assertNotNull($root, $template->name.' must be the rendered preview.');
            $this->assertStringContainsString($user->name, $root->textContent);
            if ($template->slug === 'operator-compact') {
                $this->assertGreaterThan(0, $xpath->query('.//header[contains(@class, "grid-cols-")]', $root)->length);
            }

            $style = data_get($template->settings, 'style');
            $this->assertSame($style, $root->getAttribute('data-preview-style'));

            $layoutSelectors = [
                'split' => './/header//div[contains(@class, "min-h-32")]',
                'balanced' => './/main[contains(@class, "grid-cols-2")]',
                'compact' => './/main[contains(@class, "grid-cols-2")]',
                'dense' => './/main[contains(@class, "columns-2")]',
                'brief' => './/header[contains(@class, "border-l-4")]',
                'minimal' => './/main[contains(@class, "border-t")]',
                'editorial' => './/main[contains(@class, "border-l")]',
                'academic' => './/main/section[1]/h4[text()="Education"]',
            ];

            if (isset($layoutSelectors[$style])) {
                $this->assertGreaterThan(0, $xpath->query($layoutSelectors[$style], $root)->length, $template->name.' must render its own layout.');
            }
        }

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'template_id' => $originalTemplateId]);
    }

    public function test_overview_typing_updates_preview_without_saving_until_save_is_clicked(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create([
            'title' => 'Saved resume', 'professional_title' => 'Saved title',
            'professional_summary' => 'Saved summary', 'accent_color' => '#123456',
        ]);
        $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.professional_title', 'Draft engineering lead')
            ->set('overview.professional_summary', 'Draft summary while typing')
            ->set('overview.accent_color', '#654321');

        $html = $this->documentPreviewHtml($component->html());
        $this->assertStringContainsString('Draft engineering lead', $html);
        $this->assertStringContainsString('Draft summary while typing', $html);
        $this->assertStringContainsString('#654321', $html);
        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_title' => 'Saved title', 'professional_summary' => 'Saved summary', 'accent_color' => '#123456']);

        $component->call('saveOverview')->assertHasNoErrors();

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_title' => 'Draft engineering lead', 'professional_summary' => 'Draft summary while typing', 'accent_color' => '#654321']);
    }

    public function test_new_resume_entries_appear_as_drafts_without_database_writes(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'Draft resume']);
        $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('newSkill.name', 'Draft Kubernetes')
            ->set('newExperience.company', 'Draft Northstar Labs')
            ->set('newExperience.position', 'Draft engineer')
            ->set('newEducation.school', 'Draft university')
            ->set('newProject.name', 'Draft analytics platform');

        $html = $this->documentPreviewHtml($component->html());
        foreach (['Draft Kubernetes', 'Draft Northstar Labs', 'Draft engineer', 'Draft university', 'Draft analytics platform'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        foreach (['skills', 'experiences', 'educations', 'projects'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_saving_a_skill_keeps_other_unsaved_overview_changes(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'Saved resume', 'professional_title' => 'Saved title']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.professional_title', 'Unsaved title')
            ->set('newSkill.name', 'Laravel')
            ->call('addSkill')
            ->assertHasNoErrors()
            ->assertSet('overview.professional_title', 'Unsaved title');

        $this->assertDatabaseHas('skills', ['resume_id' => $resume->id, 'name' => 'Laravel']);
        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_title' => 'Saved title']);
    }

    public function test_section_notes_and_visibility_update_preview_without_saving(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'Saved resume', 'professional_summary' => 'Original profile summary']);
        $section = $resume->sections()->create(['type' => 'summary', 'title' => 'Summary', 'is_visible' => true, 'sort_order' => 0]);
        $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set("sections.{$section->id}.title", 'My profile')
            ->set("sections.{$section->id}.content", 'Draft note for this section');

        $html = $this->documentPreviewHtml($component->html());
        $this->assertStringContainsString('My profile', $html);
        $this->assertStringContainsString('Draft note for this section', $html);
        $component->set("sections.{$section->id}.is_visible", false);
        $this->assertStringNotContainsString('Original profile summary', $this->documentPreviewHtml($component->html()));
        $this->assertDatabaseHas('resume_sections', ['id' => $section->id, 'title' => 'Summary', 'content' => null, 'is_visible' => true]);
    }

    public function test_accepting_an_ai_suggestion_updates_the_draft_without_saving(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume', 'professional_summary' => 'Saved summary']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('aiSuggestion', ['ai_rewrite' => 'Reviewed draft summary'])
            ->call('acceptSuggestion')
            ->assertSet('overview.professional_summary', 'Reviewed draft summary')
            ->assertSet('aiSuggestion', null);

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'professional_summary' => 'Saved summary']);
    }

    public function test_custom_color_is_saved_after_selecting_a_different_template(): void
    {
        $this->seed();
        $user = User::where('email', 'demo@example.com')->firstOrFail();
        $resume = $user->masterResume()->firstOrFail();
        $template = ResumeTemplate::where('id', '!=', $resume->template_id)->firstOrFail();

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.template_id', $template->id)
            ->set('overview.accent_color', '#abcdef')
            ->call('saveOverview')->assertHasNoErrors();

        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'template_id' => $template->id, 'accent_color' => '#abcdef']);
    }

    public function test_editor_exports_the_current_draft_without_saving_it(): void
    {
        $this->seed();
        $user = User::where('email', 'demo@example.com')->firstOrFail();
        $resume = $user->masterResume()->firstOrFail();
        $originalTemplateId = $resume->template_id;
        $originalTitle = $resume->title;
        $template = ResumeTemplate::where('slug', 'modern-signal')->firstOrFail();
        $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.template_id', $template->id)
            ->set('overview.title', 'Current Draft Export')
            ->set('overview.professional_summary', 'Current draft summary exported before saving.')
            ->dispatch('export-resume', format: 'pdf')
            ->assertHasNoErrors()
            ->assertFileDownloaded('current-draft-export.pdf', contentType: 'application/pdf');

        $pdf = base64_decode($component->effects['download']['content']);
        $this->assertStringStartsWith('%PDF-', $pdf);
        $temporaryPdf = tempnam(sys_get_temp_dir(), 'resume-test-');
        try {
            file_put_contents($temporaryPdf, $pdf);
            $text = Process::run(['pdftotext', $temporaryPdf, '-'])->throw()->output();
            $this->assertStringContainsString('Current draft summary exported before saving.', $text);
        } finally {
            unlink($temporaryPdf);
        }
        $this->assertDatabaseHas('resumes', ['id' => $resume->id, 'title' => $originalTitle, 'template_id' => $originalTemplateId]);
    }

    public function test_image_export_renders_the_selected_split_template_and_custom_color(): void
    {
        $this->seed();
        $user = User::where('email', 'demo@example.com')->firstOrFail();
        $resume = $user->masterResume()->firstOrFail();
        $template = ResumeTemplate::where('slug', 'modern-signal')->firstOrFail();
        $component = Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.template_id', $template->id)
            ->set('overview.accent_color', '#c45d20')
            ->call('exportCurrent', 'png')
            ->assertHasNoErrors()
            ->assertFileDownloaded(contentType: 'image/png');

        $png = base64_decode($component->effects['download']['content']);
        $image = imagecreatefromstring($png);
        $pixel = imagecolorat($image, (int) (imagesx($image) * 0.85), (int) (imagesy($image) * 0.1));
        $this->assertSame(0xC45D20, $pixel & 0xFFFFFF, 'The split header accent must be visible in the exported image.');
        imagedestroy($image);
    }

    public function test_invalid_draft_settings_cannot_be_exported(): void
    {
        $user = User::factory()->create();
        $resume = $user->resumes()->create(['title' => 'My resume']);

        Livewire::actingAs($user)->test(ResumeBuilder::class, ['resume' => $resume])
            ->set('overview.accent_color', 'red; display:none')
            ->call('exportCurrent')
            ->assertHasErrors(['overview.accent_color' => 'hex_color'])
            ->assertNoFileDownloaded();
    }

    public function test_all_seeded_template_layouts_can_be_exported_as_pdf(): void
    {
        $this->seed();
        $user = User::where('email', 'demo@example.com')->firstOrFail();
        $resume = $user->masterResume()->firstOrFail();
        $this->actingAs($user);

        foreach (ResumeTemplate::where('is_active', true)->get() as $template) {
            $resume->update(['template_id' => $template->id, 'accent_color' => $template->accent_color, 'font_family' => $template->font_family]);
            $response = $this->get(route('resumes.export', $resume));
            $response->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent(), $template->name.' must export successfully.');
        }
    }

    private function documentPreviewHtml(string $html): string
    {
        $document = new \DOMDocument;
        $previousErrors = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);
        $preview = (new \DOMXPath($document))->query('//*[@data-preview-template]')->item(0);

        return $document->saveHTML($preview);
    }
}

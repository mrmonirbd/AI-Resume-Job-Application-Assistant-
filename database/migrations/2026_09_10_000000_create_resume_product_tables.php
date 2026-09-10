<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('monthly_price_cents')->default(0);
            $table->json('features')->nullable();
            $table->json('limits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('resume_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->string('layout');
            $table->string('font_family')->default('Inter');
            $table->string('accent_color')->default('#2563eb');
            $table->json('settings')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('resume_templates')->nullOnDelete();
            $table->foreignId('source_resume_id')->nullable()->constrained('resumes')->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('general')->index();
            $table->string('professional_title')->nullable();
            $table->text('professional_summary')->nullable();
            $table->string('accent_color')->default('#2563eb');
            $table->string('font_family')->default('Inter');
            $table->string('spacing')->default('comfortable');
            $table->boolean('is_master')->default(false)->index();
            $table->string('status')->default('active')->index();
            $table->json('settings')->nullable();
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'type']);
        });

        Schema::create('resume_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('title');
            $table->text('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company');
            $table->string('position');
            $table->string('location')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->json('bullets')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('school');
            $table->string('degree')->nullable();
            $table->string('field')->nullable();
            $table->string('location')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable()->index();
            $table->string('proficiency')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['resume_id', 'name']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->json('technologies')->nullable();
            $table->json('bullets')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->string('issued_at')->nullable();
            $table->string('expires_at')->nullable();
            $table->string('credential_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('proficiency')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('issuer')->nullable();
            $table->string('awarded_at')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('publisher')->nullable();
            $table->string('published_at')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('volunteer_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('organization');
            $table->string('role')->nullable();
            $table->string('location')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->text('description')->nullable();
            $table->json('bullets')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('url');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company');
            $table->string('title');
            $table->string('job_url')->nullable();
            $table->string('location')->nullable();
            $table->string('salary')->nullable();
            $table->longText('description');
            $table->text('notes')->nullable();
            $table->string('seniority')->nullable();
            $table->string('status')->default('saved')->index();
            $table->string('stage')->default('saved')->index();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('deadline_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'stage']);
        });

        Schema::create('job_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('complete')->index();
            $table->string('extracted_title')->nullable();
            $table->string('seniority')->nullable();
            $table->json('required_skills')->nullable();
            $table->json('preferred_skills')->nullable();
            $table->json('technologies')->nullable();
            $table->json('experience_requirements')->nullable();
            $table->json('education_requirements')->nullable();
            $table->json('responsibilities')->nullable();
            $table->json('keywords')->nullable();
            $table->json('soft_skills')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('resume_job_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->unsignedTinyInteger('overall_score')->default(0);
            $table->unsignedTinyInteger('skills_score')->default(0);
            $table->unsignedTinyInteger('experience_score')->default(0);
            $table->unsignedTinyInteger('education_score')->default(0);
            $table->unsignedTinyInteger('keyword_score')->default(0);
            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->json('recommendations')->nullable();
            $table->text('disclaimer')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
            $table->index(['resume_id', 'job_id']);
        });

        Schema::create('tailored_resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_resume_id')->constrained('resumes')->cascadeOnDelete();
            $table->foreignId('resume_id')->constrained('resumes')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('resume_job_match_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->json('ai_summary')->nullable();
            $table->json('suggested_missing_information')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('jobs')->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained('resumes')->nullOnDelete();
            $table->foreignId('tailored_resume_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('saved')->index();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('interview_at')->nullable();
            $table->string('interview_type')->nullable();
            $table->text('interview_notes')->nullable();
            $table->text('notes')->nullable();
            $table->json('activity_timeline')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
        });

        Schema::create('cover_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->nullable()->constrained('jobs')->nullOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained('resumes')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->string('title');
            $table->string('tone')->default('professional');
            $table->string('length')->default('standard');
            $table->longText('content');
            $table->string('status')->default('draft')->index();
            $table->boolean('generated_by_ai')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ai_tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('resume');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ai_tool_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->default('deepseek')->index();
            $table->string('feature')->index();
            $table->string('model')->nullable();
            $table->string('prompt_hash')->nullable();
            $table->text('request_summary')->nullable();
            $table->longText('response_summary')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('cost_estimate', 10, 6)->default(0);
            $table->string('status')->default('complete')->index();
            $table->json('metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date')->index();
            $table->string('provider')->default('deepseek');
            $table->string('feature')->index();
            $table->unsignedInteger('requests')->default(0);
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->decimal('cost_estimate', 10, 6)->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'date', 'provider', 'feature']);
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('ai_usage');
        Schema::dropIfExists('ai_generations');
        Schema::dropIfExists('ai_tools');
        Schema::dropIfExists('cover_letters');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('tailored_resumes');
        Schema::dropIfExists('resume_job_matches');
        Schema::dropIfExists('job_analyses');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('custom_sections');
        Schema::dropIfExists('links');
        Schema::dropIfExists('volunteer_experiences');
        Schema::dropIfExists('publications');
        Schema::dropIfExists('awards');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('certifications');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('resume_sections');
        Schema::dropIfExists('resumes');
        Schema::dropIfExists('resume_templates');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};

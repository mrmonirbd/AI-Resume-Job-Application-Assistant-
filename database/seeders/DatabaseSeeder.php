<?php

namespace Database\Seeders;

use App\Models\AiTool;
use App\Models\Plan;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedPlans();
        $this->seedTemplates();
        $this->seedAiTools();
        $this->seedDemoUser();
    }

    private function seedPlans(): void
    {
        foreach ([
            [
                'name' => 'Free',
                'slug' => 'free',
                'monthly_price_cents' => 0,
                'features' => ['master_resume', 'basic_templates', 'ats_checker'],
                'limits' => ['master_resumes' => 1, 'ai_generations_per_month' => 10, 'pdf_exports_per_month' => 3],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'monthly_price_cents' => 1900,
                'features' => ['unlimited_resumes', 'premium_templates', 'job_tailoring', 'ats_checker', 'cover_letters', 'interview_prep'],
                'limits' => ['master_resumes' => 1, 'ai_generations_per_month' => 300, 'pdf_exports_per_month' => 100],
            ],
        ] as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }

    private function seedTemplates(): void
    {
        $templates = [
            ['Classic Clarity', 'classic-clarity', 'Professional', 'single-column', 'Inter', '#1f4e79', ['header' => 'centered', 'density' => 'balanced']],
            ['Executive Ledger', 'executive-ledger', 'Executive', 'two-column-sidebar', 'Georgia', '#334155', ['header' => 'left-rail', 'density' => 'compact']],
            ['Modern Signal', 'modern-signal', 'Modern', 'split-header', 'Inter', '#0f766e', ['header' => 'banded', 'density' => 'balanced']],
            ['Minimal Line', 'minimal-line', 'Minimal', 'single-column-rule', 'Arial', '#111827', ['header' => 'thin-rule', 'density' => 'airy']],
            ['Graduate Focus', 'graduate-focus', 'Graduate', 'education-first', 'Inter', '#4f46e5', ['header' => 'compact', 'density' => 'balanced']],
            ['Technical Grid', 'technical-grid', 'Modern', 'skills-sidebar', 'IBM Plex Sans', '#0369a1', ['header' => 'grid', 'density' => 'compact']],
            ['Consultant Brief', 'consultant-brief', 'Professional', 'impact-first', 'Aptos', '#7c2d12', ['header' => 'brief', 'density' => 'balanced']],
            ['Studio Profile', 'studio-profile', 'Modern', 'portfolio-forward', 'Inter', '#be123c', ['header' => 'portfolio', 'density' => 'airy']],
            ['Academic Plain', 'academic-plain', 'Minimal', 'publication-forward', 'Times New Roman', '#374151', ['header' => 'formal', 'density' => 'comfortable']],
            ['Operator Compact', 'operator-compact', 'Professional', 'dense-single-column', 'Arial', '#365314', ['header' => 'compact', 'density' => 'dense']],
        ];

        foreach ($templates as [$name, $slug, $category, $layout, $font, $accent, $settings]) {
            ResumeTemplate::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'category' => $category,
                    'layout' => $layout,
                    'font_family' => $font,
                    'accent_color' => $accent,
                    'settings' => $settings,
                    'is_premium' => ! in_array($slug, ['classic-clarity', 'minimal-line', 'graduate-focus'], true),
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedAiTools(): void
    {
        foreach ([
            ['Professional Summary Generator', 'professional-summary-generator', 'resume'],
            ['Experience Bullet Generator', 'experience-bullet-generator', 'resume'],
            ['Resume Rewriter', 'resume-rewriter', 'resume'],
            ['Skills Analyzer', 'skills-analyzer', 'resume'],
            ['Job Description Analyzer', 'job-description-analyzer', 'job'],
            ['Cover Letter Generator', 'cover-letter-generator', 'application'],
            ['LinkedIn Headline Generator', 'linkedin-headline-generator', 'linkedin'],
            ['LinkedIn About Generator', 'linkedin-about-generator', 'linkedin'],
            ['Professional Bio Generator', 'professional-bio-generator', 'profile'],
            ['Recruiter Message Generator', 'recruiter-message-generator', 'outreach'],
            ['Application Email Generator', 'application-email-generator', 'outreach'],
            ['Follow-Up Email Generator', 'follow-up-email-generator', 'outreach'],
            ['Interview Question Generator', 'interview-question-generator', 'interview'],
        ] as [$name, $slug, $category]) {
            AiTool::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'category' => $category,
                    'description' => 'Structured AI career utility that uses verified resume context and keeps recommendations separate from facts.',
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedDemoUser(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'professional_title' => 'Laravel Backend Developer',
                'location' => 'Remote',
                'career_goal' => 'Tailor backend engineering applications quickly and accurately.',
                'email_verified_at' => now(),
                'onboarding_completed_at' => now(),
            ]
        );

        $template = ResumeTemplate::where('slug', 'classic-clarity')->first();

        $resume = Resume::updateOrCreate(
            ['user_id' => $user->id, 'is_master' => true],
            [
                'template_id' => $template?->id,
                'title' => 'Master Resume',
                'type' => 'master',
                'professional_title' => 'Laravel Backend Developer',
                'professional_summary' => 'Backend developer with verified experience building Laravel APIs, MySQL-backed services, and clean application workflows.',
                'accent_color' => '#0f766e',
                'font_family' => 'Inter',
                'spacing' => 'comfortable',
                'status' => 'active',
                'last_edited_at' => now(),
            ]
        );

        foreach ([
            'personal' => 'Personal Information',
            'summary' => 'Professional Summary',
            'experience' => 'Work Experience',
            'education' => 'Education',
            'skills' => 'Skills',
            'projects' => 'Projects',
            'certifications' => 'Certifications',
            'languages' => 'Languages',
        ] as $index => $title) {
            $resume->sections()->updateOrCreate(
                ['type' => $index],
                [
                    'title' => $title,
                    'sort_order' => array_search($index, ['personal', 'summary', 'experience', 'education', 'skills', 'projects', 'certifications', 'languages'], true),
                    'is_visible' => true,
                ]
            );
        }

        foreach (['Laravel', 'PHP', 'MySQL', 'REST API', 'Livewire', 'Tailwind CSS', 'Git', 'Testing'] as $sortOrder => $skill) {
            $resume->skills()->updateOrCreate(
                ['name' => $skill],
                ['category' => 'Core', 'sort_order' => $sortOrder, 'is_verified' => true, 'is_visible' => true]
            );
        }

        $resume->experiences()->updateOrCreate(
            ['company' => 'Acme Digital', 'position' => 'Backend Developer'],
            [
                'location' => 'Remote',
                'start_date' => '2023',
                'end_date' => 'Present',
                'description' => 'Built and maintained Laravel application services, database-backed workflows, and internal APIs.',
                'bullets' => [
                    'Developed REST API endpoints for authenticated product workflows.',
                    'Improved resume and job matching features using verified user data.',
                    'Collaborated with frontend teams on Blade, Livewire, and Tailwind interfaces.',
                ],
                'sort_order' => 0,
                'is_visible' => true,
            ]
        );

        $resume->educations()->updateOrCreate(
            ['school' => 'Demo University'],
            [
                'degree' => 'Bachelor of Science',
                'field' => 'Computer Science',
                'start_date' => '2018',
                'end_date' => '2022',
                'sort_order' => 0,
                'is_visible' => true,
            ]
        );

        $resume->projects()->updateOrCreate(
            ['name' => 'AI Resume Workspace'],
            [
                'role' => 'Backend Developer',
                'description' => 'Created a resume and job application assistant workflow using Laravel services and structured AI output.',
                'technologies' => ['Laravel', 'Livewire', 'MySQL', 'Tailwind CSS'],
                'bullets' => ['Implemented job analysis, match scoring, cover letter generation, and PDF export workflows.'],
                'sort_order' => 0,
                'is_visible' => true,
            ]
        );
    }
}

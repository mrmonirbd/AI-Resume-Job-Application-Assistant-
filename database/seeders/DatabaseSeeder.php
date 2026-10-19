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
        $this->call(ConceptSeeder::class);
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
            ['Classic Clarity', 'classic-clarity', 'Professional', 'single-column', 'Inter', '#1f4e79', ['style' => 'classic', 'header' => 'centered', 'density' => 'balanced', 'surface' => '#ffffff', 'soft' => '#e7f0f7', 'sort' => 10]],
            ['Executive Ledger', 'executive-ledger', 'Executive', 'left-sidebar-dark', 'Georgia', '#334155', ['style' => 'sidebar', 'header' => 'left-rail', 'density' => 'compact', 'sidebar' => 'left', 'surface' => '#ffffff', 'soft' => '#e2e8f0', 'dark' => '#1f2937', 'sort' => 20]],
            ['Navy Profile', 'navy-profile', 'Executive', 'left-sidebar-navy', 'Inter', '#0f2742', ['style' => 'sidebar', 'header' => 'avatar-rail', 'density' => 'compact', 'sidebar' => 'left', 'surface' => '#ffffff', 'soft' => '#dbeafe', 'dark' => '#0f2742', 'photo' => true, 'sort' => 30]],
            ['Modern Signal', 'modern-signal', 'Modern', 'split-header', 'Inter', '#0f766e', ['style' => 'split', 'header' => 'banded', 'density' => 'balanced', 'surface' => '#ffffff', 'soft' => '#ccfbf1', 'sort' => 40]],
            ['Teal Column', 'teal-column', 'Modern', 'left-sidebar-soft', 'Inter', '#6b9f9d', ['style' => 'soft-sidebar', 'header' => 'profile-column', 'density' => 'balanced', 'sidebar' => 'left', 'surface' => '#fbf7f1', 'soft' => '#d7ece8', 'photo' => true, 'sort' => 50]],
            ['Copper Header', 'copper-header', 'Creative', 'top-band-photo', 'Georgia', '#9a6b55', ['style' => 'top-band', 'header' => 'photo-band', 'density' => 'airy', 'surface' => '#fffaf6', 'soft' => '#ead2c2', 'photo' => true, 'sort' => 60]],
            ['Black Timeline', 'black-timeline', 'Modern', 'vertical-timeline', 'Inter', '#050505', ['style' => 'timeline', 'header' => 'bold-block', 'density' => 'compact', 'surface' => '#ffffff', 'soft' => '#f3f4f6', 'dark' => '#050505', 'photo' => true, 'sort' => 70]],
            ['Blue Edge', 'blue-edge', 'Modern', 'framed-corners', 'Inter', '#0f4c81', ['style' => 'edge', 'header' => 'corner-frame', 'density' => 'balanced', 'surface' => '#ffffff', 'soft' => '#e7f2fa', 'sort' => 80]],
            ['Charcoal Sales', 'charcoal-sales', 'Professional', 'right-rail', 'Aptos', '#2f3b4c', ['style' => 'right-rail', 'header' => 'compact', 'density' => 'balanced', 'sidebar' => 'right', 'surface' => '#ffffff', 'soft' => '#edf0f3', 'dark' => '#2f3b4c', 'photo' => true, 'sort' => 90]],
            ['Minimal Line', 'minimal-line', 'Minimal', 'single-column-rule', 'Arial', '#111827', ['style' => 'minimal', 'header' => 'thin-rule', 'density' => 'airy', 'surface' => '#ffffff', 'soft' => '#f4f4f5', 'sort' => 100]],
            ['Clean Accountant', 'clean-accountant', 'Professional', 'compact-columns', 'Arial', '#5b7083', ['style' => 'compact', 'header' => 'small-center', 'density' => 'compact', 'surface' => '#ffffff', 'soft' => '#e5edf2', 'sort' => 110]],
            ['Marketing Sidebar', 'marketing-sidebar', 'Modern', 'left-rail-accent', 'Inter', '#153d73', ['style' => 'sidebar', 'header' => 'stacked-profile', 'density' => 'balanced', 'sidebar' => 'left', 'surface' => '#ffffff', 'soft' => '#eaf2ff', 'dark' => '#153d73', 'photo' => true, 'sort' => 120]],
            ['Slate Manager', 'slate-manager', 'Executive', 'left-sidebar-slate', 'Inter', '#334155', ['style' => 'sidebar', 'header' => 'portrait-card', 'density' => 'balanced', 'sidebar' => 'left', 'surface' => '#ffffff', 'soft' => '#e2e8f0', 'dark' => '#334155', 'photo' => true, 'sort' => 130]],
            ['Rose Designer', 'rose-designer', 'Creative', 'photo-band', 'Inter', '#b48b83', ['style' => 'photo-band', 'header' => 'portrait-band', 'density' => 'airy', 'surface' => '#fffaf8', 'soft' => '#ead6d2', 'photo' => true, 'sort' => 140]],
            ['Editorial Mono', 'editorial-mono', 'Minimal', 'serif-header', 'Times New Roman', '#3f3f46', ['style' => 'editorial', 'header' => 'formal-rule', 'density' => 'balanced', 'surface' => '#ffffff', 'soft' => '#f4f4f5', 'sort' => 150]],
            ['Beige Portfolio', 'beige-portfolio', 'Creative', 'wide-feature', 'Georgia', '#6b5745', ['style' => 'feature', 'header' => 'wide-profile', 'density' => 'airy', 'surface' => '#fbf8ef', 'soft' => '#d3c7b3', 'photo' => true, 'sort' => 160]],
            ['Graduate Focus', 'graduate-focus', 'Graduate', 'education-first', 'Inter', '#4f46e5', ['style' => 'academic', 'header' => 'compact', 'density' => 'balanced', 'surface' => '#ffffff', 'soft' => '#e0e7ff', 'sort' => 170]],
            ['Analytics Compact', 'analytics-compact', 'Professional', 'dense-single-column', 'Arial', '#365314', ['style' => 'dense', 'header' => 'compact', 'density' => 'dense', 'surface' => '#ffffff', 'soft' => '#ecfccb', 'sort' => 180]],
            ['Product Lead', 'product-lead', 'Modern', 'two-column-balanced', 'Inter', '#2d6a4f', ['style' => 'balanced', 'header' => 'split-name', 'density' => 'balanced', 'surface' => '#ffffff', 'soft' => '#dcfce7', 'sort' => 190]],
            ['Finance Brief', 'consultant-brief', 'Executive', 'impact-first', 'Aptos', '#7c2d12', ['style' => 'brief', 'header' => 'brief', 'density' => 'balanced', 'surface' => '#fff7ed', 'soft' => '#fed7aa', 'sort' => 200]],
            ['Academic Plain', 'academic-plain', 'Academic', 'publication-forward', 'Times New Roman', '#374151', ['style' => 'academic', 'header' => 'formal', 'density' => 'comfortable', 'surface' => '#ffffff', 'soft' => '#f3f4f6', 'sort' => 210]],
            ['Studio Black', 'studio-profile', 'Creative', 'dark-header', 'Inter', '#111827', ['style' => 'dark-header', 'header' => 'portfolio', 'density' => 'airy', 'surface' => '#ffffff', 'soft' => '#e5e7eb', 'dark' => '#111827', 'photo' => true, 'sort' => 220]],
            ['Horizon Blue', 'horizon-blue', 'Modern', 'blue-stripe', 'Inter', '#1d4ed8', ['style' => 'stripe', 'header' => 'diagonal-edge', 'density' => 'balanced', 'surface' => '#ffffff', 'soft' => '#dbeafe', 'sort' => 230]],
            ['Technical Grid', 'technical-grid', 'Technical', 'skills-sidebar', 'IBM Plex Sans', '#0369a1', ['style' => 'technical', 'header' => 'grid', 'density' => 'compact', 'sidebar' => 'left', 'surface' => '#ffffff', 'soft' => '#e0f2fe', 'dark' => '#075985', 'sort' => 240]],
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
                    'is_premium' => false,
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

# AI Resume & Job Application Assistant

A Laravel SaaS MVP for creating a verified master resume, analyzing job descriptions, matching resumes to jobs, generating tailored resume versions and cover letters, checking ATS readiness, and tracking applications.

## Stack

- Laravel 13, PHP 8.3+
- Blade, Livewire, Alpine.js, Tailwind CSS, Vite
- DeepSeek API through a provider abstraction
- DomPDF for Laravel-compatible PDF exports
- SQLite for local development by default; MySQL-ready via `.env`

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve
```

The local app is currently running at:

```text
http://127.0.0.1:8010
```

## DeepSeek

Set these in `.env` to use DeepSeek. Without an API key, the app uses a deterministic local analyzer so the MVP remains demoable.

```env
DEEPSEEK_API_KEY=
DEEPSEEK_BASE_URL=https://api.deepseek.com
DEEPSEEK_MODEL=deepseek-chat
DEEPSEEK_TIMEOUT=30
DEEPSEEK_RETRIES=2
DEEPSEEK_RETRY_SLEEP_MS=350
```

AI calls enter through `app/Services/DeepSeekService.php` and `app/Services/AI/AIManager.php`. Controllers call action classes instead of calling DeepSeek directly.

## Local Email Verification

The default mailer logs emails. After registering locally, open `storage/logs/laravel.log` and use the verification URL from the logged email.

## Main MVP Flow

1. Register and verify email.
2. Complete onboarding to create a master resume.
3. Add a job description.
4. Review extracted requirements and keywords.
5. Compare the job against the master resume.
6. Create a tailored resume version.
7. Generate a cover letter and export PDFs.
8. Track the application in the Kanban board.

## Quality Checks

```bash
php artisan test
npm run build
./vendor/bin/pint
```

Current test coverage includes onboarding plus the job analysis, resume match, and tailored resume workflow.

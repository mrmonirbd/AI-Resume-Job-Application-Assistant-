<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; line-height: 1.7; }
            h1 { font-size: 20px; margin-bottom: 18px; }
            .muted { color: #6b7280; }
            .content { white-space: pre-line; }
        </style>
    </head>
    <body>
        <h1>{{ $coverLetter->title }}</h1>
        <p class="muted">{{ $coverLetter->user->name }} · {{ $coverLetter->user->email }}</p>
        @if ($coverLetter->job)
            <p class="muted">{{ $coverLetter->job->company }} · {{ $coverLetter->job->title }}</p>
        @endif
        <div class="content">{{ $coverLetter->content }}</div>
    </body>
</html>

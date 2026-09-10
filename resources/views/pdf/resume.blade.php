<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; line-height: 1.45; }
            h1 { font-size: 26px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
            h2 { border-bottom: 1px solid {{ $resume->accent_color }}; color: #111827; font-size: 12px; margin: 20px 0 8px; padding-bottom: 4px; text-transform: uppercase; }
            p { margin: 0 0 6px; }
            ul { margin: 4px 0 0 18px; padding: 0; }
            .muted { color: #6b7280; }
            .accent { color: {{ $resume->accent_color }}; }
            .item { margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <h1>{{ $resume->user->name }}</h1>
        <p class="accent"><strong>{{ $resume->professional_title ?: $resume->user->professional_title }}</strong></p>
        <p class="muted">{{ $resume->user->email }}{{ $resume->user->location ? ' · '.$resume->user->location : '' }}</p>

        @if ($resume->professional_summary)
            <h2>Summary</h2>
            <p>{{ $resume->professional_summary }}</p>
        @endif

        @if ($resume->skills->isNotEmpty())
            <h2>Skills</h2>
            <p>{{ $resume->skills->pluck('name')->join(' · ') }}</p>
        @endif

        @if ($resume->experiences->isNotEmpty())
            <h2>Experience</h2>
            @foreach ($resume->experiences as $experience)
                <div class="item">
                    <p><strong>{{ $experience->position }}</strong>, {{ $experience->company }}</p>
                    <p class="muted">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}{{ $experience->location ? ' · '.$experience->location : '' }}</p>
                    <p>{{ $experience->description }}</p>
                    @if ($experience->bullets)
                        <ul>
                            @foreach ($experience->bullets as $bullet)
                                <li>{{ $bullet }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        @endif

        @if ($resume->projects->isNotEmpty())
            <h2>Projects</h2>
            @foreach ($resume->projects as $project)
                <div class="item">
                    <p><strong>{{ $project->name }}</strong>{{ $project->role ? ' · '.$project->role : '' }}</p>
                    <p>{{ $project->description }}</p>
                </div>
            @endforeach
        @endif

        @if ($resume->educations->isNotEmpty())
            <h2>Education</h2>
            @foreach ($resume->educations as $education)
                <p><strong>{{ $education->school }}</strong>{{ $education->degree ? ' · '.$education->degree : '' }}{{ $education->field ? ' · '.$education->field : '' }}</p>
            @endforeach
        @endif

        @if ($resume->certifications->isNotEmpty())
            <h2>Certifications</h2>
            @foreach ($resume->certifications as $certification)
                <p><strong>{{ $certification->name }}</strong>{{ $certification->issuer ? ' · '.$certification->issuer : '' }}</p>
            @endforeach
        @endif
    </body>
</html>

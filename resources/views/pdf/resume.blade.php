@php
    $settings = $resume->template?->settings ?? [];
    $style = data_get($settings, 'style', 'classic');
    $accent = $resume->accent_color ?: '#2563eb';
    $soft = data_get($settings, 'soft', '#eef2ff');
    $dark = data_get($settings, 'dark', '#111827');
    $surface = data_get($settings, 'surface', '#ffffff');
    $title = $resume->professional_title ?: $resume->user->professional_title;
    $contact = collect([$resume->user->email, $resume->user->location])->filter()->map(fn (string $item): string => e($item))->join(' &middot; ');
    $hasSidebar = in_array($style, ['sidebar', 'soft-sidebar', 'right-rail', 'technical'], true);
    $usesDarkSidebar = in_array($style, ['sidebar', 'right-rail', 'technical'], true);
    $sidebarBackground = $usesDarkSidebar ? $dark : $soft;
    $sidebarText = $usesDarkSidebar ? '#ffffff' : '#111827';
    $profileImagePath = public_path('images/demo-profile.png');
    $profileImageSource = file_exists($profileImagePath) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($profileImagePath)) : null;
@endphp
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            @page { size: A4; margin: 0; }
            * { box-sizing: border-box; }
            html, body { margin: 0; padding: 0; }
            body { background: #ffffff; color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.45; }
            h1 { font-size: 25px; line-height: 1.05; margin: 0; text-transform: uppercase; }
            h2 { color: #111827; font-size: 10px; letter-spacing: 1.8px; margin: 18px 0 8px; text-transform: uppercase; }
            h3 { font-size: 12px; margin: 0; }
            p { margin: 0 0 6px; }
            ul { margin: 4px 0 0 16px; padding: 0; }
            li { margin-bottom: 3px; }
            .muted { color: #6b7280; }
            .accent { color: {{ $accent }}; }
            .rule { border-bottom: 1px solid {{ $accent }}; padding-bottom: 5px; }
            .item { margin-bottom: 10px; }
            .skills { line-height: 1.7; }
            .page { background: {{ $surface }}; width: 100%; }
            .sheet { padding: 40pt; }
            .band { background: {{ in_array($style, ['dark-header'], true) ? $dark : $soft }}; padding: 34pt 40pt; }
            .band h1 { color: {{ in_array($style, ['dark-header'], true) ? '#ffffff' : '#111827' }}; }
            .band .muted { color: {{ in_array($style, ['dark-header'], true) ? '#d1d5db' : '#4b5563' }}; }
            .side-fill { background: {{ $sidebarBackground }}; bottom: 0; position: fixed; top: 0; width: 202pt; z-index: 0; }
            .side-fill-left { left: 0; }
            .side-fill-right { right: 0; }
            .content-table { border-collapse: collapse; position: relative; table-layout: fixed; width: 100%; z-index: 1; }
            .content-table td { vertical-align: top; }
            .side { background: transparent; color: {{ $sidebarText }}; padding: 40pt 26pt; width: 202pt; }
            .side h1 { color: {{ $sidebarText }}; font-size: 21px; }
            .side h2 { color: {{ $sidebarText }}; opacity: 0.78; }
            .side .muted { color: {{ $sidebarText }}; opacity: 0.75; }
            .main { padding: 40pt 36pt; width: 393.28pt; }
            .pill { border: 1px solid {{ $usesDarkSidebar ? 'rgba(255,255,255,0.35)' : $accent }}; border-radius: 10px; display: inline-block; margin: 0 4px 5px 0; padding: 3px 7px; }
            .two-col { border-collapse: collapse; width: 100%; }
            .two-col .left { padding-right: 22px; width: 66%; }
            .two-col .right { border-left: 1px solid #e5e7eb; padding-left: 22px; width: 34%; }
            .timeline-item { border-left: 2px solid {{ $accent }}; margin-bottom: 12px; padding-left: 12px; }
            .avatar { border: 4px solid rgba(255,255,255,0.85); border-radius: 999px; display: block; height: 62pt; margin-bottom: 24pt; width: 62pt; }
        </style>
    </head>
    <body>
        <div class="page">
        @if ($hasSidebar)
            <div class="side-fill {{ data_get($settings, 'sidebar') === 'right' ? 'side-fill-right' : 'side-fill-left' }}"></div>
            <table class="content-table">
                <tr>
                    @if (data_get($settings, 'sidebar') !== 'right')
                        <td class="side">
                            @if ($profileImageSource)
                                <img class="avatar" src="{{ $profileImageSource }}" alt="">
                            @endif
                            <h1>{{ $resume->user->name }}</h1>
                            @if ($title)
                                <p style="margin-top: 8px;"><strong>{{ $title }}</strong></p>
                            @endif
                            <h2>Contact</h2>
                            <p class="muted">{!! $contact !!}</p>
                            @if ($resume->skills->isNotEmpty())
                                <h2>Skills</h2>
                                <p>
                                    @foreach ($resume->skills->take(14) as $skill)
                                        <span class="pill">{{ $skill->name }}</span>
                                    @endforeach
                                </p>
                            @endif
                            @if ($resume->educations->isNotEmpty())
                                <h2>Education</h2>
                                @foreach ($resume->educations->take(2) as $education)
                                    <p><strong>{{ $education->school }}</strong><br><span class="muted">{{ $education->degree }} {{ $education->field }}</span></p>
                                @endforeach
                            @endif
                            @if ($resume->languages->isNotEmpty())
                                <h2>Languages</h2>
                                @foreach ($resume->languages as $language)
                                    <p>{{ $language->name }}{{ $language->proficiency ? ' - '.$language->proficiency : '' }}</p>
                                @endforeach
                            @endif
                        </td>
                    @endif

                    <td class="main">
                        @if (data_get($settings, 'sidebar') === 'right')
                            <h1>{{ $resume->user->name }}</h1>
                            @if ($title)
                                <p class="accent"><strong>{{ $title }}</strong></p>
                            @endif
                            <p class="muted">{!! $contact !!}</p>
                        @endif
                        @if ($resume->professional_summary)
                            <h2 class="rule">Profile</h2>
                            <p>{{ $resume->professional_summary }}</p>
                        @endif
                        @if ($resume->experiences->isNotEmpty())
                            <h2 class="rule">Work Experience</h2>
                            @foreach ($resume->experiences as $experience)
                                <div class="item">
                                    <h3>{{ $experience->position }}, {{ $experience->company }}</h3>
                                    <p class="muted">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}{{ $experience->location ? ' &middot; '.$experience->location : '' }}</p>
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
                            <h2 class="rule">Projects</h2>
                            @foreach ($resume->projects as $project)
                                <div class="item">
                                    <h3>{{ $project->name }}{{ $project->role ? ' - '.$project->role : '' }}</h3>
                                    <p>{{ $project->description }}</p>
                                </div>
                            @endforeach
                        @endif
                        @if ($resume->certifications->isNotEmpty())
                            <h2 class="rule">Certifications</h2>
                            @foreach ($resume->certifications as $certification)
                                <p><strong>{{ $certification->name }}</strong>{{ $certification->issuer ? ' - '.$certification->issuer : '' }}</p>
                            @endforeach
                        @endif
                    </td>

                    @if (data_get($settings, 'sidebar') === 'right')
                        <td class="side">
                            @if ($profileImageSource)
                                <img class="avatar" src="{{ $profileImageSource }}" alt="">
                            @endif
                            <h2>Contact</h2>
                            <p class="muted">{!! $contact !!}</p>
                            @if ($resume->skills->isNotEmpty())
                                <h2>Skills</h2>
                                <p>
                                    @foreach ($resume->skills->take(14) as $skill)
                                        <span class="pill">{{ $skill->name }}</span>
                                    @endforeach
                                </p>
                            @endif
                            @if ($resume->educations->isNotEmpty())
                                <h2>Education</h2>
                                @foreach ($resume->educations->take(2) as $education)
                                    <p><strong>{{ $education->school }}</strong><br><span class="muted">{{ $education->degree }} {{ $education->field }}</span></p>
                                @endforeach
                            @endif
                        </td>
                    @endif
                </tr>
            </table>
        @elseif (in_array($style, ['top-band', 'photo-band', 'feature', 'dark-header', 'stripe', 'edge'], true))
            <header class="band">
                <h1>{{ $resume->user->name }}</h1>
                @if ($title)
                    <p class="accent"><strong>{{ $title }}</strong></p>
                @endif
                <p class="muted">{!! $contact !!}</p>
            </header>
            <main class="sheet">
                <table class="two-col">
                    <tr>
                        <td class="left">
                            @if ($resume->professional_summary)
                                <h2 class="rule">About Me</h2>
                                <p>{{ $resume->professional_summary }}</p>
                            @endif
                            @if ($resume->experiences->isNotEmpty())
                                <h2 class="rule">Experience</h2>
                                @foreach ($resume->experiences as $experience)
                                    <div class="item">
                                        <h3>{{ $experience->position }}</h3>
                                        <p class="muted">{{ $experience->company }} - {{ $experience->start_date }} to {{ $experience->end_date ?: 'Present' }}</p>
                                        <p>{{ $experience->description }}</p>
                                    </div>
                                @endforeach
                            @endif
                            @if ($resume->projects->isNotEmpty())
                                <h2 class="rule">Projects</h2>
                                @foreach ($resume->projects as $project)
                                    <p><strong>{{ $project->name }}</strong><br>{{ $project->description }}</p>
                                @endforeach
                            @endif
                        </td>
                        <td class="right">
                            @if ($resume->skills->isNotEmpty())
                                <h2>Skills</h2>
                                <p class="skills">{{ $resume->skills->pluck('name')->join(' &middot; ') }}</p>
                            @endif
                            @if ($resume->educations->isNotEmpty())
                                <h2>Education</h2>
                                @foreach ($resume->educations as $education)
                                    <p><strong>{{ $education->school }}</strong><br>{{ $education->degree }} {{ $education->field }}</p>
                                @endforeach
                            @endif
                            @if ($resume->certifications->isNotEmpty())
                                <h2>Certifications</h2>
                                @foreach ($resume->certifications as $certification)
                                    <p><strong>{{ $certification->name }}</strong>{{ $certification->issuer ? ' - '.$certification->issuer : '' }}</p>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                </table>
            </main>
        @elseif ($style === 'timeline')
            <main class="sheet">
                <h1>{{ $resume->user->name }}</h1>
                @if ($title)
                    <p class="accent"><strong>{{ $title }}</strong></p>
                @endif
                <p class="muted">{!! $contact !!}</p>
                @if ($resume->professional_summary)
                    <h2 class="rule">About Me</h2>
                    <p>{{ $resume->professional_summary }}</p>
                @endif
                @if ($resume->experiences->isNotEmpty())
                    <h2>Work Experience</h2>
                    @foreach ($resume->experiences as $experience)
                        <div class="timeline-item">
                            <h3>{{ $experience->position }}, {{ $experience->company }}</h3>
                            <p class="muted">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}</p>
                            <p>{{ $experience->description }}</p>
                        </div>
                    @endforeach
                @endif
                @if ($resume->skills->isNotEmpty())
                    <h2 class="rule">Skills</h2>
                    <p class="skills">{{ $resume->skills->pluck('name')->join(' &middot; ') }}</p>
                @endif
                @if ($resume->educations->isNotEmpty())
                    <h2 class="rule">Education</h2>
                    @foreach ($resume->educations as $education)
                        <p><strong>{{ $education->school }}</strong>{{ $education->degree ? ' - '.$education->degree : '' }}</p>
                    @endforeach
                @endif
            </main>
        @else
            <main class="sheet">
                <h1>{{ $resume->user->name }}</h1>
                @if ($title)
                    <p class="accent"><strong>{{ $title }}</strong></p>
                @endif
                <p class="muted">{!! $contact !!}</p>

                @if ($resume->professional_summary)
                    <h2 class="rule">Summary</h2>
                    <p>{{ $resume->professional_summary }}</p>
                @endif

                @if ($resume->skills->isNotEmpty())
                    <h2 class="rule">Skills</h2>
                    <p class="skills">{{ $resume->skills->pluck('name')->join(' &middot; ') }}</p>
                @endif

                @if ($resume->experiences->isNotEmpty())
                    <h2 class="rule">Experience</h2>
                    @foreach ($resume->experiences as $experience)
                        <div class="item">
                            <h3>{{ $experience->position }}, {{ $experience->company }}</h3>
                            <p class="muted">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}{{ $experience->location ? ' &middot; '.$experience->location : '' }}</p>
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
                    <h2 class="rule">Projects</h2>
                    @foreach ($resume->projects as $project)
                        <div class="item">
                            <h3>{{ $project->name }}{{ $project->role ? ' - '.$project->role : '' }}</h3>
                            <p>{{ $project->description }}</p>
                        </div>
                    @endforeach
                @endif

                @if ($resume->educations->isNotEmpty())
                    <h2 class="rule">Education</h2>
                    @foreach ($resume->educations as $education)
                        <p><strong>{{ $education->school }}</strong>{{ $education->degree ? ' - '.$education->degree : '' }}{{ $education->field ? ' - '.$education->field : '' }}</p>
                    @endforeach
                @endif

                @if ($resume->certifications->isNotEmpty())
                    <h2 class="rule">Certifications</h2>
                    @foreach ($resume->certifications as $certification)
                        <p><strong>{{ $certification->name }}</strong>{{ $certification->issuer ? ' - '.$certification->issuer : '' }}</p>
                    @endforeach
                @endif
            </main>
        @endif
        </div>
    </body>
</html>

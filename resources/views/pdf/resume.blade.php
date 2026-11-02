@php
    $slug = $resume->template?->slug ?? 'default';
    $settings = $resume->template?->settings ?? [];
    $style = data_get($settings, 'style', 'classic');
    $accent = $resume->accent_color ?: '#2563eb';
    $soft = data_get($settings, 'soft', '#eef2ff');
    $dark = data_get($settings, 'dark', '#111827');
    $surface = data_get($settings, 'surface', '#ffffff');
    $isColumnLayout = in_array($style, ['split', 'balanced', 'compact', 'dense'], true) || $slug === 'operator-compact';
    $fontFamily = in_array($resume->font_family, ['Georgia', 'Times New Roman'], true) ? 'DejaVu Serif' : 'DejaVu Sans';
    $sectionGap = ['compact' => 12, 'spacious' => 24][$resume->spacing] ?? 18;
    $bodySize = $resume->spacing === 'compact' ? 10 : 11;
    $sectionSettings = $resume->sections->keyBy('type');
    $summary = $resume->professional_summary;
    if (data_get($sectionSettings->get('summary'), 'is_visible', true) === false) {
        $summary = null;
    }
    $collections = [];
    foreach (['experience' => 'experiences', 'education' => 'educations', 'skills' => 'skills', 'projects' => 'projects', 'certifications' => 'certifications', 'languages' => 'languages'] as $type => $relation) {
        $collections[$relation] = data_get($sectionSettings->get($type), 'is_visible', true) ? $resume->$relation : collect();
    }
    $title = $resume->professional_title ?? $resume->user->professional_title;
    $contact = collect([$resume->user->email, $resume->user->location])->filter()->join(' | ');
    $hasSidebar = in_array($style, ['sidebar', 'soft-sidebar', 'right-rail', 'technical'], true);
    $usesDarkSidebar = in_array($style, ['sidebar', 'right-rail', 'technical'], true);
    $sidebarBackground = $usesDarkSidebar ? $dark : $soft;
    $sidebarText = $usesDarkSidebar ? '#ffffff' : '#111827';
    $profileImagePath = $resume->user->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($resume->user->profile_photo_path)
        ? \Illuminate\Support\Facades\Storage::disk('public')->path($resume->user->profile_photo_path)
        : public_path('images/demo-profile.png');
    $profileImageMime = file_exists($profileImagePath) ? mime_content_type($profileImagePath) : null;
    $profileImageSource = $profileImageMime ? 'data:'.$profileImageMime.';base64,'.base64_encode((string) file_get_contents($profileImagePath)) : null;
@endphp
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <style>
            @page { size: A4; margin: 0; }
            * { box-sizing: border-box; }
            html, body { margin: 0; padding: 0; }
            body { background: #ffffff; color: #111827; font-family: "{{ $fontFamily }}", sans-serif; font-size: {{ $bodySize }}px; line-height: 1.45; }
            h1 { font-size: 25px; line-height: 1.05; margin: 0; text-transform: uppercase; }
            h2 { color: #111827; font-size: 10px; letter-spacing: 1.8px; margin: {{ $sectionGap }}px 0 8px; text-transform: uppercase; }
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
            .sidebar-panel { background: {{ $sidebarBackground }}; bottom: 0; color: {{ $sidebarText }}; position: fixed; top: 0; width: 34%; z-index: 1; }
            .sidebar-panel-left { left: 0; }
            .sidebar-panel-right { right: 0; }
            .side-content { padding: 40pt 24pt; }
            .sidebar-panel h1 { color: {{ $sidebarText }}; font-size: 21px; }
            .sidebar-panel h2 { color: {{ $sidebarText }}; opacity: 0.78; }
            .sidebar-panel .muted { color: {{ $sidebarText }}; opacity: 0.75; }
            .sidebar-main { padding: 40pt 26pt; }
            .sidebar-main-left { margin-left: 34%; }
            .sidebar-main-right { margin-right: 34%; }
            .pill { border: 1px solid {{ $usesDarkSidebar ? 'rgba(255,255,255,0.35)' : $accent }}; border-radius: 10px; display: inline-block; margin: 0 4px 5px 0; padding: 3px 7px; }
            .two-col { border-collapse: collapse; width: 100%; }
            .two-col .left { padding-right: 22px; width: 66%; }
            .two-col .right { border-left: 1px solid #e5e7eb; padding-left: 22px; width: 34%; }
            .timeline-item { border-left: 2px solid {{ $accent }}; margin-bottom: 12px; padding-left: 12px; }
            td { vertical-align: top; }
            h2, h3 { page-break-after: avoid; }
            .item { page-break-inside: avoid; }
            .split-header { border-collapse: collapse; margin-bottom: 24px; width: 100%; }
            .split-header .identity { padding-right: 20pt; width: 72%; }
            .split-header .color-block { background: {{ $accent }}; height: 96pt; width: 28%; }
            .style-balanced .header { border-bottom: 2px solid {{ $accent }}; padding-bottom: 16px; }
            .style-brief .header { border-left: 4px solid {{ $accent }}; padding-left: 16px; }
            .style-brief .rule { border: 0; border-left: 2px solid {{ $accent }}; padding-left: 10px; }
            .style-minimal .rule { border-bottom: 0; border-top: 1px solid #e5e7eb; padding-top: 12px; }
            .style-editorial .header { border-bottom: 1px solid #d4d4d8; letter-spacing: 1px; text-align: center; padding-bottom: 16px; }
            .style-academic .header { border-bottom: 2px solid {{ $accent }}; padding-bottom: 16px; }
            .style-compact, .style-dense { font-size: 10px; }
            .style-compact h1, .style-dense h1 { font-size: 22px; }
            .style-edge .page { border-left: 8pt solid {{ $accent }}; }
            .style-top-band .band, .style-photo-band .band { border-bottom: 4px solid {{ $accent }}; }
            .style-stripe .band { border-right: 30pt solid {{ $accent }}; }
            .header-photo { padding-right: 20px; width: 75pt; }
            .header-photo .avatar { margin: 0; }
            .section-notes { margin: 20px 40pt; white-space: pre-line; }
            .avatar { border: 4px solid rgba(255,255,255,0.85); border-radius: 999px; display: block; height: 62pt; margin-bottom: 24pt; width: 62pt; }
        </style>
    </head>
    <body class="style-{{ $style }} variant-{{ $slug }}" data-template="{{ $slug }}">
        <div class="page">
        @if ($hasSidebar)
            <aside class="sidebar-panel {{ data_get($settings, 'sidebar') === 'right' ? 'sidebar-panel-right' : 'sidebar-panel-left' }}">
                <div class="side-content">
                    @if (data_get($settings, 'sidebar') !== 'right')
                        @if ($profileImageSource)
                            <img class="avatar" src="{{ $profileImageSource }}" alt="">
                        @endif
                        <h1>{{ $resume->user->name }}</h1>
                        @if ($title)
                            <p style="margin-top: 8px;"><strong>{{ $title }}</strong></p>
                        @endif
                    @endif
                    <h2>Contact</h2>
                    <p class="muted">{{ $contact }}</p>
                    @if ($collections['skills']->isNotEmpty())
                        <h2>{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h2>
                        <p>
                            @foreach ($collections['skills']->take(14) as $skill)
                                <span class="pill">{{ $skill->name }}</span>
                            @endforeach
                        </p>
                    @endif
                    @if ($collections['educations']->isNotEmpty())
                        <h2>{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h2>
                        @foreach ($collections['educations']->take(2) as $education)
                            <p><strong>{{ $education->school }}</strong><br><span class="muted">{{ $education->degree }} {{ $education->field }}</span></p>
                        @endforeach
                    @endif
                    @if ($collections['languages']->isNotEmpty())
                        <h2>{{ data_get($sectionSettings->get('languages'), 'title', 'Languages') }}</h2>
                        @foreach ($collections['languages'] as $language)
                            <p>{{ $language->name }}{{ $language->proficiency ? ' - '.$language->proficiency : '' }}</p>
                        @endforeach
                    @endif
                </div>
            </aside>
            <main class="sidebar-main {{ data_get($settings, 'sidebar') === 'right' ? 'sidebar-main-right' : 'sidebar-main-left' }}">
                @if (data_get($settings, 'sidebar') === 'right')
                    <h1>{{ $resume->user->name }}</h1>
                    @if ($title)
                        <p class="accent"><strong>{{ $title }}</strong></p>
                    @endif
                    <p class="muted">{{ $contact }}</p>
                @endif
                @if ($summary)
                    <h2 class="rule">{{ data_get($sectionSettings->get('summary'), 'title', 'Profile') }}</h2>
                    <p>{{ $summary }}</p>
                @endif
                @if ($collections['experiences']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('experience'), 'title', 'Work Experience') }}</h2>
                    @foreach ($collections['experiences'] as $experience)
                        <div class="item">
                            <h3>{{ $experience->position }}, {{ $experience->company }}</h3>
                            <p class="muted">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}{{ $experience->location ? ' | '.$experience->location : '' }}</p>
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
                @if ($collections['projects']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('projects'), 'title', 'Projects') }}</h2>
                    @foreach ($collections['projects'] as $project)
                        <div class="item">
                            <h3>{{ $project->name }}{{ $project->role ? ' - '.$project->role : '' }}</h3>
                            <p>{{ $project->description }}</p>
                        </div>
                    @endforeach
                @endif
                @if ($collections['certifications']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('certifications'), 'title', 'Certifications') }}</h2>
                    @foreach ($collections['certifications'] as $certification)
                        <p><strong>{{ $certification->name }}</strong>{{ $certification->issuer ? ' - '.$certification->issuer : '' }}</p>
                    @endforeach
                @endif
            </main>
        @elseif (in_array($style, ['top-band', 'photo-band', 'feature', 'dark-header', 'stripe', 'edge'], true))
            <header class="band">
                <table style="width: 100%; border-collapse: collapse;"><tr>
                    @if (data_get($settings, 'photo', false) && $profileImageSource)
                        <td class="header-photo"><img class="avatar" src="{{ $profileImageSource }}" alt=""></td>
                    @endif
                    <td>
                        <h1>{{ $resume->user->name }}</h1>
                        @if ($title)
                            <p class="accent"><strong>{{ $title }}</strong></p>
                        @endif
                        <p class="muted">{{ $contact }}</p>
                    </td>
                </tr></table>
            </header>
            <main class="sheet">
                <table class="two-col">
                    <tr>
                        <td class="left">
                            @if ($summary)
                                <h2 class="rule">{{ data_get($sectionSettings->get('summary'), 'title', 'About Me') }}</h2>
                                <p>{{ $summary }}</p>
                            @endif
                            @if ($collections['experiences']->isNotEmpty())
                                <h2 class="rule">{{ data_get($sectionSettings->get('experience'), 'title', 'Experience') }}</h2>
                                @foreach ($collections['experiences'] as $experience)
                                    <div class="item">
                                        <h3>{{ $experience->position }}</h3>
                                        <p class="muted">{{ $experience->company }} - {{ $experience->start_date }} to {{ $experience->end_date ?: 'Present' }}</p>
                                        <p>{{ $experience->description }}</p>
                                    </div>
                                @endforeach
                            @endif
                            @if ($collections['projects']->isNotEmpty())
                                <h2 class="rule">{{ data_get($sectionSettings->get('projects'), 'title', 'Projects') }}</h2>
                                @foreach ($collections['projects'] as $project)
                                    <p><strong>{{ $project->name }}</strong><br>{{ $project->description }}</p>
                                @endforeach
                            @endif
                        </td>
                        <td class="right">
                            @if ($collections['skills']->isNotEmpty())
                                <h2>{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h2>
                                <p class="skills">{{ $collections['skills']->pluck('name')->join(' | ') }}</p>
                            @endif
                            @if ($collections['educations']->isNotEmpty())
                                <h2>{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h2>
                                @foreach ($collections['educations'] as $education)
                                    <p><strong>{{ $education->school }}</strong><br>{{ $education->degree }} {{ $education->field }}</p>
                                @endforeach
                            @endif
                            @if ($collections['certifications']->isNotEmpty())
                                <h2>{{ data_get($sectionSettings->get('certifications'), 'title', 'Certifications') }}</h2>
                                @foreach ($collections['certifications'] as $certification)
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
                <p class="muted">{{ $contact }}</p>
                @if ($summary)
                    <h2 class="rule">{{ data_get($sectionSettings->get('summary'), 'title', 'About Me') }}</h2>
                    <p>{{ $summary }}</p>
                @endif
                @if ($collections['experiences']->isNotEmpty())
                    <h2>{{ data_get($sectionSettings->get('experience'), 'title', 'Work Experience') }}</h2>
                    @foreach ($collections['experiences'] as $experience)
                        <div class="timeline-item">
                            <h3>{{ $experience->position }}, {{ $experience->company }}</h3>
                            <p class="muted">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}</p>
                            <p>{{ $experience->description }}</p>
                        </div>
                    @endforeach
                @endif
                @if ($collections['skills']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h2>
                    <p class="skills">{{ $collections['skills']->pluck('name')->join(' | ') }}</p>
                @endif
                @if ($collections['educations']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h2>
                    @foreach ($collections['educations'] as $education)
                        <p><strong>{{ $education->school }}</strong>{{ $education->degree ? ' - '.$education->degree : '' }}</p>
                    @endforeach
                @endif
            </main>
        @else
            <main class="sheet">
                @if ($style === 'split')
                    <table class="split-header"><tr>
                        <td class="identity">
                            <h1>{{ $resume->user->name }}</h1>
                            @if ($title)<p class="accent"><strong>{{ $title }}</strong></p>@endif
                            <p class="muted">{{ $contact }}</p>
                        </td>
                        <td class="color-block"></td>
                    </tr></table>
                @else
                    <header class="header" style="{{ in_array(data_get($settings, 'header'), ['centered', 'small-center', 'formal'], true) ? 'text-align: center;' : '' }}">
                        <h1>{{ $resume->user->name }}</h1>
                        @if ($title)<p class="accent"><strong>{{ $title }}</strong></p>@endif
                        <p class="muted">{{ $contact }}</p>
                    </header>
                @endif
                @if ($isColumnLayout)
                    <table class="two-col"><tr><td class="left">
                @endif
                @if ($style === 'academic' && $collections['educations']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h2>
                    @foreach ($collections['educations'] as $education)
                        <p><strong>{{ $education->school }}</strong><br>{{ $education->degree }} {{ $education->field }}</p>
                    @endforeach
                @endif
                @if ($summary)
                    <h2 class="rule">{{ data_get($sectionSettings->get('summary'), 'title', 'Summary') }}</h2>
                    <p>{{ $summary }}</p>
                @endif

                @if (! $isColumnLayout && $collections['skills']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h2>
                    <p class="skills">{{ $collections['skills']->pluck('name')->join(' | ') }}</p>
                @endif

                @if ($collections['experiences']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('experience'), 'title', 'Experience') }}</h2>
                    @foreach ($collections['experiences'] as $experience)
                        <div class="item">
                            <h3>{{ $experience->position }}, {{ $experience->company }}</h3>
                            <p class="muted">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}{{ $experience->location ? ' | '.$experience->location : '' }}</p>
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

                @if ($collections['projects']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('projects'), 'title', 'Projects') }}</h2>
                    @foreach ($collections['projects'] as $project)
                        <div class="item">
                            <h3>{{ $project->name }}{{ $project->role ? ' - '.$project->role : '' }}</h3>
                            <p>{{ $project->description }}</p>
                        </div>
                    @endforeach
                @endif

                @if ($isColumnLayout)
                    </td><td class="right">
                    @if ($collections['skills']->isNotEmpty())
                        <h2>{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h2>
                        <p class="skills">{{ $collections['skills']->pluck('name')->join(' | ') }}</p>
                    @endif
                @endif
                @if ($style !== 'academic' && $collections['educations']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h2>
                    @foreach ($collections['educations'] as $education)
                        <p><strong>{{ $education->school }}</strong>{{ $education->degree ? ' - '.$education->degree : '' }}{{ $education->field ? ' - '.$education->field : '' }}</p>
                    @endforeach
                @endif

                @if ($collections['certifications']->isNotEmpty())
                    <h2 class="rule">{{ data_get($sectionSettings->get('certifications'), 'title', 'Certifications') }}</h2>
                    @foreach ($collections['certifications'] as $certification)
                        <p><strong>{{ $certification->name }}</strong>{{ $certification->issuer ? ' - '.$certification->issuer : '' }}</p>
                    @endforeach
                @endif
                @if ($isColumnLayout)</td></tr></table>@endif
            </main>
        @endif
        @foreach ($resume->sections->sortBy('sort_order') as $section)
            @if ($section->is_visible && filled($section->content))
                <section class="section-notes"><h2>{{ $section->title }}</h2><p>{{ $section->content }}</p></section>
            @endif
        @endforeach
        </div>
    </body>
</html>

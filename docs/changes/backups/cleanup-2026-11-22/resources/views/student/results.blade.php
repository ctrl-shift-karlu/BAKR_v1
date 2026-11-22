@extends('student.layout')
@section('title', 'My matches | BAKR')

@section('hero_title')
Scholarship <em>matches</em>
@endsection
@section('hero_sub')
These results come from your matching form (version {{ $application->version }}), checked by BAKR's rules and the Gemini AI matcher. BAKR does not apply for you. Open a match to see why, what to prepare, and the official link to apply.
@endsection

@section('portal')
@php
    $icons = ['pass' => 'bi-check-circle-fill', 'fail' => 'bi-x-circle-fill', 'confirm' => 'bi-question-circle-fill', 'unknown' => 'bi-dash-circle-fill'];
    $labels = \App\Models\ScholarshipMatch::STATUS_LABELS;
    // While the AI matcher is still to run, it writes the explanations itself: no separate explain calls.
    $explainOnOpen = $aiAvailable && ! $application->aiPending();
@endphp
<div class="sp-actionbar">
    <a class="sp-btn sp-btn-line" href="{{ route('student.application.show') }}"><i class="bi bi-pencil-square" aria-hidden="true"></i> Change my answers</a>
</div>

@include('student.partials.ai-match-status')

@if ($counts->sum() === 0)
    @include('partials.no-match', ['catalogueUrl' => route('student.scholarships')])
@else
<div class="sp-note"><i class="bi bi-info-circle" aria-hidden="true"></i>
    <span><strong>Based on your answers.</strong> "Eligible" means you may apply, not that you are approved. Each provider makes the final decision after checking your documents, and some limit slots or rank applicants.</span></div>

<nav class="sp-filters" aria-label="Filter results">
    <a class="sp-filter" href="{{ route('student.results') }}" aria-current="{{ $filter ? 'false' : 'true' }}">All<span>{{ $counts->sum() }}</span></a>
    @foreach ($counts as $status => $n)
        <a class="sp-filter" href="{{ route('student.results', ['status' => $status]) }}" aria-current="{{ $filter === $status ? 'true' : 'false' }}">{{ $labels[$status] }}<span>{{ $n }}</span></a>
    @endforeach
</nav>

@if ($matches->isEmpty())
    <div class="sp-card"><p class="sp-near-none">Nothing in this group.</p></div>
@endif

{{-- One slim row per match. "View details" opens a dialog with the explanation, the
     requirement checks, what to prepare, and how to apply with the provider. A link to #match-{id}
     (dashboard "Why?", AI explain redirect) opens that dialog on load (bakr-dashboard.js). --}}
<ul class="sp-mlist" aria-label="Your matches">
@foreach ($matches as $m)
    @php
        $s = $m->scholarship;
        $aiText = \App\Services\ExplanationService::aiText($m);
        $isAi = (bool) $aiText;
        $dlg = 'match-dialog-'.$m->id;
        $shown = collect($m->checks)->where('result', '!=', 'na');
        $passed = $shown->where('result', 'pass')->count();
    @endphp
    <li class="sp-mrow" id="match-{{ $m->id }}" data-hash-dialog="{{ $dlg }}">
        <span class="sp-mrow-logo"><img src="{{ asset($s->logoPath()) }}" alt="" loading="lazy"></span>
        <div class="sp-mrow-main">
            <span class="sp-sector">{{ $s->sectorLabel() }}</span>
            <h2 class="sp-mrow-title" id="title-{{ $m->id }}">{{ $s->name }}</h2>
            <div class="sp-mrow-meta">
                <span class="sp-pill {{ $m->status }}">{{ $m->statusLabel() }}</span>
                @if ($m->ai_fit)<span class="sp-fit is-{{ $m->ai_fit }}">{{ \App\Models\ScholarshipMatch::FIT_LABELS[$m->ai_fit] ?? '' }}</span>@endif
                <span class="sp-mrow-checks">{{ $passed }} of {{ $shown->count() }} requirements met</span>
                @include('partials.call-status', ['scholarship' => $s, 'compact' => true])
            </div>
        </div>
        <a class="sp-btn sp-btn-green sp-btn-sm sp-mrow-btn" href="#match-{{ $m->id }}" data-open-dialog="{{ $dlg }}" aria-describedby="title-{{ $m->id }}">View details <i class="bi bi-arrow-right" aria-hidden="true"></i></a>

        <dialog class="dash-dialog sp-mdialog" id="{{ $dlg }}" aria-labelledby="dlg-title-{{ $m->id }}">
            <div class="dash-dialog-head sp-mdialog-head">
                <div class="sp-mdialog-title">
                    <span class="dash-kicker">{{ $s->sectorLabel() }} · {{ $s->provider }}</span>
                    <h2 id="dlg-title-{{ $m->id }}">{{ $s->name }}</h2>
                    <span class="sp-pill {{ $m->status }}">{{ $m->statusLabel() }}</span>
                </div>
                <button type="button" class="dash-dialog-close" data-close-dialog aria-label="Close"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </div>

            <div class="dash-dialog-body sp-mdialog-body">
                <p class="sp-lead">{{ $s->overview }}</p>

                {{-- Written by the AI (Gemini) the first time this dialog opens (student-ai.js), then saved.
                     The built-in text shows meanwhile, and stays if the AI is off or unreachable. --}}
                <div class="sp-ai" @if (! $isAi && $explainOnOpen) data-ai-url="{{ route('student.matches.explain', ['match' => $m->id]) }}" data-ai-label="AI explanation" data-fallback-label="Quick explanation" @endif>
                    <span class="sp-ai-tag" data-ai-tag><i class="bi bi-chat-left-text" aria-hidden="true"></i> {{ $isAi ? 'AI explanation' : 'Quick explanation' }}</span>
                    <p data-ai-text>{{ $isAi ? $aiText : $explainer->quick($m) }}</p>
                    @if (! $isAi && $explainOnOpen)
                        <noscript>
                            <form method="POST" action="{{ route('student.matches.explain', ['match' => $m->id]) }}">
                                @csrf
                                <button class="sp-btn sp-btn-green sp-btn-sm" type="submit"><i class="bi bi-chat-left-text" aria-hidden="true"></i> Explain with AI</button>
                            </form>
                        </noscript>
                    @endif
                </div>

                {{-- The Gemini AI matcher's review of the whole application (AiMatchService). Plain text only. --}}
                @if ($m->aiReviewed())
                    <h3 class="sp-mdialog-h">AI review of your matching form
                        @if ($m->ai_fit)<span>{{ \App\Models\ScholarshipMatch::FIT_LABELS[$m->ai_fit] ?? '' }}</span>@endif</h3>
                    <div class="sp-ai-review">
                        @foreach (['strengths' => ['In your favour', 'bi-hand-thumbs-up', 'pass'], 'concerns' => ['Concerns', 'bi-exclamation-circle', 'fail']] as $key => [$title, $icon, $tone])
                            @if ($m->aiList($key))
                                <div class="sp-ai-review-col">
                                    <h4>{{ $title }}</h4>
                                    <ul class="sp-checks">
                                        @foreach ($m->aiList($key) as $line)
                                            <li class="sp-check {{ $tone }}"><i class="bi {{ $icon }}" aria-hidden="true"></i><div>{{ $line }}</div></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    {{-- Reminders are notes, not eligibility: they never change the result (documents, deadlines, slots). --}}
                    @if ($m->aiList('reminders'))
                        <div class="sp-reminders">
                            <h4><i class="bi bi-bell" aria-hidden="true"></i> Reminders <small>not part of your eligibility</small></h4>
                            <ul>
                                @foreach ($m->aiList('reminders') as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($m->aiLowered())
                        <p class="sp-source"><i class="bi bi-info-circle" aria-hidden="true"></i>
                            BAKR's rule check alone said <strong>{{ $labels[$m->rule_status] ?? $m->rule_status }}</strong>. The AI review found something the rules cannot check, so the stricter result is shown.</p>
                    @endif
                @endif

                <h3 class="sp-mdialog-h">Rule check of the published limits <span>{{ $passed }} of {{ $shown->count() }} met</span></h3>
                <ul class="sp-checks" aria-label="Requirement checks">
                    @foreach ($shown as $c)
                        <li class="sp-check {{ $c['result'] }}">
                            <i class="bi {{ $icons[$c['result']] ?? 'bi-dash-circle-fill' }}" aria-hidden="true"></i>
                            <div>{{ $c['label'] }}@if (! $c['required'])<span class="opt">optional</span>@endif
                                @if ($c['detail'])<small>{{ $c['detail'] }}</small>@endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                <details class="sp-details">
                    <summary><i class="bi bi-journal-text" aria-hidden="true"></i> Benefits, documents to prepare, and how to apply</summary>
                    <div class="sp-detail-grid">
                        <div><h3>Benefits</h3><ul>@foreach ($s->benefits as $b)<li>{{ $b }}</li>@endforeach</ul></div>
                        <div><h3>Documents to prepare</h3><ul>@foreach ($s->documents as $d)<li>{{ $d }}</li>@endforeach</ul>
                            <p class="sp-source">You do not upload these to BAKR.</p></div>
                        <div><h3>How to apply</h3><p>{{ $s->application_route }}</p></div>
                        @if ($s->window_note || $s->callStatus())<div><h3>Application period</h3>@include('partials.call-status', ['scholarship' => $s])@if ($s->window_note)<p>{{ $s->window_note }}</p>@endif</div>@endif
                    </div>
                </details>

                {{-- Where the rules come from and when they were last checked: visible, not tucked away. --}}
                @if ($s->source_note || $s->verified_on)
                    <p class="sp-source sp-rules-checked"><i class="bi bi-calendar-check" aria-hidden="true"></i>
                        <span>@if ($s->verified_on)<strong>Rules last checked {{ $s->verified_on->format('M j, Y') }}.</strong> @endif{{ $s->source_note }}</span></p>
                @endif

                {{-- Applying happens with the provider, not in BAKR (owner's decision 2026-11-18:
                     providers only monitor scholars they already have; they add them to BAKR themselves). --}}
                @if (in_array($m->status, ['eligible', 'possible'], true))
                    <div class="sp-share is-submitted">
                        <i class="bi bi-send" aria-hidden="true"></i>
                        <div>
                            <strong>To apply, follow {{ $s->provider }}'s official process.</strong>
                            <small>{{ $s->application_route ?: 'See the official page for how and when to apply.' }}</small>
                            <small>BAKR does not send your answers to the provider. Once {{ $s->provider }} accepts you, they add you to BAKR and you get a scholar account (Scholar login).</small>
                        </div>
                    </div>
                @endif

                @if (! $isAi && ! $aiAvailable)
                    <p class="sp-ai-note">The AI is turned off on this server, so this explanation is built in.</p>
                @endif
            </div>

            <div class="dash-dialog-foot sp-mdialog-foot">
                @if ($s->official_url)
                    <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ $s->official_url }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Official information</a>
                @endif
                @if ($m->status === 'needs_info')
                    <a class="sp-btn sp-btn-gold sp-btn-sm" href="{{ route('student.application.show') }}">Answer missing questions</a>
                @endif
                <button type="button" class="sp-btn sp-btn-green sp-btn-sm" data-close-dialog>Close</button>
            </div>
        </dialog>
    </li>
@endforeach
</ul>
@endif
@endsection

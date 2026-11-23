@extends('student.layout')
@section('title', 'Dashboard | BAKR')

@section('hero_title')
Hello, <em>{{ $user->firstName() }}</em>
@endsection
@section('hero_sub')
Here is how your matching form matches the scholarships BAKR tracks. Last saved {{ $application->submitted_at->format('M j, Y') }} (version {{ $application->version }}).
@endsection

@section('hero_actions')
    <a class="sp-btn sp-btn-gold" href="{{ route('student.results') }}"><i class="bi bi-list-check" aria-hidden="true"></i> See all matches</a>
    <a class="sp-btn sp-btn-glass" href="{{ route('student.application.show') }}"><i class="bi bi-pencil-square"></i> Change my answers</a>
@endsection

@section('portal')
@include('student.partials.ai-match-status')

<section aria-labelledby="topTitle">
    <div class="sd-head"><h2 id="topTitle">Your best matches</h2><a href="{{ route('student.results') }}">View all <i class="bi bi-arrow-right"></i></a></div>
    @if ($top->isEmpty() && $needsInfo->isEmpty())
        @include('partials.no-match', ['nearMisses' => $nearMisses, 'catalogueUrl' => route('student.scholarships'), 'answersUrl' => route('student.application.show')])
    @elseif ($top->isEmpty())
        <div class="sp-card"><p class="sp-near-none">No scholarship is a clear match yet. Answer the missing questions below so BAKR can check the rest.</p></div>
    @else
        <div class="sd-cards">
            @foreach ($top as $m)
                <article class="sd-card">
                    <div class="sd-card-cover" style="background-image: url('{{ asset($m->scholarship->photoPath()) }}')">
                        <img src="{{ asset($m->scholarship->logoPath()) }}" alt="" loading="lazy">
                    </div>
                    <div class="sd-card-body">
                        <small>{{ $m->scholarship->sectorLabel() }}</small>
                        <h3>{{ $m->scholarship->name }}</h3>
                        <div class="sd-card-foot"><span class="sp-pill {{ $m->status }}">{{ $m->statusLabel() }}</span><a href="{{ route('student.results') }}#match-{{ $m->id }}">Why? <i class="bi bi-arrow-right"></i></a></div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</section>

<div class="sp-grid-2">
    <section class="sd-ai" style="--img: url('{{ asset('images/img/dnsc_campus.jpg') }}')" aria-labelledby="aiTitle">
        {{-- The AI matcher writes this overview with its review; if it could not, the explainer writes it (student-ai.js). --}}
        @php($explainNow = $aiAvailable && ! $summaryIsAi && ! $application->aiPending())
        <div class="sd-ai-glass" @if ($explainNow) data-ai-url="{{ route('student.results.explain') }}" data-ai-auto data-ai-label="AI overview" data-fallback-label="Quick overview" @endif>
            <span class="sp-ai-tag" data-ai-tag><i class="bi bi-chat-left-text" aria-hidden="true"></i> {{ $summaryIsAi ? 'AI overview' : 'Quick overview' }}</span>
            <h2 id="aiTitle">BAKR overview</h2>
            <p data-ai-text>{{ $summary }}</p>
            <p class="sp-ai-note">BAKR's rules check the published limits and the Gemini AI matcher reviews your whole application; the stricter result counts. The AI never sees your name or contact details.</p>
            @if ($explainNow)
                <noscript>
                    <form method="POST" action="{{ route('student.results.explain') }}">
                        @csrf
                        <button class="sp-btn sp-btn-gold sp-btn-sm" type="submit"><i class="bi bi-chat-left-text" aria-hidden="true"></i> Explain my results with AI</button>
                    </form>
                </noscript>
            @endif
        </div>
    </section>

    @if ($needsInfo->isNotEmpty())
        <section class="sp-card" aria-labelledby="infoTitle">
            <h2 id="infoTitle">Finish your answers</h2>
            <p class="sp-sub">These scholarships cannot be checked yet.</p>
            <ul class="sd-todo">
                @foreach ($needsInfo as $m)
                    <li><img src="{{ asset($m->scholarship->logoPath()) }}" alt="" loading="lazy"><span>{{ $m->scholarship->name }}</span></li>
                @endforeach
            </ul>
            <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ route('student.application.show') }}">Answer the missing questions</a>
        </section>
    @else
        <section class="sp-card sd-next" aria-labelledby="nextTitle">
            <h2 id="nextTitle">You're all set</h2>
            <p class="sp-sub">BAKR could check every scholarship with your answers. Here's where you stand.</p>
            <ul class="sd-tally">
                @foreach (\App\Models\ScholarshipMatch::STATUS_ORDER as $st)
                    <li>
                        <a href="{{ route('student.results', ['status' => $st]) }}">
                            <b class="sp-pill {{ $st }}">{{ \App\Models\ScholarshipMatch::STATUS_LABELS[$st] }}</b>
                            <strong>{{ $counts[$st] ?? 0 }}</strong>
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="sp-sub">Your next step: open an Eligible or Nearly eligible match and follow the provider's <strong>official way to apply</strong>. Once the provider accepts you, they add you to BAKR as a scholar.</p>
            <div class="sd-next-actions">
                <a class="sp-btn sp-btn-gold sp-btn-sm" href="{{ route('student.results') }}"><i class="bi bi-list-check" aria-hidden="true"></i> See all matches</a>
                <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ route('student.scholarships') }}"><i class="bi bi-mortarboard"></i> Browse scholarships</a>
            </div>
        </section>
    @endif
</div>
@endsection

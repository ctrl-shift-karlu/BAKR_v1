{{-- Shown when no scholarship is a match (likely, possible or needs info). Lists the scholarships
     that miss by exactly ONE required rule, closest first. Text comes only from the rule labels
     and details (RuleEvaluator), never invented.
     $nearMisses   Collection of ScholarshipMatch (ScholarshipMatch::nearMisses)
     $catalogueUrl where "Full details" points (student portal or public catalogue)
     $answersUrl   optional: where the student changes their answers
     $answersLabel optional: label for that button --}}
<section class="sp-nomatch" aria-labelledby="nomatch-title">
    <div class="sp-card sp-nomatch-head">
        <span class="sp-nomatch-icon" aria-hidden="true"><i class="bi bi-search"></i></span>
        <div>
            <h2 id="nomatch-title">No scholarship matches you right now</h2>
            <p class="sp-sub">With your current answers, every scholarship in BAKR has at least one requirement you don't meet yet.</p>
        </div>
        @isset($answersUrl)
            <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ $answersUrl }}"><i class="bi bi-pencil-square" aria-hidden="true"></i> {{ $answersLabel ?? 'Change my answers' }}</a>
        @endisset
    </div>

    @if ($nearMisses->isNotEmpty())
        <h3 class="sp-near-title">Closest: missing just one requirement <span>{{ $nearMisses->count() }}</span></h3>
        <div class="sp-near-list">
            @foreach ($nearMisses as $m)
                @php
                    $s = $m->scholarship;
                    $miss = $m->missingRequirement();
                @endphp
                <article class="sp-card sp-near" aria-labelledby="near-{{ $s->slug }}">
                    <div class="sp-near-head">
                        <div>
                            <span class="sp-sector">{{ $s->sectorLabel() }}</span>
                            <h4 id="near-{{ $s->slug }}">{{ $s->name }}</h4>
                        </div>
                        <span class="sp-near-score">1 requirement away</span>
                    </div>
                    <p class="sp-near-miss">
                        <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                        <span><strong>Missing:</strong> {{ $miss['label'] }}@if (! empty($miss['detail']))<small>{{ $miss['detail'] }}</small>@endif</span>
                    </p>
                    <div class="sp-match-foot">
                        <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ $catalogueUrl }}#card-{{ $s->slug }}"><i class="bi bi-journal-text" aria-hidden="true"></i> Full details</a>
                        @if ($s->official_url)
                            <a class="sp-btn sp-btn-line sp-btn-sm" href="{{ $s->official_url }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Official site</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <p class="sp-source">If your situation changes and you meet that requirement, update your answers and BAKR will check again. The provider always makes the final decision.</p>
    @else
        <div class="sp-card"><p class="sp-near-none">No scholarship is just one requirement away from you either. If your situation changes, update your answers and BAKR will check again.</p></div>
    @endif
</section>

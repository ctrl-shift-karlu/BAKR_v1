{{-- Where the Gemini AI matcher stands for the current application (results page + dashboard).
     pending: student-ai.js POSTs to student.results.ai-match ([data-ai-match-url]) and reloads when done;
     without JavaScript the button does the same. The rule-check results are shown meanwhile. --}}
@php($state = $application->ai_match_status)
@if ($state === \App\Models\ScholarshipApplication::AI_PENDING)
    <div class="sp-note sp-ai-run" data-ai-match-url="{{ route('student.results.ai-match') }}" role="status" aria-live="polite">
        <i class="bi bi-hourglass-split" aria-hidden="true"></i>
        <span>
            <strong data-ai-match-title>The Gemini AI matcher is reviewing your matching form.</strong>
            <span data-ai-match-text>It compares your whole form with every scholarship's eligibility and requirements. This takes up to a minute. Until it finishes, these results come from BAKR's rule check of the published limits only.</span>
        </span>
        <form method="POST" action="{{ route('student.results.ai-match') }}" data-ai-match-fallback>
            @csrf
            <button class="sp-btn sp-btn-green sp-btn-sm" type="submit"><i class="bi bi-cpu" aria-hidden="true"></i> Run the AI review</button>
        </form>
    </div>
@elseif ($state === \App\Models\ScholarshipApplication::AI_FAILED)
    <div class="sp-note sp-ai-run is-failed" role="status">
        <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
        <span><strong>The AI matcher could not finish.</strong> These results come from BAKR's rule check of the published limits only.</span>
        <form method="POST" action="{{ route('student.results.ai-match') }}">
            @csrf
            <button class="sp-btn sp-btn-green sp-btn-sm" type="submit"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Try the AI review again</button>
        </form>
    </div>
@elseif ($state === \App\Models\ScholarshipApplication::AI_DONE)
    <div class="sp-note sp-ai-run is-done">
        <i class="bi bi-cpu" aria-hidden="true"></i>
        <span><strong>Reviewed by the Gemini AI matcher</strong>@if ($application->ai_matched_at) on {{ $application->ai_matched_at->format('M j, Y') }}@endif.
            Each result is the stricter of BAKR's rule check of the published limits and the AI's review of your whole application, so the AI can never pass you on a limit you fail.</span>
    </div>
@else
    <div class="sp-note sp-ai-run">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>The AI matcher is turned off on this server, so these results come from BAKR's rule check of the published limits only.</span>
    </div>
@endif

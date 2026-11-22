{{-- The people behind BAKR (owner, 2026-11-21: names only, no roles).
     Photos: add a square image at public/images/img/team/member-1.jpg, member-2.jpg, member-3.jpg
     (same order as below, in PublicAnalyticsService::placeholders()). It replaces the placeholder on its own. --}}
<section class="nx-section bk-team-section" id="team" aria-labelledby="teamTitle">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="nx-h2" id="teamTitle">The people behind BAKR</h2>
            <p class="nx-lead mx-auto">The DNSC students who designed and built BAKR for you.</p>
        </div>
        <ul class="bk-team">
            @foreach ($blocks['team'] as $m)
                <li class="bk-team-card">
                    <figure class="bk-photo-slot bk-ratio-sq bk-slot-round" data-photo="{{ asset($m['photo']) }}" data-alt="Photo of {{ $m['name'] }}">
                        <div class="bk-slot-empty"><i class="bi bi-person-fill" aria-hidden="true"></i></div>
                    </figure>
                    <strong class="bk-team-name">{{ $m['name'] }}</strong>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- Saved answers grouped by part of the form (My profile, a saved version).
     $answers  = the version shown; $sections = ApplicationSchema::sections()
     optional: $changed = field keys that differ from $compare (the current answers), shown as "Now: …". --}}
@php
    $changed = $changed ?? [];
    $compare = $compare ?? null;
@endphp
<div class="sp-prof-parts">
    @foreach ($sections as $section)
        @php
            $rows = [];
            foreach ($section['fields'] as $key => $f) {
                if ($key === 'college_gwa_scale') {
                    continue;   // shown together with the grade it belongs to
                }
                $isChanged = in_array($key, $changed, true);
                if (! $isChanged && ! \App\Matching\ApplicationSchema::applies($f['when'] ?? null, $answers)) {
                    continue;   // a question this student was never asked (e.g. senior high school for a 2nd-year)
                }
                $rows[] = [
                    'label' => $f['label'],
                    'shown' => \App\Matching\ApplicationSchema::shownAnswer($key, $answers),
                    'changed' => $isChanged,
                    'now' => $isChanged && $compare !== null ? \App\Matching\ApplicationSchema::shownAnswer($key, $compare) : null,
                ];
            }
            $done = count(array_filter($rows, fn ($r) => $r['shown'] !== null));
            $diff = count(array_filter($rows, fn ($r) => $r['changed']));
        @endphp
        <article class="sp-prof-part">
            <header>
                <span class="sp-prof-ico" aria-hidden="true"><i class="bi {{ $section['icon'] }}"></i></span>
                <div>
                    <h3>Part {{ $section['part'] }}: {{ $section['title'] }}</h3>
                    <small>{{ $done }} of {{ count($rows) }} answered{{ $diff ? ', '.$diff.' different from now' : '' }}</small>
                </div>
            </header>
            <dl>
                @foreach ($rows as $r)
                    <div @if ($r['changed']) class="is-changed" @endif>
                        <dt>{{ $r['label'] }}@if ($r['changed'])<span class="sp-changed-tag">Different now</span>@endif</dt>
                        <dd class="{{ $r['shown'] === null ? 'empty' : '' }}">
                            {{ $r['shown'] ?? 'Not answered' }}
                            @if ($r['changed'])
                                <span class="sp-now">Now: {{ $r['now'] ?? 'Not answered' }}</span>
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        </article>
    @endforeach
</div>

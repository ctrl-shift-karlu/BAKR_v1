{{-- Scholarship catalogue: sector tabs, search, cards and detail modals, all from the scholarships table.
     Used by the public Scholarships page and the student portal (student.scholarships).
     Optional: $matches (keyed by scholarship_id) for a signed-in student. --}}
@php
    $tagFor = [
        'internal' => ['bi-building', 'DNSC INTERNAL'],
        'government' => ['bi-bank', 'GOVERNMENT / EXTERNAL'],
        'private' => ['bi-people-fill', 'PRIVATE / NGO / FOUNDATION'],
    ];
    $panelId = fn ($sector) => $sector === 'internal' ? 'dnsc' : $sector;
    $missingFor = fn ($s) => 'Not published by provider. Confirm with '.($s->provider ?: 'the provider').'.';
    $matches = $matches ?? collect();
@endphp
<div class="directory-wrap">
   <div class="container">
    <div class="library-layout">
     <aside class="library-sidebar">
      <div class="shelf-card">
       <div class="shelf-heading">
        <div class="shelf-icon"><i class="bi bi-bookshelf" aria-hidden="true"></i></div>
        <div>
         <h2>Scholarship Shelf</h2>
         <p>Choose a sector to browse</p>
        </div>
       </div>
       <div class="shelf-vertical-divider" aria-hidden="true"></div>
       <div class="nav sector-tabs" id="sectorTabs" role="tablist">
        @foreach ($sections as $section)
         <button class="nav-link @if ($loop->first) active @endif" data-bs-target="#{{ $panelId($section['sector']) }}-panel" data-bs-toggle="pill" id="{{ $panelId($section['sector']) }}-tab" role="tab" type="button">
          <i class="bi {{ $tagFor[$section['sector']][0] }}" aria-hidden="true"></i>
          <span>
           <strong>{{ $section['title'] }}</strong>
           <small>{{ $section['items']->count() }} {{ $section['kind'] }} {{ \Illuminate\Support\Str::plural('program', $section['items']->count()) }}</small>
          </span>
         </button>
        @endforeach
       </div>
      </div>
     </aside>

     <section class="library-results">
      <div class="directory-tools" role="search" aria-label="Search scholarships">
       <label class="visually-hidden" for="scholarshipSearch">Search scholarships</label>
       <div class="directory-search">
        <span class="directory-search-icon" aria-hidden="true"><i class="bi bi-search"></i></span>
        <input id="scholarshipSearch" type="search" class="form-control" placeholder="Search scholarships, benefits, or requirements..." autocomplete="off" enterkeyhint="search">
        <button type="button" class="directory-search-clear" id="clearScholarshipSearch" aria-label="Clear search" title="Clear search"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
       </div>
       <div class="directory-tool-meta d-flex flex-column flex-sm-row justify-content-sm-between gap-1 gap-sm-3"><span><i class="bi bi-funnel me-1" aria-hidden="true"></i> Filter by sector using the shelf options</span><span id="scholarshipSearchStatus" aria-live="polite">Showing all {{ $total }} scholarships</span></div>
      </div>

      <div class="tab-content" id="sectorTabContent">
       @foreach ($sections as $section)
        <div aria-labelledby="{{ $panelId($section['sector']) }}-tab" class="tab-pane fade @if ($loop->first) show active @endif" id="{{ $panelId($section['sector']) }}-panel" role="tabpanel">
         <div class="directory-head">
          <div>
           <h2>{{ $section['title'] }}</h2>
           <p>{{ $section['blurb'] }}</p>
          </div>
          <span class="count-bubble"><i class="bi bi-bookmark-star-fill"></i> {{ $section['items']->count() }} {{ \Illuminate\Support\Str::plural('program', $section['items']->count()) }}</span>
         </div>
         <div class="row g-4">
          @foreach ($section['items'] as $s)
           <div class="col-md-6">
            <article class="scholar-card" id="card-{{ $s->slug }}">
             <div class="card-paper">
              <div class="logo-orbit"><img alt="{{ $s->provider }} logo" src="{{ asset($s->logoPath()) }}"></div>
             </div>
             <div class="card-body-custom">
              <span class="sector-tag {{ $s->sector }}"><i class="bi {{ $tagFor[$s->sector][0] ?? 'bi-bookmark' }}"></i> {{ $tagFor[$s->sector][1] ?? strtoupper($s->sectorLabel()) }}</span>
              <h3>{{ $s->name }}</h3>
              @include('partials.call-status', ['scholarship' => $s, 'compact' => true])
              @php($mine = $matches->get($s->id))
              @if ($mine)
               <p class="sch-mine">
                <span class="sp-pill {{ $mine->status }}">{{ $mine->statusLabel() }}</span>
               </p>
              @endif
              <p>{{ filled($s->overview) ? $s->overview : $missingFor($s) }}</p>
              <div class="card-actions">
               <button class="btn btn-details" data-bs-target="#modal-{{ $s->slug }}" data-bs-toggle="modal" type="button">View details</button>
               @if ($s->official_url)
                <a class="btn btn-scholarship-green btn-apply" aria-label="Apply now for {{ $s->name }}, opens in a new tab" href="{{ $s->official_url }}" target="_blank" rel="noopener">Apply now <i class="bi bi-box-arrow-up-right ms-1"></i></a>
               @endif
              </div>
             </div>
            </article>
           </div>
          @endforeach
         </div>
        </div>
       @endforeach
      </div>
     </section>
    </div>
   </div>
</div>

@foreach ($sections as $section)
 @foreach ($section['items'] as $s)
  <div aria-hidden="true" class="modal fade" id="modal-{{ $s->slug }}" aria-labelledby="modal-{{ $s->slug }}-title" tabindex="-1">
   <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content scholarship-modal sector-{{ $s->sector }}">
     <div class="modal-header">
      <div class="modal-brand-line">
       <img alt="" class="modal-logo" src="{{ asset($s->logoPath()) }}">
       <div>
        <span class="modal-category">{{ $tagFor[$s->sector][1] ?? strtoupper($s->sectorLabel()) }}</span>
        <h3 id="modal-{{ $s->slug }}-title">{{ $s->name }}</h3>
        <p>{{ $s->provider }}</p>
       </div>
      </div>
      <button aria-label="Close" class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"></button>
     </div>
     <div class="modal-body">
      <div class="row g-3">
       <div class="col-lg-6">
        <div class="modal-section overview">
         <div class="modal-section-title"><i class="bi bi-book"></i> Overview</div>
         <p>{{ filled($s->overview) ? $s->overview : $missingFor($s) }}</p>
        </div>
       </div>
       <div class="col-lg-6">
        <div class="modal-section qualify">
         <div class="modal-section-title"><i class="bi bi-person-check"></i> Qualifications</div>
         @include('partials.scholarship-qualifications', ['scholarship' => $s, 'missing' => $missingFor($s)])
        </div>
       </div>
       <div class="col-lg-7">
        <div class="modal-section requirements">
         <div class="modal-section-title"><i class="bi bi-check2-square"></i> Required Documents</div>
         @if (empty($s->documents))
          <p class="pv-missing">{{ $missingFor($s) }}</p>
         @else
          <ul>@foreach ($s->documents as $d)<li>{{ $d }}</li>@endforeach</ul>
         @endif
        </div>
       </div>
       <div class="col-lg-5">
        <div class="modal-section benefits">
         <div class="modal-section-title"><i class="bi bi-gift"></i> Benefits</div>
         @if (empty($s->benefits))
          <p class="pv-missing">{{ $missingFor($s) }}</p>
         @else
          <ul>@foreach ($s->benefits as $b)<li>{{ $b }}</li>@endforeach</ul>
         @endif
         <div class="benefit-tile">
          <strong>Application window</strong>
          @include('partials.call-status', ['scholarship' => $s])
          <span>{{ filled($s->window_note) ? $s->window_note : $missingFor($s) }}</span>
         </div>
        </div>
       </div>
      </div>
      <div class="application-strip">
       <div>
        <small>Application / Inquiry</small>
        <strong>{{ filled($s->application_route) ? $s->application_route : $missingFor($s) }}</strong>
       </div>
       @if ($s->official_url)
        <a class="btn btn-light btn-sm fw-bold" href="{{ $s->official_url }}" target="_blank" rel="noopener">Official info <i class="bi bi-box-arrow-up-right ms-1"></i></a>
       @endif
      </div>
      @if ($s->source_note || $s->updated_at)
       <div class="status-note">
        <i class="bi bi-info-circle me-1"></i>
        @if ($s->verified_on)<strong>Rules last checked {{ $s->verified_on->format('M j, Y') }}.</strong> @endif
        @if ($s->source_note){{ $s->source_note }}@endif
        Last updated {{ $s->updated_at?->format('M j, Y') }}.
       </div>
      @endif
     </div>
     <div class="modal-footer">
      @if ($mine = $matches->get($s->id))
       <a class="btn btn-scholarship-green me-auto" href="{{ route('student.results') }}#match-{{ $mine->id }}"><i class="bi bi-clipboard-check me-1" aria-hidden="true"></i>See my result ({{ $mine->statusLabel() }})</a>
      @endif
      <button class="btn btn-outline-secondary" data-bs-dismiss="modal" type="button">Close card</button>
     </div>
    </div>
   </div>
  </div>
 @endforeach
@endforeach

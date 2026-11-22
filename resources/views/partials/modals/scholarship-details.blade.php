{{--
    Scholarship detail pop-ups opened from the "Scholarships to start with" cards on the home page
    (data-bs-target="#<id>"). Everything comes from the scholarships table, the same rows the provider
    edits in "My scholarships" and the public Scholarships page shows, so the three never disagree.
    $featured: slug => Scholarship (set in home.blade.php)
--}}
@php
    $featuredModals = ['dnsc-academic' => 'dnscAcademicModal', 'dnsc-gia-a' => 'giaCategoryAModal', 'dnsc-gia-b' => 'giaCategoryBModal'];
@endphp

@foreach ($featuredModals as $slug => $modalId)
    @php($s = $featured[$slug] ?? null)
    @continue(! $s)
    @php($missing = 'Not published by provider. Confirm with '.($s->provider ?: 'the provider').'.')
    <div class="modal fade nx-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content c-modal">
                <div class="modal-header">
                    <div class="modal-brand-line">
                        <img src="{{ asset($s->logoPath()) }}" class="modal-logo" alt="{{ $s->provider }} logo">
                        <div>
                            <span class="modal-category">{{ $s->sectorLabel() }}</span>
                            <h3 id="{{ $modalId }}Title">{{ $s->name }}</h3>
                            <p>{{ $s->provider }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="modal-section overview">
                                <div class="modal-section-title"><i class="bi bi-book" aria-hidden="true"></i> Overview</div>
                                <p>{{ filled($s->overview) ? $s->overview : $missing }}</p>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="modal-section qualify">
                                <div class="modal-section-title"><i class="bi bi-person-check" aria-hidden="true"></i> Qualifications</div>
                                @include('partials.scholarship-qualifications', ['scholarship' => $s, 'missing' => $missing])
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="modal-section requirements">
                                <div class="modal-section-title"><i class="bi bi-check2-square" aria-hidden="true"></i> Required Documents</div>
                                @if (empty($s->documents))<p>{{ $missing }}</p>@else<ul>@foreach ($s->documents as $doc)<li>{{ $doc }}</li>@endforeach</ul>@endif
                            </div>
                        </div>
                        @if (! empty($s->benefits))
                            <div class="col-12">
                                <div class="modal-section">
                                    <div class="modal-section-title"><i class="bi bi-gift" aria-hidden="true"></i> Benefits</div>
                                    <ul>@foreach ($s->benefits as $b)<li>{{ $b }}</li>@endforeach</ul>
                                </div>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="modal-section">
                                <div class="modal-section-title"><i class="bi bi-arrow-up-right-circle" aria-hidden="true"></i> Application</div>
                                @include('partials.call-status', ['scholarship' => $s])
                                <p>{{ filled($s->application_route) ? $s->application_route : $missing }}</p>
                                @if (filled($s->window_note))<p>{{ $s->window_note }}</p>@endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a class="btn-modal-close" href="{{ route('scholarships') }}#card-{{ $s->slug }}">All details</a>
                    <button type="button" class="btn-modal-close" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

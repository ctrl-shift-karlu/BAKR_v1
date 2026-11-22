{{-- PLACEHOLDER: add public/images/img/scholarship-office.jpg and the photo appears automatically. --}}
<section class="nx-section" id="office" aria-labelledby="officeTitle">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <figure class="bk-photo-slot bk-ratio-43" data-photo="{{ asset($blocks['office']['photo']) }}">
                    <div class="bk-slot-empty">
                        <i class="bi bi-building" aria-hidden="true"></i>
                        <strong>Photo of the Scholarship Office building</strong>
                        <span>Add <code>public/images/img/scholarship-office.jpg</code></span>
                    </div>
                </figure>
            </div>
            <div class="col-lg-6">
                <h2 class="nx-h2" id="officeTitle">The DNSC Scholarship Office</h2>
                <p class="nx-lead mb-4">BAKR helps you prepare. For official questions and endorsements, visit the office and ask in person.</p>
                <dl class="bk-office-list">
                    @foreach ($blocks['office']['details'] as $label => $value)
                        <div><dt>{{ $label }}</dt><dd class="bk-tbd">{{ $value }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</section>

{{-- Printable copy of the student's current matching form. Standalone, always light (it is a paper
     document). Only the signed-in student's own form (StudentApplicationController::printable).
     Documents come only from the matched scholarships' `documents` in the scholarships table. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $application->referenceNumber() }} | BAKR matching form</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Source+Serif+4:ital,wght@0,600;0,700;1,500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/bakr-form-print.css') }}">
</head>
<body>
<div class="pf-toolbar">
    <a href="{{ route('student.profile') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to my profile</a>
    <div class="pf-actions">
        <button type="button" class="is-line" data-save-pdf><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Save as PDF</button>
        <button type="button" data-print><i class="bi bi-printer" aria-hidden="true"></i> Print</button>
    </div>
</div>
{{-- Shown by bakr-print.js when "Save as PDF" is pressed: browsers save PDFs through their print window. --}}
<p class="pf-hint" id="pdfHint" role="status" hidden><i class="bi bi-info-circle" aria-hidden="true"></i> In the window that opens, set <strong>Destination</strong> (or <strong>Printer</strong>) to <strong>Save as PDF</strong>, then press <strong>Save</strong>. The file is named {{ $application->referenceNumber() }}.</p>

<main class="pf-sheet">
    <header class="pf-head">
        <img src="{{ asset('images/logo/bakr-logo.png') }}" alt="" width="56" height="56">
        <div>
            <span>Davao del Norte State College · BAKR</span>
            <h1>Scholarship Matching Form</h1>
        </div>
        <dl>
            <div><dt>Reference no.</dt><dd>{{ $application->referenceNumber() }}</dd></div>
            <div><dt>Version</dt><dd>{{ $application->version }}</dd></div>
            <div><dt>Submitted</dt><dd>{{ $application->submitted_at->format('F j, Y g:i A') }}</dd></div>
        </dl>
    </header>

    <p class="pf-note">This is BAKR's matching form, not the official application form of any scholarship provider. Apply to each scholarship through the provider's own process.</p>

    <section class="pf-part">
        <h2><small>Student</small></h2>
        <dl class="pf-grid">
            <div><dt>Student name</dt><dd>{{ $user->name }}</dd></div>
            <div><dt>E-mail</dt><dd>{{ $user->email }}</dd></div>
        </dl>
    </section>

    @foreach (array_values($sections) as $i => $section)
        <section class="pf-part">
            <h2><small>Part {{ $section['part'] }}</small> {{ $section['title'] }}</h2>
            <dl class="pf-rows">
                @php $n = 0; @endphp
                @foreach (array_keys($section['fields']) as $key)
                    @continue($key === 'college_gwa_scale')
                    @php $n++; @endphp
                    @php $shown = \App\Matching\ApplicationSchema::shownAnswer($key, $application->answers); @endphp
                    <div>
                        <dt><b>{{ $i + 1 }}.{{ $n }}</b> {{ $section['fields'][$key]['label'] }}</dt>
                        <dd class="{{ $shown === null ? 'is-empty' : '' }}">{{ $shown ?? 'Not answered / not applicable' }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endforeach

    <section class="pf-part pf-cert">
        <h2><small>Part {{ \App\Matching\ApplicationSchema::certificationPart() }}</small> Certification</h2>
        <p>I certify that the information I gave in this form is true and correct to the best of my knowledge. I understand that wrong answers can give me wrong matches, and that each scholarship provider will check my documents when I apply to them.</p>
        <div class="pf-sign">
            <div><span class="pf-sig">{{ $user->name }}</span><small>Signature over printed name (typed when submitted)</small></div>
            <div><span>{{ $application->submitted_at->format('F j, Y') }}</span><small>Date</small></div>
        </div>
    </section>

    <section class="pf-part">
        <h2><small>Attachment guide</small> Documents to prepare</h2>
        @if ($matches->isEmpty())
            <p class="pf-muted">None of the scholarships fit your answers yet, so there are no documents to prepare.</p>
        @else
            <p class="pf-muted">Listed by each scholarship you matched. You submit these to the provider when you apply, not to BAKR.</p>
            @foreach ($matches as $m)
                <div class="pf-docs">
                    <h3>{{ $m->scholarship->name }} <span>{{ $m->statusLabel() }}</span></h3>
                    @if (! empty($m->scholarship->documents))
                        <ul>@foreach ($m->scholarship->documents as $doc)<li><span class="pf-box" aria-hidden="true"></span>{{ $doc }}</li>@endforeach</ul>
                    @else
                        <p class="pf-muted">The provider has not listed documents. Check its official information.</p>
                    @endif
                </div>
            @endforeach
        @endif
    </section>

    <footer class="pf-foot">Printed {{ now()->format('F j, Y g:i A') }} · {{ $application->referenceNumber() }} · Confidential: contains personal information (Data Privacy Act of 2012).</footer>
</main>

<script src="{{ asset('js/bakr-print.js') }}"></script>
</body>
</html>

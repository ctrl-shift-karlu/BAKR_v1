@extends('provider.layout')

@section('title', 'Help')
@section('photo', 'opt/lib.jpg')
@section('kicker', 'Help & contact')
@section('heading')How BAKR <em>works for you</em>@endsection
@section('sub', 'What you can see and why, and a direct line to the BAKR team for corrections or questions.')

@section('content')
@php
    $hasPay = $scope->has('disbursements');
    $hasReqs = $scope->has('requirements');
    $faqs = array_values(array_filter([
        ['Does BAKR handle applications?', 'No. Students apply with you through your own process. BAKR is where you keep track of the scholars you already have: add them under Scholars (one by one or from Excel)'.($hasPay ? ', record their stipends' : '').($hasReqs ? ', and review the documents they submit each semester' : '').'. Students\' application answers are never shown to you.'],
        ['How do I add new scholars?', 'On Scholars, press "Add new scholar" to add one person, or "Import from Excel" to upload your list (.xlsx or CSV, one scholar per row). You enter the scholar\'s name, student number, campus, program (the list shows only the programs that campus offers), year level and e-mail, plus the grant type where a scholarship has more than one (for example Full or Half). BAKR fills in the rest from the scholarship: status Active, this semester as the start, and the published stipend. Adding a scholar also creates their BAKR account: the e-mail you enter becomes their sign-in, and BAKR e-mails them a temporary password that they must change the first time they sign in (you never see it). Scholars sign in at the Scholar login, which is separate from the student (matching) login. If the e-mail already has a BAKR student account, it becomes a scholar account (same password). If the e-mail does not arrive, open the scholar and press "Resend login details". Rows with a student number or e-mail that is already on that scholarship are skipped.'],
        ['A scholar says their details are wrong. Where do I see that?', 'Under Messages. Your scholars send correction requests from their Profile ("Request a correction"), and only you receive them (not the BAKR team, not other providers). Press "Open the profile to correct it" to edit the right card, then Reply: your answer is e-mailed to the scholar, shows on their Profile, and the message is marked handled.'],
        ['I forgot my password. What do I do?', 'On the provider login page, press "Forgot password?" and enter your account e-mail. BAKR sends a link to set a new password (it works for 60 minutes).'],
        $hasReqs ? ['How do maintaining documents work?', 'Set them under My scholarships › Maintaining documents: a name, accepted files, a size limit, and whether every scholar must submit it. On Requirements, set each scholarship\'s deadline for the semester: that opens the documents for its active scholars who started by then and e-mails them the date (changing it tells them again). Each scholar then uploads a file per item, and you approve it or ask for a revision.'] : null,
        $hasPay && $hasReqs ? ['Why can\'t I release a stipend?', 'A stipend can be released only after the scholar\'s required maintaining documents for that semester are ALL approved. Locked rows on Disbursements say what is missing; approve the uploads on Requirements and the lock opens by itself, so the Release button works again. You can still prepare the records before that.'] : null,
        ['If I change a qualification, what happens?', 'BAKR re-checks every student\'s current answers right away, and the public Scholarships page updates. Only publish numbers you have officially announced.'],
        $hasReqs
            ? ['When is a scholar Inactive or Terminated?', 'Inactive is automatic: a scholar with a required maintaining document still not submitted after its due date shows as Inactive, and turns Active again once they submit. Terminate a scholar (or reinstate them) from their profile under Scholars; only you can do this for your own scholars.']
            : ['Where do my scholars submit documents, and how do I mark them Inactive?', 'Maintaining documents are submitted in person at the official DNSC scholarship office ('.config('bakr.scholarship_office').'), not in BAKR. When a scholar\'s documents are not complete, open them under Scholars and press "Mark inactive"; press "Mark active" once they are. You can also terminate or reinstate a scholar there; only you can do this for your own scholars.'],
        $hasReqs ? ['Who can see uploaded files?', 'Only the scholar who uploaded them and your account. Files are stored privately, never on a public link.'] : null,
    ]));
@endphp

<div class="dash-split">
    <div class="dash-stack">
        <section class="dash-card" id="about" aria-labelledby="about-title">
            <h2 class="dash-section-title" id="about-title">About BAKR</h2>
            @include('partials.portal.about-bakr', ['audience' => 'provider'])
        </section>

        <section class="dash-card" aria-labelledby="faq-title">
            <h2 class="dash-section-title" id="faq-title">Questions providers ask</h2>
            <div class="help-faq">
                @foreach ($faqs as [$q, $a])
                    <details @if ($loop->first) open @endif>
                        <summary>{{ $q }}</summary>
                        <p>{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    </div>

    <section class="dash-card help-contact" id="message" aria-labelledby="msg-title">
        <h2 class="dash-section-title" id="msg-title">Message the BAKR team</h2>
        <p class="pv-muted">Sent as <strong>{{ $provider['name'] }}</strong>. We reply to <strong>{{ auth()->user()->email }}</strong>.</p>
        <form method="POST" action="{{ route('provider.help.message') }}" class="dash-stack" data-busy="Sending your message…" data-busy-sub="The BAKR team will reply by e-mail">
            @csrf
            <div class="dash-field">
                <label for="topic">What is it about?</label>
                <select id="topic" name="topic" required>
                    @foreach ($topics as $key => $label)
                        <option value="{{ $key }}" @selected(old('topic', 'question') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-field">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" minlength="10" maxlength="2000" required>{{ old('message') }}</textarea>            </div>
            <div><button type="submit" class="dash-btn"><i class="bi bi-send" aria-hidden="true"></i>Send message</button></div>
        </form>
    </section>
</div>
@endsection

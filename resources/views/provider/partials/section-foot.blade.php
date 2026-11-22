{{-- Save / Cancel row at the bottom of ONE section being edited (My scholarships, a scholar's profile).
     $cancel: URL back to the read view of that section.
     $note:   optional line on the left (default: the My scholarships revision note). --}}
<div class="ms-section-foot">
    <small>{{ $note ?? 'Saves only this section. Every save is recorded with your account and the time.' }}</small>
    <span class="ms-section-foot-btns">
        <a class="dash-btn is-soft" href="{{ $cancel }}">Cancel</a>
        <button type="submit" class="dash-btn"><i class="bi bi-cloud-check" aria-hidden="true"></i>Save section</button>
    </span>
</div>

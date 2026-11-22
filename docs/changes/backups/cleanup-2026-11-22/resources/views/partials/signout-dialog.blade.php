{{-- Sign-out confirmation for every portal (provider, student, scholar). Opened by any
     [data-open-dialog="confirm-signout"] (public/js/bakr-dashboard.js); without JavaScript the
     link's #confirm-signout target shows it in place (.dash-confirm:target).
     $logoutRoute: 'student.logout' | 'scholar.logout' | 'provider.logout'. The confirm button uses the danger red. --}}
<dialog class="dash-dialog dash-confirm" id="confirm-signout" aria-labelledby="signout-title">
    <div class="dash-dialog-body">
        <span class="dash-confirm-icon" aria-hidden="true"><i class="bi bi-box-arrow-right"></i></span>
        <h2 id="signout-title">Sign out of BAKR?</h2>
        <p>You'll need your e-mail and password to sign in again.</p>
    </div>
    <div class="dash-dialog-foot">
        <button type="button" class="dash-btn is-soft" data-close-dialog>Stay signed in</button>
        <form method="POST" action="{{ route($logoutRoute) }}" data-busy="Signing you out…" data-busy-sub="See you again soon">
            @csrf
            <button type="submit" class="dash-btn is-danger"><i class="bi bi-box-arrow-right" aria-hidden="true"></i>Sign out</button>
        </form>
    </div>
</dialog>

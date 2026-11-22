<?php

namespace App\Http\Controllers\Scholar;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ScholarshipCatalogController;
use App\Models\ContactMessage;
use App\Models\Scholar;
use App\Models\ScholarMessage;
use App\Services\ContactMessageService;
use App\Support\AcademicTerm;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Scholar portal (role "scholar", signed in at /scholarLogin; separate from student matching accounts).
 *   Overview         summary of every scholarship held + what needs doing now
 *   My scholarship   details, benefits, maintaining documents, stipend information
 *   Requirements     each scholarship's own maintaining documents per semester (uploads:
 *                    ScholarRequirementController), for providers that collect them in BAKR
 *   Profile          the scholar's details, grouped like the scholar prototype (read-only: kept by the provider);
 *                    "Request a correction" goes to that scholarship's provider (ScholarMessage), not the admin
 *   Settings         change password, e-mail a reset link, sign out (with confirmation)
 *   Explore          All scholarships, Help & contact
 * Every record is read through the signed-in user's own scholar rows (rule 4).
 */
class ScholarPortalController extends Controller
{
    public function overview(Request $request): View
    {
        $scholars = $this->scholars($request);
        $term = AcademicTerm::current();

        $actions = [];
        foreach ($scholars as $sc) {
            $short = $sc->scholarship->shortName();
            // A terminated scholarship has no more to-dos (its own "terminated" note is added below).
            if ($sc->scholarship->offers('requirements') && ! $sc->isTerminated()) {
                // "Only if it applies" items that were never sent are not a to-do (isOwed()). Every semester counts: a
                // provider may open an earlier semester's documents late (owner, 2026-11-21: stipends often come late).
                foreach ($sc->requirements->filter->isOwed() as $r) {
                    $when = $r->term.($r->due_on ? ' · due '.$r->due_on->format('M j, Y') : '');
                    match ($r->status) {
                        'needs_revision' => $actions[] = ['danger', 'bi-arrow-repeat', "Revise and re-upload: {$r->name}", $short.($r->review_note ? ' · “'.$r->review_note.'”' : ' · '.$r->term), route('scholar.requirements').'#req-'.$r->id],
                        'missing' => $actions[] = ['warn', 'bi-upload', "Upload: {$r->name}", "{$short} · {$when}", route('scholar.requirements').'#req-'.$r->id],
                        default => null,
                    };
                }
            }
            // A stipend waiting for this semester's requirements (released only when all are approved).
            $block = ! $sc->isTerminated() && $sc->scholarship->offers('disbursements') && $sc->disbursements->where('term', $term)->where('status', 'pending')->isNotEmpty()
                ? $sc->releaseBlock($term) : null;
            if ($block && $block[0] === 'not_approved') {
                $actions[] = ['info', 'bi-lock', "{$short}: your {$term} stipend is waiting for your requirements", $sc->scholarship->provider.' can release it once every requirement for the semester is approved.', route('scholar.requirements').'#sch-'.$sc->id];
            } elseif ($block && in_array($block[0], ['no_deadline', 'not_listed'], true)) {
                // Nothing for the scholar to upload yet: the provider has not opened this semester's documents for them.
                $actions[] = ['info', 'bi-hourglass-split', "{$short}: your {$term} stipend is waiting for your provider", $sc->scholarship->provider.' has not opened your '.$term.' maintaining documents yet. You will get an e-mail when you can upload them.', route('scholar.requirements').'#sch-'.$sc->id];
            }
            // (Inactive and terminated have their own notes below.)
            // Inactive: marked by a provider that checks documents in person, or automatic (overdue documents).
            if ($sc->isMarkedInactive()) {
                $actions[] = ['danger', 'bi-pause-circle', "Your {$short} scholarship is marked inactive",
                    'Complete your maintaining documents in person at the official DNSC scholarship office ('.config('bakr.scholarship_office').'), then '.$sc->scholarship->provider.' can mark you active again.', route('scholar.scholarship', ['s' => $sc->id])];
            } elseif ($sc->standing() === 'inactive' && ($late = $sc->overdueRequirements())->isNotEmpty()) {
                $actions[] = ['danger', 'bi-alarm', "Your {$short} scholarship is inactive: ".$late->count().' '.str('document')->plural($late->count()).' past due',
                    'Submit '.$late->pluck('name')->implode(', ').' to become active again.', route('scholar.requirements').'#req-'.$late->first()->id];
            }
            if ($sc->isTerminated()) {
                $actions[] = ['info', 'bi-x-circle', "Your {$short} scholarship was terminated", 'Contact '.$sc->scholarship->provider.' if you have questions.', route('scholar.scholarship', ['s' => $sc->id])];
            }
            if ($sc->scholarship->hasGrantTypes() && ! $sc->grant_type) {
                $actions[] = ['info', 'bi-hourglass-split', "{$short}: grant type not set yet", $sc->scholarship->provider.' still has to record whether your grant is '.implode(' or ', array_map(fn ($g) => $g['label'], $sc->scholarship->grantOptions())).'.', route('scholar.scholarship', ['s' => $sc->id])];
            }
        }
        // Most urgent first: revisions, then uploads, then the rest.
        usort($actions, fn ($a, $b) => array_search($a[0], ['danger', 'warn', 'info'], true) <=> array_search($b[0], ['danger', 'warn', 'info'], true));

        // Updates from providers (App\Notifications\ScholarUpdate, 2026-11-21): the latest few; seeing them
        // here marks them read (the sidebar badge then clears).
        $user = $request->user();
        $updates = $user->notifications()->latest()->take(6)->get();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return view('scholar.overview', [
            'scholars' => $scholars,
            'term' => $term,
            'actions' => $actions,
            'reqs' => $this->requirementCounts($scholars, $term),
            'updates' => $updates,
        ]);
    }

    public function scholarship(Request $request): View|RedirectResponse
    {
        $scholars = $this->scholars($request);
        $selected = $scholars->firstWhere('id', (int) $request->query('s')) ?? $scholars->first();
        if (! $selected) {
            return redirect()->route('scholar.overview')->with('notice', 'No scholarship is linked to your account yet. Please contact your provider.');
        }

        return view('scholar.scholarship', ['scholars' => $scholars, 'sc' => $selected, 'term' => AcademicTerm::current()]);
    }

    public function requirements(Request $request): View
    {
        $scholars = $this->scholars($request);

        return view('scholar.requirements', [
            'scholars' => $scholars,
            'term' => AcademicTerm::current(),
            'reqs' => $this->requirementCounts($scholars, AcademicTerm::current()),
        ]);
    }

    /** Profile: who the scholar is, grouped (personal, contact, school, scholarships). Read-only. */
    public function profile(Request $request): View
    {
        $scholars = $this->scholars($request);
        // Personal and contact details are recorded by each provider: the first record that has each one.
        $first = fn (string $method) => $scholars->map(fn ($sc) => $sc->{$method}())->filter()->first();

        return view('scholar.profile', [
            'scholars' => $scholars,
            'user' => $request->user(),
            'topics' => ScholarMessage::TOPICS,
            // The correction requests this account sent, newest first, with the provider's reply.
            'requests' => ScholarMessage::query()->whereIn('scholar_id', $scholars->pluck('id'))
                ->with('scholar.scholarship')->latest()->latest('id')->limit(20)->get(),
            // School details come from the provider's records; the first scholarship that has them.
            'school' => $scholars->first(fn ($sc) => $sc->student_number || $sc->program || $sc->year_level) ?? $scholars->first(),
            'personal' => [
                'birthdate' => $first('birthdateLabel'),
                'gender' => $first('genderLabel'),
                'mobile' => $first('mobileLabel'),
                'address' => $scholars->pluck('address')->filter()->first(),
            ],
        ]);
    }

    /**
     * "Request a correction" (owner, 2026-11-20): the scholar's details are kept by the provider, so the
     * request goes to the provider of the chosen scholarship (their Messages page), not to the admin.
     * The scholarship must be one of the signed-in scholar's own (rule 4); the sender comes from the account.
     */
    public function requestCorrection(Request $request): RedirectResponse
    {
        $scholars = Scholar::query()->where('user_id', $request->user()->id)->with('scholarship')->get();
        if ($scholars->isEmpty()) {
            return redirect()->route('scholar.profile')->with('notice', 'No scholarship is linked to your account yet, so there is no provider to send a correction to.');
        }

        $data = $request->validateWithBag('correction', [
            'scholar_id' => ['required', 'integer', Rule::in($scholars->pluck('id')->all())],
            'topic' => ['required', Rule::in(array_keys(ScholarMessage::TOPICS))],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'scholar_id.required' => 'Choose the scholarship whose provider should correct it.',
            'scholar_id.in' => 'Choose one of your own scholarships.',
            'topic.required' => 'Choose what needs correcting.',
            'message.required' => 'Write what is wrong and what it should be.',
            'message.min' => 'Please write at least a short sentence: what is wrong and what it should be.',
        ]);

        $sc = $scholars->firstWhere('id', (int) $data['scholar_id']);
        ScholarMessage::create([
            'scholar_id' => $sc->id,
            'user_id' => $request->user()->id,
            'topic' => $data['topic'],
            'message' => trim($data['message']),
        ]);

        return $this->sent(redirect()->to(route('scholar.profile').'#corrections'), 'Request sent',
            'Your request was sent to '.$sc->scholarship->provider.'. They will reply to '.$request->user()->email.', and their answer will show under "Your requests".');
    }

    /** Settings: change password, forgot password (reset link), sign out. */
    public function settings(Request $request): View
    {
        return view('scholar.settings', ['user' => $request->user()]);
    }

    /** "Forgot password": e-mails a reset link to the account's own address (Laravel password broker). */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $user = $request->user();
        $status = Password::sendResetLink(['email' => $user->email, 'role' => $user->role, 'status' => 'active']);

        if ($status === Password::RESET_LINK_SENT) {
            return $this->sent(back(), 'Reset link sent', 'We sent a password reset link to '.$user->email.'. It works for 60 minutes.');
        }

        return back()->with('notice', $status === Password::RESET_THROTTLED
            ? 'We sent you a reset link less than a minute ago. Check your inbox (and your spam folder), or try again in a moment.'
            : 'We couldn\'t send your reset link. Please try again later.');
    }

    /** Explore › All scholarships: the catalogue, without matching (scholars have no matches). */
    public function scholarships(): View
    {
        $scholarships = ScholarshipCatalogController::activeScholarships();

        return view('scholar.scholarships', [
            'sections' => ScholarshipCatalogController::sections($scholarships),
            'total' => $scholarships->count(),
        ]);
    }

    public function help(): View
    {
        return view('scholar.help', ['topics' => self::helpTopics()]);
    }

    /**
     * What a scholar can write to the BAKR team about. "Scholarship info needs updating" is left out
     * (owner, 2026-11-20): corrections go to the provider, from Profile › Request a correction.
     */
    public static function helpTopics(): array
    {
        return array_diff_key(ContactMessage::TOPICS, ['info' => true]);
    }

    public function message(Request $request, ContactMessageService $messages): RedirectResponse
    {
        $data = $request->validate([
            'topic' => ['required', Rule::in(array_keys(self::helpTopics()))],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'message.min' => 'Please write at least a short sentence so we can help.',
        ]);

        // Name and e-mail come from the signed-in account, never from the form.
        $messages->record($data + [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ]);

        return $this->sent(redirect()->route('scholar.help'), 'Message sent', 'The BAKR team will reply to '.$request->user()->email.'.');
    }

    /** @return Collection<int, Scholar> the signed-in scholar's scholarships, with what the pages show */
    private function scholars(Request $request): Collection
    {
        return Scholar::query()->where('user_id', $request->user()->id)
            ->with([
                'scholarship',
                'requirements' => fn ($q) => $q->orderByDesc('term')->orderBy('name'),
                'disbursements' => fn ($q) => $q->orderByDesc('term')->orderBy('period_month'),
            ])
            ->orderBy('id')->get();
    }

    /** This semester's requirement counts over the scholarships whose provider collects them in BAKR. */
    private function requirementCounts(Collection $scholars, string $term): array
    {
        $rows = $scholars->filter(fn ($sc) => $sc->scholarship->offers('requirements'))
            ->flatMap(fn ($sc) => $sc->requirements->where('term', $term))
            ->filter->isOwed();

        return [
            'total' => $rows->count(),
            'approved' => $rows->where('status', 'approved')->count(),
            'submitted' => $rows->where('status', 'submitted')->count(),
            'missing' => $rows->where('status', 'missing')->count(),
            'needs_revision' => $rows->where('status', 'needs_revision')->count(),
        ];
    }
}

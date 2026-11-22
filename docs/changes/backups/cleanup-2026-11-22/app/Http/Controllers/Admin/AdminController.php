<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReply;
use App\Matching\ProgramCatalog;
use App\Models\ContactMessage;
use App\Models\Scholar;
use App\Models\ScholarRequirement;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\ScholarshipDeadline;
use App\Models\ScholarshipMatch;
use App\Models\StipendDisbursement;
use App\Models\User;
use App\Services\Provider\ScholarAccounts;
use App\Support\AcademicTerm;
use App\Support\Spreadsheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Admin portal (role "admin"). Kept simple on purpose (2nd-year BSIT project):
 *   Overview      totals + "needs attention" + system status
 *   Analytics     matching results, scholars (per scholarship / campus), student sign-ups, provider follow-up: counts only
 *   Accounts      student / scholar / provider accounts: search, deactivate / reactivate, send reset link
 *   Scholarships  read-only health check (providers edit their own scholarships)
 *   Messages      Contact and Help messages: reply by e-mail, mark handled, approve shared feedback for the public site
 * Privacy: the admin never sees passwords or a student's matching answers (encrypted, private).
 */
class AdminController extends Controller
{
    /** Account types the admin manages (admins are created from .env only). */
    public const ROLES = ['student' => 'Students', 'scholar' => 'Scholars', 'provider' => 'Providers'];

    /** Sign-up chart periods (query ?signups=): key => button label. */
    public const SIGNUP_RANGES = ['day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year'];

    /** Accounts list order (query ?sort=): key => label. */
    public const NAME_SORTS = ['name_asc' => 'Name A–Z', 'name_desc' => 'Name Z–A'];

    /** Scholars chart groupings (query ?scholars=): key => button label. */
    public const SCHOLAR_GROUPS = ['scholarship' => 'Per scholarship', 'campus' => 'Per campus'];

    /**
     * Overview: headline totals, what needs attention and the system status.
     * "Current scholars" = Active + Inactive (Terminated scholars no longer hold a scholarship).
     */
    public function overview(): View
    {
        $term = AcademicTerm::current();
        $currentApps = ScholarshipApplication::where('is_current', true);

        return view('admin.overview', [
            'term' => $term,
            'totals' => [
                'students' => User::where('role', 'student')->count(),
                'answered' => (clone $currentApps)->count(),
                'scholars' => Scholar::whereIn('status', Scholar::CURRENT)->count(),
                'providers' => User::where('role', 'provider')->where('status', 'active')->count(),
                'released' => StipendDisbursement::where('term', $term)->where('status', 'released')->sum('amount'),
            ],
            'attention' => $this->attention($term),
            'system' => [
                'mail' => ScholarAccounts::mailIsTestMode() ? 'Test mode (e-mails only go to the log)' : 'On ('.config('mail.default').')',
                'ai' => $this->aiStatus(),
                'term' => $term,
            ],
        ]);
    }

    /**
     * Analytics: everything is a count, never a student's answers or name.
     *  - headline: accounts → answered the form → has a match ("fit" = Eligible or Nearly eligible, the same
     *    as the student's own "scholarships fit you so far"), and the current scholars
     *  - matching: students per result for each scholarship + the usual barrier (rule most often failed)
     *  - scholars / sign-ups: the two charts, each switched by its own query string (?scholars=, ?signups=)
     *  - providers: scholars by status, this semester's documents and released stipends
     * Only each student's CURRENT application counts, so changing answers never counts someone twice.
     */
    public function analytics(Request $request): View
    {
        return view('admin.analytics', $this->analyticsData($request));
    }

    /**
     * Analytics as an Excel workbook (.xlsx): the same totals as the page, one sheet per section, numbers as
     * real numbers. Scholars come both per scholarship and per campus; sign-ups follow the page's ?signups=.
     * Counts only, never a student's name or answers (same rule as the page).
     */
    public function exportAnalytics(Request $request): Response|RedirectResponse
    {
        $d = $this->analyticsData($request);
        $labels = ScholarshipMatch::STATUS_LABELS;
        $b = fn (array $cells) => array_map(fn ($c) => ['bold' => $c], $cells);   // header row
        $pct = fn ($n, $of) => $of ? round($n / $of * 100).'%' : '';

        $summary = [
            $b(['BAKR Analytics']),
            ['Semester', $d['term']],
            ['Exported', now()->format('M j, Y g:i A')],
            [],
            $b(['Headline', 'Number', 'Share']),
            ['Student accounts', $d['headline']['students']],
            ['Answered the form', $d['headline']['answered'], $pct($d['headline']['answered'], $d['headline']['students'])],
            ['Have at least one match', $d['headline']['matched'], $pct($d['headline']['matched'], $d['headline']['answered'])],
            ['Current scholars', $d['headline']['scholars']],
        ];

        $matching = [$b(['Scholarship', 'Code', $labels['eligible'], $labels['possible'], $labels['needs_info'], $labels['not_eligible']])];
        foreach ($d['matching']['rows'] as $r) {
            $matching[] = [$r['name'], $r['short'], $r['eligible'], $r['possible'], $r['needs_info'], $r['not_eligible']];
        }
        $matching[] = [];
        $matching[] = $b(['Why students don\'t qualify', 'Requirement missed most often', 'Students']);
        foreach ($d['blockers'] as $x) {
            $matching[] = [$x['scholarship'], $x['rule'], $x['students']];
        }

        $scholarships = Scholarship::orderBy('sort_order')->get()->keyBy('id');
        $scholars = [];
        foreach (array_keys(self::SCHOLAR_GROUPS) as $group) {
            $scholars[] = $b([$group === 'scholarship' ? 'Scholarship' : 'Campus', 'Active', 'Inactive', 'Terminated', 'Total']);
            foreach ($this->scholarRows($scholarships, $group) as $r) {
                $scholars[] = [$r['sub'] ? $r['label'].': '.$r['sub'] : $r['label'], $r['active'], $r['inactive'], $r['terminated'], $r['active'] + $r['inactive'] + $r['terminated']];
            }
            $scholars[] = [];
        }

        $signups = [$b(['New student accounts per '.$d['signupRange'], 'New accounts', 'Accounts in all'])];
        foreach ($d['signups']['rows'] as $r) {
            $signups[] = [$r['full'], $r['value'], $r['running']];
        }

        $providers = [$b(['Provider', 'Active', 'Inactive', 'Terminated', 'Documents approved', 'Documents owed', 'Stipends released', 'Stipends prepared', 'Released (PHP)'])];
        foreach ($d['providers'] as $p) {
            $providers[] = [
                $p['name'], $p['status']['active'], $p['status']['inactive'], $p['status']['terminated'],
                $p['hasReqs'] ? $p['reqApproved'] : 'In person at the DNSC office', $p['hasReqs'] ? $p['reqTotal'] : null,
                $p['hasPay'] ? $p['payReleased'] : 'Handled outside BAKR', $p['hasPay'] ? $p['payTotal'] : null,
                $p['hasPay'] ? $p['amount'] : null,
            ];
        }

        try {
            $bytes = Spreadsheet::workbook([
                'Summary' => $summary,
                'Matching' => $matching,
                'Scholars' => $scholars,
                'Sign-ups' => $signups,
                'Providers' => $providers,
            ]);
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.analytics')->with('notice', $e->getMessage());
        }
        $file = 'bakr-analytics-'.now()->format('Y-m-d').'.xlsx';

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$file.'"',
            'Cache-Control' => 'no-store, private',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /** Everything the Analytics page (and its Excel export) shows. */
    private function analyticsData(Request $request): array
    {
        $term = AcademicTerm::current();
        $group = array_key_exists((string) $request->query('scholars'), self::SCHOLAR_GROUPS) ? $request->query('scholars') : 'scholarship';
        $range = array_key_exists((string) $request->query('signups'), self::SIGNUP_RANGES) ? $request->query('signups') : 'month';
        $scholarships = Scholarship::orderBy('sort_order')->get()->keyBy('id');

        // ---- Matching: only the CURRENT answers of each student, as counts. ----
        $matches = ScholarshipMatch::query()
            ->whereHas('application', fn ($q) => $q->where('is_current', true))
            ->get(['id', 'scholarship_application_id', 'scholarship_id', 'status', 'checks']);

        // Results per scholarship, and the required rule that rules students out most often.
        $byScholarship = [];
        $blockers = [];
        foreach ($matches->groupBy('scholarship_id') as $sid => $rows) {
            $byScholarship[$sid] = $rows->countBy('status')->all();
            $top = $rows->where('status', 'not_eligible')
                ->flatMap(fn (ScholarshipMatch $m) => array_column($m->failedRequired(), 'label'))
                ->countBy()->sortDesc();
            if ($top->isNotEmpty() && isset($scholarships[$sid])) {
                $blockers[] = ['scholarship' => $scholarships[$sid]->shortName(), 'rule' => $top->keys()->first(), 'students' => (int) $top->first()];
            }
        }
        usort($blockers, fn ($a, $b) => $b['students'] <=> $a['students']);

        // The four headline numbers: sign-up → answered → has a match, and the scholars being monitored.
        $headline = [
            'students' => User::where('role', 'student')->count(),
            'answered' => ScholarshipApplication::where('is_current', true)->count(),
            'matched' => $matches->whereIn('status', ['eligible', 'possible'])->unique('scholarship_application_id')->count(),
            'scholars' => Scholar::whereIn('status', Scholar::CURRENT)->count(),
        ];

        // One row per provider: scholars by status, this semester's documents and stipends.
        $providers = [];
        foreach (config('providers.accounts', []) as $p) {
            $ids = $scholarships->whereIn('slug', $p['slugs'] ?? [])->keys();
            $scholarIds = Scholar::whereIn('scholarship_id', $ids)->pluck('id');
            $reqs = ScholarRequirement::where('term', $term)->whereIn('scholar_id', $scholarIds)->owed();
            $pay = StipendDisbursement::where('term', $term)->whereIn('scholar_id', $scholarIds);

            $providers[] = [
                'name' => $p['name'],
                'logo' => $p['logo'] ?? null,
                'status' => collect(array_keys(Scholar::STATUSES))->mapWithKeys(fn ($k) => [$k => Scholar::whereIn('scholarship_id', $ids)->whereStanding($k)->count()])->all(),
                'hasReqs' => in_array('requirements', $p['features'] ?? [], true),
                'reqApproved' => (clone $reqs)->where('status', 'approved')->count(),
                'reqTotal' => (clone $reqs)->count(),
                'hasPay' => in_array('disbursements', $p['features'] ?? [], true),
                'payReleased' => (clone $pay)->where('status', 'released')->count(),
                'payTotal' => (clone $pay)->count(),
                'amount' => (float) (clone $pay)->where('status', 'released')->sum('amount'),
            ];
        }

        return [
            'term' => $term,
            'blockers' => $blockers,
            'headline' => $headline,
            'providers' => $providers,
            'matching' => $this->matchingRows($scholarships, $byScholarship),
            'scholarGroup' => $group,
            'scholarRows' => $this->scholarRows($scholarships, $group),
            'signupRange' => $range,
            'signups' => $this->signups($range),
        ];
    }

    /**
     * Matching table + donut + trivia: students per result for each scholarship.
     * Sorted by eligible students; the trivia names the most and least matched scholarship
     * (only scholarships with at least one answered form count, so a tie at zero is not "least").
     */
    private function matchingRows($scholarships, array $byScholarship): array
    {
        $rows = $scholarships->map(function (Scholarship $s, $id) use ($byScholarship) {
            $c = $byScholarship[$id] ?? [];

            return [
                'name' => $s->name,
                'short' => $s->shortName(),
                'eligible' => (int) ($c['eligible'] ?? 0),
                'possible' => (int) ($c['possible'] ?? 0),
                'needs_info' => (int) ($c['needs_info'] ?? 0),
                'not_eligible' => (int) ($c['not_eligible'] ?? 0),
            ];
        })->values()->sortByDesc('eligible')->values();

        // "Matched" = Eligible (the owner's wording: eligible matches).
        $total = $rows->sum('eligible');
        $answered = $rows->filter(fn ($r) => $r['eligible'] + $r['possible'] + $r['needs_info'] + $r['not_eligible'] > 0);
        $most = $total > 0 ? $rows->first() : null;
        $least = $answered->isNotEmpty() ? $answered->sortBy('eligible')->first() : null;

        // Donut: the top five, the rest together (a donut with 14 slices cannot be read).
        $top = $rows->where('eligible', '>', 0)->take(5)->values();
        $rest = $rows->where('eligible', '>', 0)->slice(5)->sum('eligible');
        $segments = $top->map(fn ($r, $i) => ['label' => $r['short'], 'value' => $r['eligible'], 'tone' => 'cat'.($i + 1)])->all();
        if ($rest > 0) {
            $segments[] = ['label' => 'Other scholarships', 'value' => (int) $rest, 'tone' => 'cat6'];
        }

        return ['rows' => $rows->all(), 'total' => (int) $total, 'segments' => $segments, 'most' => $most, 'least' => $least];
    }

    /**
     * Scholars per scholarship or per campus, split Active / Inactive / Terminated.
     * The campus is the one the provider recorded when adding the scholar (scholars.campus). Scholars added
     * before campuses were recorded fall back to the only campus that offers their program; when several
     * campuses offer it they are counted under "Campus not set" until their provider picks one.
     */
    private function scholarRows($scholarships, string $group): array
    {
        $scholars = Scholar::query()->withStanding()->get(['id', 'scholarship_id', 'program', 'campus', 'status']);

        $byCampus = ProgramCatalog::byCampus();
        $campusOf = [];
        foreach ($byCampus as $campus => $programs) {
            foreach ($programs as $program) {
                $campusOf[$program][] = $campus;
            }
        }
        $names = $scholarships->mapWithKeys(fn (Scholarship $s) => [$s->shortName() => $s->name]);
        $label = function (Scholar $s) use ($group, $scholarships, $campusOf): string {
            if ($group === 'scholarship') {
                return $scholarships->get($s->scholarship_id)?->shortName() ?? 'Unknown';
            }
            if ($s->campus) {
                return $s->campus;
            }
            $campuses = $campusOf[$s->program] ?? [];

            return count($campuses) === 1 ? $campuses[0] : 'Campus not set';
        };

        $rows = [];
        foreach ($scholars->groupBy($label) as $name => $list) {
            $counts = $list->countBy(fn (Scholar $s) => $s->standing());
            $rows[] = [
                'label' => $name,
                'sub' => $group === 'scholarship' ? $names->get($name) : null,
                'active' => (int) ($counts['active'] ?? 0),
                'inactive' => (int) ($counts['inactive'] ?? 0),
                'terminated' => (int) ($counts['terminated'] ?? 0),
            ];
        }

        // Every scholarship / campus is listed, even with nobody yet, so the table is the full roster of places.
        $known = $group === 'scholarship'
            ? $scholarships->map->shortName()->values()->all()
            : array_keys($byCampus);
        $have = array_column($rows, 'label');
        foreach ($known as $name) {
            if (! in_array($name, $have, true)) {
                $rows[] = ['label' => $name, 'sub' => $names->get($name), 'active' => 0, 'inactive' => 0, 'terminated' => 0];
            }
        }

        $total = fn (array $r) => $r['active'] + $r['inactive'] + $r['terminated'];
        usort($rows, fn ($a, $b) => $total($b) <=> $total($a) ?: strcmp($a['label'], $b['label']));

        return $rows;
    }

    /**
     * New student (matching) accounts per day / week / month / year, zero-filled, oldest first.
     * Last 30 days, 12 weeks (Monday start), 12 months, 5 years. Counts only: no names.
     */
    private function signups(string $range): array
    {
        [$count, $step, $format, $key] = match ($range) {
            'day' => [30, 'subDay', 'M j', 'Y-m-d'],
            'week' => [12, 'subWeek', 'M j', 'o-W'],
            'month' => [12, 'subMonth', 'M Y', 'Y-m'],
            'year' => [5, 'subYear', 'Y', 'Y'],
        };

        // Align the newest bucket to the start of its period, then walk back.
        $unit = ['day' => 'startOfDay', 'week' => 'startOfWeek', 'month' => 'startOfMonth', 'year' => 'startOfYear'][$range];
        $cursor = now()->{$unit}();
        $buckets = [];
        for ($i = 0; $i < $count; $i++) {
            $buckets[$cursor->format($key)] = ['label' => $cursor->format($format), 'full' => $range === 'week' ? 'Week of '.$cursor->format('M j, Y') : $cursor->format($format), 'value' => 0];
            $cursor = $cursor->copy()->{$step}()->{$unit}();
        }
        $buckets = array_reverse($buckets, true);

        $earliest = array_key_first($buckets);
        $before = 0;
        foreach (User::where('role', 'student')->pluck('created_at') as $created) {
            if (! $created) {
                continue;
            }
            $k = $created->format($key);
            if (isset($buckets[$k])) {
                $buckets[$k]['value']++;
            } elseif ($k < $earliest) {
                $before++;   // older than the chart: counted in the running total only
            }
        }

        $running = $before;
        foreach ($buckets as $k => $b) {
            $running += $b['value'];
            $buckets[$k]['running'] = $running;
        }

        return ['rows' => array_values($buckets), 'total' => array_sum(array_column($buckets, 'value')), 'accounts' => $running];
    }

    /**
     * Accounts of one role (?role=), searchable by name or e-mail (?q=), sorted by name (?sort=name_asc|name_desc),
     * 25 per page. The Scholars tab also filters by scholarship (?scholarship=<id>) and campus (?campus=<official name>):
     * the campus the provider recorded, or for older rows without one, the only campus that offers their program
     * (the same rule as Scholar::campusLabel()). Passwords are never shown.
     */
    public function accounts(Request $request): View
    {
        $role = array_key_exists((string) $request->query('role'), self::ROLES) ? $request->query('role') : 'student';
        $q = trim((string) $request->query('q'));
        $sort = array_key_exists((string) $request->query('sort'), self::NAME_SORTS) ? $request->query('sort') : 'name_asc';

        $scholarships = $role === 'scholar' ? Scholarship::orderBy('sort_order')->get(['id', 'slug', 'name']) : collect();
        $campuses = $role === 'scholar' ? array_keys(ProgramCatalog::byCampus()) : [];
        $scholarshipId = $scholarships->firstWhere('id', (int) $request->query('scholarship'))?->id;
        $campus = in_array($request->query('campus'), $campuses, true) ? $request->query('campus') : null;

        $users = User::where('role', $role)
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->when($scholarshipId || $campus, fn ($query) => $query->whereHas('scholars', function ($s) use ($scholarshipId, $campus) {
                $s->when($scholarshipId, fn ($w) => $w->where('scholarship_id', $scholarshipId))
                    ->when($campus, fn ($w) => $w->where(fn ($c) => $c->where('campus', $campus)
                        ->orWhere(fn ($old) => $old->where(fn ($n) => $n->whereNull('campus')->orWhere('campus', ''))
                            ->whereIn('program', $this->programsOnlyAt($campus)))));
            }))
            ->when($role === 'scholar', fn ($query) => $query->with('scholars.scholarship'))
            ->orderBy('name', $sort === 'name_desc' ? 'desc' : 'asc')
            ->orderBy('id')
            ->paginate(25)->withQueryString();

        return view('admin.accounts', [
            'role' => $role,
            'q' => $q,
            'sort' => $sort,
            'scholarships' => $scholarships,
            'campuses' => $campuses,
            'scholarshipId' => $scholarshipId,
            'campus' => $campus,
            'users' => $users,
            'counts' => User::whereIn('role', array_keys(self::ROLES))->selectRaw('role, COUNT(*) as n')->groupBy('role')->pluck('n', 'role'),
        ]);
    }

    /** Programs offered at this campus and no other one (a scholar without a recorded campus belongs there). */
    private function programsOnlyAt(string $campus): array
    {
        $byCampus = ProgramCatalog::byCampus();
        $elsewhere = array_merge([], ...array_values(array_diff_key($byCampus, [$campus => true])));

        return array_values(array_diff($byCampus[$campus] ?? [], $elsewhere)) ?: ['__none__'];
    }

    /** Deactivate (cannot sign in) or reactivate a student, scholar or provider account. Nothing is deleted. */
    public function updateStatus(Request $request, int $user): RedirectResponse
    {
        $account = $this->managed($user);
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'inactive'])]]);

        $account->forceFill(['status' => $data['status']])->save();
        if ($data['status'] !== 'active') {
            $account->endSessions();   // signed out at once, not only at their next portal request
        }

        return back()->with('status', $account->name.' is now '.($data['status'] === 'active' ? 'active and can sign in.' : 'deactivated and cannot sign in.'));
    }

    /** E-mails a password-reset link to an active account (the admin never sees or sets passwords). */
    public function sendReset(int $user): RedirectResponse
    {
        $account = $this->managed($user);
        if ($account->status !== 'active') {
            return back()->with('notice', 'Reactivate '.$account->name.' first: reset links are only sent to active accounts.');
        }

        $status = Password::sendResetLink(['email' => $account->email, 'role' => $account->role, 'status' => 'active']);

        return $status === Password::RESET_LINK_SENT
            ? $this->sent(back(), 'Reset link sent', 'A password reset link was e-mailed to '.$account->email.'. It works for 60 minutes.')
            : back()->with('notice', 'The reset link could not be sent right now (one may have been sent less than a minute ago).');
    }

    /** Read-only scholarship health check: call status, last verified date, scholars and recent edits. */
    public function scholarships(): View
    {
        return view('admin.scholarships', [
            'scholarships' => Scholarship::orderBy('sort_order')->withCount(['scholars', 'revisions'])->with(['revisions' => fn ($q) => $q->latest()->limit(1)])->get(),
        ]);
    }

    /** Contact and Help messages, newest first: the open ones (default) or all (?show=all). */
    public function messages(Request $request): View
    {
        $show = $request->query('show') === 'all' ? 'all' : 'open';

        return view('admin.messages', [
            'show' => $show,
            'messages' => ContactMessage::query()->when($show === 'open', fn ($q) => $q->whereNull('handled_at'))->latest()->paginate(20)->withQueryString(),
            'openCount' => ContactMessage::whereNull('handled_at')->count(),
        ]);
    }

    /**
     * Answers a message by e-mail from inside BAKR (a mailto: link only works when the admin's computer has
     * a mail program set up). The reply is sent to the address the sender gave, kept on the message, and the
     * message is marked handled. If the e-mail cannot be sent nothing is saved and the text stays in the form.
     */
    public function replyMessage(Request $request, int $message): RedirectResponse
    {
        $m = ContactMessage::findOrFail($message);
        $data = $request->validate(['reply' => ['required', 'string', 'min:10', 'max:4000']], [
            'reply.required' => 'Write your reply first.',
            'reply.min' => 'Write at least a short sentence so the reply makes sense.',
        ]);

        try {
            Mail::to($m->email)->send(new ContactMessageReply($m, trim($data['reply'])));
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('reply_for', $m->id)
                ->with('notice', 'The reply could not be sent to '.$m->email.'. Nothing was saved: check the mail settings (docs/CONTACT-SETUP.md) and try again.');
        }

        $m->forceFill(['reply' => trim($data['reply']), 'replied_at' => now(), 'handled_at' => $m->handled_at ?? now()])->save();

        if (ScholarAccounts::mailIsTestMode()) {
            return back()->with('notice', 'E-mail is in test mode (MAIL_MAILER=log): the reply to '.$m->email.' was only written to the server log, not delivered. It is saved here and the message is marked handled.');
        }

        return $this->sent(back(), 'Reply sent', 'Your reply was e-mailed to '.$m->email.' and the message is marked handled.');
    }

    /** handled / reopen, and approve / hide shared feedback (only if the sender agreed to share it). */
    public function updateMessage(Request $request, int $message): RedirectResponse
    {
        $m = ContactMessage::findOrFail($message);
        $action = $request->validate(['action' => ['required', Rule::in(['handled', 'reopen', 'approve', 'hide'])]])['action'];

        if (in_array($action, ['approve', 'hide'], true) && ! ($m->topic === 'feedback' && $m->share_publicly)) {
            return back()->with('notice', 'Only feedback the sender agreed to share can be shown publicly.');
        }

        match ($action) {
            'handled' => $m->forceFill(['handled_at' => now()])->save(),
            'reopen' => $m->forceFill(['handled_at' => null])->save(),
            'approve' => $m->forceFill(['approved_at' => now()])->save(),
            'hide' => $m->forceFill(['approved_at' => null])->save(),
        };

        return back()->with('status', match ($action) {
            'handled' => 'Marked as handled.',
            'reopen' => 'Moved back to open messages.',
            'approve' => 'Approved: this feedback can now appear on the public site.',
            'hide' => 'Hidden from the public site.',
        });
    }

    /** A student, scholar or provider account (never an admin, never yourself). */
    private function managed(int $id): User
    {
        $account = User::whereKey($id)->whereIn('role', array_keys(self::ROLES))->firstOrFail();
        abort_if($account->id === auth()->id(), 403);

        return $account;
    }

    /** What needs someone's attention: [tone, icon, text, link]. */
    private function attention(string $term): array
    {
        $items = [];

        $notSent = Scholar::whereNull('credentials_sent_at')->whereHas('user', fn ($q) => $q->where('must_change_password', true))->count();
        if ($notSent) {
            $items[] = ['warn', 'bi-envelope-exclamation', "{$notSent} scholar ".str('login')->plural($notSent).' not e-mailed yet', 'Their provider can press "Resend login details".', route('admin.accounts', ['role' => 'scholar'])];
        }

        $slow = ScholarRequirement::where('status', 'submitted')->where('submitted_at', '<', now()->subDays(7))->count();
        if ($slow) {
            $items[] = ['warn', 'bi-hourglass-split', "{$slow} ".str('upload')->plural($slow).' waiting for review for more than 7 days', 'Providers review them on their Requirements page.', route('admin.analytics').'#monitoring'];
        }

        foreach (config('providers.accounts', []) as $p) {
            if (! in_array('requirements', $p['features'] ?? [], true)) {
                continue;
            }
            $ids = Scholarship::whereIn('slug', $p['slugs'] ?? [])->pluck('id');
            $hasScholars = Scholar::whereIn('scholarship_id', $ids)->whereIn('status', Scholar::GOOD_STANDING)->exists();
            // Each scholarship has its own deadline per semester (owner, 2026-11-21): none set yet = nothing opened.
            $opened = ScholarshipDeadline::where('term', $term)->whereIn('scholarship_id', $ids)->exists();
            if ($hasScholars && ! $opened) {
                $items[] = ['info', 'bi-calendar-x', "{$p['name']} has not set a {$term} deadline", 'Its scholars cannot upload maintaining documents yet, and stipends stay locked.', route('admin.analytics').'#monitoring'];
            }
        }

        $open = ContactMessage::whereNull('handled_at')->count();
        if ($open) {
            $items[] = ['info', 'bi-chat-left-text', "{$open} open ".str('message')->plural($open), 'From the Contact page and the portal Help pages.', route('admin.messages')];
        }

        if (ScholarAccounts::mailIsTestMode()) {
            $items[] = ['danger', 'bi-envelope-slash', 'E-mail is in test mode', 'MAIL_MAILER=log: logins and reset links are only written to the log. See docs/CONTACT-SETUP.md.', route('admin.overview').'#system'];
        }

        return $items;
    }

    /** "On (Gemini)" or "Off": whether the AI matcher has an API key for the chosen provider. */
    private function aiStatus(): string
    {
        $provider = config('bakr_ai.provider');
        $key = $provider === 'claude' ? config('bakr_ai.key') : config('bakr_ai.gemini.key');

        return $key ? 'On ('.ucfirst((string) $provider).')' : 'Off (built-in explanations)';
    }
}

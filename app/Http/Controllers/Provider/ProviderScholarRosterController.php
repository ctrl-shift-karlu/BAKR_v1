<?php

namespace App\Http\Controllers\Provider;

use App\Matching\ProgramCatalog;
use App\Models\Scholar;
use App\Services\Provider\ProviderScope;
use App\Services\Provider\ScholarAccounts;
use App\Services\Provider\ScholarRoster;
use App\Support\AcademicTerm;
use App\Support\Spreadsheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Add scholars": put the provider's new, official scholars into BAKR, one by one or from an
 * Excel (.xlsx) / CSV file. The scholarship is always looked up inside ProviderScope; status,
 * grant and stipend are filled in by ScholarRoster from the chosen scholarship. "Scholar since" (the
 * semester they started) is picked on both forms (owner, 2026-11-21).
 */
class ProviderScholarRosterController extends ProviderController
{
    public function create(Request $request): View
    {
        $scope = $this->scope($request);

        return $this->page($scope, 'provider.scholars.create', [
            'scholarships' => $scope->scholarships(),
            'withStipend' => $scope->has('disbursements'),
            'columns' => $this->templateColumns($scope),
            'yearLevels' => Scholar::YEAR_LEVELS,
            'genders' => Scholar::GENDERS,
            'campusPrograms' => ProgramCatalog::byCampus(),
            'maxRows' => Spreadsheet::MAX_ROWS,
            'term' => AcademicTerm::current(),
            'startTerms' => AcademicTerm::startOptions(),
            'report' => session('import_report'),
        ]);
    }

    public function store(Request $request, ScholarRoster $roster): RedirectResponse
    {
        $scope = $this->scope($request);

        $data = $request->validate(
            ['scholarship_id' => ['required', 'integer'], 'started_term' => ScholarRoster::startedTermRule()] + $roster->rules(),
            $roster->messages() + self::startedTermMessages(),
            $roster->attributes() + ['scholarship_id' => 'scholarship', 'started_term' => 'Scholar since'],
        );
        $scholarship = $scope->scholarship((int) $data['scholarship_id']);

        // The program must be one this campus offers.
        if ($mismatch = ScholarRoster::campusProgramError($data['campus'], $data['program'])) {
            return back()->withInput()->withErrors(['program' => $mismatch]);
        }

        [$grantKey, $grantError] = $roster->resolveGrant($scholarship, $data['grant_type'] ?? null);
        if ($grantError) {
            return back()->withInput()->withErrors(['grant_type' => $grantError]);
        }
        if ($roster->duplicateIn($scholarship, $data)) {
            return back()->withInput()->withErrors([
                'student_number' => 'A scholar of '.$scholarship->shortName().' already has this student number or e-mail.',
            ]);
        }
        // The Scholar ID is not used by another scholar of this scholarship.
        if ($codeError = ScholarRoster::scholarCodeError($scholarship, $data['scholar_code'])) {
            return back()->withInput()->withErrors(['scholar_code' => $codeError]);
        }

        $result = $roster->add($scholarship, $data, $grantKey, $scope->has('disbursements'), $request->user());
        if ($result['error']) {
            return back()->withInput()->withErrors(['email' => $result['error']]);
        }
        $scholar = $result['scholar'];
        $email = $scholar->user->email;

        $redirect = redirect()->route('provider.scholars.show', $scholar->id);

        return match (true) {
            ! $result['emailed'] && ScholarAccounts::mailIsTestMode() => $redirect->with('notice', $scholar->displayName().' was added and their account is ready, but e-mail is in test mode (MAIL_MAILER=log), so the login e-mail was only written to the server log. Set up e-mail, then press "Resend login details".'),
            ! $result['emailed'] => $redirect->with('notice', $scholar->displayName().' was added and their account is ready, but the login e-mail to '.$email.' could not be sent. Press "Resend login details" to try again.'),
            $result['new_account'] => $redirect->with('status', $scholar->displayName().' was added to '.$scholarship->shortName().'. Their scholar account was created and a temporary password was e-mailed to '.$email.'.'),
            $result['converted'] => $redirect->with('status', $scholar->displayName().' was added to '.$scholarship->shortName().'. '.$email.' was a BAKR student account; it is now a scholar account (same password), and they were told to sign in at the Scholar login.'),
            default => $redirect->with('status', $scholar->displayName().' was added to '.$scholarship->shortName().'. '.$email.' already had a BAKR scholar account, so it was linked and they were notified.'),
        };
    }

    /** New temporary password + login e-mail, while the scholar has not yet changed the first one. */
    public function resendLogin(Request $request, int $scholar, ScholarAccounts $accounts): RedirectResponse
    {
        $s = $this->scope($request)->scholar($scholar)->load('user');
        if (! $s->canResendLogin()) {
            return back()->with('notice', $s->displayName().' has already set their own password. They can use "Forgot password?" on the Scholar login page.');
        }

        return match (true) {
            $accounts->resend($s) => $this->sent(back(), 'Login details sent', 'New login details were e-mailed to '.$s->user->email.'. The earlier temporary password no longer works.'),
            ScholarAccounts::mailIsTestMode() => back()->with('notice', 'E-mail is in test mode (MAIL_MAILER=log): the new login details were only written to the server log, not sent.'),
            default => back()->with('notice', 'The e-mail to '.$s->user->email.' could not be sent. Check the mail settings and try again.'),
        };
    }

    public function import(Request $request, ScholarRoster $roster): RedirectResponse
    {
        $scope = $this->scope($request);
        $data = $request->validate([
            'scholarship_id' => ['required', 'integer'],
            'started_term' => ScholarRoster::startedTermRule(),
            'file' => ['required', 'file', 'max:5120', 'extensions:xlsx,xls,csv,txt'],
        ], [
            'file.extensions' => 'Upload an Excel file (.xlsx) or a CSV file.',
            'file.max' => 'The file must be 5 MB or smaller.',
        ] + self::startedTermMessages(), ['scholarship_id' => 'scholarship', 'started_term' => 'Scholar since']);
        $scholarship = $scope->scholarship((int) $data['scholarship_id']);
        $file = $request->file('file');

        try {
            $rows = Spreadsheet::read($file->getRealPath(), $file->getClientOriginalExtension());
        } catch (RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $result = $roster->import($scholarship, $rows, $scope->has('disbursements'), $request->user(), $data['started_term'] ?? null);
        if ($result['error']) {
            return back()->withErrors(['file' => $result['error']]);
        }

        $added = $result['added'];
        $skipped = count($result['skipped']);
        $notEmailed = count($result['not_emailed']);
        $accounts = $result['new_accounts'] ? " {$result['new_accounts']} new ".str('account')->plural($result['new_accounts']).' created; login details e-mailed.' : '';

        return redirect()->route('provider.scholars.create')
            ->with($added && ! $notEmailed ? 'status' : 'notice', match (true) {
                $added && ! $skipped => "Imported {$added} ".str('scholar')->plural($added)." into {$scholarship->shortName()}.".$accounts,
                $added > 0 => "Imported {$added} ".str('scholar')->plural($added)." into {$scholarship->shortName()}.{$accounts} {$skipped} ".str('row')->plural($skipped).' skipped (see below).',
                default => 'No scholars were imported. See the skipped rows below.',
            }.($notEmailed ? " {$notEmailed} login ".str('e-mail')->plural($notEmailed).' could not be sent (see below).' : ''))
            ->with('import_report', [
                'scholarship' => $scholarship->shortName(), 'added' => $added, 'new_accounts' => $result['new_accounts'],
                'skipped' => array_slice($result['skipped'], 0, 200), 'more' => max(0, $skipped - 200),
                'not_emailed' => array_slice($result['not_emailed'], 0, 200),
            ]);
    }

    /** Empty .xlsx with the expected column titles. */
    public function template(Request $request): Response
    {
        try {
            $bytes = Spreadsheet::xlsx([$this->templateColumns($this->scope($request))]);
        } catch (RuntimeException $e) {
            return redirect()->route('provider.scholars.create')->withFragment('import')->withErrors(['file' => $e->getMessage()]);
        }

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="bakr-scholar-roster-template.xlsx"',
            'Cache-Control' => 'no-store',
        ]);
    }

    public static function startedTermMessages(): array
    {
        return [
            'started_term.required' => 'Choose the semester the scholar started.',
            'started_term.in' => 'Choose the semester the scholar started from the list.',
        ];
    }

    /** "Grant type" only when one of this provider's scholarships has more than one type (e.g. CMSP Full / Half). */
    private function templateColumns(ProviderScope $scope): array
    {
        $hasTypes = $scope->scholarships()->contains(fn ($s) => $s->hasGrantTypes());

        return array_values(array_filter(ScholarRoster::TEMPLATE_COLUMNS, fn ($c) => $hasTypes || $c !== 'Grant type'));
    }
}

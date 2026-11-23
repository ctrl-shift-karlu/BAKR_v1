<?php

namespace App\Services\Provider;

use App\Matching\ProgramCatalog;
use App\Models\Scholar;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\AcademicTerm;
use App\Support\PersonName;
use App\Support\Spreadsheet;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * The provider's official scholar roster: adds new scholars (one at a time, or many rows from a
 * spreadsheet). Adding a scholar is the moment their BAKR login is created (ScholarAccounts):
 * the e-mail typed here becomes their sign-in e-mail and a temporary password is e-mailed to
 * them, to be changed at first sign-in. The login is a SCHOLAR account (/scholarLogin), separate
 * from student/matching accounts; an e-mail that already has a student account is converted.
 *
 * What the provider types: the scholar's own details (Scholar ID, name, student number, e-mail, campus,
 * program, year level, birthday, gender (Male / Female), mobile number, address as street, city / municipality and
 * province (owner, 2026-11-23); all required, owner
 * 2026-11-20) and, only for scholarships with more than one published type, the grant type (e.g. CMSP
 * Full / Half). The Scholar ID is typed freely (no format) and is unique within the scholarship.
 * Everything that follows from the scholarship is filled in here:
 *   status        Active (a new scholar)
 *   started_term  the semester picked on the form ("Scholar since"; AcademicTerm::startOptions(), default the
 *                 current one): no checklist and no stipend for a semester before it
 *   grant_type    the only option, or the chosen one
 *   stipend       the published amount of that option (Scholarship::grantOptions), when the
 *                 provider uses Disbursements and the scholarship publishes a fixed amount
 * The provider can still change status, grant type and stipend later on the scholar's profile.
 *
 * A row is a duplicate when the scholarship already has a scholar with the same student number
 * or the same e-mail; duplicates are skipped, never merged.
 */
class ScholarRoster
{
    /** Spreadsheet header (lower-cased, punctuation collapsed) → field. Other columns are ignored. */
    public const HEADERS = [
        // The name in parts (owner, 2026-11-23): First name + Surname required, Middle name + Suffix optional.
        'first name' => 'first_name', 'firstname' => 'first_name', 'given name' => 'first_name', 'given names' => 'first_name',
        'middle name' => 'middle_name', 'middlename' => 'middle_name', 'middle initial' => 'middle_name', 'mi' => 'middle_name', 'm i' => 'middle_name',
        'surname' => 'last_name', 'last name' => 'last_name', 'lastname' => 'last_name', 'family name' => 'last_name',
        'suffix' => 'suffix', 'name suffix' => 'suffix', 'extension' => 'suffix', 'name extension' => 'suffix', 'ext' => 'suffix', 'extension name' => 'suffix',
        'student number' => 'student_number', 'student no' => 'student_number', 'student id' => 'student_number', 'id number' => 'student_number',
        'email' => 'email', 'e mail' => 'email', 'email address' => 'email',
        'program' => 'program', 'course' => 'program', 'degree program' => 'program',
        'campus' => 'campus', 'campus name' => 'campus', 'dnsc campus' => 'campus',
        'year level' => 'year_level', 'year' => 'year_level',
        'grant type' => 'grant_type', 'grant' => 'grant_type', 'scholarship type' => 'grant_type', 'type' => 'grant_type', 'full half' => 'grant_type', 'category' => 'grant_type',
        'scholar id' => 'scholar_code', 'scholar id number' => 'scholar_code', 'scholar id no' => 'scholar_code', 'scholar no' => 'scholar_code', 'scholar number' => 'scholar_code', 'scholar code' => 'scholar_code',
        'mobile' => 'mobile', 'mobile number' => 'mobile', 'mobile no' => 'mobile', 'cellphone' => 'mobile', 'cellphone number' => 'mobile', 'cell number' => 'mobile', 'phone' => 'mobile', 'phone number' => 'mobile', 'contact number' => 'mobile', 'contact no' => 'mobile',
        // Address in parts (owner, 2026-11-23). A single "Address" column is no longer enough.
        'street' => 'address_street', 'street address' => 'address_street', 'house no street' => 'address_street', 'house no street barangay' => 'address_street', 'street barangay' => 'address_street', 'barangay street' => 'address_street', 'address street' => 'address_street', 'street purok barangay' => 'address_street',
        'city' => 'address_city', 'municipality' => 'address_city', 'city municipality' => 'address_city', 'city or municipality' => 'address_city', 'town' => 'address_city', 'city town' => 'address_city', 'address city' => 'address_city',
        'province' => 'address_province', 'address province' => 'address_province',
        'birthday' => 'birthdate', 'birthdate' => 'birthdate', 'birth date' => 'birthdate', 'date of birth' => 'birthdate', 'dob' => 'birthdate',
        'gender' => 'gender', 'sex' => 'gender',
    ];

    /** Columns of the downloadable template, in order ('Grant type' only when one of the provider's scholarships has types). */
    public const TEMPLATE_COLUMNS = ['Scholar ID', 'First name', 'Middle name', 'Surname', 'Suffix', 'Student number', 'Email', 'Mobile number', 'Street', 'City / Municipality', 'Province', 'Birthday', 'Gender', 'Campus', 'Program', 'Year level', 'Grant type'];

    /** Columns a file must have (Campus may be left out when only one campus offers the program; Grant type only for typed scholarships). */
    private const REQUIRED_COLUMNS = [
        'scholar_code' => 'Scholar ID', 'first_name' => 'First name', 'last_name' => 'Surname', 'student_number' => 'Student number', 'email' => 'Email',
        'mobile' => 'Mobile number', 'address_street' => 'Street', 'address_city' => 'City / Municipality', 'address_province' => 'Province', 'birthdate' => 'Birthday', 'gender' => 'Gender',
        'program' => 'Program', 'year_level' => 'Year level',
    ];

    /**
     * @param  ScholarAccounts  $accounts  creates or links each scholar's login
     * @param  RequirementChecklist  $checklist  gives a new scholar the checklists already open
     */
    public function __construct(private ScholarAccounts $accounts, private RequirementChecklist $checklist) {}

    /**
     * "Scholar since": the semester the scholar started (owner, 2026-11-21), picked on the Add form and the
     * import form (one semester for the whole file; the select starts on the current semester, which is also
     * used when nothing is sent). It decides their first checklist and first stipend. On the profile it is
     * required ($required) and the stored value stays allowed ($keep).
     */
    public static function startedTermRule(?string $keep = null, bool $required = false): array
    {
        return [$required ? 'required' : 'nullable', Rule::in(array_values(array_unique(array_filter([...AcademicTerm::startOptions(), $keep]))))];
    }

    /** Validation rules for one scholar, used by the Add form and for every row of an imported file. */
    public function rules(): array
    {
        return [
            // Typed freely; unique within the chosen scholarship (scholarCodeError, checked afterwards).
            'scholar_code' => ['required', 'string', 'max:'.Scholar::SCHOLAR_CODE_MAX],
            'student_number' => ['required', 'string', 'max:30'],
            // The scholar's sign-in e-mail: their login details are sent here.
            'email' => ['required', 'email:rfc', 'max:190'],
            // The DNSC campus the scholar studies at (so analytics can show scholars per campus), and one of
            // the programs that campus offers (campusProgramError); both stored under their official names.
            'campus' => ['required', 'string', 'max:80', self::campusRule()],
            'program' => ['required', 'string', 'max:150', self::programRule()],
            'year_level' => ['required', Rule::in(array_keys(Scholar::YEAR_LEVELS))],
            'grant_type' => ['nullable', 'string', 'max:60'],
        ] + PersonName::rules() + self::personalRules();
    }

    /**
     * Birthday, gender, mobile number, address: required (owner, 2026-11-20) on the Add form, in files and on the scholar's
     * profile. The address is three fields since 2026-11-23: street (house no., street, purok / barangay), city or
     * municipality, province; saved joined in `address` too (addressFields()).
     */
    public static function personalRules(): array
    {
        return [
            'birthdate' => ['required', 'date_format:Y-m-d', 'after:1900-01-01', 'before:'.now()->subYears(10)->toDateString()],
            'gender' => ['required', Rule::in(array_keys(Scholar::GENDERS))],
            'mobile' => ['required', 'string', 'max:20', self::mobileRule()],
            'address_street' => ['required', 'string', 'max:150'],
            'address_city' => ['required', 'string', 'max:80'],
            'address_province' => ['required', 'string', 'max:80'],
        ];
    }

    /** Messages for personalRules() and the Scholar ID. */
    public static function personalMessages(): array
    {
        return [
            'scholar_code.required' => 'Type the scholar\'s Scholar ID.',
            'birthdate.required' => 'Enter the scholar\'s birthday.',
            'birthdate.date_format' => 'Enter the birthday as a date, for example 2005-03-14.',
            'birthdate.before' => 'Check the birthday: a scholar is at least 10 years old.',
            'birthdate.after' => 'Check the birthday: the year looks wrong.',
            'gender.required' => 'Choose Male or Female.',
            'gender.in' => 'Choose Male or Female.',
            'mobile.required' => 'Enter the scholar\'s mobile number.',
            'address_street.required' => 'Enter the street: house no., street, purok or barangay.',
            'address_city.required' => 'Enter the city or municipality.',
            'address_province.required' => 'Enter the province.',
        ];
    }

    /**
     * The address columns to save from validated input: the three parts (spaces collapsed) and, in `address`,
     * the same joined in one line ("Purok 3, Gredu, Panabo City, Davao del Norte"), so every older reader still works.
     *
     * @return array{address_street: ?string, address_city: ?string, address_province: ?string, address: ?string}
     */
    public static function addressFields(array $data): array
    {
        $clean = fn ($v) => ($v = trim(preg_replace('/\s+/u', ' ', (string) $v))) === '' ? null : $v;
        $parts = ['address_street' => $clean($data['address_street'] ?? null), 'address_city' => $clean($data['address_city'] ?? null), 'address_province' => $clean($data['address_province'] ?? null)];

        return $parts + ['address' => Scholar::joinAddress(...array_values($parts))];
    }

    /** Validation rule: a Philippine mobile number (09XX XXX XXXX, +63 9XX XXX XXXX or 9XXXXXXXXX). */
    public static function mobileRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (is_string($value) && trim($value) !== '' && self::normalizeMobile($value) === null) {
                $fail('Enter a Philippine mobile number, for example 0917 123 4567.');
            }
        };
    }

    /** "+63 917-123-4567", "0917 123 4567", "9171234567" (Excel drops the 0) → "09171234567"; null if not a mobile number. */
    public static function normalizeMobile(?string $value): ?string
    {
        $d = preg_replace('/[\s\-().]/', '', trim((string) $value));
        if (str_starts_with($d, '+63')) {
            $d = '0'.substr($d, 3);
        } elseif (str_starts_with($d, '63') && strlen($d) === 12) {
            $d = '0'.substr($d, 2);
        } elseif (strlen($d) === 10 && str_starts_with($d, '9')) {
            $d = '0'.$d;
        }

        return preg_match('/^09\d{9}$/', $d) ? $d : null;
    }

    /**
     * Why this Scholar ID cannot be used for the scholarship, or null when it can: it must not be empty
     * and not belong to another of its scholars, ignoring capitals ($exceptId = the scholar being edited).
     */
    public static function scholarCodeError(Scholarship $scholarship, ?string $code, ?int $exceptId = null): ?string
    {
        $code = Scholar::normalizeCode($code);
        if ($code === null) {
            return 'Type the scholar\'s Scholar ID.';
        }
        $taken = Scholar::query()->where('scholarship_id', $scholarship->id)->whereRaw('LOWER(scholar_code) = ?', [mb_strtolower($code)])
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))->exists();

        return $taken ? "Scholar ID {$code} already belongs to another scholar of {$scholarship->shortName()}." : null;
    }

    /** Validation rule: the value must name a DNSC program (any case, "BS …" short form allowed). */
    public static function programRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (is_string($value) && trim($value) !== '' && ProgramCatalog::canonicalName($value) === null) {
                $fail('"'.$value.'" is not a DNSC program. Pick one from the list.');
            }
        };
    }

    /** Validation rule: the value must name a DNSC campus ("Samal", "Samal Campus", "Main" … are understood). */
    public static function campusRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) {
            if (is_string($value) && trim($value) !== '' && ProgramCatalog::canonicalCampus($value) === null) {
                $fail('"'.$value.'" is not a DNSC campus. Pick one from the list.');
            }
        };
    }

    /**
     * Null when the program is offered at the campus (or either is missing: the field rules report that),
     * else a message that names where the program IS offered.
     */
    public static function campusProgramError(?string $campus, ?string $program): ?string
    {
        $campusName = ProgramCatalog::canonicalCampus($campus);
        $programName = ProgramCatalog::canonicalName($program);
        if ($campusName === null || $programName === null || ProgramCatalog::offeredAt($campusName, $programName)) {
            return null;
        }
        $where = ProgramCatalog::campusesFor($programName);

        return "{$programName} is not offered at {$campusName}".($where ? ' (it is offered at: '.implode(', ', $where).').' : '.');
    }

    /** Field names used in error messages ("student number" instead of "student_number"). */
    public function attributes(): array
    {
        return PersonName::attributes() + ['student_number' => 'student number', 'year_level' => 'year level', 'grant_type' => 'grant type',
            'scholar_code' => 'Scholar ID', 'birthdate' => 'birthday', 'mobile' => 'mobile number',
            'address_street' => 'street', 'address_city' => 'city / municipality', 'address_province' => 'province'];
    }

    /** Error messages for rules(). */
    public function messages(): array
    {
        return ['campus.required' => 'Choose the campus the scholar studies at.'] + self::personalMessages() + PersonName::messages("the scholar's");
    }

    /**
     * The grant key for this scholarship from what was typed or picked: the key ("half"), the label
     * ("Half scholarship (Half-SSP)") or its start ("Half"), any case.
     *
     * @return array{0: ?string, 1: ?string} [key, error message]
     */
    public function resolveGrant(Scholarship $scholarship, ?string $input): array
    {
        $options = $scholarship->grantOptions();
        if ($options === []) {
            return [null, null];
        }
        if (count($options) === 1) {
            return [array_key_first($options), null];
        }

        $labels = implode(' or ', array_column($options, 'label'));
        $in = Str::lower(trim((string) $input));
        if ($in === '') {
            return [null, "Choose the grant type for {$scholarship->shortName()}: {$labels}."];
        }
        foreach ($options as $key => $g) {
            $label = Str::lower($g['label']);
            if ($in === Str::lower($key) || $in === $label || str_starts_with($label, $in)) {
                return [$key, null];
            }
        }

        return [null, "\"{$input}\" is not a grant type of {$scholarship->shortName()}. Use {$labels}."];
    }

    /**
     * Adds one validated scholar (grant already resolved; duplicates already checked by the caller),
     * creating or linking their account, then e-mails the login details.
     *
     * @param  array  $data  the validated form fields
     * @param  string|null  $grantKey  the resolved grant type (resolveGrant())
     * @param  bool  $withStipend  true when the provider uses Disbursements (the published amount is filled in)
     * @param  User  $by  the provider account adding the scholar
     * @return array{scholar: ?Scholar, error: ?string, emailed: bool, new_account: bool, converted: bool} (converted = an existing student account became a scholar account)
     */
    public function add(Scholarship $scholarship, array $data, ?string $grantKey, bool $withStipend, User $by): array
    {
        $data = $this->normalise($data);

        [$scholar, $password, $error, $converted] = DB::transaction(function () use ($scholarship, $data, $grantKey, $withStipend, $by) {
            $account = $this->accounts->forEmail($data['email'], Arr::only($data, PersonName::FIELDS));
            if ($account['error']) {
                return [null, null, $account['error'], false];
            }

            return [Scholar::create($this->record($scholarship, $data, $grantKey, $withStipend, $by) + ['user_id' => $account['user']->id]), $account['password'], null, $account['converted']];
        });

        if ($error) {
            return ['scholar' => null, 'error' => $error, 'emailed' => false, 'new_account' => false, 'converted' => false];
        }

        // After the commit: an e-mail is never sent for a row that was rolled back.
        $emailed = $this->accounts->send($scholar, $password, $converted);
        // The checklists already open for the semesters since they started (deadline set by the provider).
        $this->checklist->syncScholar($scholar);

        return ['scholar' => $scholar, 'error' => null, 'emailed' => $emailed, 'new_account' => $password !== null, 'converted' => $converted];
    }

    /**
     * Imports spreadsheet rows (first row = headers). All valid, new rows are added in one
     * transaction; invalid rows and duplicates are reported by their row number in the file.
     *
     * Every added scholar gets an account and a login e-mail; failed e-mails are listed in not_emailed
     * (the provider resends from the scholar's profile). Everyone in the file gets the same "Scholar since"
     * ($startedTerm, picked on the import form; default the current semester).
     *
     * @param  list<list<string>>  $rows
     * @return array{added: int, new_accounts: int, skipped: list<string>, not_emailed: list<string>, error: ?string}
     */
    public function import(Scholarship $scholarship, array $rows, bool $withStipend, User $by, ?string $startedTerm = null): array
    {
        $result = ['added' => 0, 'new_accounts' => 0, 'skipped' => [], 'not_emailed' => [], 'error' => null];

        $header = array_shift($rows) ?? [];
        $map = [];
        foreach ($header as $i => $title) {
            $key = self::HEADERS[$this->headerKey($title)] ?? null;
            if ($key && ! in_array($key, $map, true)) {
                $map[$i] = $key;
            }
        }
        $missing = array_diff(array_keys(self::REQUIRED_COLUMNS), $map);
        if ($missing) {
            $result['error'] = 'The first row must be the column titles. Missing: '.implode(', ', array_map(fn ($m) => '"'.self::REQUIRED_COLUMNS[$m].'"', $missing)).'. Download the template to see the expected columns.';

            return $result;
        }
        if ($scholarship->hasGrantTypes() && ! in_array('grant_type', $map, true)) {
            $result['error'] = "{$scholarship->shortName()} has more than one grant type, so the file needs a \"Grant type\" column (".implode(' or ', array_column($scholarship->grantOptions(), 'label')).').';

            return $result;
        }
        if ($rows === []) {
            $result['error'] = 'The file has column titles but no scholars under them.';

            return $result;
        }
        if (count($rows) > Spreadsheet::MAX_ROWS) {
            $result['error'] = 'The file has more than '.Spreadsheet::MAX_ROWS.' rows. Split it into smaller files.';

            return $result;
        }

        $seenNumbers = [];
        $seenEmails = [];
        $seenCodes = [];
        $valid = [];
        foreach ($rows as $n => $cells) {
            $line = $n + 2;   // +1 for the header, +1 because spreadsheets count from 1
            $data = [];
            foreach ($map as $i => $field) {
                $value = trim((string) ($cells[$i] ?? ''));
                $data[$field] = $value === '' ? null : $value;
            }
            if (isset($data['year_level'])) {
                $data['year_level'] = $this->yearKey($data['year_level']);
            }
            if (isset($data['birthdate'])) {
                $data['birthdate'] = self::birthdateValue($data['birthdate']);
            }
            if (isset($data['gender'])) {
                $data['gender'] = self::genderKey($data['gender']);
            }
            if (isset($data['suffix'])) {
                $data['suffix'] = PersonName::normalizeSuffix($data['suffix']);
            }

            // The Campus column is optional in a file: a program only one campus offers tells us the campus.
            if (empty($data['campus']) && ! empty($data['program'])) {
                $data['campus'] = ProgramCatalog::inferCampus($data['program']);
                if (empty($data['campus']) && ($where = ProgramCatalog::campusesFor($data['program']))) {
                    $result['skipped'][] = "Row {$line}: {$data['program']} is offered at more than one campus (".implode(', ', $where).'). Fill in the Campus column.';

                    continue;
                }
            }

            $v = Validator::make($data, $this->rules(), $this->messages(), $this->attributes());
            if ($v->fails()) {
                $result['skipped'][] = "Row {$line}: ".$v->errors()->first();

                continue;
            }
            if ($mismatch = self::campusProgramError($data['campus'], $data['program'])) {
                $result['skipped'][] = "Row {$line}: {$mismatch}";

                continue;
            }
            [$grantKey, $grantError] = $this->resolveGrant($scholarship, $data['grant_type'] ?? null);
            if ($grantError) {
                $result['skipped'][] = "Row {$line}: {$grantError}";

                continue;
            }

            $data = $this->normalise($v->validated());
            $number = Str::lower($data['student_number']);
            $email = Str::lower($data['email']);
            $code = mb_strtolower($data['scholar_code']);
            if (isset($seenNumbers[$number]) || isset($seenEmails[$email])) {
                $result['skipped'][] = "Row {$line}: appears twice in this file.";

                continue;
            }
            if (isset($seenCodes[$code])) {
                $result['skipped'][] = "Row {$line}: Scholar ID {$data['scholar_code']} appears twice in this file.";

                continue;
            }
            if ($this->duplicateIn($scholarship, $data)) {
                $result['skipped'][] = "Row {$line}: {$data['full_name']} is already a scholar of {$scholarship->shortName()}.";

                continue;
            }
            if ($codeError = self::scholarCodeError($scholarship, $data['scholar_code'])) {
                $result['skipped'][] = "Row {$line}: {$codeError}";

                continue;
            }

            // A provider / staff e-mail can never become a scholar login (checked before anything is created).
            if (User::query()->whereRaw('LOWER(email) = ?', [$email])->whereNotIn('role', ScholarAccounts::USABLE_ROLES)->exists()) {
                $result['skipped'][] = "Row {$line}: {$email} belongs to a BAKR provider or staff account.";

                continue;
            }

            $seenNumbers[$number] = true;
            $seenEmails[$email] = true;
            $seenCodes[$code] = true;
            $valid[] = array_merge($data, ['_grant' => $grantKey, 'started_term' => $startedTerm]);
        }

        // Accounts and scholars together: all or nothing. E-mails only after the commit.
        $created = DB::transaction(function () use ($valid, $scholarship, $withStipend, $by, &$result) {
            $out = [];
            foreach ($valid as $data) {
                $account = $this->accounts->forEmail($data['email'], Arr::only($data, PersonName::FIELDS));
                $scholar = Scholar::create($this->record($scholarship, $data, $data['_grant'], $withStipend, $by) + ['user_id' => $account['user']->id]);
                $out[] = [$scholar, $account['password'], $account['converted']];
                $result['added']++;
                $result['new_accounts'] += $account['password'] !== null ? 1 : 0;
            }

            return $out;
        });

        foreach ($created as [$scholar, $password, $converted]) {
            if (! $this->accounts->send($scholar, $password, $converted)) {
                $result['not_emailed'][] = $scholar->displayName().' ('.$scholar->user->email.')';
            }
            $this->checklist->syncScholar($scholar);
        }

        return $result;
    }

    /** Same scholarship, and the same student number or e-mail (case-insensitive), as an existing scholar. */
    public function duplicateIn(Scholarship $scholarship, array $data): bool
    {
        $number = $data['student_number'] ?? null;
        $email = $data['email'] ?? null;
        if (! $number && ! $email) {
            return false;
        }

        return Scholar::query()->where('scholarship_id', $scholarship->id)
            ->where(function ($q) use ($number, $email) {
                if ($number) {
                    $q->orWhereRaw('LOWER(student_number) = ?', [Str::lower(trim($number))]);
                }
                if ($email) {
                    $q->orWhereRaw('LOWER(email) = ?', [Str::lower(trim($email))])
                        ->orWhereHas('user', fn ($u) => $u->whereRaw('LOWER(email) = ?', [Str::lower(trim($email))]));
                }
            })
            ->exists();
    }

    /** The full row: typed details + everything that follows from the scholarship. */
    private function record(Scholarship $scholarship, array $data, ?string $grantKey, bool $withStipend, User $by): array
    {
        $grant = $grantKey ? ($scholarship->grantOptions()[$grantKey] ?? null) : null;

        return [
            'scholarship_id' => $scholarship->id,
            'scholar_code' => $data['scholar_code'],
            'full_name' => $data['full_name'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'],
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'],
            'student_number' => $data['student_number'],
            'email' => $data['email'],
            'birthdate' => $data['birthdate'],
            'gender' => $data['gender'],
            'mobile' => $data['mobile'],
            ...self::addressFields($data),
            'campus' => $data['campus'],
            'program' => $data['program'],
            'year_level' => $data['year_level'],
            'status' => 'active',
            'grant_type' => $grantKey,
            // Only a semester offered on the form; anything else (or nothing) = the current semester.
            'started_term' => in_array($data['started_term'] ?? null, AcademicTerm::startOptions(), true) ? $data['started_term'] : AcademicTerm::current(),
            'stipend_amount' => $withStipend ? ($grant['amount'] ?? null) : null,
            'added_by' => $by->id,
        ];
    }

    /** Trims and collapses spaces; lower-cases the e-mail; Scholar ID as typed; mobile as 09XXXXXXXXX. */
    private function normalise(array $data): array
    {
        $out = [];
        foreach (['scholar_code', 'first_name', 'middle_name', 'last_name', 'suffix', 'student_number', 'email', 'birthdate', 'gender', 'mobile', 'address_street', 'address_city', 'address_province', 'campus', 'program', 'year_level'] as $field) {
            $value = $data[$field] ?? null;
            $value = is_string($value) ? preg_replace('/\s+/u', ' ', trim($value)) : $value;
            $out[$field] = $value === '' ? null : $value;
        }
        $out['started_term'] = is_string($data['started_term'] ?? null) ? $data['started_term'] : null;   // checked in record()
        $out['scholar_code'] = Scholar::normalizeCode($out['scholar_code']);
        $name = PersonName::parts($out);
        $out = array_merge($out, Arr::only($name, PersonName::FIELDS), ['full_name' => $name['full']]);
        $out['email'] = $out['email'] ? Str::lower($out['email']) : null;
        $out['mobile'] = $out['mobile'] ? (self::normalizeMobile($out['mobile']) ?? $out['mobile']) : null;
        $out['program'] = ProgramCatalog::canonicalName($out['program']) ?? $out['program'];
        $out['campus'] = ProgramCatalog::canonicalCampus($out['campus']) ?? $out['campus'];

        return $out;
    }

    /**
     * A birthday cell → "Y-m-d". Understands Excel date cells (a day number such as 38425 = 2005-03-14), 2005-03-14,
     * 03/14/2005 (month first, as in the Philippines) and "March 14, 2005". Anything else is returned
     * as typed, so the row is reported instead of a wrong date being saved.
     */
    public static function birthdateValue(string $value): string
    {
        $v = trim($value);
        // Excel day numbers: 5 digits = 1927–2173 (a bare year such as "2005" is NOT read as a day number).
        if (preg_match('/^\d{5}$/', $v)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $v)->toDateString();
        }
        foreach (['Y-m-d', 'Y/m/d', 'm/d/Y', 'm-d-Y', 'F j, Y', 'M j, Y', 'F j Y', 'M j Y', 'j F Y', 'j M Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!'.$format, $v);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date && ! ($errors && ($errors['warning_count'] || $errors['error_count']))) {
                return $date->format('Y-m-d');
            }
        }

        return $v;
    }

    /** "F", "female", "Male", "M" → a Scholar::GENDERS key (male / female). Anything else is returned as-is (and fails validation). */
    public static function genderKey(string $value): string
    {
        $v = Str::lower(trim($value));
        foreach (Scholar::GENDERS as $key => $label) {
            if ($v === $key || $v === Str::lower($label)) {
                return $key;
            }
        }

        return match ($v) {
            'f', 'woman' => 'female',
            'm', 'man' => 'male',
            default => $value,
        };
    }

    /** "Full Name", "full_name", "E-mail", "Full/Half" → "full name", "e mail", "full half". */
    private function headerKey(string $title): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower($title)));
    }

    /** "2", "2nd year", "Second Year", "II" → "2". Unknown text is returned as-is (and fails validation). */
    private function yearKey(string $value): string
    {
        $v = Str::lower(trim($value));
        if (preg_match('/^([1-5])(st|nd|rd|th)?\b/', $v, $m)) {
            return $m[1];
        }
        $words = ['first' => '1', 'second' => '2', 'third' => '3', 'fourth' => '4', 'fifth' => '5', 'i' => '1', 'ii' => '2', 'iii' => '3', 'iv' => '4', 'v' => '5'];
        $first = strtok($v, ' ');

        return $words[$first] ?? $value;
    }
}

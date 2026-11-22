# 2026-11-21 · "Scholar since" decides documents and stipends; each scholarship's own deadline per semester

Owner's request: every added scholar gets an accurate start semester from the moment they are created, tied to
their documents and stipends; each scholarship has its own, editable maintaining-documents deadline **per
semester**, e-mailed automatically to its own scholars; the "Open / Update checklist" button is removed.

## Data

- New table `scholarship_deadlines` (scholarship_id, term, due_on, set_by; unique scholarship × term), model
  `ScholarshipDeadline`, `Scholarship::deadlines()` / `deadlineFor($term)`.
- Migration `2026_11_21_000003_create_scholarship_deadlines`: backfills one deadline per scholarship × semester
  that already has checklist items (latest due date of its items), and gives every scholar a valid
  `started_term` (the earliest of: their stored value, their checklist / stipend semesters, the semester they
  were added in). **Not run on the local MySQL yet**: back up `bakr`, then `php artisan migrate`.

## Logic

- `AcademicTerm`: `startOptions()` (next semester, current, 8 before), `indexOf()` (compare semesters),
  `upTo()`, `of()`.
- `Scholar::startedBy($term)` + scope `startedBy()`; `releaseBlock()` (kind + text) behind `releaseBlocker()`:
  new reasons `not_started`, `no_deadline` (replaces "requirements have not been opened"), `not_listed`.
- `App\Services\Provider\RequirementChecklist`: `setDeadline()` (save, open / update the semester's items for
  good-standing scholars who had started, move non-approved due dates, notify: everyone when the date is
  new / changed, only newly added scholars when it is the same); `syncScholar()` (after add, import,
  reinstate, "Scholar since" change: every semester with a deadline since they started); `syncScholarship()`
  (a new maintaining document joins current / coming semesters with a deadline). Notifications use
  `ScholarNotifier` kind `deadline` (in BAKR + e-mail).
- Disbursements: Generate and the "not prepared yet" / "no amount" counts skip scholars who had not started;
  each lock reason has its own hint.
- Changing "Scholar since" to a later semester removes only EMPTY earlier items (missing, no file); stipend
  records are never removed (an earlier one stays locked).
- Scholar Overview to-dos now list owed items of every semester (an earlier semester may be opened late).

## Screens

- Requirements: "Deadlines for {semester}" card, one row per scholarship (status, scholars owing, date + Set /
  Save deadline; "No maintaining documents yet" link). `POST provider/requirements/deadline`
  (`provider.requirements.deadline`, throttle 30/min). `provider.requirements.open` removed.
- Add scholars: "Scholar since" select on the single form and the import form (whole file); default current.
- Scholar profile: the Scholarship card is editable for every provider ("Scholar since" + grant type / stipend
  where they apply).
- Text: provider + scholar Help, maintaining-docs hint, scholar Requirements ("starts in…", e-mail note),
  admin "has not set a … deadline", analytics "No documents opened yet". CSS `.pv-deadline*` (replaces
  `.pv-open-form`); `sw.js` → `bakr-v11`.

## Follow-up: Excel import / template "not working"

- Cause 1: XAMPP's PHP 8.5 (`D:\XAMPP_LATEST\php\php.ini`, used by Apache) had `;extension=zip`, so every .xlsx
  read / write failed with `Class "ZipArchive" not found` (the CLI PHP `C:\php84` has zip, so tests passed).
  Turned on (`extension=zip`); **Apache must be restarted** to pick it up.
- Cause 2: the deadlines migration had not run on MySQL (`scholarship_deadlines` missing). Backed up to
  `storage/app/backups/bakr-before-deadlines-2026-11-21.sql`, then migrated (3 deadlines backfilled, all 4
  scholars have "Scholar since").
- Hardening: `Spreadsheet::requireZip()` turns a missing zip extension into a readable message (import, template
  download → error on the Add page; admin Analytics export → notice) instead of a 500.

## Tests

314/314. New `SemesterDeadlineTest` (8). Updated `ProviderOperationsTest`, `ProviderRosterTest`,
`ScholarIdAndDetailsTest`.

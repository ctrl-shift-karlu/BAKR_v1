# 2026-11-17 — Per-provider pages, scholar roster (add / Excel import), empty "My scholarships"

## Owner's decisions
- There is one shared provider dashboard, and its content changes by provider.
- CHED, DOST, Cebuana and Grab have Overview, Applicants, Scholars, Disbursements, Requirements (maintenance requirements), My scholarships and Help.
- DNSC has Overview, Applicants, Scholars, My scholarships and Help. It has **no** Disbursements or Requirements.
- Every provider can **add its existing official scholars** to BAKR's database, one by one or from an **Excel file**.

## 1. Per-provider pages
- `config/providers.php`: a new `features` list per provider (`disbursements`, `requirements`). DNSC has `[]`.
- `ProviderScope::OPTIONAL_FEATURES`, `has()` and `scholarshipHas()`. A provider without a `features` key keeps everything (backwards compatible).
- New middleware `provider.feature:<name>` (`app/Http/Middleware/ProviderFeature.php`, registered in `bootstrap/app.php`). Both route groups return **404** when the feature is off.
- Hidden for DNSC:
  - the sidebar items and their counters
  - the Overview "Stipends" button, the stipend and requirement donuts, the per-card stipend and requirement bars, and "Uploads to review" (replaced by an "Add scholars" card)
  - the Scholars columns for stipends and requirements
  - the scholar profile's requirement and stipend history and its stipend field
  - "Renewal requirements per term" on My scholarships, both when reading and editing. Saving keeps the stored list, because the key is left out of `ScholarshipEditor::apply`.
  - the Help FAQ entries for those pages
- Student side: `Scholarship::offers()`. A DNSC scholar's "My scholarship" shows a short note instead of empty stipend and upload cards, and the upload route returns 404.

## 2. Scholar roster
- Migration `2026_11_17_000001_add_roster_fields_to_scholars`: `user_id` becomes nullable, and `full_name`, `email`, `added_by` and an index on (scholarship_id, student_number) are added. The migration has been run on local `bakr`.
- `Scholar::displayName()`, `contactEmail()`, `initials()` and `isRosterOnly()`. All provider views now use these instead of `->user->name`.
- New page `/provider/scholars/new` (`ProviderScholarRosterController`, `provider/scholars/create.blade.php`), opened from the Scholars page with "Add new scholar" and "Import from Excel":
  - Add one: the scholarship (scoped), full name (required), e-mail, student number, program, year, status, "scholar since", and the stipend (only for providers with Disbursements).
  - Import: .xlsx or CSV (comma or semicolon, BOM handled), first row = titles, up to 2,000 rows, 5 MB. Title aliases work ("Name", "Student ID", "Course"…). Status accepts the key or the label. Valid rows are added in one transaction. Invalid rows and duplicates are listed by row number.
  - A template download (.xlsx) is available. It has no stipend column for DNSC.
- `App\Services\Provider\ScholarRoster` holds the rules shared by add and import. A **duplicate** means the same scholarship plus the same student number or e-mail (case-insensitive). Duplicates are skipped, so importing the same file twice is safe.
- `App\Support\Spreadsheet` is a dependency-free .xlsx reader and writer (ZipArchive + SimpleXML). It does not resolve external entities, size-checks each part before unzipping, and caps rows and columns. Old binary `.xls` gets "Save as .xlsx".
- Approving an applicant whose e-mail is already on that scholarship's roster attaches their account to the roster row instead of creating a second scholar. Roster rows are never linked automatically (there is no e-mail verification yet).

## 3. "My scholarships" had no content
**Cause:** the local `bakr` database's `scholarships` table was **empty**. The view was fine and showed its "No active scholarships" message. `campuses`, `programs` and `scholarship_applications` are also empty, so the local database looks freshly reset.
**Fix:** after checking it still had 0 scholarships and 0 revisions, I ran `php artisan db:seed --class=ScholarshipSeeder`, which loaded 11 scholarships. No provider edits existed to lose.
**Still open:** `campuses` and `programs` are empty, so student registration and the questionnaire have no campus or program options. There is still no seeder for them (ROADMAP Phase 0).

## Checks
- `php artisan test`: 154/154 pass. New: `tests/Feature/ProviderRosterTest.php` (15) and `tests/Unit/SpreadsheetTest.php` (4, using an Excel-shaped file with shared strings, numeric cells and a second sheet).
- Live (MySQL, `php artisan serve`), signed in as DNSC: dashboard, scholars, scholars/new, scholarships and help return 200; disbursements and requirements return 404. Signed in as CHED: all pages return 200. My scholarships shows content for both.
- Not checked: a file saved by real Microsoft Excel, and the pages' look in a browser.

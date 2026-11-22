# 2026-11-15 — Official sources stored in the repo + real application dates per scholarship

## 1. Every document screenshot is stored, with a text record
- **Why:** the owner asked that the document screenshots be "deeply read and used as a source for future purposes". They only existed in a temporary session folder that gets cleared.
- **Stored:** all 64 pages, copied into `docs/sources/<provider>/<document>/page-NN.png` in reading order:

| Folder | Document | Pages |
|---|---|---|
| `ched/tdp-guidelines-2026/` | Revised TDP Guidelines of 2026 (UniFAST Board Res. 2026-012) | 11 |
| `ched/cmo-13-s2025-cmsp/` | CHED Memorandum Order No. 13, s. 2025 (CMSP) | 8 |
| `ched/tes-guidelines-2026/` | Revised TES Guidelines of 2026, plus `website-unifast-tes.png` | 15 |
| `ched/bpmsp-jmc/` | CHED–TESDA–DepEd JMC on the BPMSP | 20 |
| `dost/dost-sei-undergraduate/` | DOST-SEI Undergraduate Scholarship brochure (and the priority list, in `NOTES.md`) | 1 |
| `grab/grabscholar-sy2026-2030/` | GrabScholar "6 Merit-Based Scholarships for SY 2026–2030" | 5 |
| `cebuana/clfi-faq/` | Cebuana Lhuillier Foundation FAQs Q1–Q12 | 4 |

- **Cropped:** the UniFAST web-page screenshot, to remove the owner's browser tabs and Windows taskbar.
- **`NOTES.md` per document:** a faithful, section-by-section text record (eligibility, ineligibility, amounts, documents, process, retention), ending with "How BAKR uses it". Later sessions read these first.
- **Index:** `docs/sources/README.md` links each scholarship to its folder.
- **Memory:** the "official docs are the main source" note now says to save pages and notes here.

## 2. Owner's idea: "a database per provider" → one table with real dates instead
- **Advice given:** do not create 5 separate databases.
  - Matching reads all 11 scholarships at once.
  - Five databases mean 5× migrations, backups and credentials.
  - Separation is already enforced per account by `ProviderScope` and the server-side `provider_key`.
- **The real gap:** the application period was free text, so BAKR could not tell whether a call is open.
- **Owner chose:** "Add date fields".

### What was built
- **Migration** `2026_11_15_000001_add_application_dates_to_scholarships`:
  - adds nullable `application_opens_on`, `application_closes_on` and `results_on` (date) to `scholarships`;
  - `down()` drops them.
- **`Scholarship::callStatus()`:** `open` | `upcoming` | `closed` | `null`. The closing day still counts as open; with no dates it returns `null`, so BAKR never guesses.
- **`callLabel()`:** for example "Open, closes Jul 16 (in 21 days)", "Opens Jun 21, 2027", "Closed Jul 16, 2027".
- **Provider portal → My scholarships:** three date inputs ("Applications open", "Applications close (deadline)", "Results released").
  - The text field is renamed "Application period notes", for extra details.
  - Validation: `Y-m-d`; the close and results dates cannot be before the opening date.
  - Saved through `ScholarshipEditor`, so the dates appear in the revision history.
  - Still scoped: another provider gets a 404.
- **`partials/call-status`:** a green, gold or grey badge with an icon.
  - Shown in the public Scholarships modal (Application window), the student's match rows (compact pill) and the match dialog (Application period), and the provider preview.
  - Styles are in `bakr-dashboard.css` and `bakr-scholarships.css`; the dark versions are in `bakr-dark.css`.
- **Dates belong to the provider:**
  - `ScholarshipSeeder` and `bakr:sync-scholarship` write dates (and renewal requirements) **only when `config/scholarships.php` lists them** from an official document.
  - Otherwise the provider's dates are left alone.
- **Grab:** gets its official SY 2026 dates (opens 2026-06-22, closes 2026-07-17, results 2026-08-20), so it shows "Closed Jul 17, 2026". The other scholarships show no status until their provider enters dates.

### Real database
- **Backup:** `storage/app/private/backups/bakr-before-application-dates-20261115-193451.sql` (220 KB).
- **Migrated.** `bakr:sync-scholarship grabscholar --apply` loaded Grab's dates. A re-check of the other 6 updated scholarships says "already matches".

## Tests
133/133 pass. New tests in `ScholarshipEditTest`:
- the provider sets dates, the public page shows the status, and the revision records them;
- open / upcoming / closed / none, including the deadline day;
- validation, and another provider cannot edit them.

The existing test payload now posts the dates unchanged.

**Not checked in a browser this session:** the new date inputs and badges. They render in the feature tests.

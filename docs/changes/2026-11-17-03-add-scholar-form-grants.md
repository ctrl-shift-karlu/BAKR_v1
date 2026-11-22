# 2026-11-17 — Add-scholar form: scholar's own details only, the rest automatic, Full / Half

## Owner's requests
- The form covers the scholars-table columns the provider has to supply.
- "Scholar since" is removed, because the person is a new scholar.
- Details that follow from the scholarship are filled in automatically from the scholarship picked.
- Scholarships that have Full and Half grants get a Full / Half choice.

## What the provider enters vs. what BAKR fills in

| `scholars` column | Add form / import |
|---|---|
| full_name, student_number, program, year_level | **Required.** Year level is a 1st–5th year list, the same as the student questionnaire. Import accepts "2", "2nd year", "Second year" or "II". |
| email | Optional |
| grant_type | Asked only when the scholarship has more than one published type: CMSP Full / Half, TES Listahanan / solo-parent-or-IP. Otherwise it is set automatically, or null. |
| status | Automatic: **Active** |
| started_term | Automatic: **the current term** |
| stipend_amount | Automatic: the published amount of the grant type, only for providers with Disbursements. Null when no fixed amount is published. |
| added_by, scholarship_id, user_id, provider_application_id | Set by the system |

The import ignores any Status, Stipend or "Scholar since" columns. The provider can still change status, grant type and stipend later on the scholar's profile. Changing the grant type there also switches the stipend to that type's published amount, unless a different amount is typed at the same time.

## Published amounts (`config/scholarships.php` → `grants`, copied from each scholarship's benefits)
- TDP: ₱7,500 per semester
- TES: Listahanan/4Ps ₱10,000 or solo-parent/IP ₱5,000 per semester
- CMSP: Full ₱40,000 or Half ₱20,000 per semester
- BPMSP: ₱22,500 per semester
- DOST-SEI: ₱8,000 per month
- GrabScholar: ₱8,000 per month
- **None** for DNSC ("set by the unit") or Cebuana ("up to ₱50,000"). Rule 6 applies: never invent a number.

## Changes
- Migration `2026_11_17_000002_add_grants_to_scholarships_and_scholars`:
  - adds `scholarships.grants` (JSON), filled from config by slug, so no re-seed is needed
  - adds `scholars.grant_type`
  - has been run on local `bakr`
- `ScholarshipSeeder` and `bakr:sync-scholarship` now also write `grants`.
- `Scholarship::grantOptions()`, `hasGrantTypes()`, `grantAmountLabel()`. `Scholar::YEAR_LEVELS`, `grant()`, `grantLabel()`, `yearLabel()`.
- `ScholarRoster` was rewritten: new rules, `resolveGrant()` (key, label or the start of the label, any case), a `record()` that fills the automatic fields, and import column checks. A file for CMSP must have a "Grant type" column.
- The Add page shows the Full / Half (or category) choice and a "Filled in for you" box only for the scholarship picked (`bakr-provider.js`; the hidden choices are disabled, so they are not sent). The template has a "Grant type" column only for providers that have such a scholarship.
- Approving an applicant: a scholarship with a single option gets that grant and stipend automatically. For CMSP or TES, the provider is asked to choose the grant type on the profile.
- Scholar profile: a grant type select (only when there are several types) and a year level select. The Scholars list shows the grant type, or "Grant type not set".
- The Help FAQ is updated.
- Pint also made whitespace-only fixes to `app/Models/ContactMessage.php` and `app/Models/User.php`.

## Checks
- `php artisan test`: 161/161 pass. `ProviderRosterTest` was updated, with new tests for the automatic fields, CMSP Full/Half (add, import and profile change), no published amount, required fields and year levels, and stipends on approval.
- Live (MySQL), signed in as CHED: `/provider/scholars/new` returns 200, has 4 grant choices (CMSP 2 + TES 2) and no "scholar since" input. `scholarships.grants` is filled for 7 of the 11 scholarships.
- Not checked in a browser: the show/hide behaviour when switching scholarships, and dark mode.

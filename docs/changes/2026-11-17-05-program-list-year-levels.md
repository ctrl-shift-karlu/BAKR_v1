# 2026-11-17 — Searchable DNSC program list; no 5th year

## Owner's requests
- The program field should be a list that can be searched.
- Remove 5th year.

## Changes
- `ProgramCatalog::names()` lists every DNSC program, A–Z. It reads the `programs` table, or, while that table is empty (there is still no seeder), the 18 DNSC program names already kept "as named in the database" in the `program_in` rules of `config/scholarships.php`.
- `ProgramCatalog::canonicalName()` matches a typed or imported name in any case and spacing, and also accepts the short forms "BS …", "BSEd …", "BSE …" and "AB …" (for example "BS Information Technology"). The result is stored under the official name.
- The Add scholar form and the scholar profile now use `<input list="dnsc-programs">` with a shared `<datalist>` (`provider/partials/program-list`). The browser shows the list and filters it by any part of the name as you type. No JavaScript library is used.
- `ScholarRoster::programRule()` is the server-side check, used by the form, the Excel/CSV import and the profile save: the program must be a DNSC program ("… is not a DNSC program. Pick one from the list.").
- `Scholar::YEAR_LEVELS` is now 1st–4th year; 5th year is removed. The student questionnaire (`ApplicationSchema`) still has "5th year or higher", because the scholarship rules read it. On the profile, a year saved earlier (for example an approved applicant's 5) is still shown and can stay as it is.
- Test fixtures now use real DNSC programs. New checks: the list is on the form, there is no 5th year, "BS Nursing" is rejected, and a lowercase short form is saved under the official name. Tests: 172/172 pass.

## Found during the live check (not caused by this session)
- The local `bakr` database now has **0 scholars**, and the student account `gabaes.karlangelo@dnsc.edu.ph` (user id 6) is gone. The scholar row went with it, because deleting a user also deletes their scholar rows.
- Migrations (18, last batch 4), the 5 providers, the 11 scholarships and the contact message are untouched. The `scholars` counter is at 2 and the `users` counter at 7, so rows were **deleted**, not the tables reset.
- No BAKR code deletes users or scholars, and the tests use SQLite in memory. The deletion was most likely done outside the app (phpMyAdmin / MySQL client). This still needs the owner's confirmation.
- Side finding: the log has `testing.WARNING: BAKR Gemini explainer request failed {"status":429}`, which means some tests call the real Gemini API with the key from `.env`. This should be faked in the tests (not changed yet).

# 2026-11-23 — Names in parts (first name, middle name, surname, suffix)

**Roadmap item(s):** owner's request: "Full name should be separated. First name, surname. Middle name & suffix optional."

## What changed
- `App\Support\PersonName` — rules (first name + surname required, middle name + suffix optional; letters, spaces,
  dots, hyphens, apostrophes; suffix from Jr., Sr., II, III, IV, V), messages, `parts()` / `compose()`,
  `normalizeSuffix()` ("jr" → "Jr."), `split()` (one-time guess for old one-line names).
- Database: migration `2026_11_23_000002_split_person_names` adds `first_name`, `middle_name`, `last_name`, `suffix`
  to `users` and `scholars` and splits existing student / scholar names once (best guess; middle name left empty).
  `users.name` and `scholars.full_name` stay and are always composed from the parts. Applied locally.
- Student sign-up (`auth/register`, `StudentRegisterRequest`, `StudentAuthController`): four fields.
- Provider Add scholar (`provider/partials/name-fields`, `create`) and the profile's Personal card (`show`,
  `ProviderScholarController`): four fields; saving also updates the scholar account's name parts.
- Excel / CSV import (`ScholarRoster`): columns "First name", "Surname" (required), "Middle name", "Suffix" (optional);
  a file with only "Full name" is refused with the missing columns named. Template has the four columns.
- `ScholarAccounts::forEmail()` takes the name parts for a new account.
- Scholar Profile shows First name / Middle name / Surname / Suffix. Greetings use `User::firstName()`; avatars use
  `User::initials()` / `Scholar::initials()` (first name + surname).
- Styles: `.form-select` and `.sa-optional` (`student-auth.css`, dark in `bakr-dark.css`); `.pv-optional`
  (`bakr-provider.css`). `public/sw.js` → bakr-v14.

## Tests
- New: unit `PersonNameTest` (4), `PersonNameFormsTest` (2). Updated to send the parts: `ProviderRosterTest`,
  `ScholarIdAndDetailsTest`, `ScholarAccountTest`, `ScholarNotificationTest`, `SemesterDeadlineTest`.
  **338/338 pass.** Pint clean.

## Note
Old names were split by guess (e.g. "Mike Chester Manloza Dumail" → first name "Mike Chester Manloza", surname
"Dumail"). Providers can correct a scholar's parts on the Personal card. Students have no page to edit their name
(there was none before either).

# 2026-11-21 · Every question required ("None of these"), "i" notes on unfamiliar questions, Privacy Policy

Owner's requests: "None of these" options so every question of the application form is required; a small "i" beside unfamiliar questions that explains them (hover on desktop, tap elsewhere), written from this project's own scholarship data; a Privacy Policy (Data Privacy Act of 2012) agreed to at the end of the form and reflected across the project.

## Changes

1. **Every question answered** (`ApplicationSchema`):
   - `special_groups`, `talent_categories`, `leadership_roles` get `'none' => 'None of these'`, become `required` with `'exclusive' => 'none'` (same as `existing_assistance` / `farm_fisher_family`).
   - `has_awards` gets `'when' => 'talent'` (`applies()`: a real skill ticked): shown and required only then (checked in `ScholarshipApplicationRequest::withValidator`; the sanitizer drops it otherwise; the old special case in `AnswerSanitizer` is gone).
   - `college_gwa_scale` required for 2nd year and up.
   - Matching results unchanged: `AnswerNormalizer` already read a missing group as `[]`, and `['none']` matches no rule value. Older saved forms keep their results until the student saves again.
2. **"i" notes:** `'info'` on 17 fields (strand, units, residency rule, GWA and its scale, Grade 12 average, Top 5, failing mark, sanction, DOST-SEI exam, gross income, Listahanan / 4Ps, RSBSA / ARB / NCFRS / FishR, national government grants, Student Assistant, Cebuana client / Microsavings). Each cites only what this project's scholarship rules check (`config/scholarships.php`). Obvious questions (birth date, citizenship, campus, year…) have none. `student/partials/field`: the label + `button.sp-info` in `.sp-q` (outside the `<label>`, so a tap never ticks an answer) and `p.sp-tip` below the question, inside the field's width (never floating, never off-screen). Desktop with a mouse: hover or keyboard focus shows it; every device: tap toggles (`student-form.js`; one open at a time, Esc / tap elsewhere closes). Styles `student-portal.css`, dark `bakr-dark.css`. Checked at 320 / 360 / 1400px: no overflow, every note inside its field.
3. **Privacy Policy:** public page `/privacy` (`route('privacy')`, `resources/views/privacy.blade.php`, `bakr-privacy.css`), date in `config('bakr.privacy_updated')`. Written from what BAKR really does: data per role, purposes, the de-identified Gemini matcher (incl. the free-tier caveat), who sees what (providers never see answers; admin never sees answers / documents / scholar personal details), lawful basis (RA 10173 Sec. 12(a)/(b)/(f), 13(a)), security and retention, cookies, data-subject rights, NPC, minors, contact.
   - **Application form:** the Privacy Policy agreement is at the end, required on **every** submit (`privacy_consent` always `accepted`; `StudentConsent` keeps the latest `accepted_at`, shown as "You last agreed on …").
   - Linked from: public footer, every sign-in page (`layouts.auth`), register page, the About-BAKR block (every portal's About dialog + Help page), Contact page and the portal Help form.
4. `sw.js` `VERSION` → `bakr-v4`.

- Tests: `MatchingFormTest` (fixture + 3 new tests). Full suite 283/283.

# 2026-11-15 — The questionnaire becomes a formal "Scholarship Matching Form"

## Why
The owner felt the student questionnaire did not feel like an application form. It was compared with typical Philippine scholarship forms (CHED, DOST-SEI, UniFAST TES, foundation forms). Those forms have:
- a form title and reference number;
- numbered parts and items;
- personal and family details;
- a documents checklist;
- a signed certification;
- a printable copy.

## Decisions (and what was deliberately NOT copied)
- **The questions and the matching did not change.** Rule engine, answers and fields are all the same.
- **No new personal data.** Parents' names, birthday, address and siblings are on real forms but no scholarship rule uses them (data minimisation, CLAUDE.md rule 3), so they are not asked.
- **It is called a *matching* form and says so.** It looks formal without imitating any provider's form. It states that it is not the official application of any provider, so students do not think they have already applied.
- Questions stay as full questions rather than short paper-style labels, because those are easier to answer on screen.

## What changed
- **Form header:**
  - "Scholarship Matching Form" title;
  - reference number `BAKR-YYYY-000123`, derived from the application id, nothing stored;
  - the current term;
  - the applicant's name and e-mail, read-only from the account;
  - the disclaimer above.
- **Parts I–VI** (`ApplicationSchema` sections now have `part` and formal titles):
  - Personal Information;
  - Educational Background;
  - Academic Standing and Conduct;
  - Family and Socio-Economic Background;
  - Extracurricular Involvement;
  - Provider-Specific Questions.
- **Item numbers** 1.1, 1.2, and so on, on the form and its printable copy. The GWA scale belongs to the GWA item and has no number of its own.
- **Part VII Certification:**
  - the student types their full name as a signature, and the date is shown;
  - `ScholarshipApplicationRequest` checks the typed name against the account name, ignoring letter case and extra spaces;
  - the typed name is **never stored**: it is not in the answers, and `submitted_at` already records when it was certified;
  - the privacy consent stays as before.
- **Review step:**
  - "Review and submit" opens a dialog listing every answer that applies, grouped by part (built as text only in `student-form.js`);
  - "Confirm and submit" sends the form;
  - without JavaScript the button submits directly.
- **Printable copy:**
  - `GET /student/application/print` (`student.application.print`) shows the signed-in student's current form only;
  - standalone, always light, A4 print CSS (`bakr-form-print.css`, `bakr-print.js`);
  - includes an "Attachment guide" that lists documents **only from the matched scholarships' `documents` in the scholarships table**;
  - linked from the profile page ("Print my form") and from the form header.
- The profile page uses `ApplicationSchema::shownAnswer()`, the same answer formatting as the printable copy, and shows the reference number.
- Sidebar: "Edit my answers" is renamed "My matching form", and the Help FAQ was updated to match.
- **Dark mode:**
  - the new form pieces have dark styles;
  - the `.sp-note` banners, which had stayed light in dark mode across the student portal and Finder, are now dark too.

## Files
- **New:**
  - `public/css/bakr-form.css`
  - `public/css/bakr-form-print.css`
  - `public/js/bakr-print.js`
  - `resources/views/student/application-print.blade.php`
  - `tests/Feature/MatchingFormTest.php`
- **Changed:**
  - `ApplicationSchema`
  - `ScholarshipApplication` (`referenceNumber()`)
  - `ScholarshipApplicationRequest`
  - `StudentApplicationController` (`printable()`)
  - `routes/web.php`
  - the `student/application`, `profile`, `layout`, `help` and `partials/field` views
  - `student-form.js`
  - `bakr-dark.css`

## Checked
- **Browser** (throwaway SQLite, since deleted):
  - the header, parts and numbering;
  - the review dialog;
  - submitting leads to results;
  - the printable copy;
  - dark mode;
  - 375px with no sideways scroll.
- **Tests:** 122/122 pass (7 new):
  - the signature is required and must match the account name;
  - the name is not stored;
  - the print page shows only the student's own form, and documents only from the table;
  - guests are redirected.

## Possible next step (needs approval: database change)
A second-stage, provider-specific application form, asked only when a student shares a match with a provider. Real-form fields (parents, address, documents) would be justified there, because the provider actually needs them.

## Follow-up (same day)
- **Income ranges and the GWA scale.**
  - Owner asked for a "Below ₱100,000" income range and a "5.0 is highest" GWA scale. **Neither was added.** No rule has a ₱100k cap (data minimisation). The GWA question is for DNSC students only, and DNSC grades 1.0 as the highest; a reversed scale would need an invented conversion.
  - The scale wording was made clear instead: "DNSC 1.0 – 5.0 scale (1.0 is the highest, 5.0 is failing)", with help text.
- **Printable form:**
  - The toolbar now has separate **Save as PDF** and **Print** buttons.
  - Save as PDF shows how to pick "Save as PDF" in the print window. A browser can't write a PDF itself without adding a PDF library, which would need approval.
- **Results honesty:**
  - My matches opens with a "Based on your answers" note: Eligible means you may apply, not that you are approved.
  - Each match dialog shows "Rules last checked <date>" and the source note in plain view (it used to be inside the collapsed details).
- **Not done (blocked, needs the owner's explicit approval):** changing the TES "Listahanan or 4Ps" rule from required to priority in `config/scholarships.php`.
- Tests: 122/122 pass.

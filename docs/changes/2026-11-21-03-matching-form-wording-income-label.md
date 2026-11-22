# 2026-11-21 · "Matching form", not "application form"; income label "Below ₱300k"

Owner's requests: the student's form is a **matching form**, not an application form; and the first income bracket reads "below 300k".

## Changes

1. Every place that named BAKR's own form now says **matching form**: the form page (title, heading "Scholarship Matching Form", button "Submit my matching form", saving message), the printable copy, the student sidebar ("My matching form", "Finish your matching form first"), dashboard, results ("AI review of your matching form"), AI status, Help FAQ, home page steps and FAQ, About-BAKR block, register, 403 page, admin Overview tile, Privacy Policy, success / rule-check / explainer messages, code comments, README and CLAUDE.md.
   - **Kept on purpose:** providers' own official forms ("TDP Application Form", "ILP Application Form", the BFAR and CLFI forms in `config/scholarships.php` and `docs/sources/`), "official application form of any scholarship provider", and "application" where it means applying to a provider. Older change logs and backups are history and stay as written.
   - Routes, classes and tables keep their names (`student.application.*`, `/student/application`, `ScholarshipApplication`), so links and data are unchanged.
2. `ApplicationSchema::INCOME['up_to_300k']` label "Up to ₱300,000" → **"Below ₱300k"**. Key and limits (0 – 300,000) unchanged, so saved answers and results are the same.

- Tests updated for the new wording (`MatchingFormTest`, `AiMatcherTest`). Full suite 283/283.

# 2026-11-19 (02): Maintaining documents get their own form on My scholarships

## Request
On My scholarships, in every provider account, editing the maintaining documentary requirements should be available where it applies to the scholarship, with its own form (document name, accepted files, etc.).

## Decisions taken (owner can change)
- Where it applies = providers that collect requirements in BAKR (`features` has `requirements`): every provider except DNSC. DNSC gets neither the section nor the route (404).
- Fields per document: **name**, **instructions for scholars** (optional), **accepted files** (PDF, JPG, PNG, Word DOCX; at least one), **largest file** (1 / 2 / 5 / 10 MB), **who submits it**: every scholar every semester, or only scholars it applies to.
- "Only if it applies" items can be uploaded and reviewed but never make a scholar Inactive and never hold back a stipend. Needed because TDP/TES list conditional items ("If returning after a Leave of Absence…").
- Existing plain-text lists were converted using only their own wording: "If …" → only if it applies; "PDF copy/copies" → PDF only; everything else → PDF/JPG/PNG, 5 MB, every scholar.

## Changes
- `App\Support\MaintainingDocument` (types, sizes, normalize / fromText, upload `mimes`, `accept`, labels). `Scholarship::maintainingDocuments()`, `maintainingDocument()`, `hasRequiredMaintainingDocuments()`.
- `scholarships.renewal_requirements` now holds `{name, note, types, max_mb, required}` items (strings still read).
- `scholar_requirements` + `is_optional`, `accepted_types`, `max_kb`, `instructions`: copied from the document when the checklist is opened; saving the documents updates every item that is not approved yet (`ScholarshipEditor::syncChecklistSettings`).
- `PUT provider/scholarships/{id}/documents` (`ProviderScholarshipController::updateDocuments`, `provider.feature:requirements`, scoped, revision logged). The old textarea in "Edit information" is gone.
- Views: `provider/partials/maintaining-docs` (read list + form), `provider/partials/maintaining-doc-row` (one document card; "Add a document" copies a `<template>`, Remove can be undone; without JS one blank row is always there).
- Scholar side: upload validation, the upload dialog (accept, size check, messages) and the document rows use each document's own rules and show the provider's instructions; `ScholarRequirement::MIMES` / `MAX_KB` removed.
- `Scholar::releaseBlocker()` and Inactive ignore optional items.
- Fixed `DNSC\x27s` showing literally in the TES config text.

## Database (local)
Migration `2026_11_19_000001_structured_maintaining_documents` ran: TDP/TES/CMSP/BPMSP lists converted; 3 existing checklist items got their settings. (The TES text in the DB still contains the `\x27` typo; correct it in the new form.)

## Tests
221/221 pass. New: own form + validation (types required, duplicate names, unknown type/size), DNSC/other provider 404, settings copied to checklist items and synced on edit, per-document upload type/size, optional item never Inactive / never blocks release, plain-text conversion.

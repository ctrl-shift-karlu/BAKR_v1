# 2026-11-22 · Disbursements: one Release button, no "On hold"

Owner's request: remove the On hold idea completely and replace the per-row status dropdown with a Release
button that updates the scholar's stipend status by itself.

## What changed

- `StipendDisbursement::STATUSES` = `pending` ("Not yet released") and `released` only.
- Disbursements page: the "Update" column (status dropdown + Save) is now "Action":
  - not yet released and nothing blocks it → reference box (optional) + **Release** button
    (`POST provider/disbursements/{id}/release`, `provider.disbursements.release-one`, `releaseOne()`);
  - locked → disabled "Locked" button (the reason stays under the status);
  - released → "Done".
  The "On hold" summary card is gone; the bulk "Mark selected as released" is unchanged.
- `releaseOne()` checks the same lock as before (`Scholar::releaseBlock`, error key `release`, the "or put
  it on hold" hints removed); pressing it on a released row only shows a notice (date and reference stay).
  The old `PATCH provider/disbursements/{id}` (`disbursements.update`) was removed: there is no way back to
  "Not yet released".
- On hold removed from: provider Overview (progress bar, stipend donut), Scholars list, scholar profile,
  the scholar's My scholarship (icon), provider + scholar Help text, CLAUDE.md, a CSS comment.
- Migration `2026_11_22_000001_remove_on_hold_stipends`: `on_hold` rows → `pending` (run on local MySQL:
  there were none).

## Tests

315/315. `ProviderOperationsTest` uses the new route (release, already released, no dropdown / On hold on
the page, button hidden while locked, migration); `SemesterDeadlineTest` message updated.

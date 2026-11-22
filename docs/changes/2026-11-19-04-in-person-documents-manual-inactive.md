# 2026-11-19 (04): Documents handed in person; Inactive by hand for those providers

## Request
For providers that don't handle documents in BAKR, show that maintaining documents are asked for and submitted directly, in person, at the official DNSC scholarship office. Every provider can terminate its own scholars. A scholar is automatically Inactive past the document due date; providers without document submission on the website can mark a scholar Inactive themselves.

## Changes
- `scholars.status` may now hold `inactive` (set by hand). `Scholar::standing()` / `whereStanding('inactive')` include it; new `Scholar::CURRENT` (active + inactive) for "current scholars" counts; `isMarkedInactive()`; a manually inactive scholar's stipend is locked like a terminated one's.
- `ProviderScholarController::status`: `inactive` is accepted only from providers without the `requirements` feature (today: DNSC); for the others it is refused (Inactive stays automatic). `terminated` / `active` for every provider, own scholars only.
- Scholar profile (provider): "Mark inactive" / "Mark active" for in-person providers, Terminate for all, Reinstate when terminated; a note that documents are submitted in person at the DNSC scholarship office.
- `config/bakr.php` `scholarship_office` (`BAKR_SCHOLARSHIP_OFFICE`, default "DNSC Scholarship and Grants Unit"): one place for the office name.
- Scholar portal: Requirements and My scholarship say to submit maintaining documents in person at the official DNSC scholarship office; Overview shows "Your … scholarship is marked inactive" with where to go (fixed a crash path: the overdue action assumed an overdue document existed).
- Provider My scholarships (in-person providers): the same note instead of the documents form; Help has a FAQ on submitting in person and marking Inactive; Scholars list explains each Inactive; admin analytics wording.

## Tests
227/227 pass. New: in-person provider marks inactive / active / terminated and the scholar sees why; a provider with BAKR documents cannot set Inactive by hand; every provider (all 7) can terminate its own scholars.

## Note on local data
The old `coscho` provider login (deactivated on 2026-11-18 when CoScho moved into CHED) is active again in the local database. It cannot sign in (its key is no longer in config/providers.php). Deactivate it again under Admin › Accounts › Providers if it was reactivated by mistake.

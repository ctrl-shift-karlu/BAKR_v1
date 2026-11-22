# 2026-11-21 · Provider sidebar opens each provider's official website

Owner's request: in every provider's sidebar, the "public site" link should open that provider's own official site.

## Changes

- `config/providers.php`: new `website` per account (checked 2026-11-21):
  - ched → https://ched.gov.ph/
  - dost → https://www.sei.dost.gov.ph/
  - cebuana → https://cebuanafoundation.org/
  - dnsc → https://dnsc.edu.ph/
  - landbank → https://www.landbank.com/
  - bfar → https://www.bfar.da.gov.ph/
  - grab → https://www.grab.com/ph/

  ched.gov.ph and dnsc.edu.ph answer 403 to automated requests but open in a browser.
- `ProviderScope::website()` returns the URL (https only, else null: the link is then hidden).
- `provider/layout`: the Help & info link "Public page" (BAKR `/scholarships`) is now **"Official website"**, opening `$scope->website()` in a new tab (`rel="noopener noreferrer"`, hidden "(opens in a new tab)" text).
- Unchanged: the "View public page" / "See it on the public page" buttons on My scholarships still open BAKR's Scholarships page, because they show what students see on BAKR.

## Tests

- `ProviderPortalTest`: every account has its own unique https website; every provider's sidebar links to its own site, not "Public page". Full suite: 301/301.

# 2026-11-21 · Scholarship header opens the official website; "Add scholarship" built and removed

## Kept

- My scholarships header: "See it on the public page" is now **"Official website"** → `Scholarship::officialWebsite()`: the scholarship's own `official_url` (How to apply › Official page), else the provider's `website` from `config/providers.php`. Only http(s) links, hidden when neither exists. Test: `ScholarshipEditTest::test_the_header_link_opens_the_scholarships_official_website`.
- (Earlier the same day: the provider sidebar "Official website" link, see `2026-11-21-07-provider-official-website-link.md`.)

## Removed (owner's request: remove the Add scholarship idea and everything related to it)

- Routes `provider.scholarships.create` / `.store`, controller methods `create` / `store` and the shared text-rule constants (the update validation is back to what it was), view `provider/scholarship-create`, `ScholarshipCreator`.
- `ScholarshipEditor`: no more `confirmRule`, `lines`, `qualificationErrors`, `withQualifications`, the Remove option or the 5th `apply()` argument. Providers again cannot add or remove qualification rules.
- My scholarships view: the Add buttons, the Remove checkbox, "Add qualifications" and the "Added" label in Recent changes. CSS: `.ms-new*`, `.pv-rule-checks`, `.dash-check.is-danger`, the empty-state button rule.
- Ownership column: migration `2026_11_21_000003_add_provider_key_to_scholarships` deleted, and `scholarships.provider_key` dropped from the local database (it existed only for this feature). `ProviderScope::ownedBy` / `keyForSlug` / `refresh`, `Scholarship::ownerKey` / `account` / `isProviderAdded` are gone; `offers()`, `logoPath()` and the admin per-provider numbers read the config `slugs` again, as before.
- A leftover test scholarship ("dasdsadsad", created through the form at 13:07, no scholars, 8 auto-generated matches and 1 history entry) was deleted from the local database. It was the only row outside `config/scholarships.php`: the table has the 14 official scholarships again.
- Tests: `ProviderAddScholarshipTest` deleted. `sw.js` → `bakr-v9`.
- Not touched: `users.provider_key` (provider accounts), the About page work and the earlier provider sidebar link.

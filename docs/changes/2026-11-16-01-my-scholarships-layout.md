# 2026-11-16 — Provider "My scholarships": new layout, read-only until Edit; one source for every page

## Owner's request
1. Change the layout of My scholarships in the provider portal.
2. Make it read-only, with an **Edit** button needed to change anything.
3. The public Scholarships page must reflect the same content as the provider's My scholarships.
4. The content must be the official information the owner sent.

## What changed
- **`resources/views/provider/scholarships.blade.php`** (rewritten)
  - **Profile header:**
    - provider logo, sector, scholarship name and provider;
    - the Open / Opens / Closed badge;
    - "Rules last checked";
    - in read mode: **Edit information** and **See it on the public page**.
  - **Read-only view (default)**, in the same sections as the public page's pop-up:
    - Overview;
    - Qualifications (the shared `scholarship-qualifications` partial, so it matches the public page exactly);
    - Benefits and Documents side by side;
    - Renewal requirements (labelled "Only your scholars see this").
  - **Side column:**
    - Application period: status badge, open / deadline / results dates ("Not announced" when empty), notes;
    - How to apply, with the official page;
    - Source (the official document note);
    - Recent changes. Official-source updates now read "BAKR (official source update)" instead of "Unknown".
  - **Edit mode** (`?edit=1`, reached only through the button):
    - the same layout becomes the form;
    - the header gets a gold outline and an "Editing" pill, and **stays pinned at the top** with **Cancel** and **Save and publish**;
    - a second save bar sits at the end of the side column;
    - a failed save returns to edit mode with the typed values and an error banner.
  - **No data or permission change:** the same form fields, the same `PUT` route, `ProviderScope`, `ScholarshipEditor`, validation and revision history.
- **`public/css/bakr-provider.css`:** new `.ms-*` styles on the `.dash` tokens. The page stacks to one column below 1180 px; on phones the buttons go full width.
- **`public/css/bakr-dark.css`:** chip, link and "private" label colours for dark mode.
- **Public Scholarships page** (`partials/scholarship-catalogue`):
  - each card shows the Open / Closed badge;
  - the pop-up's source note starts with "Rules last checked <date>".
- **Home page "Scholarships to start with"** (`home.blade.php`, `partials/modals/scholarship-details`):
  - the card text and the 3 pop-ups were hand-written copies (old "matching factors" and document lists);
  - they now read the same `scholarships` rows (overview, qualifications, documents, benefits, application, badge);
  - "All details" links to the full card.

## Result
There is one source for every page. The provider's My scholarships, the public Scholarships page, the student portal catalogue and the home page all read the same row, and that row holds the official-document data synced on 2026-11-15.

## Checked
- **Browser** (throwaway SQLite, logged in as a CHED provider):
  - the read view;
  - Edit, which pins the header with Save and Cancel;
  - saving an overview change, which appears in the public page's source (card and pop-up);
  - dark mode;
  - 375 px with no sideways scroll.
- **Tests:** 135/135 pass. New tests in `ScholarshipEditTest`:
  - read-only until edit; edit shows the form; a failed save comes back in edit mode;
  - a provider edit appears on `/scholarships` and on the home page pop-up.

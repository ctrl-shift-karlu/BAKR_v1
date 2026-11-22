# 2026-11-20 · Scholar ID + scholars' personal details

**Asked (owner):** add mobile number + address (contact) and birthday + gender (personal) for scholars; give every scholar a Scholar ID the provider can search/filter by, in a format the provider edits on My scholarships (default `SCID-0000-0000`), and make entered IDs follow it.

## What changed
- Migration `2026_11_20_000001_add_scholar_id_and_personal_details`: `scholarships.scholar_id_format` (default `SCID-0000-0000`); `scholars.scholar_code` (unique per scholarship), `birthdate`, `gender`, `mobile`, `address`.
- `App\Support\ScholarIdFormat`: `0` = one digit, everything else literal; stored in capitals without spaces.
- Add scholars: Scholar ID required (format shown per scholarship), fields grouped Personal / Contact / School; optional birthday, gender, mobile, address. Excel/CSV: required "Scholar ID" column, optional Mobile number / Address / Birthday (Excel dates understood) / Gender columns; template updated.
- Scholar profile (provider): Scholar ID + the four details editable; old-format ID warning; an ID can be changed but not emptied.
- Scholars list: search includes Scholar ID; new "Scholar ID starts with" filter (`none` = no ID).
- My scholarships: new "Scholar ID format" section (own Edit / Save, revision logged, not public) with counts of scholars without an ID / with an older-format ID.
- Scholar portal: Profile shows birthday, gender, mobile, address and each Scholar ID; My scholarship shows the Scholar ID.
- Disbursements / Requirements rows show the Scholar ID.

## Decisions
- Personal details are optional (older rows and files without them stay valid). Scholar ID is required for new scholars only; older scholars are listed as "No Scholar ID".
- Changing the format never rewrites saved IDs.

## Checks
- 265/265 tests. Headless Chrome probe at 500 / 820 / 1400 px, light + dark: no new overflow or tiny text.
- Already there before this change (not fixed): the provider Scholars table scrolls sideways between ~640 and ~1300 px wide wrappers (8 columns).

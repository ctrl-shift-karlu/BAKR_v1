# 2026-11-13 — Navbar: Register button, no "My account" dropdown

**Reported:**
- Visitors had no visible way to register. Registration was only a small link on the student login page.
- A signed-in student saw a "My account" dropdown on public pages like Home, which was confusing.

## Changed
- `resources/views/partials/navbar.blade.php`, account area:
  - **Signed out:** "Sign in" dropdown (renamed from "Sign in as"). It now ends with "New student? Create a free student account". A gold-outlined **Register** button (→ `student.register`) sits next to it.
  - **Signed in (student or provider):** no dropdown. One **My dashboard** / **Provider dashboard** button with the person's initials, plus a sign-out icon button (text "Sign out" on the mobile menu).
- `public/css/bakr-layout.css`: styles for `.nav-register`, `.nav-account`, `.nav-signout`, including mobile stacking.

## Added
- `tests/Feature/NavbarAccountTest.php`: guests see Register; signed-in students see "My dashboard" and no "My account".
- `WrongRolePageTest`: now expects "Provider dashboard".

## Verified
- `php artisan test` → 83/83.
- Screenshots at 1920px on a throwaway SQLite DB: the signed-out and signed-in navbar on Home.

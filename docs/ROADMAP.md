# BAKR Roadmap

> Based on a codebase analysis on 2026-11-09. Work top to bottom: each phase makes the next one safer.
> Tick items `[x]` when done and log the session in `docs/changes/`.
> **One item (or one small group) per conversation.** `/clear` between items, `/compact` when a session runs long.

Legend: **P0** must-do before real users · **P1** high value · **P2** nice to have · effort **S** (<1h) / **M** (half day) / **L** (1–3 days)

---

## 1. Architecture snapshot (what exists today)

| Area | State | Notes |
|---|---|---|
| Matching engine (`app/Matching`) | ✅ Solid | Pure PHP, deterministic, unit-tested, schema-driven |
| Student portal | ✅ Working | Register → 6-step form → versioned encrypted answers → matches → AI/template explanations |
| Contact & feedback | ✅ Working | Saved first, then emailed; honeypot; approval gate. Moderation is done in Tinker |
| Public pages | ✅ Working | Static Blade + `public/css|js` + Bootstrap CDN |
| About stats | ⚠️ Sample data | `PublicAnalyticsService::fromDatabase()` throws "not implemented" |
| Provider Portal | ✅ Working | Real auth (`role:provider` + `provider_key`), one shared dashboard for 5 providers, aggregate stats (2026-11-12) |
| Scholar / Admin | ❌ Prototype | Huge static Blade files. Login POST redirects with **no auth**. The **Officer role was dropped on 2026-11-17** (see "Roles" below) |
| Forgot password | ❌ Static | No reset flow, no email verification |
| Frontend build | ⚠️ Broken | `vite.config.js` points at files that don't exist, so `composer setup` / `npm run build` fail |
| Tests | ⚠️ 54/54 | All pass. Provider Portal is covered; the student flow still has no feature tests (no `UserFactory`) |
| Version control | ✅ Git + GitHub | Private repo `MsAbella-27/BAKR`, branch `main` |
| Reference data | ⚠️ | `campuses` and `programs` have no seeder |

### Roles (decided 2026-11-17)
- **Student**: finds matches, shares one with a provider, and follows "My scholarship" once approved.
- **Provider**: manages only its own scholarships, applicants, scholars, stipends and requirements (`ProviderScope`). The DNSC Scholarship and Grants Unit is the `dnsc` provider.
- **Admin**: sees the whole platform's analytics and manages accounts (providers, students, admins).
- There is **no Officer role**. The officer portal was removed; backups are in `docs/changes/backups/*officer*.2026-11-17.bak`.

### Architectural strengths to keep
- Rules decide and AI only explains. The AI receives no PII, and the template fallback always works.
- `ApplicationSchema` is the single source of truth for the form, validation, labels and matching.
- Answers are versioned and change detection prevents duplicate versions.
- Ownership is scoped to the user in every student query.

### Main weaknesses
- Three of four roles exist only as static prototypes, so there is no back office.
- ~~Leftover Phase 3 code and a duplicate `PublicAnalyticsService`~~ (removed 2026-11-09).
- AI calls run synchronously inside the web request, with a 20 s timeout and 1 retry.
- Scholarship rule edits don't re-match existing students (`rematch()` is never called).
- Deadlines are free text (`window_note`), so nothing can sort or notify by date.

---

## Phase 0 — Stabilise the foundation (P0)

- [x] **Install Git and init the repo** (S). Commit the current state before any change. *Prereq for everything.*
- [x] **Remove the duplicate `app/Http/Services/PublicAnalyticsService.php`** (S). It's an outdated copy with a wrong PSR-4 path.
- [x] **Retire legacy Phase 3 code** (M). *Done 2026-11-09; live `bakr` DB migrated after a backup.* Remove `StudentApplicationService`, `ApplicationDocumentPolicy`, `ApplicationDocument`, `DocumentType`, `StudentApplicationSubmitted`, `DocumentTypeSeeder` and the legacy feature test. Add a migration dropping `application_documents`, `student_applications`, `document_types` and `student_profiles` (back up the DB first).
- [ ] **Add `UserFactory` + feature tests for the real student flow** (M): register → apply → results; ownership on `matches/{id}/explain` returns 404; consent required.
- [ ] **Campus & Program seeder** (S) with real DNSC data, so a fresh install works.
- [ ] **Fix or remove the Vite config** (S). Either drop Vite/Tailwind and remove `npm run build` from `composer setup`, or create `resources/css/app.css` and `resources/js/app.js` (see Phase 4).
- [x] **`.env` hygiene** (S). *Done 2026-11-09.* Set `APP_NAME=BAKR`, sync `.env.example` with `BAKR_*` keys and MySQL defaults, and document production values (`APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`).

## Phase 1 — Security & privacy (P0)
*Use the `owasp-security` skill for review; run `/security-review` after each item.*

- [ ] **Real authentication for admin** (L). Use a separate login, as students and providers do (`Auth::attempt` + `role`), with `role:admin` middleware. Remove the `foreach` route stubs.
- [ ] **Password reset** (M). Use Laravel's broker (`password_reset_tokens` already exists). Replace the static `forgotPassword` view.
- [ ] **Email verification** for students (S–M) using `MustVerifyEmail`.
- [ ] **Security headers middleware** (S): CSP (allow jsDelivr + Google Fonts), `X-Frame-Options`, `Referrer-Policy`, `Permissions-Policy`, HSTS in production.
- [ ] **Subresource Integrity** on CDN `<link>`/`<script>` tags, or self-host Bootstrap (S).
- [ ] **Data subject rights (DPA 2012)** (M): "Download my data" and "Delete my account" on the profile page; show consent date.
- [ ] **Audit log** (M) for admin and provider actions on scholarships, feedback approval and user status.
- [ ] **Login hardening** (S): lockout messaging, and stop revealing whether an email exists.

## Phase 2 — Back office: Admin (P1)

- [ ] **Account management** (M). Admin lists, creates, deactivates and reactivates provider, student and admin accounts. Role and status stay server-set. This replaces the `.env` + `ProviderAccountSeeder` route for new providers.

- [ ] **Scholarship manager** (L). Add CRUD for `scholarships` (overview, benefits, documents, active flag, order) and a rule editor validated against the `RuleEvaluator` types. On save, dispatch a queued **re-match job** that calls `ScholarshipApplicationService::rematch()` for every current application.
- [ ] **Structured application windows** (M). Add `opens_at`/`closes_at` dates next to `window_note`, then show "closing soon" / "closed" badges.
- [ ] **Contact inbox** (M). List messages, mark them handled, approve or unapprove public feedback, and retry failed emails (`emailed_at` null). This replaces Tinker.
- [ ] **Campus & program management** (M), with a live "mapped field" preview using `ProgramCatalog::fieldFor()`.
- [x] **Provider Portal** (M). *Done 2026-11-12.* `/providerLogin` (real `Auth::attempt`) → `/provider/dashboard` for CHED/UniFAST, DOST-SEI, Cebuana, DNSC and Grab. Mapping in `config/providers.php`; accounts from `.env` via `ProviderAccountSeeder`; qualifications rendered from each scholarship's `rules`; per-scholarship match counts with MIN_GROUP_SIZE hiding. **Follow-ups:** run the migration + seeder on live `bakr`; password reset for providers; let providers report corrections (feeds the scholarship manager).
- [x] **Provider operations** (L). *Done 2026-11-12.* Applicants (student opt-in share with consent → approve creates a scholar), Scholars, stipend Disbursements per term, per-term Requirements with private uploads and review, and provider editing of their scholarships (public page now DB-driven; rule edits re-match). **Follow-ups:** run migrations on live `bakr`; file retention/deletion policy; notify students on review/release; let BAKR staff approve rule edits before they go live.
- [ ] **Real analytics** (M). Implement `PublicAnalyticsService::fromDatabase()` with GROUP BY queries and cache it for 1 h. Keep the MIN_GROUP_SIZE rule. The admin dashboard shows the same aggregates for the whole platform, plus per-scholarship and per-provider counts.
- [ ] **Convert the static `adminDashboardF` prototype** into real Blade components fed by these features (L, incremental). Drop its officer pages and workflow (Officers directory, "send back to Officer", officer columns): providers approve their own scholars now.

## Phase 3 — Student experience (P1)

- [ ] **Document checklist per match** (M). Students tick the documents they've prepared. Store only booleans, never files.
- [ ] **Deadline reminders** (M). Send an email when a matched scholarship opens or is closing (needs Phase 2 dates). Use queue + scheduler.
- [ ] **Save progress in the multi-step form** (M) so a half-filled form survives a refresh. Use a local draft; don't store unsubmitted data on the server.
- [ ] **Printable / PDF results summary** (M).
- [ ] **"What changed" view** between answer versions on the profile page (S).
- [ ] **Localisation** (L): Filipino and Cebuano via `lang/` files, starting with `ApplicationSchema` labels.
- [ ] **Scholar portal** (L), for students who already hold a scholarship: renewal requirements, grade-maintenance reminders.

## Phase 4 — Frontend architecture & design (P1–P2)
*Use `impeccable`, `ui-ux-pro-max`, `emil-design-eng` and `awesome-design-md` skills.*

- [x] **Shared dashboard shell** (M). *Done 2026-11-12.* `public/css/bakr-dashboard.css` gives every dashboard the same floating-panel layout, white sidebar, top bar, tiles, board and segmented pills. Provider + student shells rebuilt on it; staff prototypes opt in with classes only.
- [ ] **Write a `DESIGN.md`** for BAKR (colours, type, spacing, components) based on the current `bakr-core.css`. Use `awesome-design-md` examples as reference.
- [ ] **Blade components** (M–L): `<x-card>`, `<x-status-badge>`, `<x-modal>`, `<x-form.field>`. Split `scholarshipFinder.blade.php` (3k lines). *(Done 2026-11-12: `scholarships.blade.php` now renders the catalogue from the `scholarships` table.)*
- [ ] **Unify the auth pages** (`studentLogin`, `studentRegister`) with the main layout tokens (S).
- [ ] **Asset pipeline decision** (M): keep static `public/` files (simplest) or move to Vite with versioned and minified bundles.
- [ ] **Accessibility pass** (M): focus states, labels, contrast, keyboard navigation in modals and the stepper, `prefers-reduced-motion`.
- [ ] **Performance** (S): compress `public/video/dnsc-about-video.mp4` and large JPGs, add `loading="lazy"`, and serve WebP.

## Phase 5 — Quality, operations, AI (P2)

- [ ] **Playwright E2E smoke tests** (M), using the `playwright-cli` skill: home → register → apply → results → explain.
- [ ] **CI** (S) running GitHub Actions with Pint, PHPUnit and (optionally) Larastan.
- [ ] **Queue the AI explanations** (M). Dispatch a job, show "preparing…", and poll or refresh. This keeps requests fast when the API is slow.
- [ ] **AI cost guard** (S): a daily cap per student and globally, logged by count only.
- [ ] **Deployment guide** (M) covering the server, `php artisan optimize`, queue worker, scheduler, backups (including **APP_KEY escrow**), and log rotation.
- [ ] **Static analysis** (S): add Larastan level 5, then raise it gradually.

---

## Suggested order for the next sessions

| # | Session | Items |
|---|---|---|
| 1 ✅ | Git + cleanup | Install Git, init and commit, remove duplicate class, `.env.example` sync |
| 2 | Tests | UserFactory, real feature tests, delete legacy test |
| 3 ✅ | Legacy removal | Phase 3 files + drop migration |
| 4 | Seeders | Campus/Program seeder |
| 5 | Admin auth | Admin login + middleware |
| 6 | Password reset | Reset + email verification |
| 7 | Security headers | Headers + SRI + `/security-review` |
| 8+ | Back office | Scholarship manager → re-match job → contact inbox → analytics |

Each row = **one conversation**. Start each with: *"Read CLAUDE.md and docs/ROADMAP.md, do session N."* End with a log entry in `docs/changes/`, then `/clear`.

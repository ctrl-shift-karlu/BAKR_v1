# BAKR - DNSC Scholarship Matching and Monitoring System

BAKR helps students of **Davao del Norte State College (DNSC)** find the scholarships they qualify for. A student fills in one matching form (personal, academic, family, other scholarships and achievements: only questions a scholarship rule reads). BAKR's rules check the published limits of each scholarship, and the Gemini AI matcher reviews the whole de-identified application against each scholarship's eligibility and requirements; the stricter result is shown. The student sees which ones they are likely eligible for, which ones are possible, what information is missing, and which documents to prepare.

---

## 1. System description

### 1.1 Purpose

Scholarship information for DNSC students is spread across Facebook pages, government portals and foundation websites, each with its own requirements. BAKR collects that information in one place and answers one question for each student: *"Which of these scholarships fit me, and why?"*

### 1.2 Users

| User | What they can do today |
|---|---|
| **Visitor** | Browse Home, About, Scholarships, and Contact; send a message or feedback. Matching needs a student account |
| **Student** | Register, sign in, fill in the application, see matches and explanations, edit answers, view answer history |
| **Provider** | Sign in, manage their own scholarships, applicants, scholars, stipends and requirements |
| **Scholar / Admin** | Design prototypes only (static pages). Admin will see the whole platform's analytics and manage accounts (see the roadmap). There is no Officer role |

### 1.3 Main features

**Public website**
- **Home**: introduction, partner logos (CHED, DOST, Grab, Cebuana), welcome modal.
- **About**: DNSC/BAKR background, video, community insights charts (live from the database; groups smaller than 10 are hidden for privacy), office, graduate stories, team.
- **Scholarships**: a catalogue of 14 scholarships with details modals and links to the official pages.
- **Contact**: a contact/feedback form. Every message is saved to the database and emailed to the BAKR inbox. Bots are filtered with a honeypot and a rate limit. Feedback appears publicly only if the sender allowed it **and** staff approved it.

**Student portal** (`/student/...`)
- **Registration & login**: email and password (at least 8 characters, with letters and numbers). Login is rate limited.
- **Matching form**: 6 short steps (About you, Studies, Grades & conduct, Family & support, Talents & leadership, Private scholarship details). Questions that don't apply are hidden. The student picks their campus, then a program from that campus. A Data Privacy consent is required the first time.
- **Matching**: for each scholarship BAKR gives a status and a closeness score (0–100):
  - **Likely eligible**: every rule passes
  - **Possible**: required rules pass, but something must be confirmed with the provider or an optional rule failed
  - **Needs more information**: a required answer is missing
  - **Not a match**: a required rule failed
- **Dashboard**: counts per status, top matches, missing information, and an overview.
- **Results**: every scholarship with a per-rule breakdown, filterable by status.
- **Profile**: saved answers and every saved version. Changing answers creates a new version and runs the matching again.
- **AI explanations (optional)**: an "Explain with AI" button asks Claude to rewrite the results in plain language. Without an API key, the built-in explanation is shown.

### 1.4 Scholarships covered

| Sector | Scholarships |
|---|---|
| DNSC internal | DNSC Academic Scholarship, Grant-in-Aid Category A (talent: music, dance, arts, sports), Grant-in-Aid Category B, Student Assistant (Category C) |
| Government | CHED Tulong Dunong (TDP), CHED Tertiary Education Subsidy (TES), DOST-SEI, CHED Merit Scholarship (CMSP), BPMSP |
| Private | GrabScholar, Cebuana Lhuillier Foundation (CLFI) |

Their rules live in `config/scholarships.php`. Each rule records its source. BAKR never invents a cut-off: if a provider doesn't publish a number, the rule says "confirm with the provider".

### 1.5 Design principles

- **Rules decide, AI only explains.** Eligibility comes from a deterministic rule engine, so the same answers always give the same result. The AI cannot change a status.
- **Privacy by design** (Philippine Data Privacy Act of 2012):
  - The form asks only what a scholarship rule needs: no address, phone, or documents.
  - Answers are **encrypted** in the database with the app key.
  - The AI never receives a name, email, or free text.
  - Every student query is scoped to the signed-in user.
  - Public statistics are aggregates, and small groups are hidden.
- **Works offline from AI.** The system is fully usable and demonstrable without an API account.

### 1.6 How it works (architecture)

```
Browser ──► routes/web.php ──► Controllers ──► Services ──► Matching engine ──► Database
                                   │                             (pure PHP)
                                   └──► ExplanationService ──► TemplateExplainer (built-in)
                                                           └─► ClaudeExplainer  (optional, Anthropic API)
```

1. `StudentApplicationController@store` validates the form (`ScholarshipApplicationRequest`, built from `ApplicationSchema`).
2. `ScholarshipApplicationService::submit()` cleans the answers (`AnswerSanitizer`) and adds program/campus names and the program field (`ProgramCatalog`). If nothing changed, it reuses the current version. Otherwise it saves a new encrypted version.
3. `MatchingService` turns answers into facts (`AnswerNormalizer`) and runs `RuleEvaluator` against every active scholarship.
4. One row per scholarship is stored in `scholarship_matches` with status, score and per-rule checks.
5. On request, `ExplanationService` produces an explanation and caches the AI text until the answers change.

### 1.7 Tech stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3+ (tested on 8.5), Laravel 13 |
| Database | MySQL/MariaDB (XAMPP) or SQLite |
| Frontend | Blade templates, Bootstrap 5.3 + Bootstrap Icons (CDN), custom CSS/JS in `public/` |
| Email | Laravel Mail (log driver locally, Gmail SMTP in production) |
| AI (optional) | Anthropic Claude Messages API (`claude-haiku-5-5` by default) |
| Tests | PHPUnit 12 |

### 1.8 Project structure

```
app/
  Ai/                   Explainers (Claude + built-in template) and prompt builder
  Console/Commands/     bakr:check-programs
  Http/Controllers/     Public pages, student auth, student portal
  Http/Middleware/      RoleMiddleware (role:student, ...)
  Http/Requests/        Form validation
  Mail/                 Contact notification email
  Matching/             Application schema + rule-based matching engine
  Models/               Eloquent models
  Services/             Application, explanation and analytics services
config/
  scholarships.php      Scholarships and their rules
  program_fields.php    Program name → field mapping
  bakr.php, bakr_ai.php BAKR settings
database/migrations/    Schema
database/seeders/       ScholarshipSeeder
public/css, public/js   Page styles and scripts
public/images, video    Logos, photos, About video
resources/views/        Blade pages, partials, student portal, emails
routes/web.php          All web routes
tests/                  Unit (matching) and Feature (contact) tests
docs/                   Roadmap and change log
```

### 1.9 Pages and routes

| URL | Page | Access |
|---|---|---|
| `/` | Home | Public |
| `/about` | About | Public |
| `/scholarships` | Scholarship catalogue | Public |
| `/scholarship-finder` | Redirects to `/find-my-matches` (Finder removed 2026-11-19) | Public |
| `/contact` (GET/POST) | Contact & feedback | Public (POST limited to 5 per 10 min) |
| `/studentRegister`, `/studentLogin` | Student sign-up / sign-in | Guests |
| `/student/application` | Matching form | Student |
| `/student/dashboard` | Dashboard | Student |
| `/student/results` | All matches (`?status=` filter) | Student |
| `/student/profile` | Answers & versions | Student |
| `/scholarLogin`, `/adminLogin` + `…DashboardF` | Prototype portals | Static, no auth |
| `/forgotPassword` | Prototype page | Static |
| `/up` | Health check | Public |

---

## 2. Setup

> **New collaborator?** Follow [CONTRIBUTING.md](CONTRIBUTING.md). It covers setup, the database dump and `APP_KEY`, branches and pull requests, and the project rules.

### 2.1 Requirements

- PHP **8.4+** (the installed packages need 8.4.1 or newer; XAMPP's bundled PHP 8.2 is too old) with `pdo_mysql` (or `pdo_sqlite`), `openssl`, `mbstring`, `fileinfo`
- Composer 2
- MySQL/MariaDB (XAMPP works) or SQLite
- Node.js 20+ (only for tooling such as Playwright CLI; the site itself doesn't need a build step)

### 2.2 Install

```sh
cd C:\xampp\htdocs\take4\my-laravel-app
composer install
copy .env.example .env          # macOS/Linux: cp .env.example .env
php artisan key:generate
```

> **Keep `APP_KEY` safe.** Student answers are encrypted with it. If you change it later, saved answers can no longer be read.

### 2.3 Database

With MySQL (XAMPP), create a database named `bakr` and set in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bakr
DB_USERNAME=root
DB_PASSWORD=
```

Then:

```sh
php artisan migrate
php artisan db:seed --class=ScholarshipSeeder
```

**Campuses and programs:** the `campuses` and `programs` tables have no seeder. Add DNSC's campuses and programs to the database, then check how each program is classified:

```sh
php artisan bakr:check-programs
```

Any program listed as `unrecognized` needs a pattern in `config/program_fields.php`.

### 2.4 Email (contact form)

For development, write emails to `storage/logs/laravel.log`:

```env
MAIL_MAILER=log
BAKR_CONTACT_TO=bakr.dnsc@gmail.com
```

For real sending through Gmail (with an App Password), see [docs/CONTACT-SETUP.md](docs/CONTACT-SETUP.md).

### 2.5 AI explainer (optional)

```env
BAKR_AI_KEY=your-anthropic-api-key
BAKR_AI_MODEL=claude-haiku-5-5
```

Run `php artisan config:clear` after changing `.env`. If no key is set, the "Explain with AI" buttons are hidden and the built-in explanations are used.

### 2.6 Run

```sh
php artisan serve
```

Open http://localhost:8000. You can also serve the `public/` folder through XAMPP's Apache.

### 2.7 Tests

```sh
php artisan test                 # everything
php artisan test --filter=Unit   # matching engine only (no database)
```

As of 2026-11-09, all 30 tests pass. The legacy document-upload test was removed with the Phase 3 code; see `docs/ROADMAP.md`.

---

## 3. Maintaining scholarships

1. Edit `config/scholarships.php`. Add or change a scholarship's rules and keep its `slug` stable.
2. Run `php artisan db:seed --class=ScholarshipSeeder`. Rows are updated by slug.
3. Existing students' matches update the next time they save changed answers.

When a provider publishes a real cut-off (for example a DNSC GWA minimum), fill in `min_percent` on the matching `confirm` rule.

**Known data gaps:**
- BAKR couldn't read the DOST-SEI and BPMS sites or the DNSC Facebook page. Those rules come from the scholarships page, and their thresholds show as "confirm with the provider".
- Some application windows have closed (CMSP, BPMSP, GrabScholar, DOST 2027). Cards show the last known period.

---

## 4. Security notes

- Passwords are hashed. Sessions are regenerated on login and invalidated on logout. Forms are CSRF protected.
- Rate limits apply to login, registration, contact and AI requests.
- Student data is always looked up by the signed-in user, never by an ID from the URL.
- **Not production-ready yet:**
  - The scholar and admin portals have no authentication.
  - There's no password reset or email verification.

  See `docs/ROADMAP.md`.

---

## 5. Further documentation

- [CONTRIBUTING.md](CONTRIBUTING.md): setup and workflow for collaborators
- [docs/sources/](docs/sources/): the official scholarship documents (screenshots + NOTES.md) behind every rule
- [CLAUDE.md](CLAUDE.md): guide for AI coding assistants working on this repo
- [docs/ROADMAP.md](docs/ROADMAP.md): planned features and improvements
- [docs/changes/](docs/changes/): log of changes per work session
- [docs/CONTACT-SETUP.md](docs/CONTACT-SETUP.md): contact form and email setup

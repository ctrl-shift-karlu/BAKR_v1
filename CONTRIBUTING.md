# Contributing to BAKR

Welcome! BAKR is a Laravel app that matches Davao del Norte State College (DNSC) students to scholarships. This guide gets you from a fresh clone to a running copy, then explains how to work without breaking anything.

**Read [CLAUDE.md](CLAUDE.md) next.** It has the architecture, the rules that must never be broken, and the conventions.

---

## 1. What you need

| Tool | Version | Notes |
|---|---|---|
| **PHP** | **8.4 or newer** | The installed packages refuse to run on older PHP. **XAMPP's bundled PHP is 8.2, which is too old**, so install PHP 8.4+ separately and make sure `php -v` shows it. |
| Composer | 2.x | https://getcomposer.org |
| MySQL / MariaDB | any recent | XAMPP's MySQL is fine (only its PHP is too old) |
| Git | any | |
| Node / npm | not needed | BAKR has no front-end build: CSS and JS are plain files in `public/`. |

PHP extensions needed: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`, and `pdo_sqlite` (for the tests).

## 2. Get the code

```sh
git clone https://github.com/MsAbella-27/BAKR.git
cd BAKR
composer install
```

## 3. Your `.env` file (never commit it)

```sh
cp .env.example .env
```

The repository owner sends you privately (chat or in person, **never** through GitHub):

| Key | What it is |
|---|---|
| `APP_KEY` | **Use the owner's key, not a new one, if you use the owner's database dump.** Student answers are encrypted with it. With a different key they cannot be read. Only run `php artisan key:generate` if you start with an empty database (step 4, option B). |
| `DB_*` | Your own MySQL login (`DB_DATABASE=bakr`) |
| `BAKR_GEMINI_KEY` | Optional. The AI explanations. Leave it empty and BAKR uses built-in text instead. |
| `MAIL_*` | Optional. Only needed to test the Contact form email. |
| `PROVIDER_*_EMAIL` / `_PASSWORD` | Logins for the five provider accounts (CHED, DOST, Cebuana, DNSC, Grab). Choose your own for your local copy. |

After any change to `.env`, run `php artisan config:clear`.

## 4. The database

Create an empty MySQL database named `bakr` (phpMyAdmin, or `CREATE DATABASE bakr;`). Then choose **one**:

**A. The owner's dump (recommended).** It includes the DNSC campuses and programs, which have no seeder.
```sh
mysql -u root bakr < bakr-dump.sql      # the .sql file the owner sent you
php artisan migrate                     # applies any migration newer than the dump
```
Remember: with a dump, use the owner's `APP_KEY`.

**B. An empty database.** Use this if you have no dump.
```sh
php artisan key:generate
php artisan migrate
php artisan db:seed --class=ScholarshipSeeder       # the 14 scholarships
php artisan db:seed --class=ProviderAccountSeeder   # provider logins from your .env
```
The `campuses` and `programs` tables stay empty. The student form needs them, so ask the owner for those rows.

## 5. Run it

```sh
php artisan serve        # http://localhost:8000
```

- Student: register at `/studentRegister`.
- Provider: `/providerLogin`, with a `PROVIDER_*` login from your `.env`.
- The scholar and admin pages are **static mock-ups** with no real login or data.

## 6. Run the tests

```sh
php artisan test          # must stay green (all pass) before you push
vendor/bin/pint --dirty   # code style for the PHP files you changed
```

The tests use an in-memory SQLite database. They never touch your MySQL data.

---

## How we work

### Branches and pull requests
1. Start from an up-to-date `main`: `git checkout main && git pull`.
2. Make a branch per task: `git checkout -b feature/short-name` (or `fix/...`).
3. Commit small, clear steps. Run the tests before every push.
4. Push the branch and open a **pull request into `main`**. Describe what changed and how you checked it.
5. **Don't push straight to `main`.** **Don't force-push** a branch someone else is using.

### Log your work
Every work session gets a short note in `docs/changes/`, named `YYYY-MM-DD-NN-topic.md`. See `docs/changes/README.md`. Say what changed, why, and how you checked it.

### Rules that must never be broken
(Full list in [CLAUDE.md](CLAUDE.md#non-negotiable-rules-privacy--safety).)
- **The rule engine decides eligibility. The AI only explains.**
- **Never send names, emails or free text to the AI.**
- **Only collect what a scholarship rule needs** (Data Privacy Act of 2012).
- **Never invent a number** (GWA, income limit, deadline). If the official source doesn't publish it, use a `confirm` rule.
- **Never commit** `.env`, API keys, passwords or database dumps. Backups go in `storage/app/private/backups/`, which git ignores.
- **Never change `APP_KEY`** on a database that already has student answers.

### Scholarship information
- The official documents are in **`docs/sources/<provider>/<document>/`**: the page screenshots plus a `NOTES.md` summary. **Read `NOTES.md` before changing a scholarship.**
- Scholarship data lives in `config/scholarships.php` (the official version) and the `scholarships` table (what the site shows; providers edit it in the portal).
- **To update one scholarship in the database:** run `php artisan bakr:sync-scholarship <slug>` to preview, then add `--apply` (it also adds a scholarship that is new in the config). **Don't run `ScholarshipSeeder` on a database with real data**: it overwrites all 14 scholarships, including provider edits.
- New official document? Add a folder under `docs/sources/`, write its `NOTES.md`, and list it in `docs/sources/README.md`.

### Front-end conventions
- Plain Blade plus CSS/JS files in `public/css` and `public/js`. **No inline `<style>` or `<script>`.** Pass values through `data-*` attributes.
- Use the type-scale tokens (`var(--text-sm)` and so on) and the existing dashboard components (`.dash-card`, `.dash-btn` and so on).
- Every new style needs a dark-mode version in `public/css/bakr-dark.css`.
- Results use words (Eligible, Nearly eligible…), never percentages.

## Troubleshooting

| Problem | Fix |
|---|---|
| "Your Composer dependencies require a PHP version >= 8.4.1" | Your `php` is too old (often XAMPP's 8.2). Install PHP 8.4+ and use `php artisan serve` instead of XAMPP's Apache. |
| Student answers show garbled or "The payload is invalid" | Your `APP_KEY` doesn't match the dump. Use the owner's key. |
| Student form has no campuses or programs | The `campuses` / `programs` tables are empty. Import the owner's dump. |
| Large dashboards look half-styled with `php artisan serve` | Reload. On Windows the dev server sometimes cuts off big files. |
| Contact email fails with "certificate verify failed" | Antivirus email scanning (for example AVG Mail Shield) is intercepting it. Turn that scanning off; never disable SSL verification. See `docs/CONTACT-SETUP.md`. |
| AI explanations don't appear | Normal without `BAKR_GEMINI_KEY`: the built-in explanation is shown instead. |

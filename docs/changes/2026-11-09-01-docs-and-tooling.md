# 2026-11-09 — Project docs + agent tooling setup

**Roadmap item(s):** none (setup session before the roadmap existed)

**Application code changed:** none. No files under `app/`, `routes/`, `resources/`, `config/`, `database/`, `public/`, `tests/` or `bootstrap/`, and no `composer.json`/`package.json` changes.

## Changed
- `README.md`: rewritten as the full system description + setup guide. Original is in `backups/README.original.md`.
- `CLAUDE.md`: replaced the Laravel Boost bootstrap text with project guidance (architecture, rules, commands, gotchas, installed skills). Original is in `backups/CLAUDE.original.md`.

## Added
- `docs/ROADMAP.md`: architecture analysis + phased roadmap (P0 stabilise → security → back office → student UX → frontend → ops).
- `docs/changes/`: this change-log folder (`README.md` explains the format and session hygiene).
- `.claude/skills/` (project-level skills, 20 folders):
  - `playwright-cli`: installed by `playwright-cli install --skills`
  - `impeccable`: from pbakaus/impeccable (`.claude/skills/impeccable`)
  - Emil Kowalski (emilkowalski/skills), 14 web-relevant skills: `emil-design-eng`, `animate`, `animation-performance`, `animation-vocabulary`, `apple-design`, `break-ui`, `choreograph`, `find-animation-opportunities`, `gestures`, `improve-animations`, `pick-ui-library`, `prototype`, `remove-animations`, `review-animations`. Skipped `write-swift`, `mobile-native`, `animate-expo` and `ask-sonner` (iOS, React Native and React only).
  - `ui-ux-pro-max`: from nextlevelbuilder/ui-ux-pro-max-skill (repo's dev test suite left out)
  - `awesome-design-md`: 84 DESIGN.md files from VoltAgent/awesome-design-md, plus a SKILL.md written for this project (the repo has none)
  - `prompt-master`: from nidhinjs/prompt-master
  - `owasp-security`: from agamm/claude-code-owasp
- Global: `npm install -g @playwright/cli` (v0.1.23) uses the installed Chrome. Playwright also downloaded ffmpeg/winldd to `%LOCALAPPDATA%\ms-playwright`.

## How skills were installed
Git isn't installed, so `npx skills add` failed (`spawn git ENOENT`). Each repo was downloaded as a GitHub zip into the session scratchpad and checked: SKILL.md frontmatter, file types, and a grep for network calls or prompt injection. Only the skill folders were copied in. Nothing suspicious was found. Impeccable's launcher downloads its engine from its own release URL on first use.

## Verified
- `php artisan test` → 30/37 passing. The 7 failures are all in the legacy `tests/Feature/StudentApplicationTest.php` ("Class Database\Factories\UserFactory not found"). They existed before this session.
- `playwright-cli --version` → 0.1.23

## Not done / follow-ups
- **Laravel Boost** (requested by the old CLAUDE.md/AGENTS.md) was not installed, because it modifies `composer.json` and you asked for no codebase changes. `AGENTS.md` still contains the Boost bootstrap text. If you want it, back up CLAUDE.md first, since `boost:install` may regenerate it.
- Install **Git** (roadmap Phase 0, item 1). It also lets `npx skills add` and updates work normally.
- Install **Python 3** if you want ui-ux-pro-max's search scripts.
- Skills are not auto-updated. Re-download them to update.

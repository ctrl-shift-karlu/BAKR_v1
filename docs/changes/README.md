# Change log folder

Every work session gets one file here, so you can see what changed, when, and why, even without git.

## Naming

`YYYY-MM-DD-NN-short-topic.md`, for example `2026-11-10-01-git-and-cleanup.md`

## Template

```md
# <date> — <topic>

**Roadmap item(s):** <link or name from docs/ROADMAP.md>

## Changed
- path/to/file — what and why

## Added
- ...

## Removed
- ...

## Verified
- php artisan test → N/N passing
- manual check: ...

## Follow-ups
- ...
```

## `backups/`

Copies of files before they were replaced (for example the original `README.md` and `CLAUDE.md`).

## Session hygiene (to keep usage low)

- **One roadmap item per conversation.** Start with: *"Read CLAUDE.md and docs/ROADMAP.md, then do …"*
- **`/compact`** when a conversation gets long but you're still on the same task. It summarises the history and keeps going.
- **`/clear`** when you finish a task or switch topics. It starts fresh, and CLAUDE.md is reloaded automatically.
- Write the log entry **before** you `/clear`. This folder is the memory between sessions.
- Point Claude at specific files instead of asking it to "look at everything". The big prototype views (`adminDashboardF`, `officerDashboardF`, `scholarDashboardF`) cost a lot to read.

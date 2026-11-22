# 2026-11-13 — Text size hierarchy; sparkle icons removed

## Type scale
About 40 different font sizes were in use, many only a hair apart (.84 / .85 / .86 / .88 / .9rem). Some labels were 9–11px. Important and minor text looked the same.

Every text size in the shared stylesheets now uses one scale. The tokens live in `bakr-core.css` `:root` and are repeated in `bakr-dashboard.css` and `student-auth.css`, because the provider portal, the login pages and the staff prototypes don't load bakr-core.

| Token | Size | Use |
|---|---|---|
| `--text-xs` | 12px | Uppercase labels, eyebrows, kickers, badges, pills. **The smallest text anywhere.** |
| `--text-caption` | 13px | Meta: hints, timestamps, footers, legends, form labels in dashboards |
| `--text-sm` | 14px | Secondary body: table cells, list rows, descriptions |
| `--text-md` | 16px | Body, item titles, lead paragraphs, auth inputs |
| `--text-title` | 18px | Card titles |
| `--text-lg` | 20px | Section titles |
| `--text-xl` / `--text-2xl` | 24 / 32px | Display |

- **Converted:** 296 declarations across bakr-dashboard, bakr-provider, student-portal, bakr-core, bakr-layout, bakr-pages, bakr-home, student-auth, bakr-scholarships, bakr-about and bakr-contact.
- **Left alone:**
  - headings above 22px (their `clamp()` sizes);
  - icon sizes, avatar initials, SVG chart text, the brand wordmark;
  - the large staff prototype stylesheets (admin/officer/scholar).
- **Exceptions:**
  - Sidebar user name, dashboard field labels and auth bullet points step down, so they sit below the text they support.
  - List rows, the navbar dropdown titles and the sector tab titles step up to 14px, so they sit above their 12px subtitles.
  - Table row inputs are 13px.
- **Checked** in the browser at 1440px and 375px: public pages, logins, the provider portal and the student portal. No horizontal scroll and no new overflow. The sidebar "Need info" tile already wrapped before this change.

**Rule from now on:** new CSS uses `font-size: var(--text-*)`, not raw px/rem, and nothing goes below `--text-xs`.

## Sparkle icons removed
The `bi-stars` sparkle looked like an "AI" badge. It was replaced by an icon that says what each control does:

| Where | Now |
|---|---|
| "Show my matches", "Save and find my matches" | `bi-search` |
| "See all matches", "My matches" (student nav), About step "See your matches" | `bi-list-check` |
| "Quick / AI explanation" tags, "Explain with AI" buttons | `bi-chat-left-text` |
| "Open my saved matches" (Finder) | `bi-bookmark-check` |
| "See my result" (catalogue) | `bi-clipboard-check` |
| Login/register point "Tell BAKR about your studies once" | `bi-ui-checks` |
| Questionnaire section "Talents, leadership, and service" | `bi-trophy` |
| GIA Category A modal | `bi-palette` |
| Home "YOUR NEXT CHAPTER STARTS HERE" eyebrow | icon removed |

- Also removed: the floating ✳ ＋ ◇ symbols around the home intro (markup and CSS), and the smiley icons in the provider "nothing to review" messages (now `bi-check2-circle`).
- No emoji characters remain in views, CSS or JS. The staff prototype JS keeps its "✓" check marks.

Tests: 104/104 pass.

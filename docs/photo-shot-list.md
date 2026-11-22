# BAKR photo shot list

These are the photos to take for the BAKR website. They replace the borrowed pictures now on the site.

**How to use this list**
1. Take each photo below.
2. Rename it to the exact name in the **Save as** column: lowercase, hyphens, `.jpg`.
3. Put every photo in one folder: `public/images/photos/`.
4. Tick the box when it's done. When all are in, the site's image links are switched over to these names in one go.

---

## Before you shoot

- [ ] **Consent.** Get written consent from anyone whose face can be recognised. The photos go on a public website, so this is required under the Data Privacy Act of 2012. Wide shots, backs of heads and distant crowds avoid most of this.
- [ ] **No personal details in the frame.** No IDs, name tags, computer screens or papers with names or numbers.
- [ ] **Daylight.** Shoot in good natural light.
- [ ] **Landscape.** Hold the phone or camera sideways for everything except the team portraits.
- [ ] **File size.** Keep each file under about 400 KB, or send the originals and they'll be compressed.

---

## A. Wide banner shots

**Size:** landscape, at least **1920 × 1080**.
**Tip:** leave empty space on the **left third** of the picture, because the page title is printed on top there.

| ✓ | # | Save as | What to capture | Where it appears on the site |
|---|---|---|---|---|
| ☐ | 1 | `campus-main-building.jpg` | Wide shot of the main DNSC building or campus grounds, daytime | Home page banner · student login, register and password pages · background of every dashboard · About page · provider Overview · student Dashboard |
| ☐ | 2 | `campus-main-gate.jpg` | The DNSC main gate with the college name visible | Scholarships page banner · Contact page · provider login · "My scholarships" · student Profile |
| ☐ | 3 | `campus-gate-side-view.jpg` | A second angle of the gate or the entrance area | About page · scholarship photo cards |
| ☐ | 4 | `graduation-ceremony.jpg` | Graduation: caps in the air, or graduates in togas | Scholarship Finder · Home page · Disbursements banner · student "My scholarship" |
| ☐ | 5 | `students-group-outdoors.jpg` | A group of students talking or walking on campus | Applicants banner · student "My matches" · Home gallery · About page |
| ☐ | 6 | `student-studying.jpg` | One student reading or writing (a side or back view is fine) | Scholars banner · Home gallery |
| ☐ | 7 | `library-interior.jpg` | Inside the library, with shelves and tables | Requirements banner · student questionnaire · Home gallery |

---

## B. Section and card photos

**Size:** landscape, at least **1200 × 800**.

| ✓ | # | Save as | What to capture | Where it appears on the site |
|---|---|---|---|---|
| ☐ | 8 | `computer-laboratory.jpg` | A computer laboratory in use | About page |
| ☐ | 9 | `scholarship-office.jpg` | The Scholarship and Grants Unit: office door, sign or front desk | About page ("where to submit") |
| ☐ | 10 | `talent-music-dance-arts-sports.jpg` | Students performing: band, dance troupe, artwork, or a varsity game | Home page card for **GIA Category A** (talent) |
| ☐ | 11 | `student-leaders-publication.jpg` | Student council officers, or the campus publication staff at work | Home page card for **GIA Category B** (leadership) |

---

## C. Team portraits

**Size:** square, at least **800 × 800**.
**Tip:** use the same plain background and the same framing (head and shoulders) for everyone.

| ✓ | Save as | Who |
|---|---|---|
| ☐ | `team-member-1.jpg` | |
| ☐ | `team-member-2.jpg` | |
| ☐ | `team-member-3.jpg` | |
| ☐ | `team-member-4.jpg` | |
| ☐ | `team-member-5.jpg` | |

Add more rows (`team-member-6.jpg`, …) if the team is bigger. Write each person's name in the **Who** column so the site shows the right name under each photo.

---

## Do NOT photograph these

Use the official image files instead. A photo of a logo looks blurry and may break the owner's usage rules.

- The **DNSC seal**
- The **BAKR logo**
- Provider logos: **CHED, DOST, Grab, Cebuana Lhuillier**

---

## Reference: what each new photo replaces

For whoever updates the site afterwards. Current files are under `public/images/img/`.

| New file (`public/images/photos/`) | Replaces |
|---|---|
| `campus-main-building.jpg` | `dnsc_campus.jpg` |
| `campus-main-gate.jpg` | `dnsc_gate.jpg`, `opt/dnsc_gate.jpg` |
| `campus-gate-side-view.jpg` | `opt/gate2.jpg` |
| `graduation-ceremony.jpg` | `graduates.jpg` |
| `students-group-outdoors.jpg` | `opt/students2.jpg` |
| `student-studying.jpg` | `opt/student.jpg` |
| `library-interior.jpg` | `opt/lib.jpg` |
| `computer-laboratory.jpg` | `lab.jpg` |
| `scholarship-office.jpg` | `scholarship-office.jpg` (placeholder, no photo yet) |
| `talent-music-dance-arts-sports.jpg` | `catA.jpe` |
| `student-leaders-publication.jpg` | `opt/catB.jpg` |
| `team-member-N.jpg` | `team/member-N.jpg` |

The pictures are referenced from the Blade views in `resources/views/` and the stylesheets in `public/css/`. Search for `images/img/` to find every place.

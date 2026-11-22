# BAKR — Application form, matching, student dashboard, AI explainer

Copy the folders into your Laravel project (keep paths), then:

1. **Routes.** In routes/web.php, remove the old student routes (application show/store, documents) and paste the contents of `routes_student.php`.
2. **Middleware alias** `role` must exist (bootstrap/app.php): `->withMiddleware(fn ($m) => $m->alias(['role' => \App\Http\Middleware\RoleMiddleware::class]))`
3. **Migrate and seed the scholarships**
   ```
   php artisan migrate
   php artisan db:seed --class=ScholarshipSeeder
   ```
   `config/scholarships.php` holds the 11 scholarships and their rules. Re-run the seeder after editing it; it updates by slug.
4. **AI explainer (optional).** Add to `.env`, then `php artisan config:clear`:
   ```
   BAKR_AI_KEY=your-anthropic-api-key
   BAKR_AI_MODEL=claude-haiku-5-5
   ```
   With no key, every page still works and shows the built-in explanation. The "Explain with AI" buttons appear only when a key is set.
5. Make sure `APP_KEY` is set (answers are encrypted with it). **Changing APP_KEY later makes saved answers unreadable.**
6. Hard-refresh the browser (Ctrl+F5).

## How it works
- **Form** (`/student/application`): 6 short steps built from `app/Matching/ApplicationSchema.php`. No documents, no name or address; only what a scholarship rule needs. Questions that do not apply are hidden.
- **Matching** (`app/Matching`): rules decide. Each scholarship gets a status (likely eligible, possible, needs more information, not a match) and a closeness score. If a source gives no threshold, the rule says "confirm with the provider" instead of guessing.
- **AI** (`app/Ai`): explains only. It receives rule results, never name, email, or free text, and cannot change a status. Replies are cached until the student changes answers. If the AI is down the page says so and shows the built-in text.
- **Dashboard** `/student/dashboard`, **matches** `/student/results`, **profile** `/student/profile` (answers, saved versions, "Redo or change answers"). Saving changed answers creates a new version and matches again.
- Answers are stored encrypted. Every student query is scoped to the signed-in user.

## Things to know
- Two sources could not be read (DOST-SEI and BPMS sites, DNSC Facebook page). Those rules come from your scholarships page; DNSC GWA cut-offs, DOST grade/income limits are shown as "confirm with the provider". Add real numbers in `config/scholarships.php` (`min_percent`) when the DNSC unit gives them.
- Several application windows have already closed (CMSP, BPMSP, GrabScholar, DOST 2027). Matching says whether you fit; the card shows the last known period.
- Old Phase 3 document-upload files are no longer used. You can delete: StudentDocumentController, ApplicationDocumentPolicy, the old StudentApplicationService/Request, models StudentApplication/ApplicationDocument/DocumentType, DocumentTypeSeeder, and the old student-application.css/js.
- Tests (pure PHP, no database): `php artisan test --filter=Unit`

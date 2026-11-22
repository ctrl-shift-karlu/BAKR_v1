# Update: real DNSC programs + site-matching design

## Programs now come from your database
- The form asks for Campus, then Program. The program list shows only programs of that campus (campuses and programs tables).
- BAKR works out the scholarship "field" (IT, Criminology, Education...) from the program name using config/program_fields.php.
- A program that matches nothing is treated as "confirm with the provider" instead of being rejected.
- Check your data:  php artisan bakr:check-programs   (lists every program with its mapped field; add a line to config/program_fields.php for any 'unrecognized').
- Students who already answered keep their results. Edit and save once to move them to the new program question.

## Design
- Student pages (dashboard, matches, profile, application) now extend layouts.app: same navbar, hero and footer as Home/About/Contact.
- Needs from the site package: layouts/app.blade.php, css/bakr-pages.css, images/img/dnsc_gate.jpg.

## Copy these
app/Matching/{ApplicationSchema,ProgramCatalog,RuleEvaluator}.php
app/Console/Commands/CheckPrograms.php
app/Http/Requests/ScholarshipApplicationRequest.php
app/Http/Controllers/Student/StudentApplicationController.php
app/Services/ScholarshipApplicationService.php
config/program_fields.php
resources/views/student/{layout,application,dashboard,results,profile}.blade.php
resources/views/student/partials/field.blade.php
public/css/student-portal.css, public/js/student-form.js
tests/Unit/MatchingPipelineTest.php

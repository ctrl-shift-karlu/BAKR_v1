<?php

namespace Database\Seeders;

use App\Models\Scholar;
use App\Models\ScholarRequirement;
use App\Models\Scholarship;
use App\Models\User;
use App\Support\AcademicTerm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * DEMO DATA ONLY — fictional scholars so the Provider Portal can be tried out.
 *
 *   php artisan db:seed --class=DemoScholarSeeder
 *
 * - Refuses to run when APP_ENV=production.
 * - Every demo account uses an @demo.bakr.test e-mail, so it is easy to find and delete.
 * - Password: DEMO_SCHOLAR_PASSWORD from .env; if empty, a random one nobody knows.
 * - Stipend amounts below are DEMO values, not published figures. Providers set real ones.
 * - If a scholarship has no renewal requirements yet, a generic demo checklist is added;
 *   providers replace it under "My scholarships".
 * Safe to re-run: existing demo users and records are reused.
 */
class DemoScholarSeeder extends Seeder
{
    private const PER_SCHOLARSHIP = 6;

    private const DEMO_STIPEND = 5000;

    private const DEMO_REQUIREMENTS = ['Certificate of Registration (COR)', 'Grades for the previous semester'];

    private const FIRST = ['Angelo', 'Bea', 'Carlo', 'Danica', 'Elijah', 'Faith', 'Gabriel', 'Hannah', 'Ivan', 'Jasmine', 'Kevin', 'Lea', 'Marco', 'Nicole', 'Oscar', 'Patricia', 'Rafael', 'Sofia', 'Tristan', 'Vanessa'];

    private const LAST = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Garcia', 'Mendoza', 'Torres', 'Villanueva', 'Ramos', 'Aquino', 'Castillo', 'Navarro', 'Dela Cruz', 'Flores', 'Gonzales'];

    private const PROGRAMS = ['BS Information Technology', 'BS Civil Engineering', 'BS Accountancy', 'BS Agriculture', 'BS Hospitality Management', 'Bachelor of Elementary Education', 'BS Marine Biology', 'BS Psychology'];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('DemoScholarSeeder does not run in production.');

            return;
        }

        // Hash once: every demo student shares the same password.
        $passwordHash = Hash::make((string) config('providers.demo_password') ?: Str::random(40));
        $now = Carbon::now();
        // DNSC has two semesters and no Midyear: requirements and stipends use the same semesters.
        $current = AcademicTerm::current($now);
        $previous = AcademicTerm::previous($now);
        $n = 0;

        foreach (Scholarship::where('is_active', true)->orderBy('sort_order')->get() as $si => $scholarship) {
            if (empty($scholarship->renewal_requirements)) {
                $scholarship->update(['renewal_requirements' => self::DEMO_REQUIREMENTS]);
            }
            $requirements = $scholarship->maintainingDocuments();

            // Scholars only: there are no applicants in BAKR (providers add the scholars they already have).
            for ($k = 0; $k < self::PER_SCHOLARSHIP; $k++, $n++) {
                $user = $this->demoUser($n, $passwordHash);

                // Only active / terminated are stored; "inactive" follows from overdue documents.
                $status = $k === 5 && $si % 2 ? 'terminated' : 'active';
                $scholar = Scholar::firstOrCreate(
                    ['user_id' => $user->id, 'scholarship_id' => $scholarship->id],
                    [
                        'student_number' => sprintf('DEMO-%05d', 10000 + $n),
                        'program' => self::PROGRAMS[$n % count(self::PROGRAMS)],
                        'year_level' => (string) (1 + ($n % 4)),
                        'status' => $status,
                        'started_term' => $previous,
                        'stipend_amount' => self::DEMO_STIPEND,
                    ],
                );

                if (! $scholar->isInGoodStanding()) {
                    continue;
                }

                foreach ($requirements as $ri => $doc) {
                    $name = $doc['name'];
                    $settings = ScholarRequirement::settingsFrom($doc);
                    $scholar->requirements()->firstOrCreate(['term' => $previous, 'name' => $name], [
                        'status' => 'approved', 'reviewed_at' => $now->copy()->subMonths(4),
                        'due_on' => AcademicTerm::endDate($previous)?->toDateString(),
                    ] + $settings);

                    $state = ['approved', 'submitted', 'missing', 'needs_revision'][($k + $ri) % 4];
                    // Scholar #4 of each scholarship had an earlier due date and missed it: shows as Inactive.
                    $due = $k === 4 ? $now->copy()->subWeek() : AcademicTerm::endDate($current);
                    $row = $scholar->requirements()->firstOrCreate(['term' => $current, 'name' => $name], ['status' => 'missing', 'due_on' => $due?->toDateString()] + $settings);
                    if ($row->wasRecentlyCreated && $state !== 'missing') {
                        $path = 'scholar-requirements/'.$scholar->id.'/'.Str::random(40).'.pdf';
                        Storage::disk('local')->put($path, $this->placeholderPdf($name));
                        $row->update([
                            'status' => $state,
                            'file_path' => $path,
                            'original_name' => Str::slug($name).'-demo.pdf',
                            'mime' => 'application/pdf',
                            'size' => Storage::disk('local')->size($path),
                            'submitted_at' => $now->copy()->subDays(2 + $ri),
                            'reviewed_at' => $state === 'submitted' ? null : $now->copy()->subDay(),
                            'review_note' => $state === 'needs_revision' ? 'Demo: the scan is blurry, please upload a clearer copy.' : null,
                        ]);
                    }
                }

                // Stipends follow the release rule: only when every requirement of that semester is approved.
                // Last semester: everything approved, so released.
                $scholar->disbursements()->firstOrCreate(['term' => $previous, 'period_month' => ''], [
                    'amount' => self::DEMO_STIPEND, 'status' => 'released', 'released_at' => $now->copy()->subMonths(4), 'reference' => 'DEMO-'.($si + 1).'-A',
                ]);
                // This semester: released only for scholars whose requirements are all approved; a mix otherwise.
                $canRelease = $scholar->releaseBlocker($current) === null;
                if ($k % 4 === 3 && ! $scholar->disbursements()->where('term', $current)->exists()) {
                    // Paid per month: months already past are released (when allowed).
                    foreach (AcademicTerm::months($current) as $mi => $month) {
                        $paid = $canRelease && $month < $now->format('Y-m');
                        $scholar->disbursements()->create([
                            'term' => $current, 'release_type' => 'monthly', 'period_month' => $month,
                            'amount' => self::DEMO_STIPEND, 'status' => $paid ? 'released' : 'pending',
                            'released_at' => $paid ? Carbon::createFromFormat('!Y-m', $month)->endOfMonth() : null,
                            'reference' => $paid ? 'DEMO-'.($si + 1).'-M'.($mi + 1) : null,
                        ]);
                    }
                } else {
                    $pay = $canRelease ? 'released' : ['pending', 'pending', 'on_hold'][$k % 3];
                    $scholar->disbursements()->firstOrCreate(['term' => $current], [
                        'amount' => self::DEMO_STIPEND, 'status' => $pay,
                        'released_at' => $pay === 'released' ? $now->copy()->subDays(3) : null,
                        'reference' => $pay === 'released' ? 'DEMO-'.($si + 1).'-B' : null,
                    ]);
                }
            }
        }

        $this->command?->info("Demo data ready ({$n} demo scholar accounts, e-mails end in @demo.bakr.test; sign in at /scholarLogin).");
        if (! config('providers.demo_password')) {
            $this->command?->warn('DEMO_SCHOLAR_PASSWORD is empty, so demo scholars got a random password. Set it in .env and re-run to sign in as one.');
        }
    }

    private function demoUser(int $n, string $passwordHash): User
    {
        $first = self::FIRST[$n % count(self::FIRST)];
        $last = self::LAST[intdiv($n, count(self::FIRST)) % count(self::LAST)];
        $email = sprintf('demo.%03d@demo.bakr.test', $n + 1);

        $user = User::where('email', $email)->first() ?? new User;
        $user->name = "{$first} {$last}";
        $user->email = $email;
        $user->password = $passwordHash;
        $user->role = 'scholar';   // scholars have their own accounts (Scholar login)
        $user->status = 'active';
        $user->save();

        return $user;
    }

    /** A tiny valid one-page PDF that just says it is a demo file. */
    private function placeholderPdf(string $title): string
    {
        $text = 'BAKR demo file: '.preg_replace('/[^A-Za-z0-9 ()\/.-]/', '', $title);
        $stream = "BT /F1 14 Tf 60 760 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$obj}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}

<?php

use App\Support\AcademicTerm;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Owner's decisions, 2026-11-21:
 *  - Every scholarship has its OWN maintaining-documents deadline for EACH semester (scholarship_deadlines,
 *    one row per scholarship × semester). Saving it opens that semester's documents for the scholarship's
 *    scholars and e-mails them; the old "Open / Update checklist" button is gone.
 *  - Every scholar has an accurate "Scholar since" semester (scholars.started_term): no documents and no
 *    stipends for a semester before it.
 *
 * Backfill: each scholarship × semester that already has checklist items gets a deadline (the latest due date
 * of its items, else the semester's end), so open checklists keep working. A scholar without a valid
 * started_term gets the earliest semester found among their own records (checklist items, stipends) or
 * the semester they were added in, so nothing they already have falls before their start.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->string('term', 60);
            $table->date('due_on');
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['scholarship_id', 'term']);
        });

        // Deadlines for the checklists that are already open.
        DB::table('scholar_requirements')
            ->join('scholars', 'scholars.id', '=', 'scholar_requirements.scholar_id')
            ->selectRaw('scholars.scholarship_id as sid, scholar_requirements.term as term, MAX(scholar_requirements.due_on) as due')
            ->groupBy('scholars.scholarship_id', 'scholar_requirements.term')
            ->get()
            ->each(function ($row) {
                $due = $row->due ?: AcademicTerm::endDate((string) $row->term)?->toDateString();
                if ($due && AcademicTerm::indexOf((string) $row->term) !== null) {
                    DB::table('scholarship_deadlines')->insert([
                        'scholarship_id' => $row->sid, 'term' => $row->term, 'due_on' => Carbon::parse($due)->toDateString(),
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            });

        // An accurate "Scholar since" for every scholar.
        DB::table('scholars')->select(['id', 'started_term', 'created_at'])->orderBy('id')->each(function ($s) {
            $terms = collect([$s->started_term])
                ->merge(DB::table('scholar_requirements')->where('scholar_id', $s->id)->distinct()->pluck('term'))
                ->merge(DB::table('stipend_disbursements')->where('scholar_id', $s->id)->distinct()->pluck('term'))
                ->push(AcademicTerm::current($s->created_at ? Carbon::parse($s->created_at) : now()))
                ->filter(fn ($t) => AcademicTerm::indexOf($t) !== null);
            $earliest = $terms->sortBy(fn ($t) => AcademicTerm::indexOf($t))->first();
            if ($earliest !== $s->started_term) {
                DB::table('scholars')->where('id', $s->id)->update(['started_term' => $earliest]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_deadlines');
        // started_term values are kept: they are valid semesters either way.
    }
};

<?php

namespace App\Services\Provider;

use App\Models\Scholar;
use App\Models\ScholarRequirement;
use App\Models\Scholarship;
use App\Models\ScholarshipDeadline;
use App\Models\User;
use App\Services\ScholarNotifier;
use App\Support\AcademicTerm;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Maintaining-document checklists, per scholarship and per semester (owner, 2026-11-21; replaces the
 * old "Open / Update checklist" button).
 *
 *   - The provider sets each scholarship's deadline for each semester (setDeadline). Saving it opens that
 *     semester's checklist: every scholar of the scholarship who is in good standing and had started by
 *     then ("Scholar since") gets one item per maintaining document, due on that date. Items not approved
 *     yet move to a new date. The scholars are told (in BAKR + e-mail) when the date is new or changed.
 *   - The checklist then keeps itself up to date: a scholar who is added, reinstated or whose
 *     "Scholar since" changes gets the semesters that already have a deadline (syncScholar); a document
 *     added under My scholarships joins the checklists of the current and coming semesters (syncScholarship).
 *
 * Nothing here ever deletes an item that has a file or a review.
 */
class RequirementChecklist
{
    /** The scholars who owe documents for $term: in good standing and started by then. */
    public static function scholarsFor(Scholarship $scholarship, string $term): HasMany
    {
        return $scholarship->scholars()->whereIn('status', Scholar::GOOD_STANDING)->startedBy($term);
    }

    /**
     * Saves the deadline of one scholarship for one semester, opens / updates that semester's checklist and
     * tells the scholars: everyone when the date is new or changed, otherwise only scholars who just got items.
     *
     * @return array{changed: bool, created: int, notified: int}
     */
    public function setDeadline(Scholarship $scholarship, string $term, string $dueOn, User $by): array
    {
        $dueOn = Carbon::parse($dueOn)->toDateString();

        [$changed, $first, $result] = DB::transaction(function () use ($scholarship, $term, $dueOn, $by) {
            $deadline = ScholarshipDeadline::firstOrNew(['scholarship_id' => $scholarship->id, 'term' => $term]);
            $first = ! $deadline->exists;
            $changed = $first || $deadline->due_on?->toDateString() !== $dueOn;
            if ($changed) {
                $deadline->fill(['due_on' => $dueOn, 'set_by' => $by->id])->save();
            }

            return [$changed, $first, $this->fill($scholarship, $term, $dueOn)];
        });

        // After the commit: nobody is told about a deadline that was rolled back.
        $tell = $changed ? self::scholarsFor($scholarship, $term)->whereHas('requirements', fn ($q) => $q->where('term', $term))->get()
            : Scholar::whereIn('id', $result['new_for'])->get();
        $date = Carbon::parse($dueOn)->format('M j, Y');
        foreach ($tell as $scholar) {
            $opened = $first || in_array($scholar->id, $result['new_for'], true);
            ScholarNotifier::send($scholar->setRelation('scholarship', $scholarship), 'deadline',
                $opened ? "Maintaining documents due {$date}" : "New deadline: {$date}",
                $opened
                    ? "{$scholarship->provider} opened your {$term} maintaining documents for {$scholarship->name}. Upload them on your Requirements page by {$date}."
                    : "{$scholarship->provider} changed the deadline of your {$term} maintaining documents for {$scholarship->name} to {$date}.",
                route('scholar.requirements').'#sch-'.$scholar->id);
        }

        return ['changed' => $changed, 'created' => $result['created'], 'notified' => $tell->count()];
    }

    /**
     * One scholar gets the checklist of every semester that already has a deadline and that they had started by
     * (after being added, reinstated, or a new "Scholar since"). Not for scholars out of good standing, and not
     * for providers that collect documents in person.
     */
    public function syncScholar(Scholar $scholar): int
    {
        $scholar->loadMissing('scholarship');
        $scholarship = $scholar->scholarship;
        if (! $scholarship || ! $scholarship->offers('requirements') || ! $scholar->isInGoodStanding()) {
            return 0;
        }

        $created = 0;
        foreach (ScholarshipDeadline::where('scholarship_id', $scholarship->id)->get() as $deadline) {
            if ($scholar->startedBy($deadline->term)) {
                $created += $this->fill($scholarship, $deadline->term, $deadline->due_on->toDateString(), $scholar)['created'];
            }
        }

        return $created;
    }

    /**
     * After the maintaining documents change: a new document joins the checklists of the CURRENT and coming
     * semesters that have a deadline (a past semester's checklist is never reopened). Returns the items added.
     */
    public function syncScholarship(Scholarship $scholarship): int
    {
        $now = AcademicTerm::indexOf(AcademicTerm::current());
        $created = 0;
        foreach (ScholarshipDeadline::where('scholarship_id', $scholarship->id)->get() as $deadline) {
            if (AcademicTerm::indexOf($deadline->term) >= $now) {
                $created += $this->fill($scholarship, $deadline->term, $deadline->due_on->toDateString())['created'];
            }
        }

        return $created;
    }

    /**
     * Adds the missing items of $term for the scholars who owe it (or only $only), and gives every item of the
     * scholarship's $term that is not approved yet the due date. Items that already exist are kept as they are.
     *
     * @return array{created: int, new_for: list<int>}
     */
    private function fill(Scholarship $scholarship, string $term, string $dueOn, ?Scholar $only = null): array
    {
        $docs = $scholarship->maintainingDocuments();
        $created = 0;
        $newFor = [];

        if ($docs) {
            self::scholarsFor($scholarship, $term)->when($only, fn ($q) => $q->whereKey($only->id))
                ->each(function (Scholar $s) use ($docs, $term, $dueOn, &$created, &$newFor) {
                    foreach ($docs as $doc) {
                        // Each item keeps a copy of the document's settings (accepted files, size, required).
                        $row = $s->requirements()->firstOrCreate(['term' => $term, 'name' => $doc['name']],
                            ['status' => 'missing', 'due_on' => $dueOn] + ScholarRequirement::settingsFrom($doc));
                        if ($row->wasRecentlyCreated) {
                            $created++;
                            $newFor[$s->id] = $s->id;
                        }
                    }
                });
        }

        // One date for the scholarship's whole semester; approved items keep theirs (it no longer matters).
        ScholarRequirement::query()->where('term', $term)
            ->whereHas('scholar', fn ($q) => $q->where('scholarship_id', $scholarship->id))
            ->when($only, fn ($q) => $q->where('scholar_id', $only->id))
            ->where('status', '!=', 'approved')
            ->where(fn ($q) => $q->whereNull('due_on')->orWhereDate('due_on', '!=', $dueOn))
            ->update(['due_on' => $dueOn]);

        return ['created' => $created, 'new_for' => array_values($newFor)];
    }
}

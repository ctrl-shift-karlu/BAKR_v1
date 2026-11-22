<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Public Scholarships page. Cards and modals are rendered from the `scholarships` table,
 * the same rows providers edit in the Provider Portal, so the page never drifts from them.
 * The student and scholar portals show the same catalogue (StudentPortalController, ScholarPortalController)
 * through activeScholarships() and sections().
 */
class ScholarshipCatalogController extends Controller
{
    /** Sector tabs, in display order. */
    public const SECTIONS = [
        'internal' => ['DNSC Scholarships', 'Institutional programs for DNSC students and campus involvement.', 'institutional'],
        'government' => ['Government', 'National programs from CHED, UniFAST and DOST.', 'external'],
        'private' => ['Non-Government', 'Private and foundation programs.', 'private / foundation'],
    ];

    /** Shows the public Scholarships page, grouped into the sector tabs. */
    public function index(): View
    {
        $scholarships = self::activeScholarships();

        return view('scholarships', [
            'sections' => self::sections($scholarships),
            'total' => $scholarships->count(),
        ]);
    }

    /** Every active scholarship, in catalogue order (shared by the public page and both portals). */
    public static function activeScholarships(): Collection
    {
        return Scholarship::where('is_active', true)->orderBy('sort_order')->get();
    }

    /** Groups scholarships into the sector tabs (empty sectors are left out). */
    public static function sections(Collection $scholarships): Collection
    {
        return collect(self::SECTIONS)->map(fn ($meta, $sector) => [
            'sector' => $sector,
            'title' => $meta[0],
            'blurb' => $meta[1],
            'kind' => $meta[2],
            'items' => $scholarships->where('sector', $sector)->values(),
        ])->filter(fn ($s) => $s['items']->isNotEmpty())->values();
    }
}

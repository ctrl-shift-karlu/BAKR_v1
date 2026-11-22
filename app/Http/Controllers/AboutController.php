<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiteFeedbackRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use App\Services\PublicAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Public About page: live aggregate statistics (PublicAnalyticsService), the team, and the star-rating
 * feedback form. Only feedback that the sender agreed to share and the BAKR team approved is shown.
 */
class AboutController extends Controller
{
    /** Shows the About page with the matching statistics and the approved feedback summary (star counts, average, two quotes). */
    public function show(PublicAnalyticsService $analytics): View
    {
        // Only feedback the sender agreed to share AND the BAKR team approved is ever counted or quoted here.
        $shared = ContactMessage::publicFeedback()->whereNotNull('rating');
        $byStar = (clone $shared)->selectRaw('rating, count(*) as n')->groupBy('rating')->pluck('n', 'rating');
        $ratingCount = (int) $byStar->sum();

        return view('about', [
            'insights' => $analytics->summary(),
            'blocks' => $analytics->placeholders(),
            'ratings' => [
                'count' => $ratingCount,
                'average' => $ratingCount ? round((clone $shared)->avg('rating'), 1) : null,
                'stars' => collect([5, 4, 3, 2, 1])->mapWithKeys(fn ($s) => [$s => [
                    'count' => (int) ($byStar[$s] ?? 0),
                    'percent' => $ratingCount ? (int) round(($byStar[$s] ?? 0) / $ratingCount * 100) : 0,
                ]])->all(),
                'quotes' => ContactMessage::publicFeedback()->where('message', '!=', '')->latest('approved_at')->limit(2)->get(),
            ],
        ]);
    }

    /**
     * Saves a star-rating feedback message (topic "feedback") and e-mails it to the BAKR team.
     * A filled-in honeypot field ("website") means a bot: the page answers as if it worked but stores nothing.
     */
    public function feedback(SiteFeedbackRequest $request, ContactMessageService $messages): RedirectResponse
    {
        $back = redirect()->to(route('about').'#feedback');

        // Honeypot filled in: a bot. Look successful, store nothing.
        if ($request->filled('website')) {
            return $back->with('feedback_sent', true);
        }

        $messages->record(['topic' => 'feedback'] + $request->validated(), $request->boolean('share_publicly'));

        return $back->with('feedback_sent', true);
    }
}

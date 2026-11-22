<?php

namespace App\Services;

use App\Models\Scholar;
use App\Notifications\ScholarUpdate;
use App\Support\MailTime;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Tells a scholar about a change their provider made (owner, 2026-11-21). The in-portal note (database) goes to
 * the scholar's account when there is one; the e-mail goes to their contact address. A failed e-mail never
 * undoes or blocks the provider's action (it is reported, like every other BAKR mail).
 */
class ScholarNotifier
{
    public static function send(Scholar $scholar, string $kind, string $title, string $text, ?string $url = null, bool $mail = true): void
    {
        $scholar->loadMissing(['scholarship', 'user']);
        $make = fn (array $channels) => new ScholarUpdate($kind, $title, $text, $scholar->scholarship->name, $scholar->scholarship->provider, $url, $channels);

        if ($scholar->user && $scholar->user->role === 'scholar') {
            $scholar->user->notifyNow($make(['database']));
        }

        $to = $scholar->contactEmail();
        if (! $mail || ! $to) {
            return;
        }
        try {
            MailTime::extend();   // a deadline e-mails every scholar in one request
            Notification::route('mail', $to)->notifyNow($make(['mail']));
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Status change → what it means for the scholar, in their words. */
    public static function statusChanged(Scholar $scholar, string $status): void
    {
        $provider = $scholar->scholarship->provider;
        [$title, $text] = match ($status) {
            'terminated' => ['Your scholarship was terminated', "{$provider} ended your {$scholar->scholarship->name} scholarship. You no longer get stipends or document checklists for it. Contact {$provider} if you have questions."],
            'inactive' => ['Your scholarship is marked inactive', "{$provider} marked your scholarship inactive. Bring your maintaining documents to the DNSC scholarship office to become active again."],
            default => ['Your scholarship is active', "{$provider} marked your {$scholar->scholarship->name} scholarship active."],
        };
        self::send($scholar, 'status', $title, $text, route('scholar.overview'));
    }
}

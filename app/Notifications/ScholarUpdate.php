<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * An update from a provider to one of its scholars (owner, 2026-11-21), sent by App\Services\ScholarNotifier:
 *   status      the provider changed the scholar's status (Active / Inactive / Terminated)
 *   correction  the provider saved a profile card the scholar had asked to correct
 *   reply       the provider answered a correction request (the reply itself is e-mailed by ScholarMessageReply)
 *   deadline    the provider set or changed a semester's maintaining-documents deadline (RequirementChecklist)
 * Stored for the scholar's account (Overview › Updates) and/or e-mailed. Text only, no personal details
 * beyond what the scholar already sees on their own pages.
 */
class ScholarUpdate extends Notification
{
    use Queueable;

    /** @param  array<int,string>  $channels */
    public function __construct(
        public string $kind,
        public string $title,
        public string $text,
        public string $scholarship,
        public string $provider,
        public ?string $url = null,
        public array $channels = ['database'],
    ) {}

    public function via(object $notifiable): array
    {
        return $this->channels;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'title' => $this->title,
            'text' => $this->text,
            'scholarship' => $this->scholarship,
            'provider' => $this->provider,
            'url' => $this->url,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title.' · '.$this->scholarship)
            ->view('emails.scholar-update', ['update' => $this]);
    }
}

<?php

namespace App\Support;

/**
 * BAKR sends its e-mails right away (no queue worker), sometimes many in one request: a deadline e-mails
 * every scholar of the scholarship, an Excel import e-mails every new login. Over Gmail SMTP each one takes
 * about a second, so a long list ran past PHP's max_execution_time and the request died halfway (some
 * scholars told, the rest not, the provider shown an error). Call this before each e-mail: it restarts
 * PHP's timer so every message gets its own window. Does nothing when there is no limit (the CLI, tests).
 */
class MailTime
{
    public const SECONDS_PER_MAIL = 60;

    /** Restarts PHP's execution timer with at least SECONDS_PER_MAIL seconds; call it before each e-mail. */
    public static function extend(): void
    {
        $limit = (int) ini_get('max_execution_time');
        if ($limit > 0) {
            @set_time_limit(max($limit, self::SECONDS_PER_MAIL));
        }
    }
}

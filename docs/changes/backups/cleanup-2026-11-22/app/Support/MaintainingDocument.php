<?php

namespace App\Support;

/**
 * One maintaining (renewal) document a scholarship asks its scholars for every semester, as the
 * provider sets it on My scholarships › Maintaining documents (owner, 2026-11-19):
 *
 *   name      what to submit (also the checklist item's name)
 *   note      instructions for scholars (optional)
 *   types     accepted file types, keys of TYPES
 *   max_mb    largest file accepted, one of SIZES
 *   required  true  = every scholar submits it each semester (missing after the due date → Inactive,
 *                     and it holds back the stipend until approved)
 *             false = only when it applies to the scholar (e.g. "If returning after a leave"): it can
 *                     be uploaded and reviewed, but never makes anyone Inactive or blocks a stipend
 *
 * Stored in scholarships.renewal_requirements as a list of these arrays. Older rows (and
 * config/scholarships.php) hold plain strings; normalize() reads both.
 */
final class MaintainingDocument
{
    /** Accepted file types: key => [label, extensions the upload rule allows, MIME types for <input accept>]. */
    public const TYPES = [
        'pdf' => ['PDF', ['pdf'], ['application/pdf']],
        'jpg' => ['JPG', ['jpg', 'jpeg'], ['image/jpeg']],
        'png' => ['PNG', ['png'], ['image/png']],
        'docx' => ['Word (DOCX)', ['docx'], ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']],
    ];

    /** File size limits a provider can choose (MB). */
    public const SIZES = [1, 2, 5, 10];

    public const DEFAULT_TYPES = ['pdf', 'jpg', 'png'];

    public const DEFAULT_MB = 5;

    public const MAX_DOCUMENTS = 20;

    public const NAME_MAX = 255;

    public const NOTE_MAX = 300;

    /** The stored list, every item in the structured form. */
    public static function list(?array $stored): array
    {
        return array_values(array_filter(array_map(self::normalize(...), $stored ?? [])));
    }

    /** One stored item (string or array) in the structured form; null when it has no name. */
    public static function normalize(mixed $item): ?array
    {
        if (is_string($item)) {
            return trim($item) === '' ? null : self::fromText($item);
        }
        if (! is_array($item) || trim((string) ($item['name'] ?? '')) === '') {
            return null;
        }

        $types = array_values(array_intersect(array_keys(self::TYPES), (array) ($item['types'] ?? [])));
        $mb = (int) ($item['max_mb'] ?? self::DEFAULT_MB);

        return [
            'name' => trim(preg_replace('/\s+/u', ' ', (string) $item['name'])),
            'note' => filled($item['note'] ?? null) ? trim((string) $item['note']) : null,
            'types' => $types ?: self::DEFAULT_TYPES,
            'max_mb' => in_array($mb, self::SIZES, true) ? $mb : self::DEFAULT_MB,
            'required' => (bool) ($item['required'] ?? true),
        ];
    }

    /**
     * An older plain-text item. Only what its own wording says is used: "If …" items apply only to
     * some scholars, and "PDF copy" items take PDF only. Everything else gets the defaults.
     */
    public static function fromText(string $text): array
    {
        $text = trim($text);

        return [
            'name' => $text,
            'note' => null,
            'types' => preg_match('/\bPDF cop(y|ies)\b/i', $text) ? ['pdf'] : self::DEFAULT_TYPES,
            'max_mb' => self::DEFAULT_MB,
            'required' => ! preg_match('/^if\b/i', $text),
        ];
    }

    /** Extensions for the `mimes:` upload rule. */
    public static function extensions(array $types): array
    {
        return array_values(array_unique(array_merge(...array_map(fn ($t) => self::TYPES[$t][1] ?? [], $types ?: self::DEFAULT_TYPES))));
    }

    /** Value for <input type="file" accept="…">. */
    public static function accept(array $types): string
    {
        $types = $types ?: self::DEFAULT_TYPES;
        $ext = array_map(fn ($e) => '.'.$e, self::extensions($types));
        $mime = array_merge(...array_map(fn ($t) => self::TYPES[$t][2] ?? [], $types));

        return implode(',', array_merge($ext, $mime));
    }

    /** "PDF, JPG or PNG". */
    public static function typesLabel(array $types): string
    {
        $labels = array_map(fn ($t) => self::TYPES[$t][0] ?? strtoupper($t), $types ?: self::DEFAULT_TYPES);
        $last = array_pop($labels);

        return $labels ? implode(', ', $labels).' or '.$last : $last;
    }
}

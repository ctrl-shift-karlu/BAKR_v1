<?php

namespace App\Models;

use App\Support\MaintainingDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** One maintaining document of a scholar for one semester (settings copied from the scholarship). The file lives on the private `local` disk. */
class ScholarRequirement extends Model
{
    public const STATUSES = [
        'missing' => 'Not submitted',
        'submitted' => 'Waiting for review',
        'approved' => 'Approved',
        'needs_revision' => 'Needs revision',
    ];

    // Allowed file types and size are per document (accepted_types / max_kb, set by the provider).

    /** Files the browser can show in the preview (no download needed). */
    public const PREVIEW_MIMES = ['application/pdf', 'image/jpeg', 'image/png'];

    protected $fillable = [
        'scholar_id', 'term', 'name', 'is_optional', 'accepted_types', 'max_kb', 'instructions',
        'status', 'due_on', 'file_path', 'original_name', 'mime', 'size', 'submitted_at', 'reviewed_at', 'review_note',
    ];

    protected $hidden = ['file_path'];

    /** Column types: dates, file size and limits as numbers, accepted types as an array. */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime', 'reviewed_at' => 'datetime', 'due_on' => 'date', 'size' => 'integer',
            'is_optional' => 'boolean', 'accepted_types' => 'array', 'max_kb' => 'integer',
        ];
    }

    /** The settings copied from the scholarship's maintaining document (MaintainingDocument::normalize()). */
    public static function settingsFrom(array $doc): array
    {
        return [
            'is_optional' => ! $doc['required'],
            'accepted_types' => $doc['types'],
            'max_kb' => $doc['max_mb'] * 1024,
            'instructions' => $doc['note'],
        ];
    }

    /** Accepted type keys (MaintainingDocument::TYPES); older items without settings take the defaults. */
    public function acceptedTypes(): array
    {
        return $this->accepted_types ?: MaintainingDocument::DEFAULT_TYPES;
    }

    /** The largest file allowed for this item, in kilobytes (older items without settings use the default). */
    public function maxKb(): int
    {
        return $this->max_kb ?: MaintainingDocument::DEFAULT_MB * 1024;
    }

    /** "PDF or JPG, up to 2 MB" */
    public function uploadRulesLabel(): string
    {
        return MaintainingDocument::typesLabel($this->acceptedTypes()).', up to '.rtrim(rtrim(number_format($this->maxKb() / 1024, 1), '0'), '.').' MB';
    }

    /**
     * Does this item count for the scholar's to-do and everyone's totals? Every required item does;
     * an "only if it applies" item counts once the scholar has sent it (an unsent one may simply not apply).
     */
    public function isOwed(): bool
    {
        return ! $this->is_optional || $this->status !== 'missing';
    }

    /** Query version of isOwed(): use it for every count of requirements. */
    public function scopeOwed($query)
    {
        // Qualified: callers may join `scholars`, which has its own `status` column.
        return $query->where(fn ($q) => $q->where($this->qualifyColumn('is_optional'), false)->orWhere($this->qualifyColumn('status'), '!=', 'missing'));
    }

    /** Still not submitted (or sent back) after its due date: makes the scholar Inactive. Optional items never do. */
    public function isOverdue(): bool
    {
        return ! $this->is_optional
            && in_array($this->status, Scholar::OVERDUE_REQUIREMENT_STATES, true)
            && $this->due_on !== null && $this->due_on->lt(now()->startOfDay());
    }

    /** True when the uploaded file can be shown in the browser preview (PDF, JPG or PNG). */
    public function canPreview(): bool
    {
        return $this->hasFile() && in_array($this->mime, self::PREVIEW_MIMES, true);
    }

    /**
     * Streams the uploaded file from the private disk. Used by both the scholar and the provider side; each
     * controller first checks that the person may open this requirement.
     *
     * With $inline = true and a previewable type (PDF / JPG / PNG) the browser shows the file in the page,
     * with the stored type and nosniff so it is never guessed as something else; otherwise it is a download.
     *
     * @param  bool  $inline  true for the in-page preview (?inline=1)
     */
    public function fileResponse(bool $inline): StreamedResponse
    {
        $disk = Storage::disk('local');
        abort_unless($this->hasFile() && $disk->exists($this->file_path), 404);
        $name = $this->original_name ?: basename($this->file_path);

        if ($inline && $this->canPreview()) {
            return $disk->response($this->file_path, $name, [
                'Content-Type' => $this->mime,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]);
        }

        return $disk->download($this->file_path, $name);
    }

    /** The scholar this checklist item belongs to. */
    public function scholar(): BelongsTo
    {
        return $this->belongsTo(Scholar::class);
    }

    /** The status in words, e.g. "Waiting for review". */
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    /** True when the scholar has uploaded a file for this item. */
    public function hasFile(): bool
    {
        return filled($this->file_path);
    }
}

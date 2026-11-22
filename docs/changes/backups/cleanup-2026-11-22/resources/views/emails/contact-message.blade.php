<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#2b2925;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e3ddd0;border-top:4px solid #b8912f;border-radius:8px;padding:24px;">
        <p style="margin:0 0 4px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#7a5a0c;">BAKR contact form</p>
        <h2 style="margin:0 0 16px;font-size:20px;color:#143a2b;">{{ $contact->topicLabel() }}</h2>
        <p style="margin:0 0 4px;"><strong>From:</strong> {{ $contact->name }} &lt;{{ $contact->email }}&gt;</p>
        @if ($contact->rating)
            <p style="margin:0 0 4px;"><strong>Rating:</strong> {{ $contact->rating }} / 5</p>
        @endif
        @if ($contact->aspectLabels())
            <p style="margin:0 0 4px;"><strong>Parts of BAKR used:</strong> {{ implode(', ', $contact->aspectLabels()) }}</p>
        @endif
        @if ($contact->topic === 'feedback')
            <p style="margin:0 0 4px;"><strong>OK to show on the site:</strong> {{ $contact->share_publicly ? 'Yes (still needs your approval)' : 'No' }}</p>
        @endif
        <p style="margin:0 0 16px;"><strong>Received:</strong> {{ $contact->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
        <div style="padding:14px 16px;background:#faf7f2;border-radius:6px;white-space:pre-wrap;line-height:1.6;">{{ filled($contact->message) ? $contact->message : 'Rating only, no comment.' }}</div>
        <p style="margin:16px 0 0;font-size:12px;color:#6b665d;">Press Reply to answer the sender directly. Message #{{ $contact->id }} is also saved in the contact_messages table.</p>
    </div>
</body>
</html>

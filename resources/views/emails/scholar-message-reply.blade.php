<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#2b2925;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e3ddd0;border-top:4px solid #b8912f;border-radius:8px;padding:24px;">
        <p style="margin:0 0 4px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#7a5a0c;">BAKR · {{ $correction->scholar->scholarship->name }}</p>
        <h2 style="margin:0 0 16px;font-size:20px;color:#143a2b;">Hi {{ $correction->scholar->displayName() }},</h2>
        <div style="white-space:pre-wrap;line-height:1.6;">{{ $correction->reply }}</div>
        <p style="margin:20px 0 0;">{{ $providerName }}</p>
        <hr style="margin:20px 0;border:0;border-top:1px solid #e3ddd0;">
        <p style="margin:0 0 6px;font-size:12px;color:#6b665d;">You asked on {{ $correction->created_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }} ({{ $correction->topicShort() }}):</p>
        <div style="padding:12px 14px;background:#faf7f2;border-radius:6px;white-space:pre-wrap;line-height:1.5;font-size:13px;color:#6b665d;">{{ $correction->message }}</div>
        <p style="margin:16px 0 0;font-size:12px;color:#6b665d;line-height:1.5;">You can also read this reply on your Profile in the BAKR Scholar Portal.</p>
    </div>
</body>
</html>

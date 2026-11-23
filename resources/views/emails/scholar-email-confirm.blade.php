<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#2b2925;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e3ddd0;border-top:4px solid #b8912f;border-radius:8px;padding:24px;">
        <p style="margin:0 0 4px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#7a5a0c;">{{ config('bakr.system_name') }}</p>
        <h2 style="margin:0 0 16px;font-size:20px;color:#143a2b;">Hello, {{ $scholar->displayName() }}</h2>
        <p style="margin:0 0 12px;line-height:1.6;">{{ $scholar->scholarship->provider }} asked to change your BAKR sign-in e-mail to <strong>{{ $scholar->pending_email }}</strong> (this address).</p>
        <p style="margin:0 0 16px;line-height:1.6;">Press the button to confirm that this e-mail is yours and works. Until you do, you keep signing in with your current e-mail.</p>
        <p style="margin:0 0 20px;"><a href="{{ $confirmUrl }}" style="display:inline-block;padding:11px 22px;background:#c9a443;color:#143a2b;text-decoration:none;font-weight:bold;border-radius:999px;">Confirm my new e-mail</a></p>
        <p style="margin:0 0 12px;font-size:13px;color:#6b665d;line-height:1.5;">The link works for {{ $hours }} hours and only once. If the button does not open, copy this address into your browser:<br><span style="word-break:break-all;">{{ $confirmUrl }}</span></p>
        <p style="margin:0;font-size:12px;color:#6b665d;line-height:1.5;">Did not expect this? Do not press the button, and tell {{ $scholar->scholarship->provider }}. BAKR uses your details only to manage your scholarship (Data Privacy Act of 2012).</p>
    </div>
</body>
</html>

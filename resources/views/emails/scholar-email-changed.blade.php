<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#2b2925;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e3ddd0;border-top:4px solid #b8912f;border-radius:8px;padding:24px;">
        <p style="margin:0 0 4px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#7a5a0c;">{{ config('bakr.system_name') }}</p>
        <h2 style="margin:0 0 16px;font-size:20px;color:#143a2b;">Hello, {{ $scholar->displayName() }}</h2>
        <p style="margin:0 0 12px;line-height:1.6;">Your BAKR sign-in e-mail was changed to <strong>{{ $newEmail }}</strong>. The change was confirmed from that address.</p>
        <p style="margin:0 0 16px;line-height:1.6;">From now on, sign in at the Scholar login with the new e-mail. {{ $newLogin ? 'New login details (a temporary password) were sent to the new address.' : 'Your password stays the same.' }}</p>
        <p style="margin:0 0 20px;"><a href="{{ $loginUrl }}" style="display:inline-block;padding:11px 22px;background:#c9a443;color:#143a2b;text-decoration:none;font-weight:bold;border-radius:999px;">Open the Scholar login</a></p>
        <p style="margin:0;font-size:12px;color:#6b665d;line-height:1.5;">Did not ask for this? Contact {{ $scholar->scholarship->provider }} right away.</p>
    </div>
</body>
</html>

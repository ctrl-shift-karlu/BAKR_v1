<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#2b2925;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e3ddd0;border-top:4px solid #b8912f;border-radius:8px;padding:24px;">
        <p style="margin:0 0 4px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#7a5a0c;">{{ config('bakr.system_name') }}</p>
        <h2 style="margin:0 0 16px;font-size:20px;color:#143a2b;">Hello, {{ $scholar->displayName() }}</h2>
        <p style="margin:0 0 12px;line-height:1.6;">{{ $scholar->scholarship->provider }} added you as a scholar of <strong>{{ $scholar->scholarship->name }}</strong> in BAKR. You can now see your scholarship status{{ $scholar->scholarship->offers('disbursements') ? ', your stipends' : '' }}{{ $scholar->scholarship->offers('requirements') ? ' and the maintaining documents to submit each semester' : '' }}.</p>

        @if ($password)
            <p style="margin:0 0 8px;line-height:1.6;">Your BAKR account is ready. Sign in with:</p>
            <table role="presentation" style="margin:0 0 14px;border-collapse:collapse;width:100%;background:#faf7f2;border-radius:6px;">
                <tr><td style="padding:10px 14px;color:#6b665d;width:40%;">E-mail</td><td style="padding:10px 14px;"><strong>{{ $scholar->user->email }}</strong></td></tr>
                <tr><td style="padding:10px 14px;color:#6b665d;">Temporary password</td><td style="padding:10px 14px;font-family:Consolas,Menlo,monospace;font-size:16px;letter-spacing:.06em;"><strong>{{ $password }}</strong></td></tr>
            </table>
            <p style="margin:0 0 16px;padding:10px 14px;background:#fbf1dc;border-left:4px solid #b8912f;border-radius:4px;line-height:1.5;"><strong>Change this password right away.</strong> BAKR will ask you to choose your own password as soon as you sign in. Do not share this e-mail.</p>
        @elseif ($converted)
            <p style="margin:0 0 12px;line-height:1.6;">Your BAKR student account (<strong>{{ $scholar->user->email }}</strong>) is now a <strong>scholar account</strong>. Your password stays the same.</p>
            <p style="margin:0 0 16px;padding:10px 14px;background:#fbf1dc;border-left:4px solid #b8912f;border-radius:4px;line-height:1.5;"><strong>From now on, sign in at the Scholar login</strong>, not the Student login. Scholarship matching is not part of the scholar portal. If you forgot your password, <a href="{{ $forgotUrl }}" style="color:#1f4d3a;">reset it</a>.</p>
        @else
            <p style="margin:0 0 16px;line-height:1.6;">You already have a BAKR scholar account with this e-mail. Sign in at the Scholar login with your usual password and open <strong>My scholarship</strong>. If you forgot it, <a href="{{ $forgotUrl }}" style="color:#1f4d3a;">reset your password</a>.</p>
        @endif

        <p style="margin:0 0 20px;"><a href="{{ $loginUrl }}" style="display:inline-block;padding:11px 22px;background:#c9a443;color:#143a2b;text-decoration:none;font-weight:bold;border-radius:999px;">Sign in at the Scholar login</a></p>
        <p style="margin:0;font-size:12px;color:#6b665d;line-height:1.5;">If you did not expect this e-mail, contact {{ $scholar->scholarship->provider }} or reply to the BAKR team. BAKR uses your details only to manage your scholarship (Data Privacy Act of 2012).</p>
    </div>
</body>
</html>

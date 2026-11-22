<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f4f1ea;font-family:Arial,Helvetica,sans-serif;color:#2b2925;">
    <div style="max-width:560px;margin:0 auto;background:#fff;border:1px solid #e3ddd0;border-top:4px solid #b8912f;border-radius:8px;padding:24px;">
        <p style="margin:0 0 4px;font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#7a5a0c;">BAKR · {{ $update->scholarship }}</p>
        <h2 style="margin:0 0 16px;font-size:20px;color:#143a2b;">{{ $update->title }}</h2>
        <p style="margin:0;line-height:1.6;">{{ $update->text }}</p>
        @if ($update->url)
            <p style="margin:20px 0 0;"><a href="{{ $update->url }}" style="display:inline-block;padding:10px 18px;border-radius:999px;background:#1f4d3a;color:#fff;text-decoration:none;font-weight:bold;">Open the Scholar Portal</a></p>
        @endif
        <hr style="margin:20px 0;border:0;border-top:1px solid #e3ddd0;">
        <p style="margin:0;font-size:12px;color:#6b665d;line-height:1.5;">From {{ $update->provider }}, through BAKR. You also see this on your Overview in the BAKR Scholar Portal.</p>
    </div>
</body>
</html>

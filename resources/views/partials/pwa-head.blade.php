{{-- BAKR as an installable app (PWA). In the <head> of every layout (app, auth, provider, admin;
     student and scholar pages extend layouts.app). Manifest: public/manifest.json, service worker:
     public/sw.js (never caches pages), script: public/js/bakr-pwa.js.
     bakr-app-mode.js is NOT deferred: it marks the installed app (html.is-app) before the first paint,
     and bakr-app-mode.css then turns off decorative motion and blur there. --}}
<script src="{{ asset('js/bakr-app-mode.js') }}"></script>
<link rel="manifest" href="{{ asset('manifest.json') }}">
<link rel="apple-touch-icon" href="{{ asset('images/app/apple-touch-icon.png') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="BAKR">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<link rel="stylesheet" href="{{ asset('css/bakr-pwa.css') }}">
<link rel="stylesheet" href="{{ asset('css/bakr-app-mode.css') }}">
<script src="{{ asset('js/bakr-pwa.js') }}" data-sw="{{ asset('sw.js') }}" defer></script>

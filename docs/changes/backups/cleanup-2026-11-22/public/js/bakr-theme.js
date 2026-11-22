// BAKR: light / dark theme. Loaded in <head> WITHOUT defer so the theme is set before the
// first paint (no white flash). The choice is remembered per browser; until the visitor picks
// one, BAKR follows the device setting (prefers-color-scheme).
// Buttons: any element with [data-theme-toggle] (partials/theme-toggle).
(function () {
  var KEY = 'bakr.theme';
  var root = document.documentElement;
  var media = window.matchMedia ? window.matchMedia('(prefers-color-scheme: dark)') : null;

  var stored = function () {
    try { var t = localStorage.getItem(KEY); return t === 'dark' || t === 'light' ? t : null; } catch (e) { return null; }
  };
  var current = function () { return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'; };

  // The switch is role="switch" labelled "Dark mode": on = dark.
  var sync = function () {
    var dark = current() === 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
      btn.setAttribute('aria-checked', dark ? 'true' : 'false');
      btn.title = dark ? 'Dark mode is on' : 'Dark mode is off';
    });
  };
  var apply = function (theme) { root.setAttribute('data-theme', theme); sync(); };

  root.setAttribute('data-theme', stored() || (media && media.matches ? 'dark' : 'light'));

  // Device setting changes (e.g. the phone switches to dark at night): follow it unless the visitor chose.
  if (media && media.addEventListener) {
    media.addEventListener('change', function (e) { if (!stored()) apply(e.matches ? 'dark' : 'light'); });
  }

  document.addEventListener('click', function (e) {
    var btn = e.target && e.target.closest ? e.target.closest('[data-theme-toggle]') : null;
    if (!btn) return;
    var next = current() === 'dark' ? 'light' : 'dark';
    try { localStorage.setItem(KEY, next); } catch (err) { /* storage blocked: still switch for this page */ }
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (document.startViewTransition && !reduce) {
      document.startViewTransition(function () { apply(next); });   // soft cross-fade
    } else {
      apply(next);
    }
  });

  document.addEventListener('DOMContentLoaded', sync);
})();

/* BAKR — scholar sign-in guard. Loaded in <head> (not deferred) so it redirects before the dashboard paints. */
/* Sign-in guard: no scholar session means go to the scholar sign-in page first. */
(function () {
  var signedIn = "";
  try { signedIn = sessionStorage.getItem("scholarSession") || localStorage.getItem("scholarSession") || ""; } catch (e) {}
  if (!signedIn) window.location.replace((document.currentScript && document.currentScript.dataset.loginUrl) || "scholarLogin");
})();

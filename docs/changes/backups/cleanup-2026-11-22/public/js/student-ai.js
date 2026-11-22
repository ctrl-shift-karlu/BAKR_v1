// BAKR: AI explanations load by themselves. No inline scripts; settings come from data-* attributes.
//   [data-ai-url]               box whose text the AI should write (POST, JSON reply {ok, text})
//   [data-ai-auto]              ask as soon as the page loads (dashboard overview)
//   otherwise                   ask when the <dialog> holding the box opens (My matches)
//   [data-ai-tag] / [data-ai-text]   the label and paragraph to update
// The built-in explanation stays on screen while waiting, and stays if the AI is off or fails.
(() => {
  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

  const setLabel = (box, text) => {
    const tag = box.querySelector('[data-ai-tag]');
    if (tag && tag.lastChild) tag.lastChild.textContent = ' ' + text;
  };

  const run = (box) => {
    if (!box || box.dataset.aiState) return;           // ask once per page view
    box.dataset.aiState = 'loading';
    box.setAttribute('aria-busy', 'true');
    setLabel(box, 'Writing your explanation…');

    fetch(box.dataset.aiUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((r) => (r.ok ? r.json() : null))
      .then((data) => {
        const text = box.querySelector('[data-ai-text]');
        if (!data || !data.ok || !data.text || !text) throw new Error('no AI text');
        text.textContent = data.text;                   // textContent: never HTML from the AI
        setLabel(box, box.dataset.aiLabel || 'AI explanation');
        box.dataset.aiState = 'done';
      })
      .catch(() => {
        setLabel(box, box.dataset.fallbackLabel || 'Quick explanation');
        box.dataset.aiState = 'failed';
      })
      .finally(() => box.removeAttribute('aria-busy'));
  };

  // My matches: when a match's dialog opens (by click or by a #match-… link).
  document.querySelectorAll('dialog').forEach((dialog) => {
    const box = dialog.querySelector('[data-ai-url]');
    if (!box) return;
    new MutationObserver(() => { if (dialog.open) run(box); }).observe(dialog, { attributes: true, attributeFilter: ['open'] });
    if (dialog.open) run(box);
  });

  // Dashboard overview: right away.
  document.querySelectorAll('[data-ai-url][data-ai-auto]').forEach(run);

  // Gemini AI matcher (results + dashboard): runs once per saved application, then the page reloads
  // with the reviewed results. On failure the rule-check results stay and the button offers a retry.
  const matchBox = document.querySelector('[data-ai-match-url]');
  if (matchBox) {
    const fallback = matchBox.querySelector('[data-ai-match-fallback]');
    if (fallback) fallback.hidden = true;                // JS asks by itself; the button is the no-JS path
    matchBox.setAttribute('aria-busy', 'true');
    let waits = 0;
    const ask = () => fetch(matchBox.dataset.aiMatchUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((r) => r.json().catch(() => ({})).then((data) => ({ status: r.status, ok: r.ok, data })))
      .then(({ status, ok, data }) => {
        if (ok && data.ok) { window.location.reload(); return null; }
        // 202: another tab is already running this review; ask again shortly (up to ~2 minutes).
        if (status === 202 && waits < 24) {
          waits += 1;
          return new Promise((done) => { window.setTimeout(done, 5000); }).then(ask);
        }
        throw new Error(data.state || 'failed');
      });
    ask()
      .catch(() => {
        matchBox.removeAttribute('aria-busy');
        matchBox.classList.add('is-failed');
        const title = matchBox.querySelector('[data-ai-match-title]');
        const text = matchBox.querySelector('[data-ai-match-text]');
        if (title) title.textContent = 'The AI matcher could not finish right now.';
        if (text) text.textContent = 'These results come from BAKR\'s rule check of the published limits only. You can try again.';
        if (fallback) {
          fallback.hidden = false;
          const label = fallback.querySelector('button');
          if (label && label.lastChild) label.lastChild.textContent = ' Try the AI review again';
        }
      });
  }
})();

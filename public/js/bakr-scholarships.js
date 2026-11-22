/* BAKR — scholarship directory: live search across the sector tabs. */
(() => {
 const input = document.getElementById('scholarshipSearch');
 const clear = document.getElementById('clearScholarshipSearch');
 const status = document.getElementById('scholarshipSearchStatus');
 if (!input || !status) return;
 const cards = Array.from(document.querySelectorAll('.tab-pane .row > [class*="col-"]')).filter(col => col.querySelector('.scholar-card'));
 const normalize = value => (value || '').toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
 const filterCards = () => {
   const query = normalize(input.value.trim()); let visible = 0;
   cards.forEach(col => { const card = col.querySelector('.scholar-card'); const matches = !query || normalize(card.innerText).includes(query); col.hidden = !matches; if (matches) visible++; });
   document.querySelectorAll('.tab-pane').forEach(panel => { const panelCards = Array.from(panel.querySelectorAll('.scholar-card')); const shown = panelCards.filter(card => !card.closest('[hidden]')).length; const heading = panel.querySelector('.directory-head .count-bubble'); if (heading) { const noun = shown === 1 ? 'program' : 'programs'; heading.innerHTML = '<i class="bi bi-bookmark-star-fill"></i> ' + shown + ' ' + noun; } });
   status.textContent = query ? `Found ${visible} of ${cards.length} scholarships` : `Showing all ${cards.length} scholarships`;
 };
 input.addEventListener('input', filterCards);
 clear?.addEventListener('click', () => { input.value = ''; filterCards(); input.focus(); });
 document.querySelectorAll('#sectorTabs [data-bs-toggle="pill"]').forEach(tab => tab.addEventListener('shown.bs.tab', () => { if (input.value.trim()) filterCards(); }));
 filterCards();
})();

/* Links like /scholarships#card-ched-tdp: open the sector tab that holds the card (bakr-core.js then scrolls to it). */
(() => {
 const openCardTab = () => {
   if (!/^#card-[\w-]+$/.test(location.hash)) return;
   const card = document.querySelector(location.hash);
   const pane = card?.closest('.tab-pane');
   if (!pane || pane.classList.contains('active')) return;
   const tab = document.querySelector(`[data-bs-target="#${pane.id}"]`);
   if (tab && window.bootstrap) bootstrap.Tab.getOrCreateInstance(tab).show();
 };
 openCardTab();
 window.addEventListener('hashchange', () => { openCardTab(); document.querySelector(location.hash)?.scrollIntoView({ block: 'center' }); });
})();

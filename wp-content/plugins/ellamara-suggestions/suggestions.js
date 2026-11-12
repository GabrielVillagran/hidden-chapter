(() => {
  'use strict';
  const config = window.ellamaraSuggestions;
  if (!config) return;
  const key = 'ellamara_suggestions_v1';
  let activity = {views: [], search: '', expires: 0};
  try {
    const stored = JSON.parse(sessionStorage.getItem(key) || 'null');
    if (stored && stored.expires > Date.now()) {
      activity.views = Array.isArray(stored.views) ? stored.views.filter(Number.isInteger).filter(x => x > 0).slice(0, 8) : [];
      activity.search = typeof stored.search === 'string' ? stored.search.slice(0, 80) : '';
    } else if (stored) {
      sessionStorage.removeItem(key);
    }
    if (config.product) activity.views = [Number(config.product), ...activity.views.filter(id => id !== Number(config.product))].slice(0, 8);
    if (config.search) activity.search = config.search;
    if (config.product || config.search) {
      activity.expires = Date.now() + 2 * 60 * 60 * 1000;
      sessionStorage.setItem(key, JSON.stringify(activity));
    }
  } catch (_) { /* Storage restrictions leave the general catalog usable. */ }

  const root = document.querySelector('.ellamara-suggestions');
  if (!root) return;
  const message = root.querySelector('.ellamara-suggestions-message');
  const results = root.querySelector('.ellamara-suggestions-results');
  const reset = root.querySelector('.ellamara-suggestions-reset');
  let requestNumber = 0;
  async function load() {
    const number = ++requestNumber;
    root.setAttribute('aria-busy', 'true');
    reset.disabled = true;
    try {
      const response = await fetch(config.endpoint, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({views: activity.views, search: activity.search})});
      if (!response.ok) throw new Error('Catalog unavailable');
      const selection = await response.json();
      if (number !== requestNumber) return;
      results.innerHTML = selection.html;
      message.textContent = selection.message;
    } catch (_) {
      message.textContent = 'Mostramos una selección general del catálogo. No pudimos actualizar la afinidad en este momento.';
    } finally {
      if (number === requestNumber) {
        root.setAttribute('aria-busy', 'false');
        reset.disabled = false;
      }
    }
  }
  reset.hidden = false;
  reset.addEventListener('click', () => {
    activity = {views: [], search: '', expires: 0};
    try { sessionStorage.removeItem(key); } catch (_) {}
    load();
  });
  if (activity.views.length || activity.search) load();
})();

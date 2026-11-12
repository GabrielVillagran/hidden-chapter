(() => {
  const media = matchMedia('(max-width: 600px)');
  const update = () => document.querySelectorAll('.ellamara-filter-disclosure').forEach(panel => { panel.open = !media.matches; });
  update();
  media.addEventListener('change', update);
})();

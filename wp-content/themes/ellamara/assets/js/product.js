/* Native horizontal scrolling: no autoplay, cloned cards or carousel dependency. */
(() => {
  'use strict';
  document.querySelectorAll('.ellamara-related').forEach(region => {
    const track = region.querySelector('.wc-block-product-template');
    const controls = region.querySelector('.ellamara-carousel-controls');
    if (!track || !controls) return;
    const previous = controls.querySelector('[data-carousel-prev]');
    const next = controls.querySelector('[data-carousel-next]');
    track.tabIndex = 0;
    track.setAttribute('aria-label', 'Lista de productos relacionados. Usa las flechas para desplazarte.');
    const update = () => {
      const overflow = track.scrollWidth > track.clientWidth + 2;
      controls.hidden = !overflow;
      previous.disabled = track.scrollLeft <= 2;
      next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
      region.querySelector('.ellamara-carousel-hint').hidden = !overflow;
    };
    const move = direction => {
      const card = track.querySelector('li');
      const step = card ? card.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0) : track.clientWidth;
      track.scrollBy({left: direction * step, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth'});
    };
    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    track.addEventListener('scroll', update, {passive: true});
    track.addEventListener('keydown', event => {
      if (event.target !== track || !['ArrowLeft','ArrowRight'].includes(event.key)) return;
      event.preventDefault(); move(event.key === 'ArrowRight' ? 1 : -1);
    });
    new ResizeObserver(update).observe(track);
    update();
  });
})();

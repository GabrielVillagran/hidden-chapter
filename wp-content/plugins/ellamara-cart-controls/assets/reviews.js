(() => {
  'use strict';
  function initialize() {
    document.querySelectorAll('[data-review-carousel]').forEach(root => {
      if (root.dataset.reviewReady) return;
      const slides = [...root.querySelectorAll('.ellamara-review-slide')];
      if (slides.length < 2) return;
      const controls = root.querySelector('.ellamara-review-controls');
      const position = root.querySelector('[data-review-position]');
      const previous = root.querySelector('[data-review-prev]');
      const next = root.querySelector('[data-review-next]');
      if (!controls || !position || !previous || !next) return;
      let current = 0;
      function show(index) {
        const oldIndex = current;
        const focusWasInSlide = slides[current].contains(document.activeElement);
        current = Math.max(0, Math.min(index, slides.length - 1));
        if (focusWasInSlide && current !== oldIndex) (current > oldIndex ? next : previous).focus({preventScroll: true});
        slides.forEach((slide, i) => { slide.hidden = i !== current; });
        position.textContent = `Opinión ${current + 1} de ${slides.length}`;
        // Keep the activated control focused, including when it reaches an endpoint.
        previous.setAttribute('aria-disabled', String(current === 0));
        next.setAttribute('aria-disabled', String(current === slides.length - 1));
      }
      previous.addEventListener('click', () => show(current - 1));
      next.addEventListener('click', () => show(current + 1));
      root.addEventListener('keydown', event => {
        const offsets = {ArrowLeft: current - 1, ArrowRight: current + 1, Home: 0, End: slides.length - 1};
        if (!(event.key in offsets) || event.altKey || event.ctrlKey || event.metaKey) return;
        event.preventDefault();
        show(offsets[event.key]);
      });
      root.dataset.reviewReady = 'true';
      root.setAttribute('aria-roledescription', 'carrusel');
      slides.forEach(slide => slide.setAttribute('aria-roledescription', 'diapositiva'));
      show(0);
      controls.hidden = false;
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, {once: true});
  else initialize();
})();

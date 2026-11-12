/* Native navigation owns dialog state, Escape handling and focus restoration. */
document.addEventListener('click', (event) => {
  if (event.target.matches('.ellamara-menu .wp-block-navigation__responsive-container.is-menu-open')) {
    event.target.querySelector('.wp-block-navigation__responsive-container-close')?.click();
  }
});

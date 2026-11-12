(() => {
  'use strict';
  const data = window.wp?.data;
  const store = window.wc?.wcBlocksData?.cartStore;
  if (!data || !store) return;
  function sync() {
    const items = data.select(store).getCartData().items || [];
    document.querySelectorAll('.ellamara-pdp-quantity').forEach(root => {
      const quantity = items.filter(item => Number(item.id) === Number(root.dataset.productId)).reduce((sum, item) => sum + item.quantity, 0);
      const stock = Number(root.dataset.stock);
      const available = Math.max(0, Math.min(10, stock < 0 ? 10 : stock - quantity, root.dataset.individual === '1' ? 1 - quantity : 10));
      const select = root.querySelector('select');
      const selected = Math.min(available, Math.max(1, Number(select.value)));
      const values = Array.from({length: available}, (_, i) => i + 1);
      if (select.dataset.limit !== String(available)) {
        select.replaceChildren(...(available ? values.map(value => new Option(`${value} ${value === 1 ? 'ejemplar' : 'ejemplares'}`, value)) : [new Option('Sin unidades adicionales', 0)]));
        select.dataset.limit = String(available);
        select.value = String(selected);
      }
      select.disabled = !available;
      const submit = root.closest('form')?.querySelector('button[type="submit"]');
      if (submit) { submit.disabled = !available; submit.hidden = !available; }
      const viewCart = root.closest('form')?.querySelector('.ellamara-pdp-view-cart');
      if (viewCart) viewCart.hidden = available > 0;
      const help = root.querySelector('.ellamara-pdp-help');
      help.textContent = available ? `${quantity ? `Ya tienes ${quantity} en el carrito. ` : ''}${available > 1 ? `Puedes añadir hasta ${available} ejemplares más.` : ''}` : (stock === 1 ? 'Ya tienes el último ejemplar en tu carrito.' : 'Ya tienes todas las unidades disponibles en tu carrito.');
      help.hidden = !help.textContent;
    });
  }
  data.resolveSelect(store).getCartData().then(() => { sync(); data.subscribe(sync, store); }).catch(() => {});
})();

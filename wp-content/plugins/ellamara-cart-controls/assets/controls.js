/* WooCommerce's public cart store owns requests, nonces, stock validation and session. */
(() => {
  'use strict';
  const data = window.wp?.data;
  const store = window.wc?.wcBlocksData?.cartStore;
  if (!data || !store) return;
  const selectors = data.select(store);
  const actions = data.dispatch(store);
  let ready = false;
  let busy = false;
  let renderScheduled = false;

  const controls = () => [...document.querySelectorAll('.ellamara-purchase')];
  const itemFor = (root) => (selectors.getCartData().items || []).find(item => Number(item.id) === Number(root.dataset.productId));

  function render() {
    renderScheduled = false;
    if (!ready) return;
    for (const root of controls()) {
      const item = itemFor(root);
      const quantity = item?.quantity || 0;
      const max = item?.quantity_limits?.maximum ?? Number(root.dataset.max);
      root.classList.add('is-ready');
      root.querySelector('.ellamara-cart-control').hidden = false;
      root.querySelector('.ellamara-add').hidden = quantity > 0;
      root.querySelector('.ellamara-stepper').hidden = quantity === 0;
      const inCart = root.querySelector('.ellamara-in-cart');
      if (inCart) inCart.hidden = quantity === 0;
      const output = root.querySelector('.ellamara-quantity');
      if (output.textContent !== String(quantity)) output.textContent = String(quantity);
      root.setAttribute('aria-busy', String(busy));
      root.querySelectorAll('[data-cart-action]').forEach(button => {
        button.disabled = busy || (button.dataset.cartAction === 'increase' && max >= 0 && quantity >= max);
      });
    }
  }

  function scheduleRender() {
    if (!renderScheduled) {
      renderScheduled = true;
      requestAnimationFrame(render);
    }
  }

  document.addEventListener('click', async event => {
    const button = event.target.closest('[data-cart-action]');
    const root = button?.closest('.ellamara-purchase.is-ready');
    if (!root || busy || button.disabled) return;
    event.preventDefault();
    const kind = button.dataset.cartAction;
    const status = root.querySelector('.ellamara-cart-status');
    const item = itemFor(root);
    const previousQuantity = item?.quantity || 0;
    status.textContent = '';
    status.classList.remove('is-error');
    busy = true;
    render();
    let succeeded = false;
    try {
      if (kind === 'decrease' && item) {
        if (previousQuantity <= 1) await actions.removeItemFromCart(item.key);
        else await actions.changeCartItemQuantity(item.key, previousQuantity - 1);
      } else if (item) {
        await actions.changeCartItemQuantity(item.key, previousQuantity + 1);
      } else {
        await actions.addItemToCart(Number(root.dataset.productId), 1);
      }
      const quantity = itemFor(root)?.quantity || 0;
      // The server's response is authoritative, including rejected stock changes.
      if (quantity === previousQuantity) {
        const error = selectors.getCartErrors?.()?.at(-1);
        throw new Error(error?.message || 'No se pudo cambiar la cantidad. Revisa la disponibilidad e inténtalo de nuevo.');
      }
      succeeded = true;
      status.textContent = quantity ? `${quantity} ${quantity === 1 ? 'unidad' : 'unidades'} en tu carrito` : 'Producto retirado del carrito';
      document.body.dispatchEvent(new CustomEvent(kind === 'decrease' ? 'wc-blocks_removed_from_cart' : 'wc-blocks_added_to_cart', {
        bubbles: true, detail: { preserveCartData: true },
      }));
    } catch (error) {
      status.classList.add('is-error');
      status.textContent = error.message || 'No se pudo actualizar el carrito. Inténtalo de nuevo.';
    } finally {
      busy = false;
      render();
      if (succeeded && (button.hidden || button.closest('[hidden]'))) {
        const focus = itemFor(root) ? root.querySelector('[data-cart-action="increase"]') : root.querySelector('.ellamara-add');
        (focus.disabled ? root.querySelector('[data-cart-action="decrease"]') : focus).focus();
      }
    }
  });

  // Native cart/mini-cart edits update the same store, so product counters follow them.
  data.subscribe(scheduleRender, store);
  new MutationObserver(records => {
    if (records.some(record => [...record.addedNodes].some(node => node.nodeType === 1 &&
      (node.matches('.ellamara-purchase') || node.querySelector('.ellamara-purchase'))))) scheduleRender();
  }).observe(document.body, { childList: true, subtree: true });

  data.resolveSelect(store).getCartData().then(() => {
    ready = true;
    render();
  }).catch(() => { /* Keep native buttons fully usable if the store fails to load. */ });
})();

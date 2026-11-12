(() => {
    'use strict';
    const placeHelp = () => {
        const checkout = document.querySelector('.wp-block-woocommerce-proceed-to-checkout-block, .wc-proceed-to-checkout');
        if (!checkout || document.querySelector('.ellamara-cart-payment-help')) return;
        const help = document.createElement('aside');
        help.className = 'ellamara-cart-payment-help';
        help.setAttribute('aria-label', 'Opciones de pago por WhatsApp');
        const title = document.createElement('strong');
        title.textContent = '¿Prefieres pagar con Nequi, DaviPlata o PSE?';
        const description = document.createElement('p');
        description.textContent = 'Escríbenos por WhatsApp y te compartiremos las opciones y los pasos para realizar tu pago.';
        const link = document.createElement('a');
        link.href = 'https://wa.me/message/3LIIIQSYBGX5G1';
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.textContent = 'Consultar opciones de pago ↗';
        help.append(title, description, link);
        checkout.after(help);
    };
    placeHelp();
    const cart = document.querySelector('.wp-block-woocommerce-cart, .woocommerce');
    if (cart) new MutationObserver(placeHelp).observe(cart, {childList: true, subtree: true});
})();

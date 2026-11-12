(() => {
    'use strict';
    const dialog = document.getElementById('ellamara-whatsapp-welcome');
    if (!dialog || typeof dialog.showModal !== 'function') return;
    const key = 'ellamara-whatsapp-welcome-seen';
    try {
        if (sessionStorage.getItem(key)) return;
    } catch (_) { /* The invitation still works when browser storage is restricted. */ }
    const previousFocus = document.activeElement;
    const remember = () => {
        try { sessionStorage.setItem(key, '1'); } catch (_) { /* Optional session storage. */ }
    };
    const close = () => dialog.close();
    dialog.querySelector('.ellamara-whatsapp-close').addEventListener('click', close);
    dialog.querySelector('.ellamara-whatsapp-continue').addEventListener('click', close);
    dialog.querySelector('.ellamara-whatsapp-link').addEventListener('click', close);
    dialog.addEventListener('close', () => {
        remember();
        if (previousFocus instanceof HTMLElement) previousFocus.focus();
    });
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const bounds = dialog.getBoundingClientRect();
        if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) close();
    });
    dialog.showModal();
    remember();
})();

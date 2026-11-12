(() => {
    const dialog = document.getElementById('ellamara-catalog-announcement');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    const seenKey = 'ellamara-catalog-announcement-seen';
    try {
        if (sessionStorage.getItem(seenKey)) return;
        sessionStorage.setItem(seenKey, '1');
    } catch {
        return;
    }

    dialog.showModal();
})();

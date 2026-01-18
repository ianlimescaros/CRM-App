// Hard reload helper and post-reload modal.

(function () {
    function getSuccessModal() {
        return document.getElementById('successModal');
    }

    function showModal() {
        const modal = getSuccessModal();
        if (!modal) return;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        const modal = getSuccessModal();
        if (!modal) return;
        modal.classList.add('hidden');
    }

    function hardReload() {
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('_reloaded', String(Date.now()));
            window.location.replace(url.toString());
        } catch (_) {
            window.location.reload();
        }
    }

    function showReloadNoticeIfNeeded() {
        const modal = getSuccessModal();
        if (!modal) return;
        const url = new URL(window.location.href);
        if (!url.searchParams.has('_reloaded')) return;
        showModal();
        url.searchParams.delete('_reloaded');
        window.history.replaceState({}, document.title, url.toString());
    }

    window.hardReload = hardReload;
    window.showModal = showModal;
    window.closeModal = closeModal;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showReloadNoticeIfNeeded);
    } else {
        showReloadNoticeIfNeeded();
    }
})();

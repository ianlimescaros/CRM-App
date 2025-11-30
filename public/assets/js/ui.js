function showToast(message, type = 'info') {
    const bg = type === 'error' ? 'bg-red-600' : type === 'success' ? 'bg-green-600' : 'bg-gray-800';
    const toast = document.createElement('div');
    toast.className = `${bg} text-white px-4 py-2 rounded shadow fixed right-4 top-4 z-50`;
    toast.innerText = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}

function toggle(el, show) {
    if (!el) return;
    el.classList.toggle('hidden', !show);
}

function confirmModal(message) {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black/40 flex items-center justify-center z-50';
        const box = document.createElement('div');
        box.className = 'bg-white rounded shadow-lg p-5 w-80';
        box.innerHTML = `
            <div class="text-gray-800 font-semibold mb-2">Confirm</div>
            <div class="text-gray-600 text-sm mb-4">${message}</div>
            <div class="flex justify-end gap-2">
                <button class="px-3 py-1 border rounded text-sm" data-action="cancel">Cancel</button>
                <button class="px-3 py-1 bg-red-600 text-white rounded text-sm" data-action="ok">Delete</button>
            </div>
        `;
        overlay.appendChild(box);
        document.body.appendChild(overlay);
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay || e.target.dataset.action === 'cancel') {
                overlay.remove();
                resolve(false);
            }
            if (e.target.dataset.action === 'ok') {
                overlay.remove();
                resolve(true);
            }
        });
    });
}

window.ui = { showToast, toggle, confirmModal };

// Shared UI utilities (toast, modal, spinner).

function showToast(message, type = 'info') {
    const bg = type === 'error' ? 'bg-red-600' : type === 'success' ? 'bg-green-600' : 'bg-gray-800';
    const toast = document.createElement('div');
    toast.className = `${bg} text-white px-4 py-2 rounded shadow fixed right-4 top-4 z-50 toast`;
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
    toast.innerText = message;
    document.body.appendChild(toast);
    // animate in
    requestAnimationFrame(() => toast.classList.add('show'));
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 220);
    }, 2500);
}

// Show skeleton rows for list tables. container should be a tbody or list container.
function showListSkeleton(container, rows = 6, cols = 6) {
    if (!container) return;
    // Don't save original content - we'll replace it with rendered data
    const cells = new Array(cols).fill('<div class="skeleton h-3 rounded-md w-full"></div>').join('');
    let html = '';
    for (let i = 0; i < rows; i++) {
        html += `<tr class="list-item-card transition-smooth hover-elevate"><td class="px-3 py-4" colspan="${cols}"><div class="flex items-center gap-3">${cells}</div></td></tr>`;
    }
    container.innerHTML = html;
}

function hideListSkeleton(container) {
    if (!container) return;
    // Just remove the skeleton - don't restore anything. The caller has already set innerHTML with real data.
    // This function is now a no-op but kept for API compatibility
}

function toggle(el, show) {
    if (!el) return;
    el.classList.toggle('hidden', !show);
}

function confirmModal(message) {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4';
        const box = document.createElement('div');
        box.className = 'bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-2xl shadow-2xl w-full max-w-sm';
        const inner = document.createElement('div');
        inner.className = 'bg-white rounded-2xl p-5';
        box.innerHTML = `
            <div class="text-gray-800 font-semibold mb-2">Confirm</div>
            <div class="text-gray-600 text-sm mb-4">${message}</div>
            <div class="flex justify-end gap-2">
                <button class="px-3 py-1 border rounded text-sm" data-action="cancel">Cancel</button>
                <button class="px-3 py-1 bg-blue-600 text-white rounded text-sm" data-action="ok">Confirm</button>
            </div>
        `;
        inner.innerHTML = box.innerHTML;
        box.innerHTML = '';
        box.appendChild(inner);
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

function showSpinner(message = '') {
    // Avoid duplicate
    if (document.getElementById('globalSpinner')) return;
    const overlay = document.createElement('div');
    overlay.id = 'globalSpinner';
    overlay.className = 'fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4';
    overlay.innerHTML = `
        <div class="bg-white rounded-lg p-4 shadow-lg flex items-center gap-3">
            <svg class="h-6 w-6 text-indigo-600 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <div class="text-sm text-gray-700">${message}</div>
        </div>
    `;
    document.body.appendChild(overlay);
}

function hideSpinner() {
    const el = document.getElementById('globalSpinner');
    if (el) el.remove();
}

window.ui = { showToast, toggle, confirmModal, showSpinner, hideSpinner, showListSkeleton, hideListSkeleton };

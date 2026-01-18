// Global search / command palette
(function () {
    const input = document.getElementById('globalSearch');
    if (!input) return;

    let dropdown = null;
    let items = [];
    let selected = -1;
    let mode = 'search'; // or 'command'
    let lastQuery = '';
    let debounceTimer = null;

    function createDropdown() {
        if (dropdown) return dropdown;
        dropdown = document.createElement('div');
        dropdown.id = 'globalSearchDropdown';
        dropdown.className = 'absolute bg-white border border-border rounded shadow-lg z-50 mt-1 w-80 max-h-64 overflow-auto text-sm';
        dropdown.style.display = 'none';
        // accessibility
        dropdown.setAttribute('role', 'listbox');
        dropdown.setAttribute('aria-expanded', 'false');
        document.body.appendChild(dropdown);
        dropdown.addEventListener('click', (e) => {
            const item = e.target.closest('[data-type]');
            if (!item) return;
            const type = item.dataset.type;
            const id = item.dataset.id;
            onSelect(type, id);
        });

        // reposition on scroll/resize when visible
        const reposition = () => {
            if (!dropdown || dropdown.style.display === 'none') return;
            try { positionDropdown(); } catch (__) {}
        };
        window.addEventListener('resize', reposition);
        window.addEventListener('scroll', reposition, { passive: true });

        return dropdown;
    }

    function positionDropdown() {
        const rect = input.getBoundingClientRect();
        const left = Math.max(8, rect.left + window.scrollX);
        dropdown.style.left = `${left}px`;
        // smart vertical placement: prefer below, but flip if not enough space
        const spaceBelow = window.innerHeight - rect.bottom;
        const dropdownH = dropdown.offsetHeight || 220;
        if (spaceBelow > dropdownH + 12) {
            dropdown.style.top = `${rect.bottom + window.scrollY + 6}px`;
            dropdown.classList.remove('origin-top');
        } else {
            // place above
            dropdown.style.top = `${rect.top + window.scrollY - dropdownH - 6}px`;
            dropdown.classList.add('origin-top');
        }
    }

    function renderResults(data) {
        createDropdown();
        positionDropdown();
        items = [];
        selected = -1;
        const clients = (data.clients || []).slice(0,5);
        const leads = (data.leads || []).slice(0,5);
        let html = '';
        if (clients.length) {
            html += '<div class="px-3 py-2 text-xs text-gray-500">Clients</div>';
            clients.forEach((c, i) => {
                html += `<div role="option" data-index="${items.length}" data-type="client" data-id="${c.id}" class="px-3 py-2 hover:bg-gray-50 cursor-pointer flex items-center gap-2">`;
                html += `<div class="flex-1"> <div class="font-medium">${escapeHtml(c.full_name || 'Client')}</div> <div class="text-xs text-gray-500">${escapeHtml(c.email || '')}</div></div>`;
                html += `</div>`;
                items.push({ type: 'client', id: c.id });
            });
        }
        if (leads.length) {
            html += '<div class="px-3 py-2 text-xs text-gray-500">Leads</div>';
            leads.forEach((l, i) => {
                html += `<div role="option" data-index="${items.length}" data-type="lead" data-id="${l.id}" class="px-3 py-2 hover:bg-gray-50 cursor-pointer flex items-center gap-2">`;
                html += `<div class="flex-1"> <div class="font-medium">${escapeHtml(l.name || 'Lead')}</div> <div class="text-xs text-gray-500">${escapeHtml(l.email || '')}</div></div>`;
                html += `</div>`;
                items.push({ type: 'lead', id: l.id });
            });
        }
        if (!clients.length && !leads.length) {
            html = '<div class="px-3 py-3 text-sm text-gray-500">No results</div>';
        }
        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
        dropdown.setAttribute('aria-expanded', 'true');
        // ensure selection state reset
        selected = -1;
        Array.from(dropdown.querySelectorAll('[role="option"]')).forEach(el => el.setAttribute('aria-selected', 'false'));
        // position after content is set so we can compute height
        try { positionDropdown(); } catch (__) {}

    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>\"']/g, function (s) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':"&#39;"})[s];
        });
    }

    function onSelect(type, id) {
        if (type === 'client') {
            window.location = `/index.php?page=client-profile&client_id=${id}`;
            return;
        }
        if (type === 'lead') {
            window.location = `/index.php?page=leads&highlight=${id}`;
            return;
        }
    }

    function closeDropdown() {
        if (!dropdown) return;
        dropdown.style.display = 'none';
    }

    function search(q) {
        lastQuery = q;
        if (!q) {
            renderResults({ clients: [], leads: [] });
            return;
        }
        apiClient.search(q).then((res) => {
            // if query changed since request was started, ignore
            if (lastQuery !== q) return;
            renderResults(res);
        }).catch(() => {
            renderResults({ clients: [], leads: [] });
        });
    }

    input.addEventListener('input', (e) => {
        const q = (e.target.value || '').trim();
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => search(q), 250);
    });

    input.addEventListener('focus', () => {
        // open dropdown if there's text
        const q = (input.value || '').trim();
        if (q) search(q);
    });

    document.addEventListener('click', (e) => {
        if (e.target === input || (dropdown && dropdown.contains(e.target))) return;
        closeDropdown();
    });

    // keyboard shortcuts
    input.addEventListener('keydown', (e) => {
        if (!dropdown || dropdown.style.display === 'none') return;
        const els = Array.from(dropdown.querySelectorAll('[data-type]'));
        if (!els.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selected = Math.min(selected + 1, els.length - 1);
            els.forEach((el, i) => {
                const isSel = i === selected;
                el.classList.toggle('bg-gray-100', isSel);
                el.setAttribute('aria-selected', isSel ? 'true' : 'false');
            });
            els[selected].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selected = Math.max(selected - 1, 0);
            els.forEach((el, i) => {
                const isSel = i === selected;
                el.classList.toggle('bg-gray-100', isSel);
                el.setAttribute('aria-selected', isSel ? 'true' : 'false');
            });
            els[selected].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const el = els[selected] || els[0];
            if (el) el.click();
        } else if (e.key === 'Escape') {
            closeDropdown();
            input.blur();
        }
    });

    // Global keybindings: Ctrl/Cmd+K to focus
    document.addEventListener('keydown', (e) => {
        const tag = (e.target && e.target.tagName || '').toLowerCase();
        if (tag === 'input' || tag === 'textarea' || e.target.isContentEditable) return;
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            input.focus();
            input.select();
        }
    });

    // initialize dropdown container
    createDropdown();
})();

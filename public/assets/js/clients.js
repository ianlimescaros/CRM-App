// Clients list UI and CRUD actions.

function initClients() {
    const tableBody = document.getElementById('contactsTableBody');
    const addBtn = document.getElementById('contactAddBtn');
    const formContainer = document.getElementById('contactFormContainer');
    const form = document.getElementById('contactForm');
    // Accessibility: label forms and tables
    if (form && !form.getAttribute('aria-label')) form.setAttribute('aria-label', 'Client form');
    const contactsTable = tableBody ? tableBody.closest('table') : null;
    if (contactsTable && !contactsTable.getAttribute('role')) contactsTable.setAttribute('role', 'table');
    const cancelBtn = document.getElementById('contactFormCancel');
    const searchInput = document.getElementById('contactSearch');
    const formError = document.getElementById('contactFormError');
    const filesModal = document.getElementById('contactFilesModal');
    const filesList = document.getElementById('contactFilesList');
    const filesSubtitle = document.getElementById('contactFilesSubtitle');
    const filesClose = document.getElementById('contactFilesClose');

    let clientSearch = loadClientSearch();
    if (searchInput && clientSearch) searchInput.value = clientSearch;
    const clientPagination = { page: 1, per_page: 20, total: 0, sort: 'created_at', direction: 'DESC' };

    // Debounced search to avoid excessive API calls
    let searchTimeout;
    function debouncedSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            clientPagination.page = 1; // reset to first page
            loadClients();
        }, 300); // wait 300ms after user stops typing
    }

    async function loadClients() {
        ui.showListSkeleton(tableBody, Math.min(8, clientPagination.per_page || 8), 5);
        try {
            const res = await apiClient.listClients({
                page: clientPagination.page,
                per_page: clientPagination.per_page,
                sort: clientPagination.sort,
                direction: clientPagination.direction,
            });
            let list = res.clients || [];
            if (clientSearch) {
                const q = clientSearch.toLowerCase();
                list = list.filter(c =>
                    (c.full_name || '').toLowerCase().includes(q) ||
                    (c.email || '').toLowerCase().includes(q)
                );
            }
            if (res.meta) {
                clientPagination.total = res.meta.total || 0;
                clientPagination.per_page = res.meta.per_page || clientPagination.per_page;
                clientPagination.page = res.meta.page || clientPagination.page;
            }
            if (!list.length) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5">
                            <div class="py-8 text-center text-gray-600">
                                <div class="mx-auto w-36 h-24">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 48" class="mx-auto" fill="none" stroke="currentColor" stroke-width="1">
                                        <rect x="2" y="8" width="60" height="32" rx="6" stroke-opacity="0.12"></rect>
                                        <path d="M18 22h28" stroke-opacity="0.12"></path>
                                    </svg>
                                </div>
                                <div class="mt-4 font-semibold">No clients yet</div>
                                <div class="text-sm mt-2 text-gray-500">Add a client to start building your contacts.</div>
                                <div class="mt-4">
                                    <button id="addClientAction" class="px-3 py-1 bg-indigo-600 text-white rounded">Add client</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
                const addAct = document.getElementById('addClientAction');
                if (addAct) addAct.addEventListener('click', () => addBtn?.click());
                ui.hideListSkeleton(tableBody);
                return;
            }
            tableBody.innerHTML = list.map(clientRow).join('');
            // denser list
            if (tableBody && tableBody.closest('table')) tableBody.closest('table').classList.add('list-dense');
            attachActions();
            renderClientPagination();
            ui.hideListSkeleton(tableBody);
        } catch (err) {
            ui.hideListSkeleton(tableBody);
            ui.showToast('Failed to load clients', 'error');
        }
    }

    function clientRow(client) {
        return `
            <tr data-id="${client.id}" class="transition-smooth hover-elevate list-dense">
                <td class="px-3 py-2">${escapeHtml(client.full_name)}</td>
                <td class="px-3 py-2">${client.email ? escapeHtml(client.email) : ''}</td>
                <td class="px-3 py-2">${client.phone ? escapeHtml(client.phone) : ''}</td>
                <td class="px-3 py-2">
                    <button class="text-indigo-600 hover:underline contact-files">View</button>
                </td>
                <td class="px-3 py-2 space-x-2">
                    <a class="text-indigo-600 contact-profile" href="/index.php?page=client-profile&client_id=${client.id}">Profile</a>
                    <button class="text-blue-600 contact-edit">Edit</button>
                    <button class="text-red-600 contact-delete">Delete</button>
                </td>
            </tr>
        `;
    }

    function attachActions() {
        tableBody.querySelectorAll('.contact-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('tr').dataset.id;
                const row = e.target.closest('tr').children;
                if (formError) formError.classList.add('hidden');
                form.elements.id.value = id;
                form.elements.full_name.value = row[0].innerText;
                form.elements.email.value = row[1].innerText;
                form.elements.phone.value = row[2].innerText;
                document.getElementById('contactFormTitle').innerText = 'Edit Client/Landlord';
                ui.toggle(formContainer, true);
            });
        });
        tableBody.querySelectorAll('.contact-delete').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                const ok = await ui.confirmModal('Delete this client?');
                if (!ok) return;
                try {
                    await apiClient.deleteClient(id);
                    ui.showToast('Client deleted', 'success');
                    loadClients();
                } catch (err) {
                    ui.showToast('Delete failed', 'error');
                }
            });
        });
        tableBody.querySelectorAll('.contact-files').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                await openFilesModal(id);
            });
        });
    }

    addBtn?.addEventListener('click', () => {
        form.reset();
        form.elements.id.value = '';
        if (formError) formError.classList.add('hidden');
        document.getElementById('contactFormTitle').innerText = 'New Client/Landlord';
        ui.toggle(formContainer, true);
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        formError?.classList.add('hidden');
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        const id = payload.id;
        delete payload.id;
        const docInputs = Array.from(document.querySelectorAll('[data-doc-label]'));
        const docsToUpload = docInputs
            .map(inp => ({ file: inp.files?.[0], label: inp.dataset.docLabel || '' }))
            .filter(d => d.file);
        try {
            let savedId = id;
            if (id) {
                const res = await apiClient.updateClient(id, payload);
                savedId = res?.client?.id || id;
                ui.showToast('Client updated', 'success');
            } else {
                const res = await apiClient.createClient(payload);
                savedId = res?.client?.id;
                ui.showToast('Client added', 'success');
            }
            if (savedId && docsToUpload.length) {
                for (const doc of docsToUpload) {
                    const name = doc.label || doc.file.name;
                    await uploadClientDocument(savedId, doc.file, name);
                    ui.showToast(`${name} uploaded`, 'success');
                }
                docInputs.forEach(inp => (inp.value = ''));
            }
            ui.toggle(formContainer, false);
            loadClients();
        } catch (err) {
            const message = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Save failed');
            if (formError) {
                formError.textContent = message;
                formError.classList.remove('hidden');
            }
            ui.showToast(message, 'error');
        }
    });

    cancelBtn?.addEventListener('click', () => ui.toggle(formContainer, false));

    searchInput?.addEventListener('input', (e) => {
        clientSearch = e.target.value || '';
        saveClientSearch(clientSearch);
        debouncedSearch(); // Use debounced version instead of immediate call
    });

    // Keyboard shortcuts: Esc closes form, "n" or "/" opens new (when not typing)
    document.addEventListener('keydown', (e) => {
        const tag = (e.target.tagName || '').toLowerCase();
        const typing = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;
        if (e.key === 'Escape' && formContainer && !formContainer.classList.contains('hidden')) {
            e.preventDefault();
            ui.toggle(formContainer, false);
            return;
        }
        if (!typing && (e.key === 'n' || e.key === '/')) {
            e.preventDefault();
            addBtn?.click();
        }
    });

    const hideFilesModal = () => { ui.toggle(filesModal, false); detachFilesEsc(); };
    filesClose?.addEventListener('click', hideFilesModal);
    filesModal?.addEventListener('click', (e) => {
        if (e.target === filesModal) hideFilesModal();
    });
    // attach esc when showing
    let filesEscHandler;
    const attachFilesEsc = () => {
        if (filesEscHandler) return;
        filesEscHandler = (e) => {
            if (e.key === 'Escape' && filesModal && !filesModal.classList.contains('hidden')) {
                hideFilesModal();
                detachFilesEsc();
            }
        };
        document.addEventListener('keydown', filesEscHandler);
    };
    const detachFilesEsc = () => {
        if (!filesEscHandler) return;
        document.removeEventListener('keydown', filesEscHandler);
        filesEscHandler = null;
    };

    function saveClientSearch(value) {
        localStorage.setItem('crm_client_search', value);
        // keep old key synced for anyone still reading it elsewhere
        localStorage.setItem('crm_contact_search', value);
    }

    function loadClientSearch() {
        return localStorage.getItem('crm_client_search') || localStorage.getItem('crm_contact_search') || '';
    }

    function renderClientPagination() {
        const container = document.getElementById('contactPagination');
        if (!container) return;
        const totalPages = Math.max(1, Math.ceil((clientPagination.total || 0) / clientPagination.per_page));
        const page = clientPagination.page;
        container.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-gray-700 mt-2">
                <button id="contactPagePrev" class="px-3 py-1 border border-border rounded ${page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Prev</button>
                <span>Page ${page} of ${totalPages}</span>
                <button id="contactPageNext" class="px-3 py-1 border border-border rounded ${page >= totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Next</button>
                <span class="text-xs text-gray-500">${clientPagination.total || 0} results</span>
            </div>
        `;
        const prev = document.getElementById('contactPagePrev');
        const next = document.getElementById('contactPageNext');
        if (prev && page > 1) {
            prev.addEventListener('click', () => {
                clientPagination.page = Math.max(1, page - 1);
                loadClients();
            });
        }
        if (next && page < totalPages) {
            next.addEventListener('click', () => {
                clientPagination.page = Math.min(totalPages, page + 1);
                loadClients();
            });
        }
    }

    async function openFilesModal(clientId) {
        if (!filesModal || !filesList) return;
        filesList.innerHTML = '<div class="text-gray-500">Loading...</div>';
        if (filesSubtitle) {
            filesSubtitle.textContent = `Client ID: ${clientId}`;
        }
        ui.toggle(filesModal, true);
        attachFilesEsc();
        try {
            const res = await apiClient.getClientFiles(clientId);
            const files = res.files || [];
            if (!files.length) {
                filesList.innerHTML = '<div class="text-gray-500 text-sm">No documents uploaded.</div>';
                return;
            }
            filesList.innerHTML = files.map(f => {
                const size = f.size_label ? ` (${escapeHtml(f.size_label)})` : '';
                const downloadUrl = `/api.php/clients/${clientId}/files/${f.id}/download`;
                return `<div class="flex items-center justify-between border border-border rounded px-3 py-2">
                    <div class="flex-1">
                        <div class="font-medium">${escapeHtml(f.name || 'Document')}</div>
                        <div class="text-xs text-gray-500">${f.url ? 'Link' : 'Uploaded file'}${size}</div>
                    </div>
                    ${f.url ? `<a class="text-indigo-600 hover:underline text-sm" href="${escapeHtml(f.url)}" target="_blank" rel="noopener">Open</a>` : `<a class="text-indigo-600 hover:underline text-sm" href="${downloadUrl}">Download</a>`}
                </div>`;
            }).join('');
        } catch (err) {
            filesList.innerHTML = '';
            const errEl = document.createElement('div');
            errEl.className = 'text-sm text-red-600';
            errEl.textContent = err?.message || 'Failed to load documents';
            filesList.appendChild(errEl);
        }
    }

    async function uploadClientDocument(clientId, file, name = '') {
        const token = apiClient.getToken();
        const headers = {};
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        const csrf = typeof getCookie === 'function' ? getCookie('csrf_token') : null;
        if (csrf) {
            headers['X-CSRF-Token'] = csrf;
        }
        const fd = new FormData();
        fd.append('file', file);
        if (name) {
            fd.append('name', name);
        }
        const res = await fetch(`/api.php/clients/${clientId}/files`, {
            method: 'POST',
            headers,
            body: fd,
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || data.success === false) {
            const msg = data.message || 'Upload failed';
            throw new Error(msg);
        }
        return data;
    }

    loadClients();
}

function initDeals() {
    const tableBody = document.getElementById('dealsTableBody');
    const addBtn = document.getElementById('dealAddBtn');
    const formContainer = document.getElementById('dealFormContainer');
    const form = document.getElementById('dealForm');
    const cancelBtn = document.getElementById('dealFormCancel');
    const stageFilter = document.getElementById('dealStageFilter');
    const filterBtn = document.getElementById('dealFilterBtn');
    const formError = document.getElementById('dealFormError');
    const dealLeadSelect = document.getElementById('dealLeadSelect');
    const dealClientSelect = document.getElementById('dealClientSelect');
    const dealCurrency = document.getElementById('dealCurrency');
    const filesModal = document.getElementById('dealFilesModal');
    const filesList = document.getElementById('dealFilesList');
    const filesSubtitle = document.getElementById('dealFilesSubtitle');
    const filesClose = document.getElementById('dealFilesClose');
    const dealDocInput = document.getElementById('dealDocInput');
    let currentFilesDealId = null;

    let currentFilters = {};
    let dealData = [];
    let leadsCache = [];
    let clientsCache = [];
    const dealPagination = { page: 1, per_page: 20, sort: 'created_at', direction: 'DESC', total: 0 };

    const buildPropertyDetail = (lead) => {
        if (!lead) return '';
        const parts = [];
        if (lead.interested_property) parts.push(lead.interested_property);
        if (lead.property_for) parts.push(lead.property_for);
        if (lead.area) parts.push(lead.area);
        return parts.join(' | ');
    };

    const formatInputAmount = (value) => {
        const num = Number(String(value || '').replace(/,/g, ''));
        if (Number.isNaN(num)) return '';
        return new Intl.NumberFormat('en-US').format(num);
    };

    async function loadDeals() {
        try {
            const res = await apiClient.listDeals({
                ...currentFilters,
                page: dealPagination.page,
                per_page: dealPagination.per_page,
                sort: dealPagination.sort,
                direction: dealPagination.direction,
            });
            dealData = res.deals || [];
            if (res.meta) {
                dealPagination.total = res.meta.total || 0;
                dealPagination.page = res.meta.page || dealPagination.page;
                dealPagination.per_page = res.meta.per_page || dealPagination.per_page;
            }
            tableBody.innerHTML = dealData.map(dealRow).join('');
            attachActions();
            renderDealPagination();
        } catch (err) {
            ui.showToast('Failed to load deals', 'error');
        }
    }

    function dealRow(deal) {
        const stageColors = {
            ongoing: 'bg-blue-50 text-blue-700',
            pending: 'bg-amber-50 text-amber-700',
        };
        const amountDisplay = formatAmount(deal.amount, deal.currency);
        return `
            <tr data-id="${deal.id}" data-stage="${deal.stage || ''}" data-lead-id="${deal.lead_id || ''}" data-client-id="${deal.client_id || ''}" data-client-name="${deal.client_name || ''}" data-amount="${deal.amount ?? ''}" data-currency="${deal.currency || ''}" data-location="${deal.location || ''}" data-property-detail="${deal.property_detail || ''}" class="border-b last:border-b-0 even:bg-gray-50 hover:bg-gray-100">
                <td class="px-3 py-2">${escapeHtml(deal.title)}</td>
                <td class="px-3 py-2">
                    <span class="inline-flex px-2 py-1 rounded text-xs ${stageColors[deal.stage] || 'bg-gray-100 text-gray-700'}">${formatStageLabel(deal.stage)}</span>
                </td>
                <td class="px-3 py-2">${amountDisplay}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${escapeHtml(clientLabel(deal.client_id) || deal.client_name || '')}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${deal.location ? escapeHtml(deal.location) : ''}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${deal.property_detail ? escapeHtml(deal.property_detail) : ''}</td>
                <td class="px-3 py-2">${deal.close_date ? escapeHtml(String(deal.close_date)) : ''}</td>
                <td class="px-3 py-2"><button class="text-indigo-600 hover:underline deal-files">View</button></td>
                <td class="px-3 py-2 space-x-2">
                    <button class="text-blue-600 deal-edit">Edit</button>
                    <button class="text-red-600 deal-delete">Delete</button>
                </td>
            </tr>
        `;
    }

    function attachActions() {
        tableBody.querySelectorAll('.deal-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('tr').dataset.id;
                const row = e.target.closest('tr').children;
                if (formError) formError.classList.add('hidden');
                form.elements.id.value = id;
                form.elements.title.value = row[0].innerText;
                const currentStage = e.target.closest('tr').dataset.stage || 'ongoing';
                form.elements.stage.value = currentStage;
                if (!form.elements.stage.value) {
                    form.elements.stage.value = 'ongoing';
                }
                const currentDeal = dealData.find(d => String(d.id) === String(id)) || {};
                form.elements.amount.value = formatInputAmount(currentDeal.amount);
                if (dealCurrency) {
                    dealCurrency.value = currentDeal.currency || 'AED';
                }
                if (form.elements.location) form.elements.location.value = currentDeal.location || '';
                const propDetail = form.querySelector('[name="property_detail"]');
                if (propDetail) propDetail.value = currentDeal.property_detail || '';
                form.elements.close_date.value = row[6]?.innerText || '';
                const leadId = e.target.closest('tr').dataset.leadId || '';
                const clientId = e.target.closest('tr').dataset.clientId || '';
                if (dealClientSelect) dealClientSelect.value = clientId;
                if (dealLeadSelect) dealLeadSelect.value = leadId;
                if (dealClientSelect) dealClientSelect.value = clientId;
                document.getElementById('dealFormTitle').innerText = 'Edit Deal';
                formContainer?.classList.remove('hidden');
                formContainer?.classList.add('flex');
                formContainer && (formContainer.style.display = 'flex');
            });
        });
        tableBody.querySelectorAll('.deal-delete').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                const ok = await ui.confirmModal('Delete this deal?');
                if (!ok) return;
                try {
                    await apiClient.deleteDeal(id);
                    ui.showToast('Deal deleted', 'success');
                    loadDeals();
                } catch (err) {
                    ui.showToast('Delete failed', 'error');
                }
            });
        });
        tableBody.querySelectorAll('.deal-files').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                await openFilesModal(id);
            });
        });
    }

    const showForm = () => {
        if (!formContainer) return;
        form.reset();
        form.elements.id.value = '';
        if (formError) formError.classList.add('hidden');
        if (dealLeadSelect) dealLeadSelect.value = '';
        if (dealClientSelect) dealClientSelect.value = '';
        if (dealCurrency) dealCurrency.value = dealCurrency.value || 'AED';
        if (form.elements.stage) form.elements.stage.value = 'ongoing';
        const propDetail = form.querySelector('[name="property_detail"]');
        if (propDetail) propDetail.value = '';
        if (form.elements.location) form.elements.location.value = '';
        if (form.elements.amount) form.elements.amount.value = '';
        document.getElementById('dealFormTitle').innerText = 'New Deal';
        formContainer.classList.remove('hidden');
        formContainer.classList.add('flex');
        formContainer.style.display = 'flex';
    };
    const hideForm = () => {
        if (!formContainer) return;
        formContainer.classList.add('hidden');
        formContainer.classList.remove('flex');
        formContainer.style.display = 'none';
    };

    addBtn?.addEventListener('click', showForm);
    cancelBtn?.addEventListener('click', hideForm);

    // Keyboard shortcuts: Esc to close modal, "n" or "/" to open New Deal (when not typing in an input)
    document.addEventListener('keydown', (e) => {
        const tag = (e.target.tagName || '').toLowerCase();
        const typing = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;
        if (e.key === 'Escape' && formContainer && !formContainer.classList.contains('hidden')) {
            e.preventDefault();
            hideForm();
            return;
        }
        if (!typing && (e.key === 'n' || e.key === '/')) {
            e.preventDefault();
            showForm();
        }
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(form).entries());
        if (payload.amount) { payload.amount = payload.amount.replace(/,/g, '').trim(); }
        if (!payload.stage) { payload.stage = 'ongoing'; }
        if (!payload.currency && dealCurrency) { payload.currency = dealCurrency.value || 'AED'; }
        if (!payload.lead_id) payload.lead_id = null;
        payload.client_id = payload.client_id || payload.contact_id || null;
        delete payload.contact_id;
        const id = payload.id;
        delete payload.id;
        try {
            if (id) {
                await apiClient.updateDeal(id, payload);
                ui.showToast('Deal updated', 'success');
            } else {
                const created = await apiClient.createDeal(payload);
                payload.id = created?.deal?.id || id;
                ui.showToast('Deal created', 'success');
            }
            const dealIdToUse = payload.id || id;
            const docFile = dealDocInput?.files?.[0];
            if (dealIdToUse && docFile) {
                const fd = new FormData();
                fd.append('file', docFile);
                await apiClient.addDealFile(dealIdToUse, fd);
                ui.showToast('Deal document uploaded', 'success');
                if (dealDocInput) dealDocInput.value = '';
            }
            hideForm();
            loadDeals();
        } catch (err) {
            ui.showToast(err.message || 'Save failed', 'error');
            if (formError) {
                formError.textContent = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Save failed');
                formError.classList.remove('hidden');
            }
        }
    });

    filterBtn?.addEventListener('click', () => {
        currentFilters = { stage: stageFilter.value || '' };
        dealPagination.page = 1;
        loadDeals();
    });

    function populateDealSelects() {
        if (dealLeadSelect) {
            dealLeadSelect.innerHTML = `<option value="">None</option>` + leadsCache.map(l => `
                <option value="${l.id}">${escapeHtml(l.name || '')} (#${l.id})</option>
            `).join('');
        }
        if (dealClientSelect) {
            dealClientSelect.innerHTML = `<option value="">None</option>` + clientsCache.map(c => `
                <option value="${c.id}">${escapeHtml(c.full_name || '')} (#${c.id})</option>
            `).join('');
        }
    }

    Promise.all([apiClient.listLeads(), apiClient.listClients()])
        .then(([leadsRes, clientsRes]) => {
            leadsCache = leadsRes.leads || [];
            clientsCache = clientsRes.clients || [];
            populateDealSelects();
        })
        .catch(() => {
            leadsCache = [];
            clientsCache = [];
        });

    dealLeadSelect?.addEventListener('change', () => {
        const leadId = dealLeadSelect.value;
        const lead = leadsCache.find(l => String(l.id) === String(leadId));
        if (!lead) return;
        if (form.elements.title) {
            form.elements.title.value = lead.name || '';
        }
        if (form.elements.location) {
            form.elements.location.value = lead.area || '';
        }
        const propDetailField = form.querySelector('[name=\"property_detail\"]');
        if (propDetailField) {
            propDetailField.value = buildPropertyDetail(lead) || '';
        }
        if (form.elements.amount) {
            form.elements.amount.value = lead.budget ? formatInputAmount(lead.budget) : '';
        }
        if (form.elements.currency && lead.currency) {
            form.elements.currency.value = lead.currency;
        }
    });

    const hideFilesModal = () => { ui.toggle(filesModal, false); detachFilesEsc(); currentFilesDealId = null; };
    filesClose?.addEventListener('click', hideFilesModal);
    filesModal?.addEventListener('click', (e) => {
        if (e.target === filesModal) hideFilesModal();
    });
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

    async function openFilesModal(dealId) {
        if (!filesModal || !filesList) return;
        currentFilesDealId = dealId;
        filesList.innerHTML = '<div class="text-gray-500">Loading...</div>';
        if (filesSubtitle) filesSubtitle.textContent = `Deal ID: ${dealId}`;
        ui.toggle(filesModal, true);
        attachFilesEsc();
        try {
            const res = await apiClient.getDealFiles(dealId);
            const files = res.files || [];
            if (!files.length) {
                filesList.innerHTML = '<div class="text-gray-500 text-sm">No documents uploaded.</div>';
                return;
            }
            const tokenParam = apiClient.getToken() ? `?token=${encodeURIComponent(apiClient.getToken())}` : '';
            filesList.innerHTML = files.map(f => {
                const size = f.size_label ? ` - ${escapeHtml(f.size_label)}` : '';
                const downloadUrl = `/api.php/deals/${dealId}/files/${f.id}/download${tokenParam}`;
                const actionLink = f.url
                    ? `<a class="text-indigo-600 hover:underline text-sm" href="${escapeHtml(f.url)}" target="_blank" rel="noopener">Open</a>`
                    : `<a class="text-indigo-600 hover:underline text-sm" href="${downloadUrl}">Download</a>`;
                return `<div class="flex items-center justify-between border border-border rounded px-3 py-2 gap-3">
                    <div class="flex-1">
                        <div class="font-medium">${escapeHtml(f.name || 'Document')}</div>
                        <div class="text-xs text-gray-500">${f.url ? 'Link' : 'Uploaded file'}${size}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        ${actionLink}
                        <button type="button" class="text-sm text-red-600 hover:underline" data-file-id="${f.id}" data-action="delete">Delete</button>
                    </div>
                </div>`;
            }).join('');
        } catch (err) {
            filesList.innerHTML = `<div class="text-sm text-red-600">${err?.message || 'Failed to load documents'}</div>`;
        }
    }

    // Upload handled in deal form submit; no upload UI in the view modal
    filesList?.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-file-id]');
        if (!btn || !currentFilesDealId) return;
        const action = btn.dataset.action;
        const fileId = btn.dataset.fileId;
        if (action === 'delete' && fileId) {
            const ok = await ui.confirmModal('Delete this document?');
            if (!ok) return;
            try {
                await apiClient.deleteDealFile(currentFilesDealId, fileId);
                ui.showToast('Document deleted', 'success');
                await openFilesModal(currentFilesDealId);
            } catch (err) {
                ui.showToast(err.message || 'Delete failed', 'error');
            }
        }
    });

    function renderDealPagination() {
        const container = document.getElementById('dealPagination');
        if (!container) return;
        const totalPages = Math.max(1, Math.ceil((dealPagination.total || 0) / dealPagination.per_page));
        const page = dealPagination.page;
        container.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-gray-700 mt-2">
                <button id="dealPagePrev" class="px-3 py-1 border border-border rounded ${page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Prev</button>
                <span>Page ${page} of ${totalPages}</span>
                <button id="dealPageNext" class="px-3 py-1 border border-border rounded ${page >= totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Next</button>
                <span class="text-xs text-gray-500">${dealPagination.total || 0} results</span>
            </div>
        `;
        const prev = document.getElementById('dealPagePrev');
        const next = document.getElementById('dealPageNext');
        if (prev && page > 1) {
            prev.addEventListener('click', () => {
                dealPagination.page = Math.max(1, page - 1);
                loadDeals();
            });
        }
        if (next && page < totalPages) {
            next.addEventListener('click', () => {
                dealPagination.page = Math.min(totalPages, page + 1);
                loadDeals();
            });
        }
    }

    function formatAmount(amount, currency) {
        if (amount === null || amount === undefined || amount === '') return '';
        const num = Number(String(amount).replace(/,/g, ''));
        const formatted = Number.isNaN(num) ? amount : new Intl.NumberFormat('en-US').format(num);
        return `${currency ? `${currency} ` : ''}${formatted}`;
    }

    const formatStageLabel = (stage) => {
        const labels = { ongoing: 'Ongoing', pending: 'Pending' };
        return labels[stage] || (stage ? stage.charAt(0).toUpperCase() + stage.slice(1) : '');
    };

    const clientLabel = (clientId) => {
        if (!clientId) return '';
        const c = clientsCache.find(x => String(x.id) === String(clientId));
        return c?.full_name || '';
    };

    loadDeals();
}

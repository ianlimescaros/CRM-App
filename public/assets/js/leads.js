// Leads UI, filters, and archive actions.

function initLeads() {
    const tableBody = document.getElementById('leadsTableBody');
    const addBtn = document.getElementById('leadAddBtn');
    const formContainer = document.getElementById('leadFormContainer');
    const form = document.getElementById('leadForm');
    // Accessibility: label forms and tables for screen readers
    if (form && !form.getAttribute('aria-label')) form.setAttribute('aria-label', 'Lead form');
    const leadTable = tableBody ? tableBody.closest('table') : null;
    if (leadTable && !leadTable.getAttribute('role')) leadTable.setAttribute('role', 'table');
    const cancelBtn = document.getElementById('leadFormCancel');
    const statusFilter = document.getElementById('leadStatusFilter');
    const sourceFilter = document.getElementById('leadSourceFilter');
    const archiveFilter = document.getElementById('leadArchiveFilter');
    const filterBtn = document.getElementById('leadFilterBtn');
    const formError = document.getElementById('leadFormError');
    const leadTableWrap = document.getElementById('leadTableWrap');
    const leadKanban = document.getElementById('leadKanban');
    const viewTableBtn = document.getElementById('leadViewTable');
    const viewKanbanBtn = document.getElementById('leadViewKanban');
    const leadSelectAll = document.getElementById('leadSelectAll');
    const leadHeaderCheckbox = document.getElementById('leadHeaderCheckbox');
    const leadBulkStatus = document.getElementById('leadBulkStatus');
    const leadBulkApply = document.getElementById('leadBulkApply');
    const leadSort = document.getElementById('leadSort');
    const leadPagination = { page: 1, per_page: 20, sort: 'created_at', direction: 'DESC', total: 0 };
    const leadStatusLabels = {
        new: 'New',
        contacted: 'Contacted',
        qualified: 'Qualified',
        not_qualified: 'Not Qualified',
    };

    let currentFilters = loadLeadFilters();
    if (!currentFilters.archived) currentFilters.archived = 'active';
    let leadData = [];
    let viewMode = localStorage.getItem('crm_lead_view') || 'table';
    let selectedLeadIds = new Set();

    if (statusFilter && currentFilters.status) statusFilter.value = currentFilters.status;
    if (sourceFilter && currentFilters.source) sourceFilter.value = currentFilters.source;
    if (archiveFilter && currentFilters.archived) archiveFilter.value = currentFilters.archived;

    async function loadLeads() {
        // show skeleton while loading
        ui.showListSkeleton(tableBody, Math.min(8, leadPagination.per_page || 8), 11);
        try {
            const res = await apiClient.listLeads({
                ...currentFilters,
                page: leadPagination.page,
                per_page: leadPagination.per_page,
                sort: leadPagination.sort,
                direction: leadPagination.direction,
            });
            leadData = res.leads || [];
            if (res.meta) {
                leadPagination.total = res.meta.total || 0;
                leadPagination.per_page = res.meta.per_page || leadPagination.per_page;
                leadPagination.page = res.meta.page || leadPagination.page;
            }
            renderLeads();
            ui.hideListSkeleton(tableBody);
        } catch (err) {
            ui.hideListSkeleton(tableBody);
            ui.showToast('Failed to load leads', 'error');
        }
    }

    function renderLeads() {
        if (viewMode === 'kanban') {
            renderKanban();
            ui.toggle(leadKanban, true);
            ui.toggle(leadTableWrap, false);
            setViewButtons();
            return;
        }
        ui.toggle(leadKanban, false);
        ui.toggle(leadTableWrap, true);
        // make table denser for power users
        if (tableBody && tableBody.closest('table')) tableBody.closest('table').classList.add('list-dense');
        if (!leadData.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="11">
                        <div class="py-8 text-center text-gray-600">
                            <div class="mx-auto w-36 h-24">
                                <!-- simple illustration -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 48" class="mx-auto" fill="none" stroke="currentColor" stroke-width="1">
                                    <rect x="2" y="8" width="60" height="32" rx="6" stroke-opacity="0.12"></rect>
                                    <path d="M10 20h44" stroke-opacity="0.12"></path>
                                    <circle cx="16" cy="26" r="3" fill="currentColor" style="opacity:.08"></circle>
                                </svg>
                            </div>
                            <div class="mt-4 font-semibold">No leads yet</div>
                            <div class="text-sm mt-2 text-gray-500">Add your first lead to get started.</div>
                            <div class="mt-4">
                                <button id="addLeadAction" class="px-3 py-1 bg-indigo-600 text-white rounded">Add a lead</button>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
            const addAct = document.getElementById('addLeadAction');
            if (addAct) addAct.addEventListener('click', () => addBtn?.click());
            return;
        }
        tableBody.innerHTML = leadData.map(leadRow).join('');
        attachLeadActions();
        updateLeadSelectState();
        setViewButtons();
        renderLeadPagination();
    }

    function setViewButtons() {
        if (!viewTableBtn || !viewKanbanBtn) return;
        if (viewMode === 'table') {
            viewTableBtn.classList.add('bg-accent', 'text-white');
            viewKanbanBtn.classList.remove('bg-accent', 'text-white');
            viewKanbanBtn.classList.add('text-gray-700');
        } else {
            viewKanbanBtn.classList.add('bg-accent', 'text-white');
            viewTableBtn.classList.remove('bg-accent', 'text-white');
            viewTableBtn.classList.add('text-gray-700');
        }
    }

    function renderLeadPagination() {
        const container = document.getElementById('leadPagination');
        if (!container) return;
        const totalPages = Math.max(1, Math.ceil((leadPagination.total || 0) / leadPagination.per_page));
        const page = leadPagination.page;
        container.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-gray-700 mt-2">
                <button id="leadPagePrev" class="px-3 py-1 border border-border rounded ${page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Prev</button>
                <span>Page ${page} of ${totalPages}</span>
                <button id="leadPageNext" class="px-3 py-1 border border-border rounded ${page >= totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Next</button>
                <span class="text-xs text-gray-500">${leadPagination.total || 0} results</span>
            </div>
        `;
        const prev = document.getElementById('leadPagePrev');
        const next = document.getElementById('leadPageNext');
        if (prev && page > 1) {
            prev.addEventListener('click', () => {
                leadPagination.page = Math.max(1, page - 1);
                loadLeads();
            });
        }
        if (next && page < totalPages) {
            next.addEventListener('click', () => {
                leadPagination.page = Math.min(totalPages, page + 1);
                loadLeads();
            });
        }
    }

    function updateLeadSelectState() {
        if (leadHeaderCheckbox) {
            const allIds = leadData.map(l => String(l.id));
            const allSelected = allIds.length > 0 && allIds.every(id => selectedLeadIds.has(id));
            leadHeaderCheckbox.checked = allSelected;
        }
        if (leadSelectAll) {
            leadSelectAll.checked = selectedLeadIds.size > 0 && selectedLeadIds.size === leadData.length;
        }
    }

    function formatLeadStatus(status) {
        return leadStatusLabels[status] || status || '';
    }

    function formatLeadStatusClass(status) {
        switch ((status || '').toLowerCase()) {
            case 'new': return 'status-badge status-new';
            case 'contacted': return 'status-badge status-contacted';
            case 'qualified': return 'status-badge status-qualified';
            case 'not_qualified': return 'status-badge status-not_qualified';
            default: return 'status-badge bg-gray-200 text-gray-800';
        }
    }

    function formatPropertyFor(value) {
        return value || '';
    }

    function formatBudget(lead) {
        if (lead.budget === null || lead.budget === undefined || lead.budget === '') return '';
        const num = Number(String(lead.budget).replace(/,/g, ''));
        if (Number.isNaN(num)) return escapeHtml(String(lead.budget));
        const formatted = new Intl.NumberFormat('en-US').format(num);
        return `${lead.currency ? lead.currency + ' ' : ''}${formatted}`;
    }

    function leadRow(lead) {
        const archivedBadge = lead.archived_at ? '<span class="ml-2 text-xs px-2 py-1 rounded bg-gray-200 text-gray-700">Archived</span>' : '';
        const archiveAction = lead.archived_at
            ? '<button class="text-indigo-600 lead-restore">Restore</button>'
            : '<button class="text-amber-600 lead-archive">Archive</button>';
        const rowClass = lead.archived_at ? 'opacity-75' : '';
        return `
            <tr data-id="${lead.id}" data-status="${lead.status || ''}" data-property="${lead.interested_property || ''}" data-property-for="${lead.property_for || ''}" data-area="${lead.area || ''}" data-last-contact="${lead.last_contact_at || ''}" class="transition-smooth hover-elevate ${rowClass}">
                <td class="px-3 py-2"><input type="checkbox" class="lead-select h-4 w-4 border border-border rounded" data-id="${lead.id}" ${selectedLeadIds.has(String(lead.id)) ? 'checked' : ''}></td>
                <td class="px-3 py-2">${escapeHtml(lead.name)}</td>
                <td class="px-3 py-2">${lead.phone ? escapeHtml(String(lead.phone)) : '--'}</td>
                <td class="px-3 py-2">
                    <span class="${formatLeadStatusClass(lead.status)}">${formatLeadStatus(lead.status)}</span>${archivedBadge}
                </td>
                <td class="px-3 py-2">${escapeHtml(lead.source || '')}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${lead.property_for ? escapeHtml(formatPropertyFor(lead.property_for)) : '--'}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${lead.interested_property ? escapeHtml(String(lead.interested_property)) : '--'}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${lead.area ? escapeHtml(String(lead.area)) : '--'}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${lead.last_contact_at ? escapeHtml(String(lead.last_contact_at)) : '--'}</td>
                <td class="px-3 py-2">${formatBudget(lead)}</td>
                <td class="px-3 py-2 space-x-2">
                    <button class="text-blue-600 lead-edit">Edit</button>
                    ${archiveAction}
                    <button class="text-red-600 lead-delete">Delete</button>
                </td>
            </tr>
        `;
    }

    function attachLeadActions() {
        tableBody.querySelectorAll('.lead-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('tr').dataset.id;
                startEdit(id);
            });
        });
        tableBody.querySelectorAll('.lead-delete').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                const ok = await ui.confirmModal('Delete this lead?');
                if (!ok) return;
                try {
                    await apiClient.deleteLead(id);
                    ui.showToast('Lead deleted', 'success');
                    loadLeads();
                } catch (err) {
                    ui.showToast('Delete failed', 'error');
                }
            });
        });
        tableBody.querySelectorAll('.lead-archive').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                const ok = await ui.confirmModal('Archive this lead?');
                if (!ok) return;
                try {
                    await apiClient.archiveLead(id);
                    ui.showToast('Lead archived', 'success');
                    loadLeads();
                } catch (err) {
                    ui.showToast(err?.message || 'Archive failed', 'error');
                }
            });
        });
        tableBody.querySelectorAll('.lead-restore').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                const ok = await ui.confirmModal('Restore this lead?');
                if (!ok) return;
                try {
                    await apiClient.restoreLead(id);
                    ui.showToast('Lead restored', 'success');
                    loadLeads();
                } catch (err) {
                    ui.showToast(err?.message || 'Restore failed', 'error');
                }
            });
        });
        tableBody.querySelectorAll('.lead-select').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const id = e.target.dataset.id;
                if (e.target.checked) {
                    selectedLeadIds.add(id);
                } else {
                    selectedLeadIds.delete(id);
                }
                updateLeadSelectState();
            });
        });
    }

    function startEdit(id) {
        const lead = leadData.find(l => String(l.id) === String(id));
        if (!lead) return;
        if (formError) formError.classList.add('hidden');
        if (form.elements.id) form.elements.id.value = id;
        if (form.elements.name) form.elements.name.value = lead.name || '';
        if (form.elements.status) form.elements.status.value = lead.status || 'new';
        if (form.elements.source) form.elements.source.value = lead.source || '';
        if (form.elements.property_for) form.elements.property_for.value = lead.property_for || '';
        if (form.elements.payment_option) form.elements.payment_option.value = lead.payment_option || '';
        if (form.elements.interested_property) form.elements.interested_property.value = lead.interested_property || '';
        if (form.elements.area) form.elements.area.value = lead.area || '';
        if (form.elements.email) form.elements.email.value = lead.email || '';
        if (form.elements.phone) form.elements.phone.value = lead.phone || '';
        if (form.elements.last_contact_at) {
            form.elements.last_contact_at.value = lead.last_contact_at ? String(lead.last_contact_at).slice(0, 10) : '';
        }
        if (form.elements.budget) {
            form.elements.budget.value = lead.budget !== null && lead.budget !== undefined ? String(lead.budget) : '';
        }
        if (form.elements.currency) {
            form.elements.currency.value = lead.currency || 'USD';
        }
        const notesField = form.querySelector('textarea[name="notes"]');
        if (notesField) notesField.value = lead.notes || '';
        ui.toggle(formContainer, true);
        document.getElementById('leadFormTitle').innerText = 'Edit Lead';
    }

    

    addBtn?.addEventListener('click', () => {
        form.reset();
        form.elements.id.value = '';
        if (formError) formError.classList.add('hidden');
        document.getElementById('leadFormTitle').innerText = 'New Lead';
        ui.toggle(formContainer, true);
    });

    viewTableBtn?.addEventListener('click', () => {
        viewMode = 'table';
        localStorage.setItem('crm_lead_view', 'table');
        renderLeads();
    });
    viewKanbanBtn?.addEventListener('click', () => {
        viewMode = 'kanban';
        localStorage.setItem('crm_lead_view', 'kanban');
        renderLeads();
    });

    leadHeaderCheckbox?.addEventListener('change', (e) => {
        if (e.target.checked) {
            leadData.forEach(l => selectedLeadIds.add(String(l.id)));
        } else {
            selectedLeadIds.clear();
        }
        renderLeads();
    });
    leadSelectAll?.addEventListener('change', (e) => {
        if (e.target.checked) {
            leadData.forEach(l => selectedLeadIds.add(String(l.id)));
        } else {
            selectedLeadIds.clear();
        }
        renderLeads();
    });

    cancelBtn?.addEventListener('click', () => ui.toggle(formContainer, false));

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        const id = payload.id;
        delete payload.id;
        if (payload.budget) {
            payload.budget = (payload.budget || '').replace(/,/g, '').trim();
        }
        try {
            if (id) {
                await apiClient.updateLead(id, payload);
                ui.showToast('Lead updated', 'success');
            } else {
                await apiClient.createLead(payload);
                ui.showToast('Lead created', 'success');
            }
            ui.toggle(formContainer, false);
            loadLeads();
        } catch (err) {
            ui.showToast(err.message || 'Save failed', 'error');
            if (formError) {
                formError.textContent = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Save failed');
                formError.classList.remove('hidden');
            }
        }
    });

    function loadLeadFilters() {
        try {
            const filters = JSON.parse(localStorage.getItem('crm_lead_filters')) || {};
            if (!filters.archived) filters.archived = 'active';
            return filters;
        } catch (_) {
            return { archived: 'active' };
        }
    }

    function saveLeadFilters(filters) {
        localStorage.setItem('crm_lead_filters', JSON.stringify(filters));
    }

    filterBtn?.addEventListener('click', () => {
        currentFilters = {
            status: statusFilter.value || '',
            source: sourceFilter.value || '',
            archived: archiveFilter?.value || 'active',
        };
        leadPagination.page = 1;
        saveLeadFilters(currentFilters);
        loadLeads();
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

    if (leadSort) {
        const val = leadSort.value || 'created_at:DESC';
        const [sort, direction] = val.split(':');
        leadPagination.sort = sort;
        leadPagination.direction = direction;
        leadSort.addEventListener('change', () => {
            const v = leadSort.value || 'created_at:DESC';
            const [s, d] = v.split(':');
            leadPagination.sort = s;
            leadPagination.direction = d;
            leadPagination.page = 1;
            loadLeads();
        });
    }

    function renderKanban() {
        if (!leadKanban) return;
        const columns = {
            new: leadKanban.querySelector('[data-column="new"]'),
            contacted: leadKanban.querySelector('[data-column="contacted"]'),
            qualified: leadKanban.querySelector('[data-column="qualified"]'),
            not_qualified: leadKanban.querySelector('[data-column="not_qualified"]'),
        };
        const countsEls = {
            new: leadKanban.querySelector('[data-count="new"]'),
            contacted: leadKanban.querySelector('[data-count="contacted"]'),
            qualified: leadKanban.querySelector('[data-count="qualified"]'),
            not_qualified: leadKanban.querySelector('[data-count="not_qualified"]'),
        };
        Object.values(columns).forEach(col => col && (col.innerHTML = ''));
        const statusColors = {
            new: 'bg-blue-50 text-blue-700',
            contacted: 'bg-sky-50 text-sky-700',
            qualified: 'bg-emerald-50 text-emerald-700',
            not_qualified: 'bg-amber-50 text-amber-700',
        };
        const groups = { new: [], contacted: [], qualified: [], not_qualified: [] };
        leadData.forEach(l => {
            const s = l.status || 'new';
            if (groups[s]) groups[s].push(l);
        });
        Object.entries(groups).forEach(([status, list]) => {
            if (countsEls[status]) countsEls[status].textContent = list.length;
            if (!columns[status]) return;
            columns[status].innerHTML = list.map(l => `
                    <div class="border border-border rounded-card p-3 bg-white shadow-sm" draggable="true" data-id="${l.id}">
                        <div class="font-semibold text-sm">${escapeHtml(l.name)}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(l.email || 'No email')}</div>
                        <div class="mt-2 inline-flex px-2 py-1 rounded text-[11px] ${statusColors[status] || 'bg-gray-100 text-gray-700'}">${formatLeadStatus(l.status || status)}</div>
                    </div>
            `).join('') || '<div class="text-xs text-gray-500">No leads</div>';
        });

        leadKanban.querySelectorAll('[draggable="true"]').forEach(card => {
            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', card.dataset.id);
                const from = card.closest('[data-column]')?.dataset.column || '';
                e.dataTransfer.setData('fromStatus', from);
            });
        });
        const dropTargets = leadKanban.querySelectorAll('[data-column], [data-status]');
        dropTargets.forEach(col => {
            const targetStatus = col.dataset.column || col.dataset.status;
            col.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                col.classList.add('ring', 'ring-accent/40');
            });
            col.addEventListener('dragleave', () => col.classList.remove('ring', 'ring-accent/40'));
            col.addEventListener('drop', async (e) => {
                e.preventDefault();
                col.classList.remove('ring', 'ring-accent/40');
                const id = e.dataTransfer.getData('text/plain');
                const newStatus = targetStatus;
                if (!id || !newStatus) return;
                const fromStatus = e.dataTransfer.getData('fromStatus') || '';
                if (fromStatus === newStatus) return;
                await updateLeadStatus(id, newStatus);
            });
        });
    }

    leadBulkApply?.addEventListener('click', async () => {
        const action = leadBulkStatus?.value || '';
        if (!action) {
            ui.showToast('Select an action', 'error');
            return;
        }
        if (!selectedLeadIds.size) {
            ui.showToast('Select at least one lead', 'error');
            return;
        }
        try {
            const ids = Array.from(selectedLeadIds);
            if (action === 'archive') {
                await apiClient.bulkArchiveLeads(ids);
                ui.showToast('Leads archived', 'success');
            } else if (action === 'restore') {
                await apiClient.bulkRestoreLeads(ids);
                ui.showToast('Leads restored', 'success');
            } else {
                await apiClient.bulkUpdateLeads(ids, action);
                ui.showToast('Leads updated', 'success');
            }
            selectedLeadIds.clear();
            renderLeads();
            loadLeads();
        } catch (err) {
            ui.showToast(err.message || 'Bulk update failed', 'error');
        }
    });

    async function updateLeadStatus(id, newStatus) {
        const lead = leadData.find(l => String(l.id) === String(id));
        if (!lead) return;
        if (lead.archived_at) {
            ui.showToast('Restore the lead to move it', 'error');
            return;
        }
        try {
            await apiClient.updateLead(id, { ...lead, status: newStatus });
            ui.showToast('Lead moved', 'success');
            loadLeads();
        } catch (err) {
            ui.showToast(err?.message || 'Failed to update lead', 'error');
        }
    }

    loadLeads();
}

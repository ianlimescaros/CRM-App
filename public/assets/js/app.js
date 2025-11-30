document.addEventListener('DOMContentLoaded', () => {
    const pageEl = document.querySelector('[data-page]');
    const page = pageEl ? pageEl.dataset.page : null;
    const token = apiClient.getToken();

    // Simple auth gate on frontend routing
    if (!token && page !== 'login') {
        window.location = '/index.php?page=login';
        return;
    }
    if (token && page === 'login') {
        window.location = '/index.php?page=dashboard';
        return;
    }

    const logoutBtn = document.getElementById('logoutBtn');
    const logoutBtnTop = document.getElementById('logoutBtnTop');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const userEmailText = document.getElementById('userEmailText');

    function toggleSidebar(show) {
        if (!sidebar || !mobileOverlay) return;
        const shouldShow = typeof show === 'boolean' ? show : sidebar.classList.contains('hidden');
        sidebar.classList.toggle('hidden', !shouldShow);
        mobileOverlay.classList.toggle('hidden', !shouldShow);
        if (shouldShow) {
            sidebar.classList.add('fixed', 'z-30', 'top-0', 'left-0', 'h-full');
        } else {
            sidebar.classList.remove('fixed', 'z-30', 'top-0', 'left-0', 'h-full');
        }
    }

    mobileMenuBtn?.addEventListener('click', () => toggleSidebar(true));
    mobileOverlay?.addEventListener('click', () => toggleSidebar(false));
    document.querySelectorAll('a[data-nav]').forEach((link) => {
        link.addEventListener('click', () => toggleSidebar(false));
    });

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            const ok = await ui.confirmModal('Do you want to log out?');
            if (!ok) return;
            try {
                await apiClient.logout();
            } catch (_) {
                // ignore logout failures
            } finally {
                apiClient.setToken(null);
                localStorage.removeItem('crm_user_email');
                ui.showToast('Logged out', 'success');
                window.location = '/index.php?page=login';
            }
        });
    }
    if (logoutBtnTop) {
        logoutBtnTop.addEventListener('click', () => logoutBtn?.click());
    }

    // Display stored user email if available
    const storedEmail = localStorage.getItem('crm_user_email');
    if (storedEmail && userEmailText) {
        userEmailText.textContent = storedEmail;
        userEmailText.classList.remove('hidden');
    }

    switch (page) {
        case 'login':
            initLogin();
            break;
        case 'dashboard':
            initDashboard();
            break;
        case 'leads':
            initLeads();
            break;
        case 'contacts':
            initContacts();
            break;
        case 'deals':
            initDeals();
            break;
        case 'tasks':
            initTasks();
            break;
        case 'ai-assistant':
            initAiAssistant();
            break;
        case 'reports':
            initReports();
            break;
        case 'client-profile':
            initClientProfile();
            break;
        default:
            break;
    }
});

function initLogin() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const showRegister = document.getElementById('showRegister');

    if (showRegister) {
        showRegister.addEventListener('click', () => {
            ui.toggle(registerForm, true);
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const email = formData.get('email');
            const password = formData.get('password');
            const errorBox = document.getElementById('loginError');
            if (errorBox) errorBox.classList.add('hidden');
            try {
                const res = await apiClient.login(email, password);
                apiClient.setToken(res.token);
                if (res.user?.email) {
                    localStorage.setItem('crm_user_email', res.user.email);
                }
                ui.showToast('Logged in', 'success');
                window.location = '/index.php?page=dashboard';
            } catch (err) {
                ui.showToast(err.message || 'Login failed', 'error');
                if (errorBox) {
                    errorBox.textContent = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Login failed');
                    errorBox.classList.remove('hidden');
                }
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            const payload = Object.fromEntries(formData.entries());
            const errorBox = document.getElementById('registerError');
            if (errorBox) errorBox.classList.add('hidden');
            try {
                const res = await apiClient.register(payload);
                apiClient.setToken(res.token);
                if (res.user?.email) {
                    localStorage.setItem('crm_user_email', res.user.email);
                }
                ui.showToast('Account created', 'success');
                window.location = '/index.php?page=dashboard';
            } catch (err) {
                ui.showToast(err.message || 'Register failed', 'error');
                if (errorBox) {
                    errorBox.textContent = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Register failed');
                    errorBox.classList.remove('hidden');
                }
            }
        });
    }
}

async function initDashboard() {
    try {
        const [leadRes, dealRes, taskRes] = await Promise.all([
            apiClient.listLeads(),
            apiClient.listDeals(),
            apiClient.listTasks(),
        ]);
        document.getElementById('statLeads').innerText = leadRes.leads.length;
        document.getElementById('statDeals').innerText = dealRes.deals.length;
        document.getElementById('statTasks').innerText = taskRes.tasks.length;

        // simple WoW delta: current 7 days vs previous 7 days
        setDelta('statLeadsDelta', leadRes.leads);
        setDelta('statDealsDelta', dealRes.deals);
        setDelta('statTasksDelta', taskRes.tasks);

        const recentLeads = leadRes.leads.slice(0, 5);
        document.getElementById('recentLeads').innerHTML = recentLeads.length
            ? recentLeads.map(l => `
                <div class="flex justify-between items-center border border-border rounded px-3 py-2 bg-white">
                    <div>
                        <div class="font-semibold">${escapeHtml(l.name)}</div>
                        <div class="text-xs text-gray-500">${l.source || '—'}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-50 text-blue-700">${l.status}</span>
                </div>
            `).join('')
            : '<div class="text-sm text-gray-500">No leads yet.</div>';

        const upcoming = taskRes.tasks
            .filter(t => t.status === 'pending')
            .sort((a, b) => new Date(a.due_date || 0) - new Date(b.due_date || 0))
            .slice(0, 5);
        document.getElementById('upcomingTasks').innerHTML = upcoming.length
            ? upcoming.map(t => `
                <div class="flex justify-between items-center border border-border rounded px-3 py-2 bg-white">
                    <div>
                        <div class="font-semibold">${escapeHtml(t.title)}</div>
                        <div class="text-xs text-gray-500">${t.due_date ? relativeDate(t.due_date) : 'No due date'}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-amber-50 text-amber-700">${t.status}</span>
                </div>
            `).join('')
            : '<div class="text-sm text-gray-500">No upcoming tasks.</div>';

        // Simple bar chart for pipeline (by lead status)
        const statusCounts = countByStatus(leadRes.leads || [], 'status');
        const barCanvas = document.getElementById('pipelineChart');
        if (barCanvas && window.Chart) {
            renderBarChart(barCanvas, Object.keys(statusCounts), Object.values(statusCounts), 'Pipeline by status');
        } else if (barCanvas) {
            barCanvas.innerHTML = renderBars(statusCounts);
        }

        // Simple line/bar spark for leads over time
        const lineCanvas = document.getElementById('leadsLineChart');
        const series = aggregateByDate(leadRes.leads || [], 'created_at', 7);
        if (lineCanvas && window.Chart) {
            renderLineChart(lineCanvas, series.map(s => s.date.slice(5)), series.map(s => s.value), 'Leads over time');
        } else if (lineCanvas) {
            lineCanvas.innerHTML = renderSparkline(series);
        }
    } catch (err) {
        ui.showToast('Failed to load dashboard', 'error');
    }
}

function setDelta(elId, items) {
    const el = document.getElementById(elId);
    if (!el) return;
    const now = new Date();
    const currentStart = new Date(now);
    currentStart.setDate(now.getDate() - 7);
    const prevStart = new Date(now);
    prevStart.setDate(now.getDate() - 14);

    const current = items.filter(i => isInRange(i.created_at, currentStart, now)).length;
    const prev = items.filter(i => isInRange(i.created_at, prevStart, currentStart)).length;

    let delta = 0;
    if (prev === 0) {
        delta = current > 0 ? 100 : 0;
    } else {
        delta = Math.round(((current - prev) / prev) * 100);
    }
    const sign = delta > 0 ? '+' : '';
    el.textContent = `${sign}${delta}%`;
    el.classList.remove('text-blue-700', 'text-emerald-700', 'text-amber-700', 'text-red-700', 'bg-blue-50', 'bg-emerald-50', 'bg-amber-50', 'bg-red-50');
    let color = 'blue';
    if (elId.includes('Deals')) color = 'emerald';
    if (elId.includes('Tasks')) color = 'amber';
    if (delta < 0) color = 'red';
    el.classList.add(`text-${color}-700`, `bg-${color}-50`);
}

function isInRange(dateStr, start, end) {
    if (!dateStr) return false;
    const d = new Date(dateStr);
    return d >= start && d <= end;
}

function initLeads() {
    const tableBody = document.getElementById('leadsTableBody');
    const addBtn = document.getElementById('leadAddBtn');
    const formContainer = document.getElementById('leadFormContainer');
    const form = document.getElementById('leadForm');
    const cancelBtn = document.getElementById('leadFormCancel');
    const statusFilter = document.getElementById('leadStatusFilter');
    const sourceFilter = document.getElementById('leadSourceFilter');
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

    let currentFilters = loadLeadFilters();
    let leadData = [];
    let viewMode = localStorage.getItem('crm_lead_view') || 'table';
    let selectedLeadIds = new Set();

    // restore filters into inputs
    if (statusFilter && currentFilters.status) statusFilter.value = currentFilters.status;
    if (sourceFilter && currentFilters.source) sourceFilter.value = currentFilters.source;

    async function loadLeads() {
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
        } catch (err) {
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
        if (!leadData.length) {
            tableBody.innerHTML = `<tr><td class="px-3 py-4 text-center text-gray-500" colspan="8">No leads yet. Try adjusting filters or add a lead.</td></tr>`;
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

    function renderKanban() {
        if (!leadKanban) return;
        const columns = {
            new: leadKanban.querySelector('[data-column="new"]'),
            contacted: leadKanban.querySelector('[data-column="contacted"]'),
            qualified: leadKanban.querySelector('[data-column="qualified"]'),
            won: leadKanban.querySelector('[data-column="won"]'),
            lost: leadKanban.querySelector('[data-column="lost"]'),
        };
        const countsEls = {
            new: leadKanban.querySelector('[data-count="new"]'),
            contacted: leadKanban.querySelector('[data-count="contacted"]'),
            qualified: leadKanban.querySelector('[data-count="qualified"]'),
            won: leadKanban.querySelector('[data-count="won"]'),
            lost: leadKanban.querySelector('[data-count="lost"]'),
        };
        Object.values(columns).forEach(col => col && (col.innerHTML = ''));
        const statusColors = {
            new: 'bg-blue-50 text-blue-700',
            contacted: 'bg-sky-50 text-sky-700',
            qualified: 'bg-emerald-50 text-emerald-700',
            won: 'bg-green-50 text-green-700',
            lost: 'bg-red-50 text-red-700',
        };
        const groups = { new: [], contacted: [], qualified: [], won: [], lost: [] };
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
                    <div class="text-xs text-gray-500">${l.source || '—'}</div>
                    <div class="mt-2 inline-flex px-2 py-1 rounded text-[11px] ${statusColors[status] || 'bg-gray-100 text-gray-700'}">${status}</div>
                </div>
            `).join('') || '<div class="text-xs text-gray-500">No leads</div>';
        });

        leadKanban.querySelectorAll('[draggable="true"]').forEach(card => {
            card.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', card.dataset.id);
            });
        });
        leadKanban.querySelectorAll('[data-column]').forEach(col => {
            col.addEventListener('dragover', (e) => e.preventDefault());
            col.addEventListener('drop', async (e) => {
                e.preventDefault();
                const id = e.dataTransfer.getData('text/plain');
                const newStatus = col.dataset.column;
                await updateLeadStatus(id, newStatus);
            });
        });
    }

    function leadRow(lead) {
        return `
            <tr data-id="${lead.id}" data-owner="${lead.owner_id || ''}" data-last-contact="${lead.last_contact_at || ''}" class="border-b last:border-b-0 even:bg-gray-50 hover:bg-gray-100">
                <td class="px-3 py-2"><input type="checkbox" class="lead-select h-4 w-4 border border-border rounded" data-id="${lead.id}" ${selectedLeadIds.has(String(lead.id)) ? 'checked' : ''}></td>
                <td class="px-3 py-2">${escapeHtml(lead.name)}</td>
                <td class="px-3 py-2">
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-50 text-blue-700">${lead.status}</span>
                </td>
                <td class="px-3 py-2">${lead.source || ''}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${lead.owner_id || '—'}</td>
                <td class="px-3 py-2 text-xs text-gray-700">${lead.last_contact_at || '—'}</td>
                <td class="px-3 py-2">${lead.budget || ''}</td>
                <td class="px-3 py-2 space-x-2">
                    <button class="text-blue-600 lead-edit">Edit</button>
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
        const row = tableBody.querySelector(`tr[data-id="${id}"]`);
        if (!row) return;
        if (formError) formError.classList.add('hidden');
        form.elements.id.value = id;
        form.elements.name.value = row.children[1].innerText;
        form.elements.status.value = row.children[2].innerText;
        form.elements.source.value = row.children[3].innerText;
        form.elements.owner_id.value = row.dataset.owner || '';
        form.elements.last_contact_at.value = row.dataset.lastContact || '';
        form.elements.budget.value = row.children[6].innerText;
        form.querySelector('textarea[name="notes"]').value = '';
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

    leadBulkApply?.addEventListener('click', async () => {
        const status = leadBulkStatus?.value || '';
        if (!status) {
            ui.showToast('Select a status', 'error');
            return;
        }
        if (!selectedLeadIds.size) {
            ui.showToast('Select at least one lead', 'error');
            return;
        }
        try {
            await apiClient.bulkUpdateLeads(Array.from(selectedLeadIds), status);
            ui.showToast('Leads updated', 'success');
            selectedLeadIds.clear();
            renderLeads();
            loadLeads();
        } catch (err) {
            ui.showToast(err.message || 'Bulk update failed', 'error');
        }
    });

    cancelBtn?.addEventListener('click', () => ui.toggle(formContainer, false));

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());
        const id = payload.id;
        delete payload.id;
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

    filterBtn?.addEventListener('click', () => {
        currentFilters = {
            status: statusFilter.value || '',
            source: sourceFilter.value || '',
        };
        saveLeadFilters(currentFilters);
        leadPagination.page = 1;
        loadLeads();
    });

    leadSort?.addEventListener('change', () => {
        const val = leadSort.value || 'created_at:DESC';
        const [sort, direction] = val.split(':');
        leadPagination.sort = sort;
        leadPagination.direction = direction;
        leadPagination.page = 1;
        loadLeads();
    });

    async function updateLeadStatus(id, newStatus) {
        const lead = leadData.find(l => String(l.id) === String(id));
        if (!lead) return;
        try {
            await apiClient.updateLead(id, { ...lead, status: newStatus });
            ui.showToast('Lead moved', 'success');
            loadLeads();
        } catch (err) {
            ui.showToast('Failed to update lead', 'error');
        }
    }

    function saveLeadFilters(filters) {
        localStorage.setItem('crm_lead_filters', JSON.stringify(filters));
    }
    function loadLeadFilters() {
        try {
            return JSON.parse(localStorage.getItem('crm_lead_filters')) || {};
        } catch (_) {
            return {};
        }
    }

    loadLeads();
}

function initReports() {
    const statusChart = document.getElementById('reportStatusChart');
    const stageChart = document.getElementById('reportStageChart');
    const leadsLine = document.getElementById('reportLeadsLine');
    const taskChart = document.getElementById('reportTaskChart');

    Promise.all([apiClient.listLeads(), apiClient.listDeals(), apiClient.listTasks()])
        .then(([leadRes, dealRes, taskRes]) => {
            if (statusChart) {
                const counts = countByStatus(leadRes.leads || [], 'status');
                if (window.Chart) {
                    renderBarChart(statusChart, Object.keys(counts), Object.values(counts), 'Lead Status', ['#2563EB', '#10B981', '#F59E0B', '#EF4444', '#7C3AED']);
                } else {
                    statusChart.innerHTML = renderBars(counts);
                }
            }
            if (stageChart) {
                const counts = countByStatus(dealRes.deals || [], 'stage');
                if (window.Chart) {
                    renderBarChart(stageChart, Object.keys(counts), Object.values(counts), 'Deal Stages', ['#2563EB', '#0EA5E9', '#F59E0B', '#16A34A', '#DC2626']);
                } else {
                    stageChart.innerHTML = renderBars(counts);
                }
            }
            if (taskChart) {
                const counts = countByStatus(taskRes.tasks || [], 'status');
                if (window.Chart) {
                    renderBarChart(taskChart, Object.keys(counts), Object.values(counts), 'Tasks Status', ['#F59E0B', '#10B981']);
                } else {
                    taskChart.innerHTML = renderBars(counts);
                }
            }
            if (leadsLine) {
                const series = aggregateByDate(leadRes.leads || [], 'created_at', 14);
                if (window.Chart) {
                    renderLineChart(leadsLine, series.map(s => s.date.slice(5)), series.map(s => s.value), 'Leads Created', '#2563EB');
                } else {
                    leadsLine.innerHTML = renderSparkline(series);
                }
            }
        })
        .catch(() => ui.showToast('Failed to load reports', 'error'));
}

function initContacts() {
    const tableBody = document.getElementById('contactsTableBody');
    const addBtn = document.getElementById('contactAddBtn');
    const formContainer = document.getElementById('contactFormContainer');
    const form = document.getElementById('contactForm');
    const cancelBtn = document.getElementById('contactFormCancel');
    const searchInput = document.getElementById('contactSearch');
    const formError = document.getElementById('contactFormError');

    let contactSearch = loadContactSearch();
    if (searchInput && contactSearch) searchInput.value = contactSearch;
    const contactPagination = { page: 1, per_page: 20, total: 0, sort: 'created_at', direction: 'DESC' };

    async function loadContacts() {
        try {
            const res = await apiClient.listContacts({
                page: contactPagination.page,
                per_page: contactPagination.per_page,
                sort: contactPagination.sort,
                direction: contactPagination.direction,
            });
            let list = res.contacts || [];
            if (contactSearch) {
                const q = contactSearch.toLowerCase();
                list = list.filter(c =>
                    (c.full_name || '').toLowerCase().includes(q) ||
                    (c.email || '').toLowerCase().includes(q)
                );
            }
            if (res.meta) {
                contactPagination.total = res.meta.total || 0;
                contactPagination.per_page = res.meta.per_page || contactPagination.per_page;
                contactPagination.page = res.meta.page || contactPagination.page;
            }
            if (!list.length) {
                tableBody.innerHTML = `<tr><td class="px-3 py-4 text-center text-gray-500" colspan="4">No contacts found.</td></tr>`;
                return;
            }
            tableBody.innerHTML = list.map(contactRow).join('');
            attachActions();
            renderContactPagination();
        } catch (err) {
            ui.showToast('Failed to load contacts', 'error');
        }
    }

    function contactRow(contact) {
        return `
            <tr data-id="${contact.id}" class="border-b last:border-b-0 even:bg-gray-50 hover:bg-gray-100">
                <td class="px-3 py-2">${escapeHtml(contact.full_name)}</td>
                <td class="px-3 py-2">${contact.email || ''}</td>
                <td class="px-3 py-2">${contact.phone || ''}</td>
                <td class="px-3 py-2 space-x-2">
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
                form.elements.company.value = '';
                form.elements.position.value = '';
                document.getElementById('contactFormTitle').innerText = 'Edit Contact';
                ui.toggle(formContainer, true);
            });
        });
        tableBody.querySelectorAll('.contact-delete').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                const ok = await ui.confirmModal('Delete this contact?');
                if (!ok) return;
                try {
                    await apiClient.deleteContact(id);
                    ui.showToast('Contact deleted', 'success');
                    loadContacts();
                } catch (err) {
                    ui.showToast('Delete failed', 'error');
                }
            });
        });
    }

    addBtn?.addEventListener('click', () => {
        form.reset();
        form.elements.id.value = '';
        if (formError) formError.classList.add('hidden');
        document.getElementById('contactFormTitle').innerText = 'New Contact';
        ui.toggle(formContainer, true);
    });

    cancelBtn?.addEventListener('click', () => ui.toggle(formContainer, false));

    searchInput?.addEventListener('input', (e) => {
        contactSearch = e.target.value || '';
        saveContactSearch(contactSearch);
        loadContacts();
    });

    function saveContactSearch(value) {
        localStorage.setItem('crm_contact_search', value);
    }
    function loadContactSearch() {
        return localStorage.getItem('crm_contact_search') || '';
    }

    function renderContactPagination() {
        const container = document.getElementById('contactPagination');
        if (!container) return;
        const totalPages = Math.max(1, Math.ceil((contactPagination.total || 0) / contactPagination.per_page));
        const page = contactPagination.page;
        container.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-gray-700 mt-2">
                <button id="contactPagePrev" class="px-3 py-1 border border-border rounded ${page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Prev</button>
                <span>Page ${page} of ${totalPages}</span>
                <button id="contactPageNext" class="px-3 py-1 border border-border rounded ${page >= totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Next</button>
                <span class="text-xs text-gray-500">${contactPagination.total || 0} results</span>
            </div>
        `;
        const prev = document.getElementById('contactPagePrev');
        const next = document.getElementById('contactPageNext');
        if (prev && page > 1) {
            prev.addEventListener('click', () => {
                contactPagination.page = Math.max(1, page - 1);
                loadContacts();
            });
        }
        if (next && page < totalPages) {
            next.addEventListener('click', () => {
                contactPagination.page = Math.min(totalPages, page + 1);
                loadContacts();
            });
        }
    }

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(form).entries());
        const id = payload.id;
        delete payload.id;
        try {
            if (id) {
                await apiClient.updateContact(id, payload);
                ui.showToast('Contact updated', 'success');
            } else {
                await apiClient.createContact(payload);
                ui.showToast('Contact created', 'success');
            }
            ui.toggle(formContainer, false);
            loadContacts();
        } catch (err) {
            ui.showToast(err.message || 'Save failed', 'error');
            if (formError) {
                formError.textContent = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Save failed');
                formError.classList.remove('hidden');
            }
        }
    });

    loadContacts();
}

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
    const dealContactSelect = document.getElementById('dealContactSelect');

    let currentFilters = {};
    let dealData = [];
    let leadsCache = [];
    let contactsCache = [];
    const dealPagination = { page: 1, per_page: 20, sort: 'created_at', direction: 'DESC', total: 0 };

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
            prospecting: 'bg-blue-50 text-blue-700',
            proposal: 'bg-indigo-50 text-indigo-700',
            negotiation: 'bg-amber-50 text-amber-700',
            closed_won: 'bg-green-50 text-green-700',
            closed_lost: 'bg-red-50 text-red-700',
        };
        return `
            <tr data-id="${deal.id}" data-lead-id="${deal.lead_id || ''}" data-contact-id="${deal.contact_id || ''}" class="border-b last:border-b-0 even:bg-gray-50 hover:bg-gray-100">
                <td class="px-3 py-2">${escapeHtml(deal.title)}</td>
                <td class="px-3 py-2">
                    <span class="inline-flex px-2 py-1 rounded text-xs ${stageColors[deal.stage] || 'bg-gray-100 text-gray-700'}">${deal.stage}</span>
                </td>
                <td class="px-3 py-2">${deal.amount || ''}</td>
                <td class="px-3 py-2">${deal.close_date || ''}</td>
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
                form.elements.stage.value = row[1].innerText;
                form.elements.amount.value = row[2].innerText;
                form.elements.close_date.value = row[3].innerText;
                const leadId = e.target.closest('tr').dataset.leadId || '';
                const contactId = e.target.closest('tr').dataset.contactId || '';
                if (dealLeadSelect) dealLeadSelect.value = leadId;
                if (dealContactSelect) dealContactSelect.value = contactId;
                document.getElementById('dealFormTitle').innerText = 'Edit Deal';
                ui.toggle(formContainer, true);
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
    }

    addBtn?.addEventListener('click', () => {
        form.reset();
        form.elements.id.value = '';
        if (formError) formError.classList.add('hidden');
        if (dealLeadSelect) dealLeadSelect.value = '';
        if (dealContactSelect) dealContactSelect.value = '';
        document.getElementById('dealFormTitle').innerText = 'New Deal';
        ui.toggle(formContainer, true);
    });

    cancelBtn?.addEventListener('click', () => ui.toggle(formContainer, false));

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(form).entries());
        if (!payload.lead_id) payload.lead_id = null;
        if (!payload.contact_id) payload.contact_id = null;
        const id = payload.id;
        delete payload.id;
        try {
            if (id) {
                await apiClient.updateDeal(id, payload);
                ui.showToast('Deal updated', 'success');
            } else {
                await apiClient.createDeal(payload);
                ui.showToast('Deal created', 'success');
            }
            ui.toggle(formContainer, false);
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
        if (dealContactSelect) {
            dealContactSelect.innerHTML = `<option value="">None</option>` + contactsCache.map(c => `
                <option value="${c.id}">${escapeHtml(c.full_name || '')} (#${c.id})</option>
            `).join('');
        }
    }

    Promise.all([apiClient.listLeads(), apiClient.listContacts()])
        .then(([leadsRes, contactsRes]) => {
            leadsCache = leadsRes.leads || [];
            contactsCache = contactsRes.contacts || [];
            populateDealSelects();
        })
        .catch(() => {
            leadsCache = [];
            contactsCache = [];
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

    loadDeals();
}

function initTasks() {
    const tableBody = document.getElementById('tasksTableBody');
    const addBtn = document.getElementById('taskAddBtn');
    const formContainer = document.getElementById('taskFormContainer');
    const form = document.getElementById('taskForm');
    const cancelBtn = document.getElementById('taskFormCancel');
    const statusFilter = document.getElementById('taskStatusFilter');
    const dueFilter = document.getElementById('taskDueFilter');
    const filterBtn = document.getElementById('taskFilterBtn');
    const formError = document.getElementById('taskFormError');
    const taskLeadSelect = document.getElementById('taskLeadSelect');
    const taskContactSelect = document.getElementById('taskContactSelect');
    const taskViewList = document.getElementById('taskViewList');
    const taskViewCalendar = document.getElementById('taskViewCalendar');
    const taskListWrap = document.getElementById('taskListWrap');
    const taskCalendarWrap = document.getElementById('taskCalendarWrap');
    const taskCalendar = document.getElementById('taskCalendar');
    const taskCalendarMonth = document.getElementById('taskCalendarMonth');
    const taskPagination = { page: 1, per_page: 20, total: 0, sort: 'due_date', direction: 'ASC' };

    let currentFilters = {};
    let taskData = [];
    let leadsCache = [];
    let contactsCache = [];
    let taskViewMode = localStorage.getItem('crm_task_view') || 'list';

    async function loadTasks() {
        try {
            const res = await apiClient.listTasks({
                ...currentFilters,
                page: taskPagination.page,
                per_page: taskPagination.per_page,
                sort: taskPagination.sort,
                direction: taskPagination.direction,
            });
            taskData = res.tasks || [];
            if (res.meta) {
                taskPagination.total = res.meta.total || 0;
                taskPagination.page = res.meta.page || taskPagination.page;
                taskPagination.per_page = res.meta.per_page || taskPagination.per_page;
            }
            renderTasks();
            attachActions();
        } catch (err) {
            ui.showToast('Failed to load tasks', 'error');
        }
    }

    function renderTasks() {
        if (taskViewMode === 'calendar') {
            ui.toggle(taskListWrap, false);
            ui.toggle(taskCalendarWrap, true);
        renderTaskCalendar();
        setTaskViewButtons();
        return;
    }
        ui.toggle(taskListWrap, true);
        ui.toggle(taskCalendarWrap, false);
        if (!taskData.length) {
            tableBody.innerHTML = `<tr><td class="px-3 py-4 text-center text-gray-500" colspan="4">No tasks found.</td></tr>`;
            return;
        }
        tableBody.innerHTML = taskData.map(taskRow).join('');
        setTaskViewButtons();
        renderTaskPagination();
    }

    function taskRow(task) {
        const statusColors = {
            pending: 'bg-amber-50 text-amber-700',
            done: 'bg-emerald-50 text-emerald-700',
        };
        return `
            <tr data-id="${task.id}" data-lead-id="${task.lead_id || ''}" data-contact-id="${task.contact_id || ''}" class="border-b last:border-b-0 even:bg-gray-50 hover:bg-gray-100">
                <td class="px-3 py-2">${escapeHtml(task.title)}</td>
                <td class="px-3 py-2">${task.due_date || ''}</td>
                <td class="px-3 py-2">
                    <span class="inline-flex px-2 py-1 rounded text-xs ${statusColors[task.status] || 'bg-gray-100 text-gray-700'}">${task.status}</span>
                </td>
                <td class="px-3 py-2 space-x-2">
                    <button class="text-blue-600 task-edit">Edit</button>
                    <button class="text-red-600 task-delete">Delete</button>
                </td>
            </tr>
        `;
    }

    function attachActions() {
        tableBody.querySelectorAll('.task-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = e.target.closest('tr').dataset.id;
                const row = e.target.closest('tr').children;
                if (formError) formError.classList.add('hidden');
                form.elements.id.value = id;
                form.elements.title.value = row[0].innerText;
                form.elements.due_date.value = row[1].innerText;
                form.elements.status.value = row[2].innerText;
                form.elements.description.value = '';
                const leadId = e.target.closest('tr').dataset.leadId || '';
                const contactId = e.target.closest('tr').dataset.contactId || '';
                if (taskLeadSelect) taskLeadSelect.value = leadId;
                if (taskContactSelect) taskContactSelect.value = contactId;
                document.getElementById('taskFormTitle').innerText = 'Edit Task';
                ui.toggle(formContainer, true);
            });
        });
        tableBody.querySelectorAll('.task-delete').forEach(btn => {
            btn.addEventListener('click', async (e) => {
                const id = e.target.closest('tr').dataset.id;
                const ok = await ui.confirmModal('Delete this task?');
                if (!ok) return;
                try {
                    await apiClient.deleteTask(id);
                    ui.showToast('Task deleted', 'success');
                    loadTasks();
                } catch (err) {
                    ui.showToast('Delete failed', 'error');
                }
            });
        });
    }

        addBtn?.addEventListener('click', () => {
        form.reset();
        form.elements.id.value = '';
        if (formError) formError.classList.add('hidden');
        if (taskLeadSelect) taskLeadSelect.value = '';
        if (taskContactSelect) taskContactSelect.value = '';
        document.getElementById('taskFormTitle').innerText = 'New Task';
        ui.toggle(formContainer, true);
    });

    taskViewList?.addEventListener('click', () => {
        taskViewMode = 'list';
        localStorage.setItem('crm_task_view', 'list');
        renderTasks();
    });
    taskViewCalendar?.addEventListener('click', () => {
        taskViewMode = 'calendar';
        localStorage.setItem('crm_task_view', 'calendar');
        renderTasks();
    });

    cancelBtn?.addEventListener('click', () => ui.toggle(formContainer, false));

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(form).entries());
        if (!payload.lead_id) payload.lead_id = null;
        if (!payload.contact_id) payload.contact_id = null;
        const id = payload.id;
        delete payload.id;
        try {
            if (id) {
                await apiClient.updateTask(id, payload);
                ui.showToast('Task updated', 'success');
            } else {
                await apiClient.createTask(payload);
                ui.showToast('Task created', 'success');
            }
            ui.toggle(formContainer, false);
            loadTasks();
        } catch (err) {
            ui.showToast(err.message || 'Save failed', 'error');
            if (formError) {
                formError.textContent = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Save failed');
                formError.classList.remove('hidden');
            }
        }
    });

    function populateTaskSelects() {
        if (taskLeadSelect) {
            taskLeadSelect.innerHTML = `<option value="">None</option>` + leadsCache.map(l => `
                <option value="${l.id}">${escapeHtml(l.name || '')} (#${l.id})</option>
            `).join('');
        }
        if (taskContactSelect) {
            taskContactSelect.innerHTML = `<option value="">None</option>` + contactsCache.map(c => `
                <option value="${c.id}">${escapeHtml(c.full_name || '')} (#${c.id})</option>
            `).join('');
        }
    }

    Promise.all([apiClient.listLeads(), apiClient.listContacts()])
        .then(([leadsRes, contactsRes]) => {
            leadsCache = leadsRes.leads || [];
            contactsCache = contactsRes.contacts || [];
            populateTaskSelects();
        })
        .catch(() => {
            leadsCache = [];
            contactsCache = [];
        });

    filterBtn?.addEventListener('click', () => {
        currentFilters = {
            status: statusFilter.value || '',
            due_date: dueFilter.value || '',
        };
        taskPagination.page = 1;
        loadTasks();
    });

    loadTasks();

    function setTaskViewButtons() {
        if (taskViewList && taskViewCalendar) {
            if (taskViewMode === 'list') {
                taskViewList.classList.add('bg-accent', 'text-white');
                taskViewCalendar.classList.remove('bg-accent', 'text-white');
                taskViewCalendar.classList.add('text-gray-700');
            } else {
                taskViewCalendar.classList.add('bg-accent', 'text-white');
                taskViewList.classList.remove('bg-accent', 'text-white');
                taskViewList.classList.add('text-gray-700');
            }
        }
    }

    function renderTaskCalendar() {
        if (!taskCalendar) return;
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const firstDay = new Date(year, month, 1);
        const startDay = firstDay.getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        if (taskCalendarMonth) taskCalendarMonth.textContent = now.toLocaleString('default', { month: 'long', year: 'numeric' });

        const map = {};
        taskData.forEach(t => {
            if (!t.due_date) return;
            const d = new Date(t.due_date);
            if (d.getMonth() !== month || d.getFullYear() !== year) return;
            const day = d.getDate();
            if (!map[day]) map[day] = [];
            map[day].push(t);
        });

        const cells = [];
        for (let i = 0; i < startDay; i++) {
            cells.push('<div class="h-24 border border-border rounded-card bg-gray-50"></div>');
        }
        for (let day = 1; day <= daysInMonth; day++) {
            const items = map[day] || [];
            cells.push(`
                <div class="h-24 border border-border rounded-card p-2 text-xs flex flex-col gap-1">
                    <div class="font-semibold text-gray-700">${day}</div>
                    <div class="space-y-1 overflow-y-auto">
                        ${items.map(t => `<div class="px-2 py-1 rounded bg-amber-50 text-amber-700">${escapeHtml(t.title)}</div>`).join('')}
                    </div>
                </div>
            `);
        }
        taskCalendar.innerHTML = cells.join('');
    }

    function renderTaskPagination() {
        const container = document.getElementById('taskPagination');
        if (!container) return;
        const totalPages = Math.max(1, Math.ceil((taskPagination.total || 0) / taskPagination.per_page));
        const page = taskPagination.page;
        container.innerHTML = `
            <div class="flex items-center gap-2 text-sm text-gray-700 mt-2">
                <button id="taskPagePrev" class="px-3 py-1 border border-border rounded ${page <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Prev</button>
                <span>Page ${page} of ${totalPages}</span>
                <button id="taskPageNext" class="px-3 py-1 border border-border rounded ${page >= totalPages ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}">Next</button>
                <span class="text-xs text-gray-500">${taskPagination.total || 0} results</span>
            </div>
        `;
        const prev = document.getElementById('taskPagePrev');
        const next = document.getElementById('taskPageNext');
        if (prev && page > 1) {
            prev.addEventListener('click', () => {
                taskPagination.page = Math.max(1, page - 1);
                loadTasks();
            });
        }
        if (next && page < totalPages) {
            next.addEventListener('click', () => {
                taskPagination.page = Math.min(totalPages, page + 1);
                loadTasks();
            });
        }
    }
}

function initAiAssistant() {
    const summarizeForm = document.getElementById('aiSummarizeForm');
    const followupForm = document.getElementById('aiFollowupForm');
    const summaryResult = document.getElementById('aiSummaryResult');
    const followupResult = document.getElementById('aiFollowupResult');
    const summarizeBtn = summarizeForm?.querySelector('button[type="submit"]');
    const followupBtn = followupForm?.querySelector('button[type="submit"]');
    const summaryStatus = document.getElementById('aiSummaryStatus');
    const followupStatus = document.getElementById('aiFollowupStatus');
    const summaryCopy = document.getElementById('aiSummaryCopy');
    const followupCopy = document.getElementById('aiFollowupCopy');
    const summaryClear = document.getElementById('aiSummaryClear');
    const followupClear = document.getElementById('aiFollowupClear');

    // Backend AI endpoints may not be ready; guard calls
    summarizeForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const notes = new FormData(summarizeForm).get('notes');
        if (summarizeBtn) summarizeBtn.disabled = true;
        summaryStatus.innerHTML = '<span class="inline-block h-3 w-3 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></span><span>Generating...</span>';
        summaryResult.textContent = '';
        try {
            const res = await fetch('/api.php/ai/summarize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${apiClient.getToken() || ''}`,
                },
                body: JSON.stringify({ notes }),
            });
            const data = await res.json();
            summaryResult.textContent = data.summary || data.message || 'No result';
        } catch (err) {
            ui.showToast('AI summarize failed', 'error');
            summaryResult.textContent = 'Error generating summary';
        } finally {
            if (summarizeBtn) summarizeBtn.disabled = false;
            if (summaryStatus) summaryStatus.textContent = '';
        }
    });

    followupForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(followupForm).entries());
        if (followupBtn) followupBtn.disabled = true;
        if (followupStatus) followupStatus.innerHTML = '<span class="inline-block h-3 w-3 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></span><span>Generating...</span>';
        followupResult.textContent = '';
        try {
            const res = await fetch('/api.php/ai/suggest-followup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${apiClient.getToken() || ''}`,
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            followupResult.textContent = data.message || 'No result';
        } catch (err) {
            ui.showToast('AI follow-up failed', 'error');
            followupResult.textContent = 'Error generating follow-up';
        } finally {
            if (followupBtn) followupBtn.disabled = false;
            if (followupStatus) followupStatus.textContent = '';
        }
    });

    summaryCopy?.addEventListener('click', () => {
        if (!summaryResult.textContent) return;
        navigator.clipboard.writeText(summaryResult.textContent);
        ui.showToast('Summary copied', 'success');
    });
    followupCopy?.addEventListener('click', () => {
        if (!followupResult.textContent) return;
        navigator.clipboard.writeText(followupResult.textContent);
        ui.showToast('Follow-up copied', 'success');
    });

    summaryClear?.addEventListener('click', () => {
        summarizeForm?.reset();
        summaryResult.textContent = '';
        if (summaryStatus) summaryStatus.textContent = '';
    });
    followupClear?.addEventListener('click', () => {
        followupForm?.reset();
        followupResult.textContent = '';
        if (followupStatus) followupStatus.textContent = '';
    });
}

function escapeHtml(str) {
    if (typeof str !== 'string') return str;
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function relativeDate(dateStr) {
    if (!dateStr) return '';
    const target = new Date(dateStr);
    const now = new Date();
    const diffMs = target - now;
    const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
    if (diffDays === 0) return 'Due today';
    if (diffDays === 1) return 'Due tomorrow';
    if (diffDays === -1) return 'Due yesterday';
    return diffDays > 0 ? `Due in ${diffDays}d` : `${Math.abs(diffDays)}d ago`;
}

function countByStatus(items, field) {
    const counts = {};
    items.forEach(item => {
        const key = item[field] || 'unknown';
        counts[key] = (counts[key] || 0) + 1;
    });
    return counts;
}

function renderBars(counts) {
    const entries = Object.entries(counts);
    if (!entries.length) {
        return '<div class="text-sm text-blue-700 text-center py-6">No data</div>';
    }
    const max = Math.max(...entries.map(([, v]) => v));
    return `
        <div class="grid grid-cols-${entries.length} h-full items-end gap-2">
            ${entries.map(([label, value]) => {
                const height = max ? Math.max(6, (value / max) * 100) : 0;
                return `
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-full bg-white border border-blue-100 rounded-sm flex-1 flex items-end">
                            <div class="w-full bg-blue-500 rounded-sm" style="height:${height}%"></div>
                        </div>
                        <span class="text-[10px] text-blue-700 uppercase">${escapeHtml(label)}</span>
                        <span class="text-[10px] text-blue-700 font-semibold">${value}</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function aggregateByDate(items, field, days = 7) {
    const today = new Date();
    const series = [];
    for (let i = days - 1; i >= 0; i--) {
        const day = new Date(today);
        day.setDate(day.getDate() - i);
        const key = day.toISOString().slice(0, 10);
        const count = items.filter(item => (item[field] || '').slice(0, 10) === key).length;
        series.push({ date: key, value: count });
    }
    return series;
}

function renderSparkline(series) {
    if (!series.length) {
        return '<div class="text-sm text-emerald-700 text-center py-6">No data</div>';
    }
    const max = Math.max(...series.map(s => s.value), 1);
    return `
        <div class="flex items-end h-full gap-2">
            ${series.map((point) => {
                const height = max ? Math.max(4, (point.value / max) * 100) : 0;
                return `
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-3 bg-emerald-500 rounded-sm" style="height:${height}%"></div>
                        <span class="text-[10px] text-emerald-700">${point.date.slice(5)}</span>
                        <span class="text-[10px] text-emerald-700 font-semibold">${point.value}</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function renderBarChart(canvasEl, labels, data, label, colors = ['#2563EB']) {
    const ctx = canvasEl.getContext('2d');
    if (!ctx) return;
    // destroy existing chart if re-rendering
    if (canvasEl._chart) {
        canvasEl._chart.destroy();
    }
    canvasEl._chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label,
                data,
                backgroundColor: colors.length === data.length ? colors : colors[0],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#475569' }, grid: { display: false } },
                y: { ticks: { color: '#475569', precision: 0 }, grid: { color: '#E2E8F0' } }
            }
        }
    });
}

function renderLineChart(canvasEl, labels, data, label, color = '#2563EB') {
    const ctx = canvasEl.getContext('2d');
    if (!ctx) return;
    if (canvasEl._chart) {
        canvasEl._chart.destroy();
    }
    canvasEl._chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label,
                data,
                fill: true,
                backgroundColor: color.replace(')', ',0.1)').replace('rgb', 'rgba'),
                borderColor: color,
                tension: 0.3,
                pointRadius: 3,
                pointBackgroundColor: color,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#475569' }, grid: { display: false } },
                y: { ticks: { color: '#475569', precision: 0 }, grid: { color: '#E2E8F0' } }
            }
        }
    });
}

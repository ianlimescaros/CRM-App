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
    const taskClientSelect = document.getElementById('taskClientSelect');
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
    let clientsCache = [];
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
            <tr data-id="${task.id}" data-lead-id="${task.lead_id || ''}" data-client-id="${task.client_id || ''}" class="border-b last:border-b-0 even:bg-gray-50 hover:bg-gray-100">
                <td class="px-3 py-2">${escapeHtml(task.title)}</td>
                <td class="px-3 py-2">${task.due_date ? escapeHtml(String(task.due_date)) : ''}</td>
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
                const clientId = e.target.closest('tr').dataset.clientId || '';
                if (taskLeadSelect) taskLeadSelect.value = leadId;
                if (taskClientSelect) taskClientSelect.value = clientId;
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
        if (taskClientSelect) taskClientSelect.value = '';
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
        payload.client_id = payload.client_id || payload.contact_id || null;
        delete payload.contact_id;
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
        if (taskClientSelect) {
            taskClientSelect.innerHTML = `<option value="">None</option>` + clientsCache.map(c => `
                <option value="${c.id}">${escapeHtml(c.full_name || '')} (#${c.id})</option>
            `).join('');
        }
    }

    Promise.all([apiClient.listLeads(), apiClient.listClients()])
        .then(([leadsRes, clientsRes]) => {
            leadsCache = leadsRes.leads || [];
            clientsCache = clientsRes.clients || [];
            populateTaskSelects();
        })
        .catch(() => {
            leadsCache = [];
            clientsCache = [];
        });

    filterBtn?.addEventListener('click', () => {
        currentFilters = {
            status: statusFilter.value || '',
            due_date: dueFilter.value || '',
        };
        taskPagination.page = 1;
        loadTasks();
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

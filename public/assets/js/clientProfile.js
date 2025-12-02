// Client Profile page wired to API: contact detail + timeline/files/notes
document.addEventListener('DOMContentLoaded', async () => {
    const pageEl = document.querySelector('[data-page="client-profile"]');
    if (!pageEl) return;

    function escapeHtml(str) {
        if (typeof str !== 'string') return str;
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function safeText(value) {
        if (value === null || value === undefined) return '';
        return escapeHtml(String(value));
    }

    function sanitizeUrl(url) {
        if (!url) return null;
        try {
            const parsed = new URL(url, window.location.origin);
            if (parsed.protocol === 'http:' || parsed.protocol === 'https:') {
                return parsed.href;
            }
            return null;
        } catch (e) {
            return null;
        }
    }

    const params = new URLSearchParams(window.location.search);
    let contactId = params.get('contact_id');

    const nameEl = document.getElementById('clientName');
    const metaEl = document.getElementById('clientMeta');
    const initialsEl = document.getElementById('clientInitials');
    const timelineEl = document.getElementById('clientTimeline');
    const notesEl = document.getElementById('clientNotes');
    const filesEl = document.getElementById('clientFiles');
    const summaryEl = document.getElementById('clientSummary');
    const addNoteBtn = document.getElementById('clientNoteAdd');
    const tasksEl = document.getElementById('clientTasks');
    const dealsEl = document.getElementById('clientDeals');
    const quickTaskBtn = document.getElementById('clientQuickTask');
    const quickDealBtn = document.getElementById('clientQuickDeal');
    const uploadBtn = document.getElementById('clientFileAdd');
    const uploadInput = document.getElementById('clientFileInput');
    const taskModal = document.getElementById('taskModal');
    const dealModal = document.getElementById('dealModal');
    const taskSubmit = document.getElementById('taskSubmit');
    const dealSubmit = document.getElementById('dealSubmit');
    const taskTitleInput = document.getElementById('taskTitle');
    const taskDescInput = document.getElementById('taskDesc');
    const taskDueInput = document.getElementById('taskDue');
    const taskStatusInput = document.getElementById('taskStatus');
    const dealTitleInput = document.getElementById('dealTitle');
    const dealAmountInput = document.getElementById('dealAmount');
    const dealStageInput = document.getElementById('dealStage');
    const dealCloseInput = document.getElementById('dealClose');
    const noteModal = document.getElementById('noteModal');
    const noteContentInput = document.getElementById('noteContent');
    const noteSubmit = document.getElementById('noteSubmit');

    const setLoading = (el, text = 'Loading...') => {
        if (!el) return;
        el.innerHTML = `<div class="text-sm text-gray-500">${safeText(text)}</div>`;
    };

    const renderTimeline = (items = []) => {
        if (!timelineEl) return;
        if (!items.length) {
            timelineEl.innerHTML = '<div class="text-sm text-gray-500">No interactions yet.</div>';
            return;
        }
        timelineEl.innerHTML = items.map(item => `
            <div class="flex gap-3">
                <div class="text-xs text-gray-500 w-24">${safeText(item.at)}</div>
                <div class="flex-1 bg-gray-50 border border-border rounded-card px-3 py-2 text-sm">
                    <div class="font-semibold capitalize">${safeText(item.type)}</div>
                    <div class="text-gray-700">${safeText(item.detail)}</div>
                </div>
            </div>
        `).join('');
    };

    const renderNotes = (items = []) => {
        if (!notesEl) return;
        if (!items.length) {
            notesEl.innerHTML = '<div class="text-sm text-gray-500">No notes yet.</div>';
            return;
        }
        notesEl.innerHTML = items.map(n => `
            <div class="bg-gray-50 border border-border rounded-card px-3 py-2 text-sm">
                <div class="text-gray-800">${safeText(n.content)}</div>
                <div class="text-xs text-gray-500 mt-1">${safeText(n.created_at)}</div>
            </div>
        `).join('');
    };

    const renderFiles = (items = []) => {
        if (!filesEl) return;
        if (!items.length) {
            filesEl.innerHTML = '<div class="text-sm text-gray-500">No files attached.</div>';
            return;
        }
        filesEl.innerHTML = items.map(f => `
            <div class="flex justify-between items-center bg-gray-50 border border-border rounded-card px-3 py-2 text-sm">
                <div>
                    <div class="font-semibold">${safeText(f.name)}</div>
                    <div class="text-xs text-gray-500">${safeText(f.size_label || '')} ${safeText(f.created_at || f.updated_at || '')}</div>
                </div>
                <div class="flex items-center gap-2">
                    ${sanitizeUrl(f.url) ? `<a href="${sanitizeUrl(f.url)}" target="_blank" rel="noopener noreferrer" class="text-blue-600 text-xs underline">View</a>` : ''}
                    <button data-file-id="${f.id}" class="text-xs text-red-600 hover:text-red-800">Delete</button>
                </div>
            </div>
        `).join('');
    };

    const renderSummary = (contact, stats = {}, timeline = []) => {
        if (!summaryEl) return;
        const lastContact = timeline[0]?.at || contact?.updated_at || '-';
        summaryEl.innerHTML = `
            <li>Last contact: ${safeText(lastContact)}</li>
            <li>Open deals: ${safeText(stats.deals ?? 0)}</li>
            <li>Open tasks: ${safeText(stats.tasks ?? 0)}</li>
            <li>Source: ${safeText(contact?.company || '-')}</li>
        `;
    };

    const renderHeader = (contact) => {
        if (!contact) return;
        nameEl.textContent = contact.full_name || 'Unnamed';
        const metaParts = [];
        if (contact.position) metaParts.push(contact.position);
        if (contact.company) metaParts.push(`@ ${contact.company}`);
        if (contact.email) metaParts.push(contact.email);
        if (contact.phone) metaParts.push(contact.phone);
        metaEl.textContent = metaParts.join(' • ') || '—';

        const parts = (contact.full_name || '').split(' ').filter(Boolean);
        initialsEl.textContent = parts.length ? (parts[0][0] || '') + (parts[1]?.[0] || '') : 'CP';
    };

    const renderTasks = (items = []) => {
        if (!tasksEl) return;
        if (!items.length) {
            tasksEl.innerHTML = '<div class="text-sm text-gray-500">No tasks linked.</div>';
            return;
        }
        tasksEl.innerHTML = items.map(t => `
            <div class="flex justify-between items-center border border-border rounded-card px-3 py-2 bg-gray-50">
                <div>
                    <div class="font-semibold">${safeText(t.title)}</div>
                    <div class="text-xs text-gray-500">${safeText(t.due_date || 'No due date')} • ${safeText(t.status)}</div>
                </div>
                <span class="text-xs text-gray-500">#${safeText(t.id)}</span>
            </div>
        `).join('');
    };

    const renderDeals = (items = []) => {
        if (!dealsEl) return;
        if (!items.length) {
            dealsEl.innerHTML = '<div class="text-sm text-gray-500">No deals linked.</div>';
            return;
        }
        dealsEl.innerHTML = items.map(d => `
            <div class="flex justify-between items-center border border-border rounded-card px-3 py-2 bg-gray-50">
                <div>
                    <div class="font-semibold">${safeText(d.title)}</div>
                    <div class="text-xs text-gray-500">${safeText(d.stage)} • $${Number(d.amount || 0).toLocaleString()}</div>
                </div>
                <span class="text-xs text-gray-500">#${safeText(d.id)}</span>
            </div>
        `).join('');
    };

    const loadContact = async () => {
        setLoading(timelineEl);
        setLoading(notesEl);
        setLoading(filesEl);
        setLoading(summaryEl);
        setLoading(tasksEl);
        setLoading(dealsEl);

        try {
            let detail;
            if (contactId) {
                detail = await apiClient.getContact(contactId);
            } else {
                const list = await apiClient.listContacts();
                const first = (list.contacts || [])[0];
                if (!first) {
                    nameEl.textContent = 'No contact found';
                    return null;
                }
                contactId = first.id;
                detail = await apiClient.getContact(contactId);
            }

            const contact = detail.contact;
            renderHeader(contact);

            const [timelineRes, notesRes, filesRes, tasksRes, dealsRes] = await Promise.all([
                apiClient.getContactTimeline(contact.id),
                apiClient.getContactNotes(contact.id),
                apiClient.getContactFiles(contact.id),
                apiClient.listTasks({ contact_id: contact.id }),
                apiClient.listDeals({ contact_id: contact.id }),
            ]);

            renderTimeline(timelineRes.timeline || []);
            renderNotes(notesRes.notes || []);
            renderFiles(filesRes.files || []);
            renderTasks(tasksRes.tasks || tasksRes || []);
            renderDeals(dealsRes.deals || dealsRes || []);
            renderSummary(contact, detail.stats || {}, timelineRes.timeline || []);
            return contact.id;
        } catch (err) {
            nameEl.textContent = 'Failed to load contact';
            metaEl.textContent = err?.message || '';
            if (window.ui?.showToast) ui.showToast(err?.message || 'Failed to load contact', 'error');
            return null;
        }
    };

    let currentId = await loadContact();

    if (addNoteBtn) {
        addNoteBtn.addEventListener('click', () => {
            if (!currentId) return;
            if (noteContentInput) noteContentInput.value = '';
            openModal(noteModal);
        });
    }

    const openModal = (modal) => modal && modal.classList.remove('hidden');
    const closeModal = (modal) => modal && modal.classList.add('hidden');
    const resetTaskForm = () => {
        if (taskTitleInput) taskTitleInput.value = '';
        if (taskDescInput) taskDescInput.value = '';
        if (taskDueInput) taskDueInput.value = '';
        if (taskStatusInput) taskStatusInput.value = 'pending';
    };
    const resetDealForm = () => {
        if (dealTitleInput) dealTitleInput.value = '';
        if (dealAmountInput) dealAmountInput.value = '';
        if (dealStageInput) dealStageInput.value = 'prospecting';
        if (dealCloseInput) dealCloseInput.value = '';
    };

    if (quickTaskBtn) {
        quickTaskBtn.addEventListener('click', () => {
            if (!currentId) return;
            resetTaskForm();
            openModal(taskModal);
        });
    }
    if (quickDealBtn) {
        quickDealBtn.addEventListener('click', () => {
            if (!currentId) return;
            resetDealForm();
            openModal(dealModal);
        });
    }

    document.querySelectorAll('[data-close-task]').forEach(btn => btn.addEventListener('click', () => closeModal(taskModal)));
    document.querySelectorAll('[data-close-deal]').forEach(btn => btn.addEventListener('click', () => closeModal(dealModal)));
    document.querySelectorAll('[data-close-note]').forEach(btn => btn.addEventListener('click', () => closeModal(noteModal)));

    if (taskSubmit) {
        taskSubmit.addEventListener('click', async () => {
            if (!currentId) return;
            const title = (taskTitleInput?.value || '').trim();
            if (!title) {
                ui?.showToast && ui.showToast('Task title is required', 'error');
                return;
            }
            try {
                await apiClient.createTask({
                    title,
                    description: taskDescInput?.value || '',
                    due_date: taskDueInput?.value || null,
                    status: taskStatusInput?.value || 'pending',
                    contact_id: currentId,
                });
                ui?.showToast && ui.showToast('Task created', 'success');
                closeModal(taskModal);
                const tasksRes = await apiClient.listTasks({ contact_id: currentId });
                renderTasks(tasksRes.tasks || tasksRes || []);
                ContactActivityRefresh(currentId);
            } catch (err) {
                ui?.showToast && ui.showToast(err?.message || 'Failed to create task', 'error');
            }
        });
    }

    if (dealSubmit) {
        dealSubmit.addEventListener('click', async () => {
            if (!currentId) return;
            const title = (dealTitleInput?.value || '').trim();
            if (!title) {
                ui?.showToast && ui.showToast('Deal title is required', 'error');
                return;
            }
            try {
                await apiClient.createDeal({
                    title,
                    amount: dealAmountInput?.value || 0,
                    stage: dealStageInput?.value || 'prospecting',
                    close_date: dealCloseInput?.value || null,
                    contact_id: currentId,
                });
                ui?.showToast && ui.showToast('Deal created', 'success');
                closeModal(dealModal);
                const dealsRes = await apiClient.listDeals({ contact_id: currentId });
                renderDeals(dealsRes.deals || dealsRes || []);
                ContactActivityRefresh(currentId);
            } catch (err) {
                ui?.showToast && ui.showToast(err?.message || 'Failed to create deal', 'error');
            }
        });
    }

    if (uploadBtn && uploadInput) {
        uploadBtn.addEventListener('click', () => {
            if (!currentId) return;
            uploadInput.value = '';
            uploadInput.click();
        });
        uploadInput.addEventListener('change', async () => {
            if (!currentId) return;
            const file = uploadInput.files?.[0];
            if (!file) return;
            try {
                const fd = new FormData();
                fd.append('file', file);
                const token = apiClient.getToken();
                const res = await fetch(`/api.php/contacts/${currentId}/files`, {
                    method: 'POST',
                    headers: token ? { 'Authorization': `Bearer ${token}` } : {},
                    body: fd,
                });
                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    throw new Error(data.message || 'Upload failed');
                }
                if (window.ui?.showToast) ui.showToast('File uploaded', 'success');
                const filesRes = await apiClient.getContactFiles(currentId);
                renderFiles(filesRes.files || []);
                ContactActivityRefresh(currentId);
            } catch (err) {
                if (window.ui?.showToast) ui.showToast(err?.message || 'Failed to upload', 'error');
            }
        });
    }

    if (filesEl) {
        filesEl.addEventListener('click', async (e) => {
            const btn = e.target.closest('button[data-file-id]');
            if (!btn || !currentId) return;
            const id = btn.dataset.fileId;
            if (!id) return;
            try {
                await fetch(`/api.php/contacts/${currentId}/files`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        ...(apiClient.getToken() ? { 'Authorization': `Bearer ${apiClient.getToken()}` } : {})
                    },
                    body: JSON.stringify({ file_id: Number(id) })
                }).then(async res => {
                    if (!res.ok) {
                        const data = await res.json().catch(() => ({}));
                        throw new Error(data.message || 'Delete failed');
                    }
                });
                if (window.ui?.showToast) ui.showToast('File deleted', 'success');
                const filesRes = await apiClient.getContactFiles(currentId);
                renderFiles(filesRes.files || []);
                ContactActivityRefresh(currentId);
            } catch (err) {
                if (window.ui?.showToast) ui.showToast(err?.message || 'Failed to delete', 'error');
            }
        });
    }

    async function ContactActivityRefresh(id) {
        try {
            const timelineRes = await apiClient.getContactTimeline(id);
            renderTimeline(timelineRes.timeline || []);
        } catch (e) {
            // ignore
        }
    }

    if (noteSubmit) {
        noteSubmit.addEventListener('click', async () => {
            if (!currentId) return;
            const note = (noteContentInput?.value || '').trim();
            if (!note) {
                ui?.showToast && ui.showToast('Note content is required', 'error');
                return;
            }
            try {
                await apiClient.addContactNote(currentId, note);
                ui?.showToast && ui.showToast('Note added', 'success');
                closeModal(noteModal);
                const [notesRes, timelineRes] = await Promise.all([
                    apiClient.getContactNotes(currentId),
                    apiClient.getContactTimeline(currentId),
                ]);
                renderNotes(notesRes.notes || []);
                renderSummary({ id: currentId }, {}, timelineRes.timeline || []);
                ContactActivityRefresh(currentId);
            } catch (err) {
                ui?.showToast && ui.showToast(err?.message || 'Failed to add note', 'error');
            }
        });
    }
});

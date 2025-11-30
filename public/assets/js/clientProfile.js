// Client Profile page wired to API: contact detail + timeline/files/notes
document.addEventListener('DOMContentLoaded', async () => {
    const pageEl = document.querySelector('[data-page="client-profile"]');
    if (!pageEl) return;

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

    const setLoading = (el, text = 'Loading...') => {
        if (!el) return;
        el.innerHTML = `<div class="text-sm text-gray-500">${text}</div>`;
    };

    const renderTimeline = (items = []) => {
        if (!timelineEl) return;
        if (!items.length) {
            timelineEl.innerHTML = '<div class="text-sm text-gray-500">No interactions yet.</div>';
            return;
        }
        timelineEl.innerHTML = items.map(item => `
            <div class="flex gap-3">
                <div class="text-xs text-gray-500 w-24">${item.at}</div>
                <div class="flex-1 bg-gray-50 border border-border rounded-card px-3 py-2 text-sm">
                    <div class="font-semibold capitalize">${item.type}</div>
                    <div class="text-gray-700">${item.detail}</div>
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
                <div class="text-gray-800">${n.content}</div>
                <div class="text-xs text-gray-500 mt-1">${n.created_at}</div>
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
                <span>${f.name}</span>
                <span class="text-xs text-gray-500">${f.size_label || ''} • ${f.created_at || f.updated_at || ''}</span>
            </div>
        `).join('');
    };

    const renderSummary = (contact, stats = {}, timeline = []) => {
        if (!summaryEl) return;
        const lastContact = timeline[0]?.at || contact?.updated_at || '—';
        summaryEl.innerHTML = `
            <li>Last contact: ${lastContact}</li>
            <li>Open deals: ${stats.deals ?? 0}</li>
            <li>Open tasks: ${stats.tasks ?? 0}</li>
            <li>Source: ${contact?.company || '—'}</li>
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
                    <div class="font-semibold">${t.title}</div>
                    <div class="text-xs text-gray-500">${t.due_date || 'No due date'} • ${t.status}</div>
                </div>
                <span class="text-xs text-gray-500">#${t.id}</span>
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
                    <div class="font-semibold">${d.title}</div>
                    <div class="text-xs text-gray-500">${d.stage} • $${Number(d.amount || 0).toLocaleString()}</div>
                </div>
                <span class="text-xs text-gray-500">#${d.id}</span>
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
        addNoteBtn.addEventListener('click', async () => {
            if (!currentId) return;
            const note = prompt('Add a note for this contact:');
            if (!note || !note.trim()) return;
            try {
                await apiClient.addContactNote(currentId, note.trim());
                if (window.ui?.showToast) ui.showToast('Note added', 'success');
                const notesRes = await apiClient.getContactNotes(currentId);
                const timelineRes = await apiClient.getContactTimeline(currentId);
                renderNotes(notesRes.notes || []);
                renderSummary({ id: currentId }, {}, timelineRes.timeline || []);
            } catch (err) {
                if (window.ui?.showToast) ui.showToast(err?.message || 'Failed to add note', 'error');
            }
        });
    }

    if (quickTaskBtn) {
        quickTaskBtn.addEventListener('click', async () => {
            if (!currentId) return;
            const title = prompt('Task title for this contact:');
            if (!title || !title.trim()) return;
            try {
                await apiClient.createTask({ title: title.trim(), contact_id: currentId, status: 'pending' });
                if (window.ui?.showToast) ui.showToast('Task created', 'success');
                const tasksRes = await apiClient.listTasks({ contact_id: currentId });
                renderTasks(tasksRes.tasks || tasksRes || []);
                ContactActivityRefresh(currentId);
            } catch (err) {
                if (window.ui?.showToast) ui.showToast(err?.message || 'Failed to create task', 'error');
            }
        });
    }

    if (quickDealBtn) {
        quickDealBtn.addEventListener('click', async () => {
            if (!currentId) return;
            const title = prompt('Deal title for this contact:');
            if (!title || !title.trim()) return;
            try {
                await apiClient.createDeal({ title: title.trim(), stage: 'prospecting', contact_id: currentId, amount: 0 });
                if (window.ui?.showToast) ui.showToast('Deal created', 'success');
                const dealsRes = await apiClient.listDeals({ contact_id: currentId });
                renderDeals(dealsRes.deals || dealsRes || []);
                ContactActivityRefresh(currentId);
            } catch (err) {
                if (window.ui?.showToast) ui.showToast(err?.message || 'Failed to create deal', 'error');
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

    async function ContactActivityRefresh(id) {
        try {
            const timelineRes = await apiClient.getContactTimeline(id);
            renderTimeline(timelineRes.timeline || []);
        } catch (e) {
            // ignore
        }
    }
});

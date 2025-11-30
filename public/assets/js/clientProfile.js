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
                <span class="text-xs text-gray-500">${f.size} • ${f.updated_at}</span>
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

    const loadContact = async () => {
        setLoading(timelineEl);
        setLoading(notesEl);
        setLoading(filesEl);
        setLoading(summaryEl);

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

            const [timelineRes, notesRes, filesRes] = await Promise.all([
                apiClient.getContactTimeline(contact.id),
                apiClient.getContactNotes(contact.id),
                apiClient.getContactFiles(contact.id),
            ]);

            renderTimeline(timelineRes.timeline || []);
            renderNotes(notesRes.notes || []);
            renderFiles(filesRes.files || []);
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
});

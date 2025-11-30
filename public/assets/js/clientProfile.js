// Client Profile rendering using existing contacts API (stub timeline/files/notes)
document.addEventListener('DOMContentLoaded', async () => {
    const pageEl = document.querySelector('[data-page="client-profile"]');
    if (!pageEl) return;
    const params = new URLSearchParams(window.location.search);
    const contactId = params.get('contact_id');

    const nameEl = document.getElementById('clientName');
    const metaEl = document.getElementById('clientMeta');
    const initialsEl = document.getElementById('clientInitials');
    const timelineEl = document.getElementById('clientTimeline');
    const notesEl = document.getElementById('clientNotes');
    const filesEl = document.getElementById('clientFiles');
    const summaryEl = document.getElementById('clientSummary');

    try {
        const res = await apiClient.listContacts();
        const contacts = res.contacts || [];
        let contact = null;
        if (contactId) {
            contact = contacts.find(c => String(c.id) === contactId);
        }
        if (!contact && contacts.length) {
            contact = contacts[0];
        }
        if (!contact) {
            nameEl.textContent = 'No contact found';
            metaEl.textContent = '';
            return;
        }

        nameEl.textContent = contact.full_name || 'Unnamed';
        metaEl.textContent = `${contact.position || ''} ${contact.company ? '@ ' + contact.company : ''} • ${contact.email || '—'} • ${contact.phone || '—'}`;
        if (initialsEl) {
            const parts = (contact.full_name || '').split(' ').filter(Boolean);
            initialsEl.textContent = parts.length ? (parts[0][0] || '') + (parts[1]?.[0] || '') : 'CP';
        }

        const timeline = [
            { date: 'Today', type: 'call', detail: 'Called client about new listing' },
            { date: 'Yesterday', type: 'email', detail: 'Sent brochure and pricing' },
            { date: 'Last week', type: 'meeting', detail: 'On-site viewing scheduled' },
        ];
        timelineEl.innerHTML = timeline.map(item => `
            <div class="flex gap-3">
                <div class="text-xs text-gray-500 w-24">${item.date}</div>
                <div class="flex-1 bg-gray-50 border border-border rounded-card px-3 py-2 text-sm">
                    <div class="font-semibold capitalize">${item.type}</div>
                    <div class="text-gray-700">${item.detail}</div>
                </div>
            </div>
        `).join('');

        const notes = [
            'Prefers email; interested in 3-bed units.',
            'Budget flexible if location is prime.',
        ];
        notesEl.innerHTML = notes.map(n => `<div class="bg-gray-50 border border-border rounded-card px-3 py-2 text-sm">${n}</div>`).join('');

        const files = [
            { name: 'Brochure.pdf', size: '1.2MB' },
            { name: 'Floorplan.png', size: '820KB' },
        ];
        filesEl.innerHTML = files.map(f => `
            <div class="flex justify-between items-center bg-gray-50 border border-border rounded-card px-3 py-2 text-sm">
                <span>${f.name}</span>
                <span class="text-xs text-gray-500">${f.size}</span>
            </div>
        `).join('');

        summaryEl.innerHTML = `
            <li>Last contact: Today</li>
            <li>Open deals: 0</li>
            <li>Open tasks: 0</li>
            <li>Source: ${contact.company || '—'}</li>
        `;
    } catch (err) {
        if (nameEl) nameEl.textContent = 'Failed to load contact';
    }
});

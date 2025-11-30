<section data-page="contacts">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Contacts</h1>
        <div class="flex items-center gap-2">
            <input id="contactSearch" placeholder="Search name/email" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <button id="contactAddBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Contact</button>
        </div>
    </div>
    <div class="bg-white border border-border rounded-card shadow-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Email</th>
                    <th class="px-3 py-2 text-left">Phone</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="contactsTableBody"></tbody>
        </table>
    </div>
    <div id="contactPagination" class="mt-2"></div>

    <div id="contactFormContainer" class="mt-4 hidden">
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl">
            <div class="bg-white rounded-card p-4">
        <h2 class="text-xl font-semibold mb-2" id="contactFormTitle">New Contact</h2>
        <form id="contactForm" class="grid gap-3 sm:grid-cols-2">
            <div id="contactFormError" class="sm:col-span-2 text-sm text-red-600 hidden"></div>
            <input type="hidden" name="id">
            <div class="sm:col-span-2">
                <label class="block text-sm">Full Name</label>
                <input name="full_name" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" required>
            </div>
            <div>
                <label class="block text-sm">Email</label>
                <input name="email" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" type="email">
            </div>
            <div>
                <label class="block text-sm">Phone</label>
                <input name="phone" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-sm">Company</label>
                <input name="company" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-sm">Position</label>
                <input name="position" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition" type="submit">Save</button>
                <button class="px-4 py-2 border border-border rounded hover:bg-gray-100 transition" type="button" id="contactFormCancel">Cancel</button>
            </div>
        </form>
            </div>
        </div>
    </div>
</section>

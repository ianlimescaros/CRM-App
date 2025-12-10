<section data-page="contacts">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Clients/Landlords</h1>
        <div class="flex items-center gap-2">
            <label class="sr-only" for="contactSearch">Search clients</label>
            <input id="contactSearch" name="contactSearch" placeholder="Search name/email" autocomplete="off" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <button id="contactAddBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Client/Landlord</button>
        </div>
    </div>
    <div class="bg-white border border-border rounded-card shadow-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Email</th>
                    <th class="px-3 py-2 text-left">Phone</th>
                    <th class="px-3 py-2 text-left">Documents</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="contactsTableBody"></tbody>
        </table>
    </div>
    <div id="contactPagination" class="mt-2"></div>

    <div id="contactFormContainer" class="fixed inset-0 bg-black/50 hidden z-40 flex items-start justify-center overflow-y-auto p-4">
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-3xl mt-10">
            <div class="bg-white rounded-card p-5">
        <h2 class="text-xl font-semibold mb-2" id="contactFormTitle">New Client</h2>
        <form id="contactForm" class="grid gap-3 sm:grid-cols-2">
            <div id="contactFormError" class="sm:col-span-2 text-sm text-red-600 hidden"></div>
            <input type="hidden" name="id">
            <div class="sm:col-span-2">
                <label class="block text-sm" for="contactFullName">Full Name</label>
                <input id="contactFullName" name="full_name" autocomplete="name" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" required>
            </div>
            <div>
                <label class="block text-sm" for="contactEmail">Email</label>
                <input id="contactEmail" name="email" autocomplete="email" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" type="email">
            </div>
            <div>
                <label class="block text-sm" for="contactPhone">Phone</label>
                <input id="contactPhone" name="phone" autocomplete="tel" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div class="sm:col-span-2 grid sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-sm" for="contactDocTitleDeed">Title Deed (optional)</label>
                    <input id="contactDocTitleDeed" data-doc-label="Title Deed" type="file" class="w-full text-sm border border-border rounded px-3 py-2 file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:rounded file:text-sm" />
                </div>
                <div>
                    <label class="block text-sm" for="contactDocEmiratesId">Emirates ID (optional)</label>
                    <input id="contactDocEmiratesId" data-doc-label="Emirates ID" type="file" class="w-full text-sm border border-border rounded px-3 py-2 file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:rounded file:text-sm" />
                </div>
                <div>
                    <label class="block text-sm" for="contactDocPassport">Passport (optional)</label>
                    <input id="contactDocPassport" data-doc-label="Passport" type="file" class="w-full text-sm border border-border rounded px-3 py-2 file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:rounded file:text-sm" />
                </div>
                <p class="sm:col-span-3 text-xs text-gray-500">Accepted: PDF, DOC, DOCX, PNG, JPG up to 10MB. Leave blank if not provided.</p>
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition" type="submit">Save</button>
                <button class="px-4 py-2 border border-border rounded hover:bg-gray-100 transition" type="button" id="contactFormCancel">Cancel</button>
            </div>
        </form>
            </div>
        </div>
    </div>

    <div id="contactFilesModal" class="fixed inset-0 bg-black/50 hidden z-40 overflow-y-auto p-4 flex items-center justify-center">
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-xl">
            <div class="bg-white rounded-card p-5 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold">Documents</h3>
                        <p id="contactFilesSubtitle" class="text-sm text-gray-500"></p>
                    </div>
                    <button id="contactFilesClose" type="button" class="h-8 w-8 inline-flex items-center justify-center rounded-full border border-border text-gray-600 hover:bg-gray-100" aria-label="Close documents" title="Close">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="contactFilesList" class="space-y-2 text-sm overflow-y-auto">
                    <div class="text-gray-500">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</section>

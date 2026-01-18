<!-- View template for the deals page. -->

<section data-page="deals">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Deals</h1>
        <button id="dealAddBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Deal</button>
    </div>
    <div class="flex gap-2 mb-4">
        <label class="sr-only" for="dealStageFilter">Status filter</label>
        <select id="dealStageFilter" name="dealStageFilter" autocomplete="off" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <option value="">All status</option>
            <option value="ongoing" selected>Ongoing</option>
            <option value="pending">Pending</option>
        </select>
        <button id="dealFilterBtn" class="px-3 py-2 border border-border rounded text-sm hover:bg-gray-100 transition">Filter</button>
    </div>
    <div class="bg-white border border-border rounded-card shadow-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left">Buyer/Tenant Name</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Amount</th>
                    <th class="px-3 py-2 text-left">Client/Landlord</th>
                    <th class="px-3 py-2 text-left">Location</th>
                    <th class="px-3 py-2 text-left">Property Detail</th>
                    <th class="px-3 py-2 text-left">Close Date</th>
                    <th class="px-3 py-2 text-left">Documents</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="dealsTableBody"></tbody>
        </table>
    </div>
    <div id="dealPagination" class="mt-2"></div>

    <div id="dealFormContainer" class="fixed inset-0 bg-black/50 hidden z-40 flex items-start justify-center overflow-y-auto p-4">
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-3xl mt-10">
            <div class="bg-white rounded-card p-5">
        <h2 class="text-xl font-semibold mb-2" id="dealFormTitle">New Deal</h2>
        <form id="dealForm" class="grid gap-3 sm:grid-cols-2">
            <div id="dealFormError" class="sm:col-span-2 text-sm text-red-600 hidden"></div>
            <input type="hidden" name="id">
            <div class="sm:col-span-2">
                <label class="block text-sm" for="dealTitleInput">Buyer/Tenant Name</label>
                <input id="dealTitleInput" name="title" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" required>
            </div>
            <div>
                <label class="block text-sm" for="dealStageSelect">Status</label>
                <select id="dealStageSelect" name="stage" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="ongoing" selected>Ongoing</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div>
                <label class="block text-sm" for="dealAmountInput">Amount</label>
                <div class="flex">
                    <select id="dealCurrency" name="currency" class="border border-border rounded-l px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 bg-gray-50">
                        <option value="AED">AED</option>
                        <option value="USD">USD</option>
                    </select>
                    <input id="dealAmountInput" name="amount" autocomplete="off" class="w-full border border-border border-l-0 px-3 py-2 rounded-r focus:outline-none focus:ring-2 focus:ring-accent/30" type="text" inputmode="decimal" placeholder="e.g. 500,000">
                </div>
            </div>
            <div>
                <label class="block text-sm" for="dealDocInput">Deal Document (optional)</label>
                <input id="dealDocInput" type="file" class="w-full text-sm border border-border rounded px-3 py-2 file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:rounded file:text-sm" />
                <p class="text-xs text-gray-500 mt-1">Accepted: PDF, DOC, DOCX, PNG, JPG up to 10MB.</p>
            </div>
            <div>
                <label class="block text-sm" for="dealLocation">Location</label>
                <input id="dealLocation" name="location" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" placeholder="Dubai Marina">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm" for="dealPropertyDetail">Property Detail</label>
                <textarea id="dealPropertyDetail" name="property_detail" rows="2" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" placeholder="Unit type, size, notes"></textarea>
            </div>
            <div>
                <label class="block text-sm" for="dealCloseDate">Close Date</label>
                <input id="dealCloseDate" name="close_date" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" type="date">
            </div>
            <div>
                <label class="block text-sm" for="dealLeadSelect">Lead (optional)</label>
                <select name="lead_id" id="dealLeadSelect" autocomplete="off" class="w-full border border-border px-3 py-2 rounded">
                    <option value="">None</option>
                </select>
            </div>
            <div>
                <label class="block text-sm" for="dealClientSelect">Client/Landlord (optional)</label>
                <select name="client_id" id="dealClientSelect" autocomplete="off" class="w-full border border-border px-3 py-2 rounded">
                    <option value="">None</option>
                </select>
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition" type="submit">Save</button>
                <button class="px-4 py-2 border border-border rounded hover:bg-gray-100 transition" type="button" id="dealFormCancel">Cancel</button>
            </div>
        </form>
            </div>
        </div>
    </div>

    <div id="dealFilesModal" class="fixed inset-0 bg-black/50 hidden z-40 overflow-y-auto p-4 flex items-center justify-center">
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-xl">
            <div class="bg-white rounded-card p-5 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold">Deal Documents</h3>
                        <p id="dealFilesSubtitle" class="text-sm text-gray-500"></p>
                    </div>
                    <button id="dealFilesClose" type="button" class="h-8 w-8 inline-flex items-center justify-center rounded-full border border-border text-gray-600 hover:bg-gray-100" aria-label="Close documents" title="Close">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="dealFilesList" class="space-y-2 text-sm overflow-y-auto">
                    <div class="text-gray-500">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</section>

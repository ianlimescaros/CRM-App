<section data-page="leads">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold">Leads</h1>
            <div class="flex border border-border rounded-card overflow-hidden text-sm">
                <button id="leadViewTable" class="px-3 py-2 bg-accent text-white">Table</button>
                <button id="leadViewKanban" class="px-3 py-2 text-gray-700 hover:bg-gray-100">Kanban</button>
            </div>
        </div>
        <button id="leadAddBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Lead</button>
    </div>
    <div class="flex gap-2 mb-4 flex-wrap">
        <label class="sr-only" for="leadStatusFilter">Status</label>
        <select id="leadStatusFilter" name="leadStatusFilter" autocomplete="off" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <option value="">All statuses</option>
            <option value="new">New</option>
            <option value="contacted">Contacted</option>
            <option value="qualified">Qualified</option>
            <option value="not_qualified">Not Qualified</option>
        </select>
        <label class="sr-only" for="leadSourceFilter">Source</label>
        <select id="leadSourceFilter" name="leadSourceFilter" autocomplete="off" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <option value="">All sources</option>
            <option value="Bayut">Bayut</option>
            <option value="Property Finder">Property Finder</option>
            <option value="Dubizzel">Dubizzel</option>
            <option value="Reference/Random">Reference/Random</option>
            <option value="Social Media">Social Media</option>
        </select>
        <button id="leadFilterBtn" class="px-3 py-2 border border-border rounded text-sm hover:bg-gray-100 transition">Filter</button>
        <label class="sr-only" for="leadSort">Sort</label>
        <select id="leadSort" name="leadSort" autocomplete="off" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <option value="created_at:DESC">Newest</option>
            <option value="created_at:ASC">Oldest</option>
            <option value="name:ASC">Name A-Z</option>
            <option value="name:DESC">Name Z-A</option>
            <option value="last_contact_at:DESC">Last contact</option>
        </select>
    </div>
    <div class="flex items-center gap-2 mb-3 text-sm">
        <input type="checkbox" id="leadSelectAll" class="h-4 w-4 border border-border rounded">
        <span class="text-gray-600">Select all</span>
        <select id="leadBulkStatus" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <option value="">Bulk status…</option>
            <option value="new">New</option>
            <option value="contacted">Contacted</option>
            <option value="qualified">Qualified</option>
            <option value="not_qualified">Not Qualified</option>
        </select>
        <button id="leadBulkApply" class="px-3 py-2 border border-border rounded text-sm hover:bg-gray-100 transition">Apply</button>
    </div>
    <div id="leadTableWrap" class="bg-white border border-border rounded-card shadow-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left"><input type="checkbox" id="leadHeaderCheckbox" class="h-4 w-4 border border-border rounded"></th>
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Source</th>
                    <th class="px-3 py-2 text-left">Property For</th>
                    <th class="px-3 py-2 text-left">Interested Property</th>
                    <th class="px-3 py-2 text-left">Area</th>
                    <th class="px-3 py-2 text-left">Last Contacted</th>
                    <th class="px-3 py-2 text-left">Budget</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="leadsTableBody"></tbody>
        </table>
    </div>
    <div id="leadPagination" class="mt-2"></div>

    <div id="leadKanban" class="hidden mt-4">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="bg-white border border-border rounded-card shadow-card p-3" data-status="new">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-sm">New</h3>
                    <span class="text-xs text-gray-500" data-count="new">0</span>
                </div>
                <div class="space-y-2" data-column="new"></div>
            </div>
            <div class="bg-white border border-border rounded-card shadow-card p-3" data-status="contacted">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-sm">Contacted</h3>
                    <span class="text-xs text-gray-500" data-count="contacted">0</span>
                </div>
                <div class="space-y-2" data-column="contacted"></div>
            </div>
            <div class="bg-white border border-border rounded-card shadow-card p-3" data-status="qualified">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-sm">Qualified</h3>
                    <span class="text-xs text-gray-500" data-count="qualified">0</span>
                </div>
                <div class="space-y-2" data-column="qualified"></div>
            </div>
            <div class="bg-white border border-border rounded-card shadow-card p-3" data-status="not_qualified">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-sm">Not Qualified</h3>
                    <span class="text-xs text-gray-500" data-count="not_qualified">0</span>
                </div>
                <div class="space-y-2" data-column="not_qualified"></div>
            </div>
        </div>
    </div>

    <div id="leadFormContainer" class="fixed inset-0 bg-black/50 hidden z-40 flex items-start justify-center overflow-y-auto p-4">
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-3xl mt-10">
            <div class="bg-white rounded-card p-5">
        <h2 class="text-xl font-semibold mb-2" id="leadFormTitle">New Lead</h2>
        <form id="leadForm" class="grid gap-3 sm:grid-cols-2">
            <div id="leadFormError" class="sm:col-span-2 text-sm text-red-600 hidden"></div>
            <input type="hidden" name="id">
            <div class="sm:col-span-2">
                <label class="block text-sm" for="leadName">Name</label>
                <input id="leadName" name="name" autocomplete="name" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" required>
            </div>
            <div>
                <label class="block text-sm" for="leadProperty">Interested Property</label>
                <select id="leadProperty" name="interested_property" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="">Select type</option>
                    <option value="Studio">Studio</option>
                    <option value="1 Bedroom">1 Bedroom</option>
                    <option value="2 Bedroom">2 Bedroom</option>
                    <option value="3 Bedroom">3 Bedroom</option>
                    <option value="4 Bedroom">4 Bedroom</option>
                    <option value="Townhouse/Villa">Townhouse/Villa</option>
                    <option value="Commercial Warehouse">Commercial Warehouse</option>
                    <option value="Commercial Office">Commercial Office</option>
                    <option value="Commercial Rental">Commercial Rental</option>
                </select>
            </div>
            <div>
                <label class="block text-sm" for="leadArea">Area</label>
                <input id="leadArea" name="area" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" placeholder="Dubai">
            </div>
            <div>
                <label class="block text-sm" for="leadEmail">Email</label>
                <input id="leadEmail" name="email" autocomplete="email" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" type="email">
            </div>
            <div>
                <label class="block text-sm" for="leadPhone">Phone</label>
                <input id="leadPhone" name="phone" autocomplete="tel" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
            </div>
            <div>
                <label class="block text-sm" for="leadStatus">Status</label>
                <select id="leadStatus" name="status" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="qualified">Qualified</option>
                    <option value="not_qualified">Not Qualified</option>
                </select>
            </div>
            <div>
                <label class="block text-sm" for="leadPropertyFor">Property For</label>
                <select id="leadPropertyFor" name="property_for" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="">Select</option>
                    <option value="Rent/Lease">Rent/Lease</option>
                    <option value="Sale/Buy">Sale/Buy</option>
                    <option value="Off-Plan/Buyer">Off-Plan/Buyer</option>
                </select>
            </div>
            <div>
                <label class="block text-sm" for="leadPaymentOption">Payment Option (optional)</label>
                <select id="leadPaymentOption" name="payment_option" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="">Select</option>
                    <option value="Cash">Cash</option>
                    <option value="Mortgage">Mortgage</option>
                </select>
            </div>
            <div>
                <label class="block text-sm" for="leadSource">Source</label>
                <select id="leadSource" name="source" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="">Select source</option>
                    <option value="Bayut">Bayut</option>
                    <option value="Property Finder">Property Finder</option>
                    <option value="Dubizzel">Dubizzel</option>
                    <option value="Reference/Random">Reference/Random</option>
                    <option value="Social Media">Social Media</option>
                </select>
            </div>
            <div>
                <label class="block text-sm" for="leadBudget">Budget</label>
                <div class="flex">
                    <select id="leadCurrency" name="currency" class="border border-border rounded-l px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                        <option value="USD">USD</option>
                        <option value="AED">AED</option>
                    </select>
                    <input id="leadBudget" name="budget" autocomplete="off" class="w-full border border-border border-l-0 px-3 py-2 rounded-r focus:outline-none focus:ring-2 focus:ring-accent/30" placeholder="500,000" inputmode="decimal">
                </div>
            </div>
            <div>
                <label class="block text-sm" for="leadLastContact">Last Contacted</label>
                <input id="leadLastContact" name="last_contact_at" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" type="date">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm" for="leadNotes">Notes</label>
                <textarea id="leadNotes" name="notes" autocomplete="off" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30"></textarea>
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition" type="submit">Save</button>
                <button class="px-4 py-2 border border-border rounded hover:bg-gray-100 transition" type="button" id="leadFormCancel">Cancel</button>
            </div>
        </form>
            </div>
        </div>
    </div>
</section>

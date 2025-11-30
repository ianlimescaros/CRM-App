<section data-page="deals">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Deals</h1>
        <button id="dealAddBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Deal</button>
    </div>
    <div class="flex gap-2 mb-4">
        <select id="dealStageFilter" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <option value="">All stages</option>
            <option value="prospecting">Prospecting</option>
            <option value="proposal">Proposal</option>
            <option value="negotiation">Negotiation</option>
            <option value="closed_won">Closed Won</option>
            <option value="closed_lost">Closed Lost</option>
        </select>
        <button id="dealFilterBtn" class="px-3 py-2 border border-border rounded text-sm hover:bg-gray-100 transition">Filter</button>
    </div>
    <div class="bg-white border border-border rounded-card shadow-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left">Title</th>
                    <th class="px-3 py-2 text-left">Stage</th>
                    <th class="px-3 py-2 text-left">Amount</th>
                    <th class="px-3 py-2 text-left">Close Date</th>
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
                <label class="block text-sm">Title</label>
                <input name="title" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" required>
            </div>
            <div>
                <label class="block text-sm">Stage</label>
                <select name="stage" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="prospecting">Prospecting</option>
                    <option value="proposal">Proposal</option>
                    <option value="negotiation">Negotiation</option>
                    <option value="closed_won">Closed Won</option>
                    <option value="closed_lost">Closed Lost</option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Amount</label>
                <input name="amount" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" type="number" step="0.01">
            </div>
            <div>
                <label class="block text-sm">Close Date</label>
                <input name="close_date" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" type="date">
            </div>
            <div>
                <label class="block text-sm">Lead (optional)</label>
                <select name="lead_id" id="dealLeadSelect" class="w-full border border-border px-3 py-2 rounded">
                    <option value="">None</option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Contact (optional)</label>
                <select name="contact_id" id="dealContactSelect" class="w-full border border-border px-3 py-2 rounded">
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
</section>

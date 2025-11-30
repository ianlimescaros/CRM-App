<section data-page="client-profile" class="max-w-5xl">
    <div class="bg-white border border-border rounded-card shadow-card p-4 mb-4">
        <div class="flex items-center gap-3">
            <div class="h-14 w-14 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold">
                <span id="clientInitials">CP</span>
            </div>
            <div>
                <h1 id="clientName" class="text-2xl font-semibold">Client Name</h1>
                <div id="clientMeta" class="text-sm text-gray-600">Title @ Company • email@example.com • (000) 000-0000</div>
                <div class="flex gap-2 mt-2 text-xs">
                    <span class="inline-flex px-2 py-1 rounded bg-blue-50 text-blue-700">Buyer</span>
                    <span class="inline-flex px-2 py-1 rounded bg-emerald-50 text-emerald-700">High Priority</span>
                </div>
                <div class="flex gap-2 mt-3 text-xs">
                    <button id="clientQuickTask" class="px-3 py-1 border border-border rounded hover:bg-gray-100">Add task</button>
                    <button id="clientQuickDeal" class="px-3 py-1 border border-border rounded hover:bg-gray-100">Add deal</button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white border border-border rounded-card shadow-card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Timeline</h2>
                    <span class="text-xs text-muted">Recent interactions</span>
                </div>
                <div id="clientTimeline" class="space-y-3 text-sm">
                    <!-- filled by JS -->
                </div>
            </div>

            <div class="bg-white border border-border rounded-card shadow-card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Related tasks</h2>
                    <span class="text-xs text-muted">Linked to this contact</span>
                </div>
                <div id="clientTasks" class="space-y-2 text-sm text-gray-700">
                    <div class="text-gray-500">Loading...</div>
                </div>
            </div>

            <div class="bg-white border border-border rounded-card shadow-card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Related deals</h2>
                    <span class="text-xs text-muted">Linked to this contact</span>
                </div>
                <div id="clientDeals" class="space-y-2 text-sm text-gray-700">
                    <div class="text-gray-500">Loading...</div>
                </div>
            </div>

            <div class="bg-white border border-border rounded-card shadow-card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Notes</h2>
                    <button id="clientNoteAdd" class="px-3 py-1 border border-border rounded text-sm hover:bg-gray-100">Add note</button>
                </div>
                <div id="clientNotes" class="space-y-2 text-sm">
                    <div class="text-gray-500">No notes yet.</div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white border border-border rounded-card shadow-card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Files</h2>
                    <div class="flex items-center gap-2">
                        <input type="file" id="clientFileInput" class="hidden" />
                        <button id="clientFileAdd" class="px-3 py-1 border border-border rounded text-sm hover:bg-gray-100">Upload</button>
                    </div>
                </div>
                <div id="clientFiles" class="space-y-2 text-sm">
                    <div class="text-gray-500">No files attached.</div>
                </div>
            </div>
            <div class="bg-white border border-border rounded-card shadow-card p-4">
                <h2 class="font-semibold mb-3">Summary</h2>
                <ul class="space-y-1 text-sm text-gray-700" id="clientSummary">
                    <li>Last contact: —</li>
                    <li>Open deals: —</li>
                    <li>Open tasks: —</li>
                    <li>Source: —</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Quick Task Modal -->
<div id="taskModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-card shadow-xl w-full max-w-lg p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Add Task for this contact</h3>
            <button data-close-task class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="text-sm text-gray-700">Title</label>
                <input id="taskTitle" class="w-full border border-border rounded px-3 py-2" placeholder="Follow-up call" />
            </div>
            <div>
                <label class="text-sm text-gray-700">Description</label>
                <textarea id="taskDesc" class="w-full border border-border rounded px-3 py-2" rows="2" placeholder="Details (optional)"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-gray-700">Due date</label>
                    <input id="taskDue" type="date" class="w-full border border-border rounded px-3 py-2" />
                </div>
                <div>
                    <label class="text-sm text-gray-700">Status</label>
                    <select id="taskStatus" class="w-full border border-border rounded px-3 py-2">
                        <option value="pending">Pending</option>
                        <option value="done">Done</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <button data-close-task class="px-3 py-2 border border-border rounded text-sm">Cancel</button>
            <button id="taskSubmit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Save task</button>
        </div>
    </div>
</div>

<!-- Quick Deal Modal -->
<div id="dealModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-card shadow-xl w-full max-w-lg p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">Add Deal for this contact</h3>
            <button data-close-deal class="text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="text-sm text-gray-700">Title</label>
                <input id="dealTitle" class="w-full border border-border rounded px-3 py-2" placeholder="Deal title" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-gray-700">Amount</label>
                    <input id="dealAmount" type="number" step="0.01" class="w-full border border-border rounded px-3 py-2" placeholder="0.00" />
                </div>
                <div>
                    <label class="text-sm text-gray-700">Stage</label>
                    <select id="dealStage" class="w-full border border-border rounded px-3 py-2">
                        <option value="prospecting">Prospecting</option>
                        <option value="proposal">Proposal</option>
                        <option value="negotiation">Negotiation</option>
                        <option value="closed_won">Closed Won</option>
                        <option value="closed_lost">Closed Lost</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-sm text-gray-700">Close date</label>
                <input id="dealClose" type="date" class="w-full border border-border rounded px-3 py-2" />
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <button data-close-deal class="px-3 py-2 border border-border rounded text-sm">Cancel</button>
            <button id="dealSubmit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Save deal</button>
        </div>
    </div>
</div>

<section data-page="client-profile" class="max-w-5xl">
    <div class="bg-white border border-border rounded-card shadow-card p-4 mb-4">
        <div class="flex items-start gap-3 justify-between">
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
            <div class="w-48">
                <label class="block text-xs text-gray-600 mb-1" for="clientSwitcher">Switch client</label>
                <select id="clientSwitcher" name="clientSwitcher" autocomplete="off" class="w-full border border-border rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="">Select client</option>
                </select>
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
                    <span class="text-xs text-muted">Linked to this client</span>
                </div>
                <div id="clientTasks" class="space-y-2 text-sm text-gray-700">
                    <div class="text-gray-500">Loading...</div>
                </div>
            </div>

            <div class="bg-white border border-border rounded-card shadow-card p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Related deals</h2>
                    <span class="text-xs text-muted">Linked to this client</span>
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

<!-- Quick Note Modal -->
<div id="noteModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-lg">
        <div class="bg-white rounded-card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Add Note for this client</h3>
                <button data-close-note class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-700" for="noteContent">Note</label>
                        <textarea id="noteContent" name="noteContent" class="w-full border border-border rounded px-3 py-2" rows="4" placeholder="Type your note here"></textarea>
                    </div>
                </div>
            <div class="mt-4 flex justify-end gap-2">
                <button data-close-note class="px-3 py-2 border border-border rounded text-sm">Cancel</button>
                <button id="noteSubmit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Save note</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Task Modal -->
<div id="taskModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-lg">
        <div class="bg-white rounded-card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Add Task for this client</h3>
                <button data-close-task class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-700" for="taskTitle">Title</label>
                        <input id="taskTitle" name="taskTitle" class="w-full border border-border rounded px-3 py-2" placeholder="Follow-up call" />
                    </div>
                    <div>
                        <label class="text-sm text-gray-700" for="taskDesc">Description</label>
                        <textarea id="taskDesc" name="taskDesc" class="w-full border border-border rounded px-3 py-2" rows="2" placeholder="Details (optional)"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm text-gray-700" for="taskDue">Due date</label>
                            <input id="taskDue" name="taskDue" type="date" class="w-full border border-border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="text-sm text-gray-700" for="taskStatus">Status</label>
                            <select id="taskStatus" name="taskStatus" class="w-full border border-border rounded px-3 py-2">
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
</div>

<!-- Quick Deal Modal -->
<div id="dealModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
    <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-lg">
        <div class="bg-white rounded-card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold">Add Deal for this client</h3>
                <button data-close-deal class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
                <div class="space-y-3">
                    <div>
                                <label class="text-sm text-gray-700" for="dealTitle">Subject</label>
                                <input id="dealTitle" name="dealTitle" class="w-full border border-border rounded px-3 py-2" placeholder="Deal subject" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                          <div>
                            <label class="text-sm text-gray-700" for="dealAmount">Amount</label>
                            <div class="flex">
                                <select id="dealCurrency" name="dealCurrency" class="border border-border rounded-l px-3 py-2 text-sm bg-gray-50">
                                    <option value="AED">AED</option>
                                    <option value="USD">USD</option>
                                </select>
                                <input id="dealAmount" name="dealAmount" type="text" inputmode="decimal" class="w-full border border-border border-l-0 rounded-r px-3 py-2" placeholder="e.g. 500,000" />
                            </div>
                          </div>
                        <div>
                            <label class="text-sm text-gray-700" for="dealStage">Status</label>
                            <select id="dealStage" name="dealStage" class="w-full border border-border rounded px-3 py-2">
                                <option value="ongoing" selected>Ongoing</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-700" for="dealClose">Close date</label>
                        <input id="dealClose" name="dealClose" type="date" class="w-full border border-border rounded px-3 py-2" />
                    </div>
                </div>
            <div class="mt-4 flex justify-end gap-2">
                <button data-close-deal class="px-3 py-2 border border-border rounded text-sm">Cancel</button>
                <button id="dealSubmit" class="px-4 py-2 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Save deal</button>
            </div>
        </div>
    </div>
</div>

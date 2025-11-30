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
                    <button id="clientFileAdd" class="px-3 py-1 border border-border rounded text-sm hover:bg-gray-100">Upload</button>
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

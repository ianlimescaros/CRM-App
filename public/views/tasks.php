<section data-page="tasks">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-semibold">Tasks</h1>
            <div class="flex border border-border rounded-card overflow-hidden text-sm">
                <button id="taskViewList" class="px-3 py-2 bg-accent text-white">List</button>
                <button id="taskViewCalendar" class="px-3 py-2 text-gray-700 hover:bg-gray-100">Calendar</button>
            </div>
        </div>
        <button id="taskAddBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Add Task</button>
    </div>
    <div class="flex gap-2 mb-4">
        <select id="taskStatusFilter" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
            <option value="">All statuses</option>
            <option value="pending">Pending</option>
            <option value="done">Done</option>
        </select>
        <input id="taskDueFilter" type="date" class="border border-border px-3 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-accent/30">
        <button id="taskFilterBtn" class="px-3 py-2 border border-border rounded text-sm hover:bg-gray-100 transition">Filter</button>
    </div>
    <div id="taskListWrap" class="bg-white border border-border rounded-card shadow-card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Title</th>
                    <th class="px-3 py-2 text-left">Due</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody id="tasksTableBody"></tbody>
        </table>
    </div>
    <div id="taskPagination" class="mt-2"></div>

    <div id="taskCalendarWrap" class="hidden bg-white border border-border rounded-card shadow-card p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="text-sm text-gray-600">Calendar view (by due date)</div>
            <div class="text-xs text-gray-500" id="taskCalendarMonth"></div>
        </div>
        <div id="taskCalendar" class="grid grid-cols-7 gap-2 text-xs text-gray-700"></div>
    </div>

    <div id="taskFormContainer" class="fixed inset-0 bg-black/50 hidden z-40 flex items-start justify-center overflow-y-auto p-4">
        <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-emerald-500 p-[1px] rounded-card shadow-2xl w-full max-w-3xl mt-10">
            <div class="bg-white rounded-card p-5">
        <h2 class="text-xl font-semibold mb-2" id="taskFormTitle">New Task</h2>
        <form id="taskForm" class="grid gap-3 sm:grid-cols-2">
            <div id="taskFormError" class="sm:col-span-2 text-sm text-red-600 hidden"></div>
            <input type="hidden" name="id">
            <div class="sm:col-span-2">
                <label class="block text-sm">Title</label>
                <input name="title" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" required>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm">Description</label>
                <textarea name="description" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30"></textarea>
            </div>
            <div>
                <label class="block text-sm">Due Date</label>
                <input name="due_date" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30" type="date">
            </div>
            <div>
                <label class="block text-sm">Status</label>
                <select name="status" class="w-full border border-border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-accent/30">
                    <option value="pending">Pending</option>
                    <option value="done">Done</option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Lead (optional)</label>
                <select name="lead_id" id="taskLeadSelect" class="w-full border border-border px-3 py-2 rounded">
                    <option value="">None</option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Contact (optional)</label>
                <select name="contact_id" id="taskContactSelect" class="w-full border border-border px-3 py-2 rounded">
                    <option value="">None</option>
                </select>
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition" type="submit">Save</button>
                <button class="px-4 py-2 border border-border rounded hover:bg-gray-100 transition" type="button" id="taskFormCancel">Cancel</button>
            </div>
        </form>
            </div>
        </div>
    </div>
</section>

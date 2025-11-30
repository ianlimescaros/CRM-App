<section data-page="dashboard">
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <div class="flex gap-2 text-sm">
            <a class="px-3 py-2 bg-blue-600 text-white rounded" href="/index.php?page=leads">+ New Lead</a>
            <a class="px-3 py-2 border rounded" href="/index.php?page=tasks">+ New Task</a>
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="p-4 rounded shadow-card bg-white border border-blue-100">
            <div class="flex items-center justify-between">
                <div class="text-sm text-blue-700 flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-blue-500"></span>Leads
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700">+0%</span>
            </div>
            <div id="statLeads" class="text-3xl font-bold text-blue-900 mt-2">0</div>
        </div>
        <div class="p-4 rounded shadow-card bg-white border border-emerald-100">
            <div class="flex items-center justify-between">
                <div class="text-sm text-emerald-700 flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Deals
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700">+0%</span>
            </div>
            <div id="statDeals" class="text-3xl font-bold text-emerald-900 mt-2">0</div>
        </div>
        <div class="p-4 rounded shadow-card bg-white border border-amber-100">
            <div class="flex items-center justify-between">
                <div class="text-sm text-amber-700 flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-500"></span>Tasks
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-700">+0%</span>
            </div>
            <div id="statTasks" class="text-3xl font-bold text-amber-900 mt-2">0</div>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <div class="bg-white border border-border rounded-card shadow-card p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Pipeline Overview</h2>
                <span class="text-xs text-muted">Bar chart</span>
            </div>
            <div class="h-48 bg-blue-50 border border-blue-100 rounded-lg p-3">
                <canvas id="pipelineChart"></canvas>
            </div>
        </div>
        <div class="bg-white border border-border rounded-card shadow-card p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Leads Over Time</h2>
                <span class="text-xs text-muted">Line chart</span>
            </div>
            <div class="h-48 bg-emerald-50 border border-emerald-100 rounded-lg p-3">
                <canvas id="leadsLineChart"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <div class="bg-white border rounded shadow-sm p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Recent Leads</h2>
                <a class="text-sm text-blue-600" href="/index.php?page=leads">View all</a>
            </div>
            <div id="recentLeads" class="space-y-2 text-sm text-gray-700"></div>
        </div>
        <div class="bg-white border rounded shadow-sm p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Upcoming Tasks</h2>
                <a class="text-sm text-blue-600" href="/index.php?page=tasks">View all</a>
            </div>
            <div id="upcomingTasks" class="space-y-2 text-sm text-gray-700"></div>
        </div>
    </div>
</section>

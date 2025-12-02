<section data-page="dashboard" class="relative">
    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 py-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <p class="text-xs uppercase tracking-[0.25em] text-slate-500">Overview</p>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="hidden sm:flex items-center bg-white rounded-full shadow-sm border border-slate-200 px-3 py-2">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 6.5 6.5a7.5 7.5 0 0 0 10.15 10.15Z" /></svg>
                    <input class="ml-2 text-sm focus:outline-none bg-transparent" placeholder="Search..." />
                </div>
                <a class="px-4 py-2 bg-indigo-600 text-white rounded-full shadow-sm hover:bg-indigo-700 transition text-sm" href="/index.php?page=leads">+ New Lead</a>
                <a class="px-4 py-2 border border-slate-200 rounded-full shadow-sm hover:bg-slate-50 transition text-sm" href="/index.php?page=tasks">+ New Task</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="bg-white rounded-2xl shadow-md border border-blue-50 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-blue-700 text-sm font-medium">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>Leads
                    </div>
                    <span id="statLeadsDelta" class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700">+0%</span>
                </div>
                <div id="statLeads" class="text-3xl font-bold text-slate-900 mt-2">0</div>
                <p class="text-xs text-slate-500">Week over week</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md border border-emerald-50 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-emerald-700 text-sm font-medium">
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Deals
                    </div>
                    <span id="statDealsDelta" class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700">+0%</span>
                </div>
                <div id="statDeals" class="text-3xl font-bold text-slate-900 mt-2">0</div>
                <p class="text-xs text-slate-500">Pipeline health</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md border border-amber-50 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-amber-700 text-sm font-medium">
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>Tasks
                    </div>
                    <span id="statTasksDelta" class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-700">+0%</span>
                </div>
                <div id="statTasks" class="text-3xl font-bold text-slate-900 mt-2">0</div>
                <p class="text-xs text-slate-500">Due & upcoming</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-4 lg:col-span-2">
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="font-semibold text-slate-900">Pipeline Overview</h2>
                            <span class="text-xs text-indigo-600">Bar chart</span>
                        </div>
                        <div class="h-48 rounded-lg bg-white border border-indigo-100 p-3">
                            <canvas id="pipelineChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="font-semibold text-slate-900">Leads Over Time</h2>
                            <span class="text-xs text-emerald-600">Line chart</span>
                        </div>
                        <div class="h-48 rounded-lg bg-white border border-emerald-100 p-3">
                            <canvas id="leadsLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-4">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="font-semibold text-slate-900">Upcoming Tasks</h2>
                    <a class="text-sm text-indigo-600" href="/index.php?page=tasks">View all</a>
                </div>
                <div id="upcomingTasks" class="space-y-3 text-sm text-slate-700"></div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-4 lg:col-span-2">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="font-semibold text-slate-900">Recent Leads</h2>
                    <a class="text-sm text-indigo-600" href="/index.php?page=leads">View all</a>
                </div>
                <div id="recentLeads" class="space-y-3 text-sm text-slate-700"></div>
            </div>
            <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-4">
                <div class="flex justify-between items-center mb-3">
                    <h2 class="font-semibold text-slate-900">Quick Stats</h2>
                </div>
                <div class="space-y-2 text-sm text-slate-700">
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2">
                        <span>Avg. tasks per lead</span><span class="font-semibold">—</span>
                    </div>
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2">
                        <span>Open deals</span><span class="font-semibold" id="statOpenDeals">—</span>
                    </div>
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2">
                        <span>Pending tasks</span><span class="font-semibold" id="statPendingTasks">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

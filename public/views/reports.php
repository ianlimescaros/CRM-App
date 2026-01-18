<!-- View template for the reports page. -->

<section data-page="reports" class="max-w-6xl">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Reports & Analytics</h1>
        <div class="text-sm text-gray-500">Sample charts (inline)</div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="bg-white border border-border rounded-card shadow-card p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Lead Status</h2>
                <span class="text-xs text-muted">Bar</span>
            </div>
            <div class="h-52 bg-blue-50 border border-blue-100 rounded-lg p-3">
                <canvas id="reportStatusChart"></canvas>
            </div>
        </div>
        <div class="bg-white border border-border rounded-card shadow-card p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Deal Stages</h2>
                <span class="text-xs text-muted">Bar</span>
            </div>
            <div class="h-52 bg-emerald-50 border border-emerald-100 rounded-lg p-3">
                <canvas id="reportStageChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2 mt-4">
        <div class="bg-white border border-border rounded-card shadow-card p-4">
            <div class="flex justify-between items-center mb-2">
                <h2 class="font-semibold">Leads by Property For</h2>
                <span id="reportLeadsRangeLabel" class="text-xs text-muted">Last 7d</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs mb-3">
                <label for="reportLeadsRange" class="text-gray-500">Range</label>
                <select id="reportLeadsRange" name="reportLeadsRange" class="border border-border rounded px-2 py-1 bg-white text-gray-700">
                    <option value="week">Week</option>
                    <option value="month">Month</option>
                    <option value="year">Year</option>
                    <option value="custom">Date</option>
                </select>
                <div id="reportLeadsRangeCustom" class="hidden flex items-center gap-2">
                    <input type="date" id="reportLeadsRangeFrom" name="reportLeadsRangeFrom" class="border border-border rounded px-2 py-1 bg-white text-gray-700">
                    <span class="text-gray-400">to</span>
                    <input type="date" id="reportLeadsRangeTo" name="reportLeadsRangeTo" class="border border-border rounded px-2 py-1 bg-white text-gray-700">
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-600 mb-3 overflow-x-auto whitespace-nowrap">
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>Sale/Buy</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Rent/Lease</span>
                <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>Off-Plan/Buyer</span>
            </div>
            <div class="h-52 bg-gray-50 border border-border rounded-lg p-3">
                <canvas id="reportLeadsLine" class="w-full h-full"></canvas>
            </div>
        </div>
        <div class="bg-white border border-border rounded-card shadow-card p-4">
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Tasks by Status</h2>
                <span class="text-xs text-muted">Bar</span>
            </div>
            <div class="h-52 bg-amber-50 border border-amber-100 rounded-lg p-3">
                <canvas id="reportTaskChart"></canvas>
            </div>
        </div>
    </div>
</section>

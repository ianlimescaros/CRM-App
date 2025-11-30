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
            <div class="flex justify-between items-center mb-3">
                <h2 class="font-semibold">Leads Created (Last 14d)</h2>
                <span class="text-xs text-muted">Line</span>
            </div>
            <div class="h-52 bg-gray-50 border border-border rounded-lg p-3">
                <canvas id="reportLeadsLine"></canvas>
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

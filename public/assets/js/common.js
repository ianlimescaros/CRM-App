// Shared frontend helpers used across modules
function escapeHtml(str) {
    if (typeof str !== 'string') return str;
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function relativeDate(dateStr) {
    if (!dateStr) return '';
    const target = new Date(dateStr);
    const now = new Date();
    const diffMs = target - now;
    const diffDays = Math.round(diffMs / (1000 * 60 * 60 * 24));
    if (diffDays === 0) return 'Due today';
    if (diffDays === 1) return 'Due tomorrow';
    if (diffDays === -1) return 'Due yesterday';
    return diffDays > 0 ? `Due in ${diffDays}d` : `${Math.abs(diffDays)}d ago`;
}

function countByStatus(items, field) {
    const counts = {};
    items.forEach(item => {
        const key = item[field] || 'unknown';
        counts[key] = (counts[key] || 0) + 1;
    });
    return counts;
}

function renderBars(counts) {
    const entries = Object.entries(counts);
    if (!entries.length) {
        return '<div class="text-sm text-blue-700 text-center py-6">No data</div>';
    }
    const max = Math.max(...entries.map(([, v]) => v));
    return `
        <div class="grid grid-cols-${entries.length} h-full items-end gap-2">
            ${entries.map(([label, value]) => {
                const height = max ? Math.max(6, (value / max) * 100) : 0;
                return `
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-full bg-white border border-blue-100 rounded-sm flex-1 flex items-end">
                            <div class="w-full bg-blue-500 rounded-sm" style="height:${height}%"></div>
                        </div>
                        <span class="text-[10px] text-blue-700 uppercase">${escapeHtml(label)}</span>
                        <span class="text-[10px] text-blue-700 font-semibold">${value}</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function aggregateByDate(items, field, days = 7) {
    const today = new Date();
    const series = [];
    for (let i = days - 1; i >= 0; i--) {
        const day = new Date(today);
        day.setDate(day.getDate() - i);
        const key = day.toISOString().slice(0, 10);
        const count = items.filter(item => (item[field] || '').slice(0, 10) === key).length;
        series.push({ date: key, value: count });
    }
    return series;
}

function renderSparkline(series) {
    if (!series.length) {
        return '<div class="text-sm text-emerald-700 text-center py-6">No data</div>';
    }
    const max = Math.max(...series.map(s => s.value), 1);
    return `
        <div class="flex items-end h-full gap-2">
            ${series.map((point) => {
                const height = (point.value / max) * 60;
                return `
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-3 bg-emerald-500 rounded-sm" style="height:${height}px"></div>
                        <span class="text-[10px] text-emerald-700">${point.value}</span>
                    </div>
                `;
            }).join('')}
        </div>
    `;
}

function renderBarChart(canvasEl, labels, data, label, colors = ['#2563EB']) {
    const ctx = canvasEl.getContext('2d');
    if (!ctx) return;
    if (canvasEl._chart) {
        canvasEl._chart.destroy();
    }
    canvasEl._chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label,
                data,
                backgroundColor: labels.map((_, i) => colors[i % colors.length]),
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#475569' }, grid: { display: false } },
                y: { ticks: { color: '#475569', precision: 0 }, grid: { color: '#E2E8F0' } }
            }
        }
    });
}

function renderLineChart(canvasEl, labels, data, label, color = '#2563EB') {
    const ctx = canvasEl.getContext('2d');
    if (!ctx) return;
    if (canvasEl._chart) {
        canvasEl._chart.destroy();
    }
    canvasEl._chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label,
                data,
                fill: true,
                backgroundColor: color.replace(')', ',0.1)').replace('rgb', 'rgba'),
                borderColor: color,
                tension: 0.3,
                pointRadius: 3,
                pointBackgroundColor: color,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: '#475569' }, grid: { display: false } },
                y: { ticks: { color: '#475569', precision: 0 }, grid: { color: '#E2E8F0' } }
            }
        }
    });
}

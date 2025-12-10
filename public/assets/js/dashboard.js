async function initDashboard() {
    try {
        const [leadRes, dealRes, taskRes] = await Promise.all([
            apiClient.listLeads(),
            apiClient.listDeals(),
            apiClient.listTasks(),
        ]);
        document.getElementById('statLeads').innerText = leadRes.leads.length;
        document.getElementById('statDeals').innerText = dealRes.deals.length;
        document.getElementById('statTasks').innerText = taskRes.tasks.length;

        // simple WoW delta: current 7 days vs previous 7 days
        setDelta('statLeadsDelta', leadRes.leads);
        setDelta('statDealsDelta', dealRes.deals);
        setDelta('statTasksDelta', taskRes.tasks);

        const recentLeads = leadRes.leads.slice(0, 5);
        document.getElementById('recentLeads').innerHTML = recentLeads.length
            ? recentLeads.map(l => `
                <div class="flex justify-between items-center border border-border rounded px-3 py-2 bg-white">
                    <div>
                        <div class="font-semibold">${escapeHtml(l.name)}</div>
                        <div class="text-xs text-gray-500">${escapeHtml(l.source || '-')}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-50 text-blue-700">${l.status}</span>
                </div>
            `).join('')
            : '<div class="text-sm text-gray-500">No leads yet.</div>';

        const upcoming = taskRes.tasks
            .filter(t => t.status === 'pending')
            .sort((a, b) => new Date(a.due_date || 0) - new Date(b.due_date || 0))
            .slice(0, 5);
        document.getElementById('upcomingTasks').innerHTML = upcoming.length
            ? upcoming.map(t => `
                <div class="flex justify-between items-center border border-border rounded px-3 py-2 bg-white">
                    <div>
                        <div class="font-semibold">${escapeHtml(t.title)}</div>
                        <div class="text-xs text-gray-500">${t.due_date ? relativeDate(t.due_date) : 'No due date'}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-amber-50 text-amber-700">${t.status}</span>
                </div>
            `).join('')
            : '<div class="text-sm text-gray-500">No upcoming tasks.</div>';

        // Simple bar chart for pipeline (by lead status)
        const statusCounts = countByStatus(leadRes.leads || [], 'status');
        const barCanvas = document.getElementById('pipelineChart');
        if (barCanvas && window.Chart) {
            renderBarChart(barCanvas, Object.keys(statusCounts), Object.values(statusCounts), 'Pipeline by status');
        } else if (barCanvas) {
            barCanvas.innerHTML = renderBars(statusCounts);
        }

        // Simple line/bar spark for leads over time
        const lineCanvas = document.getElementById('leadsLineChart');
        const series = aggregateByDate(leadRes.leads || [], 'created_at', 7);
        if (lineCanvas && window.Chart) {
            renderLineChart(lineCanvas, series.map(s => s.date.slice(5)), series.map(s => s.value), 'Leads over time');
        } else if (lineCanvas) {
            lineCanvas.innerHTML = renderSparkline(series);
        }
    } catch (err) {
        ui.showToast('Failed to load dashboard', 'error');
    }
}

function setDelta(elId, items) {
    const el = document.getElementById(elId);
    if (!el) return;
    const now = new Date();
    const currentStart = new Date(now);
    currentStart.setDate(now.getDate() - 7);
    const prevStart = new Date(now);
    prevStart.setDate(now.getDate() - 14);

    const current = items.filter(i => isInRange(i.created_at, currentStart, now)).length;
    const prev = items.filter(i => isInRange(i.created_at, prevStart, currentStart)).length;

    let delta = 0;
    if (prev === 0) {
        delta = current > 0 ? 100 : 0;
    } else {
        delta = Math.round(((current - prev) / prev) * 100);
    }
    const sign = delta > 0 ? '+' : '';
    el.textContent = `${sign}${delta}%`;
    el.classList.remove('text-blue-700', 'text-emerald-700', 'text-amber-700', 'text-red-700', 'bg-blue-50', 'bg-emerald-50', 'bg-amber-50', 'bg-red-50');
    let color = 'blue';
    if (elId.includes('Deals')) color = 'emerald';
    if (elId.includes('Tasks')) color = 'amber';
    if (delta < 0) color = 'red';
    el.classList.add(`text-${color}-700`, `bg-${color}-50`);
}

function isInRange(dateStr, start, end) {
    if (!dateStr) return false;
    const d = new Date(dateStr);
    return d >= start && d <= end;
}

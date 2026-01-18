// Dashboard UI rendering and charts.

function formatDateYmd(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function parseInputDate(value) {
    if (!value) return null;
    const parts = value.split('-').map(Number);
    if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
    return new Date(parts[0], parts[1] - 1, parts[2]);
}

function countByDate(items, field) {
    const counts = {};
    items.forEach(item => {
        const key = String(item[field] || '').slice(0, 10);
        if (!key) return;
        counts[key] = (counts[key] || 0) + 1;
    });
    return counts;
}

function normalizePropertyFor(value) {
    if (!value) return null;
    const key = String(value).toLowerCase().replace(/\s+/g, '');
    if (key === 'sale/buy') return 'Sale/Buy';
    if (key === 'rent/lease') return 'Rent/Lease';
    if (key === 'off-plan/buyer' || key === 'offplan/buyer' || key === 'offplanbuyer') return 'Off-Plan/Buyer';
    return null;
}

function countByDateAndCategory(items, dateField, categoryField, categories) {
    const counts = {};
    categories.forEach(category => {
        counts[category] = {};
    });
    items.forEach(item => {
        const dateKey = String(item[dateField] || '').slice(0, 10);
        if (!dateKey) return;
        const normalized = normalizePropertyFor(item[categoryField]);
        if (!normalized || !counts[normalized]) return;
        counts[normalized][dateKey] = (counts[normalized][dateKey] || 0) + 1;
    });
    return counts;
}

function resolveLeadRange(rangeKey, fromValue, toValue) {
    const today = new Date();
    let rangeEnd = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    let rangeStart = null;
    let label = 'Last 7d';

    if (rangeKey === 'month') {
        rangeStart = new Date(rangeEnd.getFullYear(), rangeEnd.getMonth(), 1);
        label = 'This month';
    } else if (rangeKey === 'year') {
        rangeStart = new Date(rangeEnd.getFullYear(), 0, 1);
        label = 'This year';
    } else if (rangeKey === 'custom') {
        const customStart = parseInputDate(fromValue);
        const customEnd = parseInputDate(toValue);
        if (customStart || customEnd) {
            rangeStart = customStart || customEnd;
            rangeEnd = customEnd || customStart;
            label = `${formatDateYmd(rangeStart)} to ${formatDateYmd(rangeEnd)}`;
        }
    }

    if (!rangeStart) {
        rangeStart = new Date(rangeEnd);
        rangeStart.setDate(rangeStart.getDate() - 6);
    }

    if (rangeStart > rangeEnd) {
        const tmp = rangeStart;
        rangeStart = rangeEnd;
        rangeEnd = tmp;
    }

    if (rangeKey === 'custom') {
        label = `${formatDateYmd(rangeStart)} to ${formatDateYmd(rangeEnd)}`;
    }

    return { start: rangeStart, end: rangeEnd, label, rangeKey };
}

function buildSeriesFromCounts(counts, start, end) {
    const series = [];
    const cursor = new Date(start.getFullYear(), start.getMonth(), start.getDate());
    const last = new Date(end.getFullYear(), end.getMonth(), end.getDate());
    while (cursor <= last) {
        const key = formatDateYmd(cursor);
        series.push({ date: key, value: counts[key] || 0 });
        cursor.setDate(cursor.getDate() + 1);
    }
    return series;
}

async function fetchAllLeads(filters = {}) {
    const perPage = 50;
    let page = 1;
    let total = 0;
    let leads = [];

    while (true) {
        const res = await apiClient.listLeads({ ...filters, page, per_page: perPage });
        const batch = res.leads || [];
        leads = leads.concat(batch);
        total = Number(res.meta?.total ?? leads.length);
        if (!batch.length || leads.length >= total) break;
        page += 1;
    }

    return { leads, total };
}

async function initDashboard() {
    try {
        const leadFilters = {
            created_from: formatDateYmd(new Date(0)),
            created_to: formatDateYmd(new Date()),
            sort: 'created_at',
            direction: 'DESC',
        };
        const [leadRes, dealRes, taskRes] = await Promise.all([
            fetchAllLeads(leadFilters),
            apiClient.listDeals(),
            apiClient.listTasks(),
        ]);
        const allLeads = leadRes.leads || [];
        document.getElementById('statLeads').innerText = leadRes.total ?? allLeads.length;
        document.getElementById('statDeals').innerText = dealRes.deals.length;
        document.getElementById('statTasks').innerText = taskRes.tasks.length;

        // simple WoW delta: current 7 days vs previous 7 days
        setDelta('statLeadsDelta', allLeads);
        setDelta('statDealsDelta', dealRes.deals);
        setDelta('statTasksDelta', taskRes.tasks);

        const recentLeads = allLeads.slice(0, 5);
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
        const statusCounts = countByStatus(allLeads, 'status');
        const barCanvas = document.getElementById('pipelineChart');
        if (barCanvas && window.Chart) {
            renderBarChart(barCanvas, Object.keys(statusCounts), Object.values(statusCounts), 'Pipeline by status');
        } else if (barCanvas) {
            barCanvas.innerHTML = renderBars(statusCounts);
        }

        const lineCanvas = document.getElementById('leadsLineChart');
        if (lineCanvas) {
            const propertyForConfig = [
                { key: 'Sale/Buy', label: 'Sale/Buy', color: '#2563EB' },
                { key: 'Rent/Lease', label: 'Rent/Lease', color: '#10B981' },
                { key: 'Off-Plan/Buyer', label: 'Off-Plan/Buyer', color: '#F59E0B' },
            ];
            const countsByCategory = countByDateAndCategory(
                allLeads,
                'created_at',
                'property_for',
                propertyForConfig.map(item => item.key)
            );
            const totalCounts = countByDate(allLeads, 'created_at');
            const rangeSelect = document.getElementById('dashboardLeadsRange');
            const rangeCustom = document.getElementById('dashboardLeadsRangeCustom');
            const rangeFrom = document.getElementById('dashboardLeadsRangeFrom');
            const rangeTo = document.getElementById('dashboardLeadsRangeTo');
            const rangeLabel = document.getElementById('dashboardLeadsRangeLabel');

            const updateLine = () => {
                const rangeKey = rangeSelect?.value || 'week';
                const { start, end, label, rangeKey: resolvedKey } = resolveLeadRange(rangeKey, rangeFrom?.value, rangeTo?.value);
                if (rangeCustom) rangeCustom.classList.toggle('hidden', resolvedKey !== 'custom');
                if (resolvedKey === 'custom') {
                    if (rangeFrom && !rangeFrom.value) rangeFrom.value = formatDateYmd(start);
                    if (rangeTo && !rangeTo.value) rangeTo.value = formatDateYmd(end);
                }
                if (rangeLabel) rangeLabel.textContent = label;
                const dateSeries = buildSeriesFromCounts({}, start, end);
                const labels = dateSeries.map(s => s.date.slice(5));
                const datasets = propertyForConfig.map(config => {
                    const series = buildSeriesFromCounts(countsByCategory[config.key] || {}, start, end);
                    return {
                        label: config.label,
                        data: series.map(s => s.value),
                        borderColor: config.color,
                    };
                });
                if (window.Chart) {
                    renderMultiLineChart(lineCanvas, labels, datasets);
                } else {
                    const totalSeries = buildSeriesFromCounts(totalCounts, start, end);
                    lineCanvas.innerHTML = renderSparkline(totalSeries);
                }
            };

            rangeSelect?.addEventListener('change', updateLine);
            rangeFrom?.addEventListener('change', updateLine);
            rangeTo?.addEventListener('change', updateLine);
            updateLine();
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

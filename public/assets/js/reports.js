// Reports page UI logic.

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

function initReports() {
    const statusChart = document.getElementById('reportStatusChart');
    const stageChart = document.getElementById('reportStageChart');
    const leadsLine = document.getElementById('reportLeadsLine');
    const taskChart = document.getElementById('reportTaskChart');

    Promise.all([fetchAllLeads({ sort: 'created_at', direction: 'DESC' }), apiClient.listDeals(), apiClient.listTasks()])
        .then(([leadRes, dealRes, taskRes]) => {
            const allLeads = leadRes.leads || [];
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
            if (statusChart) {
                const counts = countByStatus(allLeads, 'status');
                if (window.Chart) {
                    renderBarChart(statusChart, Object.keys(counts), Object.values(counts), 'Lead Status', ['#2563EB', '#10B981', '#F59E0B', '#EF4444', '#7C3AED']);
                } else {
                    statusChart.innerHTML = renderBars(counts);
                }
            }
            if (stageChart) {
                const counts = countByStatus(dealRes.deals || [], 'stage');
                if (window.Chart) {
                    renderBarChart(stageChart, Object.keys(counts), Object.values(counts), 'Deal Stages', ['#2563EB', '#0EA5E9', '#F59E0B', '#16A34A', '#DC2626']);
                } else {
                    stageChart.innerHTML = renderBars(counts);
                }
            }
            if (taskChart) {
                const counts = countByStatus(taskRes.tasks || [], 'status');
                if (window.Chart) {
                    renderBarChart(taskChart, Object.keys(counts), Object.values(counts), 'Tasks Status', ['#F59E0B', '#10B981']);
                } else {
                    taskChart.innerHTML = renderBars(counts);
                }
            }
            if (leadsLine) {
                const rangeSelect = document.getElementById('reportLeadsRange');
                const rangeCustom = document.getElementById('reportLeadsRangeCustom');
                const rangeFrom = document.getElementById('reportLeadsRangeFrom');
                const rangeTo = document.getElementById('reportLeadsRangeTo');
                const rangeLabel = document.getElementById('reportLeadsRangeLabel');

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
                        renderMultiLineChart(leadsLine, labels, datasets);
                    } else {
                        const totalSeries = buildSeriesFromCounts(totalCounts, start, end);
                        leadsLine.innerHTML = renderSparkline(totalSeries);
                    }
                };

                rangeSelect?.addEventListener('change', updateLine);
                rangeFrom?.addEventListener('change', updateLine);
                rangeTo?.addEventListener('change', updateLine);
                updateLine();
            }
        })
        .catch(() => ui.showToast('Failed to load reports', 'error'));
}

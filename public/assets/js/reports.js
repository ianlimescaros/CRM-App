function initReports() {
    const statusChart = document.getElementById('reportStatusChart');
    const stageChart = document.getElementById('reportStageChart');
    const leadsLine = document.getElementById('reportLeadsLine');
    const taskChart = document.getElementById('reportTaskChart');

    Promise.all([apiClient.listLeads(), apiClient.listDeals(), apiClient.listTasks()])
        .then(([leadRes, dealRes, taskRes]) => {
            if (statusChart) {
                const counts = countByStatus(leadRes.leads || [], 'status');
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
                const series = aggregateByDate(leadRes.leads || [], 'created_at', 14);
                if (window.Chart) {
                    renderLineChart(leadsLine, series.map(s => s.date.slice(5)), series.map(s => s.value), 'Leads Created', '#2563EB');
                } else {
                    leadsLine.innerHTML = renderSparkline(series);
                }
            }
        })
        .catch(() => ui.showToast('Failed to load reports', 'error'));
}

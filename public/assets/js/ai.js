// AI assistant UI behavior and API calls.

function initAiAssistant() {
    const summarizeForm = document.getElementById('aiSummarizeForm');
    const followupForm = document.getElementById('aiFollowupForm');
    const summaryResult = document.getElementById('aiSummaryResult');
    const followupResult = document.getElementById('aiFollowupResult');
    const summarizeBtn = summarizeForm?.querySelector('button[type="submit"]');
    const followupBtn = followupForm?.querySelector('button[type="submit"]');
    const summaryStatus = document.getElementById('aiSummaryStatus');
    const followupStatus = document.getElementById('aiFollowupStatus');
    const summaryCopy = document.getElementById('aiSummaryCopy');
    const followupCopy = document.getElementById('aiFollowupCopy');
    const summaryClear = document.getElementById('aiSummaryClear');
    const followupClear = document.getElementById('aiFollowupClear');

    // Backend AI endpoints may not be ready; guard calls
    summarizeForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const notes = new FormData(summarizeForm).get('notes');
        if (summarizeBtn) summarizeBtn.disabled = true;
        summaryStatus.innerHTML = '<span class="inline-block h-3 w-3 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></span><span>Generating...</span>';
        summaryResult.textContent = '';
        try {
            const res = await fetch('/api.php/ai/summarize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${apiClient.getToken() || ''}`,
                },
                body: JSON.stringify({ notes }),
            });
            const data = await res.json();
            summaryResult.textContent = data.summary || data.message || 'No result';
        } catch (err) {
            ui.showToast('AI summarize failed', 'error');
            summaryResult.textContent = 'Error generating summary';
        } finally {
            if (summarizeBtn) summarizeBtn.disabled = false;
            if (summaryStatus) summaryStatus.textContent = '';
        }
    });

    followupForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(followupForm).entries());
        if (followupBtn) followupBtn.disabled = true;
        if (followupStatus) followupStatus.innerHTML = '<span class="inline-block h-3 w-3 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></span><span>Generating...</span>';
        followupResult.textContent = '';
        try {
            const res = await fetch('/api.php/ai/suggest-followup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${apiClient.getToken() || ''}`,
                },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            followupResult.textContent = data.message || 'No result';
        } catch (err) {
            ui.showToast('AI follow-up failed', 'error');
            followupResult.textContent = 'Error generating follow-up';
        } finally {
            if (followupBtn) followupBtn.disabled = false;
            if (followupStatus) followupStatus.textContent = '';
        }
    });

    summaryCopy?.addEventListener('click', () => {
        if (!summaryResult.textContent) return;
        navigator.clipboard.writeText(summaryResult.textContent);
        ui.showToast('Summary copied', 'success');
    });
    followupCopy?.addEventListener('click', () => {
        if (!followupResult.textContent) return;
        navigator.clipboard.writeText(followupResult.textContent);
        ui.showToast('Follow-up copied', 'success');
    });

    summaryClear?.addEventListener('click', () => {
        summarizeForm?.reset();
        summaryResult.textContent = '';
        if (summaryStatus) summaryStatus.textContent = '';
    });
    followupClear?.addEventListener('click', () => {
        followupForm?.reset();
        followupResult.textContent = '';
        if (followupStatus) followupStatus.textContent = '';
    });
}

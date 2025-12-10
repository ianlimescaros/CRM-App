document.addEventListener('DOMContentLoaded', async () => {
    const pageEl = document.querySelector('[data-page]');
    const page = pageEl ? pageEl.dataset.page : null;
    let token = apiClient.getToken();
    let tokenValid = false;

    // Validate stored token; if invalid, clear it and stay on/login page.
    if (token) {
        try {
            await apiClient.me();
            tokenValid = true;
        } catch (_) {
            apiClient.setToken(null);
            localStorage.removeItem('crm_user_email');
            token = null;
        }
    }

    // Simple auth gate on frontend routing (uses validated token state)
    if (!tokenValid && page !== 'login') {
        window.location = '/index.php?page=login';
        return;
    }
    if (tokenValid && page === 'login') {
        window.location = '/index.php?page=dashboard';
        return;
    }

    const logoutBtn = document.getElementById('logoutBtn');
    const logoutBtnTop = document.getElementById('logoutBtnTop');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebar = document.getElementById('sidebar');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const userEmailText = document.getElementById('userEmailText');

    const handleLogout = async () => {
        const ok = await ui.confirmModal('Do you want to log out?');
        if (!ok) return;
        try {
            await apiClient.logout();
        } catch (_) {
            // ignore logout failures
        } finally {
            apiClient.setToken(null);
            localStorage.removeItem('crm_user_email');
            ui.showToast('Logged out', 'success');
            window.location = '/index.php?page=login';
        }
    };

    function toggleSidebar(show) {
        if (!sidebar || !mobileOverlay) return;
        const shouldShow = typeof show === 'boolean' ? show : sidebar.classList.contains('hidden');
        sidebar.classList.toggle('hidden', !shouldShow);
        mobileOverlay.classList.toggle('hidden', !shouldShow);
        if (shouldShow) {
            sidebar.classList.add('fixed', 'z-30', 'top-0', 'left-0', 'h-full');
        } else {
            sidebar.classList.remove('fixed', 'z-30', 'top-0', 'left-0', 'h-full');
        }
    }

    mobileMenuBtn?.addEventListener('click', () => toggleSidebar(true));
    mobileOverlay?.addEventListener('click', () => toggleSidebar(false));
    document.querySelectorAll('a[data-nav]').forEach((link) => {
        link.addEventListener('click', () => toggleSidebar(false));
    });

    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
    if (logoutBtnTop) {
        logoutBtnTop.addEventListener('click', handleLogout);
    }

    // Display stored user email if available
    const storedEmail = localStorage.getItem('crm_user_email');
    if (storedEmail && userEmailText) {
        userEmailText.textContent = storedEmail;
        userEmailText.classList.remove('hidden');
    }
    const avatarInitials = document.getElementById('userAvatarInitials');
    if (storedEmail && avatarInitials) {
        const parts = storedEmail.split('@')[0];
        const initials = parts.slice(0, 2).toUpperCase();
        avatarInitials.textContent = initials;
    }

    switch (page) {
        case 'login':
            if (typeof initLogin === 'function') {
                initLogin();
            }
            break;
        case 'dashboard':
            if (typeof initDashboard === 'function') {
                initDashboard();
            }
            break;
        case 'leads':
            if (typeof initLeads === 'function') {
                initLeads();
            }
            break;
        case 'contacts':
            if (typeof initClients === 'function') {
                initClients();
            }
            break;
        case 'deals':
            if (typeof initDeals === 'function') {
                initDeals();
            }
            break;
        case 'tasks':
            if (typeof initTasks === 'function') {
                initTasks();
            }
            break;
        case 'ai-assistant':
            if (typeof initAiAssistant === 'function') {
                initAiAssistant();
            }
            break;
        case 'reports':
            if (typeof initReports === 'function') {
                initReports();
            }
            break;
        case 'client-profile':
            if (typeof initClientProfile === 'function') {
                initClientProfile();
            }
            break;
        case 'profile':
            if (typeof initProfile === 'function') {
                initProfile();
            }
            break;
        default:
            break;
    }
});


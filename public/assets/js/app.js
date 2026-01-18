// App bootstrap and per-page initializer.





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
            // Clear cookie token (index.php uses this)
            document.cookie = 'auth_token=; Max-Age=0; path=/; SameSite=Lax';

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

    // Sidebar collapse handling
    const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
    function applySidebarState() {
        const collapsed = localStorage.getItem('crm_sidebar_collapsed') === '1';
        document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
    }
    applySidebarState();
    sidebarCollapseBtn?.addEventListener('click', () => {
        const collapsed = localStorage.getItem('crm_sidebar_collapsed') === '1';
        localStorage.setItem('crm_sidebar_collapsed', collapsed ? '0' : '1');
        applySidebarState();
    });

    // Global new-item shortcut: Ctrl/Cmd+N opens add on leads/contacts pages
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'n') {
            const tag = (e.target && e.target.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || e.target.isContentEditable) return;
            if (page === 'leads') {
                e.preventDefault();
                const btn = document.getElementById('leadAddBtn');
                if (btn) btn.click();
            } else if (page === 'contacts') {
                e.preventDefault();
                const btn = document.getElementById('contactAddBtn');
                if (btn) btn.click();
            }
        }
    });

    // Command palette / keyboard niceties handled in globalSearch.js (Ctrl/Cmd+K)

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
        case 'tenancy-contracts':
            // 🔹 This is the important new bit
            if (typeof initTenancyContracts === 'function') {
                initTenancyContracts();
            }
            break;
        default:
            break;
    }
});

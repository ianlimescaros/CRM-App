// public/assets/js/apiClient.js

const API_BASE = window.location.origin + '/api.php';
const TOKEN_KEY = 'crm_token';
const COOKIE_NAME = 'auth_token';

// --------------------
// Cookie helpers
// --------------------
function getCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[$()*+.?[\\\]^{|}-]/g, '\\$&') + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
}

function setCookie(name, value) {
    // If your site is HTTPS, you can add: + '; Secure'
    document.cookie = `${name}=${encodeURIComponent(value)}; path=/; SameSite=Lax`;
}

function clearCookie(name) {
    document.cookie = `${name}=; Max-Age=0; path=/; SameSite=Lax`;
}

// --------------------
// Token storage (localStorage + cookie)
// --------------------
function getToken() {
    // Prefer localStorage (existing behavior), fallback to cookie (for PHP guard)
    return localStorage.getItem(TOKEN_KEY) || getCookie(COOKIE_NAME);
}

function setToken(token) {
    if (token) {
        // Store in localStorage for API Authorization header usage only.
        // Cookie is now set server-side (HttpOnly) on login/register responses.
        localStorage.setItem(TOKEN_KEY, token);
        // Set refresh timer: refresh token 5 minutes before expiry (typical JWT has 1hour expiry)
        scheduleTokenRefresh();
    } else {
        localStorage.removeItem(TOKEN_KEY);
        clearCookie(COOKIE_NAME);
    }
}

// Auto-refresh token before expiry
let refreshTimer;
function scheduleTokenRefresh() {
    clearTimeout(refreshTimer);
    // Refresh token every 55 minutes (assuming 1 hour expiry)
    refreshTimer = setTimeout(() => {
        refreshToken().catch(err => {
            console.warn('Token refresh failed:', err);
            // Redirect to login on refresh failure
            if (document.querySelector('[data-page]')?.dataset.page !== 'login') {
                window.location = '/index.php?page=login';
            }
        });
    }, 55 * 60 * 1000);
}

async function refreshToken() {
    try {
        const data = await request('/auth/me', { method: 'GET' });
        // If /auth/me succeeds, token is still valid
        scheduleTokenRefresh(); // reschedule
        return data;
    } catch (err) {
        // Token is invalid/expired
        setToken(null);
        throw err;
    }
}

// --------------------
// Request helper with automatic retry on network failures
function buildRequestUrl(path, method) {
    // Ensure path starts with a / if it doesn't have one
    const cleanPath = path.startsWith('/') ? path : '/' + path;
    const apiUrl = new URL(`${API_BASE}${cleanPath}`, window.location.origin);
    
    if (String(method).toUpperCase() === 'GET') {
        apiUrl.searchParams.set('t', String(Date.now()));
    }
    return apiUrl.toString();
}

async function request(path, { method = 'GET', body = null, retries = 3 } = {}) {
    const headers = { 'Content-Type': 'application/json' };
    const token = getToken();

    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    // For mutating requests from browsers, send CSRF token header if available
    if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(method.toUpperCase())) {
        const csrf = getCookie('csrf_token');
        if (csrf) headers['X-CSRF-Token'] = csrf;
    }

    // Retry logic with exponential backoff
    let lastError;
    for (let attempt = 0; attempt < retries; attempt++) {
        try {
            const res = await fetch(buildRequestUrl(path, method), {
                method,
                headers,
                body: body ? JSON.stringify(body) : null,
                cache: 'no-store',
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok || data.success === false) {
                const message = data.message || 'Request failed';
                throw { status: res.status, message, errors: data.errors || {} };
            }
            return data;
        } catch (err) {
            lastError = err;
            // Only retry on network errors, not on auth/validation errors
            const isNetworkError = !err.status || err.status >= 500;
            const shouldRetry = attempt < retries - 1 && isNetworkError;
            
            if (!shouldRetry) {
                throw err;
            }
            
            // Exponential backoff: 1s, 2s, 4s
            const waitMs = 1000 * Math.pow(2, attempt);
            await new Promise(r => setTimeout(r, waitMs));
        }
    }
    
    throw lastError;
}

// --------------------
// API client
// --------------------
const apiClient = {
    getToken,
    setToken,

    login: async (email, password) => {
        const data = await request('/auth/login', { method: 'POST', body: { email, password } });
        if (data?.token) setToken(data.token); // auto-sync token + cookie
        return data;
    },

    register: async (payload) => {
        const data = await request('/auth/register', { method: 'POST', body: payload } );
        if (data?.token) setToken(data.token); // auto-sync token + cookie
        return data;
    },

    logout: async () => {
        // best-effort server logout, then clear token + cookie locally no matter what
        try {
            await request('/auth/logout', { method: 'POST' });
        } finally {
            setToken(null);
        }
        return { success: true };
    },

    forgotPassword: (email) => request('/auth/forgot', { method: 'POST', body: { email } }),
    resetPassword: (email, token, password) => request('/auth/reset', { method: 'POST', body: { email, token, password } }),
    me: () => request('/auth/me'),
    updateProfile: (payload) => request('/auth/profile', { method: 'PUT', body: payload }),
    // Create a server-side PHP session for page-level checks
    createSession: () => request('/auth/session', { method: 'POST' }),

    listLeads: (filters = {}) => {
        const params = new URLSearchParams(filters);
        const qs = params.toString() ? `?${params.toString()}` : '';
        return request(`/leads${qs}`);
    },

    // Global search across clients and leads
    search: (q) => {
        const qs = q ? `?q=${encodeURIComponent(q)}` : '';
        return request(`/search${qs}`);
    },
    createLead: (payload) => request('/leads', { method: 'POST', body: payload }),
    updateLead: (id, payload) => request(`/leads/${id}`, { method: 'PUT', body: payload }),
    deleteLead: (id) => request(`/leads/${id}`, { method: 'DELETE' }),
    archiveLead: (id) => request(`/leads/${id}/archive`, { method: 'POST' }),
    restoreLead: (id) => request(`/leads/${id}/restore`, { method: 'POST' }),
    bulkUpdateLeads: (ids, status) => request('/leads/bulk', { method: 'PATCH', body: { ids, status } }),
    bulkArchiveLeads: (ids) => request('/leads/bulk/archive', { method: 'POST', body: { ids } }),
    bulkRestoreLeads: (ids) => request('/leads/bulk/restore', { method: 'POST', body: { ids } }),

    listClients: (filters = {}) => {
        const params = new URLSearchParams(filters);
        const qs = params.toString() ? `?${params.toString()}` : '';
        return request(`/clients${qs}`);
    },
    getClient: (id) => request(`/clients/${id}`),
    getClientTimeline: (id, params = {}) => {
        const qs = new URLSearchParams(params);
        const suffix = qs.toString() ? `?${qs.toString()}` : '';
        return request(`/clients/${id}/timeline${suffix}`);
    },
    getClientFiles: (id) => request(`/clients/${id}/files`),
    addClientFile: (id, payload) => request(`/clients/${id}/files`, { method: 'POST', body: payload }),
    getClientNotes: (id) => request(`/clients/${id}/notes`),
    addClientNote: (id, content) => request(`/clients/${id}/notes`, { method: 'POST', body: { content } }),
    createClient: (payload) => request('/clients', { method: 'POST', body: payload }),
    updateClient: (id, payload) => request(`/clients/${id}`, { method: 'PUT', body: payload }),
    deleteClient: (id) => request(`/clients/${id}`, { method: 'DELETE' }),

    listDeals: (filters = {}) => {
        const params = new URLSearchParams(filters);
        const qs = params.toString() ? `?${params.toString()}` : '';
        return request(`/deals${qs}`);
    },
    getDealFiles: (id) => request(`/deals/${id}/files`),
    addDealFile: (id, formData) => {
        const token = getToken();
        const headers = {};
        if (token) headers['Authorization'] = `Bearer ${token}`;
        const csrf = getCookie('csrf_token');
        if (csrf) headers['X-CSRF-Token'] = csrf;
        return fetch(`${API_BASE}/deals/${id}/files`, {
            method: 'POST',
            headers,
            body: formData,
        }).then(async (res) => {
            const data = await res.json().catch(() => ({}));
            if (!res.ok || data.success === false) {
                const message = data.message || 'Request failed';
                throw { status: res.status, message, errors: data.errors || {} };
            }
            return data;
        });
    },
    deleteDealFile: (id, fileId) => request(`/deals/${id}/files`, { method: 'DELETE', body: { file_id: Number(fileId) } }),
    createDeal: (payload) => request('/deals', { method: 'POST', body: payload }),
    updateDeal: (id, payload) => request(`/deals/${id}`, { method: 'PUT', body: payload }),
    deleteDeal: (id) => request(`/deals/${id}`, { method: 'DELETE' }),

    listTasks: (filters = {}) => {
        const params = new URLSearchParams(filters);
        const qs = params.toString() ? `?${params.toString()}` : '';
        return request(`/tasks${qs}`);
    },
    createTask: (payload) => request('/tasks', { method: 'POST', body: payload }),
    updateTask: (id, payload) => request(`/tasks/${id}`, { method: 'PUT', body: payload }),
    deleteTask: (id) => request(`/tasks/${id}`, { method: 'DELETE' }),
};

// Legacy aliases for backwards compatibility
apiClient.listContacts = (...args) => apiClient.listClients(...args);
apiClient.getContact = (...args) => apiClient.getClient(...args);
apiClient.getContactTimeline = (...args) => apiClient.getClientTimeline(...args);
apiClient.getContactFiles = (...args) => apiClient.getClientFiles(...args);
apiClient.addContactFile = (...args) => apiClient.addClientFile(...args);
apiClient.getContactNotes = (...args) => apiClient.getClientNotes(...args);
apiClient.addContactNote = (...args) => apiClient.addClientNote(...args);
apiClient.createContact = (...args) => apiClient.createClient(...args);
apiClient.updateContact = (...args) => apiClient.updateClient(...args);
apiClient.deleteContact = (...args) => apiClient.deleteClient(...args);

window.apiClient = apiClient;

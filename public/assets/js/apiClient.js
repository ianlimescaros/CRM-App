const API_BASE = '/api.php';
const TOKEN_KEY = 'crm_token';

function getToken() {
    return localStorage.getItem(TOKEN_KEY);
}

function setToken(token) {
    if (token) {
        localStorage.setItem(TOKEN_KEY, token);
    } else {
        localStorage.removeItem(TOKEN_KEY);
    }
}

async function request(path, { method = 'GET', body = null } = {}) {
    const headers = { 'Content-Type': 'application/json' };
    const token = getToken();
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const res = await fetch(`${API_BASE}${path}`, {
        method,
        headers,
        body: body ? JSON.stringify(body) : null,
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok || data.success === false) {
        const message = data.message || 'Request failed';
        throw { status: res.status, message, errors: data.errors || {} };
    }
    return data;
}

const apiClient = {
    getToken,
    setToken,
    login: (email, password) => request('/auth/login', { method: 'POST', body: { email, password } }),
    register: (payload) => request('/auth/register', { method: 'POST', body: payload }),
    logout: () => request('/auth/logout', { method: 'POST' }),

    listLeads: (filters = {}) => {
        const params = new URLSearchParams(filters);
        const qs = params.toString() ? `?${params.toString()}` : '';
        return request(`/leads${qs}`);
    },
    createLead: (payload) => request('/leads', { method: 'POST', body: payload }),
    updateLead: (id, payload) => request(`/leads/${id}`, { method: 'PUT', body: payload }),
    deleteLead: (id) => request(`/leads/${id}`, { method: 'DELETE' }),
    bulkUpdateLeads: (ids, status) => request('/leads/bulk', { method: 'PATCH', body: { ids, status } }),

    listContacts: () => request('/contacts'),
    createContact: (payload) => request('/contacts', { method: 'POST', body: payload }),
    updateContact: (id, payload) => request(`/contacts/${id}`, { method: 'PUT', body: payload }),
    deleteContact: (id) => request(`/contacts/${id}`, { method: 'DELETE' }),

    listDeals: (filters = {}) => {
        const params = new URLSearchParams(filters);
        const qs = params.toString() ? `?${params.toString()}` : '';
        return request(`/deals${qs}`);
    },
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

window.apiClient = apiClient;

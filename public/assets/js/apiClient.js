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
    forgotPassword: (email) => request('/auth/forgot', { method: 'POST', body: { email } }),
    resetPassword: (token, password) => request('/auth/reset', { method: 'POST', body: { token, password } }),
    me: () => request('/auth/me'),
    updateProfile: (payload) => request('/auth/profile', { method: 'PUT', body: payload }),

    listLeads: (filters = {}) => {
        const params = new URLSearchParams(filters);
        const qs = params.toString() ? `?${params.toString()}` : '';
        return request(`/leads${qs}`);
    },
    createLead: (payload) => request('/leads', { method: 'POST', body: payload }),
    updateLead: (id, payload) => request(`/leads/${id}`, { method: 'PUT', body: payload }),
    deleteLead: (id) => request(`/leads/${id}`, { method: 'DELETE' }),
    bulkUpdateLeads: (ids, status) => request('/leads/bulk', { method: 'PATCH', body: { ids, status } }),

    listContacts: (filters = {}) => {
        const params = new URLSearchParams(filters);
        const qs = params.toString() ? `?${params.toString()}` : '';
        return request(`/contacts${qs}`);
    },
    getContact: (id) => request(`/contacts/${id}`),
    getContactTimeline: (id) => request(`/contacts/${id}/timeline`),
    getContactFiles: (id) => request(`/contacts/${id}/files`),
    addContactFile: (id, payload) => request(`/contacts/${id}/files`, { method: 'POST', body: payload }),
    getContactNotes: (id) => request(`/contacts/${id}/notes`),
    addContactNote: (id, content) => request(`/contacts/${id}/notes`, { method: 'POST', body: { content } }),
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

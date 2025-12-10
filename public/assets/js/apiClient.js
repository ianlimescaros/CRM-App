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
    resetPassword: (email, token, password) => request('/auth/reset', { method: 'POST', body: { email, token, password } }),
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
    getClientFiles: (id) => {
        const token = getToken();
        const suffix = token ? `?token=${encodeURIComponent(token)}` : '';
        return request(`/clients/${id}/files${suffix}`);
    },
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
    getDealFiles: (id) => {
        const token = getToken();
        const suffix = token ? `?token=${encodeURIComponent(token)}` : '';
        return request(`/deals/${id}/files${suffix}`);
    },
    addDealFile: (id, formData) => {
        const token = getToken();
        const headers = {};
        if (token) headers['Authorization'] = `Bearer ${token}`;
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

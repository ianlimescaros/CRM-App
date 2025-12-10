async function initProfile() {
    const form = document.getElementById('profileForm');
    const errorBox = document.getElementById('profileError');
    const savedBadge = document.getElementById('profileSaved');

    async function loadProfile() {
        try {
            const res = await apiClient.me();
            if (form && res.user) {
                form.elements.name.value = res.user.name || '';
                form.elements.email.value = res.user.email || '';
                const nameDisplay = document.getElementById('profileNameDisplay');
                const emailDisplay = document.getElementById('profileEmailDisplay');
                const codeDisplay = document.getElementById('profileCodeDisplay');
                if (nameDisplay) nameDisplay.textContent = res.user.name || 'User';
                if (emailDisplay) emailDisplay.textContent = res.user.email || '--';
                if (codeDisplay) codeDisplay.textContent = '#U' + (res.user.id || '').toString().padStart(4, '0');
                const trustBar = document.getElementById('profileTrustBar');
                if (trustBar) trustBar.style.width = '85%';
            }
        } catch (err) {
            errorBox && (errorBox.textContent = err.message || 'Failed to load profile');
            errorBox && errorBox.classList.remove('hidden');
        }
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorBox && errorBox.classList.add('hidden');
            savedBadge && savedBadge.classList.add('hidden');
            const payload = {
                name: form.elements.name.value,
                email: form.elements.email.value,
            };
            const passVal = form.elements.password.value;
            if (passVal) payload.password = passVal;
            try {
                const res = await apiClient.updateProfile(payload);
                ui.showToast('Profile updated', 'success');
                if (savedBadge) savedBadge.classList.remove('hidden');
                form.elements.password.value = '';
                if (res.user?.email) {
                    localStorage.setItem('crm_user_email', res.user.email);
                    const userEmailText = document.getElementById('userEmailText');
                    if (userEmailText) {
                        userEmailText.textContent = res.user.email;
                        userEmailText.classList.remove('hidden');
                    }
                }
            } catch (err) {
                errorBox && (errorBox.textContent = err.message || 'Failed to update profile');
                errorBox && errorBox.classList.remove('hidden');
            }
        });
    }

    loadProfile();
}

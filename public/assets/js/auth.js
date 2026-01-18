// Login, register, and password reset UI logic.

function initLogin() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const registerPasswordInput = document.getElementById('registerPassword');
    const registerPasswordHint = document.getElementById('registerPasswordHint');
    const registerPasswordBar = document.getElementById('registerPasswordBar');
    const showRegister = document.getElementById('showRegister');
    const showForgot = document.getElementById('showForgot');
    const forgotForm = document.getElementById('forgotForm');
    const resetForm = document.getElementById('resetForm');
    const showResetForm = document.getElementById('showResetForm');
    const backToLoginFromForgot = document.getElementById('backToLoginFromForgot');
    const backToLoginFromRegister = document.getElementById('backToLoginFromRegister');
    const backToLoginFromReset = document.getElementById('backToLoginFromReset');
    const forgotError = document.getElementById('forgotError');
    const forgotSuccess = document.getElementById('forgotSuccess');
    const resetError = document.getElementById('resetError');
    const resetSuccess = document.getElementById('resetSuccess');


    if (showRegister) {
        showRegister.addEventListener('click', () => {
            ui.toggle(registerForm, true);
            ui.toggle(loginForm, false);
            ui.toggle(forgotForm, false);
            ui.toggle(resetForm, false);
        });
    }

    if (showForgot) {
        showForgot.addEventListener('click', () => {
            ui.toggle(loginForm, false);
            ui.toggle(registerForm, false);
            ui.toggle(resetForm, false);
            ui.toggle(forgotForm, true);
        });
    }

    if (showResetForm) {
        showResetForm.addEventListener('click', () => {
            const resetEmailInput = document.querySelector('#resetForm input[name="email"]');
            const forgotEmailField = document.getElementById('forgotEmail');
            if (resetEmailInput && forgotEmailField) {
                resetEmailInput.value = forgotEmailField.value;
            }
            ui.toggle(forgotForm, false);
            ui.toggle(resetForm, true);
        });
    }

    function updateRegisterPasswordHint() {
        if (!registerPasswordInput || !registerPasswordHint || !registerPasswordBar) return;
        const value = registerPasswordInput.value || '';
        const missing = [];
        const hasLength = value.length >= 8;
        const hasUpper = /[A-Z]/.test(value);
        const hasLower = /[a-z]/.test(value);
        const hasNumber = /\d/.test(value);
        if (!hasLength) missing.push('8+ chars');
        if (!hasUpper) missing.push('uppercase');
        if (!hasLower) missing.push('lowercase');
        if (!hasNumber) missing.push('number');
        const score = [hasLength, hasUpper, hasLower, hasNumber].filter(Boolean).length;
        const widths = [20, 35, 55, 75, 100];
        const width = widths[Math.min(score, widths.length - 1)];

        if (!value) {
            registerPasswordHint.textContent = 'At least 8 characters with uppercase, lowercase, and a number.';
            registerPasswordBar.style.width = `${width}%`;
            registerPasswordBar.className = 'h-2 bg-gray-300 transition-all rounded';
            return;
        }

        if (missing.length === 0) {
            registerPasswordHint.textContent = 'Password looks good.';
            registerPasswordBar.style.width = '100%';
            registerPasswordBar.className = 'h-2 bg-green-500 transition-all rounded';
            return;
        }

        registerPasswordHint.textContent = `Needs: ${missing.join(', ')}`;
        registerPasswordBar.style.width = `${width}%`;
        registerPasswordBar.className = 'h-2 bg-red-500 transition-all rounded';
    }

    [backToLoginFromForgot, backToLoginFromRegister, backToLoginFromReset].forEach(btn => {
        btn?.addEventListener('click', () => {
            ui.toggle(loginForm, true);
            ui.toggle(registerForm, false);
            ui.toggle(forgotForm, false);
            ui.toggle(resetForm, false);
        });
    });

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);
            const email = formData.get('email');
            const password = formData.get('password');
            const errorBox = document.getElementById('loginError');
            if (errorBox) errorBox.classList.add('hidden');

            try {
                const res = await apiClient.login(email, password);

                // keep existing behavior (store token for Authorization header usage)
                apiClient.setToken(res.token);

                if (res.user?.email) {
                    localStorage.setItem('crm_user_email', res.user.email);
                }

                // Show confirmation modal instead of immediate redirect
                const ok = await ui.confirmModal('Login successful. Proceed to the dashboard now?');
                if (ok) {
                    window.location = '/index.php?page=dashboard';
                } else {
                    ui.showToast('You are logged in. Click continue when ready.', 'success');
                }
            } catch (err) {
                ui.showToast(err.message || 'Login failed', 'error');
                if (errorBox) {
                    errorBox.textContent = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Login failed');
                    errorBox.classList.remove('hidden');
                }
            }
        });
    }

    if (forgotForm) {
        const emailInput = forgotForm.querySelector('input[name="email"]');
        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            forgotError?.classList.add('hidden');
            forgotSuccess?.classList.add('hidden');
            try {
                await apiClient.forgotPassword(emailInput?.value || '');
                ui.showToast('Reset link sent if email exists', 'success');
                if (forgotSuccess) {
                    forgotSuccess.textContent = 'If that email exists, a reset link has been sent.';
                    forgotSuccess.classList.remove('hidden');
                }
            } catch (err) {
                forgotError && (forgotError.textContent = err.message || 'Failed to send reset link');
                forgotError && forgotError.classList.remove('hidden');
            }
        });
    }

    if (resetForm) {
        const tokenInput = resetForm.querySelector('input[name="token"]');
        const passInput = resetForm.querySelector('input[name="password"]');
        const codeInputs = Array.from(resetForm.querySelectorAll('[data-reset-code]'));
        const resetEmailInput = resetForm.querySelector('input[name="email"]');
        const forgotEmailInput = document.getElementById('forgotEmail');

        const getCode = () => codeInputs.map(inp => (inp.value || '').trim()).join('');

        codeInputs.forEach((input, idx) => {
            input.addEventListener('input', () => {
                input.value = (input.value || '').replace(/\D/g, '').slice(0, 1);
                if (input.value && idx < codeInputs.length - 1) {
                    codeInputs[idx + 1].focus();
                }
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    codeInputs[idx - 1].focus();
                }
                if (!/^\d$/.test(e.key) && e.key.length === 1 && e.key !== 'Backspace') {
                    e.preventDefault();
                }
            });
        });

        resetForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            resetError?.classList.add('hidden');
            resetSuccess?.classList.add('hidden');
            try {
                const email = resetEmailInput?.value || '';
                const code = getCode();
                const tokenFromField = tokenInput?.value?.trim() || '';
                const tokenToUse = code.length === 6 ? code : tokenFromField;

                if (!email) {
                    resetError && (resetError.textContent = 'Enter your account email.');
                    resetError && resetError.classList.remove('hidden');
                    return;
                }
                if (!tokenToUse) {
                    resetError && (resetError.textContent = 'Enter the 6-digit code or use the link from your email.');
                    resetError && resetError.classList.remove('hidden');
                    return;
                }

                if (tokenInput && code.length === 6) {
                    tokenInput.value = code;
                }

                await apiClient.resetPassword(email, tokenToUse, passInput?.value || '');
                ui.showToast('Password reset', 'success');
                if (resetSuccess) {
                    resetSuccess.textContent = 'Password has been reset. You can log in now.';
                    resetSuccess.classList.remove('hidden');
                }
                ui.toggle(resetForm, false);
                ui.toggle(loginForm, true);
            } catch (err) {
                resetError && (resetError.textContent = err.message || 'Failed to reset password');
                resetError && resetError.classList.remove('hidden');
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        const presetToken = urlParams.get('reset_token') || '';
        const presetCode = urlParams.get('reset_code') || '';
        const presetEmail = urlParams.get('email') || '';
        const tokenToStore = presetToken || presetCode;

        if ((presetToken || presetCode) && tokenInput) {
            tokenInput.value = tokenToStore;
            if (resetEmailInput && presetEmail) {
                resetEmailInput.value = presetEmail;
            }

            const codeCandidate = /^\d{6}$/.test(presetCode) ? presetCode : (/^\d{6}$/.test(presetToken) ? presetToken : '');
            if (codeCandidate && codeInputs.length) {
                codeCandidate.split('').forEach((digit, i) => {
                    if (codeInputs[i]) codeInputs[i].value = digit;
                });
                codeInputs[0]?.focus();
            }
            if (!resetEmailInput?.value && forgotEmailInput?.value) {
                resetEmailInput.value = forgotEmailInput.value;
            }
            ui.toggle(loginForm, false);
            ui.toggle(registerForm, false);
            ui.toggle(forgotForm, false);
            ui.toggle(resetForm, true);
        }
    }

    if (registerForm) {
        registerPasswordInput?.addEventListener('input', updateRegisterPasswordHint);
        registerPasswordInput?.addEventListener('change', updateRegisterPasswordHint);
        registerPasswordInput?.addEventListener('keyup', updateRegisterPasswordHint);
        updateRegisterPasswordHint();
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(registerForm);
            const payload = Object.fromEntries(formData.entries());
            const errorBox = document.getElementById('registerError');
            if (errorBox) errorBox.classList.add('hidden');

            try {
                const res = await apiClient.register(payload);

                // keep existing behavior (store token for Authorization header usage)
                apiClient.setToken(res.token);

                if (res.user?.email) {
                    localStorage.setItem('crm_user_email', res.user.email);
                }

                // Show confirmation modal instead of immediate redirect
                const ok = await ui.confirmModal('Account created. Proceed to the dashboard now?');
                if (ok) {
                    // Server sets session + csrf cookie during register, so just redirect
                    window.location = '/index.php?page=dashboard';
                } else {
                    ui.showToast('Account created. You are logged in.', 'success');
                }
            } catch (err) {
                ui.showToast(err.message || 'Register failed', 'error');
                if (errorBox) {
                    errorBox.textContent = err.errors ? Object.values(err.errors).join(' ') : (err.message || 'Register failed');
                    errorBox.classList.remove('hidden');
                }
            }
        });
    }
}

// Optional: logout helper (call this from your logout button)
async function logout() {
    try {
        await apiClient.logout();
    } catch (err) {
        console.warn('Server logout failed', err);
    }
    // Clear any client-side state and redirect
    document.cookie = 'auth_token=; Max-Age=0; path=/; SameSite=Lax';
    document.cookie = 'csrf_token=; Max-Age=0; path=/; SameSite=Lax';
    apiClient.setToken('');
    localStorage.removeItem('crm_user_email');
    window.location = '/index.php?page=login';
}

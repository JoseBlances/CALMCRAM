/**
 * CramCalm — Auth helpers (login / logout / session apply)
 */
const CramAuth = (() => {
    function apiBase() {
        return (window.CRAMCALM_API || '/CALMCRAM/api').replace(/\/$/, '');
    }

    async function applySession(data) {
        if (data.token && typeof CramStore?.setToken === 'function') {
            CramStore.setToken(data.token);
        } else if (data.token) {
            localStorage.setItem('cramcalm_guest_token', data.token);
        }

        if (data.state && typeof CramStore?.applyRemoteState === 'function') {
            CramStore.applyRemoteState(data.state);
        } else if (data.state) {
            localStorage.setItem('cramcalm_data', JSON.stringify(data.state));
        }

        const onboarded = !!(data.state?.user?.onboarded || data.user?.onboarded);
        window.location.href = onboarded ? '?page=dashboard' : '?page=onboarding';
    }

    async function login(email, password) {
        const res = await fetch(apiBase() + '/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'login', email, password }),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) {
            throw new Error(data.error || 'Login failed');
        }
        await applySession(data);
        return data;
    }

    async function register(nickname, email, password) {
        const res = await fetch(apiBase() + '/auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'register', nickname, email, password }),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) {
            throw new Error(data.error || 'Sign up failed');
        }
        await applySession(data);
        return data;
    }

    async function logout() {
        const token = (typeof CramStore?.getToken === 'function')
            ? CramStore.getToken()
            : (localStorage.getItem('cramcalm_guest_token') || '');

        try {
            await fetch(apiBase() + '/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...(token ? { 'X-Guest-Token': token } : {}),
                },
                body: JSON.stringify({ action: 'logout', token }),
            });
        } catch (e) { /* ignore network errors on logout */ }

        if (typeof CramStore?.clearLocalOnly === 'function') {
            CramStore.clearLocalOnly();
        } else {
            localStorage.removeItem('cramcalm_guest_token');
            localStorage.removeItem('cramcalm_data');
        }

        window.location.href = '?page=login';
    }

    return { login, register, logout, applySession };
})();

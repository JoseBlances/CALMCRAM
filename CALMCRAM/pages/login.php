<div class="auth-page" id="page-login">
    <div class="auth-card animate-on-enter">
        <div class="auth-brand">
            <span class="auth-emoji">🌿</span>
            <h1 class="auth-title">CramCalm</h1>
            <p class="auth-tagline">Your emotionally intelligent study companion</p>
        </div>

        <div class="auth-tabs" role="tablist">
            <button type="button" class="auth-tab active" id="tab-login" role="tab" aria-selected="true"
                onclick="CramLoginUI.showTab('login')">Log in</button>
            <button type="button" class="auth-tab" id="tab-register" role="tab" aria-selected="false"
                onclick="CramLoginUI.showTab('register')">Sign up</button>
        </div>

        <p class="auth-error" id="auth-error" hidden></p>

        <form id="form-login" class="auth-form" onsubmit="return CramLoginUI.submitLogin(event)">
            <label class="auth-label" for="login-email">Email</label>
            <input class="cc-input" type="email" id="login-email" name="email" autocomplete="email"
                placeholder="you@school.edu" required>

            <label class="auth-label" for="login-password">Password</label>
            <input class="cc-input" type="password" id="login-password" name="password"
                autocomplete="current-password" placeholder="••••••••" required minlength="8">

            <button type="submit" class="cc-btn cc-btn-primary cc-btn-lg w-full mt-4" id="login-submit">
                Log in
            </button>
        </form>

        <form id="form-register" class="auth-form" hidden onsubmit="return CramLoginUI.submitRegister(event)">
            <label class="auth-label" for="register-nickname">Nickname</label>
            <input class="cc-input" type="text" id="register-nickname" name="nickname" maxlength="20"
                autocomplete="nickname" placeholder="What should we call you?" required>

            <label class="auth-label" for="register-email">Email</label>
            <input class="cc-input" type="email" id="register-email" name="email" autocomplete="email"
                placeholder="you@school.edu" required>

            <label class="auth-label" for="register-password">Password</label>
            <input class="cc-input" type="password" id="register-password" name="password"
                autocomplete="new-password" placeholder="At least 8 characters" required minlength="8">

            <label class="auth-label" for="register-confirm">Confirm password</label>
            <input class="cc-input" type="password" id="register-confirm" name="confirm"
                autocomplete="new-password" placeholder="Repeat password" required minlength="8">

            <button type="submit" class="cc-btn cc-btn-primary cc-btn-lg w-full mt-4" id="register-submit">
                Create account
            </button>
        </form>
    </div>
</div>

<script>
const CramLoginUI = (() => {
    function showTab(which) {
        const loginForm = document.getElementById('form-login');
        const registerForm = document.getElementById('form-register');
        const tabLogin = document.getElementById('tab-login');
        const tabRegister = document.getElementById('tab-register');
        const isLogin = which === 'login';

        loginForm.hidden = !isLogin;
        registerForm.hidden = isLogin;
        tabLogin.classList.toggle('active', isLogin);
        tabRegister.classList.toggle('active', !isLogin);
        tabLogin.setAttribute('aria-selected', isLogin ? 'true' : 'false');
        tabRegister.setAttribute('aria-selected', isLogin ? 'false' : 'true');
        clearError();
    }

    function clearError() {
        const el = document.getElementById('auth-error');
        if (!el) return;
        el.hidden = true;
        el.textContent = '';
    }

    function showError(msg) {
        const el = document.getElementById('auth-error');
        if (!el) return;
        el.textContent = msg || 'Something went wrong.';
        el.hidden = false;
    }

    function setBusy(btn, busy, label) {
        if (!btn) return;
        btn.disabled = !!busy;
        if (label) btn.textContent = label;
    }

    async function submitLogin(event) {
        event.preventDefault();
        clearError();
        const btn = document.getElementById('login-submit');
        const email = document.getElementById('login-email')?.value?.trim() || '';
        const password = document.getElementById('login-password')?.value || '';

        setBusy(btn, true, 'Logging in…');
        try {
            await CramAuth.login(email, password);
        } catch (err) {
            showError(err.message);
            setBusy(btn, false, 'Log in');
        }
        return false;
    }

    async function submitRegister(event) {
        event.preventDefault();
        clearError();
        const btn = document.getElementById('register-submit');
        const nickname = document.getElementById('register-nickname')?.value?.trim() || '';
        const email = document.getElementById('register-email')?.value?.trim() || '';
        const password = document.getElementById('register-password')?.value || '';
        const confirm = document.getElementById('register-confirm')?.value || '';

        if (password !== confirm) {
            showError('Passwords do not match.');
            return false;
        }

        setBusy(btn, true, 'Creating account…');
        try {
            await CramAuth.register(nickname, email, password);
        } catch (err) {
            showError(err.message);
            setBusy(btn, false, 'Create account');
        }
        return false;
    }

    return { showTab, submitLogin, submitRegister };
})();
</script>

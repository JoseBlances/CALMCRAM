/**
 * CramCalm — Main App Controller
 * Initializes all subsystems and manages global state
 */
const CramApp = (() => {
    let initialized = false;

    async function init() {
        if (initialized) return;
        initialized = true;

        console.log('🌿 CramCalm initializing...');

        // Sync with MySQL (falls back to localStorage if DB is offline)
        let syncResult = { ok: false, needsAuth: true };
        if (typeof CramStore.syncFromServer === 'function') {
            syncResult = await CramStore.syncFromServer();
        } else {
            CramStore.load();
        }

        const currentPage = new URLSearchParams(window.location.search).get('page') || 'dashboard';
        const authPages = ['login'];

        // Require login for the app
        if (syncResult.needsAuth) {
            if (!authPages.includes(currentPage)) {
                window.location.href = '?page=login';
                return;
            }
            // On login page: still init light UI (dark mode)
            initDarkMode();
            initAccessibility();
            console.log('🌿 CramCalm auth screen ready');
            return;
        }

        // Already logged in — leave the login page
        if (currentPage === 'login') {
            window.location.href = CramStore.get('user.onboarded') ? '?page=dashboard' : '?page=onboarding';
            return;
        }

        // Setup dark mode
        initDarkMode();

        // Restore accessibility settings
        initAccessibility();

        // Check onboarding
        if (!CramStore.get('user.onboarded')) {
            if (currentPage !== 'onboarding') {
                window.location.href = '?page=onboarding';
                return;
            }
        }

        // Initialize subsystems
        CramNotify?.init();
        CramState?.init();

        // Page entrance animation
        const mainContent = document.getElementById('main-content');
        if (mainContent) {
            CramAnimate?.pageEnter(mainContent);
        }

        // Setup scroll animations
        CramAnimate?.observeEntrance();

        // Setup tap feedback on interactive elements
        document.querySelectorAll('.cc-btn, .cc-card-interactive, .nav-tab').forEach(el => {
            CramAnimate?.tapFeedback(el);
        });

        // Greeting based on time of day
        updateGreeting();

        // Check for late night
        detectLateNight();

        console.log('🌿 CramCalm ready!');
    }

    function initDarkMode() {
        const root = document.documentElement;
        const settings = CramStore.get('settings') || {};
        const theme = settings.theme || 'auto';
        let dark;

        if (theme === 'light') dark = false;
        else if (theme === 'dark') dark = true;
        else if (theme === 'auto') {
            dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        } else {
            dark = !(settings.darkMode === false || settings.darkMode === 'false');
        }

        root.classList.toggle('dark', dark);
        root.style.backgroundColor = dark ? '#1A2332' : '#F0F7F4';
        root.style.colorScheme = dark ? 'dark' : 'light';
        if (CramStore.get('settings.darkMode') !== dark) {
            CramStore.set('settings.darkMode', dark);
        }

        const meta = document.getElementById('meta-theme-color');
        if (meta) meta.setAttribute('content', dark ? '#1A2332' : '#F0F7F4');

        // Listen for system changes (auto theme only)
        if (!initDarkMode._bound) {
            initDarkMode._bound = true;
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (CramStore.get('settings.theme') === 'auto') {
                    root.classList.toggle('dark', e.matches);
                    root.style.backgroundColor = e.matches ? '#1A2332' : '#F0F7F4';
                    root.style.colorScheme = e.matches ? 'dark' : 'light';
                    CramStore.set('settings.darkMode', e.matches);
                }
            });
        }
    }

    function toggleDarkMode() {
        const root = document.documentElement;
        const isDark = root.classList.toggle('dark');
        root.style.backgroundColor = isDark ? '#1A2332' : '#F0F7F4';
        root.style.colorScheme = isDark ? 'dark' : 'light';
        CramStore.set('settings.darkMode', isDark);
        CramStore.set('settings.theme', isDark ? 'dark' : 'light');
        const meta = document.getElementById('meta-theme-color');
        if (meta) meta.setAttribute('content', isDark ? '#1A2332' : '#F0F7F4');
        return isDark;
    }

    function initAccessibility() {
        const settings = CramStore.get('settings') || {};
        const root = document.documentElement;

        // Font size
        if (settings.fontSize) {
            const sizes = { small: '14px', normal: '16px', large: '18px', xlarge: '20px' };
            root.style.fontSize = sizes[settings.fontSize] || '16px';
        }

        // Accent color
        if (settings.accentColor) {
            applyAccentColor(settings.accentColor);
        }

        // High contrast
        if (settings.highContrast) root.classList.add('high-contrast');

        // Dyslexia font
        if (settings.dyslexiaFont) root.classList.add('dyslexia-font');

        // Large touches
        if (settings.largeTouches) root.classList.add('large-touches');

        // Reduced motion
        if (settings.reducedMotion) {
            root.classList.add('reduce-motion');
            root.style.setProperty('--cc-transition-base', '0ms');
        }
    }

    function applyAccentColor(hex) {
        if (!hex) return;
        const root = document.documentElement;
        root.style.setProperty('--cc-primary', hex);

        // Compute hover (darken 15%)
        const r = parseInt(hex.slice(1,3), 16);
        const g = parseInt(hex.slice(3,5), 16);
        const b = parseInt(hex.slice(5,7), 16);
        const darken = (v) => Math.max(0, Math.round(v * 0.85));
        const lighten = (v) => Math.min(255, Math.round(v * 1.2));
        root.style.setProperty('--cc-primary-hover', `rgb(${darken(r)},${darken(g)},${darken(b)})`);
        root.style.setProperty('--cc-accent', `rgb(${lighten(r)},${lighten(g)},${lighten(b)})`);
        root.style.setProperty('--cc-shadow-glow', `0 0 20px rgba(${r},${g},${b},0.25)`);
    }

    function getGreeting() {
        const hour = new Date().getHours();
        const nickname = CramStore.get('user.nickname') || 'Scholar';

        if (hour >= 5 && hour < 12) {
            return { text: `Good morning, ${nickname}!`, emoji: '🌅', sub: "Let's make today count" };
        } else if (hour >= 12 && hour < 17) {
            return { text: `Good afternoon, ${nickname}!`, emoji: '☀️', sub: 'Stay focused and hydrated' };
        } else if (hour >= 17 && hour < 22) {
            return { text: `Good evening, ${nickname}!`, emoji: '🌆', sub: 'Wrapping up the day?' };
        } else {
            return { text: `Still awake, ${nickname}?`, emoji: '🌙', sub: 'Remember to rest soon' };
        }
    }

    function updateGreeting() {
        const greetingEl = document.getElementById('greeting-text');
        const greetingEmoji = document.getElementById('greeting-emoji');
        const greetingSub = document.getElementById('greeting-sub');
        
        if (greetingEl) {
            const g = getGreeting();
            greetingEl.textContent = g.text;
            if (greetingEmoji) greetingEmoji.textContent = g.emoji;
            if (greetingSub) greetingSub.textContent = g.sub;
        }
    }

    function detectLateNight() {
        const hour = new Date().getHours();
        if (hour >= 1 && hour < 5) {
            // Deep night encouragement
            setTimeout(() => {
                CramNotify?.show({
                    message: "It's really late... Your brain needs sleep to retain what you study 💤",
                    type: 'info',
                    icon: '😴',
                    duration: 10000,
                });
            }, 5000);
        }
    }

    /**
     * Navigate to a page
     */
    function navigate(page) {
        window.location.href = `?page=${page}`;
    }

    /**
     * Format time duration
     */
    function formatDuration(minutes) {
        if (minutes < 60) return `${minutes}m`;
        const h = Math.floor(minutes / 60);
        const m = minutes % 60;
        return m > 0 ? `${h}h ${m}m` : `${h}h`;
    }

    /**
     * Format relative time
     */
    function timeAgo(date) {
        const now = new Date();
        const d = new Date(date);
        const diff = now - d;
        const mins = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (mins < 1) return 'just now';
        if (mins < 60) return `${mins}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        return d.toLocaleDateString();
    }

    /**
     * Format deadline
     */
    function formatDeadline(deadline) {
        if (!deadline) return '';
        const d = new Date(deadline);
        const now = new Date();
        const diff = d - now;
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (diff < 0) return 'Overdue!';
        if (hours < 1) return 'Due very soon!';
        if (hours < 24) return `Due in ${hours}h`;
        if (days < 7) return `Due in ${days}d`;
        return `Due ${d.toLocaleDateString()}`;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    return {
        init, navigate, toggleDarkMode, applyAccentColor,
        getGreeting, formatDuration, timeAgo, formatDeadline,
    };
})();

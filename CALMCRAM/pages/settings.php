<div class="cc-page pb-nav" id="page-settings">
    <div class="cc-page-header animate-on-enter">
        <h1 class="text-2xl font-heading font-black">Settings ⚙️</h1>
    </div>

    <!-- Appearance -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🎨 Appearance</h3>
        <div class="cc-card">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="font-heading font-bold text-sm">Dark Mode</p>
                    <p class="cc-text-xs cc-text-muted">Switch between light and dark themes</p>
                </div>
                <div class="cc-toggle" id="setting-dark-mode" onclick="CramSettings.toggleDarkMode(this)"></div>
            </div>
            <hr class="cc-divider">
            <div class="py-2">
                <p class="font-heading font-bold text-sm mb-2">Theme</p>
                <div class="chip-row" id="setting-theme">
                    <button class="chip" data-theme="auto" onclick="CramSettings.setTheme('auto', this)">🌗 Auto</button>
                    <button class="chip" data-theme="light" onclick="CramSettings.setTheme('light', this)">☀️ Light</button>
                    <button class="chip" data-theme="dark" onclick="CramSettings.setTheme('dark', this)">🌙 Dark</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customization -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🎨 Customization</h3>
        <div class="cc-card">
            <div class="py-2">
                <p class="font-heading font-bold text-sm mb-2">Accent Color</p>
                <p class="cc-text-xs cc-text-muted mb-3">Choose your theme color</p>
                <div class="flex gap-3 flex-wrap" id="accent-color-picker">
                    <button class="accent-swatch active" data-color="#6B9080" onclick="CramSettings.setAccentColor('#6B9080', this)" style="background:#6B9080;" title="Sage Green"></button>
                    <button class="accent-swatch" data-color="#4A90D9" onclick="CramSettings.setAccentColor('#4A90D9', this)" style="background:#4A90D9;" title="Ocean Blue"></button>
                    <button class="accent-swatch" data-color="#9B8EC8" onclick="CramSettings.setAccentColor('#9B8EC8', this)" style="background:#9B8EC8;" title="Lavender"></button>
                    <button class="accent-swatch" data-color="#D4845C" onclick="CramSettings.setAccentColor('#D4845C', this)" style="background:#D4845C;" title="Sunset"></button>
                    <button class="accent-swatch" data-color="#C77C8A" onclick="CramSettings.setAccentColor('#C77C8A', this)" style="background:#C77C8A;" title="Rose"></button>
                    <button class="accent-swatch" data-color="#4A5568" onclick="CramSettings.setAccentColor('#4A5568', this)" style="background:#4A5568;" title="Midnight"></button>
                </div>
            </div>
            <hr class="cc-divider">
            <div class="py-2">
                <p class="font-heading font-bold text-sm mb-2">Greeting Style</p>
                <div class="chip-row" id="setting-greeting">
                    <button class="chip active" data-style="casual" onclick="CramSettings.setGreetingStyle('casual', this)">🙂 Casual</button>
                    <button class="chip" data-style="formal" onclick="CramSettings.setGreetingStyle('formal', this)">🎩 Formal</button>
                    <button class="chip" data-style="fun" onclick="CramSettings.setGreetingStyle('fun', this)">🎉 Fun</button>
                </div>
            </div>
            <hr class="cc-divider">
            <div class="py-2">
                <p class="font-heading font-bold text-sm mb-2">App Icon Style</p>
                <div class="chip-row" id="setting-icon-style">
                    <button class="chip active" data-style="default" onclick="CramSettings.setIconStyle('default', this)">🌿 Default</button>
                    <button class="chip" data-style="minimal" onclick="CramSettings.setIconStyle('minimal', this)">⚡ Minimal</button>
                    <button class="chip" data-style="fun" onclick="CramSettings.setIconStyle('fun', this)">🎨 Fun</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Focus Mode -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🎯 Focus Mode</h3>
        <div class="cc-card">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="font-heading font-bold text-sm">Default Duration</p>
                    <p class="cc-text-xs cc-text-muted">Your preferred focus length</p>
                </div>
                <select class="cc-input" style="width: auto; padding: 0.5rem 0.75rem;" id="setting-focus-duration" onchange="CramSettings.saveFocusDuration(this.value)">
                    <option value="15">15 min</option>
                    <option value="25" selected>25 min</option>
                    <option value="30">30 min</option>
                    <option value="45">45 min</option>
                    <option value="60">60 min</option>
                    <option value="90">90 min</option>
                </select>
            </div>
            <hr class="cc-divider">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="font-heading font-bold text-sm">Short Break</p>
                    <p class="cc-text-xs cc-text-muted">Break between focus sessions</p>
                </div>
                <select class="cc-input" style="width: auto; padding: 0.5rem 0.75rem;" id="setting-break-duration" onchange="CramSettings.saveBreakDuration(this.value)">
                    <option value="3">3 min</option>
                    <option value="5" selected>5 min</option>
                    <option value="10">10 min</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🔔 Notifications</h3>
        <div class="cc-card">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="font-heading font-bold text-sm">Notifications</p>
                    <p class="cc-text-xs cc-text-muted">In-app notifications</p>
                </div>
                <div class="cc-toggle active" id="setting-notifications" onclick="CramSettings.toggle('notifications', this)"></div>
            </div>
            <hr class="cc-divider">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="font-heading font-bold text-sm">Motivational Messages</p>
                    <p class="cc-text-xs cc-text-muted">Encouraging nudges while studying</p>
                </div>
                <div class="cc-toggle active" id="setting-motivation" onclick="CramSettings.toggle('motivation', this)"></div>
            </div>
        </div>
    </div>

    <!-- Accessibility -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">♿ Accessibility</h3>
        <div class="cc-card">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="font-heading font-bold text-sm">Reduce Motion</p>
                    <p class="cc-text-xs cc-text-muted">Minimize animations</p>
                </div>
                <div class="cc-toggle" id="setting-reduced-motion" onclick="CramSettings.toggleReducedMotion(this)"></div>
            </div>
            <hr class="cc-divider">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="font-heading font-bold text-sm">High Contrast</p>
                    <p class="cc-text-xs cc-text-muted">Increase border visibility</p>
                </div>
                <div class="cc-toggle" id="setting-high-contrast" onclick="CramSettings.toggleHighContrast(this)"></div>
            </div>
            <hr class="cc-divider">
            <div class="flex items-center justify-between py-2">
            </div>
            <div class="py-2">
                <p class="font-heading font-bold text-sm mb-2">Font Size</p>
                <div class="chip-row" id="setting-font-size">
                    <button class="chip" data-size="small" onclick="CramSettings.setFontSize('small', this)">Small</button>
                    <button class="chip active" data-size="normal" onclick="CramSettings.setFontSize('normal', this)">Normal</button>
                    <button class="chip" data-size="large" onclick="CramSettings.setFontSize('large', this)">Large</button>
                    <button class="chip" data-size="xlarge" onclick="CramSettings.setFontSize('xlarge', this)">Extra Large</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Sync -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">📅 Calendar Sync</h3>
        <div class="cc-card">
            <div class="flex items-center justify-between py-2">
                <div class="flex items-center gap-3">
                    <span class="text-xl">📱</span>
                    <div>
                        <p class="font-heading font-bold text-sm">Google Calendar</p>
                        <p class="cc-text-xs cc-text-muted" id="cal-google-status">Not connected</p>
                    </div>
                </div>
                <button class="cc-btn cc-btn-primary cc-btn-sm" id="cal-google-btn" onclick="CramSettings.connectCalendar('google', this)">Connect</button>
            </div>
            <hr class="cc-divider">
            <div class="flex items-center justify-between py-2">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🍎</span>
                    <div>
                        <p class="font-heading font-bold text-sm">Apple Calendar</p>
                        <p class="cc-text-xs cc-text-muted" id="cal-apple-status">Not connected</p>
                    </div>
                </div>
                <button class="cc-btn cc-btn-secondary cc-btn-sm" id="cal-apple-btn" onclick="CramSettings.connectCalendar('apple', this)">Connect</button>
            </div>
            <hr class="cc-divider">
            <div class="flex items-center justify-between py-2">
                <div>
                    <p class="font-heading font-bold text-sm">Auto-Sync</p>
                    <p class="cc-text-xs cc-text-muted">Sync deadlines automatically</p>
                </div>
                <div class="cc-toggle" id="setting-auto-sync" onclick="CramSettings.toggle('autoSync', this)"></div>
            </div>
            <hr class="cc-divider">
            <div class="py-2">
                <button class="cc-btn cc-btn-ghost cc-w-full" onclick="CramCalendar?.exportICS?.() || CramNotify?.info('Navigate to Calendar to export')">📤 Export .ics File</button>
            </div>
        </div>
    </div>

    <!-- Academic State (Debug) -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🧪 Academic State (Demo)</h3>
        <div class="cc-card">
            <p class="cc-text-xs cc-text-muted mb-3">Test different emotional states:</p>
            <div class="chip-row">
                <button class="chip" onclick="CramState.forceState('stable')">☀️ Stable</button>
                <button class="chip" onclick="CramState.forceState('pressure')">⚡ Pressure</button>
                <button class="chip" onclick="CramState.forceState('crisis')">🌊 Crisis</button>
                <button class="chip" onclick="CramState.forceState('burnout')">🌙 Burnout</button>
            </div>
        </div>
    </div>

    <!-- Data -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">💾 Data Management</h3>
        <div class="flex flex-col gap-3">
            <button class="cc-btn cc-btn-secondary w-full" onclick="CramProfile?.exportData()">📦 Export Data</button>
            <button class="cc-btn cc-btn-danger w-full" id="clear-data-btn" onclick="CramSettings.clearData()">🗑️ Clear All Data</button>
        </div>
    </div>

    <!-- About -->
    <div class="cc-card animate-on-enter text-center mt-4 mb-8">
        <p class="font-heading font-bold">🌿 CramCalm</p>
        <p class="cc-text-xs cc-text-muted mt-1">Your emotionally intelligent study companion</p>
        <p class="cc-text-xs cc-text-muted mt-1">Made with ❤️ for students</p>
    </div>
</div>

<style>
    .accent-swatch {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 3px solid transparent;
        cursor: pointer;
        transition: all var(--cc-transition-fast);
        position: relative;
    }
    .accent-swatch:hover {
        transform: scale(1.1);
    }
    .accent-swatch.active {
        border-color: var(--cc-text);
        box-shadow: 0 0 0 2px var(--cc-card-bg), 0 0 0 4px var(--cc-text);
    }
    .accent-swatch:active {
        transform: scale(0.95);
    }
</style>

<script>
const CramSettings = {
    init() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page !== 'settings') return;

        const settings = CramStore.get('settings') || {};

        // Dark mode toggle
        const dmToggle = document.getElementById('setting-dark-mode');
        if (dmToggle && (settings.darkMode || settings.darkMode === undefined)) dmToggle.classList.add('active');

        // Reduced motion
        const rmToggle = document.getElementById('setting-reduced-motion');
        if (rmToggle && settings.reducedMotion) rmToggle.classList.add('active');

        // High contrast
        const hcToggle = document.getElementById('setting-high-contrast');
        if (hcToggle && settings.highContrast) hcToggle.classList.add('active');

        // Dyslexia font
        const dfToggle = document.getElementById('setting-dyslexia-font');
        if (dfToggle && settings.dyslexiaFont) dfToggle.classList.add('active');

        // Large touches
        const ltToggle = document.getElementById('setting-large-touches');
        if (ltToggle && settings.largeTouches) ltToggle.classList.add('active');

        // Screen reader
        const srToggle = document.getElementById('setting-screen-reader');
        if (srToggle && settings.screenReader) srToggle.classList.add('active');

        // Focus duration
        const fdSelect = document.getElementById('setting-focus-duration');
        if (fdSelect) fdSelect.value = settings.focusDuration || 25;

        // Break duration
        const bdSelect = document.getElementById('setting-break-duration');
        if (bdSelect) bdSelect.value = settings.breakDuration || 5;

        // Font size
        const fsChips = document.getElementById('setting-font-size');
        if (fsChips) {
            fsChips.querySelectorAll('.chip').forEach(c => {
                c.classList.toggle('active', c.dataset.size === (settings.fontSize || 'normal'));
            });
        }

        // Theme
        const themeChips = document.getElementById('setting-theme');
        if (themeChips) {
            themeChips.querySelectorAll('.chip').forEach(c => {
                c.classList.toggle('active', c.dataset.theme === (settings.theme || 'dark'));
            });
        }

        // Accent color
        const accentPicker = document.getElementById('accent-color-picker');
        if (accentPicker && settings.accentColor) {
            accentPicker.querySelectorAll('.accent-swatch').forEach(s => {
                s.classList.toggle('active', s.dataset.color === settings.accentColor);
            });
        }

        // Greeting style
        const greetingChips = document.getElementById('setting-greeting');
        if (greetingChips) {
            greetingChips.querySelectorAll('.chip').forEach(c => {
                c.classList.toggle('active', c.dataset.style === (settings.greetingStyle || 'casual'));
            });
        }

        // Icon style
        const iconChips = document.getElementById('setting-icon-style');
        if (iconChips) {
            iconChips.querySelectorAll('.chip').forEach(c => {
                c.classList.toggle('active', c.dataset.style === (settings.iconStyle || 'default'));
            });
        }

        // Calendar sync status
        if (settings.calGoogle) {
            const gBtn = document.getElementById('cal-google-btn');
            const gStatus = document.getElementById('cal-google-status');
            if (gBtn) { gBtn.textContent = 'Connected ✅'; gBtn.disabled = true; }
            if (gStatus) gStatus.textContent = 'Synced';
        }
        if (settings.calApple) {
            const aBtn = document.getElementById('cal-apple-btn');
            const aStatus = document.getElementById('cal-apple-status');
            if (aBtn) { aBtn.textContent = 'Connected ✅'; aBtn.disabled = true; }
            if (aStatus) aStatus.textContent = 'Synced';
        }

        // Auto-sync
        const asToggle = document.getElementById('setting-auto-sync');
        if (asToggle && settings.autoSync) asToggle.classList.add('active');
    },

    toggleDarkMode(toggle) {
        toggle.classList.toggle('active');
        CramApp.toggleDarkMode();
    },

    setTheme(theme, btn) {
        const container = document.getElementById('setting-theme');
        container?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        CramStore.set('settings.theme', theme);

        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            CramStore.set('settings.darkMode', true);
            document.getElementById('setting-dark-mode')?.classList.add('active');
        } else if (theme === 'light') {
            document.documentElement.classList.remove('dark');
            CramStore.set('settings.darkMode', false);
            document.getElementById('setting-dark-mode')?.classList.remove('active');
        } else {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', prefersDark);
            CramStore.set('settings.darkMode', prefersDark);
        }
    },

    toggle(setting, toggle) {
        toggle.classList.toggle('active');
        CramStore.set(`settings.${setting}`, toggle.classList.contains('active'));
    },

    toggleReducedMotion(toggle) {
        toggle.classList.toggle('active');
        const reduced = toggle.classList.contains('active');
        CramStore.set('settings.reducedMotion', reduced);
        document.documentElement.classList.toggle('reduce-motion', reduced);
        if (reduced) {
            document.documentElement.style.setProperty('--cc-transition-base', '0ms');
        } else {
            document.documentElement.style.removeProperty('--cc-transition-base');
        }
    },

    toggleHighContrast(toggle) {
        toggle.classList.toggle('active');
        const enabled = toggle.classList.contains('active');
        CramStore.set('settings.highContrast', enabled);
        document.documentElement.classList.toggle('high-contrast', enabled);
    },

    toggleDyslexiaFont(toggle) {
        toggle.classList.toggle('active');
        const enabled = toggle.classList.contains('active');
        CramStore.set('settings.dyslexiaFont', enabled);
        document.documentElement.classList.toggle('dyslexia-font', enabled);
    },

    toggleLargeTouches(toggle) {
        toggle.classList.toggle('active');
        const enabled = toggle.classList.contains('active');
        CramStore.set('settings.largeTouches', enabled);
        document.documentElement.classList.toggle('large-touches', enabled);
    },

    toggleScreenReader(toggle) {
        toggle.classList.toggle('active');
        const enabled = toggle.classList.contains('active');
        CramStore.set('settings.screenReader', enabled);
        // Add/remove aria-labels on key interactive elements
        document.querySelectorAll('.cc-btn:not([aria-label])').forEach(el => {
            if (enabled) el.setAttribute('aria-label', el.textContent.trim());
        });
        document.querySelectorAll('.nav-tab:not([aria-label])').forEach(el => {
            if (enabled) el.setAttribute('aria-label', 'Navigate to ' + (el.querySelector('.nav-label')?.textContent || ''));
        });
    },

    setFontSize(size, btn) {
        const container = document.getElementById('setting-font-size');
        container?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        CramStore.set('settings.fontSize', size);
        const sizes = { small: '14px', normal: '16px', large: '18px', xlarge: '20px' };
        document.documentElement.style.fontSize = sizes[size] || '16px';
    },

    setAccentColor(hex, btn) {
        const container = document.getElementById('accent-color-picker');
        container?.querySelectorAll('.accent-swatch').forEach(s => s.classList.remove('active'));
        btn.classList.add('active');
        CramStore.set('settings.accentColor', hex);
        CramApp.applyAccentColor(hex);
    },

    setGreetingStyle(style, btn) {
        const container = document.getElementById('setting-greeting');
        container?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        CramStore.set('settings.greetingStyle', style);
    },

    setIconStyle(style, btn) {
        const container = document.getElementById('setting-icon-style');
        container?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        CramStore.set('settings.iconStyle', style);
    },

    connectCalendar(provider, btn) {
        btn.textContent = 'Connecting...';
        btn.disabled = true;
        setTimeout(() => {
            CramStore.set(`settings.cal${provider.charAt(0).toUpperCase() + provider.slice(1)}`, true);
            btn.textContent = 'Connected ✅';
            const statusEl = document.getElementById(`cal-${provider}-status`);
            if (statusEl) statusEl.textContent = 'Synced';
            CramNotify?.success(`${provider.charAt(0).toUpperCase() + provider.slice(1)} Calendar connected! 📅`);
        }, 1500);
    },

    saveFocusDuration(val) {
        CramStore.set('settings.focusDuration', parseInt(val));
    },

    saveBreakDuration(val) {
        CramStore.set('settings.breakDuration', parseInt(val));
    },

    clearData() {
        if (confirm('⚠️ This will permanently delete all your CramCalm data. Are you sure?')) {
            CramStore.clearAll();
            CramNotify.info('All data cleared. Starting fresh! 🌱');
            setTimeout(() => window.location.href = '?page=onboarding', 1000);
        }
    },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => CramSettings.init());
} else {
    setTimeout(() => CramSettings.init(), 100);
}
</script>

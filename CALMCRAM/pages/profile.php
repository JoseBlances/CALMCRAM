<div class="cc-page pb-nav" id="page-profile">
    <div class="cc-page-header animate-on-enter text-center">
        <!-- Avatar -->
        <div class="w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center text-3xl font-heading font-black text-white" id="profile-avatar"
            style="background: linear-gradient(135deg, var(--cc-primary), var(--cc-accent));">
        </div>
        <h1 class="text-xl font-heading font-black" id="profile-nickname">Scholar</h1>
        <p class="cc-text-xs cc-text-muted mt-1" id="profile-email"></p>
        <button class="cc-text-xs cc-text-primary font-heading font-bold mt-1 bg-transparent border-0 cursor-pointer" onclick="CramProfile.editNickname()">✏️ Edit Name</button>
        <div class="mt-3">
            <span class="cc-badge cc-badge-primary" id="profile-account-badge">🎓 Signed in</span>
        </div>
    </div>

    <!-- Personality Badge -->
    <div class="text-center mb-5 animate-on-enter">
        <div class="personality-badge inline-flex" id="personality-badge">🎓 Balanced Scholar</div>
        <p class="cc-text-xs cc-text-muted mt-2" id="personality-desc">Well-rounded study habits!</p>
    </div>

    <!-- Streak -->
    <div class="cc-card animate-on-enter mb-5 text-center">
        <div class="streak-display justify-center">
            <span class="streak-flame">🔥</span>
            <span class="streak-count" id="profile-streak-count">0</span>
            <span class="cc-text-sm cc-text-muted">day streak</span>
        </div>
        <p class="cc-text-xs cc-text-muted mt-1">Best: <span id="profile-best-streak">0</span> days</p>
    </div>

    <!-- Stats -->
    <div class="stats-row animate-on-enter mb-5">
        <div class="stat-card">
            <div class="stat-value" id="profile-total-focus">0h</div>
            <div class="stat-label">⏱ Focus</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="profile-total-tasks">0</div>
            <div class="stat-label">✅ Done</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="profile-total-sessions">0</div>
            <div class="stat-label">🎯 Sessions</div>
        </div>
    </div>

    <!-- Achievements -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🏆 Achievements</h3>
        <div class="grid grid-cols-4 gap-3" id="achievements-grid"></div>
    </div>

    <!-- Quick Links -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">📌 Quick Links</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="?page=analytics" class="cc-card cc-card-interactive text-center py-4">
                <span class="text-2xl block mb-1">📊</span>
                <span class="cc-text-sm font-heading font-bold">Analytics</span>
            </a>
            <a href="?page=wisdom-tree" class="cc-card cc-card-interactive text-center py-4">
                <span class="text-2xl block mb-1">🌳</span>
                <span class="cc-text-sm font-heading font-bold">Wisdom Tree</span>
            </a>
            <a href="?page=calendar" class="cc-card cc-card-interactive text-center py-4">
                <span class="text-2xl block mb-1">📅</span>
                <span class="cc-text-sm font-heading font-bold">Calendar</span>
            </a>
            <a href="?page=settings" class="cc-card cc-card-interactive text-center py-4">
                <span class="text-2xl block mb-1">⚙️</span>
                <span class="cc-text-sm font-heading font-bold">Settings</span>
            </a>
        </div>
    </div>

    <!-- Data -->
    <div class="cc-section animate-on-enter">
        <button class="cc-btn cc-btn-secondary w-full mb-3" onclick="CramProfile.exportData()">📦 Export Data</button>
        <button class="cc-btn cc-btn-secondary w-full mb-3" onclick="CramProfile.logout()">🚪 Log out</button>
    </div>
</div>

<!-- Edit Nickname Modal -->
<div class="cc-modal-overlay" id="nickname-modal">
    <div class="cc-modal">
        <div class="cc-modal-handle"></div>
        <h3 class="font-heading font-bold text-lg mb-4">Edit Nickname</h3>
        <input type="text" class="cc-input mb-4" id="nickname-input" placeholder="Your nickname" maxlength="20">
        <div class="flex gap-3">
            <button class="cc-btn cc-btn-secondary flex-1" onclick="document.getElementById('nickname-modal').classList.remove('active')">Cancel</button>
            <button class="cc-btn cc-btn-primary flex-1" onclick="CramProfile.saveNickname()">Save</button>
        </div>
    </div>
</div>

<script>
const CramProfile = {
    init() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page !== 'profile') return;

        const nickname = CramStore.get('user.nickname') || 'Scholar';
        const email = CramStore.get('user.email') || '';
        const stats = CramStore.get('stats') || {};
        const streak = CramStore.get('streak') || {};

        document.getElementById('profile-nickname').textContent = nickname;
        document.getElementById('profile-avatar').textContent = nickname.charAt(0).toUpperCase();
        const emailEl = document.getElementById('profile-email');
        if (emailEl) emailEl.textContent = email;
        const badge = document.getElementById('profile-account-badge');
        if (badge) badge.textContent = email ? '🎓 Signed in' : '🎓 Account';
        document.getElementById('profile-streak-count').textContent = streak.current || 0;
        document.getElementById('profile-best-streak').textContent = streak.best || 0;
        document.getElementById('profile-total-focus').textContent = CramApp.formatDuration(stats.totalFocusMinutes || 0);
        document.getElementById('profile-total-tasks').textContent = stats.totalTasksCompleted || 0;
        document.getElementById('profile-total-sessions').textContent = stats.totalSessions || 0;

        // Achievements
        this.renderAchievements();

        // Personality
        if (typeof CramPersonality !== 'undefined') CramPersonality.init();
    },

    renderAchievements() {
        const grid = document.getElementById('achievements-grid');
        if (!grid) return;

        const stats = CramStore.get('stats') || {};
        const streak = CramStore.get('streak') || {};
        const tree = CramStore.get('wisdomTree') || {};
        const sessions = CramStore.get('focusSessions') || [];

        const achievements = [
            { emoji: '🌱', label: 'First Sprout', unlocked: (stats.totalTasksCompleted || 0) >= 1 },
            { emoji: '🔥', label: 'On Fire', unlocked: (streak.best || 0) >= 3 },
            { emoji: '🧠', label: 'Deep Focus', unlocked: sessions.some(s => (s.completedMinutes || 0) >= 60) },
            { emoji: '⚡', label: 'Speed Demon', unlocked: (stats.totalTasksCompleted || 0) >= 5 },
            { emoji: '🌳', label: 'Tree Grower', unlocked: (tree.stage || 0) >= 2 },
            { emoji: '📚', label: 'Bookworm', unlocked: (stats.totalFocusMinutes || 0) >= 300 },
            { emoji: '🏆', label: 'Champion', unlocked: (streak.best || 0) >= 7 },
            { emoji: '🌟', label: 'Star Student', unlocked: (stats.totalTasksCompleted || 0) >= 20 },
        ];

        grid.innerHTML = achievements.map(a => `
            <div class="text-center py-3 rounded-xl transition-all ${a.unlocked ? '' : 'opacity-30 grayscale'}" title="${a.label}">
                <span class="text-2xl block">${a.emoji}</span>
                <span class="cc-text-xs cc-text-muted block mt-1">${a.label}</span>
            </div>
        `).join('');
    },

    editNickname() {
        const modal = document.getElementById('nickname-modal');
        const input = document.getElementById('nickname-input');
        if (input) input.value = CramStore.get('user.nickname') || '';
        if (modal) modal.classList.add('active');
        setTimeout(() => input?.focus(), 300);
    },

    saveNickname() {
        const input = document.getElementById('nickname-input');
        const name = input?.value?.trim();
        if (!name) return;
        CramStore.set('user.nickname', name);
        document.getElementById('profile-nickname').textContent = name;
        document.getElementById('profile-avatar').textContent = name.charAt(0).toUpperCase();
        document.getElementById('nickname-modal').classList.remove('active');
        CramNotify.success('Nickname updated! 🎉');
    },

    exportData() {
        CramStore.downloadExport();
        CramNotify.success('Data exported! 📦');
    },

    async logout() {
        if (!confirm('Log out of CramCalm?')) return;
        await CramAuth.logout();
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => CramProfile.init());
} else {
    setTimeout(() => CramProfile.init(), 100);
}
</script>

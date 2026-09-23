<div class="cc-page pb-nav" id="page-dashboard">
    <!-- Greeting Section -->
    <div class="cc-page-header animate-on-enter">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-heading font-black" id="greeting-text">Good morning!</h1>
                <p class="cc-text-muted cc-text-sm mt-1" id="greeting-sub">Let's make today count</p>
            </div>
            <span class="text-4xl animate-float" id="greeting-emoji">🌅</span>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- WISDOM TREE MINI WIDGET                    -->
    <!-- ═══════════════════════════════════════════ -->
    <a href="?page=wisdom-tree" class="block mb-5 animate-on-enter" style="text-decoration: none; color: inherit;">
        <div class="cc-card cc-card-interactive" style="background: linear-gradient(135deg, var(--cc-card-bg), var(--cc-surface)); overflow: hidden; position: relative;">
            <!-- Subtle glow behind tree -->
            <div style="position:absolute; right:-10px; top:-10px; width:100px; height:100px; background: radial-gradient(circle, rgba(107,144,128,0.15), transparent 70%); border-radius: 50%;"></div>
            <div class="flex items-center gap-4">
                <!-- Tree Emoji -->
                <div class="relative flex-shrink-0" style="width: 60px; height: 60px;">
                    <div style="position:absolute;inset:0;background:radial-gradient(circle,rgba(107,144,128,0.12),transparent 70%);border-radius:50%;"></div>
                    <span class="text-5xl absolute inset-0 flex items-center justify-center animate-float" id="dash-tree-emoji">🌱</span>
                </div>
                <!-- Tree Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-heading font-bold text-sm" id="dash-tree-stage">Seed</p>
                        <span class="cc-text-xs cc-text-muted font-heading" id="dash-tree-xp-text">0 / 50 XP</span>
                    </div>
                    <div class="tree-xp-bar">
                        <div class="tree-xp-fill" id="dash-tree-xp-fill" style="width: 0%;"></div>
                    </div>
                    <div class="flex items-center justify-between mt-1.5">
                        <span class="cc-text-xs cc-text-muted">🩺 <span id="dash-tree-health">100</span>% health</span>
                        <span class="cc-text-xs cc-text-primary font-heading font-bold">View Tree →</span>
                    </div>
                </div>
            </div>
        </div>
    </a>


    <!-- ═══════════════════════════════════════════ -->
    <!-- ACADEMIC WEATHER                           -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="weather-widget animate-on-enter mb-5" id="weather-widget">
        <div class="flex items-center justify-between relative z-10">
            <div>
                <p class="weather-label opacity-80 text-sm">Academic Weather</p>
                <p class="weather-temp" id="weather-temp">Calm</p>
                <p class="weather-label mt-1" id="weather-desc">Perfect study weather!</p>
            </div>
            <span class="weather-icon" id="weather-icon">☀️</span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-row animate-on-enter mb-5">
        <div class="stat-card">
            <div class="stat-value" id="stat-streak">0</div>
            <div class="stat-label">🔥 Streak</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-tasks-done">0</div>
            <div class="stat-label">✅ Done</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="stat-focus-time">0m</div>
            <div class="stat-label">⏱️ Focus</div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- CRAM RISK & HEALTH                         -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="flex gap-4 mb-5 animate-on-enter">
        <!-- Cram Risk Gauge -->
        <div class="cc-card flex-1 flex flex-col items-center py-5">
            <div class="cram-gauge">
                <svg class="cram-gauge-ring w-full h-full" viewBox="0 0 120 120">
                    <circle class="cram-gauge-bg" cx="60" cy="60" r="50"/>
                    <circle class="cram-gauge-fill" id="cram-gauge-fill" cx="60" cy="60" r="50"
                        stroke-dasharray="314.16" stroke-dashoffset="314.16"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="cram-gauge-value" id="cram-gauge-value">0%</span>
                    <span class="cram-gauge-label">Cram Risk</span>
                </div>
            </div>
        </div>

        <!-- Energy & Health -->
        <div class="cc-card flex-1 flex flex-col justify-between py-5">
            <div>
                <p class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide mb-2">Health</p>
                <div class="cc-progress mb-1">
                    <div class="cc-progress-bar" id="health-meter" style="width: 80%;"></div>
                </div>
                <p class="cc-text-xs cc-text-muted text-right" id="health-label">80%</p>
            </div>
            <div class="mt-3">
                <p class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide mb-2">Energy</p>
                <div class="energy-bar" id="energy-bar">
                    <div class="energy-segment filled"></div>
                    <div class="energy-segment filled"></div>
                    <div class="energy-segment filled"></div>
                    <div class="energy-segment filled"></div>
                    <div class="energy-segment"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cram Risk Message -->
    <div class="cc-card animate-on-enter mb-5 flex items-start gap-3" id="cram-message-card">
        <span class="text-2xl">🔮</span>
        <div>
            <p class="font-heading font-bold text-sm">Cram Forecast</p>
            <p class="cc-text-sm cc-text-muted mt-1" id="cram-message">Looking good! No cramming detected on the horizon.</p>
        </div>
    </div>

    <!-- Upcoming Deadlines -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">📅 Upcoming Deadlines</h3>
        <div id="upcoming-deadlines" class="flex flex-col gap-3">
            <div class="cc-empty">
                <span class="emoji">📭</span>
                <p class="cc-text-muted cc-text-sm">No upcoming deadlines. Enjoy the peace! ✨</p>
            </div>
        </div>
    </div>

    <!-- Backpack Stress -->
    <div class="cc-card animate-on-enter mb-5 flex items-center gap-4" id="backpack-widget">
        <div class="backpack-meter">
            <span class="backpack-icon" id="backpack-icon">🎒</span>
            <div class="backpack-weight-bar">
                <div class="backpack-weight-fill" id="backpack-fill" style="width: 20%; background: var(--cc-success);"></div>
            </div>
        </div>
        <div class="flex-1">
            <p class="font-heading font-bold text-sm">Backpack Weight</p>
            <p class="cc-text-xs cc-text-muted" id="backpack-label">Light and breezy! 🌿</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex gap-3 animate-on-enter mb-3">
        <a href="?page=focus" class="cc-btn cc-btn-primary flex-1 text-center">🎯 Focus</a>
        <a href="?page=assistant" class="cc-btn cc-btn-secondary flex-1 text-center">💬 AI Buddy</a>
        <a href="?page=tasks" class="cc-btn cc-btn-ghost flex-1 text-center border border-[var(--cc-border)]">+ Task</a>
    </div>
    <div class="flex gap-3 animate-on-enter mb-6">
        <a href="?page=calendar" class="cc-btn cc-btn-ghost flex-1 text-center border border-[var(--cc-border)]" style="font-size: 0.85rem;">📅 Calendar</a>
        <a href="?page=analytics" class="cc-btn cc-btn-ghost flex-1 text-center border border-[var(--cc-border)]" style="font-size: 0.85rem;">📊 Analytics</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    if (page !== 'dashboard') return;

    // ─── Wisdom Tree Mini Widget ──────────────────────
    const tree = CramStore.get('wisdomTree') || { stage: 0, xp: 0, health: 100 };
    const treeStages = [
        { emoji: '🌱', label: 'Seed', threshold: 0, next: 50 },
        { emoji: '🌿', label: 'Small Plant', threshold: 50, next: 150 },
        { emoji: '🌳', label: 'Young Tree', threshold: 150, next: 350 },
        { emoji: '✨', label: 'Wisdom Tree', threshold: 350, next: 600 },
        { emoji: '🔥', label: 'Ancient Tree', threshold: 600, next: 1000 },
    ];
    const stage = treeStages[tree.stage] || treeStages[0];
    const nextStage = treeStages[tree.stage + 1] || stage;

    const dashTreeEmoji = document.getElementById('dash-tree-emoji');
    const dashTreeStage = document.getElementById('dash-tree-stage');
    const dashTreeXpFill = document.getElementById('dash-tree-xp-fill');
    const dashTreeXpText = document.getElementById('dash-tree-xp-text');
    const dashTreeHealth = document.getElementById('dash-tree-health');

    if (dashTreeEmoji) dashTreeEmoji.textContent = stage.emoji;
    if (dashTreeStage) dashTreeStage.textContent = stage.label;
    if (dashTreeHealth) dashTreeHealth.textContent = tree.health || 100;

    const xpInStage = tree.xp - stage.threshold;
    const xpNeeded = (tree.stage < 4) ? nextStage.threshold - stage.threshold : 400;
    const xpPercent = Math.min(100, (xpInStage / xpNeeded) * 100);

    if (dashTreeXpFill) setTimeout(() => { dashTreeXpFill.style.width = xpPercent + '%'; }, 400);
    if (dashTreeXpText) dashTreeXpText.textContent = `${tree.xp} / ${(tree.stage < 4) ? nextStage.threshold : tree.xp} XP`;

    // ─── Weather Widget ──────────────────────────────
    const weather = CramState.getWeather();
    const weatherIcon = document.getElementById('weather-icon');
    const weatherTemp = document.getElementById('weather-temp');
    const weatherDesc = document.getElementById('weather-desc');
    if (weatherIcon) weatherIcon.textContent = weather.icon;
    if (weatherTemp) weatherTemp.textContent = weather.temp;
    if (weatherDesc) weatherDesc.textContent = weather.desc;

    // ─── Stats ───────────────────────────────────────
    const stats = CramStore.get('stats') || {};
    const streak = CramStore.get('streak') || {};
    document.getElementById('stat-streak').textContent = streak.current || 0;
    document.getElementById('stat-tasks-done').textContent = stats.totalTasksCompleted || 0;
    document.getElementById('stat-focus-time').textContent = CramApp.formatDuration(stats.totalFocusMinutes || 0);

    CramAnimate.countUp(document.getElementById('stat-streak'), streak.current || 0, { duration: 800 });
    CramAnimate.countUp(document.getElementById('stat-tasks-done'), stats.totalTasksCompleted || 0, { duration: 800 });

    // ─── Upcoming Deadlines ──────────────────────────
    const tasks = CramStore.get('tasks') || [];
    const upcoming = tasks
        .filter(t => !t.completed && t.deadline)
        .sort((a, b) => new Date(a.deadline) - new Date(b.deadline))
        .slice(0, 3);

    const container = document.getElementById('upcoming-deadlines');
    if (upcoming.length > 0) {
        container.innerHTML = upcoming.map(t => {
            const dl = CramApp.formatDeadline(t.deadline);
            const isOverdue = new Date(t.deadline) < new Date();
            return `<div class="cc-card flex items-center gap-3 py-3 px-4">
                <span class="text-lg">${t.priority === 'panic' ? '💀' : t.priority === 'serious' ? '😬' : '😌'}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-heading font-semibold text-sm truncate">${t.title}</p>
                    <p class="cc-text-xs ${isOverdue ? 'text-red-500 font-bold' : 'cc-text-muted'}">${dl}</p>
                </div>
            </div>`;
        }).join('');
    }

    // ─── Energy Level (time of day) ──────────────────
    const hour = new Date().getHours();
    let energyLevel = 4;
    if (hour >= 22 || hour < 6) energyLevel = 1;
    else if (hour >= 20) energyLevel = 2;
    else if (hour >= 14 && hour < 16) energyLevel = 3;
    else if (hour >= 9 && hour < 12) energyLevel = 5;

    const energyBar = document.getElementById('energy-bar');
    if (energyBar) {
        const segments = energyBar.querySelectorAll('.energy-segment');
        segments.forEach((seg, i) => {
            seg.classList.remove('filled', 'low');
            if (i < energyLevel) {
                seg.classList.add(energyLevel <= 2 ? 'low' : 'filled');
            }
        });
    }
});
</script>

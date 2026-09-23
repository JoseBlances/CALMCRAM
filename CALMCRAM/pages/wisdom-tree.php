<div class="cc-page pb-nav" id="page-wisdom-tree">
    <div class="cc-page-header animate-on-enter text-center">
        <h1 class="text-2xl font-heading font-black">Wisdom Tree</h1>
        <p class="cc-text-muted cc-text-sm mt-1">Grow your knowledge, nurture your mind</p>
    </div>

    <!-- Tree Display -->
    <div class="tree-container animate-on-enter mb-4" id="tree-display">
        <div class="tree-glow"></div>
        <span class="tree-emoji" id="tree-emoji">🌱</span>
    </div>

    <div class="text-center animate-on-enter mb-6">
        <p class="tree-stage-label" id="tree-stage">Seed</p>
        <div class="tree-xp-bar mt-3">
            <div class="tree-xp-fill" id="tree-xp-fill" style="width: 0%;"></div>
        </div>
        <p class="cc-text-xs cc-text-muted mt-2" id="tree-xp-text">0 / 50 XP</p>
    </div>

    <!-- Weather -->
    <div class="cc-card animate-on-enter mb-4 flex items-center gap-3" id="tree-weather-card">
        <span class="text-2xl" id="tree-weather-icon">☀️</span>
        <div>
            <p class="font-heading font-bold text-sm">Tree Weather</p>
            <p class="cc-text-sm cc-text-muted" id="tree-weather">Sunny and warm — your tree is thriving!</p>
        </div>
    </div>

    <!-- Health -->
    <div class="cc-card animate-on-enter mb-4">
        <div class="flex items-center justify-between mb-2">
            <p class="font-heading font-bold text-sm">🩺 Tree Health</p>
            <span class="cc-text-sm font-heading font-bold" id="tree-health-value">100%</span>
        </div>
        <div class="cc-progress">
            <div class="cc-progress-bar" id="tree-health-bar" style="width: 100%;"></div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row animate-on-enter mb-5">
        <div class="stat-card">
            <div class="stat-value" id="tree-stat-tasks">0</div>
            <div class="stat-label">Tasks</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="tree-stat-sessions">0</div>
            <div class="stat-label">Sessions</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="tree-stat-streak">0</div>
            <div class="stat-label">Streak</div>
        </div>
    </div>

    <!-- Activity Feed -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">📜 Recent Activity</h3>
        <div class="flex flex-col gap-2" id="tree-activity">
            <p class="cc-text-muted cc-text-sm text-center py-4">Complete tasks and focus sessions to see activity here! 🌟</p>
        </div>
    </div>
</div>

<div class="cc-page pb-nav" id="page-analytics">
    <!-- ═══════════════════════════════════════════ -->
    <!-- PAGE HEADER                                 -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-page-header animate-on-enter">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-heading font-black">📊 Analytics</h1>
                <p class="cc-text-muted cc-text-sm mt-1">Your productivity insights</p>
            </div>
            <span class="text-4xl animate-float">🧠</span>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- DATE RANGE SELECTOR CHIPS                   -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="flex gap-2 mb-5 animate-on-enter" id="analytics-range-chips">
        <button class="cc-btn cc-btn-sm analytics-chip active" onclick="CramAnalytics.switchRange('week')">This Week</button>
        <button class="cc-btn cc-btn-sm analytics-chip" onclick="CramAnalytics.switchRange('month')">This Month</button>
        <button class="cc-btn cc-btn-sm analytics-chip" onclick="CramAnalytics.switchRange('all')">All Time</button>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- SUMMARY CARDS                               -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="stats-row animate-on-enter mb-5">
        <div class="stat-card">
            <div class="stat-value" id="an-total-focus">0m</div>
            <div class="stat-label">⏱️ Focus Time</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="an-tasks-done">0</div>
            <div class="stat-label">✅ Completed</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="an-avg-daily">0m</div>
            <div class="stat-label">📈 Avg/Day</div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- PRODUCTIVITY SCORE GAUGE                    -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-card animate-on-enter mb-5 flex flex-col items-center py-5" style="background: linear-gradient(135deg, var(--cc-card-bg), var(--cc-surface));">
        <p class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide mb-3">Productivity Score</p>
        <div class="cram-gauge" style="width: 110px; height: 110px;">
            <svg class="cram-gauge-ring w-full h-full" viewBox="0 0 120 120">
                <circle class="cram-gauge-bg" cx="60" cy="60" r="50" fill="none" stroke="var(--cc-surface)" stroke-width="10"/>
                <circle id="an-score-fill" cx="60" cy="60" r="50" fill="none" stroke="var(--cc-primary)" stroke-width="10"
                    stroke-linecap="round" stroke-dasharray="314.16" stroke-dashoffset="314.16"
                    style="transform: rotate(-90deg); transform-origin: center; transition: stroke-dashoffset 1.2s cubic-bezier(0.34, 1.56, 0.64, 1);"/>
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="cram-gauge-value font-heading font-black text-2xl" id="an-score-value">0</span>
                <span class="cc-text-xs cc-text-muted font-heading">/ 100</span>
            </div>
        </div>
        <p class="cc-text-xs cc-text-muted mt-3" id="an-score-label">Getting started...</p>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- FOCUS TIME BAR CHART                        -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">⏱️ Daily Focus Time</h3>
        <div class="cc-card" style="padding: var(--cc-gap-md) var(--cc-gap-sm);">
            <div class="an-bar-chart" id="an-focus-chart">
                <!-- Rendered dynamically -->
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- TASK COMPLETION TREND                       -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">✅ Task Completion Trend</h3>
        <div class="cc-card" style="padding: var(--cc-gap-md) var(--cc-gap-sm);">
            <div class="an-bar-chart" id="an-task-chart">
                <!-- Rendered dynamically -->
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- CATEGORY BREAKDOWN                          -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🏷️ Category Breakdown</h3>
        <div class="cc-card" id="an-category-chart">
            <!-- Rendered dynamically -->
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- BEST STUDY TIMES                            -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🔥 Best Study Times</h3>
        <div class="cc-card" style="padding: var(--cc-gap-md);" id="an-best-times">
            <!-- Rendered dynamically -->
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- STREAK CALENDAR                             -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">🔥 Activity Streak</h3>
        <div class="cc-card" style="padding: var(--cc-gap-md);" id="an-streak-cal">
            <!-- Rendered dynamically -->
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- FOCUS SESSION HISTORY                       -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-section animate-on-enter">
        <h3 class="cc-section-title">📜 Recent Focus Sessions</h3>
        <div id="an-session-history" class="flex flex-col gap-3" style="max-height: 320px; overflow-y: auto;">
            <!-- Rendered dynamically -->
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- INLINE STYLES                                    -->
<!-- ═══════════════════════════════════════════════ -->
<style>
    /* Range chips */
    .analytics-chip {
        background: var(--cc-surface);
        color: var(--cc-text);
        border-radius: var(--cc-radius-full);
        font-size: 0.78rem;
        padding: 0.4rem 0.9rem;
    }
    .analytics-chip.active {
        background: var(--cc-primary) !important;
        color: white !important;
    }

    /* Bar chart container */
    .an-bar-chart {
        display: flex;
        align-items: flex-end;
        gap: 4px;
        height: 140px;
        padding-top: 8px;
    }

    /* Individual bar column */
    .an-bar-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        justify-content: flex-end;
        position: relative;
    }

    /* Bar element */
    .an-bar {
        width: 100%;
        max-width: 32px;
        border-radius: var(--cc-radius-sm) var(--cc-radius-sm) 2px 2px;
        background: linear-gradient(180deg, var(--cc-primary), rgba(107, 144, 128, 0.6));
        min-height: 2px;
        transition: height 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }
    .dark .an-bar {
        background: linear-gradient(180deg, var(--cc-primary), rgba(126, 200, 168, 0.4));
    }

    /* Bar value tooltip */
    .an-bar-val {
        font-family: var(--cc-font-heading);
        font-size: 0.6rem;
        font-weight: 700;
        color: var(--cc-muted);
        margin-bottom: 3px;
        opacity: 0;
        transition: opacity 0.3s;
    }
    .an-bar-col:hover .an-bar-val {
        opacity: 1;
    }

    /* Bar label */
    .an-bar-label {
        font-family: var(--cc-font-heading);
        font-size: 0.6rem;
        font-weight: 600;
        color: var(--cc-muted);
        margin-top: 5px;
        white-space: nowrap;
    }

    /* Animated bar entrance */
    .an-bar.an-bar-animate {
        animation: barGrow 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
    @keyframes barGrow {
        from { height: 0 !important; }
    }

    /* Category horizontal bars */
    .an-cat-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .an-cat-row:last-child { margin-bottom: 0; }
    .an-cat-label {
        font-family: var(--cc-font-heading);
        font-size: 0.78rem;
        font-weight: 700;
        min-width: 70px;
        text-transform: capitalize;
        color: var(--cc-text);
    }
    .an-cat-bar-wrap {
        flex: 1;
        height: 12px;
        background: var(--cc-surface);
        border-radius: var(--cc-radius-full);
        overflow: hidden;
    }
    .an-cat-bar-fill {
        height: 100%;
        border-radius: var(--cc-radius-full);
        background: linear-gradient(90deg, var(--cc-primary), var(--cc-accent));
        transition: width 1s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .an-cat-count {
        font-family: var(--cc-font-heading);
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--cc-muted);
        min-width: 28px;
        text-align: right;
    }

    /* Best study times grid */
    .an-times-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 4px;
    }
    .an-time-cell {
        aspect-ratio: 1;
        border-radius: var(--cc-radius-sm);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-family: var(--cc-font-heading);
        font-size: 0.6rem;
        font-weight: 700;
        color: var(--cc-muted);
        transition: background var(--cc-transition-base);
        position: relative;
    }
    .an-time-cell .an-time-val {
        font-size: 0.72rem;
        font-weight: 800;
    }

    /* Streak calendar */
    .an-streak-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 4px;
    }
    .an-streak-header {
        font-family: var(--cc-font-heading);
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--cc-muted);
        text-align: center;
        padding-bottom: 4px;
    }
    .an-streak-day {
        aspect-ratio: 1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--cc-font-heading);
        font-size: 0.55rem;
        font-weight: 600;
        transition: background var(--cc-transition-fast);
    }
    .an-streak-day.active {
        background: var(--cc-primary);
        color: white;
    }
    .an-streak-day.inactive {
        background: var(--cc-surface);
        color: var(--cc-muted);
    }
    .an-streak-day.empty {
        background: transparent;
    }
    .an-streak-day.today-ring {
        box-shadow: inset 0 0 0 2px var(--cc-primary);
    }

    /* Session history item */
    .an-session-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0.9rem;
    }
    .an-session-icon {
        width: 36px;
        height: 36px;
        border-radius: var(--cc-radius-md);
        background: var(--cc-surface);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
</style>

<!-- ═══════════════════════════════════════════════ -->
<!-- INLINE JAVASCRIPT                               -->
<!-- ═══════════════════════════════════════════════ -->
<script>
/**
 * CramAnalytics — Productivity Tracking Dashboard
 * Pure HTML/CSS charts, reads all data from CramStore
 */
const CramAnalytics = (() => {
    // ─── State ───────────────────────────────────
    let currentRange = 'week'; // 'week' | 'month' | 'all'

    const DAY_SHORT = ['S','M','T','W','T','F','S'];
    const MONTH_NAMES = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    // ─── Initialization ─────────────────────────
    function init() {
        switchRange('week');
    }

    // ─── Switch Range & Refresh ─────────────────
    function switchRange(range) {
        currentRange = range;

        // Update chip visuals
        document.querySelectorAll('.analytics-chip').forEach(chip => chip.classList.remove('active'));
        const chips = document.querySelectorAll('.analytics-chip');
        const idx = { week: 0, month: 1, all: 2 }[range];
        if (chips[idx]) chips[idx].classList.add('active');

        // Calculate and render everything
        const metrics = calculateMetrics(range);
        renderSummaryCards(metrics);
        renderProductivityScore(metrics);
        renderBarChart('an-focus-chart', metrics.dailyFocus, { label: 'min', color: 'primary' });
        renderBarChart('an-task-chart', metrics.dailyTasks, { label: 'tasks', color: 'accent' });
        renderCategoryBreakdown(metrics.categories);
        renderBestTimes();
        renderStreakCalendar();
        renderSessionHistory();
    }

    // ─── Calculate Metrics ──────────────────────
    function calculateMetrics(range) {
        const tasks    = CramStore.get('tasks') || [];
        const sessions = CramStore.get('focusSessions') || [];
        const heatmap  = CramStore.get('heatmapData') || {};
        const now      = new Date();

        // Determine date range
        let startDate;
        if (range === 'week') {
            startDate = new Date(now);
            startDate.setDate(startDate.getDate() - 6);
        } else if (range === 'month') {
            startDate = new Date(now);
            startDate.setDate(startDate.getDate() - 29);
        } else {
            // All time: find earliest data point
            const allDates = [
                ...tasks.map(t => t.createdAt),
                ...sessions.map(s => s.startedAt),
            ].filter(Boolean).map(d => new Date(d));
            startDate = allDates.length > 0 ? new Date(Math.min(...allDates)) : new Date(now - 30 * 86400000);
        }
        startDate.setHours(0, 0, 0, 0);

        // Filter data in range
        const rangeStr = startDate.toISOString().split('T')[0];
        const filteredSessions = sessions.filter(s => s.startedAt >= rangeStr);
        const completedTasks   = tasks.filter(t => t.completed && t.completedAt && t.completedAt >= rangeStr);

        // Total focus minutes
        const totalFocus = filteredSessions.reduce((sum, s) => sum + (s.completedMinutes || 0), 0);

        // Days in range
        const dayCount = Math.max(1, Math.ceil((now - startDate) / 86400000));
        const avgDaily = Math.round(totalFocus / dayCount);

        // Daily focus & daily tasks arrays
        const numDays = range === 'week' ? 7 : range === 'month' ? 30 : Math.min(dayCount, 30);
        const dailyFocus = [];
        const dailyTasks = [];

        for (let i = numDays - 1; i >= 0; i--) {
            const d = new Date(now);
            d.setDate(d.getDate() - i);
            const ds = d.toISOString().split('T')[0];
            const dayLabel = range === 'week'
                ? DAY_SHORT[d.getDay()]
                : `${d.getDate()}`;

            // Focus for this day
            const focusMins = (heatmap[ds] || 0);
            dailyFocus.push({ label: dayLabel, value: focusMins });

            // Tasks completed this day
            const dayTaskCount = completedTasks.filter(t => t.completedAt && t.completedAt.startsWith(ds)).length;
            dailyTasks.push({ label: dayLabel, value: dayTaskCount });
        }

        // Category breakdown
        const catMap = {};
        const rangeTasks = range === 'all' ? tasks : tasks.filter(t => t.createdAt >= rangeStr);
        rangeTasks.forEach(t => {
            const cat = t.category || 'general';
            catMap[cat] = (catMap[cat] || 0) + 1;
        });
        const categories = Object.entries(catMap)
            .map(([name, count]) => ({ name, count }))
            .sort((a, b) => b.count - a.count);

        // Productivity score (0-100)
        //   Based on: consistency (active days), focus volume, task completion rate
        const activeDays = new Set();
        filteredSessions.forEach(s => {
            if (s.startedAt) activeDays.add(s.startedAt.split('T')[0]);
        });
        completedTasks.forEach(t => {
            if (t.completedAt) activeDays.add(t.completedAt.split('T')[0]);
        });

        const consistencyScore = Math.min(100, (activeDays.size / dayCount) * 100);
        const focusScore = Math.min(100, (totalFocus / (dayCount * 30)) * 100); // 30 min/day = 100%
        const totalTasksInRange = (range === 'all' ? tasks : tasks.filter(t => t.createdAt >= rangeStr)).length;
        const completionRate = totalTasksInRange > 0 ? (completedTasks.length / totalTasksInRange) * 100 : 0;

        const score = Math.round(consistencyScore * 0.35 + focusScore * 0.35 + completionRate * 0.3);

        return {
            totalFocus,
            tasksCompleted: completedTasks.length,
            avgDaily,
            dailyFocus,
            dailyTasks,
            categories,
            score,
            dayCount,
            activeDays: activeDays.size,
        };
    }

    // ─── Render Summary Cards ───────────────────
    function renderSummaryCards(metrics) {
        const focusEl = document.getElementById('an-total-focus');
        const tasksEl = document.getElementById('an-tasks-done');
        const avgEl   = document.getElementById('an-avg-daily');

        if (focusEl) focusEl.textContent = CramApp.formatDuration(metrics.totalFocus);
        if (tasksEl) tasksEl.textContent = metrics.tasksCompleted;
        if (avgEl)   avgEl.textContent = CramApp.formatDuration(metrics.avgDaily);

        // Animate numbers
        if (tasksEl) CramAnimate?.countUp(tasksEl, metrics.tasksCompleted, { duration: 800 });
    }

    // ─── Render Productivity Score ──────────────
    function renderProductivityScore(metrics) {
        const fill  = document.getElementById('an-score-fill');
        const value = document.getElementById('an-score-value');
        const label = document.getElementById('an-score-label');

        if (fill) {
            const circumference = 314.16;
            const offset = circumference - (metrics.score / 100) * circumference;
            setTimeout(() => { fill.style.strokeDashoffset = offset; }, 200);
        }
        if (value) CramAnimate?.countUp(value, metrics.score, { duration: 1000 });

        if (label) {
            if (metrics.score >= 80) label.textContent = 'Outstanding! Keep it up! 🌟';
            else if (metrics.score >= 60) label.textContent = 'Great progress! 💪';
            else if (metrics.score >= 40) label.textContent = 'Building momentum... 🚀';
            else if (metrics.score >= 20) label.textContent = 'Getting started! 🌱';
            else label.textContent = 'Start a focus session to boost this! ✨';
        }
    }

    // ─── Render Bar Chart ───────────────────────
    function renderBarChart(containerId, data, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const maxVal = Math.max(1, ...data.map(d => d.value));

        container.innerHTML = data.map((d, i) => {
            const heightPct = (d.value / maxVal) * 100;
            const barColor = options.color === 'accent'
                ? 'background: linear-gradient(180deg, var(--cc-accent), rgba(164, 195, 178, 0.5));'
                : '';
            return `<div class="an-bar-col">
                <span class="an-bar-val">${d.value}</span>
                <div class="an-bar an-bar-animate" style="height: ${heightPct}%; animation-delay: ${i * 0.05}s; ${barColor}"></div>
                <span class="an-bar-label">${d.label}</span>
            </div>`;
        }).join('');
    }

    // ─── Render Category Breakdown ──────────────
    function renderCategoryBreakdown(categories) {
        const container = document.getElementById('an-category-chart');
        if (!container) return;

        if (categories.length === 0) {
            container.innerHTML = `<div class="cc-empty">
                <span class="emoji">📂</span>
                <p class="cc-text-muted cc-text-sm">No categorized tasks yet</p>
            </div>`;
            return;
        }

        const maxCount = Math.max(1, ...categories.map(c => c.count));
        const colors = [
            'linear-gradient(90deg, var(--cc-primary), var(--cc-accent))',
            'linear-gradient(90deg, var(--cc-success), #6ee7b7)',
            'linear-gradient(90deg, var(--cc-warning), #fcd34d)',
            'linear-gradient(90deg, var(--cc-info), #93c5fd)',
            'linear-gradient(90deg, var(--cc-danger), #fca5a5)',
        ];

        container.innerHTML = categories.map((cat, i) => {
            const widthPct = (cat.count / maxCount) * 100;
            const bg = colors[i % colors.length];
            return `<div class="an-cat-row">
                <span class="an-cat-label">${cat.name}</span>
                <div class="an-cat-bar-wrap">
                    <div class="an-cat-bar-fill" style="width: 0%; background: ${bg};" data-width="${widthPct}"></div>
                </div>
                <span class="an-cat-count">${cat.count}</span>
            </div>`;
        }).join('');

        // Animate fill widths
        setTimeout(() => {
            container.querySelectorAll('.an-cat-bar-fill').forEach(bar => {
                bar.style.width = bar.dataset.width + '%';
            });
        }, 200);
    }

    // ─── Render Best Study Times ────────────────
    function renderBestTimes() {
        const container = document.getElementById('an-best-times');
        if (!container) return;

        const sessions = CramStore.get('focusSessions') || [];

        // Aggregate focus minutes by hour of day
        const hourBuckets = new Array(24).fill(0);
        sessions.forEach(s => {
            if (s.startedAt) {
                const h = new Date(s.startedAt).getHours();
                hourBuckets[h] += (s.completedMinutes || 0);
            }
        });

        const maxMins = Math.max(1, ...hourBuckets);

        // Show hours 6AM to 11PM (18 hours) in a 6-col grid
        const hourLabels = [];
        for (let h = 6; h <= 23; h++) {
            hourLabels.push(h);
        }
        // Also add 0-5 (midnight to 5AM)
        for (let h = 0; h < 6; h++) {
            hourLabels.push(h);
        }

        let html = '<div class="an-times-grid">';
        hourLabels.forEach(h => {
            const mins = hourBuckets[h];
            const intensity = mins / maxMins;
            // Map intensity to opacity
            const bgOpacity = mins > 0 ? 0.15 + intensity * 0.7 : 0.04;
            const bg = mins > 0
                ? `background: rgba(107, 144, 128, ${bgOpacity});`
                : `background: var(--cc-surface);`;
            const label = h === 0 ? '12a' : h < 12 ? `${h}a` : h === 12 ? '12p' : `${h-12}p`;

            html += `<div class="an-time-cell" style="${bg}" title="${mins} min at ${label}">
                <span class="an-time-val">${mins > 0 ? mins + 'm' : '–'}</span>
                <span>${label}</span>
            </div>`;
        });
        html += '</div>';

        container.innerHTML = html;
    }

    // ─── Render Streak Calendar ─────────────────
    function renderStreakCalendar() {
        const container = document.getElementById('an-streak-cal');
        if (!container) return;

        const now        = new Date();
        const year       = now.getFullYear();
        const month      = now.getMonth();
        const todayStr   = now.toISOString().split('T')[0];
        const heatmap    = CramStore.get('heatmapData') || {};
        const tasks      = CramStore.get('tasks') || [];

        // Build a set of "active" dates (has focus or completed tasks)
        const activeDates = new Set(Object.keys(heatmap).filter(d => heatmap[d] > 0));
        tasks.forEach(t => {
            if (t.completed && t.completedAt) {
                activeDates.add(t.completedAt.split('T')[0]);
            }
        });

        const firstDay    = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const monthName   = MONTH_NAMES[month];

        let html = `<p class="font-heading font-bold text-sm text-center mb-3">${monthName} ${year}</p>`;
        html += '<div class="an-streak-grid">';

        // Headers
        DAY_SHORT.forEach(d => {
            html += `<div class="an-streak-header">${d}</div>`;
        });

        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            html += '<div class="an-streak-day empty"></div>';
        }

        // Day cells
        for (let d = 1; d <= daysInMonth; d++) {
            const ds = `${year}-${String(month + 1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
            const isActive = activeDates.has(ds);
            const isToday  = ds === todayStr;
            const isFuture = new Date(ds) > now;

            let classes = 'an-streak-day';
            if (isFuture) {
                classes += ' empty';
            } else if (isActive) {
                classes += ' active';
            } else {
                classes += ' inactive';
            }
            if (isToday) classes += ' today-ring';

            html += `<div class="${classes}">${d}</div>`;
        }

        html += '</div>';

        // Streak info
        const streak = CramStore.get('streak') || {};
        html += `<div class="flex items-center justify-between mt-3">
            <span class="cc-text-xs cc-text-muted">🔥 Current: <strong>${streak.current || 0}</strong> day${(streak.current||0) !== 1 ? 's' : ''}</span>
            <span class="cc-text-xs cc-text-muted">🏆 Best: <strong>${streak.best || 0}</strong> day${(streak.best||0) !== 1 ? 's' : ''}</span>
        </div>`;

        container.innerHTML = html;
    }

    // ─── Render Session History ─────────────────
    function renderSessionHistory() {
        const container = document.getElementById('an-session-history');
        if (!container) return;

        const sessions = CramStore.get('focusSessions') || [];
        const tasks    = CramStore.get('tasks') || [];
        const taskMap  = {};
        tasks.forEach(t => { taskMap[t.id] = t; });

        // Show most recent 15 sessions
        const recent = [...sessions].reverse().slice(0, 15);

        if (recent.length === 0) {
            container.innerHTML = `<div class="cc-empty">
                <span class="emoji">🧘</span>
                <p class="cc-text-muted cc-text-sm">No focus sessions yet. Start one!</p>
            </div>`;
            return;
        }

        container.innerHTML = recent.map(s => {
            const task = s.taskId ? taskMap[s.taskId] : null;
            const taskTitle = task ? task.title : 'Free focus';
            const icon = s.completed ? '✅' : '⏸️';
            const mins = s.completedMinutes || 0;
            const ago  = CramApp.timeAgo(s.endedAt || s.startedAt);

            return `<div class="cc-card an-session-item">
                <div class="an-session-icon">${icon}</div>
                <div class="flex-1 min-w-0">
                    <p class="font-heading font-semibold text-sm truncate">${taskTitle}</p>
                    <p class="cc-text-xs cc-text-muted">${CramApp.formatDuration(mins)} · ${ago}</p>
                </div>
            </div>`;
        }).join('');
    }

    // ─── Auto-init ──────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';
        if (page !== 'analytics') return;
        init();
    });

    return {
        init, switchRange, calculateMetrics,
        renderBarChart, renderProductivityScore,
        renderCategoryBreakdown, renderBestTimes,
        renderStreakCalendar, renderSessionHistory,
    };
})();
</script>

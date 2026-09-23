/**
 * CramCalm — Wisdom Tree Progression System
 */
const CramWisdomTree = (() => {
    const stages = [
        { emoji: '🌱', label: 'Seed', threshold: 0, next: 50 },
        { emoji: '🌿', label: 'Small Plant', threshold: 50, next: 150 },
        { emoji: '🌳', label: 'Young Tree', threshold: 150, next: 350 },
        { emoji: '✨', label: 'Wisdom Tree', threshold: 350, next: 600 },
        { emoji: '🔥', label: 'Ancient Tree', threshold: 600, next: 1000 },
    ];

    function init() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page !== 'wisdom-tree') return;

        checkProcrastination();
        render();
    }

    function render() {
        const tree = CramStore.get('wisdomTree') || { stage: 0, xp: 0, health: 100 };
        const stats = CramStore.get('stats') || {};
        const streak = CramStore.get('streak') || {};
        const stage = stages[tree.stage] || stages[0];
        const nextStage = stages[tree.stage + 1] || stage;

        // Tree emoji
        const emojiEl = document.getElementById('tree-emoji');
        if (emojiEl) emojiEl.textContent = stage.emoji;

        // Stage label
        const stageEl = document.getElementById('tree-stage');
        if (stageEl) stageEl.textContent = stage.label;

        // XP bar
        const xpInStage = tree.xp - stage.threshold;
        const xpNeeded = (tree.stage < 4) ? nextStage.threshold - stage.threshold : 400;
        const xpPercent = Math.min(100, (xpInStage / xpNeeded) * 100);

        const xpFill = document.getElementById('tree-xp-fill');
        if (xpFill) setTimeout(() => { xpFill.style.width = xpPercent + '%'; }, 300);

        const xpText = document.getElementById('tree-xp-text');
        if (xpText) xpText.textContent = `${tree.xp} / ${(tree.stage < 4) ? nextStage.threshold : tree.xp} XP`;

        // Health
        const healthBar = document.getElementById('tree-health-bar');
        const healthValue = document.getElementById('tree-health-value');
        if (healthBar) {
            setTimeout(() => { healthBar.style.width = tree.health + '%'; }, 400);
            healthBar.style.background = tree.health > 60 ? 'var(--cc-primary)' :
                tree.health > 30 ? 'var(--cc-warning)' : 'var(--cc-danger)';
        }
        if (healthValue) healthValue.textContent = tree.health + '%';

        // Weather
        let weather;
        if (tree.health > 80) weather = { icon: '☀️', text: 'Sunny and warm — your tree is thriving!' };
        else if (tree.health > 50) weather = { icon: '⛅', text: 'Partly cloudy — keep nurturing your tree!' };
        else if (tree.health > 20) weather = { icon: '🌧️', text: 'Rainy — your tree needs more attention' };
        else weather = { icon: '⛈️', text: 'Stormy! Complete tasks to save your tree!' };

        const weatherIcon = document.getElementById('tree-weather-icon');
        const weatherText = document.getElementById('tree-weather');
        if (weatherIcon) weatherIcon.textContent = weather.icon;
        if (weatherText) weatherText.textContent = weather.text;

        // Stats
        const statTasks = document.getElementById('tree-stat-tasks');
        const statSessions = document.getElementById('tree-stat-sessions');
        const statStreak = document.getElementById('tree-stat-streak');
        if (statTasks) CramAnimate.countUp(statTasks, stats.totalTasksCompleted || 0, { duration: 800 });
        if (statSessions) CramAnimate.countUp(statSessions, stats.totalSessions || 0, { duration: 800 });
        if (statStreak) CramAnimate.countUp(statStreak, streak.current || 0, { duration: 800 });

        // Activity feed
        renderActivity();
    }

    function renderActivity() {
        const container = document.getElementById('tree-activity');
        if (!container) return;

        const tasks = (CramStore.get('tasks') || []).filter(t => t.completedAt).sort((a, b) => new Date(b.completedAt) - new Date(a.completedAt)).slice(0, 5);
        const sessions = (CramStore.get('focusSessions') || []).slice(-5).reverse();

        let items = [];
        tasks.forEach(t => {
            items.push({ text: `Completed "${t.title}" +5 XP`, icon: '✅', time: t.completedAt });
        });
        sessions.forEach(s => {
            items.push({ text: `Focus session: ${s.completedMinutes}min +${s.completed ? 10 : 5} XP`, icon: '⏱️', time: s.endedAt || s.startedAt });
        });

        items.sort((a, b) => new Date(b.time) - new Date(a.time));
        items = items.slice(0, 8);

        if (items.length === 0) {
            container.innerHTML = '<p class="cc-text-muted cc-text-sm text-center py-4">Complete tasks and focus sessions to see activity here! 🌟</p>';
            return;
        }

        container.innerHTML = items.map(item => `
            <div class="cc-card py-3 px-4 flex items-center gap-3">
                <span>${item.icon}</span>
                <div class="flex-1">
                    <p class="cc-text-sm">${item.text}</p>
                    <p class="cc-text-xs cc-text-muted">${CramApp.timeAgo(item.time)}</p>
                </div>
            </div>
        `).join('');
    }

    function checkProcrastination() {
        const tree = CramStore.get('wisdomTree') || {};
        const tasks = CramStore.get('tasks') || [];
        const now = new Date();
        const overdue = tasks.filter(t => !t.completed && t.deadline && new Date(t.deadline) < now);

        // Damage tree for overdue tasks
        if (overdue.length > 0) {
            CramStore.damageTree(overdue.length * 3);
        }

        // Check last activity
        if (tree.lastWatered) {
            const daysSince = (now - new Date(tree.lastWatered)) / (1000 * 60 * 60 * 24);
            if (daysSince > 2) {
                CramStore.damageTree(Math.floor(daysSince) * 5);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 100);
    }

    return { init, render };
})();

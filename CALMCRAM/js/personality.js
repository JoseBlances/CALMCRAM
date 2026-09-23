/**
 * CramCalm — Productivity Personality Classifier
 */
const CramPersonality = (() => {
    const types = {
        lastMinute: { label: 'Last-Minute Warrior ⚔️', emoji: '⚔️', desc: 'You thrive under pressure — but your cortisol levels disagree!' },
        grinder: { label: 'Burnout Grinder 🔥', emoji: '🔥', desc: 'Hard worker, but remember: rest is productive too.' },
        overplanner: { label: 'Overplanner 📋', emoji: '📋', desc: 'You plan beautifully — now try executing more!' },
        ghost: { label: 'Ghost Student 👻', emoji: '👻', desc: "Barely here, but there's always time to come back!" },
        balanced: { label: 'Balanced Scholar 🎓', emoji: '🎓', desc: 'Well-rounded study habits! Keep it up!' },
    };

    function evaluate() {
        const tasks = CramStore.get('tasks') || [];
        const sessions = CramStore.get('focusSessions') || [];
        const stats = CramStore.get('stats') || {};
        const completed = tasks.filter(t => t.completed);
        const incomplete = tasks.filter(t => !t.completed);

        let scores = { lastMinute: 0, grinder: 0, overplanner: 0, ghost: 0, balanced: 0 };

        // Last-Minute Warrior: tasks completed close to deadline
        completed.forEach(t => {
            if (t.deadline && t.completedAt) {
                const hoursBeforeDeadline = (new Date(t.deadline) - new Date(t.completedAt)) / 3600000;
                if (hoursBeforeDeadline < 24 && hoursBeforeDeadline >= 0) scores.lastMinute += 3;
            }
        });

        // Burnout Grinder: high focus minutes, high task count
        if ((stats.totalFocusMinutes || 0) > 300 && tasks.length > 10) scores.grinder += 5;
        if (sessions.filter(s => (s.completedMinutes || 0) >= 60).length > 3) scores.grinder += 3;

        // Overplanner: many tasks created, low completion rate
        if (tasks.length > 8 && completed.length / Math.max(1, tasks.length) < 0.3) scores.overplanner += 5;
        if (incomplete.length > completed.length * 2) scores.overplanner += 3;

        // Ghost Student: low activity
        if (tasks.length < 3 && (stats.totalSessions || 0) < 2) scores.ghost += 5;
        if ((stats.totalFocusMinutes || 0) < 30) scores.ghost += 3;

        // Balanced: decent completion rate, regular focus
        const completionRate = completed.length / Math.max(1, tasks.length);
        if (completionRate > 0.4 && (stats.totalSessions || 0) >= 3) scores.balanced += 5;
        if ((stats.totalFocusMinutes || 0) > 60 && (stats.totalFocusMinutes || 0) < 400) scores.balanced += 3;

        // Find highest
        const sorted = Object.entries(scores).sort((a, b) => b[1] - a[1]);
        const topType = sorted[0][0];

        CramStore.set('personality', { type: topType, scores });
        return topType;
    }

    function init() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page !== 'profile') return;

        const type = evaluate();
        const info = types[type] || types.balanced;

        const badge = document.getElementById('personality-badge');
        const desc = document.getElementById('personality-desc');
        if (badge) badge.textContent = info.label;
        if (desc) desc.textContent = info.desc;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 200);
    }

    return { init, evaluate, types };
})();

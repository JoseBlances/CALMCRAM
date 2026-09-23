/**
 * CramCalm — Cram Risk Detector
 */
const CramRisk = (() => {
    const messages = {
        low: [
            "Looking good! No cramming on the horizon 🌿",
            "You're ahead of the game! Keep it up 💪",
            "Future you says thanks for being responsible 🌟",
            "Smooth sailing ahead! ⛵",
        ],
        medium: [
            "Hmm, a few things are piling up... 🤔",
            "Getting warm! Maybe tackle something today? ☕",
            "Your future self is sending warning signals 📡",
            "Still recoverable — start with just 5 minutes! ⏱️",
        ],
        high: [
            "87% chance this becomes a 3AM problem 💀",
            "Future you is begging you to start now.",
            "Your textbook is collecting dust 📚",
            "At this rate, coffee won't save you ☕",
            "The cram storm is approaching... ⛈️",
        ],
        critical: [
            "🚨 CRAM ALERT: This is not a drill!",
            "Emergency mode suggested. You need a plan NOW 💀",
            "Your backpack is about to explode 🎒💥",
            "It's giving last-minute warrior energy ⚔️",
        ],
    };

    function calculate() {
        const tasks = CramStore.get('tasks') || [];
        const now = new Date();
        const sessions = CramStore.get('focusSessions') || [];

        let risk = 0;
        const incomplete = tasks.filter(t => !t.completed);
        const overdue = incomplete.filter(t => t.deadline && new Date(t.deadline) < now);
        const dueIn24h = incomplete.filter(t => {
            if (!t.deadline) return false;
            const h = (new Date(t.deadline) - now) / 3600000;
            return h > 0 && h <= 24;
        });
        const dueIn48h = incomplete.filter(t => {
            if (!t.deadline) return false;
            const h = (new Date(t.deadline) - now) / 3600000;
            return h > 24 && h <= 48;
        });

        risk += overdue.length * 20;
        risk += dueIn24h.length * 12;
        risk += dueIn48h.length * 5;
        risk += Math.min(incomplete.length * 2, 20);

        // Recent activity reduces risk
        const recent = sessions.filter(s => (now - new Date(s.endedAt || s.startedAt)) < 86400000);
        const recentMins = recent.reduce((s, r) => s + (r.completedMinutes || 0), 0);
        risk -= Math.min(recentMins / 3, 20);

        risk = Math.max(0, Math.min(100, Math.round(risk)));
        return risk;
    }

    function getMessage(risk) {
        let pool;
        if (risk >= 80) pool = messages.critical;
        else if (risk >= 50) pool = messages.high;
        else if (risk >= 25) pool = messages.medium;
        else pool = messages.low;
        return pool[Math.floor(Math.random() * pool.length)];
    }

    function getRiskColor(risk) {
        if (risk >= 80) return '#EF4444';
        if (risk >= 50) return '#F59E0B';
        if (risk >= 25) return '#FB923C';
        return '#34D399';
    }

    function init() {
        const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';
        if (page !== 'dashboard') return;

        const risk = calculate();
        const circumference = 2 * Math.PI * 50;

        // Update gauge
        const fill = document.getElementById('cram-gauge-fill');
        const value = document.getElementById('cram-gauge-value');
        const msgEl = document.getElementById('cram-message');

        if (fill) {
            const offset = circumference * (1 - risk / 100);
            fill.style.stroke = getRiskColor(risk);
            setTimeout(() => { fill.style.strokeDashoffset = offset; }, 300);
        }
        if (value) {
            CramAnimate.countUp(value, risk, { duration: 1200, suffix: '%' });
        }
        if (msgEl) {
            msgEl.textContent = getMessage(risk);
        }

        // Health meter
        const health = Math.max(0, 100 - risk);
        const healthMeter = document.getElementById('health-meter');
        const healthLabel = document.getElementById('health-label');
        if (healthMeter) setTimeout(() => { healthMeter.style.width = health + '%'; }, 400);
        if (healthLabel) healthLabel.textContent = health + '%';
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 200);
    }

    return { calculate, getMessage, getRiskColor, init };
})();

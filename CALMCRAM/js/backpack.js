/**
 * CramCalm — Backpack Stress System
 */
const CramBackpack = (() => {
    function calculate() {
        const tasks = CramStore.get('tasks') || [];
        const now = new Date();
        const incomplete = tasks.filter(t => !t.completed);
        const overdue = incomplete.filter(t => t.deadline && new Date(t.deadline) < now);

        let weight = incomplete.length * 10 + overdue.length * 25;
        weight = Math.min(100, weight);

        let level, label, color;
        if (weight >= 80) { level = 'crushing'; label = 'About to burst! 💥'; color = '#EF4444'; }
        else if (weight >= 55) { level = 'heavy'; label = 'Getting heavy... 😰'; color = '#F59E0B'; }
        else if (weight >= 30) { level = 'medium'; label = 'Manageable weight 📦'; color = '#FB923C'; }
        else { level = 'light'; label = 'Light and breezy! 🌿'; color = 'var(--cc-success)'; }

        return { weight, level, label, color };
    }

    function init() {
        const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';
        if (page !== 'dashboard' && page !== 'tasks') return;

        const data = calculate();
        const fill = document.getElementById('backpack-fill');
        const label = document.getElementById('backpack-label');
        const icon = document.getElementById('backpack-icon');

        if (fill) {
            setTimeout(() => {
                fill.style.width = data.weight + '%';
                fill.style.background = data.color;
            }, 500);
        }
        if (label) label.textContent = data.label;
        if (icon && data.level === 'crushing') icon.classList.add('heavy');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 300);
    }

    return { calculate, init };
})();

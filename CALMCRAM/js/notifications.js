/**
 * CramCalm — Notification System
 * Emotionally intelligent toast notifications
 */
const CramNotify = (() => {
    let container = null;
    const queue = [];
    const MAX_VISIBLE = 3;
    let visibleCount = 0;

    function init() {
        container = document.getElementById('toast-container');
    }

    /**
     * Show a toast notification
     * @param {Object} options
     * @param {string} options.message - The notification text
     * @param {string} options.type - 'info' | 'success' | 'warning' | 'error' | 'motivation'
     * @param {number} options.duration - Auto-dismiss time in ms (0 = manual dismiss)
     * @param {string} options.icon - Custom emoji icon
     */
    function show(options) {
        if (!container) init();
        
        const config = {
            message: options.message || options,
            type: options.type || 'info',
            duration: options.duration ?? 4000,
            icon: options.icon || null,
        };

        if (visibleCount >= MAX_VISIBLE) {
            queue.push(config);
            return;
        }

        createToast(config);
    }

    function createToast(config) {
        visibleCount++;

        const icons = {
            info: '💡',
            success: '✅',
            warning: '⚠️',
            error: '❌',
            motivation: '🌟',
        };

        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = `
            <span class="toast-icon">${config.icon || icons[config.type] || '💬'}</span>
            <span class="toast-message">${config.message}</span>
            <button class="toast-close" aria-label="Dismiss">✕</button>
        `;

        // Dismiss on click
        toast.querySelector('.toast-close').addEventListener('click', () => dismiss(toast));

        // Auto-dismiss
        if (config.duration > 0) {
            setTimeout(() => dismiss(toast), config.duration);
        }

        container.appendChild(toast);

        // Animate entrance
        CramAnimate?.enter(toast, 'fadeUp', { duration: 0.35 });

        return toast;
    }

    function dismiss(toast) {
        if (!toast || toast.classList.contains('leaving')) return;
        
        toast.classList.add('leaving');
        
        setTimeout(() => {
            toast.remove();
            visibleCount--;

            // Show queued notifications
            if (queue.length > 0) {
                createToast(queue.shift());
            }
        }, 300);
    }

    // Convenience methods
    function success(message, icon) {
        show({ message, type: 'success', icon });
    }

    function error(message, icon) {
        show({ message, type: 'error', icon });
    }

    function warning(message, icon) {
        show({ message, type: 'warning', icon });
    }

    function info(message, icon) {
        show({ message, type: 'info', icon });
    }

    function motivate(message) {
        const messages = [
            { msg: "You're doing amazing! Keep it up! 💪", icon: "🌟" },
            { msg: "One step closer to acing it! 🎯", icon: "🔥" },
            { msg: "Your future self will thank you! 🙌", icon: "✨" },
            { msg: "Focus is your superpower! 🧠", icon: "⚡" },
            { msg: "Small wins lead to big victories! 🏆", icon: "🎉" },
        ];
        const pick = message ? { msg: message, icon: "🌟" } : messages[Math.floor(Math.random() * messages.length)];
        show({ message: pick.msg, type: 'motivation', icon: pick.icon, duration: 5000 });
    }

    // State-aware notifications
    function stateAware(message, options = {}) {
        const state = CramStore?.get('academicState') || 'stable';
        
        // Softer notifications during burnout
        if (state === 'burnout') {
            show({
                message: message,
                type: 'info',
                duration: 6000,
                ...options,
            });
        } else if (state === 'crisis') {
            show({
                message: message,
                type: 'warning',
                duration: 3000,
                ...options,
            });
        } else {
            show({
                message: message,
                type: options.type || 'info',
                ...options,
            });
        }
    }

    return {
        init, show, dismiss,
        success, error, warning, info,
        motivate, stateAware,
    };
})();

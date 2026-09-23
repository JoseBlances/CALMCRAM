/**
 * CramCalm — Academic State Engine
 * The heart of CramCalm — evaluates user condition and manages state transitions
 */
const CramState = (() => {
    const STATES = ['stable', 'pressure', 'crisis', 'burnout'];
    let currentState = 'stable';
    let is3AMMode = false;
    let evaluationInterval = null;

    function init() {
        currentState = CramStore.get('academicState') || 'stable';
        applyState(currentState);
        check3AM();
        
        // Re-evaluate every 60 seconds
        evaluationInterval = setInterval(() => {
            evaluate();
            check3AM();
        }, 60000);

        // Evaluate on task changes
        CramStore.on('tasks', () => {
            setTimeout(evaluate, 500);
        });

        // Initial evaluation
        setTimeout(evaluate, 1000);
    }

    /**
     * Evaluate current academic state based on user behavior
     */
    function evaluate() {
        const tasks = CramStore.get('tasks') || [];
        const now = new Date();
        const stats = CramStore.get('stats') || {};
        const sessions = CramStore.get('focusSessions') || [];
        
        // Calculate metrics
        const overdueTasks = tasks.filter(t => !t.completed && t.deadline && new Date(t.deadline) < now);
        const urgentTasks = tasks.filter(t => {
            if (t.completed || !t.deadline) return false;
            const hoursLeft = (new Date(t.deadline) - now) / (1000 * 60 * 60);
            return hoursLeft > 0 && hoursLeft <= 24;
        });
        const incompleteTasks = tasks.filter(t => !t.completed);
        const totalTasks = tasks.length;
        const completedRecently = tasks.filter(t => {
            if (!t.completedAt) return false;
            return (now - new Date(t.completedAt)) < 24 * 60 * 60 * 1000;
        }).length;

        // Recent focus (last 24h)
        const recentSessions = sessions.filter(s => {
            return (now - new Date(s.endedAt || s.startedAt)) < 24 * 60 * 60 * 1000;
        });
        const recentFocusMinutes = recentSessions.reduce((sum, s) => sum + (s.completedMinutes || 0), 0);

        // Score the state (0-100 stress scale)
        let stressScore = 0;

        // Overdue tasks (high weight)
        stressScore += overdueTasks.length * 15;

        // Urgent tasks (within 24h)
        stressScore += urgentTasks.length * 10;

        // Total incomplete tasks pressure
        stressScore += Math.min(incompleteTasks.length * 3, 30);

        // Low activity penalty
        if (recentFocusMinutes < 15 && incompleteTasks.length > 3) {
            stressScore += 10;
        }

        // Completion bonus (reduces stress)
        stressScore -= completedRecently * 5;

        // High focus session bonus
        if (recentFocusMinutes > 60) stressScore -= 10;

        // Clamp
        stressScore = Math.max(0, Math.min(100, stressScore));

        // Determine state
        let newState;
        if (stressScore >= 75) {
            // Check if it's been high for a while → burnout
            const prevState = currentState;
            if (prevState === 'crisis' && recentFocusMinutes > 120) {
                newState = 'burnout';
            } else {
                newState = 'crisis';
            }
        } else if (stressScore >= 40) {
            newState = 'pressure';
        } else if (currentState === 'crisis' && stressScore < 30) {
            // Recovery from crisis
            newState = 'burnout';
        } else {
            newState = 'stable';
        }

        // Manual burnout detection: too many focus minutes with high task count
        if (recentFocusMinutes > 180 && incompleteTasks.length > 5) {
            newState = 'burnout';
        }

        if (newState !== currentState) {
            transition(newState);
        }

        return { stressScore, state: currentState };
    }

    /**
     * Transition to a new state with smooth visual crossfade
     */
    function transition(newState) {
        if (!STATES.includes(newState)) return;
        
        const oldState = currentState;
        currentState = newState;
        CramStore.set('academicState', newState);

        // Apply with transition
        const root = document.documentElement;
        root.classList.add('state-transitioning');
        
        requestAnimationFrame(() => {
            applyState(newState);
            root.classList.remove('state-transitioning');
        });

        // Notify user of state change
        const stateMessages = {
            stable: { msg: "Everything looks calm. Keep it up! ☀️", icon: "☀️" },
            pressure: { msg: "Deadlines approaching. Let's stay focused! ⚡", icon: "⚡" },
            crisis: { msg: "Heavy workload detected. Let's prioritize! 🌊", icon: "🌊" },
            burnout: { msg: "You've been working hard. Take care of yourself 💜", icon: "🌙" },
        };

        if (stateMessages[newState]) {
            setTimeout(() => {
                CramNotify?.stateAware(stateMessages[newState].msg, {
                    icon: stateMessages[newState].icon,
                    duration: 5000,
                });
            }, 800);
        }

        // Emit state change event
        CramStore.emit('stateChange', newState, oldState);
    }

    /**
     * Apply state to DOM
     */
    function applyState(state) {
        const root = document.documentElement;
        root.setAttribute('data-mood', state);
        
        // Update theme-color meta
        const themeColors = {
            stable: '#6B9080',
            pressure: '#CB997E',
            crisis: '#9B72AA',
            burnout: '#A89F91',
        };
        const meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.content = themeColors[state] || themeColors.stable;
    }

    /**
     * Check for 3AM mode (10PM - 5AM)
     */
    function check3AM() {
        const hour = new Date().getHours();
        const isLateNight = hour >= 22 || hour < 5;
        
        if (isLateNight && !is3AMMode) {
            is3AMMode = true;
            document.documentElement.setAttribute('data-mood-overlay', '3am');
            
            // Show 3AM messages
            const messages = [
                "You're still awake 😭 Take care of yourself!",
                "Future you also needs sleep. Consider wrapping up 🌙",
                "Late night warrior mode activated... but please rest soon 💤",
                "The stars are out, and so are you 🌟 Don't forget to sleep!",
            ];
            setTimeout(() => {
                CramNotify?.show({
                    message: messages[Math.floor(Math.random() * messages.length)],
                    type: 'info',
                    icon: '🌙',
                    duration: 8000,
                });
            }, 2000);
        } else if (!isLateNight && is3AMMode) {
            is3AMMode = false;
            document.documentElement.removeAttribute('data-mood-overlay');
        }
    }

    /**
     * Force a specific state (for testing/emergency mode)
     */
    function forceState(state) {
        if (STATES.includes(state)) {
            transition(state);
        }
    }

    /**
     * Get academic weather based on state
     */
    function getWeather() {
        const weathers = {
            stable: { icon: '☀️', label: 'Clear Skies', temp: 'Calm', desc: 'Perfect study weather!' },
            pressure: { icon: '⛅', label: 'Partly Cloudy', temp: 'Warming', desc: 'Deadlines on the horizon' },
            crisis: { icon: '⛈️', label: 'Stormy', temp: 'Hot', desc: 'Heavy workload detected' },
            burnout: { icon: '🌧️', label: 'Rainy', temp: 'Cool', desc: 'Time to rest and recover' },
        };
        return weathers[currentState] || weathers.stable;
    }

    /**
     * Get current state
     */
    function getState() {
        return currentState;
    }

    function is3AM() {
        return is3AMMode;
    }

    return {
        init, evaluate, transition, forceState,
        getWeather, getState, is3AM, applyState,
    };
})();

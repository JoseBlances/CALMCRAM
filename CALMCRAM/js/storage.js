/**
 * CramCalm — LocalStorage + MySQL Sync Manager
 * Reactive store with event-driven updates.
 * Persists to localStorage immediately and syncs to MySQL via /api/sync.php
 */
const CramStore = (() => {
    const STORAGE_KEY = 'cramcalm_data';
    const TOKEN_KEY = 'cramcalm_guest_token';
    const listeners = new Map();

    const defaults = {
        user: {
            nickname: '',
            onboarded: false,
            createdAt: null,
        },
        settings: {
            darkMode: false,
            theme: 'auto',
            reducedMotion: false,
            notifications: true,
            focusDuration: 25,
            breakDuration: 5,
            longBreakDuration: 15,
            fontSize: 'normal',
        },
        tasks: [],
        focusSessions: [],
        wisdomTree: {
            stage: 0,
            xp: 0,
            health: 100,
            lastWatered: null,
        },
        streak: {
            current: 0,
            best: 0,
            lastDate: null,
        },
        heatmapData: {},
        personality: {
            type: null,
            scores: { lastMinute: 0, grinder: 0, overplanner: 0, ghost: 0 },
        },
        academicState: 'stable',
        stats: {
            totalFocusMinutes: 0,
            totalTasksCompleted: 0,
            totalSessions: 0,
        },
        chatHistory: [],
        ui: {
            swipeHintShown: false,
        },
    };

    let state = null;
    let syncTimer = null;
    let syncing = false;
    let serverReady = false;
    let lastSyncError = null;

    function apiBase() {
        return (typeof window !== 'undefined' && window.CRAMCALM_API)
            ? window.CRAMCALM_API.replace(/\/$/, '')
            : '/CALMCRAM/api';
    }

    function getToken() {
        try {
            return localStorage.getItem(TOKEN_KEY) || '';
        } catch (e) {
            return '';
        }
    }

    function setToken(token) {
        if (!token) return;
        try {
            localStorage.setItem(TOKEN_KEY, token);
        } catch (e) { /* ignore */ }
    }

    function authHeaders(extra = {}) {
        const headers = { ...extra };
        const token = getToken();
        if (token) headers['X-Guest-Token'] = token;
        return headers;
    }

    function load() {
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const parsed = JSON.parse(saved);
                state = deepMerge(JSON.parse(JSON.stringify(defaults)), parsed);
            } else {
                state = JSON.parse(JSON.stringify(defaults));
            }
        } catch (e) {
            console.warn('CramStore: Failed to load, using defaults', e);
            state = JSON.parse(JSON.stringify(defaults));
        }
        return state;
    }

    let saveTimer = null;
    function save() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            } catch (e) {
                console.warn('CramStore: Failed to save', e);
            }
            queueServerSync();
        }, 100);
    }

    function deepMerge(target, source) {
        for (const key in source) {
            if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                if (!target[key]) target[key] = {};
                deepMerge(target[key], source[key]);
            } else {
                target[key] = source[key];
            }
        }
        return target;
    }

    function get(path) {
        if (!state) load();
        if (!path) return state;
        return path.split('.').reduce((obj, key) => obj?.[key], state);
    }

    function set(path, value) {
        if (!state) load();
        const keys = path.split('.');
        let obj = state;
        for (let i = 0; i < keys.length - 1; i++) {
            if (!obj[keys[i]]) obj[keys[i]] = {};
            obj = obj[keys[i]];
        }
        const oldValue = obj[keys[keys.length - 1]];
        obj[keys[keys.length - 1]] = value;
        save();
        emit(path, value, oldValue);
    }

    function update(path, updates) {
        if (!state) load();
        const current = get(path);
        if (typeof current === 'object' && !Array.isArray(current)) {
            set(path, { ...current, ...updates });
        }
    }

    function on(event, callback) {
        if (!listeners.has(event)) listeners.set(event, []);
        listeners.get(event).push(callback);
        return () => {
            const cbs = listeners.get(event);
            if (cbs) cbs.splice(cbs.indexOf(callback), 1);
        };
    }

    function emit(event, ...args) {
        if (listeners.has(event)) {
            listeners.get(event).forEach(cb => cb(...args));
        }
        if (listeners.has('*')) {
            listeners.get('*').forEach(cb => cb(event, ...args));
        }
        const parts = event.split('.');
        while (parts.length > 1) {
            parts.pop();
            const parent = parts.join('.');
            if (listeners.has(parent + '.*')) {
                listeners.get(parent + '.*').forEach(cb => cb(event, ...args));
            }
        }
    }

    function addTask(task) {
        const tasks = get('tasks') || [];
        const newTask = {
            id: 'task_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
            title: task.title || '',
            description: task.description || '',
            priority: task.priority || 'chill',
            deadline: task.deadline || null,
            completed: false,
            completedAt: null,
            createdAt: new Date().toISOString(),
            category: task.category || 'general',
            subtasks: task.subtasks || [],
        };
        tasks.unshift(newTask);
        set('tasks', tasks);
        return newTask;
    }

    function updateTask(id, updates) {
        const tasks = get('tasks') || [];
        const idx = tasks.findIndex(t => t.id === id);
        if (idx !== -1) {
            tasks[idx] = { ...tasks[idx], ...updates };
            set('tasks', tasks);
            return tasks[idx];
        }
        return null;
    }

    function deleteTask(id) {
        const tasks = get('tasks') || [];
        set('tasks', tasks.filter(t => t.id !== id));
    }

    function completeTask(id) {
        return updateTask(id, {
            completed: true,
            completedAt: new Date().toISOString(),
        });
    }

    function addFocusSession(session) {
        const sessions = get('focusSessions') || [];
        const newSession = {
            id: 'focus_' + Date.now(),
            duration: session.duration || 0,
            completedMinutes: session.completedMinutes || 0,
            taskId: session.taskId || null,
            startedAt: session.startedAt || new Date().toISOString(),
            endedAt: new Date().toISOString(),
            completed: session.completed || false,
        };
        sessions.push(newSession);
        set('focusSessions', sessions);

        const stats = get('stats');
        set('stats', {
            ...stats,
            totalFocusMinutes: stats.totalFocusMinutes + newSession.completedMinutes,
            totalSessions: stats.totalSessions + 1,
        });

        const today = new Date().toISOString().split('T')[0];
        const heatmap = get('heatmapData') || {};
        heatmap[today] = (heatmap[today] || 0) + newSession.completedMinutes;
        set('heatmapData', heatmap);

        return newSession;
    }

    function addTreeXP(amount) {
        const tree = get('wisdomTree');
        tree.xp += amount;
        tree.lastWatered = new Date().toISOString();

        const thresholds = [0, 50, 150, 350, 600];
        for (let i = thresholds.length - 1; i >= 0; i--) {
            if (tree.xp >= thresholds[i]) {
                tree.stage = i;
                break;
            }
        }

        set('wisdomTree', tree);
        return tree;
    }

    function damageTree(amount) {
        const tree = get('wisdomTree');
        tree.health = Math.max(0, tree.health - amount);
        set('wisdomTree', tree);
        return tree;
    }

    function updateStreak() {
        const streak = get('streak');
        const today = new Date().toISOString().split('T')[0];
        const yesterday = new Date(Date.now() - 86400000).toISOString().split('T')[0];

        if (streak.lastDate === today) return streak;

        if (streak.lastDate === yesterday) {
            streak.current += 1;
        } else if (streak.lastDate !== today) {
            streak.current = 1;
        }

        streak.lastDate = today;
        streak.best = Math.max(streak.best, streak.current);
        set('streak', streak);
        return streak;
    }

    function queueServerSync() {
        if (!serverReady) return;
        clearTimeout(syncTimer);
        syncTimer = setTimeout(() => {
            pushToServer().catch(err => {
                lastSyncError = err;
                console.warn('CramStore: sync failed', err);
            });
        }, 800);
    }

    async function pushToServer() {
        if (syncing || !state) return;
        syncing = true;
        try {
            const res = await fetch(apiBase() + '/sync.php', {
                method: 'POST',
                headers: authHeaders({ 'Content-Type': 'application/json' }),
                body: JSON.stringify({ state, token: getToken() || undefined }),
            });
            const data = await res.json();
            if (!res.ok || !data.ok) {
                throw new Error(data.error || ('HTTP ' + res.status));
            }
            if (data.token) setToken(data.token);
            lastSyncError = null;
            emit('sync', { direction: 'push', at: data.savedAt });
        } finally {
            syncing = false;
        }
    }

    /**
     * Pull state from MySQL. Prefer DB when user already has a guest token
     * or when localStorage is empty; otherwise keep local and push up.
     */
    async function syncFromServer() {
        load();
        const hadLocal = !!localStorage.getItem(STORAGE_KEY);
        const localOnboarded = !!(state && state.user && state.user.onboarded);

        try {
            const res = await fetch(apiBase() + '/sync.php', {
                method: 'GET',
                headers: authHeaders(),
            });
            const data = await res.json();

            if (!res.ok || !data.ok) {
                throw new Error(data.error || ('HTTP ' + res.status));
            }

            if (data.token) setToken(data.token);

            const remote = data.state || {};
            const remoteOnboarded = !!(remote.user && remote.user.onboarded);

            // Prefer remote if it has progress, or local is empty
            if (remoteOnboarded || !hadLocal || !localOnboarded) {
                state = deepMerge(JSON.parse(JSON.stringify(defaults)), remote);
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                } catch (e) { /* ignore */ }
                emit('sync', { direction: 'pull' });
            } else if (localOnboarded && !remoteOnboarded) {
                // Migrate existing localStorage data into MySQL once
                serverReady = true;
                await pushToServer();
            }

            serverReady = true;
            lastSyncError = null;
            return { ok: true, state };
        } catch (err) {
            lastSyncError = err;
            serverReady = false;
            console.warn('CramStore: server unavailable, using localStorage only', err);
            return { ok: false, error: err, state };
        }
    }

    async function clearAll() {
        const token = getToken();
        localStorage.removeItem(STORAGE_KEY);
        state = JSON.parse(JSON.stringify(defaults));
        emit('reset');

        if (token) {
            try {
                await fetch(apiBase() + '/sync.php', {
                    method: 'DELETE',
                    headers: authHeaders({ 'Content-Type': 'application/json' }),
                    body: JSON.stringify({ token }),
                });
            } catch (e) {
                console.warn('CramStore: clear on server failed', e);
            }
        }
    }

    function exportData() {
        return JSON.stringify(state, null, 2);
    }

    function isServerReady() {
        return serverReady;
    }

    function getLastSyncError() {
        return lastSyncError;
    }

    // Initialize local cache immediately
    load();

    return {
        get, set, update, on, emit,
        addTask, updateTask, deleteTask, completeTask,
        addFocusSession, addTreeXP, damageTree, updateStreak,
        clearAll, exportData, load,
        syncFromServer, pushToServer, isServerReady, getLastSyncError,
    };
})();

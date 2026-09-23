/**
 * CramCalm — Focus Timer Engine v2
 * Immersive lock-screen focus mode with:
 *  - Silent notification system (icon + sender only)
 *  - Bypass contact list
 *  - In-session time adjustment
 *  - Smart adaptive break scheduling
 *  - Break screen with free phone use
 *  - "Returning to mission" countdown overlay
 */
const CramTimer = (() => {
    // ─── State ────────────────────────────────────────
    let state = 'idle'; // idle | focusing | paused | onBreak | breakReturn
    let selectedMinutes = 25;
    let totalSeconds = 0;
    let remainingSeconds = 0;
    let intervalId = null;
    let selectedTaskId = null;
    let sessionStartTime = null;
    let breaksTaken = 0;
    let totalBreakTime = 0;
    const circumference = 2 * Math.PI * 115; // SVG circle circumference

    // ─── Break System ─────────────────────────────────
    // Breaks are scheduled as { atElapsed: seconds, duration: seconds, type: 'short'|'long', taken: false }
    let breakSchedule = [];

    // ─── Notification Simulation ──────────────────────
    const fakeNotifications = [
        { app: '💬', sender: 'Mom', delay: 120 },
        { app: '📸', sender: 'bestie_03', delay: 300 },
        { app: '📧', sender: 'Prof. Johnson', delay: 480 },
        { app: '💬', sender: 'Group Chat', delay: 600 },
        { app: '🐦', sender: '@studyblr', delay: 780 },
        { app: '📱', sender: 'Alex', delay: 900 },
        { app: '🔔', sender: 'Calendar', delay: 1050 },
        { app: '💬', sender: 'Dad', delay: 1200 },
    ];
    let notifTimeouts = [];

    // ─── Clock update for lock screen ─────────────────
    let clockInterval = null;

    // ═══════════════════════════════════════════════════
    // INITIALIZATION
    // ═══════════════════════════════════════════════════
    function init() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page !== 'focus') return;

        populateTaskSelect();
        renderBypassList();
        renderHistory();
        updateBreakPreview();

        // Duration selector change updates break preview
        document.getElementById('duration-selector')?.addEventListener('click', () => {
            setTimeout(updateBreakPreview, 50);
        });
    }

    // ═══════════════════════════════════════════════════
    // PRE-FOCUS SETUP
    // ═══════════════════════════════════════════════════
    function selectDuration(btn) {
        const selector = document.getElementById('duration-selector');
        if (!selector) return;
        selector.querySelectorAll('.cc-card').forEach(b => b.classList.remove('ring-2', 'ring-[var(--cc-primary)]'));
        btn.classList.add('ring-2', 'ring-[var(--cc-primary)]');
        selectedMinutes = parseInt(btn.dataset.mins);
        document.getElementById('custom-duration').value = '';
        updateBreakPreview();
    }

    function setCustomDuration() {
        const input = document.getElementById('custom-duration');
        const val = parseInt(input?.value);
        if (!val || val < 5 || val > 180) {
            CramNotify.warning('Enter a duration between 5 and 180 minutes');
            return;
        }
        selectedMinutes = val;
        // Deselect preset buttons
        document.getElementById('duration-selector')?.querySelectorAll('.cc-card').forEach(b => {
            b.classList.remove('ring-2', 'ring-[var(--cc-primary)]');
        });
        CramNotify.info(`Custom duration: ${val} minutes ⏱️`);
        updateBreakPreview();
    }

    function populateTaskSelect() {
        const select = document.getElementById('focus-task-select');
        if (!select) return;
        const tasks = (CramStore.get('tasks') || []).filter(t => !t.completed);
        tasks.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = `${t.priority === 'panic' ? '💀' : t.priority === 'serious' ? '😬' : '😌'} ${t.title}`;
            select.appendChild(opt);
        });
    }

    /**
     * Calculate break schedule based on total focus minutes
     * Rules:
     *  ≤ 20 min  → no breaks
     *  25-30 min → 1 short break (5 min) at halfway
     *  31-45 min → 1 short break at halfway
     *  46-60 min → 2 short breaks (5 min) evenly spaced
     *  61-90 min → 2 short breaks + 1 long break (10 min) after last segment
     *  91-120 min → 3 short breaks + 1 long break
     *  120+ min → short break every 25 min + long break every 50 min
     */
    function calculateBreakSchedule(totalMins) {
        const schedule = [];
        const totalSecs = totalMins * 60;

        if (totalMins <= 20) {
            // No breaks
        } else if (totalMins <= 30) {
            // 1 short break at halfway
            schedule.push({ atElapsed: Math.floor(totalSecs / 2), duration: 5 * 60, type: 'short', taken: false });
        } else if (totalMins <= 45) {
            // 1 short break at halfway
            schedule.push({ atElapsed: Math.floor(totalSecs / 2), duration: 5 * 60, type: 'short', taken: false });
        } else if (totalMins <= 60) {
            // 2 short breaks evenly spaced (at 1/3 and 2/3)
            schedule.push({ atElapsed: Math.floor(totalSecs / 3), duration: 5 * 60, type: 'short', taken: false });
            schedule.push({ atElapsed: Math.floor((totalSecs / 3) * 2), duration: 5 * 60, type: 'short', taken: false });
        } else if (totalMins <= 90) {
            // Short breaks every ~25 min + 1 long break
            const segments = Math.floor(totalMins / 25);
            for (let i = 1; i < segments; i++) {
                const isLast = (i === segments - 1);
                schedule.push({
                    atElapsed: i * 25 * 60,
                    duration: isLast ? 10 * 60 : 5 * 60,
                    type: isLast ? 'long' : 'short',
                    taken: false
                });
            }
        } else {
            // 90+ min: short break every 25 min, long break every 50 min
            let elapsed = 25 * 60;
            let breakCount = 0;
            while (elapsed < totalSecs - 5 * 60) {
                breakCount++;
                const isLong = (breakCount % 2 === 0); // every other break is long
                schedule.push({
                    atElapsed: elapsed,
                    duration: isLong ? 10 * 60 : 5 * 60,
                    type: isLong ? 'long' : 'short',
                    taken: false
                });
                elapsed += 25 * 60;
            }
        }

        return schedule;
    }

    function updateBreakPreview() {
        const preview = document.getElementById('break-preview-text');
        if (!preview) return;

        const schedule = calculateBreakSchedule(selectedMinutes);
        if (schedule.length === 0) {
            preview.textContent = `No breaks for ${selectedMinutes} min. Pure focus! 🔥`;
        } else {
            const shorts = schedule.filter(b => b.type === 'short').length;
            const longs = schedule.filter(b => b.type === 'long').length;
            let text = '';
            if (shorts > 0) text += `${shorts} short break${shorts > 1 ? 's' : ''} (5 min)`;
            if (longs > 0) text += `${text ? ' + ' : ''}${longs} long break${longs > 1 ? 's' : ''} (10 min)`;
            text += '\n';

            // Show break times
            const times = schedule.map(b => `${b.type === 'long' ? '🟢' : '☕'} at ${Math.floor(b.atElapsed / 60)} min`);
            preview.innerHTML = text + '<br><span class="cc-text-xs opacity-70">' + times.join(' · ') + '</span>';
        }
    }

    // ═══════════════════════════════════════════════════
    // BYPASS CONTACT MANAGEMENT
    // ═══════════════════════════════════════════════════
    function getBypassList() {
        return CramStore.get('settings.bypassContacts') || ['Mom', 'Emergency'];
    }

    function renderBypassList() {
        const container = document.getElementById('bypass-list');
        if (!container) return;
        const list = getBypassList();

        if (list.length === 0) {
            container.innerHTML = '<span class="cc-text-xs cc-text-muted">No bypass contacts added</span>';
            return;
        }

        container.innerHTML = list.map(name => `
            <span class="cc-badge cc-badge-primary flex items-center gap-1">
                ${name}
                <button class="ml-1 opacity-60 hover:opacity-100 bg-transparent border-0 cursor-pointer text-inherit" 
                    onclick="CramTimer.removeBypass('${name.replace(/'/g, "\\'")}')" title="Remove">✕</button>
            </span>
        `).join('');
    }

    function addBypass() {
        const input = document.getElementById('bypass-input');
        const name = input?.value?.trim();
        if (!name) return;

        const list = getBypassList();
        if (list.includes(name)) {
            CramNotify.info(`"${name}" is already in your bypass list`);
            return;
        }
        list.push(name);
        CramStore.set('settings.bypassContacts', list);
        input.value = '';
        renderBypassList();
        CramNotify.success(`"${name}" will bypass focus mode 🔔`);
    }

    function removeBypass(name) {
        const list = getBypassList().filter(n => n !== name);
        CramStore.set('settings.bypassContacts', list);
        renderBypassList();
    }

    // ═══════════════════════════════════════════════════
    // START FOCUS SESSION
    // ═══════════════════════════════════════════════════
    function start() {
        const select = document.getElementById('focus-task-select');
        selectedTaskId = select?.value || null;
        totalSeconds = selectedMinutes * 60;
        remainingSeconds = totalSeconds;
        breakSchedule = calculateBreakSchedule(selectedMinutes);
        breaksTaken = 0;
        totalBreakTime = 0;
        sessionStartTime = new Date().toISOString();
        state = 'focusing';

        // Switch to lock screen
        document.getElementById('focus-setup').classList.add('hidden');
        document.getElementById('focus-active').classList.remove('hidden');
        document.getElementById('bottom-nav')?.classList.add('translate-y-full');

        // Set task name
        const taskNameEl = document.getElementById('focus-task-name');
        if (taskNameEl && selectedTaskId) {
            const tasks = CramStore.get('tasks') || [];
            const task = tasks.find(t => t.id === selectedTaskId);
            taskNameEl.textContent = task ? `📌 ${task.title}` : '';
        } else if (taskNameEl) {
            taskNameEl.textContent = 'Free focus session';
        }

        // Render break dots
        renderBreakDots();

        // Reset notification panel
        document.getElementById('silent-notification-list').innerHTML = '';
        document.getElementById('notif-empty-msg').classList.remove('hidden');

        // Start clock, timer, and fake notifications
        updateClock();
        clockInterval = setInterval(updateClock, 30000);
        updateDisplay();
        intervalId = setInterval(tick, 1000);
        scheduleSimulatedNotifications();

        // Try fullscreen
        try { document.documentElement.requestFullscreen?.(); } catch (e) {}

        // Sync with smartwatch
        if (typeof CramWatch !== 'undefined' && CramWatch.isConnected()) {
            const taskName = document.getElementById('focus-task-name')?.textContent || 'Focus session';
            CramWatch.syncFocusStart(selectedMinutes, taskName);
        }

        CramNotify.show({ message: "🔒 Focus mode activated! All notifications are silenced.", icon: "🔇", duration: 3000 });
    }

    // ═══════════════════════════════════════════════════
    // TIMER TICK
    // ═══════════════════════════════════════════════════
    function tick() {
        if (state !== 'focusing') return;

        remainingSeconds--;
        const elapsed = totalSeconds - remainingSeconds;

        // Check for scheduled breaks
        for (let i = 0; i < breakSchedule.length; i++) {
            if (!breakSchedule[i].taken && elapsed >= breakSchedule[i].atElapsed) {
                breakSchedule[i].taken = true;
                startBreak(breakSchedule[i]);
                return;
            }
        }

        // Timer complete
        if (remainingSeconds <= 0) {
            complete();
            return;
        }

        updateDisplay();
    }

    function updateDisplay() {
        const mins = Math.floor(Math.max(0, remainingSeconds) / 60);
        const secs = Math.max(0, remainingSeconds) % 60;
        const timeEl = document.getElementById('timer-time');
        const progressEl = document.getElementById('timer-progress');

        if (timeEl) {
            timeEl.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        if (progressEl) {
            const progress = Math.max(0, totalSeconds - remainingSeconds) / totalSeconds;
            const offset = circumference * (1 - progress);
            progressEl.style.strokeDashoffset = offset;
        }

        // Update break dots
        updateBreakDots();
    }

    function updateClock() {
        const el = document.getElementById('focus-clock');
        if (el) {
            const now = new Date();
            el.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    }

    // ═══════════════════════════════════════════════════
    // IN-SESSION TIME ADJUSTMENT
    // ═══════════════════════════════════════════════════
    function adjustTime(deltaMinutes) {
        if (state !== 'focusing' && state !== 'paused') return;

        const deltaSecs = deltaMinutes * 60;
        remainingSeconds = Math.max(60, remainingSeconds + deltaSecs); // min 1 minute
        totalSeconds = Math.max(totalSeconds, (totalSeconds - remainingSeconds) + remainingSeconds); // extend total if adding

        if (deltaMinutes > 0) {
            // Recalculate breaks for added time if we extended past original
            const newTotalMins = Math.ceil(totalSeconds / 60);
            const elapsed = totalSeconds - remainingSeconds;

            // Add new breaks if time was extended significantly
            if (deltaMinutes >= 10) {
                const newBreak = {
                    atElapsed: elapsed + Math.floor((remainingSeconds) / 2),
                    duration: 5 * 60,
                    type: 'short',
                    taken: false
                };
                // Only add if there's enough remaining time
                if (remainingSeconds > 15 * 60) {
                    breakSchedule.push(newBreak);
                    breakSchedule.sort((a, b) => a.atElapsed - b.atElapsed);
                    renderBreakDots();
                }
            }

            CramNotify.show({ message: `+${deltaMinutes} min added ⏱️`, icon: '⏰', duration: 1500 });
        } else {
            CramNotify.show({ message: `${deltaMinutes} min removed`, icon: '⏰', duration: 1500 });
        }

        updateDisplay();
    }

    // ═══════════════════════════════════════════════════
    // BREAK SYSTEM
    // ═══════════════════════════════════════════════════
    function startBreak(breakInfo) {
        clearInterval(intervalId);
        state = 'onBreak';
        breaksTaken++;

        const breakScreen = document.getElementById('break-screen');
        const breakTitle = document.getElementById('break-title');
        const breakSubtitle = document.getElementById('break-subtitle');
        const breakTypeLabel = document.getElementById('break-type-label');
        const breakTimerDisplay = document.getElementById('break-timer-display');

        if (breakInfo.type === 'long') {
            breakTitle.textContent = 'Long Break! 🎉';
            breakSubtitle.textContent = "You've been grinding hard. Take a real rest!";
            breakTypeLabel.textContent = 'Long Break (10 min)';
        } else {
            breakTitle.textContent = 'Break Time! ☕';
            breakSubtitle.textContent = "You've earned a rest. Use your phone freely!";
            breakTypeLabel.textContent = 'Short Break (5 min)';
        }

        breakScreen.classList.remove('hidden');

        // Sync break with smartwatch
        if (typeof CramWatch !== 'undefined' && CramWatch.isConnected()) {
            CramWatch.syncBreakStart(breakInfo.type);
        }

        // Break countdown
        let breakRemaining = breakInfo.duration;
        totalBreakTime += breakInfo.duration;

        const breakInterval = setInterval(() => {
            breakRemaining--;
            const m = Math.floor(breakRemaining / 60);
            const s = breakRemaining % 60;
            if (breakTimerDisplay) {
                breakTimerDisplay.textContent = `${m}:${s.toString().padStart(2, '0')}`;
            }

            // Last 6 seconds: show return countdown
            if (breakRemaining <= 5 && breakRemaining > 0) {
                showReturnCountdown(breakRemaining);
            }

            if (breakRemaining <= 0) {
                clearInterval(breakInterval);
                hideReturnCountdown();
                breakScreen.classList.add('hidden');
                resumeAfterBreak();
            }
        }, 1000);

        // Store break interval ID so we can clear it on skip
        breakScreen.dataset.intervalId = breakInterval;
    }

    function showReturnCountdown(n) {
        const overlay = document.getElementById('break-return-overlay');
        const numEl = document.getElementById('return-countdown-number');
        if (overlay) overlay.classList.remove('hidden');

        // Hide break screen when countdown shows
        document.getElementById('break-screen')?.classList.add('hidden');

        if (numEl) {
            numEl.textContent = n;
            numEl.style.animation = 'none';
            void numEl.offsetWidth; // reflow
            numEl.style.animation = 'countdown-tick 1s ease-out';
        }
    }

    function hideReturnCountdown() {
        const overlay = document.getElementById('break-return-overlay');
        if (overlay) overlay.classList.add('hidden');
    }

    function resumeAfterBreak() {
        state = 'focusing';
        const phaseEl = document.getElementById('focus-phase');
        const statusEl = document.getElementById('timer-status');
        if (phaseEl) phaseEl.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-[var(--cc-primary)] mr-2 animate-pulse"></span>Focus Time';
        if (statusEl) statusEl.textContent = 'Welcome back! 💪';
        intervalId = setInterval(tick, 1000);
        updateDisplay();

        // Sync watch back to focus
        if (typeof CramWatch !== 'undefined' && CramWatch.isConnected()) {
            CramWatch.syncBreakEnd();
        }

        CramNotify.show({ message: "Back to focus! You got this 🔒", icon: "🎯", duration: 2000 });
    }

    function skipBreak() {
        const breakScreen = document.getElementById('break-screen');
        const intId = breakScreen?.dataset.intervalId;
        if (intId) clearInterval(parseInt(intId));
        breakScreen?.classList.add('hidden');
        hideReturnCountdown();
        resumeAfterBreak();
    }

    function extendBreak() {
        // Extend break by 2 minutes by hiding countdown and restarting
        hideReturnCountdown();
        const breakScreen = document.getElementById('break-screen');
        const breakTimerDisplay = document.getElementById('break-timer-display');

        breakScreen?.classList.remove('hidden');

        let extendRemaining = 120; // 2 minutes
        totalBreakTime += 120;

        const extendInterval = setInterval(() => {
            extendRemaining--;
            const m = Math.floor(extendRemaining / 60);
            const s = extendRemaining % 60;
            if (breakTimerDisplay) breakTimerDisplay.textContent = `${m}:${s.toString().padStart(2, '0')}`;

            if (extendRemaining <= 5 && extendRemaining > 0) {
                showReturnCountdown(extendRemaining);
            }

            if (extendRemaining <= 0) {
                clearInterval(extendInterval);
                hideReturnCountdown();
                breakScreen?.classList.add('hidden');
                resumeAfterBreak();
            }
        }, 1000);

        breakScreen.dataset.intervalId = extendInterval;
        CramNotify.show({ message: "+2 minutes added to break ☕", icon: "⏰", duration: 1500 });
    }

    // ═══════════════════════════════════════════════════
    // BREAK DOTS VISUALIZATION
    // ═══════════════════════════════════════════════════
    function renderBreakDots() {
        const container = document.getElementById('break-dots');
        if (!container) return;

        if (breakSchedule.length === 0) {
            container.innerHTML = '<span class="cc-text-xs cc-text-muted opacity-60">No breaks scheduled</span>';
            return;
        }

        container.innerHTML = breakSchedule.map((b, i) => {
            const atMin = Math.floor(b.atElapsed / 60);
            return `<div class="flex items-center gap-1">
                ${i > 0 ? '<div style="width:20px;height:2px;background:var(--cc-border);"></div>' : ''}
                <div class="flex flex-col items-center">
                    <div class="w-3 h-3 rounded-full transition-all duration-500 ${b.taken ? 'bg-[var(--cc-success)]' : 'bg-[var(--cc-surface)]'}" 
                         id="break-dot-${i}" title="${b.type} break at ${atMin}min"></div>
                    <span class="cc-text-xs cc-text-muted mt-0.5" style="font-size:9px;">${atMin}m</span>
                </div>
            </div>`;
        }).join('');
    }

    function updateBreakDots() {
        breakSchedule.forEach((b, i) => {
            const dot = document.getElementById(`break-dot-${i}`);
            if (dot && b.taken) {
                dot.classList.remove('bg-[var(--cc-surface)]');
                dot.classList.add('bg-[var(--cc-success)]');
            }
        });
    }

    // ═══════════════════════════════════════════════════
    // SIMULATED SILENT NOTIFICATIONS
    // ═══════════════════════════════════════════════════
    function scheduleSimulatedNotifications() {
        clearNotifTimeouts();
        const bypassList = getBypassList();

        // Shuffle and pick 3-5 notifications
        const shuffled = [...fakeNotifications].sort(() => Math.random() - 0.5);
        const count = 3 + Math.floor(Math.random() * 3);
        const picked = shuffled.slice(0, count);

        picked.forEach((notif, i) => {
            const delay = (30 + Math.random() * 60) * (i + 1) * 1000; // Spread out
            const timeoutId = setTimeout(() => {
                if (state !== 'focusing' && state !== 'paused') return;
                addSilentNotification(notif, bypassList);
            }, delay);
            notifTimeouts.push(timeoutId);
        });
    }

    function addSilentNotification(notif, bypassList) {
        const container = document.getElementById('silent-notification-list');
        const emptyMsg = document.getElementById('notif-empty-msg');
        if (!container) return;
        if (emptyMsg) emptyMsg.classList.add('hidden');

        const isBypass = bypassList.some(name => notif.sender.toLowerCase().includes(name.toLowerCase()));

        const el = document.createElement('div');
        el.className = 'flex items-center gap-3 py-2.5 px-3 rounded-xl animate-fade-in-left';
        el.style.background = isBypass ? 'rgba(52, 211, 153, 0.1)' : 'var(--cc-surface)';
        el.style.border = isBypass ? '1px solid rgba(52, 211, 153, 0.2)' : '1px solid var(--cc-border)';

        if (isBypass) {
            // Bypass contact: show full notification style
            el.innerHTML = `
                <span class="text-lg">${notif.app}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-heading font-bold text-sm">${notif.sender}</p>
                    <p class="cc-text-xs cc-text-muted">Tap to view message</p>
                </div>
                <span class="cc-badge cc-badge-success cc-text-xs">Bypass</span>
            `;
        } else {
            // Silent notification: icon + sender only, NO message preview
            el.innerHTML = `
                <span class="text-lg opacity-50">${notif.app}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-heading font-semibold text-sm opacity-60">${notif.sender}</p>
                </div>
                <span class="cc-text-xs cc-text-muted opacity-40">🔇</span>
            `;
        }

        container.insertBefore(el, container.firstChild);

        // Keep max 6 notifications visible
        while (container.children.length > 6) {
            container.removeChild(container.lastChild);
        }
    }

    function clearNotifTimeouts() {
        notifTimeouts.forEach(id => clearTimeout(id));
        notifTimeouts = [];
    }

    // ═══════════════════════════════════════════════════
    // PAUSE / RESUME
    // ═══════════════════════════════════════════════════
    function togglePause() {
        const btn = document.getElementById('focus-pause-btn');
        const phaseEl = document.getElementById('focus-phase');

        if (state === 'focusing') {
            state = 'paused';
            clearInterval(intervalId);
            if (btn) btn.innerHTML = '▶ Resume';
            if (phaseEl) phaseEl.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-[var(--cc-warning)] mr-2"></span>Paused';
            document.getElementById('timer-status').textContent = 'Paused';
        } else if (state === 'paused') {
            state = 'focusing';
            intervalId = setInterval(tick, 1000);
            if (btn) btn.innerHTML = '⏸ Pause';
            if (phaseEl) phaseEl.innerHTML = '<span class="inline-block w-2 h-2 rounded-full bg-[var(--cc-primary)] mr-2 animate-pulse"></span>Focus Time';
            document.getElementById('timer-status').textContent = 'Stay focused';
        }
    }

    // ═══════════════════════════════════════════════════
    // STOP / COMPLETE
    // ═══════════════════════════════════════════════════
    function confirmStop() {
        const modal = document.getElementById('confirm-stop-modal');
        if (modal) modal.classList.add('active');
    }

    function stop() {
        document.getElementById('confirm-stop-modal')?.classList.remove('active');

        clearInterval(intervalId);
        clearInterval(clockInterval);
        clearNotifTimeouts();

        const elapsed = totalSeconds - remainingSeconds;
        const mins = Math.floor(elapsed / 60);

        if (mins > 0) {
            saveSession(mins, false);
        }

        // Sync watch: session stopped early
        if (typeof CramWatch !== 'undefined' && CramWatch.isConnected()) {
            CramWatch.syncFocusEnd(mins, false);
        }

        resetUI();
        CramNotify.info("Session ended. Every minute counts! 💛");
    }

    function endSession() {
        // Called from break screen
        const breakScreen = document.getElementById('break-screen');
        const intId = breakScreen?.dataset.intervalId;
        if (intId) clearInterval(parseInt(intId));
        breakScreen?.classList.add('hidden');
        hideReturnCountdown();
        stop();
    }

    function complete() {
        clearInterval(intervalId);
        clearInterval(clockInterval);
        clearNotifTimeouts();
        state = 'idle';

        const mins = Math.floor(totalSeconds / 60);
        const breakMins = Math.floor(totalBreakTime / 60);

        // Show completion popup
        const popup = document.getElementById('focus-complete-popup');
        const statsEl = document.getElementById('focus-complete-stats');
        const breaksEl = document.getElementById('focus-complete-breaks');

        if (statsEl) statsEl.textContent = `${mins} minutes of pure focus! 🏆`;
        if (breaksEl) breaksEl.textContent = `${breaksTaken} break${breaksTaken !== 1 ? 's' : ''} taken · ${breakMins} min rest time`;
        if (popup) popup.classList.add('active');

        // Hide lock screen
        document.getElementById('focus-active')?.classList.add('hidden');
        document.getElementById('bottom-nav')?.classList.remove('translate-y-full');

        saveSession(mins, true);

        // Sync watch: session complete
        if (typeof CramWatch !== 'undefined' && CramWatch.isConnected()) {
            CramWatch.syncFocusEnd(mins, true);
        }

        try { document.exitFullscreen?.(); } catch (e) {}
    }

    function saveSession(mins, completed) {
        CramStore.addFocusSession({
            duration: selectedMinutes,
            completedMinutes: mins,
            taskId: selectedTaskId,
            startedAt: sessionStartTime,
            completed: completed,
        });
        CramStore.addTreeXP(completed ? 10 : 5);
        CramStore.updateStreak();
    }

    // ═══════════════════════════════════════════════════
    // POST-SESSION ACTIONS
    // ═══════════════════════════════════════════════════
    function takePostBreak() {
        closePopup();
        resetUI();
        CramNotify.show({ message: "Enjoy your break! You earned it ☕", icon: "🎉", duration: 3000 });
    }

    function continueStudying() {
        closePopup();
        resetUI();
        setTimeout(() => start(), 300);
    }

    function markDone() {
        closePopup();
        if (selectedTaskId) {
            CramStore.completeTask(selectedTaskId);
            const stats = CramStore.get('stats');
            CramStore.set('stats.totalTasksCompleted', (stats.totalTasksCompleted || 0) + 1);
            CramNotify.success("Task marked as done! 🎉");
        }
        resetUI();
    }

    function closePopup() {
        document.getElementById('focus-complete-popup')?.classList.remove('active');
    }

    // ═══════════════════════════════════════════════════
    // RESET UI
    // ═══════════════════════════════════════════════════
    function resetUI() {
        clearInterval(intervalId);
        clearInterval(clockInterval);
        clearNotifTimeouts();
        state = 'idle';

        document.getElementById('focus-setup')?.classList.remove('hidden');
        document.getElementById('focus-active')?.classList.add('hidden');
        document.getElementById('break-screen')?.classList.add('hidden');
        document.getElementById('break-return-overlay')?.classList.add('hidden');
        document.getElementById('bottom-nav')?.classList.remove('translate-y-full');

        // Reset pause button
        const btn = document.getElementById('focus-pause-btn');
        if (btn) btn.innerHTML = '⏸ Pause';

        try { document.exitFullscreen?.(); } catch (e) {}
        renderHistory();
    }

    // ═══════════════════════════════════════════════════
    // SESSION HISTORY
    // ═══════════════════════════════════════════════════
    function renderHistory() {
        const container = document.getElementById('focus-history');
        if (!container) return;
        const sessions = (CramStore.get('focusSessions') || []).slice(-5).reverse();

        if (sessions.length === 0) {
            container.innerHTML = '<p class="cc-text-muted cc-text-sm text-center py-4">No sessions yet. Start your first one! ✨</p>';
            return;
        }

        container.innerHTML = sessions.map(s => {
            const ago = CramApp.timeAgo(s.endedAt || s.startedAt);
            return `<div class="cc-card flex items-center gap-3 py-3 px-4">
                <span class="text-lg">${s.completed ? '✅' : '⏸️'}</span>
                <div class="flex-1">
                    <p class="font-heading font-semibold text-sm">${s.completedMinutes}min ${s.completed ? 'completed' : 'partial'}</p>
                    <p class="cc-text-xs cc-text-muted">${ago}</p>
                </div>
            </div>`;
        }).join('');
    }

    // ═══════════════════════════════════════════════════
    // INIT
    // ═══════════════════════════════════════════════════
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 100);
    }

    return {
        init, selectDuration, setCustomDuration, start, togglePause,
        confirmStop, stop, endSession, adjustTime,
        skipBreak, extendBreak,
        takePostBreak, continueStudying, markDone,
        addBypass, removeBypass,
    };
})();

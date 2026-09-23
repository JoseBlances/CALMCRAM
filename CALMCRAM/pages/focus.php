<div class="cc-page pb-nav" id="page-focus">
    <!-- ═══════════════════════════════════════ -->
    <!-- PRE-FOCUS SETUP SCREEN                 -->
    <!-- ═══════════════════════════════════════ -->
    <div id="focus-setup">
        <div class="cc-page-header animate-on-enter text-center">
            <span class="text-5xl block mb-3 animate-float">🎯</span>
            <h1 class="text-2xl font-heading font-black">Lock In</h1>
            <p class="cc-text-muted cc-text-sm mt-1">Choose your focus duration and let's go</p>
        </div>

        <!-- Duration Selector -->
        <div class="cc-section animate-on-enter">
            <h3 class="cc-section-title">⏱️ Duration</h3>
            <div class="grid grid-cols-3 gap-3" id="duration-selector">
                <button class="cc-card text-center py-4 cursor-pointer transition-all hover:scale-105" data-mins="15" onclick="CramTimer.selectDuration(this)">
                    <span class="font-heading font-black text-xl block">15</span>
                    <span class="cc-text-xs cc-text-muted">minutes</span>
                </button>
                <button class="cc-card text-center py-4 cursor-pointer transition-all hover:scale-105 ring-2 ring-[var(--cc-primary)]" data-mins="25" onclick="CramTimer.selectDuration(this)">
                    <span class="font-heading font-black text-xl block">25</span>
                    <span class="cc-text-xs cc-text-muted">minutes</span>
                </button>
                <button class="cc-card text-center py-4 cursor-pointer transition-all hover:scale-105" data-mins="30" onclick="CramTimer.selectDuration(this)">
                    <span class="font-heading font-black text-xl block">30</span>
                    <span class="cc-text-xs cc-text-muted">minutes</span>
                </button>
                <button class="cc-card text-center py-4 cursor-pointer transition-all hover:scale-105" data-mins="45" onclick="CramTimer.selectDuration(this)">
                    <span class="font-heading font-black text-xl block">45</span>
                    <span class="cc-text-xs cc-text-muted">minutes</span>
                </button>
                <button class="cc-card text-center py-4 cursor-pointer transition-all hover:scale-105" data-mins="60" onclick="CramTimer.selectDuration(this)">
                    <span class="font-heading font-black text-xl block">60</span>
                    <span class="cc-text-xs cc-text-muted">minutes</span>
                </button>
                <button class="cc-card text-center py-4 cursor-pointer transition-all hover:scale-105" data-mins="90" onclick="CramTimer.selectDuration(this)">
                    <span class="font-heading font-black text-xl block">90</span>
                    <span class="cc-text-xs cc-text-muted">minutes</span>
                </button>
            </div>
            <!-- Custom Duration -->
            <div class="mt-3 flex items-center gap-2">
                <input type="number" class="cc-input text-center" id="custom-duration" placeholder="Custom" min="5" max="180" style="width: 100px;">
                <span class="cc-text-sm cc-text-muted">minutes</span>
                <button class="cc-btn cc-btn-ghost cc-btn-sm" onclick="CramTimer.setCustomDuration()">Set</button>
            </div>
        </div>

        <!-- Break Preview -->
        <div class="cc-card mb-4 animate-on-enter" id="break-preview" style="background: linear-gradient(135deg, var(--cc-surface), var(--cc-card-bg));">
            <div class="flex items-start gap-3">
                <span class="text-xl">☕</span>
                <div>
                    <p class="font-heading font-bold text-sm">Break Schedule</p>
                    <p class="cc-text-sm cc-text-muted mt-1" id="break-preview-text">5 min break at the halfway mark</p>
                </div>
            </div>
        </div>

        <!-- Task Selector -->
        <div class="cc-section animate-on-enter">
            <h3 class="cc-section-title">📝 Focus On</h3>
            <select class="cc-input" id="focus-task-select">
                <option value="">Just focusing (no specific task)</option>
            </select>
        </div>

        <!-- Focus Mode Bypass Settings -->
        <div class="cc-section animate-on-enter">
            <h3 class="cc-section-title">🔔 Notification Bypass</h3>
            <div class="cc-card">
                <p class="cc-text-sm cc-text-muted mb-3">These contacts/apps will bypass silent mode and show full notifications during focus.</p>
                <div class="flex flex-wrap gap-2 mb-3" id="bypass-list">
                    <!-- Populated by JS -->
                </div>
                <div class="flex gap-2">
                    <input type="text" class="cc-input flex-1" id="bypass-input" placeholder="Add name (e.g. Mom, Boss, Emergency)">
                    <button class="cc-btn cc-btn-secondary cc-btn-sm" onclick="CramTimer.addBypass()">Add</button>
                </div>
            </div>
        </div>

        <!-- Start Button -->
        <button class="cc-btn cc-btn-primary cc-btn-lg w-full animate-on-enter mt-4" id="start-focus-btn" onclick="CramTimer.start()">
            🔒 Enter Focus Mode
        </button>

        <!-- Session History -->
        <div class="cc-section animate-on-enter mt-8">
            <h3 class="cc-section-title">📊 Recent Sessions</h3>
            <div id="focus-history" class="flex flex-col gap-2">
                <p class="cc-text-muted cc-text-sm text-center py-4">No sessions yet. Start your first one! ✨</p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- LOCK SCREEN FOCUS MODE                 -->
    <!-- ═══════════════════════════════════════ -->
    <div id="focus-active" class="hidden" style="position:fixed;inset:0;z-index:var(--z-focus-mode);background:var(--cc-bg);overflow-y:auto;">
        
        <!-- Lock Screen Top Bar -->
        <div class="flex items-center justify-between px-5 pt-[env(safe-area-inset-top,12px)] pb-2" style="padding-top: max(env(safe-area-inset-top), 12px);">
            <span class="cc-text-xs cc-text-muted font-heading font-bold" id="focus-clock">12:00</span>
            <div class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-[var(--cc-success)] animate-pulse"></span>
                <span class="cc-text-xs font-heading font-bold cc-text-primary">FOCUS MODE</span>
            </div>
            <span class="cc-text-xs cc-text-muted">🔒</span>
        </div>

        <!-- Main Timer Area -->
        <div class="flex flex-col items-center justify-center px-6" style="min-height: calc(100vh - 280px);">
            <!-- Phase indicator -->
            <p class="cc-text-muted cc-text-sm font-heading font-bold uppercase tracking-widest mb-4" id="focus-phase">
                <span class="inline-block w-2 h-2 rounded-full bg-[var(--cc-primary)] mr-2 animate-pulse"></span>Focus Time
            </p>

            <!-- Timer Ring -->
            <div class="timer-display mb-6">
                <svg class="timer-ring" viewBox="0 0 260 260">
                    <circle class="timer-ring-bg" cx="130" cy="130" r="115"/>
                    <circle class="timer-ring-progress" id="timer-progress" cx="130" cy="130" r="115"
                        stroke-dasharray="722.57" stroke-dashoffset="0"/>
                </svg>
                <div class="timer-center">
                    <p class="timer-time" id="timer-time">25:00</p>
                    <p class="timer-label" id="timer-status">Stay focused</p>
                </div>
            </div>

            <!-- Current Task -->
            <p class="focus-task-name mb-4" id="focus-task-name"></p>

            <!-- Modify Time (inline) -->
            <div class="flex items-center gap-2 mb-6" id="modify-time-row">
                <button class="cc-btn cc-btn-ghost cc-btn-sm" onclick="CramTimer.adjustTime(-5)" title="Remove 5 min">−5</button>
                <span class="cc-text-xs cc-text-muted font-heading">Adjust</span>
                <button class="cc-btn cc-btn-ghost cc-btn-sm" onclick="CramTimer.adjustTime(5)" title="Add 5 min">+5</button>
                <button class="cc-btn cc-btn-ghost cc-btn-sm" onclick="CramTimer.adjustTime(10)" title="Add 10 min">+10</button>
            </div>

            <!-- Controls -->
            <div class="flex gap-3">
                <button class="cc-btn cc-btn-secondary" id="focus-pause-btn" onclick="CramTimer.togglePause()">⏸ Pause</button>
                <button class="cc-btn cc-btn-ghost cc-text-muted cc-text-sm" onclick="CramTimer.confirmStop()">End Session</button>
            </div>
        </div>


        <!-- Silent Notification Panel (Lock Screen Style) -->
        <div class="px-5 pb-8 mt-4">
            <div class="flex items-center justify-between mb-3">
                <p class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide">Notifications</p>
                <span class="cc-text-xs cc-text-muted" id="notif-mode-label">🔇 Silent</span>
            </div>
            <div class="flex flex-col gap-2" id="silent-notification-list">
                <!-- Silent notifications appear here, showing only icon + sender -->
            </div>
            <p class="cc-text-xs cc-text-muted text-center mt-3 opacity-60" id="notif-empty-msg">No notifications while you focus ✨</p>
        </div>

        <!-- Break schedule indicator at bottom -->
        <div class="px-5 pb-6">
            <div class="cc-card py-3 px-4" style="background: var(--cc-surface); border: none;">
                <div class="flex items-center gap-2 mb-2">
                    <span class="cc-text-xs">☕</span>
                    <p class="cc-text-xs font-heading font-bold cc-text-muted uppercase tracking-wide">Break Schedule</p>
                </div>
                <div class="flex gap-1" id="break-dots">
                    <!-- Break indicator dots populated by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- BREAK MODE SCREEN                      -->
    <!-- ═══════════════════════════════════════ -->
    <div id="break-screen" class="hidden" style="position:fixed;inset:0;z-index:calc(var(--z-focus-mode) + 1);">
        <!-- Semi-transparent overlay with blur -->
        <div style="position:absolute;inset:0;background:var(--cc-overlay);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
        
        <div class="relative z-10 flex flex-col items-center justify-center min-h-screen px-6 text-center text-white">
            <span class="text-6xl mb-4 animate-float">☕</span>
            <h2 class="font-heading font-black text-2xl mb-2" id="break-title">Break Time!</h2>
            <p class="opacity-80 mb-2" id="break-subtitle">You've earned a rest. Use your phone freely!</p>
            
            <!-- Break timer -->
            <div class="mt-4 mb-6">
                <p class="font-heading font-black text-5xl" id="break-timer-display">5:00</p>
                <p class="cc-text-sm opacity-60 mt-1" id="break-type-label">Short Break</p>
            </div>

            <p class="cc-text-sm opacity-60 mb-6">App will gently remind you when break ends</p>

            <div class="flex gap-3">
                <button class="cc-btn cc-btn-secondary" onclick="CramTimer.skipBreak()" style="color: white; border-color: rgba(255,255,255,0.3);">Skip Break →</button>
                <button class="cc-btn" onclick="CramTimer.endSession()" style="color: rgba(255,255,255,0.6); background:transparent;">End Session</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- BREAK ENDING COUNTDOWN OVERLAY         -->
    <!-- ═══════════════════════════════════════ -->
    <div id="break-return-overlay" class="hidden" style="position:fixed;inset:0;z-index:calc(var(--z-focus-mode) + 2);background:rgba(0,0,0,0.85);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);">
        <div class="flex flex-col items-center justify-center min-h-screen text-white text-center px-6">
            <p class="font-heading font-bold text-lg opacity-70 mb-2">Returning to mission in</p>
            <p class="font-heading font-black text-[8rem] leading-none" id="return-countdown-number" style="text-shadow: 0 0 60px rgba(107,144,128,0.5);">5</p>
            <p class="cc-text-sm opacity-50 mt-4">Get ready to lock back in 🔒</p>
            <button class="cc-btn cc-btn-ghost mt-6" onclick="CramTimer.extendBreak()" style="color: rgba(255,255,255,0.5);">Need more time? +2 min</button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- SESSION COMPLETE POPUP                 -->
    <!-- ═══════════════════════════════════════ -->
    <div class="cc-modal-overlay" id="focus-complete-popup">
        <div class="cc-modal text-center">
            <div class="cc-modal-handle"></div>
            <span class="text-6xl block mb-4 animate-pop-in">🚀</span>
            <h2 class="font-heading font-black text-xl mb-2">TIME'S UP!</h2>
            <p class="cc-text-muted mb-1">You survived the focus zone.</p>
            <p class="font-heading font-bold cc-text-primary mb-2" id="focus-complete-stats"></p>
            <p class="cc-text-sm cc-text-muted mb-6" id="focus-complete-breaks"></p>
            <div class="flex flex-col gap-3">
                <button class="cc-btn cc-btn-secondary w-full" onclick="CramTimer.takePostBreak()">☕ Take a Break</button>
                <button class="cc-btn cc-btn-primary w-full" onclick="CramTimer.continueStudying()">📚 Continue Studying</button>
                <button class="cc-btn cc-btn-ghost w-full" onclick="CramTimer.markDone()">✅ Mark Task as Done</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════ -->
    <!-- CONFIRM END SESSION MODAL              -->
    <!-- ═══════════════════════════════════════ -->
    <div class="cc-modal-overlay" id="confirm-stop-modal">
        <div class="cc-modal text-center">
            <div class="cc-modal-handle"></div>
            <span class="text-5xl block mb-3">🤔</span>
            <h3 class="font-heading font-bold text-lg mb-2">End focus session?</h3>
            <p class="cc-text-sm cc-text-muted mb-6">You still have time left. Your progress will be saved.</p>
            <div class="flex gap-3">
                <button class="cc-btn cc-btn-secondary flex-1" onclick="document.getElementById('confirm-stop-modal').classList.remove('active')">Keep Going 💪</button>
                <button class="cc-btn cc-btn-danger flex-1" onclick="CramTimer.stop()">End Session</button>
            </div>
        </div>
    </div>
</div>

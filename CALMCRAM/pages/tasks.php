<div class="cc-page pb-nav" id="page-tasks">
    <!-- Header -->
    <div class="cc-page-header animate-on-enter">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-heading font-black">My Tasks</h1>
                <span class="cc-badge cc-badge-primary" id="task-count-badge">0</span>
            </div>
            <button class="cc-btn cc-btn-primary cc-btn-sm" onclick="CramTasks.openModal()">
                <span class="text-lg leading-none">+</span> Add
            </button>
        </div>
    </div>

    <!-- Progress Summary Card -->
    <div class="cc-card mb-4 animate-on-enter" id="task-progress-card">
        <div class="flex items-center justify-between mb-3">
            <p class="font-heading font-bold text-sm">📊 Today's Progress</p>
            <span class="cc-text-xs cc-text-muted font-heading font-bold" id="task-progress-pct">0%</span>
        </div>
        <div class="cc-progress mb-2">
            <div class="cc-progress-bar" id="task-progress-bar" style="width: 0%;"></div>
        </div>
        <div class="flex justify-between">
            <span class="cc-text-xs cc-text-muted" id="task-progress-label">0 of 0 tasks done</span>
            <span class="cc-text-xs cc-text-muted" id="task-progress-streak"></span>
        </div>
    </div>

    <!-- Recovery Banner (hidden by default) -->
    <div class="cc-card mb-4 animate-on-enter hidden" id="recovery-banner" style="background: linear-gradient(135deg, var(--cc-accent), var(--cc-primary)); color: white;">
        <div class="flex items-start gap-3">
            <span class="text-2xl">💜</span>
            <div class="flex-1">
                <p class="font-heading font-bold text-sm">You fell behind. Let's recover step by step.</p>
                <p class="text-xs opacity-90 mt-1">No judgment — just pick one small thing to start.</p>
                <button class="mt-2 text-xs font-heading font-bold underline opacity-90" onclick="CramTasks.showOverdueOnly()">View overdue tasks →</button>
            </div>
        </div>
    </div>

    <!-- Quick Add (inline) -->
    <div class="flex gap-2 mb-4 animate-on-enter" id="quick-add-row">
        <input type="text" class="cc-input flex-1" id="quick-add-input" placeholder="Quick add a task..." 
            onkeydown="if(event.key==='Enter') CramTasks.quickAdd()">
        <button class="cc-btn cc-btn-primary cc-btn-sm" onclick="CramTasks.quickAdd()" style="padding: 0.75rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M12 5v14M5 12h14"/>
            </svg>
        </button>
    </div>

    <!-- Filter Chips -->
    <div class="chip-row mb-4 animate-on-enter overflow-x-auto flex-nowrap pb-2" id="task-filters" style="-webkit-overflow-scrolling: touch;">
        <button class="chip active" data-filter="all">📋 All</button>
        <button class="chip" data-filter="today">🔆 Today</button>
        <button class="chip" data-filter="upcoming">📅 Upcoming</button>
        <button class="chip" data-filter="overdue">🔴 Overdue <span class="overdue-count-chip hidden" id="overdue-chip-count"></span></button>
        <button class="chip" data-filter="completed">✅ Done</button>
    </div>

    <!-- Sort Row -->
    <div class="flex items-center justify-between mb-3 animate-on-enter">
        <p class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide" id="task-filter-label">All Tasks</p>
        <div class="flex items-center gap-2">
            <button class="cc-text-xs cc-text-muted font-heading cursor-pointer bg-transparent border-0" id="sort-btn" onclick="CramTasks.cycleSort()" title="Change sort order">
                ↕ <span id="sort-label">Priority</span>
            </button>
        </div>
    </div>

    <!-- Task List -->
    <div class="flex flex-col gap-3" id="task-list">
        <div class="cc-empty" id="task-empty">
            <span class="emoji">📝</span>
            <p class="font-heading font-bold">No tasks yet!</p>
            <p class="cc-text-muted cc-text-sm mt-1">Use the quick add bar or tap + Add</p>
        </div>
    </div>

    <!-- Completed section separator -->
    <div class="hidden mt-6 mb-3" id="completed-section-header">
        <div class="flex items-center gap-3">
            <hr class="flex-1 border-0 h-px" style="background: var(--cc-border);">
            <button class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide bg-transparent border-0 cursor-pointer flex items-center gap-1" 
                onclick="CramTasks.toggleCompleted()" id="completed-toggle-btn">
                👻 Finished <span id="completed-count">0</span>
                <span id="completed-chevron" style="transition: transform 0.3s; transform: rotate(180deg);">▾</span>
            </button>
            <hr class="flex-1 border-0 h-px" style="background: var(--cc-border);">
        </div>
        <p class="cc-text-xs cc-text-muted text-center mt-2">Ghost tasks — reminders of what you’ve finished. Tap the check to restore.</p>
    </div>
    <div class="flex flex-col gap-3 hidden" id="completed-task-list"></div>

    <!-- Backpack Stress Widget -->
    <div class="cc-card mt-6 animate-on-enter" id="task-backpack-widget">
        <div class="flex items-center gap-3">
            <div class="backpack-meter">
                <span class="backpack-icon" id="backpack-icon">🎒</span>
            </div>
            <div class="flex-1">
                <p class="font-heading font-bold text-sm">Backpack Weight</p>
                <p class="cc-text-xs cc-text-muted" id="backpack-desc">Light and breezy!</p>
                <div class="backpack-weight-bar mt-1">
                    <div class="backpack-weight-fill" id="backpack-fill" style="width: 10%; background: var(--cc-success);"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Task Modal -->
<div class="cc-modal-overlay" id="task-modal">
    <div class="cc-modal">
        <div class="cc-modal-handle"></div>
        <h3 class="font-heading font-bold text-lg mb-1" id="modal-title">New Task</h3>
        <p class="cc-text-xs cc-text-muted mb-4" id="modal-subtitle">What's on your plate?</p>
        <form id="task-form" onsubmit="return false;">
            <input type="hidden" id="task-edit-id" value="">
            
            <div class="mb-4">
                <label class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide block mb-1.5">What do you need to do?</label>
                <input type="text" class="cc-input" id="task-title-input" placeholder="e.g., Study Chapter 5 for Biology" required maxlength="120">
                <p class="cc-text-xs cc-text-muted mt-1 text-right" id="title-char-count"></p>
            </div>

            <div class="mb-4">
                <label class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide block mb-1.5">Priority</label>
                <div class="grid grid-cols-3 gap-2" id="priority-selector">
                    <button type="button" class="cc-card text-center py-3 cursor-pointer transition-all active" data-priority="chill" style="padding: 0.65rem 0.5rem;">
                        <span class="text-xl block mb-0.5">😌</span>
                        <span class="cc-text-xs font-heading font-bold">Chill</span>
                    </button>
                    <button type="button" class="cc-card text-center py-3 cursor-pointer transition-all" data-priority="serious" style="padding: 0.65rem 0.5rem;">
                        <span class="text-xl block mb-0.5">😬</span>
                        <span class="cc-text-xs font-heading font-bold">Serious</span>
                    </button>
                    <button type="button" class="cc-card text-center py-3 cursor-pointer transition-all" data-priority="panic" style="padding: 0.65rem 0.5rem;">
                        <span class="text-xl block mb-0.5">💀</span>
                        <span class="cc-text-xs font-heading font-bold">Panic</span>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide block mb-2">📅 When is it due?</label>
                
                <!-- Quick Date Chips -->
                <div class="flex gap-2 mb-3 overflow-x-auto flex-nowrap pb-1" style="-webkit-overflow-scrolling: touch;" id="quick-date-chips">
                    <button type="button" class="chip cc-text-xs flex-shrink-0 whitespace-nowrap" onclick="CramTasks.setQuickDeadline('today', this)">
                        🔆 Today
                    </button>
                    <button type="button" class="chip cc-text-xs flex-shrink-0 whitespace-nowrap" onclick="CramTasks.setQuickDeadline('tomorrow', this)">
                        📅 Tomorrow
                    </button>
                    <button type="button" class="chip cc-text-xs flex-shrink-0 whitespace-nowrap" onclick="CramTasks.setQuickDeadline('in3days', this)">
                        📆 In 3 Days
                    </button>
                    <button type="button" class="chip cc-text-xs flex-shrink-0 whitespace-nowrap" onclick="CramTasks.setQuickDeadline('week', this)">
                        🗓️ This Sunday
                    </button>
                    <button type="button" class="chip cc-text-xs flex-shrink-0 whitespace-nowrap" onclick="CramTasks.setQuickDeadline('nextweek', this)">
                        ⏭️ Next Week
                    </button>
                    <button type="button" class="chip cc-text-xs flex-shrink-0 whitespace-nowrap" onclick="CramTasks.setQuickDeadline('in2weeks', this)">
                        📋 In 2 Weeks
                    </button>
                </div>

                <!-- Date + Time Inputs -->
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="cc-text-xs cc-text-muted block mb-1">Date</label>
                        <input type="date" class="cc-input" id="task-deadline-date" style="padding: 0.65rem 0.75rem;">
                    </div>
                    <div>
                        <label class="cc-text-xs cc-text-muted block mb-1">Time</label>
                        <input type="time" class="cc-input" id="task-deadline-time" value="23:59" style="padding: 0.65rem 0.75rem;">
                    </div>
                </div>

                <!-- Time of Day Presets -->
                <div class="flex gap-1.5 mb-3 flex-wrap" id="time-preset-chips">
                    <button type="button" class="chip cc-text-xs text-center" style="padding: 0.4rem 0.65rem; flex: 1 1 calc(50% - 0.4rem); min-width: 0;" onclick="CramTasks.setTimePreset('08:00', this)">
                        🌅 Morning
                    </button>
                    <button type="button" class="chip cc-text-xs text-center" style="padding: 0.4rem 0.65rem; flex: 1 1 calc(50% - 0.4rem); min-width: 0;" onclick="CramTasks.setTimePreset('13:00', this)">
                        ☀️ Afternoon
                    </button>
                    <button type="button" class="chip cc-text-xs text-center" style="padding: 0.4rem 0.65rem; flex: 1 1 calc(50% - 0.4rem); min-width: 0;" onclick="CramTasks.setTimePreset('18:00', this)">
                        🌆 Evening
                    </button>
                    <button type="button" class="chip cc-text-xs text-center active" style="padding: 0.4rem 0.65rem; flex: 1 1 calc(50% - 0.4rem); min-width: 0;" onclick="CramTasks.setTimePreset('23:59', this)">
                        🌙 End of Day
                    </button>
                </div>

                <!-- Deadline Preview Card (shows after picking) -->
                <div class="hidden rounded-xl py-2.5 px-3 mt-2 transition-all" id="deadline-preview" style="border: 1px dashed var(--cc-border);">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-lg" id="deadline-preview-icon">📅</span>
                            <div>
                                <p class="font-heading font-bold text-sm" id="deadline-preview-date">-</p>
                                <p class="cc-text-xs" id="deadline-preview-countdown" style="color: var(--cc-muted);">-</p>
                            </div>
                        </div>
                        <button type="button" class="cc-text-xs cc-text-muted bg-transparent border-0 cursor-pointer opacity-60 hover:opacity-100" onclick="CramTasks.clearDeadline()">✕ Clear</button>
                    </div>
                    <!-- Priority suggestion integrated -->
                    <p class="cc-text-xs mt-1.5 font-heading font-semibold" id="priority-suggestion"></p>
                </div>
                <!-- Hidden combined input for form submission -->
                <input type="hidden" id="task-deadline-input">
            </div>

            <div class="mb-4">
                <label class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide block mb-1.5">Subject / Category</label>
                <div class="flex gap-2 flex-wrap" id="category-chips">
                    <button type="button" class="chip cc-text-xs" data-cat="Math" onclick="CramTasks.selectCategory(this)">📐 Math</button>
                    <button type="button" class="chip cc-text-xs" data-cat="Science" onclick="CramTasks.selectCategory(this)">🔬 Science</button>
                    <button type="button" class="chip cc-text-xs" data-cat="English" onclick="CramTasks.selectCategory(this)">📖 English</button>
                    <button type="button" class="chip cc-text-xs" data-cat="History" onclick="CramTasks.selectCategory(this)">🏛️ History</button>
                    <button type="button" class="chip cc-text-xs" data-cat="Art" onclick="CramTasks.selectCategory(this)">🎨 Art</button>
                    <button type="button" class="chip cc-text-xs" data-cat="Other" onclick="CramTasks.selectCategory(this)">📌 Other</button>
                </div>
            </div>

            <div class="mb-6">
                <label class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide block mb-1.5">Notes (optional)</label>
                <textarea class="cc-input cc-textarea" id="task-desc-input" placeholder="Break it down, add details..." rows="2" maxlength="500"></textarea>
            </div>

            <div class="flex gap-3">
                <button type="button" class="cc-btn cc-btn-secondary flex-1" onclick="CramTasks.closeModal()">Cancel</button>
                <button type="submit" class="cc-btn cc-btn-primary flex-1" onclick="CramTasks.saveTask()">
                    <span id="save-btn-text">Save Task</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation -->
<div class="cc-modal-overlay" id="delete-confirm-modal">
    <div class="cc-modal text-center">
        <div class="cc-modal-handle"></div>
        <span class="text-5xl block mb-3">🗑️</span>
        <h3 class="font-heading font-bold text-lg mb-2">Delete this task?</h3>
        <p class="cc-text-sm cc-text-muted mb-6" id="delete-task-name"></p>
        <div class="flex gap-3">
            <button class="cc-btn cc-btn-secondary flex-1" onclick="document.getElementById('delete-confirm-modal').classList.remove('active')">Keep It</button>
            <button class="cc-btn cc-btn-danger flex-1" id="delete-confirm-btn">Delete</button>
        </div>
    </div>
</div>

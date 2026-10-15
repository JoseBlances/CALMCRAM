/**
 * CramCalm — Task Management Engine v2
 * Refined with: quick-add, sorting, categories, progress tracking,
 * collapsible completed section, delete confirmation, and polish.
 */
const CramTasks = (() => {
    let currentFilter = 'all';
    let selectedPriority = 'chill';
    let selectedCategory = '';
    let currentSort = 'priority'; // priority | deadline | created
    let showCompleted = true;

    const sortModes = ['priority', 'deadline', 'created'];
    const priorityOrder = { panic: 0, serious: 1, chill: 2 };

    // ═══════════════════════════════════════════════════
    // INIT
    // ═══════════════════════════════════════════════════
    function init() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page !== 'tasks') return;

        setupFilters();
        setupPrioritySelector();
        setupDeadlineSuggestion();
        setupCharCount();
        renderTasks(currentFilter);
        updateProgress();
        updateBackpack();
        checkRecovery();
    }

    // ═══════════════════════════════════════════════════
    // SETUP
    // ═══════════════════════════════════════════════════
    function setupFilters() {
        const filters = document.getElementById('task-filters');
        if (!filters) return;
        filters.addEventListener('click', (e) => {
            const chip = e.target.closest('.chip');
            if (!chip) return;
            filters.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            currentFilter = chip.dataset.filter;
            renderTasks(currentFilter);
            updateFilterLabel();
        });
    }

    function setupPrioritySelector() {
        const selector = document.getElementById('priority-selector');
        if (!selector) return;
        selector.addEventListener('click', (e) => {
            const card = e.target.closest('[data-priority]');
            if (!card) return;
            selector.querySelectorAll('[data-priority]').forEach(c => c.classList.remove('active', 'ring-2', 'ring-[var(--cc-primary)]'));
            card.classList.add('active', 'ring-2', 'ring-[var(--cc-primary)]');
            selectedPriority = card.dataset.priority;
        });
    }

    function setupDeadlineSuggestion() {
        const dateInput = document.getElementById('task-deadline-date');
        const timeInput = document.getElementById('task-deadline-time');
        if (!dateInput) return;

        const onDeadlineChange = () => updateDeadlinePreview();
        dateInput.addEventListener('change', onDeadlineChange);
        if (timeInput) timeInput.addEventListener('change', onDeadlineChange);
    }

    function updateDeadlinePreview() {
        const dateInput = document.getElementById('task-deadline-date');
        const timeInput = document.getElementById('task-deadline-time');
        const preview = document.getElementById('deadline-preview');
        const previewDate = document.getElementById('deadline-preview-date');
        const previewCountdown = document.getElementById('deadline-preview-countdown');
        const previewIcon = document.getElementById('deadline-preview-icon');
        const suggestion = document.getElementById('priority-suggestion');
        const hiddenInput = document.getElementById('task-deadline-input');

        if (!dateInput?.value) {
            if (preview) preview.classList.add('hidden');
            if (hiddenInput) hiddenInput.value = '';
            return;
        }

        // Combine date + time into Date object
        const timeVal = timeInput?.value || '23:59';
        const dl = new Date(`${dateInput.value}T${timeVal}`);
        const now = new Date();
        const diffMs = dl - now;
        const diffHours = diffMs / (1000 * 60 * 60);
        const diffDays = diffMs / (1000 * 60 * 60 * 24);

        // Update hidden combined input
        if (hiddenInput) {
            hiddenInput.value = dl.toISOString();
        }

        // Show preview
        if (preview) preview.classList.remove('hidden');

        // Format date display
        const dayName = dl.toLocaleDateString('en', { weekday: 'long' });
        const dateStr = dl.toLocaleDateString('en', { month: 'short', day: 'numeric', year: dl.getFullYear() !== now.getFullYear() ? 'numeric' : undefined });
        const timeStr = dl.toLocaleTimeString('en', { hour: 'numeric', minute: '2-digit' });

        if (previewDate) previewDate.textContent = `${dayName}, ${dateStr} at ${timeStr}`;

        // Countdown text + urgency color
        let countdownText, icon, borderColor, suggestionText, suggestionColor;

        if (diffMs < 0) {
            const agoHours = Math.abs(diffHours);
            if (agoHours < 1) { countdownText = '⚠️ Just passed!'; }
            else if (agoHours < 24) { countdownText = `⚠️ ${Math.floor(agoHours)} hours overdue`; }
            else { countdownText = `⚠️ ${Math.floor(Math.abs(diffDays))} days overdue`; }
            icon = '🔴'; borderColor = 'var(--cc-danger)';
            suggestionText = '⚠️ This deadline has already passed!';
            suggestionColor = 'var(--cc-danger)';
        } else if (diffHours < 3) {
            countdownText = `🔥 Due in ${Math.max(1, Math.floor(diffHours * 60))} minutes!`;
            icon = '🔥'; borderColor = 'var(--cc-danger)';
            suggestionText = '💀 Panic Mode — due very soon!';
            suggestionColor = 'var(--cc-danger)';
            autoPriority('panic');
        } else if (diffHours < 24) {
            countdownText = `⏰ Due in ${Math.floor(diffHours)} hours`;
            icon = '⏰'; borderColor = 'var(--cc-danger)';
            suggestionText = '💀 Suggested: Panic Mode (due today)';
            suggestionColor = 'var(--cc-danger)';
            autoPriority('panic');
        } else if (diffDays < 2) {
            countdownText = `⚡ Due tomorrow`;
            icon = '⚡'; borderColor = 'var(--cc-warning)';
            suggestionText = '💀 Suggested: Panic Mode (< 2 days)';
            suggestionColor = 'var(--cc-danger)';
            autoPriority('panic');
        } else if (diffDays < 5) {
            countdownText = `📅 ${Math.ceil(diffDays)} days from now`;
            icon = '📅'; borderColor = 'var(--cc-warning)';
            suggestionText = '😬 Suggested: Getting Serious (< 5 days)';
            suggestionColor = 'var(--cc-warning)';
            autoPriority('serious');
        } else if (diffDays < 14) {
            countdownText = `📆 ${Math.ceil(diffDays)} days from now — plenty of time`;
            icon = '📆'; borderColor = 'var(--cc-success)';
            suggestionText = '😌 Plenty of time — Chill mode';
            suggestionColor = 'var(--cc-success)';
            autoPriority('chill');
        } else {
            const weeks = Math.floor(diffDays / 7);
            countdownText = `🗓️ ${weeks} week${weeks > 1 ? 's' : ''} from now — no rush`;
            icon = '🗓️'; borderColor = 'var(--cc-success)';
            suggestionText = '😌 Way ahead of schedule — Chill mode';
            suggestionColor = 'var(--cc-success)';
            autoPriority('chill');
        }

        if (previewCountdown) previewCountdown.textContent = countdownText;
        if (previewIcon) previewIcon.textContent = icon;
        if (preview) preview.style.borderColor = borderColor;
        if (suggestion) {
            suggestion.textContent = suggestionText;
            suggestion.style.color = suggestionColor;
        }
    }

    function setupCharCount() {
        const input = document.getElementById('task-title-input');
        const counter = document.getElementById('title-char-count');
        if (!input || !counter) return;
        input.addEventListener('input', () => {
            const len = input.value.length;
            counter.textContent = len > 0 ? `${len}/120` : '';
            counter.style.color = len > 100 ? 'var(--cc-danger)' : 'var(--cc-muted)';
        });
    }

    // ═══════════════════════════════════════════════════
    // QUICK ADD
    // ═══════════════════════════════════════════════════
    function quickAdd() {
        const input = document.getElementById('quick-add-input');
        const title = input?.value?.trim();
        if (!title) {
            input?.focus();
            return;
        }

        // Smart priority detection from keywords
        let priority = 'chill';
        const lower = title.toLowerCase();
        if (lower.includes('urgent') || lower.includes('asap') || lower.includes('due today') || lower.includes('!!')) {
            priority = 'panic';
        } else if (lower.includes('important') || lower.includes('soon') || lower.includes('deadline')) {
            priority = 'serious';
        }

        CramStore.addTask({
            title: title,
            description: '',
            priority: priority,
            deadline: null,
            category: '',
        });

        input.value = '';
        CramNotify.success("Task added! 💪");
        renderTasks(currentFilter);
        updateProgress();
        updateBackpack();

        // Subtle animation on the input
        input.style.animation = 'none';
        void input.offsetWidth;
        input.style.animation = 'popIn 0.3s ease-out';
    }

    // ═══════════════════════════════════════════════════
    // QUICK DEADLINE HELPERS
    // ═══════════════════════════════════════════════════
    function setQuickDeadline(preset, chipEl) {
        const dateInput = document.getElementById('task-deadline-date');
        const timeInput = document.getElementById('task-deadline-time');
        if (!dateInput) return;

        // Highlight active chip
        const container = document.getElementById('quick-date-chips');
        container?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        if (chipEl) chipEl.classList.add('active');

        const now = new Date();
        const pad = (n) => n.toString().padStart(2, '0');
        let date;

        switch (preset) {
            case 'today':
                date = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                break;
            case 'tomorrow':
                date = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                break;
            case 'in3days':
                date = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 3);
                break;
            case 'week': {
                const daysToSunday = 7 - now.getDay();
                date = new Date(now.getFullYear(), now.getMonth(), now.getDate() + (daysToSunday === 0 ? 7 : daysToSunday));
                break;
            }
            case 'nextweek': {
                const daysToNextMon = ((8 - now.getDay()) % 7) + 7;
                date = new Date(now.getFullYear(), now.getMonth(), now.getDate() + daysToNextMon);
                break;
            }
            case 'in2weeks':
                date = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 14);
                break;
        }

        if (date) {
            dateInput.value = `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
            // Keep current time selection
            updateDeadlinePreview();
        }
    }

    function setTimePreset(time, chipEl) {
        const timeInput = document.getElementById('task-deadline-time');
        if (!timeInput) return;

        // Highlight active chip
        const container = document.getElementById('time-preset-chips');
        container?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        if (chipEl) chipEl.classList.add('active');

        timeInput.value = time;
        updateDeadlinePreview();
    }

    function clearDeadline() {
        const dateInput = document.getElementById('task-deadline-date');
        const timeInput = document.getElementById('task-deadline-time');
        const hiddenInput = document.getElementById('task-deadline-input');
        const preview = document.getElementById('deadline-preview');
        const suggestion = document.getElementById('priority-suggestion');

        if (dateInput) dateInput.value = '';
        if (timeInput) timeInput.value = '23:59';
        if (hiddenInput) hiddenInput.value = '';
        if (preview) preview.classList.add('hidden');
        if (suggestion) suggestion.textContent = '';

        // Reset quick date chips
        document.getElementById('quick-date-chips')?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        // Reset time chips to End of Day
        const timeChips = document.getElementById('time-preset-chips');
        timeChips?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        timeChips?.querySelector('.chip:last-child')?.classList.add('active');
    }

    // ═══════════════════════════════════════════════════
    // CATEGORY SELECTOR
    // ═══════════════════════════════════════════════════
    function selectCategory(btn) {
        const chips = document.getElementById('category-chips');
        if (!chips) return;
        const wasActive = btn.classList.contains('active');
        chips.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        if (!wasActive) {
            btn.classList.add('active');
            selectedCategory = btn.dataset.cat;
        } else {
            selectedCategory = '';
        }
    }

    // ═══════════════════════════════════════════════════
    // SORTING
    // ═══════════════════════════════════════════════════
    function cycleSort() {
        const idx = sortModes.indexOf(currentSort);
        currentSort = sortModes[(idx + 1) % sortModes.length];
        const label = document.getElementById('sort-label');
        if (label) {
            const labels = { priority: 'Priority', deadline: 'Deadline', created: 'Newest' };
            label.textContent = labels[currentSort];
        }
        renderTasks(currentFilter);
    }

    function sortTasks(tasks) {
        switch (currentSort) {
            case 'priority':
                return tasks.sort((a, b) => (priorityOrder[a.priority] || 2) - (priorityOrder[b.priority] || 2));
            case 'deadline':
                return tasks.sort((a, b) => {
                    if (!a.deadline) return 1;
                    if (!b.deadline) return -1;
                    return new Date(a.deadline) - new Date(b.deadline);
                });
            case 'created':
                return tasks.sort((a, b) => new Date(b.createdAt || 0) - new Date(a.createdAt || 0));
            default:
                return tasks;
        }
    }

    // ═══════════════════════════════════════════════════
    // RENDER TASKS
    // ═══════════════════════════════════════════════════
    function renderTasks(filter) {
        const container = document.getElementById('task-list');
        const completedContainer = document.getElementById('completed-task-list');
        const completedHeader = document.getElementById('completed-section-header');
        const badge = document.getElementById('task-count-badge');
        const overdueChip = document.getElementById('overdue-chip-count');
        if (!container) return;

        let allTasks = CramStore.get('tasks') || [];
        const now = new Date();
        const incompleteAll = allTasks.filter(t => !t.completed);
        const completedAll = allTasks.filter(t => t.completed);

        // Update badge
        if (badge) badge.textContent = incompleteAll.length;

        // Update overdue chip count
        const overdueCount = allTasks.filter(t => !t.completed && t.deadline && new Date(t.deadline) < now).length;
        if (overdueChip) {
            if (overdueCount > 0) {
                overdueChip.classList.remove('hidden');
                overdueChip.textContent = overdueCount;
            } else {
                overdueChip.classList.add('hidden');
            }
        }

        // Filter incomplete tasks
        let tasks;
        switch (filter) {
            case 'today':
                tasks = incompleteAll.filter(t => {
                    if (!t.deadline) return false;
                    const dl = new Date(t.deadline);
                    return dl.toDateString() === now.toDateString();
                });
                break;
            case 'upcoming':
                tasks = incompleteAll.filter(t => t.deadline && new Date(t.deadline) > now);
                break;
            case 'overdue':
                tasks = incompleteAll.filter(t => t.deadline && new Date(t.deadline) < now);
                break;
            case 'completed':
                tasks = completedAll;
                break;
            default:
                tasks = incompleteAll;
        }

        // Sort
        tasks = sortTasks([...tasks]);

        // Clear container
        container.innerHTML = '';

        // Show/hide completed section (only in 'all' view)
        if (filter === 'all' && completedAll.length > 0) {
            completedHeader?.classList.remove('hidden');
            const countEl = document.getElementById('completed-count');
            if (countEl) countEl.textContent = completedAll.length;
            renderCompletedList(completedAll);
        } else {
            completedHeader?.classList.add('hidden');
            completedContainer?.classList.add('hidden');
        }

        // Empty state
        if (tasks.length === 0) {
            const emptyStates = {
                all: { emoji: '📝', title: 'No tasks yet!', sub: 'Use the quick add bar or tap + Add' },
                today: { emoji: '☀️', title: 'Nothing due today!', sub: 'Enjoy or get ahead on upcoming tasks' },
                upcoming: { emoji: '📅', title: 'Nothing upcoming', sub: 'Add deadlines to your tasks' },
                overdue: { emoji: '✨', title: 'Nothing overdue!', sub: "You're on top of everything — great job!" },
                completed: { emoji: '🎯', title: 'No completed tasks yet', sub: 'Complete your first task to see it here!' },
            };
            const s = emptyStates[filter] || emptyStates.all;
            container.innerHTML = `<div class="cc-empty">
                <span class="emoji">${s.emoji}</span>
                <p class="font-heading font-bold">${s.title}</p>
                <p class="cc-text-muted cc-text-sm mt-1">${s.sub}</p>
            </div>`;
            return;
        }

        // Render tasks
        tasks.forEach((task, i) => {
            container.appendChild(createTaskCard(task, i, now));
        });

        // Animate entry
        requestAnimationFrame(() => {
            container.querySelectorAll('.stagger-item').forEach(el => {
                el.classList.remove('stagger-item');
                el.classList.add('animate-fade-in-up');
            });
        });
    }

    function renderCompletedList(completedTasks) {
        const container = document.getElementById('completed-task-list');
        if (!container) return;
        container.innerHTML = '';

        if (!showCompleted) {
            container.classList.add('hidden');
            return;
        }

        container.classList.remove('hidden');
        const sorted = [...completedTasks].sort((a, b) => new Date(b.completedAt || 0) - new Date(a.completedAt || 0));
        sorted.slice(0, 20).forEach((task, i) => {
            container.appendChild(createTaskCard(task, i, new Date(), { ghost: true }));
        });

        requestAnimationFrame(() => {
            container.querySelectorAll('.stagger-item').forEach(el => {
                el.classList.remove('stagger-item');
                el.classList.add('animate-fade-in-up');
            });
        });
    }

    function createTaskCard(task, index, now, options = {}) {
        const isGhost = !!options.ghost || !!task.completed;
        const div = document.createElement('div');
        div.className = `task-card ${isGhost ? 'completed task-ghost' : ''} stagger-item`;
        div.dataset.taskId = task.id;
        if (isGhost) div.dataset.ghost = '1';
        div.style.animationDelay = Math.min(index * 0.04, 0.4) + 's';

        const priorityConfig = {
            panic: { label: '💀 Panic', cls: 'panic' },
            serious: { label: '😬 Serious', cls: 'serious' },
            chill: { label: '😌 Chill', cls: 'chill' },
        };
        const p = priorityConfig[task.priority] || priorityConfig.chill;
        const dl = task.deadline ? formatSmartDeadline(task.deadline, now) : null;
        const isOverdue = task.deadline && new Date(task.deadline) < now && !task.completed;
        const catBadge = task.category ? `<span class="cc-text-xs cc-text-muted">· ${task.category}</span>` : '';
        const bodyClick = isGhost
            ? ''
            : `onclick="CramTasks.openModal('${task.id}')"`;

        div.innerHTML = `
            <button type="button" class="task-checkbox-wrap" onclick="event.stopPropagation(); CramTasks.toggleComplete('${task.id}')" aria-label="${task.completed ? 'Restore task' : 'Mark complete'}" title="${task.completed ? 'Restore to active' : 'Mark done'}">
                <span class="task-checkbox ${task.completed ? 'checked' : ''}" aria-hidden="true"></span>
            </button>
            <div class="task-card-body" ${bodyClick}>
                <p class="task-title">${escapeHtml(task.title)}</p>
                ${task.description && !isGhost ? `<p class="cc-text-xs cc-text-muted mt-0.5 truncate">${escapeHtml(task.description)}</p>` : ''}
                <div class="task-meta mt-1">
                    <span class="task-priority ${p.cls}">${p.label}</span>
                    ${dl && !isGhost ? `<span class="task-deadline ${isOverdue ? 'text-red-500 font-bold animate-pulse' : ''}">${dl.icon} ${dl.text}</span>` : ''}
                    ${isGhost ? '<span class="task-deadline">✓ Done</span>' : ''}
                    ${!isGhost ? catBadge : ''}
                </div>
            </div>
            ${!isGhost ? `<div class="task-card-actions">
                <button type="button" class="task-delete-wrap" onclick="event.stopPropagation(); CramTasks.confirmDelete('${task.id}')" title="Delete" aria-label="Delete task">
                    <span class="task-delete-icon" aria-hidden="true">×</span>
                </button>
            </div>` : ''}
        `;

        return div;
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatSmartDeadline(deadline, now) {
        const dl = new Date(deadline);
        const diffMs = dl - now;
        const diffHours = diffMs / (1000 * 60 * 60);
        const diffDays = diffMs / (1000 * 60 * 60 * 24);

        if (diffMs < 0) {
            const agoHours = Math.abs(diffHours);
            if (agoHours < 1) return { icon: '🔴', text: 'Just passed!' };
            if (agoHours < 24) return { icon: '🔴', text: `${Math.floor(agoHours)}h overdue` };
            return { icon: '🔴', text: `${Math.floor(Math.abs(diffDays))}d overdue` };
        }

        if (diffHours < 1) return { icon: '🔥', text: 'Due now!' };
        if (diffHours < 3) return { icon: '⚡', text: `${Math.floor(diffHours)}h left` };
        if (diffHours < 24) return { icon: '⏰', text: `${Math.floor(diffHours)}h left` };

        if (dl.toDateString() === now.toDateString()) return { icon: '🔆', text: 'Today' };

        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        if (dl.toDateString() === tomorrow.toDateString()) return { icon: '📅', text: 'Tomorrow' };

        if (diffDays < 7) return { icon: '📅', text: `${Math.ceil(diffDays)}d left` };
        return { icon: '📆', text: dl.toLocaleDateString('en', { month: 'short', day: 'numeric' }) };
    }

    // ═══════════════════════════════════════════════════
    // COMPLETED TOGGLE
    // ═══════════════════════════════════════════════════
    function toggleCompleted() {
        showCompleted = !showCompleted;
        const chevron = document.getElementById('completed-chevron');
        if (chevron) chevron.style.transform = showCompleted ? 'rotate(180deg)' : '';
        const allTasks = CramStore.get('tasks') || [];
        renderCompletedList(allTasks.filter(t => t.completed));
    }

    // ═══════════════════════════════════════════════════
    // PROGRESS
    // ═══════════════════════════════════════════════════
    function updateProgress() {
        const allTasks = CramStore.get('tasks') || [];
        const total = allTasks.length;
        const done = allTasks.filter(t => t.completed).length;
        const pct = total > 0 ? Math.round((done / total) * 100) : 0;

        const bar = document.getElementById('task-progress-bar');
        const pctEl = document.getElementById('task-progress-pct');
        const label = document.getElementById('task-progress-label');
        const streakEl = document.getElementById('task-progress-streak');

        if (bar) setTimeout(() => { bar.style.width = pct + '%'; }, 200);
        if (pctEl) pctEl.textContent = pct + '%';
        if (label) label.textContent = `${done} of ${total} tasks done`;

        // Streak
        const streak = CramStore.get('streak') || {};
        if (streakEl && (streak.current || 0) > 0) {
            streakEl.textContent = `🔥 ${streak.current} day streak`;
        }
    }

    // ═══════════════════════════════════════════════════
    // BACKPACK
    // ═══════════════════════════════════════════════════
    function updateBackpack() {
        const tasks = CramStore.get('tasks') || [];
        const now = new Date();
        const pending = tasks.filter(t => !t.completed).length;
        const overdue = tasks.filter(t => !t.completed && t.deadline && new Date(t.deadline) < now).length;
        const weight = pending + (overdue * 2);

        let level, desc, color;
        if (weight <= 3) {
            level = 15; desc = 'Light and breezy! 🌤️'; color = 'var(--cc-success)';
        } else if (weight <= 6) {
            level = 40; desc = 'Getting heavier... 📚'; color = 'var(--cc-primary)';
        } else if (weight <= 10) {
            level = 65; desc = 'Feeling the weight! 😓'; color = 'var(--cc-warning)';
        } else {
            level = 90; desc = 'Crushing weight! Lighten up! 💀'; color = 'var(--cc-danger)';
        }

        const fill = document.getElementById('backpack-fill');
        const descEl = document.getElementById('backpack-desc');
        const iconEl = document.getElementById('backpack-icon');
        if (fill) { fill.style.width = level + '%'; fill.style.background = color; }
        if (descEl) descEl.textContent = desc;
        if (iconEl && weight > 10) iconEl.classList.add('heavy');
    }

    // ═══════════════════════════════════════════════════
    // FILTER LABEL + OVERDUE
    // ═══════════════════════════════════════════════════
    function updateFilterLabel() {
        const el = document.getElementById('task-filter-label');
        if (!el) return;
        const labels = { all: 'All Tasks', today: "Today's Tasks", upcoming: 'Upcoming', overdue: 'Overdue', completed: 'Completed' };
        el.textContent = labels[currentFilter] || 'Tasks';
    }

    function showOverdueOnly() {
        const filters = document.getElementById('task-filters');
        if (filters) {
            filters.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
            const overdueChip = filters.querySelector('[data-filter="overdue"]');
            overdueChip?.classList.add('active');
        }
        currentFilter = 'overdue';
        renderTasks('overdue');
        updateFilterLabel();
    }

    // ═══════════════════════════════════════════════════
    // MODAL
    // ═══════════════════════════════════════════════════
    function openModal(taskId) {
        const modal = document.getElementById('task-modal');
        const title = document.getElementById('modal-title');
        const subtitle = document.getElementById('modal-subtitle');
        const titleInput = document.getElementById('task-title-input');
        const descInput = document.getElementById('task-desc-input');
        const dateInput = document.getElementById('task-deadline-date');
        const timeInput = document.getElementById('task-deadline-time');
        const editId = document.getElementById('task-edit-id');
        const suggestion = document.getElementById('priority-suggestion');
        const saveBtn = document.getElementById('save-btn-text');
        const charCount = document.getElementById('title-char-count');

        if (!modal) return;

        // Ghost / finished tasks are read-only
        if (taskId) {
            const existing = (CramStore.get('tasks') || []).find(t => t.id === taskId);
            if (existing?.completed) {
                CramNotify?.info('Finished tasks are read-only. Uncheck to restore and edit.');
                return;
            }
        }

        // Reset category
        document.getElementById('category-chips')?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        selectedCategory = '';

        // Reset deadline UI
        clearDeadline();

        if (taskId) {
            const tasks = CramStore.get('tasks') || [];
            const task = tasks.find(t => t.id === taskId);
            if (task) {
                title.textContent = 'Edit Task';
                subtitle.textContent = 'Update the details';
                titleInput.value = task.title;
                descInput.value = task.description || '';
                editId.value = task.id;
                saveBtn.textContent = 'Update Task';
                autoPriority(task.priority);
                if (charCount) charCount.textContent = `${task.title.length}/120`;

                // Populate split date/time from ISO deadline
                if (task.deadline) {
                    const dl = new Date(task.deadline);
                    const pad = (n) => n.toString().padStart(2, '0');
                    if (dateInput) dateInput.value = `${dl.getFullYear()}-${pad(dl.getMonth() + 1)}-${pad(dl.getDate())}`;
                    if (timeInput) timeInput.value = `${pad(dl.getHours())}:${pad(dl.getMinutes())}`;
                    updateDeadlinePreview();
                }

                // Set category
                if (task.category) {
                    selectedCategory = task.category;
                    const catChip = document.querySelector(`[data-cat="${task.category}"]`);
                    catChip?.classList.add('active');
                }
            }
        } else {
            title.textContent = 'New Task';
            subtitle.textContent = "What's on your plate?";
            titleInput.value = '';
            descInput.value = '';
            editId.value = '';
            saveBtn.textContent = 'Save Task';
            autoPriority('chill');
            if (suggestion) suggestion.textContent = '';
            if (charCount) charCount.textContent = '';
        }

        modal.classList.add('active');
        setTimeout(() => titleInput.focus(), 300);
    }

    function closeModal() {
        const modal = document.getElementById('task-modal');
        if (modal) modal.classList.remove('active');
    }

    function autoPriority(priority) {
        const selector = document.getElementById('priority-selector');
        if (!selector) return;
        selector.querySelectorAll('[data-priority]').forEach(c => {
            const match = c.dataset.priority === priority;
            c.classList.toggle('active', match);
            c.classList.toggle('ring-2', match);
            c.classList.toggle('ring-[var(--cc-primary)]', match);
        });
        selectedPriority = priority;
    }

    function saveTask() {
        const titleInput = document.getElementById('task-title-input');
        const descInput = document.getElementById('task-desc-input');
        const dateInput = document.getElementById('task-deadline-date');
        const timeInput = document.getElementById('task-deadline-time');
        const editId = document.getElementById('task-edit-id');

        const titleVal = titleInput?.value?.trim();
        if (!titleVal) {
            CramNotify.warning("Please enter a task name!");
            titleInput?.focus();
            titleInput?.classList.add('animate-wiggle');
            setTimeout(() => titleInput?.classList.remove('animate-wiggle'), 600);
            return;
        }

        // Combine split date + time inputs
        let deadline = null;
        if (dateInput?.value) {
            const timeVal = timeInput?.value || '23:59';
            deadline = new Date(`${dateInput.value}T${timeVal}`).toISOString();
        }

        if (editId?.value) {
            CramStore.updateTask(editId.value, {
                title: titleVal,
                description: descInput?.value?.trim() || '',
                priority: selectedPriority,
                deadline: deadline,
                category: selectedCategory,
            });
            CramNotify.success("Task updated! ✏️");
        } else {
            CramStore.addTask({
                title: titleVal,
                description: descInput?.value?.trim() || '',
                priority: selectedPriority,
                deadline: deadline,
                category: selectedCategory,
            });
            CramNotify.success("Task added! Let's do this 💪");
        }

        closeModal();
        renderTasks(currentFilter);
        updateProgress();
        updateBackpack();
    }

    // ═══════════════════════════════════════════════════
    // TOGGLE COMPLETE
    // ═══════════════════════════════════════════════════
    function toggleComplete(id) {
        const tasks = CramStore.get('tasks') || [];
        const task = tasks.find(t => t.id === id);
        if (!task) return;

        if (task.completed) {
            CramStore.updateTask(id, { completed: false, completedAt: null });
            CramNotify?.success('Task restored to active list');
        } else {
            CramStore.completeTask(id);
            CramStore.addTreeXP(5);
            CramStore.updateStreak();
            const stats = CramStore.get('stats') || {};
            CramStore.set('stats.totalTasksCompleted', (stats.totalTasksCompleted || 0) + 1);
            CramNotify.motivate();
        }
        // Keep finished section open so the ghost appears immediately
        showCompleted = true;
        const chevron = document.getElementById('completed-chevron');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        renderTasks(currentFilter);
        updateProgress();
        updateBackpack();
    }

    // ═══════════════════════════════════════════════════
    // DELETE
    // ═══════════════════════════════════════════════════
    function confirmDelete(id) {
        const tasks = CramStore.get('tasks') || [];
        const task = tasks.find(t => t.id === id);
        if (!task) return;

        const modal = document.getElementById('delete-confirm-modal');
        const nameEl = document.getElementById('delete-task-name');
        const confirmBtn = document.getElementById('delete-confirm-btn');

        if (nameEl) nameEl.textContent = `"${task.title}"`;
        if (confirmBtn) {
            confirmBtn.onclick = () => {
                deleteTask(id);
                modal.classList.remove('active');
            };
        }
        if (modal) modal.classList.add('active');
    }

    function deleteTask(id) {
        CramStore.deleteTask(id);
        CramNotify.info("Task removed 🗑️");
        renderTasks(currentFilter);
        updateProgress();
        updateBackpack();
    }

    // ═══════════════════════════════════════════════════
    // RECOVERY
    // ═══════════════════════════════════════════════════
    function checkRecovery() {
        const tasks = CramStore.get('tasks') || [];
        const now = new Date();
        const overdue = tasks.filter(t => !t.completed && t.deadline && new Date(t.deadline) < now);
        const banner = document.getElementById('recovery-banner');
        if (banner && overdue.length >= 3) {
            banner.classList.remove('hidden');
        }
    }

    // ═══════════════════════════════════════════════════
    // INIT HOOK
    // ═══════════════════════════════════════════════════
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 100);
    }

    return {
        init, renderTasks, openModal, closeModal, saveTask,
        toggleComplete, deleteTask, confirmDelete,
        quickAdd, setQuickDeadline, setTimePreset, clearDeadline,
        selectCategory, cycleSort, toggleCompleted,
        showOverdueOnly,
    };
})();

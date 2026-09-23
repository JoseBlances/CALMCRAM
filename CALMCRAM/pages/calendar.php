<div class="cc-page pb-nav" id="page-calendar">
    <!-- ═══════════════════════════════════════════ -->
    <!-- PAGE HEADER                                 -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-page-header animate-on-enter">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-heading font-black">📅 Calendar</h1>
                <p class="cc-text-muted cc-text-sm mt-1" id="cal-month-label">May 2026</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Sync indicator -->
                <span class="cc-badge cc-badge-success cc-text-xs" id="cal-sync-badge">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--cc-success)] inline-block"></span>
                    Calendar synced
                </span>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- NAVIGATION & VIEW TOGGLE                    -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="flex items-center justify-between mb-4 animate-on-enter">
        <!-- Month navigation -->
        <div class="flex items-center gap-2">
            <button class="cc-btn cc-btn-ghost cc-btn-sm" onclick="CramCalendar.prevMonth()" title="Previous Month">◀</button>
            <button class="cc-btn cc-btn-primary cc-btn-sm" onclick="CramCalendar.goToday()" title="Today">Today</button>
            <button class="cc-btn cc-btn-ghost cc-btn-sm" onclick="CramCalendar.nextMonth()" title="Next Month">▶</button>
        </div>
        <!-- View toggle -->
        <div class="flex items-center gap-1" style="background: var(--cc-surface); border-radius: var(--cc-radius-full); padding: 3px;">
            <button class="cc-btn cc-btn-sm cal-view-btn active" id="cal-view-month" onclick="CramCalendar.setView('month')" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">Month</button>
            <button class="cc-btn cc-btn-sm cal-view-btn" id="cal-view-week" onclick="CramCalendar.setView('week')" style="padding: 0.35rem 0.75rem; font-size: 0.75rem;">Week</button>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- CALENDAR GRID                               -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-card animate-on-enter mb-4" style="padding: var(--cc-gap-md);">
        <!-- Day-of-week headers -->
        <div class="cal-grid cal-header-row">
            <div class="cal-header">Sun</div>
            <div class="cal-header">Mon</div>
            <div class="cal-header">Tue</div>
            <div class="cal-header">Wed</div>
            <div class="cal-header">Thu</div>
            <div class="cal-header">Fri</div>
            <div class="cal-header">Sat</div>
        </div>
        <!-- Calendar body: rendered dynamically -->
        <div id="cal-body" class="cal-grid"></div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- SELECTED DAY PANEL                          -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="cc-section animate-on-enter" id="cal-day-panel" style="display: none;">
        <h3 class="cc-section-title" id="cal-day-panel-title">📋 Tasks for Today</h3>
        <div id="cal-day-tasks" class="flex flex-col gap-3">
            <!-- Populated dynamically -->
        </div>
    </div>

    <!-- ═══════════════════════════════════════════ -->
    <!-- EXPORT BUTTON                               -->
    <!-- ═══════════════════════════════════════════ -->
    <div class="animate-on-enter mb-6">
        <button class="cc-btn cc-btn-secondary cc-w-full" onclick="CramCalendar.exportICS()">
            📤 Export to .ics (Calendar File)
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════ -->
<!-- INLINE STYLES                                    -->
<!-- ═══════════════════════════════════════════════ -->
<style>
    /* Calendar grid */
    .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
    }
    .cal-header-row {
        margin-bottom: 4px;
    }
    .cal-header {
        text-align: center;
        font-family: var(--cc-font-heading);
        font-weight: 800;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--cc-muted);
        padding: 6px 0;
    }

    /* Day cells */
    .cal-day {
        position: relative;
        min-height: 48px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        padding: 6px 2px 4px;
        border-radius: var(--cc-radius-sm);
        cursor: pointer;
        transition: background var(--cc-transition-fast), transform var(--cc-transition-fast);
        user-select: none;
        -webkit-user-select: none;
    }
    .cal-day:hover {
        background: var(--cc-surface-hover);
    }
    .cal-day:active {
        transform: scale(0.93);
    }

    /* Day number */
    .cal-day-num {
        font-family: var(--cc-font-heading);
        font-weight: 700;
        font-size: 0.82rem;
        line-height: 1;
        color: var(--cc-text);
    }
    .cal-day.cal-day-outside .cal-day-num {
        color: var(--cc-muted);
        opacity: 0.4;
    }

    /* Today ring */
    .cal-day-today {
        box-shadow: inset 0 0 0 2px var(--cc-primary);
        background: rgba(107, 144, 128, 0.08);
    }
    .dark .cal-day-today {
        background: rgba(126, 200, 168, 0.08);
    }

    /* Selected day */
    .cal-day-selected {
        background: var(--cc-primary) !important;
    }
    .cal-day-selected .cal-day-num {
        color: white !important;
        opacity: 1 !important;
    }
    .cal-day-selected .cal-dot {
        box-shadow: 0 0 0 1px rgba(255,255,255,0.5);
    }

    /* Task dots */
    .cal-dots {
        display: flex;
        gap: 3px;
        margin-top: auto;
        padding-top: 3px;
        flex-wrap: wrap;
        justify-content: center;
        max-width: 100%;
    }
    .cal-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .cal-dot-chill    { background: var(--cc-success); }
    .cal-dot-serious  { background: var(--cc-warning); }
    .cal-dot-panic    { background: var(--cc-danger); }

    /* Deadline indicator */
    .cal-deadline-mark {
        position: absolute;
        top: 3px;
        right: 3px;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--cc-danger);
        box-shadow: 0 0 4px rgba(239, 68, 68, 0.5);
    }

    /* View toggle buttons */
    .cal-view-btn {
        background: transparent;
        color: var(--cc-muted);
        border-radius: var(--cc-radius-full);
    }
    .cal-view-btn.active {
        background: var(--cc-primary) !important;
        color: white !important;
    }

    /* Task card in day panel */
    .cal-task-card {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
    }
    .cal-task-card.completed .cal-task-title {
        text-decoration: line-through;
        opacity: 0.5;
    }
    .cal-task-check {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid var(--cc-border);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--cc-transition-fast);
        flex-shrink: 0;
        font-size: 0.7rem;
        background: transparent;
    }
    .cal-task-check.checked {
        background: var(--cc-primary);
        border-color: var(--cc-primary);
        color: white;
    }
    .cal-task-check:active {
        transform: scale(0.85);
    }
</style>

<!-- ═══════════════════════════════════════════════ -->
<!-- INLINE JAVASCRIPT                               -->
<!-- ═══════════════════════════════════════════════ -->
<script>
/**
 * CramCalendar — Month/Week Calendar View
 * Reads tasks from CramStore, renders a visual calendar grid
 */
const CramCalendar = (() => {
    // ─── State ───────────────────────────────────
    let currentYear  = new Date().getFullYear();
    let currentMonth = new Date().getMonth(); // 0-indexed
    let selectedDate = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
    let viewMode     = 'month'; // 'month' | 'week'

    const MONTH_NAMES = ['January','February','March','April','May','June',
                         'July','August','September','October','November','December'];
    const DAY_NAMES   = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    // ─── Initialization ─────────────────────────
    function init() {
        renderMonth(currentYear, currentMonth);
        selectDay(selectedDate);
    }

    // ─── Render Month Grid ──────────────────────
    function renderMonth(year, month) {
        currentYear  = year;
        currentMonth = month;

        // Update header label
        const label = document.getElementById('cal-month-label');
        if (label) label.textContent = `${MONTH_NAMES[month]} ${year}`;

        const body = document.getElementById('cal-body');
        if (!body) return;

        const firstDay  = new Date(year, month, 1).getDay(); // 0=Sun
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const prevMonthDays = new Date(year, month, 0).getDate();
        const todayStr = new Date().toISOString().split('T')[0];
        const tasks    = CramStore.get('tasks') || [];

        // Determine how many rows we need
        const totalCells = viewMode === 'week' ? 7 : Math.ceil((firstDay + daysInMonth) / 7) * 7;

        let html = '';
        let startCell = 0;
        let endCell   = totalCells;

        // In week view, only show the week containing today or selectedDate
        if (viewMode === 'week') {
            const selDate = new Date(selectedDate);
            if (selDate.getMonth() === month && selDate.getFullYear() === year) {
                const selDayOfMonth = selDate.getDate();
                const selIndex = firstDay + selDayOfMonth - 1;
                const weekStart = selIndex - (selIndex % 7);
                startCell = weekStart;
                endCell = weekStart + 7;
            } else {
                startCell = 0;
                endCell = 7;
            }
        }

        for (let i = startCell; i < endCell; i++) {
            let dayNum, dateStr, isOutside = false;

            if (i < firstDay) {
                // Previous month overflow
                dayNum = prevMonthDays - firstDay + i + 1;
                const pm = month === 0 ? 11 : month - 1;
                const py = month === 0 ? year - 1 : year;
                dateStr = `${py}-${String(pm + 1).padStart(2,'0')}-${String(dayNum).padStart(2,'0')}`;
                isOutside = true;
            } else if (i - firstDay >= daysInMonth) {
                // Next month overflow
                dayNum = i - firstDay - daysInMonth + 1;
                const nm = month === 11 ? 0 : month + 1;
                const ny = month === 11 ? year + 1 : year;
                dateStr = `${ny}-${String(nm + 1).padStart(2,'0')}-${String(dayNum).padStart(2,'0')}`;
                isOutside = true;
            } else {
                dayNum = i - firstDay + 1;
                dateStr = `${year}-${String(month + 1).padStart(2,'0')}-${String(dayNum).padStart(2,'0')}`;
            }

            const isToday    = dateStr === todayStr;
            const isSelected = dateStr === selectedDate;
            const dayTasks   = getTasksForDate(dateStr);
            const hasDeadline = dayTasks.some(t => t.deadline && t.deadline.startsWith(dateStr));

            let classes = 'cal-day';
            if (isOutside)  classes += ' cal-day-outside';
            if (isToday)    classes += ' cal-day-today';
            if (isSelected) classes += ' cal-day-selected';

            // Build dots (max 5 visible)
            let dots = '';
            const visibleTasks = dayTasks.slice(0, 5);
            if (visibleTasks.length > 0) {
                dots = '<div class="cal-dots">';
                visibleTasks.forEach(t => {
                    const pClass = t.priority === 'panic' ? 'cal-dot-panic'
                                 : t.priority === 'serious' ? 'cal-dot-serious'
                                 : 'cal-dot-chill';
                    dots += `<span class="cal-dot ${pClass}"></span>`;
                });
                dots += '</div>';
            }

            // Deadline indicator
            const deadlineMark = hasDeadline ? '<span class="cal-deadline-mark"></span>' : '';

            html += `<div class="${classes}" data-date="${dateStr}" onclick="CramCalendar.selectDay('${dateStr}')">
                        ${deadlineMark}
                        <span class="cal-day-num">${dayNum}</span>
                        ${dots}
                     </div>`;
        }

        body.innerHTML = html;
    }

    // ─── Select Day ─────────────────────────────
    function selectDay(dateStr) {
        selectedDate = dateStr;

        // Update selected visual
        document.querySelectorAll('.cal-day').forEach(el => {
            el.classList.toggle('cal-day-selected', el.dataset.date === dateStr);
        });

        // Render day panel
        const panel     = document.getElementById('cal-day-panel');
        const title     = document.getElementById('cal-day-panel-title');
        const container = document.getElementById('cal-day-tasks');
        if (!panel || !container) return;

        const d = new Date(dateStr + 'T00:00:00');
        const todayStr = new Date().toISOString().split('T')[0];
        const dayLabel = dateStr === todayStr ? 'Today'
                       : d.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' });

        if (title) title.textContent = `📋 Tasks for ${dayLabel}`;

        const dayTasks = getTasksForDate(dateStr);

        if (dayTasks.length === 0) {
            container.innerHTML = `<div class="cc-empty">
                <span class="emoji">📭</span>
                <p class="cc-text-muted cc-text-sm">No tasks for this day. Enjoy!</p>
            </div>`;
        } else {
            container.innerHTML = dayTasks.map(t => {
                const priorityEmoji = t.priority === 'panic' ? '🔴'
                                    : t.priority === 'serious' ? '🟡' : '🟢';
                const checkedClass = t.completed ? 'checked' : '';
                const completedClass = t.completed ? 'completed' : '';
                return `<div class="cc-card cal-task-card ${completedClass}">
                    <button class="cal-task-check ${checkedClass}" onclick="CramCalendar.toggleTask('${t.id}', event)">
                        ${t.completed ? '✓' : ''}
                    </button>
                    <div class="flex-1 min-w-0">
                        <p class="font-heading font-semibold text-sm truncate cal-task-title">${t.title}</p>
                        <p class="cc-text-xs cc-text-muted mt-0.5">${priorityEmoji} ${t.priority || 'chill'}${t.deadline ? ' · Due ' + formatShortDate(t.deadline) : ''}</p>
                    </div>
                </div>`;
            }).join('');
        }

        panel.style.display = 'block';

        // Animate panel entrance
        CramAnimate?.enter(panel, 'fadeUp', { duration: 0.3 });
    }

    // ─── Get Tasks for a Date ───────────────────
    function getTasksForDate(dateStr) {
        const tasks    = CramStore.get('tasks') || [];
        const sessions = CramStore.get('focusSessions') || [];

        // Tasks that have a deadline on this date OR were created on this date
        return tasks.filter(t => {
            const deadlineMatch = t.deadline && t.deadline.startsWith(dateStr);
            const createdMatch  = t.createdAt && t.createdAt.startsWith(dateStr);
            const completedMatch = t.completedAt && t.completedAt.startsWith(dateStr);
            return deadlineMatch || createdMatch || completedMatch;
        });
    }

    // ─── Toggle Task Completion ─────────────────
    function toggleTask(taskId, event) {
        if (event) event.stopPropagation();

        const tasks = CramStore.get('tasks') || [];
        const task = tasks.find(t => t.id === taskId);
        if (!task) return;

        if (task.completed) {
            // Un-complete
            CramStore.updateTask(taskId, { completed: false, completedAt: null });
        } else {
            CramStore.completeTask(taskId);
        }

        // Re-render the day panel
        selectDay(selectedDate);
        // Re-render grid to update dots
        renderMonth(currentYear, currentMonth);
    }

    // ─── Navigation ─────────────────────────────
    function prevMonth() {
        if (currentMonth === 0) {
            currentMonth = 11;
            currentYear--;
        } else {
            currentMonth--;
        }
        renderMonth(currentYear, currentMonth);
    }

    function nextMonth() {
        if (currentMonth === 11) {
            currentMonth = 0;
            currentYear++;
        } else {
            currentMonth++;
        }
        renderMonth(currentYear, currentMonth);
    }

    function goToday() {
        const now = new Date();
        currentYear  = now.getFullYear();
        currentMonth = now.getMonth();
        selectedDate = now.toISOString().split('T')[0];
        renderMonth(currentYear, currentMonth);
        selectDay(selectedDate);
    }

    // ─── View Toggle ────────────────────────────
    function setView(mode) {
        viewMode = mode;

        // Update active button
        document.querySelectorAll('.cal-view-btn').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById(`cal-view-${mode}`);
        if (activeBtn) activeBtn.classList.add('active');

        renderMonth(currentYear, currentMonth);
    }

    // ─── Export ICS ─────────────────────────────
    function exportICS() {
        const tasks = CramStore.get('tasks') || [];
        const withDeadlines = tasks.filter(t => t.deadline);

        if (withDeadlines.length === 0) {
            CramNotify?.show({
                message: 'No tasks with deadlines to export!',
                type: 'info',
                icon: '📭',
                duration: 3000,
            });
            return;
        }

        // Build ICS content
        let ics = 'BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//CramCalm//StudyApp//EN\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n';

        withDeadlines.forEach(t => {
            const deadlineDate = new Date(t.deadline);
            const dtStamp = formatICSDate(new Date());
            const dtStart = formatICSDate(deadlineDate);
            const dtEnd   = formatICSDate(new Date(deadlineDate.getTime() + 3600000)); // +1 hour

            const priorityMap = { panic: 1, serious: 5, chill: 9 };
            const icsP = priorityMap[t.priority] || 9;

            ics += 'BEGIN:VEVENT\r\n';
            ics += `UID:${t.id}@cramcalm\r\n`;
            ics += `DTSTAMP:${dtStamp}\r\n`;
            ics += `DTSTART:${dtStart}\r\n`;
            ics += `DTEND:${dtEnd}\r\n`;
            ics += `SUMMARY:${escapeICS(t.title)}\r\n`;
            ics += `DESCRIPTION:${escapeICS(t.description || '')} [${t.priority}]\r\n`;
            ics += `PRIORITY:${icsP}\r\n`;
            ics += `STATUS:${t.completed ? 'COMPLETED' : 'NEEDS-ACTION'}\r\n`;
            ics += 'END:VEVENT\r\n';
        });

        ics += 'END:VCALENDAR\r\n';

        // Trigger download
        const blob = new Blob([ics], { type: 'text/calendar;charset=utf-8' });
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url;
        a.download = `cramcalm-tasks-${new Date().toISOString().split('T')[0]}.ics`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        CramNotify?.show({
            message: `Exported ${withDeadlines.length} task(s) to .ics file!`,
            type: 'success',
            icon: '✅',
            duration: 3000,
        });
    }

    // ─── Helpers ─────────────────────────────────
    function formatICSDate(date) {
        return date.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '');
    }

    function escapeICS(str) {
        return str.replace(/[\\;,\n]/g, c => {
            if (c === '\n') return '\\n';
            return '\\' + c;
        });
    }

    function formatShortDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }

    // ─── Auto-init ──────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';
        if (page !== 'calendar') return;
        init();
    });

    return {
        init, renderMonth, selectDay, prevMonth, nextMonth, goToday,
        exportICS, getTasksForDate, setView, toggleTask,
    };
})();
</script>

/**
 * CramCalm — Simulated AI Study Assistant
 */
const CramAssistant = (() => {
    let isTyping = false;

    function init() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page !== 'assistant') return;

        // Load chat history or show welcome
        const history = CramStore.get('chatHistory') || [];
        if (history.length === 0) {
            addBubble('ai', "Hey there! 👋 I'm your CramCalm study buddy. I can help you organize study plans, break down tasks, analyze workload, and keep you motivated.\n\nTry tapping a suggestion above or just ask me anything!");
        } else {
            history.forEach(msg => addBubble(msg.role, msg.text, false));
        }
    }

    function addBubble(role, text, save = true) {
        const container = document.getElementById('chat-container');
        if (!container) return;

        const bubble = document.createElement('div');
        bubble.className = `chat-bubble ${role}`;
        bubble.innerHTML = text.replace(/\n/g, '<br>');
        container.appendChild(bubble);

        // Scroll to bottom
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
            bubble.scrollIntoView({ behavior: 'smooth', block: 'end' });
        }, 50);

        if (save) {
            const history = CramStore.get('chatHistory') || [];
            history.push({ role, text, time: new Date().toISOString() });
            // Keep last 50 messages
            if (history.length > 50) history.splice(0, history.length - 50);
            CramStore.set('chatHistory', history);
        }
    }

    function showTyping() {
        const container = document.getElementById('chat-container');
        if (!container) return;
        const typing = document.createElement('div');
        typing.className = 'chat-typing';
        typing.id = 'typing-indicator';
        typing.innerHTML = '<span class="dot"></span><span class="dot"></span><span class="dot"></span>';
        container.appendChild(typing);
        container.scrollTop = container.scrollHeight;
    }

    function hideTyping() {
        document.getElementById('typing-indicator')?.remove();
    }

    function sendFromInput() {
        const input = document.getElementById('chat-input');
        if (!input) return;
        const text = input.value.trim();
        if (!text) return;
        input.value = '';
        sendMessage(text);
    }

    function sendMessage(text) {
        if (isTyping) return;
        addBubble('user', text);
        isTyping = true;
        showTyping();

        const delay = 1000 + Math.random() * 1500;
        setTimeout(() => {
            hideTyping();
            const response = generateResponse(text.toLowerCase());
            addBubble('ai', response);
            isTyping = false;
        }, delay);
    }

    function generateResponse(text) {
        const tasks = CramStore.get('tasks') || [];
        const incomplete = tasks.filter(t => !t.completed);
        const panic = incomplete.filter(t => t.priority === 'panic');
        const serious = incomplete.filter(t => t.priority === 'serious');
        const chill = incomplete.filter(t => t.priority === 'chill');
        const stats = CramStore.get('stats') || {};

        // Study plan
        if (text.match(/plan|study|schedule|organize/)) {
            const p1 = panic.length > 0 ? `\n📌 Priority 1: ${panic[0].title}` : '\n📌 Priority 1: Review most challenging material';
            const p2 = serious.length > 0 ? `\n📌 Priority 2: ${serious[0].title}` : '\n📌 Priority 2: Complete pending assignments';
            const p3 = chill.length > 0 ? `\n📌 Priority 3: ${chill[0].title}` : '\n📌 Priority 3: Light review & organization';

            return `📋 Here's your personalized study plan:\n${p1}${p2}${p3}\n\n⏰ Suggested Schedule:\n• Morning: Focus on hardest task (2 hrs)\n• Afternoon: Medium tasks (1.5 hrs)\n• Evening: Light review (30 min)\n\n💡 Tip: Start with just 25 minutes using Focus Mode!`;
        }

        // Workload analysis
        if (text.match(/workload|analyze|how much|overwhelm|status/)) {
            const risk = CramRisk?.calculate() || 0;
            const assessment = risk >= 60 ? 'High stress — consider prioritizing!' : risk >= 30 ? 'Moderate — manageable with focus' : 'Low — you\'re doing great!';
            return `📊 Workload Analysis:\n\n📝 Total tasks: ${incomplete.length}\n🔴 Urgent: ${panic.length}\n🟡 Moderate: ${serious.length}\n🟢 Relaxed: ${chill.length}\n\n📈 Cram Risk: ${risk}%\n💪 Assessment: ${assessment}\n\n${risk >= 50 ? '⚡ Suggestion: Start a focus session right now!' : '✨ Keep up the momentum!'}`;
        }

        // Deadlines
        if (text.match(/deadline|due|when|time/)) {
            const withDl = incomplete.filter(t => t.deadline).sort((a, b) => new Date(a.deadline) - new Date(b.deadline));
            if (withDl.length === 0) return "You don't have any deadlines set! 🎉\nAdd deadlines to your tasks so I can help track them.";
            const list = withDl.slice(0, 5).map(t => `• ${t.title} — ${CramApp.formatDeadline(t.deadline)}`).join('\n');
            return `⏰ Your upcoming deadlines:\n\n${list}\n\n${withDl.length > 5 ? `...and ${withDl.length - 5} more\n\n` : ''}💡 Focus on what's due soonest!`;
        }

        // Motivation
        if (text.match(/motivat|encourage|tired|can't|hard|give up|help|stuck|stress/)) {
            const msgs = [
                "Every expert was once a beginner. You've got this! 💪\n\nRemember: progress beats perfection. Just do the next small thing.",
                "You're stronger than your assignments. Prove it! 🔥\n\nStart with just 5 minutes — that's all it takes to build momentum.",
                "Remember: cramming creates stress, but starting creates progress. You chose wisely by being here! ✨\n\nI believe in you!",
                "Think about how relieved future you will feel. Give them that gift! 🎁\n\nOne task at a time. You can do this.",
                "The fact that you're here shows you care. That's already winning! 🏆\n\nLet's channel that energy into a quick focus session!",
            ];
            return msgs[Math.floor(Math.random() * msgs.length)];
        }

        // Break down tasks
        if (text.match(/break|subtask|smaller|chunk|piece|split/)) {
            if (incomplete.length > 0) {
                const task = panic[0] || serious[0] || incomplete[0];
                return `✂️ Let me break down "${task.title}":\n\n1️⃣ Review the requirements (10 min)\n2️⃣ Gather materials & resources (15 min)\n3️⃣ Create an outline/plan (15 min)\n4️⃣ Work on first section (25 min)\n5️⃣ Review & refine (10 min)\n\n⏱️ Estimated total: ~75 minutes\n💡 Try tackling #1 right now in a focus session!`;
            }
            return "You don't have any tasks yet! Add a task first, then I can help break it down. 📝";
        }

        // Upload / syllabus
        if (text.match(/upload|syllabus|file|pdf|document|scan/)) {
            return "📄 Want to analyze a document?\n\nTap the 📎 button or use the 'Upload File' chip above to paste your syllabus content. I'll scan it for:\n\n• 📅 Important dates\n• 📝 Assignments & projects\n• 📊 Exam schedules\n• 📚 Reading lists";
        }

        // Stats
        if (text.match(/stats|progress|how am i|performance/)) {
            const streak = CramStore.get('streak') || {};
            const tree = CramStore.get('wisdomTree') || {};
            const stages = ['Seed 🌱', 'Small Plant 🌿', 'Young Tree 🌳', 'Wisdom Tree ✨', 'Ancient Tree 🔥'];
            return `📊 Your Progress Report:\n\n🔥 Streak: ${streak.current || 0} days (best: ${streak.best || 0})\n⏱️ Total focus: ${CramApp.formatDuration(stats.totalFocusMinutes || 0)}\n✅ Tasks completed: ${stats.totalTasksCompleted || 0}\n🌳 Wisdom Tree: ${stages[tree.stage || 0]}\n\nKeep going! Every session makes you stronger 💪`;
        }

        // Default
        const fallbacks = [
            "Hmm, I'm not sure about that, but I know you should probably start studying! 😄\n\nTry asking about study plans, workload analysis, or just say 'motivate me'!",
            "Interesting question! 🤔 I'm best at:\n\n• 📋 Creating study plans\n• 📊 Analyzing your workload\n• ⏰ Tracking deadlines\n• 💪 Motivation & encouragement\n• ✂️ Breaking down tasks\n\nWhat would you like help with?",
            "I'm still learning! 🌟 But I'm great at organizing your study life. Try one of the suggestion chips above!",
        ];
        return fallbacks[Math.floor(Math.random() * fallbacks.length)];
    }

    function uploadFile() {
        const modal = document.getElementById('upload-modal');
        if (modal) modal.classList.add('active');
    }

    function processUpload() {
        const content = document.getElementById('upload-content')?.value?.trim();
        const modal = document.getElementById('upload-modal');
        if (modal) modal.classList.remove('active');

        if (!content) {
            sendMessage('I want to upload a file');
            return;
        }

        addBubble('user', '📄 [Document uploaded]');
        isTyping = true;
        showTyping();

        // Multi-step analysis simulation
        const steps = [
            { delay: 1000, msg: '📄 Analyzing document...' },
            { delay: 2000, msg: '🔍 Scanning for key dates...' },
            { delay: 1500, msg: '⚙️ Processing content...' },
        ];

        let totalDelay = 0;
        steps.forEach((step, i) => {
            totalDelay += step.delay;
            setTimeout(() => {
                hideTyping();
                addBubble('ai', step.msg, false);
                if (i < steps.length - 1) showTyping();
            }, totalDelay);
        });

        totalDelay += 1500;
        setTimeout(() => {
            hideTyping();
            const futureDate = (days) => {
                const d = new Date();
                d.setDate(d.getDate() + days);
                return d.toLocaleDateString();
            };

            addBubble('ai', `📄 Document Analysis Complete!\n\n📅 Detected Dates:\n• Midterm Exam: ${futureDate(14)}\n• Project Due: ${futureDate(21)}\n• Final Exam: ${futureDate(35)}\n• Quiz: ${futureDate(7)}\n\n📚 Topics Found:\n• Chapters 1-5: Fundamentals\n• Chapters 6-10: Advanced Topics\n• Lab Sessions: 4 total\n\n✅ I've prepared study milestones based on this. Would you like me to create a study plan?`);
            isTyping = false;
        }, totalDelay);
    }

    const subjects = ['math','science','english','history','physics','chemistry','biology','programming','essay','project','presentation','report','lab','literature','economics','psychology','philosophy','calculus','algebra','statistics','writing','reading','research','thesis','dissertation'];
    const actions = ['finish','complete','study','review','read','write','prepare','submit','email','buy','practice','start','do','work','fix','update','edit','plan','organize','check','turn in','hand in','upload','download','print','send'];
    const urgency = ['tomorrow','today','urgent','asap','due','deadline','exam','test','quiz','final','midterm','tonight','morning','soon','important','critical'];

    function parseBrainDump(text) {
        const chunks = text
            .replace(/[,;]/g, '\n')
            .split('\n')
            .map(s => s.trim())
            .filter(s => s.length > 2);

        let items = [];
        if (chunks.length <= 1) {
            const words = text.split(/\s+/);
            let current = [];
            words.forEach(word => {
                const lower = word.toLowerCase();
                const isAction = actions.some(a => lower.startsWith(a));
                const isSubject = subjects.some(s => lower.includes(s));
                if ((isAction || isSubject) && current.length > 0) {
                    items.push(current.join(' '));
                    current = [word];
                } else {
                    current.push(word);
                }
            });
            if (current.length > 0) items.push(current.join(' '));
        } else {
            items = chunks;
        }

        return items.map(item => {
            const lower = item.toLowerCase();
            let priority = 'chill';
            let deadline = null;
            let title = item.trim();

            title = title.charAt(0).toUpperCase() + title.slice(1);

            const hasUrgency = urgency.some(u => lower.includes(u));
            const hasAction = actions.some(a => lower.includes(a));

            if (hasUrgency) {
                priority = 'panic';
                if (lower.includes('tomorrow')) {
                    const d = new Date();
                    d.setDate(d.getDate() + 1);
                    d.setHours(23, 59, 0, 0);
                    deadline = d.toISOString();
                } else if (lower.includes('today') || lower.includes('tonight')) {
                    const d = new Date();
                    d.setHours(23, 59, 0, 0);
                    deadline = d.toISOString();
                }
            } else if (hasAction) {
                priority = 'serious';
            }

            return { title, priority, deadline };
        }).filter(t => t.title.length > 2);
    }

    function openBrainDump() {
        const modal = document.getElementById('braindump-modal');
        if (modal) modal.classList.add('active');
        const textarea = document.getElementById('braindump-content');
        if (textarea) textarea.value = '';
        setTimeout(() => textarea?.focus(), 300);
    }

    function processBrainDump() {
        const content = document.getElementById('braindump-content')?.value?.trim();
        const modal = document.getElementById('braindump-modal');
        if (modal) modal.classList.remove('active');

        if (!content) {
            CramNotify?.warning("Type some thoughts first! 🧠");
            return;
        }

        addBubble('user', `🧠 [Brain Dump] ${content.substring(0, 60)}${content.length > 60 ? '...' : ''}`);
        isTyping = true;
        showTyping();

        const steps = [
            { delay: 800, msg: '🔍 Scanning your thoughts...' },
            { delay: 1000, msg: '🧠 Organizing chaos...' },
        ];

        let totalDelay = 0;
        steps.forEach((step, i) => {
            totalDelay += step.delay;
            setTimeout(() => {
                hideTyping();
                addBubble('ai', step.msg, false);
                if (i < steps.length - 1) showTyping();
            }, totalDelay);
        });

        totalDelay += 1000;
        setTimeout(() => {
            hideTyping();
            const parsed = parseBrainDump(content);
            if (parsed.length === 0) {
                addBubble('ai', "Hmm, I couldn't extract any structured tasks from that. Try typing action words like 'study', 'finish', or subjects like 'math', 'chemistry'! 🧐");
                isTyping = false;
                return;
            }

            parsed.forEach(t => {
                if (CramStore && typeof CramStore.addTask === 'function') {
                    CramStore.addTask(t);
                } else {
                    const tasks = CramStore.get('tasks') || [];
                    t.id = 'task_' + Math.random().toString(36).substr(2, 8);
                    t.completed = false;
                    t.createdAt = new Date().toISOString();
                    tasks.push(t);
                    CramStore.set('tasks', tasks);
                }
            });

            const priorityLabels = { chill: '😌 Chill', serious: '😬 Serious', panic: '💀 Panic Mode' };
            const listStr = parsed.map(t => `• **${t.title}** (${priorityLabels[t.priority]})`).join('\n');

            addBubble('ai', `🧹 I've cleaned your chaos! I parsed your brain dump and automatically added these ${parsed.length} tasks to your list:\n\n${listStr}\n\nGo check your **Tasks** tab to see them and add deadlines if needed! 📝`);
            isTyping = false;
            
            CramNotify?.success(`${parsed.length} tasks added! 🎉`);
        }, totalDelay);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 100);
    }

    return { init, sendMessage, sendFromInput, uploadFile, processUpload, openBrainDump, processBrainDump };
})();

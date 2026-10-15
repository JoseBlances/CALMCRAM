/**
 * CramCalm — AI Study Assistant (Google Gemini tutoring)
 * Falls back to local keyword replies if the API is unavailable.
 */
const CramAssistant = (() => {
    let isTyping = false;

    function apiBase() {
        return (window.CRAMCALM_API || '/CALMCRAM/api').replace(/\/$/, '');
    }

    function authHeaders() {
        const headers = { 'Content-Type': 'application/json' };
        const token = (typeof CramStore?.getToken === 'function')
            ? CramStore.getToken()
            : (localStorage.getItem('cramcalm_guest_token') || '');
        if (token) headers['X-Guest-Token'] = token;
        return headers;
    }

    function buildContext() {
        return {
            nickname: CramStore.get('user.nickname') || 'Scholar',
            tasks: CramStore.get('tasks') || [],
            stats: CramStore.get('stats') || {},
            streak: CramStore.get('streak') || {},
            academicState: CramStore.get('academicState') || 'stable',
            wisdomTree: CramStore.get('wisdomTree') || {},
        };
    }

    function init() {
        const page = new URLSearchParams(window.location.search).get('page');
        if (page !== 'assistant') return;

        const history = CramStore.get('chatHistory') || [];
        if (history.length === 0) {
            addBubble('ai', "Hey there! 👋 I'm your CramCalm study buddy, powered by Google Gemini. I can tutor you, build study plans from your real tasks, break down tough work, and keep you calm under pressure.\n\nTry a suggestion above or ask me anything about what you're studying!");
        } else {
            history.forEach(msg => addBubble(msg.role, msg.text, false));
        }
    }

    function addBubble(role, text, save = true) {
        const container = document.getElementById('chat-container');
        if (!container) return;

        const bubble = document.createElement('div');
        bubble.className = `chat-bubble ${role}`;
        bubble.innerHTML = escapeForBubble(text);
        container.appendChild(bubble);

        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
            bubble.scrollIntoView({ behavior: 'smooth', block: 'end' });
        }, 50);

        if (save) {
            const history = CramStore.get('chatHistory') || [];
            history.push({ role, text, time: new Date().toISOString() });
            if (history.length > 50) history.splice(0, history.length - 50);
            CramStore.set('chatHistory', history);
        }
    }

    function escapeForBubble(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
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

    async function askGemini(message, mode = 'chat') {
        // History already includes the just-saved user bubble — drop that last user turn
        // so we don't send the same message twice to Gemini.
        let history = CramStore.get('chatHistory') || [];
        if (mode === 'chat' && history.length) {
            const last = history[history.length - 1];
            if (last?.role === 'user' && last?.text === message) {
                history = history.slice(0, -1);
            }
        }
        history = history.slice(-12);

        const res = await fetch(apiBase() + '/assistant.php', {
            method: 'POST',
            headers: authHeaders(),
            body: JSON.stringify({
                message,
                mode,
                history,
                context: buildContext(),
            }),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) {
            const err = new Error(data.error || ('HTTP ' + res.status));
            err.needsKey = !!data.needsKey;
            err.needsAuth = !!data.needsAuth;
            throw err;
        }
        return data.reply;
    }

    async function sendMessage(text) {
        if (isTyping) return;
        addBubble('user', text);
        isTyping = true;
        showTyping();

        try {
            const reply = await askGemini(text, 'chat');
            hideTyping();
            addBubble('ai', reply);
        } catch (err) {
            console.warn('Gemini Study Buddy error:', err);
            hideTyping();
            if (err.needsKey) {
                addBubble('ai', "⚙️ Google AI isn't configured yet. Add your Gemini API key to `config.local.php`, then try again.");
            } else if (err.needsAuth) {
                addBubble('ai', 'Please log in again so I can keep tutoring you securely.');
            } else {
                addBubble('ai', "Sorry — I couldn't reach Google AI right now. Please try again in a moment.");
            }
        } finally {
            isTyping = false;
        }
    }

    function uploadFile() {
        const modal = document.getElementById('upload-modal');
        if (modal) modal.classList.add('active');
    }

    async function processUpload() {
        const content = document.getElementById('upload-content')?.value?.trim();
        const modal = document.getElementById('upload-modal');
        if (modal) modal.classList.remove('active');

        if (!content) {
            sendMessage('I want to upload a file');
            return;
        }

        addBubble('user', '📄 [Document uploaded for analysis]');
        isTyping = true;
        showTyping();

        try {
            const reply = await askGemini(content, 'document');
            hideTyping();
            addBubble('ai', reply);
        } catch (err) {
            hideTyping();
            addBubble('ai', "I couldn't reach Google AI to analyze that document right now. Paste the key sections in chat and I'll still try to help once the API is available.\n\n" + (err.message || ''));
        } finally {
            isTyping = false;
            const ta = document.getElementById('upload-content');
            if (ta) ta.value = '';
        }
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

        setTimeout(() => {
            hideTyping();
            const parsed = parseBrainDump(content);
            if (parsed.length === 0) {
                addBubble('ai', "Hmm, I couldn't extract structured tasks from that. Try action words like 'study' or 'finish', plus subjects like 'math' or 'chemistry'. 🧐");
                isTyping = false;
                return;
            }

            parsed.forEach(t => CramStore.addTask(t));

            const priorityLabels = { chill: '😌 Chill', serious: '😬 Serious', panic: '💀 Panic Mode' };
            const listStr = parsed.map(t => `• **${t.title}** (${priorityLabels[t.priority]})`).join('\n');
            addBubble('ai', `🧹 Cleaned your chaos — added ${parsed.length} tasks:\n\n${listStr}\n\nCheck the **Tasks** tab to refine deadlines. Want me to build a study plan from these?`);
            isTyping = false;
            CramNotify?.success(`${parsed.length} tasks added! 🎉`);
        }, 600);
    }

    function clearChat() {
        if (isTyping) {
            CramNotify?.info('Wait for the current reply to finish.');
            return;
        }
        if (!confirm('Clear the whole Study Buddy conversation?')) return;

        CramStore.set('chatHistory', []);
        const container = document.getElementById('chat-container');
        if (container) container.innerHTML = '';

        addBubble('ai', "Conversation cleared. ✨ Ask me anything whenever you're ready.");
        CramNotify?.success('Chat cleared');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 100);
    }

    return { init, sendMessage, sendFromInput, uploadFile, processUpload, openBrainDump, processBrainDump, clearChat };
})();

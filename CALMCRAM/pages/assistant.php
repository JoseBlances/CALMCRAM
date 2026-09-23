<div class="cc-page pb-nav" id="page-assistant">
    <div class="cc-page-header animate-on-enter">
        <div class="flex items-center gap-3">
            <span class="text-3xl animate-bob">🤖</span>
            <div>
                <h1 class="text-xl font-heading font-black">Study Buddy</h1>
                <p class="cc-text-xs cc-text-muted">Your AI-powered study assistant</p>
            </div>
        </div>
    </div>

    <!-- Suggested Prompts -->
    <div class="chip-row mb-4 animate-on-enter overflow-x-auto flex-nowrap pb-2" id="suggested-prompts">
        <button class="chip whitespace-nowrap" onclick="CramAssistant.sendMessage('Make me a study plan')">📋 Study Plan</button>
        <button class="chip whitespace-nowrap" onclick="CramAssistant.sendMessage('Analyze my workload')">📊 Workload</button>
        <button class="chip whitespace-nowrap" onclick="CramAssistant.sendMessage('Help me with deadlines')">⏰ Deadlines</button>
        <button class="chip whitespace-nowrap" onclick="CramAssistant.sendMessage('Motivate me')">💪 Motivate</button>
        <button class="chip whitespace-nowrap" onclick="CramAssistant.sendMessage('Break down a task for me')">✂️ Break Down</button>
        <button class="chip whitespace-nowrap" onclick="CramAssistant.openBrainDump()">🧠 Brain Dump</button>
        <button class="chip whitespace-nowrap" onclick="CramAssistant.uploadFile()">📄 Upload File</button>
    </div>

    <!-- Chat Container -->
    <div class="chat-container" id="chat-container"></div>

    <!-- Chat Input -->
    <div class="chat-input-bar" id="chat-input-bar">
        <div class="input-wrapper">
            <button class="cc-btn cc-btn-icon cc-btn-ghost" onclick="CramAssistant.uploadFile()" title="Upload file">📎</button>
            <input type="text" class="cc-input flex-1" id="chat-input" placeholder="Ask your study buddy..." 
                onkeydown="if(event.key==='Enter'){CramAssistant.sendFromInput();event.preventDefault();}">
            <button class="cc-btn cc-btn-icon cc-btn-primary" onclick="CramAssistant.sendFromInput()">➤</button>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="cc-modal-overlay" id="upload-modal">
    <div class="cc-modal">
        <div class="cc-modal-handle"></div>
        <h3 class="font-heading font-bold text-lg mb-2">📄File Upload</h3>
        <p class="cc-text-sm cc-text-muted mb-4">Paste your syllabus content or describe your document:</p>
        <textarea class="cc-input cc-textarea" id="upload-content" placeholder="Paste text content here..." rows="4"></textarea>
        <div class="flex gap-3 mt-4">
            <button class="cc-btn cc-btn-secondary flex-1" onclick="document.getElementById('upload-modal').classList.remove('active')">Cancel</button>
            <button class="cc-btn cc-btn-primary flex-1" onclick="CramAssistant.processUpload()">Analyze 🔍</button>
        </div>
    </div>
</div>

<!-- Brain Dump Modal -->
<div class="cc-modal-overlay" id="braindump-modal">
    <div class="cc-modal">
        <div class="cc-modal-handle"></div>
        <h3 class="font-heading font-bold text-lg mb-2">🧠 Brain Dump</h3>
        <p class="cc-text-sm cc-text-muted mb-4">Get it all out of your head. Type a chaotic list of thoughts, subjects, or deadlines (e.g., "math exam tomorrow, finish biology lab report due Friday"):</p>
        <textarea class="cc-input cc-textarea" id="braindump-content" placeholder="Type naturally, e.g., math exam tomorrow, write history essay..." rows="4"></textarea>
        <div class="flex gap-3 mt-4">
            <button class="cc-btn cc-btn-secondary flex-1" onclick="document.getElementById('braindump-modal').classList.remove('active')">Cancel</button>
            <button class="cc-btn cc-btn-primary flex-1" onclick="CramAssistant.processBrainDump()">Clean My Chaos 🧹</button>
        </div>
    </div>
</div>

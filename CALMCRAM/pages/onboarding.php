<div class="onboarding-page" id="page-onboarding">
    <div class="animate-on-enter">
        <span class="onboarding-emoji">🌿</span>
        <h1 class="onboarding-title">Welcome to CramCalm</h1>
        <p class="onboarding-subtitle">Your emotionally intelligent study companion. No more cramming, no more stress.</p>
    </div>

    <div class="w-full max-w-xs animate-on-enter" style="animation-delay: 0.2s;">
        <label class="cc-text-xs cc-text-muted font-heading font-bold uppercase tracking-wide block mb-2 text-left">What should we call you?</label>
        <input type="text" class="cc-input text-center text-lg" id="onboarding-nickname" placeholder="Your nickname" maxlength="20" autofocus
            onkeydown="if(event.key==='Enter') document.getElementById('onboarding-start').click();">
    </div>

    <button class="cc-btn cc-btn-primary cc-btn-lg mt-6 animate-on-enter" id="onboarding-start" style="animation-delay: 0.4s;"
        onclick="startOnboarding()">
        Let's Begin! 🚀
    </button>

    <p class="cc-text-xs cc-text-muted mt-8 animate-on-enter" style="animation-delay: 0.6s;">
        🌙 Fun fact: Students who plan ahead are 3x less likely to pull all-nighters!
    </p>
</div>

<script>
function startOnboarding() {
    const input = document.getElementById('onboarding-nickname');
    const nickname = input?.value?.trim();
    
    if (!nickname) {
        input?.classList.add('animate-wiggle');
        setTimeout(() => input?.classList.remove('animate-wiggle'), 600);
        input?.focus();
        return;
    }

    CramStore.set('user.nickname', nickname);
    CramStore.set('user.onboarded', true);
    CramStore.set('user.createdAt', new Date().toISOString());

    // Animate out
    const page = document.getElementById('page-onboarding');
    if (page) {
        page.style.transition = 'opacity 0.5s, transform 0.5s';
        page.style.opacity = '0';
        page.style.transform = 'scale(0.95)';
    }

    setTimeout(() => {
        window.location.href = '?page=dashboard';
    }, 500);
}
</script>

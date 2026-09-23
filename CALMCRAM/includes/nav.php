<!-- Bottom Navigation Bar -->
<nav id="bottom-nav" class="fixed bottom-0 left-0 right-0 z-50 transition-transform duration-300">
    <div class="mx-auto max-w-lg">
        <div class="nav-bar backdrop-blur-xl border-t px-2 pt-2 pb-[env(safe-area-inset-bottom,8px)] flex justify-around items-center" style="background: var(--cc-glass); border-color: var(--cc-border);">
            <?php foreach (NAV_TABS as $tab): ?>
                <?php $isActive = is_current_page($tab['page']); ?>
                <a href="<?= page_url($tab['page']) ?>" 
                   class="nav-tab group flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-2xl transition-all duration-300 relative <?= $isActive ? 'active' : '' ?>"
                   data-page="<?= $tab['page'] ?>">
                    
                    <?php if ($isActive): ?>
                    <span class="absolute -top-0.5 w-8 h-1 rounded-full bg-[var(--cc-primary)] animate-fade-in"></span>
                    <?php endif; ?>
                    
                    <span class="nav-icon w-6 h-6 flex items-center justify-center transition-all duration-300 <?= $isActive ? 'scale-110' : 'opacity-60 group-hover:opacity-100' ?>">
                        <?php echo match($tab['icon']) {
                            'home' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
                            'tasks' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
                            'focus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
                            'assistant' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
                            'profile' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
                            default => '<span class="text-lg">●</span>'
                        }; ?>
                    </span>
                    
                    <span class="nav-label text-[10px] font-heading font-semibold transition-all duration-300 <?= $isActive ? 'text-[var(--cc-primary)]' : 'text-[var(--cc-muted)] group-hover:text-[var(--cc-text)]' ?>">
                        <?= $tab['label'] ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</nav>

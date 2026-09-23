/**
 * CramCalm — Animation Engine
 * Framer Motion-style API using Web Animations API
 */
const CramAnimate = (() => {

    // Easing presets
    const easings = {
        linear: 'linear',
        ease: 'ease',
        easeIn: 'cubic-bezier(0.4, 0, 1, 1)',
        easeOut: 'cubic-bezier(0, 0, 0.2, 1)',
        easeInOut: 'cubic-bezier(0.4, 0, 0.2, 1)',
        spring: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
        bounce: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
        smooth: 'cubic-bezier(0.23, 1, 0.32, 1)',
    };

    // Check for reduced motion preference
    function prefersReducedMotion() {
        return window.matchMedia('(prefers-reduced-motion: reduce)').matches ||
               CramStore?.get('settings.reducedMotion');
    }

    /**
     * Animate an element with keyframes
     * @param {Element} el 
     * @param {Object} keyframes - CSS properties to animate { opacity: [0, 1], transform: ['translateY(20px)', 'translateY(0)'] }
     * @param {Object} options - { duration, delay, easing, fill }
     * @returns {Animation}
     */
    function animate(el, keyframes, options = {}) {
        if (!el) return null;
        if (prefersReducedMotion()) {
            // Apply final state immediately
            const finalState = {};
            for (const [prop, values] of Object.entries(keyframes)) {
                finalState[prop] = Array.isArray(values) ? values[values.length - 1] : values;
            }
            Object.assign(el.style, finalState);
            return null;
        }

        const config = {
            duration: (options.duration || 0.3) * 1000,
            delay: (options.delay || 0) * 1000,
            easing: easings[options.easing] || options.easing || easings.easeOut,
            fill: options.fill || 'forwards',
            iterations: options.iterations || 1,
        };

        // Convert { opacity: [0, 1], y: [20, 0] } → WAAPI keyframes
        const frames = convertKeyframes(keyframes);
        
        try {
            return el.animate(frames, config);
        } catch (e) {
            console.warn('Animation failed:', e);
            return null;
        }
    }

    /**
     * Convert simplified keyframes to WAAPI format
     */
    function convertKeyframes(keyframes) {
        const props = Object.keys(keyframes);
        const frameCount = Math.max(...props.map(p => 
            Array.isArray(keyframes[p]) ? keyframes[p].length : 2
        ));

        const frames = [];
        for (let i = 0; i < frameCount; i++) {
            const frame = {};
            for (const prop of props) {
                const values = Array.isArray(keyframes[prop]) ? keyframes[prop] : [keyframes[prop], keyframes[prop]];
                const value = values[Math.min(i, values.length - 1)];
                
                // Handle shortcuts
                const cssProp = propAliases[prop] || prop;
                if (cssProp === 'transform') {
                    frame.transform = value;
                } else if (prop === 'y') {
                    frame.transform = (frame.transform || '') + ` translateY(${typeof value === 'number' ? value + 'px' : value})`;
                } else if (prop === 'x') {
                    frame.transform = (frame.transform || '') + ` translateX(${typeof value === 'number' ? value + 'px' : value})`;
                } else if (prop === 'scale') {
                    frame.transform = (frame.transform || '') + ` scale(${value})`;
                } else if (prop === 'rotate') {
                    frame.transform = (frame.transform || '') + ` rotate(${typeof value === 'number' ? value + 'deg' : value})`;
                } else {
                    frame[cssProp] = value;
                }
            }
            frames.push(frame);
        }
        return frames;
    }

    const propAliases = {
        bg: 'backgroundColor',
        color: 'color',
    };

    /**
     * Staggered animation for a list of elements
     */
    function stagger(elements, keyframes, options = {}) {
        const delay = options.staggerDelay || 0.05;
        const animations = [];
        
        const els = elements instanceof NodeList ? Array.from(elements) : elements;
        els.forEach((el, i) => {
            const anim = animate(el, keyframes, {
                ...options,
                delay: (options.delay || 0) + (i * delay),
            });
            if (anim) animations.push(anim);
        });

        return animations;
    }

    /**
     * Animate element entrance (common pattern)
     */
    function enter(el, variant = 'fadeUp', options = {}) {
        const variants = {
            fadeUp: { opacity: [0, 1], y: [20, 0] },
            fadeDown: { opacity: [0, 1], y: [-20, 0] },
            fadeLeft: { opacity: [0, 1], x: [-20, 0] },
            fadeRight: { opacity: [0, 1], x: [20, 0] },
            fadeScale: { opacity: [0, 1], scale: [0.9, 1] },
            popIn: { opacity: [0, 1], scale: [0.5, 1.05, 1] },
            slideUp: { y: ['100%', '0%'] },
            fade: { opacity: [0, 1] },
        };

        return animate(el, variants[variant] || variants.fadeUp, {
            duration: 0.4,
            easing: 'spring',
            ...options,
        });
    }

    /**
     * Animate element exit
     */
    function exit(el, variant = 'fadeDown', options = {}) {
        const variants = {
            fadeDown: { opacity: [1, 0], y: [0, 20] },
            fadeUp: { opacity: [1, 0], y: [0, -20] },
            fadeScale: { opacity: [1, 0], scale: [1, 0.9] },
            fade: { opacity: [1, 0] },
        };

        return animate(el, variants[variant] || variants.fadeDown, {
            duration: 0.25,
            easing: 'easeIn',
            ...options,
        });
    }

    /**
     * Page transition
     */
    function pageEnter(container) {
        if (!container) return;
        const children = container.querySelectorAll('.animate-on-enter');
        if (children.length > 0) {
            stagger(children, { opacity: [0, 1], y: [15, 0] }, {
                duration: 0.4,
                easing: 'spring',
                staggerDelay: 0.06,
            });
        } else {
            enter(container, 'fadeUp', { duration: 0.3 });
        }
    }

    /**
     * Intersection Observer for scroll-triggered animations
     */
    function observeEntrance(selector = '.animate-on-scroll') {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    enter(entry.target, 'fadeUp');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll(selector).forEach(el => {
            el.style.opacity = '0';
            observer.observe(el);
        });
    }

    /**
     * Tap/press feedback
     */
    function tapFeedback(el) {
        if (!el || prefersReducedMotion()) return;
        
        el.addEventListener('touchstart', () => {
            animate(el, { scale: [1, 0.96] }, { duration: 0.1, easing: 'easeOut' });
        }, { passive: true });

        el.addEventListener('touchend', () => {
            animate(el, { scale: [0.96, 1] }, { duration: 0.3, easing: 'spring' });
        }, { passive: true });
    }

    /**
     * Number counter animation
     */
    function countUp(el, target, options = {}) {
        const duration = options.duration || 1000;
        const start = options.start || 0;
        const suffix = options.suffix || '';
        const prefix = options.prefix || '';
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Ease out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(start + (target - start) * eased);
            
            el.textContent = prefix + current + suffix;
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }
        
        requestAnimationFrame(update);
    }

    return {
        animate, stagger, enter, exit,
        pageEnter, observeEntrance, tapFeedback, countUp,
        easings, prefersReducedMotion,
    };
})();

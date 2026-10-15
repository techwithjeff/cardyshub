import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';

Alpine.plugin(collapse);
Alpine.plugin(focus);

window.Alpine = Alpine;

Alpine.start();

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Scroll reveal: fires once per element, then unobserves.
if (!prefersReducedMotion && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    const children = entry.target.querySelectorAll('[data-reveal-child]');
                    const items = children.length ? Array.from(children) : [entry.target];
                    items.forEach((el, i) => {
                        setTimeout(() => el.classList.add('is-visible'), i * 60);
                    });
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.15, rootMargin: '0px 0px -80px 0px' }
    );

    document.querySelectorAll('[data-reveal-section]').forEach((section) => {
        section.querySelectorAll('[data-reveal-child]').forEach((el) => el.setAttribute('data-reveal', ''));
        observer.observe(section);
    });

    document.querySelectorAll('[data-reveal]:not([data-reveal-child])').forEach((el) => observer.observe(el));
} else {
    document.querySelectorAll('[data-reveal], [data-reveal-child]').forEach((el) => el.classList.add('is-visible'));
}

// Header scroll behaviour lives in public/js/site.js.
//
// It was moved out of this bundle because the header now condenses instead of
// hiding, and this file cannot be recompiled on the current machine (no Node
// toolchain). Keeping a second implementation here would mean the built bundle
// and the live behaviour disagreed. Fold site.js back in here once `npm run
// build` is available again, and drop the extra <script> from the layout.

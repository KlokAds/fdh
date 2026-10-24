import { bindPushCard } from './push';

/*
 * Progressive enhancements for the public site and admin panel. Livewire's wire:navigate swaps pages
 * without a full reload, so state is re-applied on `livewire:navigated` and clicks use event delegation.
 */
const root = document.documentElement;
root.classList.add('js');

/* ---------- Theme (light / dark) ---------- */
function storedTheme() {
    try {
        return localStorage.getItem('fdh-theme');
    } catch {
        return null;
    }
}

function resolveTheme() {
    const theme = storedTheme() || root.getAttribute('data-default-theme') || 'light';

    if (theme === 'system') {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    return theme === 'dark' ? 'dark' : 'light';
}

function applyTheme() {
    root.setAttribute('data-theme', resolveTheme());
}

function toggleTheme() {
    const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
    try {
        localStorage.setItem('fdh-theme', next);
    } catch {
        // Storage blocked: the change still applies for this page view.
    }
    root.setAttribute('data-theme', next);
}

/* ---------- Mobile menu ---------- */
function setMenu(open, animate = true) {
    const menu = document.getElementById('menu');
    if (!menu) {
        return;
    }
    document.body.classList.toggle('menu-anim', animate);
    menu.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open);
    document.querySelectorAll('.burger[data-menu-toggle]').forEach((b) => b.setAttribute('aria-expanded', String(open)));
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-theme-toggle]')) {
        toggleTheme();
        return;
    }

    if (event.target.closest('[data-menu-toggle]')) {
        setMenu(!document.getElementById('menu')?.classList.contains('is-open'));
        return;
    }

    const ddToggle = event.target.closest('[data-dropdown-toggle]');
    if (ddToggle) {
        const dropdown = ddToggle.closest('[data-dropdown]');
        const open = !dropdown.classList.contains('is-open');
        dropdown.classList.toggle('is-open', open);
        // Only one dropdown open at a time.
        document.querySelectorAll('[data-dropdown].is-open').forEach((d) => d !== dropdown && d.classList.remove('is-open'));
        ddToggle.setAttribute('aria-expanded', String(open));
        return;
    }

    // Clicking outside an open dropdown closes it.
    if (!event.target.closest('[data-dropdown]')) {
        document.querySelectorAll('[data-dropdown].is-open').forEach((d) => d.classList.remove('is-open'));
    }

    if (event.target.closest('[data-sidebar-toggle]')) {
        document.getElementById('sidebar')?.classList.toggle('is-open');
        document.body.classList.toggle('sidebar-open');
        return;
    }

    // Choosing a link closes any open menu.
    if (event.target.closest('#menu a, #sidebar a')) {
        setMenu(false);
        document.querySelectorAll('[data-dropdown].is-open').forEach((d) => d.classList.remove('is-open'));
        document.getElementById('sidebar')?.classList.remove('is-open');
        document.body.classList.remove('sidebar-open');
    }
});

/* ---------- Custom select (service dropdown) ---------- */
function closeSelects(except = null) {
    document.querySelectorAll('[data-cselect][open]').forEach((d) => {
        if (d !== except) {
            d.open = false;
        }
    });
}

document.addEventListener('click', (event) => {
    const select = event.target.closest('[data-cselect]');

    // Close after choosing an option, and when clicking anywhere else.
    if (select && event.target.closest('.cselect-option')) {
        select.open = false;
        select.querySelector('summary')?.focus();
    }
    closeSelects(select);
});

document.addEventListener('toggle', (event) => {
    const select = event.target;
    if (select.matches?.('[data-cselect]') && select.open) {
        (select.querySelector('.cselect-option.is-selected') || select.querySelector('.cselect-option'))?.focus({ preventScroll: true });
    }
}, true);

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setMenu(false);
        document.querySelectorAll('[data-dropdown].is-open').forEach((d) => d.classList.remove('is-open'));
        const open = document.querySelector('[data-cselect][open]');
        if (open) {
            open.open = false;
            open.querySelector('summary')?.focus();
        }
        return;
    }

    const select = event.target.closest?.('[data-cselect]');
    if (!select || !['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
        return;
    }

    event.preventDefault();
    if (!select.open) {
        select.open = true;
        return;
    }

    const options = [...select.querySelectorAll('.cselect-option')];
    const current = options.indexOf(document.activeElement);
    const next = {
        ArrowDown: Math.min(current + 1, options.length - 1),
        ArrowUp: Math.max(current - 1, 0),
        Home: 0,
        End: options.length - 1,
    }[event.key];
    options[next]?.focus();
});

/* ---------- Header shadow on scroll ---------- */
function updateHeader() {
    document.querySelector('[data-header]')?.classList.toggle('is-scrolled', window.scrollY > 10);
}

/* ---------- Reveal on scroll ---------- */
let observer;

function bindReveals() {
    const items = document.querySelectorAll('.reveal:not(.is-visible)');

    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    observer?.disconnect();
    observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

    items.forEach((el) => observer.observe(el));
}

// Fallback for browsers or embedded views where the observer misses fast scrolls.
function revealInView() {
    const limit = window.innerHeight * 1.05;
    document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => {
        if (el.getBoundingClientRect().top < limit) {
            el.classList.add('is-visible');
        }
    });
}

window.addEventListener('scroll', () => requestAnimationFrame(() => {
    revealInView();
    updateHeader();
}), { passive: true });

function onPageReady() {
    bindPushCard();
    applyTheme();
    setMenu(false, false);
    updateHeader();
    bindReveals();
}

/* ---------- Meta Pixel (loaded only when a Pixel ID is set in the admin) ---------- */
let firstNavigation = true;
document.addEventListener('livewire:navigated', () => {
    // The pixel already tracked the first page load; count every later wire:navigate page.
    if (!firstNavigation && typeof window.fbq === 'function') {
        window.fbq('track', 'PageView');
    }
    firstNavigation = false;
});
window.addEventListener('fdh-lead', (event) => {
    if (typeof window.fbq === 'function') {
        window.fbq('track', 'Lead', { content_name: event.detail?.service || undefined }, { eventID: event.detail?.eventId });
    }
});

document.addEventListener('DOMContentLoaded', onPageReady);
document.addEventListener('livewire:navigated', onPageReady);
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);

// Elements re-rendered by Livewire (e.g. filtering projects) should appear immediately.
document.addEventListener('livewire:init', () => {
    window.Livewire.hook('morphed', () => {
        requestAnimationFrame(() => document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => el.classList.add('is-visible')));
    });
});

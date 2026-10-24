import { syncEditors } from './editor';
import { bindPushCard } from './push';

/*
 * Progressive enhancements for the public site and admin panel. Livewire's wire:navigate swaps pages
 * without a full reload, so state is re-applied on `livewire:navigated` and clicks use event delegation.
 */
const root = document.documentElement;
// Automated browsers (search engine renderers, page screenshots) get the page fully visible, without scroll reveals.
if (!navigator.webdriver) {
    root.classList.add('js');
}

/* ---------- Theme (light / dark) ---------- */
function storedTheme() {
    try {
        return localStorage.getItem('fdh-theme');
    } catch {
        return null;
    }
}

function resolveTheme() {
    const fallback = root.getAttribute('data-default-theme') || 'light';
    const theme = fallback === 'device' ? 'system' : storedTheme() || fallback;

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

/* ---------- Confirm modal (replaces the browser's confirm box) ---------- */
let confirmTarget = null;

function closeConfirm() {
    const modal = document.getElementById('confirm-modal');
    if (modal) {
        modal.hidden = true;
    }
    confirmTarget?.focus();
}

// Capture phase: runs before Livewire's wire:click, which only fires once the user agrees.
document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');
    const modal = document.getElementById('confirm-modal');
    if (!trigger || !modal) {
        return;
    }
    if (trigger.dataset.confirmed === '1') {
        delete trigger.dataset.confirmed;
        return;
    }
    event.preventDefault();
    event.stopImmediatePropagation();
    confirmTarget = trigger;
    modal.querySelector('#confirm-text').textContent = trigger.dataset.confirm;
    modal.hidden = false;
    modal.querySelector('[data-confirm-ok]').focus();
}, true);

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-confirm-cancel]')) {
        closeConfirm();
    } else if (event.target.closest('[data-confirm-ok]') && confirmTarget) {
        const target = confirmTarget;
        closeConfirm();
        target.dataset.confirmed = '1';
        target.click();
    }
});

/* ---------- Styled select (drives a hidden native select) ---------- */
function closeUiSelects(except = null) {
    document.querySelectorAll('[data-ui-select].is-open').forEach((box) => {
        if (box !== except) {
            box.classList.remove('is-open');
            box.querySelector('[data-ui-select-toggle]')?.setAttribute('aria-expanded', 'false');
        }
    });
}

function chooseUiOption(option) {
    const box = option.closest('[data-ui-select]');
    const native = box.querySelector('select');
    native.value = option.dataset.value;
    native.dispatchEvent(new Event('input', { bubbles: true }));
    native.dispatchEvent(new Event('change', { bubbles: true }));
    box.querySelectorAll('[role="option"]').forEach((o) => o.setAttribute('aria-selected', String(o === option)));
    const value = box.querySelector('.ui-select-value');
    value.textContent = option.textContent.trim();
    value.classList.toggle('is-placeholder', option.dataset.value === '');
    closeUiSelects();
    box.querySelector('[data-ui-select-toggle]').focus();
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-ui-select-toggle]');
    if (toggle) {
        const box = toggle.closest('[data-ui-select]');
        const open = !box.classList.contains('is-open');
        closeUiSelects(box);
        box.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        if (open) {
            (box.querySelector('[aria-selected="true"]') || box.querySelector('[role="option"]'))?.focus();
        }
        return;
    }
    const option = event.target.closest('[data-ui-select] [role="option"]');
    if (option) {
        chooseUiOption(option);
        return;
    }
    closeUiSelects();
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeUiSelects();
        if (!document.getElementById('confirm-modal')?.hidden) {
            closeConfirm();
        }
        return;
    }
    const option = event.target.closest?.('[data-ui-select] [role="option"]');
    if (!option || !['ArrowDown', 'ArrowUp'].includes(event.key)) {
        return;
    }
    event.preventDefault();
    const options = [...option.closest('[data-ui-select]').querySelectorAll('[role="option"]')];
    const next = options[options.indexOf(option) + (event.key === 'ArrowDown' ? 1 : -1)];
    next?.focus();
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

/* ---------- Count-up for hero figures (keeps Bangla or Latin digits and any text around them) ---------- */
const BN_DIGITS = '০১২৩৪৫৬৭৮৯';

function animateCount(el) {
    const text = el.textContent.trim();
    const match = text.match(/[0-9০-৯][0-9০-৯,]*/);
    if (!match || el.dataset.counted) {
        return;
    }
    el.dataset.counted = '1';
    const bangla = /[০-৯]/.test(match[0]);
    const target = Number(match[0].replace(/,/g, '').replace(/[০-৯]/g, (d) => BN_DIGITS.indexOf(d)));
    if (!target || target > 1000000) {
        return;
    }
    const [before, after] = [text.slice(0, match.index), text.slice(match.index + match[0].length)];
    const format = (n) => {
        const plain = n.toLocaleString('en-US');
        return bangla ? plain.replace(/[0-9]/g, (d) => BN_DIGITS[d]) : plain;
    };
    const start = performance.now();
    const step = (now) => {
        const progress = Math.min(1, (now - start) / 1200);
        el.textContent = before + format(Math.round(target * (1 - (1 - progress) ** 3))) + after;
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };
    requestAnimationFrame(step);
}

function bindCounters() {
    const items = document.querySelectorAll('[data-count]:not([data-counted])');
    if (!items.length || !root.classList.contains('js') || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animateCount(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.6 });
    items.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', bindCounters);
document.addEventListener('livewire:navigated', bindCounters);

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
document.addEventListener('DOMContentLoaded', syncEditors);
document.addEventListener('livewire:navigated', syncEditors);
document.addEventListener('livewire:navigated', onPageReady);
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);

// Elements re-rendered by Livewire (e.g. filtering projects) should appear immediately.
document.addEventListener('livewire:init', () => {
    // Editors pick up values Livewire sets after a response (e.g. opening another item to edit).
    window.Livewire.hook('commit', ({ succeed }) => succeed(() => setTimeout(syncEditors, 0)));
    window.Livewire.hook('morphed', () => {
        syncEditors();
        requestAnimationFrame(() => document.querySelectorAll('.reveal:not(.is-visible)').forEach((el) => el.classList.add('is-visible')));
    });
});

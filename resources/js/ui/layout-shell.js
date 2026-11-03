import { closeCompactSidebar, toggleCompactSidebar } from './sidebar-compact';

function isMobile() {
    return window.innerWidth < 768;
}

function getOverlayEl() {
    return document.querySelector('.vertical-overlay');
}

function setHamburgerOpenState(open) {
    const button = document.getElementById('topnav-hamburger-icon');
    if (button) button.setAttribute('aria-expanded', String(isMobile() ? !!open : document.documentElement.getAttribute('data-sidebar-size') !== 'sm'));
    const icon = document.querySelector('#topnav-hamburger-icon .hamburger-icon');
    if (!icon) return;
    icon.classList.toggle('open', !!open);
}

function applyDesktopSidebarState(isCollapsed) {
    closeCompactSidebar();
    const html = document.documentElement;
    html.setAttribute('data-sidebar-size', isCollapsed ? 'sm' : 'lg');
    setHamburgerOpenState(isCollapsed);

    if (isCollapsed) {
        document.querySelectorAll('.app-menu .menu-dropdown').forEach((dropdown) => {
            dropdown.classList.remove('show', 'collapsing');
            dropdown.style.display = 'none';
        });

        document.querySelectorAll('.app-menu .menu-link[data-bs-toggle="collapse"]').forEach((trigger) => {
            trigger.classList.add('collapsed');
            trigger.setAttribute('aria-expanded', 'false');
        });
    }

    try {
        sessionStorage.setItem('data-sidebar-size', isCollapsed ? 'sm' : 'lg');
    } catch (err) {
        // ignore storage failures (private mode, etc.)
    }
}

function closeMobileSidebar() {
    document.body.classList.remove('vertical-sidebar-enable');
    setHamburgerOpenState(false);
    syncOverlayState();
}

function syncOverlayState() {
    const overlay = getOverlayEl();
    if (!overlay) return;

    const shouldEnableOverlay = isMobile() && document.body.classList.contains('vertical-sidebar-enable');
    overlay.style.display = shouldEnableOverlay ? 'block' : 'none';
    overlay.style.pointerEvents = shouldEnableOverlay ? 'auto' : 'none';
}

function initTopbarDropdowns() {
    const bootstrapDropdown = window.bootstrap && window.bootstrap.Dropdown;
    const topbar = document.getElementById('page-topbar');
    if (!bootstrapDropdown || !topbar) return;

    topbar.querySelectorAll('[data-bs-toggle="dropdown"]').forEach((trigger) => {
        if (trigger.dataset.boundTopbarDropdown === '1') return;
        trigger.dataset.boundTopbarDropdown = '1';
        bootstrapDropdown.getOrCreateInstance(trigger);
    });
}

function initSidebarCollapseToggles() {
    const sidebar = document.querySelector('.app-menu');
    if (!sidebar) return;

    const syncTriggerState = (trigger, target) => {
        const isOpen = target.classList.contains('show');
        trigger.classList.toggle('collapsed', !isOpen);
        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    };

    sidebar.querySelectorAll('.menu-link[data-bs-toggle="collapse"]').forEach((trigger) => {
        if (trigger.dataset.boundSidebarCollapse === '1') return;
        trigger.dataset.boundSidebarCollapse = '1';

        const selector = trigger.getAttribute('href') || trigger.dataset.bsTarget;
        if (!selector || !selector.startsWith('#')) return;

        const target = document.querySelector(selector);
        if (!target) return;

        syncTriggerState(trigger, target);
        target.classList.add('collapse');
        target.style.display = target.classList.contains('show') ? 'block' : 'none';

        const toggleTarget = (forceClose = false) => {
            const isOpen = target.classList.contains('show');
            target.classList.remove('collapsing');

            if (isOpen || forceClose) {
                target.classList.remove('show');
                target.style.display = 'none';
            } else {
                target.classList.add('show');
                target.style.display = 'block';
            }
            syncTriggerState(trigger, target);
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopImmediatePropagation();
            event.stopPropagation();
            if (!isMobile() && document.documentElement.getAttribute('data-sidebar-size') === 'sm') {
                toggleCompactSidebar(trigger, target);
                return;
            }
            toggleTarget(false);
        }, true);

        trigger.addEventListener('dblclick', (event) => {
            event.preventDefault();
            event.stopImmediatePropagation();
            event.stopPropagation();
            if (!isMobile() && document.documentElement.getAttribute('data-sidebar-size') === 'sm') {
                closeCompactSidebar(true);
                return;
            }
            toggleTarget(true);
        }, true);
    });
}

function initFullscreenToggle() {
    const btn = document.querySelector('[data-toggle="fullscreen"]');
    if (!btn) return;
    if (btn.dataset.boundFullscreen === '1') return;
    btn.dataset.boundFullscreen = '1';
    const icon = btn.querySelector('i');

    const updateIcon = () => {
        if (!icon) return;
        const inFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
        icon.classList.toggle('bx-fullscreen', !inFullscreen);
        icon.classList.toggle('bx-exit-fullscreen', inFullscreen);
    };

    const enterFullscreen = () => {
        const root = document.documentElement;
        if (root.requestFullscreen) return root.requestFullscreen();
        if (root.webkitRequestFullscreen) return root.webkitRequestFullscreen();
        return Promise.resolve();
    };

    const exitFullscreen = () => {
        if (document.exitFullscreen) return document.exitFullscreen();
        if (document.webkitExitFullscreen) return document.webkitExitFullscreen();
        return Promise.resolve();
    };

    btn.addEventListener('click', (event) => {
        event.preventDefault();
        const inFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
        Promise.resolve(inFullscreen ? exitFullscreen() : enterFullscreen()).finally(updateIcon);
    });

    document.addEventListener('fullscreenchange', updateIcon);
    document.addEventListener('webkitfullscreenchange', updateIcon);
    updateIcon();
}

function toggleSidebar() {
    if (isMobile()) {
        document.documentElement.setAttribute('data-sidebar-size', 'lg');
        const enabled = document.body.classList.toggle('vertical-sidebar-enable');
        setHamburgerOpenState(enabled);
        syncOverlayState();
        return;
    }
    const current = document.documentElement.getAttribute('data-sidebar-size');
    const isCollapsed = current === 'sm';
    applyDesktopSidebarState(!isCollapsed);
    syncOverlayState();
}

export function initLayoutShell() {
    const toggleButton = document.getElementById('topnav-hamburger-icon');
    if (!toggleButton) return;
    if (toggleButton.dataset.boundLayoutShell === '1') return;
    toggleButton.dataset.boundLayoutShell = '1';

    // The compact rail depends on actual SVG icons, not empty feather placeholders.
    window.feather?.replace();
    document.querySelectorAll('.app-menu .menu-link').forEach((link) => {
        const label = link.querySelector('span')?.textContent.trim();
        if (label) {
            link.setAttribute('aria-label', label);
            link.setAttribute('title', label);
        }
    });
    if (!isMobile()) {
        let size = document.documentElement.getAttribute('data-sidebar-size');
        try {
            const saved = sessionStorage.getItem('data-sidebar-size');
            if (saved === 'sm' || saved === 'lg') size = saved;
        } catch (_) { /* Storage can be unavailable in private sessions. */ }
        applyDesktopSidebarState(size === 'sm');
    } else {
        document.documentElement.setAttribute('data-sidebar-size', 'lg');
    }

    // Safety: reset stale mobile sidebar/overlay state at boot.
    closeMobileSidebar();
    initTopbarDropdowns();
    initSidebarCollapseToggles();
    const activeLink = document.querySelector('.app-menu #navbar-nav a.active');
    if (activeLink) requestAnimationFrame(() => {
        // Scroll only the menu vertically. scrollIntoView also scrolls clipped
        // horizontal ancestors, shifting the entire sidebar on long labels.
        const scroller = document.querySelector('.app-menu #scrollbar');
        if (!scroller || document.documentElement.getAttribute('data-sidebar-size') === 'sm') return;
        const viewport = scroller.getBoundingClientRect();
        const link = activeLink.getBoundingClientRect();
        if (link.top < viewport.top) scroller.scrollTop += link.top - viewport.top;
        else if (link.bottom > viewport.bottom) scroller.scrollTop += link.bottom - viewport.bottom;
    });
    initFullscreenToggle();

    toggleButton.addEventListener('click', (event) => {
        event.preventDefault();
        toggleSidebar();
    });

    const overlay = getOverlayEl();
    if (overlay) {
        overlay.addEventListener('click', () => {
            closeMobileSidebar();
        });
    }

    document.addEventListener('keydown', (event) => {
        if (
            event.key !== 'Escape' ||
            !isMobile() ||
            !document.body.classList.contains('vertical-sidebar-enable') ||
            document.querySelector('.modal.show')
        ) {
            return;
        }

        event.preventDefault();
        closeMobileSidebar();
        toggleButton.focus();
    });

    let wasMobile = isMobile();
    window.addEventListener('resize', () => {
        const mobile = isMobile();
        if (mobile !== wasMobile) {
            closeCompactSidebar();
            closeMobileSidebar();
            if (mobile) {
                document.documentElement.setAttribute('data-sidebar-size', 'lg');
            } else {
                let saved = 'sm';
                try { saved = sessionStorage.getItem('data-sidebar-size') || 'sm'; } catch (_) {}
                applyDesktopSidebarState(saved === 'sm');
            }
            wasMobile = mobile;
        }
        syncOverlayState();
    });
}

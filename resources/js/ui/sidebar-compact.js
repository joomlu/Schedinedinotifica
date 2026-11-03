let openPanel = null;
let opener = null;
let originalControls = null;
let listenersBound = false;

export function closeCompactSidebar(restoreFocus = false) {
    if (!openPanel) return;
    openPanel.remove();
    openPanel = null;
    if (opener) {
        opener.classList.remove('compact-submenu-open');
        opener.setAttribute('aria-expanded', 'false');
        if (originalControls) opener.setAttribute('aria-controls', originalControls);
        else opener.removeAttribute('aria-controls');
        if (restoreFocus) opener.focus();
    }
    opener = null;
}

function iconFor(label) {
    const rules = [
        [/schedin|document|titoli|rilasciat/i, 'file-text'], [/grupp|client|alloggiat/i, 'users'],
        [/questur/i, 'shield'], [/istat|ross1000|presenz|statistic/i, 'bar-chart-2'],
        [/tassa/i, 'credit-card'], [/via|comun/i, 'map-pin'],
        [/listin/i, 'list'], [/modalit|configuraz|impostaz/i, 'sliders'],
        [/tipolog|camere/i, 'grid'], [/qualit|categor/i, 'layers'],
        [/trattament/i, 'coffee'], [/supplement/i, 'plus-circle'],
        [/bambin/i, 'smile'], [/occupaz|persone/i, 'users'],
        [/geo/i, 'map-pin'], [/calend|period/i, 'calendar'],
        [/prezz|tariff/i, 'tag'], [/comunicaz|posta|email/i, 'mail'],
        [/contatt|proprietar|amministrat/i, 'users'], [/richiest/i, 'inbox'],
        [/preventiv|proform/i, 'file-text'],
    ];
    return rules.find(([pattern]) => pattern.test(label))?.[1] || 'file-text';
}

export function toggleCompactSidebar(trigger, target) {
    if (opener === trigger) {
        closeCompactSidebar(true);
        return;
    }
    closeCompactSidebar();
    const label = trigger.getAttribute('aria-label') || trigger.textContent.trim();
    const panel = document.createElement('nav');
    panel.id = 'compact-sidebar-options';
    panel.className = 'compact-sidebar-options';
    panel.setAttribute('aria-label', label);
    const header = document.createElement('div');
    header.className = 'compact-sidebar-options-heading';
    const title = document.createElement('span');
    title.textContent = label;
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'compact-sidebar-close';
    close.setAttribute('aria-label', 'Chiudi '+label);
    close.textContent = '×';
    close.addEventListener('click', () => closeCompactSidebar(true));
    header.append(title, close);
    const grid = document.createElement('div');
    grid.className = 'compact-sidebar-options-grid';
    // Usa esclusivamente i collegamenti autorizzati già renderizzati dal server.
    target.querySelectorAll('a.nav-link[href]:not([data-bs-toggle])').forEach(source => {
        if (source.getAttribute('href').startsWith('#')) return;
        const link = document.createElement('a');
        const text = source.textContent.trim();
        link.href = source.href;
        link.className = 'compact-sidebar-option';
        link.title = text;
        if (source.classList.contains('active')) link.setAttribute('aria-current', 'page');
        const icon = window.feather?.icons[iconFor(text)];
        if (icon) link.innerHTML = icon.toSvg({width:20, height:20, 'stroke-width':1, 'aria-hidden':'true'});
        const caption = document.createElement('span');
        caption.textContent = /capienza/i.test(text) ? 'Capienza camere' : /regole occupazione/i.test(text) ? 'Occupazione camere' : text;
        link.setAttribute('aria-label', text);
        link.append(caption);
        link.addEventListener('click', () => closeCompactSidebar());
        grid.append(link);
    });
    panel.append(header, grid);
    document.body.append(panel);
    openPanel = panel;
    opener = trigger;
    originalControls = trigger.getAttribute('aria-controls');
    trigger.setAttribute('aria-controls', panel.id);
    trigger.setAttribute('aria-expanded', 'true');
    trigger.classList.add('compact-submenu-open');
    const rail = trigger.closest('.navbar-menu').getBoundingClientRect();
    const anchor = trigger.getBoundingClientRect();
    panel.style.left = Math.min(rail.right + 8, window.innerWidth - panel.offsetWidth - 8)+'px';
    panel.style.top = Math.max(8, Math.min(anchor.top, window.innerHeight - panel.offsetHeight - 8))+'px';
    grid.querySelector('a')?.focus({preventScroll:true});
    panel.addEventListener('keydown', event => {
        if (!['ArrowRight','ArrowLeft','ArrowUp','ArrowDown','Home','End'].includes(event.key)) return;
        const links = [...grid.querySelectorAll('a')];
        const index = links.indexOf(document.activeElement);
        if (index < 0) return;
        event.preventDefault();
        const step = {ArrowRight:1, ArrowLeft:-1, ArrowDown:2, ArrowUp:-2};
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? links.length - 1 : (index + step[event.key] + links.length) % links.length;
        links[next]?.focus();
    });
    if (!listenersBound) {
        listenersBound = true;
        document.addEventListener('pointerdown', event => {
            if (openPanel && !openPanel.contains(event.target) && !opener?.contains(event.target)) closeCompactSidebar();
        });
        document.addEventListener('focusin', event => {
            if (openPanel && !openPanel.contains(event.target) && !opener?.contains(event.target)) closeCompactSidebar();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && openPanel) {
                event.preventDefault();
                closeCompactSidebar(true);
            }
        });
        window.addEventListener('resize', () => closeCompactSidebar());
        document.querySelector('.navbar-menu #scrollbar')?.addEventListener('scroll', () => closeCompactSidebar(), {passive:true});
    }
}

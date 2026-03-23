import { initOnce } from '../core/once';

function hideAllHelpPopovers(except = null) {
    if (typeof window === 'undefined' || !window.bootstrap) return;

    document.querySelectorAll('[data-ui="help-popover"]').forEach((trigger) => {
        if (except && trigger === except) return;
        const instance = window.bootstrap.Popover.getInstance(trigger);
        if (instance) {
            instance.hide();
        }
        trigger.setAttribute('aria-expanded', 'false');
    });
}

export function initHelpPopovers(root = document) {
    const scope = root || document;
    const triggers = scope.querySelectorAll
        ? scope.querySelectorAll('[data-ui="help-popover"]')
        : [];

    if (typeof window === 'undefined' || !window.bootstrap || typeof window.bootstrap.Popover !== 'function') {
        return;
    }

    triggers.forEach((el) => {
        if (!initOnce(el, 'help-popover')) return;

        const instance = new window.bootstrap.Popover(el, {
            trigger: 'manual',
            placement: el.getAttribute('data-bs-placement') || 'top',
            title: el.getAttribute('data-bs-title') || '',
            content: el.getAttribute('data-bs-content') || '',
            html: false,
            sanitize: true,
            container: 'body',
            template: `
                <div class="popover ui-help-popover" role="tooltip">
                    <div class="popover-arrow"></div>
                    <h3 class="popover-header"></h3>
                    <div class="popover-body"></div>
                    <button type="button" class="btn-close ui-help-close" aria-label="Chiudi"></button>
                </div>
            `,
        });

        el.setAttribute('aria-expanded', 'false');

        el.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const tip = typeof instance.getTipElement === 'function' ? instance.getTipElement() : null;
            const isOpen = !!(tip && tip.classList.contains('show'));

            hideAllHelpPopovers(el);

            if (isOpen) {
                instance.hide();
                el.setAttribute('aria-expanded', 'false');
                return;
            }

            instance.show();
            el.setAttribute('aria-expanded', 'true');

            const liveTip = typeof instance.getTipElement === 'function' ? instance.getTipElement() : null;
            const header = liveTip ? liveTip.querySelector('.popover-header') : null;
            let closeButton = liveTip ? liveTip.querySelector('.ui-help-close') : null;

            if (header && !closeButton) {
                closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'btn-close ui-help-close';
                closeButton.setAttribute('aria-label', 'Chiudi');
                header.appendChild(closeButton);
            }

            if (closeButton && !closeButton.dataset.bound) {
                closeButton.dataset.bound = '1';
                closeButton.addEventListener('click', (closeEvent) => {
                    closeEvent.preventDefault();
                    closeEvent.stopPropagation();
                    instance.hide();
                    el.setAttribute('aria-expanded', 'false');
                });
            }
        });

        el.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                instance.hide();
                el.setAttribute('aria-expanded', 'false');
            }
        });
    });

    if (!document.body.dataset.helpPopoverBound) {
        document.body.dataset.helpPopoverBound = '1';

        document.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof Element)) return;
            if (target.closest('.ui-help-popover')) return;
            if (target.closest('[data-ui="help-popover"]')) return;
            hideAllHelpPopovers();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                hideAllHelpPopovers();
            }
        });
    }
}

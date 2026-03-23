export function isDev() {
    return !!import.meta.env.DEV;
}

export function initOnce(el, key) {
    if (!el || !key) return false;
    const attr = `data-init-${key}`;
    if (el.hasAttribute(attr)) {
        if (isDev()) {
            // eslint-disable-next-line no-console
            console.error(`Inizializzazione duplicata per ${key}`, el, new Error().stack);
        }
        return false;
    }
    el.setAttribute(attr, '1');
    return true;
}

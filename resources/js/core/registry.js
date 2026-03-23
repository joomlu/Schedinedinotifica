import { isDev } from './once';

const REGISTRY = [];

export function register(name, fn) {
    REGISTRY.push({ name, fn });
}

export function initAll(root = document) {
    const list = [...REGISTRY];
    list.forEach(({ name, fn }) => {
        try {
            fn(root);
        } catch (err) {
            if (isDev()) {
                // eslint-disable-next-line no-console
                console.error(`Errore in init modulo ${name}`, err);
            }
        }
    });
}

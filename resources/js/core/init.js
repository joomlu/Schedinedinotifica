import { initAll } from './registry';

export function initUI(root = document) {
    initAll(root);
}

if (typeof window !== 'undefined') {
    window.UI = window.UI || {};
    window.UI.init = initUI;
}

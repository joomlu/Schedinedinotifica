/* Centralized live table filter with debounce and dataset-aware search */
import { initOnce } from '../core/once';

function normalize(text = '') {
    return text
        .toString()
        .toLowerCase()
        // Strip diacritics for a more forgiving search
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        // Normalize punctuation/apostrophes/symbols to spaces
        .replace(/[^\p{L}\p{N}\s]/gu, ' ')
        // Collapse consecutive spaces
        .replace(/\s+/g, ' ')
        .trim();
}

function debounce(fn, wait = 150) {
    let t;
    return function debounced(...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}

function safeQuery(target) {
    if (!target || typeof target !== 'string') return null;
    try {
        return document.querySelector(target);
    } catch (err) {
        return null;
    }
}

function collectRowText(row) {
    const parts = [];
    row.querySelectorAll('td').forEach((td) => {
        // Only consider visible cells to mirror UI
        const isVisible = td.offsetParent !== null || td.getClientRects().length > 0;
        if (!isVisible) return;
        const txt = td.innerText || td.textContent || '';
        if (txt) parts.push(txt);
    });

    // Include all data-* values to cover hidden identifiers/labels
    if (row.dataset) {
        Object.values(row.dataset).forEach((value) => {
            if (value !== null && value !== undefined && `${value}`.trim() !== '') {
                parts.push(value);
            }
        });
    }

    return normalize(parts.join(' '));
}

export function attachLiveTableFilter(options = {}) {
    const {
        input,
        table,
        emptyState,
        clearButton = null,
        meta = null,
        resultsBadge = null,
        counter = null,
        counterFormatter = null,
        debounceMs = 150,
    } = options;
    const inputEl = typeof input === 'string' ? safeQuery(input) : input;
    const tableEl = typeof table === 'string' ? safeQuery(table) : table;
    const emptyRow = emptyState
        ? (typeof emptyState === 'string' ? safeQuery(emptyState) : emptyState)
        : null;
    const clearBtnEl = clearButton
        ? (typeof clearButton === 'string' ? safeQuery(clearButton) : clearButton)
        : null;
    const metaEl = meta
        ? (typeof meta === 'string' ? safeQuery(meta) : meta)
        : null;
    const resultsBadgeEl = resultsBadge
        ? (typeof resultsBadge === 'string' ? safeQuery(resultsBadge) : resultsBadge)
        : null;
    const counterEl = counter
        ? (typeof counter === 'string' ? safeQuery(counter) : counter)
        : null;

    if (!inputEl || !tableEl) return;
    if (!initOnce(inputEl, 'liveTableFilter')) return;

    const tbody = tableEl.tBodies && tableEl.tBodies[0] ? tableEl.tBodies[0] : null;
    if (!tbody) return;

    const dataRows = Array.from(tbody.querySelectorAll('tr')).filter((tr) => !tr.dataset.emptyState);
    const cache = new Map();

    const runFilter = () => {
        const term = normalize(inputEl.value);
        let visibleCount = 0;
        const hasTerm = term.length > 0;

        dataRows.forEach((row) => {
            let haystack = cache.get(row);
            if (!haystack) {
                haystack = collectRowText(row);
                cache.set(row, haystack);
            }
            const match = term === '' || haystack.includes(term);
            row.classList.toggle('d-none', !match);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount += 1;
        });

        if (emptyRow) {
            const noResults = visibleCount === 0;
            emptyRow.classList.toggle('d-none', !noResults);
            emptyRow.style.display = noResults ? '' : 'none';
        }

        if (clearBtnEl) {
            clearBtnEl.style.display = hasTerm ? '' : 'none';
        }

        if (metaEl) {
            metaEl.style.display = hasTerm ? '' : 'none';
        }

        if (resultsBadgeEl) {
            if (hasTerm) {
                const total = dataRows.length;
                resultsBadgeEl.textContent = `${visibleCount} / ${total}`;
                resultsBadgeEl.style.display = '';
            } else {
                resultsBadgeEl.style.display = 'none';
                resultsBadgeEl.textContent = '';
            }
        }

        if (counterEl && typeof counterFormatter === 'function') {
            counterEl.textContent = counterFormatter(visibleCount, dataRows.length);
        }
    };

    const handler = debounce(runFilter, debounceMs);
    inputEl.addEventListener('input', handler);
    if (clearBtnEl) {
        clearBtnEl.addEventListener('click', () => {
            inputEl.value = '';
            runFilter();
            inputEl.focus();
        });
    }
    runFilter();

    return {
        destroy() {
            inputEl.removeEventListener('input', handler);
        },
    };
}

// Expose globally when needed
if (typeof window !== 'undefined') {
    window.attachLiveTableFilter = attachLiveTableFilter;
}

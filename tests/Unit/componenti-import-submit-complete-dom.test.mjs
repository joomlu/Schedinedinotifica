import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { test } from 'node:test';
import assert from 'node:assert/strict';

// Copia identificata A04: completa soltanto i doppi DOM e i binding della closure.
// Esegue il vero listener Blade, senza browser, server o database.
const source = readFileSync(new URL('../../resources/views/schedina/partials/scripts.blade.php', import.meta.url), 'utf8');
const listener = source.match(/schedinaForm\.addEventListener\('submit', \(event\) => \{([\s\S]*?)\n            \}\);/);
assert.ok(listener, 'Listener del form presente');

function submit(methodInput, submitter) {
    runInNewContext(listener[1], {
        event: { submitter: submitter && { matches: () => false, ...submitter } },
        schedinaForm: { querySelector: () => methodInput },
        saveModeInput: null,
        saveModeIntentInput: null,
        activeTabInput: null,
        componentIndexInput: null,
        componentIdInput: null,
    });
}

test('importazione mantiene POST anche quando il gestore UI disabilita il submitter', () => {
    const hidden = { value: 'PUT' };
    submit(hidden, { id: 'import-componenti-btn', disabled: true, getAttribute: () => null });
    assert.equal(hidden.value, 'POST');
});

test('un salvataggio successivo ripristina PUT anche dopo una navigazione annullata', () => {
    const hidden = { value: 'POST' };
    submit(hidden, { id: '', getAttribute: name => name === 'name' ? 'save_mode' : 'full' });
    assert.equal(hidden.value, 'PUT');
});

test('invio implicito conserva PUT e il form nuovo funziona senza hidden', () => {
    const hidden = { value: 'PUT' };
    submit(hidden, null);
    assert.equal(hidden.value, 'PUT');
    assert.doesNotThrow(() => submit(null, { id: 'import-componenti-btn', getAttribute: () => null }));
});

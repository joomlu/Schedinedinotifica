const assert = require('node:assert/strict');
const FormData = require('form-data');
const random = Math.random;
try {
 Math.random = () => 0.5;
 assert.notEqual(new FormData().getBoundary(), new FormData().getBoundary());
} finally { Math.random = random; }
const form = new FormData();
form.append('nome"\r\nX-Audit: iniettato', Buffer.from('Dato sintetico'), {filename:'file"\r\nX-Audit: iniettato.txt'});
const payload = form.getBuffer().toString('utf8');
assert.equal(payload.includes('\r\nX-Audit:'), false);
assert.equal(payload.includes('Dato sintetico'), true);
console.log('PASS: boundary indipendenti da Math.random e CRLF nei parametri multipart neutralizzati; nessuna richiesta di rete.');

# Sito pubblico Tango Soft Pro

Cinque pagine HTML nel medesimo progetto Laravel, servite dal controller pubblico. Fonti canoniche: `web/assets/styles.css` e `web/assets/app.js`; le copie in `public/site-assets` devono restare byte-identiche perché possono essere servite direttamente. Le altre risorse locali sono esposte dal percorso pubblico già esistente.

`catalogo-commerciale.json` è la fonte unica di piani, tariffe e contenuti Max Full. Gli HTML usano segnaposto `{{catalog.…}}`, sostituiti e sottoposti a escaping dal controller; il form legge lo stesso JSON per il riepilogo e invia soltanto codici piano/servizio. Nessun pagamento.

Immagini fotografiche AI illustrative separate dalle cinque schermate autentiche del prodotto con dati sintetici. Nessun dato reale, tracciamento o font esterno aggiunto.

Verifiche Laravel/browser esclusivamente nell’infrastruttura isolata ufficiale. Rapporto corrente: `docs/SITO-TANGO-SOFT-PRO-2026-10-25.md`; gli esiti delle proposte precedenti non qualificano il nuovo candidato. Anteprima loopback coordinata dal principale: `http://127.0.0.1:63351/`, modulo esplicitamente dimostrativo senza database/email. Nessuna pubblicazione automatica.

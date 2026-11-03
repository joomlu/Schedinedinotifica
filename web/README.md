# Sito pubblico Tango Soft Pro

Nove pagine HTML nel medesimo progetto Laravel, servite dal controller pubblico. Fonti canoniche: `web/assets/styles.css` e `web/assets/app.js`; le copie in `public/site-assets` devono restare byte-identiche perché possono essere servite direttamente. Le altre risorse locali sono esposte dal percorso pubblico già esistente.

`catalogo-commerciale.json` è la fonte unica di piani, tariffe e contenuti Max Full. Gli HTML usano segnaposto `{{catalog.…}}`, sostituiti e sottoposti a escaping dal controller; il form legge lo stesso JSON per il riepilogo e invia soltanto codici piano/servizio. Nessun pagamento.

Immagini fotografiche AI illustrative separate dalle cinque schermate autentiche del prodotto con dati sintetici. Nessun dato reale, tracciamento o font esterno aggiunto.

Verifiche Laravel/browser esclusivamente nell’infrastruttura isolata ufficiale. Rapporto corrente: `docs/SITO-TANGO-SOFT-PRO-2026-10-25.md`; gli esiti delle proposte precedenti non qualificano il nuovo candidato. Anteprima loopback coordinata dal principale: `http://127.0.0.1:63351/`, modulo esplicitamente dimostrativo senza database/email. Nessuna pubblicazione automatica.

Il modulo pubblico usa il token CSRF della sessione sia nel POST nativo sia nel JSON. Le richieste a percorsi assenti mostrano una pagina404 utile; le rotte gestionali esistenti mantengono le protezioni di autenticazione.

Note legali, privacy e cookie usano i dati societari forniti dal titolare il25ottobre2026. Coordinate bancarie e informazioni del bar non fanno parte del sito commerciale.

## Allineamento grafico del 1 novembre 2026

Il sito segue la UI Tango di Schedine/Gestione Prezzi: bordeaux `#78170c`, verde `#24635d`, blu `#305685`, superfici bianche, bordi discreti e icone sottili. La struttura editoriale, le immagini e i contenuti commerciali restano invariati. La navigazione selezionata resta blu anche al passaggio del puntatore. Non importare il CSS del gestionale nel sito: lo stile commerciale è circoscritto al foglio canonico, copiato byte per byte in `public/site-assets/styles.css`. Nessun font remoto aggiunto.

Il manuale generale e la stampa condividono `resources/views/help/partials/navigazione-ui.blade.php` per le istruzioni di navigazione. Rapporto corrente: `docs/SITO-ALLINEATO-UI-2026-11-01.md`. Verifica ordinaria locale delle nove pagine e menu; nessuna qualifica funzionale VM o pubblicazione implicita.

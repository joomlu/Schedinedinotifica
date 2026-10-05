# Chiusura Geo → Comuni e logo — 5 ottobre 2026

## Risultato

GEO COMUNI E LOGO COMPLETATO — TEST ISOLATI PASSATI — PRONTO PER PUSH

Il lavoro resta locale. Nessun push, deploy, accesso a SPanel o intervento sulla produzione.

## Causa del 403 e fix

`App\Http\Middleware\Ruolo::handle()` riceveva un solo parametro `$ruoli`. Laravel separa `ruolo:super_admin,admin` in due argomenti; il secondo veniva ignorato, e `in_array()` escludeva Admin. Riproduzione della pipeline originale: Super Admin 200, Admin 403. Firma variadica corretta, mantenendo il confronto stretto e le policy singole.

Le diagnostiche preesistenti sono state rimosse: marker `GEO_*` e condizioni che saltavano i middleware fuori dalla sola GET dei loghi. `ImpostaStrutturaCorrente` e `VerificaServizioStruttura` sono tornati identici all'HEAD iniziale. Nessun middleware rimosso dalle route.

`GeoComuneLogoController` ora preserva il nuovo file quando sostituisce un logo sullo stesso percorso; usa l'estensione ricavata dal contenuto; limita la cancellazione alla directory dei loghi; annulla anche il campo legacy alla rimozione. La view trasmette `q` nelle action per conservare la ricerca dopo upload/rimozione. Nessun redesign.

## Architettura e caso reale studiato in sola lettura

Sorgente: `GeoComune`, tabella `geo_comuni`, relazione `provincia`. Campo principale `logo_citta`, fallback legacy `logo`; nessuna nuova tabella o copia dei master data applicativi.

Caso reale trovato: Bellaria-Igea Marina, ID 7500, ISTAT 099001. `logo = null`, `logo_citta = storage/geo_comuni/logo/7500-bellaria-igea-marina.jpg`.

Disk `public`, root `storage/app/public`, collegamento `public/storage`. File JPEG esistente in `storage/app/public/geo_comuni/logo/7500-bellaria-igea-marina.jpg`. URL: `https://schedinedinotifica.test/storage/geo_comuni/logo/7500-bellaria-igea-marina.jpg`, verificato con HEAD: HTTP 200, `image/jpeg`. Lettura in `GeoComuneLogoController::index()`, rendering con `asset()` nella view `resources/views/geo/comuni-logo.blade.php`.

File reale conservato; SHA-256 iniziale e finale identico: `2841c5024b1a428bcde35667d6e6019adebbe9cbf325fae1cd5d3601dbd19eca`. Il record reale non è stato modificato o copiato nei test. Il caso di un logo già associato è stato riprodotto nel database isolato con un Comune e un PNG sintetici; il browser ne ha verificato caricamento e dimensioni reali dell'immagine. Non si dichiara una navigazione autenticata sul server reale: tutte le prove funzionali sono isolate.

## Infrastruttura ed evidenze di isolamento

Il vecchio guardrail era incondizionato perché non esisteva infrastruttura accettata. Ora continua a rifiutare runtime esterni e riconosce soltanto le risorse attestate dell'avviatore. Il percorso di accettazione verifica processi, parentela, challenge del supervisore, ambiente, percorsi e identità SQL effettiva prima del bootstrap.

Ultima esecuzione integrata:

- directory: `/private/tmp/schedine-test-kcdkpsxd`;
- database: `test_geo_c1fd9670f8a7f4e5af9d4f0ee400a6dc`;
- MySQL 8.0.36 nuovo, PID 97423, porta 56601;
- server PHP dedicato, PID 97428, `http://127.0.0.1:56602`;
- credenziali casuali, directory MySQL nuova, nessun `.env`, dump, storage, cache o dato reale copiato;
- identità verificate via `@@datadir`, `@@server_uuid`, `@@port`, `DATABASE()` e risposta HTTP del processo attestato;
- 23 configurazioni avverse rifiutate prima delle migrazioni, riconnessione controllata;
- proxy limitato all'origine di test, service worker bloccati; URL/redirect esterni bloccati, origine vietata di controllo con zero richieste;
- tutte le migrazioni originali eseguite da zero senza modifica o riparazione;
- processi terminati e directory temporanea rimossa a fine esecuzione.

Per ripetere le prove usare i comandi di `docs/TESTING_ISOLATION.md`. La protezione rimane attiva anche eseguendo direttamente gli entrypoint di PHPUnit o gli spec browser fuori dall'avviatore.

## Test e permessi

- Guardrail fuori dall'ambiente: 5 test PASS.
- Verifica isolata della pipeline dei ruoli: 37 casi PASS.
- PHPUnit: 32 test, 193 asserzioni, PASS; suite GeoComuneLogoTest, TopbarRenderingTest e StrutturaAuthorizationTest.
- Playwright: 5 test PASS.
- Super Admin: login, menu, GET 200, pagina renderizzata, logo visibile e ricerca PASS.
- Admin: login, menu, GET 200, pagina renderizzata, logo visibile e ricerca PASS.
- Altri ruoli: GET/POST/DELETE vietati e menu assente PASS; proprietario verificato anche nel browser.
- Logo preesistente sintetico: associazione, file, URL e immagine decodificata nel browser PASS.
- Ricerca per nome/ISTAT, nessun risultato e paginazione PASS.
- Upload, sostituzione sullo stesso percorso, rimozione e fallback legacy PASS, soltanto su fixture.
- File non immagine, estensione PHP, dimensione eccessiva e CSRF errato rifiutati PASS.
- Cancellazione fuori directory e path traversal non eliminano file estranei PASS.
- Nome, ID, codice ISTAT, provincia, coordinate e numero dei Comuni invariati dopo upload/sostituzione/rimozione PASS.
- Lint PHP: 18 file PASS; sintassi Python PASS.
- Blade: view compilata e renderizzata nelle prove funzionali PASS.
- Build asset e verifica hash GEO immutabile PASS nel checkout temporaneo.

## Regressioni e Git

GEO core, modello GeoComune, import/resolver geografici, Questura, ISTAT, Clienti, Componenti, Schedina, Calendario, Notifiche, Presenze, Tassa di soggiorno, Dashboard e altre configurazioni non hanno modifiche applicative. Sono state eseguite le regressioni pertinenti di rendering e autorizzazione; non si dichiara un'esecuzione dell'intera suite storica.

HEAD iniziale: `48075a0a0f684ae51c5ff65c06070eb1a6f13707`.

Commit infrastruttura: `36caec7ca9eee528792a5d5823b68204ac9d5e54`, `test: aggiunge runtime locale isolato con guardrail verificati`.

Il commit dei loghi è separato e comprende soltanto controller, middleware dei ruoli, view, fixture/test specifici e questo report. SHA finale indicato nel messaggio di chiusura della chat.

Staging selettivo; file untracked preesistenti fuori scope preservati. Nessun dato reale modificato. Push NO; deploy NO; SPanel NO.

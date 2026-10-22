# Isolamento dei test — VM ufficiale

## Stato e avvio

PHPUnit e Playwright restano bloccati con `TEST_ISOLATION_REQUIRED` fuori dal runtime creato da `tests/Isolation/run.py`. Nessun flag, nome di database, URL o file marker sblocca i test.

Il runtime autorizzato è esclusivamente la VM Lima `schedine-sviluppo-interno`, Rocky Linux, con avviatore attestato e namespace previsto dal Maestro. Gli esempi seguenti descrivono selezioni da passare all’avviatore ufficiale **dentro la VM**, non comandi da eseguire sul Mac o contro Herd. Si veda [il protocollo Rocky](ROCKY_PROCESS_ISOLATION.md) e il [Maestro](maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md), che prevalgono sulle descrizioni storiche.

Dal 22 ottobre 2026: PHP CLI e binario FPM **8.3.36**, MariaDB **11.8.9**, Node **20.18.2** del runtime autorizzato. Il precedente riferimento Mac/MySQL 8.0.36 descrive prove storiche e non autorizza quel percorso. Nuove istanze MariaDB effimere, rete confinata, fixture sintetiche, nessun servizio o database operativo riutilizzato. PHP-FPM installato nella VM resta inattivo; i browser attraversano il server PHP dedicato dell’avviatore.

Esempi di selezione, da comporre nel comando VM ufficiale con namespace e utente `collaudo`:

```text
--phpunit tests/Feature/GeoComuneLogoTest.php
--phpunit tests/Feature/GeoComuneLogoTest.php tests/Feature/TopbarRenderingTest.php tests/Feature/StrutturaAuthorizationTest.php --fixtures tests/Isolation/seed-geo.php --playwright tests/Feature/geo-logo.playwright.spec.js
```

PHP 8.3.36 allineato alla produzione senza aggiornamenti estranei: nove soli RPM Rocky esistenti, configurazioni e moduli invariati, copie dei nove RPM precedenti e nuovi con firme verificate, archivio configurazioni e sigilli custoditi nella VM. Il ripristino richiede il confronto dello stato corrente, stop dei soli servizi interessati già attivi, reinstallazione controllata dei nove RPM precedenti e verifica dei sigilli; non eseguirlo come pulizia automatica. Differenze ARM/x86, CLI/FPM, moduli e limiti restano esplicite: versione uguale non significa configurazione identica. [Consuntivo e confronto](PENDENTI-TECNICI-WEB-CHECKIN-2026-10-22.md).

Senza suite selezionate l'avviatore verifica soltanto l'infrastruttura, il build e le migrazioni. Gli spec browser devono essere selezionati esplicitamente; le fixture sono uno script esplicito, senza seeder impliciti.

L'avviatore stampa identità, PID e porte, interrompe al primo errore e pulisce le risorse in `finally`, anche dopo un'interruzione ordinaria. Nessun ripiego su database, server, socket o `.env` esistenti. Un errore delle migrazioni ferma l'esecuzione: non viene riparato lo schema automaticamente.

## Risorse e attestazione

Ogni esecuzione crea una directory privata temporanea nella VM (`/tmp/schedine-test-*`) con:

- copia del codice versionato e dei file di test pertinenti, senza `.env*`, dati di storage, cache di sviluppo, immagini pubbliche o upload reali;
- copie separate di vendor e node_modules; asset compilati nella copia temporanea;
- nuova directory MySQL inizializzata da zero con `--no-defaults`, endpoint loopback e credenziali casuali; il solo utente applicativo ha privilegi sul database effimero;
- server PHP HTTP dedicato, storage, upload, viste, log, sessioni e temporanei propri;
- supervisore con socket Unix privato e attestazione tramite challenge;
- proxy HTTP limitato all'origine temporanea e un'origine locale vietata per le prove di deviazione.

`TestingEnvironment` verifica **prima di Composer e Laravel** checkout, assenza degli ambienti e della cache, ambiente esatto del launcher, supervisore vivo e risposta al challenge. Controlla i comandi e la parentela dei processi MySQL/HTTP. Una connessione PDO all'endpoint attestato verifica realmente `@@datadir`, `@@server_uuid`, `@@port` e `DATABASE()`: non è una semplice convenzione sul nome.

`verify.php` verifica i rifiuti prima delle migrazioni: host/porta/database differenti, URL, socket, read/write, percorsi esterni, server Herd, token/identità contraffatti e connessioni secondarie. Verifica anche la riconnessione e l'identità HTTP.

## Protezione dopo il bootstrap

`configureApplication()` controlla il percorso dell'ambiente prima di Dotenv e imposta configurazioni locali prima dei provider. La factory DB protetta rifiuta connessioni secondarie, dinamiche non previste, URL, read/write e configurazioni alterate. Ogni nuova connessione attesta ancora il runtime e l'identità MySQL. Cache in memoria, mail nel trasporto `array`, filesystem temporanei, nessun Redis o disk remoto; il client HTTP Laravel blocca richieste non simulate.

PHPUnit attraversa il vero kernel HTTP. Il supporto di test simula il contesto web solo durante ciascuna richiesta e aggiunge un token CSRF valido alle richieste sintetiche che non ne specificano uno. Il middleware CSRF resta attivo; token esplicitamente errati restano errati. Il server browser usa sessioni su file e CSRF completo; PHPUnit usa sessioni in memoria. I comandi restano in contesto console.

Playwright attesta DB e identità HTTP prima di caricare il browser. Service worker bloccati; proxy obbligatorio con esclusione del bypass loopback; richieste e redirect verso altre origini rifiutati anche a livello di trasporto. Le prove includono redirect diretto e catena di redirect, HTTP/HTTPS e un'origine vietata locale che deve ricevere **zero richieste**.

## Fixture e ambito delle suite

Le migrazioni sono quelle originali, senza dump o dati reali. Le fixture Geo creano nomi e codici sintetici, utenti temporanei e un PNG sintetico valido. Le scritture sui loghi si svolgono soltanto nel database e storage temporanei. Il caso reale può essere studiato separatamente in sola lettura; non viene copiato nelle fixture.

La factory utenti include `avatar`, obbligatorio nello schema. Le fixture di struttura hanno nomi distinti e rileggono i valori persistiti prima dei confronti. La regressione del menu con una sola struttura verifica la lista del composer, perché quella UX non espone un selettore con opzioni.

Sono supportate e verificate le suite indicate sopra. Le vecchie suite che presumono dati preesistenti, seeder o utenti reali non sono automaticamente dichiarate compatibili: richiedono proprie fixture sintetiche. L'autorizzazione delle strutture mantiene le regole applicative esistenti, inclusa l'eccezione operativa legacy dell'Admin, senza ampliarla al selettore o al CRUD.

## Verifica Gruppi del 13 ottobre 2026

Il parametro `--include` può includere esplicitamente anche i soli `database/seeders/GruppiSeeder.php` e `database/seeders/GruppoSeeder.php`. Restano esclusi tutti i seed impliciti e gli altri seed; non è un avvio automatico del DatabaseSeeder. Consente di provare realmente l’idempotenza delle personalizzazioni nel DB effimero. Protezioni originali intatte,15 guardie PASS. La selezione Gruppi e i relativi limiti sono nel [consuntivo](VERIFICA-GRUPPI-2026-10-13.md).

## Verifica Titoli del 14 ottobre 2026

Inclusione facoltativa esplicita anche di `database/seeders/TitoloSeeder.php` e `resources/views/titolo/modal.blade.php`, senza seed impliciti o ampliamento delle origini/rete/database ammessi. Permette di collaudare il seeder effettivo e la nuova modale prima del commit.15guardie e23override PASS; selezione e diagnostici nel [consuntivo Titoli](CORREZIONE-TITOLI-2026-10-14.md). Il template dell’avviatore VM segue la stessa allowlist.

## Verifica cataloghi Questura del 14 ottobre 2026

L'allowlist cumulativa consente ora l'inclusione esplicita anche di `database/seeders/TipoAlloggiatoSeeder.php` e `database/seeders/TipoDocumentoSeeder.php`. Nessun DatabaseSeeder o seed implicito; nessun ampliamento delle origini, della rete o dei database ammessi. La fixture `seed-cataloghi-questura.php` resta esclusivamente nel checkout effimero. Avviatore versionato e template operativo VM concordano sulle due inclusioni; le voci autorizzate precedentemente restano preservate. Prove e selezione esatta nel [consuntivo](VERIFICA-CATALOGHI-QUESTURA-2026-10-14.md): 76 test/1.005 asserzioni, due prove Chromium,15 guardie e23 rifiuti di override PASS.

## Tre formulari Configurazioni — 14 ottobre 2026

Le inclusioni esplicite ammettono anche `database/seeders/RilasciatoDaSeeder.php` e `database/seeders/TipoViaSeeder.php`, per verificare l'idempotenza dei cataloghi privati senza staging dei nuovi sorgenti. Nessun seeder implicito, percorso generico o allentamento dei controlli di runtime. La selezione congiunta e il recupero della migrazione sono descritti nel [consuntivo dei tre formulari](VERIFICA-TRE-CONFIGURAZIONI-2026-10-14.md).

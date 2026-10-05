# Isolamento dei test locali

## Stato e avvio

PHPUnit e Playwright restano bloccati con `TEST_ISOLATION_REQUIRED` fuori dal runtime creato da `tests/Isolation/run.py`. Nessun flag, nome di database, URL o file marker sblocca i test.

Prerequisiti: PHP con PDO MySQL, Python 3, Node/npm, dipendenze già installate, binari `mysqld` e `mysql`. Per Playwright serve Chromium già installato. Su questa macchina viene usato MySQL 8.0.36, lo stesso motore supportato dalle migrazioni, in una **nuova istanza**, senza riutilizzare il servizio Herd. Non vengono installati servizi o modificati quelli esistenti.

```sh
# Verifica il rifiuto fuori dal runtime, senza database o browser.
python3 -B tests/Isolation/test_guards.py

# Fixture e test specifici dei loghi.
python3 -B tests/Isolation/run.py --phpunit tests/Feature/GeoComuneLogoTest.php

# Loghi, rendering, regressioni di autorizzazione e browser.
python3 -B tests/Isolation/run.py \
  --phpunit tests/Feature/GeoComuneLogoTest.php \
  tests/Feature/TopbarRenderingTest.php \
  tests/Feature/StrutturaAuthorizationTest.php \
  --fixtures tests/Isolation/seed-geo.php \
  --playwright tests/Feature/geo-logo.playwright.spec.js
```

Senza suite selezionate l'avviatore verifica soltanto l'infrastruttura, il build e le migrazioni. Gli spec browser devono essere selezionati esplicitamente; le fixture sono uno script esplicito, senza seeder impliciti.

L'avviatore stampa identità, PID e porte, interrompe al primo errore e pulisce le risorse in `finally`, anche dopo un'interruzione ordinaria. Nessun ripiego su database, server, socket o `.env` esistenti. Un errore delle migrazioni ferma l'esecuzione: non viene riparato lo schema automaticamente.

## Risorse e attestazione

Ogni esecuzione crea una directory privata `/private/tmp/schedine-test-*` con:

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

# Web Check-in — testi leggibili e palette completa, 20 ottobre 2026

## Stato: pubblicato e verificato online — `5e01d7b1153daac8de96a6281cd6203ff92c034d`

Le sezioni iniziali conservano la proposta e le verifiche storiche; gli esiti attuali sono nel consuntivo conclusivo e nella ricevuta.

Base GitHub: a24bb3c8abed427c59e902b9b97e6aca87b36bfb.
Ramo: codex/web-checkin-testi-palette-2026-10-20.
BASELINE IN AUDIT invariata.

## Riscontri e intervento
Verificata direttamente l'interfaccia online: le sezioni ospite sono presenti, ma l'editor dei modelli mostra ancora la sintassi tecnica. Confermati colori fissi per sfondi e bordi nella vista pubblica e nell'email.
La proposta mostra «Nome cliente», «Nome struttura» e gli altri dati automatici in linguaggio leggibile. L'evento formdata riconverte soltanto i sei campi di messaggio nel formato canonico, sia per anteprima asincrona sia per salvataggio/duplicazione modello e condivisione. I modelli rimangono riutilizzabili; nessuna sostituzione con dati nominativi salvati nei modelli.
Aggiunta palette derivata dal colore esistente: sfondo, tono tenue, bordo e inchiostro scuro. Applicata a email e modulo pubblico, intestazioni, link, controlli e calendario. Il colore primario e la funzione di contrasto esistenti sono conservati. La palette viene calcolata dal colore dello snapshot: nessuna nuova chiave nell'identità, nessuna invalidazione generalizzata delle impronte.

## Prova storica precedente al completamento della PR
Richiesta 134, destinatario già configurato nella casella controllata dell'utente. Verificati logo, referente, soggiorno, destinatario e anteprima completa. Personalizzati oggetto, saluto e firma per questa sola comunicazione. Un solo invio, eseguito tramite la conferma del destinatario nell'interfaccia.
Alle 15:01:01 Europe/Rome l'applicazione ha attestato: «Inviato: accettato dal trasporto email; consegna non confermata».
Ricezione nella casella Gmail confermata esplicitamente dall'utente nel messaggio successivo; non ispezionata autonomamente la casella. Questa prova usa l'applicativo già online, NON la nuova palette proposta. Nessun secondo invio automatico. Nessun dato degli ospiti, modello riutilizzabile o colore della struttura modificato; creata la sola comunicazione richiesta con il suo tentativo.

## Verifiche iniziali e limiti storici
- Revisione completa dei quattro diff applicativi.
- node --check /tmp/web-checkin-editor-check.js: PASS di sintassi sullo script editor estratto, con soli valori Blade sostituiti.
- node --check review/tests/Feature/web-checkin-html.playwright.spec.js: PASS di sintassi.
- git diff --no-index --check before review: nessun errore whitespace; exit 1 indica differenze nel confronto no-index.
- Spec HTML esteso a segnaposto leggibili, serializzazione canonica di FormData, sfondi e bordi di email e modulo per colori chiari/scuri.
- PHP, rendering Blade, PHPUnit, Playwright applicativo, test negativi su due tenant, build e C5 NON ESEGUITI: in questa sessione mancano PHP, limactl e accesso al runtime ufficiale. Nessun aggiramento delle guardie e nessun test su DB reale.
- Rischio MEDIO: controllare round-trip dei modelli, modifica/ripresa, validazione delle lunghezze, FormData su browser supportati e contrasto responsive. I segnaposto con caporali sono una sintassi riservata dell'editor; la loro digitazione inserisce un dato automatico.
- Prima del rilascio eseguire nella VM ufficiale le regressioni Web Check-in HTML/comunicazioni/circuito con fixture sintetiche; controllare modello salvato su una seconda richiesta, payload anteprima e salvataggio tradizionale, contrasto e 1440/768/390. C5 solo dopo esito positivo e secondo il protocollo vigente.

## Tenant e sicurezza nella proposta iniziale
Nessuna modifica ad autorizzazioni, route, scope tenant, CSRF, token, SMTP, schema o salvataggio ospiti. Le sostituzioni sono testuali; nessuna interpretazione HTML/PHP introdotta. Le prove dinamiche di regressione restano pendenti.

CODICE MODIFICATO: SI. TEST MODIFICATI: SI. DOCUMENTAZIONE MODIFICATA: SI.
CONFIGURAZIONE MODIFICATA: NO. SCHEMA MODIFICATO: NO. DATI: SI, sola comunicazione/invio richiesti.
TEST FUNZIONALI DELLA PROPOSTA ESEGUITI: NO. CONTROLLI SINTATTICI: SI.
DEPLOY: NO. TRASMISSIONI ESTERNE: SI, un solo invio reale richiesto.

## Sigillo del collaudo isolato precedente al rilascio

R12 PHP e dodici browser storici; R13 nove browser dedicati sullo stesso applicativo: **351 test PHP / 3546 asserzioni e 21 scenari Playwright PASS** (6 circuito, 6 HTML, 9 GEO/editor/palette). Incluse le sei regressioni originarie, clienti, Schedina, Componenti, comunicazioni, isolamento tra strutture, Questura e ISTAT. 17 guardie, 23 tentativi di override rifiutati, build Vite/GEO e rendering Blade PASS. Larghezze 1440/768/390; colori #123456 e #ffffee; parziale, riapertura, conferma, errori visibili, modelli riutilizzati su una seconda richiesta e FormData canonico. Rimane una sola deprecazione preesistente nei metadati di IstatConformitaTest. Nessun test Laravel/PHPUnit/Playwright sul Mac; nessuna email reale. [Sigillo e schermate](evidenze/web-testi-palette-2026-10-20/verifica.json).

Corretti durante il collaudo: sigillo GEO omesso dalla proposta; direttiva Blade che inglobava il blocco successivo e causava completedGuests non definito; associazione errore al controllo italiano nascosto invece del campo estero; risposte asincrone italiane non più pertinenti; cittadinanza manuale sovrascritta al cambio nazione; completezza/coerenza residenza dei Componenti solo alla conferma completa; prima voce del tipo via assegnata involontariamente a un estero senza indirizzo; ombre verdi e palette di calendario/ricevute. Validazioni italiane e guardie mantenute. Nessun contratto di esportazione, schema, configurazione o trasporto modificato.

R1 STOP per sigillo GEO; R2 18 errori Blade. R3 PHP349/3522 PASS, browser interrotto dopo selettore Italia non corrispondente all’etichetta Italia (IT). R4 3 errori su separazione parziale/completo e fixture complete prive di residenza, corretti mantenendo le asserzioni originarie. R5 PHP350/3536 PASS, browser interrotto dopo clic sul campo data originale nascosto. R6 difetto reale errore estero nascosto; R7 aspettativa errata sulla riapertura prima della conversione; R8 avviso di selezione struttura da chiudere; R9 componente assente nella fixture per scope tenant; R10 richiesta di seconda anteprima riutilizzata tra prove; adattatori/fixture corretti. R11 nove dedicati PASS, R12: PHP e 19 browser PASS, un NONPASS tablet per calendario rimasto aperto dopo jumpToDate e Escape, ultimo scenario non eseguito. Corretto soltanto l’adattatore con normale clic esterno e verifica di chiusura, senza force o timeout diversi; R13 tutti i nove dedicati PASS. Applicativo identico fra R12 e R13, unica differenza nello spec dedicato. Nessun NONPASS intermedio promosso a PASS; log conservati.

Capacità VM recuperata con FIDEDUPERANGE su sole copie temporanee chiuse: contenuti, inode e metadati verificati prima/dopo, nessun file cancellato e nessuna esecuzione dei dump SQL. Conservati anche tutti i 192 dump/ridump verificati; i duplicati condividono soltanto blocchi XFS. Nessun resize/arresto VM, daily, dato operativo, env, upload, backup, cifrato o chiave alterato. Ultima misura prima del C5 da riconfermare. Riserve e budget invariati. Il minimo storico PR3 di 1243488256 byte evidenzia un consumo campionato di 1794150400 byte: il nuovo gate terrà conto anche di questo picco osservato, oltre al recupero completo, prima del RUN.

Integrazione main, C5, QA online, rimozione fixture e monitor finali autorizzati dall’utente e ancora pendenti al momento del sigillo. Ricezione dell’email della richiesta 134 già confermata dall’utente; nessun nuovo invio da eseguire. **BASELINE IN AUDIT** invariata.

## Integrazione, pubblicazione e verifica effettive

PR #4 integrata in main dopo le correzioni `797abf7cb36fd25910f51c2b963ef8110fa342b4`. **SHA integrato e realmente online: `5e01d7b1153daac8de96a6281cd6203ff92c034d`**. Verificati commit qualificato, integrazione GitHub e HEAD produttivo pulito; 111 sorgenti della task e 168 sorgenti/protetti conformi. [Ricevuta](evidenze/web-testi-palette-2026-10-20/rilascio.json), [QA](evidenze/web-testi-palette-2026-10-20/qa-online.json), [comandi](evidenze/web-testi-palette-2026-10-20/comandi-collaudo.txt).

C5 esistente, 68 file conformi salvo parametri SHA/tree: nuovo PREPARE, nuovo T0/S0 e recuperi indipendenti PASS. Unico RUN di questa task PASS: S0 131.975 s, RUN 215.217 s; 159 migrazioni / 66 tabelle, nessuna DDL. Tutti i sei nuovi cifrati PREPARE/S0 su Extra autenticati e decifrati integralmente, chiave separata. 55 campioni di quiete PASS; guardiano concluso/disarmato/inattivo/disabilitato. DB byte-identico a S0 prima della QA e 92 persistenti conservati. Gate dopo PREPARE rafforzato al massimo fra recupero+cifrati completi e consumo storico campionato aumentato per crescita, con 32 MiB e riserva 1 GiB: stima 2907930624 byte contro 5464162304 liberi prima RUN. Nuovo campionamento: minimo 3705212928 byte / 2528 campioni, sempre oltre 1 GiB. Nessun limite ridotto.

QA online eseguita dalla VM: sei circuito, sei HTML e nove GEO/editor/palette, tutte a 1440/768/390 PASS; colori chiaro/scuro, calendario, ricevute, errori visibili, parziale/riapertura/conferma, Schedina e Componenti, modelli duplicati/aggiornati e riutilizzati senza dati della prima richiesta, FormData canonico. Anche clienti/Bozze/ripresa, isolamento A/B, 24 navigazioni responsive e accessi scaduti/revocati/rigenerati PASS. Nessun POST invio, nessuna email o modifica a ospiti reali; richiesta 134 non inviata nuovamente. Anteprima HTML effettiva verificata online, trasporto e MIME provati soltanto nel runtime sintetico. Primo adattatore online dedicato: tre GEO PASS, poi NONPASS per attesa networkidle sul primo login editor, prima di modificare modelli. Schermata con elenco pronto e zero errori Laravel/FPM dall’intero T0; causa del traffico residuo non attribuita. Conservati log, risultato e schermata; sostituita soltanto l’attesa generica con load, font, render e tabella visibile. Ripetuti tutti i nove casi: PASS sul medesimo applicativo. Adeguata soltanto la selezione alle maiuscole effettive del catalogo e attese le animazioni nelle schermate, senza force o riduzione delle asserzioni.

Fixture, account e loghi sintetici rimossi: HMAC di tutte le righe preesistenti delle 66 tabelle e 92 persistenti/env/key/upload/loghi PASS; nessun restore o reset AUTO_INCREMENT, sola eccezione mtime directory dei loghi. Monitor conclusivo dopo pulizia: server 918.376 s / 31 campioni / 279 sonde e Mac 921.526 s / 31 campioni / 93 richieste HTTPS con DNS/TLS ordinari PASS. Zero errori Laravel/FPM dall’intero T0, senza azzerare i log.

Soglie Mac 8 GiB / Extra 40 GiB, riserva VM 1 GiB e budget 1800/600/1379/421 invariati. Daily 945ef553, disco 36 GiB / XFS 37290283008, SELinux Enforcing, configurazioni/credenziali/backup e checkout originario preservati. Capacità recuperata soltanto condividendo blocchi kernel di copie temporanee chiuse e verificate: nessun file cancellato, tutti i dump/ridump conservati e nessun SQL eseguito; fase vendor/node_modules senza guadagno misurato distinta dalle fasi con guadagno reale. Presidio caffeinate temporaneo terminato e verificato, nessuna impostazione permanente cambiata. Il commit documentale successivo è distinto dallo SHA applicativo online.

Nessun pendente reale nello scope verificato. Rimangono una deprecazione PHPUnit preesistente, differenza PHP VM 8.3.33 / produzione 8.3.35 e audit generale/DNS/DKIM storici separati. **BASELINE IN AUDIT** invariata.

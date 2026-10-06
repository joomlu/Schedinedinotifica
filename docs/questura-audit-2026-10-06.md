# Questura / Alloggiati Web — audit e correzioni del 6 ottobre 2026

## Fix visual/UX Questura prima del pre-commit — 07/10/2026

**FIX UX QUESTURA VERIFICATO — PRONTO PER REVISIONE PRE-COMMIT**, nel perimetro dei due difetti richiesti. Baseline funzionale **PRONTO PER PROVA REALE CONTROLLATA** preservata, progetto **BASELINE IN AUDIT**. Nessun nuovo P0/P1 emerso; nessun commit o preparazione dello staging.

### Cause riprodotte e correzioni

**Credenziali — PASS.** Lo `<small>` di stato Password era dentro l’`input-group`, fra input e pulsante occhio; Bootstrap lo trattava come elemento della riga. Spostato immediatamente dopo il gruppo, nella medesima colonna, con `d-block mt-1`, come WSKEY. Nessun nuovo stato username inventato. Stato backend preesistente preservato (`getRawOriginal`/presenza), campi segreti vuoti e funzione mostra/nascondi invariati. Nessun nuovo CSS/design system.

**Pagina Questura — PASS sul caso controllato riprodotto.** Il testo della schermata osservata proviene da `Handler::render`: risposta `text/plain` con status 500 sugli errori inattesi Questura, anche in debug. Non è un problema CSS. Trigger concreto riprodotto su fixture: ciphertext Password/WSKEY non decifrabile → cast Eloquent `encrypted` → `DecryptException` durante `QuesturaWebService::credentialsStatus` chiamato da `index`; la Blade inoltre leggeva nuovamente le proprietà cifrate. La provenienza della risposta è accertata nel codice; il trigger della singola osservazione manuale su dati operativi non è stato verificato, poiché nessun DB/credenziale/log reale è stato letto. Il fix riguarda questa condizione controllata, senza assimilare ogni 500 a un errore previsto.

Il solo `index` intercetta **esclusivamente DecryptException durante la lettura dello stato credenziali**, la registra tramite `report()` e reporting Q3 invariato, e rende la normale `questura.index` con **HTTP409**, alert fisso e configurazione non disponibile. Nessun redirect, riscrittura credenziali, risposta di successo o eccezione generica ignorata. Username/Password/WSKEY non vengono decifrati di nuovo dalla Blade: per Password/WSKEY usa lo stato già preparato; se bloccato mostra “Da verificare”. Layout/navigazione/intestazione restano normali, storico/ricevute e download autorizzati accessibili; Test/Send disabilitati in UI. TXT manuale e archiviazione riferimenti mantengono il flusso precedente. Le protezioni backend non dipendono dai pulsanti disabilitati.

Errori tecnici inattesi restano al Handler Q3 con status 500/5xx e diagnostica minimizzata; non convertiti in 409 o successo. **Handler, servizi, modelli, migrazioni, algoritmi Q1/Q2 e test preesistenti byte-identici al preflight**. Nessuna modifica dei retry, reservation, Test→Send, retention o autorizzazioni. Nessun payload/TXT/snapshot/base64, credenziale, SQL o stack aggiunto all’alert. Test in debug acceso/spento, su entrambe le credenziali, verificano risposta e log sintetico; Q3 regressione completa PASS.

### Prove e verifica visuale

Prima del fix: **6 test/19 asserzioni, 4 failure attese** (2 layout, 2 status409 atteso contro500); auth/CSRF/tenant ed errore tecnico inatteso già PASS. Runtime `/private/tmp/schedine-test-4uardoel`, DB `test_geo_352b136eefaa4d16fe08b92aecf5e9ad`, MySQL68370/57114, HTTP68375/57115, rimosso.

Mirati dopo il fix: **9 test/146 asserzioni PASS**, oltre a **4 Playwright PASS** a 1440/390 px; **14 SOAP/sicurezza PASS**. Runtime `/private/tmp/schedine-test-0a4t6nlm`, DB `test_geo_60d25f0e6fdb3ce4c99c1837f3645af3`, MySQL68785/57200, HTTP68790/57201, rimosso. DOM verifica stato fuori input-group, status configurata/non configurata e segreti non reidratati. HTTP verifica alert/layout 409/storico e download effettivo, configurazione immutata, zero tentativi/reservation, debug, auth/tenant/CSRF, errore tecnico 500 sicuro. Un test dimostra direttamente DecryptException sullo stato di una fixture non decifrabile.

Browser Chromium isolato: colonne desktop allineate; su mobile campi impilati e status sotto input; pulsante occhio allineato e toggle funzionante; nessun overflow orizzontale della pagina; alert nel layout e Test/Send disabilitati. Rafforzata poi la prova dei tab con attesa classe active e opacity1 e contenuto dello storico, evitando una fotografia durante la transizione. Quattro screenshot sintetici desktop/mobile esaminati, sotto `/private/tmp/questura-ux-{credenziali,errore}-{desktop,mobile}.png`, senza dati reali. Loghi/avatar assenti nel checkout sono una limitazione intenzionale del runtime che esclude immagini/upload reali, non oggetto del fix.

Regressione finale completa autorizzata Questura + Schedine + UX: **196 test / 2.920 asserzioni PASS**, incluse tutte le 187/2774 della baseline e i 9 nuovi casi, senza sommare suite sovrapposte. **Q1/Q2/Q3 PASS**, **4 Playwright PASS** con verifiche rafforzate. **5 guardrail, 14 SOAP/sicurezza, sintassi 45 PHP, Pint sui 2 nuovi PHP, sintassi JS, hash cataloghi/WSDL e `git diff --check` PASS**. Nessun ulteriore analizzatore statico obbligatorio configurato. Build/GEO immutabile e identità/riconnessione/23 rifiuti override prima delle migrazioni PASS; nessuna guardia indebolita.

Runtime finale `/private/tmp/schedine-test-wsqpof3c`, DB `test_geo_ab2a8344a832d8f09286e6b43fc6a493`, MySQL71038/58212, HTTP71047/58213. PHP 8.3.33 / PHPUnit 10.5.38 / MySQL 8.0.36. Tutte le risorse effimere fermate e rimosse. Il comando integrale è riportato sotto. Test/browser solo con `tests/Isolation/run.py`, mai su Herd/DB/server esistente; nessun SOAP reale. Non attestata la suite globale del gestionale, race multiprocesso o accettazione dell’ente.

### File della sola fase e Git

Modificati: `app/Http/Controllers/QuesturaExportController.php` (solo presentazione del caso in index), `resources/views/struttura/form.blade.php`, `resources/views/questura/index.blade.php`, questo rapporto e Maestro. Nuovi: `tests/Feature/QuesturaUxTest.php`, `tests/Feature/questura-ux.playwright.spec.js`, `tests/Isolation/questura-ux-fixtures.php`. **Esattamente 8 file**, altri 45 dei 50 preesistenti byte-identici agli hash iniziali. Nessun file preesistente eliminato; untracked e lavoro accumulato preservati. Fixture browser nuova riusa guardia/controlli SOAP esistenti e crea soltanto 2 utenti/strutture sintetici.

Git: `main`, HEAD e `origin/main` locale `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind locale 0/0, nessun fetch; staging vuoto, da 19 tracked / 31 untracked a 19/34 (53 file locali). 142 migrazioni storiche e 6 entry point/guardie invariati rispetto a HEAD.

Limiti operativi precedenti preservati: accettazione Alloggiati Web/rete/TLS/firma PDF non verificate, race multiprocesso non attestata, audit/bonifica legacy e **POSSIBILE BONIFICA LOG STORICI DA VALUTARE PRIMA DELLA PRODUZIONE**. Presenza reale nei log non verificata. Fix visuale provato su dati sintetici, senza accesso a configurazioni o log reali.

**CODICE/TEST/DOCUMENTAZIONE MODIFICATI SÌ; CONFIGURAZIONE/SCHEMA APPLICATIVO/DATI REALI NO; TEST ESEGUITI SÌ; COMMIT/PUSH/DEPLOY/SEND O TEST REALI NO.** Nessuna .env reale, DB/migration reali, cancellazione PMS o ispezione/modifica log operativi. **STOP finale: pronto per revisione pre-commit, nessuna operazione Git ulteriore eseguita.**

Comando integrale finale:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php \
 app/Console/Commands/QuesturaExpiredReceipts.php app/Console/Commands/QuesturaArchiveAudit.php \
 config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 database/migrations/2026_10_06_120000_add_questura_retention.php \
 database/migrations/2026_10_06_130000_add_questura_send_reservations.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json --fixtures tests/Isolation/questura-ux-fixtures.php \
 --phpunit tests/Feature/QuesturaRetentionTest.php tests/Feature/QuesturaCredentialEncryptionTest.php \
 tests/Feature/QuesturaBoundaryTest.php tests/Feature/QuesturaWsContractTest.php \
 tests/Feature/QuesturaLegacyMigrationTest.php tests/Feature/QuesturaIdempotencyTest.php tests/Feature/QuesturaTestSendSnapshotTest.php tests/Feature/QuesturaPayloadCoherenceTest.php \
 tests/Feature/StrutturaAuthorizationTest.php tests/Feature/SchedinaStoreTest.php \
 tests/Unit/PianoSyncComponentiTest.php tests/Unit/ComponentiImportP1Test.php tests/Feature/QuesturaFinalAuditTest.php tests/Feature/QuesturaLoggingPrivacyTest.php tests/Feature/QuesturaConclusiveAuditTest.php tests/Feature/QuesturaUxTest.php --playwright tests/Feature/questura-ux.playwright.spec.js
```

---

## Ripresa conclusiva dell’audit dopo P1-Q3 — 07/10/2026

**PRONTO PER PROVA REALE CONTROLLATA**, esclusivamente nel perimetro tecnico locale di questo audit. **BASELINE IN AUDIT** invariato. Nessun nuovo P0/P1 distinto emerso nelle verifiche residue e nella regressione. Nessuna certificazione generale del gestionale, produzione o accettazione dell’ente. Il gate significa soltanto candidabilità alla preparazione di una singola prova separatamente autorizzata; la prova non è stata eseguita.

### Ripresa e copertura residua

Maestro e rapporto letti integralmente. TXT/validazione, credenziali, contratto SOAP, archivi, retention e tenant erano già documentati; gli STOP successivi avevano sospeso la chiusura complessiva. Q1 identità/idempotenza, Q2 uguaglianza dei byte Test/Send e Q3 diagnostica sono corretti e verificati nelle rispettive sezioni: non riaperti né modificati, soltanto rieseguiti come regressione. Il diagnostico originale Q2 e quello Q3 restano byte-identici al preflight.

Aggiunti esclusivamente **9 casi** in `tests/Feature/QuesturaConclusiveAuditTest.php`: 3 percorsi pagina/generazione sotto auth, ruolo, CSRF e tenant; generazione periodo/singola, download storico e refresh senza rete o pulizia; ricevuta mancante/alterata e rifiuto della riconciliazione; invio incerto con acquisizione giornaliera senza finalizzazione implicita, conferma esplicita e retry idempotente; 2 ruoli admin/proprietario con ownership estranea su 6 percorsi; ricevuta non associabile a Test o ID inesistente senza rete. Test preesistenti e applicazione invariati.

| Fase | Esito locale ed evidenza |
|---|---|
| PMS → validazione → TXT | PASS: suite Boundary e regressione Schedine; nuovo confronto TXT periodo/singola e download byte-identico. Dati PMS preservati, con soli tre metadati di esportazione previsti aggiornati dalla generazione |
| WS Test → Q2 | PASS: Test positivo obbligatorio, contesto/tenant e payload esatto; nuovo Test dopo modifica; stesso valore TXT consegnato al double SOAP, senza ricostruzione intermedia |
| Q1 → WS Send → risposta/esito | PASS: prenotazione prima della rete, unique per struttura+schedina_id+trasporto; doppio submit, batch cambiato, esiti negativi/coerenti, timeout, crash e guasti DB nelle suite già acquisite |
| Ricevuta → associazione | PASS: acquisizione, byte PDF/download e integrità, duplicati, già acquisita, finestra 30 giorni e riuso successivo, parsing negativo/errore SOAP/DB, tenant e data incompatibili. Test o ID inesistente non acquisiscono; ricevuta alterata/mancante blocca download e finalizzazione |
| Riconciliazione → retention | PASS: uncertain conserva payload e non riceve implicitamente un’associazione di accettazione; conferma HTTP obbligatoria. Finalizzazione ripetuta senza nuovi eventi/rete; ricevuta, metadati, reservation e PMS conservati, payload/TXT rimossi solo nei percorsi consentiti |
| Autorizzazioni | PASS: kernel/middleware HTTP, auth, ruoli, struttura, ownership, CSRF e separazione tenant su pagina/generazione/download/Test/Send/ricevute/finalizzazione; le altre route già coperte da FinalAudit restano PASS |
| Privacy | PASS: regressione completa Q3 e log sintetico nei residui. Nessuna nuova fuga distinta osservata; nessun redesign o modifica Handler |

Ricevuta giornaliera unica per struttura/data, **non prova automatica dell’accettazione delle singole righe**. Per uncertain/partial/in_progress servono riconciliazione esplicita e verifica operativa; non si inventano identificativi remoti. Il parsing locale controlla contratto base64Binary, tipo/dimensione, intestazione/EOF e hash: nessuna attestazione strutturale completa o della firma PDF reale. Questa limitazione nota resta distinta dagli errori di parsing provati.

Senza ricevuta, per incerto non riconciliato e per TXT soltanto scaricato non si pulisce. Q1 sopravvive alla minimizzazione; Test minimizzato non autorizza nuovo Send, e modificare PMS non aggira la riserva. File/DB non sono atomici: guasti e retry sono coperti dalle suite Retention/LoggingPrivacy. Lock DB su ricevuta/tentativo/copie e vincoli unici sono garanzie strutturali; chiamate ripetute sono prove sequenziali. **Non attestata una vera race multiprocesso** su Send o retention, né acceptance browser con due tab fisici. Le richieste duplicate rappresentano la frontiera logica doppio click/two-tab; refresh GET non trasmette e non minimizza.

Legacy: regressione Q1 PASS sul blocco conservativo del solo tenant con Send non simulati e identità/reservation assente, anche dopo minimizzazione. Nessun backfill/sblocco automatico. Audit degli archivi, recupero delle associazioni tecniche e eventuale bonifica richiedono autorizzazione separata; identità non dimostrabile mantiene il blocco. Nessun dato legacy reale esaminato.

### Esecuzioni, guardrail e limiti

Suite completa autorizzata dell’audit Questura e regressione Schedine: **187 test / 2.774 asserzioni PASS**, comprendente la baseline 178/2660 più i 9 nuovi casi. Q1/Q2/Q3 tutti PASS nello stesso run; nessuna somma di suite sovrapposte. Non equivale alla suite globale di tutte le funzionalità del gestionale.

Runtime finale `/private/tmp/schedine-test-_saxbw4z`; DB `test_geo_de2e99e03104b602f4000aeeb6bc003d`; MySQL PID64182/porta55969, HTTP PID64187/porta55970. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Launcher esclusivo `tests/Isolation/run.py`, inclusioni e selezione integrale nel comando sotto. Identità/riconnessione e 23 rifiuti override prima delle migrazioni, build asset e GEO immutabile PASS. Risorse effimere fermate/rimosse e assenza verificata.

**5 guardrail PASS**, **14 controlli sicurezza/SOAP PASS**, sintassi **41 PHP PASS**, Pint `--test` sul solo nuovo file PASS, hash cataloghi/WSDL e `git diff --check` PASS. Nessun ulteriore analizzatore statico configurato nei manifest del progetto per questo intervento. 142 migrazioni storiche e 6 entry point/guardie byte-identici a HEAD. Nessuna guardia indebolita.

Registro diagnostici, senza difetti applicativi dimostrati: primo run **11/103, 1 errore** per asserzione content su risposta streamed; secondo **14/136, 2 failure** per confronto dei tre metadati di esportazione attesi e mancata riproposizione della selezione estera dopo normalizzazione sessione nella precedente richiesta respinta. Corretti soltanto i nuovi test: confronto dei dati PMS esclusi i tre metadati, verifica separata del contatore e selezione estranea esplicita per ciascuna chiamata. Terzo **14/147 PASS**, inclusi 5 test contratto SOAP sovrapposti. Runtime rispettivi `/private/tmp/schedine-test-3qpofcm2`, `/private/tmp/schedine-test-rzfqxf8r`, `/private/tmp/schedine-test-qlyb79f8`, tutti rimossi. Uno script di ispezione manifest interrotto con AttributeError perché il catalogo è una lista; hash successivamente verificati con struttura corretta. Nessun fallimento occultato né asserzione preesistente eliminata. Controllo inventario finale inizialmente interrotto perché `git status` aggregava le directory untracked: rieseguito con `--untracked-files=all`, confermati 47 file preservati e soltanto i tre file dichiarati. `ps` negato dal sandbox nella verifica dei PID; ripetuto in sola lettura con escalation, nessuno degli otto PID effimeri ancora presente. Nessun impatto sui risultati dei test.

Limiti operativi: accettazione reale Alloggiati Web, rete/TLS/auth reali e firma PDF non verificati; race multiprocesso non attestata; rollout/migrazioni/APP_KEY, storage e backup/restore richiedono procedura autorizzata. Cataloghi/WSDL datati 06/10/2026 non riverificati online in questa fase. Confronto tecnico con TXT accettati resta evidenza separata già documentata, non acceptance corrente.

**POSSIBILE BONIFICA LOG STORICI DA VALUTARE PRIMA DELLA PRODUZIONE**: meccanismo storico provato con fixture sintetiche; presenza effettiva nei log operativi NON verificata. Nessuna ispezione/cancellazione log reali. Gate preproduzione, senza impedire la chiusura locale.

### Git e file della sola fase conclusiva

Branch `main`; HEAD e `origin/main` locale `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind locale 0/0, nessun fetch; staging vuoto. Preflight **19 tracked modificati /30 untracked**, fine **19/31**, 50 file locali. Il riferimento locale non attesta il remoto corrente.

File prodotti da questa fase, esattamente:

- **nuovo** `tests/Feature/QuesturaConclusiveAuditTest.php`;
- **aggiornato** `docs/questura-audit-2026-10-06.md`;
- **aggiornato** `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`.

Gli altri **47 file preesistenti** sono byte-identici agli hash del preflight, inclusi tutti gli untracked preesistenti eccetto il rapporto espressamente aggiornato. Nessuna modifica applicativa, schema/config o test precedenti; nessuna eliminazione/sanamento workspace. Script/log di esecuzione soltanto sotto `/private/tmp`, non artefatti del repository.

**Nessun Send/Test reale, commit, push, deploy, modifica .env reale, produzione, DB/migration reali, cancellazione PMS o ispezione/modifica log operativi.** Risorse sintetiche effimere soltanto. **STOP FINALE: attendere nuova autorizzazione; nessuna prova reale eseguita o autorizzata dal presente verdetto.**

Comando completo dell’esecuzione finale:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php \
 app/Console/Commands/QuesturaExpiredReceipts.php app/Console/Commands/QuesturaArchiveAudit.php \
 config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 database/migrations/2026_10_06_120000_add_questura_retention.php \
 database/migrations/2026_10_06_130000_add_questura_send_reservations.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json --fixtures tests/Isolation/questura-checks.php \
 --phpunit tests/Feature/QuesturaRetentionTest.php tests/Feature/QuesturaCredentialEncryptionTest.php \
 tests/Feature/QuesturaBoundaryTest.php tests/Feature/QuesturaWsContractTest.php \
 tests/Feature/QuesturaLegacyMigrationTest.php tests/Feature/QuesturaIdempotencyTest.php tests/Feature/QuesturaTestSendSnapshotTest.php tests/Feature/QuesturaPayloadCoherenceTest.php \
 tests/Feature/StrutturaAuthorizationTest.php tests/Feature/SchedinaStoreTest.php \
 tests/Unit/PianoSyncComponentiTest.php tests/Unit/ComponentiImportP1Test.php tests/Feature/QuesturaFinalAuditTest.php tests/Feature/QuesturaLoggingPrivacyTest.php tests/Feature/QuesturaConclusiveAuditTest.php
```

---

## Correzione P1-Q3 — riservatezza della diagnostica Questura, 07/10/2026

**P1-Q3 CORRETTO E VERIFICATO nelle prove isolate. PRONTO A RIPRENDERE L’AUDIT QUESTURA DOPO P1-Q3. BASELINE IN AUDIT invariato.** Nessuna ripresa automatica dell’audit residuo; nessuna readiness produzione, prova reale o accettazione Alloggiati Web attestata. Lo STOP dell’appendice Q è superato esclusivamente dalla correzione e dalle evidenze qui descritte. Nessun nuovo P0/P1 sostanzialmente distinto emerso nelle prove autorizzate.

### Causa e correzione minima

Prima di modificare codice riprodotta nuovamente la selezione originale, con diagnostico byte-identico: **27 test / 317 asserzioni, 1 failure Q3**. INSERT Send annullato, zero Send e riserve, PMS conservato; base64 personale esatta presente nel log sintetico. Causa confermata: `QueryException::formatMessage` interpola i binding, incluso payload JSON con snapshot, nella query SQL del messaggio. Il controller lascia uscire il guasto generico; il reporting Laravel aggiunge l’oggetto eccezione, eventuale `context()` e catena precedente al messaggio. Nascondere la UI non sarebbe sufficiente.

**Unico file applicativo modificato: `app/Exceptions/Handler.php`, righe 45–123.** È indispensabile intervenire prima del logger, nel confine comune HTTP/console delle vie equivalenti. Non modificati controller, trasporto, servizi, modello, schema o algoritmi Q1/Q2. La modifica è espressamente nel perimetro applicativo strettamente necessario autorizzato dalla task, anche se Handler apparteneva ai 45 file precedentemente protetti.

`reportThrowable` riconosce il ciclo dalle classi Questura nella trace (anche servizi chiamati senza route) o dal nome route `questura.*`, senza usare argomenti, SQL o dati personali. Prima di callback `report()`, `context()` e reporting predefinito registra soltanto messaggio fisso, `error_code=QUESTURA_WORKFLOW_ERROR`, operazione da elenco chiuso e classe eccezione. Nessun oggetto originale, previous, stack, binding, messaggio provider, modello/DTO o snapshot viene consegnato al logger da questo reporting. Timestamp già gestito dal logger; nessun username, tenant, hash o ulteriore dato aggiunto senza necessità. Filtri Laravel sulle eccezioni non reportabili e deduplicazione preservati. Il test con eccezione dotata di `report()`/`context()` sensibili dimostra che tali metodi non vengono invocati.

Rendering HTTP degli errori inattesi Questura: risposta controllata testuale o JSON, anche con debug attivo, senza trace/SQL/contesto; 500 generico e status 5xx esistente preservato. Autenticazione, validazione e conversioni HTTP ordinarie restano Laravel, inclusi CSRF 419 e 401/403/404. Anche output console Questura controllato. Reporting estraneo a Questura resta predefinito, provato con messaggio tecnico e oggetto eccezione nel contesto: nessuna disabilitazione globale del logging DB.

### Vie equivalenti verificate

| Via | Prova e risultato |
|---|---|
| A — DB prima di Send | Diagnostico originale invariato PASS; rollback, zero chiamate Send/tentativi/riserve e PMS conservato; niente snapshot base64 nel log |
| B — DB nella reservation Q1 | Trigger MySQL reale prima dell’INSERT reservation, errore con marcatori sintetici personali/segreti: tentativo annullato, zero Send/riserve, log tecnico minimizzato |
| C — eccezione prima della rete | Eccezione con messaggio, previous e callback/context sensibili: quattro combinazioni debug e risposta HTML/JSON; zero Send/riserve, callback mai invocati. Due ulteriori eccezioni HTTP 500/503 con debug preservano status e non espongono catena |
| D/E — SOAP e timeout | SoapFault e timeout sul Send danno uncertain con riserva conservata; errore GenerateToken prima del Send dà technical_error ed esclusione certa consolidata, nessun Send e riserva liberata. Nessun segreto/payload nei risultati, eventi, risposta o log |
| E — DB dopo possibile Send | Guasto reale UPDATE esito: una chiamata Send, stato in_progress e riserva conservati, retry bloccato senza secondo Send; niente dati personali nel log |
| F — acquisizione ricevuta | SoapFault Ricevuta controllato; guasto DB INSERT ricevuta: zero record, file PDF effimero rimosso, snapshot e riserva preservati, diagnostica senza dati personali |
| G — parsing/riconciliazione | Ricevuta con PDF malformato contenente marcatori sensibili respinta; guasto DB durante minimizzazione della riconciliazione: rollback, snapshot e riserva conservati, PDF esistente preservato; nessuna copia nella diagnostica |
| Console / fuori scope | Errore retention senza route Questura report/render console minimizzati; errore estraneo mantiene il reporting normale |

Le prove controllano separatamente TXT completo, base64 personale, nomi/cognomi anche normalizzati in maiuscolo, documento, data di nascita anche nel formato TXT, indirizzo sintetico, username, password, WSKEY, token effettivo del double e marcatori di token/certificato/header. Superfici: file log single effimero, record e contesto Monolog, eventi, risposte HTTP e console pertinenti. Contesto logger composto soltanto da stringhe tecniche ammesse, senza oggetto exception. **Payload, snapshot/base64 personali e credenziali/segreti assenti nelle superfici diagnostiche verificate.** Lo snapshot temporaneo negli archivi autorizzati resta necessario al ciclo e soggetto alla retention: non si dichiara eliminato prima della ricevuta. Nessun dato reale utilizzato.

### Test, regressioni e fallimenti intermedi conservati

| Selezione | Esito nuovo | Precisazione |
|---|---|---|
| RED originale + contratto, prima del fix | **27 / 317, 1 failure Q3** | Coincide con la prova documentata; nessuna modifica al diagnostico |
| Prima verifica dopo il fix | **27 / 287, 5 failure** | Q3 passa; rendering anticipato convertiva TokenMismatchException in 500 anziché 419. Corretto il nuovo Handler rispettando prepareException Laravel; nessun test indebolito |
| Prima matrice privacy + contratto | **20 / 953 PASS** | 15 casi Q3 nuovi + 5 contratto; successivamente aggiunti due casi HTTP5xx e controlli dei marcatori normalizzati/token effettivo |
| Originale + Q1 + Q2 + contratto | **70 / 763 PASS** | 22 casi audit/Q3, 18 Q1, 25 Q2 e 5 contratto. Q1 identità e Q2 uguaglianza Test/Send restano distinti; sorgenti test byte-identiche |
| Intera baseline autorizzata ampliata | **178 test / 2.660 asserzioni PASS** | Tutti i 139 baseline precedenti + 22 audit finale + 17 nuova matrice privacy; nessun caso escluso. Nessun error/risky/failure |
| Guardrail | **5 PASS** | Esecuzione standalone nuova; nessun bootstrap DB/app fuori dal launcher |
| SOAP/sicurezza | **14 PASS** | Involucro attestato questura-checks.php dopo la suite completa, soli doubles |
| Sintassi / diff / integrità | **39 PHP PASS; diff check PASS** | Hash cataloghi/WSDL PASS; 142 migration storiche e sei guardie/entrypoint byte-identici a HEAD |

Risultati sovrapposti non sommati. Nessuna static analysis dedicata o comando lint aggiuntivo obbligatorio individuato nei file/config/script; eseguita sintassi PHP e build asset con verifica architettura/GEO nel launcher. Non eseguiti formatter globale, installazioni o aggiornamenti dipendenze. La suite completa è l’intera baseline autorizzata Questura/Schedine/autorizzazioni/Componenti ampliata, non la suite globale storica con debiti estranei.

PHP8.3.33, PHPUnit10.5.38, MySQL8.0.36. Tutti i run attestano identità processi/DB/HTTP, riconnessione, 23 rifiuti override prima delle migrazioni e build/GEO. Trigger sintetici creati prima della transazione RefreshDatabase e rimossi dopo rollback; nessuna modifica a guardie/infrastruttura. Sole fixture, trasporto SOAP integralmente double, nessuna rete Questura.

| Run / log sintetico temporaneo | Runtime / DB | MySQL PID:porta / HTTP PID:porta |
|---|---|---|
| RED — /private/tmp/questura-q3-red.log | /private/tmp/schedine-test-gwtl26yv / test_geo_a17a335286fb4777e948bc18de5f6bd8 | 54496:53487 / 54501:53488 |
| Prima verifica, FAIL — /private/tmp/questura-q3-original-green.log | /private/tmp/schedine-test-a13eh6hi / test_geo_86c23a2860ca10d20603d11c51cfffa7 | 55300:53692 / 55305:53693 |
| Vie equivalenti — /private/tmp/questura-q3-paths.log | /private/tmp/schedine-test-r2sk24cc / test_geo_470a3298bceb0bf9a6730435242af769 | 56060:53900 / 56065:53901 |
| Q1/Q2/originale — /private/tmp/questura-q3-regression.log | /private/tmp/schedine-test-oq5aw215 / test_geo_9af765a78839d8d63b4c79c7267c0576 | 56662:54058 / 56667:54059 |
| Completa — /private/tmp/questura-q3-full.log | /private/tmp/schedine-test-u4r2fpes / test_geo_d93041297d4470d09e3a647658dbc3d9 | 58268:54513 / 58273:54514 |

Il nome original-green.log non indica PASS: conserva le cinque failure intermedie. Log RED mai sovrascritto. Processi/runtime fermati e rimossi dal launcher; assenza delle cinque directory verificata.

Comando completo esatto, stessa lista include usata per le selezioni specifiche:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php \
 app/Console/Commands/QuesturaExpiredReceipts.php app/Console/Commands/QuesturaArchiveAudit.php \
 config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 database/migrations/2026_10_06_120000_add_questura_retention.php \
 database/migrations/2026_10_06_130000_add_questura_send_reservations.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json --fixtures tests/Isolation/questura-checks.php \
 --phpunit tests/Feature/QuesturaRetentionTest.php tests/Feature/QuesturaCredentialEncryptionTest.php \
 tests/Feature/QuesturaBoundaryTest.php tests/Feature/QuesturaWsContractTest.php \
 tests/Feature/QuesturaLegacyMigrationTest.php tests/Feature/QuesturaIdempotencyTest.php tests/Feature/QuesturaTestSendSnapshotTest.php tests/Feature/QuesturaPayloadCoherenceTest.php \
 tests/Feature/StrutturaAuthorizationTest.php tests/Feature/SchedinaStoreTest.php \
 tests/Unit/PianoSyncComponentiTest.php tests/Unit/ComponentiImportP1Test.php tests/Feature/QuesturaFinalAuditTest.php tests/Feature/QuesturaLoggingPrivacyTest.php
```

Selezioni specifiche con gli stessi include, senza --fixtures: RED/prima verifica `--phpunit tests/Feature/QuesturaFinalAuditTest.php tests/Feature/QuesturaWsContractTest.php`; prima matrice `--phpunit tests/Feature/QuesturaLoggingPrivacyTest.php tests/Feature/QuesturaWsContractTest.php`; regressioni `--phpunit tests/Feature/QuesturaFinalAuditTest.php tests/Feature/QuesturaIdempotencyTest.php tests/Feature/QuesturaTestSendSnapshotTest.php tests/Feature/QuesturaPayloadCoherenceTest.php tests/Feature/QuesturaWsContractTest.php`. Guardia standalone: `python3 -B tests/Isolation/test_guards.py`.

### File, Git, log storici e fermata

Esattamente quattro file modificati/creati rispetto al preflight di questa task:

1. `app/Exceptions/Handler.php`: unico cambiamento applicativo, indispensabile per il reporting comune Questura; campi dontFlash precedenti preservati.
2. `tests/Feature/QuesturaLoggingPrivacyTest.php`: nuova matrice di 17 casi, guasti e dati soltanto sintetici.
3. `docs/questura-audit-2026-10-06.md`: questo rapporto e correzione di un delimitatore Markdown nella sezione storica Q3.
4. `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`: quadro corrente e appendice R.

Diagnostico originale QuesturaFinalAuditTest e tutte le verifiche Q1/Q2 byte-identici. Dei 45 precedentemente protetti, solo Handler rientra nell’autorizzazione applicativa strettamente necessaria; **gli altri 44 invariati**. In totale **45 dei 48 file locali iniziali invariati** (comprendono il diagnostico originale); nessun file estraneo toccato. Preflight/finale: main, HEAD e origin/main locale `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0, staging vuoto, nessun fetch; da19 tracked modificati/29 untracked a19/30, 49 file locali finali. Nessun nuovo riferimento remoto attestato.

**POSSIBILE BONIFICA LOG STORICI DA VALUTARE PRIMA DELLA PRODUZIONE.** Esiste evidenza concreta del meccanismo e della copia nel log sintetico pre-fix, non soltanto un’ipotesi statica. L’esistenza di snapshot in log operativi storici resta non verificata: dipende dall’avvenimento di quei guasti. Nessun log reale, backup o produzione letto, copiato, cancellato o modificato. Valutazione/bonifica eventuale separata e autorizzata, non parte di questo fix.

Q1 regressione PASS: identità struttura+schedina_id, riserve e retry conservativi invariati. Q2 regressione PASS: payload Test uguale al Send e nuovo Test dopo modifica, TOCTOU sui byte già coperto e preservato. Nessuna race multiprocesso, TLS live, firma PDF, acceptance ente, rollout o prova reale attestati. Nessun nuovo P0/P1 distinto emerso; audit residuo non ripreso.

Chiusura: CODICE SÌ; TEST SÌ (nuovo file, precedenti invariati); DOCUMENTAZIONE/MAESTRO SÌ; CONFIGURAZIONE NO; SCHEMA APPLICATIVO NO; DATI REALI NO (sole fixture/trigger effimeri SÌ); TEST ESEGUITI SÌ; COMMIT NO; PUSH NO; DEPLOY NO; INVII QUESTURA REALI NO; CANCELLAZIONE PMS NO; BONIFICA LOG REALI NO. **P1-Q3 CORRETTO E VERIFICATO — PRONTO A RIPRENDERE L’AUDIT QUESTURA DOPO P1-Q3. Fermata qui; attendere nuova autorizzazione.**

---


## Ripresa finale dell’audit — STOP P1-Q3, 07/10/2026

**NON PRONTO — STOP P0/P1: nuovo P1-Q3 verificato. BASELINE IN AUDIT invariato.** Q1 (identità/idempotenza) e Q2 (uguaglianza payload Test/Send) restano corretti secondo le evidenze precedenti; questa ripresa non ne attesta una nuova regressione completa. Nessuna correzione applicativa eseguita. Interrotto l’audit alla prima nuova prova P1.

### Prova, causa e impatto P1-Q3

`tests/Feature/QuesturaFinalAuditTest.php::test_errore_db_pre_rete_non_replica_snapshot_personale_nei_log` (righe 119–134) esegue un vero POST WS Test positivo con SOAP double, poi un POST Send. Un trigger MySQL sintetico BEFORE INSERT su `questura_transmissions`, limitato a mode=send, solleva SQLSTATE 45000 con messaggio «GUASTO DB SINTETICO». Risultato: HTTP 500, zero chiamate Send, zero tentativi Send persistiti, zero riserve Q1, Schedina PMS conservata. Queste asserzioni passano. Fallisce soltanto l’oracolo che vieta la presenza nel log della base64 esatta dello snapshot personale verificato. La failure stampa solo un booleano e il messaggio, senza payload.

**Causa:** `QuesturaExportController.php` righe 347–395 passa il payload a `storeTransmission` nella transazione prima del trasporto; il catch gestisce il vincolo unique, ma lascia uscire la QueryException generica. `Illuminate/Database/QueryException::formatMessage` interpola i binding nella query SQL del messaggio. Il Kernel HTTP la passa a Handler::report; `app/Exceptions/Handler.php` non sanifica questa catena. `config/logging.php` usa stack/single. Il test configura single esclusivamente nel checkout effimero e dimostra che il log replica lo snapshot reversibile, fuori dalle copie gestite da `QuesturaRetentionService::minimizeCopies`.

**Impatto P1:** copia nominativa superflua nei log in caso di errore di persistenza, potenzialmente conservata oltre la minimizzazione degli archivi. La presenza esatta nel log sintetico è osservata; la persistenza oltre retention è un rischio dedotto dal codice. Non sono dimostrati accesso pubblico o cross-tenant ai log, incidenti reali o esposizione di credenziali operative. Nessun log reale o dato personale reale letto. Difetto distinto da Q1/Q2: il rollback prima della rete funziona, la riservatezza della diagnostica no.

**Proposta minima, NON implementata:** intercettare il reporting delle eccezioni di persistenza nel perimetro Questura prima che SQL, binding, payload, contesto o catena originale raggiungano il logger. Conservare solo fase/operazione, identificatori tecnici consentiti e codice tecnico controllato. Non basta nascondere l’errore UI o ripulire il messaggio lasciando la precedente eccezione nel contesto; non disabilitare globalmente il logging DB. Preservare rollback pre-Send, Q1/Q2, stato incerto dopo possibile rete e retention. Dopo autorizzazione: questo diagnostico deve passare per sanificazione effettiva, con prove DB prima/dopo possibile rete e assenza di PII in risposta/log/contesto/catena. Eventuale bonifica di log operativi e backup richiede intervento distinto autorizzato.

### Portata della ripresa e limiti

| Area | Evidenza nuova e limite |
|---|---|
| A — WS Test | Token fallito, timeout Test, autenticazione falsa, Test negativo bloccano Send; errori normali sanificati. TLS solo lettura configurazione, nessuna connessione reale |
| B — WS Send | Test positivo obbligatorio, Test ripetuto e doppio submit verificati. Guasto DB pre-rete: rollback PASS, privacy log FAIL. Q1/Q2 completi restano evidenza precedente |
| C — Ricevute | Malformata rifiutata, retry valido, duplicato senza nuova SOAP, download byte esatto; acquisizione a 30 giorni, blocco nuova a 31 e riuso già archiviata. Nessuna attestazione firma o ricevuta ufficiale |
| D — Retention | Finalizzazione conserva PMS e riserva, tenant estraneo non minimizzato. Log esclusi dalla minimizzazione: P1-Q3. Nessuna nuova suite retention completa |
| E — HTTP/ruoli/tenant/CSRF | Quattro ruoli su propria struttura; nove route con anonimo, ruolo ignoto, CSRF e tenant forgiato; ID reali di archivi/ricevute dell’altro tenant respinti. Nessuna matrice browser globale o race multiprocesso |
| F — Privacy | Errori SOAP normali e superfici risultato/eventi/risposta controllati; **QueryException replica snapshot nel log: FAIL P1-Q3** |
| G — Legacy/frontiere | Evidenze conservative precedenti preservate. Completamento arrestato; nessuna nuova attestazione globale o accettazione operativa |

### Esecuzioni: non sommare selezioni sovrapposte

| Esecuzione | Risultato e diagnosi |
|---|---|
| Prima selezione | 26 test / 298 asserzioni, 1 risky: provider chiamato testFailures scoperto come test senza asserzioni; rinominato solo nel nuovo file |
| Selezione corretta e ownership reale | **26 test / 310 asserzioni PASS**, zero risky |
| Prima fixture guasto DB | 27 test, 18 errori: DDL del trigger dentro transazione causava commit implicito e SAVEPOINT assente. Prova P1 non valida; conteggio asserzioni non attestato dall’output troncato |
| Fixture guasto DB corretta | **27 test / 317 asserzioni, 1 failure**, zero errori/risky: P1-Q3 unico fallimento; altri 21 casi residui e 5 contratto passano |
| Guardrail | **5 PASS**, python3 -B tests/Isolation/test_guards.py |

Trigger creato prima della transazione RefreshDatabase e rimosso dopo il rollback: nessuna modifica all’infrastruttura o alle guardie. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Tutti i run attestano identità/riconnessione/23 rifiuti override prima delle migrazioni e build/GEO. Sole fixture e doubles SOAP, senza rete Questura. **Suite completa 139/1.086 e 14 SOAP/sicurezza non rieseguiti per STOP**: restano risultati storici della correzione Q2, non risultati nuovi.

| Run | Runtime / DB | MySQL PID:porta / HTTP PID:porta |
|---|---|---|
| 1 | /private/tmp/schedine-test-s0hepngs / test_geo_a2c4ea3dcd2de0014b5937afe0e5b58b | 51079:52573 / 51084:52574 |
| 2 | /private/tmp/schedine-test-pv7fjcy8 / test_geo_a2dfa868c47c58104378d9011ce49377 | 51819:52766 / 51824:52767 |
| 3 | /private/tmp/schedine-test-l4pm4xp_ / test_geo_228f51532832578f7a19de17ca14cdda | 52528:52951 / 52535:52952 |
| 4 | /private/tmp/schedine-test-_n0aauzt / test_geo_ee4340880e02f33b04d0743b0e989b13 | 53353:53171 / 53360:53172 |

Il launcher ha attestato cleanup dei processi e runtime; assenza delle quattro directory controllata in chiusura. Log sintetici temporanei: /private/tmp/questura-final-specific.log (run 2, sovrascrive run 1), /private/tmp/questura-final-privacy.log (run 3), /private/tmp/questura-final-privacy-2.log (run 4). Il risultato del run 1 resta nell’output acquisito, non nel log corrente.

Comando selezione, stessi include per tutte le esecuzioni:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php \
 app/Console/Commands/QuesturaExpiredReceipts.php app/Console/Commands/QuesturaArchiveAudit.php \
 config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 database/migrations/2026_10_06_120000_add_questura_retention.php \
 database/migrations/2026_10_06_130000_add_questura_send_reservations.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json --phpunit tests/Feature/QuesturaFinalAuditTest.php tests/Feature/QuesturaWsContractTest.php
```

### Scope e chiusura

Esattamente tre file interessati da questa ripresa: nuovo `tests/Feature/QuesturaFinalAuditTest.php`, questo rapporto e `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`. Nessun test Q1/Q2 preesistente modificato o neutralizzato, nessun fix applicativo. I restanti 45 file locali preesistenti risultano byte-identici agli hash del preflight.

Preflight/finale: main, HEAD e origin/main locale `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0, staging vuoto, nessun fetch; da 19 tracked modificati/28 untracked a 19/29. Sintassi dei 38 PHP locali e git diff --check PASS in chiusura. Nessun invio reale, commit, push, deploy, modifica .env reale, DB/migration operativi o cancellazione PMS. **NON PRONTO — STOP P1-Q3. Attendere autorizzazione distinta per la correzione; nessuna prova reale autorizzata.**

---


## Correzione P1-Q2 — coerenza WS Test → WS Send, 07/10/2026

**P1-Q2 CORRETTO E VERIFICATO nelle prove isolate. PRONTO A RIPRENDERE L'AUDIT QUESTURA DOPO P1-Q2.** Questa evidenza chiude il difetto dell'appendice N del Maestro e supera il conflitto dell'appendice O tramite la deroga esplicita dell'utente. Progetto **BASELINE IN AUDIT**; nessuna readiness produzione, accettazione Alloggiati Web o ciclo reale attestati. Il resto dell'audit non viene ripreso automaticamente.

### Causa e soluzione minima

Prima del fix, ogni POST rigenerava il TXT senza legarlo al precedente Test: modificare una Schedina dopo Test permetteva il primo Send di contenuto differente. Riproduzione obbligatoria confermata prima delle modifiche: **7 test / 69 asserzioni / 1 failure**, stesso diagnostico e causa dell'appendice N. Il test originale `QuesturaTestSendSnapshotTest.php` resta byte-identico. Runtime `/private/tmp/schedine-test-y6mnb9j0`, DB `test_geo_da34794950c66677df5c3ac9e59a57bc`, MySQL PID45672/porta51213, HTTP PID45678/porta51214; attestazione/build PASS e cleanup verificato. Il file di log temporaneo della riproduzione è stato successivamente sovrascritto per errore dal primo comando GREEN: l'output RED acquisito nella conversazione e l'appendice O ne conservano risultato e causa; non si dichiara ancora disponibile quel log originale.

`QuesturaExportController::requireVerifiedPayload` controlla l'ultima verifica per struttura autorizzata, periodo e trasporto, con lock nella stessa transazione breve che precede le riserve. Nessuna fiducia in hash, flag o ID Test dal browser. Per live richiede stato Test `unknown`, conteggio valido uguale all'intero elenco ed errori per riga vuoti: `unknown` da solo, HTTP 200 o `accepted=false` non provano Test positivo. Il parser esistente attesta esito generale, tipi, conteggio e tutte le righe prima di conservare questi dati. Per simulation è richiesto Test simulation, che non autorizza live. Test più recente negativo/in_progress, assente, minimizzato/finalizzato o con contesto diverso non autorizza Send.

Il confronto copre **ID Schedine ordinati, ID componenti ordinati, tenant, periodo, scope, modalità, numero record, charset, dimensione, SHA-256 e uguaglianza esatta dei byte decodificati dello snapshot**. Una Schedina diversa con gli stessi byte non eredita la verifica. Una modifica non trasmessa (`or_address` nelle fixture) non invalida il Test soltanto perché cambia `updated_at`. Nessun nuovo payload personale permanente, modello, colonna o migration aggiunti: si usa lo snapshot temporaneo già soggetto alla retention esistente.

Mismatch: errore comprensibile «Ripetere WS Test prima di Send», zero nuove chiamate SOAP, zero tentativi Send e zero riserve. La UI spiega la nuova precondizione. Non invia automaticamente il vecchio snapshot né il nuovo contenuto; occorre nuovo Test positivo.

### Q1, Q2 e TOCTOU

- **Q1 — identità/idempotenza:** invariati algoritmo, schema e unique `(struttura_id, schedina_id, transport_mode)`, riserve prima della rete, release solo con esclusione certa consolidata, blocco legacy/uncertain/partial/crash e permanenza dopo retention. Non torna a dipendere dal solo hash.
- **Q2 — contenuto verificato:** hash robusto ed esatta uguaglianza dei byte/contesto autorizzano soltanto l'elenco corrente verificato positivamente; non sostituiscono la riserva Q1. Dopo mismatch, nuovo Test consente il primo Send; dopo Send incerto o concluso, anche un nuovo Test non libera Q1.
- **TOCTOU — payload:** il TXT costruito è catturato per valore nella transazione, confrontato con Test prima delle riserve e poi passato direttamente a `QuesturaWebService::send`. Non esiste una seconda costruzione del TXT fra confronto e trasporto; il servizio suddivide quello stesso valore sui CRLF. Il double osserva i byte effettivi all'ingresso SOAP, la riserva presente e l'uguaglianza esatta con Test. Il builder della prova fallisce se chiamato una seconda volta: una sola costruzione osservata. Non si dichiara un lock PMS durante la rete né una prova di race multiprocesso; la garanzia riguarda lo snapshot corrente preparato dal backend e i byte effettivamente consegnati al trasporto.

### Test e risultati nuovi

| Selezione | Risultato | Portata |
|---|---|---|
| RED diagnostico originale + contratto | **7 / 69, 1 failure** | Riproduzione prima del fix, nessun test modificato |
| GREEN diagnostico originale + contratto | **7 / 65 PASS** | Dopo modifica reale, il ramo modificato blocca prima del Send |
| Q2 specifica + diagnostico + contratto | **26 / 247 PASS** | Prima estensione; quattro ulteriori casi invalidi inclusi nella selezione completa finale |
| Q1 + contratto | **23 / 233 PASS** | 18 casi Q1, cinque contratto; non sommare le prove sovrapposte |
| Intera selezione baseline ampliata | **139 test / 1.086 asserzioni PASS** | Tutti i 114 casi baseline, diagnostico Q2 e 23 nuovi casi; nessuna esclusione |
| Guardrail isolamento | **5 PASS** | `python3 -B tests/Isolation/test_guards.py` e involucro isolato |
| SOAP/sicurezza | **14 PASS** | Involucro `questura-checks.php`, soli doubles |
| Sintassi PHP | **37 file PASS** | Tutti i PHP locali modificati/nuovi |
| Diff / riferimenti / schema storico | **PASS** | `git diff --check`, SHA cataloghi/WSDL, 142 migration storiche e guardie principali byte-identiche a HEAD |

Matrice Q2: nessuna modifica (diagnostico originale); nome/permanenza/documento; campo PMS non trasmesso; batch A+B+C con sola B modificata; Test assente/negativo/malformato/unknown senza prova; tenant, identità, ordine, componenti, trasporto, numero record; payload corrotto, minimizzazione/finalizzazione e Test in_progress; Test negativo successivo invalida quello positivo; identità diversa con byte identici; nuovo Test dopo mismatch e Q1 dopo Send incerto; TOCTOU. Nessun nuovo P0/P1 distinto emerso nelle prove circoscritte.

Runtime GREEN originale `/private/tmp/schedine-test-pw05r197`, DB `test_geo_96a4467bfcf4eadd2bb08e491d1aa371`, MySQL PID46243/porta51322, HTTP PID46249/porta51323. Q2 specifica `/private/tmp/schedine-test-vxnkt5tw`, DB `test_geo_837163221a1f4ae09a3a2a51ae73fd50`, MySQL PID46650/porta51412, HTTP PID46656/porta51413. Q1 `/private/tmp/schedine-test-d55ejuos`, DB `test_geo_feeff988ec6af46cacf47479cdd86da4`, MySQL PID47385/porta51623, HTTP PID47391/porta51624. Finale `/private/tmp/schedine-test-m0a9ys3k`, DB `test_geo_c00dc077331abd1d3a7589f202eafe7f`, MySQL PID48117/porta51793, HTTP PID48150/porta51794. Tutti: identità/riconnessione/23 rifiuti override prima delle migrazioni e build/GEO PASS, processi fermati e directory rimosse. PHP8.3.33/PHPUnit10.5.38/MySQL8.0.36. Log temporanei GREEN: `/private/tmp/questura-q2-diagnostico-green.log`, `/private/tmp/questura-q2-specific.log`, `/private/tmp/questura-q1-regression.log`, `/private/tmp/questura-q2-full.log`. Nessun fallimento applicativo intermedio dopo il fix. Nessun comando static analysis obbligatorio o configurazione dedicata individuati; nessun formatter globale o dipendenza modificati.

Comando finale esatto:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php \
 app/Console/Commands/QuesturaExpiredReceipts.php app/Console/Commands/QuesturaArchiveAudit.php \
 config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 database/migrations/2026_10_06_120000_add_questura_retention.php \
 database/migrations/2026_10_06_130000_add_questura_send_reservations.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json --fixtures tests/Isolation/questura-checks.php \
 --phpunit tests/Feature/QuesturaRetentionTest.php tests/Feature/QuesturaCredentialEncryptionTest.php \
 tests/Feature/QuesturaBoundaryTest.php tests/Feature/QuesturaWsContractTest.php \
 tests/Feature/QuesturaLegacyMigrationTest.php tests/Feature/QuesturaIdempotencyTest.php tests/Feature/QuesturaTestSendSnapshotTest.php tests/Feature/QuesturaPayloadCoherenceTest.php \
 tests/Feature/StrutturaAuthorizationTest.php tests/Feature/SchedinaStoreTest.php \
 tests/Unit/PianoSyncComponentiTest.php tests/Unit/ComponentiImportP1Test.php
```

Comandi specifici: stessa lista `--include`, senza `--fixtures`, con `--phpunit tests/Feature/QuesturaTestSendSnapshotTest.php tests/Feature/QuesturaPayloadCoherenceTest.php tests/Feature/QuesturaWsContractTest.php` per Q2 e `--phpunit tests/Feature/QuesturaIdempotencyTest.php tests/Feature/QuesturaWsContractTest.php` per Q1. La selezione completa è l'intera baseline autorizzata Questura/Schedine/autorizzazioni/Componenti, non la suite globale storica del repository con debiti estranei.

### Scope, deroga e file esatti

Deroga dell'utente: rapporto e sole precondizioni delle fixture Q1 necessarie, nessun indebolimento delle verifiche sostanziali. Due suite interessate: `QuesturaIdempotencyTest` e i casi Q1 di `QuesturaBoundaryTest`. Test positivo preparato attraverso il vero HTTP kernel e parser prima di abilitare gli errori del solo Send; conteggi/lookup degli archivi limitati ai Send per non includere i nuovi Test. Nel double fixture Q1 la risposta Test enumera tutte le righe; le risposte Send e tutte le verifiche riserve/unique/retry/crash/PMS/retention restano sostanzialmente invariate. Anche dopo modifica si prepara un nuovo Test positivo nelle prove Q1, così il blocco osservato dimostra Q1 e non si limita a Q2.

Esattamente **sette file modificati/creati** nella correzione:

1. `app/Http/Controllers/QuesturaExportController.php`: gate Q2 prima delle riserve, nessuna modifica alla logica Q1.
2. `resources/views/questura/index.blade.php`: spiegazione Test positivo e ripetizione dopo modifica.
3. `tests/Feature/QuesturaIdempotencyTest.php`: sole precondizioni/setup Q1 e distinzione dei conteggi Send/Test.
4. `tests/Feature/QuesturaBoundaryTest.php`: nuovi Test positivi nei setup dei casi Q1 interessati.
5. `tests/Feature/QuesturaPayloadCoherenceTest.php`: nuova matrice Q2/TOCTOU/interazione Q1.
6. `docs/questura-audit-2026-10-06.md`: questa evidenza; sezioni precedenti conservate storiche.
7. `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`: stato ufficiale e appendice P.

Preflight/finale: branch **main**, HEAD e riferimento locale origin/main **`3eb559cfea9fed6a20e7949037f1d5a0236695be`**, ahead/behind **0/0**, staging vuoto, nessun nuovo fetch. Inizio **46 file locali: 19 tracked modificati, 27 untracked**; finale **47: stessi 19 tracked, 28 untracked**. Dei 26 originariamente protetti, tre rientrano nella deroga (rapporto e due suite): **gli altri 23 restano byte-identici**. Diagnostico Q2 originale, modello/servizi Q1, config, tutte le migration e gli altri file preesistenti fuori scope invariati rispetto al preflight.

**Nessuna trasmissione reale effettuata.** Nessuna credenziale reale, .env, DB operativo, migration di produzione, backup reale, cancellazione PMS, commit, push o deploy. Nessuna nuova migration o modifica delle regole di retention. Schema/dati mutati soltanto nelle fixture effimere. Rischi residui operativi: rollout/audit legacy, TLS, firma/ricevuta e accettazione reali, backup/restore e matrice globale; nessuna promessa di produzione. **STOP finale della correzione: attendere nuova task autorizzata per il resto dell'audit.**

---

## Correzione P1-Q1 di idempotenza — 06/10/2026

**P1 IDEMPOTENZA: CORRETTO E VERIFICATO nelle prove isolate. PRONTO A RIPRENDERE L'AUDIT QUESTURA.** Questa sezione prevale sullo STOP diagnostico seguente per il solo P1-Q1. Il gate operativo deve essere ripreso e completato separatamente; nessuna readiness di produzione o accettazione Alloggiati Web. Progetto **BASELINE IN AUDIT**, Questura generale **PARZIALE**.

### Preflight e riproduzione prima del fix

Repository `/Users/jorgeluccitelli/Herd/Schedinedinotifica`, branch `main`, HEAD e riferimento locale `origin/main` `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind **0/0**. Staging vuoto. Preservati i 43 file locali iniziali (19 tracked modificati, 24 nuovi); snapshot SHA-256 iniziale per confrontare lo scope. Nessun nuovo fetch: `origin/main` è il riferimento aggiornato nella diagnosi precedente, non una nuova interrogazione del server remoto. Letti richiesta, Maestro/appendice L, rapporto, test diagnostici, controller, modello, servizi, migrazioni e protocollo isolato. Scope esclusivo P1 Questura; rischio **ALTO**.

Rieseguita integralmente la selezione diagnostica prima di modificare il codice: **96 test / 633 asserzioni, 2 failure e zero errori**, stessi due casi incerto/concluso, **due Send anziché uno** dopo modifica del nome. I 94 precedenti passano. Runtime `/private/tmp/schedine-test-4u1erd5j`, DB `test_geo_418b963ae4dd67f447620e5ef0be5fdc`, MySQL PID32569/porta63339, HTTP PID32574/porta63340; identità/riconnessione/23 rifiuti override e build/GEO PASS, risorse fermate/rimosse. Prima esecuzione bloccata dalla sandbox sul bind loopback prima di avviare risorse; rieseguita con escalation per il solo runtime effimero. Nessun bypass delle guardie. **QuesturaBoundaryTest resta byte-identico all'inizio di questa task**, inclusi i due test diagnostici e tutte le asserzioni.

### Causa e identità scelta

La vecchia `send_key` dipendeva da trasporto/tenant/hash TXT: cambiando qualsiasi dato cambiavano i byte e la chiave, senza identificare la medesima Schedina. Ora l'identità primaria è **`struttura_id + schedina.id`**; `transport_mode` distingue la simulazione dalla comunicazione live. Una famiglia/gruppo riserva la Schedina PMS capo già presente nel dominio, che comprende l'elenco dei suoi componenti. Nessun nome, documento o indirizzo nella chiave; nessun nuovo modello Schedina.

Nuova tabella `questura_send_reservations`: ID tecnico, struttura, schedina_id, transport_mode, riferimento al tentativo, created_at. Vincolo unico **`(struttura_id, schedina_id, transport_mode)`**; FK restrittive a struttura/tentativo, nessuna FK con cascade dal PMS. Conservare l'identità tecnica dopo minimizzazione impedisce un reinvio futuro e non conserva TXT, base64 o dati anagrafici. Gli ID sono riferimenti tecnici collegabili al PMS, non una dichiarazione di anonimizzazione. Il marker tecnico `identity_reserved_at` del tentativo distingue le prenotazioni nuove dallo storico non ricostruito.

L'hash resta audit/integrità. La chiave secondaria del tentativo comprende trasporto, struttura, ID sorgente ordinati e hash; così **Schedine distinte con byte identici non collidono**. Il vincolo sulle singole sorgenti, indipendente dai byte e dal periodo, protegge anche elenchi sovrapposti.

### Garanzia atomica e confini di errore

Prima del SOAP, una sola transazione breve crea tentativo `in_progress`, riserve ordinate per ID e primo evento. L'INSERT delle riserve è soggetto al vincolo unico DB, senza affidarsi a SELECT→INSERT. Una collisione annulla **l'intero elenco**, il nuovo tentativo e l'evento; risposta applicativa con errore fisso, nessun Send. Due richieste con snapshot diversi non possono ottenere due riserve valide della stessa Schedina/struttura/trasporto. Nessuna transazione applicativa resta aperta durante la rete.

La transazione successiva registra esito/evento. Solo `transmission_excluded=true`, prodotto dal servizio nei casi certi e sanificato come booleano ammesso per `technical_error`/`rejected` Send, libera le riserve **del proprio tentativo** e mette la sua send_key a NULL, atomicamente con l'esito. Lo snapshot storico resta immutabile. Un'eccezione generica del controller non genera questa prova.

| Caso | Riserve / retry |
|---|---|
| Validazione locale fallita, credenziali incomplete | Nessun tentativo/riserve/rete; correggere e riprovare |
| WS Test negativo o errore Test | Nessuna riserva Send; Test non è una trasmissione e non introduce un nuovo requisito di workflow |
| Errore DB prima del trasporto | Rollback completo; retry consentito se la registrazione non è avvenuta |
| Errore apertura client/token/autenticazione prima di Send | `technical_error` con esclusione positiva; esito consolidato e riserve liberate |
| Authentication_Test negativo | `rejected`, ma Send mai invocato; esito consolidato libera il retry |
| Rifiuto completo con esito tipizzato, zero valide e dettaglio coerente di tutte le righe negative | `rejected`, esclusione positiva, riserve liberate; nuovo tentativo esplicito consentito anche con gli stessi byte |
| Solo esito generale Send negativo, senza prova per tutte le righe | `rejected` conservativo, nessuna esclusione positiva; riserve mantenute e nessun reinvio automatico |
| Timeout, risposta Send malformata/incoerente o errore dopo ingresso in Send | `uncertain`; riserve mantenute, modifica PMS non sblocca; prima riconciliare |
| Acquisizione parziale | Tutto l'elenco resta riservato; nessun retry dell'intero batch |
| Crash prima del consolidamento dell'esito, anche se il token era fallito | `in_progress` e riserve rimangono; nessuna liberazione basata su memoria non consolidata |
| Send positivo o finalizzato | Riserva permanente dell'identità tecnica; modifiche e finalizzazione non autorizzano un nuovo Send |
| Simulazione | Riserva soltanto simulation; non occupa live, né dichiara invio o accettazione ufficiale |

Il [Manuale WS ufficiale, Rev. 01, pagine 5–6 e 10](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEWS.pdf), riaperto con solo GET documentale, distingue esito generale, conteggio e dettaglio per riga; Send acquisisce le righe corrette. **Scelta conservativa applicativa:** il solo booleano generale negativo non basta per escludere acquisizioni; per liberare dopo un Send si richiede zero coerente in tutto il dettaglio. Nessuna certificazione del comportamento remoto reale. I codici/testi arbitrari del provider restano sanificati; nessun segreto introdotto nel nuovo metadato booleano.

### Storico, retention e limiti

Nessun backfill automatico. In presenza di Send legacy non simulati del tenant con `identity_reserved_at` NULL, un nuovo Send live è **bloccato prima della rete**, anche se payload/ID erano già minimizzati. Non si ricostruiscono identità da nomi/hash né si autorizza implicitamente un reinvio. Serve audit separatamente autorizzato degli archivi e associazioni tecniche dimostrabili; se l'identità non è recuperabile il blocco conservativo resta. Né una ricevuta giornaliera né il solo cambio di status rimuovono questo limite. Nessun comando o route di sblocco automatico aggiunti.

Retention invariata: payload/base64, riferimenti e copie temporanee degli archivi possono essere eliminati secondo la regola già verificata; PDF/ricevuta e metadati restano. Le riserve tecniche sono separate e non vengono eliminate dalla finalizzazione. PMS e Componenti non modificati dal fix/retention. Nessun dato reale letto o cancellato.

Eventuali correzioni/reinvii legittimi di una Schedina già potenzialmente trasmessa/conclusa richiedono un percorso distinto, esplicito e tracciabile, **non inventato né implementato qui**. Riconciliare l'incerto non crea automaticamente una nuova autorizzazione Send. Il fix riguarda il Send WS osservabile nell'applicazione; non attesta le operazioni manuali esterne non osservate. Non aggiunge il vincolo Test→Send sul medesimo snapshot fra POST distinti: resta un punto del gate da riprendere, senza dichiararne qui la chiusura. Matrice globale, browser, carico multiprocesso, firma PDF e accettazione dell'ente non attestati.

### Prove obbligatorie A–H e regressione

| Requisito | Evidenza |
|---|---|
| A — stessa identità/stessi byte | Secondo POST bloccato nei casi baseline e dal vincolo sorgente |
| B — concluso + modifiche | Test diagnostico originale PASS; ricevuta/finalizzazione non rimuovono la protezione |
| C — incerto + modifiche | Test diagnostico originale PASS; anche partial, risposta incoerente e crash `in_progress` coperti |
| D — Schedina distinta | Send consentito per sorgenti diverse, anche con hash/byte identici |
| E — tenant | HTTP A/B indipendenti; stesso ID tecnico nel vincolo di due tenant non collide. La PK Schedina è globale: nessuna doppia PK PMS fittizia |
| F — concorrenza/doppio submit | UniqueConstraintViolation reale MySQL, una sola riserva; conflitto HTTP e rollback dell'intero elenco senza rete. **Non è una prova di race multiprocesso** |
| G — retry certo | Token/auth, rifiuto completo zero, rollback DB pre-rete; crash DB dopo prova non consolidata mantiene il blocco |
| H — minimizzazione | Riserva integralmente invariata dopo finalizzazione; payload/ID archivio NULL, PDF presente, PMS byte/colonne invariati, overlap bloccato |

**Regressione finale: 114 test / 802 asserzioni PASS**, tutti i 96 casi diagnostici inclusi, più 18 casi nuovi. Intera selezione baseline Questura/Schedine/autorizzazione/Componenti eseguita senza filtri; non è la suite globale del repository con i debiti storici estranei allo scope. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Runtime `/private/tmp/schedine-test-uer80dky`, DB `test_geo_8029433317cbdea679eca51d08a1a918`, MySQL PID41981/porta50311, HTTP PID42000/porta50312. Identità/riconnessione/23 rifiuti override, build e GEO immutabile PASS; processi e risorse fermati/rimossi. **5 guardie negative PASS**, **14 controlli SOAP/sicurezza PASS** con doubles in memoria. Nessun DB/server reale o trasporto SOAP reale.

Comando finale esatto:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php \
 app/Console/Commands/QuesturaExpiredReceipts.php app/Console/Commands/QuesturaArchiveAudit.php \
 config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 database/migrations/2026_10_06_120000_add_questura_retention.php \
 database/migrations/2026_10_06_130000_add_questura_send_reservations.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json --fixtures tests/Isolation/questura-checks.php \
 --phpunit tests/Feature/QuesturaRetentionTest.php tests/Feature/QuesturaCredentialEncryptionTest.php \
 tests/Feature/QuesturaBoundaryTest.php tests/Feature/QuesturaWsContractTest.php \
 tests/Feature/QuesturaLegacyMigrationTest.php tests/Feature/QuesturaIdempotencyTest.php \
 tests/Feature/StrutturaAuthorizationTest.php tests/Feature/SchedinaStoreTest.php \
 tests/Unit/PianoSyncComponentiTest.php tests/Unit/ComponentiImportP1Test.php
```

Comando RED: stessa selezione senza nuova migration e senza QuesturaIdempotencyTest. Guardie standalone: `python3 -B tests/Isolation/test_guards.py`, cinque PASS; lo stesso involucro autorizzato esegue i controlli finali nel checkout effimero. Sintassi **35 PHP PASS**, `git diff --check` PASS, migrazioni storiche/guardie selezionate byte-identiche a HEAD, integrità cataloghi/WSDL PASS. Nessun comando static analysis obbligatorio individuato in composer.json; nessun formatter globale o dipendenza modificati.

Registro intermedio, non sommato al finale: **108/656, 10 errori + 2 failure** nella nuova fixture, perché GeoNazione::firstOrCreate non assegnava l'ID 777 escluso dal fillable; corretta con forceCreate sintetico, nessuna validazione indebolita. **113/789, 1 failure** nel confronto strict di oggetti stdClass riletti: convertiti entrambi in array di valori, confronto integrale mantenuto. **114/804 PASS** prima della revisione conservativa del booleano generale negativo; sostituito quel caso di retry con prova del blocco in assenza del dettaglio, diagnosi originale invariata. Tutti i runtime intermedi rimossi. Il primo script ausiliario di integrità aveva assunto `file` anche nel manifest WSDL, provocando KeyError: corretto l'accesso al nome fisso service.wsdl e verificati gli hash reali, senza modifica degli artefatti.

### File e Git finali

Esattamente **otto file modificati/creati in questa task**, confronto con hash iniziali:

1. `app/Http/Controllers/QuesturaExportController.php`: riserva atomica, conflitti, legacy, consolidamento/release certo e chiave sorgenti.
2. `app/Models/QuesturaTransmission.php`: marker tecnico cast/fillable/immutabile.
3. `app/Services/QuesturaWebService.php`: prova esplicita della fase pre-Send/zero acquisite, errori conservativi.
4. `app/Services/EsitoTrasmissioneQuestura.php`: proiezione chiusa del solo booleano tecnico.
5. `database/migrations/2026_10_06_130000_add_questura_send_reservations.php`: schema aggiuntivo indispensabile, nessun backfill/down distruttivo.
6. `tests/Feature/QuesturaIdempotencyTest.php`: nuovi scenari HTTP/DB/doubles.
7. `docs/questura-audit-2026-10-06.md`: evidenze e limiti della correzione.
8. `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`: appendice M e quadro corrente.

Main, HEAD/origin/main invariati, ahead/behind 0/0, staging vuoto. Totale worktree pertinente **45 file: 19 tracked modificati e 26 untracked**, compresi tutti i precedenti preservati e i due nuovi; nessun git add/commit/push/deploy. Schema applicato solo nei MySQL effimeri; nessuna migration reale, .env, produzione, credenziale o backup reale. Nessuna Schedina PMS cancellata. La futura revisione/rollout/audit legacy e il gate operativo richiedono task autorizzate distinte.

**Chiusura:** CODICE SÌ; TEST SÌ (nuova suite, diagnostici invariati); DOCUMENTAZIONE/MAESTRO SÌ; CONFIGURAZIONE NO; SCHEMA SÌ (nuova migration, solo DB effimero); DATI REALI NO; TEST ESEGUITI SÌ; COMMIT NO; PUSH NO; DEPLOY NO; TRASMISSIONI QUESTURA REALI NO. Solo GET pubblico del manuale. **Fermata qui: PRONTO A RIPRENDERE L'AUDIT QUESTURA.**

---

## Diagnosi precedente — evidenza storica dello STOP, superata dalla correzione P1 sopra

## Gate della singola prova reale controllata — 06/10/2026, verifica successiva

**STATO: NON PRONTO — STOP PER P1 DI IDEMPOTENZA.** Questa evidenza successiva prevale sui verdetti precedenti di preparazione locale. Nessuna trasmissione reale eseguita. Il confronto TXT, la retention e i 94 test della baseline sono preservati: il difetto è riprodotto da due nuovi casi richiesti dal gate più forte sulla stessa Schedina, non dal riesame di problemi già chiusi.

### Git e perimetro

Repository `/Users/jorgeluccitelli/Herd/Schedinedinotifica`, branch `main`; preflight pwd/root/status/staging/untracked e **nuovo fetch origin riuscito**. HEAD e origin/main `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0, staging vuoto. Prima/dopo: 19 tracked modificati e 24 file nuovi preesistenti, 43 complessivi, tutti pertinenti alle fasi Questura. Nessun add/commit/push/deploy o operazione Git distruttiva. Maestro, rapporto e protocollo isolamento già letti integralmente nella conversazione, aggiornamenti successivi riletti; nessuna fonte maestra alternativa introdotta.

Questa task cambia soltanto tre file, verificati mediante hash del worktree prima/dopo:

- `tests/Feature/QuesturaBoundaryTest.php`: due nuovi casi HTTP che esprimono il requisito di blocco della stessa Schedina con payload modificato.
- `docs/questura-audit-2026-10-06.md`: riproduzione, diagnosi, flusso, STOP e proposta.
- `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`: stato ufficiale aggiornato e nuovo P1.

**Nessuna modifica applicativa/configurazione/migration in questa task.** TXT originali del Desktop non riaperti o modificati. Nessun dato personale reale, credenziale reale, .env, backup o DB operativo utilizzati.

### P1-Q1 — Reinvio della stessa Schedina mediante modifica del TXT

**Due manifestazioni di un unico difetto**, non due bug distinti. Prova sul kernel HTTP reale con dati sintetici, credenziali sintetiche e `QuesturaSoapDouble`; il vero servizio prepara token, autentica e chiama esclusivamente il double. Nessun SoapClient con trasporto di produzione.

Riproduzione A:

1. Creare una Schedina sintetica valida per il tenant A.
2. POST Send, timeout del double: tentativo `uncertain`, snapshot conservato.
3. Aggiornare il nome della medesima Schedina, senza riconciliare il primo invio.
4. POST Send sullo stesso periodo: **secondo Send**, nuovo tentativo/hash. Atteso: blocco prima del trasporto.

Riproduzione B:

1. POST Send con risposta semanticamente positiva del double (`sent`, senza inventare accettazione reale).
2. Acquisire la ricevuta sintetica il giorno successivo e finalizzare il tentativo; payload NULL e `finalized_at` valorizzato.
3. Aggiornare il nome della medesima Schedina.
4. POST Send sullo stesso periodo: **secondo Send**. Atteso: blocco della Schedina già associata a un invio concluso, salvo percorso di nuova comunicazione esplicitamente autorizzato e distinto.

**Causa:** `QuesturaExportController::runWsAction` rigenera il TXT dalle Schedine correnti; `storeTransmission` costruisce `send_key` soltanto da modalità trasporto, struttura e SHA-256 del TXT. Una modifica anagrafica o dell'elenco cambia la chiave. Il vincolo DB protegge byte identici, non l'identità della Schedina/alloggiato già riservata a un altro tentativo. Nessuna verifica di sovrapposizione sorgenti prima del SOAP. La finalizzazione elimina correttamente i riferimenti Schedina/componenti dal payload e dagli archivi per minimizzazione: una correzione non deve reintrodurre payload personali permanenti né annullare la retention.

**Impatto P1:** se il trasporto fosse abilitato, una normale correzione PMS o un elenco parzialmente sovrapposto potrebbe comunicare di nuovo gli stessi alloggiati, anche con primo esito incerto. Due operatori con snapshot diversi possono oltrepassare la medesima protezione; questa estensione è deduzione dal vincolo osservato, non test multiprocesso. Nessun P0 dimostrato, nessuna duplicazione effettiva presso l'ente attestata.

**File coinvolti nella proposta:** controller Questura, query/selezione del servizio TXT, modello/schema della prenotazione logica degli invii, servizio retention per metadati tecnici residui, UI per blocco/riconciliazione e test. File non modificati per la correzione.

**Correzione minima proposta, NON implementata:** prima del trasporto riservare atomicamente le sorgenti dell'elenco per tenant e identità stabile della comunicazione, indipendente dal contenuto TXT; vincolo univoco/lock DB che protegga anche elenchi parzialmente sovrapposti e operatori concorrenti. Conservare soltanto un identificatore tecnico opaco adeguato alla minimizzazione dopo finalizzazione, con policy documentata, mai TXT/base64 o nomi permanenti. Bloccare tentativi che intersecano un invio in_progress/uncertain/partial senza riconciliazione e quelli già chiusi senza distinto percorso di nuova comunicazione autorizzata. Unire Test e Send allo snapshot immutabile scelto per la prova, controllandone l'hash prima dell'unico Send. Coprire RED→GREEN dei due casi, sovrapposizioni, concorrenza, tenant e retention. Non basta sostituire la chiave con il periodo: bloccherebbe comunicazioni legittime diverse senza identificare le sorgenti.

Il rapporto precedente aveva già dichiarato la deduplicazione limitata al TXT identico e l'assenza di deduplicazione degli elenchi sovrapposti; ora il nuovo requisito esplicito sulla Schedina è stato riprodotto ed è **bloccante**, non soltanto una raccomandazione operativa.

### Percorso effettivo e macchina a stati

| Passaggio | Controller/service/model, route e tabella | Stato, file e UI |
|---|---|---|
| Schedina PMS | SchedinaController / Schedina + Componenti, circuito schedina; tabelle schedina/componenti | Dati originali PMS, mai cancellati dalla retention Questura |
| Preparazione/validazione | QuesturaTxtExportService + Catalogo; buildPeriodoTxt | Fasi calcolate, non stati DB autonomi; errori prima del trasporto |
| TXT | downloadPeriodo/downloadSchedina; POST generazione; QuesturaExport/questura_exports | generated, UUID .txt privato, SHA/bytes; storico e download |
| WS Test | verifyPeriodo/runWsAction; POST /questura/ws/verify; QuesturaWebService | in_progress → unknown/rejected/technical_error/simulation; payload base64/eventi; nessuna accettazione |
| Pronto invio | Credenziali, validazione e fail-closed | Nessuno stato ready DB né vincolo a un precedente Test: i POST ricalcolano l'elenco corrente; aspetto da includere nella proposta, non nuova prova completata |
| WS Send | sendPeriodo/runWsAction/storeTransmission; POST /questura/ws/send; QuesturaTransmission | in_progress prima della rete, poi sent/partial/rejected/uncertain/technical_error/simulation; P1-Q1 sul reinvio con nuovo hash |
| Risposta e accettazione | QuesturaWebService + EsitoTrasmissioneQuestura | Esito tipizzato/sanificato; accepted sempre false, HTTP 200 non prova accettazione; sent non significa accettato |
| Ricevuta disponibile/acquisita | acquireReceipt / QuesturaWebService::receipt / archiveReceipt; POST /questura/ws/receipt/{id}; QuesturaReceipt | receipt_available solo byte validati; record privato questura_receipts per tenant/giornata, acquisizione e scadenza |
| Associazione/riconciliazione | finalizeDay/finalizeTransmission; POST /questura/ws/{id}/finalizza | Riferimento ricevuta, reconciled_at/by per incerti/parziali; ricevuta giornaliera non identifica da sola ogni riga |
| Chiusura | QuesturaRetentionService::minimizeCopies | finalized_at/payload_deleted_at, TXT eliminato, payload NULL e metadati; PDF conservato |
| Scadenze/audit | QuesturaExpiredReceipts / QuesturaArchiveAudit, comandi readonly | Nessun purge/schedule implicito, nessuna trasmissione |

Nessun job/worker Questura nel percorso attuale, Kernel schedule vuoto. Richieste sincrone, due transazioni brevi del tentativo/esito; SOAP fuori dalla transazione applicativa. Eventi in `questura_transmission_events`, risultato sanificato, viste Questura e form Struttura. Middleware web/auth, CSRF, struttura/membership e manutenzione; nessun log raw previsto dal servizio, SOAP trace false. Nessuna scansione di log reali effettuata.

### Esiti per area e limiti della verifica

| Area | Evidenza corrente |
|---|---|
| TXT | Baseline empirica/sintetica preservata, incluso padding permanenza documentato; nessuna nuova analisi di dati reali |
| WS Test / WS Send | Casi baseline superati; parsing, errori e timeout mocked. Endpoint ufficiale fisso nel codice, credenziali da Struttura encrypted, TLS e timeout configurati; nessuna verifica TLS remota. Snapshot fra POST distinti non vincolato |
| Idempotenza / concorrenza | PASS per hash identico, **FAIL per identità Schedina con hash diverso**; nessun test di carico/worker reali |
| Timeout | Payload incerto preservato e nessun retry automatico; nuova richiesta con hash modificato però non bloccata: P1-Q1 |
| Ricevuta / riconciliazione | Baseline giornaliera, più invii, idempotenza, indisponibilità, integrità e tenant superata; origine/firma reale non verificate, nessuna associazione arbitraria dichiarata |
| Retention / privacy | Baseline di cancellazione TXT/base64 e permanenza PMS superata; nessuna nuova modifica di conservazione, nessun dato reale |
| Autorizzazioni | Casi HTTP auth/CSRF/manutenzione/tenant A-B della selezione superati; matrice globale di tutti i ruoli non certificata |

Dopo il P1 applicato **STOP**: ulteriori ampliamenti della matrice e preparazione operativa non eseguiti. Non attestato completamento di ogni caso della nuova richiesta. Gli esiti positivi della baseline non mascherano il nuovo difetto.

### Test e guardrail

**96 test / 633 asserzioni: 94 casi senza failure/error, 2 failure nei nuovi scenari**, zero errori e nessuna deprecazione PHPUnit segnalata. I 94 casi preesistenti restano tutti superati, asserzioni preservate; la suite ampliata **NON PASS**. Comando esatto: la selezione integrale 94/622 riportata nello storico sotto, immutata nei percorsi e ampliata dai due casi nel medesimo QuesturaBoundaryTest; nessun filtro o test escluso.

Runtime `/private/tmp/schedine-test-uhvrvhjv`, DB `test_geo_151d1bf641e9475b4b061c71533f4318`, MySQL PID30230/porta62712, HTTP PID30235/porta62713. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Identità processi/DB/HTTP, riconnessione, 23 rifiuti override prima delle migrazioni, build asset e GEO immutabile PASS. Launcher exit 1 per i due fallimenti; processi e risorse rimosse. Nessun fallback su DB/server reale. Solo credenziali sintetiche e double SOAP offline.

`python3 -B tests/Isolation/test_guards.py`: **5 PASS**, prove negative senza DB/servizi reali. `git diff --check` e sintassi PHP dei file locali PASS. Nessun comando static analysis obbligatorio individuato in composer.json; nessun formatter applicato. L'involucro finale dei 14 controlli SOAP non è stato eseguito dal launcher perché PHPUnit fallisce prima: **14 PASS resta evidenza della baseline precedente**, non nuovo risultato. Avvisi MySQL di inizializzazione/permessi riguardano il datadir effimero del launcher, nessuna configurazione del servizio operativo modificata.

### Futura prova reale — descrizione, NON autorizzazione

**Bloccata finché P1-Q1 non è corretto e la nuova regressione non passa.** Dopo correzione/revisione, rollout e ambiente autorizzati separatamente, definire un campione lecito e la sua struttura, fissare snapshot/hash, credenziali e canale ufficiale senza mostrarli. Eseguire autenticazione e Test sullo snapshot; se falliscono, zero Send. Richiedere nuova autorizzazione esplicita prima dell'unico Send dello stesso elenco. Registrare tentativo prima della chiamata e risposta semanticamente valida; su timeout nessun secondo Send, solo riconciliazione. Acquisire ricevuta della medesima struttura/giornata quando disponibile (normalmente dal giorno successivo, finestra del protocollo), verificare origine/integrità/firma e associazione, riconciliare se necessario; poi finalizzare TXT/base64, preservare Schedine PMS, conservare ricevuta cinque anni e verificare i metadati. Questa è una sequenza controllata in più momenti, non una transazione atomica o una seconda trasmissione.

**DIFETTI APERTI: P1-Q1 bloccante; nessun P0 dimostrato.** Non richiesta autorizzazione alla prova reale perché il gate non è raggiunto. Codice applicativo invariato rispetto all'inizio della task; lasciati i due test RED come evidenza. Stato generale **BASELINE IN AUDIT**.

---

## Preparazione locale precedente — evidenza storica

## Chiusura tecnica finale — 06/10/2026, terza task

Le conclusioni di questa sezione prevalgono sui due audit storici successivi. La task corrente impone il confronto dei TXT accettati come requisito di chiusura: completato dopo la comunicazione del percorso Desktop. Il precedente blocco per file mancanti è superato.

### 1. Stato

**QUESTURA — TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA nel perimetro locale verificato. BASELINE IN AUDIT invariato.** Nessuna accettazione reale attestata. Scope: Questura, prove sintetiche, integrità/retention e documentazione. Rischio ALTO per trasmissioni, segreti e dati personali, mitigato dal runtime effimero e dal trasporto integralmente sostituito.

### 2. Git preflight

Repository `/Users/jorgeluccitelli/Herd/Schedinedinotifica`, branch `main`. Fetch `origin` autorizzato ed eseguito nella task corrente. HEAD e origin/main `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind **0/0**. Staging vuoto, 42 file locali iniziali pertinenti alle due task Questura preservati. Nessun cambio branch, checkout, stash, reset, pull, merge o rebase. Maestro e rapporto precedente letti; conflitto fra vecchio verdetto locale e nuovo requisito finale risolto mediante questa evidenza successiva.

### 3. TXT accettati: disponibilità

Percorso fornito successivamente dall'utente: `/Users/jorgeluccitelli/Desktop`. Individuati esclusivamente i due nomi già indicati nella richiesta:

- `/Users/jorgeluccitelli/Desktop/1Questura-Nuovo-11-09-2026.txt`: 1018 byte, 6 righe da 168 byte, 5 CRLF intermedi.
- `/Users/jorgeluccitelli/Desktop/1Questura-Nuovo-11-09-2026 (1).txt`: 678 byte, 4 righe da 168 byte, 3 CRLF intermedi.

Analisi locale read-only in memoria, solo esiti tecnici anonimi. File originali invariati, verificati mediante confronto hash prima/dopo senza pubblicarne i digest. Nessuna copia, fixture, stampa di nominativi/date/documenti, modifica o trasmissione. La pregressa accettazione è quella dichiarata dall'utente, non una nuova accettazione o ricevuta verificata da questa task. Il terzo TXT presente sul Desktop non è stato letto.

### 4. Confronto ufficiale / empirico / implementazione

| Proprietà | Contratto e implementazione verificabili | Confronto empirico |
|---|---|---|
| Lunghezza | 168 caratteri, 168 byte nel sottoinsieme ASCII implementato | PASS nei due campioni |
| Charset | Output sottoinsieme ASCII di UTF-8, senza BOM | PASS nei due campioni |
| Newline | CRLF fra record; nessun CRLF finale | PASS nei due campioni |
| Padding e offset | Spazi a destra; offset/lunghezze dettagliati nel capitolo TXT storico e verificati dalle fixture | PASS nei due campioni |
| Date | gg/mm/aaaa, validazione stretta e round-trip | PASS nei due campioni |
| Codici | Cataloghi ufficiali datati con hash; mai ID GEO o ISTAT | PASS nei due campioni |
| Tipi/componenti | 16/17/18; 19/20 con ultime 34 posizioni blank, capo prima dei membri | PASS nei due campioni |
| Nomi/documenti | Accenti supportati normalizzati; numero documento ASCII preservato, nessun troncamento | PASS nei due campioni |

Gerarchia per conformità: fonte ufficiale e contratto SOAP, poi evidenza empirica dei TXT, poi implementazione. Confronto empirico completato: entrambi ASCII compatibile UTF-8, nessun BOM/terminatore finale/LF isolato; padding destro di nomi/documenti, date reali nel formato previsto, sesso, codici Stato/cittadinanza/documento/Comune e provincia coerenti con cataloghi pubblici. Capofamiglia/familiare e capogruppo/componente ordinati, date arrivo e permanenza uguali al rispettivo capo, ultime 34 posizioni dei membri blank. Valori di permanenza validi dopo interpretazione numerica.

**Differenza osservata:** permanenza allineata a destra con spazio nei campioni (`" 1"/" 2"`) contro zero nell'implementazione (`"01"/"02"`). Non sono byte identici: il manuale prescrive due caratteri e massimo 30, senza imporre spazio o zero. Nessun bug o rifiuto del formato attuale dimostrato; mantenuta la forma canonica testata senza modificare il codice sulla sola differenza empirica. L'accettazione dello zero padding dal servizio reale rimane da osservare nella prova controllata. Il primo controllo lessicale `isdigit()` senza stripping aveva classificato lo spazio come anomalia, poi corretto nell'analisi, senza modifiche applicative.

**Limiti dei campioni:** nessun ospite singolo, nascita estera o carattere multibyte; queste combinazioni restano coperte dalle fixture, senza riscontro empirico nei due file. Le date storiche sono state verificate nel formato/calendario, non confrontate con la finestra oggi/ieri al tempo di questa lettura. Nessuna correzione del tracciato fondata su congetture. [Manuale WS Rev. 01 del 24/01/2022](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEWS.pdf) e [guida Alloggiati](https://alloggiatiweb.poliziadistato.it/portalealloggiati/Download/Manuali/MANUALEALBERGHI.pdf) riaperti in sola lettura; tabella 1 del Manuale WS conferma 168 e CRLF intermedio. WSDL locale ufficiale con hash verificato e serializer offline. Riapertura WSDL via browser documentale non riuscita: nessuna nuova acquisizione attestata. Nella tabella descrittiva del manuale compaiono nomi minuscoli degli errori, mentre esempi XML e WSDL usano `ErroreCod/ErroreDes/ErroreDettaglio`: il codice e i doubles seguono il contratto XML formale. Nessuna discrepanza applicativa dimostrata su questi nomi.

### 5. Correzioni e RED → GREEN

Difetto riprodotto prima del fix: un disk double scrive 12 byte e restituisce false; il PDF parziale resta senza record ricevuta. **17 test / 112 asserzioni / 1 failure**. Runtime `/private/tmp/schedine-test-j638jug8`, DB `test_geo_5847911aa0e28ef6e91c50f02477c007`, MySQL PID19568/porta59864, HTTP PID19574/porta59865; risorse rimosse.

Fix minimo in `QuesturaRetentionService::archiveReceipt`: scrittura inclusa nel blocco di compensazione, presenza e hash dei byte scritti verificati prima dell'INSERT; compensazione con controllo effettivo della cancellazione, inclusa collisione sul vincolo univoco. Se lo storage impedisce la compensazione, errore 503 senza falsa acquisizione; audit read-only rileva l'orfano. Nessuna modifica ai normali flussi PMS.

Secondo difetto dimostrato nella revisione finale: un evento Eloquent `created` solleva errore dopo l'INSERT; la compensazione elimina il PDF ma il record resta. **21 test / 137 asserzioni / 1 failure**, runtime `/private/tmp/schedine-test-f94i3qwq`, DB `test_geo_5b5d91e27a920fa59dcc2fc8cf278111`, MySQL PID27232/porta61892, HTTP PID27237/porta61893; risorse rimosse. Fix minimo: creazione ricevuta in transazione breve, rollback dell'INSERT prima della compensazione. Nessuna transazione durante SOAP, nessuna dichiarazione di atomicità DB/filesystem. Prova di errore prima e dopo INSERT, scrittura parziale e compensazione impossibile inclusa nella regressione.

Registro dei successivi fallimenti: **90/565, tre failure** dovute al confronto fra Cliente appena creato e Cliente riletto con colonne default; corretta la fixture rileggendola prima, mantenendo il confronto integrale. **92/599, un errore** perché MySQL rifiuta la data impossibile nella colonna DATE; per quel caso la sola sorgente viene fornita in memoria al controller, mantenendo boundary/mapping reali. Ulteriore **93/607, una failure**: sostituzione della sorgente effettuata dopo la costruzione del controller; corretta la fixture iniettandola prima della prima richiesta. Nessuna guardia o schema indeboliti. Runtime rispettivi `/private/tmp/schedine-test-01_ogzjp` e `/private/tmp/schedine-test-8uyl8r54`, entrambi rimossi.

### 6. Ciclo sintetico completo

Prove del ciclo con Struttura/credenziali/Schedina sintetiche, mapping reale, export privato byte-esatto, Test, snapshot prima del SOAP, Send, esito, ricevuta il giorno successivo, finalizzazione e metadati. Tre scenari: successo, timeout e crash nel salvataggio DB successivo a Send. PDF scaricato byte-identico; TXT/base64 eliminati alla finalizzazione; hash e metadati rimangono. Cliente PMS e Schedina confrontati integralmente prima/dopo: invariati. Creazione e aggiornamento HTTP di singolo/famiglia/gruppo verificati nella regressione SchedinaStore separata. Non è una prova browser né una trasmissione reale.

### 7. UI

Rendering Blade verificato dalle prove esistenti: credenziali vuote, indicazione configurazione, stato temporaneo e cancellato, acquisizione/scadenza della ricevuta. Controlli backend per conferma manuale e riconciliazione obbligatorie. Nessuna acceptance visuale/browser attestata.

### 8. Credenziali

Encrypted/hidden, nessun valore in DOM o flash, input vuoto conserva il ciphertext, aggiornamento nuovo cifrato, legacy riconoscibile indecifrabile blocca la migrazione. Cestino Struttura minimizzato. Prove e migrazioni soltanto su fixture; nessun segreto o APP_KEY reale letto. Utente, Password, WSKEY restano gli unici campi Questura ordinari.

### 9. TXT manuale

Generazione/download non equivale a trasmissione. Conferma esplicita di elenco/giornata e ricevuta verificata prima di finalizzare. Nessun PDF caricato arbitrariamente diventa prova ufficiale. Archivio byte-esatto, download senza rigenerare, 410 dopo finalizzazione. Conformità nelle fixture; confronto empirico completato con differenza di padding numerico documentata, senza incompatibilità dimostrata.

### 10. WS Test

Doubles con token, Authentication_Test e Test conformi al contratto. Nessun Send nel percorso Test; nessuna accettazione, ricevuta o cancellazione prodotta dalla sola verifica. Serializer SOAP nativo offline con WSDL locale, `__doRequest` sostituito senza chiamare il trasporto padre.

### 11. WS Send

Tentativo, elenco esatto e hash persistiti prima della chiamata. Durante Test/Send il livello transazionale torna a quello della fixture PHPUnit, senza transazione applicativa trattenuta. Esiti distinti e sanificati; `accepted=false`; nessun contatore di invio confermato o retry automatico. Simulazione non confusa con invio live.

### 12. Timeout e crash dopo Send

Timeout → uncertain con payload conservato. Errore DB dopo la risposta Send → in_progress con evento/snapshot iniziale conservato. Il secondo POST dello stesso elenco è respinto senza secondo Send. Nessuna ipotesi automatica di riuscita/fallimento. Recupero: consultare esito presso l'ente con operatore autorizzato, acquisire ricevuta della stessa struttura/giornata, riconciliare l'intero elenco, quindi POST di finalizzazione con attestazione. Ricevuta da sola non cancella uncertain/in_progress; decisione e attore sono tracciati. Non rigenerare/modificare l'elenco per aggirare la deduplicazione.

### 13. Ricevuta

Risposta base64Binary gestita come byte PDF dal serializer SOAP; limite 10 MB, intestazione/EOF, hash, dimensione, MIME e storage privato. Giornata/tenant controllati, download identico senza rete. Non è verifica della firma o parser PDF completo. Origine remota/TLS e PDF reale saranno verificati nella futura prova autorizzata.

### 14. Finalizzazione

Preserva ricevuta e metadati, elimina TXT/base64/risultati personali/eventi legacy/riferimenti agli ospiti. Retry sicuro se unlink è avvenuto prima del fallimento DB; nessun timestamp di completamento se la cancellazione fallisce. Non modifica Cliente PMS, Schedina, Componenti o altri archivi del gestionale. Riconciliazione esplicita per incerti/parziali; idempotenza e giornata multi-invio provate.

### 15. Retention cinque anni

Scadenza da acquisizione certa + cinque anni di calendario alla stessa ora, senza overflow bisestile: scelta implementativa prudenziale già documentata. Prove data normale e 29 febbraio, istante precedente/esatto/successivo, isolamento tenant. Comando scadenze deterministico in sola lettura; nessun purge o cron. [Nota ufficiale del Garante](https://garanteprivacy.it/web/guest/home/docweb/-/docweb-display/docweb/10244289) riaperta: confermati cancellazione delle copie trasmesse alla ricevuta e conservazione quinquennale. Backup, repliche e restore restano responsabilità infrastrutturali; nessuna modifica o garanzia di cancellazione fisica SSD.

### 16. Multi-tenant

Matrice A/B della selezione: selezione struttura forgiata, credenziali, generazione, storico, download TXT/payload/PDF, acquisizione e finalizzazione. Percorsi alterati e ricevuta di altra giornata respinti. Componenti cross-tenant rifiutati dalla boundary; query operativa filtra per tenant. Nessun nuovo privilegio: eccezione Admin legacy invariata. Non certifica l'intera applicazione multistruttura.

### 17. Duplicati e concorrenza

Due POST Send identici producono un solo Send e un solo tentativo. Vincolo univoco DB su modalità trasporto/struttura/hash provato anche con doppio inserimento; collisione gestita senza retry SOAP. Non è una prova di carico multiprocesso né idempotenza remota. Elenchi diversi ma sovrapposti richiedono decisione operativa: non esiste retry automatico.

### 18. Failure path e archivi legacy

Comando nuovo **`questura:audit-archivi STRUTTURA_ID`**, sola lettura, limitato al tenant richiesto. Segnala TXT verificato, file assente, hash assente/diverso, percorso invalido, ricevuta non verificata e PDF senza record. Mostra ID e hash tecnici, nessun contenuto o nome personale. Mai eseguito su dati reali in questa task.

Errore prima/dopo INSERT → rollback del record e compensazione del PDF, nessuna falsa acquisizione. Delete impossibile → 503, nessuna falsa acquisizione e orfano rilevabile dall'audit; una nuova acquisizione autorizzata usa nuovamente Ricevuta, mai Send. Per recupero operativo: ripristinare storage, identificare l'orfano mediante hash del percorso, confrontare DB e file in accesso riservato, riacquisire/verificare la ricevuta tramite canale autenticato e autorizzare separatamente la rimozione della sola copia senza riferimenti. Nessuna promozione di un file arbitrario a ricevuta né cancellazione automatica. Un'interruzione di processo fra write e INSERT segue lo stesso audit; DB/file non sono atomici.

Hash legacy: calcolato dall'audit soltanto sui byte esistenti in percorso privato valido; file mancante → nessun hash. Download resta 409 senza hash archiviato. Nessun backfill implementato/eseguito. Eventuale backfill futuro richiede autorizzazione, byte presenti, tenant/path/integrità/provenienza verificati, lock e confronto dei byte immediatamente prima della scrittura; non ricostruire mai l'hash dalla Schedina attuale o da filename/data. PDF legacy senza acquisizione certa rimane non verificato, senza date inventate.

### 19. Sicurezza

Auth, CSRF, manutenzione e fail-closed verificati nello scope. Configurazione trasporto false per default e nessun toggle ordinario; in testing il client reale non viene aperto. I doubles dei test sostituiscono integralmente SOAP. Storage privato, hash, path tenant, symlink/traversal esclusi, risultati sanificati, segreti assenti dalle copie verificabili. Nessun audit globale di log/backup/dati reali.

### 20. Test eseguiti

**PASS finale: 94 test / 622 asserzioni**, **5 guardie PASS**, **14 controlli SOAP/sicurezza PASS**, nello stesso runtime isolato. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Runtime `/private/tmp/schedine-test-nc16nkjw`, DB `test_geo_4688dcc983bba0532095d925df46f447`, MySQL PID27916/porta62061, HTTP PID27921/porta62062. Identità processi/DB/HTTP, riconnessione, 23 rifiuti di override prima delle migrazioni, build asset e GEO immutabile PASS. Processi fermati e risorse rimosse. Sintassi **33 file PHP PASS**; hash cataloghi/WSDL, migrazioni storiche e guardie byte-identiche a HEAD, diff whitespace PASS. Prove sovrapposte non sommate ai risultati delle fasi precedenti.

Selezione completa 83/443 precedente preservata e ampliata, senza eliminare asserzioni. Comando integrale riproducibile:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php \
 app/Console/Commands/QuesturaExpiredReceipts.php app/Console/Commands/QuesturaArchiveAudit.php config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 database/migrations/2026_10_06_120000_add_questura_retention.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json --fixtures tests/Isolation/questura-checks.php \
 --phpunit tests/Feature/QuesturaRetentionTest.php tests/Feature/QuesturaCredentialEncryptionTest.php \
 tests/Feature/QuesturaBoundaryTest.php tests/Feature/QuesturaWsContractTest.php \
 tests/Feature/QuesturaLegacyMigrationTest.php tests/Feature/StrutturaAuthorizationTest.php \
 tests/Feature/SchedinaStoreTest.php tests/Unit/PianoSyncComponentiTest.php \
 tests/Unit/ComponentiImportP1Test.php
```

In questa prosecuzione sono cambiati soltanto i due documenti: nessun nuovo test Laravel eseguito e nessun nuovo codice applicativo/test modificato; gli esiti 94/622 restano quelli del runtime descritto. Tutte le prove Laravel nel launcher, MySQL/filesystem effimeri e doubles. `--fixtures tests/Isolation/questura-checks.php` esegue guardie e controlli SOAP nello stesso checkout attestato; nessun test diretto sul progetto operativo.

### 21. Regressione

Create/update Schedina singolo/famiglia/gruppo, autorizzazioni Struttura, PianoSyncComponenti e ComponentiImport P1 rieseguiti. Nessuna regressione dimostrata nella selezione. Suite globale e acceptance browser non eseguite; debiti storici fuori scope non riclassificati.

### 22. File

Nella terza task: aggiornati `app/Services/QuesturaRetentionService.php`, `tests/Feature/QuesturaRetentionTest.php`, `tests/Feature/QuesturaBoundaryTest.php`, rapporto e Maestro; nuovo `app/Console/Commands/QuesturaArchiveAudit.php`. Il servizio/test erano già nuovi non versionati dalle task precedenti. Complessivamente **19 tracked modificati e 24 nuovi file**, tutti pertinenti, 43 totali. Inventari delle due fasi precedenti conservati sotto. Nessuna modifica AGENTS, dominio Schedina/Cliente, GEO, ISTAT o infrastruttura permanente.

### 23. Migrazioni

Nessuna nuova migration in questa terza task. Restano le tre aggiuntive del 06/10/2026, applicate soltanto ai MySQL effimeri. Migrazioni storiche byte-identiche a HEAD. Nessuna migration reale o backfill reale.

### 24. Git finale

Main, HEAD/origin/main uguali al preflight, staging vuoto. Diff completo/riepilogo/elenco/cached e whitespace controllati; sintassi PHP, hash dei cataloghi e WSDL, migrazioni storiche e guardie confrontati con HEAD. Nessun add, commit, push, deploy o operazione Git distruttiva. Nessun dato reale introdotto nei file locali.

### 25. Limiti residui

Confronto dei due TXT completato; nessuna incompatibilità dimostrata, differenza di padding e copertura limitata dei campioni esplicite. Prima dell'uso reale: revisione, APP_KEY/backup e rollout autorizzato, audit legacy autorizzato, permessi e retention/restore infrastrutturali, controllo tempestivo della ricevuta, credenziali/abilitazione e TLS reali, accettazione e PDF/firma dell'ente. Alfabeti non supportati sono respinti esplicitamente. Storage non WORM e ricevuta giornaliera non prova individualmente le righe: servono gestione e riconciliazione operative. Nessun bug dimostrato viene trasformato in un prerequisito di ambiente; i difetti di scrittura parziale e record residuo dopo errore successivo a INSERT sono corretti e provati.

### 26. Verdetti

| Area | Verdetto corrente |
|---|---|
| A — TXT manuale | **TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA**, confronto completato entro i limiti documentati |
| B — WS Test | **PASS nelle prove locali**, attivazione/prova reale non eseguite |
| C — WS Send | **PASS nelle prove locali**, incluso crash/no reinvio; nessuna accettazione reale |
| D — Ricevuta | **PASS nelle prove locali**, inclusa compensazione/integrità; origine reale/firma da provare |
| E — Retention | **PASS nella regola implementata**, file attivi/DB; backup operativi separati |
| F — Credenziali | **PASS nei percorsi e fixture**, audit/rollout reale non eseguiti |

**CICLO QUESTURA COMPLETO: TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA**, nel perimetro locale documentato. Confronto empirico completato sui due campioni e regressione precedente 94/622 PASS; la differenza di padding è esplicita e non prova accettazione del nuovo output. Nessuna accettazione reale o compatibilità universale dichiarata. Progetto **BASELINE IN AUDIT**.

---

## Chiusura retention — storico della seconda task

### Chiusura retention e preparazione alla trasmissione — stato della seconda task

Questo aggiornamento del 06/10/2026 sostituisce il precedente requisito di **storico permanente del payload**. Le sezioni dell’audit iniziale, conservate più sotto come storico, non descrivono la policy corrente.

### Stato Questura

**TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA**, limitatamente ai flussi e alle fixture verificati, senza accettazione reale attestata e senza attivazione del trasporto. Il progetto resta **BASELINE IN AUDIT**; il modulo generale resta PARZIALE perché la prova operativa non è stata eseguita. I TXT accettati non sono usati in questa task e non bloccano la correzione retention.

### Fonte retention e regola implementata

Fonti ufficiali riverificate in sola lettura il 06/10/2026:

- [Garante, Doc-Web 10244289](https://garanteprivacy.it/web/guest/home/docweb/-/docweb-display/docweb/10244289), nota collegata al comunicato 29/04/2026.
- [DM 07/01/2013, GU 14 del 17/01/2013](https://www.gazzettaufficiale.it/eli/id/2013/01/17/13A00360/sg), testo originario.
- [DM 16/09/2021, art. 1 lettera e, GU 246 del 14/10/2021](https://www.gazzettaufficiale.it/atto/serie_generale/caricaArticoloDefault/originario?atto.codiceRedazionale=21A06000&atto.dataPubblicazioneGazzetta=2021-10-14&atto.tipoProvvedimento=DECRETO), introduzione dell’art. 4-bis comma 2.

La regola riguarda cancellazione delle copie digitali trasmesse alla generazione della ricevuta, e conservazione della ricevuta per cinque anni. I cinque anni sui sistemi ministeriali non autorizzano il PMS a conservare il payload per la stessa durata.

Il testo consultato prescrive cinque anni per la ricevuta, ma non indica un campo applicativo o un diverso dies a quo esplicito. **Scelta implementativa documentata:** `retained_until = acquired_at + 5 anni di calendario`, con `addYearsNoOverflow`, alla medesima ora; una data di acquisizione certa non anticipa la generazione della ricevuta. Non si usa arrivo dell’ospite, data di download del TXT o 365×5 giorni; `remote_date` resta distinta, come data della comunicazione. Il riferimento all’acquisizione è una scelta prudenziale del software, non una citazione di una prescrizione testuale sul dies a quo. La generazione remota non è osservabile prima del recupero: prima della prova va previsto un controllo operativo tempestivo delle ricevute disponibili.

### Architettura retention

**Prima della ricevuta:** file TXT privato, payload base64, riferimenti schedine/componenti, hash e contesto sono copie operative **temporanee**. Download non significa trasmissione. Non sono state introdotte scadenze arbitrarie per payload non riconciliati.

**Dopo la ricevuta:** `QuesturaRetentionService` verifica integrità del PDF, struttura e giornata; finalizza i Send certi della stessa giornata e le copie identiche del medesimo hash. Per uncertain/partial/in_progress è necessaria una riconciliazione esplicita dell’operatore. Non si inventano ID remoti o prove per singola riga.

**Ricevuta cinque anni:** file privato separato, struttura, data comunicazione, acquisizione, scadenza, filename/MIME/bytes/SHA-256, percorso e futuro `purged_at`. UI con data di acquisizione/scadenza, download storico senza rete.

**Metadata residui:** ID interno, struttura, utente operativo, data/ora tecnica, modalità/stato, hash non reversibile e dimensione del contenuto eliminato, quantità, riferimento ricevuta, finalizzazione/cancellazione ed eventuale attore/timestamp della riconciliazione. L’utente operativo serve alla tracciabilità della decisione di finalizzazione; non vengono aggiunte informazioni sugli ospiti.

### TXT manuale

Generazione → archivio temporaneo → download → upload manuale esterno effettuato dall’operatore → indicazione della giornata e attestazione della comunicazione dell’elenco esatto → acquisizione della ricevuta giornaliera tramite contratto Ricevuta oppure uso della stessa ricevuta già archiviata → finalizzazione.

POST `questura.txt.receipt`, con CSRF e autorizzazione della struttura. La conferma obbligatoria non sostituisce il PDF ufficiale: senza ricevuta valida il file rimane. L’applicazione non dichiara trasmesso un TXT perché scaricato; la ricevuta giornaliera da sola non identifica il singolo elenco, quindi l’associazione manuale richiede attestazione registrata dell’operatore. Nessun upload arbitrario di un PDF viene trattato come ricevuta ufficiale. Per recuperare una ricevuta non ancora archiviata servono credenziali WS della medesima struttura e disponibilità nella finestra del protocollo; un PDF già archiviato può essere riutilizzato anche oltre la finestra di recupero remoto.

TXT: **CONFORME ALLA SPECIFICA UFFICIALE NELLE PROVE LOCALI; ACCETTAZIONE REALE NON ANCORA VERIFICATA.** Ambito effettivo: fixture documentate, mapping pubblico del 06/10/2026 e alfabeti supportati; nessuna attestazione di resa universale Unicode.

### Web Service e ricevuta

Restano GenerateToken, Authentication_Test, Test, Send, Ricevuta e i parametri ufficiali provati offline nella selezione precedente. `Send` non viene convertito in accettazione definitiva e non vengono incrementati contatori di invio confermato. Dopo acquisizione della ricevuta, tutti i Send `sent`/live della struttura e giornata vengono verificati e minimizzati; i record legacy senza prova di trasporto live vengono lasciati per verifica separata.

Un timeout o un crash mantiene uncertain/in_progress e conserva il payload. Acquisizione della ricevuta non lo cancella automaticamente: POST `questura.ws.finalize` richiede attestazione di riconciliazione dell’intero elenco. Partial segue lo stesso percorso e non genera retry automatici. Altri invii e altre giornate non sono implicitamente dichiarati riconciliati. Una ricevuta comune a una giornata non diventa prova individuale di ciascuna riga.

Ricevuta: acquisizione POST distinta, date del protocollo, bytes da risposta tipizzata, PDF e hash privati. Validazione applicativa: esito SOAP, MIME, intestazione/terminatore, limite dimensioni, hash e byte_size; non è una verifica della firma digitale né un parser PDF completo. L’origine ufficiale deriva dal canale SOAP autenticato e TLS verificato; nelle prove quel canale è integralmente sostituito da doubles. Nessuna ricevuta reale o firma è stata acquisita/verificata.

### Finalizzazione e cancellazione dei dati trasmessi

Servizio dedicato, nessuna logica di cancellazione nella Blade. Lock DB su ricevuta/snapshot, controlli di tenant/data/hash e preflight di tutti i percorsi prima del primo unlink. Percorsi ammessi solo sotto `questura/struttura_ID/`, esclusione di traversal, backslash, NUL e symlink; PDF ricevuta esclusi dalla cancellazione TXT.

- **File:** eliminazione effettiva dei TXT selezionati, con controllo dell’esito e dell’assenza successiva. Un errore abortisce senza timestamp di cancellazione completata. File già mancante ammesso per un retry dopo un crash fra unlink e commit DB; la ricevuta e l’hash archiviato devono comunque essere coerenti.
- **DB:** `payload`, `result`, riferimenti schedine/componenti, date di soggiorno, messaggi/dettagli remoti e percorso TXT sono NULL dopo la finalizzazione; filename TXT sostituito da nome tecnico interno. Nessuna base64 residua del TXT. Anche le copie di Test/verify con lo stesso hash vengono minimizzate.
- **Eventi:** eventuali risultati legacy sono ridotti a un indicatore tecnico; aggiunto evento di finalizzazione senza informazioni sugli ospiti. Questa minimizzazione autorizzata sostituisce l’immutabilità assoluta del contenuto personale; CRUD Eloquent e UI restano senza update/delete degli archivi.
- **Metadata:** hash, dimensione, quantità, stato tecnico e ricevuta restano. Snapshot diverso o uncertain non riconciliato conservato temporaneamente per la propria finalizzazione; non è dichiarato archivio permanente.

Nessuna cancellazione o aggiornamento di Cliente, Prenotazione, Schedina, Componenti, soggiorni o dati PMS da parte del servizio. Una modifica della Schedina non altera il contenuto archiviato da verificare. File e DB non costituiscono una transazione atomica: se il DB fallisce dopo unlink, il DB non dichiara completamento; un retry con ricevuta verificata minimizza le copie residue. Per una giornata con più invii ogni finalizzazione è una transazione breve: risultati già completati restano tracciati e il retry riprende quelli pendenti.

### Retention ricevuta e purge

`acquired_at`, `retained_until`, `purged_at` espliciti. Il comando **in sola lettura**:

```sh
php artisan questura:ricevute-scadute STRUTTURA_ID --at=YYYY-MM-DD
```

seleziona deterministicamente ricevute con scadenza raggiunta e non già purgate, limitate alla struttura indicata, mostrando solo metadati. Senza `--at` usa il tempo corrente. Nessuna cancellazione, nessuna schedulazione e nessun purge automatico introdotti. `purged_at` resta NULL; un futuro purge effettivo richiede procedura operativa autorizzata. Ricevute legacy senza data certa di acquisizione/scadenza non ricevono date inventate e richiedono un audit autorizzato prima della migrazione dei metadati.

### Backup

La cancellazione applicativa rimuove file attivi e copie DB selezionate, **non** copie già presenti in backup, snapshot infrastrutturali o repliche storiche. Retention, accessi, scadenza e ripristino dei backup devono essere coerenti con la policy privacy; prima dell’uso reale va configurato il processo infrastrutturale e impedita la reintroduzione di copie finalizzate da un restore. Nessun backup reale è stato letto, modificato o cancellato. Nessuna promessa di sovrascrittura fisica di blocchi SSD/journal.

### Credenziali, Dati Struttura e multi-tenant

**PASS nelle fixture.** Conservati cast encrypted/hidden, DOM vuoto, esclusione flash/trimming, migration legacy e arresto su involucro riconoscibile non decifrabile, minimizzazione Cestino e guardrail fail-closed. UI normale: **Utente Alloggiati Web, Password, WSKEY**; nessun codici/PUK/toggle prova. Nessun valore reale letto.

**Multi-tenant PASS nella selezione A/B.** Operazioni tramite membership backend già esistente, eccezione admin preesistente preservata, nessun nuovo potere. Finalizzazione, acquisizione e download non possono raggiungere struttura B da A. Azioni mutanti POST/auth/CSRF, download GET senza rete e 410 quando il payload è stato eliminato. Traversal e ricevuta alterata/giornata diversa respinti.

### Test e regressione Schedine

Risultato finale: **83 test / 443 asserzioni PASS**, inclusa tutta la selezione precedente; **5 guardie PASS** e **14 controlli SOAP/sicurezza PASS**, eseguiti nello stesso checkout isolato. Runtime `/private/tmp/schedine-test-dg9l__vv`, DB `test_geo_dcf0e58db26683d41b31355f7beb2158`, MySQL PID 17235 / porta 59258, HTTP PID 17240 / porta 59259; attestazione identità/riconnessione e 23 rifiuti override PASS, build asset e GEO immutabile PASS. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Processi fermati e directory rimossa. Comando integrale, includendo tutta la precedente selezione 68/337 e i nuovi test:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php \
 app/Console/Commands/QuesturaExpiredReceipts.php config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 database/migrations/2026_10_06_120000_add_questura_retention.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json --fixtures tests/Isolation/questura-checks.php \
 --phpunit tests/Feature/QuesturaRetentionTest.php tests/Feature/QuesturaCredentialEncryptionTest.php \
 tests/Feature/QuesturaBoundaryTest.php tests/Feature/QuesturaWsContractTest.php \
 tests/Feature/QuesturaLegacyMigrationTest.php tests/Feature/StrutturaAuthorizationTest.php \
 tests/Feature/SchedinaStoreTest.php tests/Unit/PianoSyncComponentiTest.php \
 tests/Unit/ComponentiImportP1Test.php
```

Controlli finali: sintassi **30 file PHP PASS**, hash cataloghi/WSDL PASS, migrazioni storiche e guardie inalterate rispetto a HEAD, `git diff --check` PASS, staging vuoto e scope dei **42 file locali modificati/nuovi** pertinente (include le modifiche della task precedente). `git diff --stat` riguarda 19 file tracked; i 23 nuovi file restano untracked per revisione, nessuno aggiunto allo staging.

L’involucro `questura-checks.php` è attestato prima di invocare `python3 -B tests/Isolation/test_guards.py` e `php tests/Safety/questura_security.php` nel checkout temporaneo. I soli figli delle prove negative delle guardie vengono privati dell’identità `ISOLATED_*`, perché devono essere rifiutati prima del bootstrap; nessuna guardia è modificata o indebolita. Il controllo SOAP resta interamente in memoria con doubles.

Copertura A–M richiesta: nessuna ricevuta/no cancellazione; TXT solo scaricato; uncertain preservato; ricevuta valida; file realmente rimosso; base64/snapshot NULL; metadata e ricevuta preservati; cinque anni e bisestile; tenant A/B; idempotenza; errore di delete; retry con file mancante; Schedina modificata invariata dalla finalizzazione. In aggiunta: eventi minimizzati, copie Test identiche, ricevuta alterata/date discordanti, simulazione non assimilata a invio, PDF indisponibile, CSRF 419 e finalizzazione di più Send della stessa giornata senza toccare uncertain o altra giornata.

Registro fallimenti della nuova task: 80 test/379 asserzioni, 5 errori e 2 failure, tutti dovuti a `dal`/`al` non nullable negli export; corretto nella nuova migration. Successivo 80/422, un errore perché il test precedente si aspettava risposta PDF non streamed; mantenuta la risposta byte-identica esistente nel nuovo download protetto. Successivo 82/437 PASS applicativo, ma involucro delle guardie fallito perché i processi negativi ereditavano identità autorizzata; correzione limitata all’involucro. Successivo 82/437 e 5 guardie PASS, ma controllo SOAP in memoria fermato per constructor del controller con nuova dipendenza non fornita; aggiornato solo il double, senza eliminare asserzioni. Tutti i runtime fermati e rimossi; prove sovrapposte non sommate.

**Regressione Schedine:** selezione create/update singolo/famiglia/gruppo, PianoSyncComponenti e ComponentiImport P1 interamente rieseguita. Normale dominio, controller, schema e lifecycle Schedina non modificati. Suite globale e browser fuori scope, nessuna certificazione generale.

### File della nuova task e migration

Nuovi: `app/Services/QuesturaRetentionService.php`, `app/Console/Commands/QuesturaExpiredReceipts.php`, `database/migrations/2026_10_06_120000_add_questura_retention.php`, `tests/Feature/QuesturaRetentionTest.php`, `tests/Isolation/questura-checks.php`.

Aggiornati: `app/Http/Controllers/QuesturaExportController.php` (finalizzazione e archiviazione delegati, flusso manuale e download protetto), `app/Models/QuesturaReceipt.php` (cast date retention), `app/Models/QuesturaExport.php` e `QuesturaTransmission.php` (cast e ulteriore blocco CRUD finalizzato), `resources/views/questura/index.blade.php` (stati temporanei, conferme e date ricevuta), `routes/web.php` (tre route), `tests/Safety/questura_security.php` (solo nuova dipendenza nel double), questo rapporto e Maestro. Tutte le precedenti modifiche locali Questura preservate; inventario completo della prima task nel capitolo storico sotto.

**Una sola nuova migration in questa task**, aggiuntiva alle due già presenti: campi retention/finalizzazione, riferimento ricevuta, timestamp riconciliazione, FK tentativo nullable per ricevuta manuale, path e date periodo export nullable per minimizzazione. Nessun backfill di dati reali; down non distruttivo. **Nessuna migration storica modificata.** Schema eseguito soltanto nel MySQL effimero dei test.

### Git, limiti residui e verdetto

Branch main; HEAD e riferimento locale origin/main `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0. Nessun nuovo fetch in questa task: riferimento remoto verificato dal fetch della fase precedente, non attestazione istantanea del server remoto. Worktree con correzioni locali e file nuovi untracked pertinenti, staging vuoto; nessun add/commit/push/deploy.

Limiti reali prima dell’attivazione: revisione locale e backup/APP_KEY, rollout autorizzato delle tre migrazioni; audit delle ricevute/payload legacy senza hash o acquisizione certa; controllo tempestivo delle ricevute e riconciliazione degli esiti incerti/parziali; configurazione privacy dei backup; alfabeti non supportati; confronto TXT accettati separato; verifica autenticazione/TLS, ricevuta PDF/firma e accettazione con l’ente nella futura prova. Deduplicazione Send identico preservata, nessuna idempotenza remota o deduplicazione generalizzata di elenchi parzialmente sovrapposti; copie operative diverse restano per la propria riconciliazione. Storage non WORM. Nessun limite qui descritto è nascosto mediante un’etichetta di accettazione reale.

| Area | Verdetto circoscritto alla preparazione locale |
|---|---|
| A — TXT manuale | **TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA** |
| B — WS Test | **TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA** |
| C — WS Send | **TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA** |
| D — Ricevuta | **TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA**, con origine remota/firma da verificare nella prova |
| E — Retention | **CONFORME ALLA REGOLA IMPLEMENTATA**, sulle copie attive e con riconciliazione/associazione esplicite; backup operativi separati |
| F — Sicurezza credenziali | **RISOLTA nei percorsi e nelle fixture verificati**; audit/migration reale resta prerequisito operativo |

Nessuna accettazione reale Questura dichiarata. Trasporto resta disabilitato e fail-closed. Codice/test/documentazione/schema di migrazione modificati; DB/filesystem reali NO; solo risorse sintetiche effimere. **NESSUN COMMIT, PUSH, DEPLOY O TRASMISSIONE REALE.**

---

## Audit precedente — storico prima della chiusura retention

Le conclusioni seguenti sono conservate come evidenza storica della prima task. Policy corrente e verdetti della preparazione locale sono quelli dell’aggiornamento sopra; in particolare lo storico permanente del payload è SUPERATO.


## Stato generale Questura

**QUESTURA — PARZIALE.** Il progetto resta **BASELINE IN AUDIT**. Implementazione e prove locali sintetiche non equivalgono ad accettazione dell’ente. Attivazione reale bloccata da conflitto sulla conservazione dei dati trasmessi, confronto con TXT accettati non eseguibile e verifiche operative non autorizzate. Tutte le modifiche sono locali e revisionabili.

Scope autorizzato: sottosistema Questura, credenziali Struttura, sole interazioni Questura del Cestino, nuove migrazioni, test sintetici e documentazione. Rischio ALTO per trasmissioni, segreti e dati personali; nessuna operazione sui dati reali. Preflight iniziale: modifiche locali P1 in controller/modello/form Struttura, migration storica e test credenziali; conservate e completate quelle pertinenti. La migration storica è stata ripristinata al contenuto di HEAD, come richiesto.

## Fonti ufficiali

Consultate il **2026-10-06**; solo GET pubblici, mai operazioni SOAP.

| Titolo / ente | Fonte | Versione o data | Evidenza |
|---|---|---|---|
| Manuale Web Service, Polizia di Stato / CEN | [MANUALEWS.pdf](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEWS.pdf) | Rev. 01, 24/01/2022 | Token, parametri, esiti, ricevuta giornaliera e finestra di disponibilità |
| Guida Alloggiati, Polizia di Stato | [MANUALEALBERGHI.pdf](https://alloggiatiweb.poliziadistato.it/portalealloggiati/Download/Manuali/MANUALEALBERGHI.pdf) | Edizione non datata esplicitamente nel documento consultato | Tracciato, limiti, arrivi e organizzazione delle righe |
| Tabelle codici, Polizia di Stato | [Tabelle.aspx](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Tabelle.aspx) | Acquisizione 06/10/2026; nessun numero di versione dichiarato | Codici ufficiali di Comuni, Stati, documenti e tipi alloggiato |
| Contratto WSDL, Polizia di Stato | [service.asmx?WSDL](https://alloggiatiweb.poliziadistato.it/service/service.asmx?WSDL) | Acquisizione 06/10/2026 | Nomi, tipi XML, ArrayOfString, base64Binary; copia pubblica con hash nel repository |
| Descrizioni delle operazioni, Polizia di Stato | [GenerateToken](https://alloggiatiweb.poliziadistato.it/service/service.asmx?op=GenerateToken), [Test](https://alloggiatiweb.poliziadistato.it/service/service.asmx?op=Test), [Send](https://alloggiatiweb.poliziadistato.it/service/service.asmx?op=Send), [Ricevuta](https://alloggiatiweb.poliziadistato.it/service/service.asmx?op=Ricevuta), [Tabella](https://alloggiatiweb.poliziadistato.it/service/service.asmx?op=Tabella) | Consultazione 06/10/2026 | Riscontro del contratto, senza invocazioni |
| Nota di chiarimento sul trattamento dei documenti degli ospiti, Garante | [Nota ufficiale](https://garanteprivacy.it/web/guest/home/docweb/-/docweb-display/docweb/10244289) | Collegata al comunicato del 29/04/2026 | Richiama art. 4-bis comma 2 del DM 07/01/2013 modificato nel 2021: cancellazione dei dati digitali trasmessi dopo generazione della ricevuta; ricevuta conservata cinque anni |

I manifest in `reference/questura/` riportano URL specifici, data di acquisizione e SHA-256. Non sono stati archiviati documenti personali o credenziali.

**CONTRADDIZIONE documentata:** lo storico integrale permanente di TXT e payload richiesto non può essere dichiarato conforme alla disciplina richiamata dal Garante. Non è stata introdotta alcuna cancellazione automatica e non sono stati cancellati dati reali. La decisione su durata, minimizzazione e gestione della ricevuta, con conseguente modifica autorizzata dell’archivio, è requisito prima dell’uso reale. Il blocco tecnico resta attivo.

## Architettura finale e flusso Schedine

Schedina esistente → validazione specifica in `QuesturaTxtExportService` → mapping tramite `Catalogo` → contenuto esatto e metadati → TXT oppure servizio SOAP → esito sanificato ed eventi → ricevuta giornaliera separata → storico protetto. Non sono stati creati nuovi modelli di dominio Schedina per adattare il gestionale al protocollo.

Controller, modello, richieste, schema e normali regole di compilazione Schedina non sono stati modificati. Nessuna modifica ai dati GEO globali, all’import Componenti, al calendario, alle presenze o a ISTAT. Solo il test Schedina è stato reso autosufficiente, eliminando la dipendenza dall’utente ID 11 e creando fixture isolate. I contatori di esportazione già esistenti vengono aggiornati dopo l’archiviazione; i contatori di invio confermato non sono incrementati sulla sola risposta di `Send`.

## TXT e validazione Questura

Record di **168 caratteri**. L’output canonico usa un sottoinsieme ASCII di UTF-8: 168 byte per riga, CRLF fra record, nessun terminatore dopo l’ultima riga, nessun BOM. Dimensioni e offset zero-based:

| Campo | Offset | Lunghezza |
|---|---:|---:|
| Tipo alloggiato | 0 | 2 |
| Arrivo | 2 | 10 |
| Permanenza | 12 | 2 |
| Cognome | 14 | 50 |
| Nome | 64 | 30 |
| Sesso | 94 | 1 |
| Nascita | 95 | 10 |
| Comune nascita | 105 | 9 |
| Provincia nascita | 114 | 2 |
| Stato nascita | 116 | 9 |
| Cittadinanza | 125 | 9 |
| Tipo documento | 134 | 5 |
| Numero documento | 139 | 20 |
| Luogo rilascio | 159 | 9 |

Tipi 16 singolo, 17 capofamiglia, 18 capogruppo, 19 familiare e 20 componente gruppo. Capo prima dei componenti, ordinamento deterministico per ID. Le ultime 34 posizioni dei componenti sono spazi. Una famiglia o un gruppo senza componenti viene respinto dalla boundary Questura.

Validazione: campi obbligatori non vuoti, date reali con round-trip rigoroso, arrivo oggi/ieri, partenza successiva all’arrivo, permanenza entro 30, sesso valido, codici risolvibili senza ambiguità, tenant dei componenti coerente, tipi coerenti con il capo, limite 1000 righe nel batch. Nessun troncamento del record finale; nomi troppo lunghi respinti. Accenti comuni normalizzati esplicitamente, apostrofi/due varianti del trattino normalizzati e controllo dell’alfabeto ammesso; caratteri non rappresentabili respinti. Numero documento conservato esattamente quanto a lettere/case e punteggiatura ASCII, senza rimozioni silenziose; oltre 20 o non ASCII respinto. Non è attestata la resa universale di ogni nome Unicode: gli alfabeti e gli accenti non inclusi nella mappa esplicita sono respinti e richiedono una decisione documentata, evitando alterazioni dipendenti dalla locale.

Gli ID GEO e i codici ISTAT non vengono spediti come codici Questura. Risoluzione tramite etichette dei dati esistenti e cataloghi ufficiali separati, con provincia per i Comuni. Esempi tecnici pubblici: Italia `100000100`, Francia `100000215`, Roma `412058091`, distinto da ISTAT `058091`. Hash dei cataloghi verificato prima della lettura; codici ambigui o sconosciuti respinti. Non sono stati aggiornati gli archivi GEO globali.

Download: nuove generazioni via POST, file privato e metadati prima della risposta; download storico legge i byte archiviati con controllo hash, senza rigenerare. Conformità locale circoscritta alle fixture; accettazione ente NON VERIFICATA.

## Confronto TXT accettati

**BLOCCATO:** `/mnt/data` non esiste su questo Mac. Entrambi i file indicati nella richiesta non sono disponibili. Richiesto il percorso locale in sola lettura; nessuna risposta disponibile alla chiusura. Nessun confronto, compatibilità o differenza reale può essere attestato. Nessun dato personale mostrato, copiato o trasmesso.

## Dati Struttura, campi legacy e credenziali

La sezione Questura mostra **Utente Alloggiati Web, Password, WSKEY**. Password e WSKEY sono input password vuoti con indicazione di configurazione; nessun valore decifrato o `old()` segreto nel DOM. Utente resta visibile e non cifrato. Nessuna modalità prova modificabile dall’utente.

`questura_codici` e `questura_puk`: LEGACY, rimossi da form e accettazione della richiesta, nascosti nella serializzazione; colonne e valori esistenti preservati. `questura_ws_simulazione`: preservato nel DB, non alterabile tramite richiesta Struttura.

Password e WSKEY: cast Laravel `encrypted`, colonne TEXT nella nuova migrazione, campi nascosti in array/JSON, esclusione da TrimStrings e dontFlash. Aggiornamento con null/vuoto/soli spazi omette il campo, preservando il ciphertext esatto; nuovo valore cifrato una volta. Nessun segreto restituito dall’esito SOAP o registrato nello storico dell’esito; trace SOAP disabilitato e messaggi di eccezione non esposti. Non è stato eseguito un audit globale dei log o dei backup reali.

Migration legacy: lettura raw inclusi soft deleted, preflight completo prima delle scritture, ciphertext valido preservato, plaintext cifrato, null/vuoto preservati come assenza. Un involucro Laravel riconoscibile non decifrabile interrompe il processo con errore fisso senza valore segreto, indicando la necessità di verificare APP_KEY e backup; nessuna falsa doppia cifratura. Non è possibile identificare universalmente bytes corrotti che non mantengano un involucro riconoscibile. DDL MySQL non atomico con la transazione dati: il rollout futuro richiede backup e finestra senza aggiornamenti concorrenti. Test di ripetibilità e ciphertext con chiave diversa passati su fixture; nessuna scansione o migration di righe reali.

Cestino: snapshot Struttura minimizzato senza password, WSKEY, codici e PUK; restore elimina anche segreti presenti in snapshot storici. Il record originale soft deleted conserva i cast cifrati; ricreazione da snapshot richiede riconfigurazione. Nessun refactoring del resto del Cestino.

## Guardrail e Web Service

Configurazione `QUESTURA_WS_ENABLED` disabilitata per default. Ambiente non production, configurazione diversa da booleano true o flag struttura assente/true mantengono la simulazione. `makeClient` ripete il blocco prima di costruire il client: fail-closed anche se viene richiesto l’invio. Nessun toggle UI permette di aggirarlo. Nei test, doubles dedicati sostituiscono interamente il trasporto. Il test con serializer SOAP nativo legge esclusivamente il WSDL locale e ridefinisce `__doRequest`, senza trasporto padre e senza import remoti nel WSDL.

| Operazione | Stato locale | Contratto / comportamento |
|---|---|---|
| GenerateToken | IMPLEMENTATO, operativo NON VERIFICATO | Utente, Password, WsKey; token da risultato nominato, esito booleano, scadenza futura; token solo in memoria |
| Authentication_Test | IMPLEMENTATO, operativo NON VERIFICATO | Utente e token; controllo esito tipizzato |
| Test | PARZIALE | ElencoSchedine come ArrayOfString; verifica distinta da trasmissione; non produce accettazione o ricevuta |
| Send | PARZIALE | Stesso elenco; invocazione singola; possibili righe parzialmente acquisite; nessun retry automatico |
| Ricevuta | PARZIALE | Utente, token, Data; PDF base64Binary decodificato da SOAP; ultimi 30 giorni, escluso giorno corrente |
| Tabella | BLOCCATO operativamente | Nessuna chiamata live; archivio dei cataloghi pubblici già acquisiti e datati |

Client: SOAP 1.2, TLS con verifica certificato/hostname, timeout 30 secondi, trace false. Queste impostazioni sono ispezionate, ma TLS e timeout contro l’ente non sono stati provati. Nessun token inventato, risposta euristica generica o PDF dimostrativo è usato come evidenza ufficiale.

Esiti locali: simulation, in_progress, unknown, sent, partial, rejected, technical_error, uncertain, receipt_available/unavailable. `Test` positivo resta verifica non attestata, `Send` positivo è richiesta inviata; `accepted` rimane false. Dopo ingresso in Send, timeout/eccezione o risposta incoerente produce uncertain; un errore precedente è technical_error. Codici per riga 11/12 riconosciuti, altri non_classificato; testo arbitrario remoto scartato. Errori mostrati senza segreti e senza dati del payload.

## Storico TXT e Web Service

TXT: file UUID su disco local privato, struttura, utente, periodo, timestamp, filename, ID schedine/componenti, quantità, stato, charset, bytes e SHA-256. File e record archiviati, download con integrità, storico paginato. Modifica successiva di Schedina non cambia i byte storici. I vecchi record non hanno hash backfilled: download bloccato con 409 finché non avviene una verifica autorizzata.

WS: tentativo e snapshot base64 dei byte TXT più hash/dimensione/charset/contesto e primo evento `in_progress` persistiti in transazione breve **prima** del trasporto; esito sanificato e nuovo evento in transazione successiva. Nessuna transazione DB tenuta durante SOAP. Payload storico scaricabile con autorizzazione e hash. Chiave univoca per modalità trasporto + struttura + hash impedisce Send identici ripetuti/concorrenziali; la simulazione non prenota la chiave live. Nessuna garanzia di idempotenza remota, né deduplicazione fra payload diversi che contengano alcuni degli stessi ospiti.

Snapshot protetti da update/delete Eloquent; tentativo può passare dal solo stato iniziale all’esito terminale. Eventi inseriti in append; non esiste CRUD pubblico. Non è uno storage WORM: accessi amministrativi DB/filesystem possono aggirare gli eventi Eloquent. Un crash dopo Send e prima del salvataggio può lasciare in_progress: riconciliazione manuale futura senza reinvio automatico, da completare. Nessuna scadenza o eliminazione automatica introdotta; conflitto retention aperto.

## Ricevute

Acquisizione POST separata, per tentativi Send sent/uncertain/partial; data della trasmissione, non della nascita o dell’arrivo. Ricevuta **giornaliera per struttura**, non identificativo remoto del singolo invio. Una riga unica struttura/data collega il primo tentativo acquisitore; altri invii della stessa giornata scaricano la stessa ricevuta autorizzata. La ricevuta non dimostra da sola l’accettazione di ciascuna riga di un payload specifico.

Storage privato UUID, MIME PDF, byte_size e SHA-256, dati immutabili Eloquent; GET storico con controllo hash, attachment, nosniff e no-store. Nessun download esterno durante GET, nessuna rigenerazione PDF. Validazione minima del blob: limite 10 MB, intestazione `%PDF-` e terminatore EOF; **non** validazione strutturale completa, crittografica o della firma. Acquisizione e download provati con PDF sintetico e double. Concorrenza sul vincolo unico gestita rimuovendo la copia duplicata; errori DB diversi possono lasciare un file orfano, recupero da definire. PDF ufficiale e firma NON VERIFICATI.

## Multi-struttura e sicurezza

**PASS nelle prove A/B selezionate.** Struttura corrente risolta tramite membership backend esistente; nessuna fiducia nel solo ID fornito o nella sessione obsoleta. Tentativi, TXT, payload e ricevute filtrati per struttura. L’eccezione operativa admin preesistente viene preservata. Test con utenti A/B, ID diretto e selezione forgiata: accesso esterno negato e nessuna chiamata al servizio ricevute per tenant estraneo.

Auth e autorizzazioni esistenti mantenute. Generazione, Test, Send, acquisizione ricevuta e archiviazione tabelle via POST sotto middleware web/auth e CSRF. GET sulla vecchia generazione non muta DB; la route fallback restituisce 404. Richiesta senza CSRF valida produce 419. Storage privato, download senza path pubblici; segreti assenti da serializzazione, form e risultato esposto. Non attestata matrice globale ruoli/tenant, acceptance browser o analisi degli accessi amministrativi e backup.

## Test e regressione Schedine

Tutte le prove Laravel usano `tests/Isolation/run.py`: checkout temporaneo, MySQL dedicato, filesystem sintetico e nessun servizio esterno operativo. Aggiunta `--include` per includere esplicitamente file nuovi pertinenti: solo directory autorizzate, no path assoluti/traversal/symlink, nessuna copia implicita di untracked o .env e nessun indebolimento delle guardie.

Comando della selezione finale:

```sh
python3 -B tests/Isolation/run.py --include \
 app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php \
 app/Models/QuesturaReceipt.php config/questura.php \
 database/migrations/2026_10_06_100000_encrypt_questura_credentials.php \
 database/migrations/2026_10_06_110000_add_questura_archive_integrity.php \
 reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv \
 reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl \
 reference/questura/wsdl-manifest.json \
 --phpunit tests/Feature/QuesturaCredentialEncryptionTest.php \
 tests/Feature/QuesturaBoundaryTest.php tests/Feature/QuesturaWsContractTest.php \
 tests/Feature/QuesturaLegacyMigrationTest.php tests/Feature/StrutturaAuthorizationTest.php \
 tests/Feature/SchedinaStoreTest.php tests/Unit/PianoSyncComponentiTest.php \
 tests/Unit/ComponentiImportP1Test.php
```

**Risultato finale: PASS — 68 test, 337 asserzioni.** Runtime `/private/tmp/schedine-test-vk6_prvs`, DB `test_geo_ed43b8b74d382bafe9bd57617ce81815`, MySQL PID 7366 / porta 54867, HTTP PID 7371 / porta 54868. Identità, riconnessione e 23 rifiuti override PASS; build asset e GEO immutabile PASS; risorse fermate e rimosse. PHP 8.3.33 / PHPUnit 10.5.38 / MySQL 8.0.36. HEAD di riferimento `3eb559cfea9fed6a20e7949037f1d5a0236695be`, modifiche locali incluse nel checkout temporaneo. Prove sovrapposte non sommate.

| Altra prova | Risultato |
|---|---|
| `python3 -B tests/Isolation/test_guards.py` | 5 PASS |
| `php tests/Safety/questura_security.php` | 14 PASS; doubles in memoria |
| `python3 -B tests/Isolation/run.py --include .env` | Rifiuto atteso prima di avviare DB/rete, exit 2 |
| Sintassi PHP su file modificati/nuovi | PASS: 25 file |
| Hash cataloghi/WSDL e `git diff --check` | PASS alla chiusura; primo controllo manifest con errore KeyError per forma diversa del manifest WSDL, script corretto e hash verificati |

Fallimenti conservati nel registro, in ordine: primo tentativo bloccato dal sandbox prima del bind; primo runtime 26 test/87 asserzioni, 4 errori per file nuovi non inclusi; successivo 55/208, 1 errore per fixture utente implicita e 1 failure di aspettativa tenant; 58/240 PASS; estensione 61/247 con fixture legacy/famiglia incomplete; 64/296 PASS; 65/302 PASS; 67/324 con una failure aspettativa GET 405 invece del 404 della fallback; 68/333 PASS; 68/330 con una failure É trasformata da iconv in apostrofo+E, corretta con normalizzazione esplicita degli accenti comuni; successivo 68/332 con un errore iconv su carattere non rappresentabile, risolto eliminando iconv dalla normalizzazione dei nomi e respingendo alfabeti non supportati. Nessun fallimento nascosto e nessuna guardia disabilitata per ottenere il verde. Ogni runtime è stato fermato e rimosso dal launcher.

**Regressione Schedine: nessuna regressione dimostrata nella selezione.** Create/update singolo, famiglia e gruppo provati con fixture autosufficienti; componenti e nomi preservati. Test PianoSyncComponenti e ComponentiImport P1 inclusi. Non eseguita la suite completa né acceptance browser: nessuna certificazione generale del gestionale.

## File modificati e nuovi

| File / gruppo | Motivo |
|---|---|
| `app/Exceptions/Handler.php`, `app/Http/Middleware/TrimStrings.php` | Esclusione segreti da flash e trimming |
| `app/Http/Controllers/StrutturaController.php`, `app/Http/Requests/StrutturaRequest.php`, `app/Models/Struttura.php`, `resources/views/struttura/form.blade.php` | Credenziali cifrate/nascoste, aggiornamento vuoto conservativo, UI con tre campi e rimozione toggle/legacy |
| `app/Support/Questura/LegacyCredentials.php` | Conversione legacy, preservazione ciphertext e arresto su involucro indecifrabile |
| `app/Support/Questura/Catalogo.php` | Mapping ufficiale separato e controllo hash |
| `app/Services/QuesturaTxtExportService.php` | Validazione e tracciato deterministico senza modificare il dominio |
| `app/Services/QuesturaWebService.php`, `app/Services/EsitoTrasmissioneQuestura.php`, `config/questura.php` | Contratto ufficiale, esiti tipizzati, guardrail e ricevute |
| `app/Http/Controllers/QuesturaExportController.php`, `routes/web.php`, `resources/views/questura/index.blade.php` | POST/CSRF, tenant, snapshot e archivi, esiti distinti, download protetti |
| `app/Models/QuesturaExport.php`, `app/Models/QuesturaTransmission.php`, `app/Models/QuesturaReceipt.php` | Metadati, serializzazione e protezione storici |
| `app/Services/CestinoService.php` | Solo rimozione segreti Questura da snapshot/restore Struttura |
| Le due nuove migrazioni sotto elencate | Evoluzione schema e credenziali senza riscrivere la storia |
| `reference/questura/comuni.csv`, `stati.csv`, `documenti.csv`, `tipi.csv`, `manifest.json`, `service.wsdl`, `wsdl-manifest.json` | Riferimenti pubblici ufficiali datati e verificabili |
| `tests/Feature/QuesturaCredentialEncryptionTest.php`, `QuesturaLegacyMigrationTest.php`, `QuesturaBoundaryTest.php`, `QuesturaWsContractTest.php` | Fixture sicurezza, migration, controller, tenant, TXT e SOAP offline |
| `tests/Feature/SchedinaStoreTest.php` | Regressione create/update sintetica singolo/famiglia/gruppo |
| `tests/Isolation/run.py`, `tests/Safety/questura_security.php` | Inclusione esplicita sicura dei nuovi file e aggiornamento doubles ufficiali |
| Questo report e `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md` | Nuove evidenze, limiti e conflitto retention |

## Migrazioni nuove

1. `2026_10_06_100000_encrypt_questura_credentials.php`: TEXT e conversione legacy password/WSKEY, preflight e dati in transazione.
2. `2026_10_06_110000_add_questura_archive_integrity.php`: metadati nullable agli storici, chiave deduplicazione unica, eventi append e ricevute con vincoli restrittivi e unicità giornaliera.

**NESSUNA MIGRATION STORICA MODIFICATA nel diff finale.** `down()` intenzionalmente non distruttivi: rollback non cancella archivi né ripristina plaintext; non significa reversibilità automatica dello schema. Schema provato soltanto su MySQL effimero. Nessuna migration applicata a DB reale; nessun backfill degli storici reali.

## Git

Branch `main`; HEAD e `origin/main` `3eb559cfea9fed6a20e7949037f1d5a0236695be`; fetch autorizzato riuscito, ahead/behind **0/0** al preflight. Worktree con le modifiche sopra, file nuovi untracked intenzionali; staging vuoto. Nessun commit, push, pull, merge, rebase, reset o deploy. La migration storica ripristinata non compare nel diff. Il riferimento remoto è quello del fetch, non una prova di stato in produzione.

## Limiti residui e prova reale futura

Prima di qualsiasi prova reale, in una nuova autorizzazione separata:

1. Risolvere il conflitto retention con decisione conforme e implementazione verificata di minimizzazione/cancellazione dei dati trasmessi; definire gestione di ricevute, snapshot e backup. Nessuna cancellazione improvvisata.
2. Rendere disponibili i due TXT accettati in sola lettura e confrontare solo proprietà tecniche anonime: charset, byte, newline, offset, mappature e componenti. Completare nomi Unicode e combinazioni di catalogo pertinenti.
3. Revisionare il diff, backup e APP_KEY; audit legacy reale autorizzato, rollout delle due migrazioni in finestra controllata. Gestire hash dei file storici e controllare permessi storage/backup. Verificare tabelle correnti rispetto allo snapshot del 06/10/2026.
4. Completare recupero crash `in_progress`, deduplicazione di payload sovrapposti e file orfani; validazione PDF/firma e associazione dei singoli invii alla ricevuta giornaliera senza inventare ID remoti.
5. Solo dopo autorizzazione operativa: configurare credenziali reali server-side, ambiente e TLS, attivare guardrail in modo controllato; prima autenticazione e Test su un campione autorizzato; Send separatamente autorizzato senza retry cieco; recuperare ricevuta il giorno successivo nella finestra ufficiale e verificare accettazione. Riesaminare CSRF, matrice ruoli e UI browser.

Nessuno di questi passi operativi è stato eseguito. Nessuna credenziale reale letta, nessun SOAP reale, nessun upload né invio di ospiti.

## Verdetto finale separato

| Area | Verdetto | Portata |
|---|---|---|
| A — TXT manuale | **NON PRONTO** | Prove sintetiche positive, confronto accettati e retention pendenti |
| B — Web Service Test | **NON PRONTO** | Contratto/doubles provati; autenticazione operativa non autorizzata né verificata |
| C — Web Service Send | **NON PRONTO** | Retention, riconciliazione e accettazione ente aperte |
| D — Ricevuta | **NON PRONTO** | PDF reale/firma e associazione operativa non provati |
| E — Storico TXT | **INCOMPLETO** | Nuovi snapshot provati; retention e integrità legacy non chiuse |
| F — Storico Web Service | **INCOMPLETO** | Snapshot/eventi provati; crash/riconciliazione e retention aperti |
| G — Sicurezza credenziali | **NON RISOLTA sul reale** | RISOLTA sulle fixture e sui percorsi corretti; migration/audit reale non eseguiti |
| H — Regressione Schedine | **NESSUNA REGRESSIONE DIMOSTRATA** | Solo selezione isolata documentata |

Chiusura: CODICE SÌ; TEST SÌ; DOCUMENTAZIONE SÌ; CONFIGURAZIONE SÌ (file, nessuna attivazione reale); SCHEMA/DATI REALI NO (solo DB effimero di test); TEST ESEGUITI SÌ; COMMIT NO; PUSH NO; DEPLOY NO; TRASMISSIONI OPERATIVE ESTERNE NO; consultazioni GET di fonti pubbliche SÌ; MAESTRO AGGIORNATO SÌ.

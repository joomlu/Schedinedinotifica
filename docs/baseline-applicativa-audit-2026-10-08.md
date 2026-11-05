# Audit della baseline applicativa locale — 8 ottobre 2026

> Aggiornamento: le correzioni autorizzate A01–A03 e le verifiche successive sono documentate nell’appendice conclusiva. Le sezioni iniziali conservano la diagnosi precedente. La baseline resta NON VALIDATA.

## Verdetto e portata

**BASELINE NON VALIDATA. Stato ufficiale: BASELINE IN AUDIT.**

La suite PHPUnit verde è confermata come evidenza precedente sui byte applicativi attuali, ma non copre ogni percorso del gestionale. L'audit indipendente riproduce **tre P1 applicativi distinti**: uscita dall'impersonazione, parent cross-tenant Web Check-in e collegamento cliente cross-tenant nel salvataggio di una bozza. Nessuna correzione applicata. Nessun P0 dimostrato: questo non garantisce assenza di altri difetti.

Repository: `/Users/jorgeluccitelli/Herd/Schedinedinotifica`. Branch `main`; HEAD e riferimento **locale** `origin/main`: `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`; divergenza locale 0/0. Nessun fetch, pertanto non si attesta lo stato remoto corrente. Oggetto dell'audit: HEAD più le **53 modifiche locali preesistenti**, non il solo commit.

Riferimenti: Maestro ufficiale, `docs/TESTING_ISOLATION.md`, rapporti Questura, Tassa Bellaria, risanamento suite e relativi test/codice. Le appendici recenti prevalgono sugli stati storici rossi conservati nel Maestro. Non riaperta la logica fiscale certificata, né le implementazioni Questura/ISTAT.

**Obiettivo:** diagnosticare la coerenza della baseline e i gate per una successiva accettazione. **File permanenti della fase:** questo rapporto e appendice al Maestro. **Rischio operativo delle modifiche:** basso, solo documentazione; i rischi applicativi individuati sono separati. **Prove previste/eseguite:** richieste HTTP nel kernel reale, fixture sintetiche, Playwright nel runtime attestato, lettura statica di route/controller/model/migration e verifica hash.

## Metodo e isolamento

Tutte le prove Laravel e browser attraversano `tests/Isolation/run.py`. PHP 8.3.33, MySQL 8.0.36, PHPUnit 10.5.38. Ogni esecuzione crea un checkout privato, istanza MySQL, DB e storage nuovi, server HTTP loopback e broker attestato; identità/processi/riconnessione e **23 rifiuti di override prima delle migration** superati. Questura e ISTAT disabilitati nell'ambiente browser; servizi esterni non invocati. Il proxy browser limita l'origine al server effimero. Nessun `.env` operativo copiato.

Il wrapper diagnostico esterno aggiunge il test indipendente **soltanto nella copia temporanea**, immediatamente prima di PHPUnit. Non cambia test preesistenti, asserzioni, middleware, policy DB o logica applicativa. Il sorgente riproducibile completo è nell'appendice. Mail in memoria, migrazioni solo sul DB effimero; nessuna lettura o modifica del DB operativo.

## Prove realmente eseguite

| Prova | Esito | Limite |
|---|---|---|
| Prima selezione backend | 41 test / 317 asserzioni, 2 failure, 0 errori | Quattro diagnostici indipendenti e 37 casi esistenti; collisione route e uscita impersonazione falliscono |
| Selezione estesa | 43 / 322, 3 failure, 0 errori | Aggiunti parent Web Check-in e numerazione annuale; parent fail, numerazione PASS |
| Selezione conclusiva | **44 / 323, 4 failure, 0 errori, 0 skip** | Sette diagnostici indipendenti più 37 casi esistenti; aggiunto riferimento cliente non autorizzato, FAIL |
| Browser corrente | **6 scenari PASS**, 55,1 secondi | Cinque selezione struttura/percorso fiscale più uno anteprime multiaccesso; non tutti gli schermi del prodotto |
| JavaScript diagnostico storico, ripetuto | 3 test, 0 PASS, 3 FAIL | VM incompleta; non prova un bug nel DOM reale |
| Hash iniziali, prima della documentazione | **4.178 file preesistenti invariati** | Include tracked e untracked; copie diagnostiche solo fuori repository |
| `git diff --check` | PASS | Controllo whitespace, non certificazione funzionale |

Le selezioni sono sovrapposte: **non sommare** test o asserzioni. Le quattro failure finali rappresentano tre difetti: due riguardano l'impersonazione. Nessuna aspettativa alterata per ottenere risultati verdi. Il conteggio della suite PHPUnit preesistente rimane 582: i diagnostici esterni non sono stati aggiunti alla discovery del repository.

**Evidenze precedenti, non nuove esecuzioni:** due globali sullo stesso ambiente reinizializzato e una casuale seed4208, ciascuna **582 test / 6.850 asserzioni / zero failure, errori, skip e deprecazioni**. I tre JUnit e l'identità dei casi sono disponibili fuori repo. Applicazione/test preesistenti byte-identici durante questa fase. Regressioni precedenti separate Questura204/3656, ISTAT65/410, Tassa50/1572, autorizzazioni30/125. Non dedurre da ciò browser completo, accettazione dell'ente o funzionamento produttivo.

Il browser ha inoltre rieseguito 14 controlli SOAP/sicurezza **con doubles in memoria**, PASS. I risultati precedenti lint/Pint/guardrail restano datati alla fase di risanamento; non presentati come nuove esecuzioni integrali.

### Runtime e artefatti

| Esecuzione | Directory privata | DB effimero | Esito launcher |
|---|---|---|---|
| Backend iniziale | `/private/tmp/schedine-test-rrmt9wmz` | `test_geo_074b09f7a30f688401f4960f1a9b5dd5` | 1, per due diagnostici rossi |
| Browser | `/private/tmp/schedine-test-e0s2ak8j` | `test_geo_bae21918cab720b3a3bcbc5433044831` | 0 |
| Backend esteso | `/private/tmp/schedine-test-6f_x1y5q` | `test_geo_a83e3b784349a4b4f775b539df486aa9` | 1, per tre diagnostici rossi |
| Backend integrità | `/private/tmp/schedine-test-y3lc0db4` | `test_geo_961d74315002b7ddf4d3bfd63d62f834` | 1, per quattro diagnostici rossi |

Tutti i runtime sono fermati/rimossi, compresi quelli rossi. Log esterni: `/private/tmp/ids-baseline-audit-backend.log`, `ids-baseline-audit-phpunit.log`, `ids-baseline-audit-esteso.log`, `ids-baseline-audit-esteso-phpunit.log`, `ids-baseline-audit-integrita.log`, `ids-baseline-audit-integrita-phpunit.log`, `ids-baseline-audit-browser.log`, `ids-baseline-audit-js.log`. Prefissi dei file senza path completo in questa lista: `/private/tmp/`. Questi artefatti effimeri non sono versionati; le cause e il codice riproducibile restano in questo documento.

## Inventario e matrice funzionale

Inventario statico attuale: 60 controller, 57 file modello/trait, 25 servizi, 15 middleware, 148 file migration. `routes/web.php` contiene 285 dichiarazioni esplicite get/post/put/patch/delete/any/match/resource: **non** è il totale delle route risolte, perché macro/resource/Auth espandono l'inventario. Le route effettive d'uscita impersonazione sono state risolte dal router nel runtime.

| Requisito/modulo | Percorsi e prove | Stato funzionale | Lacuna o blocco |
|---|---|---|---|
| 1. Autenticazione | `/login`, `/logout`, accesso anonimo `/schedine`; credenziali errate, utente inattivo, CSRF errato, login/logout reali nel kernel; login browser proprietario/reception | PARZIALMENTE VERIFICATA | Impersonazione P1; reset/conferma password, mail, remember-me, revoca sessioni già attive e registrazione non provati end-to-end |
| Ruoli e permessi | AccessTest e CestinoSecurityTest ripetuti; super_admin/admin/proprietario/struttura_user, anonimato, CSRF, rifiuto tenant | VERIFICATI per endpoint selezionati | Nessuna matrice completa di ogni route amministrativa/CRM/supporto |
| 2. Multi-tenancy | `/strutture/seleziona`, POST selezione, reload, dashboard, Schedine e documenti fiscali dopo cambio; Tenancy e Cestino | PARZIALMENTE VERIFICATA | P1 cliente e parent Web Check-in; non validabile rigorosamente l'intero prodotto |
| 3. Cicli annuali | `resequenceCircuitCodes`, arrivo2025/2026, circuito schedina/arrivi, secondo tenant | VERIFICATI nel metodo su fixture | Nessun modello separato ciclo annuale; anno del codice deriva dall'arrivo, fallback created_at. Next code usa now prima del riordino; concorrenza e numeri stabili dopo modifiche/eliminazioni non certificati |
| 4. Clienti/componenti | Ricerca e localizzazione catena nel codice, CustomerImportedFiltersDelete e Unit import nel JUnit precedente; creazione/update di tre composizioni Schedina ripetuta | PARZIALMENTE VERIFICATI | P1 riferimento cliente; CRUD cliente/storico e deduplicazione reale completa nel browser non rieseguiti. Riutilizzo stesso proprietario è regola esplicita, non autorizza tenant arbitrari |
| 5. Schedine e arrivi | Store/update ospite singolo, famiglia, gruppo: 3/39 PASS; `/arrivi/nuovo`1/8 nella globale precedente, smoke e fiscale precedenti | PARZIALMENTE VERIFICATE | Bozza P1; conversione Arrivi→Schedina, partenze/presenze, camere e condizioni concorrenti non percorse tutte nel browser |
| 6. Questura | Backend 204/3656 precedente, contratti Q1/Q2/Q3, OFF e snapshot; 14 SOAP in memoria ripetuti | VERIFICATA regressione locale nei casi certificati | Nessun nuovo browser Questura in questa fase; TLS, ente, firma/ricevute reali e rollout esclusi |
| 7. ISTAT | Conformità29, ciclo33, sicurezza3 nella globale precedente; file XML/codifiche/snapshot/tenant | VERIFICATA regressione offline nei casi certificati | Nessuna nuova prova browser ISTAT o accettazione Ross1000 |
| 8. Tassa Bellaria | 50/1572 precedente; browser attuale anteprima zero/positiva/mobile, stampa, report, CSV, consolidamento e storico | VERIFICATA nei casi coperti | Solo categorie/profili documentati; fonti2027/categorie RTA-villaggi non certificate bloccate; accettazione StayTour, CSV solo testa e configurazioni operative escluse |
| 9. Importazioni | CSV/XLSX, date impossibili, strutturali, contratto/lock nelle Unit precedenti; filtri/delete staging con tenant | PARZIALMENTE VERIFICATE | Commit batch/rollback DB/duplice conferma e UI integrale import clienti/componenti non provati indipendentemente in questa fase; tre VM rosse |
| 10. Interfaccia | `layouts.master`, selezione una/multi/vuota, reception, cambio, errore404 cross-tenant, anteprime visibili e stampa, viewport390px | VERIFICATA per sei scenari | Non tutti i pulsanti né CRUD/admin/browser/motori/accessibilità; PDF prodotti, non nuova ispezione visiva indipendente di ogni pagina |
| 11. Database | Migration reali nella sola istanza temporanea; DDL C1 già nella globale, isolamento storage/DB precedente; cross-link sintetico permesso | PARZIALMENTE VERIFICATO | Nessun audit delle righe operative; FK semplici non garantiscono uguaglianza tenant tra parent/child. Schema/data operativo corrente non attestato |
| 12. Sicurezza | Auth/ruoli/CSRF/IDOR su selezione/documenti/cestino; input cliente e parent Web Check-in avversariali | PARZIALE, CON DIFETTI RIPRODOTTI | Tre P1; token/scadenza/riuso Web Check-in, sessioni, upload, log di altri moduli e ogni route/API non certificati |
| Cestino | 29 test /239 asserzioni ripetuti, read/restore/purge/globali/snapshot/CSRF | VERIFICATO per casi selezionati | Vecchio rischio generico R1/R2 non va ignorato né descritto come exploit attuale: questi casi sono verdi; altri tipi/race restano da valutare |
| CRM/supporto/licenze/calendario/notifiche | Lettura architettura/route, senza nuova matrice browser | NON VERIFICATI end-to-end | Richiedono accettazione dedicata se inclusi nel rilascio |

La prova fiscale SCHEDINA=RICEVUTA=REPORT=CSV resta coperta dalle regressioni Bellaria preesistenti; non equivale a certificazione normativa o accettazione amministrativa. Il test browser sostituisce `window.print` per verificare il pulsante, poi genera PDF Chromium: verifica visibilità e invocazione stampa, non dialogo stampante/driver fisico. La numerazione annuale è provata sul metodo reale tramite Reflection, non sull'intero flusso concorrente.

## Difetti riprodotti e classificazione

### A01 — P1: uscita dall'impersonazione irraggiungibile

File: `routes/web.php:216–218`, `app/Http/Controllers/Superadmin/ImpersonazioneController.php`, middleware `Ruolo.php`.

Prova: Super Admin sintetico → POST `/superadmin/impersona/{id}` → redirect e autenticazione target `struttura_user`. POST `/superadmin/impersona/esci` → **403**, invece di ripristinare l'amministratore. Seconda prova indipendente: il router risolve la route statica a **`impersona`**, non `esci`, perché la route parametrica precedente non ha vincolo numerico. Due failure, un ciclo funzionale difettoso con due ostacoli da correggere.

Causa: stop nello stesso gruppo `ruolo:super_admin`, ma l'identità corrente è ormai operativa; collisione parametrica non esclusa. Preesistente in HEAD e già rischio R3 del Maestro, ora riprodotto. Non è regressione Tassa/Questura/ISTAT. Impatto: amministratore bloccato nel ruolo impersonato, uscita ordinaria non disponibile. Non dimostrata escalation.

Correzione proposta **da autorizzare**: distinguere autorizzazione start e stop; uscita vincolata alla sessione di impersonazione valida e all'impersonatore originale, CSRF mantenuto; route numerica o ordine non ambiguo. Acceptance: start/stop su ogni ruolo, sessione assente/manomessa, target eliminato/disattivato, cambio contesto, log ended_at, nessun accesso di un ordinario non impersonato.

### A02 — P1: Web Check-in non valida il tenant della Schedina collegata

File: `app/Http/Controllers/WebCheckinController.php:415–423`, chiamato da `publicShow/publicStore/publicCompleted`; modello `WebCheckinRichiesta` e relativa migration.

Precondizione esplicita: fixture richiesta tenantA con `schedina_id` appartenente al tenantB, stato `convertito`, token sintetico valido. **Il link incoerente viene creato nella fixture; non si dimostra che l'utente ordinario possa crearlo nell'interfaccia.** GET anonimo `/checkin/{token}` restituisce **200 con il nome sentinella del tenantB**, mentre il requisito negativo impone rifiuto e assenza del dato. La prova non usa token reali o ospiti reali.

Causa: `ensureLinkedSchedina` rilegge il parent con `withoutGlobalScopes()->find()` e lo accetta senza confronto di `struttura_id`; il percorso pubblico usa poi quel record. FK/indice semplice non garantisce appartenenza del parent al tenant della richiesta. Rischio R5 già documentato, ora riprodotto in condizione di riferimento incoerente. Non classificato come exploit da token casuale o accesso a dati reali. Mutazione cross-tenant via publicStore è un rischio statico sullo stesso parent, **non provata** in questa fase.

Correzione proposta **da autorizzare**: rifiutare parent incoerenti nel punto comune, prima di esposizione o scrittura; nessuna riparazione silenziosa del dato legacy. Acceptance: GET/POST/completed/full/short, tenant uguale/diverso, record mancante, stato convertito, nessuna scrittura nel rifiuto, token invalidato/scaduto secondo contratto esplicito. Separare inventario eventuali record operativi incoerenti, che qui non viene interrogato.

### A03 — P1: bozza Schedina può collegare un cliente non autorizzato

File: `app/Http/Controllers/SchedinaController.php:270–278` e `resolveCustomerFromRequest():765–786`; analogo fallback nel ramo update va verificato, non dichiarato riprodotto.

Prova: utente `struttura_user` del tenantA, cliente sintetico tenantB non appartenente a catena autorizzata. POST `route('schedina.store')` con `save_mode=draft`, nome/cognome sintetici e `customer_id`B. Risultato: esiste **una bozza tenantA con customer_idB**, anziché zero. Nessuna manipolazione DB del collegamento: lo crea il vero controller HTTP. Le asserzioni sul salvataggio normale esistente restano verdi.

Causa: `resolveCustomerFromRequest` filtra correttamente le strutture consentite e restituisce null; `$payload['customer_id'] = $customer?->id ?: $request->input('customer_id')` ripristina l'ID rifiutato. L'esistenza SQL del cliente non dimostra autorizzazione. Impatto dimostrato: contaminazione referenziale cross-tenant; lettura di dati del clienteB attraverso la bozza e modifica del clienteB **non dimostrate**. È distinto dalla condivisione/localizzazione di clienti della stessa catena, prevista dal codice.

Correzione proposta **da autorizzare**: non persistere l'ID non risolto; rifiuto esplicito se fornito e non autorizzato, mantenendo il riuso/localizzazione consentito. Acceptance: store/update, draft/full/to_arrivi, ID inesistente/estero, stesso tenant, stessa catena autorizzata, import row; assenza nuove Schedine/clienti nel rifiuto, deduplicazione preservata.

### A04 — P2 di infrastruttura test: tre diagnostici JavaScript rossi

`tests/Unit/componenti-import-submit.test.mjs`: `submitter.matches is not a function` in due casi e `componentIndexInput is not defined` nel terzo. La VM estrae un listener dalla closure e non fornisce metodi/binding del DOM reale. Il Blade dichiara i binding; il pulsante browser possiede matches. Ripetuti 3/3 FAIL, preesistenti. Nessun fix né aspettativa indebolita. Non prova di malfunzionamento della UI import, ma copertura indipendente mancante: completare il doppio o, preferibilmente, una prova browser del flusso reale dopo autorizzazione.

### A05 — P2 ambientale: eccezione proxy dopo le prove browser

Dopo **6 PASS**, il proxy stampa `http.client.IncompleteRead(262144 bytes read, 134588 more expected)`, durante response.read(). Launcher exit0 e cleanup osservati. URL/risposta associata non identificati nel traceback, quindi **non** viene dimostrata la causa precisa né garantita irrilevanza. Anomalia già osservata storicamente, da diagnosticare separatamente prima di una nuova acceptance estesa/download pesanti. Non modifica l'esito delle sei asserzioni completate e non è classificata come bug applicativo provato.

### P0 e P3

Nessun P0 riprodotto. Nessun P3 funzionale assegnato. Le sezioni storiche del Maestro e TESTING_ISOLATION contengono conteggi/limitazioni superati: leggibilità documentale da consolidare in fase separata, non nuova falla e non autorizzazione alla cancellazione della storia.

## Rischi residui e gate

- **Blocco applicativo:** chiusura dei tre P1, con autorizzazione specifica e nuove prove RED→GREEN, prima di validare l'intera baseline.
- **Copertura:** browser CRUD cliente/componenti/import/Arrivi→Schedina/presenze e matrice negativa completa route/ruoli/tenant/API; sicurezza sessioni/password/registrazione/Web Check-in/upload/log/restore. Mancano prove dedicate in discovery per Web Check-in e impersonazione; una suite verde non copre i nuovi diagnostici.
- **Integrità/concorrenza:** FK parent/tenant, riferimenti null/legacy, numeri annuali dopo riordino, duplicazioni, race multiprocesso. Nessuna conclusione sulla popolazione operativa.
- **Fiscale:** anno/categoria coperti da fonti, configurazioni legacy da riallineare tramite consenso UI, nuova migration Tassa fuori DB operativo; nessuna fonte futura inventata, nessun fallback a zero. StayTour e integrazioni reali restano gate separati.
- **Produzione:** release esatta, Rocky/Bubblewrap/MariaDB reale isolato secondo gate documentati, configurazioni/worker/mail, backup/restore, asset/cache/permessi e acceptance server. Non riverificati né modificati qui. Le sei prove Rocky storiche non vengono rieseguite o trasformate in accettazione dell'applicazione.

## Piano ordinato, senza esecuzione di correzioni

| Priorità | Attività | Dipendenze e rischio | Criterio di accettazione |
|---|---|---|---|
| 1 / P1 | Chiudere A03 riferimenti cliente | Autorizzazione minima store/update; rischio medio su localizzazione catena/import | Matrice modi/tenant/ID, rifiuto senza scritture; riuso legittimo e contratti certificati preservati |
| 1 / P1 | Chiudere A02 parent Web Check-in | Autorizzazione; contratto su legacy/token, rischio alto su accessi pubblici | Parent coerente obbligatorio in tutti i percorsi, zero esposizione/scritture su mismatch, nessuna riparazione implicita |
| 2 / P1 | Chiudere A01 impersonazione | Autorizzazione; rischio medio su identità/sessioni | Route corretta, uscita valida per ogni target, richieste contraffatte negate, log/contesto coerenti |
| 3 / P2 | Diagnosticare proxy A05 e riallineare harness VM A04 | Autorizzazione separata, nessun adattamento della logica applicativa a test errati | Download completi e chiusura senza traceback; DOM reale e contratti POST/PUT/annullamento provati |
| 4 | Completare accettazione locale e sicurezza | P1 chiusi, fixture autosufficienti; rischio medio | Matrice completa negativa/positiva dei flussi ancora parziali, browser realmente osservato, nessun P0/P1 aperto |
| 5 | Review del candidato complessivo | Inventario per intervento, byte preservati, documenti allineati | Scope atomici approvati, segreti/artefatti esclusi, suite normale/repeat/random verde dopo fix |
| 6 | Gate server e integrazioni | Autorizzazione distinta, backup/restore e trasporti OFF | Prove del destinatario e accettazione esterna nello scope concordato; nessun automatismo dal presente rapporto |

## Inventario Git completo iniziale

29 tracked modificati +24 untracked, staging vuoto. I seguenti 53 file sono **preesistenti** all'audit e non vengono assimilati a nuove correzioni. Tassa/UI precedente, harness MariaDB, risanamento suite e altre evidenze rimangono distinguibili; nessuno staging indiscriminato.

| Stato iniziale | Percorso | Ambito |
|---|---|---|
| Tracked modificato | `app/Http/Controllers/SchedinaController.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `app/Http/Controllers/TassaDiSoggiornoController.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `app/Http/Controllers/TassaEsenzioneController.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `app/Http/Controllers/TassaReportController.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `app/Models/TassaDiSoggiorno.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `app/Services/TassaDiSoggiornoService.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `docs/DEPLOY_SPANEL.md` | Documentazione/evidenza preesistente |
| Tracked modificato | `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md` | Documentazione/evidenza preesistente |
| Tracked modificato | `resources/views/schedina/list.blade.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `resources/views/schedina/partials/form.blade.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `resources/views/schedina/print-tassa.blade.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `resources/views/struttura/seleziona.blade.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `resources/views/tassa_di_soggiorno/edit.blade.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `resources/views/tassa_di_soggiorno/rapporto-print.blade.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `resources/views/tassa_di_soggiorno/rapporto.blade.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `routes/web.php` | Candidato Tassa/UI preesistente |
| Tracked modificato | `tests/Feature/AccessTest.php` | Test risanamento suite |
| Tracked modificato | `tests/Feature/CustomerImportedFiltersDeleteTest.php` | Test risanamento suite |
| Tracked modificato | `tests/Feature/ExampleTest.php` | Test risanamento suite |
| Tracked modificato | `tests/Feature/QuesturaC1MigrationAcceptanceTest.php` | Test risanamento suite |
| Tracked modificato | `tests/Feature/SmokePagesQaTest.php` | Test risanamento suite |
| Tracked modificato | `tests/Feature/TenancyTest.php` | Test risanamento suite |
| Tracked modificato | `tests/Isolation/run.py` | Harness/isolamento preesistente |
| Tracked modificato | `tests/Support/TestingEnvironment.php` | Harness/isolamento preesistente |
| Tracked modificato | `tests/TestCase.php` | Test risanamento suite |
| Tracked modificato | `tests/Unit/ComponentiImportFormTest.php` | Test risanamento suite |
| Tracked modificato | `tests/Unit/ComponentiImportReviewStatusTest.php` | Test risanamento suite |
| Tracked modificato | `tests/Unit/ComponentiImportServiceTest.php` | Test risanamento suite |
| Untracked | `app/Models/TassaExport.php` | Candidato Tassa/UI preesistente |
| Untracked | `database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php` | Candidato Tassa/UI preesistente |
| Untracked | `docs/imposta-soggiorno-bellaria-audit-2026-10-07.md` | Documentazione/evidenza preesistente |
| Untracked | `docs/suite-globale-risanamento-2026-10-08.md` | Documentazione/evidenza preesistente |
| Untracked | `tests/Feature/ArriviEsenzioniTest.php` | Test risanamento suite |
| Untracked | `tests/Feature/StrutturaSelezioneTest.php` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/SuiteResourceIsolationTest.php` | Test risanamento suite |
| Untracked | `tests/Feature/TassaBellariaAnteprimaTest.php` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/TassaBellariaCompletenessTest.php` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/TassaBellariaEtaCsvTest.php` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/TassaBellariaRiallineamentoTest.php` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/TassaBellariaVersionamentoTest.php` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/struttura-selezione.playwright.spec.js` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/tassa-bellaria-anteprima.playwright.spec.js` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/tassa-bellaria-configurazione.playwright.spec.js` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js` | Test/fixture Tassa, UI o regressione preesistenti |
| Untracked | `tests/Isolation/database_policy.py` | Harness/isolamento preesistente |
| Untracked | `tests/Isolation/struttura-selezione-fixtures.php` | Harness/isolamento preesistente |
| Untracked | `tests/Isolation/tassa-anteprima-fixtures.php` | Harness/isolamento preesistente |
| Untracked | `tests/Isolation/tassa-bellaria-fixtures.php` | Harness/isolamento preesistente |
| Untracked | `tests/Isolation/test_database_policy.py` | Harness/isolamento preesistente |
| Untracked | `tests/Support/IsolatedTestStorage.php` | Test risanamento suite |
| Untracked | `tests/Support/componenti-import-review-status.php` | Test/fixture Tassa, UI o regressione preesistenti |

## Stato Git finale e preservazione

Uniche modifiche permanenti **della presente fase**: nuovo `docs/baseline-applicativa-audit-2026-10-08.md` e appendice a `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`. Applicazione, configurazioni, migration, test e tutti i 24 untracked iniziali invariati byte per byte; snapshot iniziale dei 4.178 file in `/private/tmp/ids-baseline-audit-iniziale.json`. Il Maestro conserva integralmente i byte iniziali come prefisso. Nessun file preesistente eliminato, sovrascritto o ripristinato.

Finale: `main`; HEAD/origin/main locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, ahead/behind0/0; **29 tracked modificati +25 untracked**, staging vuoto. Worktree volutamente non pulito per i candidati preesistenti e il rapporto autorizzato. Nessun nuovo commit/fetch/push/deploy. Nessun DB operativo interrogato/modificato, nessuna credenziale reale letta o stampata, nessun server/Questura/ISTAT reale contattato. `git diff --check` finale PASS.

**CODICE MODIFICATO NO; TEST PREESISTENTI MODIFICATI NO; DOCUMENTAZIONE SÌ; CONFIGURAZIONE NO; SCHEMA/DATI OPERATIVI NO; TEST ESEGUITI SÌ; COMMIT NO; PUSH NO; DEPLOY NO; TRASMISSIONI ESTERNE NO.** Fixture e migration solo effimere. **BASELINE NON VALIDATA**, senza autorizzazione implicita a correzioni o produzione.

## Appendice — Riproduzione indipendente

Salvare il seguente wrapper fuori repository, per esempio `/private/tmp/ids-baseline-audit-riproduzione.py`, ed eseguirlo dalla root. Non usa il DB operativo e non va eseguito con Artisan direttamente sul checkout. La modalità backend finale riproduce 44/323 con quattro failure attese nel candidato attuale; modalità browser usa i due spec esistenti e la fixture esplicita. I nomi nei test sono sentinelle sintetiche. Le credenziali indicate sono solo nuove fixture effimere, non segreti operativi.

```sh
PATH=/private/tmp/ids-runtime-bin:/Users/Shared/Herd/services/mysql/8.0.36/bin:$PATH \
 python3 -B /private/tmp/ids-baseline-audit-riproduzione.py backend
PATH=/private/tmp/ids-runtime-bin:/Users/Shared/Herd/services/mysql/8.0.36/bin:$PATH \
 python3 -B /private/tmp/ids-baseline-audit-riproduzione.py browser
node --test tests/Unit/componenti-import-submit.test.mjs
```

```python
import sys,runpy,subprocess
from pathlib import Path
root=Path('/Users/jorgeluccitelli/Herd/Schedinedinotifica'); original=subprocess.run
probe=r'''<?php
namespace Tests\Feature;
use Tests\TestCase;
use Tests\Support\StrutturaFixtures;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
class IndependentBaselineAuditTest extends TestCase {
 use RefreshDatabase, StrutturaFixtures;
 public function test_login_logout_e_csrf(): void {
  $s=$this->structureFor(null); $u=$this->actor('struttura_user',null,$s->id);
  $u->update(['username'=>'audit-sintetico','password'=>Hash::make('Audit-Sintetico-123!')]);
  $this->get('/schedine')->assertRedirect('/login');
  $this->post('/login',['login'=>'audit-sintetico','password'=>'errata'])->assertSessionHasErrors('login');
  $this->assertGuest();
  $this->post('/login',['login'=>'audit-sintetico','password'=>'Audit-Sintetico-123!'])->assertRedirect();
  $this->assertAuthenticatedAs($u);
  $this->post('/logout',['_token'=>'ERRATO'])->assertStatus(419); $this->assertAuthenticatedAs($u);
  $this->post('/logout')->assertRedirect(); $this->assertGuest();
 }
 public function test_route_uscita_impersonazione_risolta_al_metodo_esci(): void {
  $route=app('router')->getRoutes()->match(Request::create('/superadmin/impersona/esci','POST'));
  $this->assertSame('esci',$route->getActionMethod());
 }
 public function test_uscita_impersonazione_ripristina_amministratore(): void {
  $super=$this->actor('super_admin'); $s=$this->structureFor(null); $target=$this->actor('struttura_user',null,$s->id);
  $this->actingAs($super)->post('/superadmin/impersona/'.$target->id)->assertRedirect();
  $this->assertAuthenticatedAs($target);
  $r=$this->post('/superadmin/impersona/esci');
  $r->assertRedirect(); $this->assertAuthenticatedAs($super);
 }
 public function test_web_checkin_rifiuta_parent_di_un_altro_tenant(): void {
  $a=$this->structureFor(null); $b=$this->structureFor(null);
  $guest=\App\Models\Schedina::forceCreate(['struttura_id'=>$b->id,'name'=>'RISERVATO-TENANT-B','surname'=>'Sintetico','arrive'=>'2026-06-01','departure'=>'2026-06-02','cant_people'=>1]);
  \App\Models\WebCheckinRichiesta::create(['struttura_id'=>$a->id,'schedina_id'=>$guest->id,'codice'=>'AUDIT','numero_prenotazione'=>'SINTETICA','email'=>'audit@example.invalid','nome_referente'=>'Tenant A','arrivo'=>'2026-06-01','partenza'=>'2026-06-02','token'=>str_repeat('a',64),'stato'=>'convertito']);
  $r=$this->get('/checkin/'.str_repeat('a',64));
  $r->assertDontSee('RISERVATO-TENANT-B');
  $this->assertContains($r->status(),[404,409,422]);
 }
 public function test_codici_annuali_e_circuiti_non_modificano_altro_tenant(): void {
  $a=$this->structureFor(null);$b=$this->structureFor(null);$this->actingAs($this->actor('struttura_user',null,$a->id));
  $records=[];foreach([[$a,2025,'schedina'],[$a,2026,'schedina'],[$a,2026,'arrivi'],[$b,2026,'schedina']] as [$s,$y,$c]) {
   $records[]=\App\Models\Schedina::forceCreate(['struttura_id'=>$s->id,'circuito'=>$c,'scheda'=>'LEGACY','name'=>'Sintetico','surname'=>'Anno','arrive'=>$y.'-06-01','departure'=>$y.'-06-02','cant_people'=>1]);
  }
  $ctrl=app(\App\Http\Controllers\SchedinaController::class);$m=new \ReflectionMethod($ctrl,'resequenceCircuitCodes');$m->invoke($ctrl,$a->id,'schedina');
  $this->assertSame('S-25001',$records[0]->fresh()->scheda);$this->assertSame('S-26001',$records[1]->fresh()->scheda);
  $this->assertSame('LEGACY',$records[2]->fresh()->scheda);$this->assertSame('LEGACY',\App\Models\Schedina::withoutGlobalScopes()->findOrFail($records[3]->id)->scheda);
 }
 public function test_schedina_non_collega_cliente_non_autorizzato(): void {
  $a=$this->structureFor(null);$b=$this->structureFor(null);
  $cliente=\App\Models\Customers::forceCreate(['struttura_id'=>$b->id,'name'=>'Cliente B','surname'=>'Sintetico']);
  $this->actingAs($this->actor('struttura_user',null,$a->id));
  $this->post(route('schedina.store'),['save_mode'=>'draft','customer_id'=>$cliente->id,'name'=>'Bozza A','surname'=>'Sintetico']);
  $this->assertSame(0,\App\Models\Schedina::withoutGlobalScopes()->where('struttura_id',$a->id)->where('customer_id',$cliente->id)->count(),'Una bozza non deve collegare un cliente non autorizzato.');
 }
 public function test_utente_disattivato_non_accede(): void {
  $u=$this->actor('super_admin'); $u->update(['username'=>'audit-off','attivo'=>false,'password'=>Hash::make('Audit-Sintetico-123!')]);
  $this->post('/login',['login'=>'audit-off','password'=>'Audit-Sintetico-123!'])->assertSessionHasErrors('login'); $this->assertGuest();
 }
}'''
def capture(cmd,*args,**kwargs):
 if isinstance(cmd,list) and len(cmd)>1 and cmd[1]=='vendor/bin/phpunit':
  path=Path(kwargs['cwd'])/'tests/Feature/IndependentBaselineAuditTest.php';path.write_text(probe)
  cmd=[*cmd,'tests/Feature/IndependentBaselineAuditTest.php']
 result=original(cmd,*args,**kwargs)
 if isinstance(cmd,list) and len(cmd)>1 and cmd[1]=='vendor/bin/phpunit':
  Path('/private/tmp/ids-baseline-audit-integrita-phpunit.log').write_text((result.stdout or '')+(result.stderr or ''))
 return result
subprocess.run=capture
sys.path.insert(0,str(root/'tests/Isolation'))
mode=sys.argv[1]
args=['--include','app/Models/TassaExport.php','database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php']
if mode=='backend': args+=['--phpunit','tests/Feature/AccessTest.php','tests/Feature/TenancyTest.php','tests/Feature/CestinoSecurityTest.php','tests/Feature/SchedinaStoreTest.php']
else: args+=['--fixtures','tests/Isolation/struttura-selezione-fixtures.php','--playwright','tests/Feature/struttura-selezione.playwright.spec.js','tests/Feature/tassa-bellaria-anteprima.playwright.spec.js']
sys.argv=[str(root/'tests/Isolation/run.py'),*args];runpy.run_path(str(root/'tests/Isolation/run.py'),run_name='__main__')

```

## Appendice — Copertura della suite precedente, per classe

Estratta dal JUnit della prima globale precedente, non nuova esecuzione. Tutti i582casi sono verdi nelle tre globali; le classi con lo stesso nome Example sono aggregate in questa tabella.

| Classe | Casi | Asserzioni |
|---|---:|---:|
| `ComponentiImportFormTest` | 16 | 103 |
| `ComponentiImportP1Test` | 6 | 40 |
| `ComponentiImportReviewStatusTest` | 1 | 2 |
| `ComponentiImportServiceTest` | 67 | 248 |
| `ContrattoImportazioneComponentiV1Test` | 9 | 23 |
| `CustomerImportServiceTest` | 8 | 77 |
| `DatiComponenteNormalizzatiTest` | 26 | 43 |
| `EtaOperativaTest` | 10 | 15 |
| `ExampleTest` | 2 | 6 |
| `PianoSyncComponentiTest` | 17 | 39 |
| `SingleNodeBatchLockTest` | 3 | 8 |
| `TassaDiSoggiornoServiceTest` | 2 | 14 |
| `TipoAlloggiatoCatalogoTest` | 11 | 21 |
| `AccessTest` | 4 | 11 |
| `ArriviEsenzioniTest` | 1 | 8 |
| `CestinoSecurityTest` | 29 | 239 |
| `CustomerImportedFiltersDeleteTest` | 2 | 7 |
| `GeoComuneLogoTest` | 13 | 132 |
| `IstatConformitaTest` | 29 | 133 |
| `IstatCycleTest` | 33 | 263 |
| `IstatTransmissionSecurityTest` | 3 | 14 |
| `QuesturaBoundaryTest` | 16 | 236 |
| `QuesturaC1MigrationAcceptanceTest` | 1 | 32 |
| `QuesturaConclusiveAuditTest` | 9 | 114 |
| `QuesturaCredentialEncryptionTest` | 5 | 26 |
| `QuesturaFinalAuditTest` | 22 | 284 |
| `QuesturaIdempotencyTest` | 18 | 200 |
| `QuesturaLegacyMigrationTest` | 1 | 5 |
| `QuesturaLoggingPrivacyTest` | 17 | 1290 |
| `QuesturaPayloadCoherenceTest` | 23 | 214 |
| `QuesturaRetentionTest` | 21 | 138 |
| `QuesturaTestSendSnapshotTest` | 2 | 32 |
| `QuesturaTransportGuardTest` | 55 | 906 |
| `QuesturaUxTest` | 9 | 146 |
| `QuesturaWsContractTest` | 5 | 33 |
| `SchedinaStoreTest` | 3 | 39 |
| `SmokePagesQaTest` | 4 | 13 |
| `StrutturaAuthorizationTest` | 22 | 84 |
| `StrutturaSelezioneTest` | 3 | 26 |
| `SuiteResourceIsolationTest` | 2 | 7 |
| `TassaBellariaAnteprimaTest` | 8 | 112 |
| `TassaBellariaCompletenessTest` | 18 | 976 |
| `TassaBellariaConfigurazioneAutomaticaTest` | 6 | 209 |
| `TassaBellariaEtaCsvTest` | 9 | 164 |
| `TassaBellariaRiallineamentoTest` | 4 | 45 |
| `TassaBellariaVersionamentoTest` | 5 | 66 |
| `TenancyTest` | 1 | 4 |
| `TopbarRenderingTest` | 1 | 3 |


## Correzioni autorizzate A01–A03 e verifica conclusiva

**BASELINE NON VALIDATA — BASELINE IN AUDIT.** Correzioni circoscritte autorizzate dall’utente; nessuna autorizzazione a correggere altri difetti. Nessuna certificazione normativa o produttiva.

### Cause e interventi

| Difetto | Causa originale | Correzione e prova |
|---|---|---|
| A01, P1 | La route dinamica `impersona/{userId}` intercettava `esci`; il gruppo super_admin impediva comunque l’uscita dopo il cambio identità | ID numerico nella route di ingresso; uscita autenticata separata dal vincolo ruolo e dal servizio della struttura impersonata. `esci()` verifica utente corrente, tre marker sessione, log aperto e coerente, amministratore originale attivo; aggiorna il log sotto lock/transazione, ripristina identità, rigenera sessione e cancella il contesto tenant. Form POST/CSRF nella topbar ufficiale. 14 casi /112 asserzioni: quattro ruoli target, sessioni/log incoerenti, originale disattivato/non amministratore, servizio scaduto, CSRF e accessi non autorizzati |
| A02, P1 | Il parent della richiesta Web Check-in era caricato senza verificarne l’appartenenza alla struttura della richiesta | `linkedSchedinaForRichiesta()` rifiuta con404 parent mancante o di altro tenant prima di dati, token o scritture; `ensureLinkedSchedina()` e `findOwnedRichiesta()` condividono il controllo. Conservata la creazione legacy quando l’ID non è valorizzato. 14 casi /47 asserzioni: sei percorsi pubblici GET/POST/completato, cinque gestionali, parent inesistente, positivo e tenant estraneo |
| A03, P1 | Il resolver rifiutava il cliente estero ma il payload bozza ripiegava sull’ID grezzo della richiesta | `authorizedCustomerReference()` valida formato, esistenza e tenant/catena autorizzata prima dei rami store/update. Payload solo dal cliente autorizzato; localizzazione di catena preservata. Transazione del salvataggio normale, come già nel ramo importato, per evitare scritture parziali quando un riferimento componente è rifiutato. 34 casi /143 asserzioni: store/update, cinque modalità, ID estero/inesistente/malformato, cliente proprio, riuso/localizzazione di catena e rollback per componente estero |

Il collegamento incoerente A02 è introdotto esclusivamente dalla fixture diagnostica: **non è dimostrato che un utente ordinario possa crearlo**. A03 dimostra il riferimento persistito, non la lettura/modifica del cliente estero. Nessuna riscrittura di dati esistenti. Formattazione Pint del controller WebCheckin inclusa, senza ulteriori modifiche funzionali.

### Prove PHP effettive

| Verifica | Test | Asserzioni | Esito |
|---|---:|---:|---|
| Riproduzione prima delle correzioni, sette diagnostici originali più37 esistenti |44|323|4fallimenti attesi,0errori |
| Stessi diagnostici originali dopo le correzioni, byte immutati |44|327|PASS |
| Tre nuove classi di sicurezza più regressioni store/clienti/selezione |70|374|PASS |
| Discovery completa |644 casi unici|—|PASS |
| Globale1 |644|7152|PASS |
| Globale2, stesso endpoint e runtime con DB reinizializzato |644|7152|PASS |
| Globale casuale, seed4208 |644|7152|PASS |

Tutte le tre globali: **0failure,0errori,0skip,0deprecazioni**; inventario e asserzioni per caso identici. Comprendono Questura204/3656,ISTAT65/410,Tassa52/1586. Non dedurre integrazione esterna o tutte le permutazioni da questi risultati.

Esiti intermedi conservati: prima verifica dopo il fix44/294 con3failure, causate dal callback di `DB::transaction` che riceve la connessione invece dell’argomento opzionale di persistenza; corretto con closure esplicita. Prima nuova matrice70/374 con11failure: aspettativa404 invece405 per la route non numerica, confronti raw di modelli non ricaricati e chiave di errore componente diversa dal contratto preesistente. Rettificate soltanto le nuove prove; diagnostici originali e test preesistenti immutati. Nessuna aspettativa di sicurezza indebolita.

Comandi riproducibili, sempre con PHP sicuro e launcher isolato:

```sh
PATH=/private/tmp/ids-runtime-bin:/Users/Shared/Herd/services/mysql/8.0.36/bin:$PATH \
python3 -B tests/Isolation/run.py \
 --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php \
 --phpunit-global --phpunit-repeat 2
# Stesso launcher: --phpunit-global --phpunit-random-seed 4208
# Discovery: --phpunit-global --phpunit-discovery
```

Evidenze locali esterne al repository: `/private/tmp/ids-p1-verificato-globali-{1,2}.{log,xml}`, `/private/tmp/ids-p1-verificato-random-1.{log,xml}`, `/private/tmp/ids-p1-verificato-p1-1.{log,xml}`, `/private/tmp/ids-p1-verificato-discovery-1.log`, `/private/tmp/ids-p1-autorizzati-originali-finali-phpunit.log`. I wrapper esterni catturano output e inseriscono i diagnostici soltanto nel checkout temporaneo. Non sono file da includere in Git.

### Browser e controlli

**Browser reale Playwright:8/8PASS**, launcher conclusivo exit0 e cleanup confermato (`/private/tmp/ids-p1-browser-conclusivi.log`). Impersonazione da SuperAdmin → target → paginaSuperAdmin403 → menu ufficiale → uscita → identità originale e paginaSuperAdmin200; navigazione dashboard/clienti/nuovo/Schedine/nuova/Arrivi/nuovo/WebCheckin, form modifica e pannello Componenti visibili, logout e link pubblico con token completo visibile. Cinque scenari selezione struttura comprendono una/multi/vuota, personale limitato, persistenza, cambio tenant, rapporto/CSV/consolidamento/storico. Uno verifica anteprime da più ingressi, importozero e positivo e PDF leggibili. Nessuna prova di invio esterno.

Prime prove browser1/2 e7/8PASS: il test interagiva prima della conferma SweetAlert e cercava il nome accessibile esatto senza l’icona. Corrette soltanto sincronizzazione e selettore; nessun clickforzato/manipolazioneDOM. Il comando era presente nella topbar. L’esecuzione conclusiva non presentaIncompleteRead; questo non risolve A05 né ne identifica la causa originaria. Il link breve A06 è verificato separatamente come prova negativa, non incluso fra gli otto scenari verdi.

```sh
PATH=/private/tmp/ids-runtime-bin:/Users/Shared/Herd/services/mysql/8.0.36/bin:$PATH \
python3 -B tests/Isolation/run.py \
 --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php \
 --fixtures tests/Isolation/p1-baseline-fixtures.php \
 --playwright tests/Feature/p1-baseline.playwright.spec.js tests/Feature/struttura-selezione.playwright.spec.js tests/Feature/tassa-bellaria-anteprima.playwright.spec.js
```

Tutti runtime di questa fase fermati e rimossi, inclusi quelli con esito negativo. La verifica UI dei form e del pannello Componenti non certifica ogni modalità di importazione: la matrice iniziale conserva i suoi limiti.

Controlli eseguiti:5guardrail,10policyDB,14SOAP in memoria,22ISTAT in memoria PASS; Pint8PHP PASS; sintassi8PHP,4Python e nuovo JavaScript PASS. Nessun cambio ai moduli certificati. La protezione browser interrompe richieste fuori origine e il proxy valida le richieste locali. DB/storage/processi esclusivamente effimeri, identità/riconnessione e23 rifiuti di override verificati prima delle migration.

### Difetti e anomalie residue

- **A06 — P1 preesistente, link pubblico breve Web Check-in non funzionante.** `publicUrl()` genera `/w/{codice}-{primi8caratteriToken}`; la route usa `publicShow()`, che cerca invece il token completo. Fixture parent/tenant corretti: `/checkin/{tokenCompleto}`200, URL generato404. Prova indipendente esterna in copia temporanea:15test/49asserzioni,1failure,0errori; gli altri14 di sicurezza passano. Evidenze `/private/tmp/ids-p1-link-breve.py`, `/private/tmp/ids-p1-link-breve-phpunit.log`. Generatore, route e ricerca sono già presenti inHEAD: non regressione di questa fase. Nessuna correzione applicata; richiede autorizzazione separata. L’aspettativa200 è mantenuta, non trasformata in prova verde del404.
- **A04 — P2 diagnostici JavaScript:** ripetuti3/3 rossi, `submitter.matches is not a function` e `componentIndexInput is not defined`. Il test VM estrae il listener senza il DOM e i binding esterni dichiarati nella partial; sorgente e diagnostici invariati. Nessuna prova sufficiente di guasto nel DOM reale, nessun fix estraneo applicato. Log `/private/tmp/ids-p1-js-residui.log`.
- **A05 — P2 proxy:** riprodotto in memoria, senza rete, `HTTPResponse.read()` con Content-Length10 e corpo3byte →IncompleteRead(3,7). Il proxy legge il corpo prima dell’invio e non intercetta questa eccezione; URL e causa dell’interruzione originale ancora non identificati. Evidenza `/private/tmp/ids-p1-proxy-diagnosi.log`. Non attribuire automaticamente l’anomalia a cancellazione navigazione. Nessuna modifica harness.

### File di questa fase e preservazione

Cinque applicativi: `routes/web.php`, `app/Http/Controllers/Superadmin/ImpersonazioneController.php`, `app/Http/Controllers/WebCheckinController.php`, `app/Http/Controllers/SchedinaController.php`, `resources/views/layouts/topbar.blade.php`.

Cinque nuovi test/fixture: `tests/Feature/ImpersonazioneSecurityTest.php`, `tests/Feature/WebCheckinParentSecurityTest.php`, `tests/Feature/SchedinaCustomerReferenceSecurityTest.php`, `tests/Feature/p1-baseline.playwright.spec.js`, `tests/Isolation/p1-baseline-fixtures.php`.

Due documenti aggiornati: questo rapporto e `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`. Totale12file nello scope. Nessun file precedente rimosso; le sole estensioni ai file iniziali sono i cinque applicativi e i due documenti autorizzati. Questura/ISTAT/Tassa, migration, configurazioni, harness e tutti i test precedenti byte-identici allo snapshot iniziale di4179file, salvo i punti condivisi Schedina/routes già esplicitamente autorizzati. Nessun intervento sui contratti fiscali.

Git locale:main,HEAD/origin locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`,ahead/behind0/0,stagingvuoto;32tracked modificati+30untracked. Nessunfetch,commit,push,deploy,SPanel,DBoperativo,trasmissione reale.

Inventario completo finale:

```text
 M app/Http/Controllers/SchedinaController.php
 M app/Http/Controllers/Superadmin/ImpersonazioneController.php
 M app/Http/Controllers/TassaDiSoggiornoController.php
 M app/Http/Controllers/TassaEsenzioneController.php
 M app/Http/Controllers/TassaReportController.php
 M app/Http/Controllers/WebCheckinController.php
 M app/Models/TassaDiSoggiorno.php
 M app/Services/TassaDiSoggiornoService.php
 M docs/DEPLOY_SPANEL.md
 M docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md
 M resources/views/layouts/topbar.blade.php
 M resources/views/schedina/list.blade.php
 M resources/views/schedina/partials/form.blade.php
 M resources/views/schedina/print-tassa.blade.php
 M resources/views/struttura/seleziona.blade.php
 M resources/views/tassa_di_soggiorno/edit.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-print.blade.php
 M resources/views/tassa_di_soggiorno/rapporto.blade.php
 M routes/web.php
 M tests/Feature/AccessTest.php
 M tests/Feature/CustomerImportedFiltersDeleteTest.php
 M tests/Feature/ExampleTest.php
 M tests/Feature/QuesturaC1MigrationAcceptanceTest.php
 M tests/Feature/SmokePagesQaTest.php
 M tests/Feature/TenancyTest.php
 M tests/Isolation/run.py
 M tests/Support/TestingEnvironment.php
 M tests/TestCase.php
 M tests/Unit/ComponentiImportFormTest.php
 M tests/Unit/ComponentiImportReviewStatusTest.php
 M tests/Unit/ComponentiImportServiceTest.php
?? app/Models/TassaExport.php
?? database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
?? docs/baseline-applicativa-audit-2026-10-08.md
?? docs/imposta-soggiorno-bellaria-audit-2026-10-07.md
?? docs/suite-globale-risanamento-2026-10-08.md
?? tests/Feature/ArriviEsenzioniTest.php
?? tests/Feature/ImpersonazioneSecurityTest.php
?? tests/Feature/SchedinaCustomerReferenceSecurityTest.php
?? tests/Feature/StrutturaSelezioneTest.php
?? tests/Feature/SuiteResourceIsolationTest.php
?? tests/Feature/TassaBellariaAnteprimaTest.php
?? tests/Feature/TassaBellariaCompletenessTest.php
?? tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php
?? tests/Feature/TassaBellariaEtaCsvTest.php
?? tests/Feature/TassaBellariaRiallineamentoTest.php
?? tests/Feature/TassaBellariaVersionamentoTest.php
?? tests/Feature/WebCheckinParentSecurityTest.php
?? tests/Feature/p1-baseline.playwright.spec.js
?? tests/Feature/struttura-selezione.playwright.spec.js
?? tests/Feature/tassa-bellaria-anteprima.playwright.spec.js
?? tests/Feature/tassa-bellaria-configurazione.playwright.spec.js
?? tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js
?? tests/Isolation/database_policy.py
?? tests/Isolation/p1-baseline-fixtures.php
?? tests/Isolation/struttura-selezione-fixtures.php
?? tests/Isolation/tassa-anteprima-fixtures.php
?? tests/Isolation/tassa-bellaria-fixtures.php
?? tests/Isolation/test_database_policy.py
?? tests/Support/IsolatedTestStorage.php
?? tests/Support/componenti-import-review-status.php
```

### Verdetto e prossimo gate

**A01–A03 corretti e verificati. BASELINE NON VALIDATA.** A06 impedisce la chiusura funzionale del percorso invito Web Check-in breve. A04/A05 e le lacune della matrice iniziale (UI/importazioni/amministrazione/sessioni/cicli completi e acceptance esterna) restano da chiudere con prove e autorizzazioni appropriate. Gate Rocky/SPanel e produzione invariati. Nessuna correzione fuori scope o readiness dedotta dalla suite verde.


### Riproduzione indipendente A06 (da eseguire soltanto nel checkout effimero)

```php
public function test_link_breve_generato_deve_aprire_parent_autorizzato(): void
{
    $s = $this->structureFor(null); // StrutturaFixtures, RefreshDatabase
    $p = \App\Models\Schedina::forceCreate([
        'struttura_id' => $s->id, 'name' => 'Sintetico', 'surname' => 'Link',
        'arrive' => '2026-06-01', 'departure' => '2026-06-02', 'cant_people' => 1,
    ]);
    $r = \App\Models\WebCheckinRichiesta::create([
        'struttura_id' => $s->id, 'schedina_id' => $p->id, 'codice' => 'AUDIT',
        'numero_prenotazione' => 'SINTETICA', 'email' => 'audit@example.invalid',
        'nome_referente' => 'Sintetico', 'arrivo' => '2026-06-01',
        'partenza' => '2026-06-02', 'token' => str_repeat('a', 64), 'stato' => 'convertito',
    ]);
    $this->get('/checkin/'.$r->token)->assertOk(); // PASS
    $m = new \ReflectionMethod(\App\Http\Controllers\WebCheckinController::class, 'publicUrl');
    $url = $m->invoke(app(\App\Http\Controllers\WebCheckinController::class), $r);
    $this->get($url)->assertOk(); // FAIL: 404 anziché200
}
```

Da autorizzare separatamente: allineare le route brevi ai metodi previsti, conservando il nuovo controllo tenant in ogni percorso, con regressioni GET/POST/completato, token invalidi/ambigui e parent incoerenti. Non estendere automaticamente l’autorizzazione corrente.


## Correzione autorizzata A06 — link breve Web Check-in

**BASELINE IN AUDIT — NON VALIDATA.** Autorizzazione dell’utente circoscritta al link breve, alle regressioni e alla documentazione. Le sezioni A06 precedenti descrivono la diagnosi storica; questa appendice registra l’intervento successivo. Rischio MEDIO: risoluzione di una credenziale pubblica e compatibilità dei link già emessi.

### Percorso e causa dimostrata

`store()` crea `codice` e token casuale64, persistiti in `web_checkin_richieste`; il link breve non è un ulteriore campo DB. `publicAccessKey()` calcola codice + trattino + primi8caratteriToken; `publicUrl()` usa la route nominata `web_checkin.public.short.show`. `edit()`, testo email e WhatsApp impiegano lo stesso URL. Nessuna email/WhatsApp è stata inviata nelle prove.

Le tre route `/w/{token}` GET/POST e `/w/{token}/completato` chiamavano invece i metodi che cercano `where('token', tokenCompleto)`. Con un parent corretto, il link generato dall’interfaccia era404, mentre `/checkin/{tokenCompleto}`200. Erano già presenti `publicInvite`, `publicStoreShort`, `publicCompletedShort` e la vista ufficiale di invito, ma non erano collegati alle route brevi.

Riproduzione HTTP prima del fix:82test/367asserzioni,7failure,0errori (20nuovi e62sicurezza preesistenti). Browser prima del fix:1scenario fallito esattamente sull’attesa200 dopo apertura del link generato dalla schermata gestionale: ottenuto404. Il test legge il campo reale dell’interfaccia, non costruisce solo un URL ipotetico. Log `/private/tmp/ids-web-short-http-red.log` e `/private/tmp/ids-web-short-browser-red.log`.

### Correzione minima e limiti dei riferimenti

- Route brevi instradate a `publicShowShort`, `publicStoreShort`, `publicCompletedShort`, nomi e path invariati. Le route `/checkin/` e i loro metodi rimangono invariati.
- `publicShowShort()` conserva l’apertura originale quando il valore corrisponde esattamente a un token completo; altrimenti apre l’invito breve. Il resolver dà precedenza alla ricerca esatta completa anche per POST/completato. Conservati anche formati token legacy ammessi dal campo DB, non soltanto i64alfa-numerici generati oggi.
- Il resolver breve accetta codice alfa-numerico1..30 e prefisso alfa-numerico **esattamente8**. Niente wildcard SQL `%`/`_`, prefissi parziali o separatori extra. Richiede un solo candidato, token persistito64alfa-numerico, codice e prefisso identici mediante confronto PHP, senza affidarsi alla collation MySQL per la distinzione maiuscole/minuscole. La unique constraint sul codice esiste già; nessuna migration o vincolo modificati. Il rifiuto di più candidati è difesa nel codice; non è stato alterato lo schema per fabbricare una duplicazione impossibile sotto quel vincolo.
- Il controllo tenant del parent introdotto conA02 è **invariato**. Invito →ensureLinkedSchedina; salvataggio/completamento →metodi completi→stesso controllo. Nessun bypass di proprietà o di CSRF. Parent valorizzato mancante/estero404 prima di letture/scritture; il comportamento legacy di creazione quando parentID è nullo rimane quello preesistente.

Nessuna modifica a A01/A03, ai metodi del controllo A02, a Questura/ISTAT/Tassa, allo storico o alla configurazione. Il possesso di un link valido resta la credenziale del percorso pubblico anonimo: le prove cross-tenant riguardano parent incoerenti, combinazione codice/prefisso di tenant diversi e gestione privata non autorizzata; non trasformano il percorso pubblico in una pagina riservata alla sessione gestionale.

### Matrice e risultati conclusivi

| Requisito | Prova |
|---|---|
| URL realmente generato, invito e CTA | Campo interfaccia, pagina invito visibile, apertura sul token completo |
| Token completi | GET/POST/completato e alias `/w/`, generati e legacy di lunghezza/formato diversi |
| Richiesta aperta | POST breve autorizzato, aggiornamento parent proprio, stato in_compilazione, riapertura e POST completo |
| Richiesta convertita | GET/POST/completato disponibili, payload ostile non modifica parent o richiesta |
| Token invalidi | Inesistente, troppo corto/lungo, wildcard, extra separator, prefisso con case errato;404 senza dati o scritture |
| Tenant | Parent estero/inesistente rifiutato; codiceA+prefissoB rifiutato; gestione privata diB daA404 |
| CSRF | Token errato419 senza scritture; POST autorizzato con CSRF valido funziona |
| Revoca | Eliminazione richiesta e rotazione che cambia prefisso rendono i vecchi link indisponibili; completo precedente invalidato |
| Scadenza | Avanzamento sintetico fino2030: date soggiorno non fanno scadere il token; assenza di politica temporale dimostrata, non scadenza certificata |
| Regressioni precedenti | Tre classi P1 precedenti immutate, incluse nei mirati/globali; Questura/ISTAT/Tassa nella suite e Safety offline |

| Esecuzione finale | Risultato |
|---|---|
| Mirati A06 piùA01/A02/A03 |84test/412asserzioniPASS,0failure/0errori/0skip/deprecazioni |
| Discovery |666casi uniciPASS |
| Globale1 |666test/7262asserzioniPASS |
| Globale2, stesso endpoint/runtime con DB reinizializzato |666test/7262asserzioniPASS |
| Globale casuale, seed4208 |666test/7262asserzioniPASS |
| Browser conclusivo |4PASS/1FAIL: il solo caso non convertito Bellaria è bloccato daA07 |
| Diagnostico indipendente A07 |23test/112asserzioni,1failure,0errori;22A06PASS più1nuovo diagnostico esterno |

Le tre globali hanno **zero failure/errori/skip/deprecazioni**, inventario e asserzioni per caso identici. Comprendono Questura204/3656,ISTAT65/410,Tassa52/1586, treclassiP1precedenti62/302, A06nuovo22/110. La suite globale **non comprende il diagnostico esterno A07**: questo fallimento e quello browser impediscono l’accettazione end-to-end anche con le globali verdi. Nessun test esistente modificato o escluso per renderle positive.

Pint4file PASS, lint4PHP e JavaScriptPASS,5guardrail/10policyDB/14SOAPinmemoria/22ISTATinmemoria/diffcheckPASS. Nessun test verso servizi reali. NessunIncompleteRead nel browser conclusivo;A05non è risolto da questa osservazione. Tutti runtime della fase fermati/rimossi anche dopo errori o fallimenti.

Evidenze esterne: `/private/tmp/ids-web-short-finali-globali-{1,2}.{log,xml}`, `/private/tmp/ids-web-short-finali-random-1.{log,xml}`, `/private/tmp/ids-web-short-finali-short-1.{log,xml}`, `/private/tmp/ids-web-short-finali-discovery-1.log`, `/private/tmp/ids-web-short-browser-conclusivi.log`, `/private/tmp/ids-web-short-a07.py`, `/private/tmp/ids-web-short-a07-phpunit.log`. Wrapper/output sono esterni al repository, non materiale da includere indiscriminatamente in Git.

### Esiti intermedi e trasparenza

Prima matrice dopo l’instradamento83test/394asserzioniPASS. Nuova prova di compatibilità con token legacy ammessi dallo schema:84test/397asserzioni,1failure sul primo alias `/w/`, mentre il percorso completo200. La prima discriminazione64caratteri restringeva indebitamente l’alias legacy: completata la correzione con ricerca esatta prioritaria. Nessuna aspettativa indebolita, le prove prima e dopo conservate. Tre globali intermedie665/7244PASS non sostituiscono le globali con il resolver finale.

Prima browser estesa:2PASS e3failure prima dell’apertura delle pagine del link breve; setup sintetico interrompeva la lettura del parent per lo scope tenant. Solo la fixture legge esplicitamente il parent noto senza scope, poi mantiene il tenant originario; nessun cambiamento al codice di autorizzazione. Le prove successive devono superare i reali controlli applicativi.

### A07 — nuovo P1 distinto: form anonimo Bellaria bloccato

**Accettazione complessiva BLOCCATA.** Link breve valido200 e invito visibile; CTA al token completo di richiesta non convertita porta al rifiuto fiscale. Nella fixture conclusiva è presente una configurazione Bellaria2026 valida (hotel3stelle,aliquota1.50,tetto6,1giugno–30settembre,età17/18). La configurazione è presente nel DB, ma `SchedinaController::loadTassaContext()` usa `TassaDiSoggiorno::query()` con il trait `AppartieneAStruttura`: utente anonimo →allowedIds vuoti→`whereRaw('1 = 0')`. Il metodo condiviso restituisce quindi null, il service rifiuta correttamente il calcolo inaffidabile; HTML302 con errore, JSON422 `configurazione_tassa`. Non c’è un fallback fiscale da rimuovere né una tariffa da cambiare.

Diagnostico indipendente in copia effimera:1record configurazione confermato con queryDB non scoped; invocazione del metodo di lettura restituisceNULL; JSON422 con chiaveconfigurazione_tassa; aspettativaHTML200 mantenuta e fallita(302). Script esterno indicato sopra. Browser:invito200/CTA visibile, dopo click compare la richiesta di configurazione e il form non è visibile. Fallimento preservato nel nuovo spec permanente, non neutralizzato con fixture fuori Bellaria o con aspettativa di redirect.

È un difetto distinto preesistente al nuovo instradamento: `publicShow`, `loadTassaContext`, trait fiscale e service sono invariati in questa fase. Le prove A06convertito non attraversano il calcolo del form, mentre questo caso nuovo sì. Nessuna regressione delle regole fiscali certificata o attribuita senza prova. **Nessuna correzione A07 applicata**, perché l’autorizzazione corrente riguardaA06 e vieta interventi estranei. Proposta da autorizzare: lettura del contesto fiscale nel percorso pubblico esclusivamente per la struttura della richiesta già risolta e del parent già verificato, lasciando invariati gli scope gestionali e le regole/calcoli; regressioni anonimo, tenant, record assente/invalido e render/form reale. Non passare a un generico querysenza scope senza controllo tenant.

Seconda prova browser intermedia3PASS/2FAIL: fixture negativa del parent estero inserita nello stesso tenant del percorso positivo faceva bloccare legittimamente l’indice conA02; spostata soltanto la richiesta incoerente nell’altro tenant sintetico. Il caso non convertito mostrava inoltre configurazione non presente nel setup; aggiunta la configurazione2026 già certificata. La conclusiva4PASS/1FAIL dimostra che il rifiuto anonimo resta anche con configurazione valida: non è stato etichettato come semplice difetto fixture.

### Rischi residui e autorizzazioni successive

**Nessuna scadenza temporale o funzione dedicata di revoca/rotazione nell’interfaccia è presente nello schema/codice esaminato.** Non è stata inventata una durata. Il link breve espone solo8caratteri del token: la rotazione lo invalida se cambia quel prefisso; mantenere lo stesso codice/prefisso mantiene lo stesso link. Nessuna garanzia di revoca completa per rotazioni che ne conservino il prefisso è dichiarata. I token non vengono mostrati nel rapporto né letti dal DB operativo. Durata, ciclo di revoca, robustezza dei bearer link e contrasto alla ricerca automatizzata richiedono un requisito e audit separati prima della validazione generale della sicurezza Web Check-in.

A04 diagnosticiJavaScript eA05proxy rimangono aperti/invariati, nessuna correzione autorizzata in questa fase. Gate e lacune della baseline precedente restano aperti. Nessuna nuova readiness normativa/produttiva.

### Inventario della fase e Git

Sette file nello scope:

1. `routes/web.php`: solo tre route brevi.
2. `app/Http/Controllers/WebCheckinController.php`: adattatore breve e resolver rigoroso, compatibilità completa preservata.
3. `tests/Feature/WebCheckinShortLinkTest.php`: nuove regressioni HTTP sintetiche.
4. `tests/Feature/web-checkin-short.playwright.spec.js`: prove di apertura/form/salvataggio e rifiuti browser.
5. `tests/Isolation/web-checkin-short-fixtures.php`: fixture esplicita soltanto nel runtime isolato, riutilizza i setup precedenti senza modificarli.
6. Questo rapporto: evidenze, limiti e inventario.
7. `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`: conoscenza aggiornata, stato invariato.

Snapshot iniziale4184file; quattro estensioni autorizzate a file già presenti (controller/routes/due documenti), tre nuovi test/fixture. Nessuna cancellazione o sovrascrittura del lavoro precedente; gli altri4180file sono byte-identici. I test precedenti P1, harness, moduli certificati e migration sono preservati. Stato finale riconfermato:main,HEAD/origin locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`,0/0,stagingvuoto,32tracked modificati+33untracked. Nessunfetch/commit/push/deploy/server/DBoperativo/trasmissione reale.

Inventario finale del worktree (modifiche delle fasi precedenti incluse e preservate):

```text
 M app/Http/Controllers/SchedinaController.php
 M app/Http/Controllers/Superadmin/ImpersonazioneController.php
 M app/Http/Controllers/TassaDiSoggiornoController.php
 M app/Http/Controllers/TassaEsenzioneController.php
 M app/Http/Controllers/TassaReportController.php
 M app/Http/Controllers/WebCheckinController.php
 M app/Models/TassaDiSoggiorno.php
 M app/Services/TassaDiSoggiornoService.php
 M docs/DEPLOY_SPANEL.md
 M docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md
 M resources/views/layouts/topbar.blade.php
 M resources/views/schedina/list.blade.php
 M resources/views/schedina/partials/form.blade.php
 M resources/views/schedina/print-tassa.blade.php
 M resources/views/struttura/seleziona.blade.php
 M resources/views/tassa_di_soggiorno/edit.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-print.blade.php
 M resources/views/tassa_di_soggiorno/rapporto.blade.php
 M routes/web.php
 M tests/Feature/AccessTest.php
 M tests/Feature/CustomerImportedFiltersDeleteTest.php
 M tests/Feature/ExampleTest.php
 M tests/Feature/QuesturaC1MigrationAcceptanceTest.php
 M tests/Feature/SmokePagesQaTest.php
 M tests/Feature/TenancyTest.php
 M tests/Isolation/run.py
 M tests/Support/TestingEnvironment.php
 M tests/TestCase.php
 M tests/Unit/ComponentiImportFormTest.php
 M tests/Unit/ComponentiImportReviewStatusTest.php
 M tests/Unit/ComponentiImportServiceTest.php
?? app/Models/TassaExport.php
?? database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
?? docs/baseline-applicativa-audit-2026-10-08.md
?? docs/imposta-soggiorno-bellaria-audit-2026-10-07.md
?? docs/suite-globale-risanamento-2026-10-08.md
?? tests/Feature/ArriviEsenzioniTest.php
?? tests/Feature/ImpersonazioneSecurityTest.php
?? tests/Feature/SchedinaCustomerReferenceSecurityTest.php
?? tests/Feature/StrutturaSelezioneTest.php
?? tests/Feature/SuiteResourceIsolationTest.php
?? tests/Feature/TassaBellariaAnteprimaTest.php
?? tests/Feature/TassaBellariaCompletenessTest.php
?? tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php
?? tests/Feature/TassaBellariaEtaCsvTest.php
?? tests/Feature/TassaBellariaRiallineamentoTest.php
?? tests/Feature/TassaBellariaVersionamentoTest.php
?? tests/Feature/WebCheckinParentSecurityTest.php
?? tests/Feature/WebCheckinShortLinkTest.php
?? tests/Feature/p1-baseline.playwright.spec.js
?? tests/Feature/struttura-selezione.playwright.spec.js
?? tests/Feature/tassa-bellaria-anteprima.playwright.spec.js
?? tests/Feature/tassa-bellaria-configurazione.playwright.spec.js
?? tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js
?? tests/Feature/web-checkin-short.playwright.spec.js
?? tests/Isolation/database_policy.py
?? tests/Isolation/p1-baseline-fixtures.php
?? tests/Isolation/struttura-selezione-fixtures.php
?? tests/Isolation/tassa-anteprima-fixtures.php
?? tests/Isolation/tassa-bellaria-fixtures.php
?? tests/Isolation/test_database_policy.py
?? tests/Isolation/web-checkin-short-fixtures.php
?? tests/Support/IsolatedTestStorage.php
?? tests/Support/componenti-import-review-status.php
```

### Comandi di verifica riproducibili

PHP sicuro e PATH MySQL locali; sempre `tests/Isolation/run.py`, senza riuso DB o server:

```sh
PATH=/private/tmp/ids-runtime-bin:/Users/Shared/Herd/services/mysql/8.0.36/bin:$PATH \
python3 -B tests/Isolation/run.py \
 --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php \
 --phpunit tests/Feature/WebCheckinShortLinkTest.php tests/Feature/WebCheckinParentSecurityTest.php tests/Feature/ImpersonazioneSecurityTest.php tests/Feature/SchedinaCustomerReferenceSecurityTest.php
# Stesso launcher: --phpunit-global --phpunit-repeat 2
# Ordine casuale: --phpunit-global --phpunit-random-seed 4208
# Discovery: --phpunit-global --phpunit-discovery
# Browser: --fixtures tests/Isolation/web-checkin-short-fixtures.php
#          --playwright tests/Feature/web-checkin-short.playwright.spec.js tests/Feature/p1-baseline.playwright.spec.js
```

**Fermarsi dopo la verifica: richiesta nuova autorizzazione per la successiva fase di audit.** Nessuna promozione automatica della baseline.


### Riproduzione versionabile A07

Questa classe diagnostica esterna è stata inserita soltanto nel checkout effimero dal wrapper indicato, insieme ai22testA06. Non eseguirla fuori dall’infrastruttura isolata. L’aspettativa finale200 resta intenzionalmente rossa e non è stata sostituita da un’aspettativa302.

```php
<?php
namespace Tests\Feature;
use Tests\TestCase;
use Tests\Support\StrutturaFixtures;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
class IndependentBaselineAuditTest extends TestCase {
 use RefreshDatabase, StrutturaFixtures;
 public function test_anonimo_con_configurazione_bellaria_valida_deve_aprire_form(): void {
  $s=$this->structureFor(null);$s->update(['citta'=>'Bellaria-Igea Marina','tipologia_struttura'=>'Albergo','classificazione'=>'3 stelle']);
  \App\Models\TassaDiSoggiorno::create(['struttura_id'=>$s->id,'tassa_soggiorno'=>1.5,'giorni_massimo'=>6,'inizio'=>'2026-06-01','fine'=>'2026-09-30','max_age_children'=>17,'min_age_adult'=>18]);
  $p=\App\Models\Schedina::forceCreate(['struttura_id'=>$s->id,'name'=>'Sintetico','surname'=>'Form','arrive'=>'2026-06-01','departure'=>'2026-06-02','cant_people'=>1,'oa_date_nac'=>'1980-01-01','exent'=>'NO']);
  $r=\App\Models\WebCheckinRichiesta::create(['struttura_id'=>$s->id,'schedina_id'=>$p->id,'codice'=>'AUDIT','numero_prenotazione'=>'SINTETICA','email'=>'audit@example.invalid','nome_referente'=>'Sintetico','arrivo'=>'2026-06-01','partenza'=>'2026-06-02','token'=>str_repeat('a',64),'stato'=>'da_inviare']);
  $this->assertSame(1,DB::table('tassa_di_soggiorno')->where('struttura_id',$s->id)->count());
  $m=new \ReflectionMethod(\App\Http\Controllers\SchedinaController::class,'loadTassaContext');
  [$cfg]=$m->invoke(app(\App\Http\Controllers\WebCheckinController::class),$s);
  fwrite(STDERR,'Configurazione DB presente:SI; lettura anonima:'.($cfg?'presente':'NULL')."\n");
  $json=$this->getJson('/checkin/'.$r->token);
  fwrite(STDERR,'GET JSON completo:'.$json->status().'; configurazione_tassa:'.($json->json('errors.configurazione_tassa')?'errore':'nessun errore')."\n");
  $this->get('/checkin/'.$r->token)->assertOk()->assertSee('schedina-form');
 }
}
```

**Verdetto della fase: A06 instradamento corretto; accettazione end-to-end BLOCCATA daA07.** Richiesta nuova autorizzazione circoscritta; nessuna correzione fuori scope.


## Correzione autorizzata A07 — contesto fiscale del Web Check-in anonimo

**BASELINE IN AUDIT — NON VALIDATA.** Scope esclusivo A07 autorizzato dall’utente. La precedente diagnosi e lo STOP conservano valore storico; questa appendice registra la correzione successiva. Nessuna modifica a regole fiscali, tariffe, profili, configurazioni operative o dati reali. Rischio MEDIO: lettura di dati fiscali tramite credenziale pubblica.

### Percorso, causa e correzione minima

Token completo o resolverA06 →richiesta persistita →`ensureLinkedSchedina()`/guardA02 →parent della stessa struttura →`publicShow()` →contesto fiscale →service certificato →vista ufficiale Web Check-in e form condiviso. La richiesta non convertita anonima leggeva il contesto tramite `SchedinaController::loadTassaContext()`: lo scope gestionale `struttura` restituiva nessuna configurazione/esenzione per l’utente anonimo. Anche con record valido presente, il service ricevevaNULL e rifiutava correttamente il calcolo.

Unica modifica applicativa: `WebCheckinController::publicShow()` chiama il nuovo metodo privato `loadPublicTassaContext(WebCheckinRichiesta)`. Il metodo ricontrolla parent persistito e struttura, richiede corrispondenza al tenant della richiesta, poi rimuove **soltanto** lo scope gestionale nominato `struttura` dalle query di configurazione/esenzioni, applicando immediatamente `where('struttura_id', strutturaVerificata)`. Sono preservati gli altri scope, filtri attivo/777, ordinamento e il catalogoBellaria già esistente. Nessuna configurazione creata o sostituita. Il service fiscale continua a validare e calcolare senza modifiche.

La lettura gestionale originaria e il trait globale restano invariati. ParametriURL e sessione di un altro tenant non determinano il contesto pubblico; il token autorizza il solo tenant della richiesta e il parent deve esserne coerente. I percorsi pubblici restano bearerlink, non nuove pagine gestionali. Nessuna modifica a controlliA01/A02/A03 o al resolverA06.

### Riproduzioni prima della correzione

- DiagnosticoA07 originale byte-identico:23test/112asserzioni,1failure,0errori;recordcfg presente,lettura anonimaNULL,HTML302 anziché200,JSON422configurazione_tassa. `/private/tmp/ids-a07-originale-red.log`.
- Nuova matrice HTTP prima del fix:95test/442asserzioni,5failure,0errori; positivi anonimo/tenant/periodi/tempo bloccati. `/private/tmp/ids-a07-mirati-red.log`.
- BrowserRED corrente:4PASS/1FAIL,formBellaria non visibile dopoCTA. `/private/tmp/ids-a07-browser-red.log`. Il launcher usa, soltanto nella copia effimera, la preimmagine del controller verificata mediante SHA256 identico allo snapshot iniziale; nessun revert del repository. Spec e fixture sono gli stessi delGREEN.

### Matrice di sicurezza e fiscalità

| Requisito | Evidenza |
|---|---|
| Modulo anonimo con config valida | HTTP200 e form visibile; configID/tenant corretti; dettaglio3notti×1.50=4.50 |
| Link breve/completo | Invito200,CTA,aliasA06; aperture e salvataggio browser reale |
| Scope gestionale | Query anonima originaria continua a non vederecfg; nessun allentamento globale |
| Parametri e sessione esteri | TokenA +parametriB e utenteB vede solo contestoA autorizzato daltoken; query gestionaleB non acquisiscecfgA |
| Esenzioni | Catalogo della struttura autorizzata; esenzioniB, inattive e777 escluse; nessuna riscrittura |
| Parent estero/mancante e token invalido |404 senza dati; regressioniA02/A06 preservate |
| Config mancante/discordante |422 con chiave specifica configurazione_tassa/categoria_tassa/periodo_tassa; nessun fallback daB o zero |
| Fuori periodo valido | Maggio2026 tre notti→zero affidabile, datecomplete invariate |
| A cavallo periodo |31maggio–3giugno→tre notti complete,due fiscali,3.00 |
| Anno non certificato e crossyear |2027 e31dicembre2026–2gennaio2027→422regola_non_disponibile |
| Config/esenzioni persistite | SnapshotDB identico dopo letture valide e rifiuti |
| Revoca e scadenza | Eliminazione/rotazione con cambio prefisso invalidano vecchi link; nessuna scadenza temporale implementata, tempo2030 non altera profilo del soggiorno2026 |
| Questura/ISTAT/Tassa | Sorgenti e test precedenti byte-preservati; globali e Safetyoffline |

**Token “scaduti” temporali non esistono nel contratto attuale:** schema privo di expires_at, nessuna policyTTL. Non inventata una durata né dichiarata una prova impossibile. Gap di policy già documentatoA06 invariato; revoca tecnica distinta dalla scadenza. Nessuna garanzia su rotazione che conservi lo stesso prefisso breve.

### Diagnostico originale e logging

Rieseguito anche dopo il fix con codice **byte-identico**:23/112,1failure dovuta a `Invalid JSON was returned from the route`. Il messaggio diagnostico chiama incondizionatamente `$json->json()` dopo GETAcceptJSON, ma la routeWeb corretta restituisceHTML200. Il parser interrompe il test prima dell’asserzione finale; non è il precedente302/422applicativo. Evidenza `/private/tmp/ids-a07-originale-green.log`. Nessun comportamentoAPI inventato per compiacere il logger.

Conservata la versione originale; copia esterna separata `/private/tmp/ids-a07-originale-logging-compatibile.py` cambia **solo** il messaggio di logging usando status eContent-Type. Le espressioni di asserzione originali sono identiche:recordcfg presente,HTTP200 e`schedina-form`visibile. Nessuna aspettativa rimossa/indebolita, nessun test escluso. Esito PASS:23test/113asserzioni,0failure/0errori. Evidenza `/private/tmp/ids-a07-originale-logging-compatibile-phpunit.log`.

### Verifiche conclusive

Prima matriceGREEN95/475PASS; successivamente rafforzate le asserzioni negative per verificare la chiave fiscale esatta, non soltanto422. 

| Verifica conclusiva | Esito |
|---|---|
| Globale1, ambiente temporaneo pulito |677test/7335asserzioni PASS |
| Globale2, stesso runtime reinizializzato |677test/7335asserzioni PASS |
| Globale casuale, seed4208 |677test/7335asserzioni PASS |
| Browser reale A06/A07 e regressioni precedenti |5/5PASS |
| Guardrail / policyDB |5/10PASS |
| SOAP / ISTAT offline in memoria |14/22PASS |
| Pint / lint PHP |2file/2file PASS |
| git diff --check |PASS |

Tre globali:0failure,0errori,0skip,nessuna deprecazione segnalata; inventario e asserzioni per caso identici nei tre XML. Comprendono A07nuovo11/73,A06 22/110,Questura204/3656,ISTAT65/410,Tassa52/1586 e le tre classiP1precedenti62/302. Evidenze `/private/tmp/ids-a07-globali-{1,2}.{log,xml}` e `/private/tmp/ids-a07-random-1.{log,xml}`. Browser `/private/tmp/ids-a07-browser-green.log`: modulo anonimo realmente visibile, compilazione/salvataggio sintetico e riapertura, invito breve/CTA, percorso completo e rifiuti tenant. Nessuna modifica alle prove browser preesistenti.

**A07 CORRETTO E VERIFICATO nel perimetro autorizzato. BASELINE IN AUDIT — NON VALIDATA.**

Comandi, PHP sicuro e PATH MySQL locali, sempre launcherattestato:

```sh
PATH=/private/tmp/ids-runtime-bin:/Users/Shared/Herd/services/mysql/8.0.36/bin:$PATH \
python3 -B tests/Isolation/run.py \
 --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php \
 --phpunit tests/Feature/WebCheckinFiscalContextTest.php tests/Feature/WebCheckinShortLinkTest.php tests/Feature/WebCheckinParentSecurityTest.php tests/Feature/ImpersonazioneSecurityTest.php tests/Feature/SchedinaCustomerReferenceSecurityTest.php
# Globali: --phpunit-global --phpunit-repeat 2
# Terza: --phpunit-global --phpunit-random-seed 4208
# Browser: --fixtures tests/Isolation/web-checkin-short-fixtures.php
#          --playwright tests/Feature/web-checkin-short.playwright.spec.js tests/Feature/p1-baseline.playwright.spec.js
```

Il diagnostico originale e quello col solo logger compatibile sono inseriti esclusivamente nel checkout effimero dal wrapper esterno. Nessun DB/server preesistente, file ospiti o trasporto reale. Attestazioni identità/processi/HTTP,riconnessione e23rifiuti di override prima delle migration; sole risorse effimere.

### Inventario della fase e limiti

Quattro file: `app/Http/Controllers/WebCheckinController.php` (unico applicativo), nuovo `tests/Feature/WebCheckinFiscalContextTest.php`, questo rapporto e `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`. Nessuna modifica a route, fixture, specbrowser o testP1precedenti. Snapshot iniziale4187file:tre estensioni autorizzate,4184byte-identici,un nuovo test,zero cancellazioni. mainHEAD/originlocale`faef3cf01f43d19b20d1558c1fbd3ae32e48d849`,0/0,stagingvuoto,32tracked+34untracked. Nessunfetch/commit/push/deploy/SPanel/DBoperativo/.env/migrationreale/trasmissione.

Inventario finale (comprende le modifiche precedenti preservate):

```text
 M app/Http/Controllers/SchedinaController.php
 M app/Http/Controllers/Superadmin/ImpersonazioneController.php
 M app/Http/Controllers/TassaDiSoggiornoController.php
 M app/Http/Controllers/TassaEsenzioneController.php
 M app/Http/Controllers/TassaReportController.php
 M app/Http/Controllers/WebCheckinController.php
 M app/Models/TassaDiSoggiorno.php
 M app/Services/TassaDiSoggiornoService.php
 M docs/DEPLOY_SPANEL.md
 M docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md
 M resources/views/layouts/topbar.blade.php
 M resources/views/schedina/list.blade.php
 M resources/views/schedina/partials/form.blade.php
 M resources/views/schedina/print-tassa.blade.php
 M resources/views/struttura/seleziona.blade.php
 M resources/views/tassa_di_soggiorno/edit.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-print.blade.php
 M resources/views/tassa_di_soggiorno/rapporto.blade.php
 M routes/web.php
 M tests/Feature/AccessTest.php
 M tests/Feature/CustomerImportedFiltersDeleteTest.php
 M tests/Feature/ExampleTest.php
 M tests/Feature/QuesturaC1MigrationAcceptanceTest.php
 M tests/Feature/SmokePagesQaTest.php
 M tests/Feature/TenancyTest.php
 M tests/Isolation/run.py
 M tests/Support/TestingEnvironment.php
 M tests/TestCase.php
 M tests/Unit/ComponentiImportFormTest.php
 M tests/Unit/ComponentiImportReviewStatusTest.php
 M tests/Unit/ComponentiImportServiceTest.php
?? app/Models/TassaExport.php
?? database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
?? docs/baseline-applicativa-audit-2026-10-08.md
?? docs/imposta-soggiorno-bellaria-audit-2026-10-07.md
?? docs/suite-globale-risanamento-2026-10-08.md
?? tests/Feature/ArriviEsenzioniTest.php
?? tests/Feature/ImpersonazioneSecurityTest.php
?? tests/Feature/SchedinaCustomerReferenceSecurityTest.php
?? tests/Feature/StrutturaSelezioneTest.php
?? tests/Feature/SuiteResourceIsolationTest.php
?? tests/Feature/TassaBellariaAnteprimaTest.php
?? tests/Feature/TassaBellariaCompletenessTest.php
?? tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php
?? tests/Feature/TassaBellariaEtaCsvTest.php
?? tests/Feature/TassaBellariaRiallineamentoTest.php
?? tests/Feature/TassaBellariaVersionamentoTest.php
?? tests/Feature/WebCheckinFiscalContextTest.php
?? tests/Feature/WebCheckinParentSecurityTest.php
?? tests/Feature/WebCheckinShortLinkTest.php
?? tests/Feature/p1-baseline.playwright.spec.js
?? tests/Feature/struttura-selezione.playwright.spec.js
?? tests/Feature/tassa-bellaria-anteprima.playwright.spec.js
?? tests/Feature/tassa-bellaria-configurazione.playwright.spec.js
?? tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js
?? tests/Feature/web-checkin-short.playwright.spec.js
?? tests/Isolation/database_policy.py
?? tests/Isolation/p1-baseline-fixtures.php
?? tests/Isolation/struttura-selezione-fixtures.php
?? tests/Isolation/tassa-anteprima-fixtures.php
?? tests/Isolation/tassa-bellaria-fixtures.php
?? tests/Isolation/test_database_policy.py
?? tests/Isolation/web-checkin-short-fixtures.php
?? tests/Support/IsolatedTestStorage.php
?? tests/Support/componenti-import-review-status.php
```

Restano policyTTL/revoca/rate limit, lacune della matrice applicativa generale, A04JS eA05proxy e gate produttivi precedenti. Nessun intervento fuoriA07, nessuna certificazione normativa/readiness dedotta. Il casoE2E provato comprende una Schedina sintetica singola; non sostituisce audit esaustivo dei flussi di gruppi/componenti/importazioni.


## Valutazione conclusiva della baseline applicativa — 9 ottobre 2026

**Verdetto: BASELINE IN AUDIT.** Nessun P0/P1 residuo è stato riprodotto in questa valutazione; le evidenze disponibili confermano le correzioni precedenti nel rispettivo perimetro. Non sono però sufficienti per validare tutta la baseline applicativa. Le lacune sotto elencate sono verifiche mancanti, non vulnerabilità già dimostrate. Nessuna readiness produttiva o certificazione normativa.

### Metodo e controlli effettivi della fase

Rilettura del Maestro e del rapporto con le appendici, confronto delle evidenze e del codice pertinente, inventario Git completo e confronto crittografico dei file. Non è stata rieseguita PHPUnit né avviato un browser: ripetere la suite verde non colmerebbe le lacune funzionali indicate. Nessun database utilizzato, nessuna richiesta a servizi esterni, nessuna modifica applicativa o ai test.

I tre JUnit finali A07 conservati in `/private/tmp/ids-a07-globali-1.xml`, `/private/tmp/ids-a07-globali-2.xml` e `/private/tmp/ids-a07-random-1.xml` sono stati ricontati: **677 test / 7.335 asserzioni ciascuno, zero errori, fallimenti e skip**, 54 classi e 677 casi unici. Inventario e asserzioni per caso identici nelle tre esecuzioni; terza esecuzione con seed 4208 secondo le evidenze della fase A07. Sono prove precedenti rianalizzate, non nuove esecuzioni. Nessuna deprecazione segnalata nelle evidenze finali; ciò non certifica ogni runtime futuro.

Byte invariati rispetto a HEAD per configurazioni Questura/ISTAT, Handler Q3, servizi Questura/ISTAT e i tre file delle prove Rocky (`check_rocky10.py`, `rocky_isolation.py`, `test_rocky_isolation.py`). Le **147 migration tracked sono invariate**; esiste una nuova migration Tassa untracked, non applicata in questa fase. Il cambiamento al test C1 riguarda l'isolamento DDL documentato, non il contratto di trasmissione. `git diff --check` PASS. Nell'inventario dei 66 file non compaiono `.env`, log, dump, PDF, screenshot o file temporanei; questo controllo non sostituisce uno scan completo dei segreti prima del commit.

### Stato dei P1 e copertura critica

| Area | Stato ed evidenza disponibile | Limite della conclusione |
|---|---|---|
| A01 impersonazione | Corretto: route ingresso numerica, uscita POST, controllo identità/sessione/log e ripristino originale; 14 test/112 asserzioni e browser | Non certifica tutti i percorsi di amministrazione utenti |
| A02 parent Web Check-in | Corretto: appartenenza parent/struttura verificata prima di esporre o modificare dati; 14/47 | Collegamento incoerente costruito in fixture; non dimostrata la sua creazione da utente ordinario |
| A03 riferimenti cliente Schedina | Corretto: validazione server-side e transazioni; 34/143 | Non sostituisce accettazione completa di gruppi/importazioni |
| A06 link breve | Corretto: risoluzione esatta, precedenza token completo, rifiuto ambiguità e prefissi invalidi; 22/110 e browser | Politica TTL/revoca/rate limit non definita esaustivamente |
| A07 contesto fiscale anonimo | Corretto: lettura fiscale limitata al tenant autorizzato dal token, mantenendo scope gestionale e validazioni; 11/73, browser finale 5/5 | Percorso end-to-end dimostrato per Schedina singola |
| Selezione struttura | Corretto layout ufficiale; test e browser proprietario singolo/multiplo/senza strutture e personale | Non copre automaticamente ogni endpoint di tenant |
| Arrivi/esenzioni | Corretto contratto Collection e filtro 777; regressioni mirate e suite finale | Nessuna modifica fiscale autorizzata da questo audit |
| Tassa Bellaria | Regressioni 52/1.586; profili per data, blocchi configurazione/anni non certificati, anteprima, storico e asset | Categorie/anni non certificati restano esclusi; nessuna accettazione normativa esterna |
| Questura Q1/Q2/Q3/C1 | Regressioni offline 204/3.656; codice e guardrail protetti | Nessuna prova o trasmissione reale; accettazione esterna separata |
| ISTAT | Regressioni 65/410 e prove offline documentate | Nessuna accettazione Ross1000 reale |
| Importazioni/risanamento suite | Correzioni bootstrap, fixture, discovery, DB e storage documentate; globale ripetibile | Manca un percorso browser completo importazione→conferma→persistenza→rollback |
| Harness MariaDB | Bypass P1 corretti e prove sintetiche documentate | Non equivale a prova applicativa MariaDB reale isolata |

Il diagnostico A07 originale non è stato neutralizzato: dopo la correzione resta un errore del logger che prova a decodificare HTML 200 come JSON. Le aspettative originali, in copia con solo logging compatibile, passano. È una limitazione dello strumento diagnostico documentata, non evidenza di A07 ancora aperto.

### Verifiche indispensabili ancora mancanti

1. **Permessi di gestione utenti/password e sessioni.** Eseguire matrice HTTP con personale/reception, proprietario operativo, proprietario, admin e superadmin, nella stessa struttura e in struttura diversa: creazione utente, reset password e accessi diretti. Il rischio R14 resta non riprodotto: `StrutturaUserController` controlla la struttura, mentre la gestione operativa usa anche un permesso dedicato; le route `/strutture/utenti` non mostrano un controllo di ruolo specifico locale alla dichiarazione. Occorre verificare l'intera catena middleware e il contratto autorizzativo, non dedurre un exploit dal solo codice. Accettazione: soltanto ruoli previsti possono operare, rifiuti senza scritture, sessioni e identità coerenti. Completare reset/recupero e invalidazione sessioni secondo le funzioni realmente supportate.
2. **Web Check-in anonimo con gruppi e componenti.** Provare lettura, inserimento, modifica e rimozione, riferimenti esterni al tenant, parent incoerenti, input non valido, rollback, conversione e riutilizzo del token. Accettazione: nessun dato esterno esposto/scritto e nessuna scrittura parziale, preservando A02/A06/A07 e profili fiscali. Formalizzare la politica dei token: l'assenza di TTL non è qui classificata come P1; scadenza/revoca/rate limit richiedono un contratto esplicito e una valutazione di sicurezza del prefisso breve.
3. **Ciclo operativo completo con persistenza.** Browser isolato: cliente/ricerca/riutilizzo→componenti→Arrivi→Schedina→partenza/presenze, importazione con anteprima/conferma/errore/rollback e passaggio annuale. Accettazione: associazione struttura/anno/ciclo corretta, nessuna duplicazione indesiderata, date e dati completi coerenti con estrazioni Questura/ISTAT/fiscali offline. La prova del singolo flusso non copre questo insieme.
4. **Confini tenant non ancora campionati.** Completare la matrice dei principali endpoint di scrittura, ricerca, import/export, cancellazione/ripristino e risorse pubbliche per almeno due strutture e ruoli ammessi/non ammessi. I test già verdi riducono il rischio, ma non attestano tutti i percorsi. Accettazione: rifiuti server-side, nessun dato esterno e nessun effetto collaterale.

Queste prove devono usare esclusivamente l'infrastruttura isolata e fixture sintetiche. Non richiedono nuove funzionalità né indebolimento delle aspettative. Se emerge un nuovo P0/P1, documentare la riproduzione e chiedere autorizzazione alla correzione. Per CRM/supporto/licenze e altri percorsi secondari occorre delimitare esplicitamente il perimetro della successiva accettazione, oppure verificarne i flussi critici; nessuna copertura totale implicita.

### Residui non bloccanti dimostrati e rischi

- **A04, P2 diagnostico:** tre prove JavaScript rosse nel contesto VM; non costituiscono ancora una regressione DOM dimostrata. Usare prove browser reali di invio/modifica/annullamento per distinguere errore del diagnostico da comportamento applicativo; nessuna correzione in questa fase.
- **A05, P2 ambientale da chiarire:** `IncompleteRead` riprodotto in memoria con lunghezza annunciata maggiore del corpo; endpoint/causa del caso originario non identificati. Verificare completezza e leggibilità dei download CSV/PDF effettivi. L'assenza dell'errore nel browser A07 non chiude la diagnosi originaria.
- **Nuova migration Tassa:** il rollback elimina la tabella degli snapshot. Prima del deploy serve revisione esplicita di backup, recupero e compatibilità; non usare rollback distruttivo su storico reale senza piano autorizzato.
- **Git misto:** i 66 file appartengono a più interventi. Pertinenza tecnica non significa approvazione per un unico commit; file condivisi richiedono revisione di scope prima di qualsiasi staging.

### Gate produttivi separati

Le sei prove Rocky/Bubblewrap di isolamento di processo risultano già PASS nelle evidenze, e i relativi file sono invariati: non sono da riclassificare come pendenti. Restano distinti la prova applicativa MariaDB reale isolata, l'accettazione PHP CLI/FPM e server SPanel, le procedure di backup/rollback/cache/storage e le accettazioni esterne Questura/ISTAT. Nessuna produzione aggiornata da questa attività. Non è richiesta una trasmissione reale per concludere l'audit locale; è vietata in questa fase.

### Inventario completo e pertinenza

**32 tracked modificati + 34 untracked.** Ogni voce è riconducibile a interventi o prove permanenti documentate. Non è stato identificato un untracked da cancellare come artefatto: fixture e diagnostico di supporto servono ai test ripetibili. La necessità indicata vale per la rispettiva fase, non autorizza consolidamento Git indiscriminato.

| File | Stato | Classe | Origine e necessità |
|---|---|---|---|
| `app/Http/Controllers/SchedinaController.php` | tracked modificato | codice/schema applicativo | Tassa e A03: anteprime, riferimenti cliente e transazioni; file condiviso tra fasi. |
| `app/Http/Controllers/Superadmin/ImpersonazioneController.php` | tracked modificato | codice/schema applicativo | A01: sessione, uscita e ripristino identità. |
| `app/Http/Controllers/TassaDiSoggiornoController.php` | tracked modificato | codice/schema applicativo | Tassa: configurazione, riallineamento esplicito e gestione asset. |
| `app/Http/Controllers/TassaEsenzioneController.php` | tracked modificato | codice/schema applicativo | Tassa: protezione catalogo Bellaria. |
| `app/Http/Controllers/TassaReportController.php` | tracked modificato | codice/schema applicativo | Tassa: rapporto, CSV, consolidamento e storico. |
| `app/Http/Controllers/WebCheckinController.php` | tracked modificato | codice/schema applicativo | A02/A06/A07: parent, risoluzione link breve e contesto fiscale pubblico. |
| `app/Models/TassaDiSoggiorno.php` | tracked modificato | codice/schema applicativo | Tassa: riferimento immagine ricevuta. |
| `app/Services/TassaDiSoggiornoService.php` | tracked modificato | codice/schema applicativo | Tassa: profili temporali, calcolo, età, 777 e validazione configurazione. |
| `docs/DEPLOY_SPANEL.md` | tracked modificato | documentazione/evidenza | Evidenza harness MariaDB/PHP/Rocky; distinta dal candidato fiscale. |
| `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md` | tracked modificato | documentazione/evidenza | Fonte maestra e cronologia delle fasi. |
| `resources/views/layouts/topbar.blade.php` | tracked modificato | codice/schema applicativo | A01: uscita impersonazione POST con CSRF. |
| `resources/views/schedina/list.blade.php` | tracked modificato | codice/schema applicativo | Tassa: disponibilità calcolo e anteprima. |
| `resources/views/schedina/partials/form.blade.php` | tracked modificato | codice/schema applicativo | Tassa e Arrivi: modulo condiviso e normalizzazione Collection. |
| `resources/views/schedina/print-tassa.blade.php` | tracked modificato | codice/schema applicativo | Tassa: ricevuta, importo zero valido, immagine e stampa. |
| `resources/views/struttura/seleziona.blade.php` | tracked modificato | codice/schema applicativo | P1 selezione: layout ufficiale esistente. |
| `resources/views/tassa_di_soggiorno/edit.blade.php` | tracked modificato | codice/schema applicativo | Tassa: configurazione visibile, differenze e consenso. |
| `resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php` | tracked modificato | codice/schema applicativo | Tassa: controlli quantitativi e periodo. |
| `resources/views/tassa_di_soggiorno/rapporto-print.blade.php` | tracked modificato | codice/schema applicativo | Tassa: stampa periodo. |
| `resources/views/tassa_di_soggiorno/rapporto.blade.php` | tracked modificato | codice/schema applicativo | Tassa: periodo Dal/Al, storico e consolidamento. |
| `routes/web.php` | tracked modificato | codice/schema applicativo | Tassa, A01 e A06; file condiviso, non autorizzazione indiscriminata al commit. |
| `tests/Feature/AccessTest.php` | tracked modificato | test/fixture/infrastruttura | Fixture sintetiche, senza dipendenza dal seeder operativo. |
| `tests/Feature/CustomerImportedFiltersDeleteTest.php` | tracked modificato | test/fixture/infrastruttura | Fixture obbligatorie e isolamento database. |
| `tests/Feature/ExampleTest.php` | tracked modificato | test/fixture/infrastruttura | Aspettativa coerente con homepage pubblica reale. |
| `tests/Feature/QuesturaC1MigrationAcceptanceTest.php` | tracked modificato | test/fixture/infrastruttura | Isolamento DDL e reinizializzazione esclusivamente effimera; non logica Questura. |
| `tests/Feature/SmokePagesQaTest.php` | tracked modificato | test/fixture/infrastruttura | Dati sintetici espliciti, nessuna dipendenza da dati presenti. |
| `tests/Feature/TenancyTest.php` | tracked modificato | test/fixture/infrastruttura | Fixture e isolamento senza cancellazioni indiscriminate. |
| `tests/Isolation/run.py` | tracked modificato | test/fixture/infrastruttura | Harness isolato: policy database, discovery e ripetibilità. |
| `tests/Support/TestingEnvironment.php` | tracked modificato | test/fixture/infrastruttura | Attestazioni database/processo e rifiuto risorse non isolate. |
| `tests/TestCase.php` | tracked modificato | test/fixture/infrastruttura | Bootstrap e storage isolato per caso. |
| `tests/Unit/ComponentiImportFormTest.php` | tracked modificato | test/fixture/infrastruttura | Bootstrap e fixture; regressione Collection/777. |
| `tests/Unit/ComponentiImportReviewStatusTest.php` | tracked modificato | test/fixture/infrastruttura | Wrapper PHPUnit del diagnostico originale preservato. |
| `tests/Unit/ComponentiImportServiceTest.php` | tracked modificato | test/fixture/infrastruttura | Fixture CSV canoniche e attributo DataProvider; risanamento suite. |
| `app/Models/TassaExport.php` | untracked | codice/schema applicativo | Tassa: snapshot fiscale cifrato e versionato; necessario al consolidamento. |
| `database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php` | untracked | codice/schema applicativo | Tassa: schema snapshot/immagine; necessario, revisione rollback separata prima del deploy. |
| `docs/baseline-applicativa-audit-2026-10-08.md` | untracked | documentazione/evidenza | Audit applicativo e prove delle correzioni; evidenza permanente. |
| `docs/imposta-soggiorno-bellaria-audit-2026-10-07.md` | untracked | documentazione/evidenza | Fonti e audit fiscale; evidenza permanente. |
| `docs/suite-globale-risanamento-2026-10-08.md` | untracked | documentazione/evidenza | Triage e risanamento della suite; evidenza permanente. |
| `tests/Feature/ArriviEsenzioniTest.php` | untracked | test/fixture/infrastruttura | Regressione P1 Collection/HTTP e assenza scritture. |
| `tests/Feature/ImpersonazioneSecurityTest.php` | untracked | test/fixture/infrastruttura | Regressioni A01. |
| `tests/Feature/SchedinaCustomerReferenceSecurityTest.php` | untracked | test/fixture/infrastruttura | Regressioni A03. |
| `tests/Feature/StrutturaSelezioneTest.php` | untracked | test/fixture/infrastruttura | Layout, autorizzazioni e sessione. |
| `tests/Feature/SuiteResourceIsolationTest.php` | untracked | test/fixture/infrastruttura | Isolamento DB/storage e preservazione file estranei. |
| `tests/Feature/TassaBellariaAnteprimaTest.php` | untracked | test/fixture/infrastruttura | Anteprime zero/positive, autorizzazioni e assenza effetti fiscali. |
| `tests/Feature/TassaBellariaCompletenessTest.php` | untracked | test/fixture/infrastruttura | Periodo, categorie, configurazione e invarianti fiscali. |
| `tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php` | untracked | test/fixture/infrastruttura | Configurazione, categorie e immagini private. |
| `tests/Feature/TassaBellariaEtaCsvTest.php` | untracked | test/fixture/infrastruttura | Età, 777, quantità e CSV. |
| `tests/Feature/TassaBellariaRiallineamentoTest.php` | untracked | test/fixture/infrastruttura | Consenso esplicito, transazione e conservazione storico. |
| `tests/Feature/TassaBellariaVersionamentoTest.php` | untracked | test/fixture/infrastruttura | Profili per data, storico immutabile e compensazione asset. |
| `tests/Feature/WebCheckinFiscalContextTest.php` | untracked | test/fixture/infrastruttura | Regressioni A07. |
| `tests/Feature/WebCheckinParentSecurityTest.php` | untracked | test/fixture/infrastruttura | Regressioni A02. |
| `tests/Feature/WebCheckinShortLinkTest.php` | untracked | test/fixture/infrastruttura | Regressioni A06. |
| `tests/Feature/p1-baseline.playwright.spec.js` | untracked | test/fixture/infrastruttura | Prova browser permanente: A01 e navigazione applicativa sintetica. |
| `tests/Feature/struttura-selezione.playwright.spec.js` | untracked | test/fixture/infrastruttura | Prova browser permanente: Selezione/cambio struttura e ruoli. |
| `tests/Feature/tassa-bellaria-anteprima.playwright.spec.js` | untracked | test/fixture/infrastruttura | Prova browser permanente: Aperture reali e PDF zero/positivi. |
| `tests/Feature/tassa-bellaria-configurazione.playwright.spec.js` | untracked | test/fixture/infrastruttura | Prova browser permanente: UI configurazione e consenso. |
| `tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js` | untracked | test/fixture/infrastruttura | Prova browser permanente: Ricevuta con/senza immagine e gruppi fiscali. |
| `tests/Feature/web-checkin-short.playwright.spec.js` | untracked | test/fixture/infrastruttura | Prova browser permanente: A06/A07: invito, modulo pubblico, salvataggio e rifiuti. |
| `tests/Isolation/database_policy.py` | untracked | test/fixture/infrastruttura | Policy MariaDB/MySQL fail-closed; permanente, non artefatto. |
| `tests/Isolation/p1-baseline-fixtures.php` | untracked | test/fixture/infrastruttura | Fixture sintetiche A01/navigazione. |
| `tests/Isolation/struttura-selezione-fixtures.php` | untracked | test/fixture/infrastruttura | Fixture sintetiche ruoli e strutture. |
| `tests/Isolation/tassa-anteprima-fixtures.php` | untracked | test/fixture/infrastruttura | Fixture sintetiche anteprime e snapshot. |
| `tests/Isolation/tassa-bellaria-fixtures.php` | untracked | test/fixture/infrastruttura | Fixture sintetiche Bellaria. |
| `tests/Isolation/test_database_policy.py` | untracked | test/fixture/infrastruttura | Dieci prove della policy e casi avversariali. |
| `tests/Isolation/web-checkin-short-fixtures.php` | untracked | test/fixture/infrastruttura | Fixture sintetiche A06/A07 e tenant separati. |
| `tests/Support/IsolatedTestStorage.php` | untracked | test/fixture/infrastruttura | Storage per caso e pulizia soltanto delle risorse possedute. |
| `tests/Support/componenti-import-review-status.php` | untracked | test/fixture/infrastruttura | Contenuto diagnostico originale preservato fuori discovery; necessario al wrapper, non temporaneo. |

### Stato Git e raccomandazione

Repository locale corretto, branch `main`, HEAD e `origin/main` locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, ahead/behind locale **0/0**, staging vuoto. Nessun fetch: non attestata la situazione remota corrente. Worktree intenzionalmente non pulito: **32 tracked modificati, 34 untracked**, diff tracked iniziale 2.271 inserimenti/958 eliminazioni. Nessun commit/push/deploy o operazione distruttiva.

In questa fase sono aggiornati soltanto questo rapporto e il Maestro mediante appendici; nessun codice, test o migration modificato. Il confronto finale dei 4.188 file inventariati all'avvio conferma esclusivamente queste due appendici: 4.186 file byte-identici, nessuna eliminazione e inventario Git invariato. `git diff --check` finale PASS.

**Raccomandazione: mantenere BASELINE IN AUDIT.** Autorizzare come fase successiva le quattro matrici di accettazione indicate, registrandone casi, risultati e limiti; chiarire separatamente A04/A05 e politica token. Solo dopo le prove mancanti rivalutare la validabilità locale. Consolidamento Git e produzione restano gate successivi e non autorizzati. Non esiste in questa analisi una correzione applicativa da eseguire automaticamente.


## Audit approfondito autorizzato — 9 ottobre 2026

**BASELINE NON VALIDABILE — BASELINE IN AUDIT.** Questa nuova evidenza supera la precedente conclusione di sola insufficienza: sono stati riprodotti tre nuovi impedimenti P1, senza correggere il codice. A01/A02/A03/A06/A07 restano preservati; le lacune dei gruppi e delle autorizzazioni legacy non erano coperte dalla loro precedente verifica.

### Ambiente, metodo e conservazione

Repository locale, HEAD `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36 in nuove istanze effimere. Ogni launcher ha attestato identità DB/processi/HTTP, riconnessione e **23 rifiuti di override prima delle migration**, build e checkout privato. Questura/ISTAT disabilitate nel runtime; SOAP soltanto double in memoria. Nessun DB operativo o ambiente di produzione utilizzato.

Il primo tentativo dentro il sandbox non poteva aprire una porta loopback (`PermissionError`), prima della creazione del database. Il medesimo launcher è stato poi eseguito con l'autorizzazione dello strumento, senza aggirare attestazioni o riutilizzare servizi. Ogni esecuzione conclusa rimuove le sole risorse proprie. Nessuna modifica applicativa, dipendenza, configurazione o migration. Fixture sintetiche, nessun dato reale.

### Nuovi difetti dimostrati

| ID | Gravità | Causa, riproduzione e impatto | Correzione proposta, non applicata |
|---|---|---|---|
| A08 | P1 sicurezza | Reception dello stesso tenant, `canManageGestioneOperativa=false`: POST `/gestione-operativa/utenti/{id}/password` rifiutato 403; POST `/strutture/utenti/{id}/reset` cambia realmente la password del proprietario e restituisce 302. `StrutturaUserController::resetPassword` verifica appartenenza alla struttura ma non il privilegio di gestione. Browser autenticato con CSRF valido conferma 302 invece di 403. Permette interferenza con l'accesso dell'utente privilegiato. Non dimostrata modifica di credenziali di un tenant diverso. | Applicare il controllo autorizzativo server-side anche al percorso legacy, verificando tutte le operazioni legacy di gestione utenti e conservando operazioni consentite. Poi provare sessioni e ruoli. |
| A09 | P1 funzionale | Un componente sintetico persistito e appartenente alla Schedina autorizzata non compare nella Collection passata al modulo anonimo. `WebCheckinController::publicShow` usa `$schedina->componenti()->get()`; lo scope `struttura` del modello `Componenti` senza identità gestionale esclude il record. Browser Bellaria: modulo visibile ma zero input dell'accompagnatore, anziché uno. | Lettura dei componenti nel contesto autorizzato dal token, con filtro esplicito sia sul tenant sia sulla Schedina, coerente con A02/A07; verificare anche conteggi e aggiornamenti dei gruppi. Non rimuovere globalmente lo scope. |
| A10 | P1 integrità | POST anonimo con componente ID inesistente viene rifiutato 422, ma il principale conserva nome e altri campi modificati. `publicStore` salva il principale e sincronizza camere prima di `syncComponenti`; la transazione interna di quest'ultimo non comprende le scritture precedenti. Snapshot DB prima/dopo diverso nonostante il rifiuto. | Rendere atomica l'intera operazione pubblica principale/camere/componenti/stato richiesta, mantenendo le validazioni e aggiungendo rollback negativo. |

I tre meccanismi sono già presenti nei sorgenti HEAD: confronto read-only conferma reset legacy privo di privilegio, relazione scoped dei componenti e salvataggio pubblico prima della sincronizzazione. Sono difetti preesistenti non coperti, non regressioni attribuite automaticamente alle correzioni A01–A07. Nessun P0 dimostrato. A09 riguarda omissione di dati autorizzati, non esposizione di dati esterni; A10 dimostra scrittura parziale, non un'escalation cross-tenant.

### Prove nuove e risultati

| Prova | Esito effettivo | Evidenza |
|---|---|---|
| `BaselineFinalBoundaryAudit.php` | 5 test / 15 asserzioni; 3 failure, 0 errori/skip | A08/A09/A10 rossi; reset cross-tenant e password errata/escalation profilo verdi |
| `BaselineFinalAuthorizationAudit.php`, conclusiva | 5 test / 29 asserzioni; PASS, 0 errori/failure/skip | Creazione/modifica/disattivazione consentite, ruolo arbitrario ignorato, rifiuti reception e cross-tenant, componenti esterni GET/PUT/DELETE negati, logout |
| Browser `baseline-final.playwright.spec.js`, conclusivo | 2 prove, entrambe FAIL per difetti riprodotti | Accompagnatore assente; reception con CSRF valido ottiene 302 sul reset legacy |
| Globale iniziale e conclusiva | 677 test / 7.335 asserzioni PASS ciascuna; 0 errori/failure/skip, nessuna deprecazione segnalata | Due runtime puliti indipendenti; conclusiva dopo i diagnostici. Nessuna esclusione aggiunta alla configurazione PHPUnit |
| Guardrail | 5 PASS | `tests/Isolation/test_guards.py` |
| Policy DB | 10 PASS | `tests/Isolation/test_database_policy.py` |
| Policy isolamento locale | 21 PASS | Unittest discovery su `tests/Deployment/test_rocky_isolation.py`; output finale include help generato dalle prove del launcher, non esecuzione Rocky |
| SOAP | 14 PASS in memoria durante entrambe le preparazioni browser | Nessun client reale e nessuna accettazione ente |
| Sintassi | 3 PHP e 1 JavaScript PASS | PHP `-n -l`; Node `--check` |
| Pint | 3 file PASS finale | Solo nuovi diagnostici/fixture. Prima 2 problemi di stile, corretti nei soli file nuovi; nessuna aspettativa cambiata |

Il primo browser aveva un errore del nuovo selettore CSRF (meta inesistente): corretto cercando l'input `_token` presente. La ripetizione raggiunge la richiesta reale e fallisce sullo status 302. La prima matrice autorizzazioni aveva due falsi rossi nel confronto tra oggetti factory non riletti e record completi: riletto lo snapshot iniziale dal DB; stessa aspettativa di assenza scritture e stessi rifiuti, conclusiva PASS. Queste correzioni riguardano esclusivamente setup dei diagnostici nuovi; non mascherano A08/A09/A10.

I diagnostici indipendenti hanno suffisso `Audit.php` e sono selezionati esplicitamente: la discovery esistente `*Test.php` non li include. Scelta dichiarata per conservare separatamente i risultati della regressione preesistente e delle prove indipendenti fallenti. **Il totale globale verde non comprende questi tre fallimenti e non convalida la baseline.** Non sono stati modificati filtri/configurazione o aspettative dei test esistenti.

### Matrice funzionale e confini tenant

| Ambito | Copertura nuova o rieseguita | Stato e lacuna residua |
|---|---|---|
| Utenti | Gestione operativa crea/modifica/disattiva; ruolo superadmin in input non eleva; reception negata; utenti B non modificati da A; reset legacy negativo | BLOCCATO A08. Eliminazione utenti e intera gerarchia admin/proprietari non completate; non dedurre che un endpoint non implementato debba essere aggiunto |
| Password/sessioni | Password attuale errata non modifica credenziali; endpoint profilo/password con ID altrui 403; logout rende guest; reset tenant diverso 404 | PARZIALE. Invalidazione delle altre sessioni dopo cambio/reset, recupero credenziali e remember-me richiedono browser con due contesti indipendenti. AuthenticateSession risulta commentato: rischio statico, non ulteriore P1 qui riprodotto |
| Web Check-in | Token completo, invito breve e modulo reale con accompagnatore già persistito; rifiuto 422 e snapshot DB; A02/A06/A07 rieseguiti nella globale | BLOCCATO A09/A10. Conversione e gestione gruppi/accompagnatori end-to-end non certificabili sulla baseline corrente |
| Token | Regressioni esistenti validazione/rotazione/cancellazione e anni fiscali nella globale | Nessuna TTL implementata, revoca tecnica già verificata. Politica TTL/UI revoca/rate limit ancora da formalizzare; non inventata una scadenza |
| Strutture/anagrafiche/Schedine | Globale: selezione/autorizzazioni, Schedina singola/famiglia/gruppo e modifica, riferimenti cliente, fixture importate | PARZIALE per ciclo operativo browser completo e creazione struttura lungo tutta la gerarchia |
| Componenti | Endpoint legacy su componente B: GET modifica, PUT e DELETE 404; snapshot componente B invariato | Confine provato sui tre endpoint; non prova tutte le relazioni/importazioni |
| Importazioni e duplicati | Regressioni Unit/Feature nella globale; contratti e fixture preservati | Anteprima→conferma→persistenza→rollback nel browser e duplicati operativi ancora da completare |
| Tassa | Regressioni complete disponibili nella globale: profili, età/777, configurazione, ricevuta, CSV/report, snapshot, rettifiche e asset | Nessuna regressione nelle prove; non nuova verifica browser di tutte le ricevute, né certificazione normativa |
| Questura | Regressioni offline della suite esistente e 14 SOAP in memoria | Nessuna regressione nelle prove; trasporto reale vietato |
| ISTAT | Regressioni offline della suite esistente, senza abilitazione trasporto | Nessuna regressione nelle prove; accettazione esterna separata |
| Annualità/storico | Regressioni fiscali/ISTAT/Questura sulle date e storico nella globale | Passaggio operativo annuale completo e concorrenza numerazione non esaustivamente verificati |
| Sicurezza generale | CSRF browser effettivo, rifiuti profilo e tenant, controlli server-side comparati | Nessuna attestazione esaustiva di ogni route/API/upload/export |

Inventario degli endpoint aggiuntivi campionati: `POST /strutture/utenti/{id}/reset`, `POST /gestione-operativa/utenti`, `PUT /gestione-operativa/utenti/{id}`, `POST /gestione-operativa/utenti/{id}/password`, `PUT /gestione-operativa/profilo`, `POST /gestione-operativa/profilo/password`, `POST /update-profile/{id}`, `POST /update-password/{id}`, `GET /componenti/{id}/modifica`, `PUT/DELETE /componenti/{id}`, `GET /logout`, `GET /gestione-operativa`, `GET/POST /checkin/{token}`, invito `GET /w/{codice-prefisso}`. Endpoint di export fiscale/Questura/ISTAT e Cestino coperti dalle regressioni esistenti, non riclassificati come nuova matrice esaustiva.

### Riproduzione e artefatti

Comandi principali (nel repository locale):

```sh
python3 -B tests/Isolation/run.py --phpunit tests/Feature/BaselineFinalBoundaryAudit.php --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit tests/Feature/BaselineFinalAuthorizationAudit.php --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --fixtures tests/Isolation/baseline-final-fixtures.php --playwright tests/Feature/baseline-final.playwright.spec.js --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit-global --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
```

Log locali effimeri sotto `/private/tmp/ids-audit-approfondito-`: `mirati.log`, `autorizzazioni.log` (prima prova), `autorizzazioni-finale.log`, `browser.log` (prima prova), `browser-finale.log`, `globale.log`, `globale-finale.log`, `guardrail.log`, `policy.log`, `isolamento-effettivo.log`, `pint-finale.log`. Non versionati, non garanzia di conservazione futura. Report e sorgenti diagnostici conservano la riproducibilità senza dati operativi. Non riportare hash di password o contenuti sensibili dei log nei documenti.

### Modifiche della fase e raccomandazione

Quattro nuovi file permanenti, senza sovrascrittura dei preesistenti:

- `tests/Feature/BaselineFinalBoundaryAudit.php`: aspettative negative per A08/A09/A10 e due controlli positivi di sicurezza.
- `tests/Feature/BaselineFinalAuthorizationAudit.php`: matrice gestionale/tenant e logout.
- `tests/Feature/baseline-final.playwright.spec.js`: prove browser dei difetti con gruppo e reception.
- `tests/Isolation/baseline-final-fixtures.php`: sole fixture sintetiche, richiede la catena isolata già esistente.

Aggiornati soltanto questo rapporto e il Maestro mediante appendici. Inventario precedente di 66 file preservato; i quattro aggiuntivi portano a **32 tracked modificati + 38 untracked**. Nessuno staging, commit, push, fetch, deploy, produzione, DB operativo o trasmissione reale. I file preesistenti devono risultare byte-identici salvo le due appendici documentali.

Priorità: autorizzazione circoscritta ad A08 (privilegi reset legacy), A09 (contesto componenti token) e A10 (atomicità pubblica), con prove RED→GREEN e conservazione A01–A07. Successivamente completare sessioni multi-contesto e i percorsi operativi/importazioni/annualità rimasti parziali. Nessuna correzione automatica dei tre P1 né dei diagnostici A04/A05. I gate produttivi precedenti restano separati; nessuna readiness dedotta dal verde della suite.


### Chiusura effettiva della fase approfondita

Seconda globale conclusiva: **677 test/7.335 asserzioni PASS**, 80,133 secondi; prima 91,441 secondi. Nessuna nuova prova casuale richiesta/eseguita in questa fase; seed4208 rimane evidenza della fase precedente. Tutti sette runtime avviati risultano rimossi e i rispettivi log attestano la pulizia. Nessun file della directory operativa utilizzato come fixture.

Confronto finale dei4.188file iniziali: **4.186byte-identici**, sole due appendici documentali variate, zero eliminazioni e precisamente quattro nuovi file sopra elencati. Modifiche preesistenti e tutti34untracked iniziali conservati. MainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,32trackedmodificati+38untracked,stagingvuoto; nessunfetch. `git diff --check`PASS. Lint/Pint finali riguardano i nuovi file; i due diagnostici PHP sono stati eseguiti prima della sola formattazione Pint e rimozione di un import inutilizzato, senza cambiamenti funzionali o delle aspettative. Non dichiarata revisione pre-commit degli interi70file.

CODICE APPLICATIVO MODIFICATO:NO; TEST PREESISTENTI MODIFICATI:NO; NUOVI TEST/FIXTURE:SI; DOCUMENTAZIONE MODIFICATA:SI; CONFIGURAZIONE MODIFICATA:NO; SCHEMA/DATI OPERATIVI MODIFICATI:NO; SCHEMA/DATI SINTETICI:SI,solo effimeri rimossi; TEST ESEGUITI:SI; COMMIT:NO; PUSH:NO; DEPLOY:NO; TRASMISSIONI ESTERNE:NO.

**Fermarsi. Richiesta nuova autorizzazione circoscritta alla correzione di A08/A09/A10.** Le lacune critiche residue sono esplicitate nella matrice, non dichiarate completate. Nessuna baseline validata o readiness produttiva.


## Correzioni autorizzate A08–A10 e STOP su A11 — 9 ottobre 2026

**STOP — NUOVO P1 A11. BASELINE IN AUDIT — NON VALIDATA.** Applicate le sole correzioni autorizzate A08/A09/A10; diagnostici originali, regressioni specifiche, tre globali e browser risultano verdi. L'accettazione finale della fase resta **BLOCCATA** dal nuovo difetto sulle camere, riprodotto durante la verifica delle relazioni. Nessuna correzione A11 applicata, nessuna validazione generale o readiness produttiva.

### Correzioni e perimetro

| ID | Correzione applicata | Evidenza e limite |
|---|---|---|
| A08 | `StrutturaUserController::resetPassword` verifica il privilegio dopo la risoluzione della struttura: proprietario della gerarchia autorizzata oppure `canManageGestioneOperativa`. Reception e personale senza privilegio ricevono403 prima di validazione/mutazione password. La selezione del target rimane limitata alla struttura autorizzata. | Diagnostico originale immutato PASS; sei regressioni di ruolo: reception/personale non privilegiato negati; proprietario operativo, proprietario, admin della gerarchia e superadmin consentiti. Non riscritti contratti di creazione utenti o sessioni. |
| A09 | Nuovo metodo protetto `SchedinaController::componentiForSchedina`: comportamento gestionale predefinito equivalente alla query precedente. Override nel WebCheckinController rimuove soltanto lo scope nominato `struttura` e filtra esplicitamente struttura+Schedina già autorizzate. Usato per caricamento, conteggi e sincronizzazione esistenti/eliminazioni; creazione conserva payload riferito al parent. | Lettura, modifica, salvataggio e riapertura anonime; link completi/brevi; sessione di altro tenant non cambia il contesto del token; componente estraneo rifiutato; nessun allentamento globale del modello. |
| A10 | `WebCheckinController::publicStore` racchiude risoluzione richiesta/parent e intera operazione in `DB::transaction`: principale, camere, componenti e stato richiesta. Le validazioni esistenti restano attive; eccezioni comportano rollback dell'intera operazione. | Diagnostico422 invariato PASS; snapshot completo dei quattro gruppi di dati invariato su rifiuto; eccezione simulata nell'evento di persistenza finale della richiesta ripristina anche le scritture precedenti. Non dichiarata nuova validazione completa di ogni campo pubblico. |

L'override dei componenti è necessario anche al salvataggio: correggere soltanto la Collection della vista avrebbe lasciato la query degli esistenti vuota. La query gestionale ordinaria mantiene scope e filtro parent; non modificati i modelli fiscali, le regole Bellaria, le route A06, Questura/ISTAT, dipendenze, migration o configurazioni. Sole modifiche di stile Pint negli stessi controller autorizzati, inclusa rimozione di un import inutilizzato in StrutturaUserController.

### Prove effettive

| Prova | Risultato |
|---|---|
| Diagnostico originale `BaselineFinalBoundaryAudit.php`, byte invariato | **5 test /17 asserzioni PASS**, zero errori/failure/skip. Prima5/15 con3failure; incremento dovuto alle asserzioni ora raggiunte, nessuna aspettativa modificata |
| Nuove regressioni HTTP | **13 test /53 asserzioni PASS**:7 casi gruppi/atomicità e6 ruoli password |
| Globale ordinaria1 | **690 test /7.388 asserzioni PASS**, zero errori/failure/skip;99,764secondi |
| Globale ordinaria2 | **690 test /7.388 asserzioni PASS**, zero errori/failure/skip;90,174secondi;stesso runtime reinizializzato tra prove |
| Globale casuale | **690 test /7.388 asserzioni PASS**, zero errori/failure/skip;seed **4208**,97,677secondi |
| Browser conclusivo | **4/4 PASS**,9,9secondi:due diagnostici originali invariati e due nuovi percorsi modifica/salvataggio/riapertura via token completo e link breve |
| Guardrail e policy DB | **5 PASS +10 PASS**; ogni runtime attesta inoltre identità e23rifiuti prima delle migration |
| SOAP/ISTAT standalone offline | **14 PASS +22 PASS**,double in memoria,no bootstrap applicativo o trasporto reale |
| Lint/Pint | **7 PHP +1 JavaScript** sintassi PASS;Pint7file PASS;diffcheckPASS |
| Nuovo diagnostico camere A11 | **1 test /3 asserzioni,1failure**,zeroerrori/skip:salvataggio200 ma due relazioni invece di una |

Discovery eseguita nel launcher senza errori; il launcher tronca l'elenco mostrato a10.000caratteri, pertanto non usare l'estratto di103casi come totale. I JUnit ordinari attestano **690 casi unici**, inventario e asserzioni per caso identici nelle due esecuzioni. Riepiloghi minimizzati conservati in `/private/tmp/ids-a08a10-globali-1.json` e `globali-2.json`. Per il casuale è disponibile il log conclusivo, ma il JUnit non è stato acquisito prima della rimozione del runtime: **non attestata identità per caso di tutte e tre le esecuzioni**. Nessuna deprecazione/risky/warning segnalata nelle tre globali; messaggi diagnostici storici con stato importazione `ERRORE` sono output di test verdi, non failure della suite.

Regressioni nei JUnit ordinari: A01 14/112,A02 14/47,A03 34/143,A06 22/110,A07 11/73;Questura204/3656,ISTAT65/410,Tassa52/1586. Test preesistenti immutati. La scoperta di A11 conferma che una suite globale verde non copre ogni relazione del percorso anonimo.

### Verifiche browser e preparazione dei nuovi diagnostici

Due prove browser originali ora verdi, sorgente immutato: accompagnatore realmente presente nel modulo e reset legacy negato403 con sessione e CSRF reali. Nei nuovi casi il browser apre il tab Componenti, espande Dettagli quando richiusi, modifica il nome, salva tramite pulsante ufficiale, riapre e verifica valore persistito e un solo ID componente. Nessun errore JavaScript raccolto nei due nuovi casi.

Prime esecuzioni:2PASS/2FAIL perché il nuovo test provava a compilare i campi richiusi; successivamente3PASS/1FAIL nel percorso breve perché il test interagiva prima del caricamento completo della pagina destinazione. Corretto **soltanto il nuovo spec**, attendendo `load`, visibilità del pannello e usando Dettagli se necessario. Nessuna vista/script applicativo modificato, nessun click forzato o manipolazione DOM per ottenere verde, aspettative finali preservate e rafforzate con raccolta degli errori JavaScript. Prova conclusiva4/4PASS. La fixture aggiuntiva completa soltanto dati sintetici degli accompagnatori e tipo capo famiglia; non altera fixture preesistenti.

### A11 — nuovo P1 distinto, non corretto

**Causa:** `SchedinaCamera` usa `AppartieneAStruttura`. `SchedinaController::syncCamere` esegue `$schedina->camere()->delete()` prima di creare le nuove relazioni. Senza identità gestionale la relazione scoped esclude la camera esistente, quindi la cancellazione non la rimuove; `createMany` inserisce comunque il nuovo payload nel tenant autorizzato. L'atomicità A10 non risolve questa selezione errata: l'operazione valida può essere atomica e produrre comunque dati incoerenti.

**Riproduzione indipendente:** struttura sintetica, richiesta/token validi e parent coerente, una camera con `struttura_id` e `schedina_id` corretti, numero11. POST anonimo `/checkin/{token}` con payload valido e `camere=[{numero_camera:12,posti_letto:1}]`: HTTP200; conteggio DB finale **2**, atteso **1**. Non è una fixture con parent/tenant incoerente. Diagnostico `WebCheckinCameraBoundaryAudit.php` conserva l'aspettativa negativa ed è fuori discovery standard come gli altri Audit espliciti. La globale verde **non comprende questo nuovo failure**.

**Gravità e impatto:** P1 integrità del ciclo operativo, relazioni camere obsolete/aggiuntive dopo un salvataggio valido. Non dimostrata esposizione o modifica di un altro tenant, né quantificato un impatto su importi fiscali/tracciati. Il meccanismo della relazione e di syncCamere è già in HEAD; non attribuito a regressione della transazione A10. App/Models/SchedinaCamera e syncCamere non modificati in questa fase.

**STOP applicato:** nessun fix delle camere, nessuna modifica ulteriore a codice o test precedenti dopo la riproduzione; soltanto documentazione e verifiche Git finali. Necessaria nuova autorizzazione circoscritta al contesto delle relazioni camere anonime e ai relativi test di integrità. Preservare A01–A10 e verificare ripetibilità senza accumulo; non rimuovere globalmente lo scope.

### Riproduzione

```sh
python3 -B tests/Isolation/run.py --phpunit tests/Feature/BaselineFinalBoundaryAudit.php --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit tests/Feature/WebCheckinGroupAtomicityTest.php tests/Feature/LegacyPasswordAuthorizationTest.php --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit-global --phpunit-repeat 2 --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit-global --phpunit-random-seed 4208 --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --fixtures tests/Isolation/web-checkin-group-fixtures.php --playwright tests/Feature/baseline-final.playwright.spec.js tests/Feature/web-checkin-group.playwright.spec.js --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit tests/Feature/WebCheckinCameraBoundaryAudit.php --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
```

Tutti i comandi sono esclusivamente isolati; nessun Artisan/PHPUnit diretto sull'ambiente operativo. Log effimeri non versionati `/private/tmp/ids-a08a10-`:originali,regressioni,discovery,globali,random,browser,browser-finale,browser-conclusivo,browser-verificato,camere-diagnostico,guardrail,policy,soap,istat,pint. Nessun segreto o dato operativo riportato nei documenti.

### File della fase e Git

Dieci file modificati/creati nella fase, preservando gli interventi preesistenti:

| File | Modifica |
|---|---|
| `app/Http/Controllers/StrutturaUserController.php` | Guardia A08 e sola formattazione Pint |
| `app/Http/Controllers/SchedinaController.php` | Punto di query componenti con comportamento gestionale invariato |
| `app/Http/Controllers/WebCheckinController.php` | Override A09, conteggi/relazioni componenti, transazione A10 |
| `tests/Feature/LegacyPasswordAuthorizationTest.php` | Nuovo,6regressioni ruoli |
| `tests/Feature/WebCheckinGroupAtomicityTest.php` | Nuovo,7regressioni gruppi/rollback |
| `tests/Feature/web-checkin-group.playwright.spec.js` | Nuovo,salvataggio/riapertura full/short |
| `tests/Isolation/web-checkin-group-fixtures.php` | Nuovo,fixture sintetica isolata |
| `tests/Feature/WebCheckinCameraBoundaryAudit.php` | Nuovo,evidenza rossa A11 da preservare |
| `docs/baseline-applicativa-audit-2026-10-08.md` | Appendice della fase e STOP |
| `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md` | Appendice maestra e nuovo blocco |

Stato iniziale32tracked+38untracked; finale **33tracked modificati+43untracked**, totale76file da classificare prima di qualsiasi futuro consolidamento. Main,HEAD/originlocale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`,ahead/behindlocale0/0,stagingvuoto;nessunfetch. Dei4.192file iniziali,sole tre modifiche applicative autorizzate e due appendici documentali; cinque nuovi file,zeroeliminazioni. **4.187file iniziali byte-identici**,inclusi diagnostici e test preesistenti,A01/A02/A03/A06/A07,Questura/ISTAT,configurazioni,migration e dipendenze. I due controller condivisi Schedina/Web erano già modificati: preservati i cambiamenti precedenti,non ripristinati a HEAD.

Tutti dieci runtime effimeri attestati risultano rimossi. Watcher di sola lettura delle attestazioni terminati,nessuna risorsa DB operativa toccata. CODICE MODIFICATO:SI,solo3controller; TEST PREESISTENTI MODIFICATI:NO; NUOVI TEST/FIXTURE:SI; DOCUMENTAZIONE MODIFICATA:SI; CONFIGURAZIONE/DIPENDENZE MODIFICATE:NO; SCHEMA/DATI OPERATIVI MODIFICATI:NO; TEST ESEGUITI:SI; COMMIT/PUSH/DEPLOY/TRASMISSIONI ESTERNE:NO.

**Esito: verifiche A08–A10 verdi, accettazione finale BLOCCATA da A11.** Mantenere BASELINE IN AUDIT — NON VALIDATA. Restano inoltre sessioni multi-contesto, importazioni/ciclo operativo/annualità, policy token e diagnostici A04/A05/gate produttivi precedenti. Fermarsi e attendere nuova autorizzazione: nessuna correzione A11 implicita.


## Correzione autorizzata A11 — camere Web Check-in, 9 ottobre 2026

**A11 CORRETTO E VERIFICATO. BASELINE IN AUDIT — NON VALIDATA.** Questa appendice supera il precedente STOP su A11 dopo la nuova autorizzazione circoscritta. Non costituisce validazione generale, certificazione normativa o readiness produttiva.

### Causa riprodotta e correzione minima

Il diagnostico originale `tests/Feature/WebCheckinCameraBoundaryAudit.php`, preservato byte per byte, prima del fix esegue un POST anonimo valido200 e trova camera11 più camera12: **1 test /3 asserzioni,1failure**, nessun errore. La relazione `Schedina::camere()` usa `SchedinaCamera` con scope gestionale `struttura`; senza identità gestionale la cancellazione in `syncCamere()` non vede la camera precedente, mentre la creazione inserisce quella nuova. Il difetto non riguarda l'atomicità A10.

`SchedinaController::camereForSchedina()` restituisce la relazione gestionale originale. `syncCamere()` usa quella relazione per cancellazione e creazione, conservando mappatura e contratto esistenti, incluse più camere e rimozione esplicita. L'override `WebCheckinController::camereForSchedina()` opera sulla relazione della sola Schedina persistita: mantiene il vincolo `schedina_id`, rimuove esclusivamente lo scope nominato `struttura` e aggiunge `schedina_camere.struttura_id` uguale al tenant della Schedina. Il parent viene prima autorizzato da `linkedSchedinaForRichiesta()` rispetto alla struttura della richiesta/token (A02 invariata). Nessun bypass globale o modifica del modello/scope.

Il modulo pubblico precarica la stessa relazione autorizzata per mostrare la camera al successivo accesso. La transazione A10 dell'intero `publicStore()` resta invariata e include principale, camere, componenti e stato richiesta. Gli identificatori parent/tenant delle righe inviati dal client non sostituiscono quelli derivati dal parent autorizzato.

### Matrice e risultati effettivi

| Verifica | Evidenza | Esito |
|---|---|---|
| Diagnostico originale A11, immutato | RED1/3 con1failure; GREEN1/4 | PASS dopo fix |
| Nuove regressioni camere |9 test /72 asserzioni | PASS |
| Token completo e breve; camera precedente o nessuna camera | Tre salvataggi12→12→13 per ciascuno dei quattro scenari; esattamente una relazione e riapertura coerente | PASS |
| Camere multiple e rimozione | Sostituzione con due camere e successivo payload vuoto | PASS; nessun vincolo artificiale a una sola camera |
| Sessione appartenente a struttura diversa | Il token continua a selezionare solo la propria Schedina; camera esterna e altra Schedina dello stesso tenant invariate | PASS |
| Identificatori forgiati nelle righe | `id`, `struttura_id`, `schedina_id` esterni non modificano il parent/tenant assegnato server-side | PASS |
| Relazione artificialmente incoerente con tenant estraneo | Non letta né cancellata; nuova relazione pertinente al parent/tenant | PASS; nessuna riparazione automatica di dati incoerenti |
| Rifiuto422 e token inesistente404 | Snapshot delle quattro tabelle identici prima/dopo | PASS |
| Errore tardivo di persistenza | Eccezione sintetica nell'evento updating della richiesta, dopo le scritture; rollback integrale degli snapshot incluse camere | PASS; non simulato un guasto del server DB |
| Mirati cumulativi | Originale A11 + nuove camere + atomicità gruppo A09/A10 + diagnostici originali A08–A10: **22 test /130 asserzioni** | PASS |
| Browser Chromium reale | **6/6 PASS**: due casi camere full/short, due gruppo full/short, due diagnostici browser precedenti | PASS |
| Globale ordinaria1 | **699 test /7.460 asserzioni** | PASS |
| Globale ordinaria2, stesso runtime DB reinizializzato | **699 test /7.460 asserzioni** | PASS |
| Globale casuale, runtime distinto, seed4208 | **699 test /7.460 asserzioni** | PASS |
| Guardrail infrastruttura |5 prove | PASS |
| Policy DB |10 prove | PASS |
| SOAP Questura e ISTAT, doppi in memoria |14 e22 controlli | PASS; nessuna trasmissione |
| Lint e Pint |4 PHP e1 JavaScript; Pint4 PHP | PASS |
| `git diff --check` | Prima e dopo documentazione | PASS |

Tre globali con **zero failure, errori e skip**, nessuna deprecazione segnalata. I tre JUnit acquisiti prima della pulizia attestano gli stessi699casi unici e le stesse asserzioni per caso, indipendentemente dall'ordine. La discovery `*Test.php` include i nuovi nove casi; i diagnostici `*Audit.php` restano fuori dalla discovery e sono stati eseguiti esplicitamente nella prova mirata.

Regressioni aggregate nella globale: Questura204/3.656; ISTAT65/410; Tassa52/1.586. A01 14/112, A02 14/47, A03 34/143, A06 22/110, A07 11/73; A08 legacy password6/16, A09/A10 gruppo7/37. Nessuna implementazione certificata Questura/ISTAT/Tassa, regola fiscale, migration, dipendenza o configurazione del repository modificata in questa fase.

### Browser e isolamento

Le prove camere aprono il modulo tramite token completo o invito breve, modificano il campo reale, salvano12, ripetono12, poi salvano13; dopo ogni invio riaprono il modulo e verificano un solo campo visibile con il valore persistito. Nessun errore JavaScript nei due nuovi percorsi. Le prove originali di accompagnatori e reception sono immutate.

La fixture nuova abilita il campo camere facoltativo **solo nella copia applicativa effimera attestata** e sulla struttura sintetica del test. Questo rende osservabile la UI quando il flag ordinario è disabilitato; non modifica `config/app.php` del repository né alcun `.env`. I dati sono sintetici e non vengono copiati dal DB operativo.

Cinque runtime creati dall'harness (RED, mirati, browser, due ordinarie, casuale), tutti attestati prima delle migration con23rifiuti di override, identità MySQL/HTTP e riconnessione verificata. Le ordinarie condividono un solo runtime reinizializzato tra le esecuzioni. Tutti i cinque runtime sono fermati e rimossi. Storage dei singoli casi e sentinelle sono verificati anche dalle due regressioni globali `SuiteResourceIsolationTest` (2/7). Nessun DB o documento operativo letto/modificato; nessun servizio esistente riutilizzato.

### Riproducibilità

Tutti i comandi Laravel usano il launcher isolato, con inclusioni esplicite dei due sorgenti Tassa preesistenti non tracciati:

```sh
python3 -B tests/Isolation/run.py --phpunit tests/Feature/WebCheckinCameraBoundaryAudit.php tests/Feature/WebCheckinCameraIsolationTest.php tests/Feature/WebCheckinGroupAtomicityTest.php tests/Feature/BaselineFinalBoundaryAudit.php --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --fixtures tests/Isolation/web-checkin-camera-fixtures.php --playwright tests/Feature/web-checkin-camera.playwright.spec.js tests/Feature/web-checkin-group.playwright.spec.js tests/Feature/baseline-final.playwright.spec.js --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit-global --phpunit-repeat 2 --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit-global --phpunit-random-seed 4208 --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/test_guards.py
python3 -B tests/Isolation/test_database_policy.py
php -n tests/Safety/questura_security.php
php -n tests/Safety/istat_security.php
php -d auto_prepend_file= -d auto_append_file= vendor/bin/pint --test app/Http/Controllers/SchedinaController.php app/Http/Controllers/WebCheckinController.php tests/Feature/WebCheckinCameraIsolationTest.php tests/Isolation/web-checkin-camera-fixtures.php
```

Evidenze locali minimizzate fuori dal repository: `/private/tmp/ids-a11-{rosso,mirati,browser,globali,casuale}.log`, tre JSON JUnit `/private/tmp/ids-a11-junit-*.json`, snapshot iniziale `/private/tmp/ids-a11-iniziale.json`. I JSON conservano solo nomi dei casi, conteggi e asserzioni, senza dati degli ospiti o segreti. Percorsi effimeri: non sostituiscono i test e i risultati versionabili qui riportati.

### File della fase e preservazione

| File | Variazione A11 |
|---|---|
| `app/Http/Controllers/SchedinaController.php` | Query relazione camere gestionale e suo uso in syncCamere |
| `app/Http/Controllers/WebCheckinController.php` | Query camere per parent/tenant autorizzati e precaricamento nel modulo |
| `tests/Feature/WebCheckinCameraIsolationTest.php` | Nuovo: nove regressioni |
| `tests/Feature/web-checkin-camera.playwright.spec.js` | Nuovo: due percorsi reali full/short |
| `tests/Isolation/web-checkin-camera-fixtures.php` | Nuovo: fixture camere e flag UI nella sola copia effimera |
| `docs/baseline-applicativa-audit-2026-10-08.md` | Questa appendice |
| `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md` | Aggiornamento conoscenza/stato A11 |

Snapshot iniziale4.197file: **4.193byte-identici**, sole quattro variazioni autorizzate (due controller e due documenti), **zero eliminazioni**, tre nuovi file. La ricostruzione inversa in memoria dei soli blocchi A11 restituisce gli hash iniziali dei due controller: nessuna modifica aggiuntiva ai loro interventi preesistenti. Tutti i diagnostici, test, fixture ed evidenze precedenti, compreso A11 originale, sono preservati.

Git finale: main, HEAD e origin/main locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, ahead/behind0/0, stagingvuoto; **33tracked modificati +46untracked =79file**, worktree intenzionalmente non pulito. Nessun fetch, commit, push, deploy o accesso produzione. Inventario completo riportato sotto; pertinenza delle fasi precedenti resta quella delle rispettive appendici, senza autorizzazione a staging indiscriminato.

### Rischi residui e conclusione

Nessun nuovo P0/P1 indipendente riprodotto nel perimetro A11. La correzione riguarda relazioni coerenti con il tenant autorizzato e non riconcilia automaticamente eventuali righe storiche con tenant mancante/incoerente; l'inventario dei dati reali non è stato eseguito. Non attestata un'accettazione esterna o server.

Restano le lacune generali già documentate: sessioni multi-contesto, ciclo operativo/importazioni/passaggio annuale, politica token e diagnostici A04/A05; MariaDB applicativa reale isolata e gate CLI/FPM/SPanel/servizi esterni separati. Le sei prove Rocky di isolamento processo già PASS non sono state rieseguite.

**A11 CORRETTO E VERIFICATO; BASELINE IN AUDIT — NON VALIDATA.** Fermarsi e attendere autorizzazione alla verifica conclusiva. Nessun nuovo intervento applicativo, consolidamento Git o deploy implicito.

### Stato Git completo al termine A11

```text
M app/Http/Controllers/SchedinaController.php
 M app/Http/Controllers/StrutturaUserController.php
 M app/Http/Controllers/Superadmin/ImpersonazioneController.php
 M app/Http/Controllers/TassaDiSoggiornoController.php
 M app/Http/Controllers/TassaEsenzioneController.php
 M app/Http/Controllers/TassaReportController.php
 M app/Http/Controllers/WebCheckinController.php
 M app/Models/TassaDiSoggiorno.php
 M app/Services/TassaDiSoggiornoService.php
 M docs/DEPLOY_SPANEL.md
 M docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md
 M resources/views/layouts/topbar.blade.php
 M resources/views/schedina/list.blade.php
 M resources/views/schedina/partials/form.blade.php
 M resources/views/schedina/print-tassa.blade.php
 M resources/views/struttura/seleziona.blade.php
 M resources/views/tassa_di_soggiorno/edit.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-print.blade.php
 M resources/views/tassa_di_soggiorno/rapporto.blade.php
 M routes/web.php
 M tests/Feature/AccessTest.php
 M tests/Feature/CustomerImportedFiltersDeleteTest.php
 M tests/Feature/ExampleTest.php
 M tests/Feature/QuesturaC1MigrationAcceptanceTest.php
 M tests/Feature/SmokePagesQaTest.php
 M tests/Feature/TenancyTest.php
 M tests/Isolation/run.py
 M tests/Support/TestingEnvironment.php
 M tests/TestCase.php
 M tests/Unit/ComponentiImportFormTest.php
 M tests/Unit/ComponentiImportReviewStatusTest.php
 M tests/Unit/ComponentiImportServiceTest.php
?? app/Models/TassaExport.php
?? database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
?? docs/baseline-applicativa-audit-2026-10-08.md
?? docs/imposta-soggiorno-bellaria-audit-2026-10-07.md
?? docs/suite-globale-risanamento-2026-10-08.md
?? tests/Feature/ArriviEsenzioniTest.php
?? tests/Feature/BaselineFinalAuthorizationAudit.php
?? tests/Feature/BaselineFinalBoundaryAudit.php
?? tests/Feature/ImpersonazioneSecurityTest.php
?? tests/Feature/LegacyPasswordAuthorizationTest.php
?? tests/Feature/SchedinaCustomerReferenceSecurityTest.php
?? tests/Feature/StrutturaSelezioneTest.php
?? tests/Feature/SuiteResourceIsolationTest.php
?? tests/Feature/TassaBellariaAnteprimaTest.php
?? tests/Feature/TassaBellariaCompletenessTest.php
?? tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php
?? tests/Feature/TassaBellariaEtaCsvTest.php
?? tests/Feature/TassaBellariaRiallineamentoTest.php
?? tests/Feature/TassaBellariaVersionamentoTest.php
?? tests/Feature/WebCheckinCameraBoundaryAudit.php
?? tests/Feature/WebCheckinCameraIsolationTest.php
?? tests/Feature/WebCheckinFiscalContextTest.php
?? tests/Feature/WebCheckinGroupAtomicityTest.php
?? tests/Feature/WebCheckinParentSecurityTest.php
?? tests/Feature/WebCheckinShortLinkTest.php
?? tests/Feature/baseline-final.playwright.spec.js
?? tests/Feature/p1-baseline.playwright.spec.js
?? tests/Feature/struttura-selezione.playwright.spec.js
?? tests/Feature/tassa-bellaria-anteprima.playwright.spec.js
?? tests/Feature/tassa-bellaria-configurazione.playwright.spec.js
?? tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js
?? tests/Feature/web-checkin-camera.playwright.spec.js
?? tests/Feature/web-checkin-group.playwright.spec.js
?? tests/Feature/web-checkin-short.playwright.spec.js
?? tests/Isolation/baseline-final-fixtures.php
?? tests/Isolation/database_policy.py
?? tests/Isolation/p1-baseline-fixtures.php
?? tests/Isolation/struttura-selezione-fixtures.php
?? tests/Isolation/tassa-anteprima-fixtures.php
?? tests/Isolation/tassa-bellaria-fixtures.php
?? tests/Isolation/test_database_policy.py
?? tests/Isolation/web-checkin-camera-fixtures.php
?? tests/Isolation/web-checkin-group-fixtures.php
?? tests/Isolation/web-checkin-short-fixtures.php
?? tests/Support/IsolatedTestStorage.php
?? tests/Support/componenti-import-review-status.php
```


## Verifica finale della baseline completa — 9 ottobre 2026

**VERDETTO: NON VALIDATA. Stato del progetto: BASELINE IN AUDIT.** La verifica autorizzata dopo A11 ha completato le prove sulle sessioni e aggiunto controlli operativi/tenant. Quattro impedimenti P1 sono concretamente riproducibili. La conclusione non deriva dai problemi minori né dalla mancata accettazione dei servizi esterni. Nessuna correzione applicativa eseguita.

### Portata ed evidenze

Esaminati il quadro/protocollo del Maestro, il rapporto e le appendici correnti, le implementazioni dei percorsi residui, la discovery e le prove precedenti. Snapshot iniziale di4.200file, main HEAD/origin locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`;33tracked modificati+46untracked, stagingvuoto. Le sezioni precedenti descrivono stati storici: A01/A02/A03/A06/A07/A08/A09/A10/A11 restano corretti nel loro perimetro e non vengono riaperti.

Nuovi diagnostici autonomi con suffisso `Audit.php`, selezionati esplicitamente: nessun test/aspettativa precedente alterato o escluso. Il verde della discovery globale non include né nasconde questi diagnostici. I risultati qui sotto hanno origine in esecuzioni effettive, su runtime MySQL8.0.36/PHP8.3.33 e browser Chromium, tutti isolati dal launcher ufficiale.

### Impedimenti dimostrati all'accettazione

| ID | Gravità | Riproduzione effettiva | Causa nel codice e limite della prova | Criterio per chiusura |
|---|---|---|---|---|
| **A12** | **P1 sicurezza** | Login valido→accesso200→disattivazione dell'account→la sessione già aperta continua a ottenere200 su `/gestione-operativa`. HTTP indipendente e browser con due contesti. Il nuovo login dello stesso account disattivato è invece rifiutato422. | `LoginController::attemptLogin()` filtra `attivo=true`, ma `Authenticate` verifica l'autenticazione senza controllare lo stato corrente dell'account; `VerificaServizioStruttura` controlla il servizio della struttura. Non dimostrata escalation o lettura di altro tenant. La disattivazione non revoca l'accesso già autenticato. | Ogni successiva lettura/scrittura protetta dell'account disattivato negata, incluse sessioni esistenti; attivi e ruoli legittimi invariati. |
| **A13** | **P1 sicurezza** | Due sessioni reali dello stesso utente. Reset amministrativo oppure cambio password corretto: operazione302, nuovo login con nuova password riuscito, ma il contesto precedente ottiene ancora200 sul gestionale. Due casi browser distinti, entrambi rossi. | `GestioneOperativaController::updateMyPassword()` e `resetPassword()` aggiornano l'hash senza invalidare le altre sessioni. `AuthenticateSession` è commentato nel gruppo web. Non è il normale mantenimento della sessione che esegue il cambio: è una seconda sessione indipendente ancora autorizzata. Ricordami/cookie persistenti non completamente coperti. | Reset/cambio impediscono il riuso delle sessioni precedenti secondo una politica esplicita di revoca; verificare altri contesti, remember-me e mantenimento della sessione legittima dove previsto. |
| **A14** | **P1 sicurezza** | Token di recupero emesso quando l'account era attivo→account disattivato→POST valido `/password/reset`302→`attivo=false`, autenticazione vera e GET `/gestione-operativa`200. Diagnostico HTTP indipendente. | `ForgotPasswordController` aggiunge `attivo=true` alla richiesta di invio, ma `ResetPasswordController` usa il trait senza lo stesso vincolo. `ResetsPasswords::credentials()` non include `attivo`; il trait aggiorna la password e chiama `guard()->login()`. Il token viene creato direttamente dal broker di test: nessuna email reale. Dimostrato bypass della disattivazione con un token precedentemente valido, non compromissione di token altrui. | Un token precedente non deve autenticare/riabilitare un account disattivato; recupero valido su attivo, token scaduto/invalido/monouso preservati. |
| **A15** | **P1 integrità multi-tenant** | Utente strutturaA, cliente strutturaB estranea senza proprietario condiviso: POST `/arrivi` con `save_mode=to_arrivi` e customer_idB restituisce302 e crea **un collegamento cross-tenant**. ClienteB invariato. | `ArrivalsController::validateArriviModeRequest()` accetta un intero e `buildSchedinaPayload()` lo persiste, senza validare appartenenza o localizzazione del cliente. La correzione A03 in SchedinaController non copre questa route separata. Nessuna esposizione nominativa o modifica del clienteB dimostrata; l'integrità referenziale tra tenant è già violata dal vero controller, non da un collegamento artificiale. | Riferimenti esterni rifiutati prima di ogni scrittura oppure riuso/localizzazione soltanto nei confini della catena autorizzata; Arrivi legittimi e A03 preservati. |

Nessun P0 riprodotto. A12/A13/A14 riguardano controlli diversi del ciclo credenziali/sessioni e possono richiedere un intervento comune, ma hanno riproduzioni e criteri separati. Nessuna attribuzione a regressioni A01–A11: i meccanismi interessati sono nei file preesistenti; l'override camere A11 non li modifica.

### Problemi minori e rischi che non motivano questo verdetto

| Residuo | Classificazione | Evidenza e trattamento |
|---|---|---|
| **A16: creazione utenti legacy** | **P2 funzionale**, più gap autorizzativo da correggere | Proprietario operativo: POST `/strutture/utenti`500, `avatar` obbligatorio senza default non passato a User::create. Reception: stessa richiesta500 anziché403 e raggiunge il tentativo SQL. Non dimostrata creazione abusiva riuscita, arrestata dal vincolo DB; non qualificata come escalation ottenuta. La gestione operativa autorizzata crea/modifica/disattiva correttamente e rimane disponibile. Documentato, nessuna correzione né blocco autonomo dell'audit per questo residuo. |
| A04 diagnostici JavaScript VM | P2 diagnostico precedente | Tre rossi storici con DOM/binding incompleto, non nuovi rossi DOM attribuibili a queste modifiche. Non rieseguiti né corretti; restano da riallineare in fase autorizzata. I percorsi browser reali campionati restano verdi. |
| A05 IncompleteRead proxy | P2 ambientale precedente | Causa/endpoint originari non identificati; precedente riproduzione in memoria e prove di download documentate. Nessun nuovo IncompleteRead nelle esecuzioni conclusive, che non equivale a risoluzione della causa originale. Non modificato l'harness. |
| Politica token Web Check-in | Decisione/rischio residuo | Validità tecnica, rotazione/cancellazione, rifiuto di token invalidi/ambigui e parent tenant verificati. Nessuna TTL implementata, nessuna scadenza inventata dalle date soggiorno. Formalizzare durata/revoca/rate limit prima dell'accettazione dell'esposizione pubblica; non trasformata automaticamente in un nuovo P1 senza contratto/prova. |
| Righe legacy senza tenant/incoerenti | Dati reali non inventariati | A11 non riconcilia automaticamente relazioni storiche. Nessun accesso al DB operativo; nessuna attestazione sulla qualità del dataset reale. |
| Git misto | Gate di consolidamento | Gli84file locali appartengono a più fasi; non equivalgono a un inventario approvato per un unico commit. Nessuno staging autorizzato. |

### Matrice conclusiva delle lacune

| Ambito | Prove disponibili/aggiunte | Stato locale e ciò che resta |
|---|---|---|
| Login/logout, impersonazione e gerarchie | Regressioni A01/A08, matrice HTTP utenti esistente, browser uscita impersonazione e identità originale PASS | P1 precedenti preservati. **Accettazione sicurezza bloccata da A12/A13/A14**; non dalla collisione route già corretta. |
| Gestione utenti | HTTP operativo crea/modifica/disattiva senza escalation; reception e target esterni negati; reset legacy A08 protetto | Percorsi operativi verificati; legacy creazione P2 A16. Eliminazione/ricordami e tutte le gerarchie non attestati esaustivamente. |
| Recupero password | Attivo con token valido cambia password; token riutilizzato/invalido/scaduto rifiutati, account estraneo invariato | Casi positivi/negativi PASS. **Account disattivato con token preesistente BLOCCATO A14**. Non verificata consegna email esterna, trasporto mail array. |
| Strutture e selezione | Regressioni autorizzazione/selezione, browser navigazione e contesto tenant, dati di due strutture | Nessuna regressione nelle prove. Non dichiarata matrice esaustiva di ogni operazione amministrativa. |
| Schedine e gruppi | Suite e A03, HTTP singolo/famiglia/gruppo; browser formulario e navigazione | Nessuna regressione nei percorsi già certificati. **Percorso Arrivi con riferimento cliente esterno BLOCCATO A15**. |
| Web Check-in completo/breve | A02/A06/A07/A09/A10/A11, browser full/short con salvataggio/riapertura componenti e camere | Campione critico verde. Token valido non concede accesso al parent esterno; rollback e richieste convertite coperti dai test esistenti. Conversione di un intero gruppo/importazione nel browser e politica pubblica completa restano parziali. |
| Annualità/circuiti | Nuovo HTTP: Arrivo31/12/2026–03/01/2027, Arrivo10–12/01/2027, numeriA-26001/A-27001, conversione del primo in S-26001, tenant/date complete invariati | **PASS sul cambio anno operativo e numerazione campionati**. Arrivi operativi ancorati al giorno del test; non equiparati a prenotazioni future. Nessuna prova concorrenza della numerazione o nuova funzione di chiusura annuale inventata. |
| Clienti/importazioni | Nuovo HTTP su batch sintetico con righe già normalizzate: conferma2clienti, seconda conferma senza duplicazioni, show/conferma/delete da altro tenant404, errore sintetico sulla seconda creazione con rollback completo | **PASS persistenza/idempotenza/rollback/tenant campionati**. Non è una prova CSV completo upload→normalizzazione→conferma in browser: parser/preview coperti dalle suite preesistenti, composizione E2E completa ancora parziale. |
| Cestino e altri confini tenant | Suite Cestino e autorizzazioni preesistenti verde; nuovi rifiuti Arrivi esterni, batch e riferimenti | Rifiuti sui percorsi campionati PASS, **isolamento generale non validabile per A15**. Nessuna pretesa di copertura di ogni route/API. |
| Tassa Bellaria | Suite52/1.586 e browser/report/PDF/CSV precedenti; sorgenti tutti byte-identici | Nessuna regressione, ambito Bellaria documentato2026. Anni/categorie non certificati restano bloccati come previsto. Non nuova certificazione normativa. |
| Questura | Suite204/3.656,14controlli SOAP in memoria, trasportoOFF e sorgenti invariati | Verificato offline nel perimetro; nessun test/send esterno o accettazione ente. |
| ISTAT | Suite65/410,22controlli in memoria, trasportoOFF e sorgenti invariati | Verificato offline nel perimetro; nessuna trasmissione Ross1000 o accettazione ente. |
| UI/layout | Sei prove browser esistenti PASS: navigazione principale, impersonazione, gruppo/camera full/short | Nessuna nuova regressione nei percorsi interessati. Responsive esaustivo e ogni modulo secondario non nuovamente coperti. |
| CRM/supporto/licenze/GEO/dati reali | Codice/test/evidenze precedenti preservati | Non validazione totale delle funzioni amministrative secondarie né attestazione della qualità del dataset operativo. Da delimitare nell'accettazione generale; nessun nuovo difetto maggiore qui dichiarato senza prova. |

Le lacune residue non vengono presentate come nuove vulnerabilità: una prova mancante è distinta da un difetto. Per una futura dichiarazione di baseline **completa** serviranno sia la chiusura dei P1 dimostrati sia una matrice concordata dei percorsi ancora parziali. Le prove positive delle singole funzioni non sono estese a tutto il prodotto.

### Risultati effettivi, senza sommare suite sovrapposte

| Esecuzione conclusiva | Esito |
|---|---|
| Suite globale preesistente, nuova esecuzione | **699 test /7.460 asserzioni PASS**, zero failure/errori/skip, nessuna deprecazione segnalata |
| Diagnostici HTTP finali, inclusi gli originali | **24 test /107 asserzioni:5failure,0errori,0skip**, nessuna deprecazione segnalata |
| Diagnostico Arrivi autonomo, prima dell'aggregato finale | **1 test /2 asserzioni,1failure**, collegamento cliente esterno realmente persistito |
| Browser finale con contesti proxy/CSRF reali | **9prove:6PASS/3FAIL**, tre fallimenti sulle sessioni A12/A13 |
| Guardrail e policy DB | **5 e10 PASS** |
| SOAP Questura/ISTAT in memoria | **14 e22 PASS** |
| Lint nuovi diagnostici/fixture e Pint | **4PHP+1JS PASS; Pint4PHP PASS** |
| `git diff --check` | PASS |

I cinque failure HTTP finali sono A12, due casi A16 legacy, A14 e A15. I quattro nuovi casi operativi passano tutti: annualità/conversione, rifiuto arrivo esterno, conferma importazione ripetuta/batch esterno e rollback seconda riga. I11diagnostici preesistenti di autorizzazione/A08–A10/A11 passano. I tre browser rossi sono accesso200 da sessione precedente dopo disattivazione/reset/cambio; prima dell'asserzione negativa il test verifica effettivamente rifiuto del nuovo login disattivato o riuscita del nuovo login con password nuova.

Le tre globali699/7460 (due ordinarie e seed4208) della fase A11 restano evidenze ripetibili applicabili ai medesimi byte: nessun sorgente o test preesistente cambiato in questa revisione. Qui eseguita **una nuova globale**, non tre nuove. I conteggi specialistici sono quelli delle classi immutate già inventariate; la nuova globale esegue la medesima discovery.

### Setup corretti e prove intermedie conservate

- Prima HTTP18/71:3failure applicativi più1errore del nuovo setup, tabella `password_reset_tokens` non presente. Il nuovo diagnostico usa ora la tabella configurata in `auth.passwords.users.table`; aspettativa di scadenza invariata. Ripetizione18/74 con gli stessi3failure, zeroerrori.
- Prima preparazione browser interrotta durante la copia temporanea per rendere espliciti proxy e filtro origine anche nei nuovi contesti indipendenti. Nessun browser/DB avviato in quella preparazione; pulizia eseguita.
- Prime nuove fixture sessioni con utenti nella stessa struttura delle prove precedenti: l'azione autorizzata `shared_username` rinomina gli utenti della struttura e fa fallire login successivi che usavano username. Login nuovi passati a email; poi creata **struttura sintetica separata** per non interferire con le fixture precedenti. Le due prove browser intermedie4FAIL/5PASS non sono il risultato conclusivo; la finale6PASS/3FAIL distingue correttamente setup da difetti sulle sessioni.
- Primo aggregato23/97:5failure, incluso il nuovo scenario annuale scritto inizialmente come prenotazione futura. Il contratto Arrivi nel codice imposta il giorno operativo. Corretta esclusivamente la data del clock di test per le due giornate, senza cambiare assert su tenant/anno/circuito/date. Il caso finale PASS non certifica la conservazione di prenotazioni future nel circuito Arrivi.
- Il diagnostico A14 finale aggiunge evidenza minimizzata dello stato reale: accountfalse, reset302, autenticatotrue, accesso200; il vincolo di rifiuto resta negativo. Nessun hash/password/token riportato nel documento.

Dieci preparazioni/esecuzioni temporanee in totale, di cui nove avviate/attestate e una interrotta prima dell'avvio DB/browser. Tutte le nove attestazioni verificano processi/DB/HTTP, riconnessione e23rifiuti prima delle migration; tutti i dieci runtime sono rimossi. Migrazioni esclusivamente sul DB effimero. Proxy e filtro origine obbligatori anche nei contesti aggiuntivi, storage per caso e mail array; Questura/ISTATOFF. Nessun DB operativo letto o modificato, nessun ambiente/segreto produttivo copiato.

### Riproduzione

```sh
python3 -B tests/Isolation/run.py --phpunit tests/Feature/BaselineAcceptanceSecurityAudit.php tests/Feature/BaselineAcceptanceOperationalAudit.php tests/Feature/BaselineAcceptanceArriviAudit.php tests/Feature/BaselineFinalAuthorizationAudit.php tests/Feature/BaselineFinalBoundaryAudit.php tests/Feature/WebCheckinCameraBoundaryAudit.php --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --fixtures tests/Isolation/baseline-acceptance-fixtures.php --playwright tests/Feature/baseline-acceptance-sessioni.playwright.spec.js tests/Feature/web-checkin-camera.playwright.spec.js tests/Feature/web-checkin-group.playwright.spec.js tests/Feature/p1-baseline.playwright.spec.js --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit-global --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
```

Log locali fuori dal repository: `/private/tmp/ids-baseline-finale-diagnostici-conclusivi.log`, `/private/tmp/ids-baseline-finale-browser-finale.log`, `/private/tmp/ids-baseline-finale-globale.log`. I log intermedi sono conservati separatamente. I log PHPUnit possono contenere dettagli delle fixture e hash **sintetici** nei messaggi SQL delle richieste500: non versionati né riprodotti nei documenti. Evidenza versionabile: diagnostici, fixture, matrice e risultati sopra; non recuperati o mostrati segreti reali.

### Gate produttivi separati

MariaDB applicativa reale isolata, PHP CLI/FPM/SPanel, backup/restore, rilascio esatto, nuove migration, cache/storage e accettazione dei servizi esterni restano pendenti secondo il Maestro. Le sei prove Rocky/Bubblewrap di isolamento processo sono giàPASS e invariate: non riclassificate come fallite né rieseguite. Questi gate impediscono una dichiarazione produttiva, ma **non sono la motivazione dei quattro P1 locali** e non richiedono trasmissioni reali durante l'audit. Nessuna produzione aggiornata.

### Piano ordinato, da autorizzare prima di correggere

1. **A12/A14: disattivazione e recupero.** Unificare il controllo dello stato account nelle richieste protette e nel recupero prima di autenticare; preservare login attivi, errori generici, token validi/invalidi/scaduti/monouso e rifiuti tenant. Diagnostici originali devono diventare verdi senza indebolimenti.
2. **A13: revoca delle altre sessioni.** Definire/applicare revoca su reset e cambio credenziali, distinguendo sessione corrente, altre sessioni e remember-me. Verifica obbligatoria a più contesti, con prova dell'aggiornamento realmente riuscito. Evitare fix limitato all'interfaccia.
3. **A15: integrità Arrivi.** Validare/localizzare riferimenti clienti nel contesto tenant/catena ammesso prima delle scritture; aggiungere casi propri, estranei e catena autorizzata, rollback completo e regressioni A03. Nessuna modifica fiscale/Questura/ISTAT necessaria in questa diagnosi.
4. **A16 e residui P2.** Riparare creazione legacy e autorizzazione coerente con gestione operativa, oppure definire deprecazione controllata del percorso; A04/A05 con prove specifiche. Non necessario fingere che questi minori siano risolti per esprimere il verdetto corrente.
5. **Accettazione conclusiva.** Ripetere tutti i nuovi diagnostici e browser, poi suite globali ripetute/casuali, e completare o delimitare formalmente i percorsi E2E/importazioni/ricordami/secondari ancora parziali. Solo dopo rivalutare baseline; successivi gate Git e produzione separati.

### File della sola fase e stato Git

Cinque nuovi file diagnostici/fixture:

- `tests/Feature/BaselineAcceptanceSecurityAudit.php`;
- `tests/Feature/BaselineAcceptanceOperationalAudit.php`;
- `tests/Feature/BaselineAcceptanceArriviAudit.php`;
- `tests/Feature/baseline-acceptance-sessioni.playwright.spec.js`;
- `tests/Isolation/baseline-acceptance-fixtures.php`.

Sole estensioni a due file preesistenti: questo rapporto e `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`. Nessun codice applicativo, test precedente, configurazione, dipendenza o migration modificato. Snapshot4.200file: **4.198byte-identici**, due sole appendici autorizzate, **zero eliminazioni**, cinque nuovi file. Tutte le modifiche tracked/untracked iniziali preservate.

Git: main, HEAD/originmainlocale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, ahead/behind0/0, stagingvuoto, **33tracked modificati +51untracked =84file**; worktree intenzionalmente non pulito. Nessun fetch, commit, push, deploy, accesso SPanel/produzione o trasmissione reale.

**NON VALIDATA.** Il riesame è concluso anche sui residui minori: nessuna correzione automatica, nessuna accettazione implicita dei P1. StatoBASELINE IN AUDIT finché i blocchi dimostrati e i criteri della successiva accettazione non risultino superati.

### Inventario Git completo conclusivo

```text
M app/Http/Controllers/SchedinaController.php
 M app/Http/Controllers/StrutturaUserController.php
 M app/Http/Controllers/Superadmin/ImpersonazioneController.php
 M app/Http/Controllers/TassaDiSoggiornoController.php
 M app/Http/Controllers/TassaEsenzioneController.php
 M app/Http/Controllers/TassaReportController.php
 M app/Http/Controllers/WebCheckinController.php
 M app/Models/TassaDiSoggiorno.php
 M app/Services/TassaDiSoggiornoService.php
 M docs/DEPLOY_SPANEL.md
 M docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md
 M resources/views/layouts/topbar.blade.php
 M resources/views/schedina/list.blade.php
 M resources/views/schedina/partials/form.blade.php
 M resources/views/schedina/print-tassa.blade.php
 M resources/views/struttura/seleziona.blade.php
 M resources/views/tassa_di_soggiorno/edit.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php
 M resources/views/tassa_di_soggiorno/rapporto-print.blade.php
 M resources/views/tassa_di_soggiorno/rapporto.blade.php
 M routes/web.php
 M tests/Feature/AccessTest.php
 M tests/Feature/CustomerImportedFiltersDeleteTest.php
 M tests/Feature/ExampleTest.php
 M tests/Feature/QuesturaC1MigrationAcceptanceTest.php
 M tests/Feature/SmokePagesQaTest.php
 M tests/Feature/TenancyTest.php
 M tests/Isolation/run.py
 M tests/Support/TestingEnvironment.php
 M tests/TestCase.php
 M tests/Unit/ComponentiImportFormTest.php
 M tests/Unit/ComponentiImportReviewStatusTest.php
 M tests/Unit/ComponentiImportServiceTest.php
?? app/Models/TassaExport.php
?? database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
?? docs/baseline-applicativa-audit-2026-10-08.md
?? docs/imposta-soggiorno-bellaria-audit-2026-10-07.md
?? docs/suite-globale-risanamento-2026-10-08.md
?? tests/Feature/ArriviEsenzioniTest.php
?? tests/Feature/BaselineAcceptanceArriviAudit.php
?? tests/Feature/BaselineAcceptanceOperationalAudit.php
?? tests/Feature/BaselineAcceptanceSecurityAudit.php
?? tests/Feature/BaselineFinalAuthorizationAudit.php
?? tests/Feature/BaselineFinalBoundaryAudit.php
?? tests/Feature/ImpersonazioneSecurityTest.php
?? tests/Feature/LegacyPasswordAuthorizationTest.php
?? tests/Feature/SchedinaCustomerReferenceSecurityTest.php
?? tests/Feature/StrutturaSelezioneTest.php
?? tests/Feature/SuiteResourceIsolationTest.php
?? tests/Feature/TassaBellariaAnteprimaTest.php
?? tests/Feature/TassaBellariaCompletenessTest.php
?? tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php
?? tests/Feature/TassaBellariaEtaCsvTest.php
?? tests/Feature/TassaBellariaRiallineamentoTest.php
?? tests/Feature/TassaBellariaVersionamentoTest.php
?? tests/Feature/WebCheckinCameraBoundaryAudit.php
?? tests/Feature/WebCheckinCameraIsolationTest.php
?? tests/Feature/WebCheckinFiscalContextTest.php
?? tests/Feature/WebCheckinGroupAtomicityTest.php
?? tests/Feature/WebCheckinParentSecurityTest.php
?? tests/Feature/WebCheckinShortLinkTest.php
?? tests/Feature/baseline-acceptance-sessioni.playwright.spec.js
?? tests/Feature/baseline-final.playwright.spec.js
?? tests/Feature/p1-baseline.playwright.spec.js
?? tests/Feature/struttura-selezione.playwright.spec.js
?? tests/Feature/tassa-bellaria-anteprima.playwright.spec.js
?? tests/Feature/tassa-bellaria-configurazione.playwright.spec.js
?? tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js
?? tests/Feature/web-checkin-camera.playwright.spec.js
?? tests/Feature/web-checkin-group.playwright.spec.js
?? tests/Feature/web-checkin-short.playwright.spec.js
?? tests/Isolation/baseline-acceptance-fixtures.php
?? tests/Isolation/baseline-final-fixtures.php
?? tests/Isolation/database_policy.py
?? tests/Isolation/p1-baseline-fixtures.php
?? tests/Isolation/struttura-selezione-fixtures.php
?? tests/Isolation/tassa-anteprima-fixtures.php
?? tests/Isolation/tassa-bellaria-fixtures.php
?? tests/Isolation/test_database_policy.py
?? tests/Isolation/web-checkin-camera-fixtures.php
?? tests/Isolation/web-checkin-group-fixtures.php
?? tests/Isolation/web-checkin-short-fixtures.php
?? tests/Support/IsolatedTestStorage.php
?? tests/Support/componenti-import-review-status.php
```


## Correzione autorizzata A12–A15 — 9 ottobre 2026

**A12–A15 CORRETTI E VERIFICATI nel perimetro locale descritto. BASELINE IN AUDIT — NON VALIDATA.** Questa appendice supera i quattro blocchi P1 della verifica finale precedente, conservandone integralmente diagnosi, esiti rossi e limiti. Non corregge A16 e non costituisce accettazione generale o readiness produttiva.

### Obiettivo, cause e modifiche

Rischio **ALTO**: autenticazione, sessioni e riferimenti tenant. Cinque file applicativi: Kernel, nuovo AuthenticateAccountSession, EventServiceProvider, ResetPasswordController e ArrivalsController. Nessuna modifica a Questura, ISTAT, regole fiscali, modelli, route o migration rispetto all'inizio di questa fase.

| Difetto | Correzione | Evidenza effettiva |
|---|---|---|
| A12 — sessione di account disattivato ancora autorizzata | Controllo server-side di `attivo` nel gruppo web dopo StartSession e prima di binding, tenancy e controller; logout del dispositivo e pulizia della sessione | Diagnostico HTTP originale verde; due contesti Chromium, nuovo login rifiutato e cookie Ricordami rifiutato |
| A13 — seconda sessione sopravvive a reset/cambio password | Middleware Laravel AuthenticateSession riattivato tramite sottoclasse; hash acquisito all'evento Login, confronto a ogni richiesta, revoca delle sessioni con hash superato e rifiuto di quelle senza hash | Reset amministrativo, cambio personale, recupero valido e Ricordami provati in Chromium; nuova password accede, sessione precedente perde accesso; HTTP copre anche sessioni preesistenti senza hash |
| A14 — token precedente alla disattivazione autentica account inattivo | `attivo=true` imposto dal server nelle credenziali usate dal broker al consumo del token | Nessun cambio password o riattivazione nel test; modulo reale mostra errore e gestionale rifiutato; recupero valido, token invalido/scaduto e monouso restano verificati |
| A15 — Arrivi conserva customer_id di tenant estraneo | Validazione incondizionata del riferimento prima di ogni modalità di salvataggio; query limitata al perimetro già autorizzato dalla ricerca Arrivi; cliente della catena localizzato nella struttura corrente | Riferimenti esterni/inesistenti/malformati rifiutati 422 senza scritture; cliente proprio e nessun cliente consentiti; catena autorizzata riusata senza duplicati né modifica dell'origine; POST da sessione Chromium confermato |

Per A15 il semplice rifiuto di tutti i clienti non locali avrebbe eliminato il riuso di catena già esposto dalla ricerca. La versione finale conserva quel perimetro e riprende nel solo controller Arrivi la politica già esistente di localizzazione, equivalenza e numerazione di Schedina; il controller Schedina rimane byte-identico alla fotografia iniziale. Copia locale, creazione Arrivo, camere, componenti e numerazione sono racchiusi nella stessa transazione. Nessuna riconciliazione di dati reali o storici.

Le sessioni autenticate preesistenti prive dell'hash devono effettuare nuovamente il login: inizializzarle con la password corrente potrebbe conservare una sessione già superata da un reset. La sessione corrente dopo il cambio personale operativo e quella nuova dopo recupero valido restano utilizzabili; i dispositivi precedenti vengono rifiutati alla successiva richiesta. Non si dichiara chiusura fisica preventiva di tutte le sessioni persistite né una politica di revoca dei token API.

### Regressioni, diagnostici e risultati

Nuove regressioni permanenti: `AccountSessionSecurityTest` **9 test/47 asserzioni**, `ArriviCustomerReferenceSecurityTest` **7/45**, cinque nuovi casi nello spec `account-session.playwright.spec.js`, fixture sintetica `account-session-fixtures.php`. I tre casi originali browser sulle sessioni sono rieseguiti immutati.

- RED HTTP originale: **9 test/27 asserzioni, 5 failure** (A12, A14, A15 e due A16). Il RED A13 browser appartiene all'audit finale precedente; non viene dichiarata una nuova esecuzione browser rossa prima del fix in questa fase.
- Prima regressione mirata: **26/172, una failure** nel nuovo A14 perché l'asserzione sugli errori flash veniva effettuata dopo una seconda richiesta. Corretto soltanto l'ordine nel nuovo test: le aspettative di rifiuto, mancata autenticazione e accesso negato sono mantenute e rafforzate con password/attivo invariati. Successivi **28/190 PASS**, browser **9/9 PASS** prima dell'ampliamento.
- Prima globale: **714/7.537, una failure** Geo. Il test originale cambia tre identità con `actingAs` nella stessa sessione senza un evento Login; la seconda identità ereditava l'hash della prima. Aggiunto nel solo `tests/TestCase.php` il metodo `be` che inizializza l'hash quando il test imposta esplicitamente una nuova identità, come avviene al login reale. Nessun middleware disabilitato, aspettativa modificata o eccezione applicativa per i test. Compatibilità **42/330 PASS**. Due globali intermedie e una casuale seed4208: **714/7.546 PASS ciascuna**; precedono il rifiuto delle sessioni prive di hash e non sono conteggiate come globali definitive.
- Versione definitiva: mirati **48 test/369 asserzioni PASS**; **12/12 Chromium PASS**. Copre le quattro correzioni, recupero valido, Ricordami, regressioni di autorizzazione legacy, gruppo anonimo e camere full/short; quattro prove operative originali coprono anche conversione/annualità/importazioni/rollback.
- **Due globali definitive: 715 test/7.552 asserzioni PASS ciascuna**, zero failure/errori/skip, nessuna deprecazione segnalata. Stesso runtime attestato, DB reinizializzato tra esecuzioni. Due JUnit acquisiti e confrontati: 715 casi unici, inventario e asserzioni per caso identici. Nessuna globale casuale definitiva dichiarata. Questura **204/3.656**, ISTAT **65/410**, Tassa **52/1.586**, tutte verdi.
- Diagnostici originali finali immutati selezionati: **18 test/88 asserzioni, 3 failure**. Due restano A16 (creazione legacy 500, reception 500 invece di403); una è l'asserzione flash A14 dopo la richiesta di verifica accesso. Il relativo log attesta account ancora false, reset 302, autenticato false e accesso 302. La nuova regressione A14 verifica l'errore prima della seconda richiesta ed è verde. Non modificati né eliminati i diagnostici per ottenere una suite verde; `Audit.php` resta selezione esplicita fuori discovery globale.
- Guardrail **5 PASS**, policy DB **10 PASS**, SOAP **14 PASS** e ISTAT **22 PASS** soltanto in memoria; lint **9 PHP +1 JS PASS**, Pint **8 file PASS**, `git diff --check` PASS. ArrivalsController, preesistente con formattazione diversa, sottoposto a lint senza riformattazione generale.

[Evidenza strutturata, hash dei sorgenti/diagnostici e JUnit](evidenze-a12-a15-2026-10-09.json). Nove sorgenti PHP del checkout definitivo confrontati con gli hash della versione consegnata: identici. Test browser eseguiti successivamente all'ultima modifica applicativa, non inferiti dai soli test HTTP.

### Comandi e isolamento

Tutte le prove Laravel e Chromium usano esclusivamente `tests/Isolation/run.py`, checkout/storage/sessioni effimeri, nuova istanza MySQL8.0.36, server PHP dedicato e proxy obbligatorio. Ogni runtime avviato attesta processi/DB/HTTP e **23 rifiuti prima delle migration**; nessun ripiego. Il primo tentativo nel sandbox ha rifiutato il bind loopback prima di creare DB/runtime; successivi avvii consentiti dal controllo automatico. Le risorse dei runtime sono fermate/rimosse. Fixture camere abilita il campo facoltativo soltanto nel checkout effimero, come nelle prove A11; nessuna configurazione del repository alterata.

Inclusioni applicative nuove esplicite, oltre ai file tracked: nuovo middleware, TassaExport e migration Tassa già preesistenti all'inizio della fase. Comandi definitivi:

```sh
python3 -B tests/Isolation/run.py --phpunit tests/Feature/AccountSessionSecurityTest.php tests/Feature/ArriviCustomerReferenceSecurityTest.php tests/Feature/ImpersonazioneSecurityTest.php tests/Feature/GeoComuneLogoTest.php tests/Feature/BaselineAcceptanceOperationalAudit.php tests/Feature/BaselineAcceptanceArriviAudit.php --fixtures tests/Isolation/account-session-fixtures.php --playwright tests/Feature/baseline-acceptance-sessioni.playwright.spec.js tests/Feature/account-session.playwright.spec.js tests/Feature/baseline-final.playwright.spec.js tests/Feature/web-checkin-camera.playwright.spec.js --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit-global --phpunit-repeat 2 --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit tests/Feature/BaselineAcceptanceSecurityAudit.php tests/Feature/BaselineAcceptanceArriviAudit.php tests/Feature/BaselineAcceptanceOperationalAudit.php tests/Feature/BaselineFinalAuthorizationAudit.php --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/test_guards.py
python3 -B tests/Isolation/test_database_policy.py
php tests/Safety/questura_security.php
php tests/Safety/istat_security.php
php vendor/bin/pint --test app/Http/Kernel.php app/Http/Middleware/AuthenticateAccountSession.php app/Providers/EventServiceProvider.php app/Http/Controllers/Auth/ResetPasswordController.php tests/TestCase.php tests/Feature/AccountSessionSecurityTest.php tests/Feature/ArriviCustomerReferenceSecurityTest.php tests/Isolation/account-session-fixtures.php
node --check tests/Feature/account-session.playwright.spec.js
git diff --check
```

Lint tramite `php -l` sui nove file PHP elencati nell'evidenza strutturata; lo spec JavaScript è il decimo sorgente. Il comando dei diagnostici originali è eseguito prima dell'ultima aggiunta che rifiuta le sessioni senza hash; il suo esito non viene presentato come nuova esecuzione su quei byte finali. La regressione ordinata A14 e tutti i test permanenti sono invece eseguiti sulla versione definitiva.

### Conservazione, rischi residui e Git

Confronto con fotografia iniziale di 8.352 file (esclusi `.git`, vendor, node_modules e storage): sole sette variazioni preesistenti autorizzate — quattro file applicativi, estensione di TestCase e appendici dei due documenti — nessuna eliminazione. Cinque nuovi sorgenti della fase e una nuova evidenza JSON. Diagnostici e prove precedenti, Questura/ISTAT, tutte le migration e configurazioni del repository preservati. La fotografia include anche file locali non versionati: non è il precedente inventario Git dei4.200 file.

Restano **A16 P2**, A04/A05, decisioni TTL/revoca/rate limit del Web Check-in, ulteriori matrici dei moduli secondari e gate MariaDB/FPM/SPanel/backup/accettazione degli enti. Nessuna certificazione normativa, accettazione esterna o verifica produzione. BASELINE IN AUDIT, valutazione generale ancora PARZIALE; prossimo passo consigliato: autorizzazione circoscritta ad A16 e nuova accettazione concordata, senza consolidamento Git implicito.

Git: `main`, HEAD/origin locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, staging vuoto;37 tracked modificati+57 untracked alla chiusura. Nessun fetch: remoto corrente non attestato. Nessun commit/push/deploy/trasmissione reale né lettura o scrittura del DB operativo. Nessuno schema del progetto modificato; scritture/migration soltanto sui DB effimeri.

**Chiusura:** STATO VERIFICATO per A12–A15 nello scope descritto; valutazione generale PARZIALE e baseline NON VALIDATA. MAESTRO AGGIORNATO. CODICE MODIFICATO SI; TEST MODIFICATI SI; DOCUMENTAZIONE MODIFICATA SI; CONFIGURAZIONE MODIFICATA NO nel repository; SCHEMA/DATI MODIFICATI NO operativi, SI soltanto fixture effimere; TEST ESEGUITI SI; COMMIT NO; PUSH NO; DEPLOY NO; TRASMISSIONI ESTERNE NO.


## Correzione autorizzata A16 — 9 ottobre 2026

**A16 CORRETTO E VERIFICATO nel perimetro locale. A12–A15 confermati dalle nuove prove. BASELINE IN AUDIT — NON VALIDATA.** Questa appendice supera il residuo A16 delle sezioni precedenti, che rimangono integralmente conservate. Rischio ALTO per creazione account e autorizzazione; nessuna autorizzazione o operazione produttiva.

### Cause applicative e correzione minima

I due fallimenti A16 sono difetti applicativi, non errori di fixture:

1. **Creazione autorizzata500:** `StrutturaUserController::store` non passa `avatar` a `User::create`, mentre la migration originale richiede il campo senza default. Il modulo legacy non richiede un avatar all'utente, quindi il payload originale è legittimo. Inserito `avatar => ''`, coerente con il controller GestioneOperativa esistente, senza modificare schema, modello o configurazione.
2. **Reception500 invece di403:** il controller risolve la struttura ma non controlla i privilegi di gestione prima dell'inserimento. La reception raggiunge SQL e viene fermata dal vincolo avatar; il vecchio errore DB non è un controllo autorizzativo. Aggiunto controllo prima della validazione/scrittura su `store` e `create`, identico al criterio del reset legacy A08 già corretto: proprietario generale autorizzato dal tenant oppure `canManageGestioneOperativa`. Nessuna creazione abusiva riuscita dichiarata nel RED; nel GREEN il tentativo di creazione Eloquent è zero e lo snapshot utenti è invariato.

**Ulteriore difetto nello stesso percorso A16:** le viste `struttura/utenti/create` e `index` estendono `layouts.app`, inesistente nel repository. Il nuovo diagnostico del rendering riproduce GET creazione500. Entrambe passano a `layouts.master`, già usato dalle viste gestionali. Sono due sostituzioni di una riga, necessarie a mostrare modulo ed elenco dopo il salvataggio. Nessun layout globale, script UI o contenuto del modulo modificato.

Solo tre file applicativi della fase: controller e due viste. Rispetto alla fotografia iniziale, controller con tre righe aggiunte; reset A08 e risoluzione della struttura preesistenti byte-identici. Campi privilegiati del payload (`ruolo`, `ruolo_operativo`, `struttura_id`, `attivo`, `avatar`) non controllano i valori persistiti: nuovo account `struttura_user` nel tenant risolto, ruolo operativo null, attivo secondo il default già esistente, avatar vuoto e password memorizzata mediante hash. Accesso tramite email verificato; nessun privilegio di gestione attribuito implicitamente.

### A14 — problema separato del diagnostico originale

`BaselineAcceptanceSecurityAudit::test_recupero_precedentemente_emesso_non_riabilita_account_disattivato` resta **immutato**. Esegue POST reset, poi GET gestionale e soltanto dopo `assertSessionHasErrors` sul primo TestResponse. L'asserzione legge la sessione corrente, non uno snapshot della prima risposta: la seconda richiesta fa scadere il flash.

Nuovo `RecoveryFlashDiagnosticTest` **1 test/11 asserzioni PASS**: errore email presente immediatamente dopo il reset; account anonimo, password invariata e attivo false; GET successivo rifiutato302, flash scaduto, identità ancora anonima e credenziali invariate. La regressione A14 ordinata di `AccountSessionSecurityTest` rimane byte-identica e verde. Non viene prolungata artificialmente la vita del flash né modificato il contratto applicativo per soddisfare l'asserzione fuori ordine.

Diagnostici originali selezionati dopo il fix: **18 test/91 asserzioni, 1 failure**, soltanto il caso flash A14. I due casi originali A16 passano. Il log originale A14 attesta attivo false, autenticato false e accesso302; il fallimento residuo non è riclassificato come PASS né eliminato. La suite globale `*Test.php` non include questi `Audit.php`; la loro selezione esplicita e il suo esito rimangono separati.

### Risultati effettivi e limiti

- RED originale prima di modificare l'applicazione: **8 test/26 asserzioni, 3 failure** (due A16 e flash A14).
- Prime nuove regressioni prima del fix: **12/38, 6 failure** nei sei casi di ruolo; il nuovo test flash passa. Dopo l'aggiunta del solo caso dedicato alle viste, ulteriore RED con **7 failure visibili**, incluso GET creazione500: output del launcher troncato prima del riepilogo finale, nessun conteggio completo di asserzioni dichiarato per quella prova.
- Nuovo `LegacyUserCreationSecurityTest`: **12 test/89 asserzioni PASS**. Ruoli: reception e ruolo operativo mancante negati; proprietario operativo, proprietario generale, admin della catena e super_admin consentiti. Form e POST, identificatori/campi privilegiati forgiati, snapshot senza scritture sui rifiuti, assenza di tentativo Eloquent, dati mancanti/malformati/duplicati, tenant estraneo, CSRF errato, rendering e login nuovi account verificati su fixture.
- Mirati conclusivi **58 test/379 asserzioni PASS**: A16, durata flash, A12–A15, reset legacy A08, impersonazione, matrice autorizzazioni e quattro prove operative originali (annualità/conversione/importazioni/rollback).
- **Due globali 728 test/7.652 asserzioni PASS ciascuna**, zero failure/errori/skip, nessuna deprecazione segnalata. Stesso runtime attestato, DB reinizializzato tra prove. Due JUnit acquisiti: 728 casi unici, inventario e asserzioni per caso identici. I 715 casi della consegna A12–A15 mantengono le stesse asserzioni; delta13/100 corrisponde soltanto ai nuovi test. Questura **204/3.656**, ISTAT **65/410**, Tassa **52/1.586**, tutte verdi. Nessuna nuova globale casuale dichiarata.
- **Chromium finale 14/14 PASS**, in una nuova istanza isolata: creazione tramite modulo reale, due conferme di salvataggio già presenti, POST302, feedback di successo confermato e una sola riga nell'elenco, login nuovo account e negazione delle sue funzioni di gestione; reception con POST forgiato403 e assenza del nuovo account; tutti i dodici casi A12–A15/gruppo/camere della consegna precedente rieseguiti immutati.
- Browser intermedi conservati: **13/14 PASS**, un timeout nel nuovo caso di creazione; prova solo A16 **1/2 PASS**, stesso timeout anche con limite60secondi e validità HTML verificata. Causa del nuovo spec: attendeva POST senza completare le due conferme previste da `config-ux.js`. Corretto soltanto il nuovo spec per premere `Sì, salva` e `OK` attraverso la UI. Nessun bypass, DOM forzato, aspettativa indebolita, retry automatico o modifica dello script applicativo. Il timeout del nuovo spec non viene dichiarato un ulteriore difetto dell'applicazione. Ulteriore esecuzione14casi:13PASS/1FAIL, entrambi A16 verdi, timeout di navigazione nello spec originale camere short; già verde nella prima prova della fase. Conservato e rieseguito senza modificare sorgenti/aspettative nel runtime conclusivo. La sua causa non è dimostrata e il successivo PASS non cancella l'instabilità osservata. Un'altra esecuzione13/14 fallisce nel nuovo spec sulla riga accessibile dell'elenco: lo snapshot contiene già l'account creato, mentre il test non ha ancora completato il feedback di successo. Aggiunta soltanto al nuovo spec l'attesa del caricamento e la conferma `Operazione completata`/`OK`, mantenendo la verifica di una sola riga e del login; camere short verde in quella prova.
- Guardrail **5 PASS**, policy DB **10 PASS**, SOAP **14 PASS** e ISTAT **22 PASS** in memoria; Pint **4 file PASS**, lint **4 PHP+1 JS PASS**, build Vite nel checkout isolato e `git diff --check` PASS. Le due viste Blade sono renderizzate nelle prove HTTP e browser.

[Evidenza strutturata e hash](evidenze-a16-2026-10-09.json). I sei sorgenti non-JavaScript della fase nel checkout globale coincidono con la versione consegnata. Il solo spec nuovo JavaScript è stato corretto dopo l'avvio delle globali; non fa parte della discovery PHPUnit e viene eseguito nella prova Chromium finale. Sorgenti A12–A15, relativo supporto TestCase, diagnostici e precedente evidenza JSON preservati byte per byte.

### Comandi, isolamento e conservazione

Tutte le prove Laravel/browser utilizzano soltanto `tests/Isolation/run.py`: MySQL8.0.36, storage/sessioni/checkout/server HTTP effimeri; 23 rifiuti e attestazione processi/DB/HTTP prima delle migration, proxy obbligatorio, servizi esterni fake/double. Dieci runtime della fase avviati e rimossi; nessun DB operativo o ripiego su servizi preesistenti. Fixture browser estende quelle precedenti con una reception sintetica, senza modificarle. Il campo camere è abilitato soltanto nella copia effimera dalla fixture già esistente; nessuna configurazione del repository cambiata.

Comandi eseguiti (inclusioni di middleware/TassaExport/migration Tassa preesistenti esplicite):

```sh
python3 -B tests/Isolation/run.py --phpunit tests/Feature/BaselineAcceptanceSecurityAudit.php --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit tests/Feature/LegacyUserCreationSecurityTest.php tests/Feature/RecoveryFlashDiagnosticTest.php --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit tests/Feature/LegacyUserCreationSecurityTest.php --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit tests/Feature/LegacyUserCreationSecurityTest.php tests/Feature/RecoveryFlashDiagnosticTest.php tests/Feature/AccountSessionSecurityTest.php tests/Feature/ArriviCustomerReferenceSecurityTest.php tests/Feature/LegacyPasswordAuthorizationTest.php tests/Feature/ImpersonazioneSecurityTest.php tests/Feature/BaselineFinalAuthorizationAudit.php tests/Feature/BaselineAcceptanceOperationalAudit.php --fixtures tests/Isolation/legacy-user-fixtures.php --playwright tests/Feature/legacy-user.playwright.spec.js tests/Feature/account-session.playwright.spec.js tests/Feature/baseline-acceptance-sessioni.playwright.spec.js tests/Feature/baseline-final.playwright.spec.js tests/Feature/web-checkin-camera.playwright.spec.js --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit-global --phpunit-repeat 2 --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --phpunit tests/Feature/BaselineAcceptanceSecurityAudit.php tests/Feature/BaselineAcceptanceArriviAudit.php tests/Feature/BaselineAcceptanceOperationalAudit.php tests/Feature/BaselineFinalAuthorizationAudit.php --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --fixtures tests/Isolation/legacy-user-fixtures.php --playwright tests/Feature/legacy-user.playwright.spec.js --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --fixtures tests/Isolation/legacy-user-fixtures.php --playwright tests/Feature/legacy-user.playwright.spec.js tests/Feature/account-session.playwright.spec.js tests/Feature/baseline-acceptance-sessioni.playwright.spec.js tests/Feature/baseline-final.playwright.spec.js tests/Feature/web-checkin-camera.playwright.spec.js --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/test_guards.py
python3 -B tests/Isolation/test_database_policy.py
php tests/Safety/questura_security.php
php tests/Safety/istat_security.php
php vendor/bin/pint --test app/Http/Controllers/StrutturaUserController.php tests/Feature/LegacyUserCreationSecurityTest.php tests/Feature/RecoveryFlashDiagnosticTest.php tests/Isolation/legacy-user-fixtures.php
node --check tests/Feature/legacy-user.playwright.spec.js
git diff --check
```

I primi tre comandi rappresentano i RED sulla versione precedente del controller; il primo stato dei nuovi test precede l'aggiunta del caso viste. I comandi browser intermedi si riferiscono ai precedenti byte del solo spec nuovo, tutti documentati sopra. Lint tramite `php -l` sui quattro PHP del comando Pint. Nessun test originale modificato.

Fotografia iniziale di 8.358 file, esclusi .git/vendor/node_modules/storage: cinque sole variazioni preesistenti autorizzate (controller, due viste, due appendici), **8.353 byte-identici**, zero eliminazioni. Quattro nuovi sorgenti di test/fixture e una nuova evidenza JSON. Lavoro precedente, diagnosi originali, A12–A15, Questura/ISTAT, configurazioni e migration preservati.

### Verdetto, residui e Git

**VERIFICATO:** A16 e conferma locale A12–A15 entro le prove descritte. **PARZIALE:** valutazione generale; restano A04/A05, politica TTL/revoca/rate limit Web Check-in, matrice esaustiva dei moduli secondari e gate MariaDB/FPM/SPanel/backup/enti. Nessuna accettazione esterna, readiness produttiva o inventario di utenti/dati reali. L'elenco legacy e i collegamenti UI non sono oggetto di una revisione generale; il backend nega form/creazione non autorizzati. Non introdotti upload avatar o nuovi ruoli/default funzionali. A14 rimane un fallimento del diagnostico originale, separato dalla correzione applicativa verificata.

Git main, HEAD/origin locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, staging vuoto,39 tracked modificati+62 untracked. Nessun fetch, commit, push, deploy o trasmissione reale; nessuna operazione sul DB operativo e nessun cambio di configurazione/schema operativo. Maestro e rapporto aggiornati soltanto mediante appendici. Prossimo passo consigliato: concordare il perimetro di accettazione generale e valutare i residui P2; nessuna promozione automatica della baseline.

**Chiusura:** CODICE MODIFICATO SI; TEST MODIFICATI SI (nuovi file, originali invariati); DOCUMENTAZIONE MODIFICATA SI; CONFIGURAZIONE MODIFICATA NO; SCHEMA/DATI MODIFICATI NO operativi, SI soltanto fixture effimere; TEST ESEGUITI SI; COMMIT NO; PUSH NO; DEPLOY NO; TRASMISSIONI ESTERNE NO. **BASELINE IN AUDIT — NON VALIDATA.**


## Chiusura della valutazione locale — 9 ottobre 2026

**VERDETTO ESPLICITO: accettazione locale PARZIALE; accettazione completa NON DIMOSTRATA. BASELINE IN AUDIT — NON VALIDATA.** A12–A16 sono verificati nel perimetro delle regressioni e dei browser già documentati; nessun difetto applicativo locale ulteriore è stato confermato in questa fase. Le lacune di copertura locali sotto elencate impediscono la promozione della baseline anche indipendentemente dai gate produttivi. Le precedenti conclusioni bloccate da A12–A16 sono storiche e superate per questi singoli difetti, non per l'intero prodotto.

Obiettivo: classificare residui e requisiti di uscita, preservare i diagnostici, investigare il timeout camere e correggere soltanto problemi riprodotti. File: nuove copie diagnostiche A04/A14, prova camere con tracciamento, fixture/download sintetici, regressione proxy e unica modifica a `tests/Isolation/run.py`; appendici rapporto/Maestro ed evidenza JSON. Rischio circoscritto al proxy di test: una risposta upstream troncata deve produrre un fallimento esplicito, senza diventare successo parziale. Test: originali/copie, riproduzione in memoria, guardrail/policy e Chromium isolato. Nessun codice applicativo, configurazione o schema operativo modificato.

### Residui classificati per evidenza

| Pendente | Classificazione effettiva | Esito e limite |
|---|---|---|
| A16 | Difetto applicativo riproducibile, già corretto e VERIFICATO | Avatar, autorizzazione e layout corretti nella fase precedente; 12/89 regressioni, browser e globale conservati. Nessun nuovo intervento applicativo. |
| A14: asserzione flash fuori ordine | Problema del diagnostico | Originale `BaselineAcceptanceSecurityAudit.php` immutato. Copia identificata `BaselineAcceptanceSecurityFlashOrderAudit.php`: classe distinta e sola asserzione `assertSessionHasErrors('email')` spostata subito dopo POST reset, prima del GET che fa scadere il flash. Tutti gli otto casi e le medesime aspettative mantenuti; nessuna asserzione rimossa. Copia + `RecoveryFlashDiagnosticTest`: 9 test/41 asserzioni PASS. Il rosso originale 18/91 con un failure resta evidenza storica, non soppresso. |
| A04 VM | Problema del diagnostico confermato | Originale rieseguito: 3/3 FAIL. Copia `componenti-import-submit-complete-dom.test.mjs`: aggiunti soltanto metodo DOM `matches()` e binding `componentIndexInput`/`componentIdInput` mancanti; stesse tre aspettative POST/PUT/invio implicito, 3/3 PASS. Non certifica l'intero import nel browser. |
| A05 gestione risposta troncata | Problema del diagnostico confermato e corretto | Test del vero handler estratto tramite AST, con HTTPResponse e socket doppio in memoria: Content-Length10/corpo3 generava IncompleteRead non gestito (2 test, 1 errore). Correzione minima: intercetta IncompleteRead prima dell'invio, restituisce502 e registra solo conteggi; non invia200/corpo parziale. Dopo: 2/2 PASS, corpo completo preservato. Guardrail5/policy10 PASS. |
| Episodio A05 storico | Anomalia non riprodotta nelle nuove esecuzioni | Endpoint e causa upstream originari restano sconosciuti. La gestione dell'eccezione è corretta; non dichiarata la risoluzione della causa storica. Nessun IncompleteRead nei due nuovi browser; download CSV sintetico520010byte completo, hash e20001righe verificati. Non è una certificazione di ogni export applicativo. |
| Timeout camere storico | Anomalia non riprodotta | Conservato `a16-browser-finale.log`: short, goto completo dopo salvataggio, net::ERR_ABORTED/testtimeout30s; causa non dimostrata. Due nuovi runtime: originali full/short entrambi PASS ciascuno, sei campioni strumentati PASS ciascuno, nessun aumento del timeout30s. Tracciate richieste/risposte/errori JS e log HTTP integrali. Cancellazioni asset presenti durante cambi pagina, ma nessun documento fallito nei campioni; non attribuita automaticamente a queste cancellazioni la causa del timeout storico. |
| Dataset reale, compatibilità destinatario e accettazione esterna | Verifica riservata all'ambiente destinatario/operativo | Nessun accesso a dati o servizi operativi. Non convertire questi gate in difetti locali e non usarli per nascondere prove locali mancanti. |

### Requisiti locali ancora necessari per accettazione completa

Questi sono **lacune di prova o decisioni di contratto**, non difetti riprodotti né anomalie osservate: forzarli in una classificazione di guasto sarebbe una conclusione senza evidenza. Rimangono nello scope locale, separati dalle verifiche produttive.

| Requisito pendente | Evidenza acquisita | Criterio ancora non soddisfatto |
|---|---|---|
| Import clienti/componenti e P1.1/P1.2 | Parser/preview/servizi, HTTP persistenza/idempotenza/rollback/tenant verdi; A04 copia verde | Browser completo upload→normalizzazione→conferma→persistenza, annullamento e duplicati; non basta la VM. |
| Web Check-in gruppi | Parent/tenant, componenti, camere, rollback e richieste convertite coperti; full/short browser verdi | Conversione di intero gruppo e composizione con importazione nel browser. |
| Gestione utenti/strutture | A12–A16, remember-me e matrice ruoli A16 verificati; operativo crea/modifica/disattiva | Eliminazione prevista e creazione/gestione strutture lungo tutta la gerarchia; non inventare endpoint mancanti. |
| P1.3 Cestino e P1.6 tenancy | Suite e confini campionati verdi | Matrice completa read/restore/purge, entità globali, minimizzazione snapshot; lettura/scrittura/eliminazione/ricerca/import/export/API/relazioni/admin degli endpoint critici ancora non campionati. Rischio statico residuo, nessun exploit qui dichiarato. |
| Ciclo operativo/annualità | HTTP cambio anno e numerazione campionati PASS | Composizione E2E browser dell'intero ciclo e concorrenza numerazione non attestata; nessuna nuova funzione di chiusura annuale richiesta implicitamente. |
| Token pubblici | Validità tecnica, parent tenant, rotazione/cancellazione, token invalidi/ambigui verificati | Formalizzare durata, revoca e rate limit: assenza TTL non trasformata in bug senza contratto; date soggiorno non assunte come scadenza. |
| Moduli secondari/UI | Prove e sorgenti esistenti preservati | Delimitazione esplicita e matrice CRM/supporto/licenze/GEO/calendario/notifiche, responsive e operazioni amministrative non esaustive. Nessuna accettazione totale dedotta. |

### Risultati effettivi e riuso delle prove

- PHPUnit mirato copia A14 + flash: **9 test/41 asserzioni PASS**.
- A04 originale: **0/3 PASS**, copia: **3/3 PASS**; log separati conservati.
- Proxy prima: **2 test/1 errore**; dopo **2/2 PASS**. Guardrail **5 PASS**, policy DB **10 PASS**.
- Chromium prima della correzione proxy: **8/8 PASS**; dopo, nuova istanza con download: **9/9 PASS**. Ogni caso camere salva12→12→13 e riapre verificando una sola camera con valore persistito. Copia strumentata conserva identico flusso, timeout e aspettative; aggiunge tre campioni per modalità e ascoltatori diagnostici. Nessun retry automatico.
- Suite globali A16: **728 test/7652 asserzioni PASS, due esecuzioni**. Riutilizzate, non rieseguite: confronto di tutti i file iniziali conferma codice applicativo, regressioni preesistenti e configurazione byte-identici. La sola modifica preesistente è al ramo proxy HTTP, non usato da PHPUnit; verificato con test in memoria e browser dedicati. Hash JUnit e sorgenti nell'evidenza JSON. Anche A12–A15 e i sette file finali A16 invariati; browser finale precedente14/14 PASS conservato.
- Pint sui due nuovi PHP, lint PHP/Node e `git diff --check` PASS. I diagnostici `Audit.php` sono selezionati esplicitamente, non inclusi surrettiziamente nel totale globale.

Due runtime nuovi attestati, DB/storage/checkout/processi propri,23rifiuti prima delle migration, Chromium attraverso proxy vincolato all'origine. Fixture esclusivamente sintetiche effimere; entrambi i runtime fermati e rimossi. SOAP14 e preparazione offline del launcher PASS; nessun trasporto reale. Log integrali dei browser e HTTP copiati prima della pulizia, senza sovrascrivere gli artefatti A16. [Evidenza strutturata](evidenze-chiusura-locale-2026-10-09.json) include hash, tracce aggregate, risultati negativi e limiti; i log temporanei non sono permanenza garantita dopo questa sessione.

Riproduzione mirata:

```sh
node --test tests/Unit/componenti-import-submit.test.mjs
node --test tests/Unit/componenti-import-submit-complete-dom.test.mjs
python3 -B tests/Isolation/test_proxy_response.py
python3 -B tests/Isolation/test_guards.py
python3 -B tests/Isolation/test_database_policy.py
python3 -B tests/Isolation/run.py --phpunit tests/Feature/BaselineAcceptanceSecurityFlashOrderAudit.php tests/Feature/RecoveryFlashDiagnosticTest.php --playwright tests/Feature/web-checkin-camera.playwright.spec.js tests/Feature/web-checkin-camera-navigation-audit.playwright.spec.js --fixtures tests/Isolation/legacy-user-fixtures.php --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
python3 -B tests/Isolation/run.py --playwright tests/Feature/web-checkin-camera.playwright.spec.js tests/Feature/web-checkin-camera-navigation-audit.playwright.spec.js tests/Feature/proxy-download-audit.playwright.spec.js --fixtures tests/Isolation/local-closure-fixtures.php --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php
```

### Gate produttivi e consegna separati

Restano MariaDB applicativa isolata sul destinatario Linux, PHP CLI/FPM/SPanel, backup/restore e rollback migration Tassa, configurazione/cache/storage/ownership/queue/cron/SSL, rilascio su SHA esatto e consolidamento dell'inventario Git. Restano qualità dei dati reali, credenziali/codici e accettazione Questura/ISTAT/mail nei rispettivi ambienti autorizzati. Le sei prove Rocky/Bubblewrap di isolamento processo già PASS sono invariate e non rieseguite. Nessun commit/push/deploy/trasmissione: questi gate sono distinti dall'accettazione locale incompleta.

Preservazione: snapshot iniziale8363file; prima delle appendici8362byte-identici, sola variazione launcher; zero eliminazioni. Originali A14/A04/camere invariati, nessuna aspettativa indebolita. Documenti aggiornati solo con appendici. Git main e HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,staging vuoto, nessunfetch; worktree misto preservato. Tenancy/sicurezza: nessun filtro applicativo modificato, nessun dato operativo.

Flag: codice applicativo NO; test/infrastruttura SI; documentazione SI; configurazione/schema operativo NO; dati test SI, solo effimeri; commit NO; push NO; deploy NO; trasmissioni reali NO. Prossimo passo locale: completare i criteri di prova e formalizzare il contratto token; nessuna promozione della baseline giustificata da questa fase.


## Completamento delle prove locali pendenti — 9 ottobre 2026

**ACCETTAZIONE LOCALE POSITIVA DEL PERIMETRO ESEGUITO E DELIMITATO. Accettazione funzionale dell’intera baseline PARZIALE, perché manca il contratto dei token. BASELINE IN AUDIT — NON VALIDATA. Nessuna validazione produttiva.** Questa appendice aggiorna le lacune della precedente chiusura senza cancellare diagnosi, risultati negativi o verdetti storici. Il verdetto definitivo della fase è riportato nella matrice sottostante e delimitato al perimetro verificato; non costituisce validazione produttiva.

### Difetti confermati e correzioni minime

- Supporto: POST senza il campo facoltativo `modulo_riferimento` produceva500 per chiave assente. Accesso con fallback null, senza cambiare requisiti o autorizzazioni.
- Calendario: compleanni dei componenti interrogati con confronto tra DATE e stringa vuota, respinto da MySQL strict. Eliminato il solo confronto vuoto, mantenuto `whereNotNull`; regressione con compleanno e data null.
- Strutture Admin/Superadmin: i sei campi geografici fisicamente obbligatori potevano mancare alla creazione e provocare500. Richiesti in POST, omissibili in aggiornamento, non vuoti se presenti. Superadmin non passava tipologie/telefono/email obbligatori: aggiunti i valori iniziali coerenti con il percorso Admin. Creazione/modifica/eliminazione e rifiuti gerarchici verificati.
- Numerazione Arrivi/Schedina: due vere richieste parallele con connessioni distinte salvavano due righe con lo stesso codice. Serializzazione per struttura tramite lock della riga Struttura entro le transazioni già previste; inclusi aggiornamento, import e conversione Arrivi→Schedina. Regressioni concorrenti: due percorsi, quattro richieste302, tutte persistite e numeri distinti. Nessun nuovo indice o schema.
- GEO: la vista della schedina passa `nazione_text`, ma il componente leggeva soltanto `nazione`; il browser perdeva il valore iniziale obbligatorio. Corretti i soli quattro chiamanti nel form Schedina perché passino `nazione`, la chiave prevista dal contratto. Core GEO e hash immutabili byte-identici alla fotografia iniziale. Regressione HTTP1/5PASS e browser persistono nazioni/documento/tipo via.

- Conversione Web: circuito Schedina persistito nel browser ma richiesta ancora `in_compilazione`; protezione pubblica dipendente da `convertito`. Aggiornata solo la richiesta collegata al parent e tenant salvati, dentro la transazione, quando entra in Arrivi/Schedina; non sovrascritta la prima data di conversione. Nuova regressione controlla stato/data/GET senza modulo e POST anonimo incapace di alterare il parent.

I guasti applicativi sono separati dai problemi delle nuove fixture: email obbligatoria Web, ripristino con nuovo ID previsto per tre classi, catalogo alloggiato20 mancante, import DB mancante nel diagnostico, payload Customer PUT senza privacy, route osservazione dopo catch-all e duplicati clienti senza chiave stabile. Corrette soltanto le nuove prove/fixture; originali e aspettative funzionali preservati. Nomi accessibili con icone, conferme SweetAlert e attese pagina gestiti nel nuovo spec. I risultati intermedi restano nell'evidenza strutturata.

### Matrice finale: requisito, criterio, evidenza, risultato e residuo

| Requisito e criterio di accettazione | Evidenza effettiva | Risultato e pendente concreto |
|---|---|---|
| Import clienti/componenti: upload reale, normalizzazione, conferma e persistenza tenant; annullamento senza scritture; conferma monouso e duplicati | `LocalAcceptanceMatrixAudit`, browser `local-import-cycle`; CSV reali sintetici, data01/01/1980→1980-01-01, snapshot DB tramite osservatore read-only nella sola copia effimera | PASS, entrambi i casi Chromium finali; nessun pendente eseguibile della matrice import. |
| Intero gruppo Web Check-in→import componenti→Arrivi→Schedina, nessuna perdita/duplicazione e richiesta convertita | Browser anonimo e gestionale: principale+accompagnatore+componente importato, conteggio3, ID componenti conservati, tenant invariato, stato Web finale | PASS Chromium finale. Corretto anche lo stato Web rimasto in_compilazione: aggiornamento tenant+parent nella transazione operativa, prima data conservata. Regressione HTTP blocca modifiche anonime successive. |
| Gerarchie amministrative previste: creazione/modifica/eliminazione, accessi positivi e negativi | Matrice Admin/Superadmin/proprietario/operativo, strutture/proprietari; `LegacyUserCreationSecurityTest`, `StrutturaAuthorizationTest`, regressioni A12–A16 e browser storici14/14 | PASS nel contratto degli endpoint implementati; nessuna funzione di eliminazione inesistente inventata. |
| P1.3 Cestino: lettura/ripristino/purge, entità tenant e globali, minimizzazione | Tutte16classi dichiarate da `CestinoService`, più `CestinoSecurityTest`; password/token non esposti, token ripristinato diverso, conferma DB | PASS; non certifica combinazioni di archivi operativi incoerenti. |
| P1.6 tenancy: lettura/scrittura/delete/ricerca/import/export/relazioni/admin critici |20percorsi A/B aggiuntivi, snapshot invariati sui rifiuti403/404, ricerca restituisce solo tenant A; regressioni globali e autorizzazioni preesistenti | PASS nel perimetro enumerato dai test; non ogni endpoint o permutazione possibile. |
| Ciclo operativo, annualità, numeri concorrenti | HTTP originale2026→2027 conserva date e reset progressivo; browser gruppo; `LocalNumberingConcurrencyAudit`2/28PASS | PASS annualità HTTP, concorrenza e ciclo browser; nessuna nuova funzione annuale introdotta. |
| Token pubblici: validità tecnica e revoca, durata e limiti | `LocalPublicTokenContractAudit`1/4PASS: token di90giorni con soggiorno passato200, rotazione vecchio404/nuovo200, cancellazione404; GET ha solo middleware web; route pubbliche prive di throttle dedicato | Contratto implementato osservato PASS; **accettazione della politica NON COMPLETABILE**: mancano durata approvata, evento di scadenza, regole di revoca, soglia/finestra/chiave del rate limit. Serve un contratto funzionale esplicito e poi regressioni dei confini temporali/429. Non è un gate produttivo; assenza TTL non chiamata bug senza requisito. |
| Moduli secondari e responsive delimitati | HTTP CRM creazione/modifica/stato/note/accessi; supporto creazione/risposta/tenant; licenze tramite servizio struttura e stampa; GEO upload/sostituzione/rimozione/autorizzazioni; calendario eventi/stato/compleanni, notifiche. Browser2/2PASS:12pagine per viewport1440×900 e390×844, contenuto visibile, larghezza main entro viewport, nessun errore JS | PASS della matrice concreta; non tutte azioni su ogni viewport, browser o dispositivo, né qualità grafica universale. Nessun invio esterno esercitato. |
| Proxy audit: integrità corpo completo, rifiuto troncamento e download grande |2/2regressioni in memoria sul vero handler estratto AST; truncation10/3→502, zero corpo/200, log soli conteggi; Chromium download520010byte/hash/20001righe PASS | PASS. **Solo strumento di audit `tests/Isolation/run.py`, nessuna modifica al proxy/codice applicativo.** Episodio upstream A05 storico non identificato. |

### Risultati, riuso e limiti

Prima globale dopo le sei correzioni controller: **728 test/7652 asserzioni PASS**, ordine casuale seed20261009,0failure/errori/skip. Mirati finali **135/983PASS**; concorrenza **2/28PASS**; token **1/4PASS**. Questi insiemi si sovrappongono e non vanno sommati. Gli `Audit.php` nuovi richiedono selezione esplicita e non sono inclusi nel totale della discovery globale. Globale conclusiva dopo i fix dei chiamanti GEO e dello stato Web: **728/7652PASS**, runtime indipendente, seed20261009,0failure/errori/skip. Ripetizione giustificata dalle ultime modifiche applicative; nessuna terza globale eseguita.

Verifiche conclusive aggiuntive GEO/conversione/link brevi/atomicità: **31 test/165 asserzioni PASS**. Browser finale import/ciclo: **2/2PASS**, nessun retry automatico e nessun aumento del limite90s dei nuovi casi. A12–A16: mirati relativi inclusi nella selezione135 e nella globale; browser precedente14/14 conservato, senza ripetizione immotivata. Timeout camere: prove originali e campioni strumentati precedenti8/8 e9/9 restano valide; causa storica non provata. Nuovo ciclo ha anche osservato un'attesa `load` molto lunga dopo Arrivi, conservata; passaggio a DOMContentLoaded nel solo nuovo spec non dimostra la causa del timeout originale.

A14 originale byte-identico: flash asserito dopo GET successivo, problema del diagnostico già documentato. Copia identificata conserva tutti8casi, sposta soltanto l'asserzione prima del GET; copia e regressione incluse nei mirati finali, senza alterare originali o cancellare rossi. A04 originale e copia pure immutati; nuova verifica E2E aggiunta separatamente.

Guardrail5/policyDB10/proxy2PASS; launcher attesta ogni runtime con23rifiuti prima migration; SOAP14 in memoria, mail array e origini esterne bloccate. Checkout, DB MySQL, processi, sessioni, upload e storage propri; solo dati sintetici. Nessun fallback. La route di osservazione è creata dalla fixture nella copia temporanea prima del catch-all, autorizzata al solo utente sintetico e read-only: nessuna route del repository modificata.

Durante il teardown della prima prova concorrente dopo fix, rollback `down` storico ha dato1553 su vincolo/indice. Non eliminato né attribuito al fix numerazione: il nuovo diagnostico usa ora `migrate:fresh` nel solo DB attestato e directory univoca. Il gate di rollback migration destinatario resta separato e aperto. Cinque controller avevano già difformità Pint prima di questa fase, comprovate ricostruendo le versioni iniziali con hash identici; non applicata riformattazione generalizzata. Schedina aveva una sola nuova riga vuota richiesta dallo stile: corretta, equivalenza token PHP con sorgente della prima globale confermata. Lint, Pint nuovi file e diffcheck documentati separatamente dai residui storici.

### Gate destinatario e produttivi, distinti

MariaDB applicativa isolata sul destinatarioLinux, CLI/FPM/SPanel, backup/restore/rollback migration, SHA di rilascio, config/cache/storage/ownership/queue/cron/SSL, qualità dati reali e accettazione Questura/ISTAT/mail restano aperti. Sei prove Rocky/Bubblewrap storiche PASS riutilizzate; non sostituiscono questi gate. Nessun accesso operativo, variazione di configurazione/schema operativo, commit/push/deploy o trasmissione reale.

[Evidenza strutturata](evidenze-accettazione-locale-2026-10-09.json): hash dei log e JUnit, classificazioni dei negativi, preservazione e limiti. I log completi sono conservati con nomi distinti in `/private/tmp/accettazione-*`; la loro permanenza dopo la sessione non è garantita. I dati sintetici dei runtime vengono rimossi dal launcher; le evidenze storiche del repository non sono eliminate.

### Riproduzione nello stesso perimetro isolato

```sh
python3 -B tests/Isolation/run.py --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php --phpunit tests/Feature/LocalAcceptanceMatrixAudit.php tests/Feature/LocalCustomerWriteDiagnosticAudit.php tests/Feature/CestinoSecurityTest.php tests/Feature/StrutturaAuthorizationTest.php tests/Feature/GeoComuneLogoTest.php tests/Feature/LegacyUserCreationSecurityTest.php tests/Feature/BaselineAcceptanceSecurityFlashOrderAudit.php tests/Feature/BaselineAcceptanceOperationalAudit.php
python3 -B tests/Isolation/run.py --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php --phpunit tests/Feature/LocalNumberingConcurrencyAudit.php tests/Feature/LocalPublicTokenContractAudit.php
python3 -B tests/Isolation/run.py --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php --phpunit tests/Feature/LocalGeoInitialValueAudit.php tests/Feature/LocalWebConversionAudit.php tests/Feature/WebCheckinShortLinkTest.php tests/Feature/WebCheckinGroupAtomicityTest.php --fixtures tests/Isolation/local-acceptance-fixtures.php --playwright tests/Feature/local-import-cycle.playwright.spec.js tests/Feature/local-secondary-responsive.playwright.spec.js tests/Feature/proxy-download-audit.playwright.spec.js
python3 -B tests/Isolation/run.py --include app/Http/Middleware/AuthenticateAccountSession.php app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php --phpunit-global --phpunit-random-seed 20261009
python3 -B tests/Isolation/test_proxy_response.py
python3 -B tests/Isolation/test_guards.py
python3 -B tests/Isolation/test_database_policy.py
```

I comandi riproducono le selezioni e consentono di comporle; non implicano che le prove storiche siano state tutte eseguite nello stesso runtime o sull'ultima revisione della fixture. Le selezioni effettive, i log separati e i limiti di riuso restano distinti.

Preservazione finale: fotografia8370file, zeroeliminazioni; sei controller, form Schedina e due appendici documentali come sole variazioni di file preesistenti. Diagnostici originali A04/A14/camere, core GEO/hash e launcher proxy byte-identici alla fotografia iniziale. Staging vuoto; main e HEAD/origin locale invariati, nessunfetch. Flag: codiceSI, testSI, docsSI; DB/config/schemaoperativiNO; datitestSI soloeffimeri; commit/push/deploy/trasmissionirealiNO.

Attestazione conclusiva: **29 runtime di questa fase tutti rimossi**, snapshot8370file con8361byte-identici e9solevariazioni autorizzate (7sorgenti+2appendici), zeroeliminazioni. Prefissi storici dei documenti verificati mediante hash; originali A04/A14/camere e core GEO/hash preservati. I due JUnit globali hanno728casi unici e asserzioni per caso identici. Stagingvuoto/main/divergenzaHEAD-originlocale0/0; lint/Pint8/diffcheckPASS, ferme le5difformità Pint preesistenti documentate.


## Contratto dei token: analisi e decisioni pendenti — 9 ottobre 2026

**PROPOSTA NON APPROVATA E NON IMPLEMENTATA. Accettazione locale positiva del perimetro già verificato invariata. BASELINE IN AUDIT — NON VALIDATA; nessuna validazione produttiva.** [Analisi completa, inventario corrente e cinque decisioni D1–D5](contratto-token-proposta-2026-10-09.md).

Ricercate prima le regole del Maestro e delle fonti subordinate, poi confrontati controller/modelli/route/configurazioni/test e framework installato. P1.4 richiede scadenza/revoca/riuso/isolation, ma nessuna durata o soglia pubblica approvata è stata trovata. Non adottate automaticamente l'assenza di TTL e le date non invalidanti osservate nei test. Completo64, invito codice+prefisso8, completi legacy e token80 del Cestino sono rappresentazioni dello stesso Web Check-in; nessun limiter dedicato, quindi soglia/finestra/chiave correnti assenti. API60/minuto non si applica al gruppo web.

Due limiti statici rilevanti: rotazione a prefisso8 invariato conserva il breve; ripristino80 produce un completo accettabile ma non soddisfa il vincolo64 del resolver breve. Inoltre completed usa editUrl/flag non bloccato mentre show/store proteggono convertito; nessuna nuova scrittura anonima riuscita dichiarata. Non eseguita nuova prova E2E di questi limiti né applicata correzione in questa task di contratto.

Recupero password già configurato60minuti/monouso e blocco account inattivo; throttle broker60secondi tra emissioni per email, non tentativi HTTP. Componenti batch30minuti e consumo; anteprima fiscale15minuti/impronta; sessione/CSRF/Ricordami/Sanctum e token Questura inventariati distintamente. Non trasferita la politica del recupero al link ospite; nuove policy API o di autenticazione gestionale non introdotte implicitamente.

Da approvare esclusivamente: **D1** scadenza comune al primo tra30giorni assoluti dall'emissione e fine giorno successivo alla partenza, senza rinnovo automatico; **D2** riuso fino conversione, ricevuta sola lettura fino stessa scadenza, revoca/rinnovo tenant autorizzati, vecchi link sempre revocati e ripristino senza riattivazione; **D3** invito indipendente casuale32caratteri, completo64 comune alle nuove emissioni/ripristini; **D4** ingresso300/60secondi perIP, lettura60/60secondi perSID+RID+IP, scrittura20/600secondi perSID+RID+IP, contatori comuni agli alias,429/Retry-After. Il limite ingresso è deliberatamente condiviso tra strutture sullo stesso IP, compromesso da approvare, non quota tenant isolata; **D5** transizione massima7giorni per vecchi accessi già validi, limitata anche dal soggiorno, nessuna grazia a link revocati/cancellati/scaduti. Tutti sono parametri proposti, non requisiti vigenti o valori produttivi misurati.

Il documento separa motivazioni, costi per l'ospite, cambiamenti futuri e matrice di prove necessarie (confini temporali/DST, tutti alias, revoca concorrente, Cestino, isolamento A/B, soglia+1/fine finestra/CSRF/replay/chiavi e browser). Nessuna nuova suite eseguita, perché nessun codice/test modificato; le prove locali precedenti restano evidenze della baseline precedente e non certificano D1–D5. Eventuali cambi di aspettative sulla scadenza dovranno essere esplicitamente motivati dal contratto approvato, conservando originali e risultati storici.

Gate produttivi separati e invariati: destinatarioMariaDB/FPM/SPanel,backup/restore/rollback,release/config/cache/storage/queue/cron/SSL,IP affidabile/cache condivisa/dati operativi e accettazione enti/mail. L'approvazione delle decisioni funzionali non autorizza operazioni sulDB/schema/configoperativi,commit/push/deploy/trasmissioni. Modifiche solo documentali; nessun Laravel bootstrap, DB o trasporto applicativo utilizzato.

Preservazione della sola fase di contratto:8380file iniziali,8378byte-identici,2appendici e1nuovo documento specialistico;zeroeliminazioni,storia dei documenti verificata con hash. Codice/test/config/schema/evidenze preesistenti invariati,diffcheckPASS,stagingvuoto,nessuna suite o operazione applicativa eseguita.


## Approvazione circoscritta D1/D2 del contratto token — 9 ottobre 2026

**CONTRATTO PARZIALMENTE APPROVATO, NON IMPLEMENTATO. Accettazione locale già verificata preservata; BASELINE IN AUDIT — NON VALIDATA.** L'utente ha sostituito le precedenti proposte D1/D2 con tre sole regole: emissione/invio in qualsiasi momento e compilazione/salvataggi/correzioni mentre il link è valido e non convertito; conversione in Arrivo/Schedina conserva dati e blocca modifiche anonime, il link mostra solo il messaggio letterale richiesto «Check-in recibido», senza dati personali, con successive correzioni dal gestionale; scadenza alla fine del giorno successivo all'**arrivo previsto**, Europe/Rome, senza limite30giorni e senza prolungamento per visite/salvataggi.

Superati il cap30giorni, il termine legato alla partenza e la ricevuta nominativa dopo conversione. **D3–D5 e ogni ulteriore regola di revoca/rinnovo/Cestino non sono approvate.** Il blocco dopo conversione non autorizza riapertura automatica del link. Il registro vigente del contratto distingue queste decisioni dal comportamento corrente e conserva integralmente la proposta precedente come storico.

Restano soltanto: **R1** fonte autorevole dell'arrivo previsto e effetti della riprogrammazione gestionale, inclusa eventuale riattivazione di un link scaduto (oggi il salvataggio ospite aggiorna anche richiesta.arrivo, ma non può implicitamente prolungare la validità); **R2** eventi/ruoli di revoca e rigenerazione, trattamento del ripristino; **D3** formato/invito breve e compatibilità64/80; **D4** soglie/finestre/chiavi e impatto degli IP condivisi; **D5** applicazione ai link già distribuiti/legacy e date mancanti/incoerenti. I vecchi32caratteri,300/60/20 e7giorni restano proposte non approvate, da riesaminare dove interferiscono con le nuove regole.

Aggiornata anche la matrice delle prove future: emissione oltre30giorni prima dell'arrivo, confini del giorno locale/DST, salvataggi senza estensione, conversione Arrivo/Schedina e conferma senza dati personali su tutti gli alias, correzioni gestionali/tenant. Nessuna prova eseguita né politica attivata; le evidenze precedenti restano della baseline già verificata, non prova dei nuovi requisiti. Modifiche solo ai tre documenti, nessun codice/test/config/schema/dato modificato. Gate destinatario e produttivi invariati, nessun DBoperativo/commit/push/deploy/trasmissione reale.

[Registro vigente e decisioni residue](contratto-token-proposta-2026-10-09.md).


## Implementazione e accettazione locale del contratto token — 9 ottobre 2026

**VERDETTO: ACCETTAZIONE LOCALE POSITIVA del contratto Web Check-in approvato e del perimetro della baseline già verificato. BASELINE IN AUDIT — NON VALIDATA. Nessuna validazione produttiva.** Superata la precedente lacuna P1.4 di contratto non approvato/non implementato; nessuna approvazione operativa implicita. [Registro vigente del contratto](contratto-token-proposta-2026-10-09.md), [evidenza strutturata con hash](evidenze-contratto-token-2026-10-09.json), [log e JUnit conservati](evidenze/contratto-token-2026-10-09/).

### Regole approvate e implementazione effettiva

L’utente ha approvato D1–D2, R1–R2 e D3–D5. Emissione/preparazione invio in qualsiasi momento, senza cap30giorni; ospite può salvare/correggere finché valido e non convertito. Termine esclusivo arrivo previsto+2giorni00:00Europe/Rome, non48ore fisse e non partenza. Dopo conversione Arrivo/Schedina: dati conservati nel gestionale, solo «Check-in recibido» su tutte le rappresentazioni valide, nessun dato personale o modifica anonima.

Arrivo previsto registrato dall’operatore nella richiesta; compilazione ospite non riscrive le date previste. Riprogrammazione aggiorna la scadenza soltanto con vecchio link ancora valido; un link già scaduto/revocato/non valido non viene riabilitato. Revoca, rigenerazione, cancellazione e cambio gestionale di destinatario (anche contatto svuotato) invalidano tutte le rappresentazioni vecchie. Solo membership operativa autorizzata della struttura; token pubblico/ID/parametri ospite non concedono azioni gestionali. Cestino restituisce dati e un token64revocato: necessario il pulsante esplicito di nuova emissione64/32. Nessuna riemissione aprendo elenco o form.

Nuovo invito breve casuale indipendente32, completo casuale64. Vecchi completi64/80/altri formati e inviti codice+prefisso8 compatibili entroD1; nessuna transizione7giorni. Rigenerazione disabilita il prefisso legacy anche a parità dei primi8caratteri del nuovo completo. Date assenti/non di calendario o partenza< arrivo rifiutate, senza date inventate. Emissione su date coerenti ma passate non sposta il termine: il nuovo token è comunque scaduto.

`WebCheckinLink` centralizza le regole; migration aggiuntiva con4metadati nullable, testata solo localmente. Controlli sotto transazione/lock di struttura e richiesta; salvataggio già iniziato rivalida token, scadenza e conversione dopo il lock. Contatori atomici sullo storefilelocale già disponibile, condiviso fra processi del nodo:300/60sperIPprima lookup,60letture/60s e20POST/600sperSID+RID+IP, alias ecompleted condivisi, HEAD incluso tra letture, CSRF errato conteggiato prima del rifiuto.429/Retry-After, nessuna scrittura applicativa. Cache/storage del singolo caso di test separati; i processi concorrenti dello stesso caso condividono realmente i contatori. Nessuna configurazione operativa alterata.

### Matrice finale requisito / evidenza / risultato / residuo

| Requisito e criterio di accettazione | Evidenza effettiva | Risultato | Pendenti concreti / limite |
|---|---|---|---|
| Nessun cap emissione30giorni; visite/salvataggi non prolungano | Regessione emissione molti mesi prima; due salvataggi con nuove date ospite; scadenza/date previste invariate | PASS | Dati esclusivamente sintetici |
| Termine giorno locale e confine esclusivo | Un secondo prima ammesso, al termine GET/POST404; DST47/49ore; Capodanno e29febbraio | PASS | Clock controllato PHPUnit, browser non usato per alterare il clock server |
| Arrivo autorevole / riprogrammazione | HTTP e browser: link attivo ricalcolato; link scaduto resta404 dopo cambio data; nuova emissione autorizzata crea credenziale nuova | PASS | Provenienza dei valori legacy reali da verificare prima dell’attivazione |
| Revoca e tutti gli alias precedenti | Revoca/rigenerazione gestionali, CSRF errato, vecchi completi/brevi404; nuova credenziale valida; browser con entrambe le conferme UI | PASS | Nessuna trasmissione email/WhatsApp realmente eseguita |
| Cambio gestionale destinatario | Referente/email mutati dalla richiesta e da conversione operativa; contatto nullo revoca; conservazione dati operatore | PASS | Ruoli/membership esistenti; non ampliati |
| Cestino e cancellazione parent | PHP archive/restore con token64revocato; cancellazione/ripristino parent non riattiva; browser cancella→ripristina→404→emissione32→200 | PASS | Vista Cestino finale originale byte-identica; i timeout erano diagnostici |
| Conversione e privacy | HTTP tutti gli alias/show/completed/POST con contenuto esatto; snapshot personali invariati. Browser vera conversione dal form inArrivo, gestionale conserva nominativo/circuito e link mostra solo conferma | PASS | Correzioni operative conservano i controlli esistenti; nessun invio all’ente |
| Formato nuovo / legacy / nessuna grazia7giorni |64/32indipendenti; legacy64/80 e formati vari; prefisso vecchio rifiutato anche con prefisso completo uguale dopo rigenerazione | PASS | Nessuna ricognizione dei token/dati reali |
| Date mancanti/incoerenti | Assenti/zero/non calendario testate sul resolver; date invertite persistite, accesso/emissione422; correzione operatore non abilita da sola, successiva emissione sì | PASS | Lo schema locale già impedisce dateNULL persistite; nessun rilassamento dello schema |
| Tenancy e autorizzazioni | Parent/tenant incoerenti404 senza esposizione/scritture; sessione estranea non cambia token owner; emissione/revoca estranee negate; ruolo sconosciuto403 | PASS | Matrice concreta esercitata, non ogni permutazione/endpoint |
| Quote, finestre e Retry-After | Soglia+1, aliascondivisi,59/60s e599/600s con Retry-After1 prima del reset; HEAD/CSRF/IPdistinto. Quota300 dimostra zeroquery lookup al301°tentativo | PASS | Storefile/nodo locale; affidabilitàIP/proxy e nodi di produzione non attestati |
| Wi-Fi comune tra ospiti/strutture | PHP più richieste stessa struttura e altra struttura indipendenti sottoquotaRID;300generale condiviso. Chromium60letture,20POST,429 e blocco generale anche altra struttura | PASS | Nessuna valutazione del traffico/IP reali |
| Concorrenza |4prove con fork: salvataggio iniziato prima di revoca/rigenerazione/conversione rivalida dopo lock; duePOST al confine19→un200/un429 | PASS | Due processi sul medesimo nodo e DB temporaneo, non cluster |
| Migration locale reversibile | Up→down→up, campi rimossi/ripristinati, token/base table preservati | PASS | Nessuna migration eseguita sul DB operativo; backup/dati/rollback produttivi da verificare |
| Regressioni e baseline | Globale definitiva **750test/8.252asserzioni PASS**, seed20261009;22nuove regressioni contratto/600asserzioni incluse. A12–A16 e Questura204/3656,ISTAT65/410,Tassa52/1586 verdi | PASS | Vale per versione e perimetro documentati, non validazione produttiva |
| Browser reale | **Chromium9/9PASS** sui sorgenti definitivi, vista Cestino originale servita in copia effimera; cicli GUI e richieste reali al kernel isolato | PASS | Chromium locale; nessuna matrice esaustiva dispositivi/browser o servizi esterni |
| DiagnosticoA14 | Copia temporale identificata,8casi/aspettative funzionali conservati, inclusa nei supplementari14/101PASS; originale invariato | PASS della copia | Originale conserva l’asserzione flash dopo seconda richiesta: errore del diagnostico, non difetto reset |
| Proxy di audit | Handler e regressioni precedenti byte-identici;2/2nuove esecuzioni in memoriaPASS, corpo troncato→502, nessun200parziale | PASS | Solo `tests/Isolation/run.py`; nessun proxy applicativo introdotto |

Le verifiche sono su dati coerenti e sintetici, con i prerequisiti applicativi preesistenti. Validità del token non abroga i controlli fiscali: anni non certificati/configurazioni Tassa mancanti o discordanti restano rifiutati422 nelle regressioni storiche. Non viene attestata disponibilità di regole fiscali per ogni futuro soggiorno né allentato un guardrail del Maestro.

### Esecuzioni definitive e preservazione dei rossi

Globale definitiva750/8252PASS,750casi unici JUnit, zero errori/failure/skip. Nuove regressioni22/600 incluse nella globale. Supplementari separati14/101PASS: concorrenza4/50, round-tripmigration1/8, conversione operativa e copiaA14; questiAudit.php sono selezionati esplicitamente e non occultati dalla discoveryglobale. Browser definitivo9/9PASS con vista Cestino originale. Mirati intermedi79/829PASS, globale748/8234 e749/8239PASS precedono ultimi ampliamenti e non vengono presentati come definitive. Globale750/8252 con tentativo di spostamento modali anch’essaPASS, distinta dalla definitiva con vista originale.

Conservati: rifiuto del bind prima di creareDB; primo runtime senza middleware preesistente non versionato,15errori di setup; prima globale37failure per vecchie aspettative/fixture scadute e una perdita della relazione dopo nuovo lock, corretta; nuovo diagnostico gestionale con `save_mode=web` assumeva un modo non operativo disponibile: la schedina converte nei circuiti previsti, la riprogrammazione è dal formrichiesta, test corretto per verificare conversione/destinatario; confronto browser tra data locale e serializzazioneUTC corretto senza cambiare scadenza; prova fork iniziale bloccata da asserzionePID eseguita anche nel figlio, terminata e ripetuta con setup corretto. Due browser iniziali attendevano POST senza seguire entrambe le conferme UI; rossi conservati. I primi log del launcher sono soggetti al limite10000caratteri dell’harness; JUnit del primoRED non disponibile dopo cleanup, limite dichiarato senza ricostruire output mancanti.

**Cestino, distinzione applicazione/diagnostico:** DOM con primoform vuoto e secondoform nel modal; selettore per nome “Ripristina” trovava il modal nascosto perché il pulsante della riga usa icona/tooltip. La prima attribuzione a difetto funzionale della vista è stata smentita dalla prova con vista originale e pulsante visibile, associato nativamente al POST corretto. Tentativo di spostamento dei modali annullato; vista finale byte-identica all’inizio. Browserconfronto9/9PASS senza modificaCestinoUI. Restano storico tutti i timeout, il DOM acquisito e il tentativo temporaneo. Nessuna correzione mantenuta per un difetto non confermato.

Sei copie byte-identiche dei test precontratto e una della vistaCestino in `tests/Diagnostics/token-precontratto-2026-10-09/`. Nei sei test attivi: clock esplicito entro validità delle fixture; nuove aspettative approvate (scadenza, conferma senzaPII,64nelCestino); controlli tenant/rollback/componenti/camere preservati. Nessuna cancellazione, riduzione di casi o alterazione dei diagnosticiA04/A14/camere originali. La copia A14 già identificata resta separata, originale non riordinato. Il nuovo supporto per cache separata è infrastruttura di test, non una modifica della quota applicativa.

Pint15file/lintPHP+JS/diffcheckPASS; difformità storiche dei controller non riformattate indiscriminatamente. Guardrail5,policyDB10,proxy2,ISTAT22inmemoriaPASS; SOAP14inmemoria nei runtimebrowser. Tutti i19runtime effimeri attestati con23rifiuti prima delle migration e rimossi, incluso quello interrotto. Evidenze/JUnit acquisiti fuori runtime e conservati nel progetto con hash. Fotografia8.381file: zeroeliminazioni; prefissi storici rapporto/Maestro e intero documento contrattuale precedente preservati con hash. Configurazioni/schemaoperativo, coreGEO/hash, sorgenti Questura/ISTAT e dati/evidenze precedenti invariati.

### Pendenti e gate separati

**Nessuna decisione funzionale né prova locale necessaria di questo contratto rimasta aperta nel perimetro eseguito.** A12–A16 e accettazione locale precedente preservati/riconfermati dalla globale; la precedente politica token indefinita è superata. Il timeoutcamere storico e la causa upstream originariaA05 restano anomalie non riprodotte/non identificate, non nuovi guasti confermati né prove cancellate.

**Gate destinatario/produttivi non eseguiti:** autorizzazione e prova DBMariaDB/Linux isolato/CLI/FPM/SPanel; backup/restore/rollbackmigration con dati effettivi; provenienza/correzione dell’arrivo previsto e qualità dei recordlegacy reali, revoche storiche non registrate; attendibilitàIP dietroproxy, cache/lockpersistenti e topologia nodi reali; SHA/release/config/cache/storage/ownership/queue/cron/SSL; accettazioniQuestura/ISTAT/mail e regole fiscali future. Sei proveRocky/Bubblewrap storiche non sostituiscono questi gate. Nessuna validazione normativa o produttiva, nessuna ricognizione/operazione sui dati reali.

Flag: codiceapplicativoSI;testSI;docsSI;migrationpreparata/provataLOCALMENTESI;config/schemaOPERATIVONO;datitestSI soloeffimeri;DBoperativoNO;commitNO;pushNO;deployNO;trasmissionirealiNO. Nessun fetch o staging. **Accettazione locale positiva e delimitata; BASELINE IN AUDIT — NON VALIDATA mantenuta.**


## Preparazione concreta del primo rilascio controllato — 9 ottobre 2026

**Accettazione locale positiva conservata; lancio effettivo BLOCCATO da B1–B4, non dallo stato BASELINE IN AUDIT — NON VALIDATA.** Il piano unico è in [Preparazione del primo rilascio](PRIMO-RILASCIO-CONTROLLATO-2026-10-09.md). Quattro condizioni finite: candidato con SHA approvato che includa il lavoro non versionato; accettazione attuale CLI/FPM/MariaDB del destinatario; elenco migration riconciliato e restore dimostrato; chiusura documentata del gate storico di esposizione/dipendenze. V1–V4 specificano le verifiche server che chiudono quei blocchi; V5–V7 delimitano link legacy, provider/mail e ampliamento fiscale. M1–M3 restano differibili, senza nuova audit generale.

Preparati procedura di rilascio, piano delle sette migration recenti da riconciliare con lo storico effettivo, configurazione senza segreti, backup/recupero e controlli prima/dopo apertura; inventario SHA-256 di1.818sorgenti in `inventario-candidato-rilascio-2026-10-09.json`, non artefatto distribuibile. Sintassi dei due wrapperbash e5/5hash fissi supervisor PASS. Nessuna ripetizione delle750prove/8.252asserzioni o Chromium9/9già superate: nessun codice modificato.

Storico preservato: sei proveRocky/Bubblewrap PASS prevalgono sulla vecchia annotazione di assenza; non certificano runtime PHP né annullano i segfault storici da verificare. Nessuna nuova vulnerabilità dichiarata dalla sola lista avvisi dipendenze. Nessun DBoperativo, configurazione/schemaoperativo, commit/push/fetch/deploy o trasmissione; nessuna validazione produttiva. Accesso server necessario esclusivamente per le verifiche V1–V4 indicate; dati/provider per V5–V7.


## Avvio della chiusura B1–B4 — 9 ottobre 2026

[Verbale ed evidenza effettiva](CHIUSURA-BLOCCHI-PRIMO-RILASCIO-2026-10-09.md). Accettazione locale positiva mantenuta, nessun nuovo difetto applicativo affermato. Inventario1818/1818, sorgenti contratto13/13 ed evidenze31/31 corrispondono; candidato completo preparato per review prima del commit. Solo nuova prova isolata di backup/restore/upgrade:1test/26asserzioniPASS,23rifiuti primaDDL, runtime ripulito. Sorgenti applicativi invariati, globale750/8252 e Chromium9/9non ripetuti.

B1: preparazione locale con manifest/staging per review, resta futuro SHA/pubblicazione non autorizzati ora. B2: accesso al dominio documentato fuori sandbox rifiutatoTCP22, nessun profiloSSH/terminale collegato; CLI/FPM/MariaDB e segfault attuali non verificati. B3: quota sintetica locale verde, elenco pending/schema destinatario e recuperabilità reale non attestati; non si deduce pending dal solo nome migration. B4: Composer aggiornato46avvisi/12pacchetti, [triage nominativa](triage-dipendenze-primo-rilascio-2026-10-09.json); npm non completato dopo timeout40s/30s, mitigazioni vhost/trasporti non attestate. I46avvisi non sono46exploit riprodotti. Lancio ancora BLOCCATO dai medesimi quattro gate; BASELINE IN AUDIT — NON VALIDATA non è un difetto.

Precisazione dell’autorizzazione corrente: server solo lettura; migration/backup/restore solo sintetici isolati. Non restaurare snapshot operativi per completare B3 sotto questa autorizzazione; occorre una precedente evidenza affidabile di restore o separata autorizzazione futura. Nessuna credenziale richiesta in chat. Le verifiche server esatte e i limiti sono nel verbale. Nessun commit/push/fetch/deploy, configurazione server, scritturaDBoperativo o trasmissione; nessuna validazione produttiva.


## Pendenti locali Ross1000 e dipendenze — 9 ottobre 2026

[Verbale aggiornato](PENDENTI-LOCALI-ROSS1000-DIPENDENZE-2026-10-09.md). Il500specifico Ross1000 non risultava chiuso da una precedente attribuzione documentata: nuovo percorso configurazione→TabellaA HTTP1/9PASS e Chromium3/3PASS con tre profili sintetici, nessuna credenziale o trasmissione. Classificazione: anomalia non riprodotta nel campione; nessuna modifica applicativa senza causa. Per attribuirla restano necessari stack sanitizzato e SHA/schema/ruolo del destinatario; non presunta causa migration.

Composer46avvisi classificati individualmente in [matrice completa](composer-classificazione-46-2026-10-09.json): versione/catena, diretto/transitivo, ambito, esposizione, criterio e rimedio. Tutti produzione:16avvisi su2diretti,30su10transitivi. Mitigazioni locali2/11PASS per trasporti spenti/debug/locale e assenza NoPrivateNetworkHttpClient; non equivalgono a patch o configurazione server attestata.62regressioniISTAT verdi nel mirato; rossi dei soli nuovi setup/route conservati.

**npm completato:** audit definitivo62dipendenze affette(2critiche,18alte,41moderate,1bassa), senza erroreJSON; timeoutprecedenti superati tramite cache privata. Bulk indipendente105advisory/versioni su30pacchetti, conteggio distinto. Nessun aggiornamento dei lock o auditfixforce. Rimedi/mitigazioni degli avvisi applicabili ancora B4; rimosso soltanto il pendente di completamento auditnpm.

SSH: host schedinedinotifica.tanggo.software, porta22, Connection refused fuori sandbox, exit255; nessun comando remoto eseguito o modifica server. B1review/futura pubblicazione, B2runtime/segfault/500non attribuito, B3schema/pending/recupero destinatario e B4esposizione/rimedi restano aperti. Accettazione locale positiva conservata, BASELINE IN AUDIT — NON VALIDATA; nessuna validazione produttiva. Codice e lock invariati: globale750/8252 e Chromiumtoken9/9non ripetuti. Candidato e manifest aggiornati con le nuove prove/evidenze, precedente inventario194preservato. Nessun commit/push/deploy, DBoperativo, configurazioneserver o trasmissione.


## Destinatario corretto e rimedi mirati — 9 ottobre 2026

[Verbale finale](RIMEDI-DESTINATARIO-2026-10-09.md). Destinatario root@217.174.149.23:6543 raggiunto ma autenticazione negata: Permission denied (publickey,gssapi-keyex,gssapi-with-mic,password), exit255; agente senza identità. Nessun comando remoto. Il precedente dominio:22 non verifica il destinatario. CLI/FPM/MariaDB/segfault, eccezione sanitizzata Ross1000 e metadati migration restano bloccati dall’autenticazione; nessun500 dichiarato risolto. Recuperabilità non attestata.

Rimedi Composer mirati11pacchetti segnalati+Promises prerequisito, nessunmajor/rootrequire:39dei46avvisi produzione originari rimossi;7Laravel residui, oltre4sviluppo nel rapporto completo. SolverLaravel11 rifiutato dalla policy sicurezza, non disabilitata. PHP globale750/8252PASS, mirati5/25PASS; prove prima/dopo PATH_INFO e UTF8storage confermano il rimedio. Nessuna modifica applicativa.

Rimedi npm SweetAlert11.22.4/Moment2.31.0/Prism1.30.0 e Sass1.79.0 dichiarato, risolto il build non riproducibile dal manifest. Esplorazioni e rossi diagnostici preservati; nessun aggiornamento massivo. Audit finale58dipendenze affette:2critiche/16alte/40moderate; non considerare risolti gli asset residui. BrowserRoss1000 3/3PASS e assetfinali1/1PASS; build isolatiPASS. Globale non ripetuta dopo soli cambi build/Moment, verificati separatamente.

Inventario storico1818preservato:1815hash invariati, sole divergenze autorizzate composer.lock/package.json/package-lock.json. Nuovo inventario1818aggiornato; manifest e staging comprendono rimedi, regressioni e originali pubblici dei lock. Accettazione funzionale locale positiva conservata; gate sicurezza/esposizione aperto. B1review/futuroSHA;B2identitàSSH+runtime+attribuzione500;B3metadata+recuperabilità;B4Laravel e assetpubblici/CKEditor oppure esclusione tecnica dimostrata delle feature, più esposizione serverdev/vhost. BASELINE IN AUDIT — NON VALIDATA. Nessun commit/push/deploy, modifica server, scritturaDBoperativo o trasmissione reale.


## Evidenza Herd e sicurezza effettiva — 9 ottobre 2026

[Verbale consolidato](HERD-ROSS1000-SICUREZZA-2026-10-09.md), [matrice Laravel](laravel-avvisi-residui-2026-10-09.json), [matrice npm](npm-esposizione-effettiva-2026-10-09.json). Accettazione funzionale locale positiva conservata; BASELINE IN AUDIT — NON VALIDATA, nessuna validazione produttiva.

Ross1000: il500 segnalato è Herd locale. Otto eccezioni sanitizzate confermano SQLSTATE42S22, snapshot assente in istat_exports. Metadati operativi letti senza record personali, transazione READ ONLY: MySQL8.0.44, migration2026_10_07_180000_protect_istat_cycle non registrata. StoricoSHA/ruolo/fuso non attestati. Riproduzione sintetica PHP1/13 e Chromium1/1:500 prima,200 dopo la migration esistente sul soloDB effimero. Nessuna patchISTAT o migrationoperativa; guasto Herd non dichiarato risolto. Per eliminarlo serviranno riallineamento autorizzato e backup; i tre profili sintetici verdi precedenti non risolvevano il guasto osservato.

Correzione applicativa minima: resolverEmailControlValidator rifiuta CR/LF che il validatore originale accetta; Laravel2/16 e globale750/8252PASS. Rimedi asset Swiper12.1.2/ECharts6.1.0/Quill2.0.3/Axios1.20.0 e form-data4.0.6 verificati. CKEditor40.2.0 escluso dalla sola copia del nuovo artefatto, sorgenti/pacchetti/build esistenti preservati; nessun consumer applicativo trovato. Chromium artefatto+compatibilità4/4, HTTPAxios1/1 e multipartNodePASS; originali diagnostici e rossi conservati. Auditnpm finale completo53dipendenze:0critiche/15alte/37moderate/1bassa; non tutte dichiarate mitigate. QuillHTMLexport residuo privo di consumer attuale, gate prima della sua introduzione; strumenti dev non devono essere pubblici.

Laravel11.31.0 conserva sette righe advisory (due duplicate dello stesso problemaemail): condizioni e limiti provati nella matrice. Debug effettivo del destinatario ancora da attestare. [Impatto Laravel12](IMPATTO-LARAVEL12-2026-10-09.md): simulazione50update/5install, non applicata; decisione supporto/major prima del lancio. [Procedura SSH Mac](SSH-MAC-CHIAVE-AUTORIZZATA-2026-10-09.md): coppia pubblica esistente verificata, nessuna chiave privata letta/esposta/copiata, agente vuoto; nessuna nuova connessione o modifica server.

B1: candidato aggiornato per review, futuroSHA/commit non eseguiti. B2: autenticazioneSSH e letture CLI/FPM/MariaDB/segfault ancora necessarie; causa500Herd separata, identificata. B3: metadati destinatario/riconciliazione e recuperabilità reale ancora mancanti, nessuna migrationoperativa. B4: attestazione debug/vhost/devserver e artefatto pulito senza vecchioCKEditor, decisione framework/supporto; eventuali funzioni editor/exportHTML richiedono valutazione prima di attivarsi. Il nuovo artefatto non va sovrapposto alle vecchie librerie: verificare assetCKEditor404, asset aggiornati200, accesso autenticato e flussiHTTP sul destinatario in modalità senza trasmissioni, entro futura autorizzazione al deploy.

Inventario corrente1819:1813/1818hash originari invariati,5divergenze autorizzate(provider,composer.lock,package.json,package-lock.json,package-copy-config.json) e una nuova classe. Originali1818 e manifest194/212/250 conservati. Nessun commit/push/deploy, scritturaDBoperativo, configurazione server o trasmissione reale.

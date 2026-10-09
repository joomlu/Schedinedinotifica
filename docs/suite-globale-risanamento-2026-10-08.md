> **STATO ATTUALE: PASS LOCALE — BASELINE ANCORA IN AUDIT.** Il blocco iniziale riportato sotto è storico ed è superato dall'autorizzazione P1 e dalla [chiusura conclusiva](#chiusura-conclusiva--p1-autorizzato-e-suite-globale-verificata). Nessuna readiness produttiva.

# Risanamento suite globale PHPUnit — 8 ottobre 2026

## Stato

**BLOCCATO SU NUOVO P1 APPLICATIVO — BASELINE IN AUDIT.** Nessuna approvazione della suite globale o della produzione. La task autorizza il risanamento dei test, ma richiede un nuovo via libera per un P0/P1 applicativo dimostrato. Correzione funzionale non applicata.

Baseline: main, HEAD/origin/main locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, 0/0, staging vuoto. Nessun fetch, commit, push, deploy, produzione, DB operativo o trasmissione esterna. Inventario iniziale: 19 tracked modificati e 19 untracked, salvati privatamente in `/private/tmp/ids-suite-baseline`, insieme a hash e diff iniziale.

## P1 nuovo: apertura nuovo arrivo

Prova isolata reale del kernel HTTP: utente sintetico super_admin, struttura sintetica attiva, sessione con la struttura corrente, GET `/arrivi/nuovo` → **HTTP 500**. Anche il solo `SmokePagesQaTest` riproduce: 4 test, 1 fallimento, 0 errori; gli altri tre percorsi passano.

Catena dimostrata:

1. `routes/web.php`: route `newarrival`, `ArrivalsController::new()`.
2. `app/Http/Controllers/ArrivalsController.php`: assegna `$esenzioni = []` e restituisce `arrivals.new`.
3. `resources/views/arrivals/new.blade.php`: passa l'array alla partial condivisa.
4. `resources/views/schedina/partials/form.blade.php:237`: `$catalogo = $esenzioni ?? collect()` preserva l'array; `$catalogo->reject(...)` genera `Call to a member function reject() on array`.

L'errore è nel candidato Tassa preesistente alla task corrente: la partial in HEAD aveva opzioni statiche e non chiamava `reject()`. La partial, il controller Arrivals e tutti i sorgenti applicativi sono rimasti byte-identici allo stato iniziale di questa task. Non è un'aspettativa da indebolire: lo Smoke mantiene `assertOk()`.

**Proposta minima da autorizzare:** normalizzare soltanto il catalogo della vista con `collect($esenzioni ?? [])`, conservando il filtro 777, la selezione del valore corrente e l'avviso legacy. Nessuna tariffa, formula, autorizzazione, configurazione o persistenza fiscale da cambiare. Test richiesti: apertura arrivi con array vuoto, rendering con array/Collection e conservazione del filtro 777 e dei valori legacy; regressioni Schedine/Tassa. Poi riprendere globali e ordine casuale. Richiesta di autorizzazione presentata; nessun consenso dedotto dal tempo trascorso.

Evidenza indipendente: `/private/tmp/ids-suite-smoke-1.log` e `.xml`; prima emersione nel gruppo fixture: `/private/tmp/ids-suite-fixture-primo-1.log` e `.xml`.

## Cause tecniche corrette

- **Discovery:** il contenuto originale di `ComponentiImportReviewStatusTest.php` è conservato byte-identico in `tests/Support/componenti-import-review-status.php`. Il file Unit contiene ora un autentico TestCase che esegue lo script in un processo separato, verifica exit code e risultato delle sue asserzioni. Nessun codice top-level contamina la discovery. Raccoglibili **576 casi unici**, nessun errore di raccolta.
- **DDL C1:** teardown del test di accettazione ricrea soltanto lo schema del DB effimero attestato e invalida lo stato condiviso di RefreshDatabase, anche dopo failure. Le asserzioni sostanziali C1 sono intatte. Nessuna migration applicativa modificata.
- **Storage:** ogni TestCase Laravel configura disk local/public in una directory casuale propria sotto lo storage del checkout attestato. Pulizia dopo il teardown, limitata a directory possedute dal supporto del processo, con controllo percorso e runtime. Nessuna pulizia di documenti preesistenti o dello storage operativo. Due prove verificano separazione, conservazione del file precedente e rifiuto di directory non possedute.
- **Access/Tenancy:** fixture sintetiche esplicite con factory utenti, struttura, proprietario e ruoli. Nessun seeder demo ammesso. Il test tenant non trasforma più fixture/schema incompleti in skip.
- **CustomerImported:** strutture complete tramite helper ufficiale e utenti tramite factory, con i campi obbligatori reali. Conservate tutte le asserzioni filtro/cancellazione/tenant.
- **Smoke:** ciascun caso crea i propri dati; il percorso componenti dispone di Schedina e cliente sintetici invece di uno skip da dataset assente. Questo rende visibile il P1 sugli arrivi.
- **Importazione:** stati semantici allineati al contratto vigente già verificato dallo script indipendente: COMPLETO, DA_COMPLETARE e NON_IMPORTABILE. ERRORE strutturale, rifiuto colonne extra/mancanti, dati e motivazioni restano verificati. Corrette le righe dichiarate positive con 18 valori rispetto a 15 intestazioni e l'ordine dei campi esteri. Nessuna modifica al service di importazione.
- **Bootstrap Unit:** Request di test con validazione reale tramite factory, provider di validazione e resolver sintetici espliciti; letture Eloquent in-memory consentite soltanto per la struttura fittizia, nessun PDO operativo.
- **Deprecazione:** il container Unit non configurava locale/fallback; il Translator passava null a `Str::contains()`. Configurate `it` e fallback `it` nel solo test. Nessuna modifica vendor o soppressione warning.
- **Example:** route `/` e `PublicSiteController::home()` servono realmente `web/index.html`, non un redirect alla dashboard. Il test controlla HTTP 200, MIME HTML, BinaryFileResponse e identità del documento; nessuna modifica della route.
- **Launcher:** opzioni esplicite per suite globale, discovery, ripetizione 1–3 volte con reinizializzazione del solo DB attestato e seed casuale riproducibile. Controlli precedenti, inclusioni esplicite, esclusione seeder e fail-closed conservati. Nessuna cancellazione globale dello storage fra iterazioni.

## Prove effettivamente eseguite

PHP 8.3.33, MySQL 8.0.36, PHPUnit 10.5.38; tutte le prove Laravel attraverso `tests/Isolation/run.py`, checkout/DB/storage effimeri, 23 rifiuti override, identità processi/DB/HTTP, riconnessione, build asset e GEO verificati. Inclusioni applicative non versionate esplicite: TassaExport e migration Tassa preesistenti.

| Prova | Risultato |
|---|---|
| Discovery completa | 576 casi unici raccolti, exit 0 |
| Unit importazione | 78 test / 318 asserzioni PASS, zero deprecazioni |
| Questura separata, aspettative originali | 204 test / 3.656 asserzioni PASS |
| Access/Tenancy/Customer/Example/Smoke/autorizzazioni | 34 test / 124 asserzioni: 1 failure Arrivi, nessun errore, nessuno skip/deprecazione |
| Smoke da solo | 4 test / 13 asserzioni: stesso unico P1 Arrivi, nessun errore |
| Supporto storage | 2 test / 7 asserzioni PASS |
| Guardrail fuori runtime | 5 PASS |
| Pint sui file PHPUnit/supporto modificati | 12 PHP PASS |
| Sintassi | 13 PHP PASS e Python launcher PASS |
| Diff check | PASS |

Non sommare esecuzioni sovrapposte. La prova Questura e il primo gruppo Unit precedono la sola formattazione Pint dei rispettivi test; non costituiscono la verifica finale dei byte formattati dell'intera batteria. Lo script diagnostico originale resta volutamente non riformattato per preservare esattamente le prove precedenti; sintassi verificata.

Comandi riproducibili del launcher, con PATH dei runtime locali sicuri e le due inclusioni applicative esplicite:

```sh
python3 -B tests/Isolation/run.py --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php --phpunit-global --phpunit-discovery
# DA ESEGUIRE DOPO IL GATE P1:
python3 -B tests/Isolation/run.py --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php --phpunit-global --phpunit-repeat 2
python3 -B tests/Isolation/run.py --include app/Models/TassaExport.php database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php --phpunit-global --phpunit-random-seed 4208
```

Il wrapper esterno `/private/tmp/ids-suite-run.py` conserva log completi/JUnit fuori dal repository prima della pulizia del launcher; non sostituisce attestazioni o regole DB. **Due globali sullo stesso ambiente, ordine casuale e regressioni separate finali ISTAT/Tassa/autorizzazioni non eseguiti:** gate applicativo P1 aperto. Nessuna dichiarazione di zero failure globali o ripetibilità completa.

## File della task

1. `tests/TestCase.php`
2. `tests/Support/IsolatedTestStorage.php` — nuovo
3. `tests/Support/componenti-import-review-status.php` — nuovo percorso, contenuto originale invariato
4. `tests/Unit/ComponentiImportReviewStatusTest.php`
5. `tests/Unit/ComponentiImportFormTest.php`
6. `tests/Unit/ComponentiImportServiceTest.php`
7. `tests/Feature/QuesturaC1MigrationAcceptanceTest.php`
8. `tests/Feature/AccessTest.php`
9. `tests/Feature/TenancyTest.php`
10. `tests/Feature/CustomerImportedFiltersDeleteTest.php`
11. `tests/Feature/SmokePagesQaTest.php`
12. `tests/Feature/ExampleTest.php`
13. `tests/Feature/SuiteResourceIsolationTest.php` — nuovo
14. `tests/Isolation/run.py` — estensione delle modifiche preesistenti, guardrail preservati
15. `docs/suite-globale-risanamento-2026-10-08.md` — nuovo
16. `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md` — sola appendice

Dei 38 file iniziali, 36 restano byte-identici; il launcher è esteso nello scope e il Maestro conserva integralmente il testo precedente con appendice. I 19 untracked preesistenti sono tutti presenti e invariati. Nessun sorgente applicativo, vista, route, contratto fiscale, migration, configurazione, dipendenza, .env o segreto modificato dalla task. Worktree atteso: 29 tracked modificati e 23 untracked, staging vuoto; differenza rispetto ai 38 iniziali attribuita ai 16 file della task, senza staging indiscriminato.

## Verdetto e passo successivo

**RISANAMENTO PARZIALE — STOP P1 APPLICATIVO ARRIVI.** Correzioni infrastrutturali applicate e prove mirate positive; suite globale non ancora convalidata. Attendere autorizzazione alla modifica minima della vista e ai relativi test, poi completare le verifiche richieste. Nessuna readiness produttiva o certificazione normativa.


## Chiusura conclusiva — P1 autorizzato e suite globale verificata

**VERDETTO: PASS. SUITE GLOBALE VERIFICATA — BASELINE ANCORA IN AUDIT.** L'utente ha autorizzato esplicitamente la normalizzazione minima e la ripresa delle verifiche; il precedente gate P1 è superato soltanto per questo difetto. Nessun altro fix applicativo effettuato.

### Causa e correzione minima

ArrivalsController restituisce un array vuoto per le esenzioni; la partial condivisa richiedeva una Collection per reject()/contains(). Un'unica sostituzione nella vista: `$catalogo = collect($esenzioni ?? []);` al posto di `$catalogo = $esenzioni ?? collect();`. Catalogo sempre Illuminate\Support\Collection, senza cambiare controller, dati, tariffe, calcolo, esenzioni, autorizzazioni o persistenza. Il confronto con la copia privata della vista conferma che questa è l'unica differenza applicativa della fase autorizzata.

Cinque nuove prove Unit coprono array vuoto/popolato, Collection, null e selezione/legacy. Il 777 resta escluso dalle opzioni ordinarie; un valore storico 777 o sconosciuto mantiene l'avviso legacy, senza inventare una nuova esenzione. Nuovo test Feature: utente struttura_user autorizzato, GET /arrivi/nuovo HTTP200, vista/form corretti e zero nuove Schedine, export fiscali o esenzioni.

### Tre globali effettive

| Esecuzione | Ambiente/ordine | Test | Asserzioni | Failure | Error | Skip | Deprecazioni |
|---|---|---:|---:|---:|---:|---:|---:|
| Globale 1 | ordine phpunit.xml, runtime g_zdqqvj | 582 | 6.850 | 0 | 0 | 0 | 0 |
| Globale 2 | stesso runtime/endpoint g_zdqqvj, DB effimero reinizializzato | 582 | 6.850 | 0 | 0 | 0 | 0 |
| Globale casuale | runtime 85b8rvia, seed registrato 4208 | 582 | 6.850 | 0 | 0 | 0 | 0 |

L'inventario dei 582 casi e il numero di asserzioni **per ciascun caso** coincidono nelle tre esecuzioni. Non sono state escluse classi, test o provider per rendere verde la suite. La prova DDL C1 è nella posizione naturale nella globale ordinaria e viene riordinata insieme alle altre nella casuale. Seconda globale: nuovo processo PHPUnit sullo stesso server/DB/checkout temporanei, migrate:fresh attestato fra le prove; nessuna cancellazione indiscriminata dello storage fra iterazioni. Le directory dei singoli casi sono gestite dal supporto di isolamento.

### Regressioni e altri controlli conclusivi

| Prova | Risultato effettivo |
|---|---|
| Arrivi + rendering Unit | 17 test / 111 asserzioni PASS |
| Gruppo fixture/autorizzazioni richiesto | 34 / 124 PASS |
| Smoke indipendente | 4 / 13 PASS |
| Questura separata, DDL in posizione naturale | 204 / 3.656 PASS |
| ISTAT separata | 65 / 410 PASS |
| Tassa Bellaria separata | 50 / 1.572 PASS |
| Access/Tenancy/selezione/autorizzazioni separate | 30 / 125 PASS |
| Isolamento storage nella globale | 2 / 7 PASS in ogni globale |
| Discovery finale | 582 casi unici; nessun errore |
| Policy DB sintetica | 10 PASS |
| Policy isolamento Rocky, solo prove locali | 21 PASS, nessun accesso server o Bubblewrap reale |
| Guardrail fail-closed fuori runtime | 5 PASS |
| SOAP/privacy Questura standalone | 14 PASS, soli doubles in memoria |
| Sicurezza ISTAT standalone | 22 PASS, soli doubles in memoria |
| Pint | 13 file PHPUnit/supporto PASS |
| Sintassi | 14 PHP e 4 Python PASS; Blade compilata/eseguita dalle regressioni di rendering |
| git diff --check | PASS |

Tutte le esecuzioni PHPUnit sono sul runtime del launcher: PHP8.3.33, MySQL8.0.36, PHPUnit10.5.38; identità processo/DB/HTTP, riconnessione,23rifiuti/build/GEO PASS. Nessun DB o storage operativo riutilizzato. Le inclusioni non versionate restano soltanto TassaExport e migration Tassa esplicitamente richieste per il candidato. Nessuno skip da giustificare e nessuna deprecazione soppressa. Non sommare prove sovrapposte.

Le due prove storage dimostrano che il secondo caso non vede il PDF del primo, che la sua pulizia conserva il documento precedente e che un percorso non posseduto è rifiutato. Tutti i runtime conclusivi sono stati fermati/rimossi dal launcher. Nessuna perdita di file preesistenti del progetto.

### Evidenze riproducibili

Comandi: wrapper esterno `/private/tmp/ids-suite-run.py` nelle modalità arrivi,fixture,smoke,globali,random,questura,istat,tassa,autorizzazioni,discovery. Il wrapper conserva soltanto output/JUnit all'esterno e invoca il launcher originale, senza sostituirne le verifiche. Le globali corrispondono ai comandi permanenti sopra: --phpunit-global --phpunit-repeat 2 e --phpunit-global --phpunit-random-seed 4208, con le due inclusioni esplicite e PATH dei runtime locali sicuri.

Rapporti JUnit/log completi: `/private/tmp/ids-suite-final-globali-1.xml`, `-2.xml`, `/private/tmp/ids-suite-final-random-1.xml` e rispettivi `.log`. Singoli gruppi: `/private/tmp/ids-suite-final-<modalità>-1.xml/.log`. Log launcher con identità e cleanup: `/private/tmp/ids-suite-final-<modalità>-launcher.log`. Discovery: `/private/tmp/ids-suite-final-discovery-1.log`.

### File della fase autorizzata e preservazione

Rispetto allo STOP precedente, esattamente cinque file:

1. `resources/views/schedina/partials/form.blade.php` — unica riga applicativa normalizzata;
2. `tests/Unit/ComponentiImportFormTest.php` — cinque nuove regressioni, altre prove preservate;
3. `tests/Feature/ArriviEsenzioniTest.php` — nuovo test HTTP/persistenza;
4. questo rapporto — fase storica preservata, stato conclusivo aggiunto;
5. Maestro — appendice conclusiva, testo precedente preservato.

Inventario cumulativo del risanamento: i16file della fase iniziale, più vista e ArriviEsenzioniTest,18file complessivi. Nessun file preesistente cancellato. Dei38file iniziali35byte-identici; run.py esteso nello scope, Maestro appendice e vista con la sola normalizzazione autorizzata. Tutti19untracked iniziali invariati. Il codice Questura/ISTAT e il calcolo/contratti fiscali Bellaria, schema/migration, dipendenze, configurazioni e .env sono preservati.

### Git e limiti

main; HEAD e origin/main **locale** `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`;0/0;stagingvuoto;29trackedmodificati+24untracked,53filetotali spiegati dall'inventario preesistente e dal risanamento. Nessunfetch,nuovocommit,push,deploy,SPanel,produzione,migrationoperativa o trasmissionereale.

Nessun nuovo P0/P1 residuo dimostrato nel perimetro conclusivo. Sono stati risolti i problemi di discovery/fixture/isolation/contratto dei test e il P1 Arrivi espressamente autorizzato. L'indipendenza dall'ordine è verificata sull'ordine ordinario ripetuto e sul seed4208, non su tutte le permutazioni possibili. Non sono state ripetute prove browser o acceptance Rocky/Bubblewrap/enti; le policy locali non sostituiscono tali gate. Restano i backlog e i gate produttivi del Maestro. **Nessuna certificazione normativa o dichiarazione di produzione pronta.**

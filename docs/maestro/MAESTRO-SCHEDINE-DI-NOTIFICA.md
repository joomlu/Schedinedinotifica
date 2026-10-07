# MAESTRO — SCHEDINE DI NOTIFICA

Versione documentale: **1.0**
Data baseline: **2026-10-05**
Stato: **BASELINE IN AUDIT**
Fonte primaria: **repository ed evidenza verificabile**
Baseline applicativa: `f3b28c9be725252bb06e957340512620cfb2a607`

## Quadro ufficiale permanente — consolidazione 2026-10-05

> Questo documento costituisce la fonte documentale maestra del progetto Schedine di Notifica. Lo stato BASELINE IN AUDIT indica che l’applicazione esiste ed è oggetto di validazione progressiva; non implica readiness o validazione completa per produzione.

**Unica fonte maestra:** `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`. Specifiche, manuali e report specialistici sono fonti subordinate richiamate, non maestri alternativi. AGENTS.md continua a regolare lingua e istruzioni operative; qui non viene modificato. Tutte le attività del repository si svolgono in italiano.

Il progetto esistente è la base: **AUDITARE → VERIFICARE → CORREGGERE → PROVARE → DOCUMENTARE → VALIDARE → DISTRIBUIRE**, entro lo scope autorizzato di ciascuna task. Nessuna riscrittura generale, refactoring ampio o completamento implicito. Priorità: correttezza, sicurezza, isolamento, stabilità, manutenibilità, ottimizzazione. Prima di sostituire logica esistente accertarne le ragioni; preferire interventi piccoli e tracciabili.

### Stati ufficiali e gerarchia di evidenza

| Stato | Significato |
|---|---|
| VERIFICATO | Evidenza sufficiente e datata, entro uno scope esplicito |
| PARZIALE | Evidenza positiva, ma validazione incompleta |
| PENDENTE | Implementazione/intenzione con verifica sostanziale da completare |
| NO VERIFICATO | Evidenza insufficiente |
| BLOCCATO | Verifica impedita da dipendenza o condizione esplicitamente documentata |
| LEGACY | Elemento storico da non assumere come comportamento corrente |
| DEPRECATO | Elemento proposto per rimozione controllata, non ancora eliminato |

CONTRADDIZIONE è un attributo del registro delle evidenze, non uno stato funzionale aggiuntivo; STORICO indica data/provenienza. Nessuna inferenza diventa fatto. Codice presente, test presente, comportamento osservato, comportamento provato, validazione locale, accettazione dell’ente e funzionamento in produzione sono livelli distinti. Non usare “funziona”, “completo”, “validato” o “production ready” senza scope ed evidenza espliciti.

Ordine indicativo in caso di conflitto: comportamento riprodotto → test riprodotto → codice → configurazione → specifica ufficiale vigente → Maestro → audit precedente → documento storico → commento → ipotesi. Per la **conformità esterna**, un test interno non può derogare alla specifica ufficiale: se divergono, documentare il conflitto e non dichiarare conformità.

### Baseline Git e architettura

Preflight di consolidazione: directory `/Users/jorgeluccitelli/Herd/Schedinedinotifica`, branch `main`, HEAD iniziale `f3b28c9be725252bb06e957340512620cfb2a607`; tracked/staging puliti, untracked preesistenti elencati nella sezione 2. Remote configurato `origin`, URL `https://github.com/joomlu/Schedinedinotifica`. Il preflight iniziale confrontava solo il riferimento locale; il successivo fetch e confronto remoto sono descritti sotto.

**Verifica remota: VERIFICATO nello scope Git.** Il fetch inizialmente bloccato è stato successivamente autorizzato esplicitamente, inclusa la scrittura di `.git/FETCH_HEAD`, ed eseguito con esito 0. `origin/main` aggiornato: `4b42ac4d0293d28925f3ad18c36cc0b15b248fd2`; HEAD applicativo `f3b28c9be725252bb06e957340512620cfb2a607`, ahead 4 / behind 0, nessun commit remoto esclusivo. Commit locali esclusivi: `48075a0a0f684ae51c5ff65c06070eb1a6f13707` (topbar null-safe), `36caec7ca9eee528792a5d5823b68204ac9d5e54` (runtime isolato), `6d8756c1e79026409a07b39a75701899784833f6` (loghi Comuni), `f3b28c9be725252bb06e957340512620cfb2a607` (ISTAT). I quattro commit vengono preservati integralmente. Il riscontro remoto è quello del fetch, non prova stato di produzione e non garantisce assenza di aggiornamenti remoti successivi. Nessun pull, merge, rebase o reset.

Architettura e inventario ricontrollati sui file: Laravel monolitico, Eloquent, Blade, JavaScript, Vite; lock Laravel 11.31.0, PHPUnit 10.5.38, OpenSpout 4.32.0, Vite 5.2.8, Playwright 1.58.2. Inventario: 60 controller, 55 file modello/trait, 19 servizi, 15 middleware, 13 comandi, 5 provider, 2 request e 142 migrazioni. La presenza delle migrazioni non prova applicazione sul DB corrente. Nessun database interrogato durante la consolidazione.

Il Maestro è già esistente e viene consolidato, non ricreato da zero. **Versionamento documentale autorizzato nella fase di chiusura:** Maestro, istruzioni permanenti, rapporti storici e test diagnostici preesistenti vengono inclusi nel commit di baseline. La pubblicazione GitHub resta distinta e non è autorizzata in questa fase. SHA del commit di baseline consultabile nella storia Git, senza inserirlo autoreferenzialmente nel documento.

### Stato funzionale ufficiale

| Area | Stato | Limite principale |
|---|---|---|
| Multistruttura e ruoli | PARZIALE | Matrice completa backend/tenant non completata |
| Clienti / Customer Import | PARZIALE | Feature filtri/eliminazione bloccate da fixture |
| Componenti Import | PARZIALE | P1.1/P1.2 riprodotti e corretti con prove isolate sul servizio (appendice G); restano gap diagnostici, UI e persistenza end-to-end |
| Schedine e arrivi | PARZIALE | Percorsi completi e fixture store da verificare |
| GEO core | PARZIALE | Qualità/completezza dati reali non attestata |
| Comuni e logo | PARZIALE | CRUD su fixture documentato; acceptance browser isolamento incompleta |
| Questura | PARZIALE | Q1/Q2/Q3 verificati M/P/R; audit locale chiuso S, PRONTO PER PROVA REALE CONTROLLATA. Fix UX verificati T, pronto per revisione pre-commit. Accettazione/rete reali e produzione non verificate |
| ISTAT / Ross1000 | PARZIALE | Conformità locale distinta da readiness e accettazione ente |
| Cestino | PENDENTE | Tenant, entità globali, restore, purge e snapshot da verificare |
| Web Check-in | PENDENTE | Parent tenant, token, scadenza, anonimato e link corti |
| Audit operativo | PARZIALE | Utenti senza struttura fissa e operazioni escluse |
| Impersonazione | PENDENTE | Uscita/ruolo e collisioni route |
| Presenze / tassa / età | PARZIALE | Prove circoscritte, non certificazione generale |
| Calendario / notifiche | PARZIALE | Matrice operativa/tenant incompleta |
| CRM / supporto / amministrazione | PARZIALE | Percorsi/autorizzazioni completi non attestati |
| Licenze / proforme | PARZIALE | Nessuna emissione fiscale/SDI attestata |
| Deploy / Rocky | PARZIALE | Acceptance Linux e ambiente destinatario pendenti |
| Uso reale in produzione | NO VERIFICATO | Nessun accertamento corrente |

Solo nuove evidenze datate possono cambiare questi stati. VERIFICATO si applica a un requisito e scope delimitato; non promuovere un intero modulo perché un sottoinsieme di test passa.

### Backlog P1 ufficiale e criteri di uscita

| ID | Area | Evidenza/azione e criterio |
|---|---|---|
| P1.1 | Date Componenti | VERIFICATO sul servizio, 2026-10-05: riproduzione prima del fix e regressione isolata; date impossibili NON_IMPORTABILE, nessun payload. Appendice G; persistenza end-to-end non verificata |
| P1.2 | Struttura Componenti | VERIFICATO sul servizio, 2026-10-05: ERRORE escluso mediante allowlist degli stati esistenti, conteggio corretto e batch misto provato. Appendice G; persistenza end-to-end non verificata |
| P1.3 | Cestino | Verificare read/restore/purge, entità globali, tenant e minimizzazione snapshot; rischio statico, non exploit certificato |
| P1.4 | Web Check-in | Token, scadenza, anonimato, parent schedina/struttura_id, link corti, riuso e invalidazione; matrice sintetica cross-tenant |
| P1.5 | Utenti/password/impersonazione | Creazione/modifica/reset e gestione versus membership; avvio/uscita impersonazione e collisioni route |
| P1.6 | Isolamento tenant | Matrice lettura/scrittura/eliminazione/restore/import/export/ricerca/relazioni/API/route dirette/admin; chiudere gap feature e acceptance browser |
| P1.7 | ISTAT/produzione | Config reale, codice struttura, dataset, XML/XSD, specifiche, confronto con file accettati e accettazione ente nel perimetro autorizzato |

Le priorità R1–R14 della sezione 14 restano dettagli del backlog: P1.1/P1.2 estendono R7; P1.3 R1/R2; P1.4 R5; P1.5 R3/R14; P1.6 R7 e verifiche tenant; P1.7 R10/R11. R4 audit, R6 schema, R8/R9 ingressi/logout e debiti R12/R13 restano espliciti. Nessun P0 dimostrato dalle prove documentate, non garanzia di assenza. Avvisi dipendenze del 29/09 sono storici e richiedono nuovo audit: non aggiornare locks o dichiarare conteggi correnti senza verifica.

### Risultati di test: esclusivamente storici

| Prova precedente | Risultato storico | Portata |
|---|---|---|
| PHPUnit ampliato | 232 test / 868 asserzioni; 15 failure + 4 error | Suite non superata; non nuova esecuzione |
| Riproduzione mirata | 81 test / 285 asserzioni; stessi 19 casi | Quattro classi interessate |
| Triage 23 casi | A 4 / B 9 / C 9 / D 1 / E 0 | A bug reale, B obsoleto, C incorretto, D infrastruttura, E indeterminato |
| Playwright | 4 PASS / 1 FAIL | Acceptance isolamento incompleta |
| JavaScript | 3 FAIL | Contesto DOM/VM incompleto |
| Guardrail | 5 PASS | Evidenza storica, non attestazione runtime avviato ora |
| Deployment | 84 PASS / 3 SKIP | Non acceptance Rocky |

Quattro casi A rappresentano un difetto comune date; aggiungendo S1 strutturale si hanno **due difetti reali distinti**. Non riclassificare per rendere verde la suite. Ogni risultato nuovo deve indicare data, HEAD, selezione, comando, ambiente, fixture, esito e limiti; prove sovrapposte non si sommano.

### Protocolli permanenti per le prossime attività

**Prima di intervenire:** leggere Maestro e AGENTS.md; identificare modulo/stato; ispezionare codice e test; verificare Git, staging e untracked; definire rischi/scope e riprodurre quando pertinente. Dichiarare **OBIETTIVO**, **FILE COINVOLTI**, **RISCHIO** (BASSO/MEDIO/ALTO/CRITICO), **TEST PREVISTI** prima delle modifiche. Una task documentale non autorizza fix, test mutanti o deploy.

**Testing:** leggere `docs/TESTING_ISOLATION.md`, studiare i guardrail e attestare l’ambiente prima di qualunque prova con DB/filesystem/jobs/rete. Usare esclusivamente il runtime isolato del progetto per Laravel/PHPUnit/Playwright; nessun riuso DB/server reale, nessun bypass TEST_ISOLATION_REQUIRED, nessun indebolimento. Fixture sintetiche, MySQL e filesystem effimeri; servizi esterni fake/double. Il blocco Http Laravel non garantisce da solo il blocco SOAP: non invocare SoapClient reale. Test legacy con utenti/dataset impliciti non sono autosufficienti. Conservare fallimenti e motivi di mancata esecuzione.

**Tenancy:** verificare struttura_id, identità, ruolo/membership, scope, middleware, SQL/Eloquent e route binding. Dimostrare backend per leggere, modificare, eliminare, ripristinare, esportare, trasmettere ed enumerare indirettamente dati di altro tenant. Controllare import/export/Cestino/audit/web check-in/schedine/clienti/componenti/enti/calendario/notifiche/admin e API; usare almeno due tenant sintetici e ruoli distinti. Nascondere una voce UI non costituisce autorizzazione.

**Questura:** distinguere generazione, validazione, TXT/formato, credenziali/certificato, autenticazione, trasmissione, risposta, ricevuta, errori/retry, audit e automatismi. Prima di conformità o automatizzazione consultare specifiche ufficiali vigenti. Nessun invio reale durante audit senza autorizzazione esplicita. Simulazione, pulsante disponibile o test Safety non provano accettazione. Preservare il contratto Questura durante modifiche ISTAT/GEO.

**ISTAT:** distinguere XML, XSD ufficiali e binding locale, controlli semantici, codifiche, codice struttura e requisito regionale da accettazione ente. Snapshot di `reference/istat/ross1000-er/` hanno provenienza/hash e data, non garantiscono future revisioni. File accettati possono essere confronto solo nel perimetro autorizzato e con dati protetti/sintetici; non sostituiscono le specifiche, non usare file nominativi reali come fixture ordinaria.

**Sicurezza:** audit dedicato prima della dichiarazione produzione su autenticazione/autorizzazione/ruoli/tenant/CSRF/mass assignment/IDOR/binding/upload/import/export/token/check-in/password/impersonazione/log/segreti/env/errori/admin/dati personali/retention/Cestino/backup. Non salvare segreti nel repo né stamparli nei report. Rischio statico distinto da exploit e verifica negativa distinta da assenza generale.

**Git:** flusso concettuale LOCAL → GITHUB → SPANEL. Prima delle modifiche `git status`, `git branch --show-current`, `git rev-parse HEAD`; quando necessario e consentito `git fetch` e confronto esplicito. Conservare lavoro esistente, selezionare file individualmente, niente `git add .`/`-A`, reset hard, force push, pulizia untracked o cambio branch distruttivo. Branch nuovi `codex/` salvo richiesta diversa. Prima di commit: diff check/stat/name-only sia unstaged sia cached, e intero diff cached. Commit/push/eliminazioni richiedono lo scope autorizzato; questa fase non li autorizza.

**Deploy:** riferimento specialistico `docs/DEPLOY_SPANEL.md`, audit dipendenze/deployment e isolamento Rocky. Infra storica Rocky/SPanel/PHP 8.3/Composer/subdominio non prova stato corrente: **NO VERIFICATO**. Prima di deploy autorizzato identificare release/SHA approvato, PHP/Composer/estensioni, env/APP_ENV/DEBUG/URL/KEY senza esporre segreti, DB, ownership/permessi/storage/cache/log, cron/scheduler/queue/worker, asset Vite, SSL/dominio, spazio, backup e restore provato, health ed error log. Preservare APP_KEY e dati; migrazioni solo esplicitamente approvate. Verificare maintenance/backup/recovery e acceptance prima di riaprire. Nessun deploy automatico, rollback dati cieco o istruzione force-push dai vecchi runbook.

**Dopo ogni intervento:** riportare MODIFICHE, MOTIVO, TEST ESEGUITI con comandi esatti, RISULTATO (PASS/FAIL/PARZIALE), REGRESSIONI, TENANT CHECK, SICUREZZA, DOCUMENTAZIONE e GIT. Aggiornare il Maestro insieme al progetto se cambia conoscenza rilevante. Evitare log infinito: stato e decisioni nel quadro ufficiale, evidenze tecniche nei report specialistici e cronologia sintetica datata.

**Criterio produzione:** nessun P0 aperto; P1 critici risolti o accettati formalmente con scope/motivazione/responsabile/data; suite critica verde; tenancy e sicurezza verificate; Questura/ISTAT validati nello scope richiesto; infrastruttura, backup/restore, release esatta e acceptance produzione attestati. Oggi questi criteri non sono dimostrati soddisfatti.

### Chiusura obbligatoria delle attività

Ogni consegna deve indicare: **STATO** (VERIFICATO/PARZIALE/PENDENTE/BLOCCATO), **COSA È STATO FATTO**, **EVIDENZE**, **PROBLEMI TROVATI**, **MODIFICHE**, **RISCHI RESIDUI**, **TENANT / SICUREZZA**, **GIT** (branch/HEAD/stato), **MAESTRO** (AGGIORNATO/NON NECESSARIO), **PROSSIMO PASSO CONSIGLIATO**. Concludere con SI/NO per CODICE MODIFICATO, TEST MODIFICATI, DOCUMENTAZIONE MODIFICATA, CONFIGURAZIONE MODIFICATA, SCHEMA/DATI MODIFICATI, TEST ESEGUITI, COMMIT, PUSH, DEPLOY, TRASMISSIONI ESTERNE. Non occultare fallimenti né promuovere verifiche non eseguite.

### Decisioni e cronologia sintetica

| Data | Decisione/evidenza | Esito |
|---|---|---|
| 2026-03-18 | Verbale di consegna | LEGACY: riserve esterne registrate; chiusura software non prova baseline odierna |
| 2026-09-29/30 | Audit dipendenze/deploy/Rocky | Rapporti storici preservati; acceptance produzione da accertare |
| 2026-10-04 | Report Clienti e separazione ruoli | Evidenze circoscritte, non validazione globale |
| 2026-10-05 | Baseline applicativa f3b28c9 e audit | BASELINE IN AUDIT |
| 2026-10-05 | Triage appendice F | Due difetti reali distinti; suite non verde |
| 2026-10-05 | Consolidazione richiesta dal Prompt Maestro | Unica fonte maestra, stati/protocolli ufficiali; nessuna cancellazione/commit/test; fetch BLOCCATO |

### Chiusura della baseline documentale — classificazione untracked

Stato ufficiale mantenuto **BASELINE IN AUDIT**. Nessuna correzione dei bug P1, nessuna esecuzione di test, modifica applicativa/schema/dati o trasmissione. La task autorizza la baseline versionata e la pulizia di due documenti, non un push o deploy. Rapporto storico non significa prova attuale; versionare un test fallito non significa correggerlo o certificarlo.

| File preesistente | Classificazione | Motivazione |
|---|---|---|
| `.github/copilot-instructions.md` | VERSIONARE | Regole Copilot coerenti con lingua, scope e Maestro; aggiunto collegamento ufficiale. |
| `AGENTS.md` | VERSIONARE | Istruzioni italiane permanenti; rimossa limitazione alla vecchia fase linguistica e aggiunto riferimento al Maestro. |
| `docs/audit-clienti-2026-10-04.md` | VERSIONARE | Rapporto storico utile per attribuzione, prove e limiti; conservato invariato, subordinato al Maestro per stato globale. |
| `docs/chiusura-pulizia-clienti-2026-10-04.md` | VERSIONARE | Rapporto storico utile per attribuzione, prove e limiti; conservato invariato, subordinato al Maestro per stato globale. |
| `docs/chiusura-workflow-clienti-2026-10-04.md` | VERSIONARE | Rapporto storico utile per attribuzione, prove e limiti; conservato invariato, subordinato al Maestro per stato globale. |
| `docs/eliminazione-individuale-export-clienti-2026-10-04.md` | VERSIONARE | Rapporto storico utile per attribuzione, prove e limiti; conservato invariato, subordinato al Maestro per stato globale. |
| `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md` | VERSIONARE | Fonte documentale ufficiale, inventario, protocolli, rischi e triage. |
| `docs/separazione-superadmin-admin-2026-10-04.md` | VERSIONARE | Rapporto storico utile per attribuzione, prove e limiti; conservato invariato, subordinato al Maestro per stato globale. |
| `docs/verifica-finale-clienti-2026-10-04.md` | VERSIONARE | Rapporto storico utile per attribuzione, prove e limiti; conservato invariato, subordinato al Maestro per stato globale. |
| `tests/Unit/ComponentiImportFormTest.php` | VERSIONARE | Conservare byte-identico come evidenza diagnostica dei casi F15/E01 e JS01–03 (secondo il file); harness/fixture non corretti e fallimenti espliciti in appendice F. Non sono da soli riproduzione dei due bug del servizio. |
| `tests/Unit/componenti-import-submit.test.mjs` | VERSIONARE | Conservare byte-identico come evidenza diagnostica dei casi F15/E01 e JS01–03 (secondo il file); harness/fixture non corretti e fallimenti espliciti in appendice F. Non sono da soli riproduzione dei due bug del servizio. |

Istruzioni AGENTS/Copilot allineate: italiano obbligatorio, Maestro fonte unica, scope corrente, isolamento e aggiornamento documentale. Nessuna eliminazione o correzione dei due test; nessun file temporaneo incluso. I documenti del 04/10 conservano risultati e metodi storici, anche prove non isolate: non autorizzano ripetizione di quelle procedure oggi. Prima del commit previsti diff check/stat/name-only su working tree e staging e revisione del diff cached completo. README punta al Maestro; nessun altro documento eliminato.

## 0. Autorità e portata delle evidenze conservate

Le sezioni 1–20 e le appendici A–F conservano l’inventario e le diagnosi delle audit precedenti del 05/10/2026. Gli esiti di test, letture DB, verifiche CLI e interrogazioni remote lì riportati sono **evidenze storiche datate**, non esecuzioni della presente consolidazione né certificazioni del runtime corrente. Le diciture “attuale”, “questa task” e “verificato” nei resoconti si riferiscono al rispettivo accertamento originario e al suo scope. Prevalgono il quadro ufficiale iniziale e, per il triage test, l’appendice F.

Il presente intervento modifica soltanto questo documento; non ha interrogato database, avviato test/build o contattato enti/produzione. Non autorizza le operazioni descritte nei runbook. Gli inventari applicativi e le dipendenze bloccate sono riscontri di file, non prova di migrazioni applicate, funzionalità complete o accettazione esterna.

### Registro delle evidenze

| ID | Evidenza acquisita il 05/10/2026 | Portata |
|---|---|---|
| E1 | `git status`, `diff`, `diff --cached`, `diff --check`, `branch -avv`, `log`, `rev-list` | Stato locale e riferimenti locali; nessun fetch |
| E2 | `git ls-remote origin refs/heads/main` | SHA remoto corrente osservato in sola lettura; non stato del sito |
| E3 | `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, versioni CLI | Requisiti, dipendenze bloccate e runtime locale |
| E4 | Inventario completo dei file sotto `app`, route/config/migrazioni e ricerca query fuori scope | Presenza, responsabilità e rischi statici; nessuna certificazione esaustiva di ogni endpoint |
| E5 | PDO diretto su DB locale, `START TRANSACTION READ ONLY`, sole query `information_schema` e elenco `migrations`, `ROLLBACK` | Schema effettivamente presente; nessun dato nominativo/credenziale estratto |
| E6 | Runtime `tests/Isolation/run.py`: classi Unit e sette suite Feature | 232 test, 868 asserzioni, 15 fallimenti e 4 errori |
| E7 | Runtime isolato: tre suite Feature e spec Geo logo | PHPUnit 32/193 PASS; Playwright 4 PASS e 1 fallimento |
| E8 | Safety, guardrail, script autonomo Componenti e suite Deployment | Esiti dettagliati nella sezione testing |
| E9 | Report e documentazione precedenti letti/confrontati | STORICO; prove precedenti mai sommate alle nuove |
| E10 | Hash SHA256 iniziali/finali di app, route, config, migrazioni, test, risorse e documentazione | Controllo dell'unico file permanente modificato |

## 1. Identità e stack

🟢 **VERIFICATO, E3/E4.** Nome prodotto: **Schedine di Notifica**. Gestisce anagrafiche clienti, soggiorni/schedine e accompagnatori, arrivi e check-in, presenze, tassa di soggiorno, comunicazioni operative, export Questura e ISTAT, gestione gerarchica amministrativa, supporto e licenze/proforme. La presenza del modulo non certifica l'invio o l'accettazione reale.

Architettura: applicazione Laravel monolitica con controller MVC, Eloquent, servizi applicativi e Blade; JavaScript/Vite per UI e asset; MySQL per dati; filesystem Laravel per import/export/upload. Bootstrap con Kernel HTTP/Console espliciti, struttura storica Laravel mantenuta anche con framework 11. Non emerge un'applicazione SPA separata.

| Componente | Richiesto/dichiarato | Rilevato |
|---|---|---|
| PHP | `^8.2` da Composer | CLI 8.3.33, Herd `php83`; non prova PHP FPM/produzione |
| Laravel | `^11.0` | lock `v11.31.0` |
| Composer | nessun vincolo esplicito di versione nel manifest | CLI 2.10.2 |
| Node / npm | manifest senza `engines` | Node 22.21.1 / npm 11.12.0 |
| Vite | `^5.0.12` | lock 5.2.8 |
| laravel-vite-plugin | `^1.0.1` | lock 1.0.2 |
| PHPUnit | `^10.5` | lock/runtime 10.5.38 |
| Playwright | `^1.58.2` | lock/runtime 1.58.2 |
| OpenSpout | `^4.27` | lock 4.32.0 |
| Tema/frontend | manifest `velzon` 4.3.0 | Blade, Bootstrap 5.3.3, jQuery/Select2 e componenti locali |

`npm run build` pulisce gli asset build, esegue Vite e `scripts/verify-architecture.mjs`; copia anche asset/librerie tramite il plugin nel `vite.config.js`. Eseguito soltanto nelle copie isolate. Non è stato ricompilato `public/build` del checkout operativo.

Struttura: `app/` logica; `routes/` ingressi; `resources/` Blade/JS/assets; `database/` migrazioni/factory/seeder; `config/` configurazione; `public/` document root atteso dal framework; `storage/` dati/runtime; `tests/` prove e isolamento; `reference/` codifiche/specifiche; `scripts/` build/deployment; `docs/` documentazione; `vendor/` e `node_modules/` dipendenze locali ignorate.

## 2. Git e controllo di versione

🟢 **VERIFICATO, E1/E2.** Repository: `https://github.com/joomlu/Schedinedinotifica`, remote `origin` uguale per fetch/push. Branch corrente `main`; `origin/HEAD → origin/main` indica il ramo remoto principale. HEAD `f3b28c9be725252bb06e957340512620cfb2a607`.

`origin/main` locale: `4b42ac4d0293d28925f3ad18c36cc0b15b248fd2`. `ls-remote` conferma lo stesso SHA su GitHub al momento della verifica. Divergenza `origin/main...HEAD`: **0 commit solo remoti / 4 solo locali**. L'assenza di upstream visualizzato per `main` non equivale a sincronizzazione: il confronto è stato esplicito.

| Commit locale non presente su main remoto | Descrizione |
|---|---|
| `48075a0` | rendering topbar autenticata resistente ai null |
| `36caec7` | runtime locale isolato e guardrail |
| `6d8756c` | gestione loghi Comuni per Admin/Super Admin |
| `f3b28c9` | conformità ISTAT Ross1000 Emilia-Romagna |

Baseline iniziale: tracked modificati **nessuno**, staged **nessuno**, diff applicativi vuoti. Il Maestro era un file vuoto di 0 byte nella directory untracked `docs/maestro/`. File untracked preesistenti esclusi da qualsiasi staging:

- `.github/`, `AGENTS.md`, `docs/maestro/`;
- `docs/audit-clienti-2026-10-04.md`, `docs/chiusura-pulizia-clienti-2026-10-04.md`, `docs/chiusura-workflow-clienti-2026-10-04.md`;
- `docs/eliminazione-individuale-export-clienti-2026-10-04.md`, `docs/separazione-superadmin-admin-2026-10-04.md`, `docs/verifica-finale-clienti-2026-10-04.md`;
- `tests/Unit/ComponentiImportFormTest.php`, `tests/Unit/componenti-import-submit.test.mjs`.

Ulteriori branch locali: `codex/safe-spanel-deploy` a `d160c6a`, `production-deploy` a `78ece78`; nomi e commit non provano un deployment attuale. Nessuna operazione Git mutante in questa task; nessun commit del Maestro.

Politica: branch `codex/` per nuovi lavori, scope esplicito, staging selettivo, niente `git add .`/`-A`, niente scarto dei file dell'utente. Lo stato finale rimane HEAD invariato e Maestro untracked.

## 3. Architettura applicativa e dipendenze

🟢 **VERIFICATO, E4.** Censiti 60 file Controller, 55 Model (incluso trait), 19 Service, 15 Middleware, 13 Console Command, 5 Provider, 2 Request. L'inventario nominale completo con entrypoint e relazioni è in appendice A.

| Area | Responsabilità / dipendenze principali | Stato |
|---|---|---|
| HTTP | route web/API → middleware → controller → service/model → Blade/risposta | VERIFICATO nel codice |
| Tenant | StrutturaCorrente, StrutturaAccess, scope Eloquent, middleware | PARZIALE: regole e suite struttura provate, audit globale non completo |
| Anagrafiche | CustomerController/Export/Import → Customers e batch/rows | PARZIALE: unit import e codice; feature legacy fallite |
| Componenti | ComponentiImportController/Service → contratto/normalizzatore/catalogo/lock → Componenti | RISCHIO test/contratto non allineati |
| Comunicazioni obbligatorie | controller Questura/ISTAT → generatori → validatori → servizi esterni → export/transmission | PARZIALE: nessuna accettazione esterna attestata |
| Report | PresenzeReportService, TassaDiSoggiornoService, EtaOperativa | PARZIALE: unit e regressioni pertinenti |
| Operatività | calendario/notifiche/gestione operativa → soggiorni, comande, audit, accessi | PARZIALE: statico, non intero percorso E2E |
| Amministrazione | Superadmin/Admin/Proprietario → proprietari, strutture, licenze, proforme, CRM | PARZIALE: ruoli statici; non tutta matrice commerciale ripetuta |

Route: `web.php` include sito pubblico, `Auth::routes()`, gruppo autenticato, gruppi ruolo, GEO, moduli operativi, fallback e Web Check-in pubblico. `api.php`: `/api/user` protetto Sanctum e sei endpoint GEO nel gruppo API con rate limiter 60/minuto. `console.php` caricato dal Kernel; `channels.php` relativo al broadcasting. Non eseguito `artisan route:list` sul checkout operativo per evitare bootstrap e side effect locali.

Provider: AppServiceProvider imposta stringhe DB e composer topbar con StrutturaAccess; RouteServiceProvider registra web/API e rate limiter; AuthServiceProvider ha mappa Policy vuota; EventServiceProvider collega Registered a SendEmailVerificationNotification; BroadcastServiceProvider registra broadcasting. Non trovati `app/Policies`, `app/Jobs`, `app/Events`, `app/Listeners`; assenza di queste directory non esclude eventi/listener del framework. Notifica dedicata `app/Notifications/Auth/ResetPasswordNotification.php`.

Console: import/enrichment GEO, rinumerazioni clienti/schedine, pulizia orfani, setup configurazioni e demo sono comandi potenzialmente mutanti: inventariati, **non eseguiti**. Il metodo `schedule()` è vuoto salvo commento: nessun task periodico applicativo dichiarato lì; cron esterno resta PENDENTE. `QUEUE_CONNECTION=sync` locale non dimostra una coda/worker in produzione.

## 4. Multistruttura, ruoli e autorizzazione

🟢 **VERIFICATO nel codice, E4.** Modello gerarchico: User Admin → `proprietari.admin_id` → `struttura.proprietario_id`; Proprietario tramite `users.proprietario_id`; utente operativo tramite `users.struttura_id`. Ruoli: `super_admin`, `admin`, `proprietario`, `struttura_user`; `ruolo_operativo` (proprietario/reception) è distinto dal ruolo gerarchico.

- `StrutturaCorrente`: ID in memoria/sessione, nessuna selezione ordinaria in console; reset prima/dopo richiesta nel middleware.
- `ImpostaStrutturaCorrente`: seleziona struttura consentita, elimina selezioni non valide o sceglie prima disponibile; Super Admin può operare senza filtro. È contesto, non autorizzazione sufficiente da solo.
- `StrutturaAccess::query`: Super Admin tutte; Admin strutture dei propri proprietari; Proprietario proprie; struttura_user una sola; altri negati. Eccezione `operational=true`: Admin include strutture senza proprietario. Selector/CRUD non ereditano automaticamente questa eccezione.
- `RequireAuthorizedStruttura`: rifiuta ID/sessioni manipolati e selezioni conflittuali; produce oggetto autorizzato. Applicato esplicitamente alle route scheda struttura e suggerimenti zona.
- `StrutturaRequest::authorize`: rilegge la membership dell'oggetto autorizzato.
- `AppartieneAStruttura`: filtro globale per record tenant e assegnazione struttura in creazione se assente; senza utente restituisce insieme vuoto. Super Admin senza selezione non ristretto. Admin include eccezione legacy senza proprietario.
- `Ruolo`: confronto esatto su lista di ruoli; più middleware si applicano congiuntamente. Route legacy Admin con ulteriore `ruolo:super_admin` sono deliberate route sempre negate, non privilegi ampliati.
- `VerificaServizioStruttura`: Admin/Super Admin esenti; verifica servizio/struttura per operativi; Proprietario senza selezione può proseguire. La verifica licenza non sostituisce membership.

🟡 **PARZIALMENTE VERIFICATO.** Suite StrutturaAuthorization e Geo logo passate; selector, sessioni false, struttura esterna e CRUD coperti. Non tutti gli endpoint del prodotto usano `RequireAuthorizedStruttura`; le altre aree devono dimostrare filtri/controller/scope autonomi.

### Query fuori scope: usi legittimi e punti da verificare

| Caso | Evidenza / classificazione |
|---|---|
| ISTAT query/counter senza scope | filtro `struttura_id` esplicito e controllo componenti; legittimo, prove su fixture |
| HomeController conteggi senza scope | filtro struttura dashboard esplicito; staticamente circoscritto |
| NotificheService compleanni senza scope | filtro `struttura_id` esplicito; non bypass automatico |
| Calendario eventi senza scope | `guardEventoAccess` controlla proprietario del personale o membership struttura; contesti globali/personali deliberati |
| CustomerExport `DB::table` riferimenti | letture globali deliberatamente conservative prima di cancellare un cliente già autorizzato e bloccato con lock |
| QA `DB::table` conteggi | gruppo super_admin + qa.enabled; accesso diagnostico deliberato, non eseguito sul DB reale |
| ArchivosController query globale | preceduta da `abort(410)`: ramo legacy irraggiungibile, contenimento verificato Safety |
| CestinoService `DB::table`/senza scope | necessario a ripristino ID/soft-deleted, ma dipende dal controller e dall'integrità snapshot; RISCHIO R1/R2 |
| WebCheckin `ensureLinkedSchedina` | trova ID senza scope e non confronta esplicitamente struttura della schedina con richiesta; RISCHIO R5 |
| GestioneOperativa `resolveAuditContext` | arricchisce log da ID senza scope; richiede coerenza tra audit e record e prove avversariali; PARZIALE |
| rinumerazioni, cleanup, import GEO | globalità deliberata console, comandi mutanti; non eseguiti né certificati come sicuri su produzione |

`find()`/`findOrFail()` ereditano lo scope solo sui modelli che lo implementano. Route model binding non autorizza da solo: CustomerImport ricarica batch/rows con struttura e parent batch; i cataloghi globali richiedono una policy di ruoli distinta. I rischi non vengono dedotti dal solo numero di occorrenze di `withoutGlobalScopes()`.

### Impersonazione

🟡 **PARZIALE / RISCHIO R3.** Avvio solo Super Admin, log impersonator/target/IP/user agent, sessione con IDs e cambio Auth. Uscita e route `superadmin.impersona.stop` sono però nel gruppo `ruolo:super_admin`: dopo impersonazione di un ruolo inferiore quel middleware nega l'uscita ordinaria. Inoltre `/impersona/{userId}` precede `/impersona/esci` senza vincolo numerico: possibile collisione di routing, non provata end-to-end. Non è stata impersonata alcuna persona reale.

## 5. Database locale effettivo

🟢 **VERIFICATO, E5.** MySQL **8.0.44** locale, 58 tabelle; 64 colonne FK censite in KEY_COLUMN_USAGE. Le 142 migrazioni registrate corrispondono esattamente ai 142 file disponibili: nessun file da applicare e nessuna migrazione applicata priva di file. Questo non prova identità byte per byte del DDL con le migrazioni, correttezza dei dati o stato della produzione.

Lettura con PDO puro, senza Laravel, soli metadati e nomi delle migrazioni, transazione READ ONLY e rollback. Il primo collegamento è stato negato dal sandbox prima della connessione; il secondo è stato autorizzato per lettura. Nessun `migrate`, DML o dump sul DB operativo.

Tutte le tabelle rilevate sono elencate con PK/indici/FK in appendice B. Le relazioni principali: struttura→proprietario, proprietario→admin, clienti→schedine/customer references, schedina→componenti/camere, batch→rows, ticket→messages, GEO gerarchico e pivot CAP. Relazione Eloquent e FK SQL sono evidenze diverse.

### `struttura_id`: nullabilità e FK effettive

| Tabella | Nullable | FK struttura | Indici che includono la colonna |
|---|---|---|---|
| `admin_servizio_proprietario` | SÌ | struttura | admin_servizio_proprietario_unique, asp_struttura_id_idx |
| `calendario_eventi` | SÌ | struttura | calendario_eventi_struttura_id_data_evento_index, calendario_eventi_struttura_id_stato_index, calendario_eventi_struttura_id_tipo_index |
| `cestino_items` | SÌ | **ASSENTE** | cestino_items_struttura_id_index |
| `clienti` | SÌ | struttura | clienti_struttura_id_index, clienti_struttura_numero_cliente_unique |
| `componenti` | SÌ | struttura | componenti_struttura_id_index |
| `crm_leads` | SÌ | struttura | crm_leads_struttura_id_stato_index |
| `customer_import_batches` | NO | **ASSENTE** | customer_import_batches_struttura_id_created_at_index |
| `istat_exports` | SÌ | **ASSENTE** | istat_exports_struttura_id_index |
| `istat_movimenti_giornalieri` | NO | **ASSENTE** | istat_movimenti_giornalieri_struttura_id_giorno_unique, istat_movimenti_giornalieri_struttura_id_index |
| `istat_transmissions` | SÌ | **ASSENTE** | istat_transmissions_struttura_id_index |
| `licenza_assegnazioni` | SÌ | struttura | licenza_assegnazioni_struttura_id_foreign |
| `proprietario_fatturazione_righe` | SÌ | struttura | proprietario_fatturazione_righe_struttura_id_foreign |
| `questura_exports` | SÌ | **ASSENTE** | questura_exports_struttura_id_index |
| `questura_transmissions` | SÌ | **ASSENTE** | questura_transmissions_struttura_id_index |
| `schedina` | SÌ | struttura | schedina_struttura_id_index |
| `schedina_camere` | SÌ | struttura | schedina_camere_struttura_id_index |
| `struttura_accessi` | NO | struttura | struttura_accessi_struttura_id_entrata_at_index |
| `struttura_audit_logs` | NO | struttura | struttura_audit_logs_struttura_id_created_at_index |
| `struttura_comande` | NO | struttura | struttura_comande_struttura_id_stato_index |
| `struttura_zone` | NO | **ASSENTE** | struttura_zone_struttura_id_index, struttura_zone_unique |
| `support_ticket_messages` | NO | **ASSENTE** | support_ticket_messages_struttura_id_index |
| `support_tickets` | NO | **ASSENTE** | support_tickets_struttura_id_index |
| `tassa_di_soggiorno` | NO | struttura | tassa_di_soggiorno_struttura_id_foreign |
| `tassa_esenzioni` | NO | struttura | tassa_esenzioni_struttura_id_codice_unique, tassa_esenzioni_struttura_id_ordine_index |
| `users` | SÌ | struttura | users_struttura_id_index |
| `web_checkin_richieste` | NO | **ASSENTE** | web_checkin_richieste_struttura_id_numero_prenotazione_index, web_checkin_richieste_struttura_id_stato_index |

🔴 **RISCHIO R6 verificato nello schema:** 11 tabelle senza FK su `struttura_id`: `cestino_items`, `customer_import_batches`, `istat_exports`, `istat_movimenti_giornalieri`, `istat_transmissions`, `questura_exports`, `questura_transmissions`, `struttura_zone`, `support_ticket_messages`, `support_tickets`, `web_checkin_richieste`. Tutte hanno un indice che include la colonna. Nessuna FK aggiunta. Assenza FK non dimostra record orfani: non sono stati letti conteggi dei dati.

NULL è deliberato per utenti globali, amministrazione e calendario personale; per Cestino può derivare da entità globali. Per clienti/schedine/componenti/export NULL può essere legacy: non va dichiarato legittimo né cancellato automaticamente. Non verificata la popolazione NULL reale. `password_resets` non ha primary key, ma ha indice email: compatibilità legacy, non bug dimostrato.

Nomi storici: `struttura`, `schedina`, `clienti` e campi con accenti/alias (`città`, `località`, `logo_città`); non rinominati. Modello `Arrivals`/tabella `arrivi` convive con circuito arrivi su Schedina; l'attività del controller deve distinguersi dalla semplice presenza del modello.

## 6. GEO: condiviso e integrazioni

🟢 **VERIFICATO nel codice/schema.** Sei tabelle: `geo_nazioni`, `geo_regioni`, `geo_province`, `geo_comuni`, `geo_cap`, `geo_comuni_cap`. Gerarchia Nazione→Regione→Provincia→Comune con FK; CAP molti-a-molti tramite pivot con principale/priorità/località. Dati globali condivisi, non tenant. Codice ISTAT Comune e ID interno non sono intercambiabili.

GeoController serve lookup/resolver. GeoImportService/NationService e comandi import/enrichment costituiscono strumenti amministrativi; non eseguiti. UI supportata `<x-geo.italia>` con `resources/js/ui/geo-italia.js` e reexport componenti; inizializzazione attraverso infrastruttura UI. I prefissi `/geo` autenticato, `/api/geo` API e `/geo-public` pubblico presenti nelle route hanno contesti differenti.

Loghi: GeoComuneLogoController, route riservate a Admin/Super Admin, storage pubblico `geo_comuni/logo`, PNG/JPG/JPEG/WebP max 4096 KB; cancellazione limitata alla directory prevista; fallback legacy logo. Prove correnti: ruoli e flusso CRUD su fixture passati. Il core GEO è protetto anche dal controllo hash durante build isolato.

Correzione verificata 2026-10-05: il paginator di default Laravel era Tailwind (`pagination::tailwind`), che genera un `<svg class="w-5 h-5" ...>` senza stili Tailwind in questo app, causando la freccia gigante sotto la lista. La fix condivisa è stata applicata in `app/Providers/AppServiceProvider.php` tramite `Paginator::useBootstrapFive()`, mantenendo il render senza SVG delle pagine di listato e validando la regressione con `tests/Feature/GeoComuneLogoTest.php` in runtime isolato (11 test, 112 asserzioni PASS).

Ricerca Geo Comuni: il pattern riutilizzato è il debounce già presente nei componenti `x-table-topbar`/`x-crud-table` di questa applicazione. La pagina `GeoComuneLogoController` filtra automaticamente su `geo_comuni.nome` e su `geo_comuni_cap` → `geo_cap.cap`, senza usare `codice_istat`. Il campo `q` aggiorna la query con debounce di 350 ms, elimina `page` e reindirizza alla pagina 1; svuotando il campo ripristina il set completo. La validazione in runtime isolato include ricerca Comune, ricerca CAP, filtro negativo su codice ISTAT e mantenimento del flusso upload/rimozione logo.

Liste e export clienti: il filtro di ricerca della pagina `resources/views/customers/export.blade.php` è stato semplificato a un unico campo `q`, con debounce di 350 ms, aggiornamento asincrono senza full reload, focus preservato e reset di `page` verso 1. Il backend in `app/Http/Controllers/CustomerExportController.php` mantiene la logica di filtraggio server-side, consent e canali/permessi; la modifica è solo UX e non altera il contratto di sicurezza/export né lo stato di tenant o DB.

Questura: legge GEO e mapping propri di QuesturaTxtExportService. ISTAT: IstatCodifiche legge GEO e traduce su snapshot ufficiali dedicati `reference/istat/ross1000-er/`; non riscrive GEO né mapping Questura. Conformità di un'integrazione non certifica l'altra. Stato del dataset GEO reale e completezza di ogni codice non verificati qui.

## 7. Questura / Alloggiati Web

🟡 **PARZIALMENTE VERIFICATO.** Flusso: pagina Questura → QuesturaExportController → QuesturaTxtExportService (schedine/componenti, validazione, TXT fisso, mapping) → download/storico o QuesturaWebService → QuesturaExport/QuesturaTransmission. Storici filtrati per struttura; risoluzione della struttura corrente separata dai filtri Eloquent. Nessuna trasmissione eseguita.

Il servizio esterno usa SOAP: autenticazione/token e operazioni `Test`/invio secondo il ramo applicativo. Simulazione non equivale ad accettazione. EsitoTrasmissioneQuestura proietta campi ammessi e messaggi controllati; non espone body/token/provider arbitrari. Ricevute e tabelle remote non validate sono in quarantena: la presenza dei pulsanti non dimostra una ricevuta ufficiale utilizzabile. Legacy globale fermato con 410.

**E8 attuale:** 14 controlli Safety passati con double in memoria, senza DB/bootstrap/rete. **E9 precedente:** report ISTAT e test IstatConformita attestano byte-identità Questura prima/dopo generazione ISTAT sugli stessi input sintetici. Questa task non modifica alcun file Questura. Resta PENDENTE la readiness reale, mapping di tutti i casi effettivi, risposta dell'ente e ricevuta validata; non si degrada il contratto chiuso né si corregge Questura in questa audit.

## 8. ISTAT / Ross1000 Emilia-Romagna

🟢 **VERIFICATO presenza e contratto locale, E4/E6/E9.** Controller IstatTabellaAController, servizi IstatTabellaAService, IstatWebService, IstatXmlValidator, IstatStoricoValidator, IstatCodifiche, EsitoTrasmissioneIstat; modelli IstatExport/IstatTransmission/IstatMovimentoGiornaliero. Route `istat.tabella_a.*`, Blade `istat_tabella_a/index`.

Genera XML individuale, componenti inclusi, giorni/arrivi/partenze/pernottamenti distinti, residenza separata da cittadinanza, identificativi stabili, codici e domini ufficiali. Dati mancanti/ambigui, composizione incoerente, province errate e storico da rettificare bloccano l'export. Verifica locale non invia; send è la distinta operazione mutante verso l'ente.

Fonti conservate: WSDL integrale, due XSD estratti con soli import locali, adattatore **locale** `file-binding.xsd`, codifiche pubbliche e provenienza/hash. [Manuali regionali](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo), [WSDL](https://datiturismo.regione.emilia-romagna.it/ws/checkinV2?wsdl). Versione XML-WS 2.4: verifica ufficiale della task precedente al 05/10/2026, non nuova navigazione ripetuta in questa audit. Dettagli in `docs/chiusura-istat-2026-10-05.md` e README reference.

| Livello | Stato / evidenza |
|---|---|
| Conformità tecnica locale | VERIFICATA sugli input e controlli implementati; non ogni dato reale |
| Fixture sintetiche | VERIFICATE nei test IstatConformita e fixture `tests/Fixtures/istat/famiglia.xml` |
| XSD | VERIFICATO rispetto ai tipi ufficiali con adattatore locale documentato; SOAP schema ufficiale |
| Confronto reale TAVOLA-A | PARZIALE: CSV aggregati analizzati nella task precedente, non confronto nominativo con export reale |
| Codice ufficiale struttura | PENDENTE: task precedente ha constatato assenza in tutte le 7 strutture; non riletto/alterato qui |
| Trasmissione reale / ricevuta | PENDENTE: nessuna inviata, nessuna accettazione attestata |

Report precedente: marzo 32 giorni aperti su 31; settembre 31 su 30 e totale partenze 138 invece di 203. Ipotesi off-by-one/omissione esteri non dimostrate come cause del software esterno. CSV empirico non sostituisce la specifica. Nessun CSV reale o nominativo viene incorporato nel Maestro.

Readiness reale **PENDENTE**: codice Ross1000 assegnato, riconciliazione file reale/periodo e rettifiche pregresse, classificazioni individuali eventualmente diverse dai campi condivisi. Il superamento XSD/fixture non è accettazione dell'ente.

## 9. Clienti, Componenti e importazioni

🟡 **PARZIALMENTE VERIFICATO.** Customers rappresenta `clienti`, trait tenant; Componenti appartiene a struttura e schedina e può riferire customer. CustomerController gestisce anagrafica, validazioni per tipo, consensi e trasferimento a schedina; CustomerExportController filtra/esporta e cancella solo cliente tenant senza riferimenti storici, in transazione/lock e snapshot Cestino.

CustomerImport: file → batch/rows → payload raw/normalizzato, lookup GEO, stato/duplicati → preview/revisione → conferma/commit → cliente o passaggio schedina. CustomerImportController ricarica binding attraverso struttura e batch padre; righe non importate eliminate separatamente, importate protette. Necessaria distinzione nome descrittivo/codice GEO, anagrafica incompleta/conferma operativa.

ComponentiImport: CSV/TXT/XLSX tramite OpenSpout; contratto V1 e normalizzatore dedicati; preview e conferma separate, token associato a user/struttura/schedina, scadenza/consumo e `SingleNodeBatchLock` contro doppia conferma sul singolo nodo; payload struttura assegnato dal server. Nessuna garanzia multi-node dedotta dal lock locale. Catalogo TipoAlloggiatoCatalogo deve essere pronto e coerente. Import non autorizza trasmissione di dati incompleti.

🔴 **PENDENTE R7/E6:** il triage successivo in appendice F sostituisce la lettura generica del disallineamento: quattro failure rilevano un bug reale sulle date; un secondo difetto riguarda le righe strutturalmente errate. Gli altri casi distinguono contratti obsoleti, fixture errate e harness incompleto. Nessun riallineamento automatico per ottenere una suite verde. ComponentiImportFormTest untracked: un fallimento di preview e un errore Request::validate; il test JS untracked fallisce tre casi per double/browser context incompleto. Feature CustomerImportedFiltersDeleteTest: due errori di fixture struttura priva di campi obbligatori. SchedinaStoreTest presume User ID 11 e fallisce nel DB vuoto. Nessuno di questi problemi è stato corretto.

Lo script autonomo ComponentiImportReviewStatusTest passa; i test di normalizzazione/contratto/import Clienti inclusi nella suite ampliata forniscono evidenza limitata ai loro casi. Non dichiarare tutti i flussi Clienti/Componenti chiusi solo sulla base dei report del 04/10.

## 10. Cestino e ripristino

🔴 **RISCHIO R1/R2, E4.** CestinoItem non usa AppartieneAStruttura. Route index/restore/destroy sono nel gruppo auth, senza ulteriore middleware ruolo. CestinoController applica restrizioni speciali all'Admin: proprie strutture tramite proprietario assegnato, esclusione entità amministrative/globali, licenze consultabili ma non ripristinabili/eliminabili. Per altri ruoli il filtro condizionale include `struttura_id IS NULL` oppure struttura corrente; senza selezione non aggiunge quel filtro. Non è presente una guardia generale che riservi le entità globali al Super Admin. Rischio statico concreto; non eseguita una prova distruttiva né un exploit su dati reali.

Snapshot contiene class/ID originale/payload/titolo/circuito/source, utente e struttura. `struttura_id=NULL` è deliberato per varie entità globali; accessibilità e permessi devono essere decisi per tipo di entità, non dal solo valore NULL.

CestinoService ripristina in transazione, con allowlist classi supportate. Query senza scope e DB::table bypassano volutamente observer/mass assignment automatici: dati filtrati con fillable, ma struttura e relazioni dipendono dalla correttezza dello snapshot/controller. Ripristini di componenti verificano l'esistenza di parent/customer globalmente, non una membership tenant esplicita: da testare avversarialmente.

**EVIDENZA 2026-10-05 — Cestino P0 verificato nello scope autorizzato.** Il runtime isolato ha eseguito `python3 tests/Isolation/run.py --phpunit tests/Feature/CestinoSecurityTest.php` con esito `OK (29 tests, 239 assertions)`. La causa reale del blocco era la guardia di servizio `VerificaServizioStruttura`, che abortiva su ruoli non riconosciuti prima della vista Cestino, impedendo la pagina con lista vuota per utenti non appartenenti ai ruoli tenant. La correzione minima non altera gli allowlist di autorizzazione né il ripristino persistito: mantiene l'esclusione dei snapshot globali non autorizzati e la sanitizzazione server-side del payload HTML/DOM, riducendo rischio di esposizione di token/password/secret.

IDs: normalmente preservati se liberi; nuova identità per schedine, componenti e Web Check-in; codici circuito rigenerati e token Web Check-in nuovo. Amministratori/proprietari/strutture riusano soft-deleted e relazioni salvate. **Non è corretto dire che ogni ID è sempre preservato.** Purge forza cancellazione record soft-deleted solo per User/Proprietario/Struttura, poi elimina snapshot; per altre classi elimina snapshot.

R2: snapshot User usa `getAttributes()` (include hash password); snapshot Struttura usa `toArray()` e il modello non dichiara hidden/encrypted per credenziali Questura/ISTAT. Conseguenza: possibile replica di credenziali nei payload Cestino. Non letti valori né dimostrata esposizione in una risposta UI. Necessaria verifica di minimizzazione, retention, cifratura e accesso globale in task separata.

## 11. Audit operativo e accessi

🟢 **VERIFICATO nel codice.** StrutturaAuditLog conserva struttura, user, route/metodo, tipo/ID entità, descrizione, IP e timestamp; trait tenant. LogOperativeAudit è middleware web e registra dopo risposta riuscita (<400) su POST/PUT/PATCH/DELETE di prefissi selezionati.

🔴 **RISCHIO R4:** registra solo utenti con `user.struttura_id` valorizzato e usa quel valore, non la struttura autorizzata/corrente. Admin/Super Admin/Proprietario senza struttura fissa non lasciano questo log, anche quando lavorano su struttura. Non registra GET (anche se alcuni generano export o aggiornano Web Check-in), fallimenti, login/public non autenticati, Cestino e calendario fuori allowlist. Redirect 302 dopo validazione fallita può sembrare successo. Non è log transazionale garantito con la modifica applicativa e gli IDs di nuove entità non presenti nei parametri possono restare NULL. Non dichiara audit completo.

AccessoOperativoService apre/chiude StrutturaAccesso solo per utenti con struttura; LoginController controlla attivo e servizio e chiama il servizio. Fallback GET logout in route salta `close()`: potenziale accesso rimasto aperto e logout provocabile da navigazione (R9). ImpersonationLog è audit separato; iniziatore reale non è salvato come campo distinto negli audit ordinari durante impersonazione.

## 12. Altri moduli e stato dimostrabile

| Modulo | Implementazione / responsabilità | Stato baseline |
|---|---|---|
| Dashboard | HomeController, struttura dashboard, indicatori operativi/economici e notifiche | PARZIALE: presenza/codice; E2E completo non eseguito |
| Schedine | SchedinaController + Schedina/camere/componenti, circuiti bozza/schedina/web/arrivi | PARZIALE; feature store legacy fallita |
| Arrivi | ArrivalsController usa circuito Schedina e passaggio a schedina | PARZIALE; tabella Arrivals legacy da distinguere |
| Presenze | PresenzeController + PresenzeReportService, dettagli/aggregati/CSV | PARZIALE, semantica condivisa età/soggiorno |
| Calendario | CalendarioController, eventi manuali personali/struttura/portfolio e automatici | PARZIALE; guardia eventi presente, non matrice completa E2E |
| Web Check-in | WebCheckinController, richiesta + token + schedina circuito web, conversione | RISCHIO R5: collegamento tenant e route short; nessun E2E pubblico eseguito |
| Tassa soggiorno | controller configurazione/esenzioni/report e TassaDiSoggiornoService/EtaOperativa | PARZIALE; unit pertinenti passate, applicabilità normativa reale non certificata |
| Notifiche | NotificheService unisce manuali, servizio, supporto, compleanni; comande/stati | PARZIALE; letture tenant statiche, non suite completa |
| CRM | CrmController + CrmLead/Activity, lead da form sito, attività e agenda | PARZIALE; route Super Admin, legacy Admin negata |
| Supporto | SupportoController + ticket/messages, assegnazione, risposta e stati | PARZIALE; modelli scoped, struttura senza FK SQL |
| Configurazioni | tipi, gruppi, titoli, documenti, esenzioni, struttura/GEO | PARZIALE; cataloghi globali richiedono policy esplicita |
| Amministrazione | Controller Superadmin/Admin/Proprietario, selector e utenti struttura | PARZIALE: controlli presenti; impersonazione R3 |
| Proprietari/strutture | gerarchia owner/admin, stato servizio, dati anagrafici/commerciali | PARZIALE; schema/ruoli verificati, non dati effettivi |
| Licenze/fatturazione | LicenzaArticolo/Assegnazione, proforme Admin/Proprietario e righe | PARZIALE; non attestata emissione fiscale/invio SDI |
| Sito pubblico | PublicSiteController e WebsiteContactController → CRM | RISCHIO R8 per CSRF/rate limit form contatti |

## 13. Sicurezza: diagnosi non distruttiva

Non è stato condotto penetration test esaustivo; niente dati sensibili stampati. `.env` presente locale ma non tracked; Git segue solo `.env.example`; .gitignore esclude varianti, SQL, chiavi e runtime/log. `APP_ENV=local`, `APP_DEBUG=false`; sessioni file, cache file, queue sync, log stack. Cache bootstrap packages/services presente, config cache assente. Stato dei log e segreti storici Git **PENDENTE**: non eseguita una scansione integrale del contenuto di dati/log o di tutta la storia.

Autenticazione: login username/email con password Hash e `attivo=true`, soft-deleted esclusi; trait Laravel throttling. `Auth::routes()` include registrazione pubblica: RegisterController con validazione avatar/password, ma policy SaaS sull'auto-registrazione e ruolo di default da verificare. Sanctum disponibile per API user; non censiti token reali.

CSRF attivo nel gruppo web e nei test HTTP; eccezione esplicita solo route contatti sito via withoutMiddleware. GET logout non richiede CSRF e GET Web Check-in aggiorna lo stato. API ha rate limiter; route web pubbliche contatti/checkin non hanno throttle esplicito visibile. R8 è rischio abuso/spam/retention, non escalation comprovata.

Mass assignment: modelli con fillable (anche ruolo/struttura/credenziali) richiedono payload autorizzati; non basta la lista fillable come permesso. CustomerController usa request->all per Validator, non è da solo prova di update indiscriminato. StrutturaRequest autorizza membership e validazioni; nessun audit esaustivo di ogni campo/controller.

SQL raw osservate: placeholder per LOWER(nome), costanti case/ordinamenti/contatori e query reference DB::table. Non individuata SQL injection comprovata nel campione; non equivale ad assenza garantita in tutto il progetto.

Upload: Geo logo validato e directory protetta, prove corrente PASS; import Componenti validazione file + parser + contratto; avatar registra upload pubblico. Pending verifica formule CSV/XLSX, zip bomb/limiti risorse, spoofing MIME, path traversal di ogni download, retention export e accesso agli snapshot. Non allegati file reali né segreti.

Web Check-in: token completo casuale 64 caratteri; metodi di accesso corto generano codice+prime 8 cifre/token e usano LIKE prefix, senza minimo lunghezza/escaping dedicato nel metodo. **Nelle route correnti `/w/{token}` è associato direttamente ai metodi per token completo, non ai metodi Short/Invite:** link generato corto può non essere risolto. Il metodo prefix non va dichiarato esposto senza route. Nessuna scadenza token dedicata nel modello/schema osservato; converted blocca modifiche. Componenti scoped con utente anonimo e recupero schedina senza scope richiedono prova end-to-end. R5 da approfondire senza ridurre isolamento.

Gestione utenti struttura: StrutturaUserController risolve la membership, ma per struttura_user consente la propria struttura senza richiedere ruolo_operativo proprietario. Le azioni store/resetPassword non mostrano una guardia di gestione distinta dalla membership; reset seleziona qualsiasi utente della stessa struttura. R14: possibile reset credenziali di altri operatori da personale privo di ruolo gestionale; policy attesa e sfruttabilità HTTP da verificare su fixture, nessuna password reale cambiata.

Trasmissioni: prove in memoria/fake, Http Laravel preventStrayRequests nel runtime; no body arbitrari in esiti, nessuna accettazione inventata. I SoapClient reali non vanno invocati in test per il solo fatto che Http Laravel sia bloccato: restano proibiti e si utilizzano double/simulazione. Readiness produzione **PENDENTE**.

## 14. Rischi e backlog prioritizzato (non implementato)

Le priorità esprimono urgenza proposta; prove statiche sono distinte da exploit. Nessun P0 attivo dimostrato; non è garanzia di assenza di P0. Un nuovo riscontro di esposizione globale effettiva deve promuovere R1/R2/R5 a P0 e bloccare rilascio.

| ID | Priorità | Condizione / evidenza | Stato e prossimo criterio di verifica |
|---|---|---|---|
| R1 | P1 alto | Cestino senza scope tenant generale; NULL incluso per ruoli diversi; assenza guardia entità globali | RISCHIO statico: matrice restore/purge/read con fixture globali/estere e ruoli inferiori |
| R2 | P1 alto | Snapshot Struttura/User può replicare credenziali/hash senza minimizzazione | RISCHIO statico: verificare payload sintetici, serialize/UI/accesso e retention; mai estrarre segreti reali |
| R3 | P1 alto | uscita impersonazione dietro ruolo della persona impersonata e possibile collisione route | RISCHIO statico: E2E quattro ruoli e ritorno identità/sessione |
| R4 | P1 alto | audit operativo escluso per amministratori senza struttura e per azioni mutanti GET/Cestino | GAP nel codice: definire evento, tenant effettivo e iniziatore, test transazionali |
| R5 | P1 alto | Web Check-in parent senza confronto tenant, short URL scollegata, token/anonimo da verificare | PARZIALE/RISCHIO: fixture cross-tenant, scadenza, conversione, replay, link corto |
| R6 | P1 alto | 11 struttura_id senza FK; nullable tenant operativi | SCHEMA VERIFICATO: audit orfani/semantica NULL in lettura, proposta migrazione separata; non aggiungere FK alla cieca |
| R7 | P1 alto | suite ampliata rossa e spec isolamento browser non completato | RIPRODOTTO: allineare contratto/test senza mascherare difetti; ogni fallimento ha triage e fixture |
| R8 | P2 medio | form pubblico senza CSRF e throttle esplicito, registrazione pubblica da governare | RISCHIO statico: policy ingressi, anti-abuso, retention e ruoli di default |
| R9 | P2 medio | GET logout bypassa chiusura AccessoOperativoService | GAP statico: compatibilità logout e audit accessi, prova separata |
| R10 | P1 alto | ISTAT manca codice assegnato/prova reale controllata | PENDENTE readiness: non dichiarare pronto, nessuna trasmissione automatica |
| R11 | P1 alto | accettazione Rocky e configurazione produzione non verificate | PENDENTE rilascio: acceptance nell'ambiente isolato previsto e checklist produzione autorizzata |
| R12 | P2 medio | versioni legacy/bootstrap, nomenclatura accentata, controller grandi, test standalone con suffisso PHPUnit | DEBITO: documentare contratti, separare runner e fixture, non refactoring estetico |
| R14 | P1 alto | gestione utenti/reset password autorizzata dalla sola membership per struttura_user | RISCHIO statico: matrice reception/proprietario operativo e reset di altro utente con fixture |
| R13 | P3 miglioramento | documenti duplicati/lingue miste e manutenzione Maestro | PROPOSTA: indice storico e cadenza aggiornamento, nessuna pulizia ora |

P0: nessun bug critico riprodotto in questa audit; task di verifica immediata R1/R2/R5 prima di certificare sicurezza. P1: robustezza tenancy/audit/testing/readiness. P2: manutenibilità/policy/legacy. P3: organizzazione documentale. Debito tecnico non implica bug; indice presente non sostituisce FK; NULL deliberato non va eliminato genericamente.

## 15. Testing: inventario, runtime ed esiti attuali

L'inventario completo è in appendice C; non esiste una directory `tests/Integration`, ma integrazioni reali kernel/DB/XSD sono presenti nelle Feature e nel runtime. Classificazione basata sugli entrypoint, non sul solo nome dei file.

Runtime letto integralmente nelle parti di avvio, attestazione, copie, connessioni, build/migrazioni/fixture e finally; letti TestingEnvironment, bootstrap/supporto browser e documentazione isolamento. **TEST_ISOLATION_REQUIRED rimane attivo.**

Ogni run crea nuovo datadir MySQL 8.0.36 con `--no-defaults`, utente fixture limitato al DB effimero, HTTP dedicato e supervisore challenge; attesta PID/parentela/UUID/datadir/porta/DATABASE prima delle migrazioni. Copia codice tracked e test selezionati; esclude .env/dati storage/seeders/immagini reali e cache. Filesystem/mail/cache locali temporanei, HTTP Laravel fake-only; proxy browser solo origine attestata, service worker bloccati. Nessun servizio MySQL Herd corrente riutilizzato. Il fatto che il binario risieda nella cartella Herd non implica uso del suo servizio.

DB **operativo osservato 8.0.44** e binario **test 8.0.36** differiscono: schema isolato migrato da zero, non clone del DB reale. Questa differenza va conservata nella diagnosi, non appiattita.

### Esiti di questa task

| Esecuzione | Esito | Limiti |
|---|---|---|
| Guardrail esterni `tests/Isolation/test_guards.py` | **5 PASS** | nessun DB/browser reale |
| Safety Questura | **14 PASS** | double in memoria; nessuna accettazione esterna |
| Safety ISTAT | **22 PASS** | memoria, nessun bootstrap/transmission |
| Safety Geo logo | **37 controlli PASS** | pipeline ruoli in memoria |
| Script `ComponentiImportReviewStatusTest.php` | **PASS** | autonomo, non classe PHPUnit |
| Unit JS untracked `componenti-import-submit.test.mjs` | **3 falliti / 0 passati** | `submitter.matches` assente e `componentIndexInput` non definito nel contesto simulato |
| Primo runner PHPUnit | **interrotto, exit 1** | selezione erronea dello script autonomo come classe; nessuna suite certificata da quel run |
| Suite ampliata correttamente selezionata | **232 test, 868 asserzioni; 213 senza fallimenti/errori, 15 failure, 4 error; 1 deprecazione** | NON PASS; dettaglio sotto |
| Feature GeoLogo/Topbar/StrutturaAuthorization separata | **32 PASS, 193 asserzioni** | sovrapposta alla suite ampliata; non sommare |
| Playwright Geo logo | **4 PASS, 1 fallito** | quinto test isolation abort dopo 403; catena completa non dimostrata |
| Deployment unittest discovery | **87 test: 84 PASS, 3 skip, nessun failure/error** | Linux/Rocky richiesto dai tre skip; nessun deployment reale |

**E6 dettaglio:** selezionate tutte le 11 classi Unit presenti (compresa ComponentiImportFormTest untracked) e sette Feature: IstatConformita, IstatTransmissionSecurity, GeoComuneLogo, TopbarRendering, StrutturaAuthorization, CustomerImportedFiltersDelete, SchedinaStore. 14 failure ComponentiImportServiceTest; 1 failure + 1 error ComponentiImportFormTest; 2 error CustomerImportedFiltersDeleteTest (struttura senza `tipologia_struttura` obbligatorio); 1 error SchedinaStoreTest (User 11 assente). Gli errori di fixture non dimostrano il comportamento applicativo delle richieste che non hanno raggiunto il controller. Le aspettative di stati non coerenti richiedono triage semantico, non sostituzione automatica delle asserzioni.

**E7 browser dettaglio:** passati Super Admin/Admin login/menu/pagina/logo/ricerca; Admin upload/sostituzione/rimozione con CSRF; Proprietario menu assente/accesso diretto vietato. Il test finale `isolamento browser: blocca URL esterni e redirect, inclusa catena locale` fallisce a `request.get(trap/forbidden)`: il proxy restituisce 403 TEST_ISOLATION_REQUIRED e Playwright genera `aborted` anziché consegnare Response all'asserzione. Il rifiuto iniziale è osservato; non sono raggiunte tutte le asserzioni successive e zero richieste all'origine vietata non è attestato dal completamento del test. Non cambiare guardrail per farlo passare.

**Deployment:** discovery `python3 -B -m unittest discover -s tests/Deployment -v`, temporanei/repository fixture/HTTP loopback senza DB reale. Tre skip Linux subreaper/prctl/proc: daemon staccato, SIGKILL e doppio fork che ignora TERM. Il launcher Rocky/Bubblewrap ha propri test; questo non equivale a eseguirlo su Rocky.

### Runtime effettivamente utilizzati e pulizia

| Run | Database effimero / risorse | Esito |
|---|---|---|
| Prima selezione | `test_geo_7e238248e2ea20d925a6116d04ed0112`; `/private/tmp/schedine-test-qmsn7s2c`; MySQL PID15757 :62613; HTTP PID15762 :62614 | fermato dopo entrypoint errato; finally pulizia |
| E6 ampliata | `test_geo_0a9163ef2a949a43b60fffd7ad545d61`; `/private/tmp/schedine-test-5u00rdy0`; MySQL PID15960 :62666; HTTP PID15966 :62667 | 232 test; risorse rimosse |
| E7 browser | `test_geo_fd73359e8389ecab74fca200f93360b7`; `/private/tmp/schedine-test-aygx27sb`; MySQL PID18411 :63139; HTTP PID18416 :63140 | PHPUnit PASS, browser parziale; risorse rimosse |

Ogni run ha passato identità DB/HTTP, riconnessione e **23 rifiuti di override prima delle migrazioni**, build asset e verifica GEO immutabile. Migrazioni soltanto sulle istanze nuove; scritture soltanto fixture. Cleanup anche sui run falliti. Non aggiungere i numeri di run sovrapposti per vantare una suite complessiva passata.

### Comandi riproducibili

```sh
python3 -B tests/Isolation/test_guards.py
php tests/Safety/questura_security.php
php tests/Safety/istat_security.php
php tests/Safety/geo_logo_security.php
php tests/Unit/ComponentiImportReviewStatusTest.php
node --test tests/Unit/componenti-import-submit.test.mjs
python3 -B -m unittest discover -s tests/Deployment -v
```

La suite ampliata è costruita selezionando i `tests/Unit/*Test.php` che dichiarano una classe `extends`, più le sette Feature elencate sopra, e passandoli individualmente a `python3 -B tests/Isolation/run.py --phpunit ...`. Lo script autonomo non deve essere passato a PHPUnit.

```sh
python3 -B tests/Isolation/run.py \
 --phpunit tests/Feature/GeoComuneLogoTest.php \
 tests/Feature/TopbarRenderingTest.php tests/Feature/StrutturaAuthorizationTest.php \
 --fixtures tests/Isolation/seed-geo.php \
 --playwright tests/Feature/geo-logo.playwright.spec.js
```

### Non eseguiti e motivi

AccessTest e TenancyTest richiedono DemoSaasDataFullSeeder escluso dalla copia; SmokePagesQaTest presume utenti/strutture esistenti; SchedinaStoreTest è stato eseguito e ha confermato la dipendenza da User 11. Altri spec clienti/configurazioni/struttura/tassa presumono account/cataloghi demo non predisposti dalla fixture selezionata: non eseguiti, nessun ripiego su dati reali. Nessuna Integration directory autonoma. `tests/Deployment/check_rocky10.py --run-process-tests` non eseguito sul server: accesso vietato e acceptance Linux non disponibile localmente. `tavola_a_audit.py` non ripetuto su CSV reali: analisi precedente registrata come storica.

**Esiti precedenti, non nuovi:** report ISTAT 77 test/377 asserzioni PASS; Geo precedente 32/193 e 5 browser PASS; report Deployment precedente 66 totali (63 passati,3 skip). I numeri correnti 232/868 con failure e 4/5 browser prevalgono per questa baseline. Gli esiti positivi precedenti restano validi solo per il loro ambito/esecuzione.

## 16. SPanel e produzione

⚪ **PENDENTE**, nessun accesso eseguito. Le indicazioni seguenti sono **STORICHE** dai documenti/richiesta: server SPanel/Rocky Linux (report cita Rocky 10.2), PHP 8.3, repository `/home/tanggosoftware/repos/schedinedinotifica`, dominio `schedinedinotifica.tanggo.software`. Non provano configurazione o versione attuale. Branch production-deploy e guardrail locali non certificano un rilascio.

| Controllo di produzione | Stato / evidenza mancante |
|---|---|
| Document root e release/HEAD effettivo | PENDENTE, nessun accesso filesystem server |
| .env, APP_KEY e configurazione effettiva | PENDENTE, nessun segreto letto o mostrato |
| DB/motore/schema/migrazioni | PENDENTE; metadata E5 solo locale |
| Scheduler/cron | PENDENTE; Kernel locale senza schedule, cron esterno sconosciuto |
| Queue/worker/process supervisor | PENDENTE; locale sync non prova server |
| Permissions/storage/public storage symlink | PENDENTE sul server |
| Cache/log/debug/rotation/retention | PENDENTE sul server |
| SSL/proxy/headers/session cookie | PENDENTE; nessun probe del dominio |
| Mail/provider/consegna | PENDENTE; test mail array soltanto |
| Backup e restore procedure realmente provata | PENDENTE; nessun backup/restore eseguito |
| Asset compilati/manifest/versione | PENDENTE sul server; build isolate PASS |
| Health check/monitoring | PENDENTE, non richiesto accesso reale |
| Guardrail deployment e Rocky/Bubblewrap | PARZIALE: test locali PASS, 3 Linux skip, acceptance host non svolta |

## 17. Contraddizioni e lettura dei documenti storici

| Documento/affermazione | Evidenza attuale e decisione |
|---|---|
| PRODUCT-SUMMARY: invio ISTAT/Questura come funzionalità compiuta | Capacità nel codice, ma esiti non provano accettazione; ISTAT readiness PENDENTE |
| PRODUCT-SUMMARY: soggiorni senza assegnazione camere | Modello SchedinaCamera e sincronizzazione camere esistono; capacità va documentata per flusso, non negata globalmente |
| QA: `php artisan test` e seeding demo | Fuori launcher bloccato TEST_ISOLATION_REQUIRED; non eseguire sul DB operativo |
| GEO: unica fonte geo_* e JSON mai runtime | Valido per core/UI, ma integrazione ISTAT usa tabelle reference ufficiali dedicate; non globalizzare la frase ai mapping di ogni ente |
| GEO: prefisso esclusivamente /geo oppure /api/geo | Entrambi e /geo-public presenti: distinguere contesto auth/API/pubblico |
| Report Geo: 5 browser PASS | Storico; esecuzione corrente 4 PASS/1 failure nella prova isolamento, senza modifica codice |
| Report Deployment: 66 test/63 passati | Storico; discovery corrente 87 totali/84 passati/3 skip; nessuna acceptance Rocky dedotta |
| Report Clienti del 04/10 | Contengono anche limiti/fallimenti preesistenti: non trasformare il titolo di chiusura in garanzia globale. Numero/tipo dei fallimenti odierni cambia con suite/contratti/fixture |
| Rapporto senza FK struttura_id | Confermato attualmente: esattamente le 11 tabelle elencate, non una lista genericamente copiata |
| Isolamento: MySQL 8.0.36 | Confermato binario test; DB locale operativo rilevato 8.0.44, non stesso servizio/versione |
| Impersonazione: avvio/uscita disponibile come flusso completo | Codice avvio presente; uscita route ruolo Super Admin resta rischio non coperto |

Non sono stati eliminati né riscritti documenti storici. I formati MD/HTML/PDF del verbale sono trattati come artefatti di consegna, non come prova di runtime; la copia PDF non è stata validata visivamente in questa audit.

## 18. Stato finale per ambiente e integrazione

| Ambito | Stato | Evidenza sintetica |
|---|---|---|
| LOCAL | PARZIALMENTE VERIFICATO / RISCHIO | codice/schema/versioni verificati, suite ampliata rossa e rischi statici |
| GITHUB | VERIFICATO per ref main | ls-remote = origin/main locale; 4 commit locali non presenti su main remoto; nessun push |
| SPANEL | PENDENTE | dati storici soltanto, nessun accesso |
| PRODUZIONE | PENDENTE | release/config/DB/health/backup non attestati |
| DATABASE | VERIFICATO locale schema / PARZIALE dati / PENDENTE produzione | 58 tabelle,142 migrazioni,11 struttura_id senza FK; nessun audit dati effettuato |
| QUESTURA | PARZIALMENTE VERIFICATO | Safety 14 PASS, nessuna modifica; accettazione reale non dimostrata |
| ISTAT | PARZIALMENTE VERIFICATO / PENDENTE readiness | fixture/XSD/codici, ma codice assegnato/export reale/accettazione mancanti |

Questo Maestro v1.0 registra la baseline documentale, **non certifica produzione** e non implementa il backlog.

## 19. Regole permanenti per sviluppo futuro

Politica di progetto: **LOCAL → GITHUB → SPANEL**. Nessuna modifica diretta di produzione.

Ogni modifica importante deve: (1) analizzare impatto e scope; (2) lavorare localmente; (3) disporre di test riproducibili; (4) proteggere tenancy; (5) proteggere dati reali; (6) verificare regressioni; (7) creare commit identificabile; (8) eseguire push controllato quando autorizzato; (9) deploy controllato quando autorizzato; (10) verificare produzione nel perimetro autorizzato; (11) aggiornare Maestro se cambia architettura, rischio o stato.

Prima di qualunque test rispettare docs/TESTING_ISOLATION.md; vietato bypassare TEST_ISOLATION_REQUIRED, usare DB/server reale o dati reali come fixture. Schema/dati/trasmissioni reali richiedono task esplicita, non autorizzazione implicita dal documento. Per commit verificare diff/check/stat/name-only e diff cached completo, staging selettivo senza untracked estranei. Non confondere build/test con deploy riuscito né HTTP200/simulazione con accettazione ufficiale.

Tutte le attività/documenti nuovi in italiano secondo AGENTS.md e .github/copilot-instructions.md. Mantenere evidenza datata con SHA/versione/ambiente/esito/limiti; declassare dichiarazioni quando le nuove prove non le confermano. Segreti, nominativi, dump ed export reali non entrano nel Maestro o in Git.

## 20. Documentazione: destinazione proposta, nessuna pulizia

Regola generale: Maestro per stato/architettura corrente; documenti operativi per istruzioni dettagliate; report datati come evidenza storica con scope; nessuna cancellazione automatica. Inventario completo in appendice D. Decisioni di archiviazione sono proposte, non azioni eseguite.

## Appendice A — Inventario applicativo completo

VERIFICATO per presenza e metodi/relazioni estratti dai file della baseline. Un metodo pubblico non implica route attiva o ruolo autorizzato; le responsabilità di area sono descritte sopra.

### app/Http/Controllers (60 file)

| File | Metodi pubblici dichiarati (non elenco route) |
|---|---|
| `app/Http/Controllers/Admin/PagamentiController.php` | index, storeAssegnazione, updateAssegnazione, destroyAssegnazione, printAssegnazione |
| `app/Http/Controllers/Admin/ProprietariController.php` | indexProforme, index, create, store, edit, update, disable, destroy, showProforma, createProforma, storeProforma, editProforma, updateProforma, closeProforma, markFatturata, printProforma |
| `app/Http/Controllers/Admin/StruttureController.php` | index, create, store, edit, update, updateServizio, destroy |
| `app/Http/Controllers/ArchivosController.php` | generarArchivoHospedados |
| `app/Http/Controllers/ArrivalsController.php` | index, new, search, store, update, a_schedina, destroy |
| `app/Http/Controllers/Auth/ConfirmPasswordController.php` | __construct |
| `app/Http/Controllers/Auth/ForgotPasswordController.php` | nessuno dichiarato / ereditati |
| `app/Http/Controllers/Auth/LoginController.php` | __construct, username, logout |
| `app/Http/Controllers/Auth/RegisterController.php` | __construct |
| `app/Http/Controllers/Auth/ResetPasswordController.php` | nessuno dichiarato / ereditati |
| `app/Http/Controllers/Auth/VerificationController.php` | __construct |
| `app/Http/Controllers/CalendarioController.php` | index, store, update, updateStatus |
| `app/Http/Controllers/CestinoController.php` | index, destroy, restore |
| `app/Http/Controllers/ComponentiController.php` | __construct, index, new, store, edit, update, destroy |
| `app/Http/Controllers/ComponentiImportController.php` | __construct, prepare, newPrepare, index, newIndex, template, newTemplate, newPreview, preview, confirm, newConfirm |
| `app/Http/Controllers/Controller.php` | nessuno dichiarato / ereditati |
| `app/Http/Controllers/CrmController.php` | index, storeLead, createExampleLead, show, update, storeActivity, storeIndexAgenda, addExampleAgenda, updateActivityStatus |
| `app/Http/Controllers/CustomerController.php` | index, new, store, edit, print, storico, update, destroy |
| `app/Http/Controllers/CustomerExportController.php` | index, destroy, exportCsv |
| `app/Http/Controllers/CustomerImportController.php` | __construct, index, importedIndex, destroySelectedImported, useInSchedina, confirmImported, deleteImported, template, store, show, editRow, updateRow, commit, destroy |
| `app/Http/Controllers/GeoComuneLogoController.php` | index, store, destroy |
| `app/Http/Controllers/GeoController.php` | nazioni, regioni, province, comuni, cap, resolve |
| `app/Http/Controllers/GestioneOperativaController.php` | index, updateProfile, updateMyPassword, storeUtente, updateUtente, resetPassword, storeComanda, markComandaRead, closeComanda |
| `app/Http/Controllers/GroupController.php` | index, store, update, destroy |
| `app/Http/Controllers/HelpCenterController.php` | index, general, admin, print, module, management |
| `app/Http/Controllers/HomeController.php` | __construct, index, root, lang, updateProfile, updatePassword |
| `app/Http/Controllers/IstatTabellaAController.php` | __construct, index, saveControllo, downloadXml, downloadStorico, verifyPeriodo, sendPeriodo, downloadReceipt, printSummary |
| `app/Http/Controllers/LocationController.php` | __construct, provincesByRegion, provincesAll, capByProvince, citiesByProvince |
| `app/Http/Controllers/NotificheController.php` | __construct, index |
| `app/Http/Controllers/PresenzeController.php` | __construct, index, printRiepilogo, printDettaglio |
| `app/Http/Controllers/Proprietario/StruttureController.php` | index |
| `app/Http/Controllers/PublicSiteController.php` | home, page, asset |
| `app/Http/Controllers/QA/DemoMapController.php` | index |
| `app/Http/Controllers/QaController.php` | index, session, accesso, tenancy |
| `app/Http/Controllers/QuesturaExportController.php` | __construct, index, downloadPeriodo, downloadSchedina, verifyPeriodo, downloadOfficialTables, sendPeriodo, downloadStorico, downloadReceipt |
| `app/Http/Controllers/RilasciatoDaController.php` | index, store, update, destroy |
| `app/Http/Controllers/SchedinaController.php` | __construct, index, bozze, new, store, edit, copy, update, destroy, printTassa |
| `app/Http/Controllers/StrutturaController.php` | edit, update, zoneSuggestions |
| `app/Http/Controllers/StrutturaSelezioneController.php` | index, seleziona |
| `app/Http/Controllers/StrutturaUserController.php` | index, create, store, resetPassword |
| `app/Http/Controllers/StruttureController.php` | index |
| `app/Http/Controllers/Superadmin/AmministratoriController.php` | index, create, store, edit, update, disable, destroy, createProforma, storeProforma, showProforma, editProforma, updateProforma, closeProforma, markFatturata, printProforma |
| `app/Http/Controllers/Superadmin/ArticoliController.php` | index, store, update, destroy |
| `app/Http/Controllers/Superadmin/ImpersonazioneController.php` | index, impersona, esci |
| `app/Http/Controllers/Superadmin/PagamentiController.php` | index, printAssegnazione |
| `app/Http/Controllers/Superadmin/ProprietariController.php` | indexProforme, index, create, store, edit, update, disable, destroy, assegnaAdmin, showProforma, createProforma, storeProforma, editProforma, updateProforma, closeProforma, markFatturata, printProforma |
| `app/Http/Controllers/Superadmin/StruttureController.php` | index, create, store, edit, update, updateServizio, destroy |
| `app/Http/Controllers/SupportoController.php` | index, show, store, reply, updateStatus, assign |
| `app/Http/Controllers/TassaDiSoggiornoController.php` | create, edit, update |
| `app/Http/Controllers/TassaEsenzioneController.php` | store, update, destroy |
| `app/Http/Controllers/TassaReportController.php` | __construct, index, controllo, exportCsv, exportControlloCsv, printControllo |
| `app/Http/Controllers/TipoAlloggiatoController.php` | index, create, store, edit, show, update, destroy |
| `app/Http/Controllers/TipoClienteController.php` | index, store, update, destroy |
| `app/Http/Controllers/TipoDocumentiController.php` | index, store, update, destroy |
| `app/Http/Controllers/TipoDocumentoController.php` | index, create, store, edit, update, destroy |
| `app/Http/Controllers/TipoViaController.php` | index, store, update, destroy |
| `app/Http/Controllers/TitleController.php` | index, store, update, destroy |
| `app/Http/Controllers/TypeStreetController.php` | nessuno dichiarato / ereditati |
| `app/Http/Controllers/WebCheckinController.php` | index, create, store, edit, update, destroy, publicShow, publicInvite, publicStoreShort, publicCompletedShort, publicStore, publicCompleted, toSchedina |
| `app/Http/Controllers/WebsiteContactController.php` | store |

### app/Models (55 file)

| File | Tabella esplicita / trait | Relazioni dichiarate |
|---|---|---|
| `app/Models/AdminFatturazione.php` | `admin_fatturazioni` | belongsTo User, belongsTo User, hasMany AdminFatturazioneRiga |
| `app/Models/AdminFatturazioneRiga.php` | `admin_fatturazione_righe` | belongsTo AdminFatturazione, belongsTo Proprietario, belongsTo AdminServizio |
| `app/Models/AdminServizio.php` | `admin_servizi` | belongsTo User, belongsToMany Proprietario |
| `app/Models/Arrivals.php` | `arrivi` | — |
| `app/Models/CalendarioEvento.php` | `calendario_eventi` + AppartieneAStruttura | belongsTo User, belongsTo Struttura, belongsTo User, belongsTo User, belongsTo User |
| `app/Models/CestinoItem.php` | `cestino_items` | — |
| `app/Models/Classificazione.php` | `classificazioni` | belongsToMany TipologiaStruttura |
| `app/Models/Componenti.php` | `componenti` + AppartieneAStruttura | belongsTo Schedina, belongsTo Customers, belongsTo Struttura |
| `app/Models/Comuni.php` | `comuni` | — |
| `app/Models/Concerns/AppartieneAStruttura.php` | `convenzione Eloquent / trait` | — |
| `app/Models/CrmLead.php` | `convenzione Eloquent / trait` | belongsTo User, belongsTo Struttura, belongsTo User, hasMany CrmLeadActivity, hasMany CrmLeadActivity |
| `app/Models/CrmLeadActivity.php` | `convenzione Eloquent / trait` | belongsTo CrmLead, belongsTo User |
| `app/Models/CustomerImportBatch.php` | `convenzione Eloquent / trait` | hasMany CustomerImportRow, belongsTo Struttura, belongsTo Proprietario, belongsTo User |
| `app/Models/CustomerImportRow.php` | `convenzione Eloquent / trait` | belongsTo CustomerImportBatch, belongsTo Customers, belongsTo Customers |
| `app/Models/Customers.php` | `clienti` + AppartieneAStruttura | hasMany Schedina, belongsTo Struttura |
| `app/Models/GeoCap.php` | `geo_cap` | belongsToMany GeoComune, hasMany GeoComuneCap |
| `app/Models/GeoComune.php` | `geo_comuni` | belongsTo GeoProvincia, belongsToMany GeoCap, hasMany GeoComuneCap |
| `app/Models/GeoComuneCap.php` | `geo_comuni_cap` | belongsTo GeoComune, belongsTo GeoCap |
| `app/Models/GeoNazione.php` | `geo_nazioni` | hasMany GeoRegione |
| `app/Models/GeoProvincia.php` | `geo_province` | belongsTo GeoRegione, hasMany GeoComune |
| `app/Models/GeoRegione.php` | `geo_regioni` | belongsTo GeoNazione, hasMany GeoProvincia |
| `app/Models/Gruppo.php` | `gruppi` | belongsTo self, hasMany self |
| `app/Models/ImpersonationLog.php` | `convenzione Eloquent / trait` | — |
| `app/Models/IstatExport.php` | `istat_exports` | — |
| `app/Models/IstatMovimentoGiornaliero.php` | `istat_movimenti_giornalieri` + AppartieneAStruttura | — |
| `app/Models/IstatTransmission.php` | `istat_transmissions` | — |
| `app/Models/LicenzaArticolo.php` | `licenza_articoli` | belongsTo self, hasMany self, hasMany LicenzaAssegnazione |
| `app/Models/LicenzaAssegnazione.php` | `licenza_assegnazioni` | belongsTo LicenzaArticolo, belongsTo Proprietario, belongsTo Struttura, belongsTo User |
| `app/Models/Proprietario.php` | `proprietari` | belongsTo User, hasMany Struttura, hasMany User, hasMany ProprietarioFatturazione, belongsToMany AdminServizio, hasOne User |
| `app/Models/ProprietarioFatturazione.php` | `proprietario_fatturazioni` | belongsTo Proprietario, belongsTo User, hasMany ProprietarioFatturazioneRiga |
| `app/Models/ProprietarioFatturazioneRiga.php` | `proprietario_fatturazione_righe` | belongsTo ProprietarioFatturazione, belongsTo Struttura, belongsTo AdminServizio |
| `app/Models/QuesturaExport.php` | `questura_exports` | — |
| `app/Models/QuesturaTransmission.php` | `questura_transmissions` | — |
| `app/Models/RilasciatoDa.php` | `rilasciato_da` | — |
| `app/Models/Schedina.php` | `schedina` + AppartieneAStruttura | hasMany SchedinaCamera, hasMany Componenti, belongsTo User |
| `app/Models/SchedinaCamera.php` | `schedina_camere` + AppartieneAStruttura | belongsTo Schedina |
| `app/Models/Struttura.php` | `struttura` | belongsTo TipologiaGenerale, belongsTo Proprietario, hasOne User, belongsTo TipologiaStruttura, belongsTo Classificazione |
| `app/Models/StrutturaAccesso.php` | `struttura_accessi` + AppartieneAStruttura | belongsTo User |
| `app/Models/StrutturaAuditLog.php` | `struttura_audit_logs` + AppartieneAStruttura | belongsTo User |
| `app/Models/StrutturaComanda.php` | `struttura_comande` + AppartieneAStruttura | belongsTo User, belongsTo User |
| `app/Models/StrutturaZona.php` | `struttura_zone` | belongsTo Struttura, belongsTo GeoComune |
| `app/Models/SupportTicket.php` | `convenzione Eloquent / trait` + AppartieneAStruttura | belongsTo Struttura, belongsTo User, belongsTo User, hasMany SupportTicketMessage |
| `app/Models/SupportTicketMessage.php` | `convenzione Eloquent / trait` + AppartieneAStruttura | belongsTo SupportTicket, belongsTo User |
| `app/Models/Tassa.php` | `tassa` + AppartieneAStruttura | — |
| `app/Models/TassaDiSoggiorno.php` | `tassa_di_soggiorno` + AppartieneAStruttura | — |
| `app/Models/TassaEsenzione.php` | `tassa_esenzioni` + AppartieneAStruttura | — |
| `app/Models/TipoAlloggiato.php` | `tipo_alloggiato` | — |
| `app/Models/TipoCliente.php` | `tipo_cliente` | — |
| `app/Models/TipoDocumento.php` | `tipo_documento` | — |
| `app/Models/TipoVia.php` | `tipo_via` | — |
| `app/Models/TipologiaGenerale.php` | `tipologie_generali` | hasMany TipologiaStruttura |
| `app/Models/TipologiaStruttura.php` | `tipologie_struttura` | belongsTo TipologiaGenerale, belongsToMany Classificazione |
| `app/Models/Titolo.php` | `titolo` | — |
| `app/Models/User.php` | `convenzione Eloquent / trait` | belongsTo Struttura, belongsTo Proprietario, hasMany Proprietario, hasMany AdminServizio, hasMany AdminFatturazione, hasMany CrmLead, hasMany CrmLead, hasMany CrmLeadActivity |
| `app/Models/WebCheckinRichiesta.php` | `web_checkin_richieste` | belongsTo Schedina, belongsTo Struttura |

### app/Services (19 file)

| File | Metodi pubblici dichiarati (non elenco route) |
|---|---|
| `app/Services/AccessoOperativoService.php` | open, close |
| `app/Services/CestinoService.php` | archiveModel, restoreItem, purgeItem |
| `app/Services/ClientiCsvGeoEnricher.php` | enrich |
| `app/Services/ComponentiImportService.php` | headersTemplate, colonneTemplate, mappingIntestazioni, formatiSupportati, delimitatorePerFormato, nomeFileTemplate, contentTypePerFormato, contenutoTemplateVuoto, previewDaContenuto, analizzaImportazione, preparaConfermaBatch |
| `app/Services/CustomerImportService.php` | templateHeaders, templateExampleRow, createBatchFromUploadedCsv, recomputeRow, commitBatch, confirmImportedRow, completeFromSchedina, updateRowPayload, refreshBatchCounters |
| `app/Services/EsitoTrasmissioneIstat.php` | crea, daHttp, sanifica, storico |
| `app/Services/EsitoTrasmissioneQuestura.php` | crea, sanifica, storico |
| `app/Services/GeoImportService.php` | import |
| `app/Services/IstatCodifiche.php` | normalize, table, country, comune, tipo |
| `app/Services/IstatStoricoValidator.php` | errors |
| `app/Services/IstatTabellaAService.php` | schedinePerPeriodo, dailyRows, analysePeriodo, buildXml, buildSoapEnvelope, filename |
| `app/Services/IstatWebService.php` | __construct, credentialsStatus, verify, send, receipt |
| `app/Services/IstatXmlValidator.php` | document, validate |
| `app/Services/NationService.php` | getAllNations, getAllRegions, getAllProvinces, getAllCities, getAllCap, getCitiesByProvince, getProvincesByRegion, getCapByProvince |
| `app/Services/NotificheService.php` | paginateForUser, topbarForUser |
| `app/Services/PresenzeReportService.php` | schedinePeriodo, riepilogoAnno, dettaglioPeriodo, situazioneOggi, movimentiPeriodo, occupazionePeriodo |
| `app/Services/QuesturaTxtExportService.php` | schedinePerPeriodo, analizzaSchedine, buildTxt, buildTxtPerSchedina, filename, filenamePerSchedina |
| `app/Services/QuesturaWebService.php` | credentialsStatus, verify, send, receipt, downloadReferenceTables |
| `app/Services/TassaDiSoggiornoService.php` | parseDate, diffNotti, diffNottiNelPeriodo, dettaglioSchedina, exportRows |

### app/Http/Middleware (15 file)

| File | Metodi pubblici dichiarati (non elenco route) |
|---|---|
| `app/Http/Middleware/Authenticate.php` | nessuno dichiarato / ereditati |
| `app/Http/Middleware/EncryptCookies.php` | nessuno dichiarato / ereditati |
| `app/Http/Middleware/ImpostaStrutturaCorrente.php` | handle |
| `app/Http/Middleware/Localization.php` | handle |
| `app/Http/Middleware/LogOperativeAudit.php` | handle |
| `app/Http/Middleware/PreventRequestsDuringMaintenance.php` | nessuno dichiarato / ereditati |
| `app/Http/Middleware/QaEnabled.php` | handle |
| `app/Http/Middleware/RedirectIfAuthenticated.php` | handle |
| `app/Http/Middleware/RequireAuthorizedStruttura.php` | handle |
| `app/Http/Middleware/Ruolo.php` | handle |
| `app/Http/Middleware/TrimStrings.php` | nessuno dichiarato / ereditati |
| `app/Http/Middleware/TrustHosts.php` | hosts |
| `app/Http/Middleware/TrustProxies.php` | nessuno dichiarato / ereditati |
| `app/Http/Middleware/VerificaServizioStruttura.php` | handle |
| `app/Http/Middleware/VerifyCsrfToken.php` | nessuno dichiarato / ereditati |

### app/Console/Commands (13 file)

| File | Metodi pubblici dichiarati (non elenco route) |
|---|---|
| `app/Console/Commands/CleanupComponentiOrfaniCommand.php` | handle |
| `app/Console/Commands/ClientiEnrichGeoCsvCommand.php` | handle |
| `app/Console/Commands/DemoSaasCommand.php` | handle |
| `app/Console/Commands/DemoSaasFullCommand.php` | handle |
| `app/Console/Commands/ExportSchedaClienteModule.php` | handle |
| `app/Console/Commands/GeoImportCommand.php` | handle |
| `app/Console/Commands/ImportTipoDocumento.php` | nessuno dichiarato / ereditati |
| `app/Console/Commands/QaCheck.php` | handle |
| `app/Console/Commands/QaRoles.php` | handle |
| `app/Console/Commands/QaTenancy.php` | handle |
| `app/Console/Commands/RenumberClientiCommand.php` | handle |
| `app/Console/Commands/RenumberSchedineCommand.php` | handle |
| `app/Console/Commands/SetupConfigurazioneCommand.php` | handle |

### app/Providers (5 file)

| File | Metodi pubblici dichiarati (non elenco route) |
|---|---|
| `app/Providers/AppServiceProvider.php` | register, boot |
| `app/Providers/AuthServiceProvider.php` | boot |
| `app/Providers/BroadcastServiceProvider.php` | boot |
| `app/Providers/EventServiceProvider.php` | boot |
| `app/Providers/RouteServiceProvider.php` | boot |

### app/Http/Requests (2 file)

| File | Metodi pubblici dichiarati (non elenco route) |
|---|---|
| `app/Http/Requests/StrutturaRequest.php` | authorize, rules, withValidator, messages |
| `app/Http/Requests/TipoDocumentoRequest.php` | authorize, rules |

### Altri componenti applicativi

Supporto: StrutturaAccess/StrutturaCorrente, Anagrafica/EtaOperativa; Componenti/ContrattoImportazioneComponentiV1, DatiComponenteNormalizzati, PianoSyncComponenti, SingleNodeBatchLock, TipoAlloggiatoCatalogo. Eccezioni: Handler e ComponentiImportException. Notifica: Auth/ResetPasswordNotification. Bootstrap/route/provider del framework sono parte della catena, non servizi di dominio aggiuntivi.

## Appendice B — Tabelle effettive, primary key, indici e foreign key

VERIFICATO E5; ogni riga descrive metadati del DB locale, non conteggi né contenuti. `UNIQUE` significa indice univoco, `INDEX` non univoco. Le colonne composite sono nell'ordine dello schema. Lista FK indica colonna→tabella.colonna; regole ON DELETE/UPDATE non rilevate da questo inventario e restano da confrontare nelle migrazioni prima di un intervento.

| Tabella / motore | Primary key | Indici oltre PRIMARY | Foreign key effettive |
|---|---|---|---|
| `admin_fatturazione_righe` / InnoDB | id | INDEX admin_fatturazione_righe_admin_fatturazione_id_foreign(admin_fatturazione_id); INDEX admin_fatturazione_righe_admin_servizio_id_foreign(admin_servizio_id); INDEX admin_fatturazione_righe_proprietario_id_foreign(proprietario_id) | admin_fatturazione_id→admin_fatturazioni.id; admin_servizio_id→admin_servizi.id; proprietario_id→proprietari.id |
| `admin_fatturazioni` / InnoDB | id | INDEX admin_fatturazioni_created_by_foreign(created_by); UNIQUE admin_fatturazioni_numero_unique(user_id,numero) | created_by→users.id; user_id→users.id |
| `admin_servizi` / InnoDB | id | INDEX admin_servizi_user_id_attivo_index(user_id,attivo) | user_id→users.id |
| `admin_servizio_proprietario` / InnoDB | id | UNIQUE admin_servizio_proprietario_unique(admin_servizio_id,proprietario_id,struttura_id); INDEX asp_admin_servizio_id_idx(admin_servizio_id); INDEX asp_proprietario_id_idx(proprietario_id); INDEX asp_struttura_id_idx(struttura_id) | admin_servizio_id→admin_servizi.id; proprietario_id→proprietari.id; struttura_id→struttura.id |
| `arrivi` / InnoDB | id | — | — |
| `calendario_eventi` / InnoDB | id | INDEX calendario_eventi_closed_by_foreign(closed_by); INDEX calendario_eventi_created_by_foreign(created_by); INDEX calendario_eventi_struttura_id_data_evento_index(struttura_id,data_evento); INDEX calendario_eventi_struttura_id_stato_index(struttura_id,stato); INDEX calendario_eventi_struttura_id_tipo_index(struttura_id,tipo); INDEX calendario_eventi_updated_by_foreign(updated_by); INDEX calendario_eventi_user_scope_id_foreign(user_scope_id) | closed_by→users.id; created_by→users.id; struttura_id→struttura.id; updated_by→users.id; user_scope_id→users.id |
| `cestino_items` / InnoDB | id | INDEX cestino_items_circuito_index(circuito); INDEX cestino_items_deleted_at_index(deleted_at); INDEX cestino_items_entity_type_index(entity_type); INDEX cestino_items_original_id_index(original_id); INDEX cestino_items_source_index(source); INDEX cestino_items_struttura_id_index(struttura_id); INDEX cestino_items_user_id_index(user_id) | — |
| `classificazione_tipologia` / InnoDB | id | UNIQUE class_tipologia_unique(classificazione_id,tipologia_struttura_id); INDEX classificazione_tipologia_tipologia_struttura_id_foreign(tipologia_struttura_id) | classificazione_id→classificazioni.id; tipologia_struttura_id→tipologie_struttura.id |
| `classificazioni` / InnoDB | id | UNIQUE classificazioni_nome_unique(nome); UNIQUE classificazioni_tipologia_struttura_id_nome_unique(nome) | — |
| `clienti` / InnoDB | id | INDEX clienti_struttura_id_index(struttura_id); UNIQUE clienti_struttura_numero_cliente_unique(struttura_id,numero_cliente) | struttura_id→struttura.id |
| `componenti` / InnoDB | id | INDEX componenti_customer_id_index(customer_id); INDEX componenti_schedina_id_index(schedina_id); INDEX componenti_struttura_id_index(struttura_id) | customer_id→clienti.id; schedina_id→schedina.id; struttura_id→struttura.id |
| `crm_lead_activities` / InnoDB | id | INDEX crm_lead_activities_crm_lead_id_scheduled_at_index(crm_lead_id,scheduled_at); INDEX crm_lead_activities_direzione_scheduled_at_index(direzione,scheduled_at); INDEX crm_lead_activities_stato_scheduled_at_index(stato,scheduled_at); INDEX crm_lead_activities_tipo_index(tipo); INDEX crm_lead_activities_user_id_foreign(user_id) | crm_lead_id→crm_leads.id; user_id→users.id |
| `crm_leads` / InnoDB | id | INDEX crm_leads_assigned_admin_id_foreign(assigned_admin_id); INDEX crm_leads_created_by_user_id_foreign(created_by_user_id); INDEX crm_leads_fonte_index(fonte); UNIQUE crm_leads_lead_code_unique(lead_code); INDEX crm_leads_prossimo_contatto_at_index(prossimo_contatto_at); INDEX crm_leads_stato_assigned_admin_id_index(stato,assigned_admin_id); INDEX crm_leads_struttura_id_stato_index(struttura_id,stato) | assigned_admin_id→users.id; created_by_user_id→users.id; struttura_id→struttura.id |
| `customer_import_batches` / InnoDB | id | INDEX customer_import_batches_status_index(status); INDEX customer_import_batches_struttura_id_created_at_index(struttura_id,created_at); INDEX customer_import_batches_user_id_created_at_index(user_id,created_at) | — |
| `customer_import_rows` / InnoDB | id | INDEX customer_import_rows_batch_id_row_number_index(batch_id,row_number); INDEX customer_import_rows_batch_id_status_index(batch_id,status); INDEX customer_import_rows_duplicate_customer_id_index(duplicate_customer_id); INDEX customer_import_rows_imported_customer_id_index(imported_customer_id) | — |
| `failed_jobs` / InnoDB | id | UNIQUE failed_jobs_uuid_unique(uuid) | — |
| `geo_cap` / InnoDB | id | UNIQUE geo_cap_cap_unique(cap) | — |
| `geo_comuni` / InnoDB | id | UNIQUE geo_comuni_codice_istat_unique(codice_istat); INDEX geo_comuni_geo_provincia_id_index(geo_provincia_id); INDEX geo_comuni_nome_index(nome) | geo_provincia_id→geo_province.id |
| `geo_comuni_cap` / InnoDB | id | INDEX geo_comuni_cap_geo_cap_id_index(geo_cap_id); UNIQUE geo_comuni_cap_geo_comune_id_geo_cap_id_localita_unique(geo_comune_id,geo_cap_id,localita); INDEX geo_comuni_cap_geo_comune_id_principale_priorita_index(geo_comune_id,principale,priorita) | geo_cap_id→geo_cap.id; geo_comune_id→geo_comuni.id |
| `geo_nazioni` / InnoDB | id | UNIQUE geo_nazioni_codice_iso2_unique(codice_iso2); INDEX geo_nazioni_is_italia_index(is_italia) | — |
| `geo_province` / InnoDB | id | INDEX geo_province_geo_regione_id_index(geo_regione_id); UNIQUE geo_province_sigla_unique(sigla) | geo_regione_id→geo_regioni.id |
| `geo_regioni` / InnoDB | id | UNIQUE geo_regioni_geo_nazione_id_codice_regione_unique(geo_nazione_id,codice_regione); INDEX geo_regioni_geo_nazione_id_index(geo_nazione_id) | geo_nazione_id→geo_nazioni.id |
| `gruppi` / InnoDB | id | INDEX gruppi_parent_id_foreign(parent_id) | parent_id→gruppi.id |
| `impersonation_logs` / InnoDB | id | INDEX impersonation_logs_impersonated_id_foreign(impersonated_id); INDEX impersonation_logs_impersonator_id_impersonated_id_index(impersonator_id,impersonated_id) | impersonated_id→users.id; impersonator_id→users.id |
| `istat_exports` / InnoDB | id | INDEX istat_exports_struttura_id_index(struttura_id); INDEX istat_exports_user_id_index(user_id) | — |
| `istat_movimenti_giornalieri` / InnoDB | id | INDEX istat_movimenti_giornalieri_confermato_da_index(confermato_da); INDEX istat_movimenti_giornalieri_giorno_index(giorno); UNIQUE istat_movimenti_giornalieri_struttura_id_giorno_unique(struttura_id,giorno); INDEX istat_movimenti_giornalieri_struttura_id_index(struttura_id) | — |
| `istat_transmissions` / InnoDB | id | INDEX istat_transmissions_istat_export_id_index(istat_export_id); INDEX istat_transmissions_mode_index(mode); INDEX istat_transmissions_status_index(status); INDEX istat_transmissions_struttura_id_index(struttura_id); INDEX istat_transmissions_user_id_index(user_id) | — |
| `licenza_articoli` / InnoDB | id | INDEX licenza_articoli_codice_index(codice); INDEX licenza_articoli_parent_id_foreign(parent_id) | parent_id→licenza_articoli.id |
| `licenza_assegnazioni` / InnoDB | id | INDEX licenza_assegnazioni_admin_id_foreign(admin_id); INDEX licenza_assegnazioni_articolo_id_foreign(articolo_id); INDEX licenza_assegnazioni_data_scadenza_index(data_scadenza); UNIQUE licenza_assegnazioni_numero_licenza_unique(numero_licenza); INDEX licenza_assegnazioni_proprietario_id_foreign(proprietario_id); INDEX licenza_assegnazioni_stato_pagamento_index(stato_pagamento); INDEX licenza_assegnazioni_struttura_id_foreign(struttura_id) | admin_id→users.id; articolo_id→licenza_articoli.id; proprietario_id→proprietari.id; struttura_id→struttura.id |
| `migrations` / InnoDB | id | — | — |
| `password_resets` / InnoDB | **assente** | INDEX password_resets_email_index(email) | — |
| `personal_access_tokens` / InnoDB | id | UNIQUE personal_access_tokens_token_unique(token); INDEX personal_access_tokens_tokenable_type_tokenable_id_index(tokenable_type,tokenable_id) | — |
| `proprietari` / InnoDB | id | INDEX proprietari_admin_id_attivo_index(admin_id,attivo) | admin_id→users.id |
| `proprietario_fatturazione_righe` / InnoDB | id | INDEX prop_fatt_righe_fatt_fk(proprietario_fatturazione_id); INDEX proprietario_fatturazione_righe_admin_servizio_id_foreign(admin_servizio_id); INDEX proprietario_fatturazione_righe_struttura_id_foreign(struttura_id) | admin_servizio_id→admin_servizi.id; proprietario_fatturazione_id→proprietario_fatturazioni.id; struttura_id→struttura.id |
| `proprietario_fatturazioni` / InnoDB | id | INDEX proprietario_fatturazioni_created_by_foreign(created_by); UNIQUE proprietario_fatturazioni_numero_unique(proprietario_id,numero) | created_by→users.id; proprietario_id→proprietari.id |
| `questura_exports` / InnoDB | id | INDEX questura_exports_struttura_id_index(struttura_id); INDEX questura_exports_user_id_index(user_id) | — |
| `questura_transmissions` / InnoDB | id | INDEX questura_transmissions_mode_index(mode); INDEX questura_transmissions_questura_export_id_index(questura_export_id); INDEX questura_transmissions_scope_type_index(scope_type); INDEX questura_transmissions_status_index(status); INDEX questura_transmissions_struttura_id_index(struttura_id); INDEX questura_transmissions_user_id_index(user_id) | — |
| `rilasciato_da` / InnoDB | id | UNIQUE rilasciato_da_name_unique(name) | — |
| `schedina` / InnoDB | id | INDEX schedina_agganciata_da_index(agganciata_da); INDEX schedina_arrive_index(arrive); INDEX schedina_customer_id_index(customer_id); INDEX schedina_departure_index(departure); INDEX schedina_fonte_prenotazione_id_prenotazione_esterna_index(fonte_prenotazione,id_prenotazione_esterna); INDEX schedina_is_arrive_index(is_arrive); INDEX schedina_last_istat_export_id_index(last_istat_export_id); INDEX schedina_last_istat_transmission_id_index(last_istat_transmission_id); INDEX schedina_last_questura_export_id_index(last_questura_export_id); INDEX schedina_last_questura_transmission_id_index(last_questura_transmission_id); INDEX schedina_struttura_id_index(struttura_id) | agganciata_da→users.id; struttura_id→struttura.id |
| `schedina_camere` / InnoDB | id | INDEX schedina_camere_fonte_camera_camera_esterna_id_index(fonte_camera,camera_esterna_id); INDEX schedina_camere_numero_camera_index(numero_camera); INDEX schedina_camere_schedina_id_index(schedina_id); INDEX schedina_camere_struttura_id_index(struttura_id) | schedina_id→schedina.id; struttura_id→struttura.id |
| `struttura` / InnoDB | id | INDEX struttura_attiva_index(attiva); UNIQUE struttura_cir_unique(cir); INDEX struttura_classificazione_id_foreign(classificazione_id); INDEX struttura_proprietario_id_index(proprietario_id); INDEX struttura_scadenza_servizio_index(scadenza_servizio); INDEX struttura_tipologia_generale_id_foreign(tipologia_generale_id); INDEX struttura_tipologia_struttura_id_foreign(tipologia_struttura_id) | classificazione_id→classificazioni.id; proprietario_id→proprietari.id; tipologia_generale_id→tipologie_generali.id; tipologia_struttura_id→tipologie_struttura.id |
| `struttura_accessi` / InnoDB | id | INDEX struttura_accessi_struttura_id_entrata_at_index(struttura_id,entrata_at); INDEX struttura_accessi_user_id_foreign(user_id) | struttura_id→struttura.id; user_id→users.id |
| `struttura_audit_logs` / InnoDB | id | INDEX struttura_audit_logs_struttura_id_created_at_index(struttura_id,created_at); INDEX struttura_audit_logs_user_id_foreign(user_id) | struttura_id→struttura.id; user_id→users.id |
| `struttura_comande` / InnoDB | id | INDEX struttura_comande_destinatario_id_foreign(destinatario_id); INDEX struttura_comande_mittente_id_foreign(mittente_id); INDEX struttura_comande_struttura_id_stato_index(struttura_id,stato) | destinatario_id→users.id; mittente_id→users.id; struttura_id→struttura.id |
| `struttura_zone` / InnoDB | id | INDEX struttura_zone_geo_comune_id_index(geo_comune_id); INDEX struttura_zone_struttura_id_index(struttura_id); UNIQUE struttura_zone_unique(struttura_id,geo_comune_id,tipo,nome) | — |
| `support_ticket_messages` / InnoDB | id | INDEX support_ticket_messages_author_user_id_index(author_user_id); INDEX support_ticket_messages_struttura_id_index(struttura_id); INDEX support_ticket_messages_support_ticket_id_index(support_ticket_id) | — |
| `support_tickets` / InnoDB | id | INDEX support_tickets_assigned_admin_id_index(assigned_admin_id); INDEX support_tickets_opened_by_user_id_index(opened_by_user_id); INDEX support_tickets_struttura_id_index(struttura_id); UNIQUE support_tickets_ticket_code_unique(ticket_code) | — |
| `tassa_di_soggiorno` / InnoDB | id | INDEX tassa_di_soggiorno_struttura_id_foreign(struttura_id) | struttura_id→struttura.id |
| `tassa_esenzioni` / InnoDB | id | UNIQUE tassa_esenzioni_struttura_id_codice_unique(struttura_id,codice); INDEX tassa_esenzioni_struttura_id_ordine_index(struttura_id,ordine) | struttura_id→struttura.id |
| `tipo_alloggiato` / InnoDB | id | UNIQUE tipo_alloggiato_codice_unique(codice) | — |
| `tipo_cliente` / InnoDB | id | UNIQUE tipo_cliente_codice_unique(codice) | — |
| `tipo_documento` / InnoDB | id | UNIQUE tipo_documento_codice_unique(codice) | — |
| `tipo_via` / InnoDB | id | — | — |
| `tipologie_generali` / InnoDB | id | UNIQUE tipologie_generali_nome_unique(nome) | — |
| `tipologie_struttura` / InnoDB | id | UNIQUE tipologie_struttura_tipologia_generale_id_nome_unique(tipologia_generale_id,nome) | tipologia_generale_id→tipologie_generali.id |
| `titolo` / InnoDB | id | UNIQUE titolo_nome_unique(nome) | — |
| `users` / InnoDB | id | UNIQUE users_email_unique(email); INDEX users_proprietario_id_index(proprietario_id); INDEX users_struttura_id_index(struttura_id); INDEX users_username_index(username) | proprietario_id→proprietari.id; struttura_id→struttura.id |
| `web_checkin_richieste` / InnoDB | id | UNIQUE web_checkin_richieste_codice_unique(codice); INDEX web_checkin_richieste_struttura_id_numero_prenotazione_index(struttura_id,numero_prenotazione); INDEX web_checkin_richieste_struttura_id_stato_index(struttura_id,stato); UNIQUE web_checkin_richieste_token_unique(token) | — |

## Appendice C — Inventario completo testing

VERIFICATO per presenza; stato per singolo file dove noto. I file di supporto/fixture non costituiscono test autonomi. Nessun conteggio di casi dedotto soltanto da `test_` viene presentato come esecuzione.

| File | Categoria / stato in questa task |
|---|---|
| `tests/CreatesApplication.php` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/Deployment/check_rocky10.py` | Deployment/supporto: discovery 87,84PASS/3skip; check_rocky10 non eseguito su host |
| `tests/Deployment/rocky_isolation.py` | Deployment/supporto: discovery 87,84PASS/3skip; check_rocky10 non eseguito su host |
| `tests/Deployment/test_deploy_guards.py` | Deployment/supporto: discovery 87,84PASS/3skip; check_rocky10 non eseguito su host |
| `tests/Deployment/test_rocky_isolation.py` | Deployment/supporto: discovery 87,84PASS/3skip; check_rocky10 non eseguito su host |
| `tests/Feature/AccessTest.php` | Feature non eseguito: non selezionato / dataset legacy (vedi §15) |
| `tests/Feature/CustomerImportedFiltersDeleteTest.php` | Feature E6: 2 error fixture |
| `tests/Feature/ExampleTest.php` | Feature non eseguito: non selezionato / dataset legacy (vedi §15) |
| `tests/Feature/GeoComuneLogoTest.php` | Feature E6 eseguito senza failure/error; ultime tre anche E7 PASS |
| `tests/Feature/IstatConformitaTest.php` | Feature E6 eseguito senza failure/error; ultime tre anche E7 PASS |
| `tests/Feature/IstatTransmissionSecurityTest.php` | Feature E6 eseguito senza failure/error; ultime tre anche E7 PASS |
| `tests/Feature/SchedinaStoreTest.php` | Feature E6: 1 error User11 |
| `tests/Feature/SmokePagesQaTest.php` | Feature non eseguito: non selezionato / dataset legacy (vedi §15) |
| `tests/Feature/StrutturaAuthorizationTest.php` | Feature E6 eseguito senza failure/error; ultime tre anche E7 PASS |
| `tests/Feature/TenancyTest.php` | Feature non eseguito: non selezionato / dataset legacy (vedi §15) |
| `tests/Feature/TopbarRenderingTest.php` | Feature E6 eseguito senza failure/error; ultime tre anche E7 PASS |
| `tests/Feature/clienti.playwright.spec.js` | Playwright non eseguito: fixture/account demo non predisposti |
| `tests/Feature/configurazioni.playwright.spec.js` | Playwright non eseguito: fixture/account demo non predisposti |
| `tests/Feature/geo-logo.playwright.spec.js` | Playwright E7: 4 PASS/1 failure |
| `tests/Feature/struttura.playwright.spec.js` | Playwright non eseguito: fixture/account demo non predisposti |
| `tests/Feature/tassa-di-soggiorno.playwright.spec.js` | Playwright non eseguito: fixture/account demo non predisposti |
| `tests/Fixtures/istat/famiglia.xml` | Fixture sintetica XML, non dato reale |
| `tests/Isolation/browser-identity.php` | Isolamento/fixture/supporto runtime; non ogni file è suite |
| `tests/Isolation/console.php` | Isolamento/fixture/supporto runtime; non ogni file è suite |
| `tests/Isolation/prova-istat.php` | Isolamento/fixture/supporto runtime; non ogni file è suite |
| `tests/Isolation/router.php` | Isolamento/fixture/supporto runtime; non ogni file è suite |
| `tests/Isolation/run.py` | Isolamento/fixture/supporto runtime; non ogni file è suite |
| `tests/Isolation/seed-geo.php` | Isolamento/fixture/supporto runtime; non ogni file è suite |
| `tests/Isolation/test_guards.py` | Isolamento/fixture/supporto runtime; non ogni file è suite |
| `tests/Isolation/verify.php` | Isolamento/fixture/supporto runtime; non ogni file è suite |
| `tests/Safety/geo_logo_security.php` | Safety eseguito |
| `tests/Safety/istat_security.php` | Safety eseguito |
| `tests/Safety/questura_security.php` | Safety eseguito |
| `tests/Safety/tavola_a_audit.py` | Audit CSV standalone: non ripetuto, precedente analisi storica |
| `tests/Support/GeoLogoFixtures.php` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/Support/IsolatedConnectionFactory.php` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/Support/IsolationServiceProvider.php` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/Support/StrutturaFixtures.php` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/Support/TestingEnvironment.php` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/Support/playwright-isolation.js` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/Support/playwright.js` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/TestCase.php` | Bootstrap/supporto guardrail test, non suite autonoma |
| `tests/Unit/ComponentiImportFormTest.php` | Unit PHPUnit: 1 failure/1 error, untracked preesistente |
| `tests/Unit/ComponentiImportReviewStatusTest.php` | script PHP autonomo PASS |
| `tests/Unit/ComponentiImportServiceTest.php` | Unit PHPUnit: 14 failure |
| `tests/Unit/ContrattoImportazioneComponentiV1Test.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/CustomerImportServiceTest.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/DatiComponenteNormalizzatiTest.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/EtaOperativaTest.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/ExampleTest.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/PianoSyncComponentiTest.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/SingleNodeBatchLockTest.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/TassaDiSoggiornoServiceTest.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/TipoAlloggiatoCatalogoTest.php` | Unit PHPUnit eseguito, senza failure/error nel run E6 |
| `tests/Unit/componenti-import-submit.test.mjs` | Unit JS: 3 fallimenti, untracked preesistente |
| `tests/bootstrap.php` | Bootstrap/supporto guardrail test, non suite autonoma |

## Appendice D — Inventario documentale consolidato e destinazione

Inventario iniziale: **30 file** sotto `docs/`: 28 Markdown, un HTML e un PDF. Dopo la pulizia autorizzata restano **28 file**: 26 Markdown, un HTML e un PDF. I due documenti eliminati sono mantenuti nella tabella come voci storiche. Classificazione di destinazione distinta dagli stati funzionali. Rapporti specialistici conservano dettagli non riprodotti integralmente: una sintesi nel Maestro non rende il rapporto ridondante. Fonti esterne non ricontrollate durante questa consolidazione.

| File | Classificazione | Informazione utile / decisione |
|---|---|---|
| `docs/AUDIT_DEPLOY_SPANEL.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/AUDIT_SPANEL_DEPENDENCIES.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/DEMO-MAP.md` | LEGACY | Mappa dimostrativa datata; non inventario tenant corrente né fixture da riusare nelle nuove prove. |
| `docs/DEPLOY-CPANEL-CHECKLIST-RAPIDA.md` | LEGACY | Riepilogo collegato al runbook cPanel, sovrapposto ma non candidato finché archivio/ruolo non deciso. |
| `docs/DEPLOY-CPANEL-SCHEDINEDINOTIFICA.md` | LEGACY | Conservare runbook backup/upload/docroot/import e percorsi storici; non prova ambiente SPanel attuale e non autorizza operazioni. |
| `docs/DEPLOY_SPANEL.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/PRODUCT-SUMMARY.md` | CONSOLIDATO / ELIMINATO | Sintesi prodotto confluisce in identità/inventario; trasmissioni e assenza camere non trattate come fatti. Eliminazione autorizzata nella chiusura baseline. |
| `docs/PRODUCTION-CHECKLIST.md` | CONSOLIDATO / ELIMINATO | Checklist backup/Git/restore/deploy/ruoli/commerciale/log/queue consolidata; force push e operazioni implicite respinte. Backup storico release-20260323-100302 non attesta validità oggi. Eliminazione autorizzata nella chiusura baseline. |
| `docs/QA.md` | LEGACY | Conservare dettagli QA, toggle e matrice; test diretti/seed demo superati dal launcher isolato. Non usare utenti/password demo sul DB reale. |
| `docs/ROCKY_PROCESS_ISOLATION.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/TESTING_ISOLATION.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/UI-STANDARD.md` | CONSERVARE | Regole data-ui/initOnce/wrapper utili; esempi geo-select legacy da leggere con contratto GeoItalia corrente. |
| `docs/VERBALE-DI-CONSEGNA-2026-03-18.html` | CONSERVARE | Formato alternativo della stessa consegna; sovrapposizione, non equivalenza integrale attestata. |
| `docs/VERBALE-DI-CONSEGNA-2026-03-18.md` | LEGACY | Conservare consegna con riserve Questura/ISTAT; conclusioni di chiusura non prevalgono sulla baseline corrente. |
| `docs/VERBALE-DI-CONSEGNA-2026-03-18.pdf` | CONSERVARE | Artefatto di consegna; equivalenza/QA visiva non verificata, nessuna cancellazione proposta. |
| `docs/audit-clienti-2026-10-04.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/chiusura-geo-comuni-logo-2026-10-05.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/chiusura-istat-2026-10-05.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/chiusura-pulizia-clienti-2026-10-04.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/chiusura-workflow-clienti-2026-10-04.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/design/PALETTE-TANGO-PROPOSTA.md` | CONSERVARE | Proposta grafica distinta da architettura/stato funzionale; nessuna adozione implicita. |
| `docs/eliminazione-individuale-export-clienti-2026-10-04.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/geo-italia.md` | CONSERVARE | Contratto endpoint/Select2/resolve/props/esportazione componente; titolo chiuso non prova test corrente. |
| `docs/geo.md` | CONSERVARE | Core GEO e import/CAP; sovrapposizione parziale con geo-italia, non duplicato integrale. |
| `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md` | CONSERVARE | Unica fonte maestra ufficiale; inclusa nel commit di baseline documentale. |
| `docs/mysql_herd_vscode.md` | CONSERVARE | Guida locale operativa; comandi migrazione/seed/restore non sono protocollo test e richiedono scope specifico. |
| `docs/scheda-cliente-module.md` | CONSERVARE | Contratto modulo/esportazione ZIP/manifest; comando mutante non eseguito. |
| `docs/separazione-superadmin-admin-2026-10-04.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |
| `docs/struttura-tipologia.md` | CONSERVARE | Gerarchia/pivot/FK/testi legacy e validazioni; fonti datate non attestano conformità normativa corrente. |
| `docs/verifica-finale-clienti-2026-10-04.md` | CONSERVARE | Rapporto/specifica specialistica datata: evidenze e limiti richiamati nelle sezioni del Maestro; non fonte maestra alternativa. |

**DOCUMENTI CONSOLIDATI:** `PRODUCT-SUMMARY.md` (scopo prodotto, anagrafiche, soggiorni, tassa e integrazioni) e `PRODUCTION-CHECKLIST.md` (categorie di preflight, backup/restore, ruoli, commerciale e post-deploy). Sintesi dei rapporti Clienti/Geo/ISTAT/deploy già presente nelle sezioni 6–18 e appendice F: i rapporti restano conservati per l’evidenza dettagliata. Il Prompt Maestro del 05/10 è integrato nel quadro ufficiale iniziale, non copiato come ulteriore maestro.

**DOCUMENTI DUPLICATI:** nessun duplicato integrale dimostrato. Verbale MD/HTML/PDF sono formati della stessa consegna da conservare; geo.md/geo-italia.md e checklist/manuali deploy hanno sovrapposizioni parziali e dettagli propri. Nessuna equivalenza binaria o informativa completa inventata.

**DOCUMENTI ELIMINATI — lista esatta, autorizzata nella chiusura baseline:**

1. `docs/PRODUCT-SUMMARY.md`: sintesi generalista consolidata; affermazioni su invii compiuti e assenza camere corrette nel quadro ufficiale e registro contraddizioni.
2. `docs/PRODUCTION-CHECKLIST.md`: categorie utili consolidate nel protocollo deploy; conserva solo riferimenti storici a backup/data release e istruzioni superate per Git/migrazioni/cache. Il riferimento storico backup `backups/release-20260323-100302` è qui preservato senza validarvi contenuto, checksum o restore.

Eliminazione eseguita dopo autorizzazione e verifica dei riferimenti: il solo link operativo era README → PRODUCT-SUMMARY, sostituito con README → Maestro e verificato sul filesystem. Gli altri riferimenti sono citazioni storiche nel Maestro, conservate come tali. Nessuna informazione utile esclusiva rimossa: sintesi prodotto e checklist sono consolidate; istruzioni superate non trasferite come comandi da eseguire. Non eliminare specifiche Questura/ISTAT, runbook dettagliati, audit o consegne. Nessuna proposta di cancellazione per altri file.

**Informazioni uniche conservate per riferimento:** switch QA e matrice demo (QA/DEMO-MAP, LEGACY); percorsi e backup cPanel (runbook LEGACY); contratto Select2/resolve e componenti GEO; tipologie/pivot; esportazione modulo Cliente; dettagli deploy lock/503/recovery e sandbox Rocky; provenienza/hash e binding ISTAT in `reference/istat/ross1000-er/README.md`. Le specifiche rimangono subordinate per stato globale ma autorevoli nel proprio scope tecnico, se confermate dal codice/evidenza.

**Nuove contraddizioni documentali evidenziate:** Production Checklist propone force push e migrazioni generiche mentre il protocollo permanente le vieta fuori scope; UI-STANDARD propone geo-select mentre i documenti GEO lo indicano legacy; il verbale marzo parla di software chiuso mentre il triage ottobre documenta bug reali. I numeri browser discordanti sono resoconti distinti, non suite oggi verde. Le contraddizioni e le informazioni utili dei due documenti rimossi restano registrate qui; gli altri documenti storici sono preservati.

## Appendice E — Controlli finali e consegna

HEAD e riferimenti/staging invariati. Hash di tutti i file preesistenti censiti confrontati con la baseline: unica differenza permanente attesa e verificata `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`. Nessun file applicativo/test/schema/config corretto per far passare le prove. Preesistenti untracked preservati. File runtime sotto `/private/tmp/schedine-test-*` rimossi dagli avviatori anche dopo gli errori; log diagnostici di questa audit sono temporanei e non inclusi in Git.

**COMMIT = NO; PUSH = NO; DEPLOY = NO; SPANEL = nessun accesso; PRODUZIONE MODIFICATA = NO; SCHEMA REALE MODIFICATO = NO; DATI REALI MODIFICATI = NO; TRASMISSIONI QUESTURA/ISTAT = NO.**

Inventario documentale v1.0 registrato, stato **BASELINE IN AUDIT** mantenuto per evidenze parziali, rischi statici e suite non integralmente verdi. Nessuna certificazione di produzione o readiness reale viene emessa da questa documentazione.


## Appendice F — Validazione v1.0: classificazione individuale dei test falliti (05/10/2026)

Questa verifica aggiunge evidenze senza correggere codice, test o schema. Stato mantenuto: **BASELINE IN AUDIT**. I risultati storici dell’audit completo (232 test/868 asserzioni, 15 failure e 4 error) restano invariati. La riproduzione mirata delle quattro classi interessate ha prodotto **81 test/285 asserzioni, gli stessi 15 failure e 4 error**, con una deprecazione separata dal conteggio. JavaScript: stessi **3 fallimenti**. Playwright: **4 pass/1 fail**, stesso caso riprodotto.

### Isolamento ed evidenze

Letti guardrail e `docs/TESTING_ISOLATION.md`; eseguito `python3 -B tests/Isolation/test_guards.py`: **5 PASS**. Ogni runtime successivo ha attestato identità PID/DB/HTTP e riconnessione, rifiutato **23 override prima delle migrazioni** e verificato GEO immutabile. Usato esclusivamente `tests/Isolation/run.py`, MySQL temporaneo su loopback e checkout isolato; nessun DB di sviluppo/produzione.

Database temporanei: `test_geo_4bcefa1752bce18a0cbb934f351c9725`, `test_geo_aee95eed77981a85297bf733774dc13b`, `test_geo_93cabbffc69c50cd76312241151b9a61`, `test_geo_9775c84ce6be1a379d7d042b14b3b315`. Directory runtime rispettive: `/private/tmp/schedine-test-uje8q895`, `schedine-test-x96dwlnv`, `schedine-test-b44qn2jh`, `schedine-test-ygdhlba6`. Avviatori hanno fermato processi e rimosso automaticamente risorse temporanee al termine, anche dopo gli errori. Log conservati sotto `/private/tmp/classificazione-*.log`.

JavaScript eseguito nel checkout/env del launcher dopo attestazione PHP, con copia byte-identica del test verificata tramite hash. Diagnostiche osservazionali invocate in memoria prima del comando PHPUnit originale; nessuna modifica al launcher o alle prove. Esperimenti date/struttura hanno prodotto solo preview e payload in memoria, senza persistere componenti. Fixture e migrazioni del sistema di test operano esclusivamente nel DB temporaneo, come previsto dall’infrastruttura. Nessuna trasmissione Questura/ISTAT, accesso SPanel o operazione Git di scrittura.

### Tabella dei 23 casi

A = BUG REALE; B = TEST OBSOLETO; C = TEST INCORRETTO; D = PROBLEMA DI AMBIENTE/INFRASTRUTTURA DI TEST; E = INDETERMINATO. ID assegnati nell’ordine della riproduzione mirata; non costituiscono ordine del log storico.

- **S**: `tests/Unit/ComponentiImportServiceTest.php`.
- **F**: `tests/Unit/ComponentiImportFormTest.php`.
- **C**: `tests/Feature/CustomerImportedFiltersDeleteTest.php`.
- **T**: `tests/Feature/SchedinaStoreTest.php`.
- **P**: `tests/Feature/geo-logo.playwright.spec.js`.
- **J**: `tests/Unit/componenti-import-submit.test.mjs`.

| ID | TIPO TEST | ARCHIVO | TEST | MODULO | RISULTATO (atteso → ottenuto) | CAUSA | CLASSIFICAZIONE | RISCHIO | AZIONE RACCOMANDATA |
|---|---|---|---|---|---|---|---|---|---|
| F01 | PHPUnit failure | S | test_riga_malformata_tra_righe_valide_non_contamina_le_successive | Componenti Import | VALIDO → COMPLETO; ulteriori righe hanno 18/19 celle | Contratto review cambiato; fixture storiche incompatibili con header di 15 colonne | B | P2 | Riallineare stati e larghezza fixture, preservando verifica di allineamento |
| F02 | PHPUnit failure | S | test_data_giorno_mese_anno_valida_viene_normalizzata | Componenti Import | VALIDO → COMPLETO; data valida normalizzata | Asserzione sullo stato storico | B | P2 | Aggiornare contratto atteso mantenendo controllo della data |
| F03 | PHPUnit failure | S | test_data_invalida_genera_errore | Componenti Import | ERRORE → COMPLETO; 12-31-1980 confermabile come 1982-07-12 | Errori del parser data non partecipano al blocco; Carbon normalizza permissivamente | A | P1 | Bloccare date invalide in preview e conferma; validare senza normalizzazione permissiva |
| F04 | PHPUnit failure | S | test_data_impossibile_genera_errore | Componenti Import | ERRORE → COMPLETO; 31/02/1980 confermabile come 1980-03-02 | Stesso difetto F03, data impossibile ammessa | A | P1 | Stessa correzione F03 e regressione sulle date impossibili |
| F05 | PHPUnit failure | S | test_italia_valida_e_estero_valido | Componenti Import | VALIDO → COMPLETO; fixture estera di 18 colonne | Stato storico e fixture estera non aggiornata a 15 colonne | B | P2 | Aggiornare contratto e fixture Italia/estero |
| F06 | PHPUnit failure | S | test_italia_senza_provincia_o_comune_restituisce_errore | Componenti Import | ERRORE → DA_COMPLETARE con errori sui campi mancanti | Workflow legittimo di completamento anagrafica | B | P2 | Verificare DA_COMPLETARE e blocchi previsti dal nuovo workflow |
| F07 | PHPUnit failure | S | test_nazione_sconosciuta_genera_errore | Componenti Import | Atteso errore country_nac; ottenuto _struct | 18 celle contro header 15: validazione GEO mai raggiunta | C | P2 | Costruire fixture canonica 15 colonne con nazione sconosciuta |
| F08 | PHPUnit failure | S | test_xlsx_compilato_valido_produce_preview_valida | Componenti Import XLSX | VALIDO → COMPLETO; conteggi e nome validi | Asserzione sullo stato precedente | B | P2 | Aggiornare stato senza indebolire controlli XLSX |
| F09 | PHPUnit failure | S | test_xlsx_righe_successive_restano_allineate_anche_con_riga_precedente_malformata | Componenti Import XLSX | Riga successiva VALIDO → COMPLETO; allineamento preservato | Asserzione sullo stato precedente | B | P2 | Aggiornare stato e conservare controlli strutturali/allineamento |
| F10 | PHPUnit failure | S | test_xlsx_riga_invalida_diventa_errore_riga | Componenti Import XLSX | Data impossibile attesa ERRORE, ottenuta COMPLETO | Stesso difetto F03/F04 attraverso XLSX | A | P1 | Correggere validazione comune e mantenere regressione XLSX |
| F11 | PHPUnit failure | S | test_xlsx_celle_vuote_vengono_gestite_come_pipeline_corrente | Componenti Import XLSX | VALIDO → COMPLETO; righe vuote ignorate e nomi corretti | Asserzione sullo stato storico | B | P2 | Aggiornare stato mantenendo controlli sulle celle vuote |
| F12 | PHPUnit failure | S | test_xlsx_data_excel_nativa_viene_convertita_senza_cambiare_contratto | Componenti Import XLSX | VALIDO → COMPLETO; asserzioni successive sulla data non raggiunte | Prima asserzione usa stato storico; non dimostrato un difetto di conversione | B | P2 | Aggiornare stato e rieseguire tutte le asserzioni sulla data Excel |
| F13 | PHPUnit failure | S | test_piu_righe_valide_e_mix_valide_invalide | Componenti Import | Prima asserzione VALIDO → COMPLETO; anche riga con data invalida COMPLETO, conteggi 2 valide/0 errori | Oltre allo stato storico, stesso bug reale date F03 | A | P1 | Correggere blocco date e conteggi; aggiornare solo gli stati legittimamente cambiati |
| F14 | PHPUnit failure | S | test_csv_con_tab_e_estensione_csv_viene_rilevato_automaticamente | Componenti Import CSV | VALIDO → COMPLETO; rilevazione tab e nomi corretti | Asserzione sullo stato storico | B | P2 | Aggiornare stato mantenendo rilevazione delimitatore |
| F15 | PHPUnit failure | F | test_preview_conferma_e_ritorno_al_form_senza_persistenza | Form Componenti Import | 1 riga valida attesa, 0 ottenute | Fixture 18 colonne con header 15: _struct prima di conferma/rendering | C | P2 | Correggere fixture canonica e rieseguire intero percorso |
| E01 | PHPUnit error | F | test_salvataggio_componenti_nuova_schedina_senza_persistenza | Salvataggio schedina/form | Salvataggio atteso; BadMethodCallException Request::validate | Application parziale non registra macro del FoundationServiceProvider; kernel reale la registra | D | P2 | Usare harness con kernel/provider corretti nel runtime isolato |
| E02 | PHPUnit error | C | test_imported_index_filters_pending_rows_by_structure_and_filter_fields | Customer Import/multistruttura | Filtri attesi; INSERT fixture fallisce su tipologia_struttura obbligatoria | Struttura creata col solo nome, fixture non conforme allo schema | C | P1 | Fixture sintetiche complete; verificare poi filtri e isolamento tenant |
| E03 | PHPUnit error | C | test_delete_selected_rows_only_removes_pending_rows_in_current_structure | Customer Import/eliminazione | Eliminazione limitata al tenant attesa; INSERT fixture fallisce | Stessa fixture Struttura incompleta di E02 | C | P1 | Fixture complete; verificare cancellazione e separazione tenant |
| E04 | PHPUnit error | T | test_schedina_store_minimal_valid_payload_redirects_to_list_and_creates_record | Schedina | Creazione attesa; User::findOrFail(11) senza record | Dipendenza da ID/dataset storico non creato dal test | C | P2 | Creare utente, struttura e riferimenti sintetici espliciti |
| PW01 | Playwright | P | isolamento browser: blocca URL esterni e redirect, inclusa catena locale | Guardrail browser | Attesa Response 403; richiesta rifiutata con aborted e 403 nel call log | Proxy blocca a livello trasporto; asserzione presume una Response disponibile | C | P1 | Verificare rifiuto HTTP o trasporto e zero richieste arrivate; completare catene redirect senza indebolire guardrail |
| JS01 | JavaScript | J | importazione mantiene POST anche quando il gestore UI disabilita il submitter | Form import | POST atteso; TypeError submitter.matches | Doppio DOM privo di matches presente sul pulsante reale | C | P2 | Contesto DOM completo o prova browser isolata |
| JS02 | JavaScript | J | un salvataggio successivo ripristina PUT anche dopo una navigazione annullata | Form import | PUT atteso; TypeError submitter.matches | Stesso doppio DOM incompleto JS01 | C | P2 | Contesto DOM completo e sequenza annullamento/salvataggio |
| JS03 | JavaScript | J | invio implicito conserva PUT e il form nuovo funziona senza hidden | Form import | PUT conservato, poi ReferenceError componentIndexInput | VM estrae listener senza variabili della closure, dichiarate nel Blade reale | C | P2 | Fornire componentIndexInput/componentIdInput o usare DOM reale; verificare anche ramo senza hidden |

### Impatto individuale e limiti probatori

Tutti i 23 casi sono riproducibili nell’ambiente isolato corrente. **Produzione: non verificata per ciascuno dei 23 casi**; nessun accesso autorizzato o eseguito. Un bug nel codice corrente può interessare produzione se la versione corrispondente è attiva, ma non si attesta tale condizione. “Non dimostrato” non equivale a “assenza garantita”. P1 assegnato ai gap di copertura non implica vulnerabilità dimostrata.

| ID | Funzionalità reale | Tenancy | Sicurezza | Integrità dati | Problema in | Obsoleto per cambio legittimo? |
|---|---|---|---|---|---|---|
| F01 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F02 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F03 | Sì: conferma ammette date invalide | Bypass non dimostrato | Bypass non dimostrato | Sì: date errate nel payload confermabile | Codice; F13 contiene anche asserzione storica | No per il difetto date |
| F04 | Sì: conferma ammette date invalide | Bypass non dimostrato | Bypass non dimostrato | Sì: date errate nel payload confermabile | Codice; F13 contiene anche asserzione storica | No per il difetto date |
| F05 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F06 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F07 | Percorso atteso non completato dal test | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata dalla causa del test | Fixture/doppio/contesto del test | No: precondizioni o simulazione errate |
| F08 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F09 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F10 | Sì: conferma ammette date invalide | Bypass non dimostrato | Bypass non dimostrato | Sì: date errate nel payload confermabile | Codice; F13 contiene anche asserzione storica | No per il difetto date |
| F11 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F12 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F13 | Sì: conferma ammette date invalide | Bypass non dimostrato | Bypass non dimostrato | Sì: date errate nel payload confermabile | Codice; F13 contiene anche asserzione storica | No per il difetto date |
| F14 | Nessun difetto dimostrato dalla causa del fallimento | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata da questa asserzione | Contratto/fixture del test | Sì: stati review; F01/F05 anche fixture non riallineate |
| F15 | Percorso atteso non completato dal test | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata dalla causa del test | Fixture/doppio/contesto del test | No: precondizioni o simulazione errate |
| E01 | Salvataggio non raggiunto | Non verificata dal caso | Non verificata dal caso | Persistenza non raggiunta | Harness/provider del test | No |
| E02 | Non verificata: arresto prima del controller | Copertura mancante, non violazione provata | Non dimostrato | Eliminazione/filtri non verificati | Fixture del test | No: schema obbligatorio preesistente |
| E03 | Non verificata: arresto prima del controller | Copertura mancante, non violazione provata | Non dimostrato | Eliminazione/filtri non verificati | Fixture del test | No: schema obbligatorio preesistente |
| E04 | Percorso atteso non completato dal test | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata dalla causa del test | Fixture/doppio/contesto del test | No: precondizioni o simulazione errate |
| PW01 | Blocco trasporto osservato; catene successive non raggiunte | Non pertinente al guasto | Blocco osservato; acceptance completa pendente | Non pertinente | Asserzione del test | Non dimostrato |
| JS01 | Percorso atteso non completato dal test | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata dalla causa del test | Fixture/doppio/contesto del test | No: precondizioni o simulazione errate |
| JS02 | Percorso atteso non completato dal test | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata dalla causa del test | Fixture/doppio/contesto del test | No: precondizioni o simulazione errate |
| JS03 | Percorso atteso non completato dal test | Bypass non dimostrato | Bypass non dimostrato | Nessuna alterazione provata dalla causa del test | Fixture/doppio/contesto del test | No: precondizioni o simulazione errate |

### Bug reale sulle date: F03, F04, F10, F13

`ComponentiImportService::normalizzaDataGiornoMeseAnno` rileva correttamente formato invalido/data impossibile, ma `analizzaImportazione` sceglie lo stato considerando gli errori di validazione dei campi e non tutti gli errori del parser. `classificaRiga` verifica la presenza della data, non la sua validità: le righe diventano `COMPLETO`. `preparaConfermaBatch` esclude soltanto `NON_IMPORTABILE`. Nel payload, `DatiComponenteNormalizzati::normalizzaData` usa Carbon senza controllo warning/roundtrip, trasformando `12-31-1980` in `1982-07-12` e `31/02/1980` in `1980-03-02`. Dimostrati `valid_count=1` e payload confermabile, senza scritture DB. Il controller di conferma passa i payload a `Componenti::create` senza ulteriore validazione delle date. Possibili conseguenze su anagrafica, età, tassa e dati esportati; nessuna trasmissione eseguita.

Confronto osservazionale: servizio da `c3b4977^` caricato in memoria con nome classe distinto, stesso input/helpers correnti e runtime attestato. Prima: date invalide `ERRORE`, zero payload confermabili. Dopo: `COMPLETO`, un payload. Diff/blame di `c3b4977323c4b781d90fac885f8bb515e763339b` documentano il cambio di stati e filtro di conferma. **Causalità provata al livello del servizio, non ricostruzione completa dell’intera applicazione storica.** Il parser permissivo era preesistente; il nuovo gating lo espone. Non attribuita causalità ai cambi di calcolo età. F13 è A nonostante la prima asserzione obsoleta: l’esperimento prova anche la seconda riga invalida erroneamente completa e conteggi 2/0.

### S1 — Difetto aggiuntivo strutturale, fuori dai 23 casi: A / P1

Fixture di 18 celle con header canonico di 15: preview `ERRORE`, `_struct`, dati vuoti, ma `righe_in_errore=0`. La conferma produce **valid_count=1, invalid_count=0**, payload con nome, cognome, sesso e data nulli e `_review_status=ERRORE`. Il filtro esclude solo `NON_IMPORTABILE`, quindi ammette anche il vecchio stato strutturale `ERRORE`; i contatori usano i nuovi stati e ignorano `ERRORE`. Non eseguita persistenza. Il controller esistente passa il payload a creazione senza rivalidazione; in un batch misto una riga completa abilita il pulsante di conferma della UI. Rischio di componenti vuoti/incompleti; nessun bypass tenant o accessi provato.

Il difetto è coerente con il cambio di filtro/stati in `c3b4977`; non è la causa dell’asserzione su `country_nac` in F07 né della fixture di F15, che restano C. La diagnostica con **fixture canonica a 15 colonne e nazione Atlantide** restituisce correttamente `NON_IMPORTABILE`, errore `country_nac`, `valid_count=0`: il controllo GEO non è dimostrato difettoso. Azione raccomandata: esclusione esplicita delle righe strutturalmente invalide, conteggi coerenti, validazione anche in conferma e regressioni sui batch misti. Nessuna correzione applicata.

### Provenienza e regressioni

- Componenti Import: cambio legittimo da `VALIDO`/`ERRORE` ai quattro stati review in `c3b4977`, con fixture parzialmente aggiornate. Legittima l’obsolescenza B, non giustifica ammissione di date invalide o righe strutturali.
- Customer Import/multistruttura: E02/E03 non raggiungono filtri/autorizzazione/eliminazione. `tipologia_struttura` obbligatoria nella migrazione già presente in baseline `4d1f28b`, antecedente al test introdotto in `4b42ac4`. La fixture User omette anche `avatar` obbligatorio: possibile ulteriore arresto dopo la prima correzione, non eseguita qui. Nessuna regressione tenant attribuita senza esecuzione del percorso.
- E04 assume utente ID 11 senza crearlo: contratto storico di dati, incompatibile con test autosufficiente. Non usare dati reali per soddisfarlo.
- E01: `Request::validate` registrato dal `FoundationServiceProvider` nell’applicazione reale, assente nell’Application parziale del test. Nessun difetto della validazione runtime reale dimostrato.
- JavaScript: il listener contiene `submitter.matches` e usa `componentIndexInput/componentIdInput`; il Blade dichiara entrambe le variabili nella closure. Le aggiunte del percorso componente singolo sono rintracciabili in `1ab34de5`, ma il test VM untracked non fornisce metodi/bindings: nessun difetto DOM reale provato. Non attribuita causa alla topbar.
- Playwright: guardrail rifiuta l’origine vietata e il client restituisce `aborted`; il test presume un oggetto Response. Le verifiche successive di redirect e contatori non sono raggiunte. Nessuna whitelist ampliata, proxy indebolito o origine esterna contattata.
- Nessuna relazione causale dimostrata per questi guasti con Cestino, GEO core, Questura, ISTAT, autorizzazioni, topbar o calcolo età. Le date errate possono influire sul calcolo età/esportazioni come conseguenza, non come origine dimostrata.

### Conteggi, priorità e decisione

Sui **23 casi originali**: **A 4; B 9; C 9; D 1; E 0**. I quattro casi A sono manifestazioni di **un difetto comune sulle date**. Compreso S1, sono stati identificati **due difetti tecnici reali distinti**, non cinque. Nessun P0 dimostrato. P1 reali: date e S1 strutturale. P1 di copertura: E02/E03 (tenant/eliminazione) e PW01 (acceptance isolamento completa). Tutti gli altri casi P2; nessun P3 assegnato.

Ordine consigliato, da autorizzare in una fase di correzione separata:

1. Correggere gating/validazione date e righe strutturali, controlli di conferma e contatori; regressioni sintetiche CSV/XLSX/batch misti.
2. Rendere eseguibili E02/E03 e completare acceptance PW01, preservando i guardrail; verificare realmente tenancy e cancellazione.
3. Riallineare contratti e fixture B; correggere fixture F07/F15/E04 e harness E01.
4. Completare contesto DOM dei test JS e verificare i percorsi reali nel browser isolato.
5. Rieseguire suite completa e regressioni pertinenti nell’infrastruttura esistente; aggiornare stato solo in base ai risultati.

**Sicuro proseguire la documentazione della baseline: SÌ**, mantenendo espliciti debiti e limiti. **Chiudere Maestro v1.0 come validazione funzionale completata/readiness: NO**: raccomandata correzione dei due difetti P1 e chiusura dei gap di copertura prima della chiusura validata. La diagnosi supera la precedente lettura generica dei guasti come semplice disallineamento dei test; non certifica produzione.

**CODICE MODIFICATO = NO; TEST MODIFICATI = NO; SCHEMA MODIFICATO = NO; DATI REALI MODIFICATI = NO; PRODUZIONE MODIFICATA = NO; COMMIT = NO; PUSH = NO; DEPLOY = NO; SPANEL = NO; TRASMISSIONI ESTERNE = NO.** Unica modifica permanente: questa appendice del Maestro.


## Appendice G — Correzione minima P1 Componenti, 2026-10-05

Stato permanente: **BASELINE IN AUDIT**. Base Git: `0205b1fa2d5ae2713b2f7d0248b99eb175a9fe95`, branch main, origin/main uguale, ahead/behind 0/0 e worktree inizialmente pulito. Modifiche locali non committate. Questa evidenza successiva aggiorna soltanto P1.1/P1.2; i risultati e la classificazione storici dell’appendice F restano tali.

### Percorso e riproduzione prima della correzione

`ComponentiImportService::analizzaImportazione`: parsing CSV/TXT/XLSX → mapping del contratto V1 → validazione stretta `normalizzaDataGiornoMeseAnno` → normalizzazione/classificazione → preview. L’errore del parser data veniva aggiunto agli errori ma ignorato dal gating dello stato. `preparaConfermaBatch` rianalizza gli input raw e prima escludeva soltanto NON_IMPORTABILE. `buildPersistableComponentPayload` richiamava un normalizzatore permissivo delle date. Il controller usa i payload restituiti nel percorso di creazione transazionale dei componenti; nessuna scrittura applicativa è stata eseguita da queste nuove prove.

Nuova suite indipendente: `tests/Unit/ComponentiImportP1Test.php`, fixture canoniche a 15 colonne, nomi sintetici e resolver GEO in memoria (Francia); nessun dato reale o vecchio harness usato. Comando prima del fix:

```sh
python3 -B tests/Isolation/run.py --phpunit tests/Unit/ComponentiImportP1Test.php
```

Risultato: **5 test / 11 asserzioni / 3 failure**, senza errori di harness. Riproduzione P1-A: input `31/02/2026`, preview COMPLETO, valid_count=1, payload `2026-03-03`. Riproduzione P1-B: 16 celle contro 15 intestazioni, preview ERRORE, valid_count=1, payload anagrafico vuoto. Batch misto: tre payload invece del solo payload valido. Le prove del workflow review/completamento e del rifiuto di contesti estranei passavano già prima del fix. Entrambi i difetti riprodotti prima di modificare il servizio.

### Correzione circoscritta e prova successiva

Unico file applicativo modificato: `app/Services/ComponentiImportService.php`.

- Errore del parser stretto → NON_IMPORTABILE, campo date_nac, conferma falsa e valore originale conservato; nessuna conversione silenziosa.
- Conferma mediante allowlist COMPLETO, DA_VERIFICARE, DA_COMPLETARE. Preservato il workflow legittimo di completamento; ERRORE e NON_IMPORTABILE esclusi.
- ERRORE incluso nei conteggi degli errori di preview.
- Payload usa la data ISO già validata dalla rianalisi, senza richiamare il normalizzatore permissivo. Nessuna modifica generale alla gestione date.

```sh
python3 -B tests/Isolation/run.py --phpunit tests/Unit/ComponentiImportP1Test.php tests/Unit/ContrattoImportazioneComponentiV1Test.php tests/Unit/SingleNodeBatchLockTest.php
```

Risultato: **17 test / 67 asserzioni / PASS**. Verificati: `31/02/2026`, `29/02/2025`, formato non ammesso `12-31-1980` esclusi; `29/02/2024` → `2024-02-29` esatto; riga strutturale esclusa; batch misto un solo payload; stati review/completamento preservati; rifiuto di user_id, struttura_id e schedina_id estranei. Regressioni contratto V1 e lock superate.

### Attestazione, risorse e limiti

Guardrail prima delle modifiche: `python3 -B tests/Isolation/test_guards.py`, **5 PASS**. Runtime prima del fix: `/private/tmp/schedine-test-vs9_avlq`, DB `test_geo_7fdac164b37b9760f156b13cdf0a5ef4`, MySQL PID 31473/porta 52497, HTTP PID 31478/porta 52498. Runtime dopo il fix: `/private/tmp/schedine-test-isdoda34`, DB `test_geo_9612c5538c294cb9be10168606dd0a23`, MySQL PID 31651/porta 52552, HTTP PID 31656/porta 52553. Entrambi: attestazione identità processi/DB/HTTP, riconnessione e **23 rifiuti di override PASS**, build e controllo GEO immutabile PASS. Risorse fermate e directory temporanee rimosse dal launcher.

Prove sul servizio reale e sul payload, senza persistenza end-to-end o browser: non dimostrano l’intero controller/UI né la matrice completa tenant. Playwright e suite completa non eseguiti; debiti storici restano aperti. I due test diagnostici precedenti sono invariati rispetto a HEAD. GEO core, infrastruttura, schema/configurazione e dati reali invariati; nessun accesso produzione, trasmissione, commit, push o deploy. Le migrazioni originali sono eseguite soltanto dal launcher nei nuovi database temporanei previsti dal protocollo isolato.

### Allineamento preview/conferma — nuove evidenze 2026-10-05

Stato mantenuto **BASELINE IN AUDIT**; branch `main`, HEAD `0205b1fa2d5ae2713b2f7d0248b99eb175a9fe95`. Autorizzazione esplicita limitata alle migrazioni originali eseguite dal runtime isolato: nessuna migrazione su database reali, modifica delle migrazioni/configurazioni permanenti o operazione Git di pubblicazione.

Preflight in sola lettura su `run.py`, `console.php`, `TestingEnvironment.php` e protocollo isolamento: copia senza `.env*`/cache, ambiente non ereditato, nuova istanza MySQL `--no-defaults` con datadir temporaneo, utente limitato al database effimero; attestazione effettiva di datadir/UUID/porta/database e configurazione DB protetta prima delle migrazioni. Il primo tentativo nel sandbox è stato bloccato al bind loopback (`PermissionError`), prima di creare risorse o migrare; il successivo avvio con permessi estesi ha eseguito esclusivamente il runtime autorizzato.

Riproduzione prima del nuovo fix, conservando i due fix P1 già presenti:

```sh
python3 -B tests/Isolation/test_guards.py
python3 -B tests/Isolation/run.py --phpunit tests/Unit/ComponentiImportP1Test.php
```

Guardrail: **5 test PASS**. PHPUnit: **6 test / 37 asserzioni / 1 failure**, nessun errore di harness. Una sola riga DA_COMPLETARE produceva `confirmable_rows=0`, `valid_count=1`, un payload. I cinque test P1 preesistenti passavano. Runtime `/private/tmp/schedine-test-on89fdos`, database `test_geo_dca09e12ef290db1fbb743ef1e4fdc9f`, MySQL PID 36050/porta 53021, HTTP PID 36056/porta 53022.

Modifica applicativa aggiuntiva minima: includere DA_COMPLETARE nel conteggio `righe_valide` del servizio. Entrambi i percorsi preview del controller assegnano tale valore a `confirmable_rows`; il Blade usa lo stesso valore per il numero annunciato e l'abilitazione del pulsante. Nessuna modifica a controller/Blade. `righe_in_errore` mantiene il significato preesistente di righe che richiedono intervento, inclusa DA_COMPLETARE: non è il complemento delle righe confermabili. Allowlist di conferma e protezione date P1 preservate.

Regressione aggiunta alla suite P1: confronto esplicito conteggio preview/conferma/payload per sola DA_COMPLETARE e batch misto COMPLETO, DA_VERIFICARE, DA_COMPLETARE, NON_IMPORTABILE, ERRORE.

```sh
php -l app/Services/ComponentiImportService.php
php -l tests/Unit/ComponentiImportP1Test.php
python3 -B tests/Isolation/run.py --phpunit tests/Unit/ComponentiImportP1Test.php tests/Unit/ContrattoImportazioneComponentiV1Test.php tests/Unit/SingleNodeBatchLockTest.php
```

Sintassi: **PASS**. PHPUnit dopo il fix: **18 test / 71 asserzioni / PASS**. Sola DA_COMPLETARE: **1 = 1 = 1**; batch misto: **3 = 3 = 3**, ossia `preview confirmable_rows == valid_count == payload count`. Confermati i tre stati ammissibili; ERRORE e NON_IMPORTABILE esclusi, date impossibili/formato errato senza payload, data bisestile valida conservata in ISO. Rifiuti di user_id/struttura_id/schedina_id estranei, contratto V1 e lock superati. Runtime `/private/tmp/schedine-test-pzcw6579`, database `test_geo_de816a47377b308620a652293e619c6c`, MySQL PID 36280/porta 53070, HTTP PID 36285/porta 53071.

Entrambi i runtime: **PASS** identità processi/DB/HTTP, riconnessione e 23 rifiuti di override prima delle migrazioni; build e controllo GEO immutabile **PASS**; processi fermati e directory rimosse dal launcher. Prove sovrapposte non sommate ai risultati precedenti. Controller/Blade verificati in sola lettura; nessuna prova browser o persistenza end-to-end, nessuna certificazione della matrice tenant completa. Suite completa e debiti storici dell'appendice F restano fuori da queste nuove evidenze.

Git: staging vuoto; soltanto servizio, Maestro e nuovo test P1 modificati/non versionati. `git diff --check` e controllo cached superati; test diagnostici precedenti, runtime/guardrail e migration files invariati rispetto a HEAD. Nessun fetch, commit, push, deploy, accesso produzione o trasmissione esterna. Modificati codice/test/documentazione; configurazione e schema/dati persistenti non modificati; migrazioni e risorse solo temporanee autorizzate. Esito nello scope del servizio: **VERIFICATO — PRONTO PER REVISIONE PRE-COMMIT**; prossimo passo: revisione del diff senza commit automatico.


## Appendice H — Questura / Alloggiati Web, audit e correzioni 2026-10-06

**STORICO della prima task:** policy e verdetti sulla retention sono superati dall’appendice I; le prove e i fallimenti qui documentati restano evidenze storiche.

**QUESTURA — PARZIALE; BASELINE IN AUDIT invariato.** Rapporto subordinato completo: [Questura, 6 ottobre 2026](../questura-audit-2026-10-06.md). Scope autorizzato dalla task: Questura, credenziali Struttura, sole interazioni Questura del Cestino, nuove migrazioni, fixture e documentazione. Nessuna modifica al dominio o al normale flusso Schedina, ai GEO globali o a ISTAT. Rischio ALTO per segreti, trasmissioni e dati personali, circoscritto tramite isolamento e divieto operativo.

Git verificato: branch `main`, HEAD e riferimento `origin/main` dopo fetch `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0. Modifiche locali Questura P1 preesistenti preservate e completate; migration storica ripristinata a HEAD come autorizzato. Staging vuoto; nessun commit/push/deploy. Il confronto remoto non prova lo stato di produzione.

Evidenze locali: cast Laravel encrypted e hidden per Password/WSKEY; segreti assenti da DOM, flash, risultato SOAP e snapshot/restore Cestino Struttura. Aggiornamento vuoto conserva il ciphertext esatto. UI Questura Struttura ridotta a Utente, Password e WSKEY; codici/PUK conservati come legacy senza input; toggle simulazione rimosso senza modificare il valore DB. Nuova migration con preflight raw, TEXT e conversione legacy idempotente; involucro riconoscibile indecifrabile blocca, nessun audit di credenziali reali eseguito.

Boundary TXT separata: 168 byte nel sottoinsieme ASCII di UTF-8, CRLF intermedio, ordine capo/componenti deterministico, validazione rigorosa di date/dimensioni/codici, mapping ai cataloghi ufficiali pubblici acquisiti il 06/10/2026 e hash verificati. Non equiparare ID GEO/ISTAT a codici Alloggiati. Accenti comuni normalizzati senza dipendenza dalla locale, caratteri non supportati respinti. Nessuna attestazione universale dei nomi Unicode. Due TXT accettati indicati dalla task non reperibili nei percorsi /mnt/data su Mac: confronto BLOCCATO in attesa di percorso locale, nessun dato personale copiato o trasmesso.

Contratto SOAP corretto e provato con doubles e serializer PHP offline sul WSDL locale: GenerateToken / Authentication_Test / Test / Send / Ricevuta, booleani tipizzati, token con scadenza, ArrayOfString, base64Binary. Nessun SoapClient con trasporto reale nei test. Fail-closed non-production e configurazione disabled di default. Esiti Test e Send distinti, accepted sempre false; timeout/risposta ambigua dopo Send uncertain, parziale separato, nessun retry automatico e nessun contatore di invio confermato incrementato.

Nuovi storici: snapshot byte-esatto e hash, file privati, download con integrità senza rigenerazione, tentativo prima della rete, eventi append, deduplicazione Send identico per struttura/trasporto/hash. Ricevuta giornaliera privata per struttura/data con hash e download separato; non prova del singolo payload. Protezioni Eloquent e tenant testate, POST/CSRF per azioni mutanti, storico paginato. Persistono limiti reali: hash legacy non backfilled e download 409, crash dopo invio può lasciare in_progress, payload sovrapposti non deduplicati, storage non WORM, PDF verificato solo con controlli minimi e senza firma, recupero file orfani pendente. Due nuove migrazioni applicate solo al DB effimero; down non distruttivi, nessuna migration storica modificata nel diff finale.

**CONTRADDIZIONE sulla conservazione:** la [nota del Garante collegata al comunicato 29/04/2026](https://garanteprivacy.it/web/guest/home/docweb/-/docweb-display/docweb/10244289) richiama cancellazione dei dati digitali trasmessi non appena generata la ricevuta e conservazione quinquennale della ricevuta. Lo storico integrale permanente richiesto non può essere dichiarato conforme. Nessuna cancellazione automatica introdotta o effettuata; attivazione reale BLOCCATA finché non viene definita e implementata una gestione conforme e autorizzata della conservazione.

Prove finali 06/10/2026 su modifiche locali riferite al suddetto HEAD: comando completo e selezione nel rapporto, launcher `tests/Isolation/run.py` con inclusione esplicita limitata dei nuovi sorgenti senza staging. Runtime `/private/tmp/schedine-test-vk6_prvs`, DB `test_geo_ed43b8b74d382bafe9bd57617ce81815`, MySQL PID 7366/porta 54867, HTTP PID 7371/porta 54868; fixture sintetiche e trasporto sostituito. **68 test / 337 asserzioni PASS**, identità/riconnessione/23 rifiuti override PASS, build asset/GEO immutabile PASS, runtime fermato e rimosso. Guardrail 5 PASS; sicurezza SOAP in memoria 14 PASS; sintassi 25 file PHP PASS; hash riferimenti/migrazioni storiche/diff whitespace PASS. Inclusione .env respinta prima del runtime. Fallimenti intermedi e correzioni conservati nel rapporto; prove sovrapposte non sommate.

Regressione circoscritta: create/update Schedina singolo/famiglia/gruppo, autorizzazioni Struttura, PianoSyncComponenti e ComponentiImport P1 nella selezione. Nessuna regressione dimostrata; suite completa e acceptance browser non eseguite. Nessuna credenziale reale, DB reale, SOAP operativo, upload o trasmissione. Solo fonti pubbliche GET consultate.

Verdetti: TXT manuale, WS Test, WS Send e Ricevuta **NON PRONTI per prova reale**; storici TXT/WS **INCOMPLETI** rispetto a retention e recupero/legacy; sicurezza credenziali verificata sulle fixture, **NON RISOLTA sul reale** in assenza di audit/migration autorizzati. Prossima prova operativa richiede distinta autorizzazione dopo soluzione retention, confronto TXT accettati, revisione/backup/APP_KEY e rollout, riconciliazione, verifica PDF e matrice ruoli/UI. Nessuna promozione a readiness o produzione.


## Appendice I — Chiusura retention Questura e preparazione locale, 2026-10-06

**STORICO della seconda task:** preparazione locale e prove conservate; il criterio e il verdetto della chiusura finale sono quelli della successiva appendice J.

**BASELINE IN AUDIT invariato; Questura generale PARZIALE.** Nello scope delle prove locali: **TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA**, senza accettazione reale. La nuova task autorizza cancellazione/minimizzazione delle sole copie Questura dopo ricevuta, una nuova migration, servizio/comando/test e documentazione. Il precedente requisito di storico permanente del payload è **SUPERATO**, non una base giuridica. Rapporto corrente e comandi completi: [chiusura retention](../questura-audit-2026-10-06.md), aggiornamento iniziale del documento; prima task conservata sotto come storico.

Fonti riverificate solo via GET pubblico: [Garante Doc-Web 10244289, nota collegata al 29/04/2026](https://garanteprivacy.it/web/guest/home/docweb/-/docweb-display/docweb/10244289), [DM 07/01/2013](https://www.gazzettaufficiale.it/eli/id/2013/01/17/13A00360/sg), [DM 16/09/2021 art. 1 lettera e / art. 4-bis comma 2](https://www.gazzettaufficiale.it/atto/serie_generale/caricaArticoloDefault/originario?atto.codiceRedazionale=21A06000&atto.dataPubblicazioneGazzetta=2021-10-14&atto.tipoProvvedimento=DECRETO). Copie digitali trasmesse da cancellare alla ricevuta, ricevuta cinque anni; durata ministeriale distinta. Il testo non definisce un diverso campo temporale iniziale: scelta applicativa prudenziale e dichiarata `retained_until = acquired_at + 5 anni di calendario`, senza overflow del 29 febbraio, non arrivo ospite e non 365×5. Acquisizione certa non anticipa generazione; scelta del software, non prescrizione testuale sul dies a quo. Ricevute legacy senza data certa non backfilled arbitrariamente.

Architettura corrente: **payload Questura temporaneo → ricevuta → cancellazione/minimizzazione → ricevuta cinque anni**. `QuesturaRetentionService` separato, verifica tenant/data/hash/PDF e preflight dei path; finalizza tutti i Send sent/live certi della giornata, copia identica TXT e Test/verify del medesimo hash. Uncertain, partial e in_progress preservano il payload finché non esiste riconciliazione esplicita dell’intero elenco. Nessun retry cieco. Manuale: download non prova trasmissione, POST con giornata e attestazione dell’operatore, ricevuta ufficiale del medesimo account/giornata prima della finalizzazione; un semplice PDF caricato non è prova. Ricevuta giornaliera non è ID remoto né prova per singola riga.

Effetti: unlink effettivo e verificato dei TXT privati selezionati; path, payload/base64, result, ID schedine/componenti, periodo di soggiorno e testi remoti NULL; filename tecnico, eventi personali minimizzati, evento di finalizzazione tecnico. Rimangono struttura/utente operativo/data tecnica/modalità/stato/hash/bytes/quantità/ricevuta e timestamp. Eloquent CRUD resta immutabile, query builder riservato alla minimizzazione autorizzata. Idempotenza e recupero file mancante dopo crash provati. Errore filesystem non marca cancellazione completata; dopo unlink e fallimento DB un retry può completare la minimizzazione residua. Transazioni brevi per singolo invio, giornata riprendibile; nessuna falsa atomicità DB/filesystem. Percorsi solo tenant Questura, traversal e symlink respinti; ricevuta esclusa dalla cancellazione.

Ricevuta separata con acquired_at/retained_until/purged_at, metadati e file privato; UI mostra acquisizione/scadenza, GET storico senza rete, 410 per payload finalizzato. Comando `questura:ricevute-scadute STRUTTURA_ID --at=YYYY-MM-DD` in **sola lettura**, selezione deterministica per tenant; nessuna cancellazione automatica o schedulazione, purged_at futuro. Download PDF protetto dall’integrità; controlli minimi PDF e origine SOAP tipizzata, nessuna verifica di firma o accettazione reale attestata.

**Backup:** unlink e minimizzazione del DB attivo non cancellano backup, snapshot/repliche storiche o blocchi SSD/journal. Prima dell’uso reale serve configurazione operativa coerente di retention/accesso/ripristino, evitando reintroduzione di payload eliminati; nessun backup reale letto o modificato. Serve acquisizione tempestiva della ricevuta disponibile: il software non conosce la sua generazione remota prima del recupero. Copie diverse e invii sovrapposti restano associati alla propria riconciliazione; nessuna idempotenza remota inventata.

Modifiche di questa fase: nuovo servizio retention, comando scadenze, test retention e involucro controlli isolati; aggiornati controller/route/vista Questura, cast modelli archivi/ricevuta e double sicurezza per nuova dipendenza. Credenziali encrypted/hidden/DOM vuoto/Cestino/fail-safe APP_KEY e guardrail fail-closed conservati e regressi. UI Struttura ancora solo Utente, Password, WSKEY. Dominio/lifecycle/schema/controller Schedina e dati GEO/ISTAT invariati. **Una sola nuova migration** `2026_10_06_120000_add_questura_retention.php`, oltre alle due della prima task; nessuna storica modificata, nessun rollout reale.

Prova finale su HEAD di riferimento `3eb559cfea9fed6a20e7949037f1d5a0236695be` + modifiche locali: **83 test / 443 asserzioni PASS**, comprendendo tutti i precedenti 68/337, nuovi casi A–M e altri controlli. **5 guardie PASS e 14 controlli sicurezza/SOAP PASS**, dentro il checkout temporaneo tramite `questura-checks.php`; i soli processi figli delle prove negative senza identità ISOLATED_* per richiedere il rifiuto, nessuna modifica alle guardie. Runtime `/private/tmp/schedine-test-dg9l__vv`, DB `test_geo_dcf0e58db26683d41b31355f7beb2158`, MySQL PID 17235/porta 59258, HTTP PID 17240/porta 59259; identità/riconnessione/23 rifiuti override, build asset/GEO immutabile PASS; risorse fermate/rimosse. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Fallimenti intermedi: nullable date export, tipo response PDF, identità ereditata nelle prove negative e dipendenza mancante nel double; tutti preservati nel rapporto, non riclassificati né sommati ai PASS. Regresso create/update singolo/famiglia/gruppo, PianoSyncComponenti e ComponentiImport P1 PASS nella selezione; nessuna regressione dimostrata, suite globale e browser non eseguiti.

Git: branch main, HEAD e riferimento locale origin/main uguali al suddetto SHA, ahead/behind 0/0; nessun nuovo fetch, riferimento del fetch precedente. Worktree locale pertinente, staging vuoto, nuovi file untracked intenzionali. Nessun git add/commit/push/deploy o accesso a DB reale; migrazioni solo MySQL effimero. Solo GET documentali, nessun GenerateToken/Authentication_Test/Test/Send/Ricevuta operativo e nessun upload reale.

Verdetti circoscritti: TXT manuale, WS Test, WS Send e Ricevuta **TECNICAMENTE PRONTI PER PROVA REALE CONTROLLATA**; retention **CONFORME ALLA REGOLA IMPLEMENTATA** sulle copie attive e associazioni/riconciliazioni provate; credenziali **RISOLTE nei percorsi/fixture**, audit/migrazione reale ancora prerequisito. TXT **CONFORME ALLA SPECIFICA UFFICIALE NELLE PROVE LOCALI**, accettazione reale NON VERIFICATA; confronto con file accettati rinviato a task separata e non blocco di questa correzione. Prova futura distinta e autorizzata: revisione/backup/APP_KEY, rollout e audit legacy, policy infrastrutturale backup e recupero tempestivo ricevute, configurazione credenziali/TLS, autenticazione/Test e Send separati, ricezione PDF/firma e accettazione ente. Non attivare il trasporto o promuovere il progetto alla produzione sulla sola evidenza locale.

Controlli conclusivi appendice I: sintassi 30 file PHP PASS, hash riferimenti ufficiali PASS, migration storiche e guardie confrontate byte-per-byte con HEAD e inalterate, diff whitespace PASS, scope dei 42 file locali pertinente, staging vuoto. La statistica diff tracked comprende 19 file; 23 nuovi file restano untracked senza staging. Nessun dato personale o segreto reale introdotto; presenti solo fixture esplicitamente sintetiche e cataloghi pubblici.


## Appendice J — Chiusura tecnica finale Questura, 2026-10-06

**Il blocco per TXT mancanti e il relativo verdetto sono superati dalla prosecuzione in appendice K; prove e correzioni restano evidenze valide.**

**BASELINE IN AUDIT invariato. QUESTURA PARZIALE — CHIUSURA FINALE BLOCCATA SUL CONFRONTO TXT.** La terza task rende obbligatorio il confronto empirico con due TXT accettati: la preparazione locale dell'appendice I non autorizza il verdetto finale richiesto. Rapporto subordinato completo, 26 sezioni e comando: [audit Questura corrente](../questura-audit-2026-10-06.md), sezione iniziale; le due fasi precedenti restano storiche.

**TXT ACCETTATI NON DISPONIBILI — SERVE PERCORSO LOCALE READ-ONLY.** Richiesti i percorsi completi, non ricevuti. Nessun file reale copiato, mostrato, trasmesso o usato come fixture; nessun confronto empirico attestato. Manuale WS e guida ufficiale riaperti via GET: confermati 168 caratteri, offset e CRLF intermedio. Nota Garante riaperta e regola retention confermata. WSDL locale ufficiale con hash verificato; tentativo di nuova apertura documentale non riuscito, nessuna nuova acquisizione inventata. Contratto XML formale e serializer offline prevalgono sulla capitalizzazione della tabella descrittiva del manuale.

Scope autorizzato: chiusura failure path Questura, prove sintetiche e documentazione; rischio ALTO. Preflight con nuovo fetch origin riuscito: branch main, HEAD/origin/main `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0; 42 file locali iniziali pertinenti preservati, staging vuoto. Nessun add/commit/push/deploy, cambio branch o operazione Git distruttiva.

Difetto riprodotto prima del fix: disk double scrive PDF parziale e restituisce false, file orfano senza record. **17 test/112 asserzioni/1 failure**. Fix minimo: put dentro il blocco di compensazione, verifica presenza/hash prima dell'INSERT, verifica delete dopo errore/collisione. Errore DB normale compensa il PDF; se delete fallisce, 503 senza falsa acquisizione e orfano rilevabile. Nuovo comando **`questura:audit-archivi STRUTTURA_ID`** esclusivamente read-only: integrità TXT/ricevute, hash legacy mancanti/diversi, file mancanti e PDF senza record, senza contenuti personali. Byte legacy mancanti non ricevono hash inventati; download senza hash resta 409. Nessun backfill o cleanup automatico.

Secondo difetto RED dimostrato nella revisione finale: evento `created` fallisce dopo INSERT, compensazione elimina PDF ma record resta. **21 test/137 asserzioni/1 failure**, runtime `/private/tmp/schedine-test-f94i3qwq`, DB `test_geo_5b5d91e27a920fa59dcc2fc8cf278111`, MySQL PID27232/porta61892, HTTP PID27237/porta61893; risorse rimosse. Creazione ricevuta ora in transazione breve: rollback dell'INSERT prima della compensazione, incluso errore successivo all'inserimento. Nessuna transazione SOAP e nessuna falsa atomicità DB/filesystem.

Recupero operativo esplicito: in_progress dopo crash successivo a Send conserva snapshot/evento e blocca il secondo Send identico; verificare esito presso l'ente, acquisire ricevuta stessa struttura/giornata e riconciliare l'intero elenco con operatore prima della finalizzazione. Nessuna inferenza automatica dalla ricevuta giornaliera. Per orfano dopo interruzione/compensazione impossibile: audit autorizzato, ripristino storage, confronto riservato DB/file, riacquisizione Ricevuta senza Send e rimozione separatamente autorizzata della sola copia senza riferimenti; nessun PDF arbitrario promosso a ufficiale. DB/filesystem non dichiarati atomici.

Nuove prove integrate: tre cicli sintetici successo/timeout/crash DB dopo Send con doubles ufficiali, elenco/snapshot/hash prima del SOAP e nessuna transazione applicativa durante SOAP, Test senza Send/ricevuta/cancellazione, secondo POST bloccato, ricevuta del giorno successivo e download byte-identico, riconciliazione incerti/in_progress, minimizzazione effettiva e Cliente PMS/Schedina integralmente invariati. Matrice invalidi prima del trasporto, cross-tenant, scadenza normale/bisestile e limiti esatti, autenticazione e manutenzione. Data impossibile fornita in memoria perché la colonna DATE la rifiuta già; validazione/controller/mapping reali, nessuna modifica schema.

**PASS finale: 94 test / 622 asserzioni**, **5 guardie PASS**, **14 controlli SOAP/sicurezza PASS**, nello stesso runtime isolato. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Runtime `/private/tmp/schedine-test-nc16nkjw`, DB `test_geo_4688dcc983bba0532095d925df46f447`, MySQL PID27916/porta62061, HTTP PID27921/porta62062. Identità processi/DB/HTTP, riconnessione, 23 rifiuti di override prima delle migrazioni, build asset e GEO immutabile PASS. Processi fermati e risorse rimosse. Sintassi **33 file PHP PASS**; hash cataloghi/WSDL, migrazioni storiche e guardie byte-identiche a HEAD, diff whitespace PASS. Prove sovrapposte non sommate ai risultati delle fasi precedenti.

Fallimenti intermedi preservati: 90/565 con tre confronti di fixture Cliente non riletta dal DB; 92/599 con data impossibile respinta dall'INSERT fixture. Ulteriore 93/607 con una failure per sostituzione della sorgente successiva alla costruzione del controller; fixture iniettata prima della prima richiesta. Corretti i soli test, asserzioni integrali preservate e nessuna guardia indebolita. Il difetto della scrittura parziale invece è applicativo e corretto nel servizio. Nessuna suite globale/browser, firma PDF o accettazione reale attestata.

Modifiche della terza task: servizio retention, nuovo comando audit, due suite Questura e i due documenti. Totale 43 file locali pertinenti: 19 tracked modificati e 24 nuovi, staging vuoto. Nessuna nuova migration della terza task; tre aggiuntive precedenti migrate solo nei DB effimeri, storiche byte-identiche a HEAD. Nessuna modifica a dominio/lifecycle/schema Schedina o Cliente, GEO globale, ISTAT o infrastruttura permanente. File attivi/DB finalizzati minimizzati, ricevuta e metadati preservati; backup e restore infrastrutturali da configurare operativamente senza reintrodurre payload eliminati. Audit legacy reale, APP_KEY/rollout, ambiente/TLS e accettazione restano prerequisiti di prova futura autorizzata.

Verdetti A–F: TXT manuale **PARZIALE/NON PRONTO alla chiusura finale** per confronto BLOCCATO; WS Test/Send, ricevuta, retention e credenziali **PASS nei percorsi e nelle fixture documentati**. **CICLO QUESTURA COMPLETO: NON PRONTO ALLA CHIUSURA FINALE**. Nessuna compatibilità empirica o readiness generale dichiarata. Nessuna credenziale, DB, migration, backup o trasmissione reale utilizzati; niente commit/push/deploy.


## Appendice K — Confronto TXT accettati completato, 2026-10-06

**Il successivo gate più forte dell’appendice L declassa la readiness per il P1-Q1 riprodotto; il confronto empirico qui documentato resta valido.**

L'utente ha indicato `/Users/jorgeluccitelli/Desktop`. Letti esclusivamente i due file già nominati nella richiesta, senza copia né visualizzazione di dati personali: `1Questura-Nuovo-11-09-2026.txt` (1018 byte, 6 record) e `1Questura-Nuovo-11-09-2026 (1).txt` (678 byte, 4 record). Hash confrontati prima/dopo solo in memoria: originali invariati. Nessun file reale nel repository, nei test o in servizi esterni. L'accettazione pregressa è dichiarata dall'utente; nessuna ricevuta o nuova accettazione dell'ente attestata. Terzo TXT sul Desktop non letto.

**Confronto tecnico completato:** 168 byte/caratteri per record, ASCII compatibile UTF-8, nessun BOM, CRLF tra record e nessun terminatore finale; padding nomi/documenti, offset, date reali/formato, sesso, codici ufficiali e coerenza Comune/provincia, capi prima dei componenti e documento blank per familiari/membri gruppo. Arrivo/permanenza dei componenti coerenti con capo. Non coperti empiricamente ospite singolo, nascita estera e caratteri multibyte: copertura sintetica distinta.

**Differenza esplicita, nessun bug dimostrato:** permanenza con spazio sinistro (`" 1"/" 2"`) nei campioni, con zero (`"01"/"02"`) nel codice. Il Manuale WS tabella 1, riaperto in sola lettura, prescrive due caratteri e massimo 30 senza imporre quel riempimento. I valori sono validi; il controllo iniziale isdigit senza stripping è stato corretto nell'analisi. Nessuna modifica applicativa fondata sulla sola variante empirica, nessuna promessa di byte-identità o accettazione remota dello zero padding. Date storiche controllate nel calendario, non nella finestra oggi/ieri della lettura.

**Verdetto locale aggiornato: TECNICAMENTE PRONTO PER PROVA REALE CONTROLLATA**, limitatamente a confronto e flussi documentati; il blocco per indisponibilità dei TXT è chiuso. Progetto **BASELINE IN AUDIT**, Questura generale PARZIALE finché manca prova operativa/accettazione. Restano autorizzazione operativa, revisione/APP_KEY/rollout/audit legacy, ambiente/TLS/credenziali, backup/restore e verifica ricevuta/firma. Trasporto non attivato.

In questa prosecuzione modificati soltanto Maestro e rapporto: nessun codice applicativo/test/migration modificato, nessun nuovo test Laravel eseguito. Conservata l'evidenza **94 test/622 asserzioni PASS, 5 guardie PASS, 14 controlli SOAP/sicurezza PASS** del runtime dell'appendice J. Staging vuoto, branch main e modifiche locali Questura precedenti preservate; nessun commit/push/deploy, migration reale o trasmissione. Rapporto corrente in 26 sezioni aggiornato con percorsi, proprietà anonime, differenza e limiti.


## Appendice L — Gate singola prova reale: STOP per P1-Q1, 2026-10-06

**Diagnosi storica: il P1-Q1 viene corretto e verificato nell'appendice M; il gate operativo non è automaticamente promosso.**

**QUESTURA NON PRONTO; BASELINE IN AUDIT invariato.** La nuova richiesta impone STOP in presenza di P0/P1 e richiede blocco della stessa Schedina anche con primo invio incerto o concluso. Prova HTTP reale del framework con sola fixture/double SOAP: due nuovi casi dimostrano che cambiando il nome della stessa Schedina, e quindi l'hash del TXT, un secondo POST esegue **due Send invece di uno**, sia dopo uncertain senza riconciliazione sia dopo ricevuta/finalizzazione del primo tentativo.

**P1-Q1, unico difetto con due manifestazioni:** chiave send_key basata soltanto su trasporto/tenant/hash dei byte; nessuna riserva della sorgente prima della rete. Protezione identica del TXT resta PASS, ma snapshot diversi della stessa Schedina non sono bloccati. Riferimenti PMS vengono correttamente minimizzati alla finalizzazione: la soluzione non deve conservare il payload personale permanente né cancellare le Schedine. Nessuna duplicazione reale presso l'ente; impatto potenziale bloccante prima di abilitare il trasporto. Limite degli elenchi sovrapposti già dichiarato prima, ora riprodotto contro il requisito esplicito più forte.

**STOP applicato, nessuna correzione applicativa eseguita.** Proposta minima nel [rapporto corrente](../questura-audit-2026-10-06.md): riserva atomica e vincolo DB sulle sorgenti/identità logica tenant, indipendente dal TXT, identificatore tecnico opaco compatibile con minimizzazione; blocco di overlap incerti/non riconciliati e chiusi salvo distinta comunicazione autorizzata; Test/Send vincolati allo stesso snapshot. Da provare RED→GREEN, concorrenza/tenant/retention prima di un nuovo gate. Non basta bloccare il solo periodo. Ulteriori verifiche operative/matrice estesa non proseguite dopo il P1; nessuna readiness o accettazione reale dichiarata.

Preflight completo e nuovo fetch origin riuscito: main, HEAD/origin/main `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0, staging vuoto, 43 file locali pertinenti preservati. Modificati in questa task solo QuesturaBoundaryTest e i due documenti; codice/config/migration applicativi invariati.

Esecuzione della selezione completa baseline più due casi: **96 test/633 asserzioni, 2 failure e zero errori**; tutti i 94 casi precedenti passano, suite ampliata NON PASS. Runtime `/private/tmp/schedine-test-uhvrvhjv`, DB `test_geo_151d1bf641e9475b4b061c71533f4318`, MySQL PID30230/porta62712, HTTP PID30235/porta62713. Identità/riconnessione/23 rifiuti override, build/GEO immutabile PASS; risorse fermate/rimosse. PHP 8.3.33/PHPUnit10.5.38/MySQL8.0.36. Guardie negative esterne **5 PASS**; diff whitespace e sintassi PHP PASS. Controlli SOAP14 non rieseguiti perché il launcher interrompe dopo PHPUnit rosso: 14PASS rimane storico della baseline. Nessun test modificato per mascherare i due fallimenti.

La futura prova reale è descritta nel rapporto e resta bloccata: snapshot fissato, autenticazione/Test, distinta autorizzazione dell'unico Send, esito semantico/timeout senza retry, ricevuta della giornata nella finestra ufficiale, associazione/riconciliazione e finalizzazione delle copie Questura; Schedine PMS preservate. Niente richiesta di autorizzazione reale prima di correggere P1-Q1 e superare il nuovo gate. Nessun invio, credenziale reale, .env, DB operativo, migration reale, commit, push o deploy.


## Appendice M — P1-Q1 idempotenza corretto e verificato, 2026-10-06

**La ripresa successiva si arresta per il nuovo P1-Q2 nell'appendice N. Le prove e la correzione P1-Q1 qui documentate restano valide.**

**P1 IDEMPOTENZA CORRETTO E VERIFICATO nello scope isolato. PRONTO A RIPRENDERE L'AUDIT QUESTURA. BASELINE IN AUDIT invariato.** Superato il difetto dell'appendice L, non completato automaticamente il gate operativo. Nessuna accettazione Alloggiati Web o readiness produzione. [Rapporto completo corrente](../questura-audit-2026-10-06.md), prima sezione, con comandi, scenari, registro fallimenti e inventario.

Preflight main, HEAD/riferimento locale origin/main `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0, staging vuoto; nessun nuovo fetch, preservati tutti i 43 file locali iniziali. Riproduzione **prima del fix: 96 test/633 asserzioni, stessi 2 failure e zero errori**, due Send dopo modifica del nome sia incerto sia concluso. Runtime `/private/tmp/schedine-test-4u1erd5j`, DB `test_geo_418b963ae4dd67f447620e5ef0be5fdc`, MySQL PID32569/porta63339, HTTP PID32574/porta63340; attestazione/build PASS, risorse rimosse. I diagnostici originali e l'intero QuesturaBoundaryTest sono rimasti **byte-identici** all'inizio della task.

Identità scelta: **struttura_id + schedina.id**, minimo riferimento tecnico già nel dominio; trasporto live/simulation separato. Nuova migration `2026_10_06_130000_add_questura_send_reservations.php`: tabella tecnica con unique `(struttura_id, schedina_id, transport_mode)`, FK restrittive a struttura/tentativo e marker `identity_reserved_at`; nessun dato anagrafico, backfill automatico o cascade dal PMS. Il tentativo, le riserve ordinate e l'evento iniziale sono creati nella stessa transazione **prima della rete**; conflitto unique annulla l'intero nuovo elenco e genera errore senza Send. Nessuna transazione applicativa durante SOAP. La chiave secondaria comprende anche gli ID sorgente, evitando collisioni di Schedine diverse con byte identici; SHA resta audit/integrità, non autorizzazione primaria.

Riserve mantenute per sent/concluso, uncertain, partial, crash/in_progress, errori generici e rifiuto generale senza prova di zero acquisizioni. Modifica di nome/documento/periodo/indirizzo o payload minimizzato non libera la medesima identità. Riconciliazione non autorizza automaticamente un reinvio: un'eventuale correzione legittima richiede percorso distinto, esplicito e tracciabile, non implementato qui.

Retry legittimo: validazione/credenziali/Test falliti non riservano Send; DB pre-rete rollback completo. Il servizio marca `transmission_excluded=true` soltanto per apertura client/token/autenticazione falliti **prima di Send**, Authentication_Test negativo oppure risposta completa/coerente di **zero valide e tutte le righe negative**. Il metadato è un booleano tecnico nella proiezione chiusa degli esiti Send technical_error/rejected; nulla da testi arbitrari remoti. Esito/evento, release delle proprie riserve e send_key NULL avvengono nella stessa transazione. Se il consolidamento DB fallisce, resta in_progress e blocca: nessuna decisione basata su un dato solo in memoria. Solo esito generale Send negativo conserva la riserva. Manuale WS riaperto con GET documentale: distinzione esito generale/dettaglio per riga; scelta conservativa non equivalente a verifica remota.

Retention invariata: copie TXT/base64 e riferimenti archivio eliminabili, PDF/metadati e PMS preservati. Riserva tecnica separata rimane dopo finalizzazione e impedisce il secondo Send; non contiene copie personali. Gli archivi legacy Send non simulati senza marker bloccano **solo il proprio tenant** prima della rete, anche se già minimizzati: occorre audit separatamente autorizzato e identità dimostrabili, nessuna ricostruzione da nomi/hash o liberazione implicita. Identità non recuperabile conserva il blocco. Nessun rollout/migration/backfill reale eseguito.

**Finale: 114 test/802 asserzioni PASS**, tutti i 96 originali e 18 nuovi casi; **5 guardie PASS**, **14 controlli SOAP/sicurezza PASS**, sintassi **35 PHP PASS**, whitespace/integrità cataloghi-WSDL PASS; migrazioni storiche e guardie selezionate inalterate rispetto a HEAD. Runtime `/private/tmp/schedine-test-uer80dky`, DB `test_geo_8029433317cbdea679eca51d08a1a918`, MySQL PID41981/porta50311, HTTP PID42000/porta50312; identità/riconnessione/23 rifiuti override e build/GEO immutabile PASS, processi e risorse fermati/rimossi. PHP8.3.33/PHPUnit10.5.38/MySQL8.0.36, sole fixture e doubles, nessun trasporto SOAP reale. Concorrenza verificata con vincolo MySQL/duplicate key/rollback e conflitto HTTP, **non race multiprocesso**. Intera selezione baseline eseguita senza esclusioni; suite globale storica/browser non dichiarati PASS. Registro intermedio nel rapporto: fixture GEO 108/656, confronto identità stdClass 113/789, 114/804 prima della revisione conservativa; non sommati al finale né occultati.

Modificati esattamente otto file in questa task: controller Questura, modello QuesturaTransmission, servizi QuesturaWebService/EsitoTrasmissioneQuestura, nuova migration riserve, nuova QuesturaIdempotencyTest, rapporto e Maestro. Totale worktree **45 file: 19 tracked modificati, 26 untracked**; i precedenti sono preservati, staging vuoto, SHA/riferimenti/0-0 invariati. Nessuna modifica di config, dipendenze, dominio/schema PMS, GEO/ISTAT o infrastruttura. Schema solo MySQL effimero. **Nessun .env, dato/credenziale reale, cancellazione PMS, commit, push, deploy o trasmissione Questura reale.**

Limiti e prossimo passo: riprendere il gate con una nuova task autorizzata; restano revisione/rollout, audit legacy, Test e Send vincolati allo snapshot della futura prova, matrice operativa, TLS/firma/ricevuta e accettazione reali. Il fix non certifica operazioni manuali esterne non osservate. La task termina qui senza richiesta di autorizzazione a Send reale.


## Appendice N — Ripresa audit Questura: STOP P1-Q2, 2026-10-07

**Diagnosi storica: P1-Q2 corretto e verificato nell’appendice P.**

**QUESTURA NON PRONTO — STOP P1-Q2. BASELINE IN AUDIT invariato.** Ripresa dal primo requisito residuo del §6 dell'audit autorizzato: WS Test deve utilizzare esattamente il payload destinato a Send, anche fra POST distinti. Nessuna riapertura della baseline TXT, ricevute, retention o P1-Q1; il vincolo sullo stesso snapshot era esplicitamente pendente nelle appendici L–M. Interrotto immediatamente l'audit dopo la riproduzione: nessuna correzione applicativa, suite completa o prosecuzione della matrice operativa.

### Prova, causa e impatto

Nuovo diagnostico `tests/Feature/QuesturaTestSendSnapshotTest.php`, due scenari attraverso HTTP kernel, autenticazione, middleware e CSRF reali su fixture sintetiche. Vero servizio Questura con solo client SOAP sostituito dal double preesistente, nessun trasporto esterno. POST verify sul periodo/tenant, poi primo POST send sul medesimo elenco. Sorgente invariata: byte e hash coincidono, PASS. Modifica del nome della stessa Schedina dopo Test: il primo Send avviene su byte e SHA diversi, FAIL alla riga 70. L'oracolo consente sia il blocco sia l'invio dello snapshot già testato; il comportamento corrente non soddisfa nessuna delle due alternative. Verificati un solo Send, una sola riserva, snapshot Send coerente con i propri byte, hash verify storico immutato e Schedina PMS preservata. `accepted` resta false: nessuna accettazione dell'ente inferita dal double.

**P1-Q2 distinto da P1-Q1:** riguarda la prima trasmissione di contenuto non testato, non un reinvio della medesima identità. Causa nel controller `app/Http/Controllers/QuesturaExportController.php`: `runWsAction` ricostruisce il TXT ad ogni POST (righe 323, 331–341), riserva correttamente le identità (347–388), ma chiama Test oppure Send sul payload appena ricostruito senza riferimento alla verifica precedente (395–397). `buildPeriodoTxt` legge le sorgenti correnti (418–430). Contratto SOAP e serializzazione del singolo payload non causano la divergenza. Impatto potenziale: una modifica PMS fra verifica e invio cambia il contenuto trasmesso senza nuova verifica. Nessun invio reale, duplicazione reale o danno effettivo attestato.

**Proposta minima, NON implementata:** legare Send a uno snapshot verify immutabile, esplicitamente identificato e appartenente allo stesso tenant/trasporto/elenco; validare il risultato semantico Test, senza confondere HTTP 200 o `unknown` con accettazione. Prima delle riserve scegliere atomicamente il comportamento autorizzato: bloccare se la sorgente è cambiata richiedendo un nuovo Test, oppure trasmettere esclusivamente i byte dello snapshot testato. Preservare riserve/idempotenza P1-Q1, regole conservative di retry, retention temporanea e PMS. Una futura task di correzione deve portare questo diagnostico da rosso a verde e coprire snapshot cross-tenant, Test negativo/non valido e cambiamento fra preparazione e riserva; nessuna scelta di schema o workflow applicata in questa task.

### Esecuzioni e limiti

- Prima selezione: **2 test / 0 asserzioni / 2 errori**, dipendenza `QuesturaWsContractTest.php` assente dal checkout temporaneo. Corretto soltanto il comando includendo la suite che contiene il double; nessun file preesistente modificato. Runtime `/private/tmp/schedine-test-bgzeuxof`, DB `test_geo_c9df40d392d4941fea1e43c9a52536a7`, MySQL PID44653/porta50979, HTTP PID44664/porta50980. Errori non riclassificati come prova P1.
- Selezione corretta: **7 test / 69 asserzioni / 1 failure / zero errori**. Cinque test contratto SOAP preesistenti PASS, nuovo controllo invariato PASS, nuovo caso modificato FAIL. Runtime `/private/tmp/schedine-test-5dgvrc5r`, DB `test_geo_354c56015e8776321f67ccb3bf75e6f6`, MySQL PID44927/porta51040, HTTP PID44939/porta51041. PHP 8.3.33, PHPUnit 10.5.38, MySQL 8.0.36. Log diagnostico temporaneo `/private/tmp/questura-ripresa-snapshot-2.log`, soli digest sintetici nella failure.
- Entrambi i runtime: attestazione identità/riconnessione e 23 rifiuti override prima delle migrazioni, build asset/GEO PASS; processi fermati, directory effimere rimosse e assenza verificata.
- Nuove verifiche: **5 guardie PASS**, sintassi **36 file PHP PASS**, `git diff --check` PASS. Nessuna guardia indebolita.
- **114 test / 802 asserzioni PASS e 14 controlli sicurezza/SOAP PASS restano evidenze storiche dell'appendice M**, non nuove esecuzioni. I cinque test SOAP sovrapposti non si sommano alla baseline. Suite completa finale non eseguita per STOP obbligatorio; nessuna nuova attestazione complessiva P1-Q1, race multiprocesso o browser.

Comando della selezione corretta, eseguito esclusivamente nel launcher isolato:

```sh
python3 -B tests/Isolation/run.py --include app/Support/Questura/LegacyCredentials.php app/Support/Questura/Catalogo.php app/Models/QuesturaReceipt.php app/Services/QuesturaRetentionService.php app/Console/Commands/QuesturaExpiredReceipts.php app/Console/Commands/QuesturaArchiveAudit.php config/questura.php database/migrations/2026_10_06_100000_encrypt_questura_credentials.php database/migrations/2026_10_06_110000_add_questura_archive_integrity.php database/migrations/2026_10_06_120000_add_questura_retention.php database/migrations/2026_10_06_130000_add_questura_send_reservations.php reference/questura/comuni.csv reference/questura/stati.csv reference/questura/documenti.csv reference/questura/tipi.csv reference/questura/manifest.json reference/questura/service.wsdl reference/questura/wsdl-manifest.json --phpunit tests/Feature/QuesturaTestSendSnapshotTest.php tests/Feature/QuesturaWsContractTest.php
```

WS Test/Send verificati parzialmente fino al nuovo blocco; ricevute, riconciliazione/retention, matrice ruoli/tenant/CSRF, privacy/log e legacy non ulteriormente auditati dopo STOP. Le evidenze precedenti restano circoscritte ai propri casi. TLS, firma PDF, accettazione reale, rollout/APP_KEY, bonifica legacy e policy backup/restores restano da verificare separatamente; nessuna readiness operativa promossa e nessuna richiesta di autorizzazione a Send reale.

### Preflight, inventario e conservazione

Preflight nel repository autorizzato: branch `main`, HEAD e riferimento locale `origin/main` **`3eb559cfea9fed6a20e7949037f1d5a0236695be`**, ahead/behind **0/0**, staging vuoto. Nessun nuovo fetch: confronto con riferimento locale, non attestazione dello stato remoto attuale. Inizio: **45 file locali, 19 tracked modificati e 26 untracked preesistenti**. Fine: **46 file locali, stessi 19 tracked modificati e 27 untracked**. Questa task modifica soltanto il Maestro e aggiunge il diagnostico snapshot. Tutti gli altri 44 file preesistenti, inclusi i 26 untracked, risultano byte-identici agli hash del preflight. Applicazione, configurazione, schema e test preesistenti invariati.

Il rapporto `docs/questura-audit-2026-10-06.md` è fra i 26 untracked: l'esplicito divieto di modificarli prevale sull'aggiornamento del rapporto subordinato. Conservato byte-identico; questa appendice è il rapporto finale corrente della ripresa. Inventario classificato esclusivamente in lettura: **18 pertinenti al lavoro Questura, 7 diagnostici, 1 documentazione, 0 temporanei, 0 altro**. Nessuna eliminazione o staging.

| File untracked preesistente | Classificazione |
|---|---|
| `app/Console/Commands/QuesturaArchiveAudit.php` | Pertinente al lavoro Questura |
| `app/Console/Commands/QuesturaExpiredReceipts.php` | Pertinente al lavoro Questura |
| `app/Models/QuesturaReceipt.php` | Pertinente al lavoro Questura |
| `app/Services/QuesturaRetentionService.php` | Pertinente al lavoro Questura |
| `app/Support/Questura/Catalogo.php` | Pertinente al lavoro Questura |
| `app/Support/Questura/LegacyCredentials.php` | Pertinente al lavoro Questura |
| `config/questura.php` | Pertinente al lavoro Questura |
| `database/migrations/2026_10_06_100000_encrypt_questura_credentials.php` | Pertinente al lavoro Questura |
| `database/migrations/2026_10_06_110000_add_questura_archive_integrity.php` | Pertinente al lavoro Questura |
| `database/migrations/2026_10_06_120000_add_questura_retention.php` | Pertinente al lavoro Questura |
| `database/migrations/2026_10_06_130000_add_questura_send_reservations.php` | Pertinente al lavoro Questura |
| `docs/questura-audit-2026-10-06.md` | Documentazione Questura |
| `reference/questura/comuni.csv` | Pertinente al lavoro Questura |
| `reference/questura/documenti.csv` | Pertinente al lavoro Questura |
| `reference/questura/manifest.json` | Pertinente al lavoro Questura |
| `reference/questura/service.wsdl` | Pertinente al lavoro Questura |
| `reference/questura/stati.csv` | Pertinente al lavoro Questura |
| `reference/questura/tipi.csv` | Pertinente al lavoro Questura |
| `reference/questura/wsdl-manifest.json` | Pertinente al lavoro Questura |
| `tests/Feature/QuesturaBoundaryTest.php` | Diagnostico Questura |
| `tests/Feature/QuesturaCredentialEncryptionTest.php` | Diagnostico Questura |
| `tests/Feature/QuesturaIdempotencyTest.php` | Diagnostico Questura |
| `tests/Feature/QuesturaLegacyMigrationTest.php` | Diagnostico Questura |
| `tests/Feature/QuesturaRetentionTest.php` | Diagnostico Questura |
| `tests/Feature/QuesturaWsContractTest.php` | Diagnostico Questura |
| `tests/Isolation/questura-checks.php` | Diagnostico Questura |

Nuovo ventisettesimo untracked: `tests/Feature/QuesturaTestSendSnapshotTest.php`, diagnostico di questa task. Nessun git add/commit/push, deploy, cambio branch o operazione Git distruttiva. Nessun `.env`, DB operativo, credenziale o dato personale reale utilizzato; migrazioni solo nei MySQL effimeri, nessuna cancellazione PMS o trasmissione Questura reale. **Audit arrestato; P1-Q2 documentato e ancora aperto.**


## Appendice O — Preflight correzione P1-Q2 e conflitto di scope, 2026-10-07

**Conflitto risolto dalla deroga esplicita dell’utente; correzione e risultati nell’appendice P.**

**P1-Q2 ancora aperto; correzione in attesa di chiarimento dei vincoli. BASELINE IN AUDIT invariato.** Nuova task autorizza la sola coerenza Test→Send e impone blocco del payload corrente modificato, senza inviare il vecchio snapshot. Preflight: repository autorizzato, branch main, HEAD e origin/main locale `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind 0/0, staging vuoto; 19 tracked modificati e 27 untracked iniziali (26 protetti più diagnostico Q2). Nessun fetch o operazione Git mutante.

Riproduzione prima di qualsiasi fix con il medesimo comando dell'appendice N e diagnostico byte-identico: **7 test / 69 asserzioni / 1 failure**, stessa causa e messaggio. Runtime `/private/tmp/schedine-test-y6mnb9j0`, DB `test_geo_da34794950c66677df5c3ac9e59a57bc`, MySQL PID45672/porta51213, HTTP PID45678/porta51214. Identità/riconnessione/23 rifiuti override e build/GEO PASS, PHP8.3.33/PHPUnit10.5.38; SOAP solo double. Log temporaneo `/private/tmp/questura-q2-red.log`. Nessuna nuova suite completa o correzione applicativa.

**CONTRADDIZIONE nella richiesta corrente:** aggiornare `docs/questura-audit-2026-10-06.md` ma vietare modifiche ai 26 untracked che lo comprendono; richiedere Test positivo obbligatorio prima del Send, suite Q1 verde e test Q1 invariati, mentre `QuesturaIdempotencyTest::test_validazione_locale_e_test_ws_falliti_non_riservano_send` richiede esplicitamente Send dopo Test fallito (righe 180–195), e diversi altri casi inviano senza Test e contano soltanto i tentativi Send. Il nuovo requisito funzionale non può coesistere con quelle precondizioni obsolete mantenendo tutte le prove verdi. Non è un nuovo P0/P1 applicativo.

Richiesta all'utente una deroga circoscritta al rapporto e alle sole precondizioni delle fixture interessate, preservando le asserzioni delle riserve/idempotenza e gli altri untracked. Nessuna deroga assunta dal silenzio, nessuna modifica ai test per mascherare P1-Q2, nessun percorso speciale in testing o Test automatico introdotto per aggirare il requisito. Un eventuale aggiornamento delle fixture deve distinguere i conteggi Test dai Send e mantenere tutte le asserzioni sostanziali Q1.

Nell'attesa modificato soltanto questo Maestro. I 26 untracked protetti e il diagnostico Q2 restano byte-identici. Nessun codice, configurazione, schema, dato reale, .env o PMS modificato; nessun commit/push/deploy o trasmissione Questura reale. **NON CORRETTO / NON VERIFICATO: occorre risolvere il conflitto di scope prima della correzione completa.**


## Appendice P — P1-Q2 corretto e verificato, 2026-10-07

**P1-Q2 CORRETTO E VERIFICATO nelle prove isolate. PRONTO A RIPRENDERE L'AUDIT QUESTURA DOPO P1-Q2. BASELINE IN AUDIT invariato.** Nessuna ripresa automatica del resto dell'audit, nessuna accettazione dell'ente o readiness produzione. Rapporto corrente: [correzione Q2 completa](../questura-audit-2026-10-06.md), sezione iniziale, con comandi, matrice, inventario e runtimes.

L'utente autorizza esplicitamente la deroga al rapporto e ai soli setup/fixture Q1 necessari. Applicata a QuesturaIdempotencyTest e ai casi Q1 di QuesturaBoundaryTest: veri POST Test positivi prima delle prove Send, lookup/conteggi degli archivi circoscritti ai Send. Le verifiche sostanziali identità/unique/incerto/concluso/retry/crash/tenant/retention/PMS restano preservate. **Gli altri 23 dei 26 untracked restano byte-identici; diagnostico Q2 originale byte-identico.**

RED prima delle modifiche, già appendice O: **7 test/69 asserzioni, 1 failure**, stessa causa N. GREEN originale **7/65 PASS**: il ramo modificato ora si arresta senza Send. Selezione Q2 specifica **26/247 PASS** (ulteriori quattro casi invalidi verificati nella completa), Q1+contratto **23/233 PASS**. Finale completo della baseline autorizzata ampliata: **139 test/1.086 asserzioni PASS**, tutti i 114 originali più 25 Q2, nessun caso escluso. **5 guardrail PASS, 14 SOAP/sicurezza PASS, 37 PHP sintassi PASS, diff check e hash cataloghi/WSDL PASS**; 142 migration storiche e guardie principali inalterate rispetto a HEAD. Prove sovrapposte non sommate; suite globale storica/browser e race multiprocesso non attestati. Nessun nuovo P0/P1 distinto emerso nelle prove circoscritte.

Causa: controller rigenerava un TXT indipendente per ogni POST senza confrontarlo con il precedente Test. Soluzione minima esclusivamente nel controller: ultima verifica tenant/periodo/trasporto, lock e verifica positiva per tutte le righe; stato unknown/HTTP200/accepted da soli insufficienti. Test negativo successivo, assente, in_progress, finalizzato/minimizzato o contesto diverso bloccano. Confrontati ID ordinati Schedine/componenti, scope, tenant/periodo/modalità, quantità, charset, byte_size, SHA-256 e byte esatti dello snapshot. Modifica dei dati trasmessi richiede nuovo Test positivo; campo PMS non trasmesso non invalida sulla sola base di updated_at. UI e errore spiegano la ripetizione. Zero rete/tentativi/riserve in caso di mismatch; nessun vecchio snapshot inviato automaticamente.

**Q1 distinto e invariato:** riserva dell'identità struttura+schedina_id per trasporto, indipendente dal contenuto, vincolo DB e release solo con esclusione certa consolidata. Nuovo Test positivo dopo mismatch permette il primo Send; dopo incerto/concluso non libera Q1. Modello/servizi/schema Q1 e retention non modificati. **Q2:** corrispondenza del contenuto verificato e inviato, non identità primaria.

**TOCTOU sui byte:** TXT costruito una sola volta, catturato per valore, confrontato prima delle riserve nella transazione breve, poi passato direttamente allo stesso servizio SOAP senza rigenerazione. Double verifica byte all'ingresso Send e riserva presente, builder rifiuta seconda costruzione: uguaglianza esatta osservata. Nessun lock PMS mantenuto durante SOAP o certificazione di concorrenza multiprocesso; garanzia sullo snapshot corrente preparato e sul valore consegnato al trasporto. Snapshot temporaneo già esistente, nessuna nuova copia personale permanente o migration; minimizzazione/PMS preservati.

Runtime finale `/private/tmp/schedine-test-m0a9ys3k`, DB `test_geo_c00dc077331abd1d3a7589f202eafe7f`, MySQL PID48117/porta51793, HTTP PID48150/porta51794. Identità/riconnessione/23 rifiuti override prima delle migrazioni, build/GEO PASS; processi e directory rimossi. PHP8.3.33/PHPUnit10.5.38/MySQL8.0.36, sole fixture/doubles. Runtimes specifici e comandi nel rapporto. Il log temporaneo RED è stato sovrascritto per errore dal primo comando GREEN; output acquisito e appendice O conservano la prova, non si dichiara quel log ancora disponibile. Nessun fallimento applicativo dopo il fix.

Esattamente sette file della correzione: controller Questura, vista Questura, due setup suite Q1 indicati, nuovo QuesturaPayloadCoherenceTest, rapporto e Maestro. Preflight e finale main, HEAD/origin/main locale `3eb559cfea9fed6a20e7949037f1d5a0236695be`, 0/0, staging vuoto; nessun fetch. Inizio 46 file (19 tracked modificati/27 untracked), fine 47 (stessi 19/28). Tutti gli altri file iniziali invariati. No app/service Q1 redesign, config, dipendenze, nuova migration, .env, credenziali/dati reali, cancellazione PMS, commit/push/deploy o trasmissione Questura reale. Resta autorizzazione distinta per audit residuo e futuro rollout/legacy/TLS/ricevuta-firma/accettazione/backup-restore operativi.

Chiusura: CODICE SÌ; TEST SÌ; DOCUMENTAZIONE/MAESTRO SÌ; CONFIGURAZIONE NO; SCHEMA APPLICATIVO NO; DATI REALI NO (fixture effimere SÌ); TEST ESEGUITI SÌ; COMMIT NO; PUSH NO; DEPLOY NO; TRASMISSIONI QUESTURA REALI NO. **Task conclusa dopo correzione e verifica; attendere nuova autorizzazione per riprendere l'audit.**


## Appendice Q — Ripresa finale audit: STOP P1-Q3, 2026-10-07

**NON PRONTO — STOP P0/P1 per nuovo P1-Q3. BASELINE IN AUDIT invariato.** Supera esclusivamente la disponibilità a riprendere l’audit dell’appendice P, non invalida i fix Q1/Q2 né promuove le prove storiche a nuove attestazioni. [Rapporto completo](../questura-audit-2026-10-06.md), nuova sezione iniziale: causa, prova, limiti A–G, comandi, runtimes e proposta minima non implementata.

Nuovo diagnostico QuesturaFinalAuditTest, righe 119–134: Test positivo via vero POST con SOAP double; trigger MySQL sintetico rifiuta INSERT Send. HTTP500, nessuna chiamata Send, nessun tentativo/riserve persistiti, PMS conservato: PASS. Il log single del checkout effimero contiene la base64 esatta dello snapshot personale: unico oracolo privacy FAIL. QueryException interpola i binding SQL del payload, controller lascia propagare l’errore generico e Handler non sanifica la catena prima del logging. Retention delle copie archiviate non minimizza log: rischio di copia personale oltre il ciclo, senza prova di incidente o accesso pubblico/cross-tenant ai log reali.

Esecuzioni nuove separate: 26/298 con un risky (nome provider corretto solo nel nuovo test); 26/310 PASS; prima fixture trigger 27 test/18 errori per DDL dentro transazione, prova invalida; fixture corretta 27/317 con un solo fallimento P1-Q3, zero errori/risky. Trigger prima della transazione, DROP dopo rollback, guardie intatte. 5 guardrail PASS, 38 PHP sintassi PASS e diff check PASS. Nessuna suite completa o nuova selezione 14 SOAP dopo STOP: 139/1.086 e 14 SOAP restano baseline storica P. Nessuna prosecuzione dell’audit o correzione automatica.

Proposta da autorizzare: reporting circoscritto Questura con diagnostica tecnica minima senza query/binding/payload/contesto o catena originale; preservare Q1/Q2, rollback pre-rete e stato incerto post-rete. Diagnostico RED da mantenere e portare a GREEN per rimozione effettiva della copia personale, poi prove DB pre/post rete e regressioni complete. Bonifica eventuali log reali/backup distinta e non eseguita.

Esattamente tre file di questa task: nuovo tests/Feature/QuesturaFinalAuditTest.php, rapporto, Maestro. Altri 45 file locali preesistenti byte-identici al preflight; codice applicativo/config/schema/test Q1/Q2 invariati. Main e HEAD/origin/main locale 3eb559cfea9fed6a20e7949037f1d5a0236695be, 0/0, staging vuoto, nessun fetch; 19 tracked modificati, untracked da28 a29. Quattro runtime rimossi; dati solo sintetici effimeri e SOAP solo doubles. Nessun dato reale/.env/DB operativo, invio reale, cancellazione PMS, commit/push/deploy. **Fermo allo STOP; occorre nuova autorizzazione per correggere P1-Q3.**


## Appendice R — P1-Q3 corretto e verificato, 2026-10-07

**P1-Q3 CORRETTO E VERIFICATO nelle prove isolate. PRONTO A RIPRENDERE L’AUDIT QUESTURA DOPO P1-Q3. BASELINE IN AUDIT invariato.** Supera lo STOP Q esclusivamente per questo difetto, preservando tutta l’evidenza storica. Audit residuo non ripreso; nessuna prova reale, readiness produzione o accettazione ente. [Rapporto completo](../questura-audit-2026-10-06.md), nuova sezione iniziale.

Riproduzione obbligatoria prima del fix: diagnostico byte-identico, 27/317 con unico fallimento Q3. QueryException interpolava payload/binding nel messaggio SQL; reporting Laravel aggiungeva oggetto eccezione e catena/context. Unico file applicativo modificato Handler: confine comune del ciclo Questura, prima di report(), context() e logger; messaggio fisso e soli error_code/operazione ammessa/classe exception, senza originale/previous/trace/SQL/binding/payload/modelli. Ambito classi Questura nella trace o route Questura; servizi console inclusi. Rendering errori inattesi e HTTP5xx controllato anche in debug, output console minimizzato; 4xx/auth/validation e reporting estraneo preservati. Nessun logger DB globalmente disabilitato, nessun dato aggiuntivo superfluo.

Nuova matrice privacy di 17 casi: DB reservation, esito dopo Send, acquisizione e riconciliazione ricevuta; callback/context/previous prima della rete in quattro combinazioni debug/JSON; due HTTP500/503; SoapFault/timeout/pre-Send token/ricevuta/parser; console retention e reporting estraneo. TXT/base64, nomi/documento/nascita/indirizzo, credenziali/token/segreti assenti da log single effimero, record/context Monolog, risposte/eventi e console verificati. Snapshot negli archivi temporanei autorizzati resta nel ciclo di retention. Pre-rete rollback/zero Send/riserve/PMS preservati; post possibile rete riserva e in_progress conservati, retry bloccato. Q1 identità e Q2 Test==Send restano distinti, test/algoritmi preesistenti invariati.

Risultati nuovi, non sommati: RED 27/317 un failure; prima verifica 27/287 cinque failure per CSRF419 erroneamente reso500, corretto esclusivamente il nuovo Handler tramite conversione Laravel; prima matrice 20/953 PASS; diagnostico originale+Q1+Q2+contratto 70/763 PASS; **completa 178 test/2.660 asserzioni PASS**, tutti 139 originali più 22 audit finale e 17 privacy, zero failure/error/risky. **5 guardrail PASS; 14 SOAP PASS; 39 PHP sintassi PASS; diff/hash cataloghi-WSDL PASS**. 142 migration storiche e sei guardie/entrypoint byte-identici a HEAD. Nessuna static analysis dedicata obbligatoria individuata; build/architettura/GEO PASS nel launcher. Nessun nuovo P0/P1 distinto emerso nelle prove circoscritte.

Runtime finale /private/tmp/schedine-test-u4r2fpes, DB test_geo_d93041297d4470d09e3a647658dbc3d9, MySQL58268:54513, HTTP58273:54514. PHP8.3.33/PHPUnit10.5.38/MySQL8.0.36, identità/riconnessione/23 rifiuti override prima delle migrazioni; sole fixture e SOAP doubles. Tutti cinque runtime rimossi, log sintetici e fallimenti intermedi conservati e dettagliati nel rapporto, RED non sovrascritto. Nessun test Laravel fuori infrastruttura isolata.

Esattamente quattro file di questa task: Handler (tecnicamente indispensabile e autorizzato), nuovo QuesturaLoggingPrivacyTest, rapporto e Maestro. Diagnostico originale e Q1/Q2 byte-identici; degli altri 45 precedentemente protetti, solo Handler modificato per il fix, 44 invariati; totale 45 dei 48 iniziali invariati. Main e HEAD/origin/main locale 3eb559cfea9fed6a20e7949037f1d5a0236695be, 0/0, staging vuoto; nessun fetch. 19 tracked modificati, untracked 29→30. Nessun commit/push/deploy, .env reale, DB/migration operativi, dati/credenziali reali, invio reale o cancellazione PMS.

**POSSIBILE BONIFICA LOG STORICI DA VALUTARE PRIMA DELLA PRODUZIONE:** copia concreta riprodotta solo nel log sintetico pre-fix; presenza nei log operativi storici NON VERIFICATA. Nessun log reale/backup letto o modificato; eventuale valutazione/bonifica distinta autorizzata. CODICE SÌ; TEST SÌ; DOCUMENTAZIONE/MAESTRO SÌ; CONFIG NO; SCHEMA APPLICATIVO/DATI REALI NO; TEST ESEGUITI SÌ; COMMIT/PUSH/DEPLOY/INVII REALI NO. **Task fermata dopo correzione e regressione completa; attendere nuova autorizzazione per audit residuo.**


## Appendice S — Chiusura dell’audit locale Questura dopo P1-Q3, 07/10/2026

**PRONTO PER PROVA REALE CONTROLLATA**, esclusivamente nel perimetro tecnico locale definito dall’audit. **BASELINE IN AUDIT** invariato; nessuna readiness generale del gestionale/produzione o accettazione Alloggiati Web. Lo STOP dell’appendice Q è superato dalla correzione Q3 dell’appendice R e dalla chiusura dei residui qui registrata. Q1/Q2/Q3 non riaperti o modificati: tutti PASS nella regressione finale. Nessun nuovo P0/P1 distinto emerso nel perimetro Questura verificato.

Maestro e rapporto letti integralmente; ripresa dai residui ricevute/riconciliazione, retention, privacy e autorizzazioni, senza ricominciare da zero. Nuovo `tests/Feature/QuesturaConclusiveAuditTest.php`, 9 casi HTTP/kernel con fixture sintetiche e SOAP double: pagina/generazione auth-ruolo-CSRF-tenant; TXT periodo/singola/download/refresh senza rete o pulizia; ricevuta mancante/alterata; incerto con ricevuta giornaliera senza associazione implicita di accettazione, conferma obbligatoria e finalizzazione ripetibile; ownership admin/proprietario estranea su 6 percorsi; Test/ID inesistente non acquisiscono ricevute. Nessuna modifica applicativa o ai test preesistenti.

Ciclo PMS → validazione → TXT → Test → Q2 → reservation Q1 → Send double → risposta/esito → ricevuta → associazione/riconciliazione → retention → privacy/autorizzazioni **PASS nel perimetro locale**. Ricevute duplicate/tardive/già acquisite/non associabili, parsing negativo, errori SOAP/DB/download e tenant coperti dalle suite originali e nuovi residui. Ricevuta unica giornaliera struttura/data, non prova automatica delle singole righe; incerti/parziali/in_progress necessitano conferma esplicita. Parsing PDF minimo e integrità byte/hash PASS, firma/struttura PDF reale non certificate. Retention preserva ricevuta/metadati/reservation/PMS e rimuove TXT/base64 soltanto nei percorsi consentiti; nessuna pulizia per mancanza ricevuta, incerto non riconciliato o TXT soltanto scaricato. I tre metadati di esportazione PMS si aggiornano come previsto dalla generazione; i dati della Schedina restano preservati. Q2 su byte effettivi e Q1 dopo minimizzazione invariati. Privacy Q3 PASS senza redesign né nuova fuga distinta osservata.

Suite completa autorizzata Questura + regressione Schedine: **187 test / 2.774 asserzioni PASS** (baseline 178/2660 più 9 casi, senza sommare sovrapposizioni), **5 guardrail PASS**, **14 SOAP/sicurezza PASS**, sintassi **41 PHP PASS**, Pint `--test` sul nuovo file PASS, hash cataloghi/WSDL e `git diff --check` PASS. Nessun ulteriore analizzatore statico configurato per questa fase. 142 migrazioni storiche e 6 entry point/guardie invariati rispetto a HEAD. Non attestata la suite globale del gestionale. Runtime finale `/private/tmp/schedine-test-_saxbw4z`, DB `test_geo_de2e99e03104b602f4000aeeb6bc003d`, MySQL PID64182/55969, HTTP PID64187/55970; identità/riconnessione/23 rifiuti override, build/GEO PASS. Processi fermati e directory rimossa. PHP 8.3.33 / PHPUnit 10.5.38 / MySQL 8.0.36. Comando integrale, matrice delle evidenze e registro dei tre run diagnostici nel [rapporto Questura](../questura-audit-2026-10-06.md). Run diagnostici 11/103 un errore streamed e 14/136 due aspettative fixture, corretti solo nel nuovo test; 14/147 PASS con 5 SOAP sovrapposti. Nessun difetto applicativo dimostrato da tali errori.

Frontiere doppio click/richiesta duplicata/due tab rappresentate da chiamate sequenziali; refresh GET senza effetti mutanti. Lock DB ricevuta/tentativo/copie e vincoli unici sono garanzie strutturali, **non vera race multiprocesso verificata** su Send o retention; nessuna acceptance browser con due tab fisici. Timeout/crash/errori pre/post rete, SOAP inatteso, HTTP positivo con errore, batch modificato e DB coperti dalle regressioni Q1/Q2/Q3/Boundary/FinalAudit. Rete reale/TLS/auth, accettazione ente e firma PDF non verificate. Cataloghi/WSDL datati 06/10/2026, non riverificati online. Rollout/APP_KEY/storage/backup-restore e confronto TXT accettati restano evidenze/procedure operative separate.

Legacy conservativo PASS: Send non simulati senza identità/reservation bloccano il proprio tenant anche minimizzati; nessun backfill/sblocco automatico. Eventuale audit/bonifica legacy prima della produzione richiede autorizzazione distinta e associazioni dimostrabili; se irrecuperabili permane il blocco. **POSSIBILE BONIFICA LOG STORICI DA VALUTARE PRIMA DELLA PRODUZIONE**: vulnerabilità storica provata sinteticamente, presenza reale nei log operativi NON verificata. Nessuna ispezione/cancellazione log reali; limite preproduzione, non ostativo alla chiusura locale.

Git: `main`, HEAD e `origin/main` locale `3eb559cfea9fed6a20e7949037f1d5a0236695be`, ahead/behind locale 0/0, nessun fetch e staging vuoto; da 19 tracked / 30 untracked a 19/31 (50 file locali). Solo tre file prodotti dalla fase: nuovo `tests/Feature/QuesturaConclusiveAuditTest.php`, aggiornati `docs/questura-audit-2026-10-06.md` e questo Maestro. Gli altri 47 file preesistenti byte-identici al preflight, inclusi gli untracked eccetto il rapporto esplicitamente aggiornato. Nessun sanamento o eliminazione workspace, nessuna modifica applicativa/schema/config/test precedenti.

**STOP FINALE: gate locale raggiunto, attendere nuova autorizzazione.** Nessun Send/Test reale, commit, push, deploy, .env reale, produzione, DB/migration reali, cancellazione PMS o ispezione/modifica log operativi. Nessuna prova reale eseguita né autorizzata dal verdetto: soltanto candidabilità tecnica locale alla sua preparazione.


## Appendice T — Fix visual/UX Questura prima del pre-commit, 07/10/2026

**FIX UX QUESTURA VERIFICATO — PRONTO PER REVISIONE PRE-COMMIT** nel perimetro richiesto. Baseline funzionale **PRONTO PER PROVA REALE CONTROLLATA** dell’appendice S preservata, progetto **BASELINE IN AUDIT**. Nessun nuovo P0/P1 emerso. Nessuna autorizzazione a staging, commit, invio reale o deploy.

Credenziali: stato Password erroneamente dentro input-group fra campo e occhio. Spostato sotto il gruppo nella colonna; Password e WSKEY usano `d-block mt-1`, stesso design e stato backend preesistente. Campi segreti vuoti e toggle invariati. Browser desktop 1440 / mobile 390 PASS su allineamento, posizione degli stati, toggle e assenza overflow orizzontale.

Schermata testuale: risposta minimizzata `text/plain` del Handler Q3 sui guasti inattesi. Trigger riprodotto sinteticamente: credenziale Password/WSKEY non decifrabile, cast encrypted → DecryptException durante `credentialsStatus` in `index`, più lettura delle stesse proprietà dalla Blade. Nessun dato/log operativo letto: il trigger della singola osservazione manuale reale non verificato. Solo questo errore di configurazione noto viene intercettato in index, riportato tramite report/Q3 e reso nella **normale questura.index con HTTP 409**, alert fisso, layout/navigazione/intestazione/storico e download autorizzati preservati. Nessun redirect/riscrittura credenziali/falso successo. Password/WSKEY in Blade derivano dallo stato preparato; se bloccate “Da verificare”, Test/Send disabilitati in UI. Protezioni backend e azioni manuali invariate. Errori tecnici inattesi restano 500/5xx sicuri tramite Handler invariato; nessun catch generico introdotto. Q1/Q2/Q3, servizi, modelli, schema, retry e retention non modificati.

Prima del fix 6 test / 19 asserzioni con 4 failure attese (layout e 500 contro 409). Mirati 9/146 PASS più 4 Playwright PASS. Finale **196 test / 2.920 asserzioni PASS** (tutta baseline 187/2774 più 9 UX, senza somme sovrapposte); **Q1/Q2/Q3 PASS**, **4 Playwright PASS** con attesa active/opacity1 dei tab e contenuto storico, **5 guardrail**, **14 SOAP/sicurezza**, sintassi **45 PHP**, Pint sui 2 nuovi PHP, sintassi JS, hash cataloghi/WSDL e diff-check PASS. Debug acceso/spento e Password/WSKEY non decifrabili: niente TXT/base64/segreti/SQL/stack nella risposta o log sintetico; storico scaricato attraverso route autorizzata, zero tentativi/reservation, configurazione invariata. Auth/tenant/CSRF preservati; errore tecnico inatteso 500 minimizzato. Quattro screenshot sintetici esaminati sotto /private/tmp; loghi/avatar mancanti sono la limitazione intenzionale delle fixture senza immagini reali. Comandi, cause e registro completo nel [rapporto Questura](../questura-audit-2026-10-06.md).

Launcher unico tests/Isolation/run.py, PHP 8.3.33 / PHPUnit 10.5.38 / MySQL 8.0.36. Runtime finale `/private/tmp/schedine-test-wsqpof3c`, DB `test_geo_ab2a8344a832d8f09286e6b43fc6a493`, MySQL71038/58212, HTTP71047/58213; identità/riconnessione/23 rifiuti override e build/GEO PASS. Runtime RED 4uardoel e mirato 0a4t6nlm rimossi, come quello finale; risorse fermate. Nessun Laravel/browser su server/DB reali, nessun SOAP esterno. Limiti operativi S, legacy, race multiprocesso e possibile bonifica log storici preproduzione invariati; nessuna nuova attestazione reale.

Esattamente 8 file prodotti: aggiornati QuesturaExportController (solo index), Blade Questura e Struttura, rapporto e Maestro; nuovi QuesturaUxTest.php, questura-ux.playwright.spec.js e questura-ux-fixtures.php. Altri 45 dei 50 preesistenti byte-identici al preflight, compresi Handler e tutti i test precedenti; nessuna eliminazione o sanamento. 142 migrazioni storiche e 6 entry point/guardie invariati a HEAD. Main, HEAD/origin/main locale 3eb559cfea9fed6a20e7949037f1d5a0236695be, ahead/behind 0/0, nessun fetch, staging vuoto; 19 tracked modificati e 34 untracked (prima 19/31).

CODICE/TEST/DOCUMENTAZIONE SÌ; CONFIGURAZIONE/SCHEMA APPLICATIVO/DATI REALI NO; TEST ESEGUITI SÌ; COMMIT/PUSH/DEPLOY/TRASMISSIONI REALI NO. Nessuna .env reale, DB/migration reali, cancellazione PMS o ispezione/modifica log operativi. **STOP finale: attendere la revisione pre-commit autorizzata; nessun add/commit/push/deploy eseguito.**


## Appendice U — C1: guardrail globale e accettazione esclusivamente locale, 07/10/2026

**QUESTURA C1 LOCALMENTE PRONTO PER REVIEW**, nello scope guardrail richiesto; **BASELINE IN AUDIT** invariato. Supera la semantica precedente OFF→simulazione, non le evidenze storiche Q1/Q2/Q3. [Rapporto e inventario esatto](../questura-audit-2026-10-06.md), sezione iniziale.

Fetch locale: main HEAD/origin/main f742ff8d00e4fdd31235a75cad4fddf9205660cc, 0/0, workspace inizialmente pulito. Difetti riprodotti RED26/27,25failure: OFF restituiva simulazione e POST Test persisteva un tentativo. Nuova eccezione tipata fissa e guardia riusabile prima dei quattro metodi service e makeClient; controllo controller prima della persistenza, archiviazione tabelle e acquisizione/finalizzazione ricevute. OFF reale/simulazione/web/JSON/console: rifiuto, zero client/WSDL, nessun nuovo esito/record/reserva o modifica Schedine/ricevute/export/filesystem. JSON409 minimizzato, web redirect con errore; ON solo doubles, comportamento consentito conservato. CLI esistenti sono audit/retention senza trasporto. Q1/Q2/Q3 invariati; setup dei test operativi ON esplicito, verifiche sostanziali intatte.

Run con migration **226/3182 PASS**; finale ampliato **235/3621 PASS**, **4 browser**, **5 guardrail**, **14 SOAP offline**, **20 PHP lint**, **Pint tre nuovi PHP**, diff-check PASS. Non sommare run sovrapposti, non certificare suite globale gestionale. Solo runtime locale isolato, MySQL8.0.36/PHP8.3.33, fixture sintetiche e SOAP doubles; identità/23 rifiuti override/build/GEO PASS, risorse rimosse. Acceptance migration dal vero schema legacy100/191: sette strutture, password cifrata decifrabile, WSKEY null, storico preservato, down conservativi non ripristinano schema precedente, conversione rieseguibile. Migration/config/Handler byte-identici; MariaDB target da verificare separatamente.

**LOCAL ACCEPTANCE → GITHUB → ROCKY/SPANEL DEPLOY ACCEPTANCE → PRODUCTION**. Bubblewrap e sei prove sono gate obbligatorio del deploy Rocky, non ostacolo al lavoro locale. Nessun controllo indebolito, produzione non pronta/certificata. Audit del destinatario e intera release, lock durante transizione, backup/restore, OPcache/processi e schema restano separati e pendenti.

19 file esatti nel rapporto; locale main C1 invariato,0/0,staging vuoto, modifiche intenzionali non committate. Maestro aggiornato dopo prove positive. Codice/test/documentazione SÌ; config/schema applicativo/dati reali NO; test SÌ; accesso SPanel/commit/push/deploy/Questura reale/ISTAT NO. Fermarsi per review, nessuna nuova fase implicita.


## Appendice V — Finalizzazione OFF corretta; STOP per generazione TXT mutante, 07/10/2026

**QUESTURA C1 NON PRONTO**, BASELINE IN AUDIT invariato. Supera la disponibilità alla review di U nello scope del nuovo contratto zero side effect con OFF; non invalida Q1/Q2/Q3. [Rapporto completo](../questura-audit-2026-10-06.md), prima sezione.

Locale main C1 f742ff8d00e4fdd31235a75cad4fddf9205660cc su HEAD/origin dopo fetch,0/0,staging vuoto,stessi19file. Riproduzione HTTP Web/JSON di finalizeTransmission con fixture valida: OFF chiamava retention, eliminava TXT/payload, modificava export/trasmissione/eventi. RED43/717 con2failure attese. Guardia preesistente riusata dopo controllo tenant e prima di finalizzazione; nessun redesign. Quattro regressioni OFF/ON Web/JSON nello stesso test guardrail: retention non invocata, DB/file identici OFF, ON finalizza offline. Mirati64/878 PASS, solo runtime locale isolato/MySQLeffimero, risorse rimosse; niente rete reale.

Scansione successiva trova P1 aggiuntivo rispetto al requisito esteso: downloadPeriodo/downloadSchedina non controllano OFF e chiamano storeExport, che scrive TXT, crea export e aggiorna metadati Schedine. Prova statica, non bypass SOAP. La precedente ammissione delle attività locali non soddisfa il nuovo contratto. STOP senza ampliare il fix: proposta minima guardia sui due POST e prove OFF Web/JSON da autorizzare. Nessuna nuova suite completa o browser dopo STOP; risultati U restano precedenti. Lint2PHP e Pinttest PASS; Pintcontroller FAIL per stile già presente anche prima del fix,non corretto con formattazione estesa; diff-check PASS.

Solo quattro dei19file ulteriormente modificati: controller,testguardrail,rapporto,Maestro. Altri15preservati; config/HandlerQ3/migration immutati. Nessun file aggiunto,staging vuoto,commit/push/deploy/accessoSPanel/DBoperativo/Questurareale NO. Bubblewrap/Rocky e accettazione target produzione pendenti, non requisito del lavoro locale. Fermarsi e riferire il P1 aggiuntivo prima di nuova correzione.


## Appendice W — Export OFF corretti; STOP sul middleware audit operativo, 07/10/2026

**QUESTURA C1 NON PRONTO**, BASELINE IN AUDIT invariato. Supera V solo per i due export ora protetti; nuovo P1 dimostra che OFF non è ancora zero scritture DB. [Rapporto e mappa completa](../questura-audit-2026-10-06.md), sezione iniziale.

Preflight locale main HEAD/origin/main f742ff8d00e4fdd31235a75cad4fddf9205660cc dopo fetch,0/0,staging vuoto,stessi19file; altri15coerenti rispetto ai quattro precedentemente aggiornati. RED valida51/784 con4failure attese su periodo/singola Web/JSON: payload,TXT,export e metadati Schedina prodotti con OFF. Prima strumentazione51/776 con6failure includeva2errori di aspettativa dello spy,corretti solo nel test. Fix minimo riusa guardia prima di build/storeExport; ON mantiene TXT anche con Accept JSON. Due soli caller del privato storeExport Questura, entrambi ora protetti; omonimo ISTAT distinto,non modificato. Finalizzazione già protetta conservata.

Audit trasversale app/routes/controller/service/CLI/model/middleware: nuovo P1 in LogOperativeAudit (registrato nel webKernel): dopo next() scarta solo status>=400, per POST questura.* e utente con struttura inserisce StrutturaAuditLog. Il redirect OFF Web302 quindi persiste struttura_audit_logs; JSON409 è escluso. Prova statica completa,nessun bypass SOAP. I test precedenti non coprivano questa tabella. STOP senza modificare middleware; proposta da autorizzare: distinguere rifiuto tipato nella risposta ed escludere solo quel caso dall'audit,senza disabilitare audit generale.

Mirati72/960 PASS su guardrail+retention e sei tabelle selezionate/filesystem,non includono tabella audit. Runtime locale isolato/MySQL8.0.36,identità/23rifiuti/build/GEO PASS,risorse rimosse. Lint2PHP,Pinttest,diff-check PASS; PintcontrollerFAIL preesistente,verificato su copia pre-fix byte-identica al preflight e confronto delle copie formattate: nuove righe conformi,nessun restyling reale. Nessuna nuova suite completa/browser/guardrail/SOAP/migration dopo STOP; risultati precedenti restano storici. Rocky/SPanel/Questura reale/production readiness non attestati.

Sempre19file,solo controller/testguardrail/rapporto/Maestro ulteriormente modificati,altri15preservati. Config/HandlerQ3/migration/dipendenze invariati. Staging vuoto,commit/push/deploy/SPanel/DBoperativo/Questura reale NO. Nessuna nuova correzione implicita,fermarsi per autorizzazione del P1 aggiuntivo.


## Appendice X — P1 audit OFF corretto e review finale locale C1, 07/10/2026

**QUESTURA C1 LOCALMENTE PRONTO PER REVIEW FINALE**, BASELINE IN AUDIT invariato. Supera W per il writer middleware del rifiuto; non certifica produzione o intero gestionale. [Rapporto, catena middleware/listener, comandi ed esatto inventario20file](../questura-audit-2026-10-06.md), sezione iniziale.

Fetch main HEAD/origin/main f742ff8d00e4fdd31235a75cad4fddf9205660cc,0/0,staging vuoto,19file preservati. RED reale kernel: OFF Web302 crea audit1 invece0; JSON409 audit0. 53/829 con1failure attesa. Fix minimo: marcatore semantico nella risposta Web dell'eccezione tipata; LogOperativeAudit esclude soltanto questura.* con quel marcatore response. Nessuna logica flag duplicata, matching testo o esclusione generale302. Header client non aggira audit, POST nonQuestura con redirect e QuesturaONoffline restano auditati. OFF Test/Send/tabelle/ricevute/finalizzazione/export preservano audit, sette tabelle di dominio e file; zero client/WSDL.

Audit tutti middleware della catenaweb/auth/global, provider/bootstrap, hook modelli, eventi/listener/observer/job/notification/subscriber/terminazione: nessun altro writer applicativo successivo al rifiuto individuato. Sessioni/cookie/flash errore fisso e contesto tecnico distinto dalle operazioni persistenti Questura; non imposto zero scritture fisiche framework. Nessun nuovo P0/P1 distinto nello scope verificato.

Mirati76/1044PASS; conclusiva **252 test/3.857 asserzioni PASS**, inclusa accettazione4migration effimera, **4browser/5guardrail/14SOAPoffline/17PHP lint PASS**, Pint3nuoviPHP PASS e diff-check PASS. Run sovrapposti non sommati. Pintcontroller/middleware segnalano esclusivamente debiti preesistenti, provati tramite confronto copiepre/post formattate fuori repository; nuove righe conformi, nessun restyling reale. Runtime finale mb6eyle8/DB test_geo_b026672dfc5246e9107ae72b19c386bb/MySQL18821:60744/HTTP18826:60745 rimosso; PHP8.3.33/MySQL8.0.36, identità/23rifiuti/build/GEO PASS. Nessun test Laravel operativo né SOAP reale.

20file esatti: nuovo nello scope solo LogOperativeAudit, necessario alP1; altri14 dei19iniziali byte-preservati, eccezione/controller/testguardrail/rapporto/Maestro aggiornati. Config/HandlerQ3/4migration/4manifest-lock inalterati aHEAD. Nessun segreto/dato reale/endpoint reale improprio aggiunto. MainC1/0/0/stagingvuoto; nessun commit/push/deploy/SPanel/produzione/DBoperativo/.env reale/Questura reale. Bubblewrap e sei prove Rocky, targetMariaDB, preflight/transizione/backup/processi restano gate separati obbligatori pendenti. Fermarsi alla review locale, nessuna fase successiva autorizzata.

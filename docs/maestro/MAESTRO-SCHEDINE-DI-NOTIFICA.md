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


## Appendice Y — Avvio fase maestra ISTAT, 07/10/2026

**AUDIT INIZIALE PARZIALE — IMPLEMENTAZIONE NON AVVIATA**, BASELINE IN AUDIT invariato. [Evidenze, fonti e lacune](../istat-master-2026-10-07.md). Baseline main HEAD/origin dopo fetch7cdfd6e1cf84cbb0711faf3f98b8bb78007c23fd,0/0,worktree inizialmente pulito. QuesturaC1 fuori scope e invariato. Allegato ricevuto termina nel punto20 dopo «rettifica;»; richiesta parte restante e conferma del perimetro regionale, nessun requisito finale inventato.

Ricontrollate fontiRegione: Ross1000SOAP/uploadXML-TXT/portale, BasicAuthWS distinto dall'accessoSPID. WSDLpubblico SHA5ee868bda0ffcd34c5eff41bbc9bf0c398903d93a0407f00ec6c1804d47bc309 identico alreference; XML2.4 ancora collegato daRegione, v3/18marzo2026 pubblicata dalfornitore distinta, adozioneER non presunta. Demoportale non prova WStest. Rilette dueCSV aggregati in sola lettura: anomalie aperture e totalepartenze confermate, nessuna copia nei test.

Lacune preliminari: protezione segretoISTAT/form/flash, simulazione interna, URL/guardtrasporto, idempotenza/prenotazione atomica, parserrisultatiSOAP, consegnamanuale e rettifiche/snapshot. Non corrette e non dichiarate concluse. Baselineisolata **32test/146asserzioni con1errore** nel confrontoQuestura: fixturearrivo31marzo fuori finestraC1oggi/ieri, confronto nonraggiunto. Nessun indebolimento Questura; fixturecompatibile da chiarire. Runtimez4bnnc2g fermato/rimosso,DBoperativo nonconsultato,nessunrealeinvio.

Solo rapporto e Maestro modificati documentalmente. Nessun codice/test/config/schema applicativo modificato; nessun commit/push/deploy/SPanel/produzione/.env reale. CircuitoISTAT resta PARZIALE, nessuna readiness o accettazione reale.


## Appendice Z — ISTAT Emilia-Romagna / Ross1000: implementazione locale verificata, 07/10/2026

**IMPLEMENTAZIONE LOCALE VERIFICATA — DA REVISIONARE**, progetto **BASELINE IN AUDIT**. Supera l’avvio parziale Y: ricevuti i requisiti 20–46 e confermata Emilia-Romagna. [Rapporto completo, fonti, mapping, writer e inventario](../istat-master-2026-10-07.md). Nessuna readiness produzione o accettazione reale.

Baseline main, HEAD/origin dopo fetch iniziale `7cdfd6e1cf84cbb0711faf3f98b8bb78007c23fd`, 0/0, nessun nuovo commit o staging. Scope ISTAT: credenziali cifrate, non ripopolate né flashate; configurazione dedicata; simulazione operativa rimossa; guardrail globale OFF di default e endpoint regionale fisso. Unico generatore XML/XSD per preview/file/SOAP, confronto hash prima dell’invio, copie cifrate e fingerprint minimizzati; prenotazioni struttura/giorno con lock e vincoli; parser SOAP correlato, storico ed eventi. HTTP 200 non prova accettazione: processed conferma soltanto i record, non il calendario. Timeout/incompletezza → uncertain; pending dopo crash resta bloccato; retry solo per disabled/not_delivered.

Fallback ufficiale: upload XML sul portale e dichiarazione manuale esplicita. Nessuna email o receipt API inventata. Modifiche di ospiti/soggiorni, anche comunicati nel mese precedente, richiedono verifica/rettifica sul portale; storico originale conservato, nuova anteprima e controllo cronologico. Nessuna modifica PMS per rettificare. Retention a 30 giorni tecnica, non legale; comando predefinito senza scritture, --apply conserva pending/incerti/partial non riconciliati e legacy non riconoscibili. Una nuova migration ISTAT, applicata solo nei DB effimeri; down conservativo, rollback operativo separato.

Conclusiva **331 test / 4.499 asserzioni PASS**, **2 browser ISTAT + 4 browser Questura originali PASS**, race di prenotazione con due processi e due connessioni MySQL PASS; **5 guardrail / 22 privacy ISTAT / 14 SOAP Questura**, sintassi PHP, Pint sui nuovi PHP e diff-check PASS. Questura iniziale 134/2748 PASS. Baseline ISTAT32/146 con errore dovuto al clock della fixture: corretto soltanto nella prova ISTAT, mantenendo asserzioni e reset finally. Tutti dati sintetici e doubles, runtime isolati fermati/rimossi.

**Suite globale NON PASS**: copia HEAD con 316 casi, 17 errori e 83 failure; prima candidata con 329 casi, 16 errori e le stesse 83 failure. Seeder esclusi per sicurezza, fixture legacy incomplete, Example obsoleto, Smoke basato su dati preesistenti; prova DDL Questura contaminante se eseguita a metà suite. Nessun test Questura o globale modificato per nascondere fallimenti. La suite compatibile esclude esplicitamente cinque classi legacy e colloca la prova DDL per ultima. Non certificare la suite globale verde.

50 file Questura protetti e 146 migration storiche byte-identici; sole sezioni ISTAT nei file condivisi Struttura/routes, regressione Questura originale PASS. 32 file locali (20 tracked/12 nuovi), nessun artefatto temporaneo. Rapporto del 5 ottobre marcato storico. .env reale, dipendenze, Handler Q3, middleware audit e Questura invariati. Nessun DB operativo, SPanel, produzione, email, commit/push/deploy o trasmissione ISTAT/Questura reale. Rocky, Bubblewrap, sei prove obbligatorie e real acceptance restano gate separati pendenti. Fermarsi alla review locale.

Anteprima ISTAT conclusiva dai campi dell’XML validato, risposta private/no-store; riconciliazione con procedura da elenco chiuso, operatore e data. Totale ISTAT 59 casi; evidenza conclusiva 331/4499 PASS. Nessuna nuova prova o accettazione reale dedotta.


## Appendice AA — ISTAT P1/P2 corretti, pronto per re-audit, 07/10/2026

**ISTAT CORRETTO — PRONTO PER RE-AUDIT**, BASELINE IN AUDIT invariato. [Causa, fix e regressioni](../istat-master-2026-10-07.md#o-correzione-controllata-p1p2-dopo-audit-indipendente). Supera Z soltanto nello scope dei difetti trovati dall’audit; nessuna readiness produzione o accettazione reale Ross1000.

P1: una vecchia riconciliazione oscurava anche la seconda comunicazione sullo stesso export riutilizzato. Validator basato sulle comunicazioni attive del tenant/export: non riconciliate, non verify, non disabled/not_delivered. La riconciliazione libera soltanto la comunicazione interessata. Permanenti A/B/C: modifica consentita dopo prima riconciliazione, bloccata dopo nuovo Send sul medesimo export, nuovamente consentita dopo seconda riconciliazione; payload originale preservato. Tre stati processed/uncertain/partial; D/E nessuna interferenza di comunicazione/riconciliazione estranea. Stati incerti/parziali della nuova fixture assegnati sinteticamente; nessun esito reale simulato come accettazione.

P2: manual nello storico etichettato Consegna sul portale; retry conserva origine A, registra pending/finalizzazione del nuovo tentativo B usando l’evento pending esistente. UI distingue Origine/Tentativo. Nessuna nuova migration o modifica model; test A→B e colonna Tipo.

RED valido33/239 con5failure attese; corretta la sola nuova fixture che inizialmente poneva l’arrivo fuori periodo XML. Finale **337/4562 PASS**, inclusi65ISTAT, concorrenza due processi/connessioni, **2browserISTAT+4browserQuestura**, **5guardrail/22sicurezzaISTAT/14SOAPQuestura**, sintassi/Pint sui due nuovi PHP modificati/diffcheck PASS. Launcher unico tests/Isolation/run.py; tutti DB/processi HTTP effimeri fermati/rimossi, fixture sintetiche e doubles.

Sette file ulteriormente aggiornati, nessuno aggiunto: validator storico, operation service, controller ISTAT, Blade ISTAT, test Cycle, rapporto e Maestro. Inventario20tracked/12nuovi=32, main HEAD/origin locale7cdfd6e1cf84cbb0711faf3f98b8bb78007c23fd,0/0,stagingvuoto. 50Questura/Q3 e146migration storiche invariati, nessun default trasporto alterato.

Suite globale NON PASS: audit precedente HEAD Feature316/17errori/83failure, candidato343/16errori/86failure; tre nuovi fallimenti Questura da diagnosi separata e classe PHPUnit assente ComponentiImportReviewStatusTest su entrambi. Non corretti, non rieseguiti in questa fase; nessun test/fixture/seeder/ordine configurato/bootstrap globale modificato per ottenere verde.

Commit/push/deploy/SPanel/produzione/.env reale/DB operativo/trasmissioni reali NO. Rocky/Bubblewrap, sei prove obbligatorie e accettazione ufficiale restano pendenti. Fermarsi per revisione indipendente del diff corretto prima del commit.


## Appendice AB — Harness Rocky/MariaDB, gate bloccato, 07/10/2026

**HARNESS ROCKY ANCORA BLOCCATO**, BASELINE IN AUDIT invariato. [Evidenza e protocollo aggiornato](../DEPLOY_SPANEL.md#adeguamento-harness-rockymariadb--07102026). Produzione usa realmente MariaDB11.8.9; Bubblewrap0.10.0 ora disponibile, supera la precedente evidenza storica di assenza.

Adeguamento esclusivamente infrastruttura/test: vendor MySQL/MariaDB esplicito, identità prima DDL e PDO, SHA faef3cf fisso, testing/allowlist/denylist, server_id/socket/porta/datadir/processo propri; UUID MySQL preservato. Percorso canonico e Bubblewrap invariati.5 policy DB+22 policy Rocky PASS localmente e sul server;5 guardrail locali PASS. Nessun DB effimero creato o accettazione applicativa eseguita: i positivi sono sintetici.

Blocco aggiuntivo: PHP8.3.35 server con configurazione standard va in segmentation fault durante lint del supporto test, due riproduzioni; php -n -l e lint locale passano. Causa da diagnosticare, nessuna modifica runtime autorizzata/eseguita. Clone canonico pulito ma SHA d90b60cd diverso; non sostituito, evidenze preservate. Bundle harness preparato separatamente fuori dai checkout.

Produzione c21b9185a33d1f5e0720c08f1547af6fa4857b20 e origin/main faef3cf invariati, candidato separato faef3cf senza diff; sole SELECT/SHOW DB. Nessun file applicativo modificato, commit/push/deploy, .env operativo/DocumentRoot, DB operativo, build/migration o trasmissione reale. Le sei prove restano pendenti e richiedono un gate separato.


## Appendice AC — Sei prove Rocky/Bubblewrap PASS, 07/10/2026

**SEI PROVE ROCKY/BUBBLEWRAP PASS — PROCESS ISOLATION ROCKY/BUBBLEWRAP VALIDATA**. Launcher originale del commit faef3cf01f43d19b20d1558c1fbd3ae32e48d849, eseguito come tanggosoftware da /home/tanggosoftware/deploy-rocky-test con Bubblewrap0.10.0:6 test in1.751s, zero skip/errori/fallimenti, exit0. Lock, descrittore non ereditato, segnali SIGINT/SIGTERM/SIGHUP, daemon separato, doppio fork con TERM ignorato e SIGKILL PASS. I singoli unittest risultano ok; il codice0 è del launcher aggregato, non sei processi indipendenti.

Il launcher attesta namespace PID/mount/net/user distinti e nessun marker di violazione; filesystem operativo non montato, rete separata, nessun PHP/DB nel percorso. La sandbox minima verificata prima del gate aveva zero route esterne. Nessuna richiesta reale eseguita.

Clone precedente conservato integralmente e reversibilmente in /home/tanggosoftware/tmp/rocky-clone-preservato-ewyendjv/clone-originale, SHA d90b60cd84df183b0ccb49c924df33bfa442a8a1; metadati in preflight.json e output in sei-prove.log nella stessa directory privata. Clone canonico ora detached a faef3cf, remote GitHub joomlu/Schedinedinotifica, owner applicativo, mode0700, senza.env, worktree pulito/stagingvuoto/untrackednessuno, zero .rocky-process-* residui.

Produzione ancora c21b9185a33d1f5e0720c08f1547af6fa4857b20, worktree pulito e hash.env prima/dopo identico. Nessuna modifica DB/.env/DocumentRoot/cron/queue/PHP/SPanel, commit/push/deploy o trasmissione. Error_log del candidato applicativo temporaneo preservato separatamente, non copiato nel clone canonico.

Questo supera soltanto la precedente pendenza delle sei prove di process isolation. Non certifica Laravel/PHP/migration/MySQL/MariaDB/ISTAT/Questura, non autorizza deploy e non dichiara produzione pronta. Diagnosi PHP e accettazione applicativa MariaDB restano gate separati pendenti; harness locale adattato non utilizzato per queste sei prove.


## Appendice AD — Diagnosi PHP CLI Rocky, 07/10/2026

**CAUSA PHP IDENTIFICATA — PHP CLI DIAGNOSTICATO, PRONTO PER DECISIONE**. Nessuna correzione applicata. Interazione minima riproducibile di ionCube Loader15.5.1 e Zend OPcache8.3.35 con opcache.enable_cli=1: runtime normale3/3SIGSEGV (subprocess -11, equivalente shell139), php-n3/3PASS; runtime completo senza ionCube3/3PASS; minimo senza ini con entrambi Zend loader e enable_cli1:3/3SIGSEGV. Entrambi con enable_cli0:3/3PASS; normale con solo override enable_cli0:3/3PASS; normale con jit0:3/3SIGSEGV. Baseline con solo loader ionCubePASS, minimo ini ottenuto dal bisect01-ioncube_loader.ini+10-opcache.ini. Crash generale anche su PHP sintetico minimale, index.php, Struttura.php, QuesturaWebService.php e TestingEnvironment.php candidato; nessun file sorgente modificato.

Binario /usr/bin/php risolve /opt/remi/php83/root/usr/bin/php, PHP8.3.35CLI; ini /etc/opt/remi/php83/php.ini, scanned /etc/opt/remi/php83/php.d. Inventario completo letto con php--ini/php-m; JIT tracing con buffer0, nessun Xdebug/SourceGuardian rilevato. Altri moduli censiti includono imagick3.8.1, redis6.3.0, igbinary3.2.16, msgpack3.0.1, memcached3.4.0, mcrypt1.0.9 e mysqlnd8.3.35. FPM8.3.35 -i carica gli stessi ini/loader di base; nessuna prova su richieste FPM/pool e coinvolgimento non dimostrato. Journal/coredump metadata non accessibili come tanggosoftware; causa interna a livello di simboli/stack non identificata. Non usare root o modificare sistema per eludere questo limite.

File harness remoto SHA2560e55bbeb7b25f6fbaaff2e7d7b7fdc07f06ea1d71203891714e70910cc1d384e coincide con supporto locale adattato; file originale canonico SHA256552c98fe26208fcf4b77258c13590af71b7ac363cece3be028f1df18de67a0a7 coincide byte per byte con faef3cf. Configurazioni bisect e file diagnostici privati temporanei eliminati dal relativo TemporaryDirectory; coredump disabilitato solo nei figli diagnostici tramite limite processo, nessuna configurazione globale modificata. Error_log precedente preservato.

Modifiche documentali post-Rocky erano solo nel repository locale, non committate; operativo main c21b9185a33d1f5e0720c08f1547af6fa4857b20 e canonico detached faef3cf puliti. Hash.env operativo ancora identico al preflight delle sei prove. Nessuna operazione DB, Laravel, migration, Composer/build, sei prove ripetute, configurazione PHP/.env/DocumentRoot/cron/queue/SPanel, commit/push/deploy o trasmissione. Decisione separata richiesta: minimo override opcache.enable_cli0 solo runtime test da autorizzare e validare, oppure soluzione provider per compatibilità loader/OPcache; nessuna opzione applicata permanentemente.


## Appendice AE — Harness MariaDB P1/P2 corretto, pronto per re-audit, 07/10/2026

**HARNESS MARIADB CORRETTO — PRONTO PER RE-AUDIT**, non ancora approvato per uso sul server. Audit precedente: l'identità concordante consentiva3306, traversal, PID assente e server_id0. Policy ora separa risorsa autorizzata e confronto: porta49152..65535 con denylist3306 e porta operativa aggiuntiva se fornita, denylist socket operativo e DB operativo, path assoluti canonici risolti e senza symlink/traversal, root temporanea privata0700 del medesimo utente. PID positivo, supervisore corrente, UID, eseguibile e argv completi attestati tramite process snapshot prima del DDL; broker e verifiche PHP prima PDO preservati/rafforzati. server_id1..4294967295 su entrambi i lati, casuale nel launcher, non prova autonoma. MySQL mantiene auto.cnf e UUID verificato.

Vendor riconosciuto positivamente mediante marker esclusivi MySQL/MariaDB sia sul binario sia sulla versione/commento della connessione: sconosciuto o ambiguoBLOCK, nessun fallback elseMySQL. Semantica Python/PHP confrontata tramite prova senza bootstrap applicativo.

Regressioni permanenti:10 test policy con sottocasi per tutti i P1, ambienti/DB/SHA/vendor/config, porta/socket/datadir operativi anche concordi, traversal/symlink, PID assente/zero/negativo, parentela/UID/eseguibile/argv errati, server_id zero/fuori range/mismatch e positivi sintetici MySQL/MariaDB;21 test isolamento originali e5 guardrail PASS. Path launcher errato coperto dalla policy originale Rocky. Lint Python e PHP con opcache.enable_cli0, diff-checkPASS. Nessun DB creato o contattato; i positivi usano snapshot di processo sintetici e non certificano un'istanza reale.

Escluse le modifiche SHA a check_rocky10.py e relativa regressione: entrambi ripristinati byte per byte a faef3cf. Sei prove certificate e relative protezioni non alterate. Bundle locale ora6file: tests/Isolation/run.py, database_policy.py, test_database_policy.py, tests/Support/TestingEnvironment.php, questa guida e Maestro (4trackedmodificati+2nuovi). APP_BYTES_CHANGED=NO su1831file controfaef3cf.

Nessun accesso server, copia harness, commit/push, DB operativo/temporaneo, migration, Laravel, deploy o trasmissione reale. Mitigazione PHP soltanto per processo di lint/test; nessuna configurazione globale modificata. Accettazione MariaDB reale ancora NON eseguita; attendere re-audit indipendente.


## Audit IDS Bellaria Igea Marina — 2026-10-07

Fonte specialistica subordinata: [rapporto imposta di soggiorno](../imposta-soggiorno-bellaria-audit-2026-10-07.md). Baseline locale main/HEAD `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`; sola audit/documentazione, nessuna correzione applicativa.

**TASSA DI SOGGIORNO BELLARIA — DA CORREGGERE.** Nessun P0 dimostrato; sei P1 riprodotti (default periodo, età retroattiva, mapping esenzioni/residente, mese del riepilogo riversamento, notti777 perse nel CSV, storico riscritto) e un P1 statico (default tariffa/tetto indipendente da categoria e cumulativo camper assente). P2 e limiti nel rapporto. Modulo **PARZIALE**, conformità non certificata.

Fonti comunali pubblicate recuperate: regolamento CC22/2025, GC185/2024 e dispositivo indicizzato di conferma2026. Ordine del giorno28/07/2026 contiene modifica IDS, ma approvazione/testo/decorrenza non recuperati; CC33 è TARI, non prova IDS. Coordinato2026 e allegatoC integrale pendenti. File altro software accettato non individuato negli allegati/repository: percorso richiesto, confronto empirico BLOCCATO. Nessuna API STAYTOUR inventata.

Prove nel launcher isolato da copia esterna, MySQL8.0.36/PHP8.4.3: **24 test / 84 asserzioni, zero failure/error, una deprecazione**, comprendenti12 diagnostici sintetici temporanei e suite unità esistenti. Prove diagnostiche del comportamento attuale, non fix né certificazione normativa. Passaggio intermedio24/83 con aspettativa erronea sulle date invertite documentato e corretto soltanto nel diagnostico esterno. Browser esistente con fixture sintetica: **1 FAIL**, HTTP419 al login prima del modulo; causa non dimostrata, acceptance UI BLOCCATA. Rendering e scenari tenant/CSV/stampa nel kernel HTTP verificati nello scope del rapporto; matrice completa non dichiarata.

**APP_BYTES_CHANGED=NO** su1.831 file;50 protetti Questura, test Questura/ISTAT e147 migration attuali invariati. Harness MariaDB e DEPLOY_SPANEL preesistenti preservati; nuovo rapporto e questa appendice sono le sole modifiche della task. Nessun DB operativo, produzione, trasmissione, commit/push/deploy. Stato generale **BASELINE IN AUDIT** confermato. Gate successivo richiede autorizzazione separata alle correzioni; lacune normative/empiriche restano esplicite.


## Chiusura documentale IDS Bellaria — 2026-10-07

**DOCUMENTAZIONE BELLARIA ANCORA INCOMPLETA**, BASELINE IN AUDIT invariato. [Rapporto specialistico aggiornato](../imposta-soggiorno-bellaria-audit-2026-10-07.md#chiusura-documentale-pre-correzione--7-ottobre-2026). Superate le precedenti pendenze di recupero: CC29/28.07.2026 approvata14favorevoli/0contrari/1astenuto, coordinatoA e comparativoB; modifica soltanto artt.7/15, rimuove Modello21, non altera calcolo/esenzioni. Immediata eseguibilità nel dispositivo, esecutività07.09.2026 nella scheda: non confondere con efficacia fiscale subordinata a pubblicazione ministeriale, data non dimostrata. GC184/06.11.2025 e AllegatoC integralmente recuperati: conferma tariffe2026, hotel2stelle1€ e3stelle1,50€; limiti6consecutivi,10campeggi,10mensili camper, stagione giugno–settembre confermati.

CSV utente esterno ora disponibile e analizzato senza pubblicare dati personali: SHA256899d06781cca3161a60cd47147d3ba3c28cb91a2ffd3b9b9cb63b09d08968fe6,34213byte,ASCII senzaBOM,CRLF finale,553righe dati8campi.209righe777 con1146notti positive; il writer locale perde le quantità. Semantica ufficiale777 non ancora dimostrata: nessuna correzione writer autorizzata. Il campione di giugno include partenze luglio e segue arrivo; non trasferire il criterio normativo del riversamento al CSV senza specifica. Accettazione del campione riferita, non verificata mediante ricevuta/caricamento.

Restano criterio età/compleanno, decorrenze fiscali puntuali, dizionario tracciato/codici/raccordo flusso e riversamento, mapping tariffarioRTA/villaggi. Matrice7P1 nel rapporto distingue regole dimostrate e NON DIMOSTRATO. Requisito snapshot registrato immutabile/rettifica definito, non implementato. Nessuna readiness o conformità certificata. Precedenti limiti browser419 e configurazioni reali persistono. Solo rapporto e Maestro aggiornati; harness/DEPLOY_SPANEL preesistenti preservati, applicazione/Questura/ISTAT/migration e CSV originali invariati; nessun testLaravel, DB/server, correzione, commit/push/deploy o trasmissione reale.


## Chiusura definitiva fonti IDS Bellaria/STAYTOUR — 2026-10-07

**BELLARIA/STAYTOUR — SERVE CONFERMA UFFICIALE MIRATA**; BASELINE IN AUDIT e7P1 non corretti. [Aggiornamento specialistico](../imposta-soggiorno-bellaria-audit-2026-10-07.md#chiusura-definitiva-fonti-pubbliche--7-ottobre-2026). Ricerca ampliata sualbo/Comune/sportello/FAQ/portaleoperatore/fornitore e querytracciati; nessun dizionario777 pubblico recuperato. Analisi integraleCSV:209/209righe777=durata−6 a tariffa0;205gruppi non ambigui hanno base6+777=durata,188baseordinaria/15minori/2invalidi. Due chiavi ambigue contengono coppie duplicate: non certificare identità degli ospiti o deduplicare. Semantica empiricamente dimostrata nel campione, codice ufficiale non documentato; writer ancora bloccato.

Nuova discordanza pubblica: sportelloIDS indica comunicazione/pagamento fino16, regolamentoentro15giorni; non inventare proroga o equipararli. Competenza finanziaria mesefine è determinata, criterioCSV empiricoarrivo e raccordo da chiarire. Classificazione regionaleRTA2–4stelle e villaggi2–5stelle individuata, senza prova tariffariaBellaria per queste voci. GC184schedaesecutività02.12.2025 e dispositivoimmediatoeseguibile; CC29dategiàdocumentate; efficaciafiscale puntuale resta non dimostrata. Età/compleanno resta priva di istruzione esplicita; proposta conservativa di blocco dell'elaborazione automatica nei casi incerti separata dalla norma e non implementata.

Matrice7P1 aggiornata con DIMOSTRATO/DIMOSTRATOEMPIRICAMENTE/NONDIMOSTRATO e sottoinsiemi correggibili senza supposizioni; nessuna autorizzazione alfix. Preparate5domande tecniche/normative mirate aIDS/Hyksos, non inviate. Solo rapporto eMaestro modificati; originalecsv, applicazione, Questura/ISTAT, migration e harness preservati. Nessun DB/server/testLaravel, commit/push/deploy o trasmissione reale. Nessuna readiness/accettazione nuova dichiarata.


## Gate correzione IDS età/777 bloccato — 2026-10-07

**TASSA BELLARIA — CORREZIONE PARZIALE BLOCCATA**, nessuna applicazione modificata. Richiesta utente conferma operativamente esenzione fino al18°compleanno incluso e tassa dal successivo, prima parte6notti/777durata−6 anche peresenti; specifica pubblicaSTAYTOUR ancora non reperita. Percorso reale service→controllerCSV ricostruito, senza modificahelper condiviso.

STOP per dipendenza prevista dalla sezione12 della richiesta: dominio corrente usa notti nel periodo e tetto configurato.10notti/tetto10 richiederebbero cambiare limite/importo per imporre6+4; soggiorno31maggio–3giugno con periodoestivo rappresenta2notti su3, mentre requisito somma=durata richiede trattamento non specificato della notte fuori periodo. Limiti categoria e periodo sono esclusi; nessuna assunzione/nuovo codice/forzatura6 applicata. Dettagli e gate nel [rapporto](../imposta-soggiorno-bellaria-audit-2026-10-07.md#gate-correzione-mirata-età777--7-ottobre-2026).

Serve delimitazione esplicita della regola6 ai casi configurati pertinenti e trattamento delle notti fuori stagione o autorizzazione al comportamento escluso. Nessun fix parziale o test eseguito;7P1 ancora aperti, Questura/ISTAT e harness preservati. Solo rapporto/Maestro aggiornati. Nessun DB/produzione/commit/push/deploy/trasmissione. BASELINE IN AUDIT invariato.


## Fix controllato IDS età e quantità777 — 2026-10-07

**TASSA BELLARIA — CORREZIONI DOCUMENTATE PRONTE PER RE-AUDIT**, limitatamente alle parti autorizzate e verificate. BASELINE IN AUDIT invariato, nessuna certificazione generale. [Rapporto aggiornato](../imposta-soggiorno-bellaria-audit-2026-10-07.md#correzione-controllata-età-e-quantità777-su-dati-esistenti--7-ottobre-2026). Specifica localePDFutente sulDesktopSHA256d23d9f47e3c6db0a655bea911fca8201d40ba75e2a0cdf5d5f2bd6de12e0b2c7: codici400..440/777oltre6 espliciti, esempio8campi+separatorefinale; non è un download pubblico nuovo. Regola compleanno incluso confermata dall'utente, non dedotta dalPDF.

InventarioMySQLlocale inREADONLY senzaLaravel: codici400..450 presenti perID1/9/12,777solo1,28righeattive; treconfigvalorizzate tutte6/1.50/17–18/marzo–ottobre; nessun10reale.35Schedine/16componenti tutteNO: nessunmappinglegacy nei dati dimostrato; IDS03restaaperto. Nessun recordDB ricreato, seed/migration/modificaconfig.

ServiceBellaria calcola pernotte fino18°compleanno incluso, dalgiornosuccessivo tassazione; altreesenzioni prevalgono,400non permanente. Segmenti fiscali materializzati neiwriter, controlriepilogoconnotte_tassate. Quantità777nelCSV leggeeccedenza anziché0.10notti/compleanno13suarrivo10:400/0/777 con4/2/4,totale10/imposta3€. Notti1/5/6no777,7→1,10→4,20→14, imponibili/esentiPASS. RamoaltriComuni ehelperEtaOperativa condiviso invariati.

NuovoTassaBellariaEtaCsvTest9test, sintetici. GREENlauncherisolatoPHP8.3.33/MySQL8.0.36:79test/1113asserzioniPASS, comprensivoTassa/EtaOperativa/QuesturaTransportGuard/IstatTransmissionSecurity; risorse/23rifiuti/buildGEO PASS. Lint3PHP/Pint3/diffcheckPASS. REDcodiceprecedenteidenticoHEADcopiatofuoriGit,PHP8.4.3:9test/61asserzioni,5failure/1errore(segmentiassenti)/1deprecazione; runtime differente esplicitato. Nessun testesistente alterato, nessuna suiteglobale/browser.

IDS02corretto nelramoBellaria; IDS05corretto download perconfig6.777generale conlimiti differenti non risolto: conservati10eperiodo fuori stagione, nessuna forzatura6 o codiceinventato. ResiduiIDS01/03/04/06/07, data_regnow()noncorretta rispettoarrivo/partenza ammessi, storicoancoradinamico, RTA/villaggi/decorrenze/scadenza15–16, P2quoting/encoding. Confermaoperativa+CSV209/209 distinta da specificapubblica nonreperita. DuefileappTassa e nuovotest modificati, rapporto/Maestro aggiornati; Questura/ISTAT/migration/harness/DEPLOYpreesistenti eDBoriginali invariati. Nessun commit/push/deploy/produzione/trasmissione.


## Completamento controllato IDS Bellaria — 7–8 ottobre 2026

**TASSA BELLARIA — IMPLEMENTATA E PRONTA PER RE-AUDIT FINALE**, nel dominio implementabile con le fonti disponibili. Stato generale **BASELINE IN AUDIT** invariato, modulo parzialmente verificato. Nessuna conformità totale, production readiness o accettazione StayTour. Questa chiusura supera le pendenze applicative corrispondenti dei gate storici precedenti, senza cancellarne le evidenze. [Rapporto completo e inventario19file](../imposta-soggiorno-bellaria-audit-2026-10-07.md#completamento-finale-controllato-tassa-bellaria--78-ottobre-2026).

Preservati calcolatore comune, route/controller/pulsante e ricevuta esistenti, loghi e footer istituzionale. RicevutaA4 adattata con/senza immagine facoltativa, dettaglio canonico, totale e stampa; nessuna numerazione fiscale o dichiarazione di incasso inventata. Dal/Al e preset riutilizzano il calendario, validazione server e headerCSV dal periodo selezionato. Report annuali/intervalli sono interni, non dichiarazioni automaticamente ammissibili. Invariante quantità/importi Schedina=ricevuta=report=CSV esercitato su fixture sintetiche, incluse famiglie, esenti/compleanno,777, passaggio mese/stagione; notti fuori periodo distinte senza tassarle. Controllo interno riconcilia la fonte comune, non pagamenti o accettazione ente.

IDS-04: specifica consente arrivo o partenza; convenzioneBellaria **data_registrazione=arrivo**, evidenza553/553 nel campione fornito e regressioneclock2025/2030. QuotingCSV corretto,8campi logici/separatorefinale del writer; nessuna nuova certificazioneencoding/newline/accettazione. IDS-03: UI e salvataggioHTTP sintetico400..440, componenti410,777derivato e input rifiutato;450preservato non attestato. Legacy sconosciuti bloccati senza conversione dati.

IDS-06: consolidamento esplicito con transazione/lock, snapshot cifrato autenticato, hashCSV, versioni/rettifica locale e tenant; report/CSV/ricevuta storici leggono la versione conservata. Ospite/esenzione/date/tariffa modificati non alterano la versione precedente. Nessun recupero retroattivo degli export senza snapshot; asset illustrativi referenziati per percorso, non congelati. Una sola migration nuova limitata aTassa_export e immagine facoltativa, spiegata prima e applicata **soloDBeffimeri**, mai DBoperativo.

IDS-07: hotel/alberghi1..5stelle2026 tariffe1/1/1,50/2,50/2,50 e tetto6 verificati; defaultstagione1giugno–30settembre. Configurazioni discordanti respinte senza modificarle. RTA/villaggi e raccordocap10/777 restano non determinati e bloccati perexport non supportato; nessuna generalizzazione o forzatura dei dati.

RiletturaPDOlocaleREADONLY:28righeattive(no29),configID1/9/12 ancora1,50/6/17–18/marzo–ottobre,35Schedine+16componenti soloNO, invariati. **La configurazione stagionale operativa è ancora legacy e verrà respinta: riallineamento richiede fase autorizzata separata.** Nuova migration non installata operativamente. CSV originale invariato; PDFspecifica letto nei gate precedenti con hash registrato, assente ora dal percorsoDesktop: invarianza finale non riconfermata, copia da rendere disponibile al re-audit.

Verifiche finali launcher isolatoPHP8.3.33/MySQL8.0.36: **91test/1.456asserzioniPASS**, compreseTassa/età/777/SchedinaStore, QuesturaTransportGuard, IstatTransmissionSecurity, tenantA/B/IDforgiati/anonimo e ruolo senza struttura autorizzata. Browser **3PASS** con/senzaimmagine/gruppo, consolidamento/versione/ricevuta storica; PDFA4 1/1/2pagine, header/footer verificati. SOAPoffline14PASS,23rifiuti/identitàrisorse/buildGEOimmutabilePASS, cleanup completato. Sintassi10PHP/Pint8PASS; SchedinaController/routesweb hanno violazioniPint preesistenti confermate suHEAD, non formattati globalmente. DiffcheckPASS. Nessuna suiteglobale o correzione dei tre failureQuestura globali.

19file della task; worktree totale25(16tracked+9untracked), con sei file preesistenti fuori task preservati.50Questura/30ISTAT/147migration originali/helperEtaOperativa invariati perhash. HEAD/origin locale faef3cf,main,0/0,stagingvuoto. Nessun commit/push/deploy/server/produzione/trasmissione reale, DBoperativo non aggiornato.

Residui: fonti/mappingRTA-villaggi/campeggi-camper cap10/777, efficacia e scadenze15–16/riversamento, attestazioni-note esenzioni e eventuali legacy/450, accettazioneStayTour/encoding/newline, previewlegacyIDS09, conservazione/keymanagementsnapshot e asset storici, exportpreconsolidamento irrecuperabili, debitoPint baseline e disponibilitàPDF fonti. Restano gate separati; nessunP0/P1 implementabile nuovo dimostrato dalla regressione conclusiva.


## P1 periodo Tassa e Schedina — 8 ottobre 2026

Correzione mirata in verifica: il controllo fiscaleBellaria del service, chiamato durante elenco/nuova/modificaSchedina, sollevavaValidationException e il renderer errori del master/JavaScript produceva il popup. Store/update non dipendevano fiscalmente dalla stagione, ma la visualizzazione poteva essere bloccata dalla configurazione. **P1 fino a regressione positiva**, integra la precedente dichiarazione di completamento.

Nuovo adattatore nel soloSchedinaController separa il risultato fiscale dalla registrazione: configurazione mancante/invalida→totale=null e avviso nel tab, non zero; elenco non disponibile, niente ricevuta corrente impropria. Configurazione valida fuori stagione→zero corretto. Service e validazioni report/CSV/ricevuta/consolidamento preservati; soggiorno31/05–03/06 resta intero, due notti fiscali senza777 della notte fuori stagione. Apertura struttura già distinta tramite tipo_apertura/data_apertura/data_chiusura, nessuna gestione nuova.

RegressioniHTTP creazione/modifica febbraio/maggio/luglio/novembre/cavallo stagione, configurazione incoerente/incompleta/mancante e selezioni complete Questura/ISTAT aggiunte. Prima prova93test/1516asserzioni con un errore nel nome della route del test, corretto senza eliminare assertion. Esito conclusivo nella chiusura seguente. [Rapporto diagnostico completo](../imposta-soggiorno-bellaria-audit-2026-10-07.md#p1--separazione-periodo-tassa-e-gestione-schedina--8-ottobre-2026).

Soltanto controllerSchedina,due visteSchedina,nuovo testTassa già presente, rapporto eMaestro modificati rispetto all'avvio. Questura/ISTAT/service fiscale/migration/harness invariati. Nessun DBoperativo/commit/push/deploy/server/trasmissione. BASELINE IN AUDIT invariato.

### Regressione conclusiva P1 periodo/Schedina

**P1 CORRETTO E VERIFICATO.** Launcher isolatoPHP8.3.33/MySQL8.0.36: **93test/1.592asserzioniPASS**, comprensivi delle due nuove regressioni e dei test precedentiTassa/età/777/SchedinaStore/QuesturaTransportGuard/IstatTransmissionSecurity. Febbraio,maggio,novembre→creazione/modifica consentite e tassa0 con configurazione valida; luglio→€4,50 per3notti;31/05–03/06→3notti soggiornate,2pertinenti,€3 e0oltrelimite. Configurazione incoerente/incompleta/mancante→nuova/registrazione/modifica/elenco consentiti, totale=null, avviso esplicito e ricevuta corrente422. SelezioniQuestura eISTAT mantengono date/durata intere per ogni scenario.

Secondo tentativo:93test/1508asserzioni con un fallimento del test sul confronto stretto3.0vs3 restituito daCarbon. Corretto al confronto numerico della durata, senza eliminare assertion sulle date o sulle notti; ultimo esito sopra. Identitàprocessi/DB/HTTP,23rifiuti override ebuild/GEOimmutabilePASS; cleanup effimero completato. Sintassi2PHP ePint deltestPASS, diffcheckPASS. Nessuna formattazione generale diSchedinaController, già non conformePint nella baseline. Nessuna suiteglobale/browser ripetuta, nessuna trasmissione.

Confronto rispetto all'avvio: esattamente6file modificati (controllerSchedina,list/tabSchedina,testcompletamento,rapporto,Maestro), nessun nuovofile.50Questura e tutti42percorsiISTAT rilevati (inclusi i30protetti) invariati perhash; service fiscale,migration,harness e altre modifiche preesistenti invariati. HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,main,0/0,stagingvuoto. Nessun aggiornamento DBoperativo, nessun commit/push/deploy. Il progetto resta BASELINE IN AUDIT e i residui del completamento precedente rimangono separati.


## Re-audit finale indipendente Tassa Bellaria — 8 ottobre 2026

**TASSA BELLARIA — BLOCCATA PRIMA DEL COMMIT.** BASELINE IN AUDIT invariato. Il P1periodo/Schedina è approvato dall'utente e confermato indipendentemente: non riaperto o modificato. Schedina consentita fuori stagione/configinvalida, zero solo configvalida fuori periodo, calcolo non disponibile e ricevuta rifiutata quando invalido, soggiorno31/05–03/06 completo con2notti fiscali; Questura/ISTAT vedono date complete.

**Nuovo P1-TASSA-CATEGORIE:** il mappingregolaAlbergoBellaria restituiscenull perRTA/villaggi, ma dettaglioSchedina controlla tariffa solo conmapping valorizzato; validateStayTourScope controlla esclusivamente cap6. FixtureBellaria conconfig1,50/6/giugno–settembre e sola tipologia cambiata aRTA/Villaggio: CSVHTTP200, atteso422. Duefallimenti riprodotti indipendentemente suPHP8.3.33. Questa evidenza corregge la precedente dichiarazione documentale di blocco delle categorie indeterminate. Non inventare tariffe: serve esclusione esplicita, finché noncertificate. Nessun fix applicato nel re-audit.

Inventario completo candidatoTassa **21file (14tracked+7untracked)**:19delcompletamento+listSchedina+precedente testetà777. Include tutti6filedelP1 approvato;5fileharness/DEPLOY preesistenti esterni al candidato preservati. Worktree26file(17tracked+9untracked),stagingvuoto. [Inventario, matricegate, riproduzione ed esiti](../imposta-soggiorno-bellaria-audit-2026-10-07.md#re-audit-finale-indipendente-tassa-bellaria--8-ottobre-2026).

Copia temporanea byte-identica, test indipendenti solo fuori repository. Suiteconclusiva **98test/1694asserzioni:2fallimenti esclusivamenteRTA/villaggio**, nessun errore;93permanenti passano. Invariante quantità/classificazione/importi e storicorettifica/tenant positivi perprofili supportati. ConcorrenzaPASS con2processi sincroni:versioni1/2,precedente ehash corretti. Nuova migration up/down/up euniqueversione **1test/8asserzioniPASS**, soloDBeffimero. Browser3PASS,PDFA4 1/1/2pagine conheader/footer,14SOAPinmemoria,23rifiuti/identità/buildGEO ecleanupPASS. Sintassi11PHP/Pint9PASS;2file condivisi condebitoPint baseline, diffcheckPASS. Primo runtime8.4 riportava1deprecazione; tentativo DatabaseMigrations incontrava errore rollbackstorico1553 e schema parziale, escluso dal testdi concorrenza finale e documentato, senza cambiare migrationstoriche.

READONLYDBlocale:28catalogo,configID1/9/12 marzo–ottobre ancoraidentiche e fiscalmenteinvalide; setup/correzione configoperativa richiedegate separato. FontePDForiginale indisponibile all'ultimaverifica,CSVoriginaleinvariato; decorrenze ministeriali/scadenze e mappingcategorie non dimostrati, nessuna nuova riconferma dalle pagineufficiali inaccessibili. Nessuna accettazioneStayTour,conformitàtotale oproductionreadiness.

Solo rapporto/Maestro modificati duranteaudit. Codice/test permanenti/P1 approvato invariati;50Questura/42percorsiISTAT(inclusi30protetti)/147migration originali/helper efilepreesistenti esterni preservati. mainHEAD/origin faef3cf,0/0,stagingvuoto. Nessun DBoperativo scritto,commit/push/deploy/server/trasmissione. P0nessunodimostrato;P1residuo è il mancatorifiuto categorieindeterminate, conRTA/villaggi tenuti distinti daiprofili alberghieri positivi. ResiduiP2/documentali eprerequisitiambientali dettagliati nelrapporto, senza riaprireilP1 periodo.


## Fix finale P1 categorie Bellaria — 8 ottobre 2026

**TASSA BELLARIA — P1 CATEGORIE CORRETTO, PRONTA PER RE-AUDIT FINALE.** Supera il precedente blocco di codice per categorie ammesse silenziosamente; nessuna approvazione commit/deploy/readiness generale. **BASELINE IN AUDIT** invariato. P1 periodo/Schedina approvato, preservato e nuovamente positivo. [Chiusura, fixture, inventario ed evidenze](../imposta-soggiorno-bellaria-audit-2026-10-07.md#correzione-finale-p1-categorie-non-certificate--8-ottobre-2026).

Barriera unica TassaDiSoggiornoService::validaCategoriaBellaria: mapping nullo/non riconosciuto→ValidationException categoria_tassa_non_certificata, senza fallback. Dettaglio/report/controllo/CSV delegano alla stessa regola; anche intervalli senza movimenti bloccati. Consolidamento non crea export, ricevuta corrente422. Schedina nuova/creazione/modifica/elenco accessibili, totale=null e avviso specifico: non zero fittizio. Storico non modificato.

Supportati soltanto hotel/alberghi con classificazione riconosciuta1..5stelle e forme superior già ammesse dal mapping; tariffe2026 1/1/1,50/2,50/2,50/tetto6 preservate. RTA/villaggi e qualsiasi tipologia/classificazione nulla,vuota,sconosciuta,ambigua o non mappata bloccati per elaborazione fiscale. Non inventate tariffe o regole per nuove categorie. Metodi tariffe/età/777/intersezione periodo/writer identici; cambiati soltanto controllo centrale e chiamanti/errore necessario. Nessuna interferenza Questura/ISTAT.

Quattro regressioni permanenti nuove. Fixture positive esplicitate Albergo3stelle, anche testetà777/browser; le assertion originali restano. Il test numerico cap10 preserva i valori nel dominio generico AltroComune, non in un profilo Bellariahotel non valido; cap10CSV bloccato ancora verificato. Fixture rettifica storico ora4stelle/€2,50 invece di€2 senza categoria, nessuna assertion dello storico eliminata.

Conclusivo: **102test/2063asserzioniPASS**,97permanenti+5indipendenti/concorrenza fuori repository, PHP8.3.33/MySQL8.0.36 e launcher isolato. I2diagnostici RTA/Villaggio immutati ora passano422. Hotel1..5 testati su tariffa/tetto/calcolo/report/ricevuta/CSV e valori fiscali; P1periodo/età777/esenzioni/storico/tenant/Questura/ISTAT verdi. Browser3PASS,PDFA4 1/1/2pagine/footer,14SOAPoffline e23rifiuti/identità/buildGEO/cleanupPASS. Sintassi11PHP/Pint9PASS,diffcheckPASS; debitoPint2file condivisi preesistente. Tentativi con1failure fixturestorico e comando diagnostico dal percorso sbagliato documentati, poi corretti senza neutralizzare test.

Otto file della fase:service,ReportController,SchedinaController,testCompleteness,testEtaCsv,fixturebrowser,rapporto,Maestro. Nessun file nuovo, candidato21/worktree26, stagingvuoto.50Questura/42ISTAT/147migration originali+nuovaTassa/helper e5fileharnessDEPLOY preesistenti invariati. CSVoriginaleinvariato;PDFgià assente prima e dopo, hash non riconfermato. Conteggio catalogo corrente28 già confermato READONLY, nessun29residuo. Configmarzo–ottobre/DBoperativo/decorrenze non modificati. HEAD/origin faef3cf,main,0/0. Nessun commit/push/deploy/server/produzione/migrationreale/trasmissione.

Nessun nuovoP0/P1 dimostrato; atteso re-audit finale. Residui fonti/ambiente e categorie non certificate restano separati, bloccati o non certificati come documentato; nessuna estensione a tutte le tipologie ricettive, nessuna accettazione reale o produzione pronta.


## Fix P1 configurazione fiscale indipendente dai movimenti — 8 ottobre 2026

**P1 CORRETTO E VERIFICATO — ATTESO RE-AUDIT MIRATO. BASELINE IN AUDIT invariato.** Chiarimento del titolare: configurazione valida + zero movimenti → report valido a €0; configurazione invalida + zero movimenti → calcolo non disponibile e operazioni fiscali rifiutate. Nessuna inferenza di chiusura da assenza ospiti. Il precedente bypass su selezione vuota consentiva report/CSV 200 e snapshot con configurazione marzo–ottobre invalida; ora una validazione comune nel service controlla categoria, completezza, stagione e tariffa/cap 2026 prima dei cicli del report/controllo/CSV/consolidamento e nel dettaglio fiscale. Apertura, gestione Schedina, formule età/777, date complete Questura/ISTAT e consultazione degli snapshot già validi preservati.

Tre regressioni permanenti nuove: intervalli vuoti 1/5/20/31 giorni validi, configurazioni invalide con e senza movimenti, storico valido dopo configurazione corrente invalida. Matrice normale/fuori stagione/config invalida/categoria non certificata e apertura separata coperta insieme ai test precedenti. RED **18 test/878 asserzioni, 2 fallimenti attesi**; conclusiva isolata PHP8.3.33/MySQL8.0.36 **106 test/2231 asserzioni PASS**, comprese 6 prove esterne indipendenti/concorrenza. Diagnostico iniziale invariato nelle aspettative: report/CSV/consolidamento 422, nessun export nuovo. 23 rifiuti/identità/build GEO e cleanup PASS, sintassi/Pint 3 PHP PASS. Browser precedente 3 PASS non ripetuto: nessuna vista modificata.

PDF Bellaria ora nuovamente disponibile, letto integralmente e verificato visivamente: SHA `d23d9f47e3c6db0a655bea911fca8201d40ba75e2a0cdf5d5f2bd6de12e0b2c7`. La specifica prescrive intestazione e registrazioni: writer a zero produce solo intestazione, nessuna riga ospite inventata. **Accettazione amministrativa StayTour del solo record di testa non esplicitamente documentata né provata**; non equivale al corretto report fiscale a zero o alla comunicazione comunale di chiusura/assenza attività. [Matrice, evidenze e limite CSV](../imposta-soggiorno-bellaria-audit-2026-10-07.md#p1-configurazione-fiscale-e-zero-movimenti--8-ottobre-2026).

Solo 5 file della fase: service/controller report/test Completeness/rapporto/Maestro; nessun file nuovo, candidato21/worktree26, stagingvuoto. SchedinaController, viste, Questura/ISTAT, migration e harness preservati. Nessun DB operativo modificato, commit/push/deploy/server/trasmissione reale. Non costituisce chiusura del re-audit complessivo, approvazione commit o readiness produzione.


## Tassa Bellaria — configurazione automatica e tre tab — 8 ottobre 2026

**TASSA BELLARIA — CONFIGURAZIONE AUTOMATICA E UI RIORGANIZZATE, PRONTE PER RE-AUDIT. BASELINE IN AUDIT invariato.** Nessuna autorizzazione commit/deploy o readiness produzione. [Diagnosi, inventario e verifiche](../imposta-soggiorno-bellaria-audit-2026-10-07.md#configurazione-automatica-e-riorganizzazione-ui--8-ottobre-2026).

Fonte unica Dati struttura: Comune, tipologia generale, tipo e classificazione. Profilo Bellaria hotel1..5 e superior già ammesse, versione bellaria-alberghi-2026-v1; tariffe/tetto/periodo/età/777 invariati. Profilo derivato in memoria per record assente/interamente nullo, senza scrittura GET; valori legacy parziali/discordanti bloccati e conservati. Fonte incompleta → motivo preciso, Schedina disponibile ma calcolo nullo/ricevuta corrente rifiutata. RTA/villaggi e anni senza fonte certificata bloccati. ID7 derivabile; ID1/9 discordanti, ID12 anche senza classificazione; ID8/10/11 da completare nei Dati struttura. Origine default marzo–ottobre dimostrata nel codice, origine singoli record non dimostrata. Nessun riallineamento operativo.

Tab Configurazione con fonte/profilo/legacy distinti e regole read-only; Esenzioni con catalogo ufficiale unico virtuale, eventuali legacy non certificati e777 derivato; Stampa con loghi esistenti, immagine opzionale, cortesia automatica e anteprima reale tenant. Eliminato il blocco immagine dal loop: prima10DOM/9visibili admin, dopo1DOM per entrambi i ruoli. Footer esistente non configurabile, nessun nuovo template. Upload JPG/PNG/WebP≤2MB, nuove immagini private per tenant con route autenticata private/no-store. File precedenti mantenuti per snapshot; niente cancellazione automatica. Legacy pubblici non trasferiti automaticamente. Snapshot versionano la fonte fiscale e restano invariati dopo modifica struttura/preferenze.

106test/2337asserzioni PASS +2indipendenti/70PASS (108/2407complessivi), riconciliazione e concorrenza2processi. Browser5PASS, 14SOAPoffline,22ISTAToffline,5guardrail,13PHPlint,JS/Pint11/diffcheckPASS. Launcher PHP8.3.33/MySQL8.0.36,23rifiuti/identità/buildGEO ecleanup; nessun Laravel sul DB operativo. Fixture mancanti adattate allo schema enum senza indebolire SQL, amministratore usa selezione tenant esplicita; failure intermedi documentati nel rapporto, assertion preservate.

50Questura/42ISTAT/147migration storiche invariati; migration Tassa preesistente/harness MariaDB/DEPLOY invariati rispetto all’avvio; PDF/CSV originali hash confermati. DB tecnico riletto READONLY invariato. Candidato24file,worktree29(18tracked+11untracked),5preesistenti fuori scope protetti;14file della fase. mainHEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto. Nessun commit/push/deploy/server/migration reale/trasmissione. Nessun nuovoP0/P1 dimostrato; legacy/categorie non certificate, fonti oltre2026, accettazione CSVvuotoStayTour e conservazioneasset restano gate/limiti separati.


## Tassa Bellaria — P1 profilo temporale e P2 cleanup asset — 8 ottobre 2026

**TASSA BELLARIA — VERSIONAMENTO TEMPORALE E CLEANUP ASSET CORRETTI, PRONTI PER RE-AUDIT. BASELINE IN AUDIT invariato.** Il re-audit mirato aveva bloccato la precedente fase per report iniziato2026 che applicava1,50 a un soggiorno2027 noncertificato (report/CSV200 e4,50 contro ricevuta422), oltre al file orfano dopo failureDB. [Causa, risoluzione, inventario e verifiche](../imposta-soggiorno-bellaria-audit-2026-10-07.md#p1-profilo-temporale-e-p2-cleanup-asset--8-ottobre-2026).

Risoluzione canonica nel service per Dati struttura+dataarrivo, catalogo versionato con copertura temporale univoca. Il catalogo reale contiene solo2026; nessuna barriera definitiva anno!=2026 o normativa2027 inventata. Il filtro seleziona gli arrivi, ogni movimento usa il proprio profilo; report/CSV/controllo/consolidamento rifiutano un movimento scoperto senza snapshot o zerofallback. Cross-year consentito nel limite interno preesistente366giorni, con soli movimenti certificati procede; zero movimenti valida il contesto pertinente iniziale senza richiedere inutilmente una fonte futura perdata_a. Schedina resta indipendente.

Snapshot conserva configurazione/versione per ogni Schedina; ricevuta storica usa il profilo salvato con compatibilità degli snapshot precedenti. Rettifica mantiene parent/storico. Fixture2027 esclusivamente sintetica, tariffa3 contro1,50 e controllo aggregato10,50; cap4 solo dominio, cap6StayTourreale preservato. Formule età/777/stagione/esenzioni/dataregistrazione/writer eBlade/tab immutati.

P2: nuovo asset privato salvato e persistenza DB in transazione; failure/false-save→rollback e cancellazione soltanto del nuovo file. Vecchio riferimento/file preservato anche per failure dopo saved. Sostituzioni riuscite continuano a conservare asset precedenti per gli snapshot, senza introdurre cancellazioni/retention massive.

111test/2397asserzioniPASS; successivo rafforzamento del nuovo test positivo cross-year/controllo:5test/66PASS (ripetizione, non sommare);3prove indipendenti/72PASS, diagnostico annuale immutato ora422 perricevuta/report/CSV. Browser5PASS,14SOAPoffline,22ISTAToffline,5guardrail,23rifiuti/identità/buildGEO/cleanupPASS. Lint6PHP,Pint5,checkJS2/diffcheckPASS. Fixture positive età completate con tipologia generale, assertion invariate; errore route del nuovo test corretto,6errori+1failure intermedi documentati. IncompleteRead del proxy alla chiusura dopo5browserPASS, launcherexit0, nessuna modifica harness.

Otto file nella fase (service,trecontroller,due test,rapporto,Maestro), uno nuovo; candidato25/worktree30(18tracked+12untracked),5preesistenti esterni protetti.50Questura/42ISTAT/147migration storiche e migrationTassa/harness/DEPLOY/Blade/routes invariati. PDF/CSV hashconfermati, datioperativi tecnici READONLY invariati. mainHEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto. Nessun commit/push/deploy/server/migration reale/trasmissione. Nessun nuovoP0/P1 dimostrato; restano gate separati fonti future/categorie/legacy/StayTour/conservazioneasset, nessuna productionreadiness.


## Tassa Bellaria — blocco rapporto e riallineamento esplicito — 8 ottobre 2026

BASELINE IN AUDIT. Il blocco nasce dalla validazione del periodo legacy, correttamente rifiutato anche senza movimenti; mancava il percorso UI per risolverlo. DB locale letto READ ONLY: ID1 fine01/10 contro30/09; ID9 inizio01/03 contro01/06 e fine01/10 contro30/09; tariffa1,50/cap6/età17-18 già coerenti. ID12 anche senza classificazione; ID7 derivabile senza scrivere, ID8/10/11 incompleti. 35Schedine tutteID1, altre strutture vuote. Tenant della segnalazione non confermato: non dedotto dall'alert. Stato locale rettifica le precedenti descrizioni generiche “ID1 marzo–ottobre”: l'inizio effettivamente letto è giugno. Nessun dato operativo modificato.

Configurazione UI con anno e confronto dei sei valori, collegamento dal rapporto HTML bloccato; JSON/CSV fail-closed. POST esplicito con consenso e autorizzazioni, token autenticato vincolato a utente/tenant/anno/sorgente/record/profilo, scadenza e ricontrollo sotto lock. Solo sei parametri fiscali aggiornati, audit prima/dopo con versione nella stessa transazione, rollback se audit fallisce. Note, asset, Schedine e snapshot consolidati preservati. Nessuna nuova migration necessaria per il riallineamento; audit esistente varchar255. Nuova migration Tassa precedente ancora non applicata al DB operativo.

Risolto anche il bypass dimostrato dal re-audit precedente: soggiorno iniziato2026 non può usare il profilo su notti2027 non certificate. Validazione di ogni notte, niente fallbackzero; versioni diverse nella stessa Schedina bloccate esplicitamente. Formule età/777/stagione invariate. Intervalli report con soli movimenti coperti e zero movimenti restano nel comportamento già verificato; nessuna fonte2027 inventata.

115test/2448asserzioni PASS,12prove aggiuntive/161PASS (4ripetute,8esterne; concorrenza e riconciliazione incluse). Cross-stay diagnostico: ricevuta/report/CSV/controllo/consolidamento422,export0.14SOAPoffline/22ISTAToffline/5guardrail/Pint4/JS PASS. Browser6scenari distinti PASS su2esecuzioni: configurazione2ruoli/riallineamento esplicito3PASS finali, ricevute3PASS già osservate nella precedente esecuzione. Primo nuovo scenario fermato dall’avviso SweetAlert: test corretto chiudendo normalmente OK, senza aggirare la conferma. Dettagli e tentativi intermedi nel [rapporto](../imposta-soggiorno-bellaria-audit-2026-10-07.md#blocco-rapporto-e-riallineamento-esplicito-bellaria--8-ottobre-2026).

Il titolare dovrà eseguire la conferma UI sui dati locali, dopo verifica struttura/anno/differenze; l'agente non riallinea il DB. Categorie non certificate, StayTour CSVzero, fonti future e readiness produttiva restano limiti/gate separati. Nessun commit,push,deploy,server,migrationoperativa o trasmissione. Questura/ISTAT, migration e harness protetti invariati nella fase.


Chiusura:4test riallineamento/45PASS ripetuti, non aggiuntivi ai115; cleanup finalePASS. Dati tecnici DBoperativo rilette READ ONLY invariati, nessuna normalizzazione applicata. 50Questura/Q3 hash e90percorsi Questura/ISTAT identiciHEAD;147migration storiche/nuovaTassa/5harnessDEPLOY preservati.10file della fase, uno nuovo; candidato26/worktree31(18tracked+13untracked),mainHEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,diffcheckPASS. Nessunfetch. Fix verificato localmente; resta la conferma UI sulla struttura effettivamente selezionata, non ancora identificata dall’utente.


## Tassa Bellaria — ricevute a zero, anteprima e stampa — 8 ottobre 2026

BASELINE IN AUDIT. Continuazione del riallineamento, conservato con consenso/token/audit/transazione. Identificati due difetti UI generali: anno della configurazione nascondeva l’anteprima di un soggiorno valido di altro anno; configurazione derivata non restituita alla vista Schedina, che poteva chiedere una configurazione già disponibile. Anteprima ora distinta dalla stampa e dal consolidamento, scelta ultime20 del solo tenant e accessi da elenco/Schedina/storico. GET non registra documenti o movimenti. HTML422 con spiegazione e link, nessuna falsa ricevuta; JSONfail-closed. Data emissione da now() rimossa perché non sostenuta da registrazione; nessuna numerazione o pagamento inventato.

Zero fuori stagione con configurazione valida: ricevuta/anteprima/stampa; zero per esenzione o limite mantiene il motivo effettivo. Parziali maggio/giugno e settembre/ottobre corretti. Date mancanti/invertite non producono zero; stesso giorno indica nessun pernottamento. Checkout1gennaio senza notti2027 consentito, notti2027 non certificate bloccate. Profilo per ogni notte e contatore unico cap6; tariffe per segmento e contesti conservati nello snapshot. Due profili invernali esclusivamente sintetici producono12euro,6notti tassate e4oltrelimite senza reset. Cap diversi richiedono una regola di transizione documentata, altrimenti rifiuto. Nessuna fonte reale2027 aggiunta. Esenzione0 non fa apparire variabile la tariffa ordinaria; motivi parziali espliciti.

123test/2560asserzioni PASS,14prove aggiuntive/201PASS (6ripetute,8esterne; concorrenza/storico/riconciliazione);8nuove regressioni permanenti. Browser7PASS e7documenti anteprima realmente aperti/PDF, oltre a elenco, Schedina e storicozero. Prima attesa popup non completata: attesa DOM/href verificati, nessun guasto persistente di sessione/CSRF riprodotto.14SOAPoffline/22ISTAToffline/5guardrail/Pint4/sintassi/diffcheckPASS. PDF controllati visivamente: note ripetute causavano pagina quasi vuota perfooter, corrette; flusso di stampa normalizzato per la frammentazione del gruppo. Verifica conclusiva del nuovo CSS: 7 prove browser PASS; 7 anteprime PDF di una pagina, ricevute senza/con immagine di una pagina, gruppo di due pagine. Controllo visivo di zero, positivo, immagine ed entrambe le pagine del gruppo PASS, senza tagli. Risorse isolate fermate e rimosse. [Rapporto, matrice, tentativi e inventario](../imposta-soggiorno-bellaria-audit-2026-10-07.md#completamento-ricevute-anteprima-e-periodi-fiscali--8-ottobre-2026).

17file della fase,3nuovi; candidatoTassa29/worktree34(18tracked+16untracked),5file esterni invariati. mainHEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,nessunfetch.50Questura/Q3/ISTAT/migration/harness/DEPLOY preservati. Nessun DB operativo usato pertest o modificato, nessuna migration reale/commit/push/deploy/server/trasmissione.

Restano conferma UI sui record discordanti e completamento classificazioneID12; nuova migrationTassa ancora non applicata nell’ultima baseline operativa, gate separato per consolidamento. Caso reale senzaID non attribuito definitivamente: se persiste dopo configurazione valida servono soltanto identificatori tecnici, pagina/pulsante, URL ed erroreHTTP. Nessuna certificazione normativa, accettazione StayTour o readiness produttiva; categorie/fonti future/cap diversi restano bloccati o non certificati.


## Correzione selezione struttura e chiusura re-audit browser — 8 ottobre 2026

BASELINE IN AUDIT. P1 preesistente in HEAD: GET `/strutture/seleziona` richiamava `layouts.app`, inesistente. La vista usa ora il layout ufficiale `layouts.master`, breadcrumb e card del gestionale. Form, CSRF, route, controller, autorizzazioni e sessione non sono stati modificati. L'avviso di successo è gestito dal layout ufficiale.

P2: su schermi fino a600px la tabella ricevuta mantiene lo scorrimento nel contenitore, larghezza minima760px e colonna motivo almeno160px, senza spezzare arbitrariamente le parole. Regola soltanto screen: stampa A4 invariata. Pint applicato esclusivamente a `SchedinaController.php` e `routes/web.php`: token eseguibili equivalenti dopo esclusione whitespace/commenti/import e normalizzazione delle parentesi vuote di `new`; import riordinati, solo `Storage` inutilizzato rimosso dalle route. Nessuna modifica semantica Questura/ISTAT.

Prove effettive isolate:148test PHP/2670asserzioni PASS (123Tassa/Schedine/sicurezza più25selezione/autorizzazioni). Ripetizione mirata dei3nuovi test PASS. Browser:7scenari Tassa PASS e5nuovi selezione/percorso completo PASS,12distinti. Proprietario con una/due/nessuna struttura; scelta, cambio, sessione dopo reload; reception reindirizzata; esclusione strutture di altri proprietari; ricevuta del tenant precedente404 e export non visibile dopo cambio. Percorso Login → Selezione → Dashboard → Schedine → Tassa → Anteprima zero/positiva → Stampa → Rapporto → CSV/consolidamento → Storico verificato. Nessun500 osservato nei percorsi positivi. Anteprime senza registrazione/consolidamento dimostrate dai test PHP; gli export creati sono soltanto sintetici nel DB effimero.

14SOAPoffline/22ISTAToffline/5guardrail PASS; Pint16file PASS; lint205PHP più2viste,4Python,4JavaScript PASS; diffcheck PASS. PDF12documenti/13pagine con testo estraibile, zero/positivo una pagina e gruppo due; zero,positivo,gruppo e mobile controllati visivamente. Evidenze esterne: `/private/tmp/ids-selezione-regressione.log`, `/private/tmp/ids-selezione-browser-finale.log`, PDF/screenshot `ids-selezione-*`. Primo test nuovo fermato da selettore ambiguo fra topbar e tabella; secondo da filtro relativo errato del test. Corretti soltanto i selettori, applicazione mostrava già la struttura corrente;5nuove prove ripetute integralmente PASS. Tutti i runtime effimeri fermati/rimossi.

Rispetto all'inizio fase:9file modificati/creati (2viste,2file soloformattazione,3test/fixture,2documenti).233file protetti Questura/ISTAT/migration/harness/DEPLOY byte-identici allo stato iniziale. Modifiche e untracked preesistenti preservati. HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,main,0/0,stagingvuoto; nessunfetch. Nessun DB operativo usato o modificato, nessun commit/push/deploy/server/trasmissione reale. Nessun nuovo P0/P1 nel perimetro verificato. Nessuna certificazione normativa o readiness produttiva; limiti documentali e gate operativi precedenti restano aperti.

Dettagli nel [rapporto Tassa](../imposta-soggiorno-bellaria-audit-2026-10-07.md#correzione-selezione-struttura-e-chiusura-re-audit-browser--8-ottobre-2026).


## Risanamento suite globale PHPUnit — STOP P1 Arrivi, 8 ottobre 2026

**BASELINE IN AUDIT — RISANAMENTO PARZIALE, BLOCCATO SU NUOVO P1 APPLICATIVO.** [Rapporto, inventario, prove e gate](../suite-globale-risanamento-2026-10-08.md). Nessuna suite globale verde, commit, push, deploy o readiness produzione.

Corrette discovery (script originale preservato byte-identico e wrapper TestCase separato), isolamento DDL C1/storage per caso, fixture sintetiche Access/Tenancy/Customer/Smoke, contratto e bootstrap dei test importazione, locale Unit e test homepage. Nessuna logica applicativa modificata. Launcher esteso con discovery/globali/ripetizione e seed casuale, senza indebolire attestazioni, esclusioni seeder o guardrail. 576 casi unici raccolti senza errori; Unit78/318 PASS senza deprecazioni, Questura204/3656 PASS, storage2/7 PASS, 5guardrail/12Pint/13PHP/Python/diffcheck PASS. Prime prove Unit/Questura precedono la sola formattazione Pint: non equivalgono alla convalida finale globale.

Fixture ora valide rendono raggiungibile **P1 nuovo: GET /arrivi/nuovo HTTP500**. ArrivalsController::new passa esenzioni=[]; arrivals.new include la partial Schedina; il catalogo resta array e reject() fallisce a form.blade.php:237. Gruppo34test:1failure/0errori/0skip; Smoke separato4test:stesso unico fallimento. Difetto nel candidato Tassa preesistente alla task; in HEAD la select era statica. Nessuna modifica della vista o del controller, nessuna aspettativa indebolita. Proposta minima da autorizzare: collect($esenzioni ?? []), conservando777/valori legacy e contratti fiscali, con regressioni array/Collection e Arrivi. Gate P0/P1 dell'utente applicato: autorizzazione richiesta, nessun consenso implicito.

Due globali sullo stesso runtime reinizializzato, ordine casuale e regressioni finali separate restano **PENDENTI** fino al via libera. Dei38file iniziali36byte-preservati; run.py esteso nello scope e Maestro soltanto appendice. Tutti19untracked precedenti invariati; sorgenti applicativi/viste/routes/migration/config/.env e dati operativi invariati. mainHEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto. Non certificare tutti i moduli o la produzione mediante queste prove parziali.


## P1 Arrivi autorizzato e suite globale PHPUnit verificata — 8 ottobre 2026

**PASS LOCALE — SUITE GLOBALE VERIFICATA, BASELINE ANCORA IN AUDIT.** Supera lo STOP precedente soltanto per il P1 Arrivi e la verifica della suite. [Rapporto conclusivo, inventario e limiti](../suite-globale-risanamento-2026-10-08.md#chiusura-conclusiva--p1-autorizzato-e-suite-globale-verificata). Nessuna readiness produttiva.

Autorizzazione esplicita dell'utente: normalizzazione minima. Unica modifica applicativa nella partial condivisa: catalogo collect($esenzioni ?? []); controller e contratti fiscali invariati. Array vuoto/popolato,Collection,null,777 escluso dal catalogo e valori legacy preservati; GET /arrivi/nuovo autorizzato200 e zero nuove Schedine/export/esenzioni. Mirati17/111PASS,gruppofixture34/124PASS,Smoke4/13PASS.

**Tre globali complete:582test/6850asserzioniPASS ciascuna,0failure/0errori/0skip/0deprecazioni.** Prima e seconda sullo stesso runtime/endpoint/checkout,DB effimero reinizializzato fra processi,storage non cancellato indiscriminatamente. Terza ordinecasuale seed4208. Inventario e asserzioni percaso identici nelle3prove; nessuna classe/test esclusa e DDL C1 non spostato manualmente. Discoveryfinale582uniciPASS. Regressioni separate:Questura204/3656,ISTAT65/410,Tassa50/1572,accesso/tenant/selezione30/125PASS. Supportostorage2/7 nella globale;10policyDB/21policyisolamentoLOCAL/5guardrail/14SOAPinmemoria/22ISTATinmemoria/Pint13/lint14PHP+4Python/diffcheckPASS. Nessuna provaRocky reale/browser/acceptanceente dedotta o ripetuta.

Fase autorizzata:5file(vista,Unitform,nuovoArriviTest,rapporto,Maestro); risanamentocumulativo18file. Dei38iniziali35byte-preservati,solo launcheresteso/Maestroappendice/vista normalizzata; tutti19untrackedoriginali invariati. Questura/ISTAT applicativi,contrattifiscali/migration/config/.env/datioperativi preservati. Tutti runtime effimeri fermati/rimossi. mainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,29tracked+24untracked. Nessunfetch/commit/push/deploy/server/trasmissione reale. Nessun nuovoP0/P1 dimostrato nel perimetro; backlog e gate produttivi precedenti restano aperti. L'ordine è verificato nella sequenza normale e seed4208,non esaustivamente su tutte le permutazioni.


## Audit della baseline applicativa locale — 8 ottobre 2026

**BASELINE NON VALIDATA — BASELINE IN AUDIT.** La suite582/6850 verde rimane evidenza precedente preservata; non è certificazione completa. [Rapporto versionabile, matrice12aree, inventario53file, riproduzione e piano](../baseline-applicativa-audit-2026-10-08.md). Nessuna correzione autorizzata o applicata in questa fase.

Prove indipendenti finali nel launcher attestato:44test/323asserzioni,4failure/0errori/0skip;37casi esistenti e7diagnostici esterni. TreP1 distinti: A01 impersonazione→target operativo, stop403 e collisione route esci→impersona (rischioR3 ora riprodotto); A02 parent WebCheckin tenantA→SchedinaB già incoerente in fixture, GETanonimo200 espone nome sinteticoB, controllo tenant assente nel punto comune (R5 ora riprodotto, creazione del link da utente ordinario NON dimostrata); A03 POSTbozza tenantA con customer_idB non autorizzato persiste il collegamento, fallback all'ID rifiutato nel resolver. Impatto A03 referenziale, lettura/modifica datiB non dimostrata. Nessuna regressione Questura/ISTAT/Tassa attribuita senza prova.

Browser6PASS correnti:selezioneuna/multi/vuota,reception/mobile,percorsofiscale/cambiotenant,anteprime multiaccesso/PDF.14SOAPinmemoriaPASS. Dopo6PASS proxyIncompleteRead,launcherexit0:anomalia ambientaleP2,URL/causa non identificati,non ignorare prima di acceptanceestesa. JavaScriptVM3/3FAIL ripetuti,matches/binding mancanti,debitoP2 harness senza prova difettoDOM. NessunP0 riprodotto; coperturaUI e matricecompleta auth/tenant/import/Arrivi/presenze/admin/WebCheckin restano parziali o pendenti.

Ogni runtime attestaDB/processi/HTTP/riconnessione,23rifiuti prima delle migration,build/GEO;tutti fermati/rimossi anche dopo failure. SoloDB/storage effimeri,sentinellesintetiche,nessunDBoperativo o server.4.178file preesistenti invariati prima della documentazione;finale unica appendiceMaestro e nuovorapporto,24untrackediniziali preservati. mainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,29tracked+25untracked,nessunfetch/commit/push/deploy/trasmissione. Gate produttivi precedenti invariati. Prossimo passo: autorizzazione circoscritta aiP1 eproveRED→GREEN,poi completare lacune;nessuna correzione automatica.


## Correzioni autorizzate P1 A01–A03 — 8 ottobre 2026

**BASELINE NON VALIDATA — BASELINE IN AUDIT.** [Rapporto aggiornato, cause, prove e inventario](../baseline-applicativa-audit-2026-10-08.md#correzioni-autorizzate-a01a03-e-verifica-conclusiva). Questa appendice supera il precedente STOP soltanto per le tre correzioni espressamente autorizzate; le diagnosi precedenti restano evidenze storiche.

A01: route ingresso numerica, uscita autenticata separata dal vincolo ruolo/servizio target, controllo sessione/log/identità originale attiva, transazione e lock log, rigenerazione sessione e reset tenant, comando POST/CSRF nella topbar ufficiale. A02: parent Web Check-in coerente col tenant obbligatorio prima di letture/token/scritture nei percorsi pubblici e gestionali; nessuna autoreparazione dei riferimenti valorizzati. Non è dimostrata la creazione del collegamento incoerente da utente ordinario. A03: validazione customer_id prima di tutti i modi store/update, nessun fallback grezzo, localizzazione catena autorizzata preservata; transazione contro scritture parziali per riferimenti componenti respinti. Nessuna modifica ai contratti fiscali o ai moduli Questura/ISTAT certificati.

Originali immutati RED44/323 con4failure →GREEN44/327PASS; nuova matrice70/374PASS. **Discovery644unici e tre globali644/7152PASS ciascuna**,0failure/0errori/0skip/0deprecazioni, inventario/asserzioni identici. Due globali nello stesso runtime reinizializzato; terza seed4208. Nella globale Questura204/3656,ISTAT65/410,Tassa52/1586PASS.5guardrail/10policyDB/14SOAPinmemoria/22ISTATinmemoria/Pint8/lint8PHP+4Python+JS/diffcheckPASS. **Browser8/8PASS conclusivi**: impersonazione/uscita con identità originale, navigazione e pannello Componenti, WebCheckin con token completo, cinque scenari selezione/cambio tenant/percorso fiscale e anteprime multiaccesso/PDF. Prime prove rosse dovute a sincronizzazione SweetAlert e nome accessibile con icona, corretti soltanto nei nuovi test. NessunIncompleteRead nella conclusiva; non significa chiusuraA05. Tutti runtime fermati/rimossi, incluso diagnosticoA06.

**Nuovo P1 A06 preesistente, non corretto:** link breve Web Check-in generato codice-token8 restituisce404, mentre token completo200, anche con parent autorizzato. Route breve diretta al resolver del token completo; difetto già inHEAD. Prova indipendente15test/49asserzioni,1failure,0errori; richiesta nuova autorizzazione, nessuna correzione fuori scope. A04diagnosticiJS3/3rossi: DOM/binding incompleti nel VM, nessuna regressione attribuita. A05proxy: IncompleteRead riprodotto in memoria, URL/causa originali ancora sconosciuti. EntrambiP2 invariati, nessun fix harness.

Dodici file della fase: cinque applicativi condivisi autorizzati,cinque nuovi test/fixture,due documenti. Snapshot iniziale4179file: nessuna cancellazione, sole sette estensioni autorizzate; altri4172byteidentici. Tutti untracked precedenti preservati salvo appendice rapporto esplicitamente richiesta. mainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,32tracked+30untracked. Nessunfetch,commit,push,deploy,SPanel,DBoperativo,trasmissione reale. Nessuna readiness normativa/produttiva. Prossimo gate: autorizzare A06, chiudereA04/A05 e completare lacune della matrice applicativa; Rocky/SPanel/acceptanceesterna restano separati.


## A06 — correzione link breve Web Check-in e blocco di accettazione — 8 ottobre 2026

**BASELINE IN AUDIT — NON VALIDATA.** [Rapporto della fase, prove e inventario](../baseline-applicativa-audit-2026-10-08.md#correzione-autorizzata-a06--link-breve-web-check-in). L’utente ha autorizzato soltanto A06, test e documentazione; nessun consenso a correggere ulteriori P1, moduli fiscali o infrastruttura.

CausaA06 dimostrata: URL generato codice-token8 instradato ai metodi che cercavano il token completo. Route brevi collegate ai metodi dedicati, invito ufficiale visibile e CTA al percorso completo; adattatore conserva la ricerca esatta di token completi generati e legacy. Resolver breve rigoroso8alfa-numerici, codice1..30, singolo candidato, confronti esatti PHP, wildcard/prefissi parziali rifiutati. Parent/tenantA02, impersonazioneA01, riferimentiA03 e contratti Questura/ISTAT/Tassa invariati; solo controllerWebCheckin e tre route brevi modificati applicativamente.

HTTP iniziale82/367 con7failure →finale84/412PASS; tre classi P1 precedenti immutate. Compatibility aggiuntiva84/397 con1failure intermedia, corretta prima delle prove finali dando precedenza al token completo. Fixture browser inizialmente incompleta, errori e correzioni del solo setup conservati nel rapporto. Browser conclusivo4/5PASS: invito generato/CTA/convertito e rifiuti tenant passano, impersonazione/navigazione precedenti passano; il form anonimo Bellaria aperto non compare. Discovery666unici;tre globali finali666/7262PASS ciascuna,0failure/0errori/0skip/0deprecazioni,inventario/asserzioni identici,due nello stesso runtime reinizializzato e terza seed4208. ComprendonoQuestura204/3656,ISTAT65/410,Tassa52/1586,A06nuovo22/110. Pint4/lint4PHP+JS/5guardrail/10policyDB/14SOAP/22ISTATinmemoria/diffcheckPASS. Tutti runtime fermati/rimossi.

**A07 — P1 distinto, non corretto:** nel Web Check-in anonimo non convertito Bellaria il caricamento della configurazione Tassa usa lo scope della sessione gestionale; senza utente autorizzato la configurazione esistente non viene letta e il percorso completo rifiuta il rendering con richiesta di configurazione. Browser con configurazione sintetica valida bloccato. Diagnostico indipendente23/112 con1failure:DBconfig1record,letturaanonimaNULL,HTML302 anziché200 eJSON422configurazione_tassa. Aspettative non indebolite; diagnostico esterno non incluso nella globale verde. La prova browser permanente del form resta rossa, non mascherata con fixture fuoriBellaria.. Non è una regressione attribuita ai tre P1 o alle regole fiscali: il metodo condiviso di lettura fiscale e i percorsi completi non sono stati modificati in questa fase. Necessaria nuova autorizzazione circoscritta; nessuna eliminazione di validazioni o accesso DB operativo.

Scadenza: nessuna politica temporale implementata; date soggiorno non invalidano il token. Revoca tecnica per cancellazione/rotazione con cambio prefisso verificata; mantenere codice/prefisso conserva il link breve. Nessuna UI di revoca/scadenza aggiunta. Politica token/bearer/rate limit e acceptance completa restano da definire/auditare, non certificate da questa correzione. A04JS/A05proxy invariati; nessuna correzione harness.

Sette file della fase (2applicativi,3nuovi test/fixture,2documenti). Snapshot4184:quattro estensioni autorizzate,4180byteidentici,zero cancellazioni. mainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,32tracked+33untracked. Nessunfetch/commit/push/deploy/SPanel/DBoperativo/trasmissione reale. Accettazione completa A06 **BLOCCATA daA07**, nessuna baseline validata/readinessproduttiva. Fermarsi e richiedere nuova autorizzazione per la prossima fase.


## A07 — correzione contesto fiscale del Web Check-in anonimo — 8 ottobre 2026

**A07 CORRETTO E VERIFICATO; BASELINE IN AUDIT — NON VALIDATA.** [Rapporto, matrice, evidenze e inventario](../baseline-applicativa-audit-2026-10-08.md#correzione-autorizzata-a07--contesto-fiscale-del-web-check-in-anonimo). Questa appendice supera il blocco A07 della fase A06 soltanto nel perimetro autorizzato.

Causa: `publicShow` usava il lettore fiscale gestionale con scope della sessione; token pubblico valido e configurazione Bellaria esistente non bastavano a leggere il record. Nuovo metodo privato nel solo WebCheckinController verifica richiesta/struttura/parent coerenti e legge configurazione ed esenzioni mediante filtro esplicito sul tenant della richiesta, rimuovendo soltanto lo scope nominato `struttura`. Scope gestionale invariato; nessun tenant deciso da parametri o sessione estera. Catalogo e validazioni fiscali originali preservati, nessun fallback/configurazione sostitutiva, nessuna modifica a tariffe, profili, Questura, ISTAT, routeA06 o tre correzioniP1 precedenti.

RED indipendente95/442 con5failure; browser con controller iniziale byte-identico4PASS/1FAIL. GREEN mirato95/475, poi asserzioni negative ulteriormente rafforzate nelle globali finali. Tre globali **677test/7335asserzioni PASS ciascuna**,0failure/0errori/0skip/nessuna deprecazione segnalata, inventario e asserzioni identici; due sullo stesso runtime reinizializzato, terza seed4208. A07nuovo11/73,A06 22/110,Questura204/3656,ISTAT65/410,Tassa52/1586. Browser5/5PASS: modulo Bellaria anonimo realmente visibile, salvataggio sintetico/riapertura, link breve e token completo, rifiuti tenant e regressioni precedenti. Guardrail5,policyDB10,SOAP14/ISTAT22offline,Pint2/lint2PHP/diffcheckPASS.

Diagnostico originale conservato e rieseguito immutato: prima23/112 con1failure applicativo; dopo23/112 con1failure del solo logger che tenta di decodificare HTML200 come JSON. Copia diagnostica esterna con soltanto logging compatibile e tutte le aspettative originali identiche:23/113PASS. Nessuna aspettativa indebolita né modifica del contratto HTML per ottenere verde.

Quattro file della fase: WebCheckinController, nuovo WebCheckinFiscalContextTest e appendici di rapporto/Maestro. Snapshot4187file: sole tre estensioni autorizzate,4184byteidentici,zero cancellazioni; un nuovo test. Git mainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,32tracked+34untracked. Nessunfetch,commit,push,deploy,SPanel,DBoperativo o trasmissione reale. Risorse test esclusivamente effimere e rimosse.

Restano A04JS/A05proxy, policyTTL/revoca/rate limit, lacune della matrice generale e gate Rocky/SPanel/accettazione esterna. Nessuna scadenza temporale implementata; rotazione con cambio prefisso/cancellazione invalidano tecnicamente il link, stesso prefisso lo conserva. Prova E2E della Schedina singola, nessuna certificazione esaustiva gruppi/importazioni. Nessuna readiness produttiva o certificazione normativa. Fermarsi prima di ulteriori interventi.


## Audit conclusivo della baseline applicativa — 9 ottobre 2026

**BASELINE IN AUDIT — NON VALIDATA.** [Rapporto conclusivo e inventario dei 66 file](../baseline-applicativa-audit-2026-10-08.md#valutazione-conclusiva-della-baseline-applicativa--9-ottobre-2026). Nessun P0/P1 residuo riprodotto in questa fase; A01/A02/A03/A06/A07 risultano corretti nel perimetro delle prove disponibili. Non confondere assenza di difetti riprodotti con sufficienza delle evidenze.

Ricontati i tre JUnit A07: 677 test/7.335 asserzioni ciascuno, zero errori/fallimenti/skip, inventario e asserzioni identici; seed casuale 4208 nelle evidenze precedenti. Nessuna nuova esecuzione Laravel/browser in questo audit documentale e statico. Codice protetto Questura/ISTAT e tre file delle prove Rocky invariati; 147 migration tracked invariate. Git main, HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,staging vuoto,32 tracked modificati+34 untracked. Nessun fetch: stato remoto corrente non attestato.

Verifiche indispensabili prima della validabilità generale: matrice gestione utenti/password/sessioni e rischio R14 non riprodotto; Web Check-in anonimo con gruppi/componenti, rollback e conversione; ciclo operativo persistente con importazioni e passaggio annuale; confini tenant degli endpoint critici non ancora campionati. Formalizzare politica token senza inventare requisiti di scadenza. A04 JavaScript e A05 proxy restano diagnostici P2 da chiarire, non nuovi P1 dimostrati. Delimitare l'ambito dei moduli secondari nell'accettazione.

Sei prove Rocky/Bubblewrap di processo già PASS, non nuovamente eseguite. MariaDB applicativa reale isolata, accettazione CLI/FPM/SPanel e servizi esterni restano gate produttivi separati. Revisione backup/rollback necessaria prima della nuova migration Tassa; nessuna operazione su DB reale. Inventario pertinente a più fasi, nessuna autorizzazione a staging indiscriminato.

Solo appendici documentali in questa fase, nessuna modifica codice/test/migration o cancellazione. Nessun commit/push/deploy/produzione/trasmissione. Fermarsi e attendere autorizzazione per le verifiche mancanti o qualsiasi correzione; nessuna certificazione normativa/readiness produttiva.


## Audit approfondito baseline — nuovi P1 A08/A09/A10, 9 ottobre 2026

**BASELINE NON VALIDABILE — BASELINE IN AUDIT.** [Rapporto, matrice e comandi](../baseline-applicativa-audit-2026-10-08.md#audit-approfondito-autorizzato--9-ottobre-2026). Supera la precedente conclusione di sola insufficienza con tre impedimenti concreti; nessuna correzione applicativa autorizzata in questa fase.

A08 P1: reception priva di gestione operativa può cambiare password del proprietario della stessa struttura attraverso POST legacy `/strutture/utenti/{id}/reset` (302 e DB modificato), mentre la route operativa rifiuta403. A09 P1: scope gestionale Componenti esclude accompagnatore autorizzato dal modulo anonimo; prova HTTP e browser Bellaria con zero campi anziché uno. A10 P1: invio pubblico rifiutato422 per ID componente inesistente salva parzialmente principale prima della transazione dei componenti. Meccanismi presenti in HEAD, non attribuiti a regressioni A01–A07. Nessun P0 o exploit cross-tenant aggiuntivo dimostrato.

Diagnostici5/15:3failure; matrice autorizzazioni conclusiva5/29PASS; browser conclusivo2FAIL applicativi. Errori iniziali dei soli nuovi setup (snapshot factory e selettoreCSRF) corretti senza alterare aspettative. Globale esistente677/7335PASS; nuovi diagnostici `Audit.php` espliciti non inclusi nella discovery `*Test.php`: il verde globale non include e non maschera i tre nuovi fallimenti. Guardrail5/policyDB10/policyisolamento21/SOAP14inmemoria/lint3PHP+1JS/Pint3/diffcheckPASS. Solo runtime effimeri attestati con23rifiuti prima delle migration, nessun DB operativo.

Quattro nuovi test/fixture e appendici dei due documenti;32tracked+38untracked,stagingvuoto,mainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,nessunfetch. Codice e test preesistenti preservati. Sessioni multi-contesto, ciclo operativo/importazioni/passaggio annuale e matrice esaustiva restano parziali; A04/A05 e politica token invariati. Sei prove Rocky già PASS; gate applicativi server e accettazioni esterne separati. Nessuncommit/push/deploy/trasmissione.

Fermarsi: necessaria nuova autorizzazione minima A08/A09/A10, poi RED→GREEN e completamento delle lacune; nessuna correzione automatica e nessuna validazione generale.


Chiusura della fase approfondita: seconda globale conclusiva677/7335PASS,0failure/errori/skip,nessuna deprecazione segnalata. Prima e conclusiva su runtime indipendenti puliti; tutti sette runtime attestati rimossi. Confronto4.188file:4.186byte-identici,solo rapporto/Maestro appendici,zeroeliminazioni,quattro nuovi diagnostici/fixture;32tracked+38untracked,stagingvuoto,diffcheckPASS. Diagnostici PHP eseguiti prima della sola formattazione Pint dei file nuovi,aspettative identiche. Nessuna revisione pre-commit implicita. A08/A09/A10 apertiP1: necessaria nuova autorizzazione; statoBASELINE NON VALIDABILE — IN AUDIT.


## Correzioni autorizzate A08–A10 e STOP A11 — 9 ottobre 2026

**BASELINE IN AUDIT — NON VALIDATA; STOP NUOVO P1 A11.** [Rapporto con cause, risultati, inventario e riproduzione](../baseline-applicativa-audit-2026-10-08.md#correzioni-autorizzate-a08a10-e-stop-su-a11--9-ottobre-2026). Sole correzioni autorizzate A08/A09/A10 applicate; verifiche verdi, accettazione conclusiva bloccata dal nuovo P1. Nessuna validazione generale/readiness produttiva.

A08: reset legacy server-side riservato a gestione autorizzata, reception403 e password invariata;6casi ruolo positivi/negativi. A09: query componenti predefinita gestionale preservata, override Web con tenant+parent autorizzati dal token e rimozione del solo scope nominato; caricamento,salvataggio,conteggi e riapertura corretti senza duplicati. A10: transazione dell'intero publicStore inclusa risoluzione richiesta/parent,principale,camere,componenti e stato;422/eccezione finale ripristinano gli snapshot. Solo3controller applicativi modificati,nessuna regola fiscale/route/modello/Questura/ISTAT alterato.

Originali immutati5/17PASS;nuove regressioni13/53PASS;tre globali690/7388PASS ciascuna,0failure/errori/skip/nessuna deprecazione segnalata. Due nello stesso runtime reinizializzato,terza seed4208. Due inventari JUnit ordinari690unici e asserzioni identici;percasuale solo log conclusivo,non attestata identità percaso delle treprove. A01 14/112,A02 14/47,A03 34/143,A06 22/110,A07 11/73;Questura204/3656,ISTAT65/410,Tassa52/1586. Browser finale4/4PASS:diagnostici originali e modifica/salvataggio/riapertura full/short,nessun erroreJS nei nuovi casi. Prime prove rosse dei soli nuovi test dovute a dettagli richiusi/caricamento pagina,corrette soltanto nel nuovo spec,senza modificare UI o forzare DOM. Guardrail5/policyDB10/SOAP14/ISTAT22inmemoria/lint7PHP+1JS/Pint7/diffcheckPASS.

**A11 P1 distinto,non corretto:** invio anonimo valido200 con camera12 conserva la camera11 precedente e ne aggiunge una seconda:conteggio2anziché1. Diagnostico nuovo1/3con1failure. SchedinaCamera scoped esclude camera esistente dalla cancellazione syncCamere senza identità gestionale;creazione inserisce nuova relazione. Meccanismo già inHEAD,nessun tenant incoerente in fixture,nessuna esposizione cross-tenant dimostrata. Atomicità A10 non corregge la selezione errata di un invio valido. STOP richiesto applicato:nessun fix camera/modello/syncCamere,necessaria nuova autorizzazione.

Dieci file della fase:3applicativi,5nuovi test/fixture/evidenzaA11,2documenti. Snapshot4192:4187byte-identici,sole5variazioni autorizzate,zeroeliminazioni;diagnostici originali preservati. MainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,33tracked+43untracked,nessunfetch. Tutti dieci runtime effimeri rimossi,nessunDBoperativo/commit/push/deploy/trasmissione. Lacune generali e gate produttivi precedenti restano aperti;fermarsi in attesa della nuova autorizzazione.


## Chiusura correzione autorizzata A11 — 9 ottobre 2026

**A11 CORRETTO E VERIFICATO. BASELINE IN AUDIT — NON VALIDATA.** [Rapporto, matrice e comandi](../baseline-applicativa-audit-2026-10-08.md#correzione-autorizzata-a11--camere-web-check-in-9-ottobre-2026). Supera il precedente STOP A11 dopo la nuova autorizzazione; nessuna validazione generale/readiness produttiva.

Causa: cancellazione delle camere tramite scope gestionale invisibile al salvataggio anonimo, seguita dall'inserimento della nuova camera. Query base gestionale preservata; override Web rimuove solo lo scope nominato e mantiene filtro parent più tenant del token già autorizzato da A02. La stessa query precarica le camere nella riapertura del modulo. Transazione A10, componenti A09 e tutte le altre correzioni precedenti invariate; nessun bypass globale, modifica modello/scope o regola fiscale.

Diagnostico originale immutato RED1/3 con1failure→GREEN1/4. Nuove camere9/72PASS; cumulativi22/130PASS. Browser Chromium6/6PASS: camere full/short con salvataggi12→12→13 e un solo campo alla riapertura, più quattro prove originali/gruppo. Il solo campo UI facoltativo è abilitato nella copia effimera dalla fixture, nessuna configurazione del repository modificata.

Tre globali699/7460PASS ciascuna, zero failure/errori/skip, nessuna deprecazione segnalata: due nello stesso runtime reinizializzato e casuale seed4208 separata. Tre JUnit acquisiti,699casi unici e asserzioni per caso identici. Questura204/3656, ISTAT65/410, Tassa52/1586; A01/A02/A03/A06/A07 e A08–A10 verdi, sorgenti certificati invariati. Guardrail5/policyDB10/SOAP14/ISTAT22 in memoria/lint4PHP+1JS/Pint4/diffcheckPASS. Rollback422/eccezione sintetica tardiva comprovato su snapshot; sessione tenant estraneo, identificatori forgiati e relazioni esterne non alterano altri parent/tenant.

Sette file della fase: due controller, tre nuovi test/fixture, due appendici documentali. Snapshot4197:4193byte-identici, quattro sole variazioni autorizzate, zeroeliminazioni; diagnostici originali preservati. Main HEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,33tracked+46untracked,nessunfetch. Cinque runtime effimeri attestati e rimossi; nessun DB operativo/commit/push/deploy/produzione/trasmissione.

Nessun nuovo P0/P1 indipendente riprodotto nello scope. Nessuna riconciliazione automatica dei dati storici incoerenti o privi di tenant; dati reali non inventariati. Restano sessioni multi-contesto, ciclo operativo/importazioni/annualità, policy token, A04/A05 e gate produttivi precedenti. Sei prove Rocky processo già PASS, nessuna nuova esecuzione. Fermarsi in attesa di autorizzazione alla verifica conclusiva; baseline ancora IN AUDIT.


## Verdetto conclusivo baseline completa — 9 ottobre 2026

**NON VALIDATA — BASELINE IN AUDIT.** [Rapporto conclusivo, matrice e riproduzioni](../baseline-applicativa-audit-2026-10-08.md#verifica-finale-della-baseline-completa--9-ottobre-2026). La nuova autorizzazione dopoA11 ha colmato evidenze di sessione e alcuni percorsi operativi; quattro P1 concreti impediscono l'accettazione. Nessuna modifica applicativa o correzione autorizzata/eseguita in questa fase.

**A12 P1:** account disattivato continua ad accedere200 con sessione esistente; nuovo login rifiutato. HTTP e browser a due contesti confermano che il filtroattivo al solo login non revoca sessioni. **A13 P1:** reset amministrativo/cambio password effettivamente riusciti e nuova password verificata da login nuovo, ma seconda sessione ancora200; nessuna invalidazione e AuthenticateSession commentato. **A14 P1:** token recupero emesso prima della disattivazione consente reset302 e autenticazione dell'accountancorafalse, gestionale200; filtroattivo nel solo invio link, non nel consumo del token. **A15 P1:** vero POST Arrivi con cliente di tenant estraneo crea relazione customer_id incoerente,302 e unrecord; cliente esterno invariato, nessuna esposizione nominativa dimostrata. A03 Schedina non copre quel controller separato. Nessuna regressione attribuita adA01–A11, tutti preservati.

**A16 P2:** creazione utenti legacy500 peravatar mancante; reception raggiunge lo stesso tentativoSQL invece di403, ma creazione abusiva riuscita non dimostrata. Gestione operativa autorizzata funzionante. A04JSVM/A05proxy restanoP2 precedenti, non motivano da soli il verdetto. PoliticaTTL/revoca/rate limite token e qualità dati reali restano rischi/decisioni non trasformati in bug senza contratto/prova.

Nuova globale699/7460PASS,0failure/errori/skip,nessuna deprecazione segnalata. Diagnostici finali24/107:5failure,0errori/skip; cinquefailure A12,dueA16,A14,A15. Quattro casi operativiPASS: annualità/circuiti/date complete su clock delle giornate operative, conferma importata ripetuta/batchtenant, rollback seconda riga e rifiutoarrivoesterno. Browserfinale9:6PASS/3FAIL, tre rossi sulle sessioni, sei verdi navigazione/impersonazione/gruppo/camere fullshort. Diagnostici nuoviAudit.php selezionati esplicitamente, fuori discoveryglobale e non occultati dal verde. Tre globaliA11 precedenti applicabili agli stessi byte, non dichiarate come tre nuove. Guardrail5/policyDB10/SOAP14/ISTAT22inmemoria/lint4PHP+1JS/Pint4/diffcheckPASS.

Corrette soltanto fixture/diagnostici nuovi: tabella reset da configurazione; loginemail e struttura browser separata per non interferire col contratto shared_username; clock annuale coerente conArrivi delgiorno. Aspettative di sicurezza e originali immutate. Dieci runtime temporanei rimossi, nove avviati/attestati23rifiuti e uno interrotto durante copia prima diDB/browser. Nessun DB operativo, rete esterna o secretoreale.

Settefile della fase: cinque nuovi diagnostici/fixture e due appendici documentali. Snapshot4200:4198byte-identici,due sole appendici,zeroeliminazioni; app/test/migration/configcertificati invariati. MainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,33tracked+51untracked,nessunfetch/commit/push/deploy/produzione/trasmissione.

Ordine proposto da autorizzare: statoaccount/recuperoA12A14, revoca altre sessioniA13, riferimentiArriviA15; poiA16/P2 e accettazione completa concordata. Importazioni E2Ebrowser/ricordami/secondari restano parziali; non inventata nuova funzione di chiusura annuale. I gateMariaDB/FPM/SPanel/backup/enti impediscono readinessproduttiva ma sono separati dai quattro blocchi locali; sei proveRockyprocesso giàPASSinvariate. Fermarsi dopo il verdetto, nessuna correzione o consolidamentoGit implicito.


## Correzione autorizzata A12–A15 — 9 ottobre 2026

**A12–A15 CORRETTI E VERIFICATI nello scope locale; BASELINE IN AUDIT — NON VALIDATA.** [Rapporto, cause, comandi e limiti](../baseline-applicativa-audit-2026-10-08.md#correzione-autorizzata-a12a15--9-ottobre-2026), [evidenza strutturata](../evidenze-a12-a15-2026-10-09.json). Supera soltanto i quattro blocchi P1 dell'audit finale precedente; diagnosi ed esiti originali conservati.

A12: account disattivato rifiutato a ogni richiesta web autenticata, sessione pulita. A13: confronto dell'hash password della sessione tramite AuthenticateSession, hash registrato al Login, sessioni superate rifiutate alla richiesta successiva; sessioni precedenti prive di hash richiedono nuovo login. A14: broker al consumo del token limitato dal server ad account attivo, nessun cambio credenziali o autenticazione dell'account disattivato. A15: riferimento cliente validato prima di ogni modalità; perimetro della catena autorizzata preservato, cliente localizzato nella struttura corrente senza alterare l'origine o duplicare l'equivalente. Localizzazione e salvataggio Arrivi transazionali. Cinque file applicativi, nessun cambio a Questura, ISTAT, regole fiscali, modelli, route, migration o configurazioni del repository.

Nuove regressioni account **9/47** e Arrivi **7/45**. Mirati definitivi **48 test/369 asserzioni PASS**, browser Chromium **12/12 PASS**: sessioni indipendenti dopo disattivazione/reset/cambio, Ricordami, recupero rifiutato/valido, riferimenti Arrivi, reception legacy, gruppo anonimo e camere full/short. **Due globali definitive 715/7.552 PASS ciascuna**, DB reinizializzato nello stesso runtime attestato; zero failure/errori/skip, nessuna deprecazione segnalata. Due JUnit: 715 casi unici e asserzioni per caso identici; nove sorgenti PHP del checkout identici alla versione consegnata. Questura **204/3.656**, ISTAT **65/410**, Tassa **52/1.586**, tutte verdi. Tre globali intermedie **714/7.546 PASS** (una seed4208) precedono l'ultima protezione e non sono tre globali definitive.

Conservati i rossi: RED originale HTTP **9/27 con 5 failure**; prima globale **714/7.537 con 1 failure Geo** da `actingAs` che cambia identità mantenendo l'hash precedente. Solo estensione `be` nel supporto TestCase: un nuovo login sintetico inizializza l'hash, middleware e aspettative originali intatti; compatibilità **42/330 PASS**. Diagnostici originali selezionati **18/88 con 3 failure**: due A16 fuori scope, una asserzione flash A14 dopo una seconda richiesta. Log A14: attivo false/autenticato false/accesso302; la nuova regressione asserisce l'errore prima della richiesta successiva e verifica password/stato invariati. Nessun diagnostico alterato o fallimento occultato; esecuzione degli originali precedente all'ultima aggiunta sul rifiuto delle sessioni senza hash, distinta dalle prove definitive.

Guardrail **5**, policy DB **10**, SOAP **14** e ISTAT **22** in memoria, lint **9 PHP +1 JS**, Pint **8**, diff check: **PASS**. Soltanto runtime MySQL/storage/HTTP/browser effimeri, attestati con 23 rifiuti prima delle migration e rimossi. Primo bind nel sandbox rifiutato prima di creare DB; avvii isolati successivi consentiti dal controllo automatico. Nessun ripiego o servizio operativo utilizzato.

Fotografia iniziale di **8.352 file**, esclusi .git/vendor/node_modules/storage: sette sole variazioni preesistenti autorizzate, **8.345 byte-identici**, zero eliminazioni; cinque nuovi sorgenti e un'evidenza JSON. Test e diagnosi preesistenti preservati; Maestro/rapporto solo appendici. Git main, HEAD/origin locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, staging vuoto, 37 tracked modificati+57 untracked. Nessun fetch/commit/push/deploy/DB operativo/trasmissione reale. Nessuna migration del progetto modificata, dati solo effimeri.

Restano A16 P2, A04/A05, politica dei token Web Check-in, matrici secondarie e gate produttivi/accettazione esterna; nessuna readiness produttiva o riconciliazione di dati reali. Prossimo passo consigliato: valutare A16 entro una nuova autorizzazione e completare l'accettazione concordata. Stato generale PARZIALE; mantenere BASELINE IN AUDIT.


## Correzione autorizzata A16 e conferma A12–A15 — 9 ottobre 2026

**A16 CORRETTO E VERIFICATO nello scope locale; A12–A15 confermati. BASELINE IN AUDIT — NON VALIDATA.** [Rapporto, cause, A14 separato, comandi e limiti](../baseline-applicativa-audit-2026-10-08.md#correzione-autorizzata-a16--9-ottobre-2026), [evidenza strutturata](../evidenze-a16-2026-10-09.json). Supera il residuo A16 delle appendici precedenti senza riscriverne diagnosi o risultati.

Due cause applicative: `avatar` obbligatorio non passato alla creazione legacy; assenza del controllo di gestione che permette alla reception di raggiungere l'inserimento. Controller con tre righe aggiunte: avatar vuoto coerente con il percorso operativo, autorizzazione prima di form e POST secondo il criterio già usato nel reset A08. Reset e risoluzione tenant preesistenti invariati. Ulteriore difetto nello stesso flusso: due viste estendono `layouts.app` inesistente, GET creazione500 riprodotto; entrambe passano con una sola riga a `layouts.master`. Nessuno schema, modello, route, configurazione o script UI modificato.

Nuovi test A16 **12/89 PASS**, durata flash **1/11 PASS**; mirati **58/379 PASS**. **Due globali 728/7.652 PASS ciascuna**, stesso runtime attestato con DB reinizializzato: zero failure/errori/skip, nessuna deprecazione segnalata. Due JUnit 728 casi unici e asserzioni per caso identici; i 715 casi precedenti A12–A15 mantengono le stesse asserzioni. Questura 204/3.656, ISTAT 65/410, Tassa 52/1.586 verdi. Nessuna nuova globale casuale dichiarata. **Chromium finale 14/14 PASS**, compresa creazione dal modulo con conferme UI, elenco, login nuovo account, rifiuto della reception e dodici prove A12–A15/gruppo/camere originali immutate.

Rossi conservati: originali 8/26 con 3 failure (due A16, flash A14); nuove regressioni 12/38 con 6 failure; ulteriore prova viste con 7 failure visibili, GET 500 e output troncato prima del riepilogo. Browser 13/14 e mirato 1/2 con timeout del solo nuovo spec: mancava l'interazione con le due conferme già presenti nella UI; solo aumento del limite a 60 secondi insufficiente. Nuovo spec corretto per seguire `Sì, salva` e `OK`, senza forzare DOM o modificare applicazione/aspettative. Ulteriore browser 13/14: A16 verde, timeout di navigazione camere short sullo spec originale; esito conservato e prova rieseguita immutata, causa non dimostrata e instabilità non cancellata dal successivo PASS. Successiva prova13/14: nuovo spec legge l'elenco prima di completare il feedback di successo; snapshot contiene account creato, camere short verde. Aggiunte al solo nuovo spec attesa del caricamento e conferma del messaggio di successo; verifica riga e login preservata.

**A14, diagnostico separato:** originale byte-identico, asserzione sugli errori flash dopo una seconda richiesta; TestResponse legge la sessione corrente e il flash è scaduto. Nuovo test dimostra errore presente al reset, password/attivo invariati, identità anonima, accesso successivo302 e flash poi scaduto. Originali selezionati dopo il fix **18/91 con 1 failure**, solo flash A14; due casi A16 verdi. Nessun fallimento occultato o diagnostico originale modificato; selezione esplicita Audit.php distinta dalla discovery globale.

Guardrail 5, policy DB 10, SOAP 14/ISTAT 22 in memoria, Pint 4, lint 4 PHP+1 JS, build isolato e diffcheck PASS. Dieci runtime effimeri attestati con 23 rifiuti prima delle migration e rimossi. Sei sorgenti non-JS del checkout globale identici alla consegna; spec nuovo corretto dopo l'avvio delle globali e verificato nel browser finale. Sorgenti A12–A15, TestCase e precedenti evidenze intatti.

Fotografia iniziale di 8.358 file: cinque sole variazioni preesistenti autorizzate, 8.353 byte-identici, zero eliminazioni; quattro nuovi sorgenti di test/fixture e un'evidenza JSON. Maestro/rapporto soltanto appendici. Git main, HEAD/origin locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, staging vuoto,39 tracked modificati+62 untracked. Nessun fetch/commit/push/deploy/trasmissione, DB operativo o cambio configurazione/schema operativo. Dati esclusivamente sintetici effimeri.

Restano A04/A05, politica token Web Check-in, matrici secondarie e gate produttivi/accettazione esterna. Valutazione generale PARZIALE; prossimo passo consigliato: definire il perimetro di accettazione generale e valutare i residui P2. **BASELINE IN AUDIT — NON VALIDATA.**


## Chiusura della valutazione locale — 9 ottobre 2026

**BASELINE IN AUDIT — NON VALIDATA. Accettazione locale PARZIALE, accettazione completa NON DIMOSTRATA.** [Rapporto aggiornato con matrice dei requisiti, classificazione e gate separati](../baseline-applicativa-audit-2026-10-08.md#chiusura-della-valutazione-locale--9-ottobre-2026); [evidenza strutturata](../evidenze-chiusura-locale-2026-10-09.json).

A12–A16 restano VERIFICATI nello scope documentato; codice, test e configurazione applicativi invariati. Riutilizzate le due globali728/7652PASS e il precedente browser14/14PASS dopo confronto dei sorgenti, senza ripetizioni immotivate. Nessun nuovo difetto applicativo riprodotto.

A14 è un problema diagnostico: originale immutato, copia identificata sposta solo l'asserzione flash prima del GET successivo, conservando tutti gli otto casi e le aspettative. Copia+regressione flash9/41PASS; originale rosso storico preservato. A04 è un problema dei doppi VM: originale3FAIL, copia con matches/binding mancanti3PASS, nessuna prova E2E import dedotta. A05 handler troncamento confermato in memoria (2test/1errore), corretto solo nell'harness con502 esplicito e conteggi senza contenuti/token;2/2PASS,guardrail5/policy10PASS. Endpoint/causa originari A05 restano anomalia non identificata.

Timeout camere storico conservato, non riprodotto: due runtime nuovi, originali full/short e sei campioni strumentati PASS ciascuno senza aumentare30s/retry. Chromium8/8prima e9/9dopo il fix proxy, incluso download sintetico520010byte con hash/righe completi. Tracce mostrano cancellazioni asset ma nessun documento fallito: non prova della causa storica. Log browser/HTTP integrali conservati fuori repo; sintesi/hash nell'evidenza. Due runtime attestati e rimossi,23rifiuti prima migration, soli dati sintetici effimeri.

**Pendenti locali concreti:** E2E browser import clienti/componenti upload→normalizzazione→conferma→persistenza/annullamento/duplicati; conversione intero gruppo Web Check-in; intera gerarchia amministrativa utenti/strutture; P1.3 Cestino read/restore/purge/entità globali/minimizzazione; P1.6 matrice tenant dei percorsi critici non campionati; ciclo operativo E2E/concorrenza numerazione; contratto durata/revoca/rate limit token; delimitazione e acceptance moduli secondari/responsive. Sono lacune di prova/contratto, non guasti riprodotti e non verifiche riservate alla produzione. Le conclusioni storiche bloccate daA12–A16 sono superate solo per quei difetti, non per tutta la baseline.

**Gate destinatario/produzione distinti:** MariaDB applicativa isolataLinux,CLI/FPM/SPanel,backup/restore/rollbackmigration,rilascioSHA/config/cache/storage/queue/cron/SSL,qualità dati reali e accettazione Questura/ISTAT/mail. Sei proveRocky/Bubblewrap giàPASSinvariate,nonripetute. NessunDBoperativo/config/schemaoperativo/commit/push/deploy/trasmissione. Snapshot8363file,nessunaeliminazione;solevariazioni preesistenti launcher e appendici documentali;originaliA04/A14/camereimmutati. Pint/lint/diffcheckPASS,GitmainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto,nessunfetch. CodiceapplicativoNO;test/infrastrutturaSI;docsSI;datitestSI effimeri;altri flagoperativiNO. Prossimo passo: colmare la matrice locale e formalizzare i token prima di rivalutare lo stato.


## Completamento delle prove locali pendenti — 9 ottobre 2026

**ACCETTAZIONE LOCALE POSITIVA DEL PERIMETRO ESEGUITO E DELIMITATO. Accettazione funzionale dell'intera baseline PARZIALE: contratto dei token ancora non definito. BASELINE IN AUDIT — NON VALIDATA. Nessuna validazione produttiva.** [Rapporto con matrice finale requisito/criterio/evidenza/esito/residuo](../baseline-applicativa-audit-2026-10-08.md#completamento-delle-prove-locali-pendenti--9-ottobre-2026), [evidenza strutturata e hash](../evidenze-accettazione-locale-2026-10-09.json). Le lacune eseguibili enumerate nella precedente chiusura sono ora verificate nel perimetro concreto; il limite di contratto non viene trasferito alla produzione né dichiarato soddisfatto.

Difetti applicativi riprodotti e corretti minimamente: Supporto campo facoltativo assente500; Calendario confronto DATE/stringa vuota; creazione strutture Admin/Superadmin priva di campi geografici obbligatori/default fisici; numeri duplicati con due richieste concorrenti Arrivi/Schedina; chiamanti GEO nel form Schedina passavano nazione_text anziché nazione; conversione operativa lasciava Web in_compilazione e protezione anonima non attivata. Sei controller e il solo form Schedina variati rispetto all'inizio della fase. Lock di struttura nelle transazioni di numerazione; richiesta Web aggiornata tenant+parent nella stessa transazione, prima data di conversione preservata. Core GEO/hash blindati intatti: il build ha rifiutato il primo tentativo sul componente, conservato nei risultati; scelta finale corregge quattro chiamanti, senza modificare guardrail/config/schema.

Matrice effettiva: upload CSV browser clienti/componenti, annullamento senza persistenza, data normalizzata, batch monouso/duplicati; gruppo Web completo con3persone→Arrivi→Schedina, componenti e tenant conservati e richiesta convertita; CRUD gerarchico implementato strutture/proprietari e gestione utenti con rifiuti; Cestino tutte16classi tenant/globali read/restore/purge e minimizzazione;20percorsi tenant critici aggiuntivi con snapshot invariati sui rifiuti e ricerca filtrata; annualità2026/2027 via HTTP e due vere richieste parallele per ciascuno dei due controller; CRM/supporto/licenze/GEO/calendario/notifiche tramite operazioni HTTP specifiche,12pagine browser per ciascuna viewport1440×900 e390×844. PASS nel perimetro descritto dai test, non ogni endpoint/permutazione/browser/dispositivo o dati reali.

Risultati: prima globale dopo i sei fix controller728/7652PASS, mirati135/983PASS, concorrenza2/28PASS, token1/4PASS; dopo le nuove correzioni form/stato Web, GEO/conversione/link/atomicità31/165PASS e browser import/ciclo2/2PASS. Globale conclusiva indipendente728/7652PASS, seed20261009,0failure/errori/skip. Non sommare suite sovrapposte; Audit.php selezionati esplicitamente, esclusi dalla discovery globale. Secondari/responsive2/2PASS e download520010byte/hash/20001righePASS riutilizzati, sorgenti relativi invariati. A12–A16 riconfermati dai mirati e dalla globale; browser storico14/14preservato senza ripetizione immotivata.

**Proxy:** regressioni2/2PASS sul vero handler con doppi in memoria: corpo completo preservato, troncato10/3→502 senza200parziale, soli conteggi nel log. È esclusivamente strumento di audit `tests/Isolation/run.py`, byte-identico alla precedente chiusura; nessun codice applicativo proxy modificato. Guardrail5/policyDB10PASS. Timeout camere originario e causa upstream A05 restano anomalie non riprodotte/non identificate; campioni8/8e9/9storici conservati. Nuova attesa load molto lunga nel ciclo Arrivi preservata; uso DOMContentLoaded nel solo nuovo spec non prova la causa storica.

**A14 diagnostico originale preservato:** asserzione flash dopo un GET successivo; copia identificata con sole aspettative temporali riordinate conserva tutti8casi e medesime aspettative funzionali, inclusa nei mirati135PASS. A04 originale/copia immutati. Nessuna eliminazione di test o occultamento di rossi: nuovi diagnostici corretti per fixture incomplete, ID nuovi al ripristino, privacy, catch-all/nomi accessibili/conferme e chiavi stabili duplicate. Rollback down1553 durante teardown concorrente conservato: non imputato alla numerazione; il nuovo diagnostico usa migrate:fresh solo sul DB effimero. Cinque difformità Pint dei controller preesistevano (hash iniziali comprovati), nessuna riformattazione generale; Pint8file nuovi/Schedina, lint e diffcheckPASS.

**Unico impedimento funzionale locale non risolvibile mediante ulteriori esecuzioni:** manca la politica approvata di durata/scadenza/revoca/rate limit pubblico. Osservato token creato90giorni prima e soggiorno passato ancora200; rotazione vecchio404/nuovo200, cancellazione404; GET con solo middleware web, route pubbliche senza throttle dedicato. Non approvata l'assenza di TTL né inventato un valore. Servono durata/evento di scadenza, eventi di revoca, soglia/finestra/chiave limite; poi prove dei confini temporali e429 e relativa implementazione se richiesta. Questo requisito rimane aperto localmente e impedisce accettazione completa della baseline.

**Gate produttivi separati:** MariaDB applicativa isolataLinux,CLI/FPM/SPanel,backup/restore/rollbackmigration,SHA/config/cache/storage/ownership/queue/cron/SSL,qualità dati reali e accettazione Questura/ISTAT/mail. Sei prove Rocky/Bubblewrap storichePASS non sostituiscono i gate e non ripetute. Nessuna operazione operativa autorizzata o eseguita.

Preservazione/attestazioni nell'evidenza: snapshot8370file, zeroeliminazioni, sette soli sorgenti applicativi e appendici rapporto/Maestro variati; originali diagnostici, core GEO/hash e proxy precedenti intatti. Dati esclusivamente sintetici effimeri, ogni runtime attestato con23rifiuti prima migration e pulito dal launcher; SOAP14inmemoria,mail array, origini esterne bloccate. GitmainHEAD/originlocale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,stagingvuoto,nessunfetch. Log storici/integrali con nomi distinti e hash; la permanenza dei file temporanei oltre la sessione non è garantita.

Flag: codiceapplicativoSI; testSI; docsSI; configurazione/schemaoperativoNO; datitestSI soloeffimeri; DBoperativoNO; commitNO; pushNO; deployNO; trasmissionirealiNO. **Perimetro locale eseguito ACCETTATO; baseline generale resta NON VALIDATA per contratto token aperto e gate destinatario/produzione non verificati.**

Attestazione conclusiva: **29 runtime di questa fase tutti rimossi**, snapshot8370file con8361byte-identici e9solevariazioni autorizzate (7sorgenti+2appendici), zeroeliminazioni. Prefissi storici dei documenti verificati mediante hash; originali A04/A14/camere e core GEO/hash preservati. I due JUnit globali hanno728casi unici e asserzioni per caso identici. Stagingvuoto/main/divergenzaHEAD-originlocale0/0; lint/Pint8/diffcheckPASS, ferme le5difformità Pint preesistenti documentate.


## Contratto funzionale dei token: proposta da approvare — 9 ottobre 2026

**PROPOSTA NON APPROVATA, NON IMPLEMENTATA. Accettazione locale positiva del perimetro verificato preservata; BASELINE IN AUDIT — NON VALIDATA.** [Documento specialistico: regole trovate, comportamento corrente, D1–D5, cambiamenti e prove futuri](../contratto-token-proposta-2026-10-09.md); [appendice del rapporto](../baseline-applicativa-audit-2026-10-08.md#contratto-dei-token-analisi-e-decisioni-pendenti--9-ottobre-2026).

Pendente P1.4 delimitato: completo Web64, invito breve codice+prefisso8, completi legacy e ripristino80; tutti associati alla stessa richiesta/parent/struttura. Nessuna durata/soglia approvata trovata; nessun throttle web dedicato. Il breve rivela il completo; stessa credenziale logica, non autorizzazione separata. Rotazione a prefisso invariato conserva il breve; generazione80 Cestino versus resolver breve64 è una discrepanza statica, non nuova riproduzione browser. Completed non uniforma flag/editUrl al blocco show/store; backend convertito resta protetto nelle prove precedenti. Nessun fix o test modificato in questa analisi.

D1 proposta: primo tra30giorni assoluti dall'emissione e fine giorno successivo alla partenza locale; nessun rinnovo da accesso/salvataggio o date fornite dall'ospite. D2: riuso del gruppo, conversione sola ricevuta fino scadenza, revoca/rigenerazione atomica di entrambe le rappresentazioni da operatori già autorizzati; ripristino non riattiva l'invito; cambio gestionale destinatario richiede nuova emissione. D3: nuovo invito casuale indipendente32caratteri e completo64, uniforme anche nel Cestino. D4: ingresso300/minuto perIP prima lookup; lettura60/minuto e POST20/10minuti perSID+RID+IP, alias/HEAD unificati, tentativi invalidi/CSRF conteggiati,429/Retry-After; quota ingresso condivisa tra strutture sullo stessoIP esplicitamente sottoposta ad approvazione. D5: transizione massima7giorni per link esistenti già compatibili, limitata dal termine soggiorno; nessuna grazia per revocati/cancellati/scaduti. **Sono cinque decisioni funzionali da approvare, non regole in vigore; nessuno di questi numeri è imposto da standard o misurato sulla produzione.**

Altri token distinti: recupero configurato60minuti/monouso e throttle emissioni60secondi peremail (nessun limiter tentativi reset), batchComponenti30minuti con consumo e contesto, anteprimaTassa15minuti/impronta; CSRF/sessioni/Ricordami/Sanctum e token esternoQuestura delimitati. Il limiterAPI60/minuto userId/IP non protegge le routeWebCheckin. Durate framework/default non convertite in nuovi requisiti; nessuna nuova politica API/account/provider chiesta implicitamente.

Cambiamenti futuri, non eseguiti: metadati di emissione/scadenza/revoca/invito e resolver comune, azioni tenant di rigenerazione, controllo atomico al salvataggio, uniformità ricevuta/Cestino e limiter; regressioni ai confini, revoca concorrente, alias, soglia+1/ripristino finestre, isolamento A/B/NAT e browser reale. Nuove migration/configurazioni restano solo piano per una futura task locale autorizzata. Nessuna suite ripetuta per attività documentale; evidenze728/7652,135/983,31/165eChromium2/2precedenti preservate, non attestazione della proposta.

Gate produttivi e destinatario invariati e separati, inclusi IP affidabile dietroproxy/cache condivisa,qualità dati reali e accettazioni esterne. Approvazione contratto non equivale ad autorizzazioneDBoperativo/config/schemaoperativo/commit/push/deploy/trasmissioni. Flag: codiceNO;testNO;docsSI;datiNO;operazionioperativeNO. La conoscenza nuova è documentata senza cambiare l'accettazione locale già delimitata o eliminare diagnosi/storia.

Preservazione della sola fase di contratto:8380file iniziali,8378byte-identici,2appendici e1nuovo documento specialistico;zeroeliminazioni,storia dei documenti verificata con hash. Codice/test/config/schema/evidenze preesistenti invariati,diffcheckPASS,stagingvuoto,nessuna suite o operazione applicativa eseguita.


## Approvazione circoscritta D1/D2 del contratto token — 9 ottobre 2026

**CONTRATTO PARZIALMENTE APPROVATO, NON IMPLEMENTATO. Accettazione locale già verificata preservata; BASELINE IN AUDIT — NON VALIDATA.** L'utente ha sostituito le precedenti proposte D1/D2 con tre sole regole: emissione/invio in qualsiasi momento e compilazione/salvataggi/correzioni mentre il link è valido e non convertito; conversione in Arrivo/Schedina conserva dati e blocca modifiche anonime, il link mostra solo il messaggio letterale richiesto «Check-in recibido», senza dati personali, con successive correzioni dal gestionale; scadenza alla fine del giorno successivo all'**arrivo previsto**, Europe/Rome, senza limite30giorni e senza prolungamento per visite/salvataggi.

Superati il cap30giorni, il termine legato alla partenza e la ricevuta nominativa dopo conversione. **D3–D5 e ogni ulteriore regola di revoca/rinnovo/Cestino non sono approvate.** Il blocco dopo conversione non autorizza riapertura automatica del link. Il registro vigente del contratto distingue queste decisioni dal comportamento corrente e conserva integralmente la proposta precedente come storico.

Restano soltanto: **R1** fonte autorevole dell'arrivo previsto e effetti della riprogrammazione gestionale, inclusa eventuale riattivazione di un link scaduto (oggi il salvataggio ospite aggiorna anche richiesta.arrivo, ma non può implicitamente prolungare la validità); **R2** eventi/ruoli di revoca e rigenerazione, trattamento del ripristino; **D3** formato/invito breve e compatibilità64/80; **D4** soglie/finestre/chiavi e impatto degli IP condivisi; **D5** applicazione ai link già distribuiti/legacy e date mancanti/incoerenti. I vecchi32caratteri,300/60/20 e7giorni restano proposte non approvate, da riesaminare dove interferiscono con le nuove regole.

Aggiornata anche la matrice delle prove future: emissione oltre30giorni prima dell'arrivo, confini del giorno locale/DST, salvataggi senza estensione, conversione Arrivo/Schedina e conferma senza dati personali su tutti gli alias, correzioni gestionali/tenant. Nessuna prova eseguita né politica attivata; le evidenze precedenti restano della baseline già verificata, non prova dei nuovi requisiti. Modifiche solo ai tre documenti, nessun codice/test/config/schema/dato modificato. Gate destinatario e produttivi invariati, nessun DBoperativo/commit/push/deploy/trasmissione reale.

[Registro vigente e decisioni residue](../contratto-token-proposta-2026-10-09.md).


## Contratto token implementato e accettato localmente — 9 ottobre 2026

**ACCETTAZIONE LOCALE POSITIVA del contratto approvato e del perimetro della baseline già verificato. BASELINE IN AUDIT — NON VALIDATA. Nessuna validazione produttiva.** [Rapporto/matrice finale](../baseline-applicativa-audit-2026-10-08.md#implementazione-e-accettazione-locale-del-contratto-token--9-ottobre-2026), [contratto vigente](../contratto-token-proposta-2026-10-09.md), [evidenza strutturata](../evidenze-contratto-token-2026-10-09.json). Superata la precedente lacuna locale P1.4: D1–D2/R1–R2/D3–D5 tutte approvate dall’utente, implementate e verificate; nessuna ulteriore decisione funzionale di questo contratto in attesa di approvazione.

Emissione in qualsiasi momento, nessun cap30giorni; validità sino al termine esclusivo arrivo previsto+2giorni00:00Europe/Rome, fonte gestionale richiesta.arrivo. Visite/salvataggi ospite non riscrivono le date previste né estendono il termine. Riprogrammazione soltanto mentre il vecchio link è valido; dopo scadenza/correzione date invalide serve emissione esplicita, nessuna riattivazione automatica. Conversione conserva dati operatore e blocca modifiche anonime: link valido mostra solo «Check-in recibido», senza dati personali. Revoca/rigenerazione/cancellazione/cambio gestionale destinatario invalidano tutte le rappresentazioni; restoreCestino sempre revocato. Nessuna emissione implicita da GET gestionale. Nuovi completi64/inviti indipendenti32; legacy64/80/altri formati/prefisso8 entroD1, nessun taglio7giorni; rigenerazione disabilita prefisso anche a parità dei primi8caratteri.

Quote approvate300/60sperIPprima lookup,60letture/60s e20POST/600sperSID+RID+IP, alias/HEAD/completed e CSRF errato conteggiati,429/Retry-After senza scritture. Storefilelocale esistente e lock fra processi; nessuna configurazione operativa modificata. Resolver centrale e transazioni/lock proteggono richiesta e conversione, salvataggio già iniziato rivalidato dopo lock. Migration aggiuntiva4metadati nullable preparata/testata solo sui DB effimeri; nessuna migration operativa o riscrittura dei dati legacy reali.

Globale **750test/8.252asserzioni PASS**, seed20261009, JUnit750casi unici,0failure/errori/skip; nuove regressioni contratto22/600 incluse. Supplementari **14/101PASS**: concorrenza4/50 con processi reali, round-tripmigration1/8, conversione e copiaA14. **Chromium9/9PASS**: guest salva/corregge, privacy tutti alias, scadenza/revoca/legacy, azioni gestionali, riprogrammazione/scaduto, conversione GUI, cancellazione/restore/emissioneCestino, quote e Wi-Fi fra ospiti/strutture. Questura204/3656,ISTAT65/410,Tassa52/1586; A12–A16 riconfermati. Le globali intermedie748/8234 e749/8239 e quella750/8252 col tentativoUI Cestino non vengono confuse con la definitiva.

**Distinzione diagnostica Cestino:** timeout conservati, primoformvuoto/alias di azione/modal nascosto e nome accessibile dell’icona ingannavano i selettori del nuovo test. Con pulsante visibile identificato dal tooltip e form associato verificato, la vista originale funziona; prima attribuzione a difetto applicativo smentita. Tentativo di spostamento modali annullato, vista finale byte-identica all’inizio, browser9/9 sulla vista originale in copia effimera. Nessuna correzione applicativaCestinoUI mantenuta senza prova. Copie byte-identiche dei6testprecontratto e della vista inDiagnostics; nei testattivi aggiornati soltanto clock/aspettative sostituite dal nuovo contratto, controlli sicurezza/atomicità preservati.

**A14 originale preservato:** flash asserito dopo secondoGET, distinta anomalia diagnostica; copia identificata conserva8casi e aspettative funzionali e passa. Nessun diagnosticoA04/A14/camere o risultato storico cancellato. Rossi intermedi setup/perdita relazione dopo lock/clock/UTC/confermeUI/forkPID conservati; log del primoRED troncato dal limite dell’harness e JUnit non recuperato dichiarati nel rapporto. Proxy precedente byte-identico, solo strumento di audit, regressioni2/2PASS. Guardrail5/policyDB10/ISTAT22inmemoria/SOAP14inmemoria/lint/Pint15/diffcheckPASS.19runtimeattestati23rifiuti e tutti rimossi; nessun DB operativo. Hash/evidenze/JUnit conservati nel progetto.

**Pendenti locali necessari del contratto: nessuno nel perimetro eseguito.** Mantengono validità i limiti applicativi preesistenti: vincoli fiscali/configurazione e anni non certificati non vengono allentati. Nessuna disponibilità futura universale dei moduli dedotta dalla sola validità token. Timeoutcamere originario e causa upstreamA05 restano anomalie non riprodotte/non identificate, senza nuova attribuzione causale.

**Gate produttivi separati e non completati:** MariaDB/Linux isolato,CLI/FPM/SPanel,backup/restore/rollbackmigration autorizzati; date/qualità/provenienza dati legacy reali e revoche storiche non registrate; IP affidabile dietroproxy, cache/lock/topologia effettiva; releaseSHA/config/cache/storage/ownership/queue/cron/SSL; accettazioniQuestura/ISTAT/mail e regole fiscali future. Le proveRocky/Bubblewrap storiche non sostituiscono i gate. Nessuna ricognizione o operazione sui dati reali, nessuna validazione produttiva/normativa.

Flag: codiceSI;testSI;docsSI;migrationLOCALISI;config/schemaOPERATIVONO;DBoperativoNO;datitestSI soloeffimeri;commitNO;pushNO;deployNO;trasmissionirealiNO;fetch/stagingNO. Perimetro locale documentato accettato; **BASELINE IN AUDIT — NON VALIDATA mantenuta**.


## Preparazione concreta del primo rilascio controllato — 9 ottobre 2026

**Accettazione locale positiva conservata; lancio effettivo BLOCCATO da B1–B4, non dallo stato BASELINE IN AUDIT — NON VALIDATA.** Il piano unico è in [Preparazione del primo rilascio](../PRIMO-RILASCIO-CONTROLLATO-2026-10-09.md). Quattro condizioni finite: candidato con SHA approvato che includa il lavoro non versionato; accettazione attuale CLI/FPM/MariaDB del destinatario; elenco migration riconciliato e restore dimostrato; chiusura documentata del gate storico di esposizione/dipendenze. V1–V4 specificano le verifiche server che chiudono quei blocchi; V5–V7 delimitano link legacy, provider/mail e ampliamento fiscale. M1–M3 restano differibili, senza nuova audit generale.

Preparati procedura di rilascio, piano delle sette migration recenti da riconciliare con lo storico effettivo, configurazione senza segreti, backup/recupero e controlli prima/dopo apertura; inventario SHA-256 di1.818sorgenti in `inventario-candidato-rilascio-2026-10-09.json`, non artefatto distribuibile. Sintassi dei due wrapperbash e5/5hash fissi supervisor PASS. Nessuna ripetizione delle750prove/8.252asserzioni o Chromium9/9già superate: nessun codice modificato.

Storico preservato: sei proveRocky/Bubblewrap PASS prevalgono sulla vecchia annotazione di assenza; non certificano runtime PHP né annullano i segfault storici da verificare. Nessuna nuova vulnerabilità dichiarata dalla sola lista avvisi dipendenze. Nessun DBoperativo, configurazione/schemaoperativo, commit/push/fetch/deploy o trasmissione; nessuna validazione produttiva. Accesso server necessario esclusivamente per le verifiche V1–V4 indicate; dati/provider per V5–V7.


## Avvio della chiusura B1–B4 — 9 ottobre 2026

[Verbale ed evidenza effettiva](../CHIUSURA-BLOCCHI-PRIMO-RILASCIO-2026-10-09.md). Accettazione locale positiva mantenuta, nessun nuovo difetto applicativo affermato. Inventario1818/1818, sorgenti contratto13/13 ed evidenze31/31 corrispondono; candidato completo preparato per review prima del commit. Solo nuova prova isolata di backup/restore/upgrade:1test/26asserzioniPASS,23rifiuti primaDDL, runtime ripulito. Sorgenti applicativi invariati, globale750/8252 e Chromium9/9non ripetuti.

B1: preparazione locale con manifest/staging per review, resta futuro SHA/pubblicazione non autorizzati ora. B2: accesso al dominio documentato fuori sandbox rifiutatoTCP22, nessun profiloSSH/terminale collegato; CLI/FPM/MariaDB e segfault attuali non verificati. B3: quota sintetica locale verde, elenco pending/schema destinatario e recuperabilità reale non attestati; non si deduce pending dal solo nome migration. B4: Composer aggiornato46avvisi/12pacchetti, [triage nominativa](../triage-dipendenze-primo-rilascio-2026-10-09.json); npm non completato dopo timeout40s/30s, mitigazioni vhost/trasporti non attestate. I46avvisi non sono46exploit riprodotti. Lancio ancora BLOCCATO dai medesimi quattro gate; BASELINE IN AUDIT — NON VALIDATA non è un difetto.

Precisazione dell’autorizzazione corrente: server solo lettura; migration/backup/restore solo sintetici isolati. Non restaurare snapshot operativi per completare B3 sotto questa autorizzazione; occorre una precedente evidenza affidabile di restore o separata autorizzazione futura. Nessuna credenziale richiesta in chat. Le verifiche server esatte e i limiti sono nel verbale. Nessun commit/push/fetch/deploy, configurazione server, scritturaDBoperativo o trasmissione; nessuna validazione produttiva.


Controllo finale dello staging: file applicativi/test PASS con `git diff --cached --check -- app database resources routes tests`. Il controllo sull’intero candidato segnala46occorrenze whitespace in14file di evidenza/rapporto storico; byte conservati deliberatamente, non normalizzati. Esito completo in `docs/evidenze/primo-rilascio-2026-10-09/staging-controlli.json`. Non si presenta il controllo completo come PASS. Contenuto finale194file; nessun commit, HEAD invariato.


## Pendenti locali Ross1000 e dipendenze — 9 ottobre 2026

[Verbale aggiornato](../PENDENTI-LOCALI-ROSS1000-DIPENDENZE-2026-10-09.md). Il500specifico Ross1000 non risultava chiuso da una precedente attribuzione documentata: nuovo percorso configurazione→TabellaA HTTP1/9PASS e Chromium3/3PASS con tre profili sintetici, nessuna credenziale o trasmissione. Classificazione: anomalia non riprodotta nel campione; nessuna modifica applicativa senza causa. Per attribuirla restano necessari stack sanitizzato e SHA/schema/ruolo del destinatario; non presunta causa migration.

Composer46avvisi classificati individualmente in [matrice completa](../composer-classificazione-46-2026-10-09.json): versione/catena, diretto/transitivo, ambito, esposizione, criterio e rimedio. Tutti produzione:16avvisi su2diretti,30su10transitivi. Mitigazioni locali2/11PASS per trasporti spenti/debug/locale e assenza NoPrivateNetworkHttpClient; non equivalgono a patch o configurazione server attestata.62regressioniISTAT verdi nel mirato; rossi dei soli nuovi setup/route conservati.

**npm completato:** audit definitivo62dipendenze affette(2critiche,18alte,41moderate,1bassa), senza erroreJSON; timeoutprecedenti superati tramite cache privata. Bulk indipendente105advisory/versioni su30pacchetti, conteggio distinto. Nessun aggiornamento dei lock o auditfixforce. Rimedi/mitigazioni degli avvisi applicabili ancora B4; rimosso soltanto il pendente di completamento auditnpm.

SSH: host schedinedinotifica.tanggo.software, porta22, Connection refused fuori sandbox, exit255; nessun comando remoto eseguito o modifica server. B1review/futura pubblicazione, B2runtime/segfault/500non attribuito, B3schema/pending/recupero destinatario e B4esposizione/rimedi restano aperti. Accettazione locale positiva conservata, BASELINE IN AUDIT — NON VALIDATA; nessuna validazione produttiva. Codice e lock invariati: globale750/8252 e Chromiumtoken9/9non ripetuti. Candidato e manifest aggiornati con le nuove prove/evidenze, precedente inventario194preservato. Nessun commit/push/deploy, DBoperativo, configurazioneserver o trasmissione.


## Destinatario corretto e rimedi mirati — 9 ottobre 2026

[Verbale finale](../RIMEDI-DESTINATARIO-2026-10-09.md). Destinatario root@217.174.149.23:6543 raggiunto ma autenticazione negata: Permission denied (publickey,gssapi-keyex,gssapi-with-mic,password), exit255; agente senza identità. Nessun comando remoto. Il precedente dominio:22 non verifica il destinatario. CLI/FPM/MariaDB/segfault, eccezione sanitizzata Ross1000 e metadati migration restano bloccati dall’autenticazione; nessun500 dichiarato risolto. Recuperabilità non attestata.

Rimedi Composer mirati11pacchetti segnalati+Promises prerequisito, nessunmajor/rootrequire:39dei46avvisi produzione originari rimossi;7Laravel residui, oltre4sviluppo nel rapporto completo. SolverLaravel11 rifiutato dalla policy sicurezza, non disabilitata. PHP globale750/8252PASS, mirati5/25PASS; prove prima/dopo PATH_INFO e UTF8storage confermano il rimedio. Nessuna modifica applicativa.

Rimedi npm SweetAlert11.22.4/Moment2.31.0/Prism1.30.0 e Sass1.79.0 dichiarato, risolto il build non riproducibile dal manifest. Esplorazioni e rossi diagnostici preservati; nessun aggiornamento massivo. Audit finale58dipendenze affette:2critiche/16alte/40moderate; non considerare risolti gli asset residui. BrowserRoss1000 3/3PASS e assetfinali1/1PASS; build isolatiPASS. Globale non ripetuta dopo soli cambi build/Moment, verificati separatamente.

Inventario storico1818preservato:1815hash invariati, sole divergenze autorizzate composer.lock/package.json/package-lock.json. Nuovo inventario1818aggiornato; manifest e staging comprendono rimedi, regressioni e originali pubblici dei lock. Accettazione funzionale locale positiva conservata; gate sicurezza/esposizione aperto. B1review/futuroSHA;B2identitàSSH+runtime+attribuzione500;B3metadata+recuperabilità;B4Laravel e assetpubblici/CKEditor oppure esclusione tecnica dimostrata delle feature, più esposizione serverdev/vhost. BASELINE IN AUDIT — NON VALIDATA. Nessun commit/push/deploy, modifica server, scritturaDBoperativo o trasmissione reale.


## Evidenza Herd e sicurezza effettiva — 9 ottobre 2026

[Verbale consolidato](../HERD-ROSS1000-SICUREZZA-2026-10-09.md), [matrice Laravel](../laravel-avvisi-residui-2026-10-09.json), [matrice npm](../npm-esposizione-effettiva-2026-10-09.json). Accettazione funzionale locale positiva conservata; BASELINE IN AUDIT — NON VALIDATA, nessuna validazione produttiva.

Ross1000: il500 segnalato è Herd locale. Otto eccezioni sanitizzate confermano SQLSTATE42S22, snapshot assente in istat_exports. Metadati operativi letti senza record personali, transazione READ ONLY: MySQL8.0.44, migration2026_10_07_180000_protect_istat_cycle non registrata. StoricoSHA/ruolo/fuso non attestati. Riproduzione sintetica PHP1/13 e Chromium1/1:500 prima,200 dopo la migration esistente sul soloDB effimero. Nessuna patchISTAT o migrationoperativa; guasto Herd non dichiarato risolto. Per eliminarlo serviranno riallineamento autorizzato e backup; i tre profili sintetici verdi precedenti non risolvevano il guasto osservato.

Correzione applicativa minima: resolverEmailControlValidator rifiuta CR/LF che il validatore originale accetta; Laravel2/16 e globale750/8252PASS. Rimedi asset Swiper12.1.2/ECharts6.1.0/Quill2.0.3/Axios1.20.0 e form-data4.0.6 verificati. CKEditor40.2.0 escluso dalla sola copia del nuovo artefatto, sorgenti/pacchetti/build esistenti preservati; nessun consumer applicativo trovato. Chromium artefatto+compatibilità4/4, HTTPAxios1/1 e multipartNodePASS; originali diagnostici e rossi conservati. Auditnpm finale completo53dipendenze:0critiche/15alte/37moderate/1bassa; non tutte dichiarate mitigate. QuillHTMLexport residuo privo di consumer attuale, gate prima della sua introduzione; strumenti dev non devono essere pubblici.

Laravel11.31.0 conserva sette righe advisory (due duplicate dello stesso problemaemail): condizioni e limiti provati nella matrice. Debug effettivo del destinatario ancora da attestare. [Impatto Laravel12](../IMPATTO-LARAVEL12-2026-10-09.md): simulazione50update/5install, non applicata; decisione supporto/major prima del lancio. [Procedura SSH Mac](../SSH-MAC-CHIAVE-AUTORIZZATA-2026-10-09.md): coppia pubblica esistente verificata, nessuna chiave privata letta/esposta/copiata, agente vuoto; nessuna nuova connessione o modifica server.

B1: candidato aggiornato per review, futuroSHA/commit non eseguiti. B2: autenticazioneSSH e letture CLI/FPM/MariaDB/segfault ancora necessarie; causa500Herd separata, identificata. B3: metadati destinatario/riconciliazione e recuperabilità reale ancora mancanti, nessuna migrationoperativa. B4: attestazione debug/vhost/devserver e artefatto pulito senza vecchioCKEditor, decisione framework/supporto; eventuali funzioni editor/exportHTML richiedono valutazione prima di attivarsi. Il nuovo artefatto non va sovrapposto alle vecchie librerie: verificare assetCKEditor404, asset aggiornati200, accesso autenticato e flussiHTTP sul destinatario in modalità senza trasmissioni, entro futura autorizzazione al deploy.

Inventario corrente1819:1813/1818hash originari invariati,5divergenze autorizzate(provider,composer.lock,package.json,package-lock.json,package-copy-config.json) e una nuova classe. Originali1818 e manifest194/212/250 conservati. Nessun commit/push/deploy, scritturaDBoperativo, configurazione server o trasmissione reale.


## Preparazione intervento locale Herd e decisione framework — 9 ottobre 2026

[Operazione esatta per autorizzazione](../OPERAZIONE-HERD-ROSS1000-2026-10-09.md). PDO sola lettura conferma base schedinedinotifica,127.0.0.1:3306,utente tecnico tanggo,MySQL8.0.44; cacheconfig assente e nessun DATABASE_URL/DB_SOCKET o overrideDBCLI. OverrideFPM esterni non attestati: riconfermarli prima di intervenire. Letture limitate a catalogo/migration, nessun record personale. Tre pending riconciliate; soltanto protect_istat_cycle necessaria perRoss1000. Tassaexports e lifecycletoken escluse dall'operazione proposta, non dal futuro riallineamento del progetto.

Impatto non limitato a snapshot: allarga credenzialiISTATVARCHAR100→TEXT, cifra valori non vuoti in chiaro di tutte le strutture con APP_KEYesistente, aggiunge metadati export/transmission e due tabelle. down()vuoto: niente migrate:rollback; preparati dumpcompleto consistente/hash/permessi e recupero completo con rimozione delle sole due tabelle nuove. Backup operativo non acquisito né restaurato: prova reale di recuperabilità o relativa autorizzazione ancora necessaria. Migration, cifratura, controlli browser con dati reali e recupero non eseguiti. Nessuna autorizzazione implicita ai comandi del runbook.

SSH: chiave tanggo_spanel offerta esplicitamente a root@217.174.149.23:6543, server non la accetta, exit255 Permission denied(publickey,gssapi-keyex,gssapi-with-mic,password). Nessun comando remoto. Passo manuale: verificare autorizzazione.pub/policyroot mediante consoleSPanel o amministratore su canale fidato; eventuale installazione chiave server non autorizzata ora. ssh-add utile solo se la chiave viene accettata ma richiede sblocco locale. [Istruzioni aggiornate](../SSH-MAC-CHIAVE-AUTORIZZATA-2026-10-09.md).

DecisioneLaravel:11.31.0 conserva7righe/6segnalazioni distinte; condizioni locali verificate in matrice, debugdestinatario non attestato. Policyufficiale: sicurezza11terminata12/03/2026,12fino24/02/2027. Mantenere11non è alternativa sicura equivalente per il lancio pubblico. Percorso raccomandato: approvare preparazione12.69.x e verificarla prima di adozione; simulazione50update/5install non è accettazione. Nessunmajor eseguito, nessunbackport pronto o bypassComposer. Pilota ristretto/localesenza trasporti possibile entro limiti, non validazione di un lancio pubblico. [Impatto e scelta](../IMPATTO-LARAVEL12-2026-10-09.md).

Blocchi aggiornati: **HerdH1** autorizzazione dell'unica migration più backup/recuperabilità e controllooperativo200; **B1** reviewcandidato e futuroSHA senza commit ora; **B2** autorizzazionechiave poi CLI/FPM/MariaDB/segfault; **B3** metadatadestinatario/riconciliazione e recuperabilità reale; **B4** scelta framework supportato più configurazione debug/vhost/assenza devserver e artefatto pulito, provider separati prima dell'attivazione. H1non certifica schema remoto né token/tassaHerd e non azzera B3. Accettazione funzionale locale positiva conservata; BASELINE IN AUDIT — NON VALIDATA non costituisce un difetto. Nessun commit/push/deploy, scritturaDBoperativo, modifica server o trasmissione reale.

Nuova verifica proceduraHerd: backup/upgrade singolo/restore sintetico1test/85asserzioniPASS; conteggi/storico/credenziali sintetiche preservati, runtime ripulito. Rossi di setup e sorgenti originali conservati, nessuna modifica applicativa; globale750/8252 non ripetuta. Prova backup reale ancora mancante.


## Intervento autorizzato Herd e verifica Laravel12 — 9 ottobre 2026

L'autorizzazione esplicita della task consente backup riservato delDBlocaleHerd, prova restore reale isolata e singola migrationISTAT dopo gatepositivo. [VerbaleHerd](../HERD-ROSS1000-INTERVENTO-AUTORIZZATO-2026-10-09.md). Backup61tabelle/2.199.824byte, configurazione/APP_KEY protetti fuoriGit; restore suMySQL8.0.36 senza rete, dati e credenziali/storico identici. Il primoFAILletteraleSHOWCREATE e ilFAILmetadati cached sono preservati; intervento fermato primaDDL, analisi successiva solo lettura. Confronto indipendente finale metadati effettivi con statistiche di sessione aggiornate PASS per tabelle/charset/sequenze/colonne/indici/FK/vincoli/trigger. Nessun campo eliminato dal criterio per ottenerePASS.

Dopo riconferma checksum/hash e integrità operativa prima migration, applicata **solo2026_10_07_180000_protect_istat_cycle**, exit0; due nuove tabelle, una sola registrazione,61tabelle originali e valori credenziali preservati.2campi prima in chiaro ora cifrati, lettura tramite cast realeStruttura e digestriservato identico PASS; APP_KEY/config originali invariati. Chrome reale con sessione preesistenteTanggo/Admin: pulsante→paginaISTATrenderizzata, nienteQueryException/SQLSTATE, campi credenziali vuoti, inviodiretto disabilitato. Nessun dato personale o screenshotpubblico. Non inventatoHTTP200numerico, non disponibile dall'API DOM/accesslogoff. **H1Ross1000locale CHIUSO nel perimetro verificato**; le altre due migrationtassa/token restano non applicate, schemaHerd non dichiarato interamente allineato. Nessunrollback o trasmissione.

[Laravel12 nel worktree separato](../LARAVEL12-VERIFICA-ISOLATA-2026-10-09.md):12.69.3,PHPUnit11.5.57,Yaml6.4.40;50update/5install/0rimozioni, auditComposer0advisory/0abbandonati. PHPminimoeffettivo8.3 già richiesto daOpenSpout, ora dichiarato; runtime8.3.33. Globale**750/8252PASS**, miratiimport16/103 e sicurezza2/16PASS, Chromium**26/26PASS**, build/architetturaPASS; installazione pulita84pacchetti no-dev senza.env/script e autoload12.69.3PASS. La prima globale6erroriAuth derivava dai doppiunitari nonconformi aFactory/Guard; originali conservati, solo doppi corretti,8252aspettative invarianti.5deprecazioniPHPUnit restanoP2 e messaggioproxycorpoincompleto post8PASS resta residuo diagnostico non attribuito adapp. Nessuna modifica business o dei13sorgenti/31evidenze contrattotoken.

Candidato318/indice principale **preservati**; nuove conoscenze documentali in workingtree separato dall'indice. [Manifestdelta12](../laravel12-delta-verificato-2026-10-09.json), quattrofile implementativi/test, ancora nel worktreegestito; frameworkHerd/candidato resta11.31.0, nessuna adozione primaria automatica. Accettazione funzionale locale precedente positiva conservata, accettazione locale deldelta12 positiva; **BASELINE IN AUDIT — NON VALIDATA**, mai validazioneproduttiva.

Blocchi concreti: **B1** review e integrazione/selezione deldelta12verificato nel candidato prima del futuroSHA, nessuncommit ora; **B2** chiaveSSH/root6543 e lettureCLI/FPM/estensioni/MariaDB/segfault; **B3** metadatamigration/schema e backup/restore deldestinatario (Herdnon li attesta); **B4** configurazione debug/cache/vhost/assenza devserver e artefatto pulito npm, provideresterni da validare separatamente prima dell'attivazione. Npm53avvisi giàclassificati invariati, Composer0 si riferisce al lock12 separato. [Comando manuale chiave pubblica](../SSH-COMANDO-CHIAVE-ROOT-2026-10-09.md) idempotente con copiaauthorized_keys; l'assistente non ha modificato file/policySSH sulserver. Nessun cambiamento serverproduttivo, commit/push/deploy o trasmissioneISTAT/Questura/mail.


## Integrazione Laravel 12 autorizzata e nuovo esito SSH — 9 ottobre 2026

[Verbale integrazione e SSH](../INTEGRAZIONE-LARAVEL12-SSH-2026-10-09.md). Delta12 verificato integrato nei quattro file del candidato con hash identici al manifest del worktree; globale750/8252 e Chromium26/26 rimangono evidenze applicabili, nessuna suite ripetuta senza modifica. Candidato318 precedente conservato integralmente in archivio, patch binaria e manifest; nessun commit. Vendor/runtimeHerd11.31.0 invariati, nessuna installazione operativa. B1 preparazione locale completata, review e futuroSHA/pubblicazione restano gate separati.

SSHroot@217.174.149.23:6543 dopo append confermato dall'utente: connessionePASS ma chiave corretta offerta e rifiutata prima della firma, Permission denied(publickey,gssapi-keyex,gssapi-with-mic,password), exit255. Nessun comando remoto: CLI/FPM/MariaDB/segfault (B2), metadata/recuperabilità destinatario(B3), debug/cache/vhost/artefatto pubblico(B4) ancora non attestabili. Comandi manuali sola lettura per identificare listener/percorso effettivo nel verbale; non modificata policy. Composer12patch integrata non certifica esposizione del destinatario né elimina npm53righe. H1Herd chiuso nel perimetro precedente; accettazione locale positiva conservata, BASELINE IN AUDIT — NON VALIDATA. Nessun commit/push/deploy, migrationproduttiva o trasmissione.


## Accesso SSH riuscito e ricognizione destinatario — 9 ottobre 2026

[Verbale sola lettura](../SERVER-SOLA-LETTURA-2026-10-09.md). Autenticazione root@217.174.149.23:6543 chiusa positivamente. PHPCLI/FPM8.3.35,MariaDB11.8.9,Composer2.9.2,Node20.20.2/npm10.8.2,Python3.12.14,Bubblewrap0.10.0 rilevati. Kernel29segfaultPHP inopcache.so il7ottobre,nessuno dal8neljournal disponibile: causa/stabilità ancora aperte, nessuna configurazione modificata. CodiceservitoSHAc21b9185a33d1f5e0720c08f1547af6fa4857b20/Laravel11.31.0; non candidato12. DBmetadata read-only riconciliano7pending esatte, nessuna applicata. Recuperabilità produttiva non attestata dalla semplice directorybackup.

Cacheproduction/debugfalse/appEuropeRome attestati; mailSMTP, configQuestura/ISTAT delcandidato assenti sulserver, non dichiarati trasporti intercettati. Documentrootpublic e handlerFPM83 confermati. CKEditor40.2.0 pubblicoHTTP2 200 suorigineIPeffettiva; curl60loopback preservato e distinto, nessunbypassTLS. B1review/futuroSHA, B2segfault+prove stackisolato, B3backup/restore+7pending, B4artefatto/confine/trasporti restano. Accettazione locale positiva e candidato372 preservati; nuove evidenze documentali fuoriindice. BASELINE IN AUDIT — NON VALIDATA. Nessuncommit/push/deploy,migrationproduttiva,bootstrapLaravel operativo o trasmissione.


## Preparazione recuperabilità e rilascio controllato — 9 ottobre 2026

[Operazione concreta da autorizzare](../OPERAZIONE-PROTETTA-DESTINATARIO-2026-10-09.md): backup DB+file+config/APP_KEY in deposito riservato, restore su nuova istanza MariaDB senza rete, prova delle sette migration perfile e recovery in terza istanza; gate sequenziali R0/R1/R2. Nessuna acquisizione/restore remoto eseguita; consistenza DB/file richiede finestra senza scritture, eventuale manutenzione operativa separatamente autorizzata.

Diagnosi lettura: 49 eventi SIGSEGV PHP nel registro core della finestra contro29 nel kernel, due core disponibili e47assenti; campioniPID1896486/1896671 mostrano php_lint_script→ionCube→OPcache/zend_get_file_handle_timestamp. Non dimostrata causa completa né risoluzione; preparata matriceisolata standard/OPcacheoff/ionCubeoff e provaFPMprivata sintetica senza riavvio servizi. Reviewcandidato372/hashindicePASS, diffcheckapp/test/composerPASS. Rilievo concreto: due BUILD_HASHES supervisor obsoleti(package.json/package-copy-config.json), impedimento preflight da riallineare e provare senza bypass. RitiroCKEditor richiede sostituzione interapublic/build con vecchiasset conservati fuoriDocumentRoot, maioverlay; rollback coordinato provato senza migrate:rollback o perdita di nuove scritture.

B1reviewcandidato completata nel perimetro; futuroSHA/pubblicazione ancora non autorizzati. B2stabilità, B3recuperabilità/provamigration, B4bundledeployhash+asset/confine restano aperti. Accettazione locale positiva conservata; BASELINE IN AUDIT — NON VALIDATA. Nessuncommit/push/deploy, modifica configurazione, restart, migrationproduttiva o trasmissione.


## Backup autorizzato: gate coerenza e correzione supervisor — 9 ottobre 2026

[Intervento preciso e durata da autorizzare](../BACKUP-DESTINATARIO-GATE-COERENZA-2026-10-09.md). Nessuncron/jobLaravel/worker/transazione rilevato nei campioni,0eventiDB e schedulerOFF; sito aperto e manutenzioneassente impediscono garanzia snapshotcoordinato DB/file. Backup/copierestore/R0/R1/R2/stabilità non iniziati, nessuna scrittura remota. Proposta barriera503statica primaLaravel tramiteMaintenance.close/open, drenaggio massimo120secondi e finestra massimo10minuti, nessunrestart/stop/lockDB; serve autorizzazione separata esplicita. APP_KEY/config preservati.

Due hash BUILD_HASHES riallineati ai target package.json/package-copy-config.json dopo reviewclean/Vite/whitelist; tutti i controlli rimangono attivi. Guardiesupervisor66testOK,3saltati, rossooriginale conservato. Non preflightproduttivo: fetch/scratch/bootstrap non eseguiti. Nuovo candidato aggiorna solo supervisor/documentazione/evidenze verificate, precedente372 recuperabile tramitepatchbinaria/manifest. Nessuna globaleLaravel ripetuta per sole costanti guardrail. GateB2/B3 e confineB4 ancoraaperti. Accettazione locale positiva, BASELINE IN AUDIT — NON VALIDATA; nessuncommit/push/deploy/migrationproduttiva o trasmissione.


## Finestra manutenzione autorizzata: backup abortito — 9 ottobre 2026

[Verbale effettivo](../BACKUP-DESTINATARIO-FINESTRA-2026-10-09.md). Attivata503statica e ottenutocodice503, asserzionecombinata marker/headerfallita; acquisizioneabortita prima dump, nessunbackupvalido. Sigilli.env/APP_KEYverificati, barriera rimossa, /loginHTTP200 efilemanutenzioneassenti. Durata0,031secondi,sotto10minuti. Originalesorgente/esito preservati; risposte503raw sovrascritte daldiagnostico riapertura, limitetrasparente: possibilecaseheaderHTTP2 ma causa non dimostrata. Nessun nuovo tentativo operativo dopoFAIL; restore/settemigration/recovery/stabilità fermati come richiesto. Nessunrestart/stop/bloccoDB,migrationproduttiva,commit/push/deploy otrasmissione. Accettazione locale positiva conservata, BASELINE IN AUDIT — NON VALIDATA; B3nonchiuso.


## Backup e recupero destinatario PASS, SIGSEGV CLI riprodotto — 9 ottobre 2026

[Verbale effettivo](../BACKUP-RESTORE-STABILITA-DESTINATARIO-2026-10-09.md). DiagnosticoHTTPcorretto in isolamento(4regressioniPASS), nomi esclusivi perbody/header/code/esito/tempi; codice503,marker eRetryAfter60 distinti, nessuncriterioallentato. Nuovafinestra20:39:48–20:40:20UTC,31,661secondi;503+drenaggioPASS, doppidump efiletreeidentici, APP_KEY/.envsigilliPASS, riapertura/login200. Backupcoordinato in deposito riservato, seconda copiacifrata fuorihost decifrata in streaming con3hashPASS. Nessunsecret/backup/rawHTTP inGit.

R0restoreDB58tabelle e ridumpbyteidentico,16.516file/linkSHAverificati. R1sette migration tutteexit0 sulclone;57tabelleoldcolumns/HMAC e storico separatoPASS;3credenziali cifrate/decifrate/castmodelloPASS,0normalizzazionivuoto-null. R2terzaistanza+nuovaestrazionefile riproducono snapshotcompleto DB/file/APP_KEY, clone migrato intatto. Setupresolver/mappingfalliti e originali preservati, rimedi esclusivamente nelnamespaceprivato. Nessunmigration/restoreproduttivo.

StabilitàCLIstandard **FAILSIGSEGV(-11)** sufilePHPsintetico senzaLaravel/DB; probeOPcacheCLIoff/ionCubeoff/entrambioff exit0. Treprobe non certificanostabilità diunrimedio; FPMfase successiva nonavviata come richiesto dopoFAIL. Nessuna modifica.ini/loader/servizi, corememoria nonesportati. B2bloccato daruntimecombinazioneCLI; B3recuperabilità/migrationisolatiPASSnelperimetro, flussistackestesi ancora subordinatiB2; B1futuroSHA eB4artifactreplacement/confine restano. Candidatoconnuoveutility/evidenze,389precedenterecuperabile;nessunbusinesscodecambiato. BASELINE IN AUDIT — NON VALIDATA, accettazione locale positiva conservata, nessuncommit/push/deploy otrasmissione.


## Diagnosi CLI ripetuta e proposta FPM privata — 9 ottobre 2026

[Verbale tecnico](../DIAGNOSI-PHP-CLI-FPM-2026-10-09.md). Matrice80lintminimi:standard20/20SIGSEGV,OPcacheCLIoff20/20PASS,ionCubeoff20/20PASS,entrambioff20/20PASS; ini/moduli/ordineionCube01→OPcache10 ePHP8.3.35/Zend4.3.35/loader15.5.1 registrati. DipendenzaCLIionCube nonemersa:8.641PHPscreened,0stubencoded/require;450comandiComposer/Artisan(15×10×3varianti)PASSnelclone privato senzaegress, mailarray/providerfalse, nessuntaskreale. Non prova universale di ogniinput oFPM, non individuato bugupstream interno.

Rilievo distinto confermato nelrunnerArtisan:gatehardcodedLaravel11 a137rifiuta12.69.3 anche conOPcacheCLIoff; validatoriintatti, solaosservabilitàcatch in copiaidentificata. Proposta minima NONAPPLICATA:argvPHPdelprogetto con-dopcache.enable_cli=0 indeploy.py eCLIoperatore, riallineamentogate12 verificato inartisan.php, regressionsupervisor; nessunini/loader/pooloperativo. FPMsoltanto preparato consocketUnixprivato,2worker,namespace senza rete/produzione, budget/cleanup/metrichedescritti primadell'avvio. B2runnerallineato+FPMrestano;backup/R0/R1/R2 eaccettazionelocaleconservati. Candidato412indiceintatto, nuoveconoscenze documentali fuoriindice. BASELINE IN AUDIT — NON VALIDATA;nessunrestartoperativo,migrationproduttiva,commit/push/deploy otrasmissione.


## Aggiornamento effettivo — mitigazione CLI e FPM privato, 9 ottobre 2026

Mitigazione autorizzata implementata: PHP del runner con opcache.enable_cli0, gate esattoLaravel12.69.3 e primo config:clear dopo install/check-platform-reqs. Guardie conservate. Mac71raccolti/68PASS/3SKIP, Linux71/71PASS; sequenza completa su fixture con operazioni esterne simulate e recupero snapshot. Config effettiva20/20CLI0; figli Composer @php non ereditano ilflag, quindi no-scripts/no-plugins resta condizione necessaria.

FPM privato senzaegress/socketUnix: 662richieste in15avvii, sentinel200, login/CSRF/anonimo40, IP300+30HTTP429, quotealias92; master terminati e DBprivati fermati. Nessuncrashosservato nelcampione, nessuna dichiarazione di risoluzione degli eventi storici. FreshinstallMariaDB11.8 fallita su normalize_classificazioni storica, primaavvioFPM; conservata e distinta dall'upgrade R1 giàPASS. FlussiFPMautenticati/tenant, misureRSS/coldwarm e Git reale restano da completare: B2PARZIALE/B1BLOCCATO. B3backup/R0/R1/R2 conservati; B4gatevhost/asset/provider separato. Accettazione locale applicativa positiva invariata; BASELINE IN AUDIT — NON VALIDATA, nessuna validazione produttiva.

Evidenze e limiti prevalenti per questa fase: [Mitigazione CLI/FPM](../MITIGAZIONE-CLI-FPM-2026-10-09.md); candidato412 precedente recuperabile. Nessuncommit/push/deploy/configoperativa/restart/migrationproduttiva/trasmissione.


## Completamento FPM autenticato e Git reale — 9 ottobre 2026

[Verbale effettivo](../COMPLETAMENTO-FPM-GIT-2026-10-09.md). FPMprivato:1084richieste/20cicli coldwarm e81logout/revoca/alias/relazionetenantPASS; caricosostenuto985richiestetotali,904in120,44sec/4client,10workerconriciclo,7262campioniRSS e49,949MiBpiccoworkerPASS. Sessioniindipendenti/persistenti,clientiestranei404,CSRF419,righeclienteimmutate,revocacross404/propria302,aliasinvalidati,conversioneanonima,relazioneschedinaestranea404. ZeroSIGSEGV/HTTPinattesi nelcampione; nonrisoluzioneuniversale dei49eventistorici. Originalifalliti conservati:fixturecampiobbligatori eheaderno-store/private distinti dalbugDDL.

Gitrealeread/clone/archive/policyPASS sultreec4e62d5ea70080878f079cae08aa2a787f2621ecdelcandidato445preservato;tree rifiutato comecommit,HEADfaef3cfimmutato. B1restaBLOCCATO:servefuturocommitSHAapprovato/pubblicato eprepare/executeintegrale conquelSHA, nonautorizzati daldivietoattuale. B2proveisolateFPMoraVERIFICATEnelperimetro;gatevhostTLS/proxy/clock/UID/SELinux ecorrelazionestorica separati. B3R0/R1/R2conservati;B4asset/confine/providerancorapendenti.

SQLminimoMariaDB11.8.9riproduce1072:indiceuniquecomposto residuo prima dropcolonna. Rimozioneindice→dropcolonna→unique(nome)PASS solo nelriproduttore;nessunfixmigrationapplicativa. InstallazionenuovaMariaDBBLOCCATA;upgradeprevistononbloccato daquellamigrationgiàapplicata eassente dalle7pending giàPASS. Riconciliare comunque elenconelpreflighteffettivo.

Candidato445precedente recuperabile fuoriGit; rapporto,matrice,Maestro emanifestaggiornati. Accettazionelocaleapplicativapositivaconservata;BASELINE IN AUDIT — NON VALIDATA. Nessuncommit/push/deploy/DBoperativo/configservizi/trasmissione.


## Review di chiusura candidato e gate del preflight — 9 ottobre 2026

[Verbale e procedura](../CHIUSURA-CANDIDATO-2026-10-09.md). Treeinizialee66f9647c48de84b7383e88d9d9eb3a7d9ba3c41verificato:471delta/4568indice,hashmanifestcorrelati,origineGitHubmain osservatafaef3cf comeHEAD. Reviewblob/percorsi,fixtureAPP_KEY e318membriarchiviostorico:nessunriscontrodi segreti/dump/righeoperative/fileestranei;backupcandidatoesattofuoriGit. Aggiornamentochiusuradocumentale produce nuovotreeperreview; nessunulterioredeltaapp/test/runner. Messaggiocommit e sequenzaGit+runnerisolato predisposti,nessuncommit/push/deploy.

**Nuovoblocco confermato dello strumento deploy:**prepare→pending usa root/vendorattuale11.31.0,helperrichiede12.69.3 prima queryDB,install12solo inexecute. Fixtureisolata exit1 ApprovedLaravel12.69.3required.,guardoriginaleintatta. Ilfixprecedenteconfig:clear-after-Composer non risolvequestopassaggio. Le71guardie,Gitarchive/policy,FPM/RSS/tenant,coldwarm eR0/R1/R2PASS restanoVERIFICATInelproprioperimetro; runnerintegraleNONVERIFICATO eBLOCCATO dalpreflight oltrechéfuturocommit/sandboxnonallestita.

Proposta mirata:metadatareadonlybaseline separati dalgate12mutante,nessunallentamentogenericoversioni/sigillo/path/MariaDB/history;regressionitransizione11→12 eversioniestanee prima nuovareviewSHA. Nonimplementata nella taskdocumentale. ChiusuracandidatorilascioBLOCCATA;eventualecommit/pushattuale può soloarchiviare esplicitamentelostatoaudit conilblocco. Gateproduttivi/freshinstallMariaDB separati;accettazionelocaleapplicativapositivaconservata,BASELINE IN AUDIT — NON VALIDATA.

Controlli collegati alpush:nessunhooklocaleeseguibilenon-sample/CIversionata;APIGitHubreadonly0workflow/0webhook,nessunjobavviato. Messaggiodicommit eprocedure includono fail-stop su tree/HEAD/origine e nonautorizzanodeploy.


## Correzione preflight indipendente dal vendor — 10 ottobre 2026

[Rapporto ed evidenze](../CORREZIONE-PREFLIGHT-2026-10-10.md). Corretto il blocco prepare→pending con lettura PDO dei soli metadati, transazione MariaDB READ ONLY, dotenv/identità/cache/path/configDB revisionata e catalogo storico controllati, senza autoload/provider/vendor. Prima di Composer restano requisiti runtime e lock; dopo install/check-platform resta il gate esatto Laravel 12.69.3 prima dei comandi e la riconciliazione rigorosa dello storico. Nessun allentamento delle versioni mutanti, dei sigilli o dei controlli di manutenzione.

Regressioni strumenti Linux 76/76 PASS; macOS 71 PASS/5 SKIP, coperti in Linux. Vendor assente non impedisce i test preliminari: 2 PASS/2 SKIP locali DB, entrambi coperti sul MariaDB sintetico Linux. Vendor11 reale rifiutato nella fase rigorosa, versioni incompatibili e assenza vendor dopo Composer rifiutate; 12.69.3 reale verificato. Storico immutato, scrittura READ ONLY rifiutata 1792. Composer reale validate/requisiti-lock/install-no-scripts-no-plugins/requisiti-vendor PASS senza rete, con vendor12 disponibile: non prova installazione offline end-to-end da vendor11/assente. Sequenza/recupero su fixture PASS, operazioni esterne simulate esplicitamente. Originale preflight e due fallimenti diagnostici conservati; nessuna modifica app/migration/dipendenze.

Blocco tecnico preliminare CHIUSO nel perimetro verificato. B1/runner integrale resta PENDENTE: commit approvato e origine disponibile, sandbox integrale attestata, prepare/execute/recupero sul suo SHA. Il tree non è un commit e non supera quel gate. Gate produttivi, correlazione storica SIGSEGV e fresh install MariaDB distinti e invariati; accettazione applicativa locale positiva conservata. Trees e66/f25 e riferimenti precedenti recuperabili, nuovo candidato e messaggio aggiornati. BASELINE IN AUDIT — NON VALIDATA; nessun commit/push/deploy del progetto, DB operativo o trasmissione reale.

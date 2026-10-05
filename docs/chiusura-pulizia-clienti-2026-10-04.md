# Chiusura pulizia Clienti importati — 4 ottobre 2026

**CLIENTI IMPORTATI — FILTRI E PULIZIA STAGING VERIFICATI**

## Risultati

VERIFICA | TESTATA | RISULTATO
---|---|---
Eliminazione singola | SI | SUPERATA
Seleziona pagina | SI | SUPERATA
Eliminazione selezionati | SI | SUPERATA
Eliminazione risultati filtrati | SI | SUPERATA
Eliminazione batch | SI | SUPERATA
Eliminazione tutti pending | SI | SUPERATA
Altra struttura protetta | SI | SUPERATA
Promossi protetti | SI | SUPERATA
Customers invariati | SI | SUPERATA
Rollback atomico | SI | SUPERATA
Conteggi | SI | SUPERATA
Paginazione | SI | SUPERATA
Mass delete server-side | SI | SUPERATA

```text
CUSTOMERS_BEFORE=16
CUSTOMERS_AFTER=16
SINGLE_DELETE_OK=true
SELECT_PAGE_OK=true
SELECTED_DELETE_OK=true
FILTERED_DELETE_OK=true
BATCH_DELETE_OK=true
ALL_PENDING_DELETE_OK=true
OTHER_STRUCTURE_SAFE=true
PROMOTED_SAFE=true
CUSTOMERS_UNCHANGED=true
ROLLBACK_OK=true
COUNTS_OK=true
PAGINATION_OK=true
MASS_DELETE_SERVER_SIDE=true
```

## Bug reale trovato e corretto

Prima della correzione, deletePendingImportedRows completava la transazione della DELETE prima del refresh dei contatori. Un errore simulato del servizio durante refreshBatchCounters lasciava la riga cancellata e total_rows=1. Prova reale sotto transazione esterna: ROLLBACK_OK=false, RIGA_PRESENTE=false, CONTATORE_BATCH=1. Il rollback finale delle fixture ha comunque ripristinato il database.

Correzione minima nel solo CustomerImportController: individuazione batch, DELETE e aggiornamento contatori nella stessa DB::transaction. Il numero riportato usa le righe realmente eliminate dalla DELETE. Nessun cambiamento ai criteri di selezione, ownership, promozione o ai moduli protetti.

Prova successiva: servizio test-only esegue il refresh reale dei contatori e poi solleva TEST_ERRORE_CONTATORI. Prima di annullare le fixture, le impronte delle tabelle coincidono con quelle precedenti all'azione; le due righe del batch sono nuovamente presenti e i contatori sono ripristinati. ROLLBACK_OK=true. Non è rimasto codice permanente di simulazione.

## Evidenze funzionali

Evidenza precedente riutilizzata: {"filter_ok":true,"bulk_delete_ok":true}. Non ripetuto l'audit generale Clienti. La cancellazione selected è stata ricontrollata soltanto perché la correzione del metodo comune ne modifica il confine transazionale.

- Singola: action reale deleteImported, riga presente prima e assente dopo; riga esterna e Customers conservati. Richieste singole su riga esterna/promossa respinte.
- Filtrati: fixture A/B/C/D/E come da richiesta; q=Mario, tipo_cliente=Ospite e batch corrente eliminano solo A. B (Componente), C (altro batch), D (altra struttura), E (promossa) restano. ID estranei aggiunti alla richiesta non influenzano il filtro server-side. D appartiene a un batch dell'altra struttura: la struttura è proprietà del batch, non della riga.
- Selected: ID valido mescolato a ID esterno e promosso; eliminato soltanto il valido.
- Batch: 3 pending e 1 promossa in A, 2 pending in B e 2 nell'altra struttura. Dopo l'azione A pending=0, promossa presente, B=2, esterna=2. A total_rows=1 e imported_rows=1. Il totale storico non viene confuso col pending operativo.
- Batch e filtro manipolati con batch_id esterno: nessuna scrittura.
- Tutti pending: elimina solo pending della struttura corrente anche con struttura_id esterno inviato dal client; due righe esterne e la promossa restano.
- Customers: conteggio globale senza scope 16→16, Customer associato alla promossa presente, impronta del contenuto di tutti i Customers identica.
- Lista e paginazione: 21 righe, pagina 2 con una riga; eliminata quella riga, redirect senza page, ritorno alla prima pagina valida. Conteggio 20, 20 righe visibili, lastPage=1. Dopo tutti pending, lista operativa vuota anche con promossa ancora presente.
- Integrità finale: confronto impronte delle tabelle prima/dopo rollback esterno positivo. Nessuna fixture nel DB. I contatori AUTO_INCREMENT possono avanzare anche con rollback.

## Prova UI Seleziona pagina

Nel browser Chrome è stata aperta, tramite server temporaneo su localhost, la pagina HTML prodotta dal rendering reale della view imported/index.blade.php con 20 righe TEST. Nessuna modifica al JavaScript della view. Primo clic reale sull'header: 20 righe selezionate su 20. Secondo clic reale: 0 su 20. La selezione opera sulle sole checkbox renderizzate nella pagina; non esegue una query né acquisisce ID delle altre pagine.

Questa è una prova browser del comportamento della view renderizzata, non un ciclo HTTP completo di cancellazione via browser. Le operazioni distruttive sono state eseguite tramite le stesse action reali della UI, sotto transazione e rollback.

## Elaborazione server-side e limiti delle misure

Query registrata durante filtered: una sola DELETE su customer_import_rows, con EXISTS sul batch/struttura, imported_customer_id IS NULL, filtri JSON e batch_id. Non vengono inviati migliaia di ID dal browser: i form filtered/batch/all_pending inviano modalità e filtri; solo selected invia gli ID manualmente selezionati. Batch e all_pending usano lo stesso metodo e una DELETE SQL, senza ciclo DELETE per riga. La lista carica 20 righe per pagina.

Il ciclo di refresh opera per batch, non per riga cancellata. Il servizio esistente carica sul server status e imported_customer_id delle righe rimaste nel batch per ricalcolare i contatori; non le invia al browser. La soluzione evita il costo di 12.000–20.000 richieste/DELETE individuali. Non è una certificazione dei tempi a 20.000 righe: nessun benchmark di quel volume è stato eseguito. Le sei query registrate sul piccolo campione richiedono complessivamente circa 7,11 ms; filtri JSON/LIKE e refresh possono costare di più su dataset grandi. Non è stata eseguita una prova concorrente con promozioni simultanee.

## Tracciabilità

Evidenze locali in `/tmp/pulizia-clienti-20261004/`:

- atomicita-prima.log: difetto riprodotto prima della correzione.
- verifica.php e bootstrap.php: harness con action reali, guardia DB locale/InnoDB e finally con rollback.
- esiti.log: risultati backend finali, tutti positivi.
- query-filtered.json: SQL e tempi misurati.
- correzione.diff: sola differenza rispetto all'inizio di questa verifica.
- baseline-hashes.json: impronte iniziali di tutti i file versionati e non ignorati.
- runtime/lista.html: rendering usato per i clic nel browser.

Il blocco PHPUnit HTTP TEST_ISOLATION_REQUIRED non è stato aggirato; non sono stati rilanciati test Componenti o suite già dimostrate. php -l è solo controllo sintattico supplementare, non fondamento del verdetto.

## File e attribuzione delle modifiche

### A. File dell'implementazione precedente di filtri/pulizia

Questi file risultavano già modificati/aggiunti all'avvio; confronto con la baseline della precedente verifica workflow:

- app/Http/Controllers/CustomerImportController.php
- resources/views/customers/imported/index.blade.php
- routes/web.php
- tests/Feature/CustomerImportedFiltersDeleteTest.php

### B. File modificati durante questa verifica

- app/Http/Controllers/CustomerImportController.php — correzione atomica descritta sopra, su file già modificato in precedenza.
- docs/chiusura-pulizia-clienti-2026-10-04.md — questo nuovo report.

### C. Altri file già modificati/non versionati nel working tree, non toccati da questa task

- app/Http/Controllers/CalendarioController.php
- app/Http/Controllers/ComponentiImportController.php
- app/Http/Controllers/SchedinaController.php
- app/Services/ComponentiImportService.php
- app/Services/CustomerImportService.php
- app/Services/NotificheService.php
- app/Services/PresenzeReportService.php
- app/Services/TassaDiSoggiornoService.php
- app/Support/Componenti/ContrattoImportazioneComponentiV1.php
- app/Support/Componenti/DatiComponenteNormalizzati.php
- resources/js/ui/config-ux.js
- resources/views/customers/export.blade.php
- resources/views/customers/import/index.blade.php
- resources/views/schedina/componenti/import.blade.php
- resources/views/schedina/new.blade.php
- resources/views/schedina/partials/form.blade.php
- resources/views/schedina/partials/scripts.blade.php
- tests/Unit/ComponentiImportServiceTest.php
- tests/Unit/DatiComponenteNormalizzatiTest.php
- .github/copilot-instructions.md
- AGENTS.md
- app/Support/Anagrafica/EtaOperativa.php
- docs/audit-clienti-2026-10-04.md
- docs/chiusura-workflow-clienti-2026-10-04.md
- docs/verifica-finale-clienti-2026-10-04.md
- tests/Unit/ComponentiImportFormTest.php
- tests/Unit/ComponentiImportReviewStatusTest.php
- tests/Unit/CustomerImportServiceTest.php
- tests/Unit/EtaOperativaTest.php
- tests/Unit/componenti-import-submit.test.mjs

**MODULI PROTETTI MODIFICATI: NESSUNO.** Schedina invariata rispetto all'inizio della task. Le modifiche pregresse ai moduli protetti visibili in git status non sono attribuite a questa verifica.

Controlli conclusivi richiesti: git diff --check, git status --short --untracked-files=all, git diff --stat, git diff --name-only. Nessun commit, push, deploy, reset, clean o checkout distruttivo.

# Liste e export — Eliminazione individuale

**LISTE E EXPORT — ELIMINAZIONE INDIVIDUALE VERIFICATA**

## Analisi del percorso reale

Ogni riga di Anteprima clienti filtrati è un App\Models\Customers della tabella clienti, prodotta da CustomerExportController::index; paginazione 15 righe. Lo storico e i soggiorni derivano da schedina.customer_id. Nel database attuale i riferimenti espliciti al cliente sono schedina.customer_id, componenti.customer_id, customer_import_rows.imported_customer_id e duplicate_customer_id. Richiesta è anche un valore CRM del Customer; non è una tabella di soggiorni separata. Le richieste web collegano la schedina. Lo schema locale ha una FK componenti→clienti con SET NULL: la nuova azione impedisce di arrivare a quell'effetto indiretto.

La route canonica customer.destroy usa CustomerController::destroy: archivia tramite CestinoService::archiveModel e cancella il record, ma non controlla i riferimenti. Non viene richiamata direttamente perché non soddisfa la protezione richiesta. La nuova azione riutilizza il servizio canonico del cestino e aggiunge i controlli in una transazione. La route canonica precedente e il comportamento delle altre pagine non sono stati modificati.

## Implementazione

- Nuova route DELETE /clienti/liste-export/{id}, nome customer.export.destroy, ID numerico e middleware del gruppo autenticato esistente.
- Nuovo metodo CustomerExportController::destroy(Request $request, int $id).
- Struttura corrente obbligatoria; query Customers conserva gli scope di autorizzazione e aggiunge esplicitamente struttura_id corrente. ID esterno/manipolato restituisce 404; struttura non selezionata 403.
- Lock sul Customer nella transazione. Prima dell'archiviazione verifica, senza scope che nascondano riferimenti, schedine, componenti, riferimenti staging e snapshot storici del cestino con customer_id diretto o nella schedina incorporata. Sono letture, non modifiche ai moduli collegati.
- Se esistono riferimenti: nessuna cancellazione o archiviazione; messaggio «Cliente non eliminato: esistono dati storici o collegamenti applicativi da preservare.»
- Se non esistono riferimenti: snapshot nel cestino e rimozione del solo Customer, atomicamente. Nessuna cascade introdotta.
- Redirect a Liste e export con filtri conservati e pagina riportata a 1, evitando l'ultima pagina vuota.
- Pulsante compatto btn-soft-danger con icona ri-delete-bin-line nella stessa riga delle tre azioni esistenti; CSRF e metodo DELETE.
- Conferma nominativa attraverso data-confirm-text, opzione del gestore UI esistente; fallback onsubmit nativo. Nessuna modifica al JavaScript condiviso. Click sul pulsante non attiva il link della riga.

## Esiti richiesti

Controllo | Esito
---|---
pulsante Elimina | SI
conferma eliminazione | SI
eliminazione server-side | SI
cliente rimosso dalla lista | SI
isolamento struttura | SI
ID manipolato protetto | SI
storico protetto | SI
schedine protette | SI
filtri preservati | SI
contatori coerenti | SI
paginazione coerente | SI
CSV invariato | SI
Email invariato | SI
WhatsApp invariato | SI
Postale invariato | SI
Import Clienti invariato | SI
Clienti importati invariato | SI
Componenti invariato | SI
moduli protetti invariati | SI

## Prove effettuate

Harness /tmp/export-delete-20261004/probe.php, risultati esiti.log: controller e servizi reali, database locale InnoDB, sole fixture TEST e finally con rollback esterno. Tutte le verifiche finali positive.

1. Sedici Customers TEST filtrati: seconda pagina con una riga. Cancellata quella riga, snapshot nel cestino presente, totale e tre contatori di contatto/consenso passano a 15, ultima pagina=1, cliente assente dalla lista.
2. q, tipo_cliente e marketing_consent preservati nel redirect; pagina eliminata dal redirect.
3. Quattro export confrontati byte per byte con il controller precedente caricato sotto nome distinto: identici prima e dopo la cancellazione; dopo, intestazione più 15 righe.
4. Customer TEST assegnato a un'altra struttura realmente esistente: ID e struttura_id manipolati respinti senza scritture. Struttura corrente assente respinta.
5. Fixture separate con schedina, componente, riferimento importato, riferimento duplicato e storico cestino: per ciascuna, impronta delle tabelle identica prima/dopo tentativo, warning restituito e Customer conservato.
6. Errore simulato dopo creazione dello snapshot nel cestino: rollback ripristina sia Customer sia archivio, senza scritture parziali.
7. Schedina e componente TEST ancora presenti dopo i tentativi. Impronte finali del database identiche a prima delle fixture, compreso cestino_items. Nessun cliente reale eliminato; AUTO_INCREMENT può avanzare dopo rollback.
8. Browser sulla pagina reale: 15 pulsanti nella prima pagina, conferma con nome e cognome, clic Annulla, stessi 15 pulsanti e lista invariata. Layout ispezionato visivamente: quattro icone compatte affiancate. La prima ispezione ha evidenziato il testo generico del gestore condiviso; risolto usando data-confirm-text nella sola view e ricontrollato nel browser.

Il primo tentativo dell'harness aveva un ID struttura fittizio non ammesso dalla FK: fixture corretta usando un'altra struttura esistente, nessuna modifica al codice applicativo per quel problema. Nessun bypass TEST_ISOLATION_REQUIRED: le prove sono controller/service in CLI, non PHPUnit HTTP. Nessuna prova di concorrenza eseguita.

## File modificati in questa task

- app/Http/Controllers/CustomerExportController.php
- resources/views/customers/export.blade.php
- routes/web.php (sola aggiunta della route export.destroy)
- docs/eliminazione-individuale-export-clienti-2026-10-04.md (questo report)

Controller export, view e routes sono confrontati con copie iniziali in /tmp/export-delete-20261004. Tutti gli altri file della baseline restano identici: le modifiche pregresse visibili nel working tree non sono attribuite a questa task. Nessuna modifica a Import Clienti, Clienti importati, customer_import_rows, Schedina, Componenti, altri moduli protetti o schema DB.

Controlli conclusivi: git diff --check, git status --short --untracked-files=all, git diff --stat, git diff --name-only. Nessun commit, push, deploy, reset o clean.

# Liste, import ed export — 16 ottobre 2026

**Area verificata nel runtime ufficiale isolato. Pubblicazione PARZIALE, fermata prima PREPARE per capienza. BASELINE IN AUDIT invariata.**

Area unificata «Liste, import ed export», due schede con URL esistenti e navigazione lato server, viste condivise e sole funzioni correnti. Nessuna migrazione. Ricerca esplicita tramite GET/Applica filtri e reset: un errore HTTP non può essere scambiato per un elenco vuoto né lasciare risultati precedenti sotto filtri nuovi.

Liste: titolo/spiegazione, quattro contatori sull'intero insieme filtrato, filtri già supportati dal controller resi selezionabili, azioni export e anteprima con nove colonne conservate. Export su tutti i risultati; email e WhatsApp conservano i consensi richiesti. Ritorno da modifica/salvataggio/stampa/storico con filtri e pagina separati per struttura; reset della provenienza quando si apre la Lista normale. URL di ritorno costruito soltanto con route note e parametri validati, nessun redirect arbitrario.

Import: titolo, contatori globali della struttura anziché somma della sola pagina, Carica file CSV/TXT con separatore punto e virgola, Modello e regole espandibile e storico. Viste verifica/correzione/staging ricomprese nella navigazione comune. Caricamento separato dalla conferma definitiva.

Protezioni: gli stessi controlli rigorosi del controller clienti applicati a tutte le azioni import/export, inclusi accessi diretti, CSV e modello. Rilevamento duplicati di catena limitato all'intersezione fra proprietà comune e strutture autorizzate per l'attore; in assenza di attore soltanto struttura corrente. Nessuna estensione di permessi o cambi di schema/dati operativi.

Lacune preesistenti accertate, PARZIALI: missingRequiredFields usa una mappa vuota; confirmImportedRow richiede nome e cognome ma non richiama CustomerWizardValidation. Le righe marcate valide non attestano la conformità completa al wizard. La normalizzazione CAP ripulisce le cifre e completa Comune/Provincia solo per corrispondenza univoca; incongruenze vengono segnalate e conservate. Non è presente una normalizzazione geografica autonoma di via e civico: testo indirizzo conservato. Interfaccia corretta per descrivere queste capacità effettive; nessuna falsa regola operativa.

## Verifiche effettive

VM ufficiale, namespace senza rete, database/storage effimeri e dati sintetici: **20 test PHP / 331 asserzioni PASS**, **3 scenari Chromium PASS** a 1440, 768 e 390 px. Menu desktop, due schede, ricarica, accessi esistenti, filtri/reset, ritorno da cliente, file valido e PDF rifiutato, verifica e storico, assenza di overflow e di errori JavaScript. Sei immagini delle due schede e trace conservate; tutte sei ispezionate per spazi, allineamenti e adattamento; immagini dei beni produttivi escluse dalle fixture, senza modificare loghi/avatar reali. Ruoli Superadmin/Admin/Proprietario/operatore e dinieghi interstruttura provati in PHP; browser eseguito come Superadmin sintetico, nessuna attribuzione ai ruoli non attraversati in browser.

Contatori export su18 risultati con anteprima15/pagina, CSV19 righe compresa intestazione anche dalla pagina2, email3 righe compresa intestazione e solo2consensi. Dodici importazioni/duepagine,36righe/24valide/12dacompletare: contatori completi ed estranei esclusi. Upload mantiene4righe in staging e nessun nuovo cliente; conferma successiva crea la sola riga valida, riga non importabile conservata. Duplicati file/hotel/catena e restrizione dell'operatore provati; CAP ripulito/comune-provincia univoci e dato ignoto conservato con verifica manuale. Validazione comune al wizard resta lacuna accertata.

**15 guardie, 23 override rifiutati, build e GEO immutabile PASS.** Nessun test Laravel su Herd o database quotidiano, nessuna mutazione produttiva/invio. Nessuna migrazione o modifica di dati reali.

Storico dei tentativi conservato in `output/liste-import-export-2026-10-16/`: R1/R2 comando inclusioni respinto; R3 nuova vista non tracciata nella copia VM e fixture senza user_id; R4 asserzioni esterne al contesto middleware e selezione struttura non esplicita; R5 azioni icona prive di nome accessibile (corretto); R6 test upload fermo sulle conferme esistenti; R7 asserzione ultima riga incompatibile con l'ordinamento primario della relazione. PDF sintetico corretto per avere MIME PDF effettivo, non testo con estensionePDF. R8: selettore file generico ambiguo con il campo nascosto del dialogo SweetAlert, corretto usando l’etichetta File clienti. R9: selettore esatto del reset non comprendeva l’icona decorativa nel nome accessibile, corretto. R10:20test/331asserzioni e3browser PASS; ispezione visiva rileva stato storico DRAFT ancora in inglese, sostituito riusando la mappa italiana esistente. R11 contiene la conferma definitiva. Nessun aumento di timeout/retry o modifica delle guardie.

## C5 e allineamento

Preflight dopo i collaudi: **1338986496 byte liberi**, fabbisogno prudenziale **3676217344 byte**, incluso margine invariato **1073741824 byte**. Deficit **2337230848 byte**. Stima ricalcolata dalle occupazioni attuali del recupero e degli ingressi cifrati storici, non picco nuovo attestato. **STOP_CAPIENZA_PRIMA_PREPARE**: nessun PREPARE, nuovoT0/S0/RUN, barriera o manutenzione; nessuna cancellazione o crescita VM. DB/SQL/chiavi/cifrati/upload/diagnostici preservati.

Produzione riletta in sola lettura: `cf1deef92e867fc3b6a2e82318001d18d7887282`, Git pulito, FPM attivo e barriere assenti. La nuova area NON è pubblicata. Online delle due nuove schede e monitor post-rilascio PENDENTI; i vecchi risultati C5 non vengono riusati come nuove prove. Commit/push selettivi e SHA effettivi locale/GitHub/VM/produzione soltanto nella ricevuta esterna `output/liste-import-export-2026-10-16/ricevuta-finale.json`, scritta dopo tali azioni. Allineamento completo PARZIALE finché produzione resta alla baseline precedente.

[Manifest delle verifiche e impronte](evidenze/liste-import-export-2026-10-16/verifica.json). Lavoro estraneo preservato: tre altri documenti storici e tutte le modifiche preesistenti al Maestro escluse dal commit, insieme agli untracked estranei. Nessun reset/stash/clean o push forzato.

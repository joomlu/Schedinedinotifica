# Tipo cliente: catalogo di sistema immutabile — 14 ottobre 2026

**BASELINE IN AUDIT. Verifica e rilascio in corso; formulario non ancora confermato.** L'utente autorizza espressamente analisi, correzioni indispensabili, prove isolate, documentazione, commit, push e pubblicazione con la C5 vigente. Nessun invio a enti.

## Baseline e dati effettivi

Locale, GitHub main e produzione verificati in sola lettura allo SHA `dd847206254ce34480174d161b9a217cf118de75`; Git produttivo pulito. Modifiche documentali preesistenti preservate ed escluse dal commit selettivo.

La produzione contiene esattamente tre righe: ID1 OSPITE/Ospite, ID2 COMPONENTE/Componente, ID3 RICHIESTA/Richiesta, tutte attive; corrispondono al seeder. Nessun extra, duplicato o codice ambiguo rilevato; nessun elemento TipoCliente nel cestino e nessuna FK verso tipo_cliente. I clienti conservano la classificazione testuale in clienti.type_housed: 11 Ospite, 1 Componente, 1 Richiesta e 1 valore vuoto precedente, conservato senza riclassificazione arbitraria. Le relazioni clienti/schedine/componenti usano i rispettivi ID, non gli ID del catalogo. Nessun dato operativo corretto alla cieca.

## Comportamento e modifiche

`/tipo_cliente` offre soltanto consultazione, ricerca e paginazione, con codice, descrizione e stato. Unica spiegazione: «Classificazioni predefinite del sistema, non modificabili.» Rimosse azioni, switch e modali del catalogo. Il componente condiviso crud-table permette di omettere il pulsante di creazione, mantenendo i pulsanti dei formulari che lo specificano.

POST, PUT, PATCH e DELETE vengono respinti con403 per tutti i ruoli, incluso Superadmin; ID numerici inesistenti ricevono404; ID malformati sono esclusi dal vincolo numerico e PUT riceve405. Nessuna richiesta scrive. Anche salvataggio/eliminazione del modello, archiviazione e ripristino dal cestino sono bloccati. Nessun nuovo pannello o sistema di autorizzazioni. Il catalogo resta comune: la consultazione non dipende dalla struttura; i dati dei clienti e delle schedine restano isolati.

`TipoClienteSistemaService` definisce le tre classificazioni controllate dal codice. Seeder e nuova migrazione `2026_10_14_030000_initialize_tipo_cliente_sistema` inizializzano automaticamente e in transazione nuove installazioni ed esistenti. Inseriscono soltanto righe ufficiali mancanti; allineano descrizione/stato ufficiali conservando gli ID esistenti; un secondo passaggio già conforme non scrive. Extra preservati nel DB e fuori dal catalogo/selettori ufficiali di clienti, import/export e schedine; nessun truncate/fusione/eliminazione. Codice ambiguo per capitalizzazione interrompe la transazione senza inventare classificazioni. La migrazione down non elimina catalogo o riferimenti; il rilascio si recupera dal checkpoint coordinato.

La classificazione del singolo cliente resta modificabile nei flussi esistenti. Ospite e Componente seguono i requisiti già previsti; Richiesta consente dati incompleti, ma il passaggio a schedina resta soggetto ai controlli esistenti. Dimostrato e corretto un difetto in CustomerController: l'unione PHP degli array manteneva `nullable` anziché le regole required già scritte per sesso e numero documento. Sostituita con array_replace, senza nuove regole di dominio. OSPITE non viene equiparato a CAPO FAMIGLIA/CAPO GRUPPO: relationship della schedina e relazioni Componenti restano distinte e invariate. Questura invariata.

Dipendenza indispensabile per l'installazione fresca: migrazione storica normalize_classificazioni falliva su MariaDB11.8.9 con1072 per l'indice composto residuo alla rimozione della colonna. Corretto soltanto l'ordine: rimozione indice composto prima della colonna; algoritmo di classificazioni invariato. Già applicata in produzione, non viene rieseguita durante l'upgrade. I test originali RefreshDatabase e le loro asserzioni restano invariati.

## Prove e limiti

Tutti i test Laravel e Chromium vengono eseguiti nella VM ufficiale, namespace senza rete e DB/processi/checkout effimeri attestati, senza MySQL/Herd reale. Build/GEO e23rifiuti di override PASS.

Prima esecuzione FAIL: vista modificata con endif errato, confronto fixture non riletto, regressioni RefreshDatabase fermate dalla migrazione storica. Seconda FAIL conserva la dimostrazione dei campi obbligatori mancanti. Revisione automatica ha respinto la sostituzione di RefreshDatabase con DatabaseTransactions per il rischio di falso PASS: proposta non eseguita né aggirata. Corretto il difetto reale della migrazione, poi regressioni originali PASS:48test/324asserzioni, CRUD vietato quattro ruoli, ricerca/catalogo comune, ID/dati/extra preservati, tre classi in creazione e modifica, required e Richiesta incompleta, cliente estraneo404, componenti/principale e riferimenti cross-struttura. Prova definitiva PASS:49test/330asserzioni, comprendente installazione fresca con RefreshDatabase originale e seed effettivi ripetuti;15guardie PASS.

Primo Chromium FAIL per aspettativa erronea della nuova prova: il campo classificazione della schedina collegata conserva correttamente Richiesta; non deve essere forzato a Ospite. Corretto il test, nessun cambiamento applicativo. Chromium definitivo PASS:2flussi in38,4secondi. Consultazione, ricerca/reset, nessun pulsante/modal/azione; selettori e classificazione della schedina collegata conservati; due strutture e cliente estraneo404. Creazione/modifica UI in Ospite/Componente/Richiesta tramite il flusso bozza esistente, con annullamento e conferme corrette. I salvataggi completi e gli obbligatori delle tre classi sono verificati in PHPUnit. Timeout dei primi locatori UI e aspettative di risposta/ID errate delle nuove prove restano nei diagnostici: corretti i test senza rimuovere requisiti; nessuna modifica di codice per mascherarli. C5 ancora da eseguire.

## Pubblicazione

Pendente nuova composizione sigillata sulla baseline verificata, checkpoint S0 fresco recuperato indipendentemente, unica nuova migrazione autorizzata, controllo conservazione, browser online, monitor e allineamento SHA. Nessuna vecchia approvazione o piano viene riutilizzato come autorizzazione. Finestra C5 vigente1800secondi, acquisizione S0entro600, cutoff1379 e riserva421; guardiano indipendente e recupero coordinato. Non dichiarato alcun PASS produttivo anticipato.

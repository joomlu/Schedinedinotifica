# Questura / Alloggiati Web — verifica integrale del 27 ottobre 2026

**Stato: PARZIALE. Qualifica isolata PASS; pubblicazione e accettazione reale non ancora eseguite. BASELINE IN AUDIT invariata.**

## Mandato e conservazione

Incarico umano integrale: verificare e completare il circuito Questura, preservando i flussi commerciali e i dati esistenti; autorizzati modifiche, Git, PR, integrazione e pubblicazione tramite il protocollo del progetto. Nessun invio reale o dichiarazione ufficiale autorizzati. Credenziali mai richieste in chat. Worktree indipendente da main `9e27075819ce6da430f288126757c72f4a9405d0`; checkout originario `a6d406470aa63d7ff69422efcc0b1c14033e0ee5` e 2.751 file/link preesistenti attestati privatamente prima delle modifiche. Nessun reset, clean o staging indiscriminato.

Prima dei correttivi è stata redatta una matrice interna con stato esistente, verifiche e fonti. La lettura iniziale della produzione mostra SHA `c226dd8072f260eff938cd0651cdbbecbcdd36b9`, Git pulito, sorgenti Questura allineati a main, sette strutture attive, nessuna terna completa di credenziali, zero export/trasmissioni/ricevute/riserve; 66 tabelle, FPM attivo, barriera assente. Sono stati letti solo conteggi e impronte, senza estrarre valori personali o segreti.

## Fonti ufficiali

Acquisizione 27 ottobre 2026, TLS ordinario, nessuna autenticazione o chiamata Test/Send reale:

- [Manuale strutture ricettive](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEALBERGHI.pdf), 35 pagine: capitolo 12, pagine 31–34, tracciato standard UTF-8 di 168 caratteri, massimo 1.000 alloggiati, codici 16–20, capo prima dei componenti, documenti dei componenti vuoti. SHA-256 `28857705e0c419a3911317f943ac4c1782893f6503a8c9d6484a5018b31462c6`.
- [Manuale Web Service](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEWS.pdf), revisione 01 del 24 gennaio 2022, 21 pagine. SHA-256 `72d6c612ef616453e417f15e723b8fe44fd271cffab38671ec40823d568763a1`. Distinto profilo Gestione Appartamenti con record di 174 caratteri e identificativo di sei caratteri: l’applicazione usa il profilo standard. Prima dell’attivazione occorre confermare il profilo assegnato all’utenza; non è attestata compatibilità con Gestione Appartamenti.
- [WSDL ufficiale](https://alloggiatiweb.poliziadistato.it/service/service.asmx?WSDL), SHA-256 `36d5279a0577f0b116ac74f6664246532685bc0178b1d6c6a1f7926c11235cab`, identico allo snapshot del progetto. Operazioni documentate GenerateToken, Authentication_Test, Test, Send, Ricevuta e Tabella; nessuna API di riconciliazione dello stato per singola riga inventata.
- Quattro [cataloghi pubblici](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/): comuni, stati, documenti e tipi alloggiato risultano byte-identici agli snapshot esistenti; nessun aggiornamento arbitrario. Il primo confronto diagnostico aveva normalizzato solo una parte dei CRLF: ripetuto correttamente sui byte originali, senza cambiamento delle fonti.
- [DM 16 settembre 2021, GU del 14 ottobre 2021](https://www.gazzettaufficiale.it/atto/serie_generale/caricaArticoloDefault/originario?atto.codiceRedazionale=21A06000&atto.dataPubblicazioneGazzetta=2021-10-14&atto.tipoProvvedimento=DECRETO): per soggiorni non superiori a 24 ore, comunicazione entro sei ore dall’arrivo; copie per la comunicazione eliminate dopo la ricevuta, ricevuta conservata cinque anni. La home del portale conserva un’indicazione precedente sull’arrivo stesso: prevale la disposizione normativa aggiornata. Non si deduce dalla sola data PMS il rispetto dell’orario legale.
- [Garante, chiarimenti del 29 aprile 2026](https://garanteprivacy.it/web/guest/home/docweb/-/docweb-display/docweb/10244289): identificazione e copie dei documenti. La minimizzazione Questura non autorizza una cancellazione generalizzata dei dati PMS trattati per altre finalità.

Requisiti territoriali, abilitazione dell’utenza, eventuale canale alternativo PEC/fax e profilo effettivo devono essere confermati dalla Questura competente; nessun automatismo sostitutivo. Nuovo WSKEY necessario dopo cambio password secondo il manuale.

## Esistente e correttivi

Erano già presenti autenticazione e isolamento per struttura, cataloghi verificati, TXT standard, download e archivi temporanei, cifratura delle credenziali, token e TLS verificato, timeout, esiti minimizzati, riserve idempotenti per identità ospite, vincolo server fra Test e Send, storico paginato, acquisizione e conservazione ricevute e finalizzazione con minimizzazione. La sola presenza del codice non costituisce qualifica.

Difetti riprodotti sul candidato di base e corretti nello scope:

1. Anteprima senza file effettivo e senza no-store; download e Test potevano operare dopo una modifica non ricontrollata. Ora anteprima TXT e SHA-256 dei byte, impronta obbligatoria confrontata prima di archiviazione/trasporto, sia per periodo sia per schedina. I filtri e le pagine della lista non cambiano il file dell’intero periodo. Il vincolo server Test/Send resta ulteriore e indipendente dall’impronta browser.
2. Traslitterazione o rifiuto di nomi UTF-8 e padding in byte. Ora caratteri preservati, offset e limiti in caratteri UTF-8, senza troncamenti; controlli, righe aggiuntive e caratteri invisibili di controllo rifiutati. La conformità strutturale locale non prova l’accettazione dell’alfabeto da parte dell’ente.
3. Nascita successiva all’arrivo e quantità incoerente coi componenti potevano raggiungere il generatore tramite dati preesistenti. Ora bloccate con indicazione di correggere la schedina, senza modificare automaticamente dati PMS.
4. Periodo malformato causava errore 500: validazione prima del parsing.
5. Il form operativo del proprietario non aveva gli indicatori presenti nel form amministrativo: stato delle credenziali e controllo visibilità allineati, senza ripopolare segreti.
6. Storici di trasmissioni ed export finalizzati senza ricevuta collegata potevano interrompere la pagina: resi consultabili con avviso di verifica, senza attestare accettazione.

7. Allineati i pulsanti al guardrail OFF: anteprime e archivi consultabili, azioni mutanti disabilitate nell’interfaccia e sempre bloccate lato server. Indicatori di PUK/codici legacy non utilizzati rimossi dalla configurazione visibile, senza modificare dati o schema; contatori della lista distinti dall’intero periodo.
8. Filtri non testuali provocavano errore 500: riprodotto in R12-RED-FILTRI (18 test, un fallimento atteso), aggiunta validazione prima delle conversioni.
9. Descrizioni dei codici remoti 11/12 prive di semantica ufficiale verificata sostituite con messaggi neutri che conservano il codice e rimandano al portale, senza mostrare testi remoti arbitrari.
10. Test positivo ora leggibile come verifica del tracciato su tutte le righe, sia nel feedback sia nello storico; `unknown` e `accepted=false` restano invariati, nessun Test positivo presentato come esito di Send o come accettazione.

11. Schede dei due storici disposte in griglia responsive: dati e azioni impilati sul mobile; campo data della ricevuta interamente visibile, verificato geometricamente e nelle nuove catture.

Nessuna migrazione prevista. Nessuna modifica al dominio Arrivi, Web Check-in, ISTAT o Tassa. Aggiornate fixture obsolete; il controllo statico sulla cancellazione componenti nei vecchi controller è sostituito con una prova effettiva del sincronizzatore condiviso, preservando record di altra schedina e altra struttura.

## Qualifica e limiti

Tutte le prove Laravel e browser avvengono esclusivamente nella VM ufficiale, namespace attestato, database e filesystem effimeri, dati sintetici e rete esterna negata. Nessuna suite Laravel su host/Herd.

- R1: 252 test, tre errori e un fallimento di fixture/controllo statico preesistenti; conservata.
- R2: sei prove nuove falliscono prima dei correttivi, riproducendo le lacune; R3 estende le prove negative, conservata separata.
- R4: 36 test / 277 asserzioni, otto errori nelle vecchie fixture Boundary prive del totale persone; nuove prove e UX corrette passano, nessun PASS complessivo.
- R5: 262 test / 3.865 asserzioni, sei fallimenti: quattro richieste ON senza nuova impronta e due percorsi che incontrano il rendering legacy senza ricevuta. Nessun PASS complessivo; browser non eseguiti.
- R6: 263 test / 3.912 asserzioni PASS; quattro browser arrivano ai controlli funzionali ma falliscono sul percorso Mac delle catture inesistente nella VM. Percorso corretto nel solo test.
- R7: 266 test / 3.945 asserzioni PASS; sei browser PASS, tre NONPASS dopo una fixture che duplicava il codice ISO Francia. Riutilizzato il catalogo esistente per ISO, senza modificare l’applicazione.
- R8: 495 test / 6.475 asserzioni, sette fallimenti tutti nella vecchia suite `WebCheckinCameraIsolationTest`; browser non eseguiti. R9-BASE-CAMERE sulla baseline invariata: nove test e gli stessi sette fallimenti, prova distinta conservata.
- Conflitto risolto senza cambiare Web Check-in: il contratto pubblicato con `4d0bb218` il 19 ottobre riserva `room`, `beds` e `camere` all’operatore; le aspettative di sostituzione anonima dei vecchi test risalgono al contratto precedente. La suite storica resta intatta, non è dichiarata PASS né cancellata. La selezione finale usa `WebCheckinCircuitoTest` e nuove prove del mantenimento effettivo delle camere per parent/tenant sui link completo e breve. Non è attestato un PASS della discovery globale del repository.
- R10: 491 test / 6.476 asserzioni e nove browser PASS, comprendendo regressioni Arrivi, Web Check-in, ISTAT e Tassa; cinque prove dei guardrail, 23 rifiuti di override, 14 controlli SOAP in memoria, build e GEO PASS.
- R11: 271 test / 3.980 asserzioni e nove browser PASS sulla successiva distinzione UI fra OFF e prova interna. R13: 273 test / 3.993 asserzioni e nove browser PASS, dopo filtri non testuali e codici remoti neutri. Esecuzioni sovrapposte non sommate.
- R14: 273 test / 3.993 asserzioni e nove browser PASS, inclusi entrambi gli storici legacy senza ricevuta. R15: 274/4.002 e nove browser funzionali PASS, ma NONPASS visivo sul campo data mobile dello storico. **R16 finale: 274 test / 4002 asserzioni e nove browser PASS** sui quattro sorgenti applicativi finali, con feedback Test positivo distinto dall’invio ufficiale e nessuna mutazione dei relativi stati/counter. Dopo R10 nessuna modifica ai domini di Arrivi, Web Check-in, ISTAT o Tassa; delta circoscritto a Questura. [Ricevuta e impronte](evidenze/questura-integrale-2026-10-27/verifica.json). Sei catture di dettaglio originali sintetiche selezionate fra le 18 immagini private e ispezionate; nessuna credenziale reale o dato operativo nelle catture. Gli asset personali/loghi reali sono esclusi dal clone isolato: la verifica dei persistenti reali resta un gate C5/QA, non dedotta dalle immagini isolate.

Per i browser il solo router isolato può caricare, mediante un marcatore scritto dalla fixture nel filesystem effimero, il client SOAP double con trasporto abilitato nel processo di prova. Il front controller pubblico non carica il codice di collaudo; nessuna attivazione tramite URL, parametro o dato di produzione. Test dei guardrail OFF distinti e preservati.

La ricevuta giornaliera non prova l’accettazione di ogni singola riga. Sono verificati formato minimo PDF, dimensione e impronte, non una firma digitale dell’ente. Per esiti parziali, incerti o in corso, nessun retry automatico: riconciliare l’intera giornata e le identità prima di finalizzare. I dati PMS restano disponibili dopo la cancellazione delle copie Questura.

## Pubblicazione e controllo online

Nuovo PREPARE, nuovo S0 indipendente, recuperi completi, custodia cifrata Mac/VM/Extra con chiave separata, preflight, C5, verifica SHA, QA online, pulizia fixture e monitor post-rilascio ancora da eseguire. La produzione non è dichiarata aggiornata da questa task. Capacità VM: tre preflight e tre ritiri dei soli derivati ricostruibili dei vecchi recuperi ISTAT PASS, secondo il [piano circoscritto](QUESTURA-PIANO-CAPIENZA-2026-10-27.md). Nessuna nuova espansione del disco o rimozione di backup; ultimo campione durante il collaudo 6.142.074.880 byte liberi. Misure reali e gate di spazio da ripetere prima di PREPARE e RUN. Dodici prove pure del trasporto parallelo PASS, nessuna rete reale, sorgenti identici alla procedura già qualificata.

## Procedura di accettazione reale

1. Confermare struttura, Questura competente, abilitazione, profilo standard e utenza autorizzata. Inserire username/password/WSKEY esclusivamente nel form protetto. Verificare eventuale rigenerazione WSKEY e procedura territoriale alternativa.
2. Concordare periodo, ospiti effettivi, tipi/capo/componenti, date e orari, documenti e luogo di rilascio. Controllare i termini legali; non usare fixture verso l’ente.
3. Attivare il canale con procedura tecnica esplicita dopo i gate del progetto; le credenziali da sole non rimuovono il guardrail. Ricontrollare anteprima completa e impronta; una modifica impone una nuova anteprima e un nuovo Test.
4. Eseguire Test reale solo con autorizzazione, verificare tutte le righe, senza considerarlo accettazione. Prima di Send o importazione manuale ottenere conferma umana su struttura, periodo, contenuto e momento.
5. Send una sola volta o importazione del TXT standard secondo istruzioni dell’utenza. Conservare esito e identificativi effettivamente restituiti; non dedurre accettazione da HTTP 200, download o contatore locale.
6. In caso di rifiuto correggere e ricontrollare; timeout, risposta ambigua o parziale richiedono riconciliazione tramite portale/Questura, senza inviare nuovamente l’intero file alla cieca.
7. Acquisire la ricevuta giornaliera disponibile dal giorno successivo entro la finestra del servizio, riconciliare l’elenco e archiviare per cinque anni. Finalizzare le copie Questura e verificarne la minimizzazione. Conservare evidenza della corrispondenza giornata/ospiti, ricevuta e decisione dell’operatore per dichiarare chiusa l’accettazione reale.

Oltre al collaudo reale, restano attualmente pendenti Git/PR/integrazione, C5 e QA/monitor online di questo candidato. La qualifica isolata finale è PASS. Non usare la frase di prontezza in produzione finché tali verifiche non siano concluse.

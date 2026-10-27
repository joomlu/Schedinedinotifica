# Questura / Alloggiati Web — verifica integrale del 27 ottobre 2026

**Stato: PARZIALE. Verifiche tecniche, pubblicazione, QA online e monitor PASS; accettazione reale non eseguita. BASELINE IN AUDIT invariata.**

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
- R14: 273 test / 3.993 asserzioni e nove browser PASS, inclusi entrambi gli storici legacy senza ricevuta. R15: 274/4.002 e nove browser funzionali PASS, ma NONPASS visivo sul campo data mobile dello storico. **R16 finale: 274 test / 4002 asserzioni e nove browser PASS** sui quattro sorgenti applicativi finali, con feedback Test positivo distinto dall’invio ufficiale e nessuna mutazione dei relativi stati/counter. Dopo R10 nessuna modifica ai domini di Arrivi, Web Check-in, ISTAT o Tassa; delta circoscritto a Questura. [Ricevuta e impronte](evidenze/questura-integrale-2026-10-27/verifica.json). Sei catture di dettaglio originali sintetiche selezionate fra le 18 immagini private e ispezionate; nessuna credenziale reale o dato operativo nelle catture. Gli asset personali/loghi reali sono esclusi dal clone isolato: la conservazione dei persistenti non è dedotta dalle immagini; la successiva verifica C5/QA dei 92 persistenti è PASS.

Per i browser il solo router isolato può caricare, mediante un marcatore scritto dalla fixture nel filesystem effimero, il client SOAP double con trasporto abilitato nel processo di prova. Il front controller pubblico non carica il codice di collaudo; nessuna attivazione tramite URL, parametro o dato di produzione. Test dei guardrail OFF distinti e preservati.

La ricevuta giornaliera non prova l’accettazione di ogni singola riga. Sono verificati formato minimo PDF, dimensione e impronte, non una firma digitale dell’ente. Per esiti parziali, incerti o in corso, nessun retry automatico: riconciliare l’intera giornata e le identità prima di finalizzare. I dati PMS restano disponibili dopo la cancellazione delle copie Questura.

## Pubblicazione e controllo online

[PR36](https://github.com/joomlu/Schedinedinotifica/pull/36) integrata dopo revisione automatica completata senza rilievi sul candidato **`ac7dd4a9c88a6e979f073a60fe8b715523f9821e`**, effettivamente pubblicato. Merge `0d80cdd9f4404e22ae2c3a8dcc8f30a02df4b1bc`, albero identico; nessun controllo CI GitHub presente e nessuna scansione di sicurezza separata attestata. Consuntivo documentale separato dal candidato applicativo.

Primo PREPARE NONPASS per mutazione della base prima dell’offerta e senza barriera; 23.981 record confrontati successivamente senza differenze residue di contenuto/proprietà/permessi o nuovi record. La causa transitoria completa non è ricostruibile, il tentativo non è promosso. Secondo PREPARE PASS con recupero indipendente completo. Primo RUN fermato prima di execute/DDL per ricevuta non giunta entro il margine C5; traccia `backend.py:69`, non errore applicativo. I registri macOS mostrano sospensioni nella stessa finestra. Baseline `c226dd80` riaperta sana dal guardiano in 573,906 s, nessun ripristino DB; S0 storico custodito e non accettato dal server. Per la ripresa presidio temporaneo contro la sospensione automatica, nuovo piano/nonce/finestra e nuovo S0, nessuna guardia allentata.

**RUN2 PASS: S0 150,140 s, C5 260,968 s / 463 campioni, zero DDL**. PREPARE, S0 storico e S0 finale cifrati anche su Extra, integrità/HMAC/decifratura integrale verificati, chiave separata nella VM. Entrambi i guardiani propri conclusi, inattivi e disabilitati. SHA/Git/FPM/barriere conformi, database byte-identico al nuovo S0 prima della QA, 159 migrazioni / 66 tabelle, 318 sorgenti verificati; nessuna migrazione applicata.

QA online R1 NONPASS dell’adattatore: console anonima non vede i componenti per tenancy; transazione annullata, dati/schema delle 66 tabelle identici a S0 escludendo i soli AUTO_INCREMENT progrediti, nessun reset/restore. R2 NONPASS sul download della copia sintetica HTTP 404; file creati da root, proprietà dei soli file propri allineata al FPM nella R3. Pulizia R2 HMAC sulle 66 tabelle PASS e runtime proprio rimosso dopo confronto. Nessuna modifica applicativa durante tali ripetizioni.

**QA online R3 PASS a 1440/768/390 px**: form operativo con password/WSKEY vuoti; anteprima UTF-8 di 2 righe di 168 caratteri e 347 byte, byte/hash identici all’archivio; 32 export e 32 trasmissioni sintetici per verificare pagine indipendenti e tab persistente, esito non verificato leggibile, download storico no-store; tenant estraneo 403 / storico 404, reception in lettura. POST download/verify 409 per canale OFF; nessuna richiesta Send consentita dall’adattatore e nessun invio reale. Salvataggio credenziali e trasporto ON solo nella qualifica isolata, senza utenze reali. Nove catture sintetiche ispezionate privatamente: verifica circoscritta ai moduli, non certificazione del layout condiviso; intestazione fissa nelle catture lunghe dipende dallo scorrimento.

Fixture R3 rimosse: **HMAC identici sulle 66 tabelle**, una sessione sintetica residua del tentativo precedente eliminata con controllo dell’ID proprio; logout dei soli account sintetici, nessun restore DB. **92 persistenti** env/upload/loghi e metadati conformi a S0; solo mtime delle directory escluso secondo protocollo. Runtime QA R3 rimosso dopo confronto di 14 file. **Monitor finali dopo tutte le pulizie PASS: server 920,272 s / 31 campioni / 279 sonde; Mac 921,248 s / 31 campioni / 93 richieste**, DNS/TLS ordinari e nessuna forzatura. Zero nuovi errori Laravel/FPM da entrambe le finestre RUN. Dati e schema finali identici al nuovo S0 escludendo i soli contatori AUTO_INCREMENT progrediti; confronto finale ripetuto su dump fresco, nessun reset o restore. Il primo normalizzatore non gestiva due clausole AUTO_INCREMENT assenti nel dump iniziale perché le tabelle erano vuote: diagnostico conservato, confronto corretto esclude esclusivamente tali clausole. [Monitor e conteggi](evidenze/questura-integrale-2026-10-27/monitor.json).

Quattro ritiri circoscritti di soli derivati ricostruibili, compreso il PREPARE corrente dopo recupero/custodia completi, documentati nel [piano capacità](QUESTURA-PIANO-CAPIENZA-2026-10-27.md); originali/backup/SQL/chiavi preservati, nessuna espansione. HEAD quotidiano `945ef553`, DB logico, 84 file protetti e SELinux invariati. Nel checkout originale sincronizzati solo i quattro sorgenti applicativi dopo verifica delle copie preesistenti; 2.750 file/link e precedente corpo del Maestro identici, HEAD originario `a6d40647` preservato, nessun allineamento Git generale dichiarato e nessun test su Herd. [Pubblicazione](evidenze/questura-integrale-2026-10-27/pubblicazione.json), [QA online](evidenze/questura-integrale-2026-10-27/online.json), [preservazione](evidenze/questura-integrale-2026-10-27/preservazione.json).

## Matrice conclusiva delle verifiche tecniche

| Area | Stato tecnico | Evidenza e limite |
|---|---|---|
| Form e configurazione | PASS | Salvataggio e mancata ripopolazione dei segreti nella VM; form operativo online leggibile nelle tre larghezze. Nessuna credenziale reale usata. |
| Dati e flussi a monte | PASS | Tipi, gruppi/famiglie, codici, date e coerenza persone; regressioni R10 Arrivi/Web Check-in/ISTAT/Tassa e contratto corrente delle camere. |
| TXT standard | PASS | UTF-8, 168 caratteri, CRLF intermedi, massimo 1.000 righe, capo prima dei componenti; non attestato profilo Appartamenti 174 o accettazione dell’alfabeto da parte dell’ente. |
| Anteprima e download | PASS | Impronta obbligatoria e byte identici nelle prove isolate; online archivio identico all’anteprima, no-store. Nuove generazioni bloccate a canale OFF. |
| Connessione | PASS tecnico | WSDL/fonti ufficiali accessibili, TLS, autenticazione/token e timeout qualificati con doubles. Nessuna connessione autenticata reale attestata. |
| Simulazioni e retry | PASS | Test/Send con doubles, rifiuto, errore, indisponibilità, timeout, parziale/incerto e riserve idempotenti; nessun retry cieco o falsa accettazione. |
| Storico e ricevute | PASS tecnico | Pagine indipendenti e casi legacy; ricevuta giornaliera distinta dall’accettazione per riga. Parser PDF minimo, non verifica della firma dell’ente. |
| Sicurezza e conservazione | PASS | Isolamento, credenziali cifrate/non esposte, guardrail, HMAC su 66 tabelle e 92 persistenti dopo pulizia. Ricevute cinque anni, copie Questura minimizzate senza cancellazione generalizzata PMS. |
| Pubblicazione | PASS | SHA applicativo verificato, S0/recuperi/custodie completi, zero DDL. Consuntivo documentale distinto. |
| Accettazione reale | NON ESEGUITA | Abilitazione/profilo, credenziali e autorizzazione specifica su struttura/periodo/contenuto/momento richieste prima della prova reale. |

## Procedura di accettazione reale

1. Confermare struttura, Questura competente, abilitazione, profilo standard e utenza autorizzata. Inserire username/password/WSKEY esclusivamente nel form protetto. Verificare eventuale rigenerazione WSKEY e procedura territoriale alternativa.
2. Concordare periodo, ospiti effettivi, tipi/capo/componenti, date e orari, documenti e luogo di rilascio. Controllare i termini legali; non usare fixture verso l’ente.
3. Attivare il canale con procedura tecnica esplicita dopo i gate del progetto; le credenziali da sole non rimuovono il guardrail. Ricontrollare anteprima completa e impronta; una modifica impone una nuova anteprima e un nuovo Test.
4. Eseguire Test reale solo con autorizzazione, verificare tutte le righe, senza considerarlo accettazione. Prima di Send o importazione manuale ottenere conferma umana su struttura, periodo, contenuto e momento.
5. Send una sola volta o importazione del TXT standard secondo istruzioni dell’utenza. Conservare esito e identificativi effettivamente restituiti; non dedurre accettazione da HTTP 200, download o contatore locale.
6. In caso di rifiuto correggere e ricontrollare; timeout, risposta ambigua o parziale richiedono riconciliazione tramite portale/Questura, senza inviare nuovamente l’intero file alla cieca.
7. Acquisire la ricevuta giornaliera disponibile dal giorno successivo entro la finestra del servizio, riconciliare l’elenco e archiviare per cinque anni. Finalizzare le copie Questura e verificarne la minimizzazione. Conservare evidenza della corrispondenza giornata/ospiti, ricevuta e decisione dell’operatore per dichiarare chiusa l’accettazione reale.

Le verifiche tecniche del profilo standard, l’integrazione applicativa, C5, QA e monitor online sono PASS. Resta il collaudo reale di accettazione, con abilitazione/profilo, credenziali nel form protetto e conferma specifica prima di Test/Send/importazione. Nessun invio reale o accettazione esterna attestati. Il consuntivo documentale è separato dallo SHA applicativo effettivamente pubblicato.

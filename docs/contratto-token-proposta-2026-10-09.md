# Contratto funzionale Web Check-in — approvazione completa e implementazione locale

## Registro vigente — D1–D2, R1–R2 e D3–D5 approvate, 9 ottobre 2026

**Tutte le decisioni funzionali richieste sono APPROVATE dall’utente e IMPLEMENTATE nello scope locale. BASELINE IN AUDIT — NON VALIDATA.** Questo registro sostituisce i registri/proposte precedenti, conservati integralmente sotto. Nessuna autorizzazione a operazioni sul DB operativo, configurazioni o schema operativo, commit, push, deploy o trasmissioni reali.

| Decisione | Contratto approvato |
|---|---|
| D1 — Emissione e compilazione | Emissione e preparazione dell’invio in qualsiasi momento. L’ospite può completare, salvare e correggere finché il link è valido e la richiesta non è convertita. Nessun cap30giorni, nessuna estensione da visita/salvataggio. Un’emissione con arrivo ormai passato non sposta la scadenza: il nuovo link resta fuori termine. |
| D1 — Termine | Fine del giorno successivo all’arrivo previsto, **Europe/Rome**. Equivalente esclusivo: inizio del giorno arrivo+2, calcolato per giorni di calendario; non48ore fisse. Arrivo9ottobre→termine11ottobre00:00locale. |
| D2 — Conversione | Dati conservati per l’operatore, modifiche anonime bloccate dopo Arrivo/Schedina. Show, invito, completed e POST delle rappresentazioni valide restituiscono solo la stringa letterale **«Check-in recibido»**, senza dati personali, riepilogo, campi nascosti, collegamenti di modifica o download personali. Correzioni successive dal gestionale. |
| R1 — Fonte | `web_checkin_richieste.arrivo` è la data prevista della richiesta gestionale. I dati soggiorno compilati dall’ospite restano nella schedina e non riscrivono le date previste della richiesta. |
| R1 — Riprogrammazione | Cambio gestionale della data mentre il link è attivo ricalcola il termine. Se già scaduto, revocato o con date non valide, l’aggiornamento non abilita il vecchio link: occorre emissione esplicita autorizzata. |
| R2 — Eventi e ruoli | Solo identità autorizzate alla struttura secondo la membership operativa esistente possono emettere, revocare o rigenerare. Revoca manuale, rigenerazione, cancellazione e cambio gestionale di destinatario invalidano tutte le rappresentazioni precedenti. Comprende svuotamento dei contatti, non soltanto un valore nuovo. Nessun SID fornito dall’ospite concede autorizzazione gestionale. |
| R2 — Cestino | Ripristino conserva i dati ma lascia il link revocato. Nuova emissione soltanto da azione gestionale autorizzata; nessuna emissione implicita aprendo elenco/form. Anche cancellazione/ripristino del parent non riattiva il token. |
| D3 — Credenziali nuove | Completo casuale64caratteri e invito breve casuale **indipendente32caratteri**, senza derivazione dal completo. Nuove emissioni dopo Cestino uniformi64/32. Entrambi risolvono la stessa richiesta e condividono validità, revoca, conversione e quote. |
| D4 — Ingresso | **300richieste/60secondi perIP**, prima del lookup, incluse credenziali inesistenti. Quota comune a ospiti e strutture che condividono l’IP. |
| D4 — Richiesta risolta | **60letture/60secondi** e **20POST/600secondi** per struttura+richiesta+IP. Alias completi/brevi e completed condividono i contatori; HEAD consuma letture, POST con CSRF errato consuma la quota POST prima del rifiuto. Superamento429 con Retry-After e nessuna modifica applicativa. |
| D5 — Legacy | Completi64/80/altri formati esistenti e vecchi inviti codice+prefisso8 restano compatibili se ancora validi secondoD1. Nessun taglio7giorni. Rigenerazione disabilita anche il prefisso precedente, persino se il nuovo completo avesse gli stessi primi8caratteri. Nessuna grazia per revoca/cancellazione/scadenza. |
| D5 — Date | Assenza, data non di calendario o partenza precedente all’arrivo impediscono l’accesso valido/emissione. Occorre correzione gestionale autorizzata, senza inventare date; la successiva emissione resta esplicita quando la richiesta era già non valida. |

**Decisioni funzionali ancora da approvare: nessuna per questo contratto.** Non vengono modificate le politiche degli altri token (recupero account, sessioni, batch Componenti, anteprima Tassa, Sanctum, provider Questura): sono fuori dal contratto approvato Web Check-in.

### Implementazione e delimitazione tecnica

`WebCheckinLink` centralizza calendario locale, validità, emissione e risoluzione esatta delle credenziali. Quattro metadati nuovi: invito breve, scadenza, revoca ed emissione; migration aggiuntiva nullable, preparata ed eseguita **solo nei database sintetici effimeri**. Nessuna riscrittura dei dati legacy durante la migration: per essi il termine deriva dalle date registrate nella richiesta. Nessuna migration o ricognizione di dati operativi eseguita.

GET che aggiorna lo stato, salvataggio ospite, aggiornamento gestionale, revoca, rigenerazione e conversione vengono serializzati con lock di struttura/richiesta nelle transazioni pertinenti. Un salvataggio già iniziato rivalida token, stato e scadenza dopo il lock. La cancellazione della richiesta è transazionale; l’archiviazione del parent revoca i suoi inviti. Le rappresentazioni pubbliche ricevono `Cache-Control: no-store`; i rifiuti non espongono dati personali.

Il middleware pubblico esegue la quotaIP prima del resolver e la quota specifica prima di CSRF/controller. Finestre fisse dal primo tentativo: nuova finestra quando `now >= fine`, Retry-After decrescente. Contatori su **store file locale esistente**, protetti da lock fra processi; nessuna configurazione operativa modificata. Il supporto dei test assegna cache/storage esclusivi a ogni caso, conservando la condivisione tra richieste/processi dello stesso caso. Non è una deroga alle quote. Il proxy corretto nelle fasi precedenti resta esclusivamente **strumento di audit**, invariato.

La registrazione storica dell’arrivo prevista prima di questa modifica poteva essere riscritta dal vecchio salvataggio ospite: la provenienza dei valori nei dati reali non è attestata. Prima dell’attivazione operativa occorre verificarla/correggerla dal gestionale; nessuna ricostruzione automatica da data di emissione, partenza o informazioni inventate. La compatibilità locale è verificata su date sintetiche registrate dall’operatore. Eventuali revoche storiche non registrate non possono essere ricostruite senza evidenze operative; token sostituiti/non presenti e revoche esplicite sono rifiutati nelle prove locali.

### Evidenze e verdetto

I risultati definitivi e la matrice requisito/evidenza/esito/residuo sono nell’[appendice del rapporto](baseline-applicativa-audit-2026-10-08.md#implementazione-e-accettazione-locale-del-contratto-token--9-ottobre-2026) e nell’[evidenza strutturata](evidenze-contratto-token-2026-10-09.json). I verdetti storici non attestano questa implementazione; fallimenti intermedi e copie originali sono conservati separatamente. I gate produttivi rimangono distinti: migrazione/rollback/backup autorizzati, MariaDB/Linux e CLI/FPM/SPanel, dati reali, attendibilitàIP dietro proxy, persistenza/permessi/condivisione delle quote sui nodi effettivi, release/cache/storage/queue/cron/SSL e accettazioni esterne. Nessuna validazione produttiva.

## Storico integrale dei registri e delle proposte precedenti

Le approvazioni parziali, le decisioni allora pendenti e le proposte numeriche che seguono sono storico: prevale il contratto completo sopra. Tutto il documento precedente è conservato byte per byte.

---

# Contratto dei token — decisioni approvate e pendenti

## Registro vigente — approvazione D1/D2, 9 ottobre 2026

**D1 e D2 approvate esclusivamente nei termini indicati sotto. NON IMPLEMENTATE. D3–D5 e le ulteriori regole di revoca NON APPROVATE. BASELINE IN AUDIT — NON VALIDATA.** Questo registro prevale sulle proposte storiche conservate integralmente dopo la sezione corrente. Accettazione locale del perimetro già verificato preservata; nessuna validazione produttiva.

Obiettivo di questa fase: recepire le decisioni dell'utente e delimitare quelle ancora necessarie. File coinvolti: questo contratto, rapporto di audit e Maestro; rischio BASSO, sola documentazione. Nessuna implementazione, modifica di test/configurazione/schema, esecuzione Laravel o uso di dati/trasporti applicativi. Verifiche previste: integrità dei file preesistenti e delle sezioni storiche, coerenza delle tre regole e diffcheck.

### Regole funzionali ora approvate

1. **Emissione senza limite di anticipo.** Il link può essere emesso e inviato in qualsiasi momento. L'ospite può compilare, salvare e correggere il Web Check-in mentre il link è valido e la richiesta non è stata convertita in Arrivo/Schedina. Non è approvato alcun limite di30giorni dalla generazione.
2. **Conversione e riservatezza.** Alla conversione in Arrivo/Schedina i dati vengono conservati e le modifiche dal link vengono bloccate. Il link mostra esclusivamente il messaggio letterale richiesto dall'utente **«Check-in recibido»**, senza dati personali. Non mostra ricevuta nominativa, riepilogo degli ospiti/componenti, dettagli della prenotazione, campi di modifica o download contenenti tali dati. Le correzioni successive spettano all'operatore dal gestionale. La stessa regola deve coprire completo, breve e percorsi di completamento, senza esposizione tramite HTML, campi nascosti o payload della pagina.
3. **Scadenza basata sull'arrivo previsto.** Il link scade alla fine del giorno successivo alla data di arrivo prevista, nel fuso Europe/Rome. Nessun limite di30giorni dalla generazione; nessuna scadenza basata sulla partenza. Visite e salvataggi non prolungano la validità.

Espressione tecnica equivalente della scadenza: inizio del giorno **arrivo previsto+2giorni di calendario** in Europe/Rome, esclusivo. Esempio: arrivo9ottobre2026→valido durante il10ottobre, scaduto dalle00:00dell'11ottobreEurope/Rome. Questa traduzione evita un limite artificiale ai secondi e deve rispettare cambio ora legale, mese e anno; non sostituisce la data di arrivo con quella di emissione o dell'ultimo accesso.

Queste sono **decisioni di contratto**, non nuovi risultati di verifica dell'applicazione: il codice attuale ancora non implementa questa scadenza e la pagina corrente di completamento espone una ricevuta. Le prove precedenti restano valide nel loro perimetro originale, non certificano automaticamente il nuovo messaggio senza dati personali o la nuova scadenza.

### Parti delle proposte precedenti superate o non approvate

| Elemento storico | Stato vigente |
|---|---|
| Cap30giorni dall'emissione | **Superato ed escluso** da D1 approvata. |
| Termine basato sulla partenza | **Superato**, sostituito da arrivo previsto+giorno successivo. |
| Ricevuta/conferma con dati dopo conversione | **Superata**, soltanto messaggio approvato senza dati personali; dati conservati nel gestionale. |
| Revoca manuale, rigenerazione, cancellazioni, cambi destinatario/parent/struttura, disattivazione emittente, ruoli e riattivazione dal Cestino | **Non approvati** come nuove regole funzionali. Gli effetti tecnici già implementati restano fatti documentati, senza diventare il requisito per default. |
| Nuovo invito indipendente32caratteri/completo64 uniforme | **D3 non approvata.** |
| Soglie300/60/20, finestre, chiavi,429/Retry-After e compromesso IP condiviso | **D4 non approvata.** Nessun valore attivato. |
| Periodo transitorio7giorni e nuove procedure di rinnovo | **D5 non approvata.** La proposta storica deve essere riesaminata alla luce dell'arrivo previsto; non può introdurre implicitamente un nuovo limite dall'emissione. |
| Procedura che riattiva automaticamente il link convertito | Nessuna riapertura approvata; le correzioni successive alla conversione sono gestionali, come stabilito dall'utente. |

### Sole decisioni ancora necessarie prima dell'implementazione

| ID pendente | Decisione funzionale da completare | Motivo concreto |
|---|---|---|
| **R1 — Data di arrivo autorevole e riprogrammazione** | Quale data governa la scadenza: arrivo previsto della prenotazione gestita dall'operatore oppure campo soggiorno modificabile dall'ospite? Se l'operatore sposta l'arrivo, il termine viene ricalcolato e un link già scaduto può tornare valido oppure occorre emissione esplicita? | Oggi `publicStore` copia `schedina.arrive` in `richiesta.arrivo`. Un salvataggio che sposta l'arrivo in avanti non può diventare automaticamente un prolungamento del link, vietato dalla regola3. La fonte autorevole e gli effetti del cambio gestionale sono ancora da decidere; non vengono scelti implicitamente. |
| **R2 — Revoca, rigenerazione e Cestino** | Quali eventi revocano i link (azione manuale, rigenerazione, cancellazione/annullamento, cambio destinatario/contesto, disattivazione)? Chi può revocare/rigenerare nel tenant? Il ripristino richiede nuova attivazione esplicita? | D2 approvata stabilisce il blocco dopo conversione, ma **non** approva le ulteriori regole di revoca o ripristino. Il ripristino di una richiesta convertita non deve essere interpretato come autorizzazione alle correzioni dal link. |
| **D3 — Formato e accesso breve** | Mantenere il formato attuale oppure adottare un invito indipendente; formato comune a nuove richieste e ripristini e gestione della discrepanza64/80. | La proposta32caratteri resta non approvata. Il breve rivela oggi il completo e può sopravvivere a una rotazione con prefisso invariato; il breve80 resta una discrepanza statica documentata. |
| **D4 — Rate limit** | Soglie, finestre e chiavi dei contatori per lettura/salvataggio/risoluzione invalida; contatori comuni agli alias e trattamento degli IP condivisi tra strutture. |300/minuto perIP,60letture/minuto e20POST/10minuti sono soltanto la proposta storica, non valori approvati. Serve scegliere il compromesso di accessibilità/antiabuso/isolamento. |
| **D5 — Link già distribuiti** | Come applicare la nuova scadenza ai link esistenti e legacy, compresi arrivi mancanti/incoerenti; compatibilità/rigenerazione in caso di cambio formato, eventuale transizione esplicita. | Nessun periodo7giorni approvato. Occorre distinguere vecchi link ancora entro il nuovo termine arrivo da link già fuori termine; una transizione non deve sovrascrivere di nascosto D1/D2. |

Non sono richieste nuove decisioni sul recupero password, batch import, token fiscale o provider Questura: restano distinti dal pendente pubblico. Implementazione e prove sono rinviate fino al completamento del contratto; questa approvazione non autorizza invii reali o operazioni operative.

### Prove future aggiornate ai requisiti approvati

Emissione con grande anticipo rispetto all'arrivo, anche oltre30giorni; salvataggi/riaperture senza prolungamento; prima/esattamente/dopo mezzanotte del termine approvato, DST/bisestile/cambio anno; conversione in Arrivo e in Schedina conserva principale/componenti/tenant e blocca POST/replay; tutti gli alias mostrano il solo messaggio approvato senza dati personali in pagina/payload o ricevute; correzioni gestionali autorizzate preservate e accessi di tenant estraneo rifiutati. Form aperto prima della scadenza/conversione non deve aggirare il controllo al salvataggio. Le prove su arrivo riprogrammato, revoca/Cestino, nuovo formato, rate limit e legacy dipendono dalle decisioni residue sopra.

Nessuna suite ripetuta: modifica esclusivamente documentale. Diagnostici originali e prove correnti dell'assenza di TTL restano evidenze storiche; future nuove aspettative dovranno essere identificate e motivate dal contratto approvato, senza occultare risultati negativi. Accettazione locale già delimitata e gate produttivi rimangono separati e invariati. **Nessuna implementazione eseguita.**

---

## Storico integrale — proposta precedente, superata dove indicato dal registro vigente

Il contenuto che segue è conservato per tracciabilità. Le frasi “da approvare D1–D5”, i vecchi30giorni/partenza e la ricevuta dopo conversione descrivono la proposta anteriore: **non prevalgono sul registro vigente sopra** e non attestano approvazione delle regole aggiuntive di revoca.

# Contratto dei token — analisi e proposta da approvare

Data: 9 ottobre 2026. **PROPOSTA NON APPROVATA, NON IMPLEMENTATA. BASELINE IN AUDIT — NON VALIDATA.** Accettazione locale positiva del perimetro già verificato preservata; nessuna validazione produttiva.

Obiettivo: risolvere il solo pendente funzionale sui token senza assumere che l'implementazione esistente sia il requisito. Rischio della presente attività: BASSO, analisi e documentazione; rischio della futura modifica del contratto pubblico: ALTO, accesso a dati degli ospiti e compatibilità dei link distribuiti. File modificati in questa fase: questo documento e appendici di rapporto/Maestro. Nessun codice, test, configurazione, schema o dato modificato. Letture statiche del repository e del framework installato, nessun bootstrap Laravel, database, server o servizio applicativo esterno utilizzato; nessuna nuova suite necessaria per un intervento esclusivamente documentale.

## 1. Regole trovate prima della proposta

Il Maestro P1.4 richiede token/scadenza/anonimato/parent tenant/link brevi/riuso/invalidazione; le appendici A02/A06 e le successive valutazioni richiedono isolamento del parent, rifiuto degli accessi ambigui, protezione delle richieste convertite e formalizzazione della politica pubblica. **Non contengono un valore approvato di TTL, una scadenza legata al soggiorno, una procedura di rinnovo o soglie del rate limit pubblico.** Le date soggiorno sono state espressamente escluse come scadenza *implicita*, non approvate come esenzione perpetua dalla scadenza.

Le correzioni A02/A06/A09–A11 e la conversione finale fissano confini tenant e correttezza dei dati. Il link identifica una richiesta con struttura e parent coerenti; una sessione di altra struttura non deve cambiare quel contesto. La conversione operativa rende non modificabile il Web Check-in. Questi sono requisiti già stabiliti, da preservare; non chiedono una nuova approvazione.

Le prove `WebCheckinShortLinkTest::test_date_soggiorno_non_costituiscono_scadenza_implicita_del_token` e `LocalPublicTokenContractAudit` fotografano il comportamento corrente. Non sono una decisione di prodotto favorevole a token eterni. Analogamente i numeri della proposta sotto non derivano dai conteggi della suite o da uno standard che prescriva quei valori.

## 2. Token coinvolti nel pendente Web Check-in

Sono accessi alla **medesima richiesta**, non account separati né credenziali indipendenti del singolo componente di un gruppo. Chi possiede il link può accedere al gruppo autorizzato dal token; non è attestata l'identità fisica di chi lo usa.

| Tipo / rappresentazione | Comportamento corrente: durata e scadenza | Revoca e riuso correnti | Rate limit corrente: soglia, finestra, chiave |
|---|---|---|---|
| Completo ordinario, `Str::random(64)`, `/checkin/{token}`; accettato anche in `/w/{token}` | Nessun TTL/issued_at/expires_at/revoked_at nel modello. Non scade per arrivo/partenza né per inattività. Token creato90giorni prima con soggiorno passato ancora200 nella prova storica1/4PASS. | Cancellazione della richiesta e sostituzione del token invalidano il precedente accesso completo. Salvataggi ripetuti ammessi prima della conversione; convertito non consente modifiche, ma consente lettura della conferma/ricevuta. Non è monouso. Nessuna azione UI dedicata di revoca/rinnovo individuata. | Nessun limiter dedicato sui sei endpoint completi/brevi GET/POST/completato; soglia, finestra e chiave **assenti**. Il limiter API non si applica a queste route web. |
| Invito breve generato: `/w/{codice}-{primi8caratteri}` | Stessa assenza di scadenza della richiesta. Non ha una durata indipendente. Il GET invito restituisce il link al token completo. | Unico candidato richiesto, codice/prefisso esatti e prefisso case-sensitive. Rotazione che cambia codice/prefisso e cancellazione revocano il breve. **Mantenere codice e primi8caratteri conserva il vecchio breve**, anche cambiando il resto del token: il resolver associa il link al nuovo completo. POST/completato brevi delegano al completo. | Assenti: nessun conteggio per codice, prefisso, richiesta, struttura o IP. CSRF presente sui POST, ma non è un rate limit. |
| Completo legacy di formato/lunghezza diversi, conservato per compatibilità | Stessa assenza di TTL. Lookup SQL di uguaglianza del token completo precede il riconoscimento del formato breve. Non dichiarata una maggiore entropia o qualità dei token legacy. | Compatibilità di stringhe complete legacy verificata. Sostituzione/cancellazione tecnicamente revocano il completo; validità del formato breve non automaticamente garantita. | Assenti, come sopra. |
| Completo rigenerato dal Cestino, `Str::random(80)` | Nuova credenziale dopo ripristino; created_at rigenerato ma non usato come TTL. Nessuna durata formalizzata. | Vecchio token escluso dallo snapshot e sostituito. Stato e dati di conversione possono essere conservati dal ripristino. | Assenti, come sopra. **Discrepanza statica:** la generazione UI usa il prefisso di questo token80, ma il resolver breve richiede un token sottostante64; il completo resta risolvibile. Non eseguita una nuova riproduzione browser del ripristino→invito. |

Fonti: [controller Web](../app/Http/Controllers/WebCheckinController.php), [modello](../app/Models/WebCheckinRichiesta.php), [route web](../routes/web.php), [Cestino](../app/Services/CestinoService.php), [link brevi e compatibilità](../tests/Feature/WebCheckinShortLinkTest.php), [contratto osservato](../tests/Feature/LocalPublicTokenContractAudit.php), [conversione](../tests/Feature/LocalWebConversionAudit.php), [evidenze locali già acquisite](evidenze-accettazione-locale-2026-10-09.json).

Ulteriore limite statico: `publicShow`/`publicStore` applicano il blocco di conversione, mentre `publicCompleted` usa `renderCompletedView`, che passa `isLockedAfterConversion=false` e un editUrl. Il backend del salvataggio resta bloccato per convertito. La proposta deve uniformare conferma e link di modifica su tutti gli alias; non si dichiara qui una nuova scrittura anonima riuscita né si modifica il verdetto delle prove già eseguite.

## 3. Altri token: distinzione dal pendente pubblico

| Tipo | Regola documentata o codificata trovata; comportamento implementato | Conteggio tentativi attuale e limite dell'evidenza |
|---|---|---|
| Recupero password dell'account gestionale | `config/auth.php`: validità60minuti dalla creazione. Broker salva hash, sostituisce il precedente token della stessa email e cancella al reset riuscito. A14 impone account attivo anche al consumo; scaduto/invalido/monouso e password invariata verificati. Il blocco sull'account inattivo non equivale alla cancellazione fisica del token o a revoca permanente se l'account viene riattivato. | `throttle=60` è **60secondi tra emissioni** per email (chiave globale dell'account, senza namespace di struttura), non60tentativi/minuto. Il broker guarda l'ultima emissione persistita, non usa un contatore di reset falliti. Nessun throttle HTTP dedicato a POST richiesta/reset individuato. Regole esistenti non da sostituire con il TTL ospite; ulteriori politiche antiabuso dell'account sono esterne al pendente P1.4, non implicitamente approvate. |
| Batch import Componenti, UUID autenticato | Maestro §ComponentiImport e costante `BATCH_TTL_MINUTES=30`:30minuti dalla preview; sessione e user/struttura/schedina/context obbligatori. Consumo e lock singolo nodo, marker consumato86400secondi. Perdita della sessione rende il batch indisponibile. Non è una credenziale anonima d'invito. | Nessun limiter HTTP dedicato. Lock/consumo non contano tentativi. Chiavi sessione/cache per token, verifiche di identità nel batch; loader scade quando expires_at < timestamp corrente. Nessuna nuova scelta di durata richiesta; garanzia multi-nodo non dedotta. |
| Anteprima di riallineamento Tassa | Rapporto fiscale già documenta15minuti, utente/struttura/anno e impronta dei dati sorgente. Token cifrato/autenticato; scadenza strettamente futura e impronta ricontrollata sotto lock/transazione. Anteprima obsoleta/alterata o contesto estraneo409. | Nessun limiter dedicato individuato; impronta e autorizzazione sono controlli di validità, non soglie di tentativi. Non modificare la politica fiscale nell'ambito del token ospite. |
| CSRF e sessione web | CSRF legato alla sessione, confrontato dal middleware, rigenerato con logout; non ha TTL proprio. `config/session.php` ha fallback120minuti di inattività, valore effettivo può dipendere dall'ambiente non letto. | Nessun contatore proprio del token. Un419 rifiuta la richiesta, non prova l'esistenza di un limite temporale. |
| Ricordami dell'account gestionale | Token persistito e cookie; il framework installato ha rememberDuration576000minuti (400giorni), senza override app individuato. A12/A13 revocano accesso web per inattività account/password superata; questo non è TTL del link ospite. | Login eredita5fallimenti/1minuto, chiave login normalizzato+IP, azzerata al successo. Non è un conteggio dell'uso dei token Web o dei POST reset. Durata del cookie è comportamento framework, non nuova decisione di prodotto. |
| Sanctum API | User usa HasApiTokens; `/api/user` richiede auth:sanctum, config expiration=null; il framework verifica anche eventuale expires_at individuale. Nessuna route di emissione o revoca applicativa individuata; esistenza/uso di token reali non indagati. | API60richieste/minuto, chiave definita userId se risolto, altrimenti IP; scope API, non Web Check-in. Nessuna politica di emissione API generale proposta in questa fase. |
| Token Questura del provider | GenerateToken restituisce expires; client rifiuta assenza/formato invalido/scadenza trascorsa. Durata e revoca dipendono dal provider, non dal contratto ospite. | Nessun limiter applicativo dedicato dimostrato; limiti reali del provider restano gate esterno. Solo doppi in memoria nelle evidenze storiche, nessun SOAP reale ora. |

Auth::routes() non abilita verify; User non implementa MustVerifyEmail. Il controller di verifica email contiene signed/throttle6,1, ma **non è un flusso attivo di emissione da aggiungere al contratto**. I batch Clienti usano ID e autorizzazione tenant, non un token bearer pubblico. Chiavi dei servizi, APP_KEY, password e credenziali degli enti non sono token ospite e non vengono inventariate leggendo segreti.

Fonti: configurazioni [auth](../config/auth.php), [session](../config/session.php), [Sanctum](../config/sanctum.php), [limiter API](../app/Providers/RouteServiceProvider.php), controller Auth/ComponentiImport/Tassa, framework locale `DatabaseTokenRepository`, `ThrottlesLogins`, `SessionGuard`, Sanctum `Guard`; rapporto fiscale §riallineamento. Il comportamento dei valori ambientali o del cache store operativo non è stato interrogato.

## 4. Decisioni funzionali mancanti: proposta concreta

**D1–D5 richiedono approvazione dell'utente. Non sono requisiti in vigore.** I parametri numerici sono candidati motivati da uso e rischio, non misurati sulla produzione né prescritti da OWASP. Dopo approvazione richiederanno implementazione e prove; l'approvazione del contratto non costituisce approvazione di deploy, migration operative o invii.

### D1 — Durata del completo, del breve e dei token ripristinati

Proposta: stessa scadenza per tutte le rappresentazioni, **il primo tra30giorni dalla nuova emissione e fine del giorno successivo alla partenza prevista**, calcolata nel fuso applicativo già configurato, Europe/Rome (`config/app.php:74`); non introdotto implicitamente un fuso diverso per struttura. Scadenza persistita in UTC, accesso vietato quando now >= expires_at. Nessun rinnovo per visita, salvataggio o richiesta completata. Una variazione del soggiorno può accorciare la scadenza; per prolungarla occorre rinnovo esplicito dell'operatore con nuove credenziali. La data ospite non può auto-prolungare il diritto d'accesso.

Motivo: consente compilazione anticipata e più sessioni, ma limita l'esposizione di documenti e dati del gruppo. Il giorno aggiuntivo evita tagli allo scoccare della partenza. **Costo da approvare:** un invito emesso mesi prima richiede rigenerazione dopo30giorni; un lungo soggiorno non prolunga da solo il link. Non assumere che il cliente invii gli inviti soltanto negli ultimi30giorni.

Esempi: emesso1ottobre alle10:00, partenza20ottobre→scadenza21ottobre23:59:59locale; partenza20dicembre→scadenza31ottobre2026 alle09:00locale (30×24ore, con cambio ora legale nel frattempo). La regola dei30giorni è un intervallo di30×24ore dall'emissione; il termine soggiorno è calendario locale, inclusi cambi ora legale.

### D2 — Riuso, revoca, conversione e ripristino

Proposta: link riutilizzabile per compilare/correggere principale e gruppo fino a conversione o scadenza, senza OTP aggiuntivo in questo contratto. Conversione→sola ricevuta/conferma già prevista, **nessun link o azione di modifica in nessun alias**, fino alla stessa scadenza; non creare una durata infinita della ricevuta. Non riaprire automaticamente quando il circuito viene cambiato nuovamente.

Revoca immediata comune a completo e breve per revoca manuale, rigenerazione, cancellazione della richiesta, cancellazione del parent e annullamento della prenotazione quando rappresentato da un'azione autorizzata; niente stato di annullamento inventato implicitamente. Cambio di struttura/parent della richiesta non autorizza trasferimento del vecchio link: revoca e nuova richiesta coerente, se il flusso sarà previsto. Cambio gestionale del destinatario email/WhatsApp o referente deve invalidare i vecchi accessi e richiedere nuova emissione, senza cancellare dati del gruppo. Modifiche di date/quantità non rigenerano da sole, salvo accorciamento della scadenza. Disattivazione dell'account che aveva emesso il link non revoca automaticamente un invito della struttura: l'operatore è diverso dal titolare della prenotazione.

Le azioni di revoca/rinnovo spettano agli operatori già autorizzati a gestire **quella** richiesta, senza ampliare i ruoli o la catena tenant. Nessun nuovo endpoint pubblico può rinnovare o revocare link. Il ripristino dal Cestino conserva dati/stato di conversione ma lascia l'invito **non attivo**, finché un operatore ne richiede l'emissione; vecchi link sempre invalidi. Motivo: recuperare dati non equivale a riaprire accesso a chi conservava un vecchio invito.

Link scaduti/revocati/cancellati/sconosciuti: risposta generica404 senza dati della struttura/ospiti, nessuna mutazione. Un form già aperto non può salvare dopo la revoca/scadenza; controllo ripetuto nella transazione, senza fidarsi dello stato letto al GET. È possibile acquisire/stampare la ricevuta durante la validità: la durata delle copie già scaricate non è governata dal token.

### D3 — Invito breve e compatibilità di emissione

Proposta: conservare il completo64caratteri per il nuovo contratto; sostituire il prefisso8con un **invito casuale indipendente32caratteri alfanumerici**, riferito alla stessa richiesta. Non derivarlo da codice, dati ospite o struttura e non usarlo come OTP da digitare. Il codice prenotazione non è un segreto. Ospite continua ad aprire il link da email/WhatsApp e può essere guidato al form senza nuovi login.

Motivo: oggi il breve concede accesso al completo, perciò non è una semplice decorazione. Separarlo dal prefisso evita revoca incompleta e collisioni tra codici ripetuti in strutture diverse. Il link sarà più lungo di quello attuale, ma resta più corto del completo e adatto a un click. Nuove emissioni e Cestino devono usare lo stesso contratto, eliminando la divergenza64/80. La compatibilità dei completi legacy resta governata da D5, non concessa per sempre.

Impronta del nuovo segreto breve per lookup/unicità, con eventuale copia cifrata per ripresentare l'URL all'operatore autorizzato, unicità globale della credenziale e autorizzazione sulla richiesta/parent/tenant risolti dal server; mai SID fornito dall'ospite. Questa è una conseguenza tecnica della decisione, non un valore operativo applicato ora. Cambiare formato **richiede D3 e D5**, non basta correggere il solo resolver80.

### D4 — Soglie, finestre e chiavi dei tentativi pubblici

Proposta di contatori comuni a `/checkin`, `/w` e `/completato`, senza possibilità di aggirare il limite cambiando alias/token completo/breve. Finestre fisse dal primo tentativo conteggiato, non prolungate dai tentativi già bloccati; superamento restituisce429 e Retry-After, senza revoca permanente del link.

| Operazione | Soglia proposta | Finestra | Chiave funzionale del contatore | Cosa conta |
|---|---|---|---|---|
| Ingresso al flusso, prima di risolvere il token |300richieste; dalla301ª429 senza lookup o mutazione |60secondi | IP effettivo+categoria ingresso Web Check-in | Tutti GET/HEAD/POST del flusso: validi, invalidi, ambigui, scaduti, revocati, CSRF invalidi e replay. Esclusi asset e lookup GEO. |
| GET/HEAD con credenziale valida (invito/form/conferma), entro la quota ingresso |60richieste; dalla61ª429 |60secondi | struttura_id+richiesta_id+IP effettivo+categoria lettura | Ogni GET/HEAD del flusso, incluso breve seguito da completo; contatore canonico, non uno per URL. |
| POST con credenziale valida, entro la quota ingresso |20richieste; dalla21ª429 |600secondi | struttura_id+richiesta_id+IP effettivo+categoria scrittura | Ogni tentativo, anche dati invalidi, CSRF invalido o replay; conteggio prima del controller e del rifiuto CSRF. |

La chiave memorizzata può essere una HMAC dei valori canonici, mai token in chiaro nei log. Per i validi, SID/RID provengono dal record risolto dal server: stessa IP su A e B usa contatori di lettura/scrittura differenti; altri inviti nella stessa struttura non consumano il contatore della richiesta. Resolver senza scritture; un SID dichiarato dal chiamante non determina chiavi o quote. Nessuna quota aggregata per struttura e nessun blocco permanente dopo tentativi altrui. Un bucket dei soli errori controllato dopo il lookup non basta a limitare i tentativi di risoluzione: per questo propongo un limite di ingresso prima del lookup.

Motivo:60GET coprono navigazione/riaperture;20salvataggi/10minuti coprono correzioni del gruppo;300ingressi/minuto limita anche tentativi su credenziali inesistenti senza creare una chiave cache per ogni stringa casuale. **Compromesso funzionale da approvare:** il limite di ingresso è comune al medesimo IP e può temporaneamente bloccare ospiti di strutture diverse dietro lo stesso Wi-Fi/NAT; non va presentato come quota isolata per tenant.300 è una proposta iniziale prudente per un ingresso documentale, non una soglia misurata sul traffico operativo. Le quote interne restano isolate per struttura/richiesta.

Ulteriori limiti: IP distribuiti possono moltiplicare tentativi; due persone con lo stesso invito e IP condividono la quota; asset/GEO non sono protetti da queste soglie. Non è un sistema completo anti-DDoS, né una garanzia multi-nodo senza cache condivisa attestata. La robustezza deriva anche da D3; le soglie non compensano un segreto esposto.

### D5 — Transizione dei link già emessi e rinnovo

Proposta: al futuro avvio della politica, completi legacy64/80/altri formati e vecchi brevi già compatibili restano utilizzabili per **massimo7giorni**, mai oltre il termine soggiorno di D1 e mai oltre conversione per la scrittura. Nessuna grazia per già revocati/cancellati o con termine soggiorno passato. GET/POST/completato e rate limit comuni anche durante la transizione. Il semplice accesso non attribuisce nuova emissione o30giorni.

Alla rigenerazione esplicita viene emesso completo64+invito32, revocando **tutte** le precedenti rappresentazioni in modo atomico. I dati della richiesta non vengono eliminati. Motivo: consente alla reception di sostituire inviti distribuiti, senza concedere nuovi30giorni a link storici potenzialmente molto vecchi. **Costo da approvare:** ospiti con vecchio invito non sostituito entro7giorni devono chiedere un nuovo link; nessun invio automatico o messaggio reale fa parte della decisione. La scelta7giorni è una proposta di transizione, non una durata trovata nel progetto. Non promette di rendere valido il breve80 oggi incompatibile: la rigenerazione esplicita D3 fornisce un nuovo invito valido.

L'analisi non legge o modifica i link operativi. Un futuro censimento/migrazione su dati operativi resta gate separato autorizzabile; ora la transizione si può progettare e provare soltanto su fixture sintetiche.

## 5. Cambiamenti e prove richiesti dopo approvazione

Non basta configurare un numero: modello di validità centralizzato per tutte le rappresentazioni, metadati di emissione/scadenza/revoca e invito breve, azioni gestionali tenant per revocare/rigenerare, applicazione atomica al salvataggio/conversione, uniformità delle ricevute, Cestino con invito non attivo, limiter con resolver privo di mutazioni e contatori atomici. Eventuali nuove migration/configurazioni sono **solo un piano**, da implementare/provare in isolamento in una futura task autorizzata, senza operazioni sullo schema operativo.

| Decisione | Verifiche locali da aggiungere |
|---|---|
| D1 | Prima/esattamente/dopo scadenza,30giorni assoluti, termine soggiorno locale, cambio anno/bisestile/DST, rinnovo esplicito e assenza rinnovo da GET/salvataggio o date ospite; stesso esito completo/breve/legacy/GET/POST/completato; nessuna scrittura o dato esposto su rifiuto. |
| D2 | Revisita e salvataggi legittimi del gruppo, conversione sola ricevuta su tutti gli alias, revoca manuale/cancellazione/cambio destinatario/form aperto/replay; revoca e salvataggio concorrenti con snapshot invariati; prima conversione e permessi preservati; ripristino senza riattivazione e rigenerazione autorizzata; rifiuti A/B e sessione di tenant diverso. |
| D3 | Invito indipendente e unico, revoca invalida tutti gli alias anche quando il nuovo completo ha stesso prefisso8; nessun scambio tra codici uguali in A/B; completo e Cestino con stesso formato; assenza del segreto breve persistito/loggato in chiaro; link legacy soggetti alla stessa politica. |
| D4 |300/301ingressi validi/invalidi e rifiuto prima del lookup,60/61GET,20/21POST, fine finestra, Retry-After, tentativi bloccati non allungano finestra; alias, HEAD e query string non azzerano quota; POST invalidi/419 conteggiati; cookie nuovo non azzera; quote lettura/scrittura di due tenant/inviti sullo stesso IP indipendenti, limite ingresso condiviso esplicitamente provato; IP inoltrato non attendibile ignorato; contatori paralleli atomici, cache indisponibile non permette salvataggi senza il controllo richiesto. |
| D5 | Link preesistenti64/80/formati legacy e vecchio breve prima/al/dopo termine transizione; scadenza soggiorno più vicina; nessuna grazia per revoca/cancellazione; rinnovo non resetta dati; tentativo anonimo dopo scadenza; nessun rinnovo automatico di storico al ripristino. |

Browser reale: invito→compilazione gruppo→salvataggi→riapertura→conversione→ricevuta; form aperto mentre operatore revoca; scadenza e429 con recupero dopo finestra; invito ripristinato solo dopo emissione; due strutture, stesso IP e contesti browser distinti. Dati esclusivamente sintetici effimeri; Laravel/Playwright soltanto launcher attestato del progetto, mail array/SOAP doppi, nessuna comunicazione reale. Globale da ripetere dopo modifica applicativa, non durante questa analisi.

I diagnostici correnti che asseriscono assenza di TTL/irrilevanza delle date restano evidenze storiche: un contratto approvato può cambiare quelle aspettative **esplicitamente**, con copie/versioni identificate e matrice di confronto. Non indebolire requisiti tenant, CSRF, atomicità o monouso del recupero; non modificare prove per occultare errori, non trasformare osservazioni correnti in standard. I test di policy proposta non risultano PASS finché non implementati ed eseguiti.

## 6. Fondamento e limiti

[OWASP Forgot Password](https://cheatsheetseries.owasp.org/cheatsheets/Forgot_Password_Cheat_Sheet.html) supporta segreti casuali, durata limitata e protezione da tentativi automatici; il monouso del recupero password non si trasferisce automaticamente al check-in modificabile di un gruppo. [OWASP REST Security](https://cheatsheetseries.owasp.org/cheatsheets/REST_Security_Cheat_Sheet.html) richiama429 per limiti di richieste. Queste fonti orientano la proposta, **non prescrivono30giorni/7giorni/300/60/20** e non sostituiscono l'approvazione del prodotto.

Accettazione locale già verificata invariata: globale728/7652, mirati135/983, finali31/165 e Chromium2/2 restano prove della baseline precedente, non della politica proposta. Nessun nuovo verdetto di produzione. Gate separati: MariaDB/FPM/SPanel, backup/restore/rollback, release/config/cache/storage/queue/cron/SSL, IP affidabile dietro proxy/cache condivisa e qualità dati operativi, accettazioni Questura/ISTAT/mail. Eventuali limiti del provider Questura non vengono scelti mediante D1–D5.

**Da approvare solo D1–D5.** Tutte le altre regole sopra sono requisiti già documentati, comportamenti osservati o delimitazioni tecniche; nessuna nuova richiesta di autorizzazione all'analisi locale. L'approvazione funzionale non autorizza operazioni operative, commit, push, deploy o trasmissioni.

Preservazione conclusiva della presente fase: fotografia iniziale8380file,8378byte-identici,sole2appendici documentali e1nuovo documento; zeroeliminazioni. Prefissi storici rapporto/Maestro verificati con hash; codice/test/config/schema ed evidenze JSON precedenti intatti. DiffcheckPASS,stagingvuoto,mainHEAD faef3cf01f43d19b20d1558c1fbd3ae32e48d849. Nessun test applicativo, DB o trasporto eseguito.

# Schedine Arrivi — 18 ottobre 2026

**Implementazione e collaudo isolato PASS; pubblicazione non ancora eseguita. BASELINE IN AUDIT generale invariata.** Ambito autorizzato: completare Arrivi, verificarlo, fare commit/push e pubblicarlo mediante C5; soltanto dati sintetici per le scritture di prova, nessun invio agli enti o email.

## Stato iniziale riletto

Git locale/GitHub `bef640c741b8583f49e0144e691155d477ffebd7`; produzione `945ef553aab29ac437ff194f7cfad081fb7ab7c7`, FPM attivo. VM36GiB, XFS37.290.283.008 byte, liberi2.501.165.056; Extra montato in `/Volumes/extra`, APFS, disponibile. Lavoro documentale e non tracciato preesistente conservato. Ramo dedicato `codex/arrivi-parcheggio-2026-10-18`.

## Comportamento implementato

Arrivi resta una scheda in attesa, distinta da bozza ed effettiva. L’ingresso «Nuovo arrivo» conduce al normale modulo Nuova schedina in contesto Arrivi; i vecchi endpoint delegano allo stesso controller e alle medesime validazioni/sincronizzazioni. Rimosse le implementazioni duplicate del vecchio controller. «Salva in Arrivi» crea o aggiorna una sola scheda parcheggiata e conserva componenti e relazioni; un salvataggio ordinario di una scheda Arrivi non la converte, anche ricevendo il vecchio `save_mode=full`.

«Passa a schedina» richiede conferma esplicita. Il server rivalida dati, soggiorno, componenti e consistenza del numero persone prima del cambio di stato. Blocco transazionale della struttura e della riga, ID invariato, numero del circuito effettivo assegnato nella stessa transazione. Retry su conversione conclusa non crea duplicati; una vecchia scheda con contesto Arrivi non può risalvare il record ormai convertito (409). Errore di validazione o transazione conserva dati e stato. Nessuna trasmissione esterna automatica introdotta.

## Giorno operativo e mezzanotte

Non esiste un campo fuso per struttura nello schema/configurazione esaminati: si usa `config('app.timezone')`, attualmente Europe/Rome. `ArriviDay` calcola il giorno operativo. Per Arrivi il getter del modello restituisce oggi a ogni lettura, anche su un oggetto già caricato; non serve un processo periodico o una richiesta esattamente a mezzanotte. Il valore SQL precedente resta un dato materializzato dell’ultimo salvataggio: la data effettiva è la proiezione odierna, usata da elenco e modulo. I filtri dell’elenco trattano l’arrivo come oggi; i flussi confermati escludono integralmente Arrivi prima di interrogare le date.

Prima di validare/salvare Arrivi e prima della conversione il backend sostituisce sempre l’arrivo inviato con oggi. Anche il modello normalizza i salvataggi e ricontrolla il soggiorno nella transizione da Arrivi. **La partenza non viene inventata o spostata.** Può restare mancante o scaduta durante il parcheggio; alert chiaro e blocco della conversione finché non viene corretta.

Il modulo usa il riferimento temporale del server e il fuso operativo, aggiorna soltanto l’arrivo ogni secondo e risincronizza il riferimento ogni30secondi, al ritorno della pagina e della visibilità. Il campo Arrivo è di sola lettura; è separato dall’accoppiamento automatico dei calendari per evitare modifiche della partenza. Richieste da pagine vecchie vengono comunque normalizzate dal server. Risposte tardive o fuori ordine non fanno arretrare il riferimento temporale del modulo. Nessuna modifica all’ora del server e nessun nuovo scheduler necessario. Nel Kernel attuale esiste la verifica settimanale `tassa:verifica-profili`, distinta e invariata: revisiona fonti/profili, non converte Arrivi né elabora ospiti; la vecchia annotazione storica del Maestro sul Kernel vuoto non descrive il codice attuale.

## Esclusioni verificate nel codice

Classificazione conservativa: `is_arrive=true` **oppure** `circuito=arrivi` identifica Arrivi; una combinazione incoerente non viene promossa a effettiva. Scope `effettive`: flag falso e circuito schedina o legacy NULL.

| Canale | Punto di esclusione |
|---|---|
| Elenco schedine | filtri di circuito/flag già presenti, conservati |
| Questura TXT / WS | selezione comune effettive, dettaglio singolo e validazione del generatore |
| ISTAT XML / movimenti | selezione effettive in IstatTabellaAService, guardia nello store del nuovo XML e nella prenotazione dell’operazione sotto lock |
| Tassa | dataset di controllo/liquidazione, anteprime di selezione, nessun calcolo nel modulo Arrivi, servizio diretto senza righe/notti/importi, ricevuta corrente negata per Arrivi |
| Presenze/statistiche | selezione comune PresenzeReportService per riepiloghi, ospiti, arrivi, partenze, occupazione e pernottamenti |
| Automatismi | movimenti del calendario e compleanni degli ospiti soltanto effettivi |
| Contatori/esportazioni clienti | dashboard effettive e Arrivi distinti; storico soggiorni e conteggi esportati soltanto effettivi |

Archivi storici già prodotti restano leggibili e immutati; nessuna cancellazione retroattiva. Il ripristino dal cestino conserva Arrivi, normalizza i timestamp serializzati dei consensi nel formato SQL mantenendo gli istanti, materializza l’arrivo odierno e preserva la partenza. Eliminazione Arrivi e conversione condividono l’ordine di lock; una cancellazione da elenco vecchio non può eliminare la scheda già diventata effettiva. Le query che proteggono riferimenti anagrafici/cataloghi, import componenti e audit continuano a riconoscere anche le schede parcheggiate: non sono processi di soggiorni confermati. Il comando di rinumerazione tratta i circuiti separatamente e non converte stati. L’export globale legacy rimane disabilitato con410. Il vecchio modello della tabella `arrivi` non è usato dagli ingressi operativi censiti.

## Prove e diagnostici

Prime prove mirate:17test/155asserzioni PASS dopo correzione del vincolo residuo sulla partenza parcheggiata e fixture componenti. Ampliamento backend:20test/173asserzioni PASS, con data controllata, cambio fuso, permanenza parcheggiata per più giorni, conversione/retry, rollback, componenti incompleti/validi, contesto e tenant. Tre browser a1440/768/390 PASS per salvataggio, annullamento, mezzanotte senza perdita e partenza invariata. Prima selezione browser fallita per confronto esatto con il nome del pulsante comprensivo di icona; corretti i selettori. Attesa esplicita dell’inizializzazione del calendario aggiunta nel quarto caso.

Regressione iniziale205test: una regressione dello strumento applicativo rilevata e corretta prima della pubblicazione: controllo Arrivi inserito anche nella lettura storica XML, dove la collezione non esiste. Guardia limitata allo store dei nuovi XML; storico lasciato invariato. Ripetizione205test/2661asserzioni PASS, con una deprecazione PHPUnit preesistente. Ulteriori diagnostici: closure della ricerca completata con il parametro q, ordine dei test puri corretto prima dei test che avviano Laravel (anche una classe denominata Unit avvia il framework), avviso di successo chiuso tramite OK nel browser mobile. Il nuovo test del cestino ha rilevato un difetto preesistente nel formato SQL dei timestamp serializzati; corretto soltanto per il ripristino Arrivi. Ciclo esteso conclusivo **322 test /3.259 asserzioni PASS**, tre deprecazioni PHPUnit preesistenti nelle classi ISTAT selezionate. Dieci prove browser PASS (Arrivi e regressione Nuova schedina, desktop/tablet/mobile). Ultime modifiche su route numeriche e clock contro risposte vecchie/lente: **24 test /222 asserzioni e dieci browser PASS**, con controller/modelli/servizi byte-identici al ciclo esteso. Successivo solo dizionario di messaggi server italiani: **24 test /223 asserzioni PASS**; confronto normalizzato prova che non cambia il resto del controller. **17 guardie,23 override respinti, build/GEO PASS**. Suite sovrapposte non sommate. Nessun test Laravel fuori dal namespace isolato.

Originali e diagnostici in `output/arrivi-2026-10-18`; evidenze selezionate in [directory dedicata](evidenze/arrivi-2026-10-18/). Le suite sovrapposte non vengono sommate.

## Capacità e preparazione C5

Conservati182pack Git identici in recuperi conclusi mediante CoW, dopo digest/manifest/metadati e assenza utilizzatori; nessun percorso o backup eliminato. Misura VM2.438.856.704→2.803.113.984 byte. Rimossi36.192soli file vendor/build ricostruibili verificati contro inventari e cifrati autenticati; DB, SQL, env, chiavi, upload, loghi e diagnostici preservati. Rimossi inoltre19archivi filesystem decifrati derivati (327.574.924byte nominali), soltanto dopo confronto con i cifrati originali HMAC conservati e assenza utilizzatori. Nessun SQL o database candidato alla pulizia. Misure intermedie durante i test non sommate come recupero netto: primo preflight corrente STOP prima PREPARE:3.540.140.032 byte liberi contro3.732.652.032 richiesti, deficit192.512.000. Stima aggiornata ai massimi completi946.122.752 e366.555.136 byte,16MiB per ciascun nuovo recupero e margine1GiB invariati; mai ridotta dopo le pulizie. Ulteriore audit concluso:409.572file di sole dipendenze identiche nelle12copie di test proprie rimosse, canonico conservato e protetti esclusi, inventario completo con hash custodito. Misura3.540.451.328→3.749.924.864byte; controllo successivo3.749.920.768contro3.732.652.032PASS, margine1GiBinvariato. Crescita effettiva della baseline produttiva682.194byte, con blocchi prudenziali per485nuovi file2.668.754byte: entro i16MiB già previsti. Nessuna soglia abbassata.

Preflight di capacità superato; C5 previsto con nuovo kit riferito al candidato corrente, logica diagnostica già pubblicata invariata, nuovo PREPARE e recupero indipendente, S0 fresco, guardiano, rollback coordinato e monitor completo. Nessuna DDL prevista. Il backup cifrato offline su Extra della pubblicazione precedente è conservato; non viene presentato come nuovo S0 produttivo. Backup, capacità definitiva, pubblicazione, accettazione online e monitor di questa attività sono ancora pendenti.

Ultimo controllo prima PREPARE: il ripristino dal cestino riconosce Arrivi anche con flag/circuito discordanti, senza promozione implicita. Regressione isolata R15 PASS25test/233asserzioni; sola modifica applicativa rispetto a8a, suite precedenti non sommate.

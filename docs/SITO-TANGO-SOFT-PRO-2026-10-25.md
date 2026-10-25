# Sito Tango Soft Pro — 25 ottobre 2026

**PRONTO A REVISIONE, CANDIDATO QUALIFICATO. NON PUBBLICATO. BASELINE IN AUDIT invariata.** Questa fase risponde al nuovo brief e supera l’offerta di prova della precedente proposta commerciale soltanto per il sito corrente. Contratti, registri e documentazione storica restano invariati; nessuna prova gratuita o durata di prova è proposta nel nuovo sito.

## Identità e percorso

Nuova identità pubblica **Tango Soft Pro**, prodotto **Schedine di Notifica**, dominio ufficiale invariato `https://schedinedinotifica.tanggo.software`. Progetto Laravel, cinque URL e worktree dedicato preesistenti preservati. Nessun progetto esterno, push, integrazione o deploy.

Home con titolo esatto «Meno burocrazia. Più tempo per i tuoi ospiti.», descrizione e pulsanti «Richiedi una demo» / «Scopri le funzioni» del brief. Palette blu scuro/turchese/chiaro, hero fotografica piena e split con schermata autentica, alternanza di sezioni editoriali, split con immagini, fasce di profondità, piani e assistenza. Navigazione separata Funzionalità, Moduli e ampliamenti, Prezzi, Max Full, Demo e accesso. Layout HTML reale, senza mockup della pagina come immagine.

La pagina moduli conserva le condizioni verificate di anagrafiche, Arrivi, Web Check-in, componenti, Alloggiati Web, ISTAT/Ross1000, Tassa, importazioni/esportazioni/stampe/storico, strutture e accessi. Ciascun percorso espone utilizzo, vantaggio, esempio e requisiti. Stati e credenziali dei servizi esterni restano espliciti: nessuna compatibilità universale o accettazione ufficiale dichiarata, nessuna roadmap inventata. La demo mantiene il tour accessibile di cinque schermate autentiche.

## Prezzi e Max Full

`web/assets/catalogo-commerciale.json`, gestito dall’agente principale, è l’unica fonte delle tariffe. Home e Prezzi usano segnaposto renderizzati dal controller sul server; il riepilogo del modulo legge lo stesso JSON con `cache: no-store`. Nessuna tariffa duplicata nel codice JS e nessun prezzo passato come autorità al backend.

Piani distinti visivamente tra Appartamenti e Strutture ricettive. Il piano Strutture è complessivo per una o due strutture, senza criterio per numero di camere. Note annuali, IVA e quotazione separata dei servizi provengono dal catalogo. Le offerte precedenti di 400 euro, soglia 60 camere e prova di sette giorni sono assenti dal contenuto corrente.

Blocco di dettaglio `/prezzi#max-full` con titolo, sottotitolo, descrizione, sette attività, prezzo e nota commerciale esatti del brief, renderizzati dalla medesima fonte. Max Full è presentato come servizio aggiuntivo all’abbonamento, con attività, frequenza, canali e tempi definiti nel preventivo; nessuna promessa di 24/7, vigilanza continua, invii a cura del personale o delega fiscale.

## Contatti

Sistema di contatto esistente preservato. Query `piano` e `servizio=max-full` preselezionano la richiesta. Piano e servizio restano modificabili e annullabili. Riepilogo aggiornato dal catalogo, senza acquisti o pagamenti. Payload aggiunge esclusivamente codice `plan` e booleano `max_full`; validazione e ricostruzione del riepilogo ufficiale sono responsabilità del backend coordinato dal principale. La scelta del motivo di contatto non viene sovrascritta all’invio.

Conservati validazione, honeypot, gestione italiana del limite429, conservazione dei dati in errore, selettori con nome accessibile, calendario, conferma, focus trap/Escape e ritorno al pulsante. Recapiti opzionali raccolti progressivamente. Nessuna nuova informativa, telefono, indirizzo o email inventati.

## Immagini e prestazioni

Tre ambientazioni illustrative originali prodotte dal principale con ImageGen: reception1600×900, assistenza1280×720 e sviluppo1280×720. Sono immagini illustrative AI, non fotografie del personale o di clienti reali. Provenienza e digest nel manifest del principale. Tre WebP complessivi300.762byte, risorse locali senza hotlink o nuovi tracciamenti.

Cinque schermate autentiche già acquisite nel runtime sintetico ufficiale,172.172byte WebP lossless, riutilizzate senza ritocco dei pixel o del marchio interno del gestionale. L’area operativa mostra soltanto dati dimostrativi. L’esempio Tassa riguarda Bellaria giugno2026 e non rappresenta il prezzo del software. Dimensioni dichiarate, caricamento differito fuori hero e apertura a dimensione originale nel tour.

## Verifiche e limiti attuali

Analisi statica completata: sintassi Node di JS e spec; `git diff --check`; cinque HTML annidati correttamente, un H1 ciascuno, ID univoci e ancore valide; risorse canoniche/specchi CSS e JS mantenuti identici. Nessun test Laravel eseguito sull’host.

Spec browser aggiornato per le cinque pagine, larghezze1440/768/390 e quattro intermedie, prezzi DOM confrontati al catalogo, gruppi distinti, tutti i sei piani, contenuto Max Full, selezione/cambio/annullamento, payload senza prezzo client, hash HTTP di catalogo e immagini, tour e controlli esistenti del modulo. **TSP2 sul candidato `d830988255f707ba880efc2cadff892a86864ef7`: 34 test PHP / 315 asserzioni e 6 percorsi browser PASS (1,2 minuti riportati). Build/GEO, 23 rifiuti di override e 4.396 digest sorgenti PASS nella sola VM ufficiale.** I PASS COMM2 precedenti restano storici e non qualificano il nuovo design o backend.

Anteprima locale coordinata dal principale: `http://127.0.0.1:63351/`, con resa dei segnaposto dal catalogo e conferma dimostrativa esplicita senza database/email. Il backend è stato verificato separatamente nel runtime effimero: lead e attività conservano piano e servizio con tariffa ricostruita dal server; valori prezzo client ignorati, selezioni invalide respinte, nessun account o struttura attivati. Pubblicazione non eseguita né autorizzata in questa fase; dati societari/privacy/recapiti e condizioni operative dei servizi restano da confermare prima di pubblicare.

## File e coordinamento

Agente web: `web/*.html`, `web/assets/styles.css`, `web/assets/app.js`, tre foto locali, i due specchi `public/site-assets`, spec frontend dedicato e questo rapporto. Agente principale: catalogo JSON, controller/servizio, test PHP, Maestro, manifest immagini ed evidenze, anteprima e runtime. Nessuna modifica da questo agente a ISTAT, logica interna, autenticazione o database condiviso.

## Riferimento prioritario e composizione finale

Riferimento visivo condiviso dall’utente ispezionato in browser: hero reception a piena larghezza con overlay navy, screenshot autentico nello split successivo, quattro moduli con miniature, fascia fotografica con quattro piani principali e i due preventivi sempre disponibili, Max Full fotografico scuro e contatti compatti. Nessun asset, recapito o testo commerciale importato dal riferimento. Header navy e wordmark Tango Soft Pro, CTA turchese con testo navy; teal per testo normale scurito a `#07777a` sui fondi chiari. Disponibilità XML Emilia-Romagna e storico aggiornata al riscontro operativo del principale sul nuovo rilascio ISTAT, senza promettere l’accettazione dell’ente o l’abilitazione automatica del canale.

## Qualifica conclusiva e consegna

[Ricevuta, digest e immagini originali](evidenze/sito-commerciale-2026-10-25/verifica.json). Commit locali della fase: `ceacfdf4` catalogo/controller/CRM/test PHP, `8b8b0159` frontend e `d8309882` correzione responsive qualificata. I successivi aggiornamenti documentali non modificano i sorgenti qualificati. Il nuovo brief sostituisce espressamente la precedente prova di sette giorni e il vecchio listino soltanto nel sito corrente. Prezzi e identità Tango Soft Pro derivano dall’istruzione del proprietario del 25 ottobre, non da supposizioni sul listino storico.

Il riferimento grafico condiviso dal proprietario è stato aperto e ispezionato: testata fotografica piena, split con schermata autentica, quattro miniature, fascia fotografica con quattro tariffe e due preventivi distinti, Max Full scuro e contatti. Composizione originale HTML/CSS, senza copiare immagini, recapiti o testi del riferimento. XML Emilia-Romagna e storico ISTAT presentati secondo il Maestro aggiornato dopo la pubblicazione applicativa `a90d63aa`; disponibilità subordinata a credenziali/codice/abilitazioni del canale, senza dichiarare accettazione reale da Ross1000. Nessun codice ISTAT è stato modificato nel ramo web.

TSP1 conservato come NONPASS: PHP34/315 positivo, browser4/6; dopo apertura dei recapiti il checkbox della preferenza oraria ereditava width100% e min-height47px, spingendo il testo fuori dal form a768/390. Corretto con dimensioni18×18px specifiche; CSS del sito caricato dopo vendor, recuperando anche altezza e stile Select2. TSP2 ripete l’intera qualifica, con misure prima dell’invio, dopo errore422 e dopo reset/chiusura conferma. Asserzioni e timeout preservati, nessun overflow nascosto per superare le prove. Le suite ripetute non vengono sommate.

Cinque pagine a1440/768/390:15combinazioni, più navigazione/CTA a1280/1266/1151/1024. Verificati hash HTTP CSS/JS/catalogo/otto immagini/sitemap/robots, tutti sei piani e gruppi, Max Full completo, cambio/annullamento e conservazione del motivo, payload privo di tariffa autorevole client, menu/tastiera/ancore/FAQ/tour, errori422/429, dati conservati, focus/Escape e accessi. Zero errori JS o risorse mancanti nelle combinazioni controllate. Ispezionate tutte23 immagini originali; quattro allegate. Contrasti delle otto coppie principali fra4,834:1 e16,34:1; contrasto fotografico anche ispezionato visivamente, senza dichiarazione generale di conformità accessibile.

Backend limitato al catalogo pubblico e ai due controller del sito: rendering HTML con segnaposto escapati e errore esplicito sui riferimenti inesistenti; riepilogo canonico nel messaggio CRM e nell’attività collegata. Schema, contratti, storico, autenticazione e moduli interni invariati. Nessun acquisto, account, addebito, email o trasmissione a enti. Gli URL pubblici e le redirezioni storiche sono conservati; canonical/Open Graph e sitemap restano sul dominio ufficiale. Il modulo locale è dimostrativo e non registra le richieste; quello Laravel è qualificato nel runtime effimero.

Due runtime propri conclusi e rimossi; osservatore concluso:32campioni, minimo VM2.487.021.568byte≥1GiB e Mac15.837.028.352byte≥8GiB; gate Extra≥40GiB eseguito prima dell’avvio. Daily945ef553 e SELinuxEnforcing confermati prima/dopo. VM40GiB restituita spenta; nessuna cancellazione o modifica dei recuperi/backup. Il checkout originario resta al suo HEAD6a652b72, con i documenti sporchi iniziali preservati; soltanto rapporto/evidenze e una voce del Maestro sono riportati per coordinamento. Worktree web e ramo indipendenti restano il luogo del codice da revisionare. Pubblicazione e integrazione sul ramo applicativo sospese fino alla revisione dell’utente.

Restano da fornire o approvare dati societari/PartitaIVA, informativa privacy e recapiti aggiuntivi verificati, oltre alle condizioni operative da concordare per Max Full. Non impediscono la revisione locale. Nessun video disponibile è stato inventato: la Demo utilizza il percorso interattivo autentico. Prima integrazione futura: riportare i soli commit del sito sulla base applicativa aggiornata, coordinare il Maestro e ripetere i controlli pertinenti prima di un eventuale rilascio autorizzato.

# Controllo dei flussi — 24 novembre 2026

Richiesta: verificare la presentabilità dell'intero programma, correggere ordine delle colonne Tassa/Tariffa e stile del filtro Compilazione Web Check-in, fornire rapporti prima del successivo percorso guidato di check-in.

## Perimetro ed evidenze iniziali

Due soli template applicativi modificati, nessun servizio o controller, migrazione, algoritmo fiscale, trasporto o privilegio cambiato. Verifica browser sintetica positiva a 1440/768/390 su colonne e valori effettivi, selezione/ricerca/cancellazione del filtro, conservazione della ricerca, quattro stati del web check-in e cinque schede Presenze. Zero scritture da navigazione, overflow ed errori JavaScript nelle tre prove.

Ricognizione online circoscritta ai tre hotel demo: 60 pagine funzionali HTTP 200 e tre accessi Superadmin rifiutati come previsto, zero anomalie osservate. Tutte le richieste di modifica/invio, eccetto il normale login, sono state bloccate nel browser dell'audit. Inventario in sola lettura: 12 schedine, zero richieste web, zero trasmissioni Questura/ISTAT e zero consolidamenti Tassa. Nessun dato reale modificato e nessun invio eseguito.

## Suite generale

R1: raccolti **1.336 casi** dalla suite ordinaria di 116 file `*Test.php`; runtime ufficiale con rete isolata, database sintetico attestato, build/GEO e 23 rifiuti di override positivi. Il processo PHPUnit ha esaurito il proprio limite di 256 MB dopo l'avvio di circa un terzo della suite, con alcuni fallimenti/errori precedenti al limite. La prova non è un successo e non permette un verdetto globale.

R2 completata con 1024 MB nella sola copia privata: **1.336 casi, 15.413 asserzioni, 16 fallimenti e 6 errori**. Il JUnit completo è conservato privatamente. Classificazione: 17 esiti di CustomerSharing e uno di CustomerWizard dovuti ai dati lasciati dalle prove non transazionali di carico/concorrenza; un rendering delle credenziali senza contesto autenticato; una cache letta dopo il ritorno dalla data simulata, oltre la scadenza; due test di migrazione ancora vincolati al vecchio conteggio di 66 tabelle invece dell'inventario corrente di 74.

Corrette sei sole classi di test: pulizia del database effimero dopo i casi con processi/transazioni reali; contesto autenticato per il rendering; lettura della cache nel tempo simulato; confronto esatto dell'inventario delle tabelle prima/dopo la migrazione al posto del numero storico fisso. Le asserzioni sui dati, sulle identità e sui trasporti rimangono attive; nessun codice applicativo aggiuntivo modificato.

R3 mirata, con il limite originale di 256 MB: **93 test e 1.139 asserzioni positivi**, inclusa la sequenza carico → concorrenza → condivisione → clienti e le altre classi coinvolte. Prova di carico: 6.501 schede, 6.162 identità, 18.486 riferimenti locali; attivazione 22,976 secondi, picco osservato 136,7 MiB. Il dato riguarda la VM e il dataset sintetico e non è una garanzia di prestazioni di produzione.

R4 conclusa: **1336 test e 15.722 asserzioni positivi**, zero errori, fallimenti o casi saltati, con il limite ordinario di 256 MB. Tempo del rapporto JUnit: 679.374 secondi. Tutti i 116 file ordinari della suite sono inclusi. Nessuna asserzione di business rimossa per ottenere questo esito. Il JUnit completo e l’output sono conservati privatamente; il riepilogo per modulo è nelle evidenze.

## Giudizio corrente

Le due correzioni sono qualificate nel browser; la panoramica dei dati è osservabile con i limiti descritti nel [rapporto di presentazione](PANORAMICA-PRESENTAZIONE-2026-11-24.md). La suite ordinaria e i controlli UI selezionati sono positivi. Questo non equivale alla verifica di ogni possibile combinazione, di tutti gli audit manuali storici, dei trasporti reali o dell’accettazione degli enti. La panoramica online è presentabile nei limiti dei dati disponibili; il ciclo reale di check-in e gli storici di invio vanno mostrati con dati e prove dedicate. **BASELINE IN AUDIT invariata.**

## Pubblicazione delle due correzioni

Candidato `f150869fd92f1367e5fc57ff66a89ddb6d6d7315`, PR58 integrata, stesso codice installato con checkpoint nuovo di Git/viste, recupero qualificato e guardiano systemd. Svuotata sola cache Blade; nessuna migrazione o modifica di ambiente, altre cache e trasporti. Dopo il rilascio: 63 esiti attesi nella ricognizione, cinque scenari operativi delle correzioni e account proprietario positivi. L’esito delle due correzioni resta distinto dall’audit generale.

## Risultati per area della suite ordinaria

| Area | Casi positivi | Classi |
|---|---:|---:|
| Schedine e Arrivi | 197 | 13 |
| Clienti e condivisione | 153 | 13 |
| Accessi, cataloghi e altri moduli | 360 | 39 |
| Tassa | 85 | 11 |
| Presenze e calendario | 29 | 4 |
| Questura | 267 | 19 |
| ISTAT | 76 | 3 |
| Web Check-in | 169 | 14 |

I conteggi descrivono i casi eseguiti, non una percentuale di copertura delle righe o una certificazione fiscale. Aggiunte 17 guardie statiche positive, 23 rifiuti di override in ogni runtime, build/GEO e tre scenari browser riproducibili a 1440/768/390 con fixture sintetica pubblicata. [Riepilogo JUnit per modulo](evidenze/panoramica-demo-2026-11-24/suite-finale.json).

Riproduzione dei tre scenari UI nel runner ufficiale isolato: `run.py --fixtures tests/Isolation/panoramica-presentazione-fixtures.php --playwright tests/Feature/panoramica-presentazione.playwright.spec.js`. La fixture contiene soltanto identità e credenziali sintetiche dedicate e non dipende dagli accessi online.

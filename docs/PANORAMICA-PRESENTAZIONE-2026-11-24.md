# Presentazione demo — panoramica verificata, 24 novembre 2026

La panoramica dei dati e dei calcoli è dimostrabile nei tre hotel Tanggo. La dimostrazione completa di web check-in e invii richiede un percorso aggiuntivo: nel pacchetto attuale non sono presenti richieste web, trasmissioni né consolidamenti del rapporto Tassa. Non presentare questi storici vuoti come operazioni già eseguite. La verifica generale automatizzata è in corso e non costituisce ancora un esito positivo globale.

## Correzioni richieste

Nel rapporto Tassa l'ordine diventa **Tariffa € → Tassa €**: prima il prezzo unitario per persona/notte, poi l'importo della riga. Per esempio Rimini: 3 notti imponibili × 2,50 € = 7,50 €. I valori erano già distinti correttamente; la modifica riguarda l'ordine di lettura, senza cambiare formule, aliquote, CSV o consolidamenti.

Il filtro **Compilazione** del Web Check-in usa il selettore interattivo standard del programma, con ricerca e possibilità di cancellare la selezione. Mantiene le opzioni e i valori esistenti, il filtro testuale e il comportamento server. Il menu scuro dello screenshot era il selettore nativo del sistema operativo, funzionale ma non coerente visivamente con gli altri campi.

Le due viste e le cinque schede Presenze sono state provate nella VM ufficiale a 1440, 768 e 390 pixel: tre scenari positivi, nessuna scrittura durante la navigazione, nessun errore JavaScript o overflow. Pubblicazione delle correzioni da registrare al completamento.

## Dati già pronti

| Hotel | Schedine | Giugno: arrivi / partenze / presenze | Settimana 30/11–06/12: ospiti / presenze | Imposta soggiorno di giugno |
|---|---:|---|---|---:|
| Tanggo Igea Marina | 4 | 1 / 1 / 3 | 5 / 15 | 4,50 € |
| Tanggo Bellaria | 4 | 1 / 1 / 3 | 5 / 19 | 4,50 € |
| Tanggo Rimini | 4 | 1 / 1 / 3 | 5 / 17 | 7,50 € |

Tutte le date sono del 2026. Le presenze sono persone × notti, con arrivo incluso e partenza esclusa. Non sono il numero delle anagrafiche condivise. I valori sono stati letti nelle pagine operative online, non soltanto ricostruiti dal dataset originario. La ricognizione sul servito `3699926` ha controllato 60 pagine funzionali dei tre account hotel e tre rifiuti attesi dell'area Superadmin: tutti gli esiti previsti, nessun overflow o errore JavaScript rilevato.

## Cosa mostrare, e con quale significato

| Area | Disponibile per la panoramica | Limite concreto della demo |
|---|---|---|
| Accessi e strutture | Tre accessi hotel indipendenti; proprietario Tango, accesso `tanggo.demo`, con tre strutture | Nessun accesso amministrativo globale da attribuire agli operatori |
| Clienti | Anagrafiche condivise e complete; ricerca e consultazione | Fusione/annullamento e omonimi vanno mostrati come casi dimostrativi; non fondere le due Giulia con nascite differenti |
| Import / liste-export | Schermate e strumenti consultabili | Non è presente un caricamento demo eseguito in questa verifica; nessun file reale importato |
| Schedine | Quattro per hotel, con soggiorni singolo/famiglia/gruppo e scheda aggiuntiva di giugno | Non confondere la scheda programmata con un invio all'ente |
| Arrivi | Pagina consultabile | I soggiorni demo esistenti sono a giugno o dal 30 novembre; non popolano automaticamente gli Arrivi del giorno corrente |
| Presenze | Riepilogo mensile, dettaglio, situazione giornaliera, movimenti e occupazione | Selezionare il periodo demo; la data corrente può non avere ospiti |
| Tassa: aliquota e rapporto | Giugno tassabile; fuori stagione nei due hotel di Bellaria-Igea Marina; rapporto e anteprima | “Imposta calcolata/dovuta” non dimostra pagamento, incasso o versamento |
| Tassa: pagamento e consolidamento | Anteprima del calcolo disponibile | Zero versioni consolidate; nel flusso esaminato non risulta una registrazione che attesti l'effettivo pagamento dell'ospite o il versamento al Comune |
| Questura | Schermata, preparazione e controlli del TXT; regole provate con dati sintetici | Modalità demo TXT, zero trasmissioni e ricevute; arrivi futuri/pregressi non diventano esportabili oggi aggirando le regole temporali |
| ISTAT / Tavola A | Pagina e dati del periodo; creazione XML provata nel collaudo sintetico | Endpoint demo non ufficiale, zero trasmissioni; nessuna accettazione di Ross1000 dimostrata online |
| Web Check-in | Elenco, nuovo, storico e filtro disponibili | Zero richieste nel pacchetto: percorso completo da preparare separatamente |
| Calendario | Pagina e selezione dei periodi disponibili | Per mostrare i soggiorni, scegliere i periodi pertinenti; eventuali attività manuali sono distinte dai soggiorni |
| Dati struttura e cataloghi | Hotel 3 stelle, comuni corretti, loghi forniti e valori predefiniti | Presentare la tassa come calcolo secondo la configurazione della struttura, come concordato; non come automatismo fiscale completo per tutti i Comuni |
| Cestino | Pagina consultabile | Non sono state cancellate schede per costruire una scena dimostrativa |

## Traccia consigliata

1. Proprietario: mostrare le tre strutture, poi aprire una struttura.
2. Clienti: ricerca, scheda completa, condivisione fra gli hotel e distinzione degli omonimi.
3. Schedine: singolo, famiglia e gruppo; documenti/residenza/componenti/camera.
4. Presenze: anno 2026, da giugno a giugno. Mostrare una persona, tre presenze, cinque schede di analisi e stampe. Poi dettaglio dal 30 novembre al 6 dicembre, confrontando i tre hotel.
5. Tassa: rapporto di giugno e anteprima del calcolo; evidenziare tariffa, notti imponibili e importo finale. Mostrare i soggiorni fuori stagione soltanto nei due hotel di Bellaria-Igea Marina.
6. Invii: mostrare preparazione, validazione e formati disponibili, dichiarando che i dati demo non sono stati inviati. Le prove con risposte simulate non equivalgono alla consegna o accettazione dell'ente.
7. Web Check-in: mostrare l'interfaccia corretta. Per il ciclo invito → ospite → verifica reception → registrazione occorre il percorso successivo dedicato.

## Percorso di controllo successivo

Usare una nuova richiesta contrassegnata DEMO in uno dei tre hotel, con ospiti sintetici e date compatibili con il flusso da verificare. Preparare l'invito, mostrare il link senza spedire email/WhatsApp, compilare come ospite, salvare e riprendere, controllare come reception e verificare la registrazione risultante. Per provare Arrivi e il TXT del giorno servirà un arrivo nel giorno operativo effettivo, senza cambiare l'orologio di produzione. Verificare infine presenze, tassa e storico. Conservare gli ID nel manifest demo per l'eventuale rimozione selettiva. Questo percorso non è stato eseguito sui dati online in questa attività.

**BASELINE IN AUDIT invariata.** Queste verifiche descrivono il campione e i flussi osservati; non attestano assenza assoluta di errori, versamenti fiscali o accettazione degli enti.

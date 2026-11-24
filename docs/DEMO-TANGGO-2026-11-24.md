# Demo Tango — tre hotel, 24 novembre 2026

Pacchetto online `DEMO-TANGGO-20261124-7E996FCB`, dedicato alle presentazioni. Proprietario **Tango**, tre strutture e accessi indipendenti:

- Hotel Tanggo Igea Marina;
- Hotel Tanggo Bellaria;
- Hotel Tanggo Rimini.

Cinque anagrafiche di origine per hotel, quindici schede condivise visibili in ciascuna struttura (45 proiezioni locali). Nove schedine definitive: per ogni hotel un singolo, una famiglia di due persone e un gruppo di due persone. Sei componenti complessivi, nove camere associate ai soggiorni. Tutte le anagrafiche sono sintetiche, inclusi documenti, recapiti e identificativi fiscali.

Usati soltanto i cataloghi predefiniti esistenti: titoli Sig./Sig.ra, Turismo → City break → Arte e cultura, Hotel, 3 stelle. Nessuna nuova definizione globale o voce personalizzata. Il collegamento tecnico fra i tre hotel consente la condivisione clienti e non costituisce una nuova voce del catalogo Gruppi. I cataloghi locali standard dei nuovi hotel sono inizializzati dagli eventi applicativi esistenti.

## Traccia per la presentazione

1. Accedere come Tango, aprire il selettore strutture e mostrare i tre hotel.
2. In Clienti mostrare le anagrafiche condivise; cambiare hotel per ritrovare gli stessi clienti.
3. In Schedine mostrare i tre soggiorni: singolo, famiglia, gruppo. Aprire una scheda per mostrare dati personali, documento, residenza, camera e componente.
4. Aprire Condivisione clienti: **Marco Rinaldi**, data di nascita 12/06/1986, è il duplicato intenzionale. È predisposto per una fusione seguita dal relativo annullamento. Effettuare l'annullamento prima di altre modifiche o nuove registrazioni: il programma protegge le fusioni che hanno aggiornamenti successivi.
5. Le due **Giulia Romano**, nate il 09/07/1990 e il 09/07/1992, sono omonime distinte: mostrare «Mantieni separati». Non correggerne le date per poterle fondere.
6. Accedere con uno degli utenti hotel per mostrare l'accesso indipendente alla propria struttura.

Le credenziali separate sono nel file locale privato `storage/app/private/demo-tanggo-20261124/ACCESSI-DEMO.txt`. Non includerlo nei video.

## Calendario

| Hotel | Singolo | Famiglia | Gruppo |
|---|---|---|---|
| Tanggo Igea Marina | 30/11–03/12 | 01/12–05/12 | 30/11–02/12 |
| Tanggo Bellaria | 30/11–03/12 | 01/12–05/12 | 02/12–06/12 |
| Tanggo Rimini | 30/11–03/12 | 01/12–05/12 | 03/12–06/12 |

Tutte le date sono del 2026. Il duplicato Marco compare in soggiorni consecutivi, senza notti sovrapposte. Il calendario corrente può essere vuoto: selezionare la settimana **30 novembre–6 dicembre**. Le schedine future sono nel circuito Schedine, poiché Arrivi è legato al giorno corrente. Il TXT Questura non è esportabile oggi per un arrivo futuro: è il controllo temporale previsto, da non aggirare cambiando l'orologio online.

Per i due hotel di Bellaria-Igea Marina l'imposta nella settimana demo è zero perché fuori stagione. La configurazione demo di Rimini usa 2,50 € per persona/notte e massimo sette notti, coerentemente con la [guida comunale 2026](https://www.comune.rimini.it/system/files/2026-03/GUIDA%20IMPOSTA%20DI%20SOGGIORNO%202026.pdf). Nessuna dichiarazione o quietanza reale è prodotta.

## Stemmi e invii

Igea Marina e Bellaria usano copie dedicate dello stemma di Bellaria-Igea Marina già presente nell'applicazione. L'immagine associata a Rimini nel catalogo GEO preesistente rappresenta invece Bellaria: per il solo hotel demo è stata usata una copia corretta, senza modificare il GEO globale. Stemma di Rimini: **MostEpic**, [Wikimedia Commons](https://commons.wikimedia.org/wiki/File:Rimini-Stemma.svg), [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/), anteprima PNG originale non modificata.

Questura impostata su TXT, credenziali esclusivamente fittizie; il trasporto del servizio non è abilitato per queste strutture. ISTAT usa l'endpoint dimostrativo `https://demo.invalid/ross1000`, distinto da quello ufficiale e rifiutato dal controllo applicativo dell'invio. Nessun flag globale o configurazione di strutture preesistenti modificati. Nessun invio eseguito.

## Verifiche e rimozione futura

Creazione provata nella VM ufficiale isolata, con identità runtime, build/GEO e 23 rifiuti di override positivi. Validatori applicativi su tutte le 45 proiezioni clienti e sulle nove schedine; nove TXT validati simulando soltanto nella VM il relativo giorno di arrivo, tre analisi e XML ISTAT; fusione, annullamento e separazione degli omonimi positivi. Le modifiche dimostrative delle fusioni sono state annullate nella transazione di prova.

Prima della scrittura online, checkpoint cifrato nuovo e recupero nativo senza rete delle **74 tabelle** verificati sul server. Creazione in transazione, collisioni dei nomi bloccanti. Nessun codice applicativo, schema, dato preesistente o catalogo globale modificato. Stato generale **BASELINE IN AUDIT** invariato.

Manifest privato locale e sul server con proprietario, quattro utenti, tre strutture, 45 clienti, 15 persone condivise, nove schedine, sei componenti e due immagini di proprietà del pacchetto. Nessuna rimozione eseguita adesso. Per eliminarlo in futuro: nuovo checkpoint, riconciliazione del manifest con eventuali record aggiunti durante le demo, verifica dell'assenza di collegamenti esterni, separazione del collegamento clienti tramite il servizio esistente e rimozione selettiva dei soli record del pacchetto nell'ordine delle dipendenze. Conservare i cataloghi GEO e le immagini originali. Il manifest non è un pulsante di cancellazione automatica e non autorizza eliminazioni di nuove attività reali.

Verifica operativa online conclusa: **quattro accessi riusciti e tredici pagine HTTP 200**, senza immagini mancanti o errori JavaScript. Il selettore del proprietario contiene i tre hotel; gli utenti hotel sono attivi, ciascuno assegnato alla propria struttura con ruolo operativo reception. FPM attivo, barriera aperta, checkout servito pulito e invariato. I tentativi iniziali di navigazione automatizzata si sono fermati su selettori del modulo/conferma e del cambio struttura; corretti nello script operativo locale, senza modificare l’applicazione. Allineati anche i percorsi dei due stemmi nei soli tre nuovi record al prefisso pubblico `storage/`. [Esito](evidenze/demo-tanggo-2026-11-24/esito.json) · [Pagine controllate](evidenze/demo-tanggo-2026-11-24/navigazione-online.json).

## Loghi hotel forniti dal titolare

Caricati i PNG originali, senza alterazioni: `Tanggo_Igea_Marina.png`, `Tanggo_Bellaria.png`, `Tanggo_Rimini.png`, ciascuno nel rispettivo hotel. Aggiornato soltanto `logo`; `logo_citta` conserva lo stemma comunale. Manifest privato esteso ai tre nuovi file per la futura rimozione. Gli stemmi descritti sopra si riferiscono ora ai soli loghi città.

Verifica operativa autenticata positiva su tutti e tre gli hotel: pagina HTTP 200, nome file corrispondente e immagine decodificata dal browser. [Ricevuta](evidenze/demo-tanggo-2026-11-24/loghi-online.json).

## Aggiunta dei soggiorni tassabili di giugno

Aggiunta una schedina per hotel, tre notti nella prima settimana di giugno 2026: Igea Marina 01–04/06 (4,50 €), Bellaria 02–05/06 (4,50 €), Rimini 03–06/06 (7,50 €). Le schedine complessive sono ora dodici, quattro per hotel. Clienti, cataloghi e tariffe invariati; manifest privato aggiornato. [Dettagli e verifiche](ORDINE-ELENCHI-E-DEMO-GIUGNO-2026-11-24.md).

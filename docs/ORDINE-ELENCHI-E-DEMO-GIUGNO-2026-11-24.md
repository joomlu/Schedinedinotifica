# Elenchi prima di Nuovo e demo con imposta — 24 novembre 2026

La navigazione Clienti presenta **Lista → Nuovo**; Schedine presenta **Elenco schedine → Nuova schedina → Bozze**. Le voci della barra laterale mostrano lo stesso ordine e aprono i rispettivi elenchi. Route e moduli restano quelli esistenti, con selezione attiva, filtri, paginazione e bozze conservati. Tre soli template applicativi coinvolti; nessuna logica di salvataggio, calcolo, autorizzazione o invio modificata.

## Tre soggiorni dimostrativi di giugno

Aggiunta online una schedina completa per ciascuna struttura del pacchetto `DEMO-TANGGO-20261124-7E996FCB`, usando un cliente demo adulto già presente e una camera 104. L'anno è il 2026, coerente con le altre date e configurazioni del pacchetto.

| Hotel | Cliente demo | Arrivo | Partenza | Notti imponibili | Tariffa | Totale |
|---|---|---|---|---|---|---|
| Tanggo Igea Marina | Luca Bianchi | 01/06/2026 | 04/06/2026 | 3 | 1,50 € | **4,50 €** |
| Tanggo Bellaria | Davide Ferri | 02/06/2026 | 05/06/2026 | 3 | 1,50 € | **4,50 €** |
| Tanggo Rimini | Alessandro Costa | 03/06/2026 | 06/06/2026 | 3 | 2,50 € | **7,50 €** |

Le nuove schedine sono 536, 537, 538, rispettivamente nei tre hotel. Per mostrare l'imposta aprire **Elenco schedine → Anteprima ricevuta** sulla scheda di giugno. È un'anteprima del calcolo, senza versamento o certificazione di pagamento. Le schedine di novembre/dicembre restano disponibili per il confronto fra soggiorno tassabile e fuori stagione nei due hotel di Bellaria-Igea Marina. Il pacchetto contiene ora **12 schedine, quattro per hotel**, 15 anagrafiche condivise, sei componenti e dodici camere associate ai soggiorni. Nessun nuovo cliente o catalogo creato, nessuna tariffa o configurazione modificata.

Prima dell'aggiunta: checkpoint cifrato nuovo con recupero nativo di 74 tabelle MariaDB in istanza privata senza rete. Inserimento transazionale con collisioni bloccanti, validatori e numeratore esistenti, collegamento allo storico condiviso; calcolo del servizio fiscale verificato contro i tre totali. Manifest privato locale/server esteso alle schedine aggiunte, per la futura rimozione selettiva. Nessun invio reale o modifica dei dati di altre strutture. La numerazione visualizzata negli elenchi segue il normale riordino applicativo preesistente.

## Verifiche e pubblicazione

I tre soggiorni e i totali sono stati prima provati nella VM ufficiale isolata. Riprovati anche i dati demo originari, nove tracciati Questura al relativo giorno di arrivo simulato, tre XML ISTAT e i casi di fusione/annullamento/omonimia. Identità runtime, 23 rifiuti di override e build/GEO positivi.

Il recupero di codice e viste compilate è stato provato nella VM su quattro interruzioni sintetiche (file parziale, Git aggiornato, cache svuotata, cache riscritta) e nel passaggio completo. Servizi e sonde simulati in queste prove: non equivalgono al checkpoint finale del rilascio.

Le prime esecuzioni dei vecchi test si sono fermate su presupposti superati: selezione automatica del tenant, sottomenu visibile nel menu compatto e gradiente identico fra pulsanti e sidebar. Corrette soltanto le prove: selezione esplicita, apertura del menu effettivo, destinazione degli elenchi e confronto del pastello con i valori CSS già presenti. Nessun CSS, permesso o comportamento gestionale modificato. I risultati negativi sono conservati nelle evidenze. Ulteriori riprove hanno usato la chiusura prevista con Escape sul telefono (il menu aperto copre il pulsante hamburger) e sostituito nel test Clienti l’attesa intermittente di rete inattiva con DOM e riscontro effettivo del modulo/bozza. Nessun incremento dei timeout o modifica applicativa per far passare le prove.

**Candidato qualificato:** nove scenari finali positivi a 1440/768/390: tre controlli dello stile in R3, tre di Schedine in R4 e tre di Clienti in R5. Verificati ordine, accessi agli elenchi dal menu, tastiera, filtri/paginazione, ripresa bozze, zero scritture da navigazione e assenza di overflow. Sei impronte di template/test identiche fra Mac e VM. **Pubblicato online** lo stesso candidato `edb53d1fa619d7236f77fea6c3fb704abc68eab0`, [PR57 integrata](https://github.com/joomlu/Schedinedinotifica/pull/57). Checkpoint finale del codice Git e delle viste compilate verificato, guardiano systemd qualificato, svuotata la sola cache Blade e riavviato il FPM dedicato. Ambiente, cache configurazione/eventi/route/pacchetti/servizi invariati, nessuna migrazione. Server pulito, FPM attivo e barriera aperta. **39 pagine operative HTTP 200** sui soli hotel demo: le quattro pagine Clienti/Schedine a tre larghezze per hotel più tre anteprime d’imposta, senza overflow o errori JavaScript. Anteprime mostrano correttamente i tre totali. Nessuna migrazione o modifica delle dipendenze necessaria. Lavoro documentale preesistente fuori perimetro preservato. **BASELINE IN AUDIT invariata.**

[Esito finale](evidenze/ordine-elenchi-demo-giugno-2026-11-24/esito.json) · [Navigazione online](evidenze/ordine-elenchi-demo-giugno-2026-11-24/navigazione-online.json). Il successivo consuntivo documentale conserva lo stesso codice applicativo verificato.

Controllo successivo: 24 sonde HTTPS su home/login, certificati verificati e risposte HTTP 200 in tutti i campioni. [Sonde](evidenze/ordine-elenchi-demo-giugno-2026-11-24/sonde-https.json).

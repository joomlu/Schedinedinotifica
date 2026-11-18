# Conferma persistente della condivisione clienti — 18 novembre 2026

## Richiesta e riscontro

Il proprietario ha già unito Hotel Amalfi, Hotel Dream e Hotel Gori e chiede una conferma visibile della configurazione. Verifica produttiva in sola lettura: tre strutture del proprietario 8 nello stesso gruppo 1, attivo; operazione di configurazione registrata. Zero schede locali e zero persone condivise al momento del controllo. Nessuna importazione è stata eseguita e non serve ripetere l’unione.

## Modifica circoscritta

Nella scheda di ogni gruppo attivo, riepilogo persistente «Condivisione attiva», nomi degli hotel partecipanti e numero di clienti condivisi. Se il conteggio è zero, spiegazione esplicita: gruppo configurato, archivio ancora vuoto. Restano il messaggio temporaneo di conferma e le operazioni esistenti. Stessa grafica e classi del progetto, nessun cambio a controller, servizi, schema, autorizzazioni o dati.

Spec browser aggiornato per distinguere il messaggio temporaneo dal riepilogo persistente e verificarne i partecipanti dopo il ricaricamento. Caso PHP per archivio vuoto e poi popolato senza riconfigurare il gruppo. Collaudo nella sola VM ufficiale positivo: 30 test PHP e 132 asserzioni, browser a 1440/768/390 px con ricaricamento, isolamento e separazione; build/GEO e 23 rifiuti di override positivi. Tre impronte Mac/VM confrontate. Rilascio pendente. [Esito](evidenze/conferma-condivisione-2026-11-18/esito.json).

BASELINE IN AUDIT invariata.

# Verbale Di Consegna
Progetto: `Schedine di Notifica`  
Data: `18/03/2026`

## Oggetto
Chiusura tecnica del sistema e consegna del software in stato operativo.

## Premessa
Nel corso delle ultime revisioni il sistema è stato controllato modulo per modulo, con verifica dei circuiti funzionali, coerenza dei dati, regole operative, interfaccia utente, controlli di compilazione, storici, notifiche, esportazioni e flussi telematici.

L’attività ha riguardato in particolare:
- coerenza dei percorsi operativi
- separazione corretta dei circuiti
- verifica dei dati obbligatori
- controllo delle esportazioni
- verifica dei report interni
- allineamento estetico e funzionale della UI
- controllo dei moduli telematici verso enti esterni

## Esito Della Verifica
Il sistema risulta **chiuso a livello software** e **pronto all’uso operativo interno**.

Sono stati verificati e considerati chiusi i seguenti moduli:

### 1. `Schedine`
- gestione completa di schedine ufficiali, bozze, arrivi e web check-in
- salvataggi e passaggi di circuito coerenti
- liste e numerazioni corrette

### 2. `Web Check-in`
- circuito online coerente
- gestione corretta da backoffice
- separazione dal circuito operativo fino a completamento

### 3. `Tassa di soggiorno`
- calcolo coerente
- report mensili completi
- stampa A4
- CSV Comune
- CSV interno
- riepiloghi per schedina e per persona

### 4. `Presenze`
- riepiloghi gestionali affidabili
- conteggi di arrivi, partenze, presenze, italiani e stranieri
- lettura utile per la struttura

### 5. `Calendario`
- compleanni, movimenti e note separati correttamente
- esclusione dei record web dal calendario operativo

### 6. `Cestino`
- eliminazione e ripristino coerenti
- ripristino come elemento attuale
- copertura anche per bozze, arrivi e web check-in

### 7. `Profilo / Gestione operativa`
- ruoli e permessi corretti
- controllo proprietario / reception
- storico attività e accessi ampliato

### 8. `Notifiche`
- centro notifiche operativo
- notifiche automatiche di compleanno per ospiti presenti in struttura
- integrazione corretta con topbar

### 9. `Supporto online`
- separazione da Notify
- apertura ticket
- dialogo struttura / amministrazione
- no letti e assegnazioni visibili

### 10. `Centro assistenza`
- documentazione interna aggiornata
- contenuti coerenti con i moduli reali del sistema
- percorsi operativi chiariti

## Moduli Chiusi Con Riserva Esterna
I seguenti moduli risultano chiusi dal punto di vista software, ma restano subordinati all’attivazione presso gli enti esterni competenti:

### 1. `Questura`
- modulo software chiuso
- esportazione TXT funzionante
- verifica e invio diretto predisposti
- ricevuta predisposta
- tabelle ufficiali gestite
- resta da completare soltanto:
  - caricamento `WSKEY` reale
  - prova reale con `Alloggiati Web`

### 2. `Tabella A Emilia-Romagna`
- modulo software chiuso
- XML corretto
- verifica e invio predisposti
- esito e riepiloghi predisposti
- resta da completare soltanto:
  - caricamento `codice struttura Ross1000`
  - prova reale con il sistema regionale

## Precisazione Importante
Le riserve sopra indicate non costituiscono difetti del software.  
Si tratta di attività dipendenti da:
- credenziali ufficiali
- codici identificativi rilasciati dagli enti
- validazione finale su servizi esterni

## Stato Finale Del Progetto
Alla data del presente verbale, il software risulta:

- `chiuso a livello di sviluppo`
- `chiuso a livello di circuiti interni`
- `pronto all’uso gestionale`
- `in attesa delle sole attivazioni esterne` per Questura e Tabella A Emilia-Romagna

## Conclusione
Si dichiara pertanto il sistema **formalmente consegnabile e chiuso a livello software**, salvo le integrazioni finali con gli enti esterni che saranno eseguite non appena disponibili le credenziali e i codici ufficiali mancanti.

## Firma Tecnica
Sistema verificato e chiuso in data `18/03/2026`.

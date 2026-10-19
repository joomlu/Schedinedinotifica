# Invio e reinvio Web Check-in — 19 ottobre 2026

**COLLAUDO ISOLATO PASS; pubblicazione e invio reale ancora da verificare. Produzione iniziale `3d78e513528164f8c0faed60584d4b4a6df56e04`, Git iniziale `4fe171048a01391160a35109f08614267a11060c`. Nessun nuovo rilascio o invio reale attestato in questa fase. BASELINE IN AUDIT generale invariata.**

## Problema e riproduzione

L’elenco esponeva Apri richiesta e Prepara invito, senza collegamento visibile all’invio preparato o al reinvio. Il comando effettivo era nella singola comunicazione, con etichetta diversa («Invia email HTML»). Creazione rinviata al dettaglio e conferma cancellata dal controller dell’elenco. Tentativo prenotato sotto lock e trasporto sincrono già presenti: preparazione o apertura esterna non provavano accettazione SMTP. Qualunque eccezione era classificata come incerta, senza distinguere un rifiuto accertato.

Riproduzione HTTP nella VM qualificata sul codice della baseline: nuovo test negativo per assenza dell’azione nell’elenco con invito predisposto. Elenco online osservato in sola lettura: stessa assenza delle azioni. Il test negativo è conservato privatamente, distinto dai cicli di accettazione.

Rita Giorgetti ispezionata esclusivamente in lettura: email e logo validi, configurazione SMTP disponibile; link revocato e date del soggiorno trascorse. La precedente apertura esterna non attesta un invio email. Nessun dato cambiato, nessun messaggio inviato alla persona. Una nuova emissione non proroga la scadenza del soggiorno: i dati originali vanno verificati dall’operatore, senza inventare nuove date o ricreare la richiesta.

## Correzione autorizzata

Creazione → elenco con conferma; nell’elenco e nel dettaglio proiezione dello stato di comunicazione dai tentativi esistenti, ultimo invio accettato con data/ora/destinatario, preparazione/modifica, invio, reinvio, copia del link valido e blocchi spiegati. Nessuna nuova colonna, tabella o seconda fonte persistita degli stati. Compilazione mostrata separatamente: una sola visita non dimostra inserimento di dati; filtri coerenti con i dati salvati. Compilato e check-in effettuato restano distinti.

Anteprima con destinatario, oggetto, HTML/logo e pulsante, conferma del destinatario e comando «Invia Web Check-in». Personalizzazioni originali conservate cifrate nel tentativo per la nuova preparazione; per inviti precedenti si riutilizza il testo personalizzato disponibile, risolvendo nuovamente il link secondo le regole vigenti. Reinvio volontario tramite nuova preparazione e nuovo tentativo, senza riscrivere gli esiti precedenti.

Il trasporto SMTPS cURL distingue rifiuti SMTP espliciti e fallimenti prima del trasferimento del messaggio dagli esiti incerti dopo il trasferimento. Codici diagnostici limitati e messaggi comprensibili, senza eccezioni SMTP, credenziali o link nei log. Dopo esito incerto serve verifica esplicita prima di una nuova preparazione; nessun reinvio automatico. Il lock e la prenotazione del singolo tentativo impediscono duplicati dello stesso POST. Invio sincrono, nessuna nuova coda o worker richiesto. «Inviato» indica soltanto accettazione del trasporto, mai ricezione o lettura.

Route esistenti conservate: elenco `/web-checkin`; editor invito `/web-checkin/{id}/comunicazioni`; anteprima e invio sulla comunicazione `/web-checkin/{id}/comunicazioni/{communication}`, POST finale con suffisso `/invia`. Nell’elenco «Invia Web Check-in» apre la verifica finale; «Reinvia Web Check-in» apre la personalizzazione e una nuova anteprima. Blocchi di destinatario/date/revoca/dati cambiati/logo/configurazione spiegati con accessi alla richiesta o alla struttura; configurazione del servizio affidata all’amministratore.

Cestino, relazioni, recupero e conversione transazionale nello Storico conservati. Nessuna modifica ai dati reali, SMTP/DNS, credenziali, dipendenze o controlli C5. Rischio ALTO circoscritto alla comunicazione e alle regressioni del circuito; rollback coordinato tramite nuovi PREPARE/S0 da verificare prima del rilascio.

## Verifiche e pubblicazione

Ciclo conclusivo R4: **263 test PHP / 3.074 asserzioni e 21 prove browser a 1440/768/390 PASS**, 17 guardie e 23 rifiuti di override, build/GEO PASS. [Sigilli](evidenze/web-checkin-invio-2026-10-19/verifica.json). R1 non superato per mock Mail e ordine delle fixture; R2 PHP positivo ma browser non superato per date desktop a capo e conferma di creazione non chiusa nella prova; R3 PHP positivo ma aspettativa obsoleta sul caso legacy con dati già presenti. Diagnostici conservati, suite non sommate, nessun controllo rimosso. R4 include esiti/avvisi SMTP, doppio POST, rifiuto e nuovo tentativo, personalizzazione/reinvio, permessi/tenancy, link e compilazione, conversione/Storico e Cestino. Prove soltanto nella VM qualificata; nessun test Laravel sul Mac o in produzione. SMTP in memoria e TLS sintetico di loopback non sono consegne reali. Nuovi PREPARE/S0, recuperi indipendenti, C5, invio controllato reale, verifica Gmail/HTML, QA online/pulizia e monitor900s ancora pendenti. Nessun completamento anticipato.

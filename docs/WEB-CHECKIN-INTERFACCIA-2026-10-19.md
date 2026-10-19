# Riorganizzazione dell’interfaccia Web Check-in — 19 ottobre 2026

**Implementazione e collaudo isolato PASS. Codice `d17687c702a4ca438857ef56a1176d2fc9fd6d89` pubblicato sul ramo GitHub `codex/web-checkin-interfaccia-2026-10-19`. Produzione ancora invariata; capacità recuperata, nuova procedura C5 da eseguire. BASELINE IN AUDIT invariata.** [Verifica](evidenze/web-checkin-interfaccia-2026-10-19/verifica.json), [capacità](evidenze/web-checkin-interfaccia-2026-10-19/capienza.json).

## Organizzazione e percorso

L’elenco espone **Modifica richiesta**, **Anteprima e invio** e **Azioni**. Comunicazione, compilazione, validità del collegamento e ultimo invio restano distinti; spiegazioni estese e dettagli SMTP sono raccolti in Dettagli. I comandi hanno altezza minima uniforme e disposizione adattata allo spazio disponibile.

Modifica richiesta contiene prenotazione, referente, contatti, date, quantità e note interne. Adulti e bambini conservano le validazioni esistenti; il messaggio ospite precedentemente registrato è conservato. Salva mantiene il comportamento di aggiornamento; il browser avvisa prima di lasciare modifiche non salvate.

Anteprima e invio apre **1. Prepara invito**, quindi **2. Anteprima e conferma**, con l’HTML effettivo, destinatario e oggetto. Torna a modifica recupera testo e modello; Conferma e invia mantiene la conferma del destinatario e l’idempotenza. Il solo pulsante Visualizza anteprima evita la conferma CRUD generica: nessun invio automatico. Impronta, scadenza, revoca, doppio POST, esiti incerti e distinzione tra accettazione del trasporto e consegna restano invariati. Anteprima modulo ospite usa il percorso riservato esistente, con campi disabilitati e senza registrare visite. Il logo si configura in una nuova scheda, conservando il percorso corrente.

Azioni apre pagine distinte per ospiti, link, check-in e cestino, attraverso `/web-checkin/{id}/attivita/{attivita}`. Lo storico comunicazioni usa `/web-checkin/{id}/comunicazioni?storico=1`, senza compositore. La gestione link distingue non emesso, valido, scaduto e revocato, consente la copia quando valido e conferma l’invalidazione dei precedenti prima della nuova emissione. Nessuna email viene inviata generando il link.

Il check-in mostra riepilogo, dati ospiti, verifiche pendenti, camera/letti e conferma finale. Riutilizza conversione transazionale e versione esistenti; dopo la conversione apre la revisione nello Storico. Compilazione completata e check-in operativo restano separati. Cestino e ripristino mantengono le protezioni precedenti.

Nessuna migrazione o modifica SMTP, DNS, credenziali e regole estranee allo scope. Nessuna scrittura o email a Rita Giorgetti o altri ospiti reali.

## Collaudi ed evidenze

| Esecuzione | Esito |
|---|---|
| R8, regressioni ampliate | 264 test / 3.089 asserzioni PASS |
| R10, selezione Web Check-in sul contenuto finale | 66 test / 643 asserzioni PASS |
| R12, regressione ampliata | 264 test / 3.089 asserzioni, 21 scenari storici browser PASS |
| R14, correzione avviso modifiche non salvate | 66 test / 643 asserzioni, 3 percorsi completi browser PASS |
| R10, browser | 3 percorsi completi a 1440, 768, 390 px; 30 schermate PASS |
| Guardie complete | 17 PASS, inclusi i due controlli del proxy |
| Ogni runtime conclusivo | Identità DB/HTTP, riconnessione e 23 override rifiutati; build/GEO PASS |

Le esecuzioni sovrapposte non sono sommate. La selezione ampliata include invio/reinvio e trasporto SMTPS TLS sintetico, conversione concorrente/idempotente, tenancy, Cestino, schedine, clienti e Arrivi. Gli ultimi browser verificano anche menu da tastiera, avviso beforeunload, HTML personalizzato, modulo disabilitato, ritorno senza perdere il testo e separazione delle attività. Le schermate reali sono state ispezionate: nessun overflow o sovrapposizione rilevata. [Elenco desktop](evidenze/web-checkin-interfaccia-2026-10-19/elenco-1440.png), [tablet](evidenze/web-checkin-interfaccia-2026-10-19/elenco-768.png), [mobile](evidenze/web-checkin-interfaccia-2026-10-19/elenco-390.png), [email mobile](evidenze/web-checkin-interfaccia-2026-10-19/anteprima-390.png), [modulo riservato](evidenze/web-checkin-interfaccia-2026-10-19/modulo-ospite-390.png).

96 sorgenti corrispondono byte per byte tra commit, checkout e clone collaudato. Runtime nella VM qualificata, namespace senza rete esterna, MariaDB 11.8.9 e HTTP effimeri, fixture sintetiche, cleanup automatico concluso. Nessun test Laravel sul Mac o in produzione; nessun invio reale. L’avviso di trasporto non configurato nelle schermate appartiene al runtime isolato e non descrive la configurazione produttiva.

Il checkout quotidiano VM e il relativo snapshot sono divergenti: sincronizzazione ufficiale fermata senza sovrascritture. Usato un nuovo clone CoW del laboratorio precedente, conservando gli originali. R1 fu interrotto dalla guardia PHP host incompatibile; ripristinati i tre template ufficiali parametrizzati sul clone, senza disabilitare controlli. R2/R3 evidenziarono aspettative del vecchio percorso e un dettaglio SMTP mancante, corretto. R4 rilevò direttive Blade adiacenti, corrette. R5 fu fermato ordinatamente per il dialogo iniziale non gestito; R6 fallì sul doppio clic del test durante la chiusura del dialogo; R7 sulla conferma CRUD precedente all’anteprima. Diagnostici privati conservati. Timeout e retry non aumentati; R8, R9 e R10 positivi nei rispettivi ambiti.

## Pubblicazione e nuovo preflight

GitHub iniziale e checkout coincidevano su `e84627f4f8b036625815a70ecd767d10a059122c`. Il nuovo ramo candidato contiene il codice verificato. Il tentativo di aggiornare anche main è stato rifiutato dalla revisione automatica, che ha ritenuto quel ramo condiviso fuori dall’autorizzazione disponibile; pubblicato soltanto il candidato, senza aggirare il rifiuto.

Il primo preflight era fermo prima PREPARE: VM 1.969.762.304 byte liberi contro minimo prudenziale 2.418.061.312, riserva 1 GiB invariata. Il successivo audit dei recuperi conclusi ha verificato dump sorgenti e riesportati identici, sigilli delle cache e assenza di processi utilizzatori. Ritirate soltanto 12 cache datadir ricostruibili: libero durante il collaudo da 1.560.936.448 a 3.979.030.528 byte. SQL originali, riesportazioni, cifrati, backup, chiavi, sorgenti ed evidenze conservati; nessun ampliamento o arresto VM.

Nuova lettura Mac 20.643.999.744 byte, Extra 601.075.138.560, UUID Extra conforme. Sono misure preliminari: serve ancora il preflight integrale, seguito da nuovi PREPARE/S0, recuperi indipendenti, QA online e monitor finali. La produzione è stata riletta sullo SHA `78e889759b0838f35267496527c42024aa18bd00`; nessun nuovo deploy attribuito a questo candidato.

R11: 264/3089 e nuovi tre percorsi PASS, regressione storica browser NONPASS per conferme e selettori delle vecchie pagine; adattamenti completati, R12 PHP264/3089 e21browser storici PASS; tre nuovi percorsi NONPASS per avviso dopo submit invalido. Corretto il ripristino del flag dopo tutti i gestori dell’evento, R13 e R14 finali66/643 e3percorsi completi PASS. Corretto anche il ritorno dopo revisione alla pagina ospiti e verificato il mantenimento dell’avviso modifiche non salvate dopo submit invalido. Timeout e retry invariati.

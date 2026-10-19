# Riorganizzazione dell’interfaccia Web Check-in — 19 ottobre 2026

**Implementazione e collaudo isolato PASS. Codice `aca59002a945d7c3f20d678431bcf8c8e2db90fe` pubblicato sul ramo GitHub `codex/web-checkin-interfaccia-2026-10-19`. Produzione non aggiornata: STOP capienza prima del PREPARE. BASELINE IN AUDIT invariata.** [Verifica](evidenze/web-checkin-interfaccia-2026-10-19/verifica.json), [capacità](evidenze/web-checkin-interfaccia-2026-10-19/capienza.json).

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
| R10, browser | 3 percorsi completi a 1440, 768, 390 px; 30 schermate PASS |
| Guardie complete | 17 PASS, inclusi i due controlli del proxy |
| Ogni runtime conclusivo | Identità DB/HTTP, riconnessione e 23 override rifiutati; build/GEO PASS |

Le esecuzioni sovrapposte non sono sommate. La selezione ampliata include invio/reinvio e trasporto SMTPS TLS sintetico, conversione concorrente/idempotente, tenancy, Cestino, schedine, clienti e Arrivi. Gli ultimi browser verificano anche menu da tastiera, avviso beforeunload, HTML personalizzato, modulo disabilitato, ritorno senza perdere il testo e separazione delle attività. Le schermate reali sono state ispezionate: nessun overflow o sovrapposizione rilevata. [Elenco desktop](evidenze/web-checkin-interfaccia-2026-10-19/elenco-1440.png), [tablet](evidenze/web-checkin-interfaccia-2026-10-19/elenco-768.png), [mobile](evidenze/web-checkin-interfaccia-2026-10-19/elenco-390.png), [email mobile](evidenze/web-checkin-interfaccia-2026-10-19/anteprima-390.png), [modulo riservato](evidenze/web-checkin-interfaccia-2026-10-19/modulo-ospite-390.png).

96 sorgenti corrispondono byte per byte tra commit, checkout e clone collaudato. Runtime nella VM qualificata, namespace senza rete esterna, MariaDB 11.8.9 e HTTP effimeri, fixture sintetiche, cleanup automatico concluso. Nessun test Laravel sul Mac o in produzione; nessun invio reale. L’avviso di trasporto non configurato nelle schermate appartiene al runtime isolato e non descrive la configurazione produttiva.

Il checkout quotidiano VM e il relativo snapshot sono divergenti: sincronizzazione ufficiale fermata senza sovrascritture. Usato un nuovo clone CoW del laboratorio precedente, conservando gli originali. R1 fu interrotto dalla guardia PHP host incompatibile; ripristinati i tre template ufficiali parametrizzati sul clone, senza disabilitare controlli. R2/R3 evidenziarono aspettative del vecchio percorso e un dettaglio SMTP mancante, corretto. R4 rilevò direttive Blade adiacenti, corrette. R5 fu fermato ordinatamente per il dialogo iniziale non gestito; R6 fallì sul doppio clic del test durante la chiusura del dialogo; R7 sulla conferma CRUD precedente all’anteprima. Diagnostici privati conservati. Timeout e retry non aumentati; R8, R9 e R10 positivi nei rispettivi ambiti.

## Pubblicazione e unico impedimento operativo

GitHub iniziale e checkout coincidevano su `e84627f4f8b036625815a70ecd767d10a059122c`. Il nuovo ramo candidato contiene il codice verificato. Il tentativo di aggiornare anche main è stato rifiutato dalla revisione automatica, che ha ritenuto quel ramo condiviso fuori dall’autorizzazione disponibile; pubblicato soltanto il candidato, senza aggirare il rifiuto.

Due letture dopo il cleanup attestano **1.969.762.304 byte liberi VM**, contro il minimo prudenziale conservato dell’ultima C5 di **2.418.061.312 byte**: deficit minimo **448.299.008 byte, circa 428 MiB**. La riserva di **1 GiB** resta invariata. Cache utente quasi vuote; anche il libero osservato prima di creare il clone, 2.076.962.816 byte, restava sotto quel minimo. Nessuna cancellazione di backup, chiavi, originali o lavoro estraneo; nessun ampliamento o arresto della VM.

Mac **23.187.931.136 byte**, Extra **601.074.421.760 byte**, UUID Extra `488ACB8D-6B99-49AE-8A3C-175FD66F5466`. Misure datate, non certificazione del preflight completo né del nuovo picco. La produzione è stata riletta sullo SHA `78e889759b0838f35267496527c42024aa18bd00`; nessun nuovo PREPARE, T0, S0, deploy, DDL, fixture online o monitor attribuito a questo candidato.

Resta da risolvere la capacità VM con margine, poi ripetere il preflight completo e l’intera procedura C5 con nuovi backup/recuperi, QA online, pulizia e monitor finali. I controlli precedenti non sostituiscono quelli della futura finestra.

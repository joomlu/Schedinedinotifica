# Riorganizzazione dell’interfaccia Web Check-in — 19 ottobre 2026

**Applicativo `42deeaf399910d8e6ac913348306756a9c566277` online; C5, QA, pulizia e monitor finali PASS. Codice `d17687c702a4ca438857ef56a1176d2fc9fd6d89`. BASELINE IN AUDIT invariata.** [Ricevuta](evidenze/web-checkin-interfaccia-2026-10-19/rilascio.json), [verifica](evidenze/web-checkin-interfaccia-2026-10-19/verifica.json), [QA online](evidenze/web-checkin-interfaccia-2026-10-19/qa-online.json).

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

La nuova capacità ha consentito il preflight completo: VM 4.438.966.272 byte contro 3.725.107.200 stimati, Mac 20.960.686.080, Extra 601.075.138.560. Dopo il PREPARE integro: VM 3.095.830.528 contro 2.420.244.480 residui stimati; nuova misura del recupero 956.452.864 e ingresso cifrato 373.272.576 byte. Identità Extra/APFS/scrittura e riserva 1 GiB PASS, soglie 8/40 GiB e budget 1800/600/1379/421 invariati.

R11: PHP 264/3089 e nuovi tre percorsi PASS, regressione storica NONPASS per selettori/conferme; adattata. R12: PHP 264/3089 e 21 scenari storici PASS; tre nuovi percorsi rilevano l’avviso perso dopo submit invalido. Ripristino del flag rinviato dopo tutti i gestori dell’evento; R13 e R14 finali: 66/643 e 3 percorsi completi PASS. Allineato anche il messaggio di creazione ai nomi effettivi dei comandi. Nessun timeout o retry aumentato.

## Rilascio e verifica conclusiva

Un solo nuovo RUN C5 sul `42deeaf399910d8e6ac913348306756a9c566277`, baseline `78e889759b0838f35267496527c42024aa18bd00`. Nuovi PREPARE e S0 cifrati, recuperi indipendenti completi in VM senza rete, chiave separata e sei cifrati su Extra autenticati/decifrati integralmente. Prima del RUN, 55 campioni di quiete PASS. S0 185.444 s; riapertura/accettazione 272.726 s. Nessuna DDL; 159 migrazioni / 66 tabelle, 155 sorgenti e file protetti e 96 sorgenti della task conformi. DB identico byte per byte a S0 prima del QA; 75 elementi persistenti, ambiente, chiave, upload e loghi conservati. Guardiano concluso/disarmato/inattivo/disabilitato.

Online: 6 percorsi completi singolo/famiglia con bambino/gruppo,3 percorsi della nuova interfaccia a 1440/768/390 px, clienti/Bozze/tenancy e 24 navigazioni, accessi scaduti/revocati e rigenerazione esplicita, modelli personalizzati PASS. Schermate effettive ispezionate, nessun overflow o sovrapposizione. [Elenco mobile online](evidenze/web-checkin-interfaccia-2026-10-19/online-elenco-390.png), [anteprima](evidenze/web-checkin-interfaccia-2026-10-19/online-anteprima-390.png), [check-in tablet](evidenze/web-checkin-interfaccia-2026-10-19/online-check-in-768.png). Nessuna email reale o scrittura su ospiti reali.

Adattatore online inizialmente NONPASS per documento sintetico assente dal catalogo produttivo, seconda creazione sulla stessa scheda precedente correttamente rifiutata con 404 e conferma esplicita nuova emissione non gestita. Ripresi la richiesta originale e i dialoghi, adattato solo il dato sintetico a PASSAPORTO ORDINARIO; prove successive PASS. Diagnostici preservati. Rettificato il solo metadato SHA di un resoconto che leggeva il vecchio kit locale inattivo; HEAD produttivo riletto, originale conservato, esiti e tempi invariati.

Fixture rimosse; HMAC di tutte le righe delle 66 tabelle identici al pre-QA; 75 elementi persistenti identici a S0. Nessun restore/reset del DB o dei contatori. Monitor avviati dopo pulizia: server 917.984s/31 campioni / 279 sonde; Mac 923.432s/31 campioni / 93 richieste con DNS ordinario/TLS PASS. Zero nuovi errori applicativi e FPM dall’intero T0.

Risorse finali e identità VM/Extra nella ricevuta; disco VM 36 GiB, SELinux e daily conservati. Nessun pendente nello scope verificato. Audit generale BASELINE IN AUDIT, DKIM/anomalieDNS storiche e aggiornamento di main restano distinti; il rifiuto automatico del push di main non è stato aggirato. Il commit documentale conclusivo segue il candidato applicativo online e non costituisce un ulteriore deploy.

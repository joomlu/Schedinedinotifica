# Web Check-in — revisione e chiusura del rilascio, 22 ottobre 2026

## Stato: qualificato nella VM, nuova pubblicazione pendente

BASELINE IN AUDIT invariata. La nuova attività ha verificato le PR #5 e #6 integrate, main iniziale dba7c54641fd0cb375f4f7f5e1927bb1e32dfa60 e applicativo realmente online f414900b9626240dc34f1de0a449615b66d7413a. Riletti GitHub, SSH,170digest, sonda FPM autenticata e nove sonde HTTPS. Il lavoro locale preesistente è conservato nel checkout originario; le modifiche di questa attività sono nel worktree dedicato.

## Difetto riprodotto e correzione

Dopo aver rimosso l’ultimo Componente, la nuova riga ereditava la sua data di nascita: il reset svuotava input.value ma manteneva data-default-date, il corrispondente attributo legacy e lo stato flatpickr. La reinizializzazione riproponeva2000-01-01. Il pulsante Aggiungi funzionava: la sola verifica del pulsante non intercettava il dato personale residuo.

La nuova ispezione visiva online alle tre larghezze ha rilevato il difetto sulla versione f414900b. Aggiunta una regressione prima della correzione: V3-repro nella VM ufficiale NONPASS, atteso vuoto e ricevuto2000-01-01; primo caso fallito e cinque non eseguiti per lo stop invariato. [Diagnostico](evidenze/web-revisione-chiusura-2026-10-22/NONPASS-V3.log).

Sei righe applicative nel reset canonico dei Componenti svuotano ora metadati iniziali e istanza del calendario. Non cambiano i valori delle altre persone, il soggiorno, le validazioni, i servizi, lo schema o gli export. Sei righe rafforzano le tre prove browser: rimozione ultimo, aggiunta, valore canonico vuoto, campo visibile vuoto e nessuna data selezionata. Restano i controlli di geometria, interazione, demo e ripristino.

## Qualificazione ufficiale

Collaudo isolato nella sola VM schedine-sviluppo-interno, namespace senza rete esterna, database/storage/sessioni effimeri e posta intercettata. Nessuna suite sul Mac e nessun riuso del database operativo come fixture del runtime isolato. La QA online dal browser della VM e gli script autorizzati di predisposizione/pulizia delle sole strutture sintetiche sono trattati separatamente.

- V4 definitivo:355testPHP/3624asserzioni, zero fallimenti/errori/omissioni; nove prove browser a1440/768/390 superate, inclusa la nuova regressione.
- V5:24prove browser storiche superate sullo stesso codice. Totale33scenari distinti,113sorgenti identici. Le ripetizioni di base V1/V2 non si sommano.
- Sei regressioni PHP originarie PR4 presenti nel JUnit V4. Suite interessate di Schedina, clienti, Componenti, Web Check-in, concorrenza e Questura/ISTAT comprese.
- Diciassette guardie dirette e23override rifiutati prima delle migrazioni in ogni runtime. BuildVite/GEO immutabile e renderingBlade delle viste interessate superati. Nessun timeout, asserzione, validazione o guardia indebolito.
- Invio confermato e HTML effettivamente passato al trasporto controllati solo nel trasporto intercettato della VM: anteprima uguale salvo riferimento CID del logo e CRLF. Nessuna nuova email reale.

[Qualificazione](evidenze/web-revisione-chiusura-2026-10-22/verifica.json), [comandi esatti](evidenze/web-revisione-chiusura-2026-10-22/comandi-collaudo.txt), [JUnit](evidenze/web-revisione-chiusura-2026-10-22/phpunit-V4.xml), [V4](evidenze/web-revisione-chiusura-2026-10-22/V4.log), [V5](evidenze/web-revisione-chiusura-2026-10-22/V5.log).

## Diagnostici e limiti effettivi

La qualificazione iniziale V1/V2 era positiva ma non controllava la data della riga riaggiunta. La QA di base ha concluso tre anteprima, sei circuito e sei HTML; è stata sospesa ordinatamente tra scenari per il difetto, con codice di coordinamento75, senza interrompere browser. I risultati non sostituiscono la nuova QA finale. Tutte le sole fixture di base sono state rimosse: HMAC delle righe preesistenti delle66tabelle identici e92persistenti originali identici, senza restore o reset AUTO_INCREMENT.

L’invocazione aggregata iniziale delle guardie non importava database_policy dal package: diagnostico conservato, poi17prove eseguite direttamente secondo i file e superate senza cambiare i sorgenti. Un secondo trasferimento della fixture era stato rifiutato da O_EXCL perché il primo l’aveva già inclusa: verificata identità byte per byte, nessuna sovrascrittura.

Restano una deprecazione PHPUnit nei metadati doc-comment di IstatConformitaTest e un SyntaxWarning Python nel test di policy. Sono limiti degli strumenti attuali, senza fallimenti delle asserzioni, non difetti funzionali dichiarati risolti. VM PHP8.3.33; produzione risulta aggiornata a8.3.36, con pacchetti installati alle18:25UTC del21ottobre. Il nuovo error_log non tracciato contiene warning di avvio con timestamp coincidente con quell’aggiornamento; binario e51moduli del processo FPM corrente sono stati verificati senza moduli sostituiti ancora in memoria. Nessuna configurazione o credenziale modificata da questa attività. Custodia del diagnostico, nuovo preflight produttivo e verifica dopo deploy ancora pendenti.

Prove con Chromium alle tre larghezze; nessuna certificazione di tutti i programmi di posta o ispezione della casella/MIME in questo incarico. Nessun nuovo invio reale autorizzato, anche alla casella di prova. Richiesta134 e richieste esistenti non reinviate. La precedente ricezione confermata dall’utente resta un fatto storico distinto.

La revisione automatica ha rifiutato la copia integrale del log di produzione sul Mac per potenziale sensibilità del payload e assenza di consenso specifico sulla destinazione. La copia non è stata eseguita. Sono stati raccolti soltanto hash e metadati; custodia sul medesimo server, nella directory dei log e nei backup cifrati C5, ancora da eseguire. La verifica RPM dei pacchetti PHP è pulita; il nuovo piano dovrà sigillare il binario attuale mantenendo invariati utenti, gruppi, funzioni disabilitate e radici ammesse dei profili. Il piano precedente resta integro.

## Rilascio e chiusura ancora da completare

Dopo la revisione finale: PR dedicata e merge del solo candidato qualificato, nuovo C5 con PREPARE/T0/S0 e recuperi indipendenti, custodia Extra,55campioni di quiete e guardiano. Soglie8GiB Mac/40GiB Extra, riservaVM1GiB, budget1800/600/1379/421 invariati. Presidio temporaneo anti-sospensione attivo da chiudere a fine operazione.

Seguono nuova QA online dalla VM sulle sole nuove strutture sintetiche, zero POST di invio, rimozione delle sole fixture con HMAC66/persistenti92, verifica errori e monitor nuovi server e Mac di almeno900secondi/31campioni ciascuno. Gli esiti conclusivi saranno aggiunti dopo l’effettiva esecuzione. Nessun nuovo SHA dichiarato pubblicato in questa fase.

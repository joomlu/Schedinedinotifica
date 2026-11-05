# Web Check-in — revisione e chiusura del rilascio, 22 ottobre 2026

## Stato: VERIFICATO nello scope Web Check-in; PR #7 integrata e SHA online verificato

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

Restano una deprecazione PHPUnit nei metadati doc-comment di IstatConformitaTest e un SyntaxWarning Python nel test di policy. Sono limiti degli strumenti attuali, senza fallimenti delle asserzioni, non difetti funzionali dichiarati risolti. VM PHP8.3.33; produzione risulta aggiornata a8.3.36, con pacchetti installati alle18:25UTC del21ottobre. Il nuovo error_log non tracciato contiene warning di avvio con timestamp coincidente con quell’aggiornamento; binario e51moduli del processo FPM corrente sono stati verificati senza moduli sostituiti ancora in memoria. Nessuna configurazione o credenziale modificata da questa attività. Al momento di quel controllo custodia e nuovo deploy erano pendenti; gli esiti conclusivi sono riportati sotto.

Prove con Chromium alle tre larghezze; nessuna certificazione di tutti i programmi di posta o ispezione della casella/MIME in questo incarico. Nessun nuovo invio reale autorizzato, anche alla casella di prova. Richiesta134 e richieste esistenti non reinviate. La precedente ricezione confermata dall’utente resta un fatto storico distinto.

La revisione automatica ha rifiutato la copia integrale del log di produzione sul Mac per potenziale sensibilità del payload e assenza di consenso specifico sulla destinazione. La copia non è stata eseguita. Sono stati raccolti soltanto hash e metadati; custodia sul medesimo server allora ancora da eseguire, successivamente autorizzata specificamente e completata come documentato sotto. La verifica RPM dei pacchetti PHP è pulita; il nuovo piano ha sigillato il binario attuale mantenendo invariati utenti, gruppi, funzioni disabilitate e radici ammesse dei profili. Il piano precedente resta integro.

## Storico prima del consenso — rilascio ancora da completare

PR#7 e integrazione del solo candidato qualificato sono concluse. Restano il nuovo C5 con PREPARE/T0/S0 e recuperi indipendenti, custodia Extra,55campioni di quiete e guardiano. Soglie8GiB Mac/40GiB Extra, riservaVM1GiB, budget1800/600/1379/421 invariati. Il presidio temporaneo anti-sospensione, attivato durante la preparazione, è stato terminato al blocco.

Seguono nuova QA online dalla VM sulle sole nuove strutture sintetiche, zero POST di invio, rimozione delle sole fixture con HMAC66/persistenti92, verifica errori e monitor nuovi server e Mac di almeno900secondi/31campioni ciascuno. Gli esiti conclusivi saranno aggiunti dopo l’effettiva esecuzione. Nessun nuovo SHA dichiarato pubblicato in questa fase.

## Storico del blocco prima del nuovo C5

PR #7 integrata: `67b4c45f94a349d0b27c7874aacbbe770bc1cf00`, albero identico al candidato `411e4ff4ddeec4881c75fe9065e27161efcf7600` qualificato. Kit nuovo `5a539060a7ead1979366e6489df70edbdf64197b06c5ff10cc8e524f827fcedc`,68file core invariati salvo SHA/tree, depositato inattivo. Produzione riconfermata sul precedente `f414900b9626240dc34f1de0a449615b66d7413a`:170sorgenti,159migrazioni/66tabelle,chiave/env/config/egress/ownership e noveHTTPS conformi. Nessun nuovo PREPARE, T0,S0,RUN, manutenzione o deploy.

La revisione automatica ha rifiutato anche la custodia sullo stesso server: spostare error_log è stato classificato come modifica di un percorso operativo priva di consenso specifico. Entrambi i tentativi sono stati rifiutati prima dell’esecuzione: il file originale è invariato e nessun payload è stato copiato sul Mac. Il controllo read-only mostra4733byte,SHA26186b792a8320495991923c8624dc62f592bbbb7602608fc63bea23e36f8aa4,mtime21ottobre18:25:01UTC,zero descrittori aperti e nessun processo non ispezionabile. CLI usa il percorso relativo error_log; FPM usa /var/log/schedine-release-fpm/error.log.

È stato richiesto il consenso specifico alla sola custodia reversibile da /home/tanggosoftware/repos/schedinedinotifica/error_log a storage/logs/php-avvio-20261021-custodito.log, sullo stesso server, conservando contenuto,inode,permessi e metadati. Questo è il punto concreto mancante: il preflight C5 richiede Git pulito e non viene modificato per accettare il file non tracciato. Non sono stati cambiati ignore,guardrail o protocollo. Dopo il consenso servono ancora il nuovo PREPARE/recupero/Extra,T0/S0/RUN,C5,QA conclusiva,pulizia e monitor900secondi. Nessuno di questi esiti è dichiarato PASS. [Blocco ed evidenze](evidenze/web-revisione-chiusura-2026-10-22/blocco-pubblicazione.json).

Presidi propri conclusi: caffeinatePID19338 terminato e osservatoreVM terminato.1468campioni di capacità, minimo5457686528byte oltre riserva1GiB; non sono il monitor HTTP post-deploy. Il checkout originario resta sul ramo preesistente e HEAD6a652b72d9db91c1e25c855ed34a67054eb3a8d0, con i quattro documenti modificati preesistenti byte-identici. Worktree dedicato e documentazione conservati; dopo il consenso sono necessari nuovi preflight e presidi, senza riutilizzare un vecchio T0 o monitor.


## Consenso specifico, custodia e nuovo C5 concluso

Il 22 ottobre l’utente ha risposto «Autorizzo la custodia sul server». Eseguita la sola rinomina autorizzata di error_log in storage/logs/php-avvio-20261021-custodito.log nella stessa applicazione/server:4.733 byte, SHA 26186b792a8320495991923c8624dc62f592bbbb7602608fc63bea23e36f8aa4, inode/contenuto/UID/GID/permessi/mtime/xattr identici. Il ctime cambia per la rinomina ed è registrato. Zero descrittori aperti, Git pulito. Nessun contenuto integrale del log esportato al Mac; il diagnostico resta nella radice del nuovo S0 cifrato. Integrità e percorso riconfermati dopo C5. I due precedenti rifiuti automatici restano storici: nessun aggiramento e nessuna modifica di ignore o guardrail.

Nuovo preflight FileVault/Extra APFS UUID488ACB8D-6B99-49AE-8A3C-175FD66F5466, VM ufficiale daily945ef553/36GiB/XFS/SELinuxEnforcing. RPM PHP verificato, nuovo profilo sul binario8.3.36 aggiornato dal sistema; ogni altro vincolo del profilo e vecchio piano preservati. Kit di 68 file invariato salvoSHA/tree; piano RUN d17d76ab851c8de63d678b2c4a861e2a425b8ee6263ab60fc429d80061fa86f5. PREPARE, recupero indipendente completo e 55 campioni di quiete superati. Recupero PREPARE: 1.058.205.696 byte; cifrati: 468.578.304 byte; gate di 3.059.625.984 contro 4.409.511.936 byte liberi superato. Soglie Mac 8 GiB / Extra 40 GiB, riserva VM 1 GiB e budget 1800/600/1379/421 invariati. Nessuna eliminazione/condivisione blocchi/ampliamento disco o riavvio per questa ripresa.

PR #7 integrata e pubblicata tramite C5 sullo SHA esatto `67b4c45f94a349d0b27c7874aacbbe770bc1cf00`. Nuovo S0 142,556 s, RUN 235,162 s; decisione CANDIDATO_ACCETTATO, zero DDL, 159 migrazioni / 66 tabelle. Recuperi indipendenti verificati e sei cifrati PREPARE/S0 custoditi su Extra, autenticati e decifrati integralmente nella VM con chiave separata. Guardiano concluso/disarmato/inattivo/disabilitato. Prima della QA il dump DB era byte-identico a S0 e 92 persistenti conformi. SHA servito attestato da Git pulito,170 digest / 113 sorgenti qualificati, sonda FPM autenticata e nove HTTP/TLS/CSS.

## QA online conclusiva, pulizia e monitor

Nuova fixture separata con dati sintetici in due strutture, creata solo dopo C5 e rimossa alla fine. Browser esclusivamente dalla VM ufficiale. Tre anteprime interattive, sei percorsi circuito, sei HTML, tre regressioni clienti/Bozze/tenancy, due accessi e nove GEO/editor/palette: 29 prove più 24 navigazioni responsive, tutte superate a 1440/768/390 px. Zero POST di invio; nessuna email o comunicazione WhatsApp reale, né trasmissioni a enti, mai richiesta 134. Esente / tipo alloggiato, calendario, sezioni, ultimo componente/data vuota/riaggiunta e riapertura provati. Demo con HMAC di richieste/schedine/componenti identici, nessuna visita o persistenza. Singolo/famiglia/gruppo, parziale/ripresa/conferma/reception/schedina/storico, duplicati e link scaduti/revocati, isolamentoA/B e note private verificati. GEO Italia↔estero/nascita/residenza/componenti, nomi nazioni e conservazione opzionali; modelli/FormData/riuso su altra richiesta, palette chiara/scura e salvataggio asincrono superati. 50 immagini conservate con SHA. I ritagli dell’iframe mostrano anche la sovrapposizione del footer del contenitore e non rappresentano un’email completa: lo scorrimento fino al fondo e le viste Desktop/Mobile sono verificati dalla prova dedicata isolata, senza usare quei ritagli come prova di leggibilità integrale.

Pulizia esclusivamente delle strutture 57/58 e dei relativi elementi sintetici: HMAC di tutte le righe preesistenti delle 66 tabelle identici, nessun restore/reset AUTO_INCREMENT. 92 persistenti di .env/storageapp/publicimages identici a S0, eccetto il solo mtime delle directory aggiornato da creazione/rimozione loghi. Nessun dato reale sovrascritto.

Primo monitor Mac NONPASS: 18 campioni completi, poi curl 28 / timeout di 15.006 ms al 19º campione sul dominio principale. Le tre sonde successive sono tornate 200 / TLS valido; DNS A 217.174.149.23, nessun AAAA o proxy. Causa non attribuita, non dichiarata risolta. Diagnostici conservati e nuovo monitor completo senza cambiare timeout di 15 s, frequenza o controlli. Il monitor server è distinto e non ha rilevato quel timeout. Nessuna nuova modifica applicativa giustificata dalle verifiche puntuali.

Monitor nuovi dopo pulizia: server 918,971 s / 31 campioni / 279 sonde; Mac 922,470 s / 31 campioni / 93 HTTPS con DNS/TLS ordinari, tutti superati. FPM/chiave/provider/mailer/barriere e blocco di stream_socket_client / disponibilità di cURL SMTPS controllati a ogni campione server. Zero errori Laravel o journal FPM dall’intero inizio del 21 ottobre alle 23:54 Europe/Rome, includendo tutto il nuovo T0. Campionamento capacità distinto: 3.292 campioni, minimo VM 2.437.234.688 byte oltre 1 GiB. Presidi temporanei propri conclusi, nessun cambiamento globale allo standby.

## Chiusura e limiti

**STATO VERIFICATO nello scope del rilascio Web Check-in; BASELINE IN AUDIT generale invariata.** Nessun impedimento aperto per questo rilascio. La ricezione Gmail confermata in precedenza resta storica: nessun nuovo invio è autorizzato o dichiarato ricevuto. Il trasporto è provato nel canale intercettato della VM, senza garanzie su tutti i client di posta. Deprecazione PHPUnit, SyntaxWarning Python, differenza PHP 8.3.33/8.3.36 e audit generale/DNS-DKIM storici e attribuzione del timeout isolato rimangono espliciti, senza essere dichiarati risolti.

Lavoro preesistente nel checkout originario: HEAD 6a652b72 e quattro documenti sporchi byte-identici. Worktree dedicato per codice/test e documentazione. PR #7 e SHA integrato/pubblicato 67b4c45 distinti dal successivo commit solo documentale su main; nessun nuovo deploy per tale consuntivo. Maestro aggiornato. Prossimo passo consigliato: proseguire separatamente l’audit generale secondo il Maestro.

[Ricevuta conclusiva](evidenze/web-revisione-chiusura-2026-10-22/rilascio.json), [QA](evidenze/web-revisione-chiusura-2026-10-22/QA-online.json), [custodie](evidenze/web-revisione-chiusura-2026-10-22/custodia-extra.json), [consenso e diagnostico](evidenze/web-revisione-chiusura-2026-10-22/consenso-custodia-log.json).

CODICE MODIFICATO: SI; TEST MODIFICATI: SI; DOCUMENTAZIONE MODIFICATA: SI; CONFIGURAZIONE MODIFICATA: NO; SCHEMA MODIFICATO: NO; DATI MODIFICATI: SI, solo nuove fixture poi rimosse; TEST ESEGUITI: SI; COMMIT: SI; PUSH: SI; DEPLOY: SI; TRASMISSIONI ESTERNE A OSPITI/ENTI: NO.

# Invio e reinvio Web Check-in — consuntivo del 19 ottobre 2026

**Pubblicato e verificato online: `78e889759b0838f35267496527c42024aa18bd00`. C5, QA, pulizia e monitor finali PASS. BASELINE IN AUDIT generale invariata.** [Ricevuta](evidenze/web-checkin-invio-2026-10-19/rilascio.json), [collaudo](evidenze/web-checkin-invio-2026-10-19/verifica.json), [monitor](evidenze/web-checkin-invio-2026-10-19/monitor.json).

## Problema e correzione

La richiesta veniva creata e l’invito preparato, ma l’elenco non esponeva un percorso evidente per invio e reinvio. Difetto riprodotto sulla baseline nella VM e osservato online in sola lettura; preparare un messaggio non dimostra un invio.

Elenco e dettaglio mostrano Apri richiesta, Prepara/Modifica invito, Invia Web Check-in, Reinvia Web Check-in e Copia link secondo lo stato. Destinatario, oggetto, HTML con logo della struttura e collegamento vigente sono verificabili prima dell’invio, con personalizzazione. Inviato significa soltanto accettato dal trasporto; data/ora/destinatario dell’ultimo invio sono visibili. Invio sincrono, nessuna coda implicita. Rifiuti certi permettono un nuovo tentativo esplicito; esiti incerti richiedono verifica e conferma per evitare duplicati. Doppio POST/idempotenza, impronta dell’anteprima, permessi, tenancy, scadenza e revoca conservati. Le personalizzazioni HTML riprendono il layout; per vecchi messaggi solo testuali è presente il corpo minimo sicuro, con storico originale conservato.

Comunicazione e compilazione restano distinte. La sola visita non implica compilazione in corso: servono dati salvati. Compilato non equivale a check-in effettuato; conferma operativa, Storico cifrato immutabile, Cestino e ripristino mantengono le regole esistenti. Nessuna nuova DDL o configurazione SMTP/DNS.

## Percorso operativo

Menu **Schedine → Web Check-in → Elenco web check-in**, URL `/web-checkin`. Nuovo Web Check-in crea e riporta all’elenco con conferma. Riga → Prepara invito → Prepara e verifica anteprima → Invia Web Check-in; controllare destinatario e confermarlo prima dell’invio effettivo. Per una richiesta inviata: Reinvia Web Check-in → nuova anteprima → invio esplicito. Le richieste esistenti conservano ID e dati. Gli impedimenti sono spiegati con accesso alla correzione.

Rita Giorgetti ispezionata soltanto in lettura: link revocato e soggiorno trascorso. Il blocco è legittimo: prima verificare e correggere richiesta/date/validità del collegamento; non forzare un invio. Nessuna modifica ai suoi dati, nessuna email a ospiti reali.

## Collaudi e invio reale

R5 nella VM qualificata: **263 test PHP / 3.074 asserzioni, 21 prove browser a 1440/768/390 px, 17 guardie e 23 rifiuti di override PASS**, build GEO invariabile. Nessun testLaravel sul Mac o in produzione. R1–R3 e relative diagnosi conservati; risultati non sommati e controlli non rimossi. Errori SMTP e ripetizione provati con TLS sintetico/in memoria, distinti dal reale.

Sul447571f4 tre invii reali alla sola casella controllata autorizzata: nuovo con doppio POST/un messaggio, reinvio e richiesta persistente riaperta. Tutti accettati dal trasporto e daGoogle SMTP, ricevuti nella Posta in arrivo Gmail con HTML reale/logo/pulsante/dati/firma/scadenza. Pulsante ricevuto verificato sulla richiesta corretta; percorso mobile parziale/ripresa→reception→conferma operativa→Storico PASS. MIME Gmail non estratto: osservazioneUI reale distinta dal collaudo MIME isolato. [Evidenze](evidenze/web-checkin-invio-2026-10-19/invii-reali.json).

La successiva ispezione visuale ha rilevato Apri richiesta compresso sul tablet: primo candidato non dichiarato concluso e nessun monitor900 s attribuito a esso. Il correttivo `78e8897` cambia soltantoCSS elenco e controllo browser, lasciandoPHP/SMTP invariati. Nuova QA online PASS: circuito singolo/famiglia con bambino/gruppo, clienti/ricerca/selezione/Bozze,24 navigazioni, isolamento404, link/compilazione/Storico/Cestino e modelli; zero errori JS. Ispezione reale delle tre schermate PASS, azione principale leggibile. [Tablet](evidenze/web-checkin-invio-2026-10-19/elenco-tablet.png). Un campoSHA informativo leggeva il vecchio metadato: originale conservato, attribuzione78e8897 dimostrata dai controlliHEAD prima/dopo la fixture e dalle sonde server; [rettifica](evidenze/web-checkin-invio-2026-10-19/rettifica-metadato-SHA.json).

## C5, recuperi e capacità

Produzione iniziale3d78e513;447571f4 pubblicato con C5 positivo ma QA visuale incompleta. Primo RUN del correttivo `78e8897` fermato per timeout240 s nel recuperoLinux: guardiano riaprì447571f4 senza applicare il candidato oDDL, in262,629 s dal T0. Recupero tardivo non accettato retroattivamente. Diagnosi separata completa PASS in202,885 s entro lo stesso limite; causa puntuale del rallentamento non attribuita. Unità del guardiano concluso verificata inattiva e disabilitata prima della ripresa. Nuovo piano/T0/S0 e55 campioni di quiete, nessuna modifica al pool altrui. Un precedente richiamo anticipato dello scriptquiete si fermò per piano RUN assente, prima di manutenzione; ordine corretto e controllo ripetuto integralmente.

Secondo RUN finale PASS: S0 **161.957s**, RUN **242.559s**, nessuna DDL,159 migrazioni / 66 tabelle; DB byte-identico S0 prima QA,94 sorgenti del circuito e153 sorgenti/protetti complessivi verificati, nove sonde positive, produzioneGit pulita. Entrambi i guardiani conclusi/inattivi/disabilitati. Budget1800/600/1379/421 e limite ricevente240 s invariati.

Extra identificato al montaggio reale `/Volumes/extra`,APFS/UUID488ACB8D-6B99-49AE-8A3C-175FD66F5466,USBesternoPSSDT7 distinto dal Mac, scrittura verificata. Cartella esclusiva, nessun formato o contenuto preesistente cancellato. NuoviPREPARE e S0 cifrati AES256CBC+PBKDF2/HMAC, riletturaSHA/autenticazione/decifratura integrale e recuperi indipendenti. Replica aggiuntiva S0 su Extra dopo RUN, senza fingere che precedesse l’applicazione; chiave root VM0600 separata e mai esportata. Kit di recupero conservato e verificato. Anche S0 del tentativo arrestato è custodito separatamente. [Custodia](evidenze/web-checkin-invio-2026-10-19/custodia.json).

Disco VM36 GiB/XFS37290283008,SELinuxEnforcing e daily945ef553 confermati: crescita già eseguita in precedenza, non ripetuta. Backup VM pre-ampliamento cifrato e chiavi separate preservati. Recuperati soltanto derivati/cache verificati; repliche cifrate temporanee ritirate soltanto dopo verifica delle copie Extra e prova integrale di decifratura; copieCoW conservate con byte/metadati/attributi identici. Nessun SQL/upload/env/chiave o lavoro estraneo eliminato. Misure byte primo riscontro→finale: Mac **12984459264→25772613632**, VM **1684930560→2076852224**, Extra **601887510528→601063342080**. Misure reali in momenti distinti, nessuna attribuzione esclusiva del delta Mac all’intervento. Soglie Mac8 GiB/Extra40 GiB/riserva VM1 GiB rispettate. [Risorse](evidenze/web-checkin-invio-2026-10-19/risorse-finali.json).

## Chiusura

Fixture rimossa, righe preesistenti delle66 tabelle HMAC identiche e75 persistenti invariati, sola eccezione mtime delle directory interessate dai loghi sintetici. Nessunrestore o reset dei contatoriAUTO_INCREMENT. Monitor dopo la pulizia: server **917.952s/31 campioni/279 sonde**, Mac **921.961s/31 campioni/93 richieste**,DNS/TLS ordinari PASS;FPM/SMTP/blocchi verificati a ogni campione. Zero nuovi errori applicativi o journal FPM dall’intero primo T0, incluso il secondo iniziale. Maestro e GitHub aggiornati con queste evidenze; applicativo online78e8897 distinto dal commit documentale conclusivo.

Nessun impedimento generale all’invio nel circuito verificato. Rimane il blocco specifico dei dati/validità della richiestaRita, lasciata intatta. Pendenti separati:DKIM sottodominio, attribuzione anomalie DNS storiche e auditgeneraleBASELINE IN AUDIT; consegna Gmail osservata non equivale a garanzia universale futura.

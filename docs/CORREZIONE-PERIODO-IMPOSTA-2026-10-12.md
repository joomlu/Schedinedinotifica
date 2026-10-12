# Correzione mirata del periodo dell’imposta — 12 ottobre 2026

Stato finale al 13 ottobre 2026: **CORREZIONE E RILASCIO VERIFICATI nello scope sotto indicato**. Progetto **BASELINE IN AUDIT**. Autorizzazione della task: soli periodo/impostazioni Tassa, test, Maestro, commit/push e pubblicazione conservativa mediante procedura esistente; nessun invio enti, dato fittizio produttivo o cambio infrastruttura.

## Causa accertata e provenienza

`TassaDiSoggiornoController::edit` legge il record della struttura da `tassa_di_soggiorno`, senza salvarlo nel ramo automatico. «Valore salvato» arriva da `valoriConfigurazione`, campi `inizio`/`fine` di quel record; non dalle date di apertura. Il confronto e il riquadro fiscale arrivano da `profiloFiscaleBellaria` → `risolviProfiloFiscale`, selezionato per Comune, categoria e data/anno. Il solo profilo certificato attualmente presente nel servizio è quello Bellaria alberghi 2026. Non sono stati aggiunti Comuni, profili, tariffe o anni normativi.

Il calcolo Bellaria risolve già un profilo per ciascuna notte e applica le sue date inclusive, escludendo la notte della partenza. Il difetto era un controllo preliminare: `validaConfigurazioneBellaria` pretendeva che le date del record legacy coincidessero col profilo; `configurazioneAutomatica` trasformava la discordanza in blocco e richiesta di riallineamento. Una vecchia configurazione annuale o con un altro periodo impediva quindi il calcolo stagionale corretto, nonostante la fonte comunale fosse disponibile. L’etichetta «Differente / derivato dal profilo» confondeva differenza informativa e blocco.

Nessuna dipendenza dalle date di apertura è stata trovata nel percorso fiscale esaminato. Non si attribuisce l’origine storica dei valori salvati all’apertura: il codice corrente non lo dimostra. Apertura/chiusura restano usate dai flussi statistici esistenti, estranei alla correzione.

## Modifica minima

Solo servizio fiscale e vista Configurazione: le date legacy non sono più una causa di blocco. Il periodo effettivo viene dal profilo comunale pertinente all’anno/notte; il record originale resta intatto, senza migrazioni o riallineamenti automatici. Anche un record con sole date o date incomplete non sostituisce il profilo. Importi, esenzioni, limiti ed età mantengono i controlli attuali; discordanze economiche/età restano bloccate con il riallineamento esplicito già esistente e le autorizzazioni originali.

La vista indica Comune, anno e «Periodo di applicazione dell’imposta», dal/al; la colonna cita esplicitamente profilo comunale e anno. Spiega che le vecchie date sono conservate e non applicate: non occorre copiarle. La sola discordanza di date non produce avviso di blocco né pulsante obbligatorio di riallineamento. Profilo annuale mancante/ambiguo continua a bloccare: nessun ripiego su apertura o anno precedente. Gli altri Comuni conservano il percorso esistente; non viene inventata una regola universale.

Ricevute/export consolidati e conteggi storici non vengono riscritti. Le elaborazioni correnti riprendono con il profilo pertinente; i controlli sullo storico sono regressioni, non una rielaborazione produttiva. Nessuna modifica a ruoli, permessi, controller, route, schema, importi o altri moduli.

## Verifiche effettive

VM esistente `schedine-sviluppo-interno`, PHP8.3.33/MariaDB11.8.9/Laravel12.69.3, checkout temporaneo, DB effimero senza dati operativi, namespace senza rete, kernel/middleware reali. Comandi:

```sh
python3 scripts/development/ambiente-interno.py stato
python3 scripts/development/ambiente-interno.py sincronizza
python3 scripts/development/ambiente-interno.py test tests/Feature/TassaPeriodoComunaleTest.php tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php tests/Feature/TassaBellariaAnteprimaTest.php tests/Feature/TassaBellariaRiallineamentoTest.php tests/Feature/TassaBellariaCompletenessTest.php tests/Feature/TassaBellariaVersionamentoTest.php tests/Feature/TassaBellariaEtaCsvTest.php
```

Esito finale: **53 prove selezionate, 51 PASS, 1.546 asserzioni, 2 SKIP**. Le due prove immagini richiedono GD, assente: non eseguite e non dichiarate PASS; nessuna installazione per ampliare lo scope. Build asset e GEO immutabile PASS, identità runtime/DB/HTTP e 23 override respinti PASS. Temporanei del singolo test fermati/rimossi.

Prove specifiche: apertura annuale 01/01–31/12 e stagionale 01/03–15/11 con imposta del profilo 01/06–30/09; vecchie date annuali conservate, nessun falso blocco; notti prima/durante/dopo, inclusa 01/06 e 30/09, partenze ai confini e soggiorni attraversanti; periodo da profilo coerente tra vista e anteprima; sole date/date incomplete salvate; profilo2027 assente e soggiorno2026→2027 bloccato; profilo2027 esclusivamente sintetico con diverso periodo 10/04–20/04 e confini/calcolo conformi. Regressioni: importi/cap/età/esenzioni, profili/categorie mancanti, token/CSRF/autorizzazioni/tenant, audit e snapshot storici conservati.

Fallimenti conservati: primo accesso VM negato dal sandbox, ripresa autorizzata; cache Python obsoleta del modulo guardia riportava6ac6 mentre sorgente/Git erano0dd17b4, rimossa soltanto la cache compilata VM e guardie invariate; suite inizialmente `RefreshDatabase` incontrava la migrazione storica `classificazioni.tipologia_struttura_id` (52 errori, nessuna prova fiscale eseguita), sostituito con `DatabaseTransactions` sullo schema effimero corrente previsto, senza cambiare migrazioni. Successivo run52/1539: due errori GD e una fixture2027 che aveva già risolto il controller con il servizio precedente; separata la prova sintetica in una nuova applicazione di test e dichiarati i due SKIP. Nessun difetto produttivo nuovo attribuito a questi errori del collaudo.

Evidenza locale privata: `output/correzione-periodo-imposta/test-isolati-ripresa.log`. Gli esiti non certificano l’intero modulo né nuove norme comunali.

## Git e rilascio

Diff applicativo limitato a servizio e vista; test pertinenti adattati al runtime corrente. Maestro aggiornato insieme alla correzione. Modifiche locali pregresse nei documenti e artefatti non pertinenti preservate ed escluse dallo staging; nessun reset/clean/force.

Produzione attestata prima del rilascio: HEAD8b7908f, Git pulito, precedente decisione CANDIDATO_ACCETTATO, barriera assente. Catena C5 esistente, nessun nuovo laboratorio o cambio infrastruttura. Questo era il preflight iniziale; il rilascio e le verifiche online effettive sono riportati sotto.

Revisione automatica: respinta una proposta di trasferire sigilli produttivi in un nuovo file locale per la verifica dello spazio; azione non eseguita. Alternativa: verifiche dentro la VM sulle copie già custodite, senza nuova esportazione di quel payload. Backup/chiavi/dati originali restano preservati.


## Cronologia del rilascio — prosecuzione 13 ottobre 2026

Commit applicativo **2b1890ddca1e1018758e03af756e972355c0e220**, tree56bba535e6d0a8c3d2be37ca841258c7e6015a05, pubblicato su `origin/main`. Diff cached completo e whitespace controllati; undici soli file pertinenti, nessuna modifica pregressa inclusa. I file applicativi sono soltanto servizio e vista; sette file di test fiscali, consuntivo e voce Maestro completano il commit.

Procedura C5 esistente, kit e99a28e83316dbeb6fc74bdfe69a8bc0b8d7c1028e97b222561b4ceda057cd0c: soli SHA/tree rigenerati rispetto al kit proprietario R3, nessuna nuova logica infrastrutturale. Sonda privata di rilascio dedicata agli esatti SHA baseline8b7908f/candidato2b1890d, firma RSA/nonce/scadenza/chiave originali invariati; originali preservati. Quattro prove di componente, incluse sette casistiche firma/nonce/scadenza/SHA, e sei prove del gate zeroDDL PASS nel namespace esistente senza rete, chiavi esclusivamente sintetiche. Nessun nuovo laboratorio, servizio applicativo, ruolo o permesso esistente.

PREPARE1: prebuild offline PASS, base respinta per inventario mutato durante acquisizione, prima della manutenzione. Il manifest iniziale non viene scritto dalla CLI quando questo gate fallisce; la prima diagnosi che cercava quel manifest è FAIL diagnostico, conservata, non una mutazione. È stata fatta una lettura browser della configurazione prima dello STOP; non è dimostrato che ne sia la causa. Archivio/dump/prebuild originali conservati e causa specifica non attribuita. PREPARE2 in depositi nuovi, evitando ulteriori richieste browser, PASS: sei controlli prebuild, cifratura, due copie/rilettura sul Data interno attestato, ripristino indipendente nella VM con DB/files/metadati/APP_KEY/FPM/TLS/flags, nessuna rete nella prova. FileVault riconfermato attivo.

RUN1 piano989285446f5f6a758b4da8c043b0ccf6e79a70db7553a0952c0769c60d2163ca: S0 fresco fisico verificato, poi fetch del runner respinto. Causa accertata con sola classificazione sul server: mirror sotto directory privata `/root`, repository non accessibile all’UID/GID del runner. Nessun fast-forward del codice candidato né migrazioni/DML del candidato; `app_changed` è l’intento persistente registrato prima di prepare, non prova di installazione completata. Il guardiano ha arrestato il worker e recuperato conservativamente S0: **PASS_GUARDIANO / S0_RECUPERATO, riapertura195,282s, recupero38,175s**. DB/schema/file/config/key/FPM/TLS/egress/assenza query e nove sonde pubbliche PASS, HEAD tornato8b7908f. Il recupero ha materializzato nuovamente filesystem e database tramite la procedura coordinata: nessuna migrazione nuova, stato finale conforme allo S0, non assenza assoluta di scritture SQL del recupero. Il messaggio d’eccezione originale non è nel report finale del worker, terminato dal guardiano prima di persisterlo; non viene inventato un errore runtime catturato. L’inaccessibilità e il rifiuto del fetch sono verificati separatamente.

Revisione automatica ha respinto anche l’esportazione degli stderr produttivi filtrati: azione non eseguita. Alternativa approvata: lettura sul solo server con output di categorie/booleani fissi, nessuna riga grezza o segreto esportato. Questa diagnosi ha identificato il mirror inaccessibile.

Correzione operativa minima per lo stesso candidato: copia del mirror nel bundle di rilascio esistente sotto `/opt`, root-protetta, leggibile e non scrivibile dall’esatto profilo UID1002/GID489 del runner, originale privato preservato. Nessun chmod/chown di antenati o directory esistenti, chiave, ruolo, gruppo o configurazione di servizio modificato. Il primo preflight della copia usava `runuser` col gruppo predefinito1003: FAIL diagnostico, conservato. Verifica con `setpriv` del profilo reale1002/489 PASS; caso negativo/positivo/non-scrivibilità riprodotti in repository esclusivamente sintetici nella VM senza rete. Fetch con comando/ambiente/UID/GID effettivi del runner provato su ricevente Git separato dall’applicazione: PASS, SHA candidato conforme, nessuna rete esterna o scrittura nel checkout applicativo.

RUN2: piano dd3f2e650a8053ba1e4cb17473d9d6ac28777ae9bdca67804e8411a3eb4b72af, stesso kit/candidato; base/prebuild/schema/immutabili riconfermati dopo il recupero, stato precedente custodito sotto lo stesso lock. Nuovo nonce/finestra e S0 fresco, nessuna riutilizzazione del checkpoint finale precedente. **PASS_AGGIORNAMENTO_C5, CANDIDATO_ACCETTATO**: nonce0087ed9527f03bfedfbffb4a21916ebee43d784bec3231ce, S0fresco verificato116,751s, riapertura190,215s,370campioni del monitor della finestra, zeroDDL. Nessun recupero necessario per RUN2.

Capienza senza crescita/ricreazione dell’infrastruttura: rimossi soltanto derivati temporanei di verifiche concluse, dopo confronto contenuti/metadati/inode, HMAC/cifrati/dump e assenza di processi/FD/mount. Prima copiaR3:19.287record verificati, tre derivati,1.209.847.808byte recuperati immediatamente; altri cinque derivati storici943.374.336byte; tre derivati PREPARE2 dopo PASS1.212.977.152byte; tre derivatiS0 RUN1 dopo recuperoPASS1.208.414.208byte. Cifrati originali, chiavi, dump, manifest e diagnostici sempre preservati. Margine VM prima RUN1 9,667GiB, prima RUN2 9,278GiB; valori immediati, non somme teoriche du. Server e Mac attestati capienti.


## Verifica online effettiva e chiusura

HEAD produttivo **2b1890ddca1e1018758e03af756e972355c0e220**, Git pulito, Laravel12.69.3/149migrazioni/64tabelle. Dump dopo rilascio **byte-identico allo S0 finale**;45record persistenti di `.env`, upload/loghi e metadati conformi, hash APP_KEY invariato, sonda originale preservata e dedicata conforme al sigillo. FPM attivo, barriera e manutenzione interne assenti, Questura/ISTATfalse/mailarray. Nove HTTP/TLS/CSS su dominio e alias PASS; ulteriori tre campioni/27sonde in **61,675s PASS**, controllo breve proporzionato, non monitoraggio15minuti. Nessun restore dopo la riapertura.

Browser Chrome, sessione Admin esistente: configurazione2026 PASS, riferimento esplicito al profilo comunale/anno e «Periodo di applicazione dell’imposta — Dal 01/06/2026 al 30/09/2026», vecchia dicitura rimossa, nessuna ricopiatura di date o falso avviso. Selezione2027 tramite il form GET esistente PASS: messaggio leggibile di profilo mancante, nessun periodo2026 riutilizzato, nessun riallineamento disponibile. Soli GET e selezioni di schede, nessun POST/PUT o salvataggio. Tab originale dell’utente preservata.

**Limite online esplicito:** nella struttura corrente accessibile non sono presenti Schedine; l’anteprima richiede una Schedina e non è stata eseguita online. Nessun record fittizio creato per ottenerla. I casi di calcolo, confini, attraversamenti, discordanza legacy e coerenza col profilo sono PASS nel runtime isolato; non sono dichiarati come salvataggi o conteggi provati su dati reali in produzione.

Evidenze private della task: `output/correzione-periodo-imposta/`, log test/componenti, prove mirror, preparazioni/tentativi/recupero, confronto online server, verifica browser e monitor. Cifrati e chiavi restano nelle custodie già previste, senza valori nelle spiegazioni o nel Git. I due rifiuti automatici restano documentati; alternative sicure completate, nessuna approvazione residua richiesta. Tutti i fallimenti sono conservati e non promossi a PASS.

File del commit: `app/Services/TassaDiSoggiornoService.php`, `resources/views/tassa_di_soggiorno/edit.blade.php`; `tests/Feature/TassaPeriodoComunaleTest.php`, `TassaBellariaAnteprimaTest.php`, `TassaBellariaCompletenessTest.php`, `TassaBellariaConfigurazioneAutomaticaTest.php`, `TassaBellariaEtaCsvTest.php`, `TassaBellariaRiallineamentoTest.php`, `TassaBellariaVersionamentoTest.php`; questo consuntivo e `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`. Diff finale applicativo/test conforme al commit pubblicato; modifiche pregresse non pertinenti conservate, escluso lo staging globale.

**Verdetto:** correzione e pubblicazione online VERIFICATE negli ambiti descritti. Rimangono due SKIP GD delle prove immagini, estranee al periodo, e il limite dell’anteprima online senza Schedine. Importi/esenzioni/cap/ruoli/permessi/storico e altri moduli preservati. Stato generale **BASELINE IN AUDIT**, nessuna certificazione globale del software o delle norme comunali. Prossimo passo: nessun’altra mutazione necessaria per questa correzione; eventuale nuovo profilo annuale richiede una task/fonte propria, senza date inventate.

Codice modificato SÌ; test modificati/eseguiti SÌ; documentazione modificata SÌ; configurazione applicativa/servizi/infrastruttura modificata NO; soli piani/strumenti di rilascio rigenerati SÌ; nuove migrazioni o modifiche di dati fiscali/storici NO; ripristino coordinato DB/files S0 in RUN1 SÌ, stato finale preservato; commit/push/deploy SÌ; record fittizi produttivi NO; invii enti/email NO. Il consuntivo finale viene pubblicato in un commit documentale successivo, senza ulteriore deploy applicativo.


Verifica conclusiva dopo i GET browser: secondo dump byte-identico al nuovo S0 e45record persistenti/key/versione/sonde ancora PASS. Entrambi i guardiani della task risultano inattivi/disabilitati; unità e diagnostici conservati. Scheda di verifica chiusa, originale dell’utente intatta.

Ultima pulizia VM:19.908record originali confrontati, cifrati/HMAC/dump conformi e nessun processo/mount; rimossi i tre soli derivati dell’ultimo recupero indipendente concluso. L’asserzione aggiuntiva di9GiB **FAIL** dopo le rimozioni, non una perdita di backup: libero effettivo successivo **8,898GiB**, nessuna estrazione residua. Il consuntivo di recupero byte non è stato scritto dopo l’assertion: nessun recupero quantitativo inventato. Prima di RUN2 la soglia9GiB era PASS9,278; nessuna nuova finestra/test rimane da eseguire, nessuna crescita infrastrutturale o rimozione di cifrati/chiavi/dump per raggiungere9. La prossima operazione richiederà il proprio preflight di capienza. Questo FAIL diagnostico è conservato e non altera il risultato verificato del rilascio/applicazione online.

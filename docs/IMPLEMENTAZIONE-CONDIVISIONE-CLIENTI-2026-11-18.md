# Condivisione clienti — qualifica del candidato locale

18 novembre 2026. **PUBBLICATO E ABILITATO ONLINE; HOTEL DA SCEGLIERE DAL PROPRIETARIO. BASELINE IN AUDIT invariata.**

Il titolare ha autorizzato il completamento della separazione senza pacchetto, le prove di concorrenza/carico/recupero e, dopo esito positivo, un commit locale selettivo su `codex/condivisione-clienti`. Nessun push, deploy, import reale o migrazione Herd/produzione autorizzati. La cronologia precedente è conservata sotto e descrive checkpoint superati.

## Rilascio online completato

Dopo il primo rifiuto automatico, il titolare ha confermato espressamente destinazione GitHub, rami `codex/condivisione-clienti` e `main`, esportazione del commit e rilascio con migrazione/abilitazione su `https://schedinedinotifica.tanggo.software`. Push atomico senza forzatura riuscito; entrambi i rami remoti, HEAD locale corrente e codice servito coincidono a **`c1816e7b79b055c4accfcf54aa46ad9e7e88097e`**. Repository servito pulito.

Installazione sotto guardiano `schedine-clienti-c1816e7.service`, con barriera HTTP 503 verificata prima delle scritture e FPM arrestato nella finestra. Checkpoint fresco cifrato `/var/lib/schedine-release/bases/clienti-c1816e7-wHq8N2sa`: codice verificato tramite clone indipendente, database ripristinato senza rete, nuova DDL applicata sulla copia e recupero delle 66 tabelle verificato con checksum invariati; configurazione cifrata riletta e verificata. La procedura protegge il recupero prima della riapertura e non ripristina automaticamente copie vecchie dopo possibili nuove scritture degli utenti.

Applicata soltanto `2026_11_18_000000_create_customer_sharing_tables`: otto tabelle nuove, database da 66 a 74 tabelle, una sola registrazione nel registro migrazioni. `CUSTOMER_SHARING_ENABLED=true` nella configurazione online; cache di configurazione/rotte rigenerate e viste compilate svuotate; FPM riavviato, sonde FastCGI positive, barriera rimossa. Unità conclusa con successo. Checksum di `clienti`, `schedina` e `componenti` identici prima e dopo l’installazione sotto barriera.

Sonde esterne con TLS valido: home, login e CSS HTTP 200; pagina proprietario HTTP 302 verso login da anonimo. Non effettuato login produttivo di prova, nessun cliente fittizio inserito online e nessuna operazione di condivisione su hotel reali. Alla verifica finale: zero gruppi, zero partecipazioni, zero persone condivise; la funzione è disponibile al proprietario, che deve scegliere gli hotel e confermare dall’interfaccia. I CSV reali non sono stati importati.

[Ricevuta del rilascio](evidenze/condivisione-clienti-2026-11-18/rilascio-online.json) · [registro compresso](evidenze/condivisione-clienti-2026-11-18/rilascio-online.log.gz). Fonte codice invariata rispetto al candidato collaudato. Herd/MySQL originale del Mac e copia quotidiana VM non modificati; l’attivazione riguarda il sito online espressamente autorizzato. Questa sezione supera le indicazioni storiche di funzione disabilitata o pubblicazione pendente conservate sotto.

## Separazione senza pacchetto

La scelta del pacchetto è esplicita e obbligatoria: `0` non crea pacchetti, `1` autorizza lo snapshot scaricabile. Il proprietario deve comunque confermare la separazione. La struttura mantiene le schede del proprio archivio e quelle collegate alle proprie registrazioni, con codici, note, consensi e soggiorni locali; le sole proiezioni di consultazione vengono oscurate senza cancellazione. La partecipazione al gruppo viene rimossa e gli aggiornamenti diventano indipendenti, nella stessa transazione.

Senza consegna non vengono create righe in `customer_sharing_packages` né copiate nel pacchetto le versioni residenziali del gruppo. Le schede locali già condivise mantengono i valori correnti: non si ripristinano automaticamente dati anagrafici precedenti. Fonti e alternative storiche esistenti restano nella custodia del gruppo; non viene creato un nuovo archivio di consegna per il gestore uscente. L’uscita non esegue un trasferimento di proprietà/utenze o dell’intero gestionale fiscale.

Prove HTTP e browser: nessun pacchetto creato, proprie schede conservate, scheda di sola consultazione non più elencata né accessibile per ID, storico limitato all’hotel, archivio degli altri hotel conservato. Richiesta senza scelta esplicita rifiutata senza effetti. La variante con pacchetto resta coperta dai test precedenti nella regressione finale. Nessun ridisegno: nuovo selettore nello stile della pagina candidata, nessun CSS aggiunto.

## Risultati misurati nella VM ufficiale

Selezione finale nella copia privata `/home/collaudo.guest/condivisione-iFAscNiI/repo`, namespace ufficiale, PHP 8.3.36, MariaDB 11.8.9, Node 20.18.2, solo dati sintetici:

| Prova | Esito |
| --- | --- |
| Regressioni, separazione, concorrenza, recupero e carico | 155 test PHP, 1.064 asserzioni, positivi; 77,112 secondi |
| Browser, inclusa uscita senza pacchetto | 1 scenario positivo, 58,8 secondi; 1440/768/390 px |
| Guardie originali | 15 prove positive |
| Attestazione DB/processi/HTTP, riconnessione | positiva; 23 override vietati rifiutati prima delle migrazioni |
| Asset e GEO | build e integrità positive |
| Sorgenti | 30 impronte confrontate; sola differenza attesa dell’avviatore operativo Linux |

I nuovi test multiprocesso usano il medesimo endpoint effimero attestato, senza connessioni operative o rimozione delle protezioni. Due processi caricano la stessa versione prima del via: uno salva, l’altro riceve 409; proiezioni e registro sono coerenti col vincitore. Durante una separazione non ancora confermata nel DB, un’altra scrittura resta bloccata; dopo il commit procede soltanto nella struttura separata, senza modificare l’altro hotel.

Recupero verificato in due punti: **SIGKILL** del solo worker dopo l’aggiornamento SQL della persona e prima del commit/proiezioni; **eccezione sintetica** durante la preparazione dopo l’inserimento del primo collegamento. Nel primo caso dati e versione ritornano allo stato precedente, nessun aggiornamento parziale resta nel registro e la riprova riesce. Nel secondo non restano gruppo, partecipazioni o persone parziali, il cliente originale è byte-identico a livello di attributi e la riprova attiva il gruppo. Non si tratta di una prova di perdita disco o crash del server MariaDB.

Carico: **6.501 schede, 6.162 identità, 18.486 riferimenti locali**. Tutti i codici originali conservati, 2.167 provenienze per hotel, nessun soggiorno inventato e nessuna coppia dubbia nel set sintetico certo. Prima misura: anteprima 0,200 s, attivazione 32,815 s, picco del processo 132,5 MiB. Ripetizione nella suite finale: anteprima **0,138 s**, attivazione **20,889 s**; picco dell’intera suite 166,7 MiB. Limite del caso di collaudo: 120 secondi. Le differenze sono misure osservate, non uno SLA produttivo né una qualifica dei timeout del server online.

Schermate finali conservate e ispezionate: nessun overflow orizzontale; avatar della fixture senza immagine operativa, esclusa dal runtime. La prova browser copre scelta del gruppo, omonimi mantenuti distinti, risultato unico, terzo hotel escluso e separazione senza pacchetto. Non attesta ogni pagina e ogni combinazione possibile dell’applicazione.

## Evidenze e conservazione

[Ricevuta finale](evidenze/condivisione-clienti-2026-11-18/qualifica.json), [registro finale compresso](evidenze/condivisione-clienti-2026-11-18/finale-01.log.gz), [impronte](evidenze/condivisione-clienti-2026-11-18/manifest-qualifica.json). Registri compressi senza alterare i byte originali; errori intermedi conservati: prima prova di resilienza fermata da un metodo ausiliario `result()` in conflitto con un metodo final di PHPUnit, rinominato e riprovato con esito positivo. Nessuna correzione delle guardie o del codice applicativo necessaria per quel difetto del test.

Il runner e il template quotidiano ricevono solo l’opzione `--customer-sharing` e il flag nel processo effimero. Identità, namespace, proxy, origini, attestazione e rifiuti restano invariati. La differenza di impronta del runner VM dipende dal template operativo Linux documentato, dalla copia tramite reflink, dallo schema di riferimento non operativo e dal pin della base `df711e6`; non è una corrispondenza byte per byte con l’avviatore storico del Mac.

Stato prima della consegna: funzione e prove aggiornate SÌ; documentazione/evidenze SÌ; migrazioni solo su DB effimeri SÌ; dati reali modificati NO; nuove attivazioni abilitate NO; push/deploy/import/invii NO. Commit locale selettivo autorizzato dopo questi esiti, hash nella consegna. Il Maestro contiene lavoro estraneo preesistente: nell’indice viene inserita soltanto la sezione della condivisione, preservando integralmente il file di lavoro.

Restano distinti dal mandato concluso: mapping/import effettivo dei CSV, attivazione su dati reali, procedure di passaggio proprietario/utenze e ripristino infrastrutturale dopo perdita disco. Fusione con nuove registrazioni successive: annullamento automatico bloccato, attribuzione da revisionare.

---

# Cronologia dei checkpoint precedenti

# Condivisione clienti — candidato locale e collaudo pendente

18 novembre 2026. **PARZIALE: COLLAUDO DI BASE POSITIVO, QUALIFICA COMPLETA PENDENTE; NUOVE ATTIVAZIONI DISABILITATE.**

Checkpoint corrente: VM recuperata; 148 test PHP/983 asserzioni positivi, 15 prove delle guardie positive; dopo la correzione della sola nuova vista, 27 test/98 asserzioni e 1 scenario browser positivi, con schermate a 1440/768/390 px ispezionate. Restano carico, concorrenza e recupero prima dell’attivazione reale. Le sezioni cronologiche sotto conservano i blocchi e i tentativi precedenti.

Il titolare ha autorizzato l'implementazione dopo i passi documentali. Riferimenti: [progetto](PROGETTO-CONDIVISIONE-CLIENTI-2026-11-18.md), [piano esecutivo](PIANO-ESECUTIVO-CONDIVISIONE-CLIENTI-2026-11-18.md), [Maestro](maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md). **BASELINE IN AUDIT invariata**.

## Scope e stato Git

Ramo locale `codex/condivisione-clienti`, HEAD di base `df711e6c32f0cd984d09d4cf7d0253d66d70394a`. Nessun commit o push; documentazione preesistente conservata. Nessuna migrazione applicata su Herd o produzione, nessun CSV reale caricato. Le proprietà descritte sotto sono comportamenti candidati da provare, non risultati funzionali già attestati.

## Comportamento implementato nel candidato

- Configurazione `customer_sharing.enabled` falsa per impostazione predefinita. Creazione/ampliamento richiedono abilitazione esplicita. La voce del proprietario compare con funzione abilitata o gruppi esistenti, controllando anche l'esistenza della rotta. Disabilitare nuove attivazioni non interrompe gruppi già operativi.
- Scelta di almeno due strutture proprie attive; riepilogo non mutante e conferma con token legato a sessione/utente, scadenza e impronta dei dati. Gruppi sovrapposti e versioni obsolete rifiutati.
- Identità comuni cifrate, confronti indicizzati con HMAC e riferimenti locali compatibili con i precedenti `customer_id`. Il tratto globale di tenancy non cambia. `Customers::save()` delega esplicitamente al servizio: identità e proiezioni vengono aggiornate nella stessa transazione, non tramite observer o sincronizzazioni asincrone.
- Dati comuni limitati a `CustomerSharing::FIELDS`; consensi, note riservate, classificazioni e riferimenti ai cataloghi locali non vengono copiati negli altri hotel. I riferimenti locali restano proiezioni dell'identità e non copie indipendenti. Questa è la concretizzazione del modello di compatibilità del progetto.
- In preparazione si privilegia la scheda più completa; in un gruppo attivo nuove origini non sostituiscono valori discordanti del cliente corrente. Corrispondenza automatica conservativa, non per solo nome o contatto. Alternative, codici e fonti nei registri cifrati.
- Ricerca, lista ed export deduplicano per persona; gli alias storici restano risolvibili. Il picker conserva riferimenti locali autorizzati e aggiunge il token di versione; niente selezione diretta di ID di strutture escluse. L'importatore, nei gruppi, non riusa clienti per sola email/telefono.
- Il salvataggio di un soggiorno effettivo in un gruppo collega o crea l'identità tramite il percorso anagrafico esistente. Bozze e Arrivi non diventano soggiorni automaticamente. Lo storico comune mostra hotel e date, senza estendere azioni fiscali o note operative degli altri hotel. Snapshot preesistenti non riscritti.
- Scritture con versione e barriera del proprietario prima delle modifiche, letture correnti nei punti di attivazione/proiezione per evitare salvataggi fuori gruppo iniziati prima della sua attivazione. Concorrenza e durata dei lock ancora da dimostrare dinamicamente.
- Cura duplicati: confronto di fino a 100 coppie per volta, scelta della principale, conferma, mantenimento separato versionato e consultazione delle coppie revisionate. Dati cambiati ripropongono la coppia. Fusione annullabile solo senza aggiornamenti o nuove registrazioni da attribuire.
- Residenze discordanti: alternative cifrate con fonte, stato da confermare e data inizialmente vuota; conferma del proprietario o attestazione della reception sulla scheda condivisa. Modifiche successive invalidano la vecchia conferma. Nascita e residenza rimangono separate; il servizio non inventa cittadinanze.
- Separazione con consegna confermata: conserva pacchetto e versioni locali, revoca accesso generale, rende indipendenti gli aggiornamenti. Proiezioni senza origine o collegamenti vengono oscurate senza cancellazione. Anche Cestino/import sono considerati nella conservazione. Il pacchetto cifrato, con fonti locali, si consulta tramite il proprietario attuale della struttura e resta uno snapshot.
- Cancellazione/archiviazione protetta per clienti collegati o nei pacchetti; ripristino clienti passa dal servizio. Componenti controlla riferimenti locali. Contatori e compleanni distinguono il pacchetto dalla sola consultazione. Consensi discordanti fra alias del medesimo hotel non generano concessioni automatiche; aggiornamenti espliciti restano locali.

## Schema predisposto, non applicato

Una migrazione crea otto tabelle: gruppi, partecipazioni, persone, collegamenti, operazioni, versioni residenze, pacchetti separati e revisioni duplicati. Due indici compositi su `struttura` e `clienti` supportano le nuove FK di coerenza proprietario/struttura/cliente. Partecipazione unica per struttura. Cambio proprietario bloccato per strutture partecipanti, anche tramite i vincoli proposti.

Il ritorno `down()` rifiuta la rimozione se esistono gruppi. Dopo nuove scritture serve un recupero qualificato, non l'eliminazione delle tabelle o il ritorno a copie vecchie. Creazione effettiva, compatibilità MariaDB, installazione fresca, tempi e ripristino sono **PENDENTI**.

## Prove predisposte e controlli statici

Preparati 27 casi in `tests/Feature/CustomerSharingTest.php`: isolamento, due/tre hotel, altro proprietario, sovrapposizioni, versioni obsolete, consensi, identità incomplete, residenze, fusione/annullamento, origini/pacchetti, cambio proprietario e soggiorni nei due hotel. La loro presenza non dimostra il superamento.

Preparati fixture sintetica e spec Playwright: conferma del gruppo, coppia mantenuta separata, risultato unico, terza struttura esclusa e dimensioni 1440/768/390 px. Il runner aggiunge soltanto `--customer-sharing` per impostare `CUSTOMER_SHARING_ENABLED` nel processo effimero. Nessuna modifica a namespace, identità, DB, proxy, origini, override vietati o guardie. Credenziali fixture esclusivamente sintetiche.

Verifiche statiche **POSITIVE**: lint `php -n -l` su 18 PHP del candidato; sei viste compilate testualmente con `BladeCompiler::withoutComponentTags()` e poi sottoposte a lint; Node `--check` sullo spec, AST Python sul runner e `git diff --check`. [Ricevuta con impronte dei 26 file del candidato](evidenze/condivisione-clienti-2026-11-18/controlli-statici.json). La compilazione non avvia il kernel e non risolve i componenti: non equivale a rendering o prova browser. Nessun test applicativo sul Mac.

Selezione futura del runner ufficiale, soltanto dopo attestazione di VM e copia privata e corrispondenza dei sorgenti:

```text
--customer-sharing
--phpunit tests/Feature/CustomerSharingTest.php tests/Feature/SchedinaCustomerPickerTest.php tests/Feature/SchedinaCustomerReferenceSecurityTest.php tests/Feature/ArriviCustomerReferenceSecurityTest.php tests/Feature/CustomerExchangeAreaTest.php tests/Feature/CustomerImportedFiltersDeleteTest.php tests/Feature/CustomerManagementTest.php tests/Feature/CustomerWizardTest.php tests/Feature/StrutturaAuthorizationTest.php
--fixtures tests/Isolation/seed-customer-sharing.php
--playwright tests/Feature/customer-sharing.playwright.spec.js
```

Non è un comando host. I nuovi file devono essere nella copia privata inventariata secondo il launcher: non allargare genericamente allowlist. Riconciliare il pin storico del runner tramite il protocollo della copia ufficiale, senza saltare l'attestazione.

## Blocco infrastrutturale iniziale, superato dal riavvio

VM Lima `schedine-sviluppo-interno` indicata Running, ma SSH resettato e `limactl shell` senza risposta. Il ponte riporta `no route to host` verso il guest. Dopo il diniego iniziale del sandbox, la verifica con escalation autorizzata ha confermato l'irraggiungibilità: non un rifiuto della revisione automatica.

Il titolare ha autorizzato arresto/riavvio ordinato. Un'opzione timeout non supportata è stata scartata senza effetto; `limactl stop schedine-sviluppo-interno` ha ricevuto SIGINT ma si è concluso con `did not receive an event with the exiting status`. Verifica successiva ancora Running. A quel checkpoint non erano stati eseguiti `--force`, riavvio o modifiche della configurazione VM. Richiesta distinta sullo spegnimento forzato inizialmente pendente. Nessun ripiego su Herd o produzione.

## Pendenze prima dell'attivazione

1. Accesso VM recuperato, identità/runtime e spazio verificati; copie precedenti preservate. Non attestata verifica integrale del filesystem dopo l’arresto forzato.
2. Migrazione additiva eseguita nel DB effimero; test mirati/regressioni, guardie e primo percorso browser positivi. Restano recupero dopo scritture, concorrenza reale e copertura browser estesa di separazione/fusione/residenze.
3. Profilare preparazione, attivazione e proiezioni su numerosità sintetica comparabile ai CSV. Il candidato usa una transazione/barriera per proprietario: durata non misurata.
4. Separazione **senza autorizzare la consegna**: rifiutata senza modifiche. Segregare lo storico rispetto a un nuovo gestore non si ottiene nascondendo soltanto i clienti; servono decisioni ulteriori su custodia e accessi. Non dichiarare implementato il trasferimento di proprietà/utenze o ogni variante dell'affitto.
5. Fusione con registrazioni successive: annullamento automatico bloccato; attribuzione da revisionare, non duplicare arbitrariamente i soggiorni.
6. Verificare effetti indiretti su Componenti/Web, cataloghi, Cestino, export e contatori e revisionare il diff finale. Nessun audit globale dedotto dalla lettura statica.
7. Prima dei CSV reali: codici effettivi delle strutture/proprietario, sospesi e confronto con i clienti presenti. La proposta con colonne di lavoro non va caricata nell'importatore ordinario. Nessun import reale autorizzato da questo checkpoint.

## Consegna

**STATO PARZIALE.** Candidato scritto, nuove attivazioni disabilitate; VM recuperata e regressioni PHP positive. Qualifica browser e prove restanti indicate nella ripresa seguente. Rischio residuo ALTO finché non sono provati funzionalità, isolamento, recupero e UI. Nessuna regressione esclusa dal solo lint.

Codice modificato SÌ; test modificati SÌ (preparati, non eseguiti); documentazione modificata SÌ; GUI modificata SÌ nel candidato, senza nuovi CSS o ridisegno e con controlli condizionati al gruppo; configurazione modificata SÌ nel codice, flag predefinito disabilitato; schema/dati effettivi modificati NO; migrazione predisposta SÌ; test applicativi eseguiti NO; commit NO; push NO; deploy NO; import NO; trasmissioni esterne NO. Maestro aggiornato; lavoro estraneo conservato.

## Ripresa infrastrutturale del 18 novembre, ore 07:25

Su autorizzazione esplicita eseguito lo spegnimento forzato della sola VM; stato Stopped verificato. Primo avvio diretto interrotto durante il tentativo di recupero immagine: il collegamento del disco puntava al volume SchedineSviluppo non montato. Contenitore originale già collegato, UUID `8C348BA9-047A-4C81-9CB7-39E63FF5023E` conforme. Rimontaggio e avvio mediante `scripts/development/volume-vm-esterno.py avvia`: READY, SSH positivo, PHP 8.3.36, 43 GiB disponibili, zero unità systemd fallite. Questo non equivale a verifica integrale del filesystem dopo l'arresto forzato. Nessun disco nuovo, ripristino o modifica di configurazione.

Preparata copia privata `/home/collaudo.guest/condivisione-iFAscNiI/repo`, senza sovrascrivere la copia quotidiana o quella Questura. Base df711e6, overlay dei 26 file candidati, dipendenze copiate tramite reflink. Indice modificato solo nella copia privata. Template operativi del runner e delle due attestazioni vincolati alla base secondo protocollo esistente; aggiunta del solo flag clienti nel runtime effimero. Primo collaudo mirato avviato nel namespace ufficiale; esito da registrare.

Prima prova fermata correttamente per `.schema-baseline.sql` assente e risorse effimere rimosse. Copiato il riferimento non operativo della copia precedente, SHA256 `69165a45be73d6155f893a79cbca6dc5f97ced074df0c81d94e6b3197f42da27`. Seconda prova **POSITIVA: 27 test, 98 asserzioni**, PHP 8.3.36/MariaDB 11.8.9; attestazione processi/DB/HTTP, riconnessione e 23 rifiuti di override positivi, build/GEO positivi. Migrazioni applicate solo nel database effimero poi rimosso. Avviata selezione delle regressioni pertinenti e spec browser; risultato pendente. [Ricevuta ripresa](evidenze/condivisione-clienti-2026-11-18/ripresa-vm.json).

Terza sessione: **148 test PHP, 983 asserzioni positivi**, inclusi i 27 nuovi casi; ulteriori 15 prove delle guardie positive. Browser R1 negativo per errore dello spec: cercava «Entra» anziché «Accedi» sulla pagina di accesso, prima di esercitare la funzione. Corretto il solo spec per usare il pulsante e le conferme UI esistenti; riprova browser avviata con nuovo runtime effimero. Nessuna modifica applicativa derivata da questa diagnosi. Registri integrali sintetici `prova-01.log.gz`, `prova-02.log.gz`, `prova-03.log.gz` nella directory evidenze corrente; ricevuta statica precedente resta storica, nuova impronta dello spec in `manifest-copia-vm.json`.

Browser R2 negativo prima della funzione: lo spec apriva `/logout` da anonimo e il ritorno previsto dopo il login poteva rimandare a quella destinazione. Rimossa tale navigazione dalla preparazione; cambio account tramite pulizia dei cookie nel solo contesto Playwright sintetico e successivo login ordinario. Nessuna modifica al meccanismo di autenticazione, alle guardie o alle credenziali applicative. Browser R3 avviato.

Browser R3 raggiunge accesso, anteprima e creazione del gruppo; si ferma sul messaggio positivo duplicato fra layout e nuova vista. Rimossi dalla sola nuova vista i blocchi successo/errori già resi dal layout comune; nessun CSS o flusso precedente modificato. Avviata nuova sessione con i 27 casi mirati e browser, esito pendente.

## Esito della ripresa

**Browser R4 POSITIVO:** 1 scenario in 36 secondi complessivi; accesso ordinario, scelta di A/B, esclusione C dall’anteprima, creazione confermata, mantenimento separato della coppia dubbia, risultato unico in B, cliente assente da C e pagina proprietario vietata a C. Riprova contestuale PHP positiva: 27 test/98 asserzioni. Schermate 1440/768/390 px conservate e ispezionate: pagina leggibile, nessun overflow orizzontale; avatar sintetico privo dell’immagine operativa, esclusa dal runtime. Non dedotta qualifica di tutte le azioni o di tutte le pagine dell’app.

[Registro finale](evidenze/condivisione-clienti-2026-11-18/prova-06.log.gz), [ricevuta](evidenze/condivisione-clienti-2026-11-18/ripresa-vm.json) e [impronte della copia collaudata](evidenze/condivisione-clienti-2026-11-18/manifest-copia-vm.json). I 148 test/983 asserzioni precedono soltanto la rimozione degli avvisi duplicati nella nuova vista; codice applicativo PHP invariato. Nuove prove appropriate dopo tale modifica: 27 casi e browser, positivi.

**Consegna corrente PARZIALE; BASELINE IN AUDIT.** Riavvio richiesto completato, blocco infrastrutturale risolto, collaudo di base positivo. Nessuna attivazione reale, importazione, migrazione Herd/produzione, commit, push, deploy o invio. Codice applicativo PHP modificato in questa ripresa NO; nuova vista modificata SÌ (rimozione duplicazione), spec modificato SÌ; documentazione/evidenze SÌ; test applicativi nella sola VM SÌ. Restano le pendenze dichiarate, senza autorizzazione implicita al rilascio.

## Richiesta di installazione e preparazione del rilascio

Il titolare ha chiesto di installare e attivare la funzione. Verificati GitHub main e server pulito a `df711e6`, barriera aperta e FPM attivo, 66 tabelle e nessuna tabella/migrazione della condivisione presente. Kit separato preparato sulla procedura del rilascio precedente, con attesa del socket FPM, recupero della sola nuova DDL vuota prima dell’apertura e configurazione cifrata. Checkpoint verificato su copia privata senza rete: ripristino, nuova DDL derivata dalla VM ufficiale, ritorno a 66 tabelle, controlli e checksum dei dati preesistenti invariati. Test sintetici del flag positivi.

La revisione automatica ha respinto il push di `c1816e7` a `codex/condivisione-clienti` e `main` sul repository `https://github.com/joomlu/Schedinedinotifica`: richiede consenso esplicito alla destinazione e all’esportazione dei sorgenti/documenti. Comando non eseguito; GitHub riconfermato alla base. Nessun aggiramento tentato, nessuna installazione/migrazione/configurazione nel sito servito, nessuna unione di hotel. Serve conferma della pubblicazione prima del rilascio. [Ricevuta di preparazione](evidenze/condivisione-clienti-2026-11-18/preparazione-rilascio.json).

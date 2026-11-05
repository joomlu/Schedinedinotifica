# Verifiche FPM autenticate, Git reale e migrazione storica — 9 ottobre 2026

**Accettazione applicativa locale positiva conservata; BASELINE IN AUDIT — NON VALIDATA.** Nessun commit/push/deploy, scrittura DB operativo, modifica configurazione o servizio operativo, trasmissione reale. Nessun codice applicativo cambiato. Questo verbale integra, senza sostituire i risultati storici, MITIGAZIONE-CLI-FPM-2026-10-09.md e il Maestro.

## Perimetro e criteri dichiarati

Obiettivo: flussi FPM autenticati e tenancy, stabilità misurata, Git reale e classificazione della migrazione fallita. File interessati: diagnostici/evidenze sotto docs/evidenze/completamento-fpm-git-2026-10-09, rapporto, piano, Maestro e manifest; rischio MEDIO limitato a processi e DB privati. Nessuna nuova suite globale: applicazione e bundle invariati rispetto alle71regressioni giàPASS, globale750/8252 e precedenti prove Chromium conservate.

FPM/PHP8.3.35, ionCube15.5.1, OPcache8.3.35, MariaDB11.8.9. Namespace UID1002/GID1003 senza rete esterna/mount o socket operativi; nuove directory0700 e socketUnix0600,corelimit0. Ogni DB è nuovo e contiene solo ilDDL del cloneR1 e fixture sintetiche nuove, APP_KEY sintetica, mailarray/providerfalse; nessun worker/scheduler/invio. Due worker,128MiB memory_limit, max_requests100; master privati terminati e attesi, mai systemctl. Corpi/header/cookie/tempi separati per richiesta in deposito privato, nessuna esportazione cookie/config/chiavi inGit.

Criteri prima esecuzione: zeroSIGSEGV/SIGABRT, zeroHTTPinattesi;20cicli cold/warm attestati da OPcache; flussi autenticati separati fra due strutture; cross-tenant404 e righe cliente immutate; sessioni persistenti fra worker/masterprivati; RSSworker≤192MiB, ogni campione entro600secondi. Carico sostenuto separato:120secondi,4client,max8richieste/secondo, stesso budgetRSS e fail-stop. Queste sono prove finite, non una garanzia di disponibilità indefinita o di qualsiasi carico.

## Matrice di risultati

| Requisito | Evidenza | Esito effettivo e limite |
|---|---|---|
| Login e sessione | fpm-autenticato-1084.json | PASS: due operatori struttura_user/proprietario; autenticazione tramite form+CSRF reali, duecookiejar separati; sessioni persistenti nei20masterprivati |
| Tenancy lettura/scrittura | stesso manifest | PASS: elenchi con solo cliente proprio, modifica propria200; accesso cliente estraneo404, eliminazione estranea conCSRF404, eliminazione propria senzaCSRF419; righe immutate ogni ciclo.80HTTP404/40HTTP419 attesi,962HTTP200 e2login302 |
| Cold/warm OPcache | stesso manifest,cicli | PASS:20cicli, ciascunindexnoncached iniziale e cached successivo; contatorehit cresce. OPcacheweb eionCube restano caricati |
| Stabilità eRSS | fpm-autenticato-RSS.json | PASS nelcampione:1084richieste/28,702sec,1391campioni,40worker distinti; picco54,180MiBworker/42,059MiBmaster; zero crash/HTTPinattesi |
| Logout e revoca | fpm-flussi-81.json | PASS:81richieste/7,202sec; logoutPOSTconCSRF302 e successivo/clienti302, sessioneBancora200; revoca cross-tenant404, propria302, entrambialiasA404 ealiasB200 |
| Conversione e relazione tenant | stesso manifest | PASS: link full64/short32 convertiti mostrano soltanto messaggio previsto,no-store; richiestaAassociata alla schedinaB→404 da entrambialias; nessun datoB mostrato |
| Carico sostenuto/riciclo worker | fpm-sostenuto-985.json;stabilita-RSS-riciclo.json | PASS:985richieste totali,904sostenute per120,44secondi/4client,10worker distinti conriciclo;7262campioniRSS,picco49,949MiBworker/41,832MiBmaster;zeroHTTPinattesi/crash. Logout/revoca/tenant ancoraPASS dopo carico, DBprivatofermato |
| Git reale | git-reale.json | PASS: clone senza hardlink, commitHEADreale, clean e sigillo verificati; checkoutmodificato rifiutato; merge sullo stessoHEADexit0. Non è upgrade del candidato |
| Contenuto candidato Git | git-tree-reale-445.json | PASS:treec4e62d5ea70080878f079cae08aa2a787f2621ec;445delta conformi alla policy path/migration e archivio identico albundle. Tree rifiutato comecommit; nessuna guardia bypassata |
| Runnerintegrale conSHA | stessi verbali | BLOCCATO:HEADfaef3cf01f43d19b20d1558c1fbd3ae32e48d849non contiene il445; servecommitapprovato disponibile nell'origineammessa. Nessuncommit creato sotto ildivieto. Tree non sostituisce approved-sha |
| Installazione MariaDBda zero | migration-minima-mariadb.json | FAILapplicativo/compatibilitàDDL riprodotto conSQLminimo, indipendentemente daldiagnosticoFPM; dettaglio sotto |

## Migrazione storica: causa e impatto concreto

La sequenza di `2026_02_27_120000_create_tipologia_relations` crea su classificazioni l'indice univoco `(tipologia_struttura_id,nome)`. `2026_02_28_000001_normalize_classificazioni` rimuove ilforeignkey ma lascia l'indice composto, poi rimuove tipologia_struttura_id. SuMariaDB11.8.9 ilSQLminimo di due tabelle vuote riproduce **ERROR1072/42000** alla rimozione colonna, come nella migrazione fallita. NessunLaravel necessario perriprodurlo. La variante sperimentale rimuove prima l'indice composto, poi lacolonna e crea l'univocitànome: tutti3DDLexit0. Questo dimostra il rimedio SQL nelriproduttore, **non** una migrazione completa corretta/provata.

**Installazione nuova MariaDB11.8.9: BLOCCATA** lungo la sequenza corrente. Non è unproblema di credenziali, FPM o deldiagnostico; è incompatibilità della sequenzaDDL corrente con ilmotore provato. Non generalizzare adaltreversioniMariaDB/MySQL. Rimedio futuro: drop mirato dell'indice dopo migrazione dei dati e prima dropcolonna, conprove freshinstall, duplicati/deduplica, riferimenti e upgrade. Nessunfixapplicativo o modifica di migrationstorica inquesta task.

**Aggiornamento previsto del destinatario: non bloccato da questa migration.** Metadati sola lettura attestano che normalize_classificazioni è già applicata; non appartiene alle7pendenti. R1 ha eseguito soltanto quelle7conexit0, storico+integrità/decifrabilitàPASS;R2ha ripristinato snapshotcompleto. Non ripetere fresh/migrate indiscriminati. Questoverdetto riguarda lo snapshot acquisito: riconfermare che storico/elencopending non siano cambiati nelpreflight delrilascio effettivo. Mai modificare o rieseguire retroattivamente la migrationoperativa.

## Problemi del diagnostico preservati

v10:importPathmancante, prima diDB/FPM. v11:telefono mancante nella fixtureStruttura;v12:avatar mancante nellafixtureUser. KernelConsole stampava errore e restituiva0: l'assenza delmanifestfixture ha fermato la prova, nessunFPMavviato; non usare ilsoloexit0comeaccettazione. v14 completa esplicitamente campi giàrichiesti; nessunaschema/aspettativafunzionale alterata.

v15:HTTP200 ecorpoesatto corretti, maasserzioneheader troppo stretta (`Cache-Control: no-store` esatto). Risposta effettiva `no-store, private`: Laravel aggiunge ladirettivaprivate, non elimina no-store. La copia identificatav16verifica lapresenza della direttivano-store separata daaltredirettive, mantenendo200/corpoanonimo/nessundato;originali v15header/body/source/59richiesteFAIL restanoinprivato emanifestFAILinGit. Non è mitigazioneapplicativa néallentamento delrequisito. OriginaleA14 e leaspettativefunzionali precedenti non toccati.

## Blocchi residui e recuperabilità

**B1:** servefuturocommitSHAapprovato cheidentifichi ilcandidato aggiornato, pubblicazione separatamenteautorizzata; poi clonepulito/preflightprepare/executecompleto e recupero coordinato sulla copia conquelSHA. Gitreale earchivi attuali verificati, non basta approvareHEADstorico o tree. Candidato445precedente recuperabile tramitepatchriservata emanifest inriferimento-candidato-445.json; precedenti412ealtri conservati.

**B2:** risultatiFPMautenticati/coldwarm/RSS chiudono quelleprove nelcampioneisolato; caricosostenuto120secondi e riciclo10workerPASS. Restano gate di vhost/TLS/proxy/clock/UID/SELinuxoperativi ecorrelazione degli eventi storici: zeroeventinelcampione nonprova che tutti49corestorici abbiano stessacausa. Nessunconfig/restartoperativo autorizzato.

**B3:** backupcoordinato/R0/R1/R2PASS conservati;preflightlive deve riconciliarevariazioni successive. **B4:** ritiro effettivo CKEditor/assetobsoleti,flagprovider/SMTP/egress econfinepubblico dachiudere nella procedura di rilascio autorizzata. Serviziesterni nonvalidati/nonattivati. FreshinstallMariaDBbloccata ègate dellanuovainstallazione, separato dall'upgrade esistente. Nessun'accettazioneproduttiva.

Tre nuovi campioni positivi:1084+81+985=2150richieste; non sommare alle59richieste deldiagnosticoheaderfallito. I662risultati precedenti restano storici separati. IquartiliRSSperworker sono descrittivi: non dichiarata assenza di leak oltre ilcarico misurato. Tutte le20attestazionicold/warm sono registrate, nona posteriori inferite dalHTTP200. Confronto dei sorgentiFPM colcandidato e pulizia finale nelmanifestdedicato.

Attestazione:540sorgenti della copiaFPMidentici al candidato (app/config/routes/views/migration/bootstrap/composer),nessunsegretoletto;pulizia finalenessunprocessoprivatoresiduo. Nuoveprove tramiteFastCGIsocketUnix,nonunanuovaesecuzioneChromium;precedenteaccettazionebrowser conservata,non presentata comeprova delvhostFPMproduttivo.

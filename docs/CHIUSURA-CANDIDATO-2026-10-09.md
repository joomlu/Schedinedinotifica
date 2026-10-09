> Aggiornamento 10 ottobre 2026: il difetto del preflight qui diagnosticato è corretto e verificato; questo verbale conserva la review precedente. Stato corrente e limiti nel [rapporto di correzione](CORREZIONE-PREFLIGHT-2026-10-10.md). Runner integrale sul commit approvato ancora PENDENTE.

# Chiusura del candidato per autorizzazione Git — 9 ottobre 2026

**Review completata; chiusura del candidato di rilascio BLOCCATA da un difetto del preflight riprodotto.** Il contenuto può essere autorizzato per archiviazione Git dello stato di audit, ma non come rilascio chiuso. BASELINE IN AUDIT — NON VALIDATA. Nessun commit/push/deploy effettuato. Nessuna modifica DB operativo, servizio operativo o trasmissione.

## Identità e contenuto verificati

Repository locale `/Users/jorgeluccitelli/Herd/Schedinedinotifica`, branch `main`, HEAD `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`. `origin` fetch/push: `https://github.com/joomlu/Schedinedinotifica`. Lettura `git ls-remote --heads origin refs/heads/main`: SHA identico a HEAD; non aggiornati ref locali. Nessun hook eseguibile non-sample trovato; nessunworkflowCI/deployversionato. API GitHub di sola lettura:0workflow e0webhook; nessunjobavviato,config/segreti deiwebhook esclusi dallarichiesta. Ricontrollare questi riscontri prima delcommit/push. Non è una certificazione di qualsiasi automazione esterna non osservata.

Il candidato iniziale corrisponde esattamente al tree **e66f9647c48de84b7383e88d9d9eb3a7d9ba3c41**:471file modificati/aggiunti,4568file complessivi nell'indice; hash manifest/staging identici. Tree ricalcolato senza scrivere nel repository e confermato da Git write-tree in clone privato; archivio Git e bundle identici, policy path/migrationPASS. Snapshot completo riservato e manifest precedente preservati prima di questa chiusura, riferimento nella cartella evidenze dedicata.

Il tree iniziale **non è un commit**. HEAD non identifica il lavoro staged. Gli aggiornamenti richiesti al Maestro, questo verbale, il messaggio e gli esiti di review generano un nuovo tree, riportato nella consegna per approvazione. Il solo delta da e66 è documentale: nessun ulteriore cambiamento app/test/runner/dipendenze. Non committare il nuovo contenuto dichiarandolo e66.

Review del diff HEAD→e66:25file app,2migrationnuove,13view,1route,3strumentideploy,114test/diagnostici,308documenti/evidenze e5manifestdipendenze/configasset. App: contratto token, sessioni/account/impersonazione, Cestino, tenant, Arrivi/Schedine, ISTAT e tassa di soggiorno; i cambiamenti corrispondono alle correzioni accettate. Dipendenze:Laravel12.69.3/PHP8.3, rimedi Composer/npm e esclusione CKEditor dal nuovo artefatto; nessun ritiro degli asset operativi implicito. Test/diagnostici e documenti conservano gli originali e i rossi storici. Nessuna cancellazione, symlink/submodule, migrazione esistente cambiata o percorso runtime protetto nel delta. Gli540sorgenti usati dalle prove FPM corrispondono al candidato.

Scansione di tutti471blob: nessun riscontro di chiavi private, tokenGitHub/AWS, URLDB/SMTP con password, passwordDB/SMTP lunghe, cookie serializzati oAPP_KEY non-fixture. Due APP_KEY letterali sono la fixture nota composta da32lettereA, in test e copia storica; non sono chiavi operative. Email fuori fixture sono metadati pubblici dei manutentori Composer e contatto pubblico dell'ente documentato, non dati ospiti. Nessun.env/dump/backupdelDB, chiave privata o runtime inserito. I metadati sanitizzati del destinatario sono evidenze autorizzate; non sono copie delle sue righe o credenziali.

L'unico.tar.gz staged è l'archivio storico candidato318:318membri regolari di sorgenti/evidenze, verificati internamente senza.env/dump/runtime/chiavi. È un riferimento recuperabile pertinente, non backup operativo. Il delta pesa73.363.339byte; maggiore file è la patch storica candidata389 (35.634.290byte), conservata. Non introdotti artefatti temporanei o file estranei. Questa review specifica ciò che è stato controllato; una scansione non è una garanzia universale di assenza di ogni formato segreto.

## Controlli superati e gate distinti

| Controllo | Stato ed evidenza |
|---|---|
| Accettazione applicativa locale/contratto e Laravel12 | PASS nel perimetro delle matrici; globale750/8252 e browser già registrati, non ripetuti per sole modifiche documentali |
| Supervisor |71/71LinuxPASS; configurazioneCLI0, guardie/versione/sigillo/processi e sequenza su fixture, nessun rollback automatico |
| FPM privato |PASS nei campioni:662precedenti+2150successivi;20cold/warm,sessioni/tenant/CSRF/logout/revoca/alias;carico120,44sec/4client,10workerconriciclo,RSSentrocriteri |
| Backup/restore/migrazioni/recupero |R0/R1/R2PASS nel perimetro acquisito, sette pendenti provate;originali eAPP_KEY riservati preservati |
| Git reale/pre-review |Clone/archive/policy/hashPASS;origine/main osservata ugualeHEAD;tree non accettato comecommit |
| Commit/push |PENDENTE autorizzazione su nuovo tree finale e messaggio predisposto |
| Runner prepare/execute integrale |BLOCCATO anche dal rigetto del vendor11 nella letturapending; serve correzione mirata verificata, poi commitSHAapprovato/pubblicato e sandboxintegrale. Non sostituirlo con fixture/mock |
| Produzione |Vhost/TLS/proxy/clock/UID/SELinux, ritiro asset obsoleti, flag/egress/SMTP e serviziesterni restano separati; nessuna validazione produttiva |
| Installazione nuova MariaDB11.8.9 |BLOCCATA dal dropcolonna conindicecomposto della migrationstorica; non blocca l'upgrade dove è giàapplicata. Nessunfix implicito |

## Blocco emerso dalla review del runner

`prepare()` termina con `pending(source/database/migrations)`; `pending()` invoca l'helper sul **root attuale**, non sulla sorgente candidato, quindi usa il vendor11.31.0 del destinatario. Il gate dell'helper esige12.69.3 prima di leggere lostorico. Composerinstall12 avviene solo inexecute, che non viene raggiunto. Spostare config:clear dopoComposer ha corretto un altro ordine, ma non questa lettura delpreflight.

Riproduzione isolata su fixture senzaDBoperativo: vendor11.31.0,PHPmitigato,stesseguardie,path/envvalidi,sola copia identificata con osservabilitàcatch. Exit1,RuntimeException **Approved Laravel 12.69.3 required.**, prima queryDB; risultato inpreflight-legacy-diagnosi.json. L'originale è intatto. Non è fallimento delloSHA o instabilityOPcache; è un blocco applicativo dello **strumento di deploy**. Le71guardie e iGit/FPMPASS precedenti conservano il loro perimetro, ma non coprivano questa transizione completa.

Correzione proposta, non implementata nella task di review: separare la lettura strettamente readonly dello storico della baseline dal gate mutante del candidato12. Preferire helpermetadata senza boot dei providerapp11, con sigillo/path/connessioneMariaDB/transactionreadonly e confronto migrationcatalogo conservati; nessuna accettazione generica di versioni11/13 e nessunaggiramento delgate12 per operazioni mutanti. Verificare con baseline11+candidate12,sameversion12,versioniinattese,DB/APP_KEY/pathmismatch,storicoalterato,7pendingesatte e readonly, prima delrunnercompleto. Il fix modifica iltree e richiede nuova review/identificazione prima delcommitfinale. Non sostituire vendor o cambiare DB suldestinatario per farpassare ilpreflight.

## Commit e push: passi dopo l'autorizzazione, non eseguiti

Messaggio esatto: `docs/COMMIT-CANDIDATO-2026-10-09.txt`. Autorizzazione da riferire al **nuovo tree finale**, non a e66 o al vecchioHEAD. Non usare gitadd indiscriminato, --amend, forcepush, rebase/reset/clean. Prima ricontrollare indice, manifest, hook eorigine. Se cambiano tree, HEAD o origine/main, STOP e nuova review, senza integrare automaticamente cambiamenti remoti.

```sh
cd /Users/jorgeluccitelli/Herd/Schedinedinotifica
CANDIDATE_TREE='<TREE_FINALE_APPROVATO>'
test "$(git branch --show-current)" = main
test "$(git rev-parse HEAD)" = faef3cf01f43d19b20d1558c1fbd3ae32e48d849
test "$(git remote get-url origin)" = https://github.com/joomlu/Schedinedinotifica
test "$(git write-tree)" = "$CANDIDATE_TREE"
test -z "$(git diff --name-only)"
test "$(git ls-remote --heads origin refs/heads/main | cut -f1)" = faef3cf01f43d19b20d1558c1fbd3ae32e48d849
git commit -F docs/COMMIT-CANDIDATO-2026-10-09.txt
APPROVED_COMMIT=$(git rev-parse HEAD)
test "$(git rev-parse "$APPROVED_COMMIT^{tree}")" = "$CANDIDATE_TREE"
git push origin "$APPROVED_COMMIT":refs/heads/main
test "$(git ls-remote --heads origin refs/heads/main | cut -f1)" = "$APPROVED_COMMIT"
```

Questi comandi vanno eseguiti con fail-stop (`set -euo pipefail`) e autorizzazione separata. gitwrite-tree qui è soltanto la verifica al momento del commit autorizzato; non è stata eseguita sulrepository principale inquesta task. Un hook successivo o un cambio remoto ferma ilpush; non rimuovere controlli per proseguire. Dopo pubblicazione conservare SHAcommit/tree,parent everbaleGit. Commit/push non autorizzano deploy.

## Runner integrale: ambiente obbligatorio prima dei comandi

Entrypoint immutato `deploy.sh`→`scripts/deployment/deploy.py`. Il main richiede Linux, utenteUnix **tanggosoftware nonroot**, repository **/home/tanggosoftware/repos/schedinedinotifica**, branchmain e origin fra URLGitHubammessi; PHP8.3,Node20/npm10,Composer2,Python≥3.9,estensioni richieste e≥2GiBspazio. SHA40hex di **commit**, fast-forward, checkoutpulito, archivio/bundleidentici e hashbuild restano vincolanti. DEPLOY_ORIGIN_IP cambia l'origineHTTPS, non ilpath repository o ilDB: non usarlo come isolatore da solo.

**Il namespace integrale non è ancora allestito.** Non eseguire comandi seguenti nella sessioneSSHoperativa. Il main usa unpath hardcoded e ilpreflight non è interamente readonly:crea lock/scratch,fetch e invocaArtisanpending. L'autorizzazioneGit non autorizza l'esecuzione fuori isolamento.

Preparazione finita, da completare dopo disponibilità delcommit e prima di invocare main:

1. Clonare neldepositoprivato repository reale eorigine ufficiale in sola lettura; fissare ilcommitapprovato e uncheckoutmain delcommitdestinatario c21b9185a33d1f5e0720c08f1547af6fa4857b20. Nessuncommit nuovo nelclone, nessunreceive-pack/push dalnamespace. Estrarre separatamente ilbundle dalcommit e confrontarlo byteperbyte.
2. Nuovo namespacefilesystem/rete, UID1002/GID1003 e directory0700, senza mount di produzione, .envoperativo o socketDBreale. Bind **soloilclone** alpathhardcoded. Git/runtime/home/CAcache/devtmp/proc devono appartenere alnamespace. Attestare mountinfo,datadir/socket/UUIDserverid,UID,origine,commit,assenza routeesterne prima Laravel.
3. Nuovo MariaDBprivato skip-networking/eventsOFF, schemaDDL **precedente alle sette migrazioni** ricavato dalla copiaR0 e soli nomi/batchdellostoricomigration da metadati. Nessuna riga operativa; crearefixture sintetiche compatibili. Non usare migrate:fresh:ilfreshstoricoMariaDB è bloccato. SnapshotS0privato di DB/file/config prima della prova.
4. .envprivato:APP_ENVproduction/debugfalse/APP_URL deldominio atteso, APP_KEYnuova sintetica, DBsocketprivato, cache/sessionfile,mailarray, QUESTURA_WS_ENABLED=false eISTAT_WS_ENABLED=false. Verificare valori effettivi e percorso cache;vietato copiare segreti/configoperativa. Nessuncron/task/worker; connessioni provider/SMTP/reti esterne impossibili nelnamespace.
5. CacheLinuxComposer/npm preventivamentepopolate in sola lettura per i lock delcommit; nessunplugin/scriptComposer. npmci/build devono realmenteeseguirsi e usare cacheoffline, non outputsimulati o vecchinode_modules. Preparare unvendor11 delbaseline coninstallno-scripts/no-plugins in sola copia, così si prova davvero latransizione11→12.
6. HTTPSprivato su443nelnamespace eFPMUnixprivato:dominio applicativo atteso,TLSconCAprivata solonelnamespace e verificaTLSattiva, documentrootclone/public, handlerPHP8.3. Gitmirror **readonly** dentro ilnamespace, oggetti provenienti dalclone ufficiale e SHAapprovato:origine logicamantieneURLammesso, risoluzioneDNS/TLS esclusivamenteprivata e dichiarata comefixture. Disabilitare receive-pack;non impostare sslVerifyfalse/curl-k o sostituire validatori. La prova di origineGitHubreale resta ilfetch/readfuorinamespace conSHAidentico.
7. Attestare servizioHTTPSlogin200, asset, bypassmanutenzione/headersegreto confinato alclone, processi figli/namespace/datadir e sigillo.env/APP_KEY. Conservarecredenziali/cookie/CAprivatasolo neldeposito. Se Gitfetch, dipendenze oHTTPSnonfunzionano, STOP:non aprire rete generica e non usaremock per dichiararePASS.

Solo dopo la correzione verificata delbloccopreflight e questi sette riscontri, dentro **quel namespace attestato**, con PHP_BIN=/opt/remi/php83/root/usr/bin/php, COMPOSER_BIN=/usr/bin/composer, DEPLOY_ORIGIN_IP=127.0.0.1, COMPOSER_DISABLE_NETWORK=1 e npm_config_offline=true:

```sh
# Esclusivamente nella sandbox attestata; valori assegnati dal verbale del commit.
/bundle/deploy.sh --sha "$APPROVED_COMMIT" --check --migrate
/bundle/deploy.sh --sha "$APPROVED_COMMIT" --execute --migrate --backup-ref snapshot-sintetico-S0
```

L'ordine delle due invocazioni èvincolante; execute solo con checkexit0,7pendingesatte, snapshotS0verificato ebackup-refdelclone. Qui --migrate autorizza soltanto ilDDL delDBprivato, non produzione. Risultati attesi:checkoutfinalecommitapprovato,pendingvuote,Laravel12.69.3,CLI0,composer/no-dev/build reali,login/assetHTTPSverificati,barriera503funzionante e rimossa aPASS,sigilloimmutato,processiprivatiterminati. Testare ancheFAILidentificato susecondacopia, guard503retained ecleanup;runner nonfa rollbackautomatico.

Recupero: preservarecopiadelFAIL, restaurareS0 in **terza nuova copia**, verificarefile/DB/config/APP_KEY eHTTPS; mai migrate:rollback (downvuoto inprotect_istat_cycle), mai restore/reset/clean sulla copia originale oproduzione. Nessunriavvio operativo. Se uno diquesti criterimanca, runnerintegrale **non accettato**.

## Verdetto per questa autorizzazione

Reviewdelcandidato completata nelperimetro indicato. **Non raccomandata l’autorizzazione come candidato di rilascio chiuso prima della correzione del preflight.** È possibile autorizzare commit/push delnuovotree soltanto come archiviazione esplicita dello stato BASELINE IN AUDIT, includendo ilblocco documentato e ilmessaggio predisposto. Runnerintegrale resta gate successivo, richiede anche l'allestimento privato qui esplicitato. Tutti i gate produttivi restano indipendenti. Nessun deploy/servizioesterno autorizzato daquesta approvazione.

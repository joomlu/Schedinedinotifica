# Operazione protetta da autorizzare — 9 ottobre 2026

**Preparata, non eseguita.** Scopo: acquisire una copia recuperabile del destinatario e provare restore/migrazioni/stabilità esclusivamente su istanze private. Non include deploy, commit/push, riavvio di servizi o DDL sul DB operativo. Fonte maestra: Maestro; candidato372 dell'indice corrente, Laravel12.69.3, precedente318 recuperabile.

## Diagnosi segfault con evidenza disponibile

Kernel:29eventi PHP/opcache.so del7ottobre18:00:51–18:33:18UTC. Registro systemd-coredump nella stessa finestra:49SIGSEGV PHP,47senza core e2con core conservato. Conteggi di fonti diverse, non49nuovi crash aggiunti ai29. I due core PID1896486/1896671 appartengono aUID1002tanggosoftware, eseguibile /opt/remi/php83/root/usr/bin/php. Simboli letti, nessuna memoria esportata:

```text
zend_get_file_handle_timestamp → opcache_compile_file → persistent_compile_file
→ ioncube_loader.so → php_lint_script → do_cli
```

Conclusione limitata: entrambi i core disponibili sono crash del lint CLI nella catenaOPcache/ionCube, non una prova di crash FPM o di difetto business Laravel. Non attribuibili tutti49senza ulteriori stack. FPMjournal della finestra filtrato non mostra eventi pertinenti; l'assenza non chiude il gate. PHPcli/FPM/opcache RPM8.3.35-1.el10.remi, ionCube15.5.1; caricamentoionCube01primaOPcache10, enable_cli1. JITtracing ma buffer0: non dimostrata esecuzioneJIT. Ini datati22settembre: timestamp non prova immutabilità storica. Nessun servizio alterato.

Mancano: comando/input preciso dei lint originari, hash/config effettivi del7ottobre, stack degli altri47eventi, riproduzione controllata e risultato FPM sotto carico. Non accedere a cronologia shell indiscriminata o memoria core: potrebbero contenere segreti. Cercare solo log diagnostici approvati e conservare identificatori/hash sanitizzati.

### Prova di stabilità preparata (scritture remote riservate da autorizzare)

Copia372 e sorgenti interessati dal lint in sandboxBubblewrap, UIDnonroot, namespace rete privato, nessun mount di produzione/socketDB/.env, scratch0700, timeout e arresto al primoSIGSEGV. Prima verificare guardrail isolation/MariaDB già presenti; nessun php -n come certificazione.

1. Riprodurre il medesimo lint file/percorso originale se recuperabile, altrimenti dichiarare campione diverso.20passate sequenziali e20a4processi sui soliPHPdelcandidato; log exitcodici/hash/file, niente esecuzione business. Budget massimo20minuti, nessun loop infinito.
2. Matrice suprocessi privati: AconfigstandardOPcache+ionCube; Bstesseestensioni con `-d opcache.enable_cli=0`; Cini privata senzaionCube ma conOPcache; Dsenzaentrambi. C/Dsoloattribuzione, non criteri di produzione. Controllare moduli/ini effettivi per ogni variante; se Afallisce STOP e preservare core nel deposito privato senza esportare memoria.
3. Solo dopoAverde: candidataLaravel12 + DBsintetico nell'harness autorizzato, globale750/8252 sulruntimeMariaDB e flussi browser pertinenti. Sessioni warm/cold concorrenti; FPM privato avviato come processo figlio con pool/socket/ini nel solo scratch, senza systemctl/restart o connessione aFPMoperativo.200richieste sintetiche a4clienti +10riavvii del solofiglio privato. Namespace senzaegress, providerfalse/SMTPtrap.
4. Criterio: nessunSIGSEGV/SIGABRT, exitcodici0, nessun5xx imprevisto, assert tenant/alias/429/credenziali sintetiche verdi, cleanup verificato. Risultato limitato a runtime/config/campione registrati. Un miglioramentoB/Cnon autorizza modifica.ini produttivo; prepara una proposta separata e regressione della configurazione scelta. Assenza eventi neljournal non sostituisce prova.

## Backup del destinatario: oggetto e consistenza

DB esatto `tanggosoftware_schedinedinotifica`, MariaDB11.8.9:58tabelleInnoDB, event_schedulerOFF/log_binOFF. Sorgenti reali `/home/tanggosoftware/repos/schedinedinotifica`, SHA remoto c21b9185a33d1f5e0720c08f1547af6fa4857b20.

Destinazione nuova ed esclusiva proposta: `/home/tanggosoftware/backups/primo-rilascio-20261009-<UTC>-<casuale>/`, fuoriDocumentRoot, root/proprietarioautorizzato,0700/0600, nessun symlink. Seconda copia cifrata fuorihost nel deposito riservato delMac; chiave di cifratura in secretstore distinto, non nella chat/Git. Verificare spazio con dimensioni effettive prima copia; timestamp/id fissati nel verbale riservato. Non riutilizzare una vecchia directory.

AcquisizioneDB con autenticazione socketroot esistente o fileclient0600 già autorizzato; niente password inargv/envpubblici:

```sh
umask 077
mariadb-dump --single-transaction --skip-lock-tables --routines --events --triggers \
  --hex-blob --default-character-set=utf8mb4 \
  tanggosoftware_schedinedinotifica > "$SCHEDINE_BACKUP/db.sql"
```

Questo comando è un frammento preparato, non eseguito; SCHEDINE_BACKUP deve essere validato come directorynuova riservata. Verificare exitcode e stderrprivato, byte>0, SHA256. Singletransaction rende consistenteDBInnoDB, **non** i file né DDLconcorrenti. Per snapshotDB+file coerente è indispensabile una finestra senza scritture applicative/DDL, confermata dall'operatore; se non disponibile STOP. Questa task non autorizza manutenzione operativa: l'eventuale barriera scritture richiede approvazione separata. Non attestare consistenza globale con due copie fatte durante modifiche.

Archiviofile: tutto il checkout operativo incluso public/build, vendor, storage/app (export/ricevute/import/upload), public/images, storage/framework/sessioni/cache, logs e bootstrap/cache, `.env` econfig; preservare proprietà/ACL/xattr/link senza seguire link esterni. Inventariare symlink; public/storage è link esatto a storage/app/public e si archivia come link. Configurazioniserver necessarie: vhost/include, poolFPM, PHPini/moduli, versione/binari, cert/referenzeTLS e credenzialitecniche per recovery; copie riservate senza pubblicazione. Se un link/file fuori root è necessario, autorizzarne la copia esplicitamente; non tar dereference indiscriminato. APP_KEYoriginale invariata e recuperabile; conservare anche DB/SMTP/credenzialicache esistenti in deposito privato. Non stampare valori.

Controlli: sha256manifest per dump/archivio/config,0700/0600, elenco completo/exitcode, motori e catalogo prima/dopo, HMACriservato di righe/storico/archivi, APP_KEY/.envhash e legibilità delle credenzialicifrate tramite digest senza valori. Niente file backup nelrepo oURLpubblici. Nessuna cancellazione automatica delle copie.

## Restore isolato e prova delle sette migrazioni

Creare **seconda istanza MariaDB11.8.9 privata**, datadir/socket/PID/config tutti nel deposito di lavoro distinto; `--skip-networking`, event_schedulerOFF, niente socket3306operativo, utente nonroot/UID separato, server_id nuovo, nomeDB `schedine_restore_<id>` maiugualeproduzione. Guardie devono verificare PID/eseguibile/UID/socket/datadir oltre il solo nome. Non restaurare nelservizioMariaDB operativo. Namespace senzaegress e senza mountoperativi; sorgenti e secreti copiati dalbackup non collegati per symlink alvero.env.

Importare dump nella nuovaistanza. Prima di avviareLaravel riposizionare/configurare la sola copia: DBlocaleisolato, providerfalse, SMTParray/trap, URLprivato, session/cache/queuefile; eliminare dalla copia le cache con percorsiproduttivi dopo averne preservatooriginali. APP_KEYcopiata invariata; HMACsolo inreportprivato. Non usare launcher sintetico per dati reali: restoreprivato separato senzaHTTPpubblico e senzafixtures/seed.

GateR0: conteggi/HMACtutte58tabelle, metadatacolonne/indici/FK/trigger/routines/events, migrationhistory, hashfile/export e decifrabilità uguali; statistichecatalogo aggiornate evitando falsoAUTO_INCREMENT cached già diagnosticatoHerd. Ambiguità credenzialiesistente STOP, niente ricifratura per nasconderla. Prima diDDLcreare secondo snapshotisolatoS0 recuperabile.

| Ordine esatto | Migration | Impatto/requisito | Recupero |
|---|---|---|---|
|1|2026_10_06_100000_encrypt_questura_credentials|PreflightLegacyCredentials di tutte strutture, TEXTpassword/wskey, cifratura; APP_KEYcorretta e nessun involucro ambiguo|downno-op; ripristinoS0|
|2|2026_10_06_110000_add_questura_archive_integrity|SHA/byte/charset/component_ids/status/send_keyunique, eventi e ricevute/FK; tabelleQuesturapreesistenti|downno-op;S0|
|3|2026_10_06_120000_add_questura_retention|Dipende2: nullabletrasmissionericevuta, retention/indici, FKricevuta/metadati, nullablepath/date|downno-op;S0|
|4|2026_10_06_130000_add_questura_send_reservations|Identityreserved, nuovatabellariserve/FK e univocità struttura+scheda+trasporto|downno-op;S0|
|5|2026_10_07_180000_create_tassa_exports_and_receipt_image|Colonnaimmaginericevuta+versionisnapshotexport, uniqueperiodoversione|downcancellaarchivi: vietato in questo piano;S0|
|6|2026_10_07_180000_protect_istat_cycle|TEXT/cifraturaISTAT, snapshot/hashes/retention, idempotenza/attempts,2tabelleeventi/giorni; controllodecifrabilitàpreesistente indispensabile|downno-op;S0|
|7|2026_10_09_230000_add_web_checkin_link_lifecycle|4campi link, short_tokenunique; nonemette/riattiva tokenlegacy|downperde revoche/scadenze: vietato;S0|

Applicazione **solo nella copia** perfile esplicito, ordine sopra: `php artisan migrate --force --path=database/migrations/<nome>.php`. Prima/ dopo ogni file: storico+schema atteso, valorioldcolumns/HMAC, credenzialidecifrate, righeexisting/FK; dopoFAIL fermarsi e conservare DBparziale/log, non riprovare genericamente migrate. Esattamente7nuovestorico e0pending finali. Niente migrate:fresh/seed/rollback. DDLMariaDBnonatomico: non promettere rollbacktransazionale.

GateR1:7migrazioni verdi, sorgentiAPP_KEY/configoperativiimmutati, credentialdigest earchivi preserved; eseguire regressioniquestura/ISTAT/tassa/token sulla copia con nessuntrasporto. GateR2: restaurareS0 in **terzaistanza privata**, verificaretutti i dati/schema/file/digestoriginali e tempirecovery. Non sovrascrivere né produzione né copiafallita. Misurare durataacquisizione/restore/DDL per finestra. DBconrealdati mai espostobrowserpubblico; provebrowserstabilitàusanosintetici.

## Rilascio Laravel12 preparato, non autorizzato qui

Prerequisiti: futuroSHAapprovato/pubblicato, candidatoidentico, gateR0/R1/R2+stabilità, confinevhost/provider e finestra manutenzione autorizzati. Installer no-dev da lock12, minimoPHP8.3/estensioniCLI+FPM, conservazione.env/APP_KEY e upload/archivi. Non eseguire supervisorora: anche preflight fetch/scratch/HTTPapp effettua operazioni oltre le sole letture.

**Rilievo concreto della review:** BUILD_HASHES di scripts/deployment/deploy.py non corrispondono a package.json e package-copy-config.json verificati nel candidato (due hash obsoleti); il supervisor attuale rifiuterebbe il preflight. Nessun bypass o aggiornamento hash cieco. Serve review dei nuovi targetbuild e riallineamento delbundle in copiaisolata con proveguardrail prima dell'uso. Non modificato codice in questa task di preparazione. Manifest372 corrisponde all'indice, diffcheckapp/test/composerPASS; storici conservati. Globale non ripetuta.

**Ritiro asset:** nuovo public/build deve essere costruito da directoryvuota in scratch, validatomanifest eSHA/versioni, nessunCKEditor. Durante manutenzione spostare **l'intera vecchia public/build** neldepositoprivato fuoriDocumentRoot e installare l'intera nuova directory sullo stessofilesystem; nienteoverlay, copia aggiuntiva o --delete supublic/storage. Conservare tutti gliassetvecchi per recovery, senza lasciarli sottoURLpubblici. InventarioURLstoriciCKE/js/map/translations: devono diventare404/410, non200, inclalias/CDN/proxycache; autorizzare eventualepurge separato. Vite outDir non è da solo criterio di ritiro.

Postdeploy preparato:503manutenzione effettiva; SHA/runtime12/no-dev/platformPASS; esattamentestorico7; .env/APP_KEYhashinvariati; credenzialilette senzaesporle; FK/archivi/integrità; login/reset/gestionale/ISTAT/tassa/import/Cestino/token tenant/429RetryAfter e aliases su syntheticprivati; assetsnuovi200hashcorretto/CKE404; deny.env/git/dump/log/uploadPHP, debugfalse/cacheeffettiva, registrazionecontatti/perimetropilota, egressproviderSMTPbloccato;0nuoviSIGSEGV/5xx. QualsiasifallimentoSTOP e503, niente trasmissioni.

Rollback: mai migrate:rollback. Prima apertura e senza nuove scritture, recupero coordinato vecchiocodice/vendor/build/config+snapshotDB/file dimostratoR2, APP_KEYinvariata. Ripristino vecchioCKE non autorizza riapertura pubblica: mantenereperimetrochiuso. Dopo nuove scritture non restaurarevecchioDB cancellando dati: mantenere503, preservaresnapshotaggiornato e riconciliare; richiede decisione separata. Nessunfastforwardinverso/reset distruttivo automatico. Vecchiocodice sullo schemanuovo non presunto compatibile: provarlo sulclone o usare restorecoordinato.

## Prossima operazione esatta richiesta per autorizzazione

**PacchettoA: scritture esclusivamente riservate** per acquisire dump delDBindicato+archivi/config/APP_KEY, seconda copiacifrata, creareistanzeMariaDBprivate/socketsenza rete, restaurare e verificareR0, poi solo sePASS provare le7migrazioni sulclone e recoveryR2; creare scratchisolato per matriceCLI/FPM sintetica. NessunascritturaDB/schemaoperativo o deploy. Richiede conferma finestraconsistente senza scritture; se occorre imporre manutenzioneoperativa, autorizzarla esplicitamente prima, altrimentiSTOP. Criteriodisuccesso:R0/R1/R2 e stabilitàPASS, verbalisanitizzati/tempiehash, originals/segreti preservati, istanze isolate e nessuncontatto provider. UnFAIL ferma la fase successiva.

BASELINE IN AUDIT — NON VALIDATA, accettazione locale positiva conservata; nessuna validazione produttiva.


## Esito successivo conservato separatamente

Il primoFAIL resta intatto. [Nuovo tentativo eproveeffettive](BACKUP-RESTORE-STABILITA-DESTINATARIO-2026-10-09.md):31,661s,backup/R0/R1/R2PASS,CLIstandardSIGSEGV,FPMfermato. Nessundeploy omigrationproduttiva.

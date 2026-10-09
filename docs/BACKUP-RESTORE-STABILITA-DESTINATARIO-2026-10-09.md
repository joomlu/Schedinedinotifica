# Backup, restore e stabilità destinatario — 9 ottobre 2026

**Backup coordinato e recupero isolato positivi; runtime CLI standard bloccato da SIGSEGV riprodotto.** Nessun deploy o migration produttiva. Accettazione locale del candidatoLaravel12 conservata, BASELINE IN AUDIT — NON VALIDATA; non validazione produttiva.

## Diagnostico corretto prima della nuova finestra

Nuova utility `scripts/deployment/backup_http.py`, strumento di audit/backup, non codice applicativo né esenzione delsupervisor. Codice503, marker esatto e Retry-After60 sono controlli distinti; headername case-insensitive secondoHTTP, valore e presenza non allentati, duplicati rifiutati. Ogni fase crea directory esclusiva con body.raw,headers.raw,codice.raw,stderr.raw,esito.json e durata; riuso dello stesso nome rifiutato. Regressioni4PASS: caseheader/valore/marker/code, ritirobarriera sufallimenti, riapertura senza sovrascritture, timeoutconprova preservata. Il primo tentativo fallito e suo limite di conservazione restano invariati.

## Finestra effettiva e backup

Il9ottobre **20:39:48–20:40:20UTC**, durata misurata **31,661secondi** dall'avvio della chiusura alla rimozione. OriginIP217.174.149.23, TLSverificato:503/marker/Retry-After60/trasporto tuttiPASS, drenaggioPASS, monitor senza scrittori rilevati. Due dump singletransaction ordinati byte-identici; inventari file prima/dopo identici. .env/APP_KEY sigilloPASS. Riapertura /loginHTTP200, nessunbypass; barriera rimossa, niente restart/stop/lockDB.

| Artefatto riservato | Byte | SHA256 |
|---|---:|---|
|db.sql|21.562.240|f7687e42c07b1ad2cea75535b67520aa3a17c433d3d9791f8d776ce3943228ca|
|app.tar.gz|288.233.177|55ff46beb876bd1761ed4799b4c854fd08fb9b8c63d56e8aa6c7d467f2083bb4|
|config-server.tar.gz|31.615|8f0806fa534d7d819dd30719e0a5da39592d1eaf02ff5dc423b4eaa66529c99f|

Deposito nuovo server sotto /home/tanggosoftware/backups, fuoriDocumentRoot,0700/files0600. Preserva interaapp,DB,storage/upload/archivi,configapp e.env/APP_KEY; archivio tecnico separato vhost/FPM/ini/moduli e riferimentiTLS presenti. RisposteHTTP/raw/log privati conservati per fase, nessunbackup/chiave/configsegreta inGit. Seconda copia sulMac in Backups/Schedine/Destinatario, cifrataAES256/PBKDF2 con chiave separata in ChiaviRiservate,0700/0600. Decifratura streaming e hash di tutti i3artefattiPASS, senza contenuti inchiaro persistiti nelMac.

Limiteconsistenza: nessunjob/worker/eventoDB otransazione osservato, barriera e doppiosnapshotPASS; non equivalente a lockglobaleDB contro writeresterni non noti. Nessuno scrittore apparso nel monitor, nessun mutamento finale rilevato. Non introdotto lockDB o stopservizi.

## R0/R1/R2 effettivi

| Requisito | Evidenza | Risultato e limite |
|---|---|---|
|Namespace|BubblewrapUID1002, filesystem operativo assente, namespace rete senza routeesterne, socket/datadir/PIDprivati; nessun cron/worker avviato|PASS; backupdeposito riservato resta distinto dallaworkspace0700|
|R0DB|58tabelle, import1,235s, ridumpSHA identico albackup originale|PASS, dati/schema/storico rappresentati nel dump completamente identici|
|R0file/configapp|16.516file/link confrontati perSHA/byte/target, inclusi.env/APP_KEY|PASS; configurazioneserver conservata inarchivio, non applicata a servizi|
|R0credenziali|25campivuoti,3plaintextlegacy,0contenitoricifrati preesistenti|PASS sulcampione; nessuna decifrabilità preesistente inventata|
|R1sette migration|Tutte7exit0 inordineapprovato,7registrazioni aggiuntive, nessunaaltra|PASS_DDL;solo clone, circa2,327s sommamigration|
|R1integrità|57tabelleoriginali confrontate sucolonne preesistenti eHMACmultiset; migrationhistory separatamente controllato|PASS;0normalizzazioni vuoto/null, nessunvalorebusiness alterato|
|R1credenziali|3valori cifrati decifrati e confrontati conplaintextlegacy;3cast realiStruttura verificati|PASS senza valori inoutput; APP_KEYoriginale invariata nellecopie|
|R2snapshot completo|Terzaistanza+nuovaestrazionefile, import1,046s, ridumpSHA identico;58tabelle e16.516file/link;credenzialilegacyoriginali|PASS;clone migrato eproduzione non sovrascritti; nessun rollbackdown|
|StabilitàCLI|Lint sintetico minimo conconfigstandard PHP8.3.35+OPcache+ionCube|FAIL riproducibileSIGSEGV(-11)|
|AttribuzioneCLI|Stessofile:OPcacheCLIoff exit0;ionCubeoff exit0;entrambioff exit0|Tre probe positivi, non prova di stabilità di un rimedio|
|FPMprivato/regressioni stack estese|Arresto della fase successiva dopoSIGSEGVstandard|NON ESEGUITI; nessunserviziooperativo modificato|

Setupfalliti preservati: errorequote locale primaSSH; mountroot/UIDmappingPermissionDenied primaimport; inizializzazioneR0failperresolverlocalhostassente. Corretti esclusivamente argomenti/UID di avvio ehostsprivati, con datadirR0bnuovo e namespaceancorasenza rete. Nessun allargamento permessideposito o mountproduzione. Tutte le istanzeMariaDBprivate fermate dai rispettivicontroller, dati/log conservati. I core di memoria non esportati; RLIMIT_CORE0 nelleproveCLI.

## SIGSEGV e decisione rimanente

File sintetico `<?php echo 'sentinella';` senza Laravel/DB causaSIGSEGV inlint standard. Insieme ai due stackstorici php_lint_script→ionCube→OPcache, evidenza coerente di incompatibilità della combinazione nelperimetroCLI; non attribuita al codice Laravel12. Non dedurre che FPMcrashi o sia stabile. Non modificateini operative, loader, servizi o supervisor perdisabilitare controlli.

Serve scelta tecnica controllata: rimedio fornito dallamantenutore delruntime/loader oppure configurazioneCLI dedicata senzaOPcache, poi matriceestesa e provaFPMprivata. La singolaprobaCLIoff non autorizza adozione produttiva o chiusuraB2. FPM non provato perché era la fase successiva alFAIL.

## Stato dei blocchi e candidato

B1: nuova utility audit/regressioni/evidenze integrate nel candidato, precedente389recuperabile; futuroSHA/commit/pubblicazione non autorizzati. Nessuna globaleLaravel ripetuta: nessunbusinesscode modificato.

B2: **BLOCCATO daSIGSEGVCLI standard confermato** eFPM non verificato. Occorre rimedio approvato e stabilitàestesa sulruntime effettivo.

B3: acquisizione/restore/decifrabilitàcampione/setteDDL/integrità/ritornosnapshot **PASS nelperimetroisolato provato**. Restano verificheflussi sullo stackdestinatario dopoB2 e finestraoperativa finale, allineamento eventualinuovimigration/dati e rollbackdopolenuovescritture non autorizzato. Recoveryconfigserver archivio preservato, nessunrestoreOS/FPMconfig suservizio provato.

B4: vecchioCKEditor pubblicoesiste ancora, artifactreplacement e confineprovider/SMTP/accessipilota sono preparati, nondeployati. Non aprire alpubblico il vecchioasset; niente trasmissioni.

Esiti tecniciJSON in evidenze/backup-restore-destinatario-2026-10-09. Primo fallimento intatto, nessuna prova mascherata. BASELINE IN AUDIT — NON VALIDATA; accettazione funzionale locale positiva conservata.

# Diagnosi ripetuta PHP CLI e prova FPM preparata — 9 ottobre 2026

**Crash del lint CLI standard riproducibile; rimedio per-processo OPcacheCLIoff positivo nel campione ripetuto. FPM non ancora provato.** Nessuna configurazione operativa modificata. Candidato412 e backup/R0/R1/R2 preservati; accettazione localeLaravel12 positiva conservata, BASELINE IN AUDIT — NON VALIDATA.

## Configurazione effettiva e confronto ripetuto

PHP CLI8.3.35/Zend4.3.35, RPMcli/fpm/opcache8.3.35-1.el10.remi, ionCubeLoader15.5.1 da php-v (funzioneioncube_loader_version restituisce15.5). Ini /etc/opt/remi/php83/php.ini, scansione01-ioncube_loader.ini prima10-opcache.ini, ordineZendregistrato ionCube poiOPcache. `opcache.enable=1`, `enable_cli=1`, jittracing ebuffer0 nella standard: non provata esecuzioneJIT. NamespaceUID1002, nessunfile/socket operativo montato, rete senza routeesterne, corelimit0. Versioni/config/ordine/elencoini e ogni exitcode/tempo in matrice-80-prove.json; stdout/stderrindividuali nel deposito privato, niente memoria core esportata.

Riproduttore minimo: `<?php echo 'sentinella';` sottoposto a `php -l`, senza Laravel, DB ocontenuti personali.20avviifreschi perconfigurazione,80in totale:

| Variante | Cambiamento solo privato | Esiti positivi | SIGSEGV |
|---|---|---:|---:|
|A|Ini/moduli standard|0/20|20/20,exit-11|
|B|Stessa ini eionCube, `-d opcache.enable_cli=0`|20/20|0|
|C|Scansione privata senza01-ioncube, OPcacheCLIancora1|20/20|0|
|D|Scansione privata senzaionCube néOPcache|20/20|0|

**Causa dimostrata nel perimetro:** lint CLI delbinario/versione osservati con ionCube+OPcacheCLIattivo fallisce deterministicamente nelcampione; eliminare uno dei due fattori evita ilcrash. I duecorestorici mostravano zend_get_file_handle_timestamp→opcache_compile_file→persistent_compile_file→ionCube→php_lint_script. Coerenza forte tra sintomo corrente e stackstorico, non prova che tutti49eventistorici abbiano stessacausa. Non attribuire bug allaapp o alJIT; non individuata la specifica correzione upstream o il componente responsabile internamente, non provati altriordini/versioni oloadlungoperiodo.20crash sono riproduzioni deliberate suinput sintetico, non crash dei servizioperativi.

## ionCube e comandi effettivi del candidato

Scansionati8.641filePHPdelcandidato/vendorno-dev: nessunstubencoded rilevato nelleintestazioni, nessunriferimentoapp e nessunrequireComposerionCube. È uno screening, non una certificazione di qualsiasi artefattoesterno. Evidenza funzionale: comandi necessari sottoC/D senza loader passano. Nelperimetroinstallazione/operazioneCLI provato **ionCube non è richiesto**. Non dedurre che possa essere rimosso dall'host che servealtreapp.

Copia autonomacandidata12.69.3, vendor84pacchettino-dev, DBclone migrato sul socketprivato. Mailarray, providerfalse, cron/worker/scheduler non avviati e namespace senzaegress; ComposerDisableNetwork1. Conservati rawlog inprivato. PerB/C/D15comandi,10catenecomplete ciascuna: **450/450exit0**, nessunSIGSEGV. Stesso comandoComposerinstall ripetuto con vendor disponibile offline; non certifica il download originario dalregistry.

Comandi: Composer --no-plugins validate --strict --no-check-publish; check-platform-reqs --no-dev; install --no-dev --no-scripts --no-interaction --prefer-dist; Artisan package:discover;config/route/view/event:clear e:cache;migrate:status;route:list;schedule:list. Nessunmigrate,queue:work,schedule:run oinvio. PianiCLI/cacherealmente generati sulclone, non soli -v oautoloader. IlDBprivato è stato fermato dopo prove, backup ecloneR0/R2 non modificati. Non eseguita una nuova suiteglobale applicativa: businesscodeinvariato e questagatediagnosticamirrata.

### Blocco distinto nel runner del rilascio

`scripts/deployment/artisan.php:137` richiede ancora `str_starts_with($app->version(), '11.')`. Copiaidentificatacon solaosservabilitàdelcatch, validatoriimmuti e.envcloneproduction/URLatteso: exit1, causaprivataesattamente `Laravel 11 required.`. L'originale resta intatto.450PASS riguardano Composer/Artisan nativi, **non** l'interopreflightsupervisor. Ilvecchiogate deveessere riallineato alframeworkdelcandidato prima diusarerunner; non eliminato né bypassato.

## Correzione minima proposta, non applicata

1. **scripts/deployment/deploy.py:** introdurre un'unica costruzione controllata degliargvPHP con `[self.php, '-d', 'opcache.enable_cli=0', ...]`, usata perogni invocazione PHP Composer/Artisan/check. Non global.ini, nonphp-n, nonrimozioneestensioni, nontolleranzadeglierrori. Testare chequei realiargv mantengano0 (rifiutare flag successivi in conflitto) e .env/APP_KEY/path/stopguardimmutati. PerCLIoperatore documentare lo stesso prefisso nella **docs/DEPLOY_SPANEL.md**, non alterare PATHglobale. Candidatebind/runnerbundle/hashrichiedono review normale.
2. **scripts/deployment/artisan.php:** sostituire soltanto ilgate11 colgate esplicito della versione accettata12.69.3 (oppure vincolomajor12+minimo12.69.3 se siapprova quella politica), mantenendo tutte le identità e postcondizioni. Non è rimedioSIGSEGV: è compatibilitàdistinta delrunner. **tests/Deployment/test_deploy_guards.py**: regressioniargvCLI/rigetto11e13/versionemismatch e stoperrore. Testare ilrunnercompleto nelclone, non soltanto lint/Artisannativo.
3. **Nessun file /etc modificato**, nessunoverridepoolFPM proposto sulla sola provaCLI. `/etc/opt/remi/php83/php.d/01-ioncube_loader.ini` e10-opcache.ini rimangono invariati. Bèpreferibile come interventominimo perCLIdelprogetto: conserva ionCube peraltreapp eOPcacheweb, evita rimozionehostglobale.

Verifica prima adozione: ripetere80lint,450comandisulclone e regressionsupervisor dopo ilpatch; provaFPMprivata descritta sotto e flussiaffetti primaaccettazioneB2. Rollbackdelsolo patch: recuperare deploy.py/artisan.php/guardiaprecedenti dalcandidato412 inuna copiaisolata; CLIoperatore togliere ilprefisso senza alterareini. **Ilrollback torna alcrashstandard/gate11, quindi nonconsente deploy.** Mai usare rollback perriaprire unperimetro non verificato. Patch non integrata nelcandidato attuale.

## FPM: risorse e procedura esatta prima dell'avvio

**Preparata soltanto; processoFPMprivato non avviato inquesta task.** Nessuncgi-fcgi disponibile nelPATHserver: clientFastCGI minimalePython su socketUnix, da verificare prima contro responder sinteticopermetricheHTTP. Nessuninstallpacchetto o servizioApache.

Risorseproposte nello stesso namespaceBubblewrap senzaegress e senza produzione montata:

- Nuovadirectoryworkspace0700 `fpm-diagnosi-v4`, PHPini/scansioni copie private, pool `.conf` privato; UID/GID1002/1003.
- Binario /opt/remi/php83/root/usr/sbin/php-fpm con `--nodaemonize --fpm-config /work/fpm-diagnosi-v4/pool.conf -c /etc/opt/remi/php83/php.ini`. Nessunuso dei poolin/etc/php-fpm.d.
- Solo socket `/work/fpm-diagnosi-v4/fastcgi.sock`, modo0600, pid/log nella stessa directory. **Zero porteTCP**, nessunDNS/SMTP/entiexterni; nessuna esposizionevhostpubblico. namespace reteprivate econsole-client socket.
- pmstatic,max_children2, memory_limit128MiB perworker, max_requests100, request_terminate_timeout10s; massimo4clienti per200richieste sintetiche. Budget10minuti perprova,1corelimit0; nessuna modifica cgroupoperativa. Massimo circa256MiB PHPworkerpiùmaster/OPcache (allocazionediquest'ultimo da misurare); non promettere RAMtotalelimitata256MiB. controller applica timeout e registra RSS/signal; stop alprimo crash/5xxanomalo.
- PrimaPHPsentinella/OPcachemetadata priva disegreti,20warm e20cold; poiURLlogin/flussi pertinenti Laravel12 conDBsintetico nuovo datadir/socketprivato,APP_KEYsintetica,non ilDBreale restaurato. VarianteFPMstandard edeventuale privata senzaionCube perattribuzione; opcache.enable_cli0 non cambiaopcacheweb, pertanto non presume correggereFPM.
-200richiestea4clienti,10cicli divita **solofiglioFPMprivato**, tempi/errorisignal/RSS,body/headercode perrichiesta separati e finali hash; nessunsystemctl/restart serviziooperativo o alterazionepoolsocketesistente.
- Pulizia: controllarePID/PPID/eseguibile/datadir/socketprima terminate/wait deisoli figli creati; rimuovere soltanto socket/PIDdellaprova dopo uscita verificata, **conservare** config/log/risultati/snapshot. SuFAIL fermarefasisuccessive e niente riparazione configoperativa. Check nessunfiglioprivatorimasto e socketoperativo immutato.

CriterioFPM: zeroSIGSEGV/abort/5xximprevisti, risposte/tenant/session/rate-limit/CSRFattesi, entrambisetupcreati e cleanupdimostrato, configurazioneeffettiva/hash/moduliperogni pool. Non usareHTTP200dellapaginaoperativa comeprovaFPMstress. Risorse e impatto devono essere presentati prima dell'avvio; questa task prepara la prova senza avviarla. Nessun rimedio FPM operativo deriva automaticamente dai risultati CLI.

## Verdetto

Backup/R0/R1/R2 conservati. B2parzialmente avanzato: fattoreCLIriprodotto e percorsoCLIprivatoB20lint+150comandiPASS, FPM e runnerallineatoancorapendenti. B1futuroSHA eB4asset/confineproduttivo separati. Nessuna accettazione produttiva, configurazione operativa, restart, migrationproduttiva, commit/push/deploy o trasmissione.

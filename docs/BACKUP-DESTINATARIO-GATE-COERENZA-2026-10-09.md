# Backup destinatario: gate di coerenza — 9 ottobre 2026

Autorizzata acquisizione riservata e prova isolata, **non autorizzata la barriera di scrittura**. Backup coordinato non iniziato; niente dump parziale presentato come recuperabile. Riferimento operativo: OPERAZIONE-PROTETTA-DESTINATARIO-2026-10-09.md.

## Preflight effettivo

Due campioni read-only non mostrano processi UIDtanggosoftware, workerqueue, connessioni o transazioniDB applicative attive. Nessun riferimento applicazione/artisan/tanggosoftware nei file cron.d/spool esaminati; crontab utente senza righe, Kernel.php senza chiamate $schedule->. Timer sistema osservati non sono job Laravel. EventiDB0, event_schedulerOFF. Limite: cron/script indiretti, client esterni e traffico nuovo non esclusi; assenza momentanea non è garanzia. Non letti argv indiscriminati né query/dati personali.

Manutenzione assente, sito pubblico attivo: può scrivere sessioni/cache/log/export/dati durante acquisizione. Singletransaction garantisce il punto DBInnoDB, non il legame con file.58tabelle,26.8MiB stimati da catalogo; storage24MiB, immagini3MiB,build188MiB,vendor58MiB. Dimensioni indicative, non durata misurata. Nessuna scrittura remota eseguita.

## Intervento preciso da autorizzare

Finestra proposta **massimo10minuti**, previsione2–5minuti dato il volume osservato, non promessa. La prova restore/stabilità avviene dopo la riapertura e non estende questa finestra.

1. Concordare assenza di operatori/processi esterni e DDL nella finestra. Ricontrollare cron/eventi/processi/connessioni; se emerge uno scrittore non previsto STOP, niente kill/stop/bloccoDB automatico.
2. Come proprietario tanggosoftware, tramite copia riservata del solo bundle verificato, chiamare esclusivamente `Maintenance.close()` (non prepare/execute del deploy): controlla hash esatto index c96ed6bbe80f9105534af4d1510dc79f0143e6843970dba7ddd4ec7d03097cab, genera segreto/marker privati e scrive atomicamente **solo** storage/framework/maintenance.php e down. Niente artisan down, modifica index/configPHP/vhost, systemctl, cookiebypass o deploy. .env/APP_KEY sigillati; nessun servizio fermato.
3. HEAD/GET /login all'origine reale217.174.149.23 conTLSverificato, senza bypass:503,RetryAfter60,marker corretto e nessun bootstrapLaravel. Attendere drenaggio richieste già entrate massimo120secondi; verificare processiFPM/DBtransazioni senza contenuti e nessuno scrittore. Se non drenano STOP e richiedere decisione, non terminare processi.
4. Acquisire backup riservato DB+file/config nel percorso univoco predisposto secondo il piano, SHA/byte/perms e sigilloAPP_KEY; snapshot catalogo/HMAC e metadati file prima/dopo, nessuna DDL o writer concorrente. Certificare consistenza solo con barriera e drenaggio verificati. Backuppare anche lo stato manutenzione come tale; conservarlo, il restore privato non deve pubblicarlo.
5. Entro10minuti o prima: se sigilli/barriera integri, chiamare esclusivamente `Maintenance.open()` sulla stessa istanza/marker per rimuovere solo i due file da essa creati e verificare ritorno alla precedente pagina. Su fallimentobackup ma sigilli integri riaprire, conservare copia incompleta marcataFAIL e fermare restofasi. Se sigillo/barriera alterati non cancellare file di altri processi o riparare segreti automaticamente: STOP/escalation con503 e durata effettiva comunicata; la scadenza non autorizza una riapertura non sicura.

La503 blocca le richieste nuove alfrontcontroller, non un writerCLI/DBesterno: per questo serve conferma operatore e ricontrollo. Se serve fermare un processo o bloccare scrittureDB, questo piano non lo autorizza. Nessun lock globaleDB o cambio account introdotto.

**Criterio di successo:**503 primaLaravel e drenaggio, copia coordinata completa+hash/perms, APP_KEY/.env immutati, finebarriera verificata entrofinestra. Solo allora R0restore, R1sette migration/R2ritorno snapshot e stabilità nel namespaceprivato già autorizzati. Nessuna migration/restauro sulDBoperativo; nessunprovider/SMTP/task reale nellecopie.

## Hash supervisor: ambito e verifica

Letti i diff effettivi: package.json mantiene clean rimraf public/build e build clean→Vite→verify; varia soltanto dipendenze accettate/Sassdichiarato. package-copy-config.json escludeCKEditor dalla whitelist senza nuovi percorsi; Vite invariato copia solo risorse sotto public/build, verify-architecture invariato. Questi sono precisamente i due file che BUILD_HASHES deve sigillare; mantenuti gli altri hash, INDEX_HASH e tutti i rifiuti sui percorsi/override/symlink.

Riallineate esclusivamente le due costanti del supervisor. Il primo test66 conserva FAILpackagehash e PermissionErrorloopback sandbox; non nascosto. Ripetizione autorizzata fuori sandbox con solefixture sintetiche valuta il gate build_contract positivo e rifiuto di ogni file alterato, percorsiComposer/override, sigilloAPP_KEY,503statica e stop degli errori. Non è esecuzione del preflight produttivo: quello fetch/scratch/bootstrapHTTP richiederebbe operazioni ulteriori; non eseguito. Esito effettivo nel log supervisor-dopo.log.

Candidato precedente372 conservato come patchbinaria+manifest prima del nuovo staging; nessun commit. Accettazione locale positiva conservata. Restore/decifrabilità/sette migration/recovery/stabilità non eseguiti perché gatecoerenza in attesa; non dichiaratiPASS.

BASELINE IN AUDIT — NON VALIDATA; nessun deploy, commit/push, restartservizi, DDLproduttivo o trasmissione.


## Esito successivo conservato separatamente

Il primoFAIL resta intatto. [Nuovo tentativo eproveeffettive](BACKUP-RESTORE-STABILITA-DESTINATARIO-2026-10-09.md):31,661s,backup/R0/R1/R2PASS,CLIstandardSIGSEGV,FPMfermato. Nessundeploy omigrationproduttiva.

# Mitigazione CLI e verifiche FPM private — 9 ottobre 2026

**Mitigazione del runner verificata nel perimetro isolato; lancio effettivo ancora BLOCCATO.** Accettazione locale applicativa Laravel 12 conservata. BASELINE IN AUDIT — NON VALIDATA. Nessun commit, push, deploy, migrazione produttiva, configurazione o servizio operativo modificato; nessuna trasmissione esterna.

## Obiettivo, modifiche e rischio

Obiettivo: evitare l'interazione ionCube/OPcache CLI riprodotta e rendere utilizzabile il gate del candidato approvato. File: `scripts/deployment/deploy.py`, `scripts/deployment/artisan.php`, `tests/Deployment/test_deploy_guards.py`, documentazione e manifest. Rischio MEDIO: strumenti di rilascio; nessuna modifica alla logica applicativa.

Ogni invocazione PHP attraverso `Deployment.project` aggiunge `-d opcache.enable_cli=0`; gli override espliciti in conflitto e `-n` vengono rifiutati. Sigillo `.env`/APP_KEY verificato prima e dopo; nessuna modifica ini globale. Composer, probe PHP e Artisan passano dallo stesso punto. Il gate accetta esattamente **12.69.3**, respinge 11.31.0, 12.69.2 e 13.0.0; identità, path, MariaDB, comandi ammessi e postcondizioni conservati.

Ulteriore difetto del runner confermato dalla sequenza: il primo `config:clear`, prima di Composer, avrebbe ancora caricato il vendor operativo 11 e fallito il nuovo gate. È stato spostato **dopo installazione Composer e check-platform-reqs**, sempre dietro barriera statica. Composer non avvia l'applicazione: `--no-scripts` e `--no-plugins` restano obbligatori. Nessun controllo eliminato.

## Composer e comandi operativi

Misura attraverso il vero `Deployment.project` sul binario remoto nel namespace: **20/20** configurazioni `opcache.enable_cli=0`, ionCube e Zend OPcache ancora caricati; sigillo immutato. Prova sintetica `@php`: il figlio Composer vede **1**, non eredita il `-d` del padre. Non dichiarare propagazione del rimedio ai subprocessi.

Il runner attuale usa solo version/validate/check-platform-reqs/install, con plugin disabilitati e install senza script; package:discover viene eseguito separatamente con PHP mitigato. Non avvia worker o scheduler. Comandi operatore da usare, soltanto nel contesto autorizzato:

```sh
/opt/remi/php83/root/usr/bin/php -d opcache.enable_cli=0 artisan <comando-autorizzato>
/opt/remi/php83/root/usr/bin/php -d opcache.enable_cli=0 /usr/bin/composer --no-plugins install --no-dev --no-scripts --optimize-autoloader --no-interaction --prefer-dist
```

`composer run-script`, plugin, script `@php` e wrapper che avviano nuovi PHP **non sono coperti**: richiedono prefisso esplicito sul figlio e nuova verifica prima dell'uso. Il flag non disabilita OPcache FPM. Le 450 invocazioni native e la matrice 80 del rapporto precedente restano storiche e non sono sommate a queste prove.

## Matrice delle verifiche effettive

Ambiente server: PHP CLI/FPM 8.3.35, ionCube 15.5.1, OPcache 8.3.35, MariaDB 11.8.9, Python 3.12.14. Namespace Bubblewrap UID1002/GID1003 senza rete esterna né mount/socket operativi, corelimit0; socket Unix privati modo0600, directory0700. Mac usa vendor no-dev12.69.3 dedicato, senza cambiare vendor Herd11.31.0.

| Requisito | Evidenza | Risultato e limite |
|---|---|---|
| Guardie e versione | guardie-runner-finale.log; guardie-linux-finale.log | 71 raccolti Mac: 68 PASS/3 SKIP Linux; Linux 71/71 PASS. Cache Artisan reali su fixture, versioni non approvate respinte, segnali/daemon/lock/path/segreti e fail-stop provati |
| Sequenza completa runner | test_complete_sequence_and_isolated_snapshot_recovery | PASS su fixture: Git, Composer/npm, HTTP e migration simulati; argv mitigati, install prima del gate, asset/path/sigillo reali. Non è deploy con Git reale |
| Recupero | stessa regressione; R2 completo già attestato | Snapshot fixture ripristinato byte per byte; R2 precedente DB58tabelle +16516file PASS. Nessuna nuova modifica DB giustifica ripeterlo. Il runner non ha rollback automatico |
| Configurazione CLI e figlio Composer | cli-config-composer-figlio.json | 20/20 CLI0; figlio sinteticoCLI1 dimostra il limite, install protetta da no-scripts/no-plugins |
| FPM standard | fpm-sentinella-200.json | 200 richieste,4client,10 avvii privati,0crash/risposte inattese; ionCube+OPcache web attivi. Non prova stabilità indefinita né risoluzione di tutti i crash storici |
| Laravel sotto FPM | fpm-laravel-40.json | 40 richieste su nuova base soloDDL senza dati reali: login200, POST senzaCSRF419, anonimo dashboard302;2 avvii privati |
| IP condiviso | fpm-ip-330.json | 300 lookup404 +30HTTP429 conRetry-After positivo,4client e2avvii privati: contatore file persistente fra worker/processi |
| Alias/quote | fpm-contatori-92.json | 92richieste: letture1–60ammesse/61–62HTTP429; POST1–20CSRF419/21–22HTTP429; altro ospite/stessa struttura e altro IDstruttura conservano quota. Alias32/full64 condividono contatore; righe immutate. Struttura/schedina non popolate,404atteso: prova contatori, non flusso tenant completo |
| Pulizia | manifest FPM | 15 master privati terminati conexit0 nei quattro campioni; DB privati fermati, socket propri rimossi; nessun restart operativo |

Totale richieste FPM di questi quattro campioni: **662**; selezioni diverse, nessuna suite globale applicativa ripetuta. La suite precedente750/8252 e Chromium restano valide per codice applicativo invariato. Nessun nuovo test browser applicativo qui: client FastCGI su socket Unix, non Chromium.

## Fallimenti conservati e limiti

Prima suite Mac: vendor Herd11 invece del candidato12 → 12failure/1error/3skip, log originale conservato. La selezione esplicita DEPLOY_TEST_VENDOR riallinea il test al candidato, senza cambiare aspettative. Primi allestimenti Linux mancavano public/index.php e poi package.json: errori di fixture, corretti soltanto nelle copie; riscontri nella cronologia strumenti, log remoto superstite e suite finale. Un nome storico riutilizzato dal controller ha sovrascritto un log intermedio di allestimento: non considerarlo conservato integralmente; i log applicativi originali e A14 non sono stati toccati. Le rotazioni successive usano nomi univoci.

FPM v5: build assente, arresto prima di processi. FPM v6: installazione da DB vuoto fallisce nella migrazione storica `2026_02_28_000001_normalize_classificazioni`, SQLSTATE42000/1072, dropForeign di tipologia_struttura_id su MariaDB11.8. Log e manifest conservati; FPM non avviato e DB privato fermato. **Installazione MariaDB da zero non verificata**; non correggere quella migration implicitamente. Il rilascio previsto aggiorna il destinatario esistente, dove questa migration è già registrata e le sole sette pendenti hanno superato R1. Per FPM v7–v9 nuova base vuota caricata col soloDDL della copia R1, nessuna riga reale né APP_KEY reale. Non presentare questa scelta come una correzione del fallimento da zero.

Mancano la misura RSS sistematica richiesta dal piano, le attestazioni individuali cold/warm OPcache, sessione autenticata/tenant e flussi operatore completi sotto FPM, concorrenza sulle quote per richiesta (IP generale concorrente provato), clock/proxy/TLS e configurazione vhost produttiva. Queste evidenze limitano B2; il solo campione senza SIGSEGV non chiude gli eventi storici.

## Candidato, recupero e prossimo gate

Candidato412 precedente recuperabile tramite manifest, tre file precedenti e patch completa nel deposito locale riservato indicato da riferimento-candidato-412.json; HEAD invariato. Ripristino del solo delta strumenti possibile su copia da quei file: ripristinare deploy.py/artisan.php/test, controllare hash, non riaprire automaticamente. Il vecchio runner torna al gate11 e alla configurazione CLI che riproduce il crash: non è un percorso di rilascio accettabile.

**B1:** serve SHA completo approvato/pubblicato, poi clone Git pulito e prova prepare/execute contro origine isolata conforme. Non crearlo sotto il divieto di commit/push; nessun bypass delle guardie. **B2:** completare i flussi FPM sopra e delimitare l'attribuzione degli eventi storici; nessuna modifica operativa derivata implicitamente. **B3:** backup/R0/R1/R2 restano PASS nel loro perimetro, nuova installazione da zero separata. **B4:** gate asset obsoleti/provider/vhost prima apertura ancora separato. Nessuna validazione produttiva.

Verifica finale:445file nel candidato, hash del manifest corrispondenti allo staging eHEADinvariato; nessunFPM/MariaDBprivato residuo. Diff-check dei sorgenti modificatiPASS. Diff-check globale segnala spazi finali nei log/patch storici e nel log sintetico originale: conservati senza normalizzare evidenze.


## Completamento FPM autenticato e Git reale — 9 ottobre 2026

[Verbale effettivo](COMPLETAMENTO-FPM-GIT-2026-10-09.md). FPMprivato:1084richieste/20cicli coldwarm e81logout/revoca/alias/relazionetenantPASS; caricosostenuto985richiestetotali,904in120,44sec/4client,10workerconriciclo,7262campioniRSS e49,949MiBpiccoworkerPASS. Sessioniindipendenti/persistenti,clientiestranei404,CSRF419,righeclienteimmutate,revocacross404/propria302,aliasinvalidati,conversioneanonima,relazioneschedinaestranea404. ZeroSIGSEGV/HTTPinattesi nelcampione; nonrisoluzioneuniversale dei49eventistorici. Originalifalliti conservati:fixturecampiobbligatori eheaderno-store/private distinti dalbugDDL.

Gitrealeread/clone/archive/policyPASS sultreec4e62d5ea70080878f079cae08aa2a787f2621ecdelcandidato445preservato;tree rifiutato comecommit,HEADfaef3cfimmutato. B1restaBLOCCATO:servefuturocommitSHAapprovato/pubblicato eprepare/executeintegrale conquelSHA, nonautorizzati daldivietoattuale. B2proveisolateFPMoraVERIFICATEnelperimetro;gatevhostTLS/proxy/clock/UID/SELinux ecorrelazionestorica separati. B3R0/R1/R2conservati;B4asset/confine/providerancorapendenti.

SQLminimoMariaDB11.8.9riproduce1072:indiceuniquecomposto residuo prima dropcolonna. Rimozioneindice→dropcolonna→unique(nome)PASS solo nelriproduttore;nessunfixmigrationapplicativa. InstallazionenuovaMariaDBBLOCCATA;upgradeprevistononbloccato daquellamigrationgiàapplicata eassente dalle7pending giàPASS. Riconciliare comunque elenconelpreflighteffettivo.

Candidato445precedente recuperabile fuoriGit; rapporto,matrice,Maestro emanifestaggiornati. Accettazionelocaleapplicativapositivaconservata;BASELINE IN AUDIT — NON VALIDATA. Nessuncommit/push/deploy/DBoperativo/configservizi/trasmissione.

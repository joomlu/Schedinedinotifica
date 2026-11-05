# Comprobazioni del destinatario in sola lettura — 9 ottobre 2026

SSH root@217.174.149.23:6543 con tanggo_spanel riuscito, exit0, hostkey verificata; ostacolo autenticazione chiuso. Nessuna chiave privata copiata/esposta. Nessuna modifica server, deploy, migration, backup produttivo o trasmissione. Query limitate a migrations e information_schema in START TRANSACTION READ ONLY; nessun record personale letto. Non avviati artisan, worker, scheduler o bootstrap Laravel sul destinatario.

## Evidenze effettive

| Requisito | Evidenza | Risultato e limite |
|---|---|---|
| Runtime | PHP CLI/FPM8.3.35, ionCube15.5.1, OPcache8.3.35; CLI include PDOmysql/mbstring/intl/xml/zip/curl/gd/soap | MinimoPHP8.3 presente; elenco estensioni FPM e accettazione runtime applicativa ancora da completare in copia isolata |
| Strumenti | MariaDB11.8.9, Composer2.9.2, Python3.12.14, Node20.20.2/npm10.8.2, Bubblewrap0.10.0 | Versioni rilevate; isolamento/prove candidato su MariaDB non eseguiti in questa task di sola lettura |
| Segfault | Kernel29eventi PHP in opcache.so il07/10 dalle18:00:51 alle18:33:18;0eventi dal08/10 nel journal disponibile | Non soltanto i2storici: evidenza completa preservata mediante conteggio/date/modulo. Non dimostrata causa o risoluzione; JIT=tracing con jit_buffer_size0, CLIopcacheOn. Nessuna impostazione modificata; nessun nuovo carico diagnostico rischioso |
| Codice servito | SHA c21b9185a33d1f5e0720c08f1547af6fa4857b20, lockLaravel11.31.0 | Non è il candidato12. Git iniziale come root rifiuta ownership; lettura come proprietario riuscita senza safe.directory globale |
| Schema | Storico migration completo, nessuna registrata estranea ai file candidato;7pending | Pending elencate sotto; non eseguite. ISTATsnapshot e lifecycle assenti dai metadati letti |
| Vhost | DocumentRoot /home/tanggosoftware/repos/schedinedinotifica/public, handler php83, poolUIDtanggosoftware, socket0660 | Confine parzialmente rilevato; non attestati denyuploadPHP/registrazione/contatti/accessi pilota o protezioneIP/lock concorrenti |
| Cache effettiva | production/debugfalse, appEurope/Rome, cachefile/sessionfile securetrue, queuesync, SMTP | .env/cache0600; nuove configQuestura/ISTAT assenti nel codice remoto, relativi valori cache null: non dichiarati flag disabilitati. SMTP non intercettato attestato |
| Asset | CKEditor40.2.0 presente; HEAD statico TLS con IP217.174.149.23 restituisceHTTP2 200,text/javascript,1221595byte | Esposizione remota confermata, non consumer o exploit. Serve sostituzione artefatto pulito autorizzata; nessun deploy ora |
| TLS | Primo HEAD su127.0.0.1 falliscecurl60 per SAN non corrispondente; IPeffettivo confermaTLS eHTTP200 | Primo esito conservato, attribuito al diverso vhost loopback; nessun bypassTLS, non dichiarato difetto certificato pubblico |
| Dev e capacità | public/hot assente; nessun listener8000/5173/8080/9000 nel campione;305GiB disponibili;NTPsync | Non prova assenza di ogni devserver. Host/PHPUTC e appEurope/Rome coerenti se codice usa timezone applicativa |
| Recuperabilità | Directory backup presente0700 | Non dimostra backup completo né restore/APP_KEY recuperabili. Nessun contenuto backup letto o acquisito |

## Migrazioni pendenti esatte del candidato

- `2026_10_06_100000_encrypt_questura_credentials`
- `2026_10_06_110000_add_questura_archive_integrity`
- `2026_10_06_120000_add_questura_retention`
- `2026_10_06_130000_add_questura_send_reservations`
- `2026_10_07_180000_create_tassa_exports_and_receipt_image`
- `2026_10_07_180000_protect_istat_cycle`
- `2026_10_09_230000_add_web_checkin_link_lifecycle`

La presenza nello storico non certifica l'intero schema: lette colonne dei moduli pertinenti, senza DDL. Prima di applicare le7serve backup/restore del destinatario e prova su copia protetta con APP_KEY invariata; nessun migrate indiscriminato o rollback no-op.

## Gate rimanenti

B1: candidato372 Laravel12 già integrato e verificato localmente; review e futuroSHA/pubblicazione ancora separati. Nuove evidenze solo documentali restano fuori dallo staging372 per non alterare il manifest approvabile senza nuovo consolidamento.

B2: autenticazione chiusa e versioni acquisite; restano causa/risoluzione segfault, estensioni/overrideFPM effettivi e accettazione del candidato sullo stack destinatario in copia isolata senza egress. Nessuna suite locale ripetuta senza modifiche.

B3: ricognizione pending completata(7), recuperabilità ancora aperta. Serve evidenza restore autorizzata del destinatario e piano applicazione esatto provato; l'esitoHerd non certifica MariaDBproduttivo.

B4: debugfalse attestato nella cache; aperti vecchioassetCKEditor esposto, flag/trasporti del candidato non installati, SMTPconfigurato, accesso pilota/registrazione/upload/IP/egress e artefatto pubblico da verificare prima apertura. Questura/ISTAT/SMTP richiedono gate separati prima attivazione.

Verdetto: preparazione locale positiva conservata, lancio ancora BLOCCATO perB1–B4 specifici. BASELINE IN AUDIT — NON VALIDATA; nessuna validazione produttiva. Nessun commit/push/deploy o cambiamento operativo.

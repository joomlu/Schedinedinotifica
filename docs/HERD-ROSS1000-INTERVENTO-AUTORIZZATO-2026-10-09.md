# Ross1000 Herd: intervento autorizzato ed esito effettivo — 9 ottobre 2026

**Chiusura positiva del difetto500 locale nel perimetro verificato.** Accettazione funzionale locale positiva conservata; BASELINE IN AUDIT — NON VALIDATA. Nessuna validazione produttiva. Le precedenti diagnosi/rossi restano preservati; questa fase ha autorizzazione esplicita a backup reale riservato, restore isolato e singola migration dopo verifica positiva.

## Backup e restore reali, riservati

Base sorgente schedinedinotifica@127.0.0.1:3306, MySQL8.0.44. Backup completo61tabelle, routines/events/triggers e blob, consistenza InnoDB in manutenzione locale, nessunworker/scheduler applicativo rilevato. Confronto HMAC riservato di tutti i record prima/dopo dump identico. Dump **2.199.824byte**, SHA256 `6838a312c238ef74c1bbeea8c61e27992c886e1fbcd5d64a97ce2cb3e5aee04a`.

Conservazione fuori Git/pubblico: `/Users/jorgeluccitelli/Backups/Schedine/Herd/ross1000-20261009-autorizzato`, directory700/file600, FileVaultattivo. `.env` originale con APP_KEY, configurazione applicativa archiviata e patch/manifest del candidato preservati in quell'area; nessuna password/key/configsensibile nel repository o nell'output. Anche dati ripristinati, logSQL e impronte di confronto rimangono riservati. In Git sono proposti solo [verbali sanitizzati](evidenze/herd-autorizzato-2026-10-09/backup-verbale-sanitizzato.json), mai il dump o le configurazioni.

Ripristino effettivo su **nuova istanzaMySQL8.0.36 senza rete**, base distintaherd_restore, soltanto socketlocale, nessun runtimeapplicativo/provider/mail. Import exit0,61tabelle e tutti i record identici per impronta HMAC multinsieme, storico migration e credenziali identici; APP_KEY originale usata riservatamente. Istanza arrestata, dati/evidenze conservati fuoriGit.

## Due problemi del diagnostico, non perdite del restore

Il primo confronto letteraleSHOWCREATE ha datoFAIL:37tabelle emettono esplicitamente CHARACTER SETutf8mb4 su8.0.36 mentre8.0.44 lo omette dove implicito. InterventoHerd immediatamente fermato prima delDDL; evidenza originale conservata e manutenzione temporaneamente tolta. Nessun falsoPASS attribuito al primo diagnostico.

L'analisi successiva è solo lettura, istanza già ripristinata riaperta senza rete/read-only/super-read-only, nessuna nuova importazione. Un confronto indipendente dei metadati effettivi individua inoltre AUTO_INCREMENT obsoleto nella cachestatistica della sorgente. Conservati anche questoFAIL e i valori tecnici. La prova definitiva usa `information_schema_stats_expiry=0` **solo nella sessione di lettura**, apre il catalogo di tutte le tabelle e confronta **tabelle/motori/collazioni/sequenze, colonne/tipi/null/default/charset/generatori, indici/unicità, vincoli eFK/regole, trigger**: tutti identici. Non elimina alcun campo o tabella per adattare l'esito. [Confronto definitivoPASS](evidenze/herd-autorizzato-2026-10-09/restauro-verifica-metadati-sanitizzata.json), [FAIL originale](evidenze/herd-autorizzato-2026-10-09/restauro-fallimento-sanitizzato.json).

Il gate di recuperabilità locale è quindi superato mediante evidenza indipendente, preservando i diagnostici originali. Non è prova del backup o diMariaDB del destinatario produttivo.

## Unica migration applicata

Riconfermati hashdump, hashmigration `55cace558d6575888140f7b34cd76a7ad486a96f4b41b66f94a28a1be4950969`, candidato318 e dati operativi ancora identici al backup. Nuova manutenzione locale; processo CLI con trasportiISTAT/Questura/mail disabilitati per quell'esecuzione, nessuna modifica.env. Eseguito esclusivamente:

```sh
php artisan migrate --path=database/migrations/2026_10_07_180000_protect_istat_cycle.php --force --no-interaction
```

Exit0. Campi credenzialiISTATVARCHAR100→TEXT, cifratura2campi prima in chiaro, metadati export/transmission e due nuove tabelle. Confrontati tutti i campi originari delle61tabelle, normalizzando soltanto il contenuto cifrato mediante digest del valore e isolando l'unica nuova riga migration: dati/storico originali identici. Solo una migration aggiunta, solo due nuove tabelle. [Verbale](evidenze/herd-autorizzato-2026-10-09/applicazione-verbale-sanitizzato.json).

Verifica aggiuntiva tramite **cast reali del modelloStruttura**, bootstrap applicativoCLI e transazione READ ONLY:2campi cifrati letti correttamente, digest identico al valore precedente, nessun valore esposto/inviato. [Prova cast](evidenze/herd-autorizzato-2026-10-09/credenziali-cast-verbale-sanitizzato.json). APP_KEY e configurazione originali invariati. Nessun rollback eseguito, down()vuoto non invocato.

Restano **non applicate** create_tassa_exports_and_receipt_image e add_web_checkin_link_lifecycle. Nessun migrate generico/fresh/seed, nessuna autorizzazione estesa a quelle migration. La presente chiusuraRoss1000 non dichiara allineato tutto lo schemaHerd.

## Browser reale Herd

Chrome delMac, sessione preesistente autenticata, struttura **Tanggo**, ruolo mostratoAdmin. Dal pulsanteConfigurazioneRoss1000 in `/struttura` si raggiunge `/istat-tabella-a`, areaISTAT visibile, nessunaQueryException/SQLSTATE, inputusername/passwordvuoti, inviodiretto disabilitato. Nessun Salva/generaexport/verifica/invio/provider premuto. Nessuna schermata o dato personale conservato. [Evidenza DOM sanitizzata](evidenze/herd-autorizzato-2026-10-09/browser-herd-sanitizzato.json).

L'API DOM non espone lo statoHTTP numerico e access_logNginx è disabilitato: documentato il rendering effettivo della pagina, senza inventare una rilevazione200. Tentativo tecnicoPerformance non disponibile preservato nell'evidenza. Il ruolo storico dell'evento500 non è ricostruito; il contesto correnteTanggo/Admin è quello effettivamente verificato. Il vecchio loglaravel e gli otto eventi42S22 sono invariati come evidenza storica.

## Candidato e blocchi

Candidato318 e indice principale preservati; frameworkHerd resta11.31.0. L'aggiornamentoLaravel12 è verificato nel [worktree separato](LARAVEL12-VERIFICA-ISOLATA-2026-10-09.md), non installato nel runtimeHerd né integrato nel candidato. Nuove conoscenze/evidenze sono aggiunte come documentazione di lavoro separata dal suo indice.

H1Ross1000locale **CHIUSO nel perimetro sopra**. Restano B1review/selezione-integrazione del delta12/futuroSHA; B2accessoSSH e runtime/segfault; B3metadata/schema e recuperabilità del destinatario; B4attestazioni debug/vhost/devserver/artefatto pulito e attivazioni provider separate. Nessun cambiamento del server produttivo, commit/push/deploy o trasmissioneISTAT/Questura/mail.

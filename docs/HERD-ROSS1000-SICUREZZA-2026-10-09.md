# Ross1000 Herd, sicurezza effettiva e preparazione SSH — 9 ottobre 2026

**Accettazione funzionale locale positiva conservata. BASELINE IN AUDIT — NON VALIDATA. Nessuna validazione produttiva.** Il500 è locale Herd, non quello di un destinatario remoto: questa precisazione sostituisce l’attribuzione ipotetica al server dei verbali precedenti, che restano intatti.

## Ross1000: causa trovata, guasto operativo non dichiarato risolto

[Eccezione sanitizzata](evidenze/herd-sicurezza-2026-10-09/ross1000-eccezione-sanitizzata.json): otto eventi09/10 tra15:57:07 e18:11:21 in laravel.log, QueryException/SQLSTATE42S22, colonna `snapshot` assente in `istat_exports`. Catena IstatStoricoValidator::errors:20→IstatTabellaAService::analysePeriodo:181→IstatTabellaAController::index:38. Esportati soltanto classe/codice/timestamp/nome tecnico; nessuna query completa, userId, richiesta, sessione o informazione personale. Originale log non modificato, hash/byte della fonte al momento della lettura conservati.

Timestamp senza offset nel log; config sorgente corrente Europe/Rome, non attestazione del fuso storico. Non registrati SHA storico e ruolo effettivo: i frame coincidono con il codice attuale, non si inventa una correlazione esatta alla versione o alla struttura tanggo fornita dall’utente. La causa tecnica non dipende dai nomi o dati degli ospiti.

[Metadati Herd in sola lettura](evidenze/herd-sicurezza-2026-10-09/metadata-herd-sola-lettura.json): MySQL8.0.44, schemaistat_exports precedente, solo migrationISTATmarzo12 registrate. Assente la migration **2026_10_07_180000_protect_istat_cycle**, che aggiunge snapshot e le altre protezioni necessarie. Nessun SELECT di record personali: versione, colonne e storico migration tramite PDO diretto, transazione READ ONLY e ROLLBACK; nessun bootstrapLaravel operativo. Primo tentativo sandbox2002 non attesta indisponibilitàDB, lettura autorizzata fuori sandbox riuscita.

Riproduzione sintetica effettiva: schema senza tutte le aggiunte di quella migration, operatore autorizzato di struttura sintetica, configurazione200 poi stessaQueryException42S22; esecuzione della migration esistente solo sulDBeffimero e pagina200. **PHPUnit1test/13asserzioniPASS**. **Chromium1/1PASS**, pulsante→500 prima, stesso pulsante→200 dopo, invio diretto disabilitato. Prova diversa dai tre profili precedenti già migrati; non una correzione attribuita alle patch delle dipendenze.

**Classificazione: disallineamento codice/schema confermato.** Nessuna patchIstat, fallback o occultamento dell’errore. Herd operativo non migrato: il500 osservato resta da eliminare mediante riallineamento controllato, backup e autorizzazione separata. Non eseguite migrationoperative né manipolati record. Lo storico migration remoto non è deducibile da questi metadati locali.

## Laravel: sette righe nominative e condizioni provate

[Matrice completa](laravel-avvisi-residui-2026-10-09.json): identificatorePKSA/link, versione11.31.0, range, esposizione, rimedio e prova. Due righe email descrivono la stessa GHSA-5vg9-5847-vvmq.

| Identificatore | Esposizione nel candidato | Risultato locale / residuo |
|---|---|---|
| PKSA-d5tc-s1qs-h781 / GHSA-jh5r-qr3c-85q8 | Pagina debug | Debugfalse verificato in runtime isolato; configurazioneFPM/cache effettiva da attestare. |
| PKSA-q46n-4fdk-zjr4 | XSS parametro route in debug | Stessa condizione debug;11.36 patchupstream, framework attuale invariato. |
| PKSA-qzrn-rnz3-85w1 | XSS parametro richiesta in debug | Stessa condizione debug, non mitigazione server dichiarata. |
| PKSA-m5cs-t1y6-qpcs / GHSA-crmm-hgp2-wgrp | URLtemporanee firmate | Nessuna route signed/ValidateSignature attiva e User non MustVerifyEmail, verificato dal registry; tokencheckin indipendenti. Rivalutare all’introduzione del consumer. |
| PKSA-3r5d-mb8f-1qw9 e PKSA-mdq4-51ck-6kdq / GHSA-5vg9-5847-vvmq | Email non fidate | Resolver applicativo rifiuta CR/LF prima della validazioneRFC originale; patch di pacchetto non dichiarata. |
| PKSA-8qx3-n5y5-vvnd / GHSA-78fx-h6xr-vch4 | Wildcard su array file/image | Prerequisito non trovato nei consumer: upload scalari; POSTregistrazione con arrayavatar422 e nessun utente creato. Non generalizzare a futuri wildcard. |

Correzione minima confermata email: classe EmailControlValidator e registrazione del resolver in AppServiceProvider; conserva le regole del chiamante e rifiuta CR/LF anche in un locale quotato che il validatore originale accetta. Valido/nullable/annidato/email errata verificati; resetCRLF422 e nessun invio. **2test/16asserzioniPASS**, **globale750test/8252asserzioniPASS** dopo la modifica. Non certifica record legacy o ingressi senza regolaemail, né il trasporto produttivo.

AuditComposer di pacchetto conserva7Laravel+4sviluppo: un controllo applicativo non cambia la versione del framework. [ImpattoLaravel12](IMPATTO-LARAVEL12-2026-10-09.md): dryrun12.69.3 con50update/5install; Sanctum/UI/Tinker/Sail e strumenti test coinvolti. Nessun cambio maggiore eseguito o policy ignorata. Decisione di supporto del framework e attestazione delle condizioni operative restano prima del lancio.

## npm: correzioni e limite reale degli asset

[Matrice nominativa del candidato effettivo](npm-esposizione-effettiva-2026-10-09.json): per ogni dipendenza, versione, identificatori diretti oppure catena meta, ambito e rimedio. Audit finale completo: **53dipendenze affette,0critiche/15alte/37moderate/1bassa**. Nessun timeout nell’esito finale; rapporti intermedi separati, non conteggiati come stato del candidato.

| Pacchetto / identificatore | Versione candidata | Azione ed evidenza |
|---|---|---|
| Swiper / GHSA-hmx5-qpq5-p643 | 12.1.2 | Primo rimedio indicato dal maintainer; scorrimento e PoC Array.indexOf/prototypepollution PASS nel browser. |
| ECharts / GHSA-fgmj-fm8m-jvvx | 6.1.0 | RenderingSVG/dati/tooltip con etichettaHTML, nessun handleriniettato; audit senza avviso. |
| Quill / GHSA-4943-9vgg-gr5r | 2.0.3 | Vecchio avviso1.3.7 escluso; editorSnow, formattazione e incolla senza handler PASS. Non significa ogni funzione diQuill sicura. |
| Quill / GHSA-v3m3-f69x-jf25 | 2.0.3 | **Residuo** su exportHTMLformula/video: nessuna view/modulo applicativo caricaQuill e nessuna chiamata getSemanticHTML/getHTML nel sorgente risorse; non usato quel percorso. Pacchetto pubblico conservato, nessuna patchglobale dichiarata; gate prima di introdurre export/renderHTML. Non retrocedere2.0.2 soltanto per far sparire l’avviso. |
| Axios / identificatori nella matrice | 1.20.0 | Aggiornamento nella linea1; nel bundle comune benché devDependency. GETautenticata200 e PUTinvalida422, CSRF sul filo PASS. Nessun avviso nell’audit finale. |
| form-data / GHSA-fjxv-7rqg-78g4, GHSA-hmw2-7cc7-3qxx | 4.0.6 | Node/transitivo, non multipartbrowser. Prima: boundary ripetibile e headerCRLFiniettato; dopo: boundary indipendenti e CRLFneutralizzato. TestNode senza rete PASS; audit senza avviso. |
| CKEditorclipboard / GHSA-rgg8-g5x8-wr9v | 40.2.0 nei moduli, builder40.2.0 | Builder escluso soltanto dalla copia del **nuovo artefatto**; dipendenze e sorgenti preservati. Nessuna view o modulo app lo carica. GETbundle404 in nuovo build, altriasset200. |
| CKEditorHTMLSupport / GHSA-jrqm-vmqc-gm93 | Trial43.1.1, non candidato | Il trial antecedente alla licenza44 conserva XSS e82avvisi meta; non promosso. Patch47.6 richiede migrazione dell’integrazione e configurazione di licenza da definire prima di attivarlo. |

Aggiornati soltanto i pacchetti nominati e relativi prerequisiti. Le tre transizioni asset di major sono state verificate sulle API esistenti; non è un aggiornamento globale npm. CKEditor40 non dichiarato patchato: esclusione tecnica di un componente senza consumer, testata sul nuovo artefatto. **Non cancellati** il vecchio public/buildHerd, pacchetti, sorgenti o diagnostici. La procedura di rilascio deve usare un build pulito e controllare l’assenza dell’asset; non sovrapporre il nuovo artefatto a librerie vecchie. Quel controllo del destinatario resta aperto.

ModuliNode/tooldev non equivalgono a runtimePHP; assenza di serverdev pubblico e build fidato da attestare prima del lancio. Il lock può contenere advisory anche per pacchetti non distribuiti nel bundle. Non dichiarata la mitigazione di tutte le53righe dal solo flagdev.

## Prove ed originali

Ross1000PHP1/13, Ross1000legacyChromium1/1, Laravel2/16, assetChromium3/3 e verifica finale artefatto+asset4/4, HTTPAxios1/1, multipartNodePASS. Globale750/8252 dopo resolver; non ripetuta dopo soli cambiJS/build, verificati nei browser e build effimeri. Trap provider/mail in ogni runtime; nessuna trasmissione reale.

Nuovo diagnosticoAxios iniziale assumeva X-CSRF-TOKEN nella configurazione: falso, ma PUT422 corretta. La verifica finale osserva X-CSRF-TOKEN oppure X-XSRF-TOKEN **sulla richiesta inviata**, medesima aspettativa funzionale di CSRF presente; originale e rosso conservati. Prima bozza di esclusione di tre asset affinata al solo CKEditor: originale e risultati separati, nessun rosso occultato. CKEditortrial non applicato e non usato come audit del candidato.

Nuovo inventario1819sorgenti; inventario storico1818preservato, sole5divergenze autorizzate: provider, tre lock/manifest e package-copy-config; una nuova classe. Staging e manifest includono solo codice e artefatti verificati. Nessuna suite superata ripetuta senza una modifica o nuovo esito che lo giustifichi.

## SSH e pendenti concreti

[Procedura Mac](SSH-MAC-CHIAVE-AUTORIZZATA-2026-10-09.md). Coppia tanggo_spanel esistente e permessi verificati; solo impronta.pub letta, agente senza identità. Nessuna nuova connessione remota, chiave privata mostrata/copiata, modificaMac/server o credenziale richiesta. Procedura con chiave esplicita e StrictHostKeyChecking=yes; conferma autorizzazione.pub tramite canale fidato.

Restano: riallineamento controllatoHerd per eliminare il500 locale; decisioneLaravel12/supporto e condizioni debug/email/exposizione effettive; eventuale attivazioneCKEditor/exportHTML dopo contratto compatibilità/licenza; autenticazioneSSH e letture runtime/journal/schema del destinatario; recuperabilità reale; revisione candidato e futuroSHA autorizzati separatamente. Nessun commit/push/deploy, scritturaDBoperativo, configurazione server o trasmissione reale.

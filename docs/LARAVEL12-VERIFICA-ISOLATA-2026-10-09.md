# Laravel12: implementazione e verifica isolate — 9 ottobre 2026

**Accettazione locale positiva del delta12 nel perimetro verificato.** BASELINE IN AUDIT — NON VALIDATA; nessuna validazione produttiva, commit/push/deploy o trasmissione. Candidato principale318 preservato nell'indice e nel manifest, runtimeHerd ancora11.31.0. Il delta non è integrato: rimane concreto e revisionabile nel worktree gestito `/Users/jorgeluccitelli/.codex/worktrees/laravel12-verifica/Schedinedinotifica`, baseHEADfaef3cf01f43d19b20d1558c1fbd3ae32e48d849, con copia esatta della patch318 di partenza. Non copiati.env operativo, dati reali o APP_KEY nel worktree.

## Modifiche minime effettive

[Manifest dei quattro file delta e hash](laravel12-delta-verificato-2026-10-09.json): composer.json, composer.lock, tests/Unit/ComponentiImportFormTest.php e nuova copia diagnostica Laravel12ResidualExposureAudit.php. Nessuna modifica del codice di business. File e aspettative originali conservati nel candidato e nelle evidenze.

| Voce | Soluzione verificata |
|---|---|
| Laravel | 12.69.3, require^12.69 |
| PHPUnit | 11.5.57, require^11.5.50 |
| Sanctum/UI/Tinker/Sail | Compatibili12, aggiornati solo come prerequisiti solver |
| SymfonyYaml | **6.4.40**, prima patch delle tre segnalazioni nella linea6 originale; nessunmajorYaml superfluo |
| PHP | require^8.3, platformsolver8.3.0, runtimeHerd8.3.33 |
| Graph | **50aggiornamenti,5installazioni,0rimozioni** rispetto al candidato318 |

[Matrice dipendenze completa](evidenze/laravel12-isolato-2026-10-09/dipendenze-delta.json). Root precedente dichiarava^8.2 ma OpenSpout4.32 già richiedevaPHP8.3: esplicitato il minimo effettivo senza cambiare OpenSpout o il PHP operativo. Il primo solver in un shellPHP8.4 aveva selezionatoSymfony8/PHP>=8.4.1; tentativo rifiutato su runtimeHerd8.3.33, preservato. Il vincoloPHP8.3 impedisce quella deriva; non si ignora platformcheck. Nessuna attestazioneCLI/FPM del destinatario ricavata da questo testMac.

## Matrice delle verifiche effettive

| Requisito | Evidenza | Risultato | Limite / pendente |
|---|---|---|---|
| DependencygateComposer | audit finale, validate--strict | **0advisory/0abbandonati**, manifest/lock validi | Riferito al lock12 verificato, non al candidato11 ancora separato |
| Requisiti runtime | check-platform-reqs no-dev | **PASS PHP8.3.33/estensioni locali** | VerificarePHP CLI/FPM del destinatario |
| Installazione produzione | Copia privata senza.env, install no-dev/no-scripts/no-plugins | **84pacchetti installati**, autoload12.69.3PASS | Non eseguiti script o boot operativo sulserver |
| Suiteglobale | laravel12-globale-finale-06.log | **750test/8252asserzioni**,0errori;5deprecazioniPHPUnit | Deprecazioni doc-comment P2, mantenute visibili |
| Import/render unitario | laravel12-import-mirati-01.log | **16test/103asserzioniPASS** |2deprecazioni già comprese nelle5globale |
| Patchemail nativa/guard/esposizione | laravel12-sicurezza-mirati-01.log | **2test/16asserzioniPASS** | Copia12 separata, originale11 intatto |
| Token/date/revoca/alias/conversione/Cestino/Wi-Fi | laravel12-token-browser-01.log | **Chromium9/9PASS** | Solo fixture/runtime sintetico; topologia/IP/proxy reale da attestare |
| Ross1000/Axios/CSRF/assetpubblici | laravel12-ross-asset-browser-01.log | **Chromium8/8PASS** |3profili Ross1000, artefatto pulito,Swiper/ECharts/Quill; nonproviderreale |
| Import clienti/componenti, conversione e flussi principali | laravel12-principali-browser-01.log | **Chromium4/4PASS** | Calendario, supporto, notifiche, Cestino, gestione, areaadmin,desktop1440/mobile390 |
| A12–A15/sessioni/tenant | laravel12-sessioni-browser-01.log | **Chromium5/5PASS** | Reset/revoca/ricordami e isolamentoArrivi sintetici |
| Build/architetturaGEO | launcher in tutte le prove | **PASS**,GEOimmutabile | Assetnpm invariati rispetto al candidato318 |

Totale browser **26/26PASS**. Prove esclusivamente tramite tests/Isolation/run.py, istanze/processi/porte propri,23rifiuti preventivi primaDDL, trapdei trasporti e cleanup. Nessuna suite sulDBHerd e nessun dato reale nel worktree. La no-dev install è una prova di pacchetti/autoload, non un boot di produzione suldestinatario.

## Diagnostici originali e compatibilità dei test

Prima globale eseguita:750test/8218asserzioni,6errori di tipoauth(), nessun difetto applicativo confermato. Due classi anonime nei doppi unitari non implementavanoFactory/Guard; Laravel12 ora dichiara quella union nel ritorno di auth(). Correzione limitata ai doppi: PHPUnitmock dell'interfacciaGuard, stessi id11/userstruttura7, stesso divietoDB, **tutte le aspettative funzionali invariate**. Originale.txt e logrosso conservati; globale successiva ritorna alle medesime8252asserzioni.

Il diagnosticoLaravel11ResidualExposureAudit dimostrava che il validatore framework originale accettavaCRLF; in12 la patchupstream rifiuta anche senza resolver. Nuova copia identificata cambia soltanto quella precondizione di versione in assertFalse e il nomeclasse; annidato/nullable/valido/reset422/nessunmail/route/upload conservati. Non alterato l'originale11 per nascondere un esito.

Guardie di setup preservate: primo shellworktree selezionavaHomebrewMySQL9.5 (vendorambiguo), poi dipendenze richiedevanoPHP8.4, poi npm mancava nelPATH ridotto. Fallimenti prima della suite, nessuna guardia indebolita. PATHdefinitivo punta ai binariHerd/MySQL8.0.36/Node22.21.1 disponibili. Tutti i tentativi sono nelle evidenze; non presentarli come suite verdi.

P2:5deprecazioni metadataPHPUnit da migrare prima del futuroPHPUnit12. Nel logbrowser8PASS compare inoltre un messaggio proxycorpoincompleto dopo il riepilogo: conservato, senza attribuirlo a un difetto applicativo o dichiararne risolta la causa. I flussi con asserzioni completano; resta un residuo diagnostico del launcher da separare dal codice applicativo.

## Esposizione e gate prima del lancio

Il nuovo lock elimina i sette avvisiLaravel11 e i quattro sviluppo rilevati in precedenza, senza bypasspolicy. Le condizioni debugfalse/consumerfirmati assenti/filewildcard non usati e resolveremail restano verificate; configurazioneFPM/cachedestinatario non ancora attestata. npm53righe già classificate restano nel lockasset invariato: CKEditor escluso dal nuovo artefatto, QuillHTMLexport non introdotto, strumentidev non devono essere pubblici. Non dichiarata sicurezza globale di ogni dipendenza dal solo auditComposer0.

Per adottare12 nel primo rilascio: integrare/revisionare questi quattro file e relativa documentazione nel candidato mantenendo lo snapshot318; verificare runtime/estensioni/installazione no-dev del destinatario e distribuire artefatto pulito. Il comando operativo suHerd di questa task riguarda solo la migrationISTAT, non l'installazione di12 o le altre due migrationpendenti. ServerMariaDB/segfault/metadata/recuperabilità/provider restano gate separati.

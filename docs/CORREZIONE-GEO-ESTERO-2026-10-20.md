# Correzione GEO estero — Schedina e Web Check-in, 20 ottobre 2026

## Stato: pubblicato e verificato online — `5e01d7b1153daac8de96a6281cd6203ff92c034d`

Le sezioni iniziali conservano la proposta e le verifiche storiche; gli esiti attuali sono nel consuntivo conclusivo e nella ricevuta.
Ramo: codex/web-checkin-testi-palette-2026-10-20, PR #4. Candidato iniziale f518345c050abc66c19c827be7133cc8dedff729; correzioni e SHA pubblicato nel consuntivo conclusivo.
BASELINE IN AUDIT invariata. GEO, editor e palette sono pubblicati e verificati; il consuntivo conclusivo e la ricevuta riportano gli esiti effettivi.

## Evidenza reale e causa
Ispezione in sola lettura del modulo pubblico già aperto dall'utente, senza cambiare campi o inviare il form.
Il selettore della nazione mostrava il valore numerico 156. Il campo invalido letto dal DOM era oa_city, con messaggio «Il campo città anagrafica è obbligatorio.»
Il controller condiviso SchedinaController imponeva required a oa_city e or_city per qualunque nazione. WebCheckinController richiama validateSchedinaRequest anche per Conferma dati e per i pendenti. Difetto quindi presente nella validazione condivisa, non limitato alla nuova navigazione.
Il formulario condiviso passava all'inizializzazione GEO il valore grezzo di old input o schedina. In caso di errore, l'ID inviato diventava sia value sia etichetta. Ora passa dal servizio CustomerWizardValidation::initial esistente per risolvere ID/nome e modalità Italia/Estero.

## Regola verificata su fonte ufficiale
Manuale Alloggiati Web:
https://alloggiatiweb.poliziadistato.it/portalealloggiati/Download/Manuali/MANUALEALBERGHI.pdf
Consultato il 20 ottobre 2026, sezioni 3 e 12, pagine PDF 8 e 30–33: nascita estera identificata dallo Stato; comune e provincia italiani nel tracciato restano vuoti. Cittadinanza è un campo separato. La residenza non compare nei campi del tracciato Questura descritto nel manuale. Questo non elimina eventuali requisiti statistici/fiscali di altri moduli; nessuna modifica agli export ISTAT/Questura o ai loro controlli.

## Modifiche del candidato
- oa_city e or_city richiesti solo se la nazione è italiana, usando il controllo di catalogo già esistente. Nazioni e cittadinanza continuano a essere validate.
- Tipo via/indirizzo/civico obbligatori solo per residenza italiana; facoltativi per residenza estera. Nessun valore inventato.
- Quattro istanze GEO del formulario Schedina, titolare e componenti, risolvono i valori iniziali tramite il servizio esistente. Le nazioni sono mostrate col nome anche dopo errore o ripresa.
- Dati geografici esteri ulteriori raccolti sotto «Altri dati esteri (facoltativi)», chiuso inizialmente. Stesso componente GEO, con opzione predefinita disattivata per gli altri utilizzatori; nessun secondo sistema GEO.
- Un CAP estero iniziale non avvia più resolve del catalogo italiano.
- Nel modulo pubblico il messaggio di errore viene associato al controllo abilitato col nome corrispondente; l'eventuale sezione facoltativa viene aperta. Navigazione, save e componenti conservati.
- Testi informativi e indicazioni di obbligatorietà allineati.

## Verifiche eseguite
- Lettura sorgenti di controller Schedina/Web Check-in, validazione GEO/clienti, normalizzazione componenti, formulario e componente GEO.
- DOM pubblico reale: errore oa_city e nazione numerica confermati. Nessun salvataggio o dato reale modificato in questa attività.
- node --check geo-review/resources/js/ui/geo-italia.js: PASS sintattico.
- git diff --no-index --check geo-before geo-review: nessun errore whitespace, exit 1 per differenze.
- Revisione dei diff applicativi/test eseguita.

## Prove predisposte, NON eseguite
Sei nuove regressioni PHP: Schedina estera con soli Stati e senza indirizzo; comuni italiani ancora obbligatori; ID/nome/modalità GEO; conferma pubblica estera; errore e ripresa con etichetta nazione; salvataggio parziale estero e ripresa.
PHP, Blade, PHPUnit, browser applicativo, build Vite e C5 non eseguiti: la sessione non dispone di PHP, limactl e VM ufficiale. Nessun aggiramento di TEST_ISOLATION_REQUIRED, nessun DB reale usato per test.
Prima di integrare eseguire nella VM ufficiale le suite SchedinaFlowValidationTest, WebCheckinHtmlTest, CustomerWizardTest, WebCheckinCircuitoTest e gli spec HTML/circuito con fixture sintetiche. Verificare anche passaggi Italia→Estero→Italia, CAP estero, componenti aggiunti/rimossi, errori nella sezione facoltativa, due tenant e larghezze 1440/768/390. Gli esiti precedenti non certificano questo nuovo candidato.

## Ricezione email
L'utente ha confermato in questa conversazione la ricezione in Gmail dell'unico invio della richiesta 134 delle 15:01:01 Europe/Rome. È una conferma utente, non un'ispezione autonoma della casella. Nessuna nuova email inviata in questa attività. Quell'invio precede queste modifiche e usa l'applicativo precedente.

## Tenancy, rischio e chiusura
Rischio MEDIO per regole e UI condivise. Nessuna modifica a schema, token, autorizzazioni, scadenze, trasporti, serializer Questura o contratti ISTAT. Campi esteri facoltativi già presenti conservati; nessuna migrazione o pulizia dei dati. Prove dinamiche negative su due tenant e regressioni ancora pendenti. Prossimo passo: collaudo nel runtime ufficiale e C5, non pubblicazione diretta.

CODICE MODIFICATO: SI. TEST MODIFICATI: SI. DOCUMENTAZIONE MODIFICATA: SI.
CONFIGURAZIONE MODIFICATA: NO. SCHEMA/DATI REALI MODIFICATI: NO.
TEST FUNZIONALI DEL CANDIDATO ESEGUITI: NO. CONTROLLI STATICI: SI.
COMMIT/PUSH: SI sul solo ramo della PR. DEPLOY: NO. TRASMISSIONI ESTERNE: NO.

## Sigillo del collaudo isolato precedente al rilascio

R12 PHP e dodici browser storici; R13 nove browser dedicati sullo stesso applicativo: **351 test PHP / 3546 asserzioni e 21 scenari Playwright PASS** (6 circuito, 6 HTML, 9 GEO/editor/palette). Incluse le sei regressioni originarie, clienti, Schedina, Componenti, comunicazioni, isolamento tra strutture, Questura e ISTAT. 17 guardie, 23 tentativi di override rifiutati, build Vite/GEO e rendering Blade PASS. Larghezze 1440/768/390; colori #123456 e #ffffee; parziale, riapertura, conferma, errori visibili, modelli riutilizzati su una seconda richiesta e FormData canonico. Rimane una sola deprecazione preesistente nei metadati di IstatConformitaTest. Nessun test Laravel/PHPUnit/Playwright sul Mac; nessuna email reale. [Sigillo e schermate](evidenze/web-testi-palette-2026-10-20/verifica.json).

Corretti durante il collaudo: sigillo GEO omesso dalla proposta; direttiva Blade che inglobava il blocco successivo e causava completedGuests non definito; associazione errore al controllo italiano nascosto invece del campo estero; risposte asincrone italiane non più pertinenti; cittadinanza manuale sovrascritta al cambio nazione; completezza/coerenza residenza dei Componenti solo alla conferma completa; prima voce del tipo via assegnata involontariamente a un estero senza indirizzo; ombre verdi e palette di calendario/ricevute. Validazioni italiane e guardie mantenute. Nessun contratto di esportazione, schema, configurazione o trasporto modificato.

R1 STOP per sigillo GEO; R2 18 errori Blade. R3 PHP349/3522 PASS, browser interrotto dopo selettore Italia non corrispondente all’etichetta Italia (IT). R4 3 errori su separazione parziale/completo e fixture complete prive di residenza, corretti mantenendo le asserzioni originarie. R5 PHP350/3536 PASS, browser interrotto dopo clic sul campo data originale nascosto. R6 difetto reale errore estero nascosto; R7 aspettativa errata sulla riapertura prima della conversione; R8 avviso di selezione struttura da chiudere; R9 componente assente nella fixture per scope tenant; R10 richiesta di seconda anteprima riutilizzata tra prove; adattatori/fixture corretti. R11 nove dedicati PASS, R12: PHP e 19 browser PASS, un NONPASS tablet per calendario rimasto aperto dopo jumpToDate e Escape, ultimo scenario non eseguito. Corretto soltanto l’adattatore con normale clic esterno e verifica di chiusura, senza force o timeout diversi; R13 tutti i nove dedicati PASS. Applicativo identico fra R12 e R13, unica differenza nello spec dedicato. Nessun NONPASS intermedio promosso a PASS; log conservati.

Capacità VM recuperata con FIDEDUPERANGE su sole copie temporanee chiuse: contenuti, inode e metadati verificati prima/dopo, nessun file cancellato e nessuna esecuzione dei dump SQL. Conservati anche tutti i 192 dump/ridump verificati; i duplicati condividono soltanto blocchi XFS. Nessun resize/arresto VM, daily, dato operativo, env, upload, backup, cifrato o chiave alterato. Ultima misura prima del C5 da riconfermare. Riserve e budget invariati. Il minimo storico PR3 di 1243488256 byte evidenzia un consumo campionato di 1794150400 byte: il nuovo gate terrà conto anche di questo picco osservato, oltre al recupero completo, prima del RUN.

Integrazione main, C5, QA online, rimozione fixture e monitor finali autorizzati dall’utente e ancora pendenti al momento del sigillo. Ricezione dell’email della richiesta 134 già confermata dall’utente; nessun nuovo invio da eseguire. **BASELINE IN AUDIT** invariata.

## Integrazione, pubblicazione e verifica effettive

PR #4 integrata in main dopo le correzioni `797abf7cb36fd25910f51c2b963ef8110fa342b4`. **SHA integrato e realmente online: `5e01d7b1153daac8de96a6281cd6203ff92c034d`**. Verificati commit qualificato, integrazione GitHub e HEAD produttivo pulito; 111 sorgenti della task e 168 sorgenti/protetti conformi. [Ricevuta](evidenze/web-testi-palette-2026-10-20/rilascio.json), [QA](evidenze/web-testi-palette-2026-10-20/qa-online.json), [comandi](evidenze/web-testi-palette-2026-10-20/comandi-collaudo.txt).

C5 esistente, 68 file conformi salvo parametri SHA/tree: nuovo PREPARE, nuovo T0/S0 e recuperi indipendenti PASS. Unico RUN di questa task PASS: S0 131.975 s, RUN 215.217 s; 159 migrazioni / 66 tabelle, nessuna DDL. Tutti i sei nuovi cifrati PREPARE/S0 su Extra autenticati e decifrati integralmente, chiave separata. 55 campioni di quiete PASS; guardiano concluso/disarmato/inattivo/disabilitato. DB byte-identico a S0 prima della QA e 92 persistenti conservati. Gate dopo PREPARE rafforzato al massimo fra recupero+cifrati completi e consumo storico campionato aumentato per crescita, con 32 MiB e riserva 1 GiB: stima 2907930624 byte contro 5464162304 liberi prima RUN. Nuovo campionamento: minimo 3705212928 byte / 2528 campioni, sempre oltre 1 GiB. Nessun limite ridotto.

QA online eseguita dalla VM: sei circuito, sei HTML e nove GEO/editor/palette, tutte a 1440/768/390 PASS; colori chiaro/scuro, calendario, ricevute, errori visibili, parziale/riapertura/conferma, Schedina e Componenti, modelli duplicati/aggiornati e riutilizzati senza dati della prima richiesta, FormData canonico. Anche clienti/Bozze/ripresa, isolamento A/B, 24 navigazioni responsive e accessi scaduti/revocati/rigenerati PASS. Nessun POST invio, nessuna email o modifica a ospiti reali; richiesta 134 non inviata nuovamente. Anteprima HTML effettiva verificata online, trasporto e MIME provati soltanto nel runtime sintetico. Primo adattatore online dedicato: tre GEO PASS, poi NONPASS per attesa networkidle sul primo login editor, prima di modificare modelli. Schermata con elenco pronto e zero errori Laravel/FPM dall’intero T0; causa del traffico residuo non attribuita. Conservati log, risultato e schermata; sostituita soltanto l’attesa generica con load, font, render e tabella visibile. Ripetuti tutti i nove casi: PASS sul medesimo applicativo. Adeguata soltanto la selezione alle maiuscole effettive del catalogo e attese le animazioni nelle schermate, senza force o riduzione delle asserzioni.

Fixture, account e loghi sintetici rimossi: HMAC di tutte le righe preesistenti delle 66 tabelle e 92 persistenti/env/key/upload/loghi PASS; nessun restore o reset AUTO_INCREMENT, sola eccezione mtime directory dei loghi. Monitor conclusivo dopo pulizia: server 918.376 s / 31 campioni / 279 sonde e Mac 921.526 s / 31 campioni / 93 richieste HTTPS con DNS/TLS ordinari PASS. Zero errori Laravel/FPM dall’intero T0, senza azzerare i log.

Soglie Mac 8 GiB / Extra 40 GiB, riserva VM 1 GiB e budget 1800/600/1379/421 invariati. Daily 945ef553, disco 36 GiB / XFS 37290283008, SELinux Enforcing, configurazioni/credenziali/backup e checkout originario preservati. Capacità recuperata soltanto condividendo blocchi kernel di copie temporanee chiuse e verificate: nessun file cancellato, tutti i dump/ridump conservati e nessun SQL eseguito; fase vendor/node_modules senza guadagno misurato distinta dalle fasi con guadagno reale. Presidio caffeinate temporaneo terminato e verificato, nessuna impostazione permanente cambiata. Il commit documentale successivo è distinto dallo SHA applicativo online.

Nessun pendente reale nello scope verificato. Rimangono una deprecazione PHPUnit preesistente, differenza PHP VM 8.3.33 / produzione 8.3.35 e audit generale/DNS/DKIM storici separati. **BASELINE IN AUDIT** invariata.

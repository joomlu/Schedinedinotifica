# Audit dei contenuti del sito — 25 ottobre 2026

**Riscontro dei sorgenti, non attestazione di accettazione esterna. Nuovo candidato da qualificare. BASELINE IN AUDIT invariata.** Fonte gerarchicamente prevalente: [Maestro](maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md). Le fonti applicative citate sono state consultate in sola lettura; nessuna modifica ai moduli interni, autenticazione o dati.

## Funzioni e messaggi pubblici

| Funzione | Fonti applicative/documentali | Stato e limite | Testo/percorso pubblico |
|---|---|---|---|
| Clienti e anagrafiche | CustomerController: index/store/update/show/print/storico; CustomerImportController, CustomerExportController | Implementati; dati richiesti, controlli e autorizzazioni del contesto | Dati riutilizzabili, consultazione e storico; `/moduli#reception` |
| Individuali, famiglie e gruppi | SchedinaController; modelli Schedina e componenti; viste schedina | Implementati; titolare/componenti e documenti richiesti distinti | Preparazione, bozze e completamento controllato; `/moduli#reception`, `#ospiti` |
| Arrivi | ArrivalsController; SchedinaController::convertArrival; Maestro Arrivi | Implementato circuito operativo Europe/Rome distinto da soggiorni effettivi, bozze e WebCheck-in | Preparare gli arrivi della giornata e completare la pratica; `/moduli#arrivi` |
| Web Check-in | WebCheckinController; WebCheckinRichiesta; modelli e circuiti dedicati | Richiesta/referente/ospiti distinti; link ospite non è accesso gestionale; conferma reception necessaria | Raccolta anticipata e verifica; `/moduli#web-checkin`, `/ampliamenti#web-checkin` |
| Modelli e comunicazioni | WebCheckinComunicazioniController::modello/prepara/anteprima/invia; WebCheckinModello e WebCheckinComunicazione | Modelli personalizzabili, versioni, anteprima e storico; invio dipende da impostazioni/canali effettivi | Preparare comunicazioni e ritrovarne lo storico; nessuna promessa email/push universale |
| Proprietari, strutture, ruoli | Proprietario; Proprietario/StruttureController; Admin/Superadmin/ProprietariController; StrutturaUserController; Support/StrutturaAccess | Implementati; visibilità subordinata a ruolo/proprietà/assegnazioni | Selezione struttura autorizzata, responsabilità e utenti; `/moduli#strutture-proprietari`, `/ampliamenti#strutture` |
| Alloggiati Web | QuesturaExportController; servizi Questura e Maestro | TXT, verifiche, storico/operazioni e ricevute disponibili; collegamento e operazioni effettive richiedono credenziali/abilitazioni | Preparazione e controllo distinti da risposta/accettazione del destinatario; `/moduli#questura`, `/ampliamenti#alloggiati-web` |
| Tabella A, statistiche e Ross1000 | IstatTabellaAController::index/downloadXml/downloadStorico/printSummary/verifyPeriodo/sendPeriodo/reconcile; servizi ISTAT; Maestro pubblicazionea90 | XMLER, riepilogo e storico implementati e qualificati localmente; SOAP presente con credenziali/abilitazione, accettazione reale non verificata | TabellaA come riepilogo del gestionale distinta dai modelli territoriali; XMLER/storico, canali da configurare; `/moduli#istat`, `/ampliamenti#istat-ross1000` |
| Tassa ed esenzioni | TassaDiSoggiornoController; TassaEsenzioneController; TassaReportController::index/controllo/exportCsv/exportControlloCsv/consolida/downloadStorico; servizio Tassa; Maestro | Regole per Comune/classificazione/periodo; dati incompleti possono bloccare output; StayTour resta da verificare | Calcoli dettagliati, esenzioni, stampe, rapporti, CSV e rettifiche/versioni; `/moduli#tassa-soggiorno`, `/ampliamenti#tassa-soggiorno` |
| Importazioni, esportazioni e stampe | CustomerImportController/CustomerExportController, SchedinaController, viste componenti/import, stampe clienti e report | Percorsi implementati, con formati/controlli/permessi specifici; nessun import universale promesso | Anteprima, revisione e conferma; `/moduli#importazioni-stampe`, `/ampliamenti#importazioni` |
| Presenze e organizzazione | PresenzeController::index/printRiepilogo/printDettaglio; CalendarioController; NotificheController/NotificheService; SupportoController; viste operative | Report, calendario, consegne, notifiche interne e ticket; non channelmanager/bookingengine/incassi | Continuità fra turni e situazione operativa; `/moduli#organizzazione`, `/ampliamenti#organizzazione` |
| Browser/sessioni attive | Ricerca dedicata routes/controller/viste: nessun pannello gestione dispositivi o sessioni attive individuato | Non dimostrato come funzionalità del prodotto; compatibilità browser è ambito di collaudo, non feature amministrativa | Nessuna promessa di monitoraggio/revoca browser attivi aggiunta |
| Max Full | Catalogo approvato2026-10-25, istruzioni proprietario | Servizio aggiuntivo a preventivo; frequenza, attività, canali e tempi concordati | Sette attività canoniche e condizioni esatte su `/prezzi#max-full`, richiamo `/ampliamenti#max-full` |

## Circuito commerciale e disponibilità

Catalogo prezzi invariato; aggiunta soltanto la mappa centrale `interests` con dieci codici ed etichette concordati col principale. CTA `/contatti?interesse=codice#demo`, select modificabile e riepilogo canonico leggono la stessa fonte. Piano, MaxFull e interesse restano distinti; cancellazione delle scelte e reset includono l’interesse. Payload contiene codice interesse e consenso booleano, non importi autorevoli client. Il principale gestisce validazione server, ricostruzione del messaggio CRM/attività, anti-duplicati e test.

Corretto un difetto concreto del frontend precedente: un HTTP200 con JSON invalido, `ok:false` o senza IDlead poteva comunque aprire la conferma e azzerare il modulo. Ora successo soltanto con `ok===true` e `lead_id` intero positivo; negli altri casi si conservano dati e si mostra errore. Nessun account o acquisto attivato dal sito.

Le pagine presentano benefici ed esempi prima dei requisiti. «Implementato» non significa incluso automaticamente in un abbonamento, configurato per ogni struttura o accettato dall’ente. Nessuna roadmap, garanzia fiscale, invio automatico generalizzato, testimonianza o statistica commerciale inventata.

## Materiale e dati mancanti

Cinque schermate autentiche con dati dimostrativi e tre ambientazioni illustrative già approvate, senza nuove immagini o ritocchi al gestionale. Tour interattivo al posto di un video inesistente. Restano da fornire dati societari/PartitaIVA, informativa privacy, recapiti ulteriori verificati e condizioni operative MaxFull. I recapiti nel sito precedente sono fonti storiche, non prova di validità attuale.

## Confini delle prove

Questo audit riguarda contenuti e riscontro statico. Nessuna nuova prova Laravel sull’host, VM o produzione eseguita dall’agente web. Il confronto completo e le verifiche ancora necessarie sono in [Confronto del sito](CONFRONTO-SITO-2026-10-25.md). Versionamento CSS/JS, route nominali e marcatore uniforme sono proprietà del principale; non si attribuisce alla cache una causa non riprodotta.

## Controlli statici del candidato frontend

Sei HTML: un H1 e una voce corrente ciascuno, ID univoci e annidamento valido;125 riferimenti ad ancore locali o fra pagine risolti. Dieci codici interesse nel catalogo. Sintassi Node di app.js valida, specchi CSS/JS identici e `git diff --check` senza errori. Questi controlli non qualificano le interazioni runtime, la resa responsive o la persistenza CRM: restano da eseguire dal principale nella VM ufficiale.


## Qualifica successiva del principale

R6:192PHP/2103asserzioni e16browserPASS; R8-IMMAGINI:3percorsi/12figurePASS con applicazione byte-identica. Risultati, tentativi precedenti e confini nel [rapporto corrente](SITO-INTEGRALE-2026-10-25.md). Nuova pubblicazione ancora pendente C5/capacità; nessun PASS storico sostitutivo.

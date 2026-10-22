# Tassa di soggiorno — audit integrale iniziato il 22 ottobre 2026

**BASELINE IN AUDIT. Attività in corso; nessuna chiusura fiscale o accettazione StayTour attestata.**

## Identità e preservazione

Verifica remota corrente: `main` GitHub `9a3c9e64f3db3272f763e7adc5390d7bf4a42015`; SHA Git installato `67b4c45f94a349d0b27c7874aacbbe770bc1cf00`, Git produttivo pulito, FPM attivo e barriera assente. Tra questi due commit non risultano differenze in app, risorse, configurazioni, route o migrazioni. Il checkout iniziale resta a `6a652b72`, con i quattro documenti modificati e gli artefatti preesistenti conservati. Audit in worktree separata del main aggiornato, ramo `codex/tassa-audit-2026-10-22`.

Lettura produttiva in transazione SQL READ ONLY e rollback: sette strutture Bellaria-Igea Marina, tutte alberghiere/Hotel; quattro classificate 3 stelle/3 stelle superior e tre senza classificazione. Nessun export consolidato presente. Le date legacy di marzo non coincidono con il periodo del profilo, ma il correttivo già pubblicato usa il profilo annuale e conserva i record. Nessun riallineamento, assegnazione di categoria o modifica di ospiti/configurazioni reali autorizzata da questa lettura.

## Circuito effettivo

| Passaggio | Implementazione | Dati e vincoli |
|---|---|---|
| Dati struttura | Struttura, relazioni GEO/categorie; servizio `datiStrutturaFiscali` | Comune, tipologia e classificazione determinano il profilo; categorie Bellaria non certificate bloccate |
| Configurazione | TassaDiSoggiornoController; Superadmin/ConfigurazioneTassaController; TassaConfig/* | `tassa_di_soggiorno`, `config/tassa_profili.php`, archivio privato append-only; nessuna estensione automatica ad anni privi di fonte |
| Fonti e controllo periodico | VerificaProfiliTassa; cron dedicato e controllo-profili-tassa.sh | Lettura fonti e richieste private; nessuna dichiarazione comunale automatica |
| Esenzioni | TassaEsenzioneController, catalogoBellaria | `tassa_esenzioni`; catalogo Bellaria protetto; 777 derivato, 450 legacy con raccordo esterno non attestato |
| Ospiti e permanenza | SchedinaController, import Componenti, Arrivi e conversione Web Check-in | `schedina`, `componenti`; date, nascita e motivo individuali; Arrivi esclusi dal calcolo effettivo |
| Calcolo | TassaDiSoggiornoService | Notti del soggiorno e profili pertinenti, tetto unico, segmenti per tariffa/esenzione; nessun registro incassi/saldi |
| Documento individuale | SchedinaController::printTassa; schedina/print-tassa | Anteprima e stampa del calcolo, anche storico; non quietanza di pagamento accertato |
| Rapporto e controllo | TassaReportController; tassa_di_soggiorno/* | Intervallo per arrivo, dettaglio per persona/segmento e scheda; effettive, scope struttura e Cestino |
| Consolidamento | TassaReportController::consolida; TassaExport | `tassa_exports`, snapshot cifrato, digest CSV, versioni collegate; nuova versione distinta e immutabilità dello storico |
| File Comune | exportCsv, downloadStorico, csvFromRows | CSV per caricamento manuale su StayTour Bellaria; nessun trasporto API o prova di accettazione |
| Questura e ISTAT | Controller/trasporti propri | Flussi distinti dal CSV comunale; date complete del soggiorno e contratti preservati |

Accessi Tassa nelle route autenticate con struttura corrente; fonti/configurazione centralizzata riservate al Superadmin. La disponibilità di un pulsante non prova il permesso: la selezione di regressione comprende verifiche HTTP e tenant sintetici. Il CRUD Bellaria non consente tariffa/esenzioni arbitrarie dal formulario hotel; classificazioni mancanti restano una dipendenza operativa.

## Fonti e limiti normativi

Consultazione del 22–23 ottobre 2026:

- [Pagina comunale del regolamento](https://www.comune.bellaria-igea-marina.rn.it/it/documenti_pubblici/regolamento-imposta-di-soggiorno-ids) e [ufficio IDS](https://www.comune.bellaria-igea-marina.rn.it/it/unita_organizzative/imposta-di-soggiorno): accessibili; rinviano ancora al testo 2025.
- [Testo comunale accessibile](https://municipium-images-production.s3-eu-west-1.amazonaws.com/s3/602/allegati/amministrazione/documenti-e-dati/atto-normativo/regolamento_ids_2025.pdf): periodo giugno–settembre, sei notti per alberghi; distingue comunicazione mensile e riversamento, con soggiorni a cavallo attribuiti alla conclusione. Prevede documentazione per le esenzioni. Non certifica l'efficacia degli atti successivi.
- [CC29/2026](https://bellariaigeamarina.trasparenza-valutazione-merito.it/web/trasparenza/delibere-di-consiglio/-/papca/display/46217) e [GC184/2025, tariffe 2026](https://bellariaigeamarina.trasparenza-valutazione-merito.it/web/trasparenza/delibere-di-giunta/-/papca/display/39742): non accessibili dallo strumento web nella nuova consultazione. I riscontri del rapporto del 7–8 ottobre sono storici; efficacia ministeriale e aggiornamento degli obblighi annuali non riconfermati. Non reintrodurre il Modello21 sulla sola copia 2025.
- [Portale StayTour Bellaria](https://imposta-soggiorno.org/bellaria-igea-marina/): identificazione confermata; nessun accesso autenticato/importazione. Nessuna dichiarazione o dato reale trasmesso.
- Dossier locale indicato dall’utente, `tassa_di_soggiorno.pdf`, sei pagine, SHA256 `07b9723f2a5480ca71d3dd8478167d196315b240ecc0646a0a9202ffc4853b3d`: scansioni lette visivamente. Conferma separatore, intestazione, otto campi delle registrazioni, esempio con separatore finale e scelta arrivo/partenza per la data di registrazione. Il codice 777 distingue le notti oltre sei; è riportata anche una nota sui limiti dieci per campeggi/camper. Due elenchi discordanti includono/omettono 450; data, versione e provenienza editoriale non attestati. È un dossier diverso dal PDF storico di due pagine, non una riconferma del suo hash. Contiene esempi nominativi: originale e derivati esclusi dalla pubblicazione e dalle fixture. Nessun dato reale usato nei test.
- Nella stessa cartella, `H294_MOD_50_0000000167.pdf` è una guida Rimini 2024, e `H294_MOD_50_0000000171.pdf` descrive il portale Rimini: non applicati alle strutture Bellaria. I manuali Ross1000 sono relativi al flusso statistico distinto.
- [Hyksos StayTour](https://www.hyksositalia.it/staytour.php) e [manuale ufficiale strutture](https://imposta-soggiorno.org/manuale-struttura.pdf): consultati nuovamente; confermano la parametrizzazione per Comune, senza dizionario tecnico Bellaria o certificazione dell’importazione generata.

La regola applicativa del compleanno incluso è quella operativa già documentata e collaudata: non viene presentata come nuova interpretazione ufficiale del termine «minori». La nuova verifica non modifica tariffe, età, tetti, periodi, residenza o motivi. Il flag `richiede_nota` resta un metadato del catalogo: il software non attesta l’esistenza delle autocertificazioni conservate esternamente. Le route Componenti legacy reindirizzano alla Schedina senza scritture; il controller legacy non è il writer attivo di quelle route.

## Matrice e prove correnti

[Evidenza minimizzata e digest](evidenze/tassa-audit-2026-10-23/verifica.json). Rischio ALTO per la validazione fiscale; nessuna migrazione o modifica di parametri normativi.

| Regola o difetto | Implementazione e prova | Risultato |
|---|---|---|
| Esenzione solo su parte del soggiorno | Riepilogo da segmenti, non dal solo flag aggregato della persona; nuovo test del compleanno | 4 esenti + 2 tassate + 4 oltre tetto = 10 notti; €3 |
| Esenzione totale oltre sei notti | Motivo: sole sei notti prima del limite; 777 separato | 6 esenti + 4 oltre tetto = 10; €0, senza doppio conteggio |
| Numero persone per motivo | Identità locale schedina/persona, distinta dai segmenti tariffari | Fixture di due persone: codice400, 10 notti, 2 persone; 777=8 notti; imponibili=2, €3 |
| Stampa del periodo per arrivo | Dicitura del calcolo e avviso esplicito; convenzione del CSV preservata | Soggiorno giugno–luglio: €9 nel periodo per arrivo; non indicato come riversamento o incasso |
| Intestazione del controllo/stampa | Profilo effettivo usato dal dettaglio, anche senza legacy; dati non disponibili non trasformati in tariffa zero | Nuova riproduzione RED, poi tariffa €1,50/tetto6 corretti con totale €9 |
| Nascita assente | Nessuna classificazione implicita come adulto; blocco quando serve verificare l’età, anche con400 | Titolare/componente: CSV e ricevuta422, consolidamento rifiutato senza export; esenzione esplicita410 indipendente dall’età conservata |
| Nomi lunghi, accenti e impaginazione | Andata a capo dentro celle e intestazioni compatte | Tre riproduzioni RED, poi tre browser PASS; riepilogo A4 di una pagina, senza colonne invase |
| Periodi, età, cap, categorie e anni supportati | Suite Bellaria esistenti e nuovi casi; anni senza fonte/categorie non certificate restano bloccati | PASS nella selezione R8, senza nuove regole fiscali |
| Storico, rettifica e isolamento | Snapshot/versioni, ricevute, immagini e HTTP su tenant distinti | PASS; nessun ricalcolo dello snapshot consolidato |
| Upload e guasto dopo scrittura | JPG/PNG/WebP sintetici validi, controlli formato/limite/storico/tenant e rollback del nuovo file | PASS senza GD; nessuna asserzione rimossa |
| Schedine, Arrivi, Web Check-in, Questura e ISTAT | Selezione di regressione esplicita, SOAP solo double e HTTP esterno simulato | PASS; nessun invio reale |

R8: **290 test PHP / 4.528 asserzioni, zero fallimenti/errori/salti; 7 browser PASS**, di cui tre percorsi 1440/768/390. PHP8.3.36/MariaDB11.8.9 nella sola VM ufficiale, DB/storage effimeri e namespace senza rete esterna. Build Vite/GEO, 23 override rifiutati prima delle migrazioni e controlli di sicurezza integrati PASS. Suite separata `test_database_policy.py test_guards.py`: **15 test PASS**; non si sommano le cinque guardie già ripetute nel launcher. Sintassi PHP di sette file PASS. Pint ha corretto solo la spaziatura e rimosso un import inutilizzato del nuovo test; metodi e asserzioni verificati identici.

PDF: **13 documenti / 14 pagine**. Ispezione visiva del riepilogo, zero, immagine e delle due pagine del gruppo PASS; gruppo €318 in due pagine, ricevute individuali e anteprime in una. [Riepilogo sintetico](evidenze/tassa-audit-2026-10-23/riepilogo-sintetico.png), [gruppo, pagina2](evidenze/tassa-audit-2026-10-23/ricevuta-gruppo-pagina2.png). Le scansioni del dossier utente non sono incluse nelle evidenze pubbliche.

Tentativi conservati privatamente e distinti da R8: R0 STOP schema strutturale omesso nella nuova copia; R1 STOP build/configurazione radice omessa; R2 tre difetti riprodotti e un’aspettativa della vista legacy già eliminata; R3 overflow in tutti i browser; R4 287/4439 e due salti GD, tre browser nuovi PASS/quattro storici fermi su percorsi macOS; R5 intestazione errata; R6 288/4494 senza salti e sette browser PASS; R7 nascita mancante con CSV200, corretta prima di R8. I percorsi degli artefatti nei vecchi spec ora usano `testInfo.outputPath`; non è stato creato `/private` nella VM né allentata una guardia. La manutenzione Git automatica nata dal fetch è stata interrotta sul solo processo identificato, senza cancellazioni o cambio di configurazione; non è dimostrata causa unica della lentezza.

Comando della selezione finale: `tests/Isolation/run.py --phpunit` con TassaRiepilogoEsenzioniAuditTest, TassaBellariaCompletenessTest, TassaBellariaEtaCsvTest, TassaBellariaVersionamentoTest, TassaBellariaAnteprimaTest, TassaBellariaConfigurazioneAutomaticaTest, TassaBellariaRiallineamentoTest, TassaPeriodoComunaleTest, ConfigurazioneTassaSuperadminTest, TassaDiSoggiornoServiceTest, QuesturaTransportGuardTest, QuesturaPayloadCoherenceTest, IstatConformitaTest, IstatTransmissionSecurityTest, WebCheckinCircuitoTest, SchedinaStoreTest, SchedinaSoggiornoTest, ArriviLifecycleTest, WebCheckinHtmlTest, WebCheckinLifecycleTest; fixture `tassa-riepilogo-audit-fixtures.php`; browser `tassa-riepilogo-audit`, `tassa-bellaria-anteprima`, `tassa-bellaria-ricevuta`. Esecuzione esclusivamente nella copia VM con guardie istanziate dal template ufficiale; nessun test Laravel sull’host. È lo schema strutturale interno senza dati, non una prova di installazione da schema vuoto.

## Impatto operativo dei dati incompleti

Conteggi produttivi READ ONLY: dieci nascite assenti sulle Schedine, zero sui Componenti. Nove appartengono ad Arrivi/Bozze; **una Schedina effettiva sovrapposta alla stagione2026 richiede completamento della nascita da parte dell’operatore**. Nessun valore inventato o record corretto automaticamente. Il software corretto segnala il dato necessario invece di addebitare implicitamente la tariffa adulta. Questo intervento non certifica che tutti i dati operativi siano completi.

## Git e rilascio

Correzioni e collaudi locali completati; PR, integrazione e C5 in preparazione. Non si dichiara ancora una nuova versione online. Il dossier fiscale è distinto dal rilascio tecnico delle correzioni.

## Criteri di chiusura e pubblicazione

A) Funzionamento tecnico: correzioni verificate nella selezione R8 e nei documenti sintetici; pubblicazione e QA online ancora da attestare.

B) Corrispondenza municipale: parziale finché fonti, vigenze e raccordo dei codici sono incompleti. Il rapporto per arrivo non può essere presentato come ammontare del riversamento mensile.

C) Accettazione esterna StayTour/Comune: non verificata. Un CSV generato e riconciliato localmente non dimostra l'accettazione del portale, inclusi file vuoti, 450, codifica e terminatori.

Nessun rilascio se resta un difetto critico di calcolo, integrità o tenancy. Preparare PR con evidenze minimizzate; preservare gli artefatti operativi privati fuori dalla storia pubblica. Deploy soltanto con procedura C5 corrente, nuovo preflight e recuperi indipendenti, QA, pulizia dei soli dati propri e monitor prescritti. Autorizzazione software distinta da presentazione di dichiarazioni e invio di dati reali.

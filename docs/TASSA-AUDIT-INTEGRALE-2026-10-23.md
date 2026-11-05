# Tassa di soggiorno — audit integrale iniziato il 22 ottobre 2026

## Esito corrente — 23 ottobre 2026

**Applicativo `4a9c0c55ce9319bef89dea97b9a1885c1ace5e66` pubblicato mediante C5 e verificato online. BASELINE IN AUDIT; chiusura integrale del settore non attestata per i pendenti esterni e operativi elencati sotto.**

### A — Funzionamento tecnico verificato

PR #10 e #11 riconfermate integrate rispettivamente in `118d5d20b309e15226ab63c2b84e40318d99b136` e `46736ee26f692376d7c3bc8e95a8e0ae5ae96b2e`. La produzione iniziale era `67b4c45f94a349d0b27c7874aacbbe770bc1cf00`. PR #12 integra la protezione del residente (`74300449d4704619259b2d7ba8b40291f26e94c2`); PR #13 integra la correzione Web Bellaria (`4a9c0c55ce9319bef89dea97b9a1885c1ace5e66`). Revisione automatica del codice delle ultime due PR completata senza rilievi.

Qualificazione definitiva R17 nella VM ufficiale: **294 test PHP / 4.593 asserzioni, sette browser, 15 guardie pure, 23 override rifiutati, build Vite/GEO PASS**, senza errori, fallimenti o salti. Tredici PDF / 14 pagine. I 44 digest qualificati coincidono con il candidato integrato. Nessun test Laravel sull’host o sul DB operativo.

C5 finale: nuovi preflight, PREPARE, recupero indipendente, custodia Extra autenticata e decifrata con chiave separata, 55 campioni di quiete e nuovo S0/RUN. **S0 145,390 s / RUN 248,253 s**, zero DDL, 159 migrazioni e 66 tabelle. Gate prima RUN 3.165.569.024 byte liberi contro 3.092.901.888 necessari, comprensivi della riserva di 1 GiB. Limiti 90/240 s e budget 1800/600/1379/421 invariati. Il medesimo timeout storico di decifratura non è ricomparso nei nuovi recuperi; la contesa Git osservata è stata rimossa sul solo worker identificato, ma la causa esclusiva storica resta non dimostrata.

SHA servito attestato da Git pulito, sonda FPM autenticata, 205 digest e nove sonde HTTPS/TLS/CSS. Prima della QA, DB byte-identico a S0 e 92 persistenti conformi. Guardiano concluso, disarmato, inattivo e disabilitato.

QA definitiva: **29 scenari Web e tre percorsi Tassa a 1440/768/390 px**, 62 immagini e tre PDF fiscali di una pagina. Compilazione, salvataggio parziale, revisione/conversione, Storico/Cestino, HTML, GEO, editor/palette, anteprima senza scritture, revoca/scadenza/rigenerazione e isolamento A/B superati. Tassa: 20 notti = 10 esenti + 8 oltre limite + 2 imponibili, €3; stampa/CSV riconciliati, consolidamento e rettifica con versione precedente immutata. Nascita assente e residente bloccano gli output fiscali; il modulo Web resta compilabile. Tariffa arbitraria respinta422, modifica catalogo hotel403, accessi incrociati404, configurazione centralizzata riservata al Superadmin. Questura e ISTAT verificati anche in sola lettura online. Zero POST di invio o dichiarazioni, nessun CSV fittizio trasmesso a StayTour.

Tentativi NONPASS conservati: prima QA743 ha dimostrato il difetto Web corretto in PR13; prima QA4a9 interrotta da `socket hang up` dopo il ripristino dal Cestino, causa non attribuita, poi ripetizione integrale con nuove fixture e identici timeout/controlli PASS. Il primo adattatore della prova Tassa cercava un meta CSRF assente: corretto il solo selettore sul token del modulo reale e ripetuta la prova fiscale, asserzioni invariate. Nessuna modifica applicativa dopo PR13.

Tutte le fixture dei diversi tentativi sono state rimosse: HMAC delle 66 tabelle preesistenti identici e 92 persistenti conformi. Nessun restore/reset dei dati o dei contatori AUTO_INCREMENT. Sorgenti, SQL/ridump, chiavi, backup, estratti dei recuperi e diagnostici preservati; ritirate soltanto cache chiuse o repliche già verificate su Extra, con prove di inutilizzo e sigilli. Condivisione dei soli blocchi di codice identici tramite FIDEDUPERANGE con byte e metadati invariati.

Monitor finali dopo pulizia: **server 918.372 s / 31 campioni / 279 sonde; Mac 920.258 s / 31 campioni / 93 HTTPS, entrambi PASS**. Zero errori Laravel/FPM dall’intero primo T0 della task (2026-10-23T10:41:55 locali). Il diagnostico root precedente al T0 resta custodito e non è contato come errore nuovo. Campionamento capienza: 10686 misure, minimo VM 1140035584 byte sopra la riserva di 1 GiB. Daily945ef553, XFS/SELinux, HEAD originale6a652b72 e quattro documenti preesistenti preservati; VM restituita spenta come all’inizio e soli presidi temporanei propri conclusi. [Ricevuta minimizzata](evidenze/tassa-audit-2026-10-23/rilascio.json), [stampa online](evidenze/tassa-audit-2026-10-23/stampa-online.png), [controllo mobile](evidenze/tassa-audit-2026-10-23/controllo-mobile-online.png).

### B — Corrispondenza municipale verificata e limiti

CC29/2026, allegato regolamento e tariffe GC184/2025 recuperati dall’albo ufficiale; pubblicazione MEF della CC29 il 14 agosto 2026, decorrenza 1 ottobre 2026 secondo l’atto. Confermati tariffe alberghiere, stagione giugno–settembre, sei notti, esclusione residenti/day-use, esenzioni e documentazione esterna. Comunicazione/riversamento entro 15 giorni dalla fine del mese; soggiorni a cavallo nel mese di conclusione per il riversamento, distinti dal CSV applicativo per arrivo. Dichiarazione annuale ministeriale entro il 30 giugno successivo; rimozione del Modello21 nella CC29. Il report applicativo attesta il calcolo selezionato, non incasso, saldo o versamento: non esiste un registro degli incassi.

Il familiare convivente è previsto dall’art.5.1.i; resta da attestare la mappatura tecnica **450** della versione StayTour Bellaria, perché i due elenchi del dossier sono discordanti. Nessun codice inventato o modifica arbitraria al catalogo. Il compleanno incluso resta la conferma operativa umana storica, non una nuova attestazione comunale. Tre strutture senza classificazione e una Schedina effettiva senza nascita pertinente alla stagione richiedono dati reali dell’operatore; soli identificativi conservati privatamente, nessuna correzione fittizia.

### C — Accettazione StayTour non verificata

Manca una sessione gestore autenticata SPID/CIE oppure un ambiente ufficiale di test senza effetti fiscali. Il login pubblico riserva le credenziali ordinarie agli operatori comunali; nessun validatore pubblico individuato. Non è stato possibile ispezionare l’importazione autenticata, quindi non si afferma che consenta soltanto importazioni fiscali. Nessun invio al portale e nessuna dichiarazione presentata. Servono il tracciato vigente con il raccordo450 e la verifica mediante validatore ufficiale o modalità di prova comprovata. Generazione e riconciliazione locale del CSV non equivalgono ad accettazione esterna.

Le sezioni seguenti conservano lo storico dell’audit e dei tentativi; per lo stato corrente prevale questo consuntivo.


**BASELINE IN AUDIT. Storico dell’audit; per l’esito corrente vedere il consuntivo iniziale.**

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

Correzioni `1f4fab658696e5a0596df9a3f379367695d816f7` integrate mediante [PR10](https://github.com/joomlu/Schedinedinotifica/pull/10); candidato `118d5d20b309e15226ab63c2b84e40318d99b136`. Deposito e preflight C5 PASS, nove sonde positive e nessuna migrazione. Kit derivato da quello effettivo corrente, profili PHP8.3.36 verificati contro RPM e sigilli correnti, controlli e budget invariati. Il primo deposito era stato rifiutato dalla revisione automatica per autorizzazione non riconosciuta; ripresentata la stessa azione dopo rilettura del punto9 dell’allegato utente, approvata senza aggiramenti.

**PREPARE NON PASS: recupero indipendente Linux non completato; nessun RUN/deploy.** Trasferimento cifrato completato, archivio decifrato parziale e nessun file estratto. Il messaggio del verificatore non espone il dettaglio dell’eccezione; una diagnosi separata sul medesimo cifrato verso `/dev/null` riproduce `TimeoutExpired` dopo90secondi, con chiave soltanto nella VM. La causa prestazionale sottostante non è attribuita. Nessun timeout o limite aumentato; questo tentativo non costituisce recuperabilità verificata. Cifrati e diagnostici conservati privatamente, nessuna custodia Extra di questo tentativo dichiarata valida.

Rilettura produttiva successiva: Git `67b4c45f94a349d0b27c7874aacbbe770bc1cf00` pulito, FPM attivo, barriera assente, stato precedente accettato/concluso; nuovo piano RUN assente e nuovo PREPARE non verificato. Questa è evidenza del Git installato e della salute precedente, non attestazione del candidato servito. QA sintetica online preparata ma non eseguita, nessuna fixture online creata e nessuna pulizia dati necessaria; monitor900s del candidato non eseguito perché non pubblicato. [Ricevuta parziale](evidenze/tassa-audit-2026-10-23/rilascio-parziale.json).

Ripresa necessaria: diagnosticare le prestazioni del verificatore senza indebolire i presidi, riconfermare capacità Mac/VM/Extra, nuovo PREPARE e recupero indipendente nei limiti, custodia Extra,55campioni quiete, nuovo S0/RUN, verifica SHA effettivamente servito e DB/S0, QA sintetica, pulizia verificata e monitor900s. Nessun vecchio checkpoint da promuovere. Il dossier fiscale resta distinto dal rilascio tecnico.

## Criteri di chiusura e pubblicazione

A) Funzionamento tecnico: correzioni verificate nella selezione R8 e nei documenti sintetici; pubblicazione e QA online ancora da attestare.

B) Corrispondenza municipale: parziale finché fonti, vigenze e raccordo dei codici sono incompleti. Il rapporto per arrivo non può essere presentato come ammontare del riversamento mensile.

C) Accettazione esterna StayTour/Comune: non verificata. Un CSV generato e riconciliato localmente non dimostra l'accettazione del portale, inclusi file vuoti, 450, codifica e terminatori.

Nessun rilascio se resta un difetto critico di calcolo, integrità o tenancy. Preparare PR con evidenze minimizzate; preservare gli artefatti operativi privati fuori dalla storia pubblica. Deploy soltanto con procedura C5 corrente, nuovo preflight e recuperi indipendenti, QA, pulizia dei soli dati propri e monitor prescritti. Autorizzazione software distinta da presentazione di dichiarazioni e invio di dati reali.

## Ripresa autorizzata — 23 ottobre 2026

Riconfermati PR10/11 integrati, main46736ee2 e codice applicativo identico a118d5d20, produzione Git67b4c45f pulita/FPMattivo/barriera assente. Quattro documenti sporchi e HEAD6a652b72 del checkout originale preservati. VM inizialmente spenta, avviata senza tuning o crescita.

Il medesimo cifrato storico452.960.048byte viene decifrato verso/dev/null in6,353s con gli stessi AES256-CBC/PBKDF2/600.000iterazioni e timeout90s. Prova sintetica1MiB/600.000iterazioni0,409s. Individuato un nuovo GC/repack partito dal fetch della ripresa nella worktree: interrotto il solo worker identificato; nei comandi successivi sia `gc.auto=0` sia `maintenance.auto=false`, senza configurazione globale. La contesa è un fattore osservato, non prova della causa esclusiva storica. Nessun algoritmo, limite o guardia C5 modificati. Il nuovo PREPARE sul candidato118d5d20 ha completato il recupero indipendente con esitoPASS; non è il checkpoint fallito e non sarà promosso per un candidato diverso.

Capacità recuperata ritirando undici sole cache MariaDB di recuperi conclusi, dopo identità dump/ridump, inventario e assenza utilizzatori. SQL/ridump/cifrati/chiavi/daily/backup preservati. Misura3.243.339.776→5.418.147.840byte, non equiparata a incremento fisico del Mac. Nuovo recupero completo1.074.749.440byte e ingresso cifrato482.893.824; gate residuo3.090.485.248byte con massimo storico, crescita,32MiB e riserva1GiB invariati.

### Fonti ufficiali recuperate e decorrenze

Il limite dello strumento web è stato superato mediante lettura HTTP dei pulsanti pubblici dell’albo: recuperatiCC29/2026, regolamento e confronto allegati, GC184/2025 e allegato tariffe. [Registro fonti e SHA256](evidenze/tassa-audit-2026-10-23/fonti-ripresa.json). Gli elenchi ufficiali [MEF2026](https://www1.finanze.gov.it/finanze2/dipartimentopolitichefiscali/fiscalitalocale/nuova_at/dati/download.htm?anno=2026) attestano CC29 del28/07/2026 pubblicata14/08/2026: decorrenza1ottobre2026 secondo la disposizione dell’atto, salvo le norme derivanti direttamente dal decreto. Gli elenchi2025 attestanoCC22 pubblicata18/04/2025, CC71 eGC185 pubblicati16/01/2025. La nuova pagina comunale continua a esporre il testo2025, ma non è l’unico riscontro disponibile.

GC184 conferma per2026 le tariffe già vigenti: alberghi1–2stelle€1,00,3stelle€1,50,4–5stelle€2,50. Non risulta una nuova rigaGC184 perIDS nell’elencoMEF2026 consultato: non si inventa una data di pubblicazione del medesimo atto. Il regolamento conferma1giugno–30settembre,6notti alberghi, esclusione day-use e residenti, esenzioni art.5 e documentazione da conservare presso la struttura. Le modificheCC29 riguardano responsabilità/obblighi e rimozione delModello21, non nuove aliquote o nuove esenzioni. Comunicazione e riversamento entro15giorni dalla chiusura del mese; attraversamenti finanziari nel mese di conclusione, distinti dal CSV per arrivo. Dichiarazione ministeriale annuale entro30giugno successivo, distinta dal conto di gestione.

La condizione dei familiari del gestore anagraficamente conviventi è confermata dall’art.5.1.i anche nel testo recuperato2026: il dubbio450 è sul raccordo del codice nel tracciato, non sull’esistenza della condizione. Due elenchi nel dossier utente restano discordanti; nessun codice aggiunto, eliminato o certificato per assunzione. Il termine «minori di18» non contiene un’istruzione esplicita sulla singola notte del compleanno: conservata la conferma operativa umana documentata nel Maestro, senza presentarla come certificazione comunale nuova.

### Residenza — difetto riprodotto e correzione circoscritta

R10RED: residente italiano aBellaria con esenzioneNO ottiene200 invece del blocco atteso. Ora il solo calcoloBellaria riceve nazione/comune di residenza di titolare e componenti. Quando la residenza italiana aBellaria è esplicita e sono presenti notti nel periodo fiscale, calcolo/CSV/consolidamento sono sospesi con errore `residenza`: il residente non è soggetto passivo e manca un raccordo documentato per rappresentarlo nel tracciato. Non viene né addebitata tariffa adulta né inventato un codice comunale. Omonimo estero e soggiorno fuori stagione non sono bloccati; la Schedina resta registrabile, con calcolo fiscale non disponibile. Uno snapshot già consolidato resta leggibile e identico dopo correzione della residenza; nessuna riscrittura dello storico.

R9 era stato fermato dalla guardiaSHA: la cachePython dell’adattatore caricava il vecchio template perchémtime0 e dimensione erano invariati. Corretto il solo timestamp di trasferimento dei template, non il controllo. R10 conserva la riproduzione; R11mirato8test/74asserzioniPASS. R12definitivo:293testPHP/4557asserzioni e7browser1440/768/390PASS; buildVite/GEO e23overridePASS. Dieci testpolicyDB e cinqueguardie purePASS; una prima invocazione delle guardie senzaNode nelPATH eraNONPASS, ripetuta conPATHufficiale senza modifiche. Pint del nuovo testPASS. Il tentativo iniziale di custodiaExtra si è fermato prima della copia per directorymadre assente, corretto soltanto il deposito esclusivo. Il rilascio del nuovo candidato resta da attestare.

### Accettazione esterna

La pagina pubblica StayTourBellaria richiedeSPID/CIE per il gestore; le credenziali ordinarie sono dichiarate riservate agli operatori comunali. Nessuna sessione autenticata del gestore trovata e nessun validatore pubblico/sandbox dichiarato nella pagina. Richiesta all’utente una sessione già autenticata o un ambiente ufficiale senza effetti fiscali; nessuna richiesta di password/OTP. Non è dimostrato che il portale ammetta solo importazioni fiscali: la modalità autenticata non è stata ispezionata. Nessun CSV sintetico trasmesso in produzione al portale.

### Integrazione e primo RUN del candidato74300449

[PR12](https://github.com/joomlu/Schedinedinotifica/pull/12) integrata: commitcorrezione02dcbd2, merge74300449d4704619259b2d7ba8b40291f26e94c2; revisione automatica completata senza rilievi. Tutti42digest coincidono con i sorgentiR12. NuovoPREPARE743 e recupero indipendentePASS, custodiaExtra autenticata/decifrata integralmente,55campioni quiete e gate3.318.853.632contro3.090.530.304bytePASS. Kit e limiti invariati.

**PrimoRUN NONPASS in VERIFICA, prima di execute/DDL.** S0verificato indipendentemente; il runner ha trovato `storage/logs/laravel-2026-10-23.log` conUID/GID0:0. È il log creato dalla query diagnosticaPHP della task avviata come root, assente nelPREPARE e presente nelS0; la query aveva fallito per colonna geografica alias non esistente. La successiva lettura minimizzata è stata eseguita con il clientDBufficiale in transazioneREADONLY. La prima lettura più ampia era stata respinta dalla revisione automatica: eliminati nomi e campo `scheda`, conservati soltantoID operativi e classificazione fuoriGit.

Il guardiano ha terminato il worker e concluso **S0_RECUPERATO in217,352s**, recupero60,486s: DB/file/config/key/schema identici, baseline riaperta e nove sondepositive; scopeapplicativo non eseguito. NessunOOM nei diagnostici consultati. Il solo log della task è stato custodito privatamente sul server e riportato aUID1002/GID489: hash928c4be6…, contenuto/inode/mode/mtime/xattr identici, variazioneUID/GID/ctime registrata. Nessuna cancellazione o modifica dei contenuti dei log. Il guardiano concluso è inattivo/disabilitato.

Per il secondo tentativo: nuovi piano/approvazione/base/prebuild/nonce, nessuna promozione del primoS0. Lo statoS0_RECUPERATO è accettato dal solo adattatore dopo verifica stretta di conclusione del guardiano, identitànonce, preservazione e salute complete; il kit ufficiale resta invariato. Ripetuto anticipatamente, prima dell’armamento, lo stesso controllo di proprietà già presente nelrunner. CondivisioneFIDEDUPERANGE di soli blocchi identici di codice/dipendenze/asset in quattro recuperi chiusi:8.454file, byte/inode/UID/GID/permessi/tempi/xattr identici, nessun file rimosso,2.192.338.944→4.334.194.688byte liberi. Env/upload/SQL/DB/cifrati/chiavi/TLS esclusi. Successivamente ritirate soltanto replicheVM di unPREPARE concluso e cacheDB, dopo custodiaExtra di entrambe le catenePREPARE/S0 e nuovi confronti; estratti completi e protetti conservati. Nessuna crescita o tuning dellaVM.

La query minima riconferma tre strutture senza classificazione e una Schedina effettiva senza nascita pertinente alla stagione. SoliID in un file localeprivato600 per l’operatore, nessun nominativoospite esportato o valore reale inventato. Nessuna modifica a questi dati.

### Secondo RUN743 pubblicato; regressione online e candidato correttivo

Secondo C5 su74300449 PASS: S0147,714s/RUN246,755s, zeroDDL,159migrazioni/66tabelle,205digest e nove sondepositive. Recuperi indipendenti, custodieExtraPREPARE/S0 e55campioni quiete superati senza cambiare i limiti. PrimaQA dumpDB byte-identicoS0 e92persistenti conformi; guardiano concluso/disarmato/inattivo/disabilitato.

**QAonline NONPASS:** l’anteprima interattiva di una nuova richiestaBellaria priva della nascita riceve l’eccezione fiscale e ritorna alla reception. Copertura Web precedente prevalentementeRoma, combinazione non coperta. R14RED isolato riproduce302 anziché200. Correzione limitata a `WebCheckinController::publicShow`: intercetta soltantoValidationException del dettaglio fiscale e mostra il modulo con totale non disponibile, senza azzeramento o esenzione inventata. Le ricevute e gli export conservano i blocchi. Nuova regressione su nascita assente, residente e classificazione assente: anteprima senza scritture, GETospite, salvataggio parziale persistito e ricevuta422. R15 aveva aspettativa errata di redirect per il salvataggio parziale, contratto reale200 con conferma; rettificata soltanto tale aspettativa e aggiunta conferma testuale, non ridotte le verifiche di persistenza. R16mirato25test/316asserzioniPASS. R17definitivo294test/4593asserzioni e7browserPASS,15guardie/23override/buildGEO PASS. SintassiPASS dopo correzione del solo percorso del binarioVM; nessun testLaravel host.

Fixture del primo ciclo online rimosse: HMAC delle66tabelle preesistenti identici,92persistenti conformi, nessunrestore/reset o invio. Il candidato correttivo richiede nuovo C5/QA/monitor; non è ancora dichiarato servito. Ulteriori sole cache di recuperi chiusi ritirate dopo sigilli/SQL-ridump/inutilizzo; blocchi identici del codice condivisi conFIDEDUPERANGE, byte e metadati invariati. Backup, chiavi, SQL, estratti completi e dati reali preservati.

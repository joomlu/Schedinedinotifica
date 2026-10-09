# Imposta di soggiorno Bellaria Igea Marina — audit 2026-10-07

**Esito corrente (8 ottobre 2026): TASSA BELLARIA — P1 CATEGORIE CORRETTO, PRONTA PER RE-AUDIT FINALE. Vedere la chiusura finale; il precedente re-audit bloccato resta evidenza storica. P1 periodo/Schedina preservato.**

## Verdetto e perimetro

**TASSA DI SOGGIORNO BELLARIA — DA CORREGGERE.** Nessun P0 dimostrato; sei P1 direttamente riprodotti e un P1 di configurazione ricostruito staticamente. Il modulo esiste, ma non è certificabile come conforme. Audit locale, nessuna correzione applicativa. Stato generale **BASELINE IN AUDIT**.

Repository `/Users/jorgeluccitelli/Herd/Schedinedinotifica`, branch `main`, HEAD e riferimento locale `origin/main` `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`; nessun fetch richiesto/eseguito. Staging vuoto. All'inizio erano già presenti quattro tracked modificati e due untracked dell'harness MariaDB: vengono preservati. Il Maestro viene aggiornato soltanto con questa nuova evidenza.

**Aggiornamento documentale successivo nella sezione «Chiusura documentale pre-correzione»: reperiti CC29/2026 con allegati e GC184/2025 con tariffario 2026; analizzato il CSV fornito. Stato corrente del completamento: vedere la sezione finale «Completamento finale controllato Tassa Bellaria» e il suo esito conclusivo; la precedente chiusura età/777 è un gate storico. Le precedenti chiusure descrivono i gate storici.** Le sezioni iniziali conservano le evidenze e i limiti del primo audit; le pendenze superate sono indicate nella chiusura. La verifica normativa 2026 e la specifica tecnica di import **restano parziali**. Nessuna assenza di risultati di ricerca prova l'assenza di una delibera o di una specifica. Le prove diagnostiche verdi confermano il comportamento osservato, inclusi difetti; non significano conformità fiscale o accettazione dell'ente.

## Fonti primarie e versione del regolamento

Consultazione web del 7 ottobre 2026:

- [Pagina comunale del regolamento IDS](https://www.comune.bellaria-igea-marina.rn.it/it/documenti_pubblici/regolamento-imposta-di-soggiorno-ids): richiama CC 71/18.12.2024 e modifica CC 22/15.04.2025.
- [Testo pubblicato, regolamento 2025](https://municipium-images-production.s3-eu-west-1.amazonaws.com/s3/602/allegati/amministrazione/documenti-e-dati/atto-normativo/regolamento_ids_2025.pdf): letto il PDF di dieci pagine. È il testo accessibile pubblicato, **non prova che manchino successive modifiche**.
- [Avviso comunale IDS 2025](https://bellaria-igeamarina-api.municipiumapp.it/s3/602/allegati/amministrazione/uffici/imposta-di-soggiorno/avviso-imposta-di-soggiorno-2025-da-esporre.pdf): reperito testo indicizzato; accesso diretto all'allegato non riuscito. Conferma la disciplina stagionale del testo pubblicato.
- [Ufficio comunale IDS](https://www.comune.bellaria-igea-marina.rn.it/it/unita_organizzative/imposta-di-soggiorno): collegamenti a modulistica, gestione e tariffe.
- [Convocazione del 28 luglio 2026](https://www.comune.bellaria-igea-marina.rn.it/it/news/148299/convocazione-consiglio-comunale-di-martedi-28-luglio): contiene il punto «MODIFICA REGOLAMENTO IMPOSTA DI SOGGIORNO». Non è una deliberazione approvata.

**Stato al primo audit, superato per numero/approvazione/testo/impatto dalla chiusura documentale:** modifica 2026 inizialmente NON DIMOSTRATA; decorrenza fiscale puntuale ancora da documentare. Le ricerche mirate non hanno recuperato l'atto; il portale Citygov risponde 403 ai PDF consultati direttamente. L'indice comunale indica CC 33/28.07.2026 per **TARI**: non attribuirlo all'IDS. Serve delibera IDS e testo coordinato prima della certificazione definitiva. I confronti regolamentari sotto sono esplicitamente riferiti al testo pubblicato recuperato, da riconfermare sul coordinato 2026.

### Tariffario

[GC 185/25.11.2024, documento Citygov id 142330](https://citygov.comune.bellaria-igea-marina.rn.it/web/trasparenza/delibere-di-giunta?_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_action=mostraDettaglio&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_downloadSigned=false&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_fromAction=recuperaDettaglio&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_id=142330&p_p_cacheability=cacheLevelPage&p_p_col_count=1&p_p_col_id=column-1&p_p_id=jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet&p_p_lifecycle=2&p_p_mode=view&p_p_resource_id=downloadAllegato&p_p_state=normal): tabella recuperata dall'indicizzazione ufficiale; download diretto 403. Nessuna fonte alberghiera privata usata come autorità.

[Conferma tariffe 2026, Citygov id 163791](https://citygov.comune.bellaria-igea-marina.rn.it/iw_IL/web/trasparenza/papca-ap?_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_action=mostraDettaglio&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_downloadSigned=false&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_fromAction=recuperaDettaglio&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_id=163791&p_p_cacheability=cacheLevelPage&p_p_col_count=1&p_p_col_id=column-1&p_p_id=jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet&p_p_lifecycle=2&p_p_mode=view&p_p_resource_id=downloadAllegato&p_p_state=normal): dispositivo indicizzato conferma per 2026 le tariffe vigenti e richiama allegato C e GC 185/2024. Numero/data della delibera di conferma e allegato C integrale **non recuperati**; non inventati.

Nel codice non esiste un tariffario per categoria: esiste **un'aliquota manuale per struttura**, con default Bellaria €1,50. Non sono stati interrogati DB operativi: le aliquote effettivamente salvate per strutture reali sono **NON VERIFICATE**. La colonna software riguarda dunque il default su configurazione vuota, non una misura dei dati reali.

| Tipologia | Categoria | Software, default | Comune, GC185 | Confronto default |
|---|---|---:|---:|---|
| Alberghi | 1 stella | 1,50 | 1,00 | FAIL |
| Alberghi | 2 stelle | 1,50 | 1,00 | FAIL |
| Alberghi | 3 stelle | 1,50 | 1,50 | PASS numerico soltanto |
| Alberghi | 4 stelle | 1,50 | 2,50 | FAIL |
| Alberghi | 5 stelle | 1,50 | 2,50 | FAIL |
| Campeggi | 1–2–3 stelle | 1,50 | 0,50 | FAIL |
| Campeggi | 4 stelle | 1,50 | 1,00 | FAIL |
| Campeggi | 5 stelle | 1,50 | 1,50 | PASS numerico soltanto |
| Aree sosta camper | categoria unica | 1,50 | 0,20 | FAIL |
| Country House | categoria unica | 1,50 | 1,00 | FAIL |
| Ostelli | categoria unica | 1,50 | 1,00 | FAIL |
| Affittacamere | categoria unica | 1,50 | 1,00 | FAIL |
| Case vacanze | categoria unica | 1,50 | 1,00 | FAIL |
| Bed & Breakfast | categoria unica | 1,50 | 1,00 | FAIL |
| Case per ferie | categoria unica | 1,50 | 1,00 | FAIL |
| Residenze appartamenti / ammobiliati turistici | categoria unica | 1,50 | 1,00 | FAIL |
| Agriturismi | categoria unica | 1,50 | 1,00 | FAIL |
| Locazioni brevi art.4 DL50/2017 | categoria unica | 1,50 | 1,00 | FAIL |
| Altre tipologie | residua | 1,50 | 1,00 | FAIL |

RTA e villaggi sono nel presupposto pubblicato: la loro assegnazione alla categoria tariffaria concreta deve essere documentata, non dedotta automaticamente dalla voce residua. Nessuna promozione della tabella a tariffario coordinato 2026 senza allegato C.

## Inventario e circuito reale

### Nucleo

- Modelli `app/Models/TassaDiSoggiorno.php`, `TassaEsenzione.php`, `Tassa.php`: primi due attivi; `Tassa` legacy non costituisce un registro delle comunicazioni. Dominio condiviso `Schedina`, `Componenti`, `Customers`, `Struttura`, tipologie/classificazioni.
- Controller `TassaDiSoggiornoController`: configurazione, default e creazione esenzioni; `TassaEsenzioneController`: CRUD con ruolo admin/super_admin; `TassaReportController`: rapporto, controllo, CSV e stampa; `SchedinaController`: liste, editor, dettaglio e `printTassa()`.
- Service `app/Services/TassaDiSoggiornoService.php`: unico calcolo IDS principale; helper `app/Support/Anagrafica/EtaOperativa.php`.
- `routes/web.php:121–133`: GET/PUT configurazione, rapporto/CSV, controllo/CSV/stampa, CRUD esenzioni; `:392` stampa tassa Schedina. Gruppo `auth`, configurazione senza gate admin specifico.
- Tenant: `AppartieneAStruttura`, `StrutturaCorrente`, middleware `ImpostaStrutturaCorrente`; log generico `LogOperativeAudit`, **non** storico dichiarazioni.
- Writer effettivi: `TassaReportController::exportCsv()` e `exportControlloCsv()`; stampe HTML `printControllo()` e `SchedinaController::printTassa()`. `TassaDiSoggiornoService::exportRows()` è un generatore distinto di array: il CSV comunale **non lo usa**.

### Ingressi e superfici condivise

`ComponentiController`, `ArrivalsController`, `WebCheckinController`, `SchedinaController`, `ComponentiImportService`, `Support/Componenti/ContrattoImportazioneComponentiV1` e `DatiComponenteNormalizzati`: propagano/normalizzano `exent`; non creano un archivio IDS separato. `WebCheckinController` calcola il dettaglio attraverso lo stesso service. I componenti non hanno date autonome di arrivo/partenza nel modello: tutti ereditano quelle della Schedina nel calcolo.

`CestinoService/CestinoController`: archivio/restore delle esenzioni; `QaTenancy`: include tabelle IDS nei controlli generici. `PresenzeReportService/PresenzeController` e superfici statistiche contigue sono report presenze, non writer fiscale IDS. `IstatTabellaAService` resta indipendente e protetto. `HomeController`, `QaController`, `CrmController`, `GestioneOperativaController`, `HelpCenterController`: menu, contatori, cronologia operativa e testi di guida; nessun trasporto comunale identificato.

### Viste, JavaScript, configurazione

`resources/views/tassa_di_soggiorno/{edit,create,index,form,rapporto,rapporto-controllo,rapporto-print}.blade.php`; viste attive configurazione `edit`, rapporti e stampe. `create/index/form` sono superfici legacy non collegate alle route principali correnti.

`resources/views/schedina/{new,edit,list,print-tassa}.blade.php`, `partials/form.blade.php`; `arrivals/new`, `web-checkin/public`, `customers/storico`, `layouts/sidebar`, `strutture/index`, viste `statistica/presenze/*` e `istat_tabella_a/*` hanno riferimenti condivisi o navigazione. `resources/scss/custom.scss` contiene stili tassa. Script inline Blade per tab, filtri e azioni; nessun client JavaScript STAYTOUR trovato. Nessun Livewire, job, command di invio IDS, file `config` IDS o scheduler di comunicazioni individuato. Questa conclusione riguarda il repository, non servizi esterni.

Seeders `TassaEsenzioniSeeder` (E01–E06 generici) e `TassaEsenzioniBellariaSeeder` (400–450); il secondo non rimuove le voci generiche e il controller usa `firstOrCreate`. Possibile coesistenza di cataloghi: DB reali non verificati; seeders non eseguiti nell'audit.

### Migration pertinenti

- `2025_11_16_000002_create_tassa_di_soggiorno_table.php`;
- `2025_11_16_120000_add_struttura_id_to_tassa_di_soggiorno_table.php`;
- `2025_11_16_120100_add_nullable_struttura_id_to_tassa_di_soggiorno.php`;
- `2025_11_16_120200_popola_struttura_id_tassa_di_soggiorno.php`;
- `2025_11_16_120300_struttura_id_notnull_fk_tassa_di_soggiorno.php`;
- `2025_11_16_120400_delete_orfani_tassa_di_soggiorno.php`;
- `2025_11_16_130000_add_note_to_tassa_di_soggiorno_table.php`;
- `2026_03_02_000004_add_struttura_to_business_tables.php` e `2026_03_02_142000_add_struttura_to_business_tables.php` (anche tabella legacy `tassa`);
- `2026_03_05_121000_create_tassa_esenzioni_table.php`;
- `2026_03_15_221000_change_schedina_exent_to_string.php`;
- schema condiviso `create_schedina_table_if_missing`, `componenti`, `struttura`, tipologie/classificazione e relativa evoluzione. Nessuna migration IDS di snapshot/rettifica trovata.

Test specifici `tests/Unit/TassaDiSoggiornoServiceTest.php`, `EtaOperativaTest.php`, browser `tests/Feature/tassa-di-soggiorno.playwright.spec.js`. `ComponentiImportFormTest` contiene contesto tassa senza certificare il circuito fiscale. Nessuna suite IDS completa preesistente di tenancy/storico/import STAYTOUR individuata.

### Circuito

Struttura contiene Comune, tipologia e classificazione, CIR/CIN e altri codici; nessun campo dedicato STAYTOUR. La configurazione legge il Comune testuale per attivare i default. **Categoria/stelle non determinano tariffa o limite**. Schedina e Componenti forniscono persone, nascita ed `exent`; il service calcola notti e subtotali, le viste mostrano importi dinamici; il controller rigenera CSV dalla stessa base. Download manuale è l'ultimo passo implementato: consegna, pagamento e accettazione non registrati.

## Regole, esenzioni e mapping

Nel testo pubblicato: periodo **1 giugno–30 settembre**, tetto **6 notti consecutive**, **10 nei campeggi**, **10 al mese nelle aree camper**; day-use escluso. Presupposto per non residenti. Artt.2–5 del [regolamento](https://municipium-images-production.s3-eu-west-1.amazonaws.com/s3/602/allegati/amministrazione/documenti-e-dati/atto-normativo/regolamento_ids_2025.pdf) e [avviso](https://bellaria-igeamarina-api.municipiumapp.it/s3/602/allegati/amministrazione/uffici/imposta-di-soggiorno/avviso-imposta-di-soggiorno-2025-da-esporre.pdf).

| Regola / motivo | Software | Esito |
|---|---|---|
| Periodo e tetto | Default marzo–1 ottobre / 6 per ogni Schedina | FAIL |
| Minori 18 | Soglie manuali 17/18; età odierna | FAIL storico |
| Residenti | Nessun controllo residenza; voce legacy non coincide con catalogo Bellaria | FAIL mapping |
| Sanità, invalidità | 405/410: etichette generiche; nessuna verifica requisiti/numero accompagnatori | PARZIALE |
| Volontariato / calamità | 415/420 selezionabili | PARZIALE |
| Autisti / accompagnatori | 425 generico; nessun rapporto 1:20 | PARZIALE |
| Dipendenti | 430; scelta legacy capo diversa | FAIL mapping capo |
| Servizio pubblico | 440 generico | PARZIALE |
| Familiari conviventi | 450 | PARZIALE |
| Gruppi / convenzioni / riduzioni | Nessun motore specifico | NON DIMOSTRATO; non inventare sgravi |

[Autocertificazione ufficiale](https://bellaria-igeamarina-api.municipiumapp.it/s3/602/allegati/amministrazione/uffici/imposta-di-soggiorno/autocertificazione-per-esenzione-degli-ospiti.pdf): distingue requisiti sanitari, invalidità 100%, accompagnatori e rapporto gruppi; documento da conservare presso la struttura. Il gestionale salva solo `exent`; `richiede_nota` è metadato del catalogo, non vincolo sul soggiorno. Nessun collegamento dedicato all'autocertificazione trovato. Un archivio cartaceo esterno può adempiere alla conservazione, ma il software non ne attesta l'esistenza.

I codici 400,405,410,415,420,425,430,440,450 e 777 sono **codici applicativi presenti**, non codici STAYTOUR certificati dalle fonti recuperate. E01–E06 e fallback `ETA_BIMBI/ETA_MIN` possono coesistere; compatibilità import NON DIMOSTRATA. `resolveEsenzione()` accetta soltanto codice o descrizione esatta, case-insensitive. Le opzioni del capo in `schedina/partials/form:235–240` sono stringhe legacy; quelle dei componenti usano il catalogo dinamico. La guida UI promette che la scelta venga usata dal calcolo, ma alcune stringhe non vengono risolte.

## Calcolo e casi limite

Per persona: notti nel periodo → `min(notti, giorni_massimo)` → zero se esente, altrimenti moltiplicazione per aliquota; somma dei subtotali. Date di partenza escluse dal conteggio. Periodo comparato mediante mese/giorno, ignorando anno; aliquota float, cast DB a due decimali. Nessun conteggio cumulativo persona/mese per camper; nessun collegamento di Schedine che spezzano un soggiorno consecutivo. Nessuna categoria storicizzata.

| Scenario sintetico / analisi | Evidenza |
|---|---|
| Adulto, 9 notti a €1,50, tetto 6 | €9,00; 3 oltre max |
| Due adulti | €18,00; somma riconducibile alle righe |
| Gruppo 5 adulti | €45,00 |
| Famiglia 2 adulti + minore, 2 notti | €6,00 |
| Stesso giorno | 0 notti / €0 |
| 31 maggio–2 giugno | 1 notte nel periodo configurato corretto |
| 30 settembre–2 ottobre | 1 notte nel periodo configurato corretto |
| 31 dicembre–2 gennaio | 0 notti, periodo estivo |
| Partenza anticipata / prolungamento | €6 → €3 → €18 nella famiglia sintetica |
| Componente aggiunto/eliminato | Ricalcolo immediato; eliminazione adulti riporta totale a €9 |
| Tariffa cambiata | Ricalcolo a €15, senza versione precedente |
| Esente 410 senza allegato/nota | €0 comunque |
| Residente legacy, catalogo Bellaria | Non esente; €9 |
| Minore al soggiorno, diventato maggiorenne | Identico soggiorno passa €0 → €9 cambiando solo clock |
| Date invertite | `diffNotti()` restituisce 0; non il valore assoluto ipotizzato inizialmente |
| Configurazione assente | Importo zero silenzioso; non prova conformità |
| Categoria modificata | Nessun effetto automatico sull'aliquota; analisi statica |
| Componenti con permanenza diversa | Date individuali non rappresentate nel modello: verifica non realizzabile nel circuito attuale |

Periodo senza date applica tutte le notti. `giorni_massimo=0` elimina l'imposta; valori manuali fino a 365 e soglie fino a 120 ammessi. L'audit non determina i valori oggi presenti nelle strutture reali.

## Export, byte, STAYTOUR e confronto empirico

Il CSV comunale contiene una prima riga `inizio_mese;fine_mese;`, poi **9 campi**: tipo, data_reg, arrivo, partenza, nominativo, soggetti, pernottamenti_imponibili, tariffa, vuoto. Date `d/m/Y`, delimitatore `;`, LF senza newline finale, stringhe PHP senza conversione encoding/BOM, nessun quoting; virgole sostituiscono eventuali `;` nei nomi. Numeri float in rappresentazione PHP, senza formato decimale fisso; MIME `text/csv` senza charset; nome `mese_anno.csv`, nessun identificatore struttura.

Il CSV controllo è un formato diverso di **23 colonne** con intestazione, anche tassa e totale Schedina. Le stampe sono HTML/browser. Nessun writer TXT/XML/Excel IDS trovato. L'anteprima configurazione mostra invece header, 8 colonne e date ISO: non è il download effettivo.

**Difetto certo 777:** `buildRows():440–447` salva l'eccedenza in `pernottamenti_oltre_max` e pone `pernottamenti_imponibili=0`; `exportCsv():117` legge sempre quest'ultimo. Soggiorno di 9 notti produce riga 777 con **0**, perdendo le **3** notti. Il test unitario di `exportRows()` non rileva il difetto perché esercita un altro generatore.

**Difetto certo quoting:** nome sintetico con LF spezza la riga CSV; riprodotto via download. Compatibilità della rappresentazione UTF-8/decimali/LF con STAYTOUR resta NON DIMOSTRATA senza specifica/file accettato. Nessuna deduzione «9 colonne quindi corretto».

**Stato del primo audit, superato dalla successiva consegna di `mese_6_2026_1.csv`:** ricerca di TXT/CSV nel repository, riferimenti e allegati della conversazione: nessun file IDS accettato identificato; i CSV di riferimento trovati sono Questura/GEO e i TXT ISTAT non sono IDS. Ricerca strutturale negli allegati senza stampare nominativi: nessuna riga compatibile trovata. È stato chiesto il percorso del file all'utente. **Confronto stesso scenario, hash/byte/encoding/quoting/naming e accettazione: BLOCCATO finché il file non viene fornito.** Non sostituito con un file costruito ad hoc.

STAYTOUR: [portale Bellaria](https://imposta-soggiorno.org/bellaria-igea-marina/index.php) identificato; pagina operatore non recuperabile dal browser web di ricerca. [Determina 567/22.05.2025, id154089](https://citygov.comune.bellaria-igea-marina.rn.it/web/trasparenza/papca-p?_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_action=mostraDettaglio&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_downloadSigned=false&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_fromAction=recuperaDettaglio&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_id=154089&p_p_cacheability=cacheLevelPage&p_p_id=jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet&p_p_lifecycle=2&p_p_mode=view&p_p_resource_id=downloadAllegato&p_p_state=pop_up) documenta acquisto del software e attivazione CIE. Login operatore e delega non equivalgono a credenziali tecniche PMS. Nessuna autenticazione tentata.

Specifiche Bellaria di import TXT/CSV/Excel/XML, encoding, naming, controlli/errori/ricevute, API o Web Service PMS: **NON DIMOSTRATE** dalle fonti disponibili. SPID e interoperabilità non inferiti dalla disponibilità CIE. Nessun endpoint inventato o contattato per trasmettere dati.

## Storico, rettifica e adempimenti

Report e ricevuta sono dinamici. Ospite/esenzione/partenza/componenti/tariffa modificati cambiano il risultato successivo. `data_reg=now()` cambia anche senza modifica del soggiorno. Nessun export-ID, hash persistito, snapshot immutabile, versione regola, stato consegnato/accettato, ricevuta comunale o rettifica trovato. Il log generico delle operazioni non conserva la comunicazione originaria; cancellazione/restauro esenzioni può cambiare il ricalcolo. Non ci sono tre archivi ospiti separati, ma neppure uno storico fiscale autonomo.

[Allegato regolamentare comunale id145887, art.7](https://citygov.comune.bellaria-igea-marina.rn.it/web/trasparenza/papca-p?_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_action=mostraDettaglio&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_downloadSigned=false&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_fromAction=recuperaDettaglio&_jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet_id=145887&p_p_cacheability=cacheLevelPage&p_p_id=jcitygovalbopubblicazioni_WAR_jcitygovalbiportlet&p_p_lifecycle=2&p_p_mode=view&p_p_resource_id=downloadAllegato&p_p_state=pop_up): comunicazione e riversamento entro 15 giorni dal mese; soggiorni a cavallo imputati al mese di conclusione/pagamento per il riversamento; conservazione almeno cinque anni; Modello21 e ministeriale entro 30 giugno successivo. Scadenza puntuale Modello21 e modalità di pagamento comunali attuali non accertate. Non inferire automaticamente una modalità PagoPA/F24.

Il rapporto attuale seleziona per **arrivo** sia controllo sia CSV: 28 giugno–7 luglio appare interamente a giugno e non a luglio. Il riepilogo «da versare» non rappresenta dunque la regola di riversamento pubblicata. La specifica di comunicazione/import potrebbe prevedere proprie convenzioni: da acquisire senza confonderle con il riversamento.

Disponibili rapporti mensili/controllo e stampa «totale da pagare»; assenti registro incassi/riversamenti, riconciliazione scadenze, Modello21, dichiarazione ministeriale, rettifica e conservazione fiscale attestata. Una stampa con importo dovuto non dimostra avvenuto pagamento.

## Sicurezza, tenant e UI

Prove nel vero kernel HTTP, DB effimero: tenant A non vede catalogo B; CSV di A non include Schedina B anche con `sid` contraffatto; modifica aliquota A lascia B invariata; struttura_user non modifica catalogo esenzioni (403); admin in contesto A non modifica ID esenzione B (404); stampa tassa con ID Schedina B (404); anonimo configurazione/CSV rediretto a login. Filtri espliciti controller e scope Eloquent coerenti negli scenari provati.

Non si dichiara coperta l'intera matrice super_admin/proprietari multipli/selezione assente, tutti i writer condivisi o ingressi Customer/WebCheckin. Configurazione tariffa è modificabile da struttura_user: comportamento osservato, non violazione di una policy ruolo non specificata. Il GET configurazione crea record/default: scrittura applicativa anche in lettura; osservata nel solo DB sintetico.

Layout generale, card/tab, filtri, paginazione e stampe presenti. Rendering HTTP configurazione, rapporto, controllo e ricevuta PASS. Criticità UI: scelte esenzione capo discordanti, anteprima non fedele al writer, assenza storico/stato di comunicazione e validazione tariffaria per categoria. Esito browser dedicato riportato nella sezione prove dopo conclusione.

## Registro P0/P1/P2 e correzioni necessarie (non eseguite)

| ID | Priorità | File/linea | Evidenza / requisito | Correzione necessaria |
|---|---|---|---|---|
| IDS-01 | P1 | `TassaDiSoggiornoController.php:17–22` | Default periodo errato rispetto all'avviso pubblicato; GET sintetico conferma | Allineare e versionare regole Bellaria dopo coordinato2026; gestire config esistenti senza sovrascrittura silenziosa |
| IDS-02 | P1 | `TassaDiSoggiornoService.php:85–90,243–245`; `EtaOperativa.php:30` | Stesso soggiorno minore €0→€9 col solo clock | Calcolare età pertinente al soggiorno con regola documentata, preservando dominio condiviso |
| IDS-03 | P1 | `schedina/partials/form.blade.php:235–240`; `TassaDiSoggiornoService.php:214–226` | Esenzione scelta nel capo/residente non riconosciuta; importo errato riprodotto | Mapping controllato delle opzioni legacy ai motivi validi, requisito residenza e catalogo comunale coerente |
| IDS-04 | P1 | `TassaReportController.php:265–271,409–414` | Soggiorno concluso a luglio soltanto nel riepilogo giugno | Separare e correggere criterio riversamento/comunicazione sulla base delle specifiche ufficiali |
| IDS-05 | P1 | `TassaReportController.php:117,440–447` | 3 notti oltre tetto diventano 0 nel CSV | Writer deve conservare l'eccedenza; prova end-to-end sul download, non soltanto exportRows |
| IDS-06 | P1 | `TassaReportController.php:400–454`; `TassaDiSoggiornoService.php:109–115`; `SchedinaController.php:502–510` | Comunicazione/stampa passata riscritta dalla tariffa corrente; nessuna rettifica | Snapshot fiscale e ciclo esplicito di consegna/rettifica/versione; senza duplicare archivio persone |
| IDS-07 | P1 statico | `TassaDiSoggiornoController.php:17–22,129–145`; `TassaDiSoggiornoService.php:76–77` | Default unico anche per categorie diverse; limite campeggi/camper non derivato | Configurazione Bellaria per categoria verificata; tetto camper cumulativo mensile, parametri obbligatori validati |
| IDS-08 | P2 | `TassaReportController.php:110–123,174–196` | LF nel nominativo rompe CSV; quoting assente | Writer con escaping coerente col tracciato, eventuale mitigazione formule spreadsheet; import reale ancora da provare |
| IDS-09 | P2 | `tassa_di_soggiorno/edit.blade.php:252–258` | Anteprima 8 campi/ISO/header vs download 9/dd-mm-periodo | Anteprima derivata dallo stesso formato effettivo |
| IDS-10 | P2 | `TassaEsenzione.php:19`; `TassaDiSoggiornoService.php:146–197`; `TassaDiSoggiornoController.php:27–35` | richiede_nota non applicato; requisiti e prove non attestati | Definire attestazione/documentazione e limiti accompagnatori; considerare conservazione esterna senza falsa attestazione |
| IDS-11 | P2 | `TassaDiSoggiornoController.php:47–57,85–92,129–175` | GET scrive; configurazione permissiva; nessun vincolo unico struttura in schema originario | Valutare inizializzazione esplicita, unicità, validazione e avviso quando configurazione incompleta |
| IDS-12 | P2 documentale | `HelpCenterController.php:618–631,968` | Promette coerenza comunale/CSV ufficiale senza certificazione | Limitare le dichiarazioni al livello dimostrato dopo correzione |

IDS-01/04/07 richiedono riconferma normativa 2026; IDS-02/03/05/06 rimangono difetti osservati indipendentemente dall'esito della ricerca. Nessuna correzione proposta viene applicata in questa fase.

## Prove eseguite e limiti riproducibili

Sorgenti copiati fuori dal repository in `/private/tmp/ids-audit-source`, senza `.env*`/storage/cache operativi, con metadata Git di sola lettura; harness corrente preservato. Diagnostici temporanei aggiunti soltanto nella copia esterna. Ambiente: PHP CLI **8.4.3**, PHPUnit **10.5.38**, nuova istanza **MySQL8.0.36**, HTTP/DB loopback effimeri, proxy isolato, Questura/ISTAT OFF. Il launcher attesta processi/DB/HTTP e **23 rifiuti** prima delle migration della copia di test; build/GEO immutabile PASS. Nessuna migration o interrogazione su DB operativi.

Comando, dalla copia esterna:

```sh
PATH='/Users/Shared/Herd/services/mysql/8.0.36/bin':"$PATH" python3 -B tests/Isolation/run.py \
  --phpunit tests/Unit/TassaDiSoggiornoServiceTest.php \
  tests/Unit/EtaOperativaTest.php tests/Feature/ImpostaAuditDiagnosticoTest.php
```

- Esecuzione iniziale: vendor rifiutato **prima del DB**. Il wrapper, invocato da Python nella copia esterna, sceglieva Homebrew MySQL9.5.0, output senza marcatore vendor positivo. Usato poi binario8.0.36 esplicito, senza indebolire policy o riusare servizio Herd.
- Prima selezione diagnostica: **21 test / 69 asserzioni, zero failure/error, una deprecazione**.
- Estensione: **24 test / 83 asserzioni, una failure e una deprecazione**. L'aspettativa temporanea ipotizzava 9 notti per date invertite; runtime restituisce correttamente 0. Corretta soltanto questa aspettativa nella copia diagnostica, nessun codice applicativo cambiato.
- Esecuzione conclusiva: **24 test / 84 asserzioni, zero failure/error, una deprecazione**. Non sommare le esecuzioni sovrapposte. La deprecazione resta segnalata e non diagnosticata qui.

I 12 diagnostici temporanei coprono: età retroattiva, residente legacy, somma/tetto, day-use/stagione/anno, esenzione senza documento, default/rendering, CSV777/mese, tenant/config/esenzione-ID, anonimo, mutazioni famiglia/tariffa, gruppo/date/config assente, newline/storico/stampa/ID straniero. Sono asserzioni del comportamento attuale: i difetti riprodotti passano perché l'audit li misura, **non perché siano risolti**. Esenzioni automatiche e altre età esercitate anche dai due file unitari esistenti.

Diagnostico conservato fuori Git: `/private/tmp/ids-audit-source/tests/Feature/ImpostaAuditDiagnosticoTest.php`, SHA256 `0806c37e31a268f5e6396e7c36ab012ce910c233fe75a8ce5aebf2675bc990ef`. Output conclusivo `/private/tmp/ids-audit-test-output-conclusivo.txt`. Questi percorsi temporanei non sono evidenza versionata permanente e possono essere rimossi dal sistema: il rapporto registra risultati e scenari. Nessun test nuovo aggiunto al repository.

### Browser

```sh
PATH='/Users/Shared/Herd/services/mysql/8.0.36/bin':"$PATH" python3 -B tests/Isolation/run.py \
  --fixtures tests/Isolation/seed-ids-audit.php \
  --playwright tests/Feature/tassa-di-soggiorno.playwright.spec.js
```

Fixture esplicita esterna con struttura/utente completamente sintetici per lo spec esistente, senza riuso credenziali reali. **1 test FAIL**, login riceve **HTTP419 Page Expired** e rimane su `/login`; il flusso IDS/tab/validazione browser non viene raggiunto. Causa del419 non dimostrata in questo audit; non classificato come difetto fiscale, non aggirato CSRF né modificato harness/applicazione. Output `/private/tmp/ids-audit-browser.txt`; risorse isolate fermate/rimosse. Esito UI browser: **BLOCCATO**, rendering kernel HTTP PASS non sostituisce acceptance visiva/interattiva. Nessuna suite globale, sei prove Rocky o trasmissione reale eseguita.

## Integrità Git e aree protette

Hash SHA256 dei file all'avvio confrontati a fine audit; codice applicativo confrontato anche con HEAD. **1.831 file applicativi byte-identici**, `APP_BYTES_CHANGED=NO`. Tutti i **147 file migration attuali** invariati (146 storici più migration ISTAT del candidato); nessuna migration scritta. **50 file del manifest Questura** invariati; **21 file test con riferimenti Questura/ISTAT/Ross1000** invariati. Writer, configurazioni e test certificati protetti.

Anche `tests/Isolation/run.py`, `tests/Support/TestingEnvironment.php`, i due untracked policy/test MariaDB e `docs/DEPLOY_SPANEL.md` sono byte-identici allo stato iniziale della task: le loro modifiche preesistenti non appartengono a questo audit. Staging vuoto, branch/HEAD invariati. Modifiche della task limitate a questo rapporto e appendice del Maestro.

Nessun segreto/dato reale acquisito nelle fixture, nessun valore di credenziali pubblicato, nessun DB operativo modificato, nessun accesso SPanel/produzione, nessun Test/Send Questura o Ross1000/STAYTOUR, nessun commit/push/deploy.

## Gate rimasti aperti

1. Delibera IDS28/07/2026 e testo coordinato con decorrenze; numero/data conferma2026 e allegatoC integrale.
2. File altro software realmente accettato e specifica import Bellaria: confronto byte/struttura non eseguibile senza evidenza.
3. Configurazioni/tariffe effettive delle strutture reali: non interrogate; niente certificazione dei dati locali/produzione.
4. UI browser bloccata al login419; matrice autorizzazioni completa e differenze permanenza componenti da completare.
5. Correzione autorizzata separata dei P1/P2, poi regressioni e accettazione esterna controllata. Nessuna autorizzazione implicita.

**STOP alla fase di audit. Nessuna implementazione conforme, production readiness o accettazione comunale dichiarata.**


## Chiusura documentale pre-correzione — 7 ottobre 2026

**DOCUMENTAZIONE BELLARIA ANCORA INCOMPLETA.** Nessuna correzione autorizzata o eseguita. Questa sezione aggiorna e, dove indicato, supera le pendenze documentali del primo audit; non sostituisce i risultati diagnostici applicativi.

### Atti ufficiali recuperati integralmente

L'indirizzo Citygov rimanda al portale pubblico attuale. Ricerca pubblica per oggetto e lettura degli allegati, senza autenticazione o trasmissione di dati:

- [CC29 del 28/07/2026, registrazione 2026/1989](https://bellariaigeamarina.trasparenza-valutazione-merito.it/web/trasparenza/delibere-di-consiglio/-/papca/display/46217): documento principale, pareri, attestazione di pubblicazione, Allegato A coordinato e Allegato B comparativo recuperati. Votazione 14 favorevoli, zero contrari, un astenuto; medesimo esito per l'immediata eseguibilità. Pubblicazione comunale dal 12/08/2026; scheda riporta esecutività 07/09/2026, mentre il dispositivo dichiara immediata eseguibilità ex art.134 comma4. Sono evidenze distinte: non assumere che una delle due date sia decorrenza fiscale.
- CC29 modifica esclusivamente artt.7 e15: soppressione della qualifica di agente contabile e del conto di gestione Modello21, in relazione alla Cassazione SS.UU.1527/2026 richiamata dall'atto. Restano la dichiarazione ministeriale e gli adempimenti mensili. L'Allegato A è testo coordinato approvato come parte integrante; il frontespizio ha numero/data lasciati in bianco, l'identificazione deriva dalla delibera e dall'associazione nell'albo. Non inventare una diversa approvazione.
- Dispositivo punto5 e art.16 coordinato: effetto dal primo giorno del secondo mese successivo alla pubblicazione ministeriale. **Data della pubblicazione ministeriale e quindi decorrenza puntuale: NON DIMOSTRATE.** Non retrodatare le modifiche al 28 luglio o al 7 settembre. Gli artt.2–6 rilevanti per calcolo/esenzioni non sono modificati rispetto al testo2025.
- [GC184 del 06/11/2025, registrazione 2025/2634](https://bellariaigeamarina.trasparenza-valutazione-merito.it/web/trasparenza/delibere-di-giunta/-/papca/display/39742): conferma espressa per l'anno2026 delle tariffe IDS, Allegato C integrale letto. Richiama GC185/25.11.2024, costituisce atto propedeutico al bilancio2026–2028. La tabella iniziale è confermata integralmente nei valori; non è più soltanto un estratto indicizzato. Atto e anno di applicazione dimostrati; giorno di efficacia fiscale tramite pubblicazione ministeriale non documentato in questa fase.

Integrità delle copie PDF ufficiali temporanee fuori Git: CC29 principale SHA256 `ab61a4b2c5d4a501569d4d5329d0500ed4415aa6f2b7ed20eb72874809412a58`; coordinatoA `c4b0651b42f158ce25d499f8d2a5e1559b7f48d3618b79af9e02cae56428cffb`; comparativoB `bd5732daa41663d017ae834a297b53633f4cd1ee8c0da5487742a5463a5be2d0`. Fonte permanente sono i collegamenti all'albo, non i percorsi temporanei.

### Tariffe, periodo e limiti

Fonte di tutte le righe: GC184/2025, Allegato C, conferma anno2026; periodo applicabile art.2 comma4, 1 giugno–30 settembre. Importi per persona/pernottamento. Decorrenza fiscale puntuale non dimostrata, senza estrapolare la data dell'albo.

| Tipologia | Categoria | Tariffa2026 (€) | Limite regolamentare |
|---|---|---:|---|
| Alberghi | 1 stella | 1,00 | 6 consecutivi |
| Alberghi | 2 stelle | 1,00 | 6 consecutivi |
| Alberghi | 3 stelle | 1,50 | 6 consecutivi |
| Alberghi | 4 stelle | 2,50 | 6 consecutivi |
| Alberghi | 5 stelle | 2,50 | 6 consecutivi |
| Campeggi | 1–2–3 stelle | 0,50 | 10 consecutivi |
| Campeggi | 4 stelle | 1,00 | 10 consecutivi |
| Campeggi | 5 stelle | 1,50 | 10 consecutivi |
| Aree sosta camper | unica | 0,20 | 10 nel mese |
| Country House | unica | 1,00 | 6 consecutivi |
| Ostelli | unica | 1,00 | 6 consecutivi |
| Affittacamere | unica | 1,00 | 6 consecutivi |
| Case Vacanze | unica | 1,00 | 6 consecutivi |
| Bed & Breakfast | unica | 1,00 | 6 consecutivi |
| Case per Ferie | unica | 1,00 | 6 consecutivi |
| Residenze Appartamenti, appartamenti ammobiliati per uso turistico | unica | 1,00 | 6 consecutivi |
| Agriturismi | unica | 1,00 | 6 consecutivi |
| Locazioni brevi art.4 DL50/2017 | unica | 1,00 | 6 consecutivi |
| Altre tipologie | residua | 1,00 | 6 consecutivi |

RTA e villaggi turistici sono nominati nel presupposto, ma non esplicitamente associati a una voce del tariffario: mapping di queste classificazioni **NON DIMOSTRATO**, da ottenere dal Comune senza equiparazione automatica. Il day-use è escluso (art.4 comma1). Nessuna riduzione generica per convenzioni/gruppi dedotta. Il limite camper è mensile, non per singola Schedina; una configurazione unica6 è insufficiente.

### Età ed esenzioni: condizioni e mapping controllato

Art.5 comma1 lett.a: esenti i minori di18 anni. Nessuna disposizione recuperata sceglie esplicitamente arrivo, singola notte o partenza per il compleanno durante il soggiorno. **Momento giuridico puntuale e compleanno intermedio: NON DIMOSTRATI.** È dimostrato il difetto del clock odierno: un soggiorno storico non cambia perché oggi l'ospite è maggiorenne. La scelta di riferimento dovrà essere chiarita prima di implementare il caso limite.

Le seguenti associazioni sono mapping del codice locale, non un dizionario di codici STAYTOUR. Decorrenza: condizioni identiche nel testo2025 e coordinato2026; efficacia puntuale delle modifiche2026 ancora da documentare.

| Norma | Condizioni e prova | Software Bellaria | Generico/legacy da preservare e risolvere |
|---|---|---|---|
| Art.3 comma1, residente nel Comune | Non è soggetto passivo; verificare residenza comunale, non soggiorno abituale in hotel | Nessuna voce dedicata nel seeder Bellaria | E06 «Residenti nel comune» e «Residente nel comune»; non equiparare «Residente in hotel» |
| Art.5.1.a | Minore18; art.5.2 non elenca modulo specifico per a; conservare evidenza pertinente, senza inventare obbligo di autocertificazione | 400 | E01 «Minori fino a14 anni» non descrive la soglia Bellaria; ETA_BIMBI/ETA_MIN richiedono normalizzazione e data pertinente |
| Art.5.1.b | Assistenza degente in struttura sanitaria comunale: un accompagnatore; entrambi genitori se malato minore; certificazione sanitaria o dichiarazione DPR445 con periodo, modulo comunale | 405, descrizione locale più ampia della norma | «Accompagnatori per pazienti»: riconoscere motivo preservando condizioni; non estendere a qualsiasi terapia |
| Art.5.1.c | Invalidità civile100% e accompagnatore; entrambi genitori per minori; dichiarazione sostitutiva di notorietà | 410 | E02 disabili/accompagnatore: non basta generica disabilità senza100% |
| Art.5.1.d | Volontariato per eventi straordinari/emergenza; dichiarazione sostitutiva del soggetto passivo | 415 | Descrizione «eventi organizzati» troppo ampia; non normalizzare senza condizioni |
| Art.5.1.e | Soggiorno per eventi/calamità naturali; dichiarazione sostitutiva del soggetto passivo | 420 | Conservare eventuali valori già presenti senza promuoverli automaticamente |
| Art.5.1.f | Autisti pullman per servizio; accompagnatori di gruppi organizzati da agenzie, uno ogni20 soggetti; dichiarazione del responsabile art.3.2 | 425 | E04/E05, «Autista», «Acompagnatore Turistico»; mantenere limiti e prova |
| Art.5.1.g | Dipendente della struttura; dichiarazione del responsabile art.3.2 | 430 | «Personale» da ricondurre soltanto al dipendente della struttura |
| Art.5.1.h | Corpi armati statali, polizie provinciali/locali, vigili del fuoco/protezione civile in servizio; esclusi servizi pagati da privati; dichiarazione soggetto passivo | 440 | E03 e «Forze armate in seervizio»; catalogo locale non elenca tutta la norma |
| Art.5.1.i | Nucleo familiare gestore anagraficamente convivente; art.5.2 non indica modulo specifico per i; art.7 richiede documentazione dell'esenzione | 450 | Nessuna opzione capo specifica; non esenzione per semplice parentela |

Art.7 esclude l'esenzione senza documentazione attestante. `richiede_nota` non prova l'acquisizione del documento; una nota non sostituisce automaticamente certificazione/autocertificazione. `NO` significa assenza di motivo; `777` non è da trattare come esenzione personale. Preservare stringhe/codici originali e motivazione, distinguere motivo fiscale normalizzato e condizioni verificate. Non modificare dati né eseguire seeders. La lista software iniziale era più permissiva in alcune descrizioni: la presente matrice delimita i requisiti normativi, non autorizza nuovi benefici.

### Comunicazione, riversamento e annuale

Art.7: comunicazione **mensile**, entro15 giorni dalla chiusura del mese, per singola struttura; riversamento alla medesima scadenza. Per soggiorni tra mesi il riversamento segue il mese di conclusione del soggiorno; art.6.2 impone competenza anche se il pagamento arriva dopo. Non rinviare indefinitamente per mancato incasso. Nessun trimestre dedotto dalla configurabilità generica del portale. La dichiarazione ministeriale riguarda l'anno del presupposto, entro30 giugno dell'anno successivo (per2026:30/06/2027). Coordinato CC29 rimuove il Modello21; la data di efficacia della modifica resta da documentare prima di imporre o sopprimere un adempimento operativo. Nel precedente testo art.15 prevedeva30 gennaio: non presentarlo come scadenza corrente certa.

**Distinzione indispensabile:** il CSV empirico di giugno comprende218 righe con partenza a luglio e tutte le553 righe hanno arrivo a giugno. Questo dimostra la convenzione del campione, non una deroga al riversamento. IDS-04 riguarda il riepilogo «da versare»: non autorizza a cambiare automaticamente il criterio del CSV all'uscita. Specifica comunale del flusso e raccordo con conteggi finanziari ancora da acquisire.

### CSV fornito: struttura e integrità, senza dati personali

File originale esterno `/Users/jorgeluccitelli/Desktop/mese_6_2026_1.csv`, sola lettura, 34.213 byte, SHA256 `899d06781cca3161a60cd47147d3ba3c28cb91a2ffd3b9b9cb63b09d08968fe6`. Nessuna copia nel repository/fixture. Accettazione riferita dal contesto utente; non verificata con ricevuta del Comune né nuovo caricamento.

- Tutti i byte sono ASCII: compatibile con UTF-8 e varie codifiche a singolo byte; **encoding originario non identificabile**, nessuna prova su accenti. Nessun BOM.
- Separatore `;`, 554 righe CRLF, newline finale presente; nessun quoting osservato. Prima riga periodo `01/06/2026;30/06/2026;`, tre campi, nessuna intestazione nominale delle colonne.
- 553 righe dati, esattamente8 campi: codice, data di registrazione apparente, arrivo, partenza, nominativo, quantità soggetti, quantità notti, tariffa. Nomi dei campi ricostruiti per confronto, non specifica ufficiale. Date dd/mm/yyyy, data2=data3 in tutte le righe; quantità soggetti sempre1. Nessun campo nullo; zero letterale0; decimale osservato1.5. Nessuna categoria struttura o riga totale nel file. Suffisso del nome `_1` non interpretato.

| Codice | Righe | Somma notti | Intervallo notti/riga | Tariffa osservata |
|---|---:|---:|---|---:|
| 0 | 296 | 1.525 | 1–6 | 1.5 |
| 400 | 46 | 164 | 1–6 | 0 |
| 410 | 2 | 12 | 6 | 0 |
| 777 | 209 | 1.146 | 1–16 | 0 |
| Totale | 553 | 2.847 | — | — |

Quantità553 non equivale a553 persone distinte: lo stesso soggiorno appare su più codici. Importo algebrico ricostruibile1.525×1,50=€2.287,50; non è prova di imposta dichiarata, versata o ricevuta. Senza date di nascita/documenti non si prova il significato fiscale di400/410 dal solo campione.

`777` è empiricamente un **codice nel primo campo**, non la quantità (che è il settimo). Raggruppamento interno per arrivo/partenza/nominativo senza pubblicare identificatori:207 gruppi con777;188 con0,15 con400,2 con410,2 con collisioni/duplicati della stessa chiave. In205 gruppi la somma delle notti delle righe coincide con la durata del soggiorno. Evidenza forte di ripartizione delle notti oltre limite, coerente col codice applicativo; **dizionario ufficiale e semantica generale777 NON DIMOSTRATI**. Il campione non basta a certificare tutti i casi (stagione/categorie/soggiorni spezzati/esenti). Writer bloccato fino a chiarimento; perdita applicativa di3→0 resta riprodotta.

| Aspetto | Campione / writer attuale | Classificazione |
|---|---|---|
| Periodo e delimitatore | Prima riga3 campi, date slash, `;` in entrambi | Equivalente strutturalmente |
| Campi dati | 8 campi nel campione;9 nel writer, ultimo vuoto | Da verificare con specifica/importer; differenza certa, rifiuto non dimostrato |
| Terminatori | CRLF e finale nel campione; LF e nessun finale nel writer | Da verificare; byte non equivalenti |
| Codifica | Campione soloASCII; writer stringhePHP | Da verificare per accenti/charset |
| Registrazione | Nel campione uguale arrivo; writer now() | Da verificare semanticamente; valori incompatibili nello stesso scenario eseguito in altra data |
| Quantità777 | Positive nel campione; writer0 invece dell'eccedenza | Incompatibile con conservazione delle quantità osservate; significato ufficiale pendente |
| Decimali e zeri | 1.5/0 rappresentabili come writer | Equivalente per valori osservati; scala/arrotondamento generale da verificare |
| Quoting | Nessuna virgoletta nel campione; writer senza quoting | Da verificare: campione non esercita delimitatori/newline nei nomi |
| Nome file | mese numerico/suffisso vs nome italiano mese/anno | Da verificare; vincoli naming non dimostrati |
| Mese selezione | Campione per arrivi giugno; writer per arrivo | Equivalente nel criterio osservato; non prova corretto riversamento |
| Totali/categorie | Assenti nel campione; assenti nel CSV writer | Equivalente strutturalmente; requisiti complessivi non dimostrati |

Confronto statico del writer e analisi del campione, non produzione di un nuovo export con dati reali. Nessun import eseguito. Nessuna API/credenziale acquisita.

### Specifica STAYTOUR: cosa resta mancante

[Fornitore Hyksos, STAYTOUR](https://www.hyksositalia.it/staytour.php) e [manuale ufficiale strutture](https://imposta-soggiorno.org/manuale-struttura.pdf) descrivono gestione dichiarazioni, imponibili/esenti e ricevute; parametrizzazione secondo il Comune. Il manuale generico non contiene il dizionario del tracciatoBellaria,777,charset o vincoli del nome file. La data nell'elenco ricevute del manuale riguarda la ricevuta/pagamento: non dimostra il significato del secondo campoCSV. Disponibilità di funzioni generiche/webservice non prova API abilitata per Bellaria. Non autenticarsi e non tentare caricamenti per colmare questa lacuna.

Per chiudere: specifica ufficiale Comune/Hyksos del tracciatoBellaria con colonne,777/codici, date/periodo, encoding/newline, condizioni di import/controlli e classificazioni strutture; ricevuta o evidenza minimizzata di accettazione del campione se si vuole dichiararne accettazione verificata. Il campione già disponibile è sufficiente per il confronto empirico, non va richiesto nuovamente.

### Requisito storico e rettifiche (da implementare in fase distinta)

Una comunicazione/export **registrata** conserva snapshot immutabile dei dati effettivamente utilizzati: struttura/periodo, persone e permanenze pertinenti, motivi/condizioni di esenzione, data di riferimento dell'età, classificazione/tariffa/versione regole, notti imponibili/esenti/oltrelimite, importi, tracciato e file/hash prodotto, autore/data e stato documentato. Minimizzare e proteggere i dati; non duplicare indiscriminatamente l'anagrafica. Il semplice download non dimostra invio/accettazione: registrare separatamente produzione, comunicazione dichiarata e ricevuta verificata.

Modifiche successive a Schedina/componenti/configurazione non riscrivono il registrato; nuova elaborazione è distinta. Rettifica esplicita con riferimento alla versione precedente, motivo, delta e nuovo stato, senza cancellare evidenza precedente e senza simulare accettazione. Art.7 richiede conservazione almeno5anni: il requisito applicativo di snapshot deriva anche dall'obiettivo utente, non è una pretesa che il regolamento prescriva un particolare schemaDB. Nessuna implementazione effettuata.

### Matrice finale dei sette P1

| P1 | Comportamento attuale | Regola corretta | Fonte/evidenza | Correzione richiesta / determinazione |
|---|---|---|---|---|
| IDS-01 | Default marzo–ottobre | 1 giugno–30 settembre, notti effettive pertinenti | Art.2.4 invariato2025/coordinatoCC29; diagnostico precedente | Allineamento versionato, senza sovrascrivere config esistenti silenziosamente; regola determinata |
| IDS-02 | Età today riscrive storico | Età riferita al soggiorno, esenzione minori18 | Art.5.1.a e prova clock; compleanno intermedio non disciplinato esplicitamente | Eliminare clock corrente; riferimento preciso/compleanno **NON DIMOSTRATO**, non scegliere arbitrariamente |
| IDS-03 | Stringhe capo/residente non risolte; catalogo descrittivo permissivo | Mapping conservativo con residenza/requisiti/prove e limiti accompagnatori | Artt.3/5/7 invariati; tabella sopra; riproduzione precedente | Preservare legacy; nessuna conversione indiscriminata E01/E02 o residentehotel; codici esterni **NON DIMOSTRATI** |
| IDS-04 | Riepilogo da versare per arrivo | Riversamento mese fine soggiorno, competenza; comunicazione mensile15gg | Artt.6.2/7; campione arrivi giugno con checkout luglio | Separare aggregazione finanziaria e flusso; regola CSV generale **NON DIMOSTRATA**, non spostare automaticamente file alla partenza |
| IDS-05 | Notti oltre tetto777 diventano0 nel download | Quantità positive conservate per codice quando previsto dal tracciato | Prova9notti→0; campione209righe777/1.146notti | Correzione writer bloccata: significato generale777 e specifica **NON DIMOSTRATI** |
| IDS-06 | Report storico ricalcolato con dati correnti | Snapshot registrato immutabile; rettifica separata | Prova mutazioni, obiettivo utente e conservazioneart.7 | Requisito applicativo determinato; progettare in fase autorizzata senza dichiarare invii inesistenti |
| IDS-07 | Aliquota/tetto unico; camper non cumulativo | TariffeAllegatoC per categoria;6consecutivi/10campeggi/10mensilecamper | GC184/2025 AllegatoC, art.2.4; analisi statica | Regole numeriche determinate; mappingRTA/villaggi e decorrenze puntuali **NON DIMOSTRATI** |

### Gate conclusivo

Mancano precisamente: (1) data pubblicazione ministeriale/efficacia fiscale della modificaCC29 e della conferma tariffaria; (2) criterio di età per compleanno durante soggiorno; (3) specificaBellaria/Hyksos, significato ufficiale777 e raccordo flusso/riversamento; (4) assegnazione tariffariaRTA/villaggi. Pertanto non tutti i sette P1 hanno ancora regola operativa sufficientemente determinata: **DOCUMENTAZIONE BELLARIA ANCORA INCOMPLETA**. Nessuna production readiness, accettazione comunale o deploy dichiarata. I limiti browser/configurazioni reali dell'audit precedente restano, ma non sono nuove attività di questa chiusura.

Solo documentazione modificata; nessun testLaravel rieseguito, DB/server/reteQuestura/STAYTOUR contattati per operazioni applicative, nessuna correzione/commit/push/deploy.

## Chiusura definitiva fonti pubbliche — 7 ottobre 2026

**BELLARIA/STAYTOUR — SERVE CONFERMA UFFICIALE MIRATA.** Ricerca documentale ampliata e analisi integrale del campione; nessuna correzione. Questo è il gate documentale corrente, successivo alla precedente chiusura; le evidenze storiche non diventano automaticamente regole di import.

### Ricerca pubblica e limiti

Consultati nuovamente atti/allegati nell'albo storico, pagina Ufficio IDS, sportello telematico, FAQ comunali, pagina pubblica operatori STAYTOUR e manuale generale del fornitore. Ricerca web con varianti esatte `STAYTOUR 777`, `Stay Tour codice 777`, `imposta soggiorno 777`, `tracciato StayTour 777`, `CSV StayTour 777`, `import PMS StayTour`, manuali importazione/tracciato record/PDF, compleanno/compimento18, RTA/villaggi e riversamento/causale. Nessun dizionario tecnico pertinente recuperato. Risultati con numero777 di atti o statistiche non sono fonti del codice. Assenza di risultati non prova assenza di documentazione non pubblica.

[Sportello comunale IDS](https://sportellotelematico.comune.bellaria-igea-marina.rn.it/action:c_a747:imposta.soggiorno) recuperato tramite lettura pubblica diretta dopo errore del motore web; pubblica condizioni, tariffario, scadenze e collegamenti modulistica, senza tracciatoPMS. [Accesso operatori Bellaria](https://imposta-soggiorno.org/bellaria-igea-marina/index_op.php) espone login SPID/CIE/credenziali e recupero password: nessun tracciato/777 nella pagina pubblica. Nessuna autenticazione, recupero password o invio effettuati. Manuale generale già letto non specifica queste lacune.

### 777: analisi completa, non inferenza da pochi esempi

Originale e hash restano quelli registrati nella sezione precedente; parsing in memoria, nessuna pubblicazione di nomi o chiavi personali. Tutte553 righe dati inventariate;209 occorrenze777.

**209/209 righe777 hanno quantità notti esattamente uguale a durata(partenza−arrivo)−6 e tariffa0.** Distribuzione completa delle quantità:

| Notti777 | Occorrenze |
|---:|---:|
| 1 | 52 |
| 2 | 11 |
| 3 | 14 |
| 4 | 7 |
| 5 | 9 |
| 8 | 111 |
| 9 | 1 |
| 14 | 2 |
| 16 | 2 |
| Totale | 209 |

Raggruppamento per arrivo/partenza/nominativo, chiave senza identificatore univoco:207 gruppi. In205 gruppi non ambigui esistono esattamente una riga base6notti e una777, con somma uguale alla durata. Di questi:188 righe base0/tariffa1.5,15 base400/tariffa0,2 base410/tariffa0. Pertanto **non sempre «notti tassate +777»**:17 casi hanno anche la prima parte esente; relazione corretta osservata è **notti prima parte + notti777 = durata**.

Due gruppi ambigui contengono ciascuno due righe0 da6notti e due777 da1notte, durata7giorni. Somma per chiave14≠7: non ignorare o deduplicare; manca un ID per distinguere omonimia, duplicazione o rappresentazione di due soggiorni. Ogni riga777 soddisfa comunque durata−6; il multinsieme consente due coppie6+1, ma l'identità dei soggiorni e la causa della collisione **NON DIMOSTRATE**. Quindi non dichiarare207/207 gruppi validati né209 persone distinte.

Classificazione richiesta: **SEMANTICA EMPIRICAMENTE DIMOSTRATA MA CODICE UFFICIALE NON DOCUMENTATO**, limitata al campione e alla separazione dopo6notti. È dimostrato cosa contengono queste righe, non che777 abbia universalmente la stessa definizione per campeggi/camper, stagioni o soggiorni spezzati. Il campione non ha classificazione struttura: non identificare automaticamente un hotel3stelle dalla tariffa1.5, presente anche per campeggi5stelle. Il limite empirico6 è coerente con la regola generaleart.2.4; non prova l'applicabilità dei limiti10 ad altri tracciati. Nessuna correzione writer prima della conferma.

### Inventario codici e confine tra mapping e norma

| Codice | Frequenza | Pattern empirico | Mapping software | Fonte ufficiale |
|---|---:|---|---|---|
| 0 | 296 | 1.525notti,1..6/riga, tariffa1.5;188 gruppi univoci con777 più2ambigui | Riga ordinaria/imponibile nel controller | Importo1.5 possibile nel tariffario; definizione tecnica0 NON DIMOSTRATA |
| 400 | 46 | 164notti,1..6/riga,tariffa0;15 gruppi con777 | Minori fino18, seeder Bellaria | Esenzione minori art.5.1.a DIMOSTRATA; codice esterno400 non documentato, età degli ospiti non verificabile nel CSV |
| 410 | 2 | 12notti,6/riga,tariffa0;entrambe con777 | Invalidi/accompagnatore, seeder Bellaria | Invalidità100% e accompagnatore art.5.1.c DIMOSTRATI; codice esterno410 non documentato, condizione individuale non verificabile nel CSV |
| 777 | 209 | 1.146notti,1..16/riga,tariffa0;durata−6 in209/209 | Riga oltre massimo nel controller, non motivo personale di esenzione | Limite fiscale art.2.4 DIMOSTRATO; codifica ufficiale NON DIMOSTRATA, relazione empirica DIMOSTRATA |

Nessun altro codice presente nel campione. Non ricavare mapping405..450 dalla sola assenza nel file. La frequenza conta righe, non beneficiari.

### Decorrenze: tre nozioni separate

| Atto | Approvazione / eseguibilità | Esecutività nella scheda albo | Efficacia fiscale e periodo |
|---|---|---|---|
| CC29/2026 | 28/07/2026; immediata eseguibilità dichiarata nel dispositivo | 07/09/2026; pubblicazione comunale12/08/2026 | Art.16 e dispositivo subordinano alla pubblicazione ministeriale; **DECORRENZA NON DIMOSTRATA** senza data. Non assumere01/01/2026 o inizio stagione. Modifica obblighiart.7/15, non nuove tariffe |
| GC184/2025 | 06/11/2025; votazione unanime e immediata eseguibilità nel dispositivo | 02/12/2025; pubblicazione comunale06/11/2025 | Conferma espressa anno2026; disciplina speciale efficacia fiscale richiamata. Giorno puntuale della conferma **DECORRENZA NON DIMOSTRATA**. Sportello indica tariffario vigente dal01/01/2025, poi confermato; non prova la data ministeriale dell'atto2026 |

Fonti sono i due atti/allegati già collegati, non il solo ordine del giorno. «Anno2026» è dimostrato, «01/01/2026 quale efficacia dell'atto» non lo è. La stagione imponibile resta01/06–30/09 nel coordinato. Non confondere tariffe in vigore con date in cui si verificano pernottamenti imponibili. Nessuna nuova consultazione MEF oltre al perimetro richiesto delle fonti comunali; chiedere al Comune l'attestazione di pubblicazione/efficacia.

### Età: norma e proposta conservativa separata

Regolamento, sportello e modulistica recuperata confermano minori18, ma non indicano la data per il compleanno intermedio. FAQ comunali pubbliche consultate non forniscono chiarimento IDS; FAQ di alberghi non sono autorità e non vengono adottate. **Dataarrivo/pernottamento/partenza: NON DIMOSTRATA.**

Proposta conservativa, **non regola ufficiale e non implementazione autorizzata**: usare dati storici e un riferimento esplicito/versionato; se lo status di minore è identico per tutto il soggiorno, evitare qualunque dipendenza dal clock corrente. Se il compimento18 ricade tra arrivo e partenza, segnalare caso da validare e impedire registrazione fiscale automatica fino alla conferma; non scegliere arbitrariamente tassazione completa o esenzione completa. Né ricalcolare già comunicato al cambiare della data odierna. Una proposta di calcolo per singolo pernottamento sarebbe un'interpretazione da sottoporre al Comune, non normativa acquisita.

### RTA e villaggi: classificazione dimostrata, tariffa no

[Disciplina regionale delle strutture ricettive](https://imprese.regione.emilia-romagna.it/turismo/ambiti/alberghi/disciplina-delle-strutture-ricettive-dirette-allospitalita) e [DGR916/2007 AllegatoA](https://imprese.regione.emilia-romagna.it/turismo/ambiti/alberghi/alberghi/del-916.pdf/@@download/file/del%20916.pdf) distinguono alberghi e residenze turistico-alberghiere, denominate anche residence/RTA: classificazione **2–4stelle**, non soli. Case/appartamenti vacanze hanno altra classificazione a soli. Pertanto «Residenze Appartamenti (appartamenti ammobiliati per uso turistico)» nel tariffarioBellaria non è sinonimo normativo dimostrato diRTA.

[Pagina regionale campeggi/villaggi](https://imprese.regione.emilia-romagna.it/turismo/ambiti/campeggi-e-villaggi-turistici) indica villaggi **2–5stelle**, campeggi1–5. La pagina generale storica e i vecchi moduli riportano ancora2–4/1–4: non sostituire la pagina specifica corrente con moduli storici. Nessuna classificazione a soli per villaggi dedotta. Villaggio turistico distinto dalla specificazione «villaggio albergo».

Nel coordinatoBellaria art.2.3 entrambe le tipologie sono presupposto; nell'AllegatoC2026 non hanno riga tariffaria esplicita. TariffaRTA perstelle, tariffavillaggi perstelle e relativa voce applicabile **NON DIMOSTRATE**. Art.2.4 enumera10consecutivi solo per campeggi: non estendere automaticamente il10 ai villaggi per appartenenza alle strutture all'aperto; letteralmente resta il6generale, da coordinare con conferma del Comune sul mapping. Non promuovere queste tipologie né ad «alberghi» né alla residua1€ senza istruzione ufficiale.

### Riversamento: parte determinata e discordanza pubblica

Competenza per struttura e mese di conclusione soggiorno, indipendente dall'incasso tardivo: artt.6.2/7; anno del mese di conclusione per il riepilogo finanziario. Esempio sintetico28giugno–7luglio2026: imposta del soggiorno nella competenza finanziaria luglio2026. Comunicazione mensile e dichiarazione annuale restano distinte. Non introdurre trimestre, anticipazione all'arrivo o cassa pura.

**Discordanza ufficiale rilevata:** coordinatoart.7 stabilisce entro15giorni dalla chiusura; sportello pubblico «Tempi e scadenze» indica trasmissione e pagamento dal1° al16°giorno successivo. La stessa pagina sintetizza limite6 per tutte le strutture e unica eccezione camper10mensile, omettendo l'eccezione campeggi10 presente nel coordinato. Prevalenza normativa del regolamento, ma la discordanza delle istruzioni operative deve essere chiarita: non dichiarare scadenza16 validata né inventare festività/proroga. Conferma ufficiale necessaria prima di automatizzare scadenze.

Il CSV di giugno segue arrivo e include partenze luglio; il campione non documenta competenza del versamento né gli importi accettati dalla dichiarazione. Identificativo comunicazione, IUV/causale/codice fiscale struttura e modalità pagamento richiesti per questo flusso **NON DIMOSTRATI** dalle pagine recuperate; nessuno aggiunto per supposizione. Una correzione del riepilogo mesefine è determinabile, una trasformazione delCSV o riconciliazione completa no.

### Matrice aggiornata dei sette P1

| P1 | Stato della regola/evidenza | Implementabile senza supposizioni? |
|---|---|---|
| IDS-01 periodo | **DIMOSTRATO**: giugno–settembre nel coordinato | Sì per regola stagionale; transizione configurazioni esistenti da progettare e autorizzare |
| IDS-02 età | **DIMOSTRATO** il difettotoday e soglia18; riferimento/compleanno **NON DIMOSTRATO** | Non integralmente; caso compleanno richiede conferma, proposta conservativa sopra |
| IDS-03 esenzioni/legacy | **DIMOSTRATO** condizioni e mismatchlocale; codici tecnici esterni **NON DIMOSTRATI** | Sì per mapping interno conservativo/requisiti noti; no equiparazioni generiche o certificazionecodiciStaytour |
| IDS-04 mese | **DIMOSTRATO** competenza finanziaria mesefine; flussoarrivo **DIMOSTRATO EMPIRICAMENTE**; raccordo/scadenza operativa **NON DIMOSTRATI** | Sì solo riepilogo finanziario; no cambio generaleCSV o scadenza16 senza chiarimento |
| IDS-05 writer777 | **DIMOSTRATO EMPIRICAMENTE**:209/209durata−6,205/205gruppi non ambigui6+eccedenza; codice ufficiale **NON DIMOSTRATO** | No nel perimetro generale: conferma tracciato prima del fix |
| IDS-06 storico | **DIMOSTRATO** difetto e requisito snapshotregistrato/rettifica richiesto | Sì per requisito applicativo, progettazione autorizzata distinta; nessuna attestazione invio/accettazione inventata |
| IDS-07 categorie/tetti | **DIMOSTRATO** tariffario e limiti6/10/10; mappingRTA/villaggi ed efficacia puntuale **NON DIMOSTRATI** | Sì categorie esplicitamente tariffate; no automatismo universale prima delle conferme |

«Sì» significa sufficienza documentale del sottoinsieme indicato, **non autorizzazione alla modifica**. Tutti i sette P1 restano non corretti. Non affermare che restino esclusivamente dettagli tecnici: età, scadenze e decorrenze hanno natura normativa/operativa.

### Cinque domande mirate, preparate e non inviate

Destinatario: Ufficio IDS, contatto pubblico `ids@comune.bellaria-igea-marina.rn.it`, o assistenzaHyksos per tracciato. Nessun contatto automatico effettuato.

1. Qual è il tracciatoCSV/TXT Bellaria ufficiale (colonne/date/encoding/codici), e777 indica notti oltre limite anche per esenti, campeggi e camper? Sono previsti8campi dati senza campo finale vuoto?
2. Se l'ospite compie18anni durante il soggiorno, l'età rileva all'arrivo o per ciascun pernottamento? Come viene contato il limite delle notti in questo caso?
3. Quali voci/tariffe2026 e limiti si applicano aRTA2–4stelle e villaggi turistici2–5stelle, distinti dagli appartamenti ammobiliati e dai campeggi?
4. Per un arrivo a giugno con partenza luglio, quale mese si usa nelCSV/comunicazione e quale nel riversamento; la scadenza è15 o16 e quale identificativo è richiesto per riconciliare comunicazione e pagamento?
5. Quali sono date di pubblicazione ministeriale ed efficacia fiscale diCC29/28.07.2026 eGC184/06.11.2025, inclusa la decorrenza operativa della soppressioneModello21?

**STOP documentale.** Nessuna fonte mancante trasformata in certezza, nessuna email inviata, nessuna applicazione/writer/DB modificati o prova reale effettuata.


## Gate correzione mirata età/777 — 7 ottobre 2026

**TASSA BELLARIA — CORREZIONE PARZIALE BLOCCATA.** Nessuna modifica applicativa o test permanente effettuata. La richiesta successiva conferma esplicitamente due regole operative: esenzione fino al giorno del18°compleanno incluso, imposizione dal successivo; prima parte massimo6notti,777=durata−6 anche per esenti. La conferma è istruzione operativa utente, distinta dalla specifica pubblicaSTAYTOUR tuttora non reperita; supera la precedente mancata determinazione operativa di questi due punti, non le pendenze normative esterne.

Percorso reale verificato: `Schedina.oa_date_nac / Componenti.date_nac` → persone nel `TassaDiSoggiornoService::dettaglioSchedina()` → `calcolaPersona()` → notti/esenzione/tariffa → `TassaReportController::buildRows()` → `exportCsv()`. `exportRows()` del service è un writer logico distinto, non quello usato dal download. Età oggi tramiteEtaOperativa; nessun helper condiviso modificato. Esenzione personale777 già esclusa daresolveEsenzione. Test esistenti2 nel service, non coprono download o compleanno.

**Dipendenza di scope che impone loSTOP della sezione12 della richiesta:** la ripartizione corrente usa `nottiNelPeriodo` e `giorni_massimo` configurato, non sempre durata totale e6. Riferimenti: service175–182; configurazione accetta0..365 e applica default soltanto ai valori mancanti, preservando quelli già salvati. Il CSV del controller pone quantità777 a0 e non aggiunge righe fuori stagione.

Due controesempi statici, senza DB o esecuzioneLaravel:

- Soggiorno10notti tutte nel periodo, limite configurato10: dominio attuale10notti ordinarie/0oltre; regola richiesta6ordinarie/4codice777atariffa0. Applicare quest'ultima cambierebbe il limite configurato e, per imponibili, l'importo da10×tariffa a6×tariffa. Limiti/categorie sono espressamente protetti dalla stessa richiesta. Non forzare6 per campeggi né riclassificare la struttura.
- Arrivo31maggio/partenza3giugno, configurazione stagionale1giugno–30settembre: durata3notti, dominio2nelperiodo/0oltre. Somma attuale delle notti rappresentate2; requisito richiesto somma3. Occorre rappresentare anche la notte fuori periodo con un trattamento/codice non specificato oppure cambiare il criterio delle notti. Non tassare la notte esterna, attribuirle400/777, eliminare il filtrostagione o introdurre un codice per supposizione. Il periodo e gli altriP1 restano protetti.

Questi casi non smentiscono la regola confermata per soggiorni interamente nel periodo con limite6; impediscono di dichiarare la correzione generale richiesta mantenendo tutti i divieti. Il ramo età potrebbe essere sviluppato separatamente, ma la richiesta imponeFERMATI quando una delle due correzioni dipende da comportamento escluso: nessuna implementazione parziale iniziata.

Per riprendere serve delimitare esplicitamente la ripartizione6/777 ai soggiorni pertinenti con limite6, mantenendo limiti differenti, e definire la rappresentazione delle notti fuori stagione; oppure autorizzare la variazione dei comportamenti esclusi. Nessuna di queste scelte adottata autonomamente. AltriP1 aperti: IDS01periodo,IDS03legacy/esenzioni,IDS04mese/riversamento,IDS06storico,IDS07categoria/tetti; ancheIDS02/05 restano non corretti finché questo gate non è risolto. RTA/villaggi, decorrenze, scadenza15/16 invariati.

Verifiche della fase: sola lettura dei percorsi, controllo integrità e diffcheck documentale. Nessun nuovo test eseguito; non attribuire conteggi precedenti a questa fase. Nessun datoCSV personale copiato, nessun DB/server/trasmissione, commit/push/deploy. Solo rapporto eMaestro aggiornati con il blocco e le regole operative ricevute.


## Correzione controllata età e quantità777 su dati esistenti — 7 ottobre 2026

**TASSA BELLARIA — CORREZIONI DOCUMENTATE PRONTE PER RE-AUDIT.** Approvazione riferita alla verifica locale delle modifiche, non conformità complessiva, accettazione comunale o produzione. Il gate precedente è superato dalla nuova richiesta che autorizza la correzione delle sole parti determinabili e consente di lasciare residuali stagione/limiti differenti. Nessuna modificaDB/seed/migration.

### Specifica tecnica ora disponibile

Fonte locale fornita dall'utente: `/Users/jorgeluccitelli/Desktop/specifiche_importazione_bellaria.pdf` (il percorso/mnt/data dell'allegato non esiste in questo ambiente). SHA256 `d23d9f47e3c6db0a655bea911fca8201d40ba75e2a0cdf5d5f2bd6de12e0b2c7`. Titolo «Struttura del file per importare le registrazioni su StayTour da altri software — Bellaria Igea Marina». Letta integralmente. Documento fornito, non recuperato da URL pubblico del produttore; data/versione/provenienza editoriale non attestata dal testo. Codici400..440 e777 espliciti,0perquietanza,777pernottamentioltre6. Non contiene450, né la regola del compleanno incluso: quest'ultima deriva dalla conferma operativa utente.

Otto campi logici dopo il record testa; **gli esempi PDF terminano con `;`**, quindi nove elementi nel parsing per separatore, ultimo vuoto. Il CSV altro software omette il separatore finale nelle righe dati. La differenza non dimostra incompatibilità del writer: entrambe le forme hanno evidenza, non è stato eliminato ilcampo vuoto per supposizione. Specifica non prescrive encoding/newline/scala decimale; campioneASCII/CRLF finale e writerstringhePHP/LF senza finale restano differenti, non dichiarati byte-identici. Filenameitalianomese_anno.csv coerente con esempio. Nessun guest del documento/campione copiato nelle fixture.

### Inventario reale locale, sola lettura

PDO standalone senza bootstrapLaravel, destinazioneMySQL solo loopback da.envlocale, SELECT/SHOW in transazioneREADONLY conrollback. Nessuna stampa credenziali, query sugli ospiti limitate a conteggi di valori exent noti; eventuali valori liberi oscurati. Primo tentativo in sandbox non raggiunge ilDB; successiva esecuzione autorizzata per connessione locale riuscita.

Modelli `TassaEsenzione`/`TassaDiSoggiorno`, tabelle `tassa_esenzioni`/`tassa_di_soggiorno`, tenant tramite `AppartieneAStruttura` e `struttura_id`; configurazione belongsToStruttura. Struttura ha riferimenti tipologia_generale/struttura/classificazione. Migration storiche e seeders sono quelli già inventariati, invariati.

28righe esenzione, tutteattive: nove400..450 per ciascuna struttura tecnica1/9/12, più777solo per1. Non creare777 per9/12: la riga del tracciato è derivata, non causa personale; nessun record viene inserito. Catalogo esistente conserva450, regolamentare ma assente nella tabella tecnicaPDF: compatibilità esterna450 non certificata.

| Codice | DB locale | DescrizioneDB | Specifica | Uso attuale / esito |
|---|---|---|---|---|
| 400 | Presente1/9/12 | Minori fino al compimento del18°anno | Minori18 | Riconosciuto; trattamento per notte corretto, non permanente dopo compleanno |
| 405 | Presente1/9/12 | Soggetti in terapia e accompagnatori | Corrisponde | Resolver per codice; download sinteticoPASS |
| 410 | Presente1/9/12 | Soggetti invalidi e accompagnatore | Corrisponde | Resolver per codice; downloadPASS, prevale sull'età |
| 415 | Presente1/9/12 | Volontari in eventi organizzati o di emergenza | Corrisponde | Resolver per codice; downloadPASS |
| 420 | Presente1/9/12 | Soggetti coinvolti in eventi calamitosi o di emergenza | Stesso motivo tecnico | Resolver per codice; downloadPASS |
| 425 | Presente1/9/12 | Autisti di pullman e accompagnatori turistici | Corrisponde | Resolver per codice; downloadPASS |
| 430 | Presente1/9/12 | Personale dipendente della struttura ricettiva | Corrisponde | Resolver per codice; downloadPASS |
| 440 | Presente1/9/12 | Forze Armate e Vigili del Fuoco in servizio | Corrisponde | Resolver per codice; downloadPASS |
| 777 | Presente1 | Pernottamenti oltre il limite massimo di giorni imponibili | Oltre6giorni | Già escluso dalle esenzioni personali; quantità derivata corretta nelCSV perconfig6 |
| 450 | Presente1/9/12 | Familiari del gestore se anagraficamente conviventi | Non elencato | Preservato; codiceCSV esterno non certificato |

Configurazioni locali:1/9/12 hanno tariffa1.50,tetto6,soglie17/18,periodomarzo1–ottobre1;7/8/11 recordnull,10senza configurazione. Nessun limite10 reale trovato: il caso precedente era **controesempio sintetico**, non un dato osservato. Strutture1/7classificazioneID1(3stellesuperior),9ID4(3stelle),restanti classificazioneassente; nessuna assegnazione tariffaria nuova.

Legacy: tutte35Schedine e16componenti presenti hanno exentNO; nessun E01..E06 o stringa legacy osservato. DistinzioneA: codici ufficiali presenti e riconosciuti, non un difetto generale delresolver. B: opzioniUIlegacy restano potenzialmente non risolte, ma non presenti nei dati locali letti; mapping automatico non applicato. C: nessuna obsolescenza dimostrata. D: nessuna cancellazione o riscrittura storica,450preservato. **IDS03non corretto**: la condizione di autorizzazione per un mappinglegacy basato sulDB non è soddisfatta.

### Modifiche applicative circoscritte

- `TassaDiSoggiornoService`: ramoBellaria riconosciuto dalComune della struttura, data nascita e data della notte; helperEtaOperativa condiviso invariato. Checkoutescluso, notte riferita al giorno iniziale. Esenzione fino al18°compleanno incluso, imponibilità dal successivo. Codice400daetà non esenzionepermanente, altre esenzioni riconosciute prevalgono. Segmenti fiscali per persona, aggregati coerenti: nessun ospite duplicato nel riepilogo. RamoaltriComuni preservato.
- `exportRows` e materializzazioneCSV delcontroller utilizzano i segmenti delservice; nessuna regola d'età duplicata inview/controller. La quantità777 nel download legge `pernottamenti_oltre_max`, non lozero di `pernottamenti_imponibili`. Riepilogo controllousa `notti_tassate` effettive anche quando l'esenzione è parziale.
- Nuovo `tests/Feature/TassaBellariaEtaCsvTest.php`: nove test permanenti, dati interamente sintetici, catalogo creato soltanto nelDB effimero di test, nessun seeder o nuova migrazione. Fixture non copiate daDBlocale néCSVreale.
- FormatterPint applicato e verificato sui soli3filePHP della task; include normalizzazione stilistica preesistente senza modifiche funzionali fuori scope.

### Prima/dopo e interazione

Età precedente basata sulclockodierno: soggiorno passato poteva cambiare tassa. Ora età delcapo ecomponenti deriva dalle date del soggiorno; risultato identico conclock2025e2030. Casi minorenne/adulto/compleanno intermedio/all'arrivo/alla partenza coperti. Sei notti10–16giugno, compleanno13: quattro esenti, due imponibili, €3atariffa1.5. Compleannoall'arrivo: prima notte esente, cinque imponibili; compleannocheckout: tutte le notti precedenti esenti.

Con10notti e compleanno13: righe400/0/777 con4/2/4notti, somma10,imposta€3. Conprima parte tuttaimponibile:6+4,€9; tuttaesente:6+4,€0. Quantità777per1/5/6notti assente,7→1,10→4,20→14.777non rende esente l'ospite: inputpersonale777non riconosciuto come esenzione. Codici405..440 sono esercitati nel downloadHTTP, senza cambiare le condizioni sostanziali di attestazione degli altriP1.

### Confini mantenuti e residui

La correzione777 è completa **per il dominio verificato con tetto6**. Il service conserva periodo e tetto configurati; con10non si forza6 né si riduce l'imposta. Questa parte della conformità generaleBellaria resta bloccata come richiesto, perché modifica altri comportamenti fiscali. Non dichiarare777=durata−6universale nelsoftware attuale.

Per31maggio–3giugno e periodoestivo, restano2notti pertinenti su3durata: nessun codice fuori periodo inventato; stagioneconfigurata non corretta. Conservazione quantità confrontata con notti fiscalmente pertinenti, non obbligo di esportare ogni notte fuori stagione. Dataregistrazione oggi resta now(), sebbenePDFammetta arrivo o partenza: conseguenza possibile mese registrazione diverso dalle due dateammesse, in aggiunta al filtroarrivo; da risolvere nelP1periodo/riversamento, non modificato incidentalmente. Non correggere questi problemi cambiandoilwriter adesso.

Storico resta dinamico: un ricalcolo dopo modifica dati/configurazione cambia ancora il risultato; nessuno snapshot/registrorettifiche creato. Eliminato soltanto il cambiamento dovuto alclock nel ramoBellaria. RestanoIDS01periodopredefinito,IDS03legacy/requisiti,IDS04mese/riversamento/data_reg,IDS06storico,IDS07tariffe/tetti/classificazione; residui777perlimiti differenti/fuoriperiodo, RTA/villaggi, decorrenze, scadenza15/16. P2quoting e certificazioneencoding/newline restano.

### Verifiche

Launcherisolato, MySQL8.0.36/PHP8.3.33; identitàrisorse e23rifiuti prima delle migration sintetichePASS, build/GEOimmutabilePASS. Nessun testLaravel fuori dall'infrastruttura isolata.

Selezione: nuovoTassaBellariaEtaCsvTest, TassaDiSoggiornoServiceTest, EtaOperativaTest, QuesturaTransportGuardTest, IstatTransmissionSecurityTest: **79test /1.113asserzioniPASS**, nessun fallimento/errore/deprecazione riportato. LintPHP3filePASS, Pint--test3filePASS, diffcheckPASS. Nessuna suiteglobale/browser/trasmissionerealeeseguita; non sostituire questi risultati ai gate complessivi delprogetto.

ConfrontoRED della nuova suite sul codiceprecedente, copiaesterna byte-identicaHEAD per service/controller: **9test/61asserzioni,5fallimenti,1errore,1deprecazione**. Errore relativo al segmento pernotte assente nel codiceprecedente; fallimenti suclock,400permanente, interazione, quantità777download e righe età. RuntimeREDPHP8.4.3, rispetto aPHP8.3.33delGREEN: non confronto a runtime identico, limite esplicito. La deprecazioneRED non diagnosticata o nascosta. Risorse effimere rimosse dallauncher. Primo comandoRED interrotto prima dei test per percorso relativo errato della copia: corretto soltanto il comando, non test o applicazione. Nessun testesistente modificato per nascondere failure.


Integrità finale: soli2fileapplicativiTassa modificati; helperanagrafico condiviso,50protettiQuestura, testQuestura/ISTAT, tutte147migration, harness eDEPLOY_SPANEL invariati rispetto all'avvio. Catalogo e configurazioniDB riletti identici; letture aggregate35Schedine/16componenti soloNO. CSV ePDF originali invariati. HEADfaef3cf invariato, stagingvuoto. Nessun commit/push/deploy/server/trasmissione; storico e configoperativi non toccati.


## Completamento finale controllato Tassa Bellaria — 7–8 ottobre 2026

Questa sezione aggiorna e supera, nel perimetro qui indicato, le pendenze implementative delle sezioni storiche. Il progetto resta **BASELINE IN AUDIT** e il modulo resta parzialmente verificato: nessuna certificazione fiscale generale, production readiness, accettazione StayTour o autorizzazione al deploy.

### Architettura preservata e interventi

Riutilizzati ricevuta, pulsante, route, controller Schedina, service fiscale comune, loghi della struttura/Comune e footer istituzionale. Nessuna seconda ricevuta o calcolatore fiscale. I controller legacy dei componenti e le loro viste risultano identici all'avvio: il percorso effettivo è la tab integrata Schedina.

Il service restituisce i movimenti canonici, distinguendo notti soggiornate, nel periodo, imponibili, esenti e oltre limite. Il tab espone le notti effettivamente imponibili e le esenzioni parziali. La ricevuta, il report, il controllo e il writer usano gli stessi movimenti; nessuna tassazione fuori stagione inventata. Età e quantità777 della correzione precedente sono preservate, così come il comportamento degli altri Comuni e l'helper anagrafico condiviso.

La ricevuta esistente è adattata ad A4 verticale, header a tre colonne, loghi con fallback, ringraziamento separato dal calcolo, dati ospite/struttura/soggiorno/N. Schedina, dettaglio notti e subtotali, totale evidente e footer istituzionale riutilizzato. Non introduce numerazione fiscale autonoma né attesta pagamento/incasso. Immagine turistica facoltativa configurata dalla struttura; nessuna foto o proposta grafica incorporata. Il testo del ringraziamento è separato nella vista, non è ancora configurabile dall'interfaccia. Stampa senza menu e pulsanti, intestazioni ripetute e righe non spezzate.

Dal/Al riutilizza il calendario già disponibile, con preset questo mese, mese precedente e anno. Validazione server delle date reali, coppia completa, ordine e intervallo massimo di un anno, negli anni2015..2100. Query mese/anno precedenti restano compatibili. Selezione coerente tra report, controllo, stampa e headerCSV; filtro di appartenenza per arrivo. Report annuali/personalizzati sono strumenti interni: la possibilità tecnica di esportarli non dimostra validità amministrativa della dichiarazione.

**IDS-04:** specifica fornita: arrivo o partenza ammessi; convenzione Bellaria implementata: **data_registrazione=data_arrivo**, corroborata da553/553record del campione fornito. Generazione con clock2025/2030 produce identiche righe. Header deriva dal periodo selezionato. Il CSV conserva otto campi logici, quantità e importi, con quoting dei delimitatori/virgolette e separatore finale già previsto dal writer. Il campione ha CRLF senza separatore finale, il writer LF con campo finale vuoto: non si dichiara identità byte-per-byte o nuova accettazione ente. Nessuna specifica esplicita di encoding/newline è stata inventata.

Il controllo interno riconcilia i segmenti del service con quantità nel periodo e totale CSV in centesimi; segnala identificatori tecnici delle Schedine incoerenti. Non verifica pagamenti, riversamenti o accettazione esterna.

### IDS-03, IDS-06 e IDS-07

IDS-03: catalogo UI effettivo e salvataggio HTTP→DB sintetico→service→tab→ricevuta→report/CSV esercitati per400/405/410/415/420/425/430/440.400 è derivato dalle date anagrafiche; le altre esenzioni restano prevalenti. Componente410 salvato dalla tab integrata; input personale777 respinto anche nei componenti, senza creare Schedine supplementari.777 resta derivato.450 preservato, non certificato dal PDF. Valori legacy sconosciuti provocano un errore di validazione, senza conversione o cancellazione automatica. Attestazioni e note giustificative non sono state reinventate.

IDS-06: nuova tabella tassa_exports, snapshot cifrato autenticato, SHA256 dei byteCSV, versione per struttura/periodo, autore/data e collegamento alla precedente versione. Consolidamento esplicito in transazione con lock della struttura e ricaricamento configurazione/catalogo. Conserva una sola elaborazione canonica, il CSV risultante, movimenti, calcoli e configurazione/dati struttura pertinenti, escludendo credenziali, documenti e date di nascita degli ospiti. Modifica/cancellazione del model consolidato vietate; accesso tenant e hash verificati. Versione successiva costituisce rettifica locale, senza presumere procedura di rettifica comunale.

CSV, report e ricevuta storici leggono la versione conservata senza ricalcolo: nome, esenzione, partenza e tariffa modificati nelle prove non cambiano la versione1; versione2 collegata e distinta. La ricevuta storica non richiede il recupero della Schedina viva. La consultazione corrente resta esplicitamente ricalcolata. Non è possibile ricostruire retroattivamente export anteriori privi di snapshot. I file illustrativi sono referenziati per percorso, non congelati come byte: un'eventuale rimozione esterna del logo/immagine può modificare la presentazione, non i movimenti fiscali conservati. Il caricamento immagine non cancella il precedente file, proprio per preservare i riferimenti storici.

La nuova migration2026_10_07_180000 è minima e circoscritta alla Tassa: tabella export e colonna immagine facoltativa. Necessità spiegata prima dell'intervento; eseguita esclusivamente nei DB effimeri. **Non applicata al DB operativo**: funzionalità storico/configurazione immagine non sono ancora installate nell'ambiente operativo. Nessuna migration storica modificata.

IDS-07: mapping positivo solo alberghi/hotel con categoria determinabile1..5stelle, prezzi2026 €1/€1/€1,50/€2,50/€2,50, tetto6; riferimenti relazionali esistenti riutilizzati. Configurazioni2026 in contraddizione con questo profilo sono bloccate, senza modificarle automaticamente. Default Bellaria1giugno–30settembre e blocco delle configurazioni stagionali discordanti. Nessun default tariffario generico per categorie sconosciute. RTA/villaggi restano dipendenti da fonte/mapping certo. Tetti diversi da6 sono conservati dal calcolatore ma il CSVBellaria li blocca finché non è dimostrato il raccordo con777. Nessuna forzatura dei record reali.

### Dati esistenti e documentazione

Rilettura localePDO in transazioneREADONLY, senza avvioLaravel: **28righe attive**, non29, nessun duplicato struttura+codice; codici400..450 perID1/9/12,777soloID1. Tre configurazioni valorizzate restano1,50/tetto6/età17–18/marzo–ottobre;35Schedine e16componenti soloNO. Valori e conteggi riletti identici. Nessun seed, DDL o aggiornamento del DB operativo. La stagione operativa legacy discordante viene quindi respinta dal nuovo codice: occorrerà una distinta autorizzazione per riallineare le configurazioni prima dell'uso reale.

CampioneCSV originale SHA256899d06781cca3161a60cd47147d3ba3c28cb91a2ffd3b9b9cb63b09d08968fe6 invariato. Il PDF fornito è stato letto nelle fasi precedenti con SHA256d23d9f47e3c6db0a655bea911fca8201d40ba75e2a0cdf5d5f2bd6de12e0b2c7; il percorsoDesktop documentato non è più presente nell'ultima verifica, pertanto **l'invarianza finale di questo file esterno non è riconfermata**. Non è stato modificato o cancellato da questa attività; resta necessario conservarne una copia disponibile per il re-audit delle fonti.

### Verifiche finali

Regressione applicativa precedente al controllo aggiuntivo del ruolo: **91test/1.452asserzioniPASS**, launcher isolatoPHP8.3.33/MySQL8.0.36. Comprende nuovi test completamento, precedente età/777, Tassa esistente, età condivisa, SchedinaStore, QuesturaTransportGuard e IstatTransmissionSecurity. Scenari adulti/minori/compleanno, tutte esenzioni pertinenti,1/6/7/10/20notti, famiglia, più Schedine/strutture, attraversamento mese/stagione. Assert esplicite su subtotali, totale tab/ricevuta/report e somma quantità×tariffaCSV, conservazione delle notti nel periodo, non dell'intera durata fuori stagione. Totali0 e positivi, clock indipendente, periodi invalidi, tenant A/B, ID manipolati e anonimo, storico e immagine. La verifica aggiuntiva finale del ruolo senza autorizzazione è registrata nell'esito conclusivo seguente.

Browser isolato: **3PASS**, con/senza immagine e gruppo36persone, inclusi periodo, controllo, consolidamento/download, apertura versione e ricevuta storica. Doppioni in memoriaSOAP: **14controlliPASS**, nessuna reteQuestura. Launcher: identità processi/DB/HTTP,23rifiuti override e build/GEOimmutabilePASS; risorse temporanee rimosse. PDFverificati A4:1/1/2pagine, header ripetuto e footer finale; gruppo totale€318. Visualizzazione A4 resa e ispezionata.

Tentativi browser intermedi documentati: stackCSS inizialmente errato e footer nascosto in stampa corretti; selettori del test adeguati a icona del collegamento, doppia conferma del gestionale e pannello storico chiuso. Le verifiche sostanziali non sono state eliminate. Ultima prova include l'intero percorso storico.

Sintassi **10PHP PASS**, Pint **8filePASS**. SchedinaController e routes/web hanno violazioni di stile già riprodotte sulle copieHEAD: nessuna formattazione generale dei due file condivisi. Non dichiarare PintglobalePASS. Nessuna suiteglobale ripetuta e nessuna correzione dei failureQuestura globali fuori scope.

### Integrità, inventario e residui

50fileQuestura protetti,30fileISTAT protetti,147migration preesistenti, helperEtaOperativa e sei file preesistenti non appartenenti a questo completamento invariati rispetto all'avvio. Config/Handler/writer/payloadQuestura/ISTAT non modificati. HEAD/origin locale faef3cf, main,0/0 e staging vuoto; worktree intenzionalmente modificato. Nessun commit/push/deploy/server/produzione/trasmissione reale.

File di questa attività (19; comprendono il rapporto e file Tassa già modificati prima dell'avvio):
- `app/Models/TassaExport.php`
- `database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php`
- `docs/imposta-soggiorno-bellaria-audit-2026-10-07.md`
- `tests/Feature/TassaBellariaCompletenessTest.php`
- `tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js`
- `tests/Isolation/tassa-bellaria-fixtures.php`
- `app/Http/Controllers/SchedinaController.php`
- `app/Http/Controllers/TassaDiSoggiornoController.php`
- `app/Http/Controllers/TassaReportController.php`
- `app/Models/TassaDiSoggiorno.php`
- `app/Services/TassaDiSoggiornoService.php`
- `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`
- `resources/views/schedina/partials/form.blade.php`
- `resources/views/schedina/print-tassa.blade.php`
- `resources/views/tassa_di_soggiorno/edit.blade.php`
- `resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php`
- `resources/views/tassa_di_soggiorno/rapporto-print.blade.php`
- `resources/views/tassa_di_soggiorno/rapporto.blade.php`
- `routes/web.php`

I sei file preesistenti preservati sono DEPLOY_SPANEL, harnessrun.py, TestingEnvironment.php, le due policyPython e TassaBellariaEtaCsvTest.php. Il worktree complessivo include pertanto25file:16tracked modificati e9untracked. Artefatti visuali/log/PDF solo fuori repository.

Residui esterni al perimetro implementabile verificato: mappingRTA/villaggi; cap10campeggi/camper e raccordo777; efficacia fiscale puntuale delle delibere e distinzione comunicazione/riversamento/scadenza15–16; attestazioni/nota delle esenzioni e qualificazione di eventuali legacy;450non attestato; encoding/newline e accettazione veraStayTour; limiti della previewlegacy IDS-09; storico preconsolidamento non recuperabile e politiche di conservazione/keymanagement dei nuovi snapshot da definire prima dell'uso reale. DebitoPint preesistente e disponibilità della copiaPDF fonti esplicitati. ConfigurazioneDBoperativa e migration rimangono un gate separato. Nessuna conformità totale né produzione pronta.

### Esito conclusivo del completamento

**TASSA BELLARIA — IMPLEMENTATA E PRONTA PER RE-AUDIT FINALE**, limitatamente al dominio e alle fonti implementabili qui descritti. Nessun nuovoP0/P1 dimostrato in questo perimetro. Residui normativi/categorie non documentate e gate operativi restano aperti, senza certificazione di conformità totale.

Ultima regressione: **91test/1.456asserzioniPASS**. Aggiunta prova di proprietario senza struttura autorizzata: export reindirizzato alla selezione autorizzata, ricevuta corrente e storica negate404; anonimo alla login. Nessun byte applicativo cambiato tra il precedenteGREEN e questa verifica; aggiunte soltanto queste assertion di sicurezza. Browser finale3PASS. Sintassi10PHP/Pint8PASS e due file condivisi con debito di stile preesistente; diffcheckPASS. Nessun test esistente neutralizzato.


## P1 — Separazione periodo Tassa e gestione Schedina — 8 ottobre 2026

La precedente conclusione di completamento è integrata da questo difetto P1 individuato dall'utente: validazione della configurazione fiscale eseguita durante l'apertura della Schedina. Classificazione P1 fino alla regressione conclusiva positiva.

Causa ricostruita: GET /schedine e relativo elenco ricercato, GET /schedine/nuova e GET /schedine/{id}/modifica chiamavano direttamente TassaDiSoggiornoService::dettaglioSchedina. Il controllo periodo_tassa del service rigettava la configurazione Bellaria diversa da1giugno–30settembre; l'eccezione ValidationException risaliva nella risposta Laravel. Il master rende gli errori come data-server-alert=error e resources/js/ui/config-ux.js::showServerAlerts li presenta in SweetAlert “Attenzione”. Non è un middleware o guard globale: è una validazione fiscale richiamata dal controller Schedina. Store/update non validano il periodo fiscale, ma la successiva visualizzazione della Schedina poteva risultare bloccata. La configurazione legacy marzo–ottobre rilevata nelle letture precedenti spiega la condizione concreta, senza attribuire il problema al solo soggiorno fuori stagione.

Correzione minima: SchedinaController::dettaglioTassaPerGestione, usato solo da elenco/ricerca/nuova/modifica, verifica la completezza della configurazione e intercetta ValidationException del calcolo fiscale. Restituisce totale=null, nessuna riga e messaggio esplicito “Configurazione Tassa di soggiorno da verificare. La Schedina può essere registrata, ma il calcolo della Tassa non è disponibile finché la configurazione fiscale non viene corretta.” Nessun successo fiscale simulato e nessun €0 inventato. Il tab non rende totali/dettagli o stampa corrente quando il calcolo è indisponibile; l'elenco e il dato del riepilogo indicano “Calcolo non disponibile”. I pulsanti di salvataggio Schedina restano utilizzabili. Eccezioni inattese non sono assorbite da un catch generico.

La validazione fiscale non è rimossa dal service: report/CSV/consolidamento e ricevuta corrente restano soggetti alle loro regole. Ricevute storiche conservate mantengono il proprio snapshot. Calcolo valido fuori stagione restituisce correttamente zero; per31maggio–3giugno le date31/05 e03/06 e tre notti totali restano intatte, con due notti fiscali e €3 peradulto/€1,50, nessun777 per la notte fuori stagione. Il service fiscale non è stato modificato da questa correzione.

Apertura struttura già gestita con tipo_apertura e data_apertura/data_chiusura; IstatTabellaAService::isOpenForDay distingue l'apertura annuale da quella stagionale. Non introdotta alcuna nuova gestione apertura e nessun uso del periodo Tassa in sua sostituzione.

Due regressioni permanenti aggiunte al test di completamento: creazione/modificaHTTP febbraio,maggio,luglio,novembre e31/05–03/06, con date/notti/importi della Schedina e ricevuta; configurazione incoerente,incompleta,mancante con apertura nuova,registrazione,modifica,elenco consentiti, totale=null e stampa fiscale rifiutata422. Selezioni reali QuesturaTxtExportService e IstatTabellaAService ricevono le stesse date e durata complete dopo il salvataggio, anche fuori stagione: nessuna comunicazione rete eseguita.

Il primo tentativo della regressione ha riportato93test/1.516asserzioni e un errore del test per nome route schedina.create inesistente. Corretto il test al percorso effettivo /schedine/nuova, senza neutralizzare verifiche; aggiunte assertion sulle selezioni Questura/ISTAT.

File di questa correzione: app/Http/Controllers/SchedinaController.php; resources/views/schedina/list.blade.php; resources/views/schedina/partials/form.blade.php; tests/Feature/TassaBellariaCompletenessTest.php; questo rapporto; Maestro. Nessun'altra modifica rispetto all'avvio di questa task. Le modifiche preesistenti restano preservate. Nessun DB operativo, migration reale, modifica configurazione, commit/push/deploy/server o trasmissione. Stato generale BASELINE IN AUDIT.

### Regressione conclusiva P1 periodo/Schedina

**P1 CORRETTO E VERIFICATO.** Launcher isolatoPHP8.3.33/MySQL8.0.36: **93test/1.592asserzioniPASS**, comprensivi delle due nuove regressioni e dei test precedentiTassa/età/777/SchedinaStore/QuesturaTransportGuard/IstatTransmissionSecurity. Febbraio,maggio,novembre→creazione/modifica consentite e tassa0 con configurazione valida; luglio→€4,50 per3notti;31/05–03/06→3notti soggiornate,2pertinenti,€3 e0oltrelimite. Configurazione incoerente/incompleta/mancante→nuova/registrazione/modifica/elenco consentiti, totale=null, avviso esplicito e ricevuta corrente422. SelezioniQuestura eISTAT mantengono date/durata intere per ogni scenario.

Secondo tentativo:93test/1508asserzioni con un fallimento del test sul confronto stretto3.0vs3 restituito daCarbon. Corretto al confronto numerico della durata, senza eliminare assertion sulle date o sulle notti; ultimo esito sopra. Identitàprocessi/DB/HTTP,23rifiuti override ebuild/GEOimmutabilePASS; cleanup effimero completato. Sintassi2PHP ePint deltestPASS, diffcheckPASS. Nessuna formattazione generale diSchedinaController, già non conformePint nella baseline. Nessuna suiteglobale/browser ripetuta, nessuna trasmissione.

Confronto rispetto all'avvio: esattamente6file modificati (controllerSchedina,list/tabSchedina,testcompletamento,rapporto,Maestro), nessun nuovofile.50Questura e tutti42percorsiISTAT rilevati (inclusi i30protetti) invariati perhash; service fiscale,migration,harness e altre modifiche preesistenti invariati. HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,main,0/0,stagingvuoto. Nessun aggiornamento DBoperativo, nessun commit/push/deploy. Il progetto resta BASELINE IN AUDIT e i residui del completamento precedente rimangono separati.


## Re-audit finale indipendente Tassa Bellaria — 8 ottobre 2026

**TASSA BELLARIA — BLOCCATA PRIMA DEL COMMIT.** NessunP0 dimostrato. Un nuovoP1 riprodotto: categorie non certificate ammesse silenziosamente all'elaborazione fiscale. Questo esito supera la precedente disponibilità al re-audit; **il P1 periodo/Schedina approvato resta CORRETTO E VERIFICATO**, senza alcuna modifica o riapertura.

### Metodo e inventario congelato

Copia temporanea /private/tmp/schedine-reaudit-tassa, derivata dal medesimoHEAD e sovrapposta ai byte correnti. Tutti21file del candidato risultavano byte-identici al repository al momento delle prove. Test indipendenti creati esclusivamente in tale copia, mai aggiunti al candidato; infrastruttura isolata originale conservata. Nessuna modifica applicativa, al P1 approvato o ai test permanenti. Aggiornati soltanto questo rapporto eMaestro per registrare l'esito.

**Inventario completo candidato Tassa:21file,14tracked modificati+7untracked.** Integra tutti i sei file approvati del P1; rispetto ai19file del precedente completamento aggiunge list.blade.php e include anche il test età/777 della correzione precedente. Cinque file dell'harness/DEPLOY preesistenti sono fuori dal candidato Tassa e preservati. Il worktree totale è26file:17tracked+9untracked. Nessun file temporaneo diagnostico incluso nel repository originale.

- `app/Http/Controllers/SchedinaController.php`
- `app/Http/Controllers/TassaDiSoggiornoController.php`
- `app/Http/Controllers/TassaReportController.php`
- `app/Models/TassaDiSoggiorno.php`
- `app/Models/TassaExport.php`
- `app/Services/TassaDiSoggiornoService.php`
- `database/migrations/2026_10_07_180000_create_tassa_exports_and_receipt_image.php`
- `docs/imposta-soggiorno-bellaria-audit-2026-10-07.md`
- `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`
- `resources/views/schedina/list.blade.php`
- `resources/views/schedina/partials/form.blade.php`
- `resources/views/schedina/print-tassa.blade.php`
- `resources/views/tassa_di_soggiorno/edit.blade.php`
- `resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php`
- `resources/views/tassa_di_soggiorno/rapporto-print.blade.php`
- `resources/views/tassa_di_soggiorno/rapporto.blade.php`
- `routes/web.php`
- `tests/Feature/TassaBellariaCompletenessTest.php`
- `tests/Feature/TassaBellariaEtaCsvTest.php`
- `tests/Feature/tassa-bellaria-ricevuta.playwright.spec.js`
- `tests/Isolation/tassa-bellaria-fixtures.php`

### P1 residuo riproducibile — categorie non documentate ammesse

**P1-TASSA-CATEGORIE: assenza di rifiuto quando il mapping della categoria è indeterminato.** TassaDiSoggiornoService::regolaAlbergoBellaria restituisce null perRTA/villaggi; dettaglioSchedina applica il controllo tariffario solo se $regola è valorizzata. Il controller validateStayTourScope controlla soltanto tetto6. Una configurazione manuale/legacy completa1,50/6/giugno–settembre consente dunque il calcolo e l'export anche quando manca un profilo certificato.

Riproduzione indipendente, solofixture sintetiche: struttura Bellaria hotel3stelle con config valida e soggiorno10–20giugno; sostituire la sola tipologia conRTA oppureVillaggio turistico; GET /tassa_di_soggiorno/rapporto/csv?data_da=2026-06-01&data_a=2026-06-30. Atteso422/rifiuto motivato, **ottenuto200 in entrambi i casi**. Due assertion HTTPfallite, ripetute suPHP8.3.33. Nessun invio o importazione reale. Non basta che il metodo di mapping restituisca null: il chiamante deve impedire l'elaborazione del dominio non certificato. Medesima condizione statica per hotel con classificazione non riconosciuta.

Il problema è distinto dall'incertezza normativa delle categorie: **non si richiede di inventare tariffeRTA/villaggi**, ma di separarle tecnicamente dai profili supportati. Il candidato non soddisfa il requisito utente “non devono essere silenziosamente accettate”; pertanto il commit è bloccato anche se il profilo alberghiero positivo passa. Nessuna correzione effettuata nell'audit.

### Gate verificati

| Gate | Esito e limite |
|---|---|
| P1 periodo/Schedina approvato | PASS indipendente: registrazione/modifica febbraio/luglio/novembre, zero soltanto con configurazione valida fuori periodo, calcolo non disponibile con configurazione incoerente/mancante, ricevuta422. Test permanenti includono anche maggio e configurazione incompleta. |
| Cavallo stagione | PASS:31/05–03/06 resta soggiorno3notti,2fiscali,€3,0oltrelimite. Nessun777 della notte fuori stagione. |
| Questura/ISTAT | PASS: selezioni dei servizi reali mantengono date complete. Regressioni dei rispettivi guardrail/sicurezza verdi; byte invariati, nessuna rete reale. |
| Dal/Al | PASS: date reali/coppia/ordine/intervallo validati server, preset e collegamenti browser coerenti. Non certificata ammissibilità amministrativa diCSV annuali/custom. |
| data_registrazione | PASS: arrivo perBellaria, clock2030 non cambia le righe;553/553 nel campione originale. La specifica fornita ammette arrivo o partenza. |
| Invariante quantità/classificazione/importi | PASS per profilohotel certificabile e fixture note: compleanno13giugno su10–20 produce400/0/777 con4/2/4,totale10notti,€3; tab/ricevuta/report/CSV coincidono. Esenzioni, famiglia,multipli soggiorni, stagionalità e1/6/7/10/20notti nei test permanenti. |
| Esenzioni/777 | PASS tecnico400..440,400temporale,altre cause prevalenti,777derivato non personale.450preservato, non certificato dalla specifica; attestazioni/note restano separatamente documentate. |
| Categorie/tariffe | Profilo positivohotel1..5stelle:1/1/1,50/2,50/2,50,tetto6; mismatch riconosciuto rifiutato. **FAIL per categoria indeterminata: P1 sopra.** Cap10CSV bloccato; nessuna generalizzazione del6 per campeggi/camper. |
| Ricevuta/stampa/immagine/footer | Browser3PASS con/senzaimmagine egruppo36persone; PDFA4 1/1/2pagine, intestazione suogni pagina efooter finale. Totale gruppo318. Nessuna numerazione fiscale nuova o attestazione incasso. |
| Storico/snapshot/rettifiche | PASS: CSV/report/ricevuta della versione conservata non cambiano dopo modifica dati/tariffa; versione2 collegata alla1; snapshot cifrato autenticato/hashCSV eautorizzazione verificati. Vecchi export senza snapshot non ricostruibili, asset illustrativi non congelati in byte. |
| Tenant | PASS: strutturaB non accede alCSV o ricevuta storicaA; test permanenti coprono IDforgiati/anonimo/ruolo privo struttura. Nessun accesso fondato sulsolo pulsante nascosto. |
| Concorrenza | PASS reale suDBeffimero: due processiPHP separati, sincronizzati da barriera, invocano consolida sulla stessa struttura/periodo; entrambi302,versioni1/2,precedente_id corretto,hash validi. Nessuna prova serialeHTTP presentata come concorrenzaDB. |
| Nuova migration | PASS up/down/up della sola migrationTassa, colonna immagine e tabella presenti/assenti coerenti, duplicateversion respinta SQLSTATE23000;versione2 consentita. Nessuna migration operativa. |
| Configurazioni marzo–ottobre | RiletturaREADONLY identica:ID1/9/12 ancora marzo–ottobre/1,50/6. Non certificate come configurazioni fiscali valide. Corretto il comportamento Schedina/calcolo indisponibile; riallineamento richiede gate separato, non modifica automatica. |
| Decorrenze | Conferma anno2026 e valori numerici già documentati; date fiscali puntuali pubblicazione ministeriale/scadenze15–16 non dimostrate. Nessun nuovo valore o automatismo di efficacia inventato. Non certificato l'intero processo dichiarazione/riversamento. |

### Test, tentativi intermedi e limiti

Esito conclusivoPHP8.3.33/MySQL8.0.36: **98test/1.694asserzioni,2fallimenti**, esclusivamente i due diagnosticiRTA/villaggio sopra. I93test permanenti del candidato passano; le prove indipendenti suSchedina/invariante/storico/tenant e la concorrenza passano. **Non dichiarare la suite finalePASS.** Nuova migration separata: **1test/8asserzioniPASS**. Browser **3PASS**,14controlliSOAPinmemoriaPASS,23rifiuti di override/identitàrisorse/buildGEOimmutabilePASS, risorse effimere rimosse.

Primo giro indipendentePHP8.4.3:97test/1684asserzioni,2fallimenti delle categorie e1deprecazione dipendenza. Runtime poi fissato esplicitamente8.3.33, senza cambiare il candidato. Primo tentativo della prova di concorrenza usava DatabaseMigrations e attivava il rollback generale degli storici: indice asp_proprietario_id_idx richiesto daforeignkey causavaerrore1553, poi molte prove fallivano per schema parzialmente ridotto. È un problema del contesto diagnostico/rollback storico, non della nuova migrationTassa o della correzione approvata. Prova separata dalrollback globale usando schema già preparato dal launcher; file diagnostici soltanto nella copia temporanea. Nessuna migration storica corretta o risultato nascosto. La reversibilità della sola nuova migration è verificata separatamente.

Sintassi **11PHP PASS**, Pint **9filePASS**. SchedinaController e routesweb restano non conformi per debito di stile riprodotto anche sulle copieHEAD; nessuna formattazione generale. DiffcheckPASS. Nessuna suiteglobale o correzione dei failureQuestura globali.

SpecifichePDF fornite: copia originaria non più disponibile alpercorsoDesktop e nessun duplicato conhash originario trovato traPDFtemporanei. Evidenza storica ehash registrato preservati, ma disponibilità della fonte deve essere ripristinata perla certificazione documentale. Le due pagine ufficialiCC29/GC184 tentate nelre-audit non sono accessibili dallo strumento; non si dichiara una nuova riconferma normativa. CampioneCSVoriginale presente eimmutato.

### Integrità e decisione

50fileQuestura e42percorsiISTAT(inclusi30protetti),147migration originali,helperEtaOperativa e i5file preesistenti esterni al candidato invariati rispetto all'avvio. Byte applicativi/test permanenti delcandidato invariati. Solo rapporto/Maestro aggiornati con questo audit. HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,main,0/0,stagingvuoto. Nessun commit/push/deploy/server, DBoperativo scritto o trasmissione reale.

**P0: nessuno dimostrato. P1 residuo: P1-TASSA-CATEGORIE, riprodotto in due varianti.** Il P1 periodo/Schedina resta approvato epositivo. Configurazioni operative invalide/migration non applicata sono prerequisiti ambientali distinti, non un motivo per riaprire la correzione. Ulteriori limitiP2/documentali:450/attestazioni/nota,encoding/newline eaccettazioneente,previewlegacy,decorrenze/riversamento,conservazione/keymanagementsnapshot,assetstorici,recuperoexportpregressi,debitoPint efontePDF indisponibile. RTA/villaggi non sono categorie certificate; richiedono esclusione esplicita fino a fonte certa. Nessuna readiness oconformitàglobale dichiarata.


## Correzione finale P1 categorie non certificate — 8 ottobre 2026

**TASSA BELLARIA — P1 CATEGORIE CORRETTO, PRONTA PER RE-AUDIT FINALE.** La precedente chiusura bloccata è superata esclusivamente per il P1 di codice dimostrato sulle categorie; nessuna approvazione al commit, deploy, produzione o accettazione StayTour. Stato generale **BASELINE IN AUDIT** invariato. Il P1 periodo/Schedina approvato è preservato e nuovamente verificato.

### Correzione centrale e confini

Nuovo TassaDiSoggiornoService::validaCategoriaBellaria: usa il mapping regolaAlbergoBellaria già esistente, senza modificarne importi o riconoscimento. Per Bellaria, mapping null/non riconosciuto produce ValidationException con campo categoria_tassa_non_certificata e messaggio “Categoria della struttura non ancora configurata/certificata per l’Imposta di Soggiorno del Comune di Bellaria Igea Marina. Calcolo Tassa non disponibile.” Nessun fallback tariffario, zero fittizio o stack trace.

La stessa barriera è invocata da dettaglioSchedina e dai punti di ingresso report/controllo/CSV, anche quando il periodo selezionato non contiene movimenti. Non sono cinque regole diverse: tutti delegano al medesimo metodo del service. Consolidamento usa il controllo CSV esistente e dunque rifiuta la categoria prima di creare un export. Ricevuta corrente usa il dettaglio e viene rifiutata422. Storico e snapshot non modificati; le versioni già conservate mantengono il comportamento precedente.

L'adattatore già approvato SchedinaController::dettaglioTassaPerGestione continua a convertire gli errori fiscali in totale=null e righe vuote, lasciando creazione/modifica/elenco accessibili. Per il nuovo errore espone il messaggio della categoria, anziché una diagnosi generica della configurazione. Nessuna vista o route cambiata da questo fix. Il controllo non diventa validazione della Schedina, dell'apertura struttura, di Questura o di ISTAT.

**Categorie supportate nel mapping verificato:** hotel/albergo/alberghi con classificazione riconosciuta1..5stelle, comprese le forme superior già ammesse. Valori2026 preservati: €1/€1/€1,50/€2,50/€2,50 e tetto6, con validazione tariffa/tetto preesistente.

**Categorie non certificate:** RTA, villaggio turistico e ogni altra tipologia/classificazione non mappata, nulla, vuota, sconosciuta o ambigua. Calcolo fiscale non disponibile; report, controllo, CSV e ricevuta corrente bloccati. Nessuna tariffa nuova. Non si dichiara Bellaria certificata per tutte le tipologie o tutti gli anni.

Confronto dei metodi prima/dopo: regolaAlbergoBellaria, parseDate, diffNotti, diffNottiNelPeriodo, calcolaPersonaBellaria, calcolaPersona, exportRows e gli helper esenzioni/età identici. dettaglioSchedina cambia soltanto la chiamata alla nuova barriera. Periodo, età,777, importi, storico e ricevuta non ricalcolati con regole diverse.

### Regressioni permanenti e fixture

Quattro nuovi test permanenti in TassaBellariaCompletenessTest: RTA, villaggio, categorie nulle/vuote/sconosciute/ambigue e hotel1..5. I casi negativi provano apertura nuova, salvataggio/modificaHTTP, elenco, totale=null/nessuna riga; ricevuta/report/controllo/CSV/CSVcontrollo/stampa controllo/consolidamento422, campo errore esplicito e nessun export creato. Verificati anche report/controllo/CSV in intervallo vuoto e selezioni Questura/ISTAT con date complete e invarianti.

Ogni profilo hotel positivo verifica tariffa, tetto6, tab e ricevuta, totale report, riconciliazione controllo e CSV:6notti ordinarie+4oltrelimite con777 a zero, totale6×tariffa. Il precedente P1 periodo/Schedina resta verde per fuori stagione, configurazione invalida e31/05–03/06.

Fixture positive ora indicano esplicitamente Albergo/3stelle, anziché il precedente tipo generico Fixture hotel; incluse quelle browser e del test età/777. Tutte le assertion età/compleanno/quantità777 sono preservate. Il controllo numerico cap10 del test precedente è eseguito nel dominio generico “Altro Comune”, mantenendo10notti tassate/0oltrelimite; non si presenta più un hotel Bellaria2026 concap10 come profilo certificato. La precedente verifica cap10CSV rifiutato resta inalterata. Nessuna modifica delle formule fiscali.

Il test storico modificava originariamente l'aliquota a€2 senza categoria coerente, possibile quando la fixture era priva di profilo. Con Albergo3stelle la validazione preesistente correttamente respingeva il secondo consolidamento. La sola fixture della rettifica è aggiornata ad Albergo4stelle/€2,50; tutte le assertion di versione/hash/immutabilità/tenant conservate. Non si neutralizza un errore fiscale né si modifica lo storico applicativo.

### Esiti esatti

Suite conclusivaPHP8.3.33/MySQL8.0.36, launcher isolato: **102test/2.063asserzioniPASS**, zero fallimenti/errori. Comprende97test permanenti (93precedenti+4nuovi), i4test indipendenti e la prova concorrente esterni al repository originale. Le due riproduzioni diagnostiche RTA/villaggio che prima davano200 ora passano aspettando422, senza modificarle. Verificati età/777/esenzioni/report/CSV/ricevuta/storico/tenant, P1 periodo, SchedinaStore, QuesturaTransportGuard e IstatTransmissionSecurity. Anche il consolidamento a2processi conserva versioni1/2 e hash validi.

Browser isolato **3PASS**, con/senza immagine e gruppo36persone, incluso consolidamento e ricevuta storica. PDF A4 1/1/2pagine, footer presente; nessun nuovo template o immagine di produzione.14SOAPinmemoriaPASS,23rifiuti di override/identità risorse/buildGEOimmutabilePASS, cleanup effimero completato. Codice/test del candidato originali byte-identici alla copia effettivamente verificata.

Sintassi **11PHP PASS**, Pint **9filePASS**, diffcheckPASS. SchedinaController/routesweb conservano il debito di stile preesistente già documentato; nessuna formattazione generale. Nessuna suiteglobale o intervento sui failureQuestura globali.

Tentativi intermedi:97test/1946asserzioni e102test/2048asserzioni con un solo fallimento della fixture storico descritta sopra; i nuovi casi negativi già passavano. Corretta la fixture, non le assertion; esito conclusivo sopra. Un comando diagnostico lanciato dal repository originale invece della copia temporanea è stato rifiutato dal launcher prima dei test; ripetuto dalla directory corretta, senza aggiungere file diagnostici al candidato.

### Inventario, integrità e limiti

Otto file modificati rispetto all'avvio di questo fix:

- app/Services/TassaDiSoggiornoService.php — barriera centrale;
- app/Http/Controllers/TassaReportController.php — delega dai percorsi fiscali, inclusi periodi vuoti;
- app/Http/Controllers/SchedinaController.php — messaggio specifico senza blocco Schedina;
- tests/Feature/TassaBellariaCompletenessTest.php — nuove regressioni e fixture positive;
- tests/Feature/TassaBellariaEtaCsvTest.php — solo profilo fixture e dominio del cap10, assertion preservate;
- tests/Isolation/tassa-bellaria-fixtures.php — profilo alberghiero delle fixture browser;
- docs/imposta-soggiorno-bellaria-audit-2026-10-07.md — questa chiusura;
- docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md — conoscenza aggiornata.

Nessun nuovo file del candidato, che resta **21file (14tracked+7untracked)**; worktree complessivo26(17tracked+9untracked).50fileQuestura/42percorsiISTAT,147migration originali e nuova migrationTassa invariati; helperEtaOperativa e harnessMariaDB/DEPLOY preesistenti preservati. Configurazioni marzo–ottobre e DBoperativo non aggiornati. CSV originaleSHA256899d06781cca3161a60cd47147d3ba3c28cb91a2ffd3b9b9cb63b09d08968fe6 invariato. PDF originale già indisponibile prima del fix, ancora indisponibile: nessuna falsa riconferma del suo hash. Il conteggio documentale corrente è già28, confermato nella riletturaREADONLY del re-audit precedente; nessun29residuo da correggere in questa fase.

HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,main,0/0,stagingvuoto. Nessun commit/push/deploy/server/produzione, migration reale o trasmissione.

Nessun nuovoP0/P1 dimostrato nella selezione verificata. Il P1 categorie è corretto; restano il re-audit finale e i limiti documentali/ambientali precedentemente elencati: fontePDF, decorrenze non dimostrate, configurazioni operative legacy da riallineare con autorizzazione distinta, categorie non certificate mantenute bloccate, attestazioni/450, accettazione reale/encoding, conservazione/assetstorici/debitoPint. Non risolti incidentalmente e non dichiarati conformi.


## P1 configurazione fiscale e zero movimenti — 8 ottobre 2026

**P1 CORRETTO E VERIFICATO — PRONTA PER RE-AUDIT MIRATO, NON APPROVAZIONE COMMIT/DEPLOY.** Il chiarimento funzionale del titolare distingue apertura della struttura, periodo fiscale, presenza dei movimenti e validità della configurazione. Zero movimenti non è un errore. Il difetto riprodotto era il controllo della configurazione eseguito soltanto dentro il ciclo delle Schedine: albergo 3 stelle con configurazione marzo–ottobre, Schedina a giugno e selezione luglio vuota produceva report/CSV 200 e consolidamento 302 con uno snapshot invalido. Nessuna errata tassazione di soggiorni presenti era stata dimostrata nel caso diagnostico.

La validazione `TassaDiSoggiornoService::validaConfigurazioneBellaria` riusa la categoria certificata e le regole fiscali esistenti: configurazione presente e completa, tariffa/tetto alberghiero 2026, stagione 1 giugno–30 settembre. Il dettaglio per Schedina e il controller report delegano alla stessa validazione. Report corrente, controllo, CSV e consolidamento la eseguono prima di iterare i movimenti. Il controllo non dipende dal numero di arrivi. Intervalli che comprendono il 2026 verificano anche il profilo tariffario documentato 2026; non sono state inventate tariffe per altri anni.

Matrice verificata:

| Caso | Risultato |
| --- | --- |
| Configurazione valida e movimenti nel periodo fiscale | Calcolo normale, ricevuta/report/CSV coerenti |
| Configurazione valida e zero movimenti | Report valido a €0 e messaggio di assenza dati; nessun movimento artificiale |
| Configurazione valida e soggiorno fuori periodo fiscale | Schedina consentita, soggiorno completo e Tassa €0 per le notti fuori periodo |
| Configurazione invalida e movimenti | Calcolo non disponibile; operazioni fiscali rifiutate |
| Configurazione invalida e zero movimenti | Report/controllo/CSV/consolidamento 422, nessuno snapshot nuovo |
| Struttura aperta senza ospiti | Nessuna deduzione di chiusura; apertura preservata |
| Chiusura già configurata | Date e tipo di apertura preservati separatamente; nessuno stato nuovo dedotto dalla Tassa |

Tre regressioni permanenti nuove verificano intervalli vuoti di 1/5/20/31 giorni, report a zero e CSV senza righe ospite, consolidamento valido vuoto, configurazione marzo–ottobre, date/tariffa/cap mancanti o incoerenti con e senza arrivi, e consultazione dello storico valido dopo una configurazione corrente invalida. Le regressioni precedenti restano presenti per Schedina fuori stagione, configurazione invalida con gestione consentita, 31/05–03/06, categorie non certificate e invariante Schedina/ricevuta/report/CSV. Controller e viste Schedina non modificati in questa fase. Nessuna modifica a Questura o ISTAT.

**CSV senza registrazioni:** la specifica Bellaria, riletta integralmente e verificata visivamente nelle due pagine, prescrive un record di testa e tanti record quante le registrazioni. Il writer conserva la sola intestazione quando le registrazioni sono zero, senza inventare ospiti. La fonte non documenta esplicitamente l'accettazione amministrativa di un file con sola intestazione da parte di StayTour. Report a zero fiscalmente corretto e compatibilità strutturale del writer NON attestano accettazione reale, né adempimento di una comunicazione di assenza attività/chiusura nel portale comunale. Non è stata eseguita alcuna importazione reale. Fonte `/Users/jorgeluccitelli/Desktop/specifiche_importazione_bellaria.pdf`, SHA-256 `d23d9f47e3c6db0a655bea911fca8201d40ba75e2a0cdf5d5f2bd6de12e0b2c7`; disponibilità e identità ora riconfermate.

Evidenza prima del fix: **18 test / 878 asserzioni, 2 fallimenti attesi**, mentre il caso valido senza movimenti già passava. Conclusiva launcher isolato PHP 8.3.33/MySQL 8.0.36: **106 test / 2.231 asserzioni PASS**, 100 permanenti e 6 diagnostici/concorrenza esterni al repository. Comprende Tassa, età/777, gestione Schedina, protezione Questura, sicurezza ISTAT e concorrenza con due processi. Il diagnostico indipendente originale ora rileva report=422, CSV=422, consolidamento=422, export nuovi=0, senza modificare l'aspettativa. Identità processi/DB/HTTP, 23 rifiuti di override e build/GEO immutabile PASS; risorse effimere fermate e rimosse. Sintassi e Pint sui 3 PHP della fase PASS. Nessuna nuova prova browser necessaria: viste e flussi browser non modificati. I precedenti 3 browser PASS restano evidenza precedente, non rieseguita.

Cinque file della fase: service Tassa, controller report, test Completeness, questo rapporto e Maestro. Nessun file nuovo; candidato Tassa 21 file, worktree 26 (17 tracked modificati + 9 untracked), staging vuoto. HEAD/origin locale `faef3cf01f43d19b20d1558c1fbd3ae32e48d849`, main, 0/0. Harness e modifiche preesistenti estranee preservati. Nessun DB operativo scritto, migration operativa, commit/push/deploy/server o trasmissione reale. **BASELINE IN AUDIT**; il re-audit complessivo rimane da completare e non viene dichiarato approvato da questa correzione. Residui documentali/amministrativi e categorie non certificate restano separati.


## Configurazione automatica e riorganizzazione UI — 8 ottobre 2026

Fase autorizzata separata, successiva al re-audit funzionale. Fonte: Dati struttura (Comune, tipologia generale, tipologia struttura, classificazione, con riferimenti esistenti); profilo normativo datato `bellaria-alberghi-2026-v1`, esclusivamente 2026. Hotel/alberghi 1–5 stelle e forme superior già ammesse: aliquote 1/1/1,50/2,50/2,50, tetto 6, 1 giugno–30 settembre. Nessuna nuova tariffa o estensione RTA/villaggi. Anni senza fonte certificata e categorie non riconosciute falliscono chiusi.

Il precedente messaggio generico dipendeva dalla presenza/completezza del record fiscale, senza distinguere la fonte struttura dalla regola legale. Ora l’adattatore Schedina espone il motivo preciso e lascia registrazione/modifica disponibili, con totale nullo quando il calcolo non è affidabile. Record assente o interamente nullo + fonte completa certificata: profilo derivato in memoria, senza salvataggio GET. Un record con valori fiscali parziali o discordanti resta bloccato: nessun riallineamento silenzioso, nessun zero sostitutivo. Le fixture che rappresentavano “configurazione mancante” sono state esplicitate come fonte certificata mancante; assertion su indisponibilità, creazione Schedina e blocco ricevuta mantenute.

Diagnosi locale READ-ONLY: ID1 giugno–1 ottobre; ID9/12 marzo–1 ottobre; ID12 senza classificazione; ID7 categoria alberghiera 3 stelle superior riconoscibile, campi fiscali tutti nulli; ID8/10/11 classificazione mancante. ID7 può derivare il profilo senza scrivere il record. ID1/9 richiedono un riallineamento separato; ID12 richiede prima la classificazione. ID8/10/11 devono completare Dati struttura, non compilare tariffe nella Tassa. Marzo–ottobre è dimostrato come default del controller baseline; origine storica dei singoli record NON dimostrata. Nessun UPDATE operativo.

Tre tab Bellaria: Configurazione (fonte struttura, profilo/versione in sola lettura, diagnostica e legacy distinti), Esenzioni (catalogo unico, otto codici ufficiali virtuali, eventuali codici legacy indicati non certificati, 777 derivato non selezionabile), Stampa ricevuta (loghi dalle fonti esistenti, immagine facoltativa, messaggio automatico, anteprima reale del tenant). Nessuna configurazione duplicata del footer o nuovo template ricevuta. Catalogo ufficiale non modificabile anche da amministratore; richieste manuali di tariffa/periodo rifiutate. Metadata delle note ufficiali preservati. Nessuna creazione automatica del catalogo DB durante GET.

Duplicazione provata prima della modifica: il blocco immagine dentro il ciclo delle esenzioni produceva 10 copie DOM, 9 visibili per amministratore. Eliminato dal ciclo della Blade, non nascosto tramite CSS. Un solo blocco nella tab Stampa; la visibilità ON/OFF riguarda quel solo controllo.

Upload server-side JPG/PNG/WebP, massimo 2 MB; nuove immagini sul disco locale privato, percorso per struttura, route autenticata con containment reale e intestazioni private/no-store. Nessun percorso immagine arbitrario accettato dal form. Sostituzione/rimozione modificano soltanto la preferenza corrente; file precedenti conservati per gli snapshot storici. Le versioni fiscali includono fonte/categoria/versione/date del profilo e non vengono ricalcolate dopo variazioni struttura. Preferenze grafiche non cambiano quantità/importi/CSV. Asset illustrativi legacy pubblici non trasferiti automaticamente; la diagnostica operativa non rileva ancora la colonna immagine della migration candidata. La politica di conservazione/rimozione degli asset storici resta un punto separato, senza cancellazioni automatiche.

Correzioni emerse durante verifica: risposta file Symfony impostava public nonostante l’intestazione richiesta; resa esplicitamente privata, mantenendo assertion private/no-store. Fixture dati mancanti adattata allo schema storico enum obbligatorio: tipologia generale mancante simulata al caricamento del modello in memoria, senza SQL non strict o modifica schema; Comune vuoto e classificazione assente testati su record sintetici. Fixture amministratore completata con proprietario autorizzato, senza modificare autorizzazioni applicative. Primo confronto record nullo usa ora baseline fresh per confrontare identiche colonne DB, non un modello appena creato con attributi parziali. Nessuna assertion fiscale eliminata.

Inventario candidato: 24 file Tassa (precedenti 21, più TassaEsenzioneController e due nuovi test PHP/browser). Worktree atteso 29 file, dei quali 5 preesistenti harness/DEPLOY esterni alla task. Nessuna migration aggiunta o modificata in questa fase. Nessuna approvazione commit/deploy, accettazione reale StayTour o readiness produzione. BASELINE IN AUDIT invariato.

### File modificati in questa fase

- `app/Http/Controllers/SchedinaController.php`
- `app/Http/Controllers/TassaDiSoggiornoController.php`
- `app/Http/Controllers/TassaEsenzioneController.php`
- `app/Http/Controllers/TassaReportController.php`
- `app/Services/TassaDiSoggiornoService.php`
- `docs/imposta-soggiorno-bellaria-audit-2026-10-07.md`
- `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`
- `resources/views/schedina/print-tassa.blade.php`
- `resources/views/tassa_di_soggiorno/edit.blade.php`
- `routes/web.php`
- `tests/Feature/TassaBellariaCompletenessTest.php`
- `tests/Feature/TassaBellariaConfigurazioneAutomaticaTest.php`
- `tests/Feature/tassa-bellaria-configurazione.playwright.spec.js`
- `tests/Isolation/tassa-bellaria-fixtures.php`

### Verifica conclusiva configurazione automatica/UI

**TASSA BELLARIA — CONFIGURAZIONE AUTOMATICA E UI RIORGANIZZATE, PRONTE PER RE-AUDIT.** Nessun P0/P1 residuo dimostrato nello scope. Rimane un gate di re-audit, non autorizzazione commit/deploy.

Launcher isolato PHP8.3.33/MySQL8.0.36: **106 test / 2.337 asserzioni PASS**; prove indipendenti riconciliazione e concorrenza **2 test / 70 asserzioni PASS** (complessivamente 108 / 2.407). Verificato SCHEDINA = RICEVUTA = REPORT = CSV per quantità, classificazione e importi, anche compleanno, 777, esenzione e soggiorno a cavallo stagione. Due processi producono versioni distinte 1/2, parent e hash corretti. Nessuna modifica ai metodi preesistenti del service rispetto all’avvio di questa fase: aggiunti soltanto i cinque metodi di derivazione/diagnostica/catalogo.

**Browser 5 PASS:** due ruoli per tre tab, assenza input normativi, catalogo unico, nessuna immagine nelle Esenzioni, un blocco immagine DOM per entrambi, ON/OFF e anteprima reale autorizzata; tre regressioni ricevuta/report/CSV/storico. Amministratore seleziona esplicitamente la struttura autorizzata: senza selezione il middleware sceglieva la prima struttura disponibile, legittimamente priva di immagine. Nessuna modifica alle autorizzazioni. Ricevute verificate visivamente, footer centralizzato presente. Il launcher browser ha inoltre eseguito 10 test / 15 asserzioni età, non sommati alla regressione principale perché duplicati.

14 controlli SOAP offline, 22 sicurezza ISTAT offline, 5 guardrail PASS; 23 rifiuti override, attestazioni identità e build/GEO immutabile PASS nei launcher. Sintassi 13 PHP e JavaScript nuovo PASS, Pint 11 PHP PASS, git diff --check PASS. Debito Pint preesistente dei due file condivisi SchedinaController/routes non ampliato con formattazione generale. Tentativi intermedi con errori fixture SQL (NULL/enum), confronto attributi DB, cache file e selezione amministratore documentati sopra; esiti conclusivi tutti positivi, nessun test neutralizzato.

Integrità: 50 file Questura, 42 percorsi ISTAT, 147 migration storiche identici a HEAD; migration Tassa candidata, harness MariaDB e DEPLOY_SPANEL identici agli hash iniziali. PDF e CSV originali conservano rispettivamente SHA d23d9f47e3c6db0a655bea911fca8201d40ba75e2a0cdf5d5f2bd6de12e0b2c7 e 899d06781cca3161a60cd47147d3ba3c28cb91a2ffd3b9b9cb63b09d08968fe6. Inventario/configurazioni operative riletti in transazione READ ONLY e confrontati: invariati. Nessuna credenziale o dato ospite reale stampato.

main HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849, ahead/behind 0/0, staging vuoto. Worktree 29 file (18 tracked + 11 untracked), candidato Tassa 24, cinque file preesistenti fuori scope preservati. In questa fase 14 file modificati/aggiunti rispetto all’avvio, elencati sopra. Nessun commit/push/deploy/server/produzione/migration reale/trasmissione. RTA/villaggi non certificati e bloccati; legacy discordanti da riallineare separatamente; profili oltre2026 non inventati; accettazione StayTour di CSV vuoto e conservazione asset storici restano limiti separati. BASELINE IN AUDIT invariato.


## P1 profilo temporale e P2 cleanup asset — 8 ottobre 2026

Il re-audit mirato successivo alla riorganizzazione ha bloccato il candidato: un report 01/12/2026–30/06/2027, contenente arrivo 10/06/2027 e partenza13/06/2027, restituiva200/€4,50 e CSV200, mentre la ricevuta corrente rifiutava422. loadContext congelava il profilo nell’anno iniziale del filtro; buildRows accettava poi gli arrivi dell’anno successivo. La precedente dichiarazione di prontezza per re-audit non costituiva approvazione finale. Separatamente, failure della persistenza dopo upload lasciava un nuovo asset privato senza riferimento.

Correzione autorizzata limitata ai due difetti. Catalogo fiscale nel service con intervalli valida_dal/valida_al e versione/fonte; una sola risoluzione risolviProfiloFiscale(struttura,data). La copertura deve essere univoca: nessuna corrispondenza o corrispondenze multiple → regola_non_disponibile. Il catalogo reale contiene esclusivamente la fonte2026 già certificata. Nessun if “anno diverso2026” come barriera definitiva; estensioni future richiedono una fonte approvata e una voce con copertura temporale esplicita.

Data pertinente per il movimento: arrivo, coerente con la data_registrazione StayTour già approvata. Il filtro Dal/Al seleziona gli arrivi senza attribuire un’unica normativa all’intervallo. Report, CSV, controllo e consolidamento risolvono ogni Schedina dalla propria data, mediante lo stesso service della ricevuta/adattatore. Un movimento senza copertura blocca l’intera elaborazione, senza totale parziale/zero sostitutivo né snapshot. Intervalli cross-year restano consentiti entro il limite interno preesistente di366giorni; con soli movimenti coperti procedono. Per zero movimenti è validato il contesto pertinente della data iniziale, senza richiedere una normativa futura soltanto per l’estremo finale. Configurazioni legacy invalide e categorie non certificate restano bloccate.

Snapshot nuovi: configurazione/versione/fonte/date effettive conservate per ciascuna Schedina in calcoli[id].configurazione. La ricevuta storica legge quel profilo, con fallback al campo comune per gli snapshot precedenti. Un report multiversione non espone una tariffa unica fittizia nella configurazione comune. Versioni precedenti e collegamento della rettifica preservati; nessuna migrazione di snapshot esistenti o nuovo schema.

Profili2026/2027 dei test sono esclusivamente sintetici quando si introduce la seconda versione: €1,50 contro€3, con risultati separati4,50/6 e totale10,50. Nessuna dichiarazione di normativa Bellaria2027 reale. Un cap4 sintetico verifica il dominio/versionamento; il vincolo CSV StayTour certificato cap6 resta invariato. Formule età/777/intersezione stagione/esenzioni/writer CSV e grafica/tab rimangono nello scope già approvato.

P2: nuovo path privato scritto dopo validazione, persistenza in transazione. save che fallisce o restituiscefalse → rollback DB, cancellazione esclusivamente del nuovo file e rilancio del failure; riferimento/file precedente preservati. Test anche con eccezione dopo l’effettiva scrittura DB, non soltanto prima del save. Gli asset precedenti a una sostituzione riuscita restano conservati intenzionalmente per gli snapshot storici: nessuna cancellazione massiva o nuova retention implicita.

Fixture positive del test età diretto al service completate con tipologia generale Alberghiera: nessuna assertion fiscale modificata. Prima esecuzione111test/2329asserzioni:6errori di queste fixture mancanti e1failure nel nuovo test per percorso stampa inesistente; route corretta da /print a /stampa, senza modificare codice delle route. Conclusioni nella verifica seguente.

### Verifica conclusiva P1 temporale / P2 asset

**TASSA BELLARIA — VERSIONAMENTO TEMPORALE E CLEANUP ASSET CORRETTI, PRONTI PER RE-AUDIT.** BASELINE IN AUDIT invariato; nessuna approvazione commit/deploy/readiness produzione.

PHP8.3.33/MySQL8.0.36, launcher isolato:111test/2397asserzioniPASS nella regressione pertinente completa. Rafforzate successivamente soltanto le assertion del nuovo test su report cross-year con soggiorno tassabile2026 e importo del controllo multiversione: nuova esecuzione mirata5test/66asserzioniPASS, non sommata alla suite per evitare doppio conteggio. Tre prove esterne indipendenti invarianti/concorrenza/riproduzioneP1:3test/72asserzioniPASS. Il diagnostico annuale, immutato nell’atteso422, ora osserva ricevuta/report/CSV422 e nessun totale. Nuovo test permanente verifica anche controllo/stampa/CSV interno/consolidamento rifiutati e nessun export.

Report cross-year vuoto valido0, con solo soggiorno2026 fiscalmente coperto valido4,50, nessuna richiesta di profilo2027. Fixture multiversione: movimenti2026 e2027 applicano tariffe differenti, ricevute/report/controllo concordano; snapshot di ciascuna Schedina conserva la propria versione. Rettifica successiva con tariffa sintetica diversa conserva parent e precedente snapshot/CSV/report/ricevuta. Cap4 sintetico limitato alla prova di dominio, non dichiarato supporto StayTour reale. Failure dopo saved: rollback e assenza file nuovo verificati sia senza precedente sia con riferimento/file precedente invariati. Tenant/upload/formati/limiti regrediti nella suite esistente.

Browser5PASS (due ruoli/tab/anteprima/mobile e tre ricevute/report/export storico). In chiusura del proxy effimero è comparso IncompleteRead dopo il completamento delle cinque prove; launcher exit0 e cleanup completato, harness non modificato. 14SOAPoffline,22sicurezzaISTAT,5guardrailPASS;23rifiuti override/identità/buildGEO/cleanupPASS. Lint6PHP modificati, Pint5PHP (SchedinaController condiviso con debito baseline, non riformattato), checkJS2spec e gitdiffcheckPASS.

50Questura/42ISTAT/147migration storiche invariati; migration Tassa preesistente/harnessMariaDB/DEPLOY/BladeUI/routes identici agli hash di avvio. PDF e CSV originali conservano gli hash certificati. Inventario tecnico/configurazioni DBoperativo riletti in transazione READONLY e confrontati: invariati. Nessuna migration nuova, dato operativo scritto o asset operativo cancellato.

Otto file della fase:
- app/Services/TassaDiSoggiornoService.php;
- app/Http/Controllers/SchedinaController.php;
- app/Http/Controllers/TassaReportController.php;
- app/Http/Controllers/TassaDiSoggiornoController.php;
- tests/Feature/TassaBellariaEtaCsvTest.php (soltanto dato della fixture positiva);
- tests/Feature/TassaBellariaVersionamentoTest.php (nuovo);
- docs/imposta-soggiorno-bellaria-audit-2026-10-07.md;
- docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md.

Candidato Tassa25file,worktree30(18tracked+12untracked),5preesistenti esterni preservati. mainHEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto. Nessun commit/push/deploy/server/produzione/trasmissione reale. P1 temporale e P2 rollback upload corretti; nessun nuovoP0/P1 dimostrato. Rimangono separati fonti future reali, categorie non certificate, riallineamento legacy autorizzabile, accettazioneStayTour e conservazione asset storici dopo sostituzioni riuscite. La configurazione legacy discordante continua a richiedere gestione separata: nessuna modifica automatica dei record.


## Blocco rapporto e riallineamento esplicito Bellaria — 8 ottobre 2026

BASELINE IN AUDIT. Diagnosi e correzione esclusivamente locali; nessuna approvazione commit, deploy, certificazione normativa o readiness produttiva.

### Causa dimostrata e dati locali

Il messaggio sul periodo documentato nasce da `TassaDiSoggiornoService::validaConfigurazioneBellaria()`, confronto degli estremi con il profilo `bellaria-alberghi-2026-v1`. Il rapporto passa da `TassaReportController::index()` a `buildRows()`, risoluzione automatica e validazione; il controllo usa `buildControlDataset()`. Modello/tabella `TassaDiSoggiorno`/`tassa_di_soggiorno`, sorgente `Struttura`/`struttura` e riferimenti tipologia/classificazione; movimenti `schedina`/`componenti`, storico `TassaExport`/`tassa_exports`. Il resolver già conservava i record discordanti e rifiutava il calcolo, ma non offriva il riallineamento UI. Nella versione precedente di questo candidato l'eccezione interna sul periodo era già avvolta da `configurazione_legacy_discordante`: non si assume che il testo dell'alert identifichi da solo il runtime o il tenant.

Lettura locale con PDO, transazione READ ONLY, senza bootstrap Laravel, valori tecnici soltanto:

| ID struttura | Tariffa / limite / età minore-adulto | Periodo salvato | Discordanze |
|---|---|---|---|
| 1 | 1,50 / 6 / 17-18 | 01/06/2026–01/10/2026 | Fine richiesta 30/09/2026 |
| 9 | 1,50 / 6 / 17-18 | 01/03/2026–01/10/2026 | Inizio richiesto 01/06/2026; fine 30/09/2026 |
| 12 | 1,50 / 6 / 17-18 | 01/03/2026–01/10/2026 | Medesime date discordanti, inoltre classificazione mancante: nessun profilo applicabile |
| 7 | Tutti nulli | Non salvato | Categoria disponibile: derivazione in memoria, nessuna riscrittura |
| 8, 11 | Tutti nulli | Non salvato | Classificazione mancante |
| 10 | Nessun record | Non salvato | Classificazione mancante |

Per ID1/9 categoria Hotel/Alberghiera, classificazione 3 stelle (superior per ID1) coerente con tariffa 1,50. Il profilo certificato contiene giugno–settembre, cap6 e parametri17/18, senza nuove tariffe o esenzioni. 35 Schedine locali, tutte ID1; nessuna nelle altre strutture. Il DB locale non contiene ancora `tassa_exports`; la nuova migration del candidato rimane non applicata. Esiste `struttura_audit_logs` con descrizione varchar255, riutilizzata senza migration. ID selezionato nella segnalazione non confermato dall'utente: nessuna attribuzione certa dell'alert a uno specifico tenant.

Configurazione invalida senza Schedine: rifiuto fiscale intenzionale e riprodotto. Dopo riallineamento valido: rapporto zero e CSV ammessi, senza inventare presenze. Assenza movimenti non significa chiusura della struttura.

### Correzione e procedura UI

Configurazione raggiungibile dalla pagina Tassa, anno esplicito, tabella completa valore salvato/profilo richiesto/differenza. Il rapporto HTML con legacy discordante porta alla configurazione pertinente; JSON/CSV mantengono il rifiuto422. Fonte incompleta o categoria/anno non certificato: nessun comando di riallineamento, correggere prima i Dati struttura quando appropriato.

POST autenticato `tassa_di_soggiorno.riallinea`: solo amministratori, super amministratori, proprietari e utenti struttura con permesso operativo proprietario; tenant già autorizzato dal middleware e token vincolato a utente/tenant/anno. Consenso obbligatorio. Anteprima cifrata autenticata con scadenza15min; hash comprende dati sorgente, record precedente e profilo canonico. Lock struttura/configurazione e ricontrollo sotto transazione: anteprima obsoleta/manomessa/riutilizzata o altro tenant rifiutati409. Il backend non accetta tariffe/date manuali; persiste soltanto i sei parametri mostrati e preserva note/asset. Due registrazioni audit prima/dopo, versione fiscale e attore, nella medesima transazione; failure audit annulla anche il salvataggio. Snapshot/export/ricevute consolidate e Schedine non aggiornati. Le elaborazioni correnti, non consolidate, usano poi la configurazione valida.

Nessun riallineamento dei dati operativi eseguito dall'agente. Azione manuale: selezionare la struttura corretta, aprire Tassa → Configurazione, anno2026, verificare le differenze, spuntare il consenso e confermare “Riallinea al profilo certificato”. Solo ID1/9 hanno attualmente una fonte categoria sufficiente; per ID12 completare prima la classificazione, senza attribuirla automaticamente. La tabella espone anche parametri coincidenti: il fix non consiste soltanto nel cambiare due date.

### Copertura temporale

Il re-audit precedente aveva dimostrato un ulteriore bypass: arrivo31/12/2026, partenza13/06/2027, tariffa2026 riusata sulle notti2027. Ora il service risolve la copertura per ogni notte prima del calcolo, incluso soggiorno fuori stagione; anno senza profilo rifiutato, nessun fallbackzero. Checkout escluso come già previsto. Soggiorno che attraversa versioni diverse bloccato esplicitamente, finché non è disponibile un'elaborazione per segmenti certificata. Il filtro può attraversare anni con movimenti integralmente coperti; selezione vuota valida il contesto iniziale come previsto dal precedente gate. Nessuna regola2027 inventata. Formule età/777/stagione e date operative Questura/ISTAT preservate.

### Verifiche

Suite mirata isolata PHP8.3.33/MySQL8.0.36: **115 test / 2448 asserzioni PASS**. Comprende nuovo flusso legacy, consenso, GET senza scritture, rapporto/CSV zero, ricevuta4,50, snapshot byte-identico, replay/TOCTOU/ruoli/tenant/rollback audit, anno2027/categoria incompleta, copertura soggiorno. Regresso anche configurazione automatica, età/777/esenzioni, periodo/Schedina, report/controlli/rettifiche, Questura OFF e sicurezza ISTAT.

Copia temporanea del candidato e diagnostici indipendenti: **12 test /161 asserzioni PASS**, comprendenti 4 test ripetuti del flusso e8 prove esterne: riconciliazione, storico/tenant, concorrenza2processi, cleanupasset, annualità e cross-stay. Diagnostico cross-stay invariato ora ricevuta/rapporto/CSV/controllo/consolidamento422, export0. Non sommare i test ripetuti come evidenze uniche.

14SOAPoffline,22ISTAToffline,5guardrail PASS; sintassi PHP/Pint4 e JS PASS. Launcher verifica identità e23rifiuti override, buildGEO immutabile. Browser: **6 scenari distinti PASS** su due esecuzioni. Prima 5PASS e nuovo percorso bloccato dall’avviso SweetAlert non chiuso; ripetizione configurazione3PASS, incluso riallineamento reale del fixture sintetico. Il test ora chiude normalmente l’avviso con OK, poi consenso e doppia conferma del design system, senza clic forzati. Le 2 prove configurazione ripetute non sono nuovi scenari.

Tentativi intermedi: nuovo confronto GET corretto per rileggere prima dal DB (cast/colonna nullable); prima suite115 aveva un solo fallimento per etichetta UI “Configurazione legacy conservata” rimossa e poi ripristinata, senza cambiare assertion. Primo avvio indipendente non aveva la copia aggiornata e si è fermato prima dei test; rilanciato dalla copia corretta. Nessuna modifica migration/harness per superare i test.

File di questa fase: service, controller configurazione, controller rapporto, vista configurazione, routes, nuovo test riallineamento, test browser configurazione, fixture browser, rapporto audit e Maestro. Gli altri candidati preesistenti sono preservati. Nessun DB operativo scritto, migration reale, commit/push/deploy/produzione/trasmissione.


Chiusura fase: nuovo test riallineamento **4test/45asserzioni PASS** nella ripetizione browser; non sommare ai115. Risorse effimere rimosse su tutte le esecuzioni finali. Dati operativi tecnici rilette READ ONLY byte-identici alla diagnosi iniziale; nessun riallineamento applicato. 50file Questura/Q3 hashPASS,90percorsi nominali Questura/ISTAT identici aHEAD,147migration storiche e nuovaTassa identiche all’avvio;5file esterni harness/DEPLOY invariati. Inventario della fase10file (uno nuovo), candidatoTassa26/worktree31(18tracked+13untracked). mainHEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,0/0,stagingvuoto; nessun fetch in questa fase. DiffcheckPASS. Correzione locale verificata; normalizzazione dei record richiede la conferma UI dell’utente, non eseguita dall’agente.


## Completamento ricevute, anteprima e periodi fiscali — 8 ottobre 2026

**Verifica funzionale locale; BASELINE IN AUDIT.** Continuazione del riallineamento precedente, senza sovrascriverlo. Nessuna certificazione normativa, accettazione amministrativa, approvazione commit o readiness produttiva.

### Diagnosi dell’anteprima

Il caso reale non ha un ID disponibile: il titolare ha autorizzato la diagnosi autonoma esclusivamente con Schedine sintetiche. Non attribuire con certezza il problema originario a una singola configurazione o a un popup bloccato.

Difetti generali riproducibili: nella configurazione l’unico link era vincolato allo stato fiscale dell’anno della pagina e all’ultima Schedina. Anno pagina2027 non certificato nascondeva il link anche con Schedina2026 perfettamente calcolabile. La scheda del soggiorno esponeva soltanto “Stampa ricevuta”; inoltre il helper risolveva la configurazione derivata localmente ma non la restituiva alla vista, che poteva indicare erroneamente “Configura prima la tassa” pur avendo un dettaglio valido. Nessun vincolo su totale>0 trovato nella route di stampa: lo zero valido era già calcolabile, mentre l’accesso e la motivazione erano incompleti.

Ora GET autenticato `schedina.tassa.anteprima`, medesimo controller/service e tenant scope della stampa; anteprima distinta dal consolidamento, nessuna scrittura fiscale. Dalla configurazione si scelgono le ultime20 Schedine del solo tenant, senza dipendere dall’anno configurazione; le altre sono raggiungibili dall’elenco. Accessi espliciti nell’elenco, tab Tassa della Schedina e storico con export_id. Il documento viene validato per le proprie date. Popup reali aperti con clic normali, documento visibile, bottone stampa e CSS/PDF verificati; nessuna chiamata JS che crei una ricevuta durante la consultazione. Nessun problema persistente di sessione/CSRF o guasto generale ai popup riprodotto.

HTML non calcolabile restituisce422 con motivo, struttura/Schedina/anno e link configurazione/ritorno; nessuna card ricevuta né falso zero. JSON conserva gli errori di validazione. Documento corrente/anteprima/storico distinguibili; tolta la “Data emissione” generata da now(), non sostenuta da una registrazione. Nessuna numerazione ricevuta o attestazione pagamento inventata; N.Schedina e disclaimer esistenti preservati. Snapshot e file storici non riscritti.

### Motore e risultati fiscali

| Caso sintetico | Risultato |
|---|---|
| 10–13maggio2026 e1–4ottobre2026 | Ricevuta/anteprima0, motivo soggiorno fuori dal periodo di applicazione |
| 31maggio–3giugno2026 | 3notti soggiorno,2imponibili,3euro, nessun777 fuori stagione |
| 29settembre–2ottobre2026 | 3notti soggiorno,2imponibili,3euro |
| Minore in giugno | Zero per esenzione, non “fuori periodo” |
| Minore iniziale con superamento limite | Zero, esenzione effettiva e4notti oltre limite esplicite |
| Profilo cap0 esclusivamente sintetico | Zero per limite, anteprima/stampa consentite; NON è una regola Bellaria reale |
| Soggiorno positivo10–13giugno2026 | 4,50euro |
| 31dicembre2026–1gennaio2027 | Solo notte2026: zero valido, checkout escluso |
| 31dicembre2026–3gennaio2027 | Notti2027 senza fonte: rifiuto, nessun export |
| Profili invernali2026/2027 esclusivamente sintetici | Dal28dicembre al7gennaio:4notti a1,50+2a3=12euro,4notti777; un solo cap6 |
| Date mancanti o partenza prima dell’arrivo | Calcolo indisponibile, nessuna ricevuta/export |
| Soggiorno stesso giorno | Zero senza pernottamenti, non “limite raggiunto” |

Notti risolte sul profilo pertinente, anche fuori stagione; nessun profilo2027 reale aggiunto. Contatore unico per persona/soggiorno, non azzerato al cambio anno/versione. Tariffe e classificazioni2026 reali invariate. Se i cap delle versioni differiscono, manca una regola certificata di transizione: rifiuto esplicito, niente limite inventato. Segmenti di tariffa/esenzione conservati nel dettaglio e snapshot; report/CSV usano gli stessi segmenti. Prezzo variabile dichiarato solo quando variano le tariffe delle notti tassate: la tariffa zero di un’esenzione non è una seconda tariffa ordinaria. Ricevuta e controllo mostrano “Variabile” anziché zero fittizio; controllo CSV interno altrettanto. Il report aggregato non presenta un’unica configurazione per più versioni. Storico si legge dal dettaglio salvato senza ricalcolare con fonti correnti.

Motivo zero esplicito e completo, oltre-limite visibile in tabella e, per importozero, in nota. Motivazioni parziali indicano l’esenzione effettiva e l’ordinario. Nessuna esenzione777 personale: resta derivata dalle sole notti oltre limite. Report zero e consolidamento consentiti soltanto su calcoli validi; errore in un movimento annulla l’intero export.

### Prove e limiti

RED prima del fix:6test,5fallimenti, incluso il link nascosto nell’anno2027 (aspettativa permanente invariata). Primo mirato15test/177asserzioni con2fallimenti di preparazione: cast DB nel confronto e rappresentazione CSV dei decimali; corretti leggendo il modello fresco e confrontando i valori numerici, senza cambiare il writer o indebolire quantità/importi.

Suite finale isolata PHP8.3.33/MySQL8.0.36: **123test/2560asserzioni PASS**, tutti115 precedenti più8 regressioni. Copre zero/parziali/positivo, date invalide, copertura, profili sintetici, report/CSV/controllo, snapshot immutabile, transazioni, tenant, riallineamento e Questura/ISTAT.14prove aggiuntive/201PASS durante la fase (6 ripetute e8 esterne), con riconciliazione, storico, concurrency2processi e cleanupasset. Diagnostico cross-stay invariato: ricevuta/rapporto/CSV/controllo/consolidamento422, export0. Non sommare le ripetizioni come prove uniche.

Browser7scenari PASS nella batteria fiscale conclusiva: configurazione2ruoli, riallineamento,3modalità ricevuta e scenario completo anteprime. Quest’ultimo apre7documenti (prima/dopo/esente/limite/positivo/parziale/checkout), poi elenco, scheda Schedina e storicozero; rifiuto2027 verificato con card assente e link correzione. JSwindow.print invocato sotto spy, media print e PDF Chromium effettivi; nessun lavoro su stampante fisica. La prima prova si fermava sull’attesa DOM del popup dalla Schedina; aggiunta attesa domcontentloaded e verifica href, poi documentato il caricamento valido e rimossa la diagnostica temporanea.

Verifica PDF con Poppler e pypdf: tuttiA4, importi/footer presenti; confronto visivo di zero, esenzione, limite, parziale, positivo e checkout. Prima impaginazione con nuove note ripetute produceva un footer orfano: note positive ridotte al conteggio già in tabella, motivazioni zero conservate. La verifica del gruppo ha evidenziato anche la fragilità del posizionamento assoluto in stampa: CSS stampabile in flusso normale, elementi gestionali esclusi. Verifica conclusiva dopo la correzione del flusso: 7 prove browser PASS; 7 PDF anteprima di una pagina, ricevute senza/con immagine di una pagina e gruppo di due pagine. Controllo visivo delle anteprime zero e positiva, della ricevuta con immagine e di entrambe le pagine del gruppo: intestazione, righe, totale e footer leggibili, senza tagli o footer orfani. Risorse isolate fermate e rimosse.

14SOAPoffline,22ISTAToffline,5guardrail PASS; identità/23rifiuti override/buildGEO immutabile e cleanup verificati dal launcher. Sintassi PHP/JS e Pint4 PASS; SchedinaController mantiene il debito di stile baseline senza formattazioni diffuse. Un precedente run browser7PASS/exit0 ha emesso IncompleteRead del proxy alla chiusura, dopo i risultati e cleanup: non modificato il harness e non assunto come errore fiscale.

### Inventario e operazioni

17file della fase (3nuovi):

- app/Http/Controllers/SchedinaController.php
- app/Http/Controllers/TassaDiSoggiornoController.php
- app/Http/Controllers/TassaReportController.php
- app/Services/TassaDiSoggiornoService.php
- resources/views/schedina/list.blade.php
- resources/views/schedina/partials/form.blade.php
- resources/views/schedina/print-tassa.blade.php
- resources/views/tassa_di_soggiorno/edit.blade.php
- resources/views/tassa_di_soggiorno/rapporto.blade.php
- resources/views/tassa_di_soggiorno/rapporto-controllo.blade.php
- resources/views/tassa_di_soggiorno/rapporto-print.blade.php
- routes/web.php
- tests/Feature/TassaBellariaAnteprimaTest.php (nuovo)
- tests/Feature/tassa-bellaria-anteprima.playwright.spec.js (nuovo)
- tests/Isolation/tassa-anteprima-fixtures.php (nuovo)
- docs/imposta-soggiorno-bellaria-audit-2026-10-07.md
- docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md

Dati operativi non usati per test e non modificati; configurazioniID1/9/12 e riallineamento precedente preservati. Nessuna migration operativa eseguita. Baseline locale precedente: nuova migrationTassa non ancora applicata, prerequisito da gestire separatamente prima dell’uso operativo del consolidamento. Per ID1/9 resta la conferma UI autorizzata, per ID12 completare prima la classificazione effettiva. Nessun automatismo sui dati legacy.

Categorie non certificate, fonti2027, transizione fra cap diversi, accettazione StayTour del CSVheader-only e productionreadiness restano limiti/gate separati. Non sono bypassati. Se una Schedina reale continua a non aprire l’anteprima dopo configurazione valida, occorrono ID tecnico struttura/Schedina, pagina/pulsante, URL ed errore/status HTTP; nessun nome ospite, credenziale, cookie o token.

HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,main,0/0,stagingvuoto; nessunfetch. CandidatoTassa29file, worktree34(18tracked+16untracked),5file esterni preesistenti preservati.50Questura/Q3 hashPASS, percorsi ISTAT e147migration storiche/nuovaTassa/harness/DEPLOY invariati rispetto avvio. Nessun commit,push,deploy,produzione o trasmissione reale.


## Correzione selezione struttura e chiusura re-audit browser — 8 ottobre 2026

BASELINE IN AUDIT. P1 preesistente in HEAD: GET `/strutture/seleziona` richiamava `layouts.app`, inesistente. La vista usa ora il layout ufficiale `layouts.master`, breadcrumb e card del gestionale. Form, CSRF, route, controller, autorizzazioni e sessione non sono stati modificati. L'avviso di successo è gestito dal layout ufficiale.

P2: su schermi fino a600px la tabella ricevuta mantiene lo scorrimento nel contenitore, larghezza minima760px e colonna motivo almeno160px, senza spezzare arbitrariamente le parole. Regola soltanto screen: stampa A4 invariata. Pint applicato esclusivamente a `SchedinaController.php` e `routes/web.php`: token eseguibili equivalenti dopo esclusione whitespace/commenti/import e normalizzazione delle parentesi vuote di `new`; import riordinati, solo `Storage` inutilizzato rimosso dalle route. Nessuna modifica semantica Questura/ISTAT.

Prove effettive isolate:148test PHP/2670asserzioni PASS (123Tassa/Schedine/sicurezza più25selezione/autorizzazioni). Ripetizione mirata dei3nuovi test PASS. Browser:7scenari Tassa PASS e5nuovi selezione/percorso completo PASS,12distinti. Proprietario con una/due/nessuna struttura; scelta, cambio, sessione dopo reload; reception reindirizzata; esclusione strutture di altri proprietari; ricevuta del tenant precedente404 e export non visibile dopo cambio. Percorso Login → Selezione → Dashboard → Schedine → Tassa → Anteprima zero/positiva → Stampa → Rapporto → CSV/consolidamento → Storico verificato. Nessun500 osservato nei percorsi positivi. Anteprime senza registrazione/consolidamento dimostrate dai test PHP; gli export creati sono soltanto sintetici nel DB effimero.

14SOAPoffline/22ISTAToffline/5guardrail PASS; Pint16file PASS; lint205PHP più2viste,4Python,4JavaScript PASS; diffcheck PASS. PDF12documenti/13pagine con testo estraibile, zero/positivo una pagina e gruppo due; zero,positivo,gruppo e mobile controllati visivamente. Evidenze esterne: `/private/tmp/ids-selezione-regressione.log`, `/private/tmp/ids-selezione-browser-finale.log`, PDF/screenshot `ids-selezione-*`. Primo test nuovo fermato da selettore ambiguo fra topbar e tabella; secondo da filtro relativo errato del test. Corretti soltanto i selettori, applicazione mostrava già la struttura corrente;5nuove prove ripetute integralmente PASS. Tutti i runtime effimeri fermati/rimossi.

Rispetto all'inizio fase:9file modificati/creati (2viste,2file soloformattazione,3test/fixture,2documenti).233file protetti Questura/ISTAT/migration/harness/DEPLOY byte-identici allo stato iniziale. Modifiche e untracked preesistenti preservati. HEAD/origin locale faef3cf01f43d19b20d1558c1fbd3ae32e48d849,main,0/0,stagingvuoto; nessunfetch. Nessun DB operativo usato o modificato, nessun commit/push/deploy/server/trasmissione reale. Nessun nuovo P0/P1 nel perimetro verificato. Nessuna certificazione normativa o readiness produttiva; limiti documentali e gate operativi precedenti restano aperti.

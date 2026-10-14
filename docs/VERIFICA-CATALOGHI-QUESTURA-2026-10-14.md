# Verifica congiunta Tipo alloggiato e Tipo documento — 14 ottobre 2026

**Dati ufficiali confermati per entrambi i cataloghi. Correzioni e verifiche locali PASS; rilascio e conferma finale pendenti. BASELINE IN AUDIT.** La richiesta allegata e la chiusura obbligatoria autorizzano esclusivamente difetti dimostrati dei formulari/protezioni, test, documentazione, commit, push e pubblicazione tramite C5. Nessuna modifica autorizzata di codici, mapping o logiche Questura; nessun invio reale.

## Baseline e scope

Baseline locale/GitHub/produzione: `c9d8427a83c53dfb44e3ed0134bdbfaa15540b2e`, ultima chiusura Tipo cliente attestata dalla ricevuta operativa. Produzione Git pulito, 154 migrazioni/64 tabelle. Quattro documenti con modifiche preesistenti, incluso il Maestro, preservati ed esclusi dallo staging selettivo. Nessuna nuova infrastruttura o modifica a dati personali reali.

I cataloghi sono comuni e informativi; la loro consultazione non dipende dalla struttura. Tabelle/modelli/controller: tipo_alloggiato/TipoAlloggiato/TipoAlloggiatoController e tipo_documento/TipoDocumento/TipoDocumentoController, rotte resource omonime e due viste index. La colonna locked indica il blocco, non uno stato attivo/inattivo. Le azioni esistenti nelle viste sono disabilitate; non vengono introdotte nuove azioni o conferme. Ricerca per q, ordine ID crescente e pagine di dieci elementi; filtri e ordinamenti interattivi aggiuntivi non sono previsti. Dettaglio, creazione e modifica non sono funzioni disponibili.

## Fonti ufficiali e confronto integrale

Consultazione e acquisizione il 14 ottobre 2026, senza credenziali, dall'[area pubblica Tabelle Alloggiati Web](https://alloggiatiweb.poliziadistato.it/portalealloggiati/tabelle.aspx). Download diretti: [Tipo alloggiato](https://alloggiatiweb.poliziadistato.it/portalealloggiati/ashx/Download.ashx?ID=3&N=TIPO_ALLOGGIATO) e [Documenti](https://alloggiatiweb.poliziadistato.it/portalealloggiati/ashx/Download.ashx?ID=2&N=DOCUMENTI). Il lettore web non gestiva application/octet-stream; acquisizione HTTPS diretta completata, originali e SHA custoditi. Nessuna fonte terza o seeder assunto come prova ufficiale. I file non espongono un numero di versione: si registrano data e digest, identici agli snapshot ufficiali acquisiti il 6 ottobre 2026.

| Catalogo | Ufficiali | Produzione | CSV usato dal seeder | Confronto |
|---|---:|---:|---:|---|
| Tipo alloggiato | 5 | 5 | 5 | Tutti i codici e tutte le descrizioni identici |
| Tipo documento | 95 | 95 | 95 | Tutti i codici e tutte le descrizioni identici |

Nessun mancante, extra, duplicato o descrizione incongruente; tutti i record produttivi locked=1. SHA ufficiali: tipi `3cf46914cf9806e9b0d2307c27bfcf67f849de1432c65ff7e8603cd74b69f4cd`; documenti `b411088e0c5baf01c16cd64ca32c427258dde74eb9cdf13455dff904e1a00663`. I CSV in reference/libreria differiscono soltanto nella rappresentazione delle terminazioni di riga: coppie codice/descrizione identiche. Nessun dato ufficiale corretto, rinumerato o reinterpretato. La ricognizione in sola lettura non trova FK verso questi cataloghi né elementi TipoDocumento nel Cestino produttivo.

La [guida Alloggiati Web](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEALBERGHI.pdf), pp.31–34, e il [Manuale WS](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Download/Manuali/MANUALEWS.pdf), Rev.01 del24/01/2022, pp.19–20, sono stati consultati per il tracciato standard: 168 caratteri, codice documento di cinque caratteri da offset134, terminatori CRLF tra le righe. Per i tipi16/17/18 sono richiesti i campi documento; per19/20 i relativi34caratteri devono essere spazi. Il file unico appartamenti174caratteri e l'accettazione reale del servizio restano distinti da questa verifica.

## Difetti riprodotti e correzioni minime

Prima prova isolata:20test/119asserzioni,6FAIL. Tipo documento validava richieste invalide prima del diniego (422 JSON), il dettaglio diretto generava500 per show assente, e il POST di ripristino dal Cestino di un item sintetico riceveva302 anziché403; il relativo ramo restoreSimpleModel consentiva l'inserimento. Nessun item produttivo creato o ripristinato.

Corretto authorize di TipoDocumentoRequest a false, aggiunto show con403, vietate archiviazione/ripristino dei due cataloghi nel CestinoService e mutate del relativo item in CestinoController. Gli snapshot preesistenti rimangono consultabili e conservati, senza ripristino o eliminazione attraverso l'applicazione. Il controllo riguarda esclusivamente le due classi; le altre entità del Cestino mantengono il comportamento precedente.

Una seconda diagnosi GET q[]=forgiato:2test/2asserzioni,2FAIL con500. Entrambi i controller ora rifiutano la ricerca non scalare con400 prima della query. Ricerca ordinaria e regole di mapping invariate.

Browser reale e isolato riproducono overflow mobile: viewport390px, pagina401px per il gruppo ricerca largo360px. Due sole viste corrette con regola CSS nel contesto questura-catalogo, limitata al breakpoint mobile; pagina390px verificata, tabella scorre nel proprio contenitore. Componente condiviso, altri formulari, azioni e tracciato invariati.

Pint aveva riformattato parti preesistenti dei quattro file applicativi: tali variazioni estranee sono state escluse, conservando soltanto gli interventi necessari. I nuovi test sono formattati e lint/diff verificati. I primi spec browser avevano letture anticipate delle pagine e locatori CSRF inesistenti per questo layout: diagnostici conservati, corretti soltanto le nuove prove e le attese, senza ridurre asserzioni o protezioni.

## Coerenza e inizializzazione

DatabaseSeeder chiama ConfigurazioneSistemaSeeder, che richiama i due seeders effettivi. Prove su schema fresco effimero e seed ripetuti: cataloghi5/95 conformi, ID e metadati già conformi invariati, clienti/schedine/relazioni ed extra sintetico conservati. Nessun seed o reseeding eseguito sui dati produttivi. Gli ID interni sono distinti dai codici Questura; non se ne impone una rinumerazione tra installazioni.

I campi cliente usano le descrizioni del catalogo documento; il TXT conserva il codice ufficiale corrispondente. Prova esaustiva: tutti95documenti sia per codice sia per descrizione generano lo stesso codice ufficiale all'offset134, in righe168caratteri. ID numerico del record, codice estraneo e documento mancante sono rifiutati per il principale. Tipi16/17/18 per il principale e19/20 per i componenti, relazioni17→19 e18→20 e spazi documenti dei componenti comprovati. Nessun obbligo universale inventato e nessuna equivalenza automatica con le tre classificazioni Tipo cliente. QuesturaTxtExportService, QuesturaWebService, Catalogo e TipoAlloggiatoCatalogo, seeders, CSV e migrazioni restano byte-identici alla baseline.

## Verifiche e rilascio

Test Laravel/Chromium esclusivamente nella VM ufficiale, namespace senza rete e database/processi/checkout effimeri attestati, con dati sintetici e SOAP double. Guardie15PASS; build/GEO e23rifiuti di overridePASS. Prova mirata dopo le prime correzioni:74test/1.003asserzioniPASS, incluse regressioni QuesturaBoundary e QuesturaPayloadCoherence originali. Prova definitiva: 76 test/1.005 asserzioni PASS. Chromium: due prove PASS in 1,2 minuti, tutte le dieci pagine documenti, cinque alloggiati, ricerca/reset/vuoto, 32 richieste mutative con CSRF reale bloccate per quattro ruoli, Cestino senza ripristino, selettori cliente/schedina e desktop/mobile. Larghezza pagina uguale al viewport 1440/390 px; tabelle scorrono nel proprio contenitore.

Browser online sulla baseline: Tipo alloggiato5righe; Tipo documento tutte10pagine/95IDe95codicidistinti, descrizioni ufficiali identiche. Limiti tecnici di alcuni comandi CUA conservati, pagine completate tramite i collegamenti effettivamente visibili; non attribuiti al server. Verifica della versione corretta, dinieghi GET e mobile dopo C5 ancora pendenti. Richieste POST/PUT/PATCH/DELETE dei quattro ruoli e CSRF sono provati in isolamento sul codice che verrà distribuito; non si usano record personali reali per tentativi mutativi produttivi.

C5 vigente: nuova composizione, piano e sigilli; prebuild da lock e checkpoint cifrato recuperato indipendentemente prima del RUN; S0fresco nella finestra1800/S0entro600/cutoff1379/riserva421, guardiano indipendente e recupero coordinato. Zero migrazioni/DDL/DML autorizzate per questa task. Dopo rilascio: conservazione DB/config/chiave/upload, browser online, monitor15minuti e SHA; quindi consuntivo/Maestro e loro allineamento con nuova C5 documentale. Nessuna approvazione vecchia riutilizzata. Nessun PASS produttivo anticipato.

File applicativi modificati: TipoAlloggiatoController, TipoDocumentoController, TipoDocumentoRequest, CestinoController, CestinoService e le due viste index. Dipendenze indispensabili: allowlist esplicita dei due seeders in tests/Isolation/run.py; tre test PHP, uno spec Playwright e fixture isolata nuovi. Il template operativo preesistente conserva il medesimo aggiornamento selettivo senza includere il resto del suo contenuto estraneo nel commit.

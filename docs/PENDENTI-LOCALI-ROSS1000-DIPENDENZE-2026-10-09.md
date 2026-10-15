# Pendenti locali: Ross1000 e dipendenze - 9 ottobre 2026

**Accettazione locale positiva preservata; nessuna modifica applicativa o dei lock. BASELINE IN AUDIT — NON VALIDATA. Nessuna validazione produttiva.**

## Ross1000

Non trovata una precedente attribuzione/correzione documentata dello specifico500 dall'ingresso ConfigurazioneRoss1000 della struttura tanggo. Le65proveISTAT/globali precedenti non sostituiscono la riproduzione del caso reale.

Nuovo percorso `/struttura` → link «Configurazione Ross1000» → `/istat-tabella-a`: **HTTP1test/9asserzioniPASS; Chromium3/3PASS**. Tre profili sintetici operativamente autorizzati: struttura incompleta senza codice/capacita, struttura coerente senza soggiorni, soggiorno incompleto. Pulsante visibile e URLcorretto, pagina200, campi credenziali vuoti, invio diretto disabilitato. Nessuna credenzialeRoss1000 o trasmissione. Il profilo sintetico non e una copia dei dati o dei ruoli reali, non consultati.

**Anomalia non riprodotta nel campione locale; nessuna patch senza causa.** Per il500 originario servono SHA/schema/migration, ruolo effettivo e dettaglio sanitizzato dell'eccezione. Il controller legge istat_transmission_events: schema incompleto e una condizione da verificare in B3, non causa confermata; nessun fallback per mascherarla.

Rossi preservati: nuovo diagnostico iniziale puntava alla route inesistente `/struttura/{id}/edit` (404); nuovo supporto browser iniziale senza bootstrap (trait non trovato). Corretti solo URL e bootstrap del nuovo diagnostico. Dopo i3verdi browser il proxy registra2corpi incompleti e BrokenPipe con consumer non piu disponibile; log integrale preservato, non conteggiati come500 della pagina ne occultati. Nessuna modifica proxy.

## Composer

[Matrice individuale dei46avvisi](composer-classificazione-46-2026-10-09.json): ID, versione/range, uso diretto/transitivo, produzione/sviluppo, catena, esposizione, prova, rimedio e criterio di blocco. Tutti i12pacchetti sono in produzione:16avvisi su2diretti,30su10transitivi. PsySH e produzione via tinker, non dev-only.

Mitigazioni nuove **2test/11asserzioniPASS**: flagfalse ISTAT/Questura impediscono trasporto anche senza credenziali; HTTP404 pubblico con debugfalse senza trace/vendor; sessionelangostile non modifica localeit; NoPrivateNetworkHttpClient non presente nel runtime, symfony/http-client assente dal lock; mailarray e nessun invio. Valori verificati nel runtime isolato, non FPM/server. Nel mirato precedente64casi,62regressioniISTAT e controllo trasportoPASS; unico rosso nel nuovo test che assumeva404 su catch-all autenticato (302login corretto). Ripetuti solo2nuovi casi sulla routepubblica appropriata.

Guzzle/PSR7: unico client applicativo diretto individuato e ISTAT, URLcostante, redirectdisabilitati, nessun cookiejar applicativo. Provider spento e mitigazione verificata del primo perimetro; proxy/egress e attivazione ancora gate. Carbon: localeit fissato anche con input sessione ostile, non blocca quel percorso. HTTPfoundation: advisoryNoPrivateNetworkHttpClient non applicabile al candidato; **non esclude PATH_INFO**, ancora da provare/mitigare. DebugLaravel: mitigazione locale verificata, APP_DEBUGfalse effettivo da attestare sul server.

Restano pertinenti upload/normalizzazioneFlysystem e validazioneLaravel, email/reset/Mime/Mailer, routing/URL e PATH_INFO. Markdown/Commonmark e condizionale anche nei rendering mail indiretti; nessun uso diretto app/routes trovato, non esclusione globale. PsySH richiede verifica CWD/console/permessi sul destinatario. IDN riguarda domini nei flussi mail/HTTP. Il flagenti non disabilita SMTP; email non attivabile senza rimedio o mitigazione provata. Nessun pacchetto aggiornato in massa o avviso cancellato per chiudere il gate.

## npm

**Valutazione completata.** npm audit finale: **62dipendenze affette,2critiche/18alte/41moderate/1bassa**, nessun erroreJSON, exit1 per advisories. [Rapporto integrale](evidenze/pendenti-locali-2026-10-09/npm-audit-cache.json), [classificazione](npm-valutazione-locale-2026-10-09.json).

DNSsandbox e timeout40s/30s precedenti preservati. La diagnostica del secondo tentativo mostra fetch200 e calcolo meta-vulnerabilita ancora in corso; riusando la cache privata con identico lock il report termina entro60s. Non erano prova di nuove vulnerabilita o auditverde.

Controllo indipendente endpoint ufficiale bulk:391nomi/versioni, HTTP200,105advisory/versioni corrispondenti con semver npm,30pacchetti. I105 e i62 misurano cose diverse, non si sommano. Ambito dev/prod dal lock; asset copiati nelbuild non esclusi perche provenienti da node_modules.

Critici: form-data4.0.0transitivo non copiato browser; verificare consumerNode/axios/boundary o fixmirato>=4.0.4. Swiper11.1.14diretto copiato: prototypepollution richiede input/condizione sulprototype, non dimostra exploit della pagina. Rimedio upstream12.1.2; npm propone14.3.0, non applicato automaticamente: prima compatibilita o dimostrazione dell'esclusione di feature/input. [Advisory del maintainer](https://github.com/nolimits4web/swiper/security/advisories/GHSA-hmx5-qpq5-p643).

Altri asset copiati affetti: ECharts5.5.1(XSS), Moment2.24.0(locale/regex), Prism1.29.0(DOMclobbering), Quill1.3.7(XSS), SweetAlert2 11.14.4(comportamento indesiderato). SweetAlert e importato nelbundle comune: nel dist effettivo il comportamento indicato richiede locale russo eTLDru/su/by/xn--p1ai; il dominio previsto`.software` non corrisponde. Valutazione statica del prerequisito, non pacchetto corretto ne certezza su qualsiasi dominio; hostname destinatario da attestare. Possibile patchmirata>=11.22.4. Altri asset: prove input/uso o patch prima dell'esposizione della rispettiva feature. Nessun auditfixforce.

## SSH e blocchi rimasti

Host utilizzato: **schedinedinotifica.tanggo.software**, porta **22**, utente documentato tanggosoftware. BatchMode=yes, StrictHostKeyChecking=yes, ConnectTimeout=8. Fuori sandbox: **`ssh: connect to host schedinedinotifica.tanggo.software port 22: Connection refused`**, exit255. Il primo DNSera sandbox; nessuna porta alternativa provata o configurazione server cambiata. Non ripetuto identico tentativo senza cambiamento dell'accesso.

B1: review del candidato e futuro commit/pubblicazione separatamente autorizzati. B2: CLI/FPM/MariaDB/segfault e attribuzione500non riprodotto richiedono accesso. B3: pending/schema e recuperabilita destinatario; quota sintetica gia verde. B4: rimedi/mitigazioni applicabili Composer/npm e confine pubblico effettivo. **Il pendente npm non completato e chiuso**, restano gli avvisi effettivi. V5legacy/V6provider-mail/V7fiscalita sono gate di attivazione separati.

Nessuna globale ripetuta: applicazione e lock immutati, aggiunti soloAudit espliciti. Accettazione750/8252 e Chromiumtoken9/9 conservate nel perimetro originario. Nessun commit/push/deploy, DBoperativo, credenziale reale, configurazioneserver o trasmissione.

## Gruppi — 13 ottobre 2026

Registro unico dei residui della [verifica Gruppi](VERIFICA-GRUPPI-2026-10-13.md). Rilascio applicativo C5 e controlli online conclusi; nessun blocco di autorizzazione.

- **Revisione di classificazioni preesistenti negli import:**20righe della struttura12, già `needs_review` e non importate, con gruppo I senza corrispondenza nel catalogo globale. ID: 865, 1048, 1573, 1844, 1845, 2466, 2762, 2766, 3124, 3125, 3823, 3880, 5429, 5430, 5439, 5440, 6456, 6457, 6528, 10306. Normalizzati/raw/status intatti, nessuna categoria inventata o associazione arbitraria. Occorre una decisione sui valori nel normale flusso di revisione; non attribuire automaticamente un gruppo. Clienti/schedine e relativi archivi non presentano classificazioni irrisolte o SID discordanti.
- **Apertura dei record preesistenti nel browser online:**14clienti e35schedine appartengono alla struttura1 Hotel K2, già eliminata logicamente prima della task e quindi esclusa dagli accessi correnti. Coerenza e conservazione SQL verificate; edit dei medesimi record nel browser NON eseguito. Nessun ripristino, riattivazione o bypass per effettuare prove. CRUD/associazioni/modifiche/cancellazioni reali eseguiti con Chromium soltanto sul DB isolato; cataloghi e cascata dei formulari Cliente/Schedina verificati online in una struttura attiva.

Originali globali137 con20gerarchie incoerenti e2omonimie preesistenti conservati, esclusi dal catalogo operativo; base madre ufficiale135 e cataloghi privati coerenti. Nessuna nuova gestione della madre. Dettagli di implementazione/test/rilascio e file modificati nel rapporto, senza duplicarli qui. Le interruzioni preparatorie e il primo STOP C5 restano diagnostici storici, non rilasci riusciti; il successivo rilascio è verificato.

## Titoli — limite di provenienza del 14 ottobre 2026

Originali globali ID1 `Sig.s`/Signore e ID4 `Sigg.`/Signori: assenti dalla base80verificata, creazione DB4marzo2026 06:50:15 e aggiornamenti06:50:51/06:52:17. Nessun audit Titoli disponibile, nessun evento Git sulle stringhe e nessun riferimento corrente/archiviato attribuibile. Autore/contesto originario indeterminati. **Conservazione autorizzata:** originali intatti e protetti, senza assegnazione arbitraria a strutture né inserimento nella base ufficiale. Nessuna cancellazione necessaria; eventuale futura classificazione richiede nuove evidenze. Riferimento: [consuntivo Titoli](CORREZIONE-TITOLI-2026-10-14.md). Non è prova di record fittizi o difettosi. Il rilascio Titoli verificato sul codice `230ec2e` mantiene questa distinzione: base ufficiale 80, extra protetti globali e nessuna assegnazione inventata. Il limite storico resta aperto senza un difetto funzionale residuo del formulario.

## Tipo cliente — valore cliente legacy del 14 ottobre 2026

Ricognizione readonly sulla baseline dd847206: catalogo esattamente3codici ufficiali attivi, senza extra o riferimenti FK. In clienti.type_housed un valore vuoto preesistente, conservato; altre classificazioni11Ospite/1Componente/1Richiesta. Nessuna evidenza autorizza a dedurre la classificazione mancante: eventuale completamento del singolo cliente richiede i suoi dati e il flusso ordinario, fuori dall'immutabilità del catalogo. Nessuna riclassificazione automatica o dato personale pubblicato; limite non equivale a errore del nuovo catalogo. [Consuntivo](CORREZIONE-TIPO-CLIENTE-2026-10-14.md).

### Ambiente quotidiano — differenza pregressa rilevata il 14 ottobre durante i tre formulari

La VM quotidiana era a152migrazioni, produzione a154: non risultavano applicate le due migrazioni pregresse Tipo cliente del14ottobre. La sola migrazione autorizzata Rilasciato da/Tipo Via porta il quotidiano a153 e il candidato produttivo a155; i dati precedenti sono conservati. Non attribuire questa differenza ai tre formulari né eseguire migrazioni di altri moduli per azzerarla implicitamente. Chiusura: riallineamento delle due migrazioni nel pertinente scope Tipo cliente, con checkpoint e confronto conservativo; fino ad allora distinguere SHA del codice allineabile dalla differenza preesistente del DB quotidiano. [Evidenze e consuntivo](VERIFICA-TRE-CONFIGURAZIONI-2026-10-14.md).


**Riconciliazione del 15 ottobre 2026 — due pendenze database RISOLTE individualmente:** `2026_02_28_000000_prepare_classificazioni_index_for_normalize` e `2026_10_14_030000_initialize_tipo_cliente_sistema` già registrate in produzione (batch65), eseguite singolarmente tramite Laravel nella VM con checkpoint cifrato riletto. VM153→155; schema e tutte le colonne delle25.875righe precedenti identici, solo due registrazioni aggiunte. Nessuna modifica applicativa o riclassificazione del cliente vuoto. Allineamento del commit conclusivo distinto e attestabile soltanto dalla ricevuta esterna C5. [Consuntivo e posizione della ricevuta](RICONCILIAZIONE-MIGRAZIONI-TIPO-CLIENTE-2026-10-15.md). Il precedente paragrafo conserva la causa e lo stato storico, senza una seconda lista di pendenti.

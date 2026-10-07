# ISTAT Emilia-Romagna / Ross1000 — verifica locale del 07/10/2026

**ISTAT CORRETTO — PRONTO PER RE-AUDIT.** Progetto **BASELINE IN AUDIT**. Trasporto disabilitato di default. Nessuna accettazione reale, nessuna certificazione di produzione. Questo rapporto supera l’audit iniziale parziale: il seguito della richiesta ha confermato Emilia-Romagna e autorizzato i blocchi applicativi ISTAT. Questura C1 resta fuori scope.

## A. Baseline

Repository `/Users/jorgeluccitelli/Herd/Schedinedinotifica`; main, HEAD e origin/main dopo il fetch iniziale `7cdfd6e1cf84cbb0711faf3f98b8bb78007c23fd`, ahead/behind 0/0. Worktree inizialmente pulito, staging vuoto. Nessun nuovo commit, push, deploy o accesso SPanel. Nessun Laravel, browser o migration sul DB operativo: tutte le prove passano dal launcher `tests/Isolation/run.py`.

## B. Fonti ufficiali

## Fonti ricontrollate il07/10/2026

| Ente / documento | Versione/data | URL | Evidenza |
|---|---|---|---|
| Regione Emilia-Romagna, Ross1000/manuali | pagina aggiornata12/06/2025 | https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo | canale regionale; SOAP, uploadXML/TXT, portale; XML-WS2.4 ancora indicato |
| Regione Emilia-Romagna, tracciatoXML-WS | 2.4,29/09/2021 | https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/tracciatoxml-webservice.pdf/@@download/file | BasicAuth, movimentazione e rettifiche; non prova accettazione remota |
| Regione Emilia-Romagna, FAQ | nessuna versione numerica dichiarata | https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/faq-rilevazioni-turismo | upload soloXML/TXT; esito da controllare; reimport dati corretti; mesi consolidati richiedono ufficio; storico portale |
| Regione Emilia-Romagna, accesso/deleghe | v2,29/08/2023 | https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/manuale-ross1000-accesso-e-creazione-deleghe.pdf/@@download/file | SPID/CIE/CNS portale distinti dalle credenzialiWS |
| Regione Emilia-Romagna, WSDL pubblico | contratto consultato07/10/2026 | https://datiturismo.regione.emilia-romagna.it/ws/checkinV2?wsdl | letturaGET pubblica senza autenticazione; SHA2565ee868bda0ffcd34c5eff41bbc9bf0c398903d93a0407f00ec6c1804d47bc309 identico allo snapshot locale |
| Fornitore Ross1000, tracciatoXML | v3,18/03/2026 | https://www.ross1000.it/source/tracciato-xml.pdf | fonte tecnica primaria del fornitore; distinta dalla versione2.4 ancora collegata dalla Regione, adozione regionale non presunta |

La versione demo citata dalla Regione è un portale di prova, non evidenza di uno specifico ambienteWS di collaudo. Nessun accesso alla demo eseguito. Manuale importazione v1 del 28/03/2022 recuperato dal sito regionale e letto: upload, esiti, ordine cronologico, calendario e correzioni sul portale. Indicazioni email assistenza non equivalgono ad autorizzazione a consegnare file statistici via email. Non inventati CSV importabile, endpoint, codici o receiptAPI.


Fonti aggiuntive rilette:

- Regione Emilia-Romagna, [manuale importazione](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/manuale-ross1000-importazione-file.pdf/@@download/file), v1, 28/03/2022: fallback, storico e correzioni. PDF pubblico di 21 pagine; nessun accesso al portale.
- ISTAT, [rilevazione Movimento dei clienti](https://www.istat.it/informazioni-sulla-rilevazione/movimento-dei-clienti-negli-esercizi-ricettivi/), riferimento 2026, pubblicazione 03/02/2026: contesto statistico nazionale. [Definizioni istituzionali](https://noi-italia.istat.it/pagina.php?action=show&categoria=8&id=3): arrivi e notti; residenza distinta dalla cittadinanza. Nessun XML nazionale universale inventato.

Canale implementato: XML regionale validato, utilizzato sia nel SOAP `inviaMovimentazione` sia nell’upload manuale. Nessun CSV/TXT alternativo aggiunto: XML copre anche il calendario; i CSV esterni sono soltanto riferimenti aggregati. Nessun invio email previsto dalle fonti consultate; l’indirizzo di assistenza non viene presentato come destinatario dei file.

## C. Architettura precedente

Controller → service Tabella A → Schedine/Componenti → codifiche → XML/XSD → export/storage → WebService → proiezione esito/storico. Presenti validazione nominativa, statistiche e PDF locale non ufficiale. Mancavano protezione delle credenziali ISTAT, guardrail globale, prenotazioni/idempotenza, parser correlato, registrazione manuale e lifecycle delle copie. GET download mutava il DB. Simulazione interna esposta nel form generale.

## D. Difetti e interventi

| Priorità | Difetto | Risoluzione locale |
|---|---|---|
| P1 | Password in chiaro e riproposta dal form; serializzazione e flash non adeguati | Cast encrypted e hidden su username/password; configurazione dedicata senza ripopolamento né flash; migration nuova di conversione |
| P1 | Invio reale senza interruttore globale, endpoint personalizzabile | `ISTAT_WS_ENABLED=false`; ammissione solo con booleano true, regione ER ed endpoint istituzionale fisso; redirect HTTP bloccati |
| P1 | Doppio invio, concorrenza e retry incerto | Lock struttura, chiave univoca di operazione e vincolo struttura/giorno; pending persistito prima del trasporto; nessun retry di pending/uncertain/partial/processed |
| P1 | HTTP 200/pattern testuale usati come esito | Parser SOAP/XSD con correlazione completa degli identificatori; testi remoti scartati; positivo record distinto dall’accettazione del calendario |
| P1 | Copie XML personali non protette e senza lifecycle | Nuove copie cifrate, hash e snapshot minimizzato; comando retention conservativo |
| P1 | Variazioni dopo comunicazione non gestite integralmente | Fingerprint del soggiorno/ospiti; blocco e registrazione esplicita della verifica/rettifica sul portale; controllo anche dell’ospite comunicato nel mese precedente |
| P2 | Simulazione operativa, GET mutante, fallback non registrato, sequenza cronologica assente | Rimossi flag/form operativi; POST con CSRF; consegna manuale registrabile; controllo dei periodi successivi già comunicati |
| P2 | Barra ricerca ISTAT oltre la colonna mobile | Correzione CSS circoscritta alla vista ISTAT; componenti condivisi invariati |

Nessun P0 dimostrato nello scope. I difetti P1 elencati sono ISTAT; nessuna correzione Questura. I problemi della suite globale descritti in K sono preesistenti e non sono stati sanati fuori scope.

## E. Pipeline finale

```text
STRUTTURA autorizzata (StrutturaAccess)
 → SCHEDINE + OSPITE PRINCIPALE + COMPONENTI del tenant
 → IstatTabellaAService / IstatCodifiche
 → analisi e validazione → XML unico / XSD
 → anteprima: hash degli stessi byte
 → IstatPayloadStore: copia cifrata, hash e fingerprint
 → IstatOperationService: prenotazione e storico atomici
   → IstatWebService: guardrail → SOAP → Http::withBasicAuth
   → oppure upload XML sul portale + dichiarazione manuale
 → IstatResponseParser → EsitoTrasmissioneIstat (schema chiuso)
 → esito, eventi e controllo delle successive modifiche
 → IstatRetention: minimizzazione delle copie scadute
```

Preview e invio riutilizzano l’analisi del medesimo generatore, senza algoritmo alternativo. Send richiede l’hash dell’anteprima e confronta i byte rigenerati; esattamente questi byte vengono costruiti nel SOAP consegnato al double del trasporto. Una modifica successiva dei dati sorgente non cambia retroattivamente il payload già acquisito. Payload identici ancora disponibili vengono riutilizzati sotto lock della struttura.

## F. Dati utilizzati

| Campo Ross1000 | Origine / trasformazione |
|---|---|
| `codice`, `prodotto` | Codice struttura assegnato da Ross1000, configurato dall’operatore; prodotto del gestionale |
| `movimento/data` | Ogni giorno del periodo inclusivo, `AAAAMMGG`; scelta mese oppure date esatte, anche una sola giornata |
| `apertura` | Tipo/date di apertura Struttura; SI/NO |
| `camereoccupate` | Somma camere dei soggiorni presenti; gestione day-use già esistente |
| `cameredisponibili`, `lettidisponibili` | Ricettività Struttura; zero nel file per giorni chiusi |
| `arrivi/arrivo` | Arrivo Schedina nel giorno; un record per ospite principale e componente |
| `partenze/partenza` | Partenza Schedina nel giorno; conserva la data di arrivo originale |
| `idswh`, `idcapo` | `S` + ID Schedina / `C` + ID componente; collegamento al capo `S` + ID Schedina |
| `tipoalloggiato` | Relationship normalizzata: singolo/capo famiglia/capo gruppo/familiare/membro; composizione coerente obbligatoria |
| `cognome`, `nome`, `sesso` | Schedina oppure Componente; escaping XML, normalizzazione sesso e controlli |
| `cittadinanza` | `oa_city_nac` / `city_nac`, tramite codifiche ufficiali |
| `statoresidenza`, `luogoresidenza` | `or_country/or_city/or_prov` oppure `country/city/province`; comune italiano ufficiale o città estera |
| `datanascita`, `statonascita`, `comunenascita` | `oa_date_nac/oa_country/oa_city/oa_prov` oppure `date_nac/country_nac/comune_nac/province_nac` |
| `tipoturismo`, `mezzotrasporto`, `canaleprenotazione` | Campi statistici Schedina; valori/alias del catalogo del service, senza nuovi codici inventati |
| `titolostudio`, `professione` | Campi Schedina; componenti con valori opzionali vuoti, come generatore esistente |

Presenze = pernottamenti, arrivo incluso/partenza esclusa; residenza per italiani/stranieri, non cittadinanza. Esclusi prenotazioni/arrivi non completati, non turisti e altri tenant; date mancanti non escluse silenziosamente. I componenti caricati senza global scope vengono validati esplicitamente per struttura.

## G. Configurazione, trasporto e stati

Username WebService, password WebService e codice struttura sono per struttura. SPID/CIE/CNS del portale non sono credenziali SOAP. Il form generale rimanda all’area ISTAT; nessun segreto salvato viene letto nella Blade. Username/password vuoti conservano i valori precedenti. Credenziali non decifrabili sono segnalate senza valori; la riconfigurazione richiede valori nuovi forniti dall’ente. Salvataggio non prova autenticazione remota.

`config/istat.php` e `.env.example`: default trasporto OFF, 30 giorni come durata tecnica delle copie. Nessuna .env reale modificata. URL per struttura legacy non istituzionale blocca il trasporto; il salvataggio della configurazione dedicata ripristina l’endpoint istituzionale fisso. Nessuna risposta operativa simulata. Colonna storica del flag e proiezione degli esiti storici simulati restano per conservare le migrazioni/storico; non abilitano operazioni.

| Stato | Significato e ripetizione |
|---|---|
| File generato | XML disponibile; non trasmesso né accettato |
| `validated` | Validazione locale XSD; zero rete |
| `pending` | Prenotazione persistita; impedisce altro operatore, retry e sblocco dal form, anche dopo un crash |
| `disabled`, `not_delivered` | Nessuna consegna: errore certo prima del trasporto; periodo liberato e retry controllato possibile |
| `uncertain` | Timeout, HTTP/SOAP inatteso, risposta incompleta/malformata; periodo bloccato, verificare storico portale |
| `processed` | Risposta correlata positiva per tutti i record; NON certifica tutto il calendario né imposta accepted=true |
| `partial`, `rejected` | Esiti dei record parziali/negativi; nessun testo remoto libero conservato; verifica/correzione sul portale |
| `manual_registered` | Caricamento ed esiti sul portale dichiarati dall’operatore; non ricevuta automatica ufficiale |
| Riconciliato sul portale | Timestamp/evento, storico originale preservato; nuova operazione consentita solo dopo conferma esplicita |

Il WSDL di `inviaMovimentazioneResponse` contiene risultati per record e non attesta complessivamente il calendario. Risposta vuota, compreso movimento zero, resta incerta. Non inventata receipt API: il PDF è solo riepilogo locale. Nessun contatore di invio confermato incrementato automaticamente.

## H. Fallback e rettifiche ufficiali

Upload XML: Check-in → Importa file da gestionale; controllo degli esiti e dello storico, poi registrazione locale. La casella di conferma dichiara un’azione umana sul portale; il backend non può verificarla offline. Esito incerto o periodo già comunicato blocca una seconda registrazione/invio automatico. Nessuna email e nessun accesso automatico al portale.

Le correzioni seguono il portale regionale: controllo dello storico/importazioni e del calendario, modifica dei dati ammessi; eliminazione/reinserimento se necessario per tipologia/record eliminati; reimport dei dati corretti. Mese consolidato: intervento dell’ufficio regionale. Upload cronologico; arretrati richiedono anche i periodi successivi. Il gestionale registra la verifica/rettifica dichiarata, conserva il primo esito e richiede una nuova anteprima. Non implementata una sequenza SOAP di rettifica inventata; i dati PMS non vengono modificati da questa procedura.

Modifica soggiorno/ospite, aggiunta/eliminazione componente, arrivo/partenza, prolungamento/riduzione e cancellazione vengono rilevati sullo snapshot comunicato. Ripristino identico mantiene il blocco della duplicazione; dopo rettifica esplicita anche lo stesso payload può essere registrato come nuova operazione. Il ripristino nella prova è ricreazione sintetica del record: non è stato aggiunto un ripristino PMS.

## I. Writer e transazioni

| Entry point → service / writer | Tabella o file → effetto |
|---|---|
| POST configurazione → configure / Struttura encrypted casts | `struttura` → soli campi ISTAT e rimozione URL personalizzato; errori senza flash dei segreti |
| POST XML / verify / send → IstatPayloadStore.store | File privato cifrato, `istat_exports`, tre metadati ISTAT Schedina → copia e storico generazione; lock struttura + transaction DB, compensazione del file in caso di errore |
| POST send / manual → IstatOperationService.reserve | `istat_transmissions`, `istat_communication_days`, `istat_transmission_events` → pending atomico e vincoli unici prima della rete; verify senza prenotazione di consegna |
| Esito → IstatOperationService.finalize | Trasmissione + evento in transaction; soltanto disabled/not_delivered liberano la prenotazione |
| POST riconciliazione → IstatOperationService.reconcile | Timestamp/evento e rimozione prenotazione in transaction, conservando esito e identità originali; pending escluso |
| `istat:minimize` → IstatRetention | Senza --apply: sola selezione; con --apply: elimina esclusivamente copie cifrate scadute eleggibili e marca minimized_at; hash/fingerprint/storico restano |
| POST operativi → middleware audit preesistente | `struttura_audit_logs` → metadati tecnici, mai body/password; middleware immutato |

Tenancy: tutte le route risolvono appartenenza e ruolo tramite StrutturaAccess; download/export/trasmissioni filtrati per struttura. Service prenotazione rifiuta export di altro tenant, hash o modalità non validi. Identità e contenuto export/trasmissione protetti da aggiornamenti ORM accidentali. Nessuna modifica ai campi dati ospiti o al circuito Questura; i soli contatori ISTAT mantengono la funzione preesistente.

Nuova migration unica `2026_10_07_180000_protect_istat_cycle.php`: TEXT e conversione cifrata credenziali; metadata export; idempotenza/tentativi/riconciliazione; due tabelle nuove per prenotazioni ed eventi. Riutilizzate tabelle export/trasmissioni esistenti. Conversione credenziali rieseguibile senza doppia cifratura; contenitori cifrati non decifrabili preservati. Down conservativo non cancella storico né decifra segreti: non equivale a un ripristino del vecchio codice; rollback operativo richiede backup e piano separato. Applicata solo nei DB effimeri dei test.

## J. Privacy e lifecycle

Nuove copie XML cifrate nello storage locale privato, fuori da public; integrità SHA256 prima del download, controllo del prefisso tenant e dei percorsi. Snapshot conserva identificatori e fingerprint, non nomi/documenti. I fingerprint restano dati pseudonimizzati da proteggere, non vengono definiti anonimi. Source guest e payload non vengono riportati nei log applicativi. Eccezioni HTTP/remote non propagate; risposta SOAP grezza e messaggi liberi mai persistiti. Anche errori libxml omettono il contenuto potenzialmente nominativo.

30 giorni è un limite tecnico configurato per le copie di lavoro, **non un termine legale dedotto**. Pending/uncertain/partial non riconciliati sono preservati per evitare perdita della prova e retry ciechi; richiedono gestione operativa. Nessuna pianificazione automatica introdotta. File legacy senza snapshot/cifratura non cancellati o migrati alla cieca: censimento e trattamento richiedono una fase operativa autorizzata. Nessun dato PMS cancellato.

## K. Verifiche e limiti della suite globale

Launcher unico, PHP 8.3.33 / MySQL 8.0.36, processi e DB effimeri attestati, 23 rifiuti override, asset build e GEO immutabile. Nessun runtime Herd operativo utilizzato.

- Prima: regressione Questura pertinente **134 test / 2.748 asserzioni PASS**.
- Conclusiva: **331 test / 4.499 asserzioni PASS**: tutti i 59 casi ISTAT, suite Questura e ulteriori test compatibili con l’isolamento. Prova DDL Questura autonoma collocata per ultima, senza cambiare il relativo test.
- Fixture XML familiare ufficiale/XSD/golden PASS; **race reale a due processi e due connessioni MySQL PASS**, una sola prenotazione. Prova sulla prenotazione, non collaudo SOAP remoto concorrente.
- Browser ISTAT desktop/mobile **2 PASS**; browser Questura originali **4 PASS**. Campi segreti vuoti, OFF visibile, nessuna simulazione operativa, niente overflow orizzontale ISTAT. Screenshot sintetici esaminati; immagini reali escluse. Navbar con nome sintetico lungo appartiene al layout comune preesistente, non modificato.
- Guardrail isolamento **5 PASS**, SOAP/privacy Questura **14 PASS**, privacy ISTAT con doubles **22 PASS**; sintassi dei PHP modificati/nuovi e diff-check PASS, Pint sui dieci nuovi PHP PASS.
- **Suite Feature globale NON PASS**: sulla copia HEAD, 316 casi con 17 errori e 83 failure; prima candidata nella medesima sequenza, 329 casi con 16 errori e gli stessi 83 failure. Fallimenti esterni: Access/Tenancy dipendono dai seeder esclusi per sicurezza; CustomerImported usa fixture incomplete; Example ha aspettativa di route obsoleta; SmokePagesQa legge dati già presenti invece di crearli. Il test DDL C1 lascia sette strutture e una trasmissione legacy e contamina le prove successive se inserito nel mezzo. Nessun test Questura o globale modificato per nascondere queste condizioni.

Baseline ISTAT iniziale **32/146, 1 errore**: fixture arrivo31/03 e orologio07/10 non compatibili con la finestra Questura. Corretto soltanto l’orologio della prova ISTAT di confronto, con reset finally; tutte le sue asserzioni originali mantenute. Nessuna correzione/indebolimento Questura.

Copertura richiesta: tenant/ruoli, credenziali/configurazione/cifratura legacy, campi incompleti, capo/componenti, arrivi/partenze/notti/day-use, cambio mese/anno, geocodici, XML/XSD/DTD, SOAP positivo/negativo/malformato/HTTP/timeout, pending/crash/retry/doppio click/vincolo/concorrenza, download giornaliero/manuale, registrazione portale, dieci variazioni post-comunicazione e ripristino, rettifica esplicita, controllo mese precedente, retention/privacy e regressione Questura. Tutti i dati sintetici; real acceptance assente.

Log tecnici fuori repository: `/private/tmp/istat-verifica-conclusiva.log`, `/private/tmp/istat-questura-browser-regressione.log`, `/private/tmp/istat-suite-head-controllo.log`, `/private/tmp/istat-suite-completa.log`. I runtime sono fermati e rimossi. Non sommare esecuzioni sovrapposte. Nessun miglioramento o correzione dei test globali legacy fuori scope.

## L. CSV di riferimento

Desktop TAVOLA-A: SHA256 `68528cd82ccf95dfb1952dff6d670149db4a59f58ae9407c146904b3d569d290`, settembre2026, 105 righe/30 giorni; apertura31/30 e totale partenze138 invece203. Downloads: SHA256 `f546dce40bece5e815f0cbbdc12af25f1ce1a57621b93f97c5b4c7045819cfd1`, marzo2026,108 righe/31 giorni; apertura32/31. UTF8, separatore ;, 86 colonne logiche. Riletti solo aggregati, senza copia nel repository o fixture. Non ricostruiscono i record individuali, non sono specifiche né prova di upload accettato. Nessun generatore CSV non ammesso inventato.

## M. Stato e passi separati

Lavoro locale ISTAT da revisionare; nessun commit automatico. Produzione non pronta/certificata: collaudo autenticazione/accettazione ufficiale, preflight dell’ambiente, backup/migration/deploy, APP_KEY e archivi legacy, gestione pending dopo crash e policy/scheduling retention richiedono autorizzazioni e verifiche separate. Nessuna credenziale inventata. Rocky/SPanel, Bubblewrap e sei prove obbligatorie restano gate separati pendenti; nessun deploy implicito.

Questura: 50 file protetti e 146 migration storiche byte-identici. Form/model/request/controller Struttura e routes modificati esclusivamente per ISTAT; regressione Questura originale PASS. Handler Q3, LogOperativeAudit, config Questura, TXT/SOAP/ricevute/migration/test Questura invariati. Nessuna .env reale, dipendenza/lock, commit/push/deploy/SPanel/produzione, DB operativo, invio email o Test/Send ISTAT/Questura reale.

## N. Inventario finale

**20 tracked modificati:**

- `.env.example`
- `app/Http/Controllers/IstatTabellaAController.php`
- `app/Http/Controllers/StrutturaController.php`
- `app/Http/Requests/StrutturaRequest.php`
- `app/Models/IstatExport.php`
- `app/Models/IstatTransmission.php`
- `app/Models/Struttura.php`
- `app/Services/EsitoTrasmissioneIstat.php`
- `app/Services/IstatStoricoValidator.php`
- `app/Services/IstatTabellaAService.php`
- `app/Services/IstatWebService.php`
- `app/Services/IstatXmlValidator.php`
- `docs/chiusura-istat-2026-10-05.md`
- `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`
- `resources/views/istat_tabella_a/index.blade.php`
- `resources/views/struttura/form.blade.php`
- `routes/web.php`
- `tests/Feature/IstatConformitaTest.php`
- `tests/Feature/IstatTransmissionSecurityTest.php`
- `tests/Safety/istat_security.php`

**12 file nuovi:**

- `app/Console/Commands/IstatMinimize.php`
- `app/Services/IstatOperationService.php`
- `app/Services/IstatPayloadStore.php`
- `app/Services/IstatResponseParser.php`
- `app/Services/IstatRetention.php`
- `app/Services/IstatSnapshot.php`
- `config/istat.php`
- `database/migrations/2026_10_07_180000_protect_istat_cycle.php`
- `docs/istat-master-2026-10-07.md`
- `tests/Feature/IstatCycleTest.php`
- `tests/Feature/istat-cycle.playwright.spec.js`
- `tests/Isolation/istat-cycle-fixtures.php`

Totale32 file ISTAT/documentazione, staging vuoto. Nessun artefatto temporaneo, screenshot o backup nel repository. Diff stat finale riportato nella risposta conclusiva; i file nuovi non compaiono nel normale git diff --stat.

### Evidenza conclusiva aggiuntiva

Anteprima dei campi arrivo/partenza estratta direttamente dall’XML validato, senza un secondo normalizzatore; risposta della pagina con Cache-Control private, no-store. Riconciliazione registrata con procedura da elenco chiuso, identificatore operatore e data; testo libero respinto. Due nuove prove portano il totale ISTAT a 59 casi. Verifica conclusiva: 331 test / 4.499 asserzioni, prenotazione concorrente e due browser ISTAT PASS.

## O. Correzione controllata P1/P2 dopo audit indipendente

07/10/2026: fase limitata ai tre difetti ISTAT rilevati. Nessun commit/push/deploy, accesso produzione o trasporto reale. Stato BASELINE IN AUDIT, nessuna accettazione Ross1000 o readiness di produzione.

**P1 storico/rettifica:** il validator saltava l’export quando trovava una qualsiasi trasmissione riconciliata. IstatPayloadStore riutilizza correttamente copie XML identiche; una nuova comunicazione poteva quindi utilizzare lo stesso export, ma restare non protetta da modifiche PMS perché la vecchia riconciliazione oscurava il controllo. Ora il validator seleziona le comunicazioni del medesimo export e tenant: sono protette quelle non riconciliate, diverse da verify e con stato diverso da disabled/not_delivered. Una precedente riconciliazione libera l’export soltanto se non esiste una comunicazione attiva pertinente. Snapshot/hash, payload storico, vincoli e guardrail restano quelli esistenti.

Regressione permanente in IstatCycleTest: A, dopo prima riconciliazione e senza nuova comunicazione, modifica/generazione consentita; B, nuovo Send sullo stesso export/XML e modifica ospite bloccata; C, riconciliazione della seconda comunicazione e nuova generazione consentita, XML originale invariato. B/C verificati con processed, uncertain e partial; gli ultimi due sono stati tecnici impostati esplicitamente nella fixture sintetica, non nuovi esiti reali del provider. D/E, record di altra struttura, anche con associazione export incoerente sintetica, non alterano il controllo e la route di riconciliazione respinge l’ID estraneo. Il caso pending resta coperto dalle prove crash preesistenti.

**P2 etichetta:** storico manual → “Consegna sul portale”, distinto da “Invio diretto”; status manual_registered invariato. Test HTTP verifica l’esatto elemento della colonna Tipo.

**P2 operatore retry:** user_id della trasmissione conserva l’operatore originario. La finalizzazione usa l’operatore dell’ultimo evento pending dello stesso tenant/trasmissione, con fallback originale soltanto per record privi dell’evento. La vista distingue Origine e Tentativo, ricavando quest’ultimo dallo stesso audit trail. Nessuna nuova migration o modifica model. Regressione A→B: eventi pending/not_delivered attribuiti ad A, pending/processed del retry attribuiti a B, origine A e attempts=2 preservati.

**Evidenze:** RED valido su copia pre-fix: 33 test / 239 asserzioni, cinque fallimenti attesi (tre P1, operatore retry, etichetta manuale). Prima strumentazione non valida perché l’arrivo della nuova fixture cadeva nel mese precedente e il nome non appariva nell’XML selezionato; corretta soltanto la nuova fixture, nessun test preesistente indebolito. Primo run dopo fix con quella fixture precedente non costituisce prova positiva. Conclusiva **337 test / 4.562 asserzioni PASS**, inclusi **65 casi ISTAT**, regressioni Questura selezionate e accettazione migration in DB effimero; race due processi/connessioni PASS; **2 browser ISTAT + 4 browser Questura PASS**, **5 guardrail / 22 sicurezza ISTAT / 14 SOAP Questura PASS**. PHP syntax e diff-check PASS; Pint sui due PHP nuovi ulteriormente modificati PASS. Non sommare run sovrapposti.

Comando conclusivo: tests/Isolation/run.py con la medesima selezione di regressione già utilizzata, fixture istat-cycle-fixtures.php e spec istat-cycle.playwright.spec.js; quattro browser Questura tramite fixture/spec originali. Nessun ordine configurato, bootstrap, seeder o test Questura modificato. Log esterni: /private/tmp/istat-fix-red-confermato.log, /private/tmp/istat-fix-verifica.log, /private/tmp/istat-fix-browser-questura.log. Runtime xz44c0ty e o0cqh9nj fermati e rimossi; PHP8.3.33/MySQL8.0.36, identità/23rifiuti/build/GEO PASS.

**Suite globale non corretta né rieseguita in questa fase.** Audit precedente: HEAD Feature316/17errori/83fallimenti; candidato343/16errori/86fallimenti, con tre fallimenti Questura aggiuntivi da diagnosticare separatamente. Feature+Unit interrotta su entrambi dalla classe PHPUnit assente in ComponentiImportReviewStatusTest.php. La selezione positiva non certifica la suite globale.

Esattamente sette file ulteriormente modificati rispetto al worktree auditato: IstatStoricoValidator.php, IstatOperationService.php, IstatTabellaAController.php, Blade ISTAT, IstatCycleTest.php, questo rapporto e Maestro. Inventario N invariato:20tracked/12nuovi,32totali. Questura/Q3 50file e 146migration storiche invariati; config ISTAT OFF predefinita preservata. Nessun nuovo file, segreto, schema, dipendenza o modifica dei fallimenti globali. Rocky/SPanel, Bubblewrap, sei prove obbligatorie e trasmissione reale controllata restano fasi pendenti separate. Fermarsi prima del commit per re-audit indipendente.

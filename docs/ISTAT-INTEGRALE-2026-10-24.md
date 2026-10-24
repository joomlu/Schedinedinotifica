# ISTAT — verifica integrale e completamenti del 24 ottobre 2026

**STATO: PARZIALE. BASELINE IN AUDIT invariata.** Codice qualificato in isolamento; pubblicazione in attesa del gate di capienza C5. Nessuna accettazione statistica reale attestata. Questo rapporto aggiorna il quadro ISTAT del 7 ottobre senza trasformarne le prove storiche in prove correnti.

## Ambito e responsabilità

Worktree dedicato `istat-integrale-2026-10-24`, ramo `codex/istat-integrale-2026-10-24`, base `455a792cfc163d9cd52ff3964bc9629222ac5810`. Checkout originale e lavoro preesistente preservati. Sito pubblico affidato all'altro agente nel proprio worktree; nessun sorgente web del rinnovo incluso in ISTAT. Riconciliate le note documentali coordinate Tassa e sito, con le loro evidenze, senza pubblicare il nuovo sito. Rischio ALTO circoscritto al circuito statistico, alle copie personali e alle protezioni di storico/tenancy.

## Ente, nomi e fonti ufficiali

Consultazione il 24 ottobre 2026:

- [Regione Emilia-Romagna, rilevazioni turismo](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo): Regione organo intermedio per ISTAT; rilevazione nazionale IST-00139 distinta dalla rilevazione regionale EMR-00028 e dalla capacità annuale. L'applicazione è Ross1000, già Turismo5. Le quattro strutture attive osservate sono in Emilia-Romagna; nessuna estensione ad altre Regioni.
- [Pagina regionale Ross1000](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo), aggiornata 12 giugno 2025: XML-WS 2.4 e TXT 4.0, upload e WebService. Accesso portale SPID/CIE/CNS; credenziali del servizio distinte. Demo indicata dalla Regione accessibile con utenza pubblica fittizia; login effettuato senza modificare i dati della struttura condivisa visualizzata. Questo non costituisce collaudo di importazione o accettazione delle strutture dell'utente.
- [Tracciato regionale XML-WS](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/tracciatoxml-webservice.pdf/@@download/file), 2.4 del 29 settembre 2021: file UTF-8, giornate cronologiche, disponibilità/apertura, arrivi, partenze, codifiche e Basic Auth. XML scelto copre i canali A/B; TXT alternativo non necessario per completare un upload XML. Non usati endpoint di verifica/receipt inventati.
- [WSDL regionale](https://datiturismo.regione.emilia-romagna.it/ws/checkinV2?wsdl): download pubblico confrontato byte per byte con lo snapshot, identico; SHA256 `5ee868bda0ffcd34c5eff41bbc9bf0c398903d93a0407f00ec6c1804d47bc309`. `checkin.xsd`/`v2.xsd` sono schemi estratti; `file-binding.xsd` resta un binding locale dichiarato, non un ulteriore XSD regionale pubblicato.
- [FAQ regionali](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/faq-rilevazioni-turismo) e [manuale importazione](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/manuale-ross1000-importazione-file.pdf/@@download/file), v1 del 28 marzo 2022: controllo esiti/storico, correzione e reimport; mesi consolidati da sbloccare con ufficio. Non chiamare ricevuta ufficiale il riepilogo PDF locale.
- [GIES, manualistica tecnica](https://www.gies.sm/manuali): ragione sociale Gruppo Informatica e Servizi GIES S.r.l., fornitore che documenta Ross1000. Il nome corretto del portale è Ross1000, non “Rosmille/ROSMILE”. Nessuna fonte verificata identifica “GSBU”: non viene equiparato arbitrariamente a GIES.
- [ISTAT, Movimento clienti 2026](https://www.istat.it/informazioni-sulla-rilevazione/movimento-dei-clienti-negli-esercizi-ricettivi/), pubblicazione 3 febbraio 2026: arrivi/presenze per residenza; MOV/C aggregato comunale trasmesso dall'organo intermedio. Le scadenze dell'organo intermedio non vengono attribuite automaticamente al singolo gestore.
- [Tabelle ufficiali Polizia di Stato](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Tabelle.aspx), richieste dal tracciato: riscaricati Comuni/Stati/Tipi Alloggiato, identici agli snapshot dopo la sola normalizzazione CRLF→LF. Nessuna sostituzione dei codici locali GEO con ID ufficiali alla cieca.

### Significato di Tabella A e percorso Excel distinto

Nel gestionale “Tabella A Emilia-Romagna” è il nome esistente del riepilogo operativo giornaliero che alimenta l'XML Ross1000: non è una denominazione del WSDL né un XML nazionale universale. Comprende apertura/disponibilità, arrivi, partenze e pernottamenti e permette di risalire alle schede. Il riepilogo stampabile locale non viene presentato come modello ufficiale approvato.

La Regione pubblica anche [modelli Excel per 28/29/30/31 giorni](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/moduli-excel-rilevazioni-turismo), pagina aggiornata 23 settembre 2024. Il modello 30 giorni scaricato e letto ha un foglio `30 giorni`, 108 righe/88 colonne: presenze precedenti, arrivi/partenze/presenti giornalieri, Paesi esteri e regioni italiane (Trento/Bolzano separati), letti/camere e apertura. La pagina generale contempla questo percorso temporaneo per difficoltà di accesso Internet, con invio al referente territoriale. Non equivale a caricare XLS su Ross1000 e non autorizza invii alla casella generica di assistenza. Non creato un terzo circuito applicativo o un generatore Excel che finga conformità a quel modello. Questa precisazione supera l'eventuale lettura assoluta dei vecchi rapporti secondo cui nessuna modalità email sarebbe prevista dalla Regione.

## Revisione del circuito esistente

Configurazione cifrata per struttura → soggiorni effettivi e componenti → analisi unica → Tabella A/riepilogo → XML validato → copia privata cifrata → validazione locale o prenotazione atomica → upload dichiarato oppure SOAP → esito/storico/riconciliazione.

Revisionati controller e servizi `Istat*`, modelli export/trasmissioni, snapshot/codifiche/XSD, migration del ciclo protetto, permessi, viste, retention e test. Restano esclusi Arrivi non completati, non turisti e altri tenant; requisiti Questura/Tassa non trasferiti al calcolo statistico. Pernottamenti: arrivo incluso, partenza esclusa. I componenti contano individualmente; residenza distinta da cittadinanza. I giorni senza ospiti e chiusi sono rappresentati nell'XML. Il calendario deriva dalla configurazione canonica; gli override storici devono essere riconciliati.

## Difetti corretti e completamenti

1. **Storico su sottointervalli:** il confronto usava gli ospiti del periodo richiesto contro tutti quelli del vecchio export. Un mese comunicato poteva rendere invalida la successiva consultazione di due giorni invariati. Ora si rilegge l'intero periodo dell'export originale; restano controllati i soggiorni collegati che attraversano mesi e le vere modifiche/cancellazioni.
2. **Contesto di origine non conservato:** nuovi snapshot comprendono versione XML-WS, impronta della configurazione statistica e delle provenienze, oltre alle impronte nominative preesistenti. Configurazione/capacità cambiate dopo comunicazione richiedono riconciliazione. Vecchi snapshot continuano a essere letti senza riscritture né attestazioni retroattive della configurazione.
3. **Copie da rigenerare:** Storico XML mostra formato, hash e origine invariata/modificata/contesto storico non attestato/minimizzazione. Gli XML precedenti restano scaricabili byte per byte se disponibili. La deduplicazione considera sia contenuto sia snapshot; non riutilizza una copia con contesto superato.
4. **Provenienze incoerenti:** la stampa raggruppava le schede del solo titolare, limitandosi alle prime dieci, e mostrava valori grezzi. Ora usa nel servizio centrale tutti gli ospiti e tutte le provenienze per residenza, con arrivi/presenze/partenze conciliabili con Tabella A; date a cavallo mese incluse correttamente. Una famiglia sintetica con residente Italia e residente Germania produce due arrivi, due partenze, due presenze e una presenza per provenienza.
5. **Stagione invertita:** una chiusura precedente all'apertura poteva produrre un calendario interamente chiuso senza errore se non c'erano ospiti. Ora è un errore esplicito di configurazione.
6. **Conservazione della prova comunicata:** la minimizzazione poteva ritirare copie elaborate, dichiarate manualmente o riconciliate. Ora non elimina alcuna copia collegata a un tentativo potenzialmente comunicato, anche dopo riconciliazione. Le copie inutilizzate o mai consegnate restano minimizzabili dopo scadenza. Nessuna pulizia reale o pianificazione eseguita. I 30 giorni restano un limite tecnico delle copie di lavoro; non viene inventato un termine legale. Un'eventuale futura politica sulle prove comunicate richiederà un intervento distinto e controllato.
7. **Cache e browser:** download XML/storico/riepilogo PDF locale con `private, no-store`; test screenshot adattati alla directory effimera Linux e aggiunta verifica tablet e stampa per residenza. Nessuna modifica alle protezioni di rete o ai timeout.

Nessuna nuova tabella, DDL o migration; nessuna modifica dei dati canonici reali, tariffe, Questura, configurazioni esterne, DNS, vhost o codice del sito in revisione.

## XML, trasporto, stati e ricevute

XML regionale UTF-8/XSD e ulteriori regole semantiche verificati in isolamento. Hash dell'anteprima confrontato prima dell'invio; copia cifrata e identificativi stabili. Concorrenza reale su due processi/connessioni del DB effimero: una sola prenotazione; giorni univoci e idempotenza preservati.

- `validated`: solo validazione locale; nessuna rete.
- `pending`: prenotazione conservata anche dopo crash; nessuno sblocco dal normale form.
- `disabled`/`not_delivered`: assenza certa di consegna, solo retry controllato.
- `uncertain`: risposta/timeout non conclusivi; nessun invio cieco, verificare storico del portale.
- `processed`: esiti dei record correlati positivi, senza attestazione dell'intero calendario.
- `partial`/`rejected`: esiti negativi/parziali, necessaria verifica/correzione.
- `manual_registered`: caricamento e controllo dichiarati dall'operatore, non accettazione verificata automaticamente.

Il WSDL consultato non espone un'operazione non mutante di test, consultazione stato o recupero ricevuta. Non sono state inventate. Le rettifiche passano dal flusso ufficiale del portale con evento locale, utente, data e procedura; il primo esito rimane preservato. Il PDF prodotto dal gestionale è esplicitamente un riepilogo locale. Nessuna nuova ricevuta ufficiale o identificativo remoto di consegna fittizio creati.

## Riscontri produttivi e dipendenze esterne

Lettura SQL in transazione READ ONLY, solo aggregati: quattro strutture attive, quattro Emilia-Romagna, zero codici struttura ISTAT presenti, zero coppie credenziali ISTAT presenti, zero export e zero trasmissioni registrate. Non estratti o stampati valori segreti o ospiti. La presenza di una credenziale non avrebbe comunque provato abilitazione/autenticazione.

Dipendenze indispensabili: codice Ross1000 assegnato per ciascuna struttura anche per l'upload; credenziali WS/abilitazione per trasporto diretto; struttura/utenza di collaudo dedicata o file di confronto accettato per dimostrare accettazione ufficiale. Non chiedere di pubblicare password in chat: usare la configurazione protetta o la custodia approvata. La demo condivisa non sostituisce l'abilitazione delle quattro strutture.

Qualunque dichiarazione reale richiede una nuova conferma dell'utente sul contenuto concreto, struttura e periodo. Nessuna dichiarazione reale o fittizia a produzione, email o invio a enti in questa attività.

## Test correnti ed evidenze

Tutte le prove applicative in VM ufficiale `schedine-sviluppo-interno`, clone ISTAT distinto, namespace senza rete esterna, database/filesystem effimeri e fixture sintetiche. PHP 8.3.36, MariaDB 11.8.9, PHPUnit 11.5.57. Build e GEO immutabile e 23 rifiuti di override verificati dall'avviatore. Non usati i dati del DB quotidiano né Laravel sul checkout operativo.

| Esecuzione | Risultato | Portata |
|---|---|---|
| R1 | 65 test / 410 asserzioni PASS | Baseline delle tre suite ISTAT |
| R2 | 68 / 428 PASS; 2 browser NONPASS | Screenshot su `/private/tmp` assente nel contenitore Linux, diagnostici conservati |
| R3 | 91 / 517 e 3 browser PASS | ISTAT + permessi, primo riepilogo/conservazione |
| R4 | RIFIUTATA prima dei test | Nome di una suite Questura inesistente; nessuna prova eseguita |
| R5 | **171 / 1.647 e 3 browser PASS** | Codice applicativo finale: ISTAT, permessi, contratto/protezioni del trasporto e payload Questura |
| R6 | **3 browser PASS**, sei schermate | Stesso codice applicativo R5; verifica aggiuntiva stampa desktop/tablet/mobile |
| Guardie pure | 5 PASS | Primo avvio: Node non nel PATH, 13 errori di processo conservati nel resoconto; ripetizione con PATH ufficiale PASS |

Suite sovrapposte non sommate. Le nuove prove coprono il mese comunicato consultato come sottointervallo, modifica configurazione, immutabilità della copia e segnalazione di rigenerazione, provenienze con componenti e confine mese, deduplicazione, copia inutilizzata minimizzabile e stagione invertita; le prove esistenti coprono XML/XSD, anno bisestile/cambio anno, zero movimenti, non turisti, codici, timeout/rifiuti, doppio operatore, protezione segreti e separazione tenant.

Comando R5 effettivo (adattatore di copia/sigillo verso l'unico launcher ufficiale):

```sh
python3 -B /private/tmp/istat-evidenze-20261024/collaudo.py R5 --phpunit tests/Feature/IstatConformitaTest.php tests/Feature/IstatCycleTest.php tests/Feature/IstatTransmissionSecurityTest.php tests/Feature/StrutturaAuthorizationTest.php tests/Feature/QuesturaTransportGuardTest.php tests/Feature/QuesturaPayloadCoherenceTest.php --fixtures tests/Isolation/istat-cycle-fixtures.php --playwright tests/Feature/istat-cycle.playwright.spec.js
```

Ricevuta aggregata e digest: `evidenze/istat-integrale-2026-10-24/verifica.json`. Log completi e diagnostici conservati nella directory privata `/private/tmp/istat-evidenze-20261024`; schermate sintetiche selezionate nelle evidenze versionate. Nessuna prova simulata viene promossa a test ufficiale.

## Rilascio e punto di ripresa

Pubblicazione autorizzata dall'incarico, ma gate risorse C5 non soddisfatto: ultimo libero a riposo 2.072.559.616 byte, contro almeno 3.097.755.648 byte residui del C5 precedente dopo PREPARE. Occorre anche spazio per la nuova base completa. Soglia di riserva 1 GiB e budget originali invariati; nessun PREPARE/S0, armamento, manutenzione o deploy iniziato. [Piano concreto proposto 36→40 GiB](ISTAT-PIANO-CAPIENZA-2026-10-24.md), autorizzazione specifica richiesta. Stima non promossa a misura del nuovo recupero.

SHA produttivo precedente documentato `dda6b24ed40a3257ad5226ec3459a52f149b95fa`; il candidato di questa attività non è online. Confermare nuovamente SHA, sorgenti e dati al futuro preflight, senza riutilizzare S0 o approvazioni esecutive storiche. Il prossimo C5 dovrà includere nuovi backup/recuperi verificati e monitor; test online autenticati solo dopo pubblicazione, senza dichiarazioni reali. Non attestati in questo rapporto.

## Stato di chiusura

Implementazione e qualifica locale: VERIFICATE nello scope delle correzioni. Generazione/validazione/download XML: VERIFICATI nel runtime isolato. Trasporto SOAP: provato con double ufficialmente isolato, non con credenziali reali. Demo: solo accesso e ricognizione. Pubblicazione e verifica del candidato online: PENDENTI per capienza. Accettazione dell'ente e ricevute ufficiali: NON VERIFICATE per codici/credenziali/abilitazione mancanti.

Codice modificato SÌ; test modificati SÌ; documentazione modificata SÌ; configurazione applicativa modificata NO; schema/dati reali modificati NO; test eseguiti SÌ; commit/push: registrati nei successivi riscontri Git; deploy NO; dichiarazioni statistiche esterne NO. Accessi esterni effettuati: fonti pubbliche, demo con utenza fittizia e letture aggregate produttive, senza trasmissioni di ospiti.

# ISTAT — verifica integrale e pubblicazione, 24–25 ottobre 2026

**MODULO PUBBLICATO E VERIFICATO ONLINE; CIRCUITO REALE PARZIALE. BASELINE IN AUDIT invariata.** SHA produttivo `a90d63aa6bd85f668d1ba61a4a5d776e5529cb98`; C5, QA sintetica, pulizia e monitor finali PASS. Nessuna accettazione statistica reale attestata: mancano codici/credenziali/abilitazione delle strutture. Questo rapporto aggiorna il quadro ISTAT del 7 ottobre senza trasformarne le prove storiche in prove correnti.

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

## Git, autorizzazioni e rilascio effettivo

Commit applicativo `31f5bdf38e18e556266abd217e6c82bf62db4970`, aggiornamento documentale `f836b0687abe02a83a4ebde1c47ff7b39df6b985`, [PR21](https://github.com/joomlu/Schedinedinotifica/pull/21) revisionata automaticamente senza rilievi e integrata su `main`. Merge e SHA realmente servito: **`a90d63aa6bd85f668d1ba61a4a5d776e5529cb98`**, con albero applicativo identico al candidato qualificato. Il consuntivo documentale successivo è distinto dalla versione servita e non richiede un nuovo deploy applicativo.

I primi push e il primo merge erano stati respinti dal controllo automatico per necessità di consenso esplicito al repository pubblico e, separatamente, all'integrazione `main`. L'utente ha poi autorizzato espressamente «Autorizzo push e PR pubblici» e «Autorizzo merge PR21 e rilascio ISTAT con C5». Operazioni eseguite dopo i consensi, nessun aggiramento. Autorizzazione risorse distinta: «Autorizzo il piano 36 → 40 GiB».

La VM è stata arrestata ordinatamente, copiata integralmente con AES256-GCM su Extra e chiave fisicamente separata. Decifratura integrale dei 12 file e rilettura degli originali identici: **20.422.355.234 byte cifrati**. Disco da 36 a 40 GiB; XFS da 37.290.283.008 a 41.585.250.304 byte. Solo il campo disco nella configurazione Lima è variato. DB logico e 84 file protetti identici prima/dopo, daily `945ef553` e SELinux Enforcing preservati. Nessun backup precedente eliminato. [Consuntivo risorse](ISTAT-PIANO-CAPIENZA-2026-10-24.md).

Nuovi PREPARE/S0 cifrati e recuperi indipendenti completi, custodie Extra rilette/autenticate/decifrate con chiave separata PASS. PREPARE finale dopo tre STOP conservati: recupero oltre 240 s, trasferimento oltre 120 s, base cambiata durante preparazione. La diagnosi del terzo confronta il tar con lo stato successivo: nessuna causa puntuale retroattiva inventata. Ripetizioni con nuovi nonce/depositi, nessun vecchio S0 o limite riutilizzato impropriamente. Prima custodia Extra non avviata per timeout della revisione automatica, unico retry consentito riuscito. Ritirati soltanto 1.024.708.608 byte di proprie ricostruzioni temporanee del primo PREPARE, dopo verifica integrale di contenuti/metadati/ridump e file chiusi; cifrati, SQL, chiavi, manifest e log preservati.

Misura dopo PREPARE: 4.126.760.960 byte liberi contro 3.096.907.776 byte richiesti dalla stima conservativa inclusa riserva di 1 GiB. 55 campioni di quiete PASS, nessun logout o intervento sul pool condiviso. **S0 158.611 s / RUN 267.519 s**, zero DDL; budget 1800/600/1379/421 e trasferimento di 120 s invariati. Guardiano concluso/disarmato/inattivo/disabilitato. Git pulito, 250 digest, FPM/SMTP/provider e nove sonde conformi, 159 migrazioni/66 tabelle; DB byte-identico al S0 prima QA e 92 persistenti conformi.

## Verifica online e chiusura delle fixture

Tre larghezze 1440/768/390, login con account sintetici dedicati, due strutture e due ospiti. XML del mese aprile: 30 giornate/2 arrivi/2 partenze; 4 presenze e una camera occupata per ciascuna delle due notti nel riepilogo, provenienze Italia-Lazio e Germania, ciascuna con 1 arrivo, 2 presenze e 1 partenza. Validazione locale, stesso contenuto e SHA nello storico, origine invariata e download con cache `private, no-store` verificati. Credenziali non esposte, invio disabilitato, struttura estranea con HTTP 403, download di altro tenant con HTTP 404, reception in lettura, zero errori JS e nessun POST estraneo. Nessuna route di trasmissione statistica o email consentita nello spec; nessuna dichiarazione inviata. Sei schermate autentiche sintetiche ispezionate e conservate nelle evidenze.

Primo NONPASS dello spec: testo HTML delle celle concatenato `121`, confronto atteso `1 2 1`; corretta lettura delle tre celle esatte e ripetuto integralmente il percorso, senza ridurre asserzioni/timeout né cambiare l'applicazione. Diagnostici preservati. Precondizione di doppio trasferimento della fixture respinta da O_EXCL: copia già presente identica e permessi 0600 verificata senza sovrascrittura.

Fixture proprie rimosse; **HMAC di tutte le 66 tabelle identici**, nessun restore/reset dei contatori. **92 persistenti** identici al S0 per contenuti e metadati, sola eccezione mtime delle directory. Runtime browser proprio rimosso dopo confronto SHA dei 14 artefatti con la copia privata host; clone e backup conservati. VM finale: daily `945ef553`/SELinux Enforcing/DB e 84 file protetti preservati, arresto coordinato concluso; solo i presidi propri terminati.

Monitor avviati dopo tutte le pulizie: **server 919.130 s/31 campioni/279 sonde**, **Mac 921.020 s/31 campioni/93 richieste HTTPS** con DNS e TLS ordinari PASS. Zero errori Laravel/FPM dall'intero T0 del RUN. Capacità: 119 campioni, minimo VM 2.170.011.648 byte e minimo Mac 17.026.891.776 byte, riserve rispettate. Protezione temporanea dallo standby limitata alla task e poi terminata, impostazioni persistenti invariate.

Ricevuta aggregata: [rilascio.json](evidenze/istat-integrale-2026-10-24/rilascio.json). Verifica locale precedente e relativi NONPASS restano conservati separatamente; suite sovrapposte non sommate. Schermate `online-istat-*`, script QA e ricevute monitor nelle stesse evidenze; credenziali e chiavi fuori Git.

## Stato finale e dipendenze concrete

Implementazione e qualifica isolata: VERIFICATE. Generazione/validazione/download XML e storico: VERIFICATI anche online con dati sintetici. Pubblicazione: CONCLUSA sullo SHA indicato. SOAP: implementato e provato con double isolato; demo ufficiale solo consultata, nessun import/WS ufficiale di prova. **Accettazione dell'ente e ricevute ufficiali: NON VERIFICATE**.

Lettura aggregata READ ONLY finale dopo pulizia: 4 strutture attive, 4 in Emilia-Romagna, 0 codici Ross1000, 0 coppie di credenziali presenti, 0 export e 0 trasmissioni reali conservate. Servono codice assegnato per ciascuna struttura anche per l’upload; credenziali WS e abilitazione per l'invio diretto; utenza/struttura di collaudo dedicata o riscontro ufficiale per dimostrare accettazione. Configurare i segreti nel percorso protetto esistente, senza pubblicarli in chat o Git.

Prima di ogni dichiarazione reale resta obbligatoria la conferma umana su struttura, periodo e contenuto concreti. Sito rinnovato su altro worktree, escluso da questo deploy e ancora in revisione. Checkout originario e lavoro estraneo preservati. Stato generale BASELINE IN AUDIT; pubblicazione del modulo e accettazione statistica sono esiti distinti.

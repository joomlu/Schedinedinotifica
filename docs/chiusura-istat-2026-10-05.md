# ISTAT — verifica definitiva del 5 ottobre 2026

**Stato: ISTAT RESTA: identificativo ufficiale Ross1000 non configurato e file del periodo reale non ancora verificabile.**

Il codice è stato corretto e verificato esclusivamente con fixture isolate. Questo documento non certifica l'accettazione di un file reale, non inventa un codice struttura e non autorizza una trasmissione.

## Baseline e ambito

HEAD iniziale: `6d8756c1e79026409a07b39a75701899784833f6`. Nessuna modifica tracked o staged iniziale. Conservati tutti gli untracked preesistenti, senza includerli nel commit.

Letti AGENTS.md e `.github/copilot-instructions.md`. Commit ISTAT precedenti: `4d1f28b` (baseline del sistema) ed `e793b4d509f015d0f2c129d43e3552be052c26ab` (sicurezza della trasmissione e degli esiti). I successivi commit Geo Logo non modificavano ISTAT.

Questura, route, GEO core, modelli Schedina/Componenti/Struttura, migrazioni, Clienti, Presenze, tassa di soggiorno, Dashboard, Notifiche, Calendario, autenticazione, ruoli e selezione struttura non sono stati modificati. Le costanti statistiche consumate dal form schedina sono state aggiornate nel solo service ISTAT perché i vecchi valori non corrispondevano ai domini regionali.

## Architettura attiva studiata prima delle correzioni

`resources/views/istat_tabella_a/index.blade.php` → route `istat.tabella_a.*` in `routes/web.php` → `IstatTabellaAController` → `IstatTabellaAService` → Schedina/Componenti e dati geografici letti → codifica/validazione → XML → download privato oppure `IstatWebService`.

Route attive:

- GET `/istat-tabella-a`: pagina e analisi mensile.
- POST `/istat-tabella-a/controllo`: non salva override, restituisce un messaggio informativo.
- GET `/istat-tabella-a/stampa-riepilogo`: Blade di riepilogo.
- GET `/istat-tabella-a/download/xml`: generazione, storage locale privato, storico e contatori di download ISTAT.
- GET `/istat-tabella-a/download/storico/{id}`: download del proprio storico.
- POST `/istat-tabella-a/ws/verify`: ora validazione locale senza invio.
- POST `/istat-tabella-a/ws/send`: invio esplicito; mai eseguito contro un endpoint reale durante questa task.
- GET `/istat-tabella-a/ws/receipt/{id}`: riepilogo locale, esplicitamente non ricevuta ufficiale.

Modelli ISTAT: IstatExport, IstatTransmission, IstatMovimentoGiornaliero. Gli override storici vengono rilevati e bloccano la generazione finché non riconciliati; non vengono riscritti automaticamente. Nessun job o command ISTAT attivo; nessun generatore CSV/XLS/XLSX nel flusso attivo. Tab e calendari usano i componenti UI esistenti, senza nuovo JavaScript applicativo.

`ArchivosController::generarArchivoHospedados` è legacy e si interrompe con HTTP 410 prima delle query: non è il generatore attivo. Non è stato modificato.

## Sistema territoriale e fonti

La configurazione è stata studiata con PDO, senza bootstrap Laravel, in transazioni **READ ONLY** sul database locale. La query iniziale ha incontrato il nome legacy `città`, quindi la successiva lettura ha usato le colonne realmente presenti. Nessuna scrittura e nessuna modifica dello schema.

Le sette strutture presenti indicano Emilia-Romagna, RN, Bellaria-Igea Marina. **Tutte hanno `istat_codice_struttura` vuoto e simulazione attiva.** La presenza delle credenziali è stata verificata solo come booleano, senza esporle o utilizzarle. `istat_exports` contiene zero record: non esiste un XML reale registrato da confrontare con TAVOLA-A.

| Ente | Documento / URL | Versione e vigenza | Uso |
|---|---|---|---|
| Regione Emilia-Romagna | [Manuali Ross1000](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo) | Pagina aggiornata 12/06/2025, verificata 05/10/2026 | Identificazione del sistema e delle specifiche ancora indicate |
| Regione Emilia-Romagna | [XML-WS](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/tracciatoxml-webservice.pdf/@@download/file) | 2.4, 29/09/2021; collegato dalla pagina vigente | Struttura file, campi, domini, trasmissione |
| Regione Emilia-Romagna | [WSDL pubblico](https://datiturismo.regione.emilia-romagna.it/ws/checkinV2?wsdl) | Contratto effettivamente pubblicato il 05/10/2026, schemi interni 1.0 | Tipi XSD, sequenze e SOAP |
| Regione Emilia-Romagna | [Importazione file](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/manuale-ross1000-importazione-file.pdf/@@download/file) | v.1, 28/03/2022, ancora collegato dalla pagina vigente | Percorso del caricamento manuale |
| Regione Emilia-Romagna | [FAQ](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/faq-rilevazioni-turismo) | Pagina vigente consultata 05/10/2026 | Campi statistici consigliati, anomalie ricettività, import e rettifiche |
| Polizia di Stato | [Tabelle pubbliche](https://alloggiatiweb.poliziadistato.it/PortaleAlloggiati/Tabelle.aspx) | Snapshot scaricato 05/10/2026; nessuna versione dichiarata | Codifiche richieste dalla specifica regionale; nessun accesso autenticato |
| ISTAT | [Rilevazione 2026](https://www.istat.it/informazioni-sulla-rilevazione/movimento-dei-clienti-negli-esercizi-ricettivi/) e [definizioni](https://noi-italia.istat.it/pagina.php?action=show&categoria=8&id=3) | Fonti istituzionali correnti consultate 05/10/2026 | Arrivi, notti, distinzione per residenza |

L'ente ricevente è la Regione Emilia-Romagna tramite Ross1000; non viene ipotizzato un XML nazionale universale. Caricamento manuale XML e contratto SOAP sono distinti. Non sono state usate credenziali reali, SPID, demo remota o operazioni di importazione.

## TAVOLA-A: analisi integrale di entrambe le copie

Trovate due copie differenti. Sono state entrambe analizzate in sola lettura: nessuna è stata copiata nel repository o nelle fixture.

| Proprietà | Downloads/TAVOLA-A.csv | Desktop/TAVOLA-A.csv |
|---|---|---|
| SHA256 | `f546dce40bece5e815f0cbbdc12af25f1ce1a57621b93f97c5b4c7045819cfd1` | `68528cd82ccf95dfb1952dff6d670149db4a59f58ae9407c146904b3d569d290` |
| Periodo | Marzo 2026 | Settembre 2026 |
| Righe | 108 | 105 |
| Giorni / righe movimento | 31 / 93 | 30 / 90 |
| Encoding / BOM / separatore | UTF-8 / NO / `;` | UTF-8 / NO / `;` |
| Colonne fisiche | 86 o 87, ultima extra vuota | 86 o 87, ultima extra vuota |
| Arrivi / partenze / presenze calcolati | 0 / 0 / 0 | 127 / 203 / 1013 |
| Massimo camere / presenti giornalieri | 0 / 0 | 60 / 112 |
| Anomalie | Apertura dichiarata 32 giorni su 31 | Apertura 31 giorni su 30; totale partenze 138 invece di 203 |

Analizzate tutte le 86 colonne logiche: giorno, camere occupate, movimento; 59 categorie estere (colonne 3–61); totale esteri 62; 21 categorie italiane 63–83; totale Italia 84; totale generale 85. Sono aggregati, non record individuali. Il file non contiene identificativi ospite, nascita, sesso, cittadinanza, codice struttura Ross1000 o codici Comune/Stato necessari all'XML.

Per ogni giorno è stata verificata la tripla Arrivati/Partiti/Presenti, l'ordine delle date, la copertura del mese, interi non negativi, totali per categorie e bilancio `presenti = precedenti + arrivi − partenze`. Queste verifiche giornaliere passano. Passano anche i totali mensili delle singole categorie; a settembre fallisce il totale generale delle partenze. Le camere mensili sommano a 578 a settembre. Non risultano record giornalieri duplicati, negativi o incompleti. Gli spazi nei totali sono riconosciuti, i campi numerici non vengono inventati da celle vuote.

Intestazioni notevoli: CECOSLOVACCHIA, categorie residuali estere, Bolzano e Trentino-Alto Adige, apostrofi e denominazioni aggregate. La sola intestazione obsoleta non prova un errore nei valori, qui zero per quella categoria. Non è dimostrabile da questo riepilogo come il software distingua residenza e cittadinanza o ricostruisca i singoli ospiti.

**Provato:** le aperture eccedono il calendario e le partenze di settembre sono aritmeticamente incoerenti nel totale generale. **Ipotesi tecnica:** possibile conteggio inclusivo con un giorno in eccesso; possibile uso del totale Italia al posto del totale generale per le partenze, dato che 138 coincide con l'Italia e mancano i 65 esteri. Non si attribuisce una causa certa senza il codice del software esterno.

La larghezza variabile dipende dai separatori finali vuoti: non dimostra da sola un errore del relativo software. L'accettazione riferita dall'utente è un comportamento empirico; non dimostra che questo CSV coincida con il file XML/TXT importabile dal flusso ufficiale. Conformità integrale TAVOLA-A: **NO**; confronto statistico: **PARZIALE**, perché mancano i dati individuali e i corrispondenti input reali del nostro sistema.

Riproduzione: `python3 -B tests/Safety/tavola_a_audit.py /percorso/TAVOLA-A.csv`. Lo script legge tutte le celle e restituisce solo analisi e dati aggregati; nessun database o scrittura.

## Matrice dei campi attivi

Obbligatorietà: S = richiesta; C = condizionale; O = opzionale. Le lunghezze non dichiarate dalla specifica non vengono inventate. I tipi XSD stringa richiedono controlli semantici separati. “CSV assente” significa che TAVOLA-A non permette quel confronto individuale.

| Campo / vincolo | Obbl. | Tipo, formato, limite / dominio | Origine DB e trasformazione | TAVOLA-A | XML baseline | Stato corretto / evidenza |
|---|---|---|---|---|---|---|
| movimenti | S | Radice singola, senza namespace del file | DOM | CSV aggregato | Presente | SI; binding al tipo ufficiale |
| codice | S | Stringa, identificativo assegnato dall'ente | struttura.istat_codice_struttura, senza inventarlo | Assente | Campo presente, dato reale vuoto | Fixture SI; reale NO |
| prodotto | S | Descrizione del gestionale | Costante Schedinedinotifica | Assente | Ross1000 | Corretto |
| movimento | S | Uno o più, date crescenti | Periodo mensile visualizzato | Tre righe per giorno | Presente | SI, tutti i giorni compresi gli zeri |
| data | S | Data AAAAMMGG | Limiti validati e giorno civile | GG/MM + anno intestazione | Presente | SI; date invalide/invertite rifiutate |
| struttura | S | Singola, sequenza XSD | Configurazione struttura / soggiorni | Header e camere | Campi aggiuntivi non previsti | Corretta |
| apertura | S | SI/NO | Annuale o intervallo stagionale effettivo | Giorni aggregati errati | `aperta` true/false | Corretto |
| camereoccupate | S | Intero non negativo | Quantità camere dei soggiorni, anche day use; non ID camera | Colonna 1 | Calcolo solo pernottanti | Corretto e controllato mensilmente |
| cameredisponibili | S | Intero non negativo; zero da chiusa | struttura.camere_disponibili | Header 64 | Non azzerate da chiusa | Corretto |
| lettidisponibili | S | Intero non negativo; zero da chiusa | struttura.letti_disponibili | Header 154 | Non azzerati da chiusa | Corretto |
| arrivi/arrivo | O/S record | Singolo ospite; sequenza XSD | Capo e componenti, prima il capo | Conteggi aggregati | Un arrivo per schedina, persone aggregate | Corretto |
| idswh | S | Identificativo univoco, max 20 | S + schedina.id; C + componente.id | Assente | `scheda`, non campo ufficiale | Corretto, stabile e separato da numerazione UI |
| tipoalloggiato | S | Tabella ufficiale 16–20 | relationship, codice o descrizione univoca | Assente | Assente | Corretto; composizione verificata |
| idcapo | C | Necessario per 19/20, identico all'ID capo | S + schedina_id | Assente | Assente | Corretto; capo precede componenti |
| cognome | O | Stringa max 50, caratteri XML | surname del rispettivo ospite | Assente | Unito in `ospite` | Corretto; niente troncamenti arbitrari |
| nome | O | Stringa max 30, caratteri XML | name del rispettivo ospite | Assente | Unito in `ospite` | Corretto |
| sesso | S | M/F | sex del rispettivo ospite, conversione 1/2 | Assente | Assente | Corretto; invalido bloccato |
| cittadinanza | S | Codice ufficiale Stato | oa_city_nac / city_nac → adapter | Non distinguibile | Assente | Corretto; distinta dalla residenza |
| statoresidenza | S | Codice ufficiale Stato corrente | or_country / country → adapter | Categorie aggregate | `provenienza`, ID interno | Corretto |
| luogoresidenza | C | Comune ufficiale italiano; estero testo/NUTS max 30, anche vuoto | or_city / city + provincia → adapter | Nessun Comune individuale | Codice ISTAT a sei cifre in altro campo | Corretto; omonimie/incoerenze bloccate |
| datanascita | S | Data AAAAMMGG, non successiva all'arrivo | oa_date_nac / date_nac | Assente | Assente | Corretto, minori inclusi |
| statonascita | O | Codice ufficiale, anche storico | oa_country / country_nac; vuoto conservato se assente | Assente | Assente | Corretto; nessun riempimento inventato |
| comunenascita | C | Codice ufficiale se nascita italiana, altrimenti vuoto | oa_city / comune_nac + provincia | Assente | Assente | Corretto; cessati ammessi con codice ufficiale esplicito |
| tipoturismo | S prudenziale | Descrizione del dominio regionale | istat_tipo_turismo della schedina | Assente | LEISURE/BUSINESS/GROUP ecc. | Valori ambigui bloccati, scelte ufficiali esposte |
| mezzotrasporto | S prudenziale | Descrizione del dominio regionale | istat_mezzo_trasporto; alias solo univoci | Assente | Dominio parzialmente diverso | Corretto |
| canaleprenotazione | O | Descrizione ufficiale o vuoto | istat_canale_prenotazione | Assente | DIRECT/AGENCY ecc. | Ambigui bloccati, vuoto conservato |
| titolostudio | O | Descrizione ufficiale o vuoto | istat_titolo_studio solo capo | Assente | Non esportato | Corretto; non attribuito ai componenti |
| professione | O | Stringa XML | istat_professione solo capo | Assente | Non esportata | Corretto; non attribuita ai componenti |
| esenzioneimposta | O | Codice comunale, se utilizzato | Non esportato: non si riusa exent o la tassa | Assente | Assente | Omissione consentita; nessun mapping inventato |
| partenze/partenza | O/S record | Un record per ospite | Stessi IDs del check-in | Conteggi aggregati | Schedina con persone/provenienza | Corretto |
| partenza/idswh | S | Stabile, max 20 | S/C + ID | Assente | `scheda` | Corretto |
| partenza/tipoalloggiato | S | Tabella ufficiale | relationship del rispettivo ospite | Assente | Assente | Corretto |
| partenza/arrivo | S | Data originale AAAAMMGG | schedina.arrive | Non ricostruibile individualmente | Assente | Corretto, anche oltre confine mese |
| presenze | Statistica | Notti nel periodo | Ospiti nominativi, intervallo arrivo incluso/partenza esclusa | Presenti e somme | Campo aggiuntivo nell'XML | Calcolo corretto; non elemento XML |
| persone, camere, letti nell'arrivo | Non previsti | Non elementi del record ospite | cant_people verificato contro nominativi; room/beds verificati | Aggregati | Inseriti nell'arrivo | Rimossi dall'XML |
| periodoDal/Al, denominazione, comune alla radice | Non previsti | Non elementi della radice | Periodo nei movimenti e filename | Metadati | Inseriti nella radice | Rimossi dall'XML |
| Regione/Provincia | Non campi XML | Usati per lookup coerente/riepilogo | GEO in sola lettura e residenza individuale | Aggregati italiani | Riepilogo del capo | Coerenza verificata; niente elementi aggiunti |

Il PDF e la FAQ non sono perfettamente allineati sull'obbligatorietà dei campi statistici; gli XSD sono meno restrittivi. Turismo e trasporto mancanti bloccano prudenzialmente l'export invece di inventare “Non specificato”. Per i componenti sono usate le classificazioni del soggiorno salvate sulla schedina: questo mapping è esplicito, non ricostruibile da TAVOLA-A. Se gli ospiti del medesimo soggiorno hanno classificazioni diverse, i dati attualmente raccolti non permettono di dimostrarle individualmente; non si certifica quel caso reale senza verifica operativa.

Prenotazioni e rettifiche sono sezioni opzionali del protocollo. L'export corrente comprende solo soggiorni confermati, non le prenotazioni del circuito arrivi. I campi prenotazione (idswh, arrivo, partenza, ospiti, camere, prezzo, canaleprenotazione, statoprovenienza, comuneprovenienza) non vengono generati o inventati. Eliminazione/cancellazione/conferma non vengono emesse automaticamente: uno storico di download non prova l'accettazione remota. Una cancellazione o modifica di arrivo/tipo rispetto all'ultimo file del giorno blocca l'export e segnala ospite e storico da riconciliare sul portale. Questa limitazione è esplicita e verificata.

## Confronto e correzioni applicate

- Root, UTF-8 e date già corrette sono conservati; la loro sola presenza non rendeva conforme il file.
- Corretti campi e ordine rispetto al contratto regionale; introdotti record individuali, componenti, minori e identità persistenti.
- Separati residenza, cittadinanza e nascita; aggiunte le codifiche ufficiali nell'adattatore ISTAT, senza trasformare gli ID/tabelle GEO o condividere cambiamenti con Questura.
- Rifiutati fallback su testo/codici sconosciuti, date mancanti, ospiti non nominativi, tipi gruppo incoerenti, province incoerenti e domini ambigui. Gli errori identificano schedina/componente, campo e valore.
- Verificati zero, chiusure, capacità mensili, day use e perimetro struttura. Le date incomplete non spariscono dalla query. Gli override storici non sovrascrivono silenziosamente i conteggi.
- Ogni XML nuovo passa XSD prima di essere salvato; download storici incompatibili non vengono presentati come pronti.
- File di storage con UUID per evitare sovrascritture nello stesso secondo; aggiornamento dei soli contatori ISTAT della struttura esplicita.
- SOAP allineato al WSDL, credenziali solo nell'header Basic, nessuna password o modalità inventata nel corpo; endpoint pubblico attuale e SOAPAction conforme. Validazione locale separata dall'invio.
- Conservati esiti sanitizzati, nessuna accettazione dedotta da HTTP 200 o simulazione, nessun contatore di invio confermato aggiornato arbitrariamente.

Le differenze tra CSV aggregato e XML individuale non vengono “corrette” copiando il CSV. Le anomalie esterne rimangono documentate senza degradare il generatore. Il confronto tra totali reali di settembre e un nostro export reale resta non dimostrabile: non esiste un export e manca il codice ufficiale.

## XML, XSD e semantica

Artefatto sintetico: `tests/Fixtures/istat/famiglia.xml`, generato dal service reale nel runtime isolato e poi confrontato byte per byte in una nuova esecuzione. Input noto: due ospiti, 01–02/04/2026, un capo italiano residente in Italia e un minore italiano residente in Germania. Attesi: 2 arrivi, 2 partenze, 2 presenze; 1 camera occupata il primo giorno e 0 il secondo. Il minore non viene classificato per cittadinanza al posto della residenza.

XML 1.0, UTF-8 senza BOM, escaping verificato con accenti, apostrofi, `&` e `<`. Root del file senza namespace; SOAP con namespace effettivo del WSDL. Sequenze, cardinalità, campi assenti e inserimenti non previsti verificati tramite XSD. Nessuna firma/checksum aggiunti senza specifica. Il nome `tabella_a_AAAAMMGG_AAAAMMGG.xml` è una convenzione del progetto, non un requisito istituzionale inventato.

`reference/istat/ross1000-er/wsdl.xml` è il documento ufficiale integrale. `checkin.xsd` e `v2.xsd` ne riproducono esattamente i nodi, con il solo `schemaLocation` per import locali: confronto automatico superato. `file-binding.xsd` è un adattatore locale dichiarato che lega la radice del file al tipo ufficiale; non viene chiamato “XSD autonomo ufficiale”. Il payload SOAP è validato direttamente contro lo schema estratto ufficiale. Le tabelle ufficiali sono normalizzate solo da CRLF a LF, con celle identiche e hash di provenienza registrati in `provenienza.json`. WSDL SHA256: `5ee868bda0ffcd34c5eff41bbc9bf0c398903d93a0407f00ec6c1804d47bc309`.

Arrivi e partenze sono attribuiti alle rispettive date; presenze su arrivo incluso/partenza esclusa. Un soggiorno a cavallo mese ha partenze e notti nel periodo corretto senza ricreare l'arrivo fuori periodo. Stesso giorno: arrivo e partenza, zero notti, camera utilizzata in day use. Nessuna distribuzione inventata delle persone di una schedina: se cant_people non coincide con capo + componenti, il file è bloccato.

Comuni cessati per nascita sono ammessi con codice ufficiale esplicito; non vengono inventati match ambigui per nome. Dati GEO formalmente validi nel loro dominio interno non sono modificati per adeguarli al protocollo esterno. La disponibilità dichiarata in SCIA, l'assegnazione del codice struttura e la qualità individuale dei dati reali non sono dimostrabili tramite queste sole fixture.

## Test isolati e regressioni

Riutilizzato integralmente `tests/Isolation/run.py`, senza modificarlo. Nuova istanza MySQL, database effimero, checkout senza `.env`/storage/dati reali, PHP temporaneo, connessione attestata e HTTP esterno bloccato. La lettura READ ONLY della configurazione reale è distinta dai test: i test non hanno usato quel database o copiato i suoi record.

Verifica finale: **77 test PHPUnit, 377 asserzioni**, tutti passati (64 feature, 13 unit). Fixture XML con risultati attesi e confronto byte per byte: PASS.

Runtime `/private/tmp/schedine-test-d_pfprro`, database `test_geo_0483fbc281e335cc28efa6efa6a837e2`, MySQL PID 10423 porta 60719, server PHP PID 10428 su `127.0.0.1:60720`. Processi fermati e directory eliminata al termine. Nessuna connessione di test al DB reale.

Comando riproducibile:

```sh
python3 -B tests/Isolation/run.py \
  --phpunit tests/Feature/IstatConformitaTest.php \
  tests/Feature/IstatTransmissionSecurityTest.php \
  tests/Feature/GeoComuneLogoTest.php \
  tests/Feature/TopbarRenderingTest.php \
  tests/Feature/StrutturaAuthorizationTest.php \
  tests/Unit/TassaDiSoggiornoServiceTest.php \
  tests/Unit/TipoAlloggiatoCatalogoTest.php \
  --fixtures tests/Isolation/prova-istat.php
```

Guardrail esterni: 5 test passati; nel runtime identità processi/DB/HTTP, riconnessione e 23 tentativi di override respinti prima delle migrazioni. Build asset e hash GEO passati. Sicurezza ISTAT: 22 controlli in memoria; sicurezza Questura: 14, senza modificare quel modulo. Nessun test Playwright nuovo: pagina/download/CSRF sono attraversati tramite kernel HTTP reale nei feature test; non si dichiara una prova di caricamento sul portale.

Coperti dati italiani/esteri, famiglie/gruppi, minori, singoli, confini mese/anno, zero, chiusure, day use, accenti/apostrofi/escaping, assenze, codici invalidi, province incoerenti, lunghezze limite, CSRF, validazione senza credenziali e senza HTTP, storico cancellato, privacy degli esiti. XML validi e SOAP passano XSD; XML con campi mancanti/errati/aggiuntivi e DTD sono rifiutati.

Questura: output confrontato byte per byte sugli stessi input prima/dopo la generazione ISTAT; record sorgente invariati. Confronto SHA256 di tutti i file applicativi rispetto alla baseline: cambiati soltanto controller ISTAT, service ISTAT, web service ISTAT, esiti ISTAT e Blade ISTAT; aggiunti soltanto adattatori/validatori interni ISTAT. Tutti i file GEO/Questura e gli altri moduli restano identici. Non viene dichiarata l'esecuzione dell'intera suite storica.

## Punti che impediscono la chiusura operativa

1. Identificativo Ross1000 assegnato dall'ente assente in tutte le strutture: non può essere inventato o inserito qui senza modificare dati reali.
2. Nessun XML reale registrato e nessun confronto definitivo tra gli input nominativi reali, TAVOLA-A e il file del periodo reale. La conformità verificata riguarda gli input sintetici noti e i controlli implementati, non tutti i dati reali.
3. Gli identificativi dei precedenti ospiti del software esterno non sono ricostruibili da questo CSV aggregato. Eventuali rettifiche di file già importati richiedono riconciliazione controllata sul portale; non si presume un'accettazione dagli storici locali. Classificazioni diverse dei singoli componenti non sono attestabili dai campi condivisi attuali.

L'inserimento dell'identificativo e le verifiche operative successive competono a un'attività autorizzata sui dati reali. Durante questa task non sono stati alterati dati reali, trasmessi file o usate credenziali reali.

## Git e risorse

Commit locale unico, semanticamente dedicato alla conformità ISTAT, comprensivo di fonti pubbliche, controlli e prove; SHA indicato nel messaggio finale della chat. Nessun cambiamento all'infrastruttura isolata già esistente. Nessun file TAVOLA-A, XML reale, credenziale, log o untracked preesistente fuori scope incluso.

Push: NO. Deploy: NO. SPanel: NO. Produzione: NO. Trasmissione reale: NO. Risorse applicative temporanee fermate e rimosse dall'avviatore. Gli snapshot ufficiali e le fixture sintetiche permanenti restano come parte della soluzione.

## File modificati o aggiunti

- `app/Http/Controllers/IstatTabellaAController.php`
- `app/Services/EsitoTrasmissioneIstat.php`
- `app/Services/IstatCodifiche.php`
- `app/Services/IstatStoricoValidator.php`
- `app/Services/IstatTabellaAService.php`
- `app/Services/IstatWebService.php`
- `app/Services/IstatXmlValidator.php`
- `docs/chiusura-istat-2026-10-05.md`
- `reference/istat/ross1000-er/.gitattributes`
- `reference/istat/ross1000-er/README.md`
- `reference/istat/ross1000-er/checkin.xsd`
- `reference/istat/ross1000-er/comuni.txt`
- `reference/istat/ross1000-er/file-binding.xsd`
- `reference/istat/ross1000-er/provenienza.json`
- `reference/istat/ross1000-er/stati.txt`
- `reference/istat/ross1000-er/tipi.txt`
- `reference/istat/ross1000-er/v2.xsd`
- `reference/istat/ross1000-er/wsdl.xml`
- `resources/views/istat_tabella_a/index.blade.php`
- `tests/Feature/IstatConformitaTest.php`
- `tests/Feature/IstatTransmissionSecurityTest.php`
- `tests/Fixtures/istat/famiglia.xml`
- `tests/Isolation/prova-istat.php`
- `tests/Safety/tavola_a_audit.py`

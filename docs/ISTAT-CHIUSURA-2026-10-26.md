# ISTAT Emilia-Romagna — completamento tecnico e rilascio del 26 ottobre 2026

**Stato generale: PARZIALE.** Correzioni, qualifica isolata, rilascio C5 e QA online PASS. Monitor finale PASS. Accettazione reale Ross1000 non verificata. **BASELINE IN AUDIT invariata.**

Il codice applicativo pubblicato è `c226dd8072f260eff938cd0651cdbbecbcdd36b9`. La [PR34](https://github.com/joomlu/Schedinedinotifica/pull/34) è integrata con merge `17852e30df3ec410feecac06efdccf073c4f85ab`: albero identico verificato. Il consuntivo documentale è separato dal candidato eseguito.

## Base, autorizzazioni e preservazione

Base iniziale locale/GitHub `a6d406470aa63d7ff69422efcc0b1c14033e0ee5`; produzione `140a1eb6aab82f3df9cd725098835e645dc97eec`, Git pulito e stesso codice ISTAT. Modifiche in worktree separato `/private/tmp/schedine-istat-20261026`, ramo applicativo `codex/istat-chiusura-20261026`; consuntivo `codex/istat-consuntivo-20261026`. Checkout operativo originale e lavori commerciali preesistenti preservati: nessuna loro inclusione nel rilascio.

L’incarico autorizza correzioni, prove, documentazione, GitHub e pubblicazione secondo protocollo. Acquisiti inoltre tre consensi espliciti: ampliamento VM 40→44 GiB con backup verificato; esposizione pubblica del primo candidato `93f9fa02`; merge PR34 e rilascio ISTAT con C5 dopo tutti i controlli. I primi rifiuti automatici di push e merge sono conservati; le azioni sono riuscite dopo i rispettivi consensi, senza elusione. La revisione automatica aggiornata su `c226dd80` è conclusa senza rilievi.

## Correzioni applicative

1. **Download vincolato all’anteprima.** SHA256 obbligatorio e confronto con l’XML corrente prima di conservare una copia. Impronta mancante, errata o superata: messaggio italiano e ritorno al periodo, zero export e zero rete. Successo: byte identici tra anteprima, download e storico. Conferma di download esplicita, senza attestazione di consegna all’ente.
2. **Storico consultabile oltre dieci record.** Paginazioni indipendenti `xml_page` e `tx_page`, mantenendo periodo e scheda. Nessuna modifica dei record storici.
3. **Valori statistici storici conservati nella schedina.** Alias accettati dal servizio (`OTHER`, `AUTO`, `OTA`, `LAUREA`) apparivano senza selezione nelle tendine. Difetto riprodotto nel kernel HTTP prima della correzione. Le sole quattro liste ISTAT ora conservano anche valori già salvati fuori elenco, etichettati «valore salvato», compreso il valore numerico `0`. Nessuna normalizzazione automatica o scrittura dei dati. La dipendenza nel modulo condiviso Schedina è verificata anche sulle regressioni di navigazione, salvataggio, validazione e HTML Web Check-in. Nessuna funzione Questura/Tassa modificata.

## Fonti ufficiali

Consultazione del 26 ottobre 2026: [pagina regionale Ross1000](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo), aggiornata il 12/06/2025. Restano pubblicati il [tracciato XML-WS 2.4 del 29/09/2021](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/tracciatoxml-webservice.pdf/@@download/file) e il [manuale importazione v1 del 28/03/2022](https://statistica.regione.emilia-romagna.it/metadati/rilevazioni/turismo/allegati-rilevazioni-turismo/manuali-e-tracciati-rilevazioni-turismo/manuale-ross1000-importazione-file.pdf/@@download/file); FAQ e procedura riletti. Basic Auth del WS distinta dall’accesso al portale tramite SPID/CIE/CNS.

[WSDL pubblico](https://datiturismo.regione.emilia-romagna.it/ws/checkinV2?wsdl) riscaricato, SHA256 `5ee868bda0ffcd34c5eff41bbc9bf0c398903d93a0407f00ec6c1804d47bc309`, identico allo snapshot. Comuni, Stati e Tipi Alloggiato riscaricati dagli URL ufficiali registrati in `reference/istat/ross1000-er/provenienza.json`: identici dopo sola normalizzazione CRLF→LF. Schemi estratti dal WSDL ufficiali; `file-binding.xsd` è il binding locale dichiarato. Nessuna operazione remota non mutante, ricevuta o conferma ufficiale inventata.

## Qualifica isolata e diagnostici

Unico runtime: VM ufficiale `schedine-sviluppo-interno`, PHP 8.3.36, MariaDB 11.8.9, PHPUnit 11.5.57, SELinux Enforcing. Namespace senza rete esterna e DB/filesystem sintetici effimeri per ogni esecuzione; clone distinto dal quotidiano. Nessun test Laravel sul checkout operativo/Herd. Guardie, 23 rifiuti di override, build GEO e concorrenza su due processi/connessioni conformi.

**Qualifica finale: R10 PHP 238 test / 2.264 asserzioni PASS; R13 ciclo ISTAT e R14 dati origine, tre browser ciascuno a 1440/768/390 px PASS.** Impronte dei sorgenti confrontate tra esecuzioni e candidato. Cinque guardie pure PASS. Conteggi delle suite sovrapposte non sommati.

| Esecuzione | Risultato e diagnosi conservata |
| --- | --- |
| R1 | Baseline 171 test / 1.647 asserzioni e tre browser PASS |
| R2–R3 | NONPASS: scenario di test cambiava il nome di un arrivo fuori dal mese XML; aspettativa corretta con arrivo nel mese e hash esplicitamente differente |
| R4 | PHP 174/1.711 PASS; browser NONPASS perché mancava la gestione del dialogo di conferma esistente |
| R5–R7 | Prima qualifica: R6 175/1.723 e tre browser PASS; R7 tre browser per ulteriori catture |
| R8 | NONPASS atteso: selezione vuota del valore storico `OTHER`, difetto riprodotto |
| R9 | Prima variante della correzione: PHP 238/2.259 e tre browser ciclo PASS |
| R10 | PHP finale 238/2.264 PASS; esito complessivo NONPASS per fixture browser amministrativa senza `sid`, correttamente respinta con 403 |
| R11 | Browser NONPASS: aggiornamento GEO della fixture bloccato dallo scope; corretta la fixture con vincolo esplicito di struttura |
| R12 | Tre browser origine PASS; catture premature durante transizione non promosse a evidenza visiva |
| R13–R14 | Tre browser ciclo e tre origine PASS; R14 attende visibilità/opacità della scheda prima delle catture |

Le verifiche comprendono XML/XSD e semantica, componenti individuali, residenza distinta da cittadinanza, confini di mese/anno e anno bisestile, arrivo incluso e partenza esclusa dai pernottamenti, giornate vuote/apertura/disponibilità, esclusioni e incoerenze; SOAP simulato, sicurezza, duplicati e concorrenza; risposte parziali/rifiutate/incerte e procedura pending→uncertain con conservazione dei giorni e riconciliazione esplicita. I singoli casi restano circoscritti alle suite e non certificano i dati operativi reali.

Avviatore privato: `/private/tmp/istat-evidenze-20261026/collaudo.py`. Comandi finali:

```sh
python3 -B /private/tmp/istat-evidenze-20261026/collaudo.py R10 --phpunit tests/Feature/IstatConformitaTest.php tests/Feature/IstatCycleTest.php tests/Feature/IstatTransmissionSecurityTest.php tests/Feature/StrutturaAuthorizationTest.php tests/Feature/QuesturaTransportGuardTest.php tests/Feature/QuesturaPayloadCoherenceTest.php tests/Feature/SchedinaFlowValidationTest.php tests/Feature/SchedinaNavigationTest.php tests/Feature/SchedinaStoreTest.php tests/Feature/SchedinaSoggiornoTest.php tests/Feature/WebCheckinHtmlTest.php --fixtures tests/Isolation/istat-origin-fixtures.php --playwright tests/Feature/istat-origin.playwright.spec.js
python3 -B /private/tmp/istat-evidenze-20261026/collaudo.py R13 --fixtures tests/Isolation/istat-cycle-fixtures.php --playwright tests/Feature/istat-cycle.playwright.spec.js
python3 -B /private/tmp/istat-evidenze-20261026/collaudo.py R14 --fixtures tests/Isolation/istat-origin-fixtures.php --playwright tests/Feature/istat-origin.playwright.spec.js
```

Catture sintetiche di configurazione, storico, anteprima, stampa e dati origine ispezionate. Nelle fixture isolate mancano loghi/avatar reali per scelta di isolamento; nomi sintetici lunghi possono andare a capo nella topbar, tabelle larghe scorrevoli. [Ricevuta isolata](evidenze/istat-chiusura-2026-10-26/verifica.json).

## Risorse e backup della VM

VM iniziale da 40 GiB con circa 2,4 GiB liberi, insufficienti alla proiezione C5. Piano autorizzato ed eseguito: arresto ordinato, copia integrale cifrata autenticata su Extra, chiave privata sul disco interno fisicamente separato, decifratura integrale e confronto degli originali, crescita esclusiva 40→44 GiB, XFS e riavvio.

PASS: 12 file, **23.470.915.377 byte cifrati**, autenticazione/decifratura complete e originali riletti identici. Disco 47.244.640.256 byte; XFS 41.585.250.304→45.880.217.600 byte; solo campo `disk` variato. HEAD quotidiano `945ef553`, DB logico e 84 file protetti identici, SELinux Enforcing. Primo self-test fermato per `cryptography` assente nel Python di sistema, ripetuto con runtime incluso: PASS anche su corruzione/troncamento. Nessun backup precedente cancellato. Riserve VM 1 GiB, Mac 8 GiB ed Extra 40 GiB mantenute. [Ricevuta risorse](evidenze/istat-chiusura-2026-10-26/risorse.json).

## Rilascio C5 e recuperi

Primo deposito del candidato `93f9fa02` conservato superato e inattivo, senza PREPARE/S0/RUN. Nuovo kit vincolato a `c226dd80`: preflight PASS, nove sonde, nessuna migrazione prevista. Dodici casi puri del trasporto parallelo PASS; guardie e budget invariati.

Primo PREPARE fermato dalla guardia di stabilità per mutazione online prima della barriera; confronto successivo archivio/corrente senza differenze residue, quindi mutazione transitoria non ricostruibile da quel solo confronto. Secondo PREPARE con nonce/base/prebuild nuovi PASS: trasferimento completo in **40,638 s**, quattro flussi entro il limite di 120 s, recupero indipendente completo. Capacità residua verificata prima di ogni RUN.

Primo RUN fermato da un nuovo ingresso sul socket FPM precedente: nessun execute/DDL, baseline riaperta sana dal guardiano in 109,586 s, nove sonde PASS. S0 storico non promosso ad accettato; cifrati conservati e decifrati anche su Extra. Il socket condiviso resta interamente controllato; nessuna richiesta di altri siti interrotta.

Seconda finestra distinta dopo nuovi 55 campioni di quiete: **nuovo S0 indipendente 183,924 s; C5 PASS in 290,809 s / 487 campioni; zero DDL**. Decisione `CANDIDATO_ACCETTATO`, barriera rimossa. PREPARE e S0 accettato cifrati custoditi Mac/VM/Extra, autenticazione e decifratura integrale su Extra con chiave separata nella VM. Entrambi i guardiani propri conclusi, inattivi e disabilitati. Limiti 1800/600/1379/421 s, trasporto 120 s e ricevente 240 s invariati.

Verifica immediata PASS: SHA esatto, Git pulito, Laravel 12.69.3/FPM attivo, 159 migrazioni/66 tabelle, DB byte-identico al nuovo S0 prima della QA, 298 sorgenti e 92 persistenti/.env/APP_KEY/upload/loghi conformi; provider ISTAT/Questura disabilitati e mail array preservati. Un confronto aggiuntivo si è fermato sull’impronta CSS storica, superata dalla PR33 già presente nella baseline: diagnostico conservato, ripetizione PASS rispetto al candidato sigillato **e** ai blob della baseline per tutti i 47 sorgenti del sito. Nessuna modifica al sito introdotta dalla task. [Ricevuta pubblicazione](evidenze/istat-chiusura-2026-10-26/pubblicazione.json).

## QA online e controlli successivi

**QA online sintetica PASS** a 1440/768/390 px: quattro valori storici conservati, rifiuto dell’impronta errata, XML con 30 giornate/2 arrivi/2 partenze, riepilogo con 4 presenze e 2 camere-notte; provenienze Italia-Lazio e Germania ciascuna con 1 arrivo/2 presenze/1 partenza. Byte identici tra anteprima/download/storico, validazione locale, `no-store`, invio disabilitato, tenant estraneo 403/storico 404 e reception in lettura. Nessun POST estraneo o errore JavaScript.

Nove catture online ispezionate, tutte sintetiche; tre conservate nella documentazione. Fixture rimosse: HMAC/conteggi identici su tutte le 66 tabelle preesistenti, senza restore o reset AUTO_INCREMENT. I 92 persistenti restano conformi a S0. Runtime QA proprio rimosso dopo confronto dei 17 file con le copie custodite; clone dipendenze e recuperi preservati. [Ricevuta QA](evidenze/istat-chiusura-2026-10-26/online.json).

Monitor finali **PASS dopo QA e pulizia**: server 919,321 s / 31 campioni / 279 sonde; Mac 920,921 s / 31 campioni / 93 richieste con DNS/TLS ordinari. Zero nuovi errori applicativi e zero errori nel journal FPM, includendo entrambe le finestre RUN. Soli presidi locali propri chiusi dopo verifica dei PID; VM restituita attiva come all’inizio. HEAD quotidiano, DB logico, 84 file protetti e SELinux verificati invariati dopo C5/QA. [Ricevuta monitor](evidenze/istat-chiusura-2026-10-26/monitor.json).

## Matrice conclusiva

| Parte | Stato tecnico | Verifica esterna |
| --- | --- | --- |
| Formulari e configurazione | PASS isolato; valori storici anche online | Codice e credenziali reali da predisporre per il collaudo |
| Tabella A, dettaglio e riepilogo | PASS isolato e online sintetico | Non prova il calendario accettato dall’ente |
| Calcoli e provenienze | PASS nelle suite e nella riconciliazione online sintetica | Nessuna certificazione dei dati reali |
| XML | PASS schemi WSDL ufficiali, binding locale dichiarato e semantica | Accettazione Ross1000 pendente |
| Download/importazione manuale | PASS download e identità delle copie; procedura ufficiale documentata | Importazione esterna non eseguita |
| Web service | PASS contratto/SOAP simulato, sicurezza, timeout, esiti e concorrenza | TLS/auth/accettazione reali non verificati |
| Storico e riconciliazione | PASS paginazione, conservazione e revisione esplicita | Nessuna ricevuta ufficiale inventata |
| Sicurezza e conservazione | PASS isolato, C5 e QA online circoscritta | Non è audit generale di tutti i moduli |
| Accettazione esterna | PENDENTE | Richiede ambiente e utenza abilitati |

## Unico limite operativo residuo

Nello scope ISTAT resta la **prova reale di accettazione Ross1000**. Lettura produttiva aggregata in transazione READ ONLY: quattro strutture attive ER, zero codici Ross1000, zero coppie credenziali, zero export e zero trasmissioni. Nessun valore segreto o dato ospite estratto.

Servono codice assegnato, abilitazione e ambiente/utenza di collaudo dedicati; credenziali WS protette per quel canale e attivazione controllata del trasporto quando si prova il WS. Prima di dichiarazioni reali occorre conferma specifica di struttura, periodo e contenuto. Non inviare fixture al portale produttivo; la demo condivisa non è stata usata. Verificare il canale manuale/WS effettivamente abilitato, conservare l’esito dell’ente e controllare movimenti e calendario. **Nessuna prova reale contro Ross1000 è stata eseguita**, né accettazione attestata.

## Procedura operativa per importazione e risposte non conclusive

1. Correggere i dati nella schedina/componente o nella configurazione della struttura indicata, selezionare il periodo e controllare dettaglio, totali e impronta dell’anteprima. La data di partenza è esclusa dai pernottamenti; ogni componente conta individualmente. Residenza e cittadinanza hanno ruoli distinti.
2. Scaricare il file solo dall’anteprima corrente; custodire la copia. Lo storico permette di recuperare i byte originali e non rigenera le comunicazioni passate. La generazione non consegna nulla all’ente.
3. Nel portale ufficiale: Check-in → Importa file gestionale, selezionare XML, attendere l’elaborazione, controllare tutte le posizioni rifiutate/anomale e la sezione Storico. Verificare anche il calendario disponibilità. Caricare cronologicamente; per un mese arretrato ripetere poi i successivi secondo il manuale. Non registrare localmente la consegna finché questo controllo non è avvenuto. La registrazione manuale resta una dichiarazione dell’operatore, non un riscontro automatico dell’ente.
4. Per WS `partial`, `rejected` o `uncertain`, conservare XML e tentativo, controllare prima il portale. Non ripetere alla cieca. Correggere/annullare sul portale quando necessario e registrare la procedura locale con attore e data prima della nuova anteprima; un mese consolidato richiede l’ufficio regionale. `processed` riguarda solo gli esiti nominativi correlati, non prova l’intero calendario né produce ricevuta ufficiale.
5. Un tentativo `pending` dopo interruzione rimane bloccato per sicurezza. Prima di interventi tecnici identificare esattamente struttura, ID tentativo, periodo, export/hash, processo originario e stato portale; impedire nuovi invii e dimostrare che nessun processo originario possa ancora finalizzare. Se il processo è tuttora attivo o la sua cessazione non è dimostrabile, mantenere il blocco. Solo dopo cessazione accertata, un tecnico autorizzato può finalizzare quel tentativo tramite `IstatOperationService::finalize` con l’esito chiuso `EsitoTrasmissioneIstat::crea('uncertain', 'send')`: nessuna rete, nessuno sblocco dei giorni, evento pending originario preservato e nuovo evento uncertain. Quindi applicare il controllo portale e la normale riconciliazione. Non aggiornare direttamente status, cancellare tentativi/giorni/copie o dichiarare accettazione per rendere disponibile il pulsante. Questa procedura non è stata eseguita su dati reali, perché non esistono tentativi produttivi.


## Dichiarazione di perimetro e custodia

Codice SÌ; test SÌ; documentazione/Maestro SÌ; configurazione applicativa NO; configurazione risorse VM SÌ, solo 40→44 GiB autorizzati; schema/DDL NO; modifiche dei dati reali NO; fixture sintetiche temporanee SÌ, rimosse e riconciliate; test eseguiti SÌ, nel runtime isolato ufficiale; commit/push/merge/deploy SÌ; invii reali enti/email/pagamenti NO; accettazione Ross1000 NON VERIFICATA. Nessun backup o recupero precedente cancellato. BASELINE IN AUDIT invariata.

Diagnostici, sigilli e ricevute private sono conservati in `/private/tmp/istat-evidenze-20261026`; cifrati verificati su Extra, chiavi separate. Non sono pubblicati XML reali, credenziali, chiavi, righe ospiti o contenuti dei backup. Le prove pubbliche si limitano a risultati, conteggi, impronte dei sorgenti e schermate sintetiche.

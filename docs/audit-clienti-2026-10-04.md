# Audit Clienti / Import Clienti — 4 ottobre 2026

**IMPLEMENTAZIONE PARZIALE — RESTA: chiudere Salva schedina → Customer definitivo → riga importata processata e risolvere il contratto distinto tipo cliente / tipo alloggiato.**

## A. Baseline

HEAD: `4f96f79a4959502e37d3339b37932ce08200708d`.

Letti `AGENTS.md` e `.github/copilot-instructions.md`. Acquisiti HEAD, stato completo, diff, stat e copie/SHA-256 dei file già modificati prima delle correzioni dell’audit. Baseline: 21 file tracciati modificati (1.904 inserimenti, 432 eliminazioni), 9 file non tracciati; `git diff --check` iniziale senza errori. Queste modifiche NON sono tutte opera di questa audit.

Evidenze locali in `/tmp/audit-clienti-20261004/`: `baseline-status.txt`, `baseline-stat.txt`, `baseline.diff`, `hashes.json`, copie `files/`, harness `probe.php`, `verifica-finale.jsonl`, `parser.php`, `parser.jsonl`. Il contenuto di `/tmp` è temporaneo.

Nessun reset, clean, stash, migration, modifica `.env`, commit, push o deploy. Nessun bypass del guardrail dei test funzionali. Le prove applicative sono chiamate controllate ai controller/service, autorizzate dalla richiesta, sul DB locale: non sono una suite HTTP isolata né un test browser end-to-end.

## B. Architettura reale

`UploadedFile` → `CustomerImportService::createBatchFromUploadedCsv` → parsing/normalizzazione → `CustomerImportBatch` + `CustomerImportRow` (payload originale e normalizzato JSON). Nessun Customer viene creato dal caricamento.

Percorso A: `confirmImported` → `confirmImportedRow` → ricerca Customer esistente nella struttura oppure creazione in `clienti` → `imported_customer_id`, `imported_at`, stato `imported` → esclusione dalla lista operativa. Eseguito realmente, con il difetto di mapping dei tipi descritto sotto.

Percorso B: `useInSchedina` → redirect a `newschedina?customer_import_row_id=…` → precompilazione. Il form non conserva questo identificatore e `SchedinaController::store` non lo gestisce: crea la schedina senza Customer e senza processare la riga. Eseguito e osservato, non soltanto dedotto dal codice.

## C. Import e modello

Supportati dal controller CSV/TXT, limite 5 MB; parser con `;`, `,`, tab. Tre prove di parsing passate. UI reale dichiara CSV/TXT, non XLSX; XLSX non supportato. Il caricamento di tre fixture ha creato tre righe staging e **zero clienti**.

Modello Clienti indipendente da Componenti; numero colonne coerente con la riga di esempio. Eliminato un secondo pulsante che scaricava esattamente lo stesso modello. Rimane una riga dimostrativa nel template: caricando il modello senza sostituirla si importa anche quella persona dimostrativa.

Corretto BOM UTF-8 negli header e collisione dei nomi dei file caricati nello stesso secondo mediante UUID. Limiti concretamente verificati e non corretti in questa audit:

- righe corte: campi mancanti riempiti con stringa vuota;
- righe lunghe: campi eccedenti troncati senza segnalazione;
- header duplicati: il secondo valore sovrascrive il primo;
- header sconosciuti: accettati dal parser, non mappati ai campi canonici;
- CP1252: nessuna conversione a UTF-8, dimostrata con un cognome accentato sintetico; UTF-16 non verificato;
- file interamente letto in memoria; benchmark completo da 12.747 record non eseguito.

Questi limiti richiedono una politica esplicita di accettazione/segnalazione dei file legacy; non è stata introdotta una conversione ambigua dei dati.

## D. Gioiella

Verificati alias legacy verso campi canonici: `Nro Clienti`, `Gruppo`, `Sesso`, `Tipo Alloggiato`, nome/cognome, Nazione/Provincia/Citta-residenza, CAP, email/telefono, `Celular` e `Cellular`, fax, tipo via, Nazione/Provincia/Citta-Anagrafica, nascita e campi documento. `Tipo Alloggiato` resta `tipo_alloggiato`, separato da `tipo_cliente` nel JSON.

Il file Gioiella originale non è stato reperito fra i file disponibili: **compatibilità del mapping verificata con header sintetici, import del file originale non verificato**. Non è certificata la compatibilità di ogni suo valore né dell’encoding reale.

## E. Date ed età operativa

Corretto parsing permissivo che trasformava `31/02/2020` in `2020-03-02`; ora il calendario è validato con `checkdate`. Rimossa interpretazione libera delle stringhe. Con data applicativa 04/10/2026:

| Input | Nascita/rilascio |
|---|---|
| 12/09/86 | 1986-09-12 |
| 15/06/05 | 2005-06-15 |
| 00/00/0000 | vuoto |
| 2099-01-01 | vuoto/non valida |
| 1986-09-12 | invariata |
| 31/12/26 | 1926-12-31 |
| 31/02/2020 | vuoto/non valida |
| 0086-09-12 | vuoto/non valida |

Gli anni brevi di nascita/rilascio partono da 2000+YY; se la data completa è futura, si sottraggono 100 anni. La scadenza documento ha semantica diversa: una data futura è valida; `2031-01-01` e `01/01/31` sono preservate come 2031-01-01. Prima la scadenza futura veniva cancellata.

Le date invalide diventano vuote; non esiste una segnalazione specifica per tutte le date eliminate e una riga con nome/cognome può restare importabile. La data originale rimane nel `raw_payload`.

`EtaOperativa` non modificata. Nove test puri, 12 asserzioni, passati: regola `max(1, anni_compiuti)`, senza alterare la nascita; date future non valide. Non è stata svolta una nuova prova completa del calcolo tassa.

## F. GEO / CAP

Schema reale verificato: pivot `geo_comuni_cap` con `geo_comune_id` e `geo_cap_id`; l’import usa questi nomi.

| Prova su DB locale | Risultato osservato |
|---|---|
| 47814 / Bellaria-Igea Marina / RN | dato coerente, `valid` |
| 47814 / Rimini / RN | Comune e CAP preservati, avviso, `needs_review` |
| 99999 / Comune Test / ZZ | nessuna eccezione, nessun Comune inventato, dati preservati, `needs_review` |

Restano lookup per riga; nessuna modifica ai servizi GEO condivisi.

## G. Clienti importati

Lista limitata a struttura corrente e `imported_customer_id IS NULL`, paginazione 20, batch precaricato con `with('batch')`.

Provate con esito positivo nove ricerche: nome, cognome, nome+cognome, telefono, cellulare, email, documento, indirizzo, Comune. Aggiunta ricerca combinata nome/cognome e ordine inverso, con parametri SQL associati.

Provati Stato, Tipo, Comune, Provincia, Nazione e combinazione con ricerca. Corretto filtro GEO sensibile alle maiuscole del JSON: `Italia` ora trova `ITALIA`. Gruppo è implementato su quattro campi, ma non è stato oggetto di una fixture dedicata; confronto esatto. Paginazione provata con 22 righe filtrate: pagina 2 restituisce 2 righe, totale 22. `withQueryString()` conserva i filtri.

Modifica eseguita attraverso `updateRow`: risposta 302, nessun Customer creato; valore sesso invalido respinto dalla validazione. Normalizzazione e ricalcolo eseguiti. Corretta perdita di numero cliente legacy, tipo alloggiato, fax, tipo via e nome gruppo non rappresentati/inclusi nel precedente mapping. I campi effettivamente inviati vuoti rimangono svuotabili.

Scarto eseguito sulla sola fixture: riga eliminata, Customer invariati, contatore del batch coerente (7 righe). I contatori ora si aggiornano anche dopo scarto e conferma singola. La distruzione di un intero batch è stata letta, non eseguita: non elimina Customer; cancellazione file e record non costituisce un’unica transazione atomica.

UI aperta in Chrome: ricerca/Applica/Azzera visibili senza espandere filtri, tabella compatta e filtri avanzati collassati; pagina della struttura attiva priva di righe operative. Rendering Blade della tabella con fixture riuscito nell’harness. Import Clienti e Liste/Export aperti realmente; unico download modello osservato. Il confronto con Componenti resta strutturale sulle viste: non certifico una comparazione grafica completa né tutti i clic browser (acquisizione schermo intermittente). Dettagli e Modifica portano oggi allo stesso editor. Azzera è un collegamento all’URL senza query, Applica usa GET. Non sono state create grandi card per persona.

## H. Tipo cliente e tipo alloggiato — difetto aperto

| Passaggio | `tipo_cliente=Ospite` | `tipo_alloggiato=Capogruppo` |
|---|---|---|
| CSV → JSON staging | `tipo_cliente` | `tipo_alloggiato` |
| Modifica staging | conservato nel campo CRM | preservato separatamente |
| Conferma attuale → Customer | `clienti.type` | `clienti.type_housed` |
| Contratto CustomerController / Liste / Export | CRM atteso in `type_housed` | nessun campo Customer dedicato trovato |
| Schedina | `customer_type_housed` è CRM | `relationship` è la classificazione alloggiato |

Nel normale CustomerController, `type` è il titolo (Sig./Dott./…), non il CRM. L’import usa invece `type` per Ospite e `type_housed` per Capogruppo: il filtro export `tipo_cliente=Ospite` **non trova il Customer appena importato**. Provato su DB.

Non è possibile attestare la separazione corretta nel Customer definitivo. Cambiare soltanto il filtro maschererebbe l’errore; cambiare soltanto `type_housed` perderebbe la destinazione del tipo alloggiato. Occorre definire persistenza e mapping distinti (anche per i dati già importati), coordinando modello/schema e integrazione schedina. Non è stata applicata una migrazione o reinterpretazione automatica durante questa audit.

## I. Conferma cliente

Creazione reale della fixture, collegamento `imported_customer_id`, data di importazione e uscita staging verificati. Numero cliente legacy ora preservato; numero generato solo se mancante. Nome/cognome necessari anche per conferma diretta.

La seconda conferma della stessa riga riusa lo stesso ID: idempotenza sequenziale passata. La rilettura e il controllo avvengono ora in transazione con `lockForUpdate`, eliminando la finestra di concorrenza sulla stessa riga; non è stata eseguita una prova concorrente multiprocesso.

Conferma batch verificata su 22 fixture valide: 22 processate, zero valide residue, contatore importate 28 comprese le precedenti fixture. Il ricalcolo contatori è unico alla fine del batch, evitando di ripeterlo per ogni conferma.

Contatti, residenza, documento e data normalizzati seguono il mapping verificato; gruppo e note sono previsti dal mapping, senza una prova dedicata con tutti i campi popolati. Persistono il difetto tipi e la necessità di verifica completa dei valori Gioiella.

## J. Duplicati

Cinque prove separate: documento, email, telefono, cellulare, nome+cognome+nascita. Tutte hanno riusato il Customer già creato; confronto prima/dopo degli attributi riletti dal DB identico: nessuna sovrascrittura con vuoti. Il riconoscimento ora considera entrambi i numeri, anche quando l’altro numero è differente.

Nelle fixture il duplicato viene classificato `duplicate_file` perché esiste anche la riga sorgente nello stesso batch; la conferma trova comunque il Customer definitivo. La ricerca catena è limitata al proprietario e, in assenza di proprietario, alla sola struttura corrente (prima rischiava di estendersi a tutte).

Limiti: due righe diverse confermate contemporaneamente non condividono il lock della medesima riga e non esiste una garanzia di unicità globale per documento/email. Nessuna prova concorrente multi-riga. Telefono/email condivisi possono produrre corrispondenze ambigue; non è stato introdotto merge distruttivo. Modificare una riga ricalcola il suo stato, non quello di tutte le altre righe coinvolte nello stesso duplicato.

## K. Usa in schedina

Route esistente: `POST /clienti/importati/{row}/schedina`. Il controller verifica ownership, poi redirige con parametro `customer_import_row_id`; non muta dati persistenti. GET sarebbe semanticamente adeguato alla navigazione, ma POST non è stato cambiato per solo stile.

Correzione locale autorizzata dell’integrazione: `importedRowToPrefilledCustomer` restituiva `Fluent`, incompatibile con `previewFromOldInput(..., ?Customers)`, causando TypeError. Ora restituisce un modello `Customers` non persistito e senza ID. Prima l’ID staging poteva essere interpretato come `customer_id`. Trasferito anche `tipo_via_strada` nel campo `typeaway` già previsto.

Controller e rendering reale della Nuova Schedina ora riusciti; nome della fixture presente, nessun ID staging assegnato a `customer_id`. Il mapping comprende nascita, cittadinanza, residenza/CAP, contatti e documenti disponibili. Restano mancanti regione/luogo emissione se non disponibili nel contratto d’importazione; non inventati. `tipo_alloggiato` non raggiunge `relationship` e il CRM segue ancora il contratto errato sopra descritto. Non certificata la precompilazione completa di tutti i widget GEO nel browser.

## L. Dopo Salva — risultato reale

Eseguito `store` in modalità `full`, con tutti i dati obbligatori completati e validi, senza componenti. Risposta 302 e nuova schedina presente nella transazione.

| Condizione | Risultato |
|---|---|
| Schedina salvata? | **SÌ** |
| Customer definitivo creato/collegato? | **NO**: `customer_id` vuoto, variazione clienti 0 |
| `imported_customer_id` aggiornato? | **NO**: ancora NULL |
| Importato sparisce dalla lista? | **NO**: ancora presente |
| Duplicati evitati dal percorso completo? | **NO, garanzia non implementata**: in questa singola prova non nasce alcun Customer |

Il form non include `customer_import_row_id`; anche inviandolo esplicitamente nella prova, `store` lo ignora. Per chiudere il percorso servono trasporto dell’identificatore, ownership in POST, validazione completa prima della conferma, mapping delle correzioni del form, riuso/creazione Customer e aggiornamento staging/schedina in un’unica transazione, con idempotenza. Confermare la vecchia riga prima del salvataggio perderebbe le correzioni e potrebbe processarla anche con una schedina non valida.

Non è stata aggiunta una chiamata superficiale a `confirmImportedRow`: propagherebbe il mapping errato dei tipi. La chiusura coerente dipende dal contratto descritto in H; si interrompe qui l’implementazione, come richiesto quando non è chiaramente locale e sicura. Nessun refactoring del motore Schedina.

## M. Sicurezza / ownership

**SÌ nei casi effettivamente provati**: aprire editor, aggiornare, confermare, scartare e usare in schedina una riga di struttura diversa restituiscono ModelNotFound/404 a livello controller. La fixture esterna aveva un batch con struttura distinta sintetica. La query verifica il batch della riga e la struttura corrente, non si affida alla sola UI.

Trovata e corretta una fuga concreta in lista: `OR duplicate_scope IS NOT NULL` non raggruppato aggirava il vincolo struttura. Prima la fixture esterna compariva con «solo duplicati»; dopo non compare. Query di ricerca con valori associati, non concatenati in SQL.

Route nel gruppo `auth`, form con CSRF e override DELETE/PUT dove previsto. Verifica del middleware HTTP/CSRF completa non eseguita: i controlli reali dell’harness partono dai controller. Non è una certificazione generale delle autorizzazioni della catena o di tutti gli altri moduli.

## N. Performance

Misurate **51 query SQL per tre righe** nel caricamento fixture, comprese operazioni di scrittura, contatori e lookup. Il codice esegue lookup GEO e duplicati nel ciclo: costo per riga concreto, non eliminato. Non estrapolo un tempo attendibile per 12.747 righe.

Lista operativa: paginazione ed eager loading batch presenti; ricerca JSON con `%termine%`, CONCAT e filtri testuali richiedono scansioni, senza indici dedicati ai campi JSON. Modifica riga carica i payload dell’intero batch per le firme. Import e conferma batch caricano collezioni intere. Export chiama `get()` e costruisce tutto il CSV in memoria; decorazione GEO può fare ulteriori query per cliente con codici numerici. Liste/Export carica anche l’elenco completo Comuni.

Nessuna ottimizzazione generale introdotta. Contatori della conferma batch ricalcolati una volta, anziché introdurre un ulteriore ciclo quadratico.

## O. Test: eseguiti, passati, falliti e bloccati

**Passati:** test unitari puri `CustomerImportServiceTest` (7 test, 47 asserzioni); `EtaOperativaTest` (9 test, 12 asserzioni); 5 test Python delle protezioni in `tests/Isolation`. I primi sono stati lanciati con solo `vendor/autoload.php` e senza configurazione funzionale; il resolver Eloquent del test import restituisce lookup vuoti in memoria, senza PDO né bootstrap applicativo. Non sono test funzionali contro il database.

**Prove controllate passate:** parsing tre delimitatori, import staging, nove ricerche, filtri singoli/combinati, pagina 2, modifica/validazione, cinque controlli ownership, scarto/contatori, conferma ripetuta, cinque criteri duplicato senza alterazione Customer, conferma batch, GEO nei tre casi richiesti, date, rendering Blade.

**Export:** risposta 200 per generale, email, WhatsApp, postale; generale contiene Customer definitivo ed esclude staging. Canali restituiscono sola intestazione per fixture senza consenso. Non sono state inviate email o comunicazioni WhatsApp. Percorso positivo con consenso non provato in questa audit. Anteprima browser osservata con pagina 1 da 15 clienti e link a pagina 2.

**Falliti rispetto ai requisiti, ancora aperti:** ciclo Salva→Customer→uscita staging; filtro CRM sull’anagrafica importata con tipo alloggiato. Limiti parser documentati in C. Difetti iniziali ora corretti: fuga duplicati, ricerca nome completo, filtro Nazione, perdita campi, parsing date/scadenza e TypeError precompilazione.

**Bloccati:** esecuzione ordinaria `vendor/bin/phpunit tests/Unit/CustomerImportServiceTest.php` interrotta dal bootstrap con `TEST_ISOLATION_REQUIRED`. Suite funzionale/E2E non dichiarata passata e protezione non alterata.

**Non eseguiti:** import originale Gioiella/12.747 righe, concorrenza multiprocesso, tutti i campi e widget browser, confronto grafico completo con Componenti, export positivo con consensi. L’audit distingue questi limiti dalle prove riuscite.

**Pulizia:** tabelle InnoDB verificate prima delle scritture. Transazione esterna con rollback in `finally`; conteggi prima/dopo identici a ogni esecuzione: clienti 16, schedine 35, batch 1, righe staging 2. Nessun record preesistente eliminato; file TEST e viste compilate solo nella directory temporanea dell’audit. I contatori AUTO_INCREMENT possono avanzare anche quando gli inserimenti sono annullati; non sono stati forzati indietro. Non sono stati usati migrate:fresh, wipe o truncate.

## P. File modificati da questa audit

1. `app/Http/Controllers/CustomerImportController.php`: ricerca, filtri/isolamento, conservazione campi, contatori scarto.
2. `app/Services/CustomerImportService.php`: date, BOM, nomi file, preservazione payload/numero, duplicati, transazione e contatori.
3. `app/Http/Controllers/SchedinaController.php`: esclusivamente adattatore `importedRowToPrefilledCustomer`, tipo corretto, rimozione ID staging e trasferimento tipo via.
4. `resources/views/customers/imported/index.blade.php`: ricerca sempre visibile e filtri collassabili.
5. `resources/views/customers/import/index.blade.php`: eliminato download duplicato.
6. `tests/Unit/CustomerImportServiceTest.php`: lookup in memoria, correzione aspettativa tipo alloggiato, regressioni date e BOM.
7. `docs/audit-clienti-2026-10-04.md`: questo report.

I primi sei erano già modificati/non tracciati alla baseline. Il confronto delle copie e degli hash separa gli interventi di questa audit dal lavoro precedente; gli altri file della baseline non sono stati alterati.

## Q. Moduli protetti

Import Componenti **NON modificato durante questa audit**. Componenti e relativo contratto **NON modificati**. Tassa, EtaOperativa, Presenze, Calendario, Notifiche e Questura **NON modificati**. Le differenze Git già presenti in quei moduli appartengono alla baseline. Schedina: sola eccezione locale documentata in K; nessuna modifica a store, costruzione interna, componenti o viste schedina.

## R. Git

Verifica finale `git diff --check` senza errori; stato e statistiche conservati nelle evidenze temporanee. HEAD invariato. Nessun commit, push o deploy. Nessun ripristino del lavoro preesistente.

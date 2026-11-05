# Chiusura workflow Cliente importato → Schedina → Customer

## Semantica verificata prima della verifica finale

| Campo reale | Significato dimostrato |
|---|---|
| `clienti.type` | Titolo anagrafico, per esempio Sig.; non è il tipo CRM |
| `clienti.type_housed` | Tipo cliente CRM: Ospite, Componente, Richiesta |
| `schedina.relationship` | Tipo alloggiato: Capogruppo, Ospite singolo, ecc. |
| `normalized_payload.tipo_cliente` | Tipo CRM del cliente importato |
| `normalized_payload.tipo_alloggiato` | Tipo alloggiato del cliente importato, distinto dal CRM |

L’interpretazione proposta è stata **confermata**, non dedotta dai nomi inglesi:

- `resources/views/customers/partials/scheda-cliente-form.blade.php:96`: Tipo Cliente usa `type_cliente`; alle righe 149–150, Titolo usa `type` e il catalogo titoli.
- `app/Http/Controllers/CustomerController.php:973`: salva il tipo CRM in `type_housed`; riga 974 salva il titolo in `type`. La lista filtra `type_housed` a riga 36.
- `resources/views/customers/list.blade.php:84` e `app/Http/Controllers/CustomerExportController.php:92`: visualizzazione/filtro CRM su `type_housed`.
- `resources/views/schedina/partials/form.blade.php:209`: Tipo alloggiato usa `relationship` e il catalogo alloggiati; il titolo usa separatamente `type`.
- `app/Services/QuesturaTxtExportService.php:121`: determina il codice alloggiato da `schedina.relationship`.
- I model `Customers` e `Schedina` espongono questi campi; il servizio import mantiene due chiavi JSON distinte. Il precedente mapping import era incoerente con tutti questi utilizzatori.

Nessuna modifica di schema o reinterpretazione dei dati storici. Nel percorso A il tipo alloggiato rimane nel payload storico della riga: non viene impropriamente scritto nel campo CRM del Customer. Nel percorso B viene anche salvato in `schedina.relationship`.

## Matrice richiesta — percorso A, conferma diretta

| Verifica | Esito |
|---|---|
| Importato trovato | SI |
| Customer creato/collegato | SI |
| imported_customer_id | SI |
| Uscita staging | SI |
| Idempotenza | SI |

## Matrice richiesta — percorso B, schedina

| Verifica | Esito |
|---|---|
| Importato trovato | SI |
| Precompilazione | SI |
| Correzioni utente preservate | SI |
| Schedina salvata | SI |
| Customer creato/collegato | SI |
| Customer collegato alla Schedina | SI |
| imported_customer_id valorizzato | SI |
| Uscita staging | SI |
| Duplicato Customer | NO |
| Duplicati evitati | SI, nei casi provati |
| Idempotenza | SI, doppio submit della stessa riga |
| Isolamento struttura | SI |
| Fallimento validazione sicuro | SI |

## Tipi persistiti

| Scenario | `clienti.type` | `clienti.type_housed` | `schedina.relationship` |
|---|---|---|---|
| 1 | Sig. | Ospite | Capogruppo |
| 2 | Sig. | Componente | Ospite singolo |
| 3, incompleto e completato nel form | Sig. | Richiesta | Ospite singolo |

`tipo_cliente` preservato separatamente: **SI**. `tipo_alloggiato` preservato separatamente: **SI**. Mapping verificato sul DB: **SI**. Anche gli input selezionati nell’HTML renderizzato sono stati verificati, oltre ai valori persistiti.

Non è stata introdotta promozione Richiesta→Ospite. Esiste un controllo distinto in `CustomerController::validateSchedinaTransferMode`, che richiede Ospite per il comando `to_schedina` dell’editor Customer: è una validazione di quel percorso, non una promozione automatica. La creazione ordinaria in SchedinaController non promuove il CRM; il nuovo percorso import conserva il tipo ricevuto, come richiesto.

## Causa e soluzione

L’origine importata era disponibile solo nella query GET: mancavano il campo nel form e il consumo dell’identificatore nel salvataggio. Inoltre `store` collega normalmente un Customer selezionato, ma non lo crea; la precedente precompilazione non chiudeva il ciclo.

Ora l’identificatore passa nel campo nascosto dopo il controllo di ownership e viene ripreso da `old input` dopo errori o passaggi temporanei del form. GET e POST ricaricano la riga della struttura corrente. Il POST applica validazione completa, poi blocca la riga con `lockForUpdate` nella transazione dedicata alla sola origine staging.

Il medesimo blocco di persistenza schedina già esistente salva schedina, camere e componenti. Solo dopo il successo, `CustomerImportService::completeFromSchedina` trasferisce i dati della schedina salvata al payload import, riusando `updateRowPayload` e `confirmImportedRow`. Il servizio trova/crea il Customer tramite le regole duplicate esistenti, aggiorna staging e collega `schedina.customer_id`. Tutto rimane nella transazione esterna: anche un errore dopo creazione Customer e aggiornamento staging annulla l’insieme.

La riga storica non viene cancellata. Stato `imported`, `imported_at` e `imported_customer_id` la fanno uscire dalla query operativa già esistente. Se la riga è già processata, il secondo POST non crea schedine né clienti e reindirizza alla lista con messaggio esplicito.

I dati corretti del salvataggio sono la fonte per nome, contatti, anagrafica, indirizzo e documento. Il servizio continua a normalizzarli con la logica import esistente. Sul Customer riusato applica i valori non vuoti, conservando codice cliente e consensi già presenti; i vuoti non cancellano dati validi. La conferma diretta conserva il comportamento di riuso non distruttivo precedente. Un Customer esplicitamente selezionato viene accettato, in questo percorso import, solo nella struttura corrente.

Per origine staging il salvataggio persistente deve essere `full`: Bozza/Arrivi sono respinti con un messaggio di validazione, evitando di consumare o perdere l’origine senza una schedina completa. I percorsi ordinari senza origine staging restano invariati. Non sono state cambiate route, logiche di Componenti o regole del catalogo alloggiati; un valore alloggiato importato viene preservato nel form senza conversioni arbitrarie verso il CRM.

## Prove realmente eseguite

Harness locale: `/tmp/chiusura-clienti-20261004/probe.php`. Esiti finali: `/tmp/chiusura-clienti-20261004/risultati-finali.jsonl`.

- CSV temporaneo con sette record `CLI-TEST-*`: import solo staging, nessun cliente prematuro.
- Percorso A: Customer creato, riga processata, uscita lista e riconferma stesso ID.
- Percorsi B1/B2/B3: redirect «Usa in schedina», rendering reale Blade, campi nascosti/tipi selezionati, correzione/completamento, salvataggio reale, Customer collegato, uscita staging. Verificati nome/cognome, indirizzo, email, numero documento normalizzato e titolo nel Customer.
- Tre doppi submit: una schedina, un Customer e lo stesso collegamento per ogni riga.
- Schedina invalida: eccezione di validazione sul cognome, zero nuovi Customer/schedine, riga ancora operativa. Ripristino di identificatore e nome corretto tramite old input verificato renderizzando il form successivo.
- Errore simulato **dopo** il completamento del servizio: errore propagato; impronte delle tabelle identiche allo stato precedente, compresi staging, Customer e schedina.
- Duplicato automatico e Customer selezionato: riuso senza nuove anagrafiche. Verificati correzione del nome e conservazione di cellulare valido e numero cliente quando l’import invia vuoti o un altro numero legacy.
- ID riga di altro hotel: rifiuto server-side, zero scritture.
- Schedina normale senza origine import: comportamento precedente invariato.
- Bozza da staging: respinta senza scritture.
- Filtro CRM export: trova il Customer confermato come Ospite.

Le prove non hanno usato dati personali nuovi reali, né cancellato dati preesistenti. Tabelle transazionali verificate; rollback esterno in `finally`. Confrontate impronte ordinate dell’intero contenuto delle tabelle clienti, schedina, batch/righe import e tabelle componenti/camere presenti prima/dopo: **identiche**. AUTO_INCREMENT può avanzare con inserimenti annullati; non è stato ripristinato artificialmente. File e viste delle fixture restano nella directory temporanea, non nello storage ordinario.

Queste sono prove applicative reali via controller/service e DB locale autorizzate dalla richiesta, non una suite HTTP/browser isolata. La persistenza di old input è stata verificata simulando i dati di sessione che il normale gestore della validazione ripristina. Non è stata eseguita una prova concorrente multiprocesso; il doppio submit sequenziale è verificato e il lock protegge la stessa riga.

Test automatici consentiti:

- `CustomerImportServiceTest`: **8 test, 77 asserzioni passati**, solo autoload e lookup Eloquent in memoria, senza DB.
- Protezioni `tests/Isolation`: **5 test passati**.
- Invocazione PHPUnit ordinaria: **bloccata da TEST_ISOLATION_REQUIRED**; nessun aggiramento né modifica del guardrail.
- `php -l` sui PHP modificati e `git diff --check`: senza errori.

## File modificati in questa fase

1. `app/Http/Controllers/CustomerImportController.php`: allineamento CRM del modello temporaneo dell’editor import.
2. `app/Http/Controllers/SchedinaController.php`: origine/old input, controllo server-side, transazione locale e completamento staging, precompilazione CRM corretta.
3. `app/Services/CustomerImportService.php`: mapping CRM corretto, riuso Customer selezionato, completamento da dati schedina attraverso i normalizzatori esistenti.
4. `resources/views/schedina/partials/form.blade.php`: campo origine e precompilazione distinta del tipo alloggiato.
5. `tests/Unit/CustomerImportServiceTest.php`: regressione sul trasferimento dei dati corretti e dei domini distinti.
6. `docs/chiusura-workflow-clienti-2026-10-04.md`: questo report.

Baseline di questa fase in `/tmp/chiusura-clienti-20261004/`, separata dall’audit precedente: copie file, hash, stato/stat/diff e `solo-chiusura.diff`. HEAD iniziale e finale `4f96f79a4959502e37d3339b37932ce08200708d`. Ventisei altri file già modificati/non tracciati alla baseline rimangono identici. Il report dell’audit precedente rimane storico e invariato.

Schema DB, Componenti, Import Componenti, relativo contratto, Tassa, GEO, EtaOperativa, Calendario, Presenze, Notifiche, Questura ed export non modificati. Nessun commit, push o deploy.

## Limiti residui

Nessun punto mancante nei due percorsi richiesti e provati. Restano fuori da questa chiusura: bonifica delle anagrafiche storiche create col vecchio mapping, prove HTTP/browser isolate e concorrenza su righe import diverse della stessa persona. Nessuna riscrittura automatica dei dati storici. I limiti del parser e delle prestazioni rilevati nell’audit precedente non sono stati riaperti.

**IMPLEMENTAZIONE COMPLETATA — WORKFLOW BILATERALE VERIFICATO**

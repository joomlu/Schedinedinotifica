# Verifica finale workflow Clienti importati — 4 ottobre 2026

**VERIFICA FINALE SUPERATA — WORKFLOW CLIENTI PRONTO PER USO MANUALE**

Nessun problema Cliente residuo dimostrato. Riprese le evidenze già ottenute, senza ripetere le prove completate. In questa verifica non è stato modificato codice applicativo né alcun test: viene aggiunto soltanto questo report.

## Esiti richiesti

SI significa verifica superata nel perimetro descritto sotto; non implica una suite HTTP PHPUnit eseguita né ogni percorso completato mediante clic nel browser.

### IMPORT CLIENTI

| Controllo | Esito |
|---|---|
| Import file | SI |
| Staging | SI |
| Normalizzazione | SI |
| zeri iniziali | SI |
| CAP/GEO sicuro | SI |

### CONFERMA DIRETTA

| Controllo | Esito |
|---|---|
| Customer | SI |
| imported_customer_id | SI |
| uscita staging | SI |
| idempotenza | SI |

### USA IN SCHEDINA

| Controllo | Esito |
|---|---|
| precompilazione | SI |
| correzioni utente preservate | SI |
| schedina salvata | SI |
| Customer creato/collegato | SI |
| Customer collegato alla schedina | SI |
| imported_customer_id | SI |
| uscita staging | SI |
| duplicati evitati | SI |
| doppio submit sicuro | SI |

### SEMANTICA

| Controllo | Esito |
|---|---|
| clienti.type = Titolo | SI |
| clienti.type_housed = CRM | SI |
| schedina.relationship = Tipo alloggiato | SI |
| Ospite / Capogruppo separati | SI |
| Componente / Ospite singolo separati | SI |
| Richiesta preservata | SI |

### SICUREZZA

| Controllo | Esito |
|---|---|
| isolamento struttura | SI |
| rollback | SI |
| validazione sicura | SI |
| dati preesistenti invariati | SI |

### UI

| Controllo | Esito |
|---|---|
| Import Clienti | SI |
| Clienti importati | SI |
| ricerca e filtri | SI |
| ricerca data nascita | SI |
| Usa in schedina | SI |
| Export Clienti | SI |

### REGRESSIONI

| Controllo | Esito |
|---|---|
| schedina ordinaria invariata | SI |
| Componenti invariato | SI |
| Import Componenti invariato | SI |
| moduli protetti invariati | SI |

## Evidenze funzionali e integrità

Esecuzione già completata: `/tmp/verifica-clienti-finale-20261004/probe.php`, esiti in `/tmp/verifica-clienti-finale-20261004/risultati-finali.jsonl`. Bootstrap applicativo CLI, controller e servizi reali, database locale, fixture TEST e transazione esterna sempre annullata. Non è un aggiramento del bootstrap PHPUnit HTTP.

- Upload reale tramite CustomerImportController e UploadedFile: sette righe nello staging, nessun Customer prematuro; modello CSV coerente con il contratto.
- Date normalizzate; telefono, documento e CAP con zeri iniziali conservati come stringhe. GEO riconosciuto correttamente; valori sconosciuti preservati e segnalati per revisione.
- Ricerca su nome, cognome, email, telefono, cellulare, indirizzo, comune, documento e data di nascita; filtri combinati; paginazione di 41 righe; scarto senza alterare Customer o schedine.
- Conferma diretta ripetuta: stesso Customer, imported_customer_id valorizzato, uscita dall'elenco attivo dello staging. La riga storica resta conservata come processata.
- Tre percorsi Usa in schedina: redirect, Blade precompilata, origine nascosta, modifica manuale, salvataggio e collegamento reciproco verificati nel database. Customer con nome, cognome, titolo, indirizzo, email, telefono, comune, CAP e documento corretti dalla schedina salvata.
- Combinazioni persistite: Sig. / Ospite / Capogruppo; Sig. / Componente / Ospite singolo; Sig. / Richiesta / Ospite singolo. Titolo, CRM e alloggiato restano distinti.
- Ripetizione dello stesso submit senza nuova schedina o Customer; riuso del duplicato riconosciuto e del Customer selezionato. Non è stata eseguita una prova di carico concorrente: il controllo concorrente è sostenuto dal lock della riga nella transazione.
- Aggiornamento Customer esistente senza sovrascrivere con vuoti i dati conservabili; numero cliente preservato.
- Validazione negativa senza scritture; old input conserva origine e correzioni. Errore iniettato dopo il collegamento annulla atomicamente tutte le scritture.
- Origine appartenente ad altra struttura respinta con 404 senza scritture. Salvataggio incompleto da staging respinto. Schedina ordinaria senza customer_import_row_id conserva il comportamento precedente, senza creare automaticamente un Customer.
- Query Export verificate separatamente per Ospite, Componente e Richiesta.
- Impronte del contenuto delle tabelle controllate prima/dopo rollback identiche: clienti, schedina, customer_import_batches, customer_import_rows e tabelle componenti/camere ove presenti. Nessuna fixture residua nelle tabelle controllate. I contatori AUTO_INCREMENT possono avanzare anche dopo rollback.

La prima esecuzione dell'harness aveva un'aspettativa incoerente sul fallback del cellulare presente nello staging. È stata corretta soltanto la fixture di verifica; l'esecuzione finale è interamente positiva. Nessuna modifica applicativa è stata necessaria.

## UI e limiti della verifica

Ispezionate nel browser le pagine Import Clienti, Clienti importati, Export Clienti e, in sola lettura, Import Componenti per il confronto visivo. Verificati apertura filtri, ricerca e azzeramento. L'elenco attivo della struttura era vuoto: tabella popolata e paginazione sono state verificate tramite rendering reale Blade nell'harness. Precompilazione e tipi selezionati di Usa in schedina verificati nel DOM renderizzato e tramite controller/database.

Non è stato completato un nuovo ciclo di scritture mediante mouse nel browser: upload, conferma e salvataggio sono provati con controller reali in transazione. Il download del modello tramite browser non è certificato; il contenuto della risposta streamed è verificato. Resta utile l'accettazione operativa dell'utente su comodità dei campi e flusso quotidiano. Questi limiti non sono errori Clienti rilevati.

## Test già eseguiti

- CustomerImportServiceTest: **8 test, 77 asserzioni superate**.
- Otto suite pure superate complessivamente: **83 test, 221 asserzioni**, incluse le precedenti 8/77. Le altre sono ContrattoImportazioneComponentiV1Test, DatiComponenteNormalizzatiTest, EtaOperativaTest, ExampleTest, PianoSyncComponentiTest, SingleNodeBatchLockTest, TipoAlloggiatoCatalogoTest.
- Cinque test Python di isolamento superati.
- PHPUnit ordinario bloccato da **TEST_ISOLATION_REQUIRED**: blocco rispettato, nessuna suite HTTP dichiarata superata, nessun bypass. Test dipendenti da Tests\TestCase non rilanciati con bootstrap ridotto.
- ComponentiImportServiceTest: 67 test, 207 asserzioni, 26 fallimenti e 1 errore, 4 avvisi.
- ComponentiImportFormTest: 11 test, 56 asserzioni, 1 fallimento e 1 errore, 1 deprecazione.
- Script ComponentiImportReviewStatusTest interrotto sul contratto intestazioni; tre scenari JavaScript falliti nell'ambiente simulato.

## Test Componenti falliti: classificazione individuale

**E1 — contratto/fixture preesistente:** SHA-256 identico alla baseline precedente alla chiusura Clienti per ComponentiImportService, ComponentiImportController, ContrattoImportazioneComponentiV1, DatiComponenteNormalizzati e relativi test. Il contratto ha 15 colonne; diverse fixture ne inviano 18 o aspettano intestazioni rimosse. Il rifiuto strutturale produce anche campi vuoti/stati ERRORE nelle asserzioni successive. Esempi: buildValidBatch e fixture newPreview hanno 18 valori. Nessuna dipendenza dal workflow import Clienti nel punto di errore.

**E2 — ambiente PHP simulato:** Request::validate non disponibile nel container minimale. Il metodo validateComponentiRows e l'inizio di store fino al ramo modificato Clienti sono identici alla baseline precedente alla chiusura Clienti; l'errore avviene prima del nuovo ramo.

**E3 — ambiente JavaScript simulato:** mock del submitter privo di matches e variabile componentIndexInput assente. Sia lo script Blade/config-ux sia il test JavaScript sono identici alla baseline precedente alla chiusura Clienti.

La baseline di confronto è `/tmp/chiusura-clienti-20261004/hashes.json`, con copie dei file in `files/`; è distinta dalla baseline iniziale di questa verifica. “Preesistente” indica preesistente alla chiusura Clienti osservata, non una certificazione di ogni commit storico del repository.

| Test / scenario | Motivo osservato | Classificazione ed evidenza | Modifiche effettuate |
|---|---|---|---|
| `ComponentiImportServiceTest::test_ordine_colonne_differente_viene_gestito` | App\Exceptions\ComponentiImportException: Colonna sconosciuta: Regione nascita. Colonna sconosciuta: CAP nascita. Colonna sconosciuta: Regione residenza. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_mapping_header_to_field_e_definitivo` | Failed asserting that null is identical to 'city'. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_csv_con_colonna_extra_diventa_errore_di_riga` | Failed asserting that 'Numero colonne non valido: attese 15, ricevute 19.' [ASCII](length: 50) contains "attese 18, ricevute 19" [ASCII](length: 22). | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_csv_con_colonna_mancante_diventa_errore_di_riga` | Failed asserting that 'Numero colonne non valido: attese 15, ricevute 17.' [ASCII](length: 50) contains "attese 18, ricevute 17" [ASCII](length: 22). | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_txt_con_colonna_extra_diventa_errore_di_riga` | Failed asserting that 'Numero colonne non valido: attese 15, ricevute 19.' [ASCII](length: 50) contains "attese 18, ricevute 19" [ASCII](length: 22). | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_txt_con_colonna_mancante_diventa_errore_di_riga` | Failed asserting that 'Numero colonne non valido: attese 15, ricevute 17.' [ASCII](length: 50) contains "attese 18, ricevute 17" [ASCII](length: 22). | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_riga_malformata_tra_righe_valide_non_contamina_le_successive` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_trim_valori_e_utf8_vengono_preservati` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_csv_quoted_field_viene_parsato_correttamente` | Failed asserting that null is identical to 'Via Roma; centro'. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_data_giorno_mese_anno_valida_viene_normalizzata` | Failed asserting that null is identical to '1980-10-02'. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_data_invalida_genera_errore` | Failed asserting that an array is not empty. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_data_impossibile_genera_errore` | Failed asserting that an array is not empty. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_italia_valida_e_estero_valido` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_italia_senza_provincia_o_comune_restituisce_errore` | Failed asserting that an array is not empty. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_nazione_sconosciuta_genera_errore` | Failed asserting that an array is not empty. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_default_tipo_alloggiato_e_default_esente_vengono_applicati` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_xlsx_compilato_valido_produce_preview_valida` | Failed asserting that 0 is identical to 1. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_xlsx_celle_finali_presenti_ma_vuote_non_causano_errore_strutturale` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_xlsx_celle_vuote_interne_non_shiftano_le_colonne_successive` | Failed asserting that null is identical to 'Rimini'. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_xlsx_righe_successive_restano_allineate_anche_con_riga_precedente_malformata` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_xlsx_celle_vuote_vengono_gestite_come_pipeline_corrente` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_xlsx_data_excel_nativa_viene_convertita_senza_cambiare_contratto` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_template_vuoto_non_modifica_default_import` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_piu_righe_valide_e_mix_valide_invalide` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_csv_con_tab_e_estensione_csv_viene_rilevato_automaticamente` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_prepara_conferma_batch_pending_puo_procedere` | Failed asserting that null is identical to 'NO'. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportServiceTest::test_prepara_conferma_batch_supporta_context_nuova_schedina_senza_id` | Failed asserting that two strings are identical. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportFormTest::test_salvataggio_componenti_nuova_schedina_senza_persistenza` | BadMethodCallException: Method Illuminate\Http\Request::validate does not exist. | E2: ambiente simulato, fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportFormTest::test_preview_conferma_e_ritorno_al_form_senza_persistenza` | Failed asserting that 0 is identical to 1. | E1: preesistente / fuori scope; non causato dalle modifiche Clienti | Nessuna |
| `ComponentiImportReviewStatusTest.php` (script, preview iniziale) | Intestazioni Regione nascita, CAP nascita e Regione residenza sconosciute | E1: preesistente / fuori scope; non causato da Clienti | Nessuna |
| `importazione mantiene POST anche quando il gestore UI disabilita il submitter` | submitter.matches is not a function | E3: ambiente simulato / fuori scope; non causato da Clienti | Nessuna |
| `un salvataggio successivo ripristina PUT anche dopo una navigazione annullata` | submitter.matches is not a function | E3: ambiente simulato / fuori scope; non causato da Clienti | Nessuna |
| `invio implicito conserva PUT e il form nuovo funziona senza hidden` | componentIndexInput is not defined | E3: ambiente simulato / fuori scope; non causato da Clienti | Nessuna |

## Working tree

HEAD di riferimento: `4f96f79a4959502e37d3339b37932ce08200708d`. Il working tree era già modificato all'avvio, anche nei moduli protetti. Queste modifiche pregresse non sono state rimosse né alterate.

Confronto SHA-256 di tutti i file della baseline iniziale di verifica: nessun file modificato o eliminato. Unica aggiunta di questa verifica: questo report. I SI sui moduli protetti significano invariati rispetto alla baseline del lavoro, non uguali a HEAD.

Controlli finali richiesti: git diff --check, git status --short --untracked-files=all, git diff --stat, git diff --name-only. Output conservati in `/tmp/verifica-clienti-finale-20261004/finale-*.txt`. Nessun commit, push, deploy, reset o clean.

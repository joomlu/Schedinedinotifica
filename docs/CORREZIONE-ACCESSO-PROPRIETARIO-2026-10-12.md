# Correzione della validazione accesso proprietario — 12 ottobre 2026

Stato: VERIFICATO nella VM isolata; pubblicazione e verifica online PENDENTI. BASELINE IN AUDIT.

Scope autorizzato: esclusivamente validazione, messaggi e presentazione degli errori del proprietario nelle aree Admin e Superadmin. Nessun nuovo stato, modulo, permesso, ruolo o controllo di abilitazione. Commit, push e deploy autorizzati dall’utente, senza invii agli enti.

Il nome resta obbligatorio sul server e viene indicato come obbligatorio nel modulo. Username/password entrambi vuoti permettono di salvare senza accesso anche se nome/email accesso sono compilati. In creazione dell’accesso, una sola credenziale produce un errore italiano sul campo mancante; username univoco secondo la regola esistente (utenti non eliminati), password minimo otto caratteri. Per un accesso esistente, password vuota conserva l’hash e lo username vuoto conserva quello precedente. Sincronizzazione, ruoli e stato preesistenti non riprogettati. Messaggio senza accesso esatto richiesto dall’utente, anche in aggiornamento.

File applicativi: i due `ProprietariController` Admin/Superadmin e `shared/proprietari/form-panels.blade.php`. Messaggi italiani e attributi leggibili; scheda Accesso aperta sugli errori e riscontro accanto al campo. Nessuna modifica a route, configurazione, dipendenze, migrazioni o altri moduli.

Verifica: `python3 scripts/development/ambiente-interno.py stato`, `sincronizza`, `test tests/Feature/ProprietarioAccessoValidationTest.php`. VM interna Rocky10.2/PHP8.3.33/MariaDB11.8.9, copia temporanea e DB effimero dallo schema corrente privo di dati operativi, namespace senza rete. 23 tentativi di override respinti; build/GEO immutabile PASS; PHPUnit **3 test / 95 asserzioni PASS**. Kernel HTTP e middleware reali, fixture sintetiche e transazioni. Entrambe le aree coperte: nome assente, salvataggio senza credenziali, solo username, solo password, creazione completa e hash password, username duplicato senza persistenza parziale, modifica con password vuota, entrambe vuote su accesso esistente, aggiornamento senza accesso, rendering errori. Admin di altro tenant respinto404 e record/accesso intatti. Risorse effimere fermate/rimosse. Primo accesso VM dal sandbox negato dal sistema; ripetizione autorizzata fuori sandbox PASS, nessun test sul Mac/DB operativo.

Rischio medio: gestione credenziali. Restano fuori scope i criteri legacy di risoluzione accesso per email/nome e la matrice generale dei permessi. Nessuna certificazione generale del modulo o dell’applicazione. Lavoro documentale preesistente preservato, selezione Git limitata al delta di questa task.

Il rilascio richiede candidato esatto, preflight corrente, backup recuperabile e sonde online senza record fittizi. Esito effettivo da registrare dopo esecuzione, nessuna dichiarazione di deploy completato anticipata.

# Condivisione clienti — inventario e piano esecutivo

18 novembre 2026. **ANALISI STATICA COMPLETATA NEL PERIMETRO DI RICERCA; IMPLEMENTAZIONE NON AVVIATA**.

**Aggiornamento successivo nello stesso giorno:** il titolare ha autorizzato l'implementazione circoscritta. È ora presente un candidato locale disattivato, ancora non qualificato per il blocco della VM; [consuntivo corrente](IMPLEMENTAZIONE-CONDIVISIONE-CLIENTI-2026-11-18.md). Le dichiarazioni di sola documentazione e assenza di implementazione sotto descrivono lo stato al momento della stesura di questo piano, non il checkpoint corrente. Nessun import, rilascio o modifica ai dati reali autorizzato implicitamente.

Riferimenti: [Maestro](maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md), [progetto funzionale](PROGETTO-CONDIVISIONE-CLIENTI-2026-11-18.md), [isolamento dei test](TESTING_ISOLATION.md). Baseline `codex/questura-modalita-struttura`, `df711e6c32f0cd984d09d4cf7d0253d66d70394a`; **BASELINE IN AUDIT invariata**. Nessun codice, test, GUI, schema, configurazione o dato reale modificato. Documenti soltanto; nessun import o comando applicativo eseguito.

## Esito e confini della ricognizione

Ricerca statica in tutti i PHP di `app/` per `clienti|customers|customer_id`, integrata da riferimenti a modelli e SQL, rotte, viste, JavaScript e seed. Il censimento testuale ha individuato 40 file applicativi; non tutti contengono accessi all'anagrafica: alcuni hanno testi o metadati. La tabella finale conserva il censimento riproducibile con il numero di righe corrispondenti, non il numero di query.

Sono stati letti i punti operativi critici riportati sotto. Il censimento non equivale a un'analisi dinamica completa delle chiamate, delle dipendenze esterne o dello schema attivo; la copertura va dimostrata con le prove isolate. Nessuna query su database quotidiano o produttivo.

### Accessi da centralizzare

| Superficie | Lettura o scrittura riscontrata | Contratto richiesto per il gruppo |
| --- | --- | --- |
| `CustomerController` | Lista, scheda, modifica con lock, creazione con chiave wizard, storico | Una persona per risultato; aggiornamento canonico versionato; ID legacy risolto e autorizzato |
| `CustomerImportController`, `CustomerImportService` | Staging per struttura, conferma, ricerca equivalente, completamento da schedina/Web | Sorgente per struttura, persona per perimetro; idempotenza e revisione prima del collegamento |
| `CustomerExportController` | Query, conteggi, export con consensi, cancellazione per struttura | Risolvere identità senza estendere consensi; distinguere rimozione locale e cancellazione della persona |
| `SchedinaController` | Ricerca locale, precompilazione, selezione e replica fra hotel, seriali | Resolver del gruppo per ricerca e riuso; mantenere riferimenti locali compatibili e snapshot dei soggiorni |
| `ArrivalsController` | Delega ricerca e salvataggio a Schedina | Stesso contratto; il riferimento cliente non crea soggiorni né importa date |
| `ComponentiController`, `Componenti`, `ComponentiImportService` | Collegamenti `customer_id`, creazione/modifica componenti e payload di import | Autorizzare persona e parent nella struttura corrente; nessuna assunzione che il titolare sia l'identità del componente |
| `WebCheckinController`, `WebCheckinCircuito` | Campi cliente riservati; finalizzazione tramite `clienteDaSchedinaWeb`; snapshot | Token pubblico non concede ricerca nel gruppo. Risoluzione della persona solo nel percorso autorizzato della conversione |
| `CestinoController`, `CestinoService` | Snapshot, ripristino modelli e riferimenti; `baseQueryForClass` | Ripristinare soltanto riferimenti ancora autorizzati; un restore non riattiva una partecipazione al gruppo |
| `CustomerInputValidation`, `CustomerWizardValidation`, `SchedinaAnagraficaValidation` | Cataloghi locali, vecchio cliente, validazioni | Validare nel contesto dell'hotel che opera; separare attributi comuni e classificazioni locali |
| `TitoliStrutturaService`, `GruppiStrutturaService`, `CataloghiIndirizziStrutturaService` | SQL diretto su clienti, import e snapshot del Cestino | Rinomina locale non modifica l'identità comune degli altri hotel; niente propagazione di ID di catalogo |
| `CalendarioController` | Compleanni da clienti e componenti per struttura esplicita | Evitare duplicazione della stessa persona nel medesimo ambito, mantenendo eventi e permessi locali |
| `HomeController`, `GestioneOperativaController` | Conteggi per struttura e consultazione cliente da operazione | Non confondere clienti del pacchetto e persone visibili nel gruppo; conservare il significato dei contatori |
| `RenumberClientiCommand`, seed di numerazione | Query senza scope e numerazione per struttura | Numeri locali distinti dall'identificativo canonico; comandi esclusi dal passaggio automatico |
| Strumenti QA, pulizia orfani, seed e migrazioni storiche | Percorsi diagnostici o potenzialmente mutanti | Inventariati, non eseguiti; non correggere migrazioni storiche per adattare i nuovi test |

### Dipendenza GEO incompatibile con il requisito

`ClientiCsvGeoEnricher::enrich()` richiama `fillFromTwin()` fra Nazione/Provincia/Citta-residenza e Nazione/Provincia/Citta-Anagrafica. `fillFromTwin()` copia il valore non vuoto nell'altro blocco mancante. È una corrispondenza statica concreta, non un comportamento corretto per questi archivi: nascita e residenza possono differire.

Il comando `ClientiEnrichGeoCsvCommand` è quindi **escluso dal percorso di importazione proposto**. Non è stato eseguito o corretto. La preparazione privata già effettuata usava script separati e i file sorgente GEO, non questo comando. Qualunque futuro riuso richiede un contratto che normalizzi i blocchi indipendentemente e una prova negativa esplicita. Non inferire cittadinanza dalla residenza.

### Interfaccia e riferimenti indiretti

Punti individuati: `resources/views/layouts/sidebar.blade.php`, `customers/list.blade.php`, `customers/partials/scheda-cliente-form.blade.php`, `schedina/partials/form.blade.php`, `schedina/partials/scripts.blade.php`, `componenti/new.blade.php`, `componenti/edit.blade.php`; ulteriori testi in `resources/js/ui/config-ux.js` da mantenere coerenti se interessati. Rotte clienti, import/export, ricerca Schedina, alias Arrivi e alias componenti in `routes/web.php`.

Aggiunta richiesta: voce e pagina del proprietario usando il layout esistente. Non ridisegnare il resto. Conservare il contratto JSON delle ricerche correnti tramite adattamento del riferimento locale; se occorrono campi additivi per provenienza o stato, trattarli come modifica esplicita e verificarne i chiamanti. I contatori esistenti non cambiano significato implicitamente.

## Decisioni architetturali per il candidato

1. **Archivio identitario circoscritto.** Una persona canonica per perimetro di condivisione, non un indice di persone visibile a tutti i proprietari. Gli hotel non partecipanti mantengono il percorso precedente.
2. **Compatibilità storica.** Non rinumerare né riscrivere i `customer_id` dei soggiorni esistenti. Un riferimento associa ciascuna scheda locale alla persona canonica. La ricerca condivisa restituisce una persona una volta sola; il salvataggio ottiene un riferimento compatibile con la struttura, senza creare un'altra anagrafica autonoma.
3. **Dati locali espliciti.** Consensi, classificazioni CRM e cataloghi per hotel restano locali; valori di nascita, residenza e documenti condivisi sono letti/scritti dal servizio canonico. Gli snapshot di soggiorno mantengono i dati del momento. Serve un adattatore esplicito, non un observer che sincronizzi copie in modo invisibile.
4. **Appartenenza distinta dalla visibilità.** Consultare la persona perché il gruppo la condivide non la rende cliente storico della struttura. Registrazione/provenienza/soggiorno sono evidenze distinte. I contatori di pacchetto utilizzano le evidenze locali; l'elenco di ricerca usa la visibilità del gruppo.
5. **Gruppi senza sovrapposizione silenziosa.** Nella proposta iniziale una struttura partecipa a un gruppo attivo; unificazione di gruppi richiede riepilogo dell'intero nuovo perimetro. Vincolo tecnico proposto, non scelta dell'utente già acquisita.
6. **Dati incerti tracciati.** Versioni alternative, derivazioni e conferme sono record con origine; una cella vuota non autorizza a inventare un dato. Nessuna data di soggiorno ricavata dal file clienti.

Questa architettura è il candidato da provare, non una garanzia che il solo aggiornamento di pochi controller sia sufficiente. Ogni accesso della tabella precedente deve avere un esito documentato: adattato, mantenuto locale per motivo esplicito o escluso dal nuovo percorso.

## Schema logico additivo da tradurre in migrazioni

Nomi proposti, non tabelle esistenti né migrazioni applicate:

| Entità proposta | Campi e vincoli essenziali |
| --- | --- |
| `customer_sharing_groups` | Proprietario, stato, versione, autore; stato iniziale inattivo |
| `customer_sharing_memberships` | Gruppo, struttura, periodo, stato; slot attivo unico per struttura e storico conservato |
| `customer_persons` | Perimetro identitario, ID stabile, versione e stato; dati comuni con protezioni equivalenti all'anagrafica attuale |
| `customer_person_links` | ID cliente locale, persona, struttura; un cliente locale ha una sola persona attiva; evitare due riferimenti operativi equivalenti persona/struttura senza nascondere gli alias legacy |
| `customer_person_origins` | Persona, struttura, codice esterno, manifest/sorgente, tipo di evidenza, eventuale riferimento soggiorno; unicità sorgente/hotel/codice |
| `customer_person_versions` | Persona, tipo di blocco, valori, fonte, conferma effettiva, autore; distinguere storico documentale e residenza corrente confermata |
| `customer_sharing_operations` | Idempotenza, tipo, autore, versione attesa, stato, manifest degli effetti; fusione/separazione annullabile secondo evidenza |

L'appartenenza comune del proprietario si verifica anche nel servizio transazionale. Le chiavi esterne devono essere definite sulla migrazione effettiva dopo verifica delle tabelle e dei motori supportati nel runtime isolato. L'unicità del solo collegamento locale non basta alla concorrenza: bloccare il dominio/gruppo prima della ricerca di equivalenti, seguire ordine deterministico dei lock e ripetere il confronto prima della creazione. Le impronte di identità incomplete non diventano vincoli unici che confondono omonimi.

## Contratti di servizio

| Operazione | Controlli prima dell'effetto | Risultato e limite |
| --- | --- | --- |
| Risolvere gruppo | Utente/struttura attivi, appartenenza attuale, proprietario, stato/versione | Gruppo esplicito o solo struttura corrente; nessun ripiego su tutti gli hotel |
| Cercare/selezionare persona | Verifica server del perimetro anche con ID diretto | Risultato deduplicato e dati minimi; selezione non modifica la persona |
| Aggiornare persona | Partecipazione attiva, versione attesa, validazioni e audit | Aggiornamento comune oppure conflitto controllato; nessuna sovrascrittura tardiva |
| Creare/collegare cliente | Lock perimetro, confronto identità, idempotenza, classificazione locale | Riutilizzo persona o proposta di nuovo cliente; caso dubbio in revisione |
| Registrare soggiorno | Riferimento compatibile con hotel e persona, validazioni del soggiorno esistenti | Nuovo soggiorno della struttura; nessuna riscrittura dei precedenti |
| Fondere/annullare fusione | Riepilogo versionato, fonti e relazioni attribuibili | Alias conservati; relazioni successive ambigue sospese per verifica |
| Esportare | Appartenenza attuale e autorizzazione export/consensi locali | Nessuna estensione implicita a marketing del gruppo |
| Rimuovere/ripristinare | Riferimenti correnti e storici, contesto locale e membership attuale | Non cancellare persona usata altrove; restore non riattiva il gruppo |
| Separare hotel | Riepilogo, conferma titolare, consistenza pacchetto, stato e versione | Snapshot indipendente e revoca accesso generale, conservazione dell'archivio di partenza |

Il riuso di ruoli esistenti non elimina le autorizzazioni: il proprietario configura il gruppo, gli utenti già abilitati della struttura lavorano sulle persone condivise. L'ID struttura inviato dal browser non determina il perimetro.

## Attivazione, migrazione e recupero

1. **Candidato isolato disattivato:** migrazioni additive senza migrare o fondere automaticamente le persone esistenti. In assenza di gruppi attivi tutte le regressioni precedenti devono passare.
2. **Preparazione esplicita:** acquisire il perimetro del proprietario e un manifest dei riferimenti locali, versionato; riconciliare i dati sospesi prima di ogni scrittura. Nessun accesso ai dati reali in questa fase documentale.
3. **Costruzione inattiva:** creare persone, alias e provenienze nell'ambiente di prova. Lavoro a blocchi idempotenti, con conteggio e riconciliazione; non esporre gruppi parzialmente preparati.
4. **Attivazione breve e atomica:** sotto lock delle strutture in ordine e del gruppo, rileggere proprietario/versioni, riconciliare modifiche intervenute e cambiare lo stato attivo. Le richieste successive rileggono membership e versione. Sessioni e cache non conservano concessioni obsolete.
5. **Errore prima dell'attivazione:** il gruppo resta inattivo; dati di preparazione tracciati e ritentabili. Nessuna copia indipendente messa in uso.
6. **Errore dopo nuovi aggiornamenti:** non basta spegnere un interruttore e tornare ai dati legacy, che potrebbero essere vecchi. Prima del rilascio qualificare il recupero con riconciliazione delle scritture avvenute o manutenzione e ripristino coordinato autorizzato secondo Maestro. Nessun `down()` distruttivo presentato come rollback sicuro.
7. **Separazione:** costruire il pacchetto indipendente senza accesso anticipato, verificare riferimenti dei soli soggiorni locali e conferma del titolare; attivare nuova appartenenza e revocare la precedente senza finestra di doppio accesso. Nuovi gestori/sessioni verificati prima della consegna. Se non viene autorizzata la consegna, nessuna attribuzione silenziosa al nuovo gestore.

## Lotti d'implementazione proposti

| Lotto | Perimetro | Condizione per passare oltre |
| --- | --- | --- |
| A | Modello additivo, resolver e policy di gruppo, nessuna attivazione | Isolamento negativo e regressione senza gruppo positivi |
| B | Ricerca, scheda, salvataggio e riferimenti Schedina/Arrivi/Componenti/Web | Una persona, soggiorni separati, aggiornamenti concorrenti e snapshot integri |
| C | Import manifest, duplicati e integrazione senza perdita | Ripetizione idempotente, omonimi protetti e riconciliazione di tutti i codici |
| D | Export, cataloghi, Cestino, conteggi e ripristini | Nessun accesso o aggiornamento indiretto fuori dal perimetro |
| E | Voce e pagina proprietario nello stile esistente; aggiunta e separazione | Versioni obsolete rifiutate, conferme e revoche provate nel browser |
| F | Qualifica integrata, migrazione/recupero, candidato da rivedere | Diff esatto e tutti i criteri critici positivi prima di qualunque rilascio |

Nessun lotto è stato implementato. L'ordine serve a evitare una voce funzionante solo graficamente o una ricerca condivisa senza garanzie su scrittura, storico e separazione.

## Prove esecutive da preparare nel candidato

Esecuzione solo nel runtime ufficiale isolato; leggere e attestare nuovamente il protocollo prima dei test. Le suite esistenti di riferimento includono `SchedinaCustomerPickerTest`, `SchedinaCustomerReferenceSecurityTest`, `ArriviCustomerReferenceSecurityTest`, `CustomerExchangeAreaTest`, `CustomerImportedFiltersDeleteTest`, `CustomerWizardTest`, `StrutturaAuthorizationTest` e i test Cestino pertinenti. Non eseguite ora.

Nuove fixture: proprietario P con hotel A/B/C/D, proprietario Q con hotel E; gruppo A/B, C e D esclusi; utenti dei ruoli esistenti; identità sintetiche con omonimi, dati incompleti, documenti rinnovati, residenze diverse, codici esterni uguali fra hotel e consensi differenti. Nessun CSV nominativo reale nelle fixture.

Accettazione minima: A/B condividono ricerca e aggiornamenti, C/D/E non enumerano né selezionano; aggiunta esplicita C; esclusione efficace da ogni endpoint e ripristino; check-in in due hotel con storico corretto; nuovo cliente concorrente senza doppio inserimento; aggiornamento concorrente con conflitto; Web pubblico non ricerca persone; caricamento ripetuto senza duplicati; fusione annullabile; documenti/residenze non mescolati; export e consensi locali; separazione senza perdita e senza accesso generale; interruzioni di preparazione/attivazione/recupero; browser desktop/tablet/telefono senza cambi grafici fuori scope.

## Stato e prossimo passo

**VERIFICATO staticamente:** inventario testuale, lettura dei punti critici e piano candidato. **PENDENTE:** codice, migrazioni effettive, prove dinamiche e dati reali. Rischio del piano documentale BASSO; rischio dell'implementazione ALTO per identità, concorrenza e separazione dei perimetri. Nessuna valutazione di sicurezza completa dedotta dalla sola lettura.

Prima di passare al lotto A deve essere esplicito il nuovo scope: modifiche applicative limitate alla condivisione clienti e ai suoi chiamanti necessari, conservando il layout e con la sola aggiunta concordata nell'area proprietario. Il precedente vincolo «non modificare codice» resta valido per la fase documentale corrente. Implementazione non autorizza automaticamente migrazioni su Herd/produzione, import reale o rilascio.

Documentazione modificata SÌ; codice/test/GUI/configurazione/schema/dati modificati NO; test applicativi, commit, push, deploy, import e trasmissioni NO. Maestro aggiornato. Lavoro Git preesistente conservato.

## Censimento applicativo riproducibile

Comando di sola lettura: `rg -n -i 'clienti|customers|customer_id' app --glob '*.php'`.

| File | Righe corrispondenti |
| --- | ---: |
| `app/Console/Commands/CleanupComponentiOrfaniCommand.php` | 4 |
| `app/Console/Commands/ClientiEnrichGeoCsvCommand.php` | 5 |
| `app/Console/Commands/QaCheck.php` | 1 |
| `app/Console/Commands/QaTenancy.php` | 1 |
| `app/Console/Commands/RenumberClientiCommand.php` | 13 |
| `app/Console/Kernel.php` | 1 |
| `app/Http/Controllers/ArrivalsController.php` | 1 |
| `app/Http/Controllers/CalendarioController.php` | 3 |
| `app/Http/Controllers/CestinoController.php` | 1 |
| `app/Http/Controllers/ComponentiController.php` | 4 |
| `app/Http/Controllers/CrmController.php` | 3 |
| `app/Http/Controllers/CustomerController.php` | 41 |
| `app/Http/Controllers/CustomerExportController.php` | 29 |
| `app/Http/Controllers/CustomerImportController.php` | 15 |
| `app/Http/Controllers/GestioneOperativaController.php` | 5 |
| `app/Http/Controllers/GroupController.php` | 1 |
| `app/Http/Controllers/HelpCenterController.php` | 29 |
| `app/Http/Controllers/HomeController.php` | 6 |
| `app/Http/Controllers/QaController.php` | 1 |
| `app/Http/Controllers/SchedinaController.php` | 41 |
| `app/Http/Controllers/TipoClienteController.php` | 2 |
| `app/Http/Controllers/TitleController.php` | 1 |
| `app/Http/Controllers/WebCheckinController.php` | 1 |
| `app/Models/Componenti.php` | 2 |
| `app/Models/CustomerImportRow.php` | 4 |
| `app/Models/Customers.php` | 4 |
| `app/Models/Schedina.php` | 1 |
| `app/Models/SupportTicket.php` | 1 |
| `app/Services/CataloghiIndirizziStrutturaService.php` | 2 |
| `app/Services/CestinoService.php` | 10 |
| `app/Services/ClientiCsvGeoEnricher.php` | 4 |
| `app/Services/ComponentiImportService.php` | 1 |
| `app/Services/CustomerImportService.php` | 40 |
| `app/Services/CustomerInputValidation.php` | 2 |
| `app/Services/CustomerWizardValidation.php` | 2 |
| `app/Services/GruppiStrutturaService.php` | 6 |
| `app/Services/SchedinaAnagraficaValidation.php` | 1 |
| `app/Services/TitoliStrutturaService.php` | 2 |
| `app/Services/WebCheckinCircuito.php` | 3 |
| `app/Support/CustomerListReturn.php` | 3 |

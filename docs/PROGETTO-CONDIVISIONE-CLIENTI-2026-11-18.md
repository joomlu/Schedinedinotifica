# Condivisione clienti tra strutture — primo passo tecnico

Data: 18 novembre 2026. Stato: **PROGETTO DOCUMENTALE; IMPLEMENTAZIONE PENDENTE**.

Aggiornamento successivo: su autorizzazione esplicita del titolare è iniziata l'implementazione circoscritta; [candidato e blocco del collaudo](IMPLEMENTAZIONE-CONDIVISIONE-CLIENTI-2026-11-18.md). Questo documento conserva il requisito e il mandato del primo passo; il checkpoint corrente è nel Maestro. Il candidato non è attivo né qualificato e non autorizza import o rilascio.
Baseline: `codex/questura-modalita-struttura`, HEAD `df711e6c32f0cd984d09d4cf7d0253d66d70394a`.
Fonte prevalente: [Maestro](maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md). **BASELINE IN AUDIT invariata**.

## Mandato e limiti

Il primo passo autorizzato prepara il progetto tecnico. Nessuna modifica al codice applicativo, alla GUI, alle migrazioni o ai dati; nessun test Laravel, import, commit, push, deploy o invio. Le indicazioni di implementazione sotto sono proposte da qualificare, non funzioni esistenti o autorizzazioni operative.

Il titolare richiede una voce «Condivisione clienti» nel menu sinistro del proprietario, con stile e componenti esistenti. Il proprietario sceglie due o più delle proprie strutture: da quel momento utilizzano la stessa anagrafica nelle ricerche e nel check-in. Aggiungendo una struttura si estende il gruppo dopo verifica, senza condivisione implicita con tutte le strutture del proprietario. Si usano account e ruoli esistenti; non si introducono ruoli speciali.

Restano distinti: identità della persona, provenienza dagli archivi, appartenenza al pacchetto della struttura, soggiorni effettivi e visibilità concessa dal gruppo. La semplice consultazione non dimostra un soggiorno e non crea appartenenza storica.

## Evidenze nei sorgenti attuali

| Punto verificato | Comportamento attuale | Conseguenza |
| --- | --- | --- |
| `app/Models/Customers.php`, `struttura()` | Una scheda appartiene a una struttura tramite `struttura_id` | Non esprime da sola una persona condivisa tra hotel |
| `app/Models/Concerns/AppartieneAStruttura.php` | Scope operativo sulla struttura selezionata | Non estendere lo scope comune a tutte le entità |
| `app/Support/StrutturaAccess.php` | Autorizzazione basata su ruolo, struttura e proprietario | Riutilizzare per verificare l'identità del titolare; aggiungere decisione di condivisione distinta |
| `CustomerImportService::confirmImportedRow()` | Batch e cliente confermato vincolati alla stessa struttura | Non importare tre volte la proposta unificata |
| `CustomerImportService::findPotentialDuplicate()` | Cerca candidati nelle strutture autorizzate dello stesso proprietario | Non è consenso alla condivisione e non è un registro identitario |
| `SchedinaController::searchCustomers()` | Ricerca e selezione limitate alla struttura corrente | Richiede un resolver clienti dedicato al gruppo attivo |
| `SchedinaController::localizeChainCustomerForCurrentStruttura()` | Riutilizza un equivalente locale o replica il cliente | Il ramo condiviso deve evitare copie autonome e propagazione indiscriminata dei consensi |
| `CustomerController::buildCustomerHistoryContext()` | Storico da schedine effettive per `customer_id`, soggetto allo scope struttura | Occorre risolvere i collegamenti storici senza cambiare struttura alle schedine |
| `CustomerExportController` | Filtri e cancellazione riferiti alla struttura; export con consensi | Separare accesso anagrafico da autorizzazione a esportare o cancellare |
| `routes/web.php`, area proprietario; `layouts/sidebar.blade.php` | Area con ruolo proprietario e voce Strutture | Punto di ingresso della sola aggiunta UI richiesta |
| `SchedinaCustomerPickerTest`, `CustomerExchangeAreaTest`, `SchedinaCustomerReferenceSecurityTest`, `ArriviCustomerReferenceSecurityTest` | Casi esistenti su isolamento, duplicati e riuso locale | Conservare i negativi attuali senza gruppo; nuovi positivi solo con gruppo esplicito |

Ricognizione statica di sorgenti e migrazioni, non interrogazione dello schema o della configurazione reali. Nessuna prova dinamica o verifica globale di assenza di altri percorsi implicita.

## Regole funzionali da implementare

### Attivazione e composizione del gruppo

1. Il proprietario vede esclusivamente le proprie strutture attive e selezionabili. Il backend ricalcola l'appartenenza; non si fida degli ID inviati.
2. Seleziona le strutture, vede il riepilogo della condivisione e i candidati duplicati. Due gruppi disgiunti dello stesso proprietario rimangono isolati.
3. La conferma crea una decisione versionata con autore, strutture, ambito e data. Un conflitto di versione richiede un nuovo riepilogo.
4. Prima dell'attivazione si prepara il collegamento delle anagrafiche e si revisionano i casi dubbi. Finché il processo non termina con successo, resta operativo il comportamento precedente; niente visibilità parziale.
5. Ricerche e nuove registrazioni utilizzano il gruppo attivo risolto dal server. Nessun gruppo significa comportamento attuale per struttura.
6. Aggiungere un hotel ripete controllo di appartenenza, confronto duplicati e conferma. La proprietà comune da sola non attiva la condivisione.

Proposta prudenziale: una struttura appartiene a un solo gruppo attivo. La modifica di gruppi sovrapposti non produce un'unione transitiva silenziosa. Per collegare gruppi distinti serve un'operazione esplicita che presenti tutte le strutture coinvolte. Questa restrizione organizzativa è una proposta tecnica da validare, non un requisito già confermato dal titolare.

### Persona e pacchetto di ogni struttura

- Anagrafica canonica unica nel perimetro condiviso, con collegamenti alle schede e ai codici precedenti. Nessuna unificazione automatica fra proprietari diversi.
- Ogni struttura conserva la propria provenienza anagrafica, i codici originali e le relazioni con soggiorni reali. Si possono avere più provenienze per una persona.
- Una nuova anagrafica nasce nel gruppo, con struttura di registrazione tracciata. L'accessibilità agli altri hotel non crea soggiorni né arrivi.
- Dati comuni aggiornati attraverso gli utenti esistenti delle strutture partecipanti; verifica del gruppo a ogni richiesta, controllo di versione per non sovrascrivere modifiche concorrenti e audit con hotel/autore/campi modificati.
- Classificazioni e cataloghi locali non diventano identificativi globali: riconciliare titoli, gruppi CRM, tipi cliente e indirizzi senza copiare ID di cataloghi di un hotel in un altro.
- Il tipo alloggiato appartiene al soggiorno. Residenza e nascita rimangono blocchi separati; il luogo di rilascio del documento è un terzo contesto.
- Il dato condiviso non autorizza automaticamente marketing, export, invii o accesso alle configurazioni degli altri hotel. Non promuovere consensi mancanti o riferiti a una sola struttura a consensi del gruppo.

### Storico

Ogni check-in effettivo collega la persona e la struttura ospitante, mantenendo date e riferimenti del soggiorno. I documenti e gli snapshot di soggiorni preesistenti non vengono riscritti quando cambia l'anagrafica comune. Lo storico consultabile deve indicare hotel e date e rispettare i permessi del gruppo, senza includere automaticamente note operative riservate o configurazioni della struttura.

I tre CSV ricevuti documentano provenienze anagrafiche, non date o soggiorni. Per ricostruire questi ultimi occorrono esportazioni dedicate, con collegamenti verificabili. Non trattare l'origine del file come prova di presenza fisica.

### Duplicati e correzioni

- Confronto prima dell'unione di archivi e prima della creazione di una nuova persona. Ricerca del cliente esistente e riuso, con controllo concorrente sul salvataggio.
- Nome/cognome da soli, recapito condiviso e CAP non autorizzano una fusione. Dati incompleti o discordanti producono un caso da revisionare.
- Confronto dei gruppi complessivi, non solo coppie isolate: nessuna catena di unioni con identità contraddittorie.
- Anteprima della scheda principale, integrazioni compatibili, alternative preservate con fonte e codici. La completezza non dimostra attualità.
- Identificativi applicativi stabili e registro di unificazione: i codici provvisori dei file non sono chiavi applicative.
- Fusione annullabile con provenienza dei singoli dati e delle relazioni: in caso di errore, redistribuire i collegamenti storici secondo evidenza, senza attribuire automaticamente i nuovi soggiorni a entrambe le persone.
- Controllo anche dopo l'importazione; nessuna cancellazione massiva dei candidati dubbi. I documenti rinnovati possono appartenere alla stessa persona e restano versioni distinte.

### Residenze discordanti

Si conserva una persona. Le versioni compatibili possono integrare i mancanti; quelle discordanti restano alternative con hotel/codice di provenienza. Nessuna data inventata. In assenza di scelta attendibile, residenza principale non confermata e verifica alla reception; conferma futura con autore, hotel e data effettiva. Coerenza GEO e completezza non dimostrano residenza attuale.

La regola «nascita in Italia → cittadinanza Italiana» nei file è una derivazione richiesta dal titolare, con provenienza da conservare; non costituisce cittadinanza acquisita da documento né regola da applicare silenziosamente a ogni nuovo cliente del software.

### Separazione della struttura

Riepilogo e conferma esplicita del proprietario prima della consegna del pacchetto al nuovo gestore. La struttura separata perde accesso al gruppo generale; le altre conservano il proprio archivio e i dati già posseduti, senza cancellazione automatica.

Il pacchetto della struttura separata comprende clienti e storico attribuiti alla struttura secondo collegamenti verificati; almeno un soggiorno basta per mantenerne l'appartenenza. Le provenienze da vecchi archivi senza soggiorni vengono presentate separatamente per una decisione esplicita, senza scambiarle per soggiorni. Nessun accesso agli storici degli altri hotel per effetto della consegna.

La separazione crea un perimetro identitario indipendente: dati e aggiornamenti futuri non si propagano fra gruppi. Chiavi, sessioni, esportazioni pendenti, operazioni in coda e controlli su richieste già aperte devono rileggere la nuova autorizzazione. Anche la scelta di non consegnare il pacchetto è registrata, senza cancellare gli originali.

## Architettura proposta e compatibilità

Proposta additiva, da validare prima delle migrazioni:

| Concetto | Dati e vincoli necessari |
| --- | --- |
| Gruppo clienti | Proprietario, stato, versione; nessun gruppo implicito |
| Partecipazione struttura | Struttura, gruppo, intervallo di appartenenza; un gruppo attivo per struttura nella proposta iniziale |
| Persona canonica | Perimetro identitario, identificativo stabile, dati comuni e versione per aggiornamenti concorrenti |
| Riferimento locale | Scheda `clienti` esistente, persona canonica e provenienza; mantenere leggibili gli ID precedenti |
| Appartenenza/provenienza | Persona, struttura, tipo di evidenza, codice originale, sorgente import e riferimenti soggiorno distinti |
| Versioni dei dati | Residenza/documenti alternativi, fonte, eventuale conferma; non sovrascrivere gli snapshot storici |
| Registro operazioni | Conferma gruppo, aggiunta, fusione, annullamento, separazione, autore e idempotenza |

Non trasformare `clienti.struttura_id` in una lista né impostarlo a NULL per ottenere una visibilità generale. Conservare le schede locali esistenti come riferimenti di compatibilità, non come anagrafiche indipendenti da sincronizzare liberamente. Nei gruppi attivi le letture e gli aggiornamenti della persona passano da un servizio canonico; fuori gruppo resta il percorso attuale. La presenza di riferimenti locali non deve produrre più risultati della stessa persona nella ricerca del gruppo.

Prima di confermare questa architettura occorre inventariare tutti i lettori/scrittori diretti di `Customers`, SQL su `clienti`, componenti e snapshot. Se i percorsi non possono essere centralizzati con isolamento dimostrabile, il progetto va rivisto: questa ricognizione non certifica la completezza di una soluzione di compatibilità.

Lavoro previsto sui soli punti pertinenti: modelli/servizi clienti e policy dedicate; import/export e Cestino; ricerca/check-in in Schedina e Arrivi; riferimenti Componenti/WebCheck-in; nuova area del proprietario e sola voce sidebar richiesta; migrazioni additive e test sintetici. Non modificare il trait globale di tenancy per concedere accesso generalizzato a soggiorni, tasse o invii. Nessun ridisegno di login, vetrina, dashboard o formulari esistenti. Eventuali adattamenti minimi di provenienza/storico nelle viste clienti devono essere individuati nel diff prima dell'implementazione, non introdotti implicitamente.

## Importazione e passaggio dai file

1. Riconciliare proprietario reale e codici delle tre strutture, senza dedurli dai nomi dei file.
2. Inventariare eventuali clienti e riferimenti già presenti nel database autorizzato, in sola lettura e con output minimo; questa verifica non è stata eseguita.
3. Caricare in staging isolato persone, provenienze e alternative usando un manifest versionato. Vincolo di idempotenza su import/sorgente/hotel/codice originale e registro delle operazioni; ripetere il caricamento non crea clienti nuovi.
4. Separare schede candidate, sospese e identità da recuperare. I file preparati non sono già validati contro cataloghi applicativi o clienti reali esistenti.
5. Eseguire un'anteprima dei collegamenti, dei dati mancanti e dei conteggi di conservazione prima della conferma.
6. Importazione reale e attivazione sono fasi successive distinte; mai caricare la proposta unica una volta per hotel con l'importatore attuale.

Ultimo stato degli artefatti privati della chat: 6.162 anagrafiche proposte e 6.554 riferimenti originali conservati; 2.414 candidate alla prova, 2.634 sospese e 1.114 identità da recuperare. Le 58 persone con residenze discordanti hanno 131 versioni conservate. Sono risultati della preparazione precedente, non nuovi test, persone certificate o dati importati. I nominativi e i CSV reali restano fuori dal repository. La numerazione PROV è provvisoria e non va usata come chiave definitiva.

## Sequenza e criteri di accettazione

Aggiornamento del 18 novembre: [inventario dei riferimenti e piano esecutivo candidato](PIANO-ESECUTIVO-CONDIVISIONE-CLIENTI-2026-11-18.md) completati documentalmente. Nessuna implementazione avviata; schema e contratti restano da verificare nel runtime isolato. Individuato anche un arricchitore CSV preesistente che copia dati tra residenza e nascita: escluso dal nuovo percorso, senza modifica al suo codice.

| Fase | Risultato richiesto | Stato |
| --- | --- | --- |
| 1 — specifica e mappa delle evidenze | Questo documento, riferimento nel Maestro e limiti espliciti | COMPLETATA documentalmente |
| 2 — inventario dipendenze e progetto esecutivo | Censimento dei riferimenti, punti critici, schema logico e contratti candidati, piano migrazione/recupero | COMPLETATA documentalmente nel perimetro di ricerca; qualifica dinamica PENDENTE, nessuna implementazione autorizzata da questo documento |
| 3 — implementazione e prove isolate | Funzioni circoscritte e suite di accettazione sotto | PENDENTE |
| 4 — preparazione import reale | Codici reali verificati, confronto esistente, manifest, anteprima e conteggi | PENDENTE |
| 5 — rilascio e attivazione | Protocollo Maestro, recupero qualificato, revisione esplicita, autorizzazioni e verifica revisione servita | PENDENTE |

Prove obbligatorie future, esclusivamente nel runtime previsto da [TESTING_ISOLATION](TESTING_ISOLATION.md), con dati sintetici:

- Comportamento precedente invariato senza gruppo; due hotel scelti condividono, terzo escluso e altro proprietario sempre escluso anche mediante ID diretti, export e ricerca.
- Terzo aggiunto esplicitamente; due gruppi disgiunti non si vedono; rifiuto di hotel esteri o selezioni obsolete.
- Stessa persona ricercata una sola volta; check-in in hotel diversi produce soggiorni distinti senza copiare arrivi o cambiare gli snapshot precedenti.
- Aggiornamenti visibili al gruppo, con conflitto controllato fra due salvataggi contemporanei; revoca efficace anche su richieste aperte.
- Doppio clic e import ripetuto idempotenti; stesso codice originale in hotel diversi non collide; fusioni e annullamenti conservano tutte le provenienze.
- Omonimi, documenti rinnovati, identità incomplete, date anomale e contatti condivisi non producono fusioni arbitrarie; gruppi transitivi incoerenti rifiutati.
- Residenze discordanti conservate, conferma datata solo quando avvenuta; GEO non altera nascita a partire dalla residenza.
- Export e consensi verificati separatamente; cancellazione locale non elimina una persona usata altrove; Cestino non ripristina accessi revocati.
- Separazione simulata prima e dopo un soggiorno in più hotel, conferma proprietario, mantenimento in entrambe le parti e stop aggiornamenti condivisi; errore/interruzione non lascia accessi parziali.
- Migrazione e ripristino isolati con riconciliazione riferimenti storici e nessuna perdita di record; regressione UI desktop/tablet/telefono sulle sole superfici interessate, senza ridisegno.

## Consuntivo di questa fase

**VERIFICATO:** ricognizione statica dei punti riportati, definizione di scope e criteri. **PENDENTE:** inventario completo e dimostrazione dinamica della soluzione. Rischio delle sole modifiche documentali BASSO; rischio dell'implementazione ALTO per identità, dati personali e confini fra strutture. Nessuna attestazione di sicurezza globale, schema attivo o aggiornamento della produzione.

Modifiche: questo progetto e riferimento nel Maestro. Verifiche: diff e coerenza dei riferimenti locali. Nessuna suite applicativa eseguita o modificata. Lavoro Git preesistente preservato; nessuna operazione Git mutante. Prossimo passo: completare l'inventario di dipendenze e il progetto esecutivo, senza attivare condivisione o importazione.

Codice modificato NO; test modificati NO; documentazione modificata SÌ; configurazione modificata NO; schema/dati modificati NO; test applicativi eseguiti NO; commit NO; push NO; deploy NO; trasmissioni esterne NO.

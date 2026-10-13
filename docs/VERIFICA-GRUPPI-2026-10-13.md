# Verifica del modulo Gruppi — 13 ottobre 2026

Stato generale **BASELINE IN AUDIT**. Rischio ALTO per isolamento, associazioni memorizzate per nome e migrazione. L’utente ha autorizzato correzioni, migrazione conservativa necessaria, documentazione, commit, push e rilascio C5 con checkpoint recuperabile. Il rilascio è ancora PENDENTE nella presente revisione preparatoria.

## Regola definitiva e modello effettivo

I Gruppi I/II/III sono classificazioni di clienti e schedine. Sono distinti dal CAPO GRUPPO e dai MEMBRI GRUPPO: questi ultimi usano la schedina principale e `componenti.schedina_id`, con `struttura_id` coerente. Nessuna nuova regola Questura/ISTAT o trasmissione.

Base madre unica: i 135 esempi ufficiali di `GruppiSeeder` (15/40/80), trasferiti senza alterarne i valori in `config/gruppi.php`. Non esiste una nuova gestione della base madre. Ogni struttura riceve record propri con `struttura_id` e padri interni allo stesso tenant. Il marcatore `struttura.gruppi_inizializzati_at` impedisce ai successivi seed di sovrascrivere nomi, descrizioni, gerarchie o di ricreare elementi eliminati. Anche il nome storico `GruppoSeeder` delega allo stesso percorso. Inizializzazione delle nuove strutture nel normale evento `created`; nessun pulsante «Personalizza».

CRUD nel contesto autorizzato tramite `RequireAuthorizedStruttura` e `StrutturaAccess` esistenti. Admin e Superadmin operano sulla struttura corrente; mantenuta l’eccezione operativa preesistente dell’Admin per strutture legacy senza proprietario, senza ampliarla. Proprietari e utenti struttura sono limitati alla membership esistente. Gruppi globali legacy esclusi dalle query operative, nessuna visibilità di cataloghi privati di altri tenant. Controllo del padre sia server sia FK composita `(struttura_id,parent_id)`.

Relazioni effettive: `gruppi.parent_id`; `clienti.group/subgroup/subgroup1`; `schedina.customer_group/customer_subgroup/customer_subgroup1`; `schedina.customer_id`; componenti collegati alla schedina, senza FK a Gruppi. Importazioni clienti e filtri/export utilizzano i medesimi campi; i cataloghi letti attraverso Gruppo seguono il nuovo scope. Le rinomine aggiornano soltanto la catena esatta della struttura interessata, comprese classificazioni nei payload Cliente/Schedina del Cestino. Identità, dati anagrafici, componenti, ricevute/export già prodotti e collegamenti `customer_id`/`schedina_id` non riscritti.

Validazione della catena completa e univoca; nomi omonimi sotto padri differenti consentiti, duplicati nello stesso livello/padre/struttura rifiutati. Nuove associazioni estranee o inesistenti respinte, anche nelle bozze e durante persistenza via modelli. Classificazioni storiche irrisolte possono essere conservate identiche, senza assegnazione arbitraria; i formulari le espongono come valori storici. Cancellazione bloccata con figli o utilizzi attivi/archiviati; cambio di livello/padre bloccato se invalida figli o associazioni. Rinomina consentita nel normale formulario. Serializzazione mediante lock della struttura per rinomina, salvataggi collegati, archiviazione e ripristino.

## Difetti individuati e correzioni

- Catalogo globale modificabile indistintamente: introdotta l’appartenenza per struttura prescritta dall’utente.
- Cancellazione del padre lasciava livelli II/III senza padre; cambio di livello poteva invalidare figli o creare cicli. Tre regressioni prima della correzione: tre fallimenti riprodotti.
- Rinomine/cancellazioni potevano perdere i riferimenti testuali di clienti e schedine: rinomina coerente nel tenant e vincoli di utilizzo.
- Validazione clienti basata sul primo omonimo globale: risoluzione dell’intera catena nel tenant.
- Modali dentro `tbody`, annullamento che lasciava valori modificati, scelta automatica del primo padre e perdita degli input su errore: markup corretto, reset agli originali, scelta esplicita e riapertura del formulario pertinente. Gestione locale univoca degli eventi delle sole modali Gruppi.
- ID non numerici, ricerca/payload malformati e rendering degli input errati: rifiuti controllati e valori scalari nel formulario.
- Ripristino di un figlio senza padre valido: errore italiano e snapshot conservato; ripristino padre→figlio verificato.

## Migrazione e conservazione

Unica nuova migrazione `2026_10_13_210000_isolate_gruppi_per_struttura`: aggiunge appartenenza/FK/indici e marcatore; inizializza la base privata; ricopia le ulteriori catene solo quando univoche nei riferimenti effettivi di ciascuna struttura. Un gruppo condiviso usato da più strutture viene ricostruito per ciascuna, senza modificare i valori dei clienti o delle schedine. Originali globali conservati, non cancellati né assegnati arbitrariamente. I casi irrisolti restano storici; relativo stato nel documento dei pendenti già esistente. Nessun reset del DB operativo. `down` esplicitamente non distruttivo: recupero esclusivamente dal checkpoint coordinato.

Preflight produttivo in sola lettura: SHA `f960f7ac6746403922c77e959097509b2ed9d96b`, Git pulito, manutenzione assente; 137 gruppi globali, 20 gerarchie incoerenti, 2 omonimie nello stesso livello, 14 clienti, 35 schedine; zero componenti orfani o con tenant discordante. Queste anomalie sono PREESISTENTI e non sono dati di prova. Lo schema quotidiano e i dati MySQL/Herd non usati per i test.

## Prove e limiti reali

VM ufficiale `schedine-sviluppo-interno`, PHP8.3.33/MariaDB11.8.9, namespace senza rete, DB `test_geo_…` e filesystem propri. Build e controllo GEO immutabile, identità/riconnessione e 23 rifiuti di override PASS. Selezione mirata: 62 test/380 asserzioni PASS. Include CRUD/validazioni/ricerca/paginazione; due strutture dalla stessa base; rinomina/nuova struttura; avvio effettivo di entrambi i seed; cancellazione di un default senza ricreazione; ruoli/ID/parenti estranei/CSRF; clienti/schedine/componenti; bozze; archivio/ripristino; conservazione degli ambigui; FK composita e payload malformati. Guardie: 15 PASS.

Prova separata di migrazione e recupero: 1 test/23 asserzioni PASS; schema predecessore149 ricostruito solo nel DB effimero; due strutture sintetiche e classificazione condivisa. DDL/DML reali, 137 gruppi per struttura, clienti/schedine invariati; errore sintetico DOPO mutazione; restore SQL integrale e ridump byte-identico, schema precedente e originali recuperati. È una prova di recupero DB dopo mutazione, non un nuovo SIGKILL/reboot del guardiano C5.

Chromium reale attraverso proxy attestato: due flussi completi definitivi PASS in34secondi con gestione locale univoca delle modali. Elenco/ricerca automatica/Invio/paginazione; creazione/modifica; campi obbligatori e padre; duplicati e valori conservati; annullamento; eliminazione rifiutata/annullata/riuscita; rinomina visibile nelle schede Cliente e Schedina; seconda struttura con base originale; richiesta PUT con CSRF valido su ID estraneo404. Nessun falso PASS sui tentativi interrotti.

Comandi esatti e diagnostici privati: `output/verifica-gruppi-2026-10-13/test-tenant.py`, `recovery.py`, `browser.py`; riusano il launcher ufficiale con selezioni esplicite. Tre adattatori transazionali in `tests/Diagnostics` mantengono immutate le suite storiche SchedinaStore/WebCheckinGroupAtomicity/SchedinaCustomerReferenceSecurity, evitando il loro setup `migrate:fresh` incompatibile con la migrazione storica classificazioni. Inclusione facoltativa dei soli due seed Gruppi nel launcher; nessun seeder implicito o guardia indebolita. Template locale dello sviluppo preesistente adeguato, lavoro estraneo preservato.

Fallimenti intermedi conservati: cache Python compilata legata al vecchio SHA, fixture struttura incompleta, setup storico classificazioni, aspettativa errata su Admin legacy, rilettura di un modello Eloquent già eliminato, Chromium inizialmente assente, selettori/conferme e apertura modali intermittente. Corretti o delimitati, mai sommati ai PASS. Lint Python inizialmente impedito dal percorso cache macOS: verifica con `compile()` senza scritture PASS.

Browser headless ufficiale Playwright e librerie ufficiali Rocky predisposti solo nella VM. Rimossa dopo controllo processi/mount soltanto la variante Chromium completa inutilizzata scaricata da questa task: 631.115.776 byte fisici recuperati; headless e librerie conservati. VM libero2.318.520.320byte, Mac86GiB, server257.161.342.976byte alla misura; preflight da riconfermare prima del checkpoint. Ultimo S0: file logici624.500.022byte, cifrati28.512.832byte, non nuova misura del picco.

I singoli launcher hanno fermato e rimosso soltanto le proprie risorse effimere; nessun record fittizio in produzione, nessuna cancellazione dei dati reali o dei default quotidiani, nessun invio enti/email. Nessuna globale indiscriminata.

## Git e rilascio

HEAD iniziale locale `6806f779bdd099fe60fc63b6f6c98d833632c27e`, branch main; numerosi documenti/artefatti preesistenti fuori scope. Staging selettivo, inclusione della sola voce nuova del Maestro nell’indice per preservare il suo delta preesistente. Commit, push, C5, SHA online, conservazione dopo DDL e acceptance autenticata saranno registrati qui soltanto dopo esecuzione effettiva.

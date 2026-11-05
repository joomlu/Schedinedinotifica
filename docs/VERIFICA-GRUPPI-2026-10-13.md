# Verifica del modulo Gruppi — 13 ottobre 2026

Stato generale **BASELINE IN AUDIT**. Rischio ALTO per isolamento, associazioni memorizzate per nome e migrazione. L’utente ha autorizzato correzioni, migrazione conservativa necessaria, documentazione, commit, push e rilascio C5 con checkpoint recuperabile. Rilascio applicativo C5 e verifiche online **VERIFICATI nello scope eseguibile**; residui puntuali nel registro dei pendenti.

## Regola definitiva e modello effettivo

I Gruppi I/II/III sono classificazioni di clienti e schedine. Sono distinti dal CAPO GRUPPO e dai MEMBRI GRUPPO: questi ultimi usano la schedina principale e `componenti.schedina_id`, con `struttura_id` coerente. Nessuna nuova regola Questura/ISTAT o trasmissione.

Base madre unica: i 135 esempi ufficiali di `GruppiSeeder` (15/40/80), trasferiti senza alterarne i valori in `config/gruppi.php`. Non esiste una nuova gestione della base madre. Ogni struttura riceve record propri con `struttura_id` e padri interni allo stesso tenant. Il marcatore `struttura.gruppi_inizializzati_at` impedisce ai successivi seed di sovrascrivere nomi, descrizioni, gerarchie o di ricreare elementi eliminati. Anche il nome storico `GruppoSeeder` delega allo stesso percorso. Inizializzazione delle nuove strutture nel normale evento `created`; nessun pulsante «Personalizza».

CRUD nel contesto autorizzato tramite `RequireAuthorizedStruttura` e `StrutturaAccess` esistenti. Admin e Superadmin operano sulla struttura corrente; mantenuta l’eccezione operativa preesistente dell’Admin per strutture legacy senza proprietario, senza ampliarla. Proprietari e utenti struttura sono limitati alla membership esistente. Gruppi globali legacy esclusi dalle query operative, nessuna visibilità di cataloghi privati di altri tenant. Controllo del padre sia server sia FK composita `(struttura_id,parent_id)`.

Relazioni effettive: `gruppi.parent_id`; `clienti.group/subgroup/subgroup1`; `schedina.customer_group/customer_subgroup/customer_subgroup1`; `schedina.customer_id`; componenti collegati alla schedina, senza FK a Gruppi. Importazioni clienti e filtri/export utilizzano i medesimi campi; cancellazione vincolata anche dagli import ancora da confermare, rinomina estesa alle classificazioni normalizzate, lock della struttura prima della conferma/revisione. I cataloghi letti attraverso Gruppo seguono il nuovo scope. Le rinomine aggiornano soltanto la catena esatta della struttura interessata, comprese classificazioni nei payload Cliente/Schedina e schedina annidata Web Check-in del Cestino e nei payload normalizzati degli import clienti; CSV e raw_payload intatti. Identità, dati anagrafici, componenti, ricevute/export già prodotti e collegamenti `customer_id`/`schedina_id` non riscritti.

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

Prima migrazione `2026_10_13_210000_isolate_gruppi_per_struttura`: aggiunge appartenenza/FK/indici e marcatore; inizializza la base privata; ricopia le ulteriori catene solo quando univoche nei riferimenti effettivi di ciascuna struttura. Un gruppo condiviso usato da più strutture viene ricostruito per ciascuna, senza modificare i valori dei clienti o delle schedine. Originali globali conservati, non cancellati né assegnati arbitrariamente. I casi irrisolti restano storici; relativo stato nel documento dei pendenti già esistente. Seconda migrazione `2026_10_13_210100_recover_gruppi_archivi_import`: ricostruisce anche gli utilizzi presenti soltanto negli archivi o negli import e il nome valido «0», senza riscrivere i payload. Nessun reset del DB operativo. `down` esplicitamente non distruttivo: recupero esclusivamente dal checkpoint coordinato.

Preflight produttivo in sola lettura: SHA `f960f7ac6746403922c77e959097509b2ed9d96b`, Git pulito, manutenzione assente; 137 gruppi globali, 20 gerarchie incoerenti, 2 omonimie nello stesso livello, 14 clienti, 35 schedine; zero componenti orfani o con tenant discordante. Queste anomalie sono PREESISTENTI e non sono dati di prova. Lo schema quotidiano e i dati MySQL/Herd non usati per i test.

## Prove e limiti reali

VM ufficiale `schedine-sviluppo-interno`, PHP8.3.33/MariaDB11.8.9, namespace senza rete, DB `test_geo_…` e filesystem propri. Build e controllo GEO immutabile, identità/riconnessione e 23 rifiuti di override PASS. Selezione mirata: 66 test/418 asserzioni PASS. Include CRUD/validazioni/ricerca/paginazione; due strutture dalla stessa base; rinomina/nuova struttura; avvio effettivo di entrambi i seed; cancellazione di un default senza ricreazione; ruoli/ID/parenti estranei/CSRF; clienti/schedine/componenti; bozze; archivio/ripristino; conservazione degli ambigui; FK composita e payload malformati. Guardie: 15 PASS.

Prova separata di migrazione e recupero: 1 test/23 asserzioni PASS; schema predecessore149 ricostruito solo nel DB effimero; due strutture sintetiche e classificazione condivisa. DDL/DML reali, 137 gruppi per struttura, clienti/schedine invariati; errore sintetico DOPO entrambe le migrazioni e mutazione; restore SQL integrale e ridump byte-identico, schema precedente e originali recuperati. È una prova di recupero DB dopo mutazione, non un nuovo SIGKILL/reboot del guardiano C5.

Chromium reale attraverso proxy attestato: due flussi completi definitivi PASS in35,7secondi con gestione locale univoca delle modali. Elenco/ricerca automatica/Invio/paginazione; creazione/modifica; campi obbligatori e padre; duplicati e valori conservati; annullamento; eliminazione rifiutata/annullata/riuscita; rinomina visibile nelle schede Cliente e Schedina; seconda struttura con base originale; richiesta PUT con CSRF valido su ID estraneo404. Nessun falso PASS sui tentativi interrotti.

Comandi esatti e diagnostici privati: `output/verifica-gruppi-2026-10-13/test-tenant.py`, `recovery.py`, `browser.py`; riusano il launcher ufficiale con selezioni esplicite. Tre adattatori transazionali in `tests/Diagnostics` mantengono immutate le suite storiche SchedinaStore/WebCheckinGroupAtomicity/SchedinaCustomerReferenceSecurity, evitando il loro setup `migrate:fresh` incompatibile con la migrazione storica classificazioni. Inclusione facoltativa dei soli due seed Gruppi nel launcher; nessun seeder implicito o guardia indebolita. Template locale dello sviluppo preesistente adeguato, lavoro estraneo preservato.

Fallimenti intermedi conservati: cache Python compilata legata al vecchio SHA, fixture struttura incompleta, setup storico classificazioni, aspettativa errata su Admin legacy, rilettura di un modello Eloquent già eliminato, Chromium inizialmente assente, selettori/conferme e apertura modali intermittente. Corretti o delimitati, mai sommati ai PASS. Lint Python inizialmente impedito dal percorso cache macOS: verifica con `compile()` senza scritture PASS.

Browser headless ufficiale Playwright e librerie ufficiali Rocky predisposti solo nella VM. Rimossa dopo controllo processi/mount soltanto la variante Chromium completa inutilizzata scaricata da questa task: 631.115.776 byte fisici recuperati; headless e librerie conservati. VM libero2.318.520.320byte, Mac86GiB, server257.161.342.976byte alla misura; preflight da riconfermare prima del checkpoint. Ultimo S0: file logici624.500.022byte, cifrati28.512.832byte, non nuova misura del picco.

I singoli launcher hanno fermato e rimosso soltanto le proprie risorse effimere; nessun record fittizio in produzione, nessuna cancellazione dei dati reali o dei default quotidiani, nessun invio enti/email. Nessuna globale indiscriminata.

## Git e rilascio

HEAD iniziale locale `6806f779bdd099fe60fc63b6f6c98d833632c27e`, branch main; numerosi documenti/artefatti preesistenti fuori scope. Staging selettivo, inclusione della sola voce nuova del Maestro nell’indice per preservare il suo delta preesistente. Commit applicativi `d7b79ee` e `1e2c5e7` push eseguiti; pubblicazione e acceptance effettive riportate di seguito. Il consuntivo e l’ultima prova HTTP entrano nel commit conclusivo della task; SHA del commit conclusivo e allineamento finale vengono attestati prima della risposta, evitando riferimenti autoreferenziali al proprio hash.

Aggiornamento preparatorio: primo candidato `d7b79ee23b6cbb1255d99ca240e0b8a7ed21f598` registrato e push eseguito, non pubblicato. PREPARE indipendente PASS; sole copie decifrate temporanee verificate rimosse, cifrati/chiavi/dump preservati. Ambiente quotidiano VM: checkpoint privato cifrato/riletto, schema149→150; tutti i 22.760 record precedenti in64tabelle confrontati su tutte le colonne originali e identici;137 globali preservati e946 privati, zero padri cross-tenant. MySQL/Herd originale non usato. Ultimo delta archivi/import verificato65/402PASS; avvio precedente interrotto per capienza e confronto fixture poi corretto, diagnostici conservati.

Ripetizione del browser con il delta archivi/import:2flussi PASS/35,7secondi; recupero1test/23asserzioni dopo entrambe le migrazioni PASS. La prova Web Check-in completa usa archiviazione/ripristino effettivi; i nuovi ID di schedina/componenti sono il comportamento già previsto dal Cestino, non una modifica della task.

## File modificati nello scope

- `app/Http/Controllers/CustomerController.php`
- `app/Http/Controllers/GroupController.php`
- `app/Models/Customers.php`
- `app/Models/Gruppo.php`
- `app/Models/Schedina.php`
- `app/Models/Struttura.php`
- `app/Services/CestinoService.php`
- `app/Services/CustomerImportService.php`
- `app/Services/GruppiStrutturaService.php`
- `config/gruppi.php`
- `database/migrations/2026_10_13_210000_isolate_gruppi_per_struttura.php`
- `database/migrations/2026_10_13_210100_recover_gruppi_archivi_import.php`
- `database/seeders/GruppiSeeder.php`
- `database/seeders/GruppoSeeder.php`
- `docs/PENDENTI-LOCALI-ROSS1000-DIPENDENZE-2026-10-09.md`
- `docs/TESTING_ISOLATION.md`
- `docs/VERIFICA-GRUPPI-2026-10-13.md`
- `docs/maestro/MAESTRO-SCHEDINE-DI-NOTIFICA.md`
- `resources/js/components/filtri/customer-group-cascade.js`
- `resources/views/customers/partials/scheda-cliente-form.blade.php`
- `resources/views/gruppi/index.blade.php`
- `resources/views/schedina/partials/form.blade.php`
- `routes/web.php`
- `tests/Diagnostics/GruppiMigrationRecoveryTest.php`
- `tests/Diagnostics/SchedinaCustomerReferenceSecurityTestGruppiAuditTest.php`
- `tests/Diagnostics/SchedinaStoreTestGruppiAuditTest.php`
- `tests/Diagnostics/WebCheckinGroupAtomicityTestGruppiAuditTest.php`
- `tests/Feature/GruppiIntegrityTest.php`
- `tests/Feature/GruppiTenantTest.php`
- `tests/Feature/gruppi.playwright.spec.js`
- `tests/Isolation/run.py`
- `tests/Isolation/seed-gruppi.php`

Candidato completo `1e2c5e770acae7d7c8fa16b76d5ca20eb16c1d3e` commit/push PASS. Locale VM schema150→151: checkpoint cifrato/riletto,23.707record precedenti/64tabelle identici su tutte le colonne,946privati/137globali. PREPARE online1 interrotto per capienza durante inizializzazione DB della copia, prima di qualsiasi DDL/manutenzione produttiva; nessun PASS attribuito. Estratto e HMAC verificati, soli derivati rimossi e diagnostico parziale conservato; la soglia immediata di spazio della pulizia non era ancora raggiunta (rilascio differito dei blocchi). Due copie VM cifrate superate custodite/rilette nel Mac FileVault e originali server conservati; bundle/mutabili plaintext temporanei locali rimossi soltanto dopo decifratura streaming/hash dei cifrati autentici; dump/chiavi/checkpoint/diagnostici preservati. Capienza effettiva riconfermata1.948.262.400byte. Nuovo piano/base/approval PREPARE2, nessun riuso del fallimento.

## Rilascio e stato finale attestato

Ultima selezione **66test/418asserzioni PASS**: ramo HTTP dei modelli abilitato esplicitamente nell’ultima prova, richieste dirette su schedine/bozze con cataloghi estranei respinte422, salvataggio valido/rimozione e rollback provati. Nessun nuovo codice applicativo dopo `1e2c5e7`; la prova aggiuntiva chiude un limite del test CLI. Browser isolato2flussi/35,7s e recupero1/23PASS già descritti. Nessuna suite generale dichiarata.

PREPARE2STOP per inventario mutato, prima di manutenzione/DDL; confronto diagnostico TAR corretto per precisione dei timestamp:125nuove sessioni e1metadato rispetto al TAR, non attribuzione della causa originale (inventario iniziale non persistito dal ramo fallito). PREPARE3PASS con nuova base/piano, recupero indipendente completo. Custodia su Mac FileVault di cache Chromium/FFmpeg e cifrati dei checkpoint locali dopo rilettura/hash/HMAC; chiavi/dump/diagnostici nella VM. Margine effettivo2.503.819.264byte riconfermato prima della fase finale; soli derivati della task, nessuna pulizia indiscriminata.

RUN1STOP al gate degli ingressi primaDDL; worker97,439s, causa specifica non attribuita dal diagnostico originale. Guardiano autonomo PASS/BASELINE_NON_MODIFICATA, riapertura e SHAf960/Gitpulito/DB/schema identici allo S0 della stessa finestra, chiave e9sondePASS. Copie e report conservati. RUN2con nuovo piano/finestra/S0 e osservatore in sola lettura, nessun indebolimento di presidi o modifica infrastrutturale. Un precontrollo aggiuntivo prudenziale dello spazio non propagava l’errore alla sequenza shell; errore operativo riconosciuto, capienza ricontrollata durante il recupero e propagazione corretta per le sequenze successive. Recupero completo obbligatorio primaDDL rimasto operativo e superato.

RUN2**PASS_AGGIORNAMENTO_C5**, commit applicativo `1e2c5e770acae7d7c8fa16b76d5ca20eb16c1d3e`/tree97520b785766f35e9e564a21740ea99083782e94. Piano311dcf10ff9abb18b7d6f1eba22be1e4bb921c650806a32574df0df0a5311127, manifest0f2956205979b03ba9ed9b0a68d8d01e4d53ec0327308033603906410e3ec55f. S0fresco indipendente verificato124,193s; riapertura/accettazione222,606s, due migrazioni149→151, Laravel12.69.3/64tabelle. Guardiano disarmato/inattivo/disabilitato, manutenzioni assenti, Gitproduzione pulito; presidi/key/env/provider/mail originali preservati.

Conservazione **64tabelle/35.512record precedenti su tutte le colonne originali identici allo S0**, mediante HMAC su dati HEX ordinati con timezoneUTC, DB recuperato senza rete e sigilli autentici. Escluse esclusivamente colonne nuove/migrazioni nuove e gruppi privati aggiunti;137globali originali intatti. Verifica ripetuta dopo i GET nel browser PASS.946privati per7strutture, tutti inizializzati, nessun orfano/cross-tenant/gerarchia privata invalida/duplicato privato; clienti/schedine/componenti e JSON degli archivi coerenti.74persistenti preesistenti/env/upload/logo identici al checkpoint; nessun dato fittizio o invio enti. Adattatore HMAC inizialmente fermato per parametro `dump` mancante, corretto e ripetuto senza modificare dati/schema.

Browser online autenticato Superadmin, HOTEL GIOIELLA:135gruppi; ricerca Business4risultati, pulizia filtro, pagina2(11–20), apertura modali, nome obbligatorio/max100, bozza modifica→annulla→nome originale e nuova bozza→annulla→vuota PASS, nessun salvataggio su record reali. Formulari nuovi Cliente/Schedina:15radici, Business→Fiere/Meeting/Roadshow e Meeting→Board meeting/Team building PASS senza persistenza. Richiesta GETdiretta dell’ID953 di HOTEL GIOIELLA nel contesto Hotel Dream restituisce0risultati; contesto iniziale ripristinato. Schermata privata `output/verifica-gruppi-2026-10-13/gruppi-online.jpg`. Limite reale: apertura delle14/35schede originali esclusa dalla struttura già archiviata;20classificazioni import già needs_review senza origine globale, intatte. Identificativi e azioni necessarie **solo nel registro pendenti**, nessuna assegnazione arbitraria o riattivazione.

Monitoraggio reale **917,734s**,31campioni/279sonde HTTP-TLS-CSS PASS, FPM/key/flagsPASS; ulteriori9sonde e guardiano conclusoPASS. I launcher hanno rimosso tutti e soli i propri DB/processi/filesystem sintetici; default e dati reali preesistenti preservati. Cifrati, chiavi separate, dump, checkpoint e diagnostici custoditi. MySQL/Herd originale mai usato per i test. Lavoro Gitpreesistente mantenuto fuori staging. **BASELINE IN AUDIT** resta lo stato generale; nessuna affermazione di validazione completa fuori scope o dei controlli bloccati.

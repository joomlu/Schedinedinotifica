# Titoli privati, base protetta e rilascio verificato — 14 ottobre 2026

**Funzionalità, isolamento e rilascio applicativo verificati. BASELINE IN AUDIT resta lo stato generale del progetto.** Il consuntivo conclusivo viene pubblicato con la stessa C5, senza modifiche applicative o migrazioni, e l’allineamento dell’ultimo SHA viene attestato prima del verdetto finale.

La richiesta esplicita dell’utente autorizza analisi, codice, test, Maestro, commit, push e pubblicazione online. Sono stati corretti l’accesso globale ai titoli, lo stato deselezionato che rimaneva attivo e un ulteriore difetto delle conferme sul dominio reale. Modificati soltanto Titoli e le dipendenze necessarie per conservarne le relazioni. Nessun invio a enti o messaggio esterno.

## Base effettiva e provenienza

Prima del delta: SHA `fdd03d1fadf1d903c5a821754b525c6c185f494e`, 82 titoli globali, tutti attivi, verificati su nove pagine e 82 ID distinti. **80 nomi e descrizioni coincidono con il seeder** storico, presente dalla baseline `4d1f28b`; estratti senza alterazioni in `config/titoli.php`. Questo riscontro, non il solo conteggio, fonda la base ufficiale protetta di 80 titoli. La base resta disponibile per le nuove strutture.

ID 1 `Sig.s`/Signore e ID 4 `Sigg.`/Signori sono assenti dal seeder. Timestamp grezzi DB: creazione `2026-03-04 06:50:15`; aggiornamenti rispettivamente `06:50:51` e `06:52:17`. Nessun evento Git sulle stringhe prima di questa task, nessun audit Titoli e nessuna relazione corrente o archiviata attestano autore o contesto originario. **Provenienza indeterminata**: entrambi preservati globali, protetti, senza assegnazione a strutture né inclusione nella base 80. Non sono stati eliminati o classificati arbitrariamente. [Evidenza](evidenze/titoli-2026-10-14/provenienza-verificata.json) e [residuo documentato](PENDENTI-LOCALI-ROSS1000-DIPENDENZE-2026-10-09.md#titoli--limite-di-provenienza-del-14-ottobre-2026).

Le relazioni effettive usano stringhe `clienti.type` e `schedina.type`, senza FK verso gli ID Titolo: 12 clienti e 15 schedine, con Amm./Arch./Sig./Sig.ra nella struttura 1 già archiviata. Tutti i valori precedenti conservati. Le sette strutture, comprese tre archiviate, ricevono la propria base senza riattivazioni o cambi di membership. Le copie legacy ulteriori richiedono una fonte globale univoca e un’associazione già esistente; in assenza di evidenza non viene inventata un’appartenenza.

## Correzioni e comportamento risultante

Esaminata e riutilizzata la soluzione Gruppi: scope, middleware `RequireAuthorizedStruttura`, inizializzazione con marcatore e lock sulla struttura. Anche Superadmin opera sui titoli del contesto corrente. CRUD, ricerca, paginazione e Cestino non espongono o modificano titoli di altre strutture; gli ID globali non sono modificabili dal formulario.

Migrazione unica `2026_10_14_020000_isolate_titoli_per_struttura`: `struttura_id` nullable con FK restrittiva, `base_nome`, unique privati per nome/origine e `struttura.titoli_inizializzati_at`. Sostituito il unique globale sul nome; originali globali non spostati o riscritti. Il down rifiuta la perdita automatica: recupero attraverso checkpoint coordinato C5.

Ogni nuova struttura riceve 80 copie private. Seed ripetuti non sovrascrivono personalizzazioni, stati o cancellazioni. Recupero volontario aggiuntivo con modale, effetti espliciti e conferma: ripristina i nomi mancanti, conserva i titoli rinominati e ricrea separatamente il nome originale. Non modifica descrizioni, stati o relazioni già presenti, non elimina titoli personali e segnala i conflitti di nome. Il server richiede la conferma accepted; non esiste un reset distruttivo implicito.

Nome obbligatorio, massimo 50 caratteri; descrizione facoltativa, massimo 150; stato booleano valido. Hidden 0 e checkbox 1 garantiscono creazione e modifica inattive. Duplicati rifiutati nella stessa struttura, anche con diversa capitalizzazione o spazi esterni; lo stesso nome è consentito in strutture diverse. Abbreviazioni e forme simili restano record distinti, senza fusioni arbitrarie. Input malformati, CSRF e query non valide sono rifiutati; errori italiani e input validi conservati, con riapertura del solo modulo interessato.

Ricerca sulla forma completa normalizzata invece dell’OR fra frammenti: Dott.ssa restituisce il risultato pertinente; ricerca anche per descrizione e ID. Query limitata a 191 caratteri, ordine stabile, dieci righe per pagina e filtro mantenuto nella navigazione. Modali fuori dalla tabella, gestione locale univoca e reset dopo annullamento.

Dipendenze necessarie: selettori Cliente/Schedina privati e attivi; valori storici o inattivi già associati restano visibili e conservabili. Nuove selezioni estranee/inattive rifiutate dal server, anche tramite POST diretto e bozze. Rinomine transazionali nei riferimenti della stessa struttura e negli snapshot Customer/Schedina/WebCheck-in annidati. Eliminazione impedita se il titolo è utilizzato; è possibile disattivarlo. Titoli inutilizzati spostati nel Cestino privato, con ripristino autorizzato e controllo conflitti. Import non genera nuovi titoli: `type=null`; nessun raw import riscritto.

**Difetto delle conferme online:** il gestore UX condiviso cercava `/schedine` nell’azione completa del form, includendo il dominio `schedinedinotifica...`, e saltava la conferma dei POST/PUT Titoli. Corrette soltanto le due viste Titoli con azioni relative `/titolo`; gestore globale e altri formulari invariati. Regressione dedicata e prova sul dominio reale attestano la correzione.

## Verifiche funzionali complete

VM ufficiale senza rete, PHP 8.3.33/MariaDB 11.8.9; filesystem e DB effimeri attestati. Nessun test Laravel su MySQL/Herd, database quotidiano o produzione.

| Ambito | Risultato e prova |
|---|---|
| Base, nuove strutture e seed | 80 copie private, globali protetti; seed ripetuti conservano modifiche, stati e cancellati. |
| Creazione e modifica | Stato 0/1, stato assente, disattivazione e riattivazione persistiti correttamente. |
| Validazioni e duplicati | Limiti, campi obbligatori, array malformati, booleani, CSRF, trim e case-insensitive; ritorno alla vista senza errore 500. |
| Ruoli e ID manipolati | struttura_user, proprietario, Admin e Superadmin; ID estranei PUT/DELETE 404, contesti/query estranei respinti o senza dati; anonimo/inattivo/reception legacy bloccati. |
| Ricerca e paginazione | Punti, forme composte, descrizioni, ID, wildcard e pagine con filtro; isolamento sempre conservato. |
| Relazioni | Rinomine soltanto nello stesso tenant, archivi annidati, selezioni Cliente/Schedina/bozze e cancellazioni protette. |
| Cestino e recupero | Ripristino privato, conflitti, conferma obbligatoria; originali globali protetti. |
| Modali e messaggi | Apertura/chiusura, annullamento/reset, input dopo errori, conferme di salvataggio/modifica/eliminazione/recupero. |

Suite definitiva: **37 test / 403 asserzioni PASS**, TitoliTenant, GruppiIntegrity, GruppiTenant e TopbarRendering. Recupero migrazione: **1 test / 20 asserzioni PASS**, predecessore sintetico con unique globale, DDL/DML reali, riapplicazione idempotente, guasto dopo mutazione e ridump ripristinato byte-identico. Guardie 15 PASS; build/GEO e 23 override pericolosi respinti PASS. Il launcher include facoltativamente il seeder Titoli e la sua modale, senza seed impliciti o allentamenti dei controlli.

Chromium definitivo dopo la correzione delle conferme: **2 flussi / 46,1 secondi PASS**. CRUD completo, creazione/modifica inattiva, riattivazione, validazioni/duplicati/input conservati, annullamenti, entrambe le conferme, cancellazioni riuscite/rifiutate/annullate, recupero, ricerca/pagine e due utenti con ID estranei PUT/DELETE 404. [Log HTTP](evidenze/titoli-2026-10-14/http-conferme-pass.log) e [log browser](evidenze/titoli-2026-10-14/browser-conferme-pass.log).

I fallimenti diagnostici iniziali sono conservati: audit storico 4 test/38 asserzioni con due difetti riprodotti; prima copia effimera senza nuovi sorgenti; selettore senza icona e attesa del caricamento nel test browser; cache Python del precedente SHA; inizializzazione DB fermata per capienza. Corretti selezione dei sorgenti/attese/cache derivate, senza indebolire aspettative. I test storici `TitoliFormularioAudit.php` non fanno parte della suite definitiva.

## Rilascio, conservazione e browser online

Ambiente quotidiano VM aggiornato 151→152 con checkpoint cifrato/riletto: **64 tabelle / 23.708 record precedenti identici**, su tutte le colonne originali; 560 titoli privati e 82 globali, zero orfani. MySQL/Herd originale non usato.

Primo commit applicativo `da16122e246783eab6212e79261f6943f9815ffb`, pubblicato su GitHub e produzione con C5 vigente: S0 indipendente 136,384 secondi, accettazione/riapertura 254,159 secondi, sola migrazione Titoli. Conservazione autenticata di **64 tabelle / 36.460 record precedenti** su tutte le colonne originali; 74 persistenti, env/APP_KEY/upload/loghi identici. Monitor: **917,788 secondi, 31 campioni / 279 sonde PASS**.

Correzione delle conferme nel commit `230ec2e29d5520296f6674f2d515e1d63b10b580`, pubblicato su GitHub e produzione con lo stesso protocollo e nuovi piani, senza DDL. RUN4 accettato: **S0 indipendente 144,882 secondi, riapertura 228,444 secondi**. Online Laravel 12.69.3, 152 migrazioni, 64 tabelle, 560 privati/82 globali, zero orfani/duplicati/extra assegnati e marcatori presenti in tutte le sette strutture. **DB byte-identico allo S0 anche dopo il browser**; 74 persistenti/env/chiave/upload/loghi conservati, FPM attivo, manutenzione assente, provider false/mail array e nove sonde HTTP/TLS/CSS PASS. Guardiano disarmato, inattivo e disabilitato. [Ricevuta aggregata](evidenze/titoli-2026-10-14/rilascio-conferme-verificato.json).

Monitor definitivo del codice corretto: **917,953 secondi, 31 campioni / 279 sonde PASS**, su almeno 15 minuti reali. [Evidenza](evidenze/titoli-2026-10-14/monitor-conferme-finale.json).

Browser autenticato: otto pagine/80 ID privati distinti di HOTEL GIOIELLA; in Hotel Dream altri 80, con ID diversi (Amm.516 contro596), ricerca dell’ID596 con page2→0. Dott.ssa→un risultato/Dottoressa; descrizioni, azioni, stati e annullamenti verificati. Dopo il rilascio delle conferme, nel contesto effettivo Hotel Amalfi: recupero, modifica inattiva e creazione inattiva mostrano le conferme corrette; tutte annullate prima del salvataggio, 22 azioni relative verificate nel DOM. Nessun cambio forzato del contesto trovato al nuovo accesso. [Evidenza browser](evidenze/titoli-2026-10-14/browser-online-verificato.json).

La prova precedente del recupero aveva realmente inviato un POST a causa del difetto del dominio: 0 aggiunte, nessuna modifica a titoli/relazioni e confronto dei dati precedenti identico. Nessun record fittizio salvato online. Strutture archiviate non riattivate per le prove UI: relazioni reali verificate tramite SQL/conservazione e flussi equivalenti isolati; questo limite resta esplicito.

## Arresti C5 e custodia: nessun falso successo

PREPARE iniziale del primo rilascio fermato per capienza prima manutenzione/DDL; ripetizione con recupero indipendente PASS. Nella pubblicazione delle conferme, due PREPARE fermati per inventario mutato prima manutenzione; terzo PASS dopo la conclusione del monitor precedente. Causa specifica delle due variazioni non attribuita arbitrariamente.

RUN1 conferme: STOP durante preparazione del runner; guardiano recupera S0 in 42,657 secondi, riapertura 196,084 secondi. SHA baseline da16122e, dump/schema identici e nove sonde PASS, zero DDL; causa iniziale non ricostruita con certezza prima dell’arresto del worker. RUN2: nuovo S0 non attestato, baseline non modificata riaperta; eventi di sospensione del Mac e client SSH rimasti in attesa documentati. RUN3: recupero Linux fallito per errore 28/table full, prima del ridump; baseline immutata riaperta, zero DDL. Nessun tentativo fallito promosso a PASS. RUN4 è l’unico accettato per questo delta; kit/gate invariati.

Capienza recuperata conservativamente: soli duplicati cifrati attestati SHA/HMAC e riletti sul Mac FileVault; originali server e chiavi VM preservati. Soli derivati ricostruibili conclusi o del restore fallito chiusi dopo verifiche di contenuti/metadati/processi/mount; diagnostici del fallimento conservati. **Dump derivati Titoli custoditi integralmente in gzip privati nella stessa VM**, con fsync, decompressione/SHA identici e metadati di recupero: nessuna perdita o nuova trasmissione. Le copie originali server, i cifrati e le chiavi restano conservati. Per la sola base Titoli del RUN concluso, utilizzate due copie Mac già presenti dal PREPARE e nuovamente rilette, senza nuovo trasferimento. Mac mantenuto sveglio temporaneamente con caffeinate -i a scadenza; nessuna impostazione permanente modificata.

Un precedente rifiuto automatico sui tre duplicati cifrati fu superato dalla prova della seconda copia completa/riletta e l’azione venne approvata. Una successiva proposta di copia cifrata di un vecchio recupero Tassa sul Mac è stata respinta perché fuori scope: **non eseguita**. Alternativa autorizzata completata esclusivamente sui derivati Titoli, senza perdita e senza trasferimento di quel payload; nessun blocco residuo da approvare.

## Git e chiusura

Branch main. Modifiche documentali e untracked estranei preservati ed esclusi dai commit; Maestro aggiornato mediante staging selettivo del solo testo Titoli. Nessun refactoring generale, cambio di ruoli, infrastruttura, dipendenze, protocolli o trasmissione a enti.

Il commit conclusivo aggiunge soltanto consuntivo, Maestro ed evidenze. Prima del suo rilascio si verifica che il diff rispetto a230ec2e contenga esclusivamente `docs/`; si usa nuovamente C5 con S0 fresco e nessuna migrazione. SHA finale locale/GitHub/produzione e ricevuta di questa sola pubblicazione documentale sono attestati nel deposito operativo della task e nella risposta finale, evitando un hash autoreferenziale nel documento stesso. Nessun ulteriore test funzionale duplicato sul medesimo codice senza una modifica o un nuovo problema.

CODICE/TITOLI E DIPENDENZE NECESSARIE MODIFICATI: SI; TEST E DOCUMENTAZIONE: SI; SCHEMA: SOLA MIGRAZIONE TITOLI AUTORIZZATA; PRODUZIONE APPLICATIVA: PUBBLICATA E VERIFICATA; DATI PRECEDENTI/EXTRA/RELAZIONI: CONSERVATI; RECORD FITTIZI ONLINE: NO; INVII A ENTI/MESSAGGI: NO. **Verifica circoscritta a Titoli; BASELINE IN AUDIT generale invariata.**

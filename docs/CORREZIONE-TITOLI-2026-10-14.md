# Titoli privati, base protetta e recupero — 14 ottobre 2026

**Stato: verifiche locali PASS; rilascio PENDENTE. Titoli non ancora confermato. BASELINE IN AUDIT.**

L’utente autorizza esplicitamente analisi, codice, prove, Maestro, commit, push e pubblicazione online mediante la procedura vigente, con conservazione dei dati. Il protocollo completo è ora disponibile: base protetta per nuove strutture, titoli privati, personalizzazioni isolate, recupero volontario confermato senza perdite e seed che non sovrascrivono né ricreano i cancellati. Supera il limite documentale dell’[audit iniziale](VERIFICA-TITOLI-2026-10-14.md), i cui due difetti erano riprodotti prima della correzione.

## Base e provenienza effettivamente verificate

Server prima del delta: HEAD `fdd03d1fadf1d903c5a821754b525c6c185f494e`, 82 originali, unique globale sul nome, nessuna FK verso gli ID Titolo. Gli80 elementi del seeder coincidono tutti per nome e descrizione con80 record online; estratti senza alterazioni nella configurazione versionata `config/titoli.php`. Non si dichiara che82 fosse la base originaria. Origine Git del seeder: baseline `4d1f28b`; ricerca `git log --all -S 'Sig.s'` e `-S 'Sigg.'` in app/database/resources senza eventi corrispondenti.

ID1 `Sig.s` eID4 `Sigg.`: timestamp DB di creazione `2026-03-04 06:50:15`; aggiornamento rispettivamente `06:50:51` e `06:52:17`. Date originali, non prova di autore o contesto. Nessun audit Titoli disponibile né relazione live/archiviata ai due extra. Restano globali protetti, intatti, senza attribuzione a strutture e fuori dalla base ufficiale80. Evidenza aggregata [qui](evidenze/titoli-2026-10-14/provenienza-verificata.json). Nessuna ricostruzione inventata.

Relazioni online: `clienti.type` (12record) e `schedina.type` (15record), soli Amm./Arch./Sig./Sig.ra nella struttura1. Altre colonne con la parola titolo riguardano oggetti diversi e non sono relazioni a questo catalogo. Tutti i riferimenti precedenti restano conservati. Strutture7, di cui3archiviate: inizializzate nella migrazione senza riattivazione né cambio membership. Riferimenti legacy ulteriori possono essere ricopiati in privato soltanto da fonte globale univoca e da un’associazione esistente della struttura; valori irrisolti rimangono storici.

## Modifica circoscritta

Riutilizzati da Gruppi scope, middleware `RequireAuthorizedStruttura`, marcatore di inizializzazione e lock sulla struttura; nessuna nuova politica di ruolo generale. Migrazione unica `2026_10_14_020000_isolate_titoli_per_struttura`: `struttura_id` nullable/FK restrittiva e `base_nome`, unique privati per nome/origine, marcatore. Il precedente unique globale viene sostituito: consente copie dello stesso titolo in strutture diverse, mantiene il rifiuto dei duplicati nella medesima struttura. Originali non spostati/riscritti; down rifiuta perdita automatica, recupero mediante checkpoint coordinato.

Base immutabile versionata80; nuova struttura riceve una copia privata. Seed e inizializzazione successivi conservano personalizzazioni/stati/cancellazioni grazie al marcatore. Recupero volontario aggiuntivo con modale, descrizione degli effetti e conferma in due passi esistente; server richiede conferma accepted. Aggiunge i nomi mancanti, preserva il titolo rinominato e ricrea separatamente il nome originale; non sovrascrive descrizioni/stati di record già presenti. Conflitti segnalati. Nessun reset distruttivo o perdita silenziosa dei riferimenti.

Formulario: Nome obbligatorio max50, Descrizione opzionale max150, booleano valido; hidden0 con checkbox1 garantisce creazione e modifica inattive. Duplicati rifiutati case-insensitive e con trim, varianti di abbreviazione distinte conservate senza fusioni arbitrarie. Ricerca sulla forma completa normalizzata (punti/spazi/apostrofo) invece di OR per frammenti; q validata, ID numerico e ricerca descrizione, ordine stabile/paginazione10/q conservata. Modali fuori dalla tabella, gestione locale univoca, annullamento ripristina valori originali, errori italiani riaprono solo il modulo interessato con input conservati.

Dipendenze indispensabili: selettori Cliente/Schedina privati e attivi, visualizzazione dei valori storici/inattivi già associati, validazione server delle nuove selezioni anche tramite POST diretto/bozze. Rinomina transazionale nei `type` della stessa struttura e negli snapshot Customer/Schedina/WebCheck-in annidati, senza alterare identità/altre colonne. Eliminazione vietata se utilizzato: possibile disattivazione conservativa. Cestino dei titoli privati con scope, lock, ripristino autorizzato e controllo conflitti; originali storici senza struttura non ripristinati arbitrariamente. Import non importa titoli nuovi: crea `type=null`, eredita gli stessi controlli delle dipendenze; nessun raw import riscritto.

## Prove attuali

VM ufficiale senza rete, PHP8.3.33/MariaDB11.8.9, DB e filesystem effimeri attestati. Mai test Laravel su MySQL/Herd, DB quotidiano o produzione. Comandi privati sotto `output/verifica-titoli-2026-10-14/` riusano il launcher ufficiale.

- `test.py`: **36test/361asserzioni PASS**, TitoliTenant + GruppiIntegrity + GruppiTenant + TopbarRendering. Include4ruoli, almeno2strutture, ID manipolati, sessione/query estranee, proprietà non assegnabili da payload, anonimo/inattivo/ruolo legacy/CSRF, CRUD/stati/validazioni/duplicati, ricerca/paginazione, base/seeder ripetuti/nuova struttura, recupero/conflitti, relazioni/archivi e Cestino.
- `recovery.py`: **1test/20asserzioni PASS**. Predecessore sintetico con unique globale, DDL/DML reali, originali e relazioni preservati, riapplicazione idempotente, guasto dopo mutazione, ripristino dump e ridump byte-identico. Non è un nuovo reboot del guardiano.
- Guardie15PASS; build/GEO e23override pericolosi respinti PASS nelle prove. Nessun allentamento dell’attestazione. Inclusione opzionale limitata al seeder Titoli e alla sua nuova modale nel launcher/template; nessun seed implicito.
- Chromium definitivo: **2flussi PASS/47,0s**. CRUD completo, creazione/modifica inattiva, riattivazione, validazioni/duplicati/input conservati, annullamento/reset, conferme, eliminazione riuscita/rifiutata/annullata, recupero volontario, ricerca composta/paginazione, riferimenti Cliente/Schedina e secondo utente con PUT/DELETE su ID estraneo404. Tentativi intermedi falliti per selettore senza icona e ricerca prima del caricamento degli handler: corretti selettori/attese del test, aspettative e codice invariati; diagnostici conservati.

Primo HTTP fallito perché i nuovi sorgenti non erano presenti nella copia effimera basata sul Git VM; inclusione esplicita corretta e ripetizione verde. Log completi conservati in output, aspettative funzionali non indebolite. I diagnostici iniziali `TitoliFormularioAudit.php` restano storici e non sono parte della suite corrente.

## Git, dati, rilascio e rischi

Branch main/HEAD iniziale fdd03d1, modifiche documentali pregresse e untracked estranei preservati/esclusi dallo staging. Nessun refactoring fuori Titoli; lock/dipendenze/enti/infrastruttura applicativa invariati. Rischio alto del delta DDL/DML e conservazione, mitigato da prove/preflight/S0/guardiano/restore indipendente secondo C5. Procedura derivata dal rilascio vigente fdd03d1, con nuovi piano/kit/sigilli/autorizzazione corrente e unica migrazione Titoli; nessun vecchio piano eseguito come autorizzazione nuova.

Ambiente quotidiano VM aggiornato151→152: checkpoint cifrato/riletto;64tabelle/23.708record precedenti identici su tutte le colonne originali;560titoli privati/82globali preservati, zero orfani. Nessun MySQL/Herd originale usato. Rilascio online ancora da attestare. Nessuna conferma SHA/allineamento o pubblicazione senza evidenza finale. I due extra non richiedono cancellazione: limite di provenienza registrato nei Pendenti.

CODICE MODIFICATO: SI; TEST MODIFICATI: SI; DOCUMENTAZIONE MODIFICATA: SI; CONFIGURAZIONE TITOLI MODIFICATA: SI; SCHEMA/DATI EFFIMERI: SI; DATI QUOTIDIANI VM: SOLA MIGRAZIONE AUTORIZZATA CON CONSERVAZIONE; PRODUZIONE: ANCORA INVARIATA; TEST ESEGUITI: SI; COMMIT/PUSH/DEPLOY: PENDENTI; TRASMISSIONI A ENTI/MESSAGGI: NO. Prossimo passo: concludere Chromium, checkpoint/confronto VM quotidiana, commit selettivo/push, C5 e browser/conservazione/monitoraggio online, allineamento finale.

# Operazione proposta Ross1000 Herd — 9 ottobre 2026

**Documento per autorizzare un cambiamento futuro, nessuna scrittura operativa eseguita.** Accettazione funzionale locale positiva conservata; BASELINE IN AUDIT — NON VALIDATA. Non autorizza operazioni sul destinatario remoto, commit, deploy o trasmissioni.

## Destinatario e riconciliazione

Progetto `/Users/jorgeluccitelli/Herd/Schedinedinotifica`, sito `https://schedinedinotifica.test`, connessione MySQL TCP **127.0.0.1:3306**, database **schedinedinotifica**, utente tecnico **tanggo**, server **8.0.44**. PDO conferma SELECT DATABASE(); lettura di soli metadati in transazione READ ONLY/ROLLBACK. Configurazione da .env, cacheconfig assente; DATABASE_URL e DB_SOCKET assenti, nessun overrideDB nel processoCLI. Non sono state stampate password/APP_KEY né consultati record personali. Questo identifica la base configurata nel checkout Herd; non certifica eventuali override esterni al processoFPM. Prima dell'applicazione riconfermare tale assenza o fermarsi.

[Inventario completo e hash dei file](evidenze/chiusura-herd-2026-10-09/metadati-herd-preflight.json): confronto di tutti i file migration con lo storico effettivo. Tre pendenti:

| Migration | Decisione per questa operazione |
|---|---|
| 2026_10_07_180000_protect_istat_cycle | **Unica da applicare** per il500 snapshot assente. |
| 2026_10_07_180000_create_tassa_exports_and_receipt_image | Esclusa: export/ricevute tassa, non dipendenza della correzioneRoss1000. Richiede operazione distinta. |
| 2026_10_09_230000_add_web_checkin_link_lifecycle | Esclusa: lifecycle token, non dipendenza diRoss1000. Richiede operazione distinta. |

Tutte le tabelle rilevate sono InnoDB. Nessuna deduzione dei pendenti dal solo nomefile. Nessun `migrate` senza `--path`, migrate:fresh, seed o rollback sulDBHerd.

## Impatto da autorizzare

Hash SHA256 dell'unica migration: `55cace558d6575888140f7b34cd76a7ad486a96f4b41b66f94a28a1be4950969`.

- `struttura.istat_username/istat_password`: da VARCHAR a TEXT nullable; scorre le strutture in gruppi100 e cifra i valori non vuoti ancora in chiaro usando **l'APP_KEY esistente**. Contenitori cifrati riconosciuti sono preservati anche se non decifrabili. Non cambiare APP_KEY; non si tratta soltanto dell'aggiunta snapshot e non interessa soltanto tanggo.
- `istat_exports`: aggiunge sha256 nullable, encrypted_file defaultfalse, snapshotJSON nullable, expires_at e minimized_at nullable. Non ricostruisce snapshot né cifra file/export legacy.
- `istat_transmissions`: aggiunge idempotency_key nullable con unique, attempts default1 e reconciled_at nullable. Non riconcilia trasmissioni pregresse.
- Crea `istat_communication_days` e `istat_transmission_events`, con indici definiti nel file; registra soltanto quella migration nello storico.

DDLMySQL non atomicamente reversibile; un'interruzione può lasciare uno schema parziale. Non rilanciare alla cieca. **down() è intenzionalmente vuoto**, perciò migrate:rollback non costituisce recupero e potrebbe rimuovere solo la registrazione lasciando lo schema modificato. Recupero mediante backup completo, in finestra senza scritture concorrenti; l'applicazione richiede autorizzazione anche per la cifratura delle credenziali e per l'eventuale ripristino che sovrascrive i dati al punto di backup.

## Backup e verifica, preparati ma non eseguiti sul reale

Prima: sospendere accessi in scrittura al sito locale e worker/scheduler eventualmente attivi, con modalità concordata; nessuna trasmissione/provider da attivare. Non usare una suiteLaravel sulDBHerd. Verificare identità/schema/hash ancora uguali, assenza di DDL concorrente e spazio sufficiente. Se mancano privilegi dump/routines/events interrompere, senza ridurre silenziosamente il contenuto. Conservare backup fuori da repository e directory pubbliche con permessi700/600, su volume protetto. `.env`/APP_KEY e configurazione esistente devono essere recuperabili in modo riservato, senza stamparli o inserirli nel candidato.

Comandi proposti dal Mac, **da eseguire solo dopo autorizzazione**; password esclusivamente nel prompt terminale, mai nell'argomento o nella chat:

```sh
cd /Users/jorgeluccitelli/Herd/Schedinedinotifica
umask 077
ROSS_BACKUP_DIR="$HOME/Backups/Schedine/Herd/ross1000-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$ROSS_BACKUP_DIR"
chmod 700 "$ROSS_BACKUP_DIR"
mysqldump --no-defaults --protocol=TCP --host=127.0.0.1 --port=3306 \
  --user=tanggo -p --single-transaction --skip-lock-tables \
  --no-tablespaces --set-gtid-purged=OFF --routines --events --triggers \
  --hex-blob --default-character-set=utf8mb4 schedinedinotifica \
  > "$ROSS_BACKUP_DIR/database.sql"
```

Controllare exit0 immediatamente: se fallisce **fermare la procedura** e conservare il parziale identificato come non valido. Non concatenare automaticamente la migration al dump. Con exit0:

```sh
test -s "$ROSS_BACKUP_DIR/database.sql"
chmod 600 "$ROSS_BACKUP_DIR/database.sql"
(cd "$ROSS_BACKUP_DIR" && shasum -a 256 database.sql > database.sha256)
(cd "$ROSS_BACKUP_DIR" && shasum -a 256 -c database.sha256)
```

Exit0, dimensione non nulla e checksum non provano da soli la recuperabilità. Registrare versione client/server, orario, base, exit e dimensione/hash; nessun contenutoSQL da riportare nei log di audit. Il dump contiene dati personali e possibili credenziali pre-migration: non copiarlo nel progetto o allegarlo in chat. Il comando non usa --databases e non crea/seleziona altriDB.

**Verifica di restore reale ancora non eseguita:** le autorizzazioni vigenti consentono restore solo con dati sintetici. Prima di applicare sul reale serve una prova precedente affidabile del backup operativo oppure autorizzazione esplicita a restaurare questa copia in un MySQL isolato senza rete/trasporti, con trattamento riservato dei dati reali. Non equiparare la nuova prova sintetica a tale evidenza. Fino ad allora la procedura è pronta, il gate backup reale resta aperto.

## Applicazione esatta dopo chiusura del gate backup

Riconfermare checksum/hashmigration, assenza di overrideDB/cachestale e sito/worker senza scritture. Conservarne inventario di metadati e codice, senza cambiare APP_KEY o configurazione del server. Dal checkout indicato:

```sh
php artisan migrate \
  --path=database/migrations/2026_10_07_180000_protect_istat_cycle.php \
  --force --no-interaction
```

Unico file autorizzabile, nessun'altra pending inclusa. Il comando Laravel avvia i provider normali: non è una prova di sola lettura e **non è stato eseguito oggi** sulDBHerd. Interrompere con exit diverso da0; non fare migrate:rollback e non ripetere senza ricognizione del parziale.

Chiusura: riconciliare metadata attesi con filemigration, registrazione una sola volta, altre due pendenti ancora assenti, nessun cambiamento a payload storici e nessuna trasmissione. Verificare internamente la leggibilità delle credenziali cifrate senza valori nei log. Browser reale con operatore tanggo autorizzato: configurazioneRoss1000→pulsanteTabellaA→200, assenza di nuoveQueryException42S22; non generare/inviare export per dimostrare la pagina. I dati visualizzati restano riservati: nessuna schermata personale nelle evidenze pubbliche. Le prove sintetiche già verdi non sostituiscono quest'ultimo controllo operativo autorizzato.

## Recupero in caso di errore

Tenere sito/worker senza scritture. Salvare riservatamente evidenza tecnica sanitizzata e una copia dello stato fallito prima del ripristino; niente cancellazioni o logSQL personali. Confermare base127.0.0.1:3306/schedinedinotifica e checksum della copia pre-intervento.

Il backup include DROP/CREATE delle tabelle originali: l'import ripristina colonne, credenziali e storico al punto del dump. Le due tabelle nuove, assenti nel catalogo pre-intervento, vanno rimosse esplicitamente se presenti; **nessuna altra tabella da rimuovere**. Sequenza proposta solo sotto autorizzazione al recupero:

```sh
mysql --no-defaults --protocol=TCP --host=127.0.0.1 --port=3306 \
  --user=tanggo -p --database=schedinedinotifica \
  --execute='DROP TABLE IF EXISTS istat_communication_days, istat_transmission_events;'
mysql --no-defaults --protocol=TCP --host=127.0.0.1 --port=3306 \
  --user=tanggo -p --database=schedinedinotifica \
  < "$ROSS_BACKUP_DIR/database.sql"
```

Verificare exit0 separatamente per ogni comando; se l'import fallisce mantenere la finestra chiusa e diagnosticare, senza riaprire il sito. Questa operazione sovrascrive **l'intera base locale al backup**: eventuali scritture successive sarebbero perse, quindi il blocco delle scritture è un requisito. Non usare DROP DATABASE, non cambiare config/schema del serverremoto. Riconciliare catalogo precedente e migrationhistory, conteggi e controlli riservati dei dati; APP_KEY e codice compatibile con lo schema ripristinato. Il500 ritorna sul codice attuale se si ripristina lo schema vecchio: mantenere la pagina indisponibile fino a nuovo intervento, non presentare il recupero come correzione del difetto.

## Autorizzazione proposta, ancora assente

Ambito esatto: backup riservato della sola base locale schedinedinotifica, sua verifica di recuperabilità con modalità espressamente autorizzata, applicazione del singolo file protect_istat_cycle (compresa cifratura ISTAT), controlli operativi senza trasmissioni; recupero integrale da backup solo se necessario e autorizzato. Le altre due migration restano escluse. Nessun passaggio è eseguito dalla mera preparazione del documento.


## Verifica effettiva della procedura preparata

Nuova prova mirata Ross1000BackupRestoreAudit: **1test/85asserzioniPASS** tramite launcher isolato, MySQL8.0.36/CLI8.3.33. Dump consistente con routines/events/triggers, hash e rilevazione alterazione, migration singola, cifratura del valore sintetico e preservazione ciphertext dopo l'allargamento, eliminazione delle sole due nuove tabelle e restore completo. Confrontati storico, colonne, record sintetici, valoreNULL e conteggi di tutte le tabelle. Runtime ripulito; nessun dato reale. [Log effettivo](evidenze/chiusura-herd-2026-10-09/backup-verifica-04.log).

Tentativi diagnostici originali conservati:01rifiuto preventivo del parametroinclude non ammesso ai test;02/03setup di ciphertext troppo lungo nel campo storico. Nessun difetto applicativo dedotto da quei rossi: il campo Herd effettivo è VARCHAR100, non una colonna capace di contenere il ciphertextLaravel valido. Setup definitivo usa VARCHAR100 e verifica la preservazione del ciphertext dopo conversioneTEXT. Copie sorgente e log originali presenti, aspettative di recupero/copertura mantenute senza occultare risultati.

Non è stata ripetuta la globale750/8252 già superata: nessuna modifica applicativa o dipendenze in questa fase, solo diagnostico e preparazione. Le precedenti prove browserRoss10001/1 sono preservate; il controllo del sitoHerd reale resta dopo autorizzazione. Una prova sintetica su8.0.36 non certifica il backup dei dati reali su8.0.44.


## Esecuzione successivamente autorizzata

La task successiva ha autorizzato backup reale riservato/restore isolato e migration singola condizionata alPASS. Operazione completata: [verbale effettivo](HERD-ROSS1000-INTERVENTO-AUTORIZZATO-2026-10-09.md). Il piano sopra è conservato come storico, non usato per dichiarare eseguiti i passaggi prima della nuova autorizzazione.

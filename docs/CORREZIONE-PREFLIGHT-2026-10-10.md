# Correzione del preflight prima di Composer — 10 ottobre 2026

**Difetto riprodotto corretto e regressioni isolate superate. Il runner integrale sul commit approvato resta PENDENTE.** Stato: BASELINE IN AUDIT — NON VALIDATA. Accettazione applicativa locale positiva conservata; nessuna validazione produttiva.

## Causa e modifica

`prepare()` chiamava `pending()` tramite l'autoload del destinatario Laravel 11.31.0. Il gate esatto 12.69.3 respingeva la richiesta prima della query; Composer veniva eseguito soltanto successivamente in `execute()`. Il diagnostico originale in `docs/evidenze/chiusura-candidato-2026-10-09/` è conservato senza modifiche.

Modificati esclusivamente gli strumenti `scripts/deployment/deploy.py`, `scripts/deployment/artisan.php` e le regressioni `tests/Deployment/test_deploy_guards.py`; nessun codice applicativo, migration, dipendenza o asset cambiato.

La chiamata preliminare è ora `pending(directory, preliminary=True)` → `preliminary-pending`. L'helper legge direttamente con PDO lo storico `migrations`, in transazione READ ONLY su MariaDB, senza vendor, autoload, bootstrap, provider o esecuzione dei file migration. Ordina il catalogo, rifiuta storico duplicato e migration applicate assenti. Chiude sempre con rollback della sola transazione di lettura. Nessuna migration viene applicata dal preflight.

Prima della connessione conserva vincoli su file fisici/link, dotenv letterale e identità APP_KEY/DB, flag production/debug, override di processo e percorsi runtime. Vincola `config/database.php` all'hash revisionato `df1e85513075c64bb3e49336626047f7abdbcfeba673e61a773cb30cf0a7dc41`, identico nella baseline HEAD e nel candidato. Se presente, il config cache viene letto e confrontato con identità dotenv, tabella migration, prefix, URL e opzioni. Configurazioni personalizzate, interpolazioni, SSL CA o percorsi dotenv espliciti richiedono nuova review e vengono rifiutati: nessun tentativo di indovinare valori o stampare credenziali.

Il preflight mantiene controlli PHP/estensioni, Node/npm, Composer, Git/origine/SHA, bundle, destinazioni build, `composer validate` e `check-platform-reqs --lock --no-dev`: questi ultimi non avviano Laravel e non richiedono il vendor candidato. Non è un preflight interamente privo di scritture: lock, scratch e fetch restano previsti dal runner.

Dopo `Composer install --no-dev --no-scripts` con `--no-plugins`, il runner controlla i requisiti del vendor installato e chiama il helper Artisan ordinario. Qui resta obbligatorio **Laravel 12.69.3 esatto**, prima dei provider e di qualsiasi comando cache o migration. Restano le verifiche complete di configurazione, path e APP_KEY. La riconciliazione dello storico prima delle migration e il controllo finale senza pendenti continuano attraverso questo helper rigoroso; cambiamenti concorrenti fermano il runner. Non è stata ampliata la whitelist delle versioni mutanti.

## Risultati effettivi

| Requisito | Evidenza | Risultato | Limite o gate residuo |
|---|---|---|---|
| Lettura senza vendor / indipendente dal vendor 11, 12 o incompatibile | MariaDB sintetico, test `test_real_readonly_metadata_ignores_vendor_versions_and_missing_vendor`; autoload sentinella che fallirebbe se eseguito | PASS, medesimo elenco pending; nessun file migration eseguito | Si tratta dei metadati, non di accettazione del vendor per i comandi successivi |
| Vendor 11 reale respinto nella fase rigorosa | `vendor11-reale.json`, fixture locale senza DB raggiungibile | PASS, exit 1 e dotenv immutato | Non è un'installazione Composer end-to-end da vendor 11 |
| Versioni 11.31.0, 12.69.2, 13.0.0 e vendor assente dopo Composer | `ArtisanIntegrationTests`, helper reale su fixture e versioni dichiarate controllate | PASS, rifiuto prima del comando | Incompatibili non autorizzati; controllo positivo con vendor reale 12.69.3 |
| Assenza anche del vendor di supporto dei test | `vendor-assente.log` | 2 PASS, 2 SKIP MariaDB | I due casi MariaDB sono eseguiti in Linux, senza skip |
| MariaDB READ ONLY e integrità storico | `risultato-v20.json`, confronto prima/dopo; scrittura sentinella rifiutata con errore 1792 | PASS | Database nuovo, socket privato, skip-networking, eventi OFF, namespace unshare-all |
| Configurazione incoerente, override, catalogo incompleto, cache divergente | Nuove regressioni negative e guardie esistenti | PASS | Configurazioni DB non revisionate fermano il preflight |
| Suite strumenti, sequenza e recupero della fixture | `guardie-linux-v20.log` | **76/76 PASS** | Il test di sequenza simula Git/HTTP/npm; non equivale al runner integrale sul candidato |
| Suite strumenti macOS | `mac-finale.log` | 71 PASS, 5 SKIP su 76 | 3 casi Linux e 2 MariaDB; coperti tutti in Linux |
| Composer reale prima e dopo installazione | `composer-reale.json` e quattro log | validate, requisiti lock, install, requisiti vendor: exit 0; Laravel 12.69.3 | Namespace senza rete, vendor 12 già disponibile; non prova acquisizione offline completa da vendor 11/assente |
| CLI OPcache, sigillo, provider/script, manutenzione e processi | Suite strumenti sopra | PASS nel perimetro delle guardie | Nessuna modifica della mitigazione precedente o dei servizi operativi |
| Git reale, archivio e policy del nuovo tree | Evidenza finale `git-tree-finale.json` | Da leggere con il nuovo identificatore consegnato | Tree non accettato come commit; nessun commit candidato creato |
| Runner prepare/execute sullo SHA finale | Procedura di chiusura e gate Git | **PENDENTE** | Richiede commit approvato, disponibilità nella sorgente ammessa e allestimento integrale privato descritto |

La suite strumenti usa anche repository Git effimeri e commit sintetici interni alle fixture preesistenti; non crea commit nel repository condiviso o un commit del candidato. La suite globale applicativa e i campioni FPM/browser già accettati non sono ripetuti: nessun flusso applicativo o dipendenza modificato, nuove evidenze circoscritte al runner.

## Diagnostici falliti conservati

Il primo run macOS sotto sandbox ha un errore di autorizzazione sul socket HTTP sintetico: `mac-sandbox-socket-originale.log`. Ripetuto con accesso ai soli socket di test, esito verde registrato separatamente.

Il primo Linux v18 fallisce una nuova aspettativa: `DB_HOST=another` non è duplicato, e con DB_SOCKET attivo il motore usa il socket. `linux-v18-originale.log` e `diagnostico-v18-originale.txt` conservano risultato e sorgente. La versione identificata successiva usa una vera duplicazione di `DB_USERNAME`; non si indebolisce un criterio applicativo o la barriera 503. Lo storico resta immutato anche nel run fallito. Copie e risultati v18/v19/v20 separati, nessuna sovrascrittura.

## Candidato e passo successivo

Tree e66f9647c48de84b7383e88d9d9eb3a7d9ba3c41 e f25f3dd9ddef12ec61074f473379137e5179b6ee preservati; snapshot binario e manifest f25 riservati prima del nuovo staging, fuori Git. Il nuovo candidato include solo la correzione degli strumenti, regressioni ed evidenze/documentazione pertinenti rispetto a f25. Identificatore finale nella consegna; il manifest non contiene hash autoriferiti.

Il blocco tecnico della lettura preliminare è chiuso nel perimetro verificato. La sequenza completa su fixture è verde; **non dichiarato superato il gate del runner integrale**. Per chiuderlo: approvare il nuovo tree e il messaggio, autorizzare separatamente commit/push, quindi eseguire prepare/execute sullo SHA risultante esclusivamente nella sandbox integrale attestata, con recupero S0 completo. Nessun `migrate:rollback`, nessuna operazione sul destinatario operativo.

Gate produttivi e blocco distinto della nuova installazione MariaDB 11.8.9 invariati. Nessun commit/push/deploy del progetto, DB operativo, riavvio, modifica configurazione operativa o trasmissione reale.

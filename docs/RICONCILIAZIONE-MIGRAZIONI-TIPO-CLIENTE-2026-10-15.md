# Riconciliazione delle due migrazioni Tipo cliente — 15 ottobre 2026

**Riconciliazione database VERIFICATA; allineamento conclusivo del nuovo commit subordinato alla ricevuta esterna C5. BASELINE IN AUDIT generale invariata.** Richiesta corrente autorizza diagnosi, intervento minimo, prove isolate, documentazione, commit, push e C5. Nessuna trasmissione agli enti.

## Stato effettivo e causa

HEAD locale iniziale, GitHub main interrogato con ls-remote, produzione e VM ufficiale: `261429e1ad4483925254beb5a873f2c4aab75184`. Database quotidiano 153 migrazioni, produzione 155. L'allineamento del codice precedente non aveva eseguito le due migrazioni nel database quotidiano: differenza già esplicitamente esclusa dallo scope dei tre formulari. Non è una regressione del nuovo rilascio né una migrazione assente da Git.

| Migrazione versionata | Scopo ed equivalenza verificata | Produzione prima | VM prima e dopo |
| --- | --- | --- | --- |
| `2026_02_28_000000_prepare_classificazioni_index_for_normalize.php` | Aggiunta il 14 ottobre; ordinamento prima dell'originale immutabile. Su predecessore supporta FK con indice non univoco e rimuove unique composto che impediva la normalizzazione MariaDB. Nei due DB correnti la colonna tipologia_struttura_id è già assente: ramo di uscita senza DDL/DML. | Applicata, batch 65 | Non registrata → eseguita realmente con Laravel e registrata |
| `2026_10_14_030000_initialize_tipo_cliente_sistema.php` | Inizializzazione transazionale di OSPITE/COMPONENTE/RICHIESTA. ID 1/2/3, codice esatto, descrizione e stato già conformi, nessun extra nei DB operativi; nessuna scrittura di catalogo necessaria. | Applicata, batch 65 | Non registrata → eseguita realmente con Laravel e registrata |

Sono migrazioni realmente pendenti nel ledger quotidiano, con effetti già soddisfatti; nessun inserimento manuale di righe migrations. Non sono file ridondanti da eliminare: necessari rispettivamente per installazione fresca e inizializzazione automatica. Nessuna migrazione storica modificata e nessuna migrazione correttiva artificiale.

Schema confrontato: classificazioni normalizzata con unique sul nome (anche l'indice legacy residuo già presente, senza nuova pulizia); pivot classificazione_tipologia con unique coppia e FK CASCADE; FK struttura.classificazione_id conservata. tipo_cliente con PK bigint, unique codice, descrizione e stato. Nessuna FK verso tipo_cliente; classificazione cliente testuale. La produzione contiene un cliente con valore vuoto; anche la VM conserva il proprio valore vuoto e le altre classificazioni preesistenti, senza deduzioni. Contenuti personali non esportati nei documenti.

## Intervento minimo e conservazione

Nessun codice applicativo modificato. Servizio quotidiano fermato nella sola VM, checkpoint SQL completo cifrato con chiave custodita nella VM, rilettura autenticata e SHA identico. Custodia privata `/home/collaudo.guest/custodia-tipo-cliente-1792048109944095078`, originali e diagnostici conservati. Eseguite singolarmente con `php -d opcache.enable_cli=0 artisan migrate --force --no-interaction --path=database/migrations/<file>` soltanto le due migrazioni sopra; servizio riavviato in finally.

Confronto HMAC di tutte le colonne di tutte le righe preesistenti nelle 64 tabelle, ordinando in memoria senza escludere duplicati: 25.875 record identici dopo ciascuna migrazione; esclusivamente le due nuove righe migrations escluse dal confronto finale. Schema completo delle tabelle applicative identico, ID/timestamp/catalogo/personalizzazioni/relazioni intatti. `.env` e lock identici, APP_KEY conservata. VM 153→155. Nessun fresh/refresh/reset/rollback sui database operativi, nessun seeder generale o migrazione indiscriminata. MySQL/Herd originale non usato.

## Prove realmente eseguite

VM ufficiale schedine-sviluppo-interno, PHP 8.3.33/MariaDB 11.8.9, namespace senza rete esterna e checkout/DB/storage/processi effimeri attestati. Comandi completi nei driver privati `output/riconciliazione-tipo-cliente-2026-10-15/test.py` e `browser.py`, wrapper ufficiale namespace-selinux-visibile.sh, runuser collaudo, env svuotato e cache Python propria. Nessuna prova Laravel su dati quotidiani o produttivi.

- PHPUnit: **51 test / 338 asserzioni PASS**. TipoClienteRefreshDiagnosticTest con RefreshDatabase originale prova installazione da zero; TipoClienteMigrationReconciliationTest prova base normalizzata, schema/vincoli/dati/cliente vuoto, ripetizione senza scritture, voce mancante ed extra conservati; TipoClienteSistemaTest, SchedinaStoreTest, SchedinaCustomerReferenceSecurityTest e TopbarRenderingTest verificano formulario, quattro ruoli incluso Superadmin, dinieghi POST/PUT/PATCH/DELETE, modello/Cestino, tenant estraneo, selettori e passaggi cliente/schedina.
- Chromium: **2 prove PASS / 36,4 secondi**, tipo-cliente.playwright.spec.js, fixture sintetica seed-tipo-cliente.php: consultazione/ricerca/assenza azioni, selettori, bozza delle tre classificazioni, modifica e annullamento.
- Guardie ufficiali: **15 PASS**; build/GEO immutabile e **23 override rifiutati** prima delle migrazioni PASS.

Primi avvii PHPUnit e browser fermati con «SHA candidato errato»: cache Python già presente. Ripetizioni con PYTHONPYCACHEPREFIX distinto PASS; nessuna attenuazione delle guardie. Prima lettura SQL produttiva fermata perché mancava la selezione esplicita del DB nel client C5: aggiunto USE del solo database del piano, poi lettura PASS. Arresti non qualificati come prove positive; log conservati.

## Chiusura e allineamento

Le due pendenze database sono risolte individualmente nella VM e già applicate/verificate in produzione. Rimane distinto il cliente legacy vuoto nel registro Pendenti: nessuna regola approvata per riclassificarlo.

Questo consuntivo, il Maestro, l'aggiornamento del registro esistente, la regressione e l'evidenza non sensibile formano il commit selettivo. Modifiche estranee preesistenti, comprese quelle del Maestro, preservate ed escluse tramite staging del solo delta corrente. Nessuna CI GitHub configurata nel repository.

Pubblicazione conclusiva mediante nuova composizione C5 sul commit effettivo, PREPARE indipendente, S0 fresco, guardiano e budget ufficiali invariati; zero migrazioni/DDL/DML produttive. Conservazione DB byte-identico S0, file persistenti/loghi/env/APP_KEY, sonde HTTP/TLS/CSS e monitor documentale minimo 60 secondi. **Nessuna dichiarazione di allineamento finale prima del PASS della ricevuta esterna** `output/riconciliazione-tipo-cliente-2026-10-15/allineamento/verifica-allineamento.json`, che deve attestare SHA locale/GitHub/produzione/VM, 155 migrazioni in entrambi i DB e guardiano concluso. Nessun commit autoreferenziale dopo la ricevuta.

[Evidenza non sensibile](evidenze/riconciliazione-tipo-cliente-2026-10-15/verifica.json). Diagnosi, comandi, backup e log riservati sotto output e custodia VM/server. Nessuna certificazione globale del sistema: BASELINE IN AUDIT.

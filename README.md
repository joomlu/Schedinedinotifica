# Schedinedinotifica (Laravel 11)

Applicazione Laravel servita localmente con Herd su schedinedinotifica.test.

## URL locale

- HTTP: http://schedinedinotifica.test
- HTTPS: https://schedinedinotifica.test

## Health check

- Endpoint: `GET /health`
- Risposta: `{ "status": "ok", "app": "<name>", "env": "<env>", "time": "<ISO>" }`

## Backup e ripristino

- Script di backup: `backup.sh` (crea tar.gz del progetto e dump .sql nella cartella di backup configurata nel file)
- Script di ripristino: `restore.sh`

Esempio di ripristino da backup:

1) Copia i file di backup (tar.gz e .sql) nella cartella dei backup.
2) Esegui:

	 ```bash
	 ./restore.sh Schedinedinotifica_YYYY-MM-DD_HH-MM.tar.gz \
		 --project-dir /Users/jorgeluccitelli/Herd/Schedinedinotifica
	 ```

Opzioni utili di `restore.sh`:

- `--project-dir PATH` directory di destinazione del progetto
- `--db-name/--db-user/--db-pass/--db-host/--db-port` credenziali DB
- `--no-code` salta il ripristino dei file del progetto
- `--no-db` salta l'import del dump SQL
- `--skip-composer` non esegue `composer install`

Note:

- Lo script individua automaticamente il dump `.sql` corrispondente al suffisso data/ora del tarball.
- La base dati locale attuale è `schedinedinotifiche`.

## Test

Esecuzione test feature health:

```bash
php artisan test --testsuite=Feature --filter=HealthEndpointTest
```

## Manutenzione

- Rigenera le cache di configurazione dopo modifiche a `.env`:

```bash
php artisan config:clear && php artisan config:cache
```

### Qualità del codice

- PHP (Laravel Pint):

```bash
composer run lint    # solo verifica
composer run format  # applica fix
```

- Frontend (Prettier):

```bash
npm run format
```

Nota: Prettier formatta JS/CSS/JSON/MD. Il codice PHP/Blade è gestito da Pint.

### Cache e ottimizzazioni

```bash
composer run cache:clear     # svuota cache/config/route/view
composer run cache:optimize  # ottimizza autoload e cache
```

### Demo componenti (solo sviluppo)

Le pagine demo per i componenti sono disponibili solo in locale o con `APP_DEBUG=true`:

- Relazione Geografica: `/Relazione-Geografica`
- Indirizzo: `/Indirizzo`

Gli asset della libreria sono serviti da `reference/libreria/*` tramite rotte di sviluppo; non impattano la produzione.

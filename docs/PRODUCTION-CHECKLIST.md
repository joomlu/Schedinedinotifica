# Production Checklist

## 1. Backup locale

- Verificare la cartella:
  - `backups/release-20260323-100302`
- Verificare checksum:

```bash
cd /Users/jorgeluccitelli/Herd/Schedinedinotifica/backups/release-20260323-100302
shasum -a 256 -c SHA256SUMS.txt
```

## 2. Git remoto

- Configurare il remoto:

```bash
cd /Users/jorgeluccitelli/Herd/Schedinedinotifica
git remote add origin TU_URL_DEL_REPO
git push -u origin main --force
```

## 3. File da portare online

- Codice progetto completo
- File `.env` corretto per produzione
- Cartella `storage/`
- Permessi scrittura su:
  - `storage`
  - `bootstrap/cache`

## 4. Restore database

Se serve un ripristino completo:

```bash
gunzip -c schedinedinotifica-db-20260323-100302.sql.gz | mysql -u USER -p DATABASE_NAME
```

## 5. Deploy applicazione

Sul server:

```bash
cd /percorso/progetto
bash scripts/deploy-production.sh /percorso/progetto
```

Oppure manuale:

```bash
php artisan down --render="errors::minimal"
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache || true
php artisan queue:restart || true
php artisan storage:link || true
php artisan up
```

## 6. Verifica funzionale minima

- Login ok
- Dashboard struttura ok
- `Dati struttura` ok
- `SuperAdmin > Amministratori` ok
- `SuperAdmin > Proprietari` ok
- `SuperAdmin > Strutture` ok
- `SuperAdmin > Articoli` ok
- `SuperAdmin > Pagamenti / Licenze` ok
- `Admin > Proprietari` ok
- `Admin > Strutture` ok
- `Calendario` ok
- `Notifiche` ok
- `Supporto` ok
- `Aiuto` ok

## 7. Verifica commerciale

- Una struttura mostra:
  - licenza
  - tracking
  - scadenza
  - stato pagamento
- Un proprietario vede:
  - strutture
  - servizi
  - proforme
  - licenze delle strutture
- Un amministratore vede:
  - proprietari assegnati
  - servizi
  - proforme
- `Pagamenti / Licenze` mostra:
  - licenze assegnate
  - servizi struttura
  - stato conto

## 8. Post deploy

- Controllare log Laravel
- Controllare code/queue
- Controllare notifiche automatiche
- Controllare scadenze in calendario
- Fare un login per:
  - superadmin
  - admin
  - proprietario
  - struttura

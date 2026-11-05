# Checklist Rapida Deploy

## Prima di iniziare

- Verificare backup file
- Verificare backup database
- Verificare checksum
- Verificare accesso cPanel
- Verificare accesso phpMyAdmin o SSH

## File da usare

- `backups/release-20260323-100302/schedinedinotifica-files-20260323-100302.tar.gz`
- `backups/release-20260323-100302/schedinedinotifica-db-20260323-100302.sql.gz`

## Percorsi

- Laravel root:
  - `public_html/schedinedinotifica.tanggo.software/software/`
- Web root:
  - `public_html/schedinedinotifica.tanggo.software/software/public`

## Sequenza operativa

1. Fare backup del sito attuale sul server
2. Fare backup del database attuale sul server
3. Svuotare o rinominare la cartella `software/`
4. Caricare `schedinedinotifica-files-20260323-100302.tar.gz`
5. Estrarre il contenuto in `software/`
6. Verificare che `artisan` sia dentro `software/`
7. Verificare `.env` produzione
8. Importare `schedinedinotifica-db-20260323-100302.sql.gz`
9. Eseguire `composer install --no-dev --optimize-autoloader --no-interaction`
10. Eseguire:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan storage:link
```

11. Verificare:

```bash
php artisan about
php artisan migrate:status
```

12. Aprire il dominio pubblico
13. Testare login
14. Testare struttura
15. Testare superadmin
16. Testare admin
17. Testare proprietario
18. Testare pagamenti/licenze
19. Testare calendario/notifiche/supporto

## Non fare dopo il dump

Non eseguire:

```bash
php artisan db:seed
php artisan configurazione:setup
php artisan geo:import
```

## Documento completo

Per guida estesa usare:

- `docs/DEPLOY-CPANEL-SCHEDINEDINOTIFICA.md`

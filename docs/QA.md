# QA & Demo

## Toggle QA
- QA pages sono accessibili solo se `app.env != production` **oppure** `QA_ENABLED=true` in `.env` e sempre solo `super_admin`.
- Middleware: `qa.enabled` + `ruolo:super_admin` sul gruppo `/qa/*`.

## Demo data
- Esegui `php artisan demo:full` (oppure `php artisan db:seed --class=DemoSaasDataFullSeeder`).
- Password: `Passw0rd!` per tutti gli utenti demo.
- Mappa aggiornata in [docs/DEMO-MAP.md](DEMO-MAP.md).

## QA pagine (read-only)
- `/qa` dashboard
- `/qa/session` stato utente/struttura/impersonazione
- `/qa/accesso` matrice attesa OK/403 per ruolo
- `/qa/tenancy` conteggi per tabella e struttura
- `/qa/demo-map` mappa admin → proprietari → strutture (solo lettura)

## Comando CLI
- `php artisan qa:check`
  - Stampa env/APP_URL
  - Verifica middleware web: ImpostaStrutturaCorrente, VerificaServizioStruttura
  - Verifica rotte chiave (/qa/demo-map, /strutture/seleziona, /admin/proprietari, /proprietario/strutture, /schedine)
  - Conteggi per tabelle chiave (struttura, proprietari, users, schedina, clienti, componenti, schedina_camere) con struttura_id/null se presente
  - Integrità demo: 6 strutture, 6 proprietari, 1:1 proprietario-struttura, ruoli attesi (1 super_admin, 2 admin, 6 proprietario, 6 struttura_user)
  - Exit code: 0 OK, 1 WARN, 2 FAIL

## Test automatici
- `php artisan test`
  - Feature/AccessTest: accessi per ruolo (super_admin, admin, proprietario, struttura_user)
  - Feature/TenancyTest: verifica scope per struttura su schedine (skip se struttura_id mancante)

## Checklist manuale rapida
1) Login come super_admin → sidebar mostra QA e SuperAdmin. Apri `/qa`.
2) `/qa/demo-map`: verifica mappa admin → proprietario → struttura e stato servizio.
3) `/qa/session`: controlla StrutturaCorrente, impersonazione, servizio.
4) `/qa/accesso`: matrice OK/403 per ruoli.
5) `/qa/tenancy`: conteggi per struttura_id e NULL.
6) Impersonazione: da super_admin visita `/superadmin/impersonazione`, impersona admin/proprietario/struttura_user, verifica banner e uscita.

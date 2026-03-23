# Guida MySQL + Herd + VS Code

Questa guida ti aiuta a lavorare con il database `schedinedinotifica` usando Herd, MySQL e il terminale integrato di Visual Studio Code.

---

## 1. Aprire il terminale integrato di VS Code

- Vai su "Visualizza" > "Terminale" oppure usa la scorciatoia <kbd>Ctrl</kbd>+<kbd>\`</kbd> (accento grave).
- Assicurati di essere nella cartella del progetto Laravel.

---

## 2. Comandi MySQL da terminale (Herd)

### Accedere a MySQL come root (senza password):
```bash
mysql -h 127.0.0.1 -P 3306 -u root
```

### Accedere al database schedinedinotifica come utente tanggo:
```bash
mysql -h 127.0.0.1 -P 3306 -u tanggo -p
# password: tanggo
```

### Comandi utili dentro MySQL:
```sql
USE schedinedinotifica;
SHOW TABLES;
```

---

## 3. Comandi Artisan tipici

Esegui questi comandi nel terminale integrato di VS Code, nella cartella del progetto:

### Verificare lo stato delle migration
```bash
php artisan migrate:status
```

### Eseguire le migration
```bash
php artisan migrate
```

### Eseguire un seeder specifico (esempio)
```bash
php artisan db:seed --class=TipoDocumentoSeeder
```

### Pulire e ricacheare la config dopo aver modificato il .env
```bash
php artisan config:clear
php artisan config:cache
```

---

## 4. Dump e restore del database schedinedinotifica

### Dump:
```bash
mysqldump -h 127.0.0.1 -P 3306 -u tanggo -p schedinedinotifica > schedinedinotifica_dump.sql
# password: tanggo
```

### Restore:
```bash
mysql -h 127.0.0.1 -P 3306 -u tanggo -p schedinedinotifica < schedinedinotifica_dump.sql
# password: tanggo
```

---

## 5. Note su utenti MySQL

- Per amministrare MySQL (creare database, utenti, ecc.) usa:
  - utente: root
  - password: (vuota)
- Per il progetto Laravel, la connessione applicativa DEVE usare:
  - database: schedinedinotifica
  - utente: tanggo
  - password: tanggo

Non modificare il codice del progetto riguardo a utenti, middleware, ecc. Queste sono solo credenziali da usare in .env e nei comandi MySQL manuali.

---

> Tutte le operazioni possono essere fatte dal terminale integrato di VS Code, senza bisogno di altri strumenti.

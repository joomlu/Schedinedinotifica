# Deploy Produzione cPanel

## Dati di base

- Dominio pubblico: `schedinedinotifica.tanggo.software`
- Cartella di installazione applicazione:
  - `public_html/schedinedinotifica.tanggo.software/software/`
- Pannello cPanel/File Manager:
  - `server.hotelitalia.travel:2083`
- Base dati:
  - mantenere **nome, utente e password esattamente come sono ora in produzione**
- Obiettivo:
  - caricare **codice completo**
  - caricare **dump completo del database**
  - mantenere **tutti i dati attuali**, inclusi dati demo/esempio oggi presenti

## File locali da usare

### Backup progetto

- `/Users/jorgeluccitelli/Herd/Schedinedinotifica/backups/release-20260323-100302/schedinedinotifica-files-20260323-100302.tar.gz`

### Backup database

- `/Users/jorgeluccitelli/Herd/Schedinedinotifica/backups/release-20260323-100302/schedinedinotifica-db-20260323-100302.sql.gz`

### Checksum

- `/Users/jorgeluccitelli/Herd/Schedinedinotifica/backups/release-20260323-100302/SHA256SUMS.txt`

## Regola importante

Dopo aver importato il dump completo:

- **non eseguire** `php artisan db:seed`
- **non eseguire** `php artisan configurazione:setup`
- **non eseguire** `php artisan geo:import`

Motivo:
- il dump contiene gia tutti i dati reali e attuali del sistema
- comprese configurazioni, dati di esempio attuali, relazioni, licenze, proforme e strutture

## 1. Verifica locale del backup

Sul tuo computer locale:

```bash
cd /Users/jorgeluccitelli/Herd/Schedinedinotifica/backups/release-20260323-100302
shasum -a 256 -c SHA256SUMS.txt
```

Risultato atteso:
- entrambi i file devono risultare `OK`

## 2. Preparazione del repository Git

Se vuoi pubblicare anche via Git:

```bash
cd /Users/jorgeluccitelli/Herd/Schedinedinotifica
git remote add origin TU_URL_DEL_REPO
git push -u origin main --force
```

Se il remoto esiste gia:

```bash
git remote set-url origin TU_URL_DEL_REPO
git push -u origin main --force
```

## 3. Entrare in cPanel

Accedi a:

- `https://server.hotelitalia.travel:2083`

Poi apri:

- `File Manager`

Vai nella cartella:

- `public_html/schedinedinotifica.tanggo.software/software/`

## 4. Backup del server prima di toccare tutto

Prima di sostituire file o database:

### Backup file esistenti del server

Nel File Manager:

1. entra in `public_html/schedinedinotifica.tanggo.software/`
2. se esiste la cartella `software/`, rinominala ad esempio in:
   - `software_backup_pre_release_20260323`

Oppure:

1. comprimi la cartella attuale
2. scaricala

### Backup database esistente del server

In cPanel:

1. apri `phpMyAdmin`
2. seleziona il database attualmente usato dal sito
3. fai `Export`
4. salva una copia del database attuale del server

## 5. Pulizia cartella applicazione sul server

Nel File Manager:

1. crea la cartella:
   - `public_html/schedinedinotifica.tanggo.software/software/`
   se non esiste
2. assicurati che sia vuota

Attenzione:
- non toccare cartelle di altri siti
- lavora solo dentro:
  - `public_html/schedinedinotifica.tanggo.software/software/`

## 6. Upload dei file applicazione

Carica questo file:

- `schedinedinotifica-files-20260323-100302.tar.gz`

Destinazione:

- `public_html/schedinedinotifica.tanggo.software/software/`

Dopo upload:

1. seleziona l archivio
2. usa `Extract`
3. estrailo **nella stessa cartella**:
   - `public_html/schedinedinotifica.tanggo.software/software/`

Risultato atteso:
- dentro `software/` devi vedere direttamente:
  - `artisan`
  - `app/`
  - `bootstrap/`
  - `config/`
  - `database/`
  - `public/`
  - `resources/`
  - `routes/`
  - `storage/`
  - `vendor/` se presente nel backup

Se invece dopo l estrazione trovi una sottocartella extra, sposta il contenuto in modo che `artisan` stia direttamente in:

- `public_html/schedinedinotifica.tanggo.software/software/artisan`

## 7. Configurazione document root

La parte pubblica di Laravel deve puntare a:

- `public_html/schedinedinotifica.tanggo.software/software/public`

Verifica quindi nel setup del dominio/subdominio che il document root del sito pubblico:

- `schedinedinotifica.tanggo.software`

punti a:

- `public_html/schedinedinotifica.tanggo.software/software/public`

Se oggi punta gia li, non cambiare nulla.

Se oggi punta invece alla cartella `software/` o ad altra cartella, correggilo.

## 8. File .env di produzione

Nel server, dentro:

- `public_html/schedinedinotifica.tanggo.software/software/`

verifica il file:

- `.env`

Controlla almeno questi valori:

```env
APP_NAME=Schedinedinotifica
APP_ENV=production
APP_DEBUG=false
APP_URL=https://schedinedinotifica.tanggo.software

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=schedinedinotifica
DB_USERNAME=tanggo
DB_PASSWORD=tanggo
```

Importante:
- tu hai chiesto di **non cambiare nome ne password del database**
- quindi mantieni gli stessi valori gia previsti in produzione

Controlla anche:

```env
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

Se sul server usi queue reali o redis, mantieni i valori gia corretti del server.

## 9. Permessi cartelle

Assicurati che siano scrivibili:

- `storage/`
- `bootstrap/cache/`

In genere:
- cartelle `755`
- file `644`

Se il provider richiede altro, usa il set standard del tuo hosting.

## 10. Import completo del database

### Metodo A: phpMyAdmin

In cPanel:

1. apri `phpMyAdmin`
2. seleziona il database:
   - `schedinedinotifica`
3. se vuoi sostituzione totale pulita:
   - svuota tutte le tabelle esistenti
   - oppure elimina e ricrea il database se il server lo consente
4. usa `Import`
5. carica:
   - `schedinedinotifica-db-20260323-100302.sql.gz`
6. avvia import

### Metodo B: terminale SSH

Se hai accesso SSH:

```bash
cd /percorso/dove-hai-caricato-il-dump
gunzip -c schedinedinotifica-db-20260323-100302.sql.gz | mysql -u tanggo -p schedinedinotifica
```

Nota:
- l import deve essere **completo**
- non devi importare solo struttura tabelle
- devi importare **tutti i dati**

## 11. Composer

Se il server ha terminale/SSH:

```bash
cd public_html/schedinedinotifica.tanggo.software/software
composer install --no-dev --optimize-autoloader --no-interaction
```

Se `vendor/` e gia presente nel backup e il server non ha composer:
- puoi usare il backup cosi com e
- ma la soluzione migliore resta eseguire `composer install` sul server

## 12. Comandi Laravel dopo import e upload

Dentro:

- `public_html/schedinedinotifica.tanggo.software/software/`

esegui in questo ordine:

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan storage:link
```

Se `storage:link` dice che il link esiste gia:
- va bene cosi

## 13. Verifica tecnica immediata

Esegui:

```bash
php artisan about
php artisan migrate:status
```

Risultato atteso:
- app in `production`
- debug `false`
- migrations tutte `Ran`

## 14. Test del sito pubblico

Apri:

- `https://schedinedinotifica.tanggo.software`

Verifica:

1. pagina login
2. accesso senza errori 500
3. dashboard struttura
4. dati struttura
5. calendario
6. notifiche
7. supporto

## 15. Test funzionale dei ruoli

### SuperAdmin

Controlla:
- amministratori
- proprietari
- strutture
- articoli
- pagamenti / licenze
- proforme

### Admin

Controlla:
- proprietari
- strutture
- pagamenti / licenze
- catalogo articoli in sola lettura

### Proprietario

Controlla:
- strutture
- servizi
- proforme
- visibilita licenze delle strutture

### Struttura

Controlla:
- dashboard
- dati struttura
- relazioni e pagamenti
- licenze e conto

## 16. Verifica commerciale

Controlla su almeno una struttura reale:

- licenza assegnata
- numero/tracking licenza
- piano
- stato pagamento
- data scadenza
- visibilita nel calendario
- visibilita nelle notifiche
- visibilita in supporto

Controlla su almeno un proprietario:

- strutture collegate
- licenze delle strutture visibili
- proforme visibili

Controlla su almeno un amministratore:

- proprietari assegnati
- servizi amministratore
- proforme amministratore

## 17. Cose da NON fare dopo il dump

Non eseguire:

```bash
php artisan db:seed
php artisan configurazione:setup
php artisan geo:import
```

Motivo:
- il dump gia contiene i dati completi e reali
- rischi duplicazioni o riallineamenti non voluti

## 18. Controllo finale prima di dichiararlo online

Deve essere tutto vero:

- il dominio pubblico apre il sistema
- il document root punta a `software/public`
- la base dati corretta e collegata
- login ok
- niente errori 500
- superadmin ok
- admin ok
- proprietario ok
- struttura ok
- licenze visibili
- pagamenti visibili
- proforme visibili
- calendario ok
- notifiche ok
- supporto ok

## 19. Script alternativo

Se hai accesso shell e vuoi usare lo script preparato nel progetto:

```bash
cd public_html/schedinedinotifica.tanggo.software/software
bash scripts/deploy-production.sh public_html/schedinedinotifica.tanggo.software/software
```

## 20. Assunzione importante

Ho assunto che la cartella corretta sia:

- `public_html/schedinedinotifica.tanggo.software/software/`

perche il testo del tuo messaggio aveva una parte con punteggiatura spezzata.

Se il path reale del dominio in cPanel e leggermente diverso, usa **sempre il path reale del dominio pubblico** ma mantieni questa regola:

- Laravel root in `.../software/`
- document root web in `.../software/public`

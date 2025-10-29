# Guida Aggiornamento Velzon
## Update Velzon da 4.3.0 a versione più recente

---

## 🎯 Versione Attuale
- **Velzon**: 4.3.0
- **Bootstrap**: 5.3.3
- **Laravel**: 11.x
- **Vite**: 7.1.12

---

## 📋 Prerequisiti

1. **Backup completo progetto**
2. **Accesso al tuo account Themesbrand** per scaricare l'ultima versione
3. **Git commit** di tutti i cambiamenti attuali

---

## 🔄 Metodo 1: Aggiornamento Manuale (CONSIGLIATO)

### Step 1: Esegui Backup
```bash
# Rendi eseguibile lo script di backup
chmod +x backup_before_velzon_update.sh

# Esegui backup
./backup_before_velzon_update.sh
```

### Step 2: Scarica Nuova Versione Velzon
1. Accedi a https://themesbrand.com/
2. Vai su "My Downloads"
3. Scarica **Velzon Laravel** versione più recente (es. 4.4.0, 5.0.0, ecc.)
4. Estrai il file ZIP in una cartella temporanea

### Step 3: Identifica File da Aggiornare

**File SAFE da sovrascrivere** (non personalizzati):
```bash
# Assets Velzon originali
resources/js/app.js
resources/js/layout.js
resources/js/pages/*.js (tranne personalizzazioni)
resources/js/plugins.js

# SCSS Velzon
resources/scss/**/*.scss (se non modificati)

# Librerie pubbliche
public/build/libs/**/*

# Layouts base Velzon
resources/views/dashboard-*.blade.php (se non usati)
resources/views/error/*.blade.php (layouts errore)

# Package dependencies
package.json (confrontare e merge)
```

**File DA NON SOVRASCRIVERE** (personalizzati):
```bash
# Viste applicazione custom
resources/views/customers/**
resources/views/componenti/**
resources/views/schedina/**
resources/views/group/**
resources/views/subgroup/**
resources/views/subgroup1/**
resources/views/estructura/**
resources/views/arrivals/**
resources/views/title/**
resources/views/typedoc/**
resources/views/released/**
resources/views/typestreet/**

# Componenti Blade custom
resources/views/components/**

# Layouts modificati
resources/views/layouts/topbar.blade.php (lingua rimossa)
resources/views/layouts/sidebar.blade.php (menu personalizzato)
resources/views/layouts/menu.blade.php (menu items custom)

# Traduzioni
resources/lang/it/**

# Config Laravel
config/**
routes/**
app/**

# JS Custom
public/js/autofill-select.js
```

### Step 4: Merge Package.json

**Confronta e aggiorna manualmente**:

```bash
# Backup attuale
cp package.json package.json.old

# Apri il nuovo package.json di Velzon e confronta:
# - Nuove dipendenze
# - Versioni aggiornate
# - Nuovi script

# Esempio merge (mantieni personalizzazioni + aggiungi nuove deps):
```

**Esempio package.json aggiornato**:
```json
{
    "private": true,
    "name": "velzon",
    "version": "4.4.0",  // ← Aggiorna versione
    "type": "module",
    "author": "Themesbrand",
    "scripts": {
        "clean": "rimraf public/build",
        "dev": "vite",
        "build": "npm run clean && vite build",
        "build-rtl": "rtlcss public/build/css/app.min.css & rtlcss public/build/css/bootstrap.min.css"
    },
    "devDependencies": {
        // Confronta e aggiorna versioni...
        "vite": "^7.1.12"  // Verifica se c'è versione più recente
    },
    "dependencies": {
        // Aggiungi nuove dipendenze dalla nuova versione
        // Aggiorna versioni esistenti
    }
}
```

### Step 5: Copia Nuovi File Velzon

```bash
# Dalla cartella Velzon scaricata:

# 1. Aggiorna JavaScript core
cp -r velzon-new/resources/js/app.js resources/js/
cp -r velzon-new/resources/js/layout.js resources/js/
cp -r velzon-new/resources/js/pages/ resources/js/pages/
# (Attenzione a non sovrascrivere file personalizzati)

# 2. Aggiorna SCSS (se non modificato)
cp -r velzon-new/resources/scss/ resources/scss/

# 3. Aggiorna librerie pubbliche
rm -rf public/build/libs
cp -r velzon-new/public/build/libs/ public/build/libs/

# 4. Aggiorna layouts BASE (NON quelli personalizzati)
# SOLO se non hai modifiche:
# cp velzon-new/resources/views/layouts/head-css.blade.php resources/views/layouts/
# cp velzon-new/resources/views/layouts/vendor-scripts.blade.php resources/views/layouts/

# 5. Nuovo vite.config.js (se ha miglioramenti)
# Confronta manualmente prima di sovrascrivere:
diff vite.config.js velzon-new/vite.config.js
```

### Step 6: Installa Dipendenze e Ricompila

```bash
# Rimuovi vecchi node_modules
rm -rf node_modules
rm package-lock.json

# Installa nuove dipendenze
npm install

# Ricostruisci assets
npm run build

# O per sviluppo:
npm run dev
```

### Step 7: Verifica Compatibilità

**Test checklist**:
```bash
# 1. Verifica build
npm run build

# 2. Pulisci cache Laravel
php artisan view:clear
php artisan cache:clear
php artisan config:clear

# 3. Testa applicazione
php artisan serve

# Visita: http://localhost:8000
# Testa:
# - ✅ Login funziona
# - ✅ Menu sidebar corretto
# - ✅ Tabelle customers/componenti/schedina
# - ✅ Form funzionano
# - ✅ Modal si aprono
# - ✅ DataTables caricano
# - ✅ Traduzioni italiane visibili
```

### Step 8: Gestisci Breaking Changes

Se trovi errori, verifica la documentazione Velzon per breaking changes:

**Changelog comune tra versioni**:
- Bootstrap 5.3 → 5.4 (cambio classi CSS)
- DataTables API changes
- Vite configuration updates
- New layout options

---

## 🔄 Metodo 2: Aggiornamento Selettivo (PIÙ SICURO)

Aggiorna solo componenti specifici mantenendo tutto il resto:

### Aggiorna Solo Librerie JavaScript

```bash
# Backup
cp package.json package.json.backup

# Aggiorna solo le librerie che vuoi:
npm update apexcharts
npm update sweetalert2
npm update flatpickr
npm update choices.js
npm update gridjs
npm update bootstrap

# Rebuild
npm run build
```

### Aggiorna Solo Stili/CSS

```bash
# Copia nuovi file SCSS Velzon
cp -r velzon-new/resources/scss/config/ resources/scss/config/
cp -r velzon-new/resources/scss/custom/ resources/scss/custom/

# Rebuild
npm run build
```

---

## 🔄 Metodo 3: Aggiornamento NPM Automatico (PIÙ VELOCE, MENO SICURO)

```bash
# Check outdated packages
npm outdated

# Aggiorna tutte le dipendenze (attenzione a breaking changes!)
npm update

# O usa npm-check-updates per major updates
npx npm-check-updates -u
npm install

# Rebuild
npm run build
```

---

## ⚠️ Problemi Comuni e Soluzioni

### Problema 1: Vite build fallisce
```bash
# Soluzione: Pulisci cache
rm -rf node_modules
rm package-lock.json
npm install
npm run build
```

### Problema 2: Stili non caricano
```bash
# Soluzione: Ricompila SCSS
npm run build
php artisan view:clear
```

### Problema 3: JavaScript errors in console
```bash
# Controlla compatibilità librerie
# Verifica vendor-scripts.blade.php ha i path corretti
# Controlla console browser per file mancanti
```

### Problema 4: Layout rotto
```bash
# Ripristina layouts da backup
cp backup_velzon_*/topbar.blade.php resources/views/layouts/
cp backup_velzon_*/sidebar.blade.php resources/views/layouts/
```

---

## 📝 Post-Aggiornamento Checklist

- [ ] Build assets completato senza errori
- [ ] Login funziona
- [ ] Menu sidebar mostra voci corrette
- [ ] Tabelle DataTables caricano dati
- [ ] Form salvano correttamente
- [ ] Modal si aprono e chiudono
- [ ] Date picker funziona
- [ ] Select con ricerca (Choices.js) funziona
- [ ] SweetAlert mostra conferme eliminazione
- [ ] Export Excel/PDF funzionano
- [ ] Traduzioni italiane visibili
- [ ] Responsive mobile funziona
- [ ] Nessun errore in console browser
- [ ] Nessun errore in log Laravel

---

## 🚀 Se Vuoi Assistenza

**Posso aiutarti in due modi**:

### Opzione A: **Aggiornamento Guidato**
1. Tu scarichi la nuova versione Velzon
2. La carichi in una cartella temporanea
3. Ti guido passo-passo nel merge (ti mostro file per file cosa aggiornare)

### Opzione B: **Aggiornamento Automatico Script**
Ti creo uno script bash che:
- Fa backup automatico
- Confronta file nuovi vs vecchi
- Mostra diff per conflitti
- Aggiorna solo file safe
- Ti chiede conferma per file personalizzati

---

## 📞 Prossimo Step

**Dimmi**:
1. Hai già scaricato la nuova versione Velzon? (quale versione?)
2. Preferisci Metodo 1 (manuale), 2 (selettivo) o 3 (automatico)?
3. Vuoi che ti aiuti con uno script di merge automatico?

**Consiglio**: Inizia con **Metodo 2** (aggiorna solo le librerie npm) per testare compatibilità senza rischi! 🎯

# Piano Ottimizzazione Velzon Minimal
## Analisi completa progetto vs Reference minimal

### 📦 Aggiornamenti Package.json Necessari

**Librerie da aggiornare:**
```json
"vite": "^5.0.12" → "^7.1.12" (importante per build performance)
"sass-embedded": "^1.89.2" → "^1.93.2"
"apexcharts": "^4.7.0" → "^3.54.0" (downgrade: v4 è alpha)
"bootstrap": "5.3.6" → "5.3.3" (rollback stabile)
"chart.js": "4.4.9" → "4.4.4" (rollback stabile)
"moment": "2.24.0" → "^2.30.1" (aggiornamento importante sicurezza)
"quill": "^1.3.7" → "^2.0.3" (major update)
"simplebar": "^6.3.1" → "^6.2.7" (rollback stabile)
"sweetalert2": "^11.22.0" → "^11.14.1" (rollback stabile)
"swiper": "^11.2.8" → "^11.1.14" (rollback stabile)
"sortablejs": "^1.15.6" → "^1.15.3" (rollback stabile)
"@ckeditor/ckeditor5-build-classic": "^40.1.0" → "^39.0.2" (rollback)
```

**Consiglio:** Usa le versioni del reference minimal (più testate e stabili).

---

### 🎯 Campi Date da Convertire a Flatpickr

**Trovati 10+ campi type="date" da ottimizzare:**

#### arrivals/new.blade.php (5 campi)
1. Line 142: `arrive` (data arrivo) → **Range picker**
2. Line 152: `departure` (data partenza) → **Range picker**
3. Line 290: `oa_date_nac` (data nascita) → **Basic picker**
4. Line 457: `or_published_date` (data rilascio documento) → **Basic picker**
5. Line 466: `or_expire` (data scadenza documento) → **Basic picker**

#### schedina/edit.blade.php (5 campi)
6. Line 146: `arrive` (data arrivo) → **Range picker**
7. Line 156: `departure` (data partenza) → **Range picker**
8. Line 271: `oa_date_nac` (data nascita) → **Basic picker**
9. Line 421: `or_published_date` (data rilascio) → **Basic picker**
10. Line 430: `or_expire` (data scadenza) → **Basic picker**

#### schedina/new.blade.php
- Stessi campi di arrivals/new.blade.php

#### customers/new.blade.php & edit.blade.php
- Da verificare se ci sono campi data (probabile birth_date)

---

### 📋 Pattern Flatpickr da Applicare

#### Basic Date Picker (singola data)
```html
<!-- PRIMA -->
<input type="date" class="form-control" name="oa_date_nac">

<!-- DOPO -->
<input type="text" class="form-control" 
       name="oa_date_nac"
       data-provider="flatpickr" 
       data-date-format="d M, Y"
       placeholder="Seleziona data">
```

#### Range Date Picker (arrivo-partenza)
```html
<!-- PRIMA -->
<input type="date" class="form-control" name="arrive">
<input type="date" class="form-control" name="departure">

<!-- DOPO (campo combinato) -->
<input type="text" class="form-control" 
       name="arrive_departure"
       data-provider="flatpickr" 
       data-date-format="d M, Y"
       data-range-date="true"
       placeholder="Seleziona periodo">

<!-- O due campi separati ma collegati -->
<input type="text" class="form-control flatpickr-input" 
       name="arrive"
       data-provider="flatpickr" 
       data-date-format="d M, Y"
       placeholder="Data arrivo">
       
<input type="text" class="form-control flatpickr-input" 
       name="departure"
       data-provider="flatpickr" 
       data-date-format="d M, Y"
       data-min-date-from="#arrive"
       placeholder="Data partenza">
```

---

### 🔧 Layouts da Aggiornare

**Differenze trovate in:**
1. `layouts/footer.blade.php`
2. `layouts/head-css.blade.php`
3. `layouts/master.blade.php`
4. `layouts/sidebar.blade.php` (già personalizzato con menu)
5. `layouts/topbar.blade.php` (già personalizzato, lingua rimossa)
6. `layouts/vendor-scripts.blade.php`

**Azioni:**
- ✅ Sidebar e Topbar: Mantieni personalizzazioni (menu, traduzioni)
- 🔄 head-css: Verifica se mancano CSS Flatpickr
- 🔄 vendor-scripts: Verifica se manca init Flatpickr
- 🔄 master: Confronta struttura HTML

---

### 📂 File JS da Verificare/Aggiungere

Dal reference minimal, questi init potrebbero servire:
- `resources/js/pages/form-advanced.init.js` (Flatpickr, Choices, Multi-select)
- `resources/js/pages/apps-calendar.init.js` (FullCalendar con eventi)
- `resources/js/pages/datatables.init.js` (già presente, verificare versione)

---

### ✅ Checklist Ottimizzazione

#### Fase 1: Aggiornamento Dipendenze
- [ ] Backup package.json attuale
- [ ] Aggiorna package.json con versioni minimal
- [ ] `npm install`
- [ ] `npm run build`
- [ ] Testa che non ci siano breaking changes

#### Fase 2: Flatpickr Setup
- [ ] Verifica presenza Flatpickr CSS in head-css.blade.php
- [ ] Verifica presenza Flatpickr JS in vendor-scripts.blade.php
- [ ] Copia `form-advanced.init.js` dal reference
- [ ] Include init script in viste con date

#### Fase 3: Conversione Campi Date
- [ ] arrivals/new.blade.php (5 campi)
- [ ] schedina/new.blade.php (5 campi)
- [ ] schedina/edit.blade.php (5 campi)
- [ ] customers/new.blade.php (se presente)
- [ ] customers/edit.blade.php (se presente)
- [ ] componenti forms (se presenti)

#### Fase 4: DataTables con AJAX
- [ ] customers/list.blade.php → AJAX server-side
- [ ] componenti/list.blade.php → AJAX server-side
- [ ] schedina/list.blade.php → AJAX server-side
- [ ] arrivals/list.blade.php → AJAX server-side

#### Fase 5: Componenti Blade Ottimizzati
- [ ] Verifica utilizzo componenti esistenti (card, modal, button, datatable)
- [ ] Refactoring forms con pattern minimal
- [ ] SweetAlert2 per conferme eliminazione

#### Fase 6: Layouts Sync
- [ ] Confronta head-css.blade.php
- [ ] Confronta vendor-scripts.blade.php
- [ ] Confronta master.blade.php (struttura)
- [ ] Mantieni sidebar/topbar personalizzati

---

### 🎨 Funzionalità Velzon Minimal da Sfruttare

**Form Components:**
- Flatpickr (date picker)
- Choices.js (select con ricerca)
- Cleave.js (input formatting - telefono, CF, ecc.)
- Input Masks

**UI Components:**
- SweetAlert2 (conferme/notifiche)
- Toastify (toast notifications)
- Modali Bootstrap 5
- Offcanvas per filtri

**Data Display:**
- DataTables server-side
- GridJS (alternativa leggera)
- List.js (filtri client-side)

**Advanced:**
- FullCalendar per calendario prenotazioni
- Drag & Drop (Dragula, Sortable)
- File Upload (Dropzone, FilePond)

---

### 🚀 Priorità Implementazione

**Alta Priorità:**
1. Flatpickr su tutti i campi date (usabilità immediata)
2. Package.json aggiornato (sicurezza + performance)
3. DataTables AJAX su liste (performance con tanti record)

**Media Priorità:**
4. SweetAlert2 per conferme eliminazione
5. Choices.js su select con tante opzioni
6. Form validation unificata

**Bassa Priorità:**
7. FullCalendar per vista calendario prenotazioni
8. File upload avanzato (se necessario)
9. Dashboard charts (se necessario)

---

### 📝 Note Tecniche

**Flatpickr Configurazione Italiana:**
```javascript
flatpickr("[data-provider='flatpickr']", {
    locale: 'it',
    dateFormat: 'd M, Y',
    allowInput: true,
    disableMobile: true // Forza uso flatpickr anche su mobile
});
```

**Range Picker con minDate/maxDate:**
```javascript
const arriveInput = flatpickr("#arrive", {
    locale: 'it',
    dateFormat: 'd M, Y',
    onChange: function(selectedDates, dateStr, instance) {
        departureInput.set('minDate', dateStr);
    }
});

const departureInput = flatpickr("#departure", {
    locale: 'it',
    dateFormat: 'd M, Y',
    minDate: "today"
});
```

---

## Prossimi Step Operativi

1. **Vuoi che inizi subito con Flatpickr?** (conversione immediata tutti i campi date)
2. **Preferisci prima aggiornare package.json?** (poi Flatpickr)
3. **Vuoi un approccio ibrido?** (Flatpickr ora + package.json dopo)

Dimmi da dove partire e procedo sistematicamente! 🚀

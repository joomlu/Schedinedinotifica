#!/bin/bash
# Script per backup pre-aggiornamento Velzon
# Uso: ./backup_before_velzon_update.sh

echo "🔄 Backup progetto prima dell'aggiornamento Velzon..."

# Crea directory backup con timestamp
BACKUP_DIR="backup_velzon_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Backup file critici
echo "📦 Backup file di configurazione..."
cp package.json "$BACKUP_DIR/"
cp package-lock.json "$BACKUP_DIR/"
cp vite.config.js "$BACKUP_DIR/"

# Backup risorse personalizzate
echo "📦 Backup viste personalizzate..."
rsync -av --progress resources/views/customers/ "$BACKUP_DIR/views_customers/"
rsync -av --progress resources/views/componenti/ "$BACKUP_DIR/views_componenti/"
rsync -av --progress resources/views/schedina/ "$BACKUP_DIR/views_schedina/"
rsync -av --progress resources/views/group/ "$BACKUP_DIR/views_group/"
rsync -av --progress resources/views/subgroup/ "$BACKUP_DIR/views_subgroup/"
rsync -av --progress resources/views/subgroup1/ "$BACKUP_DIR/views_subgroup1/"
rsync -av --progress resources/views/estructura/ "$BACKUP_DIR/views_estructura/"
rsync -av --progress resources/views/arrivals/ "$BACKUP_DIR/views_arrivals/"
rsync -av --progress resources/views/title/ "$BACKUP_DIR/views_title/"
rsync -av --progress resources/views/typedoc/ "$BACKUP_DIR/views_typedoc/"
rsync -av --progress resources/views/released/ "$BACKUP_DIR/views_released/"
rsync -av --progress resources/views/typestreet/ "$BACKUP_DIR/views_typestreet/"

# Backup components personalizzati
echo "📦 Backup componenti Blade personalizzati..."
rsync -av --progress resources/views/components/ "$BACKUP_DIR/components/"

# Backup JS/CSS personalizzati
echo "📦 Backup assets personalizzati..."
cp -r public/js/ "$BACKUP_DIR/public_js/" 2>/dev/null || echo "No custom JS found"

# Backup layout modificati
echo "📦 Backup layouts modificati..."
cp resources/views/layouts/topbar.blade.php "$BACKUP_DIR/topbar.blade.php"
cp resources/views/layouts/sidebar.blade.php "$BACKUP_DIR/sidebar.blade.php"
cp resources/views/layouts/master.blade.php "$BACKUP_DIR/master.blade.php"

# Lista file modificati
echo "📝 Creazione lista file personalizzati..."
cat > "$BACKUP_DIR/RESTORE_NOTES.txt" << EOF
Backup creato: $(date)
Versione Velzon originale: 4.3.0

FILE PERSONALIZZATI DA PRESERVARE:
===================================

VISTE APPLICAZIONE:
- resources/views/customers/*
- resources/views/componenti/*
- resources/views/schedina/*
- resources/views/group/*
- resources/views/subgroup/*
- resources/views/subgroup1/*
- resources/views/estructura/*
- resources/views/arrivals/*
- resources/views/title/*
- resources/views/typedoc/*
- resources/views/released/*
- resources/views/typestreet/*

COMPONENTI BLADE:
- resources/views/components/*

LAYOUTS MODIFICATI:
- resources/views/layouts/topbar.blade.php (rimosso selettore lingua)
- resources/views/layouts/sidebar.blade.php (menu personalizzato)
- resources/views/layouts/master.blade.php

TRADUZIONI:
- resources/lang/it/* (tutte le traduzioni italiane)

JAVASCRIPT/CSS CUSTOM:
- public/js/autofill-select.js

DOPO L'AGGIORNAMENTO:
1. Confrontare package.json nuove dipendenze
2. Verificare compatibilità vite.config.js
3. Re-importare personalizzazioni layouts
4. Verificare routing views
5. Testare componenti Blade custom
6. Ricostruire assets: npm install && npm run build
EOF

echo "✅ Backup completato in: $BACKUP_DIR"
echo "📄 Leggi $BACKUP_DIR/RESTORE_NOTES.txt per dettagli ripristino"

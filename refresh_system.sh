#!/bin/bash
# =====================================================
# 🔁 REFRESCO COMPLETO DEL SISTEMA LARAVEL
# Autor: Jorge Luccitelli
# Uso: ./refresh_system.sh
# =====================================================

echo "====================================================="
echo "🔄 REFRESCANDO ENTORNO LARAVEL – $(date '+%Y-%m-%d %H:%M:%S')"
echo "====================================================="

# Verificar que estemos en un proyecto Laravel
if [ ! -f artisan ]; then
  echo "✖ No se encontró artisan. Ejecutá este script desde la raíz del proyecto."
  exit 1
fi

# Limpiar cachés de Laravel
echo "🧹 Limpiando cachés..."
XDEBUG_MODE=off php artisan cache:clear
XDEBUG_MODE=off php artisan route:clear
XDEBUG_MODE=off php artisan config:clear
XDEBUG_MODE=off php artisan view:clear
XDEBUG_MODE=off php artisan event:clear
XDEBUG_MODE=off php artisan optimize:clear

# Recompilar cachés
echo "⚙️  Recompilando configuración..."
XDEBUG_MODE=off php artisan config:cache
XDEBUG_MODE=off php artisan route:cache
XDEBUG_MODE=off php artisan view:cache
XDEBUG_MODE=off php artisan optimize

# Limpiar logs viejos
echo "🧾 Limpiando logs..."
find storage/logs -type f -name "*.log" -delete 2>/dev/null || true

# Sincronizar dependencias
echo "📦 Verificando dependencias..."
composer install --no-interaction --prefer-dist --quiet
composer dump-autoload -o

# Recompilar assets frontend (si existe package.json)
if [ -f package.json ]; then
  echo "🎨 Recompilando assets (npm run build)..."
  if command -v npm >/dev/null 2>&1; then
    npm run build >/dev/null 2>&1 || npm run dev >/dev/null 2>&1
  else
    echo "⚠️  npm no está instalado, saltando build frontend."
  fi
fi

# Permisos
echo "🔐 Corrigiendo permisos de storage y cache..."
chmod -R 775 storage bootstrap/cache || true

echo "====================================================="
echo "✅ SISTEMA REFRESCADO CORRECTAMENTE"
echo "Abrí ahora en el navegador → http://schedinedinotifica.test/"
echo "====================================================="

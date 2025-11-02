#!/usr/bin/env bash
set -euo pipefail

# =========================================================
# RESTORE – Proyecto Laravel + Base MySQL (Herd local)
# =========================================================
# Uso:
#   ./restore.sh <TARBALL>.tar.gz [--project-dir PATH] [--db-name NAME] [--db-user USER] [--db-pass PASS] [--db-host HOST] [--db-port PORT] [--no-code] [--no-db] [--skip-composer]
# Ejemplo:
#   ./restore.sh Schedinedinotifica_2025-01-01_12-00.tar.gz --project-dir /Users/jorgeluccitelli/Herd/Schedinedinotifica
#
# Nota: El dump .sql se busca automáticamente en la misma carpeta del tarball
#       por sufijo de fecha (p.ej. *_2025-01-01_12-00.sql)

# -------- Config por defecto (sobrescribibles por flags) --------
PROJECT_DIR="/Users/jorgeluccitelli/Herd/Schedinedinotifica"
DB_NAME="schedinedinotifica"
DB_USER="tanggo"
DB_PASS="tanggo"
DB_HOST="127.0.0.1"
DB_PORT="3306"
DO_CODE=1
DO_DB=1
RUN_COMPOSER=1

ok(){ printf "\033[1;32m✔ %s\033[0m\n" "$*"; }
info(){ printf "\033[1;36m➜ %s\033[0m\n" "$*"; }
warn(){ printf "\033[1;33m⚠ %s\033[0m\n" "$*"; }
err(){ printf "\033[1;31m✖ %s\033[0m\n" "$*" >&2; exit 1; }
need(){ command -v "$1" >/dev/null 2>&1 || err "Falta comando: $1"; }

usage(){ sed -n '1,40p' "$0" | sed 's/^# \{0,1\}//'; exit 1; }

# -------- Parseo de argumentos --------
[[ $# -lt 1 ]] && usage
TARBALL="$1"; shift || true

while [[ $# -gt 0 ]]; do
  case "$1" in
    --project-dir) PROJECT_DIR="$2"; shift 2 ;;
    --db-name)     DB_NAME="$2"; shift 2 ;;
    --db-user)     DB_USER="$2"; shift 2 ;;
    --db-pass)     DB_PASS="$2"; shift 2 ;;
    --db-host)     DB_HOST="$2"; shift 2 ;;
    --db-port)     DB_PORT="$2"; shift 2 ;;
    --no-code)     DO_CODE=0; shift ;;
    --no-db)       DO_DB=0; shift ;;
    --skip-composer) RUN_COMPOSER=0; shift ;;
    -h|--help)     usage ;;
    *) err "Flag desconocido: $1" ;;
  esac
done

# -------- Checks previos --------
[[ -f "$TARBALL" ]] || err "No existe el tarball: $TARBALL"
need tar
if [[ $DO_DB -eq 1 ]]; then
  need mysql
  need mysqladmin
fi

BACKUP_DIR="$(cd "$(dirname "$TARBALL")" && pwd)"
BASENAME="$(basename "$TARBALL")"
# Espera nombre tipo: <NAME>_YYYY-MM-DD_HH-MM.tar.gz
DATE_TAG="${BASENAME%.tar.gz}"; DATE_TAG="${DATE_TAG##*_}"

# Buscar SQL por sufijo de fecha
SQL_FILE=""
if [[ $DO_DB -eq 1 ]]; then
  mapfile -t CANDIDATES < <(find "$BACKUP_DIR" -maxdepth 1 -type f -name "*_${DATE_TAG}.sql" | sort)
  if [[ ${#CANDIDATES[@]} -gt 0 ]]; then
    SQL_FILE="${CANDIDATES[-1]}" # el más reciente
  else
    warn "No se encontró dump SQL con sufijo ${DATE_TAG} en ${BACKUP_DIR}. Se omitirá la restauración de DB."
    DO_DB=0
  fi
fi

info "Tarball: $TARBALL"
[[ -n "$SQL_FILE" ]] && info "SQL:     $SQL_FILE"
info "Destino del proyecto: $PROJECT_DIR"

# -------- Extracción --------
EXTRACT_DIR="${BACKUP_DIR}/restore_${DATE_TAG}"
rm -rf "$EXTRACT_DIR"
mkdir -p "$EXTRACT_DIR"
info "Extrayendo proyecto en $EXTRACT_DIR ..."

tar -xzf "$TARBALL" -C "$EXTRACT_DIR"
# Si el tar contiene una carpeta raíz, tomarla; si no, usar tal cual
ROOT_SUBDIR="$(find "$EXTRACT_DIR" -mindepth 1 -maxdepth 1 -type d | head -n 1 || true)"
[[ -z "$ROOT_SUBDIR" ]] && ROOT_SUBDIR="$EXTRACT_DIR"
ok "Extracción completada"

# Intentar leer .env extraído para ajustar DB si corresponde
if [[ -f "$ROOT_SUBDIR/.env" ]]; then
  DB_ENV_NAME="$(grep -E '^DB_DATABASE=' "$ROOT_SUBDIR/.env" | cut -d'=' -f2- || true)"
  DB_ENV_USER="$(grep -E '^DB_USERNAME=' "$ROOT_SUBDIR/.env" | cut -d'=' -f2- || true)"
  DB_ENV_PASS="$(grep -E '^DB_PASSWORD=' "$ROOT_SUBDIR/.env" | cut -d'=' -f2- || true)"
  DB_ENV_HOST="$(grep -E '^DB_HOST='     "$ROOT_SUBDIR/.env" | cut -d'=' -f2- || true)"
  DB_ENV_PORT="$(grep -E '^DB_PORT='     "$ROOT_SUBDIR/.env" | cut -d'=' -f2- || true)"
  [[ -n "$DB_ENV_NAME" ]] && DB_NAME="$DB_ENV_NAME"
  [[ -n "$DB_ENV_USER" ]] && DB_USER="$DB_ENV_USER"
  [[ -n "$DB_ENV_PASS" ]] && DB_PASS="$DB_ENV_PASS"
  [[ -n "$DB_ENV_HOST" ]] && DB_HOST="$DB_ENV_HOST"
  [[ -n "$DB_ENV_PORT" ]] && DB_PORT="$DB_ENV_PORT"
  info "DB detectada desde .env: ${DB_NAME}@${DB_HOST}:${DB_PORT} (usuario ${DB_USER})"
fi

# -------- Restaurar código --------
if [[ $DO_CODE -eq 1 ]]; then
  if [[ -d "$PROJECT_DIR" ]]; then
    BACKUP_DIR_EXISTENTE="${PROJECT_DIR}.bak_${DATE_TAG}"
    info "Moviendo directorio actual a ${BACKUP_DIR_EXISTENTE} ..."
    mv "$PROJECT_DIR" "$BACKUP_DIR_EXISTENTE"
  fi
  info "Instalando proyecto en ${PROJECT_DIR} ..."
  mkdir -p "$(dirname "$PROJECT_DIR")"
  mv "$ROOT_SUBDIR" "$PROJECT_DIR"
  ok "Proyecto restaurado en ${PROJECT_DIR}"
fi

# -------- Composer (opcional) --------
if [[ $DO_CODE -eq 1 && $RUN_COMPOSER -eq 1 ]]; then
  if command -v composer >/dev/null 2>&1; then
    info "Ejecutando composer install (prod)..."
    ( cd "$PROJECT_DIR" && composer install --no-dev --prefer-dist --no-interaction ) || warn "composer install falló; puede completarse manualmente"
  else
    warn "composer no está instalado; omitiendo dependencias PHP"
  fi
fi

# -------- Restaurar base de datos --------
if [[ $DO_DB -eq 1 ]]; then
  info "Creando base de datos si no existe: ${DB_NAME}"
  mysqladmin -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} create "$DB_NAME" 2>/dev/null || true
  info "Importando dump en ${DB_NAME} ..."
  mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USER" ${DB_PASS:+-p"$DB_PASS"} "$DB_NAME" < "$SQL_FILE"
  ok "Base de datos restaurada"
fi

# -------- Post pasos útiles (solo si se restauró código) --------
if [[ $DO_CODE -eq 1 ]]; then
  # Caches de Laravel (si artisan disponible)
  if [[ -x "$PROJECT_DIR/artisan" ]]; then
    info "Limpiando y generando caches de Laravel ..."
    ( cd "$PROJECT_DIR" && php artisan config:clear && php artisan config:cache && php artisan route:clear && php artisan route:cache ) || warn "Artisan caches no pudieron regenerarse"
  fi
fi

ok "Restauración finalizada"
info "Proyecto: $PROJECT_DIR"
if [[ $DO_DB -eq 1 ]]; then
  info "Base de datos: ${DB_NAME} @ ${DB_HOST}:${DB_PORT} (usuario ${DB_USER})"
fi

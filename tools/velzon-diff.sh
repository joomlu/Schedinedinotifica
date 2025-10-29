#!/bin/bash
# Confronto tra progetto corrente e reference Velzon
# Uso:
#   tools/velzon-diff.sh summary
#   tools/velzon-diff.sh diff [section]
# section: layouts|js|scss|libs|views

set -e

ROOT_DIR=$(cd "$(dirname "$0")/.." && pwd)
REF_LARAVEL="$ROOT_DIR/reference/velzon/laravel/minimal"
REF_VUE="$ROOT_DIR/reference/velzon/laravel-vue/minimal"

error() { echo "[ERR] $1" >&2; }
info() { echo "[INF] $1"; }

check_refs() {
  local missing=0
  if [ ! -d "$REF_LARAVEL" ] || [ -z "$(ls -A "$REF_LARAVEL" 2>/dev/null)" ]; then
    info "Manca il reference Laravel: copia qui dentro la cartella estratta: $REF_LARAVEL"
    missing=1
  fi
  if [ ! -d "$REF_VUE" ] || [ -z "$(ls -A "$REF_VUE" 2>/dev/null)" ]; then
    info "(Opzionale) Manca il reference Laravel+Vue: $REF_VUE"
  fi
  return $missing
}

summary() {
  echo "=== SUMMARY REFERENCE ==="
  du -sh "$REF_LARAVEL" 2>/dev/null || true
  du -sh "$REF_VUE" 2>/dev/null || true
  echo "- Progetto: $ROOT_DIR"
  echo "- Reference Laravel: $REF_LARAVEL"
  echo "- Reference Laravel+Vue: $REF_VUE"
}

diff_section() {
  local section="$1"
  case "$section" in
    layouts)
      diff -rq "$ROOT_DIR/resources/views/layouts" "$REF_LARAVEL/resources/views/layouts" || true ;;
    js)
      diff -rq "$ROOT_DIR/resources/js" "$REF_LARAVEL/resources/js" || true ;;
    scss)
      diff -rq "$ROOT_DIR/resources/scss" "$REF_LARAVEL/resources/scss" || true ;;
    libs)
      diff -rq "$ROOT_DIR/public/build/libs" "$REF_LARAVEL/public/build/libs" || true ;;
    views)
      diff -rq "$ROOT_DIR/resources/views" "$REF_LARAVEL/resources/views" | grep -v "/layouts" || true ;;
    *)
      error "Sezione non valida. Usa: layouts|js|scss|libs|views" ;;
  esac
}

case "$1" in
  summary)
    check_refs || true
    summary
    ;;
  diff)
    check_refs || { error "Aggiungi il reference prima (vedi messaggi sopra)."; exit 1; }
    diff_section "$2"
    ;;
  *)
    echo "Uso: $0 summary | $0 diff [layouts|js|scss|libs|views]" ;;
 esac

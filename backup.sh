#!/usr/bin/env bash
set -euo pipefail

# =========================================================
# BACKUP TOTAL – Proyecto Laravel + Base MySQL (Herd local)
# =========================================================

# Configuración
PROJECT_NAME="Schedinedinotifica"
PROJECT_DIR="/Users/jorgeluccitelli/Herd/Schedinedinotifica"
BACKUP_DIR="/Users/jorgeluccitelli/Backups/Schedinedinotifica"
DB_NAME="schedinedinotifica"
DB_USER="tanggo"
DB_PASS="tanggo"
DB_HOST="127.0.0.1"
DB_PORT="3306"
DATE_TAG=$(date +"%Y-%m-%d_%H-%M")

# Utilidades visuales
ok(){ printf "\033[1;32m✔ %s\033[0m\n" "$*"; }
info(){ printf "\033[1;36m➜ %s\033[0m\n" "$*"; }
err(){ printf "\033[1;31m✖ %s\033[0m\n" "$*" >&2; exit 1; }
need(){ command -v "$1" >/dev/null 2>&1 || err "Falta comando: $1"; }

# Verificar comandos necesarios
need mysqldump
need tar
need textutil || echo "⚠️  textutil no encontrado (PDF opcional)"

# Crear carpeta destino si no existe
mkdir -p "$BACKUP_DIR"

# Paths
SQL_FILE="${BACKUP_DIR}/${DB_NAME}_${DATE_TAG}.sql"
README_MD="${BACKUP_DIR}/README_${DATE_TAG}.md"
README_PDF="${BACKUP_DIR}/README_${DATE_TAG}.pdf"
TAR_FILE="${BACKUP_DIR}/${PROJECT_NAME}_${DATE_TAG}.tar.gz"

echo "========================================================="
echo "▶ Iniciando Backup Total de $PROJECT_NAME"
echo "========================================================="

# 1) Dump de la base de datos
info "Creando dump MySQL..."
if [ -n "$DB_PASS" ]; then
  mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" --routines --triggers --events "$DB_NAME" > "$SQL_FILE"
else
  mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" "$DB_NAME" > "$SQL_FILE"
fi
ok "Dump creado: $SQL_FILE"

# 2) Generar README con instrucciones completas
cat > "$README_MD" <<EOF
# Backup Completo del Proyecto ${PROJECT_NAME}

**Fecha:** $(date)
**Proyecto Laravel:** ${PROJECT_DIR}
**Base de datos:** ${DB_NAME}
**Usuario:** ${DB_USER}
**Host:** ${DB_HOST}
**Puerto:** ${DB_PORT}

---

## 📦 Archivos incluidos
- Proyecto completo de Laravel
- Base de datos: \`${SQL_FILE}\`
- Este README (instrucciones)
- Versión PDF del README

---

## 🔁 Instrucciones para restaurar el backup

1. **Copiar el archivo .tar.gz** a la carpeta local de backups:
   \`${BACKUP_DIR}\`

2. **Ejecutar el script de restauración:**
   ```bash
   cd ${BACKUP_DIR}
   ./restore.sh ${PROJECT_NAME}_${DATE_TAG}.tar.gz
   ```

3. **(Opcional) Ajustar variables** con flags si cambiaste credenciales o ruta del proyecto, por ejemplo:
   ```bash
   ./restore.sh ${PROJECT_NAME}_${DATE_TAG}.tar.gz \
     --project-dir ${PROJECT_DIR} \
     --db-name ${DB_NAME} --db-user ${DB_USER} --db-pass ${DB_PASS} \
     --db-host ${DB_HOST} --db-port ${DB_PORT}
   ```

---

## ℹ️ Notas

- Este backup incluye el proyecto Laravel y el dump SQL indicado arriba.
- Tras restaurar, corre:
  ```bash
  php artisan config:clear && php artisan config:cache
  ```
  para regenerar caches.

EOF

# 3) Convertir README a PDF (opcional)
if command -v textutil >/dev/null 2>&1; then
  textutil -convert pdf "$README_MD" -output "$README_PDF" || true
fi

# 4) Empaquetar el proyecto completo en tar.gz
info "Comprimiendo proyecto en tar.gz ..."
tar -czf "$TAR_FILE" -C "$(dirname "$PROJECT_DIR")" "$(basename "$PROJECT_DIR")"
ok "Proyecto empaquetado: $TAR_FILE"

echo "========================================================="
ok "Backup finalizado"
echo "- SQL:    $SQL_FILE"
echo "- README: $README_MD"
[[ -f "$README_PDF" ]] && echo "- PDF:    $README_PDF"
echo "- TAR:    $TAR_FILE"

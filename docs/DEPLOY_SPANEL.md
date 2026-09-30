# Despliegue de la versión LOCAL en SPanel

Fecha de auditoría: 2026-09-29. Flujo: **LOCAL → GitHub → nuevo SPanel**.
El servidor anterior NO es fuente de código. Rama de preparación: `production-deploy`. El merge a main requiere aprobación expresa.

## Actualizaciones conservadoras con deploy.sh (revisión 2026-09-30)

Producción ya funciona con PHP 8.3, Laravel 11, MariaDB, Node 20 y npm 10. No repetir el traslado inicial ni importar la base. Las secciones de instalación siguientes son históricas; las actualizaciones usan exclusivamente este procedimiento, **todavía no ejecutado en SPanel**.

`deploy.sh` mantiene `set -euo pipefail` y delega en un supervisor Python 3.9+ (biblioteca estándar) y un ejecutor PHP revisado. Se distribuyen **los cuatro archivos juntos**: `deploy.sh`, `scripts/deployment/deploy.py`, `scripts/deployment/artisan.php` y `scripts/deployment/maintenance.php`. El wrapper `scripts/deploy-production.sh` conserva el punto de entrada antiguo, sin migración automática.

El destino está fijado a `/home/tanggosoftware/repos/schedinedinotifica`, rama `main`, Document Root `public`, usuario Unix/PHP-FPM `tanggosoftware`; requiere sesiones/cache en archivos, filesystem local, mantenimiento file y queue sync. Con workers, Redis o rutas distintas, se detiene: adaptar y revisar antes de desplegar. No cambia servicios, dueño, ACL ni SELinux.

### Antes de aprobar el primer despliegue

- Revisar/publicar el cambio mediante el flujo Git autorizado; registrar el SHA completo aprobado de main.
- Tener un backup externo comprobado de MariaDB, datos persistentes y secretos. `--backup-ref` es una declaración del operador: el script no crea ni verifica backups.
- Disponer de Python 3.9+ y completar la prueba aislada de Rocky 10 descrita en `docs/AUDIT_DEPLOY_SPANEL.md`. No ejecutarla en producción.
- Confirmar PHP-FPM como el usuario indicado, vhost/TLS y OPcache con revalidación de timestamps, o coordinar su recarga. Drenar peticiones/imports y procesos externos: el script no controla los ya iniciados.
- La configuración web y CLI debe coincidir. La comprobación 503 por HTTPS directo al origen debe pasar antes de modificar código/dependencias.

### Instalar el bundle revisado y ejecutar

Los comandos siguientes son **para uso futuro tras aprobación**, no se han ejecutado durante la auditoría. Instalar fuera del checkout evita cambiar un script mientras está ejecutándose. Reemplazar los valores de ejemplo:

```bash
set -euo pipefail
umask 077
cd /home/tanggosoftware/repos/schedinedinotifica
REVIEWED_SHA=SHA_COMPLETO_DE_40_CARACTERES
DEPLOY_BUNDLE="/home/tanggosoftware/bin/schedinedinotifica-deploy-$REVIEWED_SHA"
git fetch --no-tags origin refs/heads/main
FETCHED_SHA=$(git rev-parse FETCH_HEAD) || exit 1
[[ "$FETCHED_SHA" == "$REVIEWED_SHA" ]] || exit 1
mkdir -p /home/tanggosoftware/bin
mkdir -m 700 "$DEPLOY_BUNDLE"
mkdir -p "$DEPLOY_BUNDLE/scripts/deployment"
for DEPLOY_FILE in deploy.sh scripts/deployment/deploy.py scripts/deployment/artisan.php scripts/deployment/maintenance.php; do
    git show "$REVIEWED_SHA:$DEPLOY_FILE" > "$DEPLOY_BUNDLE/$DEPLOY_FILE"
done
chmod 700 "$DEPLOY_BUNDLE/deploy.sh"

# PHP_BIN debe ser PHP CLI 8.3; COMPOSER_BIN, un PHP/phar y no wrapper shell.
PHP_BIN=/RUTA/REAL/php83 COMPOSER_BIN=/RUTA/REAL/composer \
    bash "$DEPLOY_BUNDLE/deploy.sh" --check --sha "$REVIEWED_SHA"

# Solo después de revisar preflight y backup; no autoriza migraciones.
PHP_BIN=/RUTA/REAL/php83 COMPOSER_BIN=/RUTA/REAL/composer \
    bash "$DEPLOY_BUNDLE/deploy.sh" --execute --sha "$REVIEWED_SHA" \
    --backup-ref SNAPSHOT_VERIFICADO
```

Si hay migraciones pendientes, preflight las enumera y devuelve error. Revisar cada `up()` y probar sobre una copia MariaDB. Únicamente tras aprobarlas añadir `--migrate`. Ejecuta solo la lista pendiente fijada, mediante `migrate --force --path=...`; nunca `fresh`, `refresh`, `rollback` o migraciones implícitas. `--check --migrate` sigue siendo solo preflight. No se considera `--pretend` una garantía: las migraciones contienen PHP ejecutable.

Se usa HTTPS con certificado verificado y `curl --resolve` al origen `127.0.0.1`. Si el vhost escucha en otra IP del servidor, indicar `DEPLOY_ORIGIN_IP=IP_ORIGEN`; nunca una CDN. `--help` no toca Git ni la aplicación.

### Secuencia y protecciones

1. Calcula hashes de `.env` y del valor literal de APP_KEY **antes de ejecutar código del proyecto**. Exige exactamente una APP_KEY no vacía y rechaza symlink/hardlink. Nunca imprime, restaura ni genera claves. Comprueba los hashes antes/después de cada proceso del proyecto y al finalizar, incluidos errores y señales.
2. Adquiere `flock` exclusivo en `.git/spanel-deploy.lock`, sin herencia a hijos. Exige Linux, usuario, rutas físicas, versiones, extensiones PHP y espacio libre mínimo de 2 GiB. Examina runtime y symlinks/hardlinks. Rechaza árbol/index sucio, archivos no ignorados y flags assume-unchanged/skip-worktree. No oculta cambios con stash/reset/clean.
3. Ejecuta `git fetch`, fija `FETCH_HEAD` al SHA aprobado y exige fast-forward. Usa diff NUL con `--no-renames`: cada movimiento se analiza como eliminación y adición. Rechaza cambios en `.env`, todo `storage`, `public/images`, `public/storage`, `public/build`, `public/hot`, `bootstrap/cache`, dependencias generadas y `public/index.php`. También rechaza migraciones antiguas cambiadas/borradas, symlinks y submódulos en el candidato. Los uploads ya versionados modificados bloquean aunque estén en `.gitignore`.
4. Extrae el candidato a un temporal privado, comprueba bundle idéntico, archivos necesarios, Composer/plataforma y contrato del build. Los hashes revisados de package.json, Vite, copiado de paquetes y verificación GEO fijan los destinos de escritura; cambiarlos requiere revisar/actualizar el supervisor. Rechaza rutas de instalación Composer personalizadas. PHP valida rutas de caché, vistas, sesiones, logs y filesystem, configuración activa y sin cachear, antes de providers y de cada comando. Verifica identidad de APP_KEY/configuración DB sin imprimirla.
5. Comprueba login actual y consulta MariaDB/historial de migraciones en una transacción READ ONLY. No exporta ni modifica la DB. Este preflight arranca código confiable existente y la petición GET puede generar sesión/logs. Actualiza metadatos Git y puede escribir cache Composer; no simula todo el despliegue.
6. Para ejecutar, revalida HEAD/árbol y coloca atómicamente un **guard PHP 503 independiente de vendor**, leído por el `public/index.php` revisado antes del autoload; después escribe el marcador `down`. No depende de `artisan down`. Verifica archivos y respuesta 503 con marcador único por HTTPS antes del merge fast-forward.
7. Limpia config de forma controlada y ejecuta Composer `--no-plugins install --no-dev --no-scripts --optimize-autoloader --no-interaction --prefer-dist`; comprueba plataforma. Elimina únicamente los manifests `bootstrap/cache/packages.php` y `services.php` obsoletos y ejecuta `package:discover` con el guard de rutas. No ejecuta hooks Composer que puedan generar APP_KEY o publicar archivos. El ejecutor Artisan aplica umask 0077: la nueva cache de configuración, que contiene secretos, queda en modo 0600 para el mismo usuario PHP-FPM.
8. Ejecuta `npm ci --include=dev --engine-strict --ignore-scripts --no-audit --no-fund`, y `npm --ignore-scripts run build` (con npm_config_ignore_scripts=true). El build explícito conserva clean/Vite/verificación GEO; se suprimen hooks automáticos. Verifica manifest y archivos; entonces elimina exclusivamente `node_modules`. No ejecuta npm audit fix ni cambia locks.
9. Si falta `public/storage`, ejecuta `storage:link`; después exige symlink existente con destino físico exacto `storage/app/public`. Nunca sustituye un directorio real/enlace ajeno. Añade permisos del propietario solo en `storage/framework`, `storage/logs`, `bootstrap/cache`; no recorre ni cambia permisos de `storage/app` o `public/images`. No usa chmod 777.
10. Revalida la lista de migraciones y ejecuta únicamente las explícitamente aprobadas. Ejecuta **uno por uno**, verificando códigos y resultados: `config:clear`, `route:clear`, `view:clear`, `event:clear`, `config:cache`, `route:cache`, `view:cache`, `event:cache`. No usa `optimize`, `optimize:clear` ni `cache:clear` como verificación o limpieza global; no vacía caché de negocio, sesiones o locks.
11. Comprueba pendientes, rutas, scheduler, Git, `.env`/APP_KEY y 503. Con una cookie privada temporal de bypass comprueba login, página pública y bytes del CSS por HTTPS. Revalida SHA. Solo entonces retira `down` y, al final, el guard independiente; repite login sin bypass, revisa Git/secretos y registra fecha/SHA en `.git/spanel-deploy-history`. Elimina temporal/cookie y libera el lock.

### Fallos y recuperación

Antes de activar mantenimiento, un fallo ordinario aborta sin merge/instalación. Si detecta alteración de `.env`/APP_KEY con el lock adquirido, intenta cerrar el sitio incluso durante preflight. No restaura secretos automáticamente.

Después de activar mantenimiento, cualquier fallo conserva o reinstala el guard 503; también si ocurre tras reabrir. **Solo informa mantenimiento verificado tras comprobar archivos y HTTPS 503 con el marcador correcto.** Si no puede verificarlo, imprime `CRITICAL MAINTENANCE_UNVERIFIED` y devuelve error. Alteración de secretos produce `CRITICAL ENV_INTEGRITY` (86). Fallos críticos de mantenimiento/procesos devuelven 85. No existe rollback automático.

SIGINT/SIGTERM/SIGHUP interrumpen el comando, terminan su grupo de procesos y, en Linux, recogen descendientes mediante subreaper, incluidos procesos separados de la sesión. Se comprueban secretos/mantenimiento y se libera el lock. Si no puede recoger hijos, informa estado crítico no garantizado aunque observe 503. Los hijos no heredan el descriptor del lock. SIGKILL, caída de energía o un proceso bloqueado en el kernel no permiten garantizar limpieza: inspeccionar procesos antes de reintentar; no borrar el lock para eludir la exclusión.

Tras un fallo, no ejecutar `artisan up/down` ni borrar manualmente el guard para reabrir por intuición. El preflight rechaza mantenimiento existente para obligar a una recuperación revisada. Restaurar/reparar código, vendor y build mientras el guard independiente sigue presente; revalidar estado de DB, caches, secretos y HTTPS antes de decidir la reapertura. MariaDB puede haber confirmado DDL parcial: revertir Git no restaura datos. Usar backup probado y reconciliar escrituras; nunca rollback de migraciones a ciegas.

Esto sigue siendo un despliegue en el checkout existente, con ventana de mantenimiento. El código revisado y sus dependencias se ejecutan con los permisos del usuario: **no es un sandbox frente a PHP/JavaScript malicioso** ni frente a procesos externos que modifiquen archivos simultáneamente. Los hashes detectan cambios persistentes de `.env`, pero no pueden impedir ni demostrar ausencia de una alteración transitoria realizada y deshecha por código hostil. No se certifican aquí PHP-FPM, SELinux, OPcache, TLS de SPanel, SMTP, SOAP ni todas las pantallas.

Validación: `bash -n deploy.sh scripts/deploy-production.sh` y `python3 -B -m unittest discover -s tests/Deployment -v`. La suite usa fixtures temporales y un servidor PHP de loopback; necesita permiso local para abrir ese puerto. No lee secretos reales ni conecta a una DB. Ver resultados, prueba Rocky y límites en `docs/AUDIT_DEPLOY_SPANEL.md`.

## Resultado y límites (auditoría inicial)

- Laravel bloqueado: **11.31.0**, PHP requerido `^8.2`; PHP 8.3 compatible con las restricciones del lock y con el lint local.
- `composer validate --strict` y `composer check-platform-reqs --lock --no-dev`: correctos con PHP 8.3.32. Composer local 2.10.2; ejecutar las mismas comprobaciones con Composer 2.9 del servidor.
- npm: Node 22.21.1/npm 11.12.0 usados para instalar el lock y generar assets en copia temporal. Ese fue el entorno de auditoría local. Producción confirmada el 2026-09-30: Node 20 / npm 10; el nuevo script exige esas versiones y no omite devDependencies (Vite está allí).
- No se actualizaron las versiones de composer.json/composer.lock ni package.json/package-lock.json: se conserva la versión funcional actual.
- **No aprobar todavía la exposición pública sin resolver los riesgos de seguridad**: la auditoría online del lock encontró 42 avisos Composer en 11 paquetes de producción y 61 dependencias npm afectadas (2 críticas, 17 altas, 41 moderadas, 1 baja). Los críticos npm incluyen `form-data` y `swiper`. No todos tienen la misma exposición: hay dependencias de build y bibliotecas copiadas al navegador. Ver `docs/AUDIT_SPANEL_DEPENDENCIES.md`.
- Laravel 11 terminó soporte de seguridad el 12-03-2026: https://laravel.com/docs/11.x/releases . La actualización del framework y dependencias debe probarse por separado; no ejecutar `composer update` ni `npm audit fix --force` en producción.
- No se ha accedido al nuevo servidor. TLS, PHP web, SMTP, permisos, SELinux y pruebas con la base importada siguen pendientes.

### Validación efectuada

- Lint PHP 8.3: 331 archivos, sin errores.
- `npm ci` y `npm run build`: correctos en copia temporal; verify-architecture correcto. Avisos de Sass obsoleto y bundle grande no bloqueantes. La fuente legacy hkgrotesk-bold.eot no existe; hay alternativas woff/woff2/ttf para navegadores actuales.
- `php artisan optimize`: config, eventos, rutas y vistas correctos en copia temporal con PHP 8.3 y DB SQLite en memoria aislada.
- Suite Unit: 3 tests y 15 assertions correctos; 1 aviso de deprecación de PHPUnit.
- No se ejecutó suite Feature: usa RefreshDatabase y el phpunit.xml no impone base aislada. No lanzarla contra .env local o producción.
- No se certificó instalación limpia `--no-dev` en Rocky: se verificaron constraints y arranque con el vendor local; ejecutar las comprobaciones de servidor indicadas.

## 1. Publicar la versión revisada en GitHub

Desde la copia local correcta, crear una rama antes del commit. Los archivos ya versionados del proyecto son la referencia; no copiar código del servidor anterior.

```bash
cd /Users/jorgeluccitelli/Herd/Schedinedinotifica
git status --short
git switch production-deploy
# Si todavía no existe: git switch -c production-deploy
git add .env.example .gitignore config/app.php app/Http/Middleware/QaEnabled.php resources/views/schedina/partials/form.blade.php vite.config.js public/.htaccess backup.sh docs/mysql_herd_vscode.md docs/DEPLOY-CPANEL-SCHEDINEDINOTIFICA.md resources/views/auth/passwords/email.blade.php resources/views/auth/passwords/reset.blade.php docs/DEPLOY_SPANEL.md docs/AUDIT_SPANEL_DEPENDENCIES.md
git add reference/velzon/minimal/resources/views/maps-google.blade.php
git add -u public/error_log
git rm --cached -- test-results/.last-run.json test-results/tests-Feature-_tmp_schedin-360fa-probe-schedina-submit-state/error-context.md
git diff --cached --stat
git diff --cached --check
# Revisar el diff privadamente: no incluir .env, dumps, archivos de clientes ni claves.
git commit -m "Prepare Schedinedinotifica for SPanel production deployment"
git push -u origin production-deploy
```

Abrir una PR contra `main` y revisarla. **No fusionar sin aprobación del propietario**. El despliegue de main descrito abajo requiere que esa revisión y merge ya hayan ocurrido. Registrar el SHA aprobado; no asumir que el main remoto coincide con el local.

### Inventario Git y datos

Archivos nuevos que deben añadirse: `.env.example`, `docs/DEPLOY_SPANEL.md`, `docs/AUDIT_SPANEL_DEPENDENCIES.md`. Versionar también las modificaciones indicadas en el comando anterior y la eliminación de `public/error_log`.

Al inicio había tres archivos NO versionados:

- `reference/vari/clienti_amalfi.csv`
- `reference/vari/clienti_amalfi_geo_enriched.csv`
- `reference/vari/clienti_amalfi_geo_unresolved.csv`

Son datos de importación, no código. Ahora se ignora `reference/vari/`; conservar privadamente y transferir solo si se necesitan esos trabajos. No usar `git add -f`.

`storage/app` contiene imports, exports y ficheros públicos excluidos correctamente. `public/build`, `vendor`, `node_modules`, `bootstrap/cache/*.php`, `storage/framework`, logs y el enlace `public/storage` se regeneran. `.env` y sus variantes permanecen privados; `.env.example` es la única excepción. No transferir `public/hot`.

`web/`, `public/site-assets/`, `resources/`, `package-copy-config.json`, `scripts/verify-architecture.mjs`, `scripts/geo-immutable.hashes.json` y `reference/libreria/geo/` son archivos versionados necesarios: no excluir indiscriminadamente `reference/` ni `web/`. El sitio público usa `web/*.html` y `web/assets`; comparte Laravel con el software autenticado.

Se retiraron del seguimiento Git los dos informes antiguos de `test-results/`, conservándolos localmente; los futuros informes quedan ignorados. Se sustituyó una API key literal de Google Maps en `reference/velzon/minimal/resources/views/maps-google.blade.php` por un placeholder. Su historial anterior permanece: revisar restricciones y rotar/revocar la clave si sigue activa.

Las imágenes numéricas ya versionadas de `public/images/` siguen en Git para no perder avatares actuales; futuros uploads numéricos quedan ignorados. Respaldar y transferir también `public/images/` fuera de Git. Revisar privacidad de las imágenes históricas antes de hacer público el repositorio.

Se retiró del árbol `public/error_log` (copia local privada temporal en `/tmp/spanel-public-error_log.backup`): no debe servirse ni versionarse. La eliminación en un commit no elimina su historial. Se retiró una contraseña literal de `backup.sh` y de las guías `docs/mysql_herd_vscode.md` / `docs/DEPLOY-CPANEL-SCHEDINEDINOTIFICA.md`: **rotar cualquier credencial real coincidente**, también si fue reutilizada; revisar exposición histórica. No se modificó la contraseña de la base local.

## 2. Preparar SPanel

Cuenta Unix: `tanggosoftware`. Repositorio `/home/tanggosoftware/repos/schedinedinotifica`.
Document Root del dominio **exactamente** `/home/tanggosoftware/repos/schedinedinotifica/public`.

Configurar en SPanel el dominio `schedinedinotifica.tanggo.software`, su certificado TLS y PHP web 8.3. Habilitar SSH. Confirmar PHP CLI y web por separado: la selección de PHP del dominio no garantiza la del shell. El handler cPanel `ea-php83` fue retirado; dejar que SPanel configure PHP-FPM/LiteSpeed. Para Apache/LiteSpeed permitir `.htaccess` y reescritura; si hay Nginx delante, configurar el fallback a `public/index.php`. Denegar archivos ocultos, listados y ejecución PHP dentro de uploads.

Ejecutar como la cuenta, no como root:

```bash
command -v php
php -v
command -v composer
composer --version
node --version
npm --version
php -m
```

Si `php` no es 8.3, pedir al administrador la ruta instalada y ajustar PATH antes de seguir; usar esa misma ruta absoluta en cron/workers. No asumir que existe `/usr/bin/php83`.

Extensiones: las que comprueba Composer más `pdo_mysql`, `mbstring`, `curl`, `xml`, `dom`, `fileinfo`, `openssl`, `tokenizer`, `ctype`, `session`; **soap** para Questura real y **zip** para exportaciones ZIP. Activar OPcache. Configurar límites de memoria/upload/post y tiempos según los imports reales; comprobar que PHP web tiene las mismas extensiones. Permitir salida HTTPS a ISTAT/Questura y al SMTP elegido.

Crear una base NUEVA y usuario limitado a ella en SPanel. La fuente auditada es **MySQL 8.0.44**, 58 tablas InnoDB: preferir MySQL 8.0 compatible. Si el destino es MariaDB, probar primero una restauración desechable y revisar collation, SQL y tipos; no reemplazar collations a ciegas.

Instalar una deploy key GitHub de solo lectura en la cuenta del servidor; verificar la huella SSH de GitHub antes de aceptarla. Nunca poner tokens en la URL del remoto.

```bash
mkdir -p /home/tanggosoftware/repos
cd /home/tanggosoftware/repos
git clone --branch main --single-branch git@github.com:joomlu/Schedinedinotifica.git schedinedinotifica
cd schedinedinotifica
git rev-parse HEAD
# Comparar con el SHA aprobado. Detenerse si no coincide.
umask 027
mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
cp .env.example .env
chmod 600 .env
```

Mantener el vhost cerrado al público hasta completar restauración y validación. Así no se sirve una aplicación sin configurar entre el clone y `artisan down`.

## 3. .env y APP_KEY

Editar `.env` privadamente en el servidor, sin mostrar secretos en consola compartida:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://schedinedinotifica.tanggo.software`.
- `APP_KEY`: **conservar exactamente la actual local**, transferida por canal privado. No ejecutar `key:generate` al migrar. Laravel usa AES-256-CBC. No se encontraron casts encrypted ni llamadas de cifrado de datos de negocio en app, pero Laravel cifra cookies y firma URLs con la clave. Conservarla evita invalidación de material cifrado. Las contraseñas de usuarios son hashes y no requieren recodificarse. No copiar sesiones locales; los usuarios volverán a iniciar sesión. Si la clave estuvo expuesta, planificar una rotación específica antes de publicar.
- `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` nuevos. `DB_SOCKET=` vacío: NO copiar el socket de Herd. No definir `DATABASE_URL` salvo que se quiera sustituir expresamente esta conexión.
- `CACHE_DRIVER=file`, `SESSION_DRIVER=file`, `QUEUE_CONNECTION=sync`, `FILESYSTEM_DRIVER=local`. Este proyecto lee **FILESYSTEM_DRIVER**, no FILESYSTEM_DISK; también lee CACHE_DRIVER, no CACHE_STORE.
- `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN=null`, `SESSION_COOKIE=schedinedinotifica_session`, `SESSION_LIFETIME=120`.
- `SANCTUM_STATEFUL_DOMAINS=schedinedinotifica.tanggo.software`; `ASSET_URL=` vacío (Vite interpreta el texto `null` como una URL) para assets del mismo origen.
- `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`: datos reales del proveedor. Probar reset de contraseña; no dejar Mailpit/Herd, localhost:1025 ni mailer log en producción.
- `LOG_CHANNEL=daily`, `LOG_LEVEL=warning`, `LOG_DEPRECATIONS_CHANNEL=null`. La configuración actual conserva 14 días de logs diarios; aplicar retención a logs PHP y cron por separado.
- `QA_ENABLED=false`, `UI_CSS_FALLBACK=false`. `CAMERE_REALI_ENABLED` y `CAMERE_REALI_STRUTTURE`: conservar los valores efectivos locales si se utilizan. Ahora sobreviven a config:cache.
- `BROADCAST_DRIVER=null`, `VITE_APP_NAME` sin secretos. Las variables `VITE_*` son públicas en el navegador.

El ejemplo incluye comentadas todas las variables opcionales leídas por config (Redis, AWS/S3, Mailgun/Postmark, Pusher/Ably, etc.). No activarlas sin instalar/configurar su backend. No hacen falta para la configuración file/local/sync. Credenciales Questura/ISTAT y modo simulación se almacenan en la base, no en `.env`: conservarlos y comprobar cada estructura antes de cualquier envío real.

Timezone y locale siguen como en el código actual; no se cambiaron por el despliegue. Si TLS termina en un proxy, comprobar que Laravel reconoce HTTPS. `TrustProxies` no declara proxies de confianza: configurar solo las IP reales del proxy si la topología lo requiere, nunca confiar en todos por comodidad.

## 4. Transferir la base y ficheros fuera de Git

Consulta local realizada en transacción READ ONLY: **142 migraciones aplicadas, 0 pendientes, 0 registros de migración sin archivo**. No se ejecutó ninguna migración ni escritura local.

Hay migraciones históricas destructivas en `up()`, por ejemplo eliminación de `arrivals`, `strutture`, `comuni`, `state_nac`, `released*`, borrado de huérfanos de tasas/componentes y renombrados repetidos. Por eso **restaurar estructura + datos + tabla migrations juntos**, no recrear todo ejecutando seeders/migraciones. No usar `migrate:fresh`, `refresh`, `reset`, `db:wipe`, seeders demo, `geo:import --fresh` ni comandos de limpieza/renumeración.

Detener escrituras de la aplicación local mientras se hace el snapshot final y se copian uploads. El dump no modifica la base. Sustituir los valores descriptivos; `-p` solicita la contraseña sin escribirla en la línea de comandos. Usar cliente MySQL 8 compatible:

```bash
# LOCAL: carpeta privada fuera del repositorio
umask 077
mkdir -p "$HOME/Backups/spanel-transfer"
mysqldump --host=127.0.0.1 --port=3306 --user=USUARIO_LOCAL -p \
  --single-transaction --quick --routines --triggers --events \
  --no-tablespaces --set-gtid-purged=OFF --hex-blob \
  BASE_LOCAL > "$HOME/Backups/spanel-transfer/database.sql"
# Si la conexión local es exclusivamente por socket, usar --socket=RUTA_SOCKET_LOCAL.
# No continuar si mysqldump termina con error.
tar -czf "$HOME/Backups/spanel-transfer/files.tar.gz" storage/app public/images
shasum -a 256 "$HOME/Backups/spanel-transfer/database.sql" "$HOME/Backups/spanel-transfer/files.tar.gz"
```

Transferir por SFTP/SSH a `/home/tanggosoftware/backups/initial/`, fuera de public y del repo, con permisos privados; comparar hashes con `sha256sum` en Rocky. No enviar dumps por Git ni dejar descargas públicas.

```bash
# SERVIDOR: solo contra la base NUEVA, vacía, ya verificada
mysql --host=127.0.0.1 --port=3306 --user=USUARIO_NUEVO -p BASE_NUEVA \
  < /home/tanggosoftware/backups/initial/database.sql
cd /home/tanggosoftware/repos/schedinedinotifica
tar -xzf /home/tanggosoftware/backups/initial/files.tar.gz
```

No importar sobre una base con datos. Si falla la restauración, detenerse y repetir en otra base vacía; no seguir con un import parcial. Revisar permisos/DEFINER de rutinas y eventos si los hubiera. Comparar recuentos por tabla con el origen, especialmente usuarios, propietarios, estructuras, clientes, schedina/componenti, CRM, tablas GEO y exportaciones; preservar IDs y relaciones. Buscar URLs absolutas `.test` o rutas `/Users/` dentro de los datos restaurados (logos/enlaces/configuración), sin sustituciones globales automáticas.

Transferir **todo `storage/app/`**: imports, exports, recibos Questura/ISTAT, documentos, `public/uploads`, `public/geo_comuni` y cualquier JSON GEO allí guardado. Los datasets GEO de respaldo en `reference/libreria/geo` están versionados; `NationService` también contempla JSON en storage, por lo que cualquier copia local debe conservarse. No ejecutar reimportaciones de GEO sobre la base restaurada.

Transferir `public/images/` por sus avatares mutables. No copiar vendor, node_modules, caches, sesiones, logs ni el symlink macOS. Respaldar `.env`/APP_KEY separadamente en un gestor seguro. La base contiene credenciales de integraciones y datos personales: tratar el dump como secreto.

## 5. Instalar y compilar

En el servidor o en un entorno de build compatible, ejecutar secuencialmente y detenerse al primer error:

```bash
cd /home/tanggosoftware/repos/schedinedinotifica
composer validate --strict
composer install --no-dev --optimize-autoloader --no-interaction
composer check-platform-reqs --no-dev
php artisan down
php artisan config:clear
npm ci
npm run build
# El build incluye verify-architecture y debe terminar con código 0.
# No usar npm ci --omit=dev: faltaría Vite.
php artisan storage:link
php artisan migrate:status
```

Si Node no existe en SPanel, generar `public/build/` desde el mismo SHA y lock en el entorno de build y transferir ese directorio completo (incluido `.vite/manifest.json`); nunca el servidor de desarrollo Vite. Las plantillas también usan nombres fijos `build/css`, `build/libs` y `build/js`: no basta con subir solo el manifest. El build falla ahora si no puede copiar un asset o paquete necesario. Se añadió la copia de resources/js/pages, se corrigió la ruta particles.js del reset y se retiró una referencia a eva-icon.init.js que no existe en el proyecto. No se cambió la lógica de autenticación.

### Permisos

El propietario debe ser `tanggosoftware`; PHP debe ejecutarse como esa cuenta o con un grupo/ACL acordado. No usar 777. Si PHP corre como la cuenta:

```bash
find storage bootstrap/cache -type d -exec chmod 750 {} \;
find storage bootstrap/cache -type f -exec chmod 640 {} \;
# Los archivos servidos directamente necesitan lectura/traversal para el servidor web:
find storage/app/public public/images -type d -exec chmod 755 {} \;
find storage/app/public public/images -type f -exec chmod 644 {} \;
chmod 600 .env
```

`public/images` también debe ser escribible por PHP (subida de avatares). Si PHP usa otro usuario, conceder escritura por grupo/ACL solo en storage, bootstrap/cache y public/images, y lectura de .env al proceso PHP. No dar escritura general al código. Verificar acceso de Apache/LiteSpeed al recorrido del symlink `public/storage` y a sus directorios padre. En Rocky con SELinux enforcing, el administrador debe configurar contextos persistentes/ACL adecuados y conectividad a DB/SMTP; no desactivar SELinux como solución. Validar una carga/descarga real.

### Migraciones y activación

En este traslado se espera **cero pendientes** tras importar el dump. Si aparecen pendientes, detenerse y reconciliar SHA/dump/tabla migrations, sin ejecutar a ciegas.

Para una actualización futura con migraciones aprobadas, probarlas sobre una copia restaurada y guardar backup primero. Solo entonces:

```bash
php artisan migrate --force
php artisan optimize
php artisan schedule:list
php artisan up
```

En el primer traslado, `migrate --force` será un no-op si el historial importado es el auditado. No ejecutarlo si hay migraciones pendientes sin revisar. `optimize` genera cachés de config, rutas, eventos y vistas; no transferir las de la máquina local.

## 6. Scheduler, queue y logs

Actualmente no hay tareas programadas en el Kernel; `schedule:list` lo confirma. Se puede dejar preparado un cron cada minuto en la cuenta SPanel. Sustituir `/RUTA/ABSOLUTA/php83` por el binario 8.3 realmente verificado:

```cron
* * * * * cd /home/tanggosoftware/repos/schedinedinotifica && /RUTA/ABSOLUTA/php83 artisan schedule:run >> /home/tanggosoftware/repos/schedinedinotifica/storage/logs/scheduler.log 2>&1
```

Con `QUEUE_CONNECTION=sync` **no hace falta worker**. No se localizaron jobs despachados que exijan uno; la notificación de reset importa ShouldQueue pero no lo implementa. No cambiar a database sin tabla `jobs` (no hay migración que la cree), ni a Redis sin servicio/extensión.

Si más adelante se habilita una cola, un administrador deberá configurar Supervisor/systemd con usuario tanggosoftware, WorkingDirectory del repo, reinicio automático y:

```bash
/RUTA/ABSOLUTA/php83 artisan queue:work --sleep=3 --tries=3 --timeout=60
# Tras cada despliegue, únicamente cuando haya workers:
php artisan queue:restart
```

Mantener timeout menor que retry_after (90 actual). Rotar logs de worker/cron/PHP. Supervisar `storage/logs/laravel-*.log`, HTTP 5xx, espacio en disco y backups; no publicar logs bajo public.

## 7. Verificación antes de abrir tráfico

- Certificado y redirección HTTP→HTTPS, dominio y document root correctos; sin contenido mixto.
- `/`, `/moduli`, `/prezzi`, `/contatti`, `/login` y assets build/site-assets devuelven lo esperado.
- Login/logout/reset SMTP; cookies Secure/HttpOnly, CSRF y sesiones persistentes.
- Acceso por roles y aislamiento entre estructuras; clientes/schedine/estadística y tablas GEO con los datos importados.
- Avatares, logos, storage:link, imports/exports y descargas históricas funcionan.
- `/qa/...` cerrado con QA_ENABLED=false. Registro público sigue habilitado por Auth::routes(); decidir su conveniencia antes de abrir.
- El contacto público está exento de CSRF y no declara throttling en esa ruta; logout GET está habilitado. Se preservó el comportamiento: revisión de seguridad pendiente.
- Questura/ISTAT: comprobar credenciales/endpoints y simulación; no enviar notificaciones reales como prueba automática.
- `.env`, `.git`, dumps y logs no son accesibles por HTTP. Comprobar también que uploads no ejecutan PHP.
- Si `optimize` o cualquier validación falla, mantener mantenimiento y corregir antes de `up`.

## 8. Actualizaciones de la producción existente

Usar exclusivamente el procedimiento `deploy.sh` descrito al principio de esta guía. El antiguo bloque de pull + migración automática queda sustituido por preflight, SHA aprobado y autorización explícita de migraciones.

## 9. Rollback básico (recuperación manual revisada)

Conservar por despliegue SHA anterior, backup consistente, archivos mutables y `.env`/APP_KEY en backup privado. Preservar también estado fallido y escrituras posteriores. No ejecutar directamente el antiguo rollback basado en `artisan down/up`: con vendor roto no protege la ventana crítica y puede reemplazar el guard independiente.

1. Restringir tráfico en el vhost si el 503 no se ha verificado. Conservar el guard `storage/framework/maintenance.php`; no reabrir hasta validar recuperación. Detener y comprobar procesos descendientes/externos.
2. Revisar `git status --short` y el SHA anterior. Recuperar código y dependencias de una versión conocida bajo mantenimiento, con un procedimiento específico aprobado. El deploy normal exige fast-forward y no hace rollback ni fuerza un checkout sucio.
3. Si el esquema sigue compatible, conservar la DB actual. Si una migración dejó cambios parciales, restaurar el snapshot en una base nueva de recuperación y reconciliar datos/archivos. No ejecutar `migrate:rollback` automáticamente ni sobrescribir la base fallida.
4. Reconstruir assets y caches individuales, comprobar secretos, `public/storage`, permisos y HTTPS. Solo retirar el mantenimiento después de aprobar esas comprobaciones. Cualquier cambio de credenciales/base requiere una operación manual separada; este script nunca modifica `.env` ni APP_KEY.

No usar `git reset --hard`, `git clean` o borrado de datos para recuperar una instalación.

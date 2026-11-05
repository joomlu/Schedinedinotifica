# Auditoría adversarial del deployment — 2026-09-30

Alcance: `deploy.sh`, wrapper antiguo, supervisor Python, ejecutor Artisan, guard 503 y pruebas. Revisión del flujo completo de entrada/salida, errores, señales, operaciones Git y destinos de escritura. No se ha ejecutado deployment, merge ni ninguna acción sobre SPanel. No se modificó/exportó la DB ni se cambió APP_KEY.

El entry point sigue siendo Bash con `set -euo pipefail`. Las comprobaciones complejas se ejecutan en Python 3.9+ sin pipes de shell ni sustituciones de procesos; Artisan pasa por un ejecutor PHP que valida configuración antes de los providers y antes/después del comando. No se cambió lógica funcional Laravel ni los locks de dependencias.

## Los ocho hallazgos originales

| # | Riesgo original | Corrección y evidencia |
|---|---|---|
| 1 | Rutas configurables podían limpiar datos, especialmente VIEW_COMPILED_PATH | Lista exacta de destinos para cache de config/rutas/eventos/providers, vistas, sesiones, logs, cache-file y enlaces/discos. Rechaza overrides de entorno, symlinks/hardlinks, discos/cache locales adicionales y configuración sin cachear peligrosa oculta tras cache válido. Pruebas con Laravel real preservan los archivos centinela de storage/app y public/images. |
| 2 | Renombrados ocultaban origen/destino persistente | Git diff NUL con `--no-renames`; examina cada eliminación y adición. Repositorios temporales reproducen movimientos en ambos sentidos para storage/app/public/images, eliminación persistente y migraciones antiguas alteradas. |
| 3 | optimize podía devolver éxito pese a fallar subtareas | Se eliminó el agregado. Ocho comandos individuales, exit code y postcondiciones de archivos. Pruebas reales de éxito y fallos de escritura/limpieza con runtime sin permiso de escritura; inyección de error en cada posición de la secuencia. |
| 4 | Mantenimiento requería vendor funcional | Guard PHP independiente instalado antes del marcador down, con index.php fijado por hash y verificación HTTPS antes del merge. Pruebas CLI sin vendor y con autoload sintácticamente roto; prueba HTTP real de loopback verifica 503, Retry-After, no-store y marcador. Cookies malformadas también reciben 503. |
| 5 | Mensaje Maintenance retained sin demostrar seguridad | Solo anuncia 503 verificado tras validar ambos archivos y la respuesta del origen. Recuperación fallida produce CRITICAL MAINTENANCE_UNVERIFIED; fallo de recogida de hijos nunca promete estado seguro. Probados errores antes del mantenimiento, después de reabrir, en la escritura parcial y durante limpieza final. |
| 6 | Errores Git/find/sustituciones podían parecer éxito | Cada proceso comprueba returncode; enumeración de archivos propaga errores; JSON/Git malformados fallan. Bash solo contiene una sustitución comprobada para resolver su directorio. Pruebas de fallos en status, índice, diff, tree, merge-base, walk, timeout, HTTPS y assets. |
| 7 | Hijos podían heredar y retener el lock | flock con FD no heredable y close_fds, cada comando en grupo/sesión separados, limpieza de descendientes y subreaper Linux. SIGINT/SIGTERM/SIGHUP comprobados localmente con hijo real y readquisición del lock. Tres escenarios Linux adicionales están preparados, pero **no ejecutados en Rocky**. |
| 8 | storage:link podía terminar sin enlace | Postcondición obligatoria: symlink existente, destino absoluto directo exacto y resolución física a storage/app/public. Directorio real, enlace ausente/ajeno/indirecto o roto abortan. Prueba con storage:link de Laravel real y fixtures adversariales. |

Los ocho defectos tienen corrección implementada. La aceptación específica de Linux/Rocky sigue pendiente; no debe confundirse una prueba preparada con una ejecutada.

## Integridad de secretos y datos

- `.env` se lee/hash antes de ejecutar código del proyecto. Exige APP_KEY única, literal y no vacía; compara hash del archivo y del valor sin imprimir el secreto.
- Se comprueba antes/después de procesos, incluso si fallan, y durante la salida/limpieza. Alteración, desaparición, clave vacía/cambiada y symlink/hardlink producen fallo crítico. No hay restauración automática que pudiera ocultar el incidente.
- Las caches nuevas creadas por el ejecutor PHP usan umask 0077; se comprueba que config.php queda en modo 0600.
- El ejecutor PHP compara APP_KEY y configuración DB activa/sin cachear con `.env`, incluyendo la configuración recién cacheada. Se comprobó rechazo de una APP_KEY divergente en config sin cambiar el archivo `.env`.
- Los tests usan únicamente APP_KEY y credenciales ficticias en temporales. Los comandos de integración no usan migraciones ni consultas DB; la conexión fixture apunta a un puerto local sin servicio y una base ficticia.
- Git bloquea cambios persistentes; las limpiezas explícitas quedan limitadas a manifests/cache de runtime, node_modules y temporales privados. No limpia storage/app ni public/images, ni cambia sus permisos. public/storage solo se crea si falta y se verifica estrictamente.
- Composer usa `--no-scripts --no-plugins`; npm desactiva hooks automáticos. El build explícito revisado se conserva y sus archivos de control están fijados por hash. No hay reset destructivo, clean, stash automático, force checkout, chmod 777 ni npm audit fix en el código de despliegue. El chmod 777 que aparece en un test es una fixture que debe rechazarse.

## Cachés individuales con Laravel 11 / PHP 8.3

| Comando | Ejecución normal | Fallo real de limpieza/escritura inducido |
|---|---|---|
| config:clear | Exit 0 | Exit distinto de 0 |
| route:clear | Exit 0 | Exit distinto de 0 |
| view:clear | Exit 0 | Exit distinto de 0; datos persistentes intactos |
| event:clear | Exit 0 | Exit distinto de 0 |
| config:cache | Exit 0 | Exit distinto de 0 |
| route:cache | Exit 0 | Exit distinto de 0 |
| view:cache | Exit 0 | Exit distinto de 0; también componente Blade inválido |
| event:cache | Exit 0 | Exit distinto de 0 |

La secuencia se detiene en el primer fallo; no reabre la aplicación. Los fallos de Composer/npm/migraciones se prueban por inyección en el controlador y por subprocess reales con error/timeout, sin instalar dependencias o migrar una DB real.

## Señales y prueba segura pendiente para Rocky Linux 10

**Aceptación aislada de Rocky Linux 10 pendiente:** el propietario confirmó que Bubblewrap no está instalado en el VPS Rocky Linux 10.2 y se decidió no instalar paquetes del sistema en ese VPS para esta validación. No se ejecutarán estas pruebas allí sin ese requisito ni se reducirá el aislamiento. La aceptación requiere un entorno adecuado con Bubblewrap disponible y seis pruebas ejecutadas correctamente; las pruebas locales no la sustituyen.

Localmente se han enviado SIGINT, SIGTERM y SIGHUP al supervisor con un hijo real en ejecución: salida 130, 143 y 129 respectivamente, hijo terminado, lock readquirible, `.env` intacto y guard presente. La comprobación HTTPS de recuperación está simulada en estas pruebas de señales; la respuesta PHP 503 se comprueba separadamente con HTTP real en loopback.

Actualización del arnés para un clon aislado en el mismo host: ver `docs/ROCKY_PROCESS_ISOLATION.md`. El rechazo anterior basado en la existencia de producción se sustituye por aislamiento obligatorio de filesystem, PID y red mediante Bubblewrap. El launcher no consulta la ruta productiva. Solo permite el clon físico `/home/tanggosoftware/deploy-rocky-test`, crea allí todos los temporales y ejecuta seis tests de ProcessTests, sin EnvironmentTests ni discovery general.

`--help` ahora muestra ayuda y termina sin comprobaciones del host ni ejecución. Las pruebas requieren `--run-process-tests`. Si faltan Bubblewrap, sus opciones o los namespaces, se aborta sin fallback fuera del sandbox. Esta modificación todavía no se ha validado ejecutando Bubblewrap en Rocky; los resultados de la suite anterior que figuran abajo son históricos y no certifican este nuevo aislamiento.

## Ejecución completa local

Entorno: macOS, Python 3.9.6, PHP 8.3.33 y vendor Laravel 11 local. La suite usa repositorios temporales y servidor PHP solo en 127.0.0.1; no usa la base de datos ni `.env` del proyecto. Necesita permiso para abrir un puerto loopback.

```bash
bash -n deploy.sh scripts/deploy-production.sh
php -l scripts/deployment/artisan.php
php -l scripts/deployment/maintenance.php
python3 -B -m unittest discover -s tests/Deployment -v
```

Resultado final de la suite completa: **66 tests descubiertos: 63 correctos, 3 omitidos por requerir Linux; 0 fallos y 0 errores**. Los tres omitidos son daemon separado, SIGKILL con hijo superviviente y doble fork/setsid que ignora SIGTERM. No se suman ejecuciones parciales al total. Sintaxis Bash/PHP/Python y nueve drivers de procesos anidados: correctas. El hash del `.env` real, composer.lock, package-lock.json y public/index.php permaneció idéntico durante la verificación final; `.env` no está versionado.

## Riesgos residuales y decisión

- Falta ejecutar los tres escenarios Linux en Rocky 10. No se ha probado deployment real con Composer/Node 20/npm 10/MariaDB/SELinux/PHP-FPM del servidor.
- No es aislamiento de seguridad: PHP/JavaScript y dependencias autorizadas corren con los permisos del usuario. Los hashes detectan alteraciones persistentes; no impiden código malicioso ni prueban ausencia de cambios transitorios restaurados antes de comprobar. El código del SHA y la configuración del host deben ser confiables.
- El lock es cooperativo; no bloquea ediciones manuales, peticiones anteriores ni procesos ajenos. Drenar tráfico/trabajos externos y evitar modificaciones concurrentes. No garantiza seguridad ante disco roto/lleno, permisos revocados, SIGKILL, corte de energía o procesos bloqueados en el kernel; estos casos requieren intervención.
- En fallo posterior a mantenimiento, puede quedar checkout/vendor/build parcial bajo 503. No hay rollback automático. Migraciones aprobadas pueden confirmar DDL parcial: backup y recuperación manual probada siguen siendo imprescindibles.
- OPcache sin revalidación, PHP-FPM/CLI diferentes, certificados, vhost, SELinux y funcionalidades autenticadas/SMTP/SOAP no quedan certificados por pruebas CLI/loopback. El script exige la comprobación HTTPS del origen, pero no reemplaza la aceptación funcional completa.
- Python 3.9+ es una nueva dependencia operativa. Los contratos de index/build y las rutas son deliberadamente estrictos; un cambio legítimo requiere revisar el runner y distribuir el bundle completo, no desactivar protecciones.
- Dependencias y riesgos funcionales anteriores no se actualizan en esta tarea; ver `docs/AUDIT_SPANEL_DEPENDENCIES.md`.

Conclusión: correcciones y pruebas locales completadas según el resultado registrado; **no se autoriza ni se certifica todavía un deployment real**. Completar aceptación Rocky y verificación del entorno antes de proponer ejecución en producción.

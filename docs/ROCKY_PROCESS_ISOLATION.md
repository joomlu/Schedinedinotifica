# Pruebas de procesos Rocky 10 en un host que también aloja producción

Implementación conservada para validación futura; no se ha ejecutado en SPanel. Los scripts de deployment y el código Laravel no cambian.

El arnés anterior ejecutaba pruebas incluso con `--help` y rechazaba el host completo si existía producción. Se sustituye esa condición por un aislamiento más fuerte: el proceso de pruebas no recibe la instalación productiva en su filesystem ni ve sus procesos o red.

**Aceptación aislada de Rocky Linux 10 pendiente:** el propietario confirmó que Bubblewrap no está instalado en el VPS Rocky Linux 10.2 y se decidió no instalar paquetes del sistema en ese VPS para esta validación. No se ejecutarán estas pruebas allí sin ese requisito ni se reducirá el aislamiento. La aceptación requiere un entorno adecuado con Bubblewrap disponible y seis pruebas ejecutadas correctamente; las pruebas locales no la sustituyen.

## Comandos para una futura validación con los requisitos disponibles

```bash
cd /home/tanggosoftware/deploy-rocky-test
python3 -B tests/Deployment/check_rocky10.py --help
# La ayuda nunca ejecuta pruebas ni consulta producción.

python3 -B tests/Deployment/check_rocky10.py --run-process-tests
```

Sin opciones solo muestra ayuda. No hay flag para desactivar el aislamiento ni cambiar arbitrariamente el directorio permitido.

Requiere Rocky 10.x, Python del sistema bajo `/usr` (3.9+; el entorno comunicado tiene 3.12.14), usuario no root y `/usr/bin/bwrap`. Bubblewrap debe soportar las opciones solicitadas y poder crear namespaces de usuario, PID, montajes y red; SELinux/política del host pueden impedirlo. En ese caso el arnés devuelve error antes de ejecutar las pruebas. No instala paquetes, no cambia servicios/sysctls del host ni propone un fallback inseguro. No relajar las opciones para obtener un resultado verde.

## Qué se ejecuta

Lista fija de seis métodos de `ProcessTests`, sin discovery general:

1. Exclusión y liberación del lock.
2. Descriptor del lock no heredado por el hijo.
3. SIGINT/SIGTERM/SIGHUP con hijo real y readquisición del lock.
4. Recogida de daemon separado mediante setsid/subreaper.
5. Doble fork + setsid + SIGTERM ignorado.
6. SIGKILL al supervisor con un hijo superviviente que no retiene el lock; el test recoge después al hijo.

No ejecuta EnvironmentTests, Artisan, PHP, Git, Composer, npm, HTTP, consultas DB, migraciones ni el entry point del deployment. Los fixtures construyen una aplicación ficticia, con `.env` y APP_KEY deliberadamente ficticios, para ejercitar los métodos de limpieza/mantenimiento del supervisor. La recuperación HTTPS está simulada: estas seis pruebas no certifican HTTPS ni la aplicación real.

## Guardas antes de ejecutar código de prueba

- CWD y ruta del launcher deben corresponder exactamente al clon permitido. La comparación es léxica y ocurre antes de inspeccionar rutas incorrectas.
- Se rechazan componentes symlink, archivos de entrada hardlinked, permisos de escritura para grupo/otros en el clon y montajes/bind mounts dentro del clon o ancestros que representen subárboles remapeados. Se lee `/proc/self/mountinfo`; no se compara contra la instalación productiva ni se consulta su existencia.
- Los temporales se crean con nombres aleatorios `.rocky-process-*` y permisos 0700 directamente bajo el clon. HOME, TMPDIR, fixtures, locks, cookies ficticias y temporales de recuperación quedan allí; no se usa el TMPDIR heredado.
- Solo se copian los archivos enumerados de supervisor, template 503, index de referencia, suite y guardas. Nunca se copia `.env`, vendor, DB, `.git`, node_modules o datos reales.

## Frontera de aislamiento del sistema operativo

Bubblewrap construye un filesystem nuevo. Monta `/usr`, bibliotecas del sistema y, si existe, el cache del cargador dinámico **solo lectura**. No monta el `/home`, `/etc`, `/proc` ni `/` del host. El único bind escribible del host es el temporal privado recién creado; el código copiado se vuelve a montar solo lectura y la raíz del sandbox queda solo lectura. `/proc` pertenece al nuevo namespace de PID; `/dev` es mínimo, creado por Bubblewrap.

No se monta `/home/tanggosoftware/repos/schedinedinotifica`, ni se la prueba con exists/stat/open. Por eso su presencia o ausencia no condiciona la prueba. El namespace de PID evita enviar señales a los procesos del host, incluso usando números de PID coincidentes. La red está aislada; no se hereda una red del host. Se eliminan capacidades y se bloquea crear nuevos namespaces de usuario dentro del sandbox. El runner compara sus namespaces PID/mount/net/user con los del launcher y aborta si alguno no cambió.

La entrada es `/dev/null`; los hijos reciben pipes privados para la salida y ningún descriptor abierto del proyecto o del host. `--die-with-parent` y el namespace de PID limitan la supervivencia de procesos si termina el launcher; hay timeout global de 180 segundos y terminación de Bubblewrap al fallar. El host sigue compartiendo kernel/CPU/memoria: esto no elimina riesgos de errores del kernel ni equivale a una VM.

Diseño basado en las [opciones oficiales de Bubblewrap](https://github.com/containers/bubblewrap/blob/main/bwrap.xml). No debe sustituirse por comprobaciones de rutas sin sandbox.

## Aborto explícito ante intentos prohibidos

Un guard Python se carga al iniciar cada intérprete, incluidos hijos y nietos, desde código montado solo lectura. Bloquea operaciones Python sobre `/home/tanggosoftware/repos`, incluidos open, stat/lstat/access/readlink, listados, escritura, borrado, renombrado y escapes relativos. También bloquea symlinks, red, comandos ajenos a Python y opciones/entornos de hijos que omitan el guard.

Una infracción escribe un marcador privado y termina el proceso con código 98 usando `os._exit`; no puede ser absorbida por un `except` de la prueba. El launcher rechaza toda la ejecución si aparece el marcador, incluso si el padre ignoró el exit code del hijo. La falta del guard produce salida 97. Los hooks facilitan detectar errores Python; **el aislamiento real frente a accesos nativos lo impone el namespace de filesystem**, donde producción no está disponible. No se presenta el hook Python como una barrera frente a código nativo malicioso.

## Validación y límites

Las pruebas locales de política reproducen acceso prohibido, metadatos, renombrado/borrado, symlinks/hardlinks, escapes, herencia del guard, fallo ignorado por el padre, selección fija y construcción de mounts/namespaces. Verifican también que los tests portables de procesos siguen funcionando con el guard.

Resultado local: 21 pruebas nuevas de aislamiento correctas. Suite completa: 87 tests, 84 correctos y 3 omitidos por requerir Linux; cero fallos/errores. `--help` y ejecución sin opciones devolvieron 0 sin ejecutar pruebas; sintaxis Python y `git diff --check` correctos.

Bubblewrap y los tres escenarios específicos de Linux no se han ejecutado desde macOS. La aceptación en Rocky requiere seis tests ejecutados, cero skips/fallos/errores y salida final 0; una negativa por sandbox no equivale a aceptación. No basta con que `--help` funcione.

No se debe modificar concurrentemente el clon durante la preparación. Si el launcher muere abruptamente puede quedar un temporal privado con fixtures; no contiene secretos reales. No se ha realizado merge, deployment ni operación sobre SPanel como parte de esta modificación.

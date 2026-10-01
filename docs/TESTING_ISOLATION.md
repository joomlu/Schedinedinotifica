# Estado de aislamiento de testing — P0

## Estado actual: ejecución funcional bloqueada

No existe todavía infraestructura desechable autorizada y acreditada. Por ello
PHPUnit y Playwright fallan cerrados con `TEST_ISOLATION_REQUIRED`, antes de
arrancar Laravel o crear un navegador. Este estado NO es un arnés funcional
completo ni prueba que una futura conexión MariaDB esté aislada.

- `phpunit.xml` carga `tests/bootstrap.php` antes del autoload de Composer.
- `CreatesApplication` repite la guarda antes del bootstrap, incluso si se omite
  el bootstrap de PHPUnit.
- `playwright.config.js` bloquea el arranque; cada spec existente importa una
  fixture que también bloquea ejecuciones que omitan esa configuración.
- No hay variables de autorización, archivos marcador o flags para saltarlo.
- Se retiró la URL fija de Herd de los cuatro specs.
- Sin proceso HTTP/navegador no se realizan peticiones ni siguen redirects.
  Esto NO es una implementación probada de filtrado de redirects con un
  navegador funcionando: esa validación sigue pendiente.

Solo las guardas pueden verificarse ahora:

```bash
python3 -B tests/Isolation/test_guards.py
```

Ese script usa subprocesos PHP/Node para comprobar rechazo. No arranca Laravel,
PHPUnit, Playwright, HTTP, Docker o MariaDB; no abre ninguna base de datos.
Los valores de conexión adversos son datos de entrada, nunca destinos usados.

## Requisitos antes de habilitar ejecución funcional

Requieren autorización separada. No eliminar la guarda para probar:

1. Instancia MariaDB desechable exclusiva, con credenciales efímeras, sin montar
   datos reales ni reutilizar servidores/sockets existentes. El nombre `_test`
   no acredita aislamiento. Preferir instancia aislada a adaptar migraciones
   históricas MySQL a SQLite.
2. Launcher que controle el ciclo de vida y registre identidad, endpoint y
   recursos creados. Rechazar DATABASE_URL/DB_URL, sockets y overrides heredados.
3. Validación de configuración ANTES de providers, conexiones y RefreshDatabase;
   rechazo de conexiones secundarias, read/write y reconexiones fuera de esa
   instancia. No cargar el `.env` ni las caches de desarrollo.
4. Checkout/runtime desechable: storage, public/images, uploads, vistas, logs,
   sesiones, caches y temporales separados; mail e integraciones sin envíos reales.
5. Servidor HTTP propio, no reutilizado, identidad efímera comprobada antes de
   navegación. Restringir navegación, peticiones y redirects al origen autorizado,
   bloquear service workers y servicios externos. Probar estas reglas en vivo.
6. Probar desvíos y fallos antes de habilitar las regresiones DB. No aceptar una
   variable de entorno, un prefijo de nombre o un marcador como única garantía.
7. Solo entonces preparar el esquema y fixtures de esa instancia y ejecutar tests.
   Las migraciones existentes pueden tener incompatibilidades de instalación desde
   cero: abortar sin repararlas silenciosamente ni recurrir a datos locales.

Las regresiones nuevas son `StrutturaAuthorizationTest` y sus fixtures sintéticos.
No se han ejecutado. Los tests antiguos con RefreshDatabase, seeders y usuario ID
11 permanecen bloqueados; necesitan preparación de fixtures antes de habilitar
la suite completa. No se afirma que esos tests sean ya autosuficientes.

## Autorización de estructuras

Las tres rutas de estructura actual exigen `RequireAuthorizedStruttura`.
`StrutturaAccess` aplica pertenencia y distingue acceso operativo (incluye la
excepción legacy del admin) de selector (no la incluye). Propietario sin ID o
sin estructuras no obtiene ninguna estructura. Una selección ajena, inválida o
conflictiva se rechaza; no se convierte en otra modificación silenciosa.
El contexto estático se reinicia al entrar/salir del middleware.
No se alteran el CRUD administrativo, el scope global de otros modelos, los datos
existentes ni el esquema.

# Tassa di soggiorno: verificación integral del 24 de octubre de 2026

Estado: **PARCIAL — BASELINE IN AUDIT**. Las correcciones están publicadas y el circuito sintético final está comprobado. Falta acreditar la aceptación externa de StayTour y completar datos operativos reales; no se certifica el cierre fiscal integral.

Versión realmente online: **`dda6b24ed40a3257ad5226ec3459a52f149b95fa`**, Laravel 12.69.3, PHP 8.3.36, 159 migraciones / 66 tablas. Git limpio, FPM activo, 243 archivos de código/protegidos y nueve sondas públicas conformes. [Recibo final agregado](evidenze/tassa-verifica-2026-10-24/rilascio.json), [calificación y digest](evidenze/tassa-verifica-2026-10-24/verifica.json), [fuentes oficiales](evidenze/tassa-verifica-2026-10-24/fonti.json).

## Estado reconstruido y correcciones

La producción inicial era `4a9c0c55ce9319bef89dea97b9a1885c1ace5e66`, atestada con Git/FPM, 1.710 digest y nueve sondas. Main `2fd17c8076b6631cc61852aa0bd4f0439706548a` incluía anagrafiche unificadas todavía sin publicar. Se trabajó en `codex/tassa-verifica-2026-10-24`, en un checkout separado. El checkout original `6a652b72d9db91c1e25c855ed34a67054eb3a8d0`, su rama y sus cambios preexistentes se conservaron.

Se reprodujeron y corrigieron tres defectos:

1. **450 ausente del catálogo automático sin registro legacy.** Incorporado al catálogo compartido como familiar del gestor anagráficamente conviviente, con descripción canónica e indicación de nota requerida, sin insertar copias en DB.
2. **Nacimiento imposible normalizado silenciosamente.** `2008-02-31` se convertía en marzo. El parser compara exactamente los formatos admitidos y rechaza fechas imposibles o relativas; el cálculo no presume un adulto cuando necesita un nacimiento válido.
3. **HTTP 500 en la ficha de la estructura con Comune/CAP completos.** La QA publicada detectó una consulta a `geo_comuni_cap.cap`, columna inexistente. La verificación usa ahora la relación existente hacia `geo_cap`, conservando el vínculo al Comune. Regresión positiva, CAP de otro Comune, Admin/Superadmin y aislamiento comprobados; cero DDL.

[PR18](https://github.com/joomlu/Schedinedinotifica/pull/18) publicó las primeras correcciones en `ca1ead9cebb53e2e92da0bf2e34df461b4cf8a65`. La comprobación posterior de la ficha detectó el tercer defecto, corregido en `b61e429766490f3f4ed0dc88b9a30e4ad7f3bc1b` e integrado por [PR19](https://github.com/joomlu/Schedinedinotifica/pull/19) en el SHA online final indicado arriba. El árbol del merge es idéntico al calificado; 46 fuentes de la auditoría coinciden con las pruebas. El consuntivo documental posterior es distinto de la versión aplicativa servida.

Las correcciones recientes de categorías no certificadas, contexto fiscal Web, perfiles por período, versionado y datos maestros compartidos se comprobaron nuevamente mediante las suites actuales, sin dar por resueltos los pendientes del informe anterior. No se cambiaron tarifas, temporada, máximo de noches, criterio de cumpleaños ni datos reales.

## Matriz de verificación

| Área | Prueba realizada | Resultado | Entorno | Evidencia |
|---|---|---|---|---|
| Fuente única | Clasificación, Comune, contactos y misma ficha para Superadmin, Admin, titular, estructura y reception | PASS; modificaciones compartidas, permisos y CAP válido del catálogo | VM + producción | R10/R11; 4 escenarios anagráficos finales |
| Configuración efectiva | Datos canónicos más perfil común; ausencia de copias persistidas en GET, legacy discordante preservado | PASS; clasificación indeterminada bloquea outputs fiscales | VM + lectura productiva | ConfigurazioneAutomatica/Completeness |
| Tarifas y vigencia | Hoteles 1–5 estrellas, junio–septiembre, máximo seis noches y años/categorías no certificados | PASS dentro del alcance Bellaria 2026 | VM | Suites TassaBellaria/PeriodoComunale |
| Fechas y edades | Límites, cumpleaños durante estancia, reloj histórico, cambio de mes/año y perfiles sintéticos entre años | PASS; no se extrapola certificación de 2026 a otros años | VM + caso online | EtaCsv/Anteprima/Versionamento |
| Nacimiento | Ausente, imposible, relativa, futura; titular y componente | PASS; cálculo o salida fiscal rechazados cuando el dato es indispensable | VM + 422 online | R1/R10; QA Tassa |
| Exenciones y 450 | Catálogo sin legacy, formulario, huésped, CSV, resumen, impresión y snapshot | PASS: 10 noches = 6 exentas + 4 de exceso; €0 | VM + producción | VerificaIntegrale; impresión 450 |
| Circuito completo | Individual/familia/grupo, revisión y conversión, fechas y huéspedes modificados | PASS en escenarios probados; ningún envío | VM + producción | 29 escenarios Web + 3 Tassa finales |
| Conciliación | Huésped, resumen, CSV e impresión; motivos, máximo y redondeos de los casos ensayados | PASS: 20 = 10 exentas + 8 de exceso + 2 imponibles; €3 | VM + producción | QA Tassa; PDF |
| Histórico y rectificación | Nueva versión tras cambiar condiciones; documento anterior y configuración posterior | PASS; snapshot cifrado e inmutable | VM + producción | Versionamento/Completeness; QA Tassa |
| Anulación y duplicados | Bozze/Web/Arrivi excluidos; Cestino, restauración y conversión idempotente | PASS en circuitos probados; consolidado anterior conservado | VM + producción | R7/R10; circuito Web |
| Formularios y permisos | Altas, cambios, consultas, validaciones y denegaciones entre estructuras | PASS; 403/404/422 y ninguna escritura ajena | VM + producción | R10/R11; QA final |
| Interfaz y documentos | 1440/768/390, fechas, botones, modelos, HTML, impresión con nombres largos y grupo | PASS en las vistas comprobadas, sin overflow ni errores JS | VM + producción | Capturas; 13 PDF offline y 4 PDF online |
| Questura e ISTAT | Coherencia de origen y regresiones de lectura/seguridad, flujos independientes | PASS; no se realizaron transmisiones oficiales | VM + producción | Suites específicas; lectura QA |
| Publicación y conservación | C5, recuperación, SHA, DB, persistentes y limpieza | PASS; cero DDL, HMAC de 66 tablas idénticos después de cada grupo | VM + producción + Mac/Extra | Recibo final |
| Datos reales | Lectura agregada, sin cambios de valores | 2 clasificaciones canónicas activas y 1 nacimiento efectivo faltantes; 0 exports reales consolidados | Producción READ ONLY | verifica.json |
| StayTour | Aceptación por sesión de gestor o validador oficial sin efectos fiscales | **NO VERIFICADA**: usuario confirma que no dispone de acceso | Externo | Respuesta del 24 de octubre |

## Conciliación y decisiones fiscales

Caso de cumpleaños: dos huéspedes / 20 noches. Son **10 exentas + 8 más allá del máximo + 2 imponibles a €1,50 = €3**. Los valores coinciden entre cálculo, resumen, CSV e impresión. “Cantidad 2” del motivo 400 cuenta personas con alguna noche exenta; “Esenti 1” cuenta la persona enteramente exenta. El huésped que cumple 18 años tiene una parte exenta y otra imponible: estos contadores no miden lo mismo ni duplican noches.

Código 450: **10 noches = 6 exentas + 4 con código técnico 777; €0**. No se cuentan las cuatro noches de exceso también como noches del motivo 450. La confirmación humana se recuperó del commit `68fa1cd51e94c28e1478c8a85e42d437211bccbb`: 450 para familiar conviviente y cumpleaños incluido en la exención, imposición desde el día siguiente al 18º cumpleaños. No se solicitó repetir esa autorización. Es una confirmación funcional previa; no equivale a nueva certificación municipal del criterio de cumpleaños ni a aceptación del portal.

Los casos de nacimiento ausente o inválido no generan un adulto pagante implícito. Un residente de Bellaria identificado bloquea las salidas cuando falta la correspondencia fiscal documentada; no se inventa un código de residente. No se crean reducciones que las fuentes del perfil no establecen. Las modificaciones posteriores no recalculan indebidamente los documentos ya consolidados. La lectura actual actualiza de tres a dos el pendiente de clasificación del informe anterior. La lectura de consistencia no detectó conflictos de clasificación activa ni nacimientos imposibles o futuros en los registros efectivos de la temporada. El histórico probado es sintético: la lectura agregada no encontró exports reales consolidados.

El informe/CSV selecciona por **fecha de arribo**, incluyendo el cálculo completo del movimiento seleccionado. No acredita cobro ni riversamento mensual. Para estancias que cruzan meses, el reglamento atribuye el riversamento al mes de conclusión; el módulo no contiene un registro de cobros, saldos y versamenti. Esta distinción aparece también en las impresiones.

## Fuentes y alcance normativo

La verificación usa los cinco PDF oficiales de CC29/2026, reglamento/coordinado/comparativo y GC184/2025 con tarifas: SHA256 idénticos a los recuperados anteriormente. Los CSV generales MEF cambiaron, pero sus filas de Bellaria 2025/2026 permanecen iguales. En [fonti.json](evidenze/tassa-verifica-2026-10-24/fonti.json) constan URL oficial, fecha y comprobación individual.

Perfil comprobado: **Bellaria-Igea Marina, alberghi, 2026**; tarifas por noche €1 para 1–2 estrellas, €1,50 para 3 estrellas y €2,50 para 4–5 estrellas, del 1 de junio al 30 de septiembre, máximo seis noches. Exclusiones y exenciones reglamentarias se mantienen separadas de códigos operativos y de la configuración del establecimiento. CC29 fue publicada en MEF el 14 de agosto de 2026; el acto prevé eficacia desde el 1 de octubre y cambia obligaciones/Modello21, sin nuevas tarifas. La página municipal general aún remite al texto 2025. No se certifican otros municipios, categorías o años.

## Pruebas y publicación efectivas

R1: dos fallos reproducidos antes del arreglo. R3: 331 pruebas y ocho fallos; seis se reprodujeron sobre main sin los correctivos en R4. Se corrigieron precondiciones de fixtures de categoría/FK, selección explícita de estructura ISTAT y expectativa 404 para relación de otro tenant, manteniendo no exposición/restauración. R5: 331/4.808 +7 browser PASS. R6: 3/34 +4 browser PASS. R7: 34/293 PASS, incluido Cestino.

El tercer defecto se reprodujo en R8b: 1 test / 0 aserciones / 1 error SQL. La invocación R8 previa fue rechazada por falta del selector del runner y no ejecutó tests. R9: 28/161 PASS. **R10: 333 tests / 4.829 aserciones y 7 browser PASS. R11: 24 tests / 118 aserciones y 4 browser PASS.** 17 guardias puras, 23 rechazos de override por runtime, build Vite/GEO y sintaxis comprobados. Las ejecuciones solapadas no se suman. PHPUnit/Laravel y Playwright se ejecutaron solamente en la infraestructura oficial prevista.

C5 final: **S0 148,323 s / RUN 246,648 s**, 455 muestras, cero DDL y 55 muestras de quietud previas. Nuevos PREPARE/S0, recuperación independiente completa en VM a partir de la base PREPARE verificada y el S0 fresco, sin red externa y custodia Extra autenticada/descifrada con clave separada. Transferencia del archivo completo PREPARE: 99,860 s, dentro del límite original. Guardián concluido/desarmado/inactivo/deshabilitado. Antes de la QA, DB byte-idéntico a S0 y 92 persistentes conformes. Posteriormente, eliminación de todas las fixtures propias, HMAC idénticos en las 66 tablas y 92 persistentes conformes, exceptuando únicamente el mtime de directorios por la creación/eliminación de logos sintéticos; archivos y demás metadatos preservados. Ningún restore ni reinicio de AUTO_INCREMENT.

QA publicada final **por grupos independientes sobre el mismo SHA**: 29 escenarios Web, 3 Tassa y 4 anagrafiche PASS. Un intento se detuvo en navegación del circuito; otro pasó circuito/HTML/regresiones/accesos y ocho de nueve escenarios de textos/colores antes de un nuevo timeout de navegación, seguido por timeout de la captura diagnóstica. Ambos se conservaron y sus fixtures se eliminaron con HMAC idénticos. Se repitieron completos los nueve escenarios textos/colores y las tres vistas previas sobre otra fixture, PASS. No se presenta una única ejecución monolítica sin fallos ni se suman pasadas parciales o solapadas. Solo se añadió diagnóstico sin payload, cabeceras, query ni token, manteniendo selectores, aserciones y timeouts. La causa del transporte no está atribuida.

Monitor posterior a todas las limpiezas: **servidor 918,551 s / 31 muestras / 279 sondas**, **Mac 921,895 s / 31 muestras / 93 peticiones HTTPS con DNS/TLS ordinarios**, PASS. **Cero errores Laravel/FPM desde el T0 del RUN final**. El ciclo previo produjo dos errores GEO sintéticos, conservados y corregidos; no se afirma que todo el día estuvo libre de errores.

Revisión visual: 13 PDF / 14 páginas offline y cuatro PDF online de una página, sin cortes o superposiciones en las páginas inspeccionadas. [Resumen online](evidenze/tassa-verifica-2026-10-24/stampa-online.png), [450 online](evidenze/tassa-verifica-2026-10-24/450-online.png), [grupo página 2 offline](evidenze/tassa-verifica-2026-10-24/gruppo-pagina2.png).

## Pendientes y advertencias separadas

Impiden un cierre integral: **aceptación externa del CSV**, completar con información verificada **las dos clasificaciones canónicas activas** y **el nacimiento efectivo ausente**. Este último impide acreditar la edad de ese huésped cuando sea necesaria para el cálculo. El cumpleaños incluido está validado como decisión funcional previamente confirmada, sin nueva certificación municipal. No se inventaron valores para superar controles. El próximo paso es completar esas fichas mediante Admin/Superadmin y verificar el CSV mediante validador oficial o modalidad de prueba sin efectos fiscales cuando exista acceso.

Advertencias técnicas documentadas: arranque VM vsock fallido y segundo arranque correcto; primer PREPARE detenido por custodia symlink, corregida únicamente la ruta hacia un directorio interno físico con nueve controles y symlink original preservado; cortes de transporte conservados sin causa exclusiva demostrada; un reintento temprano sobre la misma fixture produjo una precondición duplicada, resuelta mediante fixture nueva sin relajar selectores; parada segura del RUN por capacidad, luego dos planes de deduplicación expresamente autorizados. Estos últimos mantuvieron archivos, bytes, inodos y metadatos idénticos: 11.154 y 30.122 operaciones sobre conjuntos solapados, sin sumarlas como archivos únicos. No se borraron SQL, cifrados, claves, uploads ni respaldos. La primera custodia Extra del correctivo falló antes de copiar por carpeta padre ausente; creada la carpeta exclusiva y repetida la comprobación integral, PASS. Ningún límite o presupuesto fue aumentado.

La revisión automática rechazó una solicitud de exportación de identificadores privados: se continuó mediante agregados. Un verificador heredado incluyó identificadores de estructuras en su registro privado inicial; los adaptadores posteriores se limitaron a conteos y esos archivos no se publican. Las evidencias versionadas contienen datos sintéticos o agregados. No se enviaron declaraciones, CSV al portal, emails ni comunicaciones reales a huéspedes o entidades.

VM devuelta **apagada**, daily `945ef553aab29ac437ff194f7cfad081fb7ab7c7`, SELinux Enforcing, disco de 36 GiB y XFS de 37.290.283.008 bytes preservados. Reserva VM de 1 GiB y umbrales Mac 8 GiB / Extra 40 GiB mantenidos; solo procesos de vigilancia propios concluidos. Riesgo residual: uso fiscal general no acreditado por la aceptación externa pendiente y los datos reales incompletos; no se certifica todo el proyecto. El Maestro se actualizó; el estado general continúa BASELINE IN AUDIT.

| Control de entrega | Sí / No |
|---|---|
| Código / tests / documentación modificados | SÍ / SÍ / SÍ |
| Configuración fiscal, SMTP o DNS modificada | NO |
| Ruta operativa de custodia C5 adaptada | SÍ, directorio interno físico verificado |
| Esquema o valores reales modificados | NO; solo fixtures creadas y eliminadas, sin reset de contadores |
| Tests ejecutados / commit / push / integración | SÍ / SÍ / SÍ / SÍ |
| Deploy y verificación publicada | SÍ |
| Transmisiones fiscales o mensajes reales | NO |

**¿Puedo considerar cerrado el módulo Tassa di soggiorno y utilizarlo operativamente? No. El circuito técnico publicado está comprobado, pero los pendientes externos y de datos reales impiden acreditar el cierre integral y considerarlo apto para un uso fiscal general sin reservas.**

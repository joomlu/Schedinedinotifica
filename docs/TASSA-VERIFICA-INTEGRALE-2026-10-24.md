# Tassa di soggiorno: verificación integral del 24 de octubre de 2026

Estado: **PARCIAL**, BASELINE IN AUDIT. Correcciones calificadas en VM; integración y nueva publicación pendientes. Este informe se actualizará con el resultado real del procedimiento C5.

La versión online inicial es `4a9c0c55ce9319bef89dea97b9a1885c1ace5e66`: Git limpio, 1.710 digest conformes, sonda FPM autenticada y nueve sondas HTTPS positivas. GitHub main es `2fd17c8076b6631cc61852aa0bd4f0439706548a`; incorpora anagrafiche unificadas que aún no están publicadas. El checkout original `6a652b72` y sus cambios se preservan; trabajo en rama `codex/tassa-verifica-2026-10-24`.

## Correcciones y pruebas

Dos defectos reproducidos antes de corregir: código 450 ausente del catálogo automático cuando no existe un registro legacy; fecha imposible 2008-02-31 normalizada a 2008-03-02. El catálogo compartido incorpora 450 sin escribir copias en DB. El parser acepta únicamente formatos explícitos con comparación exacta, rechaza fechas imposibles o relativas y bloquea la determinación de edad cuando la fecha es inválida. No se modifican tarifas, temporada, topes ni snapshots históricos.

La confirmación expresa del usuario sobre 450 y cumpleaños se recuperó del commit `68fa1cd51e94c28e1478c8a85e42d437211bccbb`: no está pendiente de nueva autorización. 450 corresponde operativamente al familiar del gestor anagráficamente conviviente; cumpleaños incluido en la exención e imposición desde el día siguiente al 18º cumpleaños. Es confirmación funcional humana, diferenciada de certificación municipal y aceptación del portal.

R1: 2 fallos reproducidos. R2: 20 pruebas / 268 aserciones, PASS. R3: 331 pruebas, 8 fallos; seis se reprodujeron sin los correctivos sobre main en R4. Se corrigieron precondiciones de fixtures de categoría/FK, selección explícita de estructura en ISTAT y expectativa 404 para un vínculo Web de otro tenant; se conserva la prueba de no exposición y restauración. La prueba de aislamiento usa un código sintético legacy porque 450 tiene ahora descripción canónica compartida. El nuevo caso de nacimiento recibió configuración fiscal válida para verificar específicamente el error de nacimiento.

R5: 331 pruebas / 4.808 aserciones y 7 pruebas de navegador, PASS. R6: 3 pruebas / 34 aserciones y 4 pruebas de navegador, PASS. R7: 34 pruebas / 293 aserciones, PASS, incluidos Cestino y el nuevo caso de exclusión de Bozze/Web/Arrivi y conservación del consolidado después de anulación. 17 guardias puras y 23 rechazos de override por runtime, PASS; build Vite/GEO y sintaxis PHP correctos. No se suman pruebas solapadas. Todos los tests Laravel/Playwright usan exclusivamente la VM oficial, DB/storage efímeros y namespace sin red externa.

13 PDF / 14 páginas: inspección visual de resumen, exención total y grupo en dos páginas sin cortes ni superposiciones. [Resumen sintético](evidenze/tassa-verifica-2026-10-24/stampa-sintetica.png), [grupo página 2](evidenze/tassa-verifica-2026-10-24/gruppo-pagina2.png). Evidencias y digest: [verifica.json](evidenze/tassa-verifica-2026-10-24/verifica.json).

## Matriz de resultados

| Área | Prueba realizada | Resultado | Entorno | Evidencia |
|---|---|---|---|---|
| Fuente canónica | Misma estructura, catálogo y permisos para Admin, Superadmin, titular y reception | PASS; cambios visibles sin copias independientes | VM | R5 Anagrafiche / R6 navegador |
| Tarifas y períodos | Hoteles de 1–5 estrellas, temporada, máximo seis noches, categorías/años no certificados bloqueados | PASS en alcance Bellaria 2026 | VM | SuiteTassaBellaria |
| Edad y cumpleaños | Edad por noche, cumpleaños incluido, límites y reloj histórico | PASS | VM | TassaBellariaEtaCsvTest |
| Nacimiento | Ausente, imposible, relativa, futura; titular y componente | PASS; error explícito, sin adulto implícito | VM | R1/R5/R7 |
| Código 450 | Sin registro legacy, formulario, individual, CSV, resumen y snapshot | PASS: 10 noches = 6 exentas + 4 código 777; €0 | VM | TassaVerificaIntegraleTest |
| Familia y grupo | Individual, componentes, grupo, noches superiores al tope | PASS | VM | R5/PDF |
| Conciliación | Individual, resumen, CSV e impresión | PASS: 20 = 10 exentas + 8 de exceso + 2 imponibles; €3 | VM | R5 / 7 pruebas de navegador |
| Histórico y rectificación | Modificar fecha/edad/exención/categoría, nueva versión, documento anterior | PASS; snapshot cifrado e inmutable | VM | Versionamento/Completeness/R7 |
| Anulación y duplicados | Arrivi/Bozze/Web excluidos; Cestino y conversión Web idempotente | PASS en circuitos probados | VM | R5 Web / R7 Cestino |
| Tenant y permisos | Lectura, escritura, exportación, configuración y entidades ajenas | PASS; 403/404/422 | VM | R5/R6/R7 |
| Fuentes oficiales | CC29/2026, GC184/2025 y MEF | Actos idénticos y filas de Bellaria sin cambios | HTTP oficial | fonti.json |
| Datos operativos | Lectura agregada READ ONLY | 2 clasificaciones activas y 1 nacimiento efectivo faltantes; 0 exports | Producción | verifica.json |
| Publicación actual | Git, digest, FPM, HTTPS | Versión inicial 4a9c0c55 comprobada; nuevo C5 pendiente | Producción | online_iniziale |
| Aceptación StayTour | Validador o sesión de gestor | NO VERIFICADA; usuario confirma que no dispone de acceso | Externo | Respuesta del 24 de octubre |

## Alcance normativo y pendientes

[Registro actualizado de fuentes](evidenze/tassa-verifica-2026-10-24/fonti.json). Los cinco PDF oficiales de CC29, reglamento, GC184 y tarifas tienen el mismo SHA256. Los CSV generales MEF cambiaron, pero sus filas de Bellaria 2025/2026 son idénticas. El perfil aplicable comprobado es Bellaria, alberghi, 2026: tarifas de €1 / €1,50 / €2,50 según estrellas; temporada del 1 de junio al 30 de septiembre; máximo seis noches; exclusiones de residentes y day-use, y exenciones reglamentarias. CC29 publicada el 14 de agosto de 2026, vigencia del 1 de octubre según el acto; cambios de obligaciones, sin nuevas tarifas. La página municipal general sigue remitiendo al texto 2025. No se extrapolan reglas a otros municipios, categorías o años.

La selección del informe/CSV es por fecha de arribo. Para estancias a caballo del mes, no equivale al riversamento, que el reglamento atribuye al mes de conclusión. El módulo no contiene registro de cobros, saldos o versamenti y los documentos lo indican. No se inventan reducciones ni códigos de residentes; un residente de Bellaria identificado bloquea outputs mientras falta raccordo documentado. Questura e ISTAT conservan flujos propios, sin comunicaciones reales.

Para cerrar: aceptación del CSV mediante validador oficial o modalidad de prueba sin efectos fiscales; completar dos clasificaciones activas y un nacimiento con fuentes reales; publicar y verificar las correcciones en C5. No hay fuente para inventar esos valores. La revisión automática rechazó transferir los ID privados a local; se conservan solo agregados. No se enviaron declaraciones, CSV al portal ni mensajes reales.

Aviso técnico separado: primer arranque de la VM con error vsock, parada ordenada y segundo arranque correcto; no se cambiaron recursos, discos, SELinux ni daily. Se conserva el diagnóstico. No demuestra un defecto fiscal.

**¿Puedo considerar cerrado el módulo Tassa di soggiorno y utilizarlo operativamente? No: la clausura integral todavía no está acreditada.**

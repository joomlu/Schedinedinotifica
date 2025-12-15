# Git backup y reemplazo del proyecto

Este registro documenta las acciones realizadas para preservar el estado previo y preparar el repositorio para reemplazar su contenido con el proyecto actual.

## Resumen de acciones
- Se creó una rama de respaldo local `backup/work` desde el estado actual (`work`) para conservar el historial antes de cualquier sobrescritura.
- El repositorio ya estaba inicializado; no se configuró remoto porque no se proporcionó una URL.
- No se detectaron archivos externos al proyecto en el directorio de trabajo.

## Pasos pendientes para completar el flujo solicitado
1. Configurar el remoto: `git remote add origin <URL-del-repo>`.
2. Verificar el estado: `git status` (solo deberían aparecer archivos del proyecto actual).
3. Preparar y confirmar cambios: `git add . && git commit -m "Reemplaza repo con proyecto actual"` si hay modificaciones nuevas que subir.
4. Subir sobrescribiendo historial si es necesario: `git push origin main --force` (o la rama correspondiente).
5. Validar en GitHub que el contenido coincida con el proyecto actualizado.

> Nota: forzar el push sobrescribirá el historial remoto. Conservar la rama `backup/work` localmente permite recuperar el estado previo en caso de ser necesario.

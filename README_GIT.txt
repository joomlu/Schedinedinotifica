==========================================================
README — Git + VS Code + Laravel (Schedinedinotifica)
==========================================================

Proyecto: /Users/jorgeluccitelli/Herd/Schedinedinotifica
Entorno: macOS + Herd + VS Code + MySQL

Objetivo:
- Trabajar con control de versiones (Git).
- Subir el código a un repositorio remoto (GitHub/GitLab).
- Usar IA dentro de VS Code (Copilot/Continue) para asistir el desarrollo.

----------------------------------------------------------
1) Conceptos clave: add / commit / push / pull
----------------------------------------------------------

Flujo básico de trabajo:

    (tu código)         git add         git commit                git push              git pull
      cambios   ───▶  "preparar"  ───▶  "guardar versión"  ───▶  "subir al remoto"  ◀──  "traer del remoto"

- git add    → elegís qué archivos van a la próxima versión.
- git commit → guardás una versión local con mensaje descriptivo.
- git push   → subís esa versión a tu repo online (seguridad + compartir).
- git pull   → traés cambios nuevos del remoto a tu máquina.

Comandos habituales:
  git status                        # ver qué cambió
  git add .                         # preparar todos los cambios
  git commit -m "feat/fix/chore: ..."  # guardar versión local
  git push                          # subir al remoto
  git pull                          # traer cambios del remoto

----------------------------------------------------------
2) Inicializar Git en este proyecto (una sola vez)
----------------------------------------------------------

cd ~/Herd/Schedinedinotifica
git init
git branch -M main

# crear .gitignore y .env.example (si no existen)
# (ver sección 6)

git add .
git commit -m "chore: proyecto base + configuración inicial"

# cuando tengas cuenta y repo remoto:
git remote add origin git@github.com:jorgeluccitelli/schedinedinotifica.git
git push -u origin main

# rama de integración
git checkout -b dev
git push -u origin dev

----------------------------------------------------------
3) Flujo diario recomendado
----------------------------------------------------------

1) Actualizar antes de trabajar
   git pull

2) Crear rama por funcionalidad
   git checkout -b feature/nombre-claro

3) Programar con ayuda de IA en VS Code
   - Copilot/Continue para autocompletar y generar código/tests.

4) Verificaciones locales
   composer lint && composer stan && composer test

5) Guardar y subir
   git add .
   git commit -m "feat: descripción clara"
   git push -u origin feature/nombre-claro

6) Abrir Pull Request → Merge a dev → luego a main

----------------------------------------------------------
4) Uso de IA (prompts útiles en VS Code)
----------------------------------------------------------

Ejemplo 1: Refactor SOLID + tests (Pest)
  "Refactoriza este controlador aplicando SOLID.
   Mueve la lógica a un Service, crea un FormRequest para validación y
   generá tests con Pest para store/update. Mostrame el código completo."

Ejemplo 2: Recurso completo
  "Crea migración + modelo + factory + seeder + controlador REST + rutas API
   para Booking con campos: guest_name (string), check_in (date), check_out (date),
   room_id (int), status (enum: pending|confirmed|canceled). Incluí validación y tests."

Ejemplo 3: Diagnóstico
  "Analiza este método y detecta N+1 queries, índices faltantes y oportunidades de eager loading."

----------------------------------------------------------
5) Chuleta de comandos rápidos
----------------------------------------------------------

# Estado actual
git status

# Ver cambios
git diff

# Preparar cambios
git add archivo.php
git add .             # todos

# Guardar versión
git commit -m "fix: mensaje claro"

# Subir al remoto
git push

# Traer cambios remotos
git pull

# Ramas
git checkout -b feature/nueva-funcionalidad
git checkout main
git branch -d feature/nueva-funcionalidad

# Ver historial resumido
git log --oneline --graph --decorate --all

Convención de mensajes:
  feat: nueva funcionalidad
  fix: corrección de bug
  refactor: reestructuración interna
  chore: tareas varias / setup
  docs: documentación

----------------------------------------------------------
6) Archivos base del proyecto (recomendados)
----------------------------------------------------------

.vscode/extensions.json   → recomendación de extensiones
.vscode/settings.json     → formato y PHP 8.2
.gitignore                → NO subir vendor/.env/etc.
.env.example              → plantilla sin secretos

.gitignore (Laravel/Herd):
  /vendor/
  /node_modules/
  /public/storage
  /storage/*.key
  /storage/app/public
  /storage/framework/cache
  /storage/framework/sessions
  /storage/framework/testing
  /storage/framework/views
  /storage/logs
  .env
  .env.*.local
  .phpunit.result.cache
  Homestead.yaml
  .idea/
  /.DS_Store
  .vscode/*
  !.vscode/extensions.json
  !.vscode/settings.json

.env.example (plantilla):
  APP_NAME=Schedinedinotifica
  APP_ENV=local
  APP_KEY=
  APP_DEBUG=true
  APP_URL=http://schedinedinotifica.test

  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=schedinedinotifica
  DB_USERNAME=tanggo
  DB_PASSWORD=tanggo

----------------------------------------------------------
7) Dónde NO cometer errores
----------------------------------------------------------

- Nunca subir .env ni /vendor al repo.
- Hacer commit pequeños y descriptivos (uno por tarea concreta).
- Siempre hacer git pull antes de empezar a trabajar.
- Probar local (lint/stan/tests) antes de abrir PR.
- Evitar trabajar directo en main (usar dev y feature/*).

----------------------------------------------------------
8) Herramientas recomendadas
----------------------------------------------------------

VS Code:
- GitHub Copilot / Copilot Chat (o Continue/Codeium)
- PHP Intelephense
- Laravel Artisan
- Blade Formatter
- Error Lens
- EditorConfig
- GitLens

Integración continua (cuando el repo esté online):
- GitHub Actions con Pint + Larastan + Pest

==========================================================
Fin.
==========================================================

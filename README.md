# Schedinedinotifica

Proyecto Laravel basado en **Velzon + Vite**, desarrollado para la gestión y notificación de hospedes en entornos multi-hotel.

---

## 🧩 Requisitos

- **PHP 8.2 / 8.3** (Herd recomendado)
- **Composer 2.x**
- **Node.js 20 / 22** + **npm**
- **MySQL 8.x**
- **Laravel Herd** para entorno local (https://herd.laravel.com)
- **Visual Studio Code** con extensiones GitHub Copilot y GitLens

---

## ⚙️ Setup local

```bash
# Clona el repositorio
git clone https://github.com/joomlu/Schedinedinotifica.git

# Entra en la carpeta del proyecto
cd Schedinedinotifica

# Copia el archivo de entorno y genera la key
cp .env.example .env
php artisan key:generate

# Instala dependencias de Laravel y Node
composer install
npm ci

# Ejecuta las migraciones (si aplica)
php artisan migrate

# Ejecuta el entorno de desarrollo
npm run dev

# Para compilar en producción
npm run build

## 👤 Account di test (login)

Sono disponibili questi utenti preconfigurati per l'accesso all'app (tutti con la stessa password):

- Email: superadmin@test.test — Ruolo: superadmin — Password: 123456
- Email: admin@test.test — Ruolo: admin — Password: 123456
- Email: cliente@test.test — Ruolo: cliente — Password: 123456
- Email: struttura@test.test — Ruolo: struttura — Password: 123456

Note sui permessi
- **superadmin**: può creare tutte le condizioni (tutti i permessi)
- **admin**: può creare clienti e strutture, gestire utenti/ruoli, accedere al panel admin
- **cliente**: può gestire più strutture, creare clienti, gestire accessi individuali di staff (manage staff, manage users)
- **struttura**: accesso individuale per chi amministra il software; non può creare nada, solo visualizzare e usare l'app (read-only)

Il campo "role" interno del modello utente (colonna `users.role`) è mappato rispettivamente a: superadmin, admin, hotel_staff (cliente), hotel_owner (struttura).

## �📧 Configuración Mail (Reset Password)

Para probar el envío del enlace de recuperación de contraseña en local, tienes dos opciones rápidas:

1) Usar el mailer de log (no envía mails, los escribe en storage/logs/laravel.log):

```
MAIL_MAILER=log
```

2) Usar Mailtrap (sandbox SMTP):

```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Schedinedinotifica"
```

Luego visita: /password/reset, introduce el email de un usuario registrado y verifica que se envía la notificación (en log o en Mailtrap).

> Nota: En el entorno de tests (phpunit), el mailer ya está configurado para no enviar emails reales.

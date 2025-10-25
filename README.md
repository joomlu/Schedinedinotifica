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

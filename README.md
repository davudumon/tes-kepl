<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Project Setup Instructions

### Prerequisites

Before you begin, ensure you have the following installed on your system:

- **PHP** 8.2 or higher
- **Composer** (PHP dependency manager)
- **Node.js** and **npm** (for frontend assets, and for the Vue app in `frontend/`)
- **SQLite**, **MySQL**, or **PostgreSQL** (for database)
- **Git** (for version control)

### Installation Steps

1. **Clone or download the project**
   ```bash
   cd evolusi-pl-24-540567-SV-24877
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Create environment configuration file**
   ```bash
   cp .env.example .env
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Configure your database**
   
   Edit the `.env` file and update the following database variables:
   ```
   DB_CONNECTION=sqlite  # or mysql, pgsql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kepl_database
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. **Run database migrations**
   ```bash
   php artisan migrate
   ```

8. **(Optional) Seed the database with sample data**
   ```bash
   php artisan db:seed
   ```

9. **Build frontend assets**
   ```bash
   npm run build
   ```
   
   For development with hot reload:
   ```bash
   npm run dev
   ```

### Running the Application

**Development Server:**
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

**With frontend development server (in another terminal):**
```bash
npm run dev
```

**With the Vue 3 app (in another terminal):**
```bash
cd frontend
npm run dev
```

The Vue app will be available at `http://localhost:5173` and will call the
Laravel API at the address given by `VITE_API_URL`.

### Testing

Run the test suite:
```bash
php artisan test
```

## API JSON untuk Frontend

Route JSON untuk tabel CRUD `tasks` berada di `routes/api.php` dan memakai
middleware group `api` (stateless):

```
GET    /api/health
GET    /api/tasks
POST   /api/tasks
GET    /api/tasks/{task}
PUT    /api/tasks/{task}
DELETE /api/tasks/{task}
```

`config/cors.php` mengizinkan origin Vue di laptop. Daftar origin dibaca dari
`.env`:

```dotenv
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

## Frontend Vue 3

Aplikasi Vue 3 dengan Vue Router berada di folder [`frontend/`](frontend/).
Alamat API dibaca dari environment variable `VITE_API_URL`, bukan ditulis
langsung di kode.

```bash
cd frontend
cp .env.example .env
npm install
npm run dev      # http://localhost:5173
```

Jalankan `php artisan serve` di terminal lain supaya `GET /api/tasks` bisa
dijangkau. Ringkasan perintahnya ada di [`frontend/README.md`](frontend/README.md).

## Testing Frontend

```bash
cd frontend
npm run lint     # ESLint
npm run test     # Vitest, tanpa perlu Laravel berjalan
npm run build    # menghasilkan frontend/dist/
```

## CI/CD

Workflow [`.github/workflows/ci.yml`](.github/workflows/ci.yml) memuat dua
rantai.

**Backend** — `build → test → staging → production`.

**Frontend** — empat job berurutan yang dirangkai dengan `needs:`:

```
frontend-lint → frontend-test → frontend-build → frontend-deploy
  npm ci          npm ci          npm ci          (tanpa npm)
 eslint .      vitest run     vite build        download-artifact
```

- Tiga job pertama memakai `actions/setup-node@v4` dengan `cache: 'npm'`,
  lalu `npm ci`.
- `frontend-build` menyerahkan `frontend/dist` lewat `actions/upload-artifact@v4`.
- `frontend-deploy` **tidak** menjalankan `npm run build`; ia mengunduh
  artefak tersebut dan mencetak isi `dist/` ke log.
- `frontend-deploy` dan `production` memakai kondisi
  `github.event_name == 'push' && github.ref == 'refs/heads/main'`, sehingga
  pada Pull Request keduanya **skipped** sementara `lint`, `test`, dan
  `build` tetap berjalan.
- Staging hanya melakukan `echo` simulasi deployment.
- Production memakai environment GitHub `production` dengan tujuh langkah
  `echo` dari `deploy.sh`.

Aktifkan pengaman production di GitHub: **Settings → Environments → production → Required reviewers**, lalu pilih minimal satu reviewer. Pengaturan reviewer ini berada di GitHub, sehingga tidak dapat didefinisikan dalam berkas YAML workflow.

Bukti pengujian: [`docs/VALIDASI_FRONTEND_CI.md`](docs/VALIDASI_FRONTEND_CI.md) dan [`docs/VALIDASI_P3.md`](docs/VALIDASI_P3.md). Ringkasan siap dijadikan laporan: [`docs/LAPORAN_P4.md`](docs/LAPORAN_P4.md).

### Additional Useful Commands

- **Clear caches:**
  ```bash
  php artisan cache:clear
  php artisan config:clear
  php artisan view:clear
  ```

- **Create a new migration:**
  ```bash
  php artisan make:migration migration_name
  ```

- **Create a new model with migration:**
  ```bash
  php artisan make:model ModelName -m
  ```

- **Create a new controller:**
  ```bash
  php artisan make:controller ControllerName
  ```

- **Run code quality checks:**
  ```bash
  php artisan pint
  ```

### Troubleshooting

- **"No application encryption key has been specified"**
  - Run: `php artisan key:generate`

- **Database connection errors**
  - Verify database credentials in `.env` file
  - Ensure database server is running
  - Check database name and user permissions

- **Permission errors on storage or bootstrap folders**
  - Run: `chmod -R 775 storage bootstrap/cache`

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

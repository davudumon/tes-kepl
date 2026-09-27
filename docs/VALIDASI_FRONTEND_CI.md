# Validasi P4 — Endpoint JSON Laravel + Vue 3 + CI/CD 4 Job

Dokumen ini adalah bukti pengujian dari implementasi. Rincian praktikum 3
(pipeline Laravel `build → test → staging → production`) tetap ada di
[`VALIDASI_P3.md`](VALIDASI_P3.md).

## 1. Endpoint JSON dari tabel CRUD

Tabel CRUD yang dipakai adalah `tasks`. Route-nya dipindah dari
`routes/web.php` ke `routes/api.php` (baru) supaya berada di prefix `/api`
dan memakai middleware group `api` (stateless).

```
GET    /api/health
GET    /api/tasks
POST   /api/tasks
GET    /api/tasks/{task}
PUT    /api/tasks/{task}
DELETE /api/tasks/{task}
```

Berkas yang berubah:

| Berkas                 | Perubahan                                                  |
| ---------------------- | ---------------------------------------------------------- |
| `routes/api.php`       | baru, mendaftarkan `Route::apiResource('tasks', ...)`        |
| `bootstrap/app.php`    | menambah `api: __DIR__.'/../routes/api.php'`                 |
| `routes/web.php`       | route `tasks` dihapus (sudah pindah), `projects` tetap       |
| `tests/Feature/TaskTest.php` | 6 path `/tasks` → `/api/tasks`, plus 1 test prefix      |
| `tests/Feature/ApiCorsTest.php` | baru, 4 test: JSON, header CORS, preflight, health |

### Membolehkan Vue di laptop mengaksesnya

`config/cors.php` (baru) mengatur daftar origin yang diizinkan, dibaca dari
environment variable `CORS_ALLOWED_ORIGINS`:

```dotenv
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

Middleware `HandleCors` sudah ada di global stack Laravel 12, jadi seluruh
path `api/*` otomatis memakai aturan ini.

## 2. Aplikasi Vue 3

Berada di `frontend/`, memakai Vue 3 + Vue Router dengan lima route
(`/tasks`, `/tasks/new`, `/about`, redirect `/`, dan catch-all 404).

Alamat API **tidak** ditulis di dalam kode. `frontend/src/api/client.js`
membacanya dari environment variable:

```js
const url = import.meta.env.VITE_API_URL
```

Kalau kosong, aplikasi melempar error dengan pesan yang jelas. Bukti bahwa
nilai benar-benar di-inline saat build: menjalankan build dengan
`VITE_API_URL=https://api.kepl.example.com/api` menghasilkan bundle yang
mengandung string tersebut.

## 3. Workflow empat job frontend

`.github/workflows/ci.yml` memuat rantai empat job yang dirangkai dengan
`needs:`:

```
frontend-lint → frontend-test → frontend-build → frontend-deploy
   npm ci          npm ci          npm ci          (tanpa npm)
  eslint .     vitest run     vite build        download-artifact
```

- Tiga job pertama memakai `actions/setup-node@v4` dengan `cache: 'npm'` dan
  `cache-dependency-path: frontend/package-lock.json`, lalu `npm ci`.
- `frontend-deploy` tidak menjalankan `npm` sama sekali.

Verifikasi cepat (`Select-String` pada `ci.yml`):

| Perintah        | Job               |
| --------------- | ----------------- |
| `npm ci`        | lint, test, build |
| `npm run build` | build             |
| `npm` (apa pun) | deploy: tidak ada |

## 4. Penyerahan hasil build ke job deploy

`frontend-build` menjalankan `npm run build`, lalu menyerahkan
`frontend/dist` lewat `actions/upload-artifact@v4` dengan nama artefak
`frontend-dist` dan `if-no-files-found: error`.

`frontend-deploy` hanya melakukan `actions/download-artifact@v4` ke
`frontend/dist`, lalu mencetak isi `dist/` ke log:

```
--- daftar file ---
frontend/dist/assets/index-xxxx.css
frontend/dist/assets/index-xxxx.js
frontend/dist/index.html

--- frontend/dist/index.html ---
<!doctype html> ...
```

Job itu juga menyalin artefak ke `public/frontend` supaya perpindahan
artefak benar-benar terlihat, bukan sekadar dicetak.

## 5. Deploy hanya jalan dari main

```yaml
if: github.event_name == 'push' && github.ref == 'refs/heads/main'
```

- Pada **Pull Request**: `frontend-deploy` berstatus **skipped**, dan
  `production` yang `needs: [staging, frontend-deploy]` ikut **skipped**.
  `lint`, `test`, dan `build` tetap berjalan.
- Pada **push ke `main`**: `frontend-deploy` berjalan, disusul `production`
  memakai GitHub Environment `production`.

## 6. Unit test Vitest

`frontend/tests/taskStats.spec.js` menguji fungsi murni di
`frontend/src/utils/taskStats.js`:

- `summarizeTasks()` — total, selesai, belum, persen; termasuk kasus daftar
  kosong, input non-array, dan pembulatan persen.
- `validateTaskPayload()` — judul wajib, batas 255 karakter, deskripsi opsional.
- `formatDate()` — nilai kosong dan format lokal.

`frontend/tests/TaskItem.spec.js` menguji komponen `TaskItem.vue` (render,
status, event `toggle`/`remove`, tombol nonaktif).

Semua test berjalan di GitHub Actions **tanpa Laravel** karena tidak ada
`fetch` ke API maupun dependensi pada framework PHP.

```
$ npm run test
 Test Files  2 passed (2)
      Tests  17 passed (17)
```

## 7. Rangkaian bukti

### 7a. Pull Request → deploy skipped

1. Branch `feature/vue-frontend-ci` di-push ke origin.
2. Buka Pull Request `feature/vue-frontend-ci` → `main` di GitHub.
3. Perhatikan tab **Actions**: `Backend / Build`, `Backend / Test`,
   `Frontend 1/4 / Lint`, `Frontend 2/4 / Test`, `Frontend 3/4 / Build`, dan
   `Deploy to Staging` hijau, sedangkan `Frontend 4/4 / Deploy` dan
   `Deploy to Production` berstatus **skipped**.

### 7b. Gagalkan satu test → pipeline merah, lalu diperbaiki

1. Ubah satu assertion di `frontend/tests/taskStats.spec.js` supaya salah
   (misalnya `expect(summarizeTasks([]).total).toBe(99)`), lalu push.
2. `Frontend 2/4 / Test` menjadi merah dan workflow berhenti di sana:
   `Frontend 3/4 / Build`, `Deploy to Staging`, `Frontend 4/4 / Deploy`, dan
   `Deploy to Production` tidak dijalankan. Checkout Pull Request berubah merah.
3. Kembalikan assertion ke nilai yang benar dan push lagi.
4. Seluruh job hijau kembali.

## Verifikasi lokal yang sudah dijalankan

```
$ php artisan route:list --path=api
  GET|HEAD  api/health
  GET|HEAD  api/tasks
  POST      api/tasks
  GET|HEAD  api/tasks/{task}
  PUT|PATCH api/tasks/{task}
  DELETE    api/tasks/{task}

$ php artisan test
  Tests: 16 passed (40 assertions)

$ curl -H "Origin: http://localhost:5173" http://localhost:8000/api/tasks
  Access-Control-Allow-Origin: *

$ cd frontend && npm ci && npm run lint && npm run test && npm run build
  lint  : 0 error
  test  : 17 passed
  build : dist/index.html + dist/assets/index-*.css + dist/assets/index-*.js
```

## Catatan

- `database/database.sqlite` tidak dilacak Git (`.gitignore` → `*.sqlite*`).
  File lokal sempat tertinggal dari skema lama (`tasks.is_completed`), yang
  tidak cocok dengan migration (`tasks.completed`). Diperbaiki dengan
  `php artisan migrate:fresh`. Test CI memakai database `:memory:`
  sesuai `phpunit.xml`, sehingga tidak terpengaruh.
- Semua perintah deploy di `staging` dan `production` tetap berupa
  `echo` simulasi, tidak ada koneksi ke server nyata.

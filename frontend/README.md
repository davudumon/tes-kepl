# Frontend Vue 3 — Dashboard Tugas

Aplikasi Vue 3 + Vue Router di dalam folder `frontend/`. Halaman **Daftar Tugas**
mengambil data dari endpoint JSON Laravel.

## Prasyarat

- Node.js 20 atau lebih baru
- Backend Laravel sudah berjalan di `http://localhost:8000` (`php artisan serve`)

## Setup

```bash
cd frontend
cp .env.example .env      # Windows PowerShell: Copy-Item .env.example .env
npm install
```

`frontend/.env` **wajib** berisi `VITE_API_URL`:

```
VITE_API_URL=http://localhost:8000/api
```

Alamat API hanya dibaca dari environment variable ini
(`src/api/client.js` → `import.meta.env.VITE_API_URL`). Tidak ada URL yang
ditulis langsung di dalam kode. Kalau variabel ini kosong, aplikasi
menampilkan pesan error yang jelas alih-alih gagal diam-diam.

## Menjalankan

```bash
npm run dev        # dev server Vite di http://localhost:5173
npm run build      # build produksi ke frontend/dist/
npm run preview    # preview hasil build
npm run lint       # ESLint (flat config + eslint-plugin-vue)
npm run test       # Vitest, satu kali jalan
```

## Halaman (vue-router)

| Route           | Nama        | Isi                                              |
| --------------- | ----------- | ------------------------------------------------ |
| `/tasks`        | `tasks`     | Daftar tugas dari `GET /api/tasks` + statistik   |
| `/tasks/new`    | `task-create` | Form tambah tugas (`POST /api/tasks`)           |
| `/about`        | `about`     | Alamat API, health check, kontrak endpoint        |
| selain itu      | `not-found` | 404                                               |

## Endpoint Laravel yang dipakai

| Method | Path            | Dipakai oleh                          |
| ------ | --------------- | ------------------------------------- |
| GET    | `/api/tasks`    | Daftar tugas + statistik             |
| POST   | `/api/tasks`    | Form tambah tugas                     |
| PUT    | `/api/tasks/{id}` | Tandai selesai / batalkan            |
| DELETE | `/api/tasks/{id}` | Hapus tugas                         |
| GET    | `/api/health`   | Health check di halaman Tentang     |

## CORS

Backend mengirim `Access-Control-Allow-Origin` karena `config/cors.php`
mengizinkan origin yang disebut di `.env`:

```
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

Kalau frontend diakses dari HP atau komputer lain dalam satu jaringan,
tambahkan IP-nya, misalnya `http://192.168.1.20:5173`.

## Struktur logika yang diuji

`src/utils/taskStats.js` berisi fungsi murni (tanpa `fetch`, tanpa Laravel):

- `summarizeTasks(tasks)` → `{ total, selesai, belum, persen }`
- `validateTaskPayload(payload)` → `{ valid, errors, value }`
- `formatDate(value)` → string tanggal lokal

Keduanya diuji di `tests/taskStats.spec.js` dan berjalan di GitHub Actions
tanpa perlu server Laravel.

# Hasil dan Pembahasan

**Praktikum — Endpoint JSON pada Laravel, Aplikasi Vue 3, dan CI/CD Empat Job**

| | |
| --- | --- |
| Nama | Nawwaf Zayyan Musyafa |
| NIM | 540567 |
| Repository | `KEPL2026/evolusi-pl-24-540567-SV-24877` |
| Branch | `feature/vue-frontend-ci` → Pull Request ke `main` |
| Commit | `4817c39` (implementasi), `e7149e9` (bukti pipeline merah), perbaikan assertion (pipeline hijau) |
| Stack | Laravel 12 (PHP 8.3 di CI), Vue 3 + Vue Router, Vite 6, Vitest 2, ESLint 9, GitHub Actions |

---

## Langkah 1 — Menyediakan Endpoint JSON dari Tabel CRUD

### Hasil

Tabel CRUD yang digunakan adalah `tasks`. Route-nya dipindahkan dari
`routes/web.php` ke `routes/api.php` (berkas baru) supaya berada di prefix
`/api` dan memakai middleware group `api`, yaitu kelompok middleware tanpa
session maupun token CSRF. Berbeda dengan `web.php`, berkas `api.php`
otomatis mendapat prefix `/api` dari Laravel 12.

Enam endpoint yang tersedia:

```
GET    /api/health
GET    /api/tasks
POST   /api/tasks
GET    /api/tasks/{task}
PUT    /api/tasks/{task}
DELETE /api/tasks/{task}
```

Berkas yang berperan:

| Berkas | Perubahan |
| --- | --- |
| `routes/api.php` | baru — `Route::apiResource('tasks', TaskController::class)` dan `GET /api/health` |
| `bootstrap/app.php` | menambah `api: __DIR__.'/../routes/api.php'` pada `withRouting()` |
| `routes/web.php` | route `tasks` dihapus; landing page dan `projects` tetap |
| `config/cors.php` | baru — daftar origin yang diizinkan |
| `.env.example` | ditambah `FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`, `VITE_API_URL` |

### Pembahasan

`apiResource` menghasilkan lima aksi standar (index, store, show, update,
destroy) tanpa menulis routing satu per satu. Alasan route dipindahkan, bukan
disalin, adalah agar tidak ada dua definisi nama route yang sama di berkas
berbeda. Seandainya disalin, `tasks.index` akan terdefinisi ganda sehingga
`php artisan route:cache` gagal dan `route('tasks.index')` bisa menunjuk ke
route yang keliru.

Akses dari Vue di laptop dimungkinkan lewat `config/cors.php`. Middleware
`HandleCors` sudah ada di global stack Laravel 12, sehingga seluruh path
`api/*` otomatis mengikuti aturan di berkas itu tanpa perlu mendaftarkan
middleware baru. Daftar origin dibaca dari environment variable supaya mudah
disesuaikan:

```dotenv
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

Kalau frontend diakses dari HP atau komputer lain dalam satu jaringan,
origin itu cukup ditambahkan ke daftar yang sama.

### Bukti

```
$ php artisan route:list --path=api
  GET|HEAD  api/health
  GET|HEAD  api/tasks
  POST      api/tasks
  GET|HEAD  api/tasks/{task}
  PUT|PATCH api/tasks/{task}
  DELETE    api/tasks/{task}

$ curl -i -H "Origin: http://localhost:5173" http://localhost:8000/api/tasks
  HTTP/1.1 200 OK
  Access-Control-Allow-Origin: *
  [{"id":3,"title":"Tulis unit test Vitest","completed":false, ...}]
```

Pengujian otomatisnya ada di `tests/Feature/ApiCorsTest.php` (empat test: JSON,
header CORS, preflight `OPTIONS`, dan health check) serta
`tests/Feature/TaskTest.php` yang enam path-nya diupdate ke `/api/tasks` dan
ditambah satu test pemeriksa prefix route.

---

## Langkah 2 — Membuat Aplikasi Vue 3 di `frontend/`

### Hasil

Aplikasi Vue 3 dengan Vue Router berisi lima route:

| Route | Nama | Isi halaman |
| --- | --- | --- |
| `/tasks` | `tasks` | Daftar tugas dari `GET /api/tasks` dengan kartu statistik |
| `/tasks/new` | `task-create` | Form tambah tugas (`POST /api/tasks`) |
| `/about` | `about` | Alamat API, health check, dan kontrak endpoint |
| `/` | — | Redirect ke `/tasks` |
| lainnya | `not-found` | Halaman 404 |

Struktur folder `frontend/`:

```
frontend/
├─ package.json, package-lock.json
├─ vite.config.js          plugin vue
├─ vitest.config.js        environment jsdom, include tests/**/*.spec.js
├─ eslint.config.js        flat config
├─ index.html, .env.example, README.md
├─ src/
│  ├─ main.js, App.vue, style.css
│  ├─ router/index.js                 lima route
│  ├─ api/client.js                   baca VITE_API_URL
│  ├─ composables/useTasks.js         state loading/error/saving
│  ├─ utils/taskStats.js              logika murni
│  ├─ components/TaskItem.vue
│  └─ views/ TaskListView · TaskFormView · AboutView · NotFoundView
└─ tests/
   ├─ taskStats.spec.js      13 test
   └─ TaskItem.spec.js        4 test
```

Alamat API dibaca dari environment variable di
`frontend/src/api/client.js`:

```js
const url = import.meta.env.VITE_API_URL

if (!url) {
  throw new Error('VITE_API_URL belum diset. Salin frontend/.env.example ke frontend/.env ...')
}
```

### Pembahasan

Alamat API sengaja tidak ditulis langsung di dalam kode. Kalau alamatnya
tertanam di `client.js`, setiap pindah server berarti harus mengedit kode
sumber dan membangun ulang bundle. Dengan `import.meta.env.VITE_API_URL`, satu
variabel `.env` sudah cukup untuk mengganti target, dan nilainya disisipkan
Vite ke dalam bundle saat build, sehingga tidak ada string URL yang tersimpan
di dalam kode sumber.

Pemisahan tanggung jawab di dalam `src/` dibuat agar mudah diuji. Berkas
`api/client.js` khusus mengurus permintaan HTTP, `composables/useTasks.js`
khusus mengelola status pemuatan dan galat, sedangkan perhitungan seperti
"berapa tugas yang selesai" diletakkan di `utils/taskStats.js` sebagai fungsi
murni. Pemisahan inilah yang membuat langkah 6 dapat dikerjakan: logika yang
diuji tidak menyentuh jaringan maupun Laravel sama sekali.

Variabel yang kosong diperiksa dengan melempar error, bukan diganti dengan
nilai bawaan. Nilai bawaan seperti `http://localhost:8000` membuat kegagalan
sulit dilacak karena halaman hanya diam-diam gagal saat `npm run dev`. Dengan
lempar error, penyebabnya langsung terbaca di terminal.

### Bukti

```
$ cd frontend
$ cp .env.example .env
$ npm install          # 252 paket
$ npm run dev          # http://localhost:5173
```

Bukti bahwa nilainya benar-benar dipakai saat build: menjalankan
`npm run build` dengan `VITE_API_URL=https://api.kepl.example.com/api`
menghasilkan `dist/assets/index-*.js` yang di dalamnya memuat string tersebut.
Build juga tetap berhasil tanpa variabel itu, sehingga pipeline tidak rapuh.

---

## Langkah 3 — Workflow Empat Job Dirangkai dengan `needs:`

### Hasil

`.github/workflows/ci.yml` memuat rantai empat job:

```
frontend-lint ──► frontend-test ──► frontend-build ──► frontend-deploy
   npm ci           npm ci            npm ci              (tanpa npm)
  eslint .      vitest run       vite build        download-artifact
```

Konfigurasi yang dipakai pada tiga job awal:

```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    node-version: '20'
    cache: 'npm'
    cache-dependency-path: frontend/package-lock.json

- name: Install dependencies (npm ci)
  run: npm ci
  working-directory: frontend
```

### Pembahasan

Job pertama tidak diberi `needs:` sehingga bisa berjalan langsung, sedangkan
tiga job berikutnya memakai `needs:`. Akibatnya `frontend-test` baru berjalan
setelah lint hijau, `frontend-build` baru berjalan setelah test hijau, dan
`frontend-deploy` baru berjalan setelah build selesai. Kalau `frontend-lint`
gagal, tiga job berikutnya otomatis berstatus skipped tanpa perlu pemeriksaan
kondisi manual.

`npm ci` dipakai, bukan `npm install`. Perintah `npm ci` memasang dependensi
mengikuti persis `package-lock.json` dan menghapus `node_modules` terlebih
dulu, sehingga hasil build di runner sama dengan hasil build di laptop dan
tidak bergeser karena versi yang ter-resolve berbeda. Karena itu
`frontend/package-lock.json` ikut dikomit.

`cache: 'npm'` membuat `actions/setup-node` menyimpan salinan cache `~/.npm`
untuk dipakai ulang antar-run. `cache-dependency-path` harus menunjuk ke
lockfile di dalam `frontend/`, bukan lockfile root, karena proses `npm ci`
dijalankan dengan `working-directory: frontend`.

---

## Langkah 4 — Menyerahkan Hasil Build ke Job Deploy

### Hasil

`frontend-build` membangun lalu menyerahkan `frontend/dist` sebagai artefak:

```yaml
- name: Hand over dist/ ke job deploy
  uses: actions/upload-artifact@v4
  with:
    name: frontend-dist
    path: frontend/dist
    if-no-files-found: error
```

`frontend-deploy` **tidak menjalankan `npm` sama sekali** — tidak ada
`setup-node`, tidak ada `npm ci`, tidak ada `npm run build`. Ia hanya
mengunduh artefak lalu mencetak isinya ke log:

```yaml
- name: Ambil artefak dist/ hasil build
  uses: actions/download-artifact@v4
  with:
    name: frontend-dist
    path: frontend/dist

- name: Tampilkan isi dist/ ke log
  run: |
    find frontend/dist -type f | sort
    du -ah frontend/dist | sort -h
    cat frontend/dist/index.html
```

### Pembahasan

Setiap job GitHub Actions berjalan pada runner yang bersih dan terpisah,
sehingga berkas yang dihasilkan di `frontend-build` tidak otomatis tersedia
di `frontend-deploy`. `actions/upload-artifact` dan
`actions/download-artifact` itulah yang menjembataninya. alternatifnya adalah
membangun ulang di job deploy, tetapi itu justru yang dilarang oleh soal,
dan ada satu risiko tambahan: kalau dibangun ulang, tidak ada jaminan bahwa
berkas yang dideploy sama persis dengan yang sudah diuji dan lolos di
`frontend-build`.

Pilihan `if-no-files-found: error` dipakai supaya job build gagal kalau
`dist/` ternyata kosong, bukan diam-diam mengunggah artefak kosong yang
diterima job deploy tanpa keberatan.

Job deploy juga menyalin artefak ke `public/frontend` setelah dicetak,
supaya perpindahan artefak benar-benar terlihat pada runner dan bukan
sekadar baris `echo`.

Pemeriksaan bahwa job deploy bersih dari perintah build:

| Perintah | Job yang memakainya |
| --- | --- |
| `npm ci` | `frontend-lint`, `frontend-test`, `frontend-build` |
| `npm run build` | `frontend-build` saja |
| `npm` (apa pun) | `frontend-deploy`: tidak ada |

### Bukti

Keluaran yang diharapkan di log job `frontend-deploy`:

```
--- daftar file ---
frontend/dist/assets/index-xxxx.css
frontend/dist/assets/index-xxxx.js
frontend/dist/index.html

--- frontend/dist/index.html ---
<!doctype html>
<html lang="id"> ...
```

---

## Langkah 5 — Deploy Hanya Jalan dari `main`, Pull Request Tetap Lint/Test/Build

### Hasil

```yaml
frontend-deploy:
  if: github.event_name == 'push' && github.ref == 'refs/heads/main'
```

Job `production` pada pipeline Laravel memakai kondisi yang sama dan
`needs: [staging, frontend-deploy]`.

### Pembahasan

Syarat `github.event_name == 'push'` dipasang bersama syarat branch, bukan
cukup `github.ref == 'refs/heads/main'`. Alasannya, pada event `pull_request`,
`github.ref` berisi referensi seperti `refs/pull/12/merge`, bukan
`refs/heads/main`. Gabungan dua syarat membuat keputusan "ini benar-benar
push ke main" tidak bergantung pada satu nilai saja, sehingga Pull Request
yang menuju `main` tidak mungkin salah terbaca sebagai layak deploy.

Kondisi `if` hanya dipasang pada `frontend-deploy` dan `production`. Job
`frontend-lint`, `frontend-test`, dan `frontend-build` sengaja tidak diberi
kondisi apa pun, sehingga pada Pull Request ketiganya tetap berjalan seperti
biasa. Inilah yang membuat Pull Request tetap menjadi gerbang mutu tanpa
pernah menyentuh server produksi.

`production` memakai `needs: [staging, frontend-deploy]`, sehingga deploy
produksi baru boleh berjalan setelah artefak frontend benar-benar sudah
diterbitkan di runner sebelumnya.

Pengaman tambahan dilakukan lewat GitHub Environment `production` dengan
**Required reviewers** (Settings → Environments → production). Pengaturan itu
memang berada di GitHub dan tidak dapat didefinisikan di dalam YAML.

### Bukti — apa yang terlihat di tab Actions saat Pull Request dibuka

| Job | Status di Pull Request |
| --- | --- |
| Backend / Build | hijau |
| Backend / Test | hijau |
| Frontend 1/4 / Lint | hijau |
| Frontend 2/4 / Test | hijau |
| Frontend 3/4 / Build | hijau |
| Deploy to Staging | hijau |
| **Frontend 4/4 / Deploy** | **skipped** |
| **Deploy to Production** | **skipped** |

---

## Langkah 6 — Unit Test Vitest yang Lulus Tanpa Laravel

### Hasil

Logika aplikasi diletakkan di `frontend/src/utils/taskStats.js` sebagai fungsi
murni: `summarizeTasks(tasks)`, `validateTaskPayload(payload)`, dan
`formatDate(value)`.

Pengujiannya:

| Berkas test | Jumlah test | Cakupan |
| --- | --- | --- |
| `frontend/tests/taskStats.spec.js` | 13 | daftar kosong, input non-array, entri tanpa `completed`, pembulatan persen, judul kosong atau spasi, judul melebihi 255 karakter, pemanggilan tanpa argumen, format tanggal |
| `frontend/tests/TaskItem.spec.js` | 4 | render komponen, perubahan status, event `toggle` dan `remove`, tombol nonaktif |

```
$ npm run test
 Test Files  2 passed (2)
      Tests  17 passed (17)
```

### Pembahasan

Semua yang diuji adalah fungsi yang menerima nilai dan mengembalikan nilai,
tanpa `fetch`, tanpa `import.meta.env`, tanpa akses database, dan tanpa
dependensi framework PHP. Konsekuensinya, `npm run test` di GitHub Actions
hanya membutuhkan Node.js: tidak perlu `composer install`, tidak perlu
`php artisan serve`, dan tidak perlu database. Inilah yang memenuhi syarat
bahwa test harus lulus di GitHub Actions tanpa Laravel berjalan.

Kasus batas diuji, bukan hanya kasus yang sukses. Untuk `summarizeTasks`
diuji daftar kosong, input yang bukan array (`undefined`, `null`, string),
dan entri tanpa properti `completed`. Untuk `validateTaskPayload` diuji judul
kosong, judul berisi spasi saja, dan judul melebihi batas 255 karakter yang
sama dengan aturan validasi di sisi server. Menguji kasus batas di frontend
mencegah pesan kesalahan yang berbeda dari yang dikirim Laravel.

### Bukti bahwa unit test menangkap regresi

Salah satu assertion sengaja diubah agar salah (lihat langkah 7), dan test
langsung gagal dengan menampilkan nilai yang dibandingkan, bukan sekadar
"test failed":

```
FAIL  tests/taskStats.spec.js > summarizeTasks > mengembalikan persen 0 untuk daftar kosong
AssertionError: expected { total: +0, selesai: +0, …(2) } to deeply equal { total: 99, …(2) }
```

---

## Langkah 7 — Bukti: Deploy Skipped saat PR, dan Pipeline Berhenti Merah saat Test Gagal

### Hasil 7a — Pull Request, deploy skipped

1. Branch `feature/vue-frontend-ci` di-push ke origin.
2. Pull Request dibuka ke `main`:
   `https://github.com/KEPL2026/evolusi-pl-24-540567-SV-24877/pull/new/feature/vue-frontend-ci`
3. Di tab **Actions**, job `Frontend 4/4 / Deploy` dan `Deploy to Production`
   berstatus **skipped** sementara `lint`, `test`, dan `build` hijau — sesuai
   tabel pada langkah 5.

### Hasil 7b — Satu test digagalkan, pipeline berhenti merah

Satu assertion pada `frontend/tests/taskStats.spec.js` diubah agar salah, lalu
di-commit sebagai `e7149e9`:

```diff
  it('mengembalikan persen 0 untuk daftar kosong', () => {
-   expect(summarizeTasks([])).toEqual({ total: 0, selesai: 0, belum: 0, persen: 0 })
+   expect(summarizeTasks([])).toEqual({ total: 99, selesai: 0, belum: 0, persen: 0 })
  })
```

Dampaknya:

| Job | Status setelah commit `e7149e9` |
| --- | --- |
| Backend / Build, Backend / Test | hijau |
| Frontend 1/4 / Lint | hijau |
| **Frontend 2/4 / Test** | **MERAH** |
| Frontend 3/4 / Build | skipped |
| Deploy to Staging | skipped |
| Frontend 4/4 / Deploy | skipped |
| Deploy to Production | skipped |

Pipeline berhenti tepat di `frontend-test`. Tidak ada build yang dijalankan,
tidak ada artefak yang dibuat, dan tidak ada deploy yang dieksekusi. Setelah
assertion dikembalikan ke nilai yang benar dan di-push, seluruh job kembali
hijau, dengan `Frontend 4/4 / Deploy` tetap skipped karena masih Pull Request.

### Pembahasan

Skenario ini menguji dua hal sekaligus: bahwa `needs:` benar-benar bekerja,
dan bahwa job deploy tidak akan pernah berjalan selama langkah 2 masih gagal.
Seandainya `frontend-build` ternyata ikut berjalan atau `frontend-deploy`
ternyata hijau, berarti rantai `needs:` dan kondisi `if` pada langkah 3 sampai
5 belum benar.

Commit `e7149e9` sengaja tidak dibatalkan dengan `git revert`, melainkan
diperbaiki dengan mengembalikan nilai assertion. Alasannya, revert akan
menambahkan commit ketiga, sementara perbaikan assertion lebih menunjukkan
bahwa kegagalan itu memang berasal dari test yang salah tulis, bukan dari
kode aplikasi yang rusak.

### Status akhir

Assertion sudah dikembalikan dan di-push, sehingga pipeline menutup dalam
keadaan hijau:

| Job | Status setelah perbaikan |
| --- | --- |
| Backend / Build, Backend / Test | hijau |
| Frontend 1/4 / Lint | hijau |
| Frontend 2/4 / Test | hijau |
| Frontend 3/4 / Build | hijau |
| Deploy to Staging | hijau |
| **Frontend 4/4 / Deploy** | **skipped** (masih Pull Request) |
| **Deploy to Production** | **skipped** (masih Pull Request) |

---

## Ringkasan Hasil Verifikasi

| Perintah | Hasil |
| --- | --- |
| `php artisan route:list --path=api` | 6 route terdaftar |
| `php artisan test` | **16 passed** (40 assertions) |
| `curl -H "Origin: http://localhost:5173" http://localhost:8000/api/tasks` | 200 + header `Access-Control-Allow-Origin` |
| `cd frontend && npm ci` | exit 0, 252 paket |
| `npm run lint` | **0 error** |
| `npm run test` | **17 passed** (2 berkas test) |
| `npm run build` | `dist/index.html`, `dist/assets/index-*.css`, `dist/assets/index-*.js` |
| `npm run test` dengan assertion rusak | exit 1, `Frontend 2/4 / Test` merah |

---

## Catatan

- `database/database.sqlite` tidak dilacak Git (`.gitignore` → `*.sqlite*`).
  Berkas lokal tertinggal dari skema lama (`tasks.is_completed`, bukan
  `completed`) dan tidak cocok dengan migration, sehingga diperbaiki dengan
  `php artisan migrate:fresh`. Test di CI memakai database `:memory:` sesuai
  `phpunit.xml`, sehingga tidak terpengaruh.
- Seluruh perintah pada job `Deploy to Staging` dan `Deploy to Production`
  masih berupa `echo` simulasi. Tidak ada koneksi maupun perubahan pada
  server nyata.
- `ci.yml` versi lama memuat langkah duplikat (setup PHP dua kali,
  `composer install` dua kali, `php artisan test` dua kali per job) dan job
  `vendor` artifact yang tidak berguna. Semua dirapikan dalam satu tulisan
  ulang berkas.
- Berkas `Laporan_Evolusi_PL.tex` dan `P3_540567_Nawwaf_Zayyan_Musyafa.pdf`
  yang sudah terhapus di working tree tidak ikut dikomit karena tidak
  terkait tugas ini.

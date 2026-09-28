# Validasi Praktikum 3 — NIM 540567

> Endpoint JSON `/api/tasks`, aplikasi Vue 3 di `frontend/`, dan rantai
> `lint → test → build → deploy` dijelaskan di
> [`VALIDASI_FRONTEND_CI.md`](VALIDASI_FRONTEND_CI.md).

## Rantai pipeline

`build → test → staging → production`

Job `test` membutuhkan `build`; `staging` membutuhkan `test`; dan `production` membutuhkan `staging`. Semua push, termasuk `feature/*`, memicu workflow. Production menggunakan kondisi `github.event_name == 'push' && github.ref == 'refs/heads/main'`, sehingga pada push branch fitur maupun pada Pull Request job tersebut berstatus **skipped**.

## Pengamanan production

YAML memilih GitHub Environment `production`; required reviewer merupakan pengaturan repository, bukan properti workflow. Setelah workflow dipush, konfigurasi yang wajib dilakukan pada GitHub adalah:

1. **Settings → Environments → New environment**: buat/pilih `production`.
2. Pada **Deployment protection rules**, centang **Required reviewers** dan tambahkan minimal satu reviewer/dosen.
3. Simpan. Push ke `main` kini berhenti menunggu approval sebelum echo production dijalankan.

## Bukti uji lokal

- Dependensi: `composer install --no-interaction --prefer-dist --optimize-autoloader` selesai tanpa perubahan paket.
- Perbaikan akhir: `php artisan test` harus menampilkan seluruh test **PASS**.
- Skenario gagal: ubah sementara assertion pada `ProjectCrudTest` sehingga nama yang dicari salah, lalu jalankan `php artisan test --filter=ProjectCrudTest`; test gagal dan job `test` di GitHub Actions juga gagal. Karena `staging` memiliki `needs: test`, staging dan production tidak dijalankan. Kembalikan assertion yang benar dan jalankan test lagi.

## Deploy simulasi

`deploy.sh` menyimpan tujuh perintah deploy dan memakai `set -e`. Pada workflow, ketujuh perintah yang sama hanya dicetak dengan `echo`, sehingga tidak ada koneksi atau perubahan pada server.

#!/bin/sh
# =====================================================================
#  docker/entrypoint.sh
#
#  Dipanggil setiap kali container dinyalakan. Tanggung jawabnya
#  menyiapkan hal-hal yang TIDAK bisa di-hardcode di dalam image karena
#  bergantung pada environment: file .env, APP_KEY, dan database.
#
#  Semua perintah di bawahnya di-'exec' supaya `php artisan serve`
#  benar-benar menjadi PID 1 di dalam container (bukan anak shell),
#  sehingga SIGTERM dari `docker stop` sampai ke proses PHP.
# =====================================================================
set -e

cd /var/www/html

echo "=============================================="
echo "  Evolusi PL - container startup"
echo "=============================================="

# ── 1. Berkas .env ────────────────────────────────────────────────
# .env masuk .dockerignore, jadi di dalam image tidak ada. Kita buat
# dari .env.example (yang memang ikut ter-copy ke image).
if [ ! -f .env ]; then
    cp .env.example .env
    echo "[1/6] .env dibuat dari .env.example"
else
    echo "[1/6] .env sudah ada, dilewati"
fi

# ── 2. APP_KEY ────────────────────────────────────────────────────
# Dibuat sekali lalu disimpan di .env, jadi session/cookie tetap valid
# selama container hidup walaupun container di-restart.
if ! grep -qE '^APP_KEY=base64:.+' .env; then
    php artisan key:generate --force --ansi
    echo "[2/6] APP_KEY berhasil dibuat"
else
    echo "[2/6] APP_KEY sudah ada, dilewati"
fi

# ── 3. Berkas database SQLite ─────────────────────────────────────
# database/database.sqlite sengaja dikecualikan di .dockerignore, jadi
# database container selalu mulai dari nol lalu diisi migration+seeder.
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    echo "[3/6] database/database.sqlite dibuat"
else
    echo "[3/6] database/database.sqlite sudah ada"
fi

# ── 4. Migration ──────────────────────────────────────────────────
# Dijalankan setiap start (idempoten): yang sudah migrasi dilewati.
# Menghasilkan tabel users, cache, jobs, sessions, tasks, projects.
php artisan migrate --force --no-interaction
echo "[4/6] migration selesai"

# ── 5. Seeder tasks ───────────────────────────────────────────────
# Hanya mengisi kalau tabel tasks masih kosong (dilakukan di dalam
# TaskSeeder), supaya data tetap sama saat container di-restart.
php artisan db:seed --class=TaskSeeder --force --no-interaction
echo "[5/6] TaskSeeder dijalankan"

# ── 6. Cache konfigurasi + view ───────────────────────────────────
# config:cache membuat .env tidak perlu dibaca lagi tiap request.
# route:cache SENGAJA tidak dipakai: routes/web.php memuat route
# closure (Route::get('/', ...)) yang tidak bisa diserialisasi, dan
# `php artisan route:cache` akan gagal dengan closure tersebut.
php artisan config:clear >/dev/null
php artisan config:cache
php artisan view:cache
echo "[6/6] cache konfigurasi & view dibuat"

# Folder storage harus writable oleh user web server.
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache

echo "=============================================="
echo " sia-siap: $(php artisan --version)"
echo "  $*"
echo "=============================================="

exec "$@"

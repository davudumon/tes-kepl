<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Seeder;

/**
 * Mengisi tabel `tasks` dengan data contoh yang JUDULNYA TETAP.
 *
 * Berbeda dengan `Task::factory()` yang memakai `fake()`, judul di sini
 * ditulis manual supaya isi JSON pada `GET /api/tasks` selalu sama dan
 * enak dibaca saat dokumentasi (dipakai pada bukti pengujian Postman).
 *
 * Seeder ini idempoten: kalau tabel sudah berisi data, seeding dilewati.
 * Itu penting karena `docker/entrypoint.sh` memanggilnya setiap kali
 * container dinyalakan.
 */
class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Task::count() > 0) {
            $this->command?->info('Tabel tasks sudah berisi data, seeder dilewati.');

            return;
        }

        foreach ($this->tasks() as $task) {
            Task::create($task);
        }

        $this->command?->info('TaskSeeder selesai.');
    }

    /**
     * Daftar data contoh.
     *
     * @return array<int, array<string, bool|string>>
     */
    protected function tasks(): array
    {
        return [
            [
                'title' => 'Rancang Dockerfile untuk Laravel',
                'description' => 'Susun multi-stage build: composer install sebelum kode aplikasi disalin.',
                'completed' => true,
            ],
            [
                'title' => 'Buat file .dockerignore',
                'description' => 'Kecualikan vendor, node_modules, .git, dan .env dari build context.',
                'completed' => true,
            ],
            [
                'title' => 'Buktikan layer cache Docker bekerja',
                'description' => 'Build tiga kali: cold, tanpa perubahan, dan setelah satu huruf diubah.',
                'completed' => true,
            ],
            [
                'title' => 'Jalankan container dan buka di browser',
                'description' => 'Publish port 8000 lalu akses halaman landing dari host.',
                'completed' => false,
            ],
            [
                'title' => 'Uji endpoint API dari luar container',
                'description' => 'Panggil GET /api/tasks memakai Postman dan pastikan status 200.',
                'completed' => false,
            ],
        ];
    }
}

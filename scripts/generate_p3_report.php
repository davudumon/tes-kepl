<?php

declare(strict_types=1);

// Generates the required submission PDF without external office software.
$output = dirname(__DIR__).'/P3_540567_Nawwaf_Zayyan_Musyafa.pdf';
$pages = [
    [
        'LAPORAN PRAKTIKUM 3',
        'CI/CD Laravel - Empat Tahap',
        '',
        'Nama : Nawwaf Zayyan Musyafa',
        'NIM  : 540567',
        'Repository : evolusi-pl-24-540567-SV-24877',
        '',
        '1. Implementasi aplikasi',
        'Repository menggunakan Laravel 12 dan aplikasi CRUD sederhana',
        'untuk satu tabel projects. Kolomnya adalah name, description,',
        'status, serta timestamps. Route tersedia untuk daftar, formulir,',
        'simpan, dan hapus project.',
        '',
        'Tes feature menggunakan database SQLite in-memory dan RefreshDatabase.',
        'Tes memverifikasi penyimpanan project dan endpoint pembuatan project.',
        '',
        '2. Workflow GitHub Actions',
        'Workflow .github/workflows/ci.yml memiliki rantai:',
        'build -> test -> staging -> production.',
        'Job build memasang dependensi melalui composer install.',
        'Job test membutuhkan build dan menjalankan php artisan test.',
        'Job staging membutuhkan test dan hanya mencetak echo simulasi.',
    ],
    [
        '3. Production dan deploy',
        'Job production membutuhkan staging dan memiliki kondisi:',
        "github.ref == 'refs/heads/main' && github.event_name == 'push'.",
        'Akibatnya push pada feature/* tetap menjalankan build dan test,',
        'sedangkan production berstatus skipped.',
        '',
        'Job memakai environment production. Required reviewer dikonfigurasi',
        'di GitHub: Settings > Environments > production > Required reviewers.',
        'Pengaturan tersebut berada di UI GitHub dan bukan di YAML workflow.',
        '',
        'deploy.sh disimpan dengan set -e. Tujuh langkah yang dicetak pada',
        'production (tanpa menjalankan server) adalah:',
        '1. cd /var/www/evolusi-pl-540567',
        '2. php artisan down',
        '3. git pull origin main',
        '4. composer install --no-dev --optimize-autoloader --no-interaction',
        '5. php artisan migrate --force',
        '6. php artisan optimize',
        '7. php artisan up',
        '',
        '4. Bukti pengujian',
        'composer install selesai dan php artisan test menghasilkan:',
        '5 passed (9 assertions).',
        'Skenario gagal juga dilakukan: assertion nama project diubah sementara',
        'menjadi CI Pipeline BROKEN. php artisan test --filter=ProjectCrudTest',
        'menghasilkan 1 failed, 1 passed. Karena test gagal, needs:test',
        'mencegah staging serta production berjalan. Assertion lalu diperbaiki',
        'dan seluruh test kembali lulus.',
    ],
];

function esc(string $text): string
{
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
}

$objects = [];
$objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
$kids = [];
$objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
$pageObject = 4;
$contentObject = 5;
foreach ($pages as $pageLines) {
    $kids[] = $pageObject.' 0 R';
    $stream = "BT\n/F1 16 Tf\n50 790 Td\n";
    foreach ($pageLines as $index => $line) {
        if ($index > 0) {
            $stream .= "0 -24 Td\n";
        }
        $font = $index === 0 ? 16 : 11;
        $stream .= '/F1 '.$font." Tf\n(".esc($line).") Tj\n";
    }
    $stream .= "ET\n";
    $objects[$pageObject] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentObject." 0 R >>";
    $objects[$contentObject] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream.'endstream';
    $pageObject += 2;
    $contentObject += 2;
}
$objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($pages).' >>';
ksort($objects);
$pdf = "%PDF-1.4\n";
$offsets = [0];
foreach ($objects as $number => $body) {
    $offsets[$number] = strlen($pdf);
    $pdf .= $number." 0 obj\n".$body."\nendobj\n";
}
$xref = strlen($pdf);
$pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
foreach (array_keys($objects) as $number) {
    $pdf .= sprintf('%010d 00000 n ', $offsets[$number])."\n";
}
$pdf .= 'trailer << /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\nstartxref\n".$xref."\n%%EOF\n";
file_put_contents($output, $pdf);
echo "Generated {$output}\n";

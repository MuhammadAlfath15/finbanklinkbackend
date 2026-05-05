<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank; // Pastikan baris ini ada

class BankSeeder extends Seeder
{
    public function run(): void
    {
        Bank::create([
            'nama_bank' => 'Bank BCA',
            'nama_produk' => 'KUR Super Mikro',
            'bunga' => '0.5%',
            'cicilan' => 'Rp. 500.000 / Bulan',
            'skor_kecocokan' => 90,
            'deskripsi' => 'Pinjaman untuk UMKM dengan bunga rendah.'
        ]);

        Bank::create([
            'nama_bank' => 'Bank Mandiri',
            'nama_produk' => 'Kredit Usaha Mikro',
            'bunga' => '0.6%',
            'cicilan' => 'Rp. 750.000 / Bulan',
            'skor_kecocokan' => 85,
            'deskripsi' => 'Solusi modal kerja untuk usaha produktif.'
        ]);
    }
}
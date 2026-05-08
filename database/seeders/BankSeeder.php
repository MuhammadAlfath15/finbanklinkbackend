<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Bank::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        Bank::create([
            'nama_bank'     => 'Bank BCA',
            'nama_produk'   => 'KUR Super Mikro',
            'bunga'         => '0.5%',
            'cicilan'       => 'Rp. 500.000 / Bulan',
            'skor_kecocokan'=> 90,
            'deskripsi'     => 'Pinjaman untuk UMKM dengan bunga rendah.',
            'plafon_min'    => 1000000,
            'plafon_max'    => 50000000,
            'tenor_min'     => 6,
            'tenor_max'     => 36,
            'bunga_persen'  => 0.5,
            'syarat'        => [
                'Usaha telah berjalan minimal 6 bulan.',
                'Fotokopi KTP & NIB.',
                'Tidak sedang memiliki kredit produktif lain.',
                'Surat keterangan usaha dari kelurahan.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank Mandiri',
            'nama_produk'   => 'Kredit Usaha Mikro',
            'bunga'         => '0.6%',
            'cicilan'       => 'Rp. 750.000 / Bulan',
            'skor_kecocokan'=> 85,
            'deskripsi'     => 'Solusi modal kerja untuk usaha produktif.',
            'plafon_min'    => 1000000,
            'plafon_max'    => 50000000,
            'tenor_min'     => 6,
            'tenor_max'     => 36,
            'bunga_persen'  => 0.5,
            'syarat'        => [
                'Usaha telah berjalan minimal 6 bulan.',
                'Fotokopi KTP & NIB.',
                'Tidak sedang memiliki kredit produktif lain.',
                'Surat keterangan usaha dari kelurahan.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank BRI',
            'nama_produk'   => 'KUR Mikro BRI',
            'bunga'         => '6% per tahun',
            'cicilan'       => 'Rp. 1.750.000 / Bulan',
            'skor_kecocokan'=> 92,
            'deskripsi'     => 'Kredit Usaha Rakyat Mikro dengan bunga rendah dan proses cepat.',
            'plafon_min'    => 1000000,
            'plafon_max'    => 50000000,
            'tenor_min'     => 6,
            'tenor_max'     => 36,
            'bunga_persen'  => 0.5,
            'syarat'        => [
                'Usaha telah berjalan minimal 6 bulan.',
                'Fotokopi KTP, KK, dan NIB/SIUP.',
                'Tidak sedang memiliki kredit macet.',
                'Fotokopi rekening koran 3 bulan terakhir.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank BNI',
            'nama_produk'   => 'BNI Wirausaha',
            'bunga'         => '7% per tahun',
            'cicilan'       => 'Rp. 2.100.000 / Bulan',
            'skor_kecocokan'=> 85,
            'deskripsi'     => 'Pinjaman modal usaha untuk UMKM dengan tenor fleksibel hingga 5 tahun.',
            'plafon_min'    => 5000000,
            'plafon_max'    => 100000000,
            'tenor_min'     => 12,
            'tenor_max'     => 60,
            'bunga_persen'  => 0.58,
            'syarat'        => [
                'Usaha telah berjalan minimal 1 tahun.',
                'Fotokopi KTP dan NPWP.',
                'Laporan keuangan sederhana 6 bulan terakhir.',
                'Tidak memiliki tunggakan di bank lain.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank BTN',
            'nama_produk'   => 'BTN Modal Usaha',
            'bunga'         => '8% per tahun',
            'cicilan'       => 'Rp. 2.200.000 / Bulan',
            'skor_kecocokan'=> 79,
            'deskripsi'     => 'Kredit modal usaha untuk pengembangan bisnis skala menengah.',
            'plafon_min'    => 5000000,
            'plafon_max'    => 150000000,
            'tenor_min'     => 12,
            'tenor_max'     => 60,
            'bunga_persen'  => 0.67,
            'syarat'        => [
                'Usaha telah berjalan minimal 1 tahun.',
                'Fotokopi KTP, KK, dan NPWP.',
                'Bukti kepemilikan usaha (NIB/SIUP/TDP).',
                'Rekening koran 3 bulan terakhir.',
            ],
        ]);
    }
}
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
            'min_score'     => 450,
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
            'min_score'     => 350,
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
            'min_score'     => 300,
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
            'min_score'     => 320,
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
            'min_score'     => 380,
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

        Bank::create([
            'nama_bank'     => 'Bank CIMB Niaga',
            'nama_produk'   => 'Xtra Dana Bisnis',
            'bunga'         => '0.8% / Bln',
            'cicilan'       => 'Rp. 1.850.000 / Bln',
            'skor_kecocokan'=> 88,
            'deskripsi'     => 'Fasilitas pinjaman tanpa agunan untuk kebutuhan modal kerja dan pengembangan usaha.',
            'plafon_min'    => 10000000,
            'plafon_max'    => 200000000,
            'tenor_min'     => 12,
            'tenor_max'     => 60,
            'bunga_persen'  => 0.8,
            'syarat'        => [
                'Usaha telah berjalan minimal 2 tahun.',
                'Fotokopi KTP, NPWP, dan SIUP/TDP.',
                'Rekening koran 6 bulan terakhir.',
                'Memiliki riwayat kredit yang baik (BI Checking/SLIK bersih).',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank Danamon',
            'nama_produk'   => 'Kredit Modal Kerja',
            'bunga'         => '9% per tahun',
            'cicilan'       => 'Rp. 2.450.000 / Bln',
            'skor_kecocokan'=> 82,
            'min_score'     => 370,
            'deskripsi'     => 'Dukungan finansial fleksibel untuk melancarkan arus kas harian bisnis Anda.',
            'plafon_min'    => 50000000,
            'plafon_max'    => 500000000,
            'tenor_min'     => 12,
            'tenor_max'     => 48,
            'bunga_persen'  => 0.75,
            'syarat'        => [
                'Usaha minimal 2 tahun di bidang yang sama.',
                'Legalitas usaha lengkap (Akta Pendirian, SIUP, NIB).',
                'KTP pengurus perusahaan.',
                'Laporan keuangan auditan (opsional untuk plafon besar).',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank Mega',
            'nama_produk'   => 'Mega Usaha',
            'bunga'         => '1.1% / Bln',
            'cicilan'       => 'Rp. 1.250.000 / Bln',
            'skor_kecocokan'=> 76,
            'min_score'     => 360,
            'deskripsi'     => 'Kredit bagi pengusaha ritel dengan proses cepat dan syarat mudah.',
            'plafon_min'    => 5000000,
            'plafon_max'    => 100000000,
            'tenor_min'     => 6,
            'tenor_max'     => 36,
            'bunga_persen'  => 1.1,
            'syarat'        => [
                'Usaha aktif minimal 1 tahun.',
                'KTP, KK, dan surat keterangan domisili usaha.',
                'Tidak masuk daftar hitam BI.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank OCBC NISP',
            'nama_produk'   => 'KTA Bisnis',
            'bunga'         => '0.99% / Bln',
            'cicilan'       => 'Rp. 1.500.000 / Bln',
            'skor_kecocokan'=> 89,
            'min_score'     => 400,
            'deskripsi'     => 'Pinjaman tanpa jaminan dengan persetujuan cepat untuk digital native bisnis.',
            'plafon_min'    => 10000000,
            'plafon_max'    => 200000000,
            'tenor_min'     => 6,
            'tenor_max'     => 36,
            'bunga_persen'  => 0.99,
            'syarat'        => [
                'Memiliki rekening bisnis aktif selama 6 bulan.',
                'Transaksi mutasi rekening minimal Rp 50 juta/bulan.',
                'KTP & NPWP aktif.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank Panin',
            'nama_produk'   => 'Kredit Mikro Panin',
            'bunga'         => '0.85% / Bln',
            'cicilan'       => 'Rp. 950.000 / Bln',
            'skor_kecocokan'=> 81,
            'min_score'     => 410,
            'deskripsi'     => 'Solusi kredit mikro untuk pedagang pasar, toko kelontong, dan usaha kecil lainnya.',
            'plafon_min'    => 2000000,
            'plafon_max'    => 50000000,
            'tenor_min'     => 6,
            'tenor_max'     => 24,
            'bunga_persen'  => 0.85,
            'syarat'        => [
                'Usaha telah berjalan 1 tahun di lokasi yang sama.',
                'KTP pemohon dan pasangan (jika menikah).',
                'Surat Keterangan Usaha (SKU) dari kelurahan/desa.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank BSI',
            'nama_produk'   => 'BSI KUR Syariah',
            'bunga'         => 'Margin Setara 6% / Thn',
            'cicilan'       => 'Rp. 1.750.000 / Bln',
            'skor_kecocokan'=> 95,
            'min_score'     => 330,
            'deskripsi'     => 'Pembiayaan modal kerja syariah dengan akad Murabahah atau Musyarakah.',
            'plafon_min'    => 5000000,
            'plafon_max'    => 50000000,
            'tenor_min'     => 12,
            'tenor_max'     => 48,
            'bunga_persen'  => 0.5,
            'syarat'        => [
                'Usaha berjalan minimal 6 bulan.',
                'KTP, KK, dan Surat Keterangan Usaha.',
                'Sesuai dengan prinsip syariah.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank Muamalat',
            'nama_produk'   => 'Pembiayaan Modal Kerja Syariah',
            'bunga'         => 'Nisbah Bagi Hasil',
            'cicilan'       => 'Rp. 2.100.000 / Bln',
            'skor_kecocokan'=> 84,
            'min_score'     => 340,
            'deskripsi'     => 'Solusi pembiayaan untuk UMKM yang sesuai dengan prinsip syariah tanpa riba.',
            'plafon_min'    => 20000000,
            'plafon_max'    => 200000000,
            'tenor_min'     => 12,
            'tenor_max'     => 60,
            'bunga_persen'  => 0.65,
            'syarat'        => [
                'Usaha telah berjalan minimal 2 tahun.',
                'Fotokopi KTP, KK, dan legalitas usaha.',
                'Laporan keuangan 6 bulan terakhir.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'Bank Mega Syariah',
            'nama_produk'   => 'Pembiayaan Mikro Syariah',
            'bunga'         => 'Margin Setara 0.9% / Bln',
            'cicilan'       => 'Rp. 1.150.000 / Bln',
            'skor_kecocokan'=> 78,
            'min_score'     => 350,
            'deskripsi'     => 'Kredit produktif bagi pedagang dan pengusaha mikro berbasis syariah.',
            'plafon_min'    => 5000000,
            'plafon_max'    => 100000000,
            'tenor_min'     => 6,
            'tenor_max'     => 36,
            'bunga_persen'  => 0.9,
            'syarat'        => [
                'Usaha di sektor halal.',
                'KTP dan surat keterangan domisili usaha.',
                'Tidak masuk daftar hitam Bank Indonesia.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'BCA Syariah',
            'nama_produk'   => 'Pembiayaan UMKM BCA Syariah',
            'bunga'         => 'Margin Kompetitif',
            'cicilan'       => 'Rp. 2.500.000 / Bln',
            'skor_kecocokan'=> 86,
            'min_score'     => 440,
            'deskripsi'     => 'Fasilitas pembiayaan investasi dan modal kerja syariah untuk ekspansi bisnis Anda.',
            'plafon_min'    => 50000000,
            'plafon_max'    => 500000000,
            'tenor_min'     => 12,
            'tenor_max'     => 60,
            'bunga_persen'  => 0.75,
            'syarat'        => [
                'Usaha telah berjalan 2 tahun.',
                'Legalitas usaha (Akta, NIB, NPWP).',
                'Rekening koran BCA/BCA Syariah 6 bulan terakhir.',
            ],
        ]);

        Bank::create([
            'nama_bank'     => 'BTPN Syariah',
            'nama_produk'   => 'Tepat Pembiayaan Syariah',
            'bunga'         => 'Margin Setara 1.2% / Bln',
            'cicilan'       => 'Rp. 850.000 / Bln',
            'skor_kecocokan'=> 88,
            'deskripsi'     => 'Pembiayaan kelompok untuk perempuan pelaku usaha mikro tanpa agunan.',
            'plafon_min'    => 1500000,
            'plafon_max'    => 25000000,
            'tenor_min'     => 6,
            'tenor_max'     => 24,
            'bunga_persen'  => 1.2,
            'syarat'        => [
                'Perempuan pelaku usaha mikro.',
                'Bersedia membentuk kelompok.',
                'KTP dan persetujuan suami/keluarga.',
            ],
        ]);
    }
}
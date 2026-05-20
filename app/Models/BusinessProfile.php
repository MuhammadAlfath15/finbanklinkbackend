<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nama_usaha',
        'bidang_usaha',
        'alamat_usaha',
        'lama_usaha',
        'jumlah_karyawan',
        // legalitas
        'nib_path',
        'npwp_path',
        'ktp_path',
        'kk_path',
        'selfie_ktp_path',
        'ttd_path',
        // keuangan
        'rekening_path',
        'omzet_bulan_ini',
        // operasional
        'foto_usaha_path',
        'kontrak_path',
        // kapasitas utang
        'cicilan_berjalan',
        'bukti_pelunasan_path',
        // skor
        'skor_profitabilitas',
        'skor_legalitas',
        'skor_tren_omzet',
        'skor_kolektibilitas',
        'skor_keberlanjutan',
        'skor_kapasitas_utang',
        'skor_total',
    ];

    protected $casts = [
        'omzet_bulan_ini'   => 'decimal:2',
        'cicilan_berjalan'  => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'reference_code',
        'user_id',
        'bank_id',
        'nama_produk',
        'status',
        'nominal_pinjaman',
        'tenor',
        'nama_usaha',
        'bidang_usaha',
        'alamat_usaha',
        'pemohon_phone',
        'pemohon_alamat',
        'cicilan_per_bulan',
        'ktp_nik',
        'ktp_nama',
        'ktp_upload_path',
        'nib_upload_path',
        'skor_total',
        'skor_profitabilitas',
        'skor_legalitas',
        'skor_tren_omzet',
        'skor_kolektibilitas',
        'skor_keberlanjutan',
        'skor_kapasitas_utang',
        'omzet_year',
        'omzet_data',
        'bank_message',
        'user_message',
    ];

    protected $casts = [
        'cicilan_per_bulan' => 'decimal:2',
        'omzet_data'        => 'array',
        'omzet_year'        => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}

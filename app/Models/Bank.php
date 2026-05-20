<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'nama_bank', 'category', 'category_id', 'nama_produk', 'bunga', 'cicilan',
        'skor_kecocokan', 'min_score', 'deskripsi',
        'plafon_min', 'plafon_max', 'tenor_min', 'tenor_max',
        'bunga_persen', 'syarat',
    ];

    protected $casts = [
        'syarat' => 'array',
    ];

    public function categoryRef()
    {
        return $this->belongsTo(BankCategory::class, 'category_id');
    }
}
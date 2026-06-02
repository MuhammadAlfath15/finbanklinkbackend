<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = [
        'nama_bank', 'category', 'category_id', 'nama_produk', 'bunga', 'cicilan',
        'skor_kecocokan', 'min_score', 'deskripsi',
        'plafon_min', 'plafon_max', 'tenor_min', 'tenor_max',
        'bunga_persen', 'syarat', 'is_promoted', 'promo_image',
    ];

    protected $casts = [
        'syarat' => 'array',
        'is_promoted' => 'boolean',
    ];

    public function categoryRef()
    {
        return $this->belongsTo(BankCategory::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(BankCategory::class, 'bank_category_pivot', 'bank_id', 'category_id')->withTimestamps();
    }
}
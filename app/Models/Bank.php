<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    // Tambahkan baris ini agar data bisa masuk
    protected $fillable = ['nama_bank', 'nama_produk', 'bunga', 'cicilan', 'skor_kecocokan', 'deskripsi'];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function banks()
    {
        return $this->belongsToMany(Bank::class, 'bank_category_pivot', 'category_id', 'bank_id')->withTimestamps();
    }
}

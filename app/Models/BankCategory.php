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
        return $this->hasMany(Bank::class, 'category_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        'badge',
        'title',
        'description',
        'cta',
        'image_url',
        'is_active',
        'sort_order',
        'bg_color_from',
        'bg_color_to',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];
}

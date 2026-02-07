<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'cta_text',
        'cta_link',
        'image_path',
        'sort_order',
        'is_active',
    ];
}

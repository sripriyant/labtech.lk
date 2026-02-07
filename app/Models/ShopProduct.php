<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'category_id',
        'description',
        'price',
        'image_path',
        'is_active',
        'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(ShopCategory::class);
    }
}

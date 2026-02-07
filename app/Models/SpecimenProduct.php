<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class SpecimenProduct extends Model
{
    use BelongsToLab;

    protected $fillable = [
        'specimen_id',
        'lab_id',
        'shop_product_id',
        'name',
        'price',
        'quantity',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function specimen()
    {
        return $this->belongsTo(Specimen::class);
    }

    public function product()
    {
        return $this->belongsTo(ShopProduct::class, 'shop_product_id');
    }
}

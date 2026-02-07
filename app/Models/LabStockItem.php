<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class LabStockItem extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'code',
        'name',
        'description',
        'reorder_level',
        'reorder_qty',
        'unit',
        'unit_price',
        'is_active',
    ];

    protected $casts = [
        'reorder_level' => 'integer',
        'reorder_qty' => 'integer',
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function batches()
    {
        return $this->hasMany(LabStockBatch::class);
    }
}

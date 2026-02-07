<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class LabStockConsumption extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'lab_stock_item_id',
        'test_master_id',
        'specimen_test_id',
        'quantity',
        'consumed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'consumed_at' => 'datetime',
    ];
}

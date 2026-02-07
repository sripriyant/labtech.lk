<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class TestStockConsumption extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'test_master_id',
        'lab_stock_item_id',
        'quantity_per_test',
    ];

    protected $casts = [
        'quantity_per_test' => 'decimal:2',
    ];

    public function test()
    {
        return $this->belongsTo(TestMaster::class, 'test_master_id');
    }

    public function item()
    {
        return $this->belongsTo(LabStockItem::class, 'lab_stock_item_id');
    }
}

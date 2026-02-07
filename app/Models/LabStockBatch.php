<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class LabStockBatch extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'lab_stock_item_id',
        'supplier_id',
        'quantity',
        'remaining_qty',
        'purchase_date',
        'expiry_date',
        'unit_cost',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'remaining_qty' => 'integer',
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'unit_cost' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(LabStockItem::class, 'lab_stock_item_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}

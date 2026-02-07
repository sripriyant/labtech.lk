<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Payment extends Model
{
    use BelongsToLab;
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'method',
        'amount',
        'reference',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}

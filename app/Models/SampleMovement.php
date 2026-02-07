<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class SampleMovement extends Model
{
    use BelongsToLab;
    public $timestamps = false;

    protected $fillable = [
        'specimen_id',
        'from_status',
        'to_status',
        'department_id',
        'note',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Patient extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'uhid',
        'name',
        'nic',
        'dob',
        'sex',
        'phone',
        'email',
        'nationality',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'dob' => 'date',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Department extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];
}

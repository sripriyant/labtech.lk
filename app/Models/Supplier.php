<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Supplier extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'company_name',
        'contact_name',
        'address',
        'phone',
        'email',
        'remarks',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

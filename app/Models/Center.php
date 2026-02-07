<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Center extends Model
{
    use BelongsToLab;

    protected $fillable = [
        'code',
        'name',
        'address',
        'contact_phone',
        'contact_email',
        'referral_discount_pct',
        'parent_center_id',
        'is_active',
    ];
}

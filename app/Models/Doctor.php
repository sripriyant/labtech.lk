<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Doctor extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'name',
        'registration_no',
        'specialty',
        'referral_discount_pct',
        'can_approve',
        'is_active',
    ];
}

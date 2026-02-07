<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Invoice extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'invoice_no',
        'patient_id',
        'center_id',
        'referral_type',
        'referral_id',
        'total',
        'discount',
        'referral_discount',
        'vat',
        'net_total',
        'payment_status',
        'created_by',
        'updated_by',
    ];
}

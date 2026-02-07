<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Approval extends Model
{
    use BelongsToLab;
    public $timestamps = false;

    protected $fillable = [
        'specimen_test_id',
        'approved_by',
        'approved_at',
        'signature_path',
        'status',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function specimenTest()
    {
        return $this->belongsTo(SpecimenTest::class);
    }
}

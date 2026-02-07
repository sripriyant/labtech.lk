<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class TestResult extends Model
{
    use BelongsToLab;
    public $timestamps = false;

    protected $fillable = [
        'specimen_test_id',
        'result_value',
        'unit',
        'reference_range',
        'flag',
        'entered_by',
        'entered_at',
    ];

    protected $casts = [
        'entered_at' => 'datetime',
    ];

    public function specimenTest()
    {
        return $this->belongsTo(SpecimenTest::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class TestParameterResult extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'specimen_test_id',
        'test_parameter_id',
        'result_value',
        'unit',
        'reference_range',
        'flag',
        'remarks',
        'image_path',
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

    public function parameter()
    {
        return $this->belongsTo(TestParameter::class, 'test_parameter_id');
    }
}

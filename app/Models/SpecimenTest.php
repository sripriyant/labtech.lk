<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class SpecimenTest extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'specimen_id',
        'test_master_id',
        'price',
        'status',
        'is_repeated',
        'is_confirmed',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_repeated' => 'boolean',
        'is_confirmed' => 'boolean',
    ];

    public function specimen()
    {
        return $this->belongsTo(Specimen::class);
    }

    public function testMaster()
    {
        return $this->belongsTo(TestMaster::class);
    }

    public function result()
    {
        return $this->hasOne(TestResult::class);
    }

    public function parameterResults()
    {
        return $this->hasMany(TestParameterResult::class);
    }
}

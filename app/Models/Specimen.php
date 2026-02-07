<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToLab;

class Specimen extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'specimen_no',
        'patient_id',
        'age_years',
        'age_months',
        'age_days',
        'age_unit',
        'center_id',
        'invoice_id',
        'collected_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'age_years' => 'integer',
        'age_months' => 'integer',
        'age_days' => 'integer',
    ];

    public function getAgeDisplayAttribute(): ?string
    {
        $unit = $this->age_unit ?: 'Y';
        if ($unit === 'Y') {
            return $this->age_years !== null ? (string) $this->age_years : null;
        }
        if ($unit === 'M') {
            return $this->age_months !== null ? ($this->age_months . ' M') : null;
        }
        if ($unit === 'D') {
            return $this->age_days !== null ? ($this->age_days . ' D') : null;
        }
        if ($unit === 'MD') {
            $parts = [];
            if ($this->age_months !== null) {
                $parts[] = $this->age_months . ' M';
            }
            if ($this->age_days !== null) {
                $parts[] = $this->age_days . ' D';
            }
            return $parts ? implode(' ', $parts) : null;
        }
        return $this->age_years !== null ? (string) $this->age_years : null;
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function tests()
    {
        return $this->hasMany(SpecimenTest::class);
    }

    public function products()
    {
        return $this->hasMany(SpecimenProduct::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}

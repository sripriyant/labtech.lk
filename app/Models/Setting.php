<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'lab_id',
    ];

    public static function valuesForLab(int $labId = 0): array
    {
        $query = self::query();
        if (!Schema::hasColumn('settings', 'lab_id')) {
            return $query->pluck('value', 'key')->all();
        }

        $global = self::query()
            ->whereNull('lab_id')
            ->pluck('value', 'key')
            ->toArray();

        $lab = [];
        if ($labId > 0) {
            $lab = self::query()
                ->where('lab_id', $labId)
                ->pluck('value', 'key')
                ->toArray();
        }

        return array_replace($global, $lab);
    }
}

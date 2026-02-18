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
            return $query
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('key')
                ->map(function ($group) {
                    return $group->first()->value;
                })
                ->toArray();
        }

        $global = self::query()
            ->whereNull('lab_id')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('key')
            ->map(function ($group) {
                return $group->first()->value;
            })
            ->toArray();

        $lab = [];
        if ($labId > 0) {
            $lab = self::query()
                ->where('lab_id', $labId)
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('key')
                ->map(function ($group) {
                    return $group->first()->value;
                })
                ->toArray();
        }

        return array_replace($global, $lab);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Traits\BelongsToLab;

class PromoCode extends Model
{
    use BelongsToLab;
    protected $fillable = [
        'code',
        'type',
        'value',
        'starts_at',
        'ends_at',
        'is_active',
        'max_uses',
        'usage_count',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->where('is_active', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today);
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('usage_count', '<', 'max_uses');
            });
    }
}

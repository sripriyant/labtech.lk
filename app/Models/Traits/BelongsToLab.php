<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToLab
{
    protected static function bootBelongsToLab(): void
    {
        static::creating(function ($model) {
            if (!auth()->check()) {
                return;
            }
            if (!empty($model->lab_id)) {
                return;
            }
            $user = auth()->user();
            if ($user && method_exists($user, 'isSuperAdmin') && !$user->isSuperAdmin()) {
                $model->lab_id = $user->lab_id;
            }
        });

        static::addGlobalScope('lab', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }
            $user = auth()->user();
            if ($user && method_exists($user, 'isSuperAdmin') && !$user->isSuperAdmin()) {
                $table = $builder->getModel()->getTable();
                $builder->where($table . '.lab_id', $user->lab_id);
            }
        });
    }
}

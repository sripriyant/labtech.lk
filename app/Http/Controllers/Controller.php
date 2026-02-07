<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

abstract class Controller
{
    protected function requirePermission(string $permission): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        if (!Schema::hasTable('permissions') || Permission::query()->count() === 0) {
            return;
        }

        if (Schema::hasTable('permission_user') && Schema::hasTable('role_user')) {
            $hasAssignments = DB::table('permission_user')->exists() || DB::table('role_user')->exists();
            if (!$hasAssignments) {
                return;
            }
        }

        if (!$user->hasPermission($permission)) {
            abort(403);
        }
    }
}

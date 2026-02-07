<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserManagementController extends Controller
{
    private array $assignablePermissions = [
        'admin.dashboard',
        'banners.manage',
        'billing.access',
        'billing.create',
        'centers.manage',
        'departments.manage',
        'doctors.manage',
        'results.approve',
        'results.edit',
        'results.entry',
        'results.validate',
        'tests.manage',
    ];

    private function isSuperAdmin(User $user): bool
    {
        return $user->roles()->where('name', 'Super Admin')->exists();
    }

    private function filterAssignablePermissions(?User $currentUser, array $permissionIds): array
    {
        if (!$currentUser || $this->isSuperAdmin($currentUser)) {
            return $permissionIds;
        }

        $allowedIds = Permission::query()
            ->whereIn('name', $this->assignablePermissions)
            ->pluck('id')
            ->all();

        return array_values(array_intersect($permissionIds, $allowedIds));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
        if (Schema::hasTable('labs')) {
            $rules['lab_id'] = ['nullable', 'integer', 'exists:labs,id'];
            $rules['lab_name'] = ['nullable', 'string', 'max:255'];
            $rules['lab_code_prefix'] = ['nullable', 'string', 'max:10'];
        }

        $data = $request->validate($rules);

        $currentUser = $request->user();
        $isSuperAdmin = $currentUser ? $this->isSuperAdmin($currentUser) : false;
        if (!$isSuperAdmin && !empty($data['roles'])) {
            $restrictedRoles = Role::query()
                ->whereIn('name', ['Super Admin', 'Admin'])
                ->pluck('id')
                ->all();
            if (!empty($restrictedRoles)) {
                $data['roles'] = array_values(array_diff($data['roles'], $restrictedRoles));
            }
        }

        $roles = $data['roles'] ?? [];
        $adminRoleId = Role::query()->where('name', 'Admin')->value('id');
        if (empty($roles) && $isSuperAdmin && $adminRoleId) {
            $roles = [$adminRoleId];
        }

        if ($isSuperAdmin && $adminRoleId && in_array($adminRoleId, $roles, true)) {
            if (Schema::hasTable('labs') && empty($data['lab_id']) && empty($data['lab_name'])) {
                return back()
                    ->withErrors(['lab_id' => 'Select or create a lab for admin accounts.'])
                    ->withInput();
            }
        }

        $labId = $currentUser?->lab_id;
        if ($isSuperAdmin && Schema::hasTable('labs')) {
            if (!empty($data['lab_id'])) {
                $labId = (int) $data['lab_id'];
            } elseif (!empty($data['lab_name'])) {
                $lab = \App\Models\Lab::create([
                    'name' => $data['lab_name'],
                    'code_prefix' => $data['lab_code_prefix'] ?? null,
                    'is_active' => true,
                ]);
                $lab->preloadTestsFromDefault();
                $lab->preloadDoctorsFromDefault();
                $lab->preloadCentersFromDefault();
                $lab->preloadReportSettingsFromDefault();
                $labId = $lab->id;
            }
        }

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];
        if (Schema::hasColumn('users', 'lab_id')) {
            $userData['lab_id'] = $labId;
        }
        if (Schema::hasColumn('users', 'created_by')) {
            $userData['created_by'] = $currentUser?->id;
        }

        $user = User::create($userData);

        if (!empty($roles)) {
            $user->roles()->sync($roles);
        }

        $permissionIds = $data['permissions'] ?? [];
        if (empty($permissionIds)) {
            $defaultPermissionIds = [];
            $defaultSettingQuery = Setting::query()->where('key', 'user_default_permissions');
            if (Schema::hasColumn('settings', 'lab_id')) {
                if ($currentUser && !$isSuperAdmin) {
                    $defaultSettingQuery->where('lab_id', $currentUser->lab_id);
                } else {
                    $defaultSettingQuery->whereNull('lab_id');
                }
            }
            $defaultSetting = $defaultSettingQuery->value('value');
            if (!empty($defaultSetting)) {
                $defaultPermissionIds = array_filter(array_map('trim', explode(',', $defaultSetting)));
            } else {
                $defaultNames = [
                    'admin.dashboard',
                    'banners.manage',
                    'billing.access',
                    'billing.create',
                    'centers.manage',
                    'departments.manage',
                    'doctors.manage',
                    'results.approve',
                    'results.edit',
                    'results.entry',
                    'results.validate',
                    'tests.manage',
                ];
                $defaultPermissionIds = Permission::query()
                    ->whereIn('name', $defaultNames)
                    ->pluck('id')
                    ->map(fn ($id) => (string) $id)
                    ->all();
            }
            $permissionIds = array_map('intval', $defaultPermissionIds);
        }

        $permissionIds = $this->filterAssignablePermissions($currentUser, $permissionIds);
        $user->permissions()->sync($permissionIds);

        return redirect()->route('settings.index');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $currentUser = $request->user();
        $isSuperAdmin = $currentUser ? $this->isSuperAdmin($currentUser) : false;
        if (!$isSuperAdmin) {
            if (Schema::hasColumn('users', 'created_by')) {
                if ($user->created_by !== $currentUser?->id && $user->id !== $currentUser?->id) {
                    abort(403);
                }
            } else {
                abort(403);
            }
        }
        if ($user->roles()->whereIn('name', ['Super Admin', 'Admin'])->exists()) {
            if (!$isSuperAdmin || $user->id === $currentUser?->id) {
                abort(403);
            }
        }

        $data = $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'password' => ['nullable', 'string', 'min:6'],
            'lab_id' => ['nullable', 'integer', 'exists:labs,id'],
        ]);

        if (!empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
        }

        if ($isSuperAdmin && array_key_exists('lab_id', $data) && Schema::hasColumn('users', 'lab_id')) {
            $user->update([
                'lab_id' => $data['lab_id'] ?: null,
            ]);
        }

        if (array_key_exists('roles', $data)) {
            $roles = $data['roles'] ?? [];
            if (!$isSuperAdmin) {
                $restrictedRoles = Role::query()
                    ->whereIn('name', ['Super Admin', 'Admin'])
                    ->pluck('id')
                    ->all();
                if (!empty($restrictedRoles)) {
                    $roles = array_values(array_diff($roles, $restrictedRoles));
                }
            }
            $user->roles()->sync($roles);
        }

        $permissionIds = $data['permissions'] ?? [];
        $permissionIds = $this->filterAssignablePermissions($currentUser, $permissionIds);
        $user->permissions()->sync($permissionIds);

        return redirect()->route('settings.index');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $currentUser = $request->user();
        $isSuperAdmin = $currentUser ? $this->isSuperAdmin($currentUser) : false;

        if ($user->id === $currentUser?->id) {
            abort(403);
        }

        if (!$isSuperAdmin) {
            if (Schema::hasColumn('users', 'created_by')) {
                if ($user->created_by !== $currentUser?->id) {
                    abort(403);
                }
            } else {
                abort(403);
            }
            if ($user->roles()->whereIn('name', ['Super Admin', 'Admin'])->exists()) {
                abort(403);
            }
        }

        $user->roles()->detach();
        $user->permissions()->detach();
        $user->delete();

        return redirect()->route('settings.index');
    }
}

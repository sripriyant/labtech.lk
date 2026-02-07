<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LabManagementController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('admin.dashboard');

        $user = request()->user();
        if (!$user) {
            abort(403);
        }

        $labsQuery = Lab::query()
            ->with(['users.roles'])
            ->orderBy('name');

        if (!$user->isSuperAdmin()) {
            if ($user->lab_id) {
                $labsQuery->where('id', $user->lab_id);
            } else {
                $labsQuery->whereRaw('1 = 0');
            }
        }

        $labs = $labsQuery->get();

        return view('admin.labs.index', [
            'labs' => $labs,
            'isSuperAdmin' => $user->isSuperAdmin(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $user = $request->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'lab_name' => ['required', 'string', 'max:255', 'unique:labs,name'],
            'lab_code_prefix' => ['nullable', 'string', 'max:10', 'unique:labs,code_prefix'],
            'lab_active' => ['nullable', 'boolean'],
            'lab_sms_enabled' => ['nullable', 'boolean'],
            'assign_to_me' => ['nullable', 'boolean'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:6'],
        ]);

        $codePrefix = trim((string) ($data['lab_code_prefix'] ?? ''));
        if ($codePrefix !== '') {
            $codePrefix = strtoupper($codePrefix);
        }

        $lab = Lab::create([
            'name' => $data['lab_name'],
            'code_prefix' => $codePrefix !== '' ? $codePrefix : null,
            'is_active' => !empty($data['lab_active']),
            'sms_enabled' => !empty($data['lab_sms_enabled']),
        ]);
        $lab->preloadTestsFromDefault();
        $lab->preloadDoctorsFromDefault();
        $lab->preloadCentersFromDefault();
        $lab->preloadReportSettingsFromDefault();

        Setting::updateOrCreate(
            ['lab_id' => $lab->id, 'key' => 'sms_enabled'],
            ['value' => $lab->sms_enabled ? '1' : '0']
        );

        $admin = User::create([
            'name' => $data['admin_name'],
            'email' => $data['admin_email'],
            'password' => Hash::make($data['admin_password']),
            'is_active' => true,
            'created_by' => $user?->id,
            'lab_id' => $lab->id,
        ]);

        $adminRoleId = Role::query()->where('name', 'Admin')->value('id');
        if ($adminRoleId) {
            $admin->roles()->sync([$adminRoleId]);
        }

        if (!empty($data['assign_to_me'])) {
            $user->update(['lab_id' => $lab->id]);
        }

        return redirect()
            ->route('labs.index')
            ->with('status', 'Lab created successfully');
    }

    public function update(Request $request, Lab $lab): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $user = $request->user();
        if (!$user) {
            abort(403);
        }
        if (!$user->isSuperAdmin() && $user->lab_id !== $lab->id) {
            abort(403);
        }

        $adminUser = $lab->users()
            ->whereHas('roles', fn ($query) => $query->where('name', 'Admin'))
            ->first();

        $data = $request->validate([
            'lab_name' => ['required', 'string', 'max:255', Rule::unique('labs', 'name')->ignore($lab->id)],
            'lab_code_prefix' => ['nullable', 'string', 'max:10', Rule::unique('labs', 'code_prefix')->ignore($lab->id)],
            'lab_active' => ['nullable', 'boolean'],
            'lab_sms_enabled' => ['nullable', 'boolean'],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($adminUser?->id)],
            'admin_password' => ['nullable', 'string', 'min:6'],
            'admin_is_active' => ['nullable', 'boolean'],
        ]);

        $codePrefix = trim((string) ($data['lab_code_prefix'] ?? ''));
        if ($codePrefix !== '') {
            $codePrefix = strtoupper($codePrefix);
        }

        $lab->update([
            'name' => $data['lab_name'],
            'code_prefix' => $codePrefix !== '' ? $codePrefix : null,
            'is_active' => !empty($data['lab_active']),
            'sms_enabled' => !empty($data['lab_sms_enabled']),
        ]);

        Setting::updateOrCreate(
            ['lab_id' => $lab->id, 'key' => 'sms_enabled'],
            ['value' => $lab->sms_enabled ? '1' : '0']
        );

        $adminName = trim((string) ($data['admin_name'] ?? ''));
        $adminEmail = trim((string) ($data['admin_email'] ?? ''));
        $adminPassword = (string) ($data['admin_password'] ?? '');
        if ($adminName !== '' || $adminEmail !== '' || $adminPassword !== '') {
            if (!$adminUser) {
                if ($adminName !== '' && $adminEmail !== '' && $adminPassword !== '') {
                    $adminUser = User::create([
                        'name' => $adminName,
                        'email' => $adminEmail,
                        'password' => Hash::make($adminPassword),
                        'is_active' => array_key_exists('admin_is_active', $data) ? (bool) $data['admin_is_active'] : true,
                        'created_by' => $user->id,
                        'lab_id' => $lab->id,
                    ]);
                    $adminRoleId = Role::query()->where('name', 'Admin')->value('id');
                    if ($adminRoleId) {
                        $adminUser->roles()->sync([$adminRoleId]);
                    }
                }
            } else {
                $updates = [];
                if ($adminName !== '') {
                    $updates['name'] = $adminName;
                }
                if ($adminEmail !== '') {
                    $updates['email'] = $adminEmail;
                }
                if ($adminPassword !== '') {
                    $updates['password'] = Hash::make($adminPassword);
                }
                if (array_key_exists('admin_is_active', $data)) {
                    $updates['is_active'] = (bool) $data['admin_is_active'];
                }
                if (!empty($updates)) {
                    $adminUser->update($updates);
                }
            }
        }

        return redirect()
            ->route('labs.index')
            ->with('status', 'Lab updated successfully');
    }

    public function destroy(Request $request, Lab $lab): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $user = $request->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403);
        }

        $lab->delete();

        return redirect()
            ->route('labs.index')
            ->with('status', 'Lab deleted successfully');
    }
}

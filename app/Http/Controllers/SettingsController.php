<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
        private const REPORT_SETTING_KEYS = [
            'report_test_title_color',
        'report_header_html',
        'report_header_mode',
        'report_header_image_path',
        'report_footer_html',
        'report_footer_doctor_line1',
        'report_footer_doctor_line2',
        'report_footer_doctor_line3',
        'report_footer_doctor_line4',
        'report_footer_doctor_line5',
        'report_footer_address',
        'report_footer_phone_t',
        'report_footer_phone_f',
        'report_footer_email',
        'report_footer_website',
        'report_mlt_name',
        'report_logo_path',
        'report_logo_height',
        'report_logo_width',
        'report_signature_path',
    ];

    private array $assignablePermissions = [
        'admin.dashboard',
        'banners.manage',
        'billing.access',
        'billing.create',
        'clinic.billing',
        'centers.manage',
        'departments.manage',
        'doctors.manage',
        'results.approve',
        'results.edit',
        'results.entry',
        'results.validate',
        'tests.manage',
    ];

    public function index(Request $request): View
    {
        $this->requirePermission('admin.dashboard');

        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser
            ? $currentUser->roles()->where('name', 'Super Admin')->exists()
            : false;

        $hasLabId = \Illuminate\Support\Facades\Schema::hasColumn('settings', 'lab_id');
        $selectedLabId = null;
        if ($hasLabId) {
            if ($isSuperAdmin) {
                $labParam = $request->query('lab');
                if ($labParam === null || $labParam === '') {
                    $selectedLabId = null;
                } elseif (is_numeric($labParam)) {
                    $selectedLabId = (int) $labParam;
                }

                if ($selectedLabId !== null && $selectedLabId <= 0) {
                    $selectedLabId = null;
                }
            } elseif ($currentUser && $currentUser->lab_id) {
                $selectedLabId = $currentUser->lab_id;
            }
        }

        $settings = Setting::valuesForLab($selectedLabId ?? 0);

        $userQuery = \App\Models\User::query()->with(['roles', 'permissions', 'lab'])->orderBy('name');
        if (!$isSuperAdmin && $currentUser) {
            $hasCreatedBy = \Illuminate\Support\Facades\Schema::hasColumn('users', 'created_by');
            $hasLabId = \Illuminate\Support\Facades\Schema::hasColumn('users', 'lab_id');
            if ($hasCreatedBy) {
                $userQuery->where(function ($query) use ($currentUser) {
                    $query->where('created_by', $currentUser->id)
                        ->orWhere('id', $currentUser->id);
                });
            } elseif ($hasLabId && $currentUser->lab_id) {
                $userQuery->where(function ($query) use ($currentUser) {
                    $query->where('lab_id', $currentUser->lab_id)
                        ->orWhere('id', $currentUser->id);
                });
            } else {
                $userQuery->where('id', $currentUser->id);
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
            $defaultRoles = [
                ['name' => 'Admin', 'description' => 'Full lab administration access'],
                ['name' => 'Receptionist', 'description' => 'Front desk and registration access'],
                ['name' => 'Phelobotomist', 'description' => 'Sample collection access'],
                ['name' => 'Accountant', 'description' => 'Billing and finance access'],
            ];
            foreach ($defaultRoles as $defaultRole) {
                \App\Models\Role::firstOrCreate(['name' => $defaultRole['name']], $defaultRole);
            }
        }

        $roleQuery = \App\Models\Role::query()->orderBy('name');
        if (!$isSuperAdmin) {
            $roleQuery->whereNotIn('name', ['Super Admin', 'Admin']);
        }

        $permissionsQuery = \App\Models\Permission::query()->orderBy('name');
        if (!$isSuperAdmin) {
            $permissionsQuery->whereIn('name', $this->assignablePermissions);
        }

        $labsEnabled = \Illuminate\Support\Facades\Schema::hasTable('labs');
        $labs = collect();
        if ($isSuperAdmin && $labsEnabled) {
            $labs = \App\Models\Lab::query()->orderBy('name')->get();
        }

        return view('admin.settings', [
            'settings' => $settings,
            'users' => $userQuery->get(),
            'roles' => $roleQuery->get(),
            'permissions' => $permissionsQuery->get(),
            'isSuperAdmin' => $isSuperAdmin,
            'labs' => $labs,
            'labsEnabled' => $labsEnabled,
            'selectedLabId' => $selectedLabId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $currentUser = $request->user();
        $isSuperAdmin = $currentUser ? $currentUser->isSuperAdmin() : false;
        $hasLabId = \Illuminate\Support\Facades\Schema::hasColumn('settings', 'lab_id');
        $selectedLabId = null;
        if ($hasLabId) {
            if ($isSuperAdmin) {
                $requestedLabId = $request->input('settings_lab_id');
                if ($requestedLabId === '') {
                    $selectedLabId = null;
                } elseif ($requestedLabId === null) {
                    $selectedLabId = null;
                } elseif (is_numeric($requestedLabId)) {
                    $selectedLabId = (int) $requestedLabId;
                    if ($selectedLabId <= 0) {
                        $selectedLabId = null;
                    } elseif (!Lab::query()->whereKey($selectedLabId)->exists()) {
                        $selectedLabId = null;
                    }
                }
            } elseif ($currentUser && $currentUser->lab_id) {
                $selectedLabId = $currentUser->lab_id;
            }
        }
        $labId = $selectedLabId;

        $data = $request->validate([
            'lab_name' => ['nullable', 'string', 'max:255'],
            'lab_name_color' => ['nullable', 'string', 'max:20'],
            'lab_logo_path' => ['nullable', 'string', 'max:255'],
            'lab_logo_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'lab_logo_clear' => ['nullable', 'boolean'],
            'sidebar_color_start' => ['nullable', 'string', 'max:20'],
            'sidebar_color_mid' => ['nullable', 'string', 'max:20'],
            'sidebar_color_end' => ['nullable', 'string', 'max:20'],
            'sidebar_text_color' => ['nullable', 'string', 'max:20'],
            'report_test_title_color' => ['nullable', 'string', 'max:20'],
            'report_header_html' => ['nullable', 'string'],
            'report_header_mode' => ['nullable', 'in:html,image'],
            'report_header_image_path' => ['nullable', 'string', 'max:255'],
            'report_header_image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:8192'],
            'report_header_image_clear' => ['nullable', 'boolean'],
            'report_background_path' => ['nullable', 'string', 'max:255'],
            'report_background_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:8192'],
            'report_background_clear' => ['nullable', 'boolean'],
            'report_footer_html' => ['nullable', 'string'],
            'homepage_lab_name_size' => ['nullable', 'integer', 'min:16', 'max:48'],
            'billing_header_image_path' => ['nullable', 'string', 'max:255'],
            'billing_header_image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:8192'],
            'billing_header_image_clear' => ['nullable', 'boolean'],
            'billing_footer_image_path' => ['nullable', 'string', 'max:255'],
            'billing_footer_image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,pdf', 'max:8192'],
            'billing_footer_image_clear' => ['nullable', 'boolean'],
            'billing_lab_name' => ['nullable', 'string', 'max:255'],
            'billing_lab_email' => ['nullable', 'string', 'max:100'],
            'billing_lab_web' => ['nullable', 'string', 'max:100'],
            'billing_lab_contact' => ['nullable', 'string', 'max:50'],
            'billing_lab_fax' => ['nullable', 'string', 'max:50'],
            'billing_lab_address' => ['nullable', 'string', 'max:255'],
            'report_footer_doctor_line1' => ['nullable', 'string', 'max:255'],
            'report_footer_doctor_line2' => ['nullable', 'string', 'max:255'],
            'report_footer_doctor_line3' => ['nullable', 'string', 'max:255'],
            'report_footer_doctor_line4' => ['nullable', 'string', 'max:255'],
            'report_footer_doctor_line5' => ['nullable', 'string', 'max:255'],
            'report_footer_address' => ['nullable', 'string', 'max:255'],
            'report_footer_phone_t' => ['nullable', 'string', 'max:50'],
            'report_footer_phone_f' => ['nullable', 'string', 'max:50'],
            'report_footer_email' => ['nullable', 'string', 'max:100'],
            'report_footer_website' => ['nullable', 'string', 'max:100'],
            'report_mlt_name' => ['nullable', 'string', 'max:255'],
            'report_logo_path' => ['nullable', 'string', 'max:255'],
            'report_logo_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'report_logo_clear' => ['nullable', 'boolean'],
            'report_logo_height' => ['nullable', 'integer', 'min:10', 'max:300'],
            'report_logo_width' => ['nullable', 'integer', 'min:10', 'max:600'],
            'report_signature_path' => ['nullable', 'string', 'max:255'],
            'report_signature_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'report_signature_clear' => ['nullable', 'boolean'],
            'website_header_html' => ['nullable', 'string'],
            'website_footer_html' => ['nullable', 'string'],
            'website_body_html' => ['nullable', 'string'],
            'website_color_primary' => ['nullable', 'string', 'max:20'],
            'website_color_secondary' => ['nullable', 'string', 'max:20'],
            'homepage_map_embed' => ['nullable', 'string', 'max:1000'],
            'homepage_map_label' => ['nullable', 'string', 'max:255'],
            'website_email_placeholder' => ['nullable', 'string', 'max:120'],
            'email_username' => ['nullable', 'string', 'max:120'],
            'email_password' => ['nullable', 'string', 'max:120'],
            'user_default_permissions' => ['nullable', 'array'],
            'user_default_permissions.*' => ['integer'],
            'user_enforce_permissions' => ['nullable', 'boolean'],
            'settings_section' => ['nullable', 'string', 'max:40'],
            'allow_results_edit_non_admin' => ['nullable', 'boolean'],
            'sms_gateway_url' => ['nullable', 'string', 'max:255'],
            'sms_oauth_endpoint' => ['nullable', 'string', 'max:255'],
            'sms_api_key' => ['nullable', 'string', 'max:255'],
            'sms_api_token' => ['nullable', 'string', 'max:255'],
            'sms_sender_id' => ['nullable', 'string', 'max:50'],
            'sms_enabled' => ['nullable', 'boolean'],
            'sms_http_method' => ['nullable', 'in:GET,POST'],
            'sms_param_to' => ['nullable', 'string', 'max:50'],
            'sms_param_message' => ['nullable', 'string', 'max:50'],
            'sms_param_api_key' => ['nullable', 'string', 'max:50'],
            'sms_param_sender_id' => ['nullable', 'string', 'max:50'],
            'sms_extra_params' => ['nullable', 'string', 'max:1000'],
            'sms_template_billing' => ['nullable', 'string', 'max:500'],
            'sms_template_report_ready' => ['nullable', 'string', 'max:500'],
            'sms_template_report_link' => ['nullable', 'string', 'max:500'],
            'whatsapp_number' => ['nullable', 'string', 'max:50'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_youtube' => ['nullable', 'string', 'max:255'],
            'social_linkedin' => ['nullable', 'string', 'max:255'],
            'social_x' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($data['report_logo_path']) && !$request->hasFile('report_logo_file') && empty($data['report_logo_clear'])) {
            unset($data['report_logo_path']);
        }
        if (empty($data['lab_logo_path']) && !$request->hasFile('lab_logo_file') && empty($data['lab_logo_clear'])) {
            unset($data['lab_logo_path']);
        }
        if (empty($data['report_signature_path']) && !$request->hasFile('report_signature_file') && empty($data['report_signature_clear'])) {
            unset($data['report_signature_path']);
        }
        if (empty($data['report_header_image_path']) && !$request->hasFile('report_header_image_file') && empty($data['report_header_image_clear'])) {
            unset($data['report_header_image_path']);
        }
        if (empty($data['report_background_path']) && !$request->hasFile('report_background_file') && empty($data['report_background_clear'])) {
            unset($data['report_background_path']);
        }
        if (empty($data['billing_header_image_path']) && !$request->hasFile('billing_header_image_file') && empty($data['billing_header_image_clear'])) {
            unset($data['billing_header_image_path']);
        }
        if (empty($data['billing_footer_image_path']) && !$request->hasFile('billing_footer_image_file') && empty($data['billing_footer_image_clear'])) {
            unset($data['billing_footer_image_path']);
        }

        $settingsLabId = $labId;

        if (!empty($data['report_logo_clear'])) {
            $existingPathQuery = Setting::query()->where('key', 'report_logo_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $data['report_logo_path'] = null;
        }

        if ($request->hasFile('report_logo_file')) {
            $existingPathQuery = Setting::query()->where('key', 'report_logo_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('report_logo_file')->store('uploads/report', 'public');
            $data['report_logo_path'] = '/storage/' . $path;
        }

        if (!empty($data['lab_logo_clear'])) {
            $existingPathQuery = Setting::query()->where('key', 'lab_logo_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $data['lab_logo_path'] = null;
        }

        if ($request->hasFile('lab_logo_file')) {
            $existingPathQuery = Setting::query()->where('key', 'lab_logo_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('lab_logo_file')->store('uploads/lab-logos', 'public');
            $data['lab_logo_path'] = '/storage/' . $path;
        }

        if (!empty($data['report_header_image_clear'])) {
            $existingPathQuery = Setting::query()->where('key', 'report_header_image_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $data['report_header_image_path'] = null;
        }

        if ($request->hasFile('report_header_image_file')) {
            $existingPathQuery = Setting::query()->where('key', 'report_header_image_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('report_header_image_file')->store('uploads/report', 'public');
            $data['report_header_image_path'] = '/storage/' . $path;
        }

        if (!empty($data['report_background_clear'])) {
            $existingPathQuery = Setting::query()->where('key', 'report_background_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $data['report_background_path'] = null;
        }

        if ($request->hasFile('report_background_file')) {
            $existingPathQuery = Setting::query()->where('key', 'report_background_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('report_background_file')->store('uploads/report', 'public');
            $data['report_background_path'] = '/storage/' . $path;
        }

        if (!empty($data['billing_header_image_clear'])) {
            $existingPathQuery = Setting::query()->where('key', 'billing_header_image_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $data['billing_header_image_path'] = null;
        }

        if ($request->hasFile('billing_header_image_file')) {
            $existingPathQuery = Setting::query()->where('key', 'billing_header_image_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('billing_header_image_file')->store('uploads/billing', 'public');
            $data['billing_header_image_path'] = '/storage/' . $path;
        }

        if (!empty($data['billing_footer_image_clear'])) {
            $existingPathQuery = Setting::query()->where('key', 'billing_footer_image_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $data['billing_footer_image_path'] = null;
        }

        if ($request->hasFile('billing_footer_image_file')) {
            $existingPathQuery = Setting::query()->where('key', 'billing_footer_image_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('billing_footer_image_file')->store('uploads/billing', 'public');
            $data['billing_footer_image_path'] = '/storage/' . $path;
        }

        if (!empty($data['report_signature_clear'])) {
            $existingPathQuery = Setting::query()->where('key', 'report_signature_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $data['report_signature_path'] = null;
        }

        if ($request->hasFile('report_signature_file')) {
            $existingPathQuery = Setting::query()->where('key', 'report_signature_path');
            if ($hasLabId) {
                $existingPathQuery->where('lab_id', $settingsLabId);
            }
            $existingPath = $existingPathQuery->value('value');
            if ($existingPath) {
                $storagePath = ltrim(str_replace('/storage/', '', $existingPath), '/');
                Storage::disk('public')->delete($storagePath);
            }
            $path = $request->file('report_signature_file')->store('uploads/report', 'public');
            $data['report_signature_path'] = '/storage/' . $path;
        }

        $defaultPermissions = $request->input('user_default_permissions', []);
        if (is_array($defaultPermissions)) {
            $data['user_default_permissions'] = implode(',', array_map('strval', $defaultPermissions));
        }

        if (array_key_exists('user_enforce_permissions', $data)) {
            $data['user_enforce_permissions'] = $data['user_enforce_permissions'] ? '1' : '0';
        }

        if (array_key_exists('allow_results_edit_non_admin', $data)) {
            $data['allow_results_edit_non_admin'] = $data['allow_results_edit_non_admin'] ? '1' : '0';
        }

        if (array_key_exists('sms_enabled', $data)) {
            $data['sms_enabled'] = $data['sms_enabled'] ? '1' : '0';
        }

        if (empty($data['email_password'])) {
            unset($data['email_password']);
        }

        $globalKeys = [
            'website_header_html',
            'website_footer_html',
            'website_body_html',
            'website_color_primary',
            'website_color_secondary',
            'homepage_map_embed',
            'homepage_map_label',
            'website_email_placeholder',
            'homepage_lab_name_size',
        ];

        foreach ($data as $key => $value) {
            if (!$isSuperAdmin && in_array($key, $globalKeys, true)) {
                continue;
            }

            $targetLabId = in_array($key, $globalKeys, true) ? null : $labId;
            if ($key === 'sms_gateway_url' && str_contains((string) $value, 'app.text.lk')) {
                $value = 'https://app.text.lk/api/v3/';
            }
            $match = ['key' => $key];
            if ($hasLabId) {
                $match['lab_id'] = $targetLabId;
            }

            Setting::updateOrCreate(
                $match,
                ['value' => $value]
            );
        }

        $redirectParams = [];
        if ($isSuperAdmin && $selectedLabId !== null) {
            $redirectParams['lab'] = $selectedLabId;
        }

        return redirect()
            ->route('settings.index', $redirectParams)
            ->with('status', 'Saved successfully')
            ->with('settings_section', $request->input('settings_section', 'report'));
    }

    public function copyReportSettings(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'lab_ids' => ['required', 'array', 'min:1'],
            'lab_ids.*' => ['integer', 'exists:labs,id'],
        ]);

        $defaultSettings = Setting::query()
            ->whereNull('lab_id')
            ->whereIn('key', self::REPORT_SETTING_KEYS)
            ->get()
            ->keyBy('key');

        foreach ($data['lab_ids'] as $labId) {
            foreach (self::REPORT_SETTING_KEYS as $key) {
                if (!$defaultSettings->has($key)) {
                    continue;
                }
                $value = $defaultSettings->get($key)->value;
                Setting::updateOrCreate(
                    ['lab_id' => $labId, 'key' => $key],
                    ['value' => $value]
                );
            }
        }

        return redirect()
            ->route('settings.index')
            ->with('status', 'Report settings copied to selected labs')
            ->with('settings_section', 'report');
    }
}

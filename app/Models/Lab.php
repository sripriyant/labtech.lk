<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Center;
use App\Models\Doctor;
use App\Models\Setting;

class Lab extends Model
{
    use HasFactory;
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
        'report_background_path',
    ];

    protected $fillable = [
        'name',
        'code_prefix',
        'is_active',
        'sms_enabled',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function preloadTestsFromDefault(): void
    {
        if (!Schema::hasTable('test_masters') || !Schema::hasTable('departments')) {
            return;
        }

        $hasTests = TestMaster::withoutGlobalScopes()
            ->where('lab_id', $this->id)
            ->exists();
        if ($hasTests) {
            return;
        }

        $defaultDepartments = Department::withoutGlobalScopes()
            ->whereNull('lab_id')
            ->orderBy('id')
            ->get();
        if ($defaultDepartments->isEmpty()) {
            return;
        }

        $defaultTests = TestMaster::withoutGlobalScopes()
            ->whereNull('lab_id')
            ->with(['parameters', 'packageItems'])
            ->orderBy('id')
            ->get();

        DB::transaction(function () use ($defaultDepartments, $defaultTests): void {
            $existingDepartments = Department::withoutGlobalScopes()
                ->where('lab_id', $this->id)
                ->get()
                ->keyBy('code');

            $departmentMap = [];
            foreach ($defaultDepartments as $department) {
                $existing = $existingDepartments->get($department->code);
                if (!$existing) {
                    $existing = Department::withoutGlobalScopes()->firstOrCreate(
                        ['lab_id' => $this->id, 'code' => $department->code],
                        ['name' => $department->name, 'is_active' => $department->is_active]
                    );
                    $existingDepartments->put($department->code, $existing);
                }

                $departmentMap[$department->id] = $existing->id;
            }

            $testIdMap = [];
            foreach ($defaultTests as $test) {
                $existingTest = TestMaster::withoutGlobalScopes()
                    ->where('lab_id', $this->id)
                    ->where('code', $test->code)
                    ->first();

                if ($existingTest) {
                    $testIdMap[$test->id] = $existingTest->id;
                    continue;
                }

                $clone = $test->replicate();
                $clone->lab_id = $this->id;
                if (!empty($test->department_id) && isset($departmentMap[$test->department_id])) {
                    $clone->department_id = $departmentMap[$test->department_id];
                }
                $clone->save();
                $testIdMap[$test->id] = $clone->id;

                foreach ($test->parameters as $parameter) {
                    $parameterClone = $parameter->replicate();
                    $parameterClone->test_master_id = $clone->id;
                    $parameterClone->save();
                }
            }

            foreach ($defaultTests as $test) {
                if (!$test->is_package) {
                    continue;
                }

                $newPackageId = $testIdMap[$test->id] ?? null;
                if (!$newPackageId) {
                    continue;
                }

                $newItemIds = $test->packageItems
                    ->map(fn (TestMaster $item) => $testIdMap[$item->id] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                if (!empty($newItemIds)) {
                    $newPackage = TestMaster::withoutGlobalScopes()->find($newPackageId);
                    if ($newPackage) {
                        $newPackage->packageItems()->sync($newItemIds);
                    }
                }
            }
        });
    }

    public function preloadDoctorsFromDefault(): void
    {
        if (!Schema::hasTable('doctors')) {
            return;
        }

        $hasDoctors = Doctor::withoutGlobalScopes()
            ->where('lab_id', $this->id)
            ->exists();
        if ($hasDoctors) {
            return;
        }

        $defaultDoctors = Doctor::withoutGlobalScopes()
            ->whereNull('lab_id')
            ->orderBy('id')
            ->get();

        foreach ($defaultDoctors as $doctor) {
            $clone = $doctor->replicate();
            $clone->lab_id = $this->id;
            $clone->save();
        }
    }

    public function preloadCentersFromDefault(): void
    {
        if (!Schema::hasTable('centers')) {
            return;
        }

        $hasCenters = Center::withoutGlobalScopes()
            ->where('lab_id', $this->id)
            ->exists();
        if ($hasCenters) {
            return;
        }

        $defaultCenters = Center::withoutGlobalScopes()
            ->whereNull('lab_id')
            ->orderBy('id')
            ->get();
        if ($defaultCenters->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($defaultCenters): void {
            $centerMap = [];
            foreach ($defaultCenters as $center) {
                $clone = $center->replicate();
                $clone->lab_id = $this->id;
                $clone->parent_center_id = null;
                $clone->save();
                $centerMap[$center->id] = $clone;
            }

            foreach ($defaultCenters as $center) {
                if (!$center->parent_center_id) {
                    continue;
                }

                $clone = $centerMap[$center->id] ?? null;
                $parentClone = $centerMap[$center->parent_center_id] ?? null;
                if ($clone && $parentClone) {
                    $clone->parent_center_id = $parentClone->id;
                    $clone->save();
                }
            }
        });
    }

    public function preloadReportSettingsFromDefault(): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        $hasSettings = Setting::query()
            ->where('lab_id', $this->id)
            ->whereIn('key', self::REPORT_SETTING_KEYS)
            ->exists();
        if ($hasSettings) {
            return;
        }

        $defaultSettings = Setting::query()
            ->whereNull('lab_id')
            ->whereIn('key', self::REPORT_SETTING_KEYS)
            ->get();

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(
                ['lab_id' => $this->id, 'key' => $setting->key],
                ['value' => $setting->value]
            );
        }
    }
}

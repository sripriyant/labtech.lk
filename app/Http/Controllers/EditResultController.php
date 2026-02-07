<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SpecimenTest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Http\Controllers\Concerns\LipidInterpretation;

class EditResultController extends Controller
{
    use LipidInterpretation;
    public function index(Request $request): View
    {
        $this->requirePermission('results.edit');

        $this->assertEditAccess();

        $specimenNo = trim((string) $request->get('specimen_no', ''));
        $patientName = trim((string) $request->get('patient', ''));
        $test = trim((string) $request->get('test', ''));
        $status = trim((string) $request->get('status', ''));
        $sort = trim((string) $request->get('sort', 'patient_asc'));

        $query = SpecimenTest::query()
            ->with(['specimen.patient', 'testMaster.parameters' => function ($query) {
                $query->orderBy('sort_order');
            }, 'result', 'parameterResults'])
            ->orderByDesc('id');

        if ($specimenNo !== '') {
            $query->whereHas('specimen', function ($q) use ($specimenNo) {
                $q->where('specimen_no', 'like', $specimenNo . '%');
            });
        }

        if ($patientName !== '') {
            $query->whereHas('specimen.patient', function ($q) use ($patientName) {
                $q->where('name', 'like', '%' . $patientName . '%');
            });
        }

        if ($test !== '') {
            $query->whereHas('testMaster', function ($q) use ($test) {
                $q->where('name', 'like', '%' . $test . '%')
                    ->orWhere('code', 'like', $test . '%');
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $rows = $query->limit(300)->get();
        $rows->each(function (SpecimenTest $row): void {
            $row->testMaster?->ensureCbcParameters();
        });
        $severityOrder = [
            'CRITICAL' => 4,
            'ABNORMAL' => 3,
            'HIGH' => 2,
            'LOW' => 2,
            'NORMAL' => 1,
        ];
        $rows->each(function (SpecimenTest $row) use ($severityOrder): void {
            $flags = collect();
            if ($row->result && $row->result->flag) {
                $flags->push(strtoupper($row->result->flag));
            }
            if ($row->parameterResults && $row->parameterResults->isNotEmpty()) {
                foreach ($row->parameterResults as $result) {
                    if (!empty($result->flag)) {
                        $flags->push(strtoupper($result->flag));
                    }
                }
            }
            $flagSummary = '';
            if ($flags->isNotEmpty()) {
                $flagSummary = $flags->unique()
                    ->sortByDesc(fn ($flag) => $severityOrder[$flag] ?? 0)
                    ->first() ?? '';
            }
            $row->edit_flag = $flagSummary;
        });

        $rows = match ($sort) {
            'patient_desc' => $rows->sortByDesc(fn ($row) => $row->specimen?->patient?->name ?? ''),
            'specimen_asc' => $rows->sortBy(fn ($row) => $row->specimen?->specimen_no ?? ''),
            'specimen_desc' => $rows->sortByDesc(fn ($row) => $row->specimen?->specimen_no ?? ''),
            'test_asc' => $rows->sortBy(fn ($row) => $row->testMaster?->name ?? ''),
            'test_desc' => $rows->sortByDesc(fn ($row) => $row->testMaster?->name ?? ''),
            'status_asc' => $rows->sortBy(fn ($row) => $row->status ?? ''),
            'status_desc' => $rows->sortByDesc(fn ($row) => $row->status ?? ''),
            'flag_desc' => $rows->sortByDesc(fn ($row) => $severityOrder[$row->edit_flag ?? ''] ?? 0),
            'flag_asc' => $rows->sortBy(fn ($row) => $severityOrder[$row->edit_flag ?? ''] ?? 0),
            default => $rows->sortBy(fn ($row) => $row->specimen?->patient?->name ?? ''),
        };

        return view('results.edit', [
            'rows' => $rows,
            'filters' => [
                'specimen_no' => $specimenNo,
                'patient' => $patientName,
                'test' => $test,
                'status' => $status,
                'sort' => $sort,
            ],
        ]);
    }

    public function update(Request $request, SpecimenTest $specimenTest): RedirectResponse
    {
        $this->requirePermission('results.edit');
        $this->assertEditAccess();

        $data = $request->validate([
            'patient_name' => ['required', 'string', 'max:255'],
            'patient_sex' => ['nullable', 'string', 'max:20'],
            'patient_phone' => ['nullable', 'string', 'max:50'],
            'patient_nic' => ['nullable', 'string', 'max:50'],
            'result_value' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'reference_range' => ['nullable', 'string', 'max:50'],
            'parameter_results' => ['nullable', 'array'],
            'is_repeated' => ['nullable', 'boolean'],
            'is_confirmed' => ['nullable', 'boolean'],
        ]);

        $userId = auth()->id();
        $ip = $request->ip();
        $ua = (string) $request->userAgent();

        DB::transaction(function () use ($specimenTest, $data, $userId, $ip, $ua) {
            $specimenTest->load(['specimen.patient', 'result']);
            $specimenTest->update([
                'is_repeated' => (bool) ($data['is_repeated'] ?? false),
                'is_confirmed' => (bool) ($data['is_confirmed'] ?? false),
                'status' => 'RESULT_ENTERED',
                'updated_by' => $userId,
            ]);

            $patient = $specimenTest->specimen?->patient;
            if ($patient) {
                $beforePatient = $patient->only(['name', 'sex', 'phone', 'nic']);
                $updates = [
                    'name' => $data['patient_name'],
                    'sex' => $data['patient_sex'] ?? null,
                    'phone' => $data['patient_phone'] ?? null,
                    'nic' => $data['patient_nic'] ?? null,
                ];
                $patient->update($updates);

                AuditLog::create([
                    'user_id' => $userId,
                    'action' => 'patient.update',
                    'entity_type' => 'patient',
                    'entity_id' => $patient->id,
                    'before_data' => $beforePatient,
                    'after_data' => $patient->only(['name', 'sex', 'phone', 'nic']),
                    'ip_address' => $ip,
                    'user_agent' => $ua,
                ]);
            }

            $specimenTest->load(['testMaster.parameters', 'parameterResults', 'result']);
        $parameters = $specimenTest->testMaster?->parameters ?? collect();
        $parameters = $parameters->sortBy('sort_order')->values();
            $patientSex = $specimenTest->specimen?->patient?->sex ?? null;
            $patientAge = null;
            if ($specimenTest->specimen?->age_unit === 'Y' && $specimenTest->specimen?->age_years !== null) {
                $patientAge = (int) $specimenTest->specimen->age_years;
            }
            if ($parameters->isNotEmpty()) {
                $parameterResults = $data['parameter_results'] ?? [];
                $upperParams = $parameters->keyBy(function ($parameter) {
                    return strtoupper(trim((string) $parameter->name));
                });
                $creatinineParam = $upperParams->first(function ($param, $name) {
                    return str_contains($name, 'CREATININE');
                });
                $egfrParam = $upperParams->first(function ($param, $name) {
                    return str_contains($name, 'ESTIMATED GFR') || str_contains($name, 'EGFR');
                });
                if ($creatinineParam && $egfrParam) {
                    $creatinineRaw = $parameterResults[$creatinineParam->id]['result_value'] ?? '';
                    $creatinineValue = is_numeric($creatinineRaw) ? (float) $creatinineRaw : null;
                    if ($creatinineValue !== null && $patientAge !== null && $patientSex) {
                        $egfrValue = $this->computeEgfr($creatinineValue, (int) $patientAge, $patientSex);
                        if ($egfrValue !== null) {
                            $parameterResults[$egfrParam->id]['result_value'] = $egfrValue >= 90
                                ? 'Over 90'
                                : number_format($egfrValue, 2, '.', '');
                        }
                    }
                }
                $diffNames = ['NEUTROPHILS', 'LYMPHOCYTES', 'EOSINOPHILS', 'MONOCYTES', 'BASOPHILS'];
                $paramByName = $parameters->keyBy(function ($parameter) {
                    return strtoupper(trim((string) $parameter->name));
                });
                $diffParams = collect($diffNames)
                    ->map(fn ($name) => $paramByName->get($name))
                    ->filter();
                $isFullBloodCount = $this->isFullBloodCountTest($specimenTest);
                if ($diffParams->isNotEmpty() && $isFullBloodCount) {
                    $sum = 0.0;
                    $allFilled = true;
                    $filledCount = 0;
                    foreach ($diffNames as $name) {
                        $param = $paramByName->get($name);
                        if (!$param) {
                            $allFilled = false;
                            continue;
                        }
                        $value = trim((string) ($parameterResults[$param->id]['result_value'] ?? ''));
                        if ($value === '' || !is_numeric($value)) {
                            $allFilled = false;
                            continue;
                        }
                        $filledCount++;
                        $sum += (float) $value;
                    }
                    if ($filledCount < count($diffNames)) {
                        throw ValidationException::withMessages([
                            'differential_total' => ['Differential counts must include all five values before saving.'],
                        ]);
                    }
                    if ($allFilled && $filledCount === count($diffNames)) {
                        $totalRounded = round($sum, 1);
                        if (abs($totalRounded - 100.0) > 0.000001) {
                            $diffRounded = round(100.0 - $totalRounded, 1);
                            $formattedTotal = number_format($totalRounded, 1, '.', '');
                            $formattedDiff = number_format(abs($diffRounded), 1, '.', '');
                            if ($diffRounded > 0) {
                                $message = "Differential counts must total 100%. Current total: {$formattedTotal}%. Short by {$formattedDiff}%.";
                            } else {
                                $message = "Differential counts must total 100%. Current total: {$formattedTotal}%. Exceeds by {$formattedDiff}%.";
                            }
                            throw ValidationException::withMessages([
                                'differential_total' => [$message],
                            ]);
                        }
                    }
                }
                $preparedParameterResults = [];
                foreach ($parameters as $parameter) {
                    $payload = $parameterResults[$parameter->id] ?? [];
                    $value = trim((string) ($payload['result_value'] ?? ''));
                    $unit = trim((string) $parameter->unit);
                    $ref = trim((string) $parameter->reference_range);
                    $remarks = trim((string) ($payload['remarks'] ?? ''));
                    $numericValue = is_numeric($value) ? (float) $value : null;
                    $flag = $this->determineLipidInterpretation($parameter->name, $numericValue, $patientSex)
                        ?? $this->computeFlagFromRange($value, $ref, $patientSex);

                    if ($value === '' && $remarks === '' && $flag === '') {
                        continue;
                    }

                    $preparedParameterResults[$parameter->id] = [
                        'result_value' => $value,
                        'unit' => $unit ?: null,
                        'reference_range' => $ref ?: null,
                        'remarks' => $remarks ?: null,
                        'flag' => $flag ?: null,
                    ];
                }

                if (empty($preparedParameterResults)) {
                    throw ValidationException::withMessages([
                        'parameter_results' => ['Please enter at least one parameter value, remark, or flag before saving.'],
                    ]);
                }

                foreach ($preparedParameterResults as $parameterId => $payload) {
                    $existing = $specimenTest->parameterResults
                        ->firstWhere('test_parameter_id', $parameterId);
                    $beforeParam = $existing ? $existing->only(['result_value', 'unit', 'reference_range', 'remarks', 'flag']) : null;

                    $saved = $specimenTest->parameterResults()->updateOrCreate(
                        [
                            'test_parameter_id' => $parameterId,
                        ],
                        array_merge($payload, [
                            'entered_by' => $userId,
                            'entered_at' => now(),
                        ])
                    );

                    AuditLog::create([
                        'user_id' => $userId,
                        'action' => 'parameter_result.update',
                        'entity_type' => 'test_parameter_result',
                        'entity_id' => $saved->id,
                        'before_data' => $beforeParam,
                        'after_data' => $saved->only(['result_value', 'unit', 'reference_range', 'remarks', 'flag']),
                        'ip_address' => $ip,
                        'user_agent' => $ua,
                    ]);
                }
            } else {
                if (trim((string) ($data['result_value'] ?? '')) === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'result_value' => 'Result value is required.',
                    ]);
                }
                $result = $specimenTest->result;
                $beforeResult = $result ? $result->only(['result_value', 'unit', 'reference_range']) : null;

                $flag = $this->computeFlagFromRange($data['result_value'] ?? '', $data['reference_range'] ?? '', $patientSex);
                if ($result) {
                    $result->update([
                        'result_value' => $data['result_value'],
                        'unit' => $data['unit'] ?? null,
                        'reference_range' => $data['reference_range'] ?? null,
                        'flag' => $flag,
                        'entered_by' => $userId,
                        'entered_at' => now(),
                    ]);
                } else {
                    $result = $specimenTest->result()->create([
                        'result_value' => $data['result_value'],
                        'unit' => $data['unit'] ?? null,
                        'reference_range' => $data['reference_range'] ?? null,
                        'flag' => $flag,
                        'entered_by' => $userId,
                        'entered_at' => now(),
                    ]);
                }

                AuditLog::create([
                    'user_id' => $userId,
                    'action' => 'result.update',
                    'entity_type' => 'specimen_test',
                    'entity_id' => $specimenTest->id,
                    'before_data' => $beforeResult,
                    'after_data' => $result->only(['result_value', 'unit', 'reference_range']),
                    'ip_address' => $ip,
                    'user_agent' => $ua,
                ]);
            }
        });

        return redirect()->route('results.edit');
    }

    private function assertEditAccess(): void
    {
        $allowNonAdmin = true;
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $allowQuery = Setting::query()->where('key', 'allow_results_edit_non_admin');
            $currentUser = auth()->user();
            if ($currentUser && !$currentUser->isSuperAdmin()) {
                $allowQuery->where('lab_id', $currentUser->lab_id);
            } else {
                $allowQuery->whereNull('lab_id');
            }
            $allowValue = $allowQuery->value('value');
            if ($allowValue === '0') {
                $allowNonAdmin = false;
            }
        }
        if ($allowNonAdmin) {
            return;
        }
        $user = auth()->user();
        $isAdmin = $user && $user->roles()->where('name', 'Super Admin')->exists();
        if (!$isAdmin) {
            abort(403, 'Edit results is restricted to admins.');
        }
    }

    private function computeFlagFromRange(string $value, string $range, ?string $sex = null): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        $numericValue = is_numeric($value) ? (float) $value : null;
        if ($numericValue === null && preg_match('/-?\d+(?:\.\d+)?/', $value, $matches)) {
            $numericValue = (float) $matches[0];
        }
        if ($numericValue === null) {
            return null;
        }
        $rangeText = trim($range);
        if ($rangeText === '') {
            return null;
        }

        $sexRange = null;
        if ($sex) {
            $sexLower = strtolower($sex);
            if (str_contains($rangeText, 'Male') || str_contains($rangeText, 'Female') || str_contains($rangeText, 'male') || str_contains($rangeText, 'female')) {
                if (str_contains($sexLower, 'male') && preg_match('/male[^0-9]*([0-9.]+)\s*-\s*([0-9.]+)/i', $rangeText, $match)) {
                    $sexRange = $match[1] . ' - ' . $match[2];
                } elseif (str_contains($sexLower, 'female') && preg_match('/female[^0-9]*([0-9.]+)\s*-\s*([0-9.]+)/i', $rangeText, $match)) {
                    $sexRange = $match[1] . ' - ' . $match[2];
                }
            }
        }
        if ($sexRange !== null) {
            $rangeText = $sexRange;
        }

        if (preg_match('/(>=|>)\s*([0-9.]+)/', $rangeText, $match)) {
            $min = (float) $match[2];
            return $numericValue < $min ? 'LOW' : 'NORMAL';
        }
        if (preg_match('/(<=|<)\s*([0-9.]+)/', $rangeText, $match)) {
            $max = (float) $match[2];
            return $numericValue > $max ? 'HIGH' : 'NORMAL';
        }
        $numbers = [];
        if (preg_match_all('/-?\d+(?:\.\d+)?/', $rangeText, $matches)) {
            $numbers = $matches[0];
        }
        if (count($numbers) < 2) {
            return null;
        }
        $min = (float) $numbers[0];
        $max = (float) $numbers[1];
        if ($min > $max) {
            [$min, $max] = [$max, $min];
        }
        if ($numericValue < $min) {
            return 'LOW';
        }
        if ($numericValue > $max) {
            return 'HIGH';
        }
        return 'NORMAL';
    }

    private function computeEgfr(float $creatinine, int $age, string $sex): ?float
    {
        if ($creatinine <= 0 || $age <= 0) {
            return null;
        }
        $isFemale = str_contains(strtolower($sex), 'female');
        $k = $isFemale ? 0.7 : 0.9;
        $alpha = $isFemale ? -0.329 : -0.411;
        $scrByK = $creatinine / $k;
        $min = min($scrByK, 1.0);
        $max = max($scrByK, 1.0);
        $egfr = 141 * pow($min, $alpha) * pow($max, -1.209) * pow(0.993, $age);
        if ($isFemale) {
            $egfr *= 1.018;
        }
        return round($egfr, 2);
    }

    private function isFullBloodCountTest(SpecimenTest $specimenTest): bool
    {
        $testName = strtoupper(trim((string) ($specimenTest->testMaster?->name ?? '')));
        if ($testName === '') {
            return false;
        }
        return str_contains($testName, 'FULL BLOOD COUNT') || str_contains($testName, 'FBC');
    }
}

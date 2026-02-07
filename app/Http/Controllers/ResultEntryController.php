<?php

namespace App\Http\Controllers;

use App\Models\SpecimenTest;
use App\Models\TestResult;
use App\Models\TestParameterResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Http\Controllers\Concerns\LipidInterpretation;

class ResultEntryController extends Controller
{
    use LipidInterpretation;
    public function index(): View
    {
        $this->requirePermission('results.entry');

        $sort = request()->query('sort', 'latest_desc');
        $items = SpecimenTest::query()
            ->whereIn('status', ['ORDERED', 'REJECTED'])
            ->with(['specimen.patient', 'testMaster.parameters' => function ($query) {
                $query->orderBy('sort_order');
            }, 'parameterResults', 'result'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();
        $items->each(function (SpecimenTest $item): void {
            $item->testMaster?->ensureCbcParameters();
        });
        $items->each(function (SpecimenTest $item): void {
            $parameters = $item->testMaster?->parameters ?? collect();
            $parameters = $parameters->sortBy('sort_order')->values();
            $totalParams = $parameters->count();
            $entered = $item->parameterResults
                ? $item->parameterResults->filter(function ($result) {
                    $value = trim((string) ($result->result_value ?? ''));
                    $remarks = trim((string) ($result->remarks ?? ''));
                    return $value !== '' || $remarks !== '';
                })->count()
                : 0;

            if ($totalParams > 0) {
                if ($entered === 0) {
                    $item->entry_status = 'pending';
                } elseif ($entered < $totalParams) {
                    $item->entry_status = 'partial';
                } else {
                    $item->entry_status = 'saved';
                }
            } else {
                $singleValue = trim((string) ($item->result?->result_value ?? ''));
                $item->entry_status = $singleValue === '' ? 'pending' : 'saved';
            }
        });

        $statusOrder = ['pending' => 1, 'partial' => 2, 'saved' => 3];
        $items = match ($sort) {
            'latest_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->created_at ?? now()),
            'latest_asc' => $items->sortBy(fn ($item) => $item->specimen?->created_at ?? now()),
            'pending_first' => $items->sortBy(fn ($item) => $statusOrder[$item->entry_status] ?? 0),
            'patient_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->patient?->name ?? ''),
            'uhid_asc' => $items->sortBy(fn ($item) => $item->specimen?->patient?->uhid ?? ''),
            'uhid_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->patient?->uhid ?? ''),
            'specimen_asc' => $items->sortBy(fn ($item) => $item->specimen?->specimen_no ?? ''),
            'specimen_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->specimen_no ?? ''),
            'test_asc' => $items->sortBy(fn ($item) => $item->testMaster?->name ?? ''),
            'test_desc' => $items->sortByDesc(fn ($item) => $item->testMaster?->name ?? ''),
            'status_asc' => $items->sortBy(fn ($item) => $statusOrder[$item->entry_status] ?? 0),
            'status_desc' => $items->sortByDesc(fn ($item) => $statusOrder[$item->entry_status] ?? 0),
            default => $items->sortBy(fn ($item) => $item->specimen?->patient?->name ?? ''),
        };

        return view('results.entry', [
            'items' => $items,
            'sort' => $sort,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('results.entry');

        $data = $request->validate([
            'specimen_test_id' => ['required', 'integer', 'exists:specimen_tests,id'],
            'result_value' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'reference_range' => ['nullable', 'string', 'max:50'],
            'patient_name' => ['nullable', 'string', 'max:255'],
            'parameter_results' => ['nullable', 'array'],
            'is_repeated' => ['nullable', 'boolean'],
            'is_confirmed' => ['nullable', 'boolean'],
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($data, $userId) {
            $specimenTest = SpecimenTest::query()
                ->with(['specimen.patient', 'testMaster.parameters'])
                ->findOrFail($data['specimen_test_id']);

            if (!in_array($specimenTest->status, ['ORDERED', 'REJECTED'], true)) {
                return;
            }

            $patient = $specimenTest->specimen?->patient;
            if ($patient) {
                $updates = [];
                if (!empty($data['patient_name'])) {
                    $updates['name'] = $data['patient_name'];
                }
                if (!empty($updates)) {
                    $patient->update($updates);
                }
            }

            $specimenTest->update([
                'is_repeated' => (bool) ($data['is_repeated'] ?? false),
                'is_confirmed' => (bool) ($data['is_confirmed'] ?? false),
            ]);

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
                    TestParameterResult::updateOrCreate(
                        [
                            'specimen_test_id' => $specimenTest->id,
                            'test_parameter_id' => $parameterId,
                        ],
                        array_merge($payload, [
                            'entered_by' => $userId,
                            'entered_at' => now(),
                        ])
                    );
                }
            } else {
                if (trim((string) ($data['result_value'] ?? '')) === '') {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'result_value' => 'Result value is required.',
                    ]);
                }
                $result = TestResult::query()
                    ->where('specimen_test_id', $data['specimen_test_id'])
                    ->first();

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
                    TestResult::create([
                        'specimen_test_id' => $data['specimen_test_id'],
                        'result_value' => $data['result_value'],
                        'unit' => $data['unit'] ?? null,
                        'reference_range' => $data['reference_range'] ?? null,
                        'flag' => $flag,
                        'entered_by' => $userId,
                        'entered_at' => now(),
                    ]);
                }
            }

            SpecimenTest::query()
                ->whereKey($data['specimen_test_id'])
                ->update(['status' => 'RESULT_ENTERED']);
        });

        return redirect()->route('results.entry');
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

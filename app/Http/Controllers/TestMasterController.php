<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Lab;
use App\Models\TestMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestMasterController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('tests.manage');

        $user = request()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $filters = [
            'search' => trim((string) request()->query('search', '')),
            'department_id' => trim((string) request()->query('department_id', '')),
            'sort' => trim((string) request()->query('sort', 'name')),
            'dir' => strtolower(trim((string) request()->query('dir', 'asc'))),
        ];

        $sortMap = [
            'name' => 'test_masters.name',
            'code' => 'test_masters.code',
            'price' => 'test_masters.price',
            'tat_days' => 'test_masters.tat_days',
            'department' => 'departments.name',
        ];

        $dir = $filters['dir'] === 'desc' ? 'desc' : 'asc';
        $sortKey = array_key_exists($filters['sort'], $sortMap) ? $filters['sort'] : 'name';

        $testsQuery = TestMaster::query()->with(['packageItems', 'parameters', 'lab']);
        if (!$isSuperAdmin) {
            $testsQuery->where(function ($query) use ($user) {
                $query->whereNull('lab_id');
                if ($user && $user->lab_id) {
                    $query->orWhere('lab_id', $user->lab_id);
                }
            });
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            if (str_contains($search, ' - ')) {
                $search = trim(explode(' - ', $search, 2)[0]);
            }
            $testsQuery->where(function ($query) use ($search) {
                $query->where('test_masters.code', 'like', '%' . $search . '%')
                    ->orWhere('test_masters.name', 'like', '%' . $search . '%')
                    ->orWhere('test_masters.sample_type', 'like', '%' . $search . '%')
                    ->orWhere('test_masters.tube_color', 'like', '%' . $search . '%')
                    ->orWhere('test_masters.container_type', 'like', '%' . $search . '%');
            });
        }

        if ($filters['department_id'] !== '') {
            $testsQuery->where('test_masters.department_id', $filters['department_id']);
        }

        if ($sortKey === 'department') {
            $testsQuery->leftJoin('departments', 'test_masters.department_id', '=', 'departments.id')
                ->orderBy($sortMap[$sortKey], $dir)
                ->select('test_masters.*');
        } else {
            $testsQuery->orderBy($sortMap[$sortKey], $dir);
        }

        $tests = $testsQuery->get();
        $tests = $tests
            ->unique(fn (TestMaster $test) => ($test->lab_id ?? 'global') . '|' . $test->code)
            ->values();
        $tests->each(function (TestMaster $test): void {
            $test->ensureCbcParameters();
        });

        $departmentsQuery = Department::query()->orderBy('name');
        if (!$isSuperAdmin) {
            $departmentsQuery->where(function ($query) use ($user) {
                $query->whereNull('lab_id');
                if ($user && $user->lab_id) {
                    $query->orWhere('lab_id', $user->lab_id);
                }
            });
        }
        $departments = $departmentsQuery->get();

        $allTestsQuery = TestMaster::query()->orderBy('name');
        if (!$isSuperAdmin) {
            $allTestsQuery->where(function ($query) use ($user) {
                $query->whereNull('lab_id');
                if ($user && $user->lab_id) {
                    $query->orWhere('lab_id', $user->lab_id);
                }
            });
        }

        $labPalette = ['global' => '#0b5a77'];
        foreach ($tests->pluck('lab')->filter() as $lab) {
            if ($lab instanceof Lab) {
                $labPalette[$lab->id] = $this->labColorFromName($lab->name ?? '');
            }
        }

        return view('admin.tests.index', [
            'tests' => $tests,
            'departments' => $departments,
            'allTests' => $allTestsQuery->get(),
            'filters' => $filters,
            'isSuperAdmin' => $isSuperAdmin,
            'labs' => $isSuperAdmin ? Lab::query()->orderBy('name')->get() : collect(),
            'globalTests' => $isSuperAdmin
                ? TestMaster::withoutGlobalScopes()->whereNull('lab_id')->orderBy('name')->get()
                : collect(),
            'labPalette' => $labPalette,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        $currentUser = $request->user();
        $labId = ($currentUser && $currentUser->isSuperAdmin()) ? null : ($currentUser?->lab_id);

        $rules = [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('test_masters', 'code')->where(fn ($query) => $query->where('lab_id', $labId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'sample_type' => ['nullable', 'string', 'max:100'],
            'tube_color' => ['nullable', 'string', 'max:255'],
            'container_type' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'tat_days' => ['nullable', 'integer', 'min:0'],
            'is_outsource' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_billing_visible' => ['nullable', 'boolean'],
            'is_package' => ['nullable', 'boolean'],
            'package_test_ids' => ['nullable', 'array'],
            'package_test_ids.*' => ['integer', 'exists:test_masters,id'],
        ];
        if ($currentUser && $currentUser->isSuperAdmin()) {
            $rules['copy_lab_ids'] = ['nullable', 'array'];
            $rules['copy_lab_ids.*'] = ['integer', 'exists:labs,id'];
        }

        $data = $request->validate($rules);

        $departmentName = Department::query()->whereKey($data['department_id'])->value('name') ?? '';
        $defaults = $this->inferSpecimenDefaults(
            $departmentName,
            $data['name'],
            $data['sample_type'] ?? null
        );

        $test = TestMaster::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'department_id' => $data['department_id'],
            'sample_type' => $data['sample_type'] ?? null,
            'tube_color' => !empty($data['tube_color']) ? $data['tube_color'] : ($defaults['tube_color'] ?? null),
            'container_type' => !empty($data['container_type']) ? $data['container_type'] : ($defaults['container_type'] ?? null),
            'price' => $data['price'] ?? 0,
            'tat_days' => $data['tat_days'] ?? null,
            'is_outsource' => (bool) ($data['is_outsource'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_billing_visible' => array_key_exists('is_billing_visible', $data) ? (bool) $data['is_billing_visible'] : true,
            'is_package' => (bool) ($data['is_package'] ?? false),
        ]);

        if ($test->is_package) {
            $packageItems = collect($data['package_test_ids'] ?? [])
                ->reject(fn ($id) => $id === $test->id)
                ->values()
                ->all();
            $test->packageItems()->sync($packageItems);
        }

        if ($currentUser && $currentUser->isSuperAdmin()) {
            $targetLabIds = $data['copy_lab_ids'] ?? [];
            if (empty($targetLabIds)) {
                $targetLabIds = Lab::query()->pluck('id')->all();
            }

            if (!empty($targetLabIds)) {
                $targetLabs = Lab::query()
                    ->whereIn('id', $targetLabIds)
                    ->get();
                foreach ($targetLabs as $lab) {
                    $this->copyTestToLab($test, $lab);
                }
            }
        }

        return redirect()->route('tests.index');
    }

    public function update(Request $request, TestMaster $test): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        $labId = $test->lab_id;

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('test_masters', 'code')
                    ->where(fn ($query) => $query->where('lab_id', $labId))
                    ->ignore($test->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'tube_color' => ['nullable', 'string', 'max:255'],
            'container_type' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'tat_days' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_billing_visible' => ['nullable', 'boolean'],
            'is_package' => ['nullable', 'boolean'],
            'package_test_ids' => ['nullable', 'array'],
            'package_test_ids.*' => ['integer', 'exists:test_masters,id'],
        ]);

        $test->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'department_id' => $data['department_id'],
            'tube_color' => $data['tube_color'] ?? null,
            'container_type' => $data['container_type'] ?? null,
            'price' => $data['price'] ?? 0,
            'tat_days' => $data['tat_days'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_billing_visible' => array_key_exists('is_billing_visible', $data) ? (bool) $data['is_billing_visible'] : $test->is_billing_visible,
            'is_package' => (bool) ($data['is_package'] ?? false),
        ]);

        if (!empty($data['is_package'])) {
            $packageItems = collect($data['package_test_ids'] ?? [])
                ->reject(fn ($id) => $id === $test->id)
                ->values()
                ->all();
            $test->packageItems()->sync($packageItems);
        } else {
            $test->packageItems()->sync([]);
        }

        return redirect()->route('tests.index');
    }

    public function copyToLabs(Request $request): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'test_id' => ['required', 'integer', 'exists:test_masters,id'],
            'lab_ids' => ['required', 'array', 'min:1'],
            'lab_ids.*' => ['integer', 'exists:labs,id'],
        ]);

        $test = TestMaster::withoutGlobalScopes()
            ->whereNull('lab_id')
            ->findOrFail($data['test_id']);

        $labs = Lab::query()
            ->whereIn('id', $data['lab_ids'])
            ->get();

        DB::transaction(function () use ($test, $labs): void {
            foreach ($labs as $lab) {
                $this->copyTestToLab($test, $lab);
            }
        });

        return redirect()->route('tests.index');
    }

    public function priceList(Request $request): Response|StreamedResponse
    {
        $this->requirePermission('tests.manage');

        $format = strtolower((string) $request->input('format', 'print'));
        $ids = collect($request->input('price_list_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $testsQuery = TestMaster::query()->orderBy('name');
        if (!empty($ids)) {
            $testsQuery->whereIn('id', $ids);
        }
        $tests = $testsQuery->get(['id', 'code', 'name', 'price']);

        if ($format === 'csv' || $format === 'excel') {
            $filename = 'price-list-' . now()->format('Ymd-Hi') . '.csv';

            return response()->streamDownload(function () use ($tests) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Test Name', 'Code', 'Price']);
                foreach ($tests as $test) {
                    fputcsv($handle, [$test->name, $test->code, $test->price]);
                }
                fclose($handle);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

            return response()->view('admin.tests.price_list', [
                'tests' => $tests,
            ]);
        }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        $ids = collect($request->input('delete_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($ids)) {
            return redirect()->route('tests.index');
        }

        $tests = TestMaster::query()->whereIn('id', $ids)->get();
        foreach ($tests as $test) {
            $test->packageItems()->sync([]);
            $test->delete();
        }

        return redirect()->route('tests.index')->with('status', 'Selected tests deleted.');
    }

    public function destroy(TestMaster $test): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        $test->packageItems()->sync([]);
        $test->delete();

        return redirect()->route('tests.index');
    }

    private function copyTestToLab(TestMaster $test, Lab $lab): TestMaster
    {
        $existing = TestMaster::withoutGlobalScopes()
            ->where('lab_id', $lab->id)
            ->where('code', $test->code)
            ->first();
        if ($existing) {
            return $existing;
        }

        $test->loadMissing(['department', 'parameters', 'packageItems']);
        $targetDepartment = $this->ensureDepartmentForLab($test->department, $lab);

        $clone = $test->replicate();
        $clone->lab_id = $lab->id;
        if ($targetDepartment) {
            $clone->department_id = $targetDepartment->id;
        }
        $clone->save();

        foreach ($test->parameters as $parameter) {
            $parameterClone = $parameter->replicate();
            $parameterClone->test_master_id = $clone->id;
            $parameterClone->save();
        }

        if ($test->is_package) {
            $packageItemIds = [];
            foreach ($test->packageItems as $item) {
                if ($item->code === $test->code) {
                    continue;
                }
                $targetItem = TestMaster::withoutGlobalScopes()
                    ->where('lab_id', $lab->id)
                    ->where('code', $item->code)
                    ->first();
                if (!$targetItem) {
                    $targetItem = $this->copyTestToLab($item, $lab);
                }
                if ($targetItem) {
                    $packageItemIds[] = $targetItem->id;
                }
            }
            $clone->packageItems()->sync($packageItemIds);
        }

        return $clone;
    }

    private function ensureDepartmentForLab(?Department $department, Lab $lab): ?Department
    {
        if (!$department) {
            return null;
        }

        $existing = Department::withoutGlobalScopes()
            ->where('lab_id', $lab->id)
            ->where('code', $department->code)
            ->first();
        if ($existing) {
            return $existing;
        }

        $clone = $department->replicate();
        $clone->lab_id = $lab->id;
        $clone->save();

        return $clone;
    }

    private function labColorFromName(string $name): string
    {
        $hash = crc32($name ?: 'labtech');
        return sprintf('#%06X', $hash & 0xFFFFFF);
    }

    private function inferSpecimenDefaults(string $departmentName, string $testName, ?string $sampleType): array
    {
        $department = strtolower(trim($departmentName));
        $name = strtolower(trim($testName));
        $sample = strtolower(trim((string) $sampleType));

        $defaults = [];

        if (str_contains($department, 'biochem')) {
            $defaults['tube_color'] = 'Red (Clot Activator)';
        } elseif (str_contains($department, 'haemat') || str_contains($department, 'hemat')) {
            $defaults['tube_color'] = 'Purple (EDTA K2/K3)';
        }

        if (str_contains($name, 'urine culture') || str_contains($sample, 'urine culture')) {
            $defaults['container_type'] = 'Sterile Container';
        } elseif (str_contains($name, 'urine') || str_contains($sample, 'urine')) {
            $defaults['container_type'] = 'Urine Cup';
        } elseif (str_contains($name, 'stool') || str_contains($sample, 'stool')) {
            $defaults['container_type'] = 'Stool Cup';
        } elseif (str_contains($name, 'sputum') || str_contains($sample, 'sputum')) {
            $defaults['container_type'] = 'Swab';
        }

        return $defaults;
    }
}

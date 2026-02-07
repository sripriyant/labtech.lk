<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\TestMaster;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('tests.manage');

        $packages = TestMaster::query()
            ->where('is_package', true)
            ->with('packageItems')
            ->orderBy('name')
            ->get();

        $tests = TestMaster::query()
            ->where('is_package', false)
            ->orderBy('name')
            ->get();

        $departments = Department::query()
            ->orderBy('name')
            ->get();

        return view('admin.tests.packages', [
            'packages' => $packages,
            'tests' => $tests,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        $currentUser = $request->user();
        $labId = ($currentUser && $currentUser->isSuperAdmin()) ? null : ($currentUser?->lab_id);

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('test_masters', 'code')->where(fn ($query) => $query->where('lab_id', $labId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'package_test_ids' => ['required', 'array', 'min:1'],
            'package_test_ids.*' => ['integer', 'exists:test_masters,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($data['department_id'])) {
            $data['department_id'] = Department::query()->orderBy('name')->value('id');
        }

        $package = TestMaster::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'department_id' => $data['department_id'],
            'price' => $data['price'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_package' => true,
        ]);

        $package->packageItems()->sync($data['package_test_ids']);

        return redirect()->route('packages.index');
    }

    public function update(Request $request, TestMaster $package): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        if (!$package->is_package) {
            abort(404);
        }

        $labId = $package->lab_id;

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('test_masters', 'code')
                    ->where(fn ($query) => $query->where('lab_id', $labId))
                    ->ignore($package->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'package_test_ids' => ['required', 'array', 'min:1'],
            'package_test_ids.*' => ['integer', 'exists:test_masters,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (empty($data['department_id'])) {
            $data['department_id'] = $package->department_id ?: Department::query()->orderBy('name')->value('id');
        }

        $package->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'department_id' => $data['department_id'],
            'price' => $data['price'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_package' => true,
        ]);

        $package->packageItems()->sync($data['package_test_ids']);

        return redirect()->route('packages.index');
    }

    public function destroy(TestMaster $package): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        if (!$package->is_package) {
            abort(404);
        }

        $package->packageItems()->sync([]);
        $package->delete();

        return redirect()->route('packages.index');
    }
}

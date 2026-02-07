<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('departments.manage');

        $departments = Department::query()
            ->orderBy('name')
            ->get();

        return view('admin.departments.index', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('departments.manage');

        $currentUser = $request->user();
        $labId = ($currentUser && $currentUser->isSuperAdmin()) ? null : ($currentUser?->lab_id);

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments', 'code')->where(fn ($query) => $query->where('lab_id', $labId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Department::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('departments.index');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\TestMaster;
use App\Models\TestParameter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestParameterController extends Controller
{
    public function index(TestMaster $test): View
    {
        $this->requirePermission('tests.manage');

        $test->load('parameters');

        return view('admin.tests.parameters', [
            'test' => $test,
        ]);
    }

    public function store(Request $request, TestMaster $test): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:50'],
            'reference_range' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'is_bold' => ['nullable', 'boolean'],
            'is_underline' => ['nullable', 'boolean'],
            'is_italic' => ['nullable', 'boolean'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'result_column' => ['nullable', 'integer', 'min:1', 'max:2'],
            'group_label' => ['nullable', 'string', 'max:100'],
            'display_type' => ['nullable', Rule::in(['label', 'text', 'number', 'textbox', 'dropdown'])],
            'font_size' => ['nullable', 'integer', 'min:8', 'max:48'],
            'dropdown_options' => ['nullable', 'string', 'max:1000'],
        ]);

        $test->parameters()->create([
            'name' => $data['name'],
            'symbol' => $data['symbol'] ?? null,
            'unit' => $data['unit'] ?? null,
            'reference_range' => $data['reference_range'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_visible' => (bool) ($data['is_visible'] ?? false),
            'is_bold' => (bool) ($data['is_bold'] ?? false),
            'is_underline' => (bool) ($data['is_underline'] ?? false),
            'is_italic' => (bool) ($data['is_italic'] ?? false),
            'text_color' => $data['text_color'] ?? null,
            'result_column' => $data['result_column'] ?? 1,
            'group_label' => $data['group_label'] ?? null,
            'display_type' => $data['display_type'] ?? 'textbox',
            'font_size' => $data['font_size'] ?? 14,
            'dropdown_options' => $this->parseDropdownOptions($data['dropdown_options'] ?? null),
        ]);

        return redirect()->route('tests.parameters', $test);
    }

    public function update(Request $request, TestMaster $test, TestParameter $parameter): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        if ($parameter->test_master_id !== $test->id) {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:50'],
            'reference_range' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'is_bold' => ['nullable', 'boolean'],
            'is_underline' => ['nullable', 'boolean'],
            'is_italic' => ['nullable', 'boolean'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'result_column' => ['nullable', 'integer', 'min:1', 'max:2'],
            'group_label' => ['nullable', 'string', 'max:100'],
            'display_type' => ['nullable', Rule::in(['label', 'text', 'number', 'textbox', 'dropdown'])],
            'font_size' => ['nullable', 'integer', 'min:8', 'max:48'],
            'dropdown_options' => ['nullable', 'string', 'max:1000'],
        ]);

        $parameter->update([
            'name' => $data['name'],
            'symbol' => $data['symbol'] ?? null,
            'unit' => $data['unit'] ?? null,
            'reference_range' => $data['reference_range'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'is_visible' => (bool) ($data['is_visible'] ?? false),
            'is_bold' => (bool) ($data['is_bold'] ?? false),
            'is_underline' => (bool) ($data['is_underline'] ?? false),
            'is_italic' => (bool) ($data['is_italic'] ?? false),
            'text_color' => $data['text_color'] ?? null,
            'result_column' => $data['result_column'] ?? 1,
            'group_label' => $data['group_label'] ?? null,
            'display_type' => $data['display_type'] ?? $parameter->display_type,
            'font_size' => $data['font_size'] ?? $parameter->font_size,
            'dropdown_options' => $this->parseDropdownOptions($data['dropdown_options'] ?? null),
        ]);

        return redirect()->route('tests.parameters', $test);
    }

    public function destroy(TestMaster $test, TestParameter $parameter): RedirectResponse
    {
        $this->requirePermission('tests.manage');

        if ($parameter->test_master_id !== $test->id) {
            abort(404);
        }

        $parameter->delete();

        return redirect()->route('tests.parameters', $test);
    }

    private function parseDropdownOptions(?string $raw): ?array
    {
        if ($raw === null) {
            return null;
        }

        $options = array_filter(array_map('trim', explode(',', $raw)));

        return empty($options) ? null : array_values($options);
    }
}

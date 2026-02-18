<?php

namespace App\Http\Controllers;

use App\Models\TestMaster;
use App\Models\TestParameter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'parameter_id' => ['nullable', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:50'],
            'unit' => ['nullable', 'string', 'max:50'],
            'reference_range' => ['nullable', 'string', 'max:100'],
            'reference_image' => ['nullable', 'image', 'max:5120'],
            'reference_image_clear' => ['nullable', 'boolean'],
            'reference_image_width' => ['nullable', 'integer', 'min:20', 'max:800'],
            'reference_image_height' => ['nullable', 'integer', 'min:20', 'max:800'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'show_interpretation' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'is_bold' => ['nullable', 'boolean'],
            'is_underline' => ['nullable', 'boolean'],
            'is_italic' => ['nullable', 'boolean'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'result_column' => ['nullable', 'integer', 'min:1', 'max:2'],
            'group_label' => ['nullable', 'string', 'max:100'],
            'display_type' => ['nullable', Rule::in(['label', 'text', 'number', 'textbox', 'dropdown', 'image'])],
            'font_size' => ['nullable', 'integer', 'min:8', 'max:48'],
            'dropdown_options' => ['nullable', 'string', 'max:1000'],
        ]);

        $payload = [
            'name' => $data['name'],
            'symbol' => $data['symbol'] ?? null,
            'unit' => $data['unit'] ?? null,
            'reference_range' => $data['reference_range'] ?? null,
            'reference_image_width' => $data['reference_image_width'] ?? null,
            'reference_image_height' => $data['reference_image_height'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'show_interpretation' => (bool) ($data['show_interpretation'] ?? true),
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
        ];

        $referenceImagePath = null;
        if ($request->hasFile('reference_image')) {
            $file = $request->file('reference_image');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $nameSlug = Str::slug($data['name'] ?? 'parameter');
            $fileName = $nameSlug . '-' . Str::random(8) . '.' . $extension;
            $referenceImagePath = $file->storeAs('test-parameter-references/' . $test->id, $fileName, 'public');
        }

        if (!empty($data['parameter_id'])) {
            $parameter = $test->parameters()->whereKey($data['parameter_id'])->firstOrFail();
            if (!empty($data['reference_image_clear']) && $parameter->reference_image_path) {
                Storage::disk('public')->delete($parameter->reference_image_path);
                $payload['reference_image_path'] = null;
            }
            if ($referenceImagePath !== null) {
                if ($parameter->reference_image_path) {
                    Storage::disk('public')->delete($parameter->reference_image_path);
                }
                $payload['reference_image_path'] = $referenceImagePath;
            }
            $parameter->update($payload);
        } else {
            if ($referenceImagePath !== null) {
                $payload['reference_image_path'] = $referenceImagePath;
            }
            $test->parameters()->create($payload);
        }

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
            'reference_image' => ['nullable', 'image', 'max:5120'],
            'reference_image_clear' => ['nullable', 'boolean'],
            'reference_image_width' => ['nullable', 'integer', 'min:20', 'max:800'],
            'reference_image_height' => ['nullable', 'integer', 'min:20', 'max:800'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'show_interpretation' => ['nullable', 'boolean'],
            'is_visible' => ['nullable', 'boolean'],
            'is_bold' => ['nullable', 'boolean'],
            'is_underline' => ['nullable', 'boolean'],
            'is_italic' => ['nullable', 'boolean'],
            'text_color' => ['nullable', 'string', 'max:20'],
            'result_column' => ['nullable', 'integer', 'min:1', 'max:2'],
            'group_label' => ['nullable', 'string', 'max:100'],
            'display_type' => ['nullable', Rule::in(['label', 'text', 'number', 'textbox', 'dropdown', 'image'])],
            'font_size' => ['nullable', 'integer', 'min:8', 'max:48'],
            'dropdown_options' => ['nullable', 'string', 'max:1000'],
        ]);

        $updatePayload = [
            'name' => $data['name'],
            'symbol' => $data['symbol'] ?? null,
            'unit' => $data['unit'] ?? null,
            'reference_range' => $data['reference_range'] ?? null,
            'reference_image_width' => $data['reference_image_width'] ?? null,
            'reference_image_height' => $data['reference_image_height'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'show_interpretation' => (bool) ($data['show_interpretation'] ?? $parameter->show_interpretation ?? true),
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
        ];

        if (!empty($data['reference_image_clear'])) {
            if ($parameter->reference_image_path) {
                Storage::disk('public')->delete($parameter->reference_image_path);
            }
            $updatePayload['reference_image_path'] = null;
        }

        if ($request->hasFile('reference_image')) {
            $file = $request->file('reference_image');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $nameSlug = Str::slug($data['name'] ?? 'parameter');
            $fileName = $nameSlug . '-' . Str::random(8) . '.' . $extension;
            $referenceImagePath = $file->storeAs('test-parameter-references/' . $test->id, $fileName, 'public');
            if ($parameter->reference_image_path) {
                Storage::disk('public')->delete($parameter->reference_image_path);
            }
            $updatePayload['reference_image_path'] = $referenceImagePath;
        }

        $parameter->update($updatePayload);

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

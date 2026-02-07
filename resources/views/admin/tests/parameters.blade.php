@extends('layouts.admin')

@php
    $pageTitle = 'Test Parameters';
    $displayTypes = [
        'textbox' => 'Text Box',
        'text' => 'Text',
        'number' => 'Number',
        'dropdown' => 'Dropdown',
        'label' => 'Label',
    ];
@endphp

@section('content')
    <style>
        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .test-header h2 {
            margin: 0;
            font-size: 16px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
        }
        .form-grid .span-2 {
            grid-column: span 2;
        }

        .form-grid label {
            font-size: 12px;
            color: var(--muted);
        }

        .form-grid input {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .form-grid select,
        .form-grid textarea {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
            background: #fff;
        }
        .form-grid textarea {
            min-height: 52px;
            resize: vertical;
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            margin-top: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead th {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid var(--line);
            background: #f0f4f7;
            color: var(--muted);
        }

        tbody td {
            padding: 8px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        .row-input {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .btn-small {
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            background: #0a6fb3;
            color: #fff;
            font-weight: 600;
        }
    </style>

    <div class="card">
        <div class="test-header">
            <h2>{{ $test->name }} ({{ $test->code }})</h2>
            <a class="btn-small" href="{{ route('tests.index') }}" style="text-decoration:none;">Back to Tests</a>
        </div>
        <form method="post" action="{{ route('tests.parameters.store', $test) }}">
            @csrf
            <div class="form-grid">
                <div>
                    <label>Parameter Name</label>
                    <input name="name" type="text" required>
                </div>
                <div>
                    <label>Symbol</label>
                    <input name="symbol" type="text">
                </div>
                <div>
                    <label>Unit</label>
                    <input name="unit" type="text">
                </div>
                <div>
                    <label>Reference Range</label>
                    <input name="reference_range" type="text">
                </div>
                <div>
                    <label>Remarks</label>
                    <input name="remarks" type="text">
                </div>
                <div>
                    <label>Display Type</label>
                    <select name="display_type">
                        @foreach ($displayTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Font Size (px)</label>
                    <input name="font_size" type="number" min="8" max="48" value="14">
                </div>
                <div class="span-2">
                    <label>Dropdown Values (comma separated)</label>
                    <textarea name="dropdown_options" placeholder="Only needed for dropdown type"></textarea>
                </div>
                <div>
                    <label>Sort Order</label>
                    <input name="sort_order" type="number" min="0" value="0">
                </div>
                <div>
                    <label>Visible</label>
                    <input name="is_visible" type="checkbox" value="1" checked style="width:auto;">
                </div>
                <div>
                    <label>Bold</label>
                    <input name="is_bold" type="checkbox" value="1" style="width:auto;">
                </div>
                <div>
                    <label>Underline</label>
                    <input name="is_underline" type="checkbox" value="1" style="width:auto;">
                </div>
                <div>
                    <label>Italic</label>
                    <input name="is_italic" type="checkbox" value="1" style="width:auto;">
                </div>
                <div>
                    <label>Text Color</label>
                    <select name="text_color">
                        <option value="#000000">Black</option>
                        <option value="#ffffff">White</option>
                    </select>
                </div>
                <div>
                    <label>Column</label>
                    <select name="result_column">
                        <option value="1">Column 1</option>
                        <option value="2">Column 2</option>
                    </select>
                </div>
                <div>
                    <label>Group Label</label>
                    <input name="group_label" type="text" placeholder="Optional section title">
                </div>
                <div>
                    <label>Active</label>
                    <input name="is_active" type="checkbox" value="1" checked style="width:auto;">
                </div>
            </div>
            <div style="margin-top:12px;">
                <button class="btn-small" type="submit">Add Parameter</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="test-header">
            <h2 style="margin-bottom:0;">Label (Static text)</h2>
        </div>
        <p style="font-size:13px;color:#4b5563;margin-top:0;">Use this form to add a label/description row. The label will render under “Display Type” and only stores text-related styling.</p>
        <form method="post" action="{{ route('tests.parameters.store', $test) }}">
            @csrf
            <input type="hidden" name="display_type" value="label">
            <div class="form-grid">
                <div>
                    <label>Parameter Name</label>
                    <input name="name" type="text" required placeholder="Label identifier">
                </div>
                <div class="span-2">
                    <label>Label Text</label>
                    <textarea name="remarks" rows="3" placeholder="Enter the static text that will appear on the report"></textarea>
                </div>
                <div>
                    <label>Font Size (px)</label>
                    <input name="font_size" type="number" min="8" max="48" value="12">
                </div>
                <div>
                    <label>Column</label>
                    <select name="result_column">
                        <option value="1">Column 1</option>
                        <option value="2">Column 2</option>
                    </select>
                </div>
                <div>
                    <label>Sort Order</label>
                    <input name="sort_order" type="number" min="0" value="0">
                </div>
                <div>
                    <label>Bold</label>
                    <input name="is_bold" type="checkbox" value="1" style="width:auto;">
                </div>
                <div>
                    <label>Underline</label>
                    <input name="is_underline" type="checkbox" value="1" style="width:auto;">
                </div>
                <div>
                    <label>Italic</label>
                    <input name="is_italic" type="checkbox" value="1" style="width:auto;">
                </div>
                <div>
                    <label>Text Color (hex)</label>
                    <input name="text_color" type="text" placeholder="#000000">
                </div>
                <div>
                    <label>Visible</label>
                    <input name="is_visible" type="checkbox" value="1" checked style="width:auto;">
                </div>
                <div>
                    <label>Active</label>
                    <input name="is_active" type="checkbox" value="1" checked style="width:auto;">
                </div>
            </div>
            <div style="margin-top:12px;">
                <button class="btn-small" type="submit">Add Label</button>
            </div>
        </form>
    </div>

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Symbol</th>
                    <th>Unit</th>
                    <th>Reference Range</th>
                    <th>Remarks</th>
                    <th>Display Type</th>
                    <th>Font Size</th>
                    <th>Dropdown Values</th>
                    <th>Sort</th>
                    <th>Column</th>
                    <th>Group Label</th>
                    <th>Visible</th>
                    <th>Bold</th>
                    <th>Underline</th>
                    <th>Italic</th>
                    <th>Text Color</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($test->parameters as $parameter)
                    <tr>
                        <form method="post" action="{{ route('tests.parameters.update', [$test, $parameter]) }}">
                            @csrf
                            <td><input class="row-input" name="name" value="{{ $parameter->name }}"></td>
                            <td><input class="row-input" name="symbol" value="{{ $parameter->symbol }}"></td>
                            <td><input class="row-input" name="unit" value="{{ $parameter->unit }}"></td>
                            <td><input class="row-input" name="reference_range" value="{{ $parameter->reference_range }}"></td>
                            <td><input class="row-input" name="remarks" value="{{ $parameter->remarks }}"></td>
                            <td>
                                <select class="row-input" name="display_type">
                                    @foreach ($displayTypes as $value => $label)
                                        <option value="{{ $value }}" {{ $parameter->display_type === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input class="row-input" name="font_size" type="number" min="8" max="48" value="{{ $parameter->font_size ?? 14 }}"></td>
                            <td>
                                <input class="row-input" name="dropdown_options" value="{{ implode(', ', $parameter->dropdown_options ?? []) }}" placeholder="comma separated">
                            </td>
                            <td><input class="row-input" name="sort_order" type="number" min="0" value="{{ $parameter->sort_order }}"></td>
                            <td>
                                <select class="row-input" name="result_column">
                                    <option value="1" {{ $parameter->result_column == 1 ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ $parameter->result_column == 2 ? 'selected' : '' }}>2</option>
                                </select>
                            </td>
                            <td><input class="row-input" name="group_label" value="{{ $parameter->group_label }}"></td>
                            <td>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="is_visible" value="1" {{ $parameter->is_visible ? 'checked' : '' }}>
                                    <span>{{ $parameter->is_visible ? 'Yes' : 'No' }}</span>
                                </label>
                            </td>
                            <td>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="is_bold" value="1" {{ $parameter->is_bold ? 'checked' : '' }}>
                                    <span>{{ $parameter->is_bold ? 'Yes' : 'No' }}</span>
                                </label>
                            </td>
                            <td>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="is_underline" value="1" {{ $parameter->is_underline ? 'checked' : '' }}>
                                    <span>{{ $parameter->is_underline ? 'Yes' : 'No' }}</span>
                                </label>
                            </td>
                            <td>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="is_italic" value="1" {{ $parameter->is_italic ? 'checked' : '' }}>
                                    <span>{{ $parameter->is_italic ? 'Yes' : 'No' }}</span>
                                </label>
                            </td>
                            <td><input class="row-input" name="text_color" value="{{ $parameter->text_color }}"></td>
                            <td>
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input type="checkbox" name="is_active" value="1" {{ $parameter->is_active ? 'checked' : '' }}>
                                    <span>{{ $parameter->is_active ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </td>
                            <td>
                                <button class="btn-small" type="submit">Save</button>
                                <button class="btn-small" type="submit" formaction="{{ route('tests.parameters.destroy', [$test, $parameter]) }}" formmethod="post" onclick="return confirm('Delete this parameter?');" style="background:#b63b3b;">Delete</button>
                            </td>
                        </form>
                    </tr>
                @empty
                    <tr>
                        <td colspan="18">No parameters added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

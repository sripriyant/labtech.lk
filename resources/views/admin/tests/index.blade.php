@extends('layouts.admin')

@php
    $pageTitle = 'Test Master';
    $labPalette = $labPalette ?? ['global' => '#0b5a77'];
    $tubePresets = [
        'Red (Clot Activator)',
        'Orange (Coagulant)',
        'Purple (EDTA K2/K3)',
        'Pink (EDTA K2)',
        'Blue (Sodium Citrate 3.2%)',
        'Green (Heparin)',
        'Grey (Fluoride Oxalate)',
        'Yellow (Gel Separator)',
        'Black (ESR)',
        'White (PPT)',
    ];
    $containerPresets = [
        'Sterile Container',
        'Plain Container',
        'Swab',
        'Urine Cup',
        'Stool Cup',
        'Sputum Cup',
        'Blood Culture Bottle',
        'Transport Media',
    ];
@endphp

@section('content')
    <style>
        .test-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            align-items: end;
        }

        .test-grid .span-2 {
            grid-column: span 2;
        }

        .test-grid label {
            font-size: 13px;
            color: var(--muted);
            font-weight: 600;
        }

        .test-grid input,
        .test-grid select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(13, 44, 93, 0.2);
            font-size: 13px;
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .test-grid input:focus,
        .test-grid select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px rgba(8, 185, 243, 0.15);
        }

        .test-grid select[multiple] {
            min-height: 120px;
        }

        .preset-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 8px;
            margin-top: 8px;
            max-height: 140px;
            overflow: auto;
            padding-right: 4px;
        }

        .preset-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #374151;
        }

        .preset-item input[type="checkbox"] {
            accent-color: #0a6fb3;
        }

        .filter-bar {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 120px auto;
            gap: 10px;
            align-items: end;
            margin-bottom: 12px;
        }

        .filter-bar .btn,
        .filter-bar .btn.secondary {
            padding: 7px 12px;
            font-size: 12px;
            border-radius: 6px;
        }

        .filter-bar .btn.secondary {
            background: #f1f5f8;
            border: 1px solid var(--line);
            color: #32414a;
        }

        @media (max-width: 900px) {
            .filter-bar {
                grid-template-columns: 1fr;
            }

            .test-grid {
                grid-template-columns: 1fr;
            }

            .test-grid .span-2 {
                grid-column: span 1;
            }
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
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

        .lab-badge {
            --badge-color: #0b5a77;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 4px 10px;
            border: 1px solid var(--badge-color);
            color: var(--badge-color);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.02em;
            background: rgba(11, 90, 119, 0.08);
        }

        .lab-badge::before {
            content: "";
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--badge-color);
            display: inline-block;
        }

        .lab-badge--global {
            --badge-color: #0b5a77;
        }

        .row-select {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
            background: #fff;
        }

        .row-actions {
            display: flex;
            gap: 6px;
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

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge.active {
            background: #e8f6f1;
            color: #0b5a40;
        }

        .badge.inactive {
            background: #f3f4f6;
            color: #475569;
        }

        .price-list-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 12px 0;
            flex-wrap: wrap;
        }

        .price-list-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .price-list-actions .btn-small {
            background: #0b5a77;
        }

        .price-list-actions .btn-small.secondary {
            background: #f3f4f6;
            color: #1f2937;
            border: 1px solid #e5e7eb;
        }

        .price-list-note {
            font-size: 12px;
            color: var(--muted);
        }
        .form-actions {
            margin-top: 16px;
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }
        .form-actions label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }
        .test-panel {
            background: linear-gradient(180deg, rgba(11, 90, 119, 0.09), rgba(255, 255, 255, 0.95));
            border-radius: 32px;
            padding: clamp(28px, 3vw, 40px);
            border: 1px solid rgba(11, 90, 119, 0.12);
            box-shadow: 0 30px 60px rgba(11, 90, 119, 0.15);
        }

        .test-panel__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
        }

        .eyebrow-text {
            text-transform: uppercase;
            letter-spacing: 0.35em;
            font-size: 11px;
            color: rgba(11, 90, 119, 0.6);
            margin-bottom: 6px;
            font-weight: 600;
        }

        .test-panel__header h1 {
            margin: 0;
            font-size: clamp(26px, 4vw, 34px);
            color: #0b5a77;
            letter-spacing: 0.02em;
        }

        .panel-lede {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
            max-width: 480px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .outline-btn {
            border: 1px solid rgba(11, 90, 119, 0.4);
            background: transparent;
            padding: 8px 18px;
            border-radius: 999px;
            font-weight: 600;
            color: #0b5a77;
            cursor: pointer;
        }

        .panel-section {
            border-top: 1px solid rgba(11, 90, 119, 0.12);
            padding-top: 20px;
        }

        .panel-section:first-of-type {
            border-top: none;
            padding-top: 0;
        }

        .section-title {
            text-transform: uppercase;
            letter-spacing: 0.25em;
            font-size: 11px;
            color: rgba(11, 90, 119, 0.7);
            margin-bottom: 14px;
            font-weight: 700;
        }

        .panel-section--presets .section-title {
            margin-bottom: 12px;
        }

        .preset-grid-wrap {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }

        .preset-block {
            background: #fff;
            border-radius: 20px;
            padding: 16px;
            border: 1px solid #e5ecf3;
            box-shadow: inset 0 0 0 1px rgba(11, 90, 119, 0.06);
        }

        .preset-block h3 {
            margin: 0 0 12px;
            font-size: 14px;
            color: #0b5a77;
            letter-spacing: 0.04em;
        }

        .panel-section--actions {
            border-top: none;
            padding-top: 12px;
            display: flex;
            justify-content: flex-end;
        }

        .panel-divider {
            border-top: 1px solid rgba(11, 90, 119, 0.08);
            margin-top: 20px;
        }
    </style>

    <section class="test-panel">
        <form method="post" action="{{ route('tests.store') }}">
            @csrf
            <header class="test-panel__header">
                <div>
                    <div class="eyebrow-text">Global test master</div>
                    <h1>Test Master</h1>
                    <p class="panel-lede">Define tests, pricing, and visibility before publishing across branches.</p>
                </div>
                <div class="header-actions">
                    <span class="lab-badge lab-badge--global">Global</span>
                    <button type="reset" class="outline-btn">Reset fields</button>
                </div>
            </header>
            <div class="panel-section">
                <div class="section-title">Primary details</div>
                <div class="test-grid">
                    <div>
                        <label for="code">Test Code</label>
                        <input id="code" name="code" type="text" required>
                    </div>
                    <div>
                        <label for="name">Test Name</label>
                        <input id="name" name="name" type="text" required>
                    </div>
                    <div>
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" required>
                            <option value="">Select</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="sample_type">Sample Type</label>
                        <input id="sample_type" name="sample_type" type="text">
                    </div>
                    <div>
                        <label for="price">Price</label>
                        <input id="price" name="price" type="number" step="0.01" min="0">
                    </div>
                    <div>
                        <label for="tat_days">TAT (Days)</label>
                        <input id="tat_days" name="tat_days" type="number" min="0">
                    </div>
                    @if (!empty($isSuperAdmin) && $isSuperAdmin)
                        <div>
                            <label for="copy_lab_ids">Copy to Labs (optional)</label>
                            <select id="copy_lab_ids" name="copy_lab_ids[]" multiple>
                                @foreach ($labs as $lab)
                                    <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
            <div class="panel-section panel-section--presets">
                <div class="section-title">Tube &amp; Container presets</div>
                <div class="preset-grid-wrap">
                    <div class="preset-block">
                        <h3>Tube Colors</h3>
                        <label for="tube_color">Tube Color</label>
                        <input id="tube_color" name="tube_color" type="text" placeholder="Comma-separated" list="tube_presets">
                        <datalist id="tube_presets">
                            @foreach ($tubePresets as $tube)
                                <option value="{{ $tube }}"></option>
                            @endforeach
                        </datalist>
                        <div class="preset-grid" data-preset-target="tube_color">
                            @foreach ($tubePresets as $tube)
                                <label class="preset-item">
                                    <input type="checkbox" value="{{ $tube }}">
                                    <span>{{ $tube }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="preset-block">
                        <h3>Container Types</h3>
                        <label for="container_type">Container Type</label>
                        <input id="container_type" name="container_type" type="text" placeholder="Comma-separated" list="container_presets">
                        <datalist id="container_presets">
                            @foreach ($containerPresets as $container)
                                <option value="{{ $container }}"></option>
                            @endforeach
                        </datalist>
                        <div class="preset-grid" data-preset-target="container_type">
                            @foreach ($containerPresets as $container)
                                <label class="preset-item">
                                    <input type="checkbox" value="{{ $container }}">
                                    <span>{{ $container }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-section panel-section--actions">
                <div class="form-actions">
                    <label><input type="checkbox" name="is_outsource" value="1"> Outsource</label>
                    <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                    <label><input type="checkbox" name="is_billing_visible" value="1" checked> Show in Billing</label>
                    <button class="btn" type="submit">Add Test</button>
                </div>
            </div>
        </form>
    </section>

    @if (!empty($isSuperAdmin) && $isSuperAdmin)
        <div class="card" style="margin-top:20px;">
            <form method="post" action="{{ route('tests.copy') }}">
                @csrf
                <div class="test-grid">
                    <div>
                        <label for="copy_test_id">Copy Existing Global Test</label>
                        <select id="copy_test_id" name="test_id" required>
                            <option value="">Select</option>
                            @foreach ($globalTests as $test)
                                <option value="{{ $test->id }}">{{ $test->code }} - {{ $test->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="copy_to_labs">Copy to Labs</label>
                        <select id="copy_to_labs" name="lab_ids[]" multiple required>
                            @foreach ($labs as $lab)
                                <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:16px;">
                    <button class="btn" type="submit">Copy Test</button>
                </div>
            </form>
        </div>
    @endif

    <div class="card" style="margin-top:20px;">
        <form id="filterForm" method="get" action="{{ route('tests.index') }}" class="filter-bar">
            <div>
                <label for="search">Search</label>
                <input id="search" name="search" type="text" value="{{ $filters['search'] ?? '' }}" placeholder="Code, name, sample type, tube color" list="tests_suggest" autocomplete="off">
                <datalist id="tests_suggest">
                    @foreach ($allTests as $test)
                        <option value="{{ $test->code }} - {{ $test->name }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label for="filter_department">Department</label>
                <select id="filter_department" name="department_id">
                    <option value="">All</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? '') == $department->id)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sort">Sort By</label>
                <select id="sort" name="sort">
                    <option value="name" @selected(($filters['sort'] ?? '') === 'name')>Name</option>
                    <option value="code" @selected(($filters['sort'] ?? '') === 'code')>Code</option>
                    <option value="department" @selected(($filters['sort'] ?? '') === 'department')>Department</option>
                    <option value="price" @selected(($filters['sort'] ?? '') === 'price')>Price</option>
                    <option value="tat_days" @selected(($filters['sort'] ?? '') === 'tat_days')>TAT (Days)</option>
                </select>
            </div>
            <div>
                <label for="dir">Direction</label>
                <select id="dir" name="dir">
                    <option value="asc" @selected(($filters['dir'] ?? 'asc') === 'asc')>Ascending</option>
                    <option value="desc" @selected(($filters['dir'] ?? '') === 'desc')>Descending</option>
                </select>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button class="btn" type="submit">Apply</button>
                <a class="btn secondary" href="{{ route('tests.index') }}">Reset</a>
            </div>
        </form>
        <div class="price-list-bar">
            <label style="display:flex;align-items:center;gap:8px;font-size:12px;">
                <input type="checkbox" id="priceListSelectAll">
                Select all for price list
            </label>
            <div class="price-list-actions">
                <form id="price-list-form" method="post" action="{{ route('tests.price_list') }}">
                    @csrf
                </form>
                <form id="testsBulkDelete" method="post" action="{{ route('tests.bulk_delete') }}">
                    @csrf
                </form>
                <button class="btn-small" type="submit" form="price-list-form" name="format" value="print" formtarget="_blank">Print Price List</button>
                <button class="btn-small secondary" type="submit" form="price-list-form" name="format" value="csv">Export Excel (CSV)</button>
                <button class="btn-small secondary" type="button" id="testsBulkDeleteButton" style="background:#b63b3b;color:#fff;">Delete Selected</button>
            </div>
            <div class="price-list-note">If nothing is selected, all tests will be included.</div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Lab</th>
                        <th>Department</th>
                        <th>Tube</th>
                        <th>Container</th>
                        <th>Price</th>
                        <th>TAT</th>
                        <th>Status</th>
                        <th>Billing</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $plainTests = $tests->where('is_package', false);
                    @endphp
                    @forelse ($plainTests as $test)
                            <tr>
                                <form method="post" action="{{ route('tests.update', $test) }}">
                                    @csrf
                                    <td>
                                        <input type="checkbox" name="price_list_ids[]" value="{{ $test->id }}" form="price-list-form" class="price-list-item">
                                    </td>
                                    <td><input class="row-input" name="code" value="{{ $test->code }}"></td>
                                    <td><input class="row-input" name="name" value="{{ $test->name }}"></td>
                                    <td>
                                        @if ($test->lab)
                                            <span
                                                class="lab-badge"
                                                style="--badge-color: {{ $labPalette[$test->lab->id] ?? '#0a6fb3' }};"
                                            >
                                                {{ $test->lab->name }}
                                            </span>
                                        @else
                                            <span class="lab-badge lab-badge--global">Global</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select class="row-select" name="department_id">
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}" @selected($test->department_id === $department->id)>
                                                    {{ $department->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input class="row-input" name="tube_color" value="{{ $test->tube_color }}" list="tube_presets">
                                    </td>
                                    <td>
                                        <input class="row-input" name="container_type" value="{{ $test->container_type }}" list="container_presets">
                                    </td>
                                    <td><input class="row-input" name="price" type="number" step="0.01" min="0" value="{{ $test->price }}"></td>
                                    <td><input class="row-input" name="tat_days" type="number" min="0" value="{{ $test->tat_days }}"></td>
                                    <td>
                                        <label style="display:flex;align-items:center;gap:6px;">
                                            <input type="checkbox" name="is_active" value="1" {{ $test->is_active ? 'checked' : '' }}>
                                            @if ($test->is_active)
                                                <span class="badge active">Active</span>
                                            @else
                                                <span class="badge inactive">Inactive</span>
                                            @endif
                                        </label>
                                    </td>
                                    <td>
                                        <label style="display:flex;align-items:center;gap:6px;">
                                            <input type="checkbox" name="is_billing_visible" value="1" {{ $test->is_billing_visible ? 'checked' : '' }}>
                                            <span>{{ $test->is_billing_visible ? 'Show' : 'Hide' }}</span>
                                        </label>
                                    </td>
                                    <td class="row-actions">
                                        <a class="btn-small" href="{{ route('tests.parameters', $test) }}" style="background:#0b5a77;text-decoration:none;">Parameters</a>
                                        <button class="btn-small" type="submit">Save</button>
                                        <button class="btn-small" type="submit" formaction="{{ route('tests.destroy', $test) }}" formmethod="post" onclick="return confirm('Delete this test?');" style="background:#b63b3b;">Delete</button>
                                    </td>
                                </form>
                            </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="padding:12px 10px;">No tests added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            var form = document.getElementById('filterForm');
            var searchInput = document.getElementById('search');
            var deptSelect = document.getElementById('filter_department');
            var sortSelect = document.getElementById('sort');
            var dirSelect = document.getElementById('dir');
            var debounce;

            function normalizeSearch() {
                if (!searchInput) {
                    return;
                }
                var value = (searchInput.value || '').trim();
                var split = value.split(' - ');
                if (split.length > 1 && split[0]) {
                    searchInput.value = split[0].trim();
                }
            }

            function submitNow() {
                if (!form) {
                    return;
                }
                normalizeSearch();
                form.submit();
            }

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    if (debounce) {
                        clearTimeout(debounce);
                    }
                    debounce = setTimeout(submitNow, 400);
                });
                searchInput.addEventListener('change', submitNow);
            }

            [deptSelect, sortSelect, dirSelect].forEach(function (el) {
                if (!el) {
                    return;
                }
                el.addEventListener('change', submitNow);
            });

            var selectAll = document.getElementById('priceListSelectAll');
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    document.querySelectorAll('.price-list-item').forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                });
            }

            var bulkDeleteForm = document.getElementById('testsBulkDelete');
            var bulkDeleteButton = document.getElementById('testsBulkDeleteButton');
            if (bulkDeleteForm && bulkDeleteButton) {
                bulkDeleteButton.addEventListener('click', function () {
                    var selected = Array.from(document.querySelectorAll('.price-list-item:checked'))
                        .map(function (checkbox) { return checkbox.value; });
                    if (!selected.length) {
                        alert('Select at least one test to delete.');
                        return;
                    }
                    if (!confirm('Delete the selected tests? This cannot be undone.')) {
                        return;
                    }
                    bulkDeleteForm.querySelectorAll('input[name="delete_ids[]"]').forEach(function (node) {
                        node.remove();
                    });
                    selected.forEach(function (value) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'delete_ids[]';
                        input.value = value;
                        bulkDeleteForm.appendChild(input);
                    });
                    bulkDeleteForm.submit();
                });
            }

            document.querySelectorAll('.preset-grid').forEach(function (grid) {
                var targetId = grid.dataset.presetTarget;
                var targetInput = document.getElementById(targetId);
                if (!targetInput) {
                    return;
                }
                var updateValue = function () {
                    var selected = Array.from(grid.querySelectorAll('input[type="checkbox"]:checked'))
                        .map(function (checkbox) { return checkbox.value; });
                    targetInput.value = selected.join(', ');
                };
                grid.addEventListener('change', updateValue);
            });
        })();
    </script>
@endsection

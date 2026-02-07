@extends('layouts.admin')

@php
    $pageTitle = 'Test Result Entry';
@endphp

@section('content')
    <style>
        .panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 16px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 12px;
        }

        .field input,
        .field select,
        .field textarea {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .split {
            display: grid;
            grid-template-columns: 1.1fr 1.9fr;
            gap: 12px;
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead th {
            background: #f0f4f7;
            color: var(--muted);
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid var(--line);
        }

        tbody td {
            padding: 8px;
            border-bottom: 1px solid var(--line);
        }

        .row-input {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .error-note {
            display: none;
            margin-top: 8px;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #f2b6b6;
            background: #fff4f4;
            color: #a01515;
            font-size: 12px;
        }

        .diff-summary {
            display: flex;
            gap: 12px;
            align-items: center;
            font-weight: 600;
            font-size: 13px;
        }

        .diff-status {
            font-weight: 700;
        }

        .diff-status--missing,
        .diff-status--excess {
            color: #c01919;
        }

        .diff-status--ok {
            color: #0f7a47;
        }

        tbody tr.active {
            background: #e8f2f8;
        }

        .patient-group {
            background: rgba(14, 165, 233, 0.08);
        }

        .patient-group td {
            font-weight: 700;
            color: #0f172a;
            border-left: 4px solid var(--patient-color);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border: 1px solid transparent;
        }

        .status-badge.pending {
            background: #fff3e6;
            color: #b45309;
            border-color: #f5d0a6;
        }

        .status-badge.partial {
            background: #e0f2fe;
            color: #0369a1;
            border-color: #bae6fd;
        }

        .status-badge.saved {
            background: #e7f6ee;
            color: #0f7a47;
            border-color: #b7e3cc;
        }

        .btn {
            background: #0a6fb3;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }

        .lipid-reference-wrapper {
            margin-top: 16px;
            display: none;
        }

        .lipid-reference-panel {
            border: 1px solid #d0e2f0;
            border-radius: 10px;
            background: #fff;
            padding: 12px;
            font-size: 11px;
            line-height: 1.4;
        }

        .lipid-reference-panel--compact {
            padding: 8px;
            font-size: 10px;
            margin-top: 15cm;
            margin-bottom: 22cm;
            max-height: 7cm;
            overflow: hidden;
        }

        .lipid-reference-panel .lipid-reference-note {
            margin: 0 0 8px;
            font-weight: 600;
        }

        .lipid-reference-table-wrapper {
            overflow-x: auto;
        }

        .lipid-reference-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            text-align: center;
        }

        .lipid-reference-table th,
        .lipid-reference-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
        }

        .lipid-reference-table--compact th,
        .lipid-reference-table--compact td {
            padding: 4px;
            font-size: 8px;
        }

        .lipid-reference-table--smaller {
            transform: scale(0.7);
            transform-origin: top center;
            display: block;
        }

        .lipid-reference-footnote {
            margin-top: 10px;
            font-size: 11px;
            text-align: center;
            font-weight: 600;
        }

        .lipid-reference-footnote--compact {
            font-size: 9px;
            font-weight: 500;
        }

        .lipid-reference-image {
            display: none;
        }

        @media (max-width: 1100px) {
            .filters {
                grid-template-columns: repeat(2, 1fr);
            }

            .split {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="panel">
        <div class="filters">
            <div class="field">
                <label>From</label>
                <input id="filterFrom" type="date">
            </div>
            <div class="field">
                <label>To</label>
                <input id="filterTo" type="date">
            </div>
            <div class="field">
                <label>Specimen No</label>
                <input id="filterSpecimen" type="text" placeholder="Specimen No">
            </div>
            <div class="field">
                <label>Department</label>
                <input id="filterDepartment" type="text" placeholder="Department">
            </div>
            <div class="field">
                <label>Sort</label>
                <select id="sortSelect">
                    <option value="latest_desc" @selected(($sort ?? '') === 'latest_desc')>Latest Billing First</option>
                    <option value="latest_asc" @selected(($sort ?? '') === 'latest_asc')>Oldest Billing First</option>
                    <option value="pending_first" @selected(($sort ?? '') === 'pending_first')>Pending First</option>
                    <option value="patient_asc" @selected(($sort ?? '') === 'patient_asc')>Patient: A-Z</option>
                    <option value="patient_desc" @selected(($sort ?? '') === 'patient_desc')>Patient: Z-A</option>
                    <option value="uhid_asc" @selected(($sort ?? '') === 'uhid_asc')>UHID: A-Z</option>
                    <option value="uhid_desc" @selected(($sort ?? '') === 'uhid_desc')>UHID: Z-A</option>
                    <option value="specimen_asc" @selected(($sort ?? '') === 'specimen_asc')>Specimen No: A-Z</option>
                    <option value="specimen_desc" @selected(($sort ?? '') === 'specimen_desc')>Specimen No: Z-A</option>
                    <option value="test_asc" @selected(($sort ?? '') === 'test_asc')>Test: A-Z</option>
                    <option value="test_desc" @selected(($sort ?? '') === 'test_desc')>Test: Z-A</option>
                    <option value="status_asc" @selected(($sort ?? '') === 'status_asc')>Status: Pending to Saved</option>
                    <option value="status_desc" @selected(($sort ?? '') === 'status_desc')>Status: Saved to Pending</option>
                </select>
            </div>
        </div>

        <div class="split">
            <div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Specimen No</th>
                                <th>Patient Name</th>
                                <th>Test</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="specimenList">
                            @php
                                $patientPalette = ['#0ea5e9', '#8b5cf6', '#10b981', '#f97316', '#ef4444', '#14b8a6', '#a855f7', '#f59e0b'];
                                $groupedItems = $items->groupBy(fn ($item) => $item->specimen?->patient_id ?? 0);
                            @endphp
                            @forelse ($groupedItems as $patientId => $groupItems)
                                @php
                                    $first = $groupItems->first();
                                    $patientName = $first->specimen->patient->name ?? '-';
                                     $patientSex = $first->specimen->patient->sex ?? '-';
                                     $color = $patientPalette[abs((int) $patientId) % count($patientPalette)];
                                 @endphp
                                 <tr class="patient-group" data-group="patient" data-patient="{{ $patientName }}" style="--patient-color: {{ $color }};">
                                     <td colspan="4">{{ $patientName }} ({{ $patientSex }})</td>
                                </tr>
                                @foreach ($groupItems as $item)
                                    @php
                                        $status = $item->entry_status ?? 'pending';
                                    @endphp
                                    <tr data-id="{{ $item->id }}"
                                        data-group="item"
                                        data-patient="{{ $patientName }}"
                                        data-specimen="{{ $item->specimen->specimen_no ?? '-' }}"
                                        data-age-display="{{ $item->specimen?->age_display ?? '' }}"
                                        data-age-unit="{{ $item->specimen?->age_unit ?? '' }}"
                                        data-age-years="{{ $item->specimen?->age_years ?? '' }}"
                                        data-sex="{{ $patientSex }}"
                                        data-test="{{ $item->testMaster->name ?? '-' }}"
                                        data-status="{{ $status }}"
                                        data-repeated="{{ $item->is_repeated ? '1' : '0' }}"
                                        data-confirmed="{{ $item->is_confirmed ? '1' : '0' }}">
                                        <td>{{ $item->specimen->specimen_no ?? '-' }}</td>
                                        <td>{{ $patientName }}</td>
                                        <td>{{ $item->testMaster->name ?? '-' }}</td>
                                        <td><span class="status-badge {{ $status }}">{{ $status }}</span></td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="4">No results pending entry.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <form id="entryForm" method="post" action="{{ route('results.entry.store') }}">
                    @csrf
                    <input type="hidden" name="specimen_test_id" id="selectedSpecimenTest">
                    <div class="field">
                        <label>Patient Name</label>
                        <input id="patientName" name="patient_name" type="text" placeholder="Patient name">
                    </div>
            <div class="field">
                <label>Age</label>
                <input id="patientAge" type="text" placeholder="Age" readonly>
            </div>
                    <div class="field">
                        <label>Test</label>
                        <input id="testName" type="text" readonly>
                    </div>
                    <div id="singleResultBlock" class="field">
                        <label>Result Value</label>
                        <textarea id="singleResultValue" name="result_value" rows="6" required></textarea>
                    </div>
                    <div class="field">
                        <label style="display:flex;gap:6px;align-items:center;">
                            <input type="checkbox" id="isRepeatedConfirmed">
                            Repeated &amp; Confirmed
                        </label>
                        <input type="hidden" name="is_repeated" id="isRepeated" value="0">
                        <input type="hidden" name="is_confirmed" id="isConfirmed" value="0">
                    </div>
                    <div id="parameterBlock" class="table-wrap" style="display:none;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Result</th>
                                    <th>Unit</th>
                                    <th>Ref Range</th>
                                    <th>Remarks</th>
                                    <th>Flag</th>
                                </tr>
                            </thead>
                        <tbody id="parameterRows"></tbody>
                    </table>
                </div>
                    @php $differentialTotalError = $errors->first('differential_total'); @endphp
                    <div class="diff-summary" style="display:none;">
                        <div id="diffTotal" class="diff-total">Total: 0.0% (should be 100%)</div>
                        <div id="diffStatus" class="diff-status"></div>
                    </div>
                    <div id="diffError" class="error-note" style="display:{{ $differentialTotalError ? 'block' : 'none' }};">
                        {{ $differentialTotalError }}
                    </div>
                    <div class="lipid-reference-image">
                        <img src="{{ asset('images/lipid-reference-table.png') }}" alt="Lipid reference table" loading="lazy">
                    </div>
                    <div style="margin-top:10px;">
                        <button class="btn" type="submit">Save Result</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        use Illuminate\Support\Str;

        $normalizeParameterName = function (?string $name): string {
            return (string) Str::of($name ?? '')
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '');
        };

        $parameterMap = $items->mapWithKeys(function ($item) use ($normalizeParameterName) {
            $parameters = $item->testMaster?->parameters ?? collect();
            $parameters = $parameters
                ->sortBy(fn ($parameter) => sprintf('%05d-%010d', (int) ($parameter->sort_order ?? 0), (int) ($parameter->id ?? 0)))
                ->values();
            $results = $item->parameterResults?->keyBy('test_parameter_id') ?? collect();
            $seen = [];
            $parameters = $parameters->filter(function ($parameter) use (&$seen, $normalizeParameterName) {
                $normalized = $normalizeParameterName($parameter->name);
                if ($normalized === '') {
                    return true;
                }
                if (in_array($normalized, $seen, true)) {
                    return false;
                }
                $seen[] = $normalized;
                return true;
            })->values();
            return [
                $item->id => $parameters->map(function ($parameter) use ($results) {
                    $result = $results->get($parameter->id);
                return [
                    'id' => $parameter->id,
                    'name' => $parameter->name,
                    'symbol' => $parameter->symbol,
                    'unit' => $parameter->unit,
                    'reference_range' => $parameter->reference_range,
                    'remarks' => $parameter->remarks,
                    'result_value' => $result?->result_value,
                    'result_unit' => $result?->unit,
                    'result_reference_range' => $result?->reference_range,
                    'result_remarks' => $result?->remarks,
                    'flag' => $result?->flag,
                    'display_type' => $parameter->display_type ?? 'textbox',
                    'font_size' => $parameter->font_size ?? 14,
                    'dropdown_options' => $parameter->dropdown_options ?? [],
                ];
                })->values(),
            ];
        })->all();
    @endphp

    <script>
        (function () {
            var specimenList = document.getElementById('specimenList');
            var selectedInput = document.getElementById('selectedSpecimenTest');
            var patientName = document.getElementById('patientName');
            var testNameInput = document.getElementById('testName');
            var patientAge = document.getElementById('patientAge');
            var filterSpecimen = document.getElementById('filterSpecimen');
            var filterDepartment = document.getElementById('filterDepartment');
            var sortSelect = document.getElementById('sortSelect');
            var singleResultBlock = document.getElementById('singleResultBlock');
            var singleResultValue = document.getElementById('singleResultValue');
            var parameterBlock = document.getElementById('parameterBlock');
            var parameterRows = document.getElementById('parameterRows');
            var isRepeated = document.getElementById('isRepeated');
            var isConfirmed = document.getElementById('isConfirmed');
            var isRepeatedConfirmed = document.getElementById('isRepeatedConfirmed');
            var entryForm = document.getElementById('entryForm');
            var diffError = document.getElementById('diffError');
            var diffTotalLabel = document.getElementById('diffTotal');
            var diffStatusLabel = document.getElementById('diffStatus');
            var diffSummary = document.querySelector('.diff-summary');
            var differentialTestKeywords = ['FULL BLOOD COUNT'];
            var isDifferentialVisible = false;
            var differentialParameterNames = ['NEUTROPHILS', 'LYMPHOCYTES', 'EOSINOPHILS', 'MONOCYTES', 'BASOPHILS'];
            function shouldShowDifferentialSummary(testName) {
                if (!testName) {
                    return false;
                }
                var normalized = (testName || '').toString().toUpperCase();
                return differentialTestKeywords.some(function (keyword) {
                    return normalized.includes(keyword);
                });
            }
            function setDifferentialVisibility(show) {
                if (!diffSummary) {
                    isDifferentialVisible = false;
                    return;
                }
                isDifferentialVisible = !!show;
                diffSummary.style.display = show ? 'flex' : 'none';
                if (diffError) {
                    var hasErrorText = diffError.textContent.trim() !== '';
                    diffError.style.display = show && hasErrorText ? 'block' : 'none';
                }
                if (!show) {
                    if (diffTotalLabel) {
                        diffTotalLabel.textContent = '';
                    }
                    if (diffStatusLabel) {
                        diffStatusLabel.textContent = '';
                        diffStatusLabel.classList.remove('diff-status--missing', 'diff-status--excess', 'diff-status--ok');
                    }
                }
            }
            var parameterMap = @json($parameterMap);
            var lipidKeywords = ['lipid profile', 'lipid function', 'lipid panel', 'lft'];
            var currentPatientSex = '';
            var egfrInput = null;
            var egfrFlagInput = null;
            var updateEgfrField = function() {};
            var currentEgfrInputCandidates = ['estimated gfr', 'egfr', 'estimated glomerular filtration rate'];

            function isLipidTestName(value) {
                var normalized = (value || '').toLowerCase();
                return lipidKeywords.some(function (keyword) {
                    return normalized.includes(keyword);
                }) || normalized.includes('lipid');
            }

            function clearActive() {
                Array.from(specimenList.querySelectorAll('tr[data-id]')).forEach(function (row) {
                    row.classList.remove('active');
                });
            }

            function selectRow(row) {
                if (!row || !row.dataset.id) {
                    return;
                }
                clearActive();
                row.classList.add('active');
                selectedInput.value = row.dataset.id;
                patientName.value = row.dataset.patient || '-';
                if (patientAge) {
                    patientAge.value = row.dataset.ageDisplay || '';
                    patientAge.dataset.ageUnit = row.dataset.ageUnit || '';
                    patientAge.dataset.ageYears = row.dataset.ageYears || '';
                }
                testNameInput.value = row.dataset.test || '-';
                currentPatientSex = row.dataset.sex || '';
                updateEgfrField();
                if (isRepeated) {
                    isRepeated.value = row.dataset.repeated === '1' ? '1' : '0';
                }
                if (isConfirmed) {
                    isConfirmed.value = row.dataset.confirmed === '1' ? '1' : '0';
                }
                if (isRepeatedConfirmed) {
                    isRepeatedConfirmed.checked = row.dataset.repeated === '1' && row.dataset.confirmed === '1';
                }
                var params = parameterMap[row.dataset.id] || [];
                var showDifferential = params.length > 0 && shouldShowDifferentialSummary(row.dataset.test);
                if (params.length) {
                    singleResultBlock.style.display = 'none';
                    singleResultValue.required = false;
                    parameterBlock.style.display = 'block';
                    if (diffError) {
                        diffError.style.display = 'none';
                    }
                    function escapeHtml(value) {
                        return (value || '').toString()
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&#39;');
                    }

                    function renderResultField(param) {
                        var fieldName = 'parameter_results[' + param.id + '][result_value]';
                        var size = param.font_size || 14;
                        var style = 'style="font-size:' + size + 'px;"';
                        var value = param.result_value || '';
                        if (param.display_type === 'dropdown') {
                            var options = Array.isArray(param.dropdown_options) ? param.dropdown_options : [];
                            var select = '<select class="row-input" name="' + fieldName + '" ' + style + '>';
                            select += '<option value=""></option>';
                            options.forEach(function (option) {
                                var escaped = escapeHtml(option);
                                select += '<option value="' + escaped + '"' + (option === value ? ' selected' : '') + '>' + escaped + '</option>';
                            });
                            select += '</select>';
                            return select;
                        }
                        if (param.display_type === 'label') {
                            return '<div class="row-input" ' + style + '>' + escapeHtml(value || param.remarks || '') + '</div>';
                        }
                        var type = param.display_type === 'number' ? 'number' : 'text';
                        return '<input class="row-input" name="' + fieldName + '" type="' + type + '" value="' + escapeHtml(value) + '" ' + style + '>';
                    }

                    parameterRows.innerHTML = params.map(function (param) {
                        var label = param.symbol ? (param.name + ' (' + param.symbol + ')') : param.name;
                        var dataName = escapeHtml(param.name || '');
                        return '' +
                            '<tr data-param-name="' + dataName + '">' +
                            '<td>' + label + '</td>' +
                            '<td>' + renderResultField(param) + '</td>' +
                            '<td><input class="row-input" name="parameter_results[' + param.id + '][unit]" value="' + escapeHtml(param.unit || '') + '" readonly></td>' +
                            '<td><input class="row-input" name="parameter_results[' + param.id + '][reference_range]" value="' + escapeHtml(param.reference_range || '') + '" readonly></td>' +
                            '<td><input class="row-input" name="parameter_results[' + param.id + '][remarks]" value="' + escapeHtml(param.result_remarks || param.remarks || '') + '"></td>' +
                            '<td><input class="row-input flag-input" name="parameter_results[' + param.id + '][flag]" value="' + escapeHtml(param.flag || '') + '" readonly></td>' +
                            '</tr>';
                    }).join('');
                    bindFlagUpdates();
                    bindLipidProfileCalculations();
                    // --- LIPID PROFILE AUTO-CALCULATION ---
                    function bindLipidProfileCalculations() {
                        if (!testNameInput) {
                            testNameInput = document.getElementById('testName');
                        }
                        if (!testNameInput) {
                            return;
                        }
                        var testNameValue = (testNameInput.value || '').toLowerCase();

                        // Map parameter names to input elements
                        function normalize(name) {
                            return (name || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
                        }
                        var paramInputs = {};
                        var paramFlags = {};
                        Array.from(parameterRows.querySelectorAll('tr')).forEach(function (row) {
                            var label = row.querySelector('td');
                            if (!label) return;
                            var text = label.textContent || '';
                            var name = normalize(text);
                            var baseName = normalize(text.replace(/\([^)]+\)/g, ''));
                            var input = row.querySelector('input[name*="[result_value]"]');
                            var flagInput = row.querySelector('input[name*="[flag]"]');
                            if (input) {
                                paramInputs[name] = input;
                                if (baseName && baseName !== name) {
                                    paramInputs[baseName] = input;
                                }
                                if (flagInput) {
                                    paramFlags[name] = flagInput;
                                    if (baseName && baseName !== name) {
                                        paramFlags[baseName] = flagInput;
                                    }
                                }
                            }
                        });

                        function findParameterInput(keys) {
                            for (var i = 0; i < keys.length; i++) {
                                var candidate = paramInputs[normalize(keys[i])];
                                if (candidate) {
                                    return candidate;
                                }
                            }
                            return null;
                        }

                        egfrInput = findParameterInput(currentEgfrInputCandidates);
                        egfrFlagInput = findFlagInput(currentEgfrInputCandidates);
                        if (egfrInput) {
                            egfrInput.readOnly = true;
                            egfrInput.classList.add('calculated-field');
                            egfrInput.setAttribute('title', 'Auto-calculated (CKD-EPI)');
                        }

                        var creatinineInput = findParameterInput(['creatinine', 'creatinine serum', 'serum creatinine', 'scr']);
                        var creatinineTest = testNameValue.includes('creatinine') || testNameValue.includes('p000138');

                        function shouldComputeEgfr() {
                            return egfrInput && creatinineInput && creatinineTest;
                        }

                        function computeEgfrValue(creatinine, age) {
                            if (creatinine === null || age === null || age <= 0) {
                                return null;
                            }
                            var female = isFemaleSex();
                            var k = female ? 0.7 : 0.9;
                            var a = female ? -0.241 : -0.302;
                            var minValue = Math.min(creatinine / k, 1);
                            var maxValue = Math.max(creatinine / k, 1);
                            var sexFactor = female ? 1.012 : 1.0;
                            var egfr = 142 * Math.pow(minValue, a) * Math.pow(maxValue, -1.2) * Math.pow(0.9938, age) * sexFactor;
                            return Math.round(egfr * 100) / 100;
                        }

                        updateEgfrField = function() {
                            if (!egfrInput) {
                                return;
                            }
                            if (!shouldComputeEgfr()) {
                                egfrInput.value = '';
                                if (egfrFlagInput) {
                                    egfrFlagInput.value = '';
                                }
                                return;
                            }
                            var creatinineValue = parseNumericValue(['creatinine', 'creatinine serum', 'serum creatinine', 'scr']);
                            var ageValue = parsePatientAge();
                            var egfrValue = computeEgfrValue(creatinineValue, ageValue);
                            if (egfrValue === null) {
                                egfrInput.value = '';
                                if (egfrFlagInput) {
                                    egfrFlagInput.value = '';
                                }
                                return;
                            }
                            egfrInput.value = egfrValue;
                            if (egfrFlagInput) {
                                egfrFlagInput.value = 'CALCULATED (CKD-EPI) ' + egfrValue;
                            }
                        };

                        if (creatinineInput) {
                            creatinineInput.addEventListener('input', function () {
                                if (shouldComputeEgfr()) {
                                    updateEgfrField();
                                }
                            });
                        }
                        if (patientAge) {
                            patientAge.addEventListener('input', function () {
                                if (shouldComputeEgfr()) {
                                    updateEgfrField();
                                }
                            });
                        }

                        var lipidNameMap = {
                            'total cholesterol': ['total cholesterol', 'totalcholesterol', 'totalcholestrol', 'totalchol'],
                            'hdl cholesterol': ['hdl cholesterol', 'hdlcholesterol', 'hdlcholestrol', 'hdl'],
                            'triglycerides': ['triglycerides', 'triglyceride', 'tg'],
                            'ldl cholesterol': ['ldl cholesterol', 'ldlcholesterol', 'ldl', 'ldlcholestrol'],
                            'vldl cholesterol': ['vldl cholesterol', 'vldlcholesterol', 'vldl', 'vldlcholestrol'],
                            'non hdl cholesterol': ['non hdl cholesterol', 'nonhdlcholesterol', 'nonhdl'],
                            'total cholesterol/hdl ratio': ['total cholesterol/hdl ratio', 'totalcholesterol/hdl', 'totalcholesterolhdl', 'totalcholhdlratio'],
                            'triglycerides/hdl ratio': [
                                'triglycerides/hdl ratio',
                                'triglycerides / hdl ratio',
                                'tg/hdl ratio',
                                'tg / hdl ratio',
                                'triglycerideshdlratio',
                                'triglycerideshdhratio',
                                'tg/hdh ratio',
                                'tghdhratio',
                                'triglycerides/hdh ratio',
                                'triglycerides / hdh ratio',
                            ],
                        };

                        function findParamInput(keys) {
                            for (var i = 0; i < keys.length; i++) {
                                var input = paramInputs[normalize(keys[i])];
                                if (input) {
                                    return input;
                                }
                            }
                            return null;
                        }

                        function findFlagInput(keys) {
                            for (var i = 0; i < keys.length; i++) {
                                var flag = paramFlags[normalize(keys[i])];
                                if (flag) {
                                    return flag;
                                }
                            }
                            return null;
                        }

                        function hasLipidInputs() {
                            return Object.keys(lipidNameMap).some(function (name) {
                                return !!findParamInput(lipidNameMap[name] || [name]);
                            });
                        }

                        var isFullBloodTest = testNameValue.includes('full blood') || testNameValue.includes('fbc');

                        if (!isLipidTestName(testNameValue) && !hasLipidInputs() && !isFullBloodTest) {
                            return;
                        }

                        function parseVal(name) {
                            var input = findParamInput(lipidNameMap[name] || [name]);
                            if (!input) return null;
                            var v = parseFloat(input.value);
                            return isNaN(v) ? null : v;
                        }

                        function safeSet(name, val) {
                            if (val === null || isNaN(val)) return;
                            var input = findParamInput(lipidNameMap[name] || [name]);
                            if (!input) return;
                            input.value = Math.round(val * 100) / 100;
                        }

                            function parsePatientAge() {
                                if (!patientAge) {
                                    return null;
                                }
                                var unit = (patientAge.dataset.ageUnit || 'Y').toString().toUpperCase();
                                if (unit && unit !== 'Y') {
                                    return null;
                                }
                                var raw = patientAge.dataset.ageYears || patientAge.value;
                                var value = parseNumber(raw);
                                return value;
                            }

                            function isFemaleSex() {
                                if (!currentPatientSex) {
                                    return false;
                                }
                                var sex = (currentPatientSex || '').toLowerCase();
                                return sex.startsWith('f') || sex === 'female';
                            }

                            function parseNumericValue(keys) {
                                var input = findParameterInput(keys);
                                if (!input) {
                                    return null;
                                }
                                var value = parseFloat(input.value);
                                return Number.isNaN(value) ? null : value;
                            }

                            function bindFullBloodCalculations() {
                                if (!testNameValue.includes('full blood count')) {
                                    return;
                                }

                                function setCalculatedField(name, value) {
                                    var input = findParameterInput([name]);
                                    if (!input) {
                                        return;
                                    }
                                    input.readOnly = true;
                                    input.classList.add('calculated-field');
                                    if (value === null || isNaN(value)) {
                                        if (input.value !== '') {
                                            input.value = '';
                                            input.dispatchEvent(new Event('input', { bubbles: true }));
                                        }
                                        return;
                                    }
                                    var rounded = Math.round(value * 10) / 10;
                                    input.value = rounded;
                                    input.dispatchEvent(new Event('input', { bubbles: true }));
                                }

                                function computeFbc() {
                                    var hb = parseNumericValue(['haemoglobin']);
                                    var rbc = parseNumericValue(['rbc']);
                                    var hct = parseNumericValue(['hct']);

                                    if (hct !== null && rbc !== null && rbc !== 0) {
                                        setCalculatedField('mcv', (hct * 10) / rbc);
                                    } else {
                                        setCalculatedField('mcv', null);
                                    }

                                    if (hb !== null && rbc !== null && rbc !== 0) {
                                        setCalculatedField('mch', (hb * 10) / rbc);
                                    } else {
                                        setCalculatedField('mch', null);
                                    }

                                    if (hb !== null && hct !== null && hct !== 0) {
                                        setCalculatedField('mchc', (hb * 100) / hct);
                                    } else {
                                        setCalculatedField('mchc', null);
                                    }
                                }

                                ['haemoglobin', 'rbc', 'hct'].forEach(function (name) {
                                    var input = findParameterInput([name]);
                                    if (input) {
                                        input.addEventListener('input', computeFbc);
                                    }
                                });

                                computeFbc();
                            }

                            function computeEgfrValue(creatinine, age) {
                                if (creatinine === null || age === null || age <= 0) {
                                    return null;
                                }
                                var female = isFemaleSex();
                                var k = female ? 0.7 : 0.9;
                                var a = female ? -0.329 : -0.411;
                                var minValue = Math.min(creatinine / k, 1);
                                var maxValue = Math.max(creatinine / k, 1);
                                var sexFactor = female ? 1.018 : 1.0;
                                var egfr = 141 * Math.pow(minValue, a) * Math.pow(maxValue, -1.209) * Math.pow(0.993, age) * sexFactor;
                                return Math.round(egfr * 100) / 100;
                            }

                            updateEgfrField = function() {
                                if (!egfrInput) {
                                    return;
                                }
                                var creatinineValue = parseNumericValue(['creatinine', 'creatinine serum', 'serum creatinine', 'scr']);
                                var ageValue = parsePatientAge();
                                var egfrValue = computeEgfrValue(creatinineValue, ageValue);
                                if (egfrValue === null) {
                                    egfrInput.value = '';
                                    if (egfrFlagInput) {
                                        egfrFlagInput.value = '';
                                    }
                                    return;
                                }
                                egfrInput.value = egfrValue;
                                if (egfrFlagInput) {
                                    egfrFlagInput.value = 'CALCULATED (CKD-EPI) ' + egfrValue;
                                }
                            }

                            function setInterpretation(name, label) {
                                  var flagInput = paramFlags[normalize(name)];
                                  if (!flagInput) return;
                                  flagInput.value = label || '';
                            }

                            function interpretTotalCholesterol(value) {
                                  if (value === null) return '';
                                  if (value < 200) return 'DESIRABLE';
                                  if (value <= 239) return 'BORDERLINE HIGH';
                                  return 'HIGH';
                            }

                            function interpretHDL(value, sexValue) {
                                  if (value === null) return '';
                                  var sex = (sexValue || '').toLowerCase();
                                  if (value >= 60) return 'OPTIMAL';
                                  if (sex.includes('female') || sex === 'f') {
                                      if (value < 50) return 'UNDESIRABLE';
                                      if (value >= 50) return 'NEAR OPTIMAL';
                                  }
                                  if (value >= 40) return 'NEAR OPTIMAL';
                                  return 'UNDESIRABLE';
                            }

                              function interpretLDL(value) {
                                    if (value === null) return '';
                                    if (value < 100) return 'OPTIMAL';
                                    if (value <= 129) return 'NEAR OPTIMAL';
                                    if (value <= 159) return 'BORDERLINE HIGH';
                                    if (value <= 190) return 'HIGH';
                                    return 'VERY HIGH';
                              }

                              function interpretVLDL(value) {
                                    if (value === null) return '';
                                    if (value < 3.3) return 'OPTIMAL';
                                    if (value <= 4.5) return 'BORDERLINE HIGH';
                                    return 'HIGH';
                              }

                              function interpretTgHdlRatio(value) {
                                    if (value === null) return '';
                                    if (value < 3.3) return 'OPTIMAL';
                                    if (value <= 4.5) return 'BORDERLINE HIGH';
                                    return 'HIGH';
                              }

                            function interpretNonHDL(value) {
                                  if (value === null) return '';
                                  return value < 130 ? 'DESIRABLE' : 'HIGH';
                            }

                            function interpretTCHDL(value) {
                                  if (value === null) return '';
                                  if (value < 3.5) return 'DESIRABLE';
                                  if (value <= 4.5) return 'BORDERLINE';
                                  return 'HIGH';
                            }

                              function updateLipidInterpretations() {
                                    var totalChol = parseVal('total cholesterol');
                                    var hdl = parseVal('hdl cholesterol');
                                    var ldl = parseVal('ldl cholesterol');
                                    var vldl = parseVal('vldl cholesterol');
                                    var sexValue = currentPatientSex || '';

                                    setInterpretation('total cholesterol', interpretTotalCholesterol(totalChol));
                                    setInterpretation('hdl cholesterol', interpretHDL(hdl, sexValue));
                                    setInterpretation('ldl cholesterol', interpretLDL(ldl));
                                    setInterpretation('vldl cholesterol', interpretVLDL(vldl));
                              }

                            function recalcLipid() {
                                  // Use robust matching for your field names
                                  var totalChol = parseVal('total cholesterol');
                                  var hdl = parseVal('hdl cholesterol');
                                  var tg = parseVal('triglycerides');

                                  // VLDL = TG / 5
                                  if (tg !== null) safeSet('vldl cholesterol', tg / 5);

                                  // LDL = Total Chol - HDL - VLDL
                                  var vldl = parseVal('vldl cholesterol');
                                  if (totalChol !== null && hdl !== null && vldl !== null) safeSet('ldl cholesterol', totalChol - hdl - vldl);

                                  // Non-HDL = Total Chol - HDL
                                  if (totalChol !== null && hdl !== null) safeSet('non hdl cholesterol', totalChol - hdl);

                                  // TC/HDL
                                  if (totalChol !== null && hdl && hdl !== 0) safeSet('total cholesterol/hdl ratio', totalChol / hdl);

                                  // TG/HDL
                                  if (tg !== null && hdl && hdl !== 0) safeSet('triglycerides/hdl ratio', tg / hdl);

                                  updateLipidInterpretations();
                            }

                            Object.values(lipidNameMap).forEach(function (synonyms) {
                                if (!Array.isArray(synonyms)) {
                                    synonyms = [synonyms];
                                }
                                var input = findParamInput(synonyms);
                                if (input) {
                                    input.addEventListener('input', recalcLipid);
                                }
                            });
                            var creatinineInput = findParameterInput(['creatinine', 'creatinine serum', 'serum creatinine']);
                            if (creatinineInput) {
                                creatinineInput.addEventListener('input', function () {
                                    recalcLipid();
                                    updateEgfrField();
                                });
                            }
                            if (patientAge) {
                                patientAge.addEventListener('input', function () {
                                    updateEgfrField();
                                });
                            }

                            bindFullBloodCalculations();
                            updateLipidInterpretations();
                            updateEgfrField();
                        }
                        bindLipidProfileCalculations();
                            // --- LIVER PROFILE AUTO-CALCULATION ---
                            function bindLiverProfileCalculations() {
                                if (!testNameInput) {
                                    testNameInput = document.getElementById('testName');
                                }
                                if (!testNameInput) {
                                    return;
                                }
                                function normalize(name) {
                                    return (name || '').toLowerCase().replace(/[^a-z0-9]+/g, '');
                                }
                                var paramInputs = [];
                                Array.from(parameterRows.querySelectorAll('tr')).forEach(function (row) {
                                    var label = row.querySelector('td');
                                    var source = row.dataset.paramName || (label ? label.textContent : '');
                                    if (!source) return;
                                    var name = normalize(source);
                                    var resultField = row.querySelector('td:nth-child(2) .row-input');
                                    if (!resultField) {
                                        resultField = row.querySelector('[name*="[result_value]"]');
                                    }
                                    if (resultField) paramInputs.push({ name: name, input: resultField });
                                });

                                function findInput(tokens) {
                                    var list = Array.isArray(tokens) ? tokens : [tokens];
                                    var needles = list.map(function (token) {
                                        return normalize(token);
                                    }).filter(Boolean);
                                    if (needles.length === 0) {
                                        return null;
                                    }
                                    var match = paramInputs.find(function (item) {
                                        return needles.every(function (needle) {
                                            return item.name.includes(needle);
                                        });
                                    });
                                    return match ? match.input : null;
                                }

                                var testNameValue = (testNameInput.value || '').toLowerCase();
                                // Also check for code if available in the DOM (future-proof)
                                var liverKeywords = ['liver profile', 'liver function', 'lft'];
                                var isLiverProfile = liverKeywords.some(function (keyword) {
                                    return testNameValue.includes(keyword);
                                });
                                if (!isLiverProfile && testNameValue.includes('liver')) {
                                    isLiverProfile = true;
                                }
                                if (!isLiverProfile) {
                                    var hasLiverMarkers = paramInputs.some(function (item) {
                                        return item.name.includes('bilirubin') ||
                                            item.name.includes('albumin') ||
                                            item.name.includes('globulin') ||
                                            item.name.includes('protein');
                                    });
                                    if (!hasLiverMarkers) {
                                        return;
                                    }
                                }
                                function readFieldValue(field) {
                                    if (!field) return '';
                                    if (field.tagName === 'DIV' || field.tagName === 'SPAN') {
                                        return field.textContent || '';
                                    }
                                    return field.value || '';
                                }
                                function writeFieldValue(field, val) {
                                    if (!field) return;
                                    if (field.tagName === 'DIV' || field.tagName === 'SPAN') {
                                        field.textContent = val;
                                        return;
                                    }
                                    field.value = val;
                                }
                                function findRowByLabelTokens(tokens) {
                                    var list = (Array.isArray(tokens) ? tokens : [tokens])
                                        .map(function (token) { return (token || '').toString().toUpperCase(); })
                                        .filter(Boolean);
                                    if (!list.length) return null;
                                    var rows = Array.from(parameterRows.querySelectorAll('tr'));
                                    for (var i = 0; i < rows.length; i++) {
                                        var row = rows[i];
                                        var labelCell = row.querySelector('td');
                                        var labelText = (row.dataset.paramName || (labelCell ? labelCell.textContent : '') || '').toString().toUpperCase();
                                        if (!labelText) continue;
                                        var ok = list.every(function (token) { return labelText.includes(token); });
                                        if (ok) return row;
                                    }
                                    return null;
                                }
                                function getResultFieldFromRow(row) {
                                    if (!row) return null;
                                    return row.querySelector('td:nth-child(2) .row-input') ||
                                        row.querySelector('td:nth-child(2) input, td:nth-child(2) select, td:nth-child(2) textarea');
                                }
                                function findInputAny(tokenGroups) {
                                    for (var i = 0; i < tokenGroups.length; i++) {
                                        var input = findInput(tokenGroups[i]);
                                        if (input) return input;
                                    }
                                    return null;
                                }
                                function findFieldByLabelKeywords(keywords) {
                                    var tokens = (Array.isArray(keywords) ? keywords : [keywords])
                                        .map(function (token) { return normalize(token); })
                                        .filter(Boolean);
                                    if (!tokens.length) return null;
                                    var rows = Array.from(parameterRows.querySelectorAll('tr'));
                                    for (var i = 0; i < rows.length; i++) {
                                        var row = rows[i];
                                        var label = row.querySelector('td');
                                        var source = row.dataset.paramName || (label ? label.textContent : '');
                                        if (!source) continue;
                                        var name = normalize(source);
                                        var ok = tokens.every(function (token) { return name.includes(token); });
                                        if (!ok) continue;
                                        var field = row.querySelector('td:nth-child(2) .row-input');
                                        if (!field) {
                                            field = row.querySelector('td:nth-child(2) input, td:nth-child(2) select, td:nth-child(2) textarea');
                                        }
                                        if (field) return field;
                                    }
                                    return null;
                                }
                                function parseValFromInput(input) {
                                    if (!input) return null;
                                    var v = parseFloat(readFieldValue(input));
                                    return isNaN(v) ? null : v;
                                }
                                function safeSetInput(input, val) {
                                    if (!input) return;
                                    if (val === null || isNaN(val)) return;
                                    writeFieldValue(input, Math.round(val * 100) / 100);
                                }

                                function recalcLiver() {
                                        // Indirect Bilirubin = Total Bilirubin – Direct Bilirubin
                                        function isBilirubinLabel(text) {
                                            return text.includes('BILIRUBIN') ||
                                                text.includes('BILURUBIN') ||
                                                text.includes('BILRUBIN');
                                        }
                                        function findBilirubinRow(kind) {
                                            var rows = Array.from(parameterRows.querySelectorAll('tr'));
                                            for (var i = 0; i < rows.length; i++) {
                                                var row = rows[i];
                                                var labelCell = row.querySelector('td');
                                                var labelText = (row.dataset.paramName || (labelCell ? labelCell.textContent : '') || '').toString().toUpperCase();
                                                if (!labelText) continue;
                                                if (!isBilirubinLabel(labelText)) continue;
                                                if (labelText.includes(kind)) return row;
                                            }
                                            return null;
                                        }

                                        var totalRow = findBilirubinRow('TOTAL');
                                        var directRow = findBilirubinRow('DIRECT');
                                        var indirectRow = findBilirubinRow('INDIRECT');
                                        var totalBili = parseValFromInput(getResultFieldFromRow(totalRow));
                                        var directBili = parseValFromInput(getResultFieldFromRow(directRow));
                                        var indirectField = getResultFieldFromRow(indirectRow);
                                        if (totalBili !== null && directBili !== null && indirectField) {
                                            safeSetInput(indirectField, totalBili - directBili);
                                        }

                                        // A/G Ratio = Albumin ÷ Globulin
                                        var albumin = parseValFromInput(findInputAny([['albumin']]));
                                        var globulin = parseValFromInput(findInputAny([['globulin']]));
                                        var totalProtein = parseValFromInput(findInputAny([['protein', 'total'], ['total', 'protein']]));
                                        if (totalProtein !== null && albumin !== null) {
                                            var computedGlobulin = totalProtein - albumin;
                                            if (!Number.isNaN(computedGlobulin)) {
                                                globulin = computedGlobulin;
                                                safeSetInput(findInputAny([['globulin']]), computedGlobulin);
                                            }
                                        }
                                        if (albumin !== null && globulin && globulin !== 0) {
                                            safeSetInput(findInputAny([['albumin', 'globulin', 'ratio'], ['agratio']]), albumin / globulin);
                                            safeSetInput(findInputAny([['agratio']]), albumin / globulin);
                                        }
                                }

                                function bindRecalc(input) {
                                    if (!input) return;
                                    input.addEventListener('input', recalcLiver);
                                    input.addEventListener('change', recalcLiver);
                                }

                                // Bind input events for relevant fields (including misspellings)
                                bindRecalc(findInputAny([
                                    ['bilirubin', 'total'],
                                    ['bilurubin', 'total'],
                                    ['bilrubin', 'total'],
                                    ['bili', 'total'],
                                    ['bil', 'total']
                                ]));
                                bindRecalc(findInputAny([
                                    ['bilirubin', 'direct'],
                                    ['bilurubin', 'direct'],
                                    ['bilrubin', 'direct'],
                                    ['bil', 'direct']
                                ]));
                                bindRecalc(findInputAny([['albumin']]));
                                bindRecalc(findInputAny([['globulin']]));
                                bindRecalc(findInputAny([['protein', 'total'], ['total', 'protein']]));

                                if (parameterRows) {
                                    parameterRows.addEventListener('input', recalcLiver);
                                    parameterRows.addEventListener('change', recalcLiver);
                                }
                                recalcLiver();
                            }
                            bindLiverProfileCalculations();
                        } else {
                    parameterBlock.style.display = 'none';
                    parameterRows.innerHTML = '';
                    singleResultBlock.style.display = 'block';
                    singleResultValue.required = true;
                }
                setDifferentialVisibility(showDifferential);
                if (showDifferential) {
                    updateDiffSummary(collectDifferentialStats());
                }
            }

            specimenList.addEventListener('click', function (event) {
                var row = event.target.closest('tr[data-id]');
                if (row) {
                    selectRow(row);
                }
            });

            function applyFilters() {
                var spec = (filterSpecimen.value || '').toLowerCase();
                var dept = (filterDepartment.value || '').toLowerCase();
                var groups = Array.from(specimenList.querySelectorAll('tr[data-group="patient"]'));
                groups.forEach(function (groupRow) {
                    var patientName = (groupRow.dataset.patient || '').toLowerCase();
                    var rows = [];
                    var next = groupRow.nextElementSibling;
                    while (next && next.dataset.group === 'item') {
                        rows.push(next);
                        next = next.nextElementSibling;
                    }
                    var anyVisible = false;
                    rows.forEach(function (row) {
                        var matchSpec = !spec || (row.dataset.specimen || '').toLowerCase().includes(spec);
                        var matchDept = !dept || (row.dataset.test || '').toLowerCase().includes(dept);
                        var matchPatient = !spec || patientName.includes(spec);
                        var visible = (matchSpec || matchPatient) && matchDept;
                        row.style.display = visible ? '' : 'none';
                        if (visible) {
                            anyVisible = true;
                        }
                    });
                    groupRow.style.display = anyVisible ? '' : 'none';
                });
            }

            filterSpecimen.addEventListener('input', applyFilters);
            filterDepartment.addEventListener('input', applyFilters);
            if (sortSelect) {
                sortSelect.addEventListener('change', function () {
                    var url = new URL(window.location.href);
                    url.searchParams.set('sort', sortSelect.value);
                    window.location.href = url.toString();
                });
            }

            if (isRepeatedConfirmed && isRepeated && isConfirmed) {
                var syncRepeatedConfirmed = function () {
                    var checked = isRepeatedConfirmed.checked;
                    isRepeated.value = checked ? '1' : '0';
                    isConfirmed.value = checked ? '1' : '0';
                };
                isRepeatedConfirmed.addEventListener('change', syncRepeatedConfirmed);
                syncRepeatedConfirmed();
            }

            function parseNumber(value) {
                if (value === null || value === undefined) {
                    return null;
                }
                var str = String(value).trim();
                if (!str) {
                    return null;
                }
                var num = parseFloat(str);
                return Number.isNaN(num) ? null : num;
            }

            function parseRange(range) {
                var matches = String(range || '').match(/-?\d+(?:\.\d+)?/g) || [];
                if (matches.length < 2) {
                    return null;
                }
                var min = parseFloat(matches[0]);
                var max = parseFloat(matches[1]);
                if (Number.isNaN(min) || Number.isNaN(max)) {
                    return null;
                }
                if (min > max) {
                    var temp = min;
                    min = max;
                    max = temp;
                }
                return { min: min, max: max };
            }

            // patientAge changes no longer block form submission

            var flagParameters = [
                'TOTAL CHOLESTEROL',
                'HDL CHOLESTEROL',
                'LDL CHOLESTEROL',
                'VLDL CHOLESTEROL',
                'TRIGLYCERIDES',
                'TOTAL CHOLESTEROL / HDL RATIO',
                'TRIGLYCERIDES / HDL RATIO',
                'TG/HDL RATIO',
            ];

            function computeFlag(value, range) {
                var num = parseNumber(value);
                if (num === null) {
                    return '';
                }
                var parsed = parseRange(range);
                if (!parsed) {
                    return '';
                }
                if (num < parsed.min) {
                    return 'LOW';
                }
                if (num > parsed.max) {
                    return 'HIGH';
                }
                return 'NORMAL';
            }

            function normalizeName(name) {
                return (name || '').trim().replace(/\s+/g, ' ').toUpperCase();
            }

            function interpretLipidFlag(name, value, sex) {
                var num = parseNumber(value);
                if (num === null) {
                    return '';
                }
                var normalized = normalizeName(name || '');
                var normalizedSex = (sex || '').toLowerCase();
                if (normalized.includes('TOTAL CHOLESTEROL') && !normalized.includes('RATIO')) {
                    if (num < 200) {
                        return 'DESIRABLE';
                    }
                    if (num < 240) {
                        return 'BORDERLINE HIGH';
                    }
                    return 'HIGH';
                }
                if (normalized.includes('TRIGLYCERIDES') && !normalized.includes('RATIO')) {
                    if (num < 150) {
                        return 'DESIRABLE';
                    }
                    if (num < 200) {
                        return 'BORDERLINE HIGH';
                    }
                    if (num < 500) {
                        return 'HIGH';
                    }
                    return 'VERY HIGH';
                }
                if (normalized.includes('LDL CHOLESTEROL')) {
                    if (num < 100) {
                        return 'OPTIMAL';
                    }
                    if (num < 130) {
                        return 'NEAR OPTIMAL';
                    }
                    if (num < 160) {
                        return 'BORDERLINE HIGH';
                    }
                    if (num < 190) {
                        return 'HIGH';
                    }
                    return 'VERY HIGH';
                }
                if (normalized.includes('HDL CHOLESTEROL') && !normalized.includes('RATIO')) {
                    var lower = normalizedSex.startsWith('male') ? 40 : 50;
                    if (num >= 60) {
                        return 'OPTIMAL';
                    }
                    if (num >= lower) {
                        return 'NEAR OPTIMAL';
                    }
                    return 'UNDESIRABLE';
                }
                if (normalized.includes('VLDL CHOLESTEROL')) {
                    if (num < 3.3) {
                        return 'OPTIMAL';
                    }
                    if (num <= 4.5) {
                        return 'BORDERLINE HIGH';
                    }
                    return 'HIGH';
                }
                if (normalized.includes('TG/HDL') || normalized.includes('TRIGLYCERIDES / HDL') || normalized.includes('TOTAL CHOLESTEROL / HDL')) {
                    if (num < 3.3) {
                        return 'OPTIMAL';
                    }
                    if (num <= 4.5) {
                        return 'BORDERLINE HIGH';
                    }
                    return 'HIGH';
                }
                return '';
            }

            function shouldShowFlag(name) {
                if (!name) {
                    return false;
                }
                var normalized = (name || '').trim().toUpperCase();
                return flagParameters.some(function (flagName) {
                    return normalized === flagName;
                });
            }

            function bindFlagUpdates() {
                Array.from(parameterRows.querySelectorAll('tr')).forEach(function (row) {
                    var valueInput = row.querySelector('input[name*="[result_value]"]');
                    var refInput = row.querySelector('input[name*="[reference_range]"]');
                    var flagInput = row.querySelector('.flag-input');
                    var label = row.querySelector('td');
                    var paramName = label ? label.textContent : '';
                    if (!valueInput || !refInput || !flagInput || !shouldShowFlag(paramName)) {
                        return;
                    }
                    var updateFlag = function () {
                        var interpreted = interpretLipidFlag(paramName, valueInput.value, currentPatientSex);
                        flagInput.value = interpreted || computeFlag(valueInput.value, refInput.value);
                    };
                    valueInput.addEventListener('input', updateFlag);
                    updateFlag();
                });
            }

            function buildDifferentialParamMap() {
                var currentId = selectedInput ? selectedInput.value : '';
                var params = parameterMap[currentId] || [];
                var map = {};
                params.forEach(function (param) {
                    var key = String(param.name || '').trim().toUpperCase();
                    if (key) {
                        map[key] = param;
                    }
                });
                return map;
            }

            function collectDifferentialStats() {
                var map = buildDifferentialParamMap();
                var sum = 0;
                var allFilled = true;
                var filledCount = 0;
                differentialParameterNames.forEach(function (name) {
                    var param = map[name];
                    if (!param || !entryForm) {
                        allFilled = false;
                        return;
                    }
                    var input = entryForm.querySelector('input[name="parameter_results[' + param.id + '][result_value]"]');
                    var value = parseNumber(input ? input.value : '');
                    if (value === null) {
                        allFilled = false;
                        return;
                    }
                    filledCount++;
                    sum += value;
                });
                return {
                    sum: sum,
                    allFilled: allFilled,
                    filledCount: filledCount,
                };
            }

            function roundOne(value) {
                var num = typeof value === 'number' ? value : 0;
                return Math.round(num * 10) / 10;
            }

            function formatOneDecimal(value) {
                var rounded = roundOne(value);
                if (Object.is(rounded, -0)) {
                    rounded = 0;
                }
                return rounded.toFixed(1);
            }

            function updateDiffSummary(stats) {
                if (!diffTotalLabel || !isDifferentialVisible) {
                    return;
                }
                var totalRounded = roundOne(stats.sum);
                diffTotalLabel.textContent = 'Total: ' + formatOneDecimal(totalRounded) + '% (should be 100%)';
                if (diffStatusLabel) {
                    diffStatusLabel.classList.remove('diff-status--missing', 'diff-status--excess', 'diff-status--ok');
                    if (stats.filledCount === 0) {
                        diffStatusLabel.textContent = '';
                    } else {
                        var remaining = roundOne(100.0 - totalRounded);
                        if (stats.allFilled && stats.filledCount === differentialParameterNames.length && Math.abs(remaining) < 0.05) {
                            diffStatusLabel.textContent = 'OK';
                            diffStatusLabel.classList.add('diff-status--ok');
                        } else if (remaining > 0) {
                            diffStatusLabel.textContent = 'Remaining: ' + formatOneDecimal(remaining) + '%';
                            diffStatusLabel.classList.add('diff-status--missing');
                        } else {
                            diffStatusLabel.textContent = 'Excess: ' + formatOneDecimal(Math.abs(remaining)) + '%';
                            diffStatusLabel.classList.add('diff-status--excess');
                        }
                    }
                }
                if (stats.allFilled && stats.filledCount === differentialParameterNames.length) {
                    var diffRounded = roundOne(100.0 - totalRounded);
                    if (Math.abs(diffRounded) < 0.05) {
                        clearDiffError();
                    }
                }
            }

            function showDiffError(message) {
                if (!diffError) {
                    return;
                }
                diffError.textContent = message;
                diffError.style.display = 'block';
            }

            function clearDiffError() {
                if (!diffError) {
                    return;
                }
                diffError.textContent = '';
                diffError.style.display = 'none';
            }

            function validateDiffTotal() {
                if (!entryForm || !diffError) {
                    return true;
                }
                if (!isDifferentialVisible) {
                    return true;
                }
                var stats = collectDifferentialStats();
                updateDiffSummary(stats);
                if (!(stats.allFilled && stats.filledCount === differentialParameterNames.length)) {
                    return true;
                }
                var totalRounded = roundOne(stats.sum);
                if (Math.abs(totalRounded - 100.0) > 0.000001) {
                    var diffRounded = roundOne(100.0 - totalRounded);
                    var formattedTotal = formatOneDecimal(totalRounded);
                    var formattedDiff = formatOneDecimal(Math.abs(diffRounded));
                    var message = 'Differential counts must total 100%. Current total: ' + formattedTotal + '%. ';
                    message += diffRounded > 0
                        ? 'Short by ' + formattedDiff + '%.'
                        : 'Exceeds by ' + formattedDiff + '%.';
                    showDiffError(message);
                    return false;
                }
                clearDiffError();
                return true;
            }

            if (parameterRows) {
                parameterRows.addEventListener('input', function () {
                    if (isDifferentialVisible) {
                        updateDiffSummary(collectDifferentialStats());
                    }
                });
            }

            if (entryForm) {
                entryForm.addEventListener('submit', function (event) {
                    if (!validateDiffTotal()) {
                        event.preventDefault();
                    }
                });
            }

            var firstRow = specimenList.querySelector('tr[data-id]');
            if (firstRow) {
                selectRow(firstRow);
            }
        })();
    </script>
@endsection

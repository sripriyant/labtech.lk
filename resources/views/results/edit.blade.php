@extends('layouts.admin')

@php
    $pageTitle = 'Edit Result';
@endphp

@section('content')
    <style>
        .notice {
            background: #fff7e6;
            border: 1px solid #f2d19b;
            color: #7a4b00;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 12px;
            font-size: 13px;
        }

        .error-note {
            display: none;
            margin-bottom: 10px;
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

        .status-badge.high,
        .status-badge.critical {
            background: #ffe4e6;
            color: #be123c;
            border-color: #fecdd3;
        }

        .status-badge.low {
            background: #fff3e6;
            color: #b45309;
            border-color: #f5d0a6;
        }

        .status-badge.normal {
            background: #e7f6ee;
            color: #0f7a47;
            border-color: #b7e3cc;
        }

        .status-badge.abnormal {
            background: #e0f2fe;
            color: #0369a1;
            border-color: #bae6fd;
        }

        .row-input {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .row-input.readonly {
            background: #f1f3f6;
            color: #5b6b74;
            pointer-events: none;
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

        @media (max-width: 1100px) {
            .filters {
                grid-template-columns: repeat(2, 1fr);
            }

            .split {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @if ($rows->isEmpty())
        <div class="notice">
            Edit pending: no results are available yet. Enter results before editing.
        </div>
    @endif

    <form method="get" action="{{ url('/results/edit') }}">
        <div class="filters">
            <div class="field">
                <label>Specimen No</label>
                <input id="filterSpecimen" type="text" name="specimen_no" value="{{ $filters['specimen_no'] ?? '' }}">
            </div>
            <div class="field">
                <label>Patient Name</label>
                <input id="filterPatient" type="text" name="patient" value="{{ $filters['patient'] ?? '' }}">
            </div>
            <div class="field">
                <label>Test</label>
                <input id="filterTest" type="text" name="test" value="{{ $filters['test'] ?? '' }}">
            </div>
            <div class="field">
                <label>Status</label>
                <select id="filterStatus" name="status">
                    <option value="">All</option>
                    @foreach (['ORDERED', 'RESULT_ENTERED', 'VALIDATED', 'APPROVED', 'REJECTED'] as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Sort</label>
                <select name="sort">
                    <option value="patient_asc" @selected(($filters['sort'] ?? '') === 'patient_asc')>Patient: A-Z</option>
                    <option value="patient_desc" @selected(($filters['sort'] ?? '') === 'patient_desc')>Patient: Z-A</option>
                    <option value="specimen_asc" @selected(($filters['sort'] ?? '') === 'specimen_asc')>Specimen No: A-Z</option>
                    <option value="specimen_desc" @selected(($filters['sort'] ?? '') === 'specimen_desc')>Specimen No: Z-A</option>
                    <option value="test_asc" @selected(($filters['sort'] ?? '') === 'test_asc')>Test: A-Z</option>
                    <option value="test_desc" @selected(($filters['sort'] ?? '') === 'test_desc')>Test: Z-A</option>
                    <option value="status_asc" @selected(($filters['sort'] ?? '') === 'status_asc')>Status: A-Z</option>
                    <option value="status_desc" @selected(($filters['sort'] ?? '') === 'status_desc')>Status: Z-A</option>
                    <option value="flag_desc" @selected(($filters['sort'] ?? '') === 'flag_desc')>Flag: High to Normal</option>
                    <option value="flag_asc" @selected(($filters['sort'] ?? '') === 'flag_asc')>Flag: Normal to High</option>
                </select>
            </div>
            <div class="field" style="align-self:end;">
                <button class="btn" type="submit">Search</button>
            </div>
        </div>
    </form>

    <div class="split">
        <div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Specimen No</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Test</th>
                            <th>Status</th>
                            <th>Flag</th>
                        </tr>
                    </thead>
                        <tbody id="specimenList">
                            @php
                                $patientPalette = ['#0ea5e9', '#8b5cf6', '#10b981', '#f97316', '#ef4444', '#14b8a6', '#a855f7', '#f59e0b'];
                                $groupedRows = $rows->groupBy(fn ($row) => $row->specimen?->patient_id ?? 0);
                            @endphp
                            @forelse ($groupedRows as $patientId => $groupRows)
                                @php
                                    $first = $groupRows->first();
                                    $patientName = $first->specimen->patient->name ?? '-';
                                    $patientAge = $first->specimen?->age_display ?? '-';
                                    $patientSex = $first->specimen->patient->sex ?? '-';
                                    $color = $patientPalette[abs((int) $patientId) % count($patientPalette)];
                                @endphp
                                <tr class="patient-group" style="--patient-color: {{ $color }};">
                                    <td colspan="6">{{ $patientName }} ({{ $patientAge }} / {{ $patientSex }})</td>
                                </tr>
                                @foreach ($groupRows as $row)
                                    @php
                                        $flag = strtoupper($row->edit_flag ?? '');
                                        $flagClass = $flag ? strtolower($flag) : 'normal';
                                    @endphp
                                    <tr data-id="{{ $row->id }}"
                                        data-specimen="{{ $row->specimen->specimen_no ?? '-' }}"
                                        data-patient="{{ $patientName }}"
                                        data-nic="{{ $row->specimen->patient->nic ?? '' }}"
                                        data-sex="{{ $row->specimen->patient->sex ?? '' }}"
                                        data-phone="{{ $row->specimen->patient->phone ?? '' }}"
                                        data-age-display="{{ $row->specimen?->age_display ?? '' }}"
                                        data-age-unit="{{ $row->specimen?->age_unit ?? '' }}"
                                        data-age-years="{{ $row->specimen?->age_years ?? '' }}"
                                        data-test="{{ $row->testMaster->name ?? '-' }}"
                                        data-result="{{ $row->result->result_value ?? '' }}"
                                        data-unit="{{ $row->result->unit ?? '' }}"
                                        data-ref="{{ $row->result->reference_range ?? '' }}"
                                        data-status="{{ $row->status }}"
                                        data-repeated="{{ $row->is_repeated ? '1' : '0' }}"
                                        data-confirmed="{{ $row->is_confirmed ? '1' : '0' }}">
                                        <td>{{ $row->specimen->specimen_no ?? '-' }}</td>
                                        <td>{{ $row->specimen?->age_display ?? '-' }}</td>
                                        <td>{{ $row->specimen->patient->sex ?? '-' }}</td>
                                        <td>{{ $row->testMaster->name ?? '-' }}</td>
                                        <td>{{ $row->status }}</td>
                                        <td><span class="status-badge {{ $flagClass }}">{{ $flag ?: 'NORMAL' }}</span></td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6">No results available.</td>
                                </tr>
                            @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div>
            <form id="editForm" method="post">
                @csrf
                <input id="patientName" name="patient_name" type="hidden">
                <input id="patientSex" name="patient_sex" type="hidden">
                <input id="patientPhone" name="patient_phone" type="hidden">
                <input id="patientNic" name="patient_nic" type="hidden">
                <div class="field" style="grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;">
                    <label style="display:flex;gap:6px;align-items:center;">
                        <input type="checkbox" id="isRepeatedConfirmed">
                        Repeated &amp; Confirmed
                    </label>
                    <input type="hidden" id="isRepeated" name="is_repeated" value="0">
                    <input type="hidden" id="isConfirmed" name="is_confirmed" value="0">
                </div>
                <div id="singleResultBlock" class="field">
                    <label>Result Value</label>
                    <textarea id="resultValue" name="result_value" rows="6" required></textarea>
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
                <div id="singleUnitBlock" class="field">
                    <label>Unit</label>
                    <input id="resultUnit" name="unit" type="text">
                </div>
                <div id="singleRefBlock" class="field">
                    <label>Reference Range</label>
                    <input id="resultRef" name="reference_range" type="text">
                </div>
                <div style="margin-top:10px;">
                    <button class="btn" type="submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    @php
        $parameterMap = $rows->mapWithKeys(function ($row) {
            $parameters = $row->testMaster?->parameters ?? collect();
            $parameters = $parameters
                ->sortBy(fn ($parameter) => sprintf('%05d-%010d', (int) ($parameter->sort_order ?? 0), (int) ($parameter->id ?? 0)))
                ->values();
            $results = $row->parameterResults?->keyBy('test_parameter_id') ?? collect();
            return [
                $row->id => $parameters->map(function ($parameter) use ($results) {
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
                    ];
                })->values(),
            ];
        })->all();
    @endphp

    <script>
        (function () {
            var specimenList = document.getElementById('specimenList');
            var editForm = document.getElementById('editForm');
            var patientName = document.getElementById('patientName');
            var patientSex = document.getElementById('patientSex');
            var patientPhone = document.getElementById('patientPhone');
            var patientNic = document.getElementById('patientNic');
            var resultValue = document.getElementById('resultValue');
            var resultUnit = document.getElementById('resultUnit');
            var resultRef = document.getElementById('resultRef');
            var singleResultBlock = document.getElementById('singleResultBlock');
            var singleUnitBlock = document.getElementById('singleUnitBlock');
            var singleRefBlock = document.getElementById('singleRefBlock');
            var parameterBlock = document.getElementById('parameterBlock');
            var parameterRows = document.getElementById('parameterRows');
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
            var isRepeated = document.getElementById('isRepeated');
            var isConfirmed = document.getElementById('isConfirmed');
            var isRepeatedConfirmed = document.getElementById('isRepeatedConfirmed');
            var parameterMap = @json($parameterMap);

            function clearActive() {
                Array.from(specimenList.querySelectorAll('tr')).forEach(function (row) {
                    row.classList.remove('active');
                });
            }

            function selectRow(row) {
                if (!row || !row.dataset.id) {
                    return;
                }
                clearActive();
                row.classList.add('active');
                editForm.action = '/results/edit/' + row.dataset.id;
                if (patientName) {
                    patientName.value = row.dataset.patient || '';
                }
                if (patientNic) {
                    patientNic.value = row.dataset.nic || '';
                }
                if (patientSex) {
                    patientSex.value = row.dataset.sex || '';
                }
                if (patientPhone) {
                    patientPhone.value = row.dataset.phone || '';
                }
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
                    singleUnitBlock.style.display = 'none';
                    singleRefBlock.style.display = 'none';
                    resultValue.required = false;
                    parameterBlock.style.display = 'block';
                    if (diffError) {
                        diffError.style.display = 'none';
                    }
                    parameterRows.innerHTML = params.map(function (param) {
                        var label = param.symbol ? (param.name + ' (' + param.symbol + ')') : param.name;
                        return '' +
                            '<tr>' +
                            '<td>' + label + '</td>' +
                            '<td><input class="row-input" name="parameter_results[' + param.id + '][result_value]" value="' + (param.result_value || '') + '"></td>' +
                            '<td><input class="row-input readonly" name="parameter_results[' + param.id + '][unit]" value="' + (param.unit || '') + '" readonly></td>' +
                            '<td><input class="row-input readonly" name="parameter_results[' + param.id + '][reference_range]" value="' + (param.reference_range || '') + '" readonly></td>' +
                            '<td><input class="row-input" name="parameter_results[' + param.id + '][remarks]" value="' + (param.result_remarks || param.remarks || '') + '"></td>' +
                            '<td><input class="row-input flag-input" name="parameter_results[' + param.id + '][flag]" value="' + (param.flag || '') + '" readonly></td>' +
                            '</tr>';
                    }).join('');
                    bindFlagUpdates();
                } else {
                    parameterBlock.style.display = 'none';
                    parameterRows.innerHTML = '';
                    singleResultBlock.style.display = 'block';
                    singleUnitBlock.style.display = 'block';
                    singleRefBlock.style.display = 'block';
                    resultValue.required = true;
                    resultValue.value = row.dataset.result || '';
                    resultUnit.value = row.dataset.unit || '';
                    resultRef.value = row.dataset.ref || '';
                }

                if (diffError) {
                    diffError.style.display = 'none';
                }

                setDifferentialVisibility(showDifferential);
                if (showDifferential) {
                    updateDiffSummary(collectDifferentialStats());
                }
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

            specimenList.addEventListener('click', function (event) {
                var row = event.target.closest('tr');
                if (row) {
                    selectRow(row);
                }
            });

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
                        var interpreted = interpretLipidFlag(paramName, valueInput.value, patientSex?.value || '');
                        flagInput.value = interpreted || computeFlag(valueInput.value, refInput.value);
                    };
                    valueInput.addEventListener('input', updateFlag);
                    updateFlag();
                });
            }

            function buildDifferentialParamMap() {
                var actionParts = editForm && editForm.action ? editForm.action.split('/') : [];
                var currentId = actionParts.length ? actionParts[actionParts.length - 1] : '';
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
                    if (!param || !editForm) {
                        allFilled = false;
                        return;
                    }
                    var input = editForm.querySelector('input[name="parameter_results[' + param.id + '][result_value]"]');
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
                if (!editForm || !diffError) {
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

            if (editForm) {
                editForm.addEventListener('submit', function (event) {
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

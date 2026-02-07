@extends('layouts.admin')

@php
    $pageTitle = 'Approve Test Result';
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
        .panel {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 16px;
        }

        .filters {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 12px;
        }

        .field input,
        .field select {
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

        .tabs {
            display: flex;
            gap: 10px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 8px;
        }

        .tab {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            padding: 4px 8px;
            border-radius: 6px;
            background: #f5f8fa;
            cursor: pointer;
        }

        .tab.active {
            background: var(--accent);
            color: #fff;
        }

        .detail-header {
            font-weight: 600;
            margin: 10px 0 6px;
            font-size: 12px;
        }

        .comment {
            width: 100%;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 12px;
        }

        .btn {
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }

        .btn.secondary {
            background: #f1f5f8;
            color: var(--muted);
            border: 1px solid var(--line);
        }

        .btn.reject {
            background: #f5b7b1;
            color: #7a1d1d;
        }

        @media (max-width: 1100px) {
            .filters {
                grid-template-columns: repeat(3, 1fr);
            }

            .split {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @if ($items->isEmpty())
        <div class="notice">
            Approval pending: no validated results are ready. Validate results first before approval.
        </div>
    @endif

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
                    <label>Test Name</label>
                    <input id="filterTest" type="text" placeholder="Test name">
                </div>
                <div class="field">
                    <label>Find</label>
                    <button id="filterBtn" class="btn secondary" type="button">Find</button>
                </div>
            </div>

            <div class="split">
                <div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Barcode No</th>
                                    <th>Specimen No</th>
                                    <th>Patient Name</th>
                                </tr>
                            </thead>
                            <tbody id="specimenList">
                                @forelse ($items as $item)
                                    <tr data-id="{{ $item->id }}"
                                        data-specimen="{{ $item->specimen->specimen_no ?? '-' }}"
                                        data-patient="{{ $item->specimen->patient->name ?? '-' }}"
                                        data-test="{{ $item->testMaster->name ?? '-' }}"
                                        data-result="{{ $item->result->result_value ?? '-' }}"
                                        data-department="{{ $item->testMaster->department->name ?? '-' }}">
                                        <td>{{ $item->specimen->specimen_no ?? '-' }}</td>
                                        <td>{{ $item->specimen->specimen_no ?? '-' }}</td>
                                        <td>{{ $item->specimen->patient->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No results pending approval.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="detail-header">Test Name / Flag</div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Test Name</th>
                                    <th>Flag</th>
                                </tr>
                            </thead>
                            <tbody id="flagList">
                                <tr>
                                    <td id="flagTestName">-</td>
                                    <td id="flagValue">Pending</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <div class="detail-header" id="reportTitle">Select a result</div>
                    <div class="tabs">
                        <div class="tab active" data-tab="normal">Normal Test</div>
                        <div class="tab" data-tab="range">Range Test</div>
                        <div class="tab" data-tab="culture">Culture Test</div>
                        <div class="tab" data-tab="summary">Summary Test</div>
                    </div>
                    <div class="table-wrap" style="margin-top:10px;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Test Parameter</th>
                                    <th>Test Result Value</th>
                                    <th>Other Result Value</th>
                                    <th>Units</th>
                                    <th>Ref. Values</th>
                                    <th>Result Type</th>
                                </tr>
                            </thead>
                            <tbody id="resultDetails">
                                <tr>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <form method="post" action="{{ route('results.approve.action') }}" style="margin-top:10px;">
                        @csrf
                        <input type="hidden" name="specimen_test_id" id="selectedSpecimenTest">
                        <div class="detail-header">Approval Comment</div>
                        <input class="comment" type="text" name="comment" placeholder="Approval / reject comment">

                        <div class="actions">
                            <button class="btn secondary" name="action" value="reject" type="submit">Unapprove (F7)</button>
                            <button class="btn" name="action" value="approve" type="submit">Approve (F6)</button>
                            <button class="btn" name="action" value="approve_print" type="submit">Approve &amp; Print</button>
                        </div>
                    </form>
                </div>
            </div>
    </div>

    <script>
        (function () {
            var specimenList = document.getElementById('specimenList');
            var reportTitle = document.getElementById('reportTitle');
            var resultDetails = document.getElementById('resultDetails');
            var flagTestName = document.getElementById('flagTestName');
            var flagValue = document.getElementById('flagValue');
            var selectedInput = document.getElementById('selectedSpecimenTest');
            var filterBtn = document.getElementById('filterBtn');
            var filterSpecimen = document.getElementById('filterSpecimen');
            var filterDepartment = document.getElementById('filterDepartment');
            var filterTest = document.getElementById('filterTest');

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
                selectedInput.value = row.dataset.id;
                reportTitle.textContent = row.dataset.test || 'Selected Report';
                flagTestName.textContent = row.dataset.test || '-';
                flagValue.textContent = 'Pending';
                resultDetails.innerHTML = '' +
                    '<tr>' +
                    '<td>' + (row.dataset.test || '-') + '</td>' +
                    '<td>' + (row.dataset.result || '-') + '</td>' +
                    '<td>-</td>' +
                    '<td>-</td>' +
                    '<td>-</td>' +
                    '<td>Normal</td>' +
                    '</tr>';
            }

            specimenList.addEventListener('click', function (event) {
                var row = event.target.closest('tr');
                if (row) {
                    selectRow(row);
                }
            });

            filterBtn.addEventListener('click', function () {
                var spec = (filterSpecimen.value || '').toLowerCase();
                var dept = (filterDepartment.value || '').toLowerCase();
                var test = (filterTest.value || '').toLowerCase();
                Array.from(specimenList.querySelectorAll('tr')).forEach(function (row) {
                    var matchSpec = !spec || (row.dataset.specimen || '').toLowerCase().includes(spec);
                    var matchDept = !dept || (row.dataset.department || '').toLowerCase().includes(dept);
                    var matchTest = !test || (row.dataset.test || '').toLowerCase().includes(test);
                    row.style.display = (matchSpec && matchDept && matchTest) ? '' : 'none';
                });
            });

            var firstRow = specimenList.querySelector('tr[data-id]');
            if (firstRow) {
                selectRow(firstRow);
            }
        })();
    </script>
@endsection

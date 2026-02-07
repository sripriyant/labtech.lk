@extends('layouts.admin')

@php
    $pageTitle = 'Result Validation';
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

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .toolbar select {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
            background: #fff;
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

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            background: #0a6fb3;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }

        .btn.reject {
            background: #f5b7b1;
            color: #7a1d1d;
        }

        .btn.secondary {
            background: #f1f5f9;
            color: #0f172a;
            border: 1px solid var(--line);
            text-decoration: none;
        }

        .comment {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
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
    </style>

    @if ($items->isEmpty())
        <div class="notice">
            Validation pending: no results are ready yet. Please enter results first in Test Result Entry.
        </div>
    @endif

    <div class="toolbar">
        <div style="font-weight:600;color:var(--muted);">Pending Validation</div>
        <div>
            <label style="font-size:12px;color:var(--muted);margin-right:6px;">Sort</label>
            <select id="sortSelect">
                <option value="latest_desc" @selected(($sort ?? '') === 'latest_desc')>Latest Billing First</option>
                <option value="latest_asc" @selected(($sort ?? '') === 'latest_asc')>Oldest Billing First</option>
                <option value="patient_asc" @selected(($sort ?? '') === 'patient_asc')>Patient: A-Z</option>
                <option value="patient_desc" @selected(($sort ?? '') === 'patient_desc')>Patient: Z-A</option>
                <option value="specimen_asc" @selected(($sort ?? '') === 'specimen_asc')>Specimen No: A-Z</option>
                <option value="specimen_desc" @selected(($sort ?? '') === 'specimen_desc')>Specimen No: Z-A</option>
                <option value="test_asc" @selected(($sort ?? '') === 'test_asc')>Test: A-Z</option>
                <option value="test_desc" @selected(($sort ?? '') === 'test_desc')>Test: Z-A</option>
                <option value="flag_desc" @selected(($sort ?? '') === 'flag_desc')>Flag: High to Normal</option>
                <option value="flag_asc" @selected(($sort ?? '') === 'flag_asc')>Flag: Normal to High</option>
            </select>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Specimen No</th>
                    <th>Patient</th>
                    <th>Test</th>
                    <th>Result</th>
                    <th>Flag</th>
                    <th>Comment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $patientPalette = ['#0ea5e9', '#8b5cf6', '#10b981', '#f97316', '#ef4444', '#14b8a6', '#a855f7', '#f59e0b'];
                    $groupedItems = $items->groupBy(fn ($item) => $item->specimen?->patient_id ?? 0);
                @endphp
                @forelse ($groupedItems as $patientId => $groupItems)
                    @php
                        $first = $groupItems->first();
                        $patientName = $first->specimen->patient->name ?? '-';
                        $patientAge = $first->specimen?->age_display ?? '-';
                        $patientSex = $first->specimen->patient->sex ?? '-';
                        $color = $patientPalette[abs((int) $patientId) % count($patientPalette)];
                    @endphp
                    <tr class="patient-group" style="--patient-color: {{ $color }};">
                        <td colspan="7">{{ $patientName }} ({{ $patientAge }} / {{ $patientSex }})</td>
                    </tr>
                    @foreach ($groupItems as $item)
                        @php
                            $flag = strtoupper($item->validation_flag ?? '');
                            $flagClass = $flag ? strtolower($flag) : 'normal';
                            $resultValue = $item->result->result_value ?? null;
                            $resultText = $resultValue !== null && $resultValue !== '' ? $resultValue : 'Parameters';
                        @endphp
                        <tr>
                            <td>{{ $item->specimen->specimen_no ?? '-' }}</td>
                            <td>{{ $patientName }}</td>
                            <td>{{ $item->testMaster->name ?? '-' }}</td>
                            <td>{{ $resultText }}</td>
                            <td>
                                <span class="status-badge {{ $flagClass }}">{{ $flag ?: 'NORMAL' }}</span>
                            </td>
                            <td>
                                <form method="post" action="{{ route('results.validate.action') }}">
                                    @csrf
                                    <input type="hidden" name="specimen_test_id" value="{{ $item->id }}">
                                    <input class="comment" type="text" name="comment" placeholder="Validation note">
                            </td>
                            <td>
                                    <div class="actions">
                                        <a class="btn secondary" href="{{ route('reports.show', $item) }}" target="_blank" rel="noopener">View Report</a>
                                        <button class="btn" name="action" value="approve" type="submit">Validate</button>
                                        <button class="btn reject" name="action" value="reject" type="submit">Reject</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="7">No results pending validation.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        (function () {
            var sortSelect = document.getElementById('sortSelect');
            if (sortSelect) {
                sortSelect.addEventListener('change', function () {
                    var url = new URL(window.location.href);
                    url.searchParams.set('sort', sortSelect.value);
                    window.location.href = url.toString();
                });
            }
        })();
    </script>
@endsection

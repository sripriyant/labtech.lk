@extends('layouts.admin')

@php
    $pageTitle = 'Patient Statistics';
@endphp

@section('content')
    <style>
        .filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
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

        .summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .summary-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            display: grid;
            gap: 6px;
            border-left: 4px solid #6c4cf5;
        }

        .summary-card .label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
        }

        .summary-card .value {
            font-size: 20px;
            font-weight: 700;
            color: #0b3f2c;
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
            background: #f0f1ff;
            color: #2e2e3a;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid var(--line);
        }

        tbody td {
            padding: 8px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
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

        .btn.secondary {
            background: #f1f5f8;
            color: var(--muted);
            border: 1px solid var(--line);
        }

        .trend-table {
            margin-bottom: 12px;
        }

        @media (max-width: 1100px) {
            .filters {
                grid-template-columns: 1fr 1fr;
            }

            .summary {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="card">
        <form method="get" action="{{ url('/admin/page/patient-stats') }}">
            <div class="filters">
                <div class="field">
                    <label>From</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
                </div>
                <div class="field">
                    <label>To</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Group</label>
                    <select name="group">
                        <option value="day" @selected(($filters['group'] ?? '') === 'day')>Daily</option>
                        <option value="month" @selected(($filters['group'] ?? '') === 'month')>Monthly</option>
                        <option value="year" @selected(($filters['group'] ?? '') === 'year')>Annual</option>
                    </select>
                </div>
                <div class="field">
                    <label>Search</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Specimen, patient, NIC, test">
                </div>
                <div class="field" style="align-self:end;">
                    <button class="btn" type="submit">Apply</button>
                    <a class="btn secondary" href="{{ url('/admin/page/patient-stats') }}">Reset</a>
                </div>
            </div>
        </form>

        <div class="summary">
            <div class="summary-card">
                <div class="label">Patients</div>
                <div class="value">{{ $totals['patients'] ?? 0 }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Specimens</div>
                <div class="value">{{ $totals['specimens'] ?? 0 }}</div>
            </div>
            <div class="summary-card">
                <div class="label">Tests</div>
                <div class="value">{{ $totals['tests'] ?? 0 }}</div>
            </div>
        </div>

        <div class="table-wrap trend-table">
            <table>
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Patient Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trendRows as $row)
                        <tr>
                            <td>{{ $row->label ?? '-' }}</td>
                            <td>{{ $row->patient_count ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">No data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Specimen No</th>
                        <th>Patient</th>
                        <th>Age/Sex</th>
                        <th>Center</th>
                        <th>Tests</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($specimens as $specimen)
                        <tr>
                            <td>{{ optional($specimen->created_at)->format('Y-m-d') }}</td>
                            <td>{{ $specimen->specimen_no ?? '-' }}</td>
                            <td>{{ $specimen->patient->name ?? '-' }}</td>
                            <td>{{ $specimen->age_display ?? '-' }} / {{ $specimen->patient->sex ?? '-' }}</td>
                            <td>{{ $specimen->center->name ?? '-' }}</td>
                            <td>
                                @foreach ($specimen->tests as $test)
                                    <div>{{ $test->testMaster->name ?? '-' }}</div>
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

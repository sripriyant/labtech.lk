@extends('layouts.admin')

@php
    $pageTitle = 'Patient Worksheet';
@endphp

@section('content')
    <style>
        .filters {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 12px;
        }

        .field input {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            background: #fff;
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

        .link-btn {
            background: #0a6fb3;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 12px;
        }

        .worksheet-page {
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 14mm;
            margin: 0 auto;
            background: #fff;
            box-sizing: border-box;
        }

        .worksheet-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 6px;
            text-align: center;
        }

        .worksheet-meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px 12px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .worksheet-meta div {
            border: 1px solid var(--line);
            padding: 6px 8px;
            border-radius: 6px;
        }

        .worksheet-table th,
        .worksheet-table td {
            padding: 6px 8px;
            border: 1px solid var(--line);
        }

        .worksheet-table th {
            background: #f5f7f9;
        }

        .worksheet-blank {
            height: 22px;
        }

        .worksheet-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 10px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .status-badge.printed {
            background: #e7f6ee;
            color: #0f7a47;
            border-color: #b7e3cc;
        }

        .status-badge.pending {
            background: #fff3e6;
            color: #b45309;
            border-color: #f5d0a6;
        }

        .worksheet-toggle {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-right: auto;
            font-size: 12px;
            color: var(--muted);
        }

        .worksheet-toggle a {
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid var(--line);
            color: #0a6fb3;
            font-weight: 600;
        }

        .worksheet-toggle a.active {
            background: #0a6fb3;
            color: #fff;
            border-color: #0a6fb3;
        }

        @media print {
            body { background: #fff; }
            .sidebar,
            .topbar {
                display: none !important;
            }
            .shell {
                grid-template-columns: 1fr;
            }
            .content {
                padding: 0;
            }
            .worksheet-actions,
            .filters,
            .table-wrap {
                display: none;
            }
            .worksheet-page {
                padding: 0;
                width: auto;
                min-height: auto;
            }
        }
    </style>

    @if (!empty($specimen))
        @php
            $layout = request()->query('layout', 'single');
            $compact = request()->query('compact', '0') === '1';
            $multipleTests = $specimen->tests->count() > 1;
            $printed = $specimen->tests->firstWhere('printed_at', '!=', null);
            $statusLabel = $printed ? 'Printed' : 'Pending';
        @endphp
        <div class="worksheet-actions">
            <div class="worksheet-toggle">
                <span>Layout:</span>
                <a class="{{ $layout === 'single' ? 'active' : '' }}" href="{{ url('/admin/page/print-worksheet?specimen_id=' . $specimen->id . '&layout=single' . ($compact ? '&compact=1' : '')) }}">Single Page</a>
                <a class="{{ $layout === 'per_test' ? 'active' : '' }}" href="{{ url('/admin/page/print-worksheet?specimen_id=' . $specimen->id . '&layout=per_test' . ($compact ? '&compact=1' : '')) }}">Per Test A4</a>
                @if ($multipleTests)
                    <a class="{{ $compact ? 'active' : '' }}" href="{{ url('/admin/page/print-worksheet?specimen_id=' . $specimen->id . '&layout=' . $layout . ($compact ? '' : '&compact=1')) }}">Compact Parameters</a>
                @endif
            </div>
            <button class="btn" type="button" onclick="window.print()">Print Worksheet</button>
            <a class="btn secondary" href="{{ url('/admin/page/print-worksheet') }}">Back</a>
        </div>
        @php
            $testsToRender = $layout === 'per_test' ? $specimen->tests : collect([$specimen->tests]);
        @endphp
        @foreach ($testsToRender as $testGroup)
            @php
                $tests = $layout === 'per_test' ? collect([$testGroup]) : $testGroup;
            @endphp
            <div class="worksheet-page">
                <div class="worksheet-title">LAB RESULT WORKSHEET</div>
                <div class="worksheet-meta">
                    <div>Specimen No: {{ $specimen->specimen_no ?? '-' }}</div>
                    <div>Patient: {{ $specimen->patient->name ?? '-' }}</div>
                    <div>Age/Sex: {{ $specimen->age_display ?? '-' }} / {{ $specimen->patient->sex ?? '-' }}</div>
                    <div>NIC: {{ $specimen->patient->nic ?? '-' }}</div>
                    <div>Center: {{ $specimen->center->name ?? '-' }}</div>
                    <div>Date: {{ optional($specimen->created_at)->format('Y-m-d') }}</div>
                    <div>Status:
                        <span class="status-badge {{ $printed ? 'printed' : 'pending' }}">{{ $statusLabel }}</span>
                    </div>
                </div>
                <table class="worksheet-table" style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead>
                        <tr>
                            <th style="width:55%;">Test / Parameter</th>
                            <th style="width:20%;">Result</th>
                            <th style="width:25%;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tests as $test)
                            <tr>
                                <td style="font-weight:700;">
                                    {{ $test->testMaster->name ?? '-' }}
                                    @php
                                        $tube = trim((string) ($test->testMaster->tube_color ?? ''));
                                        $container = trim((string) ($test->testMaster->container_type ?? ''));
                                    @endphp
                                    @if ($tube || $container)
                                        <div style="font-weight:400;color:#5b6b74;font-size:11px;margin-top:4px;">
                                            @if ($tube)
                                                Tube: {{ $tube }}
                                            @endif
                                            @if ($tube && $container)
                                                |
                                            @endif
                                            @if ($container)
                                                Container: {{ $container }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="worksheet-blank"></td>
                                <td class="worksheet-blank"></td>
                            </tr>
                            @php
                                $parameters = $test->testMaster?->parameters ?? collect();
                                $showParameters = $parameters;
                                $omitted = 0;
                                if ($compact && $multipleTests) {
                                    $showParameters = $parameters->take(5);
                                    $omitted = max(0, $parameters->count() - $showParameters->count());
                                }
                            @endphp
                            @foreach ($showParameters as $parameter)
                                <tr>
                                    <td style="padding-left:18px;">- {{ $parameter->name }}</td>
                                    <td class="worksheet-blank"></td>
                                    <td class="worksheet-blank"></td>
                                </tr>
                            @endforeach
                            @if ($omitted > 0)
                                <tr>
                                    <td style="padding-left:18px;color:#6b7280;">{{ $omitted }} more parameters omitted</td>
                                    <td class="worksheet-blank"></td>
                                    <td class="worksheet-blank"></td>
                                </tr>
                            @endif
                        @endforeach
                        @for ($i = 0; $i < 16; $i++)
                            <tr>
                                <td class="worksheet-blank"></td>
                                <td class="worksheet-blank"></td>
                                <td class="worksheet-blank"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <form method="get" action="{{ url('/admin/page/print-worksheet') }}">
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
                    <label>Specimen No</label>
                    <input type="text" name="specimen_no" value="{{ $filters['specimen_no'] ?? '' }}">
                </div>
                <div class="field">
                    <label>NIC</label>
                    <input type="text" name="nic" value="{{ $filters['nic'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Sort</label>
                    <select name="sort">
                        <option value="date_desc" @selected(($filters['sort'] ?? '') === 'date_desc')>Date: Newest</option>
                        <option value="date_asc" @selected(($filters['sort'] ?? '') === 'date_asc')>Date: Oldest</option>
                        <option value="specimen_asc" @selected(($filters['sort'] ?? '') === 'specimen_asc')>Specimen No: A-Z</option>
                        <option value="specimen_desc" @selected(($filters['sort'] ?? '') === 'specimen_desc')>Specimen No: Z-A</option>
                    </select>
                </div>
                <div class="field">
                    <label>Layout</label>
                    <select name="layout">
                        <option value="single" @selected(($filters['layout'] ?? 'single') === 'single')>Single Page</option>
                        <option value="per_test" @selected(($filters['layout'] ?? '') === 'per_test')>Per Test A4</option>
                    </select>
                </div>
                <div class="field">
                    <label>Compact Parameters</label>
                    <label>
                        <input type="checkbox" name="compact" value="1" {{ ($filters['compact'] ?? '0') === '1' ? 'checked' : '' }}>
                        Enable for multi-test
                    </label>
                </div>
                <div class="field" style="align-self:end;">
                    <button class="btn" type="submit">Search</button>
                    <a class="btn secondary" href="{{ url('/admin/page/print-worksheet') }}">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Specimen No</th>
                        <th>Patient</th>
                        <th>NIC</th>
                        <th>Center</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Worksheet</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($specimens ?? []) as $specimen)
                        @php
                            $printed = $specimen->tests->firstWhere('printed_at', '!=', null);
                            $statusLabel = $printed ? 'Printed' : 'Pending';
                        @endphp
                        <tr>
                            <td>{{ $specimen->specimen_no ?? '-' }}</td>
                            <td>{{ $specimen->patient->name ?? '-' }}</td>
                            <td>{{ $specimen->patient->nic ?? '-' }}</td>
                            <td>{{ $specimen->center->name ?? '-' }}</td>
                            <td>{{ optional($specimen->created_at)->format('Y-m-d') }}</td>
                            <td>
                                <span class="status-badge {{ $printed ? 'printed' : 'pending' }}">{{ $statusLabel }}</span>
                            </td>
                            <td>
                                <a class="link-btn" href="{{ url('/admin/page/print-worksheet?specimen_id=' . $specimen->id . '&layout=' . ($filters['layout'] ?? 'single') . ((($filters['compact'] ?? '0') === '1') ? '&compact=1' : '')) }}">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No specimens found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection

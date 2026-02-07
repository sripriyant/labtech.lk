@extends('layouts.admin')

@php
    $pageTitle = 'Print Billing';
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

        @media (max-width: 1000px) {
            .filters {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <form method="get" action="{{ url('/billing/print') }}">
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
                    <option value="date_desc" {{ ($filters['sort'] ?? '') === 'date_desc' ? 'selected' : '' }}>Date (Newest)</option>
                    <option value="date_asc" {{ ($filters['sort'] ?? '') === 'date_asc' ? 'selected' : '' }}>Date (Oldest)</option>
                    <option value="specimen_asc" {{ ($filters['sort'] ?? '') === 'specimen_asc' ? 'selected' : '' }}>Specimen No (A-Z)</option>
                    <option value="specimen_desc" {{ ($filters['sort'] ?? '') === 'specimen_desc' ? 'selected' : '' }}>Specimen No (Z-A)</option>
                    <option value="patient_asc" {{ ($filters['sort'] ?? '') === 'patient_asc' ? 'selected' : '' }}>Patient (A-Z)</option>
                    <option value="patient_desc" {{ ($filters['sort'] ?? '') === 'patient_desc' ? 'selected' : '' }}>Patient (Z-A)</option>
                </select>
            </div>
            <div class="field" style="align-self:end;">
                <button class="btn" type="submit">Search</button>
                <a class="btn secondary" href="{{ url('/billing/print') }}">Reset</a>
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
                    <th>Print</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($specimens as $specimen)
                    <tr>
                        <td>{{ $specimen->specimen_no ?? '-' }}</td>
                        <td>{{ $specimen->patient->name ?? '-' }}</td>
                        <td>{{ $specimen->patient->nic ?? '-' }}</td>
                        <td>{{ $specimen->center->name ?? '-' }}</td>
                        <td>{{ optional($specimen->created_at)->format('Y-m-d') }}</td>
                        <td><a class="link-btn" href="{{ route('billing.print', $specimen) }}">Open</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No invoices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

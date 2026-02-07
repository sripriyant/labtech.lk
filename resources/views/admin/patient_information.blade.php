@extends('layouts.admin')

@php
    $pageTitle = 'Patient Information';
    $letters = range('A', 'Z');
@endphp

@section('content')
    <style>
        .filters {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 12px;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 13px;
        }

        .field input,
        .field select {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .az-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }

        .az-bar a {
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 13px;
            color: var(--muted);
            background: #fff;
        }

        .az-bar a.active {
            background: #0b5a77;
            color: #fff;
            border-color: #0b5a77;
        }

        .table-wrap {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
            position: relative;
        }

        .table-wrap::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #00c6ff, #0072ff, #00b894, #f39c12, #e74c3c);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
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

        .filter-actions {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }

        .btn {
            background: #0a6fb3;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }

        .btn.secondary {
            background: #f1f5f8;
            color: var(--muted);
            border: 1px solid var(--line);
        }

        .row-actions {
            display: flex;
            gap: 6px;
        }

        .btn.link {
            background: #0a6fb3;
            color: #fff;
            padding: 6px 8px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            border: none;
        }

        .btn.link.danger {
            background: #c0392b;
        }

        @media (max-width: 1200px) {
            .filters {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>

    <div class="card" style="margin-bottom:12px;">
        <div class="az-bar">
            <a class="{{ ($filters['letter'] ?? '') === '' ? 'active' : '' }}" href="{{ url('/admin/patient-information') }}">All</a>
            @foreach ($letters as $letter)
                <a class="{{ ($filters['letter'] ?? '') === $letter ? 'active' : '' }}"
                   href="{{ url('/admin/patient-information') }}?letter={{ $letter }}">{{ $letter }}</a>
            @endforeach
        </div>
    </div>

    <div class="card" style="margin-bottom:12px;">
        <form method="get" action="{{ url('/admin/patient-information') }}">
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
                    <label>UHID</label>
                    <input type="text" name="uhid" value="{{ $filters['uhid'] ?? '' }}">
                </div>
                <div class="field">
                    <label>NIC</label>
                    <input type="text" name="nic" value="{{ $filters['nic'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ $filters['phone'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Specimen No</label>
                    <input type="text" name="specimen_no" value="{{ $filters['specimen_no'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Test Code/Name</label>
                    <input type="text" name="test" value="{{ $filters['test'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Department</label>
                    <input type="text" name="department" value="{{ $filters['department'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Center</label>
                    <input type="text" name="center" value="{{ $filters['center'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Sex</label>
                    <select name="sex">
                        <option value="">All</option>
                        <option value="Male" {{ ($filters['sex'] ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ ($filters['sex'] ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All</option>
                        @foreach (['ORDERED', 'RESULT_ENTERED', 'VALIDATED', 'APPROVED', 'REJECTED'] as $status)
                            <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Sort</label>
                    <select name="sort">
                        <option value="date_desc" {{ ($filters['sort'] ?? '') === 'date_desc' ? 'selected' : '' }}>Date (Newest)</option>
                        <option value="date_asc" {{ ($filters['sort'] ?? '') === 'date_asc' ? 'selected' : '' }}>Date (Oldest)</option>
                        <option value="name_asc" {{ ($filters['sort'] ?? '') === 'name_asc' ? 'selected' : '' }}>Patient Name (A-Z)</option>
                        <option value="name_desc" {{ ($filters['sort'] ?? '') === 'name_desc' ? 'selected' : '' }}>Patient Name (Z-A)</option>
                        <option value="uhid_asc" {{ ($filters['sort'] ?? '') === 'uhid_asc' ? 'selected' : '' }}>UHID (A-Z)</option>
                        <option value="uhid_desc" {{ ($filters['sort'] ?? '') === 'uhid_desc' ? 'selected' : '' }}>UHID (Z-A)</option>
                        <option value="specimen_asc" {{ ($filters['sort'] ?? '') === 'specimen_asc' ? 'selected' : '' }}>Specimen No (A-Z)</option>
                        <option value="specimen_desc" {{ ($filters['sort'] ?? '') === 'specimen_desc' ? 'selected' : '' }}>Specimen No (Z-A)</option>
                        <option value="status_asc" {{ ($filters['sort'] ?? '') === 'status_asc' ? 'selected' : '' }}>Status (A-Z)</option>
                        <option value="status_desc" {{ ($filters['sort'] ?? '') === 'status_desc' ? 'selected' : '' }}>Status (Z-A)</option>
                    </select>
                </div>
                <div class="field">
                    <label>Age Min</label>
                    <input type="number" name="age_min" min="0" value="{{ $filters['age_min'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Age Max</label>
                    <input type="number" name="age_max" min="0" value="{{ $filters['age_max'] ?? '' }}">
                </div>
                <div class="filter-actions">
                    <button class="btn" type="submit">Search</button>
                    <a class="btn secondary" href="{{ url('/admin/patient-information') }}">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>UHID</th>
                    <th>Patient Name</th>
                    <th>NIC</th>
                    <th>Phone</th>
                    <th>Sex</th>
                    <th>Age</th>
                    <th>Specimen No</th>
                    <th>Center</th>
                    <th>Department</th>
                    <th>Test</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $patient = $row->specimen->patient;
                        $dob = $patient?->dob;
                        $age = $dob ? $dob->age : '-';
                        $phoneRaw = $patient?->phone ?? '';
                        $phoneDigits = preg_replace('/\D+/', '', (string) $phoneRaw);
                        $waLink = $phoneDigits ? 'https://wa.me/' . $phoneDigits : null;
                    @endphp
                    <tr>
                        <td>{{ $patient?->uhid ?? '-' }}</td>
                        <td>{{ $patient?->name ?? '-' }}</td>
                        <td>{{ $patient?->nic ?? '-' }}</td>
                        <td>
                            @if ($waLink)
                                <a href="{{ $waLink }}" target="_blank" rel="noopener">{{ $patient?->phone ?? '-' }}</a>
                            @else
                                {{ $patient?->phone ?? '-' }}
                            @endif
                        </td>
                        <td>{{ $patient?->sex ?? '-' }}</td>
                        <td>{{ $age }}</td>
                        <td>{{ $row->specimen->specimen_no ?? '-' }}</td>
                        <td>{{ $row->specimen->center->name ?? '-' }}</td>
                        <td>{{ $row->testMaster->department->name ?? '-' }}</td>
                        <td>{{ $row->testMaster->name ?? '-' }}</td>
                        <td>{{ $row->status }}</td>
                        <td>{{ optional($row->specimen->created_at)->format('Y-m-d') }}</td>
                        <td>
                            <div class="row-actions">
                                <a class="btn link" href="{{ route('patient.information.edit', $patient) }}">Edit</a>
                                <form method="post" action="{{ route('patient.information.destroy', $patient) }}" onsubmit="return confirm('Delete this patient and all related specimens?');">
                                    @csrf
                                    <button class="btn link danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13">No patients found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

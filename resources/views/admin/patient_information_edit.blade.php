@extends('layouts.admin')

@php
    $pageTitle = 'Edit Patient';
@endphp

@section('content')
    <style>
        .edit-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
            max-width: 720px;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .field input,
        .field select {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
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
    </style>

    <form class="edit-card" method="post" action="{{ route('patient.information.update', $patient) }}">
        @csrf
        <div class="field">
            <label>UHID</label>
            <input type="text" name="uhid" value="{{ old('uhid', $patient->uhid) }}" required>
        </div>
        <div class="field">
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $patient->name) }}" required>
        </div>
        <div class="field">
            <label>NIC</label>
            <input type="text" name="nic" value="{{ old('nic', $patient->nic) }}">
        </div>
        <div class="field">
            <label>DOB</label>
            <input type="date" name="dob" value="{{ old('dob', optional($patient->dob)->format('Y-m-d')) }}">
        </div>
        <div class="field">
            <label>Sex</label>
            <select name="sex">
                <option value="">Select</option>
                <option value="Male" {{ old('sex', $patient->sex) === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ old('sex', $patient->sex) === 'Female' ? 'selected' : '' }}>Female</option>
            </select>
        </div>
        <div class="field">
            <label>Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}">
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $patient->email) }}">
        </div>
        <div class="field">
            <label>Nationality</label>
            <input type="text" name="nationality" value="{{ old('nationality', $patient->nationality) }}">
        </div>
        <div class="actions">
            <a class="btn secondary" href="{{ url('/admin/patient-information') }}">Cancel</a>
            <button class="btn" type="submit">Save</button>
        </div>
    </form>
@endsection

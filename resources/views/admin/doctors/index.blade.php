@extends('layouts.admin')

@php
    $pageTitle = 'Doctor / Consultant Master';
@endphp

@section('content')
    <style>
        .form-grid select[multiple] {
            min-height: 120px;
        }
    </style>
    <div class="card">
        <form method="post" action="{{ route('doctors.store') }}">
            @csrf
            <div class="form-grid" style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                <div>
                    <label for="name">Doctor Name</label>
                    <input id="name" name="name" type="text" required>
                </div>
                <div>
                    <label for="registration_no">Registration No</label>
                    <input id="registration_no" name="registration_no" type="text">
                </div>
                <div>
                    <label for="specialty">Specialty</label>
                    <input id="specialty" name="specialty" type="text">
                </div>
                <div>
                    <label for="referral_discount_pct">Referral Discount %</label>
                    <input id="referral_discount_pct" name="referral_discount_pct" type="number" step="0.01" min="0" max="100" value="0">
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
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:16px;">
                <label><input type="checkbox" name="can_approve" value="1"> Approval Permission</label>
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="btn" type="submit">Add Doctor</button>
            </div>
        </form>
    </div>

    @if (!empty($isSuperAdmin) && $isSuperAdmin)
        <div class="card" style="margin-top:20px;">
            <form method="post" action="{{ route('doctors.copy') }}">
                @csrf
                <div class="form-grid" style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                    <div>
                        <label for="copy_doctor_id">Copy Existing Global Doctor</label>
                        <select id="copy_doctor_id" name="doctor_id" required>
                            <option value="">Select</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
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
                    <button class="btn" type="submit">Copy Doctor</button>
                </div>
            </form>
        </div>
    @endif

    <div class="card" style="margin-top:20px;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr>
                    <th style="padding:12px 10px;border-bottom:1px solid var(--line);">Name</th>
                    <th style="padding:12px 10px;border-bottom:1px solid var(--line);">Registration No</th>
                    <th style="padding:12px 10px;border-bottom:1px solid var(--line);">Specialty</th>
                    <th style="padding:12px 10px;border-bottom:1px solid var(--line);">Referral %</th>
                    <th style="padding:12px 10px;border-bottom:1px solid var(--line);">Can Approve</th>
                    <th style="padding:12px 10px;border-bottom:1px solid var(--line);">Status</th>
                    <th style="padding:12px 10px;border-bottom:1px solid var(--line);">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($doctors as $doctor)
                    @php
                        $updateFormId = 'doctor-update-' . $doctor->id;
                        $deleteFormId = 'doctor-delete-' . $doctor->id;
                    @endphp
                    <form id="{{ $updateFormId }}" method="post" action="{{ route('doctors.update', $doctor) }}">
                        @csrf
                    </form>
                    <form id="{{ $deleteFormId }}" method="post" action="{{ route('doctors.destroy', $doctor) }}">
                        @csrf
                    </form>
                    <tr>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <input form="{{ $updateFormId }}" class="row-input" name="name" value="{{ $doctor->name }}" style="width:100%;border-radius:6px;border:1px solid var(--line);padding:6px 8px;">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <input form="{{ $updateFormId }}" class="row-input" name="registration_no" value="{{ $doctor->registration_no }}" style="width:100%;border-radius:6px;border:1px solid var(--line);padding:6px 8px;">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <input form="{{ $updateFormId }}" class="row-input" name="specialty" value="{{ $doctor->specialty }}" style="width:100%;border-radius:6px;border:1px solid var(--line);padding:6px 8px;">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <input form="{{ $updateFormId }}" class="row-input" name="referral_discount_pct" type="number" step="0.01" min="0" max="100" value="{{ $doctor->referral_discount_pct ?? 0 }}" style="width:100%;border-radius:6px;border:1px solid var(--line);padding:6px 8px;">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <label style="display:flex;align-items:center;gap:6px;">
                                <input form="{{ $updateFormId }}" type="checkbox" name="can_approve" value="1" {{ $doctor->can_approve ? 'checked' : '' }}>
                                <span>Allow</span>
                            </label>
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <label style="display:flex;flex-direction:column;gap:4px;">
                                <label style="display:flex;align-items:center;gap:6px;">
                                    <input form="{{ $updateFormId }}" type="checkbox" name="is_active" value="1" {{ $doctor->is_active ? 'checked' : '' }}>
                                    <span>{{ $doctor->is_active ? 'Active' : 'Inactive' }}</span>
                                </label>
                                <span style="font-size:11px;color:#6b7280;">Status toggles save driver</span>
                            </label>
                        </td>
                        <td class="row-actions" style="padding:12px 10px;border-bottom:1px solid var(--line);display:flex;gap:8px;">
                            <button class="btn-small" form="{{ $updateFormId }}" type="submit">Save</button>
                            <button class="btn-small secondary" form="{{ $deleteFormId }}" type="submit" onclick="return confirm('Delete this doctor?');">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:12px 10px;">No doctors added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

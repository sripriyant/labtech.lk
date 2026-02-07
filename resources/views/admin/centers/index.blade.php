@extends('layouts.admin')

@php
    $pageTitle = 'Center / Branch Master';
@endphp

@section('content')
    <style>
        .form-grid select[multiple] {
            min-height: 120px;
        }
    </style>
    <div class="card">
        <form method="post" action="{{ route('centers.store') }}">
            @csrf
            <div class="form-grid" style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                <div>
                    <label for="code">Center Code</label>
                    <input id="code" name="code" type="text" required>
                </div>
                <div>
                    <label for="name">Center Name</label>
                    <input id="name" name="name" type="text" required>
                </div>
                <div>
                    <label for="address">Address</label>
                    <input id="address" name="address" type="text">
                </div>
                <div>
                    <label for="contact_phone">Contact Phone</label>
                    <input id="contact_phone" name="contact_phone" type="text">
                </div>
                <div>
                    <label for="contact_email">Contact Email</label>
                    <input id="contact_email" name="contact_email" type="email">
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
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="btn" type="submit">Add Center</button>
            </div>
        </form>
    </div>

    @if (!empty($isSuperAdmin) && $isSuperAdmin)
        <div class="card" style="margin-top:20px;">
            <form method="post" action="{{ route('centers.copy') }}">
                @csrf
                <div class="form-grid" style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                    <div>
                        <label for="copy_center_id">Copy Existing Global Center</label>
                        <select id="copy_center_id" name="center_id" required>
                            <option value="">Select</option>
                            @foreach ($centers as $center)
                                <option value="{{ $center->id }}">{{ $center->code }} - {{ $center->name }}</option>
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
                    <button class="btn" type="submit">Copy Center</button>
                </div>
            </form>
        </div>
    @endif

    <div class="card" style="margin-top:20px;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Code</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Name</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Address</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Contact</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Referral %</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Status</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($centers as $center)
                    <tr>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <input name="code" type="text" value="{{ $center->code }}" style="width:120px;" form="center-{{ $center->id }}">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <input name="name" type="text" value="{{ $center->name }}" style="min-width:180px;" form="center-{{ $center->id }}">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <input name="address" type="text" value="{{ $center->address ?? '' }}" style="min-width:200px;" form="center-{{ $center->id }}">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <div style="display:grid;gap:6px;">
                                <input name="contact_phone" type="text" value="{{ $center->contact_phone ?? '' }}" placeholder="Phone" form="center-{{ $center->id }}">
                                <input name="contact_email" type="email" value="{{ $center->contact_email ?? '' }}" placeholder="Email" form="center-{{ $center->id }}">
                            </div>
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <input name="referral_discount_pct" type="number" step="0.01" min="0" max="100" value="{{ number_format($center->referral_discount_pct ?? 0, 2, '.', '') }}" style="width:90px;" form="center-{{ $center->id }}">
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <label style="display:flex;align-items:center;gap:6px;font-size:12px;">
                                <input type="checkbox" name="is_active" value="1" {{ $center->is_active ? 'checked' : '' }} form="center-{{ $center->id }}">
                                Active
                            </label>
                        </td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                <button class="btn" type="submit" form="center-{{ $center->id }}">Save</button>
                                <button class="btn secondary" type="submit" form="center-delete-{{ $center->id }}">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <form method="post" action="{{ route('centers.update', $center) }}" id="center-{{ $center->id }}">
                        @csrf
                    </form>
                    <form method="post" action="{{ route('centers.destroy', $center) }}" id="center-delete-{{ $center->id }}" onsubmit="return confirm('Delete this center?');">
                        @csrf
                    </form>
                @empty
                    <tr>
                        <td colspan="7" style="padding:12px 10px;">No centers added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

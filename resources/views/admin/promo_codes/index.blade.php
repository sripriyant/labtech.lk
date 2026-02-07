@extends('layouts.admin')

@php
    $pageTitle = 'Promo Codes';
@endphp

@section('content')
    <div class="page">
        @if (!empty($tableMissing))
            <div class="card" style="padding:12px;margin-bottom:12px;background:#fff6e5;border:1px solid #f1c27b;color:#7a4b00;">
                Promo codes table is missing. Run `php artisan migrate` and refresh.
            </div>
        @endif
        <div class="card" style="padding:16px;margin-bottom:16px;">
            <h2 style="margin:0 0 12px;">Create Promo Code</h2>
            <form method="post" action="{{ route('promo-codes.store') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:10px;">
                    <div>
                        <label>Code</label>
                        <input name="code" type="text" required>
                    </div>
                    <div>
                        <label>Type</label>
                        <select name="type">
                            <option value="PERCENT">Percent (%)</option>
                            <option value="FLAT">Flat</option>
                        </select>
                    </div>
                    <div>
                        <label>Value</label>
                        <input name="value" type="number" step="0.01" min="0" required>
                    </div>
                    <div>
                        <label>Start Date</label>
                        <input name="starts_at" type="date">
                    </div>
                    <div>
                        <label>End Date</label>
                        <input name="ends_at" type="date">
                    </div>
                    <div>
                        <label>Max Uses</label>
                        <input name="max_uses" type="number" min="1">
                    </div>
                </div>
                <div style="margin-top:10px;display:flex;align-items:center;gap:10px;">
                    <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                    <button class="action-btn" type="submit">Save</button>
                </div>
            </form>
        </div>

        <div class="card" style="padding:16px;">
            <h2 style="margin:0 0 12px;">Promo Codes</h2>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Uses</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($promoCodes as $promo)
                        <tr>
                            <td>
                                <form method="post" action="{{ route('promo-codes.update', $promo) }}" style="display:grid;grid-template-columns:repeat(7,1fr) auto;gap:8px;align-items:center;">
                                    @csrf
                                    <input name="code" type="text" value="{{ $promo->code }}" style="width:100%;">
                                    <select name="type">
                                        <option value="PERCENT" {{ $promo->type === 'PERCENT' ? 'selected' : '' }}>Percent</option>
                                        <option value="FLAT" {{ $promo->type === 'FLAT' ? 'selected' : '' }}>Flat</option>
                                    </select>
                                    <input name="value" type="number" step="0.01" min="0" value="{{ $promo->value }}">
                                    <input name="starts_at" type="date" value="{{ optional($promo->starts_at)->format('Y-m-d') }}">
                                    <input name="ends_at" type="date" value="{{ optional($promo->ends_at)->format('Y-m-d') }}">
                                    <input name="max_uses" type="number" min="1" value="{{ $promo->max_uses }}">
                                    <label style="white-space:nowrap;"><input type="checkbox" name="is_active" value="1" {{ $promo->is_active ? 'checked' : '' }}> Active</label>
                                    <button class="action-btn" type="submit">Update</button>
                                </form>
                            </td>
                            <td colspan="6"></td>
                            <td>
                                <form method="post" action="{{ route('promo-codes.destroy', $promo) }}">
                                    @csrf
                                    <button class="action-btn secondary" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No promo codes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

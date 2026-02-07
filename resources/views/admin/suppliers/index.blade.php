@extends('layouts.admin')

@php
    $pageTitle = 'Suppliers';
@endphp

@section('content')
    <style>
        .supplier-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .supplier-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .field input {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
            font-family: inherit;
        }

        .field textarea {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
            font-family: inherit;
        }

        .btn {
            background: #0a6fb3;
            color: #fff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }

        .row-input {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th, td {
            padding: 8px;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }

        @media (max-width: 1100px) {
            .supplier-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="supplier-grid">
        <div class="supplier-card">
            <h3 style="margin-top:0;">Add Supplier</h3>
            <form method="post" action="{{ route('suppliers.store') }}">
                @csrf
                <div class="field">
                    <label>Company Name</label>
                    <input name="company_name" type="text" required>
                </div>
                <div class="field">
                    <label>Contact Name</label>
                    <input name="contact_name" type="text">
                </div>
                <div class="field">
                    <label>Address</label>
                    <input name="address" type="text">
                </div>
                <div class="field">
                    <label>Phone</label>
                    <input name="phone" type="text">
                </div>
                <div class="field">
                    <label>Email</label>
                    <input name="email" type="email">
                </div>
                <div class="field">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="3"></textarea>
                </div>
                <div class="field">
                    <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                </div>
                <button class="btn" type="submit">Save Supplier</button>
            </form>
        </div>

        <div class="supplier-card">
            <h3 style="margin-top:0;">Suppliers</h3>
            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Remarks</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <form method="post" action="{{ route('suppliers.update', $supplier) }}">
                                @csrf
                                <td><input class="row-input" name="company_name" value="{{ $supplier->company_name }}"></td>
                                <td><input class="row-input" name="contact_name" value="{{ $supplier->contact_name }}"></td>
                                <td><input class="row-input" name="address" value="{{ $supplier->address }}"></td>
                                <td><input class="row-input" name="phone" value="{{ $supplier->phone }}"></td>
                                <td><input class="row-input" name="email" value="{{ $supplier->email }}"></td>
                                <td><input class="row-input" name="remarks" value="{{ $supplier->remarks }}"></td>
                                <td><input type="checkbox" name="is_active" value="1" {{ $supplier->is_active ? 'checked' : '' }}></td>
                                <td><button class="btn" type="submit">Save</button></td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No suppliers added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

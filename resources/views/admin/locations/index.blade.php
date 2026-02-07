@extends('layouts.admin')

@php
    $pageTitle = 'Locations';
@endphp

@section('content')
    <style>
        .location-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .location-card {
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

        .row-input {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        @media (max-width: 1100px) {
            .location-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="location-grid">
        <div class="location-card">
            <h3 style="margin-top:0;">Add Location</h3>
            <form method="post" action="{{ route('locations.store') }}">
                @csrf
                <div class="field">
                    <label>Location Code</label>
                    <input name="code" type="text" required>
                </div>
                <div class="field">
                    <label>Location Name</label>
                    <input name="name" type="text" required>
                </div>
                <div class="field">
                    <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                </div>
                <button class="btn" type="submit">Save Location</button>
            </form>
        </div>

        <div class="location-card">
            <h3 style="margin-top:0;">Locations</h3>
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $location)
                        <tr>
                            <form method="post" action="{{ route('locations.update', $location) }}">
                                @csrf
                                <td><input class="row-input" name="code" value="{{ $location->code }}"></td>
                                <td><input class="row-input" name="name" value="{{ $location->name }}"></td>
                                <td><input type="checkbox" name="is_active" value="1" {{ $location->is_active ? 'checked' : '' }}></td>
                                <td><button class="btn" type="submit">Save</button></td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No locations added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

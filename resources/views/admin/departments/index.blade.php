@extends('layouts.admin')

@php
    $pageTitle = 'Department Master';
@endphp

@section('content')
    <div class="card">
        <form method="post" action="{{ route('departments.store') }}">
            @csrf
            <div style="display:grid;gap:16px;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                <div>
                    <label for="code">Department Code</label>
                    <input id="code" name="code" type="text" required>
                </div>
                <div>
                    <label for="name">Department Name</label>
                    <input id="name" name="name" type="text" required>
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:16px;">
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="btn" type="submit">Add Department</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:20px;">
        <table style="width:100%;border-collapse:collapse;font-size:14px;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Code</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Name</th>
                    <th style="text-align:left;padding:12px 10px;border-bottom:1px solid var(--line);">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($departments as $department)
                    <tr>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">{{ $department->code }}</td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">{{ $department->name }}</td>
                        <td style="padding:12px 10px;border-bottom:1px solid var(--line);">
                            @if ($department->is_active)
                                <span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#e8f6f1;color:#0b5a40;font-size:12px;font-weight:600;">Active</span>
                            @else
                                <span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#f3f4f6;color:#475569;font-size:12px;font-weight:600;">Inactive</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="padding:12px 10px;">No departments added.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

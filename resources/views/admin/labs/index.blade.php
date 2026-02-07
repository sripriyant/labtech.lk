@extends('layouts.admin')

@php
    $pageTitle = 'Labs';
@endphp

@section('content')
    <style>
        .lab-shell {
            display: grid;
            gap: 18px;
        }

        .lab-card {
            background: linear-gradient(135deg, rgba(8, 185, 243, 0.08), rgba(1, 122, 168, 0.06));
            border: 1px solid rgba(1, 122, 168, 0.12);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
        }

        .lab-card h2 {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 700;
        }

        .lab-subtitle {
            margin: 0 0 18px;
            color: var(--muted);
            font-size: 13px;
        }

        .lab-form-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .lab-field label {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .lab-field input[type="text"],
        .lab-field input[type="email"],
        .lab-field input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            font-size: 14px;
            background: #fff;
        }

        .lab-check {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--ink);
        }

        .lab-actions {
            margin-top: 16px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .lab-table {
            margin-top: 6px;
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .lab-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .lab-table thead th {
            text-align: left;
            padding: 12px 10px;
            background: #f5f9fc;
            color: var(--muted);
            border-bottom: 1px solid var(--line);
        }

        .lab-table tbody td {
            padding: 10px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        .lab-table input[type="text"],
        .lab-table input[type="email"],
        .lab-table input[type="password"] {
            width: 100%;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .lab-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .lab-card {
                padding: 18px;
            }
        }
    </style>

    <div class="lab-shell">
        <div class="lab-card">
            <h2>Create Lab</h2>
            <p class="lab-subtitle">Create a new lab workspace with its own users, tests, and pricing.</p>
            <form method="post" action="{{ route('labs.store') }}">
                @csrf
                <div class="lab-form-grid">
                    <div class="lab-field">
                        <label>Lab Name</label>
                        <input name="lab_name" type="text" required placeholder="New lab name">
                    </div>
                    <div class="lab-field">
                        <label>Lab Code (unique)</label>
                        <input name="lab_code_prefix" type="text" placeholder="HIM" maxlength="10">
                    </div>
                    <div class="lab-field">
                        <label>Lab Admin Name</label>
                        <input name="admin_name" type="text" required placeholder="Admin name">
                    </div>
                    <div class="lab-field">
                        <label>Lab Admin Email</label>
                        <input name="admin_email" type="email" required placeholder="admin@lab.com">
                    </div>
                    <div class="lab-field">
                        <label>Lab Admin Password</label>
                        <input name="admin_password" type="password" required>
                    </div>
                    <div class="lab-field">
                        <label>Status</label>
                        <label class="lab-check">
                            <input type="checkbox" name="lab_active" value="1" checked>
                            Active
                        </label>
                    </div>
                    <div class="lab-field">
                        <label>SMS</label>
                        <label class="lab-check">
                            <input type="checkbox" name="lab_sms_enabled" value="1" checked>
                            SMS Enabled
                        </label>
                    </div>
                    <div class="lab-field">
                        <label>Account</label>
                        <label class="lab-check">
                            <input type="checkbox" name="assign_to_me" value="1">
                            Assign this lab to my account
                        </label>
                    </div>
                </div>
                <div class="lab-actions">
                    <button class="btn" type="submit">Create Lab</button>
                </div>
            </form>
        </div>

        <div class="lab-card">
            <h2>Existing Labs</h2>
            <p class="lab-subtitle">Manage lab details, admin credentials, and status. Passwords are encrypted; use “New Password” to reset.</p>
            <div class="lab-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Active</th>
                            <th>SMS</th>
                            <th>Admin User</th>
                            <th>Admin Email</th>
                            <th>New Password</th>
                            <th>Login</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($labs as $lab)
                            @php
                                $adminUser = $lab->users->first(function ($user) {
                                    return $user->roles->contains('name', 'Admin');
                                });
                            @endphp
                            <tr>
                                <td>
                                    <input name="lab_name" form="lab-update-{{ $lab->id }}" value="{{ $lab->name }}" required>
                                </td>
                                <td>
                                    <input name="lab_code_prefix" form="lab-update-{{ $lab->id }}" value="{{ $lab->code_prefix ?? '' }}" maxlength="10">
                                </td>
                                <td>
                                    <label class="lab-status">
                                        <input type="checkbox" name="lab_active" value="1" form="lab-update-{{ $lab->id }}" {{ $lab->is_active ? 'checked' : '' }}>
                                        <span>{{ $lab->is_active ? 'Active' : 'Inactive' }}</span>
                                    </label>
                                </td>
                                <td>
                                    <label class="lab-status">
                                        <input type="checkbox" name="lab_sms_enabled" value="1" form="lab-update-{{ $lab->id }}" {{ $lab->sms_enabled ? 'checked' : '' }}>
                                        <span>{{ $lab->sms_enabled ? 'Enabled' : 'Disabled' }}</span>
                                    </label>
                                </td>
                                <td>
                                    <input name="admin_name" form="lab-update-{{ $lab->id }}" value="{{ $adminUser?->name ?? '' }}" placeholder="Admin name">
                                </td>
                                <td>
                                    <input name="admin_email" form="lab-update-{{ $lab->id }}" value="{{ $adminUser?->email ?? '' }}" placeholder="admin@lab.com">
                                </td>
                                <td>
                                    <input name="admin_password" form="lab-update-{{ $lab->id }}" type="password" placeholder="New password">
                                </td>
                                <td>
                                    @if ($isSuperAdmin)
                                        <label class="lab-status">
                                            <input type="checkbox" name="admin_is_active" value="1" form="lab-update-{{ $lab->id }}" {{ ($adminUser?->is_active ?? true) ? 'checked' : '' }}>
                                            <span>{{ ($adminUser?->is_active ?? true) ? 'Enabled' : 'Disabled' }}</span>
                                        </label>
                                    @else
                                        <span>{{ ($adminUser?->is_active ?? true) ? 'Enabled' : 'Disabled' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                        <button class="btn" type="submit" form="lab-update-{{ $lab->id }}">Save</button>
                                        @if ($isSuperAdmin)
                                            <button class="btn secondary" type="submit" form="lab-delete-{{ $lab->id }}">Delete</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <form method="post" action="{{ route('labs.update', $lab) }}" id="lab-update-{{ $lab->id }}">
                                @csrf
                            </form>
                            <form method="post" action="{{ route('labs.destroy', $lab) }}" id="lab-delete-{{ $lab->id }}" onsubmit="return confirm('Delete this lab?');">
                                @csrf
                                @method('delete')
                            </form>
                        @empty
                            <tr>
                                <td colspan="9">No labs created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

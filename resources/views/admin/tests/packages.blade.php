@extends('layouts.admin')

@php
    $pageTitle = 'Packages';
@endphp

@section('content')
    <style>
        .test-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .test-grid .span-2 {
            grid-column: span 2;
        }

        .test-grid .span-4 {
            grid-column: span 4;
        }

        .test-grid label {
            font-size: 12px;
            color: var(--muted);
        }

        .test-grid input,
        .test-grid select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .multi-select {
            min-width: 200px;
            height: 120px;
        }

        .checklist {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px;
            background: #fff;
            max-height: 220px;
            overflow: auto;
            display: grid;
            gap: 6px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .checklist label {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 12px;
            color: var(--ink);
            min-width: 0;
        }

        .checklist-search {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
            margin-bottom: 8px;
        }

        .checklist-meta {
            font-size: 11px;
            color: var(--muted);
            margin-top: 6px;
        }

        details.package-items {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 8px 10px;
            background: #fff;
        }

        details.package-items summary {
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead th {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid var(--line);
            background: #f0f4f7;
            color: var(--muted);
        }

        tbody td {
            padding: 8px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        .row-input {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
        }

        .row-select {
            width: 100%;
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            font-size: 12px;
            background: #fff;
        }

        .row-actions {
            display: flex;
            gap: 6px;
        }

        .btn-small {
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            background: #0a6fb3;
            color: #fff;
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .test-grid {
                grid-template-columns: 1fr;
            }

            .test-grid .span-2,
            .test-grid .span-4 {
                grid-column: span 1;
            }

            .checklist {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="card">
        <form method="post" action="{{ route('packages.store') }}">
            @csrf
            <div class="test-grid">
                <div>
                    <label for="code">Package Code</label>
                    <input id="code" name="code" type="text" required>
                </div>
                <div>
                    <label for="name">Package Name</label>
                    <input id="name" name="name" type="text" required>
                </div>
                <div>
                    <label for="price">Package Price</label>
                    <input id="price" name="price" type="number" step="0.01" min="0">
                </div>
                <div class="span-4">
                    <label>Package Items (Tests)</label>
                    <input class="checklist-search" type="text" placeholder="Search by code or name" data-checklist-search="new-package">
                    <div class="checklist" data-checklist="new-package">
                        @foreach ($tests as $item)
                            <label data-label="{{ strtolower($item->code . ' ' . $item->name) }}">
                                <input type="checkbox" name="package_test_ids[]" value="{{ $item->id }}" required>
                                <span>{{ $item->code }} - {{ $item->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="checklist-meta" data-checklist-meta="new-package">0 tests selected</div>
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-top:16px;">
                <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button class="btn" type="submit">Add Package</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <form method="post" action="{{ route('packages.update', $package) }}">
                                @csrf
                                <td><input class="row-input" name="code" value="{{ $package->code }}"></td>
                                <td><input class="row-input" name="name" value="{{ $package->name }}"></td>
                                <td><input class="row-input" name="price" type="number" step="0.01" min="0" value="{{ $package->price }}"></td>
                                <td>
                                    <details class="package-items">
                                        <summary>{{ $package->packageItems->count() }} tests selected</summary>
                                        <div style="margin-top:8px;">
                                            <input class="checklist-search" type="text" placeholder="Search by code or name" data-checklist-search="package-{{ $package->id }}">
                                            <div class="checklist" data-checklist="package-{{ $package->id }}">
                                                @foreach ($tests as $item)
                                                    <label data-label="{{ strtolower($item->code . ' ' . $item->name) }}">
                                                        <input type="checkbox" name="package_test_ids[]" value="{{ $item->id }}" {{ $package->packageItems->contains($item->id) ? 'checked' : '' }}>
                                                        <span>{{ $item->code }} - {{ $item->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="checklist-meta" data-checklist-meta="package-{{ $package->id }}">
                                                {{ $package->packageItems->count() }} tests selected
                                            </div>
                                        </div>
                                    </details>
                                </td>
                                <td>
                                    <label style="display:flex;align-items:center;gap:6px;">
                                        <input type="checkbox" name="is_active" value="1" {{ $package->is_active ? 'checked' : '' }}>
                                        <span>{{ $package->is_active ? 'Active' : 'Inactive' }}</span>
                                    </label>
                                </td>
                                <td class="row-actions">
                                    <button class="btn-small" type="submit">Save</button>
                                    <button class="btn-small" type="submit" formaction="{{ route('packages.destroy', $package) }}" formmethod="post" onclick="return confirm('Delete this package?');" style="background:#b63b3b;">Delete</button>
                                </td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:12px 10px;">No packages added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <script>
        (function () {
            function bindChecklist(name) {
                var search = document.querySelector('[data-checklist-search="' + name + '"]');
                var list = document.querySelector('[data-checklist="' + name + '"]');
                var meta = document.querySelector('[data-checklist-meta="' + name + '"]');
                if (!list) {
                    return;
                }

                function updateMeta() {
                    if (!meta) {
                        return;
                    }
                    var checked = list.querySelectorAll('input[type="checkbox"]:checked').length;
                    meta.textContent = checked + ' tests selected';
                }

                if (search) {
                    search.addEventListener('input', function () {
                        var term = search.value.trim().toLowerCase();
                        list.querySelectorAll('label').forEach(function (label) {
                            var haystack = label.dataset.label || '';
                            label.style.display = !term || haystack.includes(term) ? '' : 'none';
                        });
                    });
                }

                list.addEventListener('change', updateMeta);
                updateMeta();
            }

            bindChecklist('new-package');
            document.querySelectorAll('[data-checklist]').forEach(function (list) {
                var name = list.getAttribute('data-checklist');
                if (name && name !== 'new-package') {
                    bindChecklist(name);
                }
            });
        })();
    </script>
@endsection

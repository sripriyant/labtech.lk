@extends('layouts.admin')

@php
    $pageTitle = 'Lab Stock';
@endphp

@section('content')
    <style>
        .stock-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .stock-card {
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

        .field input,
        .field textarea,
        .field select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            font-size: 12px;
            font-family: inherit;
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
            vertical-align: top;
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

        .badge {
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            background: #f1f5f8;
            color: var(--muted);
        }

        .section-title {
            margin: 0 0 10px;
            font-size: 14px;
        }

        @media (max-width: 1100px) {
            .stock-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="stock-grid">
        <div class="stock-card">
            <h3 class="section-title">Add Stock Item</h3>
            <form method="post" action="{{ route('admin.stock.items.store') }}">
                @csrf
                <div class="field">
                    <label>Item Code</label>
                    <input name="code" type="text" required>
                </div>
                <div class="field">
                    <label>Name</label>
                    <input name="name" type="text" required>
                </div>
                <div class="field">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="field">
                    <label>Reorder Level</label>
                    <input name="reorder_level" type="number" min="0" value="0">
                </div>
                <div class="field">
                    <label>Reorder Qty</label>
                    <input name="reorder_qty" type="number" min="0" value="0">
                </div>
                <div class="field">
                    <label>Stock Unit</label>
                    <input name="unit" type="text" placeholder="Unit">
                </div>
                <div class="field">
                    <label>Unit Price</label>
                    <input name="unit_price" type="number" min="0" step="0.01" value="0.00">
                </div>
                <div class="field">
                    <label><input type="checkbox" name="is_active" value="1" checked> Active</label>
                </div>
                <button class="btn" type="submit">Save Item</button>
            </form>
        </div>

        <div class="stock-card">
            <h3 class="section-title">Add Stock Batch</h3>
            <form method="post" action="{{ route('admin.stock.batches.store') }}">
                @csrf
                <div class="field">
                    <label>Item</label>
                    <select name="lab_stock_item_id" required>
                        <option value="">Select item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Supplier</label>
                    <select name="supplier_id">
                        <option value="">Select supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Quantity</label>
                    <input name="quantity" type="number" min="1" required>
                </div>
                <div class="field">
                    <label>Purchase Date</label>
                    <input name="purchase_date" type="date">
                </div>
                <div class="field">
                    <label>Expiry Date</label>
                    <input name="expiry_date" type="date">
                </div>
                <div class="field">
                    <label>Unit Cost</label>
                    <input name="unit_cost" type="number" min="0" step="0.01">
                </div>
                <div class="field">
                    <label>Notes</label>
                    <input name="notes" type="text">
                </div>
                <button class="btn" type="submit">Add Batch</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h3 class="section-title">Consumption Mapping</h3>
        <form method="post" action="{{ route('admin.stock.consumption.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;align-items:end;">
                <div class="field" style="margin:0;">
                    <label>Test</label>
                    <select name="test_master_id" required>
                        <option value="">Select test</option>
                        @foreach ($tests as $test)
                            <option value="{{ $test->id }}">{{ $test->code }} - {{ $test->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="margin:0;">
                    <label>Lab Stock Item</label>
                    <select name="lab_stock_item_id" required>
                        <option value="">Select item</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="margin:0;">
                    <label>Quantity per Test</label>
                    <input name="quantity_per_test" type="number" min="0.01" step="0.01" required>
                </div>
            </div>
            <button class="btn" type="submit" style="margin-top:10px;">Save Mapping</button>
        </form>

        <div class="table-wrap" style="margin-top:12px;">
            <table>
                <thead>
                    <tr>
                        <th>Test</th>
                        <th>Item</th>
                        <th>Qty/Test</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($consumptions as $row)
                        <tr>
                            <td>{{ $row->test?->name ?? '-' }}</td>
                            <td>{{ $row->item?->name ?? '-' }}</td>
                            <td>{{ $row->quantity_per_test }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">No consumption rules yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h3 class="section-title">Stock Items</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Supplier</th>
                        <th>Reorder Lev.</th>
                        <th>Re. Qty</th>
                        <th>Stock</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $totalQty = $item->batches->sum('remaining_qty');
                            $totalValue = $item->batches->sum(fn ($b) => $b->remaining_qty * (float) $b->unit_cost);
                            $supplierName = $item->batches
                                ->sortByDesc(fn ($batch) => $batch->purchase_date ?? $batch->created_at)
                                ->first()?->supplier?->company_name;
                        @endphp
                        <tr>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->description }}</td>
                            <td>{{ $supplierName ?? '-' }}</td>
                            <td>{{ $item->reorder_level }}</td>
                            <td>{{ $item->reorder_qty }}</td>
                            <td>
                                {{ $totalQty }}
                                @if ($totalQty <= $item->reorder_level)
                                    <span class="badge">Low</span>
                                @endif
                            </td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ number_format($totalValue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">No items added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <h3 class="section-title">Stock Batches</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Supplier</th>
                        <th>Qty</th>
                        <th>Remaining</th>
                        <th>Purchase Date</th>
                        <th>Expiry Date</th>
                        <th>Unit Cost</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @foreach ($item->batches as $batch)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $batch->supplier?->company_name ?? '-' }}</td>
                                <td>{{ $batch->quantity }}</td>
                                <td>{{ $batch->remaining_qty }}</td>
                                <td>{{ optional($batch->purchase_date)->format('Y-m-d') }}</td>
                                <td>{{ optional($batch->expiry_date)->format('Y-m-d') }}</td>
                                <td>{{ number_format($batch->unit_cost, 2) }}</td>
                                <td>{{ number_format($batch->remaining_qty * (float) $batch->unit_cost, 2) }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="8">No batches added.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

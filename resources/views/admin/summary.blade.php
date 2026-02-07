@extends('layouts.admin')

@php
    $pageTitle = 'Summary';
    $activeTab = $tab ?? 'tests';
@endphp

@section('content')
    <style>
        .summary-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .summary-tabs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .summary-tabs button {
            border: 1px solid var(--line);
            background: #fff;
            color: #334155;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .summary-tabs button.active {
            background: #0a6fb3;
            color: #fff;
            border-color: #0a6fb3;
        }

        .filter-form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-form label {
            font-size: 12px;
            color: var(--muted);
        }

        .filter-form input {
            margin-left: 6px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .summary-card {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            background: #f7fafb;
        }

        .summary-card strong {
            display: block;
            font-size: 20px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .summary-table th,
        .summary-table td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }

        .summary-table th {
            background: #f0f4f7;
            color: var(--muted);
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }
    </style>

    <div class="card">
        <div class="summary-header">
            <h2 style="margin:0;">Performance Summary</h2>
            <div class="summary-tabs">
                <button type="button" class="tab-btn {{ $activeTab === 'tests' ? 'active' : '' }}" data-tab="tests">Test Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'accounts' ? 'active' : '' }}" data-tab="accounts">Accounts Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'income' ? 'active' : '' }}" data-tab="income">Income Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'refund' ? 'active' : '' }}" data-tab="refund">Refund Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'cost' ? 'active' : '' }}" data-tab="cost">Cost Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'consumption' ? 'active' : '' }}" data-tab="consumption">Consumption Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'reorder' ? 'active' : '' }}" data-tab="reorder">Reorder Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'centre' ? 'active' : '' }}" data-tab="centre">Centre Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'department' ? 'active' : '' }}" data-tab="department">Department Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'doctor' ? 'active' : '' }}" data-tab="doctor">Doctor Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'supplier' ? 'active' : '' }}" data-tab="supplier">Supplier Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'high' ? 'active' : '' }}" data-tab="high">High Result Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'low' ? 'active' : '' }}" data-tab="low">Low Result Summary</button>
                <button type="button" class="tab-btn {{ $activeTab === 'suggest' ? 'active' : '' }}" data-tab="suggest">Suggest / Note Summary</button>
            </div>
            <form method="get" action="{{ url('/admin/page/summary') }}" class="filter-form">
                <input type="hidden" name="tab" id="tab_input" value="{{ $activeTab }}">
                <label>From
                    <input name="from" type="date" value="{{ $filters['from'] ?? '' }}">
                </label>
                <label>Start Time
                    <input name="from_time" type="time" value="{{ $filters['from_time'] ?? '' }}">
                </label>
                <label>To
                    <input name="to" type="date" value="{{ $filters['to'] ?? '' }}">
                </label>
                <label>End Time
                    <input name="to_time" type="time" value="{{ $filters['to_time'] ?? '' }}">
                </label>
                <label>Sort
                    <select name="sort">
                        @php $sort = $filters['sort'] ?? 'total_desc'; @endphp
                        <option value="total_desc" {{ $sort === 'total_desc' ? 'selected' : '' }}>Total ↓</option>
                        <option value="total_asc" {{ $sort === 'total_asc' ? 'selected' : '' }}>Total ↑</option>
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name A-Z</option>
                        <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name Z-A</option>
                    </select>
                </label>
                <button class="btn" type="submit">Filter</button>
                <a class="btn secondary" href="{{ url('/admin/page/summary') }}">Reset</a>
                <a class="btn secondary" href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}">Export CSV</a>
                <a class="btn secondary" href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}">Export Excel</a>
                <a class="btn secondary" href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}">Export PDF</a>
            </form>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'tests' ? 'active' : '' }}" data-tab-pane="tests">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Test Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Total Tests (Today)</div>
                    <strong>{{ $testSummary['total_today'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Total Tests (Month)</div>
                    <strong>{{ $testSummary['total_month'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Total Tests (Filtered)</div>
                    <strong>{{ $testSummary['total_period'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Pending Tests</div>
                    <strong>{{ $testSummary['pending'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Completed Tests</div>
                    <strong>{{ $testSummary['completed'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Rejected Samples</div>
                    <strong>{{ $testSummary['rejected'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Average TAT (min)</div>
                    <strong>{{ $testSummary['avg_tat_minutes'] ? number_format($testSummary['avg_tat_minutes'], 1) : '--' }}</strong>
                </div>
            </div>
        </div>

        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Tests by Department</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Department</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($testSummary['by_department'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Test-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Test</th><th>Total</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($testSummary['test_wise'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td><td>{{ number_format($row->revenue ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Package-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Package</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($testSummary['package_wise'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Centre-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Centre</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($testSummary['centre_wise'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Doctor-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Doctor</th><th>Total</th><th>Revenue</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($testSummary['doctor_wise'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td><td>{{ number_format($row->revenue ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'accounts' ? 'active' : '' }}" data-tab-pane="accounts">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Accounts Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Total Invoiced</div>
                    <strong>{{ number_format($accountsSummary['total_invoiced'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Total Collected</div>
                    <strong>{{ number_format($accountsSummary['total_collected'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Outstanding Balance</div>
                    <strong>{{ number_format($accountsSummary['outstanding'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Credit Amount</div>
                    <strong>{{ number_format($accountsSummary['credit'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Refund Issued</div>
                    <strong>{{ number_format($accountsSummary['refund'] ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Cash / Card / Online</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Method</th><th>Count</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($accountsSummary['payments_by_method'] ?? [] as $row)
                            <tr><td>{{ $row->method }}</td><td>{{ $row->count }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Centre-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Centre</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($accountsSummary['invoice_by_centre'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Date-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Date</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($accountsSummary['invoice_by_date'] ?? [] as $row)
                            <tr><td>{{ $row->day }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>User-wise (Cashier)</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>User</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($accountsSummary['payments_by_user'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'income' ? 'active' : '' }}" data-tab-pane="income">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Income Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Gross Income</div>
                    <strong>{{ number_format($incomeSummary['gross_income'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Net Income</div>
                    <strong>{{ number_format($incomeSummary['net_income'] ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Top Tests</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Test</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($incomeSummary['income_by_test'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>By Department</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Department</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($incomeSummary['income_by_department'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>By Doctor</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Doctor</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($incomeSummary['income_by_doctor'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>By Centre</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Centre</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($incomeSummary['income_by_centre'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Date-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Date</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($incomeSummary['income_by_period'] ?? [] as $row)
                            <tr><td>{{ $row->day }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'refund' ? 'active' : '' }}" data-tab-pane="refund">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Refund Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Total Refund Count</div>
                    <strong>{{ $refundSummary['total_count'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Total Refund Amount</div>
                    <strong>{{ number_format($refundSummary['total_amount'] ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Refund Reason Breakdown</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Reason</th><th>Count</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($refundSummary['by_reason'] ?? [] as $row)
                            <tr><td>{{ $row->reason ?? 'N/A' }}</td><td>{{ $row->total }}</td><td>{{ number_format($row->amount ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Patient-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Patient</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($refundSummary['by_patient'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Test-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Test</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($refundSummary['by_test'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Date-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Date</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($refundSummary['by_date'] ?? [] as $row)
                            <tr><td>{{ $row->day }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Approved by</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>User</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($refundSummary['by_approver'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'cost' ? 'active' : '' }}" data-tab-pane="cost">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Cost Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Total Operational Cost</div>
                    <strong>{{ number_format($costSummary['total_operational'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Cost per Test</div>
                    <strong>{{ number_format($costSummary['cost_per_test'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Reagent Cost</div>
                    <strong>{{ number_format($costSummary['reagent_cost'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Staff Cost</div>
                    <strong>{{ number_format($costSummary['staff_cost'] ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Department Cost</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Department</th><th>Cost</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($costSummary['department_costs'] ?? [] as $row)
                            <tr><td>{{ $row->dept_name ?? $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Test-wise Margin</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Test</th><th>Revenue</th><th>Cost</th><th>Margin</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($costSummary['test_margins'] ?? [] as $row)
                            <tr>
                                <td>{{ $row->name }}</td>
                                <td>{{ number_format($row->revenue ?? 0, 2) }}</td>
                                <td>{{ number_format($row->cost ?? 0, 2) }}</td>
                                <td>{{ number_format($row->margin ?? 0, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'consumption' ? 'active' : '' }}" data-tab-pane="consumption">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Consumption Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Reagent Consumption (Today)</div>
                    <strong>{{ number_format($consumptionSummary['consumed_today'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Reagent Consumption (Month)</div>
                    <strong>{{ number_format($consumptionSummary['consumed_month'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Wastage %</div>
                    <strong>{{ number_format($consumptionSummary['wastage_percent'] ?? 0, 2) }}%</strong>
                </div>
                <div class="summary-card">
                    <div>Expired Items</div>
                    <strong>{{ count($consumptionSummary['expired_items'] ?? []) }}</strong>
                </div>
            </div>
        </div>

        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Item Usage</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Item</th><th>Quantity</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($consumptionSummary['item_usage'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Test vs Reagent Usage</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Test</th><th>Item</th><th>Quantity</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($consumptionSummary['test_vs_reagent'] ?? [] as $row)
                            <tr><td>{{ $row->test_name }}</td><td>{{ $row->item_name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Expired Items</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Item</th><th>Expiry Date</th><th>Remaining Qty</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($consumptionSummary['expired_items'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->expiry_date }}</td><td>{{ $row->remaining_qty }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'reorder' ? 'active' : '' }}" data-tab-pane="reorder">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Reorder Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Low Stock Items</div>
                    <strong>{{ count($reorderSummary['low_stock'] ?? []) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Out of Stock Items</div>
                    <strong>{{ count($reorderSummary['out_of_stock'] ?? []) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Reorder Alerts</div>
                    <strong>{{ $reorderSummary['alerts'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Pending Purchase Orders</div>
                    <strong>{{ $reorderSummary['pending_orders'] ?? 0 }}</strong>
                </div>
            </div>
        </div>

        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Low Stock Items</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Item</th><th>Available</th><th>Reorder Level</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($reorderSummary['low_stock'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total_qty }}</td><td>{{ $row->reorder_level }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Out of Stock Items</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Item</th><th>Available</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($reorderSummary['out_of_stock'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total_qty }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Supplier-wise</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Supplier</th><th>Item</th><th>Remaining</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($reorderSummary['supplier_wise'] ?? [] as $row)
                            <tr><td>{{ $row->supplier }}</td><td>{{ $row->item_name }}</td><td>{{ $row->total_qty }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'centre' ? 'active' : '' }}" data-tab-pane="centre">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Centre Summary</h3>
        </div>
        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Tests per Centre</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Centre</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($centreSummary['tests_per_centre'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Income per Centre</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Centre</th><th>Income</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($centreSummary['income_per_centre'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Pending Samples</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Centre</th><th>Pending</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($centreSummary['pending_samples'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Rejection Rate</h4>
                <table class="summary-table">
                    <thead>
                        <tr><th>Centre</th><th>Rejected</th><th>Total</th><th>Rate</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($centreSummary['rejection_rate'] ?? [] as $row)
                            @php $rate = $row->total > 0 ? round(($row->rejected / $row->total) * 100, 2) : 0; @endphp
                            <tr><td>{{ $row->name }}</td><td>{{ $row->rejected }}</td><td>{{ $row->total }}</td><td>{{ $rate }}%</td></tr>
                        @empty
                            <tr><td colspan="4">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'department' ? 'active' : '' }}" data-tab-pane="department">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Department Summary</h3>
        </div>
        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Tests Performed</h4>
                <table class="summary-table">
                    <thead><tr><th>Department</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($departmentSummary['tests_performed'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Pending / Delayed</h4>
                <table class="summary-table">
                    <thead><tr><th>Department</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($departmentSummary['pending'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>High Results</h4>
                <table class="summary-table">
                    <thead><tr><th>Department</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($departmentSummary['high_flags'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Low Results</h4>
                <table class="summary-table">
                    <thead><tr><th>Department</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($departmentSummary['low_flags'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Department Revenue</h4>
                <table class="summary-table">
                    <thead><tr><th>Department</th><th>Revenue</th></tr></thead>
                    <tbody>
                        @forelse ($departmentSummary['revenue'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'doctor' ? 'active' : '' }}" data-tab-pane="doctor">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Doctor Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Total Referrals</div>
                    <strong>{{ $doctorSummary['total_referrals'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Revenue Generated</div>
                    <strong>{{ number_format($doctorSummary['revenue'] ?? 0, 2) }}</strong>
                </div>
            </div>
        </div>
        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Top Tests Ordered</h4>
                <table class="summary-table">
                    <thead><tr><th>Test</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($doctorSummary['top_tests'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Report Delay (Avg Minutes)</h4>
                <table class="summary-table">
                    <thead><tr><th>Doctor</th><th>Avg Minutes</th></tr></thead>
                    <tbody>
                        @forelse ($doctorSummary['report_delay'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->avg_minutes ?? 0, 1) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'supplier' ? 'active' : '' }}" data-tab-pane="supplier">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Supplier Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Active Suppliers</div>
                    <strong>{{ $supplierSummary['active_suppliers'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Monthly Purchase Value</div>
                    <strong>{{ number_format($supplierSummary['monthly_purchase'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Outstanding Payables</div>
                    <strong>{{ number_format($supplierSummary['outstanding'] ?? 0, 2) }}</strong>
                </div>
                <div class="summary-card">
                    <div>Delivery Delays</div>
                    <strong>{{ $supplierSummary['delivery_delays'] ?? 0 }}</strong>
                </div>
            </div>
        </div>
        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Supplier Performance</h4>
                <table class="summary-table">
                    <thead><tr><th>Supplier</th><th>Total Purchase</th></tr></thead>
                    <tbody>
                        @forelse ($supplierSummary['supplier_performance'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ number_format($row->total ?? 0, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'high' ? 'active' : '' }}" data-tab-pane="high">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">High Result Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Total High Results</div>
                    <strong>{{ $highSummary['total_high'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Critical High Results</div>
                    <strong>{{ $highSummary['critical_high'] ?? 0 }}</strong>
                </div>
            </div>
        </div>
        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Tests with Frequent High Values</h4>
                <table class="summary-table">
                    <thead><tr><th>Test</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($highSummary['tests'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Patient-wise</h4>
                <table class="summary-table">
                    <thead><tr><th>Patient</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($highSummary['patients'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'low' ? 'active' : '' }}" data-tab-pane="low">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Low Result Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Total Low Results</div>
                    <strong>{{ $lowSummary['total_low'] ?? 0 }}</strong>
                </div>
            </div>
        </div>
        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Tests with Frequent Low Values</h4>
                <table class="summary-table">
                    <thead><tr><th>Test</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($lowSummary['tests'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card">
                <h4>Patient-wise</h4>
                <table class="summary-table">
                    <thead><tr><th>Patient</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse ($lowSummary['patients'] ?? [] as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
                        @empty
                            <tr><td colspan="2">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="tab-pane {{ $activeTab === 'suggest' ? 'active' : '' }}" data-tab-pane="suggest">
        <div class="card" style="margin-top:16px;">
            <h3 style="margin:0 0 10px;">Suggest / Note Summary</h3>
            <div class="summary-cards">
                <div class="summary-card">
                    <div>Auto Suggestions Triggered</div>
                    <strong>{{ $suggestSummary['auto_suggestions'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Manual Doctor Notes</div>
                    <strong>{{ $suggestSummary['doctor_notes'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Follow-up Recommended</div>
                    <strong>{{ $suggestSummary['follow_up'] ?? 0 }}</strong>
                </div>
                <div class="summary-card">
                    <div>Repeat Test Suggested</div>
                    <strong>{{ $suggestSummary['repeat_test'] ?? 0 }}</strong>
                </div>
            </div>
        </div>
        <div class="summary-grid" style="margin-top:16px;">
            <div class="card">
                <h4>Latest Notes</h4>
                <table class="summary-table">
                    <thead><tr><th>Test</th><th>Note</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse ($suggestSummary['examples'] ?? [] as $row)
                            <tr><td>{{ $row->name ?? 'N/A' }}</td><td>{{ $row->comment }}</td><td>{{ $row->approved_at }}</td></tr>
                        @empty
                            <tr><td colspan="3">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var tabButtons = document.querySelectorAll('.tab-btn');
            var tabInput = document.getElementById('tab_input');
            tabButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var tab = btn.dataset.tab;
                    var url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.location.href = url.toString();
                });
            });
            if (tabInput) {
                tabInput.value = '{{ $activeTab }}';
            }
        })();
    </script>
@endsection

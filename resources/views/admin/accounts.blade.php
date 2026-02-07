@extends('layouts.admin')

@php
    $pageTitle = 'Accounts';
    $currency = 'LKR';
    $invoiceCounts = $invoiceCounts ?? collect();
    $paymentMethods = $paymentMethods ?? collect();
    $recentInvoices = $recentInvoices ?? collect();
    $revenueTrend = $revenueTrend ?? collect();
    $topRevenueTests = $topRevenueTests ?? collect();
@endphp

@section('content')
    <style>
        .accounts-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }

        .accounts-card {
            background: #fff;
            border-radius: 16px;
            border: 1px solid var(--line);
            padding: 18px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
        }

        .accounts-card h3 {
            margin: 0 0 6px;
            font-size: 14px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .accounts-card .value {
            font-size: 26px;
            font-weight: 800;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .split-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }

        .table-wrap {
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead th {
            background: #f0f4f7;
            color: var(--muted);
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid var(--line);
        }

        tbody td {
            padding: 8px;
            border-bottom: 1px solid var(--line);
        }

        .trend {
            display: grid;
            gap: 10px;
        }

        .trend-row {
            display: grid;
            grid-template-columns: 70px 1fr 90px;
            gap: 10px;
            align-items: center;
            font-size: 12px;
        }

        .trend-bar {
            height: 10px;
            border-radius: 999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .trend-bar span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #0ea5e9, #10b981);
        }
    </style>

    <div class="accounts-grid">
        <div class="accounts-card">
            <h3>Today Revenue</h3>
            <div class="value">{{ $currency }} {{ number_format($todayRevenue ?? 0, 2) }}</div>
        </div>
        <div class="accounts-card">
            <h3>Monthly Revenue</h3>
            <div class="value">{{ $currency }} {{ number_format($monthlyRevenue ?? 0, 2) }}</div>
        </div>
        <div class="accounts-card">
            <h3>Pending Payments</h3>
            <div class="value">{{ number_format($pendingPayments ?? 0) }}</div>
        </div>
    </div>

    <div class="split-grid" style="margin-top:18px;">
        <div class="accounts-card">
            <div class="section-title">Revenue Trend</div>
            @php
                $trendMax = max(1, $revenueTrend->max('total') ?? 1);
            @endphp
            <div class="trend">
                @forelse ($revenueTrend as $row)
                    @php
                        $pct = ($row['total'] ?? 0) / $trendMax * 100;
                    @endphp
                    <div class="trend-row">
                        <div>{{ $row['label'] ?? '' }}</div>
                        <div class="trend-bar"><span style="width: {{ number_format($pct, 2, '.', '') }}%"></span></div>
                        <div style="text-align:right;">{{ $currency }} {{ number_format($row['total'] ?? 0, 2) }}</div>
                    </div>
                @empty
                    <div style="font-size:12px;color:var(--muted);">No revenue data yet.</div>
                @endforelse
            </div>
        </div>

        <div class="accounts-card">
            <div class="section-title">Payment Status</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoiceCounts as $row)
                            <tr>
                                <td>{{ $row->payment_status ?? '-' }}</td>
                                <td>{{ number_format((int) ($row->total ?? 0)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No invoices yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="section-title" style="margin-top:16px;">Payment Methods</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($paymentMethods as $row)
                            <tr>
                                <td>{{ $row->payment_mode ?? '-' }}</td>
                                <td>{{ number_format((int) ($row->total ?? 0)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No payment methods yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="split-grid" style="margin-top:18px;">
        <div class="accounts-card">
            <div class="section-title">Top Revenue Tests</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Test</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topRevenueTests as $row)
                            <tr>
                                <td>{{ $row->name ?? '-' }}</td>
                                <td>{{ $currency }} {{ number_format((float) ($row->total ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No revenue tests yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="accounts-card">
            <div class="section-title">Recent Invoices</div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>Net Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentInvoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_no ?? '-' }}</td>
                                <td>{{ optional($invoice->created_at)->format('Y-m-d') ?? '-' }}</td>
                                <td>{{ $currency }} {{ number_format((float) ($invoice->net_total ?? 0), 2) }}</td>
                                <td>{{ $invoice->payment_status ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">No invoices yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Summary Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111827;
            margin: 24px;
        }
        h1, h2 {
            margin: 0 0 8px;
        }
        h2 {
            margin-top: 18px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
    <h1>Summary Export</h1>
    <div>Lab: {{ $labName ?? 'All Labs' }}</div>
    <div>Tab: {{ ucfirst($tab ?? 'tests') }}</div>
    <div>Date: {{ now()->format('Y-m-d H:i') }}</div>

    @if (($tab ?? 'tests') === 'tests')
        <h2>Test Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Total Tests (Today)</td><td>{{ $testSummary['total_today'] ?? 0 }}</td></tr>
            <tr><td>Total Tests (Month)</td><td>{{ $testSummary['total_month'] ?? 0 }}</td></tr>
            <tr><td>Total Tests (Filtered)</td><td>{{ $testSummary['total_period'] ?? 0 }}</td></tr>
            <tr><td>Pending Tests</td><td>{{ $testSummary['pending'] ?? 0 }}</td></tr>
            <tr><td>Completed Tests</td><td>{{ $testSummary['completed'] ?? 0 }}</td></tr>
            <tr><td>Rejected Samples</td><td>{{ $testSummary['rejected'] ?? 0 }}</td></tr>
            <tr><td>Average TAT (min)</td><td>{{ $testSummary['avg_tat_minutes'] ?? '' }}</td></tr>
        </table>

        <h2>Tests by Department</h2>
        <table>
            <tr><th>Department</th><th>Total</th></tr>
            @forelse ($testSummary['by_department'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Test-wise</h2>
        <table>
            <tr><th>Test</th><th>Total</th><th>Revenue</th></tr>
            @forelse ($testSummary['test_wise'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td><td>{{ $row->revenue ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>

        <h2>Package-wise</h2>
        <table>
            <tr><th>Package</th><th>Total</th></tr>
            @forelse ($testSummary['package_wise'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Centre-wise</h2>
        <table>
            <tr><th>Centre</th><th>Total</th></tr>
            @forelse ($testSummary['centre_wise'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Doctor-wise</h2>
        <table>
            <tr><th>Doctor</th><th>Total</th><th>Revenue</th></tr>
            @forelse ($testSummary['doctor_wise'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td><td>{{ $row->revenue ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'accounts')
        <h2>Accounts Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Total Invoiced</td><td>{{ $accountsSummary['total_invoiced'] ?? 0 }}</td></tr>
            <tr><td>Total Collected</td><td>{{ $accountsSummary['total_collected'] ?? 0 }}</td></tr>
            <tr><td>Outstanding Balance</td><td>{{ $accountsSummary['outstanding'] ?? 0 }}</td></tr>
            <tr><td>Credit Amount</td><td>{{ $accountsSummary['credit'] ?? 0 }}</td></tr>
            <tr><td>Refund Issued</td><td>{{ $accountsSummary['refund'] ?? 0 }}</td></tr>
        </table>

        <h2>Payments by Method</h2>
        <table>
            <tr><th>Method</th><th>Count</th><th>Total</th></tr>
            @forelse ($accountsSummary['payments_by_method'] ?? [] as $row)
                <tr><td>{{ $row->method }}</td><td>{{ $row->count }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>

        <h2>Invoice by Centre</h2>
        <table>
            <tr><th>Centre</th><th>Total</th></tr>
            @forelse ($accountsSummary['invoice_by_centre'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Invoice by Date</h2>
        <table>
            <tr><th>Date</th><th>Total</th></tr>
            @forelse ($accountsSummary['invoice_by_date'] ?? [] as $row)
                <tr><td>{{ $row->day }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Payments by User</h2>
        <table>
            <tr><th>User</th><th>Total</th></tr>
            @forelse ($accountsSummary['payments_by_user'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'income')
        <h2>Income Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Gross Income</td><td>{{ $incomeSummary['gross_income'] ?? 0 }}</td></tr>
            <tr><td>Net Income</td><td>{{ $incomeSummary['net_income'] ?? 0 }}</td></tr>
        </table>

        <h2>Income by Test</h2>
        <table>
            <tr><th>Test</th><th>Total</th></tr>
            @forelse ($incomeSummary['income_by_test'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Income by Department</h2>
        <table>
            <tr><th>Department</th><th>Total</th></tr>
            @forelse ($incomeSummary['income_by_department'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Income by Doctor</h2>
        <table>
            <tr><th>Doctor</th><th>Total</th></tr>
            @forelse ($incomeSummary['income_by_doctor'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Income by Centre</h2>
        <table>
            <tr><th>Centre</th><th>Total</th></tr>
            @forelse ($incomeSummary['income_by_centre'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Income by Date</h2>
        <table>
            <tr><th>Date</th><th>Total</th></tr>
            @forelse ($incomeSummary['income_by_period'] ?? [] as $row)
                <tr><td>{{ $row->day }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'refund')
        <h2>Refund Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Total Refund Count</td><td>{{ $refundSummary['total_count'] ?? 0 }}</td></tr>
            <tr><td>Total Refund Amount</td><td>{{ $refundSummary['total_amount'] ?? 0 }}</td></tr>
        </table>

        <h2>Refund Reason Breakdown</h2>
        <table>
            <tr><th>Reason</th><th>Count</th><th>Amount</th></tr>
            @forelse ($refundSummary['by_reason'] ?? [] as $row)
                <tr><td>{{ $row->reason ?? 'N/A' }}</td><td>{{ $row->total }}</td><td>{{ $row->amount ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>

        <h2>Patient-wise</h2>
        <table>
            <tr><th>Patient</th><th>Amount</th></tr>
            @forelse ($refundSummary['by_patient'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Test-wise</h2>
        <table>
            <tr><th>Test</th><th>Amount</th></tr>
            @forelse ($refundSummary['by_test'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Date-wise</h2>
        <table>
            <tr><th>Date</th><th>Amount</th></tr>
            @forelse ($refundSummary['by_date'] ?? [] as $row)
                <tr><td>{{ $row->day }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Approved by</h2>
        <table>
            <tr><th>User</th><th>Amount</th></tr>
            @forelse ($refundSummary['by_approver'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'cost')
        <h2>Cost Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Total Operational Cost</td><td>{{ $costSummary['total_operational'] ?? 0 }}</td></tr>
            <tr><td>Cost per Test</td><td>{{ $costSummary['cost_per_test'] ?? 0 }}</td></tr>
            <tr><td>Reagent Cost</td><td>{{ $costSummary['reagent_cost'] ?? 0 }}</td></tr>
            <tr><td>Staff Cost</td><td>{{ $costSummary['staff_cost'] ?? 0 }}</td></tr>
        </table>

        <h2>Department Cost</h2>
        <table>
            <tr><th>Department</th><th>Cost</th></tr>
            @forelse ($costSummary['department_costs'] ?? [] as $row)
                <tr><td>{{ $row->dept_name ?? $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Test-wise Margin</h2>
        <table>
            <tr><th>Test</th><th>Revenue</th><th>Cost</th><th>Margin</th></tr>
            @forelse ($costSummary['test_margins'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->revenue ?? 0 }}</td><td>{{ $row->cost ?? 0 }}</td><td>{{ $row->margin ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="4">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'consumption')
        <h2>Consumption Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Reagent Consumption (Today)</td><td>{{ $consumptionSummary['consumed_today'] ?? 0 }}</td></tr>
            <tr><td>Reagent Consumption (Month)</td><td>{{ $consumptionSummary['consumed_month'] ?? 0 }}</td></tr>
            <tr><td>Wastage %</td><td>{{ $consumptionSummary['wastage_percent'] ?? 0 }}</td></tr>
        </table>

        <h2>Item Usage</h2>
        <table>
            <tr><th>Item</th><th>Quantity</th></tr>
            @forelse ($consumptionSummary['item_usage'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Test vs Reagent Usage</h2>
        <table>
            <tr><th>Test</th><th>Item</th><th>Quantity</th></tr>
            @forelse ($consumptionSummary['test_vs_reagent'] ?? [] as $row)
                <tr><td>{{ $row->test_name }}</td><td>{{ $row->item_name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>

        <h2>Expired Items</h2>
        <table>
            <tr><th>Item</th><th>Expiry Date</th><th>Remaining Qty</th></tr>
            @forelse ($consumptionSummary['expired_items'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->expiry_date }}</td><td>{{ $row->remaining_qty }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'reorder')
        <h2>Reorder Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Low Stock Items</td><td>{{ count($reorderSummary['low_stock'] ?? []) }}</td></tr>
            <tr><td>Out of Stock Items</td><td>{{ count($reorderSummary['out_of_stock'] ?? []) }}</td></tr>
            <tr><td>Reorder Alerts</td><td>{{ $reorderSummary['alerts'] ?? 0 }}</td></tr>
            <tr><td>Pending Purchase Orders</td><td>{{ $reorderSummary['pending_orders'] ?? 0 }}</td></tr>
        </table>

        <h2>Low Stock Items</h2>
        <table>
            <tr><th>Item</th><th>Available</th><th>Reorder Level</th></tr>
            @forelse ($reorderSummary['low_stock'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total_qty }}</td><td>{{ $row->reorder_level }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>

        <h2>Out of Stock Items</h2>
        <table>
            <tr><th>Item</th><th>Available</th></tr>
            @forelse ($reorderSummary['out_of_stock'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total_qty }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Supplier-wise</h2>
        <table>
            <tr><th>Supplier</th><th>Item</th><th>Remaining</th></tr>
            @forelse ($reorderSummary['supplier_wise'] ?? [] as $row)
                <tr><td>{{ $row->supplier }}</td><td>{{ $row->item_name }}</td><td>{{ $row->total_qty }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'centre')
        <h2>Centre Summary</h2>
        <table>
            <tr><th>Centre</th><th>Total Tests</th></tr>
            @forelse ($centreSummary['tests_per_centre'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Income per Centre</h2>
        <table>
            <tr><th>Centre</th><th>Total</th></tr>
            @forelse ($centreSummary['income_per_centre'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Pending Samples</h2>
        <table>
            <tr><th>Centre</th><th>Total</th></tr>
            @forelse ($centreSummary['pending_samples'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Rejection Rate</h2>
        <table>
            <tr><th>Centre</th><th>Rejected</th><th>Total</th><th>Rate</th></tr>
            @forelse ($centreSummary['rejection_rate'] ?? [] as $row)
                @php $rate = $row->total > 0 ? round(($row->rejected / $row->total) * 100, 2) : 0; @endphp
                <tr><td>{{ $row->name }}</td><td>{{ $row->rejected }}</td><td>{{ $row->total }}</td><td>{{ $rate }}%</td></tr>
            @empty
                <tr><td colspan="4">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'department')
        <h2>Department Summary</h2>
        <table>
            <tr><th>Department</th><th>Tests</th></tr>
            @forelse ($departmentSummary['tests_performed'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Pending / Delayed</h2>
        <table>
            <tr><th>Department</th><th>Total</th></tr>
            @forelse ($departmentSummary['pending'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>High Results</h2>
        <table>
            <tr><th>Department</th><th>Total</th></tr>
            @forelse ($departmentSummary['high_flags'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Low Results</h2>
        <table>
            <tr><th>Department</th><th>Total</th></tr>
            @forelse ($departmentSummary['low_flags'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Department Revenue</h2>
        <table>
            <tr><th>Department</th><th>Revenue</th></tr>
            @forelse ($departmentSummary['revenue'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'doctor')
        <h2>Doctor Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Total Referrals</td><td>{{ $doctorSummary['total_referrals'] ?? 0 }}</td></tr>
            <tr><td>Revenue Generated</td><td>{{ $doctorSummary['revenue'] ?? 0 }}</td></tr>
        </table>

        <h2>Top Tests Ordered</h2>
        <table>
            <tr><th>Test</th><th>Total</th></tr>
            @forelse ($doctorSummary['top_tests'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Report Delay (Avg Minutes)</h2>
        <table>
            <tr><th>Doctor</th><th>Avg Minutes</th></tr>
            @forelse ($doctorSummary['report_delay'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->avg_minutes ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'supplier')
        <h2>Supplier Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Active Suppliers</td><td>{{ $supplierSummary['active_suppliers'] ?? 0 }}</td></tr>
            <tr><td>Monthly Purchase Value</td><td>{{ $supplierSummary['monthly_purchase'] ?? 0 }}</td></tr>
            <tr><td>Outstanding Payables</td><td>{{ $supplierSummary['outstanding'] ?? 0 }}</td></tr>
            <tr><td>Delivery Delays</td><td>{{ $supplierSummary['delivery_delays'] ?? 0 }}</td></tr>
        </table>

        <h2>Supplier Performance</h2>
        <table>
            <tr><th>Supplier</th><th>Total Purchase</th></tr>
            @forelse ($supplierSummary['supplier_performance'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total ?? 0 }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'high')
        <h2>High Result Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Total High Results</td><td>{{ $highSummary['total_high'] ?? 0 }}</td></tr>
            <tr><td>Critical High Results</td><td>{{ $highSummary['critical_high'] ?? 0 }}</td></tr>
        </table>

        <h2>Tests with Frequent High Values</h2>
        <table>
            <tr><th>Test</th><th>Total</th></tr>
            @forelse ($highSummary['tests'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Patient-wise</h2>
        <table>
            <tr><th>Patient</th><th>Total</th></tr>
            @forelse ($highSummary['patients'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'low')
        <h2>Low Result Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Total Low Results</td><td>{{ $lowSummary['total_low'] ?? 0 }}</td></tr>
        </table>

        <h2>Tests with Frequent Low Values</h2>
        <table>
            <tr><th>Test</th><th>Total</th></tr>
            @forelse ($lowSummary['tests'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>

        <h2>Patient-wise</h2>
        <table>
            <tr><th>Patient</th><th>Total</th></tr>
            @forelse ($lowSummary['patients'] ?? [] as $row)
                <tr><td>{{ $row->name }}</td><td>{{ $row->total }}</td></tr>
            @empty
                <tr><td colspan="2">No data.</td></tr>
            @endforelse
        </table>
    @elseif ($tab === 'suggest')
        <h2>Suggest / Note Summary</h2>
        <table>
            <tr><th>Metric</th><th>Value</th></tr>
            <tr><td>Auto Suggestions Triggered</td><td>{{ $suggestSummary['auto_suggestions'] ?? 0 }}</td></tr>
            <tr><td>Manual Doctor Notes</td><td>{{ $suggestSummary['doctor_notes'] ?? 0 }}</td></tr>
            <tr><td>Follow-up Recommended</td><td>{{ $suggestSummary['follow_up'] ?? 0 }}</td></tr>
            <tr><td>Repeat Test Suggested</td><td>{{ $suggestSummary['repeat_test'] ?? 0 }}</td></tr>
        </table>

        <h2>Latest Notes</h2>
        <table>
            <tr><th>Test</th><th>Note</th><th>Date</th></tr>
            @forelse ($suggestSummary['examples'] ?? [] as $row)
                <tr><td>{{ $row->name ?? 'N/A' }}</td><td>{{ $row->comment }}</td><td>{{ $row->approved_at }}</td></tr>
            @empty
                <tr><td colspan="3">No data.</td></tr>
            @endforelse
        </table>
    @else
        <h2>Summary</h2>
        <p>No export template for this tab yet.</p>
    @endif
</body>
</html>

@extends('layouts.admin')

@php
    $pageTitle = 'Dashboard';
    $today = now()->toDateString();
    $monthStart = now()->startOfMonth()->toDateString();

    $kpiCards = [
        [
            'label' => '&#128101; Total Patients',
            'value' => $counts['patients'] ?? 0,
            'note' => 'All time',
            'tone' => 'green',
        ],
        [
            'label' => '&#128176; Monthly Revenue',
            'value' => number_format($metrics['monthly_revenue'] ?? 0, 2),
            'note' => now()->format('M Y'),
            'tone' => 'green',
        ],
        [
            'label' => '&#129514; Tests Completed (Today / Month)',
            'value' => ($metrics['tests_completed_today'] ?? 0) . ' / ' . ($metrics['tests_completed_month'] ?? 0),
            'note' => 'Approved',
            'tone' => 'green',
        ],
        [
            'label' => '&#9203; Pending Reports',
            'value' => $metrics['pending_reports'] ?? 0,
            'note' => 'Awaiting validation',
            'tone' => 'orange',
        ],
        [
            'label' => '&#10060; Rejected / Abnormal Results',
            'value' => ($metrics['rejected_results'] ?? 0) . ' / ' . ($metrics['abnormal_results'] ?? 0),
            'note' => 'Critical review',
            'tone' => 'red',
        ],
    ];

    $kpiBadgeMap = [
        'green' => 'OK',
        'orange' => 'PN',
        'red' => 'CR',
    ];

    $kpiTrendMap = [
        'green' => ['label' => 'Up', 'icon' => '&uarr;'],
        'orange' => ['label' => 'Pending', 'icon' => '&rarr;'],
        'red' => ['label' => 'Down', 'icon' => '&darr;'],
    ];

    $kpiBarMap = [
        'green' => 75,
        'orange' => 50,
        'red' => 30,
    ];

    $invoiceStatus = collect($billingStats['status_counts'] ?? []);
    $invoiceStatusMax = max(1, (int) ($invoiceStatus->max('total') ?? 0));

    $heatmapMax = max(1, max($testAnalytics['heatmap'] ?? [0]));

    $dailyCounts = collect($testAnalytics['daily_counts'] ?? []);
    $dailyMax = max(1, (int) ($dailyCounts->max('total') ?? 0));

    $weeklyCounts = collect($testAnalytics['weekly_counts'] ?? []);
    $weeklyMax = max(1, (int) ($weeklyCounts->max('total') ?? 0));

    $monthlyCounts = collect($testAnalytics['monthly_counts'] ?? []);
    $monthlyMax = max(1, (int) ($monthlyCounts->max('total') ?? 0));

    $billingTrend = $billingStats['monthly_trend'] ?? collect();
    if (!($billingTrend instanceof \Illuminate\Support\Collection)) {
        $billingTrend = collect($billingTrend);
    }
    $billingTrendMax = max(1, (float) ($billingTrend->max('total') ?? 0));

    $doctorReferrals = $advancedStats['doctor_referrals'] ?? collect();
    if (!($doctorReferrals instanceof \Illuminate\Support\Collection)) {
        $doctorReferrals = collect($doctorReferrals);
    }
    $doctorReferralMax = max(1, (float) ($doctorReferrals->max('total') ?? 0));

    $reagentConsumption = $advancedStats['reagent_consumption'] ?? collect();
    if (!($reagentConsumption instanceof \Illuminate\Support\Collection)) {
        $reagentConsumption = collect($reagentConsumption);
    }

    $multiBranch = $advancedStats['multi_branch'] ?? collect();
    if (!($multiBranch instanceof \Illuminate\Support\Collection)) {
        $multiBranch = collect($multiBranch);
    }
@endphp

@section('content')
    <style>
        .dash-layout {
            display: grid;
            gap: 20px;
        }

        .tab-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tab-btn {
            border: 1px solid #e2e6ea;
            background: #ffffff;
            padding: 8px 14px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 600;
            color: #4b5563;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            background: #0f6f73;
            border-color: #0a4f52;
            color: #ffffff;
        }

        .tab-panel {
            display: none;
            gap: 18px;
        }

        .tab-panel.active {
            display: grid;
        }

        .section-card {
            background: #ffffff;
            border: none;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title::before {
            content: "";
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            display: inline-block;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }

        .quick-actions a {
            text-decoration: none;
            color: inherit;
        }

        .action-card {
            padding: 14px;
            border-radius: 14px;
            border: none;
            background: #f8fafc;
            display: grid;
            gap: 6px;
            min-height: 90px;
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
            position: relative;
            overflow: hidden;
        }

        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(15, 23, 42, 0.08);
        }

        .action-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.6);
            color: #0f6f73;
        }

        .action-icon svg {
            width: 18px;
            height: 18px;
        }

        .action-title {
            font-weight: 700;
            color: #4b5563;
        }

        .quick-actions .action-card:nth-child(1) { background: linear-gradient(135deg, #00c6ff, #0072ff); color: #fff; }
        .quick-actions .action-card:nth-child(2) { background: linear-gradient(135deg, #6c5ce7, #8e44ad); color: #fff; }
        .quick-actions .action-card:nth-child(3) { background: linear-gradient(135deg, #00b894, #00cec9); color: #fff; }
        .quick-actions .action-card:nth-child(4) { background: linear-gradient(135deg, #f39c12, #f1c40f); color: #fff; }
        .quick-actions .action-card:nth-child(5) { background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; }
        .quick-actions .action-card:nth-child(6) { background: linear-gradient(135deg, #0984e3, #74b9ff); color: #fff; }

        .quick-actions .action-card .action-title { color: #fff; }

        .quick-actions .action-card:nth-child(1) .action-icon { color: #0072ff; }
        .quick-actions .action-card:nth-child(2) .action-icon { color: #8e44ad; }
        .quick-actions .action-card:nth-child(3) .action-icon { color: #00b894; }
        .quick-actions .action-card:nth-child(4) .action-icon { color: #f39c12; }
        .quick-actions .action-card:nth-child(5) .action-icon { color: #27ae60; }
        .quick-actions .action-card:nth-child(6) .action-icon { color: #0984e3; }

        .action-sub {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
        }

        .support-section {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .support-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 18px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .support-card h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
        }

        .support-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .support-table th,
        .support-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #eef2f7;
            border-right: 1px solid #eef2f7;
        }

        .support-table td:last-child,
        .support-table th:last-child {
            border-right: none;
        }

        .support-table tbody tr:last-child td {
            border-bottom: none;
        }

        .demo-form {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        }

        .demo-form input {
            border: 1px solid #d9e2ec;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
        }

        .demo-form button {
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #0f6f73, #0b5a77);
            color: #ffffff;
            font-weight: 700;
            padding: 10px 16px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .demo-form button:hover {
            transform: translateY(-1px);
        }

        .support-contact {
            display: grid;
            grid-template-columns: 96px 1fr;
            gap: 12px;
            align-items: center;
        }

        .support-avatar {
            width: 96px;
            height: 96px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.18);
        }

        .support-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .support-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .support-actions a {
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid #d9e2ec;
            font-weight: 600;
            color: #0f6f73;
            background: #f8fafc;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
        }

        .kpi-card {
            border-radius: 14px;
            padding: 14px 16px;
            background: #1f2937;
            border: none;
            display: grid;
            gap: 10px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::after {
            content: "";
            position: absolute;
            right: -20px;
            top: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
        }

        .kpi-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: rgba(255, 255, 255, 0.85);
        }

        .kpi-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 11px;
            font-weight: 700;
            border: 2px solid rgba(255, 255, 255, 0.6);
            color: #ffffff;
            background: rgba(255, 255, 255, 0.2);
        }

        .kpi-value {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
        }

        .kpi-trend {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        .kpi-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            overflow: hidden;
        }

        .kpi-bar span {
            display: block;
            height: 100%;
        }

        .kpi-card.green { background: linear-gradient(135deg, #00c853, #00b894); }
        .kpi-card.orange { background: linear-gradient(135deg, #f39c12, #f1c40f); }
        .kpi-card.red { background: linear-gradient(135deg, #e74c3c, #c0392b); }

        .kpi-card.green .kpi-bar span,
        .kpi-card.orange .kpi-bar span,
        .kpi-card.red .kpi-bar span {
            background: rgba(255, 255, 255, 0.85);
        }

        .metric-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .metric-link .metric-card {
            height: 100%;
        }


        .metric-grid .metric-link:nth-child(1) .metric-card { background: linear-gradient(135deg, #00c6ff, #0072ff); }
        .metric-grid .metric-link:nth-child(2) .metric-card { background: linear-gradient(135deg, #00b894, #00cec9); }
        .metric-grid .metric-link:nth-child(3) .metric-card { background: linear-gradient(135deg, #6c5ce7, #8e44ad); }
        .metric-grid .metric-link:nth-child(4) .metric-card { background: linear-gradient(135deg, #f39c12, #f1c40f); }
        .metric-grid .metric-link:nth-child(5) .metric-card { background: linear-gradient(135deg, #27ae60, #2ecc71); }
        .metric-grid .metric-link:nth-child(6) .metric-card { background: linear-gradient(135deg, #0984e3, #74b9ff); }
        .metric-grid .metric-link:nth-child(7) .metric-card { background: linear-gradient(135deg, #a29bfe, #6c5ce7); }
        .metric-grid .metric-link:nth-child(8) .metric-card { background: linear-gradient(135deg, #e67e22, #d35400); }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .metric-card {
            border: none;
            border-radius: 12px;
            padding: 12px 14px;
            display: grid;
            gap: 6px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .metric-card::after {
            content: "";
            position: absolute;
            right: -24px;
            top: -24px;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
        }

        .metric-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
        }

        .metric-value {
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
        }

        .metric-value.green,
        .metric-value.orange,
        .metric-value.red {
            color: #ffffff;
        }

        .activity-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        .activity-list {
            display: grid;
            gap: 8px;
            font-size: 12px;
            color: #6b7280;
        }

        .activity-item {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .status-pill {
            padding: 6px 10px;
            border-radius: 999px;
            background: #eef2f7;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .chart-area {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #eef2f7;
            padding: 12px;
            min-height: 160px;
        }

        .mini-bars {
            display: grid;
            gap: 8px;
            font-size: 12px;
        }

        .bar-row {
            display: grid;
            grid-template-columns: 110px 1fr 40px;
            gap: 8px;
            align-items: center;
        }

        .bar {
            height: 10px;
            background: #eef2f7;
            border-radius: 999px;
            overflow: hidden;
        }

        .bar span {
            display: block;
            height: 100%;
            background: linear-gradient(90deg, #60a5fa, #22c55e);
        }

        .heatmap {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 6px;
        }

        .heat-cell {
            height: 24px;
            border-radius: 6px;
            background: #eef2f7;
            display: grid;
            place-items: center;
            font-size: 10px;
            color: #6b7280;
        }

        .summary-list {
            display: grid;
            gap: 6px;
            font-size: 13px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            color: #4b5563;
        }

        .pill {
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            background: #e0f2fe;
            color: #0284c7;
        }

        .patient-stats .filters {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
            gap: 10px;
            margin-bottom: 12px;
        }

        .patient-stats .field {
            display: grid;
            gap: 6px;
            font-size: 12px;
        }

        .patient-stats .field input,
        .patient-stats .field select {
            padding: 6px 8px;
            border-radius: 6px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .patient-stats .summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 12px;
        }

        .patient-stats .summary-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px 14px;
            display: grid;
            gap: 6px;
            border-left: 4px solid #0a6fb3;
        }

        .patient-stats .summary-card .label {
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
        }

        .patient-stats .summary-card .value {
            font-size: 20px;
            font-weight: 700;
            color: #0b3f2c;
        }

        .patient-stats .table-wrap {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            margin-bottom: 12px;
        }

        .patient-stats table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .patient-stats thead th {
            background: #f0f1ff;
            color: #2e2e3a;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid var(--line);
        }

        .patient-stats tbody td {
            padding: 8px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        .patient-stats .btn {
            background: #0a6fb3;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 12px;
        }

        .patient-stats .btn.secondary {
            background: #f1f5f8;
            color: var(--muted);
            border: 1px solid var(--line);
            text-decoration: none;
        }

        @media (max-width: 1100px) {
            .patient-stats .filters {
                grid-template-columns: 1fr 1fr;
            }

            .patient-stats .summary {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .heatmap {
                grid-template-columns: repeat(6, 1fr);
            }
        }
    </style>

    <div class="dash-layout">
        <div class="tab-bar">
            <button class="tab-btn active" data-tab="overview">Overview</button>
            <button class="tab-btn" data-tab="billing">Billing & Revenue</button>
            <button class="tab-btn" data-tab="workflow">Validation & Workflow</button>
            <button class="tab-btn" data-tab="tests">Tests & Sample Analytics</button>
            <button class="tab-btn" data-tab="patients">Patient Analytics</button>
            <button class="tab-btn" data-tab="patient-stats">Patient Stats</button>
            <button class="tab-btn" data-tab="reports">Print & Report Tracking</button>
            <button class="tab-btn" data-tab="management">Advanced / Management View</button>
        </div>

        <div class="tab-panel active" id="tab-overview">
            <div class="section-card">
                <div class="section-title">Quick action</div>
                <div class="quick-actions">
                    <a href="{{ url('/admin/patient-information') }}" class="action-card">
                        <div class="action-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="7" r="4"></circle>
                                <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                            </svg>
                        </div>
                        <div class="action-title">Register New Patient</div>
                        <div class="action-sub">Register patient profile</div>
                    </a>
                    <a href="{{ url('/results/entry') }}" class="action-card">
                        <div class="action-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                            </svg>
                        </div>
                        <div class="action-title">Report Entry</div>
                        <div class="action-sub">Enter test results</div>
                    </a>
                    <a href="{{ url('/billing') }}" class="action-card">
                        <div class="action-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                <path d="M7 8h10M7 12h6M7 16h4"></path>
                            </svg>
                        </div>
                        <div class="action-title">Create Invoice</div>
                        <div class="action-sub">Billing workflow</div>
                    </a>
                    <a href="{{ url('/reports') }}" class="action-card">
                        <div class="action-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9V2h12v7"></path>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <path d="M6 14h12v8H6z"></path>
                            </svg>
                        </div>
                        <div class="action-title">Print Report</div>
                        <div class="action-sub">Ready reports</div>
                    </a>
                    <a href="{{ url('/results/validate') }}" class="action-card">
                        <div class="action-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 12l4 4 12-12"></path>
                                <path d="M4 20h16"></path>
                            </svg>
                        </div>
                        <div class="action-title">Validate Results</div>
                        <div class="action-sub">Pending approvals</div>
                    </a>
                    <a href="{{ url('/reports') }}" class="action-card">
                        <div class="action-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="7"></circle>
                                <path d="M21 21l-4.3-4.3"></path>
                            </svg>
                        </div>
                        <div class="action-title">Search Patient</div>
                        <div class="action-sub">Lookup by UHID / specimen</div>
                    </a>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">KPI Summary Cards (Top Metrics)</div>
                <div class="kpi-grid">
                    @foreach ($kpiCards as $card)
                        @php
                            $badge = $kpiBadgeMap[$card['tone']] ?? 'OK';
                            $trend = $kpiTrendMap[$card['tone']] ?? ['label' => 'Up', 'icon' => '&uarr;'];
                            $bar = $kpiBarMap[$card['tone']] ?? 60;
                        @endphp
                        <div class="kpi-card {{ $card['tone'] }}">
                            <div class="kpi-top">
                                <span>{!! $card['label'] !!}</span>
                                <span class="kpi-badge">{{ $badge }}</span>
                            </div>
                            <div class="kpi-value">{{ $card['value'] }}</div>
                            <div class="kpi-trend">
                                <span>{!! $trend['icon'] !!}</span>
                                <span>{{ $card['note'] }}</span>
                            </div>
                            <div class="kpi-bar"><span style="width: {{ $bar }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">Overview Metrics</div>
                <div class="metric-grid">
                    <a class="metric-link" href="{{ url('/admin/page/patient-stats') }}?from={{ $today }}&to={{ $today }}">
                        <div class="metric-card">
                            <div class="metric-label">Total Patients (Today)</div>
                            <div class="metric-value">{{ $overviewCounts['patients_today'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/page/print-worksheet') }}?from={{ $today }}&to={{ $today }}">
                        <div class="metric-card">
                            <div class="metric-label">Samples Collected</div>
                            <div class="metric-value">{{ $overviewCounts['samples_collected'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/reports') }}">
                        <div class="metric-card">
                            <div class="metric-label">Tests Completed</div>
                            <div class="metric-value">{{ $overviewCounts['tests_completed'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/results/entry') }}">
                        <div class="metric-card">
                            <div class="metric-label">Pending Tests</div>
                            <div class="metric-value orange">{{ $overviewCounts['pending_tests'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/page/summary') }}?from={{ $monthStart }}&to={{ $today }}">
                        <div class="metric-card">
                            <div class="metric-label">Monthly Revenue</div>
                            <div class="metric-value">{{ number_format($overviewCounts['monthly_revenue'] ?? 0, 2) }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/page/accounts') }}">
                        <div class="metric-card">
                            <div class="metric-label">Outstanding Payments</div>
                            <div class="metric-value red">{{ $overviewCounts['outstanding_payments'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/page/summary') }}?from={{ $monthStart }}&to={{ $today }}">
                        <div class="metric-card">
                            <div class="metric-label">Centre income</div>
                            <div class="metric-value">{{ number_format($overviewCounts['centre_income'] ?? 0, 2) }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/page/summary') }}?from={{ $monthStart }}&to={{ $today }}">
                        <div class="metric-card">
                            <div class="metric-label">Commitions</div>
                            <div class="metric-value">{{ number_format($overviewCounts['commissions'] ?? 0, 2) }}</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">Work Queue</div>
                <div class="metric-grid">
                    <a class="metric-link" href="{{ url('/billing') }}">
                        <div class="metric-card">
                            <div class="metric-label">Samples Waiting for Collection</div>
                            <div class="metric-value">{{ $metrics['pending_samples'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/results/entry') }}">
                        <div class="metric-card">
                            <div class="metric-label">Tests In Progress</div>
                            <div class="metric-value">{{ $workflowStats['tests_in_progress'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/results/validate') }}">
                        <div class="metric-card">
                            <div class="metric-label">Tests Pending Validation</div>
                            <div class="metric-value">{{ $workflowStats['awaiting_validation'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/reports') }}">
                        <div class="metric-card">
                            <div class="metric-label">Reports Ready to Print</div>
                            <div class="metric-value">{{ $metrics['pending_print'] ?? 0 }}</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">Charts & Graphs</div>
                <div class="grid-2">
                    <div>
                        <div class="section-title" style="font-size:14px;">Tests Distribution (CBC, FBS, LFT, etc.)</div>
                        <div class="summary-list">
                            @forelse ($testAnalytics['category_split'] as $row)
                                <div class="summary-item"><span>{{ $row->name }}</span><strong>{{ $row->total }}</strong></div>
                            @empty
                                <div class="summary-item">No distribution data.</div>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <div class="section-title" style="font-size:14px;">Daily Samples Trend</div>
                        <div class="mini-bars">
                            @foreach ($dailyCounts as $row)
                                <div class="bar-row">
                                    <span>{{ $row['label'] }}</span>
                                    <div class="bar"><span style="width: {{ number_format(($row['total'] / $dailyMax) * 100, 2, '.', '') }}%"></span></div>
                                    <strong>{{ $row['total'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="section-title" style="font-size:14px;margin-top:12px;">Invoice Status (Paid / Unpaid / Partial)</div>
                <div class="mini-bars">
                    @forelse ($invoiceStatus as $row)
                        <div class="bar-row">
                            <span>{{ $row->payment_status }}</span>
                            <div class="bar"><span style="width: {{ number_format(($row->total / $invoiceStatusMax) * 100, 2, '.', '') }}%"></span></div>
                            <strong>{{ $row->total }}</strong>
                        </div>
                    @empty
                        <div class="summary-item">No invoice status data.</div>
                    @endforelse
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">Alerts & Notifications</div>
                <div class="metric-grid">
                    <a class="metric-link" href="{{ url('/results/edit') }}">
                        <div class="metric-card">
                            <div class="metric-label">Critical Test Results</div>
                            <div class="metric-value red">{{ $alerts['critical_results'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/page/print-worksheet') }}">
                        <div class="metric-card">
                            <div class="metric-label">Delayed Samples</div>
                            <div class="metric-value orange">{{ $alerts['delayed_samples'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/tests') }}">
                        <div class="metric-card">
                            <div class="metric-label">QC Failed Tests</div>
                            <div class="metric-value">{{ $metrics['qc_alerts'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/stock') }}">
                        <div class="metric-card">
                            <div class="metric-label">Low Reagent Stock</div>
                            <div class="metric-value orange">{{ $alerts['low_reagent_stock'] ?? 0 }}</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">Recent Activity</div>
                <div class="activity-grid">
                    <div class="activity-list">
                        <div class="metric-label">Recently Validated Reports</div>
                        @forelse ($recentActivity['validated_reports'] as $row)
                            <div class="activity-item"><span>Specimen {{ $row->specimen_id }}</span><span>{{ optional($row->updated_at)->format('m/d H:i') }}</span></div>
                        @empty
                            <div class="activity-item">No validated reports.</div>
                        @endforelse
                    </div>
                    <div class="activity-list">
                        <div class="metric-label">Recently Created Invoices</div>
                        @forelse ($recentActivity['created_invoices'] as $row)
                            <div class="activity-item"><span>{{ $row->invoice_no }}</span><span>{{ number_format($row->net_total, 2) }}</span></div>
                        @empty
                            <div class="activity-item">No invoices.</div>
                        @endforelse
                    </div>
                    <div class="activity-list">
                        <div class="metric-label">Sample Rejections / Updates</div>
                        @forelse ($recentActivity['rejections'] as $row)
                            <div class="activity-item"><span>Specimen {{ $row->specimen_id }}</span><span>{{ optional($row->updated_at)->format('m/d H:i') }}</span></div>
                        @empty
                            <div class="activity-item">No rejections.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">User-Based View</div>
                <div class="metric-grid">
                    <a class="metric-link" href="{{ url('/results/validate') }}">
                        <div class="metric-card">
                            <div class="metric-label">Doctor: Pending validations</div>
                            <div class="metric-value">{{ $userView['doctor_pending_validations'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/results/edit') }}">
                        <div class="metric-card">
                            <div class="metric-label">Doctor: Critical results</div>
                            <div class="metric-value red">{{ $userView['doctor_critical_results'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/billing') }}">
                        <div class="metric-card">
                            <div class="metric-label">Technician: Sample queue</div>
                            <div class="metric-value">{{ $userView['technician_sample_queue'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/results/entry') }}">
                        <div class="metric-card">
                            <div class="metric-label">Technician: Test queue</div>
                            <div class="metric-value">{{ $userView['technician_test_queue'] ?? 0 }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/page/summary') }}?from={{ $monthStart }}&to={{ $today }}">
                        <div class="metric-card">
                            <div class="metric-label">Admin: Revenue</div>
                            <div class="metric-value">{{ number_format($userView['admin_revenue'] ?? 0, 2) }}</div>
                        </div>
                    </a>
                    <a class="metric-link" href="{{ url('/admin/stock') }}">
                        <div class="metric-card">
                            <div class="metric-label">Admin: Stock</div>
                            <div class="metric-value">{{ $userView['admin_stock'] ?? 0 }}</div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">System Status</div>
                <div class="status-grid">
                    <div class="metric-card">
                        <div class="metric-label">Analyzer Connection Status</div>
                        <div class="status-pill">{{ $systemStatus['analyzer_connection'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">LIS Online / Offline</div>
                        <div class="status-pill">{{ $systemStatus['lis_status'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-label">Backup Status (optional)</div>
                        <div class="status-pill">{{ $systemStatus['backup_status'] ?? 'Unknown' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-panel" id="tab-billing">
            <div class="section-card">
                <div class="section-title">Billing & Revenue</div>
                <div class="grid-2">
                    <div class="summary-list">
                        <div class="summary-item"><span>&#128179; Total Invoices</span><strong>{{ $billingStats['total_invoices'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>&#128176; Paid vs Unpaid</span><strong>{{ $billingStats['paid'] ?? 0 }} / {{ $billingStats['unpaid'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>&#9203; Overdue Payments</span><strong>{{ $billingStats['overdue'] ?? 0 }}</strong></div>
                    </div>
                    <div class="chart-area">
                        <div class="section-title" style="font-size:14px;">&#128200; Monthly Revenue Trend (bar / line)</div>
                        <div class="mini-bars">
                            @foreach ($billingTrend as $row)
                                <div class="bar-row">
                                    <span>{{ $row['label'] }}</span>
                                    <div class="bar"><span style="width: {{ $row['total'] > 0 ? min(100, ($row['total'] / $billingTrendMax) * 100) : 0 }}%"></span></div>
                                    <strong>{{ number_format($row['total'], 0) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-card">
                <div class="section-title">Invoice Status (Chart)</div>
                <div class="mini-bars">
                    @forelse ($invoiceStatus as $row)
                        <div class="bar-row">
                            <span>{{ $row->payment_status }}</span>
                            <div class="bar"><span style="width: {{ number_format(($row->total / $invoiceStatusMax) * 100, 2, '.', '') }}%"></span></div>
                            <strong>{{ $row->total }}</strong>
                        </div>
                    @empty
                        <div class="summary-item">No invoice data.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="tab-panel" id="tab-workflow">
            <div class="section-card">
                <div class="section-title">Validation & Workflow Status</div>
                <div class="grid-2">
                    <div class="summary-list">
                        <div class="summary-item"><span>&#129535; Awaiting Validation</span><strong>{{ $workflowStats['awaiting_validation'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>&#9989; Validated Reports</span><strong>{{ $workflowStats['validated_reports'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>&#128300; Samples Received</span><strong>{{ $workflowStats['samples_received'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>&#129514; Tests In Progress</span><strong>{{ $workflowStats['tests_in_progress'] ?? 0 }}</strong></div>
                    </div>
                    <div class="chart-area">
                        <div class="section-title" style="font-size:14px;">Workflow Snapshot</div>
                        <div class="summary-list">
                            <div class="summary-item"><span>Pending</span><span class="pill">{{ $metrics['pending_reports'] ?? 0 }}</span></div>
                            <div class="summary-item"><span>Approved</span><span class="pill">{{ $metrics['approved'] ?? 0 }}</span></div>
                            <div class="summary-item"><span>Rejected</span><span class="pill">{{ $metrics['rejected_results'] ?? 0 }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-panel" id="tab-tests">
            <div class="section-card">
                <div class="section-title">Tests & Sample Analytics</div>
                <div class="grid-2">
                    <div>
                        <div class="section-title" style="font-size:14px;">Pie chart &#8594; Test category split</div>
                        <div class="summary-list">
                            @forelse ($testAnalytics['category_split'] as $row)
                                <div class="summary-item"><span>{{ $row->name }}</span><strong>{{ $row->total }}</strong></div>
                            @empty
                                <div class="summary-item">No category data.</div>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <div class="section-title" style="font-size:14px;">Bar chart &#8594; Most requested tests</div>
                        <div class="mini-bars">
                            @foreach ($topTests as $test)
                                <div class="bar-row">
                                    <span>{{ $test->name }}</span>
                                    <div class="bar"><span style="width: {{ number_format(($test->total / max(1, $topTests->max('total'))) * 100, 2, '.', '') }}%"></span></div>
                                    <strong>{{ $test->total }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">Heatmap &#8594; Peak test hours</div>
                <div class="heatmap">
                    @foreach ($testAnalytics['heatmap'] as $hour => $count)
                        @php
                            $opacity = $heatmapMax > 0 ? min(1, $count / $heatmapMax) : 0;
                        @endphp
                        <div class="heat-cell" style="background: rgba(59, 130, 246, {{ 0.1 + ($opacity * 0.6) }});">
                            {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="section-card">
                <div class="section-title">&#128200; Test Volume</div>
                <div class="grid-2">
                    <div>
                        <div class="section-title" style="font-size:14px;">Daily</div>
                        <div class="mini-bars">
                            @foreach ($dailyCounts as $row)
                                <div class="bar-row">
                                    <span>{{ $row['label'] }}</span>
                                    <div class="bar"><span style="width: {{ number_format(($row['total'] / $dailyMax) * 100, 2, '.', '') }}%"></span></div>
                                    <strong>{{ $row['total'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <div class="section-title" style="font-size:14px;">Weekly / Monthly</div>
                        <div class="mini-bars">
                            @foreach ($weeklyCounts as $row)
                                <div class="bar-row">
                                    <span>{{ $row['label'] }}</span>
                                    <div class="bar"><span style="width: {{ number_format(($row['total'] / $weeklyMax) * 100, 2, '.', '') }}%"></span></div>
                                    <strong>{{ $row['total'] }}</strong>
                                </div>
                            @endforeach
                            @foreach ($monthlyCounts as $row)
                                <div class="bar-row">
                                    <span>{{ $row['label'] }}</span>
                                    <div class="bar"><span style="width: {{ number_format(($row['total'] / $monthlyMax) * 100, 2, '.', '') }}%"></span></div>
                                    <strong>{{ $row['total'] }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-panel" id="tab-patients">
            <div class="section-card">
                <div class="section-title">Patient Analytics</div>
                <div class="grid-2">
                    <div class="summary-list">
                        <div class="summary-item"><span>New vs Returning Patients</span><strong>{{ $patientAnalytics['new_vs_returning']['new'] ?? 0 }} / {{ $patientAnalytics['new_vs_returning']['returning'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>Gender ratio</span><strong>{{ collect($patientAnalytics['gender_ratio'])->sum('total') }}</strong></div>
                        <div class="summary-item"><span>OPD vs Referral patients</span><strong>{{ $patientAnalytics['opd_vs_referral']['opd'] ?? 0 }} / {{ $patientAnalytics['opd_vs_referral']['referral'] ?? 0 }}</strong></div>
                    </div>
                    <div>
                        <div class="section-title" style="font-size:14px;">Age group distribution</div>
                        <div class="mini-bars">
                            @foreach ($patientAnalytics['age_groups'] as $group => $count)
                                <div class="bar-row">
                                    <span>{{ $group }}</span>
                                    <div class="bar"><span style="width: {{ number_format(($count / max(1, max($patientAnalytics['age_groups']))) * 100, 2, '.', '') }}%"></span></div>
                                    <strong>{{ $count }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="section-title" style="font-size:14px; margin-top:12px;">Gender ratio</div>
                <div class="mini-bars">
                    @forelse ($patientAnalytics['gender_ratio'] as $row)
                        <div class="bar-row">
                            <span>{{ $row->sex ?: 'Unknown' }}</span>
                            <div class="bar"><span style="width: {{ number_format(($row->total / max(1, collect($patientAnalytics['gender_ratio'])->max('total'))) * 100, 2, '.', '') }}%"></span></div>
                            <strong>{{ $row->total }}</strong>
                        </div>
                    @empty
                        <div class="summary-item">No gender data.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="tab-panel" id="tab-patient-stats">
            <div class="section-card patient-stats">
                <div class="section-title">Patient Statistics</div>
                <form method="get" action="{{ url('/admin') }}">
                    <input type="hidden" name="tab" value="patient-stats">
                    <div class="filters">
                        <div class="field">
                            <label>From</label>
                            <input type="date" name="ps_from" value="{{ $patientStats['filters']['from'] ?? '' }}">
                        </div>
                        <div class="field">
                            <label>To</label>
                            <input type="date" name="ps_to" value="{{ $patientStats['filters']['to'] ?? '' }}">
                        </div>
                        <div class="field">
                            <label>Group</label>
                            <select name="ps_group">
                                <option value="day" @selected(($patientStats['filters']['group'] ?? '') === 'day')>Daily</option>
                                <option value="month" @selected(($patientStats['filters']['group'] ?? '') === 'month')>Monthly</option>
                                <option value="year" @selected(($patientStats['filters']['group'] ?? '') === 'year')>Annual</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Search</label>
                            <input type="text" name="ps_q" value="{{ $patientStats['filters']['q'] ?? '' }}" placeholder="Specimen, patient, NIC, test">
                        </div>
                        <div class="field" style="align-self:end;">
                            <button class="btn" type="submit">Apply</button>
                            <a class="btn secondary" href="{{ url('/admin?tab=patient-stats') }}">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="summary">
                    <div class="summary-card">
                        <div class="label">Patients</div>
                        <div class="value">{{ $patientStats['totals']['patients'] ?? 0 }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="label">Specimens</div>
                        <div class="value">{{ $patientStats['totals']['specimens'] ?? 0 }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="label">Tests</div>
                        <div class="value">{{ $patientStats['totals']['tests'] ?? 0 }}</div>
                    </div>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Patient Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($patientStats['trendRows'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row->label ?? '-' }}</td>
                                    <td>{{ $row->patient_count ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">No data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Specimen No</th>
                                <th>Patient</th>
                                <th>Age/Sex</th>
                                <th>Center</th>
                                <th>Tests</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($patientStats['specimens'] ?? [] as $specimen)
                                <tr>
                                    <td>{{ optional($specimen->created_at)->format('Y-m-d') }}</td>
                                    <td>{{ $specimen->specimen_no ?? '-' }}</td>
                                    <td>{{ $specimen->patient->name ?? '-' }}</td>
                                    <td>{{ $specimen->age_display ?? '-' }} / {{ $specimen->patient->sex ?? '-' }}</td>
                                    <td>{{ $specimen->center->name ?? '-' }}</td>
                                    <td>
                                        @foreach ($specimen->tests as $test)
                                            <div>{{ $test->testMaster->name ?? '-' }}</div>
                                        @endforeach
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">No records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="support-section">
                <div class="support-card">
                    <h3>Demo Accounts & Trial Access</h3>
                    <p class="text-muted" style="margin:0;">Create demo accounts for partners to evaluate the labtech.lk platform. Each demo user can see their expiry window so there's no guesswork.</p>
                    @if(session('demoAccountSuccess'))
                        <div style="border-radius:10px;background:#e6fffa;padding:10px 14px;border:1px solid #bdf5ea;font-size:13px;">
                            {{ session('demoAccountSuccess') }}
                        </div>
                    @endif
                    <table class="support-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email / Phone</th>
                                <th>Expires</th>
                                <th>Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($demoAccounts as $demo)
                                <tr>
                                    <td>{{ $demo['name'] }}</td>
                                    <td>
                                        @if($demo['email']) {{ $demo['email'] }} @endif
                                        @if($demo['phone']) <br>{{ $demo['phone'] }} @endif
                                    </td>
                                    <td>{{ $demo['expires_at'] }}</td>
                                    <td>{{ $demo['expires_in'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center;">No demo accounts yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if ($isSuperAdmin)
                        <form class="demo-form" method="post" action="{{ route('demo-accounts.store') }}">
                            @csrf
                            <input type="text" name="name" placeholder="Name" required>
                            <input type="email" name="email" placeholder="Email (optional)">
                            <input type="text" name="phone" placeholder="Phone (optional)">
                            <input type="datetime-local" name="expires_at" required>
                            <input type="text" name="notes" placeholder="Notes (optional)">
                            <button type="submit">Create Demo Account</button>
                        </form>
                    @endif
                </div>
                <div class="support-card">
                    <div class="support-contact">
                        <div class="support-avatar">
                            <img src="{{ asset('images/support/peter.svg') }}" alt="Peter Support">
                        </div>
                        <div>
                            <h3>Peter - Support Desk</h3>
                            <p style="margin:0;">Call or WhatsApp to report issues, request demo credentials, and track expiry timelines. Lab admins can escalate tickets directly to Peter.</p>
                            <p style="margin:0;font-weight:600;">+94 77 270 2303</p>
                        </div>
                    </div>
                    <div class="support-actions">
                        <a href="https://wa.me/94772702303?text=Hello%20Peter%2C%20I%20need%20support" target="_blank">WhatsApp Peter</a>
                        <a href="tel:+94772702303">Call Peter</a>
                        <a href="mailto:support@labtech.lk?subject=Support%20Ticket%20-%20Labtech.lk">Raise Ticket</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-panel" id="tab-reports">
            <div class="section-card">
                <div class="section-title">Print & Report Tracking</div>
                <div class="grid-2">
                    <div class="summary-list">
                        <div class="summary-item"><span>&#128424; Reports Printed Today</span><strong>{{ $metrics['reports_printed_today'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>&#128231; Reports Sent via Email / WhatsApp</span><strong>{{ $metrics['reports_sent_email'] ?? 0 }} / {{ $metrics['reports_sent_whatsapp'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>&#128274; QR Verified Reports</span><strong>{{ $metrics['qr_verified_reports'] ?? 0 }}</strong></div>
                        <div class="summary-item"><span>&#9888; Reprint Requests</span><strong>{{ $metrics['reprint_requests'] ?? 0 }}</strong></div>
                    </div>
                    <div class="chart-area">
                        <div class="section-title" style="font-size:14px;">Alerts & Notifications</div>
                        <div class="summary-list">
                            <div class="summary-item"><span>Printed today</span><span class="pill">{{ $metrics['reports_printed_today'] ?? 0 }}</span></div>
                            <div class="summary-item"><span>Email sent</span><span class="pill">{{ $metrics['reports_sent_email'] ?? 0 }}</span></div>
                            <div class="summary-item"><span>WhatsApp sent</span><span class="pill">{{ $metrics['reports_sent_whatsapp'] ?? 0 }}</span></div>
                            <div class="summary-item"><span>Reprint</span><span class="pill">{{ $metrics['reprint_requests'] ?? 0 }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-panel" id="tab-management">
            <div class="section-card">
                <div class="section-title">Advanced / Management View</div>
                <div class="grid-2">
                    <div class="summary-list">
                        <div class="summary-item"><span>Turnaround Time (TAT)</span><strong>{{ $advancedStats['tat_median_minutes'] ?? '--' }}m (P90 {{ $advancedStats['tat_p90_minutes'] ?? '--' }}m)</strong></div>
                        <div class="summary-item"><span>Machine Utilization</span><strong>Pending data</strong></div>
                        <div class="summary-item"><span>Reagent Consumption</span><strong>{{ $reagentConsumption->sum('total') ?? 0 }}</strong></div>
                        <div class="summary-item"><span>Multi-branch Comparison</span><strong>{{ $multiBranch->count() ?? 0 }} centers</strong></div>
                    </div>
                    <div>
                        <div class="section-title" style="font-size:14px;">Doctor-wise Referrals</div>
                        <div class="mini-bars">
                            @forelse ($doctorReferrals as $row)
                                <div class="bar-row">
                                    <span>{{ $row->name }}</span>
                                    <div class="bar"><span style="width: {{ number_format(($row->total / $doctorReferralMax) * 100, 2, '.', '') }}%"></span></div>
                                    <strong>{{ number_format($row->total, 0) }}</strong>
                                </div>
                            @empty
                                <div class="summary-item">No referral data.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setActiveTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(function (btn) {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-panel').forEach(function (panel) {
                panel.classList.remove('active');
            });
            var activeButton = document.querySelector('.tab-btn[data-tab="' + tab + '"]');
            var target = document.getElementById('tab-' + tab);
            if (activeButton && target) {
                activeButton.classList.add('active');
                target.classList.add('active');
            }
        }

        document.querySelectorAll('.tab-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                setActiveTab(button.dataset.tab);
            });
        });

        var params = new URLSearchParams(window.location.search);
        var initialTab = params.get('tab');
        if (initialTab) {
            setActiveTab(initialTab);
        }
    </script>
@endsection

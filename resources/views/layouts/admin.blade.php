<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin Panel' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #08b9f3;
            --secondary: #039ad7;
            --accent: #017aa8;
            --ink: #2c2f35;
            --muted: #6e7b88;
            --brand: #039ad7;
            --brand-dark: #015770;
            --bg: #eef7fb;
            --card: #ffffff;
            --line: #e2e6ea;
            --glass: rgba(255, 255, 255, 0.7);
            --shadow-soft: 0 10px 22px rgba(24, 35, 45, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Inter", "Segoe UI", sans-serif;
            color: var(--ink);
            background: var(--bg);
            font-size: 14px;
        }

        .shell {
            display: grid;
            grid-template-columns: 240px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--sidebar-color-start) 0%, var(--sidebar-color-mid) 35%, var(--sidebar-color-end) 65%, #02364a 100%);
            color: var(--sidebar-text-color);
            padding: 12px 16px;
            display: grid;
            grid-template-rows: auto 1fr;
            gap: 10px;
            width: 240px;
            height: 100vh;
            position: sticky;
            top: 0;
            z-index: 5;
            overflow-y: auto;
            border-right: 1px solid rgba(2, 54, 74, 0.6);
            align-self: stretch;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 16px;
            color: #e7f6f5;
        }

        .brand-mark {
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: linear-gradient(135deg, #1cc9f5, #0b7fb0);
            color: #ffffff;
            display: grid;
            place-items: center;
            font-weight: 800;
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #fff;
            display: grid;
            place-items: center;
            padding: 6px;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.08);
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .brand-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav {
            display: grid;
            gap: 4px;
            font-size: 12px;
            overflow-y: auto;
            padding-right: 4px;
            min-height: 0;
        }

        .lab-list {
            margin-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 12px;
            font-size: 12px;
        }

        .lab-title {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 10px;
            color: rgba(230, 241, 245, 0.6);
            margin-bottom: 8px;
        }

        .lab-items {
            display: grid;
            gap: 6px;
            max-height: 180px;
            overflow: auto;
            padding-right: 4px;
        }

        .lab-item {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 6px 10px;
            font-weight: 600;
            color: #fff;
        }

        .nav-section {
            margin-top: 6px;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(230, 241, 245, 0.6);
            padding: 4px 8px 0;
        }

        .nav-group-toggle {
            background: none;
            border: none;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(230, 241, 245, 0.7);
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .nav-group-toggle span {
            margin-left: auto;
            font-size: 12px;
        }

        .nav-group-items.is-hidden {
            display: none;
        }

        .nav a {
            text-decoration: none;
            color: var(--sidebar-text-color);
            padding: 8px 10px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease, border 0.2s ease;
            background: transparent;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .nav a:hover,
        .nav a.active {
            background: #1fb2c9;
            border-color: rgba(255, 255, 255, 0.25);
            color: #ffffff;
        }

        .nav .icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            background: transparent;
            border: 1px solid transparent;
        }

        .nav a:hover .icon,
        .nav a.active .icon {
            color: #ffffff;
        }

        .nav .icon svg {
            width: 18px;
            height: 18px;
        }

        .icon.is-validate { color: #35d0c2; background: rgba(53, 208, 194, 0.18); }
        .icon.is-approve { color: #7cc2ff; background: rgba(124, 194, 255, 0.18); }
        .icon.is-edit { color: #f6b26b; background: rgba(246, 178, 107, 0.18); }
        .icon.is-print { color: #f08cc8; background: rgba(240, 140, 200, 0.18); }
        .icon.is-patient { color: #9ad673; background: rgba(154, 214, 115, 0.18); }
        .icon.is-worksheet { color: #f1c84b; background: rgba(241, 200, 75, 0.18); }
        .icon.is-entry { color: #8ea0ff; background: rgba(142, 160, 255, 0.18); }
        .icon.is-stock { color: #6fd3a7; background: rgba(111, 211, 167, 0.18); }
        .icon.is-accounts { color: #ff9d7d; background: rgba(255, 157, 125, 0.18); }
        .icon.is-summary { color: #9fb7c9; background: rgba(159, 183, 201, 0.18); }
        .icon.is-settings { color: #f4c2d0; background: rgba(244, 194, 208, 0.18); }
        .icon.is-dashboard { color: #78e0b5; background: rgba(120, 224, 181, 0.18); }
        .icon.is-master { color: #8dd6ff; background: rgba(141, 214, 255, 0.18); }
        .icon.is-shop { color: #f7c27b; background: rgba(247, 194, 123, 0.18); }

        .content {
            padding: 28px 32px 48px;
        }

        .nav::-webkit-scrollbar {
            width: 6px;
        }

        .nav::-webkit-scrollbar-track {
            background: rgba(2, 54, 74, 0.35);
            border-radius: 999px;
        }

        .nav::-webkit-scrollbar-thumb {
            background: rgba(8, 185, 243, 0.6);
            border-radius: 999px;
        }

        .nav::-webkit-scrollbar-thumb:hover {
            background: rgba(8, 185, 243, 0.85);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            gap: 12px;
        }

        .title {
            font-size: 24px;
            font-weight: 700;
        }

        .sidebar-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--ink);
            cursor: pointer;
            flex-shrink: 0;
        }

        .sidebar-toggle svg {
            width: 20px;
            height: 20px;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.4);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            z-index: 4;
        }

        .user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--muted);
        }

        .card,
        .panel,
        .box,
        .widget,
        .summary-card,
        .metric-card,
        .kpi-card,
        .action-card,
        .quick-card,
        .stat-card,
        .form-card,
        .filter-card,
        .table-card,
        .table-wrap {
            position: relative;
            background: #ffffff;
            border-radius: 16px;
            border: none;
            padding: 20px;
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
            overflow: hidden;
        }

        .card::before,
        .panel::before,
        .box::before,
        .widget::before,
        .summary-card::before,
        .metric-card::before,
        .kpi-card::before,
        .action-card::before,
        .quick-card::before,
        .stat-card::before,
        .form-card::before,
        .filter-card::before,
        .table-card::before,
        .table-wrap::before {
            content: none;
        }

        @media (max-width: 900px) {
            .shell {
                grid-template-columns: 1fr;
            }

            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                width: 260px;
                transform: translateX(-110%);
                transition: transform 0.2s ease;
                z-index: 5;
            }

            body.sidebar-open .sidebar {
                transform: translateX(0);
            }

            .lab-list {
                display: none;
            }

            .content {
                padding: 18px 16px 28px;
            }

            .sidebar-toggle {
                display: inline-flex;
            }

            body.sidebar-open .sidebar-overlay {
                opacity: 1;
                visibility: visible;
            }
        }
    </style>
    <style>
        :root{
            --ui-bg:#f8fafc;
            --ui-card:#ffffff;
            --ui-border:#e2e8f0;
            --ui-primary:#0f766e;
            --ui-text:#0f172a;
            --ui-muted:#64748b;
            --ui-radius:12px;
        }

        body{
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background:var(--ui-bg);
            color:var(--ui-text);
        }

        .ui-page{
            padding:24px;
        }

        .ui-card{
            background:var(--ui-card);
            border:1px solid var(--ui-border);
            border-radius:var(--ui-radius);
            padding:16px 18px;
            margin-bottom:20px;
        }

        .ui-card-title{
            font-size:16px;
            font-weight:600;
            margin-bottom:2px;
        }

        .ui-card-sub{
            font-size:13px;
            color:var(--ui-muted);
            margin-bottom:14px;
        }

        .ui-form-grid{
            display:grid;
            grid-template-columns:repeat(2,minmax(0,1fr));
            gap:14px 16px;
        }

        .ui-form-grid-4{
            grid-template-columns:repeat(4,minmax(0,1fr));
        }

        .ui-form-grid-3{
            grid-template-columns:repeat(3,minmax(0,1fr));
        }

        @media(min-width:1400px){
            .ui-form-grid{
                grid-template-columns:repeat(3,minmax(0,1fr));
            }
        }

        @media(max-width:1200px){
            .ui-form-grid-4{
                grid-template-columns:repeat(2,minmax(0,1fr));
            }
            .ui-form-grid-3{
                grid-template-columns:repeat(2,minmax(0,1fr));
            }
        }

        @media(max-width:700px){
            .ui-form-grid-4{
                grid-template-columns:1fr;
            }
            .ui-form-grid-3{
                grid-template-columns:1fr;
            }
        }

        .ui-input,
        .ui-select{
            width:100%;
            height:44px;
            padding:8px 12px;
            border:1px solid var(--ui-border);
            border-radius:10px;
            font-size:14px;
            background:#fff;
        }

        .ui-input:focus,
        .ui-select:focus{
            outline:none;
            border-color:var(--ui-primary);
            box-shadow:0 0 0 3px rgba(15,118,110,.12);
        }

        .ui-label{
            font-size:13px;
            font-weight:500;
            margin-bottom:6px;
            display:block;
        }

        .ui-required::after{
            content:" *";
            color:#dc2626;
        }

        .ui-toggle{
            display:flex;
            align-items:center;
            gap:10px;
        }

        .ui-segment{
            display:flex;
            gap:8px;
            flex-wrap:wrap;
        }

        .ui-segment label{
            padding:7px 14px;
            border:1px solid var(--ui-border);
            border-radius:999px;
            cursor:pointer;
            font-size:13px;
            user-select:none;
        }

        .ui-segment input{
            display:none;
        }

        .ui-segment input:checked + span{
            background:var(--ui-primary);
            color:#fff;
            border-color:var(--ui-primary);
        }

        .ui-billing-grid{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:18px;
        }

        .ui-billing-grid.ui-billing-grid--with-actions{
            grid-template-columns:1fr auto 1fr;
            align-items:start;
        }

        .ui-transfer{
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:10px;
            padding-top:52px;
        }

        @media(max-width:1100px){
            .ui-billing-grid{
                grid-template-columns:1fr;
            }
            .ui-billing-grid.ui-billing-grid--with-actions{
                grid-template-columns:1fr;
            }
            .ui-transfer{
                flex-direction:row;
                padding-top:0;
            }
        }

        .ui-table{
            width:100%;
            border-collapse:separate;
            border-spacing:0;
            font-size:13px;
        }

        .ui-table th{
            text-align:left;
            font-weight:600;
            padding:10px;
            border-bottom:1px solid var(--ui-border);
            background:#f1f5f9;
        }

        .ui-table td{
            padding:9px 10px;
            border-bottom:1px solid var(--ui-border);
        }

        .ui-summary{
            border:1px solid var(--ui-border);
            border-radius:10px;
            padding:12px;
            background:#f8fafc;
        }
    </style>
</head>
<body>
    @php
        use Illuminate\Support\Facades\Schema;
        use Illuminate\Support\Facades\DB;
        use App\Models\Permission;
        use App\Models\Setting;
        use App\Models\Lab;
        $user = auth()->user();
        $permissionEnabled = false;
        $enforcePermissions = true;
        $isSuperAdmin = $user && $user->isSuperAdmin();
        if (Schema::hasTable('permissions') && Permission::query()->count() > 0) {
            if (Schema::hasTable('permission_user') && Schema::hasTable('role_user')) {
                $permissionEnabled = DB::table('permission_user')->exists() || DB::table('role_user')->exists();
            } else {
                $permissionEnabled = true;
            }
        }
        $currentLab = $user?->lab;
        $labLogoPath = null;
        $labNameSetting = null;
        $sidebarColorStart = null;
        $sidebarColorMid = null;
        $sidebarColorEnd = null;
        $sidebarTextColor = null;
        if (Schema::hasTable('settings')) {
            $enforceQuery = Setting::query()->where('key', 'user_enforce_permissions');
            if ($isSuperAdmin) {
                $enforceQuery->whereNull('lab_id');
            } elseif ($user && $user->lab_id) {
                $enforceQuery->where('lab_id', $user->lab_id);
            } else {
                $enforceQuery->whereRaw('1 = 0');
            }
            $enforceValue = $enforceQuery->value('value');
            if ($enforceValue === '0') {
                $enforcePermissions = false;
            }

            if ($currentLab) {
                $labSettings = Setting::query()
                    ->where('lab_id', $currentLab->id)
                    ->whereIn('key', [
                        'lab_name',
                        'lab_logo_path',
                        'sidebar_color_start',
                        'sidebar_color_mid',
                        'sidebar_color_end',
                        'sidebar_text_color',
                    ])
                    ->pluck('value', 'key')
                    ->all();
                $labNameSetting = $labSettings['lab_name'] ?? null;
                $labLogoPath = $labSettings['lab_logo_path'] ?? null;
                $sidebarColorStart = $labSettings['sidebar_color_start'] ?? null;
                $sidebarColorMid = $labSettings['sidebar_color_mid'] ?? null;
                $sidebarColorEnd = $labSettings['sidebar_color_end'] ?? null;
                $sidebarTextColor = $labSettings['sidebar_text_color'] ?? null;
            }
            if (!$labNameSetting) {
                $labNameSetting = Setting::query()
                    ->whereNull('lab_id')
                    ->where('key', 'lab_name')
                    ->value('value');
            }
            if (!$labLogoPath) {
                $labLogoPath = Setting::query()
                    ->whereNull('lab_id')
                    ->where('key', 'lab_logo_path')
                    ->value('value');
            }
            if (!$sidebarColorStart) {
                $sidebarColorStart = Setting::query()
                    ->whereNull('lab_id')
                    ->where('key', 'sidebar_color_start')
                    ->value('value');
            }
            if (!$sidebarColorMid) {
                $sidebarColorMid = Setting::query()
                    ->whereNull('lab_id')
                    ->where('key', 'sidebar_color_mid')
                    ->value('value');
            }
            if (!$sidebarColorEnd) {
                $sidebarColorEnd = Setting::query()
                    ->whereNull('lab_id')
                    ->where('key', 'sidebar_color_end')
                    ->value('value');
            }
            if (!$sidebarTextColor) {
                $sidebarTextColor = Setting::query()
                    ->whereNull('lab_id')
                    ->where('key', 'sidebar_text_color')
                    ->value('value');
            }
        }
        $sidebarColorStart = $sidebarColorStart ?? '#08b9f3';
        $sidebarColorMid = $sidebarColorMid ?? '#039ad7';
        $sidebarColorEnd = $sidebarColorEnd ?? '#015770';
        $sidebarTextColor = $sidebarTextColor ?? '#e6f1f5';
        $brandName = $currentLab?->name ?? $labNameSetting ?? 'Labtech.lk';
        $brandInitial = strtoupper(function_exists('mb_substr') ? mb_substr($brandName, 0, 1) : substr($brandName, 0, 1));
        $can = function (string $permission) use ($permissionEnabled, $enforcePermissions, $user) {
            if (!$permissionEnabled || !$enforcePermissions) {
                return true;
            }
            return $user && $user->hasPermission($permission);
        };
        $labList = collect();
        if ($isSuperAdmin && Schema::hasTable('labs')) {
            $labList = Lab::query()->orderBy('name')->get();
        }
    @endphp
    <style>
        :root {
            --sidebar-color-start: {{ $sidebarColorStart }};
            --sidebar-color-mid: {{ $sidebarColorMid }};
            --sidebar-color-end: {{ $sidebarColorEnd }};
            --sidebar-text-color: {{ $sidebarTextColor }};
        }
    </style>
    <div class="shell">
        <aside class="sidebar" id="sidebar">
            <div class="brand">
                @if (!empty($labLogoPath))
                    <div class="brand-logo">
                        <img src="{{ $labLogoPath }}" alt="{{ $brandName }}">
                    </div>
                @else
                    <div class="brand-mark">{{ $brandInitial }}</div>
                @endif
                <div class="brand-info">
                    <div>{{ $brandName }}</div>
                    <div style="font-size:12px;opacity:0.7;">Admin Panel</div>
                </div>
            </div>
            <nav class="nav">
                @if ($can('admin.dashboard'))
                <a class="{{ request()->is('admin') ? 'active' : '' }}" href="{{ url('/admin') }}">
                    <span class="icon is-dashboard">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="8" height="8" rx="2"></rect>
                            <rect x="13" y="3" width="8" height="5" rx="2"></rect>
                            <rect x="13" y="10" width="8" height="11" rx="2"></rect>
                            <rect x="3" y="13" width="8" height="8" rx="2"></rect>
                        </svg>
                    </span>
                    <span>Dashboard</span>
                </a>
                @endif
                @if ($can('admin.dashboard'))
                <a class="{{ request()->is('admin/patient-information') ? 'active' : '' }}" href="{{ url('/admin/patient-information') }}">
                    <span class="icon is-patient">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="7" r="4"></circle>
                            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path>
                        </svg>
                    </span>
                    <span>Patient Information</span>
                </a>
                @endif
                @if ($isSuperAdmin)
                <a class="{{ request()->is('admin/labs') ? 'active' : '' }}" href="{{ url('/admin/labs') }}">
                    <span class="icon is-master">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 10l9-7 9 7"></path>
                            <path d="M5 10v10h14V10"></path>
                            <path d="M9 20v-6h6v6"></path>
                        </svg>
                    </span>
                    <span>Labs</span>
                </a>
                @endif
                @if ($can('billing.access'))
                <a class="{{ request()->is('billing') ? 'active' : '' }}" href="{{ url('/billing') }}">
                    <span class="icon is-master">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </span>
                    <span>Patient Billing</span>
                </a>
                <a class="{{ request()->is('billing/print') ? 'active' : '' }}" href="{{ url('/billing/print') }}">
                    <span class="icon is-print">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9V2h12v7"></path>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <path d="M6 14h12v8H6z"></path>
                        </svg>
                    </span>
                    <span>Print Billing</span>
                </a>
                @endif
                @if ($can('admin.dashboard'))
                <a class="{{ request()->is('admin/page/print-worksheet') ? 'active' : '' }}" href="{{ url('/admin/page/print-worksheet') }}">
                    <span class="icon is-worksheet">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <path d="M7 7h10M7 12h10M7 17h6"></path>
                        </svg>
                    </span>
                    <span>Patient Worksheet</span>
                </a>
                @endif
                @if ($can('results.entry'))
                <a class="{{ request()->is('results/entry') ? 'active' : '' }}" href="{{ url('/results/entry') }}">
                    <span class="icon is-entry">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                        </svg>
                    </span>
                    <span>Report Entry</span>
                </a>
                @endif
                @if ($can('results.validate'))
                <a class="{{ request()->is('results/validate') ? 'active' : '' }}" href="{{ url('/results/validate') }}">
                    <span class="icon is-validate">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 12l4 4 12-12"></path>
                            <path d="M4 20h16"></path>
                        </svg>
                    </span>
                    <span>Validation</span>
                </a>
                @endif
                @if ($can('results.edit'))
                <a class="{{ request()->is('results/edit') ? 'active' : '' }}" href="{{ url('/results/edit') }}">
                    <span class="icon is-edit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 17.25V21h3.75L17.8 9.94l-3.75-3.75L3 17.25z"></path>
                            <path d="M14.1 4.1l3.75 3.75"></path>
                        </svg>
                    </span>
                    <span>Edit Result</span>
                </a>
                @endif
                @if ($can('admin.dashboard'))
                <a class="{{ request()->is('reports') ? 'active' : '' }}" href="{{ url('/reports') }}">
                    <span class="icon is-summary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19V5"></path>
                            <path d="M9 19V11"></path>
                            <path d="M14 19V9"></path>
                            <path d="M19 19V3"></path>
                        </svg>
                    </span>
                    <span>Reports</span>
                </a>
                <a class="{{ request()->is('admin/stock') ? 'active' : '' }}" href="{{ url('/admin/stock') }}">
                    <span class="icon is-stock">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 7l9-4 9 4-9 4-9-4z"></path>
                            <path d="M3 17l9 4 9-4"></path>
                            <path d="M3 12l9 4 9-4"></path>
                        </svg>
                    </span>
                    <span>Stock</span>
                </a>
                <a class="{{ request()->is('admin/page/accounts') ? 'active' : '' }}" href="{{ url('/admin/page/accounts') }}">
                    <span class="icon is-accounts">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <path d="M7 8h10M7 12h6M7 16h8"></path>
                        </svg>
                    </span>
                    <span>Accounts</span>
                </a>
                <a class="{{ request()->is('admin/promo-codes') ? 'active' : '' }}" href="{{ url('/admin/promo-codes') }}">
                    <span class="icon is-accounts">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12h18"></path>
                            <path d="M7 6h10"></path>
                            <path d="M7 18h10"></path>
                        </svg>
                    </span>
                    <span>Promo Codes</span>
                </a>
                <a class="{{ request()->is('admin/page/summary') ? 'active' : '' }}" href="{{ url('/admin/page/summary') }}">
                    <span class="icon is-summary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19V5"></path>
                            <path d="M9 19V11"></path>
                            <path d="M14 19V9"></path>
                            <path d="M19 19V3"></path>
                        </svg>
                    </span>
                    <span>Summary</span>
                </a>
                @if ($isSuperAdmin)
                <a class="{{ request()->is('admin/shop') ? 'active' : '' }}" href="{{ url('/admin/shop') }}">
                    <span class="icon is-shop">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 7h18l-2 12H5L3 7z"></path>
                            <path d="M7 7V5a5 5 0 0 1 10 0v2"></path>
                        </svg>
                    </span>
                    <span>Shop</span>
                </a>
                @endif
                @endif
                @if ($can('tests.manage'))
                <a class="{{ request()->is('admin/tests') ? 'active' : '' }}" href="{{ url('/admin/tests') }}">
                    <span class="icon is-master">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M7 3h10v4H7z"></path>
                            <path d="M5 7h14v14H5z"></path>
                        </svg>
                    </span>
                    <span>Testmaster</span>
                </a>
                <a class="{{ request()->is('admin/packages') ? 'active' : '' }}" href="{{ url('/admin/packages') }}">
                    <span class="icon is-master">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <path d="M7 8h10M7 12h6M7 16h4"></path>
                        </svg>
                    </span>
                    <span>Packages</span>
                </a>
                @endif
                <button class="nav-group-toggle" type="button" data-target="other-services">
                    Other Services
                    <span>▾</span>
                </button>
                <div class="nav-group-items" data-group="other-services">
                @if ($can('departments.manage'))
                <a class="{{ request()->is('admin/departments') ? 'active' : '' }}" href="{{ url('/admin/departments') }}">
                    <span class="icon is-master">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h10M4 18h12"></path>
                        </svg>
                    </span>
                    <span>Department</span>
                </a>
                @endif
                @if ($can('admin.dashboard'))
                <a class="{{ request()->is('admin/suppliers') ? 'active' : '' }}" href="{{ url('/admin/suppliers') }}">
                    <span class="icon is-master">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 7h18"></path>
                            <path d="M5 7v12h14V7"></path>
                            <path d="M7 11h10"></path>
                            <path d="M7 15h6"></path>
                        </svg>
                    </span>
                    <span>Suppliers</span>
                </a>
                @endif
                @if ($can('doctors.manage'))
                <a class="{{ request()->is('admin/doctors') ? 'active' : '' }}" href="{{ url('/admin/doctors') }}">
                    <span class="icon is-master">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M8 3h8v4H8z"></path>
                            <path d="M12 7v14"></path>
                            <path d="M8 13h8"></path>
                        </svg>
                    </span>
                    <span>Doctor</span>
                </a>
                @endif
                @if ($can('centers.manage'))
                <a class="{{ request()->is('admin/centers') ? 'active' : '' }}" href="{{ url('/admin/centers') }}">
                    <span class="icon is-master">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 10l9-7 9 7"></path>
                            <path d="M5 10v10h14V10"></path>
                        </svg>
                    </span>
                    <span>Centre</span>
                </a>
                @endif
                </div>
                @if ($can('admin.dashboard'))
                <a class="{{ request()->is('admin/settings') ? 'active' : '' }}" href="{{ url('/admin/settings') }}">
                    <span class="icon is-settings">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.7 1.7 0 0 0 .33 1.82l.04.04a2 2 0 0 1-2.83 2.83l-.04-.04a1.7 1.7 0 0 0-1.82-.33 1.7 1.7 0 0 0-1 1.54V22a2 2 0 0 1-4 0v-.06a1.7 1.7 0 0 0-1-1.54 1.7 1.7 0 0 0-1.82.33l-.04.04a2 2 0 0 1-2.83-2.83l.04-.04A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.54-1H3a2 2 0 0 1 0-4h.06a1.7 1.7 0 0 0 1.54-1 1.7 1.7 0 0 0-.33-1.82l-.04-.04a2 2 0 0 1 2.83-2.83l.04.04A1.7 1.7 0 0 0 8.9 4.6a1.7 1.7 0 0 0 1-1.54V3a2 2 0 0 1 4 0v.06a1.7 1.7 0 0 0 1 1.54 1.7 1.7 0 0 0 1.82-.33l.04-.04a2 2 0 0 1 2.83 2.83l-.04.04A1.7 1.7 0 0 0 19.4 9a1.7 1.7 0 0 0 1.54 1H21a2 2 0 0 1 0 4h-.06a1.7 1.7 0 0 0-1.54 1z"></path>
                        </svg>
                    </span>
                    <span>Settings</span>
                </a>
                @endif
            </nav>
            @if ($isSuperAdmin && $labList->isNotEmpty())
                <div class="lab-list">
                    <div class="lab-title">Labs</div>
                    <div class="lab-items">
                        @foreach ($labList as $lab)
                            <div class="lab-item">{{ $lab->name }}</div>
                        @endforeach
                    </div>
                </div>
            @endif
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="content">
            <div class="topbar">
                <button class="sidebar-toggle" type="button" id="sidebarToggle" aria-label="Toggle menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                <div class="title">{{ $pageTitle ?? 'Dashboard' }}</div>
                <div class="user">
                    <span>{{ auth()->user()->name ?? 'Guest' }}</span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" style="border:none;background:transparent;color:var(--brand);cursor:pointer;">Logout</button>
                    </form>
                </div>
            </div>

            @yield('content')
        </main>
    </div>
    <script>
        (function () {
            var groupToggles = document.querySelectorAll('.nav-group-toggle');
            groupToggles.forEach(function (toggle) {
                toggle.addEventListener('click', function () {
                    var target = toggle.dataset.target;
                    var list = document.querySelector('.nav-group-items[data-group="' + target + '"]');
                    if (list) {
                        list.classList.toggle('is-hidden');
                        var caret = toggle.querySelector('span');
                        if (caret) {
                            caret.textContent = list.classList.contains('is-hidden') ? 'â–¸' : 'â–¾';
                        }
                    }
                });
            });

            var toggle = document.getElementById('sidebarToggle');
            var overlay = document.getElementById('sidebarOverlay');

            function closeSidebar() {
                document.body.classList.remove('sidebar-open');
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    document.body.classList.toggle('sidebar-open');
                });
            }
            if (overlay) {
                overlay.addEventListener('click', function () {
                    closeSidebar();
                });
            }
        })();
    </script>
</body>
</html>

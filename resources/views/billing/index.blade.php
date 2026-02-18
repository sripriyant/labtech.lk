@extends('layouts.admin')

@php
    $pageTitle = 'Patient Billing';
    $activeTab = $activeTab ?? request()->get('tab', 'create');
    $tests = $tests ?? collect();
    $serviceTests = $serviceTests ?? collect();
    $products = $products ?? collect();
    $centers = $centers ?? collect();
    $doctors = $doctors ?? collect();
    $promoCodes = $promoCodes ?? collect();
    $centersPayload = $centers->map(fn ($center) => [
        'id' => $center->id,
        'name' => $center->name,
        'code' => $center->code,
    ])->values();
@endphp

@section('content')
    <style>
        .billing-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
        }

        .billing-header {
            background: #0b6a6a;
            color: #fff;
            padding: 10px 16px;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .billing-tabs {
            display: flex;
            gap: 16px;
            padding: 10px 16px;
            border-bottom: 1px solid var(--line);
            background: #f9fbfc;
        }

        .billing-tabs .tab-btn {
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 600;
            color: #3b4a55;
            cursor: pointer;
            padding: 0;
            text-decoration: none;
        }

        .billing-tabs .tab-btn.active {
            color: #0a5a8f;
            border-bottom: 2px solid #0a5a8f;
            padding-bottom: 4px;
        }

        .billing-body {
            padding: 16px;
            display: grid;
            gap: 12px;
        }

        .grid {
            display: grid;
            gap: 10px;
            align-items: end;
        }

        .grid-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .span-2 {
            grid-column: span 2;
        }

        .span-3 {
            grid-column: span 3;
        }

        .span-4 {
            grid-column: span 4;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 14px;
        }

        .field label {
            color: #5b6b74;
            font-size: 13px;
            font-weight: 700;
        }

        .field input,
        .field select {
            height: 34px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .field.invalid label {
            color: #c01919;
        }

        .field.invalid input,
        .field.invalid select {
            border-color: #c01919;
            background: #fff5f5;
        }

        .field input[disabled] {
            background: #f1f3f6;
        }

        .field.inline {
            grid-template-columns: auto 1fr;
            align-items: center;
        }

        .field.inline label {
            margin: 0;
        }

        .section {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 12px;
            background: #fff;
        }

        .section.invalid {
            border-color: #c01919;
            box-shadow: 0 0 0 1px rgba(192, 25, 25, 0.25);
        }
        .section + .section {
            margin-top: 12px;
        }

        .section.part {
            border-left: 5px solid var(--part-color);
            background: linear-gradient(90deg, color-mix(in srgb, var(--part-color) 12%, #fff) 0%, #fff 45%);
        }

        .part-header {
            border: 1px solid color-mix(in srgb, var(--part-color) 30%, #fff);
            background: color-mix(in srgb, var(--part-color) 14%, #fff);
        }

        .part-patient {
            border: 1px solid color-mix(in srgb, var(--part-color) 30%, #fff);
            background: color-mix(in srgb, var(--part-color) 10%, #fff);
            border-radius: 12px;
            padding: 12px;
        }

        .part-patient .grid-4 {
            align-items: end;
        }

        .age-row {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }

        .age-row input[type="number"] {
            flex: 1 1 120px;
            min-width: 120px;
        }

        .age-md {
            display: none;
            grid-template-columns: repeat(2, minmax(80px, 1fr));
            gap: 6px;
            align-items: center;
        }

        .age-unit {
            min-width: 120px;
        }

        .options-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .part-payment {
            border: 1px solid color-mix(in srgb, var(--part-color) 30%, #fff);
            background: color-mix(in srgb, var(--part-color) 10%, #fff);
            border-radius: 12px;
            padding: 12px;
        }

        .part-actions {
            border: 1px solid color-mix(in srgb, var(--part-color) 30%, #fff);
            background: color-mix(in srgb, var(--part-color) 12%, #fff);
            border-radius: 12px;
            padding: 10px;
        }

        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #2d3c45;
            margin-bottom: 8px;
        }

        .section-title.part-title {
            color: var(--part-color);
        }

        .test-grid {
            display: grid;
            grid-template-columns: 1.7fr 50px 1.3fr;
            gap: 10px;
            align-items: stretch;
        }

        .test-actions {
            display: grid;
            gap: 10px;
            justify-items: center;
        }

        .btn-mini {
            width: 36px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #f4f7f9;
            cursor: pointer;
            font-weight: 700;
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
            background: #f1f6fa;
            color: #50616a;
            text-align: left;
            padding: 8px 8px;
            border-bottom: 1px solid var(--line);
        }

        tbody td {
            padding: 8px 8px;
            border-bottom: 1px solid var(--line);
        }

        .scroll-area {
            max-height: 200px;
            overflow-y: auto;
        }

        .selected-row {
            background: #e7f7e7;
        }

        .totals {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px 16px;
            margin-top: 10px;
        }

        .totals .field label,
        .payment-bar .field label {
            font-size: 12px;
            font-weight: 700;
            color: #c01919;
        }

        .price-input {
            font-weight: 700;
            color: #c01919;
            font-size: 13px;
        }

        .payment-bar {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px 16px;
            margin-top: 8px;
        }

        .controls-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: center;
            font-size: 12px;
            margin-top: 8px;
        }

        .controls-row label {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .action-row {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 10px;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            background: #f1f5f8;
            color: #32414a;
        }

        .btn.pay {
            background: #198754;
            color: #fff;
        }

        .btn.register {
            background: #c51c1c;
            color: #fff;
        }

        .btn.print {
            background: #0d6efd;
            color: #fff;
        }

        .btn.new {
            background: #e2e6ea;
            color: #2c3a41;
        }

        .btn.save {
            background: #f1f5f8;
            color: #32414a;
            border: 1px solid var(--line);
        }

        .badge-note {
            font-size: 12px;
            color: #5b6b74;
        }

        .toast {
            position: fixed;
            right: 24px;
            bottom: 24px;
            background: #1f9f61;
            color: #fff;
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 600;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.12);
            display: none;
            z-index: 9999;
        }

        .toast.show {
            display: block;
        }

        .toast.error {
            background: #c01919;
        }

        .suggestions {
            position: absolute;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            z-index: 2000;
            width: 100%;
            max-height: 220px;
            overflow-y: auto;
        }

        .suggestions button {
            display: block;
            width: 100%;
            border: none;
            background: #fff;
            text-align: left;
            padding: 8px 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .suggestions button:hover {
            background: #f1f6fa;
        }

        .hint {
            font-size: 12px;
            color: #7a8a94;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .promo-select {
            min-width: 180px;
        }

        .inline-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .payment-extra {
            display: none;
            margin-top: 6px;
        }

        .payment-extra.active {
            display: block;
        }

        .label-red {
            color: #c01919;
            font-weight: 700;
        }

        @media (max-width: 1200px) {
            .grid-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .test-grid {
                grid-template-columns: 1fr;
            }

        .totals,
        .payment-bar {
            grid-template-columns: 1fr;
        }
    }

    .section-card {
        background: #fff;
        border-radius: 20px;
        border: 1px solid #eef2f5;
        padding: 20px;
        box-shadow: 0 20px 60px rgba(15, 49, 76, 0.08);
        margin-bottom: 20px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
        gap: 12px;
    }

    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #0d3b66;
        margin: 0;
        letter-spacing: 0.06em;
    }

    .section-subtitle {
        margin: 4px 0 0;
        font-size: 12px;
        color: #5b6b74;
    }

    .required-marker {
        color: #c01919;
        margin-left: 2px;
        font-size: 14px;
    }

    .section-body {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .section-actions {
        margin-top: 12px;
    }

    .section-note {
        font-size: 12px;
        color: #5b6b74;
        font-weight: 500;
    }

    .inline-label {
        display: flex;
        gap: 6px;
        align-items: center;
        font-weight: 600;
        font-size: 14px;
    }

    .billing-error-banner {
        display: none;
        margin-bottom: 14px;
        padding: 18px 24px;
        border-radius: 12px;
        background: linear-gradient(135deg, #dc2626, #991b1b);
        color: #fff;
        font-size: 18px;
        font-weight: 700;
        text-align: center;
        letter-spacing: 0.03em;
        box-shadow: 0 16px 35px rgba(0, 0, 0, 0.25);
        transition: transform 0.2s ease, opacity 0.2s ease;
    }

    .field.invalid input,
    .field.invalid select {
        border-color: #c01919;
        outline: none;
    }

    .field .error-note {
        display: none;
        color: #c01919;
        font-size: 11px;
        margin-top: 4px;
    }

    .send-sms-toggle {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    @media (max-width: 720px) {
        .billing-header {
            font-size: 16px;
            padding: 8px 12px;
        }

        .billing-tabs {
            flex-direction: column;
            gap: 8px;
            padding: 8px 12px;
        }

        .billing-tabs .tab-btn {
            font-size: 14px;
        }

        .billing-body {
            padding: 12px;
        }

        .grid-4,
        .grid-3,
        .grid-2 {
            grid-template-columns: 1fr;
        }

        .span-2,
        .span-3,
        .span-4 {
            grid-column: span 1;
        }

        .field input,
        .field select {
            height: 40px;
            font-size: 14px;
        }

        .section,
        .part-header,
        .part-patient,
        .part-payment,
        .part-actions {
            padding: 10px;
            border-radius: 10px;
        }

        .test-grid {
            grid-template-columns: 1fr;
        }

        .test-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-start;
        }

        .btn-mini {
            width: auto;
            min-width: 36px;
        }

        .options-row,
        .controls-row,
        .action-row {
            flex-direction: column;
            align-items: stretch;
        }

        .action-row .btn {
            width: 100%;
        }

        .totals,
        .payment-bar {
            grid-template-columns: 1fr;
        }

        .table-wrap {
            border: none;
            background: transparent;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            min-width: 560px;
        }

        .scroll-area {
            max-height: 240px;
        }
    }
</style>

    <div class="ui-page">
    <div class="billing-card">
        <div class="billing-header">BILLING</div>

        <div class="billing-tabs">
            <a class="tab-btn {{ $activeTab === 'create' ? 'active' : '' }}" data-tab="create" href="{{ route('billing.index', ['tab' => 'create']) }}">LAB BILLING</a>
            @if (!empty($canClinicBilling))
            <a class="tab-btn {{ $activeTab === 'clinic' ? 'active' : '' }}" data-tab="clinic" href="{{ route('billing.index', ['tab' => 'clinic']) }}">Medical Laboratory & ClinicBILLING</a>
            @endif
        </div>

        <div class="billing-body">
            <div class="tab-pane {{ $activeTab === 'create' ? 'active' : '' }}" data-tab-pane="create" style="{{ $activeTab === 'create' ? '' : 'display:none' }}">
                <div id="billing-error-banner" class="billing-error-banner">Please complete all mandatory fields before billing.</div>
                <form id="billing-form" method="post" action="{{ route('billing.store') }}">
                    @csrf
                    <input type="hidden" name="center_id" id="center_id" value="">
                    <input type="hidden" name="patient_id" id="patient_id" value="">
                    <input type="hidden" name="name" id="patient_name" value="">
                    <input type="hidden" name="tests_payload" id="tests_payload" value="">
                    <input type="hidden" name="billing_mode" value="test">
                    <input type="hidden" name="print_invoice" id="print_invoice" value="0">
                    <input type="hidden" name="auto_print" id="auto_print" value="0">
                    <input type="hidden" name="show_invoice" id="show_invoice" value="0">
                    <input type="hidden" name="show_invoice_no" id="show_invoice_no" value="0">
                    <input type="hidden" name="send_billing_sms" id="send_billing_sms" value="0">

                    @if ($errors->any())
                        <div class="section" style="border:1px solid #f5c2c7;background:#fff3f3;color:#8b1e2d;">
                            <strong>Billing form has errors:</strong>
                            <ul style="margin:6px 0 0 16px;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('sms_error'))
                        <div class="section" style="border:1px solid #f5c2c7;background:#fff3f3;color:#8b1e2d;">
                            {{ session('sms_error') }}
                        </div>
                    @endif
                    @if (session('sms_status'))
                        <div class="section" style="border:1px solid #c9e6d4;background:#eefaf2;color:#0f7a47;">
                            {{ session('sms_status') }}
                        </div>
                    @endif

                    <div class="ui-card patient-section">
                        <div class="ui-card-title">Patient details</div>
                        <div class="ui-card-sub">Fields marked * are required</div>
                        <div class="section-body">
                            <div class="ui-form-grid" style="grid-template-columns: 2fr 120px 120px; gap: 10px; margin-top: 4px;">
                                <div class="field span-2" style="position: relative;">
                                    <label class="ui-label">Find patient (NIC / Phone / Name / UHID)</label>
                                    <input type="text" id="patient_search" class="ui-input" placeholder="Type a value and click Search" autocomplete="off">
                                    <div id="patient_search_results" class="suggestions" style="display:none;"></div>
                                </div>
                                <div class="field">
                                    <label class="ui-label">&nbsp;</label>
                                    <button type="button" class="btn save" id="patient_search_btn">Search</button>
                                </div>
                                <div class="field">
                                    <label class="ui-label">&nbsp;</label>
                                    <button type="button" class="btn new" id="patient_clear_btn">Clear</button>
                                </div>
                            </div>
                            <div class="hint">Selecting a patient fills details only; age must be entered for this billing.</div>
                            <div class="part-patient" style="--part-color:#8a4b12;margin-top:10px;">
                        <div class="ui-form-grid ui-form-grid-3">
                        <div class="field">
                            <label class="ui-label ui-required">Title</label>
                            <select id="title_select" class="ui-select" required>
                                <option value="" selected>Select</option>
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                    <option value="Dr">Dr</option>
                                    <option value="Rev">Rev</option>
                                    <option value="Son of">Son of</option>
                                    <option value="Daughter of">Daughter of</option>
                                    <option value="Baby of">Baby of</option>
                                <option value="Master">Master</option>
                                <option value="Miss">Miss</option>
                            </select>
                            <div class="error-note" id="titleError">Title is required.</div>
                        </div>
                        <div class="field">
                            <label class="ui-label ui-required">First Name</label>
                            <input type="text" id="first_name" class="ui-input" placeholder="First name" required>
                            <div class="error-note" id="firstNameError">First name is required.</div>
                        </div>
                        <div class="field">
                            <label class="ui-label">Last Name</label>
                            <input type="text" id="last_name" class="ui-input" placeholder="Last name">
                        </div>
                        </div>

                        <div class="ui-form-grid ui-form-grid-3" style="margin-top: 10px;">
                            <div class="field">
                                <label class="ui-label ui-required">Gender</label>
                                <select name="sex" id="sex" class="ui-select" required>
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <div class="error-note" id="genderError">Gender is required.</div>
                            </div>
                            <div class="field">
                                <label class="ui-label">Email</label>
                                <input type="email" name="email" id="email" class="ui-input" placeholder="Email">
                            </div>
                            <div class="field">
                                <label class="ui-label">Nationality</label>
                                <input type="text" name="nationality" id="nationality" class="ui-input" placeholder="Sri Lankan">
                            </div>
                        </div>

                        <div class="ui-form-grid ui-form-grid-3" style="margin-top: 10px;">
                            <div class="field" id="age_field">
                                <label class="ui-label">Age (<span id="age_unit_label">Y</span>)</label>
                                <div class="age-row">
                                    <input type="number" id="age_years" name="age_years" class="ui-input" min="0" placeholder="Age">
                                    <div id="age_md_inputs" class="age-md">
                                        <input type="number" id="age_months" name="age_months" class="ui-input" min="0" placeholder="Months">
                                        <input type="number" id="age_days" name="age_days" class="ui-input" min="0" max="31" placeholder="Days">
                                    </div>
                                    <select id="age_unit" name="age_unit" class="age-unit ui-select">
                                        <option value="Y">Years (Y)</option>
                                    </select>
                                </div>
                                <div class="error-note" id="ageError">Age is required.</div>
                            </div>
                            <div class="field" style="position: relative;">
                                <label class="ui-label">NIC</label>
                                <input type="text" name="nic" id="nic" class="ui-input" placeholder="NIC" autocomplete="off">
                            </div>
                            <div class="field">
                            <label class="ui-label ui-required">Tele. No</label>
                            <input type="text" name="phone" id="phone" class="ui-input" placeholder="0123456789" required>
                            <div class="error-note" id="phoneError">Phone number is required.</div>
                        </div>
                        </div>

                        <div class="ui-form-grid ui-form-grid-3" style="margin-top: 10px;">
                            <div class="field">
                                <label class="ui-label">Referral Type</label>
                                <select name="referral_type" id="referral_type" class="ui-select">
                                    <option value="">None</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="center">Center</option>
                                </select>
                            </div>
                            <div class="field">
                                <label class="ui-label">Referral</label>
                                <select name="referral_doctor_id" id="referral_doctor" class="ui-select">
                                    <option value="">Select doctor</option>
                                    @foreach ($doctors as $doctor)
                                        <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                    @endforeach
                                </select>
                                <select name="referral_center_id" id="referral_center" class="ui-select" style="display:none;margin-top:6px;">
                                    <option value="">Select center</option>
                                    @foreach ($centers as $center)
                                        <option value="{{ $center->id }}">{{ $center->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div></div>
                        </div>

                        <div class="ui-form-grid ui-form-grid-4" style="margin-top: 10px;">
                            <div class="field span-2">
                                <label class="ui-label">Send billing SMS</label>
                                <div class="ui-toggle">
                                    <input type="checkbox" id="opt_send_sms">
                                    <label for="opt_send_sms" class="ui-label" style="margin:0">Enable SMS notification</label>
                                </div>
                                <small class="section-note">Uncheck to skip notifications.</small>
                            </div>
                            <div class="field span-2">
                                <label class="ui-label">Payment mode</label>
                                <div class="ui-segment">
                                    <label>
                                        <input type="radio" name="payment_mode" value="CASH" checked>
                                        <span>Cash</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="payment_mode" value="CREDIT">
                                        <span>Credit</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="payment_mode" value="CARD">
                                        <span>Card</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="payment_mode" value="BANK">
                                        <span>Bank Transfer</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="ui-form-grid ui-form-grid-4" style="margin-top: 10px;">
                            <div class="field span-2">
                                <div class="payment-extra" id="card_extra">
                                    <div class="field">
                                        <label>Card Transaction ID</label>
                                        <input type="text" name="card_transaction_id" id="card_transaction_id">
                                    </div>
                                </div>
                                <div class="payment-extra" id="bank_extra">
                                    <div class="field">
                                        <label>Bank Slip Number</label>
                                        <input type="text" name="bank_slip_no" id="bank_slip_no">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    <div class="ui-card" id="test_section">
                        <div class="ui-card-title">Tests</div>
                        <div class="ui-billing-grid ui-billing-grid--with-actions">
                            <div>
                                <div class="field" style="position: relative;">
                                    <input type="text" id="test_search" class="ui-input" placeholder="Search by code or name" autocomplete="off">
                                    <div id="test_suggestions" class="suggestions" style="display:none;"></div>
                                </div>

                                <div class="table-wrap" style="margin-top: 8px;">
                                    <div class="scroll-area">
                                        <table class="ui-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:40px;"></th>
                                                    <th>Test ID</th>
                                                    <th>Test Description</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                            <tbody id="test_list_body">
                                                @foreach ($tests as $test)
                                                    <tr data-test-id="{{ $test->id }}"
                                                        data-test-code="{{ $test->code }}"
                                                        data-test-name="{{ $test->name }}"
                                                        data-test-price="{{ number_format($test->price ?? 0, 2, '.', '') }}"
                                                        data-test-location="{{ $test->department?->name ?? '-' }}">
                                                        <td><input type="checkbox" class="test-select"></td>
                                                        <td>{{ $test->code }}</td>
                                                        <td>
                                                            <div>{{ $test->name }}</div>
                                                            @if ($test->is_package && $test->packageItems->isNotEmpty())
                                                                <div class="hint">
                                                                    Includes:
                                                                    {{ $test->packageItems->pluck('name')->implode(', ') }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>{{ $test->department?->name ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="inline-row" style="margin-top: 8px;">
                                    <button type="button" class="btn save" id="btn-select-pathology">Select Pathology</button>
                                    <select id="service_select" class="ui-select">
                                        <option value="">Add Service</option>
                                        @foreach ($serviceTests as $service)
                                            <option value="{{ $service->id }}"
                                                    data-code="{{ $service->code }}"
                                                    data-name="{{ $service->name }}"
                                                    data-price="{{ number_format($service->price ?? 0, 2, '.', '') }}"
                                                    data-location="{{ $service->department?->name ?? '-' }}"
                                                    data-is-package="{{ $service->is_package ? '1' : '0' }}">
                                                {{ $service->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn save" id="btn-add-service">Add</button>
                                </div>
                            </div>

                            <div class="ui-transfer">
                                <button type="button" class="btn-mini" id="btn-add-tests">>></button>
                                <button type="button" class="btn-mini" id="btn-remove-tests"><<</button>
                            </div>

                            <div>
                                <div class="table-wrap">
                                    <div class="scroll-area">
                                        <table class="ui-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:40px;"></th>
                                                    <th>Test Description</th>
                                                    <th>Test Price</th>
                                                    <th>Location</th>
                                                </tr>
                                            </thead>
                                            <tbody id="selected_tests_body">
                                                <tr>
                                                    <td colspan="4">Selected tests appear here</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="part-payment" style="--part-color:#8b1d1d;margin-top:10px;">
                    <div class="ui-summary">
                    <div class="ui-form-grid ui-form-grid-3 totals-row">
                        <div class="field">
                            <label>Total Test Price</label>
                            <input type="text" id="total_price" class="price-input" value="0.00" readonly>
                        </div>
                        <div class="field">
                            <label>Commission</label>
                            <input type="text" id="commission" class="price-input" value="0.00" readonly>
                        </div>
                        <div class="field">
                            <label>Commission (Manual)</label>
                            <input type="number" step="0.01" name="manual_commission" id="manual_commission" class="price-input" value="0.00">
                        </div>
                    </div>

                    <div class="ui-form-grid ui-form-grid-3 totals-row" style="margin-top:12px;">
                        <div class="field">
                            <label>Promo Code</label>
                            <select name="promo_code_id" id="promo_code" class="promo-select">
                                <option value="">Select promo</option>
                                @foreach ($promoCodes as $promo)
                                    <option value="{{ $promo->id }}"
                                            data-type="{{ $promo->type }}"
                                            data-value="{{ $promo->value }}">
                                        {{ $promo->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Professional Charge</label>
                            <input type="text" id="professional_charge" class="price-input" value="0.00" readonly>
                        </div>
                        <div class="field">
                            <label>VAT</label>
                            <input type="text" id="vat" class="price-input" value="0.00" readonly>
                        </div>
                    </div>
                    <div class="ui-form-grid ui-form-grid-3" style="margin-top: 12px;">
                        <div class="field span-3">
                            <label>Discount Type</label>
                            <div class="inline-row">
                                <label><input type="radio" name="discount_type" value="none" checked> None</label>
                                <label><input type="radio" name="discount_type" value="central"> Central</label>
                                <label><input type="radio" name="discount_type" value="doctor"> Doctor</label>
                            </div>
                        </div>
                    </div>

                    <div class="ui-form-grid ui-form-grid-4 totals-row" style="margin-top:12px;">
                        <div class="field">
                            <label>Discount</label>
                            <input type="number" step="0.01" name="discount" id="discount" class="price-input" value="0.00">
                        </div>
                        <div class="field">
                            <label>Paying Amount</label>
                            <input type="number" step="0.01" id="paying_amount" class="price-input" value="0.00">
                        </div>
                        <div class="field">
                            <label>Balance</label>
                            <input type="text" id="balance" class="price-input" value="0.00" readonly>
                        </div>
                        <div class="field">
                            <label>Discount</label>
                            <input type="text" id="discount_value" class="price-input" value="0.00" readonly>
                        </div>
                    </div>
                    </div>

                    <div class="controls-row">
                        <span>Selected test count: <strong id="selected_count">0</strong></span>
                        <label><input type="checkbox" id="opt_auto_save"> Auto Save</label>
                        <label><input type="checkbox" id="opt_auto_print"> Print Automatically</label>
                        <label><input type="checkbox" id="opt_show_invoice"> Show Invoice</label>
                        <label><input type="checkbox" id="opt_show_invoice_no"> Show Invoice Number</label>
                    </div>
                    </div>

                    <div class="part-actions" style="--part-color:#3a4a87;margin-top:10px;">
                        <div class="action-row" style="margin-top:0;">
                            <button type="button" class="btn pay" id="btn-pay">PAY (F6)</button>
                            <button type="button" class="btn new" id="btn-new">New (F1)</button>
                            <button type="button" class="btn save" id="btn-save">Save (F4)</button>
                            <button type="button" class="btn save" id="btn-close">Close (Esc)</button>
                            <button type="button" class="btn print" id="btn-print-invoice">Print Invoice</button>
                        </div>
                    </div>
                </form>
            </div>

            @if (!empty($canClinicBilling))
            <div class="tab-pane {{ $activeTab === 'clinic' ? 'active' : '' }}" data-tab-pane="clinic" style="{{ $activeTab === 'clinic' ? '' : 'display:none' }}">
                <div id="clinic-error-banner" class="billing-error-banner">Please complete all mandatory fields before billing.</div>
                <form id="clinic-billing-form" method="post" action="{{ route('billing.store') }}">
                    @csrf
                    <input type="hidden" name="billing_mode" value="clinic">
                    <input type="hidden" name="products_payload" id="clinic_products_payload" value="">

                    <div class="ui-card">
                        <div class="ui-card-title">Medical Laboratory & Clinicpatient details</div>
                        <div class="ui-form-grid" style="grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px;">
                            <div class="field">
                                <label class="ui-label ui-required">Patient Name</label>
                                <input type="text" name="name" id="clinic_name" class="ui-input">
                            </div>
                            <div class="field">
                                <label class="ui-label ui-required">Gender</label>
                                <select name="sex" id="clinic_sex" class="ui-select">
                                    <option value="">Select</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="field">
                                <label class="ui-label ui-required">Telephone</label>
                                <input type="text" name="phone" id="clinic_phone" class="ui-input">
                            </div>
                            <div class="field">
                                <label class="ui-label ui-required">Age (Years)</label>
                                <input type="number" name="age_years" id="clinic_age_years" class="ui-input" min="0" max="130">
                                <input type="hidden" name="age_unit" value="Y">
                            </div>
                            <div class="field">
                                <label class="ui-label">NIC</label>
                                <input type="text" name="nic" id="clinic_nic" class="ui-input">
                            </div>
                            <div class="field">
                                <label class="ui-label">Email</label>
                                <input type="email" name="email" id="clinic_email" class="ui-input">
                            </div>
                            <div class="field">
                                <label class="ui-label">Nationality</label>
                                <input type="text" name="nationality" id="clinic_nationality" class="ui-input" value="Sri Lankan">
                            </div>
                        </div>
                    </div>

                    <div class="ui-card" id="clinic_product_section">
                        <div class="ui-card-title">Products</div>
                        <div class="ui-billing-grid ui-billing-grid--with-actions">
                            <div>
                                <div class="field" style="position: relative;">
                                    <input type="text" id="clinic_product_search" class="ui-input" placeholder="Search products" autocomplete="off">
                                    <div id="clinic_product_suggestions" class="suggestions" style="display:none;"></div>
                                </div>
                                <div class="table-wrap" style="margin-top: 8px;">
                                    <div class="scroll-area">
                                        <table class="ui-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:40px;"></th>
                                                    <th>Product ID</th>
                                                    <th>Product Description</th>
                                                    <th>Category</th>
                                                </tr>
                                            </thead>
                                            <tbody id="clinic_product_list_body">
                                                @foreach ($products as $product)
                                                    <tr data-product-id="{{ $product->id }}"
                                                        data-product-code="{{ 'P' . str_pad((string) $product->id, 5, '0', STR_PAD_LEFT) }}"
                                                        data-product-name="{{ $product->name }}"
                                                        data-product-price="{{ number_format($product->price ?? 0, 2, '.', '') }}"
                                                        data-product-category="{{ $product->category?->name ?? ($product->category ?? '-') }}">
                                                        <td><input type="checkbox" class="clinic-product-select"></td>
                                                        <td>{{ 'P' . str_pad((string) $product->id, 5, '0', STR_PAD_LEFT) }}</td>
                                                        <td>{{ $product->name }}</td>
                                                        <td>{{ $product->category?->name ?? ($product->category ?? '-') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="ui-transfer">
                                <button type="button" class="btn-mini" id="clinic_add_products">>></button>
                                <button type="button" class="btn-mini" id="clinic_remove_products"><<</button>
                            </div>

                            <div>
                                <div class="table-wrap">
                                    <div class="scroll-area">
                                        <table class="ui-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:40px;"></th>
                                                    <th>Product Description</th>
                                                    <th>Price</th>
                                                    <th>Category</th>
                                                </tr>
                                            </thead>
                                            <tbody id="clinic_selected_products_body">
                                                <tr>
                                                    <td colspan="4">Selected products appear here</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ui-summary">
                        <div class="ui-form-grid ui-form-grid-3">
                            <div class="field">
                                <label>Total Product Price</label>
                                <input type="text" id="clinic_total_price" class="price-input" value="0.00" readonly>
                            </div>
                            <div class="field">
                                <label>Selected product count</label>
                                <input type="text" id="clinic_selected_count" class="price-input" value="0" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="part-actions" style="--part-color:#3a4a87;margin-top:10px;">
                        <div class="action-row" style="margin-top:0;">
                            <button type="submit" class="btn pay">SAVE Medical Laboratory & ClinicBILL</button>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        (function () {
            var activeTab = '{{ $activeTab }}';
            var tabButtons = document.querySelectorAll('.tab-btn');
            var tabPanes = document.querySelectorAll('.tab-pane');

            function setTab(tab) {
                tabButtons.forEach(function (btn) {
                    btn.classList.toggle('active', btn.dataset.tab === tab);
                });
                tabPanes.forEach(function (pane) {
                    pane.classList.toggle('active', pane.dataset.tabPane === tab);
                });
            }

            tabButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setTab(btn.dataset.tab);
                });
            });

            setTab(activeTab || 'create');

            var toast = document.getElementById('toast');
            function showToast(message, isError) {
                if (!toast) {
                    return;
                }
                toast.textContent = message;
                toast.classList.toggle('error', !!isError);
                toast.classList.add('show');
                setTimeout(function () {
                    toast.classList.remove('show');
                }, 2500);
            }

            @if (session('status'))
                showToast(@json(session('status')));
            @endif
            @if (session('stock_warnings'))
                showToast(@json(implode(' ', session('stock_warnings'))), true);
            @endif

            var openInvoiceUrl = @json(session('open_invoice_url'));
            if (openInvoiceUrl) {
                setTimeout(function () {
                    window.location.href = openInvoiceUrl;
                }, 500);
            }

            var centers = @json($centersPayload);

            var centerInput = document.getElementById('center_search');
            var centerSuggestions = document.getElementById('center_suggestions');
            var centerIdInput = document.getElementById('center_id');

            function renderCenterSuggestions(items) {
                if (!centerSuggestions) {
                    return;
                }
                centerSuggestions.innerHTML = '';
                if (!items.length) {
                    centerSuggestions.style.display = 'none';
                    return;
                }
                items.forEach(function (center) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = (center.code ? center.code + ' - ' : '') + center.name;
                    btn.addEventListener('click', function () {
                        centerInput.value = btn.textContent;
                        centerIdInput.value = center.id;
                        centerSuggestions.style.display = 'none';
                    });
                    centerSuggestions.appendChild(btn);
                });
                centerSuggestions.style.display = 'block';
            }

            if (centerInput) {
                centerInput.addEventListener('input', function () {
                    var query = centerInput.value.trim().toLowerCase();
                    if (!query) {
                        renderCenterSuggestions(centers);
                        return;
                    }
                    var filtered = centers.filter(function (center) {
                        return (center.code || '').toLowerCase().indexOf(query) !== -1 ||
                            center.name.toLowerCase().indexOf(query) !== -1;
                    });
                    renderCenterSuggestions(filtered);
                });

                centerInput.addEventListener('focus', function () {
                    renderCenterSuggestions(centers);
                });

                document.addEventListener('click', function (event) {
                    if (!centerSuggestions) {
                        return;
                    }
                    if (!centerSuggestions.contains(event.target) && event.target !== centerInput) {
                        centerSuggestions.style.display = 'none';
                    }
                });
            }

            var nicInput = document.getElementById('nic');
            var firstName = document.getElementById('first_name');
            var lastName = document.getElementById('last_name');
            var ageInput = document.getElementById('age_years');
            var sexInput = document.getElementById('sex');
            var phoneInput = document.getElementById('phone');
            var emailInput = document.getElementById('email');
            var nationalityInput = document.getElementById('nationality');
            var patientNameInput = document.getElementById('patient_name');
            var patientIdInput = document.getElementById('patient_id');
            var patientSearchInput = document.getElementById('patient_search');
            var patientSearchResults = document.getElementById('patient_search_results');
            var patientSearchBtn = document.getElementById('patient_search_btn');
            var patientClearBtn = document.getElementById('patient_clear_btn');
            var titleSelect = document.getElementById('title_select');
            var ageUnitSelect = document.getElementById('age_unit');
            var ageUnitLabel = document.getElementById('age_unit_label');
            var ageMdInputs = document.getElementById('age_md_inputs');
            var ageMonthsInput = document.getElementById('age_months');
            var ageDaysInput = document.getElementById('age_days');
            var billingErrorBanner = document.getElementById('billing-error-banner');
            var ageField = document.getElementById('age_field');
            var ageError = document.getElementById('ageError');
            var testSection = document.getElementById('test_section');
            function parseNumber(value) {
                var num = parseFloat(value);
                return Number.isFinite ? (Number.isFinite(num) ? num : null) : (isNaN(num) ? null : num);
            }

            var titleOptions = [
                { value: 'Mr', label: 'Mr', sex: 'Male' },
                { value: 'Master', label: 'Master', sex: 'Male' },
                { value: 'Son of', label: 'Son of', sex: 'Male', child: true },
                { value: 'Mrs', label: 'Mrs', sex: 'Female' },
                { value: 'Miss', label: 'Miss', sex: 'Female' },
                { value: 'Daughter of', label: 'Daughter of', sex: 'Female', child: true },
                { value: 'Baby of', label: 'Baby of', sex: 'Female', child: true },
                { value: 'Dr', label: 'Dr', sex: 'Any' },
                { value: 'Rev', label: 'Rev', sex: 'Any' }
            ];

            function setNameValue() {
                var title = titleSelect ? titleSelect.value : '';
                var name = [title, firstName.value, lastName.value].filter(Boolean).join(' ').trim();
                patientNameInput.value = name;
            }

            function syncTitleOptions() {
                if (!titleSelect || !sexInput) {
                    return;
                }
                var sexVal = sexInput.value || '';
                var allowed = titleOptions.filter(function (opt) {
                    return !sexVal || opt.sex === 'Any' || opt.sex === sexVal;
                });
                var current = titleSelect.value;
                titleSelect.innerHTML = '';
                allowed.forEach(function (opt) {
                    var option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    titleSelect.appendChild(option);
                });
                var stillAllowed = allowed.some(function (opt) { return opt.value === current; });
                if (stillAllowed) {
                    titleSelect.value = current;
                }
                setNameValue();
                setAgeUnitOptions();
            }

            function markFieldInvalid(input, isInvalid) {
                if (!input) {
                    return;
                }
                var field = input.closest('.field');
                if (!field) {
                    return;
                }
                field.classList.toggle('invalid', isInvalid);
                var error = field.querySelector('.error-note');
                if (error) {
                    error.style.display = isInvalid ? 'block' : 'none';
                }
            }

            function markAgeInvalid(isInvalid) {
                if (!ageField) {
                    return;
                }
                ageField.classList.toggle('invalid', isInvalid);
                if (ageError) {
                    ageError.style.display = isInvalid ? 'block' : 'none';
                }
            }

            function hasAgeValue() {
                var unit = ageUnitSelect ? ageUnitSelect.value : 'Y';
                var yearsVal = parseNumber(ageInput && ageInput.value);
                var monthsVal = parseNumber(ageMonthsInput && ageMonthsInput.value);
                var daysVal = parseNumber(ageDaysInput && ageDaysInput.value);
                if (unit === 'Y') {
                    return yearsVal !== null;
                }
                if (unit === 'M') {
                    return monthsVal !== null;
                }
                if (unit === 'D') {
                    return daysVal !== null;
                }
                if (unit === 'MD') {
                    return monthsVal !== null || daysVal !== null;
                }
                return yearsVal !== null;
            }

            function showBillingBanner(message) {
                if (!billingErrorBanner) {
                    return;
                }
                billingErrorBanner.textContent = message || 'Please complete all highlighted fields before billing.';
                billingErrorBanner.style.display = 'block';
            }

            function hideBillingBanner() {
                if (!billingErrorBanner) {
                    return;
                }
                billingErrorBanner.style.display = 'none';
            }

            if (firstName) {
                firstName.addEventListener('input', setNameValue);
            }
            if (lastName) {
                lastName.addEventListener('input', setNameValue);
            }
            if (titleSelect) {
                titleSelect.addEventListener('change', setNameValue);
                titleSelect.addEventListener('change', setAgeUnitOptions);
            }

            [firstName, sexInput, phoneInput].forEach(function (input) {
                if (!input) {
                    return;
                }
                var eventName = input.tagName === 'SELECT' ? 'change' : 'input';
                input.addEventListener(eventName, function () {
                    markFieldInvalid(input, false);
                });
            });
            if (titleSelect) {
                titleSelect.addEventListener('change', function () {
                    markFieldInvalid(titleSelect, false);
                });
            }
            if (ageInput) {
                ageInput.addEventListener('input', function () {
                    markAgeInvalid(false);
                });
                ageInput.addEventListener('blur', function () {
                    if (!hasAgeValue()) {
                        markAgeInvalid(true);
                    }
                });
            }
            if (ageMonthsInput) {
                ageMonthsInput.addEventListener('input', function () {
                    markAgeInvalid(false);
                });
                ageMonthsInput.addEventListener('blur', function () {
                    if (!hasAgeValue()) {
                        markAgeInvalid(true);
                    }
                });
            }
            if (ageDaysInput) {
                ageDaysInput.addEventListener('input', function () {
                    markAgeInvalid(false);
                });
                ageDaysInput.addEventListener('blur', function () {
                    if (!hasAgeValue()) {
                        markAgeInvalid(true);
                    }
                });
            }

            function setAgeUnitOptions() {
                if (!ageUnitSelect) {
                    return;
                }
                var title = titleSelect ? titleSelect.value : '';
                var isChild = title === 'Son of' || title === 'Daughter of' || title === 'Baby of' || title === 'Master';
                ageUnitSelect.innerHTML = '';
                if (isChild) {
                    var optMonths = document.createElement('option');
                    optMonths.value = 'M';
                    optMonths.textContent = 'Months (M)';
                    ageUnitSelect.appendChild(optMonths);
                    var optDays = document.createElement('option');
                    optDays.value = 'D';
                    optDays.textContent = 'Days (D)';
                    ageUnitSelect.appendChild(optDays);
                    var optMd = document.createElement('option');
                    optMd.value = 'MD';
                    optMd.textContent = 'Months + Days (M+D)';
                    ageUnitSelect.appendChild(optMd);
                    ageUnitSelect.value = ageUnitSelect.value || 'M';
                } else {
                    var optYears = document.createElement('option');
                    optYears.value = 'Y';
                    optYears.textContent = 'Years (Y)';
                    ageUnitSelect.appendChild(optYears);
                    ageUnitSelect.value = 'Y';
                }
                if (ageUnitLabel) {
                    ageUnitLabel.textContent = ageUnitSelect.value;
                }
                if (ageInput) {
                    ageInput.style.display = ageUnitSelect.value === 'MD' ? 'none' : 'block';
                }
                if (ageMdInputs) {
                    ageMdInputs.style.display = ageUnitSelect.value === 'MD' ? 'grid' : 'none';
                }
            }

            if (ageUnitSelect) {
                ageUnitSelect.addEventListener('change', function () {
                    if (ageUnitLabel) {
                        ageUnitLabel.textContent = ageUnitSelect.value;
                    }
                    if (ageInput) {
                        ageInput.style.display = ageUnitSelect.value === 'MD' ? 'none' : 'block';
                    }
                    if (ageMdInputs) {
                        ageMdInputs.style.display = ageUnitSelect.value === 'MD' ? 'grid' : 'none';
                    }
                });
            }

            if (sexInput) {
                sexInput.addEventListener('change', syncTitleOptions);
            }
            syncTitleOptions();
            setAgeUnitOptions();

            function parseTitleAndName(fullName) {
                var name = (fullName || '').trim();
                var titles = ['Mr', 'Mrs', 'Miss', 'Master', 'Dr', 'Rev', 'Son of', 'Daughter of', 'Baby of'];
                var foundTitle = '';
                var rest = name;
                for (var i = 0; i < titles.length; i++) {
                    var t = titles[i];
                    if (name.toLowerCase().indexOf(t.toLowerCase() + ' ') === 0) {
                        foundTitle = t;
                        rest = name.slice(t.length).trim();
                        break;
                    }
                }
                return {
                    title: foundTitle,
                    first: rest || name,
                    last: ''
                };
            }

            function clearPatientFields() {
                if (patientIdInput) {
                    patientIdInput.value = '';
                }
                if (titleSelect) {
                    titleSelect.value = '';
                }
                if (firstName) {
                    firstName.value = '';
                }
                if (lastName) {
                    lastName.value = '';
                }
                if (sexInput) {
                    sexInput.value = '';
                }
                if (nicInput) {
                    nicInput.value = '';
                }
                if (phoneInput) {
                    phoneInput.value = '';
                }
                if (emailInput) {
                    emailInput.value = '';
                }
                if (nationalityInput) {
                    nationalityInput.value = '';
                }
                if (ageInput) {
                    ageInput.value = '';
                }
                if (ageMonthsInput) {
                    ageMonthsInput.value = '';
                }
                if (ageDaysInput) {
                    ageDaysInput.value = '';
                }
                setNameValue();
                setAgeUnitOptions();
                hideBillingBanner();
            }

            function applyPatientSelection(patient) {
                if (!patient) {
                    return;
                }
                if (patientIdInput) {
                    patientIdInput.value = patient.id || '';
                }
                if (sexInput && patient.sex) {
                    sexInput.value = patient.sex;
                }
                syncTitleOptions();
                var parsed = parseTitleAndName(patient.name || '');
                if (titleSelect) {
                    if (parsed.title) {
                        titleSelect.value = parsed.title;
                    } else if (patient.sex === 'Male') {
                        titleSelect.value = 'Mr';
                    } else if (patient.sex === 'Female') {
                        titleSelect.value = 'Mrs';
                    }
                }
                if (firstName) {
                    firstName.value = parsed.first || '';
                }
                if (lastName) {
                    lastName.value = parsed.last || '';
                }
                if (nicInput) {
                    nicInput.value = patient.nic || '';
                }
                if (phoneInput) {
                    phoneInput.value = patient.phone || '';
                }
                if (emailInput) {
                    emailInput.value = patient.email || '';
                }
                if (nationalityInput) {
                    nationalityInput.value = patient.nationality || '';
                }
                setNameValue();
                setAgeUnitOptions();
                markFieldInvalid(firstName, false);
                markFieldInvalid(sexInput, false);
                markFieldInvalid(phoneInput, false);
            }

            function renderPatientResults(items) {
                if (!patientSearchResults) {
                    return;
                }
                patientSearchResults.innerHTML = '';
                if (!items || !items.length) {
                    patientSearchResults.style.display = 'none';
                    return;
                }
                items.forEach(function (patient) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    var label = (patient.name || 'Unknown') + ' | ' +
                        (patient.uhid || '-') + ' | ' +
                        (patient.nic || '-') + ' | ' +
                        (patient.phone || '-');
                    btn.textContent = label;
                    btn.addEventListener('click', function () {
                        applyPatientSelection(patient);
                        if (patientSearchInput) {
                            patientSearchInput.value = '';
                        }
                        patientSearchResults.style.display = 'none';
                    });
                    patientSearchResults.appendChild(btn);
                });
                patientSearchResults.style.display = 'block';
            }

            function runPatientSearch() {
                if (!patientSearchInput) {
                    return;
                }
                var query = patientSearchInput.value.trim();
                if (!query) {
                    renderPatientResults([]);
                    return;
                }
                fetch('{{ route('billing.patients') }}?q=' + encodeURIComponent(query))
                    .then(function (resp) { return resp.json(); })
                    .then(renderPatientResults)
                    .catch(function () {
                        renderPatientResults([]);
                    });
            }

            if (patientSearchBtn) {
                patientSearchBtn.addEventListener('click', runPatientSearch);
            }
            if (patientSearchInput) {
                patientSearchInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        runPatientSearch();
                    }
                });
            }
            if (patientClearBtn) {
                patientClearBtn.addEventListener('click', function () {
                    clearPatientFields();
                    if (patientSearchResults) {
                        patientSearchResults.style.display = 'none';
                    }
                    if (patientSearchInput) {
                        patientSearchInput.value = '';
                    }
                });
            }

            document.addEventListener('click', function (event) {
                if (!patientSearchResults) {
                    return;
                }
                if (!patientSearchResults.contains(event.target) && event.target !== patientSearchInput) {
                    patientSearchResults.style.display = 'none';
                }
            });

            var referralType = document.getElementById('referral_type');
            var referralDoctor = document.getElementById('referral_doctor');
            var referralCenter = document.getElementById('referral_center');

            function updateReferral() {
                var val = referralType.value;
                referralDoctor.style.display = val === 'doctor' ? 'block' : 'none';
                referralCenter.style.display = val === 'center' ? 'block' : 'none';
            }

            if (referralType) {
                referralType.addEventListener('change', updateReferral);
                updateReferral();
            }

            var paymentRadios = document.querySelectorAll('input[name=\"payment_mode\"]');
            var cardExtra = document.getElementById('card_extra');
            var bankExtra = document.getElementById('bank_extra');

            function updatePaymentExtras() {
                var mode = document.querySelector('input[name=\"payment_mode\"]:checked');
                var value = mode ? mode.value : 'CASH';
                cardExtra.classList.toggle('active', value === 'CARD');
                bankExtra.classList.toggle('active', value === 'BANK');
            }

            paymentRadios.forEach(function (radio) {
                radio.addEventListener('change', updatePaymentExtras);
            });
            updatePaymentExtras();

            var selectedTests = new Map();
            var selectedBody = document.getElementById('selected_tests_body');
            var totalPriceInput = document.getElementById('total_price');
            var discountInput = document.getElementById('discount');
            var discountValueInput = document.getElementById('discount_value');
            var payingInput = document.getElementById('paying_amount');
            var balanceInput = document.getElementById('balance');
            var promoSelect = document.getElementById('promo_code');
            var selectedCount = document.getElementById('selected_count');
            var testsPayload = document.getElementById('tests_payload');

            function renderSelectedTests() {
                selectedBody.innerHTML = '';
                if (selectedTests.size === 0) {
                    var emptyRow = document.createElement('tr');
                    var td = document.createElement('td');
                    td.colSpan = 4;
                    td.textContent = 'Selected tests appear here';
                    emptyRow.appendChild(td);
                    selectedBody.appendChild(emptyRow);
                } else {
                    selectedTests.forEach(function (test) {
                        var row = document.createElement('tr');
                        row.className = 'selected-row';
                        row.dataset.testId = test.id;
                        row.innerHTML = '<td><input type="checkbox" class="selected-remove"></td>' +
                            '<td>' + test.name + '</td>' +
                            '<td>' + Number(test.price || 0).toFixed(2) + '</td>' +
                            '<td>' + test.location + '</td>';
                        selectedBody.appendChild(row);
                    });
                }
                if (testSection && selectedTests.size > 0) {
                    testSection.classList.remove('invalid');
                }
                selectedCount.textContent = String(selectedTests.size);
                var payload = [];
                selectedTests.forEach(function (test) {
                    payload.push(test.id);
                });
                testsPayload.value = JSON.stringify(payload);
                syncHiddenTests(payload);
                calculateTotals();
            }

            function syncHiddenTests(payload) {
                var form = document.getElementById('billing-form');
                form.querySelectorAll('input[name="tests[]"]').forEach(function (input) {
                    input.remove();
                });
                payload.forEach(function (id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'tests[]';
                    input.value = id;
                    form.appendChild(input);
                });
            }

                function validateBasicInfo() {
                    setNameValue();
                var firstNameVal = (firstName && firstName.value || '').trim();
                var sexVal = (sexInput && sexInput.value || '').trim();
                var hasErrors = false;

                if (!firstNameVal) {
                    markFieldInvalid(firstName, true);
                    hasErrors = true;
                } else {
                    markFieldInvalid(firstName, false);
                }
                if (!sexVal) {
                    markFieldInvalid(sexInput, true);
                    showToast('Please select gender.', true);
                    hasErrors = true;
                    } else {
                        markFieldInvalid(sexInput, false);
                    }
                    if (!hasAgeValue()) {
                        markAgeInvalid(true);
                        hasErrors = true;
                        showToast('Please enter age.', true);
                    } else {
                        markAgeInvalid(false);
                    }
                    if (selectedTests.size === 0) {
                        if (testSection) {
                            testSection.classList.add('invalid');
                        }
                        showToast('Please select at least one test.', true);
                        hasErrors = true;
                    } else if (testSection) {
                        testSection.classList.remove('invalid');
                    }
                    if (hasErrors) {
                        showBillingBanner('Complete Name, Age, Gender & Test selection.');
                    } else {
                        hideBillingBanner();
                    }
                    return !hasErrors;
                }

            function calculateTotals() {
                var total = 0;
                selectedTests.forEach(function (test) {
                    total += Number(test.price || 0);
                });
                totalPriceInput.value = total.toFixed(2);

                var patientDiscount = Number(discountInput.value || 0);
                if (patientDiscount < 0) {
                    patientDiscount = 0;
                }

                var promoDiscount = 0;
                if (promoSelect && promoSelect.value) {
                    var option = promoSelect.options[promoSelect.selectedIndex];
                    var promoType = option.dataset.type || '';
                    var promoValue = Number(option.dataset.value || 0);
                    if (promoType === 'PERCENT') {
                        promoDiscount = total * (promoValue / 100);
                    } else if (promoType === 'FLAT') {
                        promoDiscount = promoValue;
                    }
                }
                promoDiscount = Math.min(promoDiscount, total);

                var net = Math.max(total - patientDiscount - promoDiscount, 0);
                discountValueInput.value = (patientDiscount + promoDiscount).toFixed(2);

                var paying = Number(payingInput.value || 0);
                if (paying < 0) {
                    paying = 0;
                }
                balanceInput.value = Math.max(net - paying, 0).toFixed(2);
            }

            document.getElementById('btn-add-tests').addEventListener('click', function () {
                document.querySelectorAll('#test_list_body .test-select:checked').forEach(function (checkbox) {
                    var row = checkbox.closest('tr');
                    var id = row.dataset.testId;
                    if (!selectedTests.has(id)) {
                        selectedTests.set(id, {
                            id: id,
                            code: row.dataset.testCode,
                            name: row.dataset.testName,
                            price: row.dataset.testPrice,
                            location: row.dataset.testLocation
                        });
                    }
                    checkbox.checked = false;
                });
                renderSelectedTests();
            });

            document.getElementById('btn-remove-tests').addEventListener('click', function () {
                document.querySelectorAll('#selected_tests_body .selected-remove:checked').forEach(function (checkbox) {
                    var row = checkbox.closest('tr');
                    selectedTests.delete(row.dataset.testId);
                });
                renderSelectedTests();
            });

            document.getElementById('btn-add-service').addEventListener('click', function () {
                var select = document.getElementById('service_select');
                var option = select.options[select.selectedIndex];
                if (!option || !option.value) {
                    return;
                }
                var id = option.value;
                if (!selectedTests.has(id)) {
                    selectedTests.set(id, {
                        id: id,
                        code: option.dataset.code,
                        name: option.dataset.name,
                        price: option.dataset.price,
                        location: option.dataset.location
                    });
                    renderSelectedTests();
                }
                select.value = '';
            });


            if (discountInput) {
                discountInput.addEventListener('input', calculateTotals);
            }
            if (payingInput) {
                payingInput.addEventListener('input', calculateTotals);
            }
            if (promoSelect) {
                promoSelect.addEventListener('change', calculateTotals);
            }

            renderSelectedTests();

            var testSearchInput = document.getElementById('test_search');
            var testSuggestions = document.getElementById('test_suggestions');
            var testTimer = null;

            function renderTestSuggestions(items) {
                testSuggestions.innerHTML = '';
                if (!items.length) {
                    testSuggestions.style.display = 'none';
                    return;
                }
                items.forEach(function (test) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = test.code + ' - ' + test.name;
                    btn.addEventListener('click', function () {
                        if (!selectedTests.has(String(test.id))) {
                            selectedTests.set(String(test.id), {
                                id: String(test.id),
                                code: test.code,
                                name: test.name,
                                price: test.price,
                                location: test.location
                            });
                            renderSelectedTests();
                        }
                        testSearchInput.value = '';
                        testSuggestions.style.display = 'none';
                    });
                    testSuggestions.appendChild(btn);
                });
                testSuggestions.style.display = 'block';
            }

            if (testSearchInput) {
                testSearchInput.addEventListener('input', function () {
                    var query = testSearchInput.value.trim();
                    if (testTimer) {
                        clearTimeout(testTimer);
                    }
                    testTimer = setTimeout(function () {
                        fetch('{{ route('billing.tests') }}?q=' + encodeURIComponent(query))
                            .then(function (resp) { return resp.json(); })
                            .then(renderTestSuggestions)
                            .catch(function () {});
                    }, 200);
                });
            }

            document.addEventListener('click', function (event) {
                if (testSuggestions && !testSuggestions.contains(event.target) && event.target !== testSearchInput) {
                    testSuggestions.style.display = 'none';
                }
            });

            var payBtn = document.getElementById('btn-pay');
            if (payBtn) {
                payBtn.addEventListener('click', function () {
                    if (!validateBasicInfo()) {
                        return;
                    }
                    setNameValue();
                    document.getElementById('billing-form').submit();
                });
            }

            var printBtn = document.getElementById('btn-print-invoice');
            if (printBtn) {
                printBtn.addEventListener('click', function () {
                    if (!validateBasicInfo()) {
                        return;
                    }
                    document.getElementById('print_invoice').value = '1';
                    setNameValue();
                    document.getElementById('billing-form').submit();
                });
            }

            var newBtn = document.getElementById('btn-new');
            if (newBtn) {
                newBtn.addEventListener('click', function () {
                    window.location.href = '{{ route('billing.index') }}';
                });
            }

            var closeBtn = document.getElementById('btn-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    window.location.href = '{{ route('admin.dashboard') }}';
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'F6') {
                    event.preventDefault();
                    payBtn.click();
                }
            });

            var optAutoPrint = document.getElementById('opt_auto_print');
            var optShowInvoice = document.getElementById('opt_show_invoice');
            var optShowInvoiceNo = document.getElementById('opt_show_invoice_no');
            var optAutoSave = document.getElementById('opt_auto_save');
            var optSendSms = document.getElementById('opt_send_sms');

            function syncOptions() {
                document.getElementById('auto_print').value = optAutoPrint.checked ? '1' : '0';
                document.getElementById('show_invoice').value = optShowInvoice.checked ? '1' : '0';
                document.getElementById('show_invoice_no').value = optShowInvoiceNo.checked ? '1' : '0';
                document.getElementById('send_billing_sms').value = optSendSms && optSendSms.checked ? '1' : '0';
                localStorage.setItem('billing_auto_print', optAutoPrint.checked ? '1' : '0');
                localStorage.setItem('billing_show_invoice', optShowInvoice.checked ? '1' : '0');
                localStorage.setItem('billing_show_invoice_no', optShowInvoiceNo.checked ? '1' : '0');
                localStorage.setItem('billing_send_sms', optSendSms && optSendSms.checked ? '1' : '0');
            }

            function loadOptions() {
                optAutoPrint.checked = localStorage.getItem('billing_auto_print') === '1';
                optShowInvoice.checked = localStorage.getItem('billing_show_invoice') === '1';
                optShowInvoiceNo.checked = localStorage.getItem('billing_show_invoice_no') === '1';
                if (optSendSms) {
                    var storedSms = localStorage.getItem('billing_send_sms');
                    optSendSms.checked = storedSms === null ? true : storedSms === '1';
                }
                syncOptions();
            }

            var clinicForm = document.getElementById('clinic-billing-form');
            var clinicSelected = new Map();
            var clinicSelectedBody = document.getElementById('clinic_selected_products_body');
            var clinicTotalPrice = document.getElementById('clinic_total_price');
            var clinicSelectedCount = document.getElementById('clinic_selected_count');
            var clinicPayload = document.getElementById('clinic_products_payload');

            function renderClinicSelected() {
                if (!clinicSelectedBody) {
                    return;
                }
                clinicSelectedBody.innerHTML = '';
                if (clinicSelected.size === 0) {
                    var emptyRow = document.createElement('tr');
                    var td = document.createElement('td');
                    td.colSpan = 4;
                    td.textContent = 'Selected products appear here';
                    emptyRow.appendChild(td);
                    clinicSelectedBody.appendChild(emptyRow);
                } else {
                    clinicSelected.forEach(function (product) {
                        var row = document.createElement('tr');
                        row.className = 'selected-row';
                        row.dataset.productId = product.id;
                        row.innerHTML = '<td><input type="checkbox" class="clinic-remove"></td>' +
                            '<td>' + product.name + '</td>' +
                            '<td>' + Number(product.price || 0).toFixed(2) + '</td>' +
                            '<td>' + product.category + '</td>';
                        clinicSelectedBody.appendChild(row);
                    });
                }
                var payload = [];
                clinicSelected.forEach(function (product) {
                    payload.push(product.id);
                });
                if (clinicPayload) {
                    clinicPayload.value = JSON.stringify(payload);
                }
                syncClinicHidden(payload);
                calculateClinicTotals();
            }

            function syncClinicHidden(payload) {
                if (!clinicForm) {
                    return;
                }
                clinicForm.querySelectorAll('input[name="products[]"]').forEach(function (input) {
                    input.remove();
                });
                payload.forEach(function (id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'products[]';
                    input.value = id;
                    clinicForm.appendChild(input);
                });
            }

            function calculateClinicTotals() {
                var total = 0;
                clinicSelected.forEach(function (product) {
                    total += Number(product.price || 0);
                });
                if (clinicTotalPrice) {
                    clinicTotalPrice.value = total.toFixed(2);
                }
                if (clinicSelectedCount) {
                    clinicSelectedCount.value = String(clinicSelected.size);
                }
            }

            var clinicAddBtn = document.getElementById('clinic_add_products');
            var clinicRemoveBtn = document.getElementById('clinic_remove_products');

            if (clinicAddBtn) {
                clinicAddBtn.addEventListener('click', function () {
                    document.querySelectorAll('#clinic_product_list_body .clinic-product-select:checked').forEach(function (checkbox) {
                        var row = checkbox.closest('tr');
                        var id = row.dataset.productId;
                        if (!clinicSelected.has(id)) {
                            clinicSelected.set(id, {
                                id: id,
                                code: row.dataset.productCode,
                                name: row.dataset.productName,
                                price: row.dataset.productPrice,
                                category: row.dataset.productCategory
                            });
                        }
                        checkbox.checked = false;
                    });
                    renderClinicSelected();
                });
            }

            if (clinicRemoveBtn) {
                clinicRemoveBtn.addEventListener('click', function () {
                    document.querySelectorAll('#clinic_selected_products_body .clinic-remove:checked').forEach(function (checkbox) {
                        var row = checkbox.closest('tr');
                        clinicSelected.delete(row.dataset.productId);
                    });
                    renderClinicSelected();
                });
            }

            var clinicSearchInput = document.getElementById('clinic_product_search');
            var clinicSuggestions = document.getElementById('clinic_product_suggestions');
            var clinicTimer = null;

            function renderClinicSuggestions(items) {
                if (!clinicSuggestions) {
                    return;
                }
                clinicSuggestions.innerHTML = '';
                if (!items.length) {
                    clinicSuggestions.style.display = 'none';
                    return;
                }
                items.forEach(function (product) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = product.code + ' - ' + product.name;
                    btn.addEventListener('click', function () {
                        if (!clinicSelected.has(String(product.id))) {
                            clinicSelected.set(String(product.id), {
                                id: String(product.id),
                                code: product.code,
                                name: product.name,
                                price: product.price,
                                category: product.location
                            });
                            renderClinicSelected();
                        }
                        clinicSearchInput.value = '';
                        clinicSuggestions.style.display = 'none';
                    });
                    clinicSuggestions.appendChild(btn);
                });
                clinicSuggestions.style.display = 'block';
            }

            if (clinicSearchInput) {
                clinicSearchInput.addEventListener('input', function () {
                    var query = clinicSearchInput.value.trim();
                    if (clinicTimer) {
                        clearTimeout(clinicTimer);
                    }
                    clinicTimer = setTimeout(function () {
                        fetch('{{ route('billing.products') }}?q=' + encodeURIComponent(query))
                            .then(function (resp) { return resp.json(); })
                            .then(renderClinicSuggestions)
                            .catch(function () {});
                    }, 200);
                });
            }

            document.addEventListener('click', function (event) {
                if (clinicSuggestions && !clinicSuggestions.contains(event.target) && event.target !== clinicSearchInput) {
                    clinicSuggestions.style.display = 'none';
                }
            });

            if (clinicForm) {
                clinicForm.addEventListener('submit', function (event) {
                    var nameVal = (document.getElementById('clinic_name') || {}).value || '';
                    var sexVal = (document.getElementById('clinic_sex') || {}).value || '';
                    var phoneVal = (document.getElementById('clinic_phone') || {}).value || '';
                    var ageVal = (document.getElementById('clinic_age_years') || {}).value || '';
                    var hasErrors = false;

                    if (!nameVal.trim()) {
                        hasErrors = true;
                    }
                    if (!sexVal.trim()) {
                        hasErrors = true;
                    }
                    if (!phoneVal.trim()) {
                        hasErrors = true;
                    }
                    if (!ageVal.trim()) {
                        hasErrors = true;
                    }
                    if (clinicSelected.size === 0) {
                        hasErrors = true;
                    }
                    if (hasErrors) {
                        event.preventDefault();
                        showToast('Complete Name, Age, Gender, Phone & Product selection.', true);
                    }
                });
            }

            if (optAutoPrint && optShowInvoice && optShowInvoiceNo) {
                optAutoPrint.addEventListener('change', syncOptions);
                optShowInvoice.addEventListener('change', syncOptions);
                optShowInvoiceNo.addEventListener('change', syncOptions);
                if (optSendSms) {
                    optSendSms.addEventListener('change', syncOptions);
                }
                loadOptions();
            }
        })();
    </script>
    </div>
@endsection

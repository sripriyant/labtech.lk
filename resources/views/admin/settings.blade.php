@extends('layouts.admin')

@php
    $pageTitle = 'Settings';
    $defaultPermissionIds = array_filter(explode(',', $settings['user_default_permissions'] ?? ''));
    $defaultPermissionIds = array_map('trim', $defaultPermissionIds);
    if (empty($defaultPermissionIds)) {
        $fallbackNames = [
            'admin.dashboard',
            'banners.manage',
            'billing.access',
            'billing.create',
            'centers.manage',
            'departments.manage',
            'doctors.manage',
            'results.approve',
            'results.edit',
            'results.entry',
            'results.validate',
            'tests.manage',
        ];
        $defaultPermissionIds = $permissions
            ->whereIn('name', $fallbackNames)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }
@endphp

@section('content')
    <style>
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px;
        }

        .tab-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .tab-btn {
            border: 1px solid var(--line);
            background: #ffffff;
            padding: 8px 14px;
            border-radius: 999px;
            cursor: pointer;
            font-weight: 600;
            color: var(--muted);
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            background: #0a6fb3;
            border-color: #0a6fb3;
            color: #fff;
        }

        .tab-hidden {
            display: none !important;
        }

        .card-block {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 16px;
        }

        .card-block.section {
            border-top: 4px solid var(--section-color);
        }

        .card-block h3 {
            margin: 0 0 8px;
            font-size: 14px;
        }

        .card-block.section h3 {
            color: var(--section-color);
        }

        .card-block.report-settings { --section-color: #0a6fb3; }
        .card-block.homepage-settings { --section-color: #7b4db3; }
        .card-block.billing-settings { --section-color: #0a6f9b; }
        .card-block.website-header-footer { --section-color: #0b6b43; }
        .card-block.website-body { --section-color: #b36b00; }
        .card-block.website-colors { --section-color: #0b7a6f; }
        .card-block.website-contact { --section-color: #0b4d6b; }
        .card-block.user-management { --section-color: #b00020; }
        .card-block.user-access { --section-color: #6b21a8; }
        .card-block.comm-settings { --section-color: #0f766e; }

        .lab-context-row {
            display: flex;
            gap: 12px;
            align-items: baseline;
            margin-bottom: 14px;
        }

        .lab-context-row select {
            border-radius: 12px;
            border: 1px solid var(--line);
            padding: 8px 12px;
            font-size: 14px;
            background: #fff;
            min-width: 220px;
        }

        .lab-context-row .lab-context-label {
            font-size: 12px;
            color: var(--muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .preview-box {
            border: 1px dashed var(--line);
            border-radius: 10px;
            padding: 10px;
            background: #fbfdff;
            font-size: 12px;
        }

        .preview-box .report-preview {
            font-family: "Times New Roman", Times, serif;
            color: #101820;
        }

        .preview-box .report-preview .header {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            align-items: start;
            gap: 10px;
            font-size: 11px;
        }

        .preview-box .report-preview .center-title h1 {
            font-size: 16px;
            margin: 2px 0;
            letter-spacing: 0.04em;
        }

        .preview-box .report-preview .confidential {
            color: #b00020;
            font-weight: 700;
            font-size: 11px;
            text-align: right;
        }

        .preview-box .report-preview img {
            max-height: 60px;
        }

        .preview-box .report-preview table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .preview-box .report-preview thead th {
            border-bottom: 1px solid #cfd9df;
            padding: 6px 4px;
            text-align: left;
            font-weight: 700;
        }

        .preview-box .report-preview tbody td {
            padding: 4px;
            vertical-align: top;
        }

        .preview-box .report-preview .divider {
            border-top: 1px solid #cfd9df;
            margin: 10px 0;
        }

        .preview-box .report-preview .footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            font-size: 11px;
        }

        .preview-box .report-preview .signature {
            text-align: right;
        }

        .preview-box .report-preview .signature-line {
            margin-top: 18px;
            border-top: 1px solid #333;
            width: 140px;
            margin-left: auto;
        }

        .preview-box .report-preview .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 24px;
            font-size: 12px;
            margin-top: 10px;
        }

        .preview-box .report-preview .info-block {
            display: grid;
            gap: 4px;
        }

        .preview-box .report-preview .info-row {
            display: grid;
            grid-template-columns: 130px 14px 1fr;
            gap: 6px;
        }

        .preview-box .report-preview .info-row strong {
            font-weight: 700;
        }

        .field {
            display: grid;
            gap: 6px;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .field-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .logo-preview {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 6px;
        }

        .logo-preview img {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: contain;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .logo-preview label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--muted);
        }

        .color-field {
            display: grid;
            grid-template-columns: 90px 1fr;
            gap: 8px;
            align-items: center;
        }

        .color-field input[type="color"] {
            width: 100%;
            height: 40px;
            padding: 0;
            border-radius: 8px;
            border: 1px solid var(--line);
        }

        .color-field input[type="text"] {
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--line);
            padding: 0 10px;
            font-size: 12px;
        }

        .radio-row {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
            font-size: 12px;
            color: var(--muted);
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

        .actions {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .section-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
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

        .btn.secondary {
            background: #f1f5f8;
            color: var(--muted);
            border: 1px solid var(--line);
            text-decoration: none;
        }

        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            background: #0b6b43;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 12px;
            box-shadow: 0 12px 30px rgba(15, 26, 33, 0.2);
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity 0.2s ease, transform 0.2s ease;
            z-index: 999;
        }

        .toast.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .toast .toast-section {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-size: 10px;
            opacity: 0.85;
        }

        @media (max-width: 1100px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    @if (session('status'))
        @php
            $toastSection = session('settings_section') ?? 'report';
            $toastLabels = [
                'report' => ['label' => 'Report', 'message' => 'Report Settings Saved'],
                'billing' => ['label' => 'Billing', 'message' => 'Billing Settings Saved'],
                'homepage' => ['label' => 'Homepage', 'message' => 'Homepage Settings Saved'],
                'website' => ['label' => 'Website', 'message' => 'Website Settings Saved'],
                'comm' => ['label' => 'Communication', 'message' => 'Communication Settings Saved'],
                'user' => ['label' => 'User', 'message' => 'User Settings Saved'],
                'all' => ['label' => 'All', 'message' => 'All Settings Saved'],
            ];
            $toastLabel = $toastLabels[$toastSection]['label'] ?? 'Settings';
            $toastMessage = $toastLabels[$toastSection]['message'] ?? 'Settings Saved';
        @endphp
        <div class="toast is-visible" role="status" data-section="{{ $toastSection }}">
            <div class="toast-section">{{ $toastLabel }}</div>
            <div>{{ $toastMessage }}</div>
        </div>
    @endif

    @if (!empty($isSuperAdmin) && $isSuperAdmin && !empty($labsEnabled) && $labs->isNotEmpty())
        <div class="lab-context-row">
            <div class="lab-context-label">
                Working on
            </div>
            <form method="get" action="{{ route('settings.index') }}" style="margin:0;">
                <select name="lab" onchange="this.form.submit()">
                    <option value="" {{ $selectedLabId === null ? 'selected' : '' }}>Global defaults</option>
                    @foreach ($labs as $lab)
                        <option value="{{ $lab->id }}" {{ $selectedLabId === $lab->id ? 'selected' : '' }}>
                            {{ $lab->name }}
                        </option>
                    @endforeach
                </select>
                <noscript><button class="btn secondary btn-sm" type="submit">Switch</button></noscript>
            </form>
        </div>
    @endif

    <form method="post" action="{{ route('settings.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="settings_section" id="settings_section" value="">
        <input type="hidden" name="settings_lab_id" value="{{ $selectedLabId ?? '' }}">
        <div class="tab-bar" id="settings_tabs">
            <button type="button" class="tab-btn active" data-tab="report">Report</button>
            <button type="button" class="tab-btn" data-tab="billing">Billing</button>
            @if ($isSuperAdmin)
                @if (!empty($isSuperAdmin) && $isSuperAdmin)
                    <button type="button" class="tab-btn" data-tab="homepage">Homepage</button>
                @endif
                <button type="button" class="tab-btn" data-tab="website">Website</button>
            @endif
            <button type="button" class="tab-btn" data-tab="comm">Communication</button>
            <button type="button" class="tab-btn" data-tab="user">User</button>
        </div>
        <div class="settings-grid">
            <div class="card-block section report-settings" data-tab-pane="report">
                <h3>Lab Report Header</h3>
                <div class="field">
                    <label>Lab Name</label>
                    <input name="lab_name" type="text" value="{{ $settings['lab_name'] ?? '' }}" placeholder="Himalaya Diagnostics">
                </div>
                <div class="field">
                    <label>Lab Name Color</label>
                    <div class="color-field">
                        <input class="color-input" name="lab_name_color" type="color" value="{{ $settings['lab_name_color'] ?? '#0b5a77' }}">
                        <input class="color-value" type="text" value="{{ $settings['lab_name_color'] ?? '#0b5a77' }}">
                    </div>
                </div>
                <div class="field">
                    <label>Lab Logo <span style="font-size:11px;color:var(--muted);">(appears in left sidebar)</span></label>
                    <div class="field-group">
                        <input name="lab_logo_path" type="text" value="{{ $settings['lab_logo_path'] ?? '' }}" placeholder="/storage/uploads/lab-logos/logo.png">
                        <input name="lab_logo_file" type="file" accept=".png,.jpg,.jpeg">
                    </div>
                    @if (!empty($settings['lab_logo_path']))
                        <div class="logo-preview">
                            <img src="{{ $settings['lab_logo_path'] }}" alt="Lab logo">
                            <label>
                                <input type="checkbox" name="lab_logo_clear" value="1">
                                Remove logo
                            </label>
                        </div>
                    @endif
                </div>
                <div class="field">
                    <label>Sidebar Gradient (per lab)</label>
                    <div class="field-group">
                        <div style="display:grid;gap:4px;">
                            <span style="font-size:11px;color:var(--muted);">Start</span>
                            <input class="color-input" name="sidebar_color_start" type="color" value="{{ $settings['sidebar_color_start'] ?? '#08b9f3' }}">
                            <input class="color-value" type="text" value="{{ $settings['sidebar_color_start'] ?? '#08b9f3' }}">
                        </div>
                        <div style="display:grid;gap:4px;">
                            <span style="font-size:11px;color:var(--muted);">Middle</span>
                            <input class="color-input" name="sidebar_color_mid" type="color" value="{{ $settings['sidebar_color_mid'] ?? '#039ad7' }}">
                            <input class="color-value" type="text" value="{{ $settings['sidebar_color_mid'] ?? '#039ad7' }}">
                        </div>
                        <div style="display:grid;gap:4px;">
                            <span style="font-size:11px;color:var(--muted);">End</span>
                            <input class="color-input" name="sidebar_color_end" type="color" value="{{ $settings['sidebar_color_end'] ?? '#015770' }}">
                            <input class="color-value" type="text" value="{{ $settings['sidebar_color_end'] ?? '#015770' }}">
                        </div>
                        <div style="display:grid;gap:4px;">
                            <span style="font-size:11px;color:var(--muted);">Text</span>
                            <input class="color-input" name="sidebar_text_color" type="color" value="{{ $settings['sidebar_text_color'] ?? '#e6f1f5' }}">
                            <input class="color-value" type="text" value="{{ $settings['sidebar_text_color'] ?? '#e6f1f5' }}">
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label>Test Title Color</label>
                    <div class="color-field">
                        <input class="color-input" name="report_test_title_color" type="color" value="{{ $settings['report_test_title_color'] ?? '#b00020' }}">
                        <input class="color-value" type="text" value="{{ $settings['report_test_title_color'] ?? '#b00020' }}">
                    </div>
                </div>
                <div class="field">
                    <label>Header HTML</label>
                    <textarea id="report_header_html" name="report_header_html" rows="8">{{ $settings['report_header_html'] ?? '' }}</textarea>
                </div>
                <div class="field">
                    <label>Header Mode (only one option can be active)</label>
                    <div class="radio-row">
                        <label>
                            <input type="radio" name="report_header_mode" value="html" {{ ($settings['report_header_mode'] ?? 'html') === 'html' ? 'checked' : '' }}>
                            Use Header HTML
                        </label>
                        <label>
                            <input type="radio" name="report_header_mode" value="image" {{ ($settings['report_header_mode'] ?? 'html') === 'image' ? 'checked' : '' }}>
                            Use Uploaded Header Image
                        </label>
                    </div>
                </div>
                <div class="field">
                    <label>Upload Full Report Sheet Background (PDF/PNG/JPG)</label>
                    <input name="report_background_file" type="file" accept=".png,.jpg,.jpeg,.pdf">
                    <div style="font-size:11px;color:var(--muted);">Use a full A4 background. PDF is for screen view; PNG/JPG is required to appear in downloaded PDFs.</div>
                </div>
                @if (!empty($settings['report_background_path']))
                    @php
                        $backgroundPath = $settings['report_background_path'];
                        $backgroundIsPdf = str_ends_with(strtolower($backgroundPath), '.pdf');
                    @endphp
                    <div class="field">
                        <label>Report Background Preview</label>
                        @if ($backgroundIsPdf)
                            <object data="{{ route('reports.asset', ['type' => 'background', 'v' => md5($backgroundPath)]) }}" type="application/pdf" style="width:100%;height:220px;border:1px solid var(--line);border-radius:8px;"></object>
                        @else
                            <img src="{{ route('reports.asset', ['type' => 'background', 'v' => md5($backgroundPath)]) }}" alt="Background Preview" style="width:100%;height:auto;border:1px solid var(--line);border-radius:8px;background:#fff;">
                        @endif
                    </div>
                    <div class="field">
                        <label>
                            <input type="checkbox" name="report_background_clear" value="1">
                            Clear Report Background
                        </label>
                    </div>
                @endif
                <div class="field">
                    <label>Header Preview</label>
                    <div id="report_header_preview" class="preview-box">
                        <div class="report-preview">
                            @php
                                $headerMode = $settings['report_header_mode'] ?? 'html';
                                $headerPath = $settings['report_header_image_path'] ?? '';
                                $headerIsPdf = $headerPath && str_ends_with(strtolower($headerPath), '.pdf');
                            @endphp
                        @if ($headerMode === 'image' && !empty($headerPath) && !$headerIsPdf)
                            <img src="{{ route('reports.asset', ['type' => 'header', 'v' => md5($headerPath)]) }}" alt="Header Preview" style="width:100%;height:auto;object-fit:contain;">
                            @elseif ($headerMode === 'image' && !empty($headerPath) && $headerIsPdf)
                                <div style="padding:6px;border:1px dashed var(--line);border-radius:8px;background:#fff;">PDF header selected</div>
                            @else
                                {!! $settings['report_header_html'] ?? '' !!}
                            @endif
                        </div>
                        <div class="report-preview">
                            <div class="info-grid">
                                <div class="info-block">
                                    <div class="info-row"><div>Patient Name</div><div>:</div><div><strong>MR S. SAMPLE</strong></div></div>
                                    <div class="info-row"><div>Location</div><div>:</div><div>Main Lab</div></div>
                                    <div class="info-row"><div>Specimen No</div><div>:</div><div>SP-000123</div></div>
                                    <div class="info-row"><div>UHID</div><div>:</div><div>UH-000001</div></div>
                                    <div class="info-row"><div>Testing Unit</div><div>:</div><div>HAEMATOLOGY</div></div>
                                </div>
                                <div class="info-block">
                                    <div class="info-row"><div>Age / Gender</div><div>:</div><div>24 / M</div></div>
                                    <div class="info-row"><div>Invoice Date</div><div>:</div><div>2026-01-10 Time : 09:11</div></div>
                                    <div class="info-row"><div>Specimen Type</div><div>:</div><div>BLOOD</div></div>
                                    <div class="info-row"><div>Specimen Collect Date</div><div>:</div><div>2026-01-10 Time : 09:21:02</div></div>
                                    <div class="info-row"><div>Referred By</div><div>:</div><div>DR. SAMPLE</div></div>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="test-title" style="text-align:center;font-weight:700;font-size:12px;margin-bottom:8px;text-transform:uppercase;">
                                SAMPLE REPORT
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Laboratory Investigation</th>
                                        <th>Result</th>
                                        <th>Unit</th>
                                        <th>Reference Interval</th>
                                        <th>Result Interpretation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>HB</td>
                                        <td>15.2</td>
                                        <td>g/dL</td>
                                        <td>13 - 17</td>
                                        <td>NORMAL</td>
                                    </tr>
                                    <tr>
                                        <td>WBC</td>
                                        <td>6.1</td>
                                        <td>x10^3/mm3</td>
                                        <td>4.5 - 11.0</td>
                                        <td>NORMAL</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="subhead">Comment :</td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="height:40px;">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label>Footer Doctors List (5 lines)</label>
                    <div style="display:grid;gap:6px;">
                        <input name="report_footer_doctor_line1" type="text" value="{{ $settings['report_footer_doctor_line1'] ?? '' }}" placeholder="Dr. ...">
                        <input name="report_footer_doctor_line2" type="text" value="{{ $settings['report_footer_doctor_line2'] ?? '' }}" placeholder="Dr. ...">
                        <input name="report_footer_doctor_line3" type="text" value="{{ $settings['report_footer_doctor_line3'] ?? '' }}" placeholder="Dr. ...">
                        <input name="report_footer_doctor_line4" type="text" value="{{ $settings['report_footer_doctor_line4'] ?? '' }}" placeholder="Dr. ...">
                        <input name="report_footer_doctor_line5" type="text" value="{{ $settings['report_footer_doctor_line5'] ?? '' }}" placeholder="Dr. ...">
                    </div>
                </div>
                <div class="field">
                    <label>Footer Contact Row</label>
                    <div style="display:grid;gap:6px;">
                        <input name="report_footer_address" type="text" value="{{ $settings['report_footer_address'] ?? '' }}" placeholder="Address">
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px;">
                            <input name="report_footer_phone_t" type="text" value="{{ $settings['report_footer_phone_t'] ?? '' }}" placeholder="T +94 ...">
                            <input name="report_footer_phone_f" type="text" value="{{ $settings['report_footer_phone_f'] ?? '' }}" placeholder="F +94 ...">
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:6px;">
                            <input name="report_footer_email" type="text" value="{{ $settings['report_footer_email'] ?? '' }}" placeholder="E example@email.com">
                            <input name="report_footer_website" type="text" value="{{ $settings['report_footer_website'] ?? '' }}" placeholder="W example.com">
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label>MLT Name</label>
                    <input name="report_mlt_name" type="text" value="{{ $settings['report_mlt_name'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Signature Image Path</label>
                    <input name="report_signature_path" type="text" value="{{ $settings['report_signature_path'] ?? '' }}" placeholder="/uploads/report/signature.png">
                </div>
                <div class="field">
                    <label>Upload Signature Image (PNG/JPG)</label>
                    <input name="report_signature_file" type="file" accept=".png,.jpg,.jpeg">
                </div>
                @if (!empty($settings['report_signature_path']))
                    <div class="field">
                        <label>Signature Preview</label>
                        <img src="{{ route('reports.asset', ['type' => 'signature', 'v' => md5($settings['report_signature_path'])]) }}" alt="Signature Preview" style="max-height:80px;max-width:240px;border:1px solid var(--line);border-radius:6px;padding:6px;background:#fff;">
                    </div>
                    <div class="field">
                        <label>
                            <input type="checkbox" name="report_signature_clear" value="1">
                            Clear Signature
                        </label>
                    </div>
                @endif
                <div class="section-actions">
                    <button class="btn section-save" type="submit" data-section="report">Save</button>
                </div>
                @if (!empty($isSuperAdmin) && $isSuperAdmin && !empty($labsEnabled) && $labsEnabled)
                    <div class="section-actions" style="margin-top:12px;">
                        <label for="report_copy_labs" style="display:block;margin-bottom:6px;">Copy Report Settings to Labs</label>
                        <select id="report_copy_labs" name="lab_ids[]" form="report-copy-form" multiple required style="min-height:120px;max-width:420px;">
                            @foreach ($labs as $lab)
                                <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                            @endforeach
                        </select>
                        <div style="margin-top:10px;">
                            <button class="btn secondary" type="submit" form="report-copy-form">Copy to Labs</button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card-block section billing-settings" data-tab-pane="billing">
                <h3>Billing Header & Footer</h3>
                <div class="field">
                    <label>Lab Name</label>
                    <input name="billing_lab_name" type="text" value="{{ $settings['billing_lab_name'] ?? ($settings['lab_name'] ?? '') }}" placeholder="Lab Name">
                </div>
                <div class="field">
                    <label>Contact</label>
                    <input name="billing_lab_contact" type="text" value="{{ $settings['billing_lab_contact'] ?? '' }}" placeholder="+94 ...">
                </div>
                <div class="field">
                    <label>Fax</label>
                    <input name="billing_lab_fax" type="text" value="{{ $settings['billing_lab_fax'] ?? '' }}" placeholder="+94 ...">
                </div>
                <div class="field">
                    <label>Email</label>
                    <input name="billing_lab_email" type="email" value="{{ $settings['billing_lab_email'] ?? '' }}" placeholder="example@lab.com">
                </div>
                <div class="field">
                    <label>Website</label>
                    <input name="billing_lab_web" type="text" value="{{ $settings['billing_lab_web'] ?? '' }}" placeholder="www.lab.com">
                </div>
                <div class="field">
                    <label>Address</label>
                    <input name="billing_lab_address" type="text" value="{{ $settings['billing_lab_address'] ?? '' }}" placeholder="Address">
                </div>
                <div class="field">
                    <label>Upload Billing Header (PNG/JPG/PDF)</label>
                    <input name="billing_header_image_file" type="file" accept=".png,.jpg,.jpeg,.pdf">
                </div>
                @if (!empty($settings['billing_header_image_path']))
                    @php
                        $billingHeaderPath = $settings['billing_header_image_path'];
                        $billingHeaderIsPdf = str_ends_with(strtolower($billingHeaderPath), '.pdf');
                    @endphp
                    <div class="field">
                        <label>Billing Header Preview</label>
                        @if ($billingHeaderIsPdf)
                            <object data="{{ route('reports.asset', ['type' => 'billing_header', 'v' => md5($billingHeaderPath)]) }}" type="application/pdf" style="width:100%;height:180px;border:1px solid var(--line);border-radius:8px;"></object>
                        @else
                            <img src="{{ route('reports.asset', ['type' => 'billing_header', 'v' => md5($billingHeaderPath)]) }}" alt="Billing Header Preview" style="width:100%;max-height:180px;object-fit:contain;border:1px solid var(--line);border-radius:8px;background:#fff;">
                        @endif
                    </div>
                    <div class="field">
                        <label>
                            <input type="checkbox" name="billing_header_image_clear" value="1">
                            Clear Billing Header
                        </label>
                    </div>
                @endif
                <div class="field">
                    <label>Upload Billing Footer (PNG/JPG/PDF)</label>
                    <input name="billing_footer_image_file" type="file" accept=".png,.jpg,.jpeg,.pdf">
                </div>
                @if (!empty($settings['billing_footer_image_path']))
                    @php
                        $billingFooterPath = $settings['billing_footer_image_path'];
                        $billingFooterIsPdf = str_ends_with(strtolower($billingFooterPath), '.pdf');
                    @endphp
                    <div class="field">
                        <label>Billing Footer Preview</label>
                        @if ($billingFooterIsPdf)
                            <object data="{{ route('reports.asset', ['type' => 'billing_footer', 'v' => md5($billingFooterPath)]) }}" type="application/pdf" style="width:100%;height:180px;border:1px solid var(--line);border-radius:8px;"></object>
                        @else
                            <img src="{{ route('reports.asset', ['type' => 'billing_footer', 'v' => md5($billingFooterPath)]) }}" alt="Billing Footer Preview" style="width:100%;max-height:180px;object-fit:contain;border:1px solid var(--line);border-radius:8px;background:#fff;">
                        @endif
                    </div>
                    <div class="field">
                        <label>
                            <input type="checkbox" name="billing_footer_image_clear" value="1">
                            Clear Billing Footer
                        </label>
                    </div>
                @endif
                <div class="section-actions">
                    <button class="btn section-save" type="submit" data-section="billing">Save</button>
                </div>
            </div>

            @if ($isSuperAdmin)
                <div class="card-block section homepage-settings" data-tab-pane="homepage">
                    <h3>Homepage Settings</h3>
                    <div class="field">
                        <label>Lab Name Size (px)</label>
                        <input name="homepage_lab_name_size" type="number" min="16" max="48" value="{{ $settings['homepage_lab_name_size'] ?? 22 }}">
                    </div>
                    <div class="section-actions">
                        <button class="btn section-save" type="submit" data-section="homepage">Save</button>
                    </div>
                </div>

                <div class="card-block section website-header-footer" data-tab-pane="website">
                    <h3>Website Header & Footer</h3>
                    <div class="field">
                        <label>Homepage Header HTML</label>
                        <textarea name="website_header_html" rows="6">{{ $settings['website_header_html'] ?? '' }}</textarea>
                    </div>
                    <div class="field">
                        <label>Homepage Footer HTML</label>
                        <textarea name="website_footer_html" rows="6">{{ $settings['website_footer_html'] ?? '' }}</textarea>
                    </div>
                    <div class="section-actions">
                        <button class="btn section-save" type="submit" data-section="website">Save</button>
                    </div>
                </div>

                <div class="card-block section website-body" data-tab-pane="website">
                    <h3>Website Body</h3>
                    <div class="field">
                        <label>Body HTML</label>
                        <textarea name="website_body_html" rows="10">{{ $settings['website_body_html'] ?? '' }}</textarea>
                    </div>
                    <div class="section-actions">
                        <button class="btn section-save" type="submit" data-section="website">Save</button>
                    </div>
                </div>

                <div class="card-block section website-colors" data-tab-pane="website">
                    <h3>Website Color Tone</h3>
                    <div class="field">
                        <label>Primary Color (hex)</label>
                        <div class="color-field">
                            <input class="color-input" name="website_color_primary" type="color" value="{{ $settings['website_color_primary'] ?? '#0b5a77' }}">
                            <input class="color-value" type="text" value="{{ $settings['website_color_primary'] ?? '#0b5a77' }}">
                        </div>
                    </div>
                    <div class="field">
                        <label>Secondary Color (hex)</label>
                        <div class="color-field">
                            <input class="color-input" name="website_color_secondary" type="color" value="{{ $settings['website_color_secondary'] ?? '#0b7a6f' }}">
                            <input class="color-value" type="text" value="{{ $settings['website_color_secondary'] ?? '#0b7a6f' }}">
                        </div>
                    </div>
                    <div class="section-actions">
                        <button class="btn section-save" type="submit" data-section="website">Save</button>
                    </div>
                </div>

                <div class="card-block section website-contact" data-tab-pane="website">
                    <h3>Contact & Map</h3>
                    <div class="field">
                        <label>Homepage Map Embed URL</label>
                        <input name="homepage_map_embed" type="text" value="{{ $settings['homepage_map_embed'] ?? '' }}" placeholder="https://www.google.com/maps/embed?...">
                    </div>
                    <div class="field">
                        <label>Homepage Map Label</label>
                        <input name="homepage_map_label" type="text" value="{{ $settings['homepage_map_label'] ?? '' }}" placeholder="Main Laboratory, Colombo">
                    </div>
                    <div class="field">
                        <label>Newsletter Email Placeholder</label>
                        <input name="website_email_placeholder" type="text" value="{{ $settings['website_email_placeholder'] ?? '' }}" placeholder="info@suyamvaram.lk">
                    </div>
                    <div class="section-actions">
                        <button class="btn section-save" type="submit" data-section="website">Save</button>
                    </div>
                </div>
            @endif

            <div class="card-block section comm-settings" data-tab-pane="comm">
                <h3>Email Credentials</h3>
                <div class="field">
                    <label>Email Username</label>
                    <input name="email_username" type="text" value="{{ $settings['email_username'] ?? '' }}" placeholder="info@lab.com">
                </div>
                <div class="field">
                    <label>Email Password</label>
                    <input name="email_password" type="password" value="" placeholder="Leave blank to keep current">
                    @if (!empty($settings['email_password']))
                        <div style="font-size:11px;color:var(--muted);">Password saved. Leave blank to keep.</div>
                    @endif
                </div>
                <div class="section-actions">
                    <button class="btn section-save" type="submit" data-section="comm">Save</button>
                </div>
            </div>

            <div class="card-block section comm-settings" data-tab-pane="comm">
                <h3>SMS Settings</h3>
                <p style="font-size:11px;color:var(--muted);margin-bottom:8px;">Each lab can supply its own gateway credentials, and super admins can also set lab-specific defaults. Values are stored per lab (lab_id), so you can keep them unique for Check Life Laboratory or any other center.</p>
                <div class="field">
                    <label>SMS API (HTTP) Endpoint (Gateway URL)</label>
                    <input name="sms_gateway_url" type="text" value="{{ $settings['sms_gateway_url'] ?? '' }}" placeholder="https://sms-provider/api/send">
                </div>
                <div class="field">
                    <label>OAuth 2.0 API Endpoint</label>
                    <input name="sms_oauth_endpoint" type="text" value="{{ $settings['sms_oauth_endpoint'] ?? '' }}" placeholder="https://auth.text.lk/oauth/token">
                </div>
                <div class="field">
                    <label>API Token (Bearer)</label>
                    <input name="sms_api_token" type="text" value="{{ $settings['sms_api_token'] ?? '' }}" placeholder="Bearer token">
                </div>
                <div class="field">
                    <label>SMS API Key</label>
                    <input name="sms_api_key" type="text" value="{{ $settings['sms_api_key'] ?? '' }}" placeholder="Optional API key">
                </div>
                <div class="field">
                    <label>SMS Sender ID</label>
                    <input name="sms_sender_id" type="text" value="{{ $settings['sms_sender_id'] ?? '' }}" placeholder="e.g. 0756510060">
                    <div style="font-size:11px;color:var(--muted);">Provide the numeric or alphanumeric sender ID your SMS provider assigned to this lab (Check Life Laboratory’s number is 0756510060, but each lab can save its own).</div>
                </div>
                <div class="field">
                    <label>Default SMS Template</label>
                    <textarea name="sms_template_billing" rows="4" placeholder="Dear {patient_name}, {lab_name} invoice {invoice_no} amount {amount}.">{{ $settings['sms_template_billing'] ?? 'Dear {patient_name}, {lab_name} invoice {invoice_no} amount {amount}.' }}</textarea>
                    <div style="font-size:11px;color:var(--muted);">Billing and report-ready messages share this template by default; report SMS will fall back to it when no dedicated template exists. Placeholders: {patient_name}, {lab_name}, {specimen_no}, {invoice_no}, {amount}, {report_link}.</div>
                </div>
                <div class="section-actions">
                    <button class="btn section-save" type="submit" data-section="comm">Save</button>
                </div>
            </div>

            <div class="card-block section comm-settings" data-tab-pane="comm">
                <h3>WhatsApp & Social</h3>
                <div class="field">
                    <label>WhatsApp Number</label>
                    <input name="whatsapp_number" type="text" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="+94XXXXXXXXX">
                </div>
                <div class="field">
                    <label>Facebook URL</label>
                    <input name="social_facebook" type="text" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/yourpage">
                </div>
                <div class="field">
                    <label>Instagram URL</label>
                    <input name="social_instagram" type="text" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/yourpage">
                </div>
                <div class="field">
                    <label>YouTube URL</label>
                    <input name="social_youtube" type="text" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/yourchannel">
                </div>
                <div class="field">
                    <label>LinkedIn URL</label>
                    <input name="social_linkedin" type="text" value="{{ $settings['social_linkedin'] ?? '' }}" placeholder="https://linkedin.com/company/yourpage">
                </div>
                <div class="field">
                    <label>X (Twitter) URL</label>
                    <input name="social_x" type="text" value="{{ $settings['social_x'] ?? '' }}" placeholder="https://x.com/yourhandle">
                </div>
                <div class="section-actions">
                    <button class="btn section-save" type="submit" data-section="comm">Save</button>
                </div>
            </div>

            <div class="card-block section user-access" data-tab-pane="user">
                <h3>Access Control</h3>
                <div class="field">
                    <label>Default Permissions for New Users</label>
                    <select name="user_default_permissions[]" multiple size="6">
                        @foreach ($permissions as $permission)
                            <option value="{{ $permission->id }}" {{ in_array((string) $permission->id, $defaultPermissionIds, true) ? 'selected' : '' }}>
                                {{ $permission->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>
                        <input type="checkbox" name="user_enforce_permissions" value="1" {{ ($settings['user_enforce_permissions'] ?? '0') === '1' ? 'checked' : '' }}>
                        Hide menu items when the user lacks permission
                    </label>
                </div>
                <div class="field">
                    <label>
                        <input type="checkbox" name="allow_results_edit_non_admin" value="1" {{ ($settings['allow_results_edit_non_admin'] ?? '1') === '1' ? 'checked' : '' }}>
                        Allow non-admin users to access Edit Results
                    </label>
                </div>
                <div class="section-actions">
                    <button class="btn section-save" type="submit" data-section="user">Save</button>
                </div>
            </div>

        </div>

        <div class="actions">
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a class="btn secondary" href="{{ url('/') }}" target="_blank">Preview Website</a>
                <a class="btn secondary" href="{{ url('/reports/latest') }}" target="_blank">Preview Report</a>
            </div>
            <button class="btn section-save" type="submit" data-section="all">Save Settings</button>
        </div>
    </form>
    @if (!empty($isSuperAdmin) && $isSuperAdmin && !empty($labsEnabled) && $labsEnabled)
        <form id="report-copy-form" method="post" action="{{ route('settings.report.copy') }}">
            @csrf
        </form>
    @endif

    <div class="card-block section user-management" style="margin-top:16px;" data-tab-pane="user">
        <h3>User Management</h3>
        @if ($errors->any())
            <div style="background:#fff3f3;border:1px solid #f5c2c7;color:#8b1e2d;padding:10px;border-radius:8px;font-size:12px;margin-bottom:12px;">
                <strong>Could not create user:</strong>
                <ul style="margin:6px 0 0 16px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @php
            $preferredRoleNames = ['Admin', 'Accountant', 'Receptionist', 'Phelobotomist'];
            $rolesByName = $roles->keyBy('name');
            $preferredRoles = collect($preferredRoleNames)
                ->map(fn ($name) => $rolesByName->get($name))
                ->filter();
            $extraRoles = $roles->filter(fn ($role) => !in_array($role->name, $preferredRoleNames, true));
            $orderedRoles = $preferredRoles->concat($extraRoles);
            $assignableRoles = $isSuperAdmin
                ? $orderedRoles
                : $orderedRoles->filter(fn ($role) => !in_array($role->name, ['Admin', 'Super Admin'], true));
            $adminRoleId = $rolesByName->get('Admin')?->id;
        @endphp
        <div class="field">
            <label>Create User</label>
        </div>
        <form method="post" action="{{ route('users.store') }}">
            @csrf
            <div class="field">
                <label>Name</label>
                <input name="name" type="text" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input name="email" type="email" required placeholder="unique@email.com">
            </div>
            <div class="field">
                <label>Password</label>
                <input name="password" type="password" required>
            </div>
            @if ($isSuperAdmin && $labsEnabled)
                <div class="field">
                    <label>Assign to Existing Lab</label>
                    <select name="lab_id">
                        <option value="">Select lab</option>
                        @foreach ($labs as $lab)
                            <option value="{{ $lab->id }}">{{ $lab->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Or Create New Lab</label>
                    <input name="lab_name" type="text" placeholder="Lab name">
                </div>
                <div class="field">
                    <label>Lab Code Prefix (optional)</label>
                    <input name="lab_code_prefix" type="text" placeholder="HIM">
                </div>
            @endif
            <div class="field">
                <label>Role</label>
                <select name="roles[]" required>
                    <option value="">Select role</option>
                    @foreach ($assignableRoles as $role)
                        <option value="{{ $role->id }}" {{ $adminRoleId && $role->id === $adminRoleId ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
                <div style="font-size:11px;color:var(--muted);">Default role is Admin for faster setup.</div>
            </div>
            <div class="field">
                <label>Permissions</label>
                <label style="display:flex;gap:6px;align-items:center;margin-bottom:6px;">
                    <input type="checkbox" id="create_user_show_permissions">
                    <span>Show advanced permissions</span>
                </label>
                <div id="create_user_permissions" style="display:none;grid-template-columns:repeat(2,minmax(140px,1fr));gap:6px;">
                    @foreach ($permissions as $permission)
                        <label style="display:flex;gap:6px;align-items:center;">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                {{ in_array((string) $permission->id, $defaultPermissionIds, true) ? 'checked' : '' }}>
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
                <div style="font-size:11px;color:var(--muted);">Leave advanced permissions hidden to use defaults.</div>
            </div>
            <button class="btn" type="submit">Add User</button>
        </form>

        <div class="field" style="margin-top:16px;">
            <label>Existing Users</label>
        </div>
        <div style="border:1px solid var(--line);border-radius:10px;overflow:auto;max-height:360px;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line);">Name</th>
                        <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line);">Email</th>
                        @if ($isSuperAdmin)
                            <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line);">Lab</th>
                        @endif
                        <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line);">Role</th>
                        <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line);">Permissions</th>
                        <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line);">Password</th>
                        <th style="text-align:left;padding:10px;border-bottom:1px solid var(--line);">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $canDelete = $isSuperAdmin || (auth()->id() !== $user->id && $user->created_by === auth()->id());
                        @endphp
                        <tr>
                            <td style="padding:10px;border-bottom:1px solid var(--line);">{{ $user->name }}</td>
                            <td style="padding:10px;border-bottom:1px solid var(--line);">{{ $user->email }}</td>
                            @if ($isSuperAdmin)
                                <td style="padding:10px;border-bottom:1px solid var(--line);">
                                    <select name="lab_id" form="update-user-{{ $user->id }}" style="min-width:160px;">
                                        <option value="">No lab</option>
                                        @foreach ($labs as $lab)
                                            <option value="{{ $lab->id }}" {{ $user->lab_id === $lab->id ? 'selected' : '' }}>
                                                {{ $lab->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                            @endif
                            <td style="padding:10px;border-bottom:1px solid var(--line);">
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <input class="role-filter" type="text" placeholder="Search roles...">
                                    <button class="btn secondary role-filter-clear" type="button">Clear</button>
                                </div>
                                <select class="role-select" name="roles[]" multiple size="4" form="update-user-{{ $user->id }}">
                                    @foreach ($assignableRoles as $role)
                                        <option value="{{ $role->id }}" {{ $user->roles->contains('id', $role->id) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="padding:10px;border-bottom:1px solid var(--line);">
                                <div style="display:grid;grid-template-columns:repeat(2,minmax(120px,1fr));gap:6px;">
                                    @foreach ($permissions as $permission)
                                        <label style="display:flex;gap:6px;align-items:center;">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" form="update-user-{{ $user->id }}"
                                                {{ $user->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                                            <span>{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </td>
                            <td style="padding:10px;border-bottom:1px solid var(--line);">
                                <input name="password" type="password" placeholder="New password" form="update-user-{{ $user->id }}">
                            </td>
                            <td style="padding:10px;border-bottom:1px solid var(--line);">
                                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                    <button class="btn" type="submit" form="update-user-{{ $user->id }}">Save</button>
                                    @if ($canDelete)
                                        <button class="btn secondary" type="submit" form="delete-user-{{ $user->id }}">Delete</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        <form method="post" action="{{ route('users.update', $user) }}" id="update-user-{{ $user->id }}">
                            @csrf
                        </form>
                        @if ($canDelete)
                            <form method="post" action="{{ route('users.destroy', $user) }}" id="delete-user-{{ $user->id }}" onsubmit="return confirm('Delete this user?');">
                                @csrf
                                @method('delete')
                            </form>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" style="padding:10px;">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            var toast = document.querySelector('.toast');
            if (toast) {
                setTimeout(function () {
                    toast.classList.remove('is-visible');
                }, 2500);
            }

            var headerInput = document.getElementById('report_header_html');
            var headerPreview = document.getElementById('report_header_preview');

            function bindPreview(input, preview) {
                if (!input || !preview) {
                    return;
                }
                input.addEventListener('input', function () {
                    preview.innerHTML = '<div class="report-preview">' + (input.value || '') + '</div>';
                });
            }

            bindPreview(headerInput, headerPreview);

            function bindRoleFilter(input, select) {
                if (!input || !select) {
                    return;
                }
                var options = Array.from(select.options).map(function (opt) {
                    return { value: opt.value, text: opt.text, selected: opt.selected };
                });
                select.addEventListener('change', function () {
                    Array.from(select.options).forEach(function (opt) {
                        var stored = options.find(function (item) { return item.value === opt.value; });
                        if (stored) {
                            stored.selected = opt.selected;
                        }
                    });
                });

                input.addEventListener('input', function () {
                    var term = (input.value || '').trim().toLowerCase();
                    select.innerHTML = '';
                    options.forEach(function (opt) {
                        if (!term || opt.text.toLowerCase().includes(term)) {
                            var option = document.createElement('option');
                            option.value = opt.value;
                            option.textContent = opt.text;
                            option.selected = opt.selected;
                            select.appendChild(option);
                        }
                    });
                });
            }

            document.querySelectorAll('.color-field').forEach(function (field) {
                var colorInput = field.querySelector('.color-input');
                var textInput = field.querySelector('.color-value');
                if (!colorInput || !textInput) {
                    return;
                }
                colorInput.addEventListener('input', function () {
                    textInput.value = colorInput.value;
                });
                textInput.addEventListener('input', function () {
                    var value = textInput.value.trim();
                    if (value.startsWith('#') && (value.length === 4 || value.length === 7)) {
                        colorInput.value = value;
                    }
                });
            });

            var filters = document.querySelectorAll('.role-filter');
            filters.forEach(function (filter) {
                var select = filter.nextElementSibling;
                if (select && select.classList.contains('role-select')) {
                    bindRoleFilter(filter, select);
                }
            });

            var clears = document.querySelectorAll('.role-filter-clear');
            clears.forEach(function (button) {
                button.addEventListener('click', function () {
                    var wrapper = button.parentElement;
                    var input = wrapper ? wrapper.querySelector('.role-filter') : null;
                    var select = wrapper ? wrapper.nextElementSibling : null;
                    if (input) {
                        input.value = '';
                        input.dispatchEvent(new Event('input'));
                    }
                });
            });

            var tabButtons = document.querySelectorAll('#settings_tabs .tab-btn');
            var tabPanes = document.querySelectorAll('[data-tab-pane]');
            var sectionInput = document.getElementById('settings_section');
            var sectionButtons = document.querySelectorAll('.section-save');
            var createPermToggle = document.getElementById('create_user_show_permissions');
            var createPermPanel = document.getElementById('create_user_permissions');
            function setTab(tab) {
                tabButtons.forEach(function (btn) {
                    btn.classList.toggle('active', btn.dataset.tab === tab);
                });
                tabPanes.forEach(function (pane) {
                    var show = pane.dataset.tabPane === tab;
                    pane.classList.toggle('tab-hidden', !show);
                });
            }

            tabButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setTab(btn.dataset.tab);
                });
            });

            if (sectionButtons.length && sectionInput) {
                sectionButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        sectionInput.value = button.dataset.section || 'report';
                    });
                });
            }

            if (createPermToggle && createPermPanel) {
                createPermToggle.addEventListener('change', function () {
                    createPermPanel.style.display = createPermToggle.checked ? 'grid' : 'none';
                });
            }

            var toast = document.querySelector('.toast');
            if (toast && toast.dataset.section) {
                var section = toast.dataset.section;
                if (section !== 'all') {
                    setTab(section);
                }
            } else {
                setTab('report');
            }
        })();
    </script>
@endsection

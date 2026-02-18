<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratory Report</title>
    <style>
        :root {
            --ink: #101820;
            --muted: #5b6b74;
            --line: #cfd9df;
            --accent: #0b5a77;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: #f3f6f8;
            font-family: "Times New Roman", Times, serif;
            color: var(--ink);
        }

        .report-actions {
            width: 794px;
            margin: 16px auto 0;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .report-actions button,
        .report-actions a {
            border: 1px solid var(--line);
            background: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            color: var(--ink);
            text-decoration: none;
            cursor: pointer;
        }

        .report-actions a.primary {
            background: #0a6fb3;
            color: #fff;
            border-color: #0a6fb3;
        }

        .report-actions a.whatsapp {
            background: #1fa855;
            color: #fff;
            border-color: #1fa855;
        }

        .page {
            width: 794px;
            min-height: 1123px;
            margin: 24px auto;
            background: #fff;
            padding: 32px 36px 96px;
            border: 1px solid var(--line);
            position: relative;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .page.with-header-image {
            padding-top: 0;
            margin-top: 0;
            border: none;
        }

        .page.with-report-background {
            padding-top: 0;
            margin-top: 0;
            border: none;
        }

        .page.with-report-background .page-content {
            padding-top: 4cm;
            padding-bottom: 3cm;
        }

        body.is-download .page {
            margin-top: 94px;
        }

        body.is-download .page-content {
            padding-top: 32px;
        }

        .page-content {
            flex: 1 1 auto;
            position: relative;
            z-index: 1;
        }

        .header {
            display: grid;
            grid-template-columns: minmax(120px, 1fr) auto minmax(120px, 1fr);
            align-items: start;
            gap: 12px;
            position: relative;
            min-height: 90px;
        }

        .logo-block {
            display: grid;
            gap: 6px;
            font-size: 11px;
            align-self: start;
        }

        .logo-mark {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #e8f2f8;
            color: var(--accent);
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .center-title {
            grid-column: 1 / -1;
            text-align: center;
            justify-self: center;
            z-index: 1;
        }

        .center-title h1 {
            font-size: 18px;
            margin: 2px 0;
            letter-spacing: 0.04em;
        }

        .report-title {
            text-align: center;
            margin: 12px 0 10px;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 24px;
            font-size: 14px;
            margin-top: 50px;
        }

        body.is-download .header {
            margin-bottom: 60px;
        }

        body.is-download .info-grid {
            margin-top: 100px;
        }

        .info-block {
            display: grid;
            gap: 4px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 130px 14px 1fr;
            gap: 6px;
        }

        .info-row strong {
            font-weight: 700;
        }

        .divider {
            border-top: 1px solid var(--line);
            margin: 14px 0;
        }

        .test-title {
            text-align: center;
            font-weight: 400;
            font-size: 15px;
            font-family: Arial, sans-serif;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
            font-family: Arial, sans-serif;
        }

        thead th {
            border-bottom: 1px solid var(--line);
            padding: 6px 4px;
            text-align: left;
            font-weight: 400;
        }

        tbody td {
            padding: 4px;
            vertical-align: top;
        }

        tbody td {
            font-weight: 400;
        }

        .result-value {
            font-weight: 700;
        }

        .subhead {
            font-weight: 400;
            padding-top: 8px;
        }

        .param-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            font-size: 11px;
        }

        .footer-card {
            font-size: 12px;
        }

        .footer-line {
            border-top: 1px solid var(--report-accent, #0b5a77);
            margin-bottom: 6px;
        }

        .footer-top {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }

        .footer-left {
            justify-self: start;
            text-align: left;
        }

        .footer-right {
            justify-self: end;
            text-align: right;
            align-self: start;
            margin-top: -20px;
        }

        .footer-top.raise {
            margin-top: -70px;
        }

        .footer-doctors {
            column-count: 2;
            column-gap: 18px;
            color: var(--report-accent, #0b5a77) !important;
            font-weight: 600;
        }

        .footer-doctors div {
            break-inside: avoid;
            margin-bottom: 2px;
        }

        .footer-meta {
            margin-top: 6px;
            color: var(--report-accent, #0b5a77) !important;
            font-weight: 700;
        }

        .footer-contact {
            display: grid;
            grid-template-columns: minmax(220px, 1.4fr) repeat(4, minmax(90px, 1fr));
            gap: 4px 14px;
            margin-top: 6px;
            color: var(--report-accent, #0b5a77) !important;
            font-weight: 400;
            font-size: 13px;
            align-items: center;
            justify-items: center;
            width: 100%;
            text-align: center;
        }

        .footer-contact span {
            white-space: nowrap;
            text-align: center;
        }

        .footer-contact .footer-address {
            white-space: normal;
            text-align: center;
        }

        .footer-contact .label {
            font-weight: 400;
            font-size: 13px;
            color: var(--report-accent, #0b5a77) !important;
        }

        .signature {
            text-align: right;
            padding-right: 0;
            margin-top: -60px;
        }

        .signature-name,
        .signature-role {
            font-size: 12px;
            display: block;
            color: #111;
            margin-top: 4px;
        }

        .signature-line {
            margin-top: 28px;
            border-top: 1px solid #333;
            width: 160px;
            margin-left: auto;
            margin-right: auto;
        }

        .signature-img {
            max-height: 50px;
            max-width: 180px;
            margin-top: 6px;
            filter: grayscale(1) contrast(1.1);
        }

        .report-header-image {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
        }

        .report-background {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .report-background img,
        .report-background object {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .pdf-background {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .pdf-background img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }



        .report-footer {
            margin-top: auto;
            padding-top: 12px;
            position: relative;
            z-index: 2;
        }

        .footer-qr {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-start;
            margin-top: 0;
        }

        .footer-qr .qr-cell,
        .footer-qr .barcode-cell {
            align-self: center;
            display: flex;
            flex-direction: column;
        }

        .footer-qr img.qr {
            width: 60px;
            height: 60px;
            display: block;
        }

        .footer-qr img.barcode {
            width: 150px;
            height: 36px;
            display: block;
            margin-top: 0;
        }

        .barcode-info {
            text-align: left;
            font-size: 9px;
            color: #101820;
            line-height: 1.2;
        }

        .barcode-info .barcode-id {
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        @page {
            size: A4;
            margin: 0;
        }

        @page :first {
            margin: 0;
        }

        @media print {
            body { background: #fff; }
            .page { border: none; margin: 0; width: auto; min-height: 1123px; }
            .report-footer { display: block; }
            .report-actions,
            .header { display: none !important; }
            body.is-pdf .report-actions,
            body.is-pdf .header {
                display: flex !important;
            }
            body.is-pdf .report-background {
                display: block !important;
            }
            .page-content { padding-top: 4cm; }
            body { font-size: 1.05em; }
            .footer-line,
            .footer-contact { display: none; }
            .signature { padding-right: 20px; margin-top: -10px; }
            .report-background { display: none; }
            body, .center-title, .report-title, .test-title, table, th, td {
                color: #000 !important;
            }
            .divider, thead th, tbody td {
                border-color: #000 !important;
            }
            html, body {
                height: 1123px;
                overflow: hidden;
            }
            .page {
                height: 1123px;
                overflow: hidden;
                page-break-after: avoid;
            }
            body.is-check-life .info-grid {
                margin-top: 3cm;
            }
        }

        body.is-print .report-actions {
            display: none;
        }

        body.is-pdf {
            background: #f3f6f8;
            margin: 0;
            font-size: 0.82em;
        }

        body.is-pdf .page {
            width: 210mm;
            height: 297mm;
            margin: 0;
            border: none;
            padding: 0;
            box-sizing: border-box;
            min-height: 0;
            display: block;
            overflow: hidden;
            position: relative;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        body.is-pdf .page-content {
            padding: 6mm 8mm 10mm;
            height: auto;
            box-sizing: border-box;
        }

        body.is-pdf .info-grid {
            font-size: 12px;
            margin-top: 20px;
        }

        body.is-check-life.is-pdf .info-grid {
            margin-top: 0;
        }

        body.is-check-life.is-pdf .page-content {
            padding-top: 7cm;
        }

        body.is-pdf .test-title {
            font-size: 12px;
            margin-bottom: 5px;
        }

        body.is-pdf table {
            font-size: 12px;
        }

        body.is-pdf .footer-card,
        body.is-pdf .footer-contact {
            font-size: 13px;
        }

        body.is-pdf .header {
            min-height: 70px;
        }

        body.is-pdf .report-header-image {
            max-height: 4cm;
        }

        body.is-pdf .report-footer {
            position: absolute;
            left: 36px;
            right: 36px;
            bottom: 24px;
        }

        body.is-pdf .page-content {
            padding-top: 4cm;
            padding-bottom: 3cm;
        }

        body.is-pdf .page {
            width: 210mm;
            min-height: 297mm;
            height: 297mm;
            margin: 0;
        }

        body.is-pdf .pdf-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        body.is-pdf .pdf-background img {
            width: 100%;
            height: 100%;
            object-fit: fill;
        }


        body.is-print .header {
            display: none !important;
        }

        body.is-print .page-content {
            padding-top: 4cm;
        }

        body.is-pdf .divider {
            margin: 10px 0;
        }

        body.is-pdf .comment-table td {
            height: 24px !important;
        }

        body.is-pdf .report-actions {
            display: none;
        }

        body.is-pdf .signature {
            padding-right: 15mm;
            margin-top: -60px;
        }

        body.is-pdf .report-footer {
            position: absolute;
            left: 8mm;
            right: 8mm;
            bottom: 20mm;
            margin-top: 0;
            display: block !important;
        }

        body.is-pdf .footer-top.raise {
            margin-top: -250px;
        }

        body.is-pdf .footer-contact {
            grid-template-columns: 2.2fr repeat(4, 1fr);
            gap: 4px 10px;
        }

        body.is-pdf .footer-qr {
            display: table;
            border-collapse: collapse;
        }

        body.is-pdf .footer-qr .qr-cell,
        body.is-pdf .footer-qr .barcode-cell {
            display: table-cell;
            vertical-align: middle;
        }

        body.is-pdf .footer-qr .barcode-cell {
            padding-left: 10px;
        }

        .lipid-table-box {
            width: 100%;
            max-width: 1240px;
            height: auto;
            box-sizing: border-box;
            border: 2px solid #000;
            overflow: hidden;
            font-family: Arial, Helvetica, sans-serif;
        }

        body.is-pdf .lipid-table-box {
            max-width: 840px;
            width: 100%;
            height: auto;
        }

        .lipid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .lipid-table th,
        .lipid-table td {
            border: 1px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 3px 4px;
            font-size: 7pt;
            line-height: 1.05;
        }

        .lipid-table th {
            font-weight: 700;
        }

        .lipid-table .big {
            font-weight: 700;
            font-size: 7pt;
        }

        .result-interpretation-cell[data-parameter="P000010"] {
            display: none;
        }

    </style>
    @php
        use Illuminate\Support\Str;

        $settings = $settings ?? [];
        $labId = $specimenTest->lab_id ?? null;
        $labRecord = \App\Models\Lab::query()->whereKey($labId)->first();
        $labName = $settings['lab_name'] ?? ($labRecord?->name ?? '');
        $logoSrc = $reportLogoSrc ?? (!empty($settings['report_logo_path']) ? route('reports.asset', ['type' => 'logo', 'lab' => $labId]) : null);
        $signatureSrc = $reportSignatureSrc ?? (!empty($settings['report_signature_path']) ? route('reports.asset', ['type' => 'signature', 'lab' => $labId]) : null);
        $reportTitleColor = $settings['report_title_color'] ?? '#0b5a77';
        $testTitleColor = $settings['report_test_title_color'] ?? '#b00020';
        $reportHeaderHtml = trim($settings['report_header_html'] ?? '');
        $reportHeaderMode = $settings['report_header_mode'] ?? 'html';
        $reportHeaderPath = $settings['report_header_image_path'] ?? '';
        $reportHeaderImageSrc = $reportHeaderImageSrc ?? (!empty($reportHeaderPath) ? route('reports.asset', ['type' => 'header', 'lab' => $labId, 'v' => md5($reportHeaderPath)]) : null);
        $reportHeaderIsPdf = !empty($reportHeaderPath) && str_ends_with(strtolower($reportHeaderPath), '.pdf');
        $reportFooterHtml = trim($settings['report_footer_html'] ?? '');
        $logoHeight = (int) ($settings['report_logo_height'] ?? 48);
        $logoHeight = $logoHeight > 0 ? $logoHeight : 48;
        $logoWidth = (int) ($settings['report_logo_width'] ?? 0);
        $logoWidth = $logoWidth > 0 ? $logoWidth : null;
        $doctorLines = array_values(array_filter([
            $settings['report_footer_doctor_line1'] ?? '',
            $settings['report_footer_doctor_line2'] ?? '',
            $settings['report_footer_doctor_line3'] ?? '',
            $settings['report_footer_doctor_line4'] ?? '',
            $settings['report_footer_doctor_line5'] ?? '',
        ], static fn ($line) => trim($line) !== ''));
        $footerAddress = trim($settings['report_footer_address'] ?? '') ?: 'A 26B, Gnanasoorium SQ, Batticaloa';
        $footerPhoneT = trim($settings['report_footer_phone_t'] ?? '') ?: '+94772702303';
        $footerPhoneF = trim($settings['report_footer_phone_f'] ?? '') ?: '+94772702303';
        $footerEmail = trim($settings['report_footer_email'] ?? '') ?: 'info@himalaya.lk';
        $footerWebsite = trim($settings['report_footer_website'] ?? '') ?: 'www.Himalayalab.lk';
        $isCheckLife = $labName !== '' && strcasecmp(trim($labName), 'Check Life Laboratory') === 0;
        $specimenTestName = strtolower(trim((string) ($specimenTest->testMaster?->name ?? '')));
        $isLipidProfileTest = str_contains($specimenTestName, 'lipid profile');

        $patientSexNormalized = strtolower(trim((string) ($patient?->sex ?? '')));
        $normalizeLipidParameter = fn (?string $name) => (string) Str::of($name ?? '')->lower()->replaceMatches('/[^a-z0-9]+/', '');
        $parseNumericValue = function (?string $value) {
            if ($value === null) {
                return null;
            }
            if (is_numeric($value)) {
                return (float) $value;
            }
            if (preg_match('/-?\d+(?:\\.?\\d+)?/', $value, $matches)) {
                return (float) $matches[0];
            }
            return null;
        };

        $interpretTotalCholesterol = function (?string $raw) use ($parseNumericValue) {
            $value = $parseNumericValue($raw);
            if ($value === null) {
                return '';
            }
            if ($value < 200) {
                return 'DESIRABLE';
            }
            if ($value <= 239) {
                return 'BORDERLINE HIGH';
            }
            return 'HIGH';
        };

        $interpretHDL = function (?string $raw) use ($parseNumericValue, $patientSexNormalized) {
            $value = $parseNumericValue($raw);
            if ($value === null) {
                return '';
            }
            $isMale = strpos($patientSexNormalized, 'male') === 0;
            if ($value >= 60) {
                return 'OPTIMAL';
            }
            if ($isMale) {
                if ($value >= 40) {
                    return 'NEAR OPTIMAL';
                }
                return 'UNDESIRABLE';
            }
            if ($value >= 50) {
                return 'NEAR OPTIMAL';
            }
            return 'UNDESIRABLE';
        };

        $interpretTriglycerides = function (?string $raw) use ($parseNumericValue) {
            $value = $parseNumericValue($raw);
            if ($value === null) {
                return '';
            }
            if ($value < 150) {
                return 'DESIRABLE';
            }
            if ($value <= 199) {
                return 'BORDERLINE HIGH';
            }
            if ($value <= 500) {
                return 'HIGH';
            }
            return 'VERY HIGH';
        };

        $interpretLDL = function (?string $raw) use ($parseNumericValue) {
            $value = $parseNumericValue($raw);
            if ($value === null) {
                return '';
            }
            if ($value < 100) {
                return 'OPTIMAL';
            }
            if ($value <= 129) {
                return 'NEAR OPTIMAL';
            }
            if ($value <= 159) {
                return 'BORDERLINE HIGH';
            }
            if ($value <= 190) {
                return 'HIGH';
            }
            return 'VERY HIGH';
        };

        $interpretNonHDL = function (?string $raw) use ($parseNumericValue) {
            $value = $parseNumericValue($raw);
            if ($value === null) {
                return '';
            }
            return $value < 130 ? 'DESIRABLE' : 'HIGH';
        };

        $interpretTGHDL = function (?string $raw) use ($parseNumericValue) {
            $value = $parseNumericValue($raw);
            if ($value === null) {
                return '';
            }
            if ($value < 2.0) {
                return 'OPTIMAL';
            }
            if ($value <= 3.0) {
                return 'MODERATE RISK';
            }
            if ($value > 3.8) {
                return 'HIGH RISK';
            }
            return 'MODERATE RISK';
        };

        $interpretTCHDL = function (?string $raw) use ($parseNumericValue) {
            $value = $parseNumericValue($raw);
            if ($value === null) {
                return '';
            }
            if ($value < 3.3) {
                return 'OPTIMAL';
            }
            if ($value <= 4.5) {
                return 'BORDERLINE HIGH';
            }
            return 'HIGH';
        };

        $interpretVldl = function (?string $raw) use ($parseNumericValue) {
            $value = $parseNumericValue($raw);
            if ($value === null) {
                return '';
            }
            if ($value < 3.3) {
                return 'OPTIMAL';
            }
            if ($value <= 4.5) {
                return 'BORDERLINE HIGH';
            }
            return 'HIGH';
        };

        $lipidInterpretationMap = [
            'totalcholesterol' => $interpretTotalCholesterol,
            'totalcholestrol' => $interpretTotalCholesterol,
            'totalcholsterol' => $interpretTotalCholesterol,
            'hdicholesterol' => $interpretHDL,
            'hdlcholesterol' => $interpretHDL,
            'hdlcholestrol' => $interpretHDL,
            'triglycerides' => $interpretTriglycerides,
            'triglyceride' => $interpretTriglycerides,
            'ldlcholesterol' => $interpretLDL,
            'ldlcholestrol' => $interpretLDL,
            'vldlcholesterol' => $interpretVldl,
            'vldlcholestrol' => $interpretVldl,
            'totalcholfasting' => $interpretTotalCholesterol,
            'totalcholesterolhdlratio' => $interpretTCHDL,
            'totalcholestrolhdlratio' => $interpretTCHDL,
            'totalcholesterolhdlr' => $interpretTCHDL,
            'tghdlratio' => $interpretTGHDL,
            'triglycerideshdlratio' => $interpretTGHDL,
            'triglycerideshdhratio' => $interpretTGHDL,
        ];

        $computeLipidInterpretation = function (?string $name, ?string $value) use ($normalizeLipidParameter, $lipidInterpretationMap) {
            $key = $normalizeLipidParameter($name);
            if ($key === '') {
                return '';
            }
            foreach ($lipidInterpretationMap as $nameKey => $callback) {
                if ($key === $nameKey) {
                    return call_user_func($callback, $value);
                }
            }
            return '';
        };

        $pageBackgroundSource = $reportBackgroundDataUri ?? $reportBackgroundSrc ?? '';
        if (!is_string($pageBackgroundSource)) {
            $pageBackgroundSource = '';
        }
    @endphp
</head>
    <body class="{{ !empty($pdfMode) ? 'is-pdf' : '' }}{{ !empty($printMode) ? ' is-print' : '' }}{{ !empty($downloadMode) ? ' is-download' : '' }}{{ $isCheckLife ? ' is-check-life' : '' }}" style="--report-accent: {{ $reportTitleColor }};">
    @if (empty($pdfMode) && empty($downloadMode) && empty($printMode))
        <div class="report-actions">
            @php
                $backUrl = url()->previous();
                if ($backUrl === url()->current()) {
                    $backUrl = route('results.validate');
                }
            @endphp
            <a href="{{ $backUrl }}">Back</a>
            @if (!empty($canPrint))
                <a class="primary" href="{{ url()->route('reports.show', $specimenTest) }}?download=1" target="_blank">Download PDF</a>
                <a href="{{ url()->route('reports.show', $specimenTest) }}?print=1" target="_blank">Print</a>
                <a id="report_email" href="#">Email</a>
                <a class="whatsapp" id="report_whatsapp" href="#" target="_blank" rel="noopener">WhatsApp</a>
            @else
                <div style="font-size:12px;color:#b00020;font-weight:700;">Result must be validated/approved before printing.</div>
            @endif
        </div>
    @endif
    <div class="page {{ ($reportHeaderMode === 'image' && !empty($reportHeaderImageSrc) && !$reportHeaderIsPdf) ? 'with-header-image' : '' }} {{ !empty($reportBackgroundSrc) ? 'with-report-background' : '' }}">
        @if (!empty($reportBackgroundSrc) && empty($printMode) && empty($downloadMode))
            <div class="report-background">
                @if (!empty($reportBackgroundIsPdf))
                    <object data="{{ $reportBackgroundSrc }}" type="application/pdf"></object>
                @else
                    <img src="{{ $reportBackgroundSrc }}" alt="Report Background">
                @endif
            </div>
        @endif
        @if (!empty($downloadMode) && !empty($reportBackgroundDataUri))
            <div class="pdf-background">
                <img src="{{ $reportBackgroundDataUri }}" alt="Report Background">
            </div>
        @endif
            <div class="page-content">
        @if (empty($canPrint))
            <div style="border:1px solid #f2b6b6;background:#fff4f4;color:#a01515;padding:8px 10px;border-radius:8px;font-size:12px;margin-bottom:10px;">
                Result must be validated/approved before printing or downloading.
            </div>
        @endif
        @if ($reportHeaderMode === 'image' && !empty($reportHeaderImageSrc) && !$reportHeaderIsPdf && empty($reportBackgroundSrc))
            <div class="header" style="display:block;">
                <img class="report-header-image" src="{{ $reportHeaderImageSrc }}" alt="Report Header">
            </div>
        @elseif ($reportHeaderMode === 'image' && !empty($reportHeaderImageSrc) && $reportHeaderIsPdf && empty($printMode) && empty($downloadMode) && empty($reportBackgroundSrc))
            <div class="header" style="display:block;">
                <object data="{{ $reportHeaderImageSrc }}" type="application/pdf" style="width:100%;height:160px;border:1px solid #d9e1e6;border-radius:8px;"></object>
            </div>
        @elseif ($reportHeaderHtml !== '' && empty($reportBackgroundSrc))
            {!! $reportHeaderHtml !!}
        @elseif (empty($reportBackgroundSrc))
            <div class="header">
                <div class="logo-block"></div>
                <div class="center-title">
                    @if (!empty($logoSrc))
                        <img src="{{ $logoSrc }}" alt="Report Logo" style="height:{{ $logoHeight }}px;{{ $logoWidth ? 'width:' . $logoWidth . 'px;' : '' }}margin:0 auto 6px;display:block;object-fit:contain;">
                    @else
                        <div class="logo-mark">H</div>
                    @endif
                    <div style="color:var(--accent);font-weight:700;">{{ $labName }}</div>
                    <h1 style="color: {{ $reportTitleColor }};">LABORATORY REPORT</h1>
                </div>
            </div>
        @endif

        <div class="report-title" style="display:none;"></div>

        @php
            $invoiceAt = $specimenTest->specimen?->created_at;
            $collectAt = $specimenTest->specimen?->collected_at ?? $invoiceAt;
            $invoiceDate = optional($invoiceAt)->format('Y-m-d');
            $invoiceTime = optional($invoiceAt)->format('H:i');
            $collectDate = optional($collectAt)->format('Y-m-d');
            $collectTime = optional($collectAt)->format('H:i');
            $specimenType = trim((string) ($specimenTest->testMaster?->sample_type ?? ''));
            $referredByLabel = trim((string) ($referredBy ?? ''));
            $resolveResultImage = function (?string $path) use ($downloadMode) {
                if (!$path) {
                    return null;
                }
                $path = ltrim($path, '/');
                $filePath = storage_path('app/public/' . $path);
                if ($downloadMode && is_file($filePath)) {
                    $mime = mime_content_type($filePath) ?: 'image/png';
                    $data = base64_encode(file_get_contents($filePath));
                    return 'data:' . $mime . ';base64,' . $data;
                }
                return \Illuminate\Support\Facades\Storage::url($path);
            };
        @endphp

        @if (!empty($pdfMode))
            <table style="width:100%;font-size:12px;border-collapse:collapse;">
                <tbody>
                    <tr>
                        <td style="width:50%;vertical-align:top;">
                            <table style="width:100%;border-collapse:collapse;">
                                <tbody>
                                    <tr><td style="width:130px;">Patient Name</td><td style="width:14px;">:</td><td><strong>{{ $patient?->name ?? '-' }}</strong></td></tr>
                                    <tr><td>Location</td><td>:</td><td>{{ $specimenTest->specimen?->center?->name ?? 'Main Lab' }}</td></tr>
                                    <tr><td>Specimen No</td><td>:</td><td>{{ $specimenTest->specimen?->specimen_no ?? '-' }}</td></tr>
                                    <tr><td>UHID</td><td>:</td><td>{{ $patient?->uhid ?? '-' }}</td></tr>
                                    <tr><td>Testing Unit</td><td>:</td><td>{{ $specimenTest->testMaster?->department?->name ?? '-' }}</td></tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width:50%;vertical-align:top;">
                            <table style="width:100%;border-collapse:collapse;">
                                <tbody>
                                    <tr><td style="width:130px;">Age / Gender</td><td style="width:14px;">:</td><td>{{ $age ?? '-' }}{{ $patient?->sex ? ' / ' . $patient->sex : '' }}</td></tr>
                                    <tr><td>Invoice Date</td><td>:</td><td>{{ $invoiceDate ?? '-' }} {{ $invoiceTime ? 'Time : ' . $invoiceTime : '' }}</td></tr>
                                    @if ($specimenType !== '')
                                        <tr><td>Specimen Type</td><td>:</td><td>{{ $specimenType }}</td></tr>
                                    @endif
                                    <tr><td>Specimen Collect Date</td><td>:</td><td>{{ $collectDate ?? '-' }} {{ $collectTime ? 'Time : ' . $collectTime : '' }}</td></tr>
                                    @if ($referredByLabel !== '')
                                        <tr><td>Referred By</td><td>:</td><td>{{ $referredByLabel }}</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="info-grid">
                <div class="info-block">
                    <div class="info-row"><div>Patient Name</div><div>:</div><div><strong>{{ $patient?->name ?? '-' }}</strong></div></div>
                    <div class="info-row"><div>Location</div><div>:</div><div>{{ $specimenTest->specimen?->center?->name ?? 'Main Lab' }}</div></div>
                    <div class="info-row"><div>Specimen No</div><div>:</div><div>{{ $specimenTest->specimen?->specimen_no ?? '-' }}</div></div>
                    <div class="info-row"><div>UHID</div><div>:</div><div>{{ $patient?->uhid ?? '-' }}</div></div>
                    <div class="info-row"><div>Testing Unit</div><div>:</div><div>{{ $specimenTest->testMaster?->department?->name ?? '-' }}</div></div>
                </div>
                <div class="info-block">
                    <div class="info-row"><div>Age / Gender</div><div>:</div><div>{{ $age ?? '-' }}{{ $patient?->sex ? ' / ' . $patient->sex : '' }}</div></div>
                    <div class="info-row"><div>Invoice Date</div><div>:</div><div>{{ $invoiceDate ?? '-' }} {{ $invoiceTime ? 'Time : ' . $invoiceTime : '' }}</div></div>
                    @if ($specimenType !== '')
                        <div class="info-row"><div>Specimen Type</div><div>:</div><div>{{ $specimenType }}</div></div>
                    @endif
                    <div class="info-row"><div>Specimen Collect Date</div><div>:</div><div>{{ $collectDate ?? '-' }} {{ $collectTime ? 'Time : ' . $collectTime : '' }}</div></div>
                    @if ($referredByLabel !== '')
                        <div class="info-row"><div>Referred By</div><div>:</div><div>{{ $referredByLabel }}</div></div>
                    @endif
                </div>
            </div>
        @endif

        <div class="divider"></div>

        <div class="test-title" style="color: {{ $testTitleColor }};">{{ $specimenTest->testMaster?->name ?? 'TEST REPORT' }}</div>
        @if (!empty($specimenTest->is_repeated) || !empty($specimenTest->is_confirmed))
            <div class="report-flags" style="text-align:center;font-size:11px;margin-top:4px;color:#2d3c45;">
                @if (!empty($specimenTest->is_repeated))
                    Repeated [x]
                @endif
                @if (!empty($specimenTest->is_repeated) && !empty($specimenTest->is_confirmed))
                    &nbsp;&nbsp;
                @endif

            </div>
        @endif

        @php
        $parameters = ($specimenTest->testMaster?->parameters ?? collect())
            ->where('is_active', true)
            ->where('is_visible', true)
            ->sortBy(fn ($parameter) => sprintf('%05d-%010d', (int) ($parameter->sort_order ?? 0), (int) ($parameter->id ?? 0)))
            ->values();
            $paramResults = $specimenTest->parameterResults?->keyBy('test_parameter_id') ?? collect();
            $hasSecondColumn = $parameters->firstWhere('result_column', 2) !== null;
            $hasInterpretation = $parameters->firstWhere('show_interpretation', true) !== null;
            $colspan = $hasInterpretation ? 5 : 4;
            $leftParams = $parameters->where('result_column', 1)->values();
            $rightParams = $parameters->where('result_column', 2)->values();
        @endphp

        <div class="{{ $hasSecondColumn ? 'param-grid' : '' }}">
            <div>
                <table>
                    <thead>
                        <tr>
                            <th>Laboratory Investigation</th>
                            <th>Result</th>
                            <th>Unit</th>
                            <th>Reference Interval</th>
                            @if ($hasInterpretation)
                                <th>Result Interpretation</th>
                            @endif
                        </tr>
                    </thead>
                        <tbody>
                            @forelse ($leftParams as $parameter)
                            @php
                                $isLabel = ($parameter->display_type ?? '') === 'label';
                                $labelText = ($parameter->remarks ?? '') !== '' ? $parameter->remarks : $parameter->name;
                            @endphp
                            @if ($isLabel)
                                @php
                                    $labelImageSrc = $resolveResultImage($parameter->reference_image_path ?? null);
                                    $labelMaxWidth = !empty($parameter->reference_image_width) ? $parameter->reference_image_width : 180;
                                    $labelMaxHeight = !empty($parameter->reference_image_height) ? $parameter->reference_image_height : 140;
                                    $labelImageStyle = 'max-width:' . $labelMaxWidth . 'px;max-height:' . $labelMaxHeight . 'px;';
                                @endphp
                                <tr style="font-size: {{ $parameter->font_size ?? 12 }}px;color: {{ $parameter->text_color ?? '#000' }};font-weight: {{ $parameter->is_bold ? '700' : '400' }};text-decoration: {{ $parameter->is_underline ? 'underline' : 'none' }};font-style: {{ $parameter->is_italic ? 'italic' : 'normal' }};">
                                    <td colspan="{{ $colspan }}" style="white-space: pre-wrap;">
                                        {!! nl2br(e($labelText)) !!}
                                        @if ($labelImageSrc)
                                            <div style="margin-top:4px;">
                                                <img src="{{ $labelImageSrc }}" alt="Label reference image" style="{{ $labelImageStyle }}border:1px solid #d1d5db;padding:2px;border-radius:4px;object-fit:contain;">
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @continue
                            @endif
                            @php
                                $result = $paramResults->get($parameter->id);
                                $rawFlag = trim((string) ($result?->flag ?? ''));
                                $computedInterpretation = $computeLipidInterpretation($parameter->name, $result?->result_value);
                                if ($parameter->show_interpretation ?? true) {
                                    if ($isLipidProfileTest && $computedInterpretation !== '') {
                                        $interpret = $computedInterpretation;
                                    } elseif ($rawFlag !== '') {
                                        $interpret = $rawFlag;
                                    } else {
                                        $interpret = 'NORMAL';
                                    }
                                } else {
                                    $interpret = '';
                                }
                                $rowStyle = [];
                                if ($parameter->is_bold) { $rowStyle[] = 'font-weight:700'; }
                                if ($parameter->is_underline) { $rowStyle[] = 'text-decoration:underline'; }
                                if ($parameter->is_italic) { $rowStyle[] = 'font-style:italic'; }
                                if (!empty($parameter->text_color)) { $rowStyle[] = 'color:' . $parameter->text_color; }
                                $rowStyleText = implode(';', $rowStyle);
                                $isBloodGroupAbo = ($parameter->code ?? '') === 'P000010';
                            @endphp
                            @if (!empty($parameter->group_label))
                                <tr>
                                    <td colspan="{{ $colspan }}" class="subhead">{{ $parameter->group_label }}</td>
                                </tr>
                            @endif
                            <tr style="{{ $rowStyleText }}">
                                <td>{{ $parameter->name }}</td>
                                @php
                                    $imageSrc = ($parameter->display_type ?? '') === 'image'
                                        ? $resolveResultImage($result?->image_path ?? null)
                                        : null;
                                @endphp
                                <td>
                                    @if ($imageSrc)
                                        <img src="{{ $imageSrc }}" alt="Result image" style="max-width:160px;max-height:120px;border:1px solid #d1d5db;padding:2px;border-radius:4px;object-fit:contain;">
                                    @else
                                        <span class="result-value">{{ $result?->result_value ?? '-' }}</span>
                                    @endif
                                </td>
                                <td>{{ $imageSrc ? '-' : ($result?->unit ?? $parameter->unit ?? '-') }}</td>
                                <td>{{ $imageSrc ? '-' : ($result?->reference_range ?? $parameter->reference_range ?? '-') }}</td>
                                @if ($hasInterpretation)
                                    <td>{{ $interpret }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td>{{ $specimenTest->testMaster?->name ?? '-' }}</td>
                                <td><span class="result-value">{{ $specimenTest->result?->result_value ?? '-' }}</span></td>
                                <td>{{ $specimenTest->result?->unit ?? '-' }}</td>
                                <td>{{ $specimenTest->result?->reference_range ?? '-' }}</td>
                                @if ($hasInterpretation)
                                    <td>NORMAL</td>
                                @endif
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($hasSecondColumn)
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th>Laboratory Investigation</th>
                                <th>Result</th>
                                <th>Unit</th>
                                <th>Reference Interval</th>
                                @if ($hasInterpretation)
                                    <th>Result Interpretation</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rightParams as $parameter)
                            @php
                                $isLabel = ($parameter->display_type ?? '') === 'label';
                                $labelText = ($parameter->remarks ?? '') !== '' ? $parameter->remarks : $parameter->name;
                            @endphp
                            @if ($isLabel)
                                @php
                                    $labelImageSrc = $resolveResultImage($parameter->reference_image_path ?? null);
                                    $labelMaxWidth = !empty($parameter->reference_image_width) ? $parameter->reference_image_width : 180;
                                    $labelMaxHeight = !empty($parameter->reference_image_height) ? $parameter->reference_image_height : 140;
                                    $labelImageStyle = 'max-width:' . $labelMaxWidth . 'px;max-height:' . $labelMaxHeight . 'px;';
                                @endphp
                                <tr style="font-size: {{ $parameter->font_size ?? 12 }}px;color: {{ $parameter->text_color ?? '#000' }};font-weight: {{ $parameter->is_bold ? '700' : '400' }};text-decoration: {{ $parameter->is_underline ? 'underline' : 'none' }};font-style: {{ $parameter->is_italic ? 'italic' : 'normal' }};">
                                    <td colspan="{{ $colspan }}" style="white-space: pre-wrap;">
                                        {!! nl2br(e($labelText)) !!}
                                        @if ($labelImageSrc)
                                            <div style="margin-top:4px;">
                                                <img src="{{ $labelImageSrc }}" alt="Label reference image" style="{{ $labelImageStyle }}border:1px solid #d1d5db;padding:2px;border-radius:4px;object-fit:contain;">
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @continue
                            @endif
                            @php
                                $result = $paramResults->get($parameter->id);
                                $rawFlag = trim((string) ($result?->flag ?? ''));
                                $computedInterpretation = $computeLipidInterpretation($parameter->name, $result?->result_value);
                                if ($parameter->show_interpretation ?? true) {
                                    if ($isLipidProfileTest && $computedInterpretation !== '') {
                                        $interpret = $computedInterpretation;
                                    } elseif ($rawFlag !== '') {
                                        $interpret = $rawFlag;
                                    } else {
                                        $interpret = 'NORMAL';
                                    }
                                } else {
                                    $interpret = '';
                                }
                                $rowStyle = [];
                                if ($parameter->is_bold) { $rowStyle[] = 'font-weight:700'; }
                                if ($parameter->is_underline) { $rowStyle[] = 'text-decoration:underline'; }
                                if ($parameter->is_italic) { $rowStyle[] = 'font-style:italic'; }
                                if (!empty($parameter->text_color)) { $rowStyle[] = 'color:' . $parameter->text_color; }
                                $rowStyleText = implode(';', $rowStyle);
                                $isBloodGroupAbo = ($parameter->code ?? '') === 'P000010';
                            @endphp
                                @if (!empty($parameter->group_label))
                                    <tr>
                                        <td colspan="{{ $colspan }}" class="subhead">{{ $parameter->group_label }}</td>
                                    </tr>
                                @endif
                                <tr style="{{ $rowStyleText }}">
                                    <td>{{ $parameter->name }}</td>
                                    @php
                                        $imageSrc = ($parameter->display_type ?? '') === 'image'
                                            ? $resolveResultImage($result?->image_path ?? null)
                                            : null;
                                    @endphp
                                    <td>
                                        @if ($imageSrc)
                                            <img src="{{ $imageSrc }}" alt="Result image" style="max-width:160px;max-height:120px;border:1px solid #d1d5db;padding:2px;border-radius:4px;object-fit:contain;">
                                        @else
                                            <span class="result-value">{{ $result?->result_value ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $imageSrc ? '-' : ($result?->unit ?? $parameter->unit ?? '-') }}</td>
                                    <td>{{ $imageSrc ? '-' : ($result?->reference_range ?? $parameter->reference_range ?? '-') }}</td>
                                    @if ($hasInterpretation)
                                        <td class="result-interpretation-cell" data-parameter="{{ $parameter->code ?? '' }}">
                                            @if (!$isBloodGroupAbo)
                                                {{ $interpret }}
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $colspan }}">-</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($isLipidProfileTest && $hasInterpretation)
            <div style="margin-top:8px;margin-bottom:8px;display:flex;justify-content:center;">
                <span style="font-size:10px;font-weight:700;">* Result Interpretation-based clinical decision values:</span>
            </div>
            <div style="display:flex;justify-content:center;">
                <div class="lipid-table-box">
                    <table class="lipid-table">
                        <thead>
                            <tr>
                                <th>Total Cholesterol<br>(mg/dL)</th>
                                <th>HDL Cholesterol<br>(mg/dL)</th>
                                <th>Triglycerides<br>(md/dL)</th>
                                <th>LDL Cholesterol<br>(mg/dL)</th>
                                <th>TGL / HDL Ratio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="big">&lt;200</span><br>Desirable</td>
                                <td><span class="big">&gt;60</span><br>Optimal</td>
                                <td><span class="big">&lt;150</span><br>Desirable</td>
                                <td><span class="big">&lt;100</span><br>Optimal</td>
                                <td><span class="big">&lt;3.3</span><br>Optimal</td>
                            </tr>
                            <tr>
                                <td><span class="big">200 – 240</span><br>Borderline High</td>
                                <td>
                                    <span class="big">40 – 60 Male<br>50 – 60 Female</span><br>
                                    Near Optimal
                                </td>
                                <td><span class="big">150 – 199</span><br>Borderline High</td>
                                <td><span class="big">100 – 129</span><br>Near Optimal</td>
                                <td><span class="big">3.3 – 4.5</span><br>Borderline High</td>
                            </tr>
                            <tr>
                                <td><span class="big">&gt;240</span><br>High</td>
                                <td>
                                    <span class="big">&lt;40 Male<br>&lt;50 Female</span><br>
                                    Undesirable
                                </td>
                                <td>
                                    <span class="big">200 – 500 High<br>&gt;500 Very High</span>
                                </td>
                                <td>
                                    <span class="big">
                                        130 – 159 Borderline High<br>
                                        160 – 190 High<br>
                                        &gt;190 Very High
                                    </span>
                                </td>
                                <td><span class="big">&gt;4.5</span><br>High</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <table class="comment-table" style="margin-top:10px;">
            <tbody>
                <tr>
                    <td class="subhead">Comment :</td>
                </tr>
                <tr>
                    @php
                        $defaultComment = '';
                        if (!empty($specimenTest->testMaster?->reference_ranges) && is_array($specimenTest->testMaster->reference_ranges)) {
                            $defaultComment = trim((string) ($specimenTest->testMaster->reference_ranges['comment'] ?? ''));
                        }
                    @endphp
                    <td style="height:60px;">
                        @if ($defaultComment !== '')
                            {!! nl2br(e($defaultComment)) !!}
                        @else
                            &nbsp;
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

        @php
            $footerStyle = '';
            if (!empty($pdfMode)) {
                $footerStyle = 'position:absolute;left:10mm;right:10mm;bottom:10mm;margin-top:0;';
            } elseif (!empty($printMode)) {
                $footerStyle = 'position:absolute;left:36px;right:36px;bottom:36px;margin-top:0;';
            }
        @endphp
        <div class="report-footer" style="{{ $footerStyle }}">
        @if ($reportFooterHtml !== '')
            {!! $reportFooterHtml !!}
        @else
            <div class="footer-card">
                <div class="footer-top raise">
                    <div class="footer-left">
                        <div class="footer-doctors">
                            @forelse ($doctorLines as $line)
                                <div>{{ $line }}</div>
                            @empty
                                <div>&nbsp;</div>
                            @endforelse
                        </div>
                        <div class="footer-meta" style="color: var(--report-accent, #0b5a77);">Printed on: {{ now()->format('Y-m-d H:i') }}</div>
                        @if (!empty($qrDataUri) || !empty($barcodeDataUri))
                            <div class="footer-qr">
                                @if (!empty($qrDataUri))
                                    <div class="qr-cell">
                                        <img class="qr" src="{{ $qrDataUri }}" alt="QR Code">
                                    </div>
                                @endif
                                @if (!empty($barcodeDataUri))
                                    <div class="barcode-cell">
                                        <img class="barcode" src="{{ $barcodeDataUri }}" alt="Barcode">
                                        <div class="barcode-info">
                                            <div class="barcode-id">{{ $specimenTest->specimen?->specimen_no ?? '' }}</div>
                                            <div>{{ ($patient?->name ?? '') }} {{ $age ? '(' . $age . ($patient?->sex ? ' ' . $patient->sex : '') . ')' : ($patient?->sex ? '(' . $patient->sex . ')' : '') }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="footer-right">
                        <div class="signature">
                            @if (!empty($signatureSrc))
                                <img class="signature-img" src="{{ $signatureSrc }}" alt="Signature">
                            @else
                                <div class="signature-line"></div>
                            @endif
                            <div class="signature-name">{{ $settings['report_mlt_name'] ?? 'Medical Laboratory Technologist' }}</div>
                            <div class="signature-role">Medical Laboratory Technologist</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @if (!empty($printMode))
        <script>
            window.addEventListener('load', function () {
                window.print();
            });
        </script>
    @endif
    <script>
        (function () {
            var emailLink = document.getElementById('report_email');
            var whatsappLink = document.getElementById('report_whatsapp');
            if (!emailLink && !whatsappLink) {
                return;
            }
            var shareUrl = '{{ url()->route('reports.show', $specimenTest) }}';
            var shareText = 'Report: ' + shareUrl;
            if (emailLink) {
                emailLink.href = 'mailto:?subject=' + encodeURIComponent('Lab Report') + '&body=' + encodeURIComponent(shareText);
            }
            if (whatsappLink) {
                whatsappLink.href = 'https://wa.me/?text=' + encodeURIComponent(shareText);
            }
        })();
    </script>
</body>
</html>

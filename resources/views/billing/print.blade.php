<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice Print</title>
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
            font-family: "Segoe UI", sans-serif;
            color: var(--ink);
        }

        .actions {
            width: 720px;
            margin: 20px auto 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .action-btn {
            background: #f1f5f8;
            border: 1px solid var(--line);
            color: #32414a;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn.primary {
            background: #0a6fb3;
            color: #fff;
            border-color: #0a6fb3;
        }

        .action-btn.whatsapp {
            background: #1fa855;
            color: #fff;
            border-color: #1fa855;
        }

        .page {
            width: 720px;
            margin: 24px auto;
            background: #fff;
            padding: 28px 32px 36px;
            border: 1px solid var(--line);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .bill-header {
            margin-bottom: 14px;
        }

        .bill-header img {
            width: 100%;
            max-height: 140px;
            object-fit: contain;
        }

        .logo-mark {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #e8f2f8;
            color: var(--accent);
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .lab-details {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }

        .bill-footer {
            margin-top: 18px;
            font-size: 11px;
            color: var(--muted);
            display: grid;
            gap: 6px;
        }

        .bill-footer img {
            width: 100%;
            max-height: 120px;
            object-fit: contain;
        }

        .title {
            font-weight: 700;
            font-size: 18px;
        }

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 16px;
            font-size: 12px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 14px;
        }

        thead th {
            border-bottom: 1px solid var(--line);
            text-align: left;
            padding: 6px 4px;
        }

        tbody td {
            padding: 6px 4px;
            border-bottom: 1px solid var(--line);
        }

        .total {
            text-align: right;
            font-weight: 700;
            margin-top: 12px;
            font-size: 12px;
        }

        @media print {
            @page {
                size: A5 landscape;
                margin: 8mm;
            }
            body { background: #fff; }
            .page {
                border: none;
                margin: 0;
                width: auto;
                min-height: 0;
                height: auto;
                padding: 8mm 10mm;
                box-sizing: border-box;
                page-break-after: avoid;
            }
            .stock-warning { display: none; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    @php
        $shareUrl = url()->current();
        $patientName = $specimen->patient->name ?? 'Patient';
        $invoiceNo = $specimen->invoice->invoice_no ?? '';
        $shareText = trim('Invoice ' . $invoiceNo . ' for ' . $patientName . ' ' . $shareUrl);
        use App\Models\Setting;
        use App\Models\Lab;
        $labId = $specimen->lab_id ?? (auth()->user()->lab_id ?? null);
        $settings = Setting::valuesForLab((int) $labId);
        $labRecord = $labId ? Lab::query()->whereKey($labId)->first() : null;
        $billingHeaderPath = $settings['billing_header_image_path'] ?? '';
        $billingFooterPath = $settings['billing_footer_image_path'] ?? '';
        $billingHeaderSrc = $billingHeaderPath
            ? route('reports.asset', ['type' => 'billing_header', 'lab' => $labId, 'v' => md5($billingHeaderPath)])
            : null;
        $billingFooterSrc = $billingFooterPath
            ? route('reports.asset', ['type' => 'billing_footer', 'lab' => $labId, 'v' => md5($billingFooterPath)])
            : null;
        $labName = $settings['billing_lab_name'] ?? ($settings['lab_name'] ?? ($labRecord?->name ?? ''));
        $labEmail = $settings['billing_lab_email'] ?? '';
        $labWeb = $settings['billing_lab_web'] ?? '';
        $labContact = $settings['billing_lab_contact'] ?? '';
        $labFax = $settings['billing_lab_fax'] ?? '';
        $labAddress = $settings['billing_lab_address'] ?? ($settings['report_footer_address'] ?? '');
    @endphp
    <div class="actions">
        @php
            $backUrl = url()->previous();
            if ($backUrl === url()->current()) {
                $backUrl = route('billing.index');
            }
        @endphp
        <a class="action-btn" href="{{ $backUrl }}">Back</a>
        <button class="action-btn primary" type="button" onclick="window.print()">Print</button>
        <button class="action-btn" type="button" onclick="window.print()">Save PDF</button>
        <a class="action-btn" id="email_share" href="#">Email</a>
        <a class="action-btn whatsapp" id="whatsapp_share" href="#" target="_blank" rel="noopener">WhatsApp</a>
    </div>
    <div class="page">
        @if (session('stock_warnings'))
            <div class="stock-warning" style="margin-bottom:10px;padding:8px 10px;border:1px solid #f1c27b;background:#fff6e5;border-radius:8px;font-size:12px;color:#7a4b00;">
                <strong>Stock warning:</strong>
                <ul style="margin:6px 0 0 16px;padding:0;">
                    @foreach (session('stock_warnings') as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (!empty($billingHeaderSrc))
            <div class="bill-header">
                <img src="{{ $billingHeaderSrc }}" alt="Billing Header">
            </div>
        @endif
        <div class="header">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="logo-mark">H</div>
                <div>
                    <div class="title">{{ $labName }}</div>
                    <div class="lab-details">
                        {{ $labAddress }}
                        @if ($labAddress && ($labContact || $labFax || $labEmail || $labWeb))
                            <span> | </span>
                        @endif
                        @if ($labContact) T: {{ $labContact }}@endif
                        @if ($labFax) {{ $labContact ? ' | ' : '' }}F: {{ $labFax }}@endif
                        @if ($labEmail) {{ ($labContact || $labFax) ? ' | ' : '' }}{{ $labEmail }}@endif
                        @if ($labWeb) {{ ($labContact || $labFax || $labEmail) ? ' | ' : '' }}{{ $labWeb }}@endif
                    </div>
                </div>
            </div>
            <div style="font-size:12px;color:var(--muted);text-align:right;">
                <div>Invoice</div>
                <div>Printed: {{ now()->format('Y-m-d H:i') }}</div>
            </div>
        </div>

        <div class="info">
            <div class="info-row"><div>Patient</div><div>: {{ $specimen->patient->name ?? '-' }}</div></div>
            <div class="info-row"><div>Specimen No</div><div>: {{ $specimen->specimen_no ?? '-' }}</div></div>
            <div class="info-row"><div>NIC</div><div>: {{ $specimen->patient->nic ?? '-' }}</div></div>
            <div class="info-row"><div>Center</div><div>: {{ $specimen->center->name ?? '-' }}</div></div>
            <div class="info-row"><div>Invoice No</div><div>: {{ $specimen->invoice->invoice_no ?? '-' }}</div></div>
        </div>

        @php
            $invoice = $specimen->invoice;
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="text-align:right;">Price</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @if ($specimen->products && $specimen->products->isNotEmpty())
                    @foreach ($specimen->products as $product)
                        @php $total += (float) ($product->price - 0); @endphp
                        <tr>
                            <td>{{ $product->name ?? '-' }}</td>
                            <td style="text-align:right;">{{ number_format($product->price ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                @elseif ($specimen->tests && $specimen->tests->isNotEmpty())
                    @foreach ($specimen->tests as $test)
                        @php $total += (float) ($test->price - 0); @endphp
                        <tr>
                            <td>{{ $test->testMaster->name ?? '-' }}</td>
                            <td style="text-align:right;">{{ number_format($test->price ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2">No products billed.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        @php
            $invoiceTotal = (float) ($invoice->total ?? 0);
            $discount = (float) ($invoice->discount ?? 0);
            $vat = (float) ($invoice->vat ?? 0);
            $netTotal = (float) ($invoice->net_total ?? 0);
            $subtotal = max($invoiceTotal - $total, 0);
        @endphp

        <div class="total">Subtotal: {{ number_format($subtotal, 2) }}</div>
        @if ($discount > 0)
            <div class="total">Discount: {{ number_format($discount, 2) }}</div>
        @endif
        @if ($vat > 0)
            <div class="total">VAT: {{ number_format($vat, 2) }}</div>
        @endif
        <div class="total">Net Total: {{ number_format($netTotal, 2) }}</div>

        <div class="bill-footer">
            @if (!empty($billingFooterSrc))
                <img src="{{ $billingFooterSrc }}" alt="Billing Footer">
            @else
                <div>{{ $labName }}</div>
                <div>{{ $labAddress }}</div>
                <div>
                    @if ($labContact) T: {{ $labContact }}@endif
                    @if ($labFax) {{ $labContact ? ' | ' : '' }}F: {{ $labFax }}@endif
                    @if ($labEmail) {{ ($labContact || $labFax) ? ' | ' : '' }}{{ $labEmail }}@endif
                    @if ($labWeb) {{ ($labContact || $labFax || $labEmail) ? ' | ' : '' }}{{ $labWeb }}@endif
                </div>
            @endif
        </div>
    </div>
</body>
@if (request()->query('auto'))
<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
@endif
<script>
    (function () {
        var shareText = @json($shareText);
        var emailLink = document.getElementById('email_share');
        var whatsappLink = document.getElementById('whatsapp_share');
        if (emailLink) {
            emailLink.href = 'mailto:?subject=' + encodeURIComponent('Invoice') + '&body=' + encodeURIComponent(shareText);
        }
        if (whatsappLink) {
            whatsappLink.href = 'https://wa.me/?text=' + encodeURIComponent(shareText);
        }
    })();
</script>
</html>

<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Doctor;
use App\Models\Lab;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\SpecimenTest;
use App\Services\SmsGateway;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $sort = request()->query('sort', 'unprinted_first');
        $items = SpecimenTest::query()
            ->with(['specimen.patient', 'specimen.invoice', 'testMaster', 'result', 'parameterResults'])
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        $severityOrder = [
            'CRITICAL' => 4,
            'ABNORMAL' => 3,
            'HIGH' => 2,
            'LOW' => 2,
            'NORMAL' => 1,
        ];

        $items->each(function (SpecimenTest $item) use ($severityOrder): void {
            $flags = collect();
            if ($item->result && $item->result->flag) {
                $flags->push(strtoupper($item->result->flag));
            }
            if ($item->parameterResults && $item->parameterResults->isNotEmpty()) {
                foreach ($item->parameterResults as $result) {
                    if (!empty($result->flag)) {
                        $flags->push(strtoupper($result->flag));
                    }
                }
            }
            $flagSummary = '';
            if ($flags->isNotEmpty()) {
                $flagSummary = $flags->unique()
                    ->sortByDesc(fn ($flag) => $severityOrder[$flag] ?? 0)
                    ->first() ?? '';
            }
            $item->report_flag = $flagSummary;
        });

        $items = match ($sort) {
            'unprinted_first' => $items->sort(function ($a, $b) {
                $aPrinted = $a->printed_at ? 1 : 0;
                $bPrinted = $b->printed_at ? 1 : 0;
                if ($aPrinted !== $bPrinted) {
                    return $aPrinted <=> $bPrinted;
                }
                $aTime = $a->specimen?->created_at?->timestamp ?? 0;
                $bTime = $b->specimen?->created_at?->timestamp ?? 0;
                return $bTime <=> $aTime;
            }),
            'patient_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->patient?->name ?? ''),
            'specimen_asc' => $items->sortBy(fn ($item) => $item->specimen?->specimen_no ?? ''),
            'specimen_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->specimen_no ?? ''),
            'test_asc' => $items->sortBy(fn ($item) => $item->testMaster?->name ?? ''),
            'test_desc' => $items->sortByDesc(fn ($item) => $item->testMaster?->name ?? ''),
            'status_asc' => $items->sortBy(fn ($item) => $item->status ?? ''),
            'status_desc' => $items->sortByDesc(fn ($item) => $item->status ?? ''),
            'flag_desc' => $items->sortByDesc(fn ($item) => $severityOrder[$item->report_flag ?? ''] ?? 0),
            'flag_asc' => $items->sortBy(fn ($item) => $severityOrder[$item->report_flag ?? ''] ?? 0),
            default => $items->sortBy(fn ($item) => $item->specimen?->patient?->name ?? ''),
        };

        return view('reports.index', [
            'items' => $items,
            'sort' => $sort,
        ]);
    }

    public function latest(): RedirectResponse
    {
        $item = SpecimenTest::query()
            ->orderByDesc('id')
            ->first();

        if (!$item) {
            return redirect()->route('reports.index');
        }

        return redirect()->route('reports.show', $item);
    }

    public function track(): View
    {
        return view('reports.track', [
            'step' => 'request',
            'message' => session('message'),
            'error' => session('error'),
        ]);
    }

    public function trackRequest(\Illuminate\Http\Request $request): RedirectResponse
    {
        $data = $request->validate([
            'uhid' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $phoneDigits = preg_replace('/\D+/', '', $data['phone']);
        $patient = Patient::query()
            ->where('uhid', $data['uhid'])
            ->where('phone', 'like', '%' . $phoneDigits . '%')
            ->first();

        if (!$patient) {
            return redirect()->route('reports.track')->with('error', 'Patient not found. Please check UHID and mobile number.');
        }

        $otp = random_int(100000, 999999);
        session([
            'track_uhid' => $data['uhid'],
            'track_phone' => $phoneDigits,
            'track_otp' => (string) $otp,
            'track_expires' => now()->addMinutes(10)->timestamp,
        ]);

        return redirect()
            ->route('reports.track.verify')
            ->with('message', 'OTP sent to your mobile number.');
    }

    public function trackVerify(): View
    {
        return view('reports.track', [
            'step' => 'verify',
            'message' => session('message'),
            'error' => session('error'),
            'uhid' => session('track_uhid'),
            'phone' => session('track_phone'),
        ]);
    }

    public function trackConfirm(\Illuminate\Http\Request $request): View
    {
        $data = $request->validate([
            'otp' => ['required', 'string', 'max:10'],
        ]);

        $otp = session('track_otp');
        $expires = (int) session('track_expires', 0);
        if (!$otp || now()->timestamp > $expires) {
            return view('reports.track', [
                'step' => 'verify',
                'error' => 'OTP expired. Please request a new one.',
            ]);
        }

        if ($data['otp'] !== $otp) {
            return view('reports.track', [
                'step' => 'verify',
                'error' => 'Invalid OTP. Try again.',
            ]);
        }

        $uhid = session('track_uhid');
        $phone = session('track_phone');

        $patient = Patient::query()
            ->where('uhid', $uhid)
            ->where('phone', 'like', '%' . $phone . '%')
            ->first();

        $reportItems = collect();
        if ($patient) {
            $reportItems = SpecimenTest::query()
                ->with(['specimen', 'testMaster'])
                ->whereHas('specimen', function ($query) use ($patient) {
                    $query->where('patient_id', $patient->id);
                })
                ->whereIn('status', ['VALIDATED', 'APPROVED'])
                ->orderByDesc('id')
                ->limit(10)
                ->get();
        }

        session()->forget(['track_otp', 'track_expires']);

        return view('reports.track', [
            'step' => 'result',
            'patient' => $patient,
            'reportItems' => $reportItems,
        ]);
    }

    public function show(SpecimenTest $specimenTest): Response|View
    {
        $specimenTest->load(['specimen.patient', 'specimen.center', 'specimen.invoice', 'testMaster.parameters', 'result', 'parameterResults']);

        if (!in_array($specimenTest->status, ['RESULT_ENTERED', 'VALIDATED', 'APPROVED'], true)) {
            abort(403);
        }

        $specimen = $specimenTest->specimen;
        $patient = $specimen?->patient;
        $age = $specimen?->age_display ?? null;
        if ($age === null || $age === '') {
            $age = $this->computeAgeDisplay($patient?->dob, $specimen?->created_at);
        }

        $referredBy = null;
        $invoice = $specimen?->invoice;
        if ($invoice?->referral_type === 'doctor' && $invoice->referral_id) {
            $referredBy = Doctor::query()->whereKey($invoice->referral_id)->value('name');
        } elseif ($invoice?->referral_type === 'center' && $invoice->referral_id) {
            $referredBy = Center::query()->whereKey($invoice->referral_id)->value('name');
        }

        $reportUrl = url()->route('reports.show', $specimenTest);
        $qrDataUri = Builder::create()
            ->writer(new PngWriter())
            ->data($reportUrl)
            ->encoding(new Encoding('UTF-8'))
            ->size(120)
            ->margin(2)
            ->build()
            ->getDataUri();
        $barcodeValue = $specimenTest->specimen?->specimen_no ?? (string) $specimenTest->id;
        $barcodeSvg = $this->buildCode39Svg($barcodeValue);
        $barcodeDataUri = $barcodeSvg ? ('data:image/svg+xml;base64,' . base64_encode($barcodeSvg)) : null;

        $labId = $specimenTest->lab_id;
        $defaultSettings = Setting::query()
            ->whereNull('lab_id')
            ->pluck('value', 'key')
            ->all();
        $labSettings = Setting::query()
            ->where('lab_id', $labId)
            ->pluck('value', 'key')
            ->all();
        $settings = array_merge($defaultSettings, $labSettings);

        $printMode = (bool) request()->query('print');
        $downloadMode = (bool) request()->query('download');
        $canPrint = in_array($specimenTest->status, ['VALIDATED', 'APPROVED'], true);

        if (($printMode || $downloadMode) && !$canPrint) {
            abort(403);
        }

        if ($printMode && !$specimenTest->printed_at) {
            $specimenTest->forceFill(['printed_at' => now()])->save();
        }

        $reportLogoSrc = $settings['report_logo_path'] ?? null;
        $reportSignatureSrc = $settings['report_signature_path'] ?? null;
        $reportHeaderImageSrc = $settings['report_header_image_path'] ?? null;
        $reportBackgroundSrc = $settings['report_background_path'] ?? null;
        $reportBackgroundIsPdf = !empty($reportBackgroundSrc) && str_ends_with(strtolower($reportBackgroundSrc), '.pdf');

        if (!empty($reportLogoSrc)) {
            $reportLogoSrc = route('reports.asset', [
                'type' => 'logo',
                'lab' => $labId,
                'v' => md5($reportLogoSrc),
            ]);
        }
        if (!empty($reportSignatureSrc)) {
            $reportSignatureSrc = route('reports.asset', [
                'type' => 'signature',
                'lab' => $labId,
                'v' => md5($reportSignatureSrc),
            ]);
        }
        if (!empty($reportHeaderImageSrc)) {
            $reportHeaderImageSrc = route('reports.asset', [
                'type' => 'header',
                'lab' => $labId,
                'v' => md5($reportHeaderImageSrc),
            ]);
        }
        if (!empty($reportBackgroundSrc)) {
            $reportBackgroundSrc = route('reports.asset', [
                'type' => 'background',
                'lab' => $labId,
                'v' => md5($reportBackgroundSrc),
            ]);
        }

        if ($downloadMode) {
            $inlineLogo = $this->makeReportAssetDataUri('logo', $labId);
            $inlineSignature = $this->makeReportAssetDataUri('signature', $labId);
            $inlineHeader = $this->makeReportAssetDataUri('header', $labId);
            $inlineBackground = $this->makeReportAssetDataUri('background', $labId);
            if ($inlineLogo) {
                $reportLogoSrc = $inlineLogo;
            }
            if ($inlineSignature) {
                $reportSignatureSrc = $inlineSignature;
            }
            if ($inlineHeader) {
                $reportHeaderImageSrc = $inlineHeader;
            }
            if ($inlineBackground) {
                $reportBackgroundSrc = $inlineBackground;
                $reportBackgroundIsPdf = false;
            }
        }

        $viewData = [
            'specimenTest' => $specimenTest,
            'patient' => $patient,
            'age' => $age,
            'referredBy' => $referredBy,
            'qrDataUri' => $qrDataUri,
            'barcodeDataUri' => $barcodeDataUri,
            'printMode' => $printMode,
            'downloadMode' => $downloadMode,
            'pdfMode' => $downloadMode,
            'reportLogoSrc' => $reportLogoSrc,
            'reportSignatureSrc' => $reportSignatureSrc,
            'reportHeaderImageSrc' => $reportHeaderImageSrc,
            'reportBackgroundSrc' => $reportBackgroundSrc,
            'reportBackgroundIsPdf' => $reportBackgroundIsPdf,
            'canPrint' => $canPrint,
            'settings' => $settings,
            'reportBackgroundDataUri' => $downloadMode && !empty($inlineBackground) ? $inlineBackground : null,
        ];

        if ($downloadMode) {
            set_time_limit(120);
            $options = new Options();
            $options->set('isRemoteEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('reports.lab_report', $viewData)->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'report-' . ($specimenTest->specimen?->specimen_no ?? $specimenTest->id) . '.pdf';

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return view('reports.lab_report', $viewData);
    }

    public function sendReportSms(SpecimenTest $specimenTest): RedirectResponse
    {
        $this->requirePermission('results.validate');

        $specimenTest->load(['specimen.patient', 'specimen.invoice']);
        if (!in_array($specimenTest->status, ['VALIDATED', 'APPROVED'], true)) {
            return redirect()->back()->with('sms_error', 'Report is not approved yet.');
        }

        $specimen = $specimenTest->specimen;
        $patient = $specimen?->patient;
        $phone = $patient?->phone;
        $labId = (int) ($specimenTest->lab_id ?? $specimen?->lab_id ?? auth()->user()?->lab_id ?? 0);

        $settings = Setting::valuesForLab($labId);
        $template = trim((string) ($settings['sms_template_report_ready'] ?? ''));
        if ($template === '') {
            $template = 'Hi {patient_name}, your report is ready.';
        }

        $reportUrl = url()->route('reports.show', $specimenTest);
        $invoiceNo = $specimen?->invoice?->invoice_no ?? '';
        $amount = $specimen?->invoice?->net_total;

        $result = $this->sendReportSmsMessage($phone, $labId, $settings, $template, [
            'patient_name' => $patient?->name ?? 'Patient',
            'specimen_no' => $specimen?->specimen_no ?? '',
            'invoice_no' => $invoiceNo,
            'amount' => $amount !== null ? number_format((float) $amount, 2, '.', '') : '',
            'report_link' => $reportUrl,
        ]);

        if (!$result['ok']) {
            return redirect()->back()->with('sms_error', $result['error'] ?? 'Failed to send SMS.');
        }

        return redirect()->back()->with('sms_status', 'Report SMS sent.');
    }

    public function sendReportLinkSms(SpecimenTest $specimenTest): RedirectResponse
    {
        $this->requirePermission('results.validate');

        $specimenTest->load(['specimen.patient', 'specimen.invoice']);
        if (!in_array($specimenTest->status, ['VALIDATED', 'APPROVED'], true)) {
            return redirect()->back()->with('sms_error', 'Report is not approved yet.');
        }

        $specimen = $specimenTest->specimen;
        $patient = $specimen?->patient;
        $phone = $patient?->phone;
        $labId = (int) ($specimenTest->lab_id ?? $specimen?->lab_id ?? auth()->user()?->lab_id ?? 0);

        $settings = Setting::valuesForLab($labId);
        $template = trim((string) ($settings['sms_template_report_link'] ?? ''));
        if ($template === '') {
            $template = 'Hi {patient_name}, your report is ready: {report_link}';
        }

        $reportUrl = url()->route('reports.show', $specimenTest);
        $invoiceNo = $specimen?->invoice?->invoice_no ?? '';
        $amount = $specimen?->invoice?->net_total;

        $result = $this->sendReportSmsMessage($phone, $labId, $settings, $template, [
            'patient_name' => $patient?->name ?? 'Patient',
            'specimen_no' => $specimen?->specimen_no ?? '',
            'invoice_no' => $invoiceNo,
            'amount' => $amount !== null ? number_format((float) $amount, 2, '.', '') : '',
            'report_link' => $reportUrl,
        ]);

        if (!$result['ok']) {
            return redirect()->back()->with('sms_error', $result['error'] ?? 'Failed to send SMS.');
        }

        return redirect()->back()->with('sms_status', 'Report link SMS sent.');
    }

    public function sendInvoiceSms(SpecimenTest $specimenTest): RedirectResponse
    {
        $this->requirePermission('billing.access');

        $specimenTest->load(['specimen.patient', 'specimen.invoice']);
        $specimen = $specimenTest->specimen;
        if (!$specimen || !$specimen->invoice_id) {
            return redirect()->back()->with('sms_error', 'Invoice not available for this report.');
        }

        $patient = $specimen->patient;
        $phone = $patient?->phone;
        $labId = (int) ($specimenTest->lab_id ?? $specimen?->lab_id ?? auth()->user()?->lab_id ?? 0);

        $settings = Setting::valuesForLab($labId);
        $template = trim((string) ($settings['sms_template_billing'] ?? ''));
        if ($template === '') {
            $template = 'Invoice {invoice_no}: {invoice_link}';
        }

        $invoiceUrl = url()->route('invoice.show', $specimen);
        $invoiceNo = $specimen?->invoice?->invoice_no ?? '';
        $amount = $specimen?->invoice?->net_total;

        $result = $this->sendReportSmsMessage($phone, $labId, $settings, $template, [
            'patient_name' => $patient?->name ?? 'Patient',
            'specimen_no' => $specimen?->specimen_no ?? '',
            'invoice_no' => $invoiceNo,
            'amount' => $amount !== null ? number_format((float) $amount, 2, '.', '') : '',
            'report_link' => $invoiceUrl,
            'invoice_link' => $invoiceUrl,
        ]);

        if (!$result['ok']) {
            return redirect()->back()->with('sms_error', $result['error'] ?? 'Failed to send SMS.');
        }

        return redirect()->back()->with('sms_status', 'Invoice SMS sent.');
    }

    private function sendReportSmsMessage(?string $phone, int $labId, array $settings, string $template, array $data): array
    {
        $phone = trim((string) $phone);
        if ($phone === '') {
            return ['ok' => false, 'error' => 'Patient phone number not available for SMS.'];
        }

        if ($labId) {
            $labSmsEnabled = Lab::query()->whereKey($labId)->value('sms_enabled');
            if ($labSmsEnabled === false || $labSmsEnabled === 0 || $labSmsEnabled === '0') {
                return ['ok' => false, 'error' => 'SMS is disabled for this lab.'];
            }
        }

        if (($settings['sms_enabled'] ?? '1') !== '1') {
            return ['ok' => false, 'error' => 'SMS is disabled for this lab.'];
        }

        $gateway = new SmsGateway();
        $labName = $settings['billing_lab_name'] ?? ($settings['lab_name'] ?? '');
        $data['lab_name'] = $labName !== '' ? $labName : 'Lab';
        $message = $gateway->renderTemplate($template, $data);
        if ($message === '') {
            return ['ok' => false, 'error' => 'SMS template is empty.'];
        }

        return $gateway->send($phone, $message, $settings);
    }

    public function asset(string $type): Response
    {
        $key = match ($type) {
            'logo' => 'report_logo_path',
            'signature' => 'report_signature_path',
            'header' => 'report_header_image_path',
            'background' => 'report_background_path',
            'billing_header' => 'billing_header_image_path',
            'billing_footer' => 'billing_footer_image_path',
            default => null,
        };

        if (!$key) {
            abort(404);
        }

        $settingsQuery = Setting::query()->where('key', $key);
        $labId = request()->query('lab');
        if ($labId !== null) {
            $settingsQuery->where('lab_id', $labId);
        } elseif (auth()->check() && !auth()->user()->isSuperAdmin()) {
            $settingsQuery->where('lab_id', auth()->user()->lab_id);
        } else {
            $settingsQuery->whereNull('lab_id');
        }

        $pathValue = $settingsQuery->value('value');
        if (!$pathValue) {
            abort(404);
        }

        $resolved = $this->resolveReportAssetPath($pathValue);
        if (!$resolved || !is_file($resolved)) {
            abort(404);
        }

        $headers = [
            'Cache-Control' => 'public, max-age=86400',
            'Pragma' => 'public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT',
            'ETag' => '"' . md5_file($resolved) . '"',
        ];

        $response = response()->file($resolved, $headers);
        if ($lastModified = @filemtime($resolved)) {
            $response->setLastModified(\DateTimeImmutable::createFromFormat('U', (string) $lastModified));
        }
        $response->setPublic();
        $response->setMaxAge(86400);
        $response->setSharedMaxAge(86400);

        return $response;
    }

    private function resolveReportAssetPath(string $value): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (str_starts_with($trimmed, '/storage/')) {
            $relative = ltrim(str_replace('/storage/', '', $trimmed), '/');
            $candidate = storage_path('app/public/' . $relative);
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        if (str_starts_with($trimmed, '/')) {
            $candidate = public_path(ltrim($trimmed, '/'));
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $candidate = storage_path('app/public/' . ltrim($trimmed, '/'));
        if (is_file($candidate)) {
            return $candidate;
        }

        return null;
    }

    private function makeReportAssetDataUri(string $type, ?int $labId): ?string
    {
        $key = match ($type) {
            'logo' => 'report_logo_path',
            'signature' => 'report_signature_path',
            'header' => 'report_header_image_path',
            'background' => 'report_background_path',
            'billing_header' => 'billing_header_image_path',
            'billing_footer' => 'billing_footer_image_path',
            default => null,
        };

        if (!$key) {
            return null;
        }

        $settingsQuery = Setting::query()->where('key', $key);
        if ($labId !== null) {
            $settingsQuery->where('lab_id', $labId);
        } elseif (auth()->check() && !auth()->user()->isSuperAdmin()) {
            $settingsQuery->where('lab_id', auth()->user()->lab_id);
        } else {
            $settingsQuery->whereNull('lab_id');
        }

        $pathValue = $settingsQuery->value('value');
        if (!$pathValue) {
            return null;
        }

        $resolved = $this->resolveReportAssetPath($pathValue);
        if (!$resolved || !is_file($resolved)) {
            return null;
        }

        $mime = mime_content_type($resolved) ?: 'image/png';
        if ($mime === 'application/pdf') {
            return null;
        }
        $data = base64_encode(file_get_contents($resolved));

        return 'data:' . $mime . ';base64,' . $data;
    }

    private function buildCode39Svg(string $value): ?string
    {
        $map = [
            '0' => 'nnnwwnwnn',
            '1' => 'wnnwnnnnw',
            '2' => 'nnwwnnnnw',
            '3' => 'wnwwnnnnn',
            '4' => 'nnnwwnnnw',
            '5' => 'wnnwwnnnn',
            '6' => 'nnwwwnnnn',
            '7' => 'nnnwnnwnw',
            '8' => 'wnnwnnwnn',
            '9' => 'nnwwnnwnn',
            'A' => 'wnnnnwnnw',
            'B' => 'nnwnnwnnw',
            'C' => 'wnwnnwnnn',
            'D' => 'nnnnwwnnw',
            'E' => 'wnnnwwnnn',
            'F' => 'nnwnwwnnn',
            'G' => 'nnnnnwwnw',
            'H' => 'wnnnnwwnn',
            'I' => 'nnwnnwwnn',
            'J' => 'nnnnwwwnn',
            'K' => 'wnnnnnnww',
            'L' => 'nnwnnnnww',
            'M' => 'wnwnnnnwn',
            'N' => 'nnnnwnnww',
            'O' => 'wnnnwnnwn',
            'P' => 'nnwnwnnwn',
            'Q' => 'nnnnnnwww',
            'R' => 'wnnnnnwwn',
            'S' => 'nnwnnnwwn',
            'T' => 'nnnnwnwwn',
            'U' => 'wwnnnnnnw',
            'V' => 'nwwnnnnnw',
            'W' => 'wwwnnnnnn',
            'X' => 'nwnnwnnnw',
            'Y' => 'wwnnwnnnn',
            'Z' => 'nwwnwnnnn',
            '-' => 'nwnnnnwnw',
            '.' => 'wwnnnnwnn',
            ' ' => 'nwwnnnwnn',
            '$' => 'nwnwnwnnn',
            '/' => 'nwnwnnnwn',
            '+' => 'nwnnnwnwn',
            '%' => 'nnnwnwnwn',
            '*' => 'nwnnwnwnn',
        ];

        $value = strtoupper(trim($value));
        if ($value === '') {
            return null;
        }

        $encoded = '*' . $value . '*';
        $chars = str_split($encoded);
        $narrow = 2;
        $wide = 6;
        $height = 40;
        $gap = $narrow;
        $quiet = $narrow * 10;

        $bars = [];
        $x = $quiet;
        foreach ($chars as $char) {
            if (!isset($map[$char])) {
                continue;
            }
            $pattern = $map[$char];
            for ($i = 0; $i < 9; $i++) {
                $isBar = $i % 2 === 0;
                $width = $pattern[$i] === 'w' ? $wide : $narrow;
                if ($isBar) {
                    $bars[] = '<rect x="' . $x . '" y="0" width="' . $width . '" height="' . $height . '" fill="#111"/>';
                }
                $x += $width;
            }
            $x += $gap;
        }
        $x += $quiet;

        if (empty($bars)) {
            return null;
        }

        $width = $x;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '" preserveAspectRatio="none">';
        $svg .= implode('', $bars);
        $svg .= '</svg>';

        return $svg;
    }

    private function computeAgeDisplay($dob, $reference): ?string
    {
        if (!$dob) {
            return null;
        }

        $reference = $reference ?? now();
        $diff = $dob->diff($reference);
        if ($diff->invert) {
            return null;
        }

        if ($diff->y > 0) {
            return (string) $diff->y;
        }

        if ($diff->m > 0 && $diff->d > 0) {
            return $diff->m . ' M ' . $diff->d . ' D';
        }

        if ($diff->m > 0) {
            return $diff->m . ' M';
        }

        return $diff->d . ' D';
    }
}

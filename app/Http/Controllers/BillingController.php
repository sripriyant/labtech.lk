<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\LabStockBatch;
use App\Models\LabStockConsumption;
use App\Models\LabStockItem;
use App\Models\Patient;
use App\Models\PromoCode;
use App\Models\ShopProduct;
use App\Models\Specimen;
use App\Models\SpecimenProduct;
use App\Models\SpecimenTest;
use App\Models\TestMaster;
use App\Models\TestStockConsumption;
use App\Models\Setting;
use App\Services\SmsGateway;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $this->requirePermission('billing.access');

        $currentUser = $request->user();
        $canClinicBilling = $this->canClinicBilling($currentUser);

        $tests = TestMaster::query()
            ->where('is_active', true)
            ->where('is_billing_visible', true)
            ->with(['department', 'packageItems'])
            ->orderBy('name')
            ->limit(7)
            ->get();

        $serviceTests = TestMaster::query()
            ->where('is_active', true)
            ->where('is_billing_visible', true)
            ->where(function ($q) {
                $q->where('is_package', true)
                    ->orWhere('code', 'like', 'ECG%')
                    ->orWhere('name', 'like', '%ECG%');
            })
            ->with(['department', 'packageItems.department'])
            ->orderBy('name')
            ->get();

        $products = ShopProduct::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(7)
            ->get();

        $centers = Center::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $doctors = Doctor::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $promoCodes = collect();
        if (Schema::hasTable('promo_codes')) {
            $promoCodes = PromoCode::query()
                ->active()
                ->orderBy('value')
                ->get();
        }

        $billingQuery = Specimen::query()
            ->with(['patient', 'center', 'invoice'])
            ->whereNotNull('invoice_id')
            ->orderByDesc('id');

        $filters = [
            'from' => trim((string) $request->get('from', '')),
            'to' => trim((string) $request->get('to', '')),
            'uhid' => trim((string) $request->get('uhid', '')),
            'nic' => trim((string) $request->get('nic', '')),
            'phone' => trim((string) $request->get('phone', '')),
            'specimen_no' => trim((string) $request->get('specimen_no', '')),
            'invoice_no' => trim((string) $request->get('invoice_no', '')),
            'center' => trim((string) $request->get('center', '')),
            'payment_status' => trim((string) $request->get('payment_status', '')),
            'test' => trim((string) $request->get('test', '')),
            'referral_type' => trim((string) $request->get('referral_type', '')),
            'amount_min' => trim((string) $request->get('amount_min', '')),
            'amount_max' => trim((string) $request->get('amount_max', '')),
            'sort' => trim((string) $request->get('sort', 'date_desc')),
        ];

        if ($filters['from'] !== '' || $filters['to'] !== '') {
            $fromDate = $filters['from'] !== '' ? Carbon::parse($filters['from'])->startOfDay() : null;
            $toDate = $filters['to'] !== '' ? Carbon::parse($filters['to'])->endOfDay() : null;
            if ($fromDate) {
                $billingQuery->where('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $billingQuery->where('created_at', '<=', $toDate);
            }
        }

        if ($filters['uhid'] !== '') {
            $billingQuery->whereHas('patient', function ($q) use ($filters) {
                $q->where('uhid', 'like', $filters['uhid'] . '%');
            });
        }

        if ($filters['nic'] !== '') {
            $billingQuery->whereHas('patient', function ($q) use ($filters) {
                $q->where('nic', 'like', $filters['nic'] . '%');
            });
        }

        if ($filters['phone'] !== '') {
            $billingQuery->whereHas('patient', function ($q) use ($filters) {
                $q->where('phone', 'like', '%' . $filters['phone'] . '%');
            });
        }

        if ($filters['specimen_no'] !== '') {
            $billingQuery->where('specimen_no', 'like', $filters['specimen_no'] . '%');
        }

        if ($filters['invoice_no'] !== '') {
            $billingQuery->whereHas('invoice', function ($q) use ($filters) {
                $q->where('invoice_no', 'like', $filters['invoice_no'] . '%');
            });
        }

        if ($filters['center'] !== '') {
            $billingQuery->whereHas('center', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['center'] . '%')
                    ->orWhere('code', 'like', $filters['center'] . '%');
            });
        }

        if ($filters['payment_status'] !== '') {
            $billingQuery->whereHas('invoice', function ($q) use ($filters) {
                $q->where('payment_status', $filters['payment_status']);
            });
        }

        if ($filters['test'] !== '') {
            $billingQuery->whereHas('products', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['test'] . '%');
            });
        }

        if ($filters['referral_type'] !== '') {
            if ($filters['referral_type'] === 'none') {
                $billingQuery->whereHas('invoice', function ($q) {
                    $q->whereNull('referral_type');
                });
            } else {
                $billingQuery->whereHas('invoice', function ($q) use ($filters) {
                    $q->where('referral_type', $filters['referral_type']);
                });
            }
        }

        $amountMin = is_numeric($filters['amount_min']) ? (float) $filters['amount_min'] : null;
        $amountMax = is_numeric($filters['amount_max']) ? (float) $filters['amount_max'] : null;
        if ($amountMin !== null || $amountMax !== null) {
            $billingQuery->whereHas('invoice', function ($q) use ($amountMin, $amountMax) {
                if ($amountMin !== null) {
                    $q->where('net_total', '>=', $amountMin);
                }
                if ($amountMax !== null) {
                    $q->where('net_total', '<=', $amountMax);
                }
            });
        }

        $allowedSorts = [
            'date_desc',
            'date_asc',
            'specimen_asc',
            'specimen_desc',
            'patient_asc',
            'patient_desc',
            'invoice_asc',
            'invoice_desc',
            'amount_asc',
            'amount_desc',
        ];
        $sort = in_array($filters['sort'], $allowedSorts, true) ? $filters['sort'] : 'date_desc';
        $filters['sort'] = $sort;

        if (in_array($sort, ['patient_asc', 'patient_desc', 'invoice_asc', 'invoice_desc', 'amount_asc', 'amount_desc'], true)) {
            $billingQuery->leftJoin('patients', 'specimens.patient_id', '=', 'patients.id')
                ->leftJoin('invoices', 'specimens.invoice_id', '=', 'invoices.id')
                ->select('specimens.*');
        }

        switch ($sort) {
            case 'date_asc':
                $billingQuery->orderBy('specimens.created_at', 'asc');
                break;
            case 'specimen_asc':
                $billingQuery->orderBy('specimens.specimen_no', 'asc');
                break;
            case 'specimen_desc':
                $billingQuery->orderBy('specimens.specimen_no', 'desc');
                break;
            case 'patient_asc':
                $billingQuery->orderBy('patients.name', 'asc');
                break;
            case 'patient_desc':
                $billingQuery->orderBy('patients.name', 'desc');
                break;
            case 'invoice_asc':
                $billingQuery->orderBy('invoices.invoice_no', 'asc');
                break;
            case 'invoice_desc':
                $billingQuery->orderBy('invoices.invoice_no', 'desc');
                break;
            case 'amount_asc':
                $billingQuery->orderBy('invoices.net_total', 'asc');
                break;
            case 'amount_desc':
                $billingQuery->orderBy('invoices.net_total', 'desc');
                break;
            default:
                $billingQuery->orderBy('specimens.created_at', 'desc');
                break;
        }

        $billingRows = $billingQuery->limit(500)->get();

        $activeTab = $request->get('tab', 'create');
        if (!in_array($activeTab, ['create', 'clinic'], true)) {
            $activeTab = 'create';
        }
        if ($activeTab === 'clinic' && !$canClinicBilling) {
            $activeTab = 'create';
        }

        return view('billing.index', [
            'tests' => $tests,
            'serviceTests' => $serviceTests,
            'products' => $products,
            'centers' => $centers,
            'doctors' => $doctors,
            'promoCodes' => $promoCodes,
            'billingRows' => $billingRows,
            'billingFilters' => $filters,
            'activeTab' => $activeTab,
            'canClinicBilling' => $canClinicBilling,
        ]);
    }

    public function searchTests(Request $request)
    {
        $this->requirePermission('billing.access');

        $query = trim((string) $request->get('q', ''));

        $tests = TestMaster::query()
            ->where('is_active', true)
            ->where('is_billing_visible', true)
            ->with(['department', 'packageItems.department'])
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('code', 'like', $query . '%')
                        ->orWhere('name', 'like', '%' . $query . '%');
                });
            })
            ->orderBy('name')
            ->limit($query === '' ? 7 : 50)
            ->get();

        $payload = $tests->map(function ($test) {
            return [
                'id' => $test->id,
                'code' => $test->code,
                'name' => $test->name,
                'price' => number_format($test->price ?? 0, 2, '.', ''),
                'location' => $test->department?->name ?? '-',
                'is_package' => (bool) $test->is_package,
                'package_items' => $test->is_package
                    ? $test->packageItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'code' => $item->code,
                            'name' => $item->name,
                            'price' => number_format($item->price ?? 0, 2, '.', ''),
                            'location' => $item->department?->name ?? '-',
                        ];
                    })->values()
                    : [],
            ];
        });

        return Response::json($payload);
    }

    public function searchProducts(Request $request)
    {
        $this->requirePermission('billing.access');

        $query = trim((string) $request->get('q', ''));

        $products = ShopProduct::query()
            ->where('is_active', true)
            ->with('category')
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('name', 'like', '%' . $query . '%')
                        ->orWhere('category', 'like', '%' . $query . '%');
                });
            })
            ->orderBy('name')
            ->limit($query === '' ? 7 : 50)
            ->get();

        $payload = $products->map(function ($product) {
            $category = $product->category?->name ?? $product->category ?? '-';
            return [
                'id' => $product->id,
                'code' => 'P' . str_pad((string) $product->id, 5, '0', STR_PAD_LEFT),
                'name' => $product->name,
                'price' => number_format($product->price ?? 0, 2, '.', ''),
                'location' => $category,
            ];
        });

        return Response::json($payload);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('billing.create');

        $currentUser = $request->user();
        $labId = $currentUser?->lab_id;
        $limitToLab = $currentUser && !$currentUser->isSuperAdmin() && $labId;
        $billingMode = $request->input('billing_mode') === 'clinic' ? 'clinic' : 'test';
        if ($billingMode === 'clinic' && !$this->canClinicBilling($currentUser)) {
            abort(403);
        }

        $rules = [
            'patient_id' => ['nullable', 'integer', Rule::exists('patients', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'nic' => ['nullable', 'string', 'max:50'],
            'sex' => ['required', 'string', 'in:Male,Female'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'age_years' => ['nullable', 'integer', 'min:0', 'max:130'],
            'age_months' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_days' => ['nullable', 'integer', 'min:0', 'max:366'],
            'age_unit' => ['nullable', 'string', 'in:Y,M,D,MD'],
            'center_id' => [
                'nullable',
                'integer',
                $limitToLab ? Rule::exists('centers', 'id')->where('lab_id', $labId) : Rule::exists('centers', 'id'),
            ],
            'referral_type' => ['nullable', 'in:doctor,center'],
            'referral_doctor_id' => ['nullable', 'integer'],
            'referral_center_id' => ['nullable', 'integer'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['nullable', 'in:CASH,CREDIT,CARD,BANK'],
            'card_transaction_id' => ['nullable', 'string', 'max:100'],
            'bank_slip_no' => ['nullable', 'string', 'max:100'],
            'auto_print' => ['nullable', 'in:0,1'],
            'show_invoice' => ['nullable', 'in:0,1'],
            'show_invoice_no' => ['nullable', 'in:0,1'],
            'send_billing_sms' => ['nullable', 'in:0,1'],
        ];

        if (Schema::hasTable('promo_codes')) {
            $rules['promo_code_id'] = ['nullable', 'integer', 'exists:promo_codes,id'];
        }

        if ($billingMode === 'clinic') {
            $rules['products'] = ['required', 'array', 'min:1'];
            $rules['products.*'] = ['integer', 'exists:shop_products,id'];
        } else {
            $rules['tests'] = ['required', 'array', 'min:1'];
            $rules['tests.*'] = ['integer', 'exists:test_masters,id'];
        }

        $referralType = $request->input('referral_type') ?: null;
        if ($referralType === 'doctor') {
            $rules['referral_doctor_id'] = [
                'required',
                'integer',
                $limitToLab ? Rule::exists('doctors', 'id')->where('lab_id', $labId) : Rule::exists('doctors', 'id'),
            ];
        } elseif ($referralType === 'center') {
            $rules['referral_center_id'] = [
                'required',
                'integer',
                $limitToLab ? Rule::exists('centers', 'id')->where('lab_id', $labId) : Rule::exists('centers', 'id'),
            ];
        }

        $data = $request->validate($rules);
        $ageUnit = $data['age_unit'] ?? 'Y';
        $ageYears = $data['age_years'] ?? null;
        $ageMonths = $data['age_months'] ?? null;
        $ageDays = $data['age_days'] ?? null;
        $hasAge = match ($ageUnit) {
            'Y' => $ageYears !== null,
            'M' => $ageMonths !== null,
            'D' => $ageDays !== null,
            'MD' => ($ageMonths !== null || $ageDays !== null),
            default => $ageYears !== null,
        };
        if (!$hasAge) {
            throw ValidationException::withMessages([
                'age_years' => ['Age is required.'],
            ]);
        }
        if (!Schema::hasTable('promo_codes')) {
            $data['promo_code_id'] = null;
        }

        $userId = auth()->id();
        $specimenId = null;
        $specimenNo = null;
        $invoiceNo = null;
        $patientPhone = null;
        $patientName = null;
        $netTotal = null;
        $stockWarnings = [];

        DB::transaction(function () use ($data, $billingMode, $userId, $referralType, $ageUnit, $ageYears, $ageMonths, $ageDays, &$specimenId, &$specimenNo, &$stockWarnings, &$invoiceNo, &$patientPhone, &$patientName, &$netTotal) {
            $patient = null;
            if (!empty($data['patient_id'])) {
                $patient = Patient::query()->find($data['patient_id']);
                if ($patient) {
                    $patient->fill([
                        'name' => $data['name'],
                        'nic' => $data['nic'] ?? null,
                        'sex' => $data['sex'] ?? null,
                        'phone' => $data['phone'] ?? null,
                        'email' => $data['email'] ?? null,
                        'nationality' => $data['nationality'] ?? null,
                        'updated_by' => $userId,
                    ]);
                    $patient->save();
                }
            }

            if (!$patient) {
                $patient = Patient::create([
                    'uhid' => $this->generateCode('UH'),
                    'name' => $data['name'],
                    'nic' => $data['nic'] ?? null,
                    'sex' => $data['sex'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            $specimen = Specimen::create([
                'specimen_no' => $this->generateCode('SP'),
                'patient_id' => $patient->id,
                'age_years' => $ageYears,
                'age_months' => $ageMonths,
                'age_days' => $ageDays,
                'age_unit' => $ageUnit,
                'center_id' => $data['center_id'] ?? null,
                'status' => 'CREATED',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $specimenId = $specimen->id;
            $specimenNo = $specimen->specimen_no;

            $total = 0;
            if ($billingMode === 'clinic') {
                $products = ShopProduct::query()
                    ->whereIn('id', $data['products'])
                    ->get()
                    ->keyBy('id');

                foreach ($data['products'] as $productId) {
                    $product = $products->get($productId);
                    $price = $product?->price ?? 0;
                    SpecimenProduct::create([
                        'specimen_id' => $specimen->id,
                        'shop_product_id' => $productId,
                        'name' => $product?->name ?? 'Product',
                        'price' => $price,
                        'quantity' => 1,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                    $total += $price;
                }
            } else {
                $tests = TestMaster::query()
                    ->whereIn('id', $data['tests'])
                    ->get()
                    ->keyBy('id');

                foreach ($data['tests'] as $testId) {
                    $test = $tests->get($testId);
                    $price = $test?->price ?? 0;
                    $specimenTest = SpecimenTest::create([
                        'specimen_id' => $specimen->id,
                        'test_master_id' => $testId,
                        'price' => $price,
                        'status' => 'ORDERED',
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                    $warnings = $this->consumeLabStock($specimenTest);
                    if (!empty($warnings)) {
                        $stockWarnings = array_merge($stockWarnings, $warnings);
                    }
                    $total += $price;
                }
            }

            $vat = 0;
            $patientDiscount = (float) ($data['discount'] ?? 0);
            $grossTotal = max($total + $vat, 0);
            $promoCodeId = $data['promo_code_id'] ?? null;
            $promoDiscount = 0;

            if ($promoCodeId && Schema::hasTable('promo_codes')) {
                $promo = PromoCode::query()->find($promoCodeId);
                if (!$promo || !$promo->is_active) {
                    $promoCodeId = null;
                } else {
                    $today = now()->toDateString();
                    if (($promo->starts_at && $promo->starts_at->toDateString() > $today) ||
                        ($promo->ends_at && $promo->ends_at->toDateString() < $today)) {
                        $promoCodeId = null;
                    } elseif ($promo->max_uses !== null && $promo->usage_count >= $promo->max_uses) {
                        $promoCodeId = null;
                    } else {
                        if ($promo->type === 'PERCENT') {
                            $promoDiscount = round($grossTotal * ((float) $promo->value / 100), 2);
                        } else {
                            $promoDiscount = (float) $promo->value;
                        }
                        $promoDiscount = min($promoDiscount, $grossTotal);
                        $promo->increment('usage_count');
                    }
                }
            }

            $maxDiscount = $grossTotal;
            if ($patientDiscount > $maxDiscount) {
                $patientDiscount = $maxDiscount;
            }
            $netTotalAmount = max($grossTotal - $patientDiscount - $promoDiscount, 0);
            $netTotal = $netTotalAmount;

            $referralId = null;
            if ($referralType === 'doctor') {
                $referralId = $data['referral_doctor_id'] ?? null;
            } elseif ($referralType === 'center') {
                $referralId = $data['referral_center_id'] ?? null;
            }

            $referralPercent = 0;
            if ($referralType === 'doctor' && $referralId) {
                $referralPercent = (float) Doctor::query()->whereKey($referralId)->value('referral_discount_pct');
            } elseif ($referralType === 'center' && $referralId) {
                $referralPercent = (float) Center::query()->whereKey($referralId)->value('referral_discount_pct');
            }

            $referralDiscount = 0;
            if ($referralPercent > 0) {
                $referralDiscount = round($netTotal * ($referralPercent / 100), 2);
            }

            $invoice = Invoice::create([
                'invoice_no' => $this->generateCode('INV'),
                'patient_id' => $patient->id,
                'center_id' => $data['center_id'] ?? null,
                'promo_code_id' => $promoCodeId,
                'referral_type' => $referralType,
                'referral_id' => $referralId,
                'total' => $total,
                'discount' => $patientDiscount,
                'promo_discount' => $promoDiscount,
                'referral_discount' => $referralDiscount,
                'vat' => $vat,
                'net_total' => $netTotalAmount,
                'payment_status' => 'UNPAID',
                'payment_mode' => $data['payment_mode'] ?? null,
                'card_transaction_id' => $data['card_transaction_id'] ?? null,
                'bank_slip_no' => $data['bank_slip_no'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $invoiceNo = $invoice->invoice_no;
            $patientPhone = $patient->phone ?? null;
            $patientName = $patient->name ?? null;

            $specimen->update([
                'invoice_id' => $invoice->id,
            ]);

            if ($referralType && $referralId && $referralDiscount > 0) {
                DB::table('referral_commissions')->insert([
                    'invoice_id' => $invoice->id,
                    'referral_type' => $referralType,
                    'referral_id' => $referralId,
                    'referral_percent' => $referralPercent,
                    'amount' => $referralDiscount,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $autoPrint = $request->get('auto_print') === '1';
        $showInvoice = $request->get('show_invoice') === '1';
        $showInvoiceNo = $request->get('show_invoice_no') === '1';
        $sendBillingSms = $request->get('send_billing_sms') === '1';

        $openInvoiceUrl = null;
        if ($request->has('print_invoice') && $specimenId) {
            $openInvoiceUrl = route('billing.print', ['specimen' => $specimenId]);
        } elseif ($autoPrint && $specimenId) {
            $openInvoiceUrl = route('billing.print', ['specimen' => $specimenId, 'auto' => 1]);
        } elseif ($showInvoice && $specimenId) {
            $openInvoiceUrl = route('billing.print', ['specimen' => $specimenId]);
        }

        $statusMessage = 'Registered patient successfully';
        if ($showInvoiceNo && $invoiceNo) {
            $statusMessage .= ' (Invoice No: ' . $invoiceNo . ')';
        }

        $redirect = redirect()->route('billing.index');
        if (!empty($openInvoiceUrl)) {
            $redirect->with('open_invoice_url', $openInvoiceUrl);
        }
        if ($sendBillingSms) {
            $invoiceLink = $specimenId ? url()->route('invoice.show', ['specimen' => $specimenId]) : '';
            $smsResult = $this->sendBillingSms($patientPhone, [
                'patient_name' => $patientName ?? 'Patient',
                'specimen_no' => $specimenNo ?? '',
                'invoice_no' => $invoiceNo ?? '',
                'amount' => $netTotal !== null ? number_format((float) $netTotal, 2, '.', '') : '',
                'report_link' => '',
                'invoice_link' => $invoiceLink,
            ]);
            if (!$smsResult['ok']) {
                $redirect->with('sms_error', $smsResult['error'] ?? 'Failed to send SMS.');
            } else {
                $redirect->with('sms_status', 'Billing SMS sent.');
            }
        }

        if (!empty($stockWarnings)) {
            return $redirect
                ->with('stock_warnings', $stockWarnings)
                ->with('status', $statusMessage);
        }

        return $redirect->with('status', $statusMessage);
    }

    private function generateCode(string $prefix): string
    {
        return $prefix . Str::upper(Str::random(10));
    }

    private function consumeLabStock(SpecimenTest $specimenTest): array
    {
        $warnings = [];
        $rules = TestStockConsumption::query()
            ->where('test_master_id', $specimenTest->test_master_id)
            ->get();

        if ($rules->isEmpty()) {
            return $warnings;
        }

        foreach ($rules as $rule) {
            $required = (float) $rule->quantity_per_test;
            if ($required <= 0) {
                continue;
            }

            $remaining = $required;

            $batches = LabStockBatch::query()
                ->where('lab_stock_item_id', $rule->lab_stock_item_id)
                ->where('remaining_qty', '>', 0)
                ->orderByRaw('expiry_date is null, expiry_date asc')
                ->orderBy('purchase_date')
                ->get();

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }
                $take = min($remaining, (float) $batch->remaining_qty);
                if ($take <= 0) {
                    continue;
                }
                $batch->remaining_qty = (float) $batch->remaining_qty - $take;
                $batch->save();
                $remaining -= $take;
            }

            $consumedQty = $required - $remaining;
            if ($consumedQty > 0) {
                LabStockConsumption::create([
                    'lab_stock_item_id' => $rule->lab_stock_item_id,
                    'test_master_id' => $specimenTest->test_master_id,
                    'specimen_test_id' => $specimenTest->id,
                    'quantity' => $consumedQty,
                    'consumed_at' => now(),
                ]);
            }
            if ($remaining > 0) {
                $itemName = LabStockItem::query()->whereKey($rule->lab_stock_item_id)->value('name') ?? 'Item';
                $warnings[] = $itemName . ' stock is insufficient by ' . number_format($remaining, 2) . '.';
            }
        }

        return $warnings;
    }

    private function canClinicBilling($user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->isSuperAdmin()) {
            return true;
        }
        return method_exists($user, 'hasPermission')
            ? $user->hasPermission('clinic.billing')
            : false;
    }

    public function searchPatients(Request $request)
    {
        $this->requirePermission('billing.access');

        $query = trim((string) $request->get('q', ''));

        if ($query === '') {
            return Response::json([]);
        }

        $patients = Patient::query()
            ->where(function ($q) use ($query) {
                $q->where('uhid', 'like', $query . '%')
                    ->orWhere('nic', 'like', $query . '%')
                    ->orWhere('phone', 'like', '%' . $query . '%')
                    ->orWhere('name', 'like', '%' . $query . '%');
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'uhid', 'name', 'nic', 'sex', 'phone', 'email', 'nationality']);

        return Response::json($patients);
    }

    public function findSpecimen(Request $request)
    {
        $this->requirePermission('billing.access');

        $specimenNo = trim((string) $request->get('specimen_no', ''));

        if ($specimenNo === '') {
            return Response::json(null);
        }

        $specimen = Specimen::query()
            ->where('specimen_no', $specimenNo)
            ->with('patient')
            ->first();

        if (!$specimen) {
            return Response::json(null);
        }

        return Response::json([
            'specimen_no' => $specimen->specimen_no,
            'patient' => [
                'id' => $specimen->patient->id,
                'name' => $specimen->patient->name,
                'nic' => $specimen->patient->nic,
                'sex' => $specimen->patient->sex,
                'phone' => $specimen->patient->phone,
                'email' => $specimen->patient->email,
                'nationality' => $specimen->patient->nationality,
                'age_display' => $specimen->age_display,
                'age_unit' => $specimen->age_unit,
                'age_years' => $specimen->age_years,
                'age_months' => $specimen->age_months,
                'age_days' => $specimen->age_days,
            ],
        ]);
    }

    public function printSpecimen(Specimen $specimen): View
    {
        $this->requirePermission('billing.access');

        $specimen->load(['patient', 'center', 'products.product', 'tests.testMaster', 'invoice']);

        return view('billing.print', [
            'specimen' => $specimen,
        ]);
    }

    public function publicInvoice(Specimen $specimen): View
    {
        $specimen->load(['patient', 'center', 'products.product', 'tests.testMaster', 'invoice']);

        if (!$specimen->invoice_id) {
            abort(404);
        }

        return view('billing.print', [
            'specimen' => $specimen,
        ]);
    }

    public function printList(Request $request): View
    {
        $this->requirePermission('billing.access');

        $specimenNo = trim((string) $request->get('specimen_no', ''));
        $nic = trim((string) $request->get('nic', ''));
        $from = trim((string) $request->get('from', ''));
        $to = trim((string) $request->get('to', ''));
        $sort = trim((string) $request->get('sort', 'date_desc'));

        $query = Specimen::query()
            ->with(['patient', 'center'])
            ->orderByDesc('id');

        if ($specimenNo !== '') {
            $query->where('specimen_no', 'like', $specimenNo . '%');
        }

        if ($nic !== '') {
            $query->whereHas('patient', function ($q) use ($nic) {
                $q->where('nic', 'like', $nic . '%');
            });
        }

        if ($from !== '' || $to !== '') {
            $fromDate = $from !== '' ? \Carbon\Carbon::parse($from)->startOfDay() : null;
            $toDate = $to !== '' ? \Carbon\Carbon::parse($to)->endOfDay() : null;
            if ($fromDate) {
                $query->where('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $query->where('created_at', '<=', $toDate);
            }
        }

        $allowedSorts = [
            'date_desc',
            'date_asc',
            'specimen_asc',
            'specimen_desc',
            'patient_asc',
            'patient_desc',
        ];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'date_desc';
        }

        if (in_array($sort, ['patient_asc', 'patient_desc'], true)) {
            $query->leftJoin('patients', 'specimens.patient_id', '=', 'patients.id')
                ->select('specimens.*');
        }

        switch ($sort) {
            case 'date_asc':
                $query->orderBy('specimens.created_at', 'asc');
                break;
            case 'specimen_asc':
                $query->orderBy('specimens.specimen_no', 'asc');
                break;
            case 'specimen_desc':
                $query->orderBy('specimens.specimen_no', 'desc');
                break;
            case 'patient_asc':
                $query->orderBy('patients.name', 'asc');
                break;
            case 'patient_desc':
                $query->orderBy('patients.name', 'desc');
                break;
            default:
                $query->orderBy('specimens.created_at', 'desc');
                break;
        }

        $specimens = $query->limit(500)->get();

        return view('billing.print_list', [
            'specimens' => $specimens,
            'filters' => [
                'specimen_no' => $specimenNo,
                'nic' => $nic,
                'from' => $from,
                'to' => $to,
                'sort' => $sort,
            ],
        ]);
    }

    private function sendBillingSms(?string $phone, array $data): array
    {
        $phone = trim((string) $phone);
        if ($phone === '') {
            return ['ok' => false, 'error' => 'Patient phone number not available for SMS.'];
        }

        $user = auth()->user();
        $labId = $user?->lab_id;
        if ($labId) {
            $labSmsEnabled = \App\Models\Lab::query()->whereKey($labId)->value('sms_enabled');
            if ($labSmsEnabled === false || $labSmsEnabled === 0 || $labSmsEnabled === '0') {
                return ['ok' => false, 'error' => 'SMS is disabled for this lab.'];
            }
        }
        $settings = Setting::valuesForLab((int) $labId);

        if (($settings['sms_enabled'] ?? '1') !== '1') {
            return ['ok' => false, 'error' => 'SMS is disabled for this lab.'];
        }

        $template = trim((string) ($settings['sms_template_billing'] ?? ''));
        if ($template === '') {
            $template = 'Dear {patient_name}, {lab_name} invoice {invoice_no} amount {amount}.';
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
}

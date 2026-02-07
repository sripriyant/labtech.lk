<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Center;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Lab;
use App\Models\DemoAccount;
use App\Models\LabStockBatch;
use App\Models\LabStockConsumption;
use App\Models\LabStockItem;
use App\Models\Patient;
use App\Models\SpecimenTest;
use App\Models\TestMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('admin.dashboard');

        $currentUser = request()->user();
        $isSuperAdmin = $currentUser && $currentUser->isSuperAdmin();

        $counts = [
            'departments' => Schema::hasTable('departments') ? Department::count() : 0,
            'centers' => Schema::hasTable('centers') ? Center::count() : 0,
            'doctors' => Schema::hasTable('doctors') ? Doctor::count() : 0,
            'tests' => Schema::hasTable('test_masters') ? TestMaster::count() : 0,
            'banners' => Schema::hasTable('banners') ? Banner::count() : 0,
            'patients' => Schema::hasTable('patients') ? Patient::count() : 0,
        ];

        $statusCounts = [
            'ordered' => 0,
            'entered' => 0,
            'validated' => 0,
            'approved' => 0,
        ];

        $dailyLabels = [];
        $dailyReceived = [];
        $dailyApproved = [];
        $departmentLoad = [];
        $stockStats = [
            'low_stock' => 0,
            'expiring' => 0,
            'today_consumption' => 0,
            'stock_value' => 0,
            'stock_count' => 0,
        ];
        $metrics = [
            'total_tests' => 0,
            'today_tests' => 0,
            'total_registered_tests' => 0,
            'local_tests' => 0,
            'outsource_tests' => 0,
            'approved' => 0,
            'pending_approval' => 0,
            'rejected' => 0,
            'pending_payment' => 0,
            'printed_reports' => 0,
            'pending_print' => 0,
            'short_expire' => 0,
            'collection_center_income' => 0,
            'low_stock_items' => collect(),
            'samples_today' => 0,
            'reports_released_today' => 0,
            'pending_samples' => 0,
            'tat_median_minutes' => null,
            'tat_p90_minutes' => null,
            'tat_p95_minutes' => null,
            'rejection_rate' => 0,
            'qc_alerts' => 0,
            'critical_results_today' => 0,
            'low_stock_count' => 0,
            'monthly_revenue' => 0,
            'tests_completed_today' => 0,
            'tests_completed_month' => 0,
            'pending_reports' => 0,
            'abnormal_results' => 0,
            'rejected_results' => 0,
            'reports_printed_today' => 0,
            'reports_sent_email' => 0,
            'reports_sent_whatsapp' => 0,
            'qr_verified_reports' => 0,
            'reprint_requests' => 0,
        ];

        $billingStats = [
            'total_invoices' => 0,
            'paid' => 0,
            'unpaid' => 0,
            'overdue' => 0,
            'status_counts' => [],
            'monthly_trend' => [],
        ];

        $workflowStats = [
            'awaiting_validation' => 0,
            'validated_reports' => 0,
            'samples_received' => 0,
            'tests_in_progress' => 0,
        ];

        $testAnalytics = [
            'category_split' => [],
            'most_requested' => collect(),
            'heatmap' => array_fill(0, 24, 0),
            'daily_counts' => [],
            'weekly_counts' => [],
            'monthly_counts' => [],
            'department_distribution' => [],
        ];

        $patientAnalytics = [
            'new_vs_returning' => ['new' => 0, 'returning' => 0],
            'age_groups' => [
                '0-12' => 0,
                '13-19' => 0,
                '20-35' => 0,
                '36-50' => 0,
                '51-65' => 0,
                '66+' => 0,
            ],
            'gender_ratio' => [],
            'opd_vs_referral' => ['opd' => 0, 'referral' => 0],
        ];

        $advancedStats = [
            'tat_median_minutes' => null,
            'tat_p90_minutes' => null,
            'tat_p95_minutes' => null,
            'doctor_referrals' => collect(),
            'machine_utilization' => [],
            'reagent_consumption' => [],
            'multi_branch' => collect(),
        ];


        $overviewCounts = [
            'patients_today' => 0,
            'samples_collected' => 0,
            'tests_completed' => 0,
            'pending_tests' => 0,
            'monthly_revenue' => 0,
            'outstanding_payments' => 0,
            'centre_income' => 0,
            'commissions' => 0,
        ];

        $alerts = [
            'critical_results' => 0,
            'delayed_samples' => 0,
            'qc_failed_tests' => 0,
            'low_reagent_stock' => 0,
        ];

        $recentActivity = [
            'validated_reports' => collect(),
            'created_invoices' => collect(),
            'rejections' => collect(),
        ];

        $userView = [
            'doctor_pending_validations' => 0,
            'doctor_critical_results' => 0,
            'technician_sample_queue' => 0,
            'technician_test_queue' => 0,
            'admin_revenue' => 0,
            'admin_stock' => 0,
        ];

        $systemStatus = [
            'analyzer_connection' => 'Unknown',
            'lis_status' => 'Online',
            'backup_status' => 'Unknown',
        ];
        $patientStats = [
            'filters' => [
                'from' => null,
                'to' => null,
                'group' => 'day',
                'q' => '',
            ],
            'trendRows' => collect(),
            'specimens' => collect(),
            'totals' => [
                'patients' => 0,
                'specimens' => 0,
                'tests' => 0,
            ],
        ];

        if (Schema::hasTable('specimen_tests')) {
            $statusCounts['ordered'] = SpecimenTest::whereIn('status', ['ORDERED', 'REJECTED'])->count();
            $statusCounts['entered'] = SpecimenTest::where('status', 'RESULT_ENTERED')->count();
            $statusCounts['validated'] = SpecimenTest::where('status', 'VALIDATED')->count();
            $statusCounts['approved'] = SpecimenTest::where('status', 'APPROVED')->count();

            $metrics['total_tests'] = SpecimenTest::count();
            $metrics['today_tests'] = SpecimenTest::whereDate('created_at', today())->count();
            $metrics['approved'] = $statusCounts['approved'];
            $metrics['pending_approval'] = $statusCounts['validated'];
            $metrics['rejected'] = SpecimenTest::where('status', 'REJECTED')->count();
            $metrics['pending_samples'] = SpecimenTest::whereIn('status', ['ORDERED', 'RESULT_ENTERED', 'VALIDATED'])->count();
            $metrics['tests_completed_today'] = SpecimenTest::where('status', 'APPROVED')
                ->whereDate('updated_at', today())
                ->count();
            $metrics['tests_completed_month'] = SpecimenTest::where('status', 'APPROVED')
                ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
            $overviewCounts['tests_completed'] = $metrics['tests_completed_today'];
            $overviewCounts['pending_tests'] = $metrics['pending_samples'];
            $metrics['pending_reports'] = SpecimenTest::whereIn('status', ['RESULT_ENTERED', 'VALIDATED'])->count();
            $metrics['rejected_results'] = SpecimenTest::where('status', 'REJECTED')->count();

            if (Schema::hasTable('test_masters')) {
                $metrics['total_registered_tests'] = TestMaster::count();
                $metrics['local_tests'] = SpecimenTest::query()
                    ->join('test_masters', 'specimen_tests.test_master_id', '=', 'test_masters.id')
                    ->where('test_masters.is_outsource', false)
                    ->count();
                $metrics['outsource_tests'] = SpecimenTest::query()
                    ->join('test_masters', 'specimen_tests.test_master_id', '=', 'test_masters.id')
                    ->where('test_masters.is_outsource', true)
                    ->count();
            }

            if (Schema::hasColumn('specimen_tests', 'printed_at')) {
                $metrics['printed_reports'] = SpecimenTest::whereNotNull('printed_at')->count();
                $metrics['pending_print'] = SpecimenTest::where('status', 'APPROVED')
                    ->whereNull('printed_at')
                    ->count();
                $metrics['reports_printed_today'] = SpecimenTest::whereNotNull('printed_at')
                    ->whereDate('printed_at', today())
                    ->count();
            }

            $days = collect(range(6, 0))->map(function ($offset) {
                return Carbon::today()->subDays($offset);
            });

            $dailyLabels = $days->map(fn ($d) => $d->format('d M'))->all();

            foreach ($days as $day) {
                $dailyReceived[] = SpecimenTest::whereDate('created_at', $day)->count();
                $dailyApproved[] = SpecimenTest::whereDate('updated_at', $day)
                    ->where('status', 'APPROVED')
                    ->count();
            }

            if (Schema::hasTable('test_masters') && Schema::hasTable('departments')) {
                $departmentLoad = SpecimenTest::query()
                    ->select('departments.name', DB::raw('count(*) as total'))
                    ->join('test_masters', 'specimen_tests.test_master_id', '=', 'test_masters.id')
                    ->join('departments', 'test_masters.department_id', '=', 'departments.id')
                    ->groupBy('departments.name')
                    ->orderByDesc('total')
                    ->limit(8)
                    ->get()
                    ->map(fn ($row) => ['name' => $row->name, 'total' => (int) $row->total])
                    ->all();

                $testAnalytics['category_split'] = SpecimenTest::query()
                    ->select('departments.name', DB::raw('count(*) as total'))
                    ->join('test_masters', 'specimen_tests.test_master_id', '=', 'test_masters.id')
                    ->join('departments', 'test_masters.department_id', '=', 'departments.id')
                    ->groupBy('departments.name')
                    ->orderByDesc('total')
                    ->get();
            }
        }

        if (Schema::hasTable('invoices')) {
            $metrics['pending_payment'] = Invoice::query()
                ->where('payment_status', '!=', 'PAID')
                ->count();

            $metrics['monthly_revenue'] = (float) Invoice::query()
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('net_total');

            $billingStats['total_invoices'] = Invoice::count();
            $billingStats['paid'] = Invoice::where('payment_status', 'PAID')->count();
            $billingStats['unpaid'] = Invoice::where('payment_status', '!=', 'PAID')->count();
            $billingStats['overdue'] = Invoice::where('payment_status', '!=', 'PAID')
                ->where('created_at', '<=', now()->subDays(30))
                ->count();
            $billingStats['status_counts'] = Invoice::select('payment_status', DB::raw('COUNT(*) as total'))
                ->groupBy('payment_status')
                ->get();

            $billingStats['monthly_trend'] = collect(range(5, 0))->map(function ($offset) {
                $start = now()->subMonths($offset)->startOfMonth();
                $end = now()->subMonths($offset)->endOfMonth();
                return [
                    'label' => $start->format('M'),
                    'total' => (float) Invoice::whereBetween('created_at', [$start, $end])->sum('net_total'),
                ];
            });
        }

        if (Schema::hasTable('referral_commissions')) {
            $metrics['collection_center_income'] = (float) DB::table('referral_commissions')
                ->where('referral_type', 'center')
                ->sum('amount');
            $overviewCounts['centre_income'] = $metrics['collection_center_income'];
            $overviewCounts['commissions'] = (float) DB::table('referral_commissions')->sum('amount');
        }

        if (Schema::hasTable('lab_stock_items')) {
            $lowStock = DB::table('lab_stock_items')
                ->leftJoin('lab_stock_batches', 'lab_stock_items.id', '=', 'lab_stock_batches.lab_stock_item_id')
                ->select(
                    'lab_stock_items.id',
                    'lab_stock_items.reorder_level',
                    DB::raw('COALESCE(SUM(lab_stock_batches.remaining_qty),0) as total_qty')
                )
                ->groupBy('lab_stock_items.id', 'lab_stock_items.reorder_level')
                ->havingRaw('COALESCE(SUM(lab_stock_batches.remaining_qty),0) <= lab_stock_items.reorder_level')
                ->count();

            $expiring = LabStockBatch::query()
                ->whereNotNull('expiry_date')
                ->where('remaining_qty', '>', 0)
                ->where('expiry_date', '<=', now()->addDays(30)->toDateString())
                ->count();

            $todayConsumption = LabStockConsumption::query()
                ->whereDate('consumed_at', today())
                ->sum('quantity');

            $stockValue = LabStockBatch::query()
                ->sum(DB::raw('remaining_qty * unit_cost'));

            $stockCount = LabStockBatch::query()
                ->sum('remaining_qty');

            $stockStats = [
                'low_stock' => (int) $lowStock,
                'expiring' => (int) $expiring,
                'today_consumption' => (float) $todayConsumption,
                'stock_value' => (float) $stockValue,
                'stock_count' => (float) $stockCount,
            ];

            $metrics['short_expire'] = (int) $expiring;
            $metrics['low_stock_count'] = (int) $lowStock;
            $alerts['low_reagent_stock'] = (int) $lowStock;
            $userView['admin_stock'] = (int) $lowStock;

            $metrics['low_stock_items'] = DB::table('lab_stock_items')
                ->leftJoin('lab_stock_batches', 'lab_stock_items.id', '=', 'lab_stock_batches.lab_stock_item_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'lab_stock_batches.supplier_id')
                ->select(
                    'lab_stock_items.id',
                    'lab_stock_items.name',
                    'lab_stock_items.reorder_level',
                    DB::raw('COALESCE(SUM(lab_stock_batches.remaining_qty),0) as total_qty'),
                    DB::raw('MAX(suppliers.company_name) as supplier_name'),
                    DB::raw('MAX(suppliers.phone) as supplier_phone'),
                    DB::raw('MAX(suppliers.email) as supplier_email')
                )
                ->groupBy('lab_stock_items.id', 'lab_stock_items.name', 'lab_stock_items.reorder_level')
                ->havingRaw('COALESCE(SUM(lab_stock_batches.remaining_qty),0) <= lab_stock_items.reorder_level')
                ->orderBy('lab_stock_items.name')
                ->limit(6)
                ->get();
        }

        if (Schema::hasTable('specimens')) {
            $metrics['samples_today'] = \App\Models\Specimen::query()
                ->whereDate('created_at', today())
                ->count();
            $workflowStats['samples_received'] = $metrics['samples_today'];
            $overviewCounts['samples_collected'] = $metrics['samples_today'];

            $testAnalytics['daily_counts'] = collect(range(6, 0))->map(function ($offset) {
                $day = now()->subDays($offset);
                return [
                    'label' => $day->format('D'),
                    'total' => \App\Models\Specimen::whereDate('created_at', $day)->count(),
                ];
            })->all();

            $testAnalytics['weekly_counts'] = collect(range(3, 0))->map(function ($offset) {
                $start = now()->subWeeks($offset)->startOfWeek();
                $end = now()->subWeeks($offset)->endOfWeek();
                return [
                    'label' => $start->format('M d'),
                    'total' => \App\Models\Specimen::whereBetween('created_at', [$start, $end])->count(),
                ];
            })->all();

            $testAnalytics['monthly_counts'] = collect(range(5, 0))->map(function ($offset) {
                $start = now()->subMonths($offset)->startOfMonth();
                $end = now()->subMonths($offset)->endOfMonth();
                return [
                    'label' => $start->format('M'),
                    'total' => \App\Models\Specimen::whereBetween('created_at', [$start, $end])->count(),
                ];
            })->all();
        }

        if (Schema::hasTable('specimen_tests')) {
            $metrics['reports_released_today'] = SpecimenTest::query()
                ->where('status', 'APPROVED')
                ->whereDate('updated_at', today())
                ->count();

            $totalTests = SpecimenTest::count();
            $metrics['rejection_rate'] = $totalTests > 0
                ? round((SpecimenTest::where('status', 'REJECTED')->count() / $totalTests) * 100, 2)
                : 0;

            $tatMinutes = SpecimenTest::query()
                ->where('status', 'APPROVED')
                ->whereNotNull('updated_at')
                ->limit(500)
                ->get(['created_at', 'updated_at'])
                ->map(function ($row) {
                    return $row->created_at && $row->updated_at
                        ? $row->updated_at->diffInMinutes($row->created_at)
                        : null;
                })
                ->filter()
                ->sort()
                ->values();

            if ($tatMinutes->isNotEmpty()) {
                $metrics['tat_median_minutes'] = $this->percentile($tatMinutes, 50);
                $metrics['tat_p90_minutes'] = $this->percentile($tatMinutes, 90);
                $metrics['tat_p95_minutes'] = $this->percentile($tatMinutes, 95);
            }

            $advancedStats['tat_median_minutes'] = $metrics['tat_median_minutes'];
            $advancedStats['tat_p90_minutes'] = $metrics['tat_p90_minutes'];
            $advancedStats['tat_p95_minutes'] = $metrics['tat_p95_minutes'];

            $workflowStats['awaiting_validation'] = SpecimenTest::where('status', 'RESULT_ENTERED')->count();
            $workflowStats['validated_reports'] = SpecimenTest::where('status', 'VALIDATED')->count();
            $workflowStats['tests_in_progress'] = SpecimenTest::where('status', 'ORDERED')->count();
            $userView['doctor_pending_validations'] = $workflowStats['awaiting_validation'];
            $userView['technician_sample_queue'] = $metrics['pending_samples'];
            $userView['technician_test_queue'] = $workflowStats['tests_in_progress'];

            $driver = DB::getDriverName();
            $hourExpr = $driver === 'sqlite' ? "CAST(strftime('%H', created_at) as integer)" : 'HOUR(created_at)';
            $heatmapRows = SpecimenTest::select(DB::raw($hourExpr . ' as hour'), DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [now()->subDays(7), now()])
                ->groupBy('hour')
                ->get();
            foreach ($heatmapRows as $row) {
                $hour = (int) $row->hour;
                if ($hour >= 0 && $hour <= 23) {
                    $testAnalytics['heatmap'][$hour] = (int) $row->total;
                }
            }
        }

        if (Schema::hasTable('test_parameter_results')) {
            $metrics['abnormal_results'] = DB::table('test_parameter_results')
                ->whereIn('flag', ['HIGH', 'LOW', 'ABNORMAL', 'CRITICAL'])
                ->count();
            $alerts['critical_results'] = $metrics['abnormal_results'];
            $userView['doctor_critical_results'] = $metrics['abnormal_results'];
        }

        if (Schema::hasTable('patients')) {
            $totalPatients = Patient::count();
            $overviewCounts['patients_today'] = Patient::whereDate('created_at', today())->count();
            $newPatients = Patient::whereBetween('created_at', [now()->subDays(30), now()])->count();
            $patientAnalytics['new_vs_returning'] = [
                'new' => $newPatients,
                'returning' => max(0, $totalPatients - $newPatients),
            ];

            $patientAnalytics['gender_ratio'] = Patient::select('sex', DB::raw('COUNT(*) as total'))
                ->groupBy('sex')
                ->get();

            $patients = Patient::whereNotNull('dob')->get(['dob']);
            foreach ($patients as $patient) {
                $age = Carbon::parse($patient->dob)->age;
                if ($age <= 12) {
                    $patientAnalytics['age_groups']['0-12']++;
                } elseif ($age <= 19) {
                    $patientAnalytics['age_groups']['13-19']++;
                } elseif ($age <= 35) {
                    $patientAnalytics['age_groups']['20-35']++;
                } elseif ($age <= 50) {
                    $patientAnalytics['age_groups']['36-50']++;
                } elseif ($age <= 65) {
                    $patientAnalytics['age_groups']['51-65']++;
                } else {
                    $patientAnalytics['age_groups']['66+']++;
                }
            }
        }

        if (Schema::hasTable('invoices')) {
            $patientAnalytics['opd_vs_referral'] = [
                'opd' => Invoice::whereNull('referral_type')->count(),
                'referral' => Invoice::whereNotNull('referral_type')->count(),
            ];
            $overviewCounts['outstanding_payments'] = $billingStats['unpaid'];
            $overviewCounts['monthly_revenue'] = $metrics['monthly_revenue'];
            $userView['admin_revenue'] = $metrics['monthly_revenue'];
        }

        if (Schema::hasTable('referral_commissions') && Schema::hasTable('doctors')) {
            $advancedStats['doctor_referrals'] = DB::table('referral_commissions')
                ->join('doctors', 'doctors.id', '=', 'referral_commissions.referral_id')
                ->where('referral_commissions.referral_type', 'doctor')
                ->select('doctors.name', DB::raw('SUM(referral_commissions.amount) as total'))
                ->groupBy('doctors.name')
                ->orderByDesc('total')
                ->limit(6)
                ->get();
        }

        if (Schema::hasTable('lab_stock_consumptions')) {
            $advancedStats['reagent_consumption'] = DB::table('lab_stock_consumptions')
                ->select(DB::raw('DATE(consumed_at) as label'), DB::raw('SUM(quantity) as total'))
                ->whereBetween('consumed_at', [now()->subDays(7), now()])
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        if (Schema::hasTable('centers') && Schema::hasTable('specimens')) {
            $advancedStats['multi_branch'] = DB::table('specimens')
                ->join('centers', 'centers.id', '=', 'specimens.center_id')
                ->select('centers.name', DB::raw('COUNT(*) as total'))
                ->groupBy('centers.name')
                ->orderByDesc('total')
                ->limit(6)
                ->get();
        }

        $orderTrend = [];
        $reportTrend = [];
        if (Schema::hasTable('specimens')) {
            $days = collect(range(13, 0))->map(function ($offset) {
                return Carbon::today()->subDays($offset);
            });
            foreach ($days as $day) {
                $label = $day->format('M d');
                $orderTrend[] = [
                    'label' => $label,
                    'count' => \App\Models\Specimen::query()->whereDate('created_at', $day)->count(),
                ];
                $reportTrend[] = [
                    'label' => $label,
                    'count' => SpecimenTest::query()
                        ->where('status', 'APPROVED')
                        ->whereDate('updated_at', $day)
                        ->count(),
                ];
            }
        }

        $departmentWorkload = collect();
        if (Schema::hasTable('specimen_tests') && Schema::hasTable('test_masters') && Schema::hasTable('departments')) {
            $departmentWorkload = SpecimenTest::query()
                ->select('departments.name as department', 'specimen_tests.status', DB::raw('COUNT(*) as total'))
                ->join('test_masters', 'specimen_tests.test_master_id', '=', 'test_masters.id')
                ->join('departments', 'test_masters.department_id', '=', 'departments.id')
                ->groupBy('departments.name', 'specimen_tests.status')
                ->get()
                ->groupBy('department')
                ->map(function ($rows) {
                    $counts = [
                        'pending' => 0,
                        'in_progress' => 0,
                        'completed' => 0,
                    ];
                    foreach ($rows as $row) {
                        $status = $row->status;
                        if (in_array($status, ['ORDERED', 'RESULT_ENTERED'], true)) {
                            $counts['pending'] += (int) $row->total;
                        } elseif ($status === 'VALIDATED') {
                            $counts['in_progress'] += (int) $row->total;
                        } elseif ($status === 'APPROVED') {
                            $counts['completed'] += (int) $row->total;
                        }
                    }
                    $counts['total'] = $counts['pending'] + $counts['in_progress'] + $counts['completed'];
                    return $counts;
                })
                ->sortByDesc('total')
                ->take(6);

            $testAnalytics['department_distribution'] = $departmentWorkload;
        }

        $queueAging = [
            '0-30 min' => 0,
            '30-60 min' => 0,
            '1-2h' => 0,
            '2-4h' => 0,
            '4-8h' => 0,
            '>8h' => 0,
        ];
        if (Schema::hasTable('specimen_tests')) {
            $pending = SpecimenTest::query()
                ->whereIn('status', ['ORDERED', 'RESULT_ENTERED', 'VALIDATED'])
                ->get(['created_at']);
            foreach ($pending as $item) {
                $minutes = $item->created_at ? now()->diffInMinutes($item->created_at) : 0;
                if ($minutes <= 30) {
                    $queueAging['0-30 min']++;
                } elseif ($minutes <= 60) {
                    $queueAging['30-60 min']++;
                } elseif ($minutes <= 120) {
                    $queueAging['1-2h']++;
                } elseif ($minutes <= 240) {
                    $queueAging['2-4h']++;
                } elseif ($minutes <= 480) {
                    $queueAging['4-8h']++;
                } else {
                    $queueAging['>8h']++;
                }
            }
        }
        $alerts['delayed_samples'] = $queueAging['>8h'] ?? 0;


        if (Schema::hasTable('specimen_tests')) {
            $recentActivity['validated_reports'] = SpecimenTest::where('status', 'VALIDATED')
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(['id', 'specimen_id', 'updated_at']);

            $recentActivity['rejections'] = SpecimenTest::where('status', 'REJECTED')
                ->orderByDesc('updated_at')
                ->limit(5)
                ->get(['id', 'specimen_id', 'updated_at']);
        }

        if (Schema::hasTable('invoices')) {
            $recentActivity['created_invoices'] = Invoice::orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'invoice_no', 'net_total', 'created_at']);
        }

        $billingMix = collect();
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'payment_mode')) {
            $billingMix = DB::table('invoices')
                ->select('payment_mode', DB::raw('COUNT(*) as total'))
                ->groupBy('payment_mode')
                ->get()
                ->filter(fn ($row) => !empty($row->payment_mode));
        }

        $clientStats = collect();
        if (Schema::hasTable('specimens') && Schema::hasTable('centers') && Schema::hasTable('specimen_tests')) {
            $hasPrice = Schema::hasColumn('specimen_tests', 'price');
            $clientQuery = DB::table('specimens')
                ->join('centers', 'specimens.center_id', '=', 'centers.id')
                ->join('specimen_tests', 'specimen_tests.specimen_id', '=', 'specimens.id')
                ->select('centers.name', DB::raw('COUNT(specimen_tests.id) as total_tests'))
                ->groupBy('centers.name')
                ->orderByDesc('total_tests')
                ->limit(6);

            if ($hasPrice) {
                $clientQuery->addSelect(DB::raw('SUM(specimen_tests.price) as revenue'));
            }

            $clientStats = $clientQuery->get()->map(function ($row) use ($hasPrice) {
                return [
                    'name' => $row->name,
                    'total_tests' => (int) $row->total_tests,
                    'revenue' => $hasPrice ? (float) ($row->revenue ?? 0) : null,
                ];
            });
        }

        $topTests = collect();
        if (Schema::hasTable('specimen_tests') && Schema::hasTable('test_masters')) {
            $topTests = SpecimenTest::query()
                ->select('test_masters.name', DB::raw('COUNT(*) as total'))
                ->join('test_masters', 'specimen_tests.test_master_id', '=', 'test_masters.id')
                ->groupBy('test_masters.name')
                ->orderByDesc('total')
                ->limit(8)
                ->get();
        }

        $rejectionReasons = collect([
            ['label' => 'Hemolyzed', 'count' => 0],
            ['label' => 'Insufficient volume', 'count' => 0],
            ['label' => 'Wrong tube', 'count' => 0],
            ['label' => 'Clotted', 'count' => 0],
            ['label' => 'Labeling error', 'count' => 0],
            ['label' => 'Transport delay', 'count' => 0],
        ]);

        $qcTrend = collect(range(6, 0))->map(function ($offset) {
            return [
                'label' => Carbon::today()->subDays($offset)->format('M d'),
                'value' => 0,
            ];
        });

        $positivityTrend = collect(range(6, 0))->map(function ($offset) {
            return [
                'label' => Carbon::today()->subDays($offset)->format('M d'),
                'value' => 0,
            ];
        });

        $reagentTrend = collect(range(6, 0))->map(function ($offset) {
            return [
                'label' => Carbon::today()->subDays($offset)->format('M d'),
                'stock' => 0,
                'consumed' => 0,
            ];
        });

        $instrumentStats = collect(range(6, 0))->map(function ($offset) {
            return [
                'label' => Carbon::today()->subDays($offset)->format('M d'),
                'downtime' => 0,
                'errors' => 0,
            ];
        });

        if (Schema::hasTable('specimens')) {
            $psFrom = request()->query('ps_from');
            $psTo = request()->query('ps_to');
            $psGroup = request()->query('ps_group', 'day');
            $psSearch = trim((string) request()->query('ps_q', ''));

            $patientStats['filters'] = [
                'from' => $psFrom,
                'to' => $psTo,
                'group' => $psGroup,
                'q' => $psSearch,
            ];

            $driver = DB::getDriverName();
            $dayExpr = $driver === 'sqlite' ? "date(specimens.created_at)" : "DATE(specimens.created_at)";
            $monthExpr = $driver === 'sqlite' ? "strftime('%Y-%m', specimens.created_at)" : "DATE_FORMAT(specimens.created_at, '%Y-%m')";
            $yearExpr = $driver === 'sqlite' ? "strftime('%Y', specimens.created_at)" : "YEAR(specimens.created_at)";

            $groupExpr = match ($psGroup) {
                'month' => $monthExpr,
                'year' => $yearExpr,
                default => $dayExpr,
            };

            $specimenQuery = \App\Models\Specimen::query()
                ->with(['patient', 'center', 'tests.testMaster'])
                ->orderByDesc('created_at');

            if ($psFrom) {
                $specimenQuery->whereDate('created_at', '>=', $psFrom);
            }
            if ($psTo) {
                $specimenQuery->whereDate('created_at', '<=', $psTo);
            }
            if ($psSearch !== '') {
                $specimenQuery->where(function ($q) use ($psSearch) {
                    $q->where('specimen_no', 'like', '%' . $psSearch . '%')
                        ->orWhereHas('patient', function ($sub) use ($psSearch) {
                            $sub->where('name', 'like', '%' . $psSearch . '%')
                                ->orWhere('nic', 'like', '%' . $psSearch . '%');
                        })
                        ->orWhereHas('tests.testMaster', function ($sub) use ($psSearch) {
                            $sub->where('name', 'like', '%' . $psSearch . '%')
                                ->orWhere('code', 'like', '%' . $psSearch . '%');
                        });
                });
            }

            $patientStats['specimens'] = $specimenQuery->limit(200)->get();

            $countQuery = \App\Models\Specimen::query();
            if ($psFrom) {
                $countQuery->whereDate('created_at', '>=', $psFrom);
            }
            if ($psTo) {
                $countQuery->whereDate('created_at', '<=', $psTo);
            }
            if ($psSearch !== '') {
                $countQuery->where(function ($q) use ($psSearch) {
                    $q->where('specimen_no', 'like', '%' . $psSearch . '%')
                        ->orWhereHas('patient', function ($sub) use ($psSearch) {
                            $sub->where('name', 'like', '%' . $psSearch . '%')
                                ->orWhere('nic', 'like', '%' . $psSearch . '%');
                        })
                        ->orWhereHas('tests.testMaster', function ($sub) use ($psSearch) {
                            $sub->where('name', 'like', '%' . $psSearch . '%')
                                ->orWhere('code', 'like', '%' . $psSearch . '%');
                        });
                });
            }

            $patientStats['totals'] = [
                'patients' => (int) (clone $countQuery)->distinct('patient_id')->count('patient_id'),
                'specimens' => (int) (clone $countQuery)->count(),
                'tests' => (int) (clone $countQuery)->join('specimen_tests', 'specimens.id', '=', 'specimen_tests.specimen_id')->count(),
            ];

            $patientStats['trendRows'] = \App\Models\Specimen::query()
                ->select(DB::raw($groupExpr . ' as label'), DB::raw('COUNT(DISTINCT patient_id) as patient_count'))
                ->when($psFrom, function ($query) use ($psFrom) {
                    $query->whereDate('created_at', '>=', $psFrom);
                })
                ->when($psTo, function ($query) use ($psTo) {
                    $query->whereDate('created_at', '<=', $psTo);
                })
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        $demoAccounts = collect();
        if (Schema::hasTable('demo_accounts')) {
            $demoAccounts = DemoAccount::orderBy('expires_at', 'asc')->get();
        }
        $demoAccounts = $demoAccounts->map(function (DemoAccount $account) {
            $expiresAt = $account->expires_at;
            $expiresLabel = $expiresAt ? $expiresAt->format('Y-m-d H:i') : 'Not set';
            $expiresIn = $expiresAt
                ? $expiresAt->diffForHumans(now(), [
                    'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
                    'parts' => 2,
                    'short' => true,
                ])
                : 'Pending';
            return [
                'id' => $account->id,
                'name' => $account->name,
                'email' => $account->email,
                'phone' => $account->phone,
                'notes' => $account->notes,
                'expires_at' => $expiresLabel,
                'expires_in' => $expiresIn,
            ];
        });

        return view('admin.dashboard', [
            'counts' => $counts,
            'statusCounts' => $statusCounts,
            'dailyLabels' => $dailyLabels,
            'dailyReceived' => $dailyReceived,
            'dailyApproved' => $dailyApproved,
            'departmentLoad' => $departmentLoad,
            'stockStats' => $stockStats,
            'metrics' => $metrics,
            'orderTrend' => $orderTrend,
            'reportTrend' => $reportTrend,
            'departmentWorkload' => $departmentWorkload,
            'queueAging' => $queueAging,
            'billingMix' => $billingMix,
            'topTests' => $topTests,
            'clientStats' => $clientStats,
            'rejectionReasons' => $rejectionReasons,
            'qcTrend' => $qcTrend,
            'positivityTrend' => $positivityTrend,
            'reagentTrend' => $reagentTrend,
            'instrumentStats' => $instrumentStats,
            'billingStats' => $billingStats,
            'workflowStats' => $workflowStats,
            'testAnalytics' => $testAnalytics,
            'patientAnalytics' => $patientAnalytics,
            'advancedStats' => $advancedStats,
            'overviewCounts' => $overviewCounts,
            'alerts' => $alerts,
            'recentActivity' => $recentActivity,
            'userView' => $userView,
            'systemStatus' => $systemStatus,
            'patientStats' => $patientStats,
            'isSuperAdmin' => $isSuperAdmin,
            'currentUser' => $currentUser,
            'demoAccounts' => $demoAccounts,
        ]);
    }

    private function percentile(\Illuminate\Support\Collection $values, int $percentile): ?int
    {
        if ($values->isEmpty()) {
            return null;
        }
        $index = (int) ceil(($percentile / 100) * $values->count()) - 1;
        $index = max(0, min($values->count() - 1, $index));
        return (int) $values->get($index);
    }

    public function placeholder(string $page)
    {
        $this->requirePermission('admin.dashboard');

        if ($page === 'patient-stats') {
            return redirect()->to(url('/admin?tab=patient-stats'));
        }

        if ($page === 'summary') {
            $currentUser = auth()->user();
            $isSuperAdmin = $currentUser && $currentUser->isSuperAdmin();
            $labId = $currentUser?->lab_id;
            $labName = 'All Labs';
            if (!$isSuperAdmin && $labId) {
                $labName = Lab::query()->whereKey($labId)->value('name') ?: 'Lab';
            }
            $from = request()->query('from');
            $to = request()->query('to');
            $fromTime = request()->query('from_time');
            $toTime = request()->query('to_time');
            $tab = request()->query('tab', 'tests');
            $sort = request()->query('sort', 'total_desc');

            $fromDate = null;
            $toDate = null;
            if ($from) {
                $fromDate = Carbon::parse($from . ' ' . ($fromTime ?: '00:00'))->startOfMinute();
            }
            if ($to) {
                $toDate = Carbon::parse($to . ' ' . ($toTime ?: '23:59'))->endOfMinute();
            }

            $labFilter = function ($query, string $table) use ($isSuperAdmin, $labId) {
                if ($isSuperAdmin || !$labId || !Schema::hasColumn($table, 'lab_id')) {
                    return $query;
                }
                return $query->where($table . '.lab_id', $labId);
            };

            $dateFilter = function ($query, string $column) use ($fromDate, $toDate) {
                if ($fromDate) {
                    $query->where($column, '>=', $fromDate);
                }
                if ($toDate) {
                    $query->where($column, '<=', $toDate);
                }
                return $query;
            };

            $sortBy = function ($query, string $nameColumn = 'name', string $totalColumn = 'total') use ($sort) {
                return match ($sort) {
                    'name_asc' => $query->orderBy($nameColumn, 'asc'),
                    'name_desc' => $query->orderBy($nameColumn, 'desc'),
                    'total_asc' => $query->orderBy($totalColumn, 'asc'),
                    default => $query->orderBy($totalColumn, 'desc'),
                };
            };

            $todayStart = now()->startOfDay();
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            $testBase = SpecimenTest::query();
            $testBase = $labFilter($testBase, 'specimen_tests');
            $testPeriod = clone $testBase;
            $testPeriod = $dateFilter($testPeriod, 'specimen_tests.created_at');

            $totalTestsToday = (clone $testBase)->where('specimen_tests.created_at', '>=', $todayStart)->count();
            $totalTestsMonth = (clone $testBase)->whereBetween('specimen_tests.created_at', [$monthStart, $monthEnd])->count();
            $totalTestsPeriod = (clone $testPeriod)->count();
            $pendingTests = (clone $testPeriod)->whereIn('specimen_tests.status', ['ORDERED', 'RESULT_ENTERED'])->count();
            $completedTests = (clone $testPeriod)->whereIn('specimen_tests.status', ['VALIDATED', 'APPROVED'])->count();
            $rejectedTests = (clone $testPeriod)->where('specimen_tests.status', 'REJECTED')->count();

            $testsByDepartment = $dateFilter(
                $labFilter(
                    DB::table('specimen_tests')
                        ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                        ->join('departments', 'departments.id', '=', 'test_masters.department_id')
                        ->select('departments.name', DB::raw('COUNT(*) as total')),
                    'specimen_tests'
                ),
                'specimen_tests.created_at'
            )
                ->groupBy('departments.name')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $avgTatMinutes = null;
            if (Schema::hasTable('approvals')) {
                $tatQuery = DB::table('approvals')
                    ->join('specimen_tests', 'specimen_tests.id', '=', 'approvals.specimen_test_id')
                    ->where('approvals.status', 'VALIDATED')
                    ->select('approvals.approved_at', 'specimen_tests.created_at');
                $tatQuery = $labFilter($tatQuery, 'approvals');
                $tatQuery = $dateFilter($tatQuery, 'approvals.approved_at');

                $driver = DB::getDriverName();
                if ($driver === 'mysql') {
                    $avgTatMinutes = $tatQuery->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, specimen_tests.created_at, approvals.approved_at)) as avg_minutes'))
                        ->value('avg_minutes');
                } elseif ($driver === 'sqlite') {
                    $avgTatMinutes = $tatQuery->select(DB::raw('AVG((julianday(approvals.approved_at) - julianday(specimen_tests.created_at)) * 1440) as avg_minutes'))
                        ->value('avg_minutes');
                }
            }

            $testWise = $dateFilter(
                $labFilter(
                    DB::table('specimen_tests')
                        ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                        ->select('test_masters.name', DB::raw('COUNT(*) as total'), DB::raw('SUM(specimen_tests.price) as revenue')),
                    'specimen_tests'
                ),
                'specimen_tests.created_at'
            )
                ->groupBy('test_masters.name')
                ->when(true, fn ($query) => $sortBy($query))
                ->limit(20)
                ->get();

            $packageWise = $dateFilter(
                $labFilter(
                    DB::table('specimen_tests')
                        ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                        ->where('test_masters.is_package', true)
                        ->select('test_masters.name', DB::raw('COUNT(*) as total')),
                    'specimen_tests'
                ),
                'specimen_tests.created_at'
            )
                ->groupBy('test_masters.name')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $centreWise = $dateFilter(
                $labFilter(
                    DB::table('specimens')
                        ->join('centers', 'centers.id', '=', 'specimens.center_id')
                        ->select('centers.name', DB::raw('COUNT(*) as total')),
                    'specimens'
                ),
                'specimens.created_at'
            )
                ->groupBy('centers.name')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $doctorWise = $dateFilter(
                $labFilter(
                    DB::table('invoices')
                        ->join('doctors', 'doctors.id', '=', 'invoices.referral_id')
                        ->where('invoices.referral_type', 'doctor')
                        ->select('doctors.name', DB::raw('COUNT(*) as total'), DB::raw('SUM(invoices.net_total) as revenue')),
                    'invoices'
                ),
                'invoices.created_at'
            )
                ->groupBy('doctors.name')
                ->when(true, fn ($query) => $sortBy($query, 'name', 'revenue'))
                ->get();

            $invoiceQuery = Invoice::query();
            $invoiceQuery = $labFilter($invoiceQuery, 'invoices');
            $invoicePeriod = clone $invoiceQuery;
            $invoicePeriod = $dateFilter($invoicePeriod, 'invoices.created_at');

            $totalInvoiced = (float) ($invoicePeriod->sum('net_total') ?? 0);
            $grossIncome = (float) ((clone $invoicePeriod)->sum('total') ?? 0);

            $paymentsQuery = DB::table('payments');
            $paymentsQuery = $labFilter($paymentsQuery, 'payments');
            $paymentsQuery = $dateFilter($paymentsQuery, 'payments.created_at');
            $totalCollected = (float) ($paymentsQuery->sum('amount') ?? 0);

            $outstandingBalance = max($totalInvoiced - $totalCollected, 0);
            $creditAmount = (float) ((clone $invoicePeriod)->where('payment_mode', 'CREDIT')->sum('net_total') ?? 0);
            $refundIssued = 0.0;

            $paymentsByMethod = (clone $paymentsQuery)
                ->select('method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('method')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $invoiceByCentre = $dateFilter(
                $labFilter(
                    DB::table('invoices')
                        ->leftJoin('centers', 'centers.id', '=', 'invoices.center_id')
                        ->select(DB::raw('COALESCE(centers.name, "Main Lab") as name'), DB::raw('SUM(invoices.net_total) as total')),
                    'invoices'
                ),
                'invoices.created_at'
            )
                ->groupBy('name')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $invoiceByDate = $dateFilter(
                $labFilter(
                    DB::table('invoices')
                        ->select(DB::raw('date(invoices.created_at) as day'), DB::raw('SUM(invoices.net_total) as total')),
                    'invoices'
                ),
                'invoices.created_at'
            )
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $paymentsByUser = $dateFilter(
                $labFilter(
                    DB::table('payments')
                        ->leftJoin('users', 'users.id', '=', 'payments.created_by')
                        ->select(DB::raw('COALESCE(users.name, "Unknown") as name'), DB::raw('SUM(payments.amount) as total')),
                    'payments'
                ),
                'payments.created_at'
            )
                ->groupBy('name')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $incomeByTest = $dateFilter(
                $labFilter(
                    DB::table('specimen_tests')
                        ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                        ->select('test_masters.name', DB::raw('SUM(specimen_tests.price) as total')),
                    'specimen_tests'
                ),
                'specimen_tests.created_at'
            )
                ->groupBy('test_masters.name')
                ->when(true, fn ($query) => $sortBy($query))
                ->limit(10)
                ->get();

            $incomeByDepartment = $dateFilter(
                $labFilter(
                    DB::table('specimen_tests')
                        ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                        ->join('departments', 'departments.id', '=', 'test_masters.department_id')
                        ->select('departments.name', DB::raw('SUM(specimen_tests.price) as total')),
                    'specimen_tests'
                ),
                'specimen_tests.created_at'
            )
                ->groupBy('departments.name')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $incomeByDoctor = $dateFilter(
                $labFilter(
                    DB::table('invoices')
                        ->join('doctors', 'doctors.id', '=', 'invoices.referral_id')
                        ->where('invoices.referral_type', 'doctor')
                        ->select('doctors.name', DB::raw('SUM(invoices.net_total) as total')),
                    'invoices'
                ),
                'invoices.created_at'
            )
                ->groupBy('doctors.name')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $incomeByCentre = $dateFilter(
                $labFilter(
                    DB::table('invoices')
                        ->leftJoin('centers', 'centers.id', '=', 'invoices.center_id')
                        ->select(DB::raw('COALESCE(centers.name, "Main Lab") as name'), DB::raw('SUM(invoices.net_total) as total')),
                    'invoices'
                ),
                'invoices.created_at'
            )
                ->groupBy('name')
                ->when(true, fn ($query) => $sortBy($query))
                ->get();

            $incomeByPeriod = $dateFilter(
                $labFilter(
                    DB::table('invoices')
                        ->select(DB::raw('date(invoices.created_at) as day'), DB::raw('SUM(invoices.net_total) as total')),
                    'invoices'
                ),
                'invoices.created_at'
            )
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            $refundSummary = [
                'total_count' => 0,
                'total_amount' => 0,
                'by_reason' => collect(),
                'by_patient' => collect(),
                'by_test' => collect(),
                'by_date' => collect(),
                'by_approver' => collect(),
            ];

            if (Schema::hasTable('refunds')) {
                $refundQuery = DB::table('refunds');
                $refundQuery = $labFilter($refundQuery, 'refunds');
                $refundQuery = $dateFilter($refundQuery, 'refunds.created_at');
                $refundSummary['total_count'] = (int) $refundQuery->count();
                $refundSummary['total_amount'] = (float) ($refundQuery->sum('amount') ?? 0);
                $refundSummary['by_reason'] = (clone $refundQuery)
                    ->select('reason', DB::raw('COUNT(*) as total'), DB::raw('SUM(amount) as amount'))
                    ->groupBy('reason')
                    ->when(true, fn ($query) => $sortBy($query, 'reason', 'amount'))
                    ->get();
                $refundSummary['by_patient'] = (clone $refundQuery)
                    ->leftJoin('patients', 'patients.id', '=', 'refunds.patient_id')
                    ->select(DB::raw('COALESCE(patients.name, "Unknown") as name'), DB::raw('SUM(refunds.amount) as total'))
                    ->groupBy('name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();
                $refundSummary['by_test'] = (clone $refundQuery)
                    ->leftJoin('test_masters', 'test_masters.id', '=', 'refunds.test_master_id')
                    ->select(DB::raw('COALESCE(test_masters.name, "Unknown") as name'), DB::raw('SUM(refunds.amount) as total'))
                    ->groupBy('name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();
                $refundSummary['by_date'] = (clone $refundQuery)
                    ->select(DB::raw('date(refunds.created_at) as day'), DB::raw('SUM(refunds.amount) as total'))
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get();
                $refundSummary['by_approver'] = (clone $refundQuery)
                    ->leftJoin('users', 'users.id', '=', 'refunds.approved_by')
                    ->select(DB::raw('COALESCE(users.name, "Unknown") as name'), DB::raw('SUM(refunds.amount) as total'))
                    ->groupBy('name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();
            }

            $reagentCost = 0.0;
            $departmentCosts = collect();
            $testMargins = collect();
            if (Schema::hasTable('lab_stock_consumptions')) {
                $avgCostSub = DB::table('lab_stock_batches')
                    ->select('lab_stock_item_id', DB::raw('AVG(unit_cost) as avg_cost'))
                    ->groupBy('lab_stock_item_id');

                $reagentCostQuery = DB::table('lab_stock_consumptions')
                    ->leftJoinSub($avgCostSub, 'batch_costs', function ($join) {
                        $join->on('batch_costs.lab_stock_item_id', '=', 'lab_stock_consumptions.lab_stock_item_id');
                    })
                    ->select(DB::raw('SUM(lab_stock_consumptions.quantity * COALESCE(batch_costs.avg_cost, 0)) as total_cost'));
                $reagentCostQuery = $labFilter($reagentCostQuery, 'lab_stock_consumptions');
                $reagentCostQuery = $dateFilter($reagentCostQuery, 'lab_stock_consumptions.created_at');
                $reagentCost = (float) ($reagentCostQuery->value('total_cost') ?? 0);

                $departmentCosts = $dateFilter(
                    $labFilter(
                        DB::table('lab_stock_consumptions')
                            ->leftJoinSub($avgCostSub, 'batch_costs', function ($join) {
                                $join->on('batch_costs.lab_stock_item_id', '=', 'lab_stock_consumptions.lab_stock_item_id');
                            })
                            ->leftJoin('specimen_tests', 'specimen_tests.id', '=', 'lab_stock_consumptions.specimen_test_id')
                            ->leftJoin('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                            ->leftJoin('departments', 'departments.id', '=', 'test_masters.department_id')
                            ->select(DB::raw('COALESCE(departments.name, "Unknown") as dept_name'), DB::raw('SUM(lab_stock_consumptions.quantity * COALESCE(batch_costs.avg_cost, 0)) as total')),
                        'lab_stock_consumptions'
                    ),
                    'lab_stock_consumptions.created_at'
                )
                    ->groupBy('dept_name')
                    ->when(true, fn ($query) => $sortBy($query, 'dept_name'))
                    ->get();

                $revenueByTest = $dateFilter(
                    $labFilter(
                        DB::table('specimen_tests')
                            ->select('test_master_id', DB::raw('SUM(price) as revenue')),
                        'specimen_tests'
                    ),
                    'specimen_tests.created_at'
                )
                    ->groupBy('test_master_id')
                    ->get()
                    ->keyBy('test_master_id');

                $costByTest = $dateFilter(
                    $labFilter(
                        DB::table('lab_stock_consumptions')
                            ->leftJoinSub($avgCostSub, 'batch_costs', function ($join) {
                                $join->on('batch_costs.lab_stock_item_id', '=', 'lab_stock_consumptions.lab_stock_item_id');
                            })
                            ->select('lab_stock_consumptions.test_master_id', DB::raw('SUM(lab_stock_consumptions.quantity * COALESCE(batch_costs.avg_cost, 0)) as cost')),
                        'lab_stock_consumptions'
                    ),
                    'lab_stock_consumptions.created_at'
                )
                    ->whereNotNull('lab_stock_consumptions.test_master_id')
                    ->groupBy('lab_stock_consumptions.test_master_id')
                    ->get()
                    ->keyBy('test_master_id');

                $testIds = $revenueByTest->keys()->merge($costByTest->keys())->unique()->values();
                if ($testIds->isNotEmpty()) {
                    $testNames = DB::table('test_masters')
                        ->whereIn('id', $testIds)
                        ->pluck('name', 'id');

                    $testMargins = $testIds->map(function ($testId) use ($testNames, $revenueByTest, $costByTest) {
                        $revenue = (float) ($revenueByTest[$testId]->revenue ?? 0);
                        $cost = (float) ($costByTest[$testId]->cost ?? 0);
                        return (object) [
                            'name' => $testNames[$testId] ?? 'Unknown',
                            'revenue' => $revenue,
                            'cost' => $cost,
                            'margin' => $revenue - $cost,
                        ];
                    })
                        ->sortByDesc('revenue')
                        ->values();
                }
            }

            $costSummary = [
                'total_operational' => $reagentCost,
                'cost_per_test' => $totalTestsPeriod > 0 ? ($reagentCost / $totalTestsPeriod) : 0,
                'department_costs' => $departmentCosts,
                'reagent_cost' => $reagentCost,
                'staff_cost' => 0,
                'test_margins' => $testMargins,
            ];

            $consumptionSummary = [
                'consumed_today' => 0,
                'consumed_month' => 0,
                'test_vs_reagent' => collect(),
                'item_usage' => collect(),
                'wastage_percent' => 0,
                'expired_items' => collect(),
            ];

            if (Schema::hasTable('lab_stock_consumptions')) {
                $consumptionBase = DB::table('lab_stock_consumptions');
                $consumptionBase = $labFilter($consumptionBase, 'lab_stock_consumptions');
                $consumptionSummary['consumed_today'] = (float) (clone $consumptionBase)
                    ->whereDate('lab_stock_consumptions.created_at', today())
                    ->sum('quantity');
                $consumptionSummary['consumed_month'] = (float) (clone $consumptionBase)
                    ->whereBetween('lab_stock_consumptions.created_at', [$monthStart, $monthEnd])
                    ->sum('quantity');

                $consumptionSummary['item_usage'] = $dateFilter(
                    $labFilter(
                        DB::table('lab_stock_consumptions')
                            ->join('lab_stock_items', 'lab_stock_items.id', '=', 'lab_stock_consumptions.lab_stock_item_id')
                            ->select('lab_stock_items.name', DB::raw('SUM(lab_stock_consumptions.quantity) as total')),
                        'lab_stock_consumptions'
                    ),
                    'lab_stock_consumptions.created_at'
                )
                    ->groupBy('lab_stock_items.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();

                $consumptionSummary['test_vs_reagent'] = $dateFilter(
                    $labFilter(
                        DB::table('lab_stock_consumptions')
                            ->leftJoin('test_masters', 'test_masters.id', '=', 'lab_stock_consumptions.test_master_id')
                            ->join('lab_stock_items', 'lab_stock_items.id', '=', 'lab_stock_consumptions.lab_stock_item_id')
                            ->select(
                                DB::raw('COALESCE(test_masters.name, "Unknown") as test_name'),
                                'lab_stock_items.name as item_name',
                                DB::raw('SUM(lab_stock_consumptions.quantity) as total')
                            ),
                        'lab_stock_consumptions'
                    ),
                    'lab_stock_consumptions.created_at'
                )
                    ->groupBy('test_name', 'item_name')
                    ->when(true, fn ($query) => $sortBy($query, 'test_name'))
                    ->limit(20)
                    ->get();
            }

            if (Schema::hasTable('lab_stock_batches')) {
                $expiredQuery = DB::table('lab_stock_batches')
                    ->join('lab_stock_items', 'lab_stock_items.id', '=', 'lab_stock_batches.lab_stock_item_id')
                    ->whereNotNull('lab_stock_batches.expiry_date')
                    ->where('lab_stock_batches.expiry_date', '<=', now()->toDateString());
                $expiredQuery = $labFilter($expiredQuery, 'lab_stock_batches');
                $consumptionSummary['expired_items'] = $expiredQuery
                    ->select('lab_stock_items.name', 'lab_stock_batches.expiry_date', 'lab_stock_batches.remaining_qty')
                    ->orderBy('lab_stock_batches.expiry_date')
                    ->get();

                $expiredQty = (float) $expiredQuery->sum('lab_stock_batches.remaining_qty');
                $totalQtyQuery = DB::table('lab_stock_batches');
                $totalQtyQuery = $labFilter($totalQtyQuery, 'lab_stock_batches');
                $totalQty = (float) $totalQtyQuery->sum('quantity');
                if ($totalQty > 0) {
                    $consumptionSummary['wastage_percent'] = round(($expiredQty / $totalQty) * 100, 2);
                }
            }

            $reorderSummary = [
                'low_stock' => collect(),
                'out_of_stock' => collect(),
                'alerts' => 0,
                'pending_orders' => 0,
                'supplier_wise' => collect(),
            ];

            if (Schema::hasTable('lab_stock_items') && Schema::hasTable('lab_stock_batches')) {
                $stockBase = DB::table('lab_stock_items')
                    ->leftJoin('lab_stock_batches', 'lab_stock_items.id', '=', 'lab_stock_batches.lab_stock_item_id')
                    ->select(
                        'lab_stock_items.id',
                        'lab_stock_items.name',
                        'lab_stock_items.reorder_level',
                        DB::raw('COALESCE(SUM(lab_stock_batches.remaining_qty),0) as total_qty')
                    )
                    ->groupBy('lab_stock_items.id', 'lab_stock_items.name', 'lab_stock_items.reorder_level');
                $stockBase = $labFilter($stockBase, 'lab_stock_items');

                $reorderSummary['low_stock'] = (clone $stockBase)
                    ->havingRaw('COALESCE(SUM(lab_stock_batches.remaining_qty),0) <= lab_stock_items.reorder_level')
                    ->when(true, fn ($query) => $sortBy($query, 'name', 'total_qty'))
                    ->get();

                $reorderSummary['out_of_stock'] = (clone $stockBase)
                    ->havingRaw('COALESCE(SUM(lab_stock_batches.remaining_qty),0) <= 0')
                    ->when(true, fn ($query) => $sortBy($query, 'name', 'total_qty'))
                    ->get();

                $reorderSummary['alerts'] = $reorderSummary['low_stock']->count();

                $reorderSummary['supplier_wise'] = DB::table('lab_stock_batches')
                    ->join('lab_stock_items', 'lab_stock_items.id', '=', 'lab_stock_batches.lab_stock_item_id')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'lab_stock_batches.supplier_id')
                    ->select(
                        DB::raw('COALESCE(suppliers.company_name, "Unknown") as supplier'),
                        'lab_stock_items.name as item_name',
                        DB::raw('SUM(lab_stock_batches.remaining_qty) as total_qty')
                    )
                    ->groupBy('supplier', 'item_name')
                    ->when(true, fn ($query) => $sortBy($query, 'supplier', 'total_qty'))
                    ->get();
            }

            $centreSummary = [
                'tests_per_centre' => collect(),
                'income_per_centre' => collect(),
                'pending_samples' => collect(),
                'rejection_rate' => collect(),
            ];

            if (Schema::hasTable('specimen_tests') && Schema::hasTable('specimens')) {
                $centreSummary['tests_per_centre'] = $dateFilter(
                    $labFilter(
                        DB::table('specimen_tests')
                            ->join('specimens', 'specimens.id', '=', 'specimen_tests.specimen_id')
                            ->leftJoin('centers', 'centers.id', '=', 'specimens.center_id')
                            ->select(DB::raw('COALESCE(centers.name, "Main Lab") as name'), DB::raw('COUNT(*) as total')),
                        'specimen_tests'
                    ),
                    'specimen_tests.created_at'
                )
                    ->groupBy('name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();

                $centreSummary['pending_samples'] = $dateFilter(
                    $labFilter(
                        DB::table('specimen_tests')
                            ->join('specimens', 'specimens.id', '=', 'specimen_tests.specimen_id')
                            ->leftJoin('centers', 'centers.id', '=', 'specimens.center_id')
                            ->whereIn('specimen_tests.status', ['ORDERED', 'RESULT_ENTERED', 'VALIDATED'])
                            ->select(DB::raw('COALESCE(centers.name, "Main Lab") as name'), DB::raw('COUNT(*) as total')),
                        'specimen_tests'
                    ),
                    'specimen_tests.created_at'
                )
                    ->groupBy('name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();

                $centreSummary['rejection_rate'] = $dateFilter(
                    $labFilter(
                        DB::table('specimen_tests')
                            ->join('specimens', 'specimens.id', '=', 'specimen_tests.specimen_id')
                            ->leftJoin('centers', 'centers.id', '=', 'specimens.center_id')
                            ->select(
                                DB::raw('COALESCE(centers.name, "Main Lab") as name'),
                                DB::raw('SUM(CASE WHEN specimen_tests.status = "REJECTED" THEN 1 ELSE 0 END) as rejected'),
                                DB::raw('COUNT(*) as total')
                            ),
                        'specimen_tests'
                    ),
                    'specimen_tests.created_at'
                )
                    ->groupBy('name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();
            }

            if (Schema::hasTable('invoices')) {
                $centreSummary['income_per_centre'] = $dateFilter(
                    $labFilter(
                        DB::table('invoices')
                            ->leftJoin('centers', 'centers.id', '=', 'invoices.center_id')
                            ->select(DB::raw('COALESCE(centers.name, "Main Lab") as name'), DB::raw('SUM(invoices.net_total) as total')),
                        'invoices'
                    ),
                    'invoices.created_at'
                )
                    ->groupBy('name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();
            }

            $departmentSummary = [
                'tests_performed' => collect(),
                'pending' => collect(),
                'high_flags' => collect(),
                'low_flags' => collect(),
                'revenue' => collect(),
            ];

            if (Schema::hasTable('specimen_tests')) {
                $departmentSummary['tests_performed'] = $dateFilter(
                    $labFilter(
                        DB::table('specimen_tests')
                            ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                            ->join('departments', 'departments.id', '=', 'test_masters.department_id')
                            ->select('departments.name', DB::raw('COUNT(*) as total')),
                        'specimen_tests'
                    ),
                    'specimen_tests.created_at'
                )
                    ->groupBy('departments.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();

                $departmentSummary['pending'] = $dateFilter(
                    $labFilter(
                        DB::table('specimen_tests')
                            ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                            ->join('departments', 'departments.id', '=', 'test_masters.department_id')
                            ->whereIn('specimen_tests.status', ['ORDERED', 'RESULT_ENTERED', 'VALIDATED'])
                            ->select('departments.name', DB::raw('COUNT(*) as total')),
                        'specimen_tests'
                    ),
                    'specimen_tests.created_at'
                )
                    ->groupBy('departments.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();

                $departmentSummary['revenue'] = $dateFilter(
                    $labFilter(
                        DB::table('specimen_tests')
                            ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                            ->join('departments', 'departments.id', '=', 'test_masters.department_id')
                            ->select('departments.name', DB::raw('SUM(specimen_tests.price) as total')),
                        'specimen_tests'
                    ),
                    'specimen_tests.created_at'
                )
                    ->groupBy('departments.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();
            }

            if (Schema::hasTable('test_parameter_results')) {
                $departmentSummary['high_flags'] = $dateFilter(
                    $labFilter(
                        DB::table('test_parameter_results')
                            ->join('test_parameters', 'test_parameters.id', '=', 'test_parameter_results.test_parameter_id')
                            ->join('test_masters', 'test_masters.id', '=', 'test_parameters.test_master_id')
                            ->join('departments', 'departments.id', '=', 'test_masters.department_id')
                            ->whereIn('test_parameter_results.flag', ['HIGH', 'CRITICAL'])
                            ->select('departments.name', DB::raw('COUNT(*) as total')),
                        'test_parameter_results'
                    ),
                    'test_parameter_results.created_at'
                )
                    ->groupBy('departments.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();

                $departmentSummary['low_flags'] = $dateFilter(
                    $labFilter(
                        DB::table('test_parameter_results')
                            ->join('test_parameters', 'test_parameters.id', '=', 'test_parameter_results.test_parameter_id')
                            ->join('test_masters', 'test_masters.id', '=', 'test_parameters.test_master_id')
                            ->join('departments', 'departments.id', '=', 'test_masters.department_id')
                            ->where('test_parameter_results.flag', 'LOW')
                            ->select('departments.name', DB::raw('COUNT(*) as total')),
                        'test_parameter_results'
                    ),
                    'test_parameter_results.created_at'
                )
                    ->groupBy('departments.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();
            }

            $doctorSummary = [
                'total_referrals' => 0,
                'revenue' => 0,
                'top_tests' => collect(),
                'report_delay' => collect(),
            ];

            if (Schema::hasTable('invoices')) {
                $doctorInvoiceBase = $dateFilter(
                    $labFilter(
                        DB::table('invoices')
                            ->where('invoices.referral_type', 'doctor'),
                        'invoices'
                    ),
                    'invoices.created_at'
                );
                $doctorSummary['total_referrals'] = (int) (clone $doctorInvoiceBase)->count();
                $doctorSummary['revenue'] = (float) (clone $doctorInvoiceBase)->sum('net_total');

                if (Schema::hasTable('specimens') && Schema::hasTable('specimen_tests')) {
                    $doctorSummary['top_tests'] = $dateFilter(
                        $labFilter(
                            DB::table('specimen_tests')
                                ->join('specimens', 'specimens.id', '=', 'specimen_tests.specimen_id')
                                ->join('invoices', 'invoices.id', '=', 'specimens.invoice_id')
                                ->join('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                                ->where('invoices.referral_type', 'doctor')
                                ->select('test_masters.name', DB::raw('COUNT(*) as total')),
                            'specimen_tests'
                        ),
                        'specimen_tests.created_at'
                    )
                        ->groupBy('test_masters.name')
                        ->when(true, fn ($query) => $sortBy($query))
                        ->limit(10)
                        ->get();
                }

                if (Schema::hasTable('approvals')) {
                    $driver = DB::getDriverName();
                    $avgExpr = $driver === 'sqlite'
                        ? 'AVG((julianday(approvals.approved_at) - julianday(specimen_tests.created_at)) * 1440)'
                        : 'AVG(TIMESTAMPDIFF(MINUTE, specimen_tests.created_at, approvals.approved_at))';

                    $doctorSummary['report_delay'] = $dateFilter(
                        $labFilter(
                            DB::table('approvals')
                                ->join('specimen_tests', 'specimen_tests.id', '=', 'approvals.specimen_test_id')
                                ->join('specimens', 'specimens.id', '=', 'specimen_tests.specimen_id')
                                ->join('invoices', 'invoices.id', '=', 'specimens.invoice_id')
                                ->join('doctors', 'doctors.id', '=', 'invoices.referral_id')
                                ->where('invoices.referral_type', 'doctor')
                                ->whereNotNull('approvals.approved_at')
                                ->select('doctors.name', DB::raw($avgExpr . ' as avg_minutes')),
                            'approvals'
                        ),
                        'approvals.approved_at'
                    )
                        ->groupBy('doctors.name')
                        ->when(true, fn ($query) => $sortBy($query))
                        ->get();
                }
            }

            $supplierSummary = [
                'active_suppliers' => 0,
                'monthly_purchase' => 0,
                'outstanding' => 0,
                'delivery_delays' => 0,
                'supplier_performance' => collect(),
                'price_comparison' => collect(),
                'batch_issues' => collect(),
            ];

            if (Schema::hasTable('suppliers')) {
                $supplierBase = DB::table('suppliers');
                $supplierBase = $labFilter($supplierBase, 'suppliers');
                $supplierSummary['active_suppliers'] = (int) $supplierBase->count();

                if (Schema::hasTable('lab_stock_batches')) {
                    $batchBase = DB::table('lab_stock_batches')
                        ->leftJoin('suppliers', 'suppliers.id', '=', 'lab_stock_batches.supplier_id');
                    $batchBase = $labFilter($batchBase, 'lab_stock_batches');
                    $batchBase = $dateFilter($batchBase, 'lab_stock_batches.purchase_date');
                    $supplierSummary['monthly_purchase'] = (float) (clone $batchBase)
                        ->whereBetween('lab_stock_batches.purchase_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                        ->sum(DB::raw('lab_stock_batches.quantity * lab_stock_batches.unit_cost'));

                    $supplierSummary['supplier_performance'] = (clone $batchBase)
                        ->select(DB::raw('COALESCE(suppliers.company_name, "Unknown") as name'), DB::raw('SUM(lab_stock_batches.quantity * lab_stock_batches.unit_cost) as total'))
                        ->groupBy('name')
                        ->when(true, fn ($query) => $sortBy($query))
                        ->get();
                }
            }

            $highSummary = [
                'total_high' => 0,
                'critical_high' => 0,
                'tests' => collect(),
                'patients' => collect(),
            ];
            $lowSummary = [
                'total_low' => 0,
                'tests' => collect(),
                'patients' => collect(),
            ];

            if (Schema::hasTable('test_parameter_results')) {
                $highParamBase = $dateFilter(
                    $labFilter(
                        DB::table('test_parameter_results')
                            ->whereIn('flag', ['HIGH', 'CRITICAL']),
                        'test_parameter_results'
                    ),
                    'test_parameter_results.created_at'
                );
                $highSummary['total_high'] += (int) $highParamBase->count();
                $highSummary['critical_high'] += (int) (clone $highParamBase)->where('flag', 'CRITICAL')->count();

                $lowParamBase = $dateFilter(
                    $labFilter(
                        DB::table('test_parameter_results')
                            ->where('flag', 'LOW'),
                        'test_parameter_results'
                    ),
                    'test_parameter_results.created_at'
                );
                $lowSummary['total_low'] += (int) $lowParamBase->count();

                $highSummary['tests'] = $dateFilter(
                    $labFilter(
                        DB::table('test_parameter_results')
                            ->join('test_parameters', 'test_parameters.id', '=', 'test_parameter_results.test_parameter_id')
                            ->join('test_masters', 'test_masters.id', '=', 'test_parameters.test_master_id')
                            ->whereIn('test_parameter_results.flag', ['HIGH', 'CRITICAL'])
                            ->select('test_masters.name', DB::raw('COUNT(*) as total')),
                        'test_parameter_results'
                    ),
                    'test_parameter_results.created_at'
                )
                    ->groupBy('test_masters.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();

                $lowSummary['tests'] = $dateFilter(
                    $labFilter(
                        DB::table('test_parameter_results')
                            ->join('test_parameters', 'test_parameters.id', '=', 'test_parameter_results.test_parameter_id')
                            ->join('test_masters', 'test_masters.id', '=', 'test_parameters.test_master_id')
                            ->where('test_parameter_results.flag', 'LOW')
                            ->select('test_masters.name', DB::raw('COUNT(*) as total')),
                        'test_parameter_results'
                    ),
                    'test_parameter_results.created_at'
                )
                    ->groupBy('test_masters.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->get();

                $highSummary['patients'] = $dateFilter(
                    $labFilter(
                        DB::table('test_parameter_results')
                            ->join('specimen_tests', 'specimen_tests.id', '=', 'test_parameter_results.specimen_test_id')
                            ->join('specimens', 'specimens.id', '=', 'specimen_tests.specimen_id')
                            ->join('patients', 'patients.id', '=', 'specimens.patient_id')
                            ->whereIn('test_parameter_results.flag', ['HIGH', 'CRITICAL'])
                            ->select('patients.name', DB::raw('COUNT(*) as total')),
                        'test_parameter_results'
                    ),
                    'test_parameter_results.created_at'
                )
                    ->groupBy('patients.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->limit(10)
                    ->get();

                $lowSummary['patients'] = $dateFilter(
                    $labFilter(
                        DB::table('test_parameter_results')
                            ->join('specimen_tests', 'specimen_tests.id', '=', 'test_parameter_results.specimen_test_id')
                            ->join('specimens', 'specimens.id', '=', 'specimen_tests.specimen_id')
                            ->join('patients', 'patients.id', '=', 'specimens.patient_id')
                            ->where('test_parameter_results.flag', 'LOW')
                            ->select('patients.name', DB::raw('COUNT(*) as total')),
                        'test_parameter_results'
                    ),
                    'test_parameter_results.created_at'
                )
                    ->groupBy('patients.name')
                    ->when(true, fn ($query) => $sortBy($query))
                    ->limit(10)
                    ->get();
            }

            if (Schema::hasTable('test_results')) {
                $highTestBase = $dateFilter(
                    $labFilter(
                        DB::table('test_results')
                            ->whereIn('flag', ['HIGH', 'CRITICAL']),
                        'test_results'
                    ),
                    'test_results.entered_at'
                );
                $highSummary['total_high'] += (int) $highTestBase->count();
                $highSummary['critical_high'] += (int) (clone $highTestBase)->where('flag', 'CRITICAL')->count();

                $lowTestBase = $dateFilter(
                    $labFilter(
                        DB::table('test_results')
                            ->where('flag', 'LOW'),
                        'test_results'
                    ),
                    'test_results.entered_at'
                );
                $lowSummary['total_low'] += (int) $lowTestBase->count();
            }

            $suggestSummary = [
                'auto_suggestions' => 0,
                'doctor_notes' => 0,
                'follow_up' => 0,
                'repeat_test' => 0,
                'examples' => collect(),
            ];

            if (Schema::hasTable('approvals')) {
                $noteBase = DB::table('approvals')->whereNotNull('comment');
                $noteBase = $labFilter($noteBase, 'approvals');
                $noteBase = $dateFilter($noteBase, 'approvals.approved_at');
                $suggestSummary['doctor_notes'] = (int) $noteBase->count();

                $driver = DB::getDriverName();
                $commentColumn = $driver === 'sqlite' ? 'lower(comment)' : 'LOWER(comment)';
                $suggestSummary['follow_up'] = (int) (clone $noteBase)
                    ->whereRaw($commentColumn . " like '%follow%'")
                    ->count();
                $suggestSummary['repeat_test'] = (int) (clone $noteBase)
                    ->whereRaw($commentColumn . " like '%repeat%'")
                    ->count();

                $suggestSummary['examples'] = (clone $noteBase)
                    ->leftJoin('specimen_tests', 'specimen_tests.id', '=', 'approvals.specimen_test_id')
                    ->leftJoin('test_masters', 'test_masters.id', '=', 'specimen_tests.test_master_id')
                    ->select('test_masters.name', 'approvals.comment', 'approvals.approved_at')
                    ->orderByDesc('approvals.approved_at')
                    ->limit(10)
                    ->get();
            }

            $export = request()->query('export');
            if (in_array($export, ['csv', 'excel', 'pdf'], true)) {
                $summaryPayload = [
                    'tab' => $tab,
                    'labName' => $labName,
                    'testSummary' => [
                        'total_today' => $totalTestsToday,
                        'total_month' => $totalTestsMonth,
                        'total_period' => $totalTestsPeriod,
                        'pending' => $pendingTests,
                        'completed' => $completedTests,
                        'rejected' => $rejectedTests,
                        'avg_tat_minutes' => $avgTatMinutes,
                        'by_department' => $testsByDepartment,
                        'test_wise' => $testWise,
                        'package_wise' => $packageWise,
                        'centre_wise' => $centreWise,
                        'doctor_wise' => $doctorWise,
                    ],
                    'accountsSummary' => [
                        'total_invoiced' => $totalInvoiced,
                        'total_collected' => $totalCollected,
                        'outstanding' => $outstandingBalance,
                        'credit' => $creditAmount,
                        'refund' => $refundIssued,
                        'payments_by_method' => $paymentsByMethod,
                        'invoice_by_centre' => $invoiceByCentre,
                        'invoice_by_date' => $invoiceByDate,
                        'payments_by_user' => $paymentsByUser,
                    ],
                    'incomeSummary' => [
                        'gross_income' => $grossIncome,
                        'net_income' => $totalInvoiced,
                        'income_by_test' => $incomeByTest,
                        'income_by_department' => $incomeByDepartment,
                        'income_by_doctor' => $incomeByDoctor,
                        'income_by_centre' => $incomeByCentre,
                        'income_by_period' => $incomeByPeriod,
                    ],
                    'refundSummary' => $refundSummary,
                    'costSummary' => $costSummary,
                    'consumptionSummary' => $consumptionSummary,
                    'reorderSummary' => $reorderSummary,
                    'centreSummary' => $centreSummary,
                    'departmentSummary' => $departmentSummary,
                    'doctorSummary' => $doctorSummary,
                    'supplierSummary' => $supplierSummary,
                    'highSummary' => $highSummary,
                    'lowSummary' => $lowSummary,
                    'suggestSummary' => $suggestSummary,
                    'filters' => [
                        'from' => $from,
                        'to' => $to,
                        'from_time' => $fromTime,
                        'to_time' => $toTime,
                        'sort' => $sort,
                    ],
                ];

                if ($export === 'pdf') {
                    $view = view('admin.summary_export', $summaryPayload)->render();
                    $options = new \Dompdf\Options();
                    $options->set('isRemoteEnabled', true);
                    $dompdf = new \Dompdf\Dompdf($options);
                    $dompdf->loadHtml($view);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    $filename = 'summary-' . $tab . '-' . now()->format('Ymd-His') . '.pdf';
                    return Response::make($dompdf->output(), 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                }

                $rows = [];
                $rows[] = ['Lab', $labName];
                $rows[] = ['Tab', $tab];
                $rows[] = ['From', $fromDate ? $fromDate->format('Y-m-d H:i') : ''];
                $rows[] = ['To', $toDate ? $toDate->format('Y-m-d H:i') : ''];
                $rows[] = ['Generated At', now()->format('Y-m-d H:i')];
                $rows[] = [];
                $addSection = function (string $title, array $headers, array $dataRows) use (&$rows) {
                    $rows[] = [$title];
                    if (!empty($headers)) {
                        $rows[] = $headers;
                    }
                    foreach ($dataRows as $row) {
                        $rows[] = $row;
                    }
                    $rows[] = [];
                };

                $sectionRows = function ($items, callable $mapper) {
                    $out = [];
                    foreach ($items as $item) {
                        $out[] = $mapper($item);
                    }
                    return $out;
                };

                if ($tab === 'tests') {
                    $addSection('Test Summary', ['Metric', 'Value'], [
                        ['Total Tests (Today)', $totalTestsToday],
                        ['Total Tests (Month)', $totalTestsMonth],
                        ['Total Tests (Filtered)', $totalTestsPeriod],
                        ['Pending Tests', $pendingTests],
                        ['Completed Tests', $completedTests],
                        ['Rejected Samples', $rejectedTests],
                        ['Average TAT (min)', $avgTatMinutes ?: ''],
                    ]);
                    $addSection('Tests by Department', ['Department', 'Total'], $sectionRows($testsByDepartment, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Test-wise', ['Test', 'Total', 'Revenue'], $sectionRows($testWise, fn ($r) => [$r->name ?? '', $r->total ?? 0, $r->revenue ?? 0]));
                    $addSection('Package-wise', ['Package', 'Total'], $sectionRows($packageWise, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Centre-wise', ['Centre', 'Total'], $sectionRows($centreWise, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Doctor-wise', ['Doctor', 'Total', 'Revenue'], $sectionRows($doctorWise, fn ($r) => [$r->name ?? '', $r->total ?? 0, $r->revenue ?? 0]));
                } elseif ($tab === 'accounts') {
                    $addSection('Accounts Summary', ['Metric', 'Value'], [
                        ['Total Invoiced', $totalInvoiced],
                        ['Total Collected', $totalCollected],
                        ['Outstanding Balance', $outstandingBalance],
                        ['Credit Amount', $creditAmount],
                        ['Refund Issued', $refundIssued],
                    ]);
                    $addSection('Payments by Method', ['Method', 'Count', 'Total'], $sectionRows($paymentsByMethod, fn ($r) => [$r->method ?? '', $r->count ?? 0, $r->total ?? 0]));
                    $addSection('Invoice by Centre', ['Centre', 'Total'], $sectionRows($invoiceByCentre, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Invoice by Date', ['Date', 'Total'], $sectionRows($invoiceByDate, fn ($r) => [$r->day ?? '', $r->total ?? 0]));
                    $addSection('Payments by User', ['User', 'Total'], $sectionRows($paymentsByUser, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                } elseif ($tab === 'income') {
                    $addSection('Income Summary', ['Metric', 'Value'], [
                        ['Gross Income', $grossIncome],
                        ['Net Income', $totalInvoiced],
                    ]);
                    $addSection('Income by Test', ['Test', 'Total'], $sectionRows($incomeByTest, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Income by Department', ['Department', 'Total'], $sectionRows($incomeByDepartment, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Income by Doctor', ['Doctor', 'Total'], $sectionRows($incomeByDoctor, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Income by Centre', ['Centre', 'Total'], $sectionRows($incomeByCentre, fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Income by Date', ['Date', 'Total'], $sectionRows($incomeByPeriod, fn ($r) => [$r->day ?? '', $r->total ?? 0]));
                } elseif ($tab === 'refund') {
                    $addSection('Refund Summary', ['Metric', 'Value'], [
                        ['Total Refund Count', $refundSummary['total_count'] ?? 0],
                        ['Total Refund Amount', $refundSummary['total_amount'] ?? 0],
                    ]);
                    $addSection('Refund Reason Breakdown', ['Reason', 'Count', 'Amount'], $sectionRows($refundSummary['by_reason'] ?? [], fn ($r) => [$r->reason ?? '', $r->total ?? 0, $r->amount ?? 0]));
                    $addSection('Patient-wise', ['Patient', 'Amount'], $sectionRows($refundSummary['by_patient'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Test-wise', ['Test', 'Amount'], $sectionRows($refundSummary['by_test'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Date-wise', ['Date', 'Amount'], $sectionRows($refundSummary['by_date'] ?? [], fn ($r) => [$r->day ?? '', $r->total ?? 0]));
                    $addSection('Approved by', ['User', 'Amount'], $sectionRows($refundSummary['by_approver'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                } elseif ($tab === 'cost') {
                    $addSection('Cost Summary', ['Metric', 'Value'], [
                        ['Total Operational Cost', $costSummary['total_operational'] ?? 0],
                        ['Cost per Test', $costSummary['cost_per_test'] ?? 0],
                        ['Reagent Cost', $costSummary['reagent_cost'] ?? 0],
                        ['Staff Cost', $costSummary['staff_cost'] ?? 0],
                    ]);
                    $addSection('Department Cost', ['Department', 'Cost'], $sectionRows($costSummary['department_costs'] ?? [], fn ($r) => [$r->dept_name ?? ($r->name ?? ''), $r->total ?? 0]));
                    $addSection('Test-wise Margin', ['Test', 'Revenue', 'Cost', 'Margin'], $sectionRows($costSummary['test_margins'] ?? [], fn ($r) => [$r->name ?? '', $r->revenue ?? 0, $r->cost ?? 0, $r->margin ?? 0]));
                } elseif ($tab === 'consumption') {
                    $addSection('Consumption Summary', ['Metric', 'Value'], [
                        ['Reagent Consumption (Today)', $consumptionSummary['consumed_today'] ?? 0],
                        ['Reagent Consumption (Month)', $consumptionSummary['consumed_month'] ?? 0],
                        ['Wastage %', $consumptionSummary['wastage_percent'] ?? 0],
                    ]);
                    $addSection('Item Usage', ['Item', 'Quantity'], $sectionRows($consumptionSummary['item_usage'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Test vs Reagent Usage', ['Test', 'Item', 'Quantity'], $sectionRows($consumptionSummary['test_vs_reagent'] ?? [], fn ($r) => [$r->test_name ?? '', $r->item_name ?? '', $r->total ?? 0]));
                    $addSection('Expired Items', ['Item', 'Expiry Date', 'Remaining Qty'], $sectionRows($consumptionSummary['expired_items'] ?? [], fn ($r) => [$r->name ?? '', $r->expiry_date ?? '', $r->remaining_qty ?? 0]));
                } elseif ($tab === 'reorder') {
                    $addSection('Low Stock Items', ['Item', 'Available', 'Reorder Level'], $sectionRows($reorderSummary['low_stock'] ?? [], fn ($r) => [$r->name ?? '', $r->total_qty ?? 0, $r->reorder_level ?? 0]));
                    $addSection('Out of Stock Items', ['Item', 'Available'], $sectionRows($reorderSummary['out_of_stock'] ?? [], fn ($r) => [$r->name ?? '', $r->total_qty ?? 0]));
                    $addSection('Supplier-wise', ['Supplier', 'Item', 'Remaining'], $sectionRows($reorderSummary['supplier_wise'] ?? [], fn ($r) => [$r->supplier ?? '', $r->item_name ?? '', $r->total_qty ?? 0]));
                } elseif ($tab === 'centre') {
                    $addSection('Tests per Centre', ['Centre', 'Total'], $sectionRows($centreSummary['tests_per_centre'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Income per Centre', ['Centre', 'Income'], $sectionRows($centreSummary['income_per_centre'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Pending Samples', ['Centre', 'Pending'], $sectionRows($centreSummary['pending_samples'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Rejection Rate', ['Centre', 'Rejected', 'Total', 'Rate'], $sectionRows($centreSummary['rejection_rate'] ?? [], function ($r) {
                        $rate = ($r->total ?? 0) > 0 ? round(($r->rejected / $r->total) * 100, 2) : 0;
                        return [$r->name ?? '', $r->rejected ?? 0, $r->total ?? 0, $rate];
                    }));
                } elseif ($tab === 'department') {
                    $addSection('Tests Performed', ['Department', 'Total'], $sectionRows($departmentSummary['tests_performed'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Pending / Delayed', ['Department', 'Total'], $sectionRows($departmentSummary['pending'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('High Results', ['Department', 'Total'], $sectionRows($departmentSummary['high_flags'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Low Results', ['Department', 'Total'], $sectionRows($departmentSummary['low_flags'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Department Revenue', ['Department', 'Revenue'], $sectionRows($departmentSummary['revenue'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                } elseif ($tab === 'doctor') {
                    $addSection('Doctor Summary', ['Metric', 'Value'], [
                        ['Total Referrals', $doctorSummary['total_referrals'] ?? 0],
                        ['Revenue Generated', $doctorSummary['revenue'] ?? 0],
                    ]);
                    $addSection('Top Tests Ordered', ['Test', 'Total'], $sectionRows($doctorSummary['top_tests'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Report Delay (Avg Minutes)', ['Doctor', 'Avg Minutes'], $sectionRows($doctorSummary['report_delay'] ?? [], fn ($r) => [$r->name ?? '', $r->avg_minutes ?? 0]));
                } elseif ($tab === 'supplier') {
                    $addSection('Supplier Summary', ['Metric', 'Value'], [
                        ['Active Suppliers', $supplierSummary['active_suppliers'] ?? 0],
                        ['Monthly Purchase Value', $supplierSummary['monthly_purchase'] ?? 0],
                        ['Outstanding Payables', $supplierSummary['outstanding'] ?? 0],
                        ['Delivery Delays', $supplierSummary['delivery_delays'] ?? 0],
                    ]);
                    $addSection('Supplier Performance', ['Supplier', 'Total Purchase'], $sectionRows($supplierSummary['supplier_performance'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                } elseif ($tab === 'high') {
                    $addSection('High Result Summary', ['Metric', 'Value'], [
                        ['Total High Results', $highSummary['total_high'] ?? 0],
                        ['Critical High Results', $highSummary['critical_high'] ?? 0],
                    ]);
                    $addSection('Tests with Frequent High Values', ['Test', 'Total'], $sectionRows($highSummary['tests'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Patient-wise', ['Patient', 'Total'], $sectionRows($highSummary['patients'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                } elseif ($tab === 'low') {
                    $addSection('Low Result Summary', ['Metric', 'Value'], [
                        ['Total Low Results', $lowSummary['total_low'] ?? 0],
                    ]);
                    $addSection('Tests with Frequent Low Values', ['Test', 'Total'], $sectionRows($lowSummary['tests'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                    $addSection('Patient-wise', ['Patient', 'Total'], $sectionRows($lowSummary['patients'] ?? [], fn ($r) => [$r->name ?? '', $r->total ?? 0]));
                } elseif ($tab === 'suggest') {
                    $addSection('Suggest / Note Summary', ['Metric', 'Value'], [
                        ['Auto Suggestions Triggered', $suggestSummary['auto_suggestions'] ?? 0],
                        ['Manual Doctor Notes', $suggestSummary['doctor_notes'] ?? 0],
                        ['Follow-up Recommended', $suggestSummary['follow_up'] ?? 0],
                        ['Repeat Test Suggested', $suggestSummary['repeat_test'] ?? 0],
                    ]);
                    $addSection('Latest Notes', ['Test', 'Note', 'Date'], $sectionRows($suggestSummary['examples'] ?? [], fn ($r) => [$r->name ?? '', $r->comment ?? '', $r->approved_at ?? '']));
                }

                $ext = $export === 'excel' ? 'xls' : 'csv';
                $filename = 'summary-' . $tab . '-' . now()->format('Ymd-His') . '.' . $ext;

                return Response::streamDownload(function () use ($rows) {
                    $handle = fopen('php://output', 'w');
                    foreach ($rows as $row) {
                        fputcsv($handle, $row);
                    }
                    fclose($handle);
                }, $filename, [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                ]);
            }

            return view('admin.summary', [
                'tab' => $tab,
                'testSummary' => [
                    'total_today' => $totalTestsToday,
                    'total_month' => $totalTestsMonth,
                    'total_period' => $totalTestsPeriod,
                    'pending' => $pendingTests,
                    'completed' => $completedTests,
                    'rejected' => $rejectedTests,
                    'avg_tat_minutes' => $avgTatMinutes,
                    'by_department' => $testsByDepartment,
                    'test_wise' => $testWise,
                    'package_wise' => $packageWise,
                    'centre_wise' => $centreWise,
                    'doctor_wise' => $doctorWise,
                ],
                'accountsSummary' => [
                    'total_invoiced' => $totalInvoiced,
                    'total_collected' => $totalCollected,
                    'outstanding' => $outstandingBalance,
                    'credit' => $creditAmount,
                    'refund' => $refundIssued,
                    'payments_by_method' => $paymentsByMethod,
                    'invoice_by_centre' => $invoiceByCentre,
                    'invoice_by_date' => $invoiceByDate,
                    'payments_by_user' => $paymentsByUser,
                ],
                'incomeSummary' => [
                    'gross_income' => $grossIncome,
                    'net_income' => $totalInvoiced,
                    'income_by_test' => $incomeByTest,
                    'income_by_department' => $incomeByDepartment,
                    'income_by_doctor' => $incomeByDoctor,
                    'income_by_centre' => $incomeByCentre,
                    'income_by_period' => $incomeByPeriod,
                ],
                'refundSummary' => $refundSummary,
                'costSummary' => $costSummary,
                'consumptionSummary' => $consumptionSummary,
                'reorderSummary' => $reorderSummary,
                'centreSummary' => $centreSummary,
                'departmentSummary' => $departmentSummary,
                'doctorSummary' => $doctorSummary,
                'supplierSummary' => $supplierSummary,
                'highSummary' => $highSummary,
                'lowSummary' => $lowSummary,
                'suggestSummary' => $suggestSummary,
                'filters' => [
                    'from' => $from,
                    'to' => $to,
                    'from_time' => $fromTime,
                    'to_time' => $toTime,
                    'sort' => $sort,
                ],
            ]);
        }

        if ($page === 'print-worksheet') {
            $specimenId = (int) request()->query('specimen_id', 0);
            if ($specimenId > 0) {
                $specimen = \App\Models\Specimen::query()
                    ->with(['patient', 'center', 'tests.testMaster.parameters'])
                    ->findOrFail($specimenId);

                return view('admin.print_worksheet', [
                    'specimen' => $specimen,
                ]);
            }

            $from = request()->query('from');
            $to = request()->query('to');
            $specimenNo = request()->query('specimen_no');
            $nic = request()->query('nic');

            $sort = request()->query('sort', 'date_desc');
            $layout = request()->query('layout', 'single');
            $compact = request()->query('compact', '0');

            $query = \App\Models\Specimen::query()
                ->with(['patient', 'center', 'tests.testMaster.parameters'])
                ->orderByDesc('id');

            if ($from) {
                $query->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $query->whereDate('created_at', '<=', $to);
            }
            if ($specimenNo) {
                $query->where('specimen_no', 'like', $specimenNo . '%');
            }
            if ($nic) {
                $query->whereHas('patient', function ($q) use ($nic) {
                    $q->where('nic', 'like', $nic . '%');
                });
            }

            if ($sort === 'date_asc') {
                $query->orderBy('created_at', 'asc');
            } elseif ($sort === 'specimen_asc') {
                $query->orderBy('specimen_no', 'asc');
            } elseif ($sort === 'specimen_desc') {
                $query->orderBy('specimen_no', 'desc');
            }

            return view('admin.print_worksheet', [
                'specimens' => $query->limit(100)->get(),
                'filters' => [
                    'from' => $from,
                    'to' => $to,
                    'specimen_no' => $specimenNo,
                    'nic' => $nic,
                    'sort' => $sort,
                    'layout' => $layout,
                    'compact' => $compact,
                ],
            ]);
        }

        if ($page === 'patient-stats') {
            $from = request()->query('from');
            $to = request()->query('to');
            $group = request()->query('group', 'day');
            $search = trim((string) request()->query('q', ''));

            $driver = DB::getDriverName();
            $dayExpr = $driver === 'sqlite' ? "date(specimens.created_at)" : "DATE(specimens.created_at)";
            $monthExpr = $driver === 'sqlite' ? "strftime('%Y-%m', specimens.created_at)" : "DATE_FORMAT(specimens.created_at, '%Y-%m')";
            $yearExpr = $driver === 'sqlite' ? "strftime('%Y', specimens.created_at)" : "YEAR(specimens.created_at)";

            $groupExpr = match ($group) {
                'month' => $monthExpr,
                'year' => $yearExpr,
                default => $dayExpr,
            };

            $specimenQuery = \App\Models\Specimen::query()
                ->with(['patient', 'center', 'tests.testMaster'])
                ->orderByDesc('created_at');

            if ($from) {
                $specimenQuery->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $specimenQuery->whereDate('created_at', '<=', $to);
            }
            if ($search !== '') {
                $specimenQuery->where(function ($q) use ($search) {
                    $q->where('specimen_no', 'like', '%' . $search . '%')
                        ->orWhereHas('patient', function ($sub) use ($search) {
                            $sub->where('name', 'like', '%' . $search . '%')
                                ->orWhere('nic', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('tests.testMaster', function ($sub) use ($search) {
                            $sub->where('name', 'like', '%' . $search . '%')
                                ->orWhere('code', 'like', '%' . $search . '%');
                        });
                });
            }

            $specimens = $specimenQuery->limit(200)->get();

            $countQuery = \App\Models\Specimen::query();
            if ($from) {
                $countQuery->whereDate('created_at', '>=', $from);
            }
            if ($to) {
                $countQuery->whereDate('created_at', '<=', $to);
            }

            $patientCount = (clone $countQuery)->distinct('patient_id')->count('patient_id');
            $specimenCount = (clone $countQuery)->count();
            $testCount = \App\Models\SpecimenTest::query()
                ->when($from, fn ($q) => $q->whereHas('specimen', fn ($s) => $s->whereDate('created_at', '>=', $from)))
                ->when($to, fn ($q) => $q->whereHas('specimen', fn ($s) => $s->whereDate('created_at', '<=', $to)))
                ->count();

            $trendRows = \App\Models\Specimen::query()
                ->select(DB::raw($groupExpr . ' as label'), DB::raw('COUNT(DISTINCT patient_id) as patient_count'))
                ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
                ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
                ->groupBy('label')
                ->orderBy('label')
                ->get();

            return view('admin.patient_stats', [
                'specimens' => $specimens,
                'trendRows' => $trendRows,
                'filters' => [
                    'from' => $from,
                    'to' => $to,
                    'group' => $group,
                    'q' => $search,
                ],
                'totals' => [
                    'patients' => $patientCount,
                    'specimens' => $specimenCount,
                    'tests' => $testCount,
                ],
            ]);
        }

        if ($page === 'accounts') {
            $todayRevenue = 0;
            $monthlyRevenue = 0;
            $pendingPayments = 0;
            $invoiceCounts = collect();
            $paymentMethods = collect();
            $recentInvoices = collect();
            $revenueTrend = collect();
            $topRevenueTests = collect();

            if (Schema::hasTable('invoices')) {
                $todayRevenue = (float) Invoice::whereDate('created_at', today())->sum('net_total');
                $monthlyRevenue = (float) Invoice::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->sum('net_total');
                $pendingPayments = (int) Invoice::where('payment_status', '!=', 'PAID')->count();
                $invoiceCounts = Invoice::select('payment_status', DB::raw('COUNT(*) as total'))
                    ->groupBy('payment_status')
                    ->get();
                $paymentMethods = Invoice::select('payment_mode', DB::raw('COUNT(*) as total'))
                    ->whereNotNull('payment_mode')
                    ->groupBy('payment_mode')
                    ->get();
                $recentInvoices = Invoice::orderByDesc('created_at')->limit(10)->get();
                $revenueTrend = collect(range(6, 0))->map(function ($offset) {
                    $day = now()->subDays($offset);
                    return [
                        'label' => $day->format('D'),
                        'total' => (float) Invoice::whereDate('created_at', $day)->sum('net_total'),
                    ];
                });
            }

            if (Schema::hasTable('specimen_tests') && Schema::hasTable('test_masters')) {
                $topRevenueTests = SpecimenTest::select('test_masters.name', DB::raw('SUM(specimen_tests.price) as total'))
                    ->join('test_masters', 'specimen_tests.test_master_id', '=', 'test_masters.id')
                    ->groupBy('test_masters.name')
                    ->orderByDesc('total')
                    ->limit(6)
                    ->get();
            }

            return view('admin.accounts', [
                'todayRevenue' => $todayRevenue,
                'monthlyRevenue' => $monthlyRevenue,
                'pendingPayments' => $pendingPayments,
                'invoiceCounts' => $invoiceCounts,
                'paymentMethods' => $paymentMethods,
                'recentInvoices' => $recentInvoices,
                'revenueTrend' => $revenueTrend,
                'topRevenueTests' => $topRevenueTests,
            ]);
        }

        $title = trim(str_replace('-', ' ', $page));
        $title = ucwords($title);

        return view('admin.placeholder', [
            'pageTitle' => $title,
        ]);
    }
}

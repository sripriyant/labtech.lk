<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CenterController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\EditResultController;
use App\Http\Controllers\DemoAccountController;
use App\Http\Controllers\LabStockController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PatientInformationController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResultEntryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShopAdminController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TestMasterController;
use App\Http\Controllers\LabManagementController;
use App\Http\Controllers\TestParameterController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ValidationController;
use App\Models\Banner;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $banners = collect();

    if (Schema::hasTable('banners')) {
        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    $settings = [];
    if (Schema::hasTable('settings')) {
        $settings = Setting::query()->whereNull('lab_id')->pluck('value', 'key')->all();
    }

    return view('welcome', [
        'banners' => $banners,
        'settings' => $settings,
    ]);
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/admin/banners', [BannerController::class, 'index'])->name('banners.index');
    Route::post('/admin/banners', [BannerController::class, 'store'])->name('banners.store');
    Route::post('/admin/banners/{banner}/delete', [BannerController::class, 'destroy'])->name('banners.destroy');
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/page/{page}', [AdminController::class, 'placeholder'])->name('admin.placeholder');

    Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/admin/departments', [DepartmentController::class, 'store'])->name('departments.store');

    Route::get('/admin/centers', [CenterController::class, 'index'])->name('centers.index');
    Route::post('/admin/centers', [CenterController::class, 'store'])->name('centers.store');
    Route::post('/admin/centers/copy', [CenterController::class, 'copyToLabs'])->name('centers.copy');
    Route::post('/admin/centers/{center}', [CenterController::class, 'update'])->name('centers.update');
    Route::post('/admin/centers/{center}/delete', [CenterController::class, 'destroy'])->name('centers.destroy');

    Route::get('/admin/doctors', [DoctorController::class, 'index'])->name('doctors.index');
    Route::post('/admin/doctors', [DoctorController::class, 'store'])->name('doctors.store');
    Route::post('/admin/doctors/{doctor}', [DoctorController::class, 'update'])->name('doctors.update');
    Route::post('/admin/doctors/{doctor}/delete', [DoctorController::class, 'destroy'])->name('doctors.destroy');
    Route::post('/admin/doctors/copy', [DoctorController::class, 'copyToLabs'])->name('doctors.copy');

    Route::get('/admin/patient-information', [PatientInformationController::class, 'index'])->name('patient.information');
    Route::get('/admin/patient-information/{patient}/edit', [PatientInformationController::class, 'edit'])->name('patient.information.edit');
    Route::post('/admin/patient-information/{patient}', [PatientInformationController::class, 'update'])->name('patient.information.update');
    Route::post('/admin/patient-information/{patient}/delete', [PatientInformationController::class, 'destroy'])->name('patient.information.destroy');
    Route::get('/admin/stock', [LabStockController::class, 'index'])->name('admin.stock.index');
    Route::post('/admin/stock/items', [LabStockController::class, 'storeItem'])->name('admin.stock.items.store');
    Route::post('/admin/stock/batches', [LabStockController::class, 'storeBatch'])->name('admin.stock.batches.store');
    Route::post('/admin/stock/consumption', [LabStockController::class, 'storeConsumption'])->name('admin.stock.consumption.store');
    Route::get('/admin/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::post('/admin/locations', [LocationController::class, 'store'])->name('locations.store');
    Route::post('/admin/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::get('/admin/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/admin/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::post('/admin/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');

    Route::get('/admin/tests', [TestMasterController::class, 'index'])->name('tests.index');
    Route::post('/admin/tests', [TestMasterController::class, 'store'])->name('tests.store');
    Route::post('/admin/tests/price-list', [TestMasterController::class, 'priceList'])->name('tests.price_list');
    Route::post('/admin/tests/copy', [TestMasterController::class, 'copyToLabs'])->name('tests.copy');
    Route::post('/admin/tests/bulk-delete', [TestMasterController::class, 'bulkDelete'])->name('tests.bulk_delete');
    Route::post('/admin/tests/{test}', [TestMasterController::class, 'update'])->name('tests.update');
    Route::post('/admin/tests/{test}/delete', [TestMasterController::class, 'destroy'])->name('tests.destroy');
    Route::get('/admin/tests/{test}/parameters', [TestParameterController::class, 'index'])->name('tests.parameters');
    Route::post('/admin/tests/{test}/parameters', [TestParameterController::class, 'store'])->name('tests.parameters.store');
    Route::post('/admin/tests/{test}/parameters/{parameter}', [TestParameterController::class, 'update'])->name('tests.parameters.update');
    Route::post('/admin/tests/{test}/parameters/{parameter}/delete', [TestParameterController::class, 'destroy'])->name('tests.parameters.destroy');
    Route::get('/admin/packages', [PackageController::class, 'index'])->name('packages.index');
    Route::post('/admin/packages', [PackageController::class, 'store'])->name('packages.store');
    Route::post('/admin/packages/{package}', [PackageController::class, 'update'])->name('packages.update');
    Route::post('/admin/packages/{package}/delete', [PackageController::class, 'destroy'])->name('packages.destroy');

    Route::get('/admin/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/admin/labs', [LabManagementController::class, 'index'])->name('labs.index');
    Route::post('/admin/demo-accounts', [DemoAccountController::class, 'store'])->name('demo-accounts.store');
    Route::post('/admin/labs', [LabManagementController::class, 'store'])->name('labs.store');
    Route::post('/admin/labs/{lab}', [LabManagementController::class, 'update'])->name('labs.update');
    Route::delete('/admin/labs/{lab}', [LabManagementController::class, 'destroy'])->name('labs.destroy');
    Route::get('/admin/users', function () {
        return redirect()->route('settings.index');
    })->name('users.index');
    Route::post('/admin/settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::post('/admin/settings/report-copy', [SettingsController::class, 'copyReportSettings'])->name('settings.report.copy');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::post('/admin/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::get('/admin/shop', [ShopAdminController::class, 'index'])->name('admin.shop.index');
    Route::post('/admin/shop', [ShopAdminController::class, 'store'])->name('admin.shop.store');
    Route::post('/admin/shop/{product}', [ShopAdminController::class, 'update'])->name('admin.shop.update');
    Route::post('/admin/shop/categories', [ShopAdminController::class, 'storeCategory'])->name('admin.shop.categories.store');
    Route::post('/admin/shop/categories/{category}', [ShopAdminController::class, 'updateCategory'])->name('admin.shop.categories.update');
    Route::post('/admin/shop/categories/{category}/delete', [ShopAdminController::class, 'destroyCategory'])->name('admin.shop.categories.destroy');
    Route::get('/admin/promo-codes', [PromoCodeController::class, 'index'])->name('promo-codes.index');
    Route::post('/admin/promo-codes', [PromoCodeController::class, 'store'])->name('promo-codes.store');
    Route::post('/admin/promo-codes/{promoCode}', [PromoCodeController::class, 'update'])->name('promo-codes.update');
    Route::post('/admin/promo-codes/{promoCode}/delete', [PromoCodeController::class, 'destroy'])->name('promo-codes.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');
    Route::get('/billing/print', [BillingController::class, 'printList'])->name('billing.print.list');
    Route::get('/billing/print/{specimen}', [BillingController::class, 'printSpecimen'])->name('billing.print');
    Route::get('/billing/tests', [BillingController::class, 'searchTests'])->name('billing.tests');
    Route::get('/billing/products', [BillingController::class, 'searchProducts'])->name('billing.products');
    Route::get('/billing/patients', [BillingController::class, 'searchPatients'])->name('billing.patients');
    Route::get('/billing/specimen', [BillingController::class, 'findSpecimen'])->name('billing.specimen');
});

Route::get('/results/entry', [ResultEntryController::class, 'index'])->name('results.entry');
Route::post('/results/entry', [ResultEntryController::class, 'store'])->name('results.entry.store');
Route::get('/results/validate', [ValidationController::class, 'index'])->name('results.validate');
Route::post('/results/validate', [ValidationController::class, 'action'])->name('results.validate.action');

Route::get('/results/edit', [EditResultController::class, 'index'])->name('results.edit');
Route::post('/results/edit/{specimenTest}', [EditResultController::class, 'update'])->name('results.edit.update');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/latest', [ReportController::class, 'latest'])->name('reports.latest');
Route::get('/reports/{specimenTest}', [ReportController::class, 'show'])->name('reports.show');
Route::get('/reports/assets/{type}', [ReportController::class, 'asset'])->name('reports.asset');
Route::get('/track-report', [ReportController::class, 'track'])->name('reports.track');
Route::post('/track-report', [ReportController::class, 'trackRequest'])->name('reports.track.request');
Route::get('/track-report/verify', [ReportController::class, 'trackVerify'])->name('reports.track.verify');
Route::post('/track-report/verify', [ReportController::class, 'trackConfirm'])->name('reports.track.confirm');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::post('/shop/cart/add', [ShopController::class, 'addToCart'])->name('shop.cart.add');
Route::post('/shop/cart/update', [ShopController::class, 'updateCart'])->name('shop.cart.update');
Route::post('/shop/cart/remove', [ShopController::class, 'removeFromCart'])->name('shop.cart.remove');
Route::get('/shop/checkout', [ShopController::class, 'checkout'])->name('shop.checkout');
Route::post('/shop/checkout', [ShopController::class, 'placeOrder'])->name('shop.checkout.submit');

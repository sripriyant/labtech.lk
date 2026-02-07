<?php

namespace App\Http\Controllers;

use App\Models\LabStockBatch;
use App\Models\LabStockItem;
use App\Models\TestMaster;
use App\Models\TestStockConsumption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LabStockController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('admin.dashboard');

        $items = LabStockItem::query()
            ->with(['batches' => function ($q) {
                $q->orderByRaw('expiry_date is null, expiry_date asc')
                    ->orderBy('purchase_date');
            }])
            ->orderBy('name')
            ->get();

        $suppliers = \App\Models\Supplier::query()
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $tests = TestMaster::query()
            ->orderBy('name')
            ->get();

        $consumptions = TestStockConsumption::query()
            ->with(['test', 'item'])
            ->orderBy('test_master_id')
            ->get();

        return view('admin.stock.index', [
            'items' => $items,
            'suppliers' => $suppliers,
            'tests' => $tests,
            'consumptions' => $consumptions,
        ]);
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:lab_stock_items,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'reorder_qty' => ['nullable', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        LabStockItem::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'reorder_level' => $data['reorder_level'] ?? 0,
            'reorder_qty' => $data['reorder_qty'] ?? 0,
            'unit' => $data['unit'] ?? null,
            'unit_price' => $data['unit_price'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('admin.stock.index');
    }

    public function storeBatch(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $data = $request->validate([
            'lab_stock_item_id' => ['required', 'integer', 'exists:lab_stock_items,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'purchase_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        LabStockBatch::create([
            'lab_stock_item_id' => $data['lab_stock_item_id'],
            'supplier_id' => $data['supplier_id'] ?? null,
            'quantity' => $data['quantity'],
            'remaining_qty' => $data['quantity'],
            'purchase_date' => $data['purchase_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'unit_cost' => $data['unit_cost'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.stock.index');
    }

    public function storeConsumption(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $data = $request->validate([
            'test_master_id' => ['required', 'integer', 'exists:test_masters,id'],
            'lab_stock_item_id' => ['required', 'integer', 'exists:lab_stock_items,id'],
            'quantity_per_test' => ['required', 'numeric', 'min:0.01'],
        ]);

        TestStockConsumption::updateOrCreate(
            [
                'test_master_id' => $data['test_master_id'],
                'lab_stock_item_id' => $data['lab_stock_item_id'],
            ],
            [
                'quantity_per_test' => $data['quantity_per_test'],
            ]
        );

        return redirect()->route('admin.stock.index');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('admin.dashboard');

        if (!Schema::hasTable('promo_codes')) {
            return view('admin.promo_codes.index', [
                'promoCodes' => collect(),
                'tableMissing' => true,
            ]);
        }

        $defaults = [5, 10, 15, 20, 25, 30, 50, 100];
        foreach ($defaults as $pct) {
            PromoCode::withoutGlobalScopes()->firstOrCreate(
                ['code' => $pct . '%'],
                ['type' => 'PERCENT', 'value' => $pct, 'is_active' => true]
            );
        }

        $promoCodes = PromoCode::query()->orderBy('value')->get();

        return view('admin.promo_codes.index', [
            'promoCodes' => $promoCodes,
            'tableMissing' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes,code'],
            'type' => ['required', 'in:PERCENT,FLAT'],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ]);

        $data['is_active'] = !empty($data['is_active']);

        PromoCode::query()->create($data);

        return redirect()->route('promo-codes.index')->with('status', 'Promo code saved');
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:promo_codes,code,' . $promoCode->id],
            'type' => ['required', 'in:PERCENT,FLAT'],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ]);

        $data['is_active'] = !empty($data['is_active']);

        $promoCode->update($data);

        return redirect()->route('promo-codes.index')->with('status', 'Promo code updated');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $promoCode->delete();

        return redirect()->route('promo-codes.index')->with('status', 'Promo code deleted');
    }
}

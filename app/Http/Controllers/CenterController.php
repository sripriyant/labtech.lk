<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Lab;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CenterController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('centers.manage');

        $user = request()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $centersQuery = Center::query()->orderBy('name');
        if ($isSuperAdmin) {
            $centersQuery->whereNull('lab_id');
        }
        $centers = $centersQuery->get();

        return view('admin.centers.index', [
            'centers' => $centers,
            'isSuperAdmin' => $isSuperAdmin,
            'labs' => $isSuperAdmin ? Lab::query()->orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('centers.manage');

        $currentUser = $request->user();
        $labId = ($currentUser && $currentUser->isSuperAdmin()) ? null : ($currentUser?->lab_id);

        $rules = [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('centers', 'code')->where(fn ($query) => $query->where('lab_id', $labId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'referral_discount_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
        if ($currentUser && $currentUser->isSuperAdmin()) {
            $rules['copy_lab_ids'] = ['nullable', 'array'];
            $rules['copy_lab_ids.*'] = ['integer', 'exists:labs,id'];
        }

        $data = $request->validate($rules);

        $center = Center::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'referral_discount_pct' => $data['referral_discount_pct'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        if ($currentUser && $currentUser->isSuperAdmin() && !empty($data['copy_lab_ids'])) {
            $targetLabs = Lab::query()
                ->whereIn('id', $data['copy_lab_ids'])
                ->get();
            foreach ($targetLabs as $lab) {
                $this->copyCenterToLab($center, $lab);
            }
        }

        return redirect()->route('centers.index');
    }

    public function update(Request $request, Center $center): RedirectResponse
    {
        $this->requirePermission('centers.manage');

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('centers', 'code')
                    ->where(fn ($query) => $query->where('lab_id', $center->lab_id))
                    ->ignore($center->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'referral_discount_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $center->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'referral_discount_pct' => $data['referral_discount_pct'] ?? 0,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('centers.index');
    }

    public function destroy(Center $center): RedirectResponse
    {
        $this->requirePermission('centers.manage');

        $center->delete();

        return redirect()->route('centers.index');
    }

    public function copyToLabs(Request $request): RedirectResponse
    {
        $this->requirePermission('centers.manage');

        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'center_id' => ['required', 'integer', 'exists:centers,id'],
            'lab_ids' => ['required', 'array', 'min:1'],
            'lab_ids.*' => ['integer', 'exists:labs,id'],
        ]);

        $center = Center::withoutGlobalScopes()
            ->whereNull('lab_id')
            ->findOrFail($data['center_id']);

        $labs = Lab::query()
            ->whereIn('id', $data['lab_ids'])
            ->get();

        DB::transaction(function () use ($center, $labs): void {
            foreach ($labs as $lab) {
                $this->copyCenterToLab($center, $lab);
            }
        });

        return redirect()->route('centers.index');
    }

    private function copyCenterToLab(Center $center, Lab $lab): Center
    {
        $existing = Center::withoutGlobalScopes()
            ->where('lab_id', $lab->id)
            ->where('code', $center->code)
            ->first();
        if ($existing) {
            return $existing;
        }

        $parentClone = null;
        if ($center->parent_center_id) {
            $parent = Center::withoutGlobalScopes()->find($center->parent_center_id);
            if ($parent) {
                $parentClone = $this->copyCenterToLab($parent, $lab);
            }
        }

        $clone = $center->replicate();
        $clone->lab_id = $lab->id;
        $clone->parent_center_id = $parentClone?->id;
        $clone->save();

        return $clone;
    }
}

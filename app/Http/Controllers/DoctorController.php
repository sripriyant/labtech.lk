<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Lab;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('doctors.manage');

        $user = request()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $doctorQuery = Doctor::query()->orderBy('name');
        if ($isSuperAdmin) {
            $doctorQuery->whereNull('lab_id');
        }
        $doctors = $doctorQuery->get();

        return view('admin.doctors.index', [
            'doctors' => $doctors,
            'isSuperAdmin' => $isSuperAdmin,
            'labs' => $isSuperAdmin ? Lab::query()->orderBy('name')->get() : collect(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requirePermission('doctors.manage');

        $currentUser = $request->user();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'registration_no' => ['nullable', 'string', 'max:100'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'referral_discount_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'can_approve' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
        if ($currentUser && $currentUser->isSuperAdmin()) {
            $rules['copy_lab_ids'] = ['nullable', 'array'];
            $rules['copy_lab_ids.*'] = ['integer', 'exists:labs,id'];
        }

        $data = $request->validate($rules);

        $doctor = Doctor::create([
            'name' => $data['name'],
            'registration_no' => $data['registration_no'] ?? null,
            'specialty' => $data['specialty'] ?? null,
            'referral_discount_pct' => $data['referral_discount_pct'] ?? 0,
            'can_approve' => (bool) ($data['can_approve'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        if ($currentUser && $currentUser->isSuperAdmin() && !empty($data['copy_lab_ids'])) {
            $targetLabs = Lab::query()
                ->whereIn('id', $data['copy_lab_ids'])
                ->get();
            foreach ($targetLabs as $lab) {
                $this->copyDoctorToLab($doctor, $lab);
            }
        }

        return redirect()->route('doctors.index');
    }

    public function copyToLabs(Request $request): RedirectResponse
    {
        $this->requirePermission('doctors.manage');

        $currentUser = $request->user();
        if (!$currentUser || !$currentUser->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'lab_ids' => ['required', 'array', 'min:1'],
            'lab_ids.*' => ['integer', 'exists:labs,id'],
        ]);

        $doctor = Doctor::withoutGlobalScopes()
            ->whereNull('lab_id')
            ->findOrFail($data['doctor_id']);

        $labs = Lab::query()
            ->whereIn('id', $data['lab_ids'])
            ->get();

        DB::transaction(function () use ($doctor, $labs): void {
        foreach ($labs as $lab) {
            $this->copyDoctorToLab($doctor, $lab);
        }
        });

        return redirect()->route('doctors.index');
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->requirePermission('doctors.manage');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'registration_no' => ['nullable', 'string', 'max:100'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'referral_discount_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'can_approve' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $doctor->update([
            'name' => $data['name'],
            'registration_no' => $data['registration_no'] ?? null,
            'specialty' => $data['specialty'] ?? null,
            'referral_discount_pct' => $data['referral_discount_pct'] ?? 0,
            'can_approve' => (bool) ($data['can_approve'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('doctors.index');
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->requirePermission('doctors.manage');
        $doctor->delete();
        return redirect()->route('doctors.index');
    }

    private function copyDoctorToLab(Doctor $doctor, Lab $lab): Doctor
    {
        $query = Doctor::withoutGlobalScopes()
            ->where('lab_id', $lab->id)
            ->where('name', $doctor->name);

        if (!empty($doctor->registration_no)) {
            $query->where('registration_no', $doctor->registration_no);
        } else {
            $query->whereNull('registration_no');
        }

        $existing = $query->first();
        if ($existing) {
            return $existing;
        }

        $clone = $doctor->replicate();
        $clone->lab_id = $lab->id;
        $clone->save();

        return $clone;
    }
}

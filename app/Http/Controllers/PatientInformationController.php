<?php

namespace App\Http\Controllers;

use App\Models\SpecimenTest;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PatientInformationController extends Controller
{
    public function index(Request $request): View
    {
        $this->requirePermission('admin.dashboard');

        $letter = strtoupper(trim((string) $request->get('letter', '')));
        $nic = trim((string) $request->get('nic', ''));
        $uhid = trim((string) $request->get('uhid', ''));
        $phone = trim((string) $request->get('phone', ''));
        $specimenNo = trim((string) $request->get('specimen_no', ''));
        $test = trim((string) $request->get('test', ''));
        $department = trim((string) $request->get('department', ''));
        $center = trim((string) $request->get('center', ''));
        $sex = trim((string) $request->get('sex', ''));
        $status = trim((string) $request->get('status', ''));
        $from = trim((string) $request->get('from', ''));
        $to = trim((string) $request->get('to', ''));
        $ageMin = trim((string) $request->get('age_min', ''));
        $ageMax = trim((string) $request->get('age_max', ''));
        $sort = trim((string) $request->get('sort', 'date_desc'));

        $query = SpecimenTest::query()
            ->with(['specimen.patient', 'specimen.center', 'testMaster.department']);

        if ($letter !== '' && preg_match('/^[A-Z]$/', $letter)) {
            $query->whereHas('specimen.patient', function ($q) use ($letter) {
                $q->where('name', 'like', $letter . '%');
            });
        }

        if ($nic !== '') {
            $query->whereHas('specimen.patient', function ($q) use ($nic) {
                $q->where('nic', 'like', $nic . '%');
            });
        }

        if ($uhid !== '') {
            $query->whereHas('specimen.patient', function ($q) use ($uhid) {
                $q->where('uhid', 'like', $uhid . '%');
            });
        }

        if ($phone !== '') {
            $query->whereHas('specimen.patient', function ($q) use ($phone) {
                $q->where('phone', 'like', '%' . $phone . '%');
            });
        }

        if ($specimenNo !== '') {
            $query->whereHas('specimen', function ($q) use ($specimenNo) {
                $q->where('specimen_no', 'like', $specimenNo . '%');
            });
        }

        if ($test !== '') {
            $query->whereHas('testMaster', function ($q) use ($test) {
                $q->where('name', 'like', '%' . $test . '%')
                    ->orWhere('code', 'like', $test . '%');
            });
        }

        if ($department !== '') {
            $query->whereHas('testMaster.department', function ($q) use ($department) {
                $q->where('name', 'like', '%' . $department . '%');
            });
        }

        if ($center !== '') {
            $query->whereHas('specimen.center', function ($q) use ($center) {
                $q->where('name', 'like', '%' . $center . '%')
                    ->orWhere('code', 'like', $center . '%');
            });
        }

        if ($sex !== '') {
            $query->whereHas('specimen.patient', function ($q) use ($sex) {
                $q->where('sex', $sex);
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($from !== '' || $to !== '') {
            $fromDate = $from !== '' ? Carbon::parse($from)->startOfDay() : null;
            $toDate = $to !== '' ? Carbon::parse($to)->endOfDay() : null;
            $query->whereHas('specimen', function ($q) use ($fromDate, $toDate) {
                if ($fromDate) {
                    $q->where('created_at', '>=', $fromDate);
                }
                if ($toDate) {
                    $q->where('created_at', '<=', $toDate);
                }
            });
        }

        if ($ageMin !== '' || $ageMax !== '') {
            $today = Carbon::today();
            $min = is_numeric($ageMin) ? (int) $ageMin : null;
            $max = is_numeric($ageMax) ? (int) $ageMax : null;
            $query->whereHas('specimen.patient', function ($q) use ($today, $min, $max) {
                if ($min !== null) {
                    $q->where('dob', '<=', $today->copy()->subYears($min));
                }
                if ($max !== null) {
                    $q->where('dob', '>=', $today->copy()->subYears($max + 1)->addDay());
                }
            });
        }

        $allowedSorts = [
            'date_desc',
            'date_asc',
            'name_asc',
            'name_desc',
            'uhid_asc',
            'uhid_desc',
            'specimen_asc',
            'specimen_desc',
            'status_asc',
            'status_desc',
        ];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'date_desc';
        }

        $needsPatientJoin = in_array($sort, ['name_asc', 'name_desc', 'uhid_asc', 'uhid_desc'], true);
        $needsSpecimenJoin = in_array($sort, ['specimen_asc', 'specimen_desc'], true);

        if ($needsPatientJoin || $needsSpecimenJoin) {
            $query->leftJoin('specimens', 'specimen_tests.specimen_id', '=', 'specimens.id');
        }
        if ($needsPatientJoin) {
            $query->leftJoin('patients', 'specimens.patient_id', '=', 'patients.id');
        }
        if ($needsPatientJoin || $needsSpecimenJoin) {
            $query->select('specimen_tests.*');
        }

        switch ($sort) {
            case 'date_asc':
                $query->orderBy('specimen_tests.created_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('patients.name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('patients.name', 'desc');
                break;
            case 'uhid_asc':
                $query->orderBy('patients.uhid', 'asc');
                break;
            case 'uhid_desc':
                $query->orderBy('patients.uhid', 'desc');
                break;
            case 'specimen_asc':
                $query->orderBy('specimens.specimen_no', 'asc');
                break;
            case 'specimen_desc':
                $query->orderBy('specimens.specimen_no', 'desc');
                break;
            case 'status_asc':
                $query->orderBy('specimen_tests.status', 'asc');
                break;
            case 'status_desc':
                $query->orderBy('specimen_tests.status', 'desc');
                break;
            default:
                $query->orderBy('specimen_tests.created_at', 'desc');
                break;
        }

        $rows = $query->limit(500)->get();

        return view('admin.patient_information', [
            'rows' => $rows,
            'filters' => [
                'letter' => $letter,
                'nic' => $nic,
                'uhid' => $uhid,
                'phone' => $phone,
                'specimen_no' => $specimenNo,
                'test' => $test,
                'department' => $department,
                'center' => $center,
                'sex' => $sex,
                'status' => $status,
                'from' => $from,
                'to' => $to,
                'age_min' => $ageMin,
                'age_max' => $ageMax,
                'sort' => $sort,
            ],
        ]);
    }

    public function edit(Patient $patient): View
    {
        $this->requirePermission('admin.dashboard');

        return view('admin.patient_information_edit', [
            'patient' => $patient,
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $this->requirePermission('admin.dashboard');

        $data = $request->validate([
            'uhid' => ['required', 'string', 'max:255', Rule::unique('patients', 'uhid')->ignore($patient->id)],
            'name' => ['required', 'string', 'max:255'],
            'nic' => ['nullable', 'string', 'max:255'],
            'dob' => ['nullable', 'date'],
            'sex' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
        ]);

        $patient->update($data);

        return redirect()
            ->route('patient.information')
            ->with('status', 'Patient updated.');
    }

    public function destroy(Patient $patient)
    {
        $this->requirePermission('admin.dashboard');

        $patient->delete();

        return redirect()
            ->route('patient.information')
            ->with('status', 'Patient deleted.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $ids = collect($request->input('patient_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return redirect()
                ->route('patient.information')
                ->with('status', 'No patients selected.');
        }

        $patients = Patient::query()->whereIn('id', $ids)->get();
        $count = $patients->count();

        foreach ($patients as $patient) {
            $patient->delete();
        }

        return redirect()
            ->route('patient.information')
            ->with('status', $count . ' patient(s) deleted.');
    }

    public function deleteAll(Request $request): RedirectResponse
    {
        $this->requirePermission('admin.dashboard');

        $confirm = trim((string) $request->input('confirm_text', ''));
        if ($confirm !== 'DELETE') {
            return redirect()
                ->route('patient.information')
                ->with('status', 'Delete all canceled.');
        }

        $total = Patient::count();
        if ($total === 0) {
            return redirect()
                ->route('patient.information')
                ->with('status', 'No patients to delete.');
        }

        Patient::query()->chunkById(200, function ($patients): void {
            foreach ($patients as $patient) {
                $patient->delete();
            }
        });

        return redirect()
            ->route('patient.information')
            ->with('status', $total . ' patient(s) deleted.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\SpecimenTest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ValidationController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('results.validate');

        $sort = request()->query('sort', 'latest_desc');
        $items = SpecimenTest::query()
            ->where('status', 'RESULT_ENTERED')
            ->with(['specimen.patient', 'testMaster', 'result', 'parameterResults'])
            ->orderByDesc('id')
            ->limit(200)
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
            $item->validation_flag = $flagSummary;
        });

        $items = match ($sort) {
            'latest_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->created_at ?? now()),
            'latest_asc' => $items->sortBy(fn ($item) => $item->specimen?->created_at ?? now()),
            'patient_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->patient?->name ?? ''),
            'specimen_asc' => $items->sortBy(fn ($item) => $item->specimen?->specimen_no ?? ''),
            'specimen_desc' => $items->sortByDesc(fn ($item) => $item->specimen?->specimen_no ?? ''),
            'test_asc' => $items->sortBy(fn ($item) => $item->testMaster?->name ?? ''),
            'test_desc' => $items->sortByDesc(fn ($item) => $item->testMaster?->name ?? ''),
            'flag_desc' => $items->sortByDesc(fn ($item) => $severityOrder[$item->validation_flag ?? ''] ?? 0),
            'flag_asc' => $items->sortBy(fn ($item) => $severityOrder[$item->validation_flag ?? ''] ?? 0),
            default => $items->sortBy(fn ($item) => $item->specimen?->patient?->name ?? ''),
        };

        return view('results.validate', [
            'items' => $items,
            'sort' => $sort,
        ]);
    }

    public function action(Request $request): RedirectResponse
    {
        $this->requirePermission('results.validate');

        $data = $request->validate([
            'specimen_test_id' => ['required', 'integer', 'exists:specimen_tests,id'],
            'action' => ['required', 'in:approve,reject'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($data, $userId) {
            $specimenTest = SpecimenTest::query()->findOrFail($data['specimen_test_id']);

            if ($specimenTest->status !== 'RESULT_ENTERED') {
                return;
            }

            Approval::create([
                'specimen_test_id' => $data['specimen_test_id'],
                'approved_by' => $userId,
                'approved_at' => now(),
                'status' => $data['action'] === 'approve' ? 'VALIDATED' : 'REJECTED',
                'comment' => $data['comment'] ?? null,
            ]);

            SpecimenTest::query()
                ->whereKey($data['specimen_test_id'])
                ->update(['status' => $data['action'] === 'approve' ? 'VALIDATED' : 'REJECTED']);
        });

        return redirect()->route('results.validate');
    }
}

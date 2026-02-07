<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\SpecimenTest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    public function index(): View
    {
        $this->requirePermission('results.approve');

        $items = SpecimenTest::query()
            ->where('status', 'VALIDATED')
            ->with(['specimen.patient', 'testMaster', 'result'])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('results.approve', [
            'items' => $items,
        ]);
    }

    public function action(Request $request): RedirectResponse
    {
        $this->requirePermission('results.approve');

        $data = $request->validate([
            'specimen_test_id' => ['required', 'integer', 'exists:specimen_tests,id'],
            'action' => ['required', 'in:approve,reject,approve_print'],
            'comment' => ['nullable', 'string', 'max:500'],
            'print' => ['nullable', 'boolean'],
        ]);

        $userId = auth()->id();

        $specimenTest = null;

        $isApprove = ($data['action'] ?? '') === 'approve' || ($data['action'] ?? '') === 'approve_print';

        DB::transaction(function () use ($data, $userId, &$specimenTest, $isApprove) {
            $specimenTest = SpecimenTest::query()->findOrFail($data['specimen_test_id']);

            if ($specimenTest->status !== 'VALIDATED') {
                return;
            }

            Approval::create([
                'specimen_test_id' => $data['specimen_test_id'],
                'approved_by' => $userId,
                'approved_at' => now(),
                'status' => $isApprove ? 'APPROVED' : 'REJECTED',
                'comment' => $data['comment'] ?? null,
            ]);

            SpecimenTest::query()
                ->whereKey($data['specimen_test_id'])
                ->update(['status' => $isApprove ? 'APPROVED' : 'REJECTED']);
        });

        if ($isApprove && (($data['action'] ?? '') === 'approve_print' || !empty($data['print'])) && $specimenTest) {
            return redirect()->route('reports.show', $specimenTest);
        }

        return redirect()->route('results.approve');
    }
}

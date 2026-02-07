<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('test_masters') || !Schema::hasTable('test_parameters') || !Schema::hasTable('departments')) {
            return;
        }

        $comment = "estimated Glomerular Filtration Rate (eGFR) and staging of Chronic Kidney Disease (CKD)\n\n"
            . "KIDNEY FUNCTION  GFR (ml/min/1.73 m2)  DESCRIPTION\n"
            . "STAGE\n"
            . "1  > 90  NORMAL OR INCREASED GFR\n"
            . "2  60 - 89  NORMAL OR SLIGHTLY DECREASED IN GFR\n"
            . "3A 45 - 59  MILD TO MODERATE DECREASE IN GFR\n"
            . "3B 30 - 44  MODERATE TO SEVERE DECREASE IN GFR\n"
            . "4  15 - 29  SEVERE DECREASE IN GFR\n"
            . "5  < 15 OR ON DIALYSIS  END STAGE KIDNEY FAILURE\n\n"
            . "eGFR is an estimate which is not completely precise for an individual,but reliable enough to provide useful information.\n\n"
            . "When interpreting kidney function stage (stage 1 - 5) should be combined with markers of renal damage such as urine albumin to creatinine ratio (ACR), haematuria and related risk factors (DM/HT) Creatinine assay is traceable to IDMS international reference standards.\n\n"
            . "The revised formula is validated up to 90 ml/min/1.73m2.\n\n"
            . "The calculated eGFR is based on average body build. it is not validated in children(<18Y), pregnancy, acute illness, those on dialysis, vegans and those with extreme body composition.";

        $labIds = [];
        if (Schema::hasTable('labs')) {
            $labIds = DB::table('labs')->pluck('id')->all();
        }
        $targets = array_merge([null], $labIds);

        foreach ($targets as $labId) {
            $deptQuery = DB::table('departments');
            if ($labId === null) {
                $deptQuery->whereNull('lab_id');
            } else {
                $deptQuery->where('lab_id', $labId);
            }
            $departmentId = $deptQuery
                ->whereRaw('lower(name) like ?', ['%biochemistry%'])
                ->value('id');

            if (!$departmentId) {
                $departmentId = $deptQuery->orderBy('id')->value('id');
            }
            if (!$departmentId) {
                continue;
            }

            $testQuery = DB::table('test_masters');
            if ($labId === null) {
                $testQuery->whereNull('lab_id');
            } else {
                $testQuery->where('lab_id', $labId);
            }
            $testId = $testQuery
                ->where(function ($query) {
                    $query->where('code', 'CREGFR')
                        ->orWhere('name', 'CREATININE/ESTIMATED GFR (CKD-EPI)');
                })
                ->value('id');

            $referenceRanges = ['comment' => $comment];

            if ($testId) {
                $existing = $testQuery->where('id', $testId)->value('reference_ranges');
                if (!empty($existing)) {
                    $decoded = json_decode($existing, true);
                    if (is_array($decoded)) {
                        $referenceRanges = array_merge($decoded, ['comment' => $comment]);
                    }
                }
                DB::table('test_masters')->where('id', $testId)->update([
                    'name' => 'CREATININE/ESTIMATED GFR (CKD-EPI)',
                    'department_id' => $departmentId,
                    'sample_type' => 'BLOOD',
                    'is_outsource' => false,
                    'is_active' => true,
                    'is_package' => false,
                    'reference_ranges' => json_encode($referenceRanges),
                    'updated_at' => now(),
                ]);
            } else {
                $testId = DB::table('test_masters')->insertGetId([
                    'code' => 'CREGFR',
                    'name' => 'CREATININE/ESTIMATED GFR (CKD-EPI)',
                    'department_id' => $departmentId,
                    'sample_type' => 'BLOOD',
                    'is_outsource' => false,
                    'is_active' => true,
                    'is_package' => false,
                    'reference_ranges' => json_encode($referenceRanges),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->upsertParameter($testId, $labId, 'CREATININE - SERUM', 'mg/dL', 'Male: 0.9 - 1.3; Female: 0.6 - 1.1', 10);
            $this->upsertParameter($testId, $labId, 'ESTIMATED GFR', 'mL/min/1.73m2', '>= 90', 20);
        }
    }

    private function upsertParameter(int $testId, $labId, string $name, string $unit, string $range, int $sort): void
    {
        $query = DB::table('test_parameters')->where('test_master_id', $testId)->where('name', $name);
        $existingId = $query->value('id');
        $payload = [
            'name' => $name,
            'unit' => $unit,
            'reference_range' => $range,
            'sort_order' => $sort,
            'is_active' => true,
            'is_visible' => true,
            'result_column' => 1,
            'updated_at' => now(),
        ];

        if ($existingId) {
            DB::table('test_parameters')->where('id', $existingId)->update($payload);
        } else {
            $payload['test_master_id'] = $testId;
            $payload['created_at'] = now();
            DB::table('test_parameters')->insert($payload);
        }
    }

    public function down(): void
    {
        // No rollback to avoid removing existing lab-specific data.
    }
};

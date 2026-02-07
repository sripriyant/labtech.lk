<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('test_masters') || !Schema::hasTable('departments')) {
            return;
        }

        $tests = DB::table('test_masters')
            ->leftJoin('departments', 'test_masters.department_id', '=', 'departments.id')
            ->select(
                'test_masters.id',
                'test_masters.name',
                'test_masters.sample_type',
                'test_masters.tube_color',
                'test_masters.container_type',
                'departments.name as department_name'
            )
            ->get();

        foreach ($tests as $test) {
            $tube = trim((string) ($test->tube_color ?? ''));
            $container = trim((string) ($test->container_type ?? ''));
            if ($tube !== '' && $container !== '') {
                continue;
            }

            $department = strtolower(trim((string) ($test->department_name ?? '')));
            $name = strtolower(trim((string) ($test->name ?? '')));
            $sample = strtolower(trim((string) ($test->sample_type ?? '')));

            $updates = [];

            if ($tube === '') {
                if (str_contains($department, 'biochem')) {
                    $updates['tube_color'] = 'Red (Clot Activator)';
                } elseif (str_contains($department, 'haemat') || str_contains($department, 'hemat')) {
                    $updates['tube_color'] = 'Purple (EDTA K2/K3)';
                }
            }

            if ($container === '') {
                if (str_contains($name, 'urine culture') || str_contains($sample, 'urine culture')) {
                    $updates['container_type'] = 'Sterile Container';
                } elseif (str_contains($name, 'urine') || str_contains($sample, 'urine')) {
                    $updates['container_type'] = 'Urine Cup';
                } elseif (str_contains($name, 'stool') || str_contains($sample, 'stool')) {
                    $updates['container_type'] = 'Stool Cup';
                } elseif (str_contains($name, 'sputum') || str_contains($sample, 'sputum')) {
                    $updates['container_type'] = 'Swab';
                }
            }

            if (!empty($updates)) {
                $updates['updated_at'] = now();
                DB::table('test_masters')->where('id', $test->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // No rollback for data backfill.
    }
};

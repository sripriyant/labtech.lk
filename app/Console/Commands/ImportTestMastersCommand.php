<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\TestMaster;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportTestMastersCommand extends Command
{
    protected $signature = 'tests:import {file : Absolute path to the CSV file} {--department=General : Department name to assign} {--lab_id= : Optional lab ID}';

    protected $description = 'Import or update test master records from a CSV price list';

    public function handle(): int
    {
        ini_set('auto_detect_line_endings', '1');
        $filePath = $this->argument('file');
        if (!$filePath || !is_file($filePath)) {
            $this->error("File not found: {$filePath}");
            return self::FAILURE;
        }

        $departmentName = trim($this->option('department') ?: 'General');
        $departmentCode = Str::upper(Str::slug($departmentName, '_') ?: 'GENERAL');
        $department = Department::firstOrCreate(
            ['code' => $departmentCode],
            ['name' => $departmentName, 'is_active' => true]
        );

        $labId = $this->option('lab_id');
        $file = new \SplFileObject($filePath, 'r');
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY);

        $rowNumber = 0;
        $imported = 0;
        $headerDetected = false;
        while (!$file->eof()) {
            $row = $file->fgetcsv();
            $rowNumber++;
            if (!$row || empty(array_filter($row))) {
                continue;
            }
            $code = trim($row[0] ?? '');
            $code = ltrim($code, "\ufeff\xef\xbb\xbf");
            if (!$code) {
                continue;
            }
            if (!$headerDetected && strcasecmp($code, 'TEST_CODE') === 0) {
                $headerDetected = true;
                continue;
            }
            if (!$headerDetected) {
                continue;
            }

            $name = trim($row[1] ?? '');
            $price = $this->toDecimal($row[2] ?? null);
            $tube = trim($row[3] ?? '');
            $tatDays = $this->toInteger($row[4] ?? null);

            if ($name === '') {
                $name = $code;
            }

            $payload = [
                'name' => $name,
                'department_id' => $department->id,
                'tube_color' => $tube !== '' ? $tube : null,
                'container_type' => $tube !== '' ? $tube : null,
                'price' => $price,
                'tat_days' => $tatDays,
                'is_active' => true,
                'is_billing_visible' => true,
                'is_outsource' => false,
            ];
            if ($labId !== null && $labId !== '') {
                $payload['lab_id'] = (int) $labId;
            }

            TestMaster::updateOrCreate(['code' => $code], $payload);
            $imported++;
        }

        $this->info("Imported/updated {$imported} test masters from {$filePath} (department: {$department->name})");
        return self::SUCCESS;
    }

    private function toDecimal($value): float
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return 0.0;
        }
        $normalized = str_replace(',', '', $normalized);
        if (!is_numeric($normalized)) {
            return 0.0;
        }
        return (float) $normalized;
    }

    private function toInteger($value): ?int
    {
        $normalized = trim((string) $value);
        if ($normalized === '') {
            return null;
        }
        if (!is_numeric($normalized)) {
            return null;
        }
        return (int) round((float) $normalized);
    }
}

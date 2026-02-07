<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sqlitePath = __DIR__ . '/../database/database.sqlite';
if (!file_exists($sqlitePath)) {
    echo "SQLite file not found: $sqlitePath\n";
    exit(1);
}

$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$labMap = [];
$selectLabs = $pdo->query('SELECT * FROM labs');
foreach ($selectLabs as $row) {
    $labCode = trim((string) ($row['code_prefix'] ?? ''));
    $existing = DB::table('labs')
        ->where('code_prefix', $labCode === '' ? null : $labCode)
        ->first();

    if ($existing) {
        $labId = $existing->id;
    } else {
        $labId = DB::table('labs')->insertGetId([
            'name' => $row['name'] ?? 'Imported Lab',
            'code_prefix' => $labCode === '' ? null : $labCode,
            'is_active' => (bool) ($row['is_active'] ?? true),
            'sms_enabled' => (bool) ($row['sms_enabled'] ?? true),
            'created_at' => $row['created_at'] ?? now(),
            'updated_at' => $row['updated_at'] ?? now(),
        ]);
    }

    $labMap[(int) ($row['id'] ?? 0)] = $labId;
}

$deptMap = [];
$selectDepartments = $pdo->query('SELECT * FROM departments');
foreach ($selectDepartments as $row) {
    $labId = null;
    if (!empty($row['lab_id']) && isset($labMap[(int) $row['lab_id']])) {
        $labId = $labMap[(int) $row['lab_id']];
    }

    $existing = DB::table('departments')
        ->where('code', $row['code'])
        ->where('lab_id', $labId)
        ->first();

    if ($existing) {
        $deptId = $existing->id;
    } else {
        $deptId = DB::table('departments')->insertGetId([
            'code' => $row['code'] ?? null,
            'name' => $row['name'] ?? 'Imported Department',
            'is_active' => (bool) ($row['is_active'] ?? true),
            'lab_id' => $labId,
            'created_at' => $row['created_at'] ?? now(),
            'updated_at' => $row['updated_at'] ?? now(),
        ]);
    }

    $deptMap[(int) ($row['id'] ?? 0)] = $deptId;
}

$inserted = 0;
$selectTests = $pdo->query('SELECT * FROM test_masters');
foreach ($selectTests as $row) {
    $labId = null;
    if (!empty($row['lab_id']) && isset($labMap[(int) $row['lab_id']])) {
        $labId = $labMap[(int) $row['lab_id']];
    }

    $deptId = $deptMap[(int) ($row['department_id'] ?? 0)] ?? null;
    if (!$deptId) {
        continue;
    }

    $query = DB::table('test_masters')->where('code', $row['code']);
    if ($labId === null) {
        $query->whereNull('lab_id');
    } else {
        $query->where('lab_id', $labId);
    }

    if ($query->exists()) {
        continue;
    }

    DB::table('test_masters')->insert([
        'lab_id' => $labId,
        'code' => $row['code'],
        'name' => $row['name'],
        'department_id' => $deptId,
        'sample_type' => $row['sample_type'],
        'tube_color' => $row['tube_color'],
        'container_type' => $row['container_type'],
        'reference_ranges' => $row['reference_ranges'],
        'panic_values' => $row['panic_values'],
        'price' => $row['price'] ?? 0,
        'tat_days' => $row['tat_days'] !== null ? (int) $row['tat_days'] : null,
        'is_outsource' => (bool) ($row['is_outsource'] ?? false),
        'is_active' => (bool) ($row['is_active'] ?? true),
        'is_billing_visible' => (bool) ($row['is_billing_visible'] ?? true),
        'is_package' => (bool) ($row['is_package'] ?? false),
        'created_at' => $row['created_at'] ?? now(),
        'updated_at' => $row['updated_at'] ?? now(),
    ]);

    $inserted++;
}

echo \"Imported $inserted tests from SQLite to MySQL.\n\";

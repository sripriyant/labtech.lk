<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$sourceDsn = 'sqlite:' . __DIR__ . '/../database/database.sqlite';
if (!file_exists(__DIR__ . '/../database/database.sqlite')) {
    echo "Source SQLite file not found, skipping sync.\n";
    exit(1);
}

$source = new PDO($sourceDsn);
$source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mysql = DB::connection()->getPdo();
$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database = DB::getDatabaseName();

$tables = $source->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
    ->fetchAll(PDO::FETCH_COLUMN);

if (empty($tables)) {
    echo "No tables found in SQLite source.\n";
    exit(0);
}

$getTargetColumns = function (string $table) use ($mysql, $database): array {
    $stmt = $mysql->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?');
    $stmt->execute([$database, $table]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
};

$mysql->exec('SET FOREIGN_KEY_CHECKS=0');
foreach ($tables as $table) {
    $targetColumns = $getTargetColumns($table);
    if (empty($targetColumns)) {
        echo "Skipping {$table}: not present in MySQL schema.\n";
        continue;
    }

    $rows = $source->query("SELECT * FROM \"{$table}\"")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "Skipping {$table}: no rows to sync.\n";
        continue;
    }

    $columns = array_values(array_intersect($targetColumns, array_keys($rows[0])));
    if (empty($columns)) {
        echo "Skipping {$table}: no shared columns found.\n";
        continue;
    }

    $mysql->exec("TRUNCATE TABLE `{$table}`");

    $placeholders = implode(',', array_fill(0, count($columns), '?'));
    $columnList = implode('`,`', $columns);
    $updateList = implode(', ', array_map(fn ($col) => "`{$col}` = VALUES(`{$col}`)", $columns));
    $stmt = $mysql->prepare("INSERT INTO `{$table}` (`{$columnList}`) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$updateList}");
    foreach ($rows as $row) {
        $values = array_map(fn ($col) => $row[$col] ?? null, $columns);
        $stmt->execute($values);
    }
    echo "Synced table {$table} (" . count($rows) . " rows).\n";
}
$mysql->exec('SET FOREIGN_KEY_CHECKS=1');

echo "SQLite → MySQL sync complete.\n";

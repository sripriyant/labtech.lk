<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\TestMaster;
use Illuminate\Database\Seeder;

class SampleTestSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::query()->first();
        if (!$department) {
            $department = Department::create([
                'name' => 'General',
                'is_active' => true,
            ]);
        }

        $tests = [
            ['code' => 'ECG', 'name' => 'ECG', 'price' => 1500],
            ['code' => 'CBC', 'name' => 'Complete Blood Count', 'price' => 1200],
            ['code' => 'FBS', 'name' => 'Fasting Blood Sugar', 'price' => 800],
            ['code' => 'LIPID', 'name' => 'Lipid Profile', 'price' => 2500],
            ['code' => 'LFT', 'name' => 'Liver Function Test', 'price' => 2200],
        ];

        $created = [];
        foreach ($tests as $data) {
            $created[] = TestMaster::query()->firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'department_id' => $department->id,
                    'price' => $data['price'],
                    'is_active' => true,
                    'is_package' => false,
                ]
            );
        }

        $package = TestMaster::query()->firstOrCreate(
            ['code' => 'PKG-BASIC'],
            [
                'name' => 'Basic Health Package',
                'department_id' => $department->id,
                'price' => 0,
                'is_active' => true,
                'is_package' => true,
            ]
        );

        $packageItems = collect($created)
            ->filter(fn ($test) => $test->code !== 'ECG')
            ->map(fn ($test) => $test->id)
            ->values()
            ->all();

        $package->packageItems()->sync($packageItems);
    }
}

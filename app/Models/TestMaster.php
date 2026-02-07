<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Lab;
use App\Models\Traits\BelongsToLab;

class TestMaster extends Model
{
    use BelongsToLab;
    private const CBC_NAMES = [
        'complete blood count',
        'full blood count',
        'cbc',
    ];

    protected $fillable = [
        'code',
        'name',
        'department_id',
        'sample_type',
        'tube_color',
        'container_type',
        'price',
        'tat_days',
        'reference_ranges',
        'panic_values',
        'is_outsource',
        'is_active',
        'is_billing_visible',
        'is_package',
    ];

    protected $casts = [
        'reference_ranges' => 'array',
        'panic_values' => 'array',
        'price' => 'decimal:2',
        'is_outsource' => 'boolean',
        'is_active' => 'boolean',
        'is_billing_visible' => 'boolean',
        'is_package' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class);
    }

    public function parameters()
    {
        return $this->hasMany(TestParameter::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function packageItems()
    {
        return $this->belongsToMany(
            TestMaster::class,
            'test_master_package_items',
            'package_id',
            'test_id'
        );
    }

    public function isCbcTest(): bool
    {
        $name = strtolower(trim((string) $this->name));
        foreach (self::CBC_NAMES as $needle) {
            if ($needle !== '' && str_contains($name, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function ensureCbcParameters(): void
    {
        if (!$this->isCbcTest()) {
            return;
        }

        if ($this->relationLoaded('parameters')) {
            if ($this->parameters->isNotEmpty()) {
                return;
            }
        } elseif ($this->parameters()->exists()) {
            return;
        }

        $this->parameters()->createMany(self::cbcParameterDefaults());
    }

    private static function cbcParameterDefaults(): array
    {
        $rows = [
            [
                'name' => 'HAEMOGLOBIN',
                'unit' => 'g/dL',
                'reference_range' => '13 - 17',
                'sort_order' => 10,
                'result_column' => 1,
            ],
            [
                'name' => 'RBC',
                'unit' => 'x10*6/mm3',
                'reference_range' => '4.5 - 5.5',
                'sort_order' => 20,
                'result_column' => 1,
            ],
            [
                'name' => 'HCT',
                'unit' => '%',
                'reference_range' => '40 - 50',
                'sort_order' => 30,
                'result_column' => 1,
            ],
            [
                'name' => 'MCV',
                'unit' => 'fL',
                'reference_range' => '80 - 96',
                'sort_order' => 40,
                'result_column' => 1,
            ],
            [
                'name' => 'MCH',
                'unit' => 'pg',
                'reference_range' => '27 - 32',
                'sort_order' => 50,
                'result_column' => 1,
            ],
            [
                'name' => 'MCHC',
                'unit' => 'g/dL',
                'reference_range' => '31.5 - 34.5',
                'sort_order' => 60,
                'result_column' => 1,
            ],
            [
                'name' => 'RDW-CV',
                'unit' => '%',
                'reference_range' => '11.6 - 14',
                'sort_order' => 70,
                'result_column' => 1,
            ],
            [
                'name' => 'PLATELET COUNT',
                'unit' => '/mm3',
                'reference_range' => '150000 - 450000',
                'sort_order' => 80,
                'result_column' => 1,
            ],
            [
                'name' => 'MPV',
                'unit' => 'fL',
                'reference_range' => '7.5 - 11.5',
                'sort_order' => 90,
                'result_column' => 1,
            ],
            [
                'name' => 'TOTAL LEUCOCYTE COUNT',
                'unit' => '/mm3',
                'reference_range' => '4000 - 11000',
                'sort_order' => 100,
                'result_column' => 1,
            ],
            [
                'name' => 'NEUTROPHILS',
                'unit' => '%',
                'reference_range' => '',
                'sort_order' => 110,
                'result_column' => 1,
                'group_label' => 'DIFFERENTIAL COUNT(%)',
            ],
            [
                'name' => 'LYMPHOCYTES',
                'unit' => '%',
                'reference_range' => '',
                'sort_order' => 120,
                'result_column' => 1,
            ],
            [
                'name' => 'EOSINOPHILS',
                'unit' => '%',
                'reference_range' => '',
                'sort_order' => 130,
                'result_column' => 1,
            ],
            [
                'name' => 'MONOCYTES',
                'unit' => '%',
                'reference_range' => '',
                'sort_order' => 140,
                'result_column' => 1,
            ],
            [
                'name' => 'BASOPHILS',
                'unit' => '%',
                'reference_range' => '',
                'sort_order' => 150,
                'result_column' => 1,
            ],
        ];

        return array_map(static function (array $row): array {
            $row['is_active'] = true;
            $row['is_visible'] = true;
            return $row;
        }, $rows);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestParameter extends Model
{
    protected $fillable = [
        'test_master_id',
        'name',
        'symbol',
        'unit',
        'reference_range',
        'remarks',
        'sort_order',
        'is_active',
        'is_visible',
        'is_bold',
        'is_underline',
        'is_italic',
        'text_color',
        'result_column',
        'group_label',
        'display_type',
        'font_size',
        'dropdown_options',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'is_visible' => 'boolean',
        'is_bold' => 'boolean',
        'is_underline' => 'boolean',
        'is_italic' => 'boolean',
        'result_column' => 'integer',
        'font_size' => 'integer',
        'dropdown_options' => 'array',
    ];

    public function testMaster()
    {
        return $this->belongsTo(TestMaster::class);
    }
}

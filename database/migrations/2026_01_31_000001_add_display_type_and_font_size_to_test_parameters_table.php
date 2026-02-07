<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            if (!Schema::hasColumn('test_parameters', 'display_type')) {
                $table->string('display_type')->default('textbox')->after('remarks');
            }
            if (!Schema::hasColumn('test_parameters', 'font_size')) {
                $table->unsignedTinyInteger('font_size')->default(14)->after('display_type');
            }
            if (!Schema::hasColumn('test_parameters', 'dropdown_options')) {
                $table->text('dropdown_options')->nullable()->after('font_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            $table->dropColumn(['display_type', 'font_size', 'dropdown_options']);
        });
    }
};

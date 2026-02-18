<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            if (!Schema::hasColumn('test_parameters', 'reference_image_path')) {
                $table->string('reference_image_path')->nullable()->after('reference_range');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            if (Schema::hasColumn('test_parameters', 'reference_image_path')) {
                $table->dropColumn('reference_image_path');
            }
        });
    }
};

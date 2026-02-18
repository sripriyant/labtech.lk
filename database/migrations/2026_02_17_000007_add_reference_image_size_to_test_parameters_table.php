<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            if (!Schema::hasColumn('test_parameters', 'reference_image_width')) {
                $table->unsignedSmallInteger('reference_image_width')->nullable()->after('reference_image_path');
            }
            if (!Schema::hasColumn('test_parameters', 'reference_image_height')) {
                $table->unsignedSmallInteger('reference_image_height')->nullable()->after('reference_image_width');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            if (Schema::hasColumn('test_parameters', 'reference_image_height')) {
                $table->dropColumn('reference_image_height');
            }
            if (Schema::hasColumn('test_parameters', 'reference_image_width')) {
                $table->dropColumn('reference_image_width');
            }
        });
    }
};

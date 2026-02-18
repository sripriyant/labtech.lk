<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_parameter_results', function (Blueprint $table) {
            if (!Schema::hasColumn('test_parameter_results', 'image_path')) {
                $table->string('image_path')->nullable()->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_parameter_results', function (Blueprint $table) {
            if (Schema::hasColumn('test_parameter_results', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};

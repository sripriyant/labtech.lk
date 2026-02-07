<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_masters', function (Blueprint $table) {
            if (!Schema::hasColumn('test_masters', 'price')) {
                $table->decimal('price', 10, 2)->default(0)->after('container_type');
            }
            if (!Schema::hasColumn('test_masters', 'tat_days')) {
                $table->unsignedInteger('tat_days')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('test_masters', function (Blueprint $table) {
            if (Schema::hasColumn('test_masters', 'tat_days')) {
                $table->dropColumn('tat_days');
            }
            if (Schema::hasColumn('test_masters', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};

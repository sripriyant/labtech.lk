<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('specimens', function (Blueprint $table) {
            if (!Schema::hasColumn('specimens', 'age_years')) {
                $table->unsignedSmallInteger('age_years')->nullable()->after('patient_id');
            }
            if (!Schema::hasColumn('specimens', 'age_months')) {
                $table->unsignedSmallInteger('age_months')->nullable()->after('age_years');
            }
            if (!Schema::hasColumn('specimens', 'age_days')) {
                $table->unsignedSmallInteger('age_days')->nullable()->after('age_months');
            }
            if (!Schema::hasColumn('specimens', 'age_unit')) {
                $table->string('age_unit', 2)->nullable()->after('age_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('specimens', function (Blueprint $table) {
            if (Schema::hasColumn('specimens', 'age_unit')) {
                $table->dropColumn('age_unit');
            }
            if (Schema::hasColumn('specimens', 'age_days')) {
                $table->dropColumn('age_days');
            }
            if (Schema::hasColumn('specimens', 'age_months')) {
                $table->dropColumn('age_months');
            }
            if (Schema::hasColumn('specimens', 'age_years')) {
                $table->dropColumn('age_years');
            }
        });
    }
};

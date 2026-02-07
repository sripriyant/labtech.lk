<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('departments', 'lab_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
                $table->unique(['lab_id', 'code']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('departments', 'lab_id')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropUnique(['lab_id', 'code']);
                $table->dropForeign(['lab_id']);
                $table->dropColumn('lab_id');
            });
        }
    }
};

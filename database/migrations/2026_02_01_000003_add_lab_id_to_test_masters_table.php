<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('test_masters', 'lab_id')) {
            Schema::table('test_masters', function (Blueprint $table) {
                $table->unsignedBigInteger('lab_id')->nullable()->after('id');
                $table->index('lab_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('test_masters', 'lab_id')) {
            Schema::table('test_masters', function (Blueprint $table) {
                $table->dropIndex(['lab_id']);
                $table->dropColumn('lab_id');
            });
        }
    }
};

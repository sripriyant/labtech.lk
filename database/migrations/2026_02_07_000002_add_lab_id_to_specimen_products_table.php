<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('specimen_products', 'lab_id')) {
            Schema::table('specimen_products', function (Blueprint $table) {
                $table->foreignId('lab_id')->nullable()->constrained('labs')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('specimen_products', 'lab_id')) {
            Schema::table('specimen_products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('lab_id');
            });
        }
    }
};

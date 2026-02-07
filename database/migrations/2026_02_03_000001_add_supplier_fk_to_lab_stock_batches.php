<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lab_stock_batches') && Schema::hasTable('suppliers')) {
            Schema::table('lab_stock_batches', function (Blueprint $table) {
                if (!Schema::hasColumn('lab_stock_batches', 'supplier_id')) {
                    return;
                }

                $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lab_stock_batches')) {
            Schema::table('lab_stock_batches', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_stock_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('lab_stock_batches', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('lab_stock_item_id');
                $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_stock_batches', function (Blueprint $table) {
            if (Schema::hasColumn('lab_stock_batches', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_stock_batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_stock_item_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('remaining_qty')->default(0);
            $table->date('purchase_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->foreign('lab_stock_item_id')->references('id')->on('lab_stock_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_stock_batches');
    }
};

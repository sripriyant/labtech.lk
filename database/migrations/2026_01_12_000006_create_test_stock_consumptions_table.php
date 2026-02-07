<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_stock_consumptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_master_id');
            $table->unsignedBigInteger('lab_stock_item_id');
            $table->decimal('quantity_per_test', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['test_master_id', 'lab_stock_item_id']);
            $table->foreign('test_master_id')->references('id')->on('test_masters')->cascadeOnDelete();
            $table->foreign('lab_stock_item_id')->references('id')->on('lab_stock_items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_stock_consumptions');
    }
};

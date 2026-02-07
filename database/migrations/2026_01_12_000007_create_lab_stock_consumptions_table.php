<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_stock_consumptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_stock_item_id');
            $table->unsignedBigInteger('test_master_id')->nullable();
            $table->unsignedBigInteger('specimen_test_id')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->dateTime('consumed_at')->nullable();
            $table->timestamps();

            $table->foreign('lab_stock_item_id')->references('id')->on('lab_stock_items')->cascadeOnDelete();
            $table->foreign('test_master_id')->references('id')->on('test_masters')->nullOnDelete();
            $table->foreign('specimen_test_id')->references('id')->on('specimen_tests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_stock_consumptions');
    }
};

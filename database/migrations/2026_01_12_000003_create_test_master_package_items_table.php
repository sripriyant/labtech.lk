<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_master_package_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('test_id');
            $table->timestamps();

            $table->unique(['package_id', 'test_id']);
            $table->foreign('package_id')->references('id')->on('test_masters')->cascadeOnDelete();
            $table->foreign('test_id')->references('id')->on('test_masters')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_master_package_items');
    }
};

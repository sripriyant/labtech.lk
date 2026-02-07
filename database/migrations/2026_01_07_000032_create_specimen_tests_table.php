<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specimen_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specimen_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_master_id')->constrained('test_masters')->cascadeOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('status')->default('ORDERED');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specimen_tests');
    }
};

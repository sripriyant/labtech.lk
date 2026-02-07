<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_masters', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('sample_type')->nullable();
            $table->string('tube_color')->nullable();
            $table->string('container_type')->nullable();
            $table->json('reference_ranges')->nullable();
            $table->json('panic_values')->nullable();
            $table->boolean('is_outsource')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_masters');
    }
};

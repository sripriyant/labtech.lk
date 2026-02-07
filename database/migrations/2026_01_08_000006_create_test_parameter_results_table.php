<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_parameter_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specimen_test_id')->constrained('specimen_tests')->cascadeOnDelete();
            $table->foreignId('test_parameter_id')->constrained('test_parameters')->cascadeOnDelete();
            $table->string('result_value')->nullable();
            $table->string('unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->string('flag')->nullable();
            $table->string('remarks')->nullable();
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('entered_at')->nullable();
            $table->timestamps();
            $table->unique(['specimen_test_id', 'test_parameter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_parameter_results');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specimen_test_id')->constrained('specimen_tests')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('status')->default('APPROVED');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};

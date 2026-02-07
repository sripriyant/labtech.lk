<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code_prefix', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('created_by')->constrained('labs')->nullOnDelete();
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
            $table->unique(['lab_id', 'key']);
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('test_masters', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('specimens', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('specimen_tests', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('test_parameter_results', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('approvals', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('sample_movements', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('lab_stock_items', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('lab_stock_batches', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('lab_stock_consumptions', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });

        Schema::table('test_stock_consumptions', function (Blueprint $table) {
            $table->foreignId('lab_id')->nullable()->after('id')->constrained('labs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_stock_consumptions', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('lab_stock_consumptions', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('lab_stock_batches', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('lab_stock_items', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('sample_movements', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('approvals', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('test_parameter_results', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('test_results', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('specimen_tests', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('specimens', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('test_masters', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['lab_id', 'key']);
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
            $table->unique('key');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropColumn('lab_id');
        });

        Schema::dropIfExists('labs');
    }
};

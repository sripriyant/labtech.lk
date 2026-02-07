<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('referral_type')->nullable()->after('center_id');
            $table->unsignedBigInteger('referral_id')->nullable()->after('referral_type');
            $table->decimal('referral_discount', 10, 2)->default(0)->after('discount');
        });

        Schema::table('specimens', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete()->after('center_id');
        });
    }

    public function down(): void
    {
        Schema::table('specimens', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['referral_type', 'referral_id', 'referral_discount']);
        });
    }
};

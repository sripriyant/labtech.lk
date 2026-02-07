<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_mode', 20)->nullable()->after('promo_discount');
            $table->string('card_transaction_id', 100)->nullable()->after('payment_mode');
            $table->string('bank_slip_no', 100)->nullable()->after('card_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'card_transaction_id', 'bank_slip_no']);
        });
    }
};

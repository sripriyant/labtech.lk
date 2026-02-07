<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->decimal('referral_discount_pct', 5, 2)->default(0)->after('specialty');
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->decimal('referral_discount_pct', 5, 2)->default(0)->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn('referral_discount_pct');
        });

        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn('referral_discount_pct');
        });
    }
};

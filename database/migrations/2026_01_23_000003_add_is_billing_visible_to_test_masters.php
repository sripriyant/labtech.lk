<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('test_masters')) {
            return;
        }

        Schema::table('test_masters', function (Blueprint $table) {
            if (!Schema::hasColumn('test_masters', 'is_billing_visible')) {
                $table->boolean('is_billing_visible')->default(true)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('test_masters')) {
            return;
        }

        Schema::table('test_masters', function (Blueprint $table) {
            if (Schema::hasColumn('test_masters', 'is_billing_visible')) {
                $table->dropColumn('is_billing_visible');
            }
        });
    }
};

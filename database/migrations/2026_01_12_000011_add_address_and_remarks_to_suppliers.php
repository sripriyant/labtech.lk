<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'address')) {
                $table->string('address')->nullable()->after('contact_name');
            }
            if (!Schema::hasColumn('suppliers', 'remarks')) {
                $table->text('remarks')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'address')) {
                $table->dropColumn('address');
            }
            if (Schema::hasColumn('suppliers', 'remarks')) {
                $table->dropColumn('remarks');
            }
        });
    }
};

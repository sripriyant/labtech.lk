<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('settings', 'lab_id')) {
            try {
                Schema::table('settings', function (Blueprint $table) {
                    $table->dropUnique('settings_key_unique');
                });
            } catch (\Throwable $e) {
                // ignore if the index does not exist yet
            }

            Schema::table('settings', function (Blueprint $table) {
                $table->unsignedBigInteger('lab_id')->nullable()->after('value');
            });

            Schema::table('settings', function (Blueprint $table) {
                $table->unique(['lab_id', 'key']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'lab_id')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropUnique(['lab_id', 'key']);
            });

            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('lab_id');
            });

            Schema::table('settings', function (Blueprint $table) {
                $table->unique('key');
            });
        }
    }
};

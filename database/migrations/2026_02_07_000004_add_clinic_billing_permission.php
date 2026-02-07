<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $exists = DB::table('permissions')->where('name', 'clinic.billing')->exists();
        if (!$exists) {
            DB::table('permissions')->insert([
                'name' => 'clinic.billing',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        DB::table('permissions')->where('name', 'clinic.billing')->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            $table->boolean('is_visible')->default(true)->after('is_active');
            $table->boolean('is_bold')->default(false)->after('is_visible');
            $table->boolean('is_underline')->default(false)->after('is_bold');
            $table->string('text_color', 20)->nullable()->after('is_underline');
            $table->unsignedTinyInteger('result_column')->default(1)->after('text_color');
            $table->string('group_label')->nullable()->after('result_column');
        });
    }

    public function down(): void
    {
        Schema::table('test_parameters', function (Blueprint $table) {
            $table->dropColumn([
                'is_visible',
                'is_bold',
                'is_underline',
                'text_color',
                'result_column',
                'group_label',
            ]);
        });
    }
};

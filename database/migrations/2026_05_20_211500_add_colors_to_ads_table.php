<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->string('bg_color_from', 20)->default('#001D4A')->after('sort_order');
            $table->string('bg_color_to', 20)->default('#0052CC')->after('bg_color_from');
        });
    }

    public function down(): void
    {
        Schema::table('ads', function (Blueprint $table) {
            $table->dropColumn(['bg_color_from', 'bg_color_to']);
        });
    }
};

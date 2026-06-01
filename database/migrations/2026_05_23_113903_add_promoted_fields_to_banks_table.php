<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            if (!Schema::hasColumn('banks', 'is_promoted')) {
                $table->boolean('is_promoted')->default(false)->after('syarat');
            }
            if (!Schema::hasColumn('banks', 'promo_image')) {
                $table->string('promo_image')->nullable()->after('is_promoted');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            if (Schema::hasColumn('banks', 'is_promoted')) {
                $table->dropColumn('is_promoted');
            }
            if (Schema::hasColumn('banks', 'promo_image')) {
                $table->dropColumn('promo_image');
            }
        });
    }
};

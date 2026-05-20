<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            if (!Schema::hasColumn('banks', 'category')) {
                $table->string('category', 80)->default('terdaftar')->after('nama_bank');
            }
        });

        DB::table('banks')
            ->whereNull('category')
            ->orWhere('category', '')
            ->update(['category' => 'terdaftar']);
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            if (Schema::hasColumn('banks', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->unsignedBigInteger('plafon_min')->default(1000000)->after('deskripsi');
            $table->unsignedBigInteger('plafon_max')->default(50000000)->after('plafon_min');
            $table->integer('tenor_min')->default(6)->after('plafon_max');
            $table->integer('tenor_max')->default(36)->after('tenor_min');
            $table->decimal('bunga_persen', 5, 2)->default(0.5)->after('tenor_max');
            $table->json('syarat')->nullable()->after('bunga_persen');
        });
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['plafon_min', 'plafon_max', 'tenor_min', 'tenor_max', 'bunga_persen', 'syarat']);
        });
    }
};

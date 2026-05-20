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
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->string('nama_usaha')->nullable()->after('user_id');
            $table->string('bidang_usaha')->nullable()->after('nama_usaha');
            $table->text('alamat_usaha')->nullable()->after('bidang_usaha');
            $table->string('lama_usaha')->nullable()->after('alamat_usaha');
            $table->string('jumlah_karyawan')->nullable()->after('lama_usaha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn(['nama_usaha', 'bidang_usaha', 'alamat_usaha', 'lama_usaha', 'jumlah_karyawan']);
        });
    }
};

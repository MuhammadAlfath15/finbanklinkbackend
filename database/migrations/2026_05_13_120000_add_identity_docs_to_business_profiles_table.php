<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->string('ktp_path')->nullable()->after('npwp_path');
            $table->string('kk_path')->nullable()->after('ktp_path');
            $table->string('selfie_ktp_path')->nullable()->after('kk_path');
            $table->string('ttd_path')->nullable()->after('selfie_ktp_path');
        });
    }

    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn(['ktp_path', 'kk_path', 'selfie_ktp_path', 'ttd_path']);
        });
    }
};

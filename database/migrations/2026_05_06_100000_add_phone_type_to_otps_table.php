<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            // Untuk OTP pinjaman: identifier pakai nomor HP
            $table->string('phone')->nullable()->after('email');
            // 'forgot_password' | 'loan_application'
            $table->string('type')->default('forgot_password')->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            $table->dropColumn(['phone', 'type']);
        });
    }
};

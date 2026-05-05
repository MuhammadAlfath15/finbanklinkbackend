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
        Schema::create('submissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('bank_id')->constrained();
    $table->string('status')->default('sedang diverifikasi');
    $table->string('nominal_pinjaman');
    $table->string('tenor');
    $table->string('ktp_nik')->nullable(); // Untuk hasil OCR nanti
    $table->string('ktp_nama')->nullable(); // Untuk hasil OCR nanti
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};

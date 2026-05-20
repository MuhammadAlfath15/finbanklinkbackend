<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // === LEGALITAS ===
            $table->string('nib_path')->nullable();       // path file NIB
            $table->string('npwp_path')->nullable();      // path file NPWP

            // === KEUANGAN / PROFITABILITAS ===
            $table->string('rekening_path')->nullable();  // rekening koran 3 bln
            $table->decimal('omzet_bulan_ini', 18, 2)->nullable(); // input nominal omzet

            // === OPERASIONAL / KEBERLANJUTAN ===
            $table->string('foto_usaha_path')->nullable(); // foto tempat usaha
            $table->string('kontrak_path')->nullable();    // kontrak sewa/kepemilikan

            // === KAPASITAS UTANG & KOLEKTIBILITAS ===
            $table->decimal('cicilan_berjalan', 18, 2)->nullable(); // total cicilan lain
            $table->string('bukti_pelunasan_path')->nullable();     // bukti lunasi utang

            // === SKOR HASIL KALKULASI (disimpan ke DB agar bisa di-query) ===
            $table->unsignedTinyInteger('skor_profitabilitas')->default(0);
            $table->unsignedTinyInteger('skor_legalitas')->default(0);
            $table->unsignedTinyInteger('skor_tren_omzet')->default(0);
            $table->unsignedTinyInteger('skor_kolektibilitas')->default(0);
            $table->unsignedTinyInteger('skor_keberlanjutan')->default(0);
            $table->unsignedTinyInteger('skor_kapasitas_utang')->default(0);
            $table->unsignedSmallInteger('skor_total')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};

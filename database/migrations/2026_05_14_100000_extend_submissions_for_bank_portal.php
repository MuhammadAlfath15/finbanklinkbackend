<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'reference_code')) {
                $table->string('reference_code', 32)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('submissions', 'nama_usaha')) {
                $table->string('nama_usaha')->nullable()->after('tenor');
            }
            if (!Schema::hasColumn('submissions', 'bidang_usaha')) {
                $table->string('bidang_usaha')->nullable()->after('nama_usaha');
            }
            if (!Schema::hasColumn('submissions', 'alamat_usaha')) {
                $table->string('alamat_usaha', 512)->nullable()->after('bidang_usaha');
            }
            if (!Schema::hasColumn('submissions', 'pemohon_phone')) {
                $table->string('pemohon_phone', 32)->nullable()->after('alamat_usaha');
            }
            if (!Schema::hasColumn('submissions', 'pemohon_alamat')) {
                $table->text('pemohon_alamat')->nullable()->after('pemohon_phone');
            }
            if (!Schema::hasColumn('submissions', 'cicilan_per_bulan')) {
                $table->decimal('cicilan_per_bulan', 18, 2)->nullable()->after('pemohon_alamat');
            }
            if (!Schema::hasColumn('submissions', 'nama_produk')) {
                $table->string('nama_produk')->nullable()->after('bank_id');
            }
            if (!Schema::hasColumn('submissions', 'ktp_upload_path')) {
                $table->string('ktp_upload_path')->nullable()->after('ktp_nama');
            }
            if (!Schema::hasColumn('submissions', 'nib_upload_path')) {
                $table->string('nib_upload_path')->nullable()->after('ktp_upload_path');
            }
            if (!Schema::hasColumn('submissions', 'skor_total')) {
                $table->unsignedSmallInteger('skor_total')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'skor_profitabilitas')) {
                $table->unsignedTinyInteger('skor_profitabilitas')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'skor_legalitas')) {
                $table->unsignedTinyInteger('skor_legalitas')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'skor_tren_omzet')) {
                $table->unsignedTinyInteger('skor_tren_omzet')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'skor_kolektibilitas')) {
                $table->unsignedTinyInteger('skor_kolektibilitas')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'skor_keberlanjutan')) {
                $table->unsignedTinyInteger('skor_keberlanjutan')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'skor_kapasitas_utang')) {
                $table->unsignedTinyInteger('skor_kapasitas_utang')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'omzet_year')) {
                $table->unsignedSmallInteger('omzet_year')->nullable();
            }
            if (!Schema::hasColumn('submissions', 'omzet_data')) {
                $table->json('omzet_data')->nullable();
            }
        });

        // Normalisasi status lama ke format portal bank
        if (Schema::hasColumn('submissions', 'status')) {
            \Illuminate\Support\Facades\DB::table('submissions')
                ->where(function ($q) {
                    $q->where('status', 'sedang diverifikasi')
                        ->orWhere('status', '')
                        ->orWhereNull('status');
                })
                ->update(['status' => 'menunggu']);
        }
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $cols = [
                'reference_code', 'nama_usaha', 'bidang_usaha', 'alamat_usaha',
                'pemohon_phone', 'pemohon_alamat', 'cicilan_per_bulan', 'nama_produk',
                'ktp_upload_path', 'nib_upload_path',
                'skor_total', 'skor_profitabilitas', 'skor_legalitas', 'skor_tren_omzet',
                'skor_kolektibilitas', 'skor_keberlanjutan', 'skor_kapasitas_utang',
                'omzet_year', 'omzet_data',
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('submissions', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};

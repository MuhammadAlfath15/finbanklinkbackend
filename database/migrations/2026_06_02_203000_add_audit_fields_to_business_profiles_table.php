<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->json('document_statuses')->nullable()->after('bukti_pelunasan_path');
            $table->json('document_feedbacks')->nullable()->after('document_statuses');
        });
    }

    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn(['document_statuses', 'document_feedbacks']);
        });
    }
};

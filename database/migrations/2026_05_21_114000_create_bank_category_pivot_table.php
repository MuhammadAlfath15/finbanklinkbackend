<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create the bank_category_pivot table
        if (!Schema::hasTable('bank_category_pivot')) {
            Schema::create('bank_category_pivot', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bank_id')->constrained('banks')->onDelete('cascade');
                $table->foreignId('category_id')->constrained('bank_categories')->onDelete('cascade');
                $table->timestamps();

                $table->unique(['bank_id', 'category_id']);
            });
        }

        // 2. Preserve existing single-category relationships by migrating them
        $banks = DB::table('banks')
            ->select('id', 'category_id')
            ->whereNotNull('category_id')
            ->get();

        foreach ($banks as $bank) {
            // Check if the record already exists in the pivot table (to avoid duplicate errors)
            $exists = DB::table('bank_category_pivot')
                ->where('bank_id', $bank->id)
                ->where('category_id', $bank->category_id)
                ->exists();

            if (!$exists) {
                DB::table('bank_category_pivot')->insert([
                    'bank_id' => $bank->id,
                    'category_id' => $bank->category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_category_pivot');
    }
};

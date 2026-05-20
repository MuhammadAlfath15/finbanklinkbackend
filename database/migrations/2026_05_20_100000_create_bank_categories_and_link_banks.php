<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bank_categories')) {
            Schema::create('bank_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80)->unique();
                $table->string('slug', 100)->unique();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        Schema::table('banks', function (Blueprint $table) {
            if (!Schema::hasColumn('banks', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('category')->constrained('bank_categories')->nullOnDelete();
            }
        });

        $rawNames = DB::table('banks')
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->pluck('category')
            ->map(fn($v) => trim(strtolower($v)))
            ->filter()
            ->values()
            ->all();

        if (!in_array('terdaftar', $rawNames, true)) {
            $rawNames[] = 'terdaftar';
        }

        foreach ($rawNames as $idx => $name) {
            $slug = Str::slug($name);
            if (empty($slug)) {
                $slug = "kategori-{$idx}";
            }

            $existing = DB::table('bank_categories')->where('slug', $slug)->first();
            if (!$existing) {
                DB::table('bank_categories')->insert([
                    'name' => $name,
                    'slug' => $slug,
                    'sort_order' => $idx,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $categories = DB::table('bank_categories')->get()->keyBy(fn($row) => strtolower($row->name));
        $defaultCategoryId = $categories['terdaftar']->id ?? null;

        $banks = DB::table('banks')->select('id', 'category')->get();
        foreach ($banks as $bank) {
            $key = strtolower(trim((string) $bank->category));
            $categoryId = $categories[$key]->id ?? $defaultCategoryId;
            DB::table('banks')->where('id', $bank->id)->update(['category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            if (Schema::hasColumn('banks', 'category_id')) {
                $table->dropConstrainedForeignId('category_id');
            }
        });

        Schema::dropIfExists('bank_categories');
    }
};

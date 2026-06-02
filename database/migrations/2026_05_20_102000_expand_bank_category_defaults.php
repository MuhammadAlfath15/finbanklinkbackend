<?php

use App\Models\Bank;
use App\Models\BankCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            ['name' => 'rekomendasi untukmu', 'slug' => 'rekomendasi-untukmu', 'sort_order' => 1],
            ['name' => 'bank bumn', 'slug' => 'bank-bumn', 'sort_order' => 2],
            ['name' => 'bank syariah', 'slug' => 'bank-syariah', 'sort_order' => 3],
            ['name' => 'bank swasta', 'slug' => 'bank-swasta', 'sort_order' => 4],
            ['name' => 'bank daerah', 'slug' => 'bank-daerah', 'sort_order' => 5],
            ['name' => 'bank digital', 'slug' => 'bank-digital', 'sort_order' => 6],
            ['name' => 'terdaftar', 'slug' => 'terdaftar', 'sort_order' => 99],
        ];

        foreach ($defaults as $item) {
            BankCategory::updateOrCreate(
                ['slug' => $item['slug']],
                ['name' => $item['name'], 'sort_order' => $item['sort_order']]
            );
        }

        $categoryBySlug = BankCategory::query()->get()->keyBy('slug');

        $groups = [
            'rekomendasi-untukmu' => ['bank bca', 'bank mandiri', 'bank bri', 'bank bni', 'bank bsi'],
            'bank-bumn' => ['bank mandiri', 'bank bri', 'bank bni', 'bank btn'],
            'bank-syariah' => ['bank bsi', 'bank muamalat', 'bank mega syariah', 'bca syariah', 'btpn syariah'],
            'bank-swasta' => ['bank bca', 'bank cimb niaga', 'bank danamon', 'bank mega', 'bank ocbc nisp', 'bank panin'],
        ];

        $recommended = $categoryBySlug['rekomendasi-untukmu'] ?? null;
        $bumn = $categoryBySlug['bank-bumn'] ?? null;
        $syariah = $categoryBySlug['bank-syariah'] ?? null;
        $swasta = $categoryBySlug['bank-swasta'] ?? null;
        $fallback = $categoryBySlug['terdaftar'] ?? null;

        foreach (Bank::all() as $bank) {
            $name = strtolower(trim($bank->nama_bank));
            $target = $fallback;

            if (in_array($name, $groups['bank-syariah'], true)) {
                $target = $syariah;
            } elseif (in_array($name, $groups['bank-swasta'], true)) {
                $target = $swasta;
            } elseif (in_array($name, $groups['bank-bumn'], true)) {
                $target = $bumn;
            }

            if (in_array($name, $groups['rekomendasi-untukmu'], true) && $recommended) {
                $target = $recommended;
            }

            if ($target) {
                $bank->category_id = $target->id;
                $bank->category = $target->name;
                $bank->save();
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};

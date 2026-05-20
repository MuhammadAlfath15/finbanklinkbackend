<?php

use App\Models\Bank;
use App\Models\BankCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $registered = BankCategory::firstOrCreate(
            ['name' => 'terdaftar'],
            ['slug' => 'terdaftar', 'sort_order' => 0]
        );

        $recommended = BankCategory::firstOrCreate(
            ['name' => 'rekomendasi untukmu'],
            ['slug' => 'rekomendasi-untukmu', 'sort_order' => 1]
        );

        $privateBanks = BankCategory::firstOrCreate(
            ['name' => 'bank swasta'],
            ['slug' => 'bank-swasta', 'sort_order' => 2]
        );

        $syariah = BankCategory::firstOrCreate(
            ['name' => 'bank syariah'],
            ['slug' => 'bank-syariah', 'sort_order' => 3]
        );

        $groups = [
            'rekomendasi untukmu' => ['bank bca', 'bank mandiri', 'bank bri', 'bank bni', 'bank btn'],
            'bank swasta' => ['bank cimb niaga', 'bank danamon', 'bank mega', 'bank ocbc nisp', 'bank panin'],
            'bank syariah' => ['bank bsi', 'bank muamalat', 'bank mega syariah', 'bca syariah', 'btpn syariah'],
        ];

        $categoryMap = [
            'rekomendasi untukmu' => $recommended,
            'bank swasta' => $privateBanks,
            'bank syariah' => $syariah,
        ];

        foreach (Bank::all() as $bank) {
            $name = strtolower(trim($bank->nama_bank));
            $selected = $registered;
            foreach ($groups as $key => $names) {
                if (in_array($name, $names, true)) {
                    $selected = $categoryMap[$key];
                    break;
                }
            }

            if (!$bank->category_id) {
                $bank->category_id = $selected->id;
                $bank->category = $selected->name;
                $bank->save();
            }
        }
    }

    public function down(): void
    {
        // no-op
    }
};

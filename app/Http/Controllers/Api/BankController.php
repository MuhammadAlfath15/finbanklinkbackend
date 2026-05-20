<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {
        $banks = Bank::query()
            ->with('categoryRef:id,name,slug,sort_order')
            ->orderBy('category_id')
            ->orderByDesc('id')
            ->get()
            ->map(function (Bank $bank) {
                $arr = $bank->toArray();
                $arr['category_name'] = $bank->categoryRef?->name ?? $bank->category ?? 'terdaftar';
                $arr['category_slug'] = $bank->categoryRef?->slug ?? null;
                $arr['category_sort_order'] = $bank->categoryRef?->sort_order ?? 999;
                return $arr;
            });

        return response()->json($banks);
    }
}
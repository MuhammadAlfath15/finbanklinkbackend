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
            ->with('categories')
            ->orderByDesc('id')
            ->get();

        $flattened = [];

        foreach ($banks as $bank) {
            $arr = $bank->toArray();
            
            if ($bank->categories->isEmpty()) {
                $arr['category_id'] = $bank->category_id;
                $arr['category_name'] = $bank->category ?? 'terdaftar';
                $arr['category_slug'] = 'terdaftar';
                $arr['category_sort_order'] = 999;
                $arr['category_ids'] = [];
                $arr['categories'] = [];
                $flattened[] = $arr;
            } else {
                $categoryIds = $bank->categories->pluck('id')->toArray();
                foreach ($bank->categories as $category) {
                    $item = $arr;
                    $item['category_id'] = $category->id;
                    $item['category_name'] = $category->name;
                    $item['category_slug'] = $category->slug;
                    $item['category_sort_order'] = $category->sort_order;
                    $item['category_ids'] = $categoryIds;
                    $item['categories'] = $bank->categories->map(function ($cat) {
                        return [
                            'id' => $cat->id,
                            'name' => $cat->name,
                            'slug' => $cat->slug,
                        ];
                    })->toArray();
                    $flattened[] = $item;
                }
            }
        }

        usort($flattened, function ($a, $b) {
            if ($a['category_sort_order'] === $b['category_sort_order']) {
                return $b['id'] <=> $a['id'];
            }
            return $a['category_sort_order'] <=> $b['category_sort_order'];
        });

        return response()->json($flattened);
    }
}
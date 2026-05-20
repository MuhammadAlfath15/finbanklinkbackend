<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Omzet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OmzetController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Get omzet for the current year, or a specific year if provided
        $year = $request->input('year', date('Y'));
        
        $data = array_fill(0, 12, 0);
        $hasData = false;

        try {
            $omzets = Omzet::where('user_id', $user->id)
                ->where('year', $year)
                ->orderBy('month')
                ->get();

            if ($omzets->count() > 0) {
                $hasData = true;
                foreach ($omzets as $omzet) {
                    // month is 1-12, array index is 0-11
                    if ($omzet->month >= 1 && $omzet->month <= 12) {
                        $data[$omzet->month - 1] = $omzet->amount;
                    }
                }
            }
        } catch (\Exception $e) {
            // Table might not exist or other DB error
            // Fallback to dummy data below
        }

        if (!$hasData) {
            // Return array of zeros so the chart is empty for new users
            $data = array_fill(0, 12, 0);
        }

        return response()->json([
            'year' => $year,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'year' => 'required|integer',
            'data' => 'required|array',
            'data.*' => 'numeric|min:0'
        ]);

        $year = $request->input('year');
        $data = $request->input('data');

        try {
            // Kita save data per bulan
            foreach ($data as $index => $amount) {
                $month = $index + 1;
                
                Omzet::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'month' => $month,
                        'year' => $year,
                    ],
                    [
                        'amount' => $amount
                    ]
                );
            }
            return response()->json(['message' => 'Data omzet berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }
    }
}

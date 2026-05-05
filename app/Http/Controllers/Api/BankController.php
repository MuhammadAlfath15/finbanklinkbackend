<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {
        // Mengambil semua data bank dari database
        $banks = Bank::all();
        
        // Mengirim data tersebut ke React dalam format JSON
        return response()->json($banks);
    }
}
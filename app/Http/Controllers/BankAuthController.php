<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BankAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $bankUser = BankUser::where('email', $request->email)->first();
        if (!$bankUser || !Hash::check($request->password, $bankUser->password)) {
            return response()->json(['message' => 'Email atau password bank salah.'], 401);
        }

        $token = $bankUser->createToken('bank_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login bank berhasil',
            'token' => $token,
            'role' => 'bank',
            'user' => $bankUser,
        ]);
    }
}

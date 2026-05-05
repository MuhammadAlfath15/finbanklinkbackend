<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth; // Wajib ada
use Illuminate\Support\Facades\Validator; // Wajib ada
use Illuminate\Support\Str;
use App\Models\Otp;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json(['message' => 'Berhasil'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

   public function login(Request $request)
{
    // 1. Validasi input
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $credentials = $request->only('email', 'password');

    // 2. Coba login (Auth::attempt otomatis nge-hash input password dan mencocokkan)
    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        
        // Ganti baris 55 sampai 59 di screenshot lu dengan ini:
$token = $user->createToken('auth_token')->plainTextToken; // Tambahkan ini buat generate token

return response()->json([
    'success' => true,
    'message' => 'Login berhasil',
    'token'   => $token,      // Harus dikirim ke React
    'role'    => $user->role, // Harus dikirim biar React bisa seleksi dashboard
    'user'    => $user
], 200);
    }

    // 3. Jika gagal
    return response()->json([
        'message' => 'Email atau Password salah!'
    ], 401);
}

public function forgotPassword(Request $request)
{
    // STEP 1: KIRIM OTP
    if (!$request->has('step') || $request->step == 1) {
        $request->validate(['email' => 'required|email|exists:users,email']);
        $otp = rand(100000, 999999);
        
        // Simpan ke tabel otps (update jika sudah ada)
        \DB::table('otps')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'expires_at' => now()->addMinutes(10)]
        );

        Mail::raw("Kode OTP kamu adalah: $otp", function ($message) use ($request) {
    $message->to($request->email) // <--- Ini yang bikin emailnya dinamis ke siapa aja
            ->subject('Kode OTP Reset Password');
});

        return response()->json(['status' => 'success', 'message' => 'OTP terkirim']);
    }

    // STEP 2: VERIFIKASI OTP
    if ($request->step == 2) {
        $check = \DB::table('otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$check) return response()->json(['message' => 'OTP Salah atau Kadaluarsa'], 400);
        return response()->json(['status' => 'success', 'message' => 'OTP Valid']);
    }

    // STEP 3: GANTI PASSWORD
    if ($request->step == 3) {
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus OTP setelah dipakai
        \DB::table('otps')->where('email', $request->email)->delete();

        return response()->json(['status' => 'success', 'message' => 'Password Berhasil Diubah']);
    }
}

}
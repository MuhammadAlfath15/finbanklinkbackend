<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    protected FonnteService $fonnte;

    public function __construct(FonnteService $fonnte)
    {
        $this->fonnte = $fonnte;
    }

    /**
     * Generate & kirim OTP pengajuan pinjaman ke WhatsApp user
     *
     * POST /api/otp/send-loan
     * Middleware: auth:sanctum
     */
    public function sendLoanOtp(Request $request)
    {
        $user = Auth::user();

        Log::info('OtpController@sendLoanOtp: dipanggil', [
            'user_id' => $user->id,
            'phone'   => $user->phone ?? 'NULL',
        ]);

        // ── Validasi nomor HP ──────────────────────────────────────────
        if (empty($user->phone)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Nomor HP belum diisi di profil kamu. Mohon lengkapi profil terlebih dahulu.',
            ], 422);
        }

        // ── Generate 6-digit OTP ───────────────────────────────────────
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // ── Simpan ke DB ───────────────────────────────────────────────
        try {
            DB::table('otps')->updateOrInsert(
                [
                    'phone' => $user->phone,
                    'type'  => 'loan_application',
                ],
                [
                    'email'      => $user->email,
                    'otp'        => $otp,
                    'expires_at' => now()->addMinutes(5),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('OtpController@sendLoanOtp: DB error', ['error' => $e->getMessage()]);

            // Kemungkinan besar migration belum dijalankan
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan database. Pastikan sudah menjalankan `php artisan migrate`.',
                'detail'  => $e->getMessage(),
            ], 500);
        }

        // ── Kirim via Fonnte ───────────────────────────────────────────
        $message = "🔐 *FinBankLink*\n\nKode OTP pengajuan pinjaman kamu adalah:\n\n*{$otp}*\n\nKode berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun.";

        $result = $this->fonnte->send($user->phone, $message);

        if (!$result['ok']) {
            Log::error('OtpController@sendLoanOtp: Fonnte gagal', ['reason' => $result['reason']]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim OTP via WhatsApp.',
                'detail'  => $result['reason'],   // tampilkan ke frontend supaya jelas
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'OTP berhasil dikirim ke WhatsApp kamu.',
            'phone'   => $this->maskPhone($user->phone),
        ]);
    }

    /**
     * Verifikasi OTP pengajuan pinjaman
     *
     * POST /api/otp/verify-loan
     * Middleware: auth:sanctum
     */
    public function verifyLoanOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        $record = DB::table('otps')
            ->where('phone', $user->phone)
            ->where('type', 'loan_application')
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kode OTP salah atau sudah kadaluarsa.',
            ], 400);
        }

        DB::table('otps')
            ->where('phone', $user->phone)
            ->where('type', 'loan_application')
            ->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'OTP berhasil diverifikasi. Pengajuan pinjaman kamu sedang diproses.',
        ]);
    }

    /**
     * Mask nomor HP: 0812****5678
     */
    private function maskPhone(string $phone): string
    {
        $clean = preg_replace('/\D/', '', $phone);
        if (strlen($clean) < 8) return $phone;
        return substr($clean, 0, 4) . str_repeat('*', strlen($clean) - 8) . substr($clean, -4);
    }

    /**
     * Generate & kirim OTP pengajuan pinjaman ke Email user
     *
     * POST /api/otp/send-loan-email
     * Middleware: auth:sanctum
     */
    public function sendLoanOtpEmail(Request $request)
    {
        $user = Auth::user();

        Log::info('OtpController@sendLoanOtpEmail: dipanggil', [
            'user_id' => $user->id,
            'email'   => $user->email ?? 'NULL',
        ]);

        // ── Validasi email ─────────────────────────────────────────────
        if (empty($user->email)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Alamat email belum diisi di profil kamu. Mohon lengkapi profil terlebih dahulu.',
            ], 422);
        }

        // ── Generate 6-digit OTP ───────────────────────────────────────
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // ── Simpan ke DB ───────────────────────────────────────────────
        try {
            DB::table('otps')->updateOrInsert(
                [
                    'phone' => $user->phone ?? 'no-phone-' . $user->id,
                    'type'  => 'loan_application',
                ],
                [
                    'email'      => $user->email,
                    'otp'        => $otp,
                    'expires_at' => now()->addMinutes(5),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        } catch (\Throwable $e) {
            Log::error('OtpController@sendLoanOtpEmail: DB error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan database. Pastikan sudah menjalankan `php artisan migrate`.',
                'detail'  => $e->getMessage(),
            ], 500);
        }

        // ── Kirim via Email ────────────────────────────────────────────
        try {
            Mail::raw("🔐 FinBankLink\n\nKode OTP pengajuan pinjaman kamu adalah: {$otp}\n\nKode berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun.", function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Kode OTP Pengajuan Pinjaman FinBankLink');
            });
        } catch (\Throwable $e) {
            Log::error('OtpController@sendLoanOtpEmail: Mail error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengirim OTP via Email. Silakan periksa konfigurasi mail server Anda.',
                'detail'  => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'OTP berhasil dikirim ke Email kamu.',
            'email'   => $this->maskEmail($user->email),
        ]);
    }

    /**
     * Mask email: u***r@example.com
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) < 2) return $email;
        $name = $parts[0];
        $domain = $parts[1];
        if (strlen($name) < 3) return $email;
        return substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1) . '@' . $domain;
    }
}

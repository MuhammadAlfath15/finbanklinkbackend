<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $token;
    protected string $apiUrl = 'https://api.fonnte.com/send';

    public function __construct()
    {
        // Baca langsung dari env() agar tidak perlu config cache
        $this->token = env('FONNTE_TOKEN', '');
    }

    /**
     * Kirim pesan WhatsApp via Fonnte
     *
     * @param  string  $phone   Nomor HP tujuan
     * @param  string  $message Isi pesan
     * @return array{ ok: bool, reason: string }
     */
    public function send(string $phone, string $message): array
    {
        if (empty($this->token)) {
            Log::error('FonnteService: FONNTE_TOKEN kosong di .env');
            return ['ok' => false, 'reason' => 'FONNTE_TOKEN tidak ditemukan di konfigurasi server.'];
        }

        $normalised = $this->normalisePhone($phone);

        Log::info('FonnteService: mencoba kirim WA', [
            'original_phone'   => $phone,
            'normalised_phone' => $normalised,
            'token_prefix'     => substr($this->token, 0, 6) . '...',
        ]);

        try {
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target'  => $normalised,
                'message' => $message,
            ]);

            $body   = $response->json() ?? [];
            $status = $response->status();

            Log::info('FonnteService: response dari Fonnte', [
                'http_status' => $status,
                'body'        => $body,
            ]);

            if (!$response->successful()) {
                $reason = $body['reason'] ?? $body['message'] ?? "HTTP $status dari Fonnte.";
                Log::error('FonnteService: HTTP error', ['status' => $status, 'body' => $body]);
                return ['ok' => false, 'reason' => $reason];
            }

            // Fonnte kadang HTTP 200 tapi status = false
            if (isset($body['status']) && $body['status'] === false) {
                $reason = $body['reason'] ?? $body['message'] ?? 'Fonnte menolak pengiriman (status false).';
                Log::error('FonnteService: status false', ['body' => $body]);
                return ['ok' => false, 'reason' => $reason];
            }

            return ['ok' => true, 'reason' => ''];

        } catch (\Throwable $e) {
            Log::error('FonnteService: exception', ['message' => $e->getMessage()]);
            return ['ok' => false, 'reason' => 'Koneksi ke server Fonnte gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Normalise nomor HP ke format 628xxxxxxx
     */
    private function normalisePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}

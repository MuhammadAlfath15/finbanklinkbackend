<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Omzet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessProfileController extends Controller
{
    /**
     * GET /api/business-profile
     * Ambil profil bisnis + skor user yang sedang login
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $bp   = BusinessProfile::firstOrCreate(['user_id' => $user->id]);

        return response()->json($this->buildResponse($bp));
    }

    /**
     * POST /api/business-profile
     * Update data bisnis (menerima multipart/form-data karena ada file)
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $bp   = BusinessProfile::firstOrCreate(['user_id' => $user->id]);

        // ── Validasi ───────────────────────────────────────────────────────────
        $request->validate([
            'nib'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'npwp'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ktp'               => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'kk'                => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'selfie_ktp'        => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'tanda_tangan'      => 'nullable|file|mimes:png|max:2048',
            'rekening'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'omzet_bulan_ini'   => 'nullable|numeric|min:0',
            'foto_usaha'        => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'kontrak'           => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'cicilan_berjalan'  => 'nullable|numeric|min:0',
            'bukti_pelunasan'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'nama_usaha'        => 'nullable|string|max:255',
            'bidang_usaha'      => 'nullable|string|max:255',
            'alamat_usaha'      => 'nullable|string',
            'lama_usaha'        => 'nullable|string|max:255',
            'jumlah_karyawan'   => 'nullable|string|max:255',
        ]);

        // ── Simpan file ───────────────────────────────────────────────────────
        $dir = 'business/' . $user->id;

        $fileMap = [
            'nib'            => 'nib_path',
            'npwp'           => 'npwp_path',
            'ktp'            => 'ktp_path',
            'kk'             => 'kk_path',
            'selfie_ktp'     => 'selfie_ktp_path',
            'tanda_tangan'   => 'ttd_path',
            'rekening'       => 'rekening_path',
            'foto_usaha'     => 'foto_usaha_path',
            'kontrak'        => 'kontrak_path',
            'bukti_pelunasan' => 'bukti_pelunasan_path',
        ];

        foreach ($fileMap as $requestKey => $column) {
            if ($request->hasFile($requestKey)) {
                if ($bp->$column) {
                    Storage::disk('public')->delete($bp->$column);
                }
                $path = $request->file($requestKey)->store($dir, 'public');
                $bp->$column = $path;
            }
        }

        // ── Simpan nilai numerik ──────────────────────────────────────────────
        if ($request->filled('omzet_bulan_ini')) {
            $bp->omzet_bulan_ini = $request->omzet_bulan_ini;
        }
        if ($request->filled('cicilan_berjalan')) {
            $bp->cicilan_berjalan = $request->cicilan_berjalan;
        }

        // ── Simpan info umum ──────────────────────────────────────────────────
        $infoFields = ['nama_usaha', 'bidang_usaha', 'alamat_usaha', 'lama_usaha', 'jumlah_karyawan'];
        foreach ($infoFields as $field) {
            if ($request->has($field)) {
                $bp->$field = $request->$field;
            }
        }

        // ── Hitung skor ───────────────────────────────────────────────────────
        $scores = $this->calculateScores($bp, $user->id);
        $bp->fill($scores);
        $bp->save();

        return response()->json([
            'message' => 'Data bisnis berhasil diperbarui',
            'data'    => $this->buildResponse($bp),
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Hitung 6 skor berdasarkan data yang tersedia.
     * Masing-masing metrik maksimum 100, total maksimum 600.
     */
    private function calculateScores(BusinessProfile $bp, int $userId): array
    {
        // ── 1. Legalitas (max 100) ─────────────────────────────────────────
        $legalitas = 20; // base: sudah daftar
        if ($bp->nib_path)  $legalitas += 40;
        if ($bp->npwp_path) $legalitas += 40;
        $legalitas = min($legalitas, 100);

        // ── 2. Profitabilitas (max 100) ────────────────────────────────────
        $profitabilitas = 30; // base
        if ($bp->rekening_path) $profitabilitas += 30;
        if ($bp->omzet_bulan_ini > 0) {
            // Omzet >= 10 juta = +40, 5-10 juta = +25, < 5 juta = +10
            if ($bp->omzet_bulan_ini >= 10_000_000)      $profitabilitas += 40;
            elseif ($bp->omzet_bulan_ini >= 5_000_000)   $profitabilitas += 25;
            else                                          $profitabilitas += 10;
        }
        $profitabilitas = min($profitabilitas, 100);

        // ── 3. Tren Omzet (max 100) ────────────────────────────────────────
        $trenOmzet = 30; // base
        try {
            $omzetRecords = Omzet::where('user_id', $userId)
                ->where('year', date('Y'))
                ->orderBy('month')
                ->pluck('amount')
                ->toArray();

            $nonZero = array_filter($omzetRecords, fn($v) => $v > 0);
            $count   = count($nonZero);

            if ($count >= 6) {
                // Hitung tren: bandingkan paruh kedua vs paruh pertama
                $half = (int)floor($count / 2);
                $vals = array_values($nonZero);
                $first  = array_sum(array_slice($vals, 0, $half)) / $half;
                $second = array_sum(array_slice($vals, $half)) / ($count - $half);
                $growth = $first > 0 ? ($second - $first) / $first : 0;

                if ($growth >= 0.1)      $trenOmzet += 70;  // tumbuh >10%
                elseif ($growth >= 0)    $trenOmzet += 50;  // stabil
                else                     $trenOmzet += 20;  // turun
            } elseif ($count >= 3) {
                $trenOmzet += 40; // ada data tapi belum lengkap
            } elseif ($count >= 1) {
                $trenOmzet += 20; // minimal ada data
            }
        } catch (\Exception $e) {
            // skip jika error
        }

        // Bonus jika omzet bulan ini juga diisi
        if ($bp->omzet_bulan_ini > 0) $trenOmzet += 10;
        $trenOmzet = min($trenOmzet, 100);

        // ── 4. Kolektibilitas (max 100) ────────────────────────────────────
        $kolektibilitas = 50; // base: asumsi tidak ada tunggakan
        if ($bp->bukti_pelunasan_path) $kolektibilitas += 30;
        if ($bp->cicilan_berjalan !== null) {
            // Ada cicilan tapi bukan nol = risiko, tapi terbuka = +10
            $kolektibilitas += 10;
            // Jika cicilan terlalu besar dibanding omzet, kurangi
            if ($bp->omzet_bulan_ini > 0 && $bp->cicilan_berjalan > ($bp->omzet_bulan_ini * 0.5)) {
                $kolektibilitas -= 15;
            }
        } else {
            // Tidak isi berarti tidak ada cicilan lain = +20
            $kolektibilitas += 20;
        }
        $kolektibilitas = min(max($kolektibilitas, 0), 100);

        // ── 5. Keberlanjutan (max 100) ─────────────────────────────────────
        $keberlanjutan = 20; // base
        if ($bp->foto_usaha_path) $keberlanjutan += 40;
        if ($bp->kontrak_path)    $keberlanjutan += 40;
        $keberlanjutan = min($keberlanjutan, 100);

        // ── 6. Kapasitas Utang (max 100) ───────────────────────────────────
        $kapasitasUtang = 50; // base
        if ($bp->cicilan_berjalan !== null) {
            if ($bp->omzet_bulan_ini > 0) {
                $ratio = $bp->cicilan_berjalan / $bp->omzet_bulan_ini;
                if ($ratio <= 0.2)      $kapasitasUtang = 90;
                elseif ($ratio <= 0.35) $kapasitasUtang = 75;
                elseif ($ratio <= 0.5)  $kapasitasUtang = 55;
                else                    $kapasitasUtang = 30;
            } elseif ($bp->cicilan_berjalan == 0) {
                $kapasitasUtang = 85; // tidak ada cicilan = sangat baik
            }
        }
        if ($bp->bukti_pelunasan_path) $kapasitasUtang = min($kapasitasUtang + 10, 100);
        $kapasitasUtang = min($kapasitasUtang, 100);

        $total = $legalitas + $profitabilitas + $trenOmzet + $kolektibilitas + $keberlanjutan + $kapasitasUtang;

        return [
            'skor_profitabilitas'  => $profitabilitas,
            'skor_legalitas'       => $legalitas,
            'skor_tren_omzet'      => $trenOmzet,
            'skor_kolektibilitas'  => $kolektibilitas,
            'skor_keberlanjutan'   => $keberlanjutan,
            'skor_kapasitas_utang' => $kapasitasUtang,
            'skor_total'           => $total,
        ];
    }

    private function buildResponse(BusinessProfile $bp): array
    {
        return [
            'id'                   => $bp->id,
            'user_id'              => $bp->user_id,
            // file presence flags (frontend tidak perlu path absolut)
            'has_nib'              => !empty($bp->nib_path),
            'has_npwp'             => !empty($bp->npwp_path),
            'has_ktp'              => !empty($bp->ktp_path),
            'has_kk'               => !empty($bp->kk_path),
            'has_selfie_ktp'       => !empty($bp->selfie_ktp_path),
            'has_ttd'              => !empty($bp->ttd_path),
            'has_rekening'         => !empty($bp->rekening_path),
            'has_foto_usaha'       => !empty($bp->foto_usaha_path),
            'has_kontrak'          => !empty($bp->kontrak_path),
            'has_bukti_pelunasan'  => !empty($bp->bukti_pelunasan_path),
            // nilai numerik
            'omzet_bulan_ini'      => $bp->omzet_bulan_ini,
            'cicilan_berjalan'     => $bp->cicilan_berjalan,
            // info umum
            'nama_usaha'           => $bp->nama_usaha,
            'bidang_usaha'         => $bp->bidang_usaha,
            'alamat_usaha'         => $bp->alamat_usaha,
            'lama_usaha'           => $bp->lama_usaha,
            'jumlah_karyawan'      => $bp->jumlah_karyawan,
            // skor
            'skor_profitabilitas'  => $bp->skor_profitabilitas,
            'skor_legalitas'       => $bp->skor_legalitas,
            'skor_tren_omzet'      => $bp->skor_tren_omzet,
            'skor_kolektibilitas'  => $bp->skor_kolektibilitas,
            'skor_keberlanjutan'   => $bp->skor_keberlanjutan,
            'skor_kapasitas_utang' => $bp->skor_kapasitas_utang,
            'skor_total'           => $bp->skor_total,
            'updated_at'           => $bp->updated_at?->toISOString(),
        ];
    }
}

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

        // Recalculate and save scores to ensure they are synchronized with the new audit rules on fetch
        $bp->recalculateScores();
        $bp->save();

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

        $statuses = $bp->document_statuses ?? [];
        $feedbacks = $bp->document_feedbacks ?? [];

        foreach ($fileMap as $requestKey => $column) {
            if ($request->hasFile($requestKey)) {
                if ($bp->$column) {
                    Storage::disk('public')->delete($bp->$column);
                }
                $path = $request->file($requestKey)->store($dir, 'public');
                $bp->$column = $path;

                // Set status to pending on new upload
                $statuses[$column] = 'pending';
                // Clear old feedback
                if (isset($feedbacks[$column])) {
                    unset($feedbacks[$column]);
                }
            }
        }

        $bp->document_statuses = $statuses;
        $bp->document_feedbacks = $feedbacks;

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
        $bp->recalculateScores();
        $bp->save();

        return response()->json([
            'message' => 'Data bisnis berhasil diperbarui',
            'data'    => $this->buildResponse($bp),
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

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
            // audit
            'document_statuses'    => $bp->document_statuses ?? (object)[],
            'document_feedbacks'   => $bp->document_feedbacks ?? (object)[],
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

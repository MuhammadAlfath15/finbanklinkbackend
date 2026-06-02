<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BusinessProfile;
use App\Models\Omzet;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    /**
     * GET /api/submissions — daftar pengajuan milik user yang sedang login.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (($user->role ?? 'user') !== 'user') {
            return response()->json(['message' => 'Hanya untuk akun nasabah.'], 403);
        }

        $rows = Submission::query()
            ->with(['bank:id,nama_bank,nama_produk'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($rows->map(fn (Submission $s) => $this->toUserRow($s)));
    }

    /**
     * DELETE /api/submissions/{id} — batalkan pengajuan (hanya saat menunggu).
     */
    public function cancel(Request $request, int $id)
    {
        $user = $request->user();
        if (($user->role ?? 'user') !== 'user') {
            return response()->json(['message' => 'Hanya untuk akun nasabah.'], 403);
        }

        $submission = Submission::where('user_id', $user->id)->findOrFail($id);

        if ($submission->status !== 'menunggu') {
            return response()->json([
                'message' => 'Pengajuan ini tidak bisa dibatalkan (sudah diproses bank atau sudah dibatalkan).',
            ], 422);
        }

        $submission->status = 'dibatalkan';
        $submission->save();

        return response()->json(['message' => 'Pengajuan berhasil dibatalkan.']);
    }

    /**
     * POST /api/submissions — user mengirim pengajuan lengkap (setelah flow OTP + form).
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (($user->role ?? 'user') !== 'user') {
            return response()->json(['message' => 'Hanya pengguna UMKM yang dapat mengajukan.'], 403);
        }

        $request->validate([
            'bank_id'             => 'required|exists:banks,id',
            'nominal_pinjaman'    => 'required|numeric|min:1',
            'tenor'               => 'required|integer|min:1|max:120',
            'cicilan_per_bulan'   => 'nullable|numeric|min:0',
            'ktp_nama'            => 'required|string|max:255',
            'ktp_nik'             => 'required|string|max:32',
            'pemohon_alamat'      => 'nullable|string|max:2000',
            'nama_usaha'          => 'nullable|string|max:255',
            'bidang_usaha'        => 'nullable|string|max:255',
            'alamat_usaha'        => 'nullable|string|max:512',
            'ktp'                 => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'nib'                 => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $bank = Bank::findOrFail($request->bank_id);

        $bp = BusinessProfile::firstOrCreate(['user_id' => $user->id]);

        $year = (int) date('Y');
        $omzetRow = array_fill(0, 12, 0);
        try {
            $rows = Omzet::where('user_id', $user->id)->where('year', $year)->orderBy('month')->get();
            foreach ($rows as $row) {
                if ($row->month >= 1 && $row->month <= 12) {
                    $omzetRow[$row->month - 1] = (float) $row->amount;
                }
            }
        } catch (\Throwable $e) {
            // biarkan nol
        }

        $submission = new Submission([
            'user_id'               => $user->id,
            'bank_id'               => $bank->id,
            'nama_produk'           => $bank->nama_produk,
            'status'                => 'menunggu',
            'nominal_pinjaman'      => (string) (int) $request->nominal_pinjaman,
            'tenor'                 => (string) (int) $request->tenor,
            'nama_usaha'            => $request->nama_usaha,
            'bidang_usaha'          => $request->bidang_usaha,
            'alamat_usaha'          => $request->alamat_usaha,
            'pemohon_phone'         => $user->phone,
            'pemohon_alamat'        => $request->pemohon_alamat,
            'cicilan_per_bulan'     => $request->filled('cicilan_per_bulan') ? $request->cicilan_per_bulan : null,
            'ktp_nik'               => $request->ktp_nik,
            'ktp_nama'              => $request->ktp_nama,
            'skor_total'            => $bp->skor_total,
            'skor_profitabilitas'   => $bp->skor_profitabilitas,
            'skor_legalitas'        => $bp->skor_legalitas,
            'skor_tren_omzet'       => $bp->skor_tren_omzet,
            'skor_kolektibilitas'   => $bp->skor_kolektibilitas,
            'skor_keberlanjutan'    => $bp->skor_keberlanjutan,
            'skor_kapasitas_utang'  => $bp->skor_kapasitas_utang,
            'omzet_year'            => $year,
            'omzet_data'            => $omzetRow,
        ]);
        $submission->save();

        $dir = 'submissions/' . $submission->id;
        $submission->ktp_upload_path = $request->file('ktp')->store($dir, 'public');
        $submission->nib_upload_path = $request->file('nib')->store($dir, 'public');
        $submission->reference_code = sprintf('REQ-%s-%06d', date('Y'), $submission->id);
        $submission->save();

        return response()->json([
            'message' => 'Pengajuan berhasil dikirim',
            'data'    => [
                'id'              => $submission->id,
                'reference_code'  => $submission->reference_code,
                'status'          => $submission->status,
            ],
        ], 201);
    }

    private function toUserRow(Submission $s): array
    {
        $ref = $s->reference_code ?: sprintf('REQ-%s-%06d', $s->created_at?->format('Y') ?? date('Y'), $s->id);
        $nominal = (int) $s->nominal_pinjaman;
        $tenor = (int) $s->tenor;
        $cicilan = $s->cicilan_per_bulan !== null ? (float) $s->cicilan_per_bulan : 0.0;

        $statusLabel = match ($s->status) {
            'disetujui'   => 'Disetujui',
            'ditolak'     => 'Ditolak',
            'dibatalkan'  => 'Dibatalkan',
            'verifikasi'  => 'Verifikasi',
            'survei'      => 'Survei',
            default       => 'Menunggu',
        };

        return [
            'submission_id' => $s->id,
            'id'            => $ref,
            'nama_bank'     => $s->bank?->nama_bank ?? 'Bank',
            'nama_produk'   => $s->nama_produk ?? $s->bank?->nama_produk ?? '—',
            'status'        => $statusLabel,
            'status_raw'    => $s->status,
            'nominal'       => $nominal,
            'tenor'         => $tenor,
            'cicilan'       => $cicilan,
            'submitted_at'  => $s->created_at?->toIso8601String(),
            'updated_at'    => $s->updated_at?->toIso8601String(),
            'bank_message'  => $s->bank_message,
        ];
    }
}

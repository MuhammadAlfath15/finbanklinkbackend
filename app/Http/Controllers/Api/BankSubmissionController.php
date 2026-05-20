<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BankSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (($user->role ?? '') !== 'bank') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $q = Submission::with(['user:id,name,email,phone', 'bank:id,nama_bank,nama_produk'])
            ->orderByDesc('created_at');

        if (!empty($user->bank_id)) {
            $q->where('bank_id', $user->bank_id);
        }

        return response()->json($q->get()->map(fn (Submission $s) => $this->toListItem($s)));
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();
        if (($user->role ?? '') !== 'bank') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $submission = Submission::with(['user:id,name,email,phone', 'bank'])->findOrFail($id);
        if (!empty($user->bank_id) && (int) $submission->bank_id !== (int) $user->bank_id) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan.'], 404);
        }

        return response()->json($this->toDetail($submission));
    }

    public function updateStatus(Request $request, int $id)
    {
        $user = $request->user();
        if (($user->role ?? '') !== 'bank') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:disetujui,ditolak',
            'message' => 'nullable|string'
        ]);

        $submission = Submission::findOrFail($id);
        if (!empty($user->bank_id) && (int) $submission->bank_id !== (int) $user->bank_id) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan.'], 404);
        }

        if ($submission->status !== 'menunggu') {
            return response()->json(['message' => 'Status pengajuan sudah pernah diputuskan.'], 422);
        }

        $submission->status = $request->status;
        $submission->save();

        $bankName = $submission->bank ? $submission->bank->nama_bank : 'Admin Bank';
        $statusText = $request->status === 'disetujui' ? 'Disetujui' : 'Ditolak';
        
        Notification::create([
            'user_id' => $submission->user_id,
            'title' => "Pesan dari {$bankName}",
            'subject' => "Pengajuan Pinjaman {$statusText}",
            'message' => $request->message ?: "Status pengajuan pinjaman Anda telah diubah menjadi {$statusText}.",
        ]);

        return response()->json([
            'message' => 'Status berhasil diperbarui',
            'data'    => $this->toListItem($submission->fresh(['user:id,name,email,phone', 'bank:id,nama_bank,nama_produk'])),
        ]);
    }

    private function publicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    private function toListItem(Submission $s): array
    {
        $ref = $s->reference_code ?: sprintf('REQ-%s-%06d', $s->created_at?->format('Y') ?? date('Y'), $s->id);
        $statusLabel = match ($s->status) {
            'disetujui'  => 'Disetujui',
            'ditolak'    => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            default      => 'Menunggu',
        };

        return [
            'id'                 => $ref,
            'submission_id'      => $s->id,
            'date'               => $s->created_at?->translatedFormat('d M Y') ?? '',
            'umkm'               => $s->nama_usaha ?: ($s->user?->name ?? 'UMKM'),
            'product'            => $s->nama_produk ?? $s->bank?->nama_produk ?? '—',
            'amount'             => (int) $s->nominal_pinjaman,
            'tenor'              => (int) $s->tenor,
            'score'              => $this->displayScore($s),
            'status'             => $statusLabel,
            'owner'              => $s->ktp_nama ?? $s->user?->name,
            'phone'              => $s->pemohon_phone ?? $s->user?->phone ?? '—',
            'businessType'       => $s->bidang_usaha ?: '—',
            'address'            => $s->alamat_usaha ?: '—',
            'skor_total'         => $s->skor_total,
            'bank_nama'          => $s->bank?->nama_bank,
        ];
    }

    /** Skor tampilan 0–100 proporsional dari total /600 (sesuai kartu bank). */
    private function displayScore(Submission $s): int
    {
        $t = (int) ($s->skor_total ?? 0);

        return (int) min(100, max(0, round($t / 6)));
    }

    private function toDetail(Submission $s): array
    {
        $list = $this->toListItem($s);

        $health = [
            'skor_total'           => (int) ($s->skor_total ?? 0),
            'skor_profitabilitas'  => (int) ($s->skor_profitabilitas ?? 0),
            'skor_legalitas'       => (int) ($s->skor_legalitas ?? 0),
            'skor_tren_omzet'      => (int) ($s->skor_tren_omzet ?? 0),
            'skor_kolektibilitas'  => (int) ($s->skor_kolektibilitas ?? 0),
            'skor_keberlanjutan'   => (int) ($s->skor_keberlanjutan ?? 0),
            'skor_kapasitas_utang'=> (int) ($s->skor_kapasitas_utang ?? 0),
        ];

        $kolekLabel = $health['skor_kolektibilitas'] >= 70 ? 'Lancar' : ($health['skor_kolektibilitas'] >= 50 ? 'Perlu Review' : 'Perhatian');
        $legalLabel = $health['skor_legalitas'] >= 60 ? 'Tervalidasi' : 'Belum lengkap';

        $omzetYear = $s->omzet_year ?? (int) date('Y');
        $omzetData = is_array($s->omzet_data) ? $s->omzet_data : array_fill(0, 12, 0);
        while (count($omzetData) < 12) {
            $omzetData[] = 0;
        }
        $omzetData = array_slice(array_map('floatval', $omzetData), 0, 12);

        $documents = array_values(array_filter([
            $s->ktp_upload_path ? [
                'key'   => 'ktp',
                'label' => 'Foto KTP (pengajuan)',
                'url'   => $this->publicUrl($s->ktp_upload_path),
            ] : null,
            $s->nib_upload_path ? [
                'key'   => 'nib',
                'label' => 'Foto NIB / dokumen usaha',
                'url'   => $this->publicUrl($s->nib_upload_path),
            ] : null,
        ]));

        return array_merge($list, [
            'ktp_nik'            => $s->ktp_nik,
            'pemohon_alamat'     => $s->pemohon_alamat,
            'cicilan_per_bulan'  => $s->cicilan_per_bulan,
            'health'             => $health,
            'health_labels'      => [
                'kolektibilitas' => $kolekLabel,
                'legalitas'      => $legalLabel,
                'riwayat'        => 'Dari data pengajuan',
            ],
            'omzet'              => [
                'year' => $omzetYear,
                'data' => $omzetData,
            ],
            'documents'          => $documents,
            'user_email'         => $s->user?->email,
        ]);
    }
}

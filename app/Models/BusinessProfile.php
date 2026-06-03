<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    protected $fillable = [
        'user_id',
        'nama_usaha',
        'bidang_usaha',
        'alamat_usaha',
        'lama_usaha',
        'jumlah_karyawan',
        // legalitas
        'nib_path',
        'npwp_path',
        'ktp_path',
        'kk_path',
        'selfie_ktp_path',
        'ttd_path',
        // keuangan
        'rekening_path',
        'omzet_bulan_ini',
        // operasional
        'foto_usaha_path',
        'kontrak_path',
        // kapasitas utang
        'cicilan_berjalan',
        'bukti_pelunasan_path',
        // audit
        'document_statuses',
        'document_feedbacks',
        // skor
        'skor_profitabilitas',
        'skor_legalitas',
        'skor_tren_omzet',
        'skor_kolektibilitas',
        'skor_keberlanjutan',
        'skor_kapasitas_utang',
        'skor_total',
    ];

    protected $casts = [
        'omzet_bulan_ini'   => 'decimal:2',
        'cicilan_berjalan'  => 'decimal:2',
        'document_statuses'  => 'array',
        'document_feedbacks' => 'array',
    ];

    public function getDocStatus(string $type): string
    {
        $statuses = $this->document_statuses ?? [];
        return $statuses[$type] ?? 'pending';
    }

    public function getDocFeedback(string $type): ?string
    {
        $feedbacks = $this->document_feedbacks ?? [];
        return $feedbacks[$type] ?? null;
    }

    public function recalculateScores(): void
    {
        // 1. Legalitas
        $legalitas = 0;
        if ($this->nib_path || $this->npwp_path) {
            $legalitas = 20; // base: sudah mulai unggah berkas
            if ($this->nib_path && $this->getDocStatus('nib_path') === 'approved')  $legalitas += 40;
            if ($this->npwp_path && $this->getDocStatus('npwp_path') === 'approved') $legalitas += 40;
        }
        $this->skor_legalitas = min($legalitas, 100);

        // 2. Profitabilitas
        $profitabilitas = 0;
        if ($this->rekening_path || $this->omzet_bulan_ini > 0) {
            $profitabilitas = 30; // base: sudah mulai unggah berkas / input data keuangan
            if ($this->rekening_path && $this->getDocStatus('rekening_path') === 'approved') $profitabilitas += 30;
            if ($this->omzet_bulan_ini > 0) {
                if ($this->omzet_bulan_ini >= 10_000_000)      $profitabilitas += 40;
                elseif ($this->omzet_bulan_ini >= 5_000_000)   $profitabilitas += 25;
                else                                           $profitabilitas += 10;
            }
        }
        $this->skor_profitabilitas = min($profitabilitas, 100);

        // 3. Tren Omzet (max 100)
        $trenOmzet = 0;
        $count = 0;
        $nonZero = [];
        try {
            $omzetRecords = \App\Models\Omzet::where('user_id', $this->user_id)
                ->where('year', date('Y'))
                ->orderBy('month')
                ->pluck('amount')
                ->toArray();

            $nonZero = array_filter($omzetRecords, fn($v) => $v > 0);
            $count   = count($nonZero);
        } catch (\Exception $e) {}

        if ($count > 0 || $this->omzet_bulan_ini > 0) {
            $trenOmzet = 30; // base: sudah ada riwayat omzet / data keuangan
            if ($count >= 6) {
                $half = (int)floor($count / 2);
                $vals = array_values($nonZero);
                $first  = array_sum(array_slice($vals, 0, $half)) / $half;
                $second = array_sum(array_slice($vals, $half)) / ($count - $half);
                $growth = $first > 0 ? ($second - $first) / $first : 0;

                if ($growth >= 0.1)      $trenOmzet += 70;
                elseif ($growth >= 0)    $trenOmzet += 50;
                else                     $trenOmzet += 20;
            } elseif ($count >= 3) {
                $trenOmzet += 40;
            } elseif ($count >= 1) {
                $trenOmzet += 20;
            }
            if ($this->omzet_bulan_ini > 0) $trenOmzet += 10;
        }
        $this->skor_tren_omzet = min($trenOmzet, 100);

        // 4. Kolektibilitas (max 100)
        $kolektibilitas = 0;
        if ($this->bukti_pelunasan_path || $this->cicilan_berjalan !== null) {
            $kolektibilitas = 50; // base: asumsi tidak ada tunggakan
            if ($this->bukti_pelunasan_path && $this->getDocStatus('bukti_pelunasan_path') === 'approved') $kolektibilitas += 30;
            if ($this->cicilan_berjalan !== null) {
                $kolektibilitas += 10;
                if ($this->omzet_bulan_ini > 0 && $this->cicilan_berjalan > ($this->omzet_bulan_ini * 0.5)) {
                    $kolektibilitas -= 15;
                }
            } else {
                $kolektibilitas += 20;
            }
        }
        $this->skor_kolektibilitas = min(max($kolektibilitas, 0), 100);

        // 5. Keberlanjutan (max 100)
        $keberlanjutan = 0;
        if ($this->foto_usaha_path || $this->kontrak_path) {
            $keberlanjutan = 20; // base: sudah mulai unggah berkas
            if ($this->foto_usaha_path && $this->getDocStatus('foto_usaha_path') === 'approved') $keberlanjutan += 40;
            if ($this->kontrak_path && $this->getDocStatus('kontrak_path') === 'approved')    $keberlanjutan += 40;
        }
        $this->skor_keberlanjutan = min($keberlanjutan, 100);

        // 6. Kapasitas Utang (max 100)
        $kapasitasUtang = 0;
        if ($this->cicilan_berjalan !== null || $this->bukti_pelunasan_path) {
            $kapasitasUtang = 50; // base
            if ($this->cicilan_berjalan !== null) {
                if ($this->omzet_bulan_ini > 0) {
                    $ratio = $this->cicilan_berjalan / $this->omzet_bulan_ini;
                    if ($ratio <= 0.2)      $kapasitasUtang = 90;
                    elseif ($ratio <= 0.35) $kapasitasUtang = 75;
                    elseif ($ratio <= 0.5)  $kapasitasUtang = 55;
                    else                    $kapasitasUtang = 30;
                } elseif ($this->cicilan_berjalan == 0) {
                    $kapasitasUtang = 85;
                }
            }
            if ($this->bukti_pelunasan_path && $this->getDocStatus('bukti_pelunasan_path') === 'approved') $kapasitasUtang = min($kapasitasUtang + 10, 100);
        }
        $this->skor_kapasitas_utang = min($kapasitasUtang, 100);

        $this->skor_total = $this->skor_profitabilitas + $this->skor_legalitas + $this->skor_tren_omzet + $this->skor_kolektibilitas + $this->skor_keberlanjutan + $this->skor_kapasitas_utang;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

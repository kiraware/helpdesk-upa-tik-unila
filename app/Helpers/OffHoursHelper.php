<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Helper untuk menghitung skor dedikasi petugas berdasarkan
 * pengerjaan tiket di luar jam operasional resmi.
 *
 * Jam Operasional:
 *   Senin – Kamis : 08.00–12.00 | 13.30–16.00 WIB
 *   Jumat         : 08.00–11.30 | 14.00–16.30 WIB
 *   Sabtu & Minggu: LIBUR (semua tiket = bonus penuh)
 */
class OffHoursHelper
{
    /**
     * Poin mentah per tiket yang dikerjakan:
     *   - Weekend / hari libur  → BONUS_WEEKEND poin
     *   - Weekday di luar jam   → BONUS_OFFHOUR poin
     *   - Weekday di dalam jam  → 0 (tidak ada bonus)
     */
    private const BONUS_WEEKEND = 2;

    private const BONUS_OFFHOUR = 1;

    /**
     * Bobot akhir bonus terhadap skor ranking gabungan.
     * 85% dari CSI + 15% dari skor dedikasi (maks 10).
     */
    public const WEIGHT_CSI = 0.85;

    public const WEIGHT_DEDIKASI = 0.15;

    /**
     * Hitung poin dedikasi mentah dari koleksi tiket seorang petugas.
     * Menggunakan assigned_at sebagai momen "mulai mengerjakan".
     *
     * @param  Collection  $tickets  Collection of Ticket model instances
     * @return array{
     *   raw_points: int,
     *   weekend_count: int,
     *   offhour_count: int,
     *   onhour_count: int,
     * }
     */
    public static function calcDedikasi(Collection $tickets): array
    {
        $rawPoints = 0;
        $weekendCount = 0;
        $offhourCount = 0;
        $onhourCount = 0;

        foreach ($tickets as $ticket) {
            // Gunakan assigned_at; kalau null fallback ke created_at
            $ts = $ticket->assigned_at ?? $ticket->created_at;
            if (! $ts) {
                continue;
            }

            // Konversi ke WIB (Asia/Jakarta) untuk evaluasi jam kerja
            $dt = $ts->copy()->timezone('Asia/Jakarta');

            if (self::isWeekend($dt)) {
                $rawPoints += self::BONUS_WEEKEND;
                $weekendCount++;
            } elseif (! self::isWithinWorkingHours($dt)) {
                $rawPoints += self::BONUS_OFFHOUR;
                $offhourCount++;
            } else {
                $onhourCount++;
            }
        }

        return [
            'raw_points' => $rawPoints,
            'weekend_count' => $weekendCount,
            'offhour_count' => $offhourCount,
            'onhour_count' => $onhourCount,
        ];
    }

    /**
     * Normalisasi raw_points seluruh staf ke skala 0–10,
     * lalu hitung ranking_score = (CSI × 0.85) + (dedikasi_norm × 10 × 0.15).
     *
     * @param  array  $staffData  Array of staff arrays, masing-masing wajib punya
     *                            'csi' (float 0–100) dan 'raw_points' (int).
     * @return array Staff data yang sudah dibubuhi 'dedikasi_score' dan 'ranking_score'.
     */
    public static function applyRankingScore(array $staffData): array
    {
        // Cari nilai raw_points tertinggi untuk normalisasi
        $maxPoints = max(1, max(array_column($staffData, 'raw_points')));

        foreach ($staffData as &$s) {
            // Normalisasi 0–10
            $dedikasiNorm = ($s['raw_points'] / $maxPoints) * 10;
            $s['dedikasi_score'] = round($dedikasiNorm, 2);

            // Skor gabungan: CSI tetap dominan (85%), dedikasi booster (15%)
            // Karena CSI sudah dalam %, dedikasi_norm dikali 10 supaya skalanya setara
            $s['ranking_score'] = round(
                ($s['csi'] * self::WEIGHT_CSI) + ($dedikasiNorm * 10 * self::WEIGHT_DEDIKASI),
                4
            );
        }
        unset($s);

        return $staffData;
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────

    /** Sabtu (6) atau Minggu (7) */
    private static function isWeekend(Carbon $dt): bool
    {
        return $dt->dayOfWeekIso >= 6;
    }

    /**
     * Apakah timestamp berada dalam jam kerja?
     *
     * Senin – Kamis : 08:00–12:00 dan 13:30–16:00
     * Jumat         : 08:00–11:30 dan 14:00–16:30
     */
    private static function isWithinWorkingHours(Carbon $dt): bool
    {
        $dow = $dt->dayOfWeekIso; // 1=Sen … 5=Jum
        $time = $dt->hour * 60 + $dt->minute; // menit sejak tengah malam

        if ($dow >= 1 && $dow <= 4) {
            // Senin – Kamis: 08:00–12:00 (480–720) | 13:30–16:00 (810–960)
            return ($time >= 480 && $time <= 720)
                || ($time >= 810 && $time <= 960);
        }

        if ($dow === 5) {
            // Jumat: 08:00–11:30 (480–690) | 14:00–16:30 (840–990)
            return ($time >= 480 && $time <= 690)
                || ($time >= 840 && $time <= 990);
        }

        return false; // Sabtu/Minggu sudah ditangkap isWeekend()
    }
}

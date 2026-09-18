<?php

namespace App\Services;

use App\Models\JadwalPerencanaan;
use Carbon\Carbon;

class JadwalCalculationService
{
    public function calculateTanggalSelesai(Carbon|string $tanggalMulai, int $durasiHari): Carbon
    {
        $start = Carbon::parse($tanggalMulai);

        return $start->copy()->addDays(max(1, $durasiHari) - 1);
    }

    public function calculateDistribusiItem(float $bobot, int $hariMulai, int $hariSelesai): array
    {
        $durasi = max(1, ($hariSelesai - $hariMulai) + 1);
        $daily = round($bobot / $durasi, 4);

        $distribusi = [];
        for ($h = $hariMulai; $h <= $hariSelesai; $h++) {
            $distribusi[(string) $h] = $daily;
        }

        return $distribusi;
    }

    public function calculateKurvaS(JadwalPerencanaan $jadwal): array
    {
        $durasi = max(1, $jadwal->durasi_hari);
        $startDate = $jadwal->tanggal_mulai ? Carbon::parse($jadwal->tanggal_mulai) : Carbon::today();
        $items = $jadwal->items;

        $days = [];
        $kumulatif = 0.0;
        $monthsGroup = [];

        for ($h = 1; $h <= $durasi; $h++) {
            $currentDate = $startDate->copy()->addDays($h - 1);
            $isFriday = $currentDate->isFriday();

            $dailyTotal = 0.0;
            foreach ($items as $item) {
                if ($item->distribusi && isset($item->distribusi[(string) $h])) {
                    $dailyTotal += (float) $item->distribusi[(string) $h];
                } elseif ($h >= $item->hari_mulai && $h <= $item->hari_selesai && $item->durasi > 0) {
                    $dailyTotal += ((float) $item->bobot / $item->durasi);
                }
            }

            $kumulatif += $dailyTotal;

            $monthKey = $currentDate->format('Y-m');
            if (! isset($monthsGroup[$monthKey])) {
                $monthsGroup[$monthKey] = [
                    'label' => strtoupper($currentDate->format('F Y')),
                    'count' => 0,
                ];
            }
            $monthsGroup[$monthKey]['count']++;

            $days[$h] = [
                'hari_ke' => $h,
                'tanggal' => $currentDate,
                'tanggal_formatted' => $currentDate->format('d/m/Y'),
                'tanggal_display' => $currentDate->format('d M'),
                'day_num' => (int) $currentDate->format('j'),
                'is_jumat' => $isFriday,
                'is_progress_cutoff' => $isFriday,
                'bobot_rencana' => round($dailyTotal, 2),
                'kumulatif_rencana' => round($kumulatif, 2),
            ];
        }

        if ($jadwal->is_bobot_valid && isset($days[$durasi])) {
            $days[$durasi]['kumulatif_rencana'] = 100.00;
        }

        return [
            'durasi_hari' => $durasi,
            'days' => $days,
            'monthsGroup' => $monthsGroup,
            'total_bobot' => round($items->sum('bobot'), 2),
            'final_kumulatif' => isset($days[$durasi]) ? $days[$durasi]['kumulatif_rencana'] : 0.0,
        ];
    }
}

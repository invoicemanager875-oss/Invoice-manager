<?php

namespace App\Services;

use App\Models\ContractCounter;
use Illuminate\Support\Facades\DB;

class ContractNumberService
{
    /**
     * Generate nomor kontrak/SPK baru.
     * Format: SPK/YYYY/001
     *
     * @return array{nomor: string, nomor_urut: int, tahun: int}
     */
    public function generate(int $brandId, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        return DB::transaction(function () use ($brandId, $year) {
            $counter = ContractCounter::query()
                ->where('brand_id', $brandId)
                ->where('tahun', $year)
                ->lockForUpdate()
                ->first();

            if (! $counter) {
                $counter = ContractCounter::create([
                    'brand_id' => $brandId,
                    'tahun' => $year,
                    'last_number' => 0,
                ]);
            }

            $counter->increment('last_number');
            $counter->refresh();

            $nomor = sprintf('SPK/%04d/%03d', $year, $counter->last_number);

            return [
                'nomor' => $nomor,
                'nomor_urut' => $counter->last_number,
                'tahun' => $year,
            ];
        });
    }
}

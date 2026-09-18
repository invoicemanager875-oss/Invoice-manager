<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\InvoiceCounter;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    /**
     * Generate nomor invoice baru.
     *
     * Format:
     * INV/YYYY/MM/BRAND/001
     *
     * @return array{nomor: string, nomor_urut: int, tahun: int, bulan: int}
     */
    public function generate(int $brandId): array
    {
        return DB::transaction(function () use ($brandId) {

            $brand = Brand::findOrFail($brandId);

            $year = now()->year;
            $month = now()->month;

            $counter = InvoiceCounter::query()
                ->where('brand_id', $brandId)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (! $counter) {

                $counter = InvoiceCounter::create([
                    'brand_id' => $brandId,
                    'year' => $year,
                    'month' => $month,
                    'last_number' => 0,
                ]);
            }

            $counter->increment('last_number');

            $counter->refresh();

            return [
                'nomor' => $this->format(
                    $year,
                    $month,
                    $brand->code,
                    $counter->last_number
                ),
                'nomor_urut' => $counter->last_number,
                'tahun' => $year,
                'bulan' => $month,
            ];
        });
    }

    /**
     * Format nomor invoice.
     */
    protected function format(
        int $year,
        int $month,
        string $brandCode,
        int $sequence
    ): string {

        return sprintf(
            'INV/%04d/%02d/%s/%03d',
            $year,
            $month,
            strtoupper($brandCode),
            $sequence
        );
    }
}

<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\FormOrderCounter;
use Illuminate\Support\Facades\DB;

class FormOrderNumberService
{
    /**
     * Generate nomor form order baru.
     *
     * Format:
     * FO/YYYY/MM/{brandId}/001
     *
     * @return array{nomor: string, nomor_urut: int, tahun: int, bulan: int}
     */
    public function generate(int $brandId): array
    {
        return DB::transaction(function () use ($brandId) {

            $brand = Brand::findOrFail($brandId);

            $year = now()->year;
            $month = now()->month;

            $counter = FormOrderCounter::query()
                ->where('brand_id', $brandId)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (! $counter) {

                $counter = FormOrderCounter::create([
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
                    $brand->id,
                    $counter->last_number
                ),
                'nomor_urut' => $counter->last_number,
                'tahun' => $year,
                'bulan' => $month,
            ];
        });
    }

    /**
     * Format nomor form order.
     */
    protected function format(
        int $year,
        int $month,
        int $brandId,
        int $sequence
    ): string {

        return sprintf(
            'FO/%04d/%02d/%d/%03d',
            $year,
            $month,
            $brandId,
            $sequence
        );
    }
}

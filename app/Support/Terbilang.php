<?php

namespace App\Support;

class Terbilang
{
    protected static array $units = [
        '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas',
    ];

    public static function words(int $number): string
    {
        $number = abs($number);

        if ($number === 0) {
            return 'Nol';
        }

        return trim(preg_replace('/\s+/', ' ', self::convert($number)));
    }

    public static function rupiah(float $number): string
    {
        return self::words((int) round($number)).' Rupiah';
    }

    public static function make(float|int $number): string
    {
        return self::rupiah((float) $number);
    }

    protected static function convert(int $number): string
    {
        if ($number < 12) {
            return ' '.self::$units[$number];
        }

        if ($number < 20) {
            return self::convert($number - 10).' Belas';
        }

        if ($number < 100) {
            return self::convert(intdiv($number, 10)).' Puluh'.self::convert($number % 10);
        }

        if ($number < 200) {
            return ' Seratus'.self::convert($number - 100);
        }

        if ($number < 1000) {
            return self::convert(intdiv($number, 100)).' Ratus'.self::convert($number % 100);
        }

        if ($number < 2000) {
            return ' Seribu'.self::convert($number - 1000);
        }

        if ($number < 1_000_000) {
            return self::convert(intdiv($number, 1000)).' Ribu'.self::convert($number % 1000);
        }

        if ($number < 1_000_000_000) {
            return self::convert(intdiv($number, 1_000_000)).' Juta'.self::convert($number % 1_000_000);
        }

        if ($number < 1_000_000_000_000) {
            return self::convert(intdiv($number, 1_000_000_000)).' Miliar'.self::convert($number % 1_000_000_000);
        }

        return self::convert(intdiv($number, 1_000_000_000_000)).' Triliun'.self::convert($number % 1_000_000_000_000);
    }
}

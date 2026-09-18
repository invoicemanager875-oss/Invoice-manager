<?php

namespace App\Actions\Brand;

use App\Actions\Brand\Concerns\StoresBrandFiles;
use App\Models\Brand;

class CreateBrandAction
{
    use StoresBrandFiles;

    public function execute(array $data, int $createdBy): Brand
    {
        $data = $this->storeBrandFiles($data);
        $data = $this->normalizeRekening($data);

        $data['code'] = $this->generateCode($data['name']);
        $data['created_by'] = $createdBy;

        return Brand::create($data);
    }

    protected function generateCode(string $name): string
    {
        $words = preg_split('/\s+/', trim(preg_replace('/[^A-Za-z\s]/', '', $name)));
        $words = array_values(array_filter($words, fn ($word) => $word !== ''));

        if (count($words) >= 2) {
            $base = collect($words)
                ->take(4)
                ->map(fn ($word) => strtoupper($word[0]))
                ->implode('');
        } elseif (count($words) === 1) {
            $base = strtoupper(substr($words[0], 0, 4));
        } else {
            $base = 'BRD';
        }

        $base = substr($base, 0, 16) ?: 'BRD';

        $code = $base;
        $suffix = 1;

        while (Brand::withTrashed()->where('code', $code)->exists()) {
            $suffix++;
            $code = $base.$suffix;
        }

        return $code;
    }
}

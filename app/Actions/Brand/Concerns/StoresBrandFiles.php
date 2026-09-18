<?php

namespace App\Actions\Brand\Concerns;

use App\Actions\Concerns\StoresImagesAsPng;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait StoresBrandFiles
{
    use StoresImagesAsPng;

    /**
     * @var array<string, string>
     */
    protected array $brandFileMap = [
        'logo' => 'logo_path',
        'qris' => 'qris_path',
        'ttd' => 'ttd_path',
        'stempel' => 'stempel_path',
        'materai' => 'materai_path',
    ];

    protected function storeBrandFiles(array $data, ?object $existingBrand = null): array
    {
        foreach ($this->brandFileMap as $field => $pathColumn) {
            $removeField = 'remove_'.$field;
            $newFile = $data[$field] ?? null;
            $hasNewFile = $newFile instanceof UploadedFile;
            $shouldRemove = ! empty($data[$removeField]);

            unset($data[$field], $data[$removeField]);

            if ($hasNewFile) {
                if ($existingBrand && $existingBrand->{$pathColumn}) {
                    Storage::disk('public')->delete($existingBrand->{$pathColumn});
                }

                $data[$pathColumn] = $this->storeImageAsPng($newFile, 'brands');
            } elseif ($shouldRemove && $existingBrand && $existingBrand->{$pathColumn}) {
                Storage::disk('public')->delete($existingBrand->{$pathColumn});

                $data[$pathColumn] = null;
            }
        }

        return $data;
    }

    protected function normalizeRekening(array $data): array
    {
        $data['rekening_config'] = collect($data['rekening'] ?? [])
            ->filter(fn ($row) => filled($row['bank'] ?? null) || filled($row['norek'] ?? null) || filled($row['nama'] ?? null))
            ->map(fn ($row) => [
                'bank' => $row['bank'] ?? '',
                'norek' => $row['norek'] ?? '',
                'nama' => $row['nama'] ?? '',
            ])
            ->values()
            ->all();

        unset($data['rekening']);

        return $data;
    }
}

<?php

namespace App\Actions\Brand;

use App\Actions\Brand\Concerns\StoresBrandFiles;
use App\Models\Brand;
use App\Models\Invoice;

class UpdateBrandAction
{
    use StoresBrandFiles;

    public function execute(Brand $brand, array $data): Brand
    {
        $previousLogoPath = $brand->logo_path;

        $data = $this->storeBrandFiles($data, $brand);
        $data = $this->normalizeRekening($data);

        unset($data['code']);

        $brand->update($data);
        $brand->refresh();

        if (array_key_exists('logo_path', $data) && $data['logo_path'] !== $previousLogoPath) {
            $this->propagateLogoToInvoices($brand, $previousLogoPath, $data['logo_path']);
        }

        return $brand;
    }

    /**
     * Invoice/SPH menyimpan snapshot logo brand di kop_config saat dibuat, jadi
     * gantinya logo brand tidak otomatis kebawa. Selaraskan snapshot invoice yang
     * masih memakai logo lama supaya tidak tampil rusak (file lama sudah dihapus).
     */
    private function propagateLogoToInvoices(Brand $brand, ?string $previousLogoPath, ?string $newLogoPath): void
    {
        Invoice::where('brand_id', $brand->id)->each(function (Invoice $invoice) use ($previousLogoPath, $newLogoPath) {
            $kopConfig = $invoice->kop_config ?? [];

            if (($kopConfig['logo_path'] ?? null) !== $previousLogoPath) {
                return;
            }

            $kopConfig['logo_path'] = $newLogoPath;

            $invoice->update(['kop_config' => $kopConfig]);
        });
    }
}

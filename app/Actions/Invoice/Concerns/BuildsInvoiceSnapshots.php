<?php

namespace App\Actions\Invoice\Concerns;

use App\Actions\Concerns\StoresImagesAsPng;
use App\Models\Brand;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait BuildsInvoiceSnapshots
{
    use StoresImagesAsPng;

    /**
     * Snapshot kop surat brand ke invoice, plus gambar kop custom (opsional).
     */
    protected function buildKopConfig(Brand $brand, array $data, array $existing = []): array
    {
        $config = [
            'name' => $brand->name,
            'address' => $brand->address,
            'phone' => $brand->phone,
            'email' => $brand->email,
            'logo_path' => $brand->logo_path,
            'color_header' => $brand->color_header,
            'color_accent' => $brand->color_accent,
        ];

        if (! empty($existing['custom_image_path'])) {
            $config['custom_image_path'] = $existing['custom_image_path'];
        }

        if (! empty($data['kop_image']) && $data['kop_image'] instanceof UploadedFile) {

            if (! empty($existing['custom_image_path'])) {
                Storage::disk('public')->delete($existing['custom_image_path']);
            }

            $config['custom_image_path'] = $this->storeImageAsPng($data['kop_image'], 'invoices/kop');
        }

        return $config;
    }

    /**
     * Snapshot tanda tangan / stempel / e-materai brand ke invoice.
     */
    protected function buildSignConfig(Brand $brand): array
    {
        return [
            'ttd_path' => $brand->ttd_path,
            'stempel_path' => $brand->stempel_path,
            'materai_path' => $brand->materai_path,
            'ttd_nama' => $brand->ttd_nama,
            'ttd_jabatan' => $brand->ttd_jabatan,
        ];
    }

    /**
     * Path QRIS invoice: pakai upload khusus invoice ini, atau fallback ke QRIS brand.
     */
    protected function resolveQrisPath(Brand $brand, array $data, ?string $existing = null): ?string
    {
        if (! empty($data['qris_image']) && $data['qris_image'] instanceof UploadedFile) {

            if ($existing) {
                Storage::disk('public')->delete($existing);
            }

            return $this->storeImageAsPng($data['qris_image'], 'invoices/qris');
        }

        return $existing ?: $brand->qris_path;
    }

    /**
     * Bangun konfigurasi SPH dari input form.
     */
    protected function buildSphConfig(array $data): ?array
    {
        if (empty($data['sph_aktif'])) {
            return null;
        }

        return [
            'aktif' => true,
            'perihal' => $data['sph_perihal'] ?? '',
            'narasi' => $data['sph_narasi'] ?? '',
            'tempat_tanggal' => $data['sph_tempat_tanggal'] ?? '',
            'pengirim' => $data['sph_pengirim'] ?? '',
        ];
    }
}

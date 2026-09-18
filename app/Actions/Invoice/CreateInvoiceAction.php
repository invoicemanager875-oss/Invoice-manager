<?php

namespace App\Actions\Invoice;

use App\Actions\Invoice\Concerns\BuildsInvoiceSnapshots;
use App\Models\Brand;
use App\Models\Invoice;
use App\Services\InvoiceCalculationService;
use App\Services\InvoiceNumberService;
use Illuminate\Support\Facades\DB;

class CreateInvoiceAction
{
    use BuildsInvoiceSnapshots;

    public function __construct(
        protected InvoiceNumberService $numberService,
        protected InvoiceCalculationService $calculationService,
    ) {}

    public function execute(array $data, int $createdBy): Invoice
    {
        return DB::transaction(function () use ($data, $createdBy) {

            $brand = Brand::findOrFail($data['brand_id']);

            $number = $this->numberService->generate($brand->id);

            $invoice = Invoice::create([
                'brand_id' => $brand->id,
                'created_by' => $createdBy,
                'nomor' => $number['nomor'],
                'nomor_urut' => $number['nomor_urut'],
                'tahun' => $number['tahun'],
                'bulan' => $number['bulan'],
                'klien' => $data['klien'],
                'alamat' => $data['alamat'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'tanggal' => $data['tanggal'],
                'jatuh_tempo' => $data['jatuh_tempo'] ?? null,
                'desain_tema' => $brand->default_desain_tema ?? 'classic',
                'kop_config' => $this->buildKopConfig($brand, $data),
                'sign_config' => $this->buildSignConfig($brand),
                'rekening_config' => $brand->rekening_config ?? [],
                'qris_path' => $this->resolveQrisPath($brand, $data),
                'sph_config' => $this->buildSphConfig($data),
                'diskon_persen' => $data['diskon_persen'] ?? 0,
                'ppn_persen' => $data['ppn_persen'] ?? 0,
                'catatan' => $data['catatan'] ?? null,
                'termin_show_pct' => $data['termin_show_pct'] ?? true,
            ]);

            foreach ($data['items'] as $index => $item) {
                $itemModel = $invoice->items()->create([
                    'type' => $item['type'],
                    'deskripsi' => $item['deskripsi'],
                    'volume' => $item['volume'],
                    'satuan' => $item['satuan'] ?? null,
                    'harga_satuan' => $item['harga_satuan'],
                    'urutan' => $item['urutan'] ?? $index + 1,
                ]);

                if ($item['type'] === 'group') {
                    foreach ($item['sub_items'] ?? [] as $subIndex => $sub) {
                        $itemModel->subItems()->create([
                            'invoice_id' => $invoice->id,
                            'type' => 'single',
                            'deskripsi' => $sub['deskripsi'],
                            'volume' => $sub['volume'],
                            'satuan' => $sub['satuan'] ?? null,
                            'harga_satuan' => $sub['harga_satuan'],
                            'urutan' => $subIndex + 1,
                        ]);
                    }
                }
            }

            foreach ($data['terms'] ?? [] as $index => $term) {
                $isLunas = $term['is_lunas'] ?? false;

                $invoice->terms()->create([
                    'label' => $term['label'],
                    'catatan' => $term['catatan'] ?? null,
                    'persen' => $term['persen'],
                    'nominal' => $term['nominal'] ?? 0,
                    'is_lunas' => $isLunas,
                    'tanggal_lunas' => $isLunas ? ($term['tanggal_lunas'] ?? now()->toDateString()) : null,
                    'urutan' => $index + 1,
                ]);
            }

            return $this->calculationService->recalculateInvoice($invoice);
        });
    }
}

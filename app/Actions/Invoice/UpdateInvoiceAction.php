<?php

namespace App\Actions\Invoice;

use App\Actions\Invoice\Concerns\BuildsInvoiceSnapshots;
use App\Models\Invoice;
use App\Services\InvoiceCalculationService;
use Illuminate\Support\Facades\DB;

class UpdateInvoiceAction
{
    use BuildsInvoiceSnapshots;

    public function __construct(
        protected InvoiceCalculationService $calculationService,
    ) {}

    public function execute(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {

            $brand = $invoice->brand;

            $invoice->update([
                'klien' => $data['klien'],
                'alamat' => $data['alamat'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'tanggal' => $data['tanggal'],
                'jatuh_tempo' => $data['jatuh_tempo'] ?? null,
                'desain_tema' => $data['desain_tema'] ?? $invoice->desain_tema ?? 'classic',
                'kop_config' => $this->buildKopConfig($brand, $data, $invoice->kop_config ?? []),
                'sign_config' => $this->buildSignConfig($brand),
                'rekening_config' => $brand->rekening_config ?? [],
                'qris_path' => $this->resolveQrisPath($brand, $data, $invoice->qris_path),
                'sph_config' => $this->buildSphConfig($data),
                'diskon_persen' => $data['diskon_persen'] ?? 0,
                'ppn_persen' => $data['ppn_persen'] ?? 0,
                'catatan' => $data['catatan'] ?? null,
                'termin_show_pct' => $data['termin_show_pct'] ?? $invoice->termin_show_pct,
            ]);

            $this->syncItems($invoice, $data['items']);
            $this->syncTerms($invoice, $data['terms'] ?? []);

            return $this->calculationService->recalculateInvoice($invoice);
        });
    }

    protected function syncItems(Invoice $invoice, array $items): void
    {
        $keepIds = [];

        foreach ($items as $index => $item) {
            $itemModel = $invoice->items()->updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'type' => $item['type'],
                    'deskripsi' => $item['deskripsi'],
                    'volume' => $item['volume'],
                    'satuan' => $item['satuan'] ?? null,
                    'harga_satuan' => $item['harga_satuan'],
                    'urutan' => $item['urutan'] ?? $index + 1,
                ]
            );

            $keepIds[] = $itemModel->id;

            if ($item['type'] === 'group') {
                foreach ($item['sub_items'] ?? [] as $subIndex => $sub) {
                    $subModel = $itemModel->subItems()->updateOrCreate(
                        ['id' => $sub['id'] ?? null],
                        [
                            'invoice_id' => $invoice->id,
                            'type' => 'single',
                            'deskripsi' => $sub['deskripsi'],
                            'volume' => $sub['volume'],
                            'satuan' => $sub['satuan'] ?? null,
                            'harga_satuan' => $sub['harga_satuan'],
                            'urutan' => $subIndex + 1,
                        ]
                    );

                    $keepIds[] = $subModel->id;
                }
            }
        }

        $invoice->items()->whereNotIn('id', $keepIds ?: [0])->delete();
    }

    protected function syncTerms(Invoice $invoice, array $terms): void
    {
        $keepIds = [];

        foreach ($terms as $index => $term) {
            $isLunas = $term['is_lunas'] ?? false;

            $termModel = $invoice->terms()->updateOrCreate(
                ['id' => $term['id'] ?? null],
                [
                    'label' => $term['label'],
                    'catatan' => $term['catatan'] ?? null,
                    'persen' => $term['persen'],
                    'nominal' => $term['nominal'] ?? 0,
                    'is_lunas' => $isLunas,
                    'tanggal_lunas' => $isLunas ? ($term['tanggal_lunas'] ?? now()->toDateString()) : null,
                    'urutan' => $index + 1,
                ]
            );

            $keepIds[] = $termModel->id;
        }

        $invoice->terms()->whereNotIn('id', $keepIds)->delete();
    }
}

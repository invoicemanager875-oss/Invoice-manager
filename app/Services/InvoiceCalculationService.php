<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class InvoiceCalculationService
{
    /**
     * Hitung total satu item.
     */
    public function calculateItemTotal(array $item): float
    {
        $volume = (float) ($item['volume'] ?? 0);
        $harga = (float) ($item['harga_satuan'] ?? 0);

        return round($volume * $harga, 2);
    }

    /**
     * Hitung subtotal invoice.
     *
     * Aturan:
     * - Item biasa (single) dihitung langsung.
     * - Item child (punya parent_item_id) tidak dihitung langsung.
     * - Item group/paket dihitung dari total child-nya.
     */
    public function calculateSubtotal(Collection $items): float
    {
        $subtotal = 0;

        $topLevelItems = $items->whereNull('parent_item_id');

        foreach ($topLevelItems as $item) {

            if ($item->type === 'group') {

                $subtotal += $items
                    ->where('parent_item_id', $item->id)
                    ->sum('jumlah');

                continue;
            }

            // single & paket: harga per baris dihitung langsung dari volume x harga_satuan.
            $subtotal += (float) $item->jumlah;
        }

        return round($subtotal, 2);
    }

    /**
     * Hitung total invoice.
     */
    public function calculateTotal(
        float $subtotal,
        float $diskonPersen,
        float $ppnPersen
    ): float {

        $diskonPersen = max(0, min(100, $diskonPersen));
        $ppnPersen = max(0, min(100, $ppnPersen));

        return round(
            $subtotal
            * (1 - ($diskonPersen / 100))
            * (1 + ($ppnPersen / 100)),
            2
        );
    }

    /**
     * Hitung ulang nominal termin.
     *
     * Termin dengan persen > 0 dihitung otomatis dari persen x total (nominal
     * dari client diabaikan). Termin dengan persen = 0 dianggap mode custom —
     * nominal yang sudah tersimpan (hasil input manual) dibiarkan apa adanya.
     */
    public function calculateTerms(
        float $total,
        array $terms
    ): array {

        $persenTotal = collect($terms)->sum('persen');

        if ($persenTotal > 100.5) {

            throw ValidationException::withMessages([
                'terms' => [
                    'Total persentase termin tidak boleh melebihi 100%.',
                ],
            ]);
        }

        return collect($terms)
            ->map(function ($term) use ($total) {

                $persen = (float) ($term['persen'] ?? 0);

                if ($persen > 0) {
                    $term['nominal'] = round(
                        $total * ($persen / 100),
                        2
                    );
                }

                return $term;
            })
            ->toArray();
    }

    /**
     * Hitung ulang seluruh invoice.
     */
    public function recalculateInvoice(Invoice $invoice): Invoice
    {
        // Fresh query, not the cached `items` relation: callers may have just
        // created/deleted items on this same $invoice instance in-memory.
        $items = $invoice->items()->get();

        foreach ($items as $item) {

            $item->jumlah = $this->calculateItemTotal([
                'volume' => $item->volume,
                'harga_satuan' => $item->harga_satuan,
            ]);

            $item->save();
        }

        $subtotal = $this->calculateSubtotal(
            $invoice->items()->get()
        );

        $total = $this->calculateTotal(
            $subtotal,
            (float) $invoice->diskon_persen,
            (float) $invoice->ppn_persen
        );

        $invoice->subtotal = $subtotal;
        $invoice->total = $total;

        $invoice->save();

        $terms = $this->calculateTerms(
            $total,
            $invoice->terms()->get()->toArray()
        );

        foreach ($terms as $termData) {

            $invoice
                ->terms()
                ->where('id', $termData['id'])
                ->update([
                    'nominal' => $termData['nominal'],
                ]);
        }

        return $invoice->fresh([
            'items',
            'terms',
            'brand',
            'creator',
        ]);
    }
}

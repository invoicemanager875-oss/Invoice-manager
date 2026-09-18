<?php

namespace App\Http\Controllers;

use App\Actions\Invoice\CreateInvoiceAction;
use App\Actions\Invoice\DeleteInvoiceAction;
use App\Actions\Invoice\UpdateInvoiceAction;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Models\Brand;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTerm;
use App\Services\InvoiceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $user = auth()->user();

        $invoices = Invoice::with('brand')
            ->when(
                ! $user->hasRole('superadmin'),
                fn ($query) => $query->whereIn('brand_id', $user->ownedBrands()->pluck('id'))
            )
            ->when(
                $user->hasRole('admin') && $request->filled('brand_id'),
                fn ($query) => $query->where('brand_id', $request->input('brand_id'))
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where('status', $request->input('status'))
            )
            ->when(
                $request->input('jatuh_tempo') === 'terlambat',
                fn ($query) => $query->where('status', '!=', 'lunas')
                    ->whereNotNull('jatuh_tempo')
                    ->whereDate('jatuh_tempo', '<', now())
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $brands = $this->brandsForUser();

        return view('invoices.index', compact('invoices', 'brands'));
    }

    public function create()
    {
        $this->authorize('create', Invoice::class);

        $brands = $this->brandsForUser();

        return view('invoices.create', compact('brands'));
    }

    public function store(StoreInvoiceRequest $request, CreateInvoiceAction $action)
    {
        $brand = Brand::findOrFail($request->validated('brand_id'));

        $this->authorize('create', [Invoice::class, $brand]);

        $invoice = $action->execute($request->validated(), auth()->id());

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items.subItems', 'terms', 'brand', 'creator']);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->load(['items.subItems', 'terms']);

        $brands = $this->brandsForUser();

        return view('invoices.edit', compact('invoice', 'brands'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice, UpdateInvoiceAction $action)
    {
        $this->authorize('update', $invoice);

        $action->execute($invoice, $request->validated());

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice, DeleteInvoiceAction $action)
    {
        $this->authorize('delete', $invoice);

        $action->execute($invoice);

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice berhasil dihapus.');
    }

    public function print(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['items.subItems', 'terms', 'brand']);

        return view('invoices.print', compact('invoice'));
    }

    public function lock(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if (! $invoice->is_locked) {
            $invoice->update(['printed_at' => now()]);
        }

        return redirect()->route('invoices.print', ['invoice' => $invoice, 'auto' => 1]);
    }

    public function markLunas(Invoice $invoice)
    {
        // Status pembayaran boleh diubah kapan pun, termasuk setelah invoice dicetak/terkunci.
        $this->authorize('view', $invoice);

        $invoice->update([
            'status' => 'lunas',
            'tanggal_lunas' => now(),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice ditandai lunas.');
    }

    /**
     * Render invoice sebagai halaman cetak dari data form yang belum disimpan,
     * supaya user bisa melihat hasil akhirnya sebelum menekan "Simpan Invoice".
     */
    public function preview(Request $request, InvoiceCalculationService $calculationService)
    {
        $existingInvoice = $request->filled('invoice_id')
            ? Invoice::find($request->input('invoice_id'))
            : null;

        if ($existingInvoice) {
            $this->authorize('update', $existingInvoice);
        } else {
            $this->authorize('create', Invoice::class);
        }

        $data = $request->validate((new StoreInvoiceRequest)->rules());

        $brand = Brand::findOrFail($data['brand_id']);

        if (! $existingInvoice && ! $this->brandsForUser()->contains('id', $brand->id)) {
            abort(403);
        }

        $invoice = new Invoice([
            'brand_id' => $brand->id,
            'nomor' => $existingInvoice->nomor ?? 'PREVIEW',
            'klien' => $data['klien'],
            'alamat' => $data['alamat'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'tanggal' => $data['tanggal'],
            'jatuh_tempo' => $data['jatuh_tempo'] ?? null,
            'desain_tema' => $existingInvoice->desain_tema ?? $brand->default_desain_tema ?? 'classic',
            'diskon_persen' => $data['diskon_persen'] ?? 0,
            'ppn_persen' => $data['ppn_persen'] ?? 0,
            'catatan' => $data['catatan'] ?? null,
            'termin_show_pct' => $data['termin_show_pct'] ?? true,
            'status' => $existingInvoice->status ?? 'menunggu',
        ]);

        $invoice->kop_config = $this->previewKopConfig($brand, $request, $existingInvoice);
        $invoice->sign_config = [
            'ttd_path' => $brand->ttd_path,
            'stempel_path' => $brand->stempel_path,
            'materai_path' => $brand->materai_path,
            'ttd_nama' => $brand->ttd_nama,
            'ttd_jabatan' => $brand->ttd_jabatan,
        ];
        $invoice->rekening_config = $brand->rekening_config ?? [];
        $invoice->qris_path = $this->previewImageDataUri($request->file('qris_image'))
            ?? $existingInvoice->qris_path
            ?? $brand->qris_path;
        $invoice->sph_config = empty($data['sph_aktif']) ? null : [
            'aktif' => true,
            'perihal' => $data['sph_perihal'] ?? '',
            'narasi' => $data['sph_narasi'] ?? '',
            'tempat_tanggal' => $data['sph_tempat_tanggal'] ?? '',
            'pengirim' => $data['sph_pengirim'] ?? '',
        ];
        $invoice->setRelation('brand', $brand);

        $itemModels = collect();

        foreach ($data['items'] as $index => $item) {
            $subModels = collect();

            if ($item['type'] === 'group') {
                foreach ($item['sub_items'] ?? [] as $subIndex => $sub) {
                    $subModel = new InvoiceItem([
                        'type' => 'single',
                        'deskripsi' => $sub['deskripsi'],
                        'volume' => $sub['volume'],
                        'satuan' => $sub['satuan'] ?? null,
                        'harga_satuan' => $sub['harga_satuan'],
                        'urutan' => $subIndex + 1,
                    ]);
                    $subModel->jumlah = $calculationService->calculateItemTotal($sub);
                    $subModels->push($subModel);
                }
            }

            $itemModel = new InvoiceItem([
                'type' => $item['type'],
                'deskripsi' => $item['deskripsi'],
                'volume' => $item['volume'],
                'satuan' => $item['satuan'] ?? null,
                'harga_satuan' => $item['harga_satuan'],
                'urutan' => $item['urutan'] ?? $index + 1,
            ]);
            $itemModel->jumlah = $item['type'] === 'group'
                ? $subModels->sum('jumlah')
                : $calculationService->calculateItemTotal($item);
            $itemModel->setRelation('subItems', $subModels);

            $itemModels->push($itemModel);
        }

        $invoice->setRelation('items', $itemModels);

        $subtotal = (float) $itemModels->sum('jumlah');
        $total = $calculationService->calculateTotal(
            $subtotal,
            (float) ($data['diskon_persen'] ?? 0),
            (float) ($data['ppn_persen'] ?? 0)
        );

        $invoice->subtotal = $subtotal;
        $invoice->total = $total;

        $termModels = collect();

        foreach ($data['terms'] ?? [] as $index => $term) {
            $persen = (float) ($term['persen'] ?? 0);
            $isLunas = $term['is_lunas'] ?? false;

            $termModel = new InvoiceTerm([
                'label' => $term['label'],
                'catatan' => $term['catatan'] ?? null,
                'persen' => $persen,
                'nominal' => $persen > 0 ? round($total * $persen / 100, 2) : (float) ($term['nominal'] ?? 0),
                'is_lunas' => $isLunas,
                'tanggal_lunas' => $isLunas ? ($term['tanggal_lunas'] ?? now()->toDateString()) : null,
                'urutan' => $index + 1,
            ]);

            $termModels->push($termModel);
        }

        $invoice->setRelation('terms', $termModels);

        return view('invoices.print', compact('invoice'));
    }

    /**
     * Kop config untuk preview: file kop yang baru diupload dirender sebagai data URI
     * (tanpa ditulis ke disk) supaya preview tidak meninggalkan file sisa di storage.
     */
    private function previewKopConfig(Brand $brand, Request $request, ?Invoice $existingInvoice): array
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

        $uploadedDataUri = $this->previewImageDataUri($request->file('kop_image'));

        if ($uploadedDataUri) {
            $config['custom_image_path'] = $uploadedDataUri;
        } elseif (! empty($existingInvoice->kop_config['custom_image_path'] ?? null)) {
            $config['custom_image_path'] = $existingInvoice->kop_config['custom_image_path'];
        }

        return $config;
    }

    private function previewImageDataUri(?UploadedFile $file): ?string
    {
        if (! $file) {
            return null;
        }

        return 'data:'.$file->getMimeType().';base64,'.base64_encode(file_get_contents($file->getRealPath()));
    }

    private function brandsForUser(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return Brand::orderBy('name')->get();
        }

        return $user->ownedBrands()->orderBy('name')->get();
    }
}

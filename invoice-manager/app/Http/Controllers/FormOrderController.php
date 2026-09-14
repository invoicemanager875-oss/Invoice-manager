<?php

namespace App\Http\Controllers;

use App\Actions\FormOrder\CreateFormOrderAction;
use App\Actions\FormOrder\DeleteFormOrderAction;
use App\Actions\FormOrder\UpdateFormOrderAction;
use App\Http\Requests\StoreFormOrderRequest;
use App\Http\Requests\UpdateFormOrderRequest;
use App\Models\Brand;
use App\Models\FormOrder;
use App\Models\FormOrderTask;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FormOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', FormOrder::class);

        $user = auth()->user();

        $formOrders = FormOrder::with('brand', 'tasks')
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
                $request->input('deadline_status') === 'mendekati',
                fn ($query) => $query->where('status', '!=', 'selesai')
                    ->whereNotNull('deadline')
                    ->whereDate('deadline', '<=', now()->addDays(30))
            )
            ->latest('tanggal_order')
            ->paginate(15)
            ->withQueryString();

        $brands = $this->brandsForUser();

        return view('form-orders.index', compact('formOrders', 'brands'));
    }

    public function create()
    {
        $this->authorize('create', FormOrder::class);

        $brands = $this->brandsForUser();
        $invoices = $this->invoicesForLingkupPrefill($brands);
        $brandDraftersMap = $this->brandDraftersMap($brands);

        return view('form-orders.create', compact('brands', 'invoices', 'brandDraftersMap'));
    }

    public function store(StoreFormOrderRequest $request, CreateFormOrderAction $action)
    {
        $brand = Brand::findOrFail($request->validated('brand_id'));

        $this->authorize('create', [FormOrder::class, $brand]);

        $formOrder = $action->execute($request->validated(), auth()->id());

        return redirect()
            ->route('form-orders.show', $formOrder)
            ->with('success', 'Form Order berhasil dibuat.');
    }

    public function show(FormOrder $form_order)
    {
        $this->authorize('view', $form_order);

        $form_order->load('brand', 'creator', 'invoice', 'images', 'revisions', 'tasks.assignee');

        $taskDrafters = config('features.drafter_tasks')
            ? $form_order->brand->users()->role('drafter')->orderBy('name')->get(['users.id', 'users.name'])
            : collect();

        return view('form-orders.show', ['formOrder' => $form_order, 'taskDrafters' => $taskDrafters]);
    }

    public function edit(FormOrder $form_order)
    {
        $this->authorize('update', $form_order);

        $form_order->load('images', 'revisions', 'tasks');

        $brands = $this->brandsForUser();
        $invoices = $this->invoicesForLingkupPrefill($brands);
        $brandDraftersMap = $this->brandDraftersMap($brands);

        return view('form-orders.edit', [
            'formOrder' => $form_order,
            'brands' => $brands,
            'invoices' => $invoices,
            'brandDraftersMap' => $brandDraftersMap,
        ]);
    }

    public function update(UpdateFormOrderRequest $request, FormOrder $form_order, UpdateFormOrderAction $action)
    {
        $this->authorize('update', $form_order);

        $newBrand = Brand::findOrFail($request->validated('brand_id'));

        $this->authorize('create', [FormOrder::class, $newBrand]);

        $action->execute($form_order, $request->validated());

        return redirect()
            ->route('form-orders.show', $form_order)
            ->with('success', 'Form Order berhasil diperbarui.');
    }

    public function destroy(FormOrder $form_order, DeleteFormOrderAction $action)
    {
        $this->authorize('delete', $form_order);

        $action->execute($form_order);

        return redirect()
            ->route('form-orders.index')
            ->with('success', 'Form Order berhasil dihapus.');
    }

    public function pdf(FormOrder $form_order)
    {
        $this->authorize('view', $form_order);

        $form_order->load('brand', 'images', 'revisions');

        $filename = str_replace(['/', '\\'], '-', "FormOrder-{$form_order->nomor}").'.pdf';

        return Pdf::loadView('form-orders.pdf', ['formOrder' => $form_order, 'forPdf' => true])
            ->stream($filename);
    }

    public function assignTask(Request $request, FormOrder $form_order, FormOrderTask $task)
    {
        $this->authorize('update', $form_order);

        abort_unless($task->form_order_id === $form_order->id, 404);

        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $assignedTo = $validated['assigned_to'] ?? null;

        if ($assignedTo && ! $form_order->brand->users()->role('drafter')->whereKey($assignedTo)->exists()) {
            abort(422, 'Drafter tersebut tidak terdaftar pada brand ini.');
        }

        $task->update(['assigned_to' => $assignedTo]);

        return back()->with('success', 'PIC tugas berhasil diperbarui.');
    }

    public function finalize(FormOrder $form_order)
    {
        $this->authorize('update', $form_order);

        $form_order->update(['status' => 'selesai']);

        return redirect()
            ->route('form-orders.show', $form_order)
            ->with('success', 'Form Order ditandai selesai.');
    }

    /**
     * Daftar Form Order yang sudah selesai, dikelompokkan per brand, dengan
     * status "sudah dikirim ke klien" — meniru tab "Daftar Project Selesai"
     * di referensi basyid_pm1.
     */
    public function finished(Request $request)
    {
        $this->authorize('viewAny', FormOrder::class);

        $user = auth()->user();

        $formOrders = FormOrder::with('brand')
            ->where('status', 'selesai')
            ->when(
                ! $user->hasRole('superadmin'),
                fn ($query) => $query->whereIn('brand_id', $user->ownedBrands()->pluck('id'))
            )
            ->when(
                $user->hasRole('admin') && $request->filled('brand_id'),
                fn ($query) => $query->where('brand_id', $request->input('brand_id'))
            )
            ->when(
                $request->input('dikirim') === 'sudah',
                fn ($query) => $query->whereNotNull('dikirim_at')
            )
            ->when(
                $request->input('dikirim') === 'belum',
                fn ($query) => $query->whereNull('dikirim_at')
            )
            ->latest('tanggal_order')
            ->get();

        $groupedByBrand = $formOrders->groupBy(fn (FormOrder $fo) => $fo->brand->name ?? 'Tanpa Brand');

        $brands = $this->brandsForUser();

        return view('form-orders.finished', compact('groupedByBrand', 'brands'));
    }

    public function markDelivered(FormOrder $form_order)
    {
        $this->authorize('view', $form_order);

        abort_unless($form_order->status === 'selesai', 404);

        $form_order->update([
            'dikirim_at' => $form_order->dikirim_at ? null : now(),
        ]);

        return back()->with('success', $form_order->dikirim_at ? 'Ditandai sudah dikirim ke klien.' : 'Ditandai belum dikirim.');
    }

    private function brandsForUser(): Collection
    {
        $user = auth()->user();

        if ($user->hasRole('superadmin')) {
            return Brand::orderBy('name')->get();
        }

        return $user->ownedBrands()->orderBy('name')->get();
    }

    private function invoicesForLingkupPrefill(Collection $brands): Collection
    {
        return Invoice::with('items.subItems')
            ->whereIn('brand_id', $brands->pluck('id'))
            ->latest()
            ->get()
            ->map(function (Invoice $invoice) {
                $descriptions = [];

                foreach ($invoice->items as $item) {
                    if ($item->parent_item_id) {
                        continue;
                    }

                    $descriptions[] = $this->resolveDeskripsi($item);

                    if ($item->type === 'group') {
                        foreach ($item->subItems as $sub) {
                            $descriptions[] = $this->resolveDeskripsi($sub);
                        }
                    }
                }

                return [
                    'id' => $invoice->id,
                    'brand_id' => $invoice->brand_id,
                    'label' => $invoice->nomor.' — '.$invoice->klien,
                    'items' => $descriptions,
                ];
            })
            ->values();
    }

    /**
     * Peta brand_id => daftar drafter (id, name) yang boleh di-assign sebagai
     * PIC lingkup pekerjaan pada brand tersebut.
     */
    private function brandDraftersMap(Collection $brands): array
    {
        if (! config('features.drafter_tasks')) {
            return [];
        }

        return Brand::with(['users' => fn ($query) => $query->role('drafter')])
            ->whereIn('id', $brands->pluck('id'))
            ->get()
            ->mapWithKeys(fn (Brand $brand) => [
                (string) $brand->id => $brand->users->map(fn ($u) => ['id' => (string) $u->id, 'name' => $u->name])->values(),
            ])
            ->all();
    }

    /**
     * Item bertipe "paket" menyimpan nama paket + daftar fitur sebagai satu
     * deskripsi multi-baris ("Paket Hemat\n• fitur 1\n• fitur 2..."). Untuk
     * prefill lingkup pekerjaan, cukup ambil nama paketnya (baris pertama).
     */
    private function resolveDeskripsi($item): string
    {
        if ($item->type !== 'paket' || ! str_contains($item->deskripsi ?? '', "\n")) {
            return $item->deskripsi;
        }

        return trim(strtok($item->deskripsi, "\n"));
    }
}

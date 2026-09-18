<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJadwalPerencanaanRequest;
use App\Http\Requests\UpdateJadwalPerencanaanRequest;
use App\Models\Brand;
use App\Models\Invoice;
use App\Models\JadwalItem;
use App\Models\JadwalPerencanaan;
use App\Services\JadwalCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalPerencanaanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $brandIds = $isSuperAdmin ? null : $user->brands()->pluck('brands.id');

        $query = JadwalPerencanaan::query()
            ->when(! $isSuperAdmin, fn ($q) => $q->whereIn('brand_id', $brandIds))
            ->with(['brand', 'creator', 'items'])
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->brand_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('nama_proyek', 'like', "%{$search}%")
                        ->orWhere('lokasi', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        $jadwals = $query->paginate(10)->withQueryString();
        $brands = $isSuperAdmin ? Brand::orderBy('name')->get() : $user->brands()->orderBy('name')->get();

        return view('jadwal.index', [
            'jadwals' => $jadwals,
            'brands' => $brands,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', JadwalPerencanaan::class);

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $brands = $isSuperAdmin ? Brand::orderBy('name')->get() : $user->brands()->orderBy('name')->get();

        $invoices = Invoice::query()
            ->when(! $isSuperAdmin, fn ($q) => $q->whereIn('brand_id', $user->brands()->pluck('brands.id')))
            ->latest('id')
            ->limit(50)
            ->get();

        return view('jadwal.create', [
            'brands' => $brands,
            'invoices' => $invoices,
        ]);
    }

    public function store(StoreJadwalPerencanaanRequest $request, JadwalCalculationService $calc)
    {
        $validated = $request->validated();
        $user = auth()->user();

        $tanggalSelesai = $calc->calculateTanggalSelesai($validated['tanggal_mulai'], (int) $validated['durasi_hari']);

        $jadwal = DB::transaction(function () use ($validated, $user, $tanggalSelesai, $calc) {
            $jadwal = JadwalPerencanaan::create([
                'brand_id' => $validated['brand_id'],
                'invoice_id' => $validated['invoice_id'] ?? null,
                'created_by' => $user->id,
                'nama_proyek' => $validated['nama_proyek'],
                'lokasi' => $validated['lokasi'] ?? null,
                'durasi_hari' => (int) $validated['durasi_hari'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $tanggalSelesai->toDateString(),
                'status' => 'draft',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $index => $itemData) {
                    $durasi = max(1, ((int) $itemData['hari_selesai'] - (int) $itemData['hari_mulai']) + 1);
                    $distribusi = $calc->calculateDistribusiItem(
                        (float) $itemData['bobot'],
                        (int) $itemData['hari_mulai'],
                        (int) $itemData['hari_selesai']
                    );

                    JadwalItem::create([
                        'jadwal_perencanaan_id' => $jadwal->id,
                        'urutan' => $index + 1,
                        'nama_item' => $itemData['nama_item'],
                        'bobot' => (float) $itemData['bobot'],
                        'hari_mulai' => (int) $itemData['hari_mulai'],
                        'hari_selesai' => (int) $itemData['hari_selesai'],
                        'durasi' => $durasi,
                        'distribusi' => $distribusi,
                    ]);
                }
            }

            return $jadwal;
        });

        return redirect()->route('jadwal-perencanaan.show', $jadwal)->with('success', 'Jadwal perencanaan berhasil dibuat.');
    }

    public function show(JadwalPerencanaan $jadwal_perencanaan, JadwalCalculationService $calc)
    {
        $this->authorize('view', $jadwal_perencanaan);

        $jadwal_perencanaan->load(['brand', 'invoice', 'creator', 'items']);
        $kurvaSData = $calc->calculateKurvaS($jadwal_perencanaan);

        return view('jadwal.show', [
            'jadwal' => $jadwal_perencanaan,
            'kurvaSData' => $kurvaSData,
        ]);
    }

    public function edit(JadwalPerencanaan $jadwal_perencanaan)
    {
        $this->authorize('update', $jadwal_perencanaan);

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $brands = $isSuperAdmin ? Brand::orderBy('name')->get() : $user->brands()->orderBy('name')->get();

        $invoices = Invoice::query()
            ->when(! $isSuperAdmin, fn ($q) => $q->whereIn('brand_id', $user->brands()->pluck('brands.id')))
            ->latest('id')
            ->limit(50)
            ->get();

        $jadwal_perencanaan->load('items');

        return view('jadwal.edit', [
            'jadwal' => $jadwal_perencanaan,
            'brands' => $brands,
            'invoices' => $invoices,
        ]);
    }

    public function update(UpdateJadwalPerencanaanRequest $request, JadwalPerencanaan $jadwal_perencanaan, JadwalCalculationService $calc)
    {
        $validated = $request->validated();
        $tanggalSelesai = $calc->calculateTanggalSelesai($validated['tanggal_mulai'], (int) $validated['durasi_hari']);

        DB::transaction(function () use ($jadwal_perencanaan, $validated, $tanggalSelesai, $calc) {
            $jadwal_perencanaan->update([
                'brand_id' => $validated['brand_id'],
                'invoice_id' => $validated['invoice_id'] ?? null,
                'nama_proyek' => $validated['nama_proyek'],
                'lokasi' => $validated['lokasi'] ?? null,
                'durasi_hari' => (int) $validated['durasi_hari'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $tanggalSelesai->toDateString(),
                'status' => $validated['status'] ?? $jadwal_perencanaan->status,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $jadwal_perencanaan->items()->delete();

            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $index => $itemData) {
                    $durasi = max(1, ((int) $itemData['hari_selesai'] - (int) $itemData['hari_mulai']) + 1);
                    $distribusi = $calc->calculateDistribusiItem(
                        (float) $itemData['bobot'],
                        (int) $itemData['hari_mulai'],
                        (int) $itemData['hari_selesai']
                    );

                    JadwalItem::create([
                        'jadwal_perencanaan_id' => $jadwal_perencanaan->id,
                        'urutan' => $index + 1,
                        'nama_item' => $itemData['nama_item'],
                        'bobot' => (float) $itemData['bobot'],
                        'hari_mulai' => (int) $itemData['hari_mulai'],
                        'hari_selesai' => (int) $itemData['hari_selesai'],
                        'durasi' => $durasi,
                        'distribusi' => $distribusi,
                    ]);
                }
            }
        });

        return redirect()->route('jadwal-perencanaan.show', $jadwal_perencanaan)->with('success', 'Jadwal perencanaan berhasil diperbarui.');
    }

    public function destroy(JadwalPerencanaan $jadwal_perencanaan)
    {
        $this->authorize('delete', $jadwal_perencanaan);

        $jadwal_perencanaan->delete();

        return redirect()->route('jadwal-perencanaan.index')->with('success', 'Jadwal perencanaan berhasil dihapus.');
    }

    public function publish(JadwalPerencanaan $jadwal_perencanaan)
    {
        $this->authorize('update', $jadwal_perencanaan);

        $jadwal_perencanaan->load('items');
        $total = (float) $jadwal_perencanaan->items->sum('bobot');

        if (abs($total - 100.00) > 0.01) {
            return back()->with('error', sprintf('Total bobot item pekerjaan adalah %.2f%%, harus tepat 100.00%%.', $total));
        }

        $jadwal_perencanaan->update(['status' => 'published']);

        return back()->with('success', 'Jadwal berhasil dipublikasikan.');
    }

    public function pdf(JadwalPerencanaan $jadwal_perencanaan, JadwalCalculationService $calc)
    {
        $this->authorize('exportPdf', $jadwal_perencanaan);

        $jadwal_perencanaan->load(['brand', 'invoice', 'creator', 'items']);
        $kurvaSData = $calc->calculateKurvaS($jadwal_perencanaan);

        $filename = 'KurvaS-'.str_replace(['/', '\\', ' '], '-', $jadwal_perencanaan->nama_proyek).'.pdf';

        return Pdf::loadView('jadwal.pdf', [
            'jadwal' => $jadwal_perencanaan,
            'kurvaSData' => $kurvaSData,
        ])
            ->setPaper('a4', 'landscape')
            ->stream($filename);
    }
}

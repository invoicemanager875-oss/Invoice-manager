<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Brand;
use App\Models\Contract;
use App\Models\ContractScope;
use App\Models\ContractTerm;
use App\Models\Invoice;
use App\Services\ContractNumberService;
use App\Services\ContractService;
use App\Support\Terbilang;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $brandIds = $isSuperAdmin ? null : $user->brands()->pluck('brands.id');

        $query = Contract::query()
            ->when(! $isSuperAdmin, fn ($q) => $q->whereIn('brand_id', $brandIds))
            ->with(['brand', 'creator'])
            ->when($request->filled('brand_id'), fn ($q) => $q->where('brand_id', $request->brand_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('nomor', 'like', "%{$search}%")
                        ->orWhere('judul_kontrak', 'like', "%{$search}%")
                        ->orWhere('pihak_kedua_nama', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        $contracts = $query->paginate(10)->withQueryString();
        $brands = $isSuperAdmin ? Brand::orderBy('name')->get() : $user->brands()->orderBy('name')->get();

        return view('contracts.index', [
            'contracts' => $contracts,
            'brands' => $brands,
        ]);
    }

    public function create(Request $request, ContractService $contractService)
    {
        $this->authorize('create', Contract::class);

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $brands = $isSuperAdmin ? Brand::orderBy('name')->get() : $user->brands()->orderBy('name')->get();

        $invoices = Invoice::query()
            ->when(! $isSuperAdmin, fn ($q) => $q->whereIn('brand_id', $user->brands()->pluck('brands.id')))
            ->latest('id')
            ->limit(50)
            ->get();

        $initialData = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::find($request->invoice_id);
            if ($invoice && ($isSuperAdmin || $user->brands()->whereKey($invoice->brand_id)->exists())) {
                $initialData = $contractService->autoPopulateFromInvoice($invoice);
            }
        }

        return view('contracts.create', [
            'brands' => $brands,
            'invoices' => $invoices,
            'initialData' => $initialData,
        ]);
    }

    public function store(StoreContractRequest $request, ContractNumberService $numberService, ContractService $contractService)
    {
        $validated = $request->validated();
        $user = auth()->user();
        $brand = Brand::findOrFail($validated['brand_id']);

        $tanggalKontrak = Carbon::parse($validated['tanggal_kontrak']);
        $nilaiKontrak = (float) $validated['nilai_kontrak'];
        $nilaiTerbilang = ! empty($validated['nilai_terbilang']) ? $validated['nilai_terbilang'] : Terbilang::make($nilaiKontrak);

        $contract = DB::transaction(function () use ($validated, $user, $brand, $tanggalKontrak, $nilaiKontrak, $nilaiTerbilang, $numberService, $contractService) {
            $numberData = $numberService->generate($brand->id, $tanggalKontrak->year);

            $narasi = $validated['narasi'] ?? null;
            if (empty($narasi)) {
                $narasi = $contractService->generateAutoNarasi([
                    'tanggal_kontrak' => $validated['tanggal_kontrak'],
                    'brand_name' => $brand->name,
                    'pihak_pertama_perusahaan' => $validated['pihak_pertama_perusahaan'],
                    'pihak_pertama_alamat' => $validated['pihak_pertama_alamat'],
                    'pihak_pertama_telepon' => $validated['pihak_pertama_telepon'],
                    'pihak_kedua_nama' => $validated['pihak_kedua_nama'],
                    'pihak_kedua_alamat' => $validated['pihak_kedua_alamat'],
                    'pihak_kedua_telepon' => $validated['pihak_kedua_telepon'],
                    'scopes' => $validated['scopes'] ?? [],
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'tanggal_selesai' => $validated['tanggal_selesai'],
                    'nilai_kontrak' => $nilaiKontrak,
                    'nilai_terbilang' => $nilaiTerbilang,
                    'terms' => $validated['terms'] ?? [],
                    'rekening_config' => $brand->rekening_config ?? [],
                    'catatan' => $validated['catatan'] ?? '',
                ]);
            }

            $contract = Contract::create([
                'brand_id' => $brand->id,
                'invoice_id' => $validated['invoice_id'] ?? null,
                'created_by' => $user->id,
                'nomor' => $numberData['nomor'],
                'nomor_urut' => $numberData['nomor_urut'],
                'tahun' => $numberData['tahun'],
                'judul_kontrak' => $validated['judul_kontrak'],
                'tanggal_kontrak' => $validated['tanggal_kontrak'],
                'pihak_pertama_nama' => $validated['pihak_pertama_nama'],
                'pihak_pertama_jabatan' => $validated['pihak_pertama_jabatan'],
                'pihak_pertama_perusahaan' => $validated['pihak_pertama_perusahaan'],
                'pihak_pertama_alamat' => $validated['pihak_pertama_alamat'],
                'pihak_pertama_telepon' => $validated['pihak_pertama_telepon'] ?? null,
                'pihak_kedua_nama' => $validated['pihak_kedua_nama'],
                'pihak_kedua_identitas' => $validated['pihak_kedua_identitas'] ?? null,
                'pihak_kedua_perusahaan' => $validated['pihak_kedua_perusahaan'] ?? null,
                'pihak_kedua_alamat' => $validated['pihak_kedua_alamat'],
                'pihak_kedua_telepon' => $validated['pihak_kedua_telepon'],
                'pihak_kedua_email' => $validated['pihak_kedua_email'] ?? null,
                'nilai_kontrak' => $nilaiKontrak,
                'nilai_terbilang' => $nilaiTerbilang,
                'durasi_hari' => (int) $validated['durasi_hari'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'narasi' => $narasi,
                'rekening_config' => $brand->rekening_config ?? [],
                'status' => 'draft',
                'catatan' => $validated['catatan'] ?? null,
            ]);

            if (! empty($validated['scopes'])) {
                foreach ($validated['scopes'] as $index => $scope) {
                    ContractScope::create([
                        'contract_id' => $contract->id,
                        'urutan' => $index + 1,
                        'nama_paket' => $scope['nama_paket'],
                        'deskripsi' => $scope['deskripsi'] ?? null,
                        'nominal' => (float) ($scope['nominal'] ?? 0),
                    ]);
                }
            }

            if (! empty($validated['terms'])) {
                foreach ($validated['terms'] as $index => $term) {
                    ContractTerm::create([
                        'contract_id' => $contract->id,
                        'termin_ke' => $index + 1,
                        'judul' => $term['judul'],
                        'persentase' => (float) $term['persentase'],
                        'nominal' => (float) $term['nominal'],
                        'syarat_pencairan' => $term['syarat_pencairan'] ?? 'Sesuai kesepakatan',
                        'status' => 'pending',
                    ]);
                }
            }

            return $contract;
        });

        return redirect()->route('contracts.show', $contract)->with('success', "Kontrak {$contract->nomor} berhasil dibuat.");
    }

    public function show(Contract $contract)
    {
        $this->authorize('view', $contract);

        $contract->load(['brand', 'invoice', 'creator', 'scopes', 'terms']);

        return view('contracts.show', [
            'contract' => $contract,
        ]);
    }

    public function edit(Contract $contract)
    {
        $this->authorize('update', $contract);

        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('superadmin');
        $brands = $isSuperAdmin ? Brand::orderBy('name')->get() : $user->brands()->orderBy('name')->get();

        $invoices = Invoice::query()
            ->when(! $isSuperAdmin, fn ($q) => $q->whereIn('brand_id', $user->brands()->pluck('brands.id')))
            ->latest('id')
            ->limit(50)
            ->get();

        $contract->load(['scopes', 'terms']);

        return view('contracts.edit', [
            'contract' => $contract,
            'brands' => $brands,
            'invoices' => $invoices,
        ]);
    }

    public function update(UpdateContractRequest $request, Contract $contract)
    {
        $validated = $request->validated();
        $nilaiKontrak = (float) $validated['nilai_kontrak'];
        $nilaiTerbilang = ! empty($validated['nilai_terbilang']) ? $validated['nilai_terbilang'] : Terbilang::make($nilaiKontrak);

        DB::transaction(function () use ($contract, $validated, $nilaiKontrak, $nilaiTerbilang) {
            $contract->update([
                'brand_id' => $validated['brand_id'],
                'invoice_id' => $validated['invoice_id'] ?? null,
                'judul_kontrak' => $validated['judul_kontrak'],
                'tanggal_kontrak' => $validated['tanggal_kontrak'],
                'pihak_pertama_nama' => $validated['pihak_pertama_nama'],
                'pihak_pertama_jabatan' => $validated['pihak_pertama_jabatan'],
                'pihak_pertama_perusahaan' => $validated['pihak_pertama_perusahaan'],
                'pihak_pertama_alamat' => $validated['pihak_pertama_alamat'],
                'pihak_pertama_telepon' => $validated['pihak_pertama_telepon'] ?? null,
                'pihak_kedua_nama' => $validated['pihak_kedua_nama'],
                'pihak_kedua_identitas' => $validated['pihak_kedua_identitas'] ?? null,
                'pihak_kedua_perusahaan' => $validated['pihak_kedua_perusahaan'] ?? null,
                'pihak_kedua_alamat' => $validated['pihak_kedua_alamat'],
                'pihak_kedua_telepon' => $validated['pihak_kedua_telepon'],
                'pihak_kedua_email' => $validated['pihak_kedua_email'] ?? null,
                'nilai_kontrak' => $nilaiKontrak,
                'nilai_terbilang' => $nilaiTerbilang,
                'durasi_hari' => (int) $validated['durasi_hari'],
                'tanggal_mulai' => $validated['tanggal_mulai'],
                'tanggal_selesai' => $validated['tanggal_selesai'],
                'narasi' => $validated['narasi'] ?? $contract->narasi,
                'catatan' => $validated['catatan'] ?? null,
            ]);

            $contract->scopes()->delete();
            if (! empty($validated['scopes'])) {
                foreach ($validated['scopes'] as $index => $scope) {
                    ContractScope::create([
                        'contract_id' => $contract->id,
                        'urutan' => $index + 1,
                        'nama_paket' => $scope['nama_paket'],
                        'deskripsi' => $scope['deskripsi'] ?? null,
                        'nominal' => (float) ($scope['nominal'] ?? 0),
                    ]);
                }
            }

            $contract->terms()->delete();
            if (! empty($validated['terms'])) {
                foreach ($validated['terms'] as $index => $term) {
                    ContractTerm::create([
                        'contract_id' => $contract->id,
                        'termin_ke' => $index + 1,
                        'judul' => $term['judul'],
                        'persentase' => (float) $term['persentase'],
                        'nominal' => (float) $term['nominal'],
                        'syarat_pencairan' => $term['syarat_pencairan'] ?? 'Sesuai kesepakatan',
                        'status' => 'pending',
                    ]);
                }
            }
        });

        return redirect()->route('contracts.show', $contract)->with('success', 'Kontrak berhasil diperbarui.');
    }

    public function finalize(Contract $contract)
    {
        $this->authorize('finalize', $contract);

        $contract->update(['status' => 'final']);

        return redirect()->route('contracts.show', $contract)->with('success', 'Kontrak berhasil difinalisasi.');
    }

    public function destroy(Contract $contract)
    {
        $this->authorize('delete', $contract);

        $contract->delete();

        return redirect()->route('contracts.index')->with('success', 'Kontrak berhasil dihapus.');
    }

    public function pdf(Contract $contract)
    {
        $this->authorize('exportPdf', $contract);

        $contract->load(['brand', 'invoice', 'creator', 'scopes', 'terms']);

        $filename = str_replace(['/', '\\'], '-', $contract->nomor).'.pdf';

        return Pdf::loadView('contracts.pdf', [
            'contract' => $contract,
        ])
            ->setPaper('a4', 'portrait')
            ->stream($filename);
    }

    public function generateNarasiAjax(Request $request, ContractService $contractService)
    {
        $narasi = $contractService->generateAutoNarasi($request->all());

        return response()->json(['narasi' => $narasi]);
    }
}

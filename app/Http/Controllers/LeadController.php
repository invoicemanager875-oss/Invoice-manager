<?php

namespace App\Http\Controllers;

use App\Actions\Lead\CreateLeadAction;
use App\Actions\Lead\DeleteLeadAction;
use App\Actions\Lead\UpdateLeadAction;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Brand;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Lead::class);

        $user = auth()->user();

        $leads = Lead::with('brand')
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
                $request->filled('sumber'),
                fn ($query) => $query->where('sumber', $request->input('sumber'))
            )
            ->latest('tanggal')
            ->paginate(15)
            ->withQueryString();

        $brands = $this->brandsForUser();

        return view('leads.index', compact('leads', 'brands'));
    }

    public function create()
    {
        $this->authorize('create', Lead::class);

        $brands = $this->brandsForUser();

        return view('leads.create', compact('brands'));
    }

    public function store(StoreLeadRequest $request, CreateLeadAction $action)
    {
        $brand = Brand::findOrFail($request->validated('brand_id'));

        $this->authorize('create', [Lead::class, $brand]);

        $lead = $action->execute($request->validated(), auth()->id());

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Leads berhasil ditambahkan.');
    }

    public function show(Lead $lead)
    {
        $this->authorize('view', $lead);

        $lead->load('brand', 'creator');

        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $this->authorize('update', $lead);

        $brands = $this->brandsForUser();

        return view('leads.edit', compact('lead', 'brands'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead, UpdateLeadAction $action)
    {
        $this->authorize('update', $lead);

        $newBrand = Brand::findOrFail($request->validated('brand_id'));

        $this->authorize('create', [Lead::class, $newBrand]);

        $action->execute($lead, $request->validated());

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Leads berhasil diperbarui.');
    }

    public function destroy(Lead $lead, DeleteLeadAction $action)
    {
        $this->authorize('delete', $lead);

        $action->execute($lead);

        return redirect()
            ->route('leads.index')
            ->with('success', 'Leads berhasil dihapus.');
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

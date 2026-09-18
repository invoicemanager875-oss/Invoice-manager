<?php

namespace App\Http\Controllers;

use App\Actions\Brand\CreateBrandAction;
use App\Actions\Brand\DeleteBrandAction;
use App\Actions\Brand\UpdateBrandAction;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Brand::class);

        $user = auth()->user();

        $brands = Brand::query()
            ->when(
                ! $user->hasRole('superadmin'),
                fn ($query) => $query->where('created_by', $user->id)
            )
            ->latest()
            ->paginate(10);

        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        $this->authorize('create', Brand::class);

        return view('brands.create');
    }

    public function store(
        StoreBrandRequest $request,
        CreateBrandAction $action
    ) {
        $this->authorize('create', Brand::class);

        $action->execute($request->validated(), auth()->id());

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand berhasil dibuat.');
    }

    public function show(Brand $brand)
    {
        $this->authorize('view', $brand);

        return view('brands.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        $this->authorize('update', $brand);

        return view('brands.edit', compact('brand'));
    }

    public function update(
        UpdateBrandRequest $request,
        Brand $brand,
        UpdateBrandAction $action
    ) {
        $this->authorize('update', $brand);

        $action->execute($brand, $request->validated());

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand berhasil diperbarui.');
    }

    public function destroy(
        Brand $brand,
        DeleteBrandAction $action
    ) {
        $this->authorize('delete', $brand);

        $action->execute($brand);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDrafterRequest;
use App\Http\Requests\UpdateDrafterRequest;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DrafterController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $drafters = User::role('drafter')->with('brands')->orderBy('name')->paginate(10);

        return view('drafters.index', compact('drafters'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $brands = Brand::orderBy('name')->get();

        return view('drafters.create', compact('brands'));
    }

    public function store(StoreDrafterRequest $request)
    {
        $this->authorize('create', User::class);

        $drafter = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'jobdesk' => $request->validated('jobdesk'),
        ]);

        $drafter->assignRole('drafter');
        $drafter->brands()->sync($request->validated('brand_ids') ?? []);

        return redirect()
            ->route('drafters.index')
            ->with('success', 'Akun drafter berhasil dibuat.');
    }

    public function edit(User $drafter)
    {
        $this->authorize('update', $drafter);

        $brands = Brand::orderBy('name')->get();
        $selectedBrandIds = $drafter->brands->pluck('id')->all();

        return view('drafters.edit', compact('drafter', 'brands', 'selectedBrandIds'));
    }

    public function update(UpdateDrafterRequest $request, User $drafter)
    {
        $this->authorize('update', $drafter);

        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'jobdesk' => $request->validated('jobdesk'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $drafter->update($data);
        $drafter->brands()->sync($request->validated('brand_ids') ?? []);

        return redirect()
            ->route('drafters.index')
            ->with('success', 'Akun drafter berhasil diperbarui.');
    }

    public function destroy(User $drafter)
    {
        $this->authorize('delete', $drafter);

        $drafter->delete();

        return redirect()
            ->route('drafters.index')
            ->with('success', 'Akun drafter berhasil dihapus.');
    }
}

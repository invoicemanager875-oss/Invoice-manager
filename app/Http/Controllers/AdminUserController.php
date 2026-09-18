<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdminUserRequest;
use App\Http\Requests\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $admins = User::role('admin')->orderBy('name')->paginate(10);

        return view('admin-users.index', compact('admins'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin-users.create');
    }

    public function store(StoreAdminUserRequest $request)
    {
        $this->authorize('create', User::class);

        $admin = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $admin->assignRole('admin');

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Akun admin berhasil dibuat.');
    }

    public function edit(User $admin_user)
    {
        $this->authorize('update', $admin_user);

        return view('admin-users.edit', ['adminUser' => $admin_user]);
    }

    public function update(UpdateAdminUserRequest $request, User $admin_user)
    {
        $this->authorize('update', $admin_user);

        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        $admin_user->update($data);

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Akun admin berhasil diperbarui.');
    }

    public function destroy(User $admin_user)
    {
        $this->authorize('delete', $admin_user);

        $admin_user->delete();

        return redirect()
            ->route('admin-users.index')
            ->with('success', 'Akun admin berhasil dihapus.');
    }
}

<?php

use App\Models\Brand;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Merombak sistem role: tambah 'superadmin', hapus 'brand_user' (diganti sepenuhnya),
     * lalu jadikan admin pertama yang ada sebagai superadmin & pemilik seluruh brand lama
     * yang belum punya pemilik (created_by).
     */
    public function up(): void
    {
        $superadmin = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'web',
        ]);

        // Admin pertama (berdasarkan role 'admin' yang sudah ada) dijadikan superadmin.
        // Kalau belum ada sama sekali, jatuhkan ke user paling awal.
        $firstAdminId = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'admin')
            ->where('model_has_roles.model_type', User::class)
            ->orderBy('model_has_roles.model_id')
            ->value('model_has_roles.model_id');

        $firstAdminId ??= User::query()->oldest('id')->value('id');

        if ($firstAdminId) {
            $firstAdmin = User::find($firstAdminId);

            if ($firstAdmin) {
                $firstAdmin->assignRole(['admin', 'superadmin']);
            }

            Brand::whereNull('created_by')->update(['created_by' => $firstAdminId]);
        }

        // Role 'brand_user' dihapus total, digantikan sepenuhnya oleh admin/superadmin.
        $brandUserRole = Role::where('name', 'brand_user')->where('guard_name', 'web')->first();

        if ($brandUserRole) {
            DB::table('model_has_roles')->where('role_id', $brandUserRole->id)->delete();
            DB::table('role_has_permissions')->where('role_id', $brandUserRole->id)->delete();
            $brandUserRole->delete();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Role::firstOrCreate([
            'name' => 'brand_user',
            'guard_name' => 'web',
        ]);

        Role::where('name', 'superadmin')->where('guard_name', 'web')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};

<?php

namespace App\Policies;

use App\Models\JadwalPerencanaan;
use App\Models\User;

class JadwalPerencanaanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, JadwalPerencanaan $jadwal): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->brands()->whereKey($jadwal->brand_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    public function update(User $user, JadwalPerencanaan $jadwal): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return $user->brands()->whereKey($jadwal->brand_id)->exists();
        }

        return false;
    }

    public function delete(User $user, JadwalPerencanaan $jadwal): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return $user->brands()->whereKey($jadwal->brand_id)->exists();
        }

        return false;
    }

    public function exportPdf(User $user, JadwalPerencanaan $jadwal): bool
    {
        return $this->view($user, $jadwal);
    }
}

<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Brand $brand): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->hasRole('admin') && $brand->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Brand $brand): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->hasRole('admin') && $brand->created_by === $user->id;
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $this->update($user, $brand);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Brand $brand): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Brand $brand): bool
    {
        return false;
    }
}

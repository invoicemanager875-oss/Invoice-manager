<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\FormOrder;
use App\Models\User;

class FormOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, FormOrder $formOrder): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->hasRole('admin') && $formOrder->brand?->created_by === $user->id;
    }

    public function create(User $user, ?Brand $brand = null): bool
    {
        if (! $user->hasRole('admin')) {
            return false;
        }

        if ($user->hasRole('superadmin') || $brand === null) {
            return true;
        }

        return $brand->created_by === $user->id;
    }

    public function update(User $user, FormOrder $formOrder): bool
    {
        if ($formOrder->is_locked) {
            return false;
        }

        return $this->view($user, $formOrder);
    }

    public function delete(User $user, FormOrder $formOrder): bool
    {
        if ($formOrder->is_locked) {
            return false;
        }

        return $this->view($user, $formOrder);
    }
}

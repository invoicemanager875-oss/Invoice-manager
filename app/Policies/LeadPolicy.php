<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Lead $lead): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->hasRole('admin') && $lead->brand?->created_by === $user->id;
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

    public function update(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->view($user, $lead);
    }
}

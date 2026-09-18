<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Contract $contract): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->brands()->whereKey($contract->brand_id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'admin']);
    }

    public function update(User $user, Contract $contract): bool
    {
        if (! $contract->isDraft()) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return $user->brands()->whereKey($contract->brand_id)->exists();
        }

        return false;
    }

    public function finalize(User $user, Contract $contract): bool
    {
        return $this->update($user, $contract);
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $this->update($user, $contract);
    }

    public function exportPdf(User $user, Contract $contract): bool
    {
        return $this->view($user, $contract);
    }
}

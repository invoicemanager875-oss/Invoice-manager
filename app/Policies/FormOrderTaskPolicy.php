<?php

namespace App\Policies;

use App\Models\FormOrderTask;
use App\Models\User;

class FormOrderTaskPolicy
{
    public function update(User $user, FormOrderTask $formOrderTask): bool
    {
        if ($formOrderTask->assigned_to === $user->id) {
            return true;
        }

        return $user->can('view', $formOrderTask->formOrder);
    }

    public function claim(User $user, FormOrderTask $formOrderTask): bool
    {
        if ($formOrderTask->assigned_to !== null || $formOrderTask->is_done) {
            return false;
        }

        if (! $user->hasRole('drafter')) {
            return false;
        }

        if ($formOrderTask->formOrder->is_locked) {
            return false;
        }

        return $user->brands()->whereKey($formOrderTask->formOrder->brand_id)->exists();
    }
}

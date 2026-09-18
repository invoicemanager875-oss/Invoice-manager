<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $user->hasRole('admin') && $invoice->brand?->created_by === $user->id;
    }

    /**
     * $brand dicek kalau tersedia (mis. saat submit form dengan brand_id tertentu).
     * Tanpa $brand, ini hanya cek apakah user boleh mengakses halaman "Buat Invoice" sama sekali.
     */
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

    public function viewReceipt(User $user, Invoice $invoice): bool
    {
        return $invoice->status === 'lunas'
            && $this->view($user, $invoice);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($invoice->status !== 'menunggu') {
            return false;
        }

        return $this->view($user, $invoice);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        if ($invoice->status !== 'menunggu') {
            return false;
        }

        return $this->view($user, $invoice);
    }
}

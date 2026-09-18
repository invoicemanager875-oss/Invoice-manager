<?php

namespace App\Actions\Invoice;

use App\Models\Invoice;

class DeleteInvoiceAction
{
    public function execute(Invoice $invoice): void
    {
        $invoice->delete();
    }
}

<?php

namespace App\Actions\FormOrder;

use App\Models\FormOrder;

class DeleteFormOrderAction
{
    public function execute(FormOrder $formOrder): void
    {
        $formOrder->delete();
    }
}

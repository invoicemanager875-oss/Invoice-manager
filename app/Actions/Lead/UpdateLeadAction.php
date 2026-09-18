<?php

namespace App\Actions\Lead;

use App\Models\Lead;

class UpdateLeadAction
{
    public function execute(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->refresh();
    }
}

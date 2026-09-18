<?php

namespace App\Actions\Lead;

use App\Models\Lead;

class CreateLeadAction
{
    public function execute(array $data, int $createdBy): Lead
    {
        return Lead::create([
            ...$data,
            'created_by' => $createdBy,
        ]);
    }
}

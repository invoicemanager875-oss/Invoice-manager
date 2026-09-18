<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractTerm extends Model
{
    protected $fillable = [
        'contract_id',
        'termin_ke',
        'judul',
        'persentase',
        'nominal',
        'syarat_pencairan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'termin_ke' => 'integer',
            'persentase' => 'decimal:2',
            'nominal' => 'decimal:2',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}

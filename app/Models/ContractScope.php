<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractScope extends Model
{
    protected $fillable = [
        'contract_id',
        'urutan',
        'nama_paket',
        'deskripsi',
        'nominal',
    ];

    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'nominal' => 'decimal:2',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }
}

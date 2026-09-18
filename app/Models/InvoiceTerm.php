<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTerm extends Model
{
    protected $fillable = [
        'invoice_id',
        'label',
        'catatan',
        'persen',
        'nominal',
        'is_lunas',
        'tanggal_lunas',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'persen' => 'decimal:2',
            'nominal' => 'decimal:2',
            'tanggal_lunas' => 'date',
            'is_lunas' => 'boolean',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

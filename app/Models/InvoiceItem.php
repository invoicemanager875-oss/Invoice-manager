<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'type',
        'parent_item_id',
        'deskripsi',
        'volume',
        'satuan',
        'harga_satuan',
        'jumlah',
        'urutan',
    ];

    protected function casts(): array
    {
        return [
            'volume' => 'decimal:2',
            'harga_satuan' => 'decimal:2',
            'jumlah' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_item_id');
    }

    public function subItems(): HasMany
    {
        return $this->hasMany(self::class, 'parent_item_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractCounter extends Model
{
    protected $fillable = [
        'brand_id',
        'tahun',
        'last_number',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'last_number' => 'integer',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
}

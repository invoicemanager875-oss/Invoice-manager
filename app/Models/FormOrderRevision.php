<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormOrderRevision extends Model
{
    protected $fillable = [
        'form_order_id',
        'catatan',
        'path',
        'urutan',
    ];

    public function formOrder(): BelongsTo
    {
        return $this->belongsTo(FormOrder::class);
    }
}

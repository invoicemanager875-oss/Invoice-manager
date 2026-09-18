<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormOrderTask extends Model
{
    protected $fillable = [
        'form_order_id',
        'assigned_to',
        'name',
        'urutan',
        'is_done',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function formOrder(): BelongsTo
    {
        return $this->belongsTo(FormOrder::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

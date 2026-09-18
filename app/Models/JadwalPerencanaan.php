<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalPerencanaan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'invoice_id',
        'created_by',
        'nama_proyek',
        'lokasi',
        'durasi_hari',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'durasi_hari' => 'integer',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(JadwalItem::class)->orderBy('urutan');
    }

    public function getTotalBobotAttribute(): float
    {
        return (float) $this->items->sum('bobot');
    }

    public function getIsBobotValidAttribute(): bool
    {
        return abs($this->total_bobot - 100.00) <= 0.01;
    }
}

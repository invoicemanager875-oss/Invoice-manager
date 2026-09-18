<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalItem extends Model
{
    protected $fillable = [
        'jadwal_perencanaan_id',
        'urutan',
        'nama_item',
        'bobot',
        'hari_mulai',
        'hari_selesai',
        'durasi',
        'distribusi',
    ];

    protected function casts(): array
    {
        return [
            'bobot' => 'decimal:2',
            'hari_mulai' => 'integer',
            'hari_selesai' => 'integer',
            'durasi' => 'integer',
            'distribusi' => 'array',
        ];
    }

    public function jadwalPerencanaan(): BelongsTo
    {
        return $this->belongsTo(JadwalPerencanaan::class);
    }
}

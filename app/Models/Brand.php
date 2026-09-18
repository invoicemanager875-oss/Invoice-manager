<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'created_by',
        'code',
        'npwp',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'qris_path',
        'rekening_config',
        'ttd_path',
        'stempel_path',
        'materai_path',
        'ttd_nama',
        'ttd_jabatan',
        'color_header',
        'color_accent',
        'default_desain_tema',
        'canva_link',
    ];

    protected function casts(): array
    {
        return [
            'rekening_config' => 'array',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function formOrders(): HasMany
    {
        return $this->hasMany(FormOrder::class);
    }

    public function jadwalPerencanaans(): HasMany
    {
        return $this->hasMany(JadwalPerencanaan::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function contractCounters(): HasMany
    {
        return $this->hasMany(ContractCounter::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'created_by',
        'nomor',
        'nomor_urut',
        'tahun',
        'bulan',
        'klien',
        'alamat',
        'phone',
        'email',
        'tanggal',
        'jatuh_tempo',
        'desain_tema',
        'kop_config',
        'diskon_persen',
        'ppn_persen',
        'status',
        'tanggal_lunas',
        'catatan',
        'sph_config',
        'sign_config',
        'rekening_config',
        'qris_path',
        'printed_at',
        'termin_show_pct',
    ];

    protected function casts(): array
    {
        return [
            'kop_config' => 'array',
            'sph_config' => 'array',
            'sign_config' => 'array',
            'rekening_config' => 'array',

            'tanggal' => 'date',
            'jatuh_tempo' => 'date',
            'tanggal_lunas' => 'date',
            'printed_at' => 'datetime',

            'subtotal' => 'decimal:2',
            'diskon_persen' => 'decimal:2',
            'ppn_persen' => 'decimal:2',
            'total' => 'decimal:2',

            'termin_show_pct' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function terms(): HasMany
    {
        return $this->hasMany(InvoiceTerm::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeLunas(Builder $query): Builder
    {
        return $query->where('status', 'lunas');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getIsLockedAttribute(): bool
    {
        return ! is_null($this->printed_at);
    }

    /**
     * Sisa hari sampai jatuh tempo (negatif kalau sudah lewat). Null kalau
     * belum diisi jatuh temponya.
     */
    public function getDaysUntilJatuhTempoAttribute(): ?int
    {
        if (! $this->jatuh_tempo) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->jatuh_tempo->copy()->startOfDay(), false);
    }

    /**
     * Apakah invoice ini lewat jatuh tempo dan belum lunas — sinyal utama
     * buat admin bahwa invoice ini perlu di-follow up ke klien.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'lunas'
            && $this->days_until_jatuh_tempo !== null
            && $this->days_until_jatuh_tempo < 0;
    }

    /**
     * Warna kop surat invoice ini. "brand" adalah tema dinamis (bukan preset
     * tetap di config) yang memakai color_header/color_accent milik brand,
     * disnapshot ke kop_config saat invoice dibuat — bukan warna brand
     * terkini, supaya invoice lama tidak berubah tampilan kalau warna brand
     * diganti nanti.
     */
    public function getThemeAttribute(): array
    {
        if ($this->desain_tema === 'brand') {
            $kop = $this->kop_config ?? [];

            return [
                'label' => 'Sesuai Warna Brand',
                'hdr' => $kop['color_header'] ?? config('invoice_themes.classic.hdr'),
                'acc' => $kop['color_accent'] ?? config('invoice_themes.classic.acc'),
            ];
        }

        if ($this->desain_tema === 'kop-gambar') {
            return [
                'label' => 'Upload Gambar Kop Surat',
                'hdr' => config('invoice_themes.classic.hdr'),
                'acc' => config('invoice_themes.classic.acc'),
            ];
        }

        return config('invoice_themes.'.$this->desain_tema) ?? config('invoice_themes.classic');
    }

    public function getDesainTemaLabelAttribute(): string
    {
        return $this->theme['label'] ?? 'Custom';
    }

    public function getNomorKwitansiAttribute(): string
    {
        return str_replace('INV/', 'KWT/', $this->nomor);
    }
}

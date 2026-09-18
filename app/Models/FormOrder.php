<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'created_by',
        'invoice_id',
        'nomor',
        'nomor_urut',
        'tahun',
        'bulan',
        'tanggal_order',
        'deadline',
        'nama_klien',
        'lokasi_project',
        'jenis_pekerjaan',
        'ukuran_bangunan',
        'arah_mata_angin',
        'share_location',
        'lingkup_pekerjaan',
        'catatan_klien',
        'status',
        'dikirim_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_order' => 'date',
            'deadline' => 'date',
            'lingkup_pekerjaan' => 'array',
            'dikirim_at' => 'datetime',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(FormOrderImage::class)->orderBy('urutan');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(FormOrderRevision::class)->orderBy('urutan');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(FormOrderTask::class)->orderBy('urutan');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getIsLockedAttribute(): bool
    {
        return $this->status === 'selesai';
    }

    public function getProgressAttribute(): int
    {
        $total = $this->tasks->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round($this->tasks->where('is_done', true)->count() / $total * 100);
    }

    /**
     * Sisa hari sampai deadline (negatif kalau sudah lewat). Null kalau
     * belum diisi deadline-nya.
     */
    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (! $this->deadline) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->deadline->copy()->startOfDay(), false);
    }

    /**
     * Klasifikasi status proyek terhadap deadline, meniru getStatus() di
     * referensi basyid_pm1: status manual "selesai" selalu menang, baru
     * setelah itu dibedakan berdasar sisa hari ke deadline.
     */
    public function getDeadlineStatusAttribute(): string
    {
        if ($this->status === 'selesai') {
            return 'selesai';
        }

        $days = $this->days_until_deadline;

        if ($days === null) {
            return 'berlangsung';
        }

        if ($days < 0) {
            return 'terlambat';
        }

        if ($days <= 30) {
            return 'mendekati';
        }

        return 'berlangsung';
    }
}

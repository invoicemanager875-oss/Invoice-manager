<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'brand_id',
        'invoice_id',
        'created_by',
        'nomor',
        'nomor_urut',
        'tahun',
        'judul_kontrak',
        'tanggal_kontrak',
        'pihak_pertama_nama',
        'pihak_pertama_jabatan',
        'pihak_pertama_perusahaan',
        'pihak_pertama_alamat',
        'pihak_pertama_telepon',
        'pihak_kedua_nama',
        'pihak_kedua_identitas',
        'pihak_kedua_perusahaan',
        'pihak_kedua_alamat',
        'pihak_kedua_telepon',
        'pihak_kedua_email',
        'nilai_kontrak',
        'nilai_terbilang',
        'durasi_hari',
        'tanggal_mulai',
        'tanggal_selesai',
        'narasi',
        'pasal_config',
        'rekening_config',
        'sign_config',
        'status',
        'signed_document_path',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_kontrak' => 'date',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'nilai_kontrak' => 'decimal:2',
            'durasi_hari' => 'integer',
            'nomor_urut' => 'integer',
            'tahun' => 'integer',
            'pasal_config' => 'array',
            'rekening_config' => 'array',
            'sign_config' => 'array',
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

    public function scopes(): HasMany
    {
        return $this->hasMany(ContractScope::class)->orderBy('urutan');
    }

    public function terms(): HasMany
    {
        return $this->hasMany(ContractTerm::class)->orderBy('termin_ke');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['final', 'signed', 'active', 'completed']);
    }
}

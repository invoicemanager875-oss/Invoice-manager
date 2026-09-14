<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormOrderImage extends Model
{
    /**
     * Ukuran tampil gambar (dipakai di halaman detail & PDF Form Order) —
     * angka di sini adalah lebar maksimum dalam pixel untuk render PDF.
     */
    public const SIZES = [
        'kecil' => ['label' => 'Kecil', 'pdf_max_width' => 220],
        'sedang' => ['label' => 'Sedang', 'pdf_max_width' => 380],
        'besar' => ['label' => 'Besar', 'pdf_max_width' => 680],
    ];

    protected $fillable = [
        'form_order_id',
        'path',
        'caption',
        'size',
        'urutan',
    ];

    public function formOrder(): BelongsTo
    {
        return $this->belongsTo(FormOrder::class);
    }

    public function getPdfMaxWidthAttribute(): int
    {
        return self::SIZES[$this->size]['pdf_max_width'] ?? self::SIZES['sedang']['pdf_max_width'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    public const STATUSES = [
        'sampah' => 'Sampah',
        'tidak_potensial' => 'Tidak Potensial',
        'potensial' => 'Potensial',
        'visit_penawaran' => 'Visit/Penawaran',
        'deal' => 'Deal',
    ];

    public const SUMBERS = [
        'google' => 'Google',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
        'twitter_x' => 'Twitter/X',
        'whatsapp' => 'WhatsApp',
        'referral' => 'Referral',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'brand_id',
        'created_by',
        'tanggal',
        'jam',
        'klien',
        'no_wa',
        'kota',
        'paket',
        'status',
        'sumber',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam' => 'datetime:H:i',
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDeal(Builder $query): Builder
    {
        return $query->where('status', 'deal');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getSumberLabelAttribute(): string
    {
        return self::SUMBERS[$this->sumber] ?? $this->sumber;
    }

    public function getFollowUpWaUrlAttribute(): string
    {
        $phone = preg_replace('/\D/', '', $this->no_wa ?? '');

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $brandName = $this->brand->name ?? '';
        $paket = $this->paket ? " terkait *{$this->paket}*" : '';

        $message = "Halo {$this->klien}\n\n"
            ."Kami dari *{$brandName}* ingin follow up kembali{$paket} yang sempat didiskusikan sebelumnya.\n\n"
            .'Apakah masih berkenan untuk melanjutkan? Kami dengan senang hati membantu jika ada pertanyaan atau butuh info tambahan.';

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }
}

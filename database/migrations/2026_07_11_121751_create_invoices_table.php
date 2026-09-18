<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relasi
            |--------------------------------------------------------------------------
            */
            $table->foreignId('brand_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Nomor Invoice
            |--------------------------------------------------------------------------
            | nomor = hasil format final (misal INV/OKG/2507/0001)
            | nomor_urut = sequence asli
            | tahun & bulan digunakan untuk menjaga sequence per periode
            */
            $table->string('nomor')->unique();
            $table->unsignedInteger('nomor_urut');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');

            /*
            |--------------------------------------------------------------------------
            | Data Client
            |--------------------------------------------------------------------------
            */
            $table->string('klien');
            $table->text('alamat')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Informasi Invoice
            |--------------------------------------------------------------------------
            */
            $table->date('tanggal');
            $table->date('jatuh_tempo');

            $table->string('desain_tema')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Snapshot Konfigurasi
            |--------------------------------------------------------------------------
            */
            $table->json('kop_config');
            $table->json('sph_config')->nullable();
            $table->json('sign_config')->nullable();
            $table->json('rekening_config')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Nilai Invoice
            |--------------------------------------------------------------------------
            */
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->decimal('ppn_persen', 5, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status Pembayaran
            |--------------------------------------------------------------------------
            */
            $table->enum('status', ['menunggu', 'lunas'])
                ->default('menunggu');

            $table->date('tanggal_lunas')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Informasi Tambahan
            |--------------------------------------------------------------------------
            */
            $table->text('catatan')->nullable();

            $table->string('qris_path')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Invoice Lock
            |--------------------------------------------------------------------------
            | Jika sudah pernah dicetak maka invoice dianggap terkunci.
            */
            $table->timestamp('printed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Sequence per Brand per Bulan
            |--------------------------------------------------------------------------
            */
            $table->unique([
                'brand_id',
                'tahun',
                'bulan',
                'nomor_urut',
            ], 'invoice_sequence_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

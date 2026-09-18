<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_orders', function (Blueprint $table) {
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

            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Nomor Form Order
            |--------------------------------------------------------------------------
            */
            $table->string('nomor')->unique();
            $table->unsignedInteger('nomor_urut');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedTinyInteger('bulan');

            /*
            |--------------------------------------------------------------------------
            | Data Proyek & Klien
            |--------------------------------------------------------------------------
            */
            $table->date('tanggal_order');
            $table->string('nama_klien');
            $table->text('lokasi_project')->nullable();
            $table->string('jenis_pekerjaan')->nullable();
            $table->string('ukuran_bangunan')->nullable();
            $table->string('arah_mata_angin')->nullable();
            $table->string('share_location')->nullable();

            $table->json('lingkup_pekerjaan')->nullable();
            $table->text('catatan_klien')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status', ['draft', 'selesai'])->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['brand_id', 'tahun', 'bulan', 'nomor_urut'], 'form_order_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_orders');
    }
};

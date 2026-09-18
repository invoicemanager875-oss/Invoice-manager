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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relasi
            |--------------------------------------------------------------------------
            */
            $table->foreignId('invoice_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Hierarki Item
            |--------------------------------------------------------------------------
            | single = item biasa
            | group  = header/group
            | paket  = paket pekerjaan
            */
            $table->enum('type', [
                'single',
                'group',
                'paket',
            ])->default('single');

            /*
            |--------------------------------------------------------------------------
            | Parent Item
            |--------------------------------------------------------------------------
            */
            $table->foreignId('parent_item_id')
                ->nullable()
                ->constrained('invoice_items')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Detail Item
            |--------------------------------------------------------------------------
            */
            $table->text('deskripsi');

            /*
            |--------------------------------------------------------------------------
            | Perhitungan
            |--------------------------------------------------------------------------
            */
            $table->decimal('volume', 10, 2)
                ->default(0);

            $table->string('satuan')
                ->nullable();

            $table->decimal('harga_satuan', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Disimpan hasil kalkulasi server
            |--------------------------------------------------------------------------
            */
            $table->decimal('jumlah', 15, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Urutan Tampilan
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('urutan')
                ->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};

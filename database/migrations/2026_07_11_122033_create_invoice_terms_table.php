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
        Schema::create('invoice_terms', function (Blueprint $table) {
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
            | Informasi Termin
            |--------------------------------------------------------------------------
            */
            $table->string('label');

            /*
            |--------------------------------------------------------------------------
            | Persentase & Nominal
            |--------------------------------------------------------------------------
            */
            $table->decimal('persen', 5, 2)->default(0);
            $table->decimal('nominal', 15, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Status Pembayaran
            |--------------------------------------------------------------------------
            */
            $table->boolean('is_lunas')->default(false);
            $table->date('tanggal_lunas')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Urutan Tampilan
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('urutan')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_terms');
    }
};

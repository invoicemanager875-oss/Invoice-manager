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
        Schema::create('leads', function (Blueprint $table) {
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
            | Waktu Leads Masuk
            |--------------------------------------------------------------------------
            */
            $table->date('tanggal');
            $table->time('jam')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Data Klien
            |--------------------------------------------------------------------------
            */
            $table->string('klien');
            $table->string('no_wa')->nullable();
            $table->string('kota')->nullable();
            $table->string('paket')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status & Sumber
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'sampah',
                'tidak_potensial',
                'potensial',
                'visit_penawaran',
                'deal',
            ])->default('potensial');

            $table->enum('sumber', [
                'google',
                'instagram',
                'facebook',
                'tiktok',
                'youtube',
                'twitter_x',
                'whatsapp',
                'referral',
                'lainnya',
            ]);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['brand_id', 'status']);
            $table->index(['brand_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

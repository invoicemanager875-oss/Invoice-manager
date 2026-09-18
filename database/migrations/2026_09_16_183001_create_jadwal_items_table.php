<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_perencanaan_id')->constrained('jadwal_perencanaans')->cascadeOnDelete();
            $table->unsignedInteger('urutan')->default(1);
            $table->string('nama_item');
            $table->decimal('bobot', 5, 2)->default(0.00);
            $table->unsignedInteger('hari_mulai')->default(1);
            $table->unsignedInteger('hari_selesai')->default(1);
            $table->unsignedInteger('durasi')->default(1);
            $table->json('distribusi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('nomor', 100);
            $table->unsignedInteger('nomor_urut');
            $table->unsignedSmallInteger('tahun');
            $table->string('judul_kontrak')->default('Surat Perjanjian Kerja');
            $table->date('tanggal_kontrak');

            // Pihak Pertama (Snapshot Brand)
            $table->string('pihak_pertama_nama');
            $table->string('pihak_pertama_jabatan');
            $table->string('pihak_pertama_perusahaan');
            $table->text('pihak_pertama_alamat');
            $table->string('pihak_pertama_telepon', 50)->nullable();

            // Pihak Kedua (Klien)
            $table->string('pihak_kedua_nama');
            $table->string('pihak_kedua_identitas', 50)->nullable();
            $table->string('pihak_kedua_perusahaan')->nullable();
            $table->text('pihak_kedua_alamat');
            $table->string('pihak_kedua_telepon', 50);
            $table->string('pihak_kedua_email')->nullable();

            // Parameter Nilai & Waktu
            $table->decimal('nilai_kontrak', 15, 2)->default(0);
            $table->string('nilai_terbilang');
            $table->unsignedInteger('durasi_hari');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            // Narasi & Konfigurasi
            $table->mediumText('narasi')->nullable();
            $table->json('pasal_config')->nullable();
            $table->json('rekening_config')->nullable();
            $table->json('sign_config')->nullable();

            $table->enum('status', ['draft', 'final', 'signed', 'active', 'completed', 'terminated', 'void'])->default('draft');
            $table->string('signed_document_path')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['brand_id', 'nomor'], 'uq_brand_nomor_spk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

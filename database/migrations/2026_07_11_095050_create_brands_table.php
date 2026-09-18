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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('npwp')->nullable();

            $table->text('address')->nullable();

            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->string('logo_path')->nullable();
            $table->string('qris_path')->nullable();

            $table->string('ttd_path')->nullable();
            $table->string('stempel_path')->nullable();
            $table->string('materai_path')->nullable();

            $table->string('ttd_nama')->nullable();
            $table->string('ttd_jabatan')->nullable();

            $table->string('color_header')->nullable();
            $table->string('color_accent')->nullable();

            $table->text('canva_link')->nullable();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};

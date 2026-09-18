<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->unsignedInteger('termin_ke')->default(1);
            $table->string('judul');
            $table->decimal('persentase', 5, 2);
            $table->decimal('nominal', 15, 2);
            $table->text('syarat_pencairan');
            $table->enum('status', ['pending', 'invoiced', 'paid'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_terms');
    }
};

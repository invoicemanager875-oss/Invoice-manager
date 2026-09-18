<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('tahun');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['brand_id', 'tahun'], 'uq_contract_counter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_counters');
    }
};

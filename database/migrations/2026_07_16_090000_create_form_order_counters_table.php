<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_order_counters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('brand_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedInteger('last_number')->default(0);

            $table->timestamps();

            $table->unique(['brand_id', 'year', 'month'], 'form_order_counter_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_order_counters');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_order_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('form_order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('urutan')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_order_images');
    }
};

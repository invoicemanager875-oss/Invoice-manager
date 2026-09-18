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
        Schema::create('form_order_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_order_id')->constrained()->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->string('path')->nullable();
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_order_revisions');
    }
};

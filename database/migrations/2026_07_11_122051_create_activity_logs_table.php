<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Activity Log
     *
     * Tabel ini bersifat immutable.
     *
     * Tidak boleh ada endpoint UPDATE maupun DELETE
     * pada controller manapun.
     *
     * Semua perubahan harus berupa INSERT record baru.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | User
            |--------------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */
            $table->string('action', 100);

            $table->text('description');

            /*
            |--------------------------------------------------------------------------
            | Request Information
            |--------------------------------------------------------------------------
            */
            $table->string('ip_address', 45)->nullable();

            /*
            |--------------------------------------------------------------------------
            | Immutable Timestamp
            |--------------------------------------------------------------------------
            */
            $table->timestamp('created_at')
                ->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

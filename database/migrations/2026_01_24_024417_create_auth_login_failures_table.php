<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auth_login_failures', function (Blueprint $table) {
            $table->id();

            // Credencial usada (email/usuario)
            $table->string('identifier', 150)->nullable();

            // Contexto de seguridad
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Motivo del fallo (controlado por backend)
            $table->string('reason', 100);

            // Relación opcional si el usuario existe
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            // Índices útiles
            $table->index(['identifier']);
            $table->index(['user_id']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_login_failures');
    }
};

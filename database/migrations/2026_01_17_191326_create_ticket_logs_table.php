<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('ticket_id');
            $table->string('action', 30);          // entry, charge, exit, cancel
            $table->string('description', 255);   // Texto descriptivo
            $table->json('payload')->nullable();  // Datos relevantes
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ticket_id')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_logs');
    }
};

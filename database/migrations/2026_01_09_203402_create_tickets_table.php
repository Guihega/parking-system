<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->string('folio', 40)->unique(); // se genera en SP o app
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types');
            $table->foreignId('parking_space_id')->constrained('parking_spaces');

            $table->string('plate', 10);
            $table->timestamp('entry_time');
            $table->timestamp('exit_time')->nullable();

            $table->foreignId('status_id')->constrained('ticket_statuses');
            $table->decimal('total_amount', 10, 2)->nullable();

            $table->timestamps();

            $table->index(['branch_id', 'status_id']);
            $table->index(['plate', 'status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
